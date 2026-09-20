@extends('layouts.app')

@section('title', 'Cadastrar Membro')
@section('header', 'Novo Membro')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">

    <!-- Cabeçalho -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-xl font-bold text-white tracking-tight">Cadastrar Novo Membro</h1>
            <p class="text-xs text-slate-400 mt-1">Preencha a ficha cadastral do membro e vincule à congregação correspondente.</p>
        </div>
        <a href="{{ route('members.index') }}" class="px-3.5 py-2 bg-slate-900 hover:bg-slate-800 text-slate-300 text-xs font-semibold rounded-xl border border-slate-800 transition-colors">
            Voltar para a Lista
        </a>
    </div>

    <!-- Formulário -->
    <form method="POST" action="{{ route('members.store') }}" enctype="multipart/form-data" class="bg-slate-900/60 p-6 md:p-8 rounded-2xl border border-slate-800/80 space-y-8 shadow-xl">
        @csrf

        <!-- SEÇÃO 1: VÍNCULO COM A CONGREGAÇÃO -->
        <div>
            <h2 class="text-sm font-bold text-brand-400 uppercase tracking-wider mb-4 pb-2 border-b border-slate-800">
                1. Congregação de Vínculo
            </h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div class="sm:col-span-2">
                    <label for="church_id" class="block text-xs font-medium text-slate-300 mb-1.5">Igreja / Congregação do Membro *</label>
                    <select name="church_id" id="church_id" required class="w-full px-3.5 py-2.5 bg-slate-950/80 border border-slate-800 rounded-xl text-xs text-slate-100 focus:outline-none focus:ring-1 focus:ring-brand-500">
                        <option value="">Selecione a congregação...</option>
                        @foreach($churches as $church)
                            <option value="{{ $church->id }}" {{ old('church_id', old('congregation_id', $preselectedChurchId)) == $church->id ? 'selected' : '' }}>
                                {{ $church->name }} ({{ $church->city }}/{{ $church->state }})
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>

        <!-- SEÇÃO 2: DADOS PESSOAIS E FOTO -->
        <div>
            <h2 class="text-sm font-bold text-brand-400 uppercase tracking-wider mb-4 pb-2 border-b border-slate-800">
                2. Identificação Pessoal e Foto
            </h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4">
                <!-- Nome Completo -->
                <div class="sm:col-span-2">
                    <label for="full_name" class="block text-xs font-medium text-slate-300 mb-1.5">Nome Completo (Civil) *</label>
                    <input type="text" name="full_name" id="full_name" value="{{ old('full_name') }}" required
                        placeholder="Ex: Maria das Graças Silva"
                        class="w-full px-3.5 py-2.5 bg-slate-950/80 border border-slate-800 rounded-xl text-xs text-slate-100 placeholder-slate-500 focus:outline-none focus:ring-1 focus:ring-brand-500">
                </div>

                <!-- Nome Social / Preferencial -->
                <div>
                    <label for="social_name" class="block text-xs font-medium text-slate-300 mb-1.5">Nome Social / Conhecido por</label>
                    <input type="text" name="social_name" id="social_name" value="{{ old('social_name') }}"
                        placeholder="Ex: Irmã Graça"
                        class="w-full px-3.5 py-2.5 bg-slate-950/80 border border-slate-800 rounded-xl text-xs text-slate-100 placeholder-slate-500 focus:outline-none focus:ring-1 focus:ring-brand-500">
                </div>

                <!-- Data de Nascimento -->
                <div>
                    <label for="birth_date" class="block text-xs font-medium text-slate-300 mb-1.5">Data de Nascimento</label>
                    <input type="date" name="birth_date" id="birth_date" value="{{ old('birth_date') }}"
                        class="w-full px-3.5 py-2.5 bg-slate-950/80 border border-slate-800 rounded-xl text-xs text-slate-100 focus:outline-none focus:ring-1 focus:ring-brand-500">
                </div>

                <!-- Sexo -->
                <div>
                    <label for="gender" class="block text-xs font-medium text-slate-300 mb-1.5">Sexo</label>
                    <select name="gender" id="gender" class="w-full px-3.5 py-2.5 bg-slate-950/80 border border-slate-800 rounded-xl text-xs text-slate-100 focus:outline-none focus:ring-1 focus:ring-brand-500">
                        <option value="">Selecione...</option>
                        <option value="M" {{ old('gender') === 'M' ? 'selected' : '' }}>Masculino</option>
                        <option value="F" {{ old('gender') === 'F' ? 'selected' : '' }}>Feminino</option>
                    </select>
                </div>

                <!-- Estado Civil -->
                <div>
                    <label for="marital_status" class="block text-xs font-medium text-slate-300 mb-1.5">Estado Civil</label>
                    <select name="marital_status" id="marital_status" class="w-full px-3.5 py-2.5 bg-slate-950/80 border border-slate-800 rounded-xl text-xs text-slate-100 focus:outline-none focus:ring-1 focus:ring-brand-500">
                        <option value="">Selecione...</option>
                        <option value="solteiro" {{ old('marital_status') === 'solteiro' ? 'selected' : '' }}>Solteiro(a)</option>
                        <option value="casado" {{ old('marital_status') === 'casado' ? 'selected' : '' }}>Casado(a)</option>
                        <option value="viuvo" {{ old('marital_status') === 'viuvo' ? 'selected' : '' }}>Viúvo(a)</option>
                        <option value="divorciado" {{ old('marital_status') === 'divorciado' ? 'selected' : '' }}>Divorciado(a)</option>
                        <option value="uniao_estavel" {{ old('marital_status') === 'uniao_estavel' ? 'selected' : '' }}>União Estável</option>
                    </select>
                </div>

                <!-- Foto do Membro: Upload ou Selfie -->
                <div class="sm:col-span-full bg-slate-950/60 p-4 rounded-xl border border-slate-800/80 space-y-3">
                    <div class="flex items-center justify-between flex-wrap gap-2">
                        <label class="block text-xs font-semibold text-slate-200">Foto do Membro (Formato 3x4 / Retrato)</label>
                        <div class="flex items-center gap-1.5 bg-slate-900 p-1 rounded-lg border border-slate-800 text-[11px]">
                            <button type="button" id="btn-tab-upload" onclick="switchPhotoMode('upload')" class="px-3 py-1 rounded font-medium bg-brand-600 text-white transition-colors">
                                Carregar Arquivo
                            </button>
                            <button type="button" id="btn-tab-camera" onclick="switchPhotoMode('camera')" class="px-3 py-1 rounded font-medium text-slate-400 hover:text-slate-200 transition-colors">
                                📷 Tirar Selfie
                            </button>
                        </div>
                    </div>

                    <!-- Modo 1: Upload Clássico -->
                    <div id="photo-upload-section" class="space-y-2">
                        <input type="file" name="photo" id="photo_input" accept="image/png, image/jpeg, image/webp"
                            class="w-full px-3 py-2 bg-slate-950/80 border border-slate-800 rounded-xl text-xs text-slate-400 file:mr-3 file:py-1.5 file:px-3.5 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-brand-600 file:text-white hover:file:bg-brand-500 cursor-pointer">
                        <p class="text-[10px] text-slate-500">Formatos aceitos: PNG, JPG, WEBP até 2MB.</p>
                    </div>

                    <!-- Modo 2: Selfie com Câmera WebRTC -->
                    <div id="photo-camera-section" class="hidden space-y-3">
                        <div class="flex flex-col sm:flex-row items-center gap-4">
                            <div class="relative w-40 h-48 bg-slate-900 rounded-2xl border border-slate-800 overflow-hidden flex items-center justify-center shrink-0">
                                <video id="camera-stream" autoplay playsinline class="w-full h-full object-cover hidden"></video>
                                <img id="selfie-preview" class="w-full h-full object-cover hidden" alt="Selfie capturada">
                                <div id="camera-placeholder" class="text-center p-3 text-slate-500 text-[11px]">
                                    <svg class="w-8 h-8 mx-auto mb-1 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    </svg>
                                    Câmera desligada
                                </div>
                                <canvas id="camera-canvas" class="hidden"></canvas>
                            </div>

                            <div class="space-y-2 text-xs">
                                <div class="flex items-center gap-2">
                                    <button type="button" id="btn-start-camera" onclick="startCamera()" class="px-3.5 py-2 bg-brand-600 hover:bg-brand-500 text-white font-semibold rounded-xl shadow transition-colors">
                                        Ligar Câmera
                                    </button>
                                    <button type="button" id="btn-capture-photo" onclick="captureSelfie()" class="hidden px-3.5 py-2 bg-emerald-600 hover:bg-emerald-500 text-white font-semibold rounded-xl shadow transition-colors">
                                        Capturar Foto
                                    </button>
                                    <button type="button" id="btn-retake-photo" onclick="retakeSelfie()" class="hidden px-3.5 py-2 bg-slate-800 hover:bg-slate-700 text-slate-200 font-semibold rounded-xl border border-slate-700 transition-colors">
                                        Tirar Outra
                                    </button>
                                    <button type="button" id="btn-stop-camera" onclick="stopCamera()" class="hidden px-3 py-2 bg-red-950 text-red-300 hover:bg-red-900 rounded-xl border border-red-800/40 transition-colors">
                                        Desligar
                                    </button>
                                </div>
                                <p id="camera-status" class="text-[11px] text-slate-400">Clique em "Ligar Câmera" para autorizar o uso da webcam do dispositivo.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- SEÇÃO 3: DOCUMENTAÇÃO CIVIL -->
        <div>
            <h2 class="text-sm font-bold text-brand-400 uppercase tracking-wider mb-4 pb-2 border-b border-slate-800">
                3. Documentos Civis
            </h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="cpf" class="block text-xs font-medium text-slate-300 mb-1.5">CPF</label>
                    <input type="text" name="cpf" id="cpf" value="{{ old('cpf') }}"
                        placeholder="000.000.000-00"
                        class="w-full px-3.5 py-2.5 bg-slate-950/80 border border-slate-800 rounded-xl text-xs text-slate-100 placeholder-slate-500 focus:outline-none focus:ring-1 focus:ring-brand-500">
                </div>

                <div>
                    <label for="rg" class="block text-xs font-medium text-slate-300 mb-1.5">RG e Órgão Expedidor</label>
                    <input type="text" name="rg" id="rg" value="{{ old('rg') }}"
                        placeholder="0000000 SSP/DF"
                        class="w-full px-3.5 py-2.5 bg-slate-950/80 border border-slate-800 rounded-xl text-xs text-slate-100 placeholder-slate-500 focus:outline-none focus:ring-1 focus:ring-brand-500">
                </div>
            </div>
        </div>

        <!-- SEÇÃO 4: CONTATO E RESIDÊNCIA -->
        <div>
            <h2 class="text-sm font-bold text-brand-400 uppercase tracking-wider mb-4 pb-2 border-b border-slate-800">
                4. Contato e Endereço Residencial
            </h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-4">
                <!-- WhatsApp -->
                <div>
                    <label for="whatsapp" class="block text-xs font-medium text-slate-300 mb-1.5">WhatsApp / Celular</label>
                    <input type="text" name="whatsapp" id="whatsapp" value="{{ old('whatsapp') }}"
                        placeholder="(61) 99999-9999"
                        class="w-full px-3.5 py-2.5 bg-slate-950/80 border border-slate-800 rounded-xl text-xs text-slate-100 placeholder-slate-500 focus:outline-none focus:ring-1 focus:ring-brand-500">
                </div>

                <!-- Telefone -->
                <div>
                    <label for="phone" class="block text-xs font-medium text-slate-300 mb-1.5">Telefone Fixo / Recado</label>
                    <input type="text" name="phone" id="phone" value="{{ old('phone') }}"
                        placeholder="(61) 3333-3333"
                        class="w-full px-3.5 py-2.5 bg-slate-950/80 border border-slate-800 rounded-xl text-xs text-slate-100 placeholder-slate-500 focus:outline-none focus:ring-1 focus:ring-brand-500">
                </div>

                <!-- E-mail -->
                <div class="sm:col-span-2">
                    <label for="email" class="block text-xs font-medium text-slate-300 mb-1.5">E-mail Pessoal</label>
                    <input type="email" name="email" id="email" value="{{ old('email') }}"
                        placeholder="membro@email.com"
                        class="w-full px-3.5 py-2.5 bg-slate-950/80 border border-slate-800 rounded-xl text-xs text-slate-100 placeholder-slate-500 focus:outline-none focus:ring-1 focus:ring-brand-500">
                </div>

                <!-- CEP -->
                <div>
                    <label for="zip_code" class="block text-xs font-medium text-slate-300 mb-1.5">CEP</label>
                    <input type="text" name="zip_code" id="zip_code" value="{{ old('zip_code') }}"
                        placeholder="70000-000"
                        class="w-full px-3.5 py-2.5 bg-slate-950/80 border border-slate-800 rounded-xl text-xs text-slate-100 placeholder-slate-500 focus:outline-none focus:ring-1 focus:ring-brand-500">
                </div>

                <!-- Logradouro -->
                <div class="sm:col-span-2 md:col-span-3">
                    <label for="address" class="block text-xs font-medium text-slate-300 mb-1.5">Logradouro / Rua / Quadra</label>
                    <input type="text" name="address" id="address" value="{{ old('address') }}"
                        placeholder="QNM 10 Conjunto B"
                        class="w-full px-3.5 py-2.5 bg-slate-950/80 border border-slate-800 rounded-xl text-xs text-slate-100 placeholder-slate-500 focus:outline-none focus:ring-1 focus:ring-brand-500">
                </div>

                <!-- Número -->
                <div>
                    <label for="number" class="block text-xs font-medium text-slate-300 mb-1.5">Número</label>
                    <input type="text" name="number" id="number" value="{{ old('number') }}"
                        placeholder="Casa 15 / S/N"
                        class="w-full px-3.5 py-2.5 bg-slate-950/80 border border-slate-800 rounded-xl text-xs text-slate-100 placeholder-slate-500 focus:outline-none focus:ring-1 focus:ring-brand-500">
                </div>

                <!-- Complemento -->
                <div>
                    <label for="complement" class="block text-xs font-medium text-slate-300 mb-1.5">Complemento</label>
                    <input type="text" name="complement" id="complement" value="{{ old('complement') }}"
                        placeholder="Fundos / Apto 201"
                        class="w-full px-3.5 py-2.5 bg-slate-950/80 border border-slate-800 rounded-xl text-xs text-slate-100 placeholder-slate-500 focus:outline-none focus:ring-1 focus:ring-brand-500">
                </div>

                <!-- Bairro -->
                <div>
                    <label for="neighborhood" class="block text-xs font-medium text-slate-300 mb-1.5">Bairro / Setor</label>
                    <input type="text" name="neighborhood" id="neighborhood" value="{{ old('neighborhood') }}"
                        placeholder="Ceilândia Norte"
                        class="w-full px-3.5 py-2.5 bg-slate-950/80 border border-slate-800 rounded-xl text-xs text-slate-100 placeholder-slate-500 focus:outline-none focus:ring-1 focus:ring-brand-500">
                </div>

                <!-- Cidade -->
                <div>
                    <label for="city" class="block text-xs font-medium text-slate-300 mb-1.5">Cidade / RA</label>
                    <input type="text" name="city" id="city" value="{{ old('city', 'Brasília') }}"
                        placeholder="Brasília"
                        class="w-full px-3.5 py-2.5 bg-slate-950/80 border border-slate-800 rounded-xl text-xs text-slate-100 placeholder-slate-500 focus:outline-none focus:ring-1 focus:ring-brand-500">
                </div>

                <!-- UF -->
                <div>
                    <label for="state" class="block text-xs font-medium text-slate-300 mb-1.5">UF</label>
                    <input type="text" name="state" id="state" value="{{ old('state', 'DF') }}" maxlength="2"
                        class="w-full px-3.5 py-2.5 bg-slate-950/80 border border-slate-800 rounded-xl text-xs text-slate-100 uppercase placeholder-slate-500 focus:outline-none focus:ring-1 focus:ring-brand-500">
                </div>
            </div>
        </div>

        <!-- SEÇÃO 5: SITUAÇÃO ECLESIÁSTICA -->
        <div>
            <h2 class="text-sm font-bold text-brand-400 uppercase tracking-wider mb-4 pb-2 border-b border-slate-800">
                5. Histórico Eclesiástico e Situação
            </h2>
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <!-- Cargo Eclesiástico Atual -->
                <div>
                    <label for="ecclesiastical_position" class="block text-xs font-medium text-slate-300 mb-1.5">Cargo Eclesiástico Atual *</label>
                    <select name="ecclesiastical_position" id="ecclesiastical_position" class="w-full px-3.5 py-2.5 bg-slate-950/80 border border-slate-800 rounded-xl text-xs text-slate-100 focus:outline-none focus:ring-1 focus:ring-brand-500">
                        <option value="">Selecione o cargo...</option>
                        @foreach(['Membro', 'Cooperador', 'Diácono', 'Diaconisa', 'Missionário', 'Missionária', 'Presbítero', 'Evangelista', 'Pastor'] as $cargo)
                            <option value="{{ $cargo }}" {{ old('ecclesiastical_position', 'Membro') === $cargo ? 'selected' : '' }}>
                                {{ $cargo }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Data de Entrada -->
                <div>
                    <label for="entry_date" class="block text-xs font-medium text-slate-300 mb-1.5">Data de Entrada / Batismo / Adesão</label>
                    <input type="date" name="entry_date" id="entry_date" value="{{ old('entry_date', date('Y-m-d')) }}"
                        class="w-full px-3.5 py-2.5 bg-slate-950/80 border border-slate-800 rounded-xl text-xs text-slate-100 focus:outline-none focus:ring-1 focus:ring-brand-500">
                </div>

                <!-- Situação -->
                <div>
                    <label for="status" class="block text-xs font-medium text-slate-300 mb-1.5">Situação Eclesiástica *</label>
                    <select name="status" id="status" required class="w-full px-3.5 py-2.5 bg-slate-950/80 border border-slate-800 rounded-xl text-xs text-slate-100 focus:outline-none focus:ring-1 focus:ring-brand-500">
                        <option value="ativo" {{ old('status', 'ativo') === 'ativo' ? 'selected' : '' }}>Ativo (Em comunhão plena)</option>
                        <option value="inativo" {{ old('status') === 'inativo' ? 'selected' : '' }}>Inativo</option>
                        <option value="transferido" {{ old('status') === 'transferido' ? 'selected' : '' }}>Transferido</option>
                        <option value="disciplina" {{ old('status') === 'disciplina' ? 'selected' : '' }}>Em Disciplina</option>
                        <option value="falecido" {{ old('status') === 'falecido' ? 'selected' : '' }}>Falecido</option>
                    </select>
                </div>

                <!-- Funções Exercidas (Múltiplas Seleções) -->
                <div class="sm:col-span-3 bg-slate-950/60 p-4 rounded-xl border border-slate-800/80 space-y-2 mt-2">
                    <div class="flex items-center justify-between">
                        <label class="block text-xs font-semibold text-brand-300">
                            Funções Exercidas / Atribuições Departamentais (Múltipla Escolha)
                        </label>
                        <span class="text-[10px] text-slate-400">O membro pode acumular múltiplas funções simultaneamente</span>
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-2.5 pt-1">
                        @forelse($availableFunctions as $func)
                            @if(!str_contains(mb_strtolower($func->name), 'secretá') && !str_contains(mb_strtolower($func->name), 'secretaria'))
                            <label class="flex items-center gap-2.5 p-2.5 rounded-xl border bg-slate-900/80 hover:bg-slate-800 cursor-pointer transition-all has-[:checked]:border-brand-500 has-[:checked]:bg-brand-950/50 has-[:checked]:ring-1 has-[:checked]:ring-brand-500/50 border-slate-800 text-slate-200">
                                <input type="checkbox" name="functions[]" value="{{ $func->id }}"
                                    {{ in_array($func->id, old('functions', [])) ? 'checked' : '' }}
                                    class="rounded bg-slate-950 border-slate-700 text-brand-600 focus:ring-brand-500 focus:ring-offset-slate-900 w-4 h-4">
                                <span class="text-xs font-medium select-none">{{ $func->name }}</span>
                            </label>
                            @endif
                        @empty
                            <p class="text-xs text-slate-500 col-span-full">Nenhuma função cadastrada.</p>
                        @endforelse
                    </div>
                </div>

                <!-- Observações / Anotações Gerais do Membro -->
                <div class="sm:col-span-3 bg-slate-950/60 p-4 rounded-xl border border-slate-800/80 mt-2 space-y-2">
                    <label for="notes" class="block text-xs font-semibold text-brand-300">
                        Observações / Anotações Gerais do Membro
                    </label>
                    <textarea name="notes" id="notes" rows="3"
                        placeholder="Insira anotações gerais, observações médicas, preferências ou dados complementares do membro..."
                        class="w-full px-3.5 py-2.5 bg-slate-950/80 border border-slate-800 rounded-xl text-xs text-slate-100 placeholder-slate-500 focus:outline-none focus:ring-1 focus:ring-brand-500">{{ old('notes') }}</textarea>
                </div>
            </div>
        </div>

        <!-- Botões de Ação -->
        <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-800">
            <a href="{{ route('members.index') }}" class="px-4 py-2.5 bg-slate-800 hover:bg-slate-700 text-slate-300 text-xs font-semibold rounded-xl border border-slate-700/60 transition-colors">
                Cancelar
            </a>
            <button type="submit" class="px-6 py-2.5 bg-gradient-to-r from-brand-600 to-blue-600 hover:from-brand-500 hover:to-blue-500 text-white text-xs font-semibold rounded-xl shadow-lg shadow-brand-600/30 hover:shadow-brand-600/50 transition-all duration-200">
                Salvar Membro
            </button>
        </div>
    </form>

</div>

<script>
    let streamInstance = null;

    function switchPhotoMode(mode) {
        const uploadSection = document.getElementById('photo-upload-section');
        const cameraSection = document.getElementById('photo-camera-section');
        const btnUpload = document.getElementById('btn-tab-upload');
        const btnCamera = document.getElementById('btn-tab-camera');

        if (mode === 'camera') {
            uploadSection.classList.add('hidden');
            cameraSection.classList.remove('hidden');
            btnCamera.classList.add('bg-brand-600', 'text-white');
            btnCamera.classList.remove('text-slate-400');
            btnUpload.classList.remove('bg-brand-600', 'text-white');
            btnUpload.classList.add('text-slate-400');
        } else {
            cameraSection.classList.add('hidden');
            uploadSection.classList.remove('hidden');
            btnUpload.classList.add('bg-brand-600', 'text-white');
            btnUpload.classList.remove('text-slate-400');
            btnCamera.classList.remove('bg-brand-600', 'text-white');
            btnCamera.classList.add('text-slate-400');
            stopCamera();
        }
    }

    async function startCamera() {
        const video = document.getElementById('camera-stream');
        const placeholder = document.getElementById('camera-placeholder');
        const selfiePreview = document.getElementById('selfie-preview');
        const status = document.getElementById('camera-status');

        try {
            status.textContent = 'Iniciando câmera...';
            streamInstance = await navigator.mediaDevices.getUserMedia({
                video: { width: { ideal: 640 }, height: { ideal: 640 }, facingMode: 'user' },
                audio: false
            });
            video.srcObject = streamInstance;
            video.classList.remove('hidden');
            placeholder.classList.add('hidden');
            selfiePreview.classList.add('hidden');

            document.getElementById('btn-start-camera').classList.add('hidden');
            document.getElementById('btn-capture-photo').classList.remove('hidden');
            document.getElementById('btn-stop-camera').classList.remove('hidden');
            document.getElementById('btn-retake-photo').classList.add('hidden');
            status.textContent = 'Câmera ativa. Posicione o rosto no centro e clique em "Capturar Foto".';
        } catch (err) {
            console.error(err);
            status.textContent = 'Não foi possível acessar a câmera. Verifique as permissões do navegador.';
            alert('Acesso à câmera negado ou indisponível no dispositivo.');
        }
    }

    function captureSelfie() {
        const video = document.getElementById('camera-stream');
        const canvas = document.getElementById('camera-canvas');
        const selfiePreview = document.getElementById('selfie-preview');
        const photoInput = document.getElementById('photo_input');
        const status = document.getElementById('camera-status');

        canvas.width = video.videoWidth || 480;
        canvas.height = video.videoHeight || 480;
        const ctx = canvas.getContext('2d');
        ctx.drawImage(video, 0, 0, canvas.width, canvas.height);

        canvas.toBlob((blob) => {
            if (!blob) return;
            const file = new File([blob], 'selfie_' + Date.now() + '.jpg', { type: 'image/jpeg' });
            const dataTransfer = new DataTransfer();
            dataTransfer.items.add(file);
            photoInput.files = dataTransfer.files;

            selfiePreview.src = URL.createObjectURL(blob);
            selfiePreview.classList.remove('hidden');
            video.classList.add('hidden');

            document.getElementById('btn-capture-photo').classList.add('hidden');
            document.getElementById('btn-retake-photo').classList.remove('hidden');
            status.textContent = '✅ Selfie capturada e anexada com sucesso ao cadastro!';
            stopCameraStreamOnly();
        }, 'image/jpeg', 0.9);
    }

    function retakeSelfie() {
        startCamera();
    }

    function stopCameraStreamOnly() {
        if (streamInstance) {
            streamInstance.getTracks().forEach(track => track.stop());
            streamInstance = null;
        }
    }

    function stopCamera() {
        stopCameraStreamOnly();
        const video = document.getElementById('camera-stream');
        const placeholder = document.getElementById('camera-placeholder');
        video.classList.add('hidden');
        placeholder.classList.remove('hidden');
        document.getElementById('btn-start-camera').classList.remove('hidden');
        document.getElementById('btn-capture-photo').classList.add('hidden');
        document.getElementById('btn-stop-camera').classList.add('hidden');
    }
</script>
@endsection
