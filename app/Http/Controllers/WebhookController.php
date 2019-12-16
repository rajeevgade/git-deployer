<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class WebhookController extends BaseController
{
    public function index(Request $request){

        $startTime = microtime(true);

        $headers = $request->headers->all();

        $postData = $request->getContent();

        $bodyData = file_get_contents('php://input');

        $validate = $this->validateData($bodyData);

       if(!$validate){
           return $this->sendError("No Valid Handler Found");
       }

       $endTime = microtime(true);

       $message = "Repo <repo-name> - <branch-name> synced in " .number_format($endTime - $startTime, 2) . "seconds";
        
       $this->updateLog($message);

        return $this->sendResponse($message);
    }

    public function validateData(){
        return true;
    }

    public function updateLog($msg = ""){

        //$log_file = 'git.log';
        
        $content = "===================";
        $content .= $msg;
        $content .= "===================";

        Storage::append('git.log', $content);
        
        //open file and write log message
        /* if (!file_exists($log_file)){
			file_put_contents($log_file, $content);
		}
        else{
			$fp = fopen($log_file, 'a');
			fwrite($fp, $content);  
			fclose($fp);  
		} */
		
    }
}
