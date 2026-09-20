<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Relatório Financeiro - {{ $church ? $church->name : 'Regional - DF' }} - {{ $formattedMonth }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @media print {
            body {
                background: white !important;
                color: black !important;
                font-size: 11pt;
            }
            .no-print {
                display: none !important;
            }
            .page-break {
                page-break-before: always;
            }
            @page {
                margin: 1.5cm;
                size: A4 portrait;
            }
        }
    </style>
</head>
<body class="bg-slate-100 text-slate-800 antialiased min-h-screen p-4 sm:p-8">
    <div class="max-w-4xl mx-auto bg-white p-8 sm:p-12 rounded-xl shadow-lg border border-slate-200 print:shadow-none print:border-none print:p-0">
        
        <!-- Botão de Impressão (Oculto ao Imprimir) -->
        <div class="no-print flex justify-between items-center mb-8 pb-4 border-b border-slate-200">
            <a href="{{ route('finance.index', array_filter(['church_id' => $church?->id, 'month' => $month, 'year' => $year])) }}" class="inline-flex items-center gap-2 text-sm font-semibold text-slate-600 hover:text-slate-900 transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                Voltar ao Módulo Financeiro
            </a>
            <button onclick="window.print()" class="inline-flex items-center gap-2 px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold rounded-lg shadow-sm transition-all">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                </svg>
                Imprimir Relatório
            </button>
        </div>

        <!-- Cabeçalho Oficial -->
        <div class="text-center pb-6 border-b-2 border-slate-900 mb-6">
            <h1 class="text-2xl font-bold uppercase tracking-wider text-slate-900">Demonstrativo Financeiro Mensal</h1>
            <p class="text-base font-semibold text-slate-700 mt-1">
                {{ $church ? $church->name : 'Todas as Congregações — Regional DF' }}
            </p>
            @if($church && ($church->city || $church->state))
                <p class="text-xs text-slate-500">{{ $church->city }} - {{ $church->state }}</p>
            @endif
            <div class="mt-3 inline-block px-4 py-1 bg-slate-100 rounded-full border border-slate-300 text-xs font-semibold text-slate-700 uppercase tracking-wider">
                Mês de Competência: <span class="text-slate-900 font-bold">{{ $formattedMonth }}</span>
            </div>
        </div>

        <!-- Resumo Consolidado (Cards) -->
        <div class="grid grid-cols-4 gap-4 mb-8">
            <div class="p-3 bg-emerald-50 rounded-lg border border-emerald-200">
                <p class="text-xs font-bold text-emerald-800 uppercase tracking-wider">Total Entradas</p>
                <p class="text-lg font-bold text-emerald-700 mt-1">R$ {{ number_format($totalEntries, 2, ',', '.') }}</p>
            </div>
            <div class="p-3 bg-rose-50 rounded-lg border border-rose-200">
                <p class="text-xs font-bold text-rose-800 uppercase tracking-wider">Total Saídas</p>
                <p class="text-lg font-bold text-rose-700 mt-1">R$ {{ number_format($totalExits, 2, ',', '.') }}</p>
            </div>
            <div class="p-3 bg-slate-50 rounded-lg border border-slate-200">
                <p class="text-xs font-bold text-slate-700 uppercase tracking-wider">Saldo Líquido</p>
                <p class="text-lg font-bold {{ $balance >= 0 ? 'text-slate-900' : 'text-rose-700' }} mt-1">
                    R$ {{ number_format($balance, 2, ',', '.') }}
                </p>
            </div>
            <div class="p-3 bg-indigo-50 rounded-lg border border-indigo-200">
                <p class="text-xs font-bold text-indigo-800 uppercase tracking-wider">Repasse Regional (10%)</p>
                @if($hasExemption)
                    <p class="text-sm font-bold text-amber-700 mt-1">ISENTO ({{ $hasExemption->type_label }})</p>
                @else
                    <p class="text-lg font-bold text-indigo-700 mt-1">R$ {{ number_format($remittance10, 2, ',', '.') }}</p>
                @endif
            </div>
        </div>

        <!-- Detalhamento dos Lançamentos -->
        <div class="mb-8">
            <h2 class="text-sm font-bold uppercase tracking-wider text-slate-900 mb-3 pb-1 border-b border-slate-300">
                Discriminação das Movimentações
            </h2>
            
            <table class="w-full text-left text-xs border-collapse">
                <thead>
                    <tr class="bg-slate-100 text-slate-700 border-b border-slate-300 uppercase tracking-wider">
                        <th class="py-2 px-3 font-bold">Data</th>
                        <th class="py-2 px-3 font-bold">Tipo / Categoria</th>
                        <th class="py-2 px-3 font-bold">Membro / Descrição</th>
                        @if(!$church)
                            <th class="py-2 px-3 font-bold">Congregação</th>
                        @endif
                        <th class="py-2 px-3 font-bold text-right">Valor</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200">
                    @forelse($transactions as $tx)
                        <tr class="{{ $loop->even ? 'bg-slate-50/50' : 'bg-white' }}">
                            <td class="py-2 px-3 text-slate-600 whitespace-nowrap">
                                {{ $tx->transaction_date ? $tx->transaction_date->format('d/m/Y') : $tx->competence_date->format('d/m/Y') }}
                            </td>
                            <td class="py-2 px-3 font-semibold whitespace-nowrap">
                                <span class="{{ $tx->type === 'entry' ? 'text-emerald-700' : 'text-rose-700' }}">
                                    {{ $tx->type === 'entry' ? 'Entrada' : 'Saída' }}
                                </span>
                                <span class="text-slate-400 font-normal">({{ $tx->category_label }})</span>
                            </td>
                            <td class="py-2 px-3 text-slate-800">
                                @if($tx->category === 'dizimo' && $tx->member)
                                    <span class="font-bold">{{ $tx->member->full_name }}</span>
                                    @if($tx->description)
                                        <span class="text-slate-500 font-normal block">{{ $tx->description }}</span>
                                    @endif
                                @else
                                    <span>{{ $tx->description ?? '-' }}</span>
                                @endif
                            </td>
                            @if(!$church)
                                <td class="py-2 px-3 text-slate-600">
                                    {{ $tx->church->name ?? '-' }}
                                </td>
                            @endif
                            <td class="py-2 px-3 text-right font-bold whitespace-nowrap {{ $tx->type === 'entry' ? 'text-emerald-700' : 'text-rose-700' }}">
                                {{ $tx->type === 'entry' ? '+' : '-' }} R$ {{ number_format($tx->amount, 2, ',', '.') }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ $church ? 4 : 5 }}" class="py-6 text-center text-slate-500 italic">
                                Nenhum lançamento financeiro registrado neste mês de competência.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
                <tfoot>
                    <tr class="border-t-2 border-slate-900 font-bold bg-slate-100">
                        <td colspan="{{ $church ? 3 : 4 }}" class="py-2.5 px-3 text-right text-slate-800 uppercase tracking-wider">Saldo do Período:</td>
                        <td class="py-2.5 px-3 text-right text-sm {{ $balance >= 0 ? 'text-emerald-700' : 'text-rose-700' }}">
                            R$ {{ number_format($balance, 2, ',', '.') }}
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>

        <!-- Assinaturas e Rodapé -->
        <div class="mt-16 pt-8 border-t border-slate-300">
            <div class="grid grid-cols-2 gap-12 text-center">
                <div>
                    <div class="border-t border-slate-900 pt-2 mx-8">
                        <p class="text-xs font-bold text-slate-900">Secretário / Tesoureiro</p>
                        <p class="text-[10px] text-slate-500 mt-0.5">{{ $church ? $church->name : 'Regional - DF' }}</p>
                    </div>
                </div>
                <div>
                    <div class="border-t border-slate-900 pt-2 mx-8">
                        <p class="text-xs font-bold text-slate-900">Pastor / Responsável</p>
                        <p class="text-[10px] text-slate-500 mt-0.5">{{ $church ? $church->name : 'Regional - DF' }}</p>
                    </div>
                </div>
            </div>

            <div class="mt-8 text-center text-[10px] text-slate-400">
                Relatório gerado pelo Sistema em {{ now()->format('d/m/Y \à\s H:i:s') }} por {{ auth()->user()->name ?? 'Usuário' }}
            </div>
        </div>

    </div>
</body>
</html>
