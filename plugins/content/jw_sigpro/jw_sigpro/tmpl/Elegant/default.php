<?php
/**
 * @version		2.5.3
 * @package		Simple Image Gallery Pro
 * @author    JoomlaWorks - http://www.joomlaworks.gr
 * @copyright	Copyright (c) 2006 - 2011 JoomlaWorks Ltd. All rights reserved.
 * @license		Commercial - This code cannot be redistributed without permission from JoomlaWorks Ltd.
 */

// no direct access
defined('_JEXEC') or die('Restricted access');

?>

<ul id="sigProId<?php echo $gal_id; ?>" class="sigProContainer sigProElegant<?php echo ' sigProElegant'.$singleThumbClass; ?>">
	<?php foreach($gallery as $count=>$photo): ?>
	<li class="sigProThumb"<?php if($gal_singlethumbmode && $count>0) echo ' style="display:none;"'; ?>>
		<span class="sigProLinkOuterWrapper">
			<span class="sigProLinkWrapper">
				<a href="<?php echo $photo->sourceImageFilePath; ?>" class="sigProLink<?php echo $extraClass; ?>" style="width:<?php echo $photo->width; ?>px;height:<?php echo $photo->height; ?>px;" rel="<?php echo $relName; ?>[gallery<?php echo $gal_id; ?>]" title="<?php echo $photo->captionDescription.$photo->downloadLink.$modulePosition; ?>" target="_blank">
					<?php if(($gal_singlethumbmode && $count==0) || !$gal_singlethumbmode): ?>
					<img class="sigProImg" src="<?php echo $transparent; ?>" alt="<?php echo JText::_('JW_SIGP_LABELS_08').' '.$photo->filename; ?>" title="<?php echo JText::_('JW_SIGP_LABELS_08').' '.$photo->filename; ?>" style="width:<?php echo $photo->width; ?>px;height:<?php echo $photo->height; ?>px;background-image:url(<?php echo $photo->thumbImageFilePath; ?>);" />
					<?php endif; ?>
					<?php if($gal_captions): ?>
					<span class="sigProPseudoCaption"><b><?php echo $photo->captionTitle; ?></b></span>
					<span class="sigProCaption" title="<?php echo $photo->captionTitle; ?>"><?php echo $photo->captionTitle; ?></span>
					<?php endif; ?>
				</a>
			</span>
		</span>
	</li>
	<?php endforeach; ?>
	<li class="sigProClear">&nbsp;</li>
</ul>

<?php if(isset($flickrSetUrl)): ?>
<a class="sigProFlickrSetLink" title="<?php echo $flickrSetTitle; ?>" target="_blank" href="<?php echo $flickrSetUrl; ?>"><?php echo JText::_('JW_SIGP_PLG_FLICKRSET'); ?></a>
<?php endif; ?>

<?php if($itemPrintURL): ?>
<div class="sigProPrintMessage">
	<?php echo JText::_('JW_SIGP_PLG_PRINT_MESSAGE'); ?>:
	<br />
	<a title="<?php echo $row->title; ?>" href="<?php echo $itemPrintURL; ?>"><?php echo $itemPrintURL; ?></a>
</div>
<?php endif; ?>
