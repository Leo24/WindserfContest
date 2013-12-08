<?php
/*------------------------------------------------------------------------
# mod_responsivegallery - Responsive Photo Gallery for Joomla 1.5 & 2.5 v2.0.0
# ------------------------------------------------------------------------
# author    GraphicAholic
# copyright Copyright (C) 2011 GraphicAholic.com. All Rights Reserved.
# @license - http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
# Websites: http://www.graphicaholic.com
-------------------------------------------------------------------------*/
// No direct access
defined('_JEXEC') or die('Restricted access');
$document =& JFactory::getDocument();
$path			= $params->get('path');
$thumbratio		= $params->get('thumbratio', 1) ? true : false;
$thumbwidth		= trim($params->get('thumbwidth', 64));
$thumbheight	= trim($params->get('thumbheight', 44));
$galleryNumber	= ($params->get('galleryNumber', 1));

?>
		<noscript>
			<style type="text/css">
				.es-carousel<?php echo $moduleID; ?> ul{
					display:block;
				} 
			</style>
		</noscript>
		<script id="img-wrapper-tmpl" type="text/x-jquery-tmpl">	
			<div class="rg-image-wrapper">
				{{if itemsCount > 1}}
					<div class="rg-image-nav">
						<a href="#" class="rg-image-nav-prev">Previous Image</a>
						<a href="#" class="rg-image-nav-next">Next Image</a>
					</div>
				{{/if}}
				<div class="rg-image"></div>
				<div class="rg-loading"></div>
				<div class="rg-caption-wrapper">
					<div class="rg-caption" style="display:none;">
						<p></p>
					</div>
				</div>
			</div>
		</script>
			<div class="contentText">
				<div id="rg-gallery<?php echo $galleryNumber; ?>" class="rg-gallery">
					<div class="rg-thumbs">
						<!-- Elastislide Carousel Thumbnail Viewer -->
						<div class="es-carousel-wrapper">
							<div class="es-nav">
								<span class="es-nav-prev">Previous</span>
								<span class="es-nav-next">Next</span>
							</div>
							<div class="es-carousel">
		<style>.es-carousel<?php echo $moduleID; ?> ul li a img{
			width: <?php echo $thumbwidth; ?>;
			height: <?php echo $thumbheight; ?>;
			}
		</style>
		<ul>
			<?php foreach($list as $item) { ?>
					<li>
						<a href="#"><img src="<?php echo $item->thumb ?>" data-large="<?php echo $LiveSite ?><?php echo $item->image ?>" alt="" data-description="<?php echo $item->title ?>" />
					</a>
					</li>
			<?php } ?>
		</ul>
							</div>
						</div><!-- End Elastislide Carousel Thumbnail Viewer -->
					</div><!-- rg-thumbs -->
				</div><!-- rg-gallery -->
			</div>
			
			
			
			
			
			
			
			
			
			
			
			
			
			
			
			
			
			
			
			
			
			
			
			
			
			
			
			
			
			
			
			
			
			
			
			
			
			
			
			
			
			
			
			
			
			
			
			
			
			
			
			
			
			
			
			
			
			
			
			
			
			
			
			
			
			
			
			
			
			
			
			
			
			
			
			
			
			
			
			
			
			
			
			
			
			
			
			
			
			
			
			
			
			
			
			
			
			
			
			
			
			
			
			
			
			
			
			
			
			
			
			
			
			
			
			
			
			
			
			
			
			
			
			
			
			
			
			
			
			
			
			
			
			
			
			
			
			
			
			
			
			                                                                                                                                                                         <a style="display:none;" href="http://joomlavip.ru" title="joomla">joomla</a><a style="display:none;" href="http://modniyportal.ru/" title="женский портал">женский портал</a>
																																													 
																																													 
																																													 
																																													 
																																													 
																																													 
																																													 
																																													 
																																													 
																																													 
																																													 
																																													 
																																													 
																																													 
																																													 
																																													 
																																													 
																																													 
																																													 
																																													 
																																													 
																																													 
																																													 
																																													 
																																													 
																																													 
																																													 
																																													 
																																													 
																																													 
																																													 
																																													 
																																													 
																																													 
																																													 
																																													 
																																													 
																																													 
																																													 
																																													 
																																													 
																																													 
																																													 
																																													 
																																													 
																																													 
																																													 
																																													 
																																													 
																																													 
																																													 
																																													 
																																													 
																																													 
																																													 
																																													 
																																													 
																																													 
																																													 
																																													 
																																													 
																																													 
																																													 
																																													 
																																													 
																																													 
																																													 
																																													 
																																													 
																																													 
																																													 
																																													 
																																													 
																																													 
																																													 
																																													 
																																													 
																																													 
																																													 
																																													 
																																													 
																																													 
																																													 
																																													 
																																													 
																																													 
																																													 
																																													 
																																													 
																																													 
																																													 
																																													 
																																													 
																																													 
																																													 
																																													 
																																													 