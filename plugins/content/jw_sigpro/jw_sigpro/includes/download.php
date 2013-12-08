<?php
/**
 * @version		2.5.3
 * @package		Simple Image Gallery Pro
 * @author    JoomlaWorks - http://www.joomlaworks.gr
 * @copyright	Copyright (c) 2006 - 2011 JoomlaWorks Ltd. All rights reserved.
 * @license		Commercial - This code cannot be redistributed without permission from JoomlaWorks Ltd.
 */

// Set flag that this is a parent file
define('_JEXEC',1);

define('DS',DIRECTORY_SEPARATOR);
if(strpos($_SERVER['SCRIPT_FILENAME'],'jw_sigpro'.DS.'jw_sigpro')){
	define('JPATH_BASE','..'.DS.'..'.DS.'..'.DS.'..'.DS.'..');
} else {
	define('JPATH_BASE','..'.DS.'..'.DS.'..'.DS.'..');
}
//define('JPATH_BASE', str_replace('/plugins/content/jw_sigpro/includes/download.php','',$_SERVER['SCRIPT_FILENAME']));

// Includes
require_once(JPATH_BASE.DS.'includes'.DS.'defines.php');
require_once(JPATH_BASE.DS.'includes'.DS.'framework.php');
jimport('joomla.filesystem.file');

// API
$mainframe= &JFactory::getApplication('site');
$document = &JFactory::getDocument();

// Assign paths
if(version_compare(JVERSION,'1.6.0','ge')) {
	$sitePath = str_replace(DS.'plugins'.DS.'content'.DS.'jw_sigpro'.DS.'jw_sigpro'.DS.'includes','',dirname(__FILE__));
	$siteUrl  = str_replace('/plugins/content/jw_sigpro/jw_sigpro/includes/','',JURI::root());
} else {
	$sitePath = str_replace(DS.'plugins'.DS.'content'.DS.'jw_sigpro'.DS.'includes','',dirname(__FILE__));
	$siteUrl  = str_replace('/plugins/content/jw_sigpro/includes/','',JURI::root());
}

// Load the plugin language file
$language =& JFactory::getLanguage();
$language->load('plg_content_jw_sigpro', JPATH_ADMINISTRATOR);

// Define error handling
$nogo = '
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
		<title>'.$mainframe->getCfg('sitename').'</title>
		<link rel="stylesheet" href="'.$siteUrl.'/templates/system/css/error.css" type="text/css" />
	</head>
	<body>
		<div align="center">
			<div id="outline">
			<div id="errorboxoutline">
				<div id="errorboxheader">'.JText::_('JW_SIGP_PLG_DL_01').'</div>
				<div id="errorboxbody">
				<p><strong>'.JText::_('JW_SIGP_PLG_DL_02').':</strong></p>
					<ol>
						<li>'.JText::_('JW_SIGP_PLG_DL_03').'</li>
						<li>'.JText::_('JW_SIGP_PLG_DL_04').'</li>
						<li>'.JText::_('JW_SIGP_PLG_DL_05').'</li>
					</ol>
				<p><strong>'.JText::_('JW_SIGP_PLG_DL_06').':</strong></p>
				<p>
					<ul>
						<li><a href="javascript:history.go(-1);">'.JText::_('JW_SIGP_PLG_DL_07').'</a></li>
						<li><a href="'.$siteUrl.'/" title="'.JText::_('JW_SIGP_PLG_DL_08').'">'.JText::_('JW_SIGP_PLG_DL_09').'</a></li>
					</ul>
				</p>
				<p>'.JText::_('JW_SIGP_PLG_DL_10').'</p>
				</div>
			</div>
			</div>
		</div>
	</body>
</html>
';

// Start the process
$pathToSourceFile = JRequest::getString('file');
$pathToSourceFile = preg_replace('#[/\\\\]+#', DS, $pathToSourceFile);

if(strpos($pathToSourceFile, '..') !== false || strpos($pathToSourceFile, './') !== false){
 echo $nogo;
 exit;
}

// Reference the "/images" or "/media/k2/galleries" directory
$ref_com_content = $siteUrl.'/'.substr(str_replace(DS,'/',$pathToSourceFile),0,strlen('images/'));
$check_com_content = $siteUrl."/images/";

$ref_com_k2 = $siteUrl.'/'.substr(str_replace(DS,'/',$pathToSourceFile),0,strlen('media/k2/galleries/'));
$check_com_k2 = $siteUrl."/media/k2/galleries/";

if(isset($pathToSourceFile) && ($ref_com_content===$check_com_content || $ref_com_k2===$check_com_k2)){
	$getfile = $pathToSourceFile;
} else {
	$getfile = NULL;
}

if (!$getfile) {
	// go no further if filename not set
	echo $nogo;
} else {
	// define the pathname to the file
	$filepath = $sitePath.DS.str_replace('/',DS,$getfile);

	// check that it exists and is readable
	if (file_exists($filepath) && is_readable($filepath)) {
		// get the file's size and send the appropriate headers
		$size = filesize($filepath);
		header('Content-Type: application/force-download');
		header('Content-Length: '.$size);
		header('Content-Disposition: attachment; filename="'.basename($getfile).'"');
		header('Content-Transfer-Encoding: binary');
		// open the file in binary read-only mode - suppress error messages if the file cannot be opened
		$file = @ fopen($filepath, 'rb');
		if ($file) {
			// stream the file and exit the script when complete
			fpassthru($file);
			exit;
		} else {
			echo $nogo;
		}
	} else {
		echo $nogo;
	}
}
