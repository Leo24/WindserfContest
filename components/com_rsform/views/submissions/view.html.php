<?php
/**
* @version 1.4.0
* @package RSform!Pro 1.4.0
* @copyright (C) 2007-2011 www.rsjoomla.com
* @license GPL, http://www.gnu.org/copyleft/gpl.html
*/

defined('_JEXEC') or die('Restricted access');

jimport( 'joomla.application.component.view');

class RSFormViewSubmissions extends JViewLegacy
{
	function display( $tpl = null )
	{
		$mainframe = JFactory::getApplication();
		
		$params = $mainframe->getParams('com_rsform');
		$this->params = $params;
		
		$layout = JRequest::getVar('layout', 'default');
		if ($layout == 'default')
		{
			$this->filter = $this->get('filter');
			$this->itemid = $this->get('Itemid');
			$this->template = $this->get('template');
			$this->pagination = $this->get('pagination');
		}
		else
		{
			$this->template = $this->get('template');
		}
		
		parent::display($tpl);
	}
}