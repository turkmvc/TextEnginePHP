<?php


class BreakEvulator extends BaseEvulator
{
	public function Render(&$tag, &$vars)
	{
		$result = new TextEvulateResult();
		$result->Result = TextEvulateResult::EVULATE_BREAK;
		return $result;
	}
}
