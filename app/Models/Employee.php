<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Employee extends Model
{
    use HasFactory;

    protected $fillable = [
        'document',
        'name',
        'job',
        'function',
        'phone',
        'birth_date',
        'event_payment',
        'user_id',
        'deleted'
    ];

    protected $dates = ['birth_date'];

    public $timestamps = false;

    public function scopeActive($query){
        return $query->where('deleted', 0);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
