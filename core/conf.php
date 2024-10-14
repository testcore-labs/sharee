<?php
namespace core; 

class conf {
    public static function file() {
        return __FWDIR__."/config.yaml";
    }
    public static function get() {
        @require(__FWDIR__."/plugins/Spyc.php");
        $rawyaml = file_get_contents(self::file());
        $rawyaml = str_replace("__FWDIR__", __FWDIR__, $rawyaml);
        
        $yaml = spyc_load($rawyaml);
        return $yaml;
    }
}