<?php
namespace core\modules; 
use Symfony\Component\Translation\Translator;
use Symfony\Component\Translation\Loader\YamlFileLoader;
use Symfony\Component\Yaml\Yaml;
use core\conf;
use Spyc;

class translate {
 const DEFAULT_LANGUAGE = 'en';
 private static $translator;
 
 public static function getfile($locale) {
  return __TRANSDIR__.'/'.$locale.'.yml';
 }
 public static function init() {
   $lang = ctype_alpha($_COOKIE['lang']) ? $_COOKIE['lang'] : self::DEFAULT_LANGUAGE;
   self::$translator = new Translator($lang);
   self::$translator->addLoader('yaml', new YamlFileLoader());
   $files = scandir(__TRANSDIR__.'/');
   self::$translator->addResource('yaml', self::getfile(self::DEFAULT_LANGUAGE), self::DEFAULT_LANGUAGE);
   foreach($files as $file) {
    $file = str_replace(".yml", "", $file);
    if($file !== self::DEFAULT_LANGUAGE) {
    self::$translator->addResource('yaml', self::getfile($file), $file);
    }
   }
  }

  public function getalllocales() {
   return self::$translator->getFallbackLocales();
  }

 public static function translator() {
  if (self::$translator === null) {
   self::init();
  }
   return self::$translator;
 }

 public static function translate($key, $parameters = []) {
  if (self::$translator === null) {
   self::init();
  }
  return self::$translator->trans($key, $parameters);
 }

 public static function t($key, $parameters = []) {
  return self::translate($key, $parameters);
 }
 public static function setlocale($locale) {
  return self::$translator->setLocale($locale);
 }
 public static function getlocale() {
  return self::$translator->getLocale();
 }

 private static function createjson($json, $keys1, $keys2) {
  $shit = array_intersect_key(Spyc::YAMLLoad($keys1), Spyc::YAMLLoad($keys2));
  // to anyone reading this mess :skull:, sorry, this will round it up so it doesnt do 53.53525232355233%
  $anypercent = round(round(count($shit)) / count(Spyc::YAMLLoad($keys1)) * 100, 2, PHP_ROUND_HALF_UP);
  file_put_contents($json, json_encode([
    "hash" => md5_file($keys1),
    "hashdefault" => md5_file($keys2),
    "completion" => $anypercent,
  ]));
  return $anypercent;
 }

 public static function transcompletion($locale) {
  $keys1 = self::getfile(self::DEFAULT_LANGUAGE);
  $keys2 = self::getfile($locale);

  $json = __FWDIR__."/cache/translation/transcompletion-".$locale.".json";
  if(file_exists($json)) {
    $decoded = json_decode(file_get_contents($json), true);
    if($decoded['hash'] == md5_file($keys1) && $decoded['hashdefault'] == md5_file($keys2)) {
      return $decoded['completion'];
    } else {
      unlink($json);
      return self::createjson($json, $keys1, $keys2);
    }
  } elseif(!file_exists($json)) {
    return self::createjson($json, $keys1, $keys2);
  }
 }
}

translate::init();