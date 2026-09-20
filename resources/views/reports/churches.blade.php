@extends(request('print') ? 'reports.print_layout' : 'layouts.app')

@section('title', 'Relatório de Congregações')
@section('header', 'Relatório de Igrejas')
@section('report_title', 'Quadro Estatístico e Cadastral de Congregações')

@section('filter_summary')
    Situação: {{ strtoupper(request('status', 'TODAS')) }}
    @if(request('city')) | Cidade: {{ request('city') }} @endif
@endsection

@section('total_count', count($churches) . ' congregação(ões)')

@section('content')
<div class="space-y-6">

    @if(!request('print'))
    <!-- Cabeçalho e Ações -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-slate-900/60 p-5 rounded-2xl border border-slate-800/80">
        <div>
            <h1 class="text-xl font-bold text-white tracking-tight">Relatório de Congregações</h1>
            <p class="text-xs text-slate-400 mt-1">Quadro geral das igrejas com quantitativo de membros e corpo de secretários.</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ request()->fullUrlWithQuery(['export' => 'csv']) }}" class="px-3.5 py-2 bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-semibold rounded-xl shadow transition-colors flex items-center gap-1.5">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                <span>Exportar CSV</span>
            </a>

            <a href="{{ request()->fullUrlWithQuery(['print' => '1']) }}" target="_blank" class="px-3.5 py-2 bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-semibold rounded-xl shadow transition-colors flex items-center gap-1.5">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                </svg>
                <span>Imprimir Quadro</span>
            </a>

            <a href="{{ route('reports.index') }}" class="px-3.5 py-2 bg-slate-900 hover:bg-slate-800 text-slate-300 text-xs font-semibold rounded-xl border border-slate-800 transition-colors">
                Voltar
            </a>
        </div>
    </div>

    <!-- Barra de Filtros -->
    <form method="GET" action="{{ route('reports.churches') }}" class="bg-slate-900/40 p-4 rounded-2xl border border-slate-800/60 space-y-3">
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
            <div>
                <label class="block text-[10px] font-semibold text-slate-400 uppercase mb-1">Status</label>
                <select name="status" class="w-full py-2 px-3 bg-slate-950/80 border border-slate-800 rounded-xl text-xs text-slate-200 focus:outline-none focus:ring-1 focus:ring-emerald-500">
                    <option value="">Todas as Situações</option>
                    <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Apenas Ativas</option>
                    <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Apenas Inativas</option>
                </select>
            </div>

            <div>
                <label class="block text-[10px] font-semibold text-slate-400 uppercase mb-1">Cidade / Região Administrativa</label>
                <input type="text" name="city" value="{{ request('city') }}" placeholder="Ex: Brasília, Ceilândia, Taguatinga..."
                    class="w-full py-2 px-3 bg-slate-950/80 border border-slate-800 rounded-xl text-xs text-slate-200 focus:outline-none focus:ring-1 focus:ring-emerald-500">
            </div>

            <div class="flex items-end gap-2">
                <button type="submit" class="flex-1 py-2 px-3 bg-slate-800 hover:bg-slate-700 text-slate-200 text-xs font-semibold rounded-xl border border-slate-700/60 transition-colors">
                    Filtrar
                </button>
                <a href="{{ route('reports.churches') }}" class="py-2 px-3 bg-slate-900 hover:bg-slate-800 text-slate-400 text-xs rounded-xl border border-slate-800 transition-colors">
                    Limpar
                </a>
            </div>
        </div>
    </form>
    @endif

    <!-- Tabela do Relatório -->
    <div class="{{ request('print') ? '' : 'bg-slate-900/60 rounded-2xl border border-slate-800/80 overflow-hidden shadow-xl' }}">
        <table class="w-full text-left {{ request('print') ? 'text-slate-900 border border-slate-300' : 'text-slate-300 text-xs' }}">
            <thead class="{{ request('print') ? 'bg-slate-100 text-slate-900 border-b border-slate-300' : 'bg-slate-950/60 text-slate-400 border-b border-slate-800' }} text-[11px] font-bold uppercase tracking-wider">
                <tr>
                    <th class="py-2.5 px-4">#</th>
                    <th class="py-2.5 px-4">Congregação</th>
                    <th class="py-2.5 px-4">Código</th>
                    <th class="py-2.5 px-4">Cidade / UF</th>
                    <th class="py-2.5 px-4">Dirigente / Contato</th>
                    <th class="py-2.5 px-4 text-center">Total Membros</th>
                    <th class="py-2.5 px-4 text-center">Membros Ativos</th>
                    <th class="py-2.5 px-4 text-center">Secretários</th>
                    <th class="py-2.5 px-4">Status</th>
                </tr>
            </thead>
            <tbody class="divide-y {{ request('print') ? 'divide-slate-200' : 'divide-slate-800/60' }}">
                @forelse($churches as $index => $church)
                <tr class="{{ request('print') ? ( $index % 2 === 0 ? 'bg-white' : 'bg-slate-50' ) : 'hover:bg-slate-800/30' }}">
                    <td class="py-2.5 px-4 font-mono text-[10px] text-slate-500">{{ $index + 1 }}</td>
                    <td class="py-2.5 px-4 font-semibold {{ request('print') ? 'text-slate-900' : 'text-white' }}">
                        {{ $church->name }}
                    </td>
                    <td class="py-2.5 px-4 font-mono text-brand-400 text-[11px]">{{ $church->code ?? '—' }}</td>
                    <td class="py-2.5 px-4">{{ $church->city }}/{{ $church->state }}</td>
                    <td class="py-2.5 px-4">
                        <span class="block font-medium">{{ $church->responsible_name ?? 'Não informado' }}</span>
                        <span class="text-[10px] text-slate-500 block">{{ $church->phone ?? $church->email ?? '' }}</span>
                    </td>
                    <td class="py-2.5 px-4 text-center font-bold {{ request('print') ? 'text-slate-900' : 'text-white' }}">{{ $church->members_count }}</td>
                    <td class="py-2.5 px-4 text-center font-bold text-emerald-400">{{ $church->active_members_count }}</td>
                    <td class="py-2.5 px-4 text-center font-bold text-indigo-300">{{ $church->users_count }}</td>
                    <td class="py-2.5 px-4 uppercase font-bold text-[10px] {{ $church->status === 'active' ? (request('print') ? 'text-emerald-700' : 'text-emerald-400') : 'text-amber-500' }}">
                        {{ $church->status === 'active' ? 'Ativa' : 'Inativa' }}
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="9" class="py-8 text-center text-slate-500">
                        Nenhuma congregação encontrada.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

</div>
@endsection
