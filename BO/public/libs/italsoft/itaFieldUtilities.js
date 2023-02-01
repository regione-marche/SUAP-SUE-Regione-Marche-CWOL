var itaFieldUtilities = (function () {
    function filterConfigOption(value, text) {
        return '<li class="ui-corner-all" style="box-sizing: border-box;"><a href="#" data-filter="' + value + '" style="padding-top: 0 !important; padding-bottom: 1px !important;">' + text + '</a></li>';
    }

    function filterConfig($field, filterconfigOptions) {
        $field.removeClass('ui-corner-all');
        $field.addClass('ui-corner-left');

        var closeTimeout;
        var $htmlConfig = $('<button title="" class="ui-widget ui-corner-right ui-widget-content" style="cursor: pointer; border-left: 0; padding: 0; background: #ddd;"><i class="ui-icon ui-icon-triangle-1-e" style="font-size: 11px; width: 8px; margin-top: -2.5px;"></i></button>');
        var $htmlConfigMen = $('<ul style="position: absolute; width: 110px; z-index: 100;"></ul>');

        if (typeof filterconfigOptions === 'undefined' || (typeof filterconfigOptions.equal != 'undefined' && filterconfigOptions.equal)) {
            $(filterConfigOption('equal', 'Uguale a')).appendTo($htmlConfigMen);
        }

        if (typeof filterconfigOptions === 'undefined' || (typeof filterconfigOptions.start != 'undefined' && filterconfigOptions.start)) {
            $(filterConfigOption('start', 'Inizia per')).appendTo($htmlConfigMen);
        }

        if (typeof filterconfigOptions === 'undefined' || (typeof filterconfigOptions.contain != 'undefined' && filterconfigOptions.contain)) {
            $(filterConfigOption('contain', 'Contiene')).appendTo($htmlConfigMen);
        }

        if (typeof filterconfigOptions === 'undefined' || (typeof filterconfigOptions.end != 'undefined' && filterconfigOptions.end)) {
            $(filterConfigOption('end', 'Finisce per')).appendTo($htmlConfigMen);
        }

        if (typeof filterconfigOptions === 'undefined' || (typeof filterconfigOptions.empty != 'undefined' && filterconfigOptions.empty)) {
            $(filterConfigOption('empty', 'Vuoto')).appendTo($htmlConfigMen);
        }

        $htmlConfigMen.find('li:not(:first)').css('margin-top', '2px');
        $htmlConfigMen.menu().hide();

        $htmlConfigMen.find('a').on('click', function (e) {
            var isActive = $(this).parent().is('.ui-state-active');

            $htmlConfigMen.find('.ui-state-active').removeClass('ui-state-active');
            $htmlConfigMen.find('.ui-icon-check').remove();
            console.log(isActive);

            if (isActive) {
                $htmlConfig.tooltip('option', 'content', '');
                $htmlConfig.css('color', '');
                $field.data('filterConfig', false);
            } else {
                $(this).prepend('<i class="ui-icon ui-icon-check" style="float: none; position: relative; top: 0; left: 0; margin: -2px 0px 0px -4px;"></i>').parent().addClass('ui-state-active');
                $htmlConfig.tooltip('option', 'content', $(this).text());
                $htmlConfig.css('color', 'red');
                $field.data('filterConfig', $(this).attr('data-filter'));
            }

            $htmlConfigMen.hide();
            return false;
        });

        $htmlConfigMen.on('mouseleave', function () {
            closeTimeout = setTimeout(function () {
                $htmlConfigMen.hide('fold', {}, 100);
            }, 800);
        }).on('mouseenter', function () {
            clearTimeout(closeTimeout);
        });

        $htmlConfig.on('click', function (e) {
            clearTimeout(closeTimeout);

            if ($htmlConfigMen.is(':visible')) {
                $htmlConfigMen.hide('fold', {}, 100);
                return false;
            }

            closeTimeout = setTimeout(function () {
                $htmlConfigMen.hide('fold', {}, 100);
            }, 1600);

            $htmlConfigMen.show().position({
                my: "left top",
                at: "right+2 top",
                of: $htmlConfig
            }).hide().show('fold', {}, 100);

            return false;
        });

        $htmlConfig.insertAfter($field);
        $htmlConfigMen.insertAfter($htmlConfig);

        creaTooltip($htmlConfig);
    }

    function inputTooltip(id, text) {
        var $field = $(protSelector('#' + id)),
            info_id = protSelector(id + '_inputTooltip');

        var $info = $('#' + info_id);

        if ($info.length) {
            /*
             * Elemento già istanziato.
             */

            $info.attr('title', text);

            if (!text) {
                $info.hide();
            } else {
                $info.show();
            }

            return;
        }

        $info = $('<span id="' + id + '_inputTooltip" class="ui-icon ui-icon-info ita-input-tooltip-icon"></span>');
        $info.attr('title', text);

        $field.parent().append($info);

        $info.tooltip({
            tooltipClass: 'ita-input-tooltip',
            show: {effect: 'fade', duration: 100},
            hide: {effect: 'fade', duration: 100},
            content: function () {
                return $(this).prop('title');
            },
            position: {
                my: "center+1 bottom-18",
                at: "center top",
                using: function (position, feedback) {
                    $(this).css(position);
                    $("<div>")
                        .addClass('arrow')
                        .addClass(feedback.vertical)
                        .addClass(feedback.horizontal)
                        .appendTo(this);
                }
            }
        });

        if (!text) {
            $info.hide();
        }
    }

    return {
        filterConfig: filterConfig,
        inputTooltip: inputTooltip
    };
})();