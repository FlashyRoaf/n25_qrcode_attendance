<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
    //
    protected $fillable = [
        'name',
        'qrcode',
        'shift',
        'division',
        'date',
        'status',
        'check_in',
        'check_out',
        'location',
    ];
}
