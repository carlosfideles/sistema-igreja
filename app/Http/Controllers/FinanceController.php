<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Church;
use App\Models\ChurchRemittanceExemption;
use App\Models\FinancialTransaction;
use App\Models\Member;
use App\Models\RegionalRemittance;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class FinanceController extends Controller
{
    // =========================================================
    // EXTRATO FINANCEIRO
    // =========================================================

    /**
     * Extrato financeiro com filtros de mês/ano e congregação.
     * Isolamento automático por escopo do usuário.
     */
    public function index(Request $request): View
    {
        $this->authorize('viewAny', FinancialTransaction::class);

        $currentUser = Auth::user();
        $accessibleChurchIds = $currentUser->accessibleChurchesQuery()->pluck('churches.id');

        // Filtros de período (padrão: mês atual)
        $year  = (int) $request->input('year',  now()->year);
        $month = (int) $request->input('month', now()->month);

        $query = FinancialTransaction::with(['member', 'church', 'createdBy'])
            ->whereIn('church_id', $accessibleChurchIds)
            ->forMonth($year, $month);

        // Filtro por congregação específica
        if ($churchId = $request->input('church_id')) {
            if ($currentUser->canAccessChurch((int) $churchId)) {
                $query->where('church_id', $churchId);
            }
        }

        // Filtro por tipo (entry/exit)
        if ($type = $request->input('type')) {
            $query->where('type', $type);
        }

        // Filtro por categoria
        if ($category = $request->input('category')) {
            $query->where('category', $category);
        }

        $transactions = $query->orderBy('transaction_date', 'desc')
            ->orderBy('created_at', 'desc')
            ->paginate(20)
            ->withQueryString();

        // Totalizadores do período filtrado (respeitando church_id se selecionado)
        $totalsQuery = FinancialTransaction::whereIn('church_id', $accessibleChurchIds)
            ->forMonth($year, $month);

        if ($churchId = $request->input('church_id')) {
            if ($currentUser->canAccessChurch((int) $churchId)) {
                $totalsQuery->where('church_id', $churchId);
            }
        }

        $totalEntries = (clone $totalsQuery)->where('type', 'entry')->sum('amount');
        $totalExits   = (clone $totalsQuery)->where('type', 'exit')->sum('amount');
        $balance      = $totalEntries - $totalExits;
        $remittance10 = round($totalEntries * 0.10, 2);

        // Lista de igrejas acessíveis para o filtro
        $churches = $currentUser->accessibleChurchesQuery()
            ->where('status', 'active')
            ->orderBy('name', 'asc')
            ->get();

        // Lista de anos disponíveis para o filtro (3 anos para trás)
        $years = range(now()->year, now()->year - 3);

        return view('finance.index', compact(
            'transactions',
            'churches',
            'years',
            'year',
            'month',
            'totalEntries',
            'totalExits',
            'balance',
            'remittance10'
        ));
    }

    // =========================================================
    // LANÇAMENTO — CRIAR
    // =========================================================

    /**
     * Formulário de novo lançamento financeiro.
     */
    public function create(Request $request): View
    {
        $this->authorize('create', FinancialTransaction::class);

        $currentUser = Auth::user();

        $churches = $currentUser->accessibleChurchesQuery()
            ->where('status', 'active')
            ->orderBy('name', 'asc')
            ->get();

        // Pré-seleciona a congregação se houver apenas uma disponível
        $preselectedChurchId = $request->input('church_id')
            ?? ($churches->count() === 1 ? $churches->first()->id : null);

        // Membros ativos da congregação pré-selecionada (carregado via AJAX ou inicial)
        $members = collect();
        if ($preselectedChurchId && $currentUser->canAccessChurch((int) $preselectedChurchId)) {
            $members = Member::where('church_id', $preselectedChurchId)
                ->where('status', 'ativo')
                ->orderBy('full_name', 'asc')
                ->get();
        }

        return view('finance.create', compact('churches', 'members', 'preselectedChurchId'));
    }

    /**
     * Salva novo lançamento com validação condicional por categoria.
     */
    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', FinancialTransaction::class);

        $currentUser = Auth::user();

        // Validação base
        $validated = $request->validate([
            'church_id'        => ['required', 'integer', 'exists:churches,id'],
            'type'             => ['required', 'in:entry,exit'],
            'category'         => ['required', 'in:dizimo,oferta,outros,despesa'],
            'amount'           => ['required', 'numeric', 'min:0.01', 'max:999999.99'],
            'description'      => ['nullable', 'string', 'max:500'],
            'member_id'        => ['nullable', 'integer', 'exists:members,id'],
            'competence_date'  => ['required', 'date'],
            'transaction_date' => ['required', 'date'],
        ]);

        // Validação de autorização de acesso à congregação
        if (!$currentUser->canAccessChurch((int) $validated['church_id'])) {
            abort(403, 'Você não tem permissão para lançar financeiro nesta congregação.');
        }

        // Validações condicionais por categoria
        $category = $validated['category'];

        if ($category === 'dizimo') {
            if (empty($validated['member_id'])) {
                return back()->withErrors(['member_id' => 'Para lançamento de Dízimo, o membro é obrigatório.'])->withInput();
            }
            // Verifica se o membro pertence à congregação
            $member = Member::find($validated['member_id']);
            if (!$member || $member->church_id != $validated['church_id']) {
                return back()->withErrors(['member_id' => 'O membro selecionado não pertence a esta congregação.'])->withInput();
            }
        }

        if ($category === 'oferta' && empty($validated['member_id']) && empty($validated['description'])) {
            return back()->withErrors(['description' => 'Para Oferta sem membro identificado, a descrição é obrigatória.'])->withInput();
        }

        if (in_array($category, ['outros', 'despesa']) && empty($validated['description'])) {
            $label = $category === 'outros' ? 'Outros' : 'Despesa';
            return back()->withErrors(['description' => "Para lançamento de {$label}, a descrição/justificativa é obrigatória."])->withInput();
        }

        $validated['created_by'] = $currentUser->id;

        $transaction = FinancialTransaction::create($validated);

        // Auditoria
        AuditLog::record(
            action: 'created',
            module: 'finance',
            description: "Registrou lançamento financeiro de {$transaction->type_label} ({$transaction->category_label}) no valor de R$ " . number_format((float) ($transaction->amount ?? 0), 2, ',', '.') . " na congregação '{$transaction->church->name}'.",
            churchId: $transaction->church_id,
            entityType: FinancialTransaction::class,
            entityId: $transaction->id,
            newValues: $transaction->only(['type', 'category', 'amount', 'description', 'member_id', 'competence_date', 'transaction_date'])
        );

        return redirect()->route('finance.index', [
            'year'  => Carbon::parse($transaction->competence_date)->year,
            'month' => Carbon::parse($transaction->competence_date)->month,
        ])->with('success', 'Lançamento registrado com sucesso!');
    }

    // =========================================================
    // LANÇAMENTO — EDITAR
    // =========================================================

    /**
     * Formulário de edição de lançamento.
     * PROTEÇÃO CONTRA IDOR via Policy.
     */
    public function edit(FinancialTransaction $transaction): View
    {
        $this->authorize('update', $transaction);

        $currentUser = Auth::user();

        $churches = $currentUser->accessibleChurchesQuery()
            ->where('status', 'active')
            ->orderBy('name', 'asc')
            ->get();

        $members = Member::where('church_id', $transaction->church_id)
            ->where('status', 'ativo')
            ->orderBy('full_name', 'asc')
            ->get();

        $transaction->load(['member', 'church', 'createdBy']);

        return view('finance.edit', compact('transaction', 'churches', 'members'));
    }

    /**
     * Atualiza lançamento financeiro com auditoria completa (old + new values).
     */
    public function update(Request $request, FinancialTransaction $transaction): RedirectResponse
    {
        $this->authorize('update', $transaction);

        $currentUser = Auth::user();

        // Captura estado ANTERIOR completo antes de qualquer alteração
        $oldValues = $transaction->only([
            'church_id', 'type', 'category', 'amount',
            'description', 'member_id', 'competence_date', 'transaction_date',
        ]);

        $validated = $request->validate([
            'type'             => ['required', 'in:entry,exit'],
            'category'         => ['required', 'in:dizimo,oferta,outros,despesa'],
            'amount'           => ['required', 'numeric', 'min:0.01', 'max:999999.99'],
            'description'      => ['nullable', 'string', 'max:500'],
            'member_id'        => ['nullable', 'integer', 'exists:members,id'],
            'competence_date'  => ['required', 'date'],
            'transaction_date' => ['required', 'date'],
        ]);

        // Validações condicionais por categoria (mesmas do store)
        $category = $validated['category'];

        if ($category === 'dizimo') {
            if (empty($validated['member_id'])) {
                return back()->withErrors(['member_id' => 'Para lançamento de Dízimo, o membro é obrigatório.'])->withInput();
            }
            $member = Member::find($validated['member_id']);
            if (!$member || $member->church_id != $transaction->church_id) {
                return back()->withErrors(['member_id' => 'O membro selecionado não pertence a esta congregação.'])->withInput();
            }
        }

        if ($category === 'oferta' && empty($validated['member_id']) && empty($validated['description'])) {
            return back()->withErrors(['description' => 'Para Oferta sem membro identificado, a descrição é obrigatória.'])->withInput();
        }

        if (in_array($category, ['outros', 'despesa']) && empty($validated['description'])) {
            $label = $category === 'outros' ? 'Outros' : 'Despesa';
            return back()->withErrors(['description' => "Para lançamento de {$label}, a descrição/justificativa é obrigatória."])->withInput();
        }

        $transaction->update($validated);

        $newValues = $transaction->only([
            'type', 'category', 'amount',
            'description', 'member_id', 'competence_date', 'transaction_date',
        ]);

        // Auditoria com OLD e NEW values — visível para o Regional
        AuditLog::record(
            action: 'updated',
            module: 'finance',
            description: "Editou lançamento financeiro (ID: {$transaction->id}) de {$transaction->type_label} ({$transaction->category_label}) na congregação '{$transaction->church->name}'.",
            churchId: $transaction->church_id,
            entityType: FinancialTransaction::class,
            entityId: $transaction->id,
            oldValues: $oldValues,
            newValues: $newValues
        );

        return redirect()->route('finance.index', [
            'year'  => Carbon::parse($transaction->competence_date)->year,
            'month' => Carbon::parse($transaction->competence_date)->month,
        ])->with('success', 'Lançamento atualizado com sucesso!');
    }

    // =========================================================
    // LANÇAMENTO — EXCLUIR
    // =========================================================

    /**
     * Exclui lançamento financeiro com auditoria completa do estado anterior.
     */
    public function destroy(FinancialTransaction $transaction): RedirectResponse
    {
        $this->authorize('delete', $transaction);

        // Captura estado COMPLETO antes de excluir (para consulta futura pelo Regional)
        $oldValues = $transaction->only([
            'church_id', 'type', 'category', 'amount',
            'description', 'member_id', 'competence_date', 'transaction_date', 'created_by',
        ]);

        $churchName   = $transaction->church->name ?? 'Congregação';
        $typeLabel    = $transaction->type_label;
        $categoryLabel = $transaction->category_label;
        $amount       = number_format((float) ($transaction->amount ?? 0), 2, ',', '.');
        $competence   = Carbon::parse($transaction->competence_date)->format('m/Y');
        $transactionId = $transaction->id;
        $churchId     = $transaction->church_id;

        $transaction->delete();

        // Auditoria com old_values completo — essencial para rastreabilidade Regional
        AuditLog::record(
            action: 'deleted',
            module: 'finance',
            description: "Excluiu lançamento financeiro (ID: {$transactionId}) de {$typeLabel} ({$categoryLabel}) no valor de R$ {$amount} referente a {$competence} da congregação '{$churchName}'.",
            churchId: $churchId,
            entityType: FinancialTransaction::class,
            entityId: $transactionId,
            oldValues: $oldValues
        );

        return redirect()->back()
            ->with('success', 'Lançamento excluído com sucesso!');
    }

    // =========================================================
    // REPASSES REGIONAIS
    // =========================================================

    /**
     * Painel de acompanhamento dos repasses regionais (apenas usuários com escopo REGIONAL).
     */
    public function remittances(Request $request): View
    {
        $this->authorize('viewRemittances', FinancialTransaction::class);

        $year  = (int) $request->input('year',  now()->year);
        $month = (int) $request->input('month', now()->month);

        $referenceMonthDate = Carbon::create($year, $month, 1)->startOfMonth();

        // Carrega todas as congregações ativas e calcula/atualiza repasses
        $churches = Church::where('status', 'active')
            ->orderBy('name', 'asc')
            ->get();

        $remittances = collect();
        $activeExemptions = collect();

        foreach ($churches as $church) {
            $remittance = RegionalRemittance::calculateOrUpdateForMonth($church->id, $year, $month);
            $remittance->setRelation('church', $church);
            $remittances->push($remittance);

            // Isenção ativa atual da congregação
            $exemption = ChurchRemittanceExemption::where('church_id', $church->id)
                ->active()
                ->latest('starts_at')
                ->first();
            $activeExemptions->put($church->id, $exemption);
        }

        // Totalizadores do mês
        $totalRemittances  = $remittances->where('status', '!=', 'exempted')->sum('remittance_amount');
        $confirmedCount    = $remittances->where('status', 'paid')->count();
        $pendingCount      = $remittances->where('status', 'pending')->count();
        $overdueCount      = $remittances->where('status', 'overdue')->count();
        $exemptedCount     = $remittances->where('status', 'exempted')->count();

        $years = range(now()->year, now()->year - 3);

        return view('finance.remittances', compact(
            'remittances',
            'activeExemptions',
            'churches',
            'year',
            'month',
            'years',
            'referenceMonthDate',
            'totalRemittances',
            'confirmedCount',
            'pendingCount',
            'overdueCount',
            'exemptedCount'
        ));
    }

    /**
     * Confirma o recebimento do repasse de 10% pelo Regional.
     * Bloqueado antes do 22º dia do mês de referência.
     */
    public function confirmRemittance(RegionalRemittance $remittance): RedirectResponse
    {
        $this->authorize('viewRemittances', FinancialTransaction::class);

        if (!$remittance->isConfirmButtonUnlocked()) {
            return back()->with('error', 'O recebimento só pode ser confirmado a partir do 22º dia do mês de referência.');
        }

        if ($remittance->status === 'paid') {
            return back()->with('error', 'Este repasse já foi confirmado anteriormente.');
        }

        if ($remittance->status === 'exempted') {
            return back()->with('error', 'Esta congregação está isenta de repasse neste mês.');
        }

        $remittance->update([
            'status'       => 'paid',
            'confirmed_by' => Auth::id(),
            'confirmed_at' => now(),
        ]);

        AuditLog::record(
            action: 'updated',
            module: 'finance',
            description: "Confirmou recebimento do repasse regional de R$ " . number_format((float) ($remittance->remittance_amount ?? 0), 2, ',', '.') . " da congregação '{$remittance->church->name}' referente a " . Carbon::parse($remittance->reference_month)->format('m/Y') . ".",
            churchId: $remittance->church_id,
            entityType: RegionalRemittance::class,
            entityId: $remittance->id,
            oldValues: ['status' => 'pending'],
            newValues: ['status' => 'paid', 'confirmed_by' => Auth::id(), 'confirmed_at' => now()->toDateTimeString()]
        );

        return back()->with('success', 'Recebimento do repasse confirmado com sucesso!');
    }

    // =========================================================
    // GESTÃO DE ISENÇÕES
    // =========================================================

    /**
     * Concede isenção de repasse (temporária ou permanente) a uma congregação.
     */
    public function grantExemption(Request $request, Church $church): RedirectResponse
    {
        $this->authorize('manageExemptions', FinancialTransaction::class);

        $validated = $request->validate([
            'type'         => ['required', 'in:temporary,permanent'],
            'months_count' => ['required_if:type,temporary', 'nullable', 'integer', 'min:1', 'max:120'],
            'starts_at'    => ['required', 'date'],
            'notes'        => ['nullable', 'string', 'max:500'],
        ]);

        $startsAt = Carbon::parse($validated['starts_at'])->startOfMonth();
        $endsAt   = null;

        if ($validated['type'] === 'temporary') {
            $endsAt = $startsAt->copy()->addMonths((int) $validated['months_count'])->subDay()->endOfMonth();
        }

        $exemption = ChurchRemittanceExemption::create([
            'church_id'    => $church->id,
            'type'         => $validated['type'],
            'months_count' => $validated['months_count'] ?? null,
            'starts_at'    => $startsAt->toDateString(),
            'ends_at'      => $endsAt?->toDateString(),
            'granted_by'   => Auth::id(),
            'notes'        => $validated['notes'] ?? null,
        ]);

        $typeLabel = $validated['type'] === 'permanent'
            ? 'permanente'
            : "temporária de {$validated['months_count']} " . ((int) $validated['months_count'] === 1 ? 'mês' : 'meses');

        AuditLog::record(
            action: 'created',
            module: 'finance',
            description: "Concedeu isenção de repasse {$typeLabel} à congregação '{$church->name}' a partir de " . $startsAt->format('m/Y') . ".",
            churchId: $church->id,
            entityType: ChurchRemittanceExemption::class,
            entityId: $exemption->id,
            newValues: ['type' => $validated['type'], 'months_count' => $validated['months_count'] ?? null, 'starts_at' => $startsAt->toDateString(), 'ends_at' => $endsAt?->toDateString()]
        );

        return back()->with('success', "Isenção {$typeLabel} concedida à congregação '{$church->name}' com sucesso!");
    }

    /**
     * Revoga uma isenção de repasse ativa.
     */
    public function revokeExemption(ChurchRemittanceExemption $exemption): RedirectResponse
    {
        $this->authorize('manageExemptions', FinancialTransaction::class);

        if ($exemption->revoked_at) {
            return back()->with('error', 'Esta isenção já foi revogada anteriormente.');
        }

        $exemption->update([
            'revoked_by' => Auth::id(),
            'revoked_at' => now(),
        ]);

        AuditLog::record(
            action: 'updated',
            module: 'finance',
            description: "Revogou isenção de repasse da congregação '{$exemption->church->name}'.",
            churchId: $exemption->church_id,
            entityType: ChurchRemittanceExemption::class,
            entityId: $exemption->id,
            oldValues: ['status' => 'active'],
            newValues: ['revoked_by' => Auth::id(), 'revoked_at' => now()->toDateTimeString()]
        );

        return back()->with('success', "Isenção da congregação '{$exemption->church->name}' revogada com sucesso!");
    }

    // =========================================================
    // RELATÓRIO DE IMPRESSÃO
    // =========================================================

    /**
     * Gera o extrato financeiro em layout limpo para impressão.
     */
    public function printReport(Request $request): View
    {
        $this->authorize('viewAny', FinancialTransaction::class);

        $currentUser = Auth::user();
        $accessibleChurchIds = $currentUser->accessibleChurchesQuery()->pluck('churches.id');

        $year  = (int) $request->input('year',  now()->year);
        $month = (int) $request->input('month', now()->month);

        $query = FinancialTransaction::with(['member', 'church', 'createdBy'])
            ->whereIn('church_id', $accessibleChurchIds)
            ->forMonth($year, $month);

        $churchId = null;
        if ($cid = $request->input('church_id')) {
            if ($currentUser->canAccessChurch((int) $cid)) {
                $query->where('church_id', $cid);
                $churchId = (int) $cid;
            }
        }

        $transactions = $query->orderBy('transaction_date', 'asc')->get();

        $totalEntries = $transactions->where('type', 'entry')->sum('amount');
        $totalExits   = $transactions->where('type', 'exit')->sum('amount');
        $balance      = $totalEntries - $totalExits;
        $remittance10 = round($totalEntries * 0.10, 2);

        $church = $churchId ? Church::find($churchId) : null;
        if (!$church && $accessibleChurchIds->count() === 1) {
            $church = Church::find($accessibleChurchIds->first());
        }

        $formattedMonth = Carbon::create($year, $month, 1)->translatedFormat('F / Y');
        $monthYear = "{$year}-" . str_pad((string)$month, 2, '0', STR_PAD_LEFT);

        $hasExemption = null;
        if ($church) {
            $hasExemption = ChurchRemittanceExemption::where('church_id', $church->id)
                ->whereNull('revoked_at')
                ->where('starts_at', '<=', Carbon::create($year, $month, 1)->toDateString())
                ->where(function ($q) use ($year, $month) {
                    $q->whereNull('ends_at')
                      ->orWhere('ends_at', '>=', Carbon::create($year, $month, 1)->toDateString());
                })
                ->first();
        }

        return view('finance.print', compact(
            'transactions',
            'year',
            'month',
            'formattedMonth',
            'monthYear',
            'totalEntries',
            'totalExits',
            'balance',
            'remittance10',
            'church',
            'hasExemption'
        ));
    }

    // =========================================================
    // AJAX — Busca de membros por congregação
    // =========================================================

    /**
     * Retorna membros ativos de uma congregação (usado pelo JS do formulário).
     */
    public function membersByChurch(Request $request, Church $church)
    {
        $currentUser = Auth::user();

        if (!$currentUser->canAccessChurch($church->id)) {
            abort(403);
        }

        $members = Member::where('church_id', $church->id)
            ->where('status', 'ativo')
            ->orderBy('full_name', 'asc')
            ->get(['id', 'full_name']);

        return response()->json($members);
    }
}
