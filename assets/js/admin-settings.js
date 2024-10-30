jQuery(document).ready(function () {
    'use strict';
    jQuery('.vi-ui.vi-ui-main.tabular.menu .item').vi_tab({
        history: true,
        historyType: 'hash'
    });
    jQuery('.vi-ui.dropdown').unbind().dropdown();
    jQuery('.vi-ui.checkbox').unbind().checkbox();
    jQuery('input[type = "number"]').unbind().on('blur change', function () {
        if (!jQuery(this).val() && jQuery(this).data('allow_empty')) {
            return false;
        }
        let new_val, min = parseFloat(jQuery(this).attr('min')) || 0,
            max = parseFloat(jQuery(this).attr('max')),
            val = parseFloat(jQuery(this).val()) || 0;
        new_val = val;
        if (min > val) {
            new_val = min;
        }
        if (max && max < val) {
            new_val = max;
        }
        jQuery(this).val(new_val);
    });
    jQuery('input[type="checkbox"]').unbind().on('change', function () {
        if (jQuery(this).prop('checked')) {
            jQuery(this).parent().find('input[type="hidden"]').val('1');
            if (jQuery(this).hasClass('vicatna-nyp_free_purchase-checkbox')) {
                jQuery('.vicatna-nyp_mess_empty-wrap').addClass('vicatna-disabled');
            }
            if (jQuery(this).hasClass('vicatna-so_atc_normal-checkbox')) {
                jQuery('.vicatna-so_atc_normal-enable').removeClass('vicatna-disabled');
                jQuery('.vicatna-so_atc_normal-disable').addClass('vicatna-disabled');
            }
            if (jQuery(this).hasClass('vicatna-so_popup-checkbox')) {
                jQuery('.vicatna-so_popup-enable').removeClass('vicatna-disabled');
            }
        } else {
            jQuery(this).parent().find('input[type="hidden"]').val('');
            if (jQuery(this).hasClass('vicatna-nyp_free_purchase-checkbox')) {
                jQuery('.vicatna-nyp_mess_empty-wrap').removeClass('vicatna-disabled');
            }
            if (jQuery(this).hasClass('vicatna-so_atc_normal-checkbox')) {
                jQuery('.vicatna-so_atc_normal-enable').addClass('vicatna-disabled');
                jQuery('.vicatna-so_atc_normal-disable').removeClass('vicatna-disabled');
            }
            if (jQuery(this).hasClass('vicatna-so_popup-checkbox')) {
                jQuery('.vicatna-so_popup-enable').addClass('vicatna-disabled');
            }
        }
    });
    jQuery('.vicatna-color').each(function () {
        jQuery(this).css({backgroundColor: jQuery(this).val()});
    });
    jQuery('.vicatna-color').unbind().minicolors({
        change: function (value, opacity) {
            jQuery(this).parent().find('.vicatna-color').css({backgroundColor: value});
        },
        animationSpeed: 50,
        animationEasing: 'swing',
        changeDelay: 0,
        control: 'wheel',
        defaultValue: '',
        format: 'rgb',
        hide: null,
        hideSpeed: 100,
        inline: false,
        keywords: '',
        letterCase: 'lowercase',
        opacity: true,
        position: 'bottom left',
        show: null,
        showSpeed: 100,
        theme: 'default',
        swatches: []
    });
});