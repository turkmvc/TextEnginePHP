<?php

class SavedMacros
{
	/** @var array */
	private $macros;
	public function GetMacro($name)
	{
		return array_value($name, $this->macros);
	}
	public  function SetMacro($name, &$tag)
	{
		$this->macros[$name] = $tag;
	}
}
