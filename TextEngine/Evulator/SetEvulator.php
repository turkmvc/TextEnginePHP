<?php
class SetEvulator  extends  BaseEvulator
{
	public function Render(&$tag, &$vars)
	{
		$result = new TextEvulateResult();
		$result->Result = TextEvulateResult::EVULATE_NOACTION;
		if ($this->ConditionSuccess($tag, "if"))
		{
			
			$defname = $tag->GetAttribute("name");
			if (empty($defname) || !ctype_alnum($defname)) return $result;
			$defvalue = $tag->GetAttribute("value");
			$this->Evulator->DefineParameters[$defname] = $this->EvulateText($defvalue);
		}
		return $result;
	}
}
