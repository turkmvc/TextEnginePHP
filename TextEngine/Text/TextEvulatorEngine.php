<?php


class TextEvulatorEngine
{
	public  static function Render($fileOrText, $isfile = true, &$globals = null)
	{
		$ev = new TextEvulator($fileOrText, true);
		$ev->param_noattrib = true;
		$ev->globalParameters = $globals;
		$ev->Parse();
		$result = $ev->Elements->EvulateValue();
		echo $result->TextContent;
	}
}
