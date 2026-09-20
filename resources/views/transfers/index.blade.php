@extends('layouts.app')

@section('title', 'Transferências de Membros')
@section('header', 'Transferências de Membros')

@section('content')
<div class="space-y-6">

    <!-- Cabeçalho do Módulo -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-slate-900/60 p-5 rounded-2xl border border-slate-800/80">
        <div>
            <h1 class="text-xl font-bold text-white tracking-tight">Transferências de Membros</h1>
            <p class="text-xs text-slate-400 mt-1">Fila de solicitações de transferências e inativações de membros pendentes de homologação regional.</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('transfers.create') }}" class="px-4 py-2.5 bg-brand-600 hover:bg-brand-500 text-white text-xs font-semibold rounded-xl shadow-lg shadow-brand-600/30 transition-all flex items-center gap-1.5">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                <span>Nova Transferência</span>
            </a>
        </div>
    </div>

    <!-- Barra de Filtros -->
    <form method="GET" action="{{ route('transfers.index') }}" class="bg-slate-900/40 p-4 rounded-2xl border border-slate-800/60 space-y-3">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
            <!-- Busca Geral -->
            <div class="relative">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Nome do membro ou motivo..."
                    class="w-full pl-9 pr-4 py-2 bg-slate-950/80 border border-slate-800 rounded-xl text-xs text-slate-200 placeholder-slate-500 focus:outline-none focus:ring-1 focus:ring-brand-500">
                <svg class="w-4 h-4 text-slate-500 absolute left-3 top-2.5 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
            </div>

            <!-- Congregação de Origem -->
            <div>
                <select name="from_church_id" class="w-full py-2 px-3 bg-slate-950/80 border border-slate-800 rounded-xl text-xs text-slate-200 focus:outline-none focus:ring-1 focus:ring-brand-500">
                    <option value="">Origem: Todas as Igrejas</option>
                    @foreach($churches as $church)
                        <option value="{{ $church->id }}" {{ request('from_church_id') == $church->id ? 'selected' : '' }}>{{ $church->name }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Congregação de Destino -->
            <div>
                <select name="to_church_id" class="w-full py-2 px-3 bg-slate-950/80 border border-slate-800 rounded-xl text-xs text-slate-200 focus:outline-none focus:ring-1 focus:ring-brand-500">
                    <option value="">Destino: Todas as Igrejas</option>
                    @foreach($churches as $church)
                        <option value="{{ $church->id }}" {{ request('to_church_id') == $church->id ? 'selected' : '' }}>{{ $church->name }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Botões -->
            <div class="flex items-center gap-2">
                <button type="submit" class="flex-1 py-2 px-3 bg-slate-800 hover:bg-slate-700 text-slate-200 text-xs font-semibold rounded-xl border border-slate-700/60 transition-colors">
                    Filtrar
                </button>
                @if(request()->hasAny(['search', 'from_church_id', 'to_church_id', 'date_start', 'date_end']))
                    <a href="{{ route('transfers.index') }}" class="py-2 px-3 bg-slate-900 hover:bg-slate-800 text-slate-400 hover:text-slate-200 text-xs rounded-xl border border-slate-800 transition-colors">
                        Limpar
                    </a>
                @endif
            </div>
        </div>
    </form>

    <!-- Tabela de Transferências -->
    <div class="bg-slate-900/60 rounded-2xl border border-slate-800/80 overflow-hidden shadow-xl">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-300">
                <thead class="bg-slate-950/60 text-[11px] font-semibold text-slate-400 uppercase tracking-wider border-b border-slate-800">
                    <tr>
                        <th class="py-3.5 px-5">Membro</th>
                        <th class="py-3.5 px-5">Tipo / Motivo</th>
                        <th class="py-3.5 px-5">Origem</th>
                        <th class="py-3.5 px-5">Destino / Situação</th>
                        <th class="py-3.5 px-5">Situação</th>
                        <th class="py-3.5 px-5">Secretário</th>
                        <th class="py-3.5 px-5">Data / Hora</th>
                        <th class="py-3.5 px-5 text-right">Ações</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800/60">
                    @forelse($transfers as $transfer)
                    <tr class="hover:bg-slate-800/30 transition-colors">
                        <!-- Membro -->
                        <td class="py-4 px-5">
                            <a href="{{ route('members.show', $transfer->member) }}" class="font-semibold text-white hover:text-brand-400 block transition-colors">
                                {{ $transfer->member->full_name }}
                            </a>
                            <span class="text-[10px] text-slate-400">CPF: {{ $transfer->member->cpf ?? 'Não informado' }}</span>
                        </td>

                        <!-- Tipo / Motivo -->
                        <td class="py-4 px-5">
                            @if(($transfer->type ?? 'transfer') === 'inactivation')
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-[10px] font-bold bg-rose-500/10 text-rose-400 border border-rose-500/20 mb-1">
                                    Inativação / Saída
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-[10px] font-bold bg-brand-500/10 text-brand-300 border border-brand-500/20 mb-1">
                                    Transferência
                                </span>
                            @endif
                            <span class="text-[10px] text-slate-400 block">{{ $transfer->reason }}</span>
                        </td>

                        <!-- Origem -->
                        <td class="py-4 px-5">
                            <span class="font-medium text-slate-300 block">{{ $transfer->fromChurch->name }}</span>
                            <span class="text-[10px] text-slate-500">{{ $transfer->fromChurch->city }}/{{ $transfer->fromChurch->state }}</span>
                        </td>

                        <!-- Destino -->
                        <td class="py-4 px-5">
                            @if(($transfer->type ?? 'transfer') === 'inactivation')
                                <span class="text-rose-400 font-medium block">Desligamento</span>
                                <span class="text-[10px] text-slate-500">Fora do Ministério</span>
                            @elseif($transfer->toChurch)
                                <div class="flex items-center gap-1.5">
                                    <svg class="w-4 h-4 text-emerald-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
                                    </svg>
                                    <div>
                                        <span class="font-bold text-emerald-300 block">{{ $transfer->toChurch->name }}</span>
                                        <span class="text-[10px] text-slate-500">{{ $transfer->toChurch->city }}/{{ $transfer->toChurch->state }}</span>
                                    </div>
                                </div>
                            @else
                                <span class="text-slate-500">-</span>
                            @endif
                        </td>

                        <!-- Situação / Status -->
                        <td class="py-4 px-5">
                            @if(($transfer->status ?? 'approved') === 'pending')
                                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[10px] font-bold bg-amber-500/10 text-amber-400 border border-amber-500/30">
                                    <span class="w-1.5 h-1.5 rounded-full bg-amber-400 animate-pulse"></span>
                                    Pendente (Regional)
                                </span>
                            @elseif(($transfer->status ?? 'approved') === 'approved')
                                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[10px] font-bold bg-emerald-500/10 text-emerald-400 border border-emerald-500/30">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                    </svg>
                                    Aprovada
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[10px] font-bold bg-rose-500/10 text-rose-400 border border-rose-500/30">
                                    Rejeitada
                                </span>
                            @endif
                        </td>

                        <!-- Secretário -->
                        <td class="py-4 px-5 text-slate-300">
                            <span class="font-medium block">{{ $transfer->transferredBy->name ?? 'Secretaria' }}</span>
                            @if($transfer->approvedBy)
                                <span class="text-[10px] text-slate-500">Aprov: {{ $transfer->approver->name ?? 'Regional' }}</span>
                            @endif
                        </td>

                        <!-- Data -->
                        <td class="py-4 px-5 text-slate-300">
                            {{ $transfer->transferred_at->format('d/m/Y H:i') }}
                        </td>

                        <!-- Ações -->
                        <td class="py-4 px-5 text-right">
                            <div class="flex items-center justify-end gap-2">
                                @if(auth()->user()->isRegional() && ($transfer->status ?? 'approved') === 'pending')
                                    <form method="POST" action="{{ route('transfers.approve', $transfer) }}" onsubmit="return confirm('Deseja realmente aprovar esta solicitação de transferência/inativação?');">
                                        @csrf
                                        <button type="submit" class="inline-flex items-center gap-1 px-2.5 py-1 bg-emerald-600 hover:bg-emerald-500 text-white text-[11px] font-bold rounded-lg shadow-md shadow-emerald-600/30 transition-colors">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                            </svg>
                                            <span>Aprovar</span>
                                        </button>
                                    </form>
                                    <form method="POST" action="{{ route('transfers.reject', $transfer) }}" onsubmit="return confirm('Deseja realmente RECUSAR esta solicitação de transferência/inativação?');">
                                        @csrf
                                        <button type="submit" class="inline-flex items-center gap-1 px-2.5 py-1 bg-rose-600 hover:bg-rose-500 text-white text-[11px] font-bold rounded-lg shadow-md shadow-rose-600/30 transition-colors">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                            </svg>
                                            <span>Recusar</span>
                                        </button>
                                    </form>
                                @endif
                                <a href="{{ route('transfers.show', $transfer) }}" class="inline-flex items-center gap-1 px-2.5 py-1 bg-slate-800 hover:bg-slate-700 text-slate-200 text-[11px] font-semibold rounded-lg border border-slate-700 transition-colors">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                    </svg>
                                    <span>Ver</span>
                                </a>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="py-10 text-center text-slate-500">
                            Nenhuma solicitação de transferência ou inativação pendente no momento.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($transfers->hasPages())
            <div class="p-4 border-t border-slate-800 bg-slate-950/40">
                {{ $transfers->links() }}
            </div>
        @endif
    </div>

</div>
@endsection
