<?php
/**
 * @package Sj Image Slideshow
 * @version 1.0
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 * @copyright (c) 2013 YouTech Company. All Rights Reserved.
 * @author YouTech Company http://www.smartaddons.com
 */

defined('_JEXEC') or die;

JHtml::stylesheet('modules/'.$module->module.'/assets/css/slideshow.css');
if( !defined('SMART_JQUERY') && $params->get('include_jquery', 0) == "1" ){
	JHtml::script('modules/'.$module->module.'/assets/js/jquery-1.8.2.min.js');
	JHtml::script('modules/'.$module->module.'/assets/js/jquery-noconflict.js');
	define('SMART_JQUERY', 1);
}

JHtml::script('modules/'.$module->module.'/assets/js/jquery.cycle2.js');
JHtml::script('modules/'.$module->module.'/assets/js/jquery.cycle2.transition.js');

ImageHelper::setDefault($params);
	
$tag_id	= 'images_lideshow_'.rand().time();
	
if($params->get('pretext') != null) { ?>
<div class="pre-text">
	<?php echo $params->get('pretext'); ?>
</div>
<?php } ?>
<div class="slideshow theme4 " id="<?php echo $tag_id; ?>">
	<div class="sl-container"
		
	>
		<?php 	
		foreach ($list as $item) { 
			$rand_keys = array_rand($effect);
			$title = (isset($item['title']) && $item['title'] != '')?htmlentities($item['title'] ,ENT_QUOTES):''; 
			$desc = (isset($item['desc']) && $item['desc'] != '')?ImageSlideShow::truncate($item['desc'],$params->get('introtext_limit',100)):'';
			$link = (isset($item['link']) && $item['link'] != '')?$item['link']:'#';
			$target = (isset($item['target']) && $item['target'] != '')?$item['target']:'';
			$show_desc = $params->get('show_introtext');
			$show_readmore = $params->get('item_readmore_display'); 
			$show_title = $params->get('item_title_display');
			$condition = ($show_desc == 0 || $desc == ''); 
		?>
		<div class="sl-item " 
			data-cycle-emptycustom ="<?php echo ($condition)?'empty':'';  ?>"
			data-cycle-target="<?php echo htmlentities('target='.$target.'',ENT_QUOTES); ?>"
			data-cycle-readmoretext="<?php echo $params->get('item_readmore_text','readmore'); ?>" 
			data-cycle-href="<?php echo	$link ?>" 
			data-tile-vertical="<?php echo ($effect[$rand_keys] == 'tileSlide'|| $effect[$rand_keys] == 'tileBlind')?'false':''; ?>" 
			data-cycle-desc="<?php echo $desc; ?>"  
			data-cycle-fx="<?php echo ($fx == 'random')?$effect[$rand_keys]:$fx; ?>" 
			data-cycle-subtitle="<?php echo htmlentities(ImageSlideShow::truncate($title, $params->get('item_title_max_characs')),ENT_QUOTES); ?>" 
			data-cycle-titlehover="<?php echo $title; ?>"
			>
			
			<a href="<?php echo $link ?>" title="<?php echo $title ?>" target="<?php echo $target; ?>" >	
				<?php echo ImageSlideShow::imageTag($item['src']);?>
			</a>
			
		</div>
		<?php } ?>
		<div class="item-info"></div>	
	</div>
	<?php if($progress) {?>
		<div class="progress" ></div>
	<?php } ?>
	<div class='sl-control'>
		<span class="sl-left">
			<span class='sl-prev'></span>
		</span>
		<span class="sl-center">
			<span class="sl-caption"></span>
		</span>
		<span class="sl-right">
			<span class='sl-next'></span>
		</span>	
	</div>
</div>
<?php if($params->get('posttext') != null ) {  ?>
<div class="post-text">
	<?php echo $params->get('posttext'); ?>
</div>
<?php }?>

<script type="text/javascript">
//<![CDATA[   
	jQuery(document).ready(function($){
		;(function(element){
			var $element = $(element);
			var $slideshow = $('.sl-container',$element );
			var $prev = $('.sl-prev',$element);
			var $next = $('.sl-next',$element);
			var $sl_item = $('.sl-item',$slideshow);
			var $infor = $('.item-info',$slideshow);
			var $caption = $('.sl-caption',$element);
			var $progress = $('.progress',$element );
			$slideshow.cycle({
			
				fx:     				'<?php echo ($fx == 'random')?$effect[0]:$fx; ?>',
				slides: 				$sl_item,
				autoHeight:				'container',
				timeout:				<?php echo $params->get('timeout', 4000); ?>,
				overlay:				$infor,	
				caption:        		$caption ,
				startingSlide:			0,
				overlayTemplate:		"<?php echo  ImageSlideShow::getOverlayTemplate($params);  ?>",
				captionTemplate:		"<?php echo ImageSlideShow::getCaptionTemplate($params); ?>",
				swipe:					<?php echo ($params->get('swipe') == 1)?'true':'false'; ?>, 
				progress:				$progress,
				loader:					true, 	
				log:					false,
				allowWrap: 				true,
				random:					false,
				speed:      			<?php echo $params->get('speed',500); ?>,
				pauseOnHover: 			<?php echo ($params->get('pauseOnHover') == 1)?'true':'false'; ?>,
				prev: 					$prev,
				next: 					$next,
				captionPlugin:			'<?php echo ($params->get('overlay_effect') == 'none')?'caption':'caption2'; ?>',
				overlayFxOut:           '<?php echo  ($params->get('overlay_effect') == 'fade')?'fadeOut':'slideUp' ?>', 
				overlayFxIn:            '<?php echo  ($params->get('overlay_effect') == 'fade')?'fadeIn':'slideDown' ?>',
				captionFxOut:           '<?php echo  ($params->get('overlay_effect') == 'fade')?'fadeOut':'slideUp' ?>', 
				captionFxIn:            '<?php echo  ($params->get('overlay_effect') == 'fade')?'fadeIn':'slideDown' ?>',
				pagerActiveClass:		'sl-pager-active'
			});
			<?php if (!$play){ ?>
				$slideshow.cycle('pause');
			<?php } ?>
			$('.sl-play-pause',$element).click(function(){
				if ( $(this).hasClass('sl-play') ){
					$(this).toggleClass('sl-play sl-pause');
					slideshow.cycle('resume');
				} else {
					$(this).toggleClass('sl-play sl-pause');
					slideshow.cycle('pause');
				}
			});

			<?php if($progress) { ?>
				$slideshow.on( 'cycle-initialized cycle-before', function( e, opts ) {
					$progress.stop(true).css( {width:0 } );
					$('.sl-caption',$slideshow).css({opacity:1});
				});

				$slideshow.on( 'cycle-initialized cycle-after', function( e, opts ) {
					$('.sl-caption',$slideshow).css({opacity:1});
					if ( ! $slideshow.is('.cycle-paused') )
						$progress.animate({ width: '100%' }, opts.timeout, 'linear' );
						
						
				});

				$slideshow.on( 'cycle-paused', function( e, opts ) {
				   $progress.stop(); 
				});

				$slideshow.on( 'cycle-resumed', function( e, opts, timeoutRemaining ) {
					$progress.animate({ width: '100%' }, timeoutRemaining, 'linear' );
				});
			<?php } ?>
		})('#<?php echo $tag_id; ?>')
	});
//]]>	
</script>



