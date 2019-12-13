<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class WebhookController extends BaseController
{
    public function index(Request $request){
        return $this->sendResponse($request);
    }
}
