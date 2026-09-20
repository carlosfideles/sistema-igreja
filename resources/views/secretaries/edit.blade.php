@extends('layouts.app')

@section('title', 'Editar Secretário')
@section('header', 'Editar Secretário')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">

    <!-- Cabeçalho -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-xl font-bold text-white tracking-tight">Editar Secretário: {{ $secretary->name }}</h1>
            <p class="text-xs text-slate-400 mt-1">Atualize os dados cadastrais, cargo, abrangência e congregações autorizadas.</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('secretaries.show', $secretary) }}" class="px-3.5 py-2 bg-slate-800 hover:bg-slate-700 text-slate-300 text-xs font-semibold rounded-xl border border-slate-700 transition-colors">
                Ver Ficha
            </a>
            <a href="{{ route('secretaries.index') }}" class="px-3.5 py-2 bg-slate-900 hover:bg-slate-800 text-slate-300 text-xs font-semibold rounded-xl border border-slate-800 transition-colors">
                Voltar à Lista
            </a>
        </div>
    </div>

    @if($secretary->isFirstSecretaryRegional())
    <div class="p-4 rounded-2xl bg-amber-950/40 border border-amber-500/30 text-amber-200 text-xs flex items-center gap-3">
        <svg class="w-5 h-5 text-amber-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
        </svg>
        <span><strong>Atenção:</strong> Este é o usuário com autoridade máxima (**1º Secretário Regional**). Seu cargo, abrangência e status são protegidos pelo sistema e não podem ser rebaixados.</span>
    </div>
    @endif

    <!-- Formulário -->
    <form method="POST" action="{{ route('secretaries.update', $secretary) }}" autocomplete="off" class="bg-slate-900/60 p-6 md:p-8 rounded-2xl border border-slate-800/80 space-y-8 shadow-xl">
        @csrf
        @method('PUT')

        <!-- Prevenção de Autopreenchimento Indesejado do Navegador -->
        <input type="text" style="display:none">
        <input type="password" style="display:none">

        <!-- SEÇÃO 1: DADOS PESSOAIS -->
        <div>
            <h2 class="text-sm font-bold text-brand-400 uppercase tracking-wider mb-4 pb-2 border-b border-slate-800">
                1. Dados de Identificação
            </h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <!-- Nome -->
                <div class="sm:col-span-2">
                    <label for="name" class="block text-xs font-medium text-slate-300 mb-1.5">Nome Completo *</label>
                    <input type="text" name="name" id="name" value="{{ old('name', $secretary->name) }}" required
                        class="w-full px-3.5 py-2.5 bg-slate-950/80 border border-slate-800 rounded-xl text-xs text-slate-100 placeholder-slate-500 focus:outline-none focus:ring-1 focus:ring-brand-500">
                </div>

                <!-- E-mail -->
                <div>
                    <label for="email" class="block text-xs font-medium text-slate-300 mb-1.5">E-mail Institucional *</label>
                    <input type="email" name="email" id="email" value="{{ old('email', $secretary->email) }}" required autocomplete="new-email"
                        class="w-full px-3.5 py-2.5 bg-slate-950/80 border border-slate-800 rounded-xl text-xs text-slate-100 placeholder-slate-500 focus:outline-none focus:ring-1 focus:ring-brand-500">
                </div>

                <!-- Nova Senha -->
                <div>
                    <label for="password" class="block text-xs font-medium text-slate-300 mb-1.5">Nova Senha (Deixe em branco para manter a atual)</label>
                    <input type="password" name="password" id="password" value="" autocomplete="new-password"
                        placeholder="••••••••••••"
                        class="w-full px-3.5 py-2.5 bg-slate-950/80 border border-slate-800 rounded-xl text-xs text-slate-100 placeholder-slate-500 focus:outline-none focus:ring-1 focus:ring-brand-500">
                </div>

                <!-- CPF -->
                <div>
                    <label for="cpf" class="block text-xs font-medium text-slate-300 mb-1.5">CPF</label>
                    <input type="text" name="cpf" id="cpf" value="{{ old('cpf', $secretary->cpf) }}"
                        class="w-full px-3.5 py-2.5 bg-slate-950/80 border border-slate-800 rounded-xl text-xs text-slate-100 placeholder-slate-500 focus:outline-none focus:ring-1 focus:ring-brand-500">
                </div>

                <!-- Telefone / WhatsApp -->
                <div>
                    <label for="phone" class="block text-xs font-medium text-slate-300 mb-1.5">Telefone / WhatsApp</label>
                    <input type="text" name="phone" id="phone" value="{{ old('phone', $secretary->phone) }}"
                        class="w-full px-3.5 py-2.5 bg-slate-950/80 border border-slate-800 rounded-xl text-xs text-slate-100 placeholder-slate-500 focus:outline-none focus:ring-1 focus:ring-brand-500">
                </div>
            </div>
        </div>

        <!-- SEÇÃO 2: PERFIL E ABRANGÊNCIA -->
        <div>
            <h2 class="text-sm font-bold text-brand-400 uppercase tracking-wider mb-4 pb-2 border-b border-slate-800">
                2. Acesso, Papel e Abrangência
            </h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <!-- Classificação / Papel -->
                <div>
                    <label for="role_id" class="block text-xs font-medium text-slate-300 mb-1.5">Classificação do Secretário *</label>
                    <select name="role_id" id="role_id" required {{ $secretary->isFirstSecretaryRegional() ? 'disabled' : '' }} class="w-full px-3.5 py-2.5 bg-slate-950/80 border border-slate-800 rounded-xl text-xs text-slate-100 focus:outline-none focus:ring-1 focus:ring-brand-500 disabled:opacity-50">
                        @foreach($roles as $role)
                            <option value="{{ $role->id }}" {{ old('role_id', $secretary->role_id) == $role->id ? 'selected' : '' }}>
                                {{ $role->name }} (Nível {{ $role->level }})
                            </option>
                        @endforeach
                    </select>
                    @if($secretary->isFirstSecretaryRegional())
                        <input type="hidden" name="role_id" value="{{ $secretary->role_id }}">
                    @endif
                </div>

                <!-- Status da Conta -->
                <div>
                    <label for="status" class="block text-xs font-medium text-slate-300 mb-1.5">Situação da Conta *</label>
                    <select name="status" id="status" required {{ $secretary->isFirstSecretaryRegional() ? 'disabled' : '' }} class="w-full px-3.5 py-2.5 bg-slate-950/80 border border-slate-800 rounded-xl text-xs text-slate-100 focus:outline-none focus:ring-1 focus:ring-brand-500 disabled:opacity-50">
                        <option value="active" {{ old('status', $secretary->status) === 'active' ? 'selected' : '' }}>Ativo (Acesso Liberado)</option>
                        <option value="inactive" {{ old('status', $secretary->status) === 'inactive' ? 'selected' : '' }}>Inativo</option>
                        <option value="blocked" {{ old('status', $secretary->status) === 'blocked' ? 'selected' : '' }}>Bloqueado</option>
                    </select>
                    @if($secretary->isFirstSecretaryRegional())
                        <input type="hidden" name="status" value="active">
                    @endif
                </div>

                <!-- Abrangência -->
                <div class="sm:col-span-2">
                    <label class="block text-xs font-medium text-slate-300 mb-2">Nível de Abrangência *</label>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        @if($currentUser->isRegional())
                        <label class="flex items-start gap-3 p-4 rounded-xl border border-slate-800 bg-slate-950/40 hover:bg-slate-950/80 cursor-pointer transition-all has-[:checked]:border-indigo-500 has-[:checked]:bg-indigo-950/20">
                            <input type="radio" name="scope" value="REGIONAL" id="scope_regional" {{ old('scope', $secretary->scope) === 'REGIONAL' ? 'checked' : '' }} onchange="toggleScopeUI()" class="mt-0.5 text-indigo-600 focus:ring-indigo-500 bg-slate-900 border-slate-700">
                            <div>
                                <span class="text-xs font-bold text-white block">Abrangência REGIONAL</span>
                                <span class="text-[11px] text-slate-400 block mt-0.5 leading-relaxed">
                                    Acesso automático a todas as congregações existentes e futuras.
                                </span>
                            </div>
                        </label>
                        @endif

                        @if(!$secretary->isFirstSecretaryRegional())
                        <label class="flex items-start gap-3 p-4 rounded-xl border border-slate-800 bg-slate-950/40 hover:bg-slate-950/80 cursor-pointer transition-all has-[:checked]:border-emerald-500 has-[:checked]:bg-emerald-950/20">
                            <input type="radio" name="scope" value="LOCAL" id="scope_local" {{ old('scope', $secretary->scope) === 'LOCAL' ? 'checked' : '' }} onchange="toggleScopeUI()" class="mt-0.5 text-emerald-600 focus:ring-emerald-500 bg-slate-900 border-slate-700">
                            <div>
                                <span class="text-xs font-bold text-white block">Abrangência LOCAL</span>
                                <span class="text-[11px] text-slate-400 block mt-0.5 leading-relaxed">
                                    Acesso estrito apenas às congregações especificamente selecionadas abaixo.
                                </span>
                            </div>
                        </label>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- SEÇÃO 3: SELEÇÃO DE CONGREGAÇÕES (SE LOCAL) -->
        <div id="churches-section" class="transition-all duration-300">
            <h2 class="text-sm font-bold text-brand-400 uppercase tracking-wider mb-2 pb-2 border-b border-slate-800">
                3. Congregações Autorizadas (Para Abrangência Local)
            </h2>
            <p class="text-xs text-slate-400 mb-4">Marque as igrejas que este secretário terá permissão de acessar.</p>

            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-2.5 max-h-64 overflow-y-auto p-3 bg-slate-950/60 rounded-xl border border-slate-800">
                @forelse($churches as $church)
                    <label class="flex items-center gap-2.5 p-2.5 rounded-lg bg-slate-900/80 border border-slate-800/80 hover:border-slate-700 cursor-pointer select-none text-xs text-slate-200">
                        <input type="checkbox" name="churches[]" value="{{ $church->id }}" {{ in_array($church->id, old('churches', $selectedChurchIds)) ? 'checked' : '' }} class="w-4 h-4 rounded text-brand-600 bg-slate-950 border-slate-700 focus:ring-brand-500">
                        <span class="truncate" title="{{ $church->name }}">{{ $church->name }}</span>
                    </label>
                @empty
                    <p class="text-xs text-slate-500 sm:col-span-3 py-4 text-center">Nenhuma congregação ativa encontrada.</p>
                @endforelse
            </div>
        </div>

        <!-- AVISO DE ACESSO AUTOMÁTICO (SE REGIONAL) -->
        <div id="regional-notice" class="hidden p-4 rounded-xl bg-indigo-950/40 border border-indigo-500/30 text-indigo-200 text-xs flex items-center gap-3">
            <svg class="w-5 h-5 text-indigo-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <span><strong>Acesso Amplo:</strong> Usuários com abrangência Regional possuem acesso irrestrito a todas as congregações da Regional.</span>
        </div>

        <!-- Botões de Ação -->
        <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-800">
            <a href="{{ route('secretaries.index') }}" class="px-4 py-2.5 bg-slate-800 hover:bg-slate-700 text-slate-300 text-xs font-semibold rounded-xl border border-slate-700/60 transition-colors">
                Cancelar
            </a>
            <button type="submit" class="px-6 py-2.5 bg-gradient-to-r from-brand-600 to-blue-600 hover:from-brand-500 hover:to-blue-500 text-white text-xs font-semibold rounded-xl shadow-lg shadow-brand-600/30 hover:shadow-brand-600/50 transition-all duration-200">
                Salvar Alterações
            </button>
        </div>
    </form>

</div>

<script>
    function toggleScopeUI() {
        const regionalRadio = document.getElementById('scope_regional');
        const isRegional = regionalRadio && regionalRadio.checked;
        const churchesSection = document.getElementById('churches-section');
        const regionalNotice = document.getElementById('regional-notice');

        if (isRegional) {
            churchesSection.classList.add('hidden');
            regionalNotice.classList.remove('hidden');
        } else {
            churchesSection.classList.remove('hidden');
            regionalNotice.classList.add('hidden');
        }
    }

    document.addEventListener('DOMContentLoaded', toggleScopeUI);
</script>
@endsection
