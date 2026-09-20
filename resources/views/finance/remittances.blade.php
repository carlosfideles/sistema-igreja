@extends('layouts.app')

@section('title', 'Repasses Regionais')
@section('header', 'Painel de Repasses Regionais')

@section('content')
<div class="max-w-7xl mx-auto space-y-6">

    {{-- Cabeçalho --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-lg font-bold text-white">Repasses Regionais — 10%</h1>
            <p class="text-xs text-slate-400 mt-0.5">
                Referência:
                <span class="text-brand-400 font-semibold">
                    {{ \Carbon\Carbon::create($year, $month, 1)->translatedFormat('F \d\e Y') }}
                </span>
                @php $day22 = \Carbon\Carbon::create($year, $month, 22); @endphp
                @if(\Carbon\Carbon::today()->lt($day22))
                <span class="ml-2 text-amber-400 text-[11px]">
                    — Confirmações liberadas a partir de {{ $day22->format('d/m/Y') }}
                </span>
                @endif
            </p>
        </div>
        <a href="{{ route('finance.index') }}"
           class="flex items-center gap-2 px-4 py-2 bg-slate-800 hover:bg-slate-700 text-slate-300 text-xs font-semibold rounded-xl border border-slate-700 transition-colors">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 14l6-6m-5.5.5h.01m4.99 5h.01M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l3.5-2 3.5 2 3.5-2 3.5 2z"/>
            </svg>
            Extrato Financeiro
        </a>
    </div>

    {{-- Filtro de mês --}}
    <form method="GET" action="{{ route('finance.remittances') }}"
          class="bg-slate-900/60 border border-slate-800/80 rounded-2xl p-4">
        <div class="flex flex-wrap items-end gap-3">
            <div>
                <label class="block text-[11px] text-slate-400 mb-1.5 font-medium">Mês</label>
                <select name="month"
                        class="bg-slate-800 border border-slate-700 text-slate-200 text-xs rounded-xl px-3 py-2 focus:outline-none focus:ring-2 focus:ring-brand-500">
                    @foreach(range(1, 12) as $m)
                    <option value="{{ $m }}" {{ $m == $month ? 'selected' : '' }}>
                        {{ \Carbon\Carbon::create(null, $m, 1)->translatedFormat('F') }}
                    </option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-[11px] text-slate-400 mb-1.5 font-medium">Ano</label>
                <select name="year"
                        class="bg-slate-800 border border-slate-700 text-slate-200 text-xs rounded-xl px-3 py-2 focus:outline-none focus:ring-2 focus:ring-brand-500">
                    @foreach($years as $y)
                    <option value="{{ $y }}" {{ $y == $year ? 'selected' : '' }}>{{ $y }}</option>
                    @endforeach
                </select>
            </div>
            <button type="submit"
                    class="px-4 py-2 bg-brand-600 hover:bg-brand-500 text-white text-xs font-semibold rounded-xl transition-colors">
                Visualizar
            </button>
        </div>
    </form>

    {{-- Cards de Resumo --}}
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
        <div class="bg-emerald-950/50 border border-emerald-500/20 rounded-2xl p-4 text-center">
            <p class="text-[11px] font-medium text-emerald-400 uppercase tracking-wider">✅ Confirmados</p>
            <p class="text-3xl font-bold text-emerald-300 mt-2">{{ $confirmedCount }}</p>
        </div>
        <div class="bg-amber-950/50 border border-amber-500/20 rounded-2xl p-4 text-center">
            <p class="text-[11px] font-medium text-amber-400 uppercase tracking-wider">🟡 Pendentes</p>
            <p class="text-3xl font-bold text-amber-300 mt-2">{{ $pendingCount }}</p>
        </div>
        <div class="bg-red-950/50 border border-red-500/20 rounded-2xl p-4 text-center">
            <p class="text-[11px] font-medium text-red-400 uppercase tracking-wider">🔴 Em Atraso</p>
            <p class="text-3xl font-bold text-red-300 mt-2">{{ $overdueCount }}</p>
        </div>
        <div class="bg-slate-800/50 border border-slate-700/40 rounded-2xl p-4 text-center">
            <p class="text-[11px] font-medium text-slate-400 uppercase tracking-wider">⚪ Isentos</p>
            <p class="text-3xl font-bold text-slate-300 mt-2">{{ $exemptedCount }}</p>
        </div>
    </div>

    {{-- Total esperado --}}
    <div class="bg-indigo-950/40 border border-indigo-500/20 rounded-2xl p-4 flex items-center justify-between">
        <div>
            <p class="text-xs font-semibold text-indigo-300">Total de Repasses do Mês (10%)</p>
            <p class="text-[11px] text-indigo-400 mt-0.5">Soma das congregações não isentas</p>
        </div>
        <p class="text-2xl font-bold text-indigo-200">R$ {{ number_format($totalRemittances, 2, ',', '.') }}</p>
    </div>

    {{-- Tabela de Repasses --}}
    <div class="bg-slate-900/60 border border-slate-800/80 rounded-2xl overflow-hidden">
        <div class="p-5 border-b border-slate-800">
            <h2 class="text-sm font-semibold text-white">Congregações — Repasse de {{ \Carbon\Carbon::create($year, $month, 1)->translatedFormat('F/Y') }}</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-xs">
                <thead>
                    <tr class="bg-slate-950/60">
                        <th class="px-5 py-3 text-left text-[11px] font-semibold text-slate-400 uppercase tracking-wider">Congregação</th>
                        <th class="px-5 py-3 text-right text-[11px] font-semibold text-slate-400 uppercase tracking-wider">Total Entradas</th>
                        <th class="px-5 py-3 text-right text-[11px] font-semibold text-slate-400 uppercase tracking-wider">Repasse (10%)</th>
                        <th class="px-5 py-3 text-center text-[11px] font-semibold text-slate-400 uppercase tracking-wider">Status</th>
                        <th class="px-5 py-3 text-center text-[11px] font-semibold text-slate-400 uppercase tracking-wider">Ações</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800/60">
                    @foreach($remittances as $remittance)
                    @php
                        $statusDisplay = $remittance->status_display;
                        $canConfirm = $remittance->isConfirmButtonUnlocked();
                        $hasExemption = $activeExemptions->get($remittance->church_id);
                    @endphp
                    <tr class="hover:bg-slate-800/30 transition-colors">
                        <td class="px-5 py-4">
                            <span class="font-semibold text-white block">{{ $remittance->church->name }}</span>
                            @if($remittance->confirmed_at)
                            <span class="text-[10px] text-slate-500 block mt-0.5">
                                Confirmado em {{ $remittance->confirmed_at->format('d/m/Y H:i') }}
                                @if($remittance->confirmedBy) por {{ $remittance->confirmedBy->name }} @endif
                            </span>
                            @endif
                            @if($hasExemption)
                            <span class="inline-flex items-center gap-1 mt-1 px-2 py-0.5 rounded-full text-[10px] bg-slate-800 text-slate-400 border border-slate-700">
                                ⚪ Isenção: {{ $hasExemption->type_label }}
                                @if($hasExemption->ends_at)
                                    — até {{ $hasExemption->ends_at->format('m/Y') }}
                                @endif
                            </span>
                            @endif
                        </td>
                        <td class="px-5 py-4 text-right">
                            @if($remittance->status === 'exempted')
                                <span class="text-slate-500">—</span>
                            @else
                                <span class="text-emerald-400 font-semibold">
                                    R$ {{ number_format($remittance->total_entries, 2, ',', '.') }}
                                </span>
                            @endif
                        </td>
                        <td class="px-5 py-4 text-right">
                            @if($remittance->status === 'exempted')
                                <span class="text-slate-500">Isento</span>
                            @else
                                <span class="font-bold text-indigo-300">
                                    R$ {{ number_format($remittance->remittance_amount, 2, ',', '.') }}
                                </span>
                            @endif
                        </td>
                        <td class="px-5 py-4 text-center">
                            @php
                                $badgeColors = [
                                    'paid'     => 'bg-emerald-950 text-emerald-300 border-emerald-500/30',
                                    'pending'  => 'bg-amber-950 text-amber-300 border-amber-500/30',
                                    'overdue'  => 'bg-red-950 text-red-300 border-red-500/30',
                                    'exempted' => 'bg-slate-800 text-slate-400 border-slate-700',
                                ];
                            @endphp
                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[11px] font-semibold border
                                {{ $badgeColors[$remittance->status] ?? 'bg-slate-800 text-slate-400 border-slate-700' }}">
                                {{ $statusDisplay['label'] }}
                            </span>
                        </td>
                        <td class="px-5 py-4">
                            <div class="flex items-center justify-center gap-2 flex-wrap">

                                {{-- Botão Confirmar Recebimento --}}
                                @if($remittance->status !== 'paid' && $remittance->status !== 'exempted')
                                    @if($canConfirm)
                                    <form method="POST" action="{{ route('finance.remittances.confirm', $remittance) }}">
                                        @csrf
                                        <button type="submit"
                                                class="px-3 py-1.5 bg-emerald-600 hover:bg-emerald-500 text-white text-[11px] font-semibold rounded-lg transition-colors">
                                            ✅ Confirmar
                                        </button>
                                    </form>
                                    @else
                                    <div title="Disponível a partir do dia 22 do mês de referência">
                                        <button type="button" disabled
                                                class="px-3 py-1.5 bg-slate-700 text-slate-500 text-[11px] font-semibold rounded-lg cursor-not-allowed opacity-60">
                                            🔒 Confirmar
                                        </button>
                                    </div>
                                    @endif
                                @endif

                                {{-- Botão Isentar --}}
                                @if(!$hasExemption || $hasExemption->revoked_at)
                                @can('manageExemptions', App\Models\FinancialTransaction::class)
                                <button type="button"
                                        onclick="openExemptionModal({{ $remittance->church->id }}, '{{ addslashes($remittance->church->name) }}')"
                                        class="px-3 py-1.5 bg-slate-700 hover:bg-slate-600 text-slate-300 text-[11px] font-semibold rounded-lg border border-slate-600 transition-colors">
                                    ⚪ Isentar
                                </button>
                                @endcan
                                @endif

                                {{-- Botão Remover Isenção --}}
                                @if($hasExemption && !$hasExemption->revoked_at)
                                @can('manageExemptions', App\Models\FinancialTransaction::class)
                                <form method="POST" action="{{ route('finance.exemptions.revoke', $hasExemption) }}"
                                      onsubmit="return confirm('Tem certeza que deseja remover a isenção de {{ addslashes($remittance->church->name) }}? O ciclo normal de repasse será retomado.')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit"
                                            class="px-3 py-1.5 bg-red-900/60 hover:bg-red-900 text-red-300 text-[11px] font-semibold rounded-lg border border-red-800/50 transition-colors">
                                        🚫 Remover Isenção
                                    </button>
                                </form>
                                @endcan
                                @endif

                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

</div>

{{-- Modal de Isenção --}}
<div id="exemption-modal" class="fixed inset-0 z-50 hidden items-center justify-center bg-slate-950/80 backdrop-blur-sm p-4">
    <div class="bg-slate-900 border border-slate-700 rounded-2xl shadow-2xl w-full max-w-md">
        <div class="p-5 border-b border-slate-800 flex items-center justify-between">
            <div>
                <h3 class="text-sm font-bold text-white">Conceder Isenção de Repasse</h3>
                <p id="exemption-church-name" class="text-xs text-slate-400 mt-0.5"></p>
            </div>
            <button type="button" onclick="closeExemptionModal()"
                    class="text-slate-500 hover:text-slate-300 transition-colors p-1">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>
        <form id="exemption-form" method="POST" action="" class="p-5 space-y-4">
            @csrf
            <div>
                <label class="block text-xs font-semibold text-slate-300 mb-2">Tipo de Isenção <span class="text-red-400">*</span></label>
                <div class="grid grid-cols-2 gap-3">
                    <label class="cursor-pointer">
                        <input type="radio" name="type" value="temporary" id="exempt-temp" class="sr-only" checked>
                        <div id="exempt-temp-box"
                             class="text-center p-3 rounded-xl border-2 border-amber-500 bg-amber-950/30 text-amber-300 text-xs font-semibold transition-all">
                            ⏳ Temporária
                        </div>
                    </label>
                    <label class="cursor-pointer">
                        <input type="radio" name="type" value="permanent" id="exempt-perm" class="sr-only">
                        <div id="exempt-perm-box"
                             class="text-center p-3 rounded-xl border-2 border-slate-700 text-slate-400 text-xs font-semibold transition-all">
                            ♾️ Permanente
                        </div>
                    </label>
                </div>
            </div>
            <div id="months-field">
                <label for="months_count" class="block text-xs font-semibold text-slate-300 mb-1.5">
                    Quantidade de Meses <span class="text-red-400">*</span>
                </label>
                <input type="number" name="months_count" id="months_count"
                       min="1" max="120" placeholder="Ex: 3"
                       class="w-full bg-slate-800 border border-slate-700 text-slate-200 text-sm rounded-xl px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-brand-500">
                <p class="text-[11px] text-slate-500 mt-1">A isenção iniciará no 1º dia do mês informado abaixo.</p>
            </div>
            <div>
                <label for="starts_at" class="block text-xs font-semibold text-slate-300 mb-1.5">
                    Início da Isenção <span class="text-red-400">*</span>
                </label>
                <input type="month" name="starts_at" id="starts_at"
                       value="{{ now()->format('Y-m') }}"
                       class="w-full bg-slate-800 border border-slate-700 text-slate-200 text-sm rounded-xl px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-brand-500">
            </div>
            <div>
                <label for="exemption_notes" class="block text-xs font-semibold text-slate-300 mb-1.5">
                    Justificativa <span class="text-slate-500 font-normal">(opcional)</span>
                </label>
                <textarea name="notes" id="exemption_notes" rows="2"
                          placeholder="Ex: Congregação em fase de estruturação"
                          class="w-full bg-slate-800 border border-slate-700 text-slate-200 text-sm rounded-xl px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-brand-500 resize-none placeholder-slate-600"></textarea>
            </div>
            <div class="flex gap-3 pt-2 border-t border-slate-800">
                <button type="submit"
                        class="flex-1 py-2.5 bg-amber-600 hover:bg-amber-500 text-white text-sm font-semibold rounded-xl transition-colors">
                    Conceder Isenção
                </button>
                <button type="button" onclick="closeExemptionModal()"
                        class="px-6 py-2.5 bg-slate-800 hover:bg-slate-700 text-slate-300 text-sm rounded-xl border border-slate-700 transition-colors">
                    Cancelar
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function openExemptionModal(churchId, churchName) {
    document.getElementById('exemption-church-name').textContent = churchName;
    document.getElementById('exemption-form').action = '/finance/exemptions/' + churchId + '/grant';
    document.getElementById('exemption-modal').classList.remove('hidden');
    document.getElementById('exemption-modal').classList.add('flex');
}

function closeExemptionModal() {
    document.getElementById('exemption-modal').classList.add('hidden');
    document.getElementById('exemption-modal').classList.remove('flex');
}

document.addEventListener('DOMContentLoaded', function () {
    const tempRadio = document.getElementById('exempt-temp');
    const permRadio = document.getElementById('exempt-perm');
    const tempBox   = document.getElementById('exempt-temp-box');
    const permBox   = document.getElementById('exempt-perm-box');
    const monthsField = document.getElementById('months-field');
    const monthsInput = document.getElementById('months_count');

    function updateExemptionType() {
        if (tempRadio.checked) {
            tempBox.className = 'text-center p-3 rounded-xl border-2 border-amber-500 bg-amber-950/30 text-amber-300 text-xs font-semibold transition-all';
            permBox.className = 'text-center p-3 rounded-xl border-2 border-slate-700 text-slate-400 text-xs font-semibold transition-all';
            monthsField.classList.remove('hidden');
            monthsInput.setAttribute('required', 'required');
        } else {
            permBox.className = 'text-center p-3 rounded-xl border-2 border-red-500 bg-red-950/30 text-red-300 text-xs font-semibold transition-all';
            tempBox.className = 'text-center p-3 rounded-xl border-2 border-slate-700 text-slate-400 text-xs font-semibold transition-all';
            monthsField.classList.add('hidden');
            monthsInput.removeAttribute('required');
        }
    }

    tempRadio.addEventListener('change', updateExemptionType);
    permRadio.addEventListener('change', updateExemptionType);

    // Converte input[type=month] → date antes de submeter
    document.getElementById('exemption-form').addEventListener('submit', function () {
        const startsAt = document.getElementById('starts_at');
        if (startsAt.value && !startsAt.value.includes('-', 7)) {
            startsAt.value = startsAt.value + '-01';
        }
    });

    // Fecha modal clicando fora
    document.getElementById('exemption-modal').addEventListener('click', function (e) {
        if (e.target === this) closeExemptionModal();
    });
});
</script>
@endsection
