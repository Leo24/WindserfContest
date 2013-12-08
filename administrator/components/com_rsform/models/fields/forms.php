<?php
/**
* @version 1.4.0
* @package RSFirewall! 1.4.0
* @copyright (C) 2009-2012 www.rsjoomla.com
* @license GPL, http://www.gnu.org/licenses/gpl-2.0.html
*/

defined('JPATH_PLATFORM') or die;

JFormHelper::loadFieldClass('list');

class JFormFieldForms extends JFormFieldList
{
	protected $type = 'Forms';
	
	protected function getOptions() {
		// Initialize variables.
		$options = array();
		
		$db 	= JFactory::getDbo();
		$query 	= $db->getQuery(true);
		$query->select($db->quoteName('FormId'))
			  ->select($db->quoteName('FormTitle'))
			  ->select($db->quoteName('FormName'))
			  ->from('#__rsform_forms');
		$db->setQuery($query);
		
		$forms = $db->loadObjectList();
		foreach ($forms as $form) {
			$tmp = JHtml::_('select.option', $form->FormId, sprintf('(%d) %s', $form->FormId, $form->FormName));

			// Add the option object to the result set.
			$options[] = $tmp;
		}

		reset($options);
		
		return $options;
	}
}
