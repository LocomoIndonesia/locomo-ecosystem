<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Purchase extends Model
{
    use HasFactory;

    protected $fillable = [
        'tenant_id',
        'supplier_id',
        'purchase_number',
        'supplier_invoice_no',
        'purchase_date',
        'total_amount',
        'payment_status',
        'payment_method',
        'due_date',
        'notes',
        'created_by_user_id',
    ];

    protected $casts = [
        'purchase_date' => 'date',
        'due_date' => 'date',
        'total_amount' => 'integer',
    ];

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function items()
    {
        return $this->hasMany(PurchaseItem::class);
    }

    public function accountPayable()
    {
        return $this->hasOne(AccountPayable::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }
}
