<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PageVisit extends Model
{
    protected $fillable = [
        'page_key',
        'fingerprint',
        'visitor_type',
        'ip',
        'user_agent',
        'visit_date',
    ];
}
