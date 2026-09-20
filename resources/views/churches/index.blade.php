@extends('layouts.app')

@section('title', 'Congregações e Igrejas')
@section('header', 'Congregações da Regional')

@section('content')
<div class="space-y-6">

    <!-- Cabeçalho do Módulo e Resumo -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-slate-900/60 p-5 rounded-2xl border border-slate-800/80">
        <div>
            <h1 class="text-xl font-bold text-white tracking-tight">Congregações e Igrejas</h1>
            <p class="text-xs text-slate-400 mt-1">Gestão cadastral, dirigentes responsáveis e controle de membros por congregação.</p>
        </div>
        @can('create', App\Models\Church::class)
        <div>
            <a href="{{ route('churches.create') }}" class="inline-flex items-center gap-2 px-4 py-2.5 bg-gradient-to-r from-brand-600 to-blue-600 hover:from-brand-500 hover:to-blue-500 text-white text-xs font-semibold rounded-xl shadow-lg shadow-brand-600/30 hover:shadow-brand-600/50 transition-all duration-200">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                <span>Nova Congregação</span>
            </a>
        </div>
        @endcan
    </div>

    <!-- Cards de Resumo -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="bg-slate-900/60 p-4 rounded-2xl border border-slate-800/80 flex items-center justify-between">
            <div>
                <span class="text-[11px] font-semibold text-slate-400 uppercase tracking-wider block">Total Acessível</span>
                <span class="text-2xl font-bold text-white mt-1 block">{{ $totalChurches }}</span>
            </div>
            <div class="w-10 h-10 rounded-xl bg-brand-500/10 text-brand-400 flex items-center justify-center">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                </svg>
            </div>
        </div>

        <div class="bg-slate-900/60 p-4 rounded-2xl border border-slate-800/80 flex items-center justify-between">
            <div>
                <span class="text-[11px] font-semibold text-emerald-400 uppercase tracking-wider block">Igrejas Ativas</span>
                <span class="text-2xl font-bold text-emerald-300 mt-1 block">{{ $activeChurches }}</span>
            </div>
            <div class="w-10 h-10 rounded-xl bg-emerald-500/10 text-emerald-400 flex items-center justify-center">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
        </div>

        <div class="bg-slate-900/60 p-4 rounded-2xl border border-slate-800/80 flex items-center justify-between">
            <div>
                <span class="text-[11px] font-semibold text-amber-400 uppercase tracking-wider block">Igrejas Inativas</span>
                <span class="text-2xl font-bold text-amber-300 mt-1 block">{{ $inactiveChurches }}</span>
            </div>
            <div class="w-10 h-10 rounded-xl bg-amber-500/10 text-amber-400 flex items-center justify-center">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                </svg>
            </div>
        </div>
    </div>

    <!-- Barra de Filtros e Busca -->
    <form method="GET" action="{{ route('churches.index') }}" class="bg-slate-900/40 p-4 rounded-2xl border border-slate-800/60 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3">
        <!-- Campo Busca -->
        <div class="lg:col-span-2 relative">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Buscar por nome, código, CNPJ ou dirigente..."
                class="w-full pl-9 pr-4 py-2 bg-slate-950/80 border border-slate-800 rounded-xl text-xs text-slate-200 placeholder-slate-500 focus:outline-none focus:ring-1 focus:ring-brand-500">
            <svg class="w-4 h-4 text-slate-500 absolute left-3 top-2.5 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
            </svg>
        </div>

        <!-- Filtro Situação -->
        <div>
            <select name="status" class="w-full py-2 px-3 bg-slate-950/80 border border-slate-800 rounded-xl text-xs text-slate-200 focus:outline-none focus:ring-1 focus:ring-brand-500">
                <option value="active" {{ request('status', 'active') === 'active' ? 'selected' : '' }}>Ativas</option>
                <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inativas</option>
                <option value="all" {{ request('status') === 'all' ? 'selected' : '' }}>Todas as Situações</option>
            </select>
        </div>

        <!-- Filtro Cidade -->
        <div>
            <select name="city" class="w-full py-2 px-3 bg-slate-950/80 border border-slate-800 rounded-xl text-xs text-slate-200 focus:outline-none focus:ring-1 focus:ring-brand-500">
                <option value="">Todas as Cidades</option>
                @foreach($cities as $city)
                    <option value="{{ $city }}" {{ request('city') === $city ? 'selected' : '' }}>{{ $city }}</option>
                @endforeach
            </select>
        </div>

        <!-- Botões -->
        <div class="flex items-center gap-2">
            <button type="submit" class="flex-1 py-2 px-3 bg-slate-800 hover:bg-slate-700 text-slate-200 text-xs font-semibold rounded-xl border border-slate-700/60 transition-colors">
                Filtrar
            </button>
            @if(request()->hasAny(['search', 'status', 'city', 'state']))
                <a href="{{ route('churches.index') }}" class="py-2 px-3 bg-slate-900 hover:bg-slate-800 text-slate-400 hover:text-slate-200 text-xs rounded-xl border border-slate-800 transition-colors">
                    Limpar
                </a>
            @endif
        </div>
    </form>

    <!-- Grid de Congregações -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
        @forelse($churches as $church)
        <div class="bg-slate-900/60 rounded-2xl border border-slate-800/80 p-5 hover:border-slate-700/80 transition-all duration-200 flex flex-col justify-between shadow-lg">
            <div>
                <!-- Topo do Card -->
                <div class="flex items-start justify-between gap-3 mb-3">
                    <div class="flex items-center gap-3">
                        <img src="/assets/images/logo.png" alt="{{ $church->name }}" class="w-10 h-10 rounded-xl object-contain bg-slate-950/60 p-1 border border-slate-700">
                        <div class="overflow-hidden">
                            <h2 class="text-sm font-bold text-white truncate" title="{{ $church->name }}">{{ $church->name }}</h2>
                            <span class="text-[11px] text-brand-400 block font-medium">{{ $church->code ?? 'S/C' }}</span>
                        </div>
                    </div>

                    @if($church->status === 'active')
                        <span class="px-2 py-0.5 rounded-full text-[10px] font-medium bg-emerald-950 text-emerald-300 border border-emerald-500/30 shrink-0">
                            Ativa
                        </span>
                    @else
                        <span class="px-2 py-0.5 rounded-full text-[10px] font-medium bg-amber-950 text-amber-300 border border-amber-500/30 shrink-0">
                            Inativa
                        </span>
                    @endif
                </div>

                <!-- Detalhes e Endereço -->
                <div class="space-y-1.5 text-xs text-slate-300 py-3 border-y border-slate-800/60">
                    <div class="flex items-center gap-2 text-slate-400">
                        <svg class="w-3.5 h-3.5 text-slate-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                        <span class="truncate">{{ $church->city }} - {{ $church->state }} @if($church->neighborhood) • {{ $church->neighborhood }} @endif</span>
                    </div>
                    @if($church->responsible_name)
                    <div class="flex items-center gap-2 text-slate-400">
                        <svg class="w-3.5 h-3.5 text-slate-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                        </svg>
                        <span class="truncate">Dirigente: <strong class="text-slate-200">{{ $church->responsible_name }}</strong></span>
                    </div>
                    @endif
                </div>

                <!-- Indicadores de Membros e Secretários -->
                <div class="grid grid-cols-2 gap-2 pt-3 text-center">
                    <div class="bg-slate-950/60 p-2 rounded-xl border border-slate-800/60">
                        <span class="text-[10px] text-slate-400 block uppercase">Membros</span>
                        <span class="text-sm font-bold text-white">{{ $church->members_count }}</span>
                    </div>
                    <div class="bg-slate-950/60 p-2 rounded-xl border border-slate-800/60">
                        <span class="text-[10px] text-slate-400 block uppercase">Secretários</span>
                        <span class="text-sm font-bold text-indigo-400">{{ $church->users_count }}</span>
                    </div>
                </div>
            </div>

            <!-- Rodapé de Ações -->
            <div class="flex items-center justify-between gap-2 pt-4 mt-3 border-t border-slate-800/60">
                <a href="{{ route('churches.show', $church) }}" class="flex-1 py-1.5 px-3 bg-slate-800 hover:bg-slate-700 text-slate-200 text-xs font-semibold rounded-xl text-center transition-colors">
                    Ver Ficha
                </a>

                @can('update', $church)
                <a href="{{ route('churches.edit', $church) }}" class="p-1.5 hover:bg-slate-800 rounded-xl text-slate-400 hover:text-brand-400 transition-colors" title="Editar">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                    </svg>
                </a>
                @endcan

                @can('delete', $church)
                <form method="POST" action="{{ route('churches.destroy', $church) }}" class="inline" onsubmit="return confirm('Tem certeza que deseja desativar esta congregação?')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="p-1.5 hover:bg-red-950/40 rounded-xl text-slate-400 hover:text-red-400 transition-colors" title="Desativar">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                        </svg>
                    </button>
                </form>
                @endcan
            </div>
        </div>
        @empty
        <div class="col-span-full py-12 text-center bg-slate-900/40 rounded-2xl border border-slate-800 text-slate-400">
            <svg class="w-10 h-10 mx-auto text-slate-600 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
            </svg>
            <p class="text-sm font-semibold text-slate-300">Nenhuma congregação encontrada.</p>
            <p class="text-xs text-slate-500 mt-1">Tente ajustar os filtros de busca ou cadastre uma nova congregação.</p>
        </div>
        @endforelse
    </div>

    <!-- Paginação -->
    @if($churches->hasPages())
        <div class="p-4 bg-slate-900/60 rounded-2xl border border-slate-800">
            {{ $churches->links() }}
        </div>
    @endif

</div>
@endsection
