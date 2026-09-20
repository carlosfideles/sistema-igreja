<!DOCTYPE html>
<html lang="pt-BR" class="h-full bg-slate-100 text-slate-900">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Relatório Oficial') — {{ config('app.name', 'IGREJA EVANGÉLICA ASSEMBLEIA DE DEUS DA MISSÃO') }}</title>

    <!-- Google Fonts: Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>

    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f8fafc;
        }

        @media print {
            body {
                background: #ffffff !important;
                color: #000000 !important;
                font-size: 10pt;
            }
            .no-print {
                display: none !important;
            }
            .print-card {
                border: none !important;
                box-shadow: none !important;
                padding: 0 !important;
                max-width: 100% !important;
            }
            table {
                font-size: 9pt;
            }
            tr {
                page-break-inside: avoid;
            }
        }
    </style>
</head>
<body class="p-4 sm:p-8">

    <!-- Barra de Controle de Impressão (Oculta na Impressão) -->
    <div class="no-print max-w-6xl mx-auto mb-6 flex items-center justify-between bg-slate-900 text-white p-4 rounded-2xl shadow-xl">
        <div class="flex items-center gap-3">
            <span class="text-xs text-slate-300">Modo de Impressão Oficial • Relatórios — {{ (auth()->check() && (auth()->user()->isRegional() || auth()->user()->isFirstSecretaryRegional())) ? 'Regional - DF' : (auth()->user()->churches->pluck('name')->implode(', ') ?: 'Congregação Local') }}</span>
        </div>
        <div class="flex items-center gap-2">
            <button onclick="window.print()" class="px-4 py-2 bg-brand-600 hover:bg-brand-500 text-white text-xs font-bold rounded-xl shadow transition-colors flex items-center gap-1.5">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                </svg>
                <span>Imprimir / Salvar PDF</span>
            </button>
            <button onclick="window.close()" class="px-3 py-2 bg-slate-800 hover:bg-slate-700 text-slate-300 text-xs font-semibold rounded-xl transition-colors">
                Fechar
            </button>
        </div>
    </div>

    <!-- Documento de Impressão Oficial -->
    <div class="print-card max-w-6xl mx-auto bg-white p-8 sm:p-10 rounded-2xl border border-slate-300 shadow-sm text-slate-900">

        <!-- Cabeçalho Oficial com Logotipo (Regra 27.1) -->
        <div class="flex items-center justify-between border-b-2 border-slate-900 pb-4 mb-6">
            <div class="flex items-center gap-4">
                <img src="/assets/images/logo.png" alt="SISTEMA DE GESTÃO REGIONAL" class="h-14 w-auto object-contain" onerror="this.style.display='none'; document.getElementById('report-logo-fallback').classList.remove('hidden');">
                <div id="report-logo-fallback" class="hidden flex items-center justify-center w-12 h-12 rounded-xl bg-slate-900 text-white font-black text-base tracking-wider">
                    IEADM
                </div>
                <div>
                    <h1 class="text-base font-black tracking-tight uppercase">IGREJA EVANGÉLICA ASSEMBLEIA DE DEUS DA MISSÃO</h1>
                    <p class="text-xs font-bold text-slate-700 uppercase tracking-wider">
                        @if(auth()->check() && (auth()->user()->isRegional() || auth()->user()->isFirstSecretaryRegional()))
                            Regional - DF
                        @elseif(auth()->check() && auth()->user()->churches->isNotEmpty())
                            {{ auth()->user()->churches->pluck('name')->implode(', ') }}
                        @else
                            Regional - DF
                        @endif
                    </p>
                    <p class="text-[11px] font-bold text-brand-600 mt-0.5 uppercase tracking-wide">@yield('report_title', 'Relatório Oficial')</p>
                </div>
            </div>

            <div class="text-right text-xs">
                <span class="text-slate-500 block text-[10px]">Emissão do Documento:</span>
                <span class="font-bold text-slate-900 block">{{ date('d/m/Y \à\s H:i') }}</span>
                <span class="text-slate-500 block text-[10px] mt-0.5">Emitido por: {{ auth()->user()->name ?? 'Secretaria' }}</span>
            </div>
        </div>

        <!-- Critérios de Filtro / Parâmetros do Relatório -->
        <div class="bg-slate-50 p-3 rounded-lg border border-slate-200 mb-6 text-xs flex flex-wrap items-center justify-between gap-2">
            <div>
                <span class="text-slate-500 font-semibold">Parâmetros Aplicados:</span>
                <span class="text-slate-800 font-medium">@yield('filter_summary', 'Listagem Geral')</span>
            </div>
            <div>
                <span class="text-slate-500 font-semibold">Total de Registros:</span>
                <span class="font-bold text-slate-900">@yield('total_count', '0')</span>
            </div>
        </div>

        <!-- Conteúdo Principal do Relatório -->
        <div class="mb-8">
            @yield('content')
        </div>

        <!-- Rodapé Institucional com Assinatura -->
        <div class="mt-12 pt-6 border-t border-slate-300">
            <div class="grid grid-cols-2 gap-10 text-center text-xs">
                <div>
                    <div class="border-b border-slate-800 mb-1.5 h-8"></div>
                    <span class="font-bold text-slate-900 block">{{ auth()->user()->name ?? 'Secretário Responsável' }}</span>
                    <span class="text-[10px] text-slate-500">Secretaria / Responsável pela Emissão</span>
                </div>
                <div>
                    <div class="border-b border-slate-800 mb-1.5 h-8"></div>
                    <span class="font-bold text-slate-900 block">Visto da Liderança / Pastor Regional</span>
                    <span class="text-[10px] text-slate-500">Coordenação Regional IEADM-DF</span>
                </div>
            </div>

            <div class="mt-8 text-center text-[9px] text-slate-400">
                {{ config('app.name', 'IGREJA EVANGÉLICA ASSEMBLEIA DE DEUS DA MISSÃO') }} • Relatório oficial gerado eletronicamente em {{ date('d/m/Y \à\s H:i:s') }}.
            </div>
        </div>

    </div>

</body>
</html>
