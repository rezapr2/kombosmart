/**
 * Exchange Rate Pricing admin: product edit screen, products list quick edit and
 * the settings page recalculation progress.
 *
 * @package ExchangeRatePricing
 */
( function ( $, config ) {
	'use strict';

	if ( ! config ) {
		return;
	}

	function debounce( callback, wait ) {
		var timer;

		return function () {
			var context = this;
			var args = arguments;

			clearTimeout( timer );
			timer = setTimeout( function () {
				callback.apply( context, args );
			}, wait );
		};
	}

	function isForeignMode( mode ) {
		return ( mode || config.defaultMode ) === 'foreign';
	}

	function localize( value ) {
		return value === '' || value === null || value === undefined ? '' : String( value ).replace( '.', config.decimalSep );
	}

	/**
	 * Product edit screen: show fields for the selected mode, lock WooCommerce's
	 * price inputs and preview the calculated store price.
	 */
	var ProductScreen = {
		init: function () {
			this.$mode = $( '#erpfw_mode' );

			if ( ! this.$mode.length ) {
				return;
			}

			var self = this;
			var refresh = debounce( function () {
				self.preview();
			}, 400 );

			this.productId = parseInt( $( '#post_ID' ).val(), 10 );

			this.$mode.on( 'change', function () {
				self.toggle();
				refresh();
			} );
			$( '#product-type' ).on( 'change', function () {
				self.toggle();
				refresh();
			} );
			$( document.body ).on( 'input change', '.erpfw-options input, .erpfw-price-fields input, .erpfw-variation-fields input', refresh );
			$( '#product_catchecklist' ).on( 'change', 'input', refresh );
			$( '#woocommerce-product-data' ).on( 'woocommerce_variations_loaded woocommerce_variations_added', function () {
				self.toggle();
				self.preview();
			} );

			this.toggle();
			this.preview();
		},

		isForeign: function () {
			return isForeignMode( this.$mode.val() );
		},

		toggle: function () {
			var foreign = this.isForeign();
			var $native = $( '#_regular_price, #_sale_price' ).add(
				'#variable_product_options input[name^="variable_regular_price"], #variable_product_options input[name^="variable_sale_price"]'
			);

			$( '.erpfw-foreign-only' ).toggle( foreign );
			$native
				.prop( 'readonly', foreign )
				.toggleClass( 'erpfw-locked', foreign )
				.attr( 'title', foreign ? config.i18n.locked : null );
		},

		items: function () {
			var items = {};

			if ( $( '#product-type' ).val() === 'variable' ) {
				$( '#variable_product_options .woocommerce_variation' ).each( function () {
					var $variation = $( this );
					var $id = $variation.find( 'input[name^="variable_post_id"]' );
					var match = ( $id.attr( 'name' ) || '' ).match( /\[(\d+)\]/ );

					if ( ! match ) {
						return;
					}

					var loop = match[ 1 ];

					items[ 'v' + loop ] = {
						id: $id.val(),
						regular: $variation.find( '[name="erpfw_variable_regular_price[' + loop + ']"]' ).val() || '',
						sale: $variation.find( '[name="erpfw_variable_sale_price[' + loop + ']"]' ).val() || '',
						sale_percent: $variation.find( '[name="erpfw_variable_sale_percent[' + loop + ']"]' ).val() || '',
					};
				} );
			} else {
				items.simple = {
					id: this.productId,
					regular: $( '#erpfw_regular_price' ).val() || '',
					sale: $( '#erpfw_sale_price' ).val() || '',
					sale_percent: $( '#erpfw_sale_percent' ).val() || '',
				};
			}

			return items;
		},

		preview: function () {
			var self = this;
			var items = this.items();

			if ( ! this.isForeign() || $.isEmptyObject( items ) ) {
				return;
			}

			var data = {
				action: 'erpfw_preview',
				nonce: config.nonce,
				product_id: this.productId,
				markup_percent: $( '#erpfw_markup_percent' ).val() || '',
				markup_fixed: $( '#erpfw_markup_fixed' ).val() || '',
				items: items,
			};
			var $categories = $( '#product_catchecklist input:checked' );

			if ( $categories.length ) {
				data.category_ids = $categories.map( function () {
					return this.value;
				} ).get();
			}

			$( '.erpfw-preview__value' ).text( config.i18n.calculating );

			if ( this.request ) {
				this.request.abort();
			}

			this.request = $.post( config.ajaxUrl, data )
				.done( function ( response ) {
					if ( ! response || ! response.success ) {
						$( '.erpfw-preview__value' ).text( ( response && response.data && response.data.message ) || config.i18n.failed );
						$( '.erpfw-preview__details' ).text( '' );
						return;
					}

					$.each( response.data.items, function ( key, item ) {
						self.render( key, item );
					} );
				} )
				.fail( function ( xhr, status ) {
					if ( status !== 'abort' ) {
						$( '.erpfw-preview__value' ).text( config.i18n.failed );
					}
				} );
		},

		render: function ( key, item ) {
			var $preview, $regular, $sale;

			if ( key === 'simple' ) {
				$preview = $( '.erpfw-preview[data-erpfw-preview="simple"]' );
				$regular = $( '#_regular_price' );
				$sale = $( '#_sale_price' );
			} else {
				var loop = key.slice( 1 );

				$preview = $( '.erpfw-preview[data-loop="' + loop + '"]' );
				$regular = $( '[name="variable_regular_price[' + loop + ']"]' );
				$sale = $( '[name="variable_sale_price[' + loop + ']"]' );
			}

			$preview.find( '.erpfw-preview__value' ).text( item.text );
			$preview.find( '.erpfw-preview__details' ).text( item.details || '' );

			// Mirror the result in WooCommerce's (locked) price fields; the server recalculates on save anyway.
			if ( item.status === 'ok' ) {
				$regular.val( localize( item.regular ) );
				$sale.val( localize( item.sale ) );
			}
		},
	};

	/**
	 * Products list: fill and lock the quick edit fields.
	 */
	var QuickEdit = {
		init: function () {
			if ( typeof window.inlineEditPost === 'undefined' || ! $( '#the-list' ).length ) {
				return;
			}

			var original = window.inlineEditPost.edit;

			window.inlineEditPost.edit = function ( id ) {
				original.apply( this, arguments );

				var postId = typeof id === 'object' ? parseInt( this.getId( id ), 10 ) : parseInt( id, 10 );

				if ( postId ) {
					QuickEdit.fill( postId );
				}
			};
		},

		fill: function ( postId ) {
			var $row = $( '#edit-' + postId );
			var $box = $row.find( '.erpfw-quick-edit' );
			var $data = $( '#post-' + postId ).find( '.erpfw-inline' );

			if ( ! $data.length ) {
				$box.hide();
				return;
			}

			$box.show();
			$row.find( '[name="erpfw_mode"]' ).val( $data.attr( 'data-mode' ) || '' );
			$row.find( '[name="erpfw_regular_price"]' ).val( $data.attr( 'data-regular' ) || '' );
			$row.find( '[name="erpfw_sale_price"]' ).val( $data.attr( 'data-sale' ) || '' );
			$row.find( '[name="erpfw_sale_percent"]' ).val( $data.attr( 'data-percent' ) || '' );
			$row.find( '.erpfw-quick-prices' ).toggle( $data.attr( 'data-type' ) !== 'variable' );

			var lock = function () {
				var foreign = isForeignMode( $row.find( '[name="erpfw_mode"]' ).val() );

				$row.find( 'input[name="_regular_price"], input[name="_sale_price"]' )
					.prop( 'readonly', foreign )
					.toggleClass( 'erpfw-locked', foreign );
				$row.find( '.erpfw-quick-prices' ).toggleClass( 'is-disabled', ! foreign );
			};

			$row.find( '[name="erpfw_mode"]' ).off( 'change.erpfw' ).on( 'change.erpfw', lock );
			lock();
		},
	};

	/**
	 * Settings page: start a recalculation and show its progress.
	 */
	var Recalc = {
		init: function () {
			this.$box = $( '.erpfw-recalc' );

			if ( ! this.$box.length ) {
				return;
			}

			var self = this;

			this.$button = this.$box.find( '.erpfw-recalc__start' );
			this.$button.on( 'click', function ( event ) {
				event.preventDefault();
				self.request( 'erpfw_recalculate' );
			} );

			if ( this.$box.attr( 'data-status' ) === 'running' ) {
				this.request( 'erpfw_recalc_status' );
			}
		},

		request: function ( action ) {
			var self = this;

			this.$button.prop( 'disabled', true );

			$.post( config.ajaxUrl, { action: action, nonce: config.nonce } )
				.done( function ( response ) {
					if ( ! response || ! response.success ) {
						self.$button.prop( 'disabled', false );
						return;
					}

					self.update( response.data );

					if ( response.data.status === 'running' ) {
						setTimeout( function () {
							self.request( 'erpfw_recalc_status' );
						}, 1500 );
					} else {
						self.$button.prop( 'disabled', false );
					}
				} )
				.fail( function () {
					setTimeout( function () {
						self.request( 'erpfw_recalc_status' );
					}, 5000 );
				} );
		},

		update: function ( state ) {
			this.$box.attr( 'data-status', state.status );
			this.$box.find( '.erpfw-recalc__summary' ).text( state.summary );
			this.$box.find( '.erpfw-progress' ).prop( 'hidden', state.status !== 'running' );
			this.$box.find( '.erpfw-progress__bar' ).css( 'width', state.percent + '%' );
		},
	};

	$( function () {
		ProductScreen.init();
		QuickEdit.init();
		Recalc.init();
	} );
} )( jQuery, window.erpfwAdmin );
