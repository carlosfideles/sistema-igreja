@php
    $monthNames = [1 => 'Janeiro', 2 => 'Fevereiro', 3 => 'Março', 4 => 'Abril', 5 => 'Maio', 6 => 'Junho', 7 => 'Julho', 8 => 'Agosto', 9 => 'Setembro', 10 => 'Outubro', 11 => 'Novembro', 12 => 'Dezembro'];
@endphp

@extends(request('print') ? 'reports.print_layout' : 'layouts.app')

@section('title', 'Relatório de Aniversariantes')
@section('header', 'Aniversariantes')
@section('report_title', 'Relatório Mensal de Aniversariantes — Mês de ' . $monthNames[$month])

@section('filter_summary')
    Mês de Referência: {{ $monthNames[$month] }} |
    Congregação: {{ request('church_id') ? ($churches->firstWhere('id', request('church_id'))->name ?? 'Filtrada') : 'Todas as Igrejas Autorizadas' }} |
    Situação: {{ strtoupper(request('status', 'ativo')) }}
@endsection

@section('total_count', count($members) . ' aniversariante(s)')

@section('content')
<div class="space-y-6">

    @if(!request('print'))
    <!-- Cabeçalho e Ações -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-slate-900/60 p-5 rounded-2xl border border-slate-800/80">
        <div>
            <h1 class="text-xl font-bold text-white tracking-tight">Aniversariantes — Mês de {{ $monthNames[$month] }}</h1>
            <p class="text-xs text-slate-400 mt-1">Relação de membros aniversariantes para ações pastorais e envio de felicitações.</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ request()->fullUrlWithQuery(['export' => 'csv']) }}" class="px-3.5 py-2 bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-semibold rounded-xl shadow transition-colors flex items-center gap-1.5">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                <span>Exportar CSV</span>
            </a>

            <a href="{{ request()->fullUrlWithQuery(['print' => '1']) }}" target="_blank" class="px-3.5 py-2 bg-purple-600 hover:bg-purple-500 text-white text-xs font-semibold rounded-xl shadow transition-colors flex items-center gap-1.5">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                </svg>
                <span>Imprimir Lista</span>
            </a>

            <a href="{{ route('reports.index') }}" class="px-3.5 py-2 bg-slate-900 hover:bg-slate-800 text-slate-300 text-xs font-semibold rounded-xl border border-slate-800 transition-colors">
                Voltar
            </a>
        </div>
    </div>

    <!-- Seletor Rápido de Meses (Tabs) -->
    <div class="flex items-center gap-1.5 overflow-x-auto pb-2">
        @foreach($monthNames as $mNum => $mLabel)
            <a href="{{ route('reports.birthdays', array_merge(request()->except('month'), ['month' => $mNum])) }}"
               class="px-3 py-1.5 rounded-xl text-xs font-semibold whitespace-nowrap transition-all {{ $month == $mNum ? 'bg-purple-600 text-white shadow-md shadow-purple-600/30' : 'bg-slate-900/60 text-slate-400 hover:text-slate-200 border border-slate-800/80' }}">
                {{ $mLabel }}
            </a>
        @endforeach
    </div>

    <!-- Barra de Filtros -->
    <form method="GET" action="{{ route('reports.birthdays') }}" class="bg-slate-900/40 p-4 rounded-2xl border border-slate-800/60 space-y-3">
        <input type="hidden" name="month" value="{{ $month }}">
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
            <div>
                <label class="block text-[10px] font-semibold text-slate-400 uppercase mb-1">Congregação</label>
                <select name="church_id" class="w-full py-2 px-3 bg-slate-950/80 border border-slate-800 rounded-xl text-xs text-slate-200 focus:outline-none focus:ring-1 focus:ring-purple-500">
                    <option value="">Todas as Igrejas</option>
                    @foreach($churches as $church)
                        <option value="{{ $church->id }}" {{ request('church_id') == $church->id ? 'selected' : '' }}>{{ $church->name }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-[10px] font-semibold text-slate-400 uppercase mb-1">Situação</label>
                <select name="status" class="w-full py-2 px-3 bg-slate-950/80 border border-slate-800 rounded-xl text-xs text-slate-200 focus:outline-none focus:ring-1 focus:ring-purple-500">
                    <option value="ativo" {{ request('status', 'ativo') === 'ativo' ? 'selected' : '' }}>Ativos</option>
                    <option value="all" {{ request('status') === 'all' ? 'selected' : '' }}>Todas as Situações</option>
                </select>
            </div>

            <div class="flex items-end gap-2">
                <button type="submit" class="flex-1 py-2 px-3 bg-slate-800 hover:bg-slate-700 text-slate-200 text-xs font-semibold rounded-xl border border-slate-700/60 transition-colors">
                    Filtrar
                </button>
                <a href="{{ route('reports.birthdays', ['month' => $month]) }}" class="py-2 px-3 bg-slate-900 hover:bg-slate-800 text-slate-400 text-xs rounded-xl border border-slate-800 transition-colors">
                    Limpar
                </a>
            </div>
        </div>
    </form>
    @endif

    <!-- Tabela de Aniversariantes -->
    <div class="{{ request('print') ? '' : 'bg-slate-900/60 rounded-2xl border border-slate-800/80 overflow-hidden shadow-xl' }}">
        <table class="w-full text-left {{ request('print') ? 'text-slate-900 border border-slate-300' : 'text-slate-300 text-xs' }}">
            <thead class="{{ request('print') ? 'bg-slate-100 text-slate-900 border-b border-slate-300' : 'bg-slate-950/60 text-slate-400 border-b border-slate-800' }} text-[11px] font-bold uppercase tracking-wider">
                <tr>
                    <th class="py-2.5 px-4 text-center">Dia</th>
                    <th class="py-2.5 px-4">Membro</th>
                    <th class="py-2.5 px-4">Congregação</th>
                    <th class="py-2.5 px-4 text-center">Idade a Completar</th>
                    <th class="py-2.5 px-4">Contato (WhatsApp / Telefone)</th>
                    <th class="py-2.5 px-4">E-mail</th>
                </tr>
            </thead>
            <tbody class="divide-y {{ request('print') ? 'divide-slate-200' : 'divide-slate-800/60' }}">
                @forelse($members as $index => $member)
                <tr class="{{ request('print') ? ( $index % 2 === 0 ? 'bg-white' : 'bg-slate-50' ) : 'hover:bg-slate-800/30' }}">
                    <td class="py-2.5 px-4 text-center">
                        <span class="inline-flex items-center justify-center w-8 h-8 rounded-xl font-bold {{ request('print') ? 'bg-slate-100 text-slate-900 border border-slate-300' : 'bg-purple-950/60 text-purple-300 border border-purple-500/30 text-xs' }}">
                            {{ $member->birth_date->format('d') }}
                        </span>
                    </td>
                    <td class="py-2.5 px-4 font-semibold {{ request('print') ? 'text-slate-900' : 'text-white' }}">
                        {{ $member->full_name }}
                    </td>
                    <td class="py-2.5 px-4">{{ $member->church->name }}</td>
                    <td class="py-2.5 px-4 text-center font-bold {{ request('print') ? 'text-slate-900' : 'text-purple-300' }}">
                        {{ \Carbon\Carbon::parse($member->birth_date)->age }} anos
                    </td>
                    <td class="py-2.5 px-4 font-medium {{ request('print') ? 'text-slate-800' : 'text-emerald-400' }}">
                        {{ $member->whatsapp ?? $member->phone ?? '—' }}
                    </td>
                    <td class="py-2.5 px-4 text-slate-400">
                        {{ $member->email ?? '—' }}
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="py-8 text-center text-slate-500">
                        Nenhum aniversariante registrado para o mês de {{ $monthNames[$month] }}.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

</div>
@endsection
