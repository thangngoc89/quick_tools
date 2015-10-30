<?php

//TODO: make a form for this
$string = file_get_contents('url_shortener_data.txt');
require_once('simpleCache.php');

main($string);

function main($string) {
  $urls = find_all_urls_in_text($string);
  $skip_hosts = ['books.khoanguyen.me'];

  foreach($urls as $url) {

    $host = parse_url($url)['host'];

    if (! in_array($host, $skip_hosts)) {
      $shortened_url = shortener($url);
      $string = str_replace($url, $shortened_url, $string);
    }
  }
  ec($string);
}

function shortener($url) {
  $cache = new Gilbitron\Util\SimpleCache();
  $cache->cache_path = 'cache/';
  $cache->cache_time = 3600;

  $key = md5($url);

  if($data = $cache->get_cache($key)){
      return $data;
  } else {
    // if (rand(1,4) % 2) {
      $data = ouo_io_shortener($url);
    // }  else {
      // $data = shorte_st_shortener($url);
    // }
    $cache->set_cache($key, $data);
  }

  return $data;
}

function ouo_io_shortener($url) {
  $data = file_get_contents('http://ouo.io/api/0G4vYlK2?s='. urlencode($url));
  return $data;
  die('Can\'t shorten link with ouo.io');
}

// shorte_st_shortener('http://books.khoanguyen.me');

function shorte_st_shortener($url) {
  $data = array('urlToShorten' => $url);
  // $data_json = json_encode($data);
  $api_endpoint = 'https://api.shorte.st/v1/data/url';
  $ch = curl_init();
  curl_setopt($ch, CURLOPT_URL, $api_endpoint);
  curl_setopt($ch, CURLOPT_HEADER, false);
  curl_setopt($ch, CURLOPT_HTTPHEADER, array('public-api-token: 60d6490808d552e7c33243ebf0bd143d'));
  curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
  curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
  $response  = curl_exec($ch);
  curl_close($ch);

  if (!$response) {
    die('Can\'t shhorten link with shorte.st');
  }

  // echo($response);
   $received_data = json_decode($response);
   if ($received_data->status != 'ok') {
     die('Can\'t shhorten link with shorte.st');
   }

   return $received_data->shortenedUrl;
}

function find_all_urls_in_text($text) {
  $regex = '/\b(https?|ftp|file):\/\/[-A-Z0-9+&@#\/%?=~_|$!:,.;]*[A-Z0-9+&@#\/%=~_|$]/i';
  preg_match_all($regex, $text, $matches);

  return $matches[0];
}

function ec($string) {
  echo "<pre>$string</pre>";
}

function dd($var) {
  die(var_dump($var));
}
