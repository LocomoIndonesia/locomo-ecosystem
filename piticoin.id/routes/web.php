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

    // Auction Routes
    Route::get('/auction', [\App\Http\Controllers\AuctionController::class, 'index'])->name('auction.index');
    Route::post('/api/auction/buyout', [\App\Http\Controllers\AuctionController::class, 'buyout'])->name('auction.buyout');

    // Digital Goods Routes
    Route::get('/digital-goods', [\App\Http\Controllers\DigitalGoodsController::class, 'index'])->name('digital-goods.index');
    Route::post('/api/digital-goods/redeem', [\App\Http\Controllers\DigitalGoodsController::class, 'redeem'])->name('digital-goods.redeem');
});

// APIs that bypass auth middleware (with their own internal keys or mock triggers)
Route::post('/api/quests/resolve', [QuestController::class, 'resolve'])->name('quests.resolve');
Route::post('/api/payment/webhook', [PaymentController::class, 'webhook'])->name('payment.webhook');

// KOA Atlas Inspector API Routes
Route::get('/api/koa-atlas/{name}', [\App\Http\Controllers\KoaAtlasController::class, 'load'])->name('koa-atlas.load');
Route::post('/api/koa-atlas/{name}', [\App\Http\Controllers\KoaAtlasController::class, 'save'])->name('koa-atlas.save');
Route::post('/api/koa-atlas/{name}/auto-segment', [\App\Http\Controllers\KoaAtlasController::class, 'autoSegment'])->name('koa-atlas.auto-segment');

require __DIR__.'/auth.php';
