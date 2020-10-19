<?php
class NoPrintEvulator extends  BaseEvulator
{
	public function Render(&$tag, &$vars)
	{
		$tag->EvulateValue(0, 0, $vars);
		return null;
	}
}
