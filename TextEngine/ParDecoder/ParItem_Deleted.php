<?php
/** @param $sender InnerItem
 *	@return ComputeResult
 */
function Compute_Old(&$vars = null, $sender = null, &$localvars = null)
{
	$cr = new ComputeResult();
	$result = null;
	$operator = null;
	$arrresult = array();
	$objresult = new stdClass();
	$lastenter = false;
	if(!$this->innerItems)
	{
		return  $this->ReturnDefault($this->prevResult, $cr);
	}
	$arrowvalue = null;
	$objvalue = null;
	$tempresult = null;
	$previtem = null;
	$tifstate = 0;

	foreach ($this->innerItems as $index => $innerItem) {
		$newarray = array_setifnotarray($innerItem);
		foreach ($newarray as $index => $item) {
			$arritem = array_setifnotarray($item);
			$gresult = null;
			$goperator = null;

			foreach ($arritem as $index => $last) {
				if($tifstate == -1) continue;
				if($tifstate > 0 && $last->is_operator)
				{
					//Expression is true
					if($tifstate == 1)
					{
						if($last->value == ':')
						{
							$tifstate = -1;
						}
					}
					//Expression is false
					else if($tifstate == 2)
					{
						if($last->value != ':')
						{
							continue;
						}
						$tifstate = 0;

					}
				}
				if(get_class($last) == 'ParItem')
				{
					/** @var $otherresult ComputeResult*/
					$this->prevResult = $result;
					$otherresult = $last->Compute($vars, $last, $localvars);
					$kurtarir = 0;
					if(ComputeActions::IsObjectOrArray($result) ||is_string($result) || $tempresult)
					{
						$kurtarir = 2;
					}
					else
					{

						$kurtarir =  ((($last->segments) && $last->parName == '[' || ($last->parName == '(' && $otherresult->resultType == ComputeResult::RESULT_ARRAY)) ? 1 : 0);
					}
					if($kurtarir > 0)
					{
						if($last->parName == '(')
						{

							$new = ComputeActions::CallMethod($last, $otherresult->result, $vars, $localvars);
						}
						else
						{
							if($kurtarir == 2)
							{
								$arrvar = null;
								if(is_object($result))
								{
									$arrvar = ComputeActions::GetPropValue($last, $result, $localvars);

								}
								else
								{
									if($tempresult)
									{
										$arrvar = &$tempresult;
									}
									else
									{
										$arrvar = &$result;
									}
								}


							}
							else
							{

								$arrvar = ComputeActions::GetPropValue($last, $vars, $localvars);


							}

							if($arrvar && (is_array($arrvar) ||is_string($arrvar)))
							{
								$new = $arrvar[$otherresult->result];
							}
							else
							{
								$new = null;
							}
						}
						if($goperator)
						{

							$gresult = ComputeActions::OperatorResult($gresult, $new, $goperator);

							$goperator = null;
						}
						else
						{
							$gresult = $new;
						}


					}
					else
					{
						if($goperator)
						{
							$gresult = ComputeActions::OperatorResult($gresult, $otherresult->result, $goperator);
							$goperator = null;
						}
						else
						{
							$gresult = $otherresult->result;
						}
					}

				}
				else
				{
					$value = $last->value;
					if($last->type == InnerItem::TYPE_VARIABLE)
					{
						if($value === 'null')
						{
							$lastenter = true;
							$value = null;
						}
						else if($value === 'false')
						{
							$lastenter = true;
							$value = false;
						}
						else if($value === 'true')
						{
							$lastenter = true;
							$value = true;
						}
						else if((($gresult && is_object($gresult) )|| ($result && is_object($result)))  && !$operator && $last->segments[0][0] == '.')
						{
							if($gresult)
							{

								$value = ComputeActions::GetPropValue($last, $gresult, $localvars);
							}
							else
							{
								$value = ComputeActions::GetPropValue($last, $result, $localvars);
							}

						}
						else if(!$this->IsObject() || $objvalue)
						{

							$value = ComputeActions::GetPropValue($last, $vars, $localvars);
						}
						if($value === null)
						{
							$lastenter = true;
						}
					}
					if(!$last->is_operator)
					{

						if($goperator &&  !str_equalsany($goperator, ',', '=>', ':', '&&', '||'))
						{
							if($goperator == '!')
							{
								$gresult = (($value) ? 0 : 1);
							}
							else
							{
								$gresult =ComputeActions::OperatorResult($gresult, $value, $goperator);

							}
							$goperator = null;
						}
						else
						{

							if($goperator == '&&')
							{
								if($gresult != $value)
								{
									$gresult = 0;
									break;
								}
							}
							else if($goperator == '||')
							{
								if($gresult == $value)
								{
									$gresult = 1;
									break;
								}
							}
							$gresult = $value;
						}
					}
					else
					{

						if(!$gresult && $result)
						{
							$gresult = $result;
							$result = null;
						}
						if($value == '&&' || $value == '||' )
						{
							if(!$gresult)
							{
								$gresult = 0;
								if($value == '&&')
								{
									$cr->result = 0;
									return $cr;
								}
							}
							else
							{
								$gresult = 1;
								if($value == '||')
								{
									$cr->result = 1;
									return $cr;
								}
							}
						}
						else if($value == '=>' || ($value == ':' && ($sender && $sender->parName == '{')))
						{

							if($value == ':')
							{
								$objvalue = $gresult;
							}
							else
							{
								$arrowvalue = $gresult;
							}
							$gresult = null;
						}
						else if($value == '?' || $value == ':')
						{
							if($value == '?')
							{
								if($gresult)
								{
									$tifstate = 1;
								}
								else
								{
									$tifstate = 2;
								}
							}
							continue;

						}
						else if($value == ',')
						{
							if($this->IsFunction() ||$this->IsArray($sender->prevResult) || $this->IsObject())
							{
								$lastenter = false;
								if($this->IsArray($sender->prevResult))
								{
									if($arrowvalue)
									{
										$arrresult[$arrowvalue] = $gresult;
									}
									else
									{

										$arrresult[] = $gresult;
									}
									$arrowvalue = null;

								}
								elseif($this->IsObject())
								{
									$objresult->$objvalue = $gresult;
									$objvalue = null;
								}
								else
								{
									$arrresult[] = $gresult;
								}
								$gresult = null;
								$goperator = null;

							}
						}
						else
						{
							$goperator = $value;
						}
					}
				}
				$previtem = $last;
			}

			if($goperator)
			{
				if(!$result)
				{
					$result = $gresult;
				}
				else
				{
					if($gresult)
					{

						$result = ComputeActions::OperatorResult($result, $gresult, $goperator);


					}
				}
				$operator = $goperator;
			}
			else
			{
				if($operator)
				{
					if(ComputeActions::IsObjectOrArray($gresult))
					{

						$tempresult = $gresult;
					}
					else
					{
						$result = ComputeActions::OperatorResult($result, $gresult, $operator);

						$operator = null;

					}


				}
				else
				{
					$result = $gresult;
				}
			}

		}

	}
	$gec= false;
	if($result || $lastenter)
	{
		if($this->IsArray($sender->prevResult) ||$this->IsFunction() || $this->IsObject())
		{
			if($this->IsArray($sender->prevResult)  && isset($arrowvalue))
			{
				$arrresult[$arrowvalue] = $result;
			}
			elseif ($this->IsObject() && isset($objvalue))
			{
				$objresult->$objvalue = $result;
			}
			else
			{
				$arrresult[] = $result;
			}
			$result = null;
			$gec = true;

		}
	}
	if($this->IsFunction() || $this->IsArray($sender->prevResult) && ($gec || !$previtem || $previtem->is_operator))
	{
		$cr->result = $arrresult;
		$cr->resultType = ComputeResult::RESULT_ARRAY;

	}
	else if( $this->IsObject())
	{
		$cr->result = $objresult;
		$cr->resultType = ComputeResult::RESULT_OBJECT;
	}
	else
	{
		if($previtem && !$previtem->is_operator && $result === null)
		{
			if(property_exists($previtem, 'value'))
			{
				$result = $previtem->value;
			}

		}
		$cr->result = $result;
	}
	return $cr;
}
