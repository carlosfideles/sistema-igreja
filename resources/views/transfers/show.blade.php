@extends('layouts.app')

@section('title', 'Comprovante de Transferência')
@section('header', 'Comprovante de Transferência')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">

    <!-- Cabeçalho -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-xl font-bold text-white tracking-tight">Comprovante de Transferência de Membro</h1>
            <p class="text-xs text-slate-400 mt-1">Registro formal e imutável de movimentação eclesiástica.</p>
        </div>
        <div class="flex items-center gap-2">
            @if(auth()->user()->isRegional() && ($transfer->status ?? 'approved') === 'pending')
                <form method="POST" action="{{ route('transfers.approve', $transfer) }}" onsubmit="return confirm('Deseja realmente aprovar esta solicitação de transferência/inativação?');">
                    @csrf
                    <button type="submit" class="px-3.5 py-2 bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-bold rounded-xl shadow-md shadow-emerald-600/30 transition-all flex items-center gap-1.5">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                        <span>Aprovar</span>
                    </button>
                </form>
                <form method="POST" action="{{ route('transfers.reject', $transfer) }}" onsubmit="return confirm('Deseja realmente RECUSAR esta solicitação de transferência/inativação?');">
                    @csrf
                    <button type="submit" class="px-3.5 py-2 bg-rose-600 hover:bg-rose-500 text-white text-xs font-bold rounded-xl shadow-md shadow-rose-600/30 transition-all flex items-center gap-1.5">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                        <span>✕ Recusar</span>
                    </button>
                </form>
            @endif
            <a href="{{ route('members.show', $transfer->member) }}" class="px-3.5 py-2 bg-slate-800 hover:bg-slate-700 text-slate-300 text-xs font-semibold rounded-xl border border-slate-700 transition-colors">
                Ver Ficha do Membro
            </a>
            <a href="{{ route('transfers.index') }}" class="px-3.5 py-2 bg-slate-900 hover:bg-slate-800 text-slate-300 text-xs font-semibold rounded-xl border border-slate-800 transition-colors">
                Voltar à Lista
            </a>
        </div>
    </div>

    <!-- Comprovante Card -->
    <div class="bg-slate-900/60 p-8 rounded-2xl border border-slate-800/80 shadow-2xl space-y-8">

        <!-- Topo com Logotipo e Protocolo -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-6 border-b border-slate-800">
            <div class="flex items-center gap-4">
                <img src="/assets/images/logo.png" alt="SISTEMA DE GESTÃO REGIONAL" class="h-12 w-auto object-contain" onerror="this.style.display='none'; document.getElementById('transfer-logo-fallback').classList.remove('hidden');">
                <div id="transfer-logo-fallback" class="hidden flex items-center justify-center w-10 h-10 rounded-xl bg-gradient-to-tr from-brand-700 to-brand-500 text-white font-bold text-sm tracking-wider">
                    DF
                </div>
                <div>
                    <h2 class="text-sm font-bold text-white uppercase tracking-wider">IEADM-DF • Secretaria Regional</h2>
                    <p class="text-xs text-slate-400">Guia de Transferência Eclesiástica</p>
                </div>
            </div>

            <div class="text-right">
                <span class="text-[10px] text-slate-500 uppercase block">Protocolo Nº</span>
                <span class="text-sm font-mono font-bold text-brand-400">TRF-{{ str_pad($transfer->id, 6, '0', STR_PAD_LEFT) }}</span>
                <span class="text-[10px] text-slate-400 block mt-0.5">{{ $transfer->transferred_at->format('d/m/Y \à\s H:i') }}</span>
            </div>
        </div>

        <!-- Identificação do Membro -->
        <div class="bg-slate-950/60 p-5 rounded-xl border border-slate-800">
            <span class="text-[10px] font-bold text-brand-400 uppercase tracking-wider block mb-2">Membro Transferido</span>
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 rounded-xl bg-slate-800 border border-slate-700 flex items-center justify-center font-bold text-sm text-brand-300 shrink-0">
                    {{ strtoupper(substr($transfer->member->full_name, 0, 2)) }}
                </div>
                <div>
                    <h3 class="text-base font-bold text-white">{{ $transfer->member->full_name }}</h3>
                    <p class="text-xs text-slate-400 mt-0.5">
                        CPF: {{ $transfer->member->cpf ?? 'Não informado' }} • Situação: <span class="text-emerald-400 font-semibold uppercase">{{ $transfer->member->status }}</span>
                    </p>
                </div>
            </div>
        </div>

        <!-- Fluxo de Origem e Destino -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Origem -->
            <div class="p-5 bg-slate-950/40 rounded-xl border border-slate-800/80">
                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block mb-2">Congregação de Origem (Anterior)</span>
                <h4 class="text-sm font-bold text-slate-200">{{ $transfer->fromChurch->name }}</h4>
                <p class="text-xs text-slate-400 mt-1">{{ $transfer->fromChurch->city }} / {{ $transfer->fromChurch->state }}</p>
                <p class="text-[11px] text-slate-500 mt-2">Dirigente: {{ $transfer->fromChurch->responsible_name ?? 'Não informado' }}</p>
            </div>

            <!-- Destino / Desligamento -->
            @if(($transfer->type ?? 'transfer') === 'inactivation')
            <div class="p-5 bg-rose-950/20 rounded-xl border border-rose-500/30">
                <span class="text-[10px] font-bold text-rose-400 uppercase tracking-wider block mb-2">Destino / Situação</span>
                <h4 class="text-sm font-bold text-rose-300">Inativação / Desligamento</h4>
                <p class="text-xs text-rose-400/80 mt-1">Saída do Ministério / Desativação</p>
                <p class="text-[11px] text-slate-400 mt-2">Membro inativado no cadastro eclesiástico</p>
            </div>
            @else
            <div class="p-5 bg-emerald-950/20 rounded-xl border border-emerald-500/30">
                <span class="text-[10px] font-bold text-emerald-400 uppercase tracking-wider block mb-2">Nova Congregação de Destino</span>
                <h4 class="text-sm font-bold text-emerald-300">{{ $transfer->toChurch->name ?? 'Não definida' }}</h4>
                <p class="text-xs text-emerald-400/80 mt-1">{{ $transfer->toChurch->city ?? '-' }} / {{ $transfer->toChurch->state ?? '-' }}</p>
                <p class="text-[11px] text-slate-400 mt-2">Dirigente: {{ $transfer->toChurch->responsible_name ?? 'Não informado' }}</p>
            </div>
            @endif
        </div>

        <!-- Justificativa, Situação e Responsáveis -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 text-xs">
            <div>
                <span class="text-slate-500 font-medium block text-[11px]">Motivo Informado:</span>
                <span class="text-slate-200 font-semibold block mt-0.5">{{ $transfer->reason }}</span>
            </div>
            <div>
                <span class="text-slate-500 font-medium block text-[11px]">Solicitado por:</span>
                <span class="text-slate-200 font-semibold block mt-0.5">{{ $transfer->transferredBy->name ?? 'Secretaria' }}</span>
            </div>
            <div>
                <span class="text-slate-500 font-medium block text-[11px]">Situação da Solicitação:</span>
                @if(($transfer->status ?? 'approved') === 'pending')
                    <span class="inline-flex items-center gap-1 px-2 py-0.5 mt-1 rounded-md text-[11px] font-bold bg-amber-500/10 text-amber-400 border border-amber-500/30">
                        Aguardando Aprovação Regional
                    </span>
                @elseif(($transfer->status ?? 'approved') === 'approved')
                    <span class="inline-flex items-center gap-1 px-2 py-0.5 mt-1 rounded-md text-[11px] font-bold bg-emerald-500/10 text-emerald-400 border border-emerald-500/30">
                        Aprovado por {{ $transfer->approver->name ?? 'Regional' }}
                    </span>
                @else
                    <span class="inline-flex items-center gap-1 px-2 py-0.5 mt-1 rounded-md text-[11px] font-bold bg-rose-500/10 text-rose-400 border border-rose-500/30">
                        Rejeitado
                    </span>
                @endif
            </div>
            @if($transfer->notes)
            <div class="sm:col-span-3">
                <span class="text-slate-500 font-medium block text-[11px]">Observações Complementares:</span>
                <p class="text-slate-300 text-xs mt-1 p-3 bg-slate-950/60 rounded-xl border border-slate-800">{{ $transfer->notes }}</p>
            </div>
            @endif
        </div>

    </div>

</div>
@endsection
