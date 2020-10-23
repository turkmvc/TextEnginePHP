<?php
namespace TextEngine;
class XPathBlock implements IXPathExpressionItem
{
	public function __construct()
	{
		$this->XPathExpressions = array();
	}
	public $IsAttributeSelector;
	public $BlockType;
	public $BlockName;
	public $XPathExpressions;
	public $Parent;
	public function IsSubItem()
	{
		return true;
	}
	public function GetParchar()
	{
		return "\0";
	}
	public function SetParchar($char)
	{
			
	}
	public function IsXPathPar()
	{
		return false;
	}
	public function IsItemContainer()
	{
		return true;
	}
}