jQuery(document).ready(function () {
    'use strict';
    if (typeof vicatna_single === 'undefined') {
        return false;
    }
    setTimeout(function () {
        jQuery(document.body).trigger('vicatna_check_form');
    },100);
    jQuery(document.body).on('vicatna_check_form', function () {
        if (!jQuery('form.cart:not(.vicatna-form-init),.variations_form:not(.vicatna-form-init),.vi-wcaio-sb-cart-form:not(.vicatna-form-init)').length){
            setTimeout(function () {
                jQuery(document.body).trigger('vicatna_check_form');
            },100);
            return false;
        }
        vicatna_form_init();
    });
    jQuery(document.body).on('vicatna_nyp_check_price', function (e, button, form) {
        let price = form.find('.vicatna_nyp_value').val();
        if (!vicatna_single.nyp_free_purchase && !price) {
            vicatna_show_message(form, vicatna_single.nyp_mess_empty);
            button.removeClass('vicatna-button-loading');
            return false;
        }
        if (vicatna_single.nyp_free_purchase && (!price || price === '0')) {
            button.removeClass('vicatna-button-loading').addClass('vicatna-nyp-single-atc-button-checked').trigger('click');
            return false;
        }
        let data = {
            price: price,
            price_min: form.find('.vicatna_nyp_value').attr('min'),
            price_max: form.find('.vicatna_nyp_value').attr('max'),
            product_id: form.find('.vicatna_nyp_value').attr('data-product_id'),
            language: vicatna_single.language,
            vicatna_nonce: vicatna_single.nonce,
        };
        jQuery.ajax({
            url: vicatna_single.wc_ajax_url.toString().replace('%%endpoint%%', 'vicatna_nyp_check_price'),
            type: 'POST',
            data: data,
            success: function (response) {
                if (response.status === 'error') {
                    button.removeClass('vicatna-button-loading');
                }
                if (response.message) {
                    vicatna_show_message(form, response.message);
                }
                if (response.status === 'success') {
                    button.removeClass('vicatna-button-loading').addClass('vicatna-nyp-single-atc-button-checked').trigger('click');
                    return true;
                }
            },
            error: function (err) {
                button.removeClass('vicatna-button-loading');
            }
        });
        return false;
    });
    jQuery(document.body).on('vicatna_so_check_price', function (e, button, form) {
        let price = form.find('.vicatna_so_value').val(),
            form_atc = jQuery('.vicatna-so-button.vicatna-button-loading').closest('.vicatna-form-init');
        if (!price) {
            vicatna_show_message(form, vicatna_single.so_mess_empty);
            button.removeClass('vicatna-button-loading');
            if (button.closest('.vicatna-form-init').length) {
                form_atc.find('.vicatna-button-loading').removeClass('vicatna-button-loading');
            }
            return false;
        }
        let data = {
            price: price,
            product_id: form.find('.vicatna_so_value').attr('data-product_id'),
            quantity: form.find('.vicatna-so-qty').val() ? (form.find('[name="quantity"]').val() || form_atc.find('[name="quantity"]').val() || 1) : 1,
            language: vicatna_single.language,
            vicatna_nonce: vicatna_single.nonce,
        };
        button.attr('data-so_bt_label', button.html());
        jQuery.ajax({
            url: vicatna_single.wc_ajax_url.toString().replace('%%endpoint%%', 'vicatna_so_check_price'),
            type: 'POST',
            data: data,
            beforeSend: function () {
                button.html(vicatna_single.so_mess_wait);
            },
            success: function (response) {
                if (response.status === 'error') {
                    button.removeClass('vicatna-button-loading');
                    if (button.closest('.vicatna-form-init').length) {
                        form_atc.find('.vicatna-button-loading').removeClass('vicatna-button-loading');
                    }
                }
                if (response.message) {
                    vicatna_show_message(form, response.message);
                }
                button.html(button.attr('data-so_bt_label'));
                if (response.status === 'success') {
                    if (form_atc.find('.vicatna-so-value-' + data.product_id).length) {
                        form_atc.find('.vicatna-so-value-' + data.product_id).val(price);
                    } else {
                        form_atc.append('<input type="hidden" name="vicatna_so_value" class="vicatna-so-value-checked vicatna-so-value-' + data.product_id + '"  value="' + price + '">');
                    }
                    if (form_atc.find('.vicatna-so-qty-min-' + data.product_id).length) {
                        form_atc.find('.vicatna-so-qty-min-' + data.product_id).val(data.quantity);
                    } else {
                        form_atc.append('<input type="hidden" name="vicatna_so_qty_min" class="vicatna-so-qty-min-checked vicatna-so-qty-min-' + data.product_id + '" value="' + data.quantity + '">');
                    }
                    if (form.find('[name="quantity"]').val()) {
                        form_atc.find('[name="quantity"]').val(form.find('[name="quantity"]').val());
                    }
                    jQuery('.vicatna-so-button.vicatna-button-loading').closest('.vicatna-wrap').addClass('vicatna-disabled');
                    if (form_atc.find('.vicatna-add-to-cart-label-wrap').length) {
                        form_atc.find('.vicatna-single-atc-button').html(form_atc.find('.vicatna-add-to-cart-label-wrap').html());
                    }
                    form_atc.find('.vicatna-single-atc-button').addClass('vicatna-so-single-atc-button-checked').removeClass('vicatna-button-loading').trigger('click');
                    return true;
                }
            },
            error: function (err) {
                button.html(button.attr('data-so_bt_label'));
                button.removeClass('vicatna-button-loading');
                form_atc.find('.vicatna-single-atc-button').removeClass('vicatna-button-loading');
            }
        });
        return false;
    });
    jQuery(document).on('ajaxComplete', function () {
        setTimeout(function () {
            jQuery(document.body).trigger('vicatna_check_form');
        },100);
        return false;
    });
    jQuery(document).on("show_variation viwpvs_show_variation", '.vicatna-form-init', function (event, variation, purchasable) {
        jQuery(this).find('.vicatna-wrap').html('').removeClass('vicatna-disabled');
        jQuery(this).find('.single_add_to_cart_button, .vi-wcaio-product-bt-atc').addClass('vicatna-single-atc-button');
        jQuery(this).find('.vicatna-single-atc-button').removeClass('vicatna-nyp-single-atc-button-not-check vicatna-so-single-atc-button-checked vicatna-so-single-atc-button-not-check');
        if (jQuery(this).find('.vicatna-single-atc-button').attr('data-label')) {
            jQuery(this).find('.vicatna-single-atc-button').html(jQuery(this).find('.vicatna-single-atc-button').attr('data-label'));
        }
        jQuery(this).find('.vicatna-so-value-checked,.vicatna-so-qty-min-checked').attr('name', '');
        jQuery(this).find('.vicatna-so-value-' + variation.variation_id).attr('name', 'vicatna_so_value');
        jQuery(this).find('.vicatna-so-qty-min-' + variation.variation_id).attr('name', 'vicatna_so_qty_min');
        if (!purchasable) {
            return false;
        }
        if (variation.vicatna_nyp_html) {
            if (jQuery(this).find('.vicatna-nyp-wrap').length) {
                jQuery(this).find('.vicatna-nyp-wrap').replaceWith(variation.vicatna_nyp_html);
            } else {
                jQuery(this).find('.woocommerce-variation-availability').after(variation.vicatna_nyp_html)
            }
            jQuery(this).find('.vicatna-single-atc-button').addClass('vicatna-nyp-single-atc-button-not-check');
        } else if (variation.vicatna_so_html) {
            if (jQuery(this).find('.vicatna-so-wrap').length) {
                jQuery(this).find('.vicatna-so-wrap').replaceWith(variation.vicatna_so_html);
            } else {
                jQuery(this).find('.woocommerce-variation-availability').after(variation.vicatna_so_html)
            }
            if (vicatna_single.so_single_atc_label) {
                if (jQuery(this).find('.vicatna-so-wrap .vicatna-so-button-checked').length) {
                    jQuery(this).find('.vicatna-single-atc-button').addClass('vicatna-so-single-atc-button-checked');
                } else if (jQuery(this).find('.vicatna-so-value-' + variation.variation_id).length) {
                    jQuery(this).find('.vicatna-single-atc-button').addClass('vicatna-so-single-atc-button-checked');
                    jQuery(this).find('.vicatna-so-wrap').addClass('vicatna-disabled');
                } else {
                    jQuery(this).find('.vicatna-single-atc-button').addClass('vicatna-so-single-atc-button-not-check');
                    jQuery(this).find('.vicatna-single-atc-button').attr('data-label', jQuery(this).find('.vicatna-single-atc-button').html());
                    jQuery(this).find('.vicatna-single-atc-button').html(vicatna_single.so_single_atc_label);
                }
            }
        }
    });
    jQuery(document).on('hide_variation viwpvs_hide_variation', '.vicatna-form-init', function () {
        if (parseInt(jQuery(this).find('.variation_id').val() || 0) > 0) {
            return false;
        }
        jQuery(this).find('.vicatna-wrap').html('');
        jQuery(this).find('.single_add_to_cart_button, .vi-wcaio-product-bt-atc').addClass('vicatna-single-atc-button');
        jQuery(this).find('.vicatna-single-atc-button').removeClass('vicatna-nyp-single-atc-button-not-check vicatna-so-single-atc-button-checked vicatna-so-single-atc-button-not-check');
        if (jQuery(this).find('.vicatna-single-atc-button').attr('data-label')) {
            jQuery(this).find('.vicatna-single-atc-button').html(jQuery(this).find('.vicatna-single-atc-button').attr('data-label'));
        }
        jQuery(this).find('.vicatna-so-value-checked,.vicatna-so-qty-min-checked').attr('name', '');
    });
    jQuery(document).on('keydown', '.vicatna-value', function () {
        jQuery(this).closest('.vicatna-form-init').find('.single_add_to_cart_button, .vi-wcaio-product-bt-atc').addClass('vicatna-single-atc-button');
        if (jQuery('.vicatna-message-popup-wrap.vicatna-message-popup-wrap-show').length) {
            vicatna_hide_message_popup();
        } else {
            vicatna_hide_message(jQuery(this).closest('.vicatna-wrap').length ? jQuery(this).closest('.vicatna-wrap') : jQuery(this).closest('.vicatna-popup-wrap'));
        }
    });
    jQuery(document).on('click', '.single_add_to_cart_button.vicatna-single-atc-button:not(.vicatna-button-loading), .vicatna-single-atc-button:not(.vicatna-button-loading)', function (e) {
        e.preventDefault();
        e.stopPropagation();
        e.stopImmediatePropagation();
        let button = jQuery(this), form = jQuery(this).closest('.vicatna-form-init');
        button.addClass('vicatna-button-loading');
        if (!form.length || button.hasClass('disabled')) {
            button.removeClass('vicatna-button-loading');
            return false;
        }
        if (button.hasClass('vicatna-so-single-atc-button-without-check')) {
            form.find('.vicatna-value').attr({'data-step': form.find('.vicatna-value').attr('step'), step: 'any'});
            button.removeClass('vicatna-button-loading vicatna-single-atc-button').attr('type', 'submit').trigger('click');
            return false;
        }
        if (form.hasClass('.variations_form')) {
            let variation_id_check = parseInt(form.find('input[name=variation_id]').val() || 0);
            if (!variation_id_check || variation_id_check <= 0) {
                button.removeClass('vicatna-button-loading');
                return false;
            }
        }
        if (!parseFloat(form.find('[name="quantity"]').val())) {
            vicatna_show_message(form, vicatna_single.i18n_empty_qty);
            form.find('[name="quantity"]').focus();
            button.removeClass('vicatna-button-loading');
            return false;
        }
        if (button.hasClass('vicatna-so-single-atc-button-checked') || button.hasClass('vicatna-nyp-single-atc-button-checked')) {
            jQuery('.vicatna-popup-overlay').trigger('click');
            form.find('.vicatna-so-button').addClass('vicatna-disabled');
            form.find('.vicatna-value').attr({'data-step': form.find('.vicatna-value').attr('step'), step: 'any'});
            button.removeClass('vicatna-button-loading vicatna-single-atc-button vicatna-nyp-single-atc-button-checked').attr('type', 'submit').trigger('click');
            return false;
        }
        if (form.find('.vicatna-value').attr('data-step')) {
            form.find('.vicatna-value').attr('step', form.find('.vicatna-value').attr('data-step'));
        }
        if (button.hasClass('vicatna-so-single-atc-button-not-check')) {
            form.find('.vicatna-so-button').trigger('click');
            return false;
        }
        if (button.hasClass('vicatna-nyp-single-atc-button-not-check')) {
            jQuery(document.body).trigger('vicatna_nyp_check_price', [button, form]);
            return false;
        }
        form.find('.vicatna-value').attr({min: '', max: ''});
        button.removeClass('vicatna-button-loading vicatna-single-atc-button').attr('type', 'submit').trigger('click');
        return false;
    });
    jQuery(document).on('click', '.vicatna-so-button:not(.vicatna-button-loading)', function (e) {
        e.preventDefault();
        e.stopPropagation();
        let button = jQuery(this),
            product_id = jQuery(this).data('product_id') || '',
            form = jQuery(this).closest('.vicatna-form-init');
        if (!form.length) {
            return false;
        }
        if (!product_id) {
            product_id = form.find('.variation_id').val();
        }
        if (!product_id || parseInt(product_id) <= 0) {
            form.find('.vicatna-single-atc-button').removeClass('vicatna-button-loading').addClass('vicatna-so-single-atc-button-checked').trigger('click');
            return false;
        }
        button.addClass('vicatna-button-loading');
        if (button.hasClass('vicatna-so-single-atc-button-checked') || form.find('.vicatna-so-qty-min-' + product_id).length) {
            form.find('.vicatna-single-atc-button').removeClass('vicatna-button-loading').addClass('vicatna-so-single-atc-button-checked').trigger('click');
            return false;
        }
        if (button.hasClass('vicatna-so-button-popup')) {
            let popup = jQuery('.vicatna-popup-wrap.vicatna-popup-wrap-so'),
                popup_data = form.find('.vicatna-popup-so-value').data('val');
            popup.find('.vicatna-popup-form').attr({'class': popup_data.class_form, 'data-product_id': product_id});
            popup.find('.vicatna-popup-form-header-wrap').html(popup_data.popup_title);
            if (popup_data.input_title) {
                popup.find('.vicatna-popup-form-content-price .vicatna-popup-form-content-title').html(popup_data.input_title);
            } else {
                popup.find('.vicatna-popup-form-content-price').addClass('vicatna-popup-form-content-center');
                popup.find('.vicatna-popup-form-content-price .vicatna-popup-form-content-title').addClass('vicatna-disabled');
            }
            popup.find('.vicatna-popup-form-content-price .vicatna-popup-form-content-value').html(popup_data.input_html);
            popup.find('.vicatna-popup-form-content-qty .vicatna-popup-form-content-value').html(popup_data.qty_html);
            popup.find('[name=quantity]').val(form.find('[name=quantity]').val() || 1);
            if (typeof Flatsome !== 'undefined') {
                jQuery('.quantity').addQty();
            }
            jQuery('html').addClass('vicatna-html-non-scroll');
            popup.removeClass('vicatna-disabled').css({left: '100%'}).animate({left: '0'}, 500);
            if (button.closest('[tabindex="-1"], .vi-wcaio-va-cart-form-wrap-wrap, .vi-wcaio-sidebar-cart, .shortcode-wcpr-modal-light-box, .wcpr-modal-light-box').css('position') === 'fixed') {
                button.closest('[tabindex="-1"], .vi-wcaio-va-cart-form-wrap-wrap, .vi-wcaio-sidebar-cart, .shortcode-wcpr-modal-light-box, .wcpr-modal-light-box').addClass('vicatna-popup-wrap-animate');
            }
            jQuery('.vicatna-popup-wrap-animate').animate({left: '-100%'}, 500).css({visibility: 'hidden'});
            return false;
        } else {
            jQuery(document.body).trigger('vicatna_so_check_price', [button, form]);
        }
        return false;
    });
    jQuery(document).on('click', '.vicatna-popup-wrap-so .vicatna-popup-form-bt:not(.vicatna-button-loading)', function (e) {
        e.preventDefault();
        e.stopPropagation();
        e.stopImmediatePropagation();
        let button = jQuery(this), form = jQuery(this).closest('.vicatna-popup-wrap-so');
        button.addClass('vicatna-button-loading');
        jQuery(document.body).trigger('vicatna_so_check_price', [button, form]);
        return false;
    });
    jQuery(document).on('click', '.vicatna-popup-overlay, .vicatna-popup-cancel', function (e) {
        jQuery('html').removeClass('vicatna-html-non-scroll');
        let popup = jQuery(this).closest('.vicatna-popup-wrap');
        popup.animate({left: '100%'}, 400);
        jQuery('.vicatna-popup-wrap-animate').removeClass('vicatna-popup-wrap-animate').css({visibility: 'visible'}).animate({left: '0'}, 400);
        jQuery('.vicatna-button-loading').removeClass('vicatna-button-loading');
        vicatna_hide_message_popup();
        setTimeout(function (popup) {
            if (popup.hasClass('vicatna-popup-wrap-non-ajax')) {
                popup.addClass('vicatna-disabled');
            } else {
                popup.remove();
            }
        }, 400, popup);
    });
});

function vicatna_form_init() {
    jQuery('form.cart:not(.vicatna-form-init),.variations_form:not(.vicatna-form-init),.vi-wcaio-sb-cart-form:not(.vicatna-form-init)').each(function () {
        jQuery(this).addClass('vicatna-form-init');
        if (jQuery(this).find('.vicatna-nyp-wrap').length) {
            jQuery(this).find('.single_add_to_cart_button, .vi-wcaio-product-bt-atc').addClass('vicatna-single-atc-button vicatna-nyp-single-atc-button-not-check');
        } else if (jQuery(this).find('.vicatna-so-wrap .vicatna-so-button-checked').length) {
            jQuery(this).find('.single_add_to_cart_button, .vi-wcaio-product-bt-atc').addClass('vicatna-single-atc-button vicatna-so-single-atc-button-checked');
        } else if (jQuery(this).find('.vicatna-so-wrap').length && vicatna_single.so_single_atc_label) {
            jQuery(this).find('.single_add_to_cart_button, .vi-wcaio-product-bt-atc').addClass('vicatna-single-atc-button vicatna-so-single-atc-button-not-check');
        } else if (jQuery(this).find('.vicatna-so-wrap').length) {
            jQuery(this).find('.single_add_to_cart_button, .vi-wcaio-product-bt-atc').addClass('vicatna-single-atc-button vicatna-so-single-atc-button-without-check');
        }
    });
}

function vicatna_show_message(form, message) {
    form = jQuery(form);
    if (form.closest('.vicatna-loop-wrap, .vicatna-popup-wrap, .vi-wcaio-va-cart-form-wrap-wrap, .vi-wcaio-sb-wrap, .wlb-product-wrapper').length || form.closest('[tabindex="-1"], .shortcode-wcpr-modal-light-box, .wcpr-modal-light-box').css('position') === 'fixed') {
        vicatna_show_message_popup(message);
        return false;
    }
    let notices_wrap = jQuery('.woocommerce-notices-wrapper');
    notices_wrap.find('.vicatna-message-wrap').remove();
    notices_wrap.prepend('<div class="vicatna-message-wrap">' + message + '</div>');
    notices_wrap.find('.vicatna-message-wrap').addClass('vicatna-message-wrap-show');
    jQuery.scroll_to_notices(notices_wrap);
    setTimeout(function () {
        vicatna_hide_message();
    }, 15000);
}

function vicatna_hide_message() {
    jQuery('.woocommerce-notices-wrapper').find('.vicatna-message-wrap').animate({height: '0px', margin: '0 !important', padding: '0 !important'}, 400);
    setTimeout(function () {
        jQuery('.woocommerce-notices-wrapper').find('.vicatna-message-wrap').remove();
    }, 400);
}

function vicatna_show_message_popup(message) {
    if (!jQuery('.vicatna-message-popup-wrap').length) {
        jQuery('body').append('<div class="vicatna-message-popup-wrap vicatna-message-popup-wrap-show"><div>' + message + '</div></div>');
    } else {
        jQuery('.vicatna-message-popup-wrap').removeClass('vicatna-message-popup-wrap-hide').addClass('vicatna-message-popup-wrap-show');
        jQuery('.vicatna-message-popup-wrap > div').html(message);
    }
    setTimeout(function () {
        vicatna_hide_message_popup();
    }, 5000);
}

function vicatna_hide_message_popup() {
    jQuery('.vicatna-message-popup-wrap').addClass('vicatna-message-popup-wrap-hide').removeClass('vicatna-message-popup-wrap-show');
}