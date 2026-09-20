@extends('layouts.app')

@section('title', 'Gestão de Secretários')
@section('header', 'Gestão de Secretários e Acessos')

@section('content')
<div class="space-y-6">

    <!-- Cabeçalho com Ação Principal -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-slate-900/60 p-5 rounded-2xl border border-slate-800/80">
        <div>
            <h1 class="text-xl font-bold text-white tracking-tight">Secretários e Usuários</h1>
            <p class="text-xs text-slate-400 mt-1">Gerenciamento de papéis, permissões granulares e abrangência (Regional / Local).</p>
        </div>
        @if(auth()->user()->hasPermission('secretaries.create'))
        <div>
            <a href="{{ route('secretaries.create') }}" class="inline-flex items-center gap-2 px-4 py-2.5 bg-gradient-to-r from-brand-600 to-blue-600 hover:from-brand-500 hover:to-blue-500 text-white text-xs font-semibold rounded-xl shadow-lg shadow-brand-600/30 hover:shadow-brand-600/50 transition-all duration-200">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                <span>Novo Secretário</span>
            </a>
        </div>
        @endif
    </div>

    <!-- Barra de Filtros e Busca -->
    <form method="GET" action="{{ route('secretaries.index') }}" class="bg-slate-900/40 p-4 rounded-2xl border border-slate-800/60 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3">
        <!-- Campo Busca -->
        <div class="lg:col-span-2 relative">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Buscar por nome, e-mail ou CPF..."
                class="w-full pl-9 pr-4 py-2 bg-slate-950/80 border border-slate-800 rounded-xl text-xs text-slate-200 placeholder-slate-500 focus:outline-none focus:ring-1 focus:ring-brand-500">
            <svg class="w-4 h-4 text-slate-500 absolute left-3 top-2.5 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
            </svg>
        </div>

        <!-- Filtro Papel -->
        <div>
            <select name="role_id" class="w-full py-2 px-3 bg-slate-950/80 border border-slate-800 rounded-xl text-xs text-slate-200 focus:outline-none focus:ring-1 focus:ring-brand-500">
                <option value="">Todas as Funções</option>
                @foreach($roles as $role)
                    <option value="{{ $role->id }}" {{ request('role_id') == $role->id ? 'selected' : '' }}>{{ $role->name }}</option>
                @endforeach
            </select>
        </div>

        <!-- Filtro Abrangência -->
        <div>
            <select name="scope" class="w-full py-2 px-3 bg-slate-950/80 border border-slate-800 rounded-xl text-xs text-slate-200 focus:outline-none focus:ring-1 focus:ring-brand-500">
                <option value="">Todas as Abrangências</option>
                <option value="REGIONAL" {{ request('scope') == 'REGIONAL' ? 'selected' : '' }}>Regional (Todas as Igrejas)</option>
                <option value="LOCAL" {{ request('scope') == 'LOCAL' ? 'selected' : '' }}>Local (Igrejas Autorizadas)</option>
            </select>
        </div>

        <!-- Botões Filtrar / Limpar -->
        <div class="flex items-center gap-2">
            <button type="submit" class="flex-1 py-2 px-3 bg-slate-800 hover:bg-slate-700 text-slate-200 text-xs font-semibold rounded-xl border border-slate-700/60 transition-colors">
                Filtrar
            </button>
            @if(request()->hasAny(['search', 'role_id', 'scope', 'status', 'church_id']))
                <a href="{{ route('secretaries.index') }}" class="py-2 px-3 bg-slate-900 hover:bg-slate-800 text-slate-400 hover:text-slate-200 text-xs rounded-xl border border-slate-800 transition-colors">
                    Limpar
                </a>
            @endif
        </div>
    </form>

    <!-- Tabela de Secretários -->
    <div class="bg-slate-900/60 rounded-2xl border border-slate-800/80 overflow-hidden shadow-xl">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-300">
                <thead class="bg-slate-950/60 text-[11px] font-semibold text-slate-400 uppercase tracking-wider border-b border-slate-800">
                    <tr>
                        <th class="py-3.5 px-5">Secretário</th>
                        <th class="py-3.5 px-5">Classificação</th>
                        <th class="py-3.5 px-5">Abrangência</th>
                        <th class="py-3.5 px-5">Congregações Autorizadas</th>
                        <th class="py-3.5 px-5">Status</th>
                        <th class="py-3.5 px-5 text-right">Ações</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800/60">
                    @forelse($secretaries as $secretary)
                    <tr class="hover:bg-slate-800/30 transition-colors">
                        <!-- Secretário (Nome, CPF e Email) -->
                        <td class="py-4 px-5">
                            <div class="flex items-center gap-3">
                                @if($secretary->avatar_url)
                                    <img src="{{ $secretary->avatar_url }}" alt="{{ $secretary->name }}" class="w-9 h-9 rounded-full object-cover border border-slate-700 shadow-sm shrink-0">
                                @else
                                    <div class="w-9 h-9 rounded-full bg-slate-800 border border-slate-700 flex items-center justify-center font-bold text-xs text-brand-300 shrink-0">
                                        {{ strtoupper(substr($secretary->name, 0, 2)) }}
                                    </div>
                                @endif
                                <div>
                                    <span class="font-semibold text-white block">{{ $secretary->name }}</span>
                                    <span class="text-[11px] text-slate-400">{{ $secretary->email }}</span>
                                    @if($secretary->cpf)
                                        <span class="text-[10px] text-slate-500 block">CPF: {{ $secretary->cpf }}</span>
                                    @endif
                                </div>
                            </div>
                        </td>

                        <!-- Classificação / Papel -->
                        <td class="py-4 px-5">
                            <span class="font-medium text-slate-200">{{ $secretary->role?->name ?? 'Não definido' }}</span>
                            @if($secretary->isFirstSecretaryRegional())
                                <span class="block text-[10px] text-amber-400 font-semibold">★ Autoridade Máxima</span>
                            @endif
                        </td>

                        <!-- Abrangência -->
                        <td class="py-4 px-5">
                            @if($secretary->isRegional())
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-indigo-950 text-indigo-300 border border-indigo-500/40">
                                    REGIONAL
                                </span>
                            @else
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-950 text-emerald-300 border border-emerald-500/40">
                                    LOCAL
                                </span>
                            @endif
                        </td>

                        <!-- Congregações Autorizadas -->
                        <td class="py-4 px-5">
                            @if($secretary->isRegional())
                                <span class="text-indigo-300/90 text-[11px] font-medium flex items-center gap-1">
                                    <svg class="w-3.5 h-3.5 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                    Todas as congregações (Automático)
                                </span>
                            @else
                                <div class="flex flex-wrap gap-1 max-w-xs">
                                    @forelse($secretary->churches as $church)
                                        <span class="inline-block bg-slate-800 border border-slate-700/80 text-slate-300 text-[10px] px-1.5 py-0.5 rounded truncate max-w-[140px]" title="{{ $church->name }}">
                                            {{ $church->name }}
                                        </span>
                                    @empty
                                        <span class="text-amber-400/80 text-[10px]">Nenhuma igreja autorizada</span>
                                    @endforelse
                                </div>
                            @endif
                        </td>

                        <!-- Status -->
                        <td class="py-4 px-5">
                            @if($secretary->status === 'active')
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-medium bg-emerald-950 text-emerald-300 border border-emerald-500/30">
                                    Ativo
                                </span>
                            @elseif($secretary->status === 'inactive')
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-medium bg-amber-950 text-amber-300 border border-amber-500/30">
                                    Inativo
                                </span>
                            @else
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-medium bg-red-950 text-red-300 border border-red-500/30">
                                    Bloqueado
                                </span>
                            @endif
                        </td>

                        <!-- Ações -->
                        <td class="py-4 px-5 text-right">
                            <div class="flex items-center justify-end gap-1.5">
                                <a href="{{ route('secretaries.show', $secretary) }}" class="p-1.5 hover:bg-slate-800 rounded-lg text-slate-400 hover:text-slate-200 transition-colors" title="Visualizar Ficha">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                    </svg>
                                </a>

                                @can('update', $secretary)
                                <a href="{{ route('secretaries.edit', $secretary) }}" class="p-1.5 hover:bg-slate-800 rounded-lg text-slate-400 hover:text-brand-400 transition-colors" title="Editar">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                    </svg>
                                </a>
                                @endcan

                                @if(!$secretary->isFirstSecretaryRegional() && $secretary->id !== auth()->id())
                                    @can('delete', $secretary)
                                    <form method="POST" action="{{ route('secretaries.destroy', $secretary) }}" class="inline" onsubmit="return confirm('Deseja realmente desativar o acesso deste secretário?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="p-1.5 hover:bg-red-950/40 rounded-lg text-slate-400 hover:text-red-400 transition-colors" title="Desativar">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                            </svg>
                                        </button>
                                    </form>
                                    @endcan
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="py-8 text-center text-slate-500">
                            Nenhum secretário encontrado com os filtros selecionados.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($secretaries->hasPages())
            <div class="p-4 border-t border-slate-800 bg-slate-950/40">
                {{ $secretaries->links() }}
            </div>
        @endif
    </div>

</div>
@endsection
