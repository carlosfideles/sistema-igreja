@extends('layouts.app')

@section('title', 'Configurações do Sistema')
@section('header', 'Configurações')

@section('content')
<div class="space-y-6 max-w-6xl mx-auto">

    <!-- Cabeçalho -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-xl font-bold text-white tracking-tight flex items-center gap-2">
                <svg class="w-6 h-6 text-brand-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                </svg>
                <span>Configurações & Parâmetros Regionais</span>
            </h1>
            <p class="text-xs text-slate-400 mt-1">Gerencie as opções de Funções Exercidas (cargos ministeriais/departamentais) disponíveis no cadastro de membros.</p>
        </div>
        <div class="flex items-center gap-2">
            <span class="inline-flex items-center px-3 py-1 rounded-xl text-xs font-semibold bg-indigo-950/80 text-indigo-300 border border-indigo-500/40">
                Acesso Exclusivo: Secretaria Regional
            </span>
        </div>
    </div>

    <!-- Grade Principal de Configurações -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        <!-- Formulário: Adicionar Nova Função -->
        <div class="lg:col-span-1">
            <div class="bg-slate-900/60 p-6 rounded-2xl border border-slate-800/80 shadow-xl space-y-4">
                <div class="flex items-center gap-2 pb-3 border-b border-slate-800">
                    <div class="w-8 h-8 rounded-xl bg-brand-500/10 text-brand-400 flex items-center justify-center font-bold">
                        +
                    </div>
                    <div>
                        <h2 class="text-sm font-bold text-white">Nova Função Exercida</h2>
                        <p class="text-[11px] text-slate-400">Cadastre uma nova opção ministerial ou departamental</p>
                    </div>
                </div>

                <form method="POST" action="{{ route('settings.functions.store') }}" class="space-y-4">
                    @csrf
                    <div>
                        <label for="function_name" class="block text-xs font-medium text-slate-300 mb-1.5">Nome da Função *</label>
                        <input type="text" name="name" id="function_name" value="{{ old('name') }}" required
                            placeholder="Ex: Líder de Jovens, Regente, etc."
                            class="w-full px-3.5 py-2.5 bg-slate-950/80 border border-slate-800 rounded-xl text-xs text-slate-100 placeholder-slate-500 focus:outline-none focus:ring-1 focus:ring-brand-500">
                    </div>

                    <button type="submit" class="w-full py-2.5 bg-gradient-to-r from-brand-600 to-blue-600 hover:from-brand-500 hover:to-blue-500 text-white text-xs font-semibold rounded-xl shadow-lg shadow-brand-600/30 hover:shadow-brand-600/50 transition-all flex items-center justify-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                        </svg>
                        <span>Cadastrar Função</span>
                    </button>
                </form>

                <div class="p-3 bg-slate-950/60 rounded-xl border border-slate-800/60 text-[11px] text-slate-400 space-y-1">
                    <p class="font-semibold text-slate-300">💡 Dica de Utilização:</p>
                    <p>As funções cadastradas ficam disponíveis no formulário de membros em formato múltiplo. Um membro pode acumular várias funções ao mesmo tempo.</p>
                </div>
            </div>
        </div>

        <!-- Tabela / Lista de Funções Cadastradas -->
        <div class="lg:col-span-2">
            <div class="bg-slate-900/60 rounded-2xl border border-slate-800/80 shadow-xl overflow-hidden">
                <div class="p-5 border-b border-slate-800/80 flex items-center justify-between">
                    <div>
                        <h2 class="text-sm font-bold text-white flex items-center gap-2">
                            <span>Funções Cadastradas</span>
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-brand-950/80 text-brand-300 border border-brand-500/30">
                                {{ $functions->count() }} total
                            </span>
                        </h2>
                        <p class="text-xs text-slate-400 mt-0.5">Lista de funções ativas e inativas para atribuição de membros</p>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs text-slate-300">
                        <thead class="bg-slate-950/60 text-[11px] font-semibold text-slate-400 uppercase tracking-wider border-b border-slate-800">
                            <tr>
                                <th class="px-5 py-3">Nome da Função</th>
                                <th class="px-4 py-3 text-center">Membros Vinculados</th>
                                <th class="px-4 py-3 text-center">Situação</th>
                                <th class="px-5 py-3 text-right">Ações</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-800/60">
                            @forelse($functions as $function)
                            <tr class="hover:bg-slate-800/30 transition-colors">
                                <td class="px-5 py-3.5">
                                    <div class="font-semibold text-slate-100 flex items-center gap-2">
                                        <span>{{ $function->name }}</span>
                                        @if($function->name === '1º Secretário')
                                            <span class="px-1.5 py-0.2 rounded text-[9px] font-bold bg-amber-950/80 text-amber-300 border border-amber-500/30">Padrão Sistema</span>
                                        @endif
                                    </div>
                                </td>
                                <td class="px-4 py-3.5 text-center">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[11px] font-medium bg-slate-950 text-slate-300 border border-slate-800">
                                        {{ $function->members_count }} membro(s)
                                    </span>
                                </td>
                                <td class="px-4 py-3.5 text-center">
                                    @if($function->is_active)
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[10px] font-bold bg-emerald-950/80 text-emerald-300 border border-emerald-500/30">
                                            Ativa
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[10px] font-bold bg-slate-800 text-slate-400 border border-slate-700">
                                            Inativa
                                        </span>
                                    @endif
                                </td>
                                <td class="px-5 py-3.5 text-right space-x-2">
                                    <!-- Botão Editar -->
                                    <button type="button"
                                        onclick="openEditModal({{ $function->id }}, '{{ addslashes($function->name) }}')"
                                        class="px-2.5 py-1.5 bg-slate-800 hover:bg-slate-700 text-slate-300 hover:text-white rounded-lg border border-slate-700/60 transition-colors inline-flex items-center gap-1 text-[11px]">
                                        <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                        </svg>
                                        <span>Editar</span>
                                    </button>

                                    <!-- Botão Alternar Status -->
                                    <form method="POST" action="{{ route('settings.functions.toggle', $function) }}" class="inline-block" onsubmit="return confirm('Deseja realmente alterar a situação da função \'{{ addslashes($function->name) }}\'?');">
                                        @csrf
                                        @method('PATCH')
                                        @if($function->is_active)
                                            <button type="submit" class="px-2.5 py-1.5 bg-amber-950/50 hover:bg-amber-900/60 text-amber-300 rounded-lg border border-amber-600/30 transition-colors inline-flex items-center gap-1 text-[11px]">
                                                <span>Desativar</span>
                                            </button>
                                        @else
                                            <button type="submit" class="px-2.5 py-1.5 bg-emerald-950/50 hover:bg-emerald-900/60 text-emerald-300 rounded-lg border border-emerald-600/30 transition-colors inline-flex items-center gap-1 text-[11px]">
                                                <span>Ativar</span>
                                            </button>
                                        @endif
                                    </form>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="4" class="px-5 py-8 text-center text-slate-500 text-xs">
                                    Nenhuma função cadastrada até o momento.
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>

</div>

<!-- Modal de Edição de Função -->
<div id="editFunctionModal" class="fixed inset-0 z-50 hidden bg-slate-950/80 backdrop-blur-sm flex items-center justify-center p-4">
    <div class="bg-slate-900 border border-slate-800 rounded-2xl max-w-md w-full p-6 shadow-2xl space-y-4">
        <div class="flex items-center justify-between pb-3 border-b border-slate-800">
            <h3 class="text-sm font-bold text-white">Editar Nome da Função</h3>
            <button type="button" onclick="closeEditModal()" class="text-slate-400 hover:text-white p-1">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>

        <form id="editFunctionForm" method="POST" action="" class="space-y-4">
            @csrf
            @method('PUT')
            <div>
                <label for="edit_function_name" class="block text-xs font-medium text-slate-300 mb-1.5">Nome da Função *</label>
                <input type="text" name="name" id="edit_function_name" required
                    class="w-full px-3.5 py-2.5 bg-slate-950/80 border border-slate-800 rounded-xl text-xs text-slate-100 focus:outline-none focus:ring-1 focus:ring-brand-500">
            </div>

            <div class="flex items-center justify-end gap-2 pt-3 border-t border-slate-800">
                <button type="button" onclick="closeEditModal()" class="px-4 py-2 bg-slate-800 hover:bg-slate-700 text-slate-300 text-xs font-medium rounded-xl">
                    Cancelar
                </button>
                <button type="submit" class="px-4 py-2 bg-brand-600 hover:bg-brand-500 text-white text-xs font-semibold rounded-xl shadow-md transition-all">
                    Salvar Alteração
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    function openEditModal(functionId, currentName) {
        const modal = document.getElementById('editFunctionModal');
        const form = document.getElementById('editFunctionForm');
        const input = document.getElementById('edit_function_name');

        form.action = `/settings/functions/${functionId}`;
        input.value = currentName;
        modal.classList.remove('hidden');
    }

    function closeEditModal() {
        document.getElementById('editFunctionModal').classList.add('hidden');
    }
</script>
@endsection
