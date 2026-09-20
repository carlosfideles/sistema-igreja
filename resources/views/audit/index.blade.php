@extends('layouts.app')

@section('title', 'Auditoria do Sistema')
@section('header', 'Auditoria do Sistema')

@section('content')
<div class="space-y-6">

    {{-- Cabeçalho da Página --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h2 class="text-2xl font-bold text-white tracking-tight">Auditoria</h2>
            <p class="text-sm text-slate-400 mt-1">Registro completo de todas as ações realizadas no sistema.</p>
        </div>
        <div class="flex items-center gap-2">
            <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-amber-950/50 border border-amber-500/30 text-amber-300 text-xs font-semibold">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                </svg>
                {{ number_format($logs->total()) }} registro(s)
            </span>
        </div>
    </div>

    {{-- Painel de Filtros --}}
    <div class="bg-slate-900/70 border border-slate-800/80 rounded-2xl p-5 shadow-xl">
        <form method="GET" action="{{ route('audit.index') }}" id="audit-filter-form">
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">

                {{-- Pesquisa por texto --}}
                <div class="sm:col-span-2 lg:col-span-2">
                    <label class="block text-xs font-semibold text-slate-400 mb-1.5 uppercase tracking-wider">Pesquisar na descrição</label>
                    <div class="relative">
                        <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                        <input type="text" name="search" value="{{ request('search') }}"
                            placeholder="Buscar na descrição do evento..."
                            class="w-full pl-9 pr-4 py-2.5 bg-slate-800/60 border border-slate-700/60 rounded-xl text-sm text-slate-200 placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-brand-500/40 focus:border-brand-500/60 transition-all">
                    </div>
                </div>

                {{-- Filtro por Usuário --}}
                <div>
                    <label class="block text-xs font-semibold text-slate-400 mb-1.5 uppercase tracking-wider">Usuário</label>
                    <select name="user_id" class="w-full px-3 py-2.5 bg-slate-800/60 border border-slate-700/60 rounded-xl text-sm text-slate-200 focus:outline-none focus:ring-2 focus:ring-brand-500/40 focus:border-brand-500/60 transition-all">
                        <option value="">Todos os usuários</option>
                        @foreach($users as $user)
                            <option value="{{ $user->id }}" {{ request('user_id') == $user->id ? 'selected' : '' }}>
                                {{ $user->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Filtro por Módulo --}}
                @php
                    $moduleLabels = [
                        'members'     => 'Membros',
                        'secretaries' => 'Secretários',
                        'churches'    => 'Congregações',
                        'transfers'   => 'Transferências',
                        'finance'     => 'Financeiro',
                        'reports'     => 'Relatórios',
                        'settings'    => 'Configurações',
                        'auth'        => 'Autenticação',
                    ];
                    $actionLabels = [
                        'login'              => 'Login',
                        'logout'             => 'Logout',
                        'created'            => 'Criação',
                        'updated'            => 'Edição / Atualização',
                        'deleted'            => 'Exclusão / Desativação',
                        'status_changed'     => 'Alteração de Status',
                        'status_change'      => 'Alteração de Status',
                        'transferred'        => 'Transferência',
                        'report_generated'   => 'Relatório Gerado',
                        'password_changed'   => 'Senha Alterada',
                    ];
                @endphp
                <div>
                    <label class="block text-xs font-semibold text-slate-400 mb-1.5 uppercase tracking-wider">Módulo</label>
                    <select name="module" class="w-full px-3 py-2.5 bg-slate-800/60 border border-slate-700/60 rounded-xl text-sm text-slate-200 focus:outline-none focus:ring-2 focus:ring-brand-500/40 focus:border-brand-500/60 transition-all">
                        <option value="">Todos os módulos</option>
                        @foreach($modules as $mod)
                            <option value="{{ $mod }}" {{ request('module') == $mod ? 'selected' : '' }}>
                                {{ $moduleLabels[$mod] ?? ucfirst($mod) }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Filtro por Ação --}}
                <div>
                    <label class="block text-xs font-semibold text-slate-400 mb-1.5 uppercase tracking-wider">Tipo de Ação</label>
                    <select name="action" class="w-full px-3 py-2.5 bg-slate-800/60 border border-slate-700/60 rounded-xl text-sm text-slate-200 focus:outline-none focus:ring-2 focus:ring-brand-500/40 focus:border-brand-500/60 transition-all">
                        <option value="">Todas as ações</option>
                        @foreach($actions as $act)
                            <option value="{{ $act }}" {{ request('action') == $act ? 'selected' : '' }}>
                                {{ $actionLabels[$act] ?? str_replace('_', ' ', ucwords($act, '_')) }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Filtro por Igreja --}}
                <div>
                    <label class="block text-xs font-semibold text-slate-400 mb-1.5 uppercase tracking-wider">Congregação</label>
                    <select name="church_id" class="w-full px-3 py-2.5 bg-slate-800/60 border border-slate-700/60 rounded-xl text-sm text-slate-200 focus:outline-none focus:ring-2 focus:ring-brand-500/40 focus:border-brand-500/60 transition-all">
                        <option value="">Todas as igrejas</option>
                        @foreach($churches as $church)
                            <option value="{{ $church->id }}" {{ request('church_id') == $church->id ? 'selected' : '' }}>
                                {{ $church->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Data Início --}}
                <div>
                    <label class="block text-xs font-semibold text-slate-400 mb-1.5 uppercase tracking-wider">Data Início</label>
                    <input type="date" name="date_start" value="{{ request('date_start') }}"
                        class="w-full px-3 py-2.5 bg-slate-800/60 border border-slate-700/60 rounded-xl text-sm text-slate-200 focus:outline-none focus:ring-2 focus:ring-brand-500/40 focus:border-brand-500/60 transition-all">
                </div>

                {{-- Data Fim --}}
                <div>
                    <label class="block text-xs font-semibold text-slate-400 mb-1.5 uppercase tracking-wider">Data Fim</label>
                    <input type="date" name="date_end" value="{{ request('date_end') }}"
                        class="w-full px-3 py-2.5 bg-slate-800/60 border border-slate-700/60 rounded-xl text-sm text-slate-200 focus:outline-none focus:ring-2 focus:ring-brand-500/40 focus:border-brand-500/60 transition-all">
                </div>

                {{-- Botões --}}
                <div class="flex items-end gap-2 sm:col-span-2 lg:col-span-1">
                    <button type="submit" class="flex-1 flex items-center justify-center gap-2 px-4 py-2.5 bg-brand-600 hover:bg-brand-500 text-white text-sm font-semibold rounded-xl transition-all shadow-md shadow-brand-700/30">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2a1 1 0 01-.293.707L13 13.414V19a1 1 0 01-.553.894l-4 2A1 1 0 017 21v-7.586L3.293 6.707A1 1 0 013 6V4z"/>
                        </svg>
                        Filtrar
                    </button>
                    @if(request()->hasAny(['search','user_id','module','action','church_id','date_start','date_end']))
                    <a href="{{ route('audit.index') }}" class="flex items-center justify-center px-3.5 py-2.5 bg-slate-700/60 hover:bg-slate-700 border border-slate-600/60 text-slate-300 text-sm font-medium rounded-xl transition-all" title="Limpar filtros">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </a>
                    @endif
                </div>
            </div>
        </form>
    </div>

    {{-- Tabela de Logs --}}
    <div class="bg-slate-900/70 border border-slate-800/80 rounded-2xl shadow-xl overflow-hidden">
        @if($logs->isEmpty())
            <div class="flex flex-col items-center justify-center py-20 text-center px-4">
                <div class="w-16 h-16 rounded-2xl bg-slate-800/60 flex items-center justify-center mb-4">
                    <svg class="w-8 h-8 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                </div>
                <p class="text-slate-300 font-semibold">Nenhum registro encontrado</p>
                <p class="text-slate-500 text-sm mt-1">Tente ajustar os filtros para encontrar resultados.</p>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-slate-800/80 bg-slate-950/40">
                            <th class="px-5 py-3.5 text-left text-xs font-semibold text-slate-400 uppercase tracking-wider">Data/Hora</th>
                            <th class="px-5 py-3.5 text-left text-xs font-semibold text-slate-400 uppercase tracking-wider">Usuário</th>
                            <th class="px-5 py-3.5 text-left text-xs font-semibold text-slate-400 uppercase tracking-wider">Ação</th>
                            <th class="px-5 py-3.5 text-left text-xs font-semibold text-slate-400 uppercase tracking-wider hidden md:table-cell">Módulo</th>
                            <th class="px-5 py-3.5 text-left text-xs font-semibold text-slate-400 uppercase tracking-wider hidden lg:table-cell">Congregação</th>
                            <th class="px-5 py-3.5 text-left text-xs font-semibold text-slate-400 uppercase tracking-wider">Descrição</th>
                            <th class="px-5 py-3.5 text-right text-xs font-semibold text-slate-400 uppercase tracking-wider">Detalhes</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-800/60">
                        @foreach($logs as $log)
                        <tr class="hover:bg-slate-800/30 transition-colors group">
                            <td class="px-5 py-3.5 text-slate-400 whitespace-nowrap text-xs">
                                <span class="block font-medium text-slate-300">{{ $log->created_at->format('d/m/Y') }}</span>
                                <span class="text-slate-500">{{ $log->created_at->format('H:i:s') }}</span>
                            </td>
                            <td class="px-5 py-3.5">
                                <div class="flex items-center gap-2">
                                    <div class="w-7 h-7 rounded-full bg-gradient-to-tr from-slate-700 to-slate-600 flex items-center justify-center text-[10px] font-bold text-brand-300 shrink-0">
                                        {{ strtoupper(substr($log->user->name ?? 'S', 0, 2)) }}
                                    </div>
                                    <span class="text-slate-300 text-xs font-medium truncate max-w-[120px]">{{ $log->user->name ?? 'Sistema' }}</span>
                                </div>
                            </td>
                            <td class="px-5 py-3.5">
                                @php
                                    $actionColors = [
                                        'login'              => 'bg-emerald-950/60 text-emerald-300 border-emerald-500/30',
                                        'logout'             => 'bg-slate-800/60 text-slate-400 border-slate-600/30',
                                        'created'            => 'bg-blue-950/60 text-blue-300 border-blue-500/30',
                                        'updated'            => 'bg-amber-950/60 text-amber-300 border-amber-500/30',
                                        'deleted'            => 'bg-red-950/60 text-red-400 border-red-500/30',
                                        'status_changed'     => 'bg-purple-950/60 text-purple-300 border-purple-500/30',
                                        'transferred'        => 'bg-indigo-950/60 text-indigo-300 border-indigo-500/30',
                                        'report_generated'   => 'bg-teal-950/60 text-teal-300 border-teal-500/30',
                                        'password_changed'   => 'bg-orange-950/60 text-orange-300 border-orange-500/30',
                                    ];
                                    $colorClass = $actionColors[$log->action] ?? 'bg-slate-800/60 text-slate-400 border-slate-600/30';
                                @endphp
                                <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[11px] font-semibold border {{ $colorClass }} whitespace-nowrap">
                                    {{ $actionLabels[$log->action] ?? str_replace('_', ' ', ucwords($log->action, '_')) }}
                                </span>
                            </td>
                            <td class="px-5 py-3.5 hidden md:table-cell">
                                <span class="text-xs text-slate-400">{{ $moduleLabels[$log->module] ?? ucfirst($log->module ?? '—') }}</span>
                            </td>
                            <td class="px-5 py-3.5 hidden lg:table-cell">
                                <span class="text-xs text-slate-400 truncate max-w-[140px] block">{{ $log->church->name ?? '—' }}</span>
                            </td>
                            <td class="px-5 py-3.5">
                                <span class="text-xs text-slate-300 line-clamp-2">{{ $log->description ?? '—' }}</span>
                            </td>
                            <td class="px-5 py-3.5 text-right">
                                <a href="{{ route('audit.show', $log) }}"
                                    class="inline-flex items-center gap-1 px-2.5 py-1.5 text-xs font-medium text-brand-400 hover:text-brand-300 bg-brand-950/40 hover:bg-brand-900/40 border border-brand-500/20 rounded-lg transition-all opacity-0 group-hover:opacity-100">
                                    Ver
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                    </svg>
                                </a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- Paginação --}}
            @if($logs->hasPages())
            <div class="px-5 py-4 border-t border-slate-800/60 flex flex-col sm:flex-row items-center justify-between gap-3">
                <p class="text-xs text-slate-500">
                    Exibindo <span class="text-slate-300 font-medium">{{ $logs->firstItem() }}</span>–<span class="text-slate-300 font-medium">{{ $logs->lastItem() }}</span>
                    de <span class="text-slate-300 font-medium">{{ number_format($logs->total()) }}</span> registros
                </p>
                <div class="flex items-center gap-1">
                    {{-- Anterior --}}
                    @if($logs->onFirstPage())
                        <span class="px-3 py-1.5 text-xs text-slate-600 bg-slate-800/30 rounded-lg cursor-not-allowed">← Anterior</span>
                    @else
                        <a href="{{ $logs->previousPageUrl() }}" class="px-3 py-1.5 text-xs text-slate-400 hover:text-white bg-slate-800/60 hover:bg-slate-700/60 rounded-lg transition-all">← Anterior</a>
                    @endif

                    @foreach($logs->getUrlRange(max(1, $logs->currentPage() - 2), min($logs->lastPage(), $logs->currentPage() + 2)) as $page => $url)
                        @if($page == $logs->currentPage())
                            <span class="px-3 py-1.5 text-xs font-bold text-white bg-brand-600 rounded-lg">{{ $page }}</span>
                        @else
                            <a href="{{ $url }}" class="px-3 py-1.5 text-xs text-slate-400 hover:text-white bg-slate-800/60 hover:bg-slate-700/60 rounded-lg transition-all">{{ $page }}</a>
                        @endif
                    @endforeach

                    {{-- Próximo --}}
                    @if($logs->hasMorePages())
                        <a href="{{ $logs->nextPageUrl() }}" class="px-3 py-1.5 text-xs text-slate-400 hover:text-white bg-slate-800/60 hover:bg-slate-700/60 rounded-lg transition-all">Próximo →</a>
                    @else
                        <span class="px-3 py-1.5 text-xs text-slate-600 bg-slate-800/30 rounded-lg cursor-not-allowed">Próximo →</span>
                    @endif
                </div>
            </div>
            @endif
        @endif
    </div>
</div>
@endsection
