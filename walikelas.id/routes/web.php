<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\EducatorDashboardController;
use App\Http\Controllers\PlanningCopilotController;
use App\Http\Controllers\ProgramController;
use Illuminate\Support\Facades\Route;

// Dashboard & Program Index
Route::get('/', [EducatorDashboardController::class, 'index'])->name('dashboard');

// Auth Switcher (Development & Testing)
Route::post('/auth/switch', [AuthController::class, 'switchUser'])->name('auth.switch');

// Program Workspace Routes
Route::post('/programs', [ProgramController::class, 'store'])->name('programs.store');
Route::get('/programs/{program}', [ProgramController::class, 'show'])->name('programs.show');

// Planning Copilot Routes
Route::post('/programs/{program}/plans/import', [PlanningCopilotController::class, 'importText'])->name('plans.import');
Route::post('/programs/{program}/plans/wizard', [PlanningCopilotController::class, 'wizard'])->name('plans.wizard');
Route::post('/programs/{program}/plans/{version}/approve', [PlanningCopilotController::class, 'approve'])->name('plans.approve');
Route::post('/programs/{program}/plans/{version}/revise', [PlanningCopilotController::class, 'revise'])->name('plans.revise');
