<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Income extends Model
{
    use HasFactory;

    protected $fillable = [
        'description',
        'amount',
        'income_type_id',
        'payment_method_id',
        'location_id',
        'date',
        'deleted'
    ];

    protected $dates = ['date'];
    
    public $timestamps = false;

    public function scopeActive($query){
        return $query->where('deleted', 0);
    }

    public function income_type(){
        return $this->belongsTo(IncomeType::class);
    }

    public function payment_method(){
        return $this->belongsTo(PaymentMethod::class);
    }

    public function location(){
        return $this->belongsTo(Location::class);
    }
}
