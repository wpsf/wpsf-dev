;( function ($) {
    'use strict';

    function vsAttrToArray() {

        this.set_defaults = function () {
            this.key = [];
            this.array = null;
            this.element = null;
            this.val_req = false;
            this.elem_key = null;
        };

        this.set_key = function ($key) {
            var $this = this;
            if ( $key !== '' && typeof $key === 'object' ) {
                $.each($key, function ($k, $v) {
                    $this.key.push($v);
                })
            }
        };

        this.render_array = function () {
            var $this = this;
            $.each($this.key, function ($key, $value) {
                $this.array = $this.hook_array($key, $value, $this.array);
            });
            return $this.array;
        };

        this.element_array = function () {
            var $elem = this.element.attr(this.elem_key);
            if ( $elem !== undefined || $elem !== '' || $elem !== false ) {
                var $regex = /\[]/g;
                var $m = null;
                if ( ( $m = $regex.exec($elem) ) !== null ) {
                    if ( $m.length === 1 ) {
                        if ( $m[0] === '[]' ) {
                            return true;
                        }
                    }
                }
            }
            return false;

        };

        this.get_value = function () {
            var $this = this;
            var $value = null;

            if ( $this.element.is('input[type=checkbox]') || $this.element.is('input[type=radio]') ) {
                $value = ( $this.element.is(":checked") ) ? $this.element.val() : false;
            } else if ( $this.element.is('textarea') ) {
                $value = $this.element.val();
            } else {
                $value = $this.element.val();
            }

            return ( this.element_array() === true && $value !== false ) ? [$value] : $value;
        };

        this.set_value = function ($arr, $key, $_value, $c_count) {
            var $value = this.get_value();
            if ( $arr[$key] === null ) {
                $arr[$key] = {};
            }
            if ( $value !== false ) {

                $arr[$key][$_value] = ( ( this.key.length - 1 ) === $c_count && this.val_req === true ) ? $value : null;
            }
            return $arr;
        };

        this.hook_array = function ($CK, $value, $arr) {
            if ( $arr === undefined ) {
                $arr = this.array;
            }


            var $this = this;

            if ( $arr === null ) {
                $arr = {};
                $arr[$value] = null;
            } else if ( typeof $arr === 'object' || typeof $arr === 'array' ) {
                $.each($arr, function ($key, $val) {
                    if ( $val === null ) {
                        $arr = $this.set_value($arr, $key, $value, $CK);

                    } else if ( typeof $val === 'object' || typeof $val === 'array' ) {
                        $arr[$key] = $this.hook_array($CK, $value, $arr[$key]);
                    }

                })
            }


            return $arr;
        };

        this.run_regex = function ($name) {
            var $regex = /\w+(?!\[)[\w&.\-]+\w+/g;
            var $m = null;
            var $this = this;

            while ( ( $m = $regex.exec($name) ) !== null ) {
                if ( $m.index === $regex.lastIndex ) {
                    $regex.lastIndex++;
                }
                $this.set_key($m);
            }

            return true;
        };

        this.get = function ($name, $element, $val, $elem_key) {
            var $this = this;
            $this.element = $element;
            $this.val_req = $val;
            $this.elem_key = $elem_key;
            this.run_regex($name);
            var $data = this.render_array();
            this.set_defaults();
            return $data;
        };

        this.get_key = function ($name) {
            this.run_regex($name);
            return ( this.key[0] !== undefined ) ? this.key[0] : null;
        };

        this.array_merge = function () {
            var args = Array.prototype.slice.call(arguments)
            var argl = args.length
            var arg
            var retObj = {}
            var k = ''
            var argil = 0
            var j = 0
            var i = 0
            var ct = 0
            var toStr = Object.prototype.toString
            var retArr = true

            for ( i = 0; i < argl; i++ ) {
                if ( toStr.call(args[i]) !== '[object Array]' ) {
                    retArr = false
                    break
                }
            }

            if ( retArr ) {
                retArr = []
                for ( i = 0; i < argl; i++ ) {
                    retArr = retArr.concat(args[i])
                }
                return retArr
            }

            for ( i = 0, ct = 0; i < argl; i++ ) {
                arg = args[i]
                if ( toStr.call(arg) === '[object Array]' ) {
                    for ( j = 0, argil = arg.length; j < argil; j++ ) {
                        retObj[ct++] = arg[j]
                    }
                } else {
                    for ( k in arg ) {
                        if ( arg.hasOwnProperty(k) ) {
                            if ( parseInt(k, 10) + '' === k ) {
                                retObj[ct++] = arg[k]
                            } else {
                                retObj[k] = arg[k]
                            }
                        }
                    }
                }
            }

            return retObj
        };

        this.array_merge_recursive = function (arr1, arr2) {
            var idx = '';
            if ( arr1 && Object.prototype.toString.call(arr1) === '[object Array]' &&
                arr2 && Object.prototype.toString.call(arr2) === '[object Array]' ) {
                for ( idx in arr2 ) {
                    arr1.push(arr2[idx])
                }
            } else if ( ( arr1 && ( arr1 instanceof Object ) ) && ( arr2 && ( arr2 instanceof Object ) ) ) {
                for ( idx in arr2 ) {
                    if ( idx in arr1 ) {
                        if ( typeof arr1[idx] === 'object' && typeof arr2 === 'object' ) {
                            arr1[idx] = this.array_merge_recursive(arr1[idx], arr2[idx]);
                        } else if ( typeof arr1[idx] === 'array' && typeof arr2 === 'array' ) {
                            arr1[idx] = this.array_merge(arr1[idx], arr2[idx]);
                        } else {
                            arr1[idx] = arr2[idx]
                        }
                    } else {
                        arr1[idx] = arr2[idx]
                    }
                }
            }
            return arr1
        }

        this.set_defaults();
    }

    $.fn.inputToArray = function ($options) {
        var $ary = {};
        var $settings = $.extend({
            key: 'name',
            value: true,
        }, $options);

        var $arr = new vsAttrToArray();
        this.each(function () {
            var $name = $(this).attr($settings.key);
            if ( $name !== undefined ) {
                var $r = $arr.get($name, $(this), $settings.value, $settings.key);
                $ary = $arr.array_merge_recursive($r, $ary);
            }
        });
        return $ary;

    };

    $.fn.inputArrayKey = function ($name) {
        if ( $name === undefined ) {
            $name = 'name';
        }
        var $name = $(this).attr($name);
        if ( $name === undefined ) {
            return false;
        }
        var $arr = new vsAttrToArray();
        return $arr.get_key($name);
    }

}(jQuery) );


;( function ($, window, document) {
    'use strict';

    $.WPSF_VC_TYPES = {
        checkbox: function ($type, $parent) {
            var $checked = $parent.find("input:checked");
            var $save = new Array();
            if ( $type === 'key_value_multi_array' ) {
                $save = {};
            }
            $.each($checked, function () {
                if ( $type === 'array' ) {
                    $save.push($(this).val());
                } else if ( $type === 'key_value_multi_array' ) {
                    var $g = $(this).data('group');
                    if ( $save[$g] === undefined ) {
                        $save[$g] = [];
                    }

                    $save[$g].push($(this).val());
                }

            });
            $.WPSF_VC_HELPER.save($parent, $save, $type);
        },

        elem_to_save: function ($parent, $element, $type) {
            var $parentKey = $element.inputArrayKey('name');
            if ( $type === undefined ) {
                $type = 'key_value_array';
            }
            var $values = $parent.find("> .wpsf-fieldset :input").inputToArray({value: true});

            if ( $values[$parentKey] !== undefined ) {
                $.WPSF_VC_HELPER.save($parent, $values[$parentKey], $type);
            }
        },

        is_vc_param_elem: function ($parent) {
            if ( $parent.data('param-name') === undefined || $parent.data('param-name') === '' ) {
                return false;
            }
            return true;
        }
    };

    $.fn.WPSF_VC_LINK = function () {
        return this.each(function () {
            if ( $.WPSF_VC_TYPES.is_vc_param_elem($(this)) === true ) {
                var $parent = $(this);
                $.WPSF_VC_TYPES.elem_to_save($parent, $parent.find('> .wpsf-fieldset :input'));
                $parent.on("wpsf-links-updated", function () {
                    $.WPSF_VC_TYPES.elem_to_save($parent, $parent.find('> .wpsf-fieldset :input'));
                });
            }
        })
    };

    $.fn.WPSF_KEY_VALUE_ARRAY = function () {
        return this.each(function () {
            if ( $.WPSF_VC_TYPES.is_vc_param_elem($(this)) === true ) {
                var $parent = $(this);
                $.WPSF_VC_TYPES.elem_to_save($parent, $parent.find("> .wpsf-fieldset :input"));
                $parent.find(":input").on('change', function () {
                    $.WPSF_VC_TYPES.elem_to_save($parent, $(this));
                })
            }
        });
    };

    $.fn.WPSF_VC_CHECKBOX = function () {
        return this.each(function () {
            if ( $.WPSF_VC_TYPES.is_vc_param_elem($(this)) === true ) {
                var $parent = $(this);
                if ( ( $parent.find("input").length > 1 || $parent.find("input").length > 0 ) && $parent.find("ul").length > 0 ) {
                    var $type = 'array';
                    if ( $parent.find('ul').length === 1 ) {
                        $type = 'array';
                    } else if ( $parent.find('ul').length > 1 ) {
                        $type = 'key_value_multi_array';
                    }
                    $parent.find("input").on('change', function () {
                        $.WPSF_VC_TYPES.checkbox($type, $parent)
                    });

                    $.WPSF_VC_TYPES.checkbox($type, $parent);
                } else {
                    var $val = $parent.find("input").attr('value');
                    $parent.find("input").attr('data-orgval', $val);
                    $parent.find("input").on('change', function () {
                        if ( $(this).is(":checked") ) {
                            $(this).val($(this).attr('data-orgval'));
                        } else {
                            $(this).val('false');
                        }
                    });
                }
            }
        })
    };

    $.fn.WPSF_VC_SELECT = function () {
        return this.each(function () {
            if ( $.WPSF_VC_TYPES.is_vc_param_elem($(this)) === true ) {
                var $parent = $(this);
                if ( $parent.hasClass('wpsf-element-select-multiple') || $parent.hasClass('wpsf-element-select-multiple-chosen') ) {
                    $parent.find('select').each(function () {
                        $.WPSF_VC_HELPER.save($parent, $(this).val(), 'array');
                        $(this).on('change', function () {
                            var $save = $(this).val();
                            $.WPSF_VC_HELPER.save($parent, $save, 'array');
                        });
                    });
                }
            }
        })
    };

    $.fn.WPSF_VC_SORTER = function () {
        return this.each(function () {
            if ( $.WPSF_VC_TYPES.is_vc_param_elem($(this)) === true ) {
                var $parent = $(this);
                $.WPSF_VC_TYPES.elem_to_save($parent, $parent.find("> .wpsf-fieldset :input"), 'sorter_values');
                $parent.on('wpsf-sorter-updated', function () {
                    $.WPSF_VC_TYPES.elem_to_save($parent, $parent.find("> .wpsf-fieldset :input"), 'sorter_values');
                })
            }
        })
    };

    $.fn.WPSF_VC_FIELDSET = function () {
        return this.each(function () {
            if ( $.WPSF_VC_TYPES.is_vc_param_elem($(this)) === true ) {
                var $parent = $(this);
                $.WPSF_VC_TYPES.elem_to_save($parent, $parent.find(".wpsf-fieldset :input"), 'sorter_values');

                $parent.find("> .wpsf-fieldset :input").on('change', function () {
                    $.WPSF_VC_TYPES.elem_to_save($parent, $parent.find(".wpsf-fieldset :input"), 'sorter_values');
                });


                $parent.find("> .wpsf-fieldset :input").on('blur', function () {
                    $.WPSF_VC_TYPES.elem_to_save($parent, $parent.find(".wpsf-fieldset :input"), 'sorter_values');
                });
            }
        })
    };

    $.fn.WPSF_VC_GROUP = function () {
        return this.each(function () {
            if ( $.WPSF_VC_TYPES.is_vc_param_elem($(this)) === true ) {
                var $parent = $(this);
                $.WPSF_VC_TYPES.elem_to_save($parent, $parent.find(":input"), 'sorter_values');

                $parent.find(":input").on('change', function () {
                    console.log('h');
                    $.WPSF_VC_TYPES.elem_to_save($parent, $parent.find(":input"), 'sorter_values');
                });


                $parent.find(":input").on('blur', function () {
                    $.WPSF_VC_TYPES.elem_to_save($parent, $parent.find(":input"), 'sorter_values');
                });
            }
        })
    };

    $.WPSF_VC_HELPER = {
        vc_popup: $('.wpb_edit_form_elements.vc_edit_form_elements'),

        save: function ($parent, $save_data, $type) {
            if ( $save_data === null ) {
                return;
            }
            var $param_name = $parent.data('param-name');
            var $value = '';

            if ( $save_data !== '' ) {
                if ( typeof $save_data === 'object' && $type === 'array' ) {
                    $value = $.WPSF_VC_HELPER.simple_array($save_data);
                } else if ( typeof $save_data === 'object' && $type === 'key_value_array' ) {
                    $value = $.WPSF_VC_HELPER.key_value_array($save_data);
                } else if ( typeof $save_data === 'object' && $type === 'key_value_multi_array' ) {
                    $value = $.WPSF_VC_HELPER.key_value_multi_array($save_data);
                } else if ( typeof $save_data === 'object' && $type === 'sorter_values' ) {
                    $value = $.WPSF_VC_HELPER.sorter_values($save_data);
                }
            }

            $.WPSF_VC_HELPER.vc_save($param_name, $value);
        },

        vc_save: function ($param_name, $value) {
            var $html = '<div id="wpsf-settings" class="hidden" style="display: none;visibility: hidden;" ></div>';
            var $wrap = $.WPSF_VC_HELPER.vc_popup;

            if ( $wrap.parent().find("div#wpsf-settings").length === 0 ) {
                $wrap.parent().append($html);
            }

            if ( $wrap.parent().find("div#wpsf-settings").length === 1 ) {
                var $parent = $wrap.parent().find("div#wpsf-settings");
                if ( $parent.find("> #" + $param_name + '.wpb_vc_param_value').length === 0 ) {
                    $parent.append($('<input type="hidden" value="" id="' + $param_name + '" name="' + $param_name + '" class="wpb_vc_param_value" />'));
                }

                $parent.find("> #" + $param_name + '.wpb_vc_param_value').val($value);

                return true;
            }

            return false;
        },

        simple_array: function ($save_data) {
            return $save_data.join(',');
        },

        key_value_array: function ($save_data) {
            var $r = new Array;
            $.each($save_data, function ($k, $v) {
                var $s = $k + ":" + $v;
                $r.push($s);
            });
            return $r.join('|');
        },

        key_value_multi_array: function ($save_data) {
            var $r = new Array;
            $.each($save_data, function ($k, $v) {
                if ( typeof $v === 'object' && typeof $v === 'array' ) {
                    $v = $v.join(',');
                }
                var $s = $k + ":" + $v;
                $r.push($s);
            });
            return $r.join('|');
        },

        sorter_values: function ($save_data) {
            return $.WPSF_VC_HELPER.encodeContent(JSON.stringify($save_data));
            var $r = {enabled: [], disabled: []};
            $.each($save_data, function ($key, $val) {
                if ( $val !== '' && typeof  $val === 'object' ) {
                    $.each($val, function ($k, $v) {
                        $r[$key].push($k + ":" + $v);
                    })
                }
            });
            return $.WPSF_VC_HELPER.key_value_multi_array($r);
        },

        rawurlencode: function (str) {
            str = ( str + '' ).toString();
            return encodeURIComponent(str).replace(/!/g, '%21').replace(/'/g, '%27').replace(/\(/g, '%28').replace(/\)/g, '%29').replace(/\*/g, '%2A');
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
                var c1 = string.charCodeAt(n);
                var enc = null;
                if ( c1 < 128 ) {
                    end++;
                } else if ( c1 > 127 && c1 < 2048 ) {
                    enc = String.fromCharCode(( c1 >> 6 ) | 192) + String.fromCharCode(( c1 & 63 ) | 128);
                } else {
                    enc = String.fromCharCode(( c1 >> 12 ) | 224) + String.fromCharCode(( ( c1 >> 6 ) & 63 ) | 128) + String.fromCharCode(( c1 & 63 ) | 128);
                }
                if ( enc !== null ) {
                    if ( end > start ) {
                        utftext += string.slice(start, end);
                    }
                    utftext += enc;
                    start = end = n + 1;
                }
            }
            if ( end > start ) {
                utftext += string.slice(start, stringl);
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
            data = $.WPSF_VC_HELPER.utf8_encode(data + '');
            do {
                o1 = data.charCodeAt(i++);
                o2 = data.charCodeAt(i++);
                o3 = data.charCodeAt(i++);
                bits = o1 << 16 | o2 << 8 | o3;
                h1 = bits >> 18 & 0x3f;
                h2 = bits >> 12 & 0x3f;
                h3 = bits >> 6 & 0x3f;
                h4 = bits & 0x3f;
                tmp_arr[ac++] = b64.charAt(h1) + b64.charAt(h2) + b64.charAt(h3) + b64.charAt(h4);
            } while ( i < data.length );
            enc = tmp_arr.join('');
            var r = data.length % 3;
            return ( r ? enc.slice(0, r - 3) : enc ) + '==='.slice(r || 3);
        },

        encodeContent: function (value) {
            return $.WPSF_VC_HELPER.base64_encode($.WPSF_VC_HELPER.rawurlencode(value));
        }
    };

    $.WPSF_VC = {
        el: $(".wpsf-framework.wpsf-vc-framework"),
        reload: function () {
            var $el = $.WPSF_VC.el;
            $el.find(".wpsf-field-checkbox").WPSF_VC_CHECKBOX();
            $el.find('.wpsf-field-radio').WPSF_VC_CHECKBOX();
            $el.find('.wpsf-field-switcher').WPSF_VC_CHECKBOX();
            $el.find('.wpsf-field-image_select').WPSF_VC_CHECKBOX();
            $el.find('.wpsf-field-color_scheme').WPSF_VC_CHECKBOX();
            $el.find('.wpsf-field-select').WPSF_VC_SELECT();
            $el.find('.wpsf-field-background').WPSF_KEY_VALUE_ARRAY();
            $el.find('.wpsf-field-typography').WPSF_KEY_VALUE_ARRAY();
            $el.find('.wpsf-field-image_size').WPSF_KEY_VALUE_ARRAY();
            $el.find('.wpsf-field-sorter').WPSF_VC_SORTER();
            $el.find('.wpsf-field-fieldset').WPSF_VC_FIELDSET();
            $el.find('.wpsf-field-accordion').WPSF_VC_FIELDSET();
            $el.find('.wpsf-field-tab').WPSF_VC_FIELDSET();
            $el.find('.wpsf-field-social_icons').WPSF_VC_FIELDSET();
            $el.find('.wpsf-field-css_builder').WPSF_VC_FIELDSET();
            $el.find('.wpsf-field-group').WPSF_VC_GROUP();
            $el.find('.wpsf-field-links').WPSF_VC_LINK();

        },
        work: function () {
            var $elem = $.WPSF_VC.el;
            $.WPSF.icons_manager();
            $.WPSF.shortcode_manager();
            $.WPSF.widget_reload();
            $elem.WPSF_DEPENDENCY();
            $elem.WPSF_RELOAD();
            $elem.find('.wpsf-field-group').WPSF_GROUP();
            $.WPSF_VC.reload();
        }
    };

    $.WPSF_VC.work();

} )(jQuery, window, document);