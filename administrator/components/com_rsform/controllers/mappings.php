<?php
/**
* @version 1.4.0
* @package RSform!Pro 1.4.0
* @copyright (C) 2007-2011 www.rsjoomla.com
* @license GPL, http://www.gnu.org/copyleft/gpl.html
*/

defined('_JEXEC') or die('Restricted access');

jimport('joomla.application.component.controller');

class RSFormControllerMappings extends RSFormController
{
	function __construct()
	{
		parent::__construct();
	}
	
	function gettables()
	{
		$post		= JRequest::get('post');
		$model		= $this->getModel('mappings');
		
		$config = array('connection' => $post['connection'] , 'host' => $post['host'] , 'port' => $post['port'],'username' => $post['username'] , 'password' => $post['password'] , 'database' => $post['database']);
		
		$tables = $model->getMappingTables($config);
		$error = !is_array($tables) ? $tables : 1;
		
		$mtables = array(JHTML::_('select.option',  '0', JText::_( 'RSFP_FORM_MAPPINGS_SELECT_TABLE' ) ));
		if (!empty($tables) && is_array($tables))
			foreach ($tables as $table)
				$mtables[] = JHTML::_('select.option',  $table, $table );
		
		$html = '<table class="admintable">
					<tr>
						<td width="160" style="width: 160px;" align="right" class="key">'.JText::_('RSFP_FORM_MAPPINGS_TABLE').'</td>
						<td>
							'.JHTML::_('select.genericlist',  $mtables, 'table', 'class="inputbox" onchange="mpColumns(this.value)"', 'value', 'text').'
						<img id="mappingloader2" src="'.JURI::root().'administrator/components/com_rsform/assets/images/loading.gif" style="vertical-align: middle; display: none;" /></td>
					</tr>
				</table>';
		
		echo $error.'|'.$html;
		exit();
	}
	
	function columns()
	{
		$model		= $this->getModel('mappings');
		$post		= JRequest::get('post');
		$type		= JRequest::getVar('type','set');
		
		$config = array('connection' => $post['connection'] , 'host' => $post['host'] , 'port' => $post['port'],'username' => $post['username'] , 'password' => $post['password'] , 'database' => $post['database'], 'table' => $post['table']);
		
		echo RSFormProHelper::mappingsColumns($config,$type);
		exit();
	}
	
	function save()
	{
		$model		= $this->getModel('mappings');
		$row = $model->save();

		$html  = '<script type="text/javascript">';
		$html .= 'window.close();';
		
		if ($row !== false)
			$html .= 'window.opener.ShowMappings('.$row->formId.')'."\n";
		
		$html .= '</script>';
		
		echo $html;
		die();
	}
	
	function ordering()
	{
		$db = JFactory::getDBO();
		$post = JRequest::get('post');
		foreach ($post as $key => $val)
		{
			$key = (int) str_replace('mpid_', '', $key);
			$val = (int) $val;
			if (empty($key)) continue;
			
			$db->setQuery("UPDATE #__rsform_mappings SET `ordering` = '".$val."' WHERE `id` = '".$key."'");
			$db->execute();
		}
		
		echo 'Ok';
		exit();
	}
	
	function remove()
	{
		$model		= $this->getModel('mappings');
		$model->remove();
		$formId = JRequest::getInt('formId');
		
		JRequest::setVar('view', 'forms');
		JRequest::setVar('layout', 'edit_mappings');
		JRequest::setVar('tmpl', 'component');
		JRequest::setVar('formId', $formId);
		
		parent::display();
		exit();
	}
	
	function showmappings()
	{
		$formId = JRequest::getInt('formId');
		
		JRequest::setVar('view', 'forms');
		JRequest::setVar('layout', 'edit_mappings');
		JRequest::setVar('tmpl', 'component');
		JRequest::setVar('formId', $formId);
		
		parent::display();
		exit();
	}
	
}