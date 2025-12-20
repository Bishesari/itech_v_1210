<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PageVisitLog extends Model
{
    protected $fillable = ['page', 'ip', 'user_agent', 'visit_date'];
}
