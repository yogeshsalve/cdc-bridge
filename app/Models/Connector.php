<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Connector extends Model {
    protected $fillable = [
        'name','db_type','host','port','database','username','password','status'
    ];
}