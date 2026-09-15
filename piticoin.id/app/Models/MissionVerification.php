<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MissionVerification extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'mission_verifications';

    protected $fillable = [
        'quest_id',
        'submitter_user_id',
        'proof_image_url',
        'proof_description',
        'mission_title',
        'reward_gold',
        'reward_silver',
        'is_honeypot',
        'honeypot_correct_verdict',
        'status',
        'valid_votes_count',
        'reject_votes_count',
        'total_jurors_count',
        'consensus_reached_at',
    ];

    protected $casts = [
        'is_honeypot' => 'boolean',
        'valid_votes_count' => 'integer',
        'reject_votes_count' => 'integer',
        'total_jurors_count' => 'integer',
        'reward_gold' => 'integer',
        'reward_silver' => 'integer',
        'consensus_reached_at' => 'datetime',
    ];

    protected $appends = [
        'masked_submitter',
    ];

    public function submitter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'submitter_user_id');
    }

    public function quest(): BelongsTo
    {
        return $this->belongsTo(Quest::class, 'quest_id');
    }

    public function jurors(): HasMany
    {
        return $this->hasMany(MissionVerificationJuror::class, 'verification_id');
    }

    /**
     * Nama pengguna pembuat yang disamarkan (Blind Validation)
     */
    public function getMaskedSubmitterAttribute(): string
    {
        if (!$this->submitter) {
            return 'Penjelajah Misterius';
        }

        $name = $this->submitter->name;
        $words = explode(' ', trim($name));
        $maskedWords = [];

        foreach ($words as $word) {
            $len = mb_strlen($word);
            if ($len <= 2) {
                $maskedWords[] = $word[0] . '*';
            } elseif ($len <= 4) {
                $maskedWords[] = mb_substr($word, 0, 1) . str_repeat('*', $len - 2) . mb_substr($word, -1);
            } else {
                $maskedWords[] = mb_substr($word, 0, 2) . str_repeat('*', $len - 3) . mb_substr($word, -1);
            }
        }

        return implode(' ', $maskedWords);
    }

    public function isResolved(): bool
    {
        return in_array($this->status, ['CONSENSUS_APPROVED', 'CONSENSUS_REJECTED', 'EXPIRED'], true);
    }
}
