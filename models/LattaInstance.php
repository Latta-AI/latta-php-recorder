<?php

namespace LattaAi\Recorder\models;

class LattaInstance
{

    private $id;
    private $framework;
    private $framework_version;
    private $os;
    private $lang;
    private $device;

    public function __construct($id, $framework = null, $framework_version = null, $os = null, $lang = null, $device = null)
    {
        $this->id = $id;
        $this->framework = $framework;
        $this->framework_version = $framework_version;
        $this->os = $os;
        $this->lang = $lang;
        $this->device = $device;
    }

    public function getId() {
        return $this->id;
    }
}