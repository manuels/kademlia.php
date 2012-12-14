<?php

ini_set('memory_limit', '50M');
ini_set('display_errors', 'on');
error_reporting(E_ERROR | E_PARSE | E_WARNING);
assert_options(ASSERT_BAIL, true);

srand(round(time()*microtime()));

if(php_sapi_name() === 'cli') {
  $server = $_SERVER['argv'][1];
}
else {
  $server = $_SERVER['SERVER_ADDR'];
}

require('../kademlia.php');

$filename = '/var/www/kademlia.php/tests/tmp/'.$server;
if(file_exists($filename))
  $json = @file_get_contents($filename);
else
  $json = "";

$url = '/';
$kad = new Kademlia\Instance($json, []);
$kad->settings->supported_protocols = [
  80 => [
    'url' => 'http://'.$server.'/kademlia.php/tests/http_test.php'
  ]
];

if(php_sapi_name() === 'cli') {
  echo "bootstrap\n";
  $n = new Kademlia\Node([
    'protocols' => [80 => [ 'url' => 'http://127.0.0.1/kademlia.php/tests/http_test.php' ] ]
  ]);
  $nodes = new Kademlia\NodeList([$n]);
  $kad->bootstrap($nodes);
}
else {
  switch($_REQUEST['action']) {
    case 'store':
      $key = hash('ripemd160', $_REQUEST['key'], true);
      $kad->store($key, $_REQUEST['value'], 60*60);
      break;
    case 'find':
      $printIt = function($arg) { var_dump('FindValue: success', $arg); };

      $key = hash('ripemd160', $_REQUEST['key'], true);
      $task = $kad->findValue($key)->success($printIt);
      break;
    default:
      echo $kad->processRequest(80, $_REQUEST);
      break;
  }
  
}


file_put_contents($filename, json_encode($kad,  JSON_PRETTY_PRINT));
@chmod($filename, 0666);


?>
