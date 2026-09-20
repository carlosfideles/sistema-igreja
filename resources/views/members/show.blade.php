@extends('layouts.app')

@section('title', 'Ficha do Membro')
@section('header', 'Ficha do Membro')

@section('content')
<div class="max-w-6xl mx-auto space-y-6">

    <!-- Cabeçalho Principal -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-slate-900/60 p-6 rounded-2xl border border-slate-800/80">
        <div class="flex items-center gap-4">
            @if($member->photo || ($member->photo_path ?? null))
                <img src="{{ $member->photo ?? $member->photo_path }}" alt="{{ $member->full_name }}" class="w-16 h-16 rounded-2xl object-cover border border-slate-700 shadow-md">
            @else
                <div class="w-16 h-16 rounded-2xl bg-gradient-to-tr from-brand-700 to-brand-500 border border-brand-400/30 flex items-center justify-center font-bold text-2xl text-white shadow-lg shadow-brand-600/30">
                    {{ strtoupper(substr($member->full_name, 0, 2)) }}
                </div>
            @endif
            <div>
                <div class="flex items-center gap-2">
                    <h1 class="text-xl font-bold text-white tracking-tight">{{ $member->full_name }}</h1>
                    @if($member->status === 'ativo')
                        <span class="px-2 py-0.5 rounded-full text-[10px] font-medium bg-emerald-950 text-emerald-300 border border-emerald-500/30">Ativo</span>
                    @elseif($member->status === 'transferido')
                        <span class="px-2 py-0.5 rounded-full text-[10px] font-medium bg-indigo-950 text-indigo-300 border border-indigo-500/30">Transferido</span>
                    @elseif($member->status === 'disciplina')
                        <span class="px-2 py-0.5 rounded-full text-[10px] font-medium bg-amber-950 text-amber-300 border border-amber-500/30">Em Disciplina</span>
                    @elseif($member->status === 'falecido')
                        <span class="px-2 py-0.5 rounded-full text-[10px] font-medium bg-slate-900 text-slate-400 border border-slate-700">Falecido</span>
                    @else
                        <span class="px-2 py-0.5 rounded-full text-[10px] font-medium bg-red-950 text-red-300 border border-red-500/30">Inativo</span>
                    @endif
                </div>
                <p class="text-xs text-slate-400 mt-1">
                    Congregação: <span class="text-brand-400 font-semibold">{{ $member->church->name }}</span> ({{ $member->church->city }}/{{ $member->church->state }})
                </p>
            </div>
        </div>

        <div class="flex items-center gap-2 flex-wrap">
            @can('transfer', $member)
            <a href="{{ route('members.transfer.create', $member) }}" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-semibold rounded-xl shadow-md transition-colors flex items-center gap-1.5">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/>
                </svg>
                <span>Transferir Membro</span>
            </a>
            @endcan

            <button type="button" onclick="openPrintModal()" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-semibold rounded-xl shadow-md transition-colors flex items-center gap-1.5 cursor-pointer">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                </svg>
                <span>Imprimir Ficha</span>
            </button>

            @can('update', $member)
            <a href="{{ route('members.edit', $member) }}" class="px-4 py-2 bg-brand-600 hover:bg-brand-500 text-white text-xs font-semibold rounded-xl shadow-md transition-colors">
                Editar Dados
            </a>
            @endcan

            <a href="{{ route('members.index') }}" class="px-4 py-2 bg-slate-900 hover:bg-slate-800 text-slate-300 text-xs font-semibold rounded-xl border border-slate-800 transition-colors">
                Voltar
            </a>
        </div>
    </div>

    <!-- Grid de Detalhes -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">

        <!-- Coluna 1: Dados Pessoais & Documentos Civis -->
        <div class="bg-slate-900/60 p-6 rounded-2xl border border-slate-800/80 space-y-4">
            <h2 class="text-xs font-bold text-brand-400 uppercase tracking-wider pb-2 border-b border-slate-800">
                Dados Pessoais e Civis
            </h2>

            <div class="space-y-3 text-xs">
                <div>
                    <span class="text-slate-400 block text-[11px]">Nome Civil Completo</span>
                    <span class="font-semibold text-white">{{ $member->full_name }}</span>
                </div>
                @if($member->social_name)
                <div>
                    <span class="text-slate-400 block text-[11px]">Nome Social / Conhecido por</span>
                    <span class="text-slate-200">{{ $member->social_name }}</span>
                </div>
                @endif
                <div>
                    <span class="text-slate-400 block text-[11px]">Data de Nascimento</span>
                    <span class="text-slate-200">
                        {{ $member->birth_date ? $member->birth_date->format('d/m/Y') . ' (' . \Carbon\Carbon::parse($member->birth_date)->age . ' anos)' : 'Não informada' }}
                    </span>
                </div>
                <div>
                    <span class="text-slate-400 block text-[11px]">Sexo</span>
                    <span class="text-slate-200">{{ $member->gender === 'M' ? 'Masculino' : ($member->gender === 'F' ? 'Feminino' : 'Outro') }}</span>
                </div>
                <div>
                    <span class="text-slate-400 block text-[11px]">Estado Civil</span>
                    <span class="text-slate-200">{{ ucfirst($member->marital_status ?? 'Não informado') }}</span>
                </div>
                <div>
                    <span class="text-slate-400 block text-[11px]">CPF</span>
                    <span class="text-slate-200">{{ $member->cpf ?? 'Não informado' }}</span>
                </div>
                <div>
                    <span class="text-slate-400 block text-[11px]">RG</span>
                    <span class="text-slate-200">{{ $member->rg ?? 'Não informado' }}</span>
                </div>
            </div>
        </div>

        <!-- Coluna 2 e 3: Contato, Residência, Eclesiástico e Histórico -->
        <div class="md:col-span-2 space-y-6">

            <!-- Contato e Endereço Residencial -->
            <div class="bg-slate-900/60 p-6 rounded-2xl border border-slate-800/80">
                <h2 class="text-xs font-bold text-brand-400 uppercase tracking-wider pb-2 border-b border-slate-800 mb-4">
                    Contato e Endereço Residencial
                </h2>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs">
                    <div>
                        <span class="text-slate-400 block text-[11px]">WhatsApp / Celular</span>
                        <span class="text-slate-200 font-semibold">{{ $member->whatsapp ?? $member->phone ?? 'Não informado' }}</span>
                    </div>
                    <div>
                        <span class="text-slate-400 block text-[11px]">E-mail</span>
                        <span class="text-slate-200">{{ $member->email ?? 'Não informado' }}</span>
                    </div>
                    <div class="sm:col-span-2">
                        <span class="text-slate-400 block text-[11px]">Endereço Residencial</span>
                        <span class="text-slate-200 block mt-0.5">
                            {{ $member->address ?? 'Sem logradouro' }}
                            @if($member->number), Nº {{ $member->number }}@endif
                            @if($member->complement), {{ $member->complement }}@endif
                        </span>
                        <span class="text-slate-400 block text-[11px] mt-0.5">
                            {{ $member->neighborhood ? $member->neighborhood . ' • ' : '' }}{{ $member->city ?? 'Brasília' }}/{{ $member->state ?? 'DF' }}
                            @if($member->zip_code) — CEP {{ $member->zip_code }}@endif
                        </span>
                    </div>
                </div>
            </div>

            <!-- Informações Eclesiásticas -->
            <div class="bg-slate-900/60 p-6 rounded-2xl border border-slate-800/80 space-y-4">
                <h2 class="text-xs font-bold text-brand-400 uppercase tracking-wider pb-2 border-b border-slate-800">
                    Situação, Cargo e Funções Exercidas
                </h2>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 text-xs">
                    <div>
                        <span class="text-slate-400 block text-[11px]">Cargo Eclesiástico Atual</span>
                        <span class="text-brand-300 font-bold text-sm">{{ $member->ecclesiastical_position ?? 'Membro' }}</span>
                    </div>
                    <div>
                        <span class="text-slate-400 block text-[11px]">Data de Entrada / Adesão</span>
                        <span class="text-slate-200 font-semibold">{{ $member->entry_date ? $member->entry_date->format('d/m/Y') : 'Não informada' }}</span>
                    </div>
                    <div>
                        <span class="text-slate-400 block text-[11px]">Situação no Rol</span>
                        <span class="text-slate-200 font-semibold uppercase">{{ $member->status }}</span>
                    </div>
                </div>

                <!-- Funções Exercidas -->
                <div class="pt-3 border-t border-slate-800/80">
                    <span class="text-slate-400 block text-[11px] mb-1.5">Funções Exercidas / Departamentos:</span>
                    @if($member->functions->isNotEmpty())
                        <div class="flex flex-wrap gap-1.5">
                            @foreach($member->functions as $function)
                                <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-semibold bg-brand-950/80 text-brand-300 border border-brand-500/30">
                                    {{ $function->name }}
                                </span>
                            @endforeach
                        </div>
                    @else
                        <span class="text-slate-400 text-xs font-medium">Nenhuma</span>
                    @endif
                </div>
            </div>

            <!-- BLOCO 1: Histórico Eclesiástico e Anotações -->
            @php
                $ecclesiasticalList = ($member->histories ?? $member->ecclesiasticalHistories)->reject(function($h) {
                    $desc = mb_strtolower($h->description ?? '');
                    return str_contains($desc, 'transferência') || str_contains($desc, 'transferencia') || str_contains($desc, 'inativação') || str_contains($desc, 'inativacao');
                });
            @endphp
            <div class="bg-slate-900/60 p-6 rounded-2xl border border-slate-800/80">
                <div class="flex items-center justify-between pb-2 border-b border-slate-800 mb-4">
                    <h2 class="text-xs font-bold text-amber-400 uppercase tracking-wider flex items-center gap-1.5">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <span>Histórico Eclesiástico e Linha do Tempo</span>
                    </h2>
                    <span class="text-[10px] text-slate-400">{{ $ecclesiasticalList->count() }} registro(s)</span>
                </div>

                <div class="space-y-3 max-h-80 overflow-y-auto">
                    @forelse($ecclesiasticalList as $history)
                    <div class="p-3.5 bg-slate-950/80 border border-slate-800/90 rounded-xl text-xs flex items-start gap-3">
                        <div class="w-2 h-2 rounded-full bg-amber-400 mt-1.5 shrink-0 ring-4 ring-amber-400/10"></div>
                        <div class="flex-1 min-w-0">
                            <p class="text-slate-200 font-medium whitespace-pre-line leading-relaxed">{{ $history->description }}</p>
                            <span class="text-[10px] text-slate-400 block mt-1.5">
                                Registrado em {{ $history->recorded_at->format('d/m/Y H:i') }}
                                @if($history->creator)
                                    por <strong class="text-slate-300">{{ $history->creator->name }}</strong> ({{ $history->creator->role?->name ?? 'Secretaria' }})
                                @endif
                            </span>
                        </div>
                    </div>
                    @empty
                    <p class="text-xs text-slate-500 py-3 text-center">
                        Nenhum registro lançado no histórico eclesiástico até o momento.
                    </p>
                    @endforelse
                </div>
            </div>

            <!-- BLOCO 2: Histórico de Transferências -->
            <div class="bg-slate-900/60 p-6 rounded-2xl border border-slate-800/80">
                <h2 class="text-xs font-bold text-brand-400 uppercase tracking-wider pb-2 border-b border-slate-800 mb-4">
                    Histórico de Transferências
                </h2>

                <div class="space-y-2">
                    @forelse($member->transfers as $transfer)
                    <div class="p-3 bg-slate-950/80 border border-slate-800 rounded-xl text-xs flex items-center justify-between">
                        <div>
                            <span class="text-slate-200 font-medium block">
                                @if($transfer->type === 'inactivation')
                                    <span class="text-red-400 font-bold">Saída do Ministério / Inativação</span> de <strong>{{ $transfer->fromChurch->name }}</strong>
                                @else
                                    De <strong class="text-white">{{ $transfer->fromChurch->name }}</strong> para <strong class="text-brand-400">{{ $transfer->toChurch?->name ?? 'Outra Congregação' }}</strong>
                                @endif
                            </span>
                            <span class="text-[10px] text-slate-400 block mt-0.5">
                                Realizada em {{ $transfer->transferred_at->format('d/m/Y H:i') }} por {{ $transfer->transferredBy->name ?? 'Secretaria' }}
                                @if($transfer->approver)
                                    • Autorizada por: {{ $transfer->approver->name }}
                                @endif
                                @if($transfer->reason) • Motivo: {{ $transfer->reason }} @endif
                            </span>
                        </div>
                        @if($transfer->status === 'pending')
                            <span class="bg-amber-500/10 text-amber-400 border border-amber-500/20 px-2 py-0.5 rounded text-xs">Pendente</span>
                        @elseif($transfer->status === 'approved' || $transfer->status === 'completed')
                            <span class="bg-emerald-500/10 text-emerald-400 border border-emerald-500/20 px-2 py-0.5 rounded text-xs font-medium">Concluída</span>
                        @elseif($transfer->status === 'rejected')
                            <span class="bg-rose-500/10 text-rose-400 border border-rose-500/20 px-2 py-0.5 rounded text-xs font-medium">Recusada</span>
                        @else
                            <span class="bg-slate-800 text-slate-400 border border-slate-700 px-2 py-0.5 rounded text-xs">{{ ucfirst($transfer->status) }}</span>
                        @endif
                    </div>
                    @empty
                    <p class="text-xs text-slate-500 py-2 text-center">
                        Este membro não possui transferências registradas no histórico.
                    </p>
                    @endforelse
                </div>
            </div>

            <!-- Histórico de Dízimos (Módulo Financeiro) -->
            <div class="bg-slate-900/60 p-6 rounded-2xl border border-slate-800/80">
                <div class="flex items-center justify-between pb-2 border-b border-slate-800 mb-4">
                    <h2 class="text-xs font-bold text-emerald-400 uppercase tracking-wider">
                        Histórico de Dízimos
                    </h2>
                    @if(isset($titheHistory) && $titheHistory->isNotEmpty())
                        <span class="text-[11px] font-semibold text-slate-400">
                            Total Registrado: <strong class="text-emerald-400">R$ {{ number_format($titheHistory->sum('amount'), 2, ',', '.') }}</strong>
                        </span>
                    @endif
                </div>

                <div class="space-y-2 max-h-80 overflow-y-auto">
                    @forelse($titheHistory ?? [] as $tithe)
                    <div class="p-3 bg-slate-950/80 border border-slate-800 rounded-xl text-xs flex items-center justify-between">
                        <div>
                            <span class="text-slate-200 font-medium block">
                                Competência: <strong class="text-white">{{ $tithe->competence_date->translatedFormat('F / Y') }}</strong>
                                <span class="text-slate-500 font-normal text-[11px]">({{ $tithe->competence_date->format('d/m/Y') }})</span>
                            </span>
                            <span class="text-[10px] text-slate-400 block mt-0.5">
                                {{ $tithe->church->name ?? 'Congregação' }}
                                @if($tithe->description) • {{ $tithe->description }} @endif
                            </span>
                        </div>
                        <div class="text-right">
                            <span class="text-xs font-bold text-emerald-400">
                                R$ {{ number_format($tithe->amount, 2, ',', '.') }}
                            </span>
                        </div>
                    </div>
                    @empty
                    <p class="text-xs text-slate-500 py-2 text-center">
                        Nenhum registro de dízimo encontrado para este membro.
                    </p>
                    @endforelse
                </div>
            </div>

        </div>

    </div>

</div>

<!-- MODAL DE OPÇÕES DE IMPRESSÃO DA FICHA DO MEMBRO -->
<div id="modalPrintOptions" class="fixed inset-0 z-50 hidden bg-slate-950/80 backdrop-blur-sm flex items-center justify-center p-4">
    <div class="bg-slate-900 border border-slate-800 rounded-2xl max-w-lg w-full p-6 shadow-2xl space-y-5 animate-in fade-in zoom-in duration-150">
        
        <!-- Cabeçalho do Modal -->
        <div class="flex items-start justify-between pb-4 border-b border-slate-800">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-indigo-600/20 text-indigo-400 flex items-center justify-center border border-indigo-500/30 shrink-0">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                    </svg>
                </div>
                <div>
                    <h3 class="text-base font-bold text-white tracking-tight">Opções de Impressão da Ficha do Membro</h3>
                    <p class="text-xs text-slate-400 mt-0.5">Selecione as seções e dados que devem constar no documento oficial</p>
                </div>
            </div>
            <button type="button" onclick="closePrintModal()" class="text-slate-400 hover:text-white p-1 rounded-lg hover:bg-slate-800 transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>

        <!-- Formulário de Opções (Envia via GET para rota de impressão) -->
        <form id="formPrintOptions" method="GET" action="{{ route('members.print', $member) }}" target="_blank" onsubmit="return validatePrintForm(event)" class="space-y-5">
            <input type="hidden" name="custom_options" value="1">

            <!-- Checkboxes de Seção -->
            <div class="space-y-2.5">
                <label class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-2">
                    1. Seções do Relatório
                </label>

                <!-- 1. Dados Cadastrais e Endereço (Padrão e Obrigatório) -->
                <label class="flex items-start gap-3 p-3 bg-slate-950/60 border border-slate-800 rounded-xl cursor-not-allowed opacity-90">
                    <input type="checkbox" checked disabled class="mt-0.5 w-4 h-4 rounded text-brand-600 bg-slate-900 border-slate-700">
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-bold text-white">Dados Cadastrais e Endereço</span>
                            <span class="text-[10px] font-semibold text-brand-400 bg-brand-950 px-2 py-0.5 rounded border border-brand-500/30">Obrigatório</span>
                        </div>
                        <p class="text-[11px] text-slate-400 mt-0.5">Identificação civil, contatos, endereço residencial e congregação atual.</p>
                    </div>
                </label>

                <!-- 2. Histórico Eclesiástico -->
                <label class="flex items-start gap-3 p-3 bg-slate-950/40 hover:bg-slate-950/80 border border-slate-800 hover:border-slate-700 rounded-xl cursor-pointer transition-all">
                    <input type="checkbox" name="include_ecclesiastical" value="1" checked class="mt-0.5 w-4 h-4 rounded text-brand-600 bg-slate-900 border-slate-700 focus:ring-brand-500">
                    <div class="flex-1 min-w-0">
                        <span class="text-xs font-bold text-white block">Incluir Histórico Eclesiástico</span>
                        <p class="text-[11px] text-slate-400 mt-0.5">Linha do tempo de anotações pastorais, cargos e observações eclesiásticas.</p>
                    </div>
                </label>

                <!-- 3. Histórico de Transferências -->
                <label class="flex items-start gap-3 p-3 bg-slate-950/40 hover:bg-slate-950/80 border border-slate-800 hover:border-slate-700 rounded-xl cursor-pointer transition-all">
                    <input type="checkbox" name="include_transfers" value="1" checked class="mt-0.5 w-4 h-4 rounded text-brand-600 bg-slate-900 border-slate-700 focus:ring-brand-500">
                    <div class="flex-1 min-w-0">
                        <span class="text-xs font-bold text-white block">Incluir Histórico de Transferências</span>
                        <p class="text-[11px] text-slate-400 mt-0.5">Histórico completo de movimentações entre congregações e inativações.</p>
                    </div>
                </label>

                <!-- 4. Histórico de Dízimos / Ofertas -->
                @php
                    $canViewFinance = auth()->check() && (auth()->user()->isFirstSecretaryRegional() || auth()->user()->hasPermission('finance.view'));
                @endphp

                @if($canViewFinance)
                <label class="flex items-start gap-3 p-3 bg-slate-950/40 hover:bg-slate-950/80 border border-slate-800 hover:border-emerald-500/40 rounded-xl cursor-pointer transition-all has-[:checked]:border-emerald-500/50 has-[:checked]:bg-emerald-950/20">
                    <input type="checkbox" name="include_tithes" id="include_tithes" value="1" onchange="toggleTithePeriod()" class="mt-0.5 w-4 h-4 rounded text-emerald-600 bg-slate-900 border-slate-700 focus:ring-emerald-500">
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-bold text-emerald-400">Incluir Histórico de Dízimos / Ofertas</span>
                            <span class="text-[10px] font-semibold text-emerald-300 bg-emerald-950 px-2 py-0.5 rounded border border-emerald-500/30">Financeiro</span>
                        </div>
                        <p class="text-[11px] text-slate-400 mt-0.5">Demonstrativo de contribuições e total acumulado no período filtrado.</p>
                    </div>
                </label>
                @endif
            </div>

            <!-- Filtro de Período Obrigatório para o Dízimo -->
            <div id="tithe_period_container" class="hidden p-4 rounded-xl bg-emerald-950/20 border border-emerald-500/30 space-y-3">
                <div class="flex items-center gap-2">
                    <svg class="w-4 h-4 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                    <span class="text-xs font-bold text-emerald-300">2. Período Obrigatório para Histórico de Dízimos</span>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label for="print_start_date" class="block text-[11px] font-medium text-slate-300 mb-1">Data Inicial *</label>
                        <input type="date" name="start_date" id="print_start_date"
                            value="{{ now()->startOfYear()->format('Y-m-d') }}"
                            class="w-full px-3 py-2 bg-slate-900 border border-slate-700 rounded-lg text-xs text-white focus:outline-none focus:ring-1 focus:ring-emerald-500">
                    </div>
                    <div>
                        <label for="print_end_date" class="block text-[11px] font-medium text-slate-300 mb-1">Data Final *</label>
                        <input type="date" name="end_date" id="print_end_date"
                            value="{{ now()->format('Y-m-d') }}"
                            class="w-full px-3 py-2 bg-slate-900 border border-slate-700 rounded-lg text-xs text-white focus:outline-none focus:ring-1 focus:ring-emerald-500">
                    </div>
                </div>

                <!-- Opção de Privacidade dos Valores -->
                <div class="pt-2 border-t border-emerald-500/20">
                    <label class="flex items-center gap-2.5 cursor-pointer select-none">
                        <input type="checkbox" name="hide_values" id="hide_values" value="1" class="w-4 h-4 rounded text-emerald-600 bg-slate-900 border-slate-700 focus:ring-emerald-500">
                        <span class="text-xs font-medium text-slate-200">Ocultar valores numéricos (Exibir R$ ***,** para privacidade)</span>
                    </label>
                </div>
            </div>

            <!-- Opções de Privacidade dos Documentos Pessoais -->
            <div class="space-y-2 pt-2 border-t border-slate-800">
                <label class="flex items-start gap-3 p-3 bg-slate-950/40 hover:bg-slate-950/80 border border-slate-800 hover:border-amber-500/40 rounded-xl cursor-pointer transition-all has-[:checked]:border-amber-500/50 has-[:checked]:bg-amber-950/20">
                    <input type="checkbox" name="hide_documents" id="hide_documents" value="1" class="mt-0.5 w-4 h-4 rounded text-amber-500 bg-slate-900 border-slate-700 focus:ring-amber-500">
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-bold text-amber-300">Ocultar documentos pessoais (RG e CPF com asteriscos)</span>
                            <span class="text-[10px] font-semibold text-amber-400 bg-amber-950 px-2 py-0.5 rounded border border-amber-500/30">Privacidade</span>
                        </div>
                        <p class="text-[11px] text-slate-400 mt-0.5">Exibe ***.***.***-** para CPF e **.***.***-* para RG no documento impresso.</p>
                    </div>
                </label>
            </div>

            <!-- Modelo de Assinatura no Rodapé -->
            @php
                $isUserLocal = auth()->check() && auth()->user()->isLocal();
                $defaultSignature = $isUserLocal ? 'local' : 'regional';
            @endphp
            <div class="space-y-2 pt-2 border-t border-slate-800">
                <label for="signature_type" class="block text-xs font-semibold text-slate-300 uppercase tracking-wider">
                    Modelo de Assinatura no Rodapé
                </label>
                <select name="signature_type" id="signature_type" class="w-full px-3.5 py-2.5 bg-slate-950/80 border border-slate-700 rounded-xl text-xs text-white focus:outline-none focus:ring-1 focus:ring-indigo-500">
                    @if(!$isUserLocal)
                        <option value="regional" {{ $defaultSignature === 'regional' ? 'selected' : '' }}>Secretaria Regional</option>
                    @endif
                    <option value="local" {{ $defaultSignature === 'local' ? 'selected' : '' }}>Secretaria Local</option>
                    @if(!$isUserLocal)
                        <option value="both">Ambas (Lado a Lado)</option>
                    @endif
                    <option value="none">Sem Assinatura</option>
                </select>
                <p class="text-[11px] text-slate-400">
                    @if($isUserLocal)
                        Seu perfil possui permissão para emissão de assinatura da Secretaria Local.
                    @else
                        Define quais linhas de assinatura serão emitidas no rodapé do documento.
                    @endif
                </p>
            </div>

            <!-- Botões de Ação do Modal -->
            <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-800">
                <button type="button" onclick="closePrintModal()" class="px-4 py-2.5 bg-slate-800 hover:bg-slate-700 text-slate-300 text-xs font-semibold rounded-xl border border-slate-700/60 transition-colors">
                    Cancelar
                </button>
                <button type="submit" class="px-6 py-2.5 bg-gradient-to-r from-indigo-600 to-brand-600 hover:from-indigo-500 hover:to-brand-500 text-white text-xs font-bold rounded-xl shadow-lg shadow-indigo-600/30 transition-all flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                    </svg>
                    <span>Gerar e Imprimir Ficha</span>
                </button>
            </div>
        </form>

    </div>
</div>

<script>
    function openPrintModal() {
        const modal = document.getElementById('modalPrintOptions');
        if (modal) {
            modal.classList.remove('hidden');
        }
    }

    function closePrintModal() {
        const modal = document.getElementById('modalPrintOptions');
        if (modal) {
            modal.classList.add('hidden');
        }
    }

    function toggleTithePeriod() {
        const tithesCheckbox = document.getElementById('include_tithes');
        const periodContainer = document.getElementById('tithe_period_container');
        const startDateInput = document.getElementById('print_start_date');
        const endDateInput = document.getElementById('print_end_date');

        if (!tithesCheckbox || !periodContainer) return;

        if (tithesCheckbox.checked) {
            periodContainer.classList.remove('hidden');
            if (startDateInput) startDateInput.setAttribute('required', 'required');
            if (endDateInput) endDateInput.setAttribute('required', 'required');
        } else {
            periodContainer.classList.add('hidden');
            if (startDateInput) startDateInput.removeAttribute('required');
            if (endDateInput) endDateInput.removeAttribute('required');
        }
    }

    function validatePrintForm(event) {
        const tithesCheckbox = document.getElementById('include_tithes');
        if (tithesCheckbox && tithesCheckbox.checked) {
            const startDate = document.getElementById('print_start_date');
            const endDate = document.getElementById('print_end_date');

            if (!startDate || !startDate.value) {
                alert('Por favor, selecione a Data Inicial para o histórico de dízimos.');
                if (startDate) startDate.focus();
                event.preventDefault();
                return false;
            }

            if (!endDate || !endDate.value) {
                alert('Por favor, selecione a Data Final para o histórico de dízimos.');
                if (endDate) endDate.focus();
                event.preventDefault();
                return false;
            }

            if (startDate.value > endDate.value) {
                alert('A Data Inicial não pode ser maior que a Data Final.');
                startDate.focus();
                event.preventDefault();
                return false;
            }
        }

        // Fecha o modal após disparar a impressão na nova aba
        setTimeout(() => {
            closePrintModal();
        }, 300);

        return true;
    }
</script>
@endsection
