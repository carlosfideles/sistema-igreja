@extends('layouts.guest')

@section('content')
<div class="relative bg-slate-800/80 backdrop-blur-xl border border-slate-700/60 shadow-2xl rounded-2xl p-8 sm:p-10 transition-all duration-300">
    <!-- Efeito de brilho de fundo -->
    <div class="absolute -top-12 -left-12 w-32 h-32 bg-brand-500/10 rounded-full blur-3xl pointer-events-none"></div>
    <div class="absolute -bottom-12 -right-12 w-32 h-32 bg-blue-600/10 rounded-full blur-3xl pointer-events-none"></div>

    <!-- Cabeçalho do Formulário com Logo Oficial (Regra 27.1) -->
    <div class="text-center mb-8">
        <div class="flex justify-center mb-4">
            <img src="/assets/images/logo.png" alt="SISTEMA DE GESTÃO REGIONAL" class="h-20 w-auto max-w-[220px] object-contain drop-shadow-md" onerror="this.style.display='none'; document.getElementById('logo-fallback').classList.remove('hidden');">
            <div id="logo-fallback" class="hidden flex items-center justify-center w-16 h-16 rounded-2xl bg-gradient-to-tr from-brand-700 to-brand-500 shadow-lg shadow-brand-500/20 text-white font-black text-2xl tracking-wider">
                IEADM
            </div>
        </div>
        <h1 class="text-2xl font-bold tracking-tight text-white">SISTEMA DE GESTÃO</h1>
        <p class="text-xs font-semibold tracking-widest text-brand-400 uppercase mt-1">IGREJA EVANGÉLICA ASSEMBLEIA DE DEUS DA MISSÃO • Regional - DF</p>
        <p class="text-sm text-slate-400 mt-2">Acesso restrito a secretários e administradores</p>
    </div>

    <!-- Mensagens de Erro / Alertas -->
    @if ($errors->any())
        <div class="mb-6 p-4 rounded-xl bg-red-950/50 border border-red-500/30 text-red-300 text-sm flex items-start gap-3">
            <svg class="w-5 h-5 text-red-400 mt-0.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
            </svg>
            <div>
                <span class="font-semibold block mb-1">Atenção</span>
                @foreach ($errors->all() as $error)
                    <p class="text-xs leading-relaxed">{{ $error }}</p>
                @endforeach
            </div>
        </div>
    @endif

    @if (session('status'))
        <div class="mb-6 p-4 rounded-xl bg-emerald-950/50 border border-emerald-500/30 text-emerald-300 text-sm">
            {{ session('status') }}
        </div>
    @endif

    <!-- Formulário de Login -->
    <form method="POST" action="{{ route('login') }}" class="space-y-5">
        @csrf

        <!-- Campo E-mail -->
        <div>
            <label for="email" class="block text-xs font-medium text-slate-300 uppercase tracking-wider mb-2">E-mail Institucional</label>
            <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.206"/>
                    </svg>
                </div>
                <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="username"
                    placeholder="seu.email@ieadm-df.org.br"
                    class="w-full pl-11 pr-4 py-2.5 bg-slate-900/80 border border-slate-700 rounded-xl text-slate-100 placeholder-slate-500 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-transparent transition-all">
            </div>
        </div>

        <!-- Campo Senha -->
        <div>
            <div class="flex items-center justify-between mb-2">
                <label for="password" class="block text-xs font-medium text-slate-300 uppercase tracking-wider">Senha</label>
            </div>
            <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                    </svg>
                </div>
                <input id="password" type="password" name="password" required autocomplete="current-password"
                    placeholder="••••••••••••"
                    class="w-full pl-11 pr-4 py-2.5 bg-slate-900/80 border border-slate-700 rounded-xl text-slate-100 placeholder-slate-500 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-transparent transition-all">
            </div>
        </div>

        <!-- Lembrar-me -->
        <div class="flex items-center justify-between pt-1">
            <label class="flex items-center text-xs text-slate-400 hover:text-slate-300 cursor-pointer select-none">
                <input type="checkbox" name="remember" class="w-4 h-4 rounded border-slate-700 bg-slate-900 text-brand-600 focus:ring-brand-500 focus:ring-offset-slate-900">
                <span class="ml-2">Manter conectado</span>
            </label>
        </div>

        <!-- Botão de Entrar -->
        <div class="pt-2">
            <button type="submit" class="w-full py-3 px-4 bg-gradient-to-r from-brand-600 to-blue-600 hover:from-brand-500 hover:to-blue-500 text-white font-semibold rounded-xl text-sm shadow-lg shadow-brand-600/30 hover:shadow-brand-600/50 active:scale-[0.99] transition-all duration-200 flex items-center justify-center gap-2">
                <span>Acessar Painel</span>
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
                </svg>
            </button>
        </div>
    </form>

    <!-- Rodapé de Segurança e Direitos -->
    <div class="mt-8 pt-6 border-t border-slate-700/50 text-center text-xs text-slate-500">
        <p>Ambiente protegido e monitorado por auditoria.</p>
        <p class="mt-1">© {{ date('Y') }} IGREJA EVANGÉLICA ASSEMBLEIA DE DEUS DA MISSÃO • Todos os direitos reservados</p>
    </div>
</div>
@endsection
