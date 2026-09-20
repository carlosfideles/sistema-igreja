@extends(request('print') ? 'reports.print_layout' : 'layouts.app')

@section('title', 'Relatório do Corpo de Secretários')
@section('header', 'Secretários')
@section('report_title', 'Relação Oficial do Corpo de Secretários e Abrangências')

@section('filter_summary')
    Abrangência: {{ request('scope', 'TODAS') }} | Status: {{ strtoupper(request('status', 'TODOS')) }}
@endsection

@section('total_count', count($secretaries) . ' secretário(s)')

@section('content')
<div class="space-y-6">

    @if(!request('print'))
    <!-- Cabeçalho e Ações -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-slate-900/60 p-5 rounded-2xl border border-slate-800/80">
        <div>
            <h1 class="text-xl font-bold text-white tracking-tight">Relatório do Corpo de Secretários</h1>
            <p class="text-xs text-slate-400 mt-1">Quadro de usuários administrativos, funções, escopos e congregações autorizadas.</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ request()->fullUrlWithQuery(['export' => 'csv']) }}" class="px-3.5 py-2 bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-semibold rounded-xl shadow transition-colors flex items-center gap-1.5">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                <span>Exportar CSV</span>
            </a>

            <a href="{{ request()->fullUrlWithQuery(['print' => '1']) }}" target="_blank" class="px-3.5 py-2 bg-amber-600 hover:bg-amber-500 text-white text-xs font-semibold rounded-xl shadow transition-colors flex items-center gap-1.5">
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

    <!-- Barra de Filtros -->
    <form method="GET" action="{{ route('reports.secretaries') }}" class="bg-slate-900/40 p-4 rounded-2xl border border-slate-800/60 space-y-3">
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
            <div>
                <label class="block text-[10px] font-semibold text-slate-400 uppercase mb-1">Abrangência</label>
                <select name="scope" class="w-full py-2 px-3 bg-slate-950/80 border border-slate-800 rounded-xl text-xs text-slate-200 focus:outline-none focus:ring-1 focus:ring-amber-500">
                    <option value="">Todas</option>
                    <option value="REGIONAL" {{ request('scope') === 'REGIONAL' ? 'selected' : '' }}>Regional (Acesso Total)</option>
                    <option value="LOCAL" {{ request('scope') === 'LOCAL' ? 'selected' : '' }}>Local (Igrejas Específicas)</option>
                </select>
            </div>

            <div>
                <label class="block text-[10px] font-semibold text-slate-400 uppercase mb-1">Status</label>
                <select name="status" class="w-full py-2 px-3 bg-slate-950/80 border border-slate-800 rounded-xl text-xs text-slate-200 focus:outline-none focus:ring-1 focus:ring-amber-500">
                    <option value="">Todos</option>
                    <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Apenas Ativos</option>
                    <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Apenas Inativos</option>
                </select>
            </div>

            <div class="flex items-end gap-2">
                <button type="submit" class="flex-1 py-2 px-3 bg-slate-800 hover:bg-slate-700 text-slate-200 text-xs font-semibold rounded-xl border border-slate-700/60 transition-colors">
                    Filtrar
                </button>
                <a href="{{ route('reports.secretaries') }}" class="py-2 px-3 bg-slate-900 hover:bg-slate-800 text-slate-400 text-xs rounded-xl border border-slate-800 transition-colors">
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
                    <th class="py-2.5 px-4">Nome do Secretário</th>
                    <th class="py-2.5 px-4">E-mail / Contato</th>
                    <th class="py-2.5 px-4">Função</th>
                    <th class="py-2.5 px-4">Abrangência</th>
                    <th class="py-2.5 px-4">Congregações Autorizadas</th>
                    <th class="py-2.5 px-4">Status</th>
                </tr>
            </thead>
            <tbody class="divide-y {{ request('print') ? 'divide-slate-200' : 'divide-slate-800/60' }}">
                @forelse($secretaries as $index => $secretary)
                <tr class="{{ request('print') ? ( $index % 2 === 0 ? 'bg-white' : 'bg-slate-50' ) : 'hover:bg-slate-800/30' }}">
                    <td class="py-2.5 px-4 font-mono text-[10px] text-slate-500">{{ $index + 1 }}</td>
                    <td class="py-2.5 px-4 font-semibold {{ request('print') ? 'text-slate-900' : 'text-white' }}">
                        {{ $secretary->name }}
                        @if($secretary->isFirstSecretaryRegional())
                            <span class="text-[9px] font-bold text-amber-500 block">1º Secretário Regional</span>
                        @endif
                    </td>
                    <td class="py-2.5 px-4">
                        <span class="block">{{ $secretary->email }}</span>
                        <span class="text-[10px] text-slate-500">{{ $secretary->phone ?? '' }}</span>
                    </td>
                    <td class="py-2.5 px-4">{{ $secretary->role->name ?? 'Secretário' }}</td>
                    <td class="py-2.5 px-4">
                        <span class="font-bold text-[10px] {{ $secretary->isRegional() ? 'text-indigo-400' : 'text-emerald-400' }}">
                            {{ $secretary->scope }}
                        </span>
                    </td>
                    <td class="py-2.5 px-4 text-xs">
                        @if($secretary->isRegional())
                            <span class="font-semibold text-indigo-400">Todas as Igrejas (Regional)</span>
                        @else
                            {{ $secretary->churches->pluck('name')->implode(', ') ?: 'Nenhuma vinculada' }}
                        @endif
                    </td>
                    <td class="py-2.5 px-4 uppercase font-bold text-[10px] {{ $secretary->status === 'active' ? (request('print') ? 'text-emerald-700' : 'text-emerald-400') : 'text-red-500' }}">
                        {{ $secretary->status === 'active' ? 'Ativo' : 'Inativo' }}
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="py-8 text-center text-slate-500">
                        Nenhum secretário encontrado.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

</div>
@endsection
