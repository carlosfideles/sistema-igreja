@extends('layouts.app')

@section('title', 'Ficha do Secretário')
@section('header', 'Detalhes do Secretário')

@section('content')
<div class="max-w-5xl mx-auto space-y-6">

    <!-- Cabeçalho -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-slate-800/50 p-6 rounded-2xl border border-slate-700/50">
        <div class="flex items-center gap-4 text-left">
            @if($secretary->avatar_url)
                <img src="{{ $secretary->avatar_url }}" alt="{{ $secretary->name }}" class="w-16 h-16 rounded-xl object-cover shrink-0 border border-slate-600 shadow-md">
            @else
                <div class="w-16 h-16 rounded-xl bg-gradient-to-tr from-brand-700 to-brand-500 border border-slate-600 flex items-center justify-center font-bold text-xl text-white shadow-md shrink-0">
                    {{ strtoupper(substr($secretary->name, 0, 2)) }}
                </div>
            @endif
            <div class="flex flex-col text-left">
                <div class="flex items-center gap-2 flex-wrap">
                    <h2 class="text-xl font-bold text-white">{{ $secretary->name }}</h2>
                    @if($secretary->isFirstSecretaryRegional())
                        <span class="text-xs bg-amber-950 text-amber-300 border border-amber-500/40 px-2 py-0.5 rounded-full font-semibold">1º Secretário Regional</span>
                    @endif
                </div>
                <span class="text-sm text-slate-400 mt-0.5">{{ $secretary->email }} • Cadastrado em {{ $secretary->created_at->format('d/m/Y') }}</span>
            </div>
        </div>

        <div class="flex items-center gap-2 shrink-0">
            @can('update', $secretary)
            <a href="{{ route('secretaries.edit', $secretary) }}" class="px-4 py-2 bg-brand-600 hover:bg-brand-500 text-white text-xs font-semibold rounded-xl shadow-md transition-colors">
                Editar Dados
            </a>
            @endcan
            <a href="{{ route('secretaries.index') }}" class="px-4 py-2 bg-slate-900 hover:bg-slate-800 text-slate-300 text-xs font-semibold rounded-xl border border-slate-800 transition-colors">
                Voltar
            </a>
        </div>
    </div>

    <!-- Grid de Informações -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">

        <!-- Coluna 1: Dados do Perfil -->
        <div class="bg-slate-900/60 p-6 rounded-2xl border border-slate-800/80 space-y-4">
            <h2 class="text-xs font-bold text-brand-400 uppercase tracking-wider pb-2 border-b border-slate-800">
                Dados Cadastrais
            </h2>

            <div class="space-y-3 text-xs">
                <div>
                    <span class="text-slate-400 block text-[11px]">Classificação / Papel</span>
                    <span class="font-semibold text-white">{{ $secretary->role?->name ?? 'Não definido' }} (Nível {{ $secretary->role?->level ?? '-' }})</span>
                </div>
                <div>
                    <span class="text-slate-400 block text-[11px]">Nível de Abrangência</span>
                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold mt-0.5 {{ $secretary->isRegional() ? 'bg-indigo-950 text-indigo-300 border border-indigo-500/40' : 'bg-emerald-950 text-emerald-300 border border-emerald-500/40' }}">
                        {{ $secretary->scope }}
                    </span>
                </div>
                <div>
                    <span class="text-slate-400 block text-[11px]">Situação</span>
                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-medium mt-0.5 {{ $secretary->status === 'active' ? 'bg-emerald-950 text-emerald-300' : ($secretary->status === 'inactive' ? 'bg-amber-950 text-amber-300' : 'bg-red-950 text-red-300') }}">
                        {{ $secretary->status === 'active' ? 'Ativo' : ($secretary->status === 'inactive' ? 'Inativo' : 'Bloqueado') }}
                    </span>
                </div>
                <div>
                    <span class="text-slate-400 block text-[11px]">CPF</span>
                    <span class="text-slate-200">{{ $secretary->cpf ?? 'Não informado' }}</span>
                </div>
                <div>
                    <span class="text-slate-400 block text-[11px]">Telefone / WhatsApp</span>
                    <span class="text-slate-200">{{ $secretary->phone ?? 'Não informado' }}</span>
                </div>
                <div>
                    <span class="text-slate-400 block text-[11px]">Último Acesso ao Sistema</span>
                    <span class="text-slate-200">{{ $secretary->last_login_at ? $secretary->last_login_at->format('d/m/Y H:i:s') : 'Nunca acessou' }}</span>
                </div>
            </div>
        </div>

        <!-- Coluna 2 e 3: Congregações e Permissões -->
        <div class="md:col-span-2 space-y-6">

            <!-- Congregações Autorizadas -->
            <div class="bg-slate-900/60 p-6 rounded-2xl border border-slate-800/80">
                <h2 class="text-xs font-bold text-brand-400 uppercase tracking-wider pb-2 border-b border-slate-800 mb-4">
                    Congregações Autorizadas
                </h2>

                @if($secretary->isRegional())
                    <div class="p-4 rounded-xl bg-indigo-950/40 border border-indigo-500/30 text-indigo-200 text-xs flex items-center gap-3">
                        <svg class="w-5 h-5 text-indigo-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <span>Este secretário possui <strong>abrangência Regional</strong> e tem acesso automático e ilimitado a todas as congregações da Regional.</span>
                    </div>
                @else
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5">
                        @forelse($secretary->churches as $church)
                            <div class="p-3 bg-slate-950/80 border border-slate-800 rounded-xl flex items-center justify-between text-xs">
                                <div>
                                    <span class="font-semibold text-white block">{{ $church->name }}</span>
                                    <span class="text-[10px] text-slate-400">{{ $church->city }}/{{ $church->state }} • Código: {{ $church->code ?? 'S/C' }}</span>
                                </div>
                                <span class="w-2 h-2 rounded-full {{ $church->status === 'active' ? 'bg-emerald-400' : 'bg-slate-600' }}"></span>
                            </div>
                        @empty
                            <p class="text-xs text-amber-400/80 sm:col-span-2 py-3 text-center bg-amber-950/20 border border-amber-500/20 rounded-xl">
                                Nenhuma congregação vinculada. Este secretário não poderá visualizar membros ou registros.
                            </p>
                        @endforelse
                    </div>
                @endif
            </div>

            <!-- Permissões do Papel / Cargo -->
            @php
                $rolePermissions = $secretary->role?->permissions ?? collect();
            @endphp
            <div class="bg-slate-900/60 p-6 rounded-2xl border border-slate-800/80">
                <div class="flex items-center justify-between pb-2 border-b border-slate-800 mb-4">
                    <h2 class="text-xs font-bold text-brand-400 uppercase tracking-wider">
                        Permissões do Papel ({{ $secretary->role->name ?? 'Sem Papel' }})
                    </h2>
                    @if(!$secretary->isFirstSecretaryRegional())
                        <span class="text-[10px] text-slate-400 font-mono">{{ $rolePermissions->count() }} ativa(s)</span>
                    @endif
                </div>

                <div class="flex flex-wrap gap-1.5">
                    @if($secretary->isFirstSecretaryRegional())
                        <span class="px-2.5 py-1.5 bg-amber-950/60 text-amber-300 border border-amber-500/30 rounded-lg text-xs font-semibold flex items-center gap-1.5 shadow-sm">
                            <svg class="w-4 h-4 text-amber-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                            <span>Acesso Pleno a Todas as Permissões e Módulos do Sistema</span>
                        </span>
                    @else
                        @forelse($rolePermissions as $perm)
                            <span class="inline-flex items-center gap-1 px-2.5 py-1 bg-slate-950/90 text-slate-200 border border-slate-800 hover:border-slate-700 rounded-lg text-[11px] font-medium transition-colors shadow-sm" title="{{ $perm->description }}">
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-400"></span>
                                <span>{{ $perm->name }}</span>
                                <span class="text-slate-500 text-[9px] font-mono">({{ $perm->slug }})</span>
                            </span>
                        @empty
                            <p class="text-slate-500 text-xs py-2">Nenhuma permissão específica atribuída a este papel.</p>
                        @endforelse
                    @endif
                </div>
            </div>

        </div>

    </div>

</div>
@endsection
