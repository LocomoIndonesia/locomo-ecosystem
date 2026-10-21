<?php

use App\Http\Controllers\AssignmentController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\EducatorDashboardController;
use App\Http\Controllers\EnrollmentController;
use App\Http\Controllers\GuardianPortalController;
use App\Http\Controllers\LearnerRunnerController;
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

// Enrollment & Guardianship Routes (Fase 2)
Route::post('/programs/{program}/learners', [EnrollmentController::class, 'storeLearner'])->name('programs.learners.store');
Route::post('/enrollments/{enrollment}/invite', [EnrollmentController::class, 'createInvite'])->name('enrollments.invite');
Route::post('/guardian-relations/{relation}/verify', [EnrollmentController::class, 'verifyGuardian'])->name('guardians.verify');
Route::post('/consents/{consent}/revoke', [EnrollmentController::class, 'revokeConsent'])->name('consents.revoke');

// Assignment Routes (Fase 2)
Route::post('/programs/{program}/assignments', [AssignmentController::class, 'store'])->name('programs.assignments.store');
Route::post('/assignments/{assignment}/launch-link', [AssignmentController::class, 'generateLink'])->name('assignments.launch_link');

// Learner Runner Routes (Server-Driven UI Web PWA, No Password) (Fase 2)
Route::get('/runner/{rawToken}', [LearnerRunnerController::class, 'show'])->name('runner.show');
Route::post('/runner/{rawToken}/step', [LearnerRunnerController::class, 'submitStep'])->name('runner.step');
Route::post('/runner/{rawToken}/finish', [LearnerRunnerController::class, 'finish'])->name('runner.finish');

// Guardian Checkpoint & Invite Portal (Fase 2)
Route::get('/guardian/invite/{rawToken}', [GuardianPortalController::class, 'showInvite'])->name('guardian.invite.show');
Route::post('/guardian/invite/{rawToken}', [GuardianPortalController::class, 'claimInvite'])->name('guardian.invite.claim');
Route::get('/guardian/checkpoint/{assignmentId}', [GuardianPortalController::class, 'showCheckpoint'])->name('guardian.checkpoint.show');
Route::post('/guardian/checkpoint/{assignmentId}', [GuardianPortalController::class, 'confirmCheckpoint'])->name('guardian.checkpoint.confirm');

// Triage Queue, AI Rubric Scoring & 3-Layer Reports (Fase 3)
use App\Http\Controllers\TriageController;
Route::get('/programs/{program}/triage', [TriageController::class, 'index'])->name('programs.triage.index');
Route::post('/programs/{program}/ai-suggestions/{suggestion}/decision', [TriageController::class, 'decideAiSuggestion'])->name('programs.ai_suggestions.decision');
Route::post('/programs/{program}/score-response', [TriageController::class, 'evaluateRubric'])->name('programs.score_response');
Route::post('/programs/{program}/validate-mastery', [TriageController::class, 'validateMastery'])->name('programs.validate_mastery');
Route::post('/programs/{program}/reports/generate', [TriageController::class, 'generateReport'])->name('programs.reports.generate');
Route::post('/programs/{program}/complete', [TriageController::class, 'completeProgram'])->name('programs.complete');

