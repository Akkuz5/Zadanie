<?php

class Contact {
    public $id;
    public $name;
    public $phone_number;

    public function __construct($id, $name, $phone_number) {
        $this->id = $id;
        $this->name = $name;
        $this->phone_number = $phone_number;
    }

    public function getId() {
        return $this->id;
    }

    public function setId($id) {
        $this->id = $id;
    }

    public function getName() {
        return $this->name;
    }

    public function setName($name) {
        $this->name = $name;
    }

    public function getPhoneNumber() {
        return $this->phone_number;
    }

    public function setPhoneNumber($phone_number) {
        $this->phone_number = $phone_number;
    }
}
?>