<?php

  require_once("bootstrap.php");
  require_once("gamedevru.php");
  require_once("gamedevruold.php");

function callHttp($url)
{
/*  $r = new HttpRequest($url, HttpRequest::METH_GET);
  try {
    return $r->send()->getBody();
  } catch (Exception $x)
  {
    echo $x;
  }
*/
  return file_get_contents($url);
//  return http_get($url);
}
  //echo "hello world\r\n";
  //print_r ($_GET);
  //print_r ($_SERVER);
  $g_truesite = "https://gamedev.ru/";
  $g_proxypath = "/gamedev.ru";
  //print_r ($_GET);
  //print_r ($_SERVER);
  $url = $_SERVER["REQUEST_URI"];
  $url = substr_replace($url, "", 0, strlen($g_proxypath));
  $url = $g_truesite.$url;
  //echo "will call: $url\r\n";

  $page = callHttp($url);
  $mode = "";
  if (strpos($_SERVER["SCRIPT_URL"], '/forum')) 
    $mode = "forum";
  if (($mode == "forum")&&(array_key_exists("id", $_GET))) 
    $mode = "thread";
  
  $doc = new DOMDocument;
  $doc->loadHTML($page);

  $xpath = new DOMXpath($doc);
  $gd = new GameDev();
  $basepath = $_SERVER["REQUEST_SCHEME"]."://".$_SERVER["HTTP_HOST"].$g_proxypath;
  $gd->basepath = $basepath."/"; // this is used NOT to create a stand-alone host, but rather a directory in existing one

  GatherSite($xpath, $gd, $mode);
  $gd->rebase($basepath);
  OutputSite($gd, $mode);
//  if /gamedev.ru/flame/forum/
  
?>