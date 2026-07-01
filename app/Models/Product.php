<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
        'product_type_id',
        'location',
        'location_id',
        'sublocation_id',
        'stock',
        'deleted'
    ];

    protected $dates = ['date'];

    public $timestamps = false;

    public function scopeActive($query){
        return $query->where('deleted', 0);
    }

    public function product_type(){
        return $this->belongsTo(ProductType::class);
    }

    public function location_model(){
        return $this->belongsTo(Location::class, 'location_id');
    }

    public function sublocation(){
        return $this->belongsTo(Sublocation::class, 'sublocation_id');
    }
}
