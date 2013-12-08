<?php
/**
* @version 1.4.0
* @package RSform!Pro 1.4.0
* @copyright (C) 2007-2011 www.rsjoomla.com
* @license GPL, http://www.gnu.org/copyleft/gpl.html
*/

defined('_JEXEC') or die('Restricted access');

JFormHelper::loadFieldClass('list');

class JFormFieldLang extends JFormFieldList
{
	protected $type = 'Lang';

	protected function getOptions()
	{
		// Initialize variables.
		$options = array();
		
		$lang = JFactory::getLanguage();
		$lang->load('com_rsform');
		$languages = $lang->getKnownLanguages(JPATH_SITE);
		$options   = array();
		$options[] = JHTML::_('select.option', '', JText::_('RSFP_SUBMISSIONS_ALL_LANGUAGES'));
		foreach ($languages as $language => $properties)
			$options[] = JHTML::_('select.option', $language, $properties['name']);

		reset($options);
		
		return $options;
	}
}