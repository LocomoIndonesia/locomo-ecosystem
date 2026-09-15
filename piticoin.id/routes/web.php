<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\QuestController;
use App\Http\Controllers\PaymentController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('dashboard');
});

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::post('/api/quests/stake', [QuestController::class, 'stake'])->name('quests.stake');
    Route::post('/api/quests/claim', [QuestController::class, 'claimReward'])->name('quests.claim');
    Route::get('/api/quests/progress/{escrowId}', [QuestController::class, 'checkProgress'])->name('quests.progress');
    Route::get('/api/agent-missions/active', [\App\Http\Controllers\Api\AgentMissionController::class, 'getActiveMission'])->name('agent-missions.active');
    Route::post('/api/agent-missions/take', [\App\Http\Controllers\Api\AgentMissionController::class, 'takeMission'])->name('agent-missions.take');
    Route::post('/api/agent-missions/submit-lead', [\App\Http\Controllers\Api\AgentMissionController::class, 'submitLead'])->name('agent-missions.submit-lead');
    Route::get('/api/game/harta-karun/state', [\App\Http\Controllers\GameController::class, 'getState'])->name('game.harta-karun.state');
    Route::post('/api/game/harta-karun/claim', [\App\Http\Controllers\GameController::class, 'claimGold'])->name('game.harta-karun.claim');
    Route::get('/api/game/koa/state', [\App\Http\Controllers\KoaGameController::class, 'getState'])->name('game.koa.state');
    Route::post('/api/game/koa/spin', [\App\Http\Controllers\KoaGameController::class, 'spin'])->name('game.koa.spin');
    Route::post('/api/game/koa/claim', [\App\Http\Controllers\KoaGameController::class, 'claimGold'])->name('game.koa.claim');

    // Onboarding Stages
    Route::get('/onboarding/awakening', [\App\Http\Controllers\OnboardingController::class, 'showAwakening'])->name('onboarding.awakening');
    Route::post('/onboarding/awakening', [\App\Http\Controllers\OnboardingController::class, 'saveAwakening']);
    Route::get('/onboarding/path', [\App\Http\Controllers\OnboardingController::class, 'showPath'])->name('onboarding.path');
    Route::post('/onboarding/path', [\App\Http\Controllers\OnboardingController::class, 'savePath']);
    Route::get('/onboarding/brotherhood', [\App\Http\Controllers\OnboardingController::class, 'showBrotherhood'])->name('onboarding.brotherhood');
    Route::post('/onboarding/brotherhood', [\App\Http\Controllers\OnboardingController::class, 'saveBrotherhood']);
    Route::get('/onboarding/pledge', [\App\Http\Controllers\OnboardingController::class, 'showPledge'])->name('onboarding.pledge');
    Route::post('/onboarding/pledge', [\App\Http\Controllers\OnboardingController::class, 'savePledge']);
    Route::get('/onboarding/pending', [\App\Http\Controllers\OnboardingController::class, 'showPending'])->name('onboarding.pending');

    // Auction Routes
    Route::get('/auction', [\App\Http\Controllers\AuctionController::class, 'index'])->name('auction.index');
    Route::post('/api/auction/buyout', [\App\Http\Controllers\AuctionController::class, 'buyout'])->name('auction.buyout');

    // Digital Goods Routes
    Route::get('/digital-goods', [\App\Http\Controllers\DigitalGoodsController::class, 'index'])->name('digital-goods.index');
    Route::post('/api/digital-goods/redeem', [\App\Http\Controllers\DigitalGoodsController::class, 'redeem'])->name('digital-goods.redeem');

    // Admin Root Level 0 Management Panel
    Route::get('/admin', [\App\Http\Controllers\AdminDashboardController::class, 'index'])->name('admin.dashboard');
    Route::post('/admin/approve-license/{userId}', [\App\Http\Controllers\AdminDashboardController::class, 'approveLicense'])->name('admin.approve-license');
    Route::get('/admin/consultations/{userId}', [\App\Http\Controllers\AdminDashboardController::class, 'getConsultation'])->name('admin.consultations.get');
    Route::post('/admin/messages/send', [\App\Http\Controllers\AdminDashboardController::class, 'sendMessage'])->name('admin.messages.send');
    Route::post('/admin/quests', [\App\Http\Controllers\AdminDashboardController::class, 'storeQuest'])->name('admin.quests.store');
    Route::put('/admin/quests/{questId}', [\App\Http\Controllers\AdminDashboardController::class, 'updateQuest'])->name('admin.quests.update');
    Route::delete('/admin/quests/{questId}', [\App\Http\Controllers\AdminDashboardController::class, 'destroyQuest'])->name('admin.quests.destroy');

    // Saudagar Consultation & Q&A Panel
    Route::get('/api/consultation/my', [\App\Http\Controllers\ConsultationController::class, 'getMyConsultation'])->name('consultation.my');
    Route::post('/api/consultation/reply', [\App\Http\Controllers\ConsultationController::class, 'replyToAdmin'])->name('consultation.reply');

    // Daily Check-in & Time-Window Horizons
    Route::get('/api/checkin/status', [\App\Http\Controllers\CheckinController::class, 'getStatus'])->name('checkin.status');
    Route::post('/api/checkin', [\App\Http\Controllers\CheckinController::class, 'checkin'])->name('checkin.perform');

    // POS Kasir Pintar & Business Hub
    Route::get('/api/pos/summary', [\App\Http\Controllers\PosController::class, 'getSummary'])->name('pos.summary');
    Route::post('/api/pos/transaction', [\App\Http\Controllers\PosController::class, 'recordTransaction'])->name('pos.transaction');
    Route::post('/api/pos/close-register', [\App\Http\Controllers\PosController::class, 'closeRegister'])->name('pos.close-register');
    Route::post('/api/user/switch-persona', [\App\Http\Controllers\PosController::class, 'switchPersona'])->name('user.switch-persona');
    Route::post('/api/user/upgrade-license', [\App\Http\Controllers\PosController::class, 'upgradeLicense'])->name('user.upgrade-license');

    // Locomo Ecosystem Service Hub & Dynamic Onboarding Wizard
    Route::post('/api/services/check-beneficiary', [\App\Http\Controllers\ServiceHubController::class, 'checkBeneficiary'])->name('services.check-beneficiary');
    Route::get('/services/{code}/onboard', [\App\Http\Controllers\ServiceHubController::class, 'showOnboard'])->name('services.onboard');
    Route::post('/services/{code}/submit', [\App\Http\Controllers\ServiceHubController::class, 'submitOnboard'])->name('services.submit');
    Route::post('/api/services/orders/{orderId}/pay-installment', [\App\Http\Controllers\ServiceHubController::class, 'payInstallment'])->name('services.orders.pay-installment');

    // Marketing Admin Portal (Affiliate Owners & Product Principals)
    Route::prefix('marketing')->name('marketing.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Marketing\MarketingPortalController::class, 'dashboard'])->name('dashboard');
        Route::get('/services/create', [\App\Http\Controllers\Marketing\MarketingPortalController::class, 'createService'])->name('services.create');
        Route::post('/services', [\App\Http\Controllers\Marketing\MarketingPortalController::class, 'storeService'])->name('services.store');
        Route::get('/services/{id}/edit', [\App\Http\Controllers\Marketing\MarketingPortalController::class, 'editService'])->name('services.edit');
        Route::put('/services/{id}', [\App\Http\Controllers\Marketing\MarketingPortalController::class, 'updateService'])->name('services.update');
        Route::get('/transactions', [\App\Http\Controllers\Marketing\MarketingPortalController::class, 'transactions'])->name('transactions');
    });

    // Baitul Mal & Kas Sosial Routes
    Route::get('/baitul-mal', [\App\Http\Controllers\BaitulMalController::class, 'index'])->name('baitul-mal.index');
    Route::post('/baitul-mal/donate', [\App\Http\Controllers\BaitulMalController::class, 'donate'])->name('baitul-mal.donate');
    Route::post('/api/baitul-mal/aamiin/{id}', [\App\Http\Controllers\BaitulMalController::class, 'aamiin'])->name('baitul-mal.aamiin');

    // WebPush Notifications (RFC 8292 VAPID)
    Route::get('/api/webpush/vapid-key', [\App\Http\Controllers\WebPushController::class, 'getPublicKey'])->name('webpush.vapid-key');
    Route::post('/api/webpush/subscribe', [\App\Http\Controllers\WebPushController::class, 'subscribe'])->name('webpush.subscribe');
    Route::post('/api/webpush/unsubscribe', [\App\Http\Controllers\WebPushController::class, 'unsubscribe'])->name('webpush.unsubscribe');
    Route::post('/api/webpush/test', [\App\Http\Controllers\WebPushController::class, 'sendTest'])->name('webpush.test');

    // Konsensus Juri Verifikasi Misi Khusus (5 Juri Acak & Honeypot Trap System)
    Route::get('/verifications', [\App\Http\Controllers\MissionVerificationController::class, 'index'])->name('verifications.index');
    Route::get('/verifications/{id}', [\App\Http\Controllers\MissionVerificationController::class, 'show'])->name('verifications.show');
    Route::post('/verifications/{id}/vote', [\App\Http\Controllers\MissionVerificationController::class, 'submitVote'])->name('verifications.vote');
    Route::post('/verifications/submit', [\App\Http\Controllers\MissionVerificationController::class, 'submitProof'])->name('verifications.submit');

    // Profile & Persona Identity Management
    Route::get('/profile', [\App\Http\Controllers\ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [\App\Http\Controllers\ProfileController::class, 'update'])->name('profile.update');
    Route::post('/profile/avatar', [\App\Http\Controllers\ProfileController::class, 'updateAvatar'])->name('profile.avatar.update');
    Route::post('/profile/avatar/preset', [\App\Http\Controllers\ProfileController::class, 'updateAvatarPreset'])->name('profile.avatar.preset');
    Route::post('/profile/preferences', [\App\Http\Controllers\ProfileController::class, 'updatePreferences'])->name('profile.preferences');
    Route::post('/profile/sessions/revoke-others', [\App\Http\Controllers\ProfileController::class, 'revokeOtherSessions'])->name('profile.sessions.revoke-others');
    Route::delete('/profile', [\App\Http\Controllers\ProfileController::class, 'destroy'])->name('profile.destroy');
});

// Public Legal & Help Center Routes
Route::get('/terms', [\App\Http\Controllers\LegalController::class, 'terms'])->name('legal.terms');
Route::get('/privacy', [\App\Http\Controllers\LegalController::class, 'privacy'])->name('legal.privacy');
Route::get('/help', [\App\Http\Controllers\HelpCenterController::class, 'index'])->name('help.index');
Route::get('/faq', [\App\Http\Controllers\HelpCenterController::class, 'index'])->name('faq.index');

// APIs that bypass auth middleware (with their own internal keys or mock triggers)
Route::post('/api/quests/resolve', [QuestController::class, 'resolve'])->name('quests.resolve');
Route::post('/api/payment/webhook', [PaymentController::class, 'webhook'])->name('payment.webhook');
Route::post('/api/admin/confirm-license/{userId}', [\App\Http\Controllers\OnboardingController::class, 'adminConfirmLicense'])->name('admin.confirm-license');

// KOA Atlas Inspector API Routes
Route::get('/api/koa-atlas/{name}', [\App\Http\Controllers\KoaAtlasController::class, 'load'])->name('koa-atlas.load');
Route::post('/api/koa-atlas/{name}', [\App\Http\Controllers\KoaAtlasController::class, 'save'])->name('koa-atlas.save');
Route::post('/api/koa-atlas/{name}/auto-segment', [\App\Http\Controllers\KoaAtlasController::class, 'autoSegment'])->name('koa-atlas.auto-segment');

require __DIR__.'/auth.php';
