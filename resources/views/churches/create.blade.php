@extends('layouts.app')

@section('title', 'Cadastrar Congregação')
@section('header', 'Nova Congregação')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">

    <!-- Cabeçalho -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-xl font-bold text-white tracking-tight">Cadastrar Nova Congregação</h1>
            <p class="text-xs text-slate-400 mt-1">Preencha os dados institucionais, localização e liderança responsável.</p>
        </div>
        <a href="{{ route('churches.index') }}" class="px-3.5 py-2 bg-slate-900 hover:bg-slate-800 text-slate-300 text-xs font-semibold rounded-xl border border-slate-800 transition-colors">
            Voltar para a Lista
        </a>
    </div>

    <!-- Formulário -->
    <form method="POST" action="{{ route('churches.store') }}" enctype="multipart/form-data" class="bg-slate-900/60 p-6 md:p-8 rounded-2xl border border-slate-800/80 space-y-8 shadow-xl">
        @csrf

        <!-- SEÇÃO 1: DADOS INSTITUCIONAIS -->
        <div>
            <h2 class="text-sm font-bold text-brand-400 uppercase tracking-wider mb-4 pb-2 border-b border-slate-800">
                1. Identificação Institucional
            </h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4">
                <!-- Nome -->
                <div class="sm:col-span-2">
                    <label for="name" class="block text-xs font-medium text-slate-300 mb-1.5">Nome da Congregação / Igreja *</label>
                    <input type="text" name="name" id="name" value="{{ old('name') }}" required
                        placeholder="Ex: IEADM - Congregação Taguatinga Norte"
                        class="w-full px-3.5 py-2.5 bg-slate-950/80 border border-slate-800 rounded-xl text-xs text-slate-100 placeholder-slate-500 focus:outline-none focus:ring-1 focus:ring-brand-500">
                </div>

                <!-- Código Identificador -->
                <div>
                    <label for="code" class="block text-xs font-medium text-slate-300 mb-1.5">Código Único (Sigla)</label>
                    <input type="text" name="code" id="code" value="{{ old('code') }}"
                        placeholder="Ex: IG-TAG-01"
                        class="w-full px-3.5 py-2.5 bg-slate-950/80 border border-slate-800 rounded-xl text-xs text-slate-100 placeholder-slate-500 focus:outline-none focus:ring-1 focus:ring-brand-500">
                </div>

                <!-- CNPJ -->
                <div>
                    <label for="cnpj" class="block text-xs font-medium text-slate-300 mb-1.5">CNPJ</label>
                    <input type="text" name="cnpj" id="cnpj" value="{{ old('cnpj') }}"
                        placeholder="00.000.000/0001-00"
                        class="w-full px-3.5 py-2.5 bg-slate-950/80 border border-slate-800 rounded-xl text-xs text-slate-100 placeholder-slate-500 focus:outline-none focus:ring-1 focus:ring-brand-500">
                </div>

                <!-- Telefone -->
                <div>
                    <label for="phone" class="block text-xs font-medium text-slate-300 mb-1.5">Telefone de Contato</label>
                    <input type="text" name="phone" id="phone" value="{{ old('phone') }}"
                        placeholder="(61) 3333-0000"
                        class="w-full px-3.5 py-2.5 bg-slate-950/80 border border-slate-800 rounded-xl text-xs text-slate-100 placeholder-slate-500 focus:outline-none focus:ring-1 focus:ring-brand-500">
                </div>

                <!-- E-mail -->
                <div>
                    <label for="email" class="block text-xs font-medium text-slate-300 mb-1.5">E-mail Institucional</label>
                    <input type="email" name="email" id="email" value="{{ old('email') }}"
                        placeholder="taguatinga@ieadm-df.org.br"
                        class="w-full px-3.5 py-2.5 bg-slate-950/80 border border-slate-800 rounded-xl text-xs text-slate-100 placeholder-slate-500 focus:outline-none focus:ring-1 focus:ring-brand-500">
                </div>
            </div>
        </div>

        <!-- SEÇÃO 2: LOCALIZAÇÃO E ENDEREÇO -->
        <div>
            <h2 class="text-sm font-bold text-brand-400 uppercase tracking-wider mb-4 pb-2 border-b border-slate-800">
                2. Localização e Endereço
            </h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-4">
                <!-- CEP -->
                <div>
                    <label for="zip_code" class="block text-xs font-medium text-slate-300 mb-1.5">CEP</label>
                    <input type="text" name="zip_code" id="zip_code" value="{{ old('zip_code') }}"
                        placeholder="70000-000"
                        class="w-full px-3.5 py-2.5 bg-slate-950/80 border border-slate-800 rounded-xl text-xs text-slate-100 placeholder-slate-500 focus:outline-none focus:ring-1 focus:ring-brand-500">
                </div>

                <!-- Logradouro -->
                <div class="sm:col-span-2 md:col-span-3">
                    <label for="address" class="block text-xs font-medium text-slate-300 mb-1.5">Endereço / Logradouro</label>
                    <input type="text" name="address" id="address" value="{{ old('address') }}"
                        placeholder="QND 15, Avenida Comercial"
                        class="w-full px-3.5 py-2.5 bg-slate-950/80 border border-slate-800 rounded-xl text-xs text-slate-100 placeholder-slate-500 focus:outline-none focus:ring-1 focus:ring-brand-500">
                </div>

                <!-- Número -->
                <div>
                    <label for="number" class="block text-xs font-medium text-slate-300 mb-1.5">Número</label>
                    <input type="text" name="number" id="number" value="{{ old('number') }}"
                        placeholder="Lote 12 / S/N"
                        class="w-full px-3.5 py-2.5 bg-slate-950/80 border border-slate-800 rounded-xl text-xs text-slate-100 placeholder-slate-500 focus:outline-none focus:ring-1 focus:ring-brand-500">
                </div>

                <!-- Complemento -->
                <div>
                    <label for="complement" class="block text-xs font-medium text-slate-300 mb-1.5">Complemento</label>
                    <input type="text" name="complement" id="complement" value="{{ old('complement') }}"
                        placeholder="Sala 101, Galpão"
                        class="w-full px-3.5 py-2.5 bg-slate-950/80 border border-slate-800 rounded-xl text-xs text-slate-100 placeholder-slate-500 focus:outline-none focus:ring-1 focus:ring-brand-500">
                </div>

                <!-- Bairro -->
                <div>
                    <label for="neighborhood" class="block text-xs font-medium text-slate-300 mb-1.5">Bairro / Setor</label>
                    <input type="text" name="neighborhood" id="neighborhood" value="{{ old('neighborhood') }}"
                        placeholder="Taguatinga Norte"
                        class="w-full px-3.5 py-2.5 bg-slate-950/80 border border-slate-800 rounded-xl text-xs text-slate-100 placeholder-slate-500 focus:outline-none focus:ring-1 focus:ring-brand-500">
                </div>

                <!-- Cidade -->
                <div>
                    <label for="city" class="block text-xs font-medium text-slate-300 mb-1.5">Cidade / RA *</label>
                    <input type="text" name="city" id="city" value="{{ old('city', 'Brasília') }}" required
                        placeholder="Brasília"
                        class="w-full px-3.5 py-2.5 bg-slate-950/80 border border-slate-800 rounded-xl text-xs text-slate-100 placeholder-slate-500 focus:outline-none focus:ring-1 focus:ring-brand-500">
                </div>

                <!-- UF -->
                <div>
                    <label for="state" class="block text-xs font-medium text-slate-300 mb-1.5">UF *</label>
                    <input type="text" name="state" id="state" value="{{ old('state', 'DF') }}" required maxlength="2"
                        placeholder="DF"
                        class="w-full px-3.5 py-2.5 bg-slate-950/80 border border-slate-800 rounded-xl text-xs text-slate-100 uppercase placeholder-slate-500 focus:outline-none focus:ring-1 focus:ring-brand-500">
                </div>
            </div>
        </div>

        <!-- SEÇÃO 3: LIDERANÇA, FUNDAÇÃO E SITUAÇÃO -->
        <div>
            <h2 class="text-sm font-bold text-brand-400 uppercase tracking-wider mb-4 pb-2 border-b border-slate-800">
                3. Liderança, Fundação e Situação
            </h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4">
                <!-- Dirigente Responsável -->
                <div class="sm:col-span-2">
                    <label for="responsible_name" class="block text-xs font-medium text-slate-300 mb-1.5">Pastor / Dirigente Responsável</label>
                    <input type="text" name="responsible_name" id="responsible_name" value="{{ old('responsible_name') }}"
                        placeholder="Pr. João Carlos de Souza"
                        class="w-full px-3.5 py-2.5 bg-slate-950/80 border border-slate-800 rounded-xl text-xs text-slate-100 placeholder-slate-500 focus:outline-none focus:ring-1 focus:ring-brand-500">
                </div>

                <!-- Data de Fundação -->
                <div>
                    <label for="foundation_date" class="block text-xs font-medium text-slate-300 mb-1.5">Data de Fundação</label>
                    <input type="date" name="foundation_date" id="foundation_date" value="{{ old('foundation_date') }}"
                        class="w-full px-3.5 py-2.5 bg-slate-950/80 border border-slate-800 rounded-xl text-xs text-slate-100 focus:outline-none focus:ring-1 focus:ring-brand-500">
                </div>

                <!-- Situação -->
                <div class="sm:col-span-1">
                    <label for="status" class="block text-xs font-medium text-slate-300 mb-1.5">Situação *</label>
                    <select name="status" id="status" required class="w-full px-3.5 py-2.5 bg-slate-950/80 border border-slate-800 rounded-xl text-xs text-slate-100 focus:outline-none focus:ring-1 focus:ring-brand-500">
                        <option value="active" {{ old('status', 'active') === 'active' ? 'selected' : '' }}>Ativa (Em pleno funcionamento)</option>
                        <option value="inactive" {{ old('status') === 'inactive' ? 'selected' : '' }}>Inativa / Desativada</option>
                    </select>
                </div>

                <!-- Observações -->
                <div class="sm:col-span-full">
                    <label for="notes" class="block text-xs font-medium text-slate-300 mb-1.5">Observações Adicionais</label>
                    <textarea name="notes" id="notes" rows="3" placeholder="Informações de histórico ou detalhes complementares..."
                        class="w-full px-3.5 py-2.5 bg-slate-950/80 border border-slate-800 rounded-xl text-xs text-slate-100 placeholder-slate-500 focus:outline-none focus:ring-1 focus:ring-brand-500">{{ old('notes') }}</textarea>
                </div>
            </div>
        </div>

        <!-- Botões de Ação -->
        <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-800">
            <a href="{{ route('churches.index') }}" class="px-4 py-2.5 bg-slate-800 hover:bg-slate-700 text-slate-300 text-xs font-semibold rounded-xl border border-slate-700/60 transition-colors">
                Cancelar
            </a>
            <button type="submit" class="px-6 py-2.5 bg-gradient-to-r from-brand-600 to-blue-600 hover:from-brand-500 hover:to-blue-500 text-white text-xs font-semibold rounded-xl shadow-lg shadow-brand-600/30 hover:shadow-brand-600/50 transition-all duration-200">
                Salvar Congregação
            </button>
        </div>
    </form>

</div>
@endsection
