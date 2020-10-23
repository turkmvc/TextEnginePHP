<?php
namespace TextEngine;
class XPathOrItem implements IXPathBlockContainer, IXPathList
{
	public function __construct()
	{
	}
	public function IsSubItem()
	{
		return true;
	}
	public function GetParchar()
	{
		return $this->parchar;
	}
	public function SetParchar($char)
	{
		$this->parchar = $char;
	}
	public function IsXPathPar()
	{
		return false;
	}

	public function Any()
	{
		return true;
	}
	public function IsBlocks()
	{
		return false;
	}
	public function IsOr()
	{
		return true;
	}
}