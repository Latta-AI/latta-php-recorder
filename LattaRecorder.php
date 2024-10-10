<?php

namespace LattaAi\Recorder;

use ErrorException;
use LattaAi\Recorder\Models\LattaInstance;

class LattaRecorder
{
    private $api;
    public static $logs = [];
    public static $relationID;

    public function __construct($apiKey) {
        $this->api = new LattaAPI($apiKey);
    }

    public function startRecording($framework, $framework_version, $os, $lang, $device)
    {
        ob_start();

        if (!file_exists("latta-instance.txt")) {
            $lattaInstance = $this->api->putInstance($framework, $framework_version, $os, $lang, $device);
            file_put_contents("latta-instance.txt", $lattaInstance->getId());
        }

        $this->relationID = isset($_COOKIE["Latta-Recording-Relation-Id"]) ? $_COOKIE["Latta-Recording-Relation-Id"] :
            (isset($_SERVER['HTTP_LATTA_RECORDING_RELATION_ID']) ? $_SERVER['HTTP_LATTA_RELATION_ID'] : null);

        if ($this->relationID == null) {
            $this->relationID = LattaUtils::uuidv4();
            setcookie("Latta-Recording-Relation-Id", $this->relationID, time() + (10 * 365 * 24 * 60 * 60), "/");
        }

        $thisObj = $this;

        set_error_handler(function($severity, $message, $file, $line) use ($thisObj)
        {
            if (!(error_reporting() & $severity)) {
                return false;
            }

            $message = htmlspecialchars($message)." at ".$file.":".$line;

            switch ($severity) {
                case E_USER_ERROR:
                    $lattaInstance = new LattaInstance($_COOKIE["Latta-Recording-Relation-Id"]);
                    $lattaSnapshot = $thisObj->api->putSnapshot($lattaInstance, "", null, LattaRecorder::$relationID);

                    $exception = new ErrorException($message, 0, $severity, $file, $line);
                    $attachment = new LattaAttachment($exception, LattaRecorder::$logs);

                    $thisObj->api->putAttachment($lattaSnapshot, $attachment);
                    exit(1);
                case E_USER_WARNING:
                    array_push(LattaRecorder::$logs, ["level" => "WARN", "message" => $message, "timestamp" => time()]);
                    break;
            
                case E_USER_NOTICE:
                    array_push(LattaRecorder::$logs, ["level" => "INFO", "message" => $message, "timestamp" => time()]);
                    break;
            }

            return true;
        });
    }
}