<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Tenant\BelongsToTenant;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Order extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'shift_id',
        'customer_id',
        'table_id',
        'order_number',
        'customer_name',
        'order_type',
        'kitchen_status',
        'payment_status',
        'total_amount',
        'order_source',
        'delivery_address',
        'delivery_latitude',
        'delivery_longitude',
        'delivery_fee',
        'notes',
        'metadata',
    ];

    protected $casts = [
        'total_amount' => 'integer',
        'delivery_fee' => 'integer',
        'metadata' => 'array',
    ];

    protected $appends = ['status', 'table_number'];

    public function getStatusAttribute()
    {
        return $this->kitchen_status;
    }

    public function setStatusAttribute($value)
    {
        $this->attributes['kitchen_status'] = $value;
    }

    public function getTableNumberAttribute()
    {
        if ($this->order_source === 'online') {
            return 'Online';
        }
        $tableRelation = $this->getAttribute('table');
        return $tableRelation ? $tableRelation->name : ($this->order_type === 'takeaway' ? 'Takeaway' : 'Counter');
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function table(): BelongsTo
    {
        return $this->belongsTo(Table::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function shift(): BelongsTo
    {
        return $this->belongsTo(Shift::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function accountReceivable(): HasOne
    {
        return $this->hasOne(AccountReceivable::class);
    }
}
