<?php
/* SVN FILE: $Id$ */
/**
 * CakeFirePHP test case
 *
 * 
 *
 * PHP versions 4 and 5
 *
 * CakePHP :  Rapid Development Framework <http://www.cakephp.org/>
 * Copyright 2006-2008, Cake Software Foundation, Inc.
 *								1785 E. Sahara Avenue, Suite 490-204
 *								Las Vegas, Nevada 89104
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @filesource
 * @copyright       Copyright 2006-2008, Cake Software Foundation, Inc.
 * @link            http://www.cakefoundation.org/projects/info/cakephp CakePHP Project
 * @package         cake
 * @subpackage      cake.cake.libs.
 * @since           CakePHP v 1.2.0.4487
 * @version         
 * @modifiedby      
 * @lastmodified    
 * @license         http://www.opensource.org/licenses/mit-license.php The MIT License
 */
App::import('Vendor', 'DebugKit.FireCake');

class TestFireCake extends FireCake {
	var $sentHeaders = array();

	function _sendHeader($name, $value) {
		$_this = FireCake::getInstance();
		$_this->sentHeaders[$name] = $value;
	}
/**
 * Reset the fireCake
 *
 * @return void
 **/
	function reset() {
		$_this = FireCake::getInstance();
		$_this->sentHeaders = array();
		$_this->_messageIndex = 1;
	}
}

class FireCakeTestCase extends CakeTestCase {
/**
 * setup test
 *
 * Fill FireCake with TestFireCake instance.
 *
 * @access public
 * @return void
 */
	function setUp() {
		$this->firecake =& FireCake::getInstance('TestFireCake');
	}

/**
 * test getInstance cheat.
 *
 * If this fails the rest of the test is going to fail too.
 *
 * @return void
 **/
	function testGetInstanceOverride() {
		$instance =& FireCake::getInstance();
		$instance2 =& FireCake::getInstance();
		$this->assertReference($instance, $instance2);
		$this->assertIsA($instance, 'FireCake');
		$this->assertIsA($instance, 'TestFireCake', 'Stored instance is not a copy of TestFireCake, bad things will happen.');
	}	
/**
 * testsetoption
 *
 * @return void
 **/
	function testSetOptions() {
		FireCake::setOptions(array('includeLineNumbers' => false));
		$this->assertEqual($this->firecake->options['includeLineNumbers'], false);
	}
/**
 * test Log()
 *
 * @access public
 * @return void
 */	
	function testLog() {
		FireCake::setOptions(array('includeLineNumbers' => false));
		FireCake::log('Testing');
		$this->assertTrue(isset($this->firecake->sentHeaders['X-Wf-Protocol-1']));
		$this->assertTrue(isset($this->firecake->sentHeaders['X-Wf-1-Plugin-1']));
		$this->assertTrue(isset($this->firecake->sentHeaders['X-Wf-1-Structure-1']));
		$this->assertEqual($this->firecake->sentHeaders['X-Wf-1-Index'], 1);
		$this->assertEqual($this->firecake->sentHeaders['X-Wf-1-1-1-1'], '26|[{"Type":"LOG"},"Testing"]|');
	}

/**
 * test info()
 *
 * @access public
 * @return void
 */	
	function testInfo() {
		FireCake::setOptions(array('includeLineNumbers' => false));
		FireCake::info('I have information');
		$this->assertTrue(isset($this->firecake->sentHeaders['X-Wf-Protocol-1']));
		$this->assertTrue(isset($this->firecake->sentHeaders['X-Wf-1-Plugin-1']));
		$this->assertTrue(isset($this->firecake->sentHeaders['X-Wf-1-Structure-1']));
		$this->assertEqual($this->firecake->sentHeaders['X-Wf-1-Index'], 1);
		$this->assertEqual($this->firecake->sentHeaders['X-Wf-1-1-1-1'], '38|[{"Type":"INFO"},"I have information"]|');
	}

/**
 * test info()
 *
 * @access public
 * @return void
 */	
	function testWarn() {
		FireCake::setOptions(array('includeLineNumbers' => false));
		FireCake::warn('A Warning');
		$this->assertTrue(isset($this->firecake->sentHeaders['X-Wf-Protocol-1']));
		$this->assertTrue(isset($this->firecake->sentHeaders['X-Wf-1-Plugin-1']));
		$this->assertTrue(isset($this->firecake->sentHeaders['X-Wf-1-Structure-1']));
		$this->assertEqual($this->firecake->sentHeaders['X-Wf-1-Index'], 1);
		$this->assertEqual($this->firecake->sentHeaders['X-Wf-1-1-1-1'], '29|[{"Type":"WARN"},"A Warning"]|');
	}
/**
 * test fb() parameter parsing
 *
 * @return void
 **/
	function testFbParameterParsing() {
		FireCake::fb('Test');
		$this->assertEqual($this->firecake->sentHeaders['X-Wf-1-1-1-1'], '23|[{"Type":"LOG"},"Test"]|');

		FireCake::fb('Test', 'warn');
		$this->assertEqual($this->firecake->sentHeaders['X-Wf-1-1-1-2'], '24|[{"Type":"WARN"},"Test"]|');

		FireCake::fb('Test', 'Custom label', 'warn');
		$this->assertEqual($this->firecake->sentHeaders['X-Wf-1-1-1-3'], '47|[{"Type":"WARN","Label":"Custom label"},"Test"]|');

		$this->expectError();
		$this->assertFalse(FireCake::fb('Test', 'Custom label', 'warn', 'more parameters'));

		$this->assertEqual($this->firecake->sentHeaders['X-Wf-1-Index'], 3);
	}
/**
 * testClientExtensionDetection.
 *
 * @return void
 **/
	function testDetectClientExtension() {
		$back = env('HTTP_USER_AGENT');
		$_SERVER['HTTP_USER_AGENT'] = 'Mozilla/5.0 (Macintosh; U; Intel Mac OS X 10.5; en-US; rv:1.9.0.4) Gecko/2008102920 Firefox/3.0.4 FirePHP/0.2.1';
		$this->assertTrue(FireCake::detectClientExtension());
		
		$_SERVER['HTTP_USER_AGENT'] = 'Mozilla/5.0 (Macintosh; U; Intel Mac OS X 10.5; en-US; rv:1.9.0.4) Gecko/2008102920 Firefox/3.0.4 FirePHP/0.0.4';
		$this->assertFalse(FireCake::detectClientExtension());
		
		$_SERVER['HTTP_USER_AGENT'] = 'Mozilla/5.0 (Macintosh; U; Intel Mac OS X 10.5; en-US; rv:1.9.0.4) Gecko/2008102920 Firefox/3.0.4';
		$this->assertFalse(FireCake::detectClientExtension());
		$_SERVER['HTTP_USER_AGENT'] = $back;
	}
/**
 * reset the FireCake counters and headers.
 *
 * @access public
 * @return void
 */
	function tearDown() {
		TestFireCake::reset();
	}
}
?>