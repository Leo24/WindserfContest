<?php
/**
 * @version		2.5.3
 * @package		Simple Image Gallery Pro
 * @author    JoomlaWorks - http://www.joomlaworks.gr
 * @copyright	Copyright (c) 2006 - 2011 JoomlaWorks Ltd. All rights reserved.
 * @license		Commercial - This code cannot be redistributed without permission from JoomlaWorks Ltd.
 */

// no direct access
defined('_JEXEC') or die('Restricted access');

jimport('joomla.plugin.plugin');

class plgContentJw_sigpro extends JPlugin {

  // JoomlaWorks reference parameters
	var $plg_name								= "jw_sigpro";
	var $plg_tag								= "gallery";
	var $plg_copyrights_start		= "\n\n<!-- JoomlaWorks \"Simple Image Gallery PRO\" Plugin (v2.5.3) starts here -->\n";
	var $plg_copyrights_end			= "\n<!-- JoomlaWorks \"Simple Image Gallery PRO\" Plugin (v2.5.3) ends here -->\n\n";

	function plgContentJw_sigpro( &$subject, $params ){
		parent::__construct( $subject, $params );
	}

	// Joomla! 1.5
	function onPrepareContent(&$row, &$params, $page = 0){
		$this->renderSIGP($row, $params, $page = 0);
	}

	// Joomla! 1.6
	function onContentPrepare($context, &$row, &$params, $page = 0){
		jimport('joomla.html.parameter');
		$this->renderSIGP($row, $params, $page = 0);
	}

	// The main function
	function renderSIGP(&$row, &$params, $page = 0){

		// API
		jimport('joomla.filesystem.file');
		$mainframe	= &JFactory::getApplication();
		$document 	= &JFactory::getDocument();

		// Requests
		$option 		= JRequest::getCmd('option');
		$view 			= JRequest::getCmd('view');
		$layout 		= JRequest::getCmd('layout');
		$page 			= JRequest::getCmd('page');
		$secid 			= JRequest::getInt('secid');
		$catid 			= JRequest::getInt('catid');
		$itemid 		= JRequest::getInt('Itemid');
		if(!$itemid) $itemid = 999999;

		// Assign paths
		$sitePath = JPATH_SITE;
		$siteUrl  = JURI::root(true);
		if(version_compare(JVERSION,'1.6.0','ge')) {
			$pluginLivePath = $siteUrl.'/plugins/content/'.$this->plg_name.'/'.$this->plg_name;
		} else {
			$pluginLivePath = $siteUrl.'/plugins/content/'.$this->plg_name;
		}

		// Check if plugin is enabled
		if(JPluginHelper::isEnabled('content',$this->plg_name)==false) return;

		// Bail out if the page is not HTML
		if(JRequest::getCmd('format')!='html' && JRequest::getCmd('format')!='') return;

		// Simple performance check to determine whether plugin should process further
		if(JString::strpos($row->text, $this->plg_tag) === false) return;

		// expression to search for
		$regex = "#{".$this->plg_tag."}(.*?){/".$this->plg_tag."}#s";

		// Find all instances of the plugin and put them in $matches
		preg_match_all($regex,$row->text,$matches);

		// Number of plugins
		$count = count($matches[0]);

		// Plugin only processes if there are any instances of the plugin in the text
		if(!$count) return;

		// Load the plugin language file the proper way
		JPlugin::loadLanguage('plg_content_'.$this->plg_name, JPATH_ADMINISTRATOR);

		// Check for basic requirements
		if(!extension_loaded('gd') && !function_exists('gd_info')) {
			JError::raiseNotice('', JText::_('JW_SIGP_PLG_NOTICE_01'));
			return;
		}
		if(!is_writable($sitePath.DS.'cache')){
			JError::raiseNotice('', JText::_('JW_SIGP_PLG_NOTICE_02'));
			return;
		}

		// Check if Simple Image Gallery is present and enabled and prompt to disable
		if(JPluginHelper::isEnabled('content','jw_simpleImageGallery')==true){
			JError::raiseNotice('', JText::_('JW_SIGP_PLG_NOTICE_00'));
			return;
		}


		// ----------------------------------- Get plugin parameters -----------------------------------

		// Get plugin info
		$plugin =& JPluginHelper::getPlugin('content',$this->plg_name);

		// Control external parameters and set variable for controlling plugin layout within modules
		if(!$params) $params = new JParameter(null);
		$parsedInModule = $params->get('parsedInModule');

		$pluginParams = new JParameter( $plugin->params );

		$galleries_rootfolder 	= ($params->get('galleries_rootfolder')) ? $params->get('galleries_rootfolder') : $pluginParams->get('galleries_rootfolder','images/stories');
		$popup_engine 					= (!is_null($params->get('popup_engine',null))) ? $params->get('popup_engine') : $pluginParams->get('popup_engine', 'jquery_slimbox');
		//$thb_template 					= (!is_null($params->get('thb_template',null))) ? $params->get('thb_template') : $pluginParams->get('thb_template', 'Polaroids');
		$thb_template 					= $pluginParams->get('thb_template', 'Polaroids');
		$thb_width 							= (!is_null($params->get('thb_width',null))) ? $params->get('thb_width') : $pluginParams->get('thb_width', 200);
		$thb_height 						= (!is_null($params->get('thb_height',null))) ? $params->get('thb_height') : $pluginParams->get('thb_height', 160);
		$smartResize 						= $pluginParams->get('smartResize', 1);
		$jpg_quality 						= $pluginParams->get('jpg_quality', 80);
		$singlethumbmode 				= (!is_null($params->get('singlethumbmode',null))) ? $params->get('singlethumbmode') : $pluginParams->get('singlethumbmode', 0);
		$sortorder	 						= $pluginParams->get('sortorder','0');
		$showcaptions						= (!is_null($params->get('showcaptions',null))) ? $params->get('showcaptions') : $pluginParams->get('showcaptions', 1);
		$wordLimit 							= $pluginParams->get('wordlimit',0);
		$enabledownload					= (!is_null($params->get('enabledownload',null))) ? $params->get('enabledownload') : $pluginParams->get('enabledownload', 1);
		$loadmoduleposition			= $pluginParams->get('loadmoduleposition','');

		$flickrImageCount				= $pluginParams->get('flickrImageCount',20);
		$yqlMaxAge							= $pluginParams->get('yqlMaxAge',60) * 60; // Set in minutes

		$cache_expire_time	 		= $pluginParams->get('cache_expire_time',120) * 60; // Cache expiration time in minutes

		// Advanced
		$memoryLimit						= (int) $pluginParams->get('memoryLimit');
		if($memoryLimit) ini_set("memory_limit",$memoryLimit."M");

		$debugMode							= $pluginParams->get('debugMode',1);
		if($debugMode==0) error_reporting(0); // Turn off all error reporting

		// Cleanups
		// Remove first and last slash if they exist
		if(substr($galleries_rootfolder,0,1)=='/') $galleries_rootfolder = substr($galleries_rootfolder,1);
		if(substr($galleries_rootfolder,-1,1)=='/') $galleries_rootfolder = substr($galleries_rootfolder,0,-1);

		// Other assignments
		$transparent			= $pluginLivePath.'/includes/images/transparent.gif';
		$downloadFile			= $enabledownload ? $pluginLivePath.'/includes/download.php' : NULL;
		$modulePosition		= $loadmoduleposition ? '<div class="sigProModulePosition">'.SimpleImageGalleryProHelper::loadModulePosition($loadmoduleposition,-1).'</div>' : NULL;

		// Variable cleanups for K2
		if(JRequest::getCmd('format')=='raw'){
			$this->plg_copyrights_start = '';
			$this->plg_copyrights_end = '';
		}

		// Includes
		require_once(dirname(__FILE__).DS.$this->plg_name.DS.'includes'.DS.'helper.php');



		// ----------------------------------- Prepare the output -----------------------------------

		// Process plugin tags
		if(preg_match_all($regex, $row->text, $matches, PREG_PATTERN_ORDER) > 0) {

			// start the replace loop
			foreach ($matches[0] as $key => $match) {

				$tagcontent = preg_replace("/{.+?}/", "", $match);

				if(strpos($tagcontent,'www.flickr.com')===false){
					/* example tag: {gallery}folder:200:80:1:2:jquery_colorbox:Galleria{/gallery} */
					$tagparams 						= explode(':',$tagcontent);
					$galleryFolder 				= $tagparams[0];
					$gal_width 						= (array_key_exists(1,$tagparams) && $tagparams[1]!='') ? $tagparams[1] : $thb_width;
					$gal_height 					= (array_key_exists(2,$tagparams) && $tagparams[2]!='') ? $tagparams[2] : $thb_height;
					$gal_singlethumbmode 	= (array_key_exists(3,$tagparams) && $tagparams[3]!='') ? $tagparams[3] : $singlethumbmode;
					$gal_captions 				= (array_key_exists(4,$tagparams) && $tagparams[4]!='') ? $tagparams[4] : $showcaptions;
					$gal_engine 					= (array_key_exists(5,$tagparams) && $tagparams[5]!='') ? $tagparams[5] : $popup_engine;
					$gal_template 				= (array_key_exists(6,$tagparams) && $tagparams[6]!='') ? $tagparams[6] : $thb_template;

					// Backwards compatibility
					if($gal_template=='Default') $gal_template = 'Classic';

					// HTML & CSS assignments
					if($gal_singlethumbmode) $singleThumbClass = 'SingleThumb'; else $singleThumbClass = '';
					$srcimgfolder = $galleries_rootfolder.'/'.$galleryFolder;
					$gal_id = substr(md5($key.$srcimgfolder),1,10);

					$flickrSetUrl = null;

					// Render the gallery
					$gallery = SimpleImageGalleryProHelper::renderGallery($srcimgfolder,$gal_width,$gal_height,$smartResize,$jpg_quality,$sortorder,$gal_captions,$row->title,$wordLimit,$cache_expire_time,$downloadFile,$gal_id);

					if(!$gallery){
						JError::raiseNotice('',JText::_('JW_SIGP_PLG_NOTICE_03').' '.$srcimgfolder);
						continue;
					}

				} else {
					// Make sure we got PHP5
					if(version_compare(PHP_VERSION, '5.0.0', '<')){
						JError::raiseNotice('',JText::_('PHP 5 is required for Flickr sets to be rendered by Simple Image Gallery Pro. Please consult your hosting company for upgrading your server to PHP5.'));
						continue;
					}

					// Get the Flickr set
					/* example tag: {gallery}http://www.flickr.com/photos/joomlaworks/sets/72157626907305094/:20:200:80:1:2:jquery_colorbox:Galleria{/gallery} */

					if(substr($tagcontent,0,7)!='http://') $tagcontent = 'http://'.$tagcontent;

					$tempFlickrParams 		= explode('http://',$tagcontent);
					$flickrParams					= explode(':',$tempFlickrParams[1]);
					$flickrSetUrl 				= $flickrParams[0];
					$gal_count 						= (array_key_exists(1,$flickrParams) && $flickrParams[1]!='') ? $flickrParams[1] : $flickrImageCount;
					$gal_width 						= (array_key_exists(2,$flickrParams) && $flickrParams[2]!='') ? $flickrParams[2] : $thb_width;
					$gal_height 					= (array_key_exists(3,$flickrParams) && $flickrParams[3]!='') ? $flickrParams[3] : $thb_height;
					$gal_singlethumbmode 	= (array_key_exists(4,$flickrParams) && $flickrParams[4]!='') ? $flickrParams[4] : $singlethumbmode;
					$gal_captions 				= (array_key_exists(5,$flickrParams) && $flickrParams[5]!='') ? $flickrParams[5] : $showcaptions;
					$gal_engine 					= (array_key_exists(6,$flickrParams) && $flickrParams[6]!='') ? $flickrParams[6] : $popup_engine;
					$gal_template 				= (array_key_exists(7,$flickrParams) && $flickrParams[7]!='') ? $flickrParams[7] : $thb_template;

					// Backwards compatibility
					if($gal_template=='Default') $gal_template = 'Classic';

					if(substr($flickrSetUrl,0,7)!='http://') $flickrSetUrl = 'http://'.$flickrSetUrl;

					$flickrRegex = "#flickr.com/photos/(.*?)/sets/(.*?)/#s";
					if(preg_match_all($flickrRegex, $flickrSetUrl, $flickrMatches, PREG_PATTERN_ORDER) > 0) {
						$flickrUsername = $flickrMatches[1][0];
						$flickrSetId = $flickrMatches[2][0];

						$flickrJson = 'http://query.yahooapis.com/v1/public/yql?q='.urlencode('SELECT * FROM query.multi WHERE queries=\'SELECT * FROM flickr.photosets.info WHERE photoset_id="'.$flickrSetId.'"; SELECT * FROM flickr.photosets.getPhotos('.$gal_count.') WHERE photoset_id="'.$flickrSetId.'" and extras="date_upload, date_taken, path_alias, url_sq, url_t, url_s, url_m, url_o"\'').'&format=json&env=store%3A%2F%2Fdatatables.org%2Falltableswithkeys';
						if($yqlMaxAge) $flickrJson .= '&_maxage='.$yqlMaxAge;
						$flickrJson .= '&callback=';

						$getFlickrJson = SimpleImageGalleryProHelper::readFile($flickrJson,'jw_sigpro');
						$getFlickrData = json_decode($getFlickrJson);
						if(is_null($getFlickrData)) continue;

						$flickrSetTitle = $getFlickrData->query->results->results[0]->photoset->title;

						// Initiate array to hold gallery
						$gallery = array();
						$galleryData = $getFlickrData->query->results->results[1]->photoset->photo;

						if(!count($galleryData)) continue;

						foreach($galleryData as $key=>$photo){

							// Caption display
							if($gal_captions==2){
								$gallery[$key]->captionTitle = $photo->title;
								$gallery[$key]->captionDescription = $photo->title.' - '.JText::_('JW_SIGP_LABELS_11').' <a href="'.$flickrSetUrl.'">'.$flickrSetTitle.'</a> '.JText::_('JW_SIGP_LABELS_12').' <a target="_blank" href="http://www.flickr.com/photos/'.$flickrUsername.'">'.$flickrUsername.'</a>';
							} elseif($gal_captions==1) {
								$gallery[$key]->captionTitle = JText::_('JW_SIGP_LABELS_09');
								$gallery[$key]->captionDescription = JText::_('JW_SIGP_LABELS_10').' <a target="_blank" href="'.$flickrSetUrl.'">'.$flickrSetTitle.'</a> '.JText::_('JW_SIGP_LABELS_12').' <a target="_blank" href="http://www.flickr.com/photos/'.$flickrUsername.'">'.$flickrUsername.'</a>';
							} else {
								$gallery[$key]->captionTitle = '';
								$gallery[$key]->captionDescription = '';
							}
							$gallery[$key]->captionTitle = htmlentities(strip_tags($gallery[$key]->captionTitle), ENT_QUOTES, 'utf-8');
							$gallery[$key]->captionDescription = htmlentities($gallery[$key]->captionDescription, ENT_QUOTES, 'utf-8');

							if(!isset($photo->url_o)) $photo->url_o = $photo->url_m;

							$gallery[$key]->downloadLink = SimpleImageGalleryProHelper::replaceHtml('<br /><br /><a class="sigProDownloadLink" target="_blank" href="'.$photo->url_o.'">'.JText::_('JW_SIGP_LABELS_13').'</a>');
							$tempFlickrFilename = array_slice(explode('/',substr($photo->url_m,0,-4).'_b.jpg'),-1);
							$gallery[$key]->filename = $tempFlickrFilename[0];
							$gallery[$key]->sourceImageFilePath = substr($photo->url_m,0,-4).'_b.jpg';
							$gallery[$key]->thumbImageFilePath = $photo->url_s;
							$gallery[$key]->width = $gal_width;
							$gallery[$key]->height = $gal_height;
						}

						// HTML & CSS assignments
						if($gal_singlethumbmode) $singleThumbClass = 'SingleThumb'; else $singleThumbClass = '';
						$gal_id = substr(md5($key.$getFlickrData->query->results->results[1]->photoset->id),1,10);

					} else {
						JError::raiseNotice('',JText::_('JW_SIGP_PLG_NOTICE_03'));
						continue;
					}

				}

				// CSS & JS includes: Append head includes, but not when we're outputing raw content (like in K2)
				if(JRequest::getCmd('format')=='' || JRequest::getCmd('format')=='html'){

					// Initiate variables
					$relName						= '';
					$extraClass					= '';
					$legacyHeadIncludes = '';

					$popupPath = "{$pluginLivePath}/includes/js/{$gal_engine}";
					$popupRequire = dirname(__FILE__).DS.$this->plg_name.DS.'includes'.DS.'js'.DS.$gal_engine.DS.'popup.php';

					if(file_exists($popupRequire) && is_readable($popupRequire)){
						require($popupRequire);
					}

					if(version_compare(JVERSION,'1.6.0','ge')) {
						JHtml::_('behavior.framework');
					} else {
						JHTML::_('behavior.mootools');
					}
					
					if(count($stylesheets)) foreach($stylesheets as $stylesheet) $document->addStyleSheet($popupPath.'/'.$stylesheet);
					if(count($stylesheetDeclarations)) foreach($stylesheetDeclarations as $stylesheetDeclaration) $document->addStyleDeclaration($stylesheetDeclaration);
					if(count($scripts)){
						foreach($scripts as $script){
							if(substr($script,0,4)=='http'){
								$document->addScript($script);
							} else {
								$document->addScript($popupPath.'/'.$script);
							}
						}
					}
					if(count($scriptDeclarations)) foreach($scriptDeclarations as $scriptDeclaration) $document->addScriptDeclaration($scriptDeclaration);

					if($legacyHeadIncludes) $document->addCustomTag($this->plg_copyrights_start.$legacyHeadIncludes.$this->plg_copyrights_end);

					$pluginCSS = SimpleImageGalleryProHelper::getTemplatePath($this->plg_name,'css/template.css',$gal_template);
					$pluginCSS = $pluginCSS->http;
					$document->addStyleSheet($pluginCSS,'text/css','screen');

					// Print CSS
					$document->addStyleSheet($pluginLivePath.'/includes/css/print.css','text/css','print');

					// Message to show when printing an article/item with a gallery
					$websiteURL = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != "off") ? "https://".$_SERVER['HTTP_HOST'] : "http://".$_SERVER['HTTP_HOST'];
					$itemPrintURL = $websiteURL.$_SERVER['REQUEST_URI'];
					$itemPrintURL = explode("#",$itemPrintURL);
					$itemPrintURL = $itemPrintURL[0].'#sigProGalleria'.$gal_id;
				} else {
					$itemPrintURL = false;
				}

				// Fetch the template
				ob_start();
				$templatePath = SimpleImageGalleryProHelper::getTemplatePath($this->plg_name,'default.php',$gal_template);
				$templatePath = $templatePath->file;
				include($templatePath);
				$getTemplate = $this->plg_copyrights_start.ob_get_contents().$this->plg_copyrights_end;
				ob_end_clean();

				// Output
				$plg_html = $getTemplate;

				// Do the replace
				$row->text = preg_replace( "#{".$this->plg_tag."}".$tagcontent."{/".$this->plg_tag."}#s", $plg_html , $row->text );

			} // end foreach

			// Global head includes
			if(JRequest::getCmd('format')=='' || JRequest::getCmd('format')=='html'){
				$document->addScript($pluginLivePath.'/includes/js/behaviour.js');
			}

		} // end if

	} // END FUNCTION

} // END CLASS
