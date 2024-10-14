<?php
namespace controllers;
use core\conf;

class file {

    private $data, $con;
    public $storage;

    public function __construct($con, $id = null, $seed = null) {
        $this->con = $con;
        $this->storage = __FWDIR__."/files";
        $queryid = "";
        $queryseed = "";
        if($id != NULL) { $queryid = " WHERE id = :id"; }
        if($seed != NULL) { $queryseed = ($id != NULL ? " AND" : "")." ORDER BY RANDOM()"; }
        $query = $this->con->prepare("SELECT * FROM files ".$queryid.$queryseed);
        if($id != NULL) { $query->bindParam(":id", $id); }
        $query->execute();   

        if(isset($query)) {
        $this->data = $query->fetch(\PDO::FETCH_ASSOC);
        } else {
        $this->data = [];
        }
    }
    public function get_all_rand_p($page = 1, $perpage = 10, $seed = null, $queryy = "") {
      $seed = isset($seed) ? $seed : time();
      $offset = ($page - 1) * $perpage;
      if($queryy !== "" || $queryy !== NULL) {
        $wheres = "";
        $queries = ["filename" => $queryy, "description" => $queryy];
        foreach($queries as $key => $column) {
        $comma = " OR ";
        if(array_pop(array_keys($queries)) == $key) {
        $comma = "";
        }
        $wheres .= "LOWER($key) LIKE CONCAT('%', LOWER(:$key), '%')".$comma;
        }
      }
      $query = $this->con->prepare("SELECT *, createdat
      FROM files
      WHERE privacy = 0 AND ($wheres)
      ORDER BY RANDOM()
      LIMIT :perpage OFFSET :offset");
      if($queryy !== "" || $queryy !== NULL) {
        foreach($queries as $key => $column) {
        $query->bindParam(":".$key, $column);
        }
      }
      $query->bindParam(":perpage", $perpage, \PDO::PARAM_INT);
      $query->bindParam(":offset", $offset, \PDO::PARAM_INT);
      $query->execute();
  
      return ["pages" => $offset, "seed" => $seed, "files" => $query->fetchAll(\PDO::FETCH_ASSOC)];
  }
  public function get_all_p($page = 1, $perpage = 1, $sort, $order, $queryy = "") {
    $offset = ($page - 1) * $perpage;
    switch(true) {
      case $sort == "name":
        $sort = "filename";
        break;
      case $sort == "createdat":
        break;
      case $sort == "size":
        break;
      case $sort == "downloads":
        break;
      default:
        $sort = "createdat";
        break;
    }
    $order = (strtoupper($order) == "ASC") ? "ASC" : "DESC";
    if($queryy !== "" || $queryy !== NULL) {
      $wheres = "";
      $queries = ["filename" => $queryy, "description" => $queryy];
      foreach($queries as $key => $column) {
      $comma = " OR ";
      if(array_pop(array_keys($queries)) == $key) {
      $comma = "";
      }
      $wheres .= "LOWER($key) LIKE CONCAT('%', LOWER(:$key), '%')".$comma;
      }
    }
    $query = $this->con->prepare("SELECT *, createdat
    FROM files
    WHERE privacy = 0 AND ($wheres)
    ORDER BY $sort $order
    LIMIT :perpage OFFSET :offset");
    if($queryy !== "" || $queryy !== NULL) {
      foreach($queries as $key => $column) {
      $query->bindParam(":".$key, $column);
      }
    }
    $query->bindParam(":perpage", $perpage, \PDO::PARAM_INT);
    $query->bindParam(":offset", $offset, \PDO::PARAM_INT);
    $query->execute();

    return ["pages" => $offset, "sort" => $sort, "files" => $query->fetchAll(\PDO::FETCH_ASSOC)];
}
  

    public function genid($howmanytimeshasittried = 0) {
      $id = "";
      $characters = array_merge(range('A','Z'), range('a','z'), range('0','9'));
      $max = count($characters) - 1;
      for ($i = 0; $i < 32; $i++) {
        $rand = mt_rand(0, $max);
        $id .= $characters[$rand];
      }
      $query = $this->con->prepare("SELECT * FROM files WHERE id = :id");
      $query->bindParam(":id", $id);
      $query->execute();
      if(count($query->fetchAll()) >= 1) {
       if($howmanytimeshasittried >= 1028) {
        return; 
       }
       $this->genid($howmanytimeshasittried + 1);
      }
      return $id;
  }
  public function keygen($howmanytimeshasittried = 0) {
    $id = "";
    $characters = array_merge(range('A','Z'), range('a','z'), range('0','9'));
    $max = count($characters) - 1;
    for ($i = 0; $i < 64; $i++) {
      $rand = mt_rand(0, $max);
      $id .= $characters[$rand];
    }
    $query = $this->con->prepare("SELECT * FROM files WHERE id = :id");
    $query->bindParam(":id", $id);
    $query->execute();
    if(count($query->fetchAll()) >= 1) {
     if($howmanytimeshasittried >= 1028) {
      return; 
     }
     $this->genid($howmanytimeshasittried + 1);
    }
    return $id;
}

    public function upload($file, $description = "", $key = null, $privacy = 0) {
        $recentupload = $this->get_iflastuploadfromipisrecent();
        if($recentupload["result"]) {
        return ["response" => false, "text" => "Please wait ".$recentupload["wait"]." second(s)."];
        }

        $id = $this->genid();
        if($id == "") {
          return ["response" => false, "text" => "Looks like no possible ID is able to be generated, try again. If the issue persists, contact the website owner."];
        }
        if(empty($file["tmp_name"])) {
          return ["response" => false, "text" => "File isnt sent..?"];
        }
        $filename = $file["name"];
        $mimetype = mime_content_type($file["tmp_name"]);
        $size = filesize($file["tmp_name"]);
        switch(true) {
          case strlen($description) > 512:
            return ["response" => false, "text" => "Description is too long. (".strlen($description)."/512 characters)"];
          case strlen($key) > 1024:
            return ["response" => false, "text" => "Key is too long. (".strlen($description)."/1024 characters)"];
          case strlen("filename") > 256:
            return ["response" => false, "text" => "Filename is too long. (".strlen($description)."/256 characters)"];
          case $size > 100000000: // 100 mb
            return ["response" => false, "text" => "File is too large. (".int_shorten($size, "1")."/100MB) [$size]"];
        }
        $filedir = $this->storage."/".$id;
        if(!is_uploaded_file($file["tmp_name"])) {
          return ["response" => false, "text" => "No file sent?"];
        }
        if(!is_dir($filedir)) {
          $old = umask(0);
          mkdir($filedir, 0777, true);
          umask($old);
        } else {
          return ["response" => false, "text" => "Folder already exists???"];
        }
        if(!copy($file["tmp_name"], $filedir."/file")) {
          return ["response" => false, "text" => "Failed uploading to storage."];
        }
        foreach(glob($filedir."/*") as $file) {
          $old = umask(0);
          chmod($file, 0777);
          umask($old);
        }

        $time = time();
        $identity = ipua2identifier();
        if(empty($key)) {
        $key = $this->keygen();
        }
        $filedirrelative = str_replace(__FWDIR__, "__FWDIR__", $filedir."/file");
        $query = $this->con->prepare("INSERT INTO files (id, file, description,filename, mimetype, size, key, identity, privacy, createdat)
        VALUES (:id, :file, :description, :filename, :mimetype, :size, :key, :identity, :privacy, :timee)");
        $query->bindParam(":id", $id);
        $query->bindParam(":file", $filedirrelative);
        $query->bindParam(":description", $description);
        $query->bindParam(":filename", $filename);
        $query->bindParam(":mimetype", $mimetype);
        $query->bindParam(":size", $size);
        $query->bindParam(":identity", $identity);
        $query->bindParam(":key", $key);
        $query->bindParam(":privacy", $privacy);
        $query->bindParam(":timee", $time);
        $query->execute(); 
        return ["response" => true, "text" => "Complete!", "result" => ["id" => $id, "key" => $key]];
    }
    public function exists() {
      return $this->data == null ? false : true;
    }

    public function get_id() {
      return $this->data["id"];
    }

    public function get_file() {
      return str_replace("__FWDIR__", __FWDIR__, $this->data["file"]);
    }

    public function get_description() {
      return $this->data["description"];
    }

    public function get_filename() {
      return $this->data["filename"];
    }

    public function get_mimetype() {
      return $this->data["mimetype"];
    }

    public function get_size() {
      return $this->data["size"];
    }

    public function get_identity() {
      return $this->data["identity"];
    }

    public function get_privacy() {
      return $this->data["privacy"];
    }
    public function get_createdat() {
      return $this->data["createdat"];
    }

    public function get_icon() {
      $ext = explode(".", $this->get_filename());
      $mime = $this->get_mimetype();
      $ext = end($ext); // this makes sure it gets the last last one
      return mimetype2icon($mime, $ext);
    }

    public function get_iflastuploadfromipisrecent() { //lmao
      $identity = ipua2identifier();
      $query = $this->con->prepare("SELECT createdat FROM files WHERE identity = :identity ORDER BY createdat DESC");
      $query->bindParam(":identity", $identity);
      $query->execute();
      $result = $query->fetch(\PDO::FETCH_ASSOC);
      $result = $result['createdat'];
      $time = time();
      $isrecent = $time - $result;
      $wait = 15;
      if ($isrecent >= $wait) {
        return ["result" => false, "wait" => 0];
      } else {
        return ["result" => true, "wait" => $wait - ($time - $result)];
      }
    }

    public function count_download() {
      $query = $this->con->prepare("UPDATE files SET downloads = downloads + 1 WHERE id = :id");
      $query->bindParam(":id", $this->data["id"]);
      $query->execute();
      return true;
    }

    public function get_downloads() {
      return $this->data["downloads"];
    }

    public function get_totalsize() {
      $query = $this->con->prepare("SELECT sum(size) as total_size FROM files");
      $query->execute();
      $result = $query->fetch(\PDO::FETCH_ASSOC);
      return $result['total_size'] ?? 0;
    }
    public function get_count($privacy = 0) {
      $query = $this->con->prepare("SELECT count(*) as total_count FROM files WHERE privacy = :privacy");
      $query->bindParam(":privacy", $privacy, \PDO::PARAM_INT);
      $query->execute();
      $result = $query->fetch(\PDO::FETCH_ASSOC);
    
      return $result['total_count'] ?? 0;
    } 
}
