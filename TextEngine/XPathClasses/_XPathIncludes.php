<?php
namespace TextEngine;
$files = glob(__DIR__ . "/*.php");
foreach ($files as $file) {
	if(str_endswith($file, '_XPathIncludes.php') || strpos($file, "XPath") == -1) continue;
	require_once $file;
}