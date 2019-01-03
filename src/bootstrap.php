<?php
// чтение структуры файла

require_once("gamedevru.php");
  
 // xpath для общих частей
 $common_sel_title = "/html/body/div[contains(@class, 'bound')]/h1";
 $common_sel_pages = "(/html/body/div[contains(@class, 'bound')]/div[contains(@class, 'pages')])[1]/a"
                    ."|(/html/body/div[contains(@class, 'bound')]/div[contains(@class, 'pages')])[1]/span";
 $common_sel_menus  ="/html/body/div[contains(@id,'tool')]/div/div[contains(@id,'menu')]/div/ul/li";
 $common_sel_submenus ="./ul/li";
 $common_sel_menulink = "./a";
  

 // xpath для сообщений
 $tm_sel = "/html/body/div[contains(@class, 'head')]"; // тэг, указывающий на присутствие 
 $tm_sel_messageid = "div[contains(@class, 'right')]/a"; // ссылка сообщения
 $tm_sel_userlink  = "(./ul/li/a)[1]"; // ссылка на юзера 
 $tm_sel_level     = "./ul/li[2]/a|./ul/li[2]"; // ссылка (либо текст) уровня юзера, "Пользователь/Учатник/Забанен"
 $tm_sel_datetime  = "./following-sibling::div[1]/p[last()]"; // ссылка (либо текст) уровня юзера, "Пользователь/Учатник/Забанен"
 $tm_sel_bodyelem  = "./following-sibling::div[1]/*[position()<last()]"; // все элемент тела сообщения.
                                                                         // для bootstap приходится исключать последний элемент (в нём дата)
 $tm_sel_paths     = "(/html/body/div[contains(@class, 'path')]/div)[1]/a"; // ссылки "пути" (внизу страницы). Путь написан дважды, необходимо ограничинть выбором только первого набора

 // селекторы для раздела форумов
 $frm_sel_nextforum = "./following-sibling::h2"
                     ."|./following-sibling::h3"
                     ."|./following-sibling::p"
                     ."|./following-sibling::div"
                     ."|./following-sibling::table";
 $frm_sel_threadlink = "./div/a[1]|./div/b/a[1]";
 $frm_sel_threadlinkpages = "./div/a[position()>1]"; 
 $frm_sel_replies = "./span"; // кол-во недавних ответов
 $frm_sel_lastreplylink = "./div/small/span/a";
 $frm_sel_author = "./div/small/span"; 

// парсим сообщения со страницы 
function GatherMessages($xpath, $site)
{
  global $tm_sel, $tm_sel_messageid, $tm_sel_userlink,
     $tm_sel_level, $tm_sel_datetime, $tm_body_isheadsibling, 
     $tm_sel_bodyelem, $tm_sel_paths;

  $msglist = $xpath->query($tm_sel);
  foreach($msglist as $elem) {
    $msg = $site->addMessage();    

    $msg->id = $elem->getAttribute('id'); 
     
    // user link
    $xml = $xpath->query($tm_sel_userlink, $elem);
    if ($xml->length>0) $msg->userlink->fromXML($xml[0]); 

    // message link
    $xml = $xpath->query($tm_sel_messageid, $elem);
    if ($xml->length>0) $msg->msglink->fromXML($xml[0]); 

    //level
    $xml = $xpath->query($tm_sel_level, $elem);
    if ($xml->length>0) {
      $msg->levellink->fromXML($xml[0]); 
      $msg->isComplex = ((strpos($msg->levellink->text, "Участник")===0)||(strpos($msg->levellink->text, "Модератор")===0));
    }


    // дата сообщения
    $xml = $xpath->query($tm_sel_datetime, $elem);
    if ($xml->length>0) {
      $msg->datestr=$xml[0]->textContent;
      $i = strpos($msg->datestr,'(');
      if (!($i===false)) {
         $msg->editstr = substr($msg->datestr, $i);
         $msg->editstr = substr($msg->editstr, 1, strlen($msg->editstr)-2);
         $msg->datestr = substr($msg->datestr, 0, $i-1);
      }
    }

    // сообщение
    $xml = $xpath->query($tm_sel_bodyelem, $elem);
    foreach($xml as $e) {
      $msg->bodyhtml.=$xpath->document->saveXML($e);
    }
  }

  $msglist = $xpath->query($tm_sel_paths);
  foreach($msglist as $elem) {
    $lnk = $site->addPath();
    $lnk->fromXML($elem);
  }
}


function GatherPages($xpath, $site)
{
  global $common_sel_pages;

  $xml = $xpath->query($common_sel_pages);
  foreach($xml as $elem) {
    $pg = $site->addPage();
    if ($elem->nodeName == "a") $pg->fromXML($elem);
    else $pg->text = $elem->textContent;
  }
}

function GatherMenus($xpath, $site)
{
  global $common_sel_menus, $common_sel_menulink, $common_sel_submenus;
  
  $xml = $xpath->query($common_sel_menus);
  foreach ($xml as $elem) 
  {
    $mnu = $site->addMenu();

    $mnu->isSelected = !(strpos($elem->getAttribute('class'),'sel')===false);

    $xmllnk = $xpath->query($common_sel_menulink, $elem);
    if ($xmllnk->length>0) $mnu->link->fromXML($xmllnk[0]);

    $xmlsub = $xpath->query($common_sel_submenus, $elem);
    foreach ($xmlsub as $subelem) 
    {
      $submnu = $mnu->addMenu();
      $xmllnk = $xpath->query($common_sel_menulink, $subelem);
      if ($xmllnk->length>0) $submnu->link->fromXML($xmllnk[0]);
    }
  }
}


// парсим страницу треда обсуждений 
function GatherThread($xpath, $site)
{
   GatherMessages($xpath, $site);
}

function GatherForum($xpath, $site)
{
  global $common_sel_title  // = "/html/body/div[contains(@class, 'bound')]/h1";
        ,$frm_sel_nextforum
        ,$frm_sel_threadlink
        ,$frm_sel_threadlinkpages
        ,$frm_sel_replies
        ,$frm_sel_lastreplylink
        ,$frm_sel_author; 

  $xml = $xpath->query($common_sel_title);
  if ($xml->length==0) return;     
  $xml=$xml[0];
  $xml=$xml->nextSibling;
  while ($xml!=null) {
   
    if ($xml->nodeName=="table") 
      // summary table
      ;
    else if ($xml->nodeName=="p") 
      // summary label 
      ;
    else if ($xml->nodeName=="h2") {
      // big-section header
      $f = $site->addForum();
      $f->level=0;
      $f->link->fromXML($xml->firstChild);
    } else if ($xml->nodeName=="h3") {
      // section header
      $f = $site->addForum();
      $f->level=1;
      $f->link->fromXML($xml->firstChild);
    } else if (($xml->nodeName=="div")&&($xml->getAttribute('class')=='title')) {
      $f = $site->addForum();
      $f->level=0;
      $f->isTitle=true;
      $f->link->text=$xml->textContent;
    } else if ($xml->nodeName=="div") {
      $cls = $xml->getAttribute("class");
      if ($cls == "pages") { 
        $xml=$xml->nextSibling;
        continue;
      }
      if (($cls == "seo")) {
        //echo "the end has been reached... $cls\r\n";
        break;
      }
      $f = $site->addForum();
      $f->level = 2;
      $x = $xpath->query($frm_sel_threadlink, $xml);
      if ($x->length>0) {
        $x = $x[0];
        $f->isComplex = (($x->parentNode!=null)&&($x->parentNode->nodeName=="b"));
        $f->link->fromXML($x);
      }

      $x = $xpath->query($frm_sel_replies, $xml);
      if ($x->length>0) $f->replies = $x[0]->textContent;

      $x = $xpath->query($frm_sel_author, $xml);
      if ($x->length>0) {
        $f->author = $x[0]->firstChild->nodeValue;
        $i = strpos($f->author, "…");
        if (!($i===false)) {
          $f->lastreplyname = substr($f->author, $i+strlen("…"));
          $f->author = substr($f->author, 0, $i);
        } 
      };
      
      $x = $xpath->query($frm_sel_lastreplylink, $xml);
      if ($x->length>0) $f->lastreplylink->fromXML($x[0]);

      $x = $xpath->query($frm_sel_threadlinkpages, $xml);
      if ($x->length>0) {
        foreach($x as $linkxml) {
          $l = $f->addPage(); 
          $l->fromXML($linkxml);
        }
      }
    }
    $xml=$xml->nextSibling;
  }
}

// парсим страницу сайта
// нужно передавать формат странички сайта, исходя из его адреса
function GatherSite($xpath, $site, $type)
{
  global $common_sel_title;

  $xml = $xpath->query($common_sel_title);
  if ($xml->length>0)  $site->title = $xml[0]->textContent;
  GatherPages($xpath, $site);
  GatherMenus($xpath, $site);
 
  if ($type=="thread") GatherThread($xpath, $site);
  else if ($type=="forum") GatherForum($xpath, $site);
}

?>