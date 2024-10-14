<?php
use core\conf;
/* uselessly useful in some cases */

// htmlspecialchars shorterned 
function nxss(string $string, int $flags = ENT_QUOTES | ENT_SUBSTITUTE | ENT_HTML401, ?string $encoding = null, bool $double_encode = true): string {
return htmlspecialchars($string, $flags, $encoding, $double_encode);
}

// image proxy
function img2proxy(string $url, int|null $w = null, int|null $h = null): string {
$size = "&w=$w&h=$h"; // how it should be formatted for the size width and height
$proxy = "//wsrv.nl/";
return $proxy."?url=$url".$size;
}

function duration_format($time) {
  $hours = floor($time / 3600);
  $minutes = floor(($time % 3600) / 60);
  $seconds = $time % 60;
  if($hours == 0) {
    if($minutes < 10) {
      return sprintf('%01d:%02d', $minutes, $seconds);
    }
    return sprintf('%02d:%02d', $minutes, $seconds);
  } else {
    return sprintf('%02d:%02d:%02d', $hours, $minutes, $seconds);
  }
}

function timeago(string|int $time) {
    if (is_numeric($time)) {
        $timestamp = (int) $time;
    } else {
        $timestamp = strtotime($time);
    }

    $timediff = $timestamp - time();

    $isfuture = $timediff < 0 ? false : true;

    $timediff = abs($timediff);

    switch (true) {
        case $timediff === 0:
            return 'just now';
        case $timediff < 60:
            return ($isfuture ? 'in ' : '') . "$timediff second" . ($timediff !== 1 ? 's' : '') . ($isfuture ? '' : ' ago');
        case $timediff < 3600:
            return ($isfuture ? 'in ' : '') . floor($timediff / 60) . ' minute' . ($timediff < 120 ? '' : 's') . ($isfuture ? '' : ' ago');
        case $timediff < 86400:
            return ($isfuture ? 'in ' : '') . floor($timediff / 3600) . ' hour' . ($timediff < 7200 ? '' : 's') . ($isfuture ? '' : ' ago');
        case $timediff < 604800:
            return ($isfuture ? 'in ' : '') . floor($timediff / 86400) . ' day' . ($timediff < 172800 ? '' : 's') . ($isfuture ? '' : ' ago');
        case $timediff < 2419200:
            return ($isfuture ? 'in ' : '') . floor($timediff / 604800) . ' week' . ($timediff < 1209600 ? '' : 's') . ($isfuture ? '' : ' ago');
        case $timediff < 29030400:
            return ($isfuture ? 'in ' : '') . floor($timediff / 2419200) . ' month' . ($timediff < 4838400 ? '' : 's') . ($isfuture ? '' : ' ago');
        default:
            return ($isfuture ? 'in ' : '') . floor($timediff / 29030400) . ' year' . ($timediff < 58060800 ? '' : 's') . ($isfuture ? '' : ' ago');
    }
}

function ordinal(int $number): string { // apparently its called an ordinal??
    if (!is_numeric($number)) {
        return $number;
    }

    if ($number % 100 >= 11 && $number % 100 <= 13) {
        return $number . 'th';
    } else {
        switch ($number % 10) {
            case 1: return $number . 'st';
            case 2: return $number . 'nd';
            case 3: return $number . 'rd';
            default: return $number . 'th';
        }
    }
}

function int_shorten(int|float $number, int|null $precision = 3, array|object|null $divisors = null): string {

    if (!isset($divisors)) {
        $divisors = array(
            pow(1000, 0) => '',
            pow(1000, 1) => 'K',
            pow(1000, 2) => 'M',
            pow(1000, 3) => 'B',
            pow(1000, 4) => 'T',
            pow(1000, 5) => 'Qa',
            pow(1000, 6) => 'Qi',
        );    
    }

    foreach ($divisors as $divisor => $shorthand) {
        if (abs($number) < ($divisor * 1000)) {
            break;
        }
    }


    return number_format($number / $divisor, $precision) . $shorthand;
}

function int_shorten_size(int|float $number, int|null $precision = 3): string {
  return int_shorten($number, $precision, array(
    pow(1000, 0) => 'B',
    pow(1000, 1) => 'KB',
    pow(1000, 2) => 'MB',
    pow(1000, 3) => 'GB',
    pow(1000, 4) => 'TB',
    pow(1000, 5) => 'PB',
    pow(1000, 6) => 'EB',
    pow(1000, 7) => 'ZB',
    pow(1000, 8) => 'YB',
));
}

// basically:
// zero_before_number(52, 5)
// output: 00052 
// see how i put 5 zeros specifcally, but what i got was 3
function zero_before_number($value, $zeros = 1): int|float|string {
    return (str_repeat(0, (($zeros - strlen($value)) > 0 ? ($zeros - strlen($value)) : 0)).$value);
}

function http_response_codename(int $code = null): string|array|object|null {
$responses = [
    100 => 'Continue',
    101 => 'Switching Protocols',
    102 => 'Processing', // WebDAV; RFC 2518
    103 => 'Early Hints', // RFC 8297
    200 => 'OK',
    201 => 'Created',
    202 => 'Accepted',
    203 => 'Non-Authoritative Information', // since HTTP/1.1
    204 => 'No Content',
    205 => 'Reset Content',
    206 => 'Partial Content', // RFC 7233
    207 => 'Multi-Status', // WebDAV; RFC 4918
    208 => 'Already Reported', // WebDAV; RFC 5842
    226 => 'IM Used', // RFC 3229
    300 => 'Multiple Choices',
    301 => 'Moved Permanently',
    302 => 'Found', // Previously "Moved temporarily"
    303 => 'See Other', // since HTTP/1.1
    304 => 'Not Modified', // RFC 7232
    305 => 'Use Proxy', // since HTTP/1.1
    306 => 'Switch Proxy',
    307 => 'Temporary Redirect', // since HTTP/1.1
    308 => 'Permanent Redirect', // RFC 7538
    400 => 'Bad Request',
    401 => 'Unauthorized', // RFC 7235
    402 => 'Payment Required',
    403 => 'Forbidden',
    404 => 'Not Found',
    405 => 'Method Not Allowed',
    406 => 'Not Acceptable',
    407 => 'Proxy Authentication Required', // RFC 7235
    408 => 'Request Timeout',
    409 => 'Conflict',
    410 => 'Gone',
    411 => 'Length Required',
    412 => 'Precondition Failed', // RFC 7232
    413 => 'Payload Too Large', // RFC 7231
    414 => 'URI Too Long', // RFC 7231
    415 => 'Unsupported Media Type', // RFC 7231
    416 => 'Range Not Satisfiable', // RFC 7233
    417 => 'Expectation Failed',
    418 => 'I\'m a teapot', // RFC 2324, RFC 7168
    421 => 'Misdirected Request', // RFC 7540
    422 => 'Unprocessable Entity', // WebDAV; RFC 4918
    423 => 'Locked', // WebDAV; RFC 4918
    424 => 'Failed Dependency', // WebDAV; RFC 4918
    425 => 'Too Early', // RFC 8470
    426 => 'Upgrade Required',
    428 => 'Precondition Required', // RFC 6585
    429 => 'Too Many Requests', // RFC 6585
    431 => 'Request Header Fields Too Large', // RFC 6585
    440 => 'Login Time-out', // IIS
    449 => 'Retry With', // IIS
    //451 => 'Redirect', // IIS
    444 => 'No Response', // nginx
    494 => 'Request header too large', // nginx
    495 => 'SSL Certificate Error', // nginx
    496 => 'SSL Certificate Required', // nginx
    497 => 'HTTP Request Sent to HTTPS Port', // nginx
    499 => 'Client Closed Request', // nginx
    451 => 'Unavailable For Legal Reasons', // RFC 7725
    500 => 'Internal Server Error',
    501 => 'Not Implemented',
    502 => 'Bad Gateway',
    503 => 'Service Unavailable',
    504 => 'Gateway Timeout',
    505 => 'HTTP Version Not Supported',
    506 => 'Variant Also Negotiates', // RFC 2295
    507 => 'Insufficient Storage', // WebDAV; RFC 4918
    508 => 'Loop Detected', // WebDAV; RFC 5842
    510 => 'Not Extended', // RFC 2774
    511 => 'Network Authentication Required', // RFC 6585
    520 => 'Web Server Returned an Unknown Error', // Cloudflare
    521 => 'Web Server Is Down', // Cloudflare
    522 => 'Connection Timed Out', // Cloudflare
    523 => 'Origin Is Unreachable', // Cloudflare
    524 => 'A Timeout Occurred', // Cloudflare
    525 => 'SSL Handshake Failed', // Cloudflare
    526 => 'Invalid SSL Certificate', // Cloudflare
    527 => 'Railgun Error', // Cloudflare
    529 => 'Site is overloaded', // Qualys in the SSLLabs
    530 => 'Site is frozen', // Pantheon web platform
];
if(empty($code)) {
return $responses;
} else {
return $responses[$code];
}
}

function truncate(string $text, int $length, string $ellipsis = "..."): string {
if (strlen($text) <= $length) {
  return $text;
} else {
 return substr($text, 0, $length - strlen($ellipsis)).$ellipsis;
}
}

function upgrade_to_https(string $domain = null): void {
if (!(isset($_SERVER['HTTPS']) && ($_SERVER['HTTPS'] == 'on' || 
   $_SERVER['HTTPS'] == 1) ||  
   isset($_SERVER['HTTP_X_FORWARDED_PROTO']) &&   
   $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https'))
{
   header('HTTP/1.1 301 Moved Permanently');
   header('Location: https://'.($domain ?? __DOMAIN__).$_SERVER['REQUEST_URI']);
   die();
}
}

function shuffle_seeded(&$items, $seed)
{
    @mt_srand($seed);
    for ($i = count($items) - 1; $i > 0; $i--)
    {
        $j = @mt_rand(0, $i);
        $tmp = $items[$i];
        $items[$i] = $items[$j];
        $items[$j] = $tmp;
    }
}

function filesize_r($dir) {
  $size = 0;
  foreach (glob(rtrim($dir, '/').'/*', GLOB_NOSORT) as $each) {
    $size += is_file($each) ? filesize($each) : filesize_r($each);
  }
  return $size;
}

function mimetype2icon($mime, $ext = "") {
$ext = strtolower($ext);
switch (true) {
  case str_contains($mime, 'image/'):
    $icon = "/assets/img/imagpng-1.png";
    $type = "image";
    break;
  case str_contains($mime, 'video/'):
    $icon = "/assets/img/video_-1.png";
    $type = "video";
    break;
  case str_contains($mime, 'audio/'):
    $icon = "/assets/img/soundyel-2.png";
    $type = "sound";
    break;
  case $ext === 'rar' || $ext === '7z' || $ext === 'tar' || $ext === "zip" || $mime === 'application/x-gzip':
    $icon = "/assets/img/zipfile_0.png";
    $type = "zip";
    break;
  case $mime === 'text/html':
    $icon = "/assets/img/html-2.png";
    $type = "html";
    break;
  case $mime === 'application/x-dosexec' || $mime === "text/x-msdos-batch" || $mime === "application/x-sh" || $mime === "application/vnd.appimage" || $mime === "application/x-iso9660-appimage":
    $icon = "/assets/img/executable-0.png";
    $type = "executable";
    break;
  case $mime === "application/java-archive" || $ext === "jar":
    $icon = "/assets/img/jar-2.png";
    $type = "jar";
    break;
  case $mime === "application/x-qzip" || $ext === "qz" || $ext === "qzip":
    $icon = "/assets/img/qzipbutverylowres.png";
    $type = "qzip";
    break;
  case $ext === "pdf":
    $icon = "/assets/img/document-0.png";
    $type = "document";
    break;
  case $ext === "rbxl" || $ext === "rbxlx":
    $icon = "/assets/img/rbx_place.png";
    $type = "roblox_place";
    break;
  case $ext === "rbxm" || $ext === "rbxmlx":
    $icon = "/assets/img/rbx_model.png";
    $type = "roblox_model";
    break;
  default:
    $icon = "/assets/img/file_lines-0.png";
    $type = "other";
    break; 
}
return ["icon" => $icon, "type" => $type];
}

function ipua2identifier() {
  if(isset($_COOKIE["admin_token"]) && is_array(conf::get()["api"]["accesskey"]) && conf::get()["api"]["accesskey"][$_COOKIE["admin_token"]]) {
  $final = conf::get()["api"]["accesskey"][$_COOKIE["admin_token"]]["name"];
  } else {
  $hash = hash("sha256", __IP__.__UA__);
  $length = strlen($hash);
  $maxchars = 20;

  $randpart = truncate(implode(unpack("LLLL", hash("sha256", __IP__))), 6, "");
  $final = substr_replace($hash, "-".$randpart."-", $maxchars/2, $length-$maxchars);
  }
  return $final;
}

function identifierto360deg($identifier) {
  foreach(conf::get()["api"]["accesskey"] as $key => $array) {
    if($array["name"] == $identifier) {
      return $array["color"];
    }
  }
  return ((int)implode(unpack("LLLL", hash("sha256", $identifier))) % 360);
}


function emojitohtml($text, $size = "m") {
  $size = strtolower($size);
  switch(true) {
    case $size == "l":
      break;
    case $size == "m":
      break;
    case $size == "s":
      break;
    case $size == "xs":
      break;
    default:
      $size = "m";
      break;
  }

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

  $pattern = '/(' . implode('|', array_map('preg_quote', array_keys($emojis))) . ')/i';

  return preg_replace_callback($pattern, function($match) use ($emojis) {
    return $emojis[$match[0]] ?? $match[0];
  }, $text);
}