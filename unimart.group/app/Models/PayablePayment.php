<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PayablePayment extends Model
{
    use HasFactory;

    protected $fillable = [
        'tenant_id',
        'payable_id',
        'shift_id',
        'payment_method',
        'amount_paid',
        'processed_by_user_id',
        'receipt_image_url',
        'notes',
    ];

    protected $casts = [
        'amount_paid' => 'integer',
    ];

    public function accountPayable()
    {
        return $this->belongsTo(AccountPayable::class, 'payable_id');
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
