<?php
namespace controllers;
use core\conf;

class ban {

    private $data, $con;

    public function __construct($con, $id) {
        $this->con = $con;

        if(is_int($id)) {
        $query = $this->con->prepare("SELECT * FROM bans WHERE id = :id");
        $query->bindParam(":id", $id, \PDO::PARAM_INT);
        $query->execute();
        } else {
        $query = $this->con->prepare("SELECT * FROM bans WHERE identity = :id");
        $query->bindParam(":id", $id);
        $query->execute();
        }   

        if(isset($query)) {
        $this->data = $query->fetch(\PDO::FETCH_ASSOC);
        } else {
        $this->data = [];
        }
    }


    public function ban($identity, $reason, $permanent = false, $offendingitem, $length) {
      switch(true) {
        case strlen($identity) <= 1:
          return ["response" => false, "text" => "No identity sent."];
        case strlen($reason) <= 1:
          return ["response" => false, "text" => "No reason sent. (512 chars)"];
        case !is_bool($permanent):
          return ["response" => false, "text" => "Permanent parameter is not a boolean. (true or false)"];
      }

      $time = time();
      $permanent = !$permanent ? 0 : 1;
      $query = $this->con->prepare("INSERT INTO bans (identity, reason, permanent, offendingitem, length, createdat)
      VALUES (:identity, :reason, :permanent, :offendingitem, :length, :timee)");
      $query->bindParam(":identity", $identity);
      $query->bindParam(":reason", $reason);
      $query->bindParam(":permanent", $permanent);
      $query->bindParam(":offendingitem", $offendingitem);
      $query->bindParam(":length", $length);
      $query->bindParam(":timee", $time);
      $query->execute(); 
      return ["response" => true, "text" => "Complete!"];
  }

    public function exists() {
      return $this->data == null ? false : true;
    }

    public function get_id() {
      return $this->data["id"];
    }

    public function get_identity() {
      return $this->data["identity"];
    }

    public function get_reason() {
      return $this->data["reason"];
    }

    public function is_permanent() {
      return $this->data["permanent"] == 0 ? false : true;
    }

    public function get_offendingitem() {
      return $this->data["offendingitem"];
    }
    public function get_length() {
      return $this->data["length"];
    }

    public function get_createdat() {
      return $this->data["createdat"];
    }
}
