<?php

use App\Modules\Vanguard\Controllers\VanguardOnboardingController;
use App\Modules\Vanguard\Controllers\VanguardDashboardController;
use App\Modules\Vanguard\Controllers\VanguardVotingController;
use App\Modules\Vanguard\Controllers\TicRevitalizationController;
use App\Modules\Vanguard\Controllers\AdminManagerController;
use Illuminate\Support\Facades\Route;

Route::middleware(['web', 'auth'])->prefix('vanguard')->group(function () {
    // Onboarding
    Route::get('/register', [VanguardOnboardingController::class, 'showRegistrationForm'])->name('vanguard.register');
    Route::post('/register', [VanguardOnboardingController::class, 'register'])->name('vanguard.register.submit');
    
    // Dashboard
    Route::get('/dashboard', [VanguardDashboardController::class, 'index'])->name('vanguard.dashboard');
    Route::post('/checkin', [VanguardDashboardController::class, 'checkin'])->name('vanguard.checkin');
    Route::post('/mission', [VanguardDashboardController::class, 'submitMission'])->name('vanguard.mission.submit');
    
    // E-Voting
    Route::get('/evoting', [VanguardVotingController::class, 'showVotingForm'])->name('vanguard.voting');
    Route::post('/evoting/vote', [VanguardDashboardController::class, 'vote'])->name('vanguard.voting.submit');
    
    // Daftar Tugas (Tasks) & Laporan Pengawasan (Supervision Reports)
    Route::get('/tugas', \App\Modules\Vanguard\Controllers\TaskController::class . '@index')->name('vanguard.tasks');
    Route::get('/laporan-pengawasan', \App\Modules\Vanguard\Controllers\SupervisionReportController::class . '@index')->name('vanguard.supervision');
    Route::post('/laporan-pengawasan', \App\Modules\Vanguard\Controllers\SupervisionReportController::class . '@storeLog')->name('vanguard.supervision.submit');

    // Admin Manager Community Orchestration
    Route::get('/admin/surveys', [AdminManagerController::class, 'indexSurveys'])->name('vanguard.admin.surveys');
    Route::post('/admin/surveys', [AdminManagerController::class, 'storeSurvey'])->name('vanguard.admin.surveys.store');
    Route::post('/admin/surveys/{id}/update', [AdminManagerController::class, 'updateSurvey'])->name('vanguard.admin.surveys.update');
    Route::post('/admin/inaugurate', [AdminManagerController::class, 'inaugurateVanguard'])->name('vanguard.admin.inaugurate');
    Route::post('/admin/verify-user/{id}', [AdminManagerController::class, 'verifyUser'])->name('vanguard.admin.verify');

    // Admin Manager: Kegiatan / Misi (Activities)
    Route::post('/admin/missions', [AdminManagerController::class, 'storeMission'])->name('vanguard.admin.missions.store');
    Route::post('/admin/missions/{id}/update', [AdminManagerController::class, 'updateMission'])->name('vanguard.admin.missions.update');
    Route::post('/admin/missions/{id}/delete', [AdminManagerController::class, 'deleteMission'])->name('vanguard.admin.missions.delete');

    // Admin Manager: Validasi Laporan Tugas
    Route::post('/admin/reports/{id}/approve', [AdminManagerController::class, 'approveReport'])->name('vanguard.admin.reports.approve');
    Route::post('/admin/reports/{id}/reject', [AdminManagerController::class, 'rejectReport'])->name('vanguard.admin.reports.reject');

    // Admin Manager: Manajemen Group Pengguna (Role/Access)
    Route::post('/admin/users/{id}/role', [AdminManagerController::class, 'updateUserRole'])->name('vanguard.admin.users.role');
});
