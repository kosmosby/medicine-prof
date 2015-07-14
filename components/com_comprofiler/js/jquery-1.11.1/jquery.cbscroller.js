(function($) {
	var instances = [];
	var methods = {
		init: function( options ) {
			return this.each( function () {
				var $this = this;
				var cbscroller = $( $this ).data( 'cbscroller' );

				if ( cbscroller ) {
					return; // cbscroller is already bound; so no need to rebind below
				}

				cbscroller = {};
				cbscroller.options = options;
				cbscroller.defaults = $.fn.cbscroller.defaults;
				cbscroller.settings = $.extend( true, {}, cbscroller.defaults, cbscroller.options );
				cbscroller.element = $( $this );

				if ( cbscroller.settings.useData ) {
					$.each( cbscroller.defaults, function( key, value ) {
						if ( ( key != 'init' ) && ( key != 'useData' ) ) {
							// Dash Separated:
							var dataValue = cbscroller.element.data( 'cbscroller' + key.charAt( 0 ).toUpperCase() + key.slice( 1 ) );

							if ( typeof dataValue != 'undefined' ) {
								cbscroller.settings[key] = dataValue;
							} else {
								// No Separater:
								dataValue = cbscroller.element.data( 'cbscroller' + key.charAt( 0 ).toUpperCase() + key.slice( 1 ).toLowerCase() );

								if ( typeof dataValue != 'undefined' ) {
									cbscroller.settings[key] = dataValue;
								}
							}
						}
					});
				}

				cbscroller.element.triggerHandler( 'cbscroller.init.before', [cbscroller] );

				if ( ! cbscroller.settings.init ) {
					return;
				}

				cbscroller.resizeHandler = function() {
					updateScroller.call( $this, cbscroller );
				};

				$( window ).on( 'resize', cbscroller.resizeHandler );

				if ( cbscroller.settings.width ) {
					cbscroller.leftHandler = function( e ) {
						e.preventDefault();

						scrollToTarget.call( $this, 'left', cbscroller );
					};

					cbscroller.rightHandler = function( e ) {
						e.preventDefault();

						scrollToTarget.call( $this, 'right', cbscroller );
					};

					cbscroller.element.find( '.cbScrollerLeft:first' ).on( 'click', cbscroller.leftHandler );
					cbscroller.element.find( '.cbScrollerRight:first' ).on( 'click', cbscroller.rightHandler );
				}

				if ( cbscroller.settings.height ) {
					cbscroller.upHandler = function( e ) {
						e.preventDefault();

						scrollToTarget.call( $this, 'up', cbscroller );
					};

					cbscroller.downHandler = function( e ) {
						e.preventDefault();

						scrollToTarget.call( $this, 'down', cbscroller );
					};

					cbscroller.element.find( '.cbScrollerUp:first' ).on( 'click', cbscroller.upHandler );
					cbscroller.element.find( '.cbScrollerDown:first' ).on( 'click', cbscroller.downHandler );
				}

				updateScroller.call( $this, cbscroller );

				// If the cbscroller is cloned we need to rebind it back:
				cbscroller.element.on( 'cloned.cbscroller', function( e, oldId ) {
					$( this ).off( 'cloned.cbscroller' );
					$( this ).removeData( 'cbscroller' );

					if ( cbscroller.settings.width ) {
						$( this ).find( '.cbScrollerLeft:first' ).off( 'click', cbscroller.leftHandler ).addClass( 'hidden' );
						$( this ).find( '.cbScrollerRight:first' ).off( 'click', cbscroller.rightHandler ).addClass( 'hidden' );
					}

					if ( cbscroller.settings.height ) {
						$( this ).find( '.cbScrollerUp:first' ).off( 'click', cbscroller.upHandler ).addClass( 'hidden' );
						$( this ).find( '.cbScrollerDown:first' ).off( 'click', cbscroller.downHandler ).addClass( 'hidden' );
					}

					$( this ).removeClass( 'cbScrollerWidth cbScrollerHeight' );

					$( this ).cbscroller( cbscroller.options );
				});

				cbscroller.element.triggerHandler( 'cbscroller.init.after', [cbscroller] );

				// Bind the cbscroller to the element so it's reusable and chainable:
				cbscroller.element.data( 'cbscroller', cbscroller );

				// Add this instance to our instance array so we can keep track of our cbscroller instances:
				instances.push( cbscroller );
			});
		},
		update: function() {
			var cbscroller = $( this ).data( 'cbscroller' );

			if ( ! cbscroller ) {
				return false;
			}

			updateScroller.call( this, cbscroller );

			return true;
		},
		left: function() {
			var cbscroller = $( this ).data( 'cbscroller' );

			if ( ( ! cbscroller ) || ( ! cbscroller.settings.width ) ) {
				return false;
			}

			scrollToTarget.call( this, 'left', cbscroller );

			return true;
		},
		right: function() {
			var cbscroller = $( this ).data( 'cbscroller' );

			if ( ( ! cbscroller ) || ( ! cbscroller.settings.width ) ) {
				return false;
			}

			scrollToTarget.call( this, 'right', cbscroller );

			return true;
		},
		up: function() {
			var cbscroller = $( this ).data( 'cbscroller' );

			if ( ( ! cbscroller ) || ( ! cbscroller.settings.height ) ) {
				return false;
			}

			scrollToTarget.call( this, 'up', cbscroller );

			return true;
		},
		down: function() {
			var cbscroller = $( this ).data( 'cbscroller' );

			if ( ( ! cbscroller ) || ( ! cbscroller.settings.height ) ) {
				return false;
			}

			scrollToTarget.call( this, 'down', cbscroller );

			return true;
		},
		destroy: function() {
			var cbscroller = $( this ).data( 'cbscroller' );

			if ( ! cbscroller ) {
				return false;
			}

			cbscroller.element.off( 'cloned.cbscroller' );

			$( window ).off( 'resize', cbscroller.resizeHandler );

			if ( cbscroller.settings.width ) {
				cbscroller.element.find( '.cbScrollerLeft:first' ).off( 'click', cbscroller.leftHandler ).addClass( 'hidden' );
				cbscroller.element.find( '.cbScrollerRight:first' ).off( 'click', cbscroller.rightHandler ).addClass( 'hidden' );
			}

			if ( cbscroller.settings.height ) {
				cbscroller.element.find( '.cbScrollerUp:first' ).off( 'click', cbscroller.upHandler ).addClass( 'hidden' );
				cbscroller.element.find( '.cbScrollerDown:first' ).off( 'click', cbscroller.downHandler ).addClass( 'hidden' );
			}

			cbscroller.element.removeClass( 'cbScrollerWidth cbScrollerHeight' );
			cbscroller.element.removeData( 'cbscroller' );
			cbscroller.element.triggerHandler( 'cbscroller.destroyed', [cbscroller] );

			return true;
		},
		instances: function() {
			return instances;
		}
	};

	function updateScroller( cbscroller ) {
		cbscroller.element.removeClass( 'cbScrollerWidth cbScrollerHeight' );

		var content = cbscroller.element.find( '.cbScrollerContent:first' );

		var width = 0;
		var height = 0;

		var items = content.children();

		if ( cbscroller.settings.elements ) {
			items = items.find( cbscroller.settings.elements );
		}

		if ( cbscroller.settings.ignore ) {
			items = items.not( cbscroller.settings.ignore );
		}

		items.each( function() {
			width += $( this ).width();
			height += $( this ).height();
		});

		if ( cbscroller.settings.width ) {
			var left = cbscroller.element.find( '.cbScrollerLeft:first' ).addClass( 'hidden' );
			var right = cbscroller.element.find( '.cbScrollerRight:first' ).addClass( 'hidden' );

			if ( width > content.width() ) {
				cbscroller.element.addClass( 'cbScrollerWidth' );
				left.removeClass( 'hidden' );
				right.removeClass( 'hidden' );
			}
		}

		if ( cbscroller.settings.height ) {
			var up = cbscroller.element.find( '.cbScrollerUp:first' ).addClass( 'hidden' );
			var down = cbscroller.element.find( '.cbScrollerDown:first' ).addClass( 'hidden' );

			if ( height > content.height() ) {
				cbscroller.element.addClass( 'cbScrollerHeight' );
				up.removeClass( 'hidden' );
				down.removeClass( 'hidden' );
			}
		}
	}

	function scrollToTarget( direction, cbscroller ) {
		if ( ( ( direction == 'left' ) || ( direction == 'right' ) ) && ( ! cbscroller.settings.width ) ) {
			return;
		} else if ( ( ( direction == 'up' ) || ( direction == 'down' ) ) && ( ! cbscroller.settings.height ) ) {
			return;
		}

		var content = cbscroller.element.find( '.cbScrollerContent:first' );

		var width = 0;
		var height = 0;
		var target = null;

		var items = content.children();

		if ( cbscroller.settings.elements ) {
			items = items.find( cbscroller.settings.elements );
		}

		if ( cbscroller.settings.ignore ) {
			items = items.not( cbscroller.settings.ignore );
		}

		items.each( function() {
			width += $( this ).width();
			height += $( this ).height();
		});

		items.each( function() {
			switch ( direction ) {
				case 'left':
					if ( $( this ).offset().left < content.offset().left ) {
						target = $( this );
					}
					break;
				case 'right':
					if ( ( $( this ).offset().left + $( this ).width() ) > ( content.offset().left + content.width() ) ) {
						target = $( this );
						return false;
					}
					break;
				case 'up':
					if ( $( this ).offset().top < content.offset().top ) {
						target = $( this );
					}
					break;
				case 'down':
					if ( ( $( this ).offset().top + $( this ).height() ) > ( content.offset().top + content.height() ) ) {
						target = $( this );
						return false;
					}
					break;
			}
		});

		if ( ( direction == 'left' ) || ( direction == 'right' ) ) {
			if ( target ) {
				content.scrollTo( target, 'fast', { axis: 'x', easing: 'linear' } );
			} else {
				if ( cbscroller.settings.loop ) {
					var loopWidth = width;

					if ( direction == 'right' ) {
						loopWidth = 0;
					}

					content.scrollTo( loopWidth, 0, { axis: 'x', easing: 'linear' } );
				} else {
					var scrollWidth = 0;

					if ( direction == 'right' ) {
						scrollWidth = width;
					}

					content.scrollTo( scrollWidth, 'fast', { axis: 'x', easing: 'linear' } );
				}
			}
		} else if ( ( direction == 'up' ) || ( direction == 'down' ) ) {
			if ( target ) {
				content.scrollTo( target, 'fast', { axis: 'y', easing: 'linear' } );
			} else {
				if ( cbscroller.settings.loop ) {
					var loopHeight = height;

					if ( direction == 'down' ) {
						loopHeight = 0;
					}

					content.scrollTo( loopHeight, 0, { axis: 'x', easing: 'linear' } );
				} else {
					var scrollHeight = 0;

					if ( direction == 'down' ) {
						scrollHeight = height;
					}

					content.scrollTo( scrollHeight, 'fast', { axis: 'y', easing: 'linear' } );
				}
			}
		}
	}

	$.fn.cbscroller = function( options ) {
		if ( methods[options] ) {
			return methods[ options ].apply( this, Array.prototype.slice.call( arguments, 1 ) );
		} else if ( ( typeof options === 'object' ) || ( ! options ) ) {
			return methods.init.apply( this, arguments );
		}

		return this;
	};

	$.fn.cbscroller.defaults = {
		init: true,
		useData: true,
		elements: null,
		ignore: null,
		width: true,
		height: true,
		loop: false
	};
})(jQuery);