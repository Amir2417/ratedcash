<?php

namespace App\Models;

use App\Models\Admin\ReceiverCounty;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Receipient extends Model
{
    use HasFactory;
    protected $guarded = ['id'];
    protected $casts        = [
        'user_id'           => 'integer',
        'bank_name'         => 'string',
        'bank_code'         => 'string',
        'account_name'      => 'string',
        'account_number'    => 'string',
    ];
    public function scopeAuth($query) {
        $query->where("user_id",auth()->user()->id);
    }
    public function getFullnameAttribute()
    {

        return $this->firstname . ' ' . $this->lastname;
    }
    public function user() {
        return $this->belongsTo(User::class);
    }
    public function receiver_country() {
        return $this->belongsTo(ReceiverCounty::class,'country');
    }
   

}
