<?php

declare(strict_types=1);

namespace App\Modules\Vanguard\Models;

use App\Models\BaseModel;
use Illuminate\Support\Facades\Crypt;

class VanguardVote extends BaseModel
{
    protected $fillable = [
        'encrypted_ballot',
        'signature_hash',
        'survey_id',
        'voter_hash',
    ];

    /**
     * Store and encrypt a ballot choice securely using AES-256-CBC (ISO standard)
     * and SHA-256 hashing for signature verification.
     * Supports updating existing vote to allow changing choices before voting ends.
     */
    public static function castVote(string $choice, string $voterTrackingId, string $surveyId): self
    {
        // Encrypt the ballot choice using AES-256-CBC (ISO/IEC 18033-3)
        $encrypted = Crypt::encryptString($choice);
        
        // Generate signature hash for ballot verification (ISO/IEC 10118-3 SHA-256)
        $signature = hash('sha256', $choice . $voterTrackingId . config('app.key'));

        // Generate anonymous voter hash to support vote changes without identifying the user directly
        $voterHash = hash('sha256', $voterTrackingId . $surveyId . config('app.key'));

        // Look up if a vote for this survey and voter already exists
        $vote = self::where('survey_id', $surveyId)
            ->where('voter_hash', $voterHash)
            ->first();

        if ($vote) {
            $vote->update([
                'encrypted_ballot' => $encrypted,
                'signature_hash' => $signature,
            ]);
            return $vote;
        }

        return self::create([
            'encrypted_ballot' => $encrypted,
            'signature_hash' => $signature,
            'survey_id' => $surveyId,
            'voter_hash' => $voterHash,
        ]);
    }

    /**
     * Decrypt the ballot choice
     */
    public function getChoice(): string
    {
        return Crypt::decryptString($this->encrypted_ballot);
    }

    public function survey()
    {
        return $this->belongsTo(VanguardSurvey::class, 'survey_id');
    }
}
