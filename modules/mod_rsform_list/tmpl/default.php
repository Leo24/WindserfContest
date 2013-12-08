<?php
/**
* @version 1.3.0
* @package RSform!Pro 1.3.0
* @copyright (C) 2007-2010 www.rsjoomla.com
* @license GPL, http://www.gnu.org/copyleft/gpl.html
*/

// no direct access
defined('_JEXEC') or die('Restricted access');
?>
<?php if ($moduleclass_sfx) { ?>
<div class="<?php echo $moduleclass_sfx; ?>">
<?php } ?>
	<?php echo $html; ?>
<?php if ($moduleclass_sfx) { ?>
</div>
<?php } ?>