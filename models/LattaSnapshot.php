<?php

namespace LattaAi\Reporter\Models;
use Exception;

class LattaSnapshot
{
    private $id;
    private $message;
    private $relation_id;
    private $related_to_relation_id;

    public function __construct($id, $message = null, $relation_id = null, $related_to_relation_id = null)
    {
        $this->id = $id;
        $this->message = $message;
        $this->relation_id = $relation_id;
        $this->related_to_relation_id = $related_to_relation_id;
    }

    public function getId(): string {
        return $this->id;
    }
}