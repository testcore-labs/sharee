<?php
namespace controllers;
use controllers\file;
use core\conf;

class comment {

    private $data, $con;

    public function __construct($con, $id) {
        $this->con = $con;

        $query = $this->con->prepare("SELECT * FROM comments WHERE id = :id");
        $query->bindParam(":id", $id, \PDO::PARAM_INT);
        $query->execute();   

        if(isset($query)) {
        $this->data = $query->fetch(\PDO::FETCH_ASSOC);
        } else {
        $this->data = [];
        }
    }

    public function get_all_p($fileid, $page = 1, $perpage = 1, $sort, $order, $queryy = "") {
      $file = new file($this->con, $fileid);
      if(!$file->exists()) {
        return false;
      }
      $offset = ($page - 1) * $perpage;
      switch(true) {
        case $sort == "createdat":
          break;
        case $sort == "comment":
          break;
        default:
          $sort = "createdat";
          break;
      }
      $order = (strtoupper($order) == "ASC") ? "ASC" : "DESC";
      if($queryy !== "" || $queryy !== NULL) {
        $wheres = "";
        $queries = ["comment" => $queryy];
        foreach($queries as $key => $column) {
        $comma = " OR ";
        if(array_pop(array_keys($queries)) == $key) {
        $comma = "";
        }
        $wheres .= "LOWER($key) LIKE CONCAT('%', LOWER(:$key), '%')".$comma;
        }
      }
      $query = $this->con->prepare("SELECT *, createdat
      FROM comments
      WHERE fileid = :fileid AND $wheres
      ORDER BY $sort $order
      LIMIT :perpage OFFSET :offset");
      if($queryy !== "" || $queryy !== NULL) {
        foreach($queries as $key => $column) {
        $query->bindParam(":".$key, $column);
        }
      }
      $query->bindParam(":fileid", $fileid, \PDO::PARAM_STR);
      $query->bindParam(":perpage", $perpage, \PDO::PARAM_INT);
      $query->bindParam(":offset", $offset, \PDO::PARAM_INT);
      $query->execute();
  
      return ["pages" => $offset, "sort" => $sort, "comments" => $query->fetchAll(\PDO::FETCH_ASSOC)];
    }

    public function comment($fileid, $comment) {
      $recentupload = $this->get_iflastcommentfromipisrecent();
      if($recentupload["result"]) {
      return ["response" => false, "text" => "Please wait ".$recentupload["wait"]." second(s)."];
      }

      $file = new file($this->con, $fileid);
      switch(true) {
        case !$file->exists():
          return ["response" => false, "text" => "File doesn't exist."];
        case strlen($comment) > 128:
          return ["response" => false, "text" => "Comment is too long. (".strlen($comment)."/128 characters)"];
        case strlen($comment) <= 1:
          return ["response" => false, "text" => "Comment is too short. (2 chars or more)"];
      }
      $fileid = $file->get_id();

      $time = time();
      $identity = ipua2identifier();
      $query = $this->con->prepare("INSERT INTO comments (fileid, comment, identity, createdat)
      VALUES (:fileid, :comment, :identity, :timee)");
      $query->bindParam(":fileid", $fileid);
      $query->bindParam(":comment", $comment);
      $query->bindParam(":identity", $identity);
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

    public function get_fileid() {
      return str_replace("__FWDIR__", __FWDIR__, $this->data["file"]);
    }

    public function get_comment() {
      return $this->data["comment"];
    }

    public function get_identity() {
      return $this->data["identity"];
    }

    public function get_createdat() {
      return $this->data["createdat"];
    }

    public function get_iflastcommentfromipisrecent() { //lmao
      $identity = ipua2identifier();
      $query = $this->con->prepare("SELECT createdat FROM comments WHERE identity = :identity ORDER BY createdat DESC");
      $query->bindParam(":identity", $identity);
      $query->execute();
      $result = $query->fetch(\PDO::FETCH_ASSOC);
      $result = $result['createdat'];
      $time = time();
      $isrecent = $time - $result;
      $wait = 5;
      if ($isrecent >= $wait) {
        return ["result" => false, "wait" => 0];
      } else {
        return ["result" => true, "wait" => $wait - ($time - $result)];
      }
    }

    public function get_count($fileid) {
      $query = $this->con->prepare("SELECT count(*) as total_count FROM comments WHERE fileid = :fileid");
      $query->bindParam(":fileid", $fileid);
      $query->execute();
      $result = $query->fetch(\PDO::FETCH_ASSOC);
    
      return $result['total_count'] ?? 0;
    } 
}
