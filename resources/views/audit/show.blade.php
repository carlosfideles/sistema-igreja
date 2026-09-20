@extends('layouts.app')

@section('title', 'Detalhe do Registro de Auditoria')
@section('header', 'Detalhe da Auditoria')

@section('content')
<div class="space-y-6 max-w-4xl mx-auto">

    {{-- Navegação --}}
    <div class="flex items-center gap-2 text-sm text-slate-500">
        <a href="{{ route('audit.index') }}" class="flex items-center gap-1.5 hover:text-slate-300 transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
            Auditoria
        </a>
        <span class="text-slate-700">/</span>
        <span class="text-slate-400">Registro #{{ $log->id }}</span>
    </div>

    {{-- Card principal --}}
    <div class="bg-slate-900/70 border border-slate-800/80 rounded-2xl shadow-xl overflow-hidden">

        {{-- Header do Card --}}
        <div class="px-6 py-5 border-b border-slate-800/80 bg-slate-950/40 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h3 class="text-base font-semibold text-white">Registro de Auditoria</h3>
                <p class="text-xs text-slate-500 mt-0.5">ID #{{ $log->id }}</p>
            </div>
            <div class="flex items-center gap-2">
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
                <span class="inline-flex items-center px-3 py-1 rounded-lg text-sm font-semibold border {{ $colorClass }}">
                    {{ $actionLabels[$log->action] ?? str_replace('_', ' ', ucwords($log->action, '_')) }}
                </span>
            </div>
        </div>

        {{-- Corpo do Card --}}
        <div class="p-6 space-y-6">

            {{-- Linha 1: Info Principal --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">

                {{-- Data e Hora --}}
                <div class="bg-slate-800/40 rounded-xl p-4 border border-slate-700/40">
                    <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1.5">Data e Hora</p>
                    <p class="text-white font-medium">{{ $log->created_at->format('d/m/Y') }}</p>
                    <p class="text-slate-400 text-sm">{{ $log->created_at->format('H:i:s') }} — {{ $log->created_at->diffForHumans() }}</p>
                </div>

                {{-- Usuário --}}
                <div class="bg-slate-800/40 rounded-xl p-4 border border-slate-700/40">
                    <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1.5">Usuário Executor</p>
                    @if($log->user)
                        <div class="flex items-center gap-2">
                            <div class="w-8 h-8 rounded-full bg-gradient-to-tr from-slate-700 to-slate-600 flex items-center justify-center text-xs font-bold text-brand-300 shrink-0">
                                {{ strtoupper(substr($log->user->name, 0, 2)) }}
                            </div>
                            <div>
                                <p class="text-white font-medium text-sm">{{ $log->user->name }}</p>
                                <p class="text-slate-500 text-xs">{{ $log->user->email }}</p>
                            </div>
                        </div>
                    @else
                        <p class="text-slate-400 italic">Sistema (sem usuário)</p>
                    @endif
                </div>

                {{-- Congregação --}}
                <div class="bg-slate-800/40 rounded-xl p-4 border border-slate-700/40">
                    <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1.5">Congregação</p>
                    @if($log->church)
                        <p class="text-white font-medium">{{ $log->church->name }}</p>
                        @if($log->church->code)
                            <p class="text-slate-400 text-sm">Cód. {{ $log->church->code }}</p>
                        @endif
                    @else
                        <p class="text-slate-400 italic">— (ação geral)</p>
                    @endif
                </div>
            </div>

            {{-- Linha 2: Módulo e Entidade --}}
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-5">
                <div class="bg-slate-800/40 rounded-xl p-4 border border-slate-700/40">
                    <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1.5">Módulo</p>
                    <p class="text-white font-medium">{{ $moduleLabels[$log->module] ?? ucfirst($log->module ?? '—') }}</p>
                </div>

                <div class="bg-slate-800/40 rounded-xl p-4 border border-slate-700/40">
                    <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1.5">Tipo de Entidade</p>
                    <p class="text-white font-medium">{{ $log->entity_type ?? '—' }}</p>
                </div>

                <div class="bg-slate-800/40 rounded-xl p-4 border border-slate-700/40">
                    <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1.5">ID da Entidade</p>
                    <p class="text-white font-medium">{{ $log->entity_id ? '#' . $log->entity_id : '—' }}</p>
                </div>
            </div>

            {{-- Descrição --}}
            <div class="bg-slate-800/40 rounded-xl p-4 border border-slate-700/40">
                <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider mb-2">Descrição do Evento</p>
                <p class="text-slate-200 text-sm leading-relaxed">{{ $log->description ?? 'Sem descrição registrada.' }}</p>
            </div>

            {{-- Valores Anteriores / Novos --}}
            @if($log->old_values || $log->new_values)
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                @if($log->old_values)
                <div class="bg-red-950/20 border border-red-500/20 rounded-xl p-4">
                    <p class="text-xs font-semibold text-red-400 uppercase tracking-wider mb-3 flex items-center gap-1.5">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        Valores Anteriores
                    </p>
                    <div class="space-y-1.5">
                        @foreach($log->old_values as $field => $value)
                        <div class="flex gap-2 text-xs">
                            <span class="text-slate-500 font-medium min-w-[100px] shrink-0">{{ str_replace('_', ' ', ucfirst($field)) }}:</span>
                            <span class="text-red-300 break-all">{{ is_array($value) ? json_encode($value) : ($value ?? '—') }}</span>
                        </div>
                        @endforeach
                    </div>
                </div>
                @endif

                @if($log->new_values)
                <div class="bg-emerald-950/20 border border-emerald-500/20 rounded-xl p-4">
                    <p class="text-xs font-semibold text-emerald-400 uppercase tracking-wider mb-3 flex items-center gap-1.5">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v3m0 0v3m0-3h3m-3 0H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        Novos Valores
                    </p>
                    <div class="space-y-1.5">
                        @foreach($log->new_values as $field => $value)
                        <div class="flex gap-2 text-xs">
                            <span class="text-slate-500 font-medium min-w-[100px] shrink-0">{{ str_replace('_', ' ', ucfirst($field)) }}:</span>
                            <span class="text-emerald-300 break-all">{{ is_array($value) ? json_encode($value) : ($value ?? '—') }}</span>
                        </div>
                        @endforeach
                    </div>
                </div>
                @endif
            </div>
            @endif

            {{-- Informações Técnicas --}}
            <div class="bg-slate-950/60 border border-slate-800/60 rounded-xl p-4">
                <p class="text-xs font-semibold text-slate-600 uppercase tracking-wider mb-3">Informações Técnicas</p>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-xs">
                    <div>
                        <span class="text-slate-600">Endereço IP: </span>
                        <span class="text-slate-400 font-mono">{{ $log->ip_address ?? '—' }}</span>
                    </div>
                    <div class="sm:col-span-1">
                        <span class="text-slate-600">Navegador: </span>
                        <span class="text-slate-400 break-all">{{ $log->user_agent ? Str::limit($log->user_agent, 80) : '—' }}</span>
                    </div>
                </div>
            </div>

        </div>
    </div>

    {{-- Botão Voltar --}}
    <div class="flex justify-start">
        <a href="{{ route('audit.index') }}"
            class="inline-flex items-center gap-2 px-4 py-2.5 bg-slate-800/60 hover:bg-slate-700/60 border border-slate-700/60 text-slate-300 hover:text-white text-sm font-medium rounded-xl transition-all">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
            Voltar para Auditoria
        </a>
    </div>

</div>
@endsection
