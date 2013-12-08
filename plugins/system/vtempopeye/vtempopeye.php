<?php
/**
* @Copyright Copyright (C) 2010 VTEM . All rights reserved.
* @license GNU/GPL http://www.gnu.org/copyleft/gpl.html
* @link     	http://www.vtem.net
**/

defined('_JEXEC') or die( 'Restricted access' );
jimport('joomla.environment.response');
jimport('joomla.document.document');
jimport('joomla.plugin.helper');
jimport('joomla.plugin.plugin');
jimport('joomla.html');
jimport('joomla.application.module.helper');

class plgSystemVtempopeye extends JPlugin {
    function plgSystemVtempopeye(&$subject, $config) {
		parent::__construct($subject, $config);
		$this->mainframe = JFactory::getApplication();
	}

	public function onAfterDispatch() {
		if($this->mainframe->isAdmin()) {
			return;
		}
		$document = JFactory::getDocument();
        $document->addStyleSheet( JURI::root() . 'media/plg_vtempopeye/css/style.css' );
		if($this->params->get("script") == 1){
		$document->addScript(JURI::root().'media/plg_vtempopeye/js/jquery-1.7.2.min.js');
		}
		$document->addScript(JURI::root().'media/plg_vtempopeye/js/jquery.popeye-2.1.min.js');
	}
	
	public function onAfterRender() {
	  $body = JResponse::getBody();
	  if(!JString::strpos($body, 'vtempopeye') || $this->mainframe->isAdmin()){
	    return;
	  }	
	  $regex = "#{vtempopeye\s*(.*?)}#s";   
	  preg_match_all($regex, $body, $matches);
 	  if (!empty($matches[0])) {
 	    $this->_build($body, $matches);
 	    return true;
	  }
		return false;
	}

	private function _build( $body, $matches ){
	    for( $i = 0; $i < count($matches[0]); $i++ ){
			$tipParams = $matches[1][$i];
			$popeye    = "";
			$thumbwidth = $this->_params( $tipParams, "width", $this->params->get('width', 320));
			$thumbheight = $this->_params( $tipParams, "height", $this->params->get('height', 200));
			$navigation = $this->_params( $tipParams, "navigation", $this->params->get('navigation', 'hover'));
			$imageCaption = $this->_params( $tipParams, "imageCaption", $this->params->get('imageCaption', 'hover'));
			$direction = $this->_params( $tipParams, "direction", $this->params->get('direction','right'));
			$opacity = $this->_params( $tipParams, "opacity", $this->params->get('opacity',0.8));
			$duration = $this->_params( $tipParams, "duration", $this->params->get('duration', 240));
			$slidespeed = $this->_params( $tipParams, "slidespeed", $this->params->get('slidespeed',2000));
			$autoslide = $this->_params( $tipParams, "autoslide", $this->params->get('autoslide',0));
			$pstyle = $this->_params( $tipParams, "pstyle", $this->params->get('pstyle','style1'));
			
			$float      = $this->_params( $tipParams, "float", false );
			$counttext      = $this->_params( $tipParams, "counttext", false );
			$vtcaption      = str_replace('"','\'',strip_tags($this->_params( $tipParams, "caption", false )));
			$folder      = $this->_params( $tipParams, "folder", false );
			$imagelist      = trim(strip_tags($this->_params( $tipParams, "imagelist", false )));
			
			if($folder){$imagePath = $this->cleanDir($folder);
			}else{$imagePath = $this->cleanDir($this->_params( $tipParams, "imagepath", 'images/sampledata/fruitshop' ));}
			$sortCriteria = $this->_params( $tipParams, "sortCriteria", 0 );
			$sortOrder = $this->_params( $tipParams, "sortOrder", 'asc' );
			
			if($imagelist)
			    $images = explode(";", $imagelist);
			else
				$images = $this->imageList($imagePath, $sortCriteria, $sortOrder);						
			  $popeye .='<div class="ppy '.$pstyle.'" id="vtempopeye'.(int)$i.'" style="float:'.$float.'"><ul class="ppy-imglist">';
				foreach($images as $key => $img) {
				  $vttitles = explode(";",$vtcaption);
				  $vttitle = (isset($vttitles[$key])) ? $vttitles[$key] : '';
				  $popeye .='<li><a href="'.JURI::root().($imagelist ? "" : $imagePath).$img.'">
						 <img src="'.JURI::root().($imagelist ? "" : $imagePath).$img.'" alt="" style="width:'.$thumbwidth.'px;"/>
						 </a>
						 <div class="ppy-extcaption">'.$vttitle.'</div>
						 </li>';
				}		
				switch($pstyle){
				  default:
				  case "style1":
				  case "style4":
						$popeye .='</ul><div class="ppy-outer">
										<div class="ppy-stage" style="width:'.$thumbwidth.'px;height:'.$thumbheight.'px;">
											<div class="ppy-nav">
											   <div class="nav-wrap">
												<a class="ppy-prev" title="Previous image">Previous image</a>
												<a class="ppy-switch-enlarge" title="Enlarge">Enlarge</a>
												<a class="ppy-switch-compact" title="Close">Close</a>
												<a class="ppy-play" title="Play Slideshow">Play Slideshow</a>
												<a class="ppy-pause" title="Stop Slideshow">Stop Slideshow</a>
												<a class="ppy-next" title="Next image">Next image</a>
											   </div>
											</div>
										</div>
									</div>
									<div class="ppy-caption">
										<div class="ppy-counter">
											'.($counttext ? $counttext : 'Image').' <strong class="ppy-current"></strong> / <strong class="ppy-total"></strong> 
										</div>
										<span class="ppy-text"></span>
									</div>';
				   break;
				  case "style2":
				  case "style5":
						$popeye .='</ul><div class="ppy-outer">
										<div class="ppy-stage" style="width:'.$thumbwidth.'px;height:'.$thumbheight.'px;">
											<div class="ppy-counter">
												<strong class="ppy-current"></strong> / <strong class="ppy-total"></strong> 
											</div>
										</div>
										<div class="ppy-nav">
											<div class="nav-wrap">
												<a class="ppy-next" title="Next image">Next image</a>
												<a class="ppy-switch-enlarge" title="Enlarge">Enlarge</a>
												<a class="ppy-switch-compact" title="Close">Close</a>
												<a class="ppy-play" title="Play Slideshow">Play Slideshow</a>
												<a class="ppy-pause" title="Stop Slideshow">Stop Slideshow</a>
												<a class="ppy-prev" title="Previous image">Previous image</a>
											</div>
										</div>
									</div>
									<div class="ppy-caption">
											<span class="ppy-text"></span>
									</div>';
				   break;
				   case "style3":
						$popeye .='</ul><div class="ppy-outer">
										<div class="ppy-stage" style="width:'.$thumbwidth.'px;height:'.$thumbheight.'px;">
											<div class="ppy-nav">
												<div class="nav-wrap">
													<a class="ppy-prev" title="Previous image">Previous image</a>
													<a class="ppy-play" title="Play Slideshow">Play Slideshow</a>
													<a class="ppy-pause" title="Stop Slideshow">Stop Slideshow</a>
													<a class="ppy-next" title="Next image">Next image</a>
												</div>
											</div>
											<div class="ppy-counter">
												<strong class="ppy-current"></strong> / <strong class="ppy-total"></strong> 
											</div>
										</div>
										<div class="ppy-caption">
											<span class="ppy-text"></span>
										</div>
									</div>';
				   break;
				  } 
			    $popeye .='</div>';
				$popeye .= '<script type="text/javascript">
						 jQuery(document).ready(function(){
							 var options = {
								    navigation: "'.$navigation.'",
									caption:    "'.$imageCaption.'",  
									direction:  "'.$direction.'", 
									duration:   '.$duration.', 
									opacity:    '.$opacity.',         
									slidespeed: '.$slidespeed.',
									autoslide:  '.$autoslide.'
								 };
						     jQuery("#vtempopeye'.(int)$i.'").popeye(options);
						});</script>';
						
			$body = str_replace($matches[0][$i], $popeye, $body);
		}
		JResponse::setBody($body);
    }
	private function _params( $TipParams, $param, $default = false ){
		$regex = "/". $param ."=(\s*\[.*?\])/s";
		preg_match_all( $regex, $TipParams, $options );
		$value = !empty($options[1][0]) ? JString::trim( $options[1][0], '[]' ) : trim($default);
		return $value;
	}
	
	private function imageList ($directory, $sortcriteria, $sortorder) {
	    $results = array();
	    $handler = opendir($directory);
			$i = 0;
	    while ($file = readdir($handler)) {
	        if ($file != '.' && $file != '..' && $this->isImage($file)) {
						$results[$i][0] = $file;
						$results[$i][1] = filemtime($directory . "/" .$file);
						$i++;
					}
	    }
	    closedir($handler);

			foreach($results as $res) {
				if ($sortcriteria == 0 ) $sortAux[] = $res[0];
				else $sortAux[] = $res[1];
			}

			if ($sortorder == 0) {
				array_multisort($sortAux, SORT_ASC, $results);
			} elseif ($sortorder == 2) {
				srand((float)microtime() * 1000000);
				shuffle($results);
			} else {
				array_multisort($sortAux, SORT_DESC, $results);
			}

			foreach($results as $res) {
				$sorted_results[] = $res[0];
			}

	    return $sorted_results;
	}

	private function isImage($file) {
		$imagetypes = array(".jpg", ".jpeg", ".gif", ".png");
		$extension = substr($file,strrpos($file,"."));
		if (in_array($extension, $imagetypes)) return true;
		else return false;
	}
	
	private function cleanDir($dir) {
		if (substr($dir, -1, 1) == '/')
			return $dir;
		else
			return $dir . "/";
	}
}

