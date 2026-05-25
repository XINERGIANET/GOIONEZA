<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Quotation extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'phone',
        'package_id',
        'people_number',
        'event_date',
        'answer_date',
        'date',
        'deleted'
    ];

    protected $dates = ['event_date', 'answer_date', 'date'];
    
    public $timestamps = false;

    public function scopeActive($query){
        return $query->where('deleted', 0);
    }

    public function package(){
        return $this->belongsTo(Package::class);
    }
}
