<?php

namespace App\Models\Admin;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VirtualAccountService extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $casts    = [
        'id'            => 'integer',
        'admin_id'      => 'integer',
        'image'         => 'string',
        'card_details'  => 'string',
        'config'        => 'object'
    ];
}
