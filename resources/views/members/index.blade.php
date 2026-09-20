@extends('layouts.app')

@section('title', 'Gestão de Membros')
@section('header', 'Membros da Regional')

@section('content')
<div class="space-y-6">

    <!-- Cabeçalho do Módulo e Resumo -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-slate-900/60 p-5 rounded-2xl border border-slate-800/80">
        <div>
            <h1 class="text-xl font-bold text-white tracking-tight">Cadastro de Membros</h1>
            <p class="text-xs text-slate-400 mt-1">Gerenciamento completo do rol de membros, fichas cadastrais e histórico eclesiástico.</p>
        </div>
        @can('create', App\Models\Member::class)
        <div>
            <a href="{{ route('members.create') }}" class="inline-flex items-center gap-2 px-4 py-2.5 bg-gradient-to-r from-brand-600 to-blue-600 hover:from-brand-500 hover:to-blue-500 text-white text-xs font-semibold rounded-xl shadow-lg shadow-brand-600/30 hover:shadow-brand-600/50 transition-all duration-200">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/>
                </svg>
                <span>Novo Membro</span>
            </a>
        </div>
        @endcan
    </div>

    <!-- Cards de Resumo -->
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
        <div class="bg-slate-900/60 p-4 rounded-2xl border border-slate-800/80">
            <span class="text-[10px] font-semibold text-slate-400 uppercase tracking-wider block">Total no Rol</span>
            <span class="text-2xl font-bold text-white mt-1 block">{{ $totalMembers }}</span>
        </div>
        <div class="bg-slate-900/60 p-4 rounded-2xl border border-slate-800/80">
            <span class="text-[10px] font-semibold text-emerald-400 uppercase tracking-wider block">Membros Ativos</span>
            <span class="text-2xl font-bold text-emerald-300 mt-1 block">{{ $activeMembers }}</span>
        </div>
        <div class="bg-slate-900/60 p-4 rounded-2xl border border-slate-800/80">
            <span class="text-[10px] font-semibold text-amber-400 uppercase tracking-wider block">Membros Inativos</span>
            <span class="text-2xl font-bold text-amber-300 mt-1 block">{{ $inactiveMembers }}</span>
        </div>
        <div class="bg-slate-900/60 p-4 rounded-2xl border border-slate-800/80">
            <span class="text-[10px] font-semibold text-indigo-400 uppercase tracking-wider block">Transferidos</span>
            <span class="text-2xl font-bold text-indigo-300 mt-1 block">{{ $transferredMembers }}</span>
        </div>
    </div>

    <!-- Barra de Filtros Avançados -->
    <form method="GET" action="{{ route('members.index') }}" class="bg-slate-900/40 p-4 rounded-2xl border border-slate-800/60 space-y-3">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-6 gap-3">
            <!-- Busca Geral -->
            <div class="lg:col-span-2 relative">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Nome, CPF, RG ou telefone..."
                    class="w-full pl-9 pr-4 py-2 bg-slate-950/80 border border-slate-800 rounded-xl text-xs text-slate-200 placeholder-slate-500 focus:outline-none focus:ring-1 focus:ring-brand-500">
                <svg class="w-4 h-4 text-slate-500 absolute left-3 top-2.5 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
            </div>

            <!-- Filtro Congregação -->
            <div>
                <select name="church_id" class="w-full py-2 px-3 bg-slate-950/80 border border-slate-800 rounded-xl text-xs text-slate-200 focus:outline-none focus:ring-1 focus:ring-brand-500">
                    <option value="">Todas as Igrejas</option>
                    @foreach($churches as $church)
                        <option value="{{ $church->id }}" {{ request('church_id') == $church->id ? 'selected' : '' }}>{{ $church->name }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Filtro Situação -->
            <div>
                <select name="status" class="w-full py-2 px-3 bg-slate-950/80 border border-slate-800 rounded-xl text-xs text-slate-200 focus:outline-none focus:ring-1 focus:ring-brand-500">
                    <option value="ativo" {{ request('status', 'ativo') === 'ativo' ? 'selected' : '' }}>Ativos</option>
                    <option value="inativo" {{ request('status') === 'inativo' ? 'selected' : '' }}>Inativos</option>
                    <option value="transferido" {{ request('status') === 'transferido' ? 'selected' : '' }}>Transferidos</option>
                    <option value="disciplina" {{ request('status') === 'disciplina' ? 'selected' : '' }}>Em Disciplina</option>
                    <option value="falecido" {{ request('status') === 'falecido' ? 'selected' : '' }}>Falecidos</option>
                    <option value="all" {{ request('status') === 'all' ? 'selected' : '' }}>Todas as Situações</option>
                </select>
            </div>

            <!-- Filtro Sexo -->
            <div>
                <select name="gender" class="w-full py-2 px-3 bg-slate-950/80 border border-slate-800 rounded-xl text-xs text-slate-200 focus:outline-none focus:ring-1 focus:ring-brand-500">
                    <option value="">Todos os Sexos</option>
                    <option value="M" {{ request('gender') === 'M' ? 'selected' : '' }}>Masculino</option>
                    <option value="F" {{ request('gender') === 'F' ? 'selected' : '' }}>Feminino</option>
                </select>
            </div>

            <!-- Filtro Faixa Etária -->
            <div>
                <select name="age_group" class="w-full py-2 px-3 bg-slate-950/80 border border-slate-800 rounded-xl text-xs text-slate-200 focus:outline-none focus:ring-1 focus:ring-brand-500">
                    <option value="">Todas as Idades</option>
                    <option value="0-12" {{ request('age_group') === '0-12' ? 'selected' : '' }}>Crianças (0-12 anos)</option>
                    <option value="13-17" {{ request('age_group') === '13-17' ? 'selected' : '' }}>Adolescentes (13-17 anos)</option>
                    <option value="18-29" {{ request('age_group') === '18-29' ? 'selected' : '' }}>Jovens (18-29 anos)</option>
                    <option value="30-59" {{ request('age_group') === '30-59' ? 'selected' : '' }}>Adultos (30-59 anos)</option>
                    <option value="60+" {{ request('age_group') === '60+' ? 'selected' : '' }}>Melhor Idade (60+ anos)</option>
                </select>
            </div>
        </div>

        <!-- Linha Secundária: Período de Entrada e Botões -->
        <div class="flex flex-col sm:flex-row items-center justify-between gap-3 pt-2 border-t border-slate-800/60">
            <div class="flex items-center gap-2 text-xs text-slate-400 w-full sm:w-auto">
                <span class="shrink-0">Data de Entrada:</span>
                <input type="date" name="entry_start" value="{{ request('entry_start') }}" class="py-1 px-2.5 bg-slate-950/80 border border-slate-800 rounded-lg text-xs text-slate-200">
                <span>até</span>
                <input type="date" name="entry_end" value="{{ request('entry_end') }}" class="py-1 px-2.5 bg-slate-950/80 border border-slate-800 rounded-lg text-xs text-slate-200">
            </div>

            <div class="flex items-center gap-2 w-full sm:w-auto justify-end">
                <button type="submit" class="py-1.5 px-4 bg-slate-800 hover:bg-slate-700 text-slate-200 text-xs font-semibold rounded-xl border border-slate-700/60 transition-colors">
                    Aplicar Filtros
                </button>
                @if(request()->hasAny(['search', 'church_id', 'status', 'gender', 'age_group', 'entry_start', 'entry_end']))
                    <a href="{{ route('members.index') }}" class="py-1.5 px-3 bg-slate-900 hover:bg-slate-800 text-slate-400 hover:text-slate-200 text-xs rounded-xl border border-slate-800 transition-colors">
                        Limpar
                    </a>
                @endif
            </div>
        </div>
    </form>

    <!-- Tabela de Membros -->
    <div class="bg-slate-900/60 rounded-2xl border border-slate-800/80 overflow-hidden shadow-xl">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-300">
                <thead class="bg-slate-950/60 text-[11px] font-semibold text-slate-400 uppercase tracking-wider border-b border-slate-800">
                    <tr>
                        <th class="py-3.5 px-5">Membro</th>
                        <th class="py-3.5 px-5">Congregação</th>
                        <th class="py-3.5 px-5">Contato</th>
                        <th class="py-3.5 px-5">Data de Entrada</th>
                        <th class="py-3.5 px-5">Situação</th>
                        <th class="py-3.5 px-5 text-right">Ações</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800/60">
                    @forelse($members as $member)
                    <tr class="hover:bg-slate-800/30 transition-colors">
                        <!-- Foto e Nome -->
                        <td class="py-4 px-5">
                            <div class="flex items-center gap-3">
                                @if($member->photo)
                                    <img src="{{ $member->photo }}" alt="{{ $member->full_name }}" class="w-10 h-10 rounded-xl object-cover border border-slate-700 shrink-0">
                                @else
                                    <div class="w-10 h-10 rounded-xl bg-slate-800 border border-slate-700 flex items-center justify-center font-bold text-xs text-brand-300 shrink-0">
                                        {{ strtoupper(substr($member->full_name, 0, 2)) }}
                                    </div>
                                @endif
                                <div>
                                    <span class="font-semibold text-white block">{{ $member->full_name }}</span>
                                    @if($member->social_name)
                                        <span class="text-[10px] text-slate-400 block">({{ $member->social_name }})</span>
                                    @endif
                                    <div class="text-[10px] text-slate-500 mt-0.5">
                                        @if($member->birth_date)
                                            {{ \Carbon\Carbon::parse($member->birth_date)->age }} anos •
                                        @endif
                                        {{ $member->gender === 'M' ? 'Masculino' : ($member->gender === 'F' ? 'Feminino' : '') }}
                                    </div>
                                </div>
                            </div>
                        </td>

                        <!-- Congregação -->
                        <td class="py-4 px-5">
                            <span class="font-medium text-slate-200 block">{{ $member->church->name }}</span>
                            <span class="text-[10px] text-slate-400">{{ $member->church->city }}/{{ $member->church->state }}</span>
                        </td>

                        <!-- Contato -->
                        <td class="py-4 px-5">
                            @if($member->whatsapp || $member->phone)
                                <span class="text-slate-200 block">{{ $member->whatsapp ?? $member->phone }}</span>
                            @else
                                <span class="text-slate-500 block text-[10px]">Sem telefone</span>
                            @endif
                            @if($member->email)
                                <span class="text-[10px] text-slate-400 block">{{ $member->email }}</span>
                            @endif
                        </td>

                        <!-- Data de Entrada -->
                        <td class="py-4 px-5 text-slate-300">
                            {{ $member->entry_date ? $member->entry_date->format('d/m/Y') : 'Não informada' }}
                        </td>

                        <!-- Situação -->
                        <td class="py-4 px-5">
                            @if($member->status === 'ativo')
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-medium bg-emerald-950 text-emerald-300 border border-emerald-500/30">
                                    Ativo
                                </span>
                            @elseif($member->status === 'transferido')
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-medium bg-indigo-950 text-indigo-300 border border-indigo-500/30">
                                    Transferido
                                </span>
                            @elseif($member->status === 'disciplina')
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-medium bg-amber-950 text-amber-300 border border-amber-500/30">
                                    Em Disciplina
                                </span>
                            @elseif($member->status === 'falecido')
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-medium bg-slate-900 text-slate-400 border border-slate-700">
                                    Falecido
                                </span>
                            @else
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-medium bg-red-950 text-red-300 border border-red-500/30">
                                    Inativo
                                </span>
                            @endif
                        </td>

                        <!-- Ações -->
                        <td class="py-4 px-5 text-right">
                            <div class="flex items-center justify-end gap-1.5">
                                <a href="{{ route('members.show', $member) }}" class="p-1.5 hover:bg-slate-800 rounded-lg text-slate-400 hover:text-slate-200 transition-colors" title="Visualizar Ficha">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                    </svg>
                                </a>

                                <a href="{{ route('members.print', $member) }}" target="_blank" class="p-1.5 hover:bg-slate-800 rounded-lg text-slate-400 hover:text-indigo-400 transition-colors" title="Imprimir Ficha">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                                    </svg>
                                </a>

                                @can('update', $member)
                                <a href="{{ route('members.edit', $member) }}" class="p-1.5 hover:bg-slate-800 rounded-lg text-slate-400 hover:text-brand-400 transition-colors" title="Editar">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                    </svg>
                                </a>
                                @endcan

                                @can('delete', $member)
                                <form method="POST" action="{{ route('members.destroy', $member) }}" class="inline" onsubmit="return confirm('Deseja realmente desativar este membro?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="p-1.5 hover:bg-red-950/40 rounded-lg text-slate-400 hover:text-red-400 transition-colors" title="Desativar">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                        </svg>
                                    </button>
                                </form>
                                @endcan
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="py-10 text-center text-slate-500">
                            Nenhum membro encontrado com os filtros selecionados.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($members->hasPages())
            <div class="p-4 border-t border-slate-800 bg-slate-950/40">
                {{ $members->links() }}
            </div>
        @endif
    </div>

</div>
@endsection
