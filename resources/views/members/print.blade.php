<!DOCTYPE html>
<html lang="pt-BR" class="h-full bg-slate-100 text-slate-900">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ficha de Membro — {{ $member->full_name }} — {{ config('app.name', 'IGREJA EVANGÉLICA ASSEMBLEIA DE DEUS DA MISSÃO') }}</title>

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
                font-size: 11pt;
            }
            .no-print {
                display: none !important;
            }
            .print-card {
                border: 1px solid #cbd5e1 !important;
                box-shadow: none !important;
                page-break-inside: avoid;
            }
        }
    </style>
</head>
<body class="p-4 sm:p-8">

    <!-- Barra de Controle de Impressão (Oculta na Impressão) -->
    <div class="no-print max-w-4xl mx-auto mb-6 flex items-center justify-between bg-slate-900 text-white p-4 rounded-2xl shadow-xl">
        <div class="flex items-center gap-3">
            <span class="text-xs text-slate-300">Visualização de Impressão • Ficha Cadastral Oficial</span>
        </div>
        <div class="flex items-center gap-2">
            <button onclick="window.print()" class="px-4 py-2 bg-brand-600 hover:bg-brand-500 text-white text-xs font-bold rounded-xl shadow transition-colors flex items-center gap-1.5">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                </svg>
                <span>Imprimir Agora</span>
            </button>
            <button onclick="window.close()" class="px-3 py-2 bg-slate-800 hover:bg-slate-700 text-slate-300 text-xs font-semibold rounded-xl transition-colors">
                Fechar
            </button>
        </div>
    </div>

    <!-- Ficha de Impressão Oficial -->
    <div class="print-card max-w-4xl mx-auto bg-white p-8 sm:p-10 rounded-2xl border border-slate-300 shadow-sm text-slate-900">

        <!-- Cabeçalho Oficial com Logotipo (Regra 27.1) -->
        <div class="flex items-center justify-between border-b-2 border-slate-900 pb-5 mb-6">
            <div class="flex items-center gap-4">
                <img src="/assets/images/logo.png" alt="SISTEMA DE GESTÃO REGIONAL" class="h-16 w-auto object-contain" onerror="this.style.display='none'; document.getElementById('print-logo-fallback').classList.remove('hidden');">
                <div id="print-logo-fallback" class="hidden flex items-center justify-center w-14 h-14 rounded-xl bg-slate-900 text-white font-black text-lg tracking-wider">
                    IEADM
                </div>
                <div>
                    <h1 class="text-lg font-black tracking-tight uppercase">IGREJA EVANGÉLICA ASSEMBLEIA DE DEUS DA MISSÃO</h1>
                    <p class="text-xs font-bold text-slate-700 uppercase tracking-wider">
                        @if(auth()->check() && (auth()->user()->isRegional() || auth()->user()->isFirstSecretaryRegional()))
                            Regional - DF
                        @elseif(isset($member) && $member->church)
                            {{ $member->church->name }}
                        @elseif(auth()->check() && auth()->user()->churches->isNotEmpty())
                            {{ auth()->user()->churches->pluck('name')->implode(', ') }}
                        @else
                            Regional - DF
                        @endif
                    </p>
                    <p class="text-[11px] text-slate-500 mt-0.5">FICHA CADASTRAL INDIVIDUAL DE MEMBRO</p>
                </div>
            </div>

            <!-- Foto 3x4 / Placeholder -->
            @if($member->photo || ($member->photo_path ?? null))
                <div class="w-24 h-32 rounded-lg overflow-hidden shrink-0 shadow-sm border border-slate-300">
                    <img src="{{ $member->photo ?? $member->photo_path }}" alt="{{ $member->full_name }}" class="w-full h-full object-cover">
                </div>
            @else
                <div class="w-24 h-32 border-2 border-dashed border-slate-400 rounded-lg overflow-hidden flex items-center justify-center bg-slate-50 text-center shrink-0">
                    <span class="text-[10px] text-slate-400 font-semibold uppercase px-1">Foto 3x4</span>
                </div>
            @endif
        </div>

        <!-- Bloco 1: Vínculo e Situação Eclesiástica -->
        <div class="mb-6">
            <div class="bg-slate-100 px-3 py-1.5 rounded font-bold text-xs uppercase tracking-wider text-slate-800 border border-slate-300 mb-3">
                1. Congregação de Vínculo e Situação Eclesiástica
            </div>
            <div class="grid grid-cols-3 gap-3 text-xs">
                <div>
                    <span class="text-slate-500 font-medium block text-[10px]">Congregação / Igreja:</span>
                    <strong class="text-slate-900 block">{{ $member->church->name }}</strong>
                </div>
                <div>
                    <span class="text-slate-500 font-medium block text-[10px]">Cidade / UF:</span>
                    <span class="text-slate-800 font-semibold">{{ $member->church->city }} / {{ $member->church->state }}</span>
                </div>
                <div>
                    <span class="text-slate-500 font-medium block text-[10px]">Situação Eclesiástica:</span>
                    <span class="font-bold uppercase {{ $member->status === 'ativo' ? 'text-emerald-700' : 'text-slate-800' }}">{{ $member->status }}</span>
                </div>
                <div>
                    <span class="text-slate-500 font-medium block text-[10px]">Cargo Eclesiástico:</span>
                    <span class="text-slate-900 font-bold">{{ $member->ecclesiastical_position ?? 'Membro' }}</span>
                </div>
                <div>
                    <span class="text-slate-500 font-medium block text-[10px]">Funções Exercidas:</span>
                    <span class="text-slate-900 font-bold">
                        @if($member->functions->isNotEmpty())
                            {{ $member->functions->pluck('name')->implode(', ') }}
                        @else
                            Nenhuma
                        @endif
                    </span>
                </div>
                <div>
                    <span class="text-slate-500 font-medium block text-[10px]">Data de Entrada / Adesão:</span>
                    <span class="text-slate-800 font-semibold">{{ $member->entry_date ? $member->entry_date->format('d/m/Y') : 'Não informada' }}</span>
                </div>
                <div>
                    <span class="text-slate-500 font-medium block text-[10px]">Pastor / Dirigente da Congregação:</span>
                    <span class="text-slate-800 font-semibold">{{ $member->church->responsible_name ?? 'Não informado' }}</span>
                </div>
            </div>
        </div>

        <!-- Bloco 2: Dados Pessoais e Civis -->
        <div class="mb-6">
            <div class="bg-slate-100 px-3 py-1.5 rounded font-bold text-xs uppercase tracking-wider text-slate-800 border border-slate-300 mb-3">
                2. Identificação Pessoal e Documentos Civis
            </div>
            <div class="grid grid-cols-3 gap-3 text-xs">
                <div class="col-span-2">
                    <span class="text-slate-500 font-medium block text-[10px]">Nome Civil Completo:</span>
                    <strong class="text-slate-900 text-sm block">{{ $member->full_name }}</strong>
                </div>
                <div>
                    <span class="text-slate-500 font-medium block text-[10px]">Nome Social / Conhecido por:</span>
                    <span class="text-slate-800">{{ $member->social_name ?? '—' }}</span>
                </div>
                <div>
                    <span class="text-slate-500 font-medium block text-[10px]">CPF:</span>
                    <span class="text-slate-800 font-semibold">
                        @if($hideDocuments ?? false)
                            {{ $member->cpf ? '***.***.***-**' : '—' }}
                        @else
                            {{ $member->cpf ?? '—' }}
                        @endif
                    </span>
                </div>
                <div>
                    <span class="text-slate-500 font-medium block text-[10px]">RG / Órgão Expedidor:</span>
                    <span class="text-slate-800 font-semibold">
                        @if($hideDocuments ?? false)
                            {{ $member->rg ? '**.***.***-*' : '—' }}
                        @else
                            {{ $member->rg ?? '—' }}
                        @endif
                    </span>
                </div>
                <div>
                    <span class="text-slate-500 font-medium block text-[10px]">Data de Nascimento / Idade:</span>
                    <span class="text-slate-800 font-semibold">
                        {{ $member->birth_date ? $member->birth_date->format('d/m/Y') . ' (' . \Carbon\Carbon::parse($member->birth_date)->age . ' anos)' : '—' }}
                    </span>
                </div>
                <div>
                    <span class="text-slate-500 font-medium block text-[10px]">Sexo:</span>
                    <span class="text-slate-800">{{ $member->gender === 'M' ? 'Masculino' : ($member->gender === 'F' ? 'Feminino' : 'Outro') }}</span>
                </div>
                <div>
                    <span class="text-slate-500 font-medium block text-[10px]">Estado Civil:</span>
                    <span class="text-slate-800">{{ ucfirst($member->marital_status ?? 'Não informado') }}</span>
                </div>
                <div>
                    <span class="text-slate-500 font-medium block text-[10px]">Data do Cadastro no Sistema:</span>
                    <span class="text-slate-800">{{ $member->created_at->format('d/m/Y') }}</span>
                </div>
            </div>
        </div>

        <!-- Bloco 3: Contato e Endereço Residencial -->
        <div class="mb-6">
            <div class="bg-slate-100 px-3 py-1.5 rounded font-bold text-xs uppercase tracking-wider text-slate-800 border border-slate-300 mb-3">
                3. Contato e Endereço Residencial
            </div>
            <div class="grid grid-cols-3 gap-3 text-xs">
                <div>
                    <span class="text-slate-500 font-medium block text-[10px]">Telefone / Celular:</span>
                    <span class="text-slate-800 font-semibold">{{ $member->whatsapp ?? $member->phone ?? '—' }}</span>
                </div>
                <div class="col-span-2">
                    <span class="text-slate-500 font-medium block text-[10px]">E-mail:</span>
                    <span class="text-slate-800">{{ $member->email ?? '—' }}</span>
                </div>
                <div class="col-span-2">
                    <span class="text-slate-500 font-medium block text-[10px]">Logradouro / Endereço:</span>
                    <span class="text-slate-800">{{ $member->address ?? '—' }} @if($member->number), Nº {{ $member->number }}@endif @if($member->complement), {{ $member->complement }}@endif</span>
                </div>
                <div>
                    <span class="text-slate-500 font-medium block text-[10px]">Bairro / Setor:</span>
                    <span class="text-slate-800">{{ $member->neighborhood ?? '—' }}</span>
                </div>
                <div>
                    <span class="text-slate-500 font-medium block text-[10px]">Cidade / RA:</span>
                    <span class="text-slate-800">{{ $member->city ?? 'Brasília' }}</span>
                </div>
                <div>
                    <span class="text-slate-500 font-medium block text-[10px]">UF:</span>
                    <span class="text-slate-800">{{ $member->state ?? 'DF' }}</span>
                </div>
                <div>
                    <span class="text-slate-500 font-medium block text-[10px]">CEP:</span>
                    <span class="text-slate-800">{{ $member->zip_code ?? '—' }}</span>
                </div>
            </div>
        </div>

        @if($includeEcclesiastical ?? true)
        <!-- Bloco 4: Histórico Eclesiástico e Linha do Tempo -->
        <div class="mb-6">
            <div class="bg-slate-100 px-3 py-1.5 rounded font-bold text-xs uppercase tracking-wider text-slate-800 border border-slate-300 mb-3 flex items-center justify-between">
                <span>Histórico Eclesiástico e Linha do Tempo</span>
                <span class="text-[10px] font-normal text-slate-600">{{ $member->ecclesiasticalHistories->count() }} registro(s)</span>
            </div>

            @if($member->ecclesiasticalHistories->isNotEmpty())
                <div class="border border-slate-300 rounded overflow-hidden">
                    <table class="w-full text-left text-xs border-collapse">
                        <thead>
                            <tr class="bg-slate-50 border-b border-slate-300 text-[10px] uppercase font-bold text-slate-700">
                                <th class="py-2 px-3 w-32">Data / Hora</th>
                                <th class="py-2 px-3">Descrição do Evento / Anotação</th>
                                <th class="py-2 px-3 w-48">Responsável pelo Registro</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200 text-xs">
                            @foreach($member->ecclesiasticalHistories as $history)
                                <tr class="hover:bg-slate-50/50">
                                    <td class="py-2 px-3 font-semibold text-slate-800 whitespace-nowrap align-top text-[11px]">
                                        {{ $history->recorded_at ? $history->recorded_at->format('d/m/Y H:i') : $history->created_at->format('d/m/Y H:i') }}
                                    </td>
                                    <td class="py-2 px-3 text-slate-800 align-top leading-relaxed whitespace-pre-line">
                                        {{ $history->description }}
                                    </td>
                                    <td class="py-2 px-3 text-slate-600 align-top text-[11px]">
                                        @if($history->creator)
                                            <strong class="text-slate-800 block">{{ $history->creator->name }}</strong>
                                            <span class="text-[10px] text-slate-500">{{ $history->creator->role?->name ?? 'Secretaria' }}</span>
                                        @else
                                            <span class="text-slate-400">Sistema / Não informado</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <p class="text-xs text-slate-500 p-2.5 bg-slate-50 rounded border border-slate-200 italic">
                    Nenhum registro lançado no histórico eclesiástico até o momento.
                </p>
            @endif
        </div>
        @endif

        @if($includeTransfers ?? true)
        <!-- Bloco 5: Histórico de Transferências -->
        <div class="mb-6">
            <div class="bg-slate-100 px-3 py-1.5 rounded font-bold text-xs uppercase tracking-wider text-slate-800 border border-slate-300 mb-3 flex items-center justify-between">
                <span>Histórico de Transferências</span>
                <span class="text-[10px] font-normal text-slate-600">{{ $member->transfers->count() }} registro(s)</span>
            </div>

            @if($member->transfers->isNotEmpty())
                <div class="border border-slate-300 rounded overflow-hidden">
                    <table class="w-full text-left text-xs border-collapse">
                        <thead>
                            <tr class="bg-slate-50 border-b border-slate-300 text-[10px] uppercase font-bold text-slate-700">
                                <th class="py-2 px-3 w-28">Data / Hora</th>
                                <th class="py-2 px-3">Origem &rarr; Destino</th>
                                <th class="py-2 px-3">Motivo / Justificativa</th>
                                <th class="py-2 px-3 w-40">Responsáveis</th>
                                <th class="py-2 px-3 w-24 text-center">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200 text-xs">
                            @foreach($member->transfers as $transfer)
                                <tr class="hover:bg-slate-50/50">
                                    <td class="py-2 px-3 font-semibold text-slate-800 whitespace-nowrap align-top text-[11px]">
                                        {{ $transfer->transferred_at ? $transfer->transferred_at->format('d/m/Y H:i') : $transfer->created_at->format('d/m/Y H:i') }}
                                    </td>
                                    <td class="py-2 px-3 text-slate-800 align-top leading-relaxed">
                                        @if($transfer->type === 'inactivation')
                                            <span class="font-bold text-red-700">Saída / Inativação</span> de <strong>{{ $transfer->fromChurch->name }}</strong>
                                        @else
                                            <div><strong>De:</strong> {{ $transfer->fromChurch->name }}</div>
                                            <div><strong>Para:</strong> {{ $transfer->toChurch?->name ?? 'Outra Congregação' }}</div>
                                        @endif
                                    </td>
                                    <td class="py-2 px-3 text-slate-700 align-top leading-relaxed text-[11px]">
                                        {{ $transfer->reason ?: ($transfer->notes ?: '—') }}
                                    </td>
                                    <td class="py-2 px-3 text-slate-600 align-top text-[11px]">
                                        <div><strong class="text-slate-800">Realizado:</strong> {{ $transfer->transferredBy->name ?? 'Secretaria' }}</div>
                                        @if($transfer->approver)
                                            <div class="mt-0.5"><strong class="text-slate-800">Autorizado:</strong> {{ $transfer->approver->name }}</div>
                                        @endif
                                    </td>
                                    <td class="py-2 px-3 text-center align-top whitespace-nowrap">
                                        <span class="inline-block px-2 py-0.5 rounded text-[10px] font-bold uppercase {{ $transfer->status === 'approved' ? 'bg-emerald-100 text-emerald-800 border border-emerald-300' : ($transfer->status === 'pending' ? 'bg-amber-100 text-amber-800 border border-amber-300' : 'bg-slate-100 text-slate-700 border border-slate-300') }}">
                                            {{ $transfer->status === 'approved' ? 'Concluída' : ($transfer->status === 'pending' ? 'Pendente' : ucfirst($transfer->status)) }}
                                        </span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <p class="text-xs text-slate-500 p-2.5 bg-slate-50 rounded border border-slate-200 italic">
                    Este membro não possui transferências registradas no histórico.
                </p>
            @endif
        </div>
        @endif

        @if($includeTithes ?? false)
        <!-- Bloco: Histórico de Dízimos e Contribuições -->
        <div class="mb-6">
            <div class="bg-slate-100 px-3 py-1.5 rounded font-bold text-xs uppercase tracking-wider text-slate-800 border border-slate-300 mb-3 flex items-center justify-between">
                <span>Histórico de Dízimos e Contribuições</span>
                @if($startDate || $endDate)
                    <div class="text-[10px] font-semibold text-slate-700">
                        <span>Período: <strong>{{ $startDate ? \Carbon\Carbon::parse($startDate)->format('d/m/Y') : 'Início' }}</strong> até <strong>{{ $endDate ? \Carbon\Carbon::parse($endDate)->format('d/m/Y') : 'Hoje' }}</strong></span>
                    </div>
                @endif
            </div>

            @if(isset($tithes) && $tithes->isNotEmpty())
                <div class="border border-slate-300 rounded overflow-hidden">
                    <table class="w-full text-left text-xs border-collapse">
                        <thead>
                            <tr class="bg-slate-50 border-b border-slate-300 text-[10px] uppercase font-bold text-slate-700">
                                <th class="py-2 px-3 w-32">Competência</th>
                                <th class="py-2 px-3">Congregação / Descrição</th>
                                <th class="py-2 px-3 w-36">Tipo / Categoria</th>
                                <th class="py-2 px-3 w-32 text-right">Valor (R$)</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200 text-xs">
                            @foreach($tithes as $tithe)
                                <tr class="hover:bg-slate-50/50">
                                    <td class="py-2 px-3 font-semibold text-slate-800 whitespace-nowrap align-top text-[11px]">
                                        {{ $tithe->competence_date ? $tithe->competence_date->translatedFormat('F / Y') : '—' }}
                                        <span class="text-[10px] text-slate-500 block font-normal">{{ $tithe->competence_date ? $tithe->competence_date->format('d/m/Y') : '' }}</span>
                                    </td>
                                    <td class="py-2 px-3 text-slate-800 align-top leading-relaxed">
                                        <div class="font-semibold text-slate-900">{{ $tithe->church->name ?? 'Congregação' }}</div>
                                        @if($tithe->description)
                                            <div class="text-[11px] text-slate-600 mt-0.5">{{ $tithe->description }}</div>
                                        @endif
                                    </td>
                                    <td class="py-2 px-3 text-slate-700 align-top text-[11px]">
                                        <span class="inline-block px-1.5 py-0.5 rounded text-[10px] font-semibold bg-slate-100 border border-slate-300 uppercase">
                                            {{ ucfirst($tithe->category ?? 'Dízimo') }}
                                        </span>
                                    </td>
                                    <td class="py-2 px-3 text-right font-bold text-slate-900 align-top whitespace-nowrap text-[12px]">
                                        @if($hideValues ?? false)
                                            R$ ***,**
                                        @else
                                            R$ {{ number_format($tithe->amount, 2, ',', '.') }}
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <p class="text-xs text-slate-500 p-2.5 bg-slate-50 rounded border border-slate-200 italic">
                    Nenhum lançamento de dízimo encontrado para este membro no período selecionado.
                </p>
            @endif
        </div>
        @endif

        <!-- Observações Adicionais (se houver) -->
        @if($member->notes)
        <div class="mb-6">
            <div class="bg-slate-100 px-3 py-1.5 rounded font-bold text-xs uppercase tracking-wider text-slate-800 border border-slate-300 mb-2">
                Observações Adicionais
            </div>
            <p class="text-xs text-slate-700 p-2.5 bg-slate-50 rounded border border-slate-200 leading-relaxed">{{ $member->notes }}</p>
        </div>
        @endif

        <!-- Termo de Compromisso e Assinaturas -->
        <div class="mt-10 pt-6 border-t-2 border-slate-900">
            @if(($signatureType ?? 'regional') !== 'none')
                <p class="text-[10px] text-slate-500 text-center mb-8 leading-relaxed">
                    Declaro, para os devidos fins eclesiásticos, que os dados constantes nesta ficha correspondem à verdade e que estou ciente do regimento interno e doutrinas da IGREJA EVANGÉLICA ASSEMBLEIA DE DEUS DA MISSÃO.
                </p>

                @if(($signatureType ?? 'regional') === 'both')
                    <div class="grid grid-cols-3 gap-6 text-center text-xs">
                        <div>
                            <div class="border-b border-slate-800 mb-1.5 h-8"></div>
                            <span class="font-bold text-slate-900 block">{{ $member->full_name }}</span>
                            <span class="text-[10px] text-slate-500">Assinatura do Membro</span>
                        </div>
                        <div>
                            <div class="border-b border-slate-800 mb-1.5 h-8"></div>
                            <span class="font-bold text-slate-900 block">Secretaria Regional</span>
                            <span class="text-[10px] text-slate-500">Assinatura do Secretário Regional</span>
                        </div>
                        <div>
                            <div class="border-b border-slate-800 mb-1.5 h-8"></div>
                            <span class="font-bold text-slate-900 block">Secretaria Local</span>
                            <span class="text-[10px] text-slate-500">Assinatura do Secretário Local</span>
                        </div>
                    </div>
                @else
                    <div class="grid grid-cols-2 gap-10 text-center text-xs">
                        <div>
                            <div class="border-b border-slate-800 mb-1.5 h-8"></div>
                            <span class="font-bold text-slate-900 block">{{ $member->full_name }}</span>
                            <span class="text-[10px] text-slate-500">Assinatura do Membro</span>
                        </div>
                        <div>
                            <div class="border-b border-slate-800 mb-1.5 h-8"></div>
                            <span class="font-bold text-slate-900 block">
                                {{ ($signatureType ?? 'regional') === 'local' ? 'Secretaria Local' : 'Secretaria Regional' }}
                            </span>
                            <span class="text-[10px] text-slate-500">
                                {{ ($signatureType ?? 'regional') === 'local' ? 'Assinatura do Secretário Local' : 'Assinatura do Secretário Regional' }}
                            </span>
                        </div>
                    </div>
                @endif
            @endif

            <div class="{{ ($signatureType ?? 'regional') !== 'none' ? 'mt-8' : 'mt-2' }} text-center text-[9px] text-slate-400">
                Documento emitido eletronicamente pelo SISTEMA DE GESTÃO — {{ config('app.name', 'IGREJA EVANGÉLICA ASSEMBLEIA DE DEUS DA MISSÃO') }} em {{ date('d/m/Y \à\s H:i') }}.
            </div>
        </div>

    </div>

</body>
</html>
