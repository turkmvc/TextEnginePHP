<?php
class ParamEvulator extends  BaseEvulator
{
	public function Render(&$tag, &$vars)
	{
		$result = new TextEvulateResult();
		if(!$tag->ElementType = TextElementType::Parameter)
		{
			return $result;
		}
		$result->Result = TextEvulateResult::EVULATE_TEXT;
		$etresult = $this->EvulateText($tag->ElemName,$vars);
		if(is_array($etresult))
		{
			$etresult = $etresult[0];
		}
		$result->TextContent .= $etresult;
		return $result;
	}
}
