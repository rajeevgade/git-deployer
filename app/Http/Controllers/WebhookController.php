<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class WebhookController extends BaseController
{
    public function index(Request $request){

        $startTime = microtime(true);

        $this->updateLog(date("d-m-Y (H:i:s)", time()) . "\n");

        $headers = $request->headers->all();

        $postData = $request->getContent();

        $content = file_get_contents('php://input');

        $this->validateData($content);

        /* if(!$validate){
            return $this->sendError("No Valid Handler Found");
        } */

        $endTime = microtime(true);

        $message = "Repo <repo-name> - <branch-name> synced in " .number_format($endTime - $startTime, 2) . "seconds";
            
        $this->updateLog($message);

        return $this->sendResponse($message);
    }

    public function validateData($content){
        $token = false;
        //$file = 'deploy.log';
        $secretToken = "test123";
        $json = json_decode($content, true); 
        $branch = "refs/heads/master";
        $afterPull = "";
        $beforePull = "";
        $directory = "/var/www/html/test-deployer/";
        $DIR = preg_match("/\/$/", $directory) ? $directory : $directory . "/";
        $git = "/usr/bin/git";
        $sha = false;

        // retrieve the token
        if (!$token && isset($_SERVER["HTTP_X_HUB_SIGNATURE"])) {
            list($algo, $token) = explode("=", $_SERVER["HTTP_X_HUB_SIGNATURE"], 2) + array("", "");
        } elseif (isset($_SERVER["HTTP_X_GITLAB_TOKEN"])) {
            $token = $_SERVER["HTTP_X_GITLAB_TOKEN"];
        } elseif (isset($_GET["token"])) {
            $token = $_GET["token"];
        }

        $message = "";

        // Check for a GitHub signature
        if (!empty($secretToken) && isset($_SERVER["HTTP_X_HUB_SIGNATURE"]) && $token !== hash_hmac($algo, $content, $secretToken)) {
            $message = "X-Hub-Signature does not match TOKEN";
            $this->updateLog($message);
            return $this->sendError($message);
        // Check for a GitLab token
        } elseif (!empty($secretToken) && isset($_SERVER["HTTP_X_GITLAB_TOKEN"]) && $token !== $secretToken) {
            $message = "X-GitLab-Token does not match TOKEN";
            $this->updateLog($message);
            return $this->sendError($message);
        // Check for a $_GET token
        } elseif (!empty($secretToken) && isset($_GET["token"]) && $token !== $secretToken) {
            $message = "\$_GET[\"token\"] does not match TOKEN";
            $this->updateLog($message);
            return $this->sendError($message);
        // if none of the above match, but a token exists, exit
        } elseif (!empty($secretToken) && !isset($_SERVER["HTTP_X_HUB_SIGNATURE"]) && !isset($_SERVER["HTTP_X_GITLAB_TOKEN"]) && !isset($_GET["token"])) {
            $message = "No token detected";
            $this->updateLog($message);
            return $this->sendError($message);
        } else {
            // check if pushed branch matches branch specified in config
            if ($json["ref"] === $branch) {
                $this->updateLog($content . PHP_EOL);
                // ensure directory is a repository
                if (file_exists($DIR . ".git") && is_dir($DIR)) {
                    // change directory to the repository
                    chdir($DIR);
                    // write to the log
                    $this->updateLog("*** AUTO PULL INITIATED ***" . "\n");
                    /**
                     * Attempt to reset specific hash if specified
                     */
                    if (!empty($_GET["reset"]) && $_GET["reset"] === "true") {
                        // write to the log
                        $this->updateLog("*** RESET TO HEAD INITIATED ***" . "\n");
                        exec($git . " reset --hard HEAD 2>&1", $output, $exit);
                        // reformat the output as a string
                        $output = (!empty($output) ? implode("\n", $output) : "[no output]") . "\n";
                        // if an error occurred, return 500 and log the error
                        if ($exit !== 0) {
                            http_response_code(500);
                            $output = "=== ERROR: Reset to head failed using GIT `" . $git . "` ===\n" . $output;
                        }
                        // write the output to the log and the body
                        $this->updateLog($output);
                        echo $output;
                    }
                    /**
                     * Attempt to execute BEFORE_PULL if specified
                     */
                    if (!empty($beforePull)) {
                        // write to the log
                        $this->updateLog("*** BEFORE_PULL INITIATED ***" . "\n");
                        // execute the command, returning the output and exit code
                        exec($beforePull . " 2>&1", $output, $exit);
                        // reformat the output as a string
                        $output = (!empty($output) ? implode("\n", $output) : "[no output]") . "\n";
                        // if an error occurred, return 500 and log the error
                        if ($exit !== 0) {
                            http_response_code(500);
                            $output = "=== ERROR: BEFORE_PULL `" . $beforePull . "` failed ===\n" . $output;
                        }
                        // write the output to the log and the body
                        $this->updateLog($output);
                        echo $output;
                    }
                    /**
                     * Attempt to pull, returing the output and exit code
                     */
                    exec($git . " pull 2>&1", $output, $exit);
                    // reformat the output as a string
                    $output = (!empty($output) ? implode("\n", $output) : "[no output]") . "\n";
                    // if an error occurred, return 500 and log the error
                    if ($exit !== 0) {
                        http_response_code(500);
                        $output = "=== ERROR: Pull failed using GIT `" . $git . "` and DIR `" . $directory . "` ===\n" . $output;
                    }
                    // write the output to the log and the body
                    $this->updateLog($output);
                    echo $output;
                    /**
                     * Attempt to checkout specific hash if specified
                     */
                    if (!empty($sha)) {
                        // write to the log
                        $this->updateLog("*** RESET TO HASH INITIATED ***" . "\n");
                        exec($git . " reset --hard {$sha} 2>&1", $output, $exit);
                        // reformat the output as a string
                        $output = (!empty($output) ? implode("\n", $output) : "[no output]") . "\n";
                        // if an error occurred, return 500 and log the error
                        if ($exit !== 0) {
                            http_response_code(500);
                            $output = "=== ERROR: Reset failed using GIT `" . $git . "` and \$sha `" . $sha . "` ===\n" . $output;
                        }
                        // write the output to the log and the body
                        $this->updateLog($output);
                        echo $output;
                    }
                    /**
                     * Attempt to execute $afterPull if specified
                     */
                    if (!empty($afterPull)) {
                        // write to the log
                        $this->updateLog("*** AFTER_PULL INITIATED ***" . "\n");
                        // execute the command, returning the output and exit code
                        exec($afterPull . " 2>&1", $output, $exit);
                        // reformat the output as a string
                        $output = (!empty($output) ? implode("\n", $output) : "[no output]") . "\n";
                        // if an error occurred, return 500 and log the error
                        if ($exit !== 0) {
                            http_response_code(500);
                            $output = "=== ERROR: AFTER_PULL `" . $afterPull . "` failed ===\n" . $output;
                        }
                        // write the output to the log and the body
                        $this->updateLog($output);
                        echo $output;
                    }
                    // write to the log
                    $this->updateLog("*** AUTO PULL COMPLETE ***" . "\n");
                } else {
                    // prepare the generic error
                    $error = "=== ERROR: DIR `" . $directory . "` is not a repository ===\n";
                    // try to detemrine the real error
                    if (!file_exists($directory)) {
                        $error = "=== ERROR: DIR `" . $directory . "` does not exist ===\n";
                    } elseif (!is_dir($directory)) {
                        $error = "=== ERROR: DIR `" . $directory . "` is not a directory ===\n";
                    }
                    // bad request
                    http_response_code(400);
                    // write the error to the log and the body
                    $this->updateLog($error);
                    echo $error;
                }
            } else{
                $error = "=== ERROR: Pushed branch `" . $json["ref"] . "` does not match BRANCH `" . $branch . "` ===\n";
                // bad request
                http_response_code(400);
                // write the error to the log and the body
                $this->updateLog($error);
                echo $error;
            }
        }


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

    // function to forbid access
    public function forbid($file, $reason) {
        // format the error
        $error = "=== ERROR: " . $reason . " ===\n*** ACCESS DENIED ***\n";
        // forbid
        //http_response_code(403);
        $this->sendError('Not Authorized to make a request', 403);

        // write the error to the log and the body
        //fputs($file, $error . "\n\n");
        Storage::append($file, $error . "\n\n");
        
        echo $error;
        // close the log
        //fclose($file);
        // stop executing
        exit;
    }
}
