<?php
namespace TextEngine;
interface IXPathExpressionItem
{
	public function IsSubItem();
	public function IsItemContainer();
	public function GetParchar();
	public function SetParchar($char);
	//XPathBlockContainer XPathBlockList { get; set; }
	//IXPathBlockContainer Parent { get; set; }
}