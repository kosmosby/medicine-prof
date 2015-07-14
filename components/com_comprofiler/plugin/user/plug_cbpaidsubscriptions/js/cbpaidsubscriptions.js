/**
 * Copyright (C) 2007-2014 and Trademark of Lightning MultiCom SA, Switzerland - www.joomlapolis.com - and its licensors, all rights reserved
 * License: GNU General Public License version 2 http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
 */
// var cbpayHideFields;

(function($) {
	var cbpaySels;
	var cbpayFirstTimeDone = false;
	var cbpayPlansConds = [];
	/**
	 * Handles the checking/unchecking of plans and the corresponding hiding of children plans and of registration-fields:
	 */
	function paidsubsChange(e) {
		var fieldsToShow = new Array();
		var fieldsToHide = new Array();
		var r  = new RegExp('^cbpplan[EN]\\[(\\d+)\\]\\[selected\\]\\[\\]$', '');
		if ( ! cbpayFirstTimeDone ) {
			// first time:
			cbpaySels.each( function(i) {
				if ( $(this).prop('checked') || ( ( $(this).attr('type') == 'hidden') && ($(this).val() > 0) ) ) {
					$(this).attr('cbsubschkdef', '1' );
				}
			});
			cbpaySels.each( function(i) {
				var iPlan = r.exec( $(this).attr('name') );
				if ( iPlan[1] == '0' ) {
					// checks if the selected plan has no parent, its childrens are set to default values:
					if ( $(this).prop('checked') || ( ( $(this).attr('type' ) == 'hidden') && ($(this).val() > 0) ) ) {
						cbpaySels.filter( '[name=\'cbpplanE[' + $(this).val() + '][selected][]\'],[name=\'cbpplanN[' + $(this).val() + '][selected][]\']' ).filter( function() {
							return ( $(this).attr('cbsubschkdef') == '1');
						} ).attr( 'checked', true );
					}
				} else {
					// checks that parent plan is checked, if exists, otherwise unchecks this:
					if ( cbpaySels.filter( '[value=\'' + parseInt(iPlan[1]) + '\']' ).filter( '[name=\'cbpplanE[0][selected][]\'],[name=\'cbpplanN[0][selected][]\']' ).prop('checked') == '' ) {
						$(this).prop( 'checked', false );
					}
				}
			});
		} else {
			var clickedObject = $( typeof(e) != 'undefined' ? ( typeof(e.target) != 'undefined' ? e.target : e ) : '' );
			var clickedPlan = r.exec(clickedObject.attr('name'));
			if ( clickedPlan !== null ) {
				if ( clickedPlan[1] == '0' ) {
					// a parent plan has been clicked:
					if ( clickedObject.prop('checked') ) {
						// if it's now checked, its childrens are set to default values:
						cbpaySels.filter( '[name=\'cbpplanE[' + clickedObject.val() + '][selected][]\'],[name=\'cbpplanN[' + clickedObject.val() + '][selected][]\']' ).filter( function() {
							return ( $(this).attr('cbsubschkdef') == '1');
						} ).prop( 'checked', true );
					}
				} else {
					// a child plan has been clicked:
					// then, if a child is checked, checks its parent too:
					var p = cbpaySels.filter( '[value=\'' + parseInt(clickedPlan[1]) + '\']' ).filter( '[name=\'cbpplanE[0][selected][]\'],[name=\'cbpplanN[0][selected][]\']' );
					if ( $(this).prop('checked') == true ) {
						p.prop( 'checked', true );
						// and uncheck the children of other now unchecked (in case of radios) parents below
					}					
				}
			}
		}
		cbpaySels.each( function(i) {
			var iPlan = r.exec( $(this).attr('name') );
			// and uncheck the children of unchecked parents:
			if ( ( iPlan[1] == '0' ) && ( $(this).val() != '0' ) && ( ! $(this).prop('checked') ) ) {
				cbpaySels.filter( '[name=\'cbpplanE[' + $(this).val() + '][selected][]\'],[name=\'cbpplanN[' + $(this).val() + '][selected][]\']' ).prop( 'checked', false );
			}
		});
		cbpaySels.each( function(i) {
			// 1) decides which CB fields to show or hide:
			// 2) removes required from inputs as plan is not selected:
			if ( $(this).prop('checked') || ( ( $(this).attr('type' ) == 'hidden') && ($(this).val() > 0) ) ) {
				fieldsToHide = fieldsToHide.concat( cbpayHideFields[$(this).val()] );
				$(this).closest('.cbregPlanSelector').find('.cbRegNameDesc').find(':not(.cbregSubPlanSelector) .fieldCell input.requiredDisabled,:not(.cbregSubPlanSelector) .fieldCell select.requiredDisabled').removeClass('requiredDisabled').addClass('required');
			} else {
				fieldsToShow = fieldsToShow.concat( cbpayHideFields[$(this).val()] );
				$(this).closest('.cbregPlanSelector').find('.cbRegNameDesc').find(':not(.cbregSubPlanSelector) .fieldCell input.required,:not(.cbregSubPlanSelector) .fieldCell select.required').removeClass('required').addClass('requiredDisabled');
			}
		});
		// Show or hide fields:
		for (var i=0;i<fieldsToShow.length;i++) {
			var fieldToShow = $('#cbfr_' + fieldsToShow[i] + ',#cbfrd_' + fieldsToShow[i] + ',#cbfr_' + fieldsToShow[i] + '__verify');
			if ( cbpayFirstTimeDone && ! fieldToShow.parent().is( ':hidden' ) ) {
				fieldToShow.fadeIn("slow");
			} else {
				fieldToShow.show();
			}
			fieldToShow.find('.fieldCell,.cb_field').removeClass('requiredDisabled').find('.requiredDisabled').removeClass('requiredDisabled').addClass('required').attr('mosReq','1');
		}
		for (var i=0;i<fieldsToHide.length;i++) {
			var fieldToHide = $('#cbfr_' + fieldsToHide[i] + ',#cbfrd_' + fieldsToHide[i] + ',#cbfr_' + fieldsToHide[i] + '__verify');
			if ( cbpayFirstTimeDone && ! fieldToHide.parent().is( ':hidden' ) ) {
				fieldToHide.fadeOut("slow");
			} else {
				fieldToHide.hide();
			}
			fieldToHide.find('.fieldCell,.cb_field').addClass('requiredDisabled').find('input.required,select.required,textarea.required').removeClass('required').addClass('requiredDisabled').attr('mosReq','0');
		}
		// Show or hide plans at registration depending on conditions to show or hide:
		for (var i=0; i<cbpayPlansConds.length;i++) {
			var cond = cbpayPlansConds[i];			// 	{ planId : planId, plansReq : plansReq, plansNotReq : plansNotReq, fieldsReq : fieldsReq, fieldsNotReq : fieldsNotReq, field1 : field1, regexp1 : regexp1 }

			// Checks plans conditions:
			var pOk = cond.plansReq.length ? false : true;
			// Any required plan is enough to show the target plan:
			for (var j=0; j<cond.plansReq.length;j++) {
				if ( $('#cbregUpgrades #cbregProduct_'+cond.plansReq[j]+'.cbregPlanSelector .cbregTick input').prop( 'checked' ) == true ) {
					pOk = true;
					break;
				}
			}
			// All non-allowed plans must be unselected to show the target plan:
			for (var j=0; j<cond.plansNotReq.length;j++) {
				if ( $('#cbregUpgrades #cbregProduct_'+cond.plansNotReq[j]+'.cbregPlanSelector .cbregTick input').prop( 'checked' ) == true ) {
					pOk = false;
					break;
				}
			}

			// check fields conditions:
			var fOk = true;
			// All required fields must be non-empty to show the target plan:
			for (var j=0; j<cond.fieldsReq.length;j++) {
				var f = $('#cbfv_'+cond.fieldsReq[j]+' input,#cbfv_'+cond.fieldsReq[j]+' select');
				if ( ! ( f.prop( 'checked' ) == true || ( ( f.attr( 'type' ) != 'checkbox' ) && ( f.attr( 'type' ) != 'radio' ) && ( f.val() != '' ) ) ) ) {
					fOk = false;
					break;
				}
			}
			// All non-allowed fields must be empty to show the target plan:
			for (var j=0; j<cond.fieldsNotReq.length;j++) {
				var f = $('#cbfv_'+cond.fieldsNotReq[j]+' input,#cbfv_'+cond.fieldsNotReq[j]+' select');
				if ( f.prop( 'checked' ) == true || ( ( f.attr( 'type' ) != 'checkbox' ) && ( f.attr( 'type' ) != 'radio' ) && ( f.val() != '' ) ) ) {
					fOk = false;
					break;
				}
			}
			if (cond.field1 > 0) {
				var f = $('#cbfv_'+cond.field1+' input,#cbfv_'+cond.field1+' select');
				if ( f.length > 0 ) {
					var val = '';
					if ( ( f.attr( 'type' ) == 'checkbox' ) || ( f.attr( 'type' ) == 'radio' ) ) {
						f.each( function() {
							if ( $(this).prop( 'checked' ) == true ) {
								val = val + ( val == '' ? '' : '|*|' ) + $(this).attr('value'); 
							}
						});
					} else if ( f.is('select') ) {
						val = f.val() || [];
						if ( $.isArray( val ) ) {
							val = val.join("|*|");
						}
					} else {
						val = f.val();
					}
					var op = cond.regexp1[0];
					if (op == '<' || op == '<') {
						var c = cond.regexp1.slice(1);
						if ( c.match(/^[0-9.]+$/) ) {
							if ( val.match(/^[0-9.]*$/) ) {
								// numbers compare:
								if ( op == '<' ) {
									if ( ! ( Number(val) < Number(c) ) ) {
										fOk = false;
									}
								} else {
									if ( ! ( Number(val) > Number(c) ) ) {
										fOk = false;
									}
								}
							} else {
								// field is not a number: fail
								fOk = false;
							}
						} else {
							// strings compare:
							if ( op == '<' ) {
								if ( ! ( val < c ) ) {
									fOk = false;
								}
							} else {
								if ( ! ( val > c ) ) {
									fOk = false;
								}
							}
						}
					} else if ( op == '/' ) {
						// regular expression compare:
						var r = new RegExp(cond.regexp1.slice(1,-1));
						if ( ! val.match(r) ) {
							fOk = false;
						}
					}
				}
			}

			// Now applies condition:
			var planTarget = $('#cbregUpgrades #cbregProduct_'+cond.planId+'.cbregPlanSelector');
			if ( pOk && fOk ) {
				if ( cbpayFirstTimeDone ) {
					planTarget.slideDown('slow');
				} else {
					planTarget.show();
				}
				var targetWasVisible = planTarget.hasClass('cbregDoHideChildrenVisible');
				planTarget.removeClass('cbregDoHideChildrenHidden').addClass('cbregDoHideChildrenVisible');
				planTarget.find('.fieldCell input.requiredDisabled,.fieldCell select.requiredDisabled').removeClass('requiredDisabled').addClass('required').attr('mosReq','1');
				var tick = planTarget.find('.cbregTick input');
				if ( (!targetWasVisible) && ( tick.attr('cbsubschkdef') == '1' ) ) {
					tick.prop( 'checked', true );
				}
			} else {
				if ( cbpayFirstTimeDone ) {
					planTarget.slideUp('slow');
				} else {
					planTarget.hide();
				}
				planTarget.removeClass('cbregDoHideChildrenVisible').addClass('cbregDoHideChildrenHidden');
				planTarget.find('.fieldCell input.required,.fieldCell select.required').removeClass('required').addClass('requiredDisabled').attr('mosReq','0');
				planTarget.find('.cbregTick input').prop( 'checked', false );
			}
		}

		// Finally hides or shows children:
		$('#cbregUpgrades .cbregDoHideChildren .cbregTick input').each( function() {
			var subPlans = $(this).closest('.cbregPlanSelector').find('.cbRegNameDesc .cbregSubPlanSelector');
			if ( $(this).prop( 'checked' ) == true ) {
				if ( cbpayFirstTimeDone ) {
					subPlans.slideDown('slow');
				} else {
					subPlans.show();
				}
				$(this).closest('.cbregPlanSelector').removeClass('cbregDoHideChildrenHidden').addClass('cbregDoHideChildrenVisible');
				subPlans.find('.fieldCell input.requiredDisabled,.fieldCell select.requiredDisabled').removeClass('requiredDisabled').addClass('required').attr('mosReq','1');
			} else {
				if ( cbpayFirstTimeDone ) {
					subPlans.slideUp('slow');
				} else {
					subPlans.hide();
				}
				$(this).closest('.cbregPlanSelector').removeClass('cbregDoHideChildrenVisible').addClass('cbregDoHideChildrenHidden');
				subPlans.find('.fieldCell input.required,.fieldCell select.required').removeClass('required').addClass('requiredDisabled').attr('mosReq','0');
			}
		});
		// adds class to selected ones:
		cbpaySels.each( function(i) {
			if ( $(this).prop('checked') ) {
				$(this).parents('.cbregPlanSelector').addClass('cbregPlanSelected');
			} else {
				$(this).parents('.cbregPlanSelector').removeClass('cbregPlanSelected');
			}
		});
		cbpayFirstTimeDone = true;
	}
	var donationSelval = [];
	/*
	 * Handles the Donation plans select drop-down:
	 */
	function paidsubsDonationSelect() {
		// [1]: prefix, [2]: parent_id, [3]: plan_id :
		var iNamePrefix = /^([^\[]+)[EN]\[([^\]]+)\]\[donate\]\[plan([^\]]+)\]\[donsel\]$/.exec($(this).attr('name'));
		// unchecks the selection of the plan if the donation is not selected:
		$( '#'+iNamePrefix[1]+iNamePrefix[3] ).prop( 'checked', ( $(this).val() !== '' ) ).triggerHandler('click');
		// unhides the free donation field if the selected value is 'other' (0):
		if ( $(this).val() === '0' ) {
			$(this).closest('.cbregDonationSelect').next('span.cbregDonationValue').fadeIn('slow');
			if ( ( typeof( donationSelval[$(this).attr('name')] ) == 'undefined' ) || ( ! donationSelval[$(this).attr('name')] ) ) {
				$(this).closest('.cbregDonationSelect').next('span.cbregDonationValue').children('input.cbregDonationFreeValue').focus();
				donationSelval[$(this).attr('name')] = true;
			}
		} else {
			$(this).closest('.cbregDonationSelect').next('span.cbregDonationValue').fadeOut('slow');
			donationSelval[$(this).attr('name')] = false;
		}
		return true;
	}
	/*
	 * Handles the Donation plans free donation value text input box:
	 */
	function paidsubsDonationValueBlur() {
		// if the donation selector selects 'other' (0):
		if ( $(this).parents('.cbregDonationValue').slice(0,1).prev('span.cbregDonationSelect').children('select.cbregDonationSelector').val() === '0' ) {
			// [1]: prefix, [2]: parent_id, [3]: plan_id :
			var iNamePrefix = /^([^\[]+)[EN]\[([^\]]+)\]\[donate\]\[plan([^\]]+)\]\[donval\]$/.exec($(this).attr('name'));
			// checks if the value is non-zero float or empty:
			var isZeroOrEmpty = ( /^ *0*\.?0* *$/.test( $(this).val() ) );
			// ticks/unticks the corresponding donation plan selector depending of free donation value and triggers the other events:
			$( '#'+iNamePrefix[1]+iNamePrefix[3] ).prop( 'checked', ! isZeroOrEmpty ).triggerHandler('click');
		}
		return true;
	}
	/*
	 * Handles the Donation plans free donation value text input box:
	 */
	function paidsubsDonationValueChangeKeyUp() {
		// avoids non-float value character inputs:
		$(this).val( /[0-9]*\.?[0-9]*/.exec( $(this).val() ) );
		return true;
	}
	var termsChecked = false;
	/*
	 * In basket, terms and conditions acceptance got changed
	 */
	function paidsubsTermsAcceptChange() {
		var paybuttons = $('.cbpayChoices .cbpaidCCbutton form input[type="image"], .cbpayChoices .cbpaidCCbutton form button');
		if ( $(this).prop('checked') ) {
			paybuttons.removeAttr('disabled');
			$('#cbregTermsError').fadeOut( function() { $(this).remove(); });
			paybuttons.fadeTo( 'fast', 1 );
		} else {
			paybuttons.fadeTo( termsChecked ? 'fast' : 0, 0.3 );
			if ( ! termsChecked ) {
				$('.cbpayChoices').show();
				$('button#cbTermsAccept').parent().hide();
				termsChecked = true;
			}
			paybuttons.click( function() {
				if ( ! $('input#terms_accepted').prop('checked') ) {
					if ( $('#cbregTermsError').length == 0 ) {
						$('.cbregTermsAccept').after( '<div class="cb_result_warning" id="cbregTermsError">' + $('button#cbTermsAccept').attr('title') + '</div>' );
					}
					$('#cbregTermsError').fadeIn("slow", function() { $(this).fadeOut( function() { $(this).fadeIn(); }); });
					return false;
				}
			});
		}
	}
	var clickableDesc = true;
	/**
	 * Handles clicks into descriptions to select plan:
	 */
	function paidsubsClickDescription(event) {
		if ( clickableDesc ) {
			if ( $(event.target).filter('input,select,label,button,option,a').length == 0 ) {
				clickableDesc = false;
				var checkboxOfDesc = $(this).find('.cbregTick input[type!="hidden"]').first().filter( ':not(:checked)');
				checkboxOfDesc.prop('checked',true);
				clickableDesc = true;
				paidsubsChange(checkboxOfDesc);		//checkboxOfDesc.click(); can not be used here, since it toggles the checkbox after calling other event handlers, while a real mouse click toggles the checkbox first.
				return false;
			}
		}
        return null;
	}
	function hextobin( hex ) {
		var bytes = [];
		for ( var i=0; i < hex.length-1; i+=2 ) {
			bytes.push( parseInt( hex.substr( i, 2 ), 16 ) );
		}
		return String.fromCharCode.apply(String, bytes);
	}
	var paidsubsInitDone = false;
	$.extend({
		cbpaidsubs : {
			paidsubsInit : function() {
				if ( paidsubsInitDone ) {
					return;
				}
				paidsubsInitDone = true;
				// parent and child plans selection:
				cbpaySels = $('#cbregUpgrades input').filter( function(index) {
					return /^cbpplan[EN]\[(\d+)\]\[selected\]\[\]$/.test( $(this).attr('name') );
				});
				paidsubsChange();
				cbpaySels.click( paidsubsChange );
				// make whole plan description backend clickable:
				$(document).on( 'click', '.cbregPlanSelector', paidsubsClickDescription );
				// donations:
				$('#cbregUpgrades select.cbregDonationSelector').change( paidsubsDonationSelect );
				$('#cbregUpgrades input.cbregDonationFreeValue').blur( paidsubsDonationValueBlur ).change( paidsubsDonationValueChangeKeyUp ).keyup( paidsubsDonationValueChangeKeyUp );

				var cbpPMajaxSubmited = false;
				var ajaxFormOptions = {
					target: $('#cbpayOrderContainer'),
					type: 'POST',
					beforeSubmit:	function(formData, jqForm, options) {
						options.url = hextobin( $(jqForm).find( 'input[name="ajaxurl"]' ).attr('value') );
						$('#cbpayOrderContainer>div').fadeTo( 'fast', 0.07 ).parent().prepend('<div style="position:absolute; z-index:100;" class="cbregAjLoading cbregAjLoadingCentered">&nbsp;</div>').fadeIn( 'fast' );
					},
					success: function( responseText, statusText ) {
						attachRadios();
						cbpPMajaxSubmited = false;
					}
				};
				function attachRadios() {
					termsChecked = false;
					$('input#terms_accepted').each( paidsubsTermsAcceptChange ).change( paidsubsTermsAcceptChange );
					$('.cbregCCradioLi:not(.cbregCCradioSelected)').each( function() {
						$(this).find('.cbregCCselDescription').hide();
					} ).hover( function() {
						$('.cbregCCselDescription').stop(true,true);
						$(this).find('.cbregCCselDescription').slideDown('fast');
					}, function() {
						$('.cbregCCselDescription').stop(true,true);
						$(this).find('.cbregCCselDescription').slideUp('fast');
					} );
					$('.cbregPaymentMethodsSelect form, .cbregCurrencySelect form').ajaxForm( ajaxFormOptions );
					$('button#cbregSelectPayment, button#cbregSelectCurrency').parent().hide();
				}
				attachRadios();
				$('#cbpayOrderContainer').delegate( '.cpayOrderCurrency', 'change', function() {
					// In basket, currency got changed:
					if ( ! cbpPMajaxSubmited ) {
						// Disable all payment buttons while basket is changing its currency:
						$('.cbpayChoices form input:image, .cbpayChoices form input:button, .cbpayChoices form button, .cbpayChoices form input:submit, .cbregPaymentMethodChoice form input:radio, .cbregPaymentMethodChoice form button').prop( 'disabled', false );
						cbpPMajaxSubmited = true;
						$(this.form).submit();
					}
					return true;
				} );
/* Not needed, as can be displayed in text:
				// change of currency payment radios:
				$('#cbpayOrderContainer').delegate( '.cbregCCradioLi:not(.cbregCCradioSelected) .cbregconfirmtitleonclick input.cbpaidCCpaymethod', 'change', function() {
					return confirm( $(this).attr('title') );
				} );
*/
				// change of currency payment buttons:
				$('#cbpayOrderContainer').delegate( '.cbregconfirmtitleonclick input.cbpaidCCimageInput', 'click', function() {
					var title = $(this).attr('title');
					if ( title == '' ) {
						return true;
					} else {
						if ( confirm( $(this).attr('title') ) ) {
							$('#cbpayOrderContainer>div').fadeTo( 'fast', 0.07 ).parent().prepend('<div style="position:absolute; z-index:100;" class="cbregAjLoading cbregAjLoadingCentered">&nbsp;</div>').fadeIn( 'fast' );
							return true;
						} else {
							return false;
						}
					}
				} );
				$('#cbpayOrderContainer').delegate( '.cbregCCradioLi:not(.cbregCCradioSelected)', 'click', function() {
					// In basket, radios for payment method got clicked:
					if ( ! cbpPMajaxSubmited ) {
						cbpPMajaxSubmited = true;
						$($(this).find('input:radio.cbpaidCCpaymethod').prop('checked', true)[0].form).submit();
					}
					return true;
				} );
				// submit of payment input buttons:
				$('input.cbpaidjsSubmit').click( function() { $(this.form).submit(); } );
			},
			paidsubsPlanConditions : function( planId, plansReq, plansNotReq, fieldsReq, fieldsNotReq, field1, regexp1 ) {
				$.merge( cbpayPlansConds, [{ planId : planId, plansReq : plansReq, plansNotReq : plansNotReq, fieldsReq : fieldsReq, fieldsNotReq : fieldsNotReq, field1 : field1, regexp1 : regexp1 }] );
				for (var j=0; j<fieldsReq.length;j++) {
					var f = $('#cbfv_'+fieldsReq[j]+' input,#cbfv_'+fieldsReq[j]+' select');
					if ( ( f.attr( 'type' ) != 'checkbox' ) && ( f.attr( 'type' ) != 'radio' ) ) {
						f.change( paidsubsChange );
					} else {
						f.click( paidsubsChange );
					}
				}
				for (var j=0; j<fieldsNotReq.length;j++) {
					var f = $('#cbfv_'+fieldsNotReq[j]+' input,#cbfv_'+fieldsNotReq[j]+' select');
					if ( ( f.attr( 'type' ) != 'checkbox' ) && ( f.attr( 'type' ) != 'radio' ) ) {
						f.change( paidsubsChange );
					} else {
						f.click( paidsubsChange );
					}
				}
				if (field1 > 0) {
					var f = $('#cbfv_'+field1+' input,#cbfv_'+field1+' select');
					if ( ( f.attr( 'type' ) != 'checkbox' ) && ( f.attr( 'type' ) != 'radio' ) ) {
						f.change( paidsubsChange );
					} else {
						f.click( paidsubsChange );
					}
				}
			}
		}
	});
})(jQuery);
