@extends('layouts.app')

@section('title', 'Novo Lançamento')
@section('header', 'Novo Lançamento Financeiro')

@section('content')
<div class="max-w-2xl mx-auto">

    <div class="bg-slate-900/60 border border-slate-800/80 rounded-2xl overflow-hidden">
        <div class="p-5 border-b border-slate-800 flex items-center justify-between">
            <h2 class="text-sm font-semibold text-white">Registrar Lançamento</h2>
            <a href="{{ route('finance.index') }}"
               class="text-xs text-slate-400 hover:text-slate-200 transition-colors flex items-center gap-1">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
                Voltar ao Extrato
            </a>
        </div>

        <form method="POST" action="{{ route('finance.store') }}" class="p-6 space-y-5">
            @csrf

            {{-- Tipo de Lançamento --}}
            <div>
                <label class="block text-xs font-semibold text-slate-300 mb-2">Tipo de Lançamento <span class="text-red-400">*</span></label>
                <div class="grid grid-cols-2 gap-3">
                    <label id="label-entry" class="cursor-pointer">
                        <input type="radio" name="type" value="entry" id="type-entry"
                               class="sr-only" {{ old('type', 'entry') === 'entry' ? 'checked' : '' }}>
                        <div class="flex items-center justify-center gap-2 p-3 rounded-xl border-2 transition-all
                            {{ old('type', 'entry') === 'entry' ? 'border-emerald-500 bg-emerald-950/40 text-emerald-300' : 'border-slate-700 text-slate-400 hover:border-slate-600' }}"
                             id="entry-box">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18"/>
                            </svg>
                            <span class="text-sm font-semibold">Entrada</span>
                        </div>
                    </label>
                    <label id="label-exit" class="cursor-pointer">
                        <input type="radio" name="type" value="exit" id="type-exit"
                               class="sr-only" {{ old('type') === 'exit' ? 'checked' : '' }}>
                        <div class="flex items-center justify-center gap-2 p-3 rounded-xl border-2 transition-all
                            {{ old('type') === 'exit' ? 'border-red-500 bg-red-950/40 text-red-300' : 'border-slate-700 text-slate-400 hover:border-slate-600' }}"
                             id="exit-box">
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
                        class="w-full bg-slate-800 border border-slate-700 text-slate-200 text-sm rounded-xl px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-brand-500 transition-all">
                    <option value="">— Selecione a categoria —</option>
                    <option value="dizimo"  data-type="entry" {{ old('category') === 'dizimo'  ? 'selected' : '' }}>💚 Dízimo</option>
                    <option value="oferta"  data-type="entry" {{ old('category') === 'oferta'  ? 'selected' : '' }}>🟡 Oferta</option>
                    <option value="outros"  data-type="entry" {{ old('category') === 'outros'  ? 'selected' : '' }}>🔵 Outros (Entrada)</option>
                    <option value="despesa" data-type="exit"  {{ old('category') === 'despesa' ? 'selected' : '' }}>🔴 Despesa</option>
                </select>
                @error('category')<p class="text-red-400 text-xs mt-1.5">{{ $message }}</p>@enderror
            </div>

            {{-- Congregação (apenas se houver mais de uma) --}}
            @if($churches->count() > 1)
            <div>
                <label for="church_id" class="block text-xs font-semibold text-slate-300 mb-1.5">
                    Congregação <span class="text-red-400">*</span>
                </label>
                <select name="church_id" id="church_id"
                        class="w-full bg-slate-800 border border-slate-700 text-slate-200 text-sm rounded-xl px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-brand-500">
                    <option value="">— Selecione —</option>
                    @foreach($churches as $church)
                    <option value="{{ $church->id }}" {{ old('church_id', $preselectedChurchId) == $church->id ? 'selected' : '' }}>
                        {{ $church->name }}
                    </option>
                    @endforeach
                </select>
                @error('church_id')<p class="text-red-400 text-xs mt-1.5">{{ $message }}</p>@enderror
            </div>
            @else
            <input type="hidden" name="church_id" value="{{ $churches->first()->id ?? $preselectedChurchId }}">
            @endif

            {{-- Membro (dinâmico: obrigatório para dízimo, opcional para oferta) --}}
            <div id="member-field" class="hidden">
                <label for="member_id" class="block text-xs font-semibold text-slate-300 mb-1.5">
                    Membro
                    <span id="member-required-badge" class="hidden ml-1 text-red-400">*</span>
                    <span id="member-optional-badge" class="ml-1 text-slate-500 font-normal">(opcional)</span>
                </label>
                <select name="member_id" id="member_id"
                        class="w-full bg-slate-800 border border-slate-700 text-slate-200 text-sm rounded-xl px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-brand-500">
                    <option value="">— Selecione o membro —</option>
                    @foreach($members as $member)
                    <option value="{{ $member->id }}" {{ old('member_id') == $member->id ? 'selected' : '' }}>
                        {{ $member->full_name }}
                    </option>
                    @endforeach
                </select>
                @error('member_id')<p class="text-red-400 text-xs mt-1.5">{{ $message }}</p>@enderror
            </div>

            {{-- Descrição / Justificativa (dinâmica) --}}
            <div id="description-field" class="hidden">
                <label for="description" class="block text-xs font-semibold text-slate-300 mb-1.5">
                    <span id="description-label">Descrição</span>
                    <span id="description-required-badge" class="hidden ml-1 text-red-400">*</span>
                    <span id="description-optional-badge" class="ml-1 text-slate-500 font-normal hidden">(opcional)</span>
                </label>
                <textarea name="description" id="description" rows="2"
                          placeholder="Ex: Oferta do culto de quinta-feira"
                          class="w-full bg-slate-800 border border-slate-700 text-slate-200 text-sm rounded-xl px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-brand-500 resize-none placeholder-slate-600">{{ old('description') }}</textarea>
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
                           value="{{ old('amount') }}"
                           min="0.01" max="999999.99" step="0.01"
                           placeholder="0,00"
                           class="w-full bg-slate-800 border border-slate-700 text-slate-200 text-sm rounded-xl pl-10 pr-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-brand-500 placeholder-slate-600">
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
                           value="{{ old('transaction_date', now()->toDateString()) }}"
                           class="w-full bg-slate-800 border border-slate-700 text-slate-200 text-sm rounded-xl px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-brand-500">
                    @error('transaction_date')<p class="text-red-400 text-xs mt-1.5">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="competence_date" class="block text-xs font-semibold text-slate-300 mb-1.5">
                        Mês de Competência <span class="text-red-400">*</span>
                    </label>
                    <input type="month" name="competence_date" id="competence_date"
                           value="{{ old('competence_date', now()->format('Y-m')) }}"
                           class="w-full bg-slate-800 border border-slate-700 text-slate-200 text-sm rounded-xl px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-brand-500">
                    @error('competence_date')<p class="text-red-400 text-xs mt-1.5">{{ $message }}</p>@enderror
                </div>
            </div>

            {{-- Botões --}}
            <div class="flex items-center gap-3 pt-2 border-t border-slate-800">
                <button type="submit"
                        class="flex-1 py-2.5 bg-emerald-600 hover:bg-emerald-500 text-white text-sm font-semibold rounded-xl shadow-md transition-colors">
                    Registrar Lançamento
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

    // Tipo de lançamento (visual)
    const typeEntryInput = document.getElementById('type-entry');
    const typeExitInput  = document.getElementById('type-exit');
    const entryBox       = document.getElementById('entry-box');
    const exitBox        = document.getElementById('exit-box');

    function updateTypeStyle() {
        if (typeEntryInput.checked) {
            entryBox.className = entryBox.className.replace(/border-slate-700|border-red-500|bg-red-950\/40|text-red-300/g, '').trim();
            entryBox.classList.add('border-emerald-500', 'bg-emerald-950/40', 'text-emerald-300');
            exitBox.className = exitBox.className.replace(/border-emerald-500|bg-emerald-950\/40|text-emerald-300/g, '').trim();
            exitBox.classList.remove('border-red-500', 'bg-red-950/40', 'text-red-300');
            exitBox.classList.add('border-slate-700', 'text-slate-400');
        } else {
            exitBox.classList.add('border-red-500', 'bg-red-950/40', 'text-red-300');
            exitBox.classList.remove('border-slate-700', 'text-slate-400');
            entryBox.classList.remove('border-emerald-500', 'bg-emerald-950/40', 'text-emerald-300');
            entryBox.classList.add('border-slate-700', 'text-slate-400');
        }
    }

    // Lógica dinâmica de campos por categoria
    function updateFieldsByCategory() {
        const cat = categorySelect.value;

        // Oculta tudo primeiro
        memberField.classList.add('hidden');
        descField.classList.add('hidden');
        memberSelect.removeAttribute('required');
        descTextarea.removeAttribute('required');

        if (cat === 'dizimo') {
            // Dízimo: membro obrigatório, sem descrição
            memberField.classList.remove('hidden');
            memberRequired.classList.remove('hidden');
            memberOptional.classList.add('hidden');
            memberSelect.setAttribute('required', 'required');
        } else if (cat === 'oferta') {
            // Oferta: membro opcional, descrição condicional
            memberField.classList.remove('hidden');
            memberRequired.classList.add('hidden');
            memberOptional.classList.remove('hidden');

            descField.classList.remove('hidden');
            descLabel.textContent = 'Descrição da Oferta';
            descOptional.classList.remove('hidden');
            descRequired.classList.add('hidden');

            // Descrição torna-se obrigatória quando não há membro
            memberSelect.addEventListener('change', checkOfertaDescription);
            checkOfertaDescription();
        } else if (cat === 'outros') {
            // Outros: só descrição obrigatória
            descField.classList.remove('hidden');
            descLabel.textContent = 'Justificativa / Motivo';
            descRequired.classList.remove('hidden');
            descOptional.classList.add('hidden');
            descTextarea.setAttribute('required', 'required');
            descTextarea.placeholder = 'Ex: Venda de cadeira antiga / ativo patrimonial';
        } else if (cat === 'despesa') {
            // Despesa: só descrição obrigatória
            descField.classList.remove('hidden');
            descLabel.textContent = 'Descrição da Despesa';
            descRequired.classList.remove('hidden');
            descOptional.classList.add('hidden');
            descTextarea.setAttribute('required', 'required');
            descTextarea.placeholder = 'Ex: Conta de energia elétrica – Agosto/2026';
        }

        // Atualiza tipo de lançamento visualmente pela categoria
        if (cat === 'despesa') {
            typeExitInput.checked = true;
        } else if (['dizimo','oferta','outros'].includes(cat)) {
            typeEntryInput.checked = true;
        }
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

    // Converte input[type=month] → date (primeiro dia do mês) antes de submeter
    document.querySelector('form').addEventListener('submit', function () {
        const monthInput = document.getElementById('competence_date');
        if (monthInput.value && !monthInput.value.includes('-', 7)) {
            monthInput.value = monthInput.value + '-01';
        }
    });

    // Inicializa com o valor do old() se houver
    if (categorySelect.value) {
        updateFieldsByCategory();
    }
    updateTypeStyle();
});
</script>
@endsection
