<?php


class SectionEvulator extends BaseEvulator
{
	public function Render(&$tag, &$vars)
	{
		$name = $tag->GetAttribute('name');
		if(!$name) return null;
		$cresult = $tag->EvulateValue(0, 0, $vars);
		SECTION::AddSectionDirectly($name, $cresult->TextContent);
		return null;
	}
}
