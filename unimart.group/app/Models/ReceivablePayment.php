<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReceivablePayment extends Model
{
    use HasFactory;

    protected $fillable = [
        'tenant_id',
        'receivable_id',
        'shift_id',
        'payment_method',
        'amount_paid',
        'processed_by_user_id',
        'notes',
    ];

    protected $casts = [
        'amount_paid' => 'integer',
    ];

    public function accountReceivable()
    {
        return $this->belongsTo(AccountReceivable::class, 'receivable_id');
    }

    public function shift()
    {
        return $this->belongsTo(Shift::class);
    }

    public function processor()
    {
        return $this->belongsTo(User::class, 'processed_by_user_id');
    }
}
