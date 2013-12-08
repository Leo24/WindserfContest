/*! scrollVert transition plugin for Cycle2; version: 20121120 */
(function($) {
"use strict";

$.fn.cycle.transitions.scrollVert = {
    before: function( opts, curr, next, fwd ) {
        opts.API.stackSlides( opts, curr, next, fwd );
        var height = opts.container.css('overflow','hidden').height();
        opts.cssBefore = { top: fwd ? -height : height, left: 0, opacity: 1, display: 'block' };
        opts.animIn = { top: 0 };
        opts.animOut = { top: fwd ? height : -height };
    }
};

})(jQuery);


/*! tile transition plugin for Cycle2; version: 20121120 */
(function ($) {
"use strict";

$.fn.cycle.transitions.tileSlide =
$.fn.cycle.transitions.tileBlind = {

    before: function( opts, curr, next, fwd ) {
        opts.API.stackSlides( curr, next, fwd );
        $(curr).show();
        opts.container.css('overflow', 'hidden');
        // set defaults
        opts.tileDelay = opts.tileDelay || opts.fx == 'tileSlide' ? 100 : 125;
        opts.tileCount = opts.tileCount || 7;
        opts.tileVertical = opts.tileVertical !== false;

        if (!opts.container.data('cycleTileInitialized')) {
            opts.container.on('cycle-destroyed', $.proxy(this.onDestroy, opts.API));
            opts.container.data('cycleTileInitialized', true);
        }
    },

    transition: function( opts, curr, next, fwd, callback ) {
        opts.slides.not(curr).not(next).hide();

        var tiles = $();
        var $curr = $(curr), $next = $(next);
        var tile, tileWidth, tileHeight, lastTileWidth, lastTileHeight,
            num = opts.tileCount,
            vert = opts.tileVertical,
            height = opts.container.height(),
            width = opts.container.width();

        if ( vert ) {
            tileWidth = Math.floor(width / num);
            lastTileWidth = width - (tileWidth * (num - 1));
            tileHeight = lastTileHeight = height;
        }
        else {
            tileWidth = lastTileWidth = width;
            tileHeight = Math.floor(height / num);
            lastTileHeight = height - (tileHeight * (num - 1));
        }

        // opts.speed = opts.speed / 2;
        opts.container.find('.cycle-tiles-container').remove();

        var animCSS;
        var tileCSS = { left: 0, top: 0, overflow: 'hidden', position: 'absolute', margin: 0, padding: 0 };
        if ( vert ) {
            animCSS = opts.fx == 'tileSlide' ? { top: height } : { width: 0 };
        }
        else {
            animCSS = opts.fx == 'tileSlide' ? { left: width } : { height: 0 };
        }

        var tilesContainer = $('<div class="cycle-tiles-container"></div>');
        tilesContainer.css({
            zIndex: $curr.css('z-index'),
            overflow: 'visible',
            position: 'absolute',
            top: 0
        });
        tilesContainer.insertBefore( next );

        for (var i = 0; i < num; i++) {
            tile = $('<div></div>')
            .css( tileCSS )
            .css({
                width: ((num - 1 === i) ? lastTileWidth : tileWidth),
                height: ((num - 1 === i) ? lastTileHeight : tileHeight),
                marginLeft: vert ? ((i * tileWidth)) : 0,
                marginTop: vert ? 0 : (i * tileHeight)
            })
            .append($curr.clone().css({
                position: 'relative',
                maxWidth: 'none',
                width: $curr.width(),
                margin: 0, padding: 0,
                marginLeft: vert ? -(i * tileWidth) : 0,
                marginTop: vert ? 0 : -(i * tileHeight)
            }));
            tiles = tiles.add(tile);
        }

        tilesContainer.append(tiles);
        $curr.hide();
        $next.show().css( 'opacity', 1 );
        animateTile(fwd ? 0 : num - 1);
        
        opts._tileAniCallback = function() {
            $next.show();
            $curr.hide();
            tilesContainer.remove();
            callback();
        };

        function animateTile(i) {
            tiles.eq(i).animate( animCSS, {
                duration: opts.speed,
                easing: opts.easing,
                complete: function () {
                    if (fwd ? (num - 1 === i) : (0 === i)) {
                        opts._tileAniCallback();
                    }
                }
            });

            setTimeout(function () {
                if (fwd ? (num - 1 !== i) : (0 !== i)) {
                    animateTile(fwd ? (i + 1) : (i - 1));
                }
            }, opts.tileDelay);
        }
    },

    // tx API impl
    stopTransition: function( opts ) {
        opts.container.find('*').stop( true, true );
        if (opts._tileAniCallback)
            opts._tileAniCallback();
    },

    // core API supplement
    onDestroy: function( e ) {
        var opts = this.opts();
        opts.container.find('.cycle-tiles-container').remove();
    }
};

})(jQuery);

/*! shuffle transition plugin for Cycle2;  version: 20121120 */
(function($) {
"use strict";

$.fn.cycle.transitions.shuffle = {

    transition: function( opts, currEl, nextEl, fwd, callback ) {
        $( nextEl ).show();
        var width = opts.container.css( 'overflow', 'visible' ).width();
        var speed = opts.speed / 2; // shuffle has 2 transitions
        var element = fwd ? currEl : nextEl;

        opts = opts.API.getSlideOpts( fwd ? opts.currSlide : opts.nextSlide );
        var props1 = { left:-width, top:15 };
        var props2 =  opts.slideCss || { left:0, top:0 };

        if ( opts.shuffleLeft !== undefined ) {
            props1.left = props1.left + parseInt(opts.shuffleLeft, 10) || 0;
        } 
        else if ( opts.shuffleRight !== undefined ) {
            props1.left = width + parseInt(opts.shuffleRight, 10) || 0;
        } 
        if ( opts.shuffleTop ) {
            props1.top = opts.shuffleTop;
        }

        // transition slide in 3 steps: move, re-zindex, move
        $( element )
            .animate( props1, speed, opts.easeIn || opts.easing )
            .queue( 'fx', $.proxy(reIndex, this))
            .animate( props2, speed, opts.easeOut || opts.easing, callback );

        function reIndex(nextFn) {
            /*jshint validthis:true */
            this.stack(opts, currEl, nextEl, fwd);
            nextFn();
        }
    },

    stack: function( opts, currEl, nextEl, fwd ) {
        var i, z;

        if (fwd) {
            opts.API.stackSlides( nextEl, currEl, fwd );
            // force curr slide to bottom of the stack
            $(currEl).css( 'zIndex', 1 );
        }
        else {
            z = 1;
            for (i = opts.nextSlide - 1; i >= 0; i--) {
                $(opts.slides[i]).css('zIndex', z++);
            }
            for (i = opts.slideCount - 1; i > opts.nextSlide; i--) {
                $(opts.slides[i]).css('zIndex', z++);
            }
            $(nextEl).css( 'zIndex', opts.maxZ );
            $(currEl).css( 'zIndex', opts.maxZ - 1 );
        }
    }
};

})(jQuery);

/*! progressive loader plugin for Cycle2; version: 20130315 */
(function($) {
"use strict";

$.extend($.fn.cycle.defaults, {
    progressive: false
});

$(document).on( 'cycle-pre-initialize', function( e, opts ) {
    if ( !opts.progressive )
        return;

    var API = opts.API;
    var nextFn = API.next;
    var prevFn = API.prev;
    var prepareTxFn = API.prepareTx;
    var type = $.type( opts.progressive );
    var slides, scriptEl;

    if ( type == 'array' ) {
        slides = opts.progressive;
    }
    else if ($.isFunction( opts.progressive ) ) {
        slides = opts.progressive( opts );
    }
    else if ( type == 'string' ) {
        scriptEl = $( opts.progressive );
        slides = $.trim( scriptEl.html() );
        if ( !slides )
            return;
        // is it json array?
        if ( /^(\[)/.test( slides ) ) {
            try {
                slides = $.parseJSON( slides );
            }
            catch(err) {
                API.log( 'error parsing progressive slides', err );
                return;
            }
        }
        else {
            // plain text, split on delimeter
            slides = slides.split( new RegExp( scriptEl.data('cycle-split') || '\n') );
            
            // #95; look for empty slide
            if ( ! slides[ slides.length - 1 ] )
                slides.pop();
        }
    }



    if ( prepareTxFn ) {
        API.prepareTx = function( manual, fwd ) {
            var index, slide;

            if ( manual || slides.length === 0 ) {
                prepareTxFn.apply( opts.API, [ manual, fwd ] );
                return;
            }

            if ( fwd && opts.currSlide == ( opts.slideCount-1) ) {
                slide = slides[ 0 ];
                slides = slides.slice( 1 );
                opts.container.one('cycle-slide-added', function(e, opts ) {
                    setTimeout(function() {
                        opts.API.advanceSlide( 1 );
                    },50);
                });
                opts.API.add( slide );
            }
            else if ( !fwd && opts.currSlide === 0 ) {
                index = slides.length-1;
                slide = slides[ index ];
                slides = slides.slice( 0, index );
                opts.container.one('cycle-slide-added', function(e, opts ) {
                    setTimeout(function() {
                        opts.currSlide = 1;
                        opts.API.advanceSlide( -1 );
                    },50);
                });
                opts.API.add( slide, true );
            }
            else {
                prepareTxFn.apply( opts.API, [ manual, fwd ] );
            }
        };
    }

    if ( nextFn ) {
        API.next = function() {
            var opts = this.opts();
            if ( slides.length && opts.currSlide == ( opts.slideCount - 1 ) ) {
                var slide = slides[ 0 ];
                slides = slides.slice( 1 );
                opts.container.one('cycle-slide-added', function(e, opts ) {
                    nextFn.apply( opts.API );
                    opts.container.removeClass('cycle-loading');
                });
                opts.container.addClass('cycle-loading');
                opts.API.add( slide );
            }
            else {
                nextFn.apply( opts.API );
            }
        };
    }
    
    if ( prevFn ) {
        API.prev = function() {
            var opts = this.opts();
            if ( slides.length && opts.currSlide === 0 ) {
                var index = slides.length-1;
                var slide = slides[ index ];
                slides = slides.slice( 0, index );
                opts.container.one('cycle-slide-added', function(e, opts ) {
                    opts.currSlide = 1;
                    opts.API.advanceSlide( -1 );
                    opts.container.removeClass('cycle-loading');
                });
                opts.container.addClass('cycle-loading');
                opts.API.add( slide, true );
            }
            else {
                prevFn.apply( opts.API );
            }
        };
    }
});

})(jQuery);

/*! swipe plugin for Cycle2; version: 20121120 */
(function($) {
"use strict";

// this script adds support for touch events. the logic is lifted from jQuery Mobile.
// if you have jQuery Mobile installed, you do NOT need this script

var supportTouch = 'ontouchend' in document;

$.event.special.swipe = $.event.special.swipe || {
    scrollSupressionThreshold: 10, // More than this horizontal displacement, and we will suppress scrolling.
    durationThreshold: 1000, // More time than this, and it isn't a swipe.
    horizontalDistanceThreshold: 30, // Swipe horizontal displacement must be more than this.
    verticalDistanceThreshold: 75, // Swipe vertical displacement must be less than this.

    setup: function() {
        var $this = $( this );

        $this.bind( 'touchstart', function( event ) {
            var data = event.originalEvent.touches ? event.originalEvent.touches[ 0 ] : event;
            var stop, start = {
                time: ( new Date() ).getTime(),
                coords: [ data.pageX, data.pageY ],
                origin: $( event.target )
            };

            function moveHandler( event ) {
                if ( !start )
                    return;

                var data = event.originalEvent.touches ? event.originalEvent.touches[ 0 ] : event;

                stop = {
                    time: ( new Date() ).getTime(),
                    coords: [ data.pageX, data.pageY ]
                };

                // prevent scrolling
                if ( Math.abs( start.coords[ 0 ] - stop.coords[ 0 ] ) > $.event.special.swipe.scrollSupressionThreshold ) {
                    event.preventDefault();
                }
            }

            $this.bind( 'touchmove', moveHandler )
                .one( 'touchend', function( event ) {
                    $this.unbind( 'touchmove', moveHandler );

                    if ( start && stop ) {
                        if ( stop.time - start.time < $.event.special.swipe.durationThreshold &&
                                Math.abs( start.coords[ 0 ] - stop.coords[ 0 ] ) > $.event.special.swipe.horizontalDistanceThreshold &&
                                Math.abs( start.coords[ 1 ] - stop.coords[ 1 ] ) < $.event.special.swipe.verticalDistanceThreshold ) {

                            start.origin.trigger( "swipe" )
                                .trigger( start.coords[0] > stop.coords[ 0 ] ? "swipeleft" : "swiperight" );
                        }
                    }
                    start = stop = undefined;
                });
        });
    }
};

$.event.special.swipeleft = $.event.special.swipeleft || {
    setup: function() {
        $( this ).bind( 'swipe', $.noop );
    }
};
$.event.special.swiperight = $.event.special.swiperight || $.event.special.swipeleft;

})(jQuery);

/*! caption plugin for Cycle2; version: 20130306 */
(function($) {
"use strict";

$.extend($.fn.cycle.defaults, {
    caption: '> .cycle-caption',
    captionTemplate: '{{slideNum}} / {{slideCount}}',
    overlay: '> .cycle-overlay',
    overlayTemplate: '<div>{{title}}</div><div>{{desc}}</div>',
    captionModule: 'caption'
});

$(document).on( 'cycle-update-view', function( e, opts, slideOpts, currSlide ) {
    if ( opts.captionModule !== 'caption' )
        return;
    var el;
    $.each(['caption','overlay'], function() {
        var name = this;
        var template = slideOpts[name+'Template'];
        var el = opts.API.getComponent( name );
        if( el.length && template ) {
            el.html( opts.API.tmpl( template, slideOpts, opts, currSlide ) );
            el.show();
        }
        else {
            el.hide();
        }
    });
});

$(document).on( 'cycle-destroyed', function( e, opts ) {
    var el;
    $.each(['caption','overlay'], function() {
        var name = this, template = opts[name+'Template'];
        if ( opts[name] && template ) {
            el = opts.API.getComponent( 'caption' );
            el.empty();
        }
    });
});

})(jQuery);

/*! caption2 plugin for Cycle2; version: 20130306 */
(function($) {
"use strict";

$.extend($.fn.cycle.defaults, {
    captionFxOut: 'fadeOut',
    captionFxIn: 'fadeIn',
    captionFxSel: undefined,
    overlayFxOut: 'fadeOut',
    overlayFxIn: 'fadeIn',
    overlayFxSel: undefined
});

$(document).on( 'cycle-bootstrap', function(e, opts) {
    opts.container.on( 'cycle-update-view-before', update );
    opts.container.one( 'cycle-update-view-after', init );
});

function update( e, opts, slideOpts, currSlide, isAfter ) {
	
    if ( opts.captionPlugin !== 'caption2' )
        return;
    $.each(['caption','overlay'], function() {
        var fxBase = this + 'Fx',
            fx = opts[fxBase + 'Out'] || 'hide',
            template = slideOpts[this+'Template'],
            el = opts.API.getComponent( this ),
            sel = opts[fxBase+'Sel'],
            speed = opts.speed,
            animEl;

        if ( opts.sync)
            speed = speed/2;

        animEl = sel ? el.find( sel ) : el;

        if( el.length && template ) {
            if ( fx == 'hide')
                speed = 0;
            animEl[fx]( speed, function() {
                var content = opts.API.tmpl( template, slideOpts, opts, currSlide );
                el.html( content );
                animEl = sel ? el.find( sel ) : el;
                if ( sel )
                    animEl.hide();
                fx = opts[ fxBase + 'In'] || 'show';
                animEl[fx]( speed );
            });
        }
        else {
            el.hide();
        }
    });
}

function init( e, opts, slideOpts, currSlide, isAfter ) {
    if ( opts.captionPlugin !== 'caption2' )
        return;
    $.each(['caption','overlay'], function() {
        var template = slideOpts[this+'Template'],
            el = opts.API.getComponent( this );

        if( el.length && template )
            el.html( opts.API.tmpl( template, slideOpts, opts, currSlide ) );
    });
}

})(jQuery);

/*! loader plugin for Cycle2;  version: 20130307 */
(function($) {
"use strict";

$.extend($.fn.cycle.defaults, {
    loader: false
});

$(document).on( 'cycle-bootstrap', function( e, opts ) {
    var addFn;

    if ( !opts.loader )
        return;

    // override API.add for this slideshow
    addFn = opts.API.add;
    opts.API.add = add;

    function add( slides, prepend ) {
        var slideArr = [];
        if ( $.type( slides ) == 'string' )
            slides = $.trim( slides );
        else if ( $.type( slides) === 'array' ) {
            for (var i=0; i < slides.length; i++ )
                slides[i] = $(slides[i])[0];
        }

        slides = $( slides );
        var slideCount = slides.length;

        if ( ! slideCount )
            return;

        slides.hide().appendTo('body').each(function(i) { // appendTo fixes #56
            var count = 0;
            var slide = $(this);
            var images = slide.is('img') ? slide : slide.find('img');
            slide.data('index', i);
            // allow some images to be marked as unimportant (and filter out images w/o src value)
            images = images.filter(':not(.cycle-loader-ignore)').filter(':not([src=""])');
            if ( ! images.length ) {
                --slideCount;
                slideArr.push( slide );
                return;
            }

            count = images.length;
            images.each(function() {
                // add images that are already loaded
                if ( this.complete ) {
                    imageLoaded();
                }
                else {
                    $(this).load(function() {
                        imageLoaded();
                    }).error(function() {
                        if ( --count === 0 ) {
                            // ignore this slide
                            opts.API.log('slide skipped; img not loaded:', this.src);
                            if ( --slideCount === 0 && opts.loader == 'wait') {
                                addFn.apply( opts.API, [ slideArr, prepend ] );
                            }
                        }
                    });
                }
            });

            function imageLoaded() {
                if ( --count === 0 ) {
                    --slideCount;
                    addSlide( slide );
                }
            }
        });

        if ( slideCount )
            opts.container.addClass('cycle-loading');
        

        function addSlide( slide ) {
            var curr;
            if ( opts.loader == 'wait' ) {
                slideArr.push( slide );
                if ( slideCount === 0 ) {
                    // #59; sort slides into original markup order
                    slideArr.sort( sorter );
                    addFn.apply( opts.API, [ slideArr, prepend ] );
                    opts.container.removeClass('cycle-loading');
                }
            }
            else {
                curr = $(opts.slides[opts.currSlide]);
                addFn.apply( opts.API, [ slide, prepend ] );
                curr.show();
                opts.container.removeClass('cycle-loading');
            }
        }

        function sorter(a, b) {
            return a.data('index') - b.data('index');
        }
    }
});

})(jQuery);

