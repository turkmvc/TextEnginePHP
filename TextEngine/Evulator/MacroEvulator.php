<?php


class MacroEvulator extends BaseEvulator
{
	public function Render(&$tag, &$vars)
	{
		$name = $tag->GetAttribute('name');
		if($name)
		{
			$this->Evulator->SavedMacrosList->SetMacro($name, $tag);
		}
		
		return null;
	}
}
