(function($) {
	var instances = [];
	var methods = {
		init: function( options ) {
			return this.each( function () {
				var $this = this;
				var cbselect2 = $( $this ).data( 'cbselect2' );

				if ( cbselect2 ) {
					return; // cbselect2 is already bound; so no need to rebind below
				}

				cbselect2 = {};
				cbselect2.options = options;
				cbselect2.defaults = $.fn.cbselect2.defaults;
				cbselect2.settings = $.extend( true, {}, cbselect2.defaults, cbselect2.options );
				cbselect2.element = $( $this );

				if ( cbselect2.settings.useData ) {
					var dataOptions = $.extend( true, {}, $.fn.select2.defaults, $.fn.cbselect2.defaults );

					$.each( dataOptions, function( key, value ) {
						if ( ( key != 'init' ) && ( key != 'useData' ) ) {
							// Dash Separated:
							var dataValue = cbselect2.element.data( 'cbselect2' + key.charAt( 0 ).toUpperCase() + key.slice( 1 ) );

							if ( typeof dataValue != 'undefined' ) {
								cbselect2.settings[key] = dataValue;
							} else {
								// No Separater:
								dataValue = cbselect2.element.data( 'cbselect2' + key.charAt( 0 ).toUpperCase() + key.slice( 1 ).toLowerCase() );

								if ( typeof dataValue != 'undefined' ) {
									cbselect2.settings[key] = dataValue;
								}
							}
						}
					});
				}

				cbselect2.element.triggerHandler( 'cbselect2.init.before', [cbselect2] );

				if ( ! cbselect2.settings.init ) {
					return;
				}

				var tags = cbselect2.element.data( 'cbselect2-tags' );

				if ( typeof tags != 'undefined' ) {
					var separators = cbselect2.element.data( 'cbselect2-tags-separators' );

					if ( typeof separators == 'undefined' ) {
						separators = [','];
					}

					if ( cbselect2.settings.width == 'calculate' ) {
						cbselect2.settings.width = 'auto'
					}

					if ( cbselect2.settings.height == 'calculate' ) {
						cbselect2.settings.height = 'auto'
					}

					cbselect2.settings.tags = tags;
					cbselect2.settings.tokenSeparators = separators;

					if ( typeof cbselect2.settings.containerCssClass == 'undefined' ) {
						cbselect2.settings.containerCssClass = 'select2-container-tag';
					} else {
						cbselect2.settings.containerCssClass = cbselect2.settings.containerCssClass + ' select2-container-tag';
					}
				}

				var width = null;
				var height = null;

				if ( ( cbselect2.settings.width == 'calculate' ) || ( cbselect2.settings.height == 'calculate' ) ) {
					width = cbselect2.element.outerWidth();
					height = cbselect2.element.outerHeight();

					if ( cbselect2.element.is( ':hidden' ) ) {
						var cssWidth = cbselect2.element.css( 'width' );
						var cssHeight = cbselect2.element.css( 'height' );

						var temporary = cbselect2.element.clone( false ).attr({
							id: '',
							class: ''
						}).css({
							position: 'absolute',
							display: 'block',
							width: ( cssWidth && ( cssWidth != '0px' ) ? cssWidth : 'auto' ),
							height: ( cssHeight && ( cssHeight != '0px' ) ? cssHeight : 'auto' ),
							visibility: 'hidden',
							padding: cbselect2.element.css( 'padding' ),
							border: cbselect2.element.css( 'border' ),
							margin: cbselect2.element.css( 'margin' ),
							fontFamily: cbselect2.element.css( 'font-family' ),
							fontSize: cbselect2.element.css( 'font-size' ),
							fontWeight: cbselect2.element.css( 'font-weight' ),
							boxSizing: cbselect2.element.css( 'box-sizing' )
						}).appendTo( 'body' );

						width = temporary.outerWidth();
						height = temporary.outerHeight();

						temporary.remove();
					}

					if ( cbselect2.settings.width == 'calculate' ) {
						cbselect2.settings.width = ( width + 30 );
					}

					if ( cbselect2.settings.height == 'calculate' ) {
						cbselect2.settings.height = height;
					}
				}

				if ( cbselect2.settings.height == 'off' ) {
					height = null;
				}

				if ( cbselect2.settings.height == 'element' ) {
					height = cbselect2.element.outerHeight();

					if ( ( ! height ) || ( height == '0px' ) ) {
						height = 'auto';
					}
				}

				if ( ( cbselect2.settings.height == 'copy' ) || ( cbselect2.settings.height == 'resolve' ) ) {
					height = cbselect2.element.css( 'height' );

					if ( ( ! height ) || ( height == '0px' ) ) {
						height = 'auto';
					}
				}

				cbselect2.select2 = cbselect2.element.select2( cbselect2.settings );

				if ( height && ( height != '0px' ) ) {
					cbselect2.element.select2( 'container' ).css( 'height', height );
				}

				cbselect2.select2.on( 'change', function() {
					if ( typeof cbParamChange != 'undefined' ) {
						cbParamChange.call( $this );
					}
				});

				// If the cbselect2 element is modified we need to rebuild it to ensure all our bindings are still ok:
				cbselect2.element.on( 'modified.cbselect2', function( e, oldId, newId, index ) {
					if ( oldId != newId ) {
						cbselect2.element.cbselect2( 'destroy' );
						cbselect2.element.cbselect2( cbselect2.options );
					}
				});

				// If the select2 is cloned we need to rebind it back:
				cbselect2.element.on( 'cloned.cbselect2', function( e, oldId ) {
					$( this ).off( 'cloned.cbselect2' );
					$( this ).off( 'modified.cbselect2' );
					$( this ).select2( 'close' );
					$( this ).removeData( 'cbselect2' );
					$( this ).removeData( 'select2' );
					$( this ).off( '.select2' );
					$( this ).removeClass( 'select2-offscreen' );
					$( this ).siblings( '.select2-container' ).remove();
					$( this ).show();
					$( this ).cbselect2( cbselect2.options );
				});

				cbselect2.element.triggerHandler( 'cbselect2.init.after', [cbselect2] );

				// Bind the cbselect2 to the element so it's reusable and chainable:
				cbselect2.element.data( 'cbselect2', cbselect2 );

				// Add this instance to our instance array so we can keep track of our select2 instances:
				instances.push( cbselect2 );
			});
		},
		open: function() {
			var cbselect2 = $( this ).data( 'cbselect2' );

			if ( ! cbselect2 ) {
				return false;
			}

			cbselect2.element.select2( 'open' );

			return true;
		},
		close: function() {
			var cbselect2 = $( this ).data( 'cbselect2' );

			if ( ! cbselect2 ) {
				return false;
			}

			cbselect2.element.select2( 'close' );

			return true;
		},
		enable: function() {
			var cbselect2 = $( this ).data( 'cbselect2' );

			if ( ! cbselect2 ) {
				return false;
			}

			cbselect2.element.select2( 'enable', true );

			return true;
		},
		disable: function() {
			var cbselect2 = $( this ).data( 'cbselect2' );

			if ( ! cbselect2 ) {
				return false;
			}

			cbselect2.element.select2( 'enable', false );

			return true;
		},
		destroy: function() {
			var cbselect2 = $( this ).data( 'cbselect2' );

			if ( ! cbselect2 ) {
				return false;
			}

			cbselect2.element.select2( 'destroy' );
			cbselect2.element.off( 'cloned.cbselect2' );
			cbselect2.element.off( 'modified.cbselect2' );

			$.each( instances, function( i, instance ) {
				if ( instance.element == cbselect2.element ) {
					instances.splice( i, 1 );

					return false;
				}

				return true;
			});

			cbselect2.element.removeData( 'cbselect2' );
			cbselect2.element.triggerHandler( 'cbselect2.destroyed', [cbselect2] );

			return true;
		},
		instances: function() {
			return instances;
		}
	};

	$.fn.cbselect2 = function( options ) {
		if ( methods[options] ) {
			return methods[ options ].apply( this, Array.prototype.slice.call( arguments, 1 ) );
		} else if ( ( typeof options === 'object' ) || ( ! options ) ) {
			return methods.init.apply( this, arguments );
		}

		return this;
	};

	$.fn.cbselect2.defaults = {
		init: true,
		useData: true,
		width: 'calculate',
		height: 'calculate',
		triggerChange: true,
		dropdownAutoWidth: true,
		closeOnSelect: false,
		placeholderOption: function(){},
		placeholder: null
	};
})(jQuery);