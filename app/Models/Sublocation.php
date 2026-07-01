<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Sublocation extends Model
{
    use HasFactory;

    protected $fillable = [
        'location_id',
        'name',
        'deleted'
    ];

    public $timestamps = true;

    public function scopeActive($query){
        return $query->where('deleted', 0);
    }

    public function location(){
        return $this->belongsTo(Location::class);
    }
}