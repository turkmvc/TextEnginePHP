<?php


class RenderSectionEvulator extends BaseEvulator
{
	public function Render(&$tag, &$vars)
	{
		$name = $tag->GetAttribute('name');
		if (!$name) return null;
		$result = new TextEvulateResult();
		$result->Result = TextEvulateResult::EVULATE_TEXT;
		$result->TextContent = SECTION::Render($name, false, true );
		return $result;
	}
}
