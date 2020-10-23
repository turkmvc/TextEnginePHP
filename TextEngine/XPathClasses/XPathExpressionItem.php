<?php
namespace TextEngine;
class XPathExpressionItem implements IXPathExpressionItem
{
	private $value;
	public function GetValue()
	{
		return $this->value;
	}
	public function SetValue($value)
	{
		$this->isNumeric = false;
		$this->isBool = false;
		if (!$this->IsOperator() && $this->QuotChar == "\0")
		{	
			if (is_numeric($value))
			{
				$this->isNumeric = true;
			}
			else if ($value == "true" || $value == 'false')
			{
				$this->isBool = true;
			}
		}
		$this->value = $value;
	}
	public $QuotChar;
	private $isNumeric;
	private $isBool;
	private $isOperator;
	private $isVariable;
	public function IsNumeric()
	{
		return $this->isNumeric;
	}
	public function IsBool()
	{
		return $this->isBool;
	}
	public function SetIsOperator($value)
	{
		$this->isOperator = $value;
	}
	public function IsOperator()
	{
		return $this->isOperator;
	}
	public function IsVariable()
	{
		return !$this->IsOperator() && !$this->IsNumeric() && !$this->IsBool() &&  $this->QuotChar == "\0";
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
	public function IsItemContainer()
	{
		return false;
	}
}