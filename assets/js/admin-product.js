jQuery(document).ready(function () {
    'use strict';
    if (typeof woocommerce_admin !== "undefined") {
        woocommerce_admin.vicatna_i18n_regular_empty = vicatna_admin_product.vicatna_i18n_regular_empty;
        woocommerce_admin.i18n_so_qty_greater_max_percentage = vicatna_admin_product.i18n_so_qty_greater_max_percentage;
        woocommerce_admin.i18n_so_qty_less_than_from_qty = vicatna_admin_product.i18n_so_qty_less_than_from_qty;
        woocommerce_admin.i18n_so_qty_greater_than_to_qty = vicatna_admin_product.i18n_so_qty_greater_than_to_qty;
        woocommerce_admin.i18n_nyp_max_less_than_nyp_min = vicatna_admin_product.i18n_nyp_max_less_than_nyp_min;
        woocommerce_admin.i18n_nyp_min_greater_than_nyp_max = vicatna_admin_product.i18n_nyp_min_greater_than_nyp_max;
        woocommerce_admin.i18n_nyp_max_less_than_regular_price = vicatna_admin_product.i18n_nyp_max_less_than_regular_price;
    }
    jQuery(document.body).on('woocommerce-product-type-change', function (e, select_val) {
        if (select_val === 'variable') {
            jQuery('.vicatnat-simple input, .vicatnat-simple select').each(function () {
                jQuery(this).attr('name', '');
            });
        } else {
            jQuery('.vicatnat-simple input, .vicatnat-simple select').each(function () {
                jQuery(this).attr('name', jQuery(this).data('name') || '');
            });
            jQuery('.vicatnat-simple .vicatna-so-qty-condition-accept-price-wrap').removeClass('vicatna-disabled');
            jQuery('.vicatna_type_not_nyp option[value="0"]').attr('disabled', 'disabled');
            jQuery('.vicatna_type_not_so option[value="1"]').attr('disabled', 'disabled');
            if (jQuery('select[name=vicatna_type]').val()) {
                jQuery('._sale_price_field').addClass('vicatna-disabled');//.find('input').data('old_val',  jQuery('._sale_price_field').find('input').val()).val('');
                jQuery('.sale_price_dates_fields').addClass('vicatna-disabled');//.find('input').data('old_val',  jQuery('.sale_price_dates_fields').find('input').val()).val('');
            }
        }
    });
    jQuery(document).on('change', '.vicatna_type_select', function () {
        let wrap = jQuery(this).closest('.vicatnat-settings-wrap'),
            val = jQuery(this).val();
        wrap.find('.vicatna-settings').addClass('vicatna-disabled');
        if (val === '0') {
            wrap.find('.vicatna-settings-nyp').removeClass('vicatna-disabled');
        } else if (val === '1') {
            wrap.find('.vicatna-settings-so').removeClass('vicatna-disabled');
            let regular_price_field;
            if (wrap.hasClass('vicatnat-variation')) {
                regular_price_field = wrap.parents('.variable_pricing').find('.wc_input_price[name^=variable_regular_price]');
            } else {
                regular_price_field = wrap.parents('.pricing').find('#_regular_price');
            }
            if (regular_price_field.length && regular_price_field.val() === '') {
                alert(vicatna_admin_product.vicatna_i18n_regular_empty);
                regular_price_field.focus();
                jQuery(this).val('').trigger('change');
                return false;
            }
        }
        let date_field, sale_price_file;
        if (wrap.prev('.form-field.sale_price_dates_fields').length) {
            date_field = wrap.prev('.form-field');
            sale_price_file = date_field.prev('.form-field');
        } else {
            date_field = wrap.next('.form-field.sale_price_dates_fields');
            sale_price_file = wrap.prev('.form-field');
        }
        if (val) {
            date_field.addClass('vicatna-disabled').find('input').data('old_val', date_field.find('input').val()).val('');
            sale_price_file.addClass('vicatna-disabled').find('input').data('old_val', sale_price_file.find('input').val()).val('');
        } else {
            date_field.removeClass('vicatna-disabled').find('input').val(date_field.find('input').data('old_val') || '');
            sale_price_file.removeClass('vicatna-disabled').find('input').val(sale_price_file.find('input').data('old_val') || '');
        }
    });
    jQuery(document).on('change', '.vicatna_so_qty_select', function () {
        let wrap = jQuery(this).closest('.vicatna-settings-so'),
            val = jQuery(this).val();
        if (val === '0') {
            wrap.find('.vicatna-settings-so-qty-disable').removeClass('vicatna-disabled');
            wrap.find('.vicatna-settings-so-qty-enable').addClass('vicatna-disabled');
        } else if (val === '1') {
            wrap.find('.vicatna-settings-so-qty-disable').addClass('vicatna-disabled');
            wrap.find('.vicatna-settings-so-qty-enable').removeClass('vicatna-disabled');
        }
    });
    jQuery(document).on('change', '.vicatna-settings-so-qty-disable select', function () {
        if (jQuery(this).val() === '1') {
            jQuery(this).closest('.vicatna-settings-so-qty-disable').find('input').attr('max', 100);
        } else {
            jQuery(this).closest('.vicatna-settings-so-qty-disable').find('input').attr('max', '');
        }
    });
    jQuery(document).on('vicatna_so_get_accept_price', '.vicatna-so-qty-wrap', function (e) {
        let qty_wrap = jQuery(this);
        let regular_price_field;
        if (qty_wrap.closest('.variable_pricing').length) {
            regular_price_field = qty_wrap.closest('.variable_pricing').find('.wc_input_price[name^=variable_regular_price]');
        } else {
            regular_price_field = qty_wrap.closest('.pricing').find('#_regular_price');
        }
        if (!regular_price_field.length) {
            return false;
        }
        let regular_price = parseFloat(window.accounting.unformat(regular_price_field.val(), woocommerce_admin.mon_decimal_point));
        if (regular_price === 0) {
            qty_wrap.find('.vicatna-so-qty-condition-accept').addClass('vicatna-disabled');
            return false;
        }
        regular_price = regular_price.toFixed(vicatna_admin_product.price_decimals);
        qty_wrap.find('.vicatna-so-qty-condition-accept-max').html(regular_price);
        let min_price, discount_value = qty_wrap.find('.vicatna-so-qty-min').val(),
            discount_type = qty_wrap.find('.vicatna-so-qty-type').val();
        if (!discount_value) {
            qty_wrap.find('.vicatna-so-qty-condition-accept:not(.vicatna-so-qty-condition-accept-max)').addClass('vicatna-disabled');
            return false;
        }
        discount_value = parseFloat(window.accounting.unformat(discount_value, woocommerce_admin.mon_decimal_point));
        qty_wrap.find('.vicatna-so-qty-condition-accept').removeClass('vicatna-disabled');
        if (discount_type === '0') {
            min_price = regular_price - discount_value;
            min_price = min_price > 0 ? min_price : 0;
        } else {
            min_price = regular_price - (regular_price * discount_value / 100);
        }
        min_price = min_price.toFixed(vicatna_admin_product.price_decimals);
        qty_wrap.find('.vicatna-so-qty-condition-accept-min').html(min_price);
    });
    jQuery(document).on('woocommerce_variations_loaded', '#woocommerce-product-data', function () {
        settings_init();
    });
    settings_init();

    jQuery(document).on('change blur', '.wc_input_price[name=_regular_price], .wc_input_price[name^=variable_regular_price]', function () {
        let regular_price_field = jQuery(this), input;
        if (regular_price_field.attr('name').indexOf('variable') !== -1) {
            input = regular_price_field.parents('.variable_pricing').find('.wc_input_price[name^=vicatna_loop_nyp_min]');
            regular_price_field.parents('.variable_pricing').find('.vicatna-so-qty-wrap').each(function () {
                jQuery(this).trigger('vicatna_so_get_accept_price');
            });
        } else {
            input = regular_price_field.parents('.pricing').find('.wc_input_price[name=vicatna_nyp_min]');
            regular_price_field.parents('.pricing').find('.vicatna-so-qty-wrap').each(function () {
                jQuery(this).trigger('vicatna_so_get_accept_price');
            });
        }
        if (!input.length || regular_price_field.val() === '') {
            return false;
        }
        let min_price = parseFloat(window.accounting.unformat(input.val(), woocommerce_admin.mon_decimal_point)),
            regular_price = parseFloat(window.accounting.unformat(regular_price_field.val(), woocommerce_admin.mon_decimal_point));
        if (min_price > regular_price) {
            input.val(input.attr('min') || '');
        }
    });
    jQuery(document).on('change blur', '.wc_input_price[name=vicatna_nyp_min], .wc_input_price[name^=vicatna_loop_nyp_min]', function () {
        if (jQuery(this).hasClass('vicatna-nyp-min-reset')) {
            jQuery(this).removeClass('vicatna-nyp-min-reset').val('');
        }
    });
    jQuery(document).on('keyup', '.wc_input_price[name=vicatna_nyp_min], .wc_input_price[name^=vicatna_loop_nyp_min]', function () {
        let input = jQuery(this), regular_price_field, input_max;
        if (input.attr('name').indexOf('loop') !== -1) {
            regular_price_field = input.parents('.variable_pricing').find('.wc_input_price[name^=variable_regular_price]');
            input_max = input.parents('.vicatna-settings').find('[name^=vicatna_loop_nyp_max');
        } else {
            regular_price_field = input.parents('.pricing').find('#_regular_price');
            input_max = input.parents('.vicatna-settings').find('[name=vicatna_nyp_max');
        }
        let min_price = parseFloat(window.accounting.unformat(input.val(), woocommerce_admin.mon_decimal_point));
        if (regular_price_field.length && regular_price_field.val()) {
            let regular_price = parseFloat(window.accounting.unformat(regular_price_field.val(), woocommerce_admin.mon_decimal_point));
            if (min_price > regular_price) {
                jQuery(document.body).triggerHandler('wc_add_error_tip', [input, 'i18n_sale_less_than_regular_error']);
                input.addClass('vicatna-nyp-min-reset');
                return false;
            } else {
                jQuery(document.body).triggerHandler('wc_remove_error_tip', [input, 'i18n_sale_less_than_regular_error']);
                input.removeClass('vicatna-nyp-min-reset');
            }
        }
        if (input_max.length && input_max.val()) {
            let max_price = parseFloat(window.accounting.unformat(input_max.val(), woocommerce_admin.mon_decimal_point));
            if (min_price > max_price) {
                jQuery(document.body).triggerHandler('wc_add_error_tip', [input, 'i18n_nyp_min_greater_than_nyp_max']);
                input.addClass('vicatna-nyp-min-reset');
            } else {
                jQuery(document.body).triggerHandler('wc_remove_error_tip', [input, 'i18n_nyp_min_greater_than_nyp_max']);
                input.removeClass('vicatna-nyp-min-reset');
            }
        }
    });
    jQuery(document).on('change blur', '.wc_input_price[name=vicatna_nyp_max], .wc_input_price[name^=vicatna_loop_nyp_max]', function () {
        if (jQuery(this).hasClass('vicatna-nyp-max-reset')) {
            jQuery(this).removeClass('vicatna-nyp-max-reset').val('');
        }
    });
    jQuery(document).on('keyup', '.wc_input_price[name=vicatna_nyp_max], .wc_input_price[name^=vicatna_loop_nyp_max]', function () {
        let input = jQuery(this), regular_price_field, input_min;
        if (input.attr('name').indexOf('loop') !== -1) {
            regular_price_field = input.parents('.variable_pricing').find('.wc_input_price[name^=variable_regular_price]');
            input_min = input.parents('.vicatna-settings').find('[name^=vicatna_loop_nyp_min');
        } else {
            regular_price_field = input.parents('.pricing').find('#_regular_price');
            input_min = input.parents('.vicatna-settings').find('[name=vicatna_nyp_min');
        }
        let max_price = parseFloat(window.accounting.unformat(input.val(), woocommerce_admin.mon_decimal_point));
        if (regular_price_field.length && regular_price_field.val()) {
            let regular_price = parseFloat(window.accounting.unformat(regular_price_field.val(), woocommerce_admin.mon_decimal_point));
            if (max_price > 0 && max_price < regular_price) {
                jQuery(document.body).triggerHandler('wc_add_error_tip', [input, 'i18n_nyp_max_less_than_regular_price']);
                input.addClass('vicatna-nyp-max-reset');
                return false;
            } else {
                jQuery(document.body).triggerHandler('wc_remove_error_tip', [input, 'i18n_nyp_max_less_than_regular_price']);
                input.removeClass('vicatna-nyp-max-reset');
            }
        }
        if (input_min.length && input_min.val()) {
            let min_price = parseFloat(window.accounting.unformat(input_min.val(), woocommerce_admin.mon_decimal_point));
            if (max_price > 0 && min_price > max_price) {
                jQuery(document.body).triggerHandler('wc_add_error_tip', [input, 'i18n_nyp_max_less_than_nyp_min']);
                input.addClass('vicatna-nyp-max-reset');
            } else {
                jQuery(document.body).triggerHandler('wc_remove_error_tip', [input, 'i18n_nyp_max_less_than_nyp_min']);
                input.removeClass('vicatna-nyp-max-reset');
            }
        }
    });
    jQuery(document).on('change blur', '.wc_input_price[name=vicatna_so_min], .wc_input_price[name^=vicatna_loop_so_min],.vicatna-so-qty-min', function () {
        if (jQuery(this).hasClass('vicatna-so-min-reset')) {
            jQuery(this).removeClass('vicatna-so-min-reset').val(jQuery(this).attr('min') || '');
        } else if (jQuery(this).hasClass('vicatna-so-min-setmax')) {
            jQuery(this).removeClass('vicatna-so-min-setmax').val(jQuery(this).attr('max') || jQuery(this).attr('min') || '');
        }
    });
    jQuery(document).on('keyup', '.wc_input_price[name=vicatna_so_min], .wc_input_price[name^=vicatna_loop_so_min],.vicatna-so-qty-min', function () {
        let input = jQuery(this), regular_price_field;
        if (input.closest('.vicatnat-variation').length) {
            regular_price_field = input.parents('.variable_pricing').find('.wc_input_price[name^=variable_regular_price]');
        } else {
            regular_price_field = input.parents('.pricing').find('#_regular_price');
        }
        if (!regular_price_field.length) {
            return false;
        }
        if (regular_price_field.val() === '') {
            alert(vicatna_admin_product.vicatna_i18n_regular_empty);
            regular_price_field.focus();
            return false;
        }
        if (input.attr('max') === '100') {
            if (input.val() && parseFloat(input.val()) > 100) {
                jQuery(document.body).triggerHandler('wc_add_error_tip', [input, 'i18n_so_qty_greater_max_percentage']);
                input.addClass('vicatna-so-min-setmax');
            } else {
                jQuery(document.body).triggerHandler('wc_remove_error_tip', [input, 'i18n_so_qty_greater_max_percentage']);
                input.removeClass('vicatna-so-min-setmax');
            }
            return false;
        }
        let min_price = parseFloat(window.accounting.unformat(input.val(), woocommerce_admin.mon_decimal_point)),
            regular_price = parseFloat(window.accounting.unformat(regular_price_field.val(), woocommerce_admin.mon_decimal_point));
        if (min_price > regular_price) {
            jQuery(document.body).triggerHandler('wc_add_error_tip', [input, 'i18n_sale_less_than_regular_error']);
            input.addClass('vicatna-so-min-reset');
        } else {
            jQuery(document.body).triggerHandler('wc_remove_error_tip', [input, 'i18n_sale_less_than_regular_error']);
            input.removeClass('vicatna-so-min-reset');
        }
    });

    function settings_init() {
        jQuery('.vicatna-settings-so-qty-wrap:not(.vicatna-settings-so-qty-wrap-init)').each(function (k, v) {
            jQuery(this).addClass('vicatna-settings-so-qty-wrap-init').sortable({
                connectWith: ".vicatna-settings-so-qty-wrap-" + Date.now() + '-' + k,
                handle: ".vicatna-so-qty-move",
                placeholder: "vicatna-placeholder",
            });
        });
        jQuery('.vicatna-so-qty-wrap:not(.vicatna-so-qty-wrap-init)').each(function () {
            jQuery(this).addClass('vicatna-so-qty-wrap').vicatna_rule_so_qty();
        });
        jQuery('.vicatna_type_not_nyp option[value="0"]').attr('disabled', 'disabled');
        jQuery('.vicatna_type_not_so option[value="1"]').attr('disabled', 'disabled');
        jQuery('.vicatnat-variation').each(function () {
            if (jQuery(this).find('[name^=vicatna_loop_type]').val()) {
                let date_field = jQuery(this).prev('.form-field');
                let sale_price_file = date_field.prev('.form-field');
                date_field.addClass('vicatna-disabled');//.find('input').data('old_val',  date_field.find('input').val()).val('');
                sale_price_file.addClass('vicatna-disabled');//.find('input').data('old_val',  sale_price_file.find('input').val()).val('');
            }
        });
    }
});

function vicatna_set_value_number(input) {
    jQuery(input).off().on('blur change', function () {
        if (!jQuery(this).val() && jQuery(this).data('allow_empty')) {
            return false;
        }
        let new_val, min = parseFloat(jQuery(this).attr('min') || 0),
            max = jQuery(this).attr('max') ? parseFloat(jQuery(this).attr('max')) : '',
            val = parseFloat(jQuery(this).val() || 0);
        new_val = val;
        if (min > val) {
            new_val = min;
        }
        if (max && max < val) {
            new_val = max;
        }
        jQuery(this).val(new_val);
    });
}

jQuery.fn.vicatna_rule_so_qty = function () {
    new vicatna_rule_so_qty_init(this);
    return this;
};
var vicatna_rule_so_qty_init = function (rule) {
    this.rule = rule;
    this.init();
    setTimeout(function () {
        rule.trigger('vicatna_so_get_accept_price');
    }, 100);
};
vicatna_rule_so_qty_init.prototype.init = function () {
    let rule = this.rule;
    rule.find('input[type = "number"]:not(.vicatna-inputnumber-init)').addClass('vicatna-inputnumber-init').each(function () {
        vicatna_set_value_number(jQuery(this));
    });
    rule.find('.vicatna-so-qty-min').on('change', function () {
        rule.trigger('vicatna_so_get_accept_price');
    });
    rule.find('.vicatna-so-qty-type').on('change', function () {
        if (jQuery(this).val() === '1') {
            rule.find('.vicatna-so-qty-min').attr('max', 100);
        } else {
            rule.find('.vicatna-so-qty-min').attr('max', '');
        }
        rule.trigger('vicatna_so_get_accept_price');
    });
    rule.find('.vicatna-so-qty-from').on('keyup', function () {
        let input = jQuery(this), val = jQuery(this).val(), max_qty = rule.find('.vicatna-so-qty-to').val();
        if (!val || val === '' || !max_qty || max_qty === '') {
            return false;
        }
        if (parseFloat(val) > parseFloat(max_qty)) {
            jQuery(document.body).triggerHandler('wc_add_error_tip', [input, 'i18n_so_qty_greater_than_to_qty']);
        } else {
            jQuery(document.body).triggerHandler('wc_remove_error_tip', [input, 'i18n_so_qty_greater_than_to_qty']);
        }
    });
    rule.find('.vicatna-so-qty-to').on('keyup', function () {
        let input = jQuery(this), val = jQuery(this).val(), min_qty = rule.find('.vicatna-so-qty-from').val();
        if (!val || val === '' || !min_qty || min_qty === '') {
            return false;
        }
        if (parseFloat(val) < parseFloat(min_qty)) {
            jQuery(document.body).triggerHandler('wc_add_error_tip', [input, 'i18n_so_qty_less_than_from_qty']);
        } else {
            jQuery(document.body).triggerHandler('wc_remove_error_tip', [input, 'i18n_so_qty_less_than_from_qty']);
        }
    });
    this.add_new(rule);
    this.remove(rule);
};
vicatna_rule_so_qty_init.prototype.add_new = function (rule) {
    rule.find('.vicatna-so-qty-clone').off().on('click', function (e) {
        e.stopPropagation();
        rule.find('.vicatna-warning-wrap').removeClass('vicatna-warning-wrap');
        let qty_to = rule.find('.vicatna-so-qty-to').val();
        if (!qty_to) {
            alert('Please enter Qty to value');
            rule.find('.vicatna-so-qty-to').addClass('vicatna-warning-wrap').focus();
            return false;
        }
        if (parseFloat(qty_to) < parseFloat(rule.find('.vicatna-so-qty-from').val())) {
            alert('Please enter in a value greater than the Qty from value');
            rule.find('.vicatna-so-qty-to').addClass('vicatna-warning-wrap').focus();
            return false;
        }
        let newrule = rule.clone();
        newrule.find('.vicatna-inputnumber-init').removeClass('vicatna-inputnumber-init');
        newrule.find('.vicatna-so-qty-from').val(parseFloat(rule.find('.vicatna-so-qty-to').val()) + 1);
        newrule.find('.vicatna-so-qty-to').val('');
        newrule.find('.vicatna-so-qty-type').val(rule.find('.vicatna-so-qty-type').val());
        newrule.vicatna_rule_so_qty();
        newrule.insertAfter(rule);
        jQuery(document.body).trigger('init_tooltips');
        e.stopPropagation();
    });
};
vicatna_rule_so_qty_init.prototype.remove = function (rule) {
    rule.find('.vicatna-so-qty-remove').off().on('click', function (e) {
        e.stopPropagation();
        if (rule.closest('.vicatna-settings-so-qty-wrap').find('.vicatna-so-qty-remove').length === 1) {
            alert('You can not remove the last item.');
            return false;
        }
        if (confirm("Would you want to remove this?")) {
            rule.remove();
        }
        e.stopPropagation();
    });
};