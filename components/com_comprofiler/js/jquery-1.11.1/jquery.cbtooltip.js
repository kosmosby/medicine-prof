(function($) {
	var instances = [];
	var methods = {
		init: function( options ) {
			return this.each( function () {
				var $this = this;
				var cbtooltip = $( $this ).data( 'cbtooltip' );

				if ( cbtooltip ) {
					return; // cbtooltip is already bound; so no need to rebind below
				}

				cbtooltip = {};
				cbtooltip.options = options;
				cbtooltip.defaults = $.fn.cbtooltip.defaults;
				cbtooltip.settings = $.extend( true, {}, cbtooltip.defaults, cbtooltip.options );
				cbtooltip.element = $( $this );

				// Parse the elements data for settings overrides if data use is enabled:
				if ( cbtooltip.settings.useData ) {
					$.each( cbtooltip.defaults, function( key, value ) {
						if ( ( key != 'init' ) && ( key != 'id' ) && ( key != 'useData' ) ) {
							// Dash Separated:
							var dataValue = cbtooltip.element.data( 'cbtooltip' + key.charAt( 0 ).toUpperCase() + key.slice( 1 ) );

							if ( typeof dataValue != 'undefined' ) {
								if ( key.indexOf( 'classes' ) !== -1 ) {
									cbtooltip.settings[key] += ' ' + dataValue;
								} else {
									cbtooltip.settings[key] = dataValue;
								}
							} else {
								// No Separater:
								dataValue = cbtooltip.element.data( 'cbtooltip' + key.charAt( 0 ).toUpperCase() + key.slice( 1 ).toLowerCase() );

								if ( typeof dataValue != 'undefined' ) {
									if ( key.indexOf( 'classes' ) !== -1 ) {
										cbtooltip.settings[key] += ' ' + dataValue;
									} else {
										cbtooltip.settings[key] = dataValue;
									}
								}
							}
						}
					});
				}

				cbtooltip.element.triggerHandler( 'cbtooltip.init.before', [cbtooltip] );

				if ( ! cbtooltip.settings.init ) {
					return;
				}

				var tooltipClone = ( cbtooltip.settings.clone != null ? ( cbtooltip.settings.clone ? true : false ) : true );
				var tooltipModal = ( cbtooltip.settings.modal != null ? ( cbtooltip.settings.modal ? true : false ) : false );
				var tooltipMenu = ( cbtooltip.settings.menu != null ? ( cbtooltip.settings.menu ? true : false ) : false );
				var tooltipButtonHide = ( cbtooltip.settings.buttonHide != null ? ( cbtooltip.settings.buttonHide ? true : false ) : false );

				// Prepare the restoration of the element encase we're not cloning and we're not keeping alive the tooltip (basically moving instead of cloning):
				var tooltipRestore = ( cbtooltip.settings.tooltipTarget ? ( $( cbtooltip.settings.tooltipTarget ).length ? ( ( ! tooltipClone ) && ( ! cbtooltip.settings.keepAlive ) ? $( cbtooltip.settings.tooltipTarget ) : null ) : null ) : null );
				var tooltipRestoreParent = ( tooltipRestore && tooltipRestore.length ? tooltipRestore.parent() : null );

				if ( tooltipModal ) {
					tooltipMenu = false;
				} else if ( tooltipMenu ) {
					tooltipModal = false;

					// Menu default width needs to be the width supplied or auto as we want it to conform to its content:
					if ( cbtooltip.settings.width != cbtooltip.element.data( 'width' ) ) {
						cbtooltip.settings.width = null;
					}
				}

				// Prepare qTip with the settings parsed above:
				cbtooltip.tooltip = cbtooltip.element.qtip({
					id: cbtooltip.settings.id,
					overwrite: true,
					content: {
						text: ( cbtooltip.settings.tooltipTarget ? ( $( cbtooltip.settings.tooltipTarget ).length ? ( tooltipClone ? $( cbtooltip.settings.tooltipTarget ).clone( true ) : $( cbtooltip.settings.tooltipTarget ) ) : ( cbtooltip.settings.tooltip ? cbtooltip.settings.tooltip : null ) ) : ( cbtooltip.settings.tooltip ? cbtooltip.settings.tooltip : null ) ),
						title: ( cbtooltip.settings.titleTarget ? ( $( cbtooltip.settings.titleTarget ).length ? ( tooltipClone ? $( cbtooltip.settings.titleTarget ).clone( true ) : $( cbtooltip.settings.titleTarget ) ) : ( cbtooltip.settings.title ? cbtooltip.settings.title : null ) ) : ( cbtooltip.settings.title ? cbtooltip.settings.title : null ) ),
						button: ( ( cbtooltip.settings.openEvent && ( cbtooltip.settings.openEvent.indexOf( 'click' ) !== -1 ) ) || tooltipModal ? ( tooltipButtonHide || tooltipMenu ? false : cbtooltip.settings.buttonClose ) : false )
					},
					position: {
						container: ( cbtooltip.settings.container ? ( $( cbtooltip.settings.container ).length ? $( cbtooltip.settings.container ) : cbtooltip.settings.container ) : false ),
						viewport: ( cbtooltip.settings.viewport ? ( $( cbtooltip.settings.viewport ).length ? $( cbtooltip.settings.viewport ) : cbtooltip.settings.viewport ) : $( window ) ),
						my: ( cbtooltip.settings.positionMy ? cbtooltip.settings.positionMy : ( tooltipModal ? 'center' : 'top left' ) ),
						at: ( cbtooltip.settings.positionAt ? cbtooltip.settings.positionAt : ( tooltipMenu ? 'bottom left' : ( tooltipModal ? 'center' : 'bottom right' ) ) ),
						target: ( cbtooltip.settings.positionTarget ? ( cbtooltip.settings.positionTarget == 'mouse' ? 'mouse' : ( $( cbtooltip.settings.positionTarget ).length ? $( cbtooltip.settings.positionTarget ) : false ) ) : ( tooltipModal ? $( window ) : false ) ),
						adjust: {
							x: ( cbtooltip.settings.adjustX != null ? cbtooltip.settings.adjustX : 0 ),
							y: ( cbtooltip.settings.adjustY != null ? cbtooltip.settings.adjustY : ( tooltipMenu ? 5 : 0 ) ),
							scroll: ( cbtooltip.settings.adjustScroll != null ? cbtooltip.settings.adjustScroll : true ),
							resize: ( cbtooltip.settings.adjustResize != null ? cbtooltip.settings.adjustResize : true ),
							method: ( cbtooltip.settings.adjustMethod ? cbtooltip.settings.adjustMethod : 'shift flipinvert' )
						},
						effect: false
					},
					show: {
						target: ( cbtooltip.settings.openTarget ? ( $( cbtooltip.settings.openTarget ).length ? $( cbtooltip.settings.openTarget ) : false ) : false ),
						event: ( cbtooltip.settings.openEvent ? cbtooltip.settings.openEvent : ( tooltipModal ? 'click' : 'mouseenter click' ) ),
						solo: ( cbtooltip.settings.openSolo != null ? ( $( cbtooltip.settings.openSolo ).length ? $( cbtooltip.settings.openSolo ) : ( cbtooltip.settings.openSolo ? true : false ) ) : false ),
						delay: ( cbtooltip.settings.openDelay != null ? cbtooltip.settings.openDelay : 0 )
					},
					hide: {
						target: ( cbtooltip.settings.closeTarget ? ( $( cbtooltip.settings.closeTarget ).length ? $( cbtooltip.settings.closeTarget ) : false ) : false ),
						event: ( cbtooltip.settings.closeEvent ? cbtooltip.settings.closeEvent : ( tooltipModal ? 'unfocus' : 'mouseleave unfocus' ) ),
						fixed: ( cbtooltip.settings.closeFixed != null ? cbtooltip.settings.closeFixed : ( tooltipMenu || tooltipModal ) ),
						delay: ( cbtooltip.settings.closeDelay != null ? cbtooltip.settings.closeDelay : ( tooltipMenu ? 200 : 0 ) ),
						distance: ( cbtooltip.settings.closeDistance != null ? cbtooltip.settings.closeDistance : false ),
						leave: ( cbtooltip.settings.closeLeave != null ? cbtooltip.settings.closeLeave : 'window' ),
						inactive: ( cbtooltip.settings.closeInactive != null ? cbtooltip.settings.closeInactive : false )
					},
					style: {
						width: ( cbtooltip.settings.width != null ? cbtooltip.settings.width : false ),
						height: ( cbtooltip.settings.height != null ? cbtooltip.settings.height : false ),
						tip: {
							corner: ( cbtooltip.settings.tipHide != null ? ( cbtooltip.settings.tipHide ? false : true ) : ( ! ( tooltipMenu || tooltipModal ) ) ),
							width: ( cbtooltip.settings.tipWidth != null ? cbtooltip.settings.tipWidth : 6 ),
							height: ( cbtooltip.settings.tipHeight != null ? cbtooltip.settings.tipHeight : 6 ),
							offset: ( cbtooltip.settings.tipOffset != null ? cbtooltip.settings.tipOffset : 0 )
						},
						classes: ( cbtooltip.settings.classes ? cbtooltip.settings.classes : '' ) + ( tooltipMenu ? ' qtip-menu' : '' ) + ( tooltipModal ? ' qtip-modal' : '' )
					},
					events: {
						render: function( event, api ) {
							cbtooltip.element.triggerHandler( 'cbtooltip.render', [cbtooltip, event, api] );

							if ( tooltipMenu ) {
								$( api.elements.content ).on( 'click', function() {
									api.toggle( false );
								});
							}

							// Checks to see if the tooltip is too wide for the window and shrinks it as needed (auto makes tooltips mobile friendly):
							if ( api.elements.tooltip ) {
								var maxWidth = ( $( window ).width() - 50 );
								var tipWidth = api.elements.tooltip.width();

								if ( tipWidth > maxWidth ) {
									api.set( 'style.width', maxWidth );
								}
							}
						},
						show: function( event, api ) {
							cbtooltip.element.triggerHandler( 'cbtooltip.show', [cbtooltip, event, api] );

							if ( cbtooltip.settings.closeClasses ) {
								$( api.elements.target ).removeClass( cbtooltip.settings.closeClasses );
							}

							if ( cbtooltip.settings.openClasses ) {
								$( api.elements.target ).addClass( cbtooltip.settings.openClasses );
							}
						},
						hide: function( event, api ) {
							cbtooltip.element.triggerHandler( 'cbtooltip.hide', [cbtooltip, event, api] );

							if ( cbtooltip.settings.openClasses ) {
								$( api.elements.target ).removeClass( cbtooltip.settings.openClasses );
							}

							if ( cbtooltip.settings.closeClasses ) {
								$( api.elements.target ).addClass( cbtooltip.settings.closeClasses );
							}

							if ( tooltipRestore && tooltipRestore.length ) {
								tooltipRestoreParent.append( tooltipRestore );
							}
						},
						toggle: function( event, api ) {
							cbtooltip.element.triggerHandler( 'cbtooltip.toggle', [cbtooltip, event, api] );
						},
						visible: function( event, api ) {
							cbtooltip.element.triggerHandler( 'cbtooltip.visible', [cbtooltip, event, api] );

							if ( tooltipModal ) {
								api.elements.overlay = $( '<div class="qtip-overlay"></div>' );

								$( api.elements.overlay ).insertAfter( api.elements.tooltip );
								$( api.elements.overlay ).css( 'z-index', ( $.fn.qtip.zindex + 150 ) );
								$( api.elements.tooltip ).css( 'z-index', ( $.fn.qtip.zindex + 200 ) );
							}

							calculateContentMaxHeight.call( $this, cbtooltip, api );
						},
						hidden: function( event, api ) {
							cbtooltip.element.triggerHandler( 'cbtooltip.hidden', [cbtooltip, event, api] );

							if ( tooltipModal ) {
								$( api.elements.overlay ).remove();
							}

							if ( ! cbtooltip.settings.keepAlive ) {
								cbtooltip.options.id = api.get( 'id' );

								cbtooltip.element.cbtooltip( 'destroy' );
								cbtooltip.element.cbtooltip( cbtooltip.options );
							}
						},
						move: function( event, api ) {
							calculateContentMaxHeight.call( $this, cbtooltip, api );

							cbtooltip.element.triggerHandler( 'cbtooltip.move', [cbtooltip, event, api] );
						},
						focus: function( event, api ) {
							cbtooltip.element.triggerHandler( 'cbtooltip.focus', [cbtooltip, event, api] );
						},
						blur: function( event, api ) {
							cbtooltip.element.triggerHandler( 'cbtooltip.blur', [cbtooltip, event, api] );
						}
					}
				});

				// If the cbtooltip element is modified we need to rebuild it to ensure all our bindings are still ok:
				cbtooltip.element.on( 'modified.cbtooltip', function( e, oldId, newId, index ) {
					if ( oldId != newId ) {
						var targets = ['tooltip-target', 'title-target', 'open-target', 'close-target', 'position-target'];

						$.each( targets, function( targetId, target ) {
							var targetAttr = cbtooltip.element.attr( 'data-cbtooltip-' + target );

							if ( typeof targetAttr != 'undefined' ) {
								cbtooltip.element.attr( 'data-cbtooltip-' + target, targetAttr.replace( oldId, newId ) );
							}

							var targetData = cbtooltip.element.data( 'cbtooltip-' + target );

							if ( typeof targetData != 'undefined' ) {
								cbtooltip.element.data( 'cbtooltip-' + target, targetData.replace( oldId, newId ) );
							}
						});

						cbtooltip.element.cbtooltip( 'destroy' );
						cbtooltip.element.cbtooltip( cbtooltip.options );
					}
				});

				// If the cbtooltip is cloned we need to rebind it back:
				cbtooltip.element.on( 'cloned.cbtooltip', function( e, oldId ) {
					$( this ).off( 'cloned.cbtooltip' );
					$( this ).off( 'modified.cbtooltip' );

					var eventNamespace = $( this ).data( 'qtip' )._id;

					$( this ).removeData( 'cbtooltip' );
					$( this ).removeData( 'hasqtip' );
					$( this ).removeData( 'qtip' );
					$( this ).removeAttr( 'data-hasqtip' );
					$( this ).off( '.' + eventNamespace );
					$( this ).cbtooltip( cbtooltip.options );
				});

				cbtooltip.element.triggerHandler( 'cbtooltip.init.after', [cbtooltip] );

				// Bind the cbtooltip to the element so it's reusable and chainable:
				cbtooltip.element.data( 'cbtooltip', cbtooltip );

				// Add this instance to our instance array so we can keep track of our cbtooltip instances:
				instances.push( cbtooltip );
			});
		},
		get: function( option ) {
			var cbtooltip = $( this ).data( 'cbtooltip' );

			if ( ! cbtooltip ) {
				return false;
			}

			cbtooltip.tooltip.qtip( 'api' ).get( option );

			return true;
		},
		set: function( option, value ) {
			var cbtooltip = $( this ).data( 'cbtooltip' );

			if ( ! cbtooltip ) {
				return false;
			}

			cbtooltip.tooltip.qtip( 'api' ).set( option, value );

			return true;
		},
		toggle: function() {
			var cbtooltip = $( this ).data( 'cbtooltip' );

			if ( ! cbtooltip ) {
				return false;
			}

			cbtooltip.tooltip.qtip( 'api' ).toggle();

			return true;
		},
		show: function() {
			var cbtooltip = $( this ).data( 'cbtooltip' );

			if ( ! cbtooltip ) {
				return false;
			}

			cbtooltip.tooltip.qtip( 'api' ).toggle( true );

			return true;
		},
		hide: function() {
			var cbtooltip = $( this ).data( 'cbtooltip' );

			if ( ! cbtooltip ) {
				return false;
			}

			cbtooltip.tooltip.qtip( 'api' ).toggle( false );

			return true;
		},
		enable: function() {
			var cbtooltip = $( this ).data( 'cbtooltip' );

			if ( ! cbtooltip ) {
				return false;
			}

			cbtooltip.tooltip.qtip( 'api' ).disable( false );

			cbtooltip.element.triggerHandler( 'cbtooltip.enable', [cbtooltip] );

			return true;
		},
		disable: function() {
			var cbtooltip = $( this ).data( 'cbtooltip' );

			if ( ! cbtooltip ) {
				return false;
			}

			cbtooltip.tooltip.qtip( 'api' ).disable( true );

			cbtooltip.element.triggerHandler( 'cbtooltip.disable', [cbtooltip] );

			return true;
		},
		reposition: function() {
			var cbtooltip = $( this ).data( 'cbtooltip' );

			if ( ! cbtooltip ) {
				return false;
			}

			cbtooltip.tooltip.qtip( 'api' ).reposition();

			return true;
		},
		focus: function() {
			var cbtooltip = $( this ).data( 'cbtooltip' );

			if ( ! cbtooltip ) {
				return false;
			}

			cbtooltip.tooltip.qtip( 'api' ).focus();

			return true;
		},
		blur: function() {
			var cbtooltip = $( this ).data( 'cbtooltip' );

			if ( ! cbtooltip ) {
				return false;
			}

			cbtooltip.tooltip.qtip( 'api' ).blur();

			return true;
		},
		destroy: function() {
			var cbtooltip = $( this ).data( 'cbtooltip' );

			if ( ! cbtooltip ) {
				return false;
			}

			cbtooltip.tooltip.qtip( 'api' ).destroy( true );
			cbtooltip.element.off( 'cloned.cbtooltip' );
			cbtooltip.element.off( 'modified.cbtooltip' );

			$.each( instances, function( i, instance ) {
				if ( instance.element == cbtooltip.element ) {
					instances.splice( i, 1 );

					return false;
				}

				return true;
			});

			cbtooltip.element.removeData( 'cbtooltip' );
			cbtooltip.element.triggerHandler( 'cbtooltip.destroyed', [cbtooltip] );

			return true;
		},
		instances: function() {
			return instances;
		}
	};

	function calculateContentMaxHeight( cbtooltip, api ) {
		if ( api.elements.tooltip ) {
			api.elements.content.css( 'max-height', '' );

			var tipHeight = api.elements.tooltip.height();

			if ( tipHeight ) {
				if ( api.elements.titlebar ) {
					api.elements.titlebar.css( 'max-height', '' );

					var titleHeight = api.elements.titlebar.outerHeight( true );

					if ( titleHeight ) {
						api.elements.titlebar.css( 'max-height', titleHeight );

						if ( api.elements.content ) {
							api.elements.content.css( 'max-height', ( tipHeight - titleHeight ) );
						}
					}
				} else if ( api.elements.content ) {
					api.elements.content.css( 'max-height', tipHeight );
				}
			}
		}
	}

	$.fn.cbtooltip = function( options ) {
		if ( methods[options] ) {
			return methods[ options ].apply( this, Array.prototype.slice.call( arguments, 1 ) );
		} else if ( ( typeof options === 'object' ) || ( ! options ) ) {
			return methods.init.apply( this, arguments );
		}

		return this;
	};

	$.fn.cbtooltip.defaults = {
		init: true,
		useData: true,
		id: null,
		tooltip: null,
		tooltipTarget: null,
		title: null,
		titleTarget: null,
		openEvent: null,
		openTarget: null,
		openClasses: null,
		openSolo: null,
		openDelay: null,
		closeEvent: null,
		closeTarget: null,
		closeClasses: null,
		closeFixed: null,
		closeDelay: null,
		closeDistance: null,
		closeLeave: null,
		closeInactive: null,
		buttonHide: null,
		buttonClose: null,
		width: null,
		height: null,
		modal: null,
		menu: null,
		clone: null,
		classes: null,
		container: null,
		viewport: null,
		positionMy: null,
		positionAt: null,
		positionTarget: null,
		adjustX: null,
		adjustY: null,
		adjustScroll: null,
		adjustResize: null,
		adjustMethod: null,
		tipHide: null,
		tipWidth: null,
		tipHeight: null,
		tipOffset: null,
		keepAlive: null
	};
})(jQuery);