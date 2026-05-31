<?php

/*
|--------------------------------------------------------------------------
| PATH FILE:
| app/Models/Piket.php
|--------------------------------------------------------------------------
*/

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Piket extends Model
{
    use HasFactory;

    protected $table = 'pikets';

    protected $fillable = [
        'date',
        'user_id',
        'status',
        'department',
        'created_by',
    ];

    protected $casts = [
        'date' => 'date',
    ];

    /**
     * Relasi ke mekanik yang dipiketkan
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Relasi ke koordinator/admin yang membuat jadwal piket
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
