/**
 * @package    JomWALL 
 * @subpackage  mod_awdwallmembers
 * @link http://www.AWDsolution.com
 * @license        GNU/GPL http://www.gnu.org/copyleft/gpl.html
 */

;( function( $, window, undefined ) {
	
	'use strict';

	// http://www.hardcode.nl/subcategory_1/article_317-array-shuffle-function
	Array.prototype.shuffle = function() {
		var i=this.length,p,t;
		while (i--) {
			p = Math.floor(Math.random()*i);
			t = this[i];
			this[i]=this[p];
			this[p]=t;
		}
		return this;
	};

	var $event = $.event,
	$special,
	resizeTimeout;


	// global
	var $window				= $( window ),
		Modernizr			= window.Modernizr;

	$.GridRotator			= function( options, element ) {
		
		this.$el	= $( element );
		if( Modernizr.backgroundsize ) {

		this._init( options );

		}
		
	};

	$.GridRotator.defaults	= {
		rows			: 4,
		columns			: 10,
		w1024			: {
			rows	: 3,
			columns	: 8
		},
		w768			: {
			rows	: 3,
			columns	: 7
		},
		w480			: {
			rows	: 3,
			columns	: 5
		},
		w320			: {
			rows	: 2,
			columns	: 4
		},
		w240			: {
			rows	: 2,
			columns	: 3
		},
		step			: 'random',
		maxStep			: 3,
		preventClick	: false,
		animType		: 'random',
		animSpeed		: 500,
		animEasingOut	: 'linear',
		animEasingIn	: 'linear',
		interval		: 3000
	};

	$.GridRotator.prototype	= {

		_init				: function( options ) {

			var _self			= this;
			
			this.options		= $.extend( true, {}, $.GridRotator.defaults, options );

			this.animTypesAll	= [ 'fadeInOut', 'slideLeft', 'slideRight', 'slideTop', 'slideBottom', 'rotateLeft', 'rotateRight', 'rotateTop', 'rotateBottom', 'scale', 'rotate3d', 'rotateLeftScale', 'rotateRightScale', 'rotateTopScale', 'rotateBottomScale' ];
			this.animTypesCond	= [ 'fadeInOut', 'slideLeft', 'slideRight', 'slideTop', 'slideBottom' ];

			this.animTypes		= this.animTypesCond;
			if( Modernizr.csstransforms3d ) {

				this.animTypes = this.animTypesAll;

			}

			this.animType		= this.options.animType;
			if( this.animType !== 'random' ) {

				if( !Modernizr.csstransforms3d && $.inArray( this.animType, this.animTypesCond ) === -1 && this.animType !== 'showHide' ) {
					this.animType = 'fadeInOut';

				}

			}
			this.animTypesTotal	= this.animTypes.length;

			this.$list			= this.$el.children( 'ul' );
			var loaded			= 0,
				$imgs			= this.$list.find( 'img' ),
				count			= $imgs.length;

			this.$el.addClass( 'ri-grid-loading' );

			$imgs.each( function() {

				var $img	= $( this ),
					src		= $img.attr( 'src' );

				$( '<img/>' ).load( function() {

					++loaded;
					$img.parent().css( 'background-image', 'url(' + src + ')' );
					if( loaded === count ) {

						$imgs.remove();
				 
						_self.$el.removeClass( 'ri-grid-loading' );
						_self.$items		= _self.$list.children( 'li' );
						_self.$itemsCache	= _self.$items.clone();
						_self.itemsTotal	= _self.$items.length;
						_self.outItems		= [];

						_self._layout();
						_self._initEvents();
						_self._start();

					}

				} ).attr( 'src', src )
				 
			} );

		},
		_layout				: function( callback ) {

			var _self		= this;

			this._setGridDim();
			this.$list.empty();
			this.$items		= this.$itemsCache.clone().appendTo( this.$list );
			
			var $outItems	= this.$items.filter( ':gt(' + ( this.showTotal - 1 ) + ')' ),
				$outAItems	= $outItems.children( 'a' );

			this.outItems.length = 0;

			$outAItems.each( function( i ) {

				_self.outItems.push( $( this ) );

			} );

			$outItems.remove();

			var containerWidth	= ( document.defaultView ) ? parseInt( document.defaultView.getComputedStyle( this.$el.get( 0 ), null ).width ) : this.$el.width(),
				itemWidth		= Math.floor( containerWidth / this.columns ),
				gapWidth		= containerWidth - ( this.columns * Math.floor( itemWidth ) );

			for( var i = 0; i < this.rows; ++i ) {

				for( var j = 0; j < this.columns; ++j ) {

					var $item	= this.$items.eq( this.columns * i + j ),
						h		= itemWidth,
						w		= ( j < Math.floor( gapWidth ) ) ? itemWidth + 1 : itemWidth;

					$item.css( {
						width	: 51,
						height	: 51
					} );
				}

			}

			if( this.options.preventClick ) {
				this.$items.children().css( 'cursor', 'default' ).on( 'click.gridrotator', false );
			}

			if( callback ) {

				callback.call();

			}

		},
		_setGridDim			: function() {
			var c_w			= this.$el.width();

			switch( true ) {
				default				: this.rows = this.options.rows; this.columns = this.options.columns; break;

			}

			this.showTotal	= this.rows * this.columns;

		},
		_initEvents			: function() {

			var _self = this;

			$window.on( 'debouncedresize.gridrotator', function( event ) {

				clearTimeout( _self.playtimeout );

				_self._layout( function() {

					_self._start();

				} );
				
			} );

		},
		_start				: function() {

			if( this.showTotal < this.itemsTotal ) {

				this._showNext();

			}

		},
		_getAnimType		: function() {
			if( this.animType === 'random' ) {
				return this.animTypes[ Math.floor( Math.random() * this.animTypesTotal ) ];
			}
			else {
				return this.animType;
			}
		},
		_getAnimProperties 	: function( $in, $out ) {

			var startInProp		= {},
				startOutProp	= {},
				endInProp		= {},
				endOutProp		= {},

				animType		= this._getAnimType(),
				speed;

			switch( animType ) {

				case 'showHide'	:
					speed = 0;
					
					endOutProp.opacity	= 0;
					
					break;
				case 'fadeInOut'	:
					endOutProp.opacity	= 0;
					
					break;
				case 'slideLeft'	:
					startInProp.left 	= $out.width();
					
					endInProp.left		= 0;
					endOutProp.left		= -$out.width();
					
					break;
				case 'slideRight'	:
					startInProp.left 	= -$out.width();
					
					endInProp.left		= 0;
					endOutProp.left		= $out.width();
					
					break;


			}

			var animSpeed = ( speed != undefined ) ? speed : this.options.animSpeed;

			return {
				startInProp		: startInProp,
				startOutProp	: startOutProp,
				endInProp		: endInProp,
				endOutProp		: endOutProp,
				animSpeed		: animSpeed
			};

		},
		_showNext			: function( t ) {

			var _self = this;

			clearTimeout( this.playtimeout );

			this.playtimeout = setTimeout( function() {

				var step		= _self.options.step,
					max			= _self.options.maxStep,
					min			= 1;
				
				if( max > _self.showTotal ) {

					max = _self.showTotal;

				}

				var $items	= _self.$items, 
					outs	= [],
					nmbOut	= ( step === 'random' ) ? Math.floor( Math.random() * max + min ) : Math.min( Math.abs( step ) , max ) ,
					randArr	= _self._getRandom( nmbOut, _self.showTotal );
				
				for( var i = 0; i < nmbOut; ++i ) {
					var $out = $items.eq( randArr[ i ] );
					if( $out.data( 'active' ) ) {
						_self._showNext( 1 );
						return false;

					}
					outs.push( $out );

				}

				for( var i = 0; i < nmbOut; ++i ) {

					var $out		= outs[ i ],
						$outA		= $out.children( 'a:last' ),
						newElProp	= {
							width	: $outA.width(),
							height	: $outA.height()
						};

					$out.data( 'active', true );

					var $inA		= _self.outItems.shift();
					_self.outItems.push( $outA.clone() );
					
					$inA.css( newElProp ).prependTo( $out );

					var animProp	= _self._getAnimProperties( $inA, $outA );
					
					if( Modernizr.csstransitions ) {

						$inA.css( animProp.startInProp ).transition( animProp.endInProp, animProp.animSpeed, _self.options.animEasingIn );
						$outA.css( animProp.startOutProp ).transition( animProp.endOutProp, animProp.animSpeed, _self.options.animEasingOut, function() {

							$( this ).parent().data( 'active', false ).end().remove();

						} );
					
					}
					else {

						$inA.css( animProp.startInProp ).stop().animate( animProp.endInProp, animProp.animSpeed );
						$outA.css( animProp.startOutProp ).stop().animate( animProp.endOutProp, animProp.animSpeed, function() {

							$( this ).parent().data( 'active', false ).end().remove();

						} )

					}

				}

				_self._showNext();

			}, t || Math.max( Math.abs( this.options.interval ) , 300 ) );

		},
		_getRandom			: function( cnt, limit ) {

			var randArray = [];

			for( var i = 0; i < limit; ++i ) {

				randArray.push( i )

			}
			
			return randArray.shuffle().slice(0,cnt); 

		}

	};
	
	var logError		= function( message ) {

		if ( window.console ) {

			window.console.error( message );
		
		}

	};
	
	$.fn.gridrotator	= function( options ) {
		
		if ( typeof options === 'string' ) {
			
			var args = Array.prototype.slice.call( arguments, 1 );
			
			this.each(function() {
			
				var instance = $.data( this, 'gridrotator' );
				
				if ( !instance ) {

					logError( "cannot call methods on gridrotator prior to initialization; " +
					"attempted to call method '" + options + "'" );
					return;
				
				}
				
				if ( !$.isFunction( instance[options] ) || options.charAt(0) === "_" ) {

					logError( "no such method '" + options + "' for gridrotator instance" );
					return;
				
				}
				
				instance[ options ].apply( instance, args );
			
			});
		
		} 
		else {
		
			this.each(function() {
				
				var instance = $.data( this, 'gridrotator' );
				
				if ( instance ) {

					instance._init();
				
				}
				else {

					$.data( this, 'gridrotator', new $.GridRotator( options, this ) );
				
				}

			});
		
		}
		
		return this;
		
	};
	
} )( jQuery, window );