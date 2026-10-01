/**
 * Quick exchange rate update from the dashboard widget and the admin bar.
 *
 * @package RateMint
 */
( function () {
	'use strict';

	var config = window.erpfwRate;

	if ( ! config || ! window.fetch || ! window.FormData ) {
		return; // The form still works without JavaScript through admin-post.php.
	}

	function setText( selector, text ) {
		document.querySelectorAll( selector ).forEach( function ( element ) {
			element.textContent = text;
		} );
	}

	document.addEventListener( 'submit', function ( event ) {
		var form = event.target;

		if ( ! form.classList || ! form.classList.contains( 'erpfw-rate-form' ) ) {
			return;
		}

		event.preventDefault();

		var message = form.querySelector( '.erpfw-rate-form__message' );
		var button = form.querySelector( 'button[type="submit"]' );
		var data = new FormData( form );

		data.set( 'action', 'erpfw_update_rate' );

		if ( button ) {
			button.disabled = true;
		}

		if ( message ) {
			message.textContent = config.i18n.saving;
		}

		window
			.fetch( config.ajaxUrl, { method: 'POST', credentials: 'same-origin', body: data } )
			.then( function ( response ) {
				return response.json();
			} )
			.then( function ( response ) {
				var result = response && response.data ? response.data : {};

				if ( message ) {
					message.textContent = result.message || ( response && response.success ? '' : config.i18n.error );
				}

				if ( ! response || ! response.success ) {
					return;
				}

				setText( '.erpfw-rate-label', result.label );
				setText( '.erpfw-rate-current', result.rate_text );
				setText( '.erpfw-rate-updated', result.updated_text );

				document.querySelectorAll( '.erpfw-rate-form input[name="rate"]' ).forEach( function ( input ) {
					input.value = result.rate_input;
				} );
				document.querySelectorAll( '.erpfw-rate-updated.is-stale' ).forEach( function ( element ) {
					element.classList.remove( 'is-stale' );
				} );

				var barItem = document.getElementById( 'wp-admin-bar-erpfw-rate' );

				if ( barItem ) {
					barItem.classList.remove( 'erpfw-stale' );
				}
			} )
			.catch( function () {
				if ( message ) {
					message.textContent = config.i18n.error;
				}
			} )
			.then( function () {
				if ( button ) {
					button.disabled = false;
				}
			} );
	} );
} )();
