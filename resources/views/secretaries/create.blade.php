@extends('layouts.app')

@section('title', 'Cadastrar Secretário')
@section('header', 'Novo Secretário')

@php
    $membersList = \App\Models\Member::with('church')
        ->where('status', 'ativo')
        ->when(auth()->check() && auth()->user()->isLocal(), function($q) {
            $userChurchIds = auth()->user()->churches()->pluck('churches.id');
            $q->whereIn('church_id', $userChurchIds);
        })
        ->orderBy('full_name', 'asc')
        ->get();
@endphp

@section('content')
<div class="max-w-4xl mx-auto space-y-6">

    <!-- Cabeçalho -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-xl font-bold text-white tracking-tight">Cadastrar Novo Secretário</h1>
            <p class="text-xs text-slate-400 mt-1">Vincule um membro ativo para concessão de credenciais de secretário e acesso ao sistema.</p>
        </div>
        <a href="{{ route('secretaries.index') }}" class="px-3.5 py-2 bg-slate-900 hover:bg-slate-800 text-slate-300 text-xs font-semibold rounded-xl border border-slate-800 transition-colors">
            Voltar para a Lista
        </a>
    </div>

    <!-- Formulário -->
    <form method="POST" action="{{ route('secretaries.store') }}" autocomplete="off" class="bg-slate-900/60 p-6 md:p-8 rounded-2xl border border-slate-800/80 space-y-8 shadow-xl">
        @csrf

        <!-- Prevenção de Autopreenchimento Indesejado do Navegador -->
        <input type="text" style="display:none">
        <input type="password" style="display:none">

        <!-- ID do Membro Selecionado -->
        <input type="hidden" name="member_id" id="selected_member_id" value="{{ old('member_id') }}" required>

        <!-- SEÇÃO 1: DADOS PESSOAIS -->
        <div>
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 pb-2 border-b border-slate-800 mb-4">
                <h2 class="text-sm font-bold text-brand-400 uppercase tracking-wider">
                    1. Dados de Identificação (Vinculação de Membro)
                </h2>
                <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-amber-950/80 text-amber-300 border border-amber-500/30">
                    <svg class="w-3.5 h-3.5 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                    <span>Obrigatório vincular a um membro ativo</span>
                </span>
            </div>

            <!-- Banner do Membro Selecionado (Exibido dinamicamente) -->
            <div id="selected_member_banner" class="{{ old('name') ? '' : 'hidden' }} mb-4 p-4 rounded-xl bg-brand-950/40 border border-brand-500/40 flex items-center justify-between gap-3 transition-all">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-full bg-brand-600/30 text-brand-300 flex items-center justify-center font-bold text-sm border border-brand-500/40" id="selected_member_avatar">
                        ✓
                    </div>
                    <div>
                        <div class="flex items-center gap-2">
                            <span class="text-xs font-bold text-white" id="selected_member_name_display">{{ old('name', 'Membro Selecionado') }}</span>
                            <span class="px-1.5 py-0.2 rounded text-[10px] font-bold bg-emerald-950 text-emerald-300 border border-emerald-500/30">Membro Vinculado</span>
                        </div>
                        <p class="text-[11px] text-slate-400 mt-0.5" id="selected_member_info_display">Dados sincronizados com o cadastro de membros</p>
                    </div>
                </div>
                <button type="button" onclick="openSelectMemberModal()" class="px-3 py-1.5 bg-slate-800 hover:bg-slate-700 text-slate-300 hover:text-white rounded-lg border border-slate-700 text-xs font-medium transition-colors">
                    Trocar Membro
                </button>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <!-- Nome Completo (Bloqueado) + Botão de Busca -->
                <div class="sm:col-span-2">
                    <div class="flex items-center justify-between mb-1.5">
                        <label for="name" class="block text-xs font-medium text-slate-300">Nome Completo *</label>
                        <button type="button" onclick="openSelectMemberModal()" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-brand-600/20 hover:bg-brand-600/30 text-brand-300 hover:text-brand-200 border border-brand-500/40 text-xs font-semibold shadow-sm transition-all">
                            <svg class="w-3.5 h-3.5 text-brand-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                            </svg>
                            <span>🔍 Selecionar do Cadastro de Membros</span>
                        </button>
                    </div>
                    <div class="relative">
                        <input type="text" name="name" id="name" value="{{ old('name') }}" required readonly
                            placeholder="Clique no botão acima para selecionar um membro..."
                            class="w-full px-3.5 py-2.5 bg-slate-950/90 border border-slate-800 rounded-xl text-xs text-slate-100 placeholder-slate-500 focus:outline-none focus:ring-1 focus:ring-brand-500 cursor-not-allowed font-medium">
                        <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none text-slate-500 text-xs">
                            🔒 Bloqueado (via seleção)
                        </div>
                    </div>
                </div>

                <!-- E-mail -->
                <div>
                    <label for="email" class="block text-xs font-medium text-slate-300 mb-1.5">E-mail Institucional *</label>
                    <input type="email" name="email" id="email" value="{{ old('email') }}" required autocomplete="new-email"
                        placeholder="joao.silva@ieadm-df.org.br"
                        class="w-full px-3.5 py-2.5 bg-slate-950/80 border border-slate-800 rounded-xl text-xs text-slate-100 placeholder-slate-500 focus:outline-none focus:ring-1 focus:ring-brand-500">
                </div>

                <!-- Senha Inicial -->
                <div>
                    <label for="password" class="block text-xs font-medium text-slate-300 mb-1.5">Senha de Acesso (Mín. 8 caracteres) *</label>
                    <input type="password" name="password" id="password" value="" required autocomplete="new-password"
                        placeholder="••••••••••••"
                        class="w-full px-3.5 py-2.5 bg-slate-950/80 border border-slate-800 rounded-xl text-xs text-slate-100 placeholder-slate-500 focus:outline-none focus:ring-1 focus:ring-brand-500">
                </div>

                <!-- CPF -->
                <div>
                    <label for="cpf" class="block text-xs font-medium text-slate-300 mb-1.5">CPF (Opcional)</label>
                    <input type="text" name="cpf" id="cpf" value="{{ old('cpf') }}"
                        placeholder="000.000.000-00"
                        class="w-full px-3.5 py-2.5 bg-slate-950/80 border border-slate-800 rounded-xl text-xs text-slate-100 placeholder-slate-500 focus:outline-none focus:ring-1 focus:ring-brand-500">
                </div>

                <!-- Telefone / WhatsApp -->
                <div>
                    <label for="phone" class="block text-xs font-medium text-slate-300 mb-1.5">Telefone / WhatsApp</label>
                    <input type="text" name="phone" id="phone" value="{{ old('phone') }}"
                        placeholder="(61) 90000-0000"
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
                    <select name="role_id" id="role_id" required class="w-full px-3.5 py-2.5 bg-slate-950/80 border border-slate-800 rounded-xl text-xs text-slate-100 focus:outline-none focus:ring-1 focus:ring-brand-500">
                        <option value="">Selecione o papel...</option>
                        @foreach($roles as $role)
                            <option value="{{ $role->id }}" {{ old('role_id') == $role->id ? 'selected' : '' }}>
                                {{ $role->name }} (Nível {{ $role->level }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Status da Conta -->
                <div>
                    <label for="status" class="block text-xs font-medium text-slate-300 mb-1.5">Situação da Conta *</label>
                    <select name="status" id="status" required class="w-full px-3.5 py-2.5 bg-slate-950/80 border border-slate-800 rounded-xl text-xs text-slate-100 focus:outline-none focus:ring-1 focus:ring-brand-500">
                        <option value="active" {{ old('status', 'active') === 'active' ? 'selected' : '' }}>Ativo (Acesso Liberado)</option>
                        <option value="inactive" {{ old('status') === 'inactive' ? 'selected' : '' }}>Inativo</option>
                        <option value="blocked" {{ old('status') === 'blocked' ? 'selected' : '' }}>Bloqueado</option>
                    </select>
                </div>

                <!-- Abrangência -->
                <div class="sm:col-span-2">
                    <label class="block text-xs font-medium text-slate-300 mb-2">Nível de Abrangência *</label>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        @if($currentUser->isRegional())
                        <label class="flex items-start gap-3 p-4 rounded-xl border border-slate-800 bg-slate-950/40 hover:bg-slate-950/80 cursor-pointer transition-all has-[:checked]:border-indigo-500 has-[:checked]:bg-indigo-950/20">
                            <input type="radio" name="scope" value="REGIONAL" id="scope_regional" {{ old('scope') === 'REGIONAL' ? 'checked' : '' }} onchange="toggleScopeUI()" class="mt-0.5 text-indigo-600 focus:ring-indigo-500 bg-slate-900 border-slate-700">
                            <div>
                                <span class="text-xs font-bold text-white block">Abrangência REGIONAL</span>
                                <span class="text-[11px] text-slate-400 block mt-0.5 leading-relaxed">
                                    Acesso automático a todas as congregações existentes e às novas congregações criadas no futuro.
                                </span>
                            </div>
                        </label>
                        @endif

                        <label class="flex items-start gap-3 p-4 rounded-xl border border-slate-800 bg-slate-950/40 hover:bg-slate-950/80 cursor-pointer transition-all has-[:checked]:border-emerald-500 has-[:checked]:bg-emerald-950/20">
                            <input type="radio" name="scope" value="LOCAL" id="scope_local" {{ old('scope', $currentUser->isLocal() ? 'LOCAL' : '') === 'LOCAL' ? 'checked' : '' }} onchange="toggleScopeUI()" class="mt-0.5 text-emerald-600 focus:ring-emerald-500 bg-slate-900 border-slate-700">
                            <div>
                                <span class="text-xs font-bold text-white block">Abrangência LOCAL</span>
                                <span class="text-[11px] text-slate-400 block mt-0.5 leading-relaxed">
                                    Acesso estrito apenas às congregações que forem especificamente selecionadas e autorizadas abaixo.
                                </span>
                            </div>
                        </label>
                    </div>
                </div>
            </div>
        </div>

        <!-- SEÇÃO 3: SELEÇÃO DE CONGREGAÇÕES (SE LOCAL) -->
        <div id="churches-section" class="transition-all duration-300">
            <h2 class="text-sm font-bold text-brand-400 uppercase tracking-wider mb-2 pb-2 border-b border-slate-800">
                3. Congregações Autorizadas (Para Abrangência Local)
            </h2>
            <p class="text-xs text-slate-400 mb-4">Selecione as igrejas que este secretário terá permissão de visualizar e gerenciar.</p>

            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-2.5 max-h-64 overflow-y-auto p-3 bg-slate-950/60 rounded-xl border border-slate-800">
                @forelse($churches as $church)
                    <label class="flex items-center gap-2.5 p-2.5 rounded-lg bg-slate-900/80 border border-slate-800/80 hover:border-slate-700 cursor-pointer select-none text-xs text-slate-200">
                        <input type="checkbox" name="churches[]" value="{{ $church->id }}" {{ in_array($church->id, old('churches', [])) ? 'checked' : '' }} class="w-4 h-4 rounded text-brand-600 bg-slate-950 border-slate-700 focus:ring-brand-500">
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
            <span><strong>Acesso Amplo:</strong> Usuários com abrangência Regional possuem acesso irrestrito a todas as congregações. Não é necessário vincular congregações manualmente.</span>
        </div>

        <!-- Botões de Ação -->
        <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-800">
            <a href="{{ route('secretaries.index') }}" class="px-4 py-2.5 bg-slate-800 hover:bg-slate-700 text-slate-300 text-xs font-semibold rounded-xl border border-slate-700/60 transition-colors">
                Cancelar
            </a>
            <button type="submit" class="px-6 py-2.5 bg-gradient-to-r from-brand-600 to-blue-600 hover:from-brand-500 hover:to-blue-500 text-white text-xs font-semibold rounded-xl shadow-lg shadow-brand-600/30 hover:shadow-brand-600/50 transition-all duration-200">
                Salvar Secretário
            </button>
        </div>
    </form>

</div>

<!-- MODAL DE BUSCA E SELEÇÃO DE MEMBROS -->
<div id="modalSelectMember" class="fixed inset-0 z-50 hidden bg-slate-950/80 backdrop-blur-sm flex items-center justify-center p-4">
    <div class="bg-slate-900 border border-slate-800 rounded-2xl max-w-3xl w-full max-h-[90vh] flex flex-col shadow-2xl overflow-hidden">
        
        <!-- Cabeçalho do Modal -->
        <div class="p-5 border-b border-slate-800 flex items-center justify-between">
            <div>
                <h3 class="text-base font-bold text-white flex items-center gap-2">
                    <svg class="w-5 h-5 text-brand-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                    </svg>
                    <span>Selecionar Membro para Secretário</span>
                </h3>
                <p class="text-xs text-slate-400 mt-0.5">Escolha um membro ativo do rol para vincular as credenciais de acesso</p>
            </div>
            <button type="button" onclick="closeSelectMemberModal()" class="text-slate-400 hover:text-white p-1.5 rounded-lg hover:bg-slate-800 transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>

        <!-- Barra de Pesquisa em Tempo Real -->
        <div class="p-4 bg-slate-950/60 border-b border-slate-800">
            <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                </div>
                <input type="text" id="searchMemberInput" placeholder="Filtrar por nome do membro ou CPF..."
                    class="w-full pl-10 pr-4 py-2.5 bg-slate-900 border border-slate-800 rounded-xl text-xs text-slate-100 placeholder-slate-500 focus:outline-none focus:ring-1 focus:ring-brand-500">
            </div>
        </div>

        <!-- Lista de Membros -->
        <div class="flex-1 overflow-y-auto p-4 max-h-96">
            <table class="w-full text-left text-xs text-slate-300">
                <thead class="bg-slate-950/40 text-[11px] font-semibold text-slate-400 uppercase tracking-wider border-b border-slate-800 sticky top-0">
                    <tr>
                        <th class="px-4 py-2.5">Membro</th>
                        <th class="px-3 py-2.5">CPF</th>
                        <th class="px-3 py-2.5">Congregação</th>
                        <th class="px-4 py-2.5 text-right">Ação</th>
                    </tr>
                </thead>
                <tbody id="membersTableBody" class="divide-y divide-slate-800/60">
                    @forelse($membersList as $member)
                    <tr class="member-row hover:bg-slate-800/40 transition-colors"
                        data-id="{{ $member->id }}"
                        data-name="{{ $member->full_name }}"
                        data-email="{{ $member->email ?? '' }}"
                        data-phone="{{ $member->phone ?? $member->whatsapp ?? '' }}"
                        data-cpf="{{ $member->cpf ?? '' }}"
                        data-church="{{ $member->church->name ?? 'Regional' }}"
                        data-search="{{ mb_strtolower($member->full_name . ' ' . $member->cpf . ' ' . ($member->church->name ?? '')) }}">
                        
                        <td class="px-4 py-3">
                            <div class="flex items-center gap-3">
                                @if($member->photo)
                                    <img src="{{ $member->photo }}" alt="{{ $member->full_name }}" class="w-8 h-8 rounded-full object-cover border border-slate-700 shrink-0">
                                @else
                                    <div class="w-8 h-8 rounded-full bg-slate-800 text-brand-300 font-bold flex items-center justify-center text-xs shrink-0 border border-slate-700">
                                        {{ strtoupper(substr($member->full_name, 0, 2)) }}
                                    </div>
                                @endif
                                <div class="min-w-0">
                                    <p class="font-semibold text-slate-100 truncate">{{ $member->full_name }}</p>
                                    <span class="text-[11px] text-slate-400 block truncate">{{ $member->ecclesiastical_position ?? 'Membro' }}</span>
                                </div>
                            </div>
                        </td>

                        <td class="px-3 py-3 text-slate-300 font-mono text-[11px]">
                            {{ $member->cpf ?: '—' }}
                        </td>

                        <td class="px-3 py-3 text-slate-300">
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] bg-slate-950 border border-slate-800 truncate max-w-[140px]" title="{{ $member->church->name ?? '—' }}">
                                {{ $member->church->name ?? 'Regional' }}
                            </span>
                        </td>

                        <td class="px-4 py-3 text-right">
                            <button type="button"
                                onclick="selectMember({{ $member->id }}, '{{ addslashes($member->full_name) }}', '{{ addslashes($member->email ?? '') }}', '{{ addslashes($member->phone ?? $member->whatsapp ?? '') }}', '{{ addslashes($member->cpf ?? '') }}', '{{ addslashes($member->church->name ?? '') }}')"
                                class="px-3 py-1.5 bg-brand-600 hover:bg-brand-500 text-white font-semibold rounded-lg text-xs shadow-md transition-all">
                                Selecionar
                            </button>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="px-4 py-8 text-center text-slate-500 text-xs">
                            Nenhum membro ativo disponível para vinculação.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
            <div id="noMembersFoundMessage" class="hidden py-8 text-center text-slate-400 text-xs">
                Nenhum membro encontrado com o termo pesquisado.
            </div>
        </div>

        <!-- Rodapé do Modal -->
        <div class="p-4 border-t border-slate-800 bg-slate-950/40 flex items-center justify-between text-xs text-slate-400">
            <span>Total de membros ativos: <strong class="text-slate-200">{{ $membersList->count() }}</strong></span>
            <button type="button" onclick="closeSelectMemberModal()" class="px-4 py-2 bg-slate-800 hover:bg-slate-700 text-slate-300 rounded-xl transition-colors">
                Fechar
            </button>
        </div>

    </div>
</div>

<script>
    function openSelectMemberModal() {
        const modal = document.getElementById('modalSelectMember');
        modal.classList.remove('hidden');
        const searchInput = document.getElementById('searchMemberInput');
        searchInput.value = '';
        filterMembers('');
        setTimeout(() => searchInput.focus(), 50);
    }

    function closeSelectMemberModal() {
        document.getElementById('modalSelectMember').classList.add('hidden');
    }

    function filterMembers(query) {
        const rows = document.querySelectorAll('#membersTableBody tr.member-row');
        const cleanQuery = query.toLowerCase().trim();
        let visibleCount = 0;

        rows.forEach(row => {
            const searchData = row.getAttribute('data-search') || '';
            if (!cleanQuery || searchData.includes(cleanQuery)) {
                row.style.display = '';
                visibleCount++;
            } else {
                row.style.display = 'none';
            }
        });

        const noFoundMsg = document.getElementById('noMembersFoundMessage');
        if (noFoundMsg) {
            noFoundMsg.classList.toggle('hidden', visibleCount > 0);
        }
    }

    document.getElementById('searchMemberInput')?.addEventListener('input', function(e) {
        filterMembers(e.target.value);
    });

    function selectMember(id, name, email, phone, cpf, church) {
        document.getElementById('selected_member_id').value = id;
        
        const nameInput = document.getElementById('name');
        nameInput.value = name;

        // Auto-preenchimento opcional de dados já existentes do membro
        if (email) {
            const emailInput = document.getElementById('email');
            if (!emailInput.value) {
                emailInput.value = email;
            }
        }

        if (phone) {
            const phoneInput = document.getElementById('phone');
            if (!phoneInput.value) {
                phoneInput.value = phone;
            }
        }

        if (cpf) {
            const cpfInput = document.getElementById('cpf');
            if (!cpfInput.value) {
                cpfInput.value = cpf;
            }
        }

        // Atualização visual do Banner
        const banner = document.getElementById('selected_member_banner');
        const nameDisplay = document.getElementById('selected_member_name_display');
        const infoDisplay = document.getElementById('selected_member_info_display');

        if (banner && nameDisplay) {
            nameDisplay.textContent = name;
            if (infoDisplay) {
                infoDisplay.textContent = `Congregação de Origem: ${church || 'Regional'} • CPF: ${cpf || 'Não informado'}`;
            }
            banner.classList.remove('hidden');
        }

        closeSelectMemberModal();
    }

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
