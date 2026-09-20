<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\FunctionModel;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class SettingsController extends Controller
{
    /**
     * Garante que apenas usuários com perfil Regional acessem o módulo de Configurações.
     */
    protected function authorizeRegional(): void
    {
        $user = Auth::user();

        if (!$user || (!$user->isRegional() && !$user->isFirstSecretaryRegional())) {
            abort(403, 'Acesso restrito à Secretaria Regional.');
        }
    }

    /**
     * Painel de configurações gerais (Funções exercidas, parâmetros).
     */
    public function index(): View
    {
        $this->authorizeRegional();

        $functions = FunctionModel::withCount('members')
            ->orderBy('name', 'asc')
            ->get();

        return view('settings.index', compact('functions'));
    }

    /**
     * Cadastra nova função exercida.
     */
    public function storeFunction(Request $request): RedirectResponse
    {
        $this->authorizeRegional();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100', 'unique:functions,name'],
        ], [
            'name.required' => 'O nome da função é obrigatório.',
            'name.unique'   => 'Já existe uma função cadastrada com este nome.',
            'name.max'      => 'O nome da função não pode exceder 100 caracteres.',
        ]);

        $function = FunctionModel::create([
            'name'      => trim($validated['name']),
            'is_active' => true,
        ]);

        AuditLog::record(
            action: 'created',
            module: 'settings',
            description: "Cadastrou a nova função exercida '{$function->name}'.",
            entityType: FunctionModel::class,
            entityId: $function->id,
            newValues: ['name' => $function->name, 'is_active' => true]
        );

        return redirect()->route('settings.index')
            ->with('success', "Função '{$function->name}' cadastrada com sucesso!");
    }

    /**
     * Atualiza o nome de uma função exercida existente.
     */
    public function updateFunction(Request $request, FunctionModel $function): RedirectResponse
    {
        $this->authorizeRegional();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100', Rule::unique('functions', 'name')->ignore($function->id)],
        ], [
            'name.required' => 'O nome da função é obrigatório.',
            'name.unique'   => 'Já existe uma função cadastrada com este nome.',
            'name.max'      => 'O nome da função não pode exceder 100 caracteres.',
        ]);

        $oldName = $function->name;
        $function->update([
            'name' => trim($validated['name']),
        ]);

        AuditLog::record(
            action: 'updated',
            module: 'settings',
            description: "Alterou o nome da função de '{$oldName}' para '{$function->name}'.",
            entityType: FunctionModel::class,
            entityId: $function->id,
            oldValues: ['name' => $oldName],
            newValues: ['name' => $function->name]
        );

        return redirect()->route('settings.index')
            ->with('success', "Função atualizada com sucesso!");
    }

    /**
     * Ativa / Desativa uma função exercida.
     */
    public function toggleFunctionStatus(FunctionModel $function): RedirectResponse
    {
        $this->authorizeRegional();

        $oldStatus = $function->is_active;
        $newStatus = !$oldStatus;

        $function->update([
            'is_active' => $newStatus,
        ]);

        $statusText = $newStatus ? 'ativada' : 'desativada';

        AuditLog::record(
            action: 'status_changed',
            module: 'settings',
            description: "Função '{$function->name}' foi {$statusText}.",
            entityType: FunctionModel::class,
            entityId: $function->id,
            oldValues: ['is_active' => $oldStatus],
            newValues: ['is_active' => $newStatus]
        );

        return redirect()->route('settings.index')
            ->with('success', "Função '{$function->name}' {$statusText} com sucesso!");
    }
}
