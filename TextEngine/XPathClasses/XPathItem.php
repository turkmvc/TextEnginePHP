<?php
namespace TextEngine;
class XPathItem implements IXPathBlockContainer
{
	public $XPathBlocks;
	public $XPathBlockList;
	public $Parent;
	public function __construct()
	{
		$this->XPathBlocks = array();
		$this->XPathBlockList = array();
	}
	public static function ParseNew($xpath)
	{
		$expisparexp = false;
		$pathitem = new XPathItem();
		$curblock = new XPathBlock();
		$curstr = "";
		$current = &$pathitem;
		$blocks = new XPathBlocks();
		$current->XPathBlockList[] = &$blocks;
		$curexp = &$curblock->XPathExpressions;
		for ($i = 0; $i < strlen($xpath); $i++)
		{
			$cur = $xpath[$i];
			$next = ($i + 1 < strlen($xpath)) ? $xpath[$i + 1] : "\0";
			if($cur == '|' || $cur == ')' || $cur == '(')
			{
				if (empty($curblock->BlockName))
				{
					$curblock->BlockName = $curstr;
				}
				if (!empty($curblock->BlockName) || $curblock->IsAttributeSelector)
				{
					$blocks->AddItem($curblock);
					//$blocks[] = &$curblock;
				}
				$curstr = '';
			}
			if ($cur == '[')
			{
				if (count($curblock->XPathExpressions) == 0)
				{
					$curblock->BlockName = $curstr;
					$curstr = '';
				}
				$newexp = XPathExpression::Parse($xpath, $i);
				if(!empty($curblock->BlockName))
				{
					$curblock->XPathExpressions[] = &$newexp;
				}
				else
				{
					$curexp[] = &$newexp;
				}
				unset($newexp);
				continue;
			}
			else if ($cur == '|' || $cur == '(')
			{
				unset($lastitem);
				$index = count($current->XPathBlockList) - 1;
				unset($lastitem);
				$lastitem = null;
				if($index >= 0)
				{
					$lastitem = &$current->XPathBlockList[$index];
				}
				
				if ($lastitem)
				{
					if (!$lastitem->Any())
					{
						array_splice($current->XPathBlockList, count($current->XPathBlockList) - 1, 1);
					}
				}
				unset($curblock);
				$curblock = new XPathBlock();
				unset($curexp);
				$curexp = &$curblock->XPathExpressions;
				if ($cur == '(')
				{
					$xpar = new XPathPar();
					$xpar->Parent = &$current;
					$current->XPathBlockList[] = &$xpar;
					unset($current);
					$current = &$xpar;
				}
				else
				{
					$current->XPathBlockList[] = new XPathOrItem();
				}
				unset($blocks);
				$blocks = new XPathBlocks();
				$current->XPathBlockList[] = &$blocks;
				continue;
			}
			else if($cur == ')')
			{
				unset($lastitem);
				$index = count($current->XPathBlockList) - 1;
				$lastitem = null;
				if($index >= 0)
				{
					$lastitem = &$current->XPathBlockList[$index];
				}
				if ($lastitem)
				{
					if (!$lastitem->Any())
					{
						array_splice($current->XPathBlockList, count($current->XPathBlockList) - 1, 1);
					}
				}
				unset($curexp);
				$curexp = &$current->XPathExpressions;
				unset($current);
				$current = &$current->Parent;
				$expisparexp = true;
				if ($current == null)
				{
					throw new Exception("Syntax error");
				}
				unset($blocks);
				$blocks = new XPathBlocks();
				$current->XPathBlockList[] = &$blocks;
				// current.XPathBlockList.Add(blocks);
				unset($curblock);
				$curblock = new XPathBlock();
				continue;
			}
			else if ($cur == '/')
			{
				if (empty($curblock->BlockName))
				{
					$curblock->BlockName = $curstr;
				}
				if (empty($curblock->BlockName))
				{
					if ($next == '/')
					{
						$curblock->BlockType = XPathBlockType::XPathBlockScanAllElem;
						$i += 1;
					}
				}
				else
				{
					$blocks->AddItem($curblock);
					//$blocks[] = &$curblock;
					unset($curblock);
					$curblock = new XPathBlock();
					if ($next == '/')
					{
						$curblock->BlockType = XPathBlockType::XPathBlockScanAllElem;
					}
					$curstr = '';
				}
				if($expisparexp)
				{
					unset($curexp);
					$curexp = &$curblock->XPathExpressions;
					$expisparexp = false;
				}
				continue;
			}
			else if ($cur == '@')
			{
				if (empty($curblock->BlockName))
				{
					$curblock->IsAttributeSelector = true;
				}
				else
				{
					throw new Exception("Syntax Error");
				}
				continue;
			}
			$curstr .= $cur;
		}
		if (empty($curblock->BlockName))
		{
			$curblock->BlockName = $curstr;
		}
		if (!empty($curblock->BlockName) || $curblock->IsAttributeSelector)
		{
			$blocks->AddItem($curblock);
			//current.XPathBlockList.Add(curblock);
		}
		$sonitem = end($current->XPathBlockList);
		if ($sonitem)
		{
			if (!$sonitem->Any())
			{
				array_splice($current->XPathBlockList, count($current->XPathBlockList) - 1, 1);
			}
		}
		return $pathitem;
	}
	public static function Parse($xpath)
	{
		$pathitem = new XPathItem();
		$curblock = new XPathBlock();
		$curexp = null;
		$curstr = "";
		for ($i = 0; $i < strlen($xpath); $i++)
		{
			$cur = $xpath[$i];
			$next = ($i + 1 < strlen($xpath)) ? $xpath[$i + 1] : '\0';
			if ($cur == '[')
			{
				if (count($curblock->XPathExpressions) == 0)
				{
					$curblock->BlockName = $curstr;
					$curstr = '';
				}
				$curexp = XPathExpression::Parse($xpath, $i);
				$curblock->XPathExpressions[] = &$curexp;
				unset($curexp);
				continue;
			}
			else if ($cur == '/')
			{
				if (empty($curblock->BlockName))
				{
					$curblock->BlockName = $curstr;
				}
				if (empty($curblock->BlockName))
				{
					if ($next == '/')
					{
						$curblock->BlockType = XPathBlockType::XPathBlockScanAllElem;
						$i += 1;
					}
				}
				else
				{
					$pathitem->XPathBlocks[] = &$curblock;
					unset($curblock);
					$curblock = new XPathBlock();
					if ($next == '/')
					{
						$curblock->BlockType = XPathBlockType::XPathBlockScanAllElem;
					}
					$curstr = '';
				}
				continue;
			}
			else if ($cur == '@')
			{
				if (empty($curblock->BlockName))
				{
					$curblock->IsAttributeSelector = true;
				}
				else
				{
					throw new Exception("Syntax Error");
				}
				continue;
			}
			$curstr .= $cur;
		}
		if (empty($curblock->BlockName))
		{
			$curblock->BlockName = $curstr;
		}
		if (!empty($curblock->BlockName) || $curblock->IsAttributeSelector)
		{
			$pathitem->XPathBlocks[] = &$curblock;
		}
		return $pathitem;
	}
	public function IsXPathPar()
	{
		return true;
	}
}