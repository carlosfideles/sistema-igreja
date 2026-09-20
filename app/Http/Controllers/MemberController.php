<?php

namespace App\Http\Controllers;

use App\Http\Requests\Members\StoreMemberRequest;
use App\Http\Requests\Members\UpdateMemberRequest;
use App\Models\AuditLog;
use App\Models\Church;
use App\Models\FinancialTransaction;
use App\Models\FunctionModel;
use App\Models\Member;
use App\Models\MemberEcclesiasticalHistory;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class MemberController extends Controller
{
    /**
     * Listagem de membros com isolamento por igreja, filtros avançados e paginação.
     */
    public function index(Request $request): View
    {
        $currentUser = Auth::user();

        // Isolamento de escopo: Usuário só acessa membros das congregações autorizadas
        $accessibleChurchIds = $currentUser->accessibleChurchesQuery()->pluck('churches.id');

        $query = Member::with('church')
            ->whereIn('church_id', $accessibleChurchIds);

        // Filtro de Busca Textual (Nome, Nome Social, CPF, RG, E-mail, Telefone)
        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('full_name', 'like', "%{$search}%")
                  ->orWhere('social_name', 'like', "%{$search}%")
                  ->orWhere('cpf', 'like', "%{$search}%")
                  ->orWhere('rg', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%")
                  ->orWhere('whatsapp', 'like', "%{$search}%");
            });
        }

        // Filtro por Congregação
        if ($churchId = $request->input('church_id')) {
            if ($currentUser->canAccessChurch((int) $churchId)) {
                $query->where('church_id', $churchId);
            }
        }

        // Filtro por Situação
        if ($status = $request->input('status')) {
            if ($status !== 'all') {
                $query->where('status', $status);
            }
        } else {
            // Padrão: membros ativos
            $query->where('status', 'ativo');
        }

        // Filtro por Sexo
        if ($gender = $request->input('gender')) {
            $query->where('gender', $gender);
        }

        // Filtro por Período de Entrada
        if ($entryStart = $request->input('entry_start')) {
            $query->whereDate('entry_date', '>=', $entryStart);
        }
        if ($entryEnd = $request->input('entry_end')) {
            $query->whereDate('entry_date', '<=', $entryEnd);
        }

        // Filtro por Faixa Etária
        if ($ageGroup = $request->input('age_group')) {
            $today = Carbon::today();
            switch ($ageGroup) {
                case '0-12':
                    $query->whereBetween('birth_date', [$today->copy()->subYears(12), $today]);
                    break;
                case '13-17':
                    $query->whereBetween('birth_date', [$today->copy()->subYears(17), $today->copy()->subYears(13)]);
                    break;
                case '18-29':
                    $query->whereBetween('birth_date', [$today->copy()->subYears(29), $today->copy()->subYears(18)]);
                    break;
                case '30-59':
                    $query->whereBetween('birth_date', [$today->copy()->subYears(59), $today->copy()->subYears(30)]);
                    break;
                case '60+':
                    $query->where('birth_date', '<=', $today->copy()->subYears(60));
                    break;
            }
        }

        $members = $query->orderBy('full_name', 'asc')->paginate(15)->withQueryString();

        // Lista de congregações acessíveis para o filtro
        $churches = $currentUser->accessibleChurchesQuery()
            ->where('status', 'active')
            ->orderBy('name', 'asc')
            ->get();

        // Contadores gerais para os cards
        $baseScopeQuery = Member::whereIn('church_id', $accessibleChurchIds);
        $totalMembers = (clone $baseScopeQuery)->count();
        $activeMembers = (clone $baseScopeQuery)->where('status', 'ativo')->count();
        $inactiveMembers = (clone $baseScopeQuery)->where('status', 'inativo')->count();
        $transferredMembers = (clone $baseScopeQuery)->where('status', 'transferido')->count();

        return view('members.index', compact(
            'members',
            'churches',
            'totalMembers',
            'activeMembers',
            'inactiveMembers',
            'transferredMembers'
        ));
    }

    /**
     * Formulário de cadastro de membro.
     */
     public function create(Request $request): View
     {
         $this->authorize('create', Member::class);

         $currentUser = Auth::user();
         $churches = $currentUser->accessibleChurchesQuery()
             ->where('status', 'active')
             ->orderBy('name', 'asc')
             ->get();

         // Funções disponíveis para membros (excluindo funções exclusivas de secretariado)
         $availableFunctions = FunctionModel::where('is_active', true)
             ->where('name', 'not like', '%Secretá%')
             ->where('name', 'not like', '%Secretaria%')
             ->orderBy('name', 'asc')
             ->get();

         $preselectedChurchId = $request->input('church_id');

         return view('members.create', compact('churches', 'preselectedChurchId', 'availableFunctions'));
     }

    /**
     * Salva novo membro no banco de dados.
     */
    public function store(StoreMemberRequest $request): RedirectResponse
    {
        $data = $request->validated();

        if (empty($data['church_id']) && !empty($data['congregation_id'])) {
            $data['church_id'] = $data['congregation_id'];
        }
        unset($data['congregation_id']);

        // Upload de foto do membro
        if ($request->hasFile('photo')) {
            $file = $request->file('photo');
            $extension = $file->guessExtension() ?: 'jpg';
            $filename = time() . '_' . uniqid() . '.' . $extension;
            $path = $file->storeAs('uploads/members', $filename, 'public');
            $data['photo'] = '/storage/' . $path;
        }

        $member = Member::create($data);

        // Vínculo de Múltiplas Funções Exercidas (somente funções não restritas)
        if ($request->has('functions')) {
            $rawFunctionIds = array_filter(array_map('intval', (array) $request->input('functions', [])));
            $allowedFunctionIds = FunctionModel::whereIn('id', $rawFunctionIds)
                ->where('name', 'not like', '%Secretá%')
                ->where('name', 'not like', '%Secretaria%')
                ->pluck('id')
                ->toArray();

            $member->functions()->sync($allowedFunctionIds);

            $userName = Auth::user()->name ?? 'Sistema';
            foreach ($allowedFunctionIds as $fId) {
                $func = FunctionModel::find($fId);
                if ($func) {
                    MemberEcclesiasticalHistory::create([
                        'member_id'   => $member->id,
                        'description' => "Atribuída a função: {$func->name} por {$userName}",
                        'recorded_at' => now(),
                        'created_by'  => Auth::id(),
                    ]);
                }
            }
        }

        // Auditoria
        AuditLog::record(
            action: 'created',
            module: 'members',
            description: "Cadastrou o membro '{$member->full_name}' na congregação '{$member->church->name}'.",
            churchId: $member->church_id,
            entityType: Member::class,
            entityId: $member->id,
            newValues: $member->only(['full_name', 'cpf', 'church_id', 'status', 'entry_date'])
        );

        return redirect()->route('members.show', $member)
            ->with('success', "Membro '{$member->full_name}' cadastrado com sucesso!");
    }

    /**
     * Exibe a ficha cadastral completa do membro.
     * PROTEÇÃO CONTRA IDOR: Validação estrita via MemberPolicy (canAccessChurch).
     */
    public function show(Member $member): View
    {
        $this->authorize('view', $member);

        $member->load([
            'church',
            'transfers.fromChurch',
            'transfers.toChurch',
            'transfers.transferredBy',
            'transfers.approver',
            'ecclesiasticalHistories.creator',
            'functions',
        ]);

        $recentAuditLogs = AuditLog::where('entity_type', Member::class)
            ->where('entity_id', $member->id)
            ->orderBy('created_at', 'desc')
            ->take(8)
            ->get();

        $titheHistory = FinancialTransaction::where('member_id', $member->id)
            ->where('category', 'dizimo')
            ->orderBy('competence_date', 'desc')
            ->get();

        return view('members.show', compact('member', 'recentAuditLogs', 'titheHistory'));
    }

    /**
     * Exibe a ficha oficial de membro para impressão / emissão de documento.
     * PROTEÇÃO CONTRA IDOR: Validação estrita via MemberPolicy.
     */
    public function print(Request $request, Member $member): View
    {
        $this->authorize('view', $member);

        $hasCustomOptions = $request->has('custom_options') || $request->has('include_ecclesiastical') || $request->has('include_transfers') || $request->has('include_tithes');

        $includeCadastral = true;
        $includeEcclesiastical = $hasCustomOptions ? $request->boolean('include_ecclesiastical', false) : true;
        $includeTransfers = $hasCustomOptions ? $request->boolean('include_transfers', false) : true;
        $includeTithes = $hasCustomOptions ? $request->boolean('include_tithes', false) : false;

        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');
        $hideValues = $request->boolean('hide_values', false);
        $hideDocuments = $request->boolean('hide_documents', false);

        $defaultSignature = (Auth::check() && Auth::user()->isLocal()) ? 'local' : 'regional';
        $signatureType = $request->input('signature_type', $defaultSignature);

        // Trava de segurança: Secretário LOCAL só pode emitir assinatura Local ou Sem Assinatura
        if (Auth::check() && Auth::user()->isLocal() && in_array($signatureType, ['regional', 'both'])) {
            $signatureType = 'local';
        }

        $relations = ['church', 'functions'];

        if ($includeTransfers) {
            $relations[] = 'transfers.fromChurch';
            $relations[] = 'transfers.toChurch';
            $relations[] = 'transfers.transferredBy';
            $relations[] = 'transfers.approver';
        }

        if ($includeEcclesiastical) {
            $relations[] = 'ecclesiasticalHistories.creator.role';
        }

        $member->load($relations);

        $tithes = collect();
        $currentUser = Auth::user();
        $canViewFinance = $currentUser && ($currentUser->isFirstSecretaryRegional() || $currentUser->hasPermission('finance.view'));

        if ($includeTithes && $canViewFinance) {
            $tithesQuery = FinancialTransaction::with('church')
                ->where('member_id', $member->id)
                ->where('category', 'dizimo');

            if ($currentUser->isLocal()) {
                $accessibleChurchIds = $currentUser->churches()->pluck('churches.id');
                $tithesQuery->whereIn('church_id', $accessibleChurchIds);
            }

            if ($startDate) {
                $tithesQuery->whereDate('competence_date', '>=', $startDate);
            }

            if ($endDate) {
                $tithesQuery->whereDate('competence_date', '<=', $endDate);
            }

            $tithes = $tithesQuery->orderBy('competence_date', 'desc')->get();
        } else {
            $includeTithes = false;
        }

        return view('members.print', compact(
            'member',
            'includeCadastral',
            'includeEcclesiastical',
            'includeTransfers',
            'includeTithes',
            'tithes',
            'startDate',
            'endDate',
            'hideValues',
            'hideDocuments',
            'signatureType'
        ));
    }

    /**
     * Formulário de edição de membro.
     * PROTEÇÃO CONTRA IDOR: Validação estrita via MemberPolicy.
     */
    public function edit(Member $member): View
    {
        $this->authorize('update', $member);

        $currentUser = Auth::user();
        $churches = $currentUser->accessibleChurchesQuery()
            ->where('status', 'active')
            ->orderBy('name', 'asc')
            ->get();

        $member->load('functions');
        
        // Funções disponíveis para atribuição manual (excluindo secretariado)
        $availableFunctions = FunctionModel::where('is_active', true)
            ->where('name', 'not like', '%Secretá%')
            ->where('name', 'not like', '%Secretaria%')
            ->orderBy('name', 'asc')
            ->get();

        return view('members.edit', compact('member', 'churches', 'availableFunctions'));
    }

    /**
     * Atualiza os dados do membro.
     */
    public function update(UpdateMemberRequest $request, Member $member): RedirectResponse
    {
        $this->authorize('update', $member);

        $oldPosition = trim((string) $member->ecclesiastical_position);
        $oldValues = $member->only(['full_name', 'social_name', 'cpf', 'rg', 'church_id', 'status', 'phone', 'email', 'address', 'ecclesiastical_position']);
        $data = $request->validated();

        // Trava de Segurança: congregação não pode ser alterada via edição direta (somente via fluxo de transferências)
        $data['church_id'] = $member->church_id;
        unset($data['congregation_id']);

        // Upload de nova foto com remoção segura da anterior
        if ($request->hasFile('photo')) {
            if ($member->photo && str_starts_with($member->photo, '/storage/')) {
                $oldPath = str_replace('/storage/', '', $member->photo);
                Storage::disk('public')->delete($oldPath);
            }

            $file = $request->file('photo');
            $extension = $file->guessExtension() ?: 'jpg';
            $filename = time() . '_' . uniqid() . '.' . $extension;
            $path = $file->storeAs('uploads/members', $filename, 'public');
            $data['photo'] = '/storage/' . $path;
        }

        $member->update($data);

        // Sincronização de Funções Exercidas (preservando funções restritas de Secretário)
        $existingSecretaryFunctionIds = $member->functions()
            ->where(function ($q) {
                $q->where('name', 'like', '%Secretá%')
                  ->orWhere('name', 'like', '%Secretaria%');
            })
            ->pluck('functions.id')
            ->toArray();

        $oldRegularFunctionIds = $member->functions()
            ->where('name', 'not like', '%Secretá%')
            ->where('name', 'not like', '%Secretaria%')
            ->pluck('functions.id')
            ->toArray();

        $submittedFunctionIds = array_filter(array_map('intval', (array) $request->input('functions', [])));
        
        // Filtra apenas funções permitidas no envio
        $newRegularFunctionIds = FunctionModel::whereIn('id', $submittedFunctionIds)
            ->where('name', 'not like', '%Secretá%')
            ->where('name', 'not like', '%Secretaria%')
            ->pluck('id')
            ->toArray();

        // Combina as funções normais enviadas com as de secretário preexistentes
        $finalFunctionIds = array_unique(array_merge($newRegularFunctionIds, $existingSecretaryFunctionIds));
        $member->functions()->sync($finalFunctionIds);

        $addedIds   = array_diff($newRegularFunctionIds, $oldRegularFunctionIds);
        $removedIds = array_diff($oldRegularFunctionIds, $newRegularFunctionIds);
        $userName   = Auth::user()->name ?? 'Sistema';

        foreach ($addedIds as $fId) {
            $func = FunctionModel::find($fId);
            if ($func) {
                MemberEcclesiasticalHistory::create([
                    'member_id'   => $member->id,
                    'description' => "Atribuída a função: {$func->name} por {$userName}",
                    'recorded_at' => now(),
                    'created_by'  => Auth::id(),
                ]);
            }
        }

        foreach ($removedIds as $fId) {
            $func = FunctionModel::find($fId);
            if ($func) {
                MemberEcclesiasticalHistory::create([
                    'member_id'   => $member->id,
                    'description' => "Removida a função: {$func->name} por {$userName}",
                    'recorded_at' => now(),
                    'created_by'  => Auth::id(),
                ]);
            }
        }

        $newPosition = trim((string) ($request->input('ecclesiastical_position') ?? $member->ecclesiastical_position));

        $normalizedOld = $oldPosition !== '' ? $oldPosition : 'Membro';
        $normalizedNew = $newPosition !== '' ? $newPosition : 'Membro';

        // Se o cargo foi alterado e é estritamente diferente, registra no histórico eclesiástico
        if ($normalizedOld !== $normalizedNew) {
            MemberEcclesiasticalHistory::create([
                'member_id' => $member->id,
                'description' => "Cargo alterado de {$normalizedOld} para {$normalizedNew}",
                'recorded_at' => now(),
                'created_by' => Auth::id(),
            ]);
        }

        // Se foi enviada uma nova anotação no histórico eclesiástico
        if ($request->filled('ecclesiastical_record')) {
            MemberEcclesiasticalHistory::create([
                'member_id' => $member->id,
                'description' => $request->input('ecclesiastical_record'),
                'recorded_at' => now(),
                'created_by' => Auth::id(),
            ]);
        }

        $newValues = $member->only(['full_name', 'social_name', 'cpf', 'rg', 'church_id', 'status', 'phone', 'email', 'address', 'ecclesiastical_position']);

        // Auditoria
        AuditLog::record(
            action: 'updated',
            module: 'members',
            description: "Atualizou os dados do membro '{$member->full_name}'.",
            churchId: $member->church_id,
            entityType: Member::class,
            entityId: $member->id,
            oldValues: $oldValues,
            newValues: $newValues
        );

        return redirect()->route('members.show', $member)
            ->with('success', "Dados do membro '{$member->full_name}' atualizados com sucesso!");
    }

    /**
     * Alterna a situação eclesiástica do membro.
     * SEGURANÇA: valida o status recebido contra lista de valores permitidos.
     */
    public function toggleStatus(Request $request, Member $member): RedirectResponse
    {
        $this->authorize('update', $member);

        // Validação estrita: só aceita status reconhecidos pelo sistema
        $validated = $request->validate([
            'status' => ['required', 'string', \Illuminate\Validation\Rule::in([
                'ativo', 'inativo', 'transferido', 'disciplina', 'falecido',
            ])],
        ]);

        $oldStatus = $member->status;
        $newStatus = $validated['status'];

        $member->update(['status' => $newStatus]);

        AuditLog::record(
            action: 'status_changed',
            module: 'members',
            description: "Alterou a situação do membro '{$member->full_name}' de '{$oldStatus}' para '{$newStatus}'.",
            churchId: $member->church_id,
            entityType: Member::class,
            entityId: $member->id,
            oldValues: ['status' => $oldStatus],
            newValues: ['status' => $newStatus]
        );

        $statusLabel = match($newStatus) {
            'ativo' => 'Ativo',
            'inativo' => 'Inativo',
            'transferido' => 'Transferido',
            'disciplina' => 'Em Disciplina',
            'falecido' => 'Falecido',
            default => $newStatus,
        };

        return redirect()->back()
            ->with('success', "Situação do membro alterada para '{$statusLabel}' com sucesso!");
    }

    /**
     * Desativação do membro (Soft Delete).
     */
    public function destroy(Member $member): RedirectResponse
    {
        $this->authorize('delete', $member);

        $name = $member->full_name;
        $memberId = $member->id;
        $churchId = $member->church_id;

        $member->delete();

        AuditLog::record(
            action: 'deleted',
            module: 'members',
            description: "Desativou o membro '{$name}' (ID: {$memberId}).",
            churchId: $churchId,
            entityType: Member::class,
            entityId: $memberId
        );

        return redirect()->route('members.index')
            ->with('success', "Membro '{$name}' desativado com sucesso!");
    }
}
