<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AccountReceivable extends Model
{
    use HasFactory;

    protected $table = 'accounts_receivable';

    protected $fillable = [
        'tenant_id',
        'customer_id',
        'order_id',
        'receivable_number',
        'total_debt_amount',
        'remaining_amount',
        'due_date',
        'status',
        'notes',
    ];

    protected $casts = [
        'total_debt_amount' => 'integer',
        'remaining_amount' => 'integer',
        'due_date' => 'date',
    ];

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function payments()
    {
        return $this->hasMany(ReceivablePayment::class, 'receivable_id');
    }
}
