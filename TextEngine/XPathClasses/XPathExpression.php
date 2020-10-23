<?php
namespace TextEngine;
class XPathExpression
{
	public static $priotirystop =
	[
		"and",
		"&&",
		"||",
		"|",
		"==",
		"=",
		">",
		"<",
		">=",
		"<=",
		"or",
		"+",
		"-",
		","
	];
	private static $operators = 
	[
		"and",
		"mod",
		"div",
		"or",
		"!=",
		"==",
		">=",
		"<=",
		"&&",
		"||",
		"+",
		"-",
		"*",
		"/",
		"%",
		"=",
		"<",
		">",
		","
	];
	public $XPathExpressionItems;
	public function __construct()
	{
		$this->XPathExpressionItems = array();
	}
	public $Parent;
	public static function Parse($input, &$istate)
	{
		$inquot = false;
		$quotchar = "\0";
		$inspec = false;
		$curstr = '';
		$elem = new XPathExpression();
		$inputlen = strlen($input);
		if($input[$istate] == '['  || $input[$istate] == '(')
		{
			$istate++;
		}
		for ($i = $istate; $i < $inputlen; $i++)
		{
			$cur = $input[$i];
			$next = ($i + 1 < $inputlen) ? $input[$i + 1] : '\0';
			if ($inspec)
			{
				$inspec = false;
				$curstr .= $cur;;
				continue;
			}
			if (!$inquot && $cur == "'" || $cur == '"')
			{
				if (strlen($curstr) > 0 || $quotchar != "\0")
				{
					$tempelem = new XPathExpressionItem();
					$tempelem->QuotChar = $quotchar;
					$tempelem->SetIsOperator($quotchar == "\0" && in_array($curstr, self::$operators));
					$tempelem->SetValue($curstr);
					$elem->XPathExpressionItems[] = &$tempelem;
					unset($tempelem);
				}
				$curstr = "";
				$inquot = true;
				$quotchar = $cur;
				//curstr.Clear();
				continue;
			}
			if ($inquot)
			{
				if ($cur == $quotchar)
				{
					$inquot = false;
				}
				else
				{
					$curstr .= $cur;
				}
				continue;
			}
			if ($cur == "\\")
			{
				$inspec = true;
				continue;
			}
			if ($cur == ' ' && $next == ' ') continue;
			if($cur == '-' || $cur == '/')
			{
				
				if(strlen($curstr) > 0 && !is_numeric($curstr))
				{
					$curstr .= $cur;
					continue;
				}
			}
			if($cur != ' ' && strlen($curstr) > 0)
			{
				if(!in_array($cur, self::$operators) && in_array($curstr, self::$operators) || in_array($cur, self::$operators) && !in_array($curstr, self::$operators))
				{
					if(strlen($curstr) > 0 || $quotchar != "\0")
					{
						$tempelem = new XPathExpressionItem();
						$tempelem->QuotChar = $quotchar;
						$tempelem->SetIsOperator($quotchar == "\0" && in_array($curstr, self::$operators));
						$tempelem->SetValue($curstr);
						$elem->XPathExpressionItems[] = &$tempelem;
						unset($tempelem);
					}
					$curstr = "";
				}
			}
			if ($cur == ' ' || $cur == ':' || $cur == ']' || $cur == ')' )
			{
				if(strlen($curstr) > 0 || $quotchar != '\0')
				{
					$tempelem = new XPathExpressionItem();
					$tempelem->QuotChar = $quotchar;
					$tempelem->SetIsOperator($quotchar == "\0" && in_array($curstr, self::$operators));
					$tempelem->SetValue($curstr);
					$elem->XPathExpressionItems[] = &$tempelem;
					unset($tempelem);
				}
				$quotchar = "\0";
				$curstr = '';
				if ($cur == ']' || $cur == ')')
				{
					$istate = $i;
					break;
				}
				continue;
			}  
			if($cur == '(' || $cur == '[')
			{
				if(strlen($curstr) > 0 || $quotchar != "\0")
				{
   					$tempelem = new XPathExpressionItem();
					$tempelem->QuotChar = $quotchar;
					$tempelem->SetIsOperator($quotchar == "\0" && in_array($curstr, self::$operators));
					$tempelem->SetValue($curstr);
					$elem->XPathExpressionItems[] = &$tempelem;
					unset($tempelem);
					$curstr = '';
				}
                $subElem = self::Parse($input, $i);
                $subitem = new XPathExpressionSubItem();
				$subitem->ParChar = $cur;
				$subElem->Parent = &$elem;
                $subitem->XPathExpressions[] = &$subElem;
				$elem->XPathExpressionItems[] = &$subitem;
				unset($subElem);
				unset($subitem);
				continue;
			}
			$curstr .= $cur;

		}
		if(strlen($curstr) > 0 || $quotchar != "\0")
		{
			$tempelem = new XPathExpressionItem();
			$tempelem->QuotChar = $quotchar;
			$tempelem->SetIsOperator($quotchar == "\0" && in_array($curstr, self::$operators));
			$tempelem->SetValue($curstr);
			$elem->XPathExpressionItems[] = &$tempelem;
			unset($tempelem);
			$curstr = '';
		}
		return $elem;
	}
}