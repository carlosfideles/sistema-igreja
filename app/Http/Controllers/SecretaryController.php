<?php

namespace App\Http\Controllers;

use App\Http\Requests\Secretaries\StoreSecretaryRequest;
use App\Http\Requests\Secretaries\UpdateSecretaryRequest;
use App\Models\AuditLog;
use App\Models\Church;
use App\Models\FunctionModel;
use App\Models\Member;
use App\Models\MemberEcclesiasticalHistory;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class SecretaryController extends Controller
{
    /**
     * Listagem de secretários com filtros, busca e paginação.
     */
    public function index(Request $request): View
    {
        $currentUser = Auth::user();

        $query = User::with(['role', 'churches', 'member']);

        // Isolamento de escopo: usuários locais só visualizam secretários de suas congregações ou a si próprios
        if ($currentUser->isLocal()) {
            $userChurchIds = $currentUser->churches()->pluck('churches.id');
            $query->where(function ($q) use ($currentUser, $userChurchIds) {
                $q->where('id', $currentUser->id)
                  ->orWhereHas('churches', function ($c) use ($userChurchIds) {
                      $c->whereIn('churches.id', $userChurchIds);
                  });
            });
        }

        // Filtro de busca textual (Nome, E-mail, CPF)
        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('cpf', 'like', "%{$search}%");
            });
        }

        // Filtro por Status
        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        // Filtro por Papel / Classificação
        if ($roleId = $request->input('role_id')) {
            $query->where('role_id', $roleId);
        }

        // Filtro por Abrangência (Regional / Local)
        if ($scope = $request->input('scope')) {
            $query->where('scope', $scope);
        }

        // Filtro por Congregação
        if ($churchId = $request->input('church_id')) {
            $query->where(function ($q) use ($churchId) {
                $q->where('scope', 'REGIONAL')
                  ->orWhereHas('churches', function ($c) use ($churchId) {
                      $c->where('churches.id', $churchId);
                  });
            });
        }

        $secretaries = $query->orderBy('name', 'asc')->paginate(15)->withQueryString();

        $roles = Role::orderBy('level', 'asc')->get();
        $churches = $currentUser->accessibleChurchesQuery()->where('status', 'active')->orderBy('name', 'asc')->get();

        return view('secretaries.index', compact('secretaries', 'roles', 'churches'));
    }

    /**
     * Formulário de cadastro de novo secretário.
     */
    public function create(): View
    {
        $currentUser = Auth::user();

        // Filtrar papéis conforme hierarquia do usuário
        $rolesQuery = Role::query();
        if (!$currentUser->isFirstSecretaryRegional()) {
            $currentLevel = $currentUser->role?->level ?? 99;
            $rolesQuery->where('level', '>=', $currentLevel);
        }
        $roles = $rolesQuery->orderBy('level', 'asc')->get();

        // Congregações disponíveis para vinculação
        $churches = $currentUser->accessibleChurchesQuery()
            ->where('status', 'active')
            ->orderBy('name', 'asc')
            ->get();

        return view('secretaries.create', compact('roles', 'churches', 'currentUser'));
    }

    /**
     * Salva novo secretário no banco de dados.
     */
    public function store(StoreSecretaryRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $data['password'] = Hash::make($data['password']);

        $secretary = User::create($data);

        // Se for abrangência LOCAL, vincula as igrejas selecionadas
        if ($secretary->scope === 'LOCAL' && !empty($request->input('churches'))) {
            $secretary->churches()->sync($request->input('churches'));
        }

        // Se o secretário estiver ativo, sincroniza a função "1º Secretário" com a ficha do membro
        if ($secretary->isActive()) {
            $this->syncSecretaryFunctionWithMember($secretary);
        }

        // Auditoria
        AuditLog::record(
            action: 'created',
            module: 'secretaries',
            description: "Cadastrou o secretário '{$secretary->name}' ({$secretary->email}) com abrangência {$secretary->scope}.",
            entityType: User::class,
            entityId: $secretary->id,
            newValues: $secretary->only(['name', 'email', 'cpf', 'role_id', 'scope', 'status'])
        );

        return redirect()->route('secretaries.index')
            ->with('success', "Secretário '{$secretary->name}' cadastrado com sucesso!");
    }

    /**
     * Exibe a ficha completa do secretário.
     */
    public function show(User $secretary): View
    {
        $currentUser = Auth::user();

        // Checagem de isolamento para secretários locais
        if ($currentUser->isLocal() && $currentUser->id !== $secretary->id) {
            $hasCommonChurch = $currentUser->churches()->pluck('churches.id')
                ->intersect($secretary->churches()->pluck('churches.id'))
                ->isNotEmpty();

            if (!$hasCommonChurch) {
                abort(403, 'Você não possui permissão para acessar os dados deste secretário.');
            }
        }

        $secretary->load(['role.permissions', 'churches', 'member']);
        $recentAuditLogs = AuditLog::where('user_id', $secretary->id)
            ->orderBy('created_at', 'desc')
            ->take(10)
            ->get();

        return view('secretaries.show', compact('secretary', 'recentAuditLogs'));
    }

    /**
     * Formulário de edição de secretário.
     */
    public function edit(User $secretary): View
    {
        $currentUser = Auth::user();

        $rolesQuery = Role::query();
        if (!$currentUser->isFirstSecretaryRegional()) {
            $currentLevel = $currentUser->role?->level ?? 99;
            $rolesQuery->where('level', '>=', $currentLevel);
        }
        $roles = $rolesQuery->orderBy('level', 'asc')->get();

        $churches = $currentUser->accessibleChurchesQuery()
            ->where('status', 'active')
            ->orderBy('name', 'asc')
            ->get();

        $selectedChurchIds = $secretary->churches()->pluck('churches.id')->toArray();

        return view('secretaries.edit', compact('secretary', 'roles', 'churches', 'selectedChurchIds', 'currentUser'));
    }

    /**
     * Atualiza os dados do secretário.
     */
    public function update(UpdateSecretaryRequest $request, User $secretary): RedirectResponse
    {
        $oldValues = $secretary->only(['name', 'email', 'cpf', 'phone', 'role_id', 'scope', 'status']);

        $data = $request->validated();

        if (!empty($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        } else {
            unset($data['password']);
        }

        $secretary->update($data);

        // Atualização dos vínculos de congregação
        if ($secretary->scope === 'LOCAL') {
            $secretary->churches()->sync($request->input('churches', []));
        } else {
            // Se for REGIONAL, remove vínculos manuais (acesso amplo automático)
            $secretary->churches()->detach();
        }

        $newValues = $secretary->only(['name', 'email', 'cpf', 'phone', 'role_id', 'scope', 'status']);

        // Auditoria
        AuditLog::record(
            action: 'updated',
            module: 'secretaries',
            description: "Atualizou os dados do secretário '{$secretary->name}' ({$secretary->email}).",
            entityType: User::class,
            entityId: $secretary->id,
            oldValues: $oldValues,
            newValues: $newValues
        );

        return redirect()->route('secretaries.index')
            ->with('success', "Dados do secretário '{$secretary->name}' atualizados com sucesso!");
    }

    /**
     * Alterna o status do secretário (Ativo / Inativo / Bloqueado).
     * SEGURANÇA: verifica hierarquia — ninguém pode bloquear alguém de nível igual ou superior.
     */
    public function toggleStatus(Request $request, User $secretary): RedirectResponse
    {
        $currentUser = Auth::user();

        // Proteção absoluta do 1º Secretário Regional
        if ($secretary->isFirstSecretaryRegional()) {
            return redirect()->back()->with('error', 'O 1º Secretário Regional não pode ter seu status alterado.');
        }

        // Um usuário não pode alterar o próprio status
        if ($secretary->id === $currentUser->id) {
            return redirect()->back()->with('error', 'Você não pode alterar seu próprio status.');
        }

        // Verificação de permissão via Policy
        $this->authorize('delete', $secretary);

        // Verificação hierárquica: não pode bloquear alguém de nível igual ou superior
        if (!$currentUser->isFirstSecretaryRegional()) {
            $currentLevel = $currentUser->role?->level ?? 99;
            $targetLevel  = $secretary->role?->level ?? 99;

            if ($targetLevel <= $currentLevel) {
                return redirect()->back()->with('error', 'Você não possui autorização hierárquica para alterar o status deste secretário.');
            }
        }

        // Validação estrita do status permitido
        $validated = $request->validate([
            'status' => ['required', 'string', \Illuminate\Validation\Rule::in(['active', 'inactive', 'blocked'])],
        ]);

        $oldStatus = $secretary->status;
        $newStatus = $validated['status'];

        $secretary->update(['status' => $newStatus]);

        if ($newStatus === 'active') {
            $this->syncSecretaryFunctionWithMember($secretary);
        } else {
            $this->detachSecretaryFunctionFromMember($secretary);
        }

        AuditLog::record(
            action: 'status_changed',
            module: 'secretaries',
            description: "Alterou o status do secretário '{$secretary->name}' de '{$oldStatus}' para '{$newStatus}'.",
            entityType: User::class,
            entityId: $secretary->id,
            oldValues: ['status' => $oldStatus],
            newValues: ['status' => $newStatus]
        );

        $statusLabel = match($newStatus) {
            'active' => 'Ativo',
            'inactive' => 'Inativo',
            'blocked' => 'Bloqueado',
            default => $newStatus,
        };

        return redirect()->back()->with('success', "Status do secretário alterado para '{$statusLabel}' com sucesso!");
    }

    /**
     * Desativa / Soft delete do secretário.
     */
    public function destroy(User $secretary): RedirectResponse
    {
        if ($secretary->isFirstSecretaryRegional()) {
            return redirect()->back()->with('error', 'O 1º Secretário Regional nunca pode ser excluído ou desativado.');
        }

        if ($secretary->id === Auth::id()) {
            return redirect()->back()->with('error', 'Você não pode desativar seu próprio usuário.');
        }

        $name = $secretary->name;
        $email = $secretary->email;

        // Ao desativar/excluir secretário, remove a função 1º Secretário do membro mantendo as demais
        $this->detachSecretaryFunctionFromMember($secretary);

        $secretary->delete();

        AuditLog::record(
            action: 'deleted',
            module: 'secretaries',
            description: "Desativou o secretário '{$name}' ({$email}).",
            entityType: User::class,
            entityId: $secretary->id
        );

        return redirect()->route('secretaries.index')
            ->with('success', "Secretário '{$name}' desativado com sucesso!");
    }

    /**
     * Sincroniza a função '1º Secretário' na ficha do membro correspondente (por ID, e-mail ou CPF).
     */
    protected function syncSecretaryFunctionWithMember(User $secretary, ?int $memberId = null): void
    {
        $member = null;
        if ($memberId) {
            $member = Member::find($memberId);
        }
        if (!$member && request()->filled('member_id')) {
            $member = Member::find(request()->input('member_id'));
        }
        if (!$member) {
            $member = Member::where('email', $secretary->email)
                ->when(!empty($secretary->cpf), function ($q) use ($secretary) {
                    $q->orWhere('cpf', $secretary->cpf);
                })
                ->first();
        }

        if ($member) {
            $function = FunctionModel::firstOrCreate(
                ['name' => '1º Secretário'],
                ['is_active' => true]
            );

            $hasFunction = $member->functions()->where('functions.id', $function->id)->exists();

            if (!$hasFunction) {
                $member->functions()->syncWithoutDetaching([$function->id]);

                $userName = Auth::user()->name ?? 'Sistema';
                MemberEcclesiasticalHistory::create([
                    'member_id'   => $member->id,
                    'description' => "Atribuída a função: 1º Secretário por {$userName}",
                    'recorded_at' => now(),
                    'created_by'  => Auth::id(),
                ]);
            }
        }
    }

    /**
     * Remove a função '1º Secretário' da ficha do membro correspondente mantendo as demais funções intactas.
     */
    protected function detachSecretaryFunctionFromMember(User $secretary, ?int $memberId = null): void
    {
        $member = null;
        if ($memberId) {
            $member = Member::find($memberId);
        }
        if (!$member && request()->filled('member_id')) {
            $member = Member::find(request()->input('member_id'));
        }
        if (!$member) {
            $member = Member::where('email', $secretary->email)
                ->when(!empty($secretary->cpf), function ($q) use ($secretary) {
                    $q->orWhere('cpf', $secretary->cpf);
                })
                ->first();
        }

        if ($member) {
            $function = FunctionModel::where('name', '1º Secretário')->first();

            if ($function && $member->functions()->where('functions.id', $function->id)->exists()) {
                $member->functions()->detach($function->id);

                $userName = Auth::user()->name ?? 'Sistema';
                MemberEcclesiasticalHistory::create([
                    'member_id'   => $member->id,
                    'description' => "Removida a função: 1º Secretário por {$userName}",
                    'recorded_at' => now(),
                    'created_by'  => Auth::id(),
                ]);
            }
        }
    }
}
