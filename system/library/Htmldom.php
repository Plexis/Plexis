<?php
/* 
| --------------------------------------------------------------
| 
| Frostbite Framework
|
| --------------------------------------------------------------
|
| Author:       Steven Wilson
| Copyright:    Copyright (c) 2012, Steven Wilson
| License:      GNU GPL v3
|
| ---------------------------------------------------------------
| Class: HtmlDom
| ---------------------------------------------------------------
|
| An HTML dom class for html manipulation
|
*/
namespace Library;

class Htmldom
{
	protected $source = null;
	
	public function load($html)
	{
		$this->source = $html;
		$this->dom = new \DOMDocument();
		$this->dom->loadHTML($html);
	}

/*
| ---------------------------------------------------------------
| Method: find()
| ---------------------------------------------------------------
|
| Finds an html element, and returns an htmldom nod object
|
*/
    public function find($element, $id = 0)
    {
		$mode = substr($element,0, 1);
		if($mode == '#')
		{
			var_dump( $this->dom->getElementById( substr($element, 1) ) ); die();
		}
		elseif($mode == '.')
		{
			$finder = new \DomXPath($this->dom);
			$classname = substr($element, 1);
			$nodes = $finder->query("//*[contains(@class, '$classname')]");
			var_dump($nodes); die();

		}
		else
		{
			var_dump( $this->dom->getElementById( substr($element, 1) ) ); die();
		}
		
		$pattern = "/([\w-:\*]*)(?:\#([\w-]+)|\.([\w-]+))?(?:\[@?(!?[\w-:]+)(?:([!*^$]?=)[\"']?(.*?)[\"']?)?\])?([\/, ]+)/is";
        preg_match_all($pattern, $element.' ', $matches, PREG_SET_ORDER);
		// var_dump($matches);
		
		$selectors = array();
        $result = array();
        //print_r($matches);

		// Convert each match to an easy array of data
        foreach ($matches as $m) 
		{
            $m[0] = trim($m[0]);
            if (empty($m[0]) || $m[0] == '/' || $m[0] == '//') continue;
			
            // for browser generated xpath
            if ($m[1]==='tbody') continue;

            list($tag, $key, $val, $exp, $no_key) = array($m[1], null, null, '=', false);
            if (!empty($m[2])) {$key='id'; $val=$m[2];}
            if (!empty($m[3])) {$key='class'; $val=$m[3];}
            if (!empty($m[4])) {$key=$m[4];}
            if (!empty($m[5])) {$exp=$m[5];}
            if (!empty($m[6])) {$val=$m[6];}

            //elements that do NOT have the specified attribute
            if (isset($key[0]) && $key[0] === '!') {$key=substr($key, 1); $no_key=true;}

            $result[] = array($tag, $key, $val, $exp, $no_key);
            if (trim($m[7]) === ',') {
                $selectors[] = $result;
                $result = array();
            }
        }
        if (count($result)>0) $selectors[] = $result;
		
        var_dump( $selectors );
    }
}
// EOF