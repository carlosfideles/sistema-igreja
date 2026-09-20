@extends(request('print') ? 'reports.print_layout' : 'layouts.app')

@section('title', 'Relatório de Transferências')
@section('header', 'Transferências')
@section('report_title', 'Relatório de Movimentações e Transferências Eclesiásticas')

@section('filter_summary')
    @if(request('from_church_id')) Origem: {{ $churches->firstWhere('id', request('from_church_id'))->name ?? 'Filtrada' }} | @endif
    @if(request('to_church_id')) Destino: {{ $churches->firstWhere('id', request('to_church_id'))->name ?? 'Filtrada' }} | @endif
    Período: {{ request('date_start') ? date('d/m/Y', strtotime(request('date_start'))) : 'Início' }} até {{ request('date_end') ? date('d/m/Y', strtotime(request('date_end'))) : 'Hoje' }}
@endsection

@section('total_count', count($transfers) . ' transferência(s)')

@section('content')
<div class="space-y-6">

    @if(!request('print'))
    <!-- Cabeçalho e Ações -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-slate-900/60 p-5 rounded-2xl border border-slate-800/80">
        <div>
            <h1 class="text-xl font-bold text-white tracking-tight">Relatório de Transferências</h1>
            <p class="text-xs text-slate-400 mt-1">Histórico analítico de membros transferidos entre as congregações.</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ request()->fullUrlWithQuery(['export' => 'csv']) }}" class="px-3.5 py-2 bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-semibold rounded-xl shadow transition-colors flex items-center gap-1.5">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                <span>Exportar CSV</span>
            </a>

            <a href="{{ request()->fullUrlWithQuery(['print' => '1']) }}" target="_blank" class="px-3.5 py-2 bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-semibold rounded-xl shadow transition-colors flex items-center gap-1.5">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                </svg>
                <span>Imprimir Relatório</span>
            </a>

            <a href="{{ route('reports.index') }}" class="px-3.5 py-2 bg-slate-900 hover:bg-slate-800 text-slate-300 text-xs font-semibold rounded-xl border border-slate-800 transition-colors">
                Voltar
            </a>
        </div>
    </div>

    <!-- Barra de Filtros -->
    <form method="GET" action="{{ route('reports.transfers') }}" class="bg-slate-900/40 p-4 rounded-2xl border border-slate-800/60 space-y-3">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
            <div>
                <label class="block text-[10px] font-semibold text-slate-400 uppercase mb-1">Origem</label>
                <select name="from_church_id" class="w-full py-2 px-3 bg-slate-950/80 border border-slate-800 rounded-xl text-xs text-slate-200 focus:outline-none focus:ring-1 focus:ring-indigo-500">
                    <option value="">Todas as Origens</option>
                    @foreach($churches as $church)
                        <option value="{{ $church->id }}" {{ request('from_church_id') == $church->id ? 'selected' : '' }}>{{ $church->name }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-[10px] font-semibold text-slate-400 uppercase mb-1">Destino</label>
                <select name="to_church_id" class="w-full py-2 px-3 bg-slate-950/80 border border-slate-800 rounded-xl text-xs text-slate-200 focus:outline-none focus:ring-1 focus:ring-indigo-500">
                    <option value="">Todos os Destinos</option>
                    @foreach($churches as $church)
                        <option value="{{ $church->id }}" {{ request('to_church_id') == $church->id ? 'selected' : '' }}>{{ $church->name }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-[10px] font-semibold text-slate-400 uppercase mb-1">Data Início</label>
                <input type="date" name="date_start" value="{{ request('date_start') }}" class="w-full py-2 px-3 bg-slate-950/80 border border-slate-800 rounded-xl text-xs text-slate-200 focus:outline-none focus:ring-1 focus:ring-indigo-500">
            </div>

            <div class="flex items-end gap-2">
                <div class="flex-1">
                    <label class="block text-[10px] font-semibold text-slate-400 uppercase mb-1">Data Fim</label>
                    <input type="date" name="date_end" value="{{ request('date_end') }}" class="w-full py-2 px-3 bg-slate-950/80 border border-slate-800 rounded-xl text-xs text-slate-200 focus:outline-none focus:ring-1 focus:ring-indigo-500">
                </div>
                <button type="submit" class="py-2 px-3 bg-slate-800 hover:bg-slate-700 text-slate-200 text-xs font-semibold rounded-xl border border-slate-700/60 transition-colors">
                    Filtrar
                </button>
            </div>
        </div>
    </form>
    @endif

    <!-- Tabela do Relatório -->
    <div class="{{ request('print') ? '' : 'bg-slate-900/60 rounded-2xl border border-slate-800/80 overflow-hidden shadow-xl' }}">
        <table class="w-full text-left {{ request('print') ? 'text-slate-900 border border-slate-300' : 'text-slate-300 text-xs' }}">
            <thead class="{{ request('print') ? 'bg-slate-100 text-slate-900 border-b border-slate-300' : 'bg-slate-950/60 text-slate-400 border-b border-slate-800' }} text-[11px] font-bold uppercase tracking-wider">
                <tr>
                    <th class="py-2.5 px-4">Protocolo</th>
                    <th class="py-2.5 px-4">Membro</th>
                    <th class="py-2.5 px-4">Origem</th>
                    <th class="py-2.5 px-4">Destino</th>
                    <th class="py-2.5 px-4">Secretário Responsável</th>
                    <th class="py-2.5 px-4">Data / Hora</th>
                    <th class="py-2.5 px-4">Motivo</th>
                </tr>
            </thead>
            <tbody class="divide-y {{ request('print') ? 'divide-slate-200' : 'divide-slate-800/60' }}">
                @forelse($transfers as $transfer)
                <tr class="{{ request('print') ? 'bg-white' : 'hover:bg-slate-800/30' }}">
                    <td class="py-2.5 px-4 font-mono text-[11px] font-bold {{ request('print') ? 'text-slate-900' : 'text-brand-400' }}">
                        TRF-{{ str_pad($transfer->id, 6, '0', STR_PAD_LEFT) }}
                    </td>
                    <td class="py-2.5 px-4 font-semibold {{ request('print') ? 'text-slate-900' : 'text-white' }}">
                        {{ $transfer->member->full_name }}
                    </td>
                    <td class="py-2.5 px-4">{{ $transfer->fromChurch->name }}</td>
                    <td class="py-2.5 px-4 font-bold {{ request('print') ? 'text-slate-900' : 'text-emerald-300' }}">{{ $transfer->toChurch->name }}</td>
                    <td class="py-2.5 px-4">{{ $transfer->transferredBy->name ?? 'Secretaria' }}</td>
                    <td class="py-2.5 px-4">{{ $transfer->transferred_at->format('d/m/Y H:i') }}</td>
                    <td class="py-2.5 px-4 text-slate-400 text-[11px]">{{ $transfer->reason }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="py-8 text-center text-slate-500">
                        Nenhuma transferência registrada no período selecionado.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

</div>
@endsection
