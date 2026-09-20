@extends(request('print') ? 'reports.print_layout' : 'layouts.app')

@section('title', 'Relatório do Rol de Membros')
@section('header', 'Relatório de Membros')
@section('report_title', 'Relatório Geral do Rol de Membros')

@section('filter_summary')
    Congregação: {{ request('church_id') ? ($churches->firstWhere('id', request('church_id'))->name ?? 'Filtrada') : 'Todas as Igrejas Autorizadas' }} |
    Situação: {{ strtoupper(request('status', 'ativo')) }}
    @if(request('gender')) | Sexo: {{ request('gender') === 'M' ? 'Masculino' : 'Feminino' }} @endif
    @if(request('age_group')) | Faixa: {{ request('age_group') }} anos @endif
@endsection

@section('total_count', count($members) . ' membro(s)')

@section('content')
<div class="space-y-6">

    @if(!request('print'))
    <!-- Cabeçalho e Ações (Oculto na Impressão) -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-slate-900/60 p-5 rounded-2xl border border-slate-800/80">
        <div>
            <h1 class="text-xl font-bold text-white tracking-tight">Relatório do Rol de Membros</h1>
            <p class="text-xs text-slate-400 mt-1">Aplique os filtros desejados e visualize, imprima ou exporte a listagem.</p>
        </div>
        <div class="flex items-center gap-2">
            <!-- Botão Exportar CSV -->
            <a href="{{ request()->fullUrlWithQuery(['export' => 'csv']) }}" class="px-3.5 py-2 bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-semibold rounded-xl shadow transition-colors flex items-center gap-1.5">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                <span>Exportar CSV</span>
            </a>

            <!-- Botão Imprimir Oficial -->
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
    <form method="GET" action="{{ route('reports.members') }}" class="bg-slate-900/40 p-4 rounded-2xl border border-slate-800/60 space-y-3">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3">
            <div>
                <label class="block text-[10px] font-semibold text-slate-400 uppercase mb-1">Congregação</label>
                <select name="church_id" class="w-full py-2 px-3 bg-slate-950/80 border border-slate-800 rounded-xl text-xs text-slate-200 focus:outline-none focus:ring-1 focus:ring-brand-500">
                    <option value="">Todas as Igrejas</option>
                    @foreach($churches as $church)
                        <option value="{{ $church->id }}" {{ request('church_id') == $church->id ? 'selected' : '' }}>{{ $church->name }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-[10px] font-semibold text-slate-400 uppercase mb-1">Situação</label>
                <select name="status" class="w-full py-2 px-3 bg-slate-950/80 border border-slate-800 rounded-xl text-xs text-slate-200 focus:outline-none focus:ring-1 focus:ring-brand-500">
                    <option value="ativo" {{ request('status', 'ativo') === 'ativo' ? 'selected' : '' }}>Ativos</option>
                    <option value="inativo" {{ request('status') === 'inativo' ? 'selected' : '' }}>Inativos</option>
                    <option value="transferido" {{ request('status') === 'transferido' ? 'selected' : '' }}>Transferidos</option>
                    <option value="disciplina" {{ request('status') === 'disciplina' ? 'selected' : '' }}>Em Disciplina</option>
                    <option value="falecido" {{ request('status') === 'falecido' ? 'selected' : '' }}>Falecidos</option>
                    <option value="all" {{ request('status') === 'all' ? 'selected' : '' }}>Todas as Situações</option>
                </select>
            </div>

            <div>
                <label class="block text-[10px] font-semibold text-slate-400 uppercase mb-1">Sexo</label>
                <select name="gender" class="w-full py-2 px-3 bg-slate-950/80 border border-slate-800 rounded-xl text-xs text-slate-200 focus:outline-none focus:ring-1 focus:ring-brand-500">
                    <option value="">Todos</option>
                    <option value="M" {{ request('gender') === 'M' ? 'selected' : '' }}>Masculino</option>
                    <option value="F" {{ request('gender') === 'F' ? 'selected' : '' }}>Feminino</option>
                </select>
            </div>

            <div>
                <label class="block text-[10px] font-semibold text-slate-400 uppercase mb-1">Faixa Etária</label>
                <select name="age_group" class="w-full py-2 px-3 bg-slate-950/80 border border-slate-800 rounded-xl text-xs text-slate-200 focus:outline-none focus:ring-1 focus:ring-brand-500">
                    <option value="">Todas</option>
                    <option value="0-12" {{ request('age_group') === '0-12' ? 'selected' : '' }}>0 a 12 anos</option>
                    <option value="13-17" {{ request('age_group') === '13-17' ? 'selected' : '' }}>13 a 17 anos</option>
                    <option value="18-29" {{ request('age_group') === '18-29' ? 'selected' : '' }}>18 a 29 anos</option>
                    <option value="30-59" {{ request('age_group') === '30-59' ? 'selected' : '' }}>30 a 59 anos</option>
                    <option value="60+" {{ request('age_group') === '60+' ? 'selected' : '' }}>60+ anos</option>
                </select>
            </div>

            <div class="flex items-end gap-2">
                <button type="submit" class="flex-1 py-2 px-3 bg-slate-800 hover:bg-slate-700 text-slate-200 text-xs font-semibold rounded-xl border border-slate-700/60 transition-colors">
                    Filtrar
                </button>
                <a href="{{ route('reports.members') }}" class="py-2 px-3 bg-slate-900 hover:bg-slate-800 text-slate-400 text-xs rounded-xl border border-slate-800 transition-colors">
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
                    <th class="py-2.5 px-3">#</th>
                    <th class="py-2.5 px-3">Nome do Membro</th>
                    <th class="py-2.5 px-3">Congregação</th>
                    <th class="py-2.5 px-3">Idade / Nasc.</th>
                    <th class="py-2.5 px-3">Contato</th>
                    <th class="py-2.5 px-3">Entrada</th>
                    <th class="py-2.5 px-3">Situação</th>
                </tr>
            </thead>
            <tbody class="divide-y {{ request('print') ? 'divide-slate-200' : 'divide-slate-800/60' }}">
                @forelse($members as $index => $member)
                <tr class="{{ request('print') ? ( $index % 2 === 0 ? 'bg-white' : 'bg-slate-50' ) : 'hover:bg-slate-800/30' }}">
                    <td class="py-2.5 px-3 font-mono text-[10px] text-slate-500">{{ $index + 1 }}</td>
                    <td class="py-2.5 px-3 font-semibold {{ request('print') ? 'text-slate-900' : 'text-white' }}">
                        {{ $member->full_name }}
                        @if($member->social_name)
                            <span class="text-[10px] text-slate-500 font-normal">({{ $member->social_name }})</span>
                        @endif
                    </td>
                    <td class="py-2.5 px-3">{{ $member->church->name }}</td>
                    <td class="py-2.5 px-3">
                        @if($member->birth_date)
                            {{ \Carbon\Carbon::parse($member->birth_date)->age }} anos
                            <span class="text-[10px] text-slate-400 block">{{ $member->birth_date->format('d/m/Y') }}</span>
                        @else
                            —
                        @endif
                    </td>
                    <td class="py-2.5 px-3">
                        {{ $member->whatsapp ?? $member->phone ?? '—' }}
                    </td>
                    <td class="py-2.5 px-3">
                        {{ $member->entry_date ? $member->entry_date->format('d/m/Y') : '—' }}
                    </td>
                    <td class="py-2.5 px-3 uppercase font-bold text-[10px] {{ $member->status === 'ativo' ? (request('print') ? 'text-emerald-700' : 'text-emerald-400') : 'text-slate-500' }}">
                        {{ $member->status }}
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="py-8 text-center text-slate-500">
                        Nenhum membro localizado com os filtros selecionados.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

</div>
@endsection
