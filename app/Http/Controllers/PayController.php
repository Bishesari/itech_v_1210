<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class PayController extends Controller
{

    public function call_back(Request $request)
    {
        return response($request->all());
    }
}
