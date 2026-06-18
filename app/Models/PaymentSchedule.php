<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaymentSchedule extends Model
{
    use HasFactory;

    protected $fillable = [
        'description',
        'amount',
        'day',
        'deleted',
        'deadline_date'
    ];

    protected $dates = ['deadline_date'];

    public $timestamps = false;

    public function scopeActive($query){
        return $query->where('deleted', 0);
    }
}
