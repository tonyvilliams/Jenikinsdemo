<?php
// (c) Copyright 2002-2013 by authors of the Tiki Wiki CMS Groupware Project
// 
// All Rights Reserved. See copyright.txt for details and a complete list of authors.
// Licensed under the GNU LESSER GENERAL PUBLIC LICENSE. See license.txt for details.
// $Id: CodeTest.php 44444 2013-01-05 21:24:24Z changi67 $

require_once('lib/wiki-plugins/wikiplugin_code.php');

class WikiPlugin_CodeTest extends PHPUnit_Framework_TestCase
{
	/**
	 * @dataProvider provider
	 */
	public function testWikiPluginCode($data, $expectedOutput, $params = array())
	{
		$this->assertEquals($expectedOutput, wikiplugin_code($data, $params));
	}

	public function provider()
	{
		return array(
			array('', '<pre class="codelisting"  data-wrap="1"  dir="ltr"  style="white-space:pre-wrap; white-space:-moz-pre-wrap !important; white-space:-pre-wrap; white-space:-o-pre-wrap; word-wrap:break-word;" id="codebox1" >~np~~/np~</pre>'),
			array('<script>alert(document.cookie);</script>', '<pre class="codelisting"  data-wrap="1"  dir="ltr"  style="white-space:pre-wrap; white-space:-moz-pre-wrap !important; white-space:-pre-wrap; white-space:-o-pre-wrap; word-wrap:break-word;" id="codebox2" >~np~&lt;script>alert(document.cookie);&lt;/script>~/np~</pre>', array('ishtml' => 1)),
		);
	}
}
