<?php
/**
* @version 1.4.0
* @package RSform!Pro 1.4.0
* @copyright (C) 2007-2011 www.rsjoomla.com
* @license GPL, http://www.gnu.org/copyleft/gpl.html
*/

defined('_JEXEC') or die('Restricted access');

JHTML::_('behavior.tooltip');
?>
<form action="index.php" method="post" name="adminForm">
	<div class="span2">
		<?php echo $this->sidebar; ?>
	</div>
	<div class="span10">
		<div id="dashboard-left">
			<?php echo $this->loadTemplate('buttons'); ?>
		</div>
		<div id="dashboard-right" class="hidden-phone hidden-tablet">
			<?php echo $this->loadTemplate('version'); ?>
			<p align="center"><a href="http://www.rsjoomla.com/joomla-components/joomla-security.html?utm_source=rsform&amp;utm_medium=banner_approved&amp;utm_campaign=rsfirewall" target="_blank"><img src="components/com_rsform/assets/images/rsfirewall-approved.png" align="middle" alt="RSFirewall! Approved"/></a></p>
		</div>
	</div>
	
	<input type="hidden" name="option" value="com_rsform" />
	<input type="hidden" name="task" value="" />
</form>