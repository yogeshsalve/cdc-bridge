<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CDCLog extends Model
{
    use HasFactory;
    protected $table = 'cdc_logs';
    protected $fillable = [
        'table_name',
        'operation',
        'data',
    ];

    protected $casts = [
        'data' => 'array',
    ];
}
