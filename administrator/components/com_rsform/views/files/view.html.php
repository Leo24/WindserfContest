<?php
/**
* @version 1.4.0
* @package RSform!Pro 1.4.0
* @copyright (C) 2007-2011 www.rsjoomla.com
* @license GPL, http://www.gnu.org/copyleft/gpl.html
*/

defined('_JEXEC') or die('Restricted access');

jimport( 'joomla.application.component.view');

class RSFormViewFiles extends JViewLegacy
{
	function display( $tpl = null )
	{
		$this->canUpload = $this->get('canUpload');
		$this->files = $this->get('files');
		$this->folders = $this->get('folders');
		$this->elements = $this->get('elements');
		$this->current = $this->get('current');
		$this->previous = $this->get('previous');
		
		parent::display($tpl);
	}
}