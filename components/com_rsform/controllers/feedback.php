<?php
/**
* @version 1.4.0
* @package RSform!Pro 1.4.0
* @copyright (C) 2007-2011 www.rsjoomla.com
* @license GPL, http://www.gnu.org/copyleft/gpl.html
*/

defined('_JEXEC') or die('Restricted access');

class RSFormControllerFeedback extends RSFormController
{
	public function __construct() {
		parent::__construct();
	}
	
	public function image() {
		$session = JFactory::getSession();
		$app	 = JFactory::getApplication();
		if ($params = $session->get('mod_rsform_feedback.params')) {
			$position = $params->get('position', 'left');
			if ($position == 'left') {
				$angle = 270;
			} elseif ($position == 'right') {
				$angle = 90;
			} elseif ($position == 'top') {
				$angle = 0;
			} elseif ($position == 'bottom') {
				$angle = 0;
			}
			
			$options = array(
				'string' 	 => $params->get('string', 'Contact Us!'),
				'size' 		 => $params->get('font-size', 14),
				'angle' 	 => $angle,
				'text-color' => $params->get('text-color', '#000000'),
				'bg-color' 	 => $params->get('bg-color', '#FFFFFF'),
				'font' 		 => $params->get('font', 'ariblk').'.ttf',
				'type' 		 => $params->get('type', 'png'),
				'transparent'=> 0
			);
			
			require_once JPATH_SITE.'/modules/mod_rsform_feedback/helper.php';
			$img = new RSFormProImageText($options);
			$img->showImage();
		}
		$app->close();
	}
}