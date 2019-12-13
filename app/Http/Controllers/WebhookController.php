<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class WebhookController extends BaseController
{
    public function index(Request $request){

        $content = file_get_contents("php://input");

        return $this->sendResponse(json_encode($content));
    }
}
