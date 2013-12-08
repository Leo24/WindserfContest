<?php
/**
* @version 1.4.0
* @package RSFirewall! 1.4.0
* @copyright (C) 2009-2012 www.rsjoomla.com
* @license GPL, http://www.gnu.org/licenses/gpl-2.0.html
*/

defined('_JEXEC') or die('Restricted access');

class RSFormViewUpdates extends JViewLegacy
{
	protected $hash;
	protected $jversion;
	protected $revision;
	protected $sidebar;
	
	public function display($tpl = null) {
		$this->addToolBar();
		
		$this->hash 	= $this->get('hash');
		$this->jversion = $this->get('joomlaVersion');
		$this->revision = $this->get('revision');
		
		$this->sidebar	= $this->get('SideBar');
		
		parent::display($tpl);
	}
	
	protected function addToolbar() {
		// set title
		JToolBarHelper::title('RSForm! Pro', 'rsform');
		
		require_once JPATH_COMPONENT.'/helpers/toolbar.php';
		RSFormProToolbarHelper::addToolbar('updates');
	}
}