<?php
/**
 * Limb Web Application Framework
 *
 * @link http://limb-project.com
 *
 * @copyright  Copyright &copy; 2004-2007 BIT
 * @license    LGPL http://www.gnu.org/copyleft/lesser.html
 * @version    $Id: lmbHandleTest.class.php 4987 2007-02-08 15:35:15Z pachanga $
 * @package    core
 */

lmb_require('limb/core/src/lmbDelegate.class.php');

class DelegateTestingStub
{
  public $instance_arg;
  public $instance_called = false;

  static public $static_arg;
  static public $static_called = false;

  function instanceMethod($arg)
  {
    $this->instance_arg = $arg;
    $this->instance_called = true;
  }

  function instanceReturningMethod($arg)
  {
    $this->instance_called = true;
    return $arg;
  }

  function staticMethod($arg)
  {
    self :: $static_arg = $arg;
    self :: $static_called = true;
  }
}

function DelegateTestingStubFunction($arg = null)
{
  static $remember;
  if($arg)
    $remember = $arg;
  else
    return $remember;
}

class lmbDelegateTest extends UnitTestCase
{
  function testDelegateToObject()
  {
    $stub = new DelegateTestingStub();
    $this->assertFalse($stub->instance_called);
    $delegate = new lmbDelegate($stub, 'instanceMethod');
    $delegate->invoke('bar');
    $this->assertTrue($stub->instance_called);
    $this->assertEqual($stub->instance_arg, 'bar');
  }

  function testInvalidObjectDelegatee()
  {
    $stub = new DelegateTestingStub();
    $delegate = new lmbDelegate($stub, 'xxxx');
    try
    {
      $delegate->invoke();
      $this->assertTrue(false);
    }
    catch(lmbException $e){}
  }

  function testDelegateToStaticClass()
  {
    $delegate = new lmbDelegate('DelegateTestingStub', 'staticMethod');
    $this->assertFalse(DelegateTestingStub :: $static_called);
    $delegate->invoke('bar');
    $this->assertTrue(DelegateTestingStub :: $static_called);
    $this->assertEqual(DelegateTestingStub :: $static_arg, 'bar');
  }

  function testInvalidStaticDelegatee()
  {
    $delegate = new lmbDelegate('DelegateTestingStubFunction', 'xxxx');
    try
    {
      $delegate->invoke();
      $this->assertTrue(false);
    }
    catch(lmbException $e){}
  }

  function testDelegateToFunction()
  {
    $delegate = new lmbDelegate('DelegateTestingStubFunction');
    $delegate->invoke('bar');
    $this->assertEqual(DelegateTestingStubFunction(), 'bar');
  }

  function testInvalidFunctionDelegatee()
  {
    $delegate = new lmbDelegate('Foo' . mt_rand());
    try
    {
      $delegate->invoke();
      $this->assertTrue(false);
    }
    catch(lmbException $e){}
  }

  function testDelegateToPHPCallback()
  {
    $stub = new DelegateTestingStub();
    $this->assertFalse($stub->instance_called);
    $delegate = new lmbDelegate(array($stub, 'instanceMethod'));
    $delegate->invoke('bar');
    $this->assertTrue($stub->instance_called);
    $this->assertEqual($stub->instance_arg, 'bar');
  }

  function testInvokeAll()
  {
    $s1 = new DelegateTestingStub();
    $s2 = new DelegateTestingStub();
    $s3 = new DelegateTestingStub();

    $d1 = new lmbDelegate($s1, 'instanceMethod');
    $d2 = new lmbDelegate($s2, 'instanceMethod');
    $d3 = new lmbDelegate($s3, 'instanceMethod');

    lmbDelegate :: invokeAll(array($d1, $d2, $d3), 'bar');

    $this->assertTrue($s1->instance_called);
    $this->assertEqual($s1->instance_arg, 'bar');
    $this->assertTrue($s2->instance_called);
    $this->assertEqual($s2->instance_arg, 'bar');
    $this->assertTrue($s3->instance_called);
    $this->assertEqual($s3->instance_arg, 'bar');
  }

  function testInvokeChain()
  {
    $s1 = new DelegateTestingStub();
    $s2 = new DelegateTestingStub();
    $s3 = new DelegateTestingStub();

    $d1 = new lmbDelegate($s1, 'instanceMethod');
    $d2 = new lmbDelegate($s2, 'instanceReturningMethod');//returns argument
    $d3 = new lmbDelegate($s3, 'instanceMethod');

    lmbDelegate :: invokeChain(array($d1, $d2, $d3), 'bar');

    $this->assertTrue($s1->instance_called);
    $this->assertEqual($s1->instance_arg, 'bar');
    $this->assertTrue($s2->instance_called);
    $this->assertFalse($s3->instance_called);
    $this->assertNull($s3->instance_arg);
  }
}
?>