<?php
namespace TextEngine;
class XPathExpressionSubItem implements IXPathExpressionItem
{
	private $parchar = '(';
	public $XPathExpressions;
	public function __construct()
	{
		$this->XPathExpressions = array();
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
	public function IsItemContainer()
	{
		return true;
	}
	
}