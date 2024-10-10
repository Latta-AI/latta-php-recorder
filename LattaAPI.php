<?php

namespace LattaAi\Reporter;
use Exception;
use LattaAi\LattaLaravelReporter\Models\LattaInstance;
use LattaAi\LattaLaravelReporter\Models\LattaSnapshot;

class LattaAPI {

    private $apiKey;

    public function __construct($apiKey)
    {
        $this->apiKey = $apiKey;
    }

    public function putInstance($framework, $framework_version, $os, $lang, $device)
    {
        $data = [
            "framework" => $framework,
            "framework_version" => $framework_version,
            "os" => $os,
            "lang" => $lang,
            "device" => $device
        ];

        $ch = curl_init("https://recording.latta.ai/v1/instance/backend");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $this->apiKey
        ]);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));

        $response = curl_exec($ch);

        if ($response === false) {
            $error = curl_error($ch);
            curl_close($ch);
            throw new Exception("Curl request failed: " . $error);
        }

        curl_close($ch);

        var_dump($response);

        $decodedResponse = json_decode($response);

        $instance = new LattaInstance($decodedResponse->id);
        return $instance;
    }

    public function putSnapshot(LattaInstance $instance, $message, $relation_id = null, $related_to_relation_id = null)
    {
        $data = [
            "message" => $message,
            "relation_id" => $relation_id,
            "related_to_relation_id" => $related_to_relation_id
        ];

        $ch = curl_init("https://recording.latta.ai/v1/snapshot/".$instance->getId());
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $this->apiKey
        ]);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));

        $response = curl_exec($ch);

        if ($response === false) {
            $error = curl_error($ch);
            curl_close($ch);
            throw new Exception("Curl request failed: " . $error);
        }

        curl_close($ch);

        $decodedResponse = json_decode($response);

        $snapshot = new LattaSnapshot($decodedResponse->id);
        return $snapshot;
    }

    public function putAttachment(LattaSnapshot $snapshot, $attachment)
    {
        $ch = curl_init("https://recording.latta.ai/v1/snapshot/".$snapshot->getId()."/attachment");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $this->apiKey
        ]);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($attachment));

        $response = curl_exec($ch);

        if ($response === false) {
            $error = curl_error($ch);
            curl_close($ch);
            throw new Exception("Curl request failed: " . $error);
        }

        curl_close($ch);
    }

}