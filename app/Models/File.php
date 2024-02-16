<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class File extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'file_id',
        'url',
        'owner_id',
    ];

}
