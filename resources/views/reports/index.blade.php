@extends('layouts.app')

@section('title', 'Central de Relatórios')
@section('header', 'Relatórios Oficiais')

@section('content')
<div class="space-y-6">

    <!-- Cabeçalho -->
    <div class="bg-slate-900/60 p-6 rounded-2xl border border-slate-800/80 shadow-xl flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-xl font-bold text-white tracking-tight">Central de Relatórios Oficiais</h1>
            <p class="text-xs text-slate-400 mt-1">
                Gere, imprima em formato oficial A4/PDF ou exporte relatórios consolidados para planilhas.
            </p>
        </div>
        <div class="flex items-center gap-2">
            <span class="text-xs text-slate-400">Escopo Atual:</span>
            <span class="px-2.5 py-1 rounded-lg text-xs font-bold {{ auth()->user()->isRegional() ? 'bg-indigo-950 text-indigo-300 border border-indigo-500/40' : 'bg-emerald-950 text-emerald-300 border border-emerald-500/40' }}">
                {{ auth()->user()->scope }} ({{ $churchesCount }} congregações)
            </span>
        </div>
    </div>

    <!-- Grade de Relatórios -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">

        <!-- 1. Relatório de Membros -->
        <div class="bg-slate-900/60 p-6 rounded-2xl border border-slate-800/80 hover:border-brand-500/50 transition-all flex flex-col justify-between shadow-lg group">
            <div class="space-y-3">
                <div class="w-12 h-12 rounded-2xl bg-brand-600/10 text-brand-400 border border-brand-500/20 flex items-center justify-center group-hover:scale-110 transition-transform">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                    </svg>
                </div>
                <h3 class="text-base font-bold text-white">Rol Geral de Membros</h3>
                <p class="text-xs text-slate-400 leading-relaxed">
                    Listagem analítica de membros cadastrados com filtros por congregação, situação eclesiástica, sexo, faixa etária e período de entrada.
                </p>
            </div>
            <div class="pt-6">
                <a href="{{ route('reports.members') }}" class="w-full py-2.5 px-4 bg-brand-600 hover:bg-brand-500 text-white text-xs font-semibold rounded-xl text-center block shadow transition-colors">
                    Gerar Relatório de Membros →
                </a>
            </div>
        </div>

        <!-- 2. Relatório de Aniversariantes -->
        <div class="bg-slate-900/60 p-6 rounded-2xl border border-slate-800/80 hover:border-purple-500/50 transition-all flex flex-col justify-between shadow-lg group">
            <div class="space-y-3">
                <div class="w-12 h-12 rounded-2xl bg-purple-600/10 text-purple-400 border border-purple-500/20 flex items-center justify-center group-hover:scale-110 transition-transform">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                </div>
                <h3 class="text-base font-bold text-white">Aniversariantes do Mês</h3>
                <p class="text-xs text-slate-400 leading-relaxed">
                    Relação cronológica de aniversariantes por dia, idade a completar, congregação e contatos telefônicos para envio de felicitações.
                </p>
            </div>
            <div class="pt-6">
                <a href="{{ route('reports.birthdays') }}" class="w-full py-2.5 px-4 bg-purple-600 hover:bg-purple-500 text-white text-xs font-semibold rounded-xl text-center block shadow transition-colors">
                    Gerar Aniversariantes →
                </a>
            </div>
        </div>

        <!-- 3. Relatório de Congregações -->
        <div class="bg-slate-900/60 p-6 rounded-2xl border border-slate-800/80 hover:border-emerald-500/50 transition-all flex flex-col justify-between shadow-lg group">
            <div class="space-y-3">
                <div class="w-12 h-12 rounded-2xl bg-emerald-600/10 text-emerald-400 border border-emerald-500/20 flex items-center justify-center group-hover:scale-110 transition-transform">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                    </svg>
                </div>
                <h3 class="text-base font-bold text-white">Quadro de Congregações</h3>
                <p class="text-xs text-slate-400 leading-relaxed">
                    Quadro estatístico e cadastral das igrejas, incluindo códigos oficiais, dirigentes responsáveis, telefones e totalizadores de membros.
                </p>
            </div>
            <div class="pt-6">
                <a href="{{ route('reports.churches') }}" class="w-full py-2.5 px-4 bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-semibold rounded-xl text-center block shadow transition-colors">
                    Gerar Relatório de Igrejas →
                </a>
            </div>
        </div>

        <!-- 4. Relatório de Transferências -->
        <div class="bg-slate-900/60 p-6 rounded-2xl border border-slate-800/80 hover:border-indigo-500/50 transition-all flex flex-col justify-between shadow-lg group">
            <div class="space-y-3">
                <div class="w-12 h-12 rounded-2xl bg-indigo-600/10 text-indigo-400 border border-indigo-500/20 flex items-center justify-center group-hover:scale-110 transition-transform">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/>
                    </svg>
                </div>
                <h3 class="text-base font-bold text-white">Transferências no Período</h3>
                <p class="text-xs text-slate-400 leading-relaxed">
                    Histórico consolidado de transferências efetuadas entre congregações com registro de secretários, datas e motivos.
                </p>
            </div>
            <div class="pt-6">
                <a href="{{ route('reports.transfers') }}" class="w-full py-2.5 px-4 bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-semibold rounded-xl text-center block shadow transition-colors">
                    Gerar Transferências →
                </a>
            </div>
        </div>

        <!-- 5. Relatório de Secretários (Apenas autorizados) -->
        @can('viewAny', App\Models\User::class)
        <div class="bg-slate-900/60 p-6 rounded-2xl border border-slate-800/80 hover:border-amber-500/50 transition-all flex flex-col justify-between shadow-lg group">
            <div class="space-y-3">
                <div class="w-12 h-12 rounded-2xl bg-amber-600/10 text-amber-400 border border-amber-500/20 flex items-center justify-center group-hover:scale-110 transition-transform">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                    </svg>
                </div>
                <h3 class="text-base font-bold text-white">Corpo de Secretários</h3>
                <p class="text-xs text-slate-400 leading-relaxed">
                    Relação dos secretários cadastrados, níveis hierárquicos, escopo de atuação (Regional / Local) e congregações vinculadas.
                </p>
            </div>
            <div class="pt-6">
                <a href="{{ route('reports.secretaries') }}" class="w-full py-2.5 px-4 bg-amber-600 hover:bg-amber-500 text-white text-xs font-semibold rounded-xl text-center block shadow transition-colors">
                    Gerar Secretários →
                </a>
            </div>
        </div>
        @endcan

    </div>

</div>
@endsection
