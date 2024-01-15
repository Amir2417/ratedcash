<?php

namespace App\Models\Admin;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SmsTemplate extends Model
{
    use HasFactory;
    protected $guarded = ['id'];

    protected $table = 'sms_templates';

    protected $casts    = [
        'id'            => 'integer',
        'act'           => 'string',
        'name'          => 'string',
        'subj'          => 'string',
        'sms_body'      => 'string',
        'shortcodes'    => 'object',
        'sms_status'    => 'string'
    ];
}
