 var timeout         = 0;
var closetimer		= 0;
var ddmenuitem      = 0;

function jsddm_open()
{	jsddm_canceltimer();
	jsddm_close();
	ddmenuitem = $(this).find('ul').eq(0).css('display', 'block');}

function jsddm_close()
{	if(ddmenuitem) ddmenuitem.css('display', 'none')}

function jsddm_timer()
{	closetimer = window.setTimeout(jsddm_close, timeout);}

function jsddm_canceltimer()
{	if(closetimer)
	{	window.clearTimeout(closetimer);
		closetimer = null;}}

$(document).ready(function()
{	$('#navigation > ul > li').bind('mouseover', jsddm_open);
	$('#navigation > ul > li').bind('mouseout',  jsddm_timer);
});

document.onclick = jsddm_close;