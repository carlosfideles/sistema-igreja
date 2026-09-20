<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Church;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuditController extends Controller
{
    /**
     * Lista os logs de auditoria com filtros, respeitando o escopo do usuário.
     */
    public function index(Request $request): View
    {
        $currentUser = Auth::user();

        // Protege o acesso: exclusivamente para escopo REGIONAL com permissão audit.view
        if (! $currentUser->isRegional() || ! $currentUser->hasPermission('audit.view')) {
            abort(403, 'Acesso restrito exclusivamente à Secretaria Regional.');
        }

        $query = AuditLog::with(['user', 'church'])->orderBy('created_at', 'desc');

        // --- Filtros ---

        // Filtro por usuário
        if ($userId = $request->input('user_id')) {
            $query->where('user_id', $userId);
        }

        // Filtro por ação
        if ($action = $request->input('action')) {
            $query->where('action', $action);
        }

        // Filtro por módulo
        if ($module = $request->input('module')) {
            $query->where('module', $module);
        }

        // Filtro por igreja
        if ($churchId = $request->input('church_id')) {
            // Verifica se o usuário tem acesso à igreja filtrada
            if ($currentUser->canAccessChurch((int) $churchId)) {
                $query->where('church_id', $churchId);
            }
        }

        // Filtro por período (data início)
        if ($dateStart = $request->input('date_start')) {
            $query->whereDate('created_at', '>=', $dateStart);
        }

        // Filtro por período (data fim)
        if ($dateEnd = $request->input('date_end')) {
            $query->whereDate('created_at', '<=', $dateEnd);
        }

        // Filtro por texto (descrição)
        if ($search = $request->input('search')) {
            $query->where('description', 'like', "%{$search}%");
        }

        $logs = $query->paginate(30)->withQueryString();

        // Dados para os selects de filtro
        $users = $currentUser->isRegional()
            ? User::orderBy('name')->get(['id', 'name'])
            : User::where('id', $currentUser->id)->get(['id', 'name']);

        $churches = $currentUser->accessibleChurchesQuery()->orderBy('name')->get(['id', 'name']);

        // Ações distintas já registradas (para o select de filtro)
        $actions = AuditLog::distinct()->orderBy('action')->pluck('action');
        $modules = AuditLog::distinct()->orderBy('module')->pluck('module');

        return view('audit.index', compact('logs', 'users', 'churches', 'actions', 'modules'));
    }

    /**
     * Exibe os detalhes de um registro de auditoria.
     */
    public function show(AuditLog $log): View
    {
        $currentUser = Auth::user();

        // Protege o acesso: exclusivamente para escopo REGIONAL com permissão audit.view
        if (! $currentUser->isRegional() || ! $currentUser->hasPermission('audit.view')) {
            abort(403, 'Acesso restrito exclusivamente à Secretaria Regional.');
        }

        $log->load(['user', 'church']);

        return view('audit.show', compact('log'));
    }
}
