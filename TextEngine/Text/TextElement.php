<?php
abstract class TextElementType
{
	const ElementNode = 1;
	const AttributeNode = 2;
	const TextNode = 3;
	const CDATASection = 4;
	const EntityReferenceNode = 5;
	const CommentNode = 8;
	const Document = 9;
	const Parameter = 16;
	const XMLTag = 17;
}
class TextElement
{
	public $ElemName;
	/** @var array */
	public $ElemAttr = array();
	/** @var TextEvulator */
	public $BaseEvulator;
	public $Closed;
	public $Value;
	/** @var TextElement[] */
	public $SubElements;
	public $SubElementsCount = 0;
	public $SlashUsed;
	/** @var TextElement */
	public $Parent;
	/** @var bool */
	public $DirectClosed;
	public $AutoAdded;
	public $IsParam = false;
	public $IsSummary = false;
	/** @var string */
	public $AliasName;
	public $AutoClosed;
	/** @var int */
	public $Index_old;
	/** @var string */
	public $TagAttrib;
	
	public $ElementType = TextElementType::ElementNode;
	public function Index()
	{
		if(!$this->Parent) return -1;
		return array_search($this, $this->Parent->SubElements);
	}
	/** @param $element TextElement */
	public function AddElement(&$element)
	{
		$this->SubElements[] = $element;
		$element->Index_old = $this->SubElementsCount;
		$this->SubElementsCount++;
	}

	public function GetAttribute($name, $default = null)
	{
		return array_value($name, $this->ElemAttr, $default);
	}

	public function SetAttribute($name, $value)
	{
		$this->ElemAttr[$name] = $value;
	}

	public function NameEquals($name, $matchalias = false)
	{
		if (strtolower($this->ElemName) == strtolower($name)) return true;
		if ($matchalias) {
			if (array_key_exists($name, $this->BaseEvulator->Aliasses)) {
				$alias = $this->BaseEvulator->Aliasses[$name];
				if (!is_array($alias)) {
					if ($alias == $this->ElemName) return true;
				} else {
					if (array_value_exists(strtolower($this->ElemName), $alias)) return true;
				}
			}
		}
		return false;
	}

	public function SetInner($text)
	{
		$this->BaseEvulator->Text = $text;
		$this->SubElements = array();
		$this->SubElementsCount = 0;
		$this->BaseEvulator->Parse($this);
		return $this;
	}
	public function FirstChild()
	{
		if($this->SubElements && $this->SubElementsCount > 0)
		{
			return $this->SubElements[0];
		}
		return null;
	}
	public function LastChild()
	{
		if($this->SubElements && $this->SubElementsCount > 0)
		{
			return $this->SubElements[$this->SubElementsCount - 1];
		}
		return null;
	}
	public function Outer($outputformat = false)
	{
		if ($this->ElemName == '#document') {
			return $this->Inner();
		}
		if ($this->ElemName == '#text') {
			return $this->Value;
		}
		if ($this->ElementType == TextElementType::CommentNode) {
			return $this->BaseEvulator->LeftTag . '--' . $this->value . '--' . $this->BaseEvulator->RightTag;
		}
		$text = '';
		$additional = '';
		if ($this->TagAttrib) {
			$additional .= '=' . $this->TagAttrib;
		}
		if ($this->ElementType == TextElementType::Parameter) {
			$text .= $this->BaseEvulator->LeftTag . $this->BaseEvulator->ParamChar . $this->ElemName . HTMLUTIL::toAttribute($this->ElemAttr) . $this->BaseEvulator->RightTag;
		}
		else 
		{
			if ($this->AutoAdded) {
				if (!$this->SubElements) return '';
			}
			$text .= $this->BaseEvulator->LeftTag . $this->ElemName . $additional . HTMLUTIL::toAttribute($this->ElemAttr);
			if ($this->DirectClosed) {
				$text .= " /" . $this->BaseEvulator->RightTag;
			} else if ($this->AutoClosed) {
				$text .= $this->BaseEvulator->RightTag;
			} else {
				$text .= $this->BaseEvulator->RightTag;
				$text .= $this->Inner($outputformat);
				$eName = $this->ElemName;
				if (!empty($this->AliasName)) {
					$eName = $this->AliasName;
				}
				$text .= $this->BaseEvulator->LeftTag . '/' . $eName . $this->BaseEvulator->RightTag;
			}
		}
		return $text;
	}
	public function HeaderText($outputformat = false)
	{
		if ($this->AutoAdded && $this->SubElementsCount == 0) return "";
		$depth = $this->Depth();
        $text = '';
		if ($outputformat)
		{
			$text .= str_repeat('\t', $depth);
		}
		if ($this->ElementType == TextElementType::XMLTag)
		{
			$text .= $this->BaseEvulator->LeftTag . "?" . $this->ElemName . HTMLUTIL::toAttribute($this->ElemAttr) . "?" . $this->BaseEvulator->RightTag;
		}
		if ($this->ElementType == TextElementType::Parameter)
		{
			$text .= $this->BaseEvulator->LeftTag . $this->BaseEvulator->ParamChar . $this->ElemName . HTMLUTIL::toAttribute($this->ElemAttr) . $this->BaseEvulator->RightTag;
		}
		else if ($this->ElementType == TextElementType::ElementNode)
		{
			$additional = '';
			if (!empty($this->TagAttrib))
			{
				$additional .= '=' . $this->TagAttrib;
			}
			$text .= $this->BaseEvulator->LeftTag . $this->ElemName . $additional . HTMLUTIL::toAttribute($this->ElemAttr);
			if ($this->DirectClosed)
			{
				$text .= " /" . $this->BaseEvulator->RightTag;
			}
			else if ($this->AutoClosed)
			{
				$text .= $this->BaseEvulator->RightTag;
			}
			else
			{
				$text .= $this->BaseEvulator->RightTag;
			}
		}
		else if ($this->ElementType == TextElementType::CDATASection)
		{
			$text .= $this->BaseEvulator->LeftTag + "![CDATA[" + $this->Value + "]]" + $this->BaseEvulator->RightTag;
		}
		else if ($this->ElementType == TextElementType::CommentNode)
		{
			$text .= $this->BaseEvulator->LeftTag + "--" + $this->Value + "--" + $this->BaseEvulator->RightTag;
		}
		if ($outputformat && $this->FirstChild() && $this->FirstChild()->ElemName != "#text")
		{
			$text .= '\r\n';
		}
		return $text;
	}
	public function Footer($outputformat = false)
	{
		if ($this->SlashUsed || $this->DirectClosed || $this->AutoClosed) return null;
		$text = '';
		if ($this->ElementType == TextElementType::ElementNode)
		{
			if ($outputformat)
			{
				if ($this->LastChild() && $this->LastChild()->ElemName != "#text")
				{
					$text .= str_repeat('\t', $this->Depth());
				}
			}
			$eName = $this->ElemName;
			if (!empty($this->AliasName))
			{
				$eName = $this->AliasName;
			}
			$text .= $this->BaseEvulator->LeftTag . '/' + $eName . $this->BaseEvulator->RightTag;
		}
		if ($outputformat)
		{
			$text .= "\r\n";
		}
		return $text;
	}
	public function Inner($outputformat = false)
	{
		$text = '';
		if ($this->ElementType == TextElementType::CommentNode || $this->ElementType == TextElementType::XMLTag)
		{
			return $text;
		}
		if ($this->ElemName == '#text' || $this->ElementType == TextElementType::CDATASection) {
			if ($this->ElementType == TextElementType::EntityReferenceNode)
			{
				$text .= "&" + $this->Value + ";";
				return $text;
			}
			return $this->Value;
		}
		if (!$this->SubElements) return $text;
		foreach ($this->SubElements as $index => $subElement) {
			if ($subElement->ElemName == '#text') 
			{
				$text .= $subElement->Inner($outputformat);
			} 
			else if ($this->ElementType == TextElementType::CDATASection) 
			{
				$text .= $subElement->HeaderText();
			} 
			else if ($this->ElementType == TextElementType::CommentNode) 
			{
				$text .= $subElement->Outer($outputformat);
			} 
			else if ($this->ElementType == TextElementType::Parameter) 
			{
				//$text .= $this->BaseEvulator->LeftTag . $this->BaseEvulator->ParamChar . $subElement->ElemName . HTMLUTIL::toAttribute($subElement->elemAttr) . $this->BaseEvulator->RightTag;
				$text .= $subElement->HeaderText();
			} 
			else 
			{
				$text .= $subElement->HeaderText($outputformat);
				$text .= $subElement->Inner($outputformat);
				$text .= $subElement->Footer($outputformat);
			}
		}
		return $text;
	}

	public function PreviousElementWN($name)
	{
		$prev = $this->PreviousElement();
		while ($prev != null) {
			if ($prev->ElementType == TextElementType::Parameter  || $prev->ElemName == '#text') {
				$prev = $prev->PreviousElement();
				continue;
			}
			
			if (preg_grep( "/$prev->ElemName/i" ,  func_get_args() )) {
				return $prev;
			}
			$prev = $prev->PreviousElement();
		}
		return null;
	}

	public function NextElementWN($name)
	{
		$next = $this->NextElement();
		while ($next != null) {
			if ($next->ElementType == TextElementType::Parameter || $next->ElemName == '#text') {
				$next = $next->NextElement();
				continue;
			}
			if (preg_grep( "/$next->ElemName/i" ,  func_get_args() )) {
				return $next;
			}
			$next = $next->NextElement();
		}
		return null;
	}

	public function PreviousElement()
	{
		if ($this->Index() - 1 >= 0) {
			return $this->parent->SubElements[$this->Index() - 1];
		}
		return null;
	}

	public function NextElement()
	{
		if ($this->Index() + 1 < $this->parent->SubElementsCount) {
			return $this->parent->SubElements[$this->Index() + 1];
		}
		return null;
	}

	public function GetSubElement($name)
	{

		for ($i = 0; $i < $this->SubElementsCount; $i++) {
			$ename = $this->SubElements[$i]->ElemName;
			if (preg_grep( "/$ename/i" ,  func_get_args() )) {
				return $this->SubElements[$i];
			}
		}
		return null;
	}

	public function InnerText()
	{
		if ($this->ElemName == '#text' ||  $this->ElementType == TextElementType::CDATASection) {
			if ($this->ElementType == TextElementType::EntityReferenceNode)
            {
				return array_value($this.Value, $this->BaseEvulator->AmpMaps);
			}
			return $this->Value;
		}
		$text = '';
		if (!$this->SubElements) return $text;
		foreach ($this->subElements as $index => $subElement) 
		{
			if ($subElement->ElemName == '#text' ||  $subElement->ElementType == TextElementType::CDATASection) 
			{
				if ($subElement->ElementType == TextElementType::EntityReferenceNode)
				{
					$text .= array_value($subElement.Value, $this->BaseEvulator->AmpMaps);
				}
				else
				{
					$text .= $subElement->Value;
				}
			} 
			else 
			{
				$text .= $subElement->InnerText();
			}

		}
		return $text;
	}

	/** @return TextEvulateResult */
	public function EvulateValue($start = 0, $end = 0, &$vars = null)
	{
		$result = new TextEvulateResult();
		$result->Result = TextEvulateResult::EVULATE_TEXT;
		if ($this->ElementType == TextElementType::CommentNode)
		{
			return null;
		}
		if ($this->ElemName == '#text') {
			$result->TextContent = $this->Value;
			return $result;
		}

		if ($this->ElementType == TextElementType::Parameter) 
		{
			$pclass = $this->BaseEvulator->EvulatorTypes->Param;
	
			if($pclass && class_exists($pclass))
			{

				$evulator = new $pclass($this->BaseEvulator);
				$vresult = $evulator->Render($this, $vars);
				$result->Result = $vresult->Result;
				if ($vresult->Result == TextEvulateResult::EVULATE_TEXT) {
					$result->TextContent .= $vresult->TextContent;
				}
				$result->Result = TextEvulateResult::EVULATE_TEXT;

				return $result;
			}
			return null;
		}
		if ($end == 0) $end = $this->SubElementsCount;
		for ($i = $start; $i < $end; $i++) {
			$subElement = $this->SubElements[$i];
			//$className = $subElement->ElemName . 'Evulator';
			$className = '';
			if($subElement->ElemName != "#text")
			{
				$className = $this->BaseEvulator->EvulatorTypes[strtolower($subElement->ElemName)];
				if(!$className)
				{
					$className = $this->BaseEvulator->EvulatorTypes->GeneralType;
				}
			}
			if ($subElement->ElementType == TextElementType::Parameter) 
			{
				$className = $this->BaseEvulator->EvulatorTypes->Param;

			}
				
			if (!empty($className) && class_exists($className)) 
			{
				
				$evulatorObj = new $className($this->BaseEvulator);
				$vresult = $evulatorObj->Render($subElement, $vars);
				if (!$vresult) continue;
				if ($vresult->Result == TextEvulateResult::EVULATE_DEPTHSCAN) {
					$vresult = $subElement->EvulateValue($vresult->Start, $vresult->End, $vars);
				}
			
			}
			else 
			{
				$vresult = $subElement->EvulateValue(0, 0, $vars);
				if (!$vresult) continue;
				//$vresult = new TextEvulateResult();
				//$vresult->Result = TextEvulateResult::EVULATE_TEXT;
				//$vresult->TextContent = $subElement->Outer();
			}
			if ($vresult->Result == TextEvulateResult::EVULATE_TEXT) {
				$result->TextContent .= $vresult->TextContent;
			} 
			else if ($vresult->Result == TextEvulateResult::EVULATE_RETURN || $vresult->Result == TextEvulateResult::EVULATE_BREAK || $vresult->Result == TextEvulateResult::EVULATE_CONTINUE)
			{

				$result->Result = $vresult->Result;
				$result->TextContent .= $vresult->TextContent;
				break;
			}
		}
		return $result;
	}
}
class TextEvulateResult
{
	const EVULATE_NOACTION = 0;
	const EVULATE_TEXT = 1;
	const EVULATE_CONTINUE = 2;
	const EVULATE_RETURN = 3;
	const EVULATE_DEPTHSCAN = 4;
	const EVULATE_BREAK = 5;

	public $TextContent;
	public $Result;
	public $Start = 0;
	public $End = 0;
}
