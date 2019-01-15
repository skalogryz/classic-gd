
function Skif_Email(auth,em) {
	em = em.substring(3,em.length-3);
	auth = auth.substring(4,auth.length-4);
	document.write('<a href="mailto:',em,'" title="Защищён от спам-роботов">',auth,'</a>');
}

function check_submit(form) {
	for (i=0; i<form.length; i++) {
		var tempobj=form.elements[i]
		if(tempobj.type.toLowerCase()=="submit") {
			tempobj.disabled=true
		}
	}
}

function ins_tag(_tag_start, _tag_end) {
	document.postform.text.focus();	
	var area = document.postform.text;
	if (document.getSelection) {
		var insertText = _tag_start + area.value.substring(area.selectionStart, area.selectionEnd) + _tag_end;
		var curPos = area.selectionStart + insertText.length;
		area.value=area.value.substring(0, area.selectionStart) + insertText + area.value.substring(area.selectionEnd, area.value.length);
		area.selectionEnd = curPos;
	}
	else {
		document.selection.createRange().text=_tag_start + document.selection.createRange().text + _tag_end;;
	}
	document.postform.text.focus();
}


function get_sel_text() {
	var area = document.postform.text;
	if (document.getSelection) {
		return area.value.substring(area.selectionStart, area.selectionEnd);
	}
	else if (document.selection) {
		return document.selection.createRange().text;
	}
	return '';
}


function ins_incursor(_text) {
	document.postform.text.focus();
	var area = document.postform.text;
	if ((area.selectionStart)||(area.selectionStart=='0')) {
		var curPos = area.selectionStart + _text.length;
		area.value=area.value.substring(0, area.selectionStart)+ _text + area.value.substring(area.selectionEnd, area.value.length);
		area.selectionEnd = curPos;
	}
	else if (document.selection) {
		document.selection.createRange().text=_text;
	}
	document.postform.text.focus();
}


function delete_lastChar() {
	var area = document.postform.text;
	if ((area.selectionStart)) {
		var curPos = area.selectionStart - 1;
		area.value = area.value.substring(0, curPos) + area.value.substring(area.selectionEnd, area.value.length);
		area.selectionEnd = curPos;
	}
}


function pr(text) {
	document.postform.text.focus();
	document.postform.text.value += text;
}


function ins_quote(event) {
	var maxlen = 80;

	event.returnValue = false;
	if (event.preventDefault)
		event.preventDefault();

	var nick = '';

	var eventTarget = event.target;
	if (!eventTarget)
		eventTarget = event.srcElement;

	if (eventTarget)
		nick = eventTarget.getAttribute('data-nick');

	var text = skif.getSelText();
	if (text == '' && skif.storedQuote != '')
		text = skif.storedQuote;

	pr("[b]"+nick+"[/b]\n", 1);

	if (text == '')
		return;

	var s = text.split('\n');
	var len = s.length;
	var i, j, begstr, str, cmts, si;

	text = "";

	for (i=0; i<len; i++)
	{
		ti=(s[i].indexOf(" >")==0 ? 1 : 0);
		tend=s[i].length-1;
		while (s[i].charAt(tend)==' ') tend--;
		if (ti || tend<s[i].length-1) s[i]=s[i].substring(ti,tend+1);
		cmts = "";
		if (((si = s[i].indexOf(' ')) == -1) || (s[i] == ""))
		{
			text += ("> " + s[i] + "\n");
			continue;
		}
		for (j=0; j<s[i].length; j++)
		{
			if (s[i].charAt(j) == '>')
				cmts += "> ";
			else
				break;
		}
		str = cmts;
		s[i] = s[i].slice(cmts.length);
		s[i] += " ";
		while ((si = s[i].indexOf(' ')) != -1)
		{
			begstr = s[i].slice(0, si);
			s[i] = s[i].slice(si+1);
			if (begstr.length + str.length >= maxlen)
			{
				if (str.slice(cmts.length) != '')
				{
					str = str.slice(0, str.length-1);
					text += ("> " + str + "\n");
				}
				str = cmts + begstr + " ";
				continue;
			}
			if (begstr.length < maxlen)
				str += (begstr + " ");
			else
				str = cmts + begstr + " ";
			if (str.length >= maxlen)
			{
				str = str.slice(0, str.length-1);
				text += ("> " + str + "\n");
				str = cmts;
			}
			if (s[i].length == 0)
				break;
		}
		str = str.slice(0, str.length-1);
		if (str.slice(cmts.length) != '')
		{
			text += ("> " + str + "\n");
		}
	}
	pr(text);
}

function p_nick(text) {
	pr("[b]"+text+"[/b]\n", 1);
}


function showSpoiler(spoilerId, show) {
	document.getElementById('spoilerHead' + spoilerId).style.display = show == 1 ? 'none' : 'block';
	document.getElementById('spoiler' + spoilerId).style.display = show == 1 ? 'block' : 'none';
}


function showImgSpoiler(spoilerId) {
	var spoilerHead = document.getElementById('spoilerHead' + spoilerId);
	spoilerHead.parentNode.removeChild(spoilerHead);
	var spoiler = document.getElementById('spoiler' + spoilerId);
	spoiler.setAttribute('src', spoiler.getAttribute('data-src'));
	spoiler.style.display = "inline";
	if (event.preventDefault)
		event.preventDefault();
}


function Url(url) {
	this.url = url;

	var fragments = url.split('#');
	this.fragment = fragments.length > 1 ? fragments[1] : '';

	var queries = fragments[0].split('?'); 
	this.query = queries.length > 1 ? queries[1] : '';

	var protocols = queries[0].split(':');
	this.protocol = protocols.length > 1 ? protocols[0] : '';

	this.address = queries[0].substring(this.protocol.length);
	if (this.address.length>3 && this.address.substr(0,3) == '://')
		this.address = this.address.substr(3);

	this.getDomain = function() {
		var domains = this.address.split('/')[0].split('.');
		if (domains.length >= 2)
			return domains[domains.length-2] + '.' + domains[domains.length-1];
		return '';
	}

	this.getParam = function(param) {
		var params = this.query.split('&'); 
		for(var i=0; i<params.length; i++) {
			var var_value = params[i].split('=');
			if (var_value[0] == param && var_value.length > 1)
				return var_value[1];
		} 
	  return ''; 
	}

	this.getPath = function() {
		var paths = this.address.split('/');
		if (paths.length > 1)
			return this.address.substring(paths[0].length + 1);
		return '';
	}
}


function but_YouTube() {
	var text = get_sel_text();
	var url = new Url(text);

	var v = url.getParam('v');
	var t = url.getParam('t');
	var time = '';

	if (t.length > 0) {
		var times = t.split('s');
		times = times[0].split('m');
		if (times.length>1)
			times[0] = 60 * times[0] + 1 * times[1];
		time = '#t=' + times[0];
	}

	if (url.getDomain() == 'youtube.com' && v != '') {
		text = v;
	}
	else if (url.getDomain() == 'youtu.be') {
		text = url.getPath();
	}
	else {
		time = '';
	}

	ins_incursor('[youtube=' + text + time + ']\n');
}


var lastMinusPressed = 0;
function key_pressed(event) {
	if (!event.ctrlKey && !event.shiftKey) {
		var keyID = (event.charCode) ? event.charCode : ((event.which) ? event.which : event.keyCode);
		if (keyID==189) { // '-'
			if (lastMinusPressed) {
				var d = new Date();
				var t = d.getTime();
				if (t - lastMinusPressed < 300) {
					lastMinusPressed = 0;
					delete_lastChar();
					ins_incursor('—');
					event.preventDefault();
				}
				else {
					lastMinusPressed = t;
				}
			}
			else {
				var d = new Date(); 
				lastMinusPressed = d.getTime();
			}
		}
		else if (lastMinusPressed) {
			lastMinusPressed = 0;
		}

		if (keyID==9) { // TAB
			ins_incursor('	');
			event.preventDefault();
		}
	}

	if (!event.ctrlKey || !event.shiftKey)
		return;

	if (event.keyCode==10 || event.keyCode==13) {
		document.postform.submit();
	}
	if (event.keyCode==2 || event.keyCode==98) {
		ins_tag('[b]', '[/b]');
	}
}


function editpasted(event)
{
  var str = event.clipboardData.getData("text/plain");
  var d = skif.domain;
  var trg = "https://gamedev.ru/";
  var any = false;
  while (str.indexOf(d)>=0) {
    any = true;
    str = str.replace(d, trg);
  }
  if ((any)&&(event.target)&&(event.target.setRangeText)) {
    event.preventDefault();
    //var trg =  event.target;
    //trg.setRangeText(str, trg.selectionStart, trg.selectionEnd, 'end');
    document.execCommand('insertText', false, str);
  }
}

function Core_OutAreaTags() {
	var s = document.getElementById('areatags');
	if (s == null)
		return;

	s.innerHTML = '<a href="javascript:ins_tag(\'[b]\',\'[/b]\')" title="[b][/b] Жирный шрифт (Ctrl+Shift+B)">[b]</a> '+
	'<a href="javascript:ins_tag(\'[i]\',\'[/i]\')" title="[i][/i] Наклонный шрифт">[i]</a> ' +
	'<a href="javascript:ins_tag(\'[s]\',\'[/s]\')" title="[s][/s] Зачёркнутый текст">[s]</a> ' +
	'<a href="javascript:ins_tag(\'[code]\n\',\'\n[/code]\n\')" title="[code] Код: Моноширинный шрифт">[code]</a> ' +
	'<a href="javascript:ins_tag(\'[code=cpp]\n\',\'\n[/code]\n\')" title="[code=cpp] Код: с подсветкой для C++">[c++]</a> ' +
	'<a href="javascript:ins_tag(\'[code=pas]\n\',\'\n[/code]\n\')" title="[code=pas] Код: с подсветкой для Pascal">[pas]</a> ' +
	'<a href="javascript:ins_tag(\'[quote]\n\',\'\n[/quote]\n\')" title="[quote] Цитата: Выделенный блок текста">[quote]</a> ' +
	'<a href="javascript:ins_tag(\'[spoiler]\n\',\'\n[/spoiler]\n\')" title="[spoiler] Спойлер: спрятать текст">[spoiler]</a> ' +
	'<a href="javascript:ins_tag(\'[offtop]\n\',\'\n[/offtop]\n\')" title="[offtop] Тусклый текст с уменьшенным шрифтом">[offtop]</a> ' +
	'<a href="javascript:ins_tag(\'[img=\',\']\')" title="[img]: Внешнее изображение. Можно выделить URL на изображение">[img]</a> ' +
	'<a href="javascript:but_YouTube()" title="YouTube: Можно выделить URL или код из URL на видео">[youtube]</a>' +
	' | <a target="_blank" href="http://skif.qrim.ru/docs/help">Справка по тегам</a>' +
	' | <a target="_blank" href="' + skif.domain  + 'files/?upload">Загрузка изображений и файлов</a>';
}


function Core_AddForumQuotes() {
	var elems = document.getElementsByTagName('a');
	for (var i = 0; i < elems.length; i++ ) {
		var el = elems[i];
		if (el.className == 'fquote') {
			skif.addEvent(el, 'click', ins_quote, true);
 			el.addEventListener("mousedown", skif.storeQuote, true);
			if (skif.isTouch)
				el.addEventListener("touchstart", skif.storeQuote, true);
		}
	}
}

function Core_OnResize() {
	var mh = document.getElementById('main_body').offsetHeight;
	var rh = document.getElementById('right').offsetHeight;
	document.getElementById('main_add').style.paddingBottom = mh < rh ? (rh - mh) + 'px' : '0px';
}


function Core_GetXML(url) {
	var xml;
	if (window.XMLHttpRequest) {
		xml = new window.XMLHttpRequest();
		xml.open("GET", url, false);
		xml.setRequestHeader("Cache-Control", "no-cache");
		xml.send();
		return xml.responseXML;
	}
	else if (window.ActiveXObject) {
		xml = new ActiveXObject("Microsoft.XMLDOM");
		xml.async = false;
		xml.load(url);
		return xml;
	}
	else {
		return null;
	}
}


function Core_GetAttribs(xml, item) {
	items = xml.getElementsByTagName(item);
	if (!items)
		return null;
	item = items[0];
	if (!item)
		return null;
	var ret = new Object();
	for (var i = 0; i < item.attributes.length; i++)
		ret[item.attributes[i].name] = item.attributes[i].value;
	return ret;
}


var Buttons = {
	add: function (el, callback)
	{
		el.onclick = callback;
	}
}


var Scripts = {
	files: [],

	include: function(url, callback) {
		var head = document.getElementsByTagName('head')[0];
		var script = document.createElement('script');
		script.type = 'text/javascript';
		script.src = url;
		script.onreadystatechange = function () {
			if (callback!=undefined && this.readyState == 'complete')
				callback();
		}
		script.onload = callback;
		head.appendChild(script);
		return script;
	},

	load: function (file, fn) {
		var isCalled = false;
		this.callback = function () {
			if (isCalled)
				return;
			isCalled = true;
			Scripts.files[file] = true;
			fn();
		}

		if (this.files[file] != true) {
			this.include(skif.domain + file, this.callback);
			console.log('load script: ', file);
		}
		else {
			fn();
		}
	}
};


function Core_AddSound(id, file) {
	Scripts.load('_js/swfobject.js', function () {
		var sx = new SWFObject('/_js/mediaplayer.swf', 'apv', '200', '14', '7');
		sx.addVariable('width', '200');
		sx.addVariable('height', '17');
		sx.addVariable('file', skif.domain + 'files/sounds/' + file);
		sx.write(id);
	});
}


var Search = {
	submit: function (form) {
                var dom = (!skif.searchdomain) ? document.domain : skif.searchdomain;
		window.location.href = 'https://google.com/search?q=' + encodeURIComponent('site:' + dom + ' ' + form.q.value);
		return false;
	},

	focused: function (isFocus) {
		var srch = document.getElementById('search');
		var inp = document.getElementById('search-input');
		if (isFocus) {
			srch.className = "search search_focused";
			if (inp.value == "Поиск")
				inp.value = "";
		}
		else {
			if (inp.value == "") {
				inp.value = "Поиск";
				srch.className = "search";
			}
		}
	},

	init: function () {
		var e = document.getElementById('search');
		if (e != null) {
			e.innerHTML = '<form method="post" onSubmit="return Search.submit(this);">' + 
				'<button type="submit"><span>Q</span></button>' + 
				'<div style="overflow:hidden; padding-right:.5em;"><input id="search-input" type="text" name="q" maxlength="255" accesskey="s" onfocus="Search.focused(true);" onblur="Search.focused(false);" /></div></form>';
			this.focused(false);
		}
	}
};


var skif = {
	domain: location.protocol + '//' + document.domain + '/',
	isTouch: !!('ontouchstart' in document),

	addEvent: function (obj, type, fn, useCapture) {
		if (obj.addEventListener) {
			obj.addEventListener(type, fn, useCapture);
		}
		else if (obj.attachEvent) {
			return obj.attachEvent('on' + type, fn);
		}
	},

	//share: function (event) {
	//	Scripts.load('_js/skif_social.js?v=2', function () {
	//		Social_Process(event.target);
	//	});
	//},

	getSelText: function() {
		if (window.getSelection)
			return window.getSelection().toString();
		if (document.getSelection)
			return document.getSelection().toString();
		return document.selection.createRange().text;
	},

	storeQuote: function(event) {
		skif.storedQuote = skif.getSelText();
	},

	HTMLfn: function(el, fn) {
		var e = document.getElementById(el);
		if (e != null && fn != null)
			e.innerHTML = fn();
	},

	run: function() {
		e = document.getElementById('login');
		if (e != null)
			e.innerHTML = '<form method="post" action="' + skif.domain + 'users/?login"><input type="hidden" name="action" value="autopost" /><a href="' + skif.domain + 'users/?login">Логин</a>:<input class="thinbut" name="nick" value="" size="10" /> Пароль:<input class="thinbut" name="pass" type="password" value="" size="10" /> <input class="thinbut" id="_gdr_post" name="_gdr_post" type="submit" value="Войти" /></form>';

		Search.init();

		e = document.getElementById('ad_content');
		if (e != null)
			e.style.display = "block";

		e = document.getElementById('pda');
		if (e != null)
			e.innerHTML = '<form method="post" action="' + skif.domain + '_sys/"><p><input type="hidden" name="action" value="pda" /><input type="submit" value="Мобильная версия" class="thinbut" /></p></form>';

		Core_AddForumQuotes();
		Core_OutAreaTags();

		skif.HTMLfn('social', skif.site.social);
		skif.HTMLfn('counters', skif.site.counters);

		e = document.getElementById('right');
		if (e != null && e.offsetWidth > 0)
		{
			skif.addEvent(window, 'resize', Core_OnResize, true);
			Core_OnResize();
		}
	},

	load: function() {

	},

	// private
	storedQuote: '',
	site: null
};

//skif.addEvent(window, 'load', skif.load);

skif.site = {
	name: 'GameDev.ru',

	social: function() {
		return '<a class="vk" href="https://vk.com/gamedev_ru" target="_blank"></a> ' +
			'<a class="facebook" href="https://www.facebook.com/gamedevru" target="_blank"></a> ' +
			'<a class="twitter" href="https://twitter.com/ru_gamedev" target="_blank"></a> ' +
			'<a class="googleplus" href="https://plus.google.com/+GamedevRus/" target="_blank"></a> ' +
			'<a class="livejournal" href="http://gamedev-ru.livejournal.com/" target="_blank"></a>';
	},

	counters: function() {
		return '';
		/*
		var a = ';r=' + escape(document.referrer)
		s = screen;
		a += ';s=' + s.width + '*' + s.height;
		a += ';d=' + (s.colorDepth ? s.colorDepth : s.pixelDepth);

		return '<a href="http://top.mail.ru/jump?from=261892"><img src="http://top.list.ru/counter?id=261892;t=56;' + a + 
			';rand=' + Math.random() + '" alt="Рейтинг@Mail.ru"' + ' border="0" height="31" width="88" /><\/a>';
			*/
	}
};
