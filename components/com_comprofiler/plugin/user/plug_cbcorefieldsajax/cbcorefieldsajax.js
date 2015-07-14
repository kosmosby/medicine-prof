(function($) {
	var instances = [];
	var methods = {
		init: function( options ) {
			return this.each( function () {
				var $this = this;
				var cbajaxfield = $( $this ).data( 'cbajaxfield' );

				if ( cbajaxfield ) {
					return; // cbajaxfield is already bound; so no need to rebind below
				}

				cbajaxfield = {};
				cbajaxfield.options = options;
				cbajaxfield.defaults = $.fn.cbajaxfield.defaults;
				cbajaxfield.settings = $.extend( true, {}, cbajaxfield.defaults, cbajaxfield.options );
				cbajaxfield.element = $( $this );

				if ( cbajaxfield.settings.useData ) {
					$.each( cbajaxfield.defaults, function( key, value ) {
						if ( ( key != 'init' ) && ( key != 'useData' ) ) {
							// Dash Separated:
							var dataValue = cbajaxfield.element.data( 'cbajaxfield' + key.charAt( 0 ).toUpperCase() + key.slice( 1 ) );

							if ( typeof dataValue != 'undefined' ) {
								cbajaxfield.settings[key] = dataValue;
							} else {
								// No Separater:
								dataValue = cbajaxfield.element.data( 'cbajaxfield' + key.charAt( 0 ).toUpperCase() + key.slice( 1 ).toLowerCase() );

								if ( typeof dataValue != 'undefined' ) {
									cbajaxfield.settings[key] = dataValue;
								}
							}
						}
					});
				}

				cbajaxfield.element.trigger( 'cbajaxfield.init.before', [cbajaxfield] );

				if ( ! cbajaxfield.settings.init ) {
					return;
				}

				cbajaxfield.element.children( '.cbAjaxForm' ).ajaxForm({
					target: cbajaxfield.element.children( '.cbAjaxValue' ),
					type: 'POST',
					beforeSerialize: function( form, options ) {
						cbajaxfield.element.trigger( 'cbajaxfield.serialize', [cbajaxfield, form, options] );
					},
					beforeSubmit: function( formData, form, options ) {
						var validator = cbajaxfield.element.children( '.cbAjaxForm' ).data( 'cbvalidate' );

						if ( validator ) {
							if ( ! validator.element.cbvalidate( 'validate' ) ) {
								return false;
							}
						}

						cbajaxfield.element.children( '.cbAjaxForm' ).addClass( 'hidden' );
						cbajaxfield.element.append( '<span class="cbSpinner fa fa-spinner fa-spin-fast"></span>' );

						cbajaxfield.element.trigger( 'cbajaxfield.submit', [cbajaxfield, formData, form, options] );
					},
					error: function( jqXHR, textStatus, errorThrown ) {
						cbajaxfield.element.trigger( 'cbajaxfield.error', [cbajaxfield, jqXHR, textStatus, errorThrown] );
					},
					success: function( data, textStatus, jqXHR ) {
						cbajaxfield.element.trigger( 'cbajaxfield.success', [cbajaxfield, data, textStatus, jqXHR] );
					},
					complete: function( jqXHR, textStatus, form ) {
						cbajaxfield.element.children( '.cbAjaxValue' ).removeClass( 'hidden' );
						cbajaxfield.element.children( '.cbSpinner' ).remove();

						cbajaxfield.element.trigger( 'cbajaxfield.done', [cbajaxfield, jqXHR, textStatus, form] );
					}
				});

				cbajaxfield.cancelHandler = function( e ) {
					cbajaxfield.element.children( '.cbAjaxForm' ).addClass( 'hidden' );
					cbajaxfield.element.children( '.cbAjaxValue' ).removeClass( 'hidden' );

					cbajaxfield.element.trigger( 'cbajaxfield.cancel', [cbajaxfield, e] );
				};

				cbajaxfield.element.on( 'click', '.cbAjaxCancel', cbajaxfield.cancelHandler );

				cbajaxfield.editHandler = function( e ) {
					if ( ! $( e.target ).is( 'a' ) ) {
						cbajaxfield.element.children( '.cbAjaxValue' ).addClass( 'hidden' );
						cbajaxfield.element.children( '.cbAjaxForm' ).removeClass( 'hidden' );
					}

					cbajaxfield.element.trigger( 'cbajaxfield.edit', [cbajaxfield, e] );
				};

				cbajaxfield.element.on( 'click', '.cbAjaxValue', cbajaxfield.editHandler );

				// If the cbajaxfield element is modified we need to rebuild it to ensure all our bindings are still ok:
				cbajaxfield.element.on( 'modified.cbajaxfield', function( e, orgId, oldId, newId ) {
					if ( oldId != newId ) {
						cbajaxfield.element.cbajaxfield( 'destroy' );
						cbajaxfield.element.cbajaxfield( cbajaxfield.options );
					}
				});

				// If the cbajaxfield is cloned we need to rebind it back:
				cbajaxfield.element.on( 'cloned.cbajaxfield', function( e, oldId ) {
					$( this ).off( 'cloned.cbajaxfield' );
					$( this ).off( 'modified.cbajaxfield' );
					$( this ).removeData( 'cbajaxfield' );
					$( this ).children( '.cbAjaxForm' ).addClass( 'hidden' );
					$( this ).children( '.cbAjaxValue' ).removeClass( 'hidden' );
					$( this ).children( '.cbSpinner' ).remove();
					$( this ).off( 'click', '.cbAjaxCancel', cbajaxfield.cancelHandler );
					$( this ).off( 'click', '.cbAjaxValue', cbajaxfield.editHandler );
					$( this ).children( '.cbAjaxForm' ).ajaxFormUnbind();
					$( this ).cbajaxfield( cbajaxfield.options );
				});

				cbajaxfield.element.trigger( 'cbajaxfield.init.after', [cbajaxfield] );

				// Bind the cbajaxfield to the element so it's reusable and chainable:
				cbajaxfield.element.data( 'cbajaxfield', cbajaxfield );

				// Add this instance to our instance array so we can keep track of our cbajaxfield instances:
				instances.push( cbajaxfield );
			});
		},
		destroy: function() {
			var cbajaxfield = $( this ).data( 'cbajaxfield' );

			if ( ! cbajaxfield ) {
				return false;
			}

			cbajaxfield.element.children( '.cbAjaxForm' ).ajaxFormUnbind();
			cbajaxfield.element.off( 'cloned.cbajaxfield' );
			cbajaxfield.element.off( 'modified.cbajaxfield' );

			$.each( instances, function( i, instance ) {
				if ( instance.element == cbajaxfield.element ) {
					instances.splice( i, 1 );

					return false;
				}

				return true;
			});

			cbajaxfield.element.children( '.cbAjaxForm' ).addClass( 'hidden' );
			cbajaxfield.element.children( '.cbAjaxValue' ).removeClass( 'hidden' );
			cbajaxfield.element.children( '.cbSpinner' ).remove();
			cbajaxfield.element.off( 'click', '.cbAjaxCancel', cbajaxfield.cancelHandler );
			cbajaxfield.element.off( 'click', '.cbAjaxValue', cbajaxfield.editHandler );
			cbajaxfield.element.removeData( 'cbajaxfield' );
			cbajaxfield.element.trigger( 'cbajaxfield.destroyed', [cbajaxfield] );

			return true;
		},
		instances: function() {
			return instances;
		}
	};

	$.fn.cbajaxfield = function( options ) {
		if ( methods[options] ) {
			return methods[ options ].apply( this, Array.prototype.slice.call( arguments, 1 ) );
		} else if ( ( typeof options === 'object' ) || ( ! options ) ) {
			return methods.init.apply( this, arguments );
		}

		return this;
	};

	$.fn.cbajaxfield.defaults = {
		init: true,
		useData: false
	};
})(jQuery);