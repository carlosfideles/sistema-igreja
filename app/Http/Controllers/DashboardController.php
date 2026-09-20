<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Church;
use App\Models\Member;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Exibe o painel principal com indicadores filtrados automaticamente por escopo.
     */
    public function index(Request $request): View
    {
        $currentUser = Auth::user();

        // Escopo de congregações autorizadas
        $accessibleChurchesQuery = $currentUser->accessibleChurchesQuery();
        $accessibleChurchIds = (clone $accessibleChurchesQuery)->pluck('churches.id');

        // 1. CARDS DE TOTAIS
        $totalChurches = (clone $accessibleChurchesQuery)->count();
        $activeChurches = (clone $accessibleChurchesQuery)->where('status', 'active')->count();

        $baseMembersQuery = Member::whereIn('church_id', $accessibleChurchIds);
        $totalMembers = (clone $baseMembersQuery)->count();
        $activeMembers = (clone $baseMembersQuery)->where('status', 'ativo')->count();
        $inactiveMembers = (clone $baseMembersQuery)->where('status', 'inativo')->count();
        $transferredMembers = (clone $baseMembersQuery)->where('status', 'transferido')->count();
        $newMembersLast30Days = (clone $baseMembersQuery)
            ->where('created_at', '>=', now()->subDays(30))
            ->count();

        // Contagem de secretários
        if ($currentUser->isRegional()) {
            $totalSecretaries = User::where('status', 'active')->count();
        } else {
            $totalSecretaries = User::where('status', 'active')
                ->where(function ($q) use ($accessibleChurchIds, $currentUser) {
                    $q->where('id', $currentUser->id)
                      ->orWhereHas('churches', function ($c) use ($accessibleChurchIds) {
                          $c->whereIn('churches.id', $accessibleChurchIds);
                      });
                })->count();
        }

        // 2. GRÁFICO 1: MEMBROS POR IGREJA (TOP CONGREGAÇÕES)
        $churchesWithMembers = (clone $accessibleChurchesQuery)
            ->withCount('members')
            ->orderBy('members_count', 'desc')
            ->take(8)
            ->get();

        $chartMembersByChurchLabels = $churchesWithMembers->pluck('name')->toArray();
        $chartMembersByChurchData = $churchesWithMembers->pluck('members_count')->toArray();

        // 3. GRÁFICO 2: SITUAÇÃO DOS MEMBROS
        $statusCounts = (clone $baseMembersQuery)
            ->selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status')
            ->toArray();

        $chartStatusLabels = ['Ativos', 'Inativos', 'Transferidos', 'Em Disciplina', 'Falecidos'];
        $chartStatusData = [
            $statusCounts['ativo'] ?? 0,
            $statusCounts['inativo'] ?? 0,
            $statusCounts['transferido'] ?? 0,
            $statusCounts['disciplina'] ?? 0,
            $statusCounts['falecido'] ?? 0,
        ];

        // 4. GRÁFICO 3: CRESCIMENTO / HISTÓRICO DE ENTRADAS NOS ÚLTIMOS 6 MESES
        $chartMonthlyLabels = [];
        $chartMonthlyData = [];

        for ($i = 5; $i >= 0; $i--) {
            $monthDate = Carbon::now()->subMonths($i);
            $monthName = $monthDate->translatedFormat('M/y');
            $chartMonthlyLabels[] = ucfirst($monthName);

            $countInMonth = (clone $baseMembersQuery)
                ->whereYear('created_at', $monthDate->year)
                ->whereMonth('created_at', $monthDate->month)
                ->count();

            $chartMonthlyData[] = $countInMonth;
        }

        // 5. GRÁFICO 4: DISTRIBUIÇÃO POR FAIXA ETÁRIA
        $today = Carbon::today();
        $age0to12 = (clone $baseMembersQuery)->whereBetween('birth_date', [$today->copy()->subYears(12), $today])->count();
        $age13to17 = (clone $baseMembersQuery)->whereBetween('birth_date', [$today->copy()->subYears(17), $today->copy()->subYears(13)])->count();
        $age18to29 = (clone $baseMembersQuery)->whereBetween('birth_date', [$today->copy()->subYears(29), $today->copy()->subYears(18)])->count();
        $age30to59 = (clone $baseMembersQuery)->whereBetween('birth_date', [$today->copy()->subYears(59), $today->copy()->subYears(30)])->count();
        $age60plus = (clone $baseMembersQuery)->where('birth_date', '<=', $today->copy()->subYears(60))->count();

        $chartAgeLabels = ['Crianças (0-12)', 'Adolescentes (13-17)', 'Jovens (18-29)', 'Adultos (30-59)', 'Melhor Idade (60+)'];
        $chartAgeData = [$age0to12, $age13to17, $age18to29, $age30to59, $age60plus];

        // 6. TABELA DE CONGREGAÇÕES COM CONTAGEM EM TEMPO REAL
        $dashboardChurches = (clone $accessibleChurchesQuery)
            ->withCount(['members', 'activeMembers', 'users'])
            ->orderBy('name', 'asc')
            ->take(10)
            ->get();

        // 7. ATIVIDADES RECENTES (AUDITORIA) - Exclusivo para usuários Regionais
        if ($currentUser->isRegional()) {
            $recentActivities = AuditLog::with(['user', 'church'])
                ->orderBy('created_at', 'desc')
                ->take(6)
                ->get();
        } else {
            $recentActivities = collect();
        }

        return view('dashboard', compact(
            'currentUser',
            'totalChurches',
            'activeChurches',
            'totalMembers',
            'activeMembers',
            'inactiveMembers',
            'transferredMembers',
            'newMembersLast30Days',
            'totalSecretaries',
            'chartMembersByChurchLabels',
            'chartMembersByChurchData',
            'chartStatusLabels',
            'chartStatusData',
            'chartMonthlyLabels',
            'chartMonthlyData',
            'chartAgeLabels',
            'chartAgeData',
            'dashboardChurches',
            'recentActivities'
        ));
    }
}
