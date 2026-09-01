<?php

declare(strict_types=1);

namespace App\Modules\Vanguard\Models;

use App\Models\BaseModel;

class TicRevitalizationLog extends BaseModel
{
    protected $table = 'tic_revitalization_logs';

    protected $fillable = [
        'title',
        'reporter_name',
        'description',
        'status', // pending, in_progress, completed
    ];
}
