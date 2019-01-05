<?php

  require_once("bootstrap.php");
  require_once("gamedevru.php");
  require_once("gamedevruold.php");

function callHttp($url, $method, $baseurl, $oldbase)
{
  $method = strtoupper($method);
  $cookie = "";
  foreach ($_COOKIE as $nm=>$vl) $cookie .= $nm.'='.$vl.";";
  if ($cookie!="") $cookie = "Cookie: ".$cookie;

  $opts = array(
  'http'=>array(
    'method'=>$method,
    'header'=>$cookie 
    )
  );

  $ispost = $method=='POST';

  if ($ispost) {
    $post = array();

    foreach($_POST as $key=>$value) {
      // gamedev, doesn't support utf8 as input chars
      // converting to cp1251
      $post[$key]=iconv("UTF-8", "CP1251//IGNORE", $value);
    }

    $opts['http']['content']=http_build_query($post);
    $hdr=$opts['http']['header'];
    if ($hdr!="") $hdr.="\r\n";
    $hdr.="Content-type: application/x-www-form-urlencoded\r\n";  
    $opts['http']['header'] = $hdr;
  }

  $context = stream_context_create($opts);
  $res = file_get_contents($url, false, $context);

  if ($ispost) {
    $isredirect = false;
    $redirpath = "";

    foreach($http_response_header as $h) {
      if (!(strpos($h ,"Set-Cookie:")===false)) {
        $s = str_replace("Set-Cookie:", "", $h);
        $s = trim($s);
        $arr = explode(";",$s);
        foreach($arr as $pair) {
          $v = explode("=",trim($pair));
          $nm = ""; $vl ="";
          if (sizeof($v)<=1) $nm = $v[0]; 
          else {
            $nm = $v[0];
            $vl = $v[1];
          }
          $unm = strtoupper($nm);
          if (($unm == "EXPIRES") || ($unm == "PATH")|| ($unm == "SECURE")|| ($unm == "HTTPONLY")) continue; //todo: need to forward!

          $parts = parse_url($baseurl);
          setcookie($nm, $vl,  time()+60*60*24*365*2, $parts["path"], $parts["host"]); // todo: the cookie comes first, and then the rest of information
          break;
        }
      } else if (!(strpos($h ,"Location:")===false)) {
        $isredirect = true;
        $redirpath = trim(str_replace("Location:", "", $h));
      }
    }
    if ($isredirect) {
      //todo: rebase
      header("Location: ".str_replace( $oldbase, $baseurl, $redirpath ) , true, 302); 
      die(); // redirect and bail-out
    }
  }

  return $res;
}

function JoinUrls($a, $b)
{
  if (($a!="") && ($b!="")) 
    if (($b[0]=="/") && ($b[0]==$a[strlen($a)-1])) 
      return $a.substr($b, 1);
  return $a.$b;
}

function RemoveSlash($a)
{
  if (($a!="")&&($a[strlen($a)-1]=="/")) 
    return substr($a, 0, strlen($a)-1);
  else
    return $a;
}


  $g_truesite = "https://gamedev.ru/";
  $g_proxypath = "/gamedev.ru";

  $basepath = $_SERVER["REQUEST_SCHEME"]."://".$_SERVER["HTTP_HOST"].$g_proxypath;

  $url = $_SERVER["REQUEST_URI"];

  // todo: cleanup the mess! :(
  //if ($_SERVER["REQUEST_METHOD"] != 'POST') {
  
  $url = substr_replace($url, "", 0, strlen($g_proxypath));
  $sitepath = $url; 

  $url = JoinUrls($g_truesite,$url);
  //} else {
  //  if (strpos($url, "/")==0) $url=substr($url, 1);
  //  $url = $g_truesite.$url;
  //}

  $page = callHttp($url, $_SERVER["REQUEST_METHOD"], RemoveSlash($basepath), RemoveSlash($g_truesite));

  $mode = "";
  if (($sitepath =="")||($sitepath =="/"))
    $mode = "main";
  else if (!(strpos($sitepath, '/forum')===false)) 
    $mode = "forum";

  if (($mode == "forum")&&(array_key_exists("id", $_GET))) 
    $mode = "thread";
  
  $doc = new DOMDocument;
  libxml_use_internal_errors(true);
  $doc->loadHTML($page);
  libxml_clear_errors();
  
  $xpath = new DOMXpath($doc);
  $gd = new GameDev();
  $gd->basepath = $basepath."/"; // this is used NOT to create a stand-alone host, but rather a directory in existing one
  
  GatherSite($xpath, $gd, $mode);
  $gd->rebase($basepath);
  OutputSite($gd, $mode);
  
?>