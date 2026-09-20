<?php

namespace App\Http\Controllers;

use App\Http\Requests\Transfers\StoreTransferRequest;
use App\Models\AuditLog;
use App\Models\Church;
use App\Models\Member;
use App\Models\MemberTransfer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class TransferController extends Controller
{
    /**
     * Listagem de solicitações de transferências e inativações pendentes (Fila de Aprovação).
     */
    public function index(Request $request): View
    {
        $currentUser = Auth::user();

        // Fila de pendências: apenas registros pendentes de homologação
        $query = MemberTransfer::with(['member', 'fromChurch', 'toChurch', 'transferredBy', 'approver'])
            ->where('status', 'pending');

        // Isolamento de escopo: Usuário local só visualiza transferências que envolvem suas congregações autorizadas
        if ($currentUser->isLocal()) {
            $accessibleChurchIds = $currentUser->churches()->pluck('churches.id');
            $query->where(function ($q) use ($accessibleChurchIds) {
                $q->whereIn('from_church_id', $accessibleChurchIds)
                  ->orWhereIn('to_church_id', $accessibleChurchIds);
            });
        }

        // Filtro de Busca Textual (Nome do Membro, Motivo)
        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('reason', 'like', "%{$search}%")
                  ->orWhereHas('member', function ($m) use ($search) {
                      $m->where('full_name', 'like', "%{$search}%")
                        ->orWhere('cpf', 'like', "%{$search}%");
                  });
            });
        }

        // Filtro por Congregação de Origem
        if ($fromChurchId = $request->input('from_church_id')) {
            $query->where('from_church_id', $fromChurchId);
        }

        // Filtro por Congregação de Destino
        if ($toChurchId = $request->input('to_church_id')) {
            $query->where('to_church_id', $toChurchId);
        }

        // Filtro por Período de Data
        if ($dateStart = $request->input('date_start')) {
            $query->whereDate('transferred_at', '>=', $dateStart);
        }
        if ($dateEnd = $request->input('date_end')) {
            $query->whereDate('transferred_at', '<=', $dateEnd);
        }

        $transfers = $query->orderBy('transferred_at', 'desc')->paginate(15)->withQueryString();

        $churches = $currentUser->accessibleChurchesQuery()
            ->orderBy('name', 'asc')
            ->get();

        $totalTransfers = (clone $query)->count();

        return view('transfers.index', compact('transfers', 'churches', 'totalTransfers'));
    }

    /**
     * Formulário para iniciar a transferência de membros com busca e seleção dinâmica.
     * Suporta tanto abertura direta (/transfers/create) quanto pré-seleção (/members/{member}/transfer).
     */
    public function create(Request $request, ?Member $member = null): View
    {
        $currentUser = Auth::user();

        if (!$member || !$member->id) {
            if ($request->filled('member_id')) {
                $member = Member::with('church')->findOrFail($request->input('member_id'));
            }
        }

        if ($member && $member->id) {
            $this->authorize('transfer', $member);
            $member->load('church');

            $targetChurches = Church::where('id', '!=', $member->church_id)
                ->where('status', 'active')
                ->orderBy('name', 'asc')
                ->get();
            $availableMembers = collect();
        } else {
            // Membros ativos disponíveis para transferência com base no escopo
            if ($currentUser->isLocal()) {
                $accessibleChurchIds = $currentUser->churches()->pluck('churches.id');
                $availableMembers = Member::with('church')
                    ->whereIn('church_id', $accessibleChurchIds)
                    ->where('status', 'ativo')
                    ->orderBy('full_name', 'asc')
                    ->get();
            } else {
                $availableMembers = Member::with('church')
                    ->where('status', 'ativo')
                    ->orderBy('full_name', 'asc')
                    ->get();
            }

            $targetChurches = Church::where('status', 'active')
                ->orderBy('name', 'asc')
                ->get();
        }

        return view('transfers.create', compact('member', 'targetChurches', 'availableMembers'));
    }

    /**
     * Executa a solicitação de transferência ou inativação de forma atômica.
     * Secretário Regional: aprovação imediata e aplicação da alteração cadastral.
     * Secretário Local: solicitação pendente para posterior aprovação pelo Regional.
     */
    public function store(StoreTransferRequest $request, ?Member $member = null): RedirectResponse
    {
        if (!$member || !$member->id) {
            $member = Member::findOrFail($request->input('member_id'));
        }

        $this->authorize('transfer', $member);

        $fromChurch = $member->church;
        $type = $request->input('type', 'transfer');
        $toChurch = ($type === 'transfer' && $request->to_church_id) ? Church::findOrFail($request->to_church_id) : null;
        $currentUser = Auth::user();
        $isRegional = $currentUser->isRegional();
        $status = $isRegional ? 'approved' : 'pending';
        $approvedBy = $isRegional ? $currentUser->id : null;
        $approvedAt = $isRegional ? now() : null;

        DB::transaction(function () use ($request, $member, $fromChurch, $toChurch, $currentUser, $type, $status, $approvedBy, $approvedAt, $isRegional) {
            // 1. Gravação imutável no histórico de transferências
            $transfer = MemberTransfer::create([
                'member_id' => $member->id,
                'from_church_id' => $fromChurch->id,
                'to_church_id' => $toChurch?->id,
                'transferred_by' => $currentUser->id,
                'transferred_at' => now(),
                'reason' => $request->reason,
                'notes' => $request->notes,
                'status' => $status,
                'type' => $type,
                'approved_by' => $approvedBy,
                'approved_at' => $approvedAt,
            ]);

            // 2. Se for Regional, aplica imediatamente a alteração no cadastro do membro
            if ($isRegional) {
                if ($type === 'transfer' && $toChurch) {
                    $member->update([
                        'church_id' => $toChurch->id,
                        'status' => 'ativo',
                    ]);
                } elseif ($type === 'inactivation') {
                    $member->update([
                        'status' => 'inativo',
                    ]);
                }
            }

            // 3. Registro no log de auditoria
            $desc = $type === 'transfer'
                ? "Transferência ({$status}): Membro '{$member->full_name}' para congregação '" . ($toChurch?->name ?? 'N/A') . "' solicitada por '{$currentUser->name}'. Motivo: {$request->reason}"
                : "Inativação ({$status}): Membro '{$member->full_name}' da congregação '{$fromChurch->name}' solicitada por '{$currentUser->name}'. Motivo: {$request->reason}";

            AuditLog::record(
                action: $type === 'transfer' ? 'transfer' : 'inactivation',
                module: 'members',
                description: $desc,
                churchId: $toChurch?->id ?? $fromChurch->id,
                entityType: Member::class,
                entityId: $member->id,
                oldValues: [
                    'church_id' => $fromChurch->id,
                    'church_name' => $fromChurch->name,
                    'status' => $member->status,
                ],
                newValues: [
                    'church_id' => $toChurch?->id ?? $fromChurch->id,
                    'church_name' => $toChurch?->name ?? $fromChurch->name,
                    'type' => $type,
                    'status' => $status,
                    'transfer_id' => $transfer->id,
                ]
            );
        });

        if (!$isRegional) {
            $msg = $type === 'transfer'
                ? "Solicitação de transferência do membro '{$member->full_name}' enviada para aprovação do Secretário Regional!"
                : "Solicitação de inativação do membro '{$member->full_name}' enviada para aprovação do Secretário Regional!";
            return redirect()->route('members.show', $member)->with('success', $msg);
        }

        $msg = $type === 'transfer'
            ? "Membro '{$member->full_name}' transferido com sucesso para a congregação '{$toChurch->name}'!"
            : "Membro '{$member->full_name}' inativado com sucesso!";

        return redirect()->route('members.show', $member)->with('success', $msg);
    }

    /**
     * Aprova uma solicitação de transferência/inativação pendente (Exclusivo Regional).
     */
    public function approve(MemberTransfer $transfer): RedirectResponse
    {
        $currentUser = Auth::user();

        if (!$currentUser->isRegional()) {
            abort(403, 'Apenas usuários com perfil Regional podem aprovar transferências.');
        }

        if ($transfer->status !== 'pending') {
            return redirect()->back()->with('error', 'Esta solicitação já foi processada anteriormente.');
        }

        DB::transaction(function () use ($transfer, $currentUser) {
            $member = $transfer->member;

            if ($transfer->type === 'transfer' && $transfer->to_church_id) {
                $member->update([
                    'church_id' => $transfer->to_church_id,
                    'status' => 'ativo',
                ]);
            } elseif ($transfer->type === 'inactivation') {
                $member->update([
                    'status' => 'inativo',
                ]);
            }

            $transfer->update([
                'status' => 'approved',
                'approved_by' => $currentUser->id,
                'approved_at' => now(),
            ]);

            AuditLog::record(
                action: 'transfer_approved',
                module: 'members',
                description: "Solicitação de " . ($transfer->type === 'transfer' ? 'transferência' : 'inativação') . " aprovada pelo Secretário Regional '{$currentUser->name}' para o membro '{$member->full_name}'.",
                churchId: $transfer->to_church_id ?? $transfer->from_church_id,
                entityType: Member::class,
                entityId: $member->id,
                oldValues: ['status' => 'pending'],
                newValues: [
                    'status' => 'approved',
                    'transfer_id' => $transfer->id,
                    'approved_by' => $currentUser->id,
                ]
            );
        });

        return redirect()->back()->with('success', "Transferência/Inativação do membro '{$transfer->member->full_name}' aprovada e efetivada com sucesso!");
    }

    /**
     * Recusa uma solicitação de transferência/inativação pendente (Exclusivo Regional).
     */
    public function reject(MemberTransfer $transfer): RedirectResponse
    {
        $currentUser = Auth::user();

        if (!$currentUser->isRegional()) {
            abort(403, 'Apenas usuários com perfil Regional podem recusar transferências.');
        }

        if ($transfer->status !== 'pending') {
            return redirect()->back()->with('error', 'Esta solicitação já foi processada anteriormente.');
        }

        DB::transaction(function () use ($transfer, $currentUser) {
            $member = $transfer->member;

            $transfer->update([
                'status' => 'rejected',
                'approved_by' => $currentUser->id,
                'approved_at' => now(),
                'local_notified' => false,
            ]);

            // Registro no log de auditoria
            AuditLog::record(
                action: 'transfer_rejected',
                module: 'members',
                description: "Solicitação de " . ($transfer->type === 'transfer' ? 'transferência' : 'inativação') . " recusada pelo Secretário Regional '{$currentUser->name}' para o membro '{$member->full_name}'.",
                churchId: $transfer->from_church_id,
                entityType: Member::class,
                entityId: $member->id,
                oldValues: ['status' => 'pending'],
                newValues: [
                    'status' => 'rejected',
                    'transfer_id' => $transfer->id,
                    'approved_by' => $currentUser->id,
                ]
            );
        });

        return redirect()->back()->with('success', "Solicitação de transferência/inativação do membro '{$transfer->member->full_name}' recusada com sucesso.");
    }

    /**
     * Exibe o comprovante da transferência.
     */
    public function show(MemberTransfer $transfer): View
    {
        $currentUser = Auth::user();

        // Checagem de isolamento para secretários locais
        if ($currentUser->isLocal()) {
            $accessibleChurchIds = $currentUser->churches()->pluck('churches.id')->toArray();
            if (!in_array($transfer->from_church_id, $accessibleChurchIds) && (!in_array($transfer->to_church_id, $accessibleChurchIds))) {
                abort(403, 'Você não possui autorização para visualizar este comprovante de transferência.');
            }
        }

        $transfer->load(['member', 'fromChurch', 'toChurch', 'transferredBy', 'approver']);

        return view('transfers.show', compact('transfer'));
    }

    /**
     * Marca a notificação de resultado da transferência como lida pelo Secretário Local.
     */
    public function markNotified(MemberTransfer $transfer): JsonResponse
    {
        $currentUser = Auth::user();

        // Isolamento de escopo para secretários locais
        if ($currentUser && $currentUser->isLocal()) {
            $accessibleChurchIds = $currentUser->churches()->pluck('churches.id')->toArray();
            if ($transfer->transferred_by !== $currentUser->id && !in_array($transfer->from_church_id, $accessibleChurchIds)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Você não tem permissão para alterar esta notificação.',
                ], 403);
            }
        }

        $transfer->update([
            'local_notified' => true,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Notificação de transferência marcada como lida com sucesso.',
        ]);
    }
}
