@extends('layouts.app')

@section('title', 'Nova Transferência de Membro')
@section('header', 'Transferências de Membros')

@php
    $selectedOldMember = null;
    if (old('member_id') && isset($availableMembers)) {
        $selectedOldMember = $availableMembers->firstWhere('id', old('member_id'));
    }
@endphp

@section('content')
<div class="max-w-3xl mx-auto space-y-6">

    <!-- Cabeçalho -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-xl font-bold text-white tracking-tight">Nova Transferência de Membro</h1>
            <p class="text-xs text-slate-400 mt-1">Selecione o membro, defina o tipo de movimentação e registre a congregação de destino.</p>
        </div>
        <a href="{{ isset($member) && $member->id ? route('members.show', $member) : route('transfers.index') }}" class="px-3.5 py-2 bg-slate-900 hover:bg-slate-800 text-slate-300 text-xs font-semibold rounded-xl border border-slate-800 transition-colors">
            Cancelar e Voltar
        </a>
    </div>

    <!-- Formulário de Transferência / Inativação -->
    <form id="transfer-form" method="POST" action="{{ isset($member) && $member->id ? route('members.transfer.store', $member) : route('transfers.store') }}" class="bg-slate-900/60 p-6 md:p-8 rounded-2xl border border-slate-800/80 space-y-6 shadow-xl">
        @csrf

        @if(isset($member) && $member->id)
            <!-- Membro Pré-selecionado -->
            <input type="hidden" name="member_id" id="selected_member_id" value="{{ $member->id }}">
            <div class="bg-slate-950/70 p-5 rounded-2xl border border-slate-800 flex items-center justify-between shadow-inner">
                <div class="flex items-center gap-4">
                    @if($member->photo)
                        <img src="{{ $member->photo }}" alt="{{ $member->full_name }}" class="w-14 h-14 rounded-2xl object-cover border border-slate-700 shadow-md shrink-0">
                    @else
                        <div class="w-14 h-14 rounded-2xl bg-gradient-to-tr from-brand-700 to-brand-500 border border-brand-400/30 flex items-center justify-center font-bold text-xl text-white shadow-lg shadow-brand-600/30 shrink-0">
                            {{ strtoupper(substr($member->full_name, 0, 2)) }}
                        </div>
                    @endif
                    <div>
                        <h2 class="text-base font-bold text-white">{{ $member->full_name }}</h2>
                        <p class="text-xs text-slate-400 mt-0.5">CPF: {{ $member->cpf ?? 'Não informado' }}</p>
                        <div class="flex items-center gap-2 mt-1.5">
                            <span class="text-[11px] text-slate-400">Congregação Atual:</span>
                            <span class="px-2.5 py-0.5 rounded-lg text-xs font-bold bg-slate-900 text-brand-300 border border-slate-700">
                                {{ $member->church?->name ?? 'Regional' }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        @else
            <!-- ID do Membro Selecionado -->
            <input type="hidden" name="member_id" id="selected_member_id" value="{{ old('member_id') }}" required>

            <!-- SEÇÃO DE IDENTIFICAÇÃO E SELEÇÃO DO MEMBRO -->
            <div class="space-y-4">
                <!-- Banner do Membro Selecionado (Exibido dinamicamente) -->
                <div id="selected_member_banner" class="{{ $selectedOldMember ? '' : 'hidden' }} p-4 rounded-xl bg-brand-950/40 border border-brand-500/40 flex items-center justify-between gap-3 transition-all">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-full bg-brand-600/30 text-brand-300 flex items-center justify-center font-bold text-sm border border-brand-500/40" id="selected_member_avatar">
                            {{ $selectedOldMember ? strtoupper(substr($selectedOldMember->full_name, 0, 2)) : '✓' }}
                        </div>
                        <div>
                            <div class="flex items-center gap-2">
                                <span class="text-xs font-bold text-white" id="selected_member_name_display">{{ $selectedOldMember ? $selectedOldMember->full_name : '' }}</span>
                                <span class="px-1.5 py-0.2 rounded text-[10px] font-bold bg-emerald-950 text-emerald-300 border border-emerald-500/30">Membro Selecionado</span>
                            </div>
                            <p class="text-[11px] text-slate-400 mt-0.5" id="selected_member_info_display">
                                {{ $selectedOldMember ? 'Congregação de Origem: ' . ($selectedOldMember->church?->name ?? 'Regional') . ' • CPF: ' . ($selectedOldMember->cpf ?: 'Não informado') : 'Dados sincronizados com o cadastro de membros' }}
                            </p>
                        </div>
                    </div>
                    <button type="button" onclick="openSelectMemberModal()" class="px-3 py-1.5 bg-slate-800 hover:bg-slate-700 text-slate-300 hover:text-white rounded-lg border border-slate-700 text-xs font-medium transition-colors shrink-0">
                        Trocar Membro
                    </button>
                </div>

                <!-- Nome Completo (Bloqueado) + Botão de Busca -->
                <div>
                    <div class="flex items-center justify-between mb-1.5">
                        <label for="name" class="block text-xs font-medium text-slate-300">Nome Completo / Selecionar Membro *</label>
                        <button type="button" onclick="openSelectMemberModal()" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-brand-600/20 hover:bg-brand-600/30 text-brand-300 hover:text-brand-200 border border-brand-500/40 text-xs font-semibold shadow-sm transition-all">
                            <svg class="w-3.5 h-3.5 text-brand-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                            </svg>
                            <span>🔍 Selecionar do Cadastro de Membros</span>
                        </button>
                    </div>
                    <div class="relative">
                        <input type="text" name="name" id="name" value="{{ $selectedOldMember ? $selectedOldMember->full_name : old('name') }}" required readonly
                            placeholder="Clique no botão acima para selecionar um membro..."
                            class="w-full px-3.5 py-2.5 bg-slate-950/90 border border-slate-800 rounded-xl text-xs text-slate-100 placeholder-slate-500 focus:outline-none focus:ring-1 focus:ring-brand-500 cursor-not-allowed font-medium">
                        <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none text-slate-500 text-xs">
                            🔒 Bloqueado (via seleção)
                        </div>
                    </div>
                    @error('member_id')
                        <p class="text-xs text-rose-400 mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        @endif

        <!-- Tipo de Movimentação -->
        <div>
            <label class="block text-xs font-medium text-slate-300 mb-2">Tipo de Movimentação *</label>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <label class="relative flex items-center p-3 rounded-xl border border-slate-800 bg-slate-950/60 cursor-pointer hover:border-brand-500/50 transition-all has-[:checked]:border-brand-500 has-[:checked]:bg-brand-500/10">
                    <input type="radio" name="type" value="transfer" id="type_transfer" class="text-brand-500 focus:ring-brand-500 mr-3" checked onchange="toggleMovementType()">
                    <div>
                        <span class="block text-xs font-semibold text-white">Transferência de Congregação</span>
                        <span class="block text-[11px] text-slate-400">Transferir membro para outra congregação da Regional</span>
                    </div>
                </label>
                <label class="relative flex items-center p-3 rounded-xl border border-slate-800 bg-slate-950/60 cursor-pointer hover:border-amber-500/50 transition-all has-[:checked]:border-amber-500 has-[:checked]:bg-amber-500/10">
                    <input type="radio" name="type" value="inactivation" id="type_inactivation" class="text-amber-500 focus:ring-amber-500 mr-3" onchange="toggleMovementType()">
                    <div>
                        <span class="block text-xs font-semibold text-amber-300">Inativação / Saída do Ministério</span>
                        <span class="block text-[11px] text-slate-400">Membro desligado ou transferido para fora do ministério</span>
                    </div>
                </label>
            </div>
        </div>

        <!-- Seleção da Nova Congregação (Oculto se inativação) -->
        <div id="target_church_container">
            <label for="to_church_id" class="block text-xs font-medium text-slate-300 mb-1.5">Nova Congregação de Destino *</label>
            <select name="to_church_id" id="to_church_id" class="w-full px-3.5 py-2.5 bg-slate-950/80 border border-slate-800 rounded-xl text-xs text-slate-100 focus:outline-none focus:ring-1 focus:ring-brand-500">
                <option value="">Selecione a congregação de destino...</option>
                @foreach($targetChurches as $church)
                    <option value="{{ $church->id }}" data-church-id="{{ $church->id }}" {{ old('to_church_id') == $church->id ? 'selected' : '' }}>
                        {{ $church->name }} ({{ $church->city }}/{{ $church->state }})
                    </option>
                @endforeach
            </select>
            @error('to_church_id')
                <p class="text-xs text-rose-400 mt-1">{{ $message }}</p>
            @enderror
        </div>

        <!-- Motivo da Movimentação -->
        <div>
            <label for="reason" class="block text-xs font-medium text-slate-300 mb-1.5" id="reason_label">Motivo da Transferência *</label>
            <input type="text" name="reason" id="reason" value="{{ old('reason') }}" required
                placeholder="Ex: Mudança de endereço residencial / Designação ministerial"
                class="w-full px-3.5 py-2.5 bg-slate-950/80 border border-slate-800 rounded-xl text-xs text-slate-100 placeholder-slate-500 focus:outline-none focus:ring-1 focus:ring-brand-500">
            @error('reason')
                <p class="text-xs text-rose-400 mt-1">{{ $message }}</p>
            @enderror
        </div>

        <!-- Observações Complementares -->
        <div>
            <label for="notes" class="block text-xs font-medium text-slate-300 mb-1.5">Observações Complementares (Opcional)</label>
            <textarea name="notes" id="notes" rows="3" placeholder="Detalhes adicionais para o histórico permanente..."
                class="w-full px-3.5 py-2.5 bg-slate-950/80 border border-slate-800 rounded-xl text-xs text-slate-100 placeholder-slate-500 focus:outline-none focus:ring-1 focus:ring-brand-500">{{ old('notes') }}</textarea>
            @error('notes')
                <p class="text-xs text-rose-400 mt-1">{{ $message }}</p>
            @enderror
        </div>

        <!-- Botões de Ação com Acionamento do Modal -->
        <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-800">
            <a href="{{ isset($member) && $member->id ? route('members.show', $member) : route('transfers.index') }}" class="px-4 py-2.5 bg-slate-800 hover:bg-slate-700 text-slate-300 text-xs font-semibold rounded-xl border border-slate-700/60 transition-colors">
                Cancelar
            </a>
            <button type="button" onclick="openConfirmationModal()" id="submit_btn" class="px-6 py-2.5 bg-gradient-to-r from-brand-600 to-blue-600 hover:from-brand-500 hover:to-blue-500 text-white text-xs font-semibold rounded-xl shadow-lg shadow-brand-600/30 hover:shadow-brand-600/50 transition-all duration-200">
                Confirmar Transferência
            </button>
        </div>
    </form>

</div>

@if(!isset($member) || !$member->id)
<!-- MODAL DE BUSCA E SELEÇÃO DE MEMBROS -->
<div id="modalSelectMember" class="fixed inset-0 z-50 hidden bg-slate-950/80 backdrop-blur-sm flex items-center justify-center p-4">
    <div class="bg-slate-900 border border-slate-800 rounded-2xl max-w-3xl w-full max-h-[90vh] flex flex-col shadow-2xl overflow-hidden animate-in fade-in zoom-in duration-150">
        
        <!-- Cabeçalho do Modal -->
        <div class="p-5 border-b border-slate-800 flex items-center justify-between">
            <div>
                <h3 class="text-base font-bold text-white flex items-center gap-2">
                    <svg class="w-5 h-5 text-brand-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                    </svg>
                    <span>Selecionar Membro para Movimentação</span>
                </h3>
                <p class="text-xs text-slate-400 mt-0.5">Escolha um membro ativo do rol para registrar a transferência ou inativação</p>
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
                <input type="text" id="searchMemberInput" placeholder="Filtrar por nome do membro, CPF ou congregação..."
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
                        <th class="px-3 py-2.5">Congregação Atual</th>
                        <th class="px-4 py-2.5 text-right">Ação</th>
                    </tr>
                </thead>
                <tbody id="membersTableBody" class="divide-y divide-slate-800/60">
                    @forelse($availableMembers as $availMember)
                    <tr class="member-row hover:bg-slate-800/40 transition-colors"
                        data-id="{{ $availMember->id }}"
                        data-name="{{ $availMember->full_name }}"
                        data-cpf="{{ $availMember->cpf ?? '' }}"
                        data-church-id="{{ $availMember->church_id }}"
                        data-church-name="{{ $availMember->church->name ?? 'Regional' }}"
                        data-search="{{ mb_strtolower($availMember->full_name . ' ' . $availMember->cpf . ' ' . ($availMember->church->name ?? '')) }}">
                        
                        <td class="px-4 py-3">
                            <div class="flex items-center gap-3">
                                @if($availMember->photo)
                                    <img src="{{ $availMember->photo }}" alt="{{ $availMember->full_name }}" class="w-8 h-8 rounded-full object-cover border border-slate-700 shrink-0">
                                @else
                                    <div class="w-8 h-8 rounded-full bg-slate-800 text-brand-300 font-bold flex items-center justify-center text-xs shrink-0 border border-slate-700">
                                        {{ strtoupper(substr($availMember->full_name, 0, 2)) }}
                                    </div>
                                @endif
                                <div class="min-w-0">
                                    <p class="font-semibold text-slate-100 truncate">{{ $availMember->full_name }}</p>
                                    <span class="text-[11px] text-slate-400 block truncate">{{ $availMember->ecclesiastical_position ?? 'Membro' }}</span>
                                </div>
                            </div>
                        </td>

                        <td class="px-3 py-3 text-slate-300 font-mono text-[11px]">
                            {{ $availMember->cpf ?: '—' }}
                        </td>

                        <td class="px-3 py-3 text-slate-300">
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] bg-slate-950 border border-slate-800 truncate max-w-[140px]" title="{{ $availMember->church->name ?? '—' }}">
                                {{ $availMember->church->name ?? 'Regional' }}
                            </span>
                        </td>

                        <td class="px-4 py-3 text-right">
                            <button type="button"
                                onclick="selectMember({{ $availMember->id }}, '{{ addslashes($availMember->full_name) }}', '{{ addslashes($availMember->cpf ?? '') }}', '{{ $availMember->church_id }}', '{{ addslashes($availMember->church->name ?? 'Regional') }}')"
                                class="px-3 py-1.5 bg-brand-600 hover:bg-brand-500 text-white font-semibold rounded-lg text-xs shadow-md transition-all">
                                Selecionar
                            </button>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="px-4 py-8 text-center text-slate-500 text-xs">
                            Nenhum membro ativo disponível para movimentação nas congregações autorizadas.
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
            <span>Total de membros elegíveis: <strong class="text-slate-200">{{ $availableMembers->count() }}</strong></span>
            <button type="button" onclick="closeSelectMemberModal()" class="px-4 py-2 bg-slate-800 hover:bg-slate-700 text-slate-300 rounded-xl transition-colors">
                Fechar
            </button>
        </div>

    </div>
</div>
@endif

<!-- Modal de Confirmação Prévia Obrigatória -->
<div id="confirmation-modal" class="fixed inset-0 z-50 bg-slate-950/80 backdrop-blur-sm hidden flex items-center justify-center p-4">
    <div class="bg-slate-900 border border-slate-800 rounded-2xl max-w-md w-full p-6 shadow-2xl space-y-5 animate-in fade-in zoom-in duration-200">
        <div class="flex items-start gap-4">
            <div id="modal_icon_container" class="w-12 h-12 rounded-2xl bg-amber-500/10 text-amber-400 flex items-center justify-center shrink-0 border border-amber-500/20">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                </svg>
            </div>
            <div>
                <h3 class="text-base font-bold text-white" id="modal_title">Confirmação de Transferência</h3>
                <p class="text-xs text-slate-300 mt-1.5 font-medium leading-relaxed" id="modal_question">
                    Tem certeza que deseja transferir este membro?
                </p>
                <p class="text-[11px] text-slate-400 mt-1" id="modal_desc">
                    Esta ação atualizará a congregação do membro e registrará a movimentação permanentemente no histórico imutável.
                </p>
            </div>
        </div>

        <div class="flex items-center justify-end gap-3 pt-3 border-t border-slate-800">
            <button type="button" onclick="closeConfirmationModal()" class="px-4 py-2 bg-slate-800 hover:bg-slate-700 text-slate-300 text-xs font-semibold rounded-xl border border-slate-700 transition-colors">
                Não, cancelar
            </button>
            <button type="button" onclick="submitTransferForm()" id="modal_confirm_btn" class="px-5 py-2 bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-bold rounded-xl shadow-lg shadow-emerald-600/30 transition-colors">
                Sim, confirmar
            </button>
        </div>
    </div>
</div>

<script>
    function openSelectMemberModal() {
        const modal = document.getElementById('modalSelectMember');
        if (!modal) return;
        modal.classList.remove('hidden');
        const searchInput = document.getElementById('searchMemberInput');
        if (searchInput) {
            searchInput.value = '';
            filterMembers('');
            setTimeout(() => searchInput.focus(), 50);
        }
    }

    function closeSelectMemberModal() {
        const modal = document.getElementById('modalSelectMember');
        if (modal) {
            modal.classList.add('hidden');
        }
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

    function selectMember(id, name, cpf, churchId, churchName) {
        const memberIdInput = document.getElementById('selected_member_id');
        if (memberIdInput) {
            memberIdInput.value = id;
        }
        
        const nameInput = document.getElementById('name');
        if (nameInput) {
            nameInput.value = name;
        }

        // Atualização visual do Banner
        const banner = document.getElementById('selected_member_banner');
        const nameDisplay = document.getElementById('selected_member_name_display');
        const infoDisplay = document.getElementById('selected_member_info_display');
        const avatarDisplay = document.getElementById('selected_member_avatar');

        if (banner && nameDisplay) {
            nameDisplay.textContent = name;
            if (infoDisplay) {
                infoDisplay.textContent = `Congregação de Origem: ${churchName || 'Regional'} • CPF: ${cpf || 'Não informado'}`;
            }
            if (avatarDisplay && name) {
                avatarDisplay.textContent = name.substring(0, 2).toUpperCase();
            }
            banner.classList.remove('hidden');
        }

        // Desabilita a opção da congregação atual no select de destino
        updateTargetChurches(churchId);

        closeSelectMemberModal();
    }

    function updateTargetChurches(sourceChurchId) {
        const toChurchSelect = document.getElementById('to_church_id');
        if (toChurchSelect && sourceChurchId) {
            for (let i = 0; i < toChurchSelect.options.length; i++) {
                const opt = toChurchSelect.options[i];
                if (opt.value && String(opt.value) === String(sourceChurchId)) {
                    opt.disabled = true;
                    if (toChurchSelect.value === String(sourceChurchId)) {
                        toChurchSelect.value = '';
                    }
                } else {
                    opt.disabled = false;
                }
            }
        }
    }

    function toggleMovementType() {
        const isTransfer = document.getElementById('type_transfer').checked;
        const targetContainer = document.getElementById('target_church_container');
        const toChurchSelect = document.getElementById('to_church_id');
        const reasonLabel = document.getElementById('reason_label');
        const reasonInput = document.getElementById('reason');
        const submitBtn = document.getElementById('submit_btn');

        if (isTransfer) {
            targetContainer.style.display = 'block';
            toChurchSelect.setAttribute('required', 'required');
            reasonLabel.textContent = 'Motivo da Transferência *';
            reasonInput.placeholder = 'Ex: Mudança de endereço residencial / Designação ministerial';
            submitBtn.textContent = 'Confirmar Transferência';
            submitBtn.className = 'px-6 py-2.5 bg-gradient-to-r from-brand-600 to-blue-600 hover:from-brand-500 hover:to-blue-500 text-white text-xs font-semibold rounded-xl shadow-lg shadow-brand-600/30 hover:shadow-brand-600/50 transition-all duration-200';
        } else {
            targetContainer.style.display = 'none';
            toChurchSelect.removeAttribute('required');
            toChurchSelect.value = '';
            reasonLabel.textContent = 'Motivo da Inativação / Saída *';
            reasonInput.placeholder = 'Ex: Desligamento a pedido / Mudança para outro ministério / Abandono';
            submitBtn.textContent = 'Confirmar Inativação';
            submitBtn.className = 'px-6 py-2.5 bg-gradient-to-r from-amber-600 to-rose-600 hover:from-amber-500 hover:to-rose-500 text-white text-xs font-semibold rounded-xl shadow-lg shadow-amber-600/30 hover:shadow-amber-600/50 transition-all duration-200';
        }
    }

    function openConfirmationModal() {
        const isTransfer = document.getElementById('type_transfer').checked;
        const memberIdInput = document.getElementById('selected_member_id');
        const toChurch = document.getElementById('to_church_id').value;
        const reason = document.getElementById('reason').value;

        if (memberIdInput && !memberIdInput.value) {
            alert('Por favor, selecione um membro para realizar a movimentação.');
            openSelectMemberModal();
            return;
        }

        if (isTransfer && !toChurch) {
            alert('Por favor, selecione a congregação de destino.');
            document.getElementById('to_church_id').focus();
            return;
        }

        if (!reason.trim()) {
            alert(isTransfer ? 'Por favor, informe o motivo da transferência.' : 'Por favor, informe o motivo da inativação.');
            document.getElementById('reason').focus();
            return;
        }

        const modalTitle = document.getElementById('modal_title');
        const modalQuestion = document.getElementById('modal_question');
        const modalDesc = document.getElementById('modal_desc');
        const modalBtn = document.getElementById('modal_confirm_btn');

        if (isTransfer) {
            modalTitle.textContent = 'Confirmação de Transferência';
            modalQuestion.textContent = 'Tem certeza que deseja solicitar a transferência deste membro?';
            modalDesc.textContent = 'Esta movimentação será registrada no histórico e aplicada conforme permissão regional.';
            modalBtn.textContent = 'Sim, transferir membro';
            modalBtn.className = 'px-5 py-2 bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-bold rounded-xl shadow-lg shadow-emerald-600/30 transition-colors';
        } else {
            modalTitle.textContent = 'Confirmação de Inativação';
            modalQuestion.textContent = 'Tem certeza que deseja inativar este membro?';
            modalDesc.textContent = 'O membro será marcado como "Inativo" e o desligamento registrado no histórico.';
            modalBtn.textContent = 'Sim, inativar membro';
            modalBtn.className = 'px-5 py-2 bg-rose-600 hover:bg-rose-500 text-white text-xs font-bold rounded-xl shadow-lg shadow-rose-600/30 transition-colors';
        }

        document.getElementById('confirmation-modal').classList.remove('hidden');
    }

    function closeConfirmationModal() {
        document.getElementById('confirmation-modal').classList.add('hidden');
    }

    function submitTransferForm() {
        document.getElementById('transfer-form').submit();
    }

    // Inicialização ao carregar
    document.addEventListener('DOMContentLoaded', function() {
        @if(isset($member) && $member->id)
            updateTargetChurches('{{ $member->church_id }}');
        @elseif($selectedOldMember)
            updateTargetChurches('{{ $selectedOldMember->church_id }}');
        @endif
    });
</script>
@endsection
