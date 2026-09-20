<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Church;
use App\Models\Member;
use App\Models\MemberTransfer;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends Controller
{
    /**
     * Painel central com todos os relatórios disponíveis.
     */
    public function index(): View
    {
        $currentUser = Auth::user();
        $churchesCount = $currentUser->accessibleChurchesQuery()->count();
        $membersCount = Member::whereIn('church_id', $currentUser->accessibleChurchesQuery()->pluck('churches.id'))->count();

        return view('reports.index', compact('churchesCount', 'membersCount'));
    }

    /**
     * Relatório do Rol Geral de Membros.
     */
    public function members(Request $request): View|StreamedResponse
    {
        $currentUser = Auth::user();
        $accessibleChurchIds = $currentUser->accessibleChurchesQuery()->pluck('churches.id');

        $query = Member::with('church')
            ->whereIn('church_id', $accessibleChurchIds);

        // Filtro por congregação
        if ($churchId = $request->input('church_id')) {
            if ($currentUser->canAccessChurch((int) $churchId)) {
                $query->where('church_id', $churchId);
            }
        }

        // Filtro por situação
        if ($status = $request->input('status')) {
            if ($status !== 'all') {
                $query->where('status', $status);
            }
        } else {
            $query->where('status', 'ativo');
        }

        // Filtro por sexo
        if ($gender = $request->input('gender')) {
            $query->where('gender', $gender);
        }

        // Filtro por período de entrada
        if ($entryStart = $request->input('entry_start')) {
            $query->whereDate('entry_date', '>=', $entryStart);
        }
        if ($entryEnd = $request->input('entry_end')) {
            $query->whereDate('entry_date', '<=', $entryEnd);
        }

        // Filtro por faixa etária
        if ($ageGroup = $request->input('age_group')) {
            $today = Carbon::today();
            switch ($ageGroup) {
                case '0-12':
                    $query->whereBetween('birth_date', [$today->copy()->subYears(12), $today]);
                    break;
                case '13-17':
                    $query->whereBetween('birth_date', [$today->copy()->subYears(17), $today->copy()->subYears(13)]);
                    break;
                case '18-29':
                    $query->whereBetween('birth_date', [$today->copy()->subYears(29), $today->copy()->subYears(18)]);
                    break;
                case '30-59':
                    $query->whereBetween('birth_date', [$today->copy()->subYears(59), $today->copy()->subYears(30)]);
                    break;
                case '60+':
                    $query->where('birth_date', '<=', $today->copy()->subYears(60));
                    break;
            }
        }

        $members = $query->orderBy('full_name', 'asc')->get();

        // Registro de auditoria para geração de relatório
        AuditLog::record(
            action: 'report_generated',
            module: 'reports',
            description: "Gerou o Relatório de Membros com " . count($members) . " registros filtrados."
        );

        // Exportação para Planilha CSV
        if ($request->input('export') === 'csv') {
            return $this->exportMembersCsv($members);
        }

        $churches = $currentUser->accessibleChurchesQuery()->orderBy('name', 'asc')->get();

        return view('reports.members', compact('members', 'churches'));
    }

    /**
     * Relatório de Aniversariantes do Mês ou Período.
     */
    public function birthdays(Request $request): View|StreamedResponse
    {
        $currentUser = Auth::user();
        $accessibleChurchIds = $currentUser->accessibleChurchesQuery()->pluck('churches.id');

        $month = (int) $request->input('month', date('n'));
        if ($month < 1 || $month > 12) {
            $month = (int) date('n');
        }

        $query = Member::with('church')
            ->whereIn('church_id', $accessibleChurchIds)
            ->whereNotNull('birth_date')
            ->whereMonth('birth_date', $month);

        if ($churchId = $request->input('church_id')) {
            if ($currentUser->canAccessChurch((int) $churchId)) {
                $query->where('church_id', $churchId);
            }
        }

        if ($status = $request->input('status')) {
            if ($status !== 'all') {
                $query->where('status', $status);
            }
        } else {
            $query->where('status', 'ativo');
        }

        $members = $query->orderByRaw('DAY(birth_date) asc')->get();

        AuditLog::record(
            action: 'report_generated',
            module: 'reports',
            description: "Gerou o Relatório de Aniversariantes para o mês {$month} com " . count($members) . " aniversariantes."
        );

        if ($request->input('export') === 'csv') {
            return $this->exportBirthdaysCsv($members, $month);
        }

        $churches = $currentUser->accessibleChurchesQuery()->orderBy('name', 'asc')->get();

        return view('reports.birthdays', compact('members', 'churches', 'month'));
    }

    /**
     * Relatório de Congregações da Regional.
     */
    public function churches(Request $request): View|StreamedResponse
    {
        $currentUser = Auth::user();
        $query = $currentUser->accessibleChurchesQuery()
            ->withCount(['members', 'activeMembers', 'users']);

        if ($status = $request->input('status')) {
            if ($status !== 'all') {
                $query->where('status', $status);
            }
        }

        if ($city = $request->input('city')) {
            $query->where('city', 'like', "%{$city}%");
        }

        $churches = $query->orderBy('name', 'asc')->get();

        AuditLog::record(
            action: 'report_generated',
            module: 'reports',
            description: "Gerou o Relatório de Congregações com " . count($churches) . " igrejas listadas."
        );

        if ($request->input('export') === 'csv') {
            return $this->exportChurchesCsv($churches);
        }

        return view('reports.churches', compact('churches'));
    }

    /**
     * Relatório de Transferências de Membros.
     */
    public function transfers(Request $request): View|StreamedResponse
    {
        $currentUser = Auth::user();
        $query = MemberTransfer::with(['member', 'fromChurch', 'toChurch', 'transferredBy']);

        if ($currentUser->isLocal()) {
            $accessibleChurchIds = $currentUser->churches()->pluck('churches.id');
            $query->where(function ($q) use ($accessibleChurchIds) {
                $q->whereIn('from_church_id', $accessibleChurchIds)
                  ->orWhereIn('to_church_id', $accessibleChurchIds);
            });
        }

        if ($fromChurchId = $request->input('from_church_id')) {
            $query->where('from_church_id', $fromChurchId);
        }

        if ($toChurchId = $request->input('to_church_id')) {
            $query->where('to_church_id', $toChurchId);
        }

        if ($dateStart = $request->input('date_start')) {
            $query->whereDate('transferred_at', '>=', $dateStart);
        }

        if ($dateEnd = $request->input('date_end')) {
            $query->whereDate('transferred_at', '<=', $dateEnd);
        }

        $transfers = $query->orderBy('transferred_at', 'desc')->get();

        AuditLog::record(
            action: 'report_generated',
            module: 'reports',
            description: "Gerou o Relatório de Transferências com " . count($transfers) . " movimentações listadas."
        );

        if ($request->input('export') === 'csv') {
            return $this->exportTransfersCsv($transfers);
        }

        $churches = $currentUser->accessibleChurchesQuery()->orderBy('name', 'asc')->get();

        return view('reports.transfers', compact('transfers', 'churches'));
    }

    /**
     * Relatório de Secretários e Usuários do Sistema.
     */
    public function secretaries(Request $request): View|StreamedResponse
    {
        $this->authorize('viewAny', User::class);

        $currentUser = Auth::user();
        $query = User::with(['role', 'churches']);

        if ($currentUser->isLocal()) {
            $accessibleChurchIds = $currentUser->churches()->pluck('churches.id');
            $query->where(function ($q) use ($accessibleChurchIds, $currentUser) {
                $q->where('id', $currentUser->id)
                  ->orWhereHas('churches', function ($c) use ($accessibleChurchIds) {
                      $c->whereIn('churches.id', $accessibleChurchIds);
                  });
            });
        }

        if ($scope = $request->input('scope')) {
            $query->where('scope', $scope);
        }

        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        $secretaries = $query->orderBy('name', 'asc')->get();

        AuditLog::record(
            action: 'report_generated',
            module: 'reports',
            description: "Gerou o Relatório de Secretários com " . count($secretaries) . " usuários listados."
        );

        if ($request->input('export') === 'csv') {
            return $this->exportSecretariesCsv($secretaries);
        }

        return view('reports.secretaries', compact('secretaries'));
    }

    // ==========================================
    // EXPORTADORES CSV / EXCEL COM UTF-8 BOM
    // ==========================================

    private function exportMembersCsv($members): StreamedResponse
    {
        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="relatorio_membros_ieadm_df_' . date('Ymd_His') . '.csv"',
        ];

        return response()->stream(function () use ($members) {
            $handle = fopen('php://output', 'w');
            // Adiciona BOM UTF-8 para garantir abertura correta no Excel brasileiro
            fputs($handle, "\xEF\xBB\xBF");

            fputcsv($handle, [
                'ID', 'Nome Completo', 'Nome Social', 'Congregação', 'CPF', 'RG',
                'Data de Nascimento', 'Idade', 'Sexo', 'Estado Civil', 'Telefone',
                'WhatsApp', 'E-mail', 'Endereço', 'Cidade/UF', 'Data de Entrada', 'Situação'
            ], ';');

            foreach ($members as $m) {
                fputcsv($handle, [
                    $m->id,
                    $m->full_name,
                    $m->social_name ?? '',
                    $m->church->name ?? '',
                    $m->cpf ?? '',
                    $m->rg ?? '',
                    $m->birth_date ? $m->birth_date->format('d/m/Y') : '',
                    $m->birth_date ? Carbon::parse($m->birth_date)->age : '',
                    $m->gender ?? '',
                    $m->marital_status ?? '',
                    $m->phone ?? '',
                    $m->whatsapp ?? '',
                    $m->email ?? '',
                    $m->address ? "{$m->address}, {$m->number} - {$m->neighborhood}" : '',
                    "{$m->city}/{$m->state}",
                    $m->entry_date ? $m->entry_date->format('d/m/Y') : '',
                    strtoupper($m->status)
                ], ';');
            }

            fclose($handle);
        }, 200, $headers);
    }

    private function exportBirthdaysCsv($members, $month): StreamedResponse
    {
        $monthNames = [1 => 'Janeiro', 2 => 'Fevereiro', 3 => 'Março', 4 => 'Abril', 5 => 'Maio', 6 => 'Junho', 7 => 'Julho', 8 => 'Agosto', 9 => 'Setembro', 10 => 'Outubro', 11 => 'Novembro', 12 => 'Dezembro'];
        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="aniversariantes_' . strtolower($monthNames[$month]) . '_' . date('Ymd') . '.csv"',
        ];

        return response()->stream(function () use ($members) {
            $handle = fopen('php://output', 'w');
            fputs($handle, "\xEF\xBB\xBF");

            fputcsv($handle, ['Dia', 'Nome Completo', 'Congregação', 'Idade a Completar', 'WhatsApp/Telefone', 'E-mail', 'Situação'], ';');

            foreach ($members as $m) {
                fputcsv($handle, [
                    $m->birth_date->format('d'),
                    $m->full_name,
                    $m->church->name ?? '',
                    Carbon::parse($m->birth_date)->age,
                    $m->whatsapp ?? $m->phone ?? '',
                    $m->email ?? '',
                    strtoupper($m->status)
                ], ';');
            }

            fclose($handle);
        }, 200, $headers);
    }

    private function exportChurchesCsv($churches): StreamedResponse
    {
        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="relatorio_igrejas_ieadm_df_' . date('Ymd') . '.csv"',
        ];

        return response()->stream(function () use ($churches) {
            $handle = fopen('php://output', 'w');
            fputs($handle, "\xEF\xBB\xBF");

            fputcsv($handle, ['ID', 'Nome da Congregação', 'Código', 'Cidade', 'UF', 'Responsável/Dirigente', 'Telefone', 'E-mail', 'Membros Totais', 'Membros Ativos', 'Secretários', 'Status'], ';');

            foreach ($churches as $c) {
                fputcsv($handle, [
                    $c->id,
                    $c->name,
                    $c->code ?? '',
                    $c->city,
                    $c->state,
                    $c->responsible_name ?? '',
                    $c->phone ?? '',
                    $c->email ?? '',
                    $c->members_count,
                    $c->active_members_count,
                    $c->users_count,
                    $c->status === 'active' ? 'ATIVA' : 'INATIVA'
                ], ';');
            }

            fclose($handle);
        }, 200, $headers);
    }

    private function exportTransfersCsv($transfers): StreamedResponse
    {
        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="relatorio_transferencias_ieadm_df_' . date('Ymd') . '.csv"',
        ];

        return response()->stream(function () use ($transfers) {
            $handle = fopen('php://output', 'w');
            fputs($handle, "\xEF\xBB\xBF");

            fputcsv($handle, ['Protocolo', 'Membro', 'Congregação de Origem', 'Nova Congregação (Destino)', 'Secretário Executor', 'Data/Hora', 'Motivo', 'Observações'], ';');

            foreach ($transfers as $t) {
                fputcsv($handle, [
                    "TRF-" . str_pad($t->id, 6, '0', STR_PAD_LEFT),
                    $t->member->full_name ?? '',
                    $t->fromChurch->name ?? '',
                    $t->toChurch->name ?? '',
                    $t->transferredBy->name ?? 'Secretaria',
                    $t->transferred_at->format('d/m/Y H:i'),
                    $t->reason,
                    $t->notes ?? ''
                ], ';');
            }

            fclose($handle);
        }, 200, $headers);
    }

    private function exportSecretariesCsv($secretaries): StreamedResponse
    {
        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="relatorio_secretarios_ieadm_df_' . date('Ymd') . '.csv"',
        ];

        return response()->stream(function () use ($secretaries) {
            $handle = fopen('php://output', 'w');
            fputs($handle, "\xEF\xBB\xBF");

            fputcsv($handle, ['ID', 'Nome', 'E-mail', 'Função', 'Abrangência', 'Status', 'Igrejas Autorizadas', 'Último Login'], ';');

            foreach ($secretaries as $s) {
                $churchesList = $s->isRegional() ? 'TODAS (Regional)' : $s->churches->pluck('name')->implode(', ');
                fputcsv($handle, [
                    $s->id,
                    $s->name,
                    $s->email,
                    $s->role->name ?? '',
                    $s->scope,
                    $s->status === 'active' ? 'ATIVO' : 'INATIVO',
                    $churchesList,
                    $s->last_login_at ? $s->last_login_at->format('d/m/Y H:i') : 'Nunca'
                ], ';');
            }

            fclose($handle);
        }, 200, $headers);
    }
}
