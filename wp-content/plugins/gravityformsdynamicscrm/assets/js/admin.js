/*global jQuery, document, ajaxurl */
(function($) {
	"use strict";

	var GFDCRM = {

		message: '',
		license_field: $('#license_key'),
		activate_button : $( '[data-edd_action=activate_license]' ),
		deactivate_button: $( '[data-edd_action=deactivate_license]' ),
		check_button: $( '[data-edd_action=check_license]' ),
		loading: $('#gfdcrm-loading'),
		crm_url: $('#dynamics_crm_url'),
		connect_button: $('#gform-connect-to-dynamics'),
		disconnect_button: $('#gform-disconnect-from-dynamics'),
		username_field: $('#username'),

		init: function() {

			GFDCRM.message_fadeout();
			GFDCRM.add_status_container();

			$( document )
				.on( 'ready keyup', GFDCRM.license_field, GFDCRM.key_change )
				.on( 'click', ".gfdcrm-edd-action", GFDCRM.clicked )
				.on( 'gfdcrm-edd-failed gv-edd-invalid', GFDCRM.failed )
				.on( 'gfdcrm-edd-valid', GFDCRM.valid )
				.on( 'gfdcrm-edd-deactivated', GFDCRM.deactivated )
				.on( 'gfdcrm-edd-inactive gv-edd-other', GFDCRM.other );
			GFDCRM.connect_button.click(GFDCRM.connect);
			GFDCRM.disconnect_button.click(GFDCRM.disconnect);

			if ( GFDCRM.disconnect_button.length > 0 || GFDCRM.username_field.length > 0 ) {
				var data = {
					action: 'gfdcrm_get_module_fields',
				};

				console.log('Retrieving entity metadata...');
				var $username_status = $( '#username-status' );
				var $password_status = $( '#password-status' );
				// $username_status
				// 	.find('i')
				// 	.removeClass('fa-check-circle')
				// 	.addClass('fa-spin');
				// $password_status
				// 	.find('i')
				// 	.removeClass('fa-check-circle')
				// 	.addClass('fa-spin');
				var jqxhr = $.post(ajaxurl, data, function(response) {
					// $username_status
					// 	.find('i')
					// 	.removeClass('fa-spin')
					// 	.addClass('fa-check-circle');
					// $password_status
					// 	.find('i')
					// 	.removeClass('fa-spin')
					// 	.addClass('fa-check-circle');
					// $username_status.attr('title', '<h6>' + GFDCRMGlobals.tooltips.valid_credentials.title + '</h6>' + GFDCRMGlobals.tooltips.valid_credentials.text);
					// $password_status.attr('title', '<h6>' + GFDCRMGlobals.tooltips.valid_credentials.title + '</h6>' + GFDCRMGlobals.tooltips.valid_credentials.text);
					// gform_initialize_tooltips();
					console.log('Retrieval of entity metadata complete.');
				});
				// .fail(function() {
			 //    	$username_status
				// 		.find('i')
				// 		.removeClass('fa-spin gf_invalid')
				// 		.addClass('fa-exclamation-circle gf_invalid');
				// 	$password_status
				// 		.find('i')
				// 		.removeClass('fa-spin gf_valid')
				// 		.addClass('fa-exclamation-circle gf_invalid');	
				// 	$username_status.attr('title', '<h6>' + GFDCRMGlobals.tooltips.invalid_credentials.title + '</h6>' + GFDCRMGlobals.tooltips.invalid_credentials.text);
				// 	$password_status.attr('title', '<h6>' + GFDCRMGlobals.tooltips.invalid_credentials.title + '</h6>' + GFDCRMGlobals.tooltips.invalid_credentials.text);
				// });

			}

		}, //end function init

		/**
		 * Hide the "Settings Updated" message after save
		 */
		message_fadeout: function() {
			setTimeout( function() {
				$('#gform_tab_group #message' ).fadeOut();
			}, 2000 );
		},

		add_status_container: function() {
			$( GFDCRMGlobals.license_box ).insertBefore( GFDCRM.license_field );
		},

		/**
		 * When the license key changes, change the button visibility
		 * @todo refactor- no need having this, plus all the separate methods
		 * @param e
		 */
		key_change: function( e ) {

			//return;
			var license_key = $('#license_key').val();

			var showbuttons = false;
			var hidebuttons = false;

			//buttons.show();

			if (license_key.length > 0) {

				switch( $('#license_key_status' ).val() ) {
					case 'valid':
						hidebuttons = $('[data-edd_action=activate_license]' );
						showbuttons = $('[data-edd_action=deactivate_license],[data-edd_action=check_license]' );
						break;
					case 'deactivated':
					case 'site_inactive':
					default:
						hidebuttons = $('[data-edd_action=deactivate_license]' );
						showbuttons = $('[data-edd_action=activate_license],[data-edd_action=check_license]' );
						break;
				}
			} else if ( license_key.length === 0 ) {
				hidebuttons = $('[data-edd_action*=_license]');
			}

			// On load, no animation. Otherwise, 100ms
			var speed = ( e.type === 'ready' ) ? 0 : 'fast';

			if( hidebuttons ) {
				hidebuttons.filter(':visible').fadeOut( speed );
			}
			if( showbuttons ) {
				showbuttons.filter( ':hidden' ).removeClass( 'hide' ).hide().fadeIn( speed );
			}
		},

		/**
		 * Show the HTML of the message
		 * @param message HTML for new status
		 */
		update_status: function( message ) {
			if( message !== '' ) {
				$( '#gfdcrm-edd-status' ).replaceWith( message );
			}
		},

		set_pending_message: function( message ) {
			var $gfdcrm_edd_status = $( '#gfdcrm-edd-status' );

			$gfdcrm_edd_status
				.find('span')
				.attr('title', '<h6>' + GFDCRMGlobals.tooltips.validating_license_key.title + '</h6>' + GFDCRMGlobals.tooltips.validating_license_key.text);
			gform_initialize_tooltips();

			$gfdcrm_edd_status
				.addClass('pending')
				.find('i')
				.removeClass('fa-check-circle')
				.addClass('fa-refresh fa-spin');
		},

		clicked: function( e ) {
			e.preventDefault();

			var $that = $( this );

			var theData = {
				license: $('#license_key').val(),
				edd_action: $that.attr( 'data-edd_action' ),
				field_id: $that.attr( 'id' ),
			};

			$that.not( GFDCRM.check_button ).addClass('button-disabled');

			GFDCRM.wait();

			GFDCRM.set_pending_message( $that.attr('data-pending_text') );

			GFDCRM.post_data( theData );

		},

		popupCenter: function(pageURL, title,w,h) {
			var left = (screen.width/2)-(w/2);
			var top = (screen.height/2)-(h/2);
			var targetWin = window.open (pageURL, title, 'toolbar=no, location=no, directories=no, status=no, menubar=no, scrollbars=no, resizable=no, copyhistory=no, width='+w+', height='+h+', top='+top+', left='+left);
			return targetWin;
		},

		connect: function(e) {
			console.log('connect');
			e.preventDefault();

			GFDCRM.wait();

			var url = $(this).attr('href');
			var login_window = GFDCRM.popupCenter(url, "Dynamics CRM Login", 640, 480),
				auth_poll = null;

			auth_poll = setInterval(function() {
				if (login_window.closed) {
					clearInterval(auth_poll);
					window.location.reload();
				}
			}, 100);
		},

		disconnect: function(e) {
			console.log('disconnect');
			e.preventDefault();

			GFDCRM.wait();

			var data = {
				action: 'gfdcrm_disconnect',
			};

			$.post(ajaxurl, data, function(response) {
				var data = JSON.parse(response);
				window.location.reload();
			});
		},

		wait: function() {
			$( '#gform-settings')
				.css('cursor', 'wait')
					.find('.button')
					.css('cursor', 'wait')
					.find('input')
					.css('cursor', 'wait');
		},

		stop: function() {
			$( '#gform-settings')
				.css('cursor', 'default')
					.find('.button')
					.css('cursor', 'pointer')
					.find('input')
					.css('cursor', 'pointer');
		},

		/**
		 * Take a string that may be JSON or may be JSON
		 *
		 * @since 1.12
		 * @param {string} string JSON text to attempt to parse
		 * @returns {object} Either JSON-parsed object or object with a message key containing an error message
		 */
		parse_response_json: function( string ) {
			var response_object;

			// Parse valid JSON
			try {

				response_object = $.parseJSON( string );

			} catch( exception ) {

				// The JSON didn't parse most likely because PHP warnings.
				// We attempt to strip out all content up to the expected JSON `{"`
				var second_try = string.replace(/((.|\n)+?){"/gm, "{\"");

				try {

					response_object = $.parseJSON( second_try );

				} catch( exception ) {

					console.log( '*** \n*** \n*** Error-causing response:\n***\n***\n', string );

					var error_message = 'JSON failed: another plugin caused a conflict with completing this request. Check your browser\'s Javascript console to view the invalid content.';

					response_object = {
						message: '<div id="gv-edd-status" class="gv-edd-message inline error"><p>' + error_message + '</p></div>'
					};
				}
			}

			return response_object;
		},

		post_data: function( theData ) {

			$.post( ajaxurl, {
				'action': 'gravityformsdynamicscrm_license',
				'data': theData
			}, function ( response ) {

				var response_object = GFDCRM.parse_response_json( response );

				GFDCRM.message = response_object.message;

				if( theData.edd_action !== 'check_license' ) {
					$( '#license_key_status' ).val( response_object.license );
					$( '#license_key_response' ).val( JSON.stringify( response_object ) );
					$( document ).trigger( 'gfdcrm-edd-' + response_object.license, response_object );
				}

				GFDCRM.update_status( response_object.message );
				gform_initialize_tooltips();

				GFDCRM.stop();
			} );

		},

		valid: function( e ) {
			GFDCRM.activate_button
				.fadeOut( 'medium', function () {
					GFDCRM.activate_button.removeClass( 'button-disabled' );
					GFDCRM.deactivate_button.fadeIn().css( "display", "inline-block" );
				} );
		},

		failed: function( e ) {
			GFDCRM.deactivate_button.removeClass( 'button-disabled' );
			GFDCRM.activate_button.removeClass( 'button-disabled' );
		},

		deactivated: function( e ) {
			GFDCRM.deactivate_button
				.css('min-width', function() {
					return $(this ).width();
				})
				.fadeOut( 'medium', function () {
					GFDCRM.deactivate_button.removeClass( 'button-disabled' );
					GFDCRM.activate_button.fadeIn(function() {
						$(this).css( "display", "inline-block" );
					});
				} );

		},

		other: function( e ) {
			GFDCRM.deactivate_button.fadeOut( 'medium', function () {
				GFDCRM.activate_button
					.removeClass( 'button-disabled' )
					.fadeIn()
					.css( "display", "inline-block" );
			} );
		}

	};

	GFDCRM.init();

})(jQuery);