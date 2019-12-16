<?php

namespace App\Http\Controllers;

use App\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class WebhookController extends BaseController
{
    public function index(Request $request){

        $content = file_get_contents('php://input');

        $this->validateData($content);
    }

    public function validateData($content){

        $startTime = microtime(true);
        
        $token = false;
        
        $json = json_decode($content, true); 
        
        $repository = (isset($json["repository"])) ? $json["repository"] : "";
        
        $repository_name = ($repository) ? $repository["full_name"] : "";

        $message = "";

        $file = 'public/' . $repository_name . '_deploy.log';

        if(Storage::exists($file))
            Storage::delete($file);

        $this->updateLog($file, date("d-m-Y (H:i:s)", time()) . "\n");
        
        echo "CHECKING Repository " . $repository_name . " IN Database";

        $project = Project::where('name', $repository_name)->first();

        $directory = "";
        $branch = "";
        $afterPull = "";
        $beforePull = "";
        $secretToken = "";

        if(!$project){
            $this->serverError($file, "Repo not found in your Database", 404);
        }else{
            if(!$project->status){
                $this->serverError($file, "Repo not set to active in your Database", 500);
            }

            //project exists in our database
            $directory = $project->path;
            $branch = "refs/heads/" . $project->branch;

            $afterPull = $project->pre_hook;
            $beforePull = $project->post_hook;

            $secretToken = $project->secret;

            //$project->email_result;
        }

        $project->update([
                            'last_hook_time' => date("d-m-Y (H:i:s)", time()),
                            'last_hook_status' => 0
                        ]);
        
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
        $output = "";
        $error = "";

        // Check for a GitHub signature
        if (!empty($secretToken) && isset($_SERVER["HTTP_X_HUB_SIGNATURE"]) && $token !== hash_hmac($algo, $content, $secretToken)) {
            $message = "X-Hub-Signature does not match TOKEN";
            $this->updateLog($file, $message);
            $this->forbid($file, $message);
        // Check for a GitLab token
        } elseif (!empty($secretToken) && isset($_SERVER["HTTP_X_GITLAB_TOKEN"]) && $token !== $secretToken) {
            $message = "X-GitLab-Token does not match TOKEN";
            $this->updateLog($file, $message);
            $this->forbid($file, $message);
        // Check for a $_GET token
        } elseif (!empty($secretToken) && isset($_GET["token"]) && $token !== $secretToken) {
            $message = "\$_GET[\"token\"] does not match TOKEN";
            $this->updateLog($file, $message);
            $this->forbid($file, $message);
        // if none of the above match, but a token exists, exit
        } elseif (!empty($secretToken) && !isset($_SERVER["HTTP_X_HUB_SIGNATURE"]) && !isset($_SERVER["HTTP_X_GITLAB_TOKEN"]) && !isset($_GET["token"])) {
            $message = "No token detected";
            $this->updateLog($file, $message);
            $this->forbid($file, $message);
        } else {

            // check if pushed branch matches branch specified in config
            if ($json["ref"] === $branch) {
                $this->updateLog($file, $content . PHP_EOL);
                // ensure directory is a repository
                if (file_exists($DIR . ".git") && is_dir($DIR)) {
                    // change directory to the repository
                    chdir($DIR);
                    // write to the log
                    $this->updateLog($file, "*** AUTO PULL INITIATED ***" . "\n");
                    /**
                     * Attempt to reset specific hash if specified
                     */
                    if (!empty($_GET["reset"]) && $_GET["reset"] === "true") {
                        // write to the log
                        $this->updateLog($file, "*** RESET TO HEAD INITIATED ***" . "\n");
                        exec($git . " reset --hard HEAD 2>&1", $output, $exit);
                        // reformat the output as a string
                        $output = (!empty($output) ? implode("\n", $output) : "[no output]") . "\n";
                        // if an error occurred, return 500 and log the error
                        if ($exit !== 0) {
                            $output = "=== ERROR: Reset to head failed using GIT `" . $git . "` ===\n" . $output;
                            $this->updateLog($file, $output);
                            $this->serverError($file, $output, 500);
                        }
                        // write the output to the log and the body
                        $this->updateLog($file, $output);
                        echo $output;
                    }
                    /**
                     * Attempt to execute BEFORE_PULL if specified
                     */
                    if (!empty($beforePull)) {
                        // write to the log
                        $this->updateLog($file, "*** BEFORE_PULL INITIATED ***" . "\n");
                        // execute the command, returning the output and exit code
                        exec($beforePull . " 2>&1", $output, $exit);
                        // reformat the output as a string
                        $output = (!empty($output) ? implode("\n", $output) : "[no output]") . "\n";
                        // if an error occurred, return 500 and log the error
                        if ($exit !== 0) {
                            $output = "=== ERROR: BEFORE_PULL `" . $beforePull . "` failed ===\n" . $output;
                            $this->updateLog($file, $output);
                            $this->serverError($file, $output, 500);
                        }
                        // write the output to the log and the body
                        $this->updateLog($file, $output);
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
                        $output = "=== ERROR: Pull failed using GIT `" . $git . "` and DIR `" . $directory . "` ===\n" . $output;
                        $this->updateLog($file, $output);
                        $this->serverError($file, $output, 500);
                    }
                    // write the output to the log and the body
                    $this->updateLog($file, $output);
                    echo $output;
                    /**
                     * Attempt to checkout specific hash if specified
                     */
                    if (!empty($sha)) {
                        // write to the log
                        $this->updateLog($file, "*** RESET TO HASH INITIATED ***" . "\n");
                        exec($git . " reset --hard {$sha} 2>&1", $output, $exit);
                        // reformat the output as a string
                        $output = (!empty($output) ? implode("\n", $output) : "[no output]") . "\n";
                        // if an error occurred, return 500 and log the error
                        if ($exit !== 0) {
                            $output = "=== ERROR: Reset failed using GIT `" . $git . "` and \$sha `" . $sha . "` ===\n" . $output;
                            $this->updateLog($file, $output);
                            $this->serverError($file, $output, 500);
                        }
                        // write the output to the log and the body
                        $this->updateLog($file, $output);
                        echo $output;
                    }
                    /**
                     * Attempt to execute $afterPull if specified
                     */
                    if (!empty($afterPull)) {
                        // write to the log
                        $this->updateLog($file, "*** AFTER_PULL INITIATED ***" . "\n");
                        // execute the command, returning the output and exit code
                        exec($afterPull . " 2>&1", $output, $exit);
                        // reformat the output as a string
                        $output = (!empty($output) ? implode("\n", $output) : "[no output]") . "\n";
                        // if an error occurred, return 500 and log the error
                        if ($exit !== 0) {
                            $output = "=== ERROR: AFTER_PULL `" . $afterPull . "` failed ===\n" . $output;
                            $this->updateLog($file, $output);
                            $this->serverError($file, $output, 500);
                        }
                        // write the output to the log and the body
                        $this->updateLog($file, $output);
                        echo $output;
                    }
                    // write to the log
                    $this->updateLog($file, "*** AUTO PULL COMPLETE ***" . "\n");
                } else {
                    // prepare the generic error
                    $error = "=== ERROR: DIR `" . $directory . "` is not a repository ===\n";
                    // try to detemrine the real error
                    if (!file_exists($directory)) {
                        $error = "=== ERROR: DIR `" . $directory . "` does not exist ===\n";
                    } elseif (!is_dir($directory)) {
                        $error = "=== ERROR: DIR `" . $directory . "` is not a directory ===\n";
                    }
                    // write the error to the log and the body
                    $this->updateLog($file, $error);
                    // bad request
                    $this->serverError($file, $error, 400);
                    echo $error;
                }
            } else{
                $error = "=== ERROR: Pushed branch `" . $json["ref"] . "` does not match BRANCH `" . $branch . "` ===\n";
                // write the error to the log and the body
                $this->updateLog($file, $error);
                // bad request
                $this->serverError($file, $error, 400);
                echo $error;
            }
        }

        $endTime = microtime(true);

        $project->update([
                            'last_hook_duration' => number_format($endTime - $startTime, 2),
                            'last_hook_time' => date("d-m-Y (H:i:s)", time()),
                            'last_hook_status' => 1
                        ]);

        $message = "Repo <repo-name> - <branch-name> synced in " .number_format($endTime - $startTime, 2) . "seconds";
            
        $this->updateLog($file, $message);

        return $this->sendResponse($message);
    }

    public function updateLog($file, $msg = ""){

        $content = "===================";
        $content .= $msg;
        $content .= "===================";

        Storage::append($file, $content);
    }

    // function to forbid access
    public function forbid($file, $reason) {
        // format the error
        $error = "=== ERROR: " . $reason . " ===\n*** ACCESS DENIED ***\n";
        // forbid
        http_response_code(403);
        //$this->sendError('Not Authorized to make a request', 403);

        // write the error to the log and the body
        Storage::append($file, $error . "\n\n");
        
        echo $error;
        $this->sendError('Not Authorized to make a request', 403);
        exit;
    }

    // function to forbid access
    public function serverError($file, $error, $code = 404) {
        // format the error
        //$error = "=== ERROR: " . $reason . " ===\n*** ACCESS DENIED ***\n";
        // forbid
        http_response_code($code);

        // write the error to the log and the body
        Storage::append($file, $error . "\n\n");
        
        echo $error;
        $this->sendError($error, $code);
        exit;
    }
}
