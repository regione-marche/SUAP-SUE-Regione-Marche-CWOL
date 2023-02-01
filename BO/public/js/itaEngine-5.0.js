/**
 *  itaEngine JavaScript framework
 *  Versione: 5.0 - 23.09.2014
 *  (c) 2009 Italsoft SNC
 *
 *  itaEngine is freely distributable under the terms of ...............
 *  For details, see the Italsoft web site: http://italsoft-mc.it
 * 
 *--------------------------------------------------------------------------*/

var urlController = './controller.php';
var uploaders_pl = new Array();
var token = '';
var tmpToken = '';
var ita_silentClose = false;
var enableBlockMsg = true;
var dialogLayoutStack = new Array();
var gridLastSel = new Array();
var gridInlineLock = new Array();
var gridSerializedRow = new Array();
var homeLayout = null;
var dialogLightBoxOpt = {};
var dialogShortCutMap = new Array();
var dialogLastFocus = new Array();
var currDialogFocus = '';
var resizeTimer = null;
var onSelectRowTimer = null;
var editRowTimer = null;
var calendarParams = new Array();
var ieChildWindows = new Array();
var jsPlumbInstances = {Renderers: {}, ViewInfo: {}};
var ieParent;
var scrollBarWidth = 8;
var bottone;
var clientEngine = 'itaEngine';
var isLocalStorageAvailable = false;

var defaultNumberFormatterOptions = {
    precision: 2,
    decimal: ',',
    thousand: '.',
    prefix: '',
    suffix: ''
};

var itaEngine = {
    Version: '5.0',
    require: function (libraryName) {
        // inserting via DOM fails in Safari 2.0, so brute force approach
        document.write('<script type="text/javascript" src="' + libraryName + '"><\/script>');
    },
    plUploaders: new Array(),
    load: function () {
    }
};

var desktopContext = false;

var blockUIStandardParams = function () {
    var top = ($(window).height() - 60) / 2 + 'px', left = ($(window).width() - 60) / 2 + 'px';

    return {
        overlayCSS: {
            backgroundColor: 'transparent',
            'z-index': 99999999
        },
        css: {
            padding: '5px',
            width: '60px',
            top: top,
            left: left,
            backgroundColor: 'transparent',
            '-webkit-border-radius': '10px',
            '-moz-border-radius': '10px',
            'border-radius': '10px',
            opacity: .5,
            color: '#fff'
        },
        message: '<img class="ita-block-events" align="center" valing="middle" src="public/css/images/wait.gif" />'
    };
};

itaEngine.load();

$(function () {
    scrollBarWidth = getScrollBarWidth();
    $.blockUI.defaults.fadeIn = 0;
    $.blockUI.defaults.fadeOut = 0;
    $.blockUI.defaults.message = '';
    $.datepicker.setDefaults($.datepicker.regional[ "it" ]);

    if ("onhelp" in window)
        window.onhelp = function () {
            return false;
        };

    $(document).jkey('f1,f2,f3,f4,f5,f6,f7,f8,f10,f11,f12,pageup,pagedown', function (key) {
        // previene il default
        return false;
    }).keydown(function (e) {
        if (e.keyCode == 8) {
            switch (e.target.nodeName) {
                case 'HTML':
                case 'BODY':
                case 'TABLE':
                case 'TBODY':
                case 'TR':
                case 'TD':
                case 'DIV':
                    e.preventDefault();
            }
        }
    });

    var test = '';
    if ($.url(true).param('test') != undefined)
        test = '?test=' + $.url(true).param('test');
    urlController += test;

    jQuery.itaGetForm = function (id) {
        return $('#' + protSelector(id));
    };

    $.fullCalendar.Calendar.defaults.titleRangeSeparator = ' - ';

    jQuery.fn.extend({
        itaGetParentForm: function () {
            return this.closest('form, div.ita-model');
        },
        itaGetId: function () {
            return this.attr('id');
        },
        itaGetModelBackend: function () {
            return this.length > 0 && this.metadata() && this.metadata().modelBackend ? this.metadata().modelBackend : undefined;
        },
        itaGetChildForms: function () {
            return this.find('form, div.ita-model');
        },
        itaGetHostForm: function () {
            if (this.length > 0 && this.metadata() && this.metadata().serializeHostForm == true) {
                return this.closest('form');
            } else {
                return this;
            }
        },
        andFind: function (selector) {
            return this.find(selector).addBack(selector);
        }
    });

    /*
     * Filtro jQuery per selezionare solo gli elementi
     * contenuti in uno specifico id.
     * Es. $(':itaModel(proAnacat)');
     */
    jQuery.expr[':'].itaModel = function (a, i, m) {
        return jQuery(a).closest('#' + m[3]).length > 0;
    };

    /* Metodi Tree */
    var orgExpandNode = $.fn.jqGrid.expandNode, orgCollapseNode = $.fn.jqGrid.collapseNode;
    $.jgrid.extend({
        expandNode: function (rc) {
            var postdata = $(this).getGridParam('postData');
            $(this).trigger('ita-expand-node', [rc._id_, postdata]);
            return orgExpandNode.call(this, rc);
        },
        collapseNode: function (rc) {
            var postdata = $(this).getGridParam('postData');
            $(this).trigger('ita-collapse-node', [rc._id_, postdata]);
            return orgCollapseNode.call(this, rc);
        }
    });

    jQuery.colorpicker.regional[""].none = 'Nessuno';
    jQuery.colorpicker.regional[""].cancel = 'Annulla';
    jQuery.colorpicker.swatches.custom_array = [
        {name: 'red', r: 1, g: 0, b: 0},
        {name: 'orange', r: 1, g: 0.5, b: 0},
        {name: 'yellow', r: 1, g: 1, b: 0},
        {name: 'charteusegreen', r: 0.5, g: 1, b: 0},
        {name: 'green', r: 0, g: 1, b: 0},
        {name: 'springgreen', r: 0, g: 1, b: 0.5},
        {name: 'cyan', r: 0, g: 1, b: 1},
        {name: 'azure', r: 0, g: 0.5, b: 1},
        {name: 'blue', r: 0, g: 0, b: 1},
        {name: 'violet', r: 0.5, g: 0, b: 1},
        {name: 'magenta', r: 1, g: 0, b: 1},
        {name: 'rose', r: 1, g: 0, b: 0.5}
    ];

    itaGetLib('libs/italsoft/itaFieldUtilities.js', 'itaFieldUtilities');

    try {
        if (window.localStorage) {
            window.localStorage.getItem('');
            isLocalStorageAvailable = true;
        }
    } catch (e) {
        console.error('itaEngine: localStorage non disponibile (' + e.message + ')');
    }
});

$(window).resize(function () {
    clearTimeout(resizeTimer);
    resizeTimer = setTimeout(function () {

        // Aggiunta per gestione resize - Carlo 29.07.15
        window.itaWindowSize = window.innerWidth + '' + window.innerHeight;

        resizeTabs();
        unlockResizeGrid('');
        resizeGrid('', true);

    }, 400);
});


// Aggiunta per gestione resize - Carlo 29.07.15
window.itaWindowSize = window.innerWidth + '' + window.innerHeight;

$(window).on('beforeunload', function () {
    if (!navigator.sendBeacon) {
        itaGo('ItaCall', '', {
            event: 'onunload',
            asyncCall: false
        });
    }
});

$(window).on('unload', function () {
    if (navigator.sendBeacon) {
        var postData = new FormData();
        postData.append('clientEngine', clientEngine);
        postData.append('event', 'onunload');
        postData.append('TOKEN', token);
        postData.append('tmpToken', tmpToken);
        navigator.sendBeacon(urlController, postData);
    }
});

$(document).on('focusin', function (e) {
    if ($(e.target).closest(".mce-window").length) {
        e.stopImmediatePropagation();
    }
});

function setFocus() {
    window.focus();
}
function loadSlider() {
    var defaultSize = 75;
    $("#fontsize_slider").slider({
        value: defaultSize,
        min: 30,
        max: 200,
        step: 5,
        slide: function (event, ui) {
            $('body').css('fontSize', ui.value + '%');
            $("#fontsize").html('Zoom ' + ui.value + '%');
        },
        stop: function (event, ui) {
            resizeTabs();
            resizeGrid('', true);
        }
    });
    $("#fontsize").html('Zoom ' + $("#fontsize_slider").slider("value") + '%');
}

function loadTabs() {
    $("#mainTabs").tabs({
        activate: function (event, ui) {
            resizeGrid($(ui.newPanel).children("div").attr("id"), true);//, true);
            fullCalendarRender($(ui.newPanel).find('.ita-calendar').toArray());
            desktopContext = ui.newPanel;
            if ($(ui.newPanel).children("div").attr("id") != "ita-home-content") {
                if (($(ui.newPanel).find('.ita-dialog-wrapper').eq(0).attr("id") != undefined)) {
                    currDialogFocus = $(ui.newPanel).find('.ita-dialog-wrapper').eq(0).attr("id");
                }
            }
        }
    });
    $("#mainTabs").tabs('option', 'active', 0);

    // Funzione per gestione resize - Carlo 29.07.15
    $('#mainTabs').on('tabsactivate', function (e, ui) {
        var divTab = $(ui.newPanel).children('div').attr('id');
        var wrapper = $(ui.newPanel).find('.ita-dialog-wrapper').eq(0).attr('id');

        if (divTab == 'ita-home-content' && homeLayout.updatedToResize != window.itaWindowSize) {
            homeLayout.updatedToResize = window.itaWindowSize;
            homeLayout.resizeAll();
        }

        if (divTab != 'ita-home-content' && wrapper != undefined && dialogLayoutStack[wrapper] && dialogLayoutStack[wrapper].updatedToResize != window.itaWindowSize) {
            dialogLayoutStack[wrapper].updatedToResize = window.itaWindowSize;
            dialogLayoutStack[wrapper].resizeAll();
        }
    });
}

function unlockResizeGrid(container) {
    var griglie;
    if (container != '') {
        griglie = $("#" + container).find('.ui-jqgrid.ita-resizeGridLock');
    } else {
        griglie = $('.ui-jqgrid.ita-resizeGridLock');
    }
    griglie.each(function () {
        $(this).removeClass('ita-resizeGridLock');
    });
}

function refreshItaTabs(container) {
    var tabs = $("#" + container).find('.ita-tab');
    tabs.each(function () {
        $(this).tabs({
            activate: function (event, ui) {

                tinyDeActivate($(ui.newPanel).find('textarea.ita-edit-tinymce').toArray());
                fullCalendarRender($(ui.newPanel).find('.ita-calendar').toArray());

                $(ui.newPanel).find('textarea.ita-edit-tinymce').each(function () {
                    tinyActivate($(this).attr('id'));
                });
                $(ui.newPanel).find('.ita-plupload-uploader').each(function () {
                    pluploadActivate($(this).attr('id'));
                });
                $(ui.newPanel).find('.ita-flowchart').each(function () {
                    itaJsPlumbHelper.activate(this.id);
                });
                resizeGrid($(ui.newPanel).attr('id'), true, true);
            }
        });
    });
}

function itaCurrencyFormat(number, precision, dec_sep, tho_sep) {
    precision = Math.abs(parseInt(precision));
    dec_sep = dec_sep == undefined ? defaultNumberFormatterOptions.decimal : dec_sep;
    tho_sep = tho_sep == undefined ? defaultNumberFormatterOptions.thousand : tho_sep;

    number = String(number);

    /*
     * Ricavo l'eventuale segno
     */
    var sign = number.substr(0, 1) === '-' ? '-' : '';

    /*
     * Rimuovo i caratteri estranei 
     */
    number = number.replace(/[^0-9.,]/g, '');

    /*
     * Separo il numero nella varie parti separati da "," o "."
     */
    var s = (number.length ? number : '0').split(/,|\./);

    /*
     * Se precision = 0, aggiungo una parte decimale fittizia.
     */
    if (precision == 0) {
        s.push('');
    }

    /*
     * Se ci sono meno di due parti, ne aggiungo di vuote per raggiungere almeno
     * una parte intera ed una decimale
     */
    while (s.length < 2) {
        s.push('');
    }

    /*
     * Prendo l'ultima parte
     */
    var m = s.splice(-1, 1)[0];

    /*
     * Aggiungo 0 alla parte decimale finchÈ non raggiunge la lunghezza
     * di "precision"
     */
    while (m.length < precision) {
        m += '0';
    }

    if (m.length > precision) {
        m = m.substr(0, precision);
    }

    /*
     * Prendo la parte intera e rimuovo gli "0" avanti
     */
    var r = s.join('').replace(/^0+/, '');
    r = sign + (r.length ? r : '0');

    /*
     * Ritorno il numero formattato, includendo la parte decimale se "precision
     * > 0"
     */
    return r.replace(/\B(?=(\d{3})+(?!\d))/g, tho_sep) + (precision > 0 ? dec_sep + m.substr(0, precision) : '');
}

function itaCurrencyFormatterOptions(fieldID) {
    var metadata = $('#' + protSelector(fieldID)).metadata(), formatterOptions = $.extend({}, defaultNumberFormatterOptions);

    if (metadata.formatterOptions) {
        for (var k in metadata.formatterOptions) {
            formatterOptions[k] = metadata.formatterOptions[k];
        }
    }

    if (formatterOptions.decimal == '') {
        formatterOptions.decimal = defaultNumberFormatterOptions.decimal;
    }

    return formatterOptions;
}

function getItaCurrencyValue($currencyField) {
    var formatterOptions = itaCurrencyFormatterOptions($currencyField.attr('id'));
    return itaCurrencyFormat($currencyField.val(), formatterOptions.precision, '.', '');

}
function getItaCurrencyString($currencyField) {
    var formatterOptions = itaCurrencyFormatterOptions($currencyField.attr('id'));
    return formatterOptions.prefix + itaCurrencyFormat($currencyField.val(), formatterOptions.precision, formatterOptions.decimal, formatterOptions.thousand) + formatterOptions.suffix;
}

function itaCurrencyFormatterDisplayOn($currencyField) {
    if ($currencyField.hasClass('currency-display')) {
        return;
    }

//    $currencyField.data('ita-value', getItaCurrencyValue($currencyField));
    $currencyField.val(getItaCurrencyString($currencyField));
    $currencyField.addClass('currency-display');
}

function itaCurrencyFormatterDisplayOff($currencyField) {
    if (!$currencyField.hasClass('currency-display') || $currencyField.hasClass('ita-readonly')) {
        return;
    }

    $currencyField.val(getItaCurrencyValue($currencyField));
    $currencyField[0].setSelectionRange(0, $currencyField.val().length);

    $currencyField.removeClass('currency-display');
}

function itaInputMask() {
    $('input.ita-datepicker').mask("99/99/9999");
    $('input.ita-date').mask("99/99/9999");
    $('input.ita-time').mask("99:99");
    $('input.ita-month').mask("99/9999");

    $('input.ita-edit-time').each(function () {
        var $this = $(this), format = $this.metadata().format || 'hi';
        switch (format.toLowerCase()) {
            case 'his':
                $this.mask("99:99:99");
                break;

            case 'hi':
            default:
                $this.mask("99:99");
                break;
        }
    });
}

function itaInputUnmask() {
    $('input.ita-datepicker').unmask();
    $('input.ita-date').unmask();
    $('input.ita-time').unmask();
    $('input.ita-edit-time').unmask();
    $('input.ita-month').unmask();
}

function itaInputMaskWithin($element) {
    $element.find('input.ita-datepicker').addBack('input.ita-datepicker').mask("99/99/9999");
    $element.find('input.ita-date').addBack('input.ita-date').mask("99/99/9999");
    $element.find('input.ita-time').addBack('input.ita-time').mask("99:99");
    $element.find('input.ita-month').addBack('input.ita-month').mask("99/9999");

    $element.find('input.ita-edit-time').addBack('input.ita-edit-time').each(function () {
        var $this = $(this), format = $this.metadata().format || 'hi';
        switch (format.toLowerCase()) {
            case 'his':
                $this.mask("99:99:99");
                break;

            case 'hi':
            default:
                $this.mask("99:99");
                break;
        }
    });
}

function itaInputUnmaskWithin($element) {
    $element.find('input.ita-datepicker').addBack('input.ita-datepicker').unmask();
    $element.find('input.ita-date').addBack('input.ita-date').unmask();
    $element.find('input.ita-time').addBack('input.ita-time').unmask();
    $element.find('input.ita-edit-time').addBack('input.ita-edit-time').unmask();
    $element.find('input.ita-month').addBack('input.ita-month').unmask();
}

function parseDateTimeWithin($element) {
    $element.find('input.ita-datepicker').addBack('input.ita-datepicker').each(function () {
        var data;
        var ret;
        var parsedDate = $(this).val().substr(0, 10);
        if (isDate(parsedDate, 'yyyy-MM-dd')) {
            data = new Date(getDateFromFormat(parsedDate, 'yyyy-MM-dd'));
            ret = formatDate(data, 'dd/MM/yyyy');
            $(this).addClass('ita-isodate');
            $(this).val(ret);
        } else {
            if (isDate(parsedDate, 'yyyyMMdd')) {
                data = new Date(getDateFromFormat(parsedDate, 'yyyyMMdd'));
                ret = formatDate(data, 'dd/MM/yyyy');
                $(this).addClass('ita-eqdate');
                $(this).val(ret);
            }
        }
    });


    $element.find('input.ita-date').addBack('input.ita-date').each(function () {
        var data;
        var ret;
        var parsedDate = $(this).val().substr(0, 10);
        if (isDate(parsedDate, 'yyyy-MM-dd')) {
            data = new Date(getDateFromFormat(parsedDate, 'yyyy-MM-dd'));
            ret = formatDate(data, 'dd/MM/yyyy');
            $(this).addClass('ita-isodate');
            $(this).val(ret);
        } else {
            if (isDate(parsedDate, 'yyyyMMdd')) {
                data = new Date(getDateFromFormat(parsedDate, 'yyyyMMdd'));
                ret = formatDate(data, 'dd/MM/yyyy');
                $(this).addClass('ita-eqdate');
                $(this).val(ret);
            }
        }
    });

    $element.find('input.ita-month').addBack('input.ita-month').each(function () {
        var month;
        var ret;
        if (isDate($(this).val(), 'yyyy-MM')) {
            month = new Date(getDateFromFormat($(this).val(), 'yyyy-MM'));
            ret = formatDate(month, 'MM/yyyy');
            //$(this).addClass('ita-isodate');
            $(this).val(ret);
        } else {
            if (isDate($(this).val(), 'yyyyMM')) {
                month = new Date(getDateFromFormat($(this).val(), 'yyyyMM'));
                ret = formatDate(month, 'MM/yyyy');
                //$(this).addClass('ita-eqdate');
                $(this).val(ret);
            }
        }
    });
}

function resizeDivs(container) {
    var $divs;

    if (container != '') {
        $divs = $('#' + container).find('.ita-expand-div').parent();
    } else {
        $divs = $('.ita-expand-div').parent();
    }

    $divs.each(function () {
        var $div = $(this), div_height = $div.height(), expand_divs = [], other_height = 0;

        $div.children().each(function () {
            if ($(this).hasClass('ita-expand-div')) {
                expand_divs.push($(this));
            } else {
                other_height += $(this).outerHeight();
            }
        });

        $.each(expand_divs, function (k, $expand_div) {
            var outer_diff = $expand_div.outerHeight(true) - $expand_div.height();
            $expand_div.height(((div_height - other_height) / expand_divs.length) - outer_diff);
        });
    });
}

function resizeGrid(container, reload, forceResize) {
    resizeDivs(container);

    var griglie;
    if (container != '') {
        griglie = $("#" + container).find('.ui-jqgrid');
    } else {
        griglie = $('.ui-jqgrid');
    }

    griglie.each(function () {
        if ($(this).hasClass('ita-resizeGridLock') && forceResize != true) {
            return;
        }
        var grid_mrg_l = 0;
        var grid_brd_l = 0;
        var grid_mrg_r = 0;
        var grid_brd_r = 0;
        var grid_mrg_t = 0;
        var grid_mrg_b = 0;
        var grid_brd = 4;
        var grid_titlebar = 0;
        var grid_header = 0;
        var grid_pager = 0;
        var grid_footerrow = 0;
        var new_width;
        var new_height;
        var cont_height = 0;
        var cont_pt = 0;
        var cont_pb = 0;
        var fatto;
        var $parentDiv = $(this).parents('div:eq(0)');

        // 
        // Nuova Larghezza
        //
        if ($(this).css("margin-left") != undefined)
            grid_mrg_l = parseInt($(this).css("margin-left"));
        if ($(this).css("margin-right") != undefined)
            grid_mrg_r = parseInt($(this).css("margin-right"));
        if ($(this).css("border-left") != undefined)
            grid_brd_l = parseInt($(this).css("border-left"));
        if ($(this).css("border-right") != undefined)
            grid_brd_r = parseInt($(this).css("border-right"));

        if ($parentDiv.is(':hidden')) {
            if ($parentDiv[0]._resizeGridTimer) {
                return;
            }

            $parentDiv[0]._resizeGridTimer = setInterval(function () {
                if ($parentDiv.is(':hidden')) {
                    return;
                }

                clearInterval($parentDiv[0]._resizeGridTimer);
                delete $parentDiv[0]._resizeGridTimer;

                resizeGrid(container, reload, forceResize);
            }, 50);
            return;
        }

        new_width = $parentDiv.width();
        if (new_width == undefined)
            return;

        new_width = parseInt(new_width) - grid_mrg_l - grid_mrg_r;
        if (new_width <= 0)
            return;

        //
        // Nuova Altezza
        //
        if ($(this).css("margin-top") != undefined)
            grid_mrg_t = parseInt($(this).css("margin-top"));
        if ($(this).find('.ui-jqgrid-titlebar').is(':visible') && $(this).find('.ui-jqgrid-titlebar').outerHeight(true) != undefined)
            grid_titlebar = parseInt($(this).find('.ui-jqgrid-titlebar').outerHeight(true));
        if ($(this).find('.ui-jqgrid-hdiv').is(':visible') && $(this).find('.ui-jqgrid-hdiv').outerHeight(true) != undefined)
            grid_header = parseInt($(this).find('.ui-jqgrid-hdiv').outerHeight(true));
        if ($(this).find('.ui-jqgrid-pager').is(':visible') && $(this).find('.ui-jqgrid-pager').outerHeight(true) != undefined)
            grid_pager = parseInt($(this).find('.ui-jqgrid-pager').outerHeight(true));
        if ($(this).find('.ui-jqgrid-ftable').is(':visible') && $(this).find('.ui-jqgrid-pager').outerHeight(true) != undefined)
            grid_footerrow = parseInt($(this).find('.ui-jqgrid-ftable').outerHeight(true));
        if ($(this).css("margin-bottom") != undefined)
            grid_mrg_b = parseInt($(this).css("margin-bottom"));

        cont_height = $parentDiv.height();
        if (cont_height == undefined)
            return;

        cont_height = parseInt(cont_height);

        if ($parentDiv.css("padding-top") != undefined)
            cont_pt = parseInt($parentDiv.css("padding-top"));
        if ($parentDiv.css("padding-bottom") != undefined)
            cont_pb = parseInt($parentDiv.css("padding-bottom"));

        cont_height = cont_height - cont_pt - cont_pb;

        if (grid_header < 0)
            grid_header = 22;
        if (grid_pager < 0)
            grid_pager = 22;
        if (grid_footerrow < 0)
            grid_footerrow = 0;
        if (grid_titlebar < 0)
            grid_titlebar = 22;

        new_height = cont_height - grid_mrg_t - grid_titlebar - grid_header - grid_pager - grid_footerrow - grid_mrg_b;

        if (new_height <= 0)
            return;

        //var pp=$(this).parent('div').eq(0);
        //var new_height = $(this).parents('div:eq(0)').innerHeight();
        //var new_height = parseInt($(this).parents('div:eq(0)').innerHeight())-parseInt($(this).parents('div:eq(0)').css("padding-top"))-parseInt($(this).parents('div:eq(0)').css('padding-bottom'));
        //alert(new_height);

        fatto = 0;
        $(this).find('.ita-jqGrid-activated.ita-jqGrid-resizetoparent').each(function () {
            if (new_width - grid_mrg_l - grid_mrg_r - grid_brd > 0) {
                $(this).setGridWidth(new_width - grid_mrg_l - grid_mrg_r - grid_brd);

                /*
                 * Se c'Ë lo shrinkToFit, verifica la presenza di colonne con minWidth
                 */
                var shrinkToFit = $(this).jqGrid('getGridParam', 'shrinkToFit');
                if (shrinkToFit) {
                    var gridColModel = $(this).getGridParam('colModel');
                    for (var i in gridColModel) {
                        if (gridColModel[i].minWidth && gridColModel[i].minWidth > gridColModel[i].width) {
                            this.blockLocalStorage = true;
                            $(this).jqGrid('setColWidth', gridColModel[i].name, gridColModel[i].minWidth, false);
                        }
                    }
                }

                if (new_height > 0) {
                    $(this).setGridHeight(new_height);
                    var gridRowH;
                    gridRowH = $(this).attr('data-itagridrowheight');

                    if (!gridRowH) {
                        gridRowH = $(this).find('tr.jqgrow').height();
                        if (!gridRowH) {
                            gridRowH = 23;
                        } else {
                            $(this).find('tr.jqgrow').height(gridRowH);
                        }
                    }

                    var num = parseInt(new_height / parseInt(gridRowH));
                    var pgInput = $(this).jqGrid('getGridParam', 'pginput');
                    var pgButtons = $(this).jqGrid('getGridParam', 'pgbuttons');

                    $(this).find('.ui-state-highlight').removeClass('ui-state-highlight');

                    if (pgButtons == true || pgInput == true) {
                        var metaData = $(this).metadata();

                        if (typeof metaData.reloadOnResize === 'undefined' || metaData.reloadOnResize == true) {
                            $(this).jqGrid('setGridParam', {
                                rowNum: num
                            });
                            if (reload == true) {
                                $(this).trigger("reloadGrid");
                            }
                        }
                    }
                }
                fatto = 1;
            }
        });
        if (fatto == 1) {
            $(this).addClass('ita-resizeGridLock');
        }
    });
}


//
// Individua tutti i div panel e esegue il resize dell'oggetto layout'
//
//function resizeTabs(){
//    $('body').height($(window).height());
//    $('#mainTabs').children('div').each(function(){
//        var curtab=$(this);
//        alert(curtab.attr('id'));        
//        if(curtab.attr('id') == 'ita-home'){
//            curtab.css("minHeight",maxh).css("height",'auto');
//            curtab.css("width",$(window).width());
//            if (homeLayout != null){
//                curtab.find('#ita-home-content').css("minHeight",maxh).css("height",'auto');
//                curtab.find('#ita-home-content').css("width",$(window).width());
//                homeLayout.resizeAll();
//            }else{
//                curtab.find('#ita-home-content').css("minHeight",maxh).css("height",'auto');
//                curtab.find('#ita-home-content').css("width",$(window).width());
//            }            
//            curtab.find('#ita-controlpad').css("minHeight",maxh).css("height",maxh);
//            curtab.find('#ita-controlpad').css("width",$(window).width()-curtab.find('#menPersonal').eq(0).width()-5);
//        }else{
//            maxh=$(window).height()-$("#desktopHeader").height()-$("#mainTabs > ul").height()-$("#mainTabs .appPath:last").height();
//            curtab.find('.ita-app').css("minHeight",maxh-8).css("height",maxh-8);
//            curtab.find('.ita-app').css("width",$(window).width()-8);
//            
//        }
//        var wrapper=curtab.find('.ita-dialog-wrapper').eq(0).attr('id');
//        if( wrapper != undefined ){
//            if(dialogLayoutStack[wrapper] != undefined){
//                dialogLayoutStack[wrapper].resizeAll();
//            }
//        }
//    });
//}

function resizeTabs() {
    $('body').height($(window).height());
    $('#mainTabs').children('div').each(function () {
        var curtab = $(this);
        var maxh = $(window).height() - $("#desktopHeader").height() - $("#mainTabs > ul").height() - $("#mainTabs .appPath:last").height();
        curtab.find('.ita-app').css("minHeight", maxh - 8).css("height", maxh - 8);
        curtab.find('.ita-app').css("width", $(window).width() - 8);

        //        curtab.find('#ita-home').css("minHeight",maxh).css("height",'auto');
        //        curtab.find('#ita-home').css("width",$(window).width());
        if (curtab.attr('id') == 'ita-home') {
            if (homeLayout != null) {
                curtab.find('#ita-home-content').css("minHeight", maxh).css("height", 'auto');
                curtab.find('#ita-home-content').css("width", $(window).width());
                homeLayout.resizeAll();

                // Aggiunta per gestione resize - Carlo 29.07.15
                if (curtab.is(':visible')) {
                    homeLayout.updatedToResize = window.itaWindowSize;
                }
            } else {
                curtab.find('#ita-home-content').css("minHeight", maxh).css("height", 'auto');
                curtab.find('#ita-home-content').css("width", $(window).width());
            }
            curtab.find('#ita-controlpad').css("minHeight", maxh).css("height", maxh);
            curtab.find('#ita-controlpad').css("width", $(window).outerWidth(true) - curtab.find('#menPersonal-resizer').eq(0).outerWidth(true) - curtab.find('#menPersonal').eq(0).outerWidth(true) - scrollBarWidth - 5);
        }

        var wrapper = curtab.find('.ita-dialog-wrapper').eq(0).attr('id');

        if (wrapper != undefined) {
            if (dialogLayoutStack[wrapper] != undefined) {
                dialogLayoutStack[wrapper].resizeAll();

                // Aggiunta per gestione resize - Carlo 29.07.15
                if (curtab.is(':visible')) {
                    dialogLayoutStack[wrapper].updatedToResize = window.itaWindowSize;
                }
            }
        }
    });
}

function itaImplode(leaf, matchTag) {
    return $(leaf).closest(matchTag);
//    var Iam = $(leaf).get(0);
//    if (typeof (Iam.tagName) != undefined) {
//        if (Iam.tagName.toUpperCase() == matchTag.toUpperCase()) {
//            return Iam;
//        }
//    }
//    var leafparent = $(leaf).parent().get(0);
//    if (typeof (leafparent) == undefined || leafparent.tagName == undefined) {
//        return false;
//    }
//    if (leafparent.tagName.toUpperCase() == matchTag.toUpperCase()) {
//        return leafparent;
//    } else {
//        return itaImplode(leafparent, matchTag);
//    }
}

function protSelector(selector) {
    var pa = "\\["; // PROTEGGO LE PARENTESI PER FAR FUNZIONARE I SELETTORI CSS
    var pc = "\\]";
    var newSelector = selector.replace(/\[/g, pa);
    newSelector = newSelector.replace(/\]/g, pc);
    return newSelector;
}

function closeMe()
{
    var win = window.open("", "_self");
    win.close();
}

function getCurrDialog() {
    var maxZ = 0;
    var topDiag = null;
    $('.ui-dialog-content').each(function () {
        if ($(this).parents('.ui-dialog').css('z-index') >= maxZ) {
            maxZ = $(this).parents('.ui-dialog').css('z-index');
            topDiag = $(this).attr('id');
        }
    });
    if (topDiag == null) {
        $('.ita-appPane').each(function () {
            if ($(this).parents('.ui-tabs-panel').hasClass('ui-tabs-hide') == false) {
                topDiag = $(this).find('.ita-dialog-wrapper').eq(0).attr('id');

            }
        });
    }
    return topDiag;
}

function closeCurrDialog() {
    var topDiag = "#" + getCurrDialog();
    if (!topDiag)
        return;
    $(topDiag).dialog('close');
    $(topDiag).dialog('destroy');
    $(topDiag).remove();
}

function errorDialogMessage(XMLHttpRequest, status, dataRequested) {
    function getQueryValue(key) {
        return dataRequested.indexOf(key + '=') > -1 ? dataRequested.split(key + '=')[1].split('&')[0] : '';
    }
    
    if (XMLHttpRequest.status == 0) {
        $('body').append('<div id="dialogItaEngineError" title="Attenzione">\
<div class="ita-box ui-state-error ui-corner-all ita-Wordwrap" style="padding: 15px 0;">\
<span class="ui-icon ui-icon-alert" style="float: left; margin: 0 .5em;"></span>\
Connessione di rete non disponibile.\
</div></div>');

        $('#dialogItaEngineError').dialog({
            modal: true,
            resizable: false,
            width: 350,
            buttons: {
                Ok: function () {
                    $(this).dialog("close");
                }
            },
            close: function () {
                $(this).remove();
            }
        });
        
        return;
    }

    var tmpDiv = document.createElement('div');
    tmpDiv.innerText = XMLHttpRequest.responseText;

    var errorData = 'Id: ' + getQueryValue('id') + '<br>Event: ' + getQueryValue('event') + '<br>Model: ' + getQueryValue('model');
    if (dataRequested.indexOf('&rowid=') > -1)
        errorData += '<br>Rowid: ' + getQueryValue('rowid');
    var errorDetails = ('<b>Dettagli:</b><br><i>(' + status + ')</i><br>' + 'Status Code: ' + XMLHttpRequest.status + '<br>' + errorData + '<br>' + XMLHttpRequest.getAllResponseHeaders() + '<br><br><b>Response:</b><br>' + tmpDiv.innerHTML).replace(/(\r\n|\n|\r)/gm, "<br>");
    $('body').append('<div id="dialogItaEngineError" title="Comportamento imprevisto"> \
<div class="ita-box ui-state-error ui-corner-all ita-Wordwrap" style="overflow: auto; max-height: 550px; padding: 10px 5px;">\
<span class="ui-icon ui-icon-alert" style="float: left; margin: 0 0.2em;"></span>\
Si &egrave; verificato un errore non previsto di sistema.<br><br>' + errorDetails + ' \
</div></div>');

    var mailBody = encodeURI("Segnalazione errore itaEngine:\n\n" + errorDetails.replace(/(<b>|<\/b>|<i>|<\/i>)/gi, '').replace(/<br>/gi, "\n"));
    var mailButton = '<a id="sendError" href="mailto:?subject=Invio segnalazione errore itaEngine&body=' + mailBody + '"></a>';

    $('#dialogItaEngineError').dialog({
        modal: true,
        width: 500,
        maxWidth: 800,
        maxHeight: 500,
        buttons: {
            "Invia segnalazione": function () {
            },
            Ok: function () {
                $(this).dialog("close");
            }
        },
        close: function () {
            $(this).remove();
        }
    }).parent('div').find('.ui-dialog-buttonpane .ui-button').first().wrap(mailButton);
}

function itaGo(nomeClasse, elemento, parametri) {
    var istanzaClasse = eval('new ' + nomeClasse);
    istanzaClasse.elemento = elemento;
    if (parametri == null || parametri == '')
        parametri = new Array();
    if (typeof (parametri['formato']) == 'undefined')
        parametri['formato'] = 'xml';
    if (typeof (parametri['bloccaui']) == 'undefined')
        parametri['bloccaui'] = true;
    if (typeof (parametri['conferma']) == 'undefined')
        parametri['conferma'] = false;
    if (typeof (parametri['event']) == 'undefined')
        parametri['event'] = istanzaClasse.defaultEvent;
    if (typeof (parametri['timeout']) == 'undefined')
        parametri['timeout'] = 18000000;
    //if(typeof(parametri['testoattesa']) == 'undefined') parametri['testoattesa']='Attendere...';
    if (typeof (parametri['leggiform']) == 'undefined')
        parametri['leggiform'] = 'tutto';
    if (typeof (parametri['validate']) == 'undefined')
        parametri['validate'] = true;
    if (typeof (parametri['asyncCall']) == 'undefined')
        parametri['asyncCall'] = true; //false;
    $('.ui-state-error.ita-state-error').removeClass('ui-state-error');

    istanzaClasse.parametri = parametri;
    //if($('#boxAttesa').length==0){
    //$('body').append('<div id="boxAttesa"><img src="/italsoftimg/wait.gif" style="vertical-align:middle"/></div>');
    //}
    // esegue xmlHTTPRequest solo se pre elaborazione successa
    if (istanzaClasse.beforeRequest() == true) {

        if (istanzaClasse.parametri['conferma']) {
            if ($('#dialog').length == 0) {
                $('body').append('<div id="dialog" title="Richiesta di conferma">Confermi l\'operazione?</div>');
                //$('#dialog').hide();
            }
            $("#dialog").dialog({
                bgiframe: true,
                resizable: false,
                height: 140,
                modal: true,
                close: function (event, ui) {
                    $("#dialog").remove();
                },
                buttons: {
                    'NO': function () {
                        $(this).dialog('close');
                    },
                    'SI': function () {
                        $(this).dialog('close');
                        if (parametri['bloccaui'] == true)
                            $.blockUI();
                        istanzaClasse.sendRequest();
                    }
                }
            });
        } else {

            if (parametri['bloccaui'] == true) {
                if (enableBlockMsg == true) {
                    $.blockUI(blockUIStandardParams());
                } else {
                    $.blockUI({
                        overlayCSS: {
                            backgroundColor: 'transparent'
                        },
                        css: {
                            backgroundColor: 'transparent'
                        },
                        message: ''
                    });
                }
            }
            istanzaClasse.sendRequest();
        }
    }
    return false;
}

function loadFormAuth() {
    itaGo('ItaLoad', $('#boxAuth'), {
        bloccaui: false
    });
}

function checkAuth() {
    if (token != '' && token != '0') {
        $('.container').each(function () {
            itaGo('ItaLoad', this, {
                bloccaui: false
            });
        });
    } else {
        $('#errorAuth').show();
    }
}

/**
 * CLASSE BASE
 */

function ItaBase() {

}

ItaBase.prototype.sendRequest = function () {
    var istanza = this;
    //chiamata ajax
    istanza.post += '&clientEngine=' + clientEngine;
    $.ajax({
        //jsonp:null,
        //jsonpCallback:null,
        url: istanza.url,
        async: istanza.parametri['asyncCall'],
        cache: false,
        type: 'POST',
        data: istanza.post,
        dataType: istanza.parametri['formato'],
        contentType: 'application/x-www-form-urlencoded; charset=ISO-8859-15',
        timeout: istanza.parametri['timeout'],
        beforeSend: function (xhr) {
            xhr.setRequestHeader("Accept-Charset", "ISO-8859-15");
            xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded; charset=ISO-8859-15");
        },
        error: function (XMLHttpRequest, textStatus) {
            //			istanza.errore(textStatus, XMLHttpRequest);
            errorDialogMessage(XMLHttpRequest, textStatus, istanza.post);
            if (istanza.parametri['bloccaui'] == true) {
                $.unblockUI();
            }
        },
        success: function (risposta) {
            if (istanza.parametri['bloccaui'] == true) {
                $.unblockUI();
            }
            istanza.parseRisposta(risposta);
            istanza.afterRequest(risposta);
        }
    });
};

ItaBase.prototype.beforeRequest = function () {
    return true;
};

ItaBase.prototype.afterRequest = function (risposta) {
    //
};

ItaBase.prototype.errore = function (codice) {
    alert('Errore:' + codice);
};

ItaBase.prototype.parseRisposta = function (risposta) {

    /*
     * 18.10.2016 #nuovo-input-mask
     * Ristretto l'utilizzo di itaInputMask/Unmask solo quando necessario
     * Problema datepicker con itaTimer
     */
//    itaInputUnmask();

    $(risposta).children('root').children().each(function () {
        var idElemento;
        var tag = this.tagName;
        switch (tag) {
            case 'openDocument' :
                if (this.childNodes.length) {
                    var newwindow = window.open(this.childNodes[0].nodeValue);
                    break;
                }
                break;
            case 'hide' :
                idElemento = protSelector($(this).attr('id'));

                tinyDeActivate($("#" + idElemento).find('textarea.ita-edit-tinymce').toArray());

                var effect = $(this).attr('effect');
                var duration = $(this).attr('duration');
                $.fx.speeds._default = 0;

                //$("#"+idElemento,desktopContext).hide(effect,{},0);
                $("#" + idElemento).hide(effect, {}, parseInt(duration));

                /*
                 * Cerco se vengono nascosti elementi ita-fullscreen attivi
                 * per disattivarli.
                 */
                $("#" + idElemento).andFind('.ita-fullscreen-active').trigger('fullscreen-off');
                break;
            case 'show':
                idElemento = protSelector($(this).attr('id'));
                var effect = $(this).attr('effect');
                var duration = $(this).attr('duration');
                //$.fx.speeds._default=0;
                $("#" + idElemento).show(effect, {}, parseInt(duration));
                refreshItaTabs(idElemento);
                resizeGrid(idElemento, true);
                break;
            case 'msgBlock' :
                var parent = protSelector($(this).attr('parent'));
                var timeout = $(this).attr('timeout');
                var modal = $(this).attr('modal');
                var nodeValue = this.childNodes[0].nodeValue;
                var blockParams = {
                    fadeIn: 600,
                    fadeOut: 600,
                    timeout: timeout,
                    overlayCSS: {
                        backgroundColor: 'transparent',
                        opacity: .4,
                        'z-index': 99999999
                    },
                    css: {
                        border: 0,
                        backgroundColor: 'transparent'
                    },
                    message: '<div class="ita-thick-border ita-msgBlock ui-corner-all">' + nodeValue + '</div>'
                };

                if (parent) {
                    $('#' + parent).block(blockParams);
                } else {
                    $.blockUI(blockParams);
                }
                break;
            case 'block' :
                idElemento = protSelector($(this).attr('id'));
                var bgColor = $(this).attr('bgColor');
                var opacity = parseFloat($(this).attr('opacity'));
                $("#" + idElemento).block({
                    overlayCSS: {
                        backgroundColor: bgColor,
                        opacity: opacity,
                        cursor: 'auto'
                    }
                });
                break;
            case 'unBlock' :
                idElemento = protSelector($(this).attr('id'));
                $("#" + idElemento).unblock();
                break;
            case 'setAppTitle' :
            case 'setDialogTitle':
                if (this.childNodes.length) {
                    var idApp = $(this).attr('id');
                    var appTitle = this.childNodes[0].nodeValue;
                    if ($("#" + idApp).hasClass('ita-app')) {
                        $("#tab-label-" + idApp).html(appTitle);
                    } else if ($("#" + idApp).hasClass('ita-dialog')) {
                        var idDialog = $(this).attr('id') + "_wrapper";
                        $("#" + idDialog).dialog('option', 'title', appTitle);
                    }
                }
                break;
            case 'setDialogOption' :
                var idDialog = $(this).attr('id') + "_wrapper";
                if (this.childNodes.length) {
                    var optKey = $(this).attr('option');
                    var optObj = eval(this.childNodes[0].nodeValue);
                    $("#" + idDialog).dialog('option', optKey, optObj);
                }
                break;
            case 'showDialog' :
                var idDialog = $(this).attr('id');
                if (this.childNodes.length) {
                    itaUIDialog(idDialog);
                }
                break;

            case 'callDialogMethod' :
                var callParam = "";
                if (this.childNodes.length) {
                    callParam = this.childNodes[0].nodeValue;
                }
                var idDialog = $(this).attr('id') + "_wrapper";
                var idMethod = $(this).attr('method');
                $("#" + idDialog).dialog(idMethod);

                break;
            case 'dialog':
                var idDialog = $(this).attr('id') + "_wrapper";
                var comando = $(this).attr('comando');
                var current = false;
                switch (comando) {
                    case 'closeCurrent':
                        current = true;
                    case 'close':
                        //FIXME: DA CONTROLLARE
                        if ($("#" + $(this).attr('id')).hasClass('ita-app')) {
                            closeApp($(this).attr('id'), false);
                            break;
                        }
                        var objDialog;
                        if (current == true) {
                            objDialog = $("#" + getCurrDialog());
                        } else {
                            objDialog = $("#" + idDialog);
                        }
                        if (objDialog == null || objDialog.length == 0)
                            break;

                        tinyDeActivate(objDialog.find('textarea.ita-edit-tinymce').toArray());

                        objDialog.find('.ita-activedTimer').each(function () {
                            $(this).stopTime('ita-timer');
                        });
                        delDialogLayout(objDialog);
                        delete dialogLightBoxOpt[idDialog];
                        try {
                            objDialog.dialog('destroy');
                        } catch (e) {
                            console.log(e);
                        }
                        objDialog.remove();
                        currDialogFocus = getCurrDialog();
                        if (currDialogFocus)
                            $("#" + currDialogFocus).focus();
                        if (dialogLastFocus["#" + currDialogFocus]) {
                            $(protSelector("#" + dialogLastFocus["#" + currDialogFocus])).focus();
                        }
                        delete dialogShortCutMap["#" + idDialog];
                        delete dialogLastFocus["#" + idDialog];
                        break;
                    case 'moveToTop':
                        $("#" + idDialog).dialog('moveToTop');
                        break;
                    case 'setOpt':
                        if (this.childNodes.length) {
                            var optKey = $(this).attr('option');
                            var optValue = this.childNodes[0].nodeValue;
                            $("#" + idDialog).dialog('option', optKey, optValue);
                        }
                        break;

                    default:
                        break;
                }
                break;
            case 'container' :
                var idParent = $(this).attr('parent');
                var idContainer = $(this).attr('id');
                var comando = $(this).attr('comando');
                var wdDialog = $(this).attr('width');
                var hgDialog = $(this).attr('height');
                var wdContent = wdDialog - 15;
                var hgContent = hgDialog - 40;

                var cfSelector = 'input, select, textarea';

                switch (comando) {
                    case 'del':
                        $(protSelector('#' + idContainer)).remove();
                        break;
                    case 'append':
                        $(idParent).append('<div id="' + idContainer + '"></div>');
                        break;
                    case 'prepend':
                        $(idParent).prepend('<div id="' + idContainer + '"></div>');
                        break;
                    case 'add':
                        $(idParent).append('<div id="' + idContainer + '"></div>');
                        break;
                    case 'dialog':
                        var titleContainer = $(this).attr('title');
                        var resizContainer = $(this).attr('resizable');
                        var modalContainer = $(this).attr('modal');
                        var posContainer = $(this).attr('position');
                        var minHeight = $(this).attr('minheight');
                        var Height = $(this).attr('height');
                        var Width = $(this).attr('width');
                        //$('#main').append('<div id="'+idContainer+'"></div>');
                        $("#" + idContainer).dialog({
                            bgiframe: true,
                            height: Height,
                            width: Width,
                            resizable: resizContainer,
                            title: titleContainer,
                            modal: modalContainer,
                            position: posContainer,
                            minHeight: minHeight,
                            open: function (eventi, ui) {
                                //$(this).parents('.ui-dialog').prependTo('#desktopBody');
                                //$('body .ui-widget-overlay').prependTo('#desktopBody');
                            },
                            close: function (event, ui) {
                                $("#" + idContainer).remove();
                            }

                        });
                        break;

                    case 'enablefields':
                        var selector = $(this).attr('selector'),
                            $fieldsContainer = $(protSelector('#' + idContainer));

                        if (selector) {
                            $fieldsContainer = $fieldsContainer.find(selector);
                        }

                        $fieldsContainer.find(cfSelector).addBack(cfSelector).each(function () {
                            if (this.id) {
                                enableField(protSelector(this.id));
                            }
                        });
                        break;

                    case 'disablefields':
                        var selector = $(this).attr('selector'),
                            $fieldsContainer = $(protSelector('#' + idContainer));

                        if (selector) {
                            $fieldsContainer = $fieldsContainer.find(selector);
                        }

                        $fieldsContainer.find(cfSelector).addBack(cfSelector).each(function () {
                            if (this.id) {
                                disableField(protSelector(this.id));
                            }
                        });
                        break;

                    case 'restorefields':
                        var selector = $(this).attr('selector'),
                            $fieldsContainer = $(protSelector('#' + idContainer));

                        if (selector) {
                            $fieldsContainer = $fieldsContainer.find(selector);
                        }

                        $fieldsContainer.find(cfSelector).addBack(cfSelector).each(function () {
                            if (this.id) {
                                restoreField(protSelector(this.id));
                            }
                        });
                        break;
                }
                break;
            case 'tabs' :
                var idTab = $(this).attr('idTab');
                var comando = $(this).attr('comando');
                var idPane, index;
                switch (comando) {
                    case 'setTitle':
                        idPane = $(this).attr('idPane');
                        var nodeValue = this.childNodes[0].nodeValue;
                        $('#' + idTab + ' a[href="#' + idPane + '"]').html(nodeValue);
                        break;
                    case 'disable':
                    case 'enable':
                        idPane = $(this).attr('idPane');
                        index = $('#' + idTab + ' a[href="#' + idPane + '"]').parent().index();
                        if (index != -1) {
                            $('#' + idTab).tabs(comando, index);
                        }
                        break;
                    case 'remove':
                        idPane = $(this).attr('idPane');
                        index = $('#' + idTab + ' a[href="#' + idPane + '"]').parent().index();
                        if (index != -1) {
                            $("#" + idTab).find(".ui-tabs-nav li:eq(" + index + ")").remove();
                            $("#" + idPane).remove();
                            $("#" + idTab).tabs("refresh");
                        }
                        break;
                    case 'add':
                        idPane = $(this).attr('idPane');
                        var nodeValue = this.childNodes[0].nodeValue;
                        var newPane = $(nodeValue);
                        if (idPane) {
                            $("#" + idTab).find('a[href="#' + idPane + '"]').eq(0).closest('li').before("<li><a href=\"#" + $(newPane).attr('id') + "\">" + $(newPane).attr('Title') + "</a></li>");
                            $("#" + idPane).before(nodeValue);
                            $(newPane).children('.ita-jqGrid').each(function () {
                                var pane_wrapper_id = $(this).attr('id') + "_tabwrapper";
                                $(this).wrap('<div id="' + pane_wrapper_id + '"></div>');
                            });
                        } else {
                            $("#" + idTab).find('ul').eq(0).append("<li><a href=\"#" + $(newPane).attr('id') + "\">" + $(newPane).attr('Title') + "</a></li>");
                            $("#" + idTab).append(nodeValue);
                            $(newPane).children('.ita-jqGrid').each(function () {
                                var pane_wrapper_id = $(this).attr('id') + "_tabwrapper";
                                $(this).wrap('<div id="' + pane_wrapper_id + '"></div>');
                            });

                        }
                        parseHtmlContainer($("#" + $(newPane).attr('id')));
                        $("#" + idTab).tabs("refresh");
                        break;
                    case 'select':
                        idPane = $(this).attr('idPane');
                        index = $('#' + idTab + ' a[href="#' + idPane + '"]').parent().index();
                        if (index != -1) {
                            $('#' + idTab).tabs("option", "active", index);
                        }
                        break;

                    case 'select-desktop':
                        if (idTab !== 'ita-home') {
                            idTab = 'tab-' + idTab;
                        }

                        index = $('a[href="#' + idTab + '"]').parent().index();
                        if (index != -1) {
                            $('#mainTabs').tabs("option", "active", index);
                        }
                        break;
                }
                break;
            case 'dialogHtml':
            case 'appHtml':
            case 'innerHtml':
            case 'html' :
                if (this.childNodes.length) {
                    idElemento = $(this).attr('container');
                    var container = $('#' + protSelector(idElemento));
                    var modo = $(this).attr('modo');
                    switch (modo) {
                        case '':
                            container.html(this.childNodes[0].nodeValue);
                            parseHtmlContainer(container, tag);
                            break;
                        case 'append':
                            var $append = $('<div>' + this.childNodes[0].nodeValue.trim() + '</div>');
                            container.append($append);
                            parseHtmlContainer($append, tag);
                            $append.children().unwrap();
                            break;
                        case 'prepend':
                            var $prepend = $('<div>' + this.childNodes[0].nodeValue.trim() + '</div>');
                            container.prepend($prepend);
                            parseHtmlContainer($prepend, tag);
                            $prepend.children().unwrap();
                            break;
                    }
                }
                break;
            case 'css':
                if (this.childNodes.length) {
                    idElemento = protSelector($(this).attr('id'));
                    var property = $(this).attr('prop');
                    var cssValue = this.childNodes[0].nodeValue;
                    $('#' + idElemento).css(property, cssValue);
                }
                break;
            case 'attributi' :
                if (this.childNodes.length) {
                    idElemento = protSelector($(this).attr('id'));
                    var tipoAttributo = $(this).attr('attributo');
                    var $element = $('#' + idElemento);
                    if ($(this).attr('del') == '1') {
                        $element.removeAttr(tipoAttributo);

                        if (tipoAttributo == 'readonly') {
                            itaInputMaskWithin($element);
                        }
                    } else if ($(this).attr('del') == '0') {
                        //$('#'+idElemento).attr(tipoAttributo,this.childNodes[0].nodeValue);
                        if (tipoAttributo == 'checked') {
                            if (this.childNodes[0].nodeValue == 'checked') {
                                $element.prop(tipoAttributo, true);
                            } else {
                                $element.prop(tipoAttributo, false);
                            }
                            break;
                        } else if (tipoAttributo == 'disabled') {
                            if (this.childNodes[0].nodeValue == 'disabled') {
                                $element.prop(tipoAttributo, true);
                            } else {
                                $element.prop(tipoAttributo, false);
                            }
                            break;
                        } else if (tipoAttributo == 'selected') {
                            if (this.childNodes[0].nodeValue == '1') {
                                $element.prop(tipoAttributo, true);
                            } else {
                                $element.prop(tipoAttributo, false);
                            }
                            break;
                        } else if (tipoAttributo == 'readonly') {
                            $element.attr(tipoAttributo, this.childNodes[0].nodeValue);
                            itaInputUnmaskWithin($element);
                        } else {
                            $element.attr(tipoAttributo, this.childNodes[0].nodeValue);
                        }
                    }

                    if (tipoAttributo == 'readonly' && $element.hasClass('ita-datepicker')) {
                        var $datepicker_button = $element.next('#' + idElemento + '_datepickertrigger');
                        if ($(this).attr('del') == '0') {
                            $datepicker_button.css('display', 'none');
                        } else {
                            $datepicker_button.css('display', 'inline-block');
                        }
                    }

                    if (tipoAttributo == 'readonly' && $element.is('select')) {
                        var $options = $element.children('option');

                        if ($(this).attr('del') == '0') {
                            $options.attr('disabled', true);
                        } else {
                            $options.removeAttr('disabled');
                        }

                        if ($element[0].selectedIndex > -1) {
                            $options.eq($element[0].selectedIndex).removeAttr('disabled');
                        }
                    }
                }
                break;

            case 'setFocus' :
                if (this.childNodes.length) {
                    idElemento = protSelector($(this).attr('id'));
                    var idForm = protSelector($(this).attr('form'));

                    if (idElemento == '') {
                        if (!idForm)
                            break;

                        idElemento = $("#" + idForm + " input:text:visible:first").attr('id');
                    }
                    //objElemento=$( "#"+idElemento+":not([readonly])");
                    //alert(objElemento);
                    //$( "#"+idElemento).focus();
                    if (typeof ($("#" + idElemento).attr('readonly')) != 'undefined' && $("#" + idElemento).attr('readonly') == 'readonly') {
                        break;
                    }

                    window.setTimeout(function () {
                        $("#" + idElemento).focus();
                    }, 50);

                    dialogLastFocus[$("#" + idElemento).parents('.ui-dialog-content:first').attr('id')] = idElemento;

                    if (currDialogFocus != $("#" + idElemento).parents('.ui-dialog-content:first').attr('id')) {
                        window.setTimeout(function () {
                            $("#" + currDialogFocus).focus();
                        }, 50);
                        //$("#"+currDialogFocus).focus();
                    }
                }
                break;
            case 'clearFields' :
                if (this.childNodes.length) {
                    var idForm = protSelector($(this).attr('form'));
                    var idContainer = protSelector($(this).attr('container'));
                    if (idContainer != '') {
                        var campi = $(':input', $('#' + idForm).find('#' + idContainer));
                    } else {
                        var campi = $(':input', $('#' + idForm));
                    }
                    campi.each(function () {
                        var type = this.type;
                        var tag = this.tagName.toLowerCase(); // normalize case
                        // it's ok to reset the value attr of text inputs,
                        // password inputs, and textareas
                        if (type == 'text' || type == 'password' || tag == 'textarea')
                            this.value = "";
                        // checkboxes and radios need to have their checked state cleared
                        // but should *not* have their 'value' changed
                        else if (type == 'checkbox' || type == 'radio')
                            this.checked = false;
                        // select elements need to have their 'selectedIndex' property set to -1
                        // (this works for both single and multiple select elements)
                        else if (tag == 'select')
                            this.selectedIndex = -1;

                        /*
                         * Reset per formatter number.
                         */
                        if (this.dataset.itaValue) {
                            this.dataset.itaValue = '';
                        }
                    });
                }
                break;

            case 'valori' :
                var nodeVal = '';
                if (this.childNodes.length) {
                    //nodeVal=$.trim(this.childNodes[0].nodeValue);
                    nodeVal = this.childNodes[0].nodeValue;
                }

                idElemento = $(this).attr('id');
                var pa = "\\["; // PROTEGGO LE PARENTESI PER FAR FUNZIONARE I SELETTORI CSS
                var pc = "\\]";

                idElemento = idElemento.replace(/\[/g, pa);
                idElemento = idElemento.replace(/\]/g, pc);

                var $elemento = $('#' + idElemento);

                /*
                 * 18.10.2016 #nuovo-input-mask
                 */
                itaInputUnmaskWithin($elemento);

                if ($elemento.is('input:checkbox')) {
                    if (nodeVal == '1') {
                        // $elemento.attr('checked','checked');
                        $elemento.prop('checked', true);
                    } else {
                        // $elemento.removeAttr('checked');
                        $elemento.prop('checked', false);
                    }
                    // $elemento.val('1');
                } else {
                    if ($elemento.hasClass('ita-edit-unicode')) {
                        nodeVal = unescape(nodeVal);
                    }

                    if ($elemento.hasClass('ita-edit-uppercase')) {
                        $elemento.val(ita_ucwords(nodeVal, true));
                    } else if ($elemento.hasClass('ita-edit-lowercase')) {
                        $elemento.val(ita_lcwords(nodeVal, true));
                    } else if ($elemento.hasClass('ita-edit-capitalize')) {
                        $elemento.val(ita_ucfirst(nodeVal, true));
                    } else {
                        $elemento.val(nodeVal);
                    }

                    if ($elemento.is('select[readonly]') && !$elemento.is('select[disabled]')) {
                        $elemento.children('option').attr('disabled', true);
                        if ($elemento[0].selectedIndex > -1) {
                            $elemento.children('option').eq($elemento[0].selectedIndex).removeAttr('disabled');
                        }
                    }

                    // Modifica per TinyMCE 4.0
                    if ($elemento.hasClass('ita-edit-tinymce') && tinymce.get($elemento.attr('id'))) {
                        tinymce.get($elemento.attr('id')).setContent(nodeVal);
                    }

                    if ($elemento.hasClass('ita-code-editor'))
                        $elemento.trigger('change');

                    try {
                        if ($elemento.data() && $elemento.metadata() && $elemento.metadata().formatter && $elemento.metadata().formatter == 'number') {
                            $elemento.removeClass('currency-display');
                            itaCurrencyFormatterDisplayOn($elemento);
                        }
                    } catch (e) {
                        console.log(e);
                    }
                }

                /*
                 * 18.10.2016 #nuovo-input-mask
                 */
                parseDateTimeWithin($elemento);
                itaInputMaskWithin($elemento);
                break;

            case 'classi' :
                if (this.childNodes.length) {
                    idElemento = $(this).attr('id');
                    var comando = $(this).attr('comando');
                    switch (comando) {
                        case 'add':
                            $(protSelector('#' + idElemento)).addClass(this.childNodes[0].nodeValue);
                            if (this.childNodes[0].nodeValue == 'datepicker')
                                $('#xx' + idElemento).datepicker({
                                    changeYear: true,
                                    changeMonth: true
                                });
                            break;
                        case 'del':
                            $(protSelector('#' + idElemento)).removeClass(this.childNodes[0].nodeValue);
                            break;
                    }
                }
                break;
            case 'select' :
                if (this.childNodes.length) {
                    var voption = this.childNodes[0].nodeValue;
                    idElemento = $(this).attr('id');
                    var comando = $(this).attr('comando');
                    var returnval = $(this).attr('returnval');
                    var style = $(this).attr('style');
                    if (comando == '0') {
                        $(protSelector("#" + idElemento) + " option[value='" + returnval + "']").remove();
                    } else {
                        var option = $('<option></option>').appendTo($(protSelector('#' + idElemento))).val(returnval).html(voption);
                        //                                                if($(this).attr('selected') == '1'){
                        //                                                    option.attr('selected','selected');   
                        //                                                }
                        if ($(this).attr('selected') == '1') {
                            option.prop('selected', true);
                        }

                        if ($(this).attr('style') != '') {
                            option.attr('style', style);
                        }
                    }
                }
                break;
            case 'broadcastMsg' :
                if (this.childNodes.length) {
                    var sender = $(this).attr('sender');
                    var message = $(this).attr('message');
                    var currModel;
                    var jsontext = this.childNodes[0].nodeValue;
                    try {
                        var myjsondata;
                        if (jsontext) {
                            myjsondata = eval("(" + jsontext + ")");
                        }
                        var extraData;
                        $('#ita-desktop').itaGetChildForms().each(function () { // @FORM FIXED 16.03.15 | 07.10.15
                            currModel = $(this).itaGetParentForm().itaGetId(); // FIX ID 07.10.15
                            if (currModel != sender) {
                                itaGo('ItaCall', '', {
                                    sender: sender,
                                    message: message,
                                    model: currModel,
                                    event: 'broadcastMsg',
                                    msgData: myjsondata,
                                    /* Carlo @ 30.04.15 - Con bloccaui: true (default) si hanno problemi se si usa un broadcast insieme ad output visivi (es. msgBlock) */
                                    bloccaui: false
                                });
                            }
                        });
                    } catch (err) {
                        alert("broadcastEvt json error: " + err.message);
                    }

                }
                break;
            case 'codice' :
                if (this.childNodes.length) {
                    eval(this.childNodes[0].nodeValue);
                }
                break;
            case 'setGridOption' :
                var idGrid = $(this).attr('id');
                if (this.childNodes.length) {
                    var optKey = $(this).attr('option');
                    var optObj = eval(this.childNodes[0].nodeValue);
                    $("#" + idGrid).dialog('option', optKey, optObj);
                }
                break;

            case 'callGridMethod' :
                var callParam = "";
                if (this.childNodes.length) {
                    callParam = eval(this.childNodes[0].nodeValue);
                }
                var idGrid = $(this).attr('id');
                var idMethod = $(this).attr('method');
                $("#" + idGrid).jqGrid(idMethod, callParam);

                break;

            case 'field' :
                if (this.childNodes.length) {
                    idElemento = protSelector($(this).attr('id'));
                    var comando = $(this).attr('comando');

                    switch (comando) {
                        case 'enable':
                            enableField(idElemento);
                            break;

                        case 'disable':
                            disableField(idElemento);
                            break;

                        case 'restore':
                            restoreField(idElemento);
                            break;
                    }
                }
                break;

            case 'tabella' :
                if (this.childNodes.length) {
                    var disableSel = true;
                    var idTabella = $(this).attr('id');
                    var comando = $(this).attr('comando');
                    var objTabella = $('#' + idTabella);
                    var gridRowH = objTabella.attr('data-itagridrowheight');
                    switch (comando) {
                        case 'show' :

                            break;
                        case 'new':

                            break;

                        case 'setSelection':
                            var rowid = $(this).attr('rowid');
                            var selectionType = $(this).attr('type');
                            var metadata = objTabella.metadata();
                            var propagateEvent = $(this).attr('event') ? ($(this).attr('event') == '1' ? true : false) : true;
                            if (selectionType == undefined || selectionType == 'id') {
                                //objTabella.jqGrid('setSelection',rowid);
                                jQuery('#' + idTabella).jqGrid('setSelection', rowid, propagateEvent);
                            }
                            if (selectionType == 'sequence') {
                                var trId = $('#' + idTabella).find('tr.jqgrow').eq(rowid).attr('id');
                                if (trId != undefined) {
                                    jQuery('#' + idTabella).jqGrid('setSelection', trId, propagateEvent);
                                }
                            }

                            break;
                        case 'enableSelection':
                            disableSel = false;
                        case 'disableSelection':
                            var rowid = $(this).attr('rowid');
                            var selectionType = $(this).attr('type');
                            if (selectionType == undefined || selectionType == 'id') {
                                $('#' + idTabella).find("#" + rowid).find("td > input.cbox").prop('disabled', disableSel);
                            }
                            if (selectionType == 'sequence') {
                                var trId = $('#' + idTabella).find('tr.jqgrow').eq(rowid).attr('id');
                                if (trId != undefined) {
                                    $('#' + idTabella).find("#" + trId).find("td > input.cbox").prop('disabled', disableSel);
                                }
                            }

                            break;
                        case 'setSelectAll':
                            $('#' + idTabella).resetSelection();
                            var ids = $('#' + idTabella).getDataIDs();
                            for (var i = 0, il = ids.length; i < il; i++)
                                $('#' + idTabella).setSelection(ids[i], false);
                            break;
                        case 'setDeselectAll':
                            $('#' + idTabella).resetSelection();
                            break;
                        case 'reload':
                            $('#' + idTabella).trigger('reloadGrid');
                            break;
                        case 'setRowData':
                            var rowid = parseInt($(this).attr('rowid'));
                            var jsontext = $(this).find("data").eq(0).text();
                            var jsoncss = $(this).find("css").eq(0).text();
                            try {
                                var myrowdata;
                                if (jsontext == '') {
                                    myrowdata = [];
                                } else {
                                    myrowdata = JSON.parse(jsontext);
                                }

                                var mycss = jsoncss ? JSON.parse(jsoncss) : '';
                                $('#' + idTabella).setRowData(rowid, myrowdata, mycss);
                            } catch (err) {
                                alert("setrowdata fail: " + err.message);
                            }

                            break;

                        case 'setFooterData':
                            var jsontext = $(this).find("data").eq(0).text();
                            try {
                                if (jsontext == '') {
                                    myrowdata = false;
                                } else {
                                    myrowdata = JSON.parse(jsontext);
                                }
                                $('#' + idTabella).footerData('set', myrowdata);
                            } catch (err) {
                                alert("setFooterData fail: " + err.message);
                            }

                            break;

                        case 'setCellValue':
                            var value = this.childNodes[0].nodeValue;
                            var rowid = $(this).attr('rowid');
                            var colname = $(this).attr('colname');
                            var cellclass;
                            if ($(this).attr('class').indexOf('{') < 0) {
                                cellclass = $(this).attr('class');
                            } else
                            {
                                cellclass = eval("(" + $(this).attr('class') + ")");
                            }
                            var properties = $(this).attr('properties');
                            var forceup = $(this).attr('forceup');
                            try {
                                /*  Carlo 31.10.14: cambiato 'filter' con 'find'
                                 *  var editCellId = $($("#" + idTabella).jqGrid('getCell', rowid, colname)).filter('.ita-edit-cell').attr('id');
                                 */
                                var editCellId = $($("#" + idTabella).jqGrid('getCell', rowid, colname)).find('.ita-edit-cell').attr('id');

                                if (editCellId != undefined) {
                                    var $editCell = $('#' + editCellId);
                                    if ($editCell.length > 0) {
                                        //TODO GESTIRE CHECK E SELECT TEXT E OPTIONS
                                        $editCell.val(value);
                                    }
                                    break;
                                }
                            } catch (err) {
                            }

                            $('#' + idTabella).find("#ita-jqg-editcheckbox-" + colname + "-" + rowid).eq(0).unbind('click');
                            $("#" + idTabella).jqGrid('setCell', rowid, colname, value, cellclass, properties, forceup);
                            $('#' + idTabella).find("#ita-jqg-editcheckbox-" + colname + "-" + rowid).each(function () {
                                if ($(this).parent('td').hasClass('not-editable-cell')) {
                                    $(this).attr('disabled', 'disabled');
                                } else {
                                    var idObj = $("#" + idTabella);
                                    var extraParm = $(this).metadata();
                                    $(this).click(function () {
                                        //var value = $(this).attr('checked') ? '1' : '0';
                                        var value = $(this).prop('checked') ? '1' : '0';
                                        itaGo('ItaForm', idObj, {
                                            event: 'afterSaveCell',
                                            validate: true,
                                            rowid: extraParm.rowid,
                                            cellname: extraParm.cellname,
                                            value: value
                                        });
                                    });
                                }

                            });
                            //}
                            break;

                        case 'setCellFocus':
                            var value = this.childNodes[0].nodeValue;
                            var rowid = $(this).attr('rowid');
                            var colname = $(this).attr('colname');
                            var cellclass = $(this).attr('class');
                            var editCellId = $($("#" + idTabella).jqGrid('getCell', rowid, colname)).filter('.ita-edit-cell').attr('id');
                            var $editCell = $('#' + editCellId);
                            if ($editCell.length > 0) {
                                //TODO GESTIRE CHECK E SELECT TEXT E OPTIONS
                                $editCell.focus();
                            }
                            break;

                        case 'addXML':
                            if ($(this).attr('clearGrid') == '1') {
                                objTabella.clearGridData(true);
                            }
                            var xmltext = $(this).find('jqgrid')[0];
                            var mygrid = jQuery('#' + idTabella)[0];
                            mygrid.addXmlData(xmlobject);
                            break;
                        case 'addJson':
                            var oldScrollTop = $('#' + idTabella).closest(".ui-jqgrid-bdiv").scrollTop();
                            if ($(this).attr('clearGrid') == '1') {
                                objTabella.clearGridData(true);
                            }
                            var jsontext = this.childNodes[0].nodeValue;
                            var mygrid = jQuery('#' + idTabella)[0];
                            if (typeof (mygrid) == 'undefined') {
                                break;
                            }
                            try {
                                var myjsongrid = eval("(" + jsontext + ")");
                                mygrid.addJSONData(myjsongrid);
                            } catch (err) {
                                alert("addjson fail: " + err.message);
                            }
                            $('#' + idTabella).find(".ita-jqg-editcheckbox").each(function () {
                                if ($(this).parent('td').hasClass('not-editable-cell')) {
                                    $(this).attr('disabled', 'disabled');
                                } else {
                                    var idObj = $("#" + idTabella);
                                    var extraParm = $(this).metadata();
                                    $(this).click(function () {
                                        //var value = $(this).attr('checked') ? '1' : '0';
                                        var value = $(this).prop('checked') ? '1' : '0';
                                        itaGo('ItaForm', idObj, {
                                            event: 'afterSaveCell',
                                            validate: true,
                                            rowid: extraParm.rowid,
                                            cellname: extraParm.cellname,
                                            value: value
                                        });
                                    });
                                }
                            });
                            myjsongrid = null;
                            jsontext = null;
                            $('#' + idTabella).closest(".ui-jqgrid-bdiv").scrollTop(oldScrollTop);

                            if (gridRowH) {
                                $('#' + idTabella).find('tr.jqgrow').height(gridRowH);
                            }
                            break;

                        case 'add':
                            var jsontext = this.childNodes[0].nodeValue;
                            var mygrid = jQuery('#' + idTabella)[0];
                            var rowid = $(this).attr('rowid');
                            var pos = $(this).attr('position');
                            var ref = $(this).attr('reference');
                            var row = JSON.parse(jsontext);

                            objTabella.jqGrid('addRowData', rowid, row, pos, ref);
                            break;

                            if ($("#" + idTabella).hasClass('ita-jqGrid-activated')) {
                                var idRiga = $(this).attr('idRiga');
                                var ita_arRiga = "";
                                $(this).find('cella').each(function () {
                                    ita_arRiga = ita_arRiga + $(this).attr('id') + ':\"' + this.childNodes[0].nodeValue + '\",';
                                });
                                ita_arRiga = "[{" + ita_arRiga.substr(0, ita_arRiga.length - 1) + "}]";
                                var xdata = eval(ita_arRiga);
                                $("#" + idTabella).addRowData(idRiga, xdata[0]);

                                if (gridRowH) {
                                    $('#' + idTabella).find('tr.jqgrow').height(gridRowH);
                                }
                            } else {
                                var nuovaRiga = objTabella.find('#baseRow').clone();
                                var nuovoId = $(this).attr('idRiga');
                                nuovaRiga.attr('id', $(this).attr('idRiga'));
                                nuovaRiga.find('td').each(function () {
                                    var vecchioId = $(this).attr('id');
                                    var nuovoIDCella = nuovoId + '_' + $(this).attr('id');
                                    $(this).attr('id', nuovoIDCella);
                                    $(this).attr('vecchioid', vecchioId);
                                });
                                $(this).find('cella').each(function () {
                                    nuovaRiga.find(protSelector('#' + $(this).attr('id'))).html(this.childNodes[0].nodeValue);
                                });

                                nuovaRiga.find('.ita-active-cell').each(function () {
                                    var innerCell = $(this).html();
                                    $(this).html('<a href="#" id="' + idTabella + '_' + $(this).attr('vecchioid') + '" onClick="itaGo(\'ItaGrid\',this,{event:\'onClick\'});">' + innerCell + '</a>');
                                });

                                nuovaRiga.find('td').each(function () {
                                    $(this).removeAttr('vecchioid');
                                });


                                $(objTabella).children('tbody').append(nuovaRiga);
                                objTabella.find('#baseRow').hide();
                                $(protSelector('#' + nuovoId)).show();
                            }
                            break;

                        case 'del':
                            objTabella.clearGridData(true);
                            break;
                        case 'upd':
                            var Riga = objTabella.find('tr[id=' + $(this).attr('idRiga') + ']');
                            $(this).find('cella').each(function () {
                                Riga.find(protSelector('#' + $(this).attr('id'))).html(this.childNodes[0].nodeValue);
                            });
                            break;
                        case 'html':
                            var RigaHtml = objTabella.find('tr[id=' + $(this).attr('idRiga') + ']');
                            $(this).find('cella').each(function () {
                                var idElemento = $(this).attr('container');
                                RigaHtml.find(protSelector('#' + $(this).attr('id'))).find(protSelector('#' + idElemento)).html(this.childNodes[0].nodeValue);
                            });
                            break;

                        case 'addChildren':
                            var oldScrollTop = $('#' + idTabella).closest(".ui-jqgrid-bdiv").scrollTop();
                            var jsontext = this.childNodes[0].nodeValue;
                            var mygrid = jQuery('#' + idTabella)[0];

                            // Imposta il datatype = local altrimenti l'aggiunta dei nodi figli non va a buon fine
                            // (Rif.: http://www.trirand.com/jqgridwiki/doku.php?id=wiki:treegrid )
                            var oldDatatype = mygrid.p.datatype;
                            mygrid.p.datatype = 'local';

                            if (typeof (mygrid) == 'undefined') {
                                break;
                            }
                            try {
                                var children = eval("(" + jsontext + ")");
                                children.row.forEach(function (row) {
                                    $('#' + idTabella).jqGrid('addChildNode', row.idx, row.parent, row);
                                });
                            } catch (err) {
                                alert("addChildren fail: " + err.message);
                            }
                            $('#' + idTabella).find(".ita-jqg-editcheckbox").each(function () {
                                if ($(this).parent('td').hasClass('not-editable-cell')) {
                                    $(this).attr('disabled', 'disabled');
                                } else {
                                    var idObj = $("#" + idTabella);
                                    var extraParm = $(this).metadata();
                                    $(this).click(function () {
                                        //var value = $(this).attr('checked') ? '1' : '0';
                                        var value = $(this).prop('checked') ? '1' : '0';
                                        itaGo('ItaForm', idObj, {
                                            event: 'afterSaveCell',
                                            validate: true,
                                            rowid: extraParm.rowid,
                                            cellname: extraParm.cellname,
                                            value: value
                                        });
                                    });
                                }
                            });
                            myjsongrid = null;
                            jsontext = null;
                            $('#' + idTabella).closest(".ui-jqgrid-bdiv").scrollTop(oldScrollTop);

                            if (gridRowH) {
                                $('#' + idTabella).find('tr.jqgrow').height(gridRowH);
                            }

                            // Ripristina il datatype
                            mygrid.p.datatype = oldDatatype;

                            break;

                        case 'removeChildren':
                            var oldScrollTop = $('#' + idTabella).closest(".ui-jqgrid-bdiv").scrollTop();
                            var jsontext = this.childNodes[0].nodeValue;
                            var mygrid = jQuery('#' + idTabella)[0];

                            // Imposta il datatype = local altrimenti l'aggiunta dei nodi figli non va a buon fine
                            // (Rif.: http://www.trirand.com/jqgridwiki/doku.php?id=wiki:treegrid )
                            var oldDatatype = mygrid.p.datatype;
                            mygrid.p.datatype = 'local';

                            if (typeof (mygrid) == 'undefined') {
                                break;
                            }
                            try {
                                // La risposta json contiene i dati del nodo padre
                                var parent = eval("(" + jsontext + ")");
                                var parentRec = $('#' + idTabella).jqGrid('getRowData', parent.id);
                                parentRec["_id_"] = parent.id;

                                // Legge i figli
                                var children = $('#' + idTabella).jqGrid('getNodeChildren', parentRec);

                                // Rimuove tutti i nodi
                                children.forEach(function (row) {
                                    // Controllo se non sto togliendo la riga parent
                                    if (row._id_ != parent.id) {
                                        $('#' + idTabella).jqGrid('delTreeNode', row._id_);
                                    }
                                });

                            } catch (err) {
                                alert("removeChildren fail: " + err.message);
                            }
                            $('#' + idTabella).find(".ita-jqg-editcheckbox").each(function () {
                                if ($(this).parent('td').hasClass('not-editable-cell')) {
                                    $(this).attr('disabled', 'disabled');
                                } else {
                                    var idObj = $("#" + idTabella);
                                    var extraParm = $(this).metadata();
                                    $(this).click(function () {
                                        //var value = $(this).attr('checked') ? '1' : '0';
                                        var value = $(this).prop('checked') ? '1' : '0';
                                        itaGo('ItaForm', idObj, {
                                            event: 'afterSaveCell',
                                            validate: true,
                                            rowid: extraParm.rowid,
                                            cellname: extraParm.cellname,
                                            value: value
                                        });
                                    });
                                }
                            });
                            myjsongrid = null;
                            jsontext = null;
                            $('#' + idTabella).closest(".ui-jqgrid-bdiv").scrollTop(oldScrollTop);

                            if (gridRowH) {
                                $('#' + idTabella).find('tr.jqgrow').height(gridRowH);
                            }

                            // Ripristina il datatype
                            mygrid.p.datatype = oldDatatype;

                            break;
                    }
                }
                break;

            case 'layout' :
                if (this.childNodes.length) {
                    idElemento = protSelector($(this).attr('idPanel'));
                    var $elemento = $('#' + idElemento);

                    var panelSide;
                    if ($elemento.hasClass('ui-layout-pane-east'))
                        panelSide = 'east';
                    if ($elemento.hasClass('ui-layout-pane-west'))
                        panelSide = 'west';
                    if ($elemento.hasClass('ui-layout-pane-north'))
                        panelSide = 'north';
                    if ($elemento.hasClass('ui-layout-pane-south'))
                        panelSide = 'south';

                    var $layout = $elemento.closest('.ui-layout-container');

                    if ($layout.length < 1) {
                        break;
                    }

                    var comando = $(this).attr('comando');

                    switch (comando) {
                        case 'open':
                            $layout.layout().open(panelSide);
                            break;
                        case 'close':
                            $layout.layout().close(panelSide);
                            break;
                        case 'hide':
                            $layout.layout().hide(panelSide);
                            break;
                        case 'show':
                            $layout.layout().show(panelSide);
                            break;
                    }
                }
                break;

            case 'required':
                if (this.childNodes.length) {
                    var field_id = $(this).attr('id');
                    var required = $(this).attr('required');
                    var validate = $(this).attr('validate');

                    var $field = $('#' + protSelector(field_id));
                    var $lbl_required = $('#' + protSelector(field_id) + '_lbl_required');

                    if (required) {
                        $lbl_required.text('*');
                    } else {
                        $lbl_required.text('');
                    }

                    if (validate) {
                        $field.addClass('required');
                    } else {
                        $field.removeClass('required');
                    }
                }
                break;

            case 'tabpane':
                if (this.childNodes.length) {
                    var tabid = $(this).attr('id');
                    var comando = $(this).attr('comando');
                    switch (comando) {
                        case 'hide':
                            var $tab = $('[href="#' + tabid + '"]').parent();

                            if ($tab.is('.ui-state-active')) {
                                if ($tab.prev(':not(.ita-tab-hidden)').length) {
                                    $tab.prev(':not(.ita-tab-hidden)').children().click();
                                } else if ($tab.next(':not(.ita-tab-hidden)').length) {
                                    $tab.next(':not(.ita-tab-hidden)').children().click();
                                }
                            }

                            $tab.addClass('.ita-tab-hidden').hide();
                            break;

                        case 'show':
                            $('[href="#' + tabid + '"]').parent().removeClass('.ita-tab-hidden').show();
                            break;
                    }
                }
                break;

            case 'menugrid' :
                if (this.childNodes.length) {
                    var id = protSelector($(this).attr('id'));
                    var $this = $('#' + id);

                    var comando = $(this).attr('comando');

                    switch (comando) {
                        case 'init':
                            var opzioni = JSON.parse(this.childNodes[0].nodeValue);

                            var noClickTimeout, noClickTimeoutDelay = 600, opts = {
                                autogenerate_stylesheet: true,
                                widget_selector: 'div',
                                widget_margins: [10, 10],
                                widget_base_dimensions: [120, 120],
                                serialize_params: function ($w, wgd) {
                                    return {id: $w.attr('id'), col: wgd.col, row: wgd.row, size_x: wgd.size_x, size_y: wgd.size_y};
                                },
                                draggable: {
                                    start: function (e, ui) {
                                        if (noClickTimeout) {
                                            clearTimeout(noClickTimeout);
                                        }
                                        $(e.target).closest('.gs-w').addClass('no-click');
                                    },
                                    stop: function (e, ui) {
                                        noClickTimeout = setTimeout(function () {
                                            $(e.target).closest('.gs-w').removeClass('no-click');
                                            noClickTimeout = null;
                                        }, noClickTimeoutDelay);

                                        itaGo('ItaForm', $this, {
                                            event: 'onMenuGridChange',
                                            cell: $(e.target).attr('id'),
                                            grid: JSON.stringify(menuGrids[id].serialize())
                                        });
                                    }
                                },
                                resize: {
                                    enabled: true,
                                    max_size: [3, 2],
                                    min_size: [1, 1],
                                    start: function (e, ui, $widget) {
                                        if (noClickTimeout) {
                                            clearTimeout(noClickTimeout);
                                        }
                                        $widget.addClass('no-click');
                                    },
                                    stop: function (e, ui, $widget) {
                                        noClickTimeout = setTimeout(function () {
                                            $widget.removeClass('no-click');
                                            noClickTimeout = null;
                                        }, noClickTimeoutDelay);

                                        itaGo('ItaForm', $this, {
                                            event: 'onMenuGridChange',
                                            cell: $(e.target).attr('id'),
                                            grid: JSON.stringify(menuGrids[id].serialize())
                                        });
                                    }
                                }
                            };

                            for (var key in opzioni) {
                                opts[key] = opzioni[key];
                            }

                            menuGrids[id] = $this.gridster(opts).data('gridster');
                            menuGrids[id].opts = opts;
                            break;
                    }
                }
                break;
        }
    });

    /*
     * 18.10.2016 #nuovo-input-mask
     * Ristretto l'utilizzo di itaInputMask/Unmask solo quando necessario
     * Problema datepicker con itaTimer
     */
//    parseDateTime();
//    itaInputMask();
};

/**
 * CLASSE ITACALL (eredita ITABASE)
 */
function ItaCall() {
    this.defaultEvent = 'generale';
}

ItaCall.prototype = new ItaBase();
ItaCall.prototype.constructor = ItaCall();

ItaCall.prototype.beforeRequest = function () {
    this.url = urlController;

    /*
     * Nuovo parametro "nameform"
     */
    if (this.parametri.model && $.itaGetForm(this.parametri.model).itaGetModelBackend()) {
        this.parametri.nameform = this.parametri.model;
        this.parametri.model = $.itaGetForm(this.parametri.model).itaGetModelBackend();
    }

    if (this.parametri.modelBackend) {
        this.parametri.nameform = this.parametri.model;
        this.parametri.model = this.parametri.modelBackend;
        delete this.parametri.modelBackend;
    }

    var callPost = $.param(this.parametri);
    this.post = 'TOKEN=' + token + '&' + callPost + '&tmpToken=' + tmpToken;
    return true;
};

ItaCall.prototype.errore = function (codice) {
    switch (codice) {
        case 'parseError':
            alert('Errore nella risposta del server');
            break;
        default:
            alert('(ItaCall) Chiamata non funzionante: ' + codice);
    }
};

ItaCall.prototype.afterRequest = function (risposta) {
    // Nessuna Azione Definita
};

/**
 * CLASSE ITAFORM (eredita ITABASE)
 */
function ItaForm() {
    this.defaultEvent = 'generale';
}

ItaForm.prototype = new ItaBase();
ItaForm.prototype.constructor = ItaForm();

ItaForm.prototype.beforeRequest = function () {

    jQuery.validator.addClassRules('ita-datepicker', {
        date_check: true
    });
    jQuery.validator.addClassRules('ita-date', {
        date_check: true
    });
    jQuery.validator.addMethod('date_check', function (v, e, p) {
        if (v == '' || v == null)
            return true;
        return isDate(v, 'dd/MM/yyyy');
    }, 'Data non valida');

    jQuery.validator.addClassRules('ita-month', {
        month_check: true
    });

    jQuery.validator.addMethod('month_check', function (v, e, p) {
        if (v == '' || v == null)
            return true;
        return isDate(v, 'MM/yyyy');
    }, 'Mese non valido');

    jQuery.validator.addClassRules('ita-time', {
        time_check: true
    });

    jQuery.validator.addMethod('time_check', function (v, e, p) {
        if (v == '' || v == null)
            return true;
        var regex = /^(2[0-3])|[01][0-9]:[0-5][0-9]$/;
        return regex.test(v);
    }, 'Orario non valido');

    jQuery.validator.addClassRules('ita-regexp', {
        regexp_check: true
    });

    jQuery.validator.addMethod('regexp_check', function (v, e, p) {
        var meta = $(e).metadata();
        if (!meta.regexp) {
            return true;
        }

        try {
            var regexp = new RegExp(meta.regexp, meta.regexpFlags ? meta.regexpFlags : '');
        } catch (exc) {
            console.log('Errore validazione RegExp', exc);
            return true;
        }

        return regexp.test(v);
    }, 'Valore non valido');

    for (var key in tinymce.editors) {
        tinymce.editors[key].save();
    }

    for (var key in window.codeMirrors) {
        window.codeMirrors[key].save();
    }

    var idElemento = this.parametri.id ? this.parametri.id : this.elemento.id;
    var $activeItaFullscreen = $(protSelector('#' + idElemento)).closest('.ita-fullscreen-active');

    $activeItaFullscreen.trigger('fullscreen-off');

    var myForm; // @FORM
    var myId;
    if (this.elemento == "") {
        myForm = $("#" + this.parametri['model']);
        delete this.parametri['model'];
        myId = this.parametri['id'];
        delete this.parametri['id'];
    } else {
        if (typeof (this.parametri.model) == 'undefined') {
            myForm = $(this.elemento).itaGetParentForm(); // $(itaImplode($(this.elemento), 'form, div.ita-model')); // @FORM FIXED 16.03.15 | 07.10.15
        } else {
            /* @fix 19.06.15 - Carlo */
            myForm = $("#" + this.parametri.model);
        }
        delete this.parametri.model;
        if (typeof (this.parametri.id) == 'undefined') {
            myId = $(this.elemento).attr('id');
        } else {
            myId = this.parametri.id;
        }
    }

    /*
     * Nuovo parametro "nameform"
     */
    var nameForm = myForm.itaGetParentForm().itaGetId();
    var modelBackend = myForm.itaGetModelBackend();

    myForm = myForm.itaGetHostForm();

    var validateForm = myForm,
        validateOptions = {
            meta: 'rules',
            errorClass: 'ui-state-error ita-state-error',
            errorPlacement: function (error, element) {
                var label = $(element).prev('label');
                if (label) {
                    $("#validateMsg").append($(label).html() + " (" + $(element).attr('id') + ") : ");
                } else {
                    $("#validateMsg").append($(element).attr('id') + ": ");
                }
                $("#validateMsg").append('<a id ="errlblfor_' + $(element).attr('id') + '" href="#">' + error.html() + '</a>').append('<br>');
                $(protSelector("#errlblfor_" + $(element).attr('id'))).click(function () {
                    $("#validateDlg").dialog('close');
                    $(element).focus();
                });
                $(element).addClass('ui-state-error');
                $(element).addClass('ita-state-error');
            },
            invalidHandler: function (form, validator) {
                $(form).find('.ui-state-error.ita-state-error').removeClass('ui-state-error');
                var errors = validator.numberOfInvalids();
                if (errors) {
                    var message = 'Ci sono ' + errors + ' campi con errore. Controllare nella lista sottostante:<br>';
                    //var dlg = $('<div/>').html('<div id="validateDlg" class="ui-state-error ui-corner-all" style="padding:1.3em"><p>'+message+'</p></div>').appendTo('body');
                    var dlg = $('<div id="validateDlg"><div id="validateMsg" class="ui-state-error ui-corner-all" style="padding:1.3em"><p>' + message + '</p></div></div>').appendTo('body');
                    dlg.dialog({
                        bgiframe: true,
                        height: 'auto',
                        width: 'auto',
                        resizable: false,
                        title: 'Errore di Validazione',
                        modal: true,
                        position: 'auto',
                        close: function (event, ui) {
                            dlg.remove();
                        }
                    });
                }
            }
        };

    if (!validateForm.is('form')) {
        validateForm = validateForm.closest('form');

        if (validateForm.length) {
            validateOptions.ignore = ':hidden, :not(:itaModel(' + myForm[0].id + '))';
        }
    }

    if (validateForm.is('form')) {
        validateForm.validate(validateOptions);
    }

    var ita_event = this.parametri['event'];
    delete this.parametri['event'];
    var ita_validate = this.parametri['validate'];
    if (ita_validate == true) {
        if (!validateForm.valid()) {
            return false;
        }

    }

    //var model = myForm.eq(0).attr('action').substr(1);
    var model = myForm.itaGetParentForm().itaGetId(); // myForm.eq(0).attr('id'); // @FORM Fixed 07.10.15
    if (model == '')
        alert('Model di gestione non indicato!');

    var modelMetadata = $('#' + model).metadata();

    $(myForm).find('input:checkbox').each(function () {
        //        if ($(this).attr('checked') ){
        //            $(this).attr('value','1').attr('checked',true);
        //        }else{
        //            $(this).attr('value','0').attr('checked',true);
        //        }
        if ($(this).prop('checked')) {
            $(this).attr('value', '1').prop('checked', true);
        } else {
            $(this).attr('value', '0').prop('checked', true);
        }

    });

    var formVal = '';
    var formValArray;
    var formValFieldParam = {};
    if (this.parametri['leggiform'] == 'tutto') {
        if (myForm.prop("tagName") == 'DIV') {
            formValArray = myForm.find("input,select,textarea").serializeArray();
        } else {
            formValArray = $(myForm).serializeArray();
        }

    } else {
        formValArray = $(myForm).find(protSelector("#" + myId)).serializeArray();
    }

    $.each(formValArray, function (i, v) {
        var campo = $(myForm).find('input[name="' + v['name'] + '"]').eq(0);

        if (campo.attr('id') != undefined) {
            if (campo.hasClass('ita-datepicker') || campo.hasClass('ita-date')) {
                if (v['value'] == '__/__/____') {
                    v['value'] = '';
                    formValArray[i]['value'] = '';
                }

                if (v['value'] != '' && v['value'] != null) {
                    var campoMetadata = campo.metadata();
                    var dateReturnValue = campoMetadata.dateReturn || modelMetadata.dateReturn;

                    formValArray[i]['value'] = formatItaDate(v['value'], dateReturnValue);
                }
            }

            if (campo.hasClass('ita-month')) {
                if (v['value'] != '' && v['value'] != null) {
                    var campoMetadata = campo.metadata();
                    var month = new Date(getDateFromFormat(v['value'], 'MM/yyyy'));
                    var dateReturnValue = campoMetadata.dateReturn || modelMetadata.dateReturn;

                    if (dateReturnValue) {
                        switch (dateReturnValue) {
                            case 'iso':
                                formValArray[i]['value'] = formatDate(month, 'yyyy-MM');
                                break;

                            case 'iso-basic':
                                formValArray[i]['value'] = formatDate(month, 'yyyyMM');
                                break;
                        }
                    } else {
                        formValArray[i]['value'] = formatDate(month, 'yyyyMM');
                    }
                }
            }

            if (campo.hasClass('ita-edit')) {
                var metaData = campo.metadata();
                if (metaData && metaData.formatter && metaData.formatter == 'number') {
                    formValArray[i]['value'] = getItaCurrencyValue(campo);
                }

                if (typeof (metaData.filterconfig) !== 'undefined' && metaData.filterconfig == true) {
                    if (campo.data('filterConfig')) {
                        formValFieldParam[campo.attr('id')] = {filterMode: campo.data('filterConfig')};
                    }
                }

                if (typeof (metaData.filterconfigEmptyValue) !== 'undefined') {
                    if (!formValFieldParam[campo.attr('id')]) {
                        formValFieldParam[campo.attr('id')] = {};
                    }

                    formValFieldParam[campo.attr('id')].filterEmptyValue = metaData.filterconfigEmptyValue;
                }
            }

            if (campo.hasClass('ita-edit-time')) {
                var format = campo.metadata().format || 'hi';
                if (v.value && format.toLowerCase() === 'hi') {
                    formValArray[i]['value'] = v.value + ':00';
                }
            }
        }
    });

    for (var i = 0; i < formValArray.length; i++) {
        formValArray[i]['value'] = formValArray[i]['value'].replace(/[\x00-\x08\x0B-\x0C\x0E-\x1F]/g, function (i) {
            return '&#' + i.charCodeAt(0) + ';';
        });
        formVal += "&" + formValArray[i]['name'] + "=" + escape(formValArray[i]['value']).replace(new RegExp("\\+", "g"), "%2B");//escape(formValArray[i]['value']);
    }

    formVal += objectToQuery(formValFieldParam, 'fieldParam');

    $(myForm).find('input:checkbox').each(function () {
        if ($(this).attr('value') == 0) {
            $(this).prop('checked', false);
        }
    });

    $(myForm).find('.ita-flowchart').each(function () {
        var diagramInstance = jsPlumbInstances[this.id],
            diagramJSONData = diagramInstance.exportData(),
            diagramData = {
                selectedNodes: itaJsPlumbHelper.getSelectedNodes(this.id)
            };

        /*
         * Fix per valori top/left mancanti nei nodi all'export. 
         */
        for (var i in diagramJSONData.nodes) {
            if (!diagramJSONData.nodes[i].top) {
                diagramJSONData.nodes[i].top = parseInt($('[data-jtk-node-id="' + diagramJSONData.nodes[i].id + '"]').css('top'));
            }

            if (!diagramJSONData.nodes[i].left) {
                diagramJSONData.nodes[i].left = parseInt($('[data-jtk-node-id="' + diagramJSONData.nodes[i].id + '"]').css('left'));
            }
        }

        diagramData.json = JSON.stringify(diagramJSONData);

        var panData = jsPlumbInstances.Renderers[this.id].getPan();
        diagramData.pan = {x: panData[0], y: panData[1]};
        diagramData.zoom = jsPlumbInstances.Renderers[this.id].getZoom();

        /*
         * Metodi per esportazione JSON
         */
        // formVal += objectToQuery(diagramInstance.exportData(), this.id); // Output come oggetto
        // formVal += '&' + this.id + '=' + JSON.stringify(diagramInstance.exportData()); // Output come stringa JSON

        formVal += objectToQuery(diagramData, this.id);
    });

    $(myForm).find('.ita-jqGrid-activated').each(function () {
        var $that = $(this);
        var gridId = $that.attr('id');
        if ($that.jqGrid('getGridParam', 'multiselect') == true) {
            formVal += "&" + gridId + "[gridParam][selarrrow]=" + $that.getGridParam("selarrrow");
        } else {
            formVal += "&" + gridId + "[gridParam][selarrrow]=" + $that.getGridParam("selrow");
        }
        formVal += "&" + gridId + "[gridParam][selrow]=" + $that.getGridParam("selrow");
        formVal += "&" + gridId + "[gridParam][rowNum]=" + $that.getGridParam("rowNum");
        formVal += "&" + gridId + "[gridParam][page]=" + $that.getGridParam("page");

        if ($that.hasClass('ita-dataSheet')) {
            if (gridLastSel[gridId]) {
                formVal += "&" + gridId + "[gridParam][itaDataSheet][active]=true";
            } else {
                formVal += "&" + gridId + "[gridParam][itaDataSheet][active]=false";
            }
        }
    });

    /*
     * ID per subgrid
     */
    if (myId) {
        var $elObj = $('#' + protSelector(myId));

        if ($elObj.data('ita-grid-id') || $elObj.hasClass('ita-jqgrid-subgrid')) {
            var $subGrid = $elObj.find('table').filter(function () {
                return $(this).data('ita-grid-parent') === myId;
            });

//        if ($subGrid.length) {
//            this.parametri.childId = $subGrid.data('ita-grid-id');
//            this.parametri.subgridChildId = $subGrid.attr('id');
//        }

            if ($elObj.data('ita-grid-id')) {
                /*
                 * Nel caso sia una subgrid...
                 */
                this.parametri.isSubgrid = true;
                this.parametri.subgridId = myId;

                myId = $elObj.data('ita-grid-id');
            }

            this.parametri.subgridData = Array();

            /*
             * Creo una lista delle tabelle padre partendo da quella attuale
             * e una lista con i rispettivi rowid
             */
            var subgridsList = [$elObj.attr('id')];
            var subgridsRwId = [(this.parametri.rowid ? this.parametri.rowid : "")];
            var $tmpGrid = $elObj;
            while ($tmpGrid.data('ita-grid-id')) {
                subgridsList.push($tmpGrid.data('ita-grid-parent'));
                subgridsRwId.push($tmpGrid.data('ita-grid-parent-rowid'));
                $tmpGrid = $('#' + protSelector($tmpGrid.data('ita-grid-parent')));
            }

            /*
             * Capovolgo le liste
             */
            subgridsList.reverse();
            subgridsRwId.reverse();

            /*
             * Per ogni oggetto della lista creo un entry in subgridData
             */
            for (var i in subgridsList) {
                $tmpGrid = $('#' + protSelector(subgridsList[i]));
                var realId = $tmpGrid.data('ita-grid-id') ? $tmpGrid.data('ita-grid-id') : $tmpGrid.attr('id');
                this.parametri.subgridData[realId] = subgridsRwId[i];
            }
        }

        if ($elObj.closest('.ui-jqgrid').length) {
            delete dialogLastFocus['#' + $elObj.parents('.ui-dialog-content:first').attr('id')];
        }
    }

    //
    // Serializzo i dati delle liste
    //
    $(myForm).find('.ita-list').each(function () {
        var listKey = $(this).attr("id") + "[]";
        formVal += "&" + $(this).sortable("serialize", {
            key: listKey
        });
    });

    //
    // Serialzzo i dati array
    //
    for (var key in this.parametri) {
        if (typeof this.parametri[key] == 'object') {
            formVal += objectToQuery(this.parametri[key], key);
            delete this.parametri[key];
        }
    }

    if (modelBackend) {
        formVal += '&nameform=' + nameForm;
        model = modelBackend;
    }

    $activeItaFullscreen.trigger('fullscreen-on');

    delete this.parametri['leggiform'];
    this.url = urlController;

//    var callPost = $.param(this.parametri);
    var callPost = '';
    for (var key in this.parametri) {
        var safeParam = this.parametri[key] == null ? '' : escape(this.parametri[key]);
        callPost = (callPost ? callPost + '&' : '') + key + '=' + safeParam.replace('+', '%2B');
    }

    this.post = 'TOKEN=' + token + '&tmpToken=' + tmpToken + '&model=' + model + '&id=' + myId + '&event=' + ita_event + '&' + callPost + '&' + formVal;
    myForm = null;
    myId = null;
    var tmpForm = null;
    formVal = null;
    var textAreaVal = null;
    var selectVal = null;

    //itaInputMask();

    return true;
};

function objectToQuery(object, key) {
    var ret = '';
    for (var ind in object) {
        if (typeof object[ind] == 'object') {
            ret += objectToQuery(object[ind], key + '[' + ind + ']');
        } else {
//            if (object[ind] !== null && object[ind] !== undefined)
//                ret += "&" + key + "[" + ind + "]=" + object[ind];
            ret += "&" + key + "[" + ind + "]=" + (object[ind] == null ? '' : object[ind]);
        }
    }
    return ret;
}

ItaForm.prototype.errore = function (codice, XMLHttpRequest) {
    switch (codice) {
        case 'parseError':
            alert('Errore nella risposta del server');
            break;
        default:
            alert('(ItaForm) Form non funzionante: ' + codice);
    }
};

ItaForm.prototype.afterRequest = function (risposta) {
    // $("form").validate();
};
/**
 * CLASSE ITAGRID (eredita ITABASE)
 */
function ItaGrid() {
    this.defaultEvent = 'generale';
}

ItaGrid.prototype = new ItaBase();
ItaGrid.prototype.constructor = ItaGrid();
// @TODO ELIMINARE DOPO VERIFICHE
ItaGrid.prototype.beforeRequest = function () {
    //var model = $('form').eq(0).attr('action').substr(1);
    var model = $('form').eq(0).attr('id'); // @FORM
    if (model == '')
        alert('Model di gestione non indicato!');
    this.url = urlController;
    var trobj = $(itaImplode($(this.elemento), 'TR'));
    if (trobj != false) {
        this.rowId = $(trobj).attr('id');
        var valore = $.trim($(this.elemento).html());
        this.post = 'TOKEN=' + token + '&tmpToken=' + tmpToken + '&model=' + model + '&id=' + this.elemento.id + '&event=' + this.parametri['event'] + '&rowid=' + this.rowId + '&nodevalue=' + valore;
        return true;
    } else {
        return false;
    }
};

ItaGrid.prototype.errore = function (codice) {
    switch (codice) {
        case 'parseError':
            alert('Errore nella risposta del server');
            break;
        default:
            alert('(ItaGrid) Form non funzionante: ' + codice);
    }
};

ItaGrid.prototype.afterRequest = function (risposta) {

};

/**
 * CLASSE ITACLICK (eredita ITABASE)
 */

function ItaClick() {
    this.defaultEvent = 'onClick';
}

ItaClick.prototype = new ItaBase();
ItaClick.prototype.constructor = ItaClick();

ItaClick.prototype.beforeRequest = function () {

    //imposto l'url del controller
    this.url = urlController;
    var href = '';
    //separo il model dai possibili parametri
    if (this.elemento.tagName == 'A') {
        href = $(this.elemento).attr('href').split('#');
        href = href[1].split('?');
        //href = $(this.elemento).attr('href').substr(1).split('?',2);
    } else {
        //href = $(this.elemento).attr('value').substr(1).split('?',2);
        href = $(this.elemento).attr('value').split('#');
        href = href[1].split('?');
    }

    //parametri del click
    this.post = 'TOKEN=' + token + '&tmpToken=' + tmpToken + '&id=' + this.elemento.id + '&model=' + href[0];
    //se non ? presente il parametro event nell'url, l'evento lo prendo in maniera standard
    if ($.url(this.url + '?' + href[1]).param('event') == null) {
        this.post += '&event=' + this.parametri['event'];
    }
    //se sono presenti parametri nel href del link li aggiungo al post
    if (typeof (href[1]) != 'undefined')
        this.post += '&' + href[1];
    return true;
};

ItaClick.prototype.errore = function (codice) {
    switch (codice) {
        case 'parseError':
            alert('Errore nella risposta del server');
            break;
        default:
            alert('(ItaClick) Link non funzionante: ' + codice);
    }
};

ItaClick.prototype.afterRequest = function (risposta) {
    //nessuna azione definita
};


/**
 * CLASSE ItaApp (eredita ITABASE)
 */
function ItaApp() {
    this.defaultEvent = 'click';
}
ItaApp.prototype = new ItaClick();
ItaApp.prototype.constructor = ItaApp;

ItaApp.prototype.afterRequest = function (risposta) {
    $('#desktopBody').children('img').remove();
    if ($('#desktopBody').find(".ita-buttonbar-wrap").length == 0) {
        $('#desktopBody').find(".ita-buttonbar").css('width', 'auto').wrap('<div class="ita-buttonbar-wrap" />');
    }
    $('#desktopBody .ita-app').children("br").remove();
    resizeTabs();
};

ItaApp.prototype.errore = function (codice) {
    $('#desktopBody').children('img').remove();
    $('#desktopBody').append('Applicazione attualmente non attiva (' + codice + ')');
};


/**
 * CLASSE ITALOAD (eredita ITABASE)
 */

function ItaLoad() {
    this.defaultEvent = 'load';
}

ItaLoad.prototype = new ItaBase();
ItaLoad.prototype.constructor = ItaLoad();

ItaLoad.prototype.beforeRequest = function () {
    this.url = urlController;
    var href = $(this.elemento).attr('rel').substr(1).split('?', 2);
    var model = href[0];
    this.post = 'TOKEN=' + token + '&tmpToken=' + tmpToken + '&id=' + $(this.elemento).attr('id') + '&model=' + model;
    //se non ? presente il parametro event nell'url, l'evento lo prendo in maniera standard
    if ($.url(this.url + '?' + href[1]).param('event') == null) {
        this.post += '&event=' + this.parametri['event'];
    }
    if (typeof (href[1]) != 'undefined')
        this.post += '&' + href[1];
    $(this.elemento).block();
    //pause(300);
    return true;
};

function pause(millisecondi) {
    var now = new Date();
    var exitTime = now.getTime() + millisecondi;

    while (true)
    {
        now = new Date();
        if (now.getTime() > exitTime)
            return;
    }
}

ItaLoad.prototype.errore = function (codice) {
    $(this.elemento).html('Errore di caricamento. Errore: ' + codice);
};

ItaLoad.prototype.afterRequest = function (risposta) {
    $(this.elemento).unblock();
};

function itaUIModel(idDialog) {
    var idWrapper = idDialog + '_wrapper';
    //$('#' + idWrapper).addClass('ui-dialog-content');
    setDialogLayout($("#" + idWrapper));    // ** MM
    dialogShortCutMap["#" + idWrapper] = new Array();
    $("#" + idWrapper).css('height', '100%').show();
    resizeTabs();

}

function itaUIApp(idDialog) {
    var idWrapper = idDialog + '_wrapper';
    var idTabLabel = 'tab-label-' + idDialog;
    var idTabIndex = 'tab-index-' + idDialog;
    var idCloseIcon = 'close-icon-' + idDialog;
    var idPane = 'tab-' + idDialog;
    var idPaneBody = 'tab-' + idDialog + 'Body';
    var obj = $('#' + idDialog).metadata();
    var label = obj.title;
    if ($('#' + idPane).length == 0) {
        var closeIcon = '<span id="' + idCloseIcon + '" class="ui-icon ui-icon-close">Remove tab</span>';

        if ($('#' + idDialog).hasClass('ita-app-portlet')) {
            closeIcon = '<span id="portlet-' + idCloseIcon + '" class="ui-icon ui-icon-trash ita-portlet-trash">Remove tab</span>';
        }

        var tabTemplate = '<li id="' + idTabIndex + '"><a href="#{href}"><span id="' + idTabLabel + '">#{label}<span></a>' + closeIcon + '</li>';
        var li = $(tabTemplate.replace(/#\{href\}/g, "#" + idPane).replace(/#\{label\}/g, label));
        var tabContentHtml = '<span class="appPath">' + label + '</span><div id="' + idPaneBody + '" class="ita-appPane"></div>';
        var tabs = $('#mainTabs');//.tabs();
        $('#mainTabs > .ui-tabs-nav').append(li);
        $('#mainTabs').append('<div id="' + idPane + '"><p>' + tabContentHtml + '</p></div>');
        $('#mainTabs').tabs("refresh");
        $('#mainTabs').tabs().find('.ui-tabs-nav').sortable({
            axis: 'x'
        });
        // fix 151020, aggiunto undelegate per prevenire eventi multipli
        $('#mainTabs').undelegate("#" + idCloseIcon, "click").delegate("#" + idCloseIcon, "click", function () {
            if (obj.closeAuto !== 'undefined' && obj.closeAuto === false) {
                var myModel = $("#" + idWrapper).itaGetChildForms().first().itaGetId();
                if (typeof (myModel) != 'undefined') {
                    itaGo('ItaCall', '', {
                        id: 'before-close-portlet',
                        event: 'onClick',
                        model: myModel,
                        validate: false
                    });
                    return false;
                }
            }

            closeApp(idDialog, true);
        });
    }
    var wrapper_obj = $('#' + idWrapper).detach();
    wrapper_obj.appendTo('#' + idPaneBody);
    $('#' + idWrapper).addClass('ui-dialog-content');
    setDialogLayout($("#" + idWrapper));    // ** MM
    dialogShortCutMap["#" + idWrapper] = new Array();
    //$('#mainTabs').tabs('option','active',1);  

    var index = $('#mainTabs a[href="#' + idDialog + '"]').parent().index();
    $('#mainTabs').tabs('option', 'active', index);
    $("#" + idWrapper).show();
    resizeTabs();
}


function closeApp(idDialog, fireEvent) {
    var idWrapper = idDialog + '_wrapper';
    //
    // Disattivo i TinyMCE
    //

    tinyDeActivate($("#" + idWrapper).find('textarea.ita-edit-tinymce').toArray());

    $("#" + idWrapper).find('.ita-activedTimer').each(function () {
        $(this).stopTime('ita-timer');
    });

    //
    //reset del DOM
    //
    delDialogLayout($("#" + idWrapper));
    delete dialogShortCutMap["#" + idWrapper];
    if (fireEvent == true) {
        var myModel = $("#" + idWrapper).itaGetChildForms().first().itaGetId(); // @FORM FIXED 16.03.15 | 07.10.15
        if (typeof (myModel) != 'undefined') {
            itaGo('ItaCall', '', {
                id: 'close-portlet',
                event: 'onClick',
                model: myModel,
                validate: false
            });
        }
    }
    //
    // Rimuovo panel
    //
    var index = $('#mainTabs li').index($('#tab-index-' + idDialog));
    if (index != -1) {
        var tab = $('#mainTabs').find(".ui-tabs-nav li:eq(" + index + ")").remove();
        $("#tab-" + idDialog).remove();
        $("#mainTabs").tabs("refresh");
    }

}

function itaUIDialog(idDialog) {
    var idWrapper = idDialog + '_wrapper';
    var saveParent = $("#" + idWrapper).parent().attr('id');

    /*
     * Se Ë una dialog div.ita-model la trasformo in form
     * per far convalidare correttamente i campi all'itaGo.
     */
    var dialogEl = document.getElementById(idDialog);
    if ($(dialogEl).is('div.ita-model')) {
        var formEl = $('<form>')[0];

        $.each(dialogEl.attributes, function () {
            formEl.setAttribute(this.name, this.value);
        });

        $(dialogEl).children().clone(true, true).appendTo(formEl);
        dialogEl.parentNode.replaceChild(formEl, dialogEl);
    }

    var obj = $('#' + idDialog).metadata();
    var myCloseOnEscape = false;

    var closeButton = true;
    if (typeof (obj.closeButton) != 'undefined')
        closeButton = obj.closeButton;
    delete obj.closeButton;

    var maximized = false;
    if (typeof (obj.maximized) != 'undefined')
        maximized = obj.maximized;
    delete obj.maximized;

    var maximizedSize = false;
    if (typeof (obj.maximizedSize) != 'undefined')
        maximizedSize = obj.maximizedSize;
    delete obj.maximizedSize;

    var closeAuto = true;
    if (typeof (obj.closeAuto) != 'undefined')
        closeAuto = obj.closeAuto;
    delete obj.closeAuto;

    var dialogHeader = true;
    if (typeof (obj.dialogHeader) != 'undefined')
        dialogHeader = obj.dialogHeader;
    delete obj.dialogHeader;
    var uiDiagObj = {};
    uiDiagObj.autoOpen = false;
    uiDiagObj.closeOnEscape = false;
    if (closeButton == true) {
        myCloseOnEscape = true;
    }
    //    if (closeButton == true) {
    //        uiDiagObj.closeOnEscape = true;
    //    }

    //    if (typeof (obj.closeOnEscape) != 'undefined') {
    //        uiDiagObj.closeOnEscape = obj.closeOnEscape;
    //    }
    if (typeof (obj.closeOnEscape) != 'undefined') {
        myCloseOnEscape = obj.closeOnEscape;
    }

    uiDiagObj.closeText = '';
    uiDiagObj.height = 'auto';
    uiDiagObj.width = 'auto';
    uiDiagObj.minHeight = 0;
    uiDiagObj.minWidth = 0;
    uiDiagObj.bgiframe = true;

    if (saveParent) {
        uiDiagObj.appendTo = "#" + saveParent;
    }
    uiDiagObj.open = function (eventi, ui) {
        if (closeButton == false) {
            $(this).parent().children().children('.ui-dialog-titlebar-close').hide();
        }
        setDialogLayout(this);
        dialogShortCutMap["#" + idWrapper] = new Array();
        setDialogLightBoxOpt($('#' + idDialog));
        $(this).find('.ui-dialog-content').focus();
    };

    uiDiagObj.beforeClose = function (event, ui) {
        if (closeAuto !== 'undefined' && closeAuto === false) {
            var myModel = $(this).itaGetChildForms().first().itaGetId();
            if (typeof (myModel) != 'undefined') {
                itaGo('ItaCall', '', {
                    id: 'before-close-portlet',
                    event: 'onClick',
                    model: myModel,
                    validate: false
                });
                return false;
            }
        }

        tinyDeActivate($(this).find('textarea.ita-edit-tinymce').toArray());
    };

    uiDiagObj.close = function (event, ui) {
        //var myModel = $(this).find('form').attr('action');

        $(this).find('.ita-activedTimer').each(function () {
            $(this).stopTime('ita-timer');
        });

        var myModel = $(this).itaGetChildForms().first().itaGetId(); // @FORM FIXED 16.03.15 | 07.10.15

        var itaCallOpts = {
            id: 'close-portlet',
            event: 'onClick',
            model: myModel,
            validate: false
        };

        if (myModel) {
            var modelBackend = $.itaGetForm(myModel).itaGetModelBackend();
            if (modelBackend) {
                itaCallOpts.modelBackend = modelBackend;
            }
        }

        delDialogLayout(this);
        delete dialogLightBoxOpt[$(this).attr('id')];
        var keyMap = "#" + $(this).attr('id');
        $(keyMap).stopTime();
        delete dialogShortCutMap[keyMap];
        keyMap = null;
        delete dialogLastFocus["#" + $(this).attr('id')];
        $(this).dialog('destroy');
        $(this).remove();
        if (typeof (myModel) != 'undefined') {
            //myModel = myModel.substr(1);
            itaGo('ItaCall', '', itaCallOpts);
        }
        currDialogFocus = getCurrDialog();
        if (currDialogFocus)
            $("#" + currDialogFocus).focus();
        if (dialogLastFocus["#" + currDialogFocus]) {
            $(protSelector("#" + dialogLastFocus["#" + currDialogFocus])).focus();
        }
    };


    uiDiagObj.dragStop = function (event, ui) {
        var dialogContainer = $(this).parents(".ui-dialog");
        if (parseInt(dialogContainer.css("top"), 10) < 0)
            dialogContainer.css("top", 0);
        if (parseInt(dialogContainer.css("left"), 10) + dialogContainer.width() < 10)
            dialogContainer.css("left", 0);
    };

    uiDiagObj.focus = function (event, ui) {
        currDialogFocus = $(this).attr('id');
        //        if(currDialogFocus !='') $("#"+currDialogFocus).focus();

    };


    uiDiagObj.resize = function (event, ui) {
        dialogLayoutStack[$(this).attr('id')].resizeAll();
    };

    if (maximized == true) {
        obj.height = $("#" + idWrapper).parent().innerHeight() - 10;
        if (dialogHeader == false) {
            obj.height = obj.height + 28; //$("#"+idWrapper).parents('.ui-dialog').find('.ui-dialog-titlebar').innerHeight();
        }
        obj.width = $("#" + idWrapper).parent().innerWidth() - 10;
        obj.position = [0, 0];

        if (maximizedSize) {
            var maximizedPercentage = parseInt(maximizedSize);

            obj.width = obj.width / 100 * maximizedPercentage;
            obj.height = obj.height / 100 * maximizedPercentage;
            obj.position = [(window.innerWidth - obj.width) / 2, (window.innerHeight - obj.height) / 2];
        }

        obj.resizable = false;
        obj.draggable = false;
    }
    var key = "";
    for (key in obj) {
        uiDiagObj[key] = obj[key];
    }

    obj = null;
    key = null;

    $("#" + idWrapper).attr('tabindex', '0');
    $("#" + idWrapper).dialog(uiDiagObj);
    $("#" + idWrapper).dialog('open');
    if (dialogHeader == false) {
        $("#" + idWrapper).parents('.ui-dialog').find('.ui-dialog-titlebar').remove();
    }

    if (myCloseOnEscape) {
        $("#" + idWrapper).dialog().on('keydown', function (evt) {
            if (evt.keyCode == 27) {
                evt.stopPropagation();
            }
        }).on('keyup', function (evt) {
            if (evt.keyCode == 27) {
                evt.stopPropagation();
                $("#" + idWrapper).dialog('close');
            }
        }).on('keypress', function (evt) {
            if (evt.keyCode == 27) {
                evt.stopPropagation();
            }
        });
    }

}

function itaJQGrid(id /*, autowidth = false*/) {
    var obj = $('#' + id).metadata();
    var $gridEl = $('#' + id);
    gridLastSel[id] = null;
    gridInlineLock[id] = false;
    if (typeof (obj.readerId) == 'undefined') {
        obj.readerId = 'ROWID';
    }
    if (typeof (obj.hidegrid) == 'undefined') {
        obj.hidegrid = false;
    }
    if (typeof (obj.rowHeight) !== 'undefined') {
        $('#' + id).attr('data-itagridrowheight', obj.rowHeight);
        delete obj.rowHeight;
    }
    var gridObj = {};
    gridObj.datatype = function (postdata) {
        if ($("#" + id).hasClass('ita-jqgrid-active')) {
            var idObj = $("#" + id);
            itaGo('ItaForm', idObj, {
                event: 'onClickTablePager',
                validate: false,
                rows: postdata.rows,
                page: postdata.page,
                sidx: postdata.sidx,
                sord: postdata.sord,
                _search: postdata._search,
                nodeid: postdata.nodeid,
                parentid: postdata.parentid,
                n_level: postdata.n_level
            });
        }
    };
    gridObj.jsonReader = {
        root: "row",
        page: "pagina",
        total: "pagine",
        records: "righe",
        repeatitems: false,
        id: obj.readerId
    };

    gridObj.xmlReader = {
        root: "jqgrid",
        row: "row",
        page: "jqgrid>pagina",
        total: "jqgrid>pagine",
        records: "jqgrid>righe",
        repeatitems: false,
        id: obj.readerId
    };

    gridObj.ondblClickRow = function (rowid, indexRow, indexCol, e) {
        clearTimeout(onSelectRowTimer);
        var idObj = $("#" + id);
        itaGo('ItaForm', idObj, {
            event: 'dbClickRow',
            validate: false,
            rowid: rowid
        });

        e.stopPropagation();
    };

    gridObj.onCellSelect = function (rowid, iCol, cellcontent, e) {
        var idObj = $("#" + id);
        var myColModel = $("#" + id).getGridParam('colModel');

        if (!gridObj.multiselect) {
            $gridEl.jqGrid('setSelection', rowid);
        }

        if (myColModel[iCol].itaSelectable == true) {
            var customEvent = false;

            switch (myColModel[iCol].name) {
                case 'VIEWROW':
                    customEvent = 'viewRowInline';
                    break;

                case 'EDITROW':
                    customEvent = 'editRowInline';
                    break;

                case 'DELETEROW':
                    customEvent = 'delRowInline';
                    break;
            }

            this._skipSelectRow = true;

            if (customEvent) {
                if (idObj.data('isDisabled')) {
                    return false;
                }

                itaGo('ItaForm', idObj, {
                    event: customEvent,
                    validate: false,
                    rowid: rowid
                });
            } else {
                itaGo('ItaForm', idObj, {
                    event: 'cellSelect',
                    validate: false,
                    rowid: rowid,
                    iCol: iCol,
                    colName: myColModel[iCol].name,
                    cellContent: cellcontent
                });
            }
        }
    };

    gridObj.afterSaveCell = function (rowid, cellname, value, iRow, iCol) {
        var idObj = $("#" + id);
        itaGo('ItaForm', idObj, {
            event: 'afterSaveCell',
            validate: false,
            rowid: rowid,
            cellname: cellname,
            value: value
        });
    };


    gridObj.caption = "Tabella";
    gridObj.autowidth = false;
    gridObj.rowNum = 10;
    gridObj.rowList = [10, 20];
    gridObj.pager = id + "-ita-pager";
    gridObj.cellsubmit = 'clientArray';
    gridObj.viewrecords = true;
    gridObj.sortable = true;
    gridObj.scrollOffset = 0;

    /*
     * Modifiche per subGrid, 23/09/2016
     */

    if (arguments[1] && arguments[1] === true) {
        gridObj.autowidth = true;
    }

    gridObj.subGrid = false;
    if (obj && obj.subGrid == true) {
        gridObj.subGrid = true;
    }

    var $subGrid = $('#' + id + ' > tbody > #baseRow > td > .ita-jqGrid');
    $subGrid.parent().detach();

    if ($subGrid.length) {
        $('#' + id).addClass('ita-jqgrid-subgrid');
        $subGrid.addClass('ita-jqgrid-subgrid');
    }

    gridObj.subGridRowColapsed = function (childDivId, parentId) {
        var idObj = $("#" + id);

        var $cSubGrid = $('.ita-jqgrid-active').filter(function () {
            return $(this).data('ita-grid-parent') === id;
        });

        var params = {
            event: 'subGridRowCollapsed',
            validate: false,
            rowid: parentId
        };

        if ($cSubGrid.length) {
            params.childId = $cSubGrid.data('ita-grid-id');
            params.subgridChildId = $cSubGrid.attr('id');
        }

        itaGo('ItaForm', idObj, params);
    };

    gridObj.subGridRowExpanded = function (childDivId, parentId) {
        var subgridid = '',
            parentSuffix = $('#' + id).data('ita-grid-suffix'),
            suffix = (parentSuffix ? parentSuffix : '') + '_' + parentId;

        if ($subGrid.length) {
            subgridid = $subGrid.attr('id') + suffix;
            var $cSubGrid = $subGrid.clone(true).attr('id', subgridid);
            $('#' + childDivId).append($cSubGrid);
            itaJQGrid(subgridid, true);
            $cSubGrid.data('ita-grid-id', $subGrid.attr('id'))
                .data('ita-grid-parent', id)
                .data('ita-grid-parent-rowid', parentId)
                .data('ita-grid-suffix', suffix);
        }

        var idObj = $("#" + id);

        var params = {
            event: 'subGridRowExpanded',
            validate: false,
            rowid: parentId,
            subgridDivId: childDivId
        };

        if (subgridid) {
            params.childId = $subGrid.attr('id');
            params.subgridChildId = subgridid;
        }

        itaGo('ItaForm', idObj, params);
    };

    gridObj.colMenu = true;

    /*
     * Fine modifiche subGrid
     */

    var key = "";
    var filterToolbar = false;
    var navGrid = false;
    var navButtonAdd = false;
    var navButtonDel = false;
    var navButtonEdit = false;
    var navButtonView = false;
    var navButtonExcel = false;
    var navButtonPrint = false;
    var navButtonRefresh = false;
    var navButtonColch = true;
    var navButtonCopy = false;
    var disableselectall = false;
    var sortablerows = false;
    var onExpandNode = false;
    var onCollapseNode = false;
    var showInlineButtons = false;
    var frozenInlineButtons = false;
    var expandAllButton = false;
    var showAuditColumns = false;
    var showRecordStatus = false;
    var multiselectEvents = false;

    for (key in obj) {
        if (key == 'filterToolbar') {
            filterToolbar = obj[key];
            continue;
        }
        if (key == 'columnChooser') {
            navButtonColch = obj[key];
            continue;
        }
        if (key == 'sortablerows') {
            sortablerows = obj[key];
            continue;
        }
        if (key == 'navGrid') {
            navGrid = obj[key];
            continue;
        }
        if (key == 'navButtonAdd') {
            navButtonAdd = obj[key];
            continue;
        }
        if (key == 'navButtonDel') {
            navButtonDel = obj[key];
            continue;
        }
        if (key == 'navButtonEdit') {
            navButtonEdit = obj[key];
            continue;
        }
        if (key == 'navButtonView') {
            navButtonView = obj[key];
            continue;
        }
        if (key == 'navButtonExcel') {
            navButtonExcel = obj[key];
            continue;
        }
        if (key == 'navButtonPrint') {
            navButtonPrint = obj[key];
            continue;
        }
        if (key == 'navButtonRefresh') {
            navButtonRefresh = obj[key];
            continue;
        }
        if (key == 'navButtonCopy') {
            navButtonCopy = obj[key];
            continue;
        }
        if (key == 'disableselectall') {
            disableselectall = obj[key];
            continue;
        }
        if (key == 'onExpandNode') {
            onExpandNode = obj[key];
            continue;
        }
        if (key == 'onCollapseNode') {
            onCollapseNode = obj[key];
            continue;
        }
        if (key == 'showInlineButtons') {
            showInlineButtons = obj[key];
            continue;
        }
        if (key == 'frozenInlineButtons') {
            frozenInlineButtons = obj[key];
            continue;
        }
        if (key == 'expandAllButton') {
            expandAllButton = obj[key];
            continue;
        }
        if (key == 'showAuditColumns') {
            showAuditColumns = obj[key];
            continue;
        }
        if (key == 'showRecordStatus') {
            showRecordStatus = obj[key];
            continue;
        }
        if (key == 'multiselectEvents') {
            multiselectEvents = obj[key];
            continue;
        }
        gridObj[key] = obj[key];
    }

    var ita_colnames = "";
    gridObj.colModel = new Array();

    if (showInlineButtons) {
        if (showInlineButtons === true || showInlineButtons.view === true) {
            ita_colnames += "'',";

            gridObj.colModel.push({
                index: "VIEWROW",
                itaSelectable: true,
                name: "VIEWROW",
                width: 20,
                resizable: false,
                search: false,
                sortable: false,
                fixed: true,
                frozen: frozenInlineButtons
            });
        }

        if (showInlineButtons === true || showInlineButtons.edit === true) {
            ita_colnames += "'',";

            gridObj.colModel.push({
                index: "EDITROW",
                itaSelectable: true,
                name: "EDITROW",
                width: 20,
                resizable: false,
                search: false,
                sortable: false,
                fixed: true,
                frozen: frozenInlineButtons
            });
        }

        if (showInlineButtons === true || showInlineButtons.delete === true) {
            ita_colnames += "'',";

            gridObj.colModel.push({
                index: "DELETEROW",
                itaSelectable: true,
                name: "DELETEROW",
                width: 20,
                resizable: false,
                search: false,
                sortable: false,
                fixed: true,
                frozen: frozenInlineButtons
            });
        }
    }

    //var tableTh = $('#' + id).find('th');
    $('#' + id).find('th').each(function () {
        ita_colnames = ita_colnames + "\'" + addslashes($(this).html()) + "\',";
    });

    var ita_colmodel = "";
    var ita_metaHeader = new Array();

    $('#' + id).find('#baseRow td').each(function () {
        /*
         * Controllo per subGrid, 26/09/2016
         */

        if ($(this).children('.ita-jqGrid').length) {
            return true; // == continue;
        }

        var colmodelObj = new Object();
        ita_colmodel += "},";
        colmodelObj['name'] = $(this).attr('id');
        colmodelObj['index'] = $(this).attr('id');
        colmodelObj['width'] = parseInt($(this).attr('width'));
        if ($(this).attr('sortable'))
            colmodelObj['sortable'] = $(this).attr('sortable');
        if ($(this).attr('formatter')) {
            if ($(this).attr('formatter') == 'eqdate') {
                colmodelObj['formatter'] = $(this).attr('formatter');
            } else {
                colmodelObj['formatter'] = $(this).attr('formatter');
            }
        }
        if ($(this).attr('editable'))
            colmodelObj['editable'] = $(this).attr('editable');

        var metadata = $(this).metadata();
        if (typeof (metadata['colEvent']) != 'undefined') {
            var colParam = new Array();
            if (typeof (metadata['colEventIcon']) != 'undefined') {
                colParam['colEventIcon'] = metadata['colEventIcon'];
            }
            ita_metaHeader[$(this).attr('id')] = colParam;
        }

        for (var indx in metadata)
        {
            colmodelObj[indx] = metadata[indx];
        }
        gridObj.colModel.push(colmodelObj);
    });

    $('#' + id).addClass('scroll').attr('cellpadding', '0').attr('cellspacing', '0').removeClass('ita-jqGrid').addClass('ita-jqGrid-activated');
    if (gridObj.resizeToParent) {
        $('#' + id).addClass('ita-jqGrid-resizetoparent');
    }
    $('#' + id).find('thead').remove();
    $('#' + id).find('tbody').remove();
    if (gridObj.pager)
        $('#' + id).parent().parent().parent().append('<div class="ui-corner-bottom" id="' + gridObj.pager + '" style="height: 32px"></div>');

    if (showAuditColumns) {
        ita_colnames += "'Utente mod.','Data mod.',";

        gridObj.colModel.push({
            index: 'CODUTE',
            name: 'CODUTE',
            align: 'center',
            width: 120,
            fixed: true
        }, {
            index: 'DATATIMEOPER',
            name: 'DATATIMEOPER',
            search: false,
            align: 'center',
            width: 125,
            fixed: true
        });
    }

    if (showRecordStatus) {
        ita_colnames += "'Dis.',";

        gridObj.colModel.push({
            index: 'FLAG_DIS',
            name: 'FLAG_DIS',
            editable: false,
            formatter: 'checkbox',
            align: 'center',
            search: true,
            stype: 'select',
            editoptions: {value: {'': '---TUTTI---', 'D': 'Disabilitato', 'A': 'Abilitato'}},
            width: 80,
            fixed: true
        });
    }

    ita_colnames = '[' + ita_colnames.substr(0, ita_colnames.length - 1) + ']';
    gridObj.colNames = eval(ita_colnames);

    if (navButtonColch) {
        var localStorageGridId = id.split('_')[0] + '_' + id.split('_').pop();

        $('#' + id).bind('jqGridRemapColumns jqGridResizeStop', function () {
            var gridElement = $gridEl[0];
            if (gridElement.blockLocalStorage) {
                gridElement.blockLocalStorage = null;
                return;
            }

            if (!isLocalStorageAvailable) {
                return;
            }

            var localColModel = $('#' + id).jqGrid('getGridParam', 'colModel');
            var localColNames = $('#' + id).jqGrid('getGridParam', 'colNames');

            localColModel = $.extend([], localColModel);
            localColNames = $.extend([], localColNames);

            if (gridObj.subGrid === true) {
                localColModel.shift();
                localColNames.shift();
            }

            for (var i = localColModel.length - 1; i > -1; i--) {
                if (typeof localColModel[i].formatter === 'function') {
                    localColModel[i].formatterFunction = localColModel[i].formatter.name;
//                    delete localColModel[i].formatter;
                }

                if (gridObj.multiselect == true) {
                    if (['cb'].indexOf(localColModel[i].name) > -1) {
                        localColModel.splice(i, 1);
                        localColNames.splice(i, 1);
                    }
                }
            }

            if (gridObj.treeGrid == true) {
                for (var i = localColNames.length - 1; i > -1; i--) {
                    if (['level', 'parent', 'isLeaf', 'expanded', 'loaded', 'icon'].indexOf(localColNames[i]) > -1) {
                        localColModel.splice(i, 1);
                        localColNames.splice(i, 1);
                    }
                }
            }

            localStorage.setItem(localStorageGridId, JSON.stringify({
                colModel: localColModel,
                colNames: localColNames
            }));

            $('#' + id + '_gridConfig').css('color', 'red');
        });

        if (isLocalStorageAvailable && localStorage.getItem(localStorageGridId)) {
            var colModelMapNames = function (v) {
                return v.name;
            };

            var localData = JSON.parse(localStorage.getItem(localStorageGridId)),
                hasSameColNames = true,
                localDataColNames = localData.colModel.map(colModelMapNames),
                currentDataColNames = gridObj.colModel.map(colModelMapNames);

            for (var i = localDataColNames.length - 1; i > -1; i--) {
                if (currentDataColNames.indexOf(localDataColNames[i]) < 0) {
                    localStorage.removeItem(localStorageGridId);
                    hasSameColNames = false;
                    break;
                }
            }

            if (hasSameColNames) {
                for (var i = localData.colModel.length - 1; i > -1; i--) {
                    if (localData.colModel[i].formatterFunction) {
                        localData.colModel[i].formatter = window[localData.colModel[i].formatterFunction];
                        delete localData.colModel[i].formatterFunction;
                    }
                }

                gridObj.colModel = localData.colModel;
                gridObj.colNames = localData.colNames;
                gridObj.shrinkToFit = false;
            }
        }
    }

    $('#' + id).jqGrid(gridObj);

    $('#' + id).jqGrid('setGridParam', {
        gridComplete: function () {
            gridLastSel[id] = null;

            $('#' + id).find('div.ita-html').each(function () {
                parseHtmlContainer($(this), 'html');
            });

            if (!gridObj.multiselect) {
                $gridEl.find('.jqgrow').find('input, select, textarea').on('focus', function () {
                    $gridEl.jqGrid('setSelection', $(this).closest('tr').attr('id'));
                });

                $gridEl.find('.jqgrow button').on('click', function () {
                    $gridEl.jqGrid('setSelection', $(this).closest('tr').attr('id'));
                });
            }

            $('#' + id).find('td').each(function () {
                var idObj = $("#" + id);
                var myColModel = $("#" + id).getGridParam('colModel');
                if (myColModel[$(this).index()].itaSelectable && myColModel[$(this).index()].itaSelectable == true) {
                    $(this).css('cursor', 'pointer');
                }
            });

            /*
             * Controllo se ci sono colonne con larghezza minima (minWidth)
             * che non la rispettano
             */
            var gridColModel = $gridEl.getGridParam('colModel');
            for (var i in gridColModel) {
                if (gridColModel[i].minWidth && gridColModel[i].minWidth > gridColModel[i].width) {
                    $gridEl[0].blockLocalStorage = true;
                    $gridEl.jqGrid('setColWidth', gridColModel[i].name, gridColModel[i].minWidth, false);
                }
            }

            if (showInlineButtons) {
                var $showIcon = $('#' + id).find('td[aria-describedby$="VIEWROW"]').html('<span class="ui-icon ui-icon-eye" title="Visualizza"></span>');
                var $editIcon = $('#' + id).find('td[aria-describedby$="EDITROW"]').html('<span class="ui-icon ui-icon-pencil" title="Modifica"></span>');
                var $trashIcon = $('#' + id).find('td[aria-describedby$="DELETEROW"]').html('<span class="ui-icon ui-icon-trash" title="Cancella"></span>');
                creaTooltip($editIcon);
                creaTooltip($showIcon);
                creaTooltip($trashIcon);
            }

            if (expandAllButton) {
                function _triggerAllChilds(_id_, btnClass) {
                    $('#' + id + ' #' + _id_ + ' .treeclick.' + btnClass).click();
                    var childs = $('#' + id).jqGrid('getNodeChildren', {_id_: _id_});
                    for (var i = childs.length - 1; i > -1; i--) {
                        _triggerAllChilds(childs[i]._id_, btnClass);
                    }
                }

                $('#' + id).find('.tree-wrap > .treeclick').not('.tree-leaf').parent().each(function () {
                    var $divWrap = $('<div class="tree-wrap" style="z-index: 1; left: ' + (parseInt(this.children[0].style.left)) + 'px;"></div>'),
                        $expandAll = $('<span class="ui-icon ui-icon-circle-b-plus treeclick"></span>'),
                        $collapseAll = $('<span class="ui-icon ui-icon-circle-b-minus treeclick"></span>');

                    $expandAll.on('click', function () {
                        if (onExpandNode) {
                            document.getElementById(id)._doTriggerAll = true;
                            $(this).parent().next().find('.treeclick.tree-plus').click();
                        } else {
                            _triggerAllChilds($(this).closest('tr').attr('id'), 'tree-plus');
                        }
                    });

                    $collapseAll.on('click', function () {
                        if (onCollapseNode) {
                            document.getElementById(id)._doTriggerAll = true;
                            $(this).parent().next().find('.treeclick.tree-minus').click();
                        } else {
                            _triggerAllChilds($(this).closest('tr').attr('id'), 'tree-minus');
                        }
                    });

                    $divWrap.append($collapseAll).append($expandAll);
                    $divWrap.insertBefore(this);

                    if (this.children[0].classList.contains('tree-plus')) {
                        $collapseAll.css('visibility', 'hidden');
                    }
                });

            }
        },
        onSelectRow: function (rowid, status) {
            if (this._skipSelectRow === true) {
                $(this).jqGrid('setSelection', rowid, false);
                this._skipSelectRow = false;
                return false;
            }

            // Utilizzo il selettore per attributo id per accettare 'rowid' che non rispettano
            // la sintassi standard degli id.
            var selectcbox = $(this).find('[id="' + rowid + '"]').find("td > input.cbox");
            if (selectcbox.length) {
                if (status) {
                    if ($(selectcbox).prop('disabled')) {
                        $(this).jqGrid('setSelection', rowid, false);
                    }
                }

                if (multiselectEvents) {
                    itaGo('ItaForm', $("#" + id), {
                        event: 'onSelectCheckRow',
                        validate: false,
                        rowid: rowid,
                        status: status,
                        bloccaui: false
                    });
                }
            } else if (status) {
                if ($(this).hasClass('ita-dataSheet')) {
                    var objthis = this;
                    clearTimeout(editRowTimer);
                    editRowTimer = setTimeout(function () {
                        inlineEditHandle(objthis, rowid, status);
                    }, 200);
                }

                if ($(this).metadata()['onSelectRow'] == true) {
                    clearTimeout(onSelectRowTimer);
                    onSelectRowTimer = setTimeout(function () {
                        itaGo('ItaForm', $("#" + id), {
                            event: 'onSelectRow',
                            validate: false,
                            rowid: rowid,
                            bloccaui: false
                        });
                    }, 200);
                }
            }
        },
        afterEditCell: function (rowid, cellname, value, iRow, iCol) {
            var $el = $('#' + id).find('#' + iRow + "_" + cellname);
            if ($el.is('select')) {
                $el.change(function (e) {
                    $('#' + id).jqGrid('saveCell', iRow, iCol);
                });
            }
        },
        onSelectAll: function (aRowids, status) {
            if (multiselectEvents) {
                itaGo('ItaForm', $("#" + id), {
                    event: 'onSelectCheckAll',
                    validate: false,
                    rowids: aRowids,
                    status: status,
                    bloccaui: false
                });
            }

            if (status == true) {
                var objGrid = this;
                $(objGrid).find("tr.jqgrow").each(function () {
                    var rowid = $(this).attr('id');
                    var selectcbox = $(this).find("td > input.cbox:disabled");
                    if (typeof ($(selectcbox).attr('id')) != 'undefined') {
                        if ($(selectcbox).prop('disabled')) {
                            $(objGrid).jqGrid('setSelection', rowid, false);
                        }
                    }
                });
                $("#cb_" + $(objGrid).attr('id')).prop('checked', true);
            } else {
                return true;
            }
        },
        afterInsertRow: function () {
            //resizeGrid(); //*
        },
        loadComplete: function () {

            //resizeGrid(); //*
        },
        resizeStop: function (w, i) {
            var currentColModel = $gridEl.getGridParam('colModel')[i];
            if (currentColModel.minWidth && currentColModel.minWidth > w) {
                $gridEl.jqGrid('setColWidth', currentColModel.name, currentColModel.minWidth, false);
            }
        }
    });

    if (filterToolbar) {
        $('#' + id).filterToolbar();
    }

    $('#' + id).jqGrid('bindKeys', {
        onEnter: function (rowid) {
            if ($(this).hasClass('ita-dataSheet')) {
                return;
            }

            if ($(document.activeElement).is('input, select, textarea')) {
                return;
            }

            /*
             * Fix per subGrid
             * 03.10.2016
             */
            if ($(document.activeElement).closest('table').get(0) === this) {
                var idObj = $("#" + id);
                itaGo('ItaForm', idObj, {
                    event: 'dbClickRow',
                    validate: false,
                    rowid: rowid
                });
            }
        }
    });

    var navPager = gridObj.pager;
    if (navGrid) {
        $('#' + id).jqGrid('navGrid', '#' + navPager, {
            edit: false,
            add: false,
            del: false,
            search: false,
            refreshstate: "current",
            refresh: navButtonRefresh
        });

        if (navButtonPrint) {
            $('#' + id).jqGrid('navButtonAdd', '#' + navPager, {
                caption: "",
                title: "Stampa Elenco",
                buttonicon: "ui-icon-print",
                id: id + "_printTableToHTML",
                onClickButton: function () {
                    var idObj = $("#" + id);
                    var postdata = idObj.getGridParam('postData');
                    itaGo('ItaForm', idObj, {
                        event: 'printTableToHTML',
                        validate: false,
                        rows: postdata.rows,
                        page: postdata.page,
                        sidx: postdata.sidx,
                        sord: postdata.sord,
                        _search: postdata._search
                    });
                },
                position: "first"
            });

            $('#' + id + "_printTableToHTML");

            /*
             * Per nuova icona:
             * cambiare buttonicon con 'ui-icon-print'
             * commentare ultima riga (removeClass - addClass)
             */
        }

        if (navButtonExcel) {
            $('#' + id).jqGrid('navButtonAdd', '#' + navPager, {
                caption: "",
                title: "Esporta Excel",
                /*
                 * Per nuova icona:
                 * buttonicon: "ita-icon-excel-flat-16x16"
                 */
                buttonicon: "ui-icon-file-report",
                id: id + "_exportTableToExcel",
                onClickButton: function () {
                    var idObj = $("#" + id);
                    var postdata = idObj.getGridParam('postData');
                    itaGo('ItaForm', idObj, {
                        event: 'exportTableToExcel',
                        validate: false,
                        rows: postdata.rows,
                        page: postdata.page,
                        sidx: postdata.sidx,
                        sord: postdata.sord,
                        _search: postdata._search
                    });
                },
                position: "first"
            });
            $('#' + id + "_exportTableToExcel");
        }

        if (navButtonDel) {
            $('#' + id).jqGrid('navButtonAdd', '#' + navPager, {
                caption: "",
                title: "Cancella",
                buttonicon: "ui-icon-trash",
                id: id + "_delGridRow",
                onClickButton: function () {
                    var rowid = $('#' + id).getGridParam('selrow');
                    if (rowid != null) {
                        var idObj = $("#" + id);

                        if (idObj.data('isDisabled')) {
                            return false;
                        }

                        itaGo('ItaForm', idObj, {
                            event: 'delGridRow',
                            validate: false,
                            rowid: rowid
                        });
                    }
                },
                position: "first"
            });
            $('#' + id + "_delGridRow").addClass('ita-delgridrow');
        }

        if (navButtonView) {
            $('#' + id).jqGrid('navButtonAdd', '#' + navPager, {
                caption: "",
                title: "Lettura",
                buttonicon: "ui-icon-eye",
                id: id + "_viewGridRow",
                onClickButton: function () {
                    var rowid = $('#' + id).getGridParam('selrow');
                    if (rowid != null) {
                        var idObj = $("#" + id);

                        if (idObj.data('isDisabled')) {
                            return false;
                        }

                        itaGo('ItaForm', idObj, {
                            event: 'viewGridRow',
                            validate: false,
                            rowid: rowid
                        });
                    }
                },
                position: "first"
            });

            $('#' + id + "_viewGridRow").addClass('ita-viewgridrow');
        }

        if (navButtonCopy) {
            $('#' + id).jqGrid('navButtonAdd', '#' + navPager, {
                caption: "",
                title: "Duplica",
                buttonicon: "ui-icon-files",
                id: id + "_copyGridRow",
                onClickButton: function () {
                    var rowid = $('#' + id).getGridParam('selrow');
                    if (rowid != null) {
                        var idObj = $("#" + id);

                        if (idObj.data('isDisabled')) {
                            return false;
                        }

                        itaGo('ItaForm', idObj, {
                            event: 'copyGridRow',
                            validate: false,
                            rowid: rowid
                        });
                    }
                },
                position: "first"
            });
        }

        if (navButtonEdit) {
            $('#' + id).jqGrid('navButtonAdd', '#' + navPager, {
                caption: "",
                title: "Modifica",
                buttonicon: "ui-icon-pencil",
                id: id + "_editGridRow",
                onClickButton: function () {
                    var rowid = $('#' + id).getGridParam('selrow');
                    if (rowid != null) {
                        var idObj = $("#" + id);

                        if (idObj.data('isDisabled')) {
                            return false;
                        }

                        itaGo('ItaForm', idObj, {
                            event: 'editGridRow',
                            validate: false,
                            rowid: rowid
                        });
                    }
                },
                position: "first"
            });
            $('#' + id + "_editGridRow").addClass('ita-editgridrow');
        }

        if (navButtonAdd) {
            $('#' + id).jqGrid('navButtonAdd', '#' + navPager, {
                caption: "",
                title: "Aggiungi",
                buttonicon: "ui-icon-plus",
                id: id + "_addGridRow",
                onClickButton: function () {
                    var idObj = $("#" + id);

                    if (idObj.data('isDisabled')) {
                        return false;
                    }

                    itaGo('ItaForm', idObj, {
                        event: 'addGridRow',
                        validate: false
                    });
                },
                position: "first"
            });
            $('#' + id + "_addGridRow").addClass('ita-addgridrow');
        }

        if (navButtonColch) {
            $('#' + id).jqGrid('navSeparatorAdd', '#' + navPager);

            /*
             * Menu configurazione tabella
             */
            var $menu = $('<div id="' + id + '_gridConfig_popupContent" style="position: absolute; display: inline-block; z-index: 9999;"><ul></ul></div>').appendTo($('#' + id).itaGetParentForm()).find('ul').menu().hide();
            var closeTimeout;

            $menu.on('mouseleave', function () {
                closeTimeout = setTimeout(function () {
                    $menu.hide();
                }, 500);
            }).on('mouseenter', function () {
                clearTimeout(closeTimeout);
            });

            $('<li class="ui-menu-item"><a href="#">Configura colonne</a></li>').appendTo($menu).on('click', function () {
                $('#' + id).jqGrid('columnChooser', {
                    done: function (perm) {
                        if (perm) {
                            $('#' + id).jqGrid("remapColumns", perm, true);
                            unlockResizeGrid('');
                            resizeGrid('', false);
                        }
                    }
                });

                $menu.hide();
            });

            $('<li class="ui-menu-item"><a href="#">Ripristina configurazioni</a></li>').appendTo($menu).on('click', function () {
                $('<div title="Ripristina configurazioni"><div class="ita-box ui-state-highlight ui-corner-all" style="padding: 10px;">Al prossimo avvio della tabella saranno ripristinate le configurazioni iniziali.</div></div>').dialog({
                    modal: true,
                    resizable: false,
                    draggable: false,
                    minHeight: 0
                });

                if (isLocalStorageAvailable) {
                    localStorage.removeItem(localStorageGridId);
                }

                $('#' + id + '_gridConfig').css('color', 'inherit');

                $menu.hide();
            });

            $('#' + id).jqGrid('navButtonAdd', '#' + navPager, {
                caption: "",
                title: "Configura Tabella",
                buttonicon: "ui-icon-gear",
                id: id + "_gridConfig",
                onClickButton: function () {
                    if ($menu.is(':visible')) {
                        return $menu.hide();
                    }

                    $menu.show().position({
                        my: "left bottom",
                        at: "left top-5",
                        of: document.getElementById(id + '_gridConfig')
                    });

                    closeTimeout = setTimeout(function () {
                        $menu.hide();
                    }, 1000);
                },
                position: "last"
            });

            if (isLocalStorageAvailable && localStorage.getItem(localStorageGridId)) {
                $('#' + id + '_gridConfig').css('color', 'red');
            }
        }

        if ($('#' + id).hasClass('ita-dataSheet')) {

            $('#' + id).jqGrid('navButtonAdd', '#' + navPager, {
                caption: "",
                title: "Salva riga",
                buttonicon: "ui-icon-disk",
                id: id + "_saveInlineEdit",
                onClickButton: function () {
                    var $lastRow = $('#' + id).find('tr.jqgrow#' + gridLastSel[id]);
                    if (gridSerializedRow[id] !== serializeInlineData($lastRow)) {
                        $lastRow.removeClass('ita-edit-row-changed');
                        gridInlineLock[id] = false;//true;
                        itaGo('ItaForm', $('#' + id), {
                            event: 'afterSaveRow',
                            validate: false,
                            rowid: gridLastSel[id],
                            asyncCall: false,
                            nextRowid: -1
                        });
                    } else {
                        cancelInlineEdit($('#' + id), gridLastSel[id], false, true);
                    }
                },
                position: "last"
            });
            $('#' + id + "_saveInlineEdit").addClass('ita-saveinlineedit ui-state-disabled');


            $('#' + id).jqGrid('navButtonAdd', '#' + navPager, {
                caption: "",
                title: "Esci da modifica",
                buttonicon: "ui-icon-cancel",
                id: id + "_exitInlineEdit",
                onClickButton: function () {
                    cancelInlineEdit($('#' + id), gridLastSel[id], true, true);
                },
                position: "last"
            });
            $('#' + id + "_exitInlineEdit").addClass('ita-exitinlineedit ui-state-disabled');
        }
    }

    $('#gview_' + id).find('th').find('div').each(function () {
        $(this).css('height', 'auto');
    });
    for (var prop in ita_metaHeader) {
        $('#' + id + "_" + prop).append('<div id="itacol' + id + "_" + prop + ' style="display:inline-block;" class="' + ita_metaHeader[prop]['colEventIcon'] + '"></div>').click(
            function () {
                var arrTmp = [];
                arrTmp = $(this).attr('id').split('_');
                var colNameStr = arrTmp[2];
                var idObj = $("#" + id);
                itaGo('ItaForm', idObj, {
                    event: 'colSelect',
                    validate: false,
                    colName: colNameStr
                });
            });
    }
    //    $(tableTh).each(function() { 
    //        console.log($(this).attr('id'));
    //        $('#' + $(this).attr('id')).each(function(){
    //            $(this).append('<div style="display:inline-block;" class="ui-icon ui-icon-gear"></div>');
    //        });
    //    });    

    if (disableselectall) {
        $("#cb_" + id).hide();
    }
    if (sortablerows) {
        var startRowIndex;
        $('#' + id).jqGrid('sortableRows', {
            start: function (ev, ui) {
                startRowIndex = ui.item[0].rowIndex;
            },
            update: function (ev, ui) {
                var stopRowIndex = ui.item[0].rowIndex;
                var rowid = ui.item[0].id;
                var idObj = $("#" + id);
                var currModel = idObj.itaGetParentForm().itaGetId(); // $(itaImplode(idObj, 'form, div.ita-model')).attr('id'); // @FORM FIXED 16.03.15 | 07.10.15
                itaGo('ItaForm', idObj, {
                    event: 'sortRowUpdate',
                    rowid: rowid,
                    startRowIndex: startRowIndex,
                    stopRowIndex: stopRowIndex
                });
            }
        });
    }

    /* Tree Grid - eventi per gestione lazy */
    var $jqGrid = $('#' + id).on('ita-expand-node', function (e, rowid, postdata) {
        if (onExpandNode) {
            var parent_record = $jqGrid.jqGrid('getRowData', rowid);
            parent_record["_id_"] = rowid;
            var childrens = $jqGrid.jqGrid('getNodeChildren', parent_record);
            if (childrens.length && childrens[0]._id_ === rowid) {
                childrens.splice(0, 1);
            }

            itaGo('ItaForm', $jqGrid, {
                event: 'expandNode',
                validate: false,
                rowid: rowid,
                _search: postdata._search,
                treeNodeHasChilds: childrens.length > 0 ? true : false,
                doExpandAll: document.getElementById(id)._doTriggerAll ? true : false
            });

            document.getElementById(id)._doTriggerAll = false;
        }

        $jqGrid.find('#' + rowid + ' > td > .tree-wrap > .ui-icon-circle-b-minus').css('visibility', 'visible');
    }).on('ita-collapse-node', function (e, rowid, postdata) {
        if (onCollapseNode) {
            itaGo('ItaForm', $jqGrid, {
                event: 'collapseNode',
                validate: false,
                rowid: rowid,
                doCollapseAll: document.getElementById(id)._doTriggerAll ? true : false
            });

            document.getElementById(id)._doTriggerAll = false;
        }

        $jqGrid.find('#' + rowid + ' > td > .tree-wrap > .ui-icon-circle-b-minus').css('visibility', 'hidden');
    });

    $('#' + id).jqGrid('setFrozenColumns');
}

//
// Verifica l'evento di selezione riga e attua le diverse funzioni
//
function inlineEditHandle(gridObj, rowid, status) {
    var gridId = $(gridObj).attr('id');
    var lastRow = $(gridObj).find('tr.jqgrow#' + gridLastSel[gridId]);

    if (rowid && gridLastSel[gridId] != null && rowid !== gridLastSel[gridId]) {
        var $lastRow = $(lastRow);

        if (gridSerializedRow[gridId] !== serializeInlineData($lastRow)) {
            $lastRow.removeClass('ita-edit-row-changed');
            gridInlineLock[gridId] = false;//true;
            $(gridObj).jqGrid('setSelection', gridLastSel[gridId], false);
            itaGo('ItaForm', gridObj, {
                event: 'afterSaveRow',
                validate: false,
                rowid: gridLastSel[gridId],
                asyncCall: false,
                nextRowid: rowid//,
            });
        } else {
            saveInlineEdit(gridObj, gridLastSel[$(gridObj).attr('id')], rowid);
        }
    } else {
        setInlineEdit(gridObj, rowid);
        itaGo('ItaForm', gridObj, {
            event: 'beginInlineEdit',
            validate: false,
            rowid: rowid,
            asyncCall: false
        });
    }
}

//
// Apre in modifica la linea specificata
//
function setInlineEdit(gridObj, rowid, focusName) {
    //$(this).jqGrid('setSelection', rowid, false);
    if (gridInlineLock[$(gridObj).attr('id')] == false) {
        $(gridObj).jqGrid('editRow', rowid, false, function (rowid) {
            $(gridObj).jqGrid('setSelection', rowid, false);
            var gridId = $(gridObj).attr('id');
            var trContainer = $(gridObj).find('tr.jqgrow#' + rowid).addClass('ita-data-page');
            trContainer.find('input,select,textarea').each(function () {
                var colName = $(this).attr('name'),
                    gridParam = $(gridObj).jqGrid('getGridParam');

                /*
                 * Fix per colName non definito in alcuni casi.
                 * Carlo 25.07.17
                 */
                if (!colName) {
                    colName = gridParam.colNames[this.parentElement.cellIndex];
                    this.id = rowid + '_' + colName;
                }

                if ($(this).is('select')) {
                    var colModel = gridParam.colModel[this.parentElement.cellIndex];
                    if (colModel.editoptions.class) {
                        $(this).addClass(colModel.editoptions.class);
                    }

                    $(this).addClass('ita-edit ita-select').bind('keydown', function (e) {
                        if (e.keyCode == 38 || e.keyCode == 40) {
                            e.stopPropagation();
                        }
                    });
                }

                $(this).attr('name', gridId + '[gridParam][rowDataEdit][' + colName + "]");
                $(this).addClass('ita-edit-cell').css('float', 'left');
            });

            parseHtmlContainer(trContainer, 'html');

            if (focusName != undefined) {
                $(trContainer).find('[name="' + protSelector(focusName) + '"]').focus();
            } else {
                $(trContainer).find('input,select,textarea').filter(':first').focus();
            }

            gridLastSel[$(gridObj).attr('id')] = rowid;
            gridSerializedRow[$(gridObj).attr('id')] = serializeInlineData(trContainer);
            $('#' + gridId + '-ita-pager').find('.ita-saveinlineedit').removeClass('ui-state-disabled');
            $('#' + gridId + '-ita-pager').find('.ita-exitinlineedit').removeClass('ui-state-disabled');
        });

    }
}

//
// Salva la riga in jqGrid ed apre la riga successiva se presente nextRowid
//
function saveInlineEdit(gridObj, rowid, nextRowid, newRowid) {
    newRowid = newRowid !== null ? newRowid : false;

    var gridId = $(gridObj).attr('id');
    var lastRow = $(gridObj).find('tr.jqgrow#' + rowid);
    var focusName = $(gridObj).find(":focus").attr('name');
    var unchangedNewLine = gridSerializedRow[gridId] == serializeInlineData(lastRow) ? true : false;

    if (nextRowid == undefined) {
        nextRowid = false;
    }

    var alreadyCanceled = false;

    $(gridObj).jqGrid('saveRow', rowid, {
        "url": 'clientArray',
        "aftersavefunc": function () {
            //console.log('successssss');
            gridInlineLock[gridId] = false;
            if ($(lastRow).hasClass('jqgrid-new-row') && unchangedNewLine) {
                $(gridObj).jqGrid('delRowData', rowid);
                $('#' + gridId + '-ita-pager').find('.ita-addgridrow').removeClass('ui-state-disabled');
            } else if (newRowid) {
                $(lastRow).attr('id', newRowid);
                $(lastRow).removeClass('jqgrid-new-row');
                $('#' + gridId + '-ita-pager').find('.ita-addgridrow').removeClass('ui-state-disabled');
            }
            if (!alreadyCanceled && nextRowid == -1) {
                alreadyCanceled = true;
                cancelInlineEdit(gridObj, rowid, false, true);
            } else if (nextRowid !== false) {
                setInlineEdit(gridObj, nextRowid, focusName);
            }
        }
    });

    if (!alreadyCanceled && nextRowid == -1) {
        alreadyCanceled = true;
        cancelInlineEdit(gridObj, rowid, false, true);
    }
}

//
// Gestisce il comportamento all'uscita della modifica di una riga
//
function cancelInlineEdit(gridObj, rowid, cancelConfirm, resetSelection) {
    gridObj = $(gridObj).hasClass('ita-edit-cell') ? $(gridObj).parents('.ita-dataSheet') : gridObj;
    rowid = rowid != null ? rowid : gridLastSel[$(gridObj).attr('id')];
    cancelConfirm = cancelConfirm != null ? cancelConfirm : false;
    resetSelection = resetSelection != null ? resetSelection : false;
    var $currRow = $(gridObj).find('tr.jqgrow#' + gridLastSel[$(gridObj).attr('id')]);

    if (cancelConfirm && gridSerializedRow[$(gridObj).attr('id')] !== serializeInlineData($currRow)) {
        if ($('#dataSheet-cancelConfirm-dialog').length == 0) {
            $('body').append('<div id="dataSheet-cancelConfirm-dialog" title="Richiesta di conferma">Sicuro di voler uscire e abbandonare le modifiche?</div>');
        }

        $("#dataSheet-cancelConfirm-dialog").dialog({
            bgiframe: true,
            resizable: false,
            height: 140,
            modal: true,
            close: function (event, ui) {
                $("#dialog").remove();
            },
            buttons: {
                'NO': function () {
                    $(this).dialog('close');
                    $(this).dialog('destroy');
                    $(this).remove();
                },
                'SI': function () {
                    $(this).dialog('close');
                    $(this).dialog('destroy');
                    $(this).remove();

                    cancelInlineEdit(gridObj, rowid, false, resetSelection);
                }
            }
        });
    } else if (resetSelection) {
        var gridId = $(gridObj).attr('id');
        $(gridObj).jqGrid('restoreRow', rowid);
        gridInlineLock[gridId] = false;
        gridLastSel[gridId] = null;
        gridSerializedRow[gridId] = null;
        $(gridObj).jqGrid('resetSelection');
        $(gridObj).find('tr.jqgrow[tabindex=0]').attr('tabindex', '-1');
        $('#' + gridId + '-ita-pager').find('.ita-saveinlineedit').addClass('ui-state-disabled');
        $('#' + gridId + '-ita-pager').find('.ita-exitinlineedit').addClass('ui-state-disabled');
        $('#' + gridId + '-ita-pager').find('.ita-addgridrow').removeClass('ui-state-disabled');
        itaGo('ItaForm', gridObj, {
            event: 'endInlineEdit',
            validate: false,
            rowid: rowid,
            asyncCall: false
        });
    } else {
        $(gridObj).jqGrid('setSelection', rowid);
    }
}

function newInlineEdit(gridObj) {
    // cancelInlineEdit(gridObj, gridLastSel[$(gridObj).attr('id')], false, true, true);
    $(gridObj).jqGrid('restoreRow', gridLastSel[$(gridObj).attr('id')], function () {
        return false;
    });

    var rowid = '0';
    $(gridObj).jqGrid('addRow', {
        rowID: rowid,
        position: 'last'
    });
    //var rowid = $(gridObj).find('.jqgrid-new-row').attr('id');
    $('#' + $(gridObj).attr('id') + '-ita-pager').find('.ita-addgridrow').addClass('ui-state-disabled');

    var gridId = $(gridObj).attr('id');
    var trContainer = $(gridObj).find('tr.jqgrow#' + rowid);
    trContainer.attr('editable', 0);
    trContainer.find('input,select,textarea').each(function () {
        var tmpval = $(this).val();
        $(this).parent('td').html(tmpval);
    });

    setInlineEdit(gridObj, rowid);

    gridLastSel[$(gridObj).attr('id')] = rowid;
    //gridSerializedRow[$(gridObj).attr('id')] = 'new-line';
    gridSerializedRow[$(gridObj).attr('id')] = serializeInlineData(trContainer);
    $('#' + gridId + '-ita-pager').find('.ita-saveinlineedit').removeClass('ui-state-disabled');
    $('#' + gridId + '-ita-pager').find('.ita-exitinlineedit').removeClass('ui-state-disabled');
}

function restoreInlineEdit(gridObj, rowid) {
    $(gridObj).jqGrid('restoreRow', rowid, function () {
        gridInlineLock[$(gridObj).attr('id')] = false;
    });
}

function unlockGridInline(gridObj) {
    gridInlineLock[$(gridObj).attr('id')] = false;
}

function serializeInlineData($row) {
    return $row.find('input, textarea, select').serialize();
}


function itaOnEditFunc(rowid) {
    alert(rowid);
}
function itaSaveRow(result) {
//alert(result);
//console.log(result);
//                            var idObj = $("#" + id);
//                            itaGo('ItaForm', idObj, {
//                                event: 'dbClickRow',
//                                validate: false,
//                                rowid: rowid
//                            });
}

//function itaHtml(cellValue,options,rowObject){
//    var container = $("<div></div>");
//    container.html(cellValue);
//    parseHtmlContainer(container, 'html');
//    return container;
//    
//}

function eqdate(cellvalue, options, rowObject) {
    var retValue = cellvalue;
    if (isDate(cellvalue, 'yyyyMMdd')) {
        var data = new Date(getDateFromFormat(cellvalue, 'yyyyMMdd'));
        retValue = formatDate(data, 'dd/MM/yyyy');
    }
    return retValue;
}

function itaTime(cellvalue, options, rowObject) {
    var retValue = cellvalue;
    var timeformat = "HHMMSS";
    if (typeof (options.colModel.formatoptions) != 'undefined') {
        if (typeof (options.colModel.formatoptions.timeformat) != 'undefined') {
            timeformat = options.colModel.formatoptions.timeformat;
        }
    }
    if (cellvalue != '') {
        switch (timeformat) {
            case "HH":
                retValue = cellvalue.substr(0, 2);
                break;
            case "HHMM":
                retValue = cellvalue.substr(0, 2) + ":" + cellvalue.substr(2, 2);
                break;
            case "HHMMSS":
            default:
                retValue = cellvalue.substr(0, 2) + ":" + cellvalue.substr(2, 2) + ":" + cellvalue.substr(4, 2);
                break;
        }
    }
    return retValue;
}


function itacheckbox(cellvalue, options, rowObject) {
    cellvalue = cellvalue + "";
    cellvalue = cellvalue.toLowerCase();
    var id = 'ita-jqg-editcheckbox-' + options.colModel.name + "-" + options.rowId;
    var bchk;
    if (cellvalue == '') {
        bchk = "";
    } else {
        bchk = cellvalue.search(/(false|0|no|off|n)/i) < 0 ? "checked=\"checked\"" : "";
    }
    return '<input id="' + id + '" class="ita-jqg-editcheckbox {rowid:\'' + options.rowId + '\',cellname:\'' + options.colModel.name + '\'}" type="checkbox" ' + bchk + '/>';
}

function styleEqIFrame() {
    if ($('#eqIFrame').length > 0) {
        var myTheme = $('link.ui-theme').clone();
        var myStyle = $('link.ita-style').clone();
        $(myTheme).each(function () {
            $(this).attr('href', this.href);
        });
        $(myStyle).each(function () {
            $(this).attr('href', this.href);
        });
        $('#eqIFrame').contents().find('link.ui-theme').remove();
        $('#eqIFrame').contents().find("head").prepend(myTheme).append(myStyle);
        myStyle = null;
        myTheme = null;
    }
}
function setDialogLightBoxOpt(objDiag) {
    if ($(objDiag).hasClass('ita-LightBox')) {
        var idWrapper = $(objDiag).attr('id') + '_wrapper';
        var currOpt = {};
        currOpt['diagHeight'] = $(objDiag).parents('.ui-dialog').height();
        currOpt['diagWidth'] = $(objDiag).parents('.ui-dialog').width();
        currOpt['contHeight'] = $(objDiag).find('.ita-LightBox-Content').height();
        currOpt['contWidth'] = $(objDiag).find('.ita-LightBox-Content').width();
        dialogLightBoxOpt[$(objDiag).attr('id')] = currOpt;

        $(objDiag).find('img').load(function () {
            $(this).removeAttr('width').removeAttr('height');
            var new_height = $(this).height();
            var new_width = $(this).width();
            var img_ratio = new_height / new_width;
            if (new_height > $(document).height() - 200) {
                new_height = $(document).height() - 200;
                new_width = parseInt(new_height / img_ratio);
            }
            if (new_width > $(document).width() - 200) {
                new_width = $(document).width() - 200;
                new_height = parseInt(new_height * img_ratio);
            }
            $(this).attr('width', new_width).attr('height', new_height);
            $('#' + idWrapper).dialog("option", "position", 'top');
        });
    }
}
function delDialogLightBoxOpt(objDiag) {
    delete dialogLightBoxOpt[$(objDiag).attr('id')];
}
function setDialogLayout(objDiag) {
    if ($(objDiag).find('.ita-layout-diag').eq(0).attr('id')) {
        var $center = $(objDiag).find('.ita-layout-center');
        var $north = $(objDiag).find('.ita-layout-north');
        var $south = $(objDiag).find('.ita-layout-south');
        var $east = $(objDiag).find('.ita-layout-east');
        var $west = $(objDiag).find('.ita-layout-west');

        var id_north = $north.attr('id');
        var id_north_size = $north.height();
        var id_center = $center.attr('id');
        var id_south = $south.attr('id');
        var id_south_size = $south.height();
        var id_west = $west.attr('id');
        var id_west_size = $west.width();
        var id_east = $east.attr('id');
        var id_east_size = $east.width();

        var north_closed = true, north_hidden = false;
        var south_closed = false, south_hidden = false;
        var east_closed = false, east_hidden = false;
        var west_closed = false, west_hidden = false;

        if (typeof (id_center) != 'undefined')
            id_center = "#" + id_center;

        if (typeof (id_north) != 'undefined') {
            id_north = "#" + id_north;
            north_closed = $north.metadata().initClosed || north_closed;
            north_hidden = $north.metadata().initHidden || north_hidden;
        }
        if (typeof (id_south) != 'undefined') {
            id_south = "#" + id_south;
            south_closed = $south.metadata().initClosed || south_closed;
            south_hidden = $south.metadata().initHidden || south_hidden;
        }
        if (typeof (id_west) != 'undefined') {
            id_west = "#" + id_west;
            west_closed = $west.metadata().initClosed || west_closed;
            west_hidden = $west.metadata().initHidden || west_hidden;
        }
        if (typeof (id_east) != 'undefined') {
            id_east = "#" + id_east;
            east_closed = $east.metadata().initClosed || east_closed;
            east_hidden = $east.metadata().initHidden || east_hidden;
        }

        $(objDiag).itaGetChildForms().css('height', '99%'); // @FORM FIXED 16.03.15 | 07.10.15

        var resizeCallback = function (l, o) {
            resizeGrid(o[0].id, true, true);

            $(protSelector('#' + o[0].id)).find('.ita-flowchart').each(function () {
                itaJsPlumbHelper.activate(this.id);
            });
        };

        var dialogLayout_settings = {
            zIndex: 0,
            resizeWithWindow: false,
            spacing_open: 10,
            spacing_closed: 10,
            north__paneSelector: id_north,
            north__size: id_north_size,
            north__initClosed: north_closed,
            north__initHidden: north_hidden,
            south__paneSelector: id_south,
            south__size: id_south_size,
            south__closable: true,
            south__resizable: true,
            south__slidable: true,
            south__initClosed: south_closed,
            south__initHidden: south_hidden,
            west__paneSelector: id_west,
            west__size: id_west_size,
            west__onresize: $.layout.callbacks.resizePaneAccordions,
            west__minSize: 100,
            west__initClosed: west_closed,
            west__initHidden: west_hidden,
            east__paneSelector: id_east,
            east__size: id_east_size,
            east__onresize: $.layout.callbacks.resizePaneAccordions,
            east__minSize: 100,
            east__initClosed: east_closed,
            east__initHidden: east_hidden,
            center__paneSelector: id_center,
            center__size: 'auto',
            center__minSize: 100,
            applyDefaultStyles: true,
            enableCursorHotkey: false,
            center__onresize_end: resizeCallback
        };
        dialogLayoutStack[$(objDiag).attr('id')] = $(objDiag).find('.ita-layout-diag').layout(dialogLayout_settings);
        dialogLayoutStack[$(objDiag).attr('id')].updatedToResize = window.innerWidth + '' + window.innerHeight;
    }
    return true;
}


function setHomeLayout() {
    var id_center = '#ita-controlpad';
    var id_west = '#menPersonal';
    var id_west_size = $('#menPersonal').width();
    var dialogLayout_settings = {
        zIndex: 0,
        resizeWithWindow: true,
        spacing_open: 6,
        spacing_closed: 6,
        west__paneSelector: id_west,
        west__size: id_west_size,
        west__minSize: 100,
        west__onresize: $.layout.callbacks.resizePaneAccordions,
        center__paneSelector: id_center,
        center__size: 'auto',
        center__minSize: 100,
        applyDefaultStyles: true
    };
    homeLayout = $('#ita-home-content').layout(dialogLayout_settings);
    return true;
}

function delDialogLayout(objDiag) {
    //return; //** MM
    if (dialogLayoutStack[$(objDiag).attr('id')]) {
        //dialogLayoutStack.splice($(objDiag).attr('id'),1);
        delete dialogLayoutStack[$(objDiag).attr('id')];
    }
}

function implode(glue, pieces) {
    // Joins array elements placing glue string between items and return one string
    //
    // version: 909.322
    // discuss at: http://phpjs.org/functions/implode
    // +   original by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
    // +   improved by: Waldo Malqui Silva
    // *     example 1: implode(' ', ['Kevin', 'van', 'Zonneveld']);
    // *     returns 1: 'Kevin van Zonneveld'
    return ((pieces instanceof Array) ? pieces.join(glue) : pieces);
}

function setItaTimer(timerParam) {
    var callType = timerParam.serializeForm == true ? 'ItaForm' : 'ItaCall';

    $('#' + timerParam.element).addClass('ita-timer ita-activedTimer').everyTime(timerParam.delay, 'ita-timer', function () {
        var params = {
            bloccaui: false,
            asyncCall: true,
            event: 'ontimer',
            id: this.id
        };

        if (timerParam.model) {
            params.model = timerParam.model;
        } else {
            params.model = $(this).itaGetParentForm().itaGetId();
        }

        if (typeof timerParam.backgroundTick === 'undefined' || timerParam.backgroundTick || ($(this).itaGetParentForm().is(':visible'))) {
            itaGo(callType, this, params);
        }
    });
}

function removeTimer(element) {
    $('#' + element).stopTime('ita-timer');
}

function removeDesktop() {
    token = null;
    tmpToken = null;
    $('.ita-timer').stopTime('ita-timer');
    $("#ita-desktop").remove();
}

function parseDateTime() {
    $('input.ita-datepicker').each(function () {
        var data;
        var ret;
        var parsedDate = $(this).val().substr(0, 10);
        if (isDate(parsedDate, 'yyyy-MM-dd')) {
            data = new Date(getDateFromFormat(parsedDate, 'yyyy-MM-dd'));
            ret = formatDate(data, 'dd/MM/yyyy');
            $(this).addClass('ita-isodate');
            $(this).val(ret);
        } else {
            if (isDate(parsedDate, 'yyyyMMdd')) {
                data = new Date(getDateFromFormat(parsedDate, 'yyyyMMdd'));
                ret = formatDate(data, 'dd/MM/yyyy');
                $(this).addClass('ita-eqdate');
                $(this).val(ret);
            }
        }
    });


    $('input.ita-date').each(function () {
        var data;
        var ret;
        var parsedDate = $(this).val().substr(0, 10);
        if (isDate(parsedDate, 'yyyy-MM-dd')) {
            data = new Date(getDateFromFormat(parsedDate, 'yyyy-MM-dd'));
            ret = formatDate(data, 'dd/MM/yyyy');
            $(this).addClass('ita-isodate');
            $(this).val(ret);
        } else {
            if (isDate(parsedDate, 'yyyyMMdd')) {
                data = new Date(getDateFromFormat(parsedDate, 'yyyyMMdd'));
                ret = formatDate(data, 'dd/MM/yyyy');
                $(this).addClass('ita-eqdate');
                $(this).val(ret);
            }
        }
    });

    $('input.ita-month').each(function () {
        var month;
        var ret;
        if (isDate($(this).val(), 'yyyy-MM')) {
            month = new Date(getDateFromFormat($(this).val(), 'yyyy-MM'));
            ret = formatDate(month, 'MM/yyyy');
            //$(this).addClass('ita-isodate');
            $(this).val(ret);
        } else {
            if (isDate($(this).val(), 'yyyyMM')) {
                month = new Date(getDateFromFormat($(this).val(), 'yyyyMM'));
                ret = formatDate(month, 'MM/yyyy');
                //$(this).addClass('ita-eqdate');
                $(this).val(ret);
            }
        }
    });
}

function formatItaDate(parsedDate, dateReturnValue) {
    var result = '';
    if (parsedDate != '' && parsedDate != null && parsedDate !== 'undefined') {
        var data = new Date(getDateFromFormat(parsedDate, 'dd/MM/yyyy'));
        if (dateReturnValue) {
            switch (dateReturnValue) {
                case 'iso':
                    result = formatDate(data, 'yyyy-MM-dd');
                    break;

                case 'iso-basic':
                    result = formatDate(data, 'yyyyMMdd');
                    break;
            }
        } else {
            result = formatDate(data, 'yyyyMMdd');
        }
    }
    return result;
}

/**
 * (DEPRECATA)
 * @param {type} itaFunction
 * @param {type} itaScript
 * @returns {undefined}
 */
function itaGetScript(itaFunction, itaScript) {
    if (typeof window[itaFunction] == 'undefined' || itaFunction == '') {
        $.ajaxSetup({
            async: false
        });
        /*
         * La funzione originale caricava lo script dalla itajsCore, equivalente alla attuale directory
         * di itaEngine.js
         */
        $.getScript('./public/js/' + itaScript);
        $.ajaxSetup({
            async: true
        });
    }
}

/**
 * Include una libreria di itaEngine
 * @param {String} Percorso del file da includere relativo a 'public/'
 * @param {String} Se specificato, controlla prima la presenza del namespace sotto 'window'
 * @returns {Boolean} Ritorna 'false' se il namespace specificato Ë definito, altrimenti 'true'
 */
function itaGetLib(filename /*, namespace */) {
    var ext = filename.split('.').pop().split('?')[0],
        namespace = arguments[1] || false,
        uri = 'public/' + filename;

    if (namespace) {
        var ns = window, terms = namespace.split('.');
        for (i = 0; i < terms.length; i++) {
            ns = ns[terms[i]];

            if (typeof ns === 'undefined') {
                break;
            }
        }

        if (typeof ns !== 'undefined') {
            return false;
        }
    }

    switch (ext) {
        case 'js':
            $.ajaxSetup({async: false});
            $.getScript(uri);
            $.ajaxSetup({async: true});
            break;

        case 'css':
            if (!$('[data-rel="' + uri + '"]').length) {
                $.get(uri, function (data) {
                    $('head').append('<style type="text/css" data-rel="' + uri + '">' + data + '</style>');
                });
            }
            break;
    }

    return true;
}

function pluploadActivate(idElemento) {
    if (itaEngine.plUploaders[idElemento].runtime == '') {
        itaEngine.plUploaders[idElemento].bind('Init', function (up, params) {
            $('#' + protSelector(up.settings.container) + ' > .plupload').css('z-index', '99999');
        });
        itaEngine.plUploaders[idElemento].init();

        itaEngine.plUploaders[idElemento].bind('FilesAdded', function (up, files) {
            up.start();
            up.refresh();
        });
        itaEngine.plUploaders[idElemento].bind('BeforeUpload', function (up, file) {
            $.blockUI({
                theme: true, // true to enable jQuery UI support
                draggable: true, // draggable option is only supported when jquery UI script is included
                title: 'Upload Files', // only used when theme == true
                message: '<div id="' + up.settings.browseButton + '_pgbar"></div><img src="./public/css/images/wait.gif" /> UPLOAD in corso...'
            });
            //        $('#'+protSelector(up.settings.browseButton+'_pgbar')).progressbar({
            //            value: 37
            //        });
        });

        itaEngine.plUploaders[idElemento].bind('UploadProgress', function (up, file) {
            if (file.percent < 100 && file.percent >= 1) {
                $('#' + protSelector(up.settings.browseButton + '_pgbar')).progressbar({
                    value: file.percent
                });
            } else {
                $('#' + protSelector(up.settings.browseButton + '_pgbar')).fadeOut(600);
            }
        });

        itaEngine.plUploaders[idElemento].bind('FileUploaded', function (up, file, response) {
            var metaData = [];
            var buttonElement = $('#' + protSelector(idElemento) + ' > button.ita-button:first');
            if (buttonElement.length) {
                metaData = buttonElement.metadata();
            }
            $.unblockUI();
            //$('body').unblock();
            var objResponse = eval("(" + response.response + ")");
            var obj = $("#" + protSelector(up.settings.browse_button));
//
//            /* Fix button upload meta, Carlo - 22.06.15 */
            if (metaData.extraCodeBefore) {
                eval(metaData.extraCodeBefore);
            }
            var data = {
                event: 'onClick',
                file: file.name,
                model: metaData.model,
                validate: false,
                response: objResponse.response
            };

            if (metaData.extraData) {
                data = $.extend(data, metaData.extraData);
            }
            itaGo('ItaForm', obj, data);
            if (metaData.extraCode) {
                eval(metaData.extraCode);
            }
        });


//        itaEngine.plUploaders[idElemento].bind('FileUploaded', function (up, file, response) {
//            var metaData = $('#' + protSelector(idElemento) + ' > button.ita-button').metadata();
//            $.unblockUI();
//            //$('body').unblock();
//            var objResponse = eval("(" + response.response + ")");
//            var obj = $("#" + protSelector(up.settings.browse_button));
//
//            /* Fix button upload meta, Carlo - 22.06.15 */
//            if (metaData.extraCodeBefore) {
//                eval(metaData.extraCodeBefore);
//            }
//            var data = {
//                event: 'onClick',
//                file: file.name,
//                model: metaData.model,
//                validate: false,
//                response: objResponse.response
//            };
//            if (metaData.extraData) {
//                data = $.extend(data, metaData.extraData);
//            }
//            itaGo('ItaForm', obj, data);
//            if (metaData.extraCode) {
//                eval(metaData.extraCode);
//            }
//        });
    }
    /* fix per focus Chrome 19.06.15 - Carlo */
    if (jQuery.browser.name == 'Chrome') {
        $('#' + idElemento + ' > button').focus().blur();
    }

//    if (jQuery.browser.name == 'Firefox' && parseFloat(jQuery.browser.version) <= 21.0) {
//        $('#' + idElemento + ' > div.plupload').css('z-index', '0');
//    } else if (jQuery.browser.name == 'Opera') {
//        $('#' + idElemento + ' > div.plupload').css('z-index', '0');
//    } else {
//        $('#' + idElemento + ' > div.plupload').css('z-index', '999999');
//    }
    $('#' + idElemento + ' > div.plupload').css('z-index', '0');
    $('#' + idElemento + ' > button.ita-button').css('z-index', '1');
    $('#' + idElemento + ' > div.ita-plupload-browser').css('z-index', '1');

}

// Senza setTimeout sembra non inizializzare bene le instanze su textarea aperte in precedenza (TinyMCE si apre senza contenuto e non Ë editabile)
function tinyActivate(idElemento, edit) {
    setTimeout(function () {
        var elemento = $('#' + protSelector(idElemento));
        if (elemento.length > 0) {
            /* var parents = $(elemento).parents().toArray();
             for (var index in parents) if ( $(parents[index]).css('display') == 'none' ) {
             // Elemento nascosto, inutile instanziare il tiny
             return false;
             } */

            //var myModel = $(itaImplode($(elemento), 'FORM')).attr('action').substr(1);
            var myModel = $(elemento).itaGetParentForm().itaGetId(); // $(itaImplode($(elemento), 'form, div.ita-model')).attr('id'); // @FORM FIXED 16.03.15 | 07.10.15
            var readOnly = false;
            if ($(elemento).prop('disabled') || $(elemento).prop('readonly')) {
                readOnly = true;
            }
            var metadata = $(elemento).metadata();
            if (metadata.edit == undefined) {
                metadata.edit = false;
            }
            if (metadata.editMode == undefined) {
                metadata.editMode = "base";
            }
            if (metadata.compile == undefined) {
                metadata.compile = false;
            }
            if (metadata.vars == undefined) {
                metadata.vars = false;
            }
            if (metadata.height)
                metadata.height = (metadata.height.indexOf('px') > -1) ? (parseInt(metadata.height) - 120) + 'px' : metadata.height;

            var plugins = ['advlist link image charmap save table contextmenu paste textcolor colorpicker'];
            var toolbar3 = '';
            var fontsize_formats = '8pt 10pt 12pt 14pt 18pt 24pt 36pt';
            var lineheight_heights = 'Interlinea=normal 5pt 6pt 7pt 8pt 9pt 10pt 11pt 12pt 14pt 16pt 18pt 20pt 24pt 32pt 36pt';

            if (metadata.editMode == 'advanced') {
                plugins = ['lineheight visualblocks advlist link image lists charmap print preview hr anchor pagebreak wordcount visualchars code nonbreaking save table contextmenu paste fullscreen textcolor'];
                toolbar3 = 'lineheightselect | hr removeformat visualaid visualblocks | pagebreak | link itaEmbedImg itaEmbedVar itaCompileVar | code';
                //fontsize_formats = '8pt 10pt 11pt 12pt 14pt 16pt 18pt 20pt 24pt 32pt 36pt';
                fontsize_formats = '6pt 7pt 8pt 10pt 11pt 12pt 14pt 16pt 18pt 20pt 24pt 32pt 36pt';
            }

            var fullScreenElement = '<span class="ita-tiny-fullscreen-text" style="margin-left: 7px; vertical-align: initial; color: #ff0000; font-weight: bold;">TORNA SU GESTIONALE</span>';

            if (!tinymce.get(idElemento)) {
                if (metadata.editMode == 'minimal') {
                    tinymce.init({
                        selector: "#" + protSelector(idElemento),
                        readonly: readOnly,
                        width: '100%',
                        height: 'auto',
                        language: 'it',
                        plugins: ['textcolor contextmenu colorpicker fullscreen'],
                        menubar: false,
                        toolbar: 'fontselect fontsizeselect | forecolor backcolor | fullscreen',
                        statusbar: false,
                        contextmenu: 'cut copy paste | formats',
                        forced_root_block: '',
                        fontsize_formats: fontsize_formats,
                        setup: function (editor) {
                            editor.on('FullscreenStateChanged', function () {
                                if (editor.plugins.fullscreen.isFullscreen())
                                    $('.mce-btn[aria-label="Fullscreen"] button').append(fullScreenElement);
                                else
                                    $('.ita-tiny-fullscreen-text').remove();
                            });
                        }
                    });
                } else if (metadata.editMode == 'base' || metadata.editMode == 'advanced') {
                    tinymce.init({
                        selector: "#" + protSelector(idElemento),
                        readonly: readOnly,
                        width: '100%',
                        height: metadata.height,
                        language: 'it',
                        menubar: false,
                        paste_data_images: true,
                        plugins: plugins,
                        contextmenu: 'cut copy paste | charmap formats | inserttable tableprops deletetable cell row column',
                        force_br_newlines: false,
                        force_p_newlines: true,
                        pagebreak_separator: '<p style="page-break-after: always;"><!--pagebreak--></p>',
                        toolbar1: "fontselect fontsizeselect | cut copy paste | undo redo | table | cell row column | tableprops deletetable | fullscreen",
                        toolbar2: "bold italic underline strikethrough | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | forecolor backcolor",
                        toolbar3: toolbar3,
                        lineheight_formats: lineheight_heights,
                        fontsize_formats: fontsize_formats,
                        setup: function (editor) {
                            editor.on('FullscreenStateChanged', function () {
                                if (editor.plugins.fullscreen.isFullscreen())
                                    $('.mce-btn[aria-label="Fullscreen"] button').append(fullScreenElement);
                                else
                                    $('.ita-tiny-fullscreen-text').remove();
                            });
                            editor.addButton('itaEmbedImg', {
                                title: 'Incorpora immagine',
                                icon: ' ita-icon ita-icon-bitmap-24x24',
                                onclick: function () {
                                    itaGo('ItaCall', '', {
                                        id: idElemento,
                                        event: 'openform',
                                        model: 'utiImgBrowser',
                                        validate: false
                                    });
                                }
                            });
                            if (metadata.vars == true)
                                editor.addButton('itaEmbedVar', {
                                    title: 'Incorpora variabile',
                                    icon: ' ita-icon ita-icon-dictionary-24x24',
                                    onclick: function () {
                                        itaGo('ItaForm', '', {
                                            id: idElemento,
                                            event: 'embedVars',
                                            model: myModel,
                                            validate: false
                                        });
                                    }
                                });
                            if (metadata.compile == true)
                                editor.addButton('itaCompileVar', {
                                    title: 'Compila variabili',
                                    icon: ' ita-icon ita-icon-edit-24x24',
                                    onclick: function () {
                                        itaGo('ItaForm', '', {
                                            id: idElemento,
                                            event: 'compileVars',
                                            model: myModel,
                                            validate: false
                                        });
                                    }
                                });
                        },
                        // Tables
                        table_class_list: [
                            {
                                title: 'None',
                                value: ''
                            },
                            {
                                title: 'ita-table-template',
                                value: 'ita-table-template'
                            }
                        ],
                        table_row_class_list: [
                            {
                                title: 'None',
                                value: ''
                            },
                            {
                                title: 'ita-table-header',
                                value: 'ita-table-header'
                            },
                            {
                                title: 'ita-table-footer',
                                value: 'ita-table-footer'
                            }
                        ],
                        table_cell_class_list: [
                            {
                                title: 'None',
                                value: ''
                            }
                        ]
                    });
                }
            }
        }
    }, 0);
}

function tinyDeActivate(elementi) {
    if (typeof elementi == 'string') { //idElemento
        elementi = $('#' + protSelector(elementi)).toArray();
    }
    if (elementi.length > 0) {
        for (var key in elementi) {
            if (tinymce.get($(elementi[key]).attr('id'))) {
                var parents = $(elementi[key]).parents().toArray();
                var hidden = false;
                for (var index in parents)
                    if ($(parents[index]).css('display') == 'none') {
                        if (tinymce.get($(elementi[key]).attr('id'))) {
                            tinymce.get($(elementi[key]).attr('id')).remove();
                        }

                        hidden = true;
                    }
                if (!hidden) {
                    tinymce.get($(elementi[key]).attr('id')).save();
                    tinymce.get($(elementi[key]).attr('id')).remove();
                }
            }
        }
    }
}

function tinySetContent(editor_id, value) {
    var editormce = tinymce.get(editor_id);
    if (editormce) {
        editormce.setContent(value);
    }
}

function tinyInsertContent(editor_id, value) {
    var editormce = tinymce.get(editor_id);
    if (editormce) {
        editormce.insertContent(value);
    }
}

function tinyInsertRawHTML(editor_id, value) {
    var editormce = tinymce.get(editor_id);
    if (editormce) {
        editormce.insertContent(value, {
            format: 'raw'
        });
    }
}

function fullCalendarRender(elements) {
    if (typeof elements == 'string') { // id
        elements = $('#' + protSelector(elements)).toArray();
    }
    if (elements.length > 0) {
        for (var key in elements) {
            $(elements[key]).fullCalendar('render');
        }
    }
}

// 
// DA CONCLUDERE E OTTIMIZZARE
//
function insertAppletTab(container, idapplet, code, name, w, h, m, codebase, archive) {
    alert("Aggiunto");
    var obj = document.createElement("applet");
    obj.setAttribute("code", code);
    obj.setAttribute("width", w + "px");
    obj.setAttribute("height", h + "px");
    obj.setAttribute("name", name);
    obj.setAttribute("id", idapplet);
    obj.setAttribute("codebase", codebase);
    obj.setAttribute("archive", archive);
    $("#" + container).append(obj);
//$( "#dialog-modal" ).append(d);
}

function getScrollBarWidth() {
    var $outer = $('<div>').css({
        width: '100px',
        height: '100px',
        overflow: 'scroll',
        position: 'absolute',
        top: '-9999px'
    }).appendTo('body');
    var widthWithScroll = $('<div>').css({
        width: '100%'
    }).appendTo($outer).outerWidth();
    $outer.remove();
    return 100 - widthWithScroll;
}

function parseHtmlContainer(container, tag) {
    /*
     * 18.10.2016 #nuovo-input-mask
     */
    itaInputUnmaskWithin(container);

    container.find('iframe').each(function () {
        this.onload = function () {
            $(this.contentWindow.document).jkey('f1,f2,f3,f4,f5,f6,f7,f8,f10,f11,f12,pageup,pagedown', function (key) {
                return false;
            }).keydown(function (e) {
                if (e.keyCode == 8) {
                    switch (e.target.nodeName) {
                        case 'HTML':
                        case 'BODY':
                        case 'TABLE':
                        case 'TBODY':
                        case 'TR':
                        case 'TD':
                        case 'DIV':
                            e.preventDefault();
                    }
                }
            });
        };
    });

    if (container.hasClass('ita-mail-body')) {
        $(this).find('a[href^="http://"]').each(function () {
            $(this).attr({
                target: "_blank",
                title: "Apri Esternamente"
            });
        });
        // Verifichiamo se serve - da cancellare
//        return;
    }

    //
    //
    //
    // da vedere con andrea perch√® mette questo <br>
    //container.find('br:first').remove();
    //                    container.find('a').each(function(){
    //                        if($(this).attr('href').substr(0,1) == '#' && ($(this).attr('onClick') == 'undefined' || $(this).attr('onClick') == null)){
    //                            $(this).click(function(){
    //                                //itaGo('ItaClick',this);
    //                                });
    //                        }
    //                    });
    container.find('.ita-app, .ita-dialog, .ita-model, .ita-app-portlet').each(function () {
        if (tag == 'dialogHtml') {
            $(this).removeClass('ita-app').addClass('ita-dialog');
        }

        if (tag == 'appHtml') {
            $(this).addClass('ita-app ita-dialog');
        }

        if (tag == 'innerHtml') {
            $(this).removeClass('ita-app ita-dialog');
        }

        if ($(this).hasClass('ita-app')) {
            itaUIApp($(this).attr('id'));
        } else if ($(this).hasClass('ita-dialog')) {
            itaUIDialog($(this).attr('id'));
        } else if ($(this).hasClass('ita-model')) {
            itaUIModel($(this).attr('id'));
        }
    });
    //                    container.find('.ita-buttonbar').each(function(){
    //                        $(this).addClass('ita-buttonbaractived');
    //                    });
    container.find('.ita-buttonbar').each(function () {
        $(this).removeClass('ita-buttonbar').addClass('ita-buttonbaractived ui-widget-content ui-corner-all').wrapInner("<div class=\"ita-buttonbar-content\"></div>");
    });

    container.find('.ita-tooltip').each(function () {
        if ($(this).attr('id')) {
            creaTooltip($(this).attr('id'));
        } else {
            creaTooltip($(this));
        }
    });

    container.find('.ita-progress-bar').each(function () {
        var pvalue = 0;
        var pmax = 100;
        var prefresh = 0;
        var createLabel = 'Attendere...';
        var completeLabel = 'Completato...';
        var responseTimeout = 0;
        var metadata = $(this).metadata();
        if (typeof (metadata.value) !== 'undefined')
            pvalue = parseInt(metadata.value);
        if (typeof (metadata.max) !== 'undefined')
            pmax = parseInt(metadata.max);
        if (typeof (metadata.refreshDelay) !== 'undefined') {
            prefresh = parseInt(metadata.refreshDelay);
        }
        if (typeof (metadata.createLabel) !== 'undefined') {
            createLabel = metadata.createLabel;
        }
        if (typeof (metadata.completeLabel) !== 'undefined') {
            completeLabel = metadata.completeLabel;
        }
        if (typeof (metadata.responseTimeout) !== 'undefined') {
            responseTimeout = metadata.responseTimeout;
        }

        var openCallback = null;
        if (typeof (metadata.openCallback) != 'undefined') {
            openCallback = metadata.openCallback;
            delete metadata.openCallBack;
        }

        if ($(this).attr('id')) {
            $(this).progressbar({
                value: pvalue,
                max: pmax,
                create: function (event, ui) {
                    $(this).find('.ita-progress-label').width($(this).innerWidth());
                    $(this).find('.ita-progress-label').find('span').eq(0).html(createLabel);
                    if (openCallback != null) {
                        itaGo('ItaCall', '', {
                            id: openCallback.id,
                            event: openCallback.event,
                            model: openCallback.model,
                            validate: false,
                            asyncCall: openCallback.asyncCall,
                            bloccaui: false
                        });
                    }

                    if (prefresh > 0) {
                        //$(this).addClass('ita-timer');
                        $(this).addClass('ita-activedTimer').everyTime(prefresh + 's', "ita-timer", function (i) {
                            itaGo('ItaCall', '', {
                                bloccaui: false,
                                asyncCall: true,
                                validate: false,
                                event: 'refreshProcess',
                                model: openCallback.model,
                                id: openCallback.id
                            });

                        });
                    }
                },
                change: function (event, ui) {
                    //                    $(this).find('.ita-progress-label').text( $(this).progressbar( "option","value" ) + "/" + $(this).progressbar( "option","max" ));
                    //                    $(this).stopTime('ita-response-timeout');
                    //                    if (responseTimeout>0){
                    //
                    //                        $(this).addClass('ita-activedTimer').everyTime(responseTimeout+'s', "ita-response-timeout", function(i) {
                    //                           $(this).stopTime('ita-response-timeout');
                    //                            var hangProgess = $(this).attr('id') + '-hang';
                    //                            if ($('#'+hangProgess).length == 0) {
                    //                                $('body').append('<div id="'+hangProgess+'" title="Il processo non risponde...."></div>');
                    //                            }
                    //                            $('#'+hangProgess).dialog({
                    //                                bgiframe: true,
                    //                                resizable: false,
                    //                                height: 140,
                    //                                modal: true,
                    //                                close: function(event, ui) {
                    //                                    $('#'+hangProgess).remove();
                    //                                },
                    //                                buttons: {
                    //                                    'Attendi il la ripresa del processo': function() {
                    //                                        $(this).dialog('close');
                    //                                    },
                    //                                    'Chiudi la finestra di controllo': function() {
                    //                                        $(this).dialog('close');
                    //                                    }
                    //                                }
                    //                            });
                    //
                    //                        });
                    //                    }
                },
                complete: function (event, ui) {
                    $(this).find('.ita-progress-label').find('span').eq(0).html(completeLabel);
                }

            });
        }
    });

    container.find('.ita-bullet').each(function () {
        $(this).addClass('ui-state-default ui-corner-all');
    });

    container.find('.ita-area-select').each(function () {
        var zoom = false;
        var disableEvent = false;
        var obj = $(this).metadata();
        if (typeof (obj.zoom) != 'undefined')
            zoom = obj.zoom;
        delete obj.zoom;
        if (typeof (obj.disableEvent) != 'undefined')
            disableEvent = obj.disableEvent;
        delete obj.disableEvent;

        var myModel = $(this).attr('model');
        var myId = $(this).attr('id');

        $(this).imgAreaSelect({
            handles: true,
            autoHide: true,
            onSelectEnd: function (img, selection) {
                if (zoom === true) {
                    var scaleX = img.width / (selection.width || 1);
                    var scaleY = img.height / (selection.height || 1);
                    var scaleNew = scaleX;
                    if (scaleY < scaleX) {
                        scaleNew = scaleY;
                    }
                    var newWidth = Math.round(img.width * scaleNew) + 'px';
                    var newHeight = Math.round(img.height * scaleNew) + 'px';
                    $("#" + myId).css({
                        width: newWidth,
                        height: newHeight,
                        marginLeft: '-' + Math.round(scaleNew * selection.x1) + 'px',
                        marginTop: '-' + Math.round(scaleNew * selection.y1) + 'px'

                    });
                }
                if (disableEvent === false) {
                    itaGo('ItaForm', "", {
                        id: myId,
                        event: 'imgAreaSelect',
                        model: myModel,
                        validate: false,
                        leggiform: 'tutto',
                        x1: selection.x1,
                        x2: selection.x2,
                        y1: selection.y1,
                        y2: selection.y2
                    });
                }
            }
        });
        $(this).imgAreaSelect(obj);
    });

    /*
     container.find('.ita-point-select').each(function() {
     var myModel = $(this).attr('model');
     var myId = $(this).attr('id');
     $('img').click(function(e) {
     var offset = $(this).offset();
     itaGo('ItaForm', "", {
     id: myId,
     event: 'imgPointSelect',
     model: myModel,
     validate: false,
     leggiform: 'tutto',
     x: Math.round(e.clientX - offset.left),
     y: Math.round(e.clientY - offset.top)
     });
     });
     });
     */
    container.find('.ita-list').each(function () {
        var metadata = $(this).metadata();
        if (typeof (metadata.sortable) == 'undefined')
            metadata.sortable = false;
        if (metadata.sortable) {
            delete metadata.sortable;
            var ulOptions = {};
            var key = "";
            for (key in metadata) {
                ulOptions[key] = metadata[key];
            }

            $(this).sortable(ulOptions);
        }
    });

    container.find('.ita-sortable').each(function () {
        var $el = $(this),
            metadata = $el.metadata(),
            options = {};

        if (metadata.sortableHandle) {
            options.handle = metadata.sortableHandle;
        }

        if (metadata.sortableEvents) {
            options.update = function (e, ui) {
                itaGo('ItaForm', $el, {
                    asyncCall: false,
                    bloccaui: true,
                    event: 'sortStop',
                    order: $el.sortable('toArray').toString(),
                    validate: false
                });
            };
        }

        $(this).sortable(options);
    });

    container.find('.ita-box').each(function () {
        var $itaBox = $(this),
            metadata = $itaBox.metadata(),
            idBody = this.id + '_boxBody',
            idHeader = this.id + '_boxHeader';

        var resizable = metadata.resizable || false;

        /*
         * Verifico che non sia gi‡ presente un header
         */
        if ($itaBox.children(':first').is('.ita-header')) {
            return;
        }

        if (!$itaBox.attr('title')) {
            return;
        }

        var $itaBoxHeader = $(protSelector('#' + idHeader)),
            $itaBoxBody = $(protSelector('#' + idBody));

        if (!$itaBoxHeader.length || !$itaBoxBody.length) {
            $itaBox.contents().wrapAll('<div id="' + idBody + '"></div>');
            $itaBox.prepend('<div id="' + idHeader + '"></div>');

            $itaBoxHeader = $(protSelector('#' + idHeader));
            $itaBoxBody = $(protSelector('#' + idBody));
        }

        $itaBoxHeader.addClass('ita-box-header ui-widget-header ui-corner-all').css({padding: '2px', margin: '1px', fontSize: '12px'});
        $itaBoxHeader.append('<div id="' + idHeader + '_title" style="display: inline-block;">' + $itaBox.attr('title') + '</div>');

        $itaBoxBody.addClass('ita-box-body');

        if (metadata.collapse === true) {
            var collapseHeight = $itaBox.prop('style').height;
            var collapseEvents = metadata.collapseEvents || false;

            var $itaBoxHeaderIcons = $('<div class="ita-box-header-icons" style="float: right; margin-top: -2px;"></div>');
            $itaBoxHeader.append($itaBoxHeaderIcons);

            var $collapseIcon = $('<span class="ita-portlet-icon ita-portlet-plus ui-icon ui-icon-minusthick"></span>');
            $itaBoxHeaderIcons.append($collapseIcon);

            $collapseIcon.click(function () {
                $collapseIcon.toggleClass('ui-icon-minusthick').toggleClass('ui-icon-plusthick');
                $itaBoxBody.toggle();
                if ($collapseIcon.is('.ui-icon-minusthick')) {
                    /*
                     * onExpand
                     */
                    $itaBox.css('height', collapseHeight || '');

                    if (resizable) {
                        $itaBox.resizable({
                            disabled: false
                        });
                    }

                    if (collapseEvents) {
                        itaGo('ItaForm', $itaBox, {
                            asyncCall: false,
                            bloccaui: true,
                            event: 'onExpand',
                            validate: false
                        });
                    }

                    resizeTabs();
                } else {
                    /*
                     * onCollapse
                     */
                    collapseHeight = $itaBox.prop('style').height;
                    $itaBox.css('height', '');

                    if (resizable) {
                        $itaBox.resizable({
                            disabled: true
                        }).removeClass('ui-state-disabled');
                    }

                    if (collapseEvents) {
                        itaGo('ItaForm', $itaBox, {
                            asyncCall: false,
                            bloccaui: true,
                            event: 'onCollapse',
                            validate: false
                        });
                    }

                    resizeTabs();
                }
            });

            if (metadata.collapseOnload === true) {
                $collapseIcon.click();
            }

            if (metadata.fullscreen) {
                var $fullscreenToggle = $('<span class="ui-icon ui-icon-fullscreen"></span>');
                $itaBoxHeaderIcons.prepend($fullscreenToggle);

                itaFullscreenActivate($itaBox, $fullscreenToggle, function () {
                    $collapseIcon.addClass('ui-icon-minusthick').removeClass('ui-icon-plusthick').hide();
                    $itaBoxBody.show();

                    $itaBox.css('height', '');
                }, function () {
                    if (collapseHeight) {
                        $collapseIcon.show();

                        $itaBox.css('height', collapseHeight);
                    }
                });
            }
        }

        if (resizable) {
            var resizableEvents = metadata.resizableEvents || false;
            var handles = 'e, s, se';
            var resizeSize;

            if (resizable == 'vertical') {
                handles = 's';
            } else if (resizable == 'horizontal') {
                handles = 'e';
            }

            $itaBox.resizable({
                helper: 'ui-resizable-helper',
                handles: handles,
                start: function (e, ui) {
                    if (resizable == 'vertical') {
                        resizeSize = $itaBox.prop('style').width;
                    } else if (resizable == 'horizontal') {
                        resizeSize = $itaBox.prop('style').height;
                    }
                },
                stop: function (e, ui) {
                    if (resizable == 'vertical') {
                        $itaBox.css('width', resizeSize || '');
                    } else if (resizable == 'horizontal') {
                        $itaBox.css('height', resizeSize || '');
                    }

                    if (resizableEvents) {
                        itaGo('ItaForm', $itaBox, {
                            asyncCall: false,
                            bloccaui: true,
                            event: 'onResize',
                            validate: false,
                            resizeWidth: this.style.width,
                            resizeHeight: this.style.height
                        });
                    }
                }
            });
        }
    });

    container.find('.ita-box-highlight').each(function () {
        $(this).addClass('ui-widget-content ui-corner-all ui-state-highlight');
    });

    container.find('.ita-box-error').each(function () {
        $(this).addClass('ui-widget-content ui-corner-all ui-state-error');
    });

    container.find('.ita-mail-body').each(function () {
        $(this).find('a[href^="http://"]').each(function () {
            $(this).attr({
                target: "_blank",
                title: "Apri Esternamente"
            });
        });
    });

    container.find('.ita-tab').each(function () {
        var metaData = $(this).metadata();
        if (typeof (metaData.eventActivate) == 'undefined')
            metaData.eventActivate = false;
        var id_tab = $(this).attr('id');
        var htm_index = $('<ul></ul>');
        $(this).children('.ita-tabpane').each(function () {
            var tabPaneMetadata = $(this).metadata(),
                tabPaneIcon = tabPaneMetadata.icon ? '<i class="' + tabPaneMetadata.icon + '" style="margin: 0 6px 0 0;"></i>' : '';

            htm_index.append("<li><a href=\"#" + $(this).attr('id') + "\">" + tabPaneIcon + $(this).attr('Title') + "</a></li>");
            $(this).children('.ita-jqGrid').each(function () {
                var pane_wrapper_id = $(this).attr('id') + "_tabwrapper";
                $(this).wrap('<div id="' + pane_wrapper_id + '"></div>');
            });
        });
        $(this).prepend(htm_index);
        $("#" + id_tab).tabs({
            activate: function (event, ui) {

                tinyDeActivate($(ui.newPanel).find('textarea.ita-edit-tinymce').toArray());

                $(ui.newPanel).find('textarea.ita-edit-tinymce').each(function () {
                    tinyActivate($(this).attr('id'));
                });
                $(ui.newPanel).find('.ita-plupload-uploader').each(function () {
                    pluploadActivate($(this).attr('id'));
                });
                $(ui.newPanel).find('.ita-flowchart').each(function () {
                    itaJsPlumbHelper.activate(this.id);
                });
                resizeGrid($(ui.newPanel).attr('id'), true, true);

            },
            beforeActivate: function (event, ui) {

                tinyDeActivate($(ui.oldPanel).find('textarea.ita-edit-tinymce').toArray());

                if (metaData.eventActivate == true) {
                    if (typeof (ui.newPanel) != 'undefined') {
                        itaGo('ItaForm', this, {
                            id: $(ui.newPanel).attr('id'),
                            event: 'onClick',
                            validate: false
                        });
                    }
                }
            }
        });
        $("#" + id_tab).tabs('option', 'active', 0);
    });

    container.find('.ita-accordion').each(function () {
        var metaData = $(this).metadata();
        var id_tab = $(this).attr('id');

        $(this).children('.ita-tabpane').each(function () {
            $(this).before("<h3>" + $(this).attr('Title') + "</h3>");
        });

        if (metaData.activeEvents) {
            this._lastTabIndex = false;

            metaData.activate = function (e, ui) {
                var tabIndex = $(this).accordion('option', 'active');

                itaGo('ItaForm', this, {
                    event: 'onAccordion',
                    tabIndex: tabIndex,
                    lastTabIndex: e.target._lastTabIndex,
                    validate: false
                });

                e.target._lastTabIndex = tabIndex;
            };

            delete metaData['activeEvents'];
        }

        var params = $.extend({
            collapsible: true,
            heightStyle: 'fill'
        }, metaData);

        $("#" + id_tab).accordion(params);
        $("#" + id_tab).accordion('enable');
        $("#" + id_tab).accordion('refresh');
    });

    container.find('.ita-header').each(function () {
        $(this).append("<span class=\"ita-header-content\">" + $(this).attr('title') + "<span>");
    });
    container.find('.ita-span').each(function () {
        $(this).html($(this).attr('value'));
    });
    container.find('.ita-workspace').each(function () {
        $(this).addClass('ui-widget-content ui-corner-all');
    });
    container.find('textarea.ita-edit-multiline').each(function () {
        $(this).addClass('ui-widget-content ui-corner-all');
    });
    container.find('textarea.ita-edit-tinymce').each(function () {
        var id = $(this).attr('id');
        var metadata = $(this).metadata();
        var width = metadata.width !== undefined ? metadata.width : 'auto';
        var fieldWidth = width == '100%' ? '99.4%' : width;
        var wrapperWidth = width.indexOf('%') > -1 ? '100%' : 'auto';
        var height = metadata.height !== undefined ? metadata.height : 'auto';
        //console.log( $("#" + protSelector(elId)).parent('#' + elId + '_field'), elWidth );
        $('#' + protSelector(id) + '_field').css('width', fieldWidth).css('height', height);
        $("#" + protSelector(id)).wrap('<div id="' + id + '_wrapper" class="ita-edit-tinymce-wrapper" style="float: left; width: ' + wrapperWidth + ';" />');
    });
    container.find('select.ita-edit,input.ita-edit,textarea.ita-edit').each(function () {

        var srcField = $(this);
        var metadata = $(this).metadata();
        if (typeof (metadata.serialize) == 'undefined')
            metadata.serialize = true;
        if (typeof (metadata.wrapOptions) != 'undefined') {
            for (var i in metadata.wrapOptions) {
                $('#' + protSelector($(this).attr('id')) + '_field').attr(i, metadata.wrapOptions[i]);
            }
        }
        if (typeof (metadata.filterconfig) == 'undefined')
            metadata.filterconfig = false;

        var $requiredBox = $('<span id="' + this.id + '_lbl_required" style="display: inline-block; color: red; font-weight: bold; width: 6px; font-family: sans-serif;"></span>');
        var $label = $('#' + protSelector($(this).attr('id')) + '_lbl');

        if (srcField.is('.required') || $label.is('.ita-required-char')) {
            $requiredBox.text('*');
        }

        if ($label.length) {
            $label.text($label.text().trim());
            $label.append($requiredBox);
        } else {
            srcField.prepend($requiredBox);
        }

        var leggi;
        var myForm = $(this).itaGetParentForm(); // itaImplode($(this), 'form, div.ita-model'); // @FORM FIXED 16.03.15 | 07.10.15
        (metadata.serialize == true) ? leggi = "tutto" : leggi = "singolo";
        $(this).addClass('ui-widget-content ui-corner-all');

        if (metadata.filterconfig == true && !srcField.is('.ita-edit-lookup, .ita-edit-upload')) {
            itaFieldUtilities.filterConfig(srcField, metadata.filterconfigOptions);
        }

        // STOP PASSWORD AUTOPOPOLATE
        if ($(this).attr('type') === 'password') {
            $(this).attr('autocomplete', 'off');
        }

        //
        //  AUTOCOMPLETE EVENTS
        //
        if (typeof (metadata.autocomplete) != 'undefined') {
            if (metadata.autocomplete.active) {
                var acParm = metadata.autocomplete;
                var minLength = 3;
                var sendEvent = false;

                if (typeof (acParm.minLength) != 'undefined') {
                    minLength = acParm.minLength;
                }
                var delay = 500;
                if (typeof (acParm.delay) != 'undefined') {
                    delay = acParm.delay;
                }
                var waitImg = false;
                if (typeof (acParm.waitImg) != 'undefined') {
                    waitImg = acParm.waitImg;
                }
                var maxH = false;
                if (typeof (acParm.maxHeight) != 'undefined') {
                    maxH = acParm.maxHeight;
                }

                if (typeof (acParm.sendEvent) != 'undefined') {
                    sendEvent = acParm.sendEvent;
                }

                var acWidth = 260;
                if (typeof (metadata.autocomplete.width) != 'undefined') {
                    acWidth = metadata.autocomplete.width;
                }
                var idInput = $(this).attr('id');
                //var myModel = $(itaImplode($(this), 'FORM')).attr('action').substr(1);
                //var myModel = $(itaImplode($(this), 'form, div.ita-model')).attr('id'); // @FORM FIXED 16.03.15 | 07.10.15
                var myModel = $(this).itaGetParentForm().itaGetId();
                var backendModel = $(this).itaGetParentForm().itaGetModelBackend();

                var cache = {};
                var lastXhr;

                $(this).bind('keydown', function (e) {
                    if (e.keyCode == 38 || e.keyCode == 40) {
                        e.preventDefault();
                        e.stopPropagation();
                    }
                });

                $(this).autocomplete({
                    autoFocus: true,
                    search: function (event, ui) {
                        if (waitImg == true) {
                            $('#' + protSelector(idInput)).css('background', "white url('public/css/images/ui-anim_basic_16x16.gif') no-repeat scroll right center");
                        }
                        if (maxH != false) {
                            $('.ui-autocomplete').css('max-height', maxH);
                            $('.ui-autocomplete').css('overflow-y', "auto");
                            $('.ui-autocomplete').css('overflow-x', "hidden");
                        }
                    },
                    open: function (event, ui) {
                        $('#' + protSelector(idInput)).css('background', '');

                        var message = srcField.data('ita-suggest-alert');
                        if (message) {
                            var $ul = srcField.autocomplete("widget");
                            $ul.prepend('<li style="padding: 5px 8px;"><b><span class="ui-icon ui-icon-alert" style="vertical-align: initial; position: relative; margin: -3px 2px 0 -5px;"></span> ' + message + '</b></li>');
                            $ul.children().last().remove();
                            srcField.data('ita-suggest-alert', '');
                        }
                    },
                    source: function (request, response) {
                        var term = request.term;
                        //                                        if ( term in cache ) {
                        //                                            response( cache[ term ] );
                        //                                            return;
                        //                                        }
                        var params = {
                            id: idInput,
                            TOKEN: token,
                            event: 'suggest',
                            model: myModel,
                            limit: 10
                        };

                        if (backendModel) {
                            params['model'] = backendModel;
                            params['nameform'] = myModel;
                        }

                        // Parametro "q" spostato fuori dall'oggetto per utilizzare
                        // la funzione escpace (ISO-8859-1) invece di encodeURI (UTF-8) (internamente in jQuery)
                        var newParams = $.param(params) + '&q=' + escape(term);

                        lastXhr = $.post(urlController, newParams, function (data, status, xhr) {
                            $('#' + protSelector(idInput)).css('background', '');

                            var key = '__MORE__';
                            if (data.substr(data.lastIndexOf('\n'), key.length + 1) === '\n' + key) {
                                var message = data.substr(data.lastIndexOf('\n')).split('|')[1];
                                if (message) {
                                    srcField.data('ita-suggest-alert', message);
                                } else {
                                    data = data.substr(0, data.lastIndexOf('\n'));
                                }
                            }

                            var dataA = $.map(data.split('\n').filter(function (n) {
                                return n;
                            }), function (v) {
                                var cols = v.split('|');
                                var extraCols = null;

                                if (cols.length > 3) {
                                    extraCols = cols.slice(3, cols.length);
                                }

                                return {
                                    label: cols[0],
                                    value: cols[0],
                                    codice: cols[2],
                                    altro: cols[1],
                                    extraCols: extraCols
                                };
                            });
                            //cache[ term ] = dataA;
                            if (xhr == lastXhr) {
                                response(dataA);
                            }
                        });
                    },
                    minLength: minLength,
                    delay: delay,
                    select: function (event, ui) {
                        event.stopPropagation();

                        $(this).addClass('ita-autocomplete-selected');
                        $('#' + protSelector(idInput)).css('background', '');
                        if (ui.item.value) {
                            var suggestCampi = {};
                            suggestCampi[ui.item.altro] = ui.item.codice;

                            $(protSelector("#" + ui.item.altro)).focus();
                            $(protSelector("#" + ui.item.altro)).val(ui.item.codice);
                            if (ui.item.extraCols != null) {
                                for (var idx = 0; idx < ui.item.extraCols.length; idx = idx + 2) {
                                    suggestCampi[ui.item.extraCols[idx]] = ui.item.extraCols[idx + 1];
                                    $(protSelector("#" + ui.item.extraCols[idx])).val(ui.item.extraCols[idx + 1]);
                                    parseDateTimeWithin($(protSelector("#" + ui.item.extraCols[idx])));
                                    itaInputMaskWithin($(protSelector("#" + ui.item.extraCols[idx])));
                                }
                            }

                            if (sendEvent == true) {
                                itaGo('ItaForm', srcField, {
                                    event: 'onSuggest',
                                    suggestLabel: ui.item.label,
                                    suggestCampi: suggestCampi,
                                    validate: false
                                });
                            }
                        }
                    }
                });
            }
        }

        // NUMBER FORMATTER
        if (metadata.formatter) {
            if (metadata.formatter == 'number') {
                var regExpCurrency, formatterOptions = itaCurrencyFormatterOptions(srcField.attr('id'));

                /*
                 * Controllo il parametro precision
                 */

                if (parseInt(formatterOptions.precision) == 0) {
                    /*
                     * Se precision Ë 0, allora la text sar‡ composta da soli numeri
                     */

                    regExpCurrency = /^([+-])?(\d*)/;
                } else {
                    /*
                     * Se abbiamo 1 o pi˘ cifre decimali, allora consento anche
                     * la presenza di un punto o virgola ("(\.|,)?") e di X cifre
                     * decimali ("(\d{0,X})?")
                     */

                    regExpCurrency = new RegExp("^([+-])?(?:(\\d*)(\\.|,)(\\d{0," + Math.abs(parseInt(formatterOptions.precision)) + "})|(\\d*))");
                }

                $(this).css('text-align', 'right').keypress(function (e) {
                    var resultString = e.target.value.substr(0, e.target.selectionStart) + e.key + e.target.value.substr(e.target.selectionEnd);

                    if (!e.altKey && !e.ctrlKey && e.key.length === 1 && regExpCurrency.exec(resultString)[0] !== resultString) {
                        e.preventDefault();
                    }
                }).blur(function (e) {
                    itaCurrencyFormatterDisplayOn(srcField);
                }).focus(function (e) {
                    itaCurrencyFormatterDisplayOff(srcField);
                });

                itaCurrencyFormatterDisplayOn(srcField);
            }
        }

        if (metadata.tooltip) {
            itaFieldUtilities.inputTooltip(this.id, metadata.tooltip);
        }

        //
        //  CUSTOM BLUR KEYBOARD EVENTS CR AND TAB
        //
        $(this).bind('ita-blur', function (event, mode) {
            if (mode == 'next') {
                var destF = moveNext(this);
            } else {
                var destF = movePrev(this);
            }
            var prevTab = $(this).parents(".ita-tab");
            if (typeof (prevTab) != 'undefined') {
                var prevPane = $(this).parents(".ita-tabpane");
            }
            var curTab = $(destF).parents(".ita-tab");
            if (typeof (prevTab) != 'undefined') {
                var curPane = $(destF).parents(".ita-tabpane");
            }
            if (typeof (curTab) != 'undefined' && typeof (curPane) != 'undefined') {
                if (typeof (prevTab) == 'undefined' || $(prevTab).attr('id') + $(prevPane).attr('id') != $(curTab).attr('id') + $(curPane).attr('id')) {
                    $(curTab).tabs('option', 'active', "#" + $(curPane).attr('id'));
                }
            }
            $(this).blur();
            $(destF).focus();
            if ($(myForm).hasClass('ita-select-field_content')) {
                $(destF).select();
            }
            if ($(this).hasClass('ita-edit-onblur')) {
                var obj = $("#" + protSelector($(this).attr('id')));

                if ($(this).hasClass('ita-edit-cell')) {
                    var gridObj = $(obj).parents('.ita-dataSheet:first');
                    var gridRowid = $(obj).parents('tr:first').attr('id');
                    var arrTmp = $(obj).attr('id').split('_');
                    var gridCellname = arrTmp[1];
                    itaGo('ItaForm', gridObj, {
                        event: 'onBlurGridCell',
                        rowid: gridRowid,
                        cellname: gridCellname,
                        validate: false,
                        leggiform: leggi
                    });
                } else {
                    itaGo('ItaForm', obj, {
                        event: 'onBlur',
                        validate: false,
                        leggiform: leggi
                    });
                }


            }
        });
        $(this).focus(function (e) {
            //if ( e.relatedTarget && $(e.relatedTarget).is('input') ) $(e.relatedTarget).focus();
            dialogLastFocus["#" + $(this).parents('.ui-dialog-content:first').attr('id')] = $(this).attr('id');
            if ($(myForm).hasClass('ita-select-field_content')) {
                $(this).select();
            }
        });
        $(this).keyup(function (event) {
            switch (event.keyCode) {
                case 13:
                    if ($(this).is('textarea.ita-edit-newline')) {
                        break;
                    }
                    event.preventDefault();
                    break;
                case 9:
                    event.preventDefault();
                    break;
                case 27:
                    //console.log(event);
                    if ($(this).hasClass('ita-edit-cell')) {
                        //alert("annullo");
                        cancelInlineEdit(this, null, true, true);
                    }
                    break;
                default:
                    if ($(this).hasClass('ita-edit-uppercase')) {
                        var ita_field_pos = ita_get_pointer_index(this);
                        var uc_string = ita_ucwords($(this).val(), true);
                        $(this).val(uc_string);
                    } else if ($(this).hasClass('ita-edit-lowercase')) {
                        var ita_field_pos = ita_get_pointer_index(this);
                        var lc_string = ita_lcwords($(this).val(), true);
                        $(this).val(lc_string);
                    } else if ($(this).hasClass('ita-edit-capitalize')) {
                        var ita_field_pos = ita_get_pointer_index(this);
                        var uf_string = ita_ucfirst($(this).val(), true);
                        $(this).val(uf_string);
                    }

                    if (ita_field_pos) {
                        ita_set_pointer_index(this, ita_field_pos);
                    }

                    if (metadata.autoblur && this.maxLength && this.maxLength > 0 && this._last_length && this.value.length != this._last_length && this.value.length >= this.maxLength) {
                        $(this).trigger('ita-blur', 'next');
                    }
                    break;
            }

            delete this._last_length;
        });
        $(this).keypress(function (event) {
            switch (event.keyCode) {
                case 13:
                    if ($(this).is('textarea.ita-edit-newline')) {
                        break;
                    }
                    event.preventDefault();
                    break;
                case 9:
                    event.preventDefault();
                    break;
            }
        });
        $(this).keydown(function (event) {
            if (!this._last_length) {
                this._last_length = this.value.length;
            }

            switch (event.keyCode) {
                case 13:
                    if ($(this).is('textarea.ita-edit-newline')) {
                        break;
                    }
                    event.preventDefault();
                    if ($(this).hasClass('ita-autocomplete-selected')) {
                        $(this).removeClass('ita-autocomplete-selected');
                    } else {
                        $(this).trigger('ita-blur', 'next');
                    }
                    break;
                case 9:
                    event.preventDefault();
                    if (event.shiftKey == 1) {
                        $(this).trigger("ita-blur", "prev");
                    } else {
                        $(this).trigger("ita-blur", "next");
                    }
                    break;
            }
        });
    });
    container.find('.ita-select').each(function () {
        $(this).addClass('ui-widget-content ui-corner-all');
    });
    container.find('.ita-edit-spinner').each(function () {
        $(this).spinner();
    });
    container.find('.ita-edit-lookup').each(function () {
        var metaData = $(this).metadata();
        var showIcon = null;
        var iconClass = 'ui-icon-search';
        if (typeof (metaData.lookupIcon) === 'undefined') {
            showIcon = true;
            //metaData.lookupIcon = true;
        } else if (metaData.lookupIcon === false) {
            showIcon = false;
        } else if (metaData.lookupIcon === true) {
            showIcon = true;
        } else {
            showIcon = true;
            iconClass = metaData.lookupIcon;
        }
        var obj = this;

        var idlookup = $(this).attr('id') + "_butt";
        var idlabel = $(this).attr('id') + "_lbl";
        var iconVAlign = $(protSelector("#" + idlabel)).hasClass('top') ? 'bottom' : 'top';

        var id_lk_table = $(this).attr('id') + "_lk_table";
        var id_lk_cell1 = $(this).attr('id') + "_lk_cell1";
        var id_lk_cell2 = $(this).attr('id') + "_lk_cell2";

        $(this).addClass('ui-widget-content ui-corner-all');

        $(protSelector("#" + idlabel)).add(this).wrapAll('<div id="' + id_lk_cell1 + '" style="width:100%;display:table-cell;"></div>');

        $(protSelector("#" + id_lk_cell1)).after('<div id="' + id_lk_cell2 + '" style="display: table-cell; vertical-align: ' + iconVAlign + ';" ><div id="' + idlookup + '" class=" ita-edit-lookup-trigger ita-icon-right ita-element-animate ui-state-default ui-widget-content ui-corner-all" style="float: none; vertical-align: ' + iconVAlign + ';"><span class="ui-icon ' + iconClass + '"></span></div></div>');
        $(protSelector("#" + id_lk_cell1) + "," + protSelector("#" + id_lk_cell2)).wrapAll('<div id="' + id_lk_table + '" style="display:table;"></div>');

        $(protSelector('#' + id_lk_cell2)).css('width', $(protSelector('#' + idlookup)).css('width'));

        // CHROME FIX
        $(protSelector('#' + $(this).attr('id') + '_field')).css('display', 'inline-table');


        if (showIcon == false) {
            $(protSelector('#' + idlookup)).css('display', 'none');
        }

        if (typeof (metaData.filterconfig) == 'undefined')
            metaData.filterconfig = false;

        if (metaData.filterconfig == true) {
            itaFieldUtilities.filterConfig($(obj), metaData.filterconfigOptions);
        }

        $("#" + protSelector(idlookup)).click(function () {
//            if ($(obj).hasClass('ita-readonly')) {
//                return false;
//            }

            if ($(obj).hasClass('ita-edit-cell')) {
                var gridObj = $(this).parents('.ita-dataSheet:first');
                var gridRowid = $(this).parents('tr:first').attr('id');
                var arrTmp = $(obj).attr('id').split('_');
                var gridCellname = arrTmp[1];

                itaGo('ItaForm', gridObj, {
                    event: 'onClickGridCell',
                    rowid: gridRowid,
                    cellname: gridCellname,
                    validate: false
                });
            } else {
                itaGo('ItaForm', this, {
                    event: 'onClick',
                    validate: false
                });
            }
        });
        $(this).jkey('f1', function () {
            $("#" + protSelector(idlookup)).click();
        });
    });
    container.find('.ita-edit-upload').each(function () {
        var metaData = $(this).metadata();
        var idupload = $(this).attr('id') + "_upld";
        var iduploader = idupload + '_uploader';
        $(this).addClass('ui-widget-content ui-corner-all');

        $(this).after('<div id="' + iduploader + '" class="ita-plupload-uploader" style="float:right;display:inline-block;"><div id="' + idupload + '" class="ita-plupload-browser ita-icon-right ita-element-animate ui-state-default ui-widget-content ui-corner-all"><span class="ui-icon ui-icon-disk"/></div></div>');

        if (typeof (metaData.filterconfig) == 'undefined')
            metaData.filterconfig = false;

        if (metaData.filterconfig == true) {
            itaFieldUtilities.filterConfig($(this), metaData.filterconfigOptions);
        }

        var upload_options = {
            runtimes: 'gears,html5,browserplus',
            browse_button: idupload,
            container: iduploader,
            url: window.location.href.replace(/[^/]*$/, '') + 'plupload.php',
            multipart_params: {
                TOKEN: token
            }
        };

        if (metaData.uploadOptions) {
            for (var key in metaData.uploadOptions) {
                upload_options[key] = metaData.uploadOptions[key];
            }
        }

        /*
         * Modifica opzioni personalizzate per plUpload
         * Carlo - 20.06.2016
         */

        itaEngine.plUploaders[iduploader] = new plupload.Uploader(upload_options);
//        itaEngine.plUploaders[iduploader] = new plupload.Uploader({
//            runtimes: 'gears,html5,browserplus',
//            browse_button: idupload,
//            container: iduploader,
//            url: 'plupload.php',
//            multipart_params: {
//                token: token
//            }
//        });
    });
    container.find('.ita-edit-onchange,.ita-edit-cell').each(function () {
        var obj = $("#" + protSelector($(this).attr('id')));
        $(this).change(function (e) {

            if ($(this).hasClass('ita-edit-cell')) {
                $(this).parents('tr:first').addClass('ita-edit-row-changed');
            }
            if ($(this).hasClass('ita-edit-onchange')) {
                if ($(this).hasClass('ita-edit-cell')) {
                    var gridObj = $(this).parents('.ita-dataSheet:first');
                    var gridRowid = $(this).parents('tr:first').attr('id');
                    var arrTmp = $(obj).attr('id').split('_');
                    var gridCellname = arrTmp[1];
                    itaGo('ItaForm', gridObj, {
                        event: 'onChangeGridCell',
                        rowid: gridRowid,
                        cellname: gridCellname,
                        validate: false
                    });
                } else {
                    itaGo('ItaForm', obj, {
                        event: 'onChange',
                        validate: false,
                        asyncCall: false
                    });
                }
            }
        });

    });
    container.find('input.ita-decode').each(function () {
        $(this).attr('disabled', 'disabled').addClass('ui-widget-content ui-corner-all');
    });
    container.find('select.ita-readonly,textarea.ita-readonly,input.ita-readonly').each(function () {
        var metadata = $(this).metadata();
        if (typeof (metadata.wrapOptions) != 'undefined') {
            for (var i in metadata.wrapOptions) {
                $('#' + protSelector($(this).attr('id')) + '_field').attr(i, metadata.wrapOptions[i]);
            }
        }
        $(this).attr('readonly', 'readonly').addClass('ui-widget-content ui-corner-all').focus(function () {
            var destF = moveNext(this);
            $(destF).focus();
        });
        ;
    });

    container.find('.ita-colorpicker').each(function () {
        var metadata = $(this).metadata();

        if (metadata.type == 'divColor') {
            var that = this,
                baseColor = $(that).css('background-color'),
                colorPickerOptions = {
                    colorFormat: '#HEX',
                    color: $(that).css('background-color'),
                    closeOnOutside: true,
                    inline: false,
                    okOnEnter: true,
                    select: function (e, data) {
                        $(e.target).css('background-color', data.formatted);
                    },
                    close: function (e, data) {
                        if (data.colorPicker.confirmSelect) {
                            data.colorPicker.confirmSelect = null;

                            itaGo('ItaForm', that, {
                                event: 'returnColorpicker',
                                validate: false,
                                colorPicked: data.formatted
                            });
                        } else {
                            $(e.target).css('background-color', baseColor);
                        }
                    },
                    ok: function (e, data) {
                        data.colorPicker.confirmSelect = true;
                    }
                };

            if (metadata.swatches == true) {
                colorPickerOptions.parts = ['preview', 'swatches', 'footer'];
                colorPickerOptions.layout = {
                    preview: [0, 0, 1, 1],
                    swatches: [0, 1, 1, 1],
                    footer: [0, 2, 1, 1]
                };

                colorPickerOptions.swatches = 'custom_array';
                colorPickerOptions.showNoneButton = true;
                colorPickerOptions.revert = true;
                colorPickerOptions.select = null;
            }

            $(this).colorpicker(colorPickerOptions);
        } else {
            var myColor = 'rgb(' + $(this).val() + ')';
            $(this).colorpicker({
                colorFormat: 'rd,gd,bd',
                closeOnOutside: true,
                //modal:true,
                inline: false,
                okOnEnter: true
            });
        }
    });

    container.find('input.ita-datepicker').each(function () {
        var metaData = $(this).metadata();
        if (typeof (metaData.showOn) == 'undefined')
            metaData.showOn = 'button';
        var dtp = $(this);
        dtp.jkey('f1', function () {
//            if (dtp.hasClass('ita-readonly')) {
//                return false;
//            }

            dtp.datepicker("show");
        });
        dtp.datepicker({
            changeYear: true,
            changeMonth: true,
            dateFormat: 'dd/mm/yy',
            showOn: metaData.showOn,
            showAnim: 'slideDown',
            onSelect: function (d, i) {
                dtp.focus();

                if (d !== i.lastVal) {
                    dtp.change();
                }
            },
            yearRange: "-100:+10"
        }).next(".ui-datepicker-trigger").addClass("ita-icon-right ui-widget-content ui-state-default ui-corner-all").attr('id', dtp.attr('id') + '_datepickertrigger').html('<span class="ui-icon ui-icon-calculator"></span>');

//		if ( $(this).attr('readonly') !== '' && $(this).attr('readonly') !== 'false' && $(this).attr('readonly') !== '0' ) {
//			$datepicker_button = dtp.next('#' + dtp.attr('id') + '_datepickertrigger');
//			$datepicker_button.css('display', 'none');
//		}
    });
    container.find('input.ita-date').each(function () {
        //$(this).mask("99/99/9999");
    });
    container.find('input.ita-time').each(function () {
        //                        $(this).mask("99:99");
    });

    container.find('input.ita-datepicker, input.ita-date').each(function () {
        var metadata = $(this).metadata();

        if (metadata.dateAutocomplete === true) {
            $(this).on('blur', function (e) {
                /*
                 * Se c'Ë un input (indexOf('_') != 0) e
                 * se l'anno non Ë impostato (=== '____')
                 */
                if (this.value.indexOf('_') > 0 && this.value.substr(6, 4) === '____') {
                    var today = new Date();
                    var d = parseInt(this.value.substr(0, 2));
                    var m = parseInt(this.value.substr(3, 2));

                    if (isNaN(d) || d === 0 || (!isNaN(m) && m === 0)) {
                        return;
                    }

                    if (isNaN(m)) {
                        m = today.getMonth() + 1;
                    }

                    var date = ('0' + d).slice(-2) + '/' + ('0' + m).slice(-2) + '/' + today.getFullYear();

                    this.value = date;

                    /*
                     * Simulo l'inserimento
                     */
                    $(this).trigger('keyup');
                }
            });
        }
    });

    container.find('a.ita-hyperlink').each(function () {
        var metaData = $(this).metadata();
        if (typeof (metaData.id) == 'undefined')
            metaData.id = $(this).attr('id');
        if (typeof (metaData.event) == 'undefined')
            metaData.event = "onClick";
        if (typeof (metaData.request) == 'undefined')
            metaData.request = 'ItaForm';
        if (typeof (metaData.noObj) == 'undefined')
            metaData.noObj = false;
        $(this).click(function () {
            if (metaData.extraCodeBefore) {
                eval(metaData.extraCodeBefore);
            }
            var obj;
            if (metaData.noObj) {
                obj = '';
            } else {
                obj = this;
            }

            var data = {
                id: metaData.id,
                model: metaData.model,
                event: metaData.event,
                validate: false
            };

            if (metaData.extraData) {
                data = $.extend(data, metaData.extraData);
            }

            itaGo(metaData.request, obj, data);

            if (metaData.extraCode) {
                eval(metaData.extraCode);
            }
            return false;
        });
        //return;
    });
    container.find('.ita-button, .ita-button-validate, .ita-button-client').each(function () {
        $(this).addClass('ui-corner-all');
        //var myId=$(this).attr('id');
        var metaData = $(this).metadata();
        if (typeof (metaData.id) == 'undefined')
            metaData.id = $(this).attr('id');
        if (typeof (metaData.fgMenu) == 'undefined')
            metaData.fgMenu = false;
        if (typeof (metaData.upload) == 'undefined')
            metaData.upload = false;
        if (typeof (metaData.event) == 'undefined')
            metaData.event = "onClick";
        if (typeof (metaData.request) == 'undefined')
            metaData.request = 'ItaForm';
        if (typeof (metaData.noObj) == 'undefined')
            metaData.noObj = false;
        var maxH = 0;

        var $parentButtonBar = $(this).closest('.ita-buttonbaractived').length ? $(this).closest('.ita-buttonbaractived') : false;

        if ($parentButtonBar && $(this).closest('.ita-buttonbar-small, .ita-buttonbar-small-center').length) {
            // Da commentare se si abilita la condizione ($(this).closest('.ita-buttonbaractived').length) @line 5835
            metaData.fitWidth = true;

            metaData.template = 'small';
        }

        if ($parentButtonBar && $(this).closest('.ita-buttonbar-medium, .ita-buttonbar-medium-center').length) {
            // Da commentare se si abilita la condizione ($(this).closest('.ita-buttonbaractived').length) @line 5835
            metaData.fitWidth = true;

            metaData.template = 'medium';
        }

        if ($parentButtonBar && $(this).closest('.ita-buttonbar-large, .ita-buttonbar-large-center').length) {
            // Da commentare se si abilita la condizione ($(this).closest('.ita-buttonbaractived').length) @line 5835
            metaData.fitWidth = true;

            metaData.template = 'large';
        }

        if ($parentButtonBar && $(this).closest('.ita-buttonbar-small-center, .ita-buttonbar-medium-center, .ita-buttonbar-large-center').length) {
            if (metaData.iconLeft) {
                metaData.iconCenter = metaData.iconLeft;
                metaData.iconLeft = null;
            } else if (metaData.iconRight) {
                metaData.iconCenter = metaData.iconRight;
                metaData.iconRight = null;
            }
        }

        if ((typeof (metaData.fitWidth) != 'undefined') && metaData.fitWidth == true) {
            $(this).css('width', '100%').css('margin', '2px 0');
            $(this).find('+ br.ita-br').remove();
        }

        if (typeof (metaData.iconLeft) != 'undefined') {
            $('<div id="' + $(this).attr('id') + '_icon_left" class="ita-button-element ita-button-icon-left ita-icon ' + metaData.iconLeft + '" style ></div>')
                .appendTo("#" + $(this).attr('id'));
        }

        if (typeof ($(this).attr('value')) != 'undefined') {
            $('<div class="ita-button-element ita-button-text"><div id="' + $(this).attr('id') + '_lbl" style="height: 100%;" class="ita-button-text-content"></div></div>')
                .appendTo("#" + $(this).attr('id'));

            $('#' + $(this).attr('id') + '_lbl').html($(this).attr('value'));
            $(this).removeAttr('value');
        } else {
            if (!this.style.padding) {
                this.style.padding = '0';
            }
        }

        if (typeof (metaData.iconRight) != 'undefined') {
            $('<div id="' + $(this).attr('id') + '_icon_right" class="ita-button-element ita-button-icon-right ita-icon ' + metaData.iconRight + '"></div>')
                .appendTo("#" + $(this).attr('id'));
        } else if (typeof (metaData.iconCenter) != 'undefined') {
            $('<div id="' + this.id + '_icon_center" style="margin: 0 auto;" class="ita-button-element ita-button-icon-center ita-icon ' + metaData.iconCenter + '"></div>').prependTo(this);
            $('#' + this.id + '_lbl').css('width', '100%').css('padding', '0')
                .parent().css('width', '100%').css('margin', '0').css('height', 'auto');
        }

        if (typeof (metaData.template) != 'undefined') {
            switch (metaData.template) {
                case 'small':
                    $(this).css('height', '32px');
                    break;

                case 'medium':
                    $(this).css('height', '48px');
                    break;

                case 'large':
                    $(this).css('height', '64px');
                    break;
            }
        }

        $(this).find('.ita-button-element').each(function () {
            if ($(this).height() > maxH) {
                maxH = $(this).height();
            }
        });
        if (typeof (metaData.shortCut) != 'undefined') {
            var bottone = $(this).attr('id');
            var idWrapper = $(this).parents('.ui-dialog-content:first').attr('id');
            dialogShortCutMap["#" + idWrapper][metaData.shortCut] = bottone;
            $('#' + idWrapper).jkey(metaData.shortCut, function (key) {

                if ($('.ita-block-events').length) {
                    return false;
                }
                var butnTarget;
                butnTarget = "#" + dialogShortCutMap['#' + idWrapper][key];
                if ($(protSelector(butnTarget)).is(":visible") && $(protSelector(butnTarget)).is(":enabled")) {
//                    if ($(':focus').hasClass('ita-edit-onchange')) {
//                        return false;
//                    }

                    $(protSelector(butnTarget)).focus();
                    $(protSelector(butnTarget)).click();
                }
            });
            bottone = null;
        }

        if (!metaData.iconCenter) {
            if (maxH > 0) {
                var centro = (maxH - $(this).find('.ita-button-text').height()) / 2;
                //                            $(this).find('.ita-button-element').css({
                //                                height:maxH
                //                            });

                $(this).find('.ita-button-element.ita-button-text').css({
                    height: '100%'
                });
            }
        }

        if (metaData.upload == true) {
            var idupload = $(this).attr('id');
            var iduploader = idupload + '_uploader';

            $(this).wrap('<div id="' + iduploader + '" class="ita-plupload-uploader" style="display:inline-block;"></div>');
//            $(this).wrap('<div id="' + iduploader + '" class="ita-plupload-uploader"></div>');

            /*
             * Modifica opzioni personalizzate per plUpload
             * Carlo - 20.06.2016
             */

            var upload_options = {
                runtimes: 'html5',
                browse_button: idupload,
                container: iduploader,
                url: window.location.href.replace(/[^/]*$/, '') + 'plupload.php',
                multipart_params: {
                    TOKEN: token
                }
            };

            if (metaData.uploadOptions) {
                for (var key in metaData.uploadOptions) {
                    upload_options[key] = metaData.uploadOptions[key];
                }
            }

            itaEngine.plUploaders[iduploader] = new plupload.Uploader(upload_options);

//            itaEngine.plUploaders[iduploader] = new plupload.Uploader({
//                runtimes: 'html5',
//                browse_button: idupload,
//                container: iduploader,
//                url: 'plupload.php',
//                multipart_params: {
//                    token: token
//                }
//            });
            return;
        }

        if ($(this).hasClass('ita-popup-menu')) {
            var id = $(this).attr('id');

            $('#' + id + '_lbl').parent().remove();

            var $popupcontainer = $('<div id="' + id + '_popupcontent" class="ui-menu-itacontainer ui-widget ui-widget-content ui-corner-all" style="position: absolute;"></div>').appendTo('body');
            var $menu;

            var itaCloseMenu = function () {
                var closeId = arguments[0] || id;
                $('#' + closeId).removeClass('ui-state-active');
                $('#' + closeId + '_popupcontent').find('.ui-state-active').removeClass('ui-state-active');
                $('#' + closeId + '_popupcontent').hide().css('min-width', '0px').find('ul').hide();
            };

            var parseSubPopupMenu = function ($sub, $a) {
                // Gestione scroll
                $sub.removeClass('scrollable').css('height', 'initial').find('li').show().parent().find('.ui-menu-scroll-up, .ui-menu-scroll-down').remove();

                if ($sub.height() > window.innerHeight - 50) {
                    $sub.css('width', $sub.width() + 'px');
                    var itemVisibili = Math.floor(window.innerHeight / 30); // Calcolo approssimativo
                    $sub.children('li').hide().slice(0, itemVisibili).show();
                    $sub.addClass('scrollable').append('<div class="ui-menu-scroll-up"><span class="ui-icon ui-icon-triangle-1-n"></span></div><div class="ui-menu-scroll-down"><span class="ui-icon ui-icon-triangle-1-s"></span></div>');

                    $sub.css('height', $sub.height() + 'px');
                    var $li = $sub.children('li');

                    var menuScrollFuncUp = function () {
                        if ($li.first().is(':visible')) {
                            return false;
                        }
                        $li.filter(':visible:last').hide();
                        $li.filter(':visible:first').prev().show();
                    };

                    var menuScrollFuncDown = function () {
                        if ($li.last().is(':visible')) {
                            return false;
                        }
                        $li.filter(':visible:first').hide();
                        $li.filter(':visible:last').next().show();
                    };

                    var menuScrollSpeed = 180;

                    $sub.children('.ui-menu-scroll-up, .ui-menu-scroll-down').on('mouseenter', function (e) {
                        if (window.menuScrollTimer) {
                            return false;
                        }

                        if ($(this).hasClass('ui-menu-scroll-up')) {
                            window.menuScrollTimer = window.setInterval(menuScrollFuncUp, menuScrollSpeed);
                        } else {
                            window.menuScrollTimer = window.setInterval(menuScrollFuncDown, menuScrollSpeed);
                        }
                    }).on('mouseleave', function (e) {
                        clearInterval(window.menuScrollTimer);
                        window.menuScrollTimer = null;
                    });
                }

                // Seleziono il menu (.next()), lo mostro per poter effettuare il posizionamento, lo posiziono e lo rimostro
                // con animazione
                $sub.show().position($.extend({of: $a, collision: 'flipfit'}, $menu.menu('option', 'position'))).css('left', '+=2').hide().itaOpenMenu();
            };

            var parsePopupMenu = function (res) {
                $popupcontainer.html(res);

                $menu = $popupcontainer.children('ul').menu({
                    icons: {submenu: "ui-icon-triangle-1-e"}
                }).unbind('mouseenter mouseleave click keydown focus');

                $popupcontainer.hide();

                $menu.hide();

                $.fn.itaOpenMenu = function () {
                    return this.show('blind', 150);
                };

                $menu.on('click', function (e) {
                    e.preventDefault();

                    var $target = $(e.target);
                    var $a = $target.is('a') ? $target : ($target.parent().is('a') ? $target.parent() : false);
                    var $top = ($target.is('ul') ? $target.parent().children('ul') : $target.closest('ul').parent().children('ul'));
                    var $sub = $a ? $a.next('ul') : false;

                    // Rimuovo gli active in questo e nei successivi livelli
                    $top.find('.ui-state-active').removeClass('ui-state-active');

                    if ($a && $a.attr('href').indexOf('?') > -1) {
                        var blocca = $a.get(0).id.indexOf('&') > 0 ? false : true;

                        // Lancio il programma
                        itaGo('ItaClick', $a.get(0), {
                            event: 'onClick',
                            bloccaui: blocca,
                            asyncCall: true,
                            formato: 'xml'
                        });

                        itaCloseMenu();
                        return true;
                    }

                    if ($a && $sub.is(':hidden')) {
                        $a.addClass('ui-state-active');
                        // Nascondo i menu che non rigurdano l'elemento
                        $menu.find('ul').not($a.parents('ul')).hide();

                        // AJAX
                        if ($sub.children().length === 0) {
                            var classes = $a.find('span').attr('class');
                            $a.find('span').removeClass().addClass('loading');

                            $.ajax({
                                type: 'POST',
                                url: urlController,
                                data: {
                                    id: '',
                                    event: 'onClick',
                                    model: metaData.model,
                                    menu: $a.attr('href'),
                                    TOKEN: token
                                },
                                success: function (res) {
                                    $a.find('span').removeClass().addClass(classes);
                                    $sub.append(res);
                                    $menu.menu('refresh');
                                    $menu.find('.ui-menu-scroll-up, .ui-menu-scroll-down').removeClass('ui-widget-content ui-menu-divider');
                                    parseSubPopupMenu($sub, $a);
                                },
                                dataType: "html"
                            });
                        } else {
                            parseSubPopupMenu($sub, $a);
                        }
                    } else {
                        // Click a vuoto, nascondo i submenu in questo livello
                        $top.find('ul').hide();
                    }
                }).on('mouseover', function (e) {
                    var $target = $(e.target);
                    ($target.is('a') ? $target : ($target.parent().is('a') ? $target.parent() : $())).addClass('ui-state-focus');
                }).on('mouseout', function (e) {
                    $('.ui-state-focus').removeClass('ui-state-focus');
                });
            };

            $.ajax({
                type: 'POST',
                url: urlController,
                data: {
                    id: metaData.id,
                    event: 'openButton',
                    model: metaData.model,
                    rootMenu: metaData.rootMenu,
                    TOKEN: token
                },
                success: parsePopupMenu,
                dataType: 'html'
            });

            $(this).unbind('click').on('click', function (e) {
                var that = this;

                if ($popupcontainer.is(':visible')) {
                    itaCloseMenu();
                    return false;
                }

                $('.ita-popup-menu.ui-state-active').not(this).each(function () {
                    itaCloseMenu(this.id);
                });

                $(this).addClass('ui-state-active');

                $menu.show();

                $popupcontainer.show().position({
                    my: "left top",
                    at: "left bottom",
                    of: that
                }).hide().itaOpenMenu();

                $popupcontainer.css('min-width', ($popupcontainer.width() + 1) + 'px');

                return false;
            });

            if (metaData.popupAutoClose) {
                $popupcontainer.on('mouseleave', function (e) {
                    itaCloseMenu();
                });
            }

//            $(document).on("click", function (e) {
//                if ($(e.target).is(':hidden')) {
//                    return true;
//                }
//
//                if ($(e.target).closest('#' + $popupcontainer.attr('id')).length < 1) {
//                    $(document).unbind('click');
//                    itaCloseMenu();
//                }
//            });

            return;
        }


        if (metaData.fgMenu == true) {
            var id = $(this).attr('id');
            var contentId = id + "_content";

            var params = {
                id: metaData.id,
                TOKEN: token,
                event: 'openButton',
                model: metaData.model,
                rootMenu: metaData.rootMenu
            };
            var m;
            $.ajax({
                async: false,
                type: 'POST',
                url: urlController,
                data: params,
                success: function (resp) {
                    m = resp;
                },
                dataType: "html"
            });
            $(this).fgmenu({
                content: m,
                flyOut: metaData.flyOut,
                id: contentId,
                model: metaData.model,
                chooseItem: function (item) {
                    var id = $.url($(item).attr('href').substr(1)).param('prog');
                    var label = $(item).text().split('.', 2);
                    label = label[1];
                    //openTab(id,label,item,1);
                    itaGo('ItaClick', item, {
                        event: 'onClick',
                        bloccaui: false,
                        asyncCall: true,
                        formato: 'xml'
                    });
                }
            });
            return;
        }

        if ($(this).hasClass('ita-button-validate')) {
            $(this).click(function () {
                var obj;
                if (metaData.noObj) {
                    obj = '';
                } else {
                    obj = this;
                }
                itaGo(metaData.request, obj, {
                    id: metaData.id,
                    model: metaData.model,
                    event: metaData.event,
                    validate: true
                });
                return false;
            });
            return;
        }
        if ($(this).hasClass('ita-button')) {
            $(this).click(function () {
                if (metaData.extraCodeBefore) {
                    eval(metaData.extraCodeBefore);
                }
                var obj;
                if (metaData.noObj) {
                    obj = '';
                } else {
                    obj = this;
                }
                var data = {
                    id: metaData.id,
                    model: metaData.model,
                    event: metaData.event,
                    validate: false
                };
                if (metaData.extraData) {
                    data = $.extend(data, metaData.extraData);
                }
                itaGo(metaData.request, obj, data);
                if (metaData.extraCode) {
                    eval(metaData.extraCode);
                }
                return false;
            });
            return;
        }
    });

    container.find('.ita-slider').each(function () {
        var metaData = $(this).metadata();
        if (typeof (metaData.range) == 'undefined')
            metaData.range = false;
        $("#" + $(this).attr('id')).slider({
            range: metaData.range,
            stop: function (event, ui) {
                alert(" " + ui.value);
            }
        });
        if (typeof (metaData.min) != 'undefined') {
            $("#" + $(this).attr('id')).slider("option", "min", metaData.min);
        }
        if (typeof (metaData.max) != 'undefined') {
            $("#" + $(this).attr('id')).slider("option", "max", metaData.max);
        }
        if (typeof (metaData.value) != 'undefined') {
            $("#" + $(this).attr('id')).slider("option", "value", metaData.value);
        }

        if (typeof (metaData.step) != 'undefined') {
            $("#" + $(this).attr('id')).slider("option", 'step', metaData.step);
        }

        if (typeof (metaData.values) != 'undefined') {
            $("#" + $(this).attr('id')).slider("option", 'values', metaData.values);
        }

    });

    container.find('.ita-jqGrid').each(function () {
        /*
         * Controllo se la griglia non Ë "detached"
         * (nel caso di subgrid)
         */
        if (!$.contains(document, this)) {
            return true;
        }

        itaJQGrid($(this).attr('id'));
    });

    container.find('.ita-calendar').each(function () {
        var metadata = $(this).metadata();
        var formId = $(container).attr('id').split('_')[0];
        var that = $(this);
        var id = that.attr('id');

        if (!that.hasClass('fc')) {
            that.parent().css('padding-bottom', '15px');
            calendarParams[id] = {
                view: null,
                start: $.fullCalendar.moment().stripTime().stripZone(),
                end: null,
                init: false
            };
            calendarParams[id]['sources'] = {};
            calendarParams[id]['sourcesSele'] = {};

            that.css('width', '98%').css('margin', '10px auto 0').fullCalendar({
                theme: true,
                // params readonly
                selectable: metadata.readOnly == true ? false : true,
                unselectAuto: false,
                editable: metadata.readOnly == true ? false : true,
                // --
                eventLimit: true,
                titleFormat: 'MMMM YYYY',
                slotEventOverlap: false,
                displayEventEnd: {
                    month: true
                },
                buttonText: {
                    'agendaWeek': 'Sett. Agenda'
                },
                dayNames: ['Domenica', 'LunedÏ', 'MartedÏ', 'MercoledÏ', 'GiovedÏ', 'VenerdÏ', 'Sabato'],
                height: that.parent().height() ? that.parent().height() : 'auto',
                fixedWeekCount: false,
                header: {
                    left: 'prev,next today',
                    center: 'title',
                    right: metadata.views ? metadata.views : 'month,basicWeek,agendaWeek'
                },
                viewRender: function (view, element) {
                    if (calendarParams[id].view !== null && calendarParams[id].start && calendarParams[id].start.isValid()) {
                        calendarParams[id].selectFromJS = true;
                        calendarParams[id].end && calendarParams[id].end.isValid() ? that.fullCalendar('select', calendarParams[id].start, calendarParams[id].end) : that.fullCalendar('select', calendarParams[id].start);
                        if (view.name !== calendarParams[id].view) {
                            if (!(view.start < calendarParams[id].start && calendarParams[id].start < view.end)) {
                                that.fullCalendar('gotoDate', calendarParams[id].start);
                            }
                        }
                    }

                    calendarParams[id].view = view.name;
                    view.end.subtract(1, 'days');
                    itaGo('ItaForm', that, {
                        event: 'onCalendarChange',
                        validate: true,
                        calendarParam: {
                            'start': view.start.format(),
                            'end': view.end.format(),
                            'view': calendarParams[id].view
                        }
                    });
                },
                select: function (start, end, event, view) {
                    if (!calendarParams[id].selectFromJS) {
                        calendarParams[id].start = start;

                        //                        if ( !end.hasTime() ) {
                        //                            end.subtract(1, 'days');
                        //                            calendarParams[id].end = end;
                        //                            if ( start.format('DMYY') == end.format('DMYY') ) {
                        //                                calendarParams[id].end = null;
                        //                            }
                        //                        } else
                        calendarParams[id].end = end;

                        itaGo('ItaForm', that, {
                            event: 'onCalendarSelect',
                            validate: true,
                            calendarParam: {
                                'start': calendarParams[id].start.format(),
                                'end': calendarParams[id].end ? calendarParams[id].end.format() : false,
                                'view': calendarParams[id].view
                            }
                        });
                    }

                    calendarParams[id].selectFromJS = false;
                },
                unselect: function (view, event) {
                    //calendarParams[id].start = null;
                    //calendarParams[id].end = null;
                },
                eventClick: function (event) {
                    if (event.editable == true)
                        itaGoCalendarEvent(that, event, 'onCalendarEventClick');
                },
                eventDrop: function (event, delta, revertFunc) {
                    itaGoCalendarEvent(that, event, 'onCalendarEventChange');
                },
                eventResize: function (event, delta, revertFunc) {
                    itaGoCalendarEvent(that, event, 'onCalendarEventChange');
                },
                events: function (start, end, timezone, callback) {
                    if (calendarParams[id].init) {
                        calendarParams[id].justRequested = true;
                        that.fullCalendar('removeEvents');
                        itaGo('ItaForm', that, {
                            event: 'onCalendarFetch',
                            validate: true,
                            calendarParam: {
                                'start': start.format(),
                                'end': end.format(),
                                'view': calendarParams[id].view
                            }
                        });
                    } else
                        calendarParams[id].init = true;

                    // Necessario per non far rimanere il calendario in "sospeso"
                    callback({});
                },
                eventRender: function (event, element, view) {
                    if ($(element).attr('href'))
                        $(element).attr('target', '_blank');
                    var text = '<div style="text-align:center;">' + event.calname + '<br><span style="font-size: .9em;">';
                    if (event.allDay == true) {
                        text += 'Tutto il giorno';
                    } else {
                        if (event.end && event.start.format('d') == event.end.format('d')) {
                            text += event.start.format('ddd, HH:mm') + ' - ' + event.end.format('HH:mm');
                        } else {
                            text += event.start.format('ddd, HH:mm');
                            if (event.end)
                                text += ' - ' + event.end.format('ddd, HH:mm');
                        }
                    }
                    text += '<br><b>' + event.title + '</b></span></div>';

                    if (event.descrizione)
                        text += '<br><br>' + event.descrizione;

                    var prom = event.hasProm ? $('<span class="ui-icon ui-icon-clock ui-icon-white" style="position: absolute; right: 0; background-color: ' + event.color + ';"></span>').attr('title', '<div style="text-transform: none;">' + event.promType + ' ' + moment.duration(event.hasProm * 1000).humanize() + ' prima</div>') : '';

                    $(element).attr('title', '<div style="text-transform: none;">' + text + '</div>').find('.fc-content').prepend(prom).prepend(event.icon).css('line-height', '19px').css('height', '18px').find('.ui-icon').css('margin-top', '1px');
                    creaTooltip($(element));
                    return element;
                }
            });
            if (!metadata.readOnly)
                $('<button id="' + formId + '_calendarNew" class="ui-button ui-state-default ui-corner-left ui-corner-right ita-button ita-calendar-new" type="button">Nuovo</button>').appendTo(that.find('.fc-left')).button().click(function () {
                    itaGo('ItaForm', that, {
                        event: 'onClick',
                        id: $(this).attr('id'),
                        validate: true,
                        calendarParam: {
                            'start': calendarParams[id].start.format(),
                            'end': calendarParams[id].end ? calendarParams[id].end.format() : false,
                            'view': calendarParams[id].view
                        }
                    });
                });
            $('<button id="' + formId + '_calendarSettings" class="ui-button ui-state-default ui-corner-left ui-corner-right ita-button ita-calendar-settings" type="button" style="width: 31px;"></button>').appendTo(that.find('.fc-left')).button({
                icons: {
                    primary: "ui-icon-gear"
                }
            }).click(function () {
                itaGo('ItaForm', that, {
                    event: 'onClick',
                    id: $(this).attr('id'),
                    validate: true,
                    calendarParam: {
                        'start': calendarParams[id].start.format(),
                        'end': calendarParams[id].end ? calendarParams[id].end.format() : false,
                        'view': calendarParams[id].view
                    }
                });
            });

            itaGetLib('libs/italsoft/GoogleClient.js', 'GoogleClient');

            var handleGoogleResponse = function (isAuthorized) {
                if (isAuthorized) {
                    $signInButton.get(0).style.visibility = 'hidden';
                    $signOutButton.get(0).style.visibility = '';

                    gapi.client.load('calendar', 'v3').then(function () {
                        gapi.client.calendar.calendarList.list({}).then(function (res) {
                            googleBroadcastHelper({
                                event: 'onGoogleCalendarList',
                                calendarList: res.result
                            });
                        }, function (reason) {
                            GoogleClient.signOut();
                            console.log('Error: ' + reason.result.error.message);
                        });
                    });
                } else {
                    $signInButton.get(0).style.visibility = '';
                    $signOutButton.get(0).style.visibility = 'hidden';
                }
            }

            var $signInButton = $('<button id="google-authorize-button" class="ui-button ui-state-default ui-corner-left ui-corner-right ita-button ita-calendar-new" type="button">Google</button>').appendTo(that.find('.fc-left')).button().css('visibility', 'hidden').click(function () {
                GoogleClient.signIn(handleGoogleResponse);
            });

            var $signOutButton = $('<button id="google-authorize-button-off" class="ui-button ui-state-default ui-corner-left ui-corner-right ita-button ita-calendar-new" type="button">Disconnetti Google</button>').appendTo(that.find('.fc-left')).button().css('visibility', 'hidden').click(function () {
                GoogleClient.signOut();
                handleGoogleResponse(false);
            });

            GoogleClient.load(handleGoogleResponse);

            $('<button id="' + formId + '_openAttivita" class="ui-button ui-state-default ui-corner-left ui-corner-right ita-button" type="button">Attivit&agrave;</button>').appendTo(that.find('.fc-right')).button().click(function () {
                itaGo('ItaForm', that, {
                    event: 'onClick',
                    id: $(this).attr('id'),
                    validate: true
                });
            });

            $('<button id="' + formId + '_openScadenze" class="ui-button ui-state-default ui-corner-left ui-corner-right ita-button" type="button">Scadenze</button>').appendTo(that.find('.fc-right')).button().click(function () {
                itaGo('ItaForm', that, {
                    event: 'onClick',
                    id: $(this).attr('id'),
                    validate: true
                });
            });
            //parseHtmlContainer(that.find('.fc-left'), 'html');
            setTimeout(function () {
                that.fullCalendar('render');
            }, 0);
        }
    });

    container.find('.ita-twain-plugin').each(function () {
        itaGetScript('itaTwainInit', 'itaTwain.js');
        itaTwainInit($(this).attr('id'));
    });

    container.find('.ita-twain-dwt-container').each(function () {
        itaGetScript('itaWebTwainLoad', 'dynamicwebtwain/itaWebTwainLoader.js');
        itaWebTwainLoad($(this).attr('id'));
    });

    container.find('.ita-element-animate').each(function () {
        $(this).addClass('ui-state-default').hover(
            function () {
                $(this).addClass('ui-state-hover');
            },
            function () {
                $(this).removeClass('ui-state-hover');
            }
        );
    });

    container.find('.ita-code-editor').each(function () {
        var id = $(this).attr('id');
        var metaData = $(this).metadata();

        if (!metaData.mode) {
            metaData.mode = 'php';
        }

        if ($(this).parent().hasClass('ita-field'))
            $(this).unwrap();

        $(this).change(function (e) {
            window.codeMirrors[id].setValue(this.value);
            window.codeMirrors[id].clearHistory();
            window.codeMirrors[id].setOption('mode', metaData.mode);
        });

        if (itaGetLib('libs/codemirror/codemirror.js', 'CodeMirror')) {
            itaGetLib('libs/codemirror/codemirror.css');

            itaGetLib('libs/codemirror/mode/php/php.js');
            itaGetLib('libs/codemirror/mode/xml/xml.js');
            itaGetLib('libs/codemirror/mode/css/css.js');
            itaGetLib('libs/codemirror/mode/clike/clike.js');
            itaGetLib('libs/codemirror/mode/htmlmixed/htmlmixed.js');
            itaGetLib('libs/codemirror/mode/javascript/javascript.js');
            itaGetLib('libs/codemirror/mode/properties/properties.js');

            itaGetLib('libs/codemirror/addon/selection/active-line.js');
            itaGetLib('libs/codemirror/addon/edit/closebrackets.js');
            itaGetLib('libs/codemirror/addon/edit/matchbrackets.js');
            itaGetLib('libs/codemirror/addon/edit/matchtags.js');
            itaGetLib('libs/codemirror/addon/fold/foldcode.js');
            itaGetLib('libs/codemirror/addon/fold/foldgutter.js');
            itaGetLib('libs/codemirror/addon/fold/foldgutter.css');
            itaGetLib('libs/codemirror/addon/fold/xml-fold.js');
            itaGetLib('libs/codemirror/addon/fold/brace-fold.js');
            itaGetLib('libs/codemirror/addon/fold/indent-fold.js');
            itaGetLib('libs/codemirror/addon/fold/comment-fold.js');
            itaGetLib('libs/codemirror/addon/fold/markdown-fold.js');

            $('head').append('<style>.CodeMirror { border: 1px solid #eee; font-size: 14px; height: 100%; }</style>');
            window.codeMirrors = {};
        }

        window.codeMirrors[id] = CodeMirror.fromTextArea(this, {
            dragDrop: false,
            lineNumbers: true,
            styleActiveLine: true,
            autoCloseBrackets: true,
            foldGutter: true,
            gutters: ["CodeMirror-linenumbers", "CodeMirror-foldgutter"],
            matchBrackets: true,
            matchTags: true,
            mode: metaData.mode
        });
    });

    container.find('.ita-menugrid').each(function () {
        if (itaGetLib('libs/gridster/jquery.gridster.js', '$.fn.gridster')) {
            itaGetLib('libs/gridster/jquery.gridster.css');

            window.menuGrids = Array();
        }

        var $this = $(this), id = $this.attr('id');

        $this.html('');
        menuGrids[id] = null;

        $this.on('click', function (e) {
            var $target = $(e.target);

            if ($target.is('.gs-resize-handle') || $target.is('[onclick]')) {
                return;
            }

            $target = $target.closest('.gs-w');

            if ($target.is('.gs-w') && !$target.is('.no-click')) {
                itaGo('ItaForm', $this, {
                    event: 'onClick',
                    cell: $target.attr('id')
                });

                $this.find('.preview-holder').remove();
            }
        });

        $this.on('ita-resize', function () {
            var $widgets = $this.children().detach();

            var min_cols = 0;
            $widgets.each(function () {
                min_cols = parseInt($(this).data('col')) > min_cols ? parseInt($(this).data('col')) : min_cols;
            });

            /*.attr('data-col', '1')
             .attr('data-row', '1')*/
            $widgets.find('.gs-resize-handle').remove();

            var opts = menuGrids[id].opts;
            opts.min_cols = min_cols;
            menuGrids[id].destroy();
            $this.html('');

            setTimeout(function () {
                $this.append($widgets);

                setTimeout(function () {
                    menuGrids[id] = $this.gridster(opts).data('gridster');
                    menuGrids[id].opts = opts;
                }, 10);
            }, 20);
        });
    });

    container.find('.ita-div-cms').each(function () {
        var metadata = $(this).metadata();

        if (metadata.elementSlug) {
            itaGo('ItaForm', this, {
                event: 'containerLoad',
                slug: metadata.elementSlug,
                validate: false
            });
        }
    });

    container.find('.ita-div-flex').each(function () {
        var metadata = $(this).metadata(),
            $divFlex = $(this),
            $lkCell1 = $(protSelector('#' + this.id + '_lk_cell1')),
            $itaField = $(protSelector('#' + this.id + '_field'));

        if ($itaField.length) {
            $divFlex = $itaField;
            delete metadata.flexNth;
            metadata.flexSelector = '#' + this.id;

            if ($itaField.next().is('.ita-br')) {
                $itaField.next().remove();
            }

            if ($lkCell1.length) {
                $itaField.css('width', '100%');
                $(protSelector('#' + this.id + '_lk_table')).css('width', '100%');
                $divFlex = $lkCell1;
            }
        }

        $divFlex.css({
            boxSizing: 'border-box',
            width: '100%',
            display: 'flex'
        });

        if (!metadata) {
            return;
        }

        if (metadata.flexColumn) {
            $divFlex.css('flex-direction', 'column');
        }

        $divFlex.children().css('flex', '0 0 auto');

        if (metadata.flexSelector) {
            $divFlex.children(protSelector(metadata.flexSelector)).css('flex', '1 1 auto');
        }

        if (typeof metadata.flexNth !== 'undefined') {
            $divFlex.children().eq(metadata.flexNth).css('flex', '1 1 auto');
        }
    });

    container.find('input.ita-selection-onfocus, textarea.ita-selection-onfocus').each(function () {
        $(this).focus(function () {
            this.setSelectionRange(0, this.value.length);
        });
    });

    container.find('.ita-flowchart').each(function () {
        var flowchart = this,
            flowchartID = flowchart.id,
            actionBarID = flowchartID + '_ActionBar',
            miniViewID = flowchartID + '_MiniView',
            metaData = $(flowchart).metadata(),
            multiSelectionButtons = [],
            selectionButtons = [];

        if (itaGetLib('libs/jsplumb/jsplumbtoolkit.min.js', 'jsPlumbToolkit')) {
            itaGetLib('libs/italsoft/itaJsPlumbHelper.js', 'itaJsPlumbHelper');
        }

        $(this).append('<div class="ita-jtk-actionbar" id="' + actionBarID + '"></div>');
        $(this).append('<div id="' + miniViewID + '"></div>');

        var addNodeButton = metaData && metaData.addNodeButton ? metaData.addNodeButton : false,
            editNodeButton = metaData && metaData.editNodeButton ? metaData.editNodeButton : false,
            delNodeButton = metaData && metaData.delNodeButton ? metaData.delNodeButton : false,
            exportButton = metaData && metaData.exportButton ? metaData.exportButton : false,
            importButton = metaData && metaData.importButton ? metaData.importButton : false,
            colorButton = metaData && metaData.colorButton ? metaData.colorButton : false,
            multiSelection = metaData && metaData.multiSelection ? metaData.multiSelection : false;
        groupsButton = metaData && metaData.groupsButton ? metaData.groupsButton : false;

        jsPlumbToolkit.ready(function () {
            var toolkit = jsPlumbToolkit.newInstance({
                portDataProperty: 'anchors',
                idFunction: function (e) {
                    return e.id;
                },
                typeFunction: function (e) {
                    return e.type;
                },
                beforeConnect: function (source, target) {
                    if (source.objectType !== 'Node') {
                        source = source.getNode();
                    }

                    if (target.objectType !== 'Node') {
                        target = target.getNode();
                    }

                    return source !== target;
                }
            });

            var renderer = toolkit.render({
                container: flowchartID,
                miniview: {container: miniViewID},
                enableAnimation: false,
                enablePanButtons: false,
                elementsDroppable: true,
                consumeRightClick: false,
                lassoInvert: true,
                layout: {
                    type: 'Absolute'
                },
                view: {
                    nodes: {
                        'default': {
                            events: {
                                tap: function (params) {
                                    var isSelected = true;
                                    toolkit.getSelection().eachNode(function (i, node) {
                                        if (node.id === params.node.id) {
                                            isSelected = false;
                                            return;
                                        }
                                    });

                                    if (!multiSelection) {
                                        toolkit.clearSelection();
                                        $('.ita-jtk-selected').removeClass('ita-jtk-selected');

                                        if (isSelected) {
                                            toolkit.addToSelection(params.node);
                                        }
                                    } else {
                                        toolkit.toggleSelection(params.node);
                                    }

                                    if (toolkit.getSelection().getNodes().length == 1) {
                                        itaJsPlumbHelper.showSelectionButtons(selectionButtons);
                                        itaJsPlumbHelper.showSelectionButtons(multiSelectionButtons);
                                    } else {
                                        itaJsPlumbHelper.hideSelectionButtons(selectionButtons);
                                    }

                                    itaGo('ItaForm', flowchart, {
                                        event: 'onClick',
                                        validate: true,
                                        selectionNode: params.node.id,
                                        selectionAction: isSelected ? 1 : 0
                                    });
                                },
                                mousedown: function (params) {
                                    if ($(params.e.target).is('.jtk-draw-handle')) {
                                        params.el._isResizing = true;
                                    }
                                },
                                mousemove: function (params) {
                                    if (params.el._isResizing === true) {
                                        $(params.el).find('.ita-jtk-anchor').each(function () {
                                            itaJsPlumbHelper.repositionNodeAnchor.call(this, {
                                                position: {
                                                    left: parseInt(this.style.left),
                                                    top: parseInt(this.style.top)
                                                }
                                            });
                                        });
                                    }
                                },
                                mouseup: function (params) {
                                    if (params.el._isResizing === true) {
                                        var node = renderer.getObjectInfo(params.el),
                                            data = node.obj.data;

                                        data.left = Math.round(data.left / 10) * 10;
                                        data.top = Math.round(data.top / 10) * 10;

                                        data.w = Math.round(data.w / 10) * 10;
                                        data.h = Math.round(data.h / 10) * 10;

                                        if (((data.w - 10) / 2) % 10 === 5) {
                                            data.w += 10;
                                        }

                                        if (((data.h - 10) / 2) % 10 === 5) {
                                            data.h += 10;
                                        }

                                        toolkit.updateNode(node.obj, data);

                                        $(params.el).find('.ita-jtk-anchor').each(function () {
                                            itaJsPlumbHelper.repositionNodeAnchor.call(this, {
                                                position: {
                                                    left: parseInt(this.style.left),
                                                    top: parseInt(this.style.top)
                                                }
                                            });
                                        });

                                        params.el._isResizing = false;
                                    }
                                }
                            }
                        },
                        'start': {parent: 'default', template: 'templateStart'},
                        'question': {parent: 'default', template: 'templateQuestion'},
                        'action': {parent: 'default', template: 'templateAction'},
                        'output': {parent: 'default', template: 'templateOutput'},
                        'edgeBreak': {
                            template: 'templateEdgeBreak',
                            anchor: 'Center',
                            events: {
                                contextmenu: function (params) {
                                    var edges = params.node.getAllEdges(),
                                        sourceEdge = edges[0].target.id == params.node.id ? edges[0] : edges[1],
                                        targetEdge = edges[1].source.id == params.node.id ? edges[1] : edges[0];

                                    var edge = toolkit.connect({
                                        source: sourceEdge.source,
                                        target: targetEdge.target,
                                        data: targetEdge.data
                                    });

                                    toolkit.removeNode(params.node);
                                    toolkit.addToSelection(edge);

                                    params.e.preventDefault();
                                    return false;
                                }
                            }
                        }
                    },
                    edges: {
                        'default': {
                            endpoint: ['Dot', {cssClass: 'jtk-endpoint-invisible'}],
                            connector: ['Straight'],
                            paintStyle: {strokeWidth: 3, stroke: '${strokeColor}', outlineStroke: 'transparent', outlineWidth: 5},
                            hoverPaintStyle: {strokeWidth: 3, stroke: 'red'},
                            detachable: false,
                            anchor: 'Center',
                            events: {
                                tap: function (params) {
                                    itaJsPlumbHelper.edgeTapFunction(toolkit, params);
                                },
                                contextmenu: function (params) {
                                    itaJsPlumbHelper.edgeContextmenuFunction(toolkit, params);
                                }
                            },
                            overlays: [
                                ['Label', {label: '${label}'}]
                            ]
                        },
                        'connection': {
                            detachable: false,
                            overlays: [
                                ['Arrow', {width: 15, length: 15, location: 1}]
                            ],
                            events: {
                                tap: function (params) {
                                    itaJsPlumbHelper.edgeTapFunction(toolkit, params);
                                },
                                contextmenu: function (params) {
                                    itaJsPlumbHelper.edgeContextmenuFunction(toolkit, params);
                                }
                            }
                        }
                    },
                    ports: {
                        'default': {
                            endpoint: ['Dot', {cssClass: 'jtk-endpoint-invisible'}],
                            maxConnections: -1,
                            isTarget: false,
                            isSource: false
                        }
                    }
                },
                events: {
                    canvasClick: function () {
                        itaJsPlumbHelper.clearCanvasSelection(toolkit, flowchart);
                        itaJsPlumbHelper.hideSelectionButtons(selectionButtons);
                        itaJsPlumbHelper.hideSelectionButtons(multiSelectionButtons);
                    },
                    relayout: function () {
                        itaJsPlumbHelper.hideSelectionButtons(selectionButtons);
                        itaJsPlumbHelper.hideSelectionButtons(multiSelectionButtons);
                    }
                },
                dragOptions: {
                    grid: [10, 10],
                    filter: '.jtk-draw-handle, .node-action, .node-action i, .ita-jtk-anchor'
                }
            });

            new jsPlumbToolkit.DrawingTools({
                renderer: renderer
            });

            var $actionBar = $('#' + actionBarID);

            if (addNodeButton) {
                var $addNodeButton = itaJsPlumbHelper.getActionBarButton(actionBarID + '_Add', 'ui-icon-plus', 'Nodo').appendTo($actionBar);
            }

            if (editNodeButton) {
                var $editNodeButton = itaJsPlumbHelper.getActionBarButton(actionBarID + '_Edit', 'ui-icon-pencil', 'Modifica').appendTo($actionBar).hide();
                selectionButtons.push($editNodeButton);
            }

            if (delNodeButton) {
                var $delNodeButton = itaJsPlumbHelper.getActionBarButton(actionBarID + '_Delete', 'ui-icon-delete', 'Cancella').appendTo($actionBar).hide();
                selectionButtons.push($delNodeButton);
            }

            if (exportButton) {
                var $exportButton = itaJsPlumbHelper.getActionBarButton(actionBarID + '_Export', 'ui-icon-logoff', 'Esporta').appendTo($actionBar);
            }

            if (importButton) {
                var $importButton = itaJsPlumbHelper.getActionBarButton(actionBarID + '_Import', 'ui-icon-login', 'Importa').appendTo($actionBar);
            }

            if (colorButton) {
                var $changecolorButton = itaJsPlumbHelper.getActionBarButton(actionBarID + '_ChangeColor', 'ui-icon-bucket', 'Colora').appendTo($actionBar);
                multiSelectionButtons.push($changecolorButton);
            }

            if (groupsButton) {
                var $groupsButton = itaJsPlumbHelper.getActionBarButton(actionBarID + '_Groups', 'ui-icon-structure', 'Gruppi').appendTo($actionBar);
            }

            toolkit.bind('dataLoadEnd', function () {
                $('.ita-jtk-anchor').each(function () {
                    $(this).draggable({
                        drag: itaJsPlumbHelper.updateNodeAnchor,
                        stop: itaJsPlumbHelper.updateNodeAnchor
                    });
                });
            });

            parseHtmlContainer($actionBar);

            jsPlumbInstances[flowchartID] = toolkit;
            jsPlumbInstances.Renderers[flowchartID] = renderer;
        });
    });

    container.find('.ita-fullscreen').each(function () {
        var $toggle = $('<i class="ita-fullscreen-toggle ui-icon ui-icon-fullscreen"></i>');
        $(this).prepend($toggle);

        itaFullscreenActivate($(this), $toggle);
    });

    container.find('.ita-clipboard').each(function () {
        itaGetLib('libs/clipboard.js/clipboard.min.js', 'ClipboardJS');

        if (!ClipboardJS.isSupported()) {
            $(this).remove();
            return;
        }

        var metadata = $(this).metadata(),
            metadataTarget = $(metadata.target).get(0);

        var clipboard = new ClipboardJS(this, {
            target: function () {
                return metadataTarget;
            }
        });

        clipboard.on('success', function (e) {
            $.blockUI({
                fadeIn: 600,
                fadeOut: 600,
                timeout: 1000,
                overlayCSS: {
                    backgroundColor: 'transparent',
                    opacity: .4,
                    'z-index': 99999999
                },
                css: {
                    border: 0,
                    backgroundColor: 'transparent'
                },
                message: '<div class="ita-thick-border ita-msgBlock ui-corner-all">Testo copiato</div>'
            });

            e.clearSelection();
        });
    });

    /*
     * 18.10.2016 #nuovo-input-mask
     */
    parseDateTimeWithin(container);
    itaInputMaskWithin(container);
}

function itaGoCalendarEvent(calendar, event, eventName) {
    if (event.start)
        var start = event.start.format();
    if (event.end)
        var end = event.end.format();
    itaGo('ItaForm', calendar, {
        event: eventName,
        validate: true,
        calendarParam: {
            'start': calendarParams[$(calendar).attr('id')].start.format(),
            'end': calendarParams[$(calendar).attr('id')].end ? calendarParams[$(calendar).attr('id')].end.format() : false,
            'view': calendarParams[$(calendar).attr('id')].view,
            'event': {
                'id': event.id,
                'title': event.title,
                'allDay': event.allDay,
                'start': start ? start : null,
                'end': end ? end : null,
                'url': event.url,
                'className': event.className,
                'color': event.color,
                'backgroundColor': event.backgroundColor,
                'borderColor': event.borderColor,
                'textColor': event.textColor
            }
        }
    });
}

function fullCalendarGoogle(id, source, start, end) {
    calendarParams[id].justRequested = false; // Fix per eventi duplicati da Google
    start = moment(start);
    end = moment(end);
    var request = gapi.client.calendar.events.list({
        calendarId: source.gid,
        timeMin: start.format("YYYY-MM-DD") + 'T' + start.format("HH:mm:ssZ"),
        timeMax: end.format("YYYY-MM-DD") + 'T' + end.format("HH:mm:ssZ")
    });
    request.then(function (resp) {
        var events = [];
        $.each(resp.result.items, function (i, entry) {
            events.push({
                id: entry.id,
                title: entry.summary,
                start: entry.start.dateTime || entry.start.date,
                end: entry.end.dateTime || entry.end.date,
                url: entry.htmlLink,
                location: entry.location,
                description: entry.description,
                color: source.color,
                calname: source.name,
                icon: source.icon,
                editable: false
            });
        });
        if (calendarParams[id].justRequested == false)
            $('#' + id).fullCalendar('addEventSource', events);
    }, function (reason) {
        googleAuthSignOut();
        console.log('Error: ' + reason.result.error.message);
    });
}

function creaTooltip(id) {
    var obj;
    if (id instanceof jQuery) {
        obj = id;
    } else {
        obj = $(protSelector("#" + id));
    }
    if ($(obj).data('tooltip')) {
        $(obj).tooltip("destroy");
    }

    if ($(obj).is('.ita-simple-tooltip')) {
        $(obj).tooltip({
            content: function () {
                return $(this).prop('title');
            },
            tooltipClass: 'ita-ui-simple-tooltip',
            position: {
                my: "center bottom-10",
                at: "center top"
            }
        });

        return true;
    }

    $(obj).tooltip({
        content: function () {
            return $(this).prop('title');
        },
        position: {
            my: "center bottom-20",
            at: "center top",
            using: function (position, feedback) {
                $(this).css(position);
                $("<div>")
                    .addClass("arrow")
                    .addClass(feedback.vertical)
                    .addClass(feedback.horizontal)
                    .appendTo(this);
            }
        }
    });
}


function googleBroadcastHelper(opts) {
    $('#ita-desktop').itaGetChildForms().each(function () { // @FORM FIXED 16.03.15 | 07.10.15
        var currModel = $(this).attr('id');
        opts.model = currModel;
        if (currModel) {
            itaGo('ItaCall', '', opts);
        }
    });
}

function ieOpenWindows(openParam, requestParam) {
    //var ieurl, ieuser, iepass, iedomain, ietoken;
    var encodedParam = '',
        domain = openParam['iedomain'],
        target = false;

    for (var propertyName in requestParam) {
        encodedParam += '&' + propertyName + '=' + requestParam[propertyName];
    }

    if (ieParent && ieParent == domain && window.opener && window.opener.closed == false) {
        target = window.opener;
    } else if (ieChildWindows[domain]) {
        if (ieChildWindows[domain].closed == false) {
            target = ieChildWindows[domain];
        } else {
            ieChildWindows[domain] = null;
        }
    }

    if (target) {
        if (openParam['ieOpenMessage']) {
            target.alert(openParam['ieOpenMessage']);
        }
        target.itaGo('ItaCall', '', requestParam);
    } else {
        var initsep = openParam.ieurl.indexOf('?') > -1 ? '&' : '?';
        ieChildWindows[domain] = window.open(openParam['ieurl'] + initsep + "accesstoken=" + openParam['ietoken'] + "&accessorg=" + openParam['iedomain'] + "&access=direct" + encodedParam, '');
        if (openParam.ieparent) {
            ieChildWindows[domain].ieParent = openParam.ieparent;
        }
    }
}

function enableField(id) {
    var $elemento = $('#' + id);
    var buttons = [];

    if ($elemento.hasClass('ita-datepicker')) {
        buttons.push($('#' + id + '_datepickertrigger'));
    } else if ($elemento.hasClass('ita-edit-lookup')) {
        buttons.push($('#' + id + '_butt').parent());
    } else if ($elemento.hasClass('ita-edit-upload')) {
        buttons.push($('#' + id + '_upld').parent());
    }

    if (!$elemento.attr('data-field-original-status')) {
        $elemento.attr('data-field-original-status', ($elemento.hasClass('ita-readonly') || $elemento.is('[readonly]') || $elemento.is('[disabled]') ? 'disabled' : 'enabled'));
    }

    $elemento.removeAttr('readonly').removeClass('ita-readonly');

    if ($elemento.is('select, input[type="checkbox"], input[type="radio"]')) {
        $elemento.removeAttr('disabled');
    }

    buttons.forEach(function ($button) {
        $button.css('visibility', 'visible');
    });

    itaInputMaskWithin($elemento.parent());
}

function disableField(id) {
    var $elemento = $('#' + id);
    var buttons = [];

    if ($elemento.hasClass('ita-datepicker')) {
        buttons.push($('#' + id + '_datepickertrigger'));
    } else if ($elemento.hasClass('ita-edit-lookup')) {
        buttons.push($('#' + id + '_butt').parent());
    } else if ($elemento.hasClass('ita-edit-upload')) {
        buttons.push($('#' + id + '_upld').parent());
    }

    if (!$elemento.attr('data-field-original-status')) {
        $elemento.attr('data-field-original-status', ($elemento.hasClass('ita-readonly') || $elemento.is('[readonly]') || $elemento.is('[disabled]') ? 'disabled' : 'enabled'));
    }

    $elemento.attr('readonly', 'readonly').addClass('ita-readonly');

    if ($elemento.is('select, input[type="checkbox"], input[type="radio"]')) {
        $elemento.attr('disabled', 'disabled');
    }

    buttons.forEach(function ($button) {
        $button.css('visibility', 'hidden');
    });

    itaInputUnmaskWithin($elemento.parent());
}

function restoreField(id) {
    var $elemento = $('#' + id);


    switch ($elemento.attr('data-field-original-status')) {
        case 'enabled':
            enableField(id);
            break;

        case 'disabled':
            disableField(id);
            break;
    }
}

function itaFullscreenActivate($source, $toggle /*, callbackFullscreenOn, callbackFullscrenOff*/) {
    var metadata = $source.metadata(),
        $target = metadata.fullscreenTarget ? $source.parent().closest(metadata.fullscreenTarget) : $source.parent(),
        targetPosition = $target.css('position'),
        $originPrevious, $originParent,
        sendEvents = metadata.fullscreenEvents || false,
        callbackFullscreenOn = arguments[2] || false,
        callbackFullscrenOff = arguments[3] || false;

    $source.css('position', 'relative');
    if (!$source.get(0).style.backgroundColor) {
        $source.css('background-color', 'white');
    }

    $source.on('fullscreen-on', function () {
        $toggle.removeClass('ui-icon-fullscreen').addClass('ui-icon-fullscreen-off');

        if ($target) {
            $originPrevious = $originParent = false;

            /*
             * Trovo l'elemento di riferimento per il riposizionamento.
             */
            var $previous = $source.prev();
            if ($previous.length) {
                $originPrevious = $previous;
            } else {
                $originParent = $source.parent();
            }

            $source.detach().appendTo($target);
        }

        if (targetPosition != 'relative' && targetPosition != 'absolute') {
            $source.parent().addClass('ita-fullscreen-container');
        }

        $source.addClass('ita-fullscreen-active');

        if (typeof callbackFullscreenOn === 'function') {
            callbackFullscreenOn();
        }

        if (sendEvents) {
            itaGo('ItaForm', $source, {
                asyncCall: false,
                bloccaui: true,
                event: 'fullscreenOn',
                validate: false
            });
        }

        resizeTabs();
    }).on('fullscreen-off', function () {
        $toggle.removeClass('ui-icon-fullscreen-off').addClass('ui-icon-fullscreen');

        $source.parent().removeClass('ita-fullscreen-container');
        $source.removeClass('ita-fullscreen-active');

        if ($target) {
            if ($originParent) {
                $source.detach().prependTo($originParent);
            } else {
                $source.detach().insertAfter($originPrevious);
            }
        }

        if (typeof callbackFullscrenOff === 'function') {
            callbackFullscrenOff();
        }

        if (sendEvents) {
            itaGo('ItaForm', $source, {
                asyncCall: false,
                bloccaui: true,
                event: 'fullscreenOff',
                validate: false
            });
        }

        resizeTabs();
    });

    $toggle.on('click', function () {
        if ($toggle.hasClass('ui-icon-fullscreen')) {
            $source.trigger('fullscreen-on');
        } else {
            $source.trigger('fullscreen-off');
        }
    });
}