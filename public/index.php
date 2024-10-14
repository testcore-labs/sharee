<?php
require(dirname(__DIR__).'/core/bootstrap.php');
use core\route;
use core\conf;
use core\modules\twig;
use controllers\ban;
use controllers\file;
use controllers\comment;
$con;
$discord = "pHPBy8FvAY";
$storage = __FWDIR__."/files";
$filee = new file($con, 0);
twig::$variables["filesuploaded"] = int_shorten($filee->get_count(), 0);
twig::$variables["filesuploadedprivate"] = int_shorten($filee->get_count(1), 0);
twig::$variables["diskused"] = int_shorten_size($filee->get_totalsize(), 1);
twig::$classes["con"] = $con;
twig::$variables["motd"] = conf::get()["project"]["motd"];
route::$r404 = function() {
  return twig::view("404");
};

$banned = new ban($con, ipua2identifier());
twig::$classes["banned"] = $banned;
route::addcategory("front", function() use ($banned) {
if($banned->exists() && $banned->is_permanent()) {
  echo twig::view("banned", ["ban" => $banned]);
  die();
}
});

route::addcategory("api", function() use ($banned) {
if($banned->exists() && !$banned->is_permanent() && $banned->get_length() >= time()) {
  header("Content-Type: application/json");
  echo json_encode(["response" => false, "text" => "BANNED!"]);
  die();
}
});

route::addcategory("api-strict", function() use ($banned) {
if($banned->exists() && $banned->is_permanent()) {
  header("Content-Type: application/json");
  echo json_encode(["response" => false, "text" => "BANNED!"]);
  die();
}
});

route::get("/", function() {
  return twig::view("index");
}, [], ["category" => ["front"]]);

route::get("/winks", function() {
  $emojis = [
    ":nerd:" => "<span class=\"winks winks-$size nerd\" title=\":nerd:\"></span>",
    ":sarcastic:" => "<span class=\"winks winks-$size sarcastic\" title=\":sarcastic:\"></span>",
    ":secret-telling:" => "<span class=\"winks winks-$size secret-telling\" title=\":secret_telling:\"></span>",
    ":sick:" => "<span class=\"winks winks-$size sick\" title=\":sick:\"></span>",
    ":idontknow:" => "<span class=\"winks winks-$size idontknow\" title=\":idk:\"></span>",
    ":angry:" => "<span class=\"winks winks-$size angry\" title=\":angry:\"></span>",
    ":lookingup:" => "<span class=\"winks winks-$size lookingup\" title=\":lookingup:\"></span>",
    ":party:" => "<span class=\"winks winks-$size party\" title=\":party:\"></span>",
    ":disappointed:" => "<span class=\"winks winks-$size disappointed\" title=\":disappointed:\"></span>",
    ":angel:" => "<span class=\"winks winks-$size angel\" title=\":angel:\"></span>",
    ":woozy:" => "<span class=\"winks winks-$size woozy\" title=\":woozy:\"></span>",
    ":nuhuh:" => "<span class=\"winks winks-$size nuhuh\" title=\":nuhuh:\"></span>",
    ":devil:" => "<span class=\"winks winks-$size devil\" title=\":devil:\"></span>",
    ":smooch:" => "<span class=\"winks winks-$size smooch\" title=\":smooch:\"></span>",
    ":surprised:" => "<span class=\"winks winks-$size surprised\" title=\":surprised:\"></span>",
    ":blush:" => "<span class=\"winks winks-$size blush\" title=\":blush:\"></span>",
    ":excited:" => "<span class=\"winks winks-$size excited\" title=\":excited:\"></span>",
    ":sad:" => "<span class=\"winks winks-$size sad\" title=\":sad:\"></span>",
    ":sunglasses:" => "<span class=\"winks winks-$size sunglasses\" title=\":sunglasses:\"></span>",
    ":happy:" => "<span class=\"winks winks-$size happy\" title=\":happy:\"></span>",
    ":hungry:" => "<span class=\"winks winks-$size hungry\" title=\":hungry:\"></span>",
    ":shocked:" => "<span class=\"winks winks-$size shocked\" title=\":shocked:\"></span>",
    ":wink:" => "<span class=\"winks winks-$size wink\" title=\":wink:\"></span>",
    ":shush:" => "<span class=\"winks winks-$size shush\" title=\":shush:\"></span>",
    ":anger:" => "<span class=\"winks winks-$size anger\" title=\":anger:\"></span>",
    //custom
    ":qzip:" => "<span class=\"winks winks-$size qzip\" title=\":qzip:\"></span>",
    ":sharee:" => "<span class=\"winks winks-$size sharee\" title=\":sharee:\"></span>",
    ":uwu:" => "<span class=\"winks winks-$size uwu\" title=\":uwu:\"></span>",
  ];
  return twig::view("winkers", ["emojis" => $emojis]);
}, [], ["category" => ["front"]]);

route::get("/winker-{test}", function($test) {
  $winker = emojitohtml($test);
  return twig::view("winkers", ["winker" => $winker]);
}, [], ["category" => ["front"]]);

route::get("/kofi", function() {
  return header("Location: //ko-fi.com/testcorelabs");
}, [], ["category" => ["front"]]);

route::get("/files", function() use ($con) {
  $page = isset($_REQUEST["p"]) ? (int)$_REQUEST["p"] : 1;
  $perpage = isset($_REQUEST["perpage"]) ? $_REQUEST["perpage"] : 20;
  if((isset($_REQUEST["rand"]) || isset($_REQUEST["seed"])) && !isset($_REQUEST["sort"])) {
    $seed = isset($_REQUEST["seed"]) && strlen((int)$_REQUEST["seed"]) > 0 ? (int)$_REQUEST["seed"] : null;
    $files = (new file($con, 0))->get_all_rand_p($page, $perpage, $seed, (strlen((string)$_REQUEST["q"]) > 0 ? (string)$_REQUEST["q"] : ""));
    if($seed != $files["seed"]) {
      header("Location: ?seed=".$files["seed"]);
    }
  } else {
    $files = (new file($con, 0))->get_all_p($page, $perpage, (string)$_REQUEST["sort"], (string)$_REQUEST["order"], (strlen((string)$_REQUEST["q"]) > 0 ? (string)$_REQUEST["q"] : ""));
  }
  $filesobj = $files["files"];
  foreach($filesobj as $key => $file) {
    if(isset($filesobj[$key]["key"])) {
      unset($filesobj[$key]["key"]);
    }
    if(isset($filesobj[$key]["file"])) {
      unset($filesobj[$key]["file"]);
    }
  }
  $count = (new file($con, 0))->get_count(0);
  $totalpages = ceil($count / $perpage);
  $obj = ["files" => $filesobj, "totalpages" => $totalpages, "perpage" => $perpage, "count" => $count, "page" => $page, "seed" => (int)$files["seed"]];
  if(isset($_REQUEST["json"])) {
    header("Content-Type: application/json");
    return json_encode($obj);
  } else {
    return twig::view("files", $obj);
  }
}, [], ["category" => ["front"]]);

route::get("/tos", function() {
  return twig::view("tos");
}, [], ["category" => ["front"]]);

route::get("/dsc", function() use ($discord) {
  return header("Location: //discord.gg/$discord");
});

route::get("/privacypolicy", function() {
  return twig::view("privacypolicy");
}, [], ["category" => ["front"]]);

route::get("/ask", function() {
  return twig::view("ask");
}, [], ["category" => ["front"]]);

route::get("/api/ask", function() {
$content = "--- INSTRUCTIONS ---
you cannot be proffesional,
your name is `Sheeves`, you are a Windows 98 computer
You will have to output your current generated emotion at the start formatted as e:(SAD) for sad, e:(MAD) for mad, e:(HAPPY) for happy and e:(THINK) for thinking, your emotion must be the first line with no other spaces before or after, newline for the content you generated.
--- END INSTRUCTIONS ---
".(string)$_REQUEST["content"];
$json_data = json_encode(array(
    "messages" => array(
        array("role" => "user", "content" => $content)
    ),
    "model" => "gpt-4"
));

$api_url = 'http://gpt4.discord.rocks/ask';
$headers = array(
    'Content-Type: application/json',
    'Accept: application/json'
);

$context_options = array(
    'http' => array(
        'method' => 'POST',
        'header' => implode("\r\n", $headers),
        'content' => $json_data
    )
);

$context = stream_context_create($context_options);
$response = file_get_contents($api_url, false, $context);
if(str_contains(__UA__, "Discord")) {
  $content = nxss(truncate((string)$_REQUEST["content"], 100, "…"));
  $response = json_decode($response, true)["response"] ?? "";
  $response = nxss(truncate($response, 500, "…"));
  return "<meta property=\"og:site_name\" content=\"ChatGPT 4\">
  <meta property=\"og:image\" content=\"/assets/img/chatgpt.png\" />
  <meta content='\"$content\"' property=\"og:title\">
  <meta content=\"$response\" property=\"og:description\">
  <meta name=\"theme-color\" content=\"#a96cf9\">";
}
if(isset($_REQUEST["html"])) {
  $response = json_decode($response, true)["response"] ?? "";
  $response = nxss($response);
  $response = preg_replace('~```(.+)```~s', "<code style=\"width: 100%;\"><pre style=\"width: calc(100% - 4em); box-sizing: border-box; overflow: auto; max-height: 8em;\">".str_replace("<br>", "\n", '$1')."</pre></code>", $response);
  $lines = explode("\n", $response);
  $img = "sheeves1.png";
  switch(trim($lines[0])) {
    case "e:(SAD)":
      $img = "sheeves-sad.png";
      break;
    case "e:(MAD)":
      $img = "sheeves-mad.png";
      break;
    case "e:(HAPPY)":
      $img = "sheeves1.png";
      break;
    case "e:(THINK)":
      $img = "sheeves-think.png";
      break;
    default:
      $img = "sheeves1.png";
      break;
  }
  unset($lines[0]);
  if(trim($lines[1]) == "\n") {
    unset($lines[1]);
  }
  $response = implode("\n", $lines);
  $response = nl2br($response);
  return <<<EOF
  <div style="display: flex; flex-direction: row; gap: 0.25em;">
  <div style="position: relative; align-self: baseline;">
    <img src="/assets/img/$img" style="image-rendering: pixelated;" width="48">
    <img height=48 class="htmx-indicator hourglass" style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); image-rendering: pixelated;" src="/assets/img/loading.gif">
  </div>
  <div style="display: flex; flex-direction: column; gap: 0.25em; padding: 0.25em; width: 100%; box-sizing: border-box;"><b style="margin: 0; font-size: 1.33em;">Sheeves [BETA]</b><div style="max-width: 100%; width: 100%; white-space: normal; word-break: break-all !important; word-wrap: break-word !important; overflow-wrap:break-word; overflow: auto;">$response</div></div>
  </div>
EOF;
}
if(isset($_REQUEST["plaintext"])) {
  return json_decode($response, true)["response"] ?? "";
} else {
  header("Content-Type: application/json");
  return $response;
}
}, [], ["category" => []]);


route::get("/f/{id}", function($id) use ($con) {
  $file = new file($con, $id);
  if(!$file->exists()) {    
    return twig::view("404");
  }
  $isbanned = new ban($con, $file->get_identity());
  if($isbanned->exists() && $isbanned->is_permanent() && !isset($_REQUEST["bypass"])) {
    return twig::view("404");
  }
  $ext = explode(".", $file->get_filename());
  $ext = end($ext); // this makes sure it gets the last last one
  $mimetype2icon = mimetype2icon($file->get_mimetype(), $ext);
  $icon = $mimetype2icon["icon"];
  $type = $mimetype2icon["type"];

  $page = isset($_REQUEST["p"]) ? (int)$_REQUEST["p"] : 1;
  $perpage = 8;
  $comments = (new comment($con, 0))->get_all_p($file->get_id(), $page, $perpage, (string)$_REQUEST["sort"], (string)$_REQUEST["order"], (strlen((string)$_REQUEST["q"]) > 0 ? (string)$_REQUEST["q"] : ""));
  $count = (new comment($con, 0))->get_count($file->get_id());
  $totalpages = ceil($count / $perpage);
  return twig::view("file", ["icon" => $icon, "file" => $file, "type" => $type, "count" => $count, "comments" => $comments["comments"], "totalpages" => $totalpages, "page" => $page]);
}, [], ["category" => ["front"]]);

route::post("/upload", function() use ($con) {
  $upload = new file($con, 0);
  $result = $upload->upload($_FILES["file"], $_POST["description"], $_POST["key"], $_POST["privacy"] ?? 0);
  if(isset($_POST["redirect"]) && $result["response"] == true) {
    header("Hx-Redirect: /f/".$result["result"]["id"]."?key=".urlencode((string)$result["result"]["key"]));
  }
  return json_encode($result);
},["Content-Type" => "application/json"], ["category" => ["api", "api-strict"]]);

route::post("/api/file/{id}/comment", function($id) use ($con) {
  $comment = new comment($con, 0);
  $result = $comment->comment($id, $_POST["comment"]);
  if(isset($_GET["redirect"]) && $result["response"] == true) {
    header("Location: /f/".$id."?commentuploaded");
  }
  return json_encode($result);
},["Content-Type" => "application/json"], ["category" => ["api", "api-strict"]]);

route::get("/api/admin/{access}/ban", function($access) use ($con) {
  if(is_array(conf::get()["api"]["accesskey"]) && conf::get()["api"]["accesskey"][$access] || conf::get()["api"]["accesskey"][$_COOKIE["admin_token"]]) {
    header("Content-Type: application/json");
    $ban = new ban($con, 0);
    $result = $ban->ban($_REQUEST["identity"], $_REQUEST["reason"], (bool)$_REQUEST["permanent"], $_REQUEST["offendingitem"], time() + $_REQUEST["length"]);
    return json_encode($result);
  } else {
    return twig::view("404");
  }
}, [], ["category" => ["api", "api-strict"]]);

route::get("/api/file/{id}/info", function($id) use ($con) {
  $file = new file($con, $id);
  if(!$file->exists()) {    
    header("Content-Type: application/json");
    return json_encode(["response" => false, "text" => "File not found."]);
  }
  return json_encode([
    "id" => $file->get_id(),
    "filename" => $file->get_filename(),
    "description" => $file->get_description(),
    "mimetype" => $file->get_mimetype(),
    "size" => $file->get_size(),
    "privacy" => $file->get_privacy(),
    "identity" => $file->get_identity(),
    "downloads" => $file->get_downloads(),
    "createdat" => $file->get_createdat(),
  ], JSON_UNESCAPED_SLASHES/* || JSON_PRETTY_PRINT*/);
}, ["Content-Type" => "application/json"], ["category" => ["api"]]);

route::get("/api/mimetype2icon", function() {
  return mimetype2icon($_REQUEST["mimetype"], $_REQUEST["ext"])["icon"];
}, ["Content-Type" => "text/plain"], ["category" => ["api"]]);

route::get("/api/file/{id}/download", function($id) use ($con) {
  ob_start();
  $file = new file($con, $id);
  if(!$file->exists()) {    
    header("Content-Type: application/json");
    return json_encode(["response" => false, "text" => "File not found."]);
  }
  header("Content-Disposition: attachment; filename=\"".$file->get_filename()."\"");
  header("Content-Type: ".$file->get_mimetype() ?? "application/octet-stream");
  $file->count_download();
  ob_end_clean();
  return file_get_contents($file->get_file());
}, [], ["category" => ["api"]]);
route::get("/api/file/{id}/view", function($id) use ($con) {
  $file = new file($con, $id);
  if(!$file->exists()) {    
    header("Content-Type: application/json");
    return json_encode(["response" => false, "text" => "File not found."]);
  }
  if(!isset($_REQUEST["plaintext"])) {
    header("Content-Type: ".$file->get_mimetype());
  } else {
    header("Content-Type: text/plain");
  }
  return file_get_contents($file->get_file());
}, [], ["category" => ["api"]]);

route::get("/api/file/{id}/{filename.ext}", function($id, $filename) use ($con) {
  $file = new file($con, $id);
  if(!$file->exists()) {    
    header("Content-Type: application/json");
    return json_encode(["response" => false, "text" => "File not found."]);
  }
  if(!isset($_REQUEST["plaintext"])) {
    header("Content-Type: ".$file->get_mimetype());
  } else {
    header("Content-Type: text/plain");
  }
  return file_get_contents($file->get_file());
}, [], ["category" => ["api"]]);


route::get("/docs/api", function() {
  return twig::view("docsapi");
}, [], ["category" => ["front"]]);


route::get("/sitemap", function() {
  return twig::view("sitemap", ["routes" => route::$routes]);
}, [], ["category" => ["front"]]);