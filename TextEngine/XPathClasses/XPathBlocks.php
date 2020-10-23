<?php
namespace TextEngine;
class XPathBlocks implements IXPathList, \ArrayAccess
{
	private $container = array();
	public function IsBlocks()
	{
		return true;
	}
	public function Any()
	{
		return count($this->container) > 0;
	}
	public function IsOr()
	{
		return false;
	}
    public function offsetSet($offset, $value) {
        if (is_null($offset)) {
            $this->container[] = $value;
        } else {
            $this->container[$offset] = $value;
        }
    }
    
    public function offsetExists($offset) {
        return isset($this->container[$offset]);
    }
    
    public function offsetUnset($offset) {
        unset($this->container[$offset]);
    }
    
    public function offsetGet($offset) {
        return isset($this->container[$offset]) ? $this->container[$offset] : null;
    }
	public function AddItem(&$item)
	{
		$this->container[] = &$item;
	}
	public function GetCount()
	{
		return count($this->container);
	}
}