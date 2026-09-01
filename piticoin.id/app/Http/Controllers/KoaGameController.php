<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Wallet;
use App\Models\LiquidityPoolSystem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Exception;

class KoaGameController extends Controller
{
    /**
     * Configuration parameters for the Slot KOA game.
     * Dynamic values that can be adjusted later as requested.
     */
    protected array $gameConfig = [
        'grid_rows' => 5,
        'grid_cols' => 5,
        'bet_options' => [20, 50, 100, 500, 1000, 5000, 10000, 18000],
        'card_multipliers' => [
            200 => 0.5, // Wild (Mandan)
            // High Tier (21 - 30)
            21 => 0.3, 22 => 0.3, 23 => 0.3, 24 => 0.3, 25 => 0.3,
            26 => 0.3, 27 => 0.3, 28 => 0.3, 29 => 0.3, 30 => 0.3,
            // Medium Tier (11 - 20)
            11 => 0.2, 12 => 0.2, 13 => 0.2, 14 => 0.2, 15 => 0.2,
            16 => 0.2, 17 => 0.2, 18 => 0.2, 19 => 0.2, 20 => 0.2,
            // Low Tier (1 - 10)
            1 => 0.1, 2 => 0.1, 3 => 0.1, 4 => 0.1, 5 => 0.1,
            6 => 0.1, 7 => 0.1, 8 => 0.1, 9 => 0.1, 10 => 0.1,
        ],
        'multipliers_normal' => [1, 2, 4, 5],
        'multipliers_free_spins' => [2, 4, 6, 10],
        'free_spins_reward' => 12,
        'conversion_rate' => 100, // 100 Silver = 1 Gold
        'max_win_pool_percentage' => 0.35 // 35% of pool limit
    ];

    /**
     * Verify that the user is authenticated and has an ACTIVE license.
     */
    protected function verifyUserLicense()
    {
        $user = Auth::user();
        if (!$user) {
            throw new Exception('Unauthenticated', 401);
        }

        if ($user->license_status !== 'ACTIVE') {
            throw new Exception('Akses ditolak: Hanya untuk member aktif dengan lisensi.', 403);
        }

        return $user;
    }

    /**
     * Get user's wallet balances and the global liquidity pool state.
     */
    public function getState()
    {
        try {
            $user = $this->verifyUserLicense();

            $wallet = Wallet::firstOrCreate(
                ['user_id' => $user->id],
                ['silver_balance' => 0, 'gold_balance' => 0]
            );

            $lp = LiquidityPoolSystem::find(1);
            if (!$lp) {
                $lp = LiquidityPoolSystem::create([
                    'id' => 1,
                    'total_fiat_in_bank' => 500000,
                    'total_gold_in_circulation' => 0,
                ]);
            }

            return response()->json([
                'success' => true,
                'wallet' => [
                    'silver_balance' => $wallet->silver_balance,
                    'gold_balance' => $wallet->gold_balance,
                ],
                'global_pool' => [
                    'total_fiat_in_bank' => $lp->total_fiat_in_bank,
                    'total_gold_in_circulation' => $lp->total_gold_in_circulation,
                ],
                'csrf_token' => csrf_token(),
                'game_config' => $this->gameConfig
            ]);

        } catch (Exception $e) {
            $code = $e->getCode();
            $status = in_array($code, [401, 403]) ? $code : 400;
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], $status);
        }
    }

    /**
     * Claim accumulated gold, burning silver from the user's wallet.
     */
    public function claimGold(Request $request)
    {
        try {
            $user = $this->verifyUserLicense();

            $request->validate([
                'gold_amount' => 'required|integer|min:1',
            ]);

            $goldAmount = intval($request->input('gold_amount'));
            $silverCost = $goldAmount * $this->gameConfig['conversion_rate'];

            $result = DB::transaction(function () use ($user, $goldAmount, $silverCost) {
                // 1. Lock user's wallet
                $wallet = Wallet::where('user_id', $user->id)->lockForUpdate()->first();
                if (!$wallet) {
                    throw new Exception('Dompet Anda tidak ditemukan.');
                }

                // 2. Validate silver balance
                if ($wallet->silver_balance < $silverCost) {
                    throw new Exception("Saldo koin perak tidak cukup untuk klaim. Dibutuhkan: {$silverCost} Perak, dimiliki: {$wallet->silver_balance} Perak.");
                }

                // 3. Lock liquidity pool
                $lp = LiquidityPoolSystem::where('id', 1)->lockForUpdate()->first();
                if (!$lp) {
                    throw new Exception('Sistem Liquidity Pool tidak terinisialisasi.');
                }

                // 4. Verify global circulation constraint
                $projectedGold = $lp->total_gold_in_circulation + $goldAmount;
                if ($projectedGold > $lp->total_fiat_in_bank) {
                    throw new Exception(
                        "Gagal klaim: Transaksi melanggar batas likuiditas global. Koin Emas beredar ({$projectedGold}) melebihi kapasitas fiat ({$lp->total_fiat_in_bank})."
                    );
                }

                // 5. Deduct silver (burned) and add gold
                $wallet->decrement('silver_balance', $silverCost);
                $wallet->increment('gold_balance', $goldAmount);

                // 6. Update liquidity pool
                $lp->increment('total_gold_in_circulation', $goldAmount);

                return [
                    'wallet' => $wallet->fresh(),
                    'global_pool' => $lp->fresh()
                ];
            });

            return response()->json([
                'success' => true,
                'message' => "Berhasil mengklaim {$goldAmount} Koin Emas!",
                'wallet' => [
                    'silver_balance' => $result['wallet']->silver_balance,
                    'gold_balance' => $result['wallet']->gold_balance,
                ],
                'global_pool' => [
                    'total_fiat_in_bank' => $result['global_pool']->total_fiat_in_bank,
                    'total_gold_in_circulation' => $result['global_pool']->total_gold_in_circulation,
                ]
            ]);

        } catch (Exception $e) {
            $code = $e->getCode();
            $status = in_array($code, [401, 403]) ? $code : 400;
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], $status);
        }
    }

    /**
     * Process spin request deterministically on the server side.
     */
    public function spin(Request $request)
    {
        try {
            $user = $this->verifyUserLicense();

            $request->validate([
                'bet_amount' => 'required|integer|in:' . implode(',', $this->gameConfig['bet_options']),
                'free_spins_active' => 'required|boolean',
                'free_spins_left' => 'required|integer|min:0',
                'free_spins_win_accumulated' => 'required|integer|min:0',
                'user_gold_quota' => 'required|numeric',
                'force_free_spins' => 'nullable|boolean'
            ]);

            $betAmount = intval($request->input('bet_amount'));
            $freeSpinsActive = filter_var($request->input('free_spins_active'), FILTER_VALIDATE_BOOLEAN);
            $freeSpinsLeft = intval($request->input('free_spins_left'));
            $freeSpinsWinAccumulated = intval($request->input('free_spins_win_accumulated'));
            $userGoldQuota = floatval($request->input('user_gold_quota'));
            $forceFreeSpins = filter_var($request->input('force_free_spins'), FILTER_VALIDATE_BOOLEAN);

            $result = DB::transaction(function () use ($user, $betAmount, $freeSpinsActive, $freeSpinsLeft, $freeSpinsWinAccumulated, $userGoldQuota, $forceFreeSpins) {
                // 1. Lock wallet and liquidity pool
                $wallet = Wallet::where('user_id', $user->id)->lockForUpdate()->first();
                if (!$wallet) {
                    throw new Exception('Dompet Anda tidak ditemukan.');
                }

                $lp = LiquidityPoolSystem::where('id', 1)->lockForUpdate()->first();
                if (!$lp) {
                    throw new Exception('Sistem Liquidity Pool tidak terinisialisasi.');
                }

                // 2. Validate session states & silver balance
                $isFreeSpin = ($freeSpinsActive && $freeSpinsLeft > 0);

                if (!$isFreeSpin) {
                    if ($wallet->silver_balance < $betAmount) {
                        throw new Exception("Saldo Perak Anda tidak mencukupi untuk melakukan spin.");
                    }
                    // Deduct bet amount (in Silver)
                    $wallet->decrement('silver_balance', $betAmount);
                } else {
                    $freeSpinsLeft--;
                }

                // 3. Define win limit caps based on Pool Bank (max 35%)
                // We use total_fiat_in_bank as pool size equivalent to Gold
                $maxWinCapInGold = intval($lp->total_fiat_in_bank * $this->gameConfig['max_win_pool_percentage']);
                $maxWinCapInSilver = $maxWinCapInGold * $this->gameConfig['conversion_rate'];

                // 4. RNG simulation loops to generate compliant cascades
                $selectedRound = null;

                if ($forceFreeSpins) {
                    $initialGrid = $this->generateWinningGrid(true);
                    // Ensure at least 3 Scatters are placed in the grid
                    $indices = [];
                    while (count($indices) < 3) {
                        $idx = mt_rand(0, 24);
                        if (!in_array($idx, $indices)) {
                            $indices[] = $idx;
                            $initialGrid[$idx] = 100;
                        }
                    }
                    $selectedRound = $this->simulateCascade($initialGrid, $betAmount, $isFreeSpin);
                    $selectedRound['scattersCount'] = 3;
                    $selectedRound['triggerCeki'] = true;
                    $selectedRound['triggerFreeSpins'] = true;
                } else {
                    $attempts = 0;
                    $maxAttempts = 30;

                    while ($attempts < $maxAttempts) {
                        $winRoll = mt_rand(0, 99) / 100;
                        $initialGrid = [];

                        if ($isFreeSpin) {
                            $initialGrid = ($winRoll < 0.60) ? $this->generateWinningGrid(false) : $this->generateNoWinGrid();
                        } elseif ($winRoll < 0.60) {
                            // 60% chance of a clean non-winning grid
                            $initialGrid = $this->generateNoWinGrid();
                        } else {
                            // 40% chance of starting with some matches
                            $initialGrid = $this->generateWinningGrid(false);
                        }

                        $cascadeResult = $this->simulateCascade($initialGrid, $betAmount, $isFreeSpin);

                        // Ensure the cascade win does not break the pool caps
                        if ($cascadeResult['totalWin'] <= $maxWinCapInSilver) {
                            $selectedRound = $cascadeResult;
                            $finalGrid = $cascadeResult['grids'][count($cascadeResult['grids']) - 1];
                            
                            $scatterCount = 0;
                            foreach ($finalGrid as $cell) {
                                if ($cell === 100) {
                                    $scatterCount++;
                                }
                            }

                            $selectedRound['scattersCount'] = $scatterCount;
                            $selectedRound['triggerCeki'] = ($scatterCount >= 2); // Tension mode trigger for 2 or more Scatters
                            $selectedRound['triggerFreeSpins'] = ($scatterCount >= 3);
                            break;
                        }
                        $attempts++;
                    }
                }

                if (!$selectedRound) {
                    $fallbackGrid = $this->generateNoWinGrid();
                    $selectedRound = [
                        'grids' => [$fallbackGrid],
                        'matches' => [],
                        'payouts' => [],
                        'multipliers' => [],
                        'totalWin' => 0,
                        'scattersCount' => 0,
                        'triggerCeki' => false,
                        'triggerFreeSpins' => false
                    ];
                }

                // 5. Update user wallet and pool balances
                $goldWin = floatval($selectedRound['totalWin'] / $this->gameConfig['conversion_rate']);
                $silverWin = 0; // Winnings are not paid in Silver, they go to Hasil Koin Emas pot
                $maxWinReached = false;

                // Bet decreases the Hasil Koin Emas pot
                if (!$isFreeSpin) {
                    $userGoldQuota -= ($betAmount / $this->gameConfig['conversion_rate']);
                }

                // Winnings are converted to Gold and added to Hasil Koin Emas pot
                $userGoldQuota += $goldWin;
                
                // Hasil Koin Emas cannot be negative
                $userGoldQuota = max(0.0, $userGoldQuota);

                if ($isFreeSpin) {
                    $freeSpinsWinAccumulated += $selectedRound['totalWin'];
                    if ($freeSpinsWinAccumulated >= $maxWinCapInSilver) {
                        $maxWinReached = true;
                        $freeSpinsLeft = 0;
                        $freeSpinsActive = false;
                    }
                }

                // Adjust liquidity pool bank (RTP calculation)
                if (!$isFreeSpin) {
                    // Add bet and subtract win to/from pool bank
                    $silverDiff = $betAmount - $selectedRound['totalWin'];
                    $goldDiff = floatval($silverDiff / $this->gameConfig['conversion_rate']);
                    $lp->increment('total_fiat_in_bank', intval(round($goldDiff)));
                } else {
                    // Free spin wins are paid out of the pool
                    $lp->decrement('total_fiat_in_bank', intval(round($goldWin)));
                }

                // Minimum safety boundary for the pool
                if ($lp->total_fiat_in_bank < 10000) {
                    $lp->total_fiat_in_bank = 10000;
                }

                // If Scatter triggers free spins for the first time
                if ($selectedRound['triggerFreeSpins'] && !$isFreeSpin) {
                    $freeSpinsActive = true;
                    $freeSpinsLeft = $this->gameConfig['free_spins_reward'];
                    $freeSpinsWinAccumulated = 0;
                }

                return [
                    'wallet' => $wallet->fresh(),
                    'global_pool' => $lp->fresh(),
                    'payload' => [
                        'success' => true,
                        'isFreeSpin' => $isFreeSpin,
                        'freeSpinsLeft' => $freeSpinsLeft,
                        'freeSpinsActive' => $freeSpinsActive,
                        'freeSpinsWinAccumulated' => $freeSpinsWinAccumulated,
                        'grids' => $selectedRound['grids'],
                        'matches' => $selectedRound['matches'],
                        'payouts' => $selectedRound['payouts'],
                        'multipliers' => $selectedRound['multipliers'],
                        'totalWin' => $selectedRound['totalWin'],
                        'goldWin' => $goldWin,
                        'silverWin' => $silverWin,
                        'triggerCeki' => $selectedRound['triggerCeki'],
                        'triggerFreeSpins' => $selectedRound['triggerFreeSpins'],
                        'maxWinReached' => $maxWinReached,
                        'userSilver' => $wallet->silver_balance,
                        'userGoldQuota' => $userGoldQuota,
                        'userGoldVault' => $wallet->gold_balance,
                        'liquidityPool' => $lp->total_fiat_in_bank
                    ]
                ];
            });

            return response()->json($result['payload']);

        } catch (Exception $e) {
            $code = $e->getCode();
            $status = in_array($code, [401, 403]) ? $code : 400;
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], $status);
        }
    }

    /**
     * Generate a 5x5 grid with NO matches present.
     */
    private function generateNoWinGrid(): array
    {
        $grid = array_fill(0, 25, 0);
        for ($r = 0; $r < 5; $r++) {
            for ($c = 0; $c < 5; $c++) {
                $index = $r * 5 + $c;
                $attempts = 0;
                do {
                    $cardId = mt_rand(1, 30);
                    $attempts++;
                } while (
                    (($c > 0 && $grid[$index - 1] === $cardId) || ($r > 0 && $grid[$index - 5] === $cardId)) && 
                    $attempts < 100
                );
                $grid[$index] = $cardId;
            }
        }
        return $grid;
    }

    /**
     * Generate a winning grid with initial matches or scatters.
     */
    private function generateWinningGrid(bool $forceScatters): array
    {
        $grid = $this->generateNoWinGrid();

        if ($forceScatters) {
            $indices = [];
            while (count($indices) < 3) {
                $idx = mt_rand(0, 24);
                if (!in_array($idx, $indices)) {
                    $indices[] = $idx;
                    $grid[$idx] = 100; // Scatter
                }
            }
        } else {
            $matchCardType = mt_rand(1, 30);
            $startPos = mt_rand(0, 2); // Can span 3 horizontally: 0, 1, 2
            $row = mt_rand(0, 4);
            
            $grid[$row * 5 + $startPos] = $matchCardType;
            $grid[$row * 5 + $startPos + 1] = $matchCardType;
            $grid[$row * 5 + $startPos + 2] = $matchCardType;

            // 15% chance to replace adjacent with Wild
            if (mt_rand(0, 99) < 15) {
                $wildIdx = $row * 5 + $startPos + (mt_rand(0, 1) === 0 ? -1 : 3);
                if ($wildIdx >= 0 && $wildIdx < 25) {
                    $grid[$wildIdx] = 200; // Wild
                }
            }
        }
        return $grid;
    }

    /**
     * DFS/Flood fill algorithm to locate cluster matches in a 5x5 grid.
     */
    private function findMatches(array $grid): array
    {
        $matchedIndices = [];
        $detailedMatches = [];

        // Check regular cards (1 to 30)
        for ($cardType = 1; $cardType <= 30; $cardType++) {
            $visited = array_fill(0, 25, false);

            for ($i = 0; $i < 25; $i++) {
                $cell = $grid[$i];
                if (($cell === $cardType || $cell === 200) && !$visited[$i]) {
                    $cluster = [];
                    $queue = [$i];
                    $visited[$i] = true;

                    while (count($queue) > 0) {
                        $curr = array_shift($queue);
                        $cluster[] = $curr;

                        $r = intval($curr / 5);
                        $c = $curr % 5;

                        $neighbors = [];
                        if ($r > 0) $neighbors[] = ($r - 1) * 5 + $c;
                        if ($r < 4) $neighbors[] = ($r + 1) * 5 + $c;
                        if ($c > 0) $neighbors[] = $r * 5 + $c - 1;
                        if ($c < 4) $neighbors[] = $r * 5 + $c + 1;

                        foreach ($neighbors as $n) {
                            $nVal = $grid[$n];
                            if (($nVal === $cardType || $nVal === 200) && !$visited[$n]) {
                                $visited[$n] = true;
                                $queue[] = $n;
                            }
                        }
                    }

                    if (count($cluster) >= 3) {
                        $hasRegularCard = false;
                        foreach ($cluster as $idx) {
                            if ($grid[$idx] === $cardType) {
                                $hasRegularCard = true;
                                break;
                            }
                        }
                        if ($hasRegularCard) {
                            foreach ($cluster as $idx) {
                                $matchedIndices[$idx] = true;
                            }
                            $detailedMatches[] = [
                                'cardType' => $cardType,
                                'indices' => $cluster
                            ];
                        }
                    }
                }
            }
        }

        // Check for pure Wild matches (3 or more adjacent Wilds)
        $wildVisited = array_fill(0, 25, false);
        for ($i = 0; $i < 25; $i++) {
            if ($grid[$i] === 200 && !$wildVisited[$i]) {
                $cluster = [];
                $queue = [$i];
                $wildVisited[$i] = true;

                while (count($queue) > 0) {
                    $curr = array_shift($queue);
                    $cluster[] = $curr;

                    $r = intval($curr / 5);
                    $c = $curr % 5;

                    $neighbors = [];
                    if ($r > 0) $neighbors[] = ($r - 1) * 5 + $c;
                    if ($r < 4) $neighbors[] = ($r + 1) * 5 + $c;
                    if ($c > 0) $neighbors[] = $r * 5 + $c - 1;
                    if ($c < 4) $neighbors[] = $r * 5 + $c + 1;

                    foreach ($neighbors as $n) {
                        if ($grid[$n] === 200 && !$wildVisited[$n]) {
                            $wildVisited[$n] = true;
                            $queue[] = $n;
                        }
                    }
                }

                if (count($cluster) >= 3) {
                    foreach ($cluster as $idx) {
                        $matchedIndices[$idx] = true;
                    }
                    $detailedMatches[] = [
                        'cardType' => 200,
                        'indices' => $cluster
                    ];
                }
            }
        }

        return [
            'matchedIndices' => array_keys($matchedIndices),
            'detailedMatches' => $detailedMatches
        ];
    }

    /**
     * Cascading physics logic simulation loop.
     */
    private function simulateCascade(array $initialGrid, int $betAmount, bool $isFreeSpin): array
    {
        $grids = [json_decode(json_encode($initialGrid), true)];
        $matches = [];
        $payouts = [];
        $multipliers = [];
        $currentGrid = json_decode(json_encode($initialGrid), true);
        
        $step = 0;
        $totalWin = 0;
        $injectScatterNext = false;

        // Build active deck
        $activeSubDeck = [];
        while (count($activeSubDeck) < 10) {
            $id = mt_rand(1, 30);
            if (!in_array($id, $activeSubDeck)) {
                $activeSubDeck[] = $id;
            }
        }

        $getNewCardId = function() use ($activeSubDeck) {
            $rng = mt_rand(0, 99) / 100;
            if ($rng < 0.02) {
                return 200; // 2% Wild
            }
            return $activeSubDeck[array_rand($activeSubDeck)];
        };

        // Limit cascade depth to 10 steps to prevent stack overflow
        while ($step < 10) {
            $matchInfo = $this->findMatches($currentGrid);
            if (count($matchInfo['matchedIndices']) === 0) {
                break;
            }

            // Normal multipliers: [1, 2, 4, 5], Free spins: [2, 4, 6, 10]
            $multScale = $isFreeSpin ? $this->gameConfig['multipliers_free_spins'] : $this->gameConfig['multipliers_normal'];
            $mult = $multScale[min($step, count($multScale) - 1)];

            // Calculate payout
            $stepWin = 0;
            foreach ($matchInfo['detailedMatches'] as $match) {
                $cardType = $match['cardType'];
                $cardMult = $this->gameConfig['card_multipliers'][$cardType] ?? 0;
                $stepWin += $betAmount * $cardMult * count($match['indices']) * $mult;
            }

            $payouts[] = $stepWin;
            $multipliers[] = $mult;
            $matches[] = $matchInfo;
            $totalWin += $stepWin;

            // Trigger Scatter drop on 3rd cascade (step === 2) AND 4th cascade (step === 3)
            if ($step === 2 || $step === 3) {
                $injectScatterNext = true;
            }

            $nextGrid = json_decode(json_encode($currentGrid), true);

            // Nullify matched cells: Gold-framed cards have a 30% chance to transform into Wilds
            $handIndices = [5, 9, 11, 13, 17, 21, 22, 23, 12, 16, 18];
            foreach ($matchInfo['matchedIndices'] as $idx) {
                $col = $idx % 5;
                $isHand = in_array($idx, $handIndices);
                if ($isHand && $col > 0 && mt_rand(0, 99) < 30) {
                    $nextGrid[$idx] = 200; // Transforms to Wild
                } else {
                    $nextGrid[$idx] = null;
                }
            }

            // Drop cards down column by column
            for ($c = 0; $c < 5; $c++) {
                $colCards = [];
                for ($r = 4; $r >= 0; $r--) {
                    $idx = $r * 5 + $c;
                    if ($nextGrid[$idx] !== null) {
                        $colCards[] = $nextGrid[$idx];
                    }
                }

                while (count($colCards) < 5) {
                    if ($injectScatterNext) {
                        $colCards[] = 100; // Inject Scatter
                        $injectScatterNext = false;
                    } else {
                        $colCards[] = $getNewCardId();
                    }
                }

                $colCards = array_reverse($colCards);
                for ($r = 0; $r < 5; $r++) {
                    $nextGrid[$r * 5 + $c] = $colCards[$r];
                }
            }

            $grids[] = $nextGrid;
            $currentGrid = $nextGrid;
            $step++;
        }

        return [
            'grids' => $grids,
            'matches' => $matches,
            'payouts' => $payouts,
            'multipliers' => $multipliers,
            'totalWin' => $totalWin
        ];
    }
}
