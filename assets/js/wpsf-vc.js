;( function ($, window, document) {
	'use strict';

	$.WPSF_VC_TYPES = {
		checkbox: function ($type, $parent) {
			var $checked = $parent.find( "input:checked" );
			var $save = [];
			if ( $type === 'key_value_multi_array' ) {
				$save = {};
			}
			$.each( $checked, function () {
				if ( $type === 'array' ) {
					$save.push( $( this ).val() );
				} else if ( $type === 'key_value_multi_array' ) {
					var $g = $( this ).data( 'group' );
					if ( $save[ $g ] === undefined ) {
						$save[ $g ] = [];
					}

					$save[ $g ].push( $( this ).val() );
				}

			} );
			$.WPSF_VC_HELPER.save( $parent, $save, $type );
		},

		elem_to_save: function ($parent, $element, $type) {
			var $parentKey = $element.inputArrayKey( 'name' );
			if ( $type === undefined ) {
				$type = 'key_value_array';
			}
			var $values = $parent.find( "> .wpsf-fieldset :input" ).inputToArray( {value: true} );

			if ( $values[ $parentKey ] !== undefined ) {
				$.WPSF_VC_HELPER.save( $parent, $values[ $parentKey ], $type );
			}
		},

		is_vc_param_elem: function ($parent) {
			if ( $parent.data( 'param-name' ) === undefined || $parent.data( 'param-name' ) === '' ) {
				return false;
			}
			return true;
		}
	};

	$.fn.WPSF_VC_LINK = function () {
		return this.each( function () {
			if ( $.WPSF_VC_TYPES.is_vc_param_elem( $( this ) ) === true ) {
				var $parent = $( this );
				$.WPSF_VC_TYPES.elem_to_save( $parent, $parent.find( '> .wpsf-fieldset :input' ) );
				$parent.on( "wpsf-links-updated", function () {
					$.WPSF_VC_TYPES.elem_to_save( $parent, $parent.find( '> .wpsf-fieldset :input' ) );
				} );
			}
		} )
	};

	$.fn.WPSF_KEY_VALUE_ARRAY = function () {
		return this.each( function () {
			if ( $.WPSF_VC_TYPES.is_vc_param_elem( $( this ) ) === true ) {
				var $parent = $( this );
				$.WPSF_VC_TYPES.elem_to_save( $parent, $parent.find( "> .wpsf-fieldset :input" ) );
				$parent.find( ":input" ).on( 'change', function () {
					$.WPSF_VC_TYPES.elem_to_save( $parent, $( this ) );
				} )
			}
		} );
	};

	$.fn.WPSF_VC_CHECKBOX = function () {
		return this.each( function () {
			if ( $.WPSF_VC_TYPES.is_vc_param_elem( $( this ) ) === true ) {
				var $parent = $( this );
				if ( ( $parent.find( "input" ).length > 1 || $parent.find( "input" ).length > 0 ) && $parent.find( "ul" ).length > 0 ) {
					var $type = 'array';
					if ( $parent.find( 'ul' ).length === 1 ) {
						$type = 'array';
					} else if ( $parent.find( 'ul' ).length > 1 ) {
						$type = 'key_value_multi_array';
					}
					$parent.find( "input" ).on( 'change', function () {
						$.WPSF_VC_TYPES.checkbox( $type, $parent )
					} );

					$.WPSF_VC_TYPES.checkbox( $type, $parent );
				} else {
					var $val = $parent.find( "input" ).attr( 'value' );
					$parent.find( "input" ).attr( 'data-orgval', $val );
					$parent.find( "input" ).on( 'change', function () {
						if ( $( this ).is( ":checked" ) ) {
							$( this ).val( $( this ).attr( 'data-orgval' ) );
						} else {
							$( this ).val( 'false' );
						}
					} );
				}
			}
		} )
	};

	$.fn.WPSF_VC_SELECT = function () {
		return this.each( function () {
			if ( $.WPSF_VC_TYPES.is_vc_param_elem( $( this ) ) === true ) {
				var $parent = $( this );
				if ( $parent.hasClass( 'wpsf-element-select-multiple' ) || $parent.hasClass( 'wpsf-element-select-multiple-chosen' ) ) {
					$parent.find( 'select' ).each( function () {
						$.WPSF_VC_HELPER.save( $parent, $( this ).val(), 'array' );
						$( this ).on( 'change', function () {
							var $save = $( this ).val();
							$.WPSF_VC_HELPER.save( $parent, $save, 'array' );
						} );
					} );
				}
			}
		} )
	};

	$.fn.WPSF_VC_SORTER = function () {
		return this.each( function () {
			if ( $.WPSF_VC_TYPES.is_vc_param_elem( $( this ) ) === true ) {
				var $parent = $( this );
				$.WPSF_VC_TYPES.elem_to_save( $parent, $parent.find( "> .wpsf-fieldset :input" ), 'sorter_values' );
				$parent.on( 'wpsf-sorter-updated', function () {
					$.WPSF_VC_TYPES.elem_to_save( $parent, $parent.find( "> .wpsf-fieldset :input" ), 'sorter_values' );
				} )
			}
		} )
	};

	$.fn.WPSF_VC_FIELDSET = function () {
		return this.each( function () {
			if ( $.WPSF_VC_TYPES.is_vc_param_elem( $( this ) ) === true ) {
				var $parent = $( this );
				$.WPSF_VC_TYPES.elem_to_save( $parent, $parent.find( ".wpsf-fieldset :input" ), 'sorter_values' );

				$parent.find( "> .wpsf-fieldset :input" ).on( 'change', function () {
					$.WPSF_VC_TYPES.elem_to_save( $parent, $parent.find( ".wpsf-fieldset :input" ), 'sorter_values' );
				} );


				$parent.find( "> .wpsf-fieldset :input" ).on( 'blur', function () {
					$.WPSF_VC_TYPES.elem_to_save( $parent, $parent.find( ".wpsf-fieldset :input" ), 'sorter_values' );
				} );
			}
		} )
	};

	$.fn.WPSF_VC_GROUP = function () {
		return this.each( function () {
			if ( $.WPSF_VC_TYPES.is_vc_param_elem( $( this ) ) === true ) {
				var $parent = $( this );
				$.WPSF_VC_TYPES.elem_to_save( $parent, $parent.find( ":input" ), 'sorter_values' );

				$parent.find( ":input" ).on( 'change', function () {
					console.log( 'h' );
					$.WPSF_VC_TYPES.elem_to_save( $parent, $parent.find( ":input" ), 'sorter_values' );
				} );


				$parent.find( ":input" ).on( 'blur', function () {
					$.WPSF_VC_TYPES.elem_to_save( $parent, $parent.find( ":input" ), 'sorter_values' );
				} );
			}
		} )
	};

	$.WPSF_VC_HELPER = {
		vc_popup: $( '.wpb_edit_form_elements.vc_edit_form_elements' ),

		save: function ($parent, $save_data, $type) {
			if ( $save_data === null ) {
				return;
			}
			var $param_name = $parent.data( 'param-name' );
			var $value = '';

			if ( $save_data !== '' ) {
				if ( typeof $save_data === 'object' && $type === 'array' ) {
					$value = $.WPSF_VC_HELPER.simple_array( $save_data );
				} else if ( typeof $save_data === 'object' && $type === 'key_value_array' ) {
					$value = $.WPSF_VC_HELPER.key_value_array( $save_data );
				} else if ( typeof $save_data === 'object' && $type === 'key_value_multi_array' ) {
					$value = $.WPSF_VC_HELPER.key_value_multi_array( $save_data );
				} else if ( typeof $save_data === 'object' && $type === 'sorter_values' ) {
					$value = $.WPSF_VC_HELPER.sorter_values( $save_data );
				}
			}

			$.WPSF_VC_HELPER.vc_save( $param_name, $value );
		},

		vc_save: function ($param_name, $value) {
			var $html = '<div id="wpsf-settings" class="hidden" style="display: none;visibility: hidden;" ></div>';
			var $wrap = $.WPSF_VC_HELPER.vc_popup;

			if ( $wrap.parent().find( "div#wpsf-settings" ).length === 0 ) {
				$wrap.parent().append( $html );
			}

			if ( $wrap.parent().find( "div#wpsf-settings" ).length === 1 ) {
				var $parent = $wrap.parent().find( "div#wpsf-settings" );
				if ( $parent.find( "> #" + $param_name + '.wpb_vc_param_value' ).length === 0 ) {
					$parent.append( $( '<input type="hidden" value="" id="' + $param_name + '" name="' + $param_name + '" class="wpb_vc_param_value" />' ) );
				}

				$parent.find( "> #" + $param_name + '.wpb_vc_param_value' ).val( $value );

				return true;
			}

			return false;
		},

		simple_array: function ($save_data) {
			return $save_data.join( ',' );
		},

		key_value_array: function ($save_data) {
			var $r = [];
			$.each( $save_data, function ($k, $v) {
				var $s = $k + ":" + $v;
				$r.push( $s );
			} );
			return $r.join( '|' );
		},

		key_value_multi_array: function ($save_data) {
			var $r = [];
			$.each( $save_data, function ($k, $v) {
				if ( typeof $v === 'object' && typeof $v === 'array' ) {
					$v = $v.join( ',' );
				}
				var $s = $k + ":" + $v;
				$r.push( $s );
			} );
			return $r.join( '|' );
		},

		sorter_values: function ($save_data) {
			return $.WPSF_VC_HELPER.encodeContent( JSON.stringify( $save_data ) );
			var $r = {enabled: [], disabled: []};
			$.each( $save_data, function ($key, $val) {
				if ( $val !== '' && typeof  $val === 'object' ) {
					$.each( $val, function ($k, $v) {
						$r[ $key ].push( $k + ":" + $v );
					} )
				}
			} );
			return $.WPSF_VC_HELPER.key_value_multi_array( $r );
		},

		rawurlencode: function (str) {
			str = ( str + '' ).toString();
			return encodeURIComponent( str ).replace( /!/g, '%21' ).replace( /'/g, '%27' ).replace( /\(/g, '%28' ).replace( /\)/g, '%29' ).replace( /\*/g, '%2A' );
		},

		utf8_encode: function (argString) {
			if ( argString === null || typeof argString === "undefined" ) {
				return "";
			}
			var string = ( argString + '' );
			var utftext = "",
				start, end, stringl = 0;
			start = end = 0;
			stringl = string.length;
			for ( var n = 0; n < stringl; n++ ) {
				var c1 = string.charCodeAt( n );
				var enc = null;
				if ( c1 < 128 ) {
					end++;
				} else if ( c1 > 127 && c1 < 2048 ) {
					enc = String.fromCharCode( ( c1 >> 6 ) | 192 ) + String.fromCharCode( ( c1 & 63 ) | 128 );
				} else {
					enc = String.fromCharCode( ( c1 >> 12 ) | 224 ) + String.fromCharCode( ( ( c1 >> 6 ) & 63 ) | 128 ) + String.fromCharCode( ( c1 & 63 ) | 128 );
				}
				if ( enc !== null ) {
					if ( end > start ) {
						utftext += string.slice( start, end );
					}
					utftext += enc;
					start = end = n + 1;
				}
			}
			if ( end > start ) {
				utftext += string.slice( start, stringl );
			}
			return utftext;
		},

		base64_encode: function (data) {
			var b64 = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/=";
			var o1, o2, o3, h1, h2, h3, h4, bits, i = 0,
				ac = 0,
				enc = "",
				tmp_arr = [];
			if ( !data ) {
				return data;
			}
			data = $.WPSF_VC_HELPER.utf8_encode( data + '' );
			do {
				o1 = data.charCodeAt( i++ );
				o2 = data.charCodeAt( i++ );
				o3 = data.charCodeAt( i++ );
				bits = o1 << 16 | o2 << 8 | o3;
				h1 = bits >> 18 & 0x3f;
				h2 = bits >> 12 & 0x3f;
				h3 = bits >> 6 & 0x3f;
				h4 = bits & 0x3f;
				tmp_arr[ ac++ ] = b64.charAt( h1 ) + b64.charAt( h2 ) + b64.charAt( h3 ) + b64.charAt( h4 );
			} while ( i < data.length );
			enc = tmp_arr.join( '' );
			var r = data.length % 3;
			return ( r ? enc.slice( 0, r - 3 ) : enc ) + '==='.slice( r || 3 );
		},

		encodeContent: function (value) {
			return $.WPSF_VC_HELPER.base64_encode( $.WPSF_VC_HELPER.rawurlencode( value ) );
		}
	};

	$.WPSF_VC = {
		el: $( ".wpsf-framework.wpsf-vc-framework" ),
		reload: function () {
			var $el = $.WPSF_VC.el;
			$el.find( ".wpsf-field-checkbox" ).WPSF_VC_CHECKBOX();
			$el.find( '.wpsf-field-radio' ).WPSF_VC_CHECKBOX();
			$el.find( '.wpsf-field-switcher' ).WPSF_VC_CHECKBOX();
			$el.find( '.wpsf-field-image_select' ).WPSF_VC_CHECKBOX();
			$el.find( '.wpsf-field-color_scheme' ).WPSF_VC_CHECKBOX();
			$el.find( '.wpsf-field-select' ).WPSF_VC_SELECT();
			$el.find( '.wpsf-field-background' ).WPSF_KEY_VALUE_ARRAY();
			$el.find( '.wpsf-field-typography' ).WPSF_KEY_VALUE_ARRAY();
			$el.find( '.wpsf-field-image_size' ).WPSF_KEY_VALUE_ARRAY();
			$el.find( '.wpsf-field-sorter' ).WPSF_VC_SORTER();
			$el.find( '.wpsf-field-fieldset' ).WPSF_VC_FIELDSET();
			$el.find( '.wpsf-field-accordion' ).WPSF_VC_FIELDSET();
			$el.find( '.wpsf-field-tab' ).WPSF_VC_FIELDSET();
			$el.find( '.wpsf-field-social_icons' ).WPSF_VC_FIELDSET();
			$el.find( '.wpsf-field-css_builder' ).WPSF_VC_FIELDSET();
			$el.find( '.wpsf-field-group' ).WPSF_VC_GROUP();
			$el.find( '.wpsf-field-links' ).WPSF_VC_LINK();

		},
		work: function () {
			var $elem = $.WPSF_VC.el;
			$.WPSF.icons_manager();
			$.WPSF.shortcode_manager();
			$.WPSF.widget_reload();
			$elem.WPSF_DEPENDENCY();
			$elem.WPSF_RELOAD();
			$elem.find( '.wpsf-field-group' ).WPSF_GROUP();
			$.WPSF_VC.reload();
		}
	};

	$.WPSF_VC.work();

} )( jQuery, window, document );