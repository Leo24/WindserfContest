<?php
/**
* @version 1.4.0
* @package RSForm! Pro 1.4.0
* @copyright (C) 2009-2012 www.rsjoomla.com
* @license GPL, http://www.gnu.org/licenses/gpl-2.0.html
*/

defined('_JEXEC') or die('Restricted access');

class RSFormModelUpdates extends JModelLegacy
{
	public function getHash() {
		$version = new RSFormProVersion();
		return md5(RSFormProConfig::getInstance()->get('global.register.code').$version->key);
	}
	
	public function getJoomlaVersion() {
		$jversion = new JVersion();
		return $jversion->getShortVersion();
	}
	
	public function getRevision() {
		$version = new RSFormProVersion();
		return $version->revision;
	}
	
	public function getSideBar() {
		require_once JPATH_COMPONENT.'/helpers/toolbar.php';
		
		return RSFormProToolbarHelper::render();
	}
}