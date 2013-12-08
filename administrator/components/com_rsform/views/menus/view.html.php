<?php
/**
* @version 1.4.0
* @package RSform!Pro 1.4.0
* @copyright (C) 2007-2011 www.rsjoomla.com
* @license GPL, http://www.gnu.org/copyleft/gpl.html
*/

defined('_JEXEC') or die('Restricted access');

jimport('joomla.application.component.view');

class RSFormViewMenus extends JViewLegacy
{
	function display($tpl = null)
	{
		$mainframe = JFactory::getApplication();
		
		JToolBarHelper::title('RSForm! Pro','rsform');
		
		if (RSFormProHelper::isJ16())
		{
			$lang = JFactory::getLanguage();
			$lang->load('com_rsform.sys', JPATH_ADMINISTRATOR);
			
			JSubMenuHelper::addEntry(JText::_('COM_RSFORM_MANAGE_FORMS'), 'index.php?option=com_rsform&task=forms.manage', true);
			JSubMenuHelper::addEntry(JText::_('COM_RSFORM_MANAGE_SUBMISSIONS'), 'index.php?option=com_rsform&task=submissions.manage');
			JSubMenuHelper::addEntry(JText::_('COM_RSFORM_CONFIGURATION'), 'index.php?option=com_rsform&task=configuration.edit');
			JSubMenuHelper::addEntry(JText::_('COM_RSFORM_BACKUP_RESTORE'), 'index.php?option=com_rsform&task=backup.restore');
			JSubMenuHelper::addEntry(JText::_('COM_RSFORM_UPDATES'), 'index.php?option=com_rsform&task=updates.manage');
			JSubMenuHelper::addEntry(JText::_('COM_RSFORM_PLUGINS'), 'index.php?option=com_rsform&task=goto.plugins');
		}
		
		$this->formId = JRequest::getInt('formId');
		$this->formTitle = $this->get('formtitle');
		$this->menus = $this->get('menus');
		$this->pagination = $this->get('pagination');
		
		parent::display($tpl);
	}
}