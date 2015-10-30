<?php
require_once __DIR__ . '/vendor/autoload.php';

use GuzzleHttp\Client;

$cache = new Gilbitron\Util\SimpleCache();
$cache->cache_path = '../cache/';
$cache->cache_time = 3600;

// $fb = new Facebook\Facebook([
//   'app_id' => '383096061852800',
//   'app_secret' => '3779a8e257f889d0e89c86c078c6b817',
//   'default_graph_version' => 'v2.5',
//   ]);
$postId = '895942130491623';
$access_token = 'CAACEdEose0cBACUGKmj8lxLgAg9K6DPyBXZBlKYtwgoempZBvtx1iY6eP0uSJjaWZAwmmUAJOg4qBpHrZAxmvfYTTfd1vuYrUtPH5cMwZB1KZCZBGU5XK6Dv3s3U0wLECZCrIpZAvY6etZAZCfA5vxIGHQ1xPYQTAt1t5C2LLQ0GYmWcY7PZAxZAAbTBndgqHsVa0c6eUdAAKuZBuYleWSgZCEMgqwJEtb8Vs2qW6wZD';

// Init the function
get_all_comments_from_facebook_post($postId, $access_token);

function get_all_comments_from_facebook_post($postId, $access_token) {
  $graph_base_url = 'https://graph.facebook.com/v2.5/';
  $url =  $graph_base_url . $postId .'/comments?access_token=' . $access_token;
  $next = true;
  $data = get_from_url($url);
  $emails = []; // Use for store the final emails result
  while($next) {
    $new_emails = pick_emails($data);
    // Merge exisiting emails with new emails
    $emails = array_merge($new_emails, $emails);

    if (isset($data->paging->next)){
      // There is still a page, get that page
      $data = get_from_url($data->paging->next);
    } else {
      $next = false;
    }
  }
  return print_array_as_a_list($emails);
}

// Loop through recived data and pick emails in comments
function pick_emails(stdClass $data) {
  $temp_string = ''; // For joining all message together
  foreach($data->data as $comment) {
    $message = $comment->message;
    $temp_string .= $message;
  }

  return pick_emails_from_string($temp_string);
}

// This export all emails avaiable in the string
function pick_emails_from_string($string) {
  $pattern = '/[A-Za-z0-9._\-\+]+@[a-z0-9\-]+\.([a-z]{2,3})(?:\.[a-z]{2})?/i';
  preg_match_all($pattern, $string, $matches);
  return $matches[0];
}

// Get data from url and apply json_encode
function get_from_url($url) {
  global $cache;

  $key = md5($url);
  if ($data = $cache->get_cache($key)) {
    return json_decode($data);
  }
  // If this url was not cache yet. Get it
  $data = $cache->do_curl($url);
  $cache->set_cache($key, $data);

  return json_encode($data);
}

// helper function to dump variable
function dd($var) {
  return die(var_dump($var));
}

function print_array_as_a_list($array){
  echo '<pre>';
  foreach ($array as $element) {
    echo $element . PHP_EOL;
  }
  echo '</pre>';
}
