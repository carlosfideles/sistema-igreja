<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\AuditController;
use App\Http\Controllers\ChurchController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\FinanceController;
use App\Http\Controllers\MemberController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\SecretaryController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\TransferController;
use Illuminate\Support\Facades\Route;

// Redirecionamento da raiz
Route::get('/', function () {
    return redirect()->route('login');
});

// Rotas de Autenticação (Visitantes)
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('/login', [AuthenticatedSessionController::class, 'store']);
});

// Rotas Autenticadas e Protegidas por Usuário Ativo
Route::middleware(['auth', 'active.user'])->group(function () {
    Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');

    // Dashboard Interativo (Fase 7)
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Módulo de Secretários (Fase 3)
    Route::patch('/secretaries/{secretary}/status', [SecretaryController::class, 'toggleStatus'])->name('secretaries.status');
    Route::resource('secretaries', SecretaryController::class);

    // Módulo de Igrejas / Congregações (Fase 4)
    Route::patch('/churches/{church}/status', [ChurchController::class, 'toggleStatus'])->name('churches.status');
    Route::resource('churches', ChurchController::class);

    // Módulo de Membros (Fase 5)
    Route::get('/members/{member}/print', [MemberController::class, 'print'])->name('members.print');
    Route::patch('/members/{member}/status', [MemberController::class, 'toggleStatus'])->name('members.status');
    Route::resource('members', MemberController::class);

    // Módulo de Transferências (Fase 6)
    Route::get('/transfers', [TransferController::class, 'index'])->name('transfers.index');
    Route::get('/transfers/create', [TransferController::class, 'create'])->name('transfers.create');
    Route::post('/transfers', [TransferController::class, 'store'])->name('transfers.store');
    Route::get('/transfers/{transfer}', [TransferController::class, 'show'])->name('transfers.show');
    Route::post('/transfers/{transfer}/approve', [TransferController::class, 'approve'])->name('transfers.approve');
    Route::post('/transfers/{transfer}/reject', [TransferController::class, 'reject'])->name('transfers.reject');
    Route::post('/transfers/{transfer}/mark-notified', [TransferController::class, 'markNotified'])->name('transfers.markNotified');
    Route::get('/members/{member}/transfer', [TransferController::class, 'create'])->name('members.transfer.create');
    Route::post('/members/{member}/transfer', [TransferController::class, 'store'])->name('members.transfer.store');

    // Módulo Financeiro
    Route::get('/finance', [FinanceController::class, 'index'])->name('finance.index');
    Route::get('/finance/create', [FinanceController::class, 'create'])->name('finance.create');
    Route::post('/finance', [FinanceController::class, 'store'])->name('finance.store');
    Route::get('/finance/print', [FinanceController::class, 'printReport'])->name('finance.print');
    Route::get('/finance/remittances', [FinanceController::class, 'remittances'])->name('finance.remittances');
    Route::post('/finance/remittances/{remittance}/confirm', [FinanceController::class, 'confirmRemittance'])->name('finance.remittances.confirm');
    Route::post('/finance/exemptions/{church}/grant', [FinanceController::class, 'grantExemption'])->name('finance.exemptions.grant');
    Route::delete('/finance/exemptions/{exemption}/revoke', [FinanceController::class, 'revokeExemption'])->name('finance.exemptions.revoke');
    Route::get('/finance/members-by-church/{church}', [FinanceController::class, 'membersByChurch'])->name('finance.members-by-church');
    Route::get('/finance/{transaction}/edit', [FinanceController::class, 'edit'])->name('finance.edit');
    Route::put('/finance/{transaction}', [FinanceController::class, 'update'])->name('finance.update');
    Route::delete('/finance/{transaction}', [FinanceController::class, 'destroy'])->name('finance.destroy');

    // Módulo de Relatórios (Fase 8)
    Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
    Route::get('/reports/members', [ReportController::class, 'members'])->name('reports.members');
    Route::get('/reports/birthdays', [ReportController::class, 'birthdays'])->name('reports.birthdays');
    Route::get('/reports/churches', [ReportController::class, 'churches'])->name('reports.churches');
    Route::get('/reports/transfers', [ReportController::class, 'transfers'])->name('reports.transfers');
    Route::get('/reports/secretaries', [ReportController::class, 'secretaries'])->name('reports.secretaries');

    // Módulo de Auditoria (Fase 9)
    Route::get('/audit', [AuditController::class, 'index'])->name('audit.index');
    Route::get('/audit/{log}', [AuditController::class, 'show'])->name('audit.show');

    // Módulo de Configurações (Restrito à Regional)
    Route::prefix('settings')->name('settings.')->group(function () {
        Route::get('/', [SettingsController::class, 'index'])->name('index');
        Route::post('/functions', [SettingsController::class, 'storeFunction'])->name('functions.store');
        Route::put('/functions/{function}', [SettingsController::class, 'updateFunction'])->name('functions.update');
        Route::patch('/functions/{function}/toggle', [SettingsController::class, 'toggleFunctionStatus'])->name('functions.toggle');
    });
});
