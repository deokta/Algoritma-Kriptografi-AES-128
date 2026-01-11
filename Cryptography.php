<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cryptography extends Model
{
    use HasFactory;

    protected $table = 'cryptographies';
    protected $fillable = [
        'name',
        'file',
        'status'
    ];
}
