<?php
// структура сайта

class ALink
{
  public $text;
  public $href;
  public $title;

  function __construct ($text = "", $href = "", $title = "")
  {
    $this->text = $text;
    $this->href = $href;
    $this->title = $title;
  }

  public function rebase($newbase, $oldbase)
  {
    if ($this->href=="") return;
    if (strpos($this->href, '#')===0) return;

    if (strpos($this->href,"http")===false) {
      $this->href = $newbase . $this->href;
    } else if (strpos($this->href, $oldbase)===0) {
      $this->href = substr_replace($this->href, $newbase, 0, strlen($oldbase));
    }  
  }

  public function fromXML(DOMNode $nd)
  {
    if ($nd == null) return;
    $this->text = $nd->textContent;
    $this->href = $nd->getAttribute('href');
    $this->title = $nd->getAttribute('title');
  }

  public function toHTML($plainTextIfNoRef = true)
  {
    if (($this->href=="") && ($plainTextIfNoRef))
      return $this->text;
    else {
      $res = "<a";
      if  ($this->href) $res .= ' href="'. $this->href.'"';
      if  ($this->title) $res .= ' title="'. $this->title.'"';
      $res .= ">".$this->text."</a>";
      return $res;
    }
  }
}

class Message
{
   public $msglink;            // ссылка на сообщение
   public $userlink;           // ссылка на пользователя 
   public $isComplex = false;  // сообщение от гуру 
   public $levellink;          // уровень пользователя
   public $datestr = "";       // дата, когда сообщение было сделано
   public $timestr = "";       // дата, когда сообщение было сделано
   public $editstr = "";       // последняя правка 
   public $bodyhtml = "";      // html тела сообщения
   public $id = "";            // id сообщения
   public $quotenick;          // имя ника для цитирования
   public $editlink;
   public $deletelink;
   public $timepass = false;
   function __construct()
   {
      $this->msglink = new ALink();
      $this->userlink = new ALink();
      $this->levellink = new ALink();
      $this->editlink = new ALink();
      $this->deletelink = new ALink();
   }
   function rebase($newbase, $oldbase)
   {
      $this->msglink->rebase($newbase, $oldbase);
      $this->userlink->rebase($newbase, $oldbase);
      $this->levellink->rebase($newbase, $oldbase);
      $this->editlink->rebase($newbase, $oldbase);
      $this->deletelink->rebase($newbase, $oldbase);
   }
}

class Menu
{
  public $menus = array(); 
  public $link;
  public $isSelected = false;
  function __construct ()
  {
    $this->link = new ALink();
  }

  function addMenu()
  {
     $menu = new Menu();
     array_push($this->menus, $menu);
     return $menu;
  }

  function rebase($newbase, $oldbase)
  {
    $this->link->rebase($newbase, $oldbase);
    foreach ($this->menus as $mnu)
      $mnu->rebase($newbase, $oldbase);
  }
}

class Forum
{
  public $level;  // 0 - master, 1 - section, 2 - thread
  public $link;   // имя/ссылка
  public $seclink; // ссылка на секцию (используется в "дочерних форумах" н.р   Urtho 3D: Болатлка -> $seclink : $link
  public $isComplex = false; // сложная тема или повышенной важности
  public $isTitle = false;   // заголовок дочерние форумы
  public $replies = "";
  public $lastreplylink;
  public $author = "";
  public $lastreplyname = "";
  public $pages = array();
  function __construct()
  {
    $this->link = new ALink();
    $this->lastreplylink = new ALink();
    $this->seclink = new ALink();
  }

  function addPage()
  {
    $page = new ALink();
    array_push($this->pages, $page);
    return $page;
  }

  function rebase($newbase, $oldbase)
  {  
    $this->link->rebase($newbase, $oldbase);
    $this->lastreplylink->rebase($newbase, $oldbase);
    $this->seclink->rebase($newbase, $oldbase);
    foreach($this->pages as $page)
      $page->rebase($newbase, $oldbase);
  }
}

class Article
{
  public $id; 
  public $link; // do not rebase (for now)
  //public $img;
  //public $author;
  //public $title;
  //public $shortext;
  //public $paths = array();
  public $html;

  function __construct()
  {
    $this->link = new ALink();
  }

  function rebase($newbase, $oldbase)
  {
    //пока не нужно. пусть отправляет на основной сайт!
    //$this->link->rebase($newbase, $oldbase)
  }
  
}

class Summary
{  
  public $link;
  public $total = "";
  public $subsum = array();

  public $subfrmsum = false; // итого "рубрики" (встречается в рубриках на 1й странице), выводится html таблицей

  function __construct()
  {
    $this->link = new ALink(); 
  }

  function rebase($newbase, $oldbase)
  {  
    $this->link->rebase($newbase, $oldbase);
    foreach($this->subsum as $s) $s->rebase($newbase, $oldbase);
  }
  
  function addSubSum()
  {
    $s = new Summary();
    array_push($this->subsum, $s);
    return $s;
  }
}

class UserInfo
{
  public $friendlink;
  public $messagelink;
  public $id;
  
  public function __construct() 
  {
    $friendlink = new ALink();  // добавить/удалить из друзей
    $messagelink = new ALink();  // посмотреть "переписку"
    $maillink = new ALink(); // почтовый адрес опицональный 
  }

  public function rebase()
  {
  }
}

class GameDev
{
  public $mainlink;
  public $slogan = "GameDev.ru — Разработка игр";

  public $basepath = ""; // должен заканчиваться на слэш, если не пустой.
                         // пусть добавляется к ресурсам (main.css)

  public $title = "";
  public $messages = array();
  public $pages = array();
  public $menus = array();
  public $forums = array();
  public $paths = array();
  public $isGuest = true; // индикатор анонимного (true) или авторизованного (false) пользователя
  public $userlink;
  public $previewhtml = "";
  public $edittext="";
  public $editcheck="";

  public $articles = array();
  public $footerlinks = array();
  public $summaries = array();
  public $summaryText = "";
  
  public $userinfo;

  function __construct()
  {
    $this->userinfo = new UserInfo();
    $this->mainlink = new ALink();
    $this->mainlink->text = "GameDev.ru";
    $this->mainlink->href = "http://gamedev.ru";
    $this->userlink = new ALink();
  }

  public function addMessage() 
  {
    $msg = new Message();
    array_push($this->messages, $msg);
    return $msg; 
  }

  public function addPage()
  {
    $page = new ALink();
    array_push($this->pages, $page);
    return $page;
  }

  public function rebase($newbase, $oldbase = "https://gamedev.ru")
  { 
    foreach ($this->pages as $link)   $link->rebase($newbase, $oldbase);
    foreach ($this->messages as $msg) $msg->rebase($newbase, $oldbase);
    foreach ($this->menus as $mnu)    $mnu->rebase($newbase, $oldbase);
    foreach ($this->forums as $forum) $forum->rebase($newbase, $oldbase);
    foreach ($this->paths as $link)   $link->rebase($newbase, $oldbase);
    foreach ($this->articles as $art) $art->rebase($newbase, $oldbase);
    foreach ($this->footerlinks as $link) $link->rebase($newbase, $oldbase);
    foreach ($this->summaries as $sm) $sm->rebase($newbase, $oldbase);
  }

  public function addMenu()
  {
    $menu = new Menu();
    array_push($this->menus, $menu);
    return $menu;
  } 
  
  public function addForum()
  {
     $forum = new Forum();
     array_push($this->forums, $forum);
     return $forum;
  }
 
  public function addPath()
  {
     $path = new ALink();
     array_push($this->paths, $path);
     return $path;
  }

  public function addArticle()
  {
     $art = new Article();
     array_push($this->articles, $art);
     return $art;
  }

  public function addSummary()
  {
     $sm = new Summary();
     array_push($this->summaries, $sm);
     return $sm;
  }


  public function addFooterLink($text = "", $href = "", $title = "")
  {
     $link = new ALink($text, $href, $title);
     array_push($this->footerlinks, $link);
     return $link;
  }

  public function defaultFooterLinks()
  {
    if ($this->isGuest) $this->addFooterLink("Войти", "https://gamedev.ru/users/?login");
    $this->addFooterLink("Участники", "https://gamedev.ru/members/");
    $this->addFooterLink("Каталог сайтов", "https://gamedev.ru/top/");
    $this->addFooterLink("Категории", "https://gamedev.ru/tags/");
    $this->addFooterLink("Архив новостей", "https://gamedev.ru/news/?adoc=arch");
  }

}

?>