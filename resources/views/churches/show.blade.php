@extends('layouts.app')

@section('title', 'Ficha da Congregação')
@section('header', 'Detalhes da Congregação')

@section('content')
<div class="max-w-6xl mx-auto space-y-6">

    <!-- Cabeçalho -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-slate-900/60 p-6 rounded-2xl border border-slate-800/80">
        <div class="flex items-center gap-4">
            <img src="/assets/images/logo.png" alt="{{ $church->name }}" class="w-16 h-16 rounded-2xl object-contain bg-slate-950/60 p-1.5 border border-slate-700 shadow-md">
            <div>
                <div class="flex items-center gap-2">
                    <h1 class="text-xl font-bold text-white tracking-tight">{{ $church->name }}</h1>
                    @if($church->status === 'active')
                        <span class="px-2 py-0.5 rounded-full text-[10px] font-medium bg-emerald-950 text-emerald-300 border border-emerald-500/30">Ativa</span>
                    @else
                        <span class="px-2 py-0.5 rounded-full text-[10px] font-medium bg-amber-950 text-amber-300 border border-amber-500/30">Inativa</span>
                    @endif
                </div>
                <p class="text-xs text-slate-400 mt-1">
                    Código: <span class="text-brand-400 font-semibold">{{ $church->code ?? 'Não atribuído' }}</span> • {{ $church->city }} / {{ $church->state }}
                </p>
            </div>
        </div>

        <div class="flex items-center gap-2">
            @if(auth()->check() && auth()->user()->isRegional())
            <a href="{{ route('churches.edit', $church) }}" class="px-4 py-2 bg-brand-600 hover:bg-brand-500 text-white text-xs font-semibold rounded-xl shadow-md transition-colors">
                Editar Dados
            </a>
            @endif
            <a href="{{ route('churches.index') }}" class="px-4 py-2 bg-slate-900 hover:bg-slate-800 text-slate-300 text-xs font-semibold rounded-xl border border-slate-800 transition-colors">
                Voltar à Lista
            </a>
        </div>
    </div>

    <!-- Indicadores Rápidos -->
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
        <div class="bg-slate-900/60 p-4 rounded-2xl border border-slate-800/80">
            <span class="text-[10px] font-semibold text-slate-400 uppercase tracking-wider block">Total de Membros</span>
            <span class="text-2xl font-bold text-white mt-1 block">{{ $church->members_count }}</span>
        </div>
        <div class="bg-slate-900/60 p-4 rounded-2xl border border-slate-800/80">
            <span class="text-[10px] font-semibold text-emerald-400 uppercase tracking-wider block">Membros Ativos</span>
            <span class="text-2xl font-bold text-emerald-300 mt-1 block">{{ $church->active_members_count }}</span>
        </div>
        <div class="bg-slate-900/60 p-4 rounded-2xl border border-slate-800/80">
            <span class="text-[10px] font-semibold text-indigo-400 uppercase tracking-wider block">Secretários Vinculados</span>
            <span class="text-2xl font-bold text-indigo-300 mt-1 block">{{ $church->users->count() }}</span>
        </div>
        <div class="bg-slate-900/60 p-4 rounded-2xl border border-slate-800/80">
            <span class="text-[10px] font-semibold text-slate-400 uppercase tracking-wider block">Data de Fundação</span>
            <span class="text-base font-semibold text-slate-200 mt-1.5 block">{{ $church->foundation_date ? $church->foundation_date->format('d/m/Y') : 'Não informada' }}</span>
        </div>
    </div>

    <!-- Grade de Detalhes -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">

        <!-- Coluna 1: Localização e Contato -->
        <div class="bg-slate-900/60 p-6 rounded-2xl border border-slate-800/80 space-y-4">
            <h2 class="text-xs font-bold text-brand-400 uppercase tracking-wider pb-2 border-b border-slate-800">
                Endereço e Contato
            </h2>

            <div class="space-y-3 text-xs">
                <div>
                    <span class="text-slate-400 block text-[11px]">Dirigente Responsável</span>
                    <span class="font-semibold text-white">{{ $church->responsible_name ?? 'Não informado' }}</span>
                </div>
                <div>
                    <span class="text-slate-400 block text-[11px]">Endereço Completo</span>
                    <span class="text-slate-200 block">
                        {{ $church->address ?? 'S/Logradouro' }}
                        @if($church->number), Nº {{ $church->number }}@endif
                        @if($church->complement), {{ $church->complement }}@endif
                    </span>
                    <span class="text-slate-400 block text-[11px] mt-0.5">
                        {{ $church->neighborhood ? $church->neighborhood . ' • ' : '' }}{{ $church->city }}/{{ $church->state }}
                        @if($church->zip_code) — CEP {{ $church->zip_code }}@endif
                    </span>
                </div>
                <div>
                    <span class="text-slate-400 block text-[11px]">Telefone</span>
                    <span class="text-slate-200">{{ $church->phone ?? 'Não informado' }}</span>
                </div>
                <div>
                    <span class="text-slate-400 block text-[11px]">E-mail</span>
                    <span class="text-slate-200">{{ $church->email ?? 'Não informado' }}</span>
                </div>
                <div>
                    <span class="text-slate-400 block text-[11px]">CNPJ</span>
                    <span class="text-slate-200">{{ $church->cnpj ?? 'Não informado' }}</span>
                </div>
                @if($church->notes)
                <div>
                    <span class="text-slate-400 block text-[11px]">Observações</span>
                    <p class="text-slate-300 text-xs mt-1 bg-slate-950/60 p-2.5 rounded-xl border border-slate-800">{{ $church->notes }}</p>
                </div>
                @endif
            </div>
        </div>

        <!-- Coluna 2 e 3: Secretários Vinculados e Auditoria -->
        <div class="md:col-span-2 space-y-6">

            <!-- Secretários Locais Autorizados -->
            <div class="bg-slate-900/60 p-6 rounded-2xl border border-slate-800/80">
                <h2 class="text-xs font-bold text-brand-400 uppercase tracking-wider pb-2 border-b border-slate-800 mb-4">
                    Secretários com Acesso Autorizado
                </h2>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    @forelse($church->users as $secretary)
                    <div class="p-3 bg-slate-950/80 border border-slate-800 rounded-xl flex items-center gap-3">
                        @if($secretary->avatar_url)
                            <img src="{{ $secretary->avatar_url }}" alt="{{ $secretary->name }}" class="w-8 h-8 rounded-full object-cover border border-slate-700 shrink-0">
                        @else
                            <div class="w-8 h-8 rounded-full bg-slate-800 border border-slate-700 flex items-center justify-center font-bold text-xs text-brand-300 shrink-0">
                                {{ strtoupper(substr($secretary->name, 0, 2)) }}
                            </div>
                        @endif
                        <div class="overflow-hidden">
                            <span class="font-semibold text-white text-xs block truncate">{{ $secretary->name }}</span>
                            <span class="text-[10px] text-slate-400 block truncate">{{ $secretary->role?->name ?? 'Secretário' }} • {{ $secretary->email }}</span>
                        </div>
                    </div>
                    @empty
                    <p class="text-xs text-slate-500 sm:col-span-2 py-3 text-center bg-slate-950/40 rounded-xl border border-slate-800/60">
                        Nenhum secretário local especificamente vinculado. (Secretários Regionais possuem acesso amplo).
                    </p>
                    @endforelse
                </div>
            </div>

            <!-- Últimos Membros Registrados -->
            <div class="bg-slate-900/60 p-6 rounded-2xl border border-slate-800/80">
                <div class="flex items-center justify-between pb-2 border-b border-slate-800 mb-4">
                    <h2 class="text-xs font-bold text-brand-400 uppercase tracking-wider">
                        Membros Recentes
                    </h2>
                </div>

                <div class="space-y-2">
                    @forelse($recentMembers as $member)
                    <div class="p-2.5 bg-slate-950/80 border border-slate-800 rounded-xl flex items-center justify-between text-xs">
                        <span class="font-medium text-white">{{ $member->full_name }}</span>
                        <span class="text-[10px] text-slate-400">{{ $member->status }} • Entrada: {{ $member->entry_date ? $member->entry_date->format('d/m/Y') : 'S/D' }}</span>
                    </div>
                    @empty
                    <p class="text-xs text-slate-500 py-3 text-center">
                        Nenhum membro cadastrado nesta congregação ainda.
                    </p>
                    @endforelse
                </div>
            </div>

        </div>

    </div>

</div>
@endsection
