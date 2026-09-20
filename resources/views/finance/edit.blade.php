@extends('layouts.app')

@section('title', 'Editar Lançamento')
@section('header', 'Editar Lançamento Financeiro')

@section('content')
<div class="max-w-2xl mx-auto">

    <div class="bg-slate-900/60 border border-slate-800/80 rounded-2xl overflow-hidden">
        <div class="p-5 border-b border-slate-800 flex items-center justify-between">
            <div>
                <h2 class="text-sm font-semibold text-white">Editar Lançamento</h2>
                <p class="text-[11px] text-slate-400 mt-0.5">
                    Registrado em {{ $transaction->created_at->format('d/m/Y H:i') }}
                    @if($transaction->createdBy)
                    por <span class="text-brand-400">{{ $transaction->createdBy->name }}</span>
                    @endif
                </p>
            </div>
            <a href="{{ route('finance.index') }}"
               class="text-xs text-slate-400 hover:text-slate-200 transition-colors flex items-center gap-1">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
                Voltar
            </a>
        </div>

        {{-- Aviso de auditoria --}}
        <div class="mx-6 mt-5 p-3.5 bg-amber-950/40 border border-amber-500/30 rounded-xl flex items-start gap-3">
            <svg class="w-4 h-4 text-amber-400 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
            </svg>
            <p class="text-xs text-amber-300">
                <strong>Auditoria ativa:</strong> os valores anteriores e as alterações realizadas serão registrados automaticamente no histórico de auditoria para consulta da Regional.
            </p>
        </div>

        <form method="POST" action="{{ route('finance.update', $transaction) }}" class="p-6 space-y-5">
            @csrf
            @method('PUT')

            {{-- Tipo de Lançamento --}}
            <div>
                <label class="block text-xs font-semibold text-slate-300 mb-2">Tipo de Lançamento <span class="text-red-400">*</span></label>
                <div class="grid grid-cols-2 gap-3">
                    <label class="cursor-pointer">
                        <input type="radio" name="type" value="entry" id="type-entry"
                               class="sr-only" {{ old('type', $transaction->type) === 'entry' ? 'checked' : '' }}>
                        <div id="entry-box" class="flex items-center justify-center gap-2 p-3 rounded-xl border-2 transition-all
                            {{ old('type', $transaction->type) === 'entry' ? 'border-emerald-500 bg-emerald-950/40 text-emerald-300' : 'border-slate-700 text-slate-400' }}">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18"/>
                            </svg>
                            <span class="text-sm font-semibold">Entrada</span>
                        </div>
                    </label>
                    <label class="cursor-pointer">
                        <input type="radio" name="type" value="exit" id="type-exit"
                               class="sr-only" {{ old('type', $transaction->type) === 'exit' ? 'checked' : '' }}>
                        <div id="exit-box" class="flex items-center justify-center gap-2 p-3 rounded-xl border-2 transition-all
                            {{ old('type', $transaction->type) === 'exit' ? 'border-red-500 bg-red-950/40 text-red-300' : 'border-slate-700 text-slate-400' }}">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3"/>
                            </svg>
                            <span class="text-sm font-semibold">Saída</span>
                        </div>
                    </label>
                </div>
                @error('type')<p class="text-red-400 text-xs mt-1.5">{{ $message }}</p>@enderror
            </div>

            {{-- Categoria --}}
            <div>
                <label for="category" class="block text-xs font-semibold text-slate-300 mb-1.5">
                    Categoria <span class="text-red-400">*</span>
                </label>
                <select name="category" id="category"
                        class="w-full bg-slate-800 border border-slate-700 text-slate-200 text-sm rounded-xl px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-brand-500">
                    <option value="">— Selecione a categoria —</option>
                    <option value="dizimo"  {{ old('category', $transaction->category) === 'dizimo'  ? 'selected' : '' }}>💚 Dízimo</option>
                    <option value="oferta"  {{ old('category', $transaction->category) === 'oferta'  ? 'selected' : '' }}>🟡 Oferta</option>
                    <option value="outros"  {{ old('category', $transaction->category) === 'outros'  ? 'selected' : '' }}>🔵 Outros (Entrada)</option>
                    <option value="despesa" {{ old('category', $transaction->category) === 'despesa' ? 'selected' : '' }}>🔴 Despesa</option>
                </select>
                @error('category')<p class="text-red-400 text-xs mt-1.5">{{ $message }}</p>@enderror
            </div>

            {{-- Congregação (read-only: não pode mudar a congregação na edição) --}}
            <div>
                <label class="block text-xs font-semibold text-slate-300 mb-1.5">Congregação</label>
                <div class="bg-slate-800/60 border border-slate-700 rounded-xl px-4 py-2.5 text-sm text-slate-400">
                    {{ $transaction->church->name }}
                    <span class="text-slate-600 text-xs ml-2">(não pode ser alterada)</span>
                </div>
            </div>

            {{-- Membro --}}
            <div id="member-field" class="{{ in_array(old('category', $transaction->category), ['dizimo','oferta']) ? '' : 'hidden' }}">
                <label for="member_id" class="block text-xs font-semibold text-slate-300 mb-1.5">
                    Membro
                    <span id="member-required-badge" class="{{ old('category', $transaction->category) === 'dizimo' ? '' : 'hidden' }} ml-1 text-red-400">*</span>
                    <span id="member-optional-badge" class="{{ old('category', $transaction->category) === 'oferta' ? '' : 'hidden' }} ml-1 text-slate-500 font-normal">(opcional)</span>
                </label>
                <select name="member_id" id="member_id"
                        class="w-full bg-slate-800 border border-slate-700 text-slate-200 text-sm rounded-xl px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-brand-500"
                        {{ old('category', $transaction->category) === 'dizimo' ? 'required' : '' }}>
                    <option value="">— Selecione o membro —</option>
                    @foreach($members as $member)
                    <option value="{{ $member->id }}" {{ old('member_id', $transaction->member_id) == $member->id ? 'selected' : '' }}>
                        {{ $member->full_name }}
                    </option>
                    @endforeach
                </select>
                @error('member_id')<p class="text-red-400 text-xs mt-1.5">{{ $message }}</p>@enderror
            </div>

            {{-- Descrição --}}
            <div id="description-field" class="{{ in_array(old('category', $transaction->category), ['oferta','outros','despesa']) ? '' : 'hidden' }}">
                <label for="description" class="block text-xs font-semibold text-slate-300 mb-1.5">
                    <span id="description-label">
                        @if(old('category', $transaction->category) === 'outros') Justificativa / Motivo
                        @elseif(old('category', $transaction->category) === 'despesa') Descrição da Despesa
                        @else Descrição da Oferta
                        @endif
                    </span>
                    <span id="description-required-badge" class="{{ in_array(old('category', $transaction->category), ['outros','despesa']) ? '' : 'hidden' }} ml-1 text-red-400">*</span>
                    <span id="description-optional-badge" class="{{ old('category', $transaction->category) === 'oferta' ? '' : 'hidden' }} ml-1 text-slate-500 font-normal">(opcional)</span>
                </label>
                <textarea name="description" id="description" rows="2"
                          class="w-full bg-slate-800 border border-slate-700 text-slate-200 text-sm rounded-xl px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-brand-500 resize-none placeholder-slate-600"
                          {{ in_array(old('category', $transaction->category), ['outros','despesa']) ? 'required' : '' }}>{{ old('description', $transaction->description) }}</textarea>
                @error('description')<p class="text-red-400 text-xs mt-1.5">{{ $message }}</p>@enderror
            </div>

            {{-- Valor --}}
            <div>
                <label for="amount" class="block text-xs font-semibold text-slate-300 mb-1.5">
                    Valor (R$) <span class="text-red-400">*</span>
                </label>
                <div class="relative">
                    <span class="absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-sm font-semibold">R$</span>
                    <input type="number" name="amount" id="amount"
                           value="{{ old('amount', $transaction->amount) }}"
                           min="0.01" max="999999.99" step="0.01"
                           class="w-full bg-slate-800 border border-slate-700 text-slate-200 text-sm rounded-xl pl-10 pr-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-brand-500">
                </div>
                @error('amount')<p class="text-red-400 text-xs mt-1.5">{{ $message }}</p>@enderror
            </div>

            {{-- Datas --}}
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label for="transaction_date" class="block text-xs font-semibold text-slate-300 mb-1.5">
                        Data do Recebimento <span class="text-red-400">*</span>
                    </label>
                    <input type="date" name="transaction_date" id="transaction_date"
                           value="{{ old('transaction_date', $transaction->transaction_date->format('Y-m-d')) }}"
                           class="w-full bg-slate-800 border border-slate-700 text-slate-200 text-sm rounded-xl px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-brand-500">
                    @error('transaction_date')<p class="text-red-400 text-xs mt-1.5">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="competence_date" class="block text-xs font-semibold text-slate-300 mb-1.5">
                        Mês de Competência <span class="text-red-400">*</span>
                    </label>
                    <input type="month" name="competence_date" id="competence_date"
                           value="{{ old('competence_date', $transaction->competence_date->format('Y-m')) }}"
                           class="w-full bg-slate-800 border border-slate-700 text-slate-200 text-sm rounded-xl px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-brand-500">
                    @error('competence_date')<p class="text-red-400 text-xs mt-1.5">{{ $message }}</p>@enderror
                </div>
            </div>

            {{-- Botões --}}
            <div class="flex items-center gap-3 pt-2 border-t border-slate-800">
                <button type="submit"
                        class="flex-1 py-2.5 bg-brand-600 hover:bg-brand-500 text-white text-sm font-semibold rounded-xl shadow-md transition-colors">
                    Salvar Alterações
                </button>
                <a href="{{ route('finance.index') }}"
                   class="px-6 py-2.5 bg-slate-800 hover:bg-slate-700 text-slate-300 text-sm font-medium rounded-xl border border-slate-700 transition-colors">
                    Cancelar
                </a>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const categorySelect  = document.getElementById('category');
    const memberField     = document.getElementById('member-field');
    const descField       = document.getElementById('description-field');
    const memberSelect    = document.getElementById('member_id');
    const memberRequired  = document.getElementById('member-required-badge');
    const memberOptional  = document.getElementById('member-optional-badge');
    const descRequired    = document.getElementById('description-required-badge');
    const descOptional    = document.getElementById('description-optional-badge');
    const descLabel       = document.getElementById('description-label');
    const descTextarea    = document.getElementById('description');
    const typeEntryInput  = document.getElementById('type-entry');
    const typeExitInput   = document.getElementById('type-exit');
    const entryBox        = document.getElementById('entry-box');
    const exitBox         = document.getElementById('exit-box');

    function updateTypeStyle() {
        if (typeEntryInput.checked) {
            entryBox.classList.add('border-emerald-500', 'bg-emerald-950/40', 'text-emerald-300');
            entryBox.classList.remove('border-slate-700', 'text-slate-400');
            exitBox.classList.remove('border-red-500', 'bg-red-950/40', 'text-red-300');
            exitBox.classList.add('border-slate-700', 'text-slate-400');
        } else {
            exitBox.classList.add('border-red-500', 'bg-red-950/40', 'text-red-300');
            exitBox.classList.remove('border-slate-700', 'text-slate-400');
            entryBox.classList.remove('border-emerald-500', 'bg-emerald-950/40', 'text-emerald-300');
            entryBox.classList.add('border-slate-700', 'text-slate-400');
        }
    }

    function updateFieldsByCategory() {
        const cat = categorySelect.value;
        memberField.classList.add('hidden');
        descField.classList.add('hidden');
        memberSelect.removeAttribute('required');
        descTextarea.removeAttribute('required');

        if (cat === 'dizimo') {
            memberField.classList.remove('hidden');
            memberRequired.classList.remove('hidden');
            memberOptional.classList.add('hidden');
            memberSelect.setAttribute('required', 'required');
        } else if (cat === 'oferta') {
            memberField.classList.remove('hidden');
            memberRequired.classList.add('hidden');
            memberOptional.classList.remove('hidden');
            descField.classList.remove('hidden');
            descLabel.textContent = 'Descrição da Oferta';
            descOptional.classList.remove('hidden');
            descRequired.classList.add('hidden');
            memberSelect.addEventListener('change', checkOfertaDescription);
            checkOfertaDescription();
        } else if (cat === 'outros') {
            descField.classList.remove('hidden');
            descLabel.textContent = 'Justificativa / Motivo';
            descRequired.classList.remove('hidden');
            descOptional.classList.add('hidden');
            descTextarea.setAttribute('required', 'required');
        } else if (cat === 'despesa') {
            descField.classList.remove('hidden');
            descLabel.textContent = 'Descrição da Despesa';
            descRequired.classList.remove('hidden');
            descOptional.classList.add('hidden');
            descTextarea.setAttribute('required', 'required');
        }

        if (cat === 'despesa') typeExitInput.checked = true;
        else if (['dizimo','oferta','outros'].includes(cat)) typeEntryInput.checked = true;
        updateTypeStyle();
    }

    function checkOfertaDescription() {
        if (!memberSelect.value) {
            descTextarea.setAttribute('required', 'required');
            descRequired.classList.remove('hidden');
            descOptional.classList.add('hidden');
        } else {
            descTextarea.removeAttribute('required');
            descRequired.classList.add('hidden');
            descOptional.classList.remove('hidden');
        }
    }

    categorySelect.addEventListener('change', updateFieldsByCategory);
    typeEntryInput.addEventListener('change', updateTypeStyle);
    typeExitInput.addEventListener('change', updateTypeStyle);

    document.querySelector('form').addEventListener('submit', function () {
        const monthInput = document.getElementById('competence_date');
        if (monthInput.value && !monthInput.value.includes('-', 7)) {
            monthInput.value = monthInput.value + '-01';
        }
    });

    updateTypeStyle();
});
</script>
@endsection
