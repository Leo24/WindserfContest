<?php
/*
# ------------------------------------------------------------------------
# Templates for Joomla 2.5 - Joomla 3.5
# ------------------------------------------------------------------------
# Copyright (C) 2011-2013 Jtemplate.ru. All Rights Reserved.
# @license - PHP files are GNU/GPL V2.
# Author: Makeev Vladimir
# Websites:  http://www.jtemplate.ru 
# ---------  http://code.google.com/p/jtemplate/   
# ------------------------------------------------------------------------
*/

// no direct access
defined('_JEXEC') or die;
?>
<div id="jt-vk-groups<?php echo $moduleclass_sfx; ?>">
	<div id="vk_groups"></div>
	<script type="text/javascript">
	VK.Widgets.Group("vk_groups", {mode: <?php echo $mode ?>, width: "<?php echo $width; ?>", height: "<?php echo $height; ?>"}, <?php echo $group_id; ?>);
	</script>
	<div style="clear:both;"></div>
</div>


