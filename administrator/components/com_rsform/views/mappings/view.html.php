<?php
/**
* @version 1.4.0
* @package RSform!Pro 1.4.0
* @copyright (C) 2007-2011 www.rsjoomla.com
* @license GPL, http://www.gnu.org/copyleft/gpl.html
*/

defined('_JEXEC') or die('Restricted access');

jimport( 'joomla.application.component.view');

class RSFormViewMappings extends JViewLegacy
{
	function display( $tpl = null )
	{
		$formId = JRequest::getVar('formId');
		$this->formId = $formId;
		$this->fields = $this->get('fields');
		
		$model = $this->getModel('mappings');
		$this->mapping = $this->get('mapping');
		$lists['MappingConnection'] = JHTML::_('select.booleanlist', 'connection', 'class="inputbox" onclick="enableDbDetails(this.value)"', $this->mapping->connection,JText::_('RSFP_FORM_MAPPINGS_CONNECTION_REMOTE'),JText::_('RSFP_FORM_MAPPINGS_CONNECTION_LOCAL'));
		$arr = array(
					JHTML::_('select.option',  '0', JText::_( 'RSFP_FORM_MAPPINGS_METHOD_INSERT' ) ),
					JHTML::_('select.option',  '1', JText::_( 'RSFP_FORM_MAPPINGS_METHOD_UPDATE' ) ),
					JHTML::_('select.option',  '2', JText::_( 'RSFP_FORM_MAPPINGS_METHOD_DELETE' ) )
				);
		
		$lists['MappingMethod'] = JHTML::_('select.radiolist',  $arr, 'method', 'class="inputbox"', 'value', 'text', (int) $this->mapping->method );
		
		$config = array('connection' => $this->mapping->connection , 'host' => $this->mapping->host , 'port' => $this->mapping->port,'username' => $this->mapping->username , 'password' => $this->mapping->password , 'database' => $this->mapping->database , 'table' => $this->mapping->table);
		
		$tables = $model->getMappingTables($config);			
		$mtables = array(JHTML::_('select.option',  '0', JText::_( 'RSFP_FORM_MAPPINGS_SELECT_TABLE' ) ));
		if (!empty($tables))
			foreach ($tables as $table)
				$mtables[] = JHTML::_('select.option',  $table, $table );
		
		$lists['tables'] = JHTML::_('select.genericlist',  $mtables, 'table', 'class="inputbox" onchange="mpColumns(this.value)"', 'value', 'text',$this->mapping->table);
		
		$this->lists = $lists;
		$this->config = $config;
		
		parent::display($tpl);
	}
}