@extends('layouts.app')

@section('title', 'Financeiro')
@section('header', 'Controle Financeiro')

@section('content')
<div class="max-w-7xl mx-auto space-y-6">

    {{-- Cabeçalho com ações --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-lg font-bold text-white">Extrato Financeiro</h1>
            <p class="text-xs text-slate-400 mt-0.5">
                Lançamentos de
                <span class="text-brand-400 font-semibold">
                    {{ \Carbon\Carbon::create($year, $month, 1)->translatedFormat('F \d\e Y') }}
                </span>
            </p>
        </div>
        <div class="flex items-center gap-2 flex-wrap">
            @can('create', App\Models\FinancialTransaction::class)
            <a href="{{ route('finance.create') }}"
               class="flex items-center gap-2 px-4 py-2 bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-semibold rounded-xl shadow-md transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                Novo Lançamento
            </a>
            @endcan

            @can('viewRemittances', App\Models\FinancialTransaction::class)
            <a href="{{ route('finance.remittances') }}"
               class="flex items-center gap-2 px-4 py-2 bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-semibold rounded-xl shadow-md transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 14l6-6m-5.5.5h.01m4.99 5h.01M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l3.5-2 3.5 2 3.5-2 3.5 2z"/>
                </svg>
                Repasses Regionais
            </a>
            @endcan

            <a href="{{ route('finance.print', request()->only(['year','month','church_id'])) }}"
               target="_blank"
               class="flex items-center gap-2 px-4 py-2 bg-slate-700 hover:bg-slate-600 text-slate-200 text-xs font-semibold rounded-xl shadow-md transition-colors border border-slate-600">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                </svg>
                Imprimir Extrato
            </a>
        </div>
    </div>

    {{-- Filtros --}}
    <form method="GET" action="{{ route('finance.index') }}"
          class="bg-slate-900/60 border border-slate-800/80 rounded-2xl p-5">
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-3">
            <div>
                <label class="block text-[11px] text-slate-400 mb-1.5 font-medium">Mês</label>
                <select name="month" id="filter-month"
                        class="w-full bg-slate-800 border border-slate-700 text-slate-200 text-xs rounded-xl px-3 py-2 focus:outline-none focus:ring-2 focus:ring-brand-500">
                    @foreach(range(1, 12) as $m)
                    <option value="{{ $m }}" {{ $m == $month ? 'selected' : '' }}>
                        {{ \Carbon\Carbon::create(null, $m, 1)->translatedFormat('F') }}
                    </option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-[11px] text-slate-400 mb-1.5 font-medium">Ano</label>
                <select name="year" id="filter-year"
                        class="w-full bg-slate-800 border border-slate-700 text-slate-200 text-xs rounded-xl px-3 py-2 focus:outline-none focus:ring-2 focus:ring-brand-500">
                    @foreach($years as $y)
                    <option value="{{ $y }}" {{ $y == $year ? 'selected' : '' }}>{{ $y }}</option>
                    @endforeach
                </select>
            </div>
            @if($churches->count() > 1)
            <div>
                <label class="block text-[11px] text-slate-400 mb-1.5 font-medium">Congregação</label>
                <select name="church_id"
                        class="w-full bg-slate-800 border border-slate-700 text-slate-200 text-xs rounded-xl px-3 py-2 focus:outline-none focus:ring-2 focus:ring-brand-500">
                    <option value="">Todas</option>
                    @foreach($churches as $church)
                    <option value="{{ $church->id }}" {{ request('church_id') == $church->id ? 'selected' : '' }}>
                        {{ $church->name }}
                    </option>
                    @endforeach
                </select>
            </div>
            @endif
            <div>
                <label class="block text-[11px] text-slate-400 mb-1.5 font-medium">Tipo</label>
                <select name="type"
                        class="w-full bg-slate-800 border border-slate-700 text-slate-200 text-xs rounded-xl px-3 py-2 focus:outline-none focus:ring-2 focus:ring-brand-500">
                    <option value="">Todos</option>
                    <option value="entry" {{ request('type') === 'entry' ? 'selected' : '' }}>Entradas</option>
                    <option value="exit"  {{ request('type') === 'exit'  ? 'selected' : '' }}>Saídas</option>
                </select>
            </div>
            <div class="flex items-end">
                <button type="submit"
                        class="w-full px-4 py-2 bg-brand-600 hover:bg-brand-500 text-white text-xs font-semibold rounded-xl transition-colors">
                    Filtrar
                </button>
            </div>
        </div>
    </form>

    {{-- Cards de Totais --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-emerald-950/50 border border-emerald-500/20 rounded-2xl p-5">
            <p class="text-[11px] font-medium text-emerald-400 uppercase tracking-wider">Total Entradas</p>
            <p class="text-2xl font-bold text-emerald-300 mt-2">
                R$ {{ number_format($totalEntries, 2, ',', '.') }}
            </p>
        </div>
        <div class="bg-red-950/50 border border-red-500/20 rounded-2xl p-5">
            <p class="text-[11px] font-medium text-red-400 uppercase tracking-wider">Total Saídas</p>
            <p class="text-2xl font-bold text-red-300 mt-2">
                R$ {{ number_format($totalExits, 2, ',', '.') }}
            </p>
        </div>
        <div class="{{ $balance >= 0 ? 'bg-brand-950/50 border-brand-500/20' : 'bg-amber-950/50 border-amber-500/20' }} border rounded-2xl p-5">
            <p class="text-[11px] font-medium {{ $balance >= 0 ? 'text-brand-400' : 'text-amber-400' }} uppercase tracking-wider">Saldo do Mês</p>
            <p class="text-2xl font-bold {{ $balance >= 0 ? 'text-brand-300' : 'text-amber-300' }} mt-2">
                R$ {{ number_format($balance, 2, ',', '.') }}
            </p>
        </div>
        <div class="bg-indigo-950/50 border border-indigo-500/20 rounded-2xl p-5">
            <p class="text-[11px] font-medium text-indigo-400 uppercase tracking-wider">Repasse Regional (10%)</p>
            <p class="text-2xl font-bold text-indigo-300 mt-2">
                R$ {{ number_format($remittance10, 2, ',', '.') }}
            </p>
        </div>
    </div>

    {{-- Tabela de Lançamentos --}}
    <div class="bg-slate-900/60 border border-slate-800/80 rounded-2xl overflow-hidden">
        <div class="p-5 border-b border-slate-800 flex items-center justify-between">
            <h2 class="text-sm font-semibold text-white">Lançamentos do Período</h2>
            <span class="text-xs text-slate-400">{{ $transactions->total() }} registro(s)</span>
        </div>

        @if($transactions->isEmpty())
        <div class="p-12 text-center">
            <svg class="w-10 h-10 text-slate-600 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 14l6-6m-5.5.5h.01m4.99 5h.01M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l3.5-2 3.5 2 3.5-2 3.5 2z"/>
            </svg>
            <p class="text-sm text-slate-500">Nenhum lançamento encontrado para este período.</p>
            @can('create', App\Models\FinancialTransaction::class)
            <a href="{{ route('finance.create') }}" class="inline-flex items-center gap-1.5 mt-3 text-xs text-brand-400 hover:text-brand-300 transition-colors">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                Registrar primeiro lançamento
            </a>
            @endcan
        </div>
        @else
        <div class="overflow-x-auto">
            <table class="w-full text-xs">
                <thead>
                    <tr class="bg-slate-950/60">
                        <th class="px-5 py-3 text-left text-[11px] font-semibold text-slate-400 uppercase tracking-wider">Data</th>
                        <th class="px-5 py-3 text-left text-[11px] font-semibold text-slate-400 uppercase tracking-wider">Tipo / Categoria</th>
                        <th class="px-5 py-3 text-left text-[11px] font-semibold text-slate-400 uppercase tracking-wider">Descrição / Membro</th>
                        @if($churches->count() > 1)
                        <th class="px-5 py-3 text-left text-[11px] font-semibold text-slate-400 uppercase tracking-wider">Congregação</th>
                        @endif
                        <th class="px-5 py-3 text-right text-[11px] font-semibold text-slate-400 uppercase tracking-wider">Valor</th>
                        <th class="px-5 py-3 text-center text-[11px] font-semibold text-slate-400 uppercase tracking-wider">Ações</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800/60">
                    @foreach($transactions as $transaction)
                    <tr class="hover:bg-slate-800/30 transition-colors">
                        <td class="px-5 py-3.5 text-slate-300 whitespace-nowrap">
                            {{ $transaction->transaction_date->format('d/m/Y') }}
                            <span class="block text-[10px] text-slate-500">
                                Competência: {{ $transaction->competence_date->format('m/Y') }}
                            </span>
                        </td>
                        <td class="px-5 py-3.5">
                            @if($transaction->type === 'entry')
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-semibold bg-emerald-950 text-emerald-300 border border-emerald-500/30">
                                    ↑ Entrada
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-semibold bg-red-950 text-red-300 border border-red-500/30">
                                    ↓ Saída
                                </span>
                            @endif
                            <span class="block text-[11px] text-slate-400 mt-1">{{ $transaction->category_label }}</span>
                        </td>
                        <td class="px-5 py-3.5 max-w-xs">
                            @if($transaction->member)
                                <span class="font-medium text-white block truncate">{{ $transaction->member->full_name }}</span>
                            @endif
                            @if($transaction->description)
                                <span class="text-slate-400 block truncate {{ $transaction->member ? 'text-[10px] mt-0.5' : '' }}">
                                    {{ $transaction->description }}
                                </span>
                            @endif
                            @if(!$transaction->member && !$transaction->description)
                                <span class="text-slate-600">—</span>
                            @endif
                        </td>
                        @if($churches->count() > 1)
                        <td class="px-5 py-3.5 text-slate-400 whitespace-nowrap">
                            {{ $transaction->church->name }}
                        </td>
                        @endif
                        <td class="px-5 py-3.5 text-right font-bold whitespace-nowrap
                            {{ $transaction->type === 'entry' ? 'text-emerald-400' : 'text-red-400' }}">
                            {{ $transaction->type === 'entry' ? '+' : '-' }}
                            R$ {{ number_format($transaction->amount, 2, ',', '.') }}
                        </td>
                        <td class="px-5 py-3.5 text-center whitespace-nowrap">
                            <div class="flex items-center justify-center gap-2">
                                @can('update', $transaction)
                                <a href="{{ route('finance.edit', $transaction) }}"
                                   class="text-brand-400 hover:text-brand-300 transition-colors p-1 rounded-lg hover:bg-brand-950"
                                   title="Editar">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                    </svg>
                                </a>
                                @endcan
                                @can('delete', $transaction)
                                <form method="POST" action="{{ route('finance.destroy', $transaction) }}"
                                      onsubmit="return confirm('Tem certeza que deseja excluir este lançamento? Esta ação não poderá ser desfeita.')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit"
                                            class="text-red-400 hover:text-red-300 transition-colors p-1 rounded-lg hover:bg-red-950"
                                            title="Excluir">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                        </svg>
                                    </button>
                                </form>
                                @endcan
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{-- Paginação --}}
        @if($transactions->hasPages())
        <div class="px-5 py-4 border-t border-slate-800">
            {{ $transactions->links() }}
        </div>
        @endif
        @endif
    </div>

</div>
@endsection
