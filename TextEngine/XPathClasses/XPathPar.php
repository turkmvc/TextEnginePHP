<?php
namespace TextEngine;
class XPathPar implements IXPathBlockContainer, IXPathList, IXPathExpressionItem
{
	public $Parent;
	public $XPathBlockList;
	public $XPathExpressions;
	public function __construct()
	{
		$this->XPathExpressions = array();
		$this->XPathBlockList =  array();
	}
	public function IsSubItem()
	{
		return false;
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
		return true;
	}

	public function Any()
	{
		return count($this->XPathBlockList > 0) || count($this->XPathExpressions);
	}
	public function IsBlocks()
	{
		return false;
	}
	public function IsOr()
	{
		return false;
	}
	public function IsItemContainer()
	{
		return true;
	}
}