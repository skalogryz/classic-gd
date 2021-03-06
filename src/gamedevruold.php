<?php

//
// Конвертируем сайт в старый формат
// Т.к. старый формат не меняется, весь html захардкожен


require_once("gamedevru.php");

function OutputBegin($site)
{
echo '<html>
<head>
<meta http-equiv="X-UA-Compatible" content="IE=Edge">
<link href="'.$site->basepath.'main.css" rel="stylesheet" type="text/css"/>
<script type="application/javascript" src="'.$site->basepath.'skif.js"></script>
</head>
<body>

<div style="background: #efefef; height:19px">';

if ($site->isGuest) echo '<div id="login" style="float: right;"></div>';

echo '<b>'.$site->slogan.'</b></div>
<div id="header" style="min-height: 92px;">';
echo '<a id="sitename" href="'.$site->mainlink->href.'">'.$site->mainlink->text.' &nbsp;</a>
<!--
<a href="https://zavod.games/#jobs">
<img align="right" src="https://gamedev.ru/files/images/zavodgames.gif"/></a>
-->
</div>

 <div id="path"><div id="search" style="float: right;"></div>
 <b>'.$site->slogan.'</b> [<a href="https://gamedev.ru/forum/">Форум</a> / <a href="https://gamedev.ru/?info">Инфо</a>]
 </div>

 <div id="container">';

}

function OutputEdit($site)
{

echo '
<div>
<div id="preview">'.$site->previewhtml.'</div>
<h2>Ваш ответ, <b>'.$site->userlink->toHTML().'</b>:</h2>

<script language="JavaScript">
<!--
function verifySubmitFields(form)
{
	if (document.postform.text.value == ""){
	alert("Вы не ввели значение для Сообщение");
	return false;}
	if (document.postform.text.value.length > 10000){
	alert("Длина поля Сообщение не должна превышать 10000 символов!");
	return false;}

	postform._gdr_post.disabled = true;
	return true;
}
-->
</script>
<form name="postform" method="POST" action="#preview" onsubmit="return verifySubmitFields(this)">

<p><b>Сообщение:</b> Максимум 10000 символов. Отправить: Ctrl+Shift+Enter</p>
<p><span id="areatags"></span>
<textarea onpaste="editpasted(event)" class="gdr" name="text" cols="68" rows="18" onkeydown="key_pressed(event);">'.$site->edittext.'</textarea></p>
<p><label for="subscribe"><input type="checkbox" id="subscribe" name="subscribe">Получать ответы на e-mail</label>

<input type="hidden" name="action" value="autopost">
<input type="hidden" name="huyita" value="'.$site->editcheck.'">
</p><p class="r"><input id="_gdr_preview" name="_gdr_preview" type="submit" value="Предпросмотр">
<input id="_gdr_post" name="_gdr_post" type="submit" class="blue" value="Отправить"></p>
</form>
</div>
';
}

function OutputEnd($site)
{

echo '

    <div class="clear"></div>
  </div>
';


echo '<div id="footer">';
for ($i = 0; $i < sizeof($site->footerlinks); $i++)
{
  $link = $site->footerlinks[$i];
  if ($i > 0) echo " | ";
  echo $link->toHTML();
}
echo  '</div>';

echo '<div id="bottom">
   <div>2001—2019 &copy; <b>'.$site->slogan.'</b></div>
   <div id="social"></div>
   <div id="pda"></div>
 </div>
 <div class="seo"></div>';

echo'
<script type="application/javascript"><!--';

if ($site->basepath!="") 
echo '
skif.domain = "'.$site->basepath.'";
skif.searchdomain = "gamedev.ru";
';

echo ' skif.run();
//-->
</script>
</body>
</html>';
}

function OutputMenuArray($menus)
{
  foreach ($menus as $mnu) 
  {
    if ($mnu->isSelected) echo '<li class="sel">';
    else echo '<li>';
    echo $mnu->link->toHTML();
    if (sizeof($mnu->menus)>0) 
    {
      echo "<ul>";
      OutputMenuArray($mnu->menus);       
      echo "</ul>";
    }
    echo '</li>';
  }
}

function OutputMenu($site)
{
  echo '<ul class="menu">';
  OutputMenuArray($site->menus);
  echo '</ul>';
}

function OutputLeft($site)
{
  echo '<div id="left">';
  OutputMenu($site);
  echo '</div>';
}

function OutputRight()
{
  echo '<div id="right" class="show_right"></div>';
}

function OutputThread($site)
{

  foreach($site->messages as $msg)
  {
    if ($msg->timepass) {
      echo '<div class="timePassed" style="text-align:center; font-size:2em; margin-bottom:1em; margin-top:1em">... '.$msg->html.' ...</div>';
      continue;
    }
  
    echo '<div id="'.$msg->id.'" class="mes">';
    echo '<table class="mes"><tbody><tr>';
    if ($msg->isComplex) echo '<th class="red">'; else echo "<th>";
    echo "<b>".$msg->userlink->toHTML()."</b></th>";
    echo '<td class="level">'.$msg->levellink->toHTML()."</td>";
    echo '<td class="center">www</td>';
    if ($msg->editlink->href!="")
    {
      echo '<td class="center">'
          .'<a style="cursor: pointer" href="'.$msg->editlink->href.'" title="'.$msg->editlink->title.'">Правка</a>'
          .'</td>';
    }
    if ($msg->deletelink->href!="")
    {
      echo '<td class="center">'
          .'<a style="cursor: pointer" href="'.$msg->deletelink->href.'" title="'.$msg->deletelink->title.'">Удалить</a>'
          .'</td>';
    }

    if ($msg->quotenick!="")
    {
      echo '<td class="center">'
          .'<a style="cursor: pointer" class="fquote" data-nick="'.$msg->quotenick.'" title="Вставка ника с цитированием выделенного текста">« »</a>'
          .'</td>';
    }
    echo '<td style="width: 100px">'.$msg->datestr."</td><td>".$msg->timestr."</td>";
    echo "<td><i>".$msg->msglink->toHTML()."</i></td>";
    echo "</tr></tbody></table>";
    echo '<div class="block">'.$msg->bodyhtml;
    if ($msg->editstr) {
      echo '<p class="r" style="margin-top: 1px; margin-bottom: 2px;"><span class="q" style="font-size: 80%;">'.$msg->editstr.'</span></p>';
    }
    echo "</div>";

    echo "</div>";
  }
}

// дочерние форумы
function OutputForum2Daughter($frm, $even)
{
  if ($frm->isComplex) echo '<tr class="red">';
  else if ($even) echo '<tr class="sec">';
  else echo '<tr>';

  echo '<td>';
  echo '<b>'.$frm->link->toHTML().'</b>';

  $cnt = 0;
  if (sizeof($frm->pages)>0) {
    echo ' [&nbsp;';
    $lastpg = intval($frm->pages[0]->text)-1;
    foreach ($frm->pages as $page) {
      $pg = intval($page->text);
      if ($pg!=$lastpg+1) echo " &hellip;";
      $lastpg = $pg;
      if ($cnt>0) echo " ";
      $cnt++;
      echo $page->toHTML();
    }
    echo '&nbsp;]';
  }
  echo '</td>';
  echo '<td style="text-align: center;">'.$frm->author.'</td>';
  echo '<td style="text-align: right;">'.$frm->replies.'</td>';
  echo '<td style="text-align: right;"><small>'.$frm->lastreplyname." ".$frm->lastreplylink->toHTML().'</small></td>';
  echo '<td><b>'.$frm->seclink->toHTML().'</b>';
  echo '</tr>';
}

function OutputForum2Default($frm, $even)
{
  if ($frm->isComplex) echo '<tr class="red">';
  else if ($even) echo '<tr class="sec">';
  else echo '<tr>';

  echo '<td>';
  if ($frm->isnew) echo '<span class="red">New!</span> ';

  echo '<b>';
  $lstr = $frm->seclink->toHTML();
  if ($lstr!="") echo $lstr.": ";

  echo $frm->link->toHTML();
  echo '</b>';

  $cnt = 0;
  if (sizeof($frm->pages)>0) {
    echo ' [&nbsp;';
    $lastpg = intval($frm->pages[0]->text)-1;
    foreach ($frm->pages as $page) {
      $pg = intval($page->text);
      if ($pg!=$lastpg+1) echo " &hellip;";
      $lastpg = $pg;
      if ($cnt>0) echo " ";
      $cnt++;
      echo $page->toHTML();
    }
    echo '&nbsp;]';
  }
  echo '</td>';
  echo '<td style="text-align: center;">'.$frm->author.'</td>';
  echo '<td style="text-align: center;">'.$frm->lastreplyname.'</td>';
  echo '<td style="text-align: right;">'.$frm->replies.'</td>';
  echo '<td style="text-align: right;">'.$frm->lastreplylink->toHTML().'</td>';
  echo '</tr>';
}


function OutputForum2($forums, $i, $isdaught = false)
{
  $even = false;
  while ($i<sizeof($forums)&&($forums[$i]->level==2)) 
  {
    $frm = $forums[$i];
    // можно и не использовать формат дочерних форумов!, просто всегда выводить как Default
    // но тогда и заголовки нужно выводить не для дочерних!
    if ($isdaught) OutputForum2Daughter($frm, $even);
    else OutputForum2Default($frm, $even);
    $even = !$even;
    $i++;
  }
  return $i;
}

function OutputForum1($forums, $i)
{
  while ($i<sizeof($forums)&&($forums[$i]->level>=1)) 
  {
    $frm = $forums[$i];
    $isdaught = false;
    echo '<table class="r" cellspacing="1"><tbody><tr>';
    if ($frm->level==1) {
      echo '<th>'.$frm->link->toHTML().'</th>';
      echo '<th width="120" style="text-align: center;">Автор</th>';
      echo '<th width="120" style="text-align: center;">Последний</th>';
      echo '<th width="40" style="text-align: right;">Отв.</th>';
      echo '<th width="110" style="text-align: right;">Обновление</th>';
      $i++;
    } else {
      $isdaught = true;
      echo '<th>Название</th>';
      echo '<th width="120" style="text-align: center;">Автор</th>';
      echo '<th width="40" style="text-align: right;">Отв.</th>';
      echo '<th width="130" style="text-align: right;">Обнов.</th>';
      echo '<th width="120">Раздел</th>';
    }
    echo '</tr>';

    $i=OutputForum2($forums, $i, $isdaught);
    echo '</tbody></table>';
    echo '<div style="height: 10px"></div>';
  }
  return $i;
}

function OutputForum0($forums, $i)
{
  while ($i<sizeof($forums)&&($forums[$i]->level==0)) 
  {
    $frm = $forums[$i];
    $i++;
    if ($frm->isTitle) {
      echo '<div class="title">';
      echo $frm->link->text;
      echo '</div>';
      $i=OutputForum1($forums, $i);
    } else {
      echo '<div class="docs"><div class="menuco"><h2>';
      echo $frm->link->toHTML();
      echo '</h2></div>';
      $i=OutputForum1($forums, $i);
      echo '</div>';
    }   
  }
  return $i;
}

function OutputForum($site)
{
  $lastlvl = -1;
  $even = false;

  $lvl0 = 0;
  $lvl1 = 0;
  $lvl2 = 0;
  foreach ($site->forums as $frm) { 
    if ($frm->level==0) $lvl0++;
    else if ($frm->level==1) $lvl1++;
    else $lvl2++;
  }
  
  $islvl2only = (($lvl2>0)&&($lvl1==0)&&($lvl0==0));
   
  if ($islvl2only) {
    echo '<table class="r" cellspacing="1"><tbody><tr>';
    echo '<th>Название</th>';
    echo '<th width="120" style="text-align: center;">Автор</th>';
    echo '<th width="120" style="text-align: center;">Последний</th>';
    echo '<th width="40" style="text-align: right;">Отв.</th>';
    echo '<th width="110" style="text-align: right;">Обновление</th>';
    echo '</tr>';
    $i=0;
    OutputForum2($site->forums, $i);
    echo "</tbody></table>";
  } else {
    $i = 0;
    while ($i < sizeof($site->forums))
    {
      $frm = $site->forums[$i];
      if ($frm->level==0)
        $i = OutputForum0($site->forums, $i);
      else if ($frm->level==1) 
        $i = OutputForum1($site->forums, $i);
      else 
        $i = OutputForum2($site->forums, $i);
    }       
  }

}

function OutputPages($site)
{
  if (sizeof($site->pages)==0) return;
  echo '<div class="pages">Страницы: ';
  $lastpg = intval($site->pages[0]->text)-1;
  $hasellipse = false;
  foreach ($site->pages as $link) {
    $pg = intval($link->text);
    if (($pg!=$lastpg+1)&&($pg>0)) echo "&hellip; ";
    $lastpg = $pg;
    
    if (!$link->href) {
      echo "<span>".$link->text."</span>";
    } else {
      echo $link->toHTML();
    }
    echo " ";
  }
  echo "</div>";
}

function OutputMainStart($title)
{
  echo '<div id="main">
    <div id="main_body"><h1 class="title" itemprop="name headline">'.$title.'</h1>
   <div style="height: 18px; text-align: right; padding: 5px;"><span style="padding: 5px;"></span></div>'; // "padding is seen in a Thread
}

function OutputMainEnd($site, $editable)
{
   echo '</div><div id="main_add">';

   if ((!$site->isGuest)&&$editable) OutputEdit($site);
 
   echo '</div></div>';
}

function OutputPaths($site)
{
  $cnt = 0;
  echo '<p id="bottompath">';
  foreach($site->paths as $pth) 
  {
    echo "/ ";
    if ($cnt==0) {
      echo "<b>";
      echo $pth->toHTML();
      echo "</b>";
    } else   
      echo $pth->toHTML();
    echo " ";
    $cnt++;
  } 
  echo '</p>';
}

function OutputArticles($site)
{
  if (sizeof($site->articles)==0) return;
  echo '<div class="docs">';
  echo '<div class="menucode"><h2><a href="https://gamedev.ru/articles/">Новости и Статьи</a></h2></div>';
  foreach($site->articles as $art) 
  {
    echo '<a id="'.$art->id.'"></a>';
    echo '<table width="100%" cellspacing="1" cellpadding="3">
<tbody><tr>
<td class="co" style=" "><b>'.$art->link->toHTML().'</b></td>
</tr>
</tbody></table>';
    echo $art->html;
  }
  echo '</div>';
}

function OutputSummary($site)
{
  foreach($site->summaries as $sm)
  {
     if ($sm->subfrmsum) continue;
     echo '<div class="list">';
     echo '<b>'.$sm->link->toHTML().'</b>';
     if ($sm->total=="") echo ": "; 
     else echo " [".$sm->total. "]";

     foreach ($sm->subsum as $sub) 
     {
       echo $sub->link->toHTML();
       echo " ".$sub->total." ";
     }
     echo '</div>';
  }
  $cnt = 0;
  $ttl = 0;
  $usettl = true;
  foreach($site->summaries as $sm)
  {
     if (!$sm->subfrmsum) continue;
     if ($cnt == 0) echo '<table class="r" cellspacing="1"><tbody><tr>'
         .'<th>Рубрика</th> <th width="60" style="text-align: right;">Темы</th> <th width="100" style="text-align: right;">Сообщения</th>'
         .'</tr>';
     $cnt++;

     echo '<tr><td><b>';
     echo $sm->link->toHTML();
     echo '</b></td><td style="text-align: right;">';
     echo $sm->total;
     echo '</td><td style="text-align: right;"></td></tr>';
     $ttl += intval(trim($sm->total));
  }
  if ($cnt>0) {
    echo '</tbody></table>';
    if ($usettl) echo '<p class="r">Всего тем: '.$ttl.', Сообщений: много</p>';
    else echo '<p class="r">'.$site->summaryText.'</p>';
  }

}


function OutputSite($site, $type)
{
  if ($type=="thread") {
    OutputBegin($site);
    OutputLeft($site);
    OutputMainStart($site->title);
    OutputPages($site);
    OutputThread($site);
    OutputPages($site);
    OutputPaths($site);
    OutputMainEnd($site, true);
    OutputEnd($site);
  } else if ($type=="forum") {
    OutputBegin($site);
    OutputLeft($site);
    OutputMainStart($site->title);
    OutputSummary($site);
    OutputPages($site);
    OutputForum($site);
    OutputPages($site);
    OutputMainEnd($site, false);
    OutputEnd($site);
  } else {
    OutputBegin($site);
    OutputLeft($site);
    OutputMainStart($site->title);
    OutputPages($site);
    OutputArticles($site);
    OutputPages($site);
    OutputMainEnd($site, false);
    OutputEnd($site);
  }
}

?>