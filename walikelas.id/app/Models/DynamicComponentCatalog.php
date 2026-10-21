<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DynamicComponentCatalog extends Model
{
    use HasFactory;

    protected $table = 'dynamic_component_catalog';
    protected $primaryKey = 'component_type';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;

    protected $fillable = [
        'component_type',
        'schema_definition',
        'description',
        'is_active',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'schema_definition' => 'array',
            'is_active' => 'boolean',
            'created_at' => 'datetime',
        ];
    }
}
