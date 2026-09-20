<?php

namespace App\Http\Controllers;

use App\Http\Requests\Churches\StoreChurchRequest;
use App\Http\Requests\Churches\UpdateChurchRequest;
use App\Models\AuditLog;
use App\Models\Church;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class ChurchController extends Controller
{
    /**
     * Listagem de congregações com filtros, contadores e paginação.
     */
    public function index(Request $request): View
    {
        $currentUser = Auth::user();

        // Isolamento de escopo: Usuários regionais acessam todas as congregações; locais apenas as autorizadas
        $query = $currentUser->accessibleChurchesQuery()
            ->withCount([
                'members',
                'activeMembers',
                'users' => function ($q) {
                    $q->where('status', 'active');
                }
            ]);

        // Busca textual (Nome, Código, CNPJ, Responsável, Cidade)
        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%")
                  ->orWhere('cnpj', 'like', "%{$search}%")
                  ->orWhere('responsible_name', 'like', "%{$search}%")
                  ->orWhere('city', 'like', "%{$search}%");
            });
        }

        // Filtro por Situação
        if ($status = $request->input('status')) {
            if ($status !== 'all') {
                $query->where('status', $status);
            }
        } else {
            // Padrão: congregações ativas
            $query->where('status', 'active');
        }

        // Filtro por Cidade
        if ($city = $request->input('city')) {
            $query->where('city', $city);
        }

        // Filtro por Estado
        if ($state = $request->input('state')) {
            $query->where('state', $state);
        }

        $churches = $query->orderBy('name', 'asc')->paginate(12)->withQueryString();

        // Lista de cidades disponíveis para o filtro
        $cities = $currentUser->accessibleChurchesQuery()
            ->select('city')
            ->distinct()
            ->orderBy('city', 'asc')
            ->pluck('city');

        // Contadores gerais para cards
        $totalChurches = (clone $currentUser->accessibleChurchesQuery())->count();
        $activeChurches = (clone $currentUser->accessibleChurchesQuery())->where('status', 'active')->count();
        $inactiveChurches = (clone $currentUser->accessibleChurchesQuery())->where('status', 'inactive')->count();

        return view('churches.index', compact(
            'churches',
            'cities',
            'totalChurches',
            'activeChurches',
            'inactiveChurches'
        ));
    }

    /**
     * Formulário de cadastro de nova congregação.
     */
    public function create(): View
    {
        $this->authorize('create', Church::class);

        return view('churches.create');
    }

    /**
     * Salva nova congregação no banco de dados.
     */
    public function store(StoreChurchRequest $request): RedirectResponse
    {
        $data = $request->validated();

        // Upload seguro de logotipo se enviado
        if ($request->hasFile('logo')) {
            $file = $request->file('logo');
            $extension = $file->guessExtension() ?: 'png';
            $filename = time() . '_' . uniqid() . '.' . $extension;
            $path = $file->storeAs('uploads/churches', $filename, 'public');
            $data['logo'] = '/storage/' . $path;
        }

        $church = Church::create($data);

        // Se o criador for um secretário local com permissão especial, autoriza o vínculo automático
        $currentUser = Auth::user();
        if ($currentUser->isLocal()) {
            $currentUser->churches()->attach($church->id);
        }

        // Auditoria
        AuditLog::record(
            action: 'created',
            module: 'churches',
            description: "Cadastrou a congregação '{$church->name}' (Código: {$church->code}, Cidade: {$church->city}/{$church->state}).",
            churchId: $church->id,
            entityType: Church::class,
            entityId: $church->id,
            newValues: $church->only(['name', 'code', 'cnpj', 'city', 'state', 'responsible_name', 'status'])
        );

        return redirect()->route('churches.index')
            ->with('success', "Congregação '{$church->name}' cadastrada com sucesso!");
    }

    /**
     * Exibe os detalhes e perfil completo da congregação.
     * PROTEÇÃO CONTRA IDOR: Validação estrita via canAccessChurch / ChurchPolicy.
     */
    public function show(Church $church): View
    {
        $this->authorize('view', $church);

        $church->loadCount(['members', 'activeMembers']);
        $church->load(['users' => function ($q) {
            $q->with('role')->where('status', 'active');
        }]);

        $recentMembers = $church->members()
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();

        $recentAuditLogs = AuditLog::where('church_id', $church->id)
            ->orderBy('created_at', 'desc')
            ->take(8)
            ->get();

        return view('churches.show', compact('church', 'recentMembers', 'recentAuditLogs'));
    }

    /**
     * Formulário de edição da congregação.
     * PROTEÇÃO CONTRA IDOR: Validação estrita via canAccessChurch / ChurchPolicy.
     */
    public function edit(Church $church): View
    {
        if (!auth()->user()->isRegional()) {
            abort(403, 'Acesso não autorizado. Apenas secretários regionais podem editar dados da congregação.');
        }

        $this->authorize('update', $church);

        return view('churches.edit', compact('church'));
    }

    /**
     * Atualiza os dados da congregação.
     */
    public function update(UpdateChurchRequest $request, Church $church): RedirectResponse
    {
        if (!auth()->user()->isRegional()) {
            abort(403, 'Acesso não autorizado. Apenas secretários regionais podem editar dados da congregação.');
        }

        $this->authorize('update', $church);

        $oldValues = $church->only(['name', 'code', 'cnpj', 'phone', 'email', 'address', 'city', 'state', 'responsible_name', 'status']);
        $data = $request->validated();

        // Upload de novo logotipo com remoção segura do antigo
        if ($request->hasFile('logo')) {
            if ($church->logo && str_starts_with($church->logo, '/storage/')) {
                $oldPath = str_replace('/storage/', '', $church->logo);
                Storage::disk('public')->delete($oldPath);
            }

            $file = $request->file('logo');
            $extension = $file->guessExtension() ?: 'png';
            $filename = time() . '_' . uniqid() . '.' . $extension;
            $path = $file->storeAs('uploads/churches', $filename, 'public');
            $data['logo'] = '/storage/' . $path;
        }

        $church->update($data);

        $newValues = $church->only(['name', 'code', 'cnpj', 'phone', 'email', 'address', 'city', 'state', 'responsible_name', 'status']);

        // Auditoria
        AuditLog::record(
            action: 'updated',
            module: 'churches',
            description: "Atualizou os dados da congregação '{$church->name}'.",
            churchId: $church->id,
            entityType: Church::class,
            entityId: $church->id,
            oldValues: $oldValues,
            newValues: $newValues
        );

        return redirect()->route('churches.show', $church)
            ->with('success', "Dados da congregação '{$church->name}' atualizados com sucesso!");
    }

    /**
     * Alterna o status da congregação (Ativo / Inativo).
     */
    public function toggleStatus(Request $request, Church $church): RedirectResponse
    {
        if (!auth()->user()->isRegional()) {
            abort(403, 'Acesso não autorizado. Apenas secretários regionais podem editar dados da congregação.');
        }

        $this->authorize('update', $church);

        $newStatus = $church->status === 'active' ? 'inactive' : 'active';
        $oldStatus = $church->status;

        $church->update(['status' => $newStatus]);

        AuditLog::record(
            action: 'status_change',
            module: 'churches',
            description: "Alterou a situação da congregação '{$church->name}' de '{$oldStatus}' para '{$newStatus}'.",
            churchId: $church->id,
            entityType: Church::class,
            entityId: $church->id,
            oldValues: ['status' => $oldStatus],
            newValues: ['status' => $newStatus]
        );

        $statusLabel = match($newStatus) {
            'active' => 'Ativa',
            'inactive' => 'Inativa',
            default => $newStatus,
        };

        return redirect()->back()
            ->with('success', "Situação da congregação alterada para '{$statusLabel}' com sucesso!");
    }

    /**
     * Desativação da congregação (Soft Delete).
     */
    public function destroy(Church $church): RedirectResponse
    {
        $this->authorize('delete', $church);

        $name = $church->name;
        $churchId = $church->id;

        $church->delete();

        AuditLog::record(
            action: 'deleted',
            module: 'churches',
            description: "Desativou a congregação '{$name}' (ID: {$churchId}).",
            churchId: $churchId,
            entityType: Church::class,
            entityId: $churchId
        );

        return redirect()->route('churches.index')
            ->with('success', "Congregação '{$name}' desativada com sucesso!");
    }
}
