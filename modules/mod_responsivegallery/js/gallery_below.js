// ======================= Gallery Number Function Call ===============================
jQuery(document).ready(function(){
    gallery('rg-gallery1');
    gallery('rg-gallery2');
    gallery('rg-gallery3');
    gallery('rg-gallery4');
    gallery('rg-gallery5');
    gallery('rg-gallery6');
    gallery('rg-gallery7');
    gallery('rg-gallery8');
    gallery('rg-gallery9');
    gallery('rg-gallery10');
    gallery('rg-gallery11');
    gallery('rg-gallery12');
    gallery('rg-gallery13');
    gallery('rg-gallery14');
    gallery('rg-gallery15');
    gallery('rg-gallery16');
    gallery('rg-gallery17');
    gallery('rg-gallery18');
    gallery('rg-gallery19');
    gallery('rg-gallery20');
    gallery('rg-gallery21');
    gallery('rg-gallery22');
    gallery('rg-gallery23');
    gallery('rg-gallery24');
    gallery('rg-gallery25');
    gallery('rg-gallery26');
    gallery('rg-gallery27');
    gallery('rg-gallery28');
    gallery('rg-gallery29');
    gallery('rg-gallery30');
    gallery('rg-gallery31');
    gallery('rg-gallery32');
    gallery('rg-gallery33');
    gallery('rg-gallery34');
    gallery('rg-gallery35');
    gallery('rg-gallery36');
    gallery('rg-gallery37');
    gallery('rg-gallery38');
    gallery('rg-gallery39');
    gallery('rg-gallery40');
    gallery('rg-gallery41');
    gallery('rg-gallery42');
    gallery('rg-gallery43');
    gallery('rg-gallery44');
    gallery('rg-gallery45');
    gallery('rg-gallery46');
    gallery('rg-gallery47');
    gallery('rg-gallery48');
    gallery('rg-gallery49');
    gallery('rg-gallery50');
});

function gallery(gal) {
	// ======================= imagesLoaded Plugin ===============================
	// https://github.com/desandro/imagesloaded

	// jQuery('#my-container').imagesLoaded(myFunction)
	// execute a callback when all images have loaded.
	// needed because .load() doesn't work on cached images

	// callback function gets image collection as argument
	//  this is the container

	// original: mit license. paul irish. 2010.
	// contributors: Oren Solomianik, David DeSandro, Yiannis Chatzikonstantinou

	jQuery.fn.imagesLoaded 		= function( callback ) {
	var jQueryimages = this.find('img'),
		len 	= jQueryimages.length,
		_this 	= this,
		blank 	= 'data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///ywAAAAAAQABAAACAUwAOw==';

	function triggerCallback() {
		callback.call( _this, jQueryimages );
	}

	function imgLoaded() {
		if ( --len <= 0 && this.src !== blank ){
			setTimeout( triggerCallback );
			jQueryimages.off( 'load error', imgLoaded );
		}
	}

	if ( !len ) {
		triggerCallback();
	}

	jQueryimages.on( 'load error',  imgLoaded ).each( function() {
		// cached images don't fire load sometimes, so we reset src.
		if (this.complete || this.complete === undefined){
			var src = this.src;
			// webkit hack from http://groups.google.com/group/jquery-dev/browse_thread/thread/eee6ab7b2da50e1f
			// data uri bypasses webkit log warning (thx doug jones)
			this.src = blank;
			this.src = src;
		}
	});

	return this;
	};

	// gallery container
	var jQueryrgGallery			= jQuery('#'+gal),
	// carousel container
	jQueryesCarousel			= jQueryrgGallery.find('div.es-carousel-wrapper'),
	// the carousel items
	jQueryitems				= jQueryesCarousel.find('ul > li'),
	// total number of items
	itemsCount			= jQueryitems.length;
	
	Gallery				= (function() {
			// index of the current item
		var current			= 0, 
			// mode : carousel || fullview
			mode 			= 'carousel',
			// control if one image is being loaded
			anim			= false,
			init			= function() {
				
				// (not necessary) preloading the images here...
				jQueryitems.add('<img src="images/ajax-loader.gif"/><img src="images/black.png"/>').imagesLoaded( function() {
					// add options
					_addViewModes();
					
					// add large image wrapper
					_addImageWrapper();
					
					// show first image
					_showImage( jQueryitems.eq( current ) );
						
				});
				
				// initialize the carousel
				if( mode === 'carousel' )
					_initCarousel();
				
			},
			_initCarousel	= function() {
				
				// we are using the elastislide plugin:
				// http://tympanus.net/codrops/2011/09/12/elastislide-responsive-carousel/
				jQueryesCarousel.show().elastislide({
					imageW 	: 80,
					onClick	: function( jQueryitem ) {
						if( anim ) return false;
						anim	= true;
						// on click show image
						_showImage(jQueryitem);
						// change current
						current	= jQueryitem.index();
					}
				});
				
				// set elastislide's current to current
				jQueryesCarousel.elastislide( 'setCurrent', current );
				
			},
			_addViewModes	= function() {
				
				// top right buttons: hide / show carousel
				
				var jQueryviewfull	= jQuery('<a href="#" class="rg-view-full"></a>'),
					jQueryviewthumbs	= jQuery('<a href="#" class="rg-view-thumbs rg-view-selected"></a>');
				
				jQueryrgGallery.prepend( jQuery('<div class="rg-view"/>').append( jQueryviewfull ).append( jQueryviewthumbs ) );
				
				jQueryviewfull.on('click.rgGallery', function( event ) {
						if( mode === 'carousel' )
							jQueryesCarousel.elastislide( 'destroy' );
						jQueryesCarousel.hide();
					jQueryviewfull.addClass('rg-view-selected');
					jQueryviewthumbs.removeClass('rg-view-selected');
					mode	= 'fullview';
					return false;
				});
				
				jQueryviewthumbs.on('click.rgGallery', function( event ) {
					_initCarousel();
					jQueryviewthumbs.addClass('rg-view-selected');
					jQueryviewfull.removeClass('rg-view-selected');
					mode	= 'carousel';
					return false;
				});
				
				if( mode === 'fullview' )
					jQueryviewfull.trigger('click');
					
			},
			_addImageWrapper= function() {
				
				// adds the structure for the large image and the navigation buttons (if total items > 1)
				// also initializes the navigation events
				
				jQuery('#img-wrapper-tmpl').tmpl( {itemsCount : itemsCount} ).prependTo( jQueryrgGallery );
				
				if( itemsCount > 1 ) {
					// addNavigation
					var jQuerynavPrev		= jQueryrgGallery.find('a.rg-image-nav-prev'),
						jQuerynavNext		= jQueryrgGallery.find('a.rg-image-nav-next'),
						jQueryimgWrapper		= jQueryrgGallery.find('div.rg-image');
						
					jQuerynavPrev.on('click.rgGallery', function( event ) {
						_navigate( 'left' );
						return false;
					});	
					
					jQuerynavNext.on('click.rgGallery', function( event ) {
						_navigate( 'right' );
						return false;
					});
				
					// add touchwipe events on the large image wrapper
					jQueryimgWrapper.touchwipe({
						wipeLeft			: function() {
							_navigate( 'right' );
						},
						wipeRight			: function() {
							_navigate( 'left' );
						},
						preventDefaultEvents: false
					});
				
					jQuery(document).on('keyup.rgGallery', function( event ) {
						if (event.keyCode == 39)
							_navigate( 'right' );
						else if (event.keyCode == 37)
							_navigate( 'left' );	
					});
					
				}
				
			},
			_navigate		= function( dir ) {
				
				// navigate through the large images
				
				if( anim ) return false;
				anim	= true;
				
				if( dir === 'right' ) {
					if( current + 1 >= itemsCount )
						current = 0;
					else
						++current;
				}
				else if( dir === 'left' ) {
					if( current - 1 < 0 )
						current = itemsCount - 1;
					else
						--current;
				}
				
				_showImage( jQueryitems.eq( current ) );
				
			},
			_showImage		= function( jQueryitem ) {
				
				// shows the large image that is associated to the jQueryitem
				
				var jQueryloader	= jQueryrgGallery.find('div.rg-loading').show();
				
				jQueryitems.removeClass('selected');
				jQueryitem.addClass('selected');
					 
				var jQuerythumb		= jQueryitem.find('img'),
					largesrc	= jQuerythumb.data('large'),
					title		= jQuerythumb.data('description');
				
				jQuery('<img/>').load( function() {
					
					jQueryrgGallery.find('div.rg-image').empty().append('<img src="' + largesrc + '"/>');
					
					if( title )
						jQueryrgGallery.find('div.rg-caption').show().children('p').empty().text( title );
					
					jQueryloader.hide();
					
					if( mode === 'carousel' ) {
						jQueryesCarousel.elastislide( 'reload' );
						jQueryesCarousel.elastislide( 'setCurrent', current );
					}
					
					anim	= false;
					
				}).attr( 'src', largesrc );
				
			},
			addItems		= function( jQuerynew ) {
			
				jQueryesCarousel.find('ul').append(jQuerynew);
				jQueryitems 		= jQueryitems.add( jQuery(jQuerynew) );
				itemsCount	= jQueryitems.length; 
				jQueryesCarousel.elastislide( 'add', jQuerynew );
			
			};
		
		return { 
			init 		: init,
			addItems	: addItems
		};
	
	})();

	Gallery.init();
	

}
