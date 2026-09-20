@extends('layouts.app')

@section('title', 'Dashboard')
@section('header', 'Painel de Controle')

@section('content')
<!-- Import Chart.js CDN -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<div class="space-y-6">

    <!-- Banner de Boas-Vindas e Contexto de Escopo -->
    <div class="relative overflow-hidden bg-gradient-to-r from-slate-900 via-slate-900/90 to-brand-950/40 p-6 rounded-3xl border border-slate-800/80 shadow-2xl">
        <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <div class="flex items-center gap-2 mb-1">
                    <span class="text-xs text-brand-400 font-semibold tracking-wider uppercase">IEADM-DF • Painel Principal</span>
                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-black tracking-wider {{ $currentUser->isRegional() ? 'bg-indigo-950 text-indigo-300 border border-indigo-500/40' : 'bg-emerald-950 text-emerald-300 border border-emerald-500/40' }}">
                        ABRANGÊNCIA {{ $currentUser->scope }}
                    </span>
                </div>
                <h1 class="text-2xl font-extrabold text-white tracking-tight">
                    Olá, {{ $currentUser->name }}!
                </h1>
                <p class="text-xs text-slate-300 mt-1 max-w-2xl leading-relaxed">
                    @if($currentUser->isRegional())
                        Você possui visão consolidada da <strong>Secretaria Regional</strong> com acesso a todas as {{ $totalChurches }} congregações.
                    @else
                        Você está autenticado na visão <strong>Local</strong>, gerenciando os dados das {{ $totalChurches }} congregações a você autorizadas.
                    @endif
                </p>
            </div>

            <div class="flex items-center gap-2 shrink-0">
                <a href="{{ route('members.create') }}" class="px-4 py-2 bg-brand-600 hover:bg-brand-500 text-white text-xs font-semibold rounded-xl shadow-lg shadow-brand-600/30 transition-all flex items-center gap-1.5">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    <span>Novo Membro</span>
                </a>
                @can('create', App\Models\Church::class)
                <a href="{{ route('churches.create') }}" class="px-4 py-2 bg-slate-800 hover:bg-slate-700 text-slate-200 text-xs font-semibold rounded-xl border border-slate-700/80 transition-all flex items-center gap-1.5">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                    </svg>
                    <span>Nova Igreja</span>
                </a>
                @endcan
            </div>
        </div>
    </div>

    <!-- 6 CARDS DE TOTAIS E INDICADORES -->
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-4">
        <!-- Card 1: Total de Igrejas -->
        <div class="bg-slate-900/60 p-4 rounded-2xl border border-slate-800/80 hover:border-slate-700 transition-all shadow-lg">
            <span class="text-[10px] font-semibold text-slate-400 uppercase tracking-wider block">Igrejas</span>
            <span class="text-2xl font-black text-white mt-1 block">{{ $totalChurches }}</span>
            <span class="text-[10px] text-emerald-400 font-medium block mt-0.5">{{ $activeChurches }} ativas</span>
        </div>

        <!-- Card 2: Total de Membros -->
        <div class="bg-slate-900/60 p-4 rounded-2xl border border-slate-800/80 hover:border-slate-700 transition-all shadow-lg">
            <span class="text-[10px] font-semibold text-slate-400 uppercase tracking-wider block">Total Membros</span>
            <span class="text-2xl font-black text-brand-300 mt-1 block">{{ $totalMembers }}</span>
            <span class="text-[10px] text-slate-400 block mt-0.5">no rol geral</span>
        </div>

        <!-- Card 3: Membros Ativos -->
        <div class="bg-slate-900/60 p-4 rounded-2xl border border-slate-800/80 hover:border-slate-700 transition-all shadow-lg">
            <span class="text-[10px] font-semibold text-emerald-400 uppercase tracking-wider block">Membros Ativos</span>
            <span class="text-2xl font-black text-emerald-300 mt-1 block">{{ $activeMembers }}</span>
            <span class="text-[10px] text-emerald-400/80 block mt-0.5">
                {{ $totalMembers > 0 ? round(($activeMembers / $totalMembers) * 100) : 0 }}% do total
            </span>
        </div>

        <!-- Card 4: Membros Inativos -->
        <div class="bg-slate-900/60 p-4 rounded-2xl border border-slate-800/80 hover:border-slate-700 transition-all shadow-lg">
            <span class="text-[10px] font-semibold text-amber-400 uppercase tracking-wider block">Inativos</span>
            <span class="text-2xl font-black text-amber-300 mt-1 block">{{ $inactiveMembers }}</span>
            <span class="text-[10px] text-amber-400/80 block mt-0.5">
                {{ $totalMembers > 0 ? round(($inactiveMembers / $totalMembers) * 100) : 0 }}% do total
            </span>
        </div>

        <!-- Card 5: Novos Membros (30 dias) -->
        <div class="bg-slate-900/60 p-4 rounded-2xl border border-slate-800/80 hover:border-slate-700 transition-all shadow-lg">
            <span class="text-[10px] font-semibold text-indigo-400 uppercase tracking-wider block">Novos (30 dias)</span>
            <span class="text-2xl font-black text-indigo-300 mt-1 block">+{{ $newMembersLast30Days }}</span>
            <span class="text-[10px] text-indigo-400/80 block mt-0.5">adesões recentes</span>
        </div>

        <!-- Card 6: Total de Secretários -->
        <div class="bg-slate-900/60 p-4 rounded-2xl border border-slate-800/80 hover:border-slate-700 transition-all shadow-lg">
            <span class="text-[10px] font-semibold text-purple-400 uppercase tracking-wider block">Secretários</span>
            <span class="text-2xl font-black text-purple-300 mt-1 block">{{ $totalSecretaries }}</span>
            <span class="text-[10px] text-purple-400/80 block mt-0.5">usuários ativos</span>
        </div>
    </div>

    <!-- SEÇÃO DE GRÁFICOS DINÂMICOS (2 GRIDS) -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

        <!-- Gráfico 1: Membros por Igreja -->
        <div class="bg-slate-900/60 p-5 rounded-2xl border border-slate-800/80 shadow-xl">
            <div class="flex items-center justify-between mb-4 pb-2 border-b border-slate-800">
                <h3 class="text-xs font-bold text-slate-200 uppercase tracking-wider flex items-center gap-2">
                    <svg class="w-4 h-4 text-brand-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                    </svg>
                    <span>Membros por Congregação (Top Igrejas)</span>
                </h3>
            </div>
            <div class="h-64 relative">
                <canvas id="chartMembersByChurch"></canvas>
            </div>
        </div>

        <!-- Gráfico 2: Situação dos Membros -->
        <div class="bg-slate-900/60 p-5 rounded-2xl border border-slate-800/80 shadow-xl">
            <div class="flex items-center justify-between mb-4 pb-2 border-b border-slate-800">
                <h3 class="text-xs font-bold text-slate-200 uppercase tracking-wider flex items-center gap-2">
                    <svg class="w-4 h-4 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 3.055A9.001 9.001 0 1020.945 13H11V3.055z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.488 9H15V3.512A9.025 9.025 0 0120.488 9z"/>
                    </svg>
                    <span>Situação Eclesiástica dos Membros</span>
                </h3>
            </div>
            <div class="h-64 relative flex items-center justify-center">
                <canvas id="chartMemberStatus"></canvas>
            </div>
        </div>

        <!-- Gráfico 3: Crescimento nos Últimos 6 Meses -->
        <div class="bg-slate-900/60 p-5 rounded-2xl border border-slate-800/80 shadow-xl">
            <div class="flex items-center justify-between mb-4 pb-2 border-b border-slate-800">
                <h3 class="text-xs font-bold text-slate-200 uppercase tracking-wider flex items-center gap-2">
                    <svg class="w-4 h-4 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
                    </svg>
                    <span>Crescimento de Membros (Últimos 6 Meses)</span>
                </h3>
            </div>
            <div class="h-64 relative">
                <canvas id="chartMonthlyGrowth"></canvas>
            </div>
        </div>

        <!-- Gráfico 4: Distribuição por Faixa Etária -->
        <div class="bg-slate-900/60 p-5 rounded-2xl border border-slate-800/80 shadow-xl">
            <div class="flex items-center justify-between mb-4 pb-2 border-b border-slate-800">
                <h3 class="text-xs font-bold text-slate-200 uppercase tracking-wider flex items-center gap-2">
                    <svg class="w-4 h-4 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                    </svg>
                    <span>Distribuição por Faixa Etária</span>
                </h3>
            </div>
            <div class="h-64 relative flex items-center justify-center">
                <canvas id="chartAgeDistribution"></canvas>
            </div>
        </div>

    </div>

    <!-- SEÇÃO 3: TABELA DE CONGREGAÇÕES E ATIVIDADES RECENTES -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        <!-- Tabela de Congregações da Regional / Autorizadas (2 ou 3 Colunas conforme perfil) -->
        <div class="{{ auth()->user()->isRegional() ? 'lg:col-span-2' : 'lg:col-span-3' }} bg-slate-900/60 rounded-2xl border border-slate-800/80 overflow-hidden shadow-xl">
            <div class="p-5 border-b border-slate-800 flex items-center justify-between">
                <h3 class="text-xs font-bold text-slate-200 uppercase tracking-wider">
                    Congregações Autorizadas (Visão Geral)
                </h3>
                <a href="{{ route('churches.index') }}" class="text-xs font-semibold text-brand-400 hover:text-brand-300 transition-colors">
                    Ver Todas →
                </a>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs text-slate-300">
                    <thead class="bg-slate-950/60 text-[11px] font-semibold text-slate-400 uppercase tracking-wider border-b border-slate-800">
                        <tr>
                            <th class="py-3 px-4">Igreja</th>
                            <th class="py-3 px-4">Código</th>
                            <th class="py-3 px-4 text-center">Membros</th>
                            <th class="py-3 px-4 text-center">Secretários</th>
                            <th class="py-3 px-4">Status</th>
                            <th class="py-3 px-4 text-right">Ações</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-800/60">
                        @forelse($dashboardChurches as $church)
                        <tr class="hover:bg-slate-800/30 transition-colors">
                            <td class="py-3 px-4 font-semibold text-white">
                                <a href="{{ route('churches.show', $church) }}" class="hover:text-brand-400 transition-colors">
                                    {{ $church->name }}
                                </a>
                                <span class="text-[10px] text-slate-400 block">{{ $church->city }}/{{ $church->state }}</span>
                            </td>
                            <td class="py-3 px-4 font-mono text-brand-400 text-[11px]">
                                {{ $church->code ?? 'S/C' }}
                            </td>
                            <td class="py-3 px-4 text-center font-bold text-white">
                                {{ $church->members_count }}
                            </td>
                            <td class="py-3 px-4 text-center font-bold text-indigo-300">
                                {{ $church->users_count }}
                            </td>
                            <td class="py-3 px-4">
                                @if($church->status === 'active')
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-medium bg-emerald-950 text-emerald-300 border border-emerald-500/30">Ativa</span>
                                @else
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-medium bg-amber-950 text-amber-300 border border-amber-500/30">Inativa</span>
                                @endif
                            </td>
                            <td class="py-3 px-4 text-right">
                                <a href="{{ route('churches.show', $church) }}" class="px-2.5 py-1 bg-slate-800 hover:bg-slate-700 text-slate-200 text-[11px] font-medium rounded-lg border border-slate-700 transition-colors">
                                    Acessar
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="py-6 text-center text-slate-500">
                                Nenhuma congregação acessível no momento.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        @if(auth()->user()->isRegional())
        <!-- Atividades Recentes de Auditoria (1 Coluna - Exclusivo Regional) -->
        <div class="bg-slate-900/60 rounded-2xl border border-slate-800/80 p-5 shadow-xl space-y-4">
            <h3 class="text-xs font-bold text-slate-200 uppercase tracking-wider pb-2 border-b border-slate-800 flex items-center justify-between">
                <span>Atividades Recentes</span>
                <span class="text-[10px] text-brand-400 font-semibold">Auditoria</span>
            </h3>

            <div class="space-y-3">
                @forelse($recentActivities as $activity)
                <div class="p-3 bg-slate-950/60 border border-slate-800/80 rounded-xl text-xs space-y-1">
                    <div class="flex items-center justify-between">
                        <span class="font-semibold text-white text-[11px]">{{ $activity->user->name ?? 'Sistema' }}</span>
                        <span class="text-[10px] text-slate-500">{{ $activity->created_at->diffForHumans() }}</span>
                    </div>
                    <p class="text-slate-300 text-[11px] leading-snug">{{ $activity->description }}</p>
                    @if($activity->church)
                        <span class="inline-block text-[9px] text-brand-400 bg-brand-950/40 px-1.5 py-0.2 rounded border border-brand-500/20">
                            {{ $activity->church->name }}
                        </span>
                    @endif
                </div>
                @empty
                <p class="text-xs text-slate-500 text-center py-6">Nenhuma atividade recente registrada.</p>
                @endforelse
            </div>
        </div>
        @endif

    </div>

</div>

<!-- SCRIPT DE INICIALIZAÇÃO DOS GRÁFICOS (CHART.JS) -->
<script>
document.addEventListener('DOMContentLoaded', function () {
    // Paleta de Cores do Tema Escuro
    const colors = {
        brand: '#0c8fe9',
        emerald: '#10b981',
        indigo: '#6366f1',
        amber: '#f59e0b',
        purple: '#a855f7',
        red: '#ef4444',
        slateText: '#94a3b8',
        slateGrid: '#1e293b'
    };

    // 1. Gráfico Membros por Igreja
    new Chart(document.getElementById('chartMembersByChurch'), {
        type: 'bar',
        data: {
            labels: {!! json_encode($chartMembersByChurchLabels) !!},
            datasets: [{
                label: 'Total de Membros',
                data: {!! json_encode($chartMembersByChurchData) !!},
                backgroundColor: 'rgba(12, 143, 233, 0.7)',
                borderColor: '#0c8fe9',
                borderWidth: 1.5,
                borderRadius: 8,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false }
            },
            scales: {
                x: {
                    grid: { color: colors.slateGrid },
                    ticks: { color: colors.slateText, font: { size: 10 } }
                },
                y: {
                    beginAtZero: true,
                    grid: { color: colors.slateGrid },
                    ticks: { color: colors.slateText, font: { size: 10 }, precision: 0 }
                }
            }
        }
    });

    // 2. Gráfico Situação dos Membros
    new Chart(document.getElementById('chartMemberStatus'), {
        type: 'doughnut',
        data: {
            labels: {!! json_encode($chartStatusLabels) !!},
            datasets: [{
                data: {!! json_encode($chartStatusData) !!},
                backgroundColor: [
                    '#10b981', // Ativos
                    '#f59e0b', // Inativos
                    '#6366f1', // Transferidos
                    '#ec4899', // Disciplina
                    '#64748b'  // Falecidos
                ],
                borderWidth: 2,
                borderColor: '#0f172a'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'right',
                    labels: { color: colors.slateText, font: { size: 10 }, boxWidth: 12 }
                }
            },
            cutout: '70%'
        }
    });

    // 3. Gráfico Crescimento Mensal
    new Chart(document.getElementById('chartMonthlyGrowth'), {
        type: 'line',
        data: {
            labels: {!! json_encode($chartMonthlyLabels) !!},
            datasets: [{
                label: 'Novas Entradas',
                data: {!! json_encode($chartMonthlyData) !!},
                borderColor: '#6366f1',
                backgroundColor: 'rgba(99, 102, 241, 0.15)',
                borderWidth: 2.5,
                tension: 0.3,
                fill: true,
                pointBackgroundColor: '#6366f1',
                pointRadius: 4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false }
            },
            scales: {
                x: {
                    grid: { color: colors.slateGrid },
                    ticks: { color: colors.slateText, font: { size: 10 } }
                },
                y: {
                    beginAtZero: true,
                    grid: { color: colors.slateGrid },
                    ticks: { color: colors.slateText, font: { size: 10 }, precision: 0 }
                }
            }
        }
    });

    // 4. Gráfico Faixa Etária
    new Chart(document.getElementById('chartAgeDistribution'), {
        type: 'doughnut',
        data: {
            labels: {!! json_encode($chartAgeLabels) !!},
            datasets: [{
                data: {!! json_encode($chartAgeData) !!},
                backgroundColor: [
                    '#38bdf8', // Crianças
                    '#a855f7', // Adolescentes
                    '#f43f5e', // Jovens
                    '#3b82f6', // Adultos
                    '#eab308'  // Idosos
                ],
                borderWidth: 2,
                borderColor: '#0f172a'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'right',
                    labels: { color: colors.slateText, font: { size: 10 }, boxWidth: 12 }
                }
            },
            cutout: '65%'
        }
    });
});
</script>
@endsection
