<?php
namespace HumanRelation\Entities\Base;

abstract class TestSequence {

    /** @var int  */
    protected $id;
    /** @var string  */
    protected $text;

    public function getId() {
        return $this->id;
    }

    public function setId($id) {
        $this->id = $id;
    }

    public function getText() {
        return $this->text;
    }

    public function setText($text) {
        $this->text = $text;
    }
}