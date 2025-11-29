<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OtpLog extends Model
{
    protected $fillable=['ip', 'n_code', 'mobile_nu', 'otp', 'otp_next_try_time'];
}
