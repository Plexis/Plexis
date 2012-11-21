<?php
// Expiration time for cookies set with the SetCookie method
$cookieExpireTime = (60 * 60 * 24 * 365);

// Blacklisted tags
$tagBlacklist = array(
    'applet', 
    'body', 
    'bgsound', 
    'base', 
    'basefont', 
    'embed', 
    'frame', 
    'frameset', 
    'head', 
    'html', 
    'id', 
    'iframe', 
    'ilayer', 
    'layer', 
    'link', 
    'meta', 
    'name', 
    'object', 
    'script', 
    'style', 
    'title', 
    'xml'
);

// Blacklisted attributes
$attrBlacklist = array('action', 'background', 'codebase', 'dynsrc', 'lowsrc');