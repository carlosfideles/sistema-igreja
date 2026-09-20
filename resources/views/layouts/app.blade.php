<!DOCTYPE html>
<html lang="pt-BR" class="h-full bg-slate-950">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Painel') — {{ config('app.name', 'IGREJA EVANGÉLICA ASSEMBLEIA DE DEUS DA MISSÃO') }}</title>

    <!-- Google Fonts: Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    colors: {
                        brand: {
                            50: '#f0f7ff',
                            100: '#e0effe',
                            200: '#bae0fd',
                            300: '#7cc7fb',
                            400: '#36abf7',
                            500: '#0c8fe9',
                            600: '#0171c7',
                            700: '#025aa2',
                            800: '#064d85',
                            900: '#0b416e',
                            950: '#072949',
                        }
                    },
                    fontFamily: {
                        sans: ['Inter', 'sans-serif'],
                    }
                }
            }
        }
    </script>
    <style>
        body {
            font-family: 'Inter', sans-serif;
        }
        /* Custom scrollbar */
        ::-webkit-scrollbar {
            width: 6px;
            height: 6px;
        }
        ::-webkit-scrollbar-track {
            background: #0f172a;
        }
        ::-webkit-scrollbar-thumb {
            background: #334155;
            border-radius: 4px;
        }
        ::-webkit-scrollbar-thumb:hover {
            background: #475569;
        }
    </style>
</head>
<body class="h-full bg-slate-950 text-slate-100 antialiased flex flex-col md:flex-row min-h-screen">

    <!-- Mobile Top Header (Visível apenas em telas pequenas) -->
    <div class="md:hidden flex items-center justify-between px-4 py-3 bg-slate-900 border-b border-slate-800 z-30 shrink-0">
        <div class="flex items-center gap-2.5">
            <img src="/assets/images/logo.png" alt="SISTEMA DE GESTÃO REGIONAL" class="h-8 w-auto max-w-[90px] object-contain drop-shadow" onerror="this.style.display='none'; document.getElementById('mobile-logo-fallback').classList.remove('hidden');">
            <div id="mobile-logo-fallback" class="hidden flex items-center justify-center w-8 h-8 rounded-lg bg-gradient-to-tr from-brand-700 to-brand-500 text-white font-bold text-xs tracking-wider shadow-sm shrink-0">
                DF
            </div>
            <div class="min-w-0 flex-1">
                <span class="text-xs font-bold text-white tracking-wide block whitespace-normal break-words leading-tight">IGREJA EVANGÉLICA ASSEMBLEIA DE DEUS DA MISSÃO</span>
                <p class="text-[9px] font-semibold text-brand-400 uppercase tracking-widest whitespace-normal break-words leading-tight mt-0.5">
                    @if(auth()->check() && (auth()->user()->isRegional() || auth()->user()->isFirstSecretaryRegional()))
                        Regional - DF
                    @elseif(auth()->check() && auth()->user()->churches->count() === 1)
                        {{ auth()->user()->churches->first()->name }}
                    @elseif(auth()->check() && auth()->user()->churches->count() >= 2)
                        Congregação Subsede
                    @else
                        Regional - DF
                    @endif
                </p>
            </div>
        </div>
        <button id="mobile-menu-toggle" type="button" class="p-2 rounded-lg bg-slate-800 text-slate-300 hover:text-white hover:bg-slate-700 transition-colors focus:outline-none" aria-label="Menu principal">
            <svg id="menu-icon-open" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
            </svg>
            <svg id="menu-icon-close" class="w-5 h-5 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
        </button>
    </div>

    <!-- Sidebar de Navegação -->
    <aside id="app-sidebar" class="hidden md:flex w-full md:w-64 bg-slate-900/90 border-r border-slate-800/80 flex-col shrink-0">
        <!-- Logo e Cabeçalho Oficial (Regra 27.1) -->
        <div class="p-5 border-b border-slate-800 hidden md:flex flex-col items-center justify-center text-center gap-3">
            <div class="flex items-center justify-center">
                <img src="/assets/images/logo.png" alt="SISTEMA DE GESTÃO REGIONAL" class="h-12 w-auto max-w-[130px] object-contain drop-shadow" onerror="this.style.display='none'; document.getElementById('sidebar-logo-fallback').classList.remove('hidden');">
                <div id="sidebar-logo-fallback" class="hidden flex items-center justify-center w-12 h-12 rounded-xl bg-gradient-to-tr from-brand-700 to-brand-500 text-white font-bold text-sm tracking-wider shadow-md shadow-brand-600/30">
                    DF
                </div>
            </div>
            <div class="w-full text-center">
                <h1 class="text-xs font-bold text-white tracking-wide text-center whitespace-normal break-words leading-snug">IGREJA EVANGÉLICA ASSEMBLEIA DE DEUS DA MISSÃO</h1>
                <p class="text-[10px] font-semibold text-brand-400 uppercase tracking-widest text-center whitespace-normal break-words leading-tight mt-1">
                    @if(auth()->check() && (auth()->user()->isRegional() || auth()->user()->isFirstSecretaryRegional()))
                        Regional - DF
                    @elseif(auth()->check() && auth()->user()->churches->count() === 1)
                        {{ auth()->user()->churches->first()->name }}
                    @elseif(auth()->check() && auth()->user()->churches->count() >= 2)
                        Congregação Subsede
                    @else
                        Regional - DF
                    @endif
                </p>
            </div>
        </div>

        <!-- Perfil do Usuário Logado -->
        <div class="p-3 border-b border-slate-800/60 bg-slate-950/40">
            <div class="flex items-center gap-3 p-3 rounded-xl bg-slate-800/50 border border-slate-700/50">
                @if(auth()->check() && auth()->user()->avatar_url)
                    <img src="{{ auth()->user()->avatar_url }}" alt="{{ auth()->user()->name }}" class="w-10 h-10 rounded-full object-cover shrink-0 border border-slate-600 shadow-sm">
                @else
                    <div class="w-10 h-10 rounded-full bg-gradient-to-tr from-slate-700 to-slate-600 flex items-center justify-center font-bold text-sm text-brand-300 shrink-0 border border-slate-600 shadow-sm">
                        {{ strtoupper(substr(auth()->user()->name ?? 'U', 0, 2)) }}
                    </div>
                @endif
                <div class="flex flex-col min-w-0 flex-1">
                    <p class="truncate text-sm font-semibold text-white" title="{{ auth()->user()->name ?? 'Usuário' }}">{{ auth()->user()->name ?? 'Usuário' }}</p>
                    <div class="flex items-center gap-1.5 mt-0.5">
                        <span class="text-[11px] text-slate-400 truncate">{{ auth()->user()->role?->name ?? 'Secretário' }}</span>
                        <span class="inline-flex items-center px-1.5 py-0.2 rounded text-[9px] font-bold tracking-wider shrink-0 {{ auth()->user()->isRegional() ? 'bg-indigo-950 text-indigo-300 border border-indigo-500/40' : 'bg-emerald-950 text-emerald-300 border border-emerald-500/40' }}">
                            {{ auth()->user()->scope }}
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Menu de Navegação -->
        <nav class="flex-1 p-4 space-y-1.5 overflow-y-auto">
            <a href="{{ route('dashboard') }}" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-medium transition-all {{ request()->routeIs('dashboard') ? 'bg-brand-600/20 text-brand-300 border border-brand-500/30 shadow-sm' : 'text-slate-400 hover:text-slate-200 hover:bg-slate-800/60' }}">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                </svg>
                <span>Dashboard</span>
            </a>

            @if(auth()->user()->hasPermission('secretaries.view'))
            <a href="{{ route('secretaries.index') }}" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-medium transition-all {{ request()->routeIs('secretaries.*') ? 'bg-brand-600/20 text-brand-300 border border-brand-500/30 shadow-sm' : 'text-slate-400 hover:text-slate-200 hover:bg-slate-800/60' }}">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                </svg>
                <span>Secretários</span>
            </a>
            @endif

            @if(auth()->user()->hasPermission('churches.view'))
            <a href="{{ route('churches.index') }}" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-medium transition-all {{ request()->routeIs('churches.*') ? 'bg-brand-600/20 text-brand-300 border border-brand-500/30 shadow-sm' : 'text-slate-400 hover:text-slate-200 hover:bg-slate-800/60' }}">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                </svg>
                <span>Igrejas</span>
            </a>
            @endif

            @if(auth()->user()->hasPermission('members.view'))
            <a href="{{ route('members.index') }}" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-medium transition-all {{ request()->routeIs('members.*') ? 'bg-brand-600/20 text-brand-300 border border-brand-500/30 shadow-sm' : 'text-slate-400 hover:text-slate-200 hover:bg-slate-800/60' }}">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                </svg>
                <span>Membros</span>
            </a>
            @endif

            @if(auth()->user()->hasPermission('members.transfer'))
            <a href="{{ route('transfers.index') }}" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-medium transition-all {{ request()->routeIs('transfers.*') || request()->routeIs('members.transfer.*') ? 'bg-brand-600/20 text-brand-300 border border-brand-500/30 shadow-sm' : 'text-slate-400 hover:text-slate-200 hover:bg-slate-800/60' }}">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/>
                </svg>
                <span>Transferências</span>
            </a>
            @endif

            @if(auth()->user()->hasPermission('finance.view'))
            <a href="{{ route('finance.index') }}" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-medium transition-all {{ request()->routeIs('finance.*') ? 'bg-brand-600/20 text-brand-300 border border-brand-500/30 shadow-sm' : 'text-slate-400 hover:text-slate-200 hover:bg-slate-800/60' }}">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <span>Financeiro</span>
            </a>
            @endif

            @if(auth()->user()->hasPermission('reports.view'))
            <a href="{{ route('reports.index') }}" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-medium transition-all {{ request()->routeIs('reports.*') ? 'bg-brand-600/20 text-brand-300 border border-brand-500/30 shadow-sm' : 'text-slate-400 hover:text-slate-200 hover:bg-slate-800/60' }}">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                <span>Relatórios</span>
            </a>
            @endif

            @if(auth()->check() && auth()->user()->isRegional())
            <a href="{{ route('audit.index') }}" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-medium transition-all {{ request()->routeIs('audit.*') ? 'bg-brand-600/20 text-brand-300 border border-brand-500/30 shadow-sm' : 'text-slate-400 hover:text-slate-200 hover:bg-slate-800/60' }}">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                </svg>
                <span>Auditoria</span>
            </a>
            <a href="{{ route('settings.index') }}" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-medium transition-all {{ request()->routeIs('settings.*') ? 'bg-brand-600/20 text-brand-300 border border-brand-500/30 shadow-sm' : 'text-slate-400 hover:text-slate-200 hover:bg-slate-800/60' }}">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                </svg>
                <span>Configurações</span>
            </a>
            @endif
        </nav>

        <!-- Botão de Sair -->
        <div class="p-4 border-t border-slate-800/80">
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="w-full flex items-center justify-center gap-2 px-3 py-2 text-xs font-medium text-red-400 hover:text-red-300 hover:bg-red-950/40 rounded-xl transition-all border border-red-900/30">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                    </svg>
                    <span>Sair do Sistema</span>
                </button>
            </form>
        </div>
    </aside>

    <!-- Conteúdo Principal -->
    <main class="flex-1 flex flex-col min-w-0 overflow-y-auto">
        <!-- Topbar -->
        <header class="min-h-16 bg-slate-900/50 backdrop-blur-md border-b border-slate-800/80 px-6 py-3 flex items-center justify-between gap-4 shrink-0">
            <div class="flex items-center gap-3">
                <h2 class="text-base font-semibold text-white">@yield('header', 'Painel')</h2>
            </div>
            <div class="flex items-center gap-3 text-right">
                <span class="text-xs text-slate-300 hidden sm:inline whitespace-normal break-words leading-tight font-medium">
                    @if(auth()->check() && (auth()->user()->isRegional() || auth()->user()->isFirstSecretaryRegional()))
                        Regional - DF
                    @elseif(auth()->check() && auth()->user()->churches->count() === 1)
                        {{ auth()->user()->churches->first()->name }}
                    @elseif(auth()->check() && auth()->user()->churches->count() >= 2)
                        Congregação Subsede
                    @else
                        Regional - DF
                    @endif
                </span>
                @if(auth()->check())
                    <div class="flex items-center gap-2 pl-2 sm:border-l sm:border-slate-800">
                        @if(auth()->user()->avatar_url)
                            <img src="{{ auth()->user()->avatar_url }}" alt="{{ auth()->user()->name }}" class="w-8 h-8 rounded-full object-cover ring-2 ring-brand-500/40 shadow-sm shrink-0">
                        @else
                            <div class="w-8 h-8 rounded-full bg-gradient-to-tr from-slate-700 to-slate-600 flex items-center justify-center font-bold text-xs text-brand-300 ring-2 ring-brand-500/30 shrink-0">
                                {{ strtoupper(substr(auth()->user()->name ?? 'U', 0, 2)) }}
                            </div>
                        @endif
                    </div>
                @endif
            </div>
        </header>

        <!-- Área de Conteúdo da Página -->
        <div class="p-6 md:p-8 flex-1">
            <!-- Alertas Flash com Dismiss -->
            @if(session('success'))
                <div class="flash-alert mb-6 p-4 rounded-xl bg-emerald-950/60 border border-emerald-500/30 text-emerald-300 text-sm flex items-center justify-between gap-3 shadow-lg shadow-emerald-950/30 transition-all">
                    <div class="flex items-center gap-3">
                        <svg class="w-5 h-5 text-emerald-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                        <span>{{ session('success') }}</span>
                    </div>
                    <button type="button" onclick="this.closest('.flash-alert').remove()" class="text-emerald-400 hover:text-emerald-200 transition-colors p-1" aria-label="Fechar">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
            @endif

            @if(session('error'))
                <div class="flash-alert mb-6 p-4 rounded-xl bg-red-950/60 border border-red-500/30 text-red-300 text-sm flex items-center justify-between gap-3 shadow-lg shadow-red-950/30 transition-all">
                    <div class="flex items-center gap-3">
                        <svg class="w-5 h-5 text-red-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                        <span>{{ session('error') }}</span>
                    </div>
                    <button type="button" onclick="this.closest('.flash-alert').remove()" class="text-red-400 hover:text-red-200 transition-colors p-1" aria-label="Fechar">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
            @endif

            @if ($errors->any())
                <div class="flash-alert mb-6 p-4 rounded-xl bg-red-950/60 border border-red-500/30 text-red-300 text-sm shadow-lg shadow-red-950/30 relative">
                    <button type="button" onclick="this.closest('.flash-alert').remove()" class="absolute top-3 right-3 text-red-400 hover:text-red-200 transition-colors p-1" aria-label="Fechar">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                    <div class="font-semibold mb-1 flex items-center gap-2">
                        <svg class="w-4 h-4 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <span>Erros de validação:</span>
                    </div>
                    <ul class="list-disc list-inside text-xs space-y-0.5 ml-1">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @yield('content')
        </div>
    </main>

    <!-- Modal Pop-up de Notificação de Transferência para Secretários Locais -->
    @if(isset($unreadTransferNotifications) && $unreadTransferNotifications->isNotEmpty())
        <div id="transfer-notification-overlay" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/85 backdrop-blur-sm transition-opacity duration-300">
            @foreach($unreadTransferNotifications as $index => $notif)
                <div id="transfer-notif-card-{{ $notif->id }}" class="transfer-notif-card w-full max-w-md bg-slate-900 border border-slate-800 rounded-2xl shadow-2xl overflow-hidden transition-all duration-300 transform scale-100 {{ $index === 0 ? 'flex flex-col' : 'hidden' }}" data-transfer-id="{{ $notif->id }}">
                    <!-- Header do Card com Efeito de Luz Suave -->
                    <div class="relative p-6 pb-4 flex flex-col items-center text-center border-b border-slate-800/80 bg-gradient-to-b {{ $notif->status === 'approved' ? 'from-emerald-950/30 to-slate-900' : 'from-red-950/30 to-slate-900' }}">
                        
                        <!-- Ícone / Badge de Destaque -->
                        @if($notif->status === 'approved')
                            <div class="w-16 h-16 rounded-2xl bg-emerald-500/15 border border-emerald-500/30 flex items-center justify-center text-emerald-400 mb-3.5 shadow-lg shadow-emerald-950/50">
                                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                                </svg>
                            </div>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold tracking-wider uppercase bg-emerald-950/80 text-emerald-300 border border-emerald-500/40 mb-2">
                                Solicitação Aprovada
                            </span>
                        @else
                            <div class="w-16 h-16 rounded-2xl bg-red-500/15 border border-red-500/30 flex items-center justify-center text-red-400 mb-3.5 shadow-lg shadow-red-950/50">
                                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                            </div>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold tracking-wider uppercase bg-red-950/80 text-red-300 border border-red-500/40 mb-2">
                                Solicitação Recusada
                            </span>
                        @endif

                        <h3 class="text-lg font-bold text-white tracking-wide">Atualização de Transferência</h3>
                        @if($unreadTransferNotifications->count() > 1)
                            <span class="text-[11px] text-slate-400 mt-1">Notificação {{ $index + 1 }} de {{ $unreadTransferNotifications->count() }}</span>
                        @endif
                    </div>

                    <!-- Conteúdo do Texto -->
                    <div class="p-6 text-center space-y-4">
                        <p class="text-sm text-slate-300 leading-relaxed">
                            A solicitação de {{ $notif->type === 'inactivation' ? 'inativação' : 'transferência' }} do membro <strong class="text-white font-semibold">{{ $notif->member?->full_name ?? 'Membro' }}</strong>{{ $notif->toChurch ? ' para a congregação ' . $notif->toChurch->name : '' }} foi 
                            <span class="font-bold {{ $notif->status === 'approved' ? 'text-emerald-400' : 'text-red-400' }}">
                                {{ $notif->status === 'approved' ? 'APROVADA' : 'RECUSADA' }}
                            </span>
                            pela Secretaria Regional.
                        </p>

                        @if($notif->notes || $notif->reason)
                            <div class="p-3 bg-slate-950/60 rounded-xl border border-slate-800/80 text-xs text-slate-400 text-left space-y-1">
                                @if($notif->reason)
                                    <div><strong class="text-slate-300">Motivo:</strong> {{ $notif->reason }}</div>
                                @endif
                                @if($notif->notes)
                                    <div><strong class="text-slate-300">Observações:</strong> {{ $notif->notes }}</div>
                                @endif
                            </div>
                        @endif
                    </div>

                    <!-- Botão Principal Centralizado -->
                    <div class="p-5 pt-0 flex justify-center">
                        <button type="button" 
                                onclick="acknowledgeTransferNotification({{ $notif->id }})" 
                                id="btn-ack-{{ $notif->id }}"
                                class="w-full sm:w-auto min-w-[200px] px-6 py-2.5 rounded-xl font-semibold text-sm transition-all shadow-md flex items-center justify-center gap-2 {{ $notif->status === 'approved' ? 'bg-emerald-600 hover:bg-emerald-500 text-white shadow-emerald-950/40' : 'bg-brand-600 hover:bg-brand-500 text-white shadow-brand-950/40' }} focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-offset-slate-900 focus:ring-brand-500">
                            <span>Entendido / Ciente</span>
                        </button>
                    </div>
                </div>
            @endforeach
        </div>

        <script>
            function acknowledgeTransferNotification(transferId) {
                const btn = document.getElementById('btn-ack-' + transferId);
                const currentCard = document.getElementById('transfer-notif-card-' + transferId);
                const overlay = document.getElementById('transfer-notification-overlay');
                const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

                if (btn) {
                    btn.disabled = true;
                    btn.innerHTML = `
                        <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <span>Processando...</span>
                    `;
                }

                fetch('/transfers/' + transferId + '/mark-notified', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfToken || ''
                    },
                    body: JSON.stringify({})
                })
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Falha ao registrar notificação.');
                    }
                    return response.json();
                })
                .then(data => {
                    if (currentCard) {
                        currentCard.classList.add('opacity-0', 'scale-95');
                        setTimeout(() => {
                            currentCard.remove();
                            const remainingCards = document.querySelectorAll('.transfer-notif-card');
                            if (remainingCards.length > 0) {
                                remainingCards[0].classList.remove('hidden');
                                remainingCards[0].classList.add('flex', 'flex-col');
                            } else if (overlay) {
                                overlay.classList.add('opacity-0');
                                setTimeout(() => {
                                    overlay.remove();
                                }, 300);
                            }
                        }, 250);
                    }
                })
                .catch(error => {
                    console.error('Erro ao marcar notificação:', error);
                    // Em caso de erro na rede, fecha suavemente para não travar o usuário
                    if (currentCard) {
                        currentCard.classList.add('opacity-0', 'scale-95');
                        setTimeout(() => {
                            currentCard.remove();
                            const remainingCards = document.querySelectorAll('.transfer-notif-card');
                            if (remainingCards.length > 0) {
                                remainingCards[0].classList.remove('hidden');
                                remainingCards[0].classList.add('flex', 'flex-col');
                            } else if (overlay) {
                                overlay.classList.add('opacity-0');
                                setTimeout(() => {
                                    overlay.remove();
                                }, 300);
                            }
                        }, 250);
                    }
                });
            }
        </script>
    @endif

    <!-- Script de Alternância do Menu Mobile -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const toggleBtn = document.getElementById('mobile-menu-toggle');
            const sidebar = document.getElementById('app-sidebar');
            const iconOpen = document.getElementById('menu-icon-open');
            const iconClose = document.getElementById('menu-icon-close');

            if (toggleBtn && sidebar) {
                toggleBtn.addEventListener('click', function() {
                    const isHidden = sidebar.classList.contains('hidden');
                    if (isHidden) {
                        sidebar.classList.remove('hidden');
                        sidebar.classList.add('flex');
                        iconOpen.classList.add('hidden');
                        iconClose.classList.remove('hidden');
                    } else {
                        sidebar.classList.add('hidden');
                        sidebar.classList.remove('flex');
                        iconOpen.classList.remove('hidden');
                        iconClose.classList.add('hidden');
                    }
                });
            }
        });
    </script>
</body>
</html>
