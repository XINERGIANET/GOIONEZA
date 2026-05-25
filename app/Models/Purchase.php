<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Purchase extends Model
{
    use HasFactory;

    protected $fillable = [
        'description',
        'voucher',
        'voucher_number',
        'provider',
        'amount',
        'expense_type_id',
        'payment_method_id',
        'date',
        'deleted'
    ];

    protected $dates = ['date'];
    
    public $timestamps = false;

    public function scopeActive($query){
        return $query->where('deleted', 0);
    }

    public function expense_type(){
        return $this->belongsTo(ExpenseType::class);
    }

    public function payment_method(){
        return $this->belongsTo(PaymentMethod::class);
    }
}
