<?php
class TextEvulator
{
	public $Text;
	/** @var TextElement */
	public $Elements;
	private	$Depth = 0;
	public $LeftTag = "{";
	public $RightTag = "}";
	public $NoParseTag = "noparse";
	public $NoParseEnabled = true;
	public $ParamChar = '%';
	public $Aliasses = array();
	public $AutoClosedTags = array('elif', 'else', 'return', 'break', 'continue', 'include', 'renderSection', 'cm', 'set', 'unset');
	public $GlobalParameters = array();
	/** @var ArrayGroup */
	public $LocalVariables;
	public $ParamNoAttrib = false;
	public $DecodeAmpCode = false;
	public $AmpMaps = array();
	public $ConditionalTags = array();
	public $NoAttributedTags = array();
	public $SupportCDATA = false;
	public $SupportExclamationTag = false;
	public $AllowXMLTag = true;
	public $TrimStartEnd = false;
	public $TrimMultipleSpaces = true;
	public $AllowParseCondition = true;
	public $ThrowExceptionIFPrevIsNull = true;
	/** @var array */
	public $DefineParameters = array();
	/** @var SavedMacros */
	public $SavedMacrosList;
	/** @var EvulatorTypesClass */
	public $EvulatorTypes;
	/** @var bool */
	public $IsParseMode;

	public function __construct($text = null, $isfile = false)
	{
		$this->EvulatorTypes = new EvulatorTypesClass();
		$this->SavedMacrosList = new SavedMacros();
		$this->Elements = new TextElement();
		$this->LocalVariables = new ArrayGroup();
		$this->Elements->ElemName = "#document";
		$this->LocalVariables->AddArray($this->DefineParameters);
		if ($isfile) {
			$this->Text = file_get_contents( $text);
		} else {
			$this->Text = $text;
		}
		$this->InitNoAttributedTags();
		$this->InitEvulator();
		$this->InitAmpMaps();
		$this->InitConditionalTags();
	}
	private function InitEvulator()
	{
		$this->EvulatorTypes->Param = "ParamEvulator";
		$this->EvulatorTypes->GeneralType = "GeneralEvulator";
		$this->EvulatorTypes["if"] = "IfEvulator";
		$this->EvulatorTypes["for"] = "ForEvulator";
		$this->EvulatorTypes["foreach"] = "ForeachEvulator";
		$this->EvulatorTypes["switch"] = "SwitchEvulator";
		$this->EvulatorTypes["return"] = "ReturnEvulator";
		$this->EvulatorTypes["break"] = "BreakEvulator";
		$this->EvulatorTypes["continue"] = "ContinueEvulator";
		$this->EvulatorTypes["cm"] = "CMEvulator";
		$this->EvulatorTypes["macro"] = "MacroEvulator";
		$this->EvulatorTypes["noprint"] = "NoPrintEvulator";
		$this->EvulatorTypes["repeat"] = "RepeatEvulator";
		$this->EvulatorTypes["include"] = "IncludeEvulator";
		$this->EvulatorTypes["set"] = "SetEvulator";
		$this->EvulatorTypes["unset"] = "UnsetEvulator";
	}
	private function InitNoAttributedTags()
	{
		$this->NoAttributedTags[] = "if";
	}
	private function InitConditionalTags()
	{
		$this->ConditionalTags[] = "if";
		$this->ConditionalTags[] = "include";
		$this->ConditionalTags[] = "set";
		$this->ConditionalTags[] = "unset";
	}
	private function InitAmpMaps()
	{
		$this->AmpMaps['nbsp'] = ' ';
		$this->AmpMaps['amp'] = '&';
		$this->AmpMaps['quot'] = '"';
		$this->AmpMaps['lt'] = '<';
		$this->AmpMaps['gt'] = '>';
	}
	public function Parse()
	{
		$parser = new TextEvulatorParser($this);
		$parser->Parse($this->Elements, $this->Text);
	}
	public function ParseText($baselement, $text)
	{
		$parser = new TextEvulatorParser($this);
		$parser->Parse($baselement, $text);
	}
	public function OnTagClosed($element)
	{
		if (!$this->AllowParseCondition || !$this->IsParseMode || !in_array($element->ElemName, $this->ConditionalTags)) return;
		$indis = $element->Index();
		$element->Parent->EvulateValue($indis, $indis + 1);
	}
}