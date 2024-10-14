<?php
// DEPRECATED
// DO NOT USE!
// TERRIBLE IDEA AS ITS UNSTABLE
try {
$fiber = new Fiber(function() : void {
$file = __FWDIR__.'/access/access.json';

// if only i knew what a lock was.
$filecontents = json_decode(file_get_contents($file)) ?? null;
if(count($filecontents) >= 1000) {
file_put_contents(__FWDIR__.'/access/archive/access-'.time().".json", json_encode($filecontents, JSON_PRETTY_PRINT));
unlink($file);
}
$lock = fopen($file, 'c+');
if (flock($lock, LOCK_EX)) {
    $json = json_decode(file_get_contents($file), true);

    $json[] = [
        "time" => date("Y-m-d H:i:s"),
        "response_code" => http_response_code(),
        "domain" => __DOMAIN__,
        "uri" => __URL__,
        "ip" => __IP__,
        "useragent" => __UA__,
        "referer" => __REFERER__,
        __METHOD__ => $_REQUEST,
        "cookies" => __COOKIE__
    ];

    file_put_contents($file, json_encode($json, JSON_PRETTY_PRINT));
    flock($lock, LOCK_UN);
    fclose($lock);
} else {
}


// unique visitors / requests

$file2 = __FWDIR__.'/access/unique.json';
$json2 = json_decode(file_get_contents($file2), true);

if(empty($json2[__IP__])) {
$json2[__IP__] = [
"time" => date("Y-m-d H:i:s"),
"blockeddomains" => [],
"blockeduris" => [],
];
}

file_put_contents($file2, json_encode($json2, JSON_PRETTY_PRINT));

});
} catch (e) {
return;
}

$fiber->start();