<?php
/*
 * Limb PHP Framework
 *
 * @link http://limb-project.com
 * @copyright  Copyright &copy; 2004-2007 BIT(http://bit-creative.com)
 * @license    LGPL http://www.gnu.org/copyleft/lesser.html
 */
lmb_require('limb/core/src/lmbCollectionDecorator.class.php');
lmb_require('limb/core/src/lmbClassPath.class.php');

/**
 * class lmbARRecordSetDecorator.
 *
 * @package active_record
 * @version $Id: lmbARRecordSetDecorator.class.php 5997 2007-06-18 12:27:21Z pachanga $
 */
class lmbARRecordSetDecorator extends lmbCollectionDecorator
{
  protected $class_path;
  protected $conn;

  function __construct($record_set, $class_path = '', $conn = null)
  {
    $this->class_path = $class_path;
    $this->conn = $conn;

    parent :: __construct($record_set);
  }

  function setClassPath($class_path)
  {
    $this->class_path = $class_path;
  }

  function current()
  {
    if(!$this->class_path)
      throw new lmbException('ActiveRecord class path is not defined');

    if(!$record = parent :: current())
      return null;

    return $this->_createObjectFromRecord($record);
  }

  protected function _createObjectFromRecord($record)
  {
    $object = $this->_createObject($record);
    $object->loadFromRecord($record);
    return $object;
  }

  protected function _createObject($record)
  {
    if($path = $record->get(lmbActiveRecord :: getInheritanceField()))
    {
      $class = end(lmbActiveRecord :: decodeInheritancePath($path));
      if(!class_exists($class))
        throw new lmbException("Class '$class' not found");
      return new $class(null, $this->conn);
    }
    else
      return lmbClassPath :: create($this->class_path, array(null, $this->conn));
  }

  function at($pos)
  {
    if(!$record = parent :: at($pos))
      return null;

    return $this->_createObjectFromRecord($record);
  }
}

?>
