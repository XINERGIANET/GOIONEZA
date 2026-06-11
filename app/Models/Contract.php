<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Contract extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'code',
        'document',
        'name',
        'business_document',
        'business_name',
        'phone',
        'email',
        'location_id',
        'event_type_id',
        'event_date',
        'event_time',
        'event_duration',
        'event_end',
        'package_id',
        'people_number',
        'extras',
        'employees',
        'total',
        'discount_type',
        'discount',
        'initial_payment',
        'payment_type',
        'payment_method_id',
        'date',
        'debt',
        'debt_payment_date',
        'paid',
        'deleted'
    ];

    protected $dates = ['event_date', 'event_time', 'event_end', 'date', 'debt_payment_date'];
    
    public $timestamps = false;

    public function scopeActive($query){
        return $query; // SoftDeletes handles the filtering natively now
    }

    public function scopePending($query){
        return $query->where('paid', 0);
    }

    public function location(){
        return $this->belongsTo(Location::class);
    }

    public function event_type(){
        return $this->belongsTo(EventType::class);
    }

    public function package(){
        return $this->belongsTo(Package::class);
    }

    public function payment_method(){
        return $this->belongsTo(PaymentMethod::class);
    }

    public function payments(){
        return $this->hasMany(Payment::class);
    }
}
