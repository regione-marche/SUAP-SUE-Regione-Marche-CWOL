/*
 * Fix compatibilità jQuery.
 */

jQuery.curCSS = function (e, p, v) {
    return jQuery(e).css(p, v);
};

$ = jQuery;

jQuery(function ($) {
    /**
     * Fix per IE8
     */
    if (!Array.indexOf) {
        Array.prototype.indexOf = function (obj) {
            for (var i = 0; i < this.length; i++) {
                if (this[i] == obj) {
                    return i;
                }
            }
            return -1;
        };
    }
});

function protSelector(selector) {
    var pa = "\\["; // PROTEGGO LE PARENTESI PER FAR FUNZIONARE I SELETTORI CSS
    var pc = "\\]";
    var newSelector = selector.replace(/\[/g, pa);
    newSelector = newSelector.replace(/\]/g, pc);
    return newSelector;
}

function openMaps(Comune, Provincia) {
    $('.ita-open-maps').each(function () {
        $(this).click(function () {
            idButton = $(this).attr("id");
            idSelect = idButton.substring(0, 17);
            via = $('#' + protSelector(idSelect)).val();
            urlTmp = "http://maps.google.com/?hl=it&tab=wl&q=" + Comune + ", " + Provincia + ", " + via;
            url = urlTmp.replace(" ", "+");
            window.open(url, '');
        });
    });
}

/**
 * Utility itaFrontOffice
 */
var itaFrontOffice = (function ($) {
    var isAdvancedUpload = function () {
        var div = document.createElement('div');
        return (('draggable' in div) || ('ondragstart' in div && 'ondrop' in div)) && 'FormData' in window && 'FileReader' in window;
    }();

    function getQueryParams(qs) {
        qs = qs.split('+').join(' ');

        var params = {},
            tokens,
            re = /[?&]{1}([^&=]+)=([^&]*)/g;

        while (tokens = re.exec(qs)) {
            params[decodeURIComponent(tokens[1])] = decodeURIComponent(tokens[2]);
        }

        return params;
    }

    function clear_form_data(form) {
        var not = '[type="hidden"], [readonly], [disabled]';

        $(form).find('input, textarea').not(not).each(function () {
            if (['checkbox', 'radio'].indexOf(this.type.toLowerCase()) > -1) {
                this.checked = false;
            } else {
                this.value = '';
            }
        });

        $(form).find('select').not(not).each(function () {
            this.selectedIndex = 0;
        });

        /*
         * Check per input readonly ma con lentina di ricerca
         */
        $(form).find('input').each(function () {
            if ($(this).parent('.ita-field').find('[data-ricerca]').length) {
                this.value = '';
            }
        });
    }

    function italsoft_table_selectable($table) {
        $table.find('tbody > tr').on('click', function () {
            var fields = $table.data('selectable'),
                idRaccolta = $table.attr('data-raccolta') ? '_' + $table.attr('data-raccolta') : '';

            for (var k in fields) {
                if (!fields[k]) {
                    continue;
                }

                var $input = $("#" + protSelector(fields[k] + idRaccolta));

                if (!$input.length) {
                    continue;
                }

                var value = $(this).find('td').eq(k).text();

                switch ($input.prop('tagName').toLowerCase()) {
                    case 'input':
                        $input.val(value);
                        break;

                    case 'select':
                        var select = $input.find('option[value="' + value + '"]');

                        if (select.length) {
                            select.attr('selected', 'selected');
                        }
                        break;
                }
            }

            if ($(this).closest('table').parent().is('.ui-dialog-content')) {
                $(this).closest('table').parent().dialog('close');
            }
        });
    }

    function itafrontoffice_table_get_addgridrow_button($tableObj) {
        return itafrontoffice_table_get_action_button('addGridRow', 'ion-plus', $tableObj);
    }

    function itafrontoffice_table_get_editgridrow_button($tableObj, rowid, ajax) {
        return itafrontoffice_table_get_action_button('editGridRow', 'ion-edit', $tableObj, rowid);
    }

    function itafrontoffice_table_get_delgridrow_button($tableObj, rowid, ajax) {
        return itafrontoffice_table_get_action_button('delGridRow', 'ion-trash-a', $tableObj, rowid);
    }

    function itafrontoffice_table_get_action_button(event, icon, $tableObj, rowid) {
        var buttonID = '';
        if (typeof $tableObj !== 'undefined') {
            buttonID = $tableObj.attr('id') + '_' + event;
            if (typeof rowid !== 'undefined') {
                buttonID += '_' + rowid;
            }
        }

        var $button = $('<div id="' + buttonID + '" style="text-align: center;"><button class="italsoft-button italsoft-button--circled"><span class="italsoft-icon ' + icon + '"></span></button></div>');
        $button.on('click', function (e) {
            var extraData = {};

            if (typeof rowid !== 'undefined') {
                extraData.rowid = rowid;
            }

            if (typeof $tableObj !== 'undefined') {
                extraData.id = $tableObj.attr('id');
            }

            itafrontoffice_ajax(ajax.action, ajax.model, event, this, extraData);

            e.preventDefault();
        });

        return $button;
    }

    function itafrontoffice_parse($element) {
        $element.find('.italsoft-datatable').each(function () {
            var $this = $(this),
                $pager = $('#pager' + this.id).length ? $('#pager' + this.id) : $('#pager');

            $this.tablesorter({
                widgets: ['zebra', 'reflow'],
                theme: 'blue',
                dateFormat: 'ddmmyyyy'
            });

            if ($pager.length) {
                $this.tablesorterPager({
                    container: $pager,
                    positionFixed: false
                });
            }
        });

        $element.find(".tabella_allegati").each(function () {
            var $table = $(this);
            var widgets = $(this).hasClass('tabella_filtri') ? ['zebra', 'filter'] : ['zebra'];
            widgets.push('reflow');

            $table.tablesorter({
                widthFixed: true,
                widgets: widgets,
                theme: 'blue'
            });

            if (widgets.indexOf('filter') > -1) {
                $table.find('th.ita-hidden-cell').each(function () {
                    $table.find('tr.tablesorter-filter-row td').eq($(this).index()).addClass('ita-hidden-cell');
                });
            }
        });

        $element.find(".tabella_allegati").each(function () {
            $(this).find("table").attr("class", "tabella_allegati");
            var numTable = $(this).attr("id").substring(16);
            var pInfo = $("#pager" + numTable).find(".pagedisplay").val();
            var pSize = $("#pager" + numTable).find(".pagesize").val();
            if (pInfo) {
                var pArr = pInfo.split("/");
                var pNum;
                if (pArr[0] > 0) {
                    pNum = pArr[0] - 1
                } else {
                    pNum = 0;
                }
                //var pMax = pArr[1];    
            } else {
                pNum = 0;
            }
            $(this).tablesorterPager({
                container: $("#pager" + numTable),
                positionFixed: false,
                page: pNum,
                size: pSize
            });
        });

        $element.find("#tabel_result_not_zebra").tablesorter({
            widthFixed: true
        });

        $element.find('.ita-alert').each(function () {
            $(this).css("display", "block").dialog({
                height: "auto",
                width: "auto",
                modal: true,
                close: function (event, ui) {
                    $(this).remove();
                }
            });
        });


        $element.find(".ita-button-ricerca").click(function () {
            $("#div_" + this.id + "_ric").slideToggle();
        }).each(function () {
            var $table = $("#div_" + this.id + "_ric");
            $table.find('table tbody tr').click(function (e) {
                $(this).parents('.ita-field').children('input').val($(this).children('td').first().text().trim());
                $table.slideUp();
            });
        });

        $element.find(".ita-button-ricerca_dialog").click(function () {
            $("#dialog_ricerca_indirizzi").attr('data-parent', $(this).attr('id'));
            var tableCaption = $("#dialog_ricerca_indirizzi").attr('data-caption');
            $("#dialog_ricerca_indirizzi").show();
            $("#dialog_ricerca_indirizzi").dialog({
                modal: true,
                title: tableCaption,
                close: function (event, ui) {
                    $("#dialog_ricerca_indirizzi").removeAttr('data-parent');
                }
            });

        }).each(function () {
            var $table = $("#dialog_ricerca_indirizzi");
            $table.find('table tbody tr').click(function (e) {
                var bottone = $(this).parents('#dialog_ricerca_indirizzi').attr('data-parent');
                $("#" + bottone).parents('.ita-field').children('input').val($(this).children('td').first().text());
                $("#dialog_ricerca_indirizzi").removeAttr('data-parent');
                $table.dialog("destroy");
                $table.hide();
            });
        });

        $element.find('.ita-question').each(function () {
            $(this).css("display", "block").dialog({
                height: "auto",
                width: "auto",
                modal: true,
                close: function (event, ui) {
                    $(this).remove();
                }
            });
        });

        $element.find('.ita-info').each(function () {
            $(this).css("display", "block").dialog({
                height: "auto",
                width: "auto",
                modal: true,
                close: function (event, ui) {
                    $(this).remove();
                }
            });
        });


        $element.find('.ita-button-elencopassi').each(function () {
            $(this).click(function () {
                $("#divElencoPassi").slideToggle();
            });
        });

        $element.find('.ita-close-dialog').each(function () {
            $(this).click(function () {
                $(".ita-alert").dialog('close');
            });
        });

        $element.find('.ita-table, .italsoft-table').each(function () {
            var $parentForm = $(this).parents('form');

            $(this).find('tbody tr').click(function (e) {
                var idRaccolta = $(this).parents('table').attr('data-raccolta');
                var strIdRaccolta = idRaccolta ? '[' + idRaccolta + ']' : '';

                $(this).find('td[data-ita-edit-ref]').each(function () {
                    var inputValue = $(this).text();
                    var inputName = $(this).attr('data-ita-edit-ref').replace(/\[/, strIdRaccolta + '[');

                    var $inputs = $parentForm.find('[name="' + protSelector(inputName) + '"]');

                    $inputs.each(function () {
                        var $input = $(this);

                        switch ($input.prop('tagName').toLowerCase()) {
                            case 'input':
                                switch ($input.prop('type').toLowerCase()) {
                                    default:
                                        $input.val(inputValue);
                                        break;

                                    case 'checkbox':
                                        if (['1', 'on', 'si'].indexOf(inputValue) !== -1) {
                                            $input.attr('checked', 'checked');
                                        } else {
                                            $input.removeAttr('checked');
                                        }
                                        break;

                                    case 'radio':
                                        if ($input.val() == inputValue) {
                                            $input.attr('checked', 'checked');
                                        } else {
                                            $input.removeAttr('checked');
                                        }
                                        break;
                                }
                                break;

                            case 'select':
                                var select = $input.find('option[value="' + inputValue + '"]');

                                if (select.length) {
                                    select.attr('selected', 'selected');
                                }
                                break;

                            case 'textarea':
                                $input.val(inputValue);
                                break;
                        }

                        $input.trigger('change');
                    });
                });

                var isClosable = (!$(e.target).is('td') || $(this).find('[data-ita-edit-ref]').length);
                if ($(this).closest('table').parent().is('.ui-dialog-content') && isClosable) {
                    $(this).closest('table').parent().dialog('close');
                }
            });
        });

        $element.find('.ita-accordion-menu a').each(function () {
            var $this = $(this), $subMenu = $this.next('ul'), isCollapsed = true;

            if ($subMenu.length) {
                var $accordionToggle = $('<span class="ita-accordion-menu-arrow" style="font-weight: bold; font-family: monospace; float: right;">[-]</span>').prependTo($this);
                $subMenu.css('margin-bottom', '10px');

                if ($subMenu.find('[href="?' + window.location.href.split('?', 2)[1] + '"]').length) {
                    isCollapsed = false;
                }

                $this.bind('closeAccordion', function (e, forceClose) {
                    $accordionToggle.html('[+]');
                    isCollapsed = true;
                    forceClose ? $subMenu.hide() : $subMenu.hide('slide up');
                });

                $accordionToggle.bind('click', function (e) {
                    if (isCollapsed) {
                        $subMenu.find('a').trigger('closeAccordion', true);
                        $this.parent().siblings().find('a').trigger('closeAccordion', false);
                    }

                    $accordionToggle.html(isCollapsed ? '[-]' : '[+]');
                    isCollapsed = !isCollapsed;

                    e.preventDefault();
                    $subMenu.stop(true, true).toggle('slide up');
                });

                if (isCollapsed) {
                    $this.trigger('closeAccordion', true);
                }
            }
        });

        $element.find('[readonly]').each(function () {
            $(this).attr('tabindex', "-1");
        });

        $element.find('.italsoft-treeview').each(function () {
            var isAccordion = $(this).is('.italsoft-treeview--accordion');

            $(this).on('click', function (e) {
                var $target = $(e.target);

                if ($target.is('a, ul') || $target.parent().is('a')) {
                    return true;
                }

                e.preventDefault();
                e.stopPropagation();

                var $li = $target.closest('li'),
                    $ul = $li.children('ul'),
                    $button = $li.find('> div > .italsoft-treeview-button-group .italsoft-treeview-button--expand');

                if ($ul.length) {
                    if (!$li.is('.italsoft-treeview-item--open')) {
                        $li.addClass('italsoft-treeview-item--open');
                        $ul.slideDown(250);

                        if (isAccordion) {
                            $li
                                .siblings('li.italsoft-treeview-item--open')
                                .find('ul').find('.italsoft-treeview-item--open > ul').addBack('ul')
                                .slideUp(250, function () {
                                    $(this).parent().removeClass('italsoft-treeview-item--open');

                                    if ($li.offset().top < $(window).scrollTop()) {
                                        $('html, body').animate({scrollTop: ($li.offset().top - 100)}, 250);
                                    }
                                });
                        }
                    } else {
                        $ul.slideUp(250, function () {
                            $li.removeClass('italsoft-treeview-item--open');

                            if (isAccordion) {
                                $li.find('.italsoft-treeview-item--open > ul').slideUp(0, function () {
                                    $(this).parent().removeClass('italsoft-treeview-item--open');
                                });
                            }
                        });
                    }

                    return false;
                }

                if ($button.is('[data-ajax]')) {
                    itafrontoffice_ajax(ajax.action, ajax.model, 'onTreeViewExpand', this, {id: $button[0].dataset.ajax}, function (res) {
                        $li.append(res.html).children('ul')
                            .removeClass('italsoft-treeview').css('display', 'none');

                        $li.click();
                    });
                }

                return false;
            });
        });

        $element.find('.italsoft-table').each(function () {
            var $this = $(this),
                $form = $this.closest('form'),
                $pager = $('<div class="pager"></div>'),
                rowsPerPage = $this.attr('data-rows-per-page') ? JSON.parse($this.attr('data-rows-per-page')) : [10, 20, 50],
                currentPage = $this.attr('data-page') ? (parseInt($this.attr('data-page')) - 1) : 0,
                n,
                options = {
                    widgets: ['zebra', 'reflow'],
                    dateFormat: 'ddmmyyyy',
                    widgetOptions: {},
                    textExtraction: function (node, table, cellIndex) {
                        n = $(node);
                        return n.attr('data-sortValue') || n.find('[data-sortValue]').attr('data-sortValue') || n.text();
                    }
                };

            if (!$this.is('.italsoft-table--sortable')) {
                $this.find('th').data('sorter', false);
            }

            if ($this.is('.italsoft-table--filters')) {
                options.widgets.push('filter');
                if ($this.is('[data-ajax]')) {
                    options.widgetOptions.filter_liveSearch = false;
                }
            }

            if ($this.is('.italsoft-table--stickyheaders')) {
                options.widgets.push('cssStickyHeaders');
                options.widgetOptions.cssStickyHeaders_attachTo = this.parentElement;
                options.widgetOptions.cssStickyHeaders_filteredToTop = false;
            }

            if ($this.is('.italsoft-table--button-add')) {
                $pager.append($('<div style="float: left; margin-left: 15px;"></div>').append(itafrontoffice_table_get_addgridrow_button($this)));
            }

            if ($this.is('.italsoft-table--button-edit')) {
                $this.find('thead tr, tfoot tr').prepend('<th class="filter-false" data-sorter="false"></th>');
                $this.find('tbody tr').each(function () {
                    $(this).prepend($('<td></td>').append(itafrontoffice_table_get_editgridrow_button($this, $(this).data('key'))));
                });
            }

            if ($this.is('.italsoft-table--button-del')) {
                $this.find('thead tr, tfoot tr').prepend('<th class="filter-false" data-sorter="false"></th>');
                $this.find('tbody tr').each(function () {
                    $(this).prepend($('<td></td>').append(itafrontoffice_table_get_delgridrow_button($this, $(this).data('key'))));
                });
            }

            if ($this.is('[data-sort-column]')) {
                var columnIndex = $this.data('sort-column'),
                    sortOrder = $this.data('sort-order');

                if (isNaN(columnIndex)) {
                    columnIndex = $this.find('thead [data-key="' + columnIndex + '"]').index();
                }

                if (typeof sortOrder == 'string') {
                    sortOrder = sortOrder.toLowerCase();
                }

                options.sortList = [[columnIndex, sortOrder === 'desc' ? 1 : 0]];
            }

            $this.tablesorter(options);

            if ($this.is('.italsoft-table--filters')) {
                $this.find('.tablesorter-filter-row > td > input').each(function () {
                    this.id = 'tablefilter_' + this.dataset.column;
                    this.name = 'tablefilter_' + this.dataset.column;
                });
            }

            if ($this.is('[data-selectable]')) {
                italsoft_table_selectable($this);
            }

            if ($this.is('.italsoft-table--paginated')) {
                var selectHtml = '<select name="ita-pageNrows" class="pagesize">';
                for (var i in rowsPerPage) {
                    selectHtml += '<option value="' + rowsPerPage[i] + '">' + rowsPerPage[i] + '</option>';
                }
                selectHtml += '</select> per pagina &mdash; ';

                $pager
                    .append('<button type="button" class="first italsoft-button italsoft-button--circled"><i class="icon ion-ios-skipbackward italsoft-icon"></i></button>')
                    .append('<button type="button" class="prev italsoft-button italsoft-button--circled"><i class="icon ion-arrow-left-b italsoft-icon"></i></button>')
                    .append('<span class="pagedisplay"></span>')
                    .append('<button type="button" class="next italsoft-button italsoft-button--circled"><i class="icon ion-arrow-right-b italsoft-icon"></i></button>')
                    .append('<button type="button" class="last italsoft-button italsoft-button--circled"><i class="icon ion-ios-skipforward italsoft-icon"></i></button>')
                    .append(selectHtml)
                    .append('pag. <select class="gotoPage"></select>');

                var datapagerOptions = {
                    container: $pager,
                    positionFixed: false,
                    size: rowsPerPage[0],
                    page: currentPage,
                    savePages: false,
                    output: '{startRow} &ndash; {endRow} di {totalRows}'
                };

                if ($this.is('[data-save-pager]')) {
                    datapagerOptions.savePages = true;
                }

                if ($this.is('[data-ajax]')) {
                    var $thead = $this.find('thead > tr > th'),
                        jsonData = {};

                    jsonData.action = ajax.action;
                    jsonData.model = ajax.model;
                    jsonData.data = ajax.data;
                    jsonData.event = 'tablePager';
                    if (this.id) {
                        jsonData.id = this.id;
                    }

                    for (var i in this.dataset) {
                        jsonData[i] = this.dataset[i];
                    }

                    var queryString = window.location.search ? window.location.search + '&' : '?';

                    datapagerOptions.processAjaxOnInit = true;
                    datapagerOptions.ajaxUrl = ajax.url + queryString + 'page={page}&size={size}&{sortList:column}';

                    datapagerOptions.ajaxProcessing = function (data) {
                        itafrontoffice_parse_ajax_response(data);
                        var tableData = data;

                        if (data && typeof data.table !== 'undefined') {
                            tableData = data.table;
                        }

                        if ($this.is('.italsoft-table--button-edit')) {
                            for (var i in tableData) {
                                tableData[i].unshift(itafrontoffice_table_get_editgridrow_button($this, i));
                            }
                        }

                        if ($this.is('.italsoft-table--button-del')) {
                            for (var i in tableData) {
                                tableData[i].unshift(itafrontoffice_table_get_delgridrow_button($this, i));
                            }
                        }

                        return tableData;
                    };

                    datapagerOptions.ajaxObject = {
                        type: 'POST',
                        data: jsonData,
                        beforeSend: function () {
                            $('body').addClass('italsoft-loading');
                            var ajaxData = '';

                            if ($form.length) {
                                var formData = $form.serializeArray();

                                $.map(formData, function (n, i) {
                                    if (['action', 'model', 'data', 'event'].indexOf(n['name']) > -1) {
                                        return;
                                    }

                                    ajaxData += '&' + [n['name']] + '=' + encodeURIComponent(n['value']);
                                });
                            }

                            if (options.widgets.indexOf('filter') > -1) {
                                $this.find('.tablesorter-filter').each(function () {
                                    ajaxData += '&' + this.id + '=' + encodeURIComponent(this.value);
                                });
                            }

                            this.data += ajaxData;
                        },
                        complete: function () {
                            $('body').removeClass('italsoft-loading');

                            $thead.each(function () {
                                var n = this.cellIndex + 1;
                                $this.find('tbody > tr > *:nth-child(' + n + ')').attr('data-title', this.innerText);
                            });

                            if ($this.is('[data-selectable]')) {
                                italsoft_table_selectable($this);
                            }

                            itafrontoffice_parse($this.find('tbody'));
                        }
                    };
                }

                $this.after($pager);
                $this.tablesorterPager(datapagerOptions);
            }

            if (options.widgets.indexOf('filter') > -1) {
                $this.find('th.ita-hidden-cell, td.ita-hidden-cell').each(function () {
                    $this.find('tr.tablesorter-filter-row td').eq($(this).index()).addClass('ita-hidden-cell');
                });
            }
        });

        $element.find('.italsoft-input--datepicker').not('[readonly], [disabled]').each(function () {
            $(this)
                .datepicker({
                    changeYear: true,
                    changeMonth: true,
                    dateFormat: 'dd/mm/yy',
                    showOn: 'button',
                    showAnim: 'slideDown',
                    onSelect: function () {
                        $(this).focus();
                    },
                    yearRange: "-100:+10"
                })
                .next('.ui-datepicker-trigger')
                .html('<i class="icon ion-android-calendar italsoft-icon"></i>');

            $(this).mask('99/99/9999');
        });

        function itaCurrencyFormat(number, precision, dec_sep, tho_sep) {
            precision = Math.abs(parseInt(precision));
            number = number.replace(/^([+-])?0+(?![.,]|$)/, '$1').replace(/[^0-9.,-]/g, '');
            var s = (number.length ? number : '0').split(/,|\./);

            while (s.length < 2) {
                s.push('');
            }

            if (!s[0].match(/\d/)) {
                s[0] += '0';
            }

            var m = s.splice(-1, 1)[0];

            while (m.length < precision) {
                m += '0';
            }

            return s.join('').replace(/\B(?=(\d{3})+(?!\d))/g, tho_sep) + (precision > 0 ? dec_sep + m.substr(0, precision) : '')
        }

        $element.find('.italsoft-input--currency').each(function () {
            var regExpCurrency, currInput = this, metadata = $(this).metadata(), formatterOptions = {
                precision: 2,
                decimal: ',',
                thousand: '.',
                prefix: '',
                suffix: ''
            };

            if (metadata.formatterOptions) {
                for (var k in metadata.formatterOptions) {
                    formatterOptions[k] = metadata.formatterOptions[k];
                }
            }

            if (parseInt(formatterOptions.precision) == 0) {
                regExpCurrency = /^([+-])?(\d*)/;
            } else {
                regExpCurrency = new RegExp("^([+-])?(?:(\\d*)(\\.|,)(\\d{0," + Math.abs(parseInt(formatterOptions.precision)) + "})|(\\d*))");
            }

            $(this).css('text-align', 'right').keypress(function (e) {
                var resultString = e.target.value.substr(0, e.target.selectionStart) + e.key + e.target.value.substr(e.target.selectionEnd);

                if (!e.altKey && !e.ctrlKey && e.key.length === 1 && regExpCurrency.exec(resultString)[0] !== resultString) {
                    e.preventDefault();
                }
            }).blur(function (e) {
                if ($(this).hasClass('currency-display')) {
                    return;
                }

                var val = parseFloat(this.value.replace(',', '.'));
                if (isNaN(val)) {
                    val = 0;
                }
                val = val.toFixed(formatterOptions.precision);

                this.setAttribute('data-ita-value', val);
                this.value = formatterOptions.prefix + itaCurrencyFormat(this.value, formatterOptions.precision, formatterOptions.decimal, formatterOptions.thousand) + formatterOptions.suffix;

                $(this).addClass('currency-display');
            }).focus(function (e) {
                if (!$(this).hasClass('currency-display') || $(this).hasClass('ita-readonly')) {
                    return;
                }

                this.value = this.getAttribute('data-ita-value');
                this.setSelectionRange(0, this.value.length);
                this.setAttribute('data-ita-value', '');

                $(this).removeClass('currency-display');
            });

            if (this.value) {
                $(this).trigger('blur');
            }

            if (this.form) {
                $(this.form).on('submit', function () {
                    currInput.value = currInput.dataset.itaValue ? currInput.dataset.itaValue : currInput.value.replace(',', '.');
                });
            }
        });

        $element.find('.italsoft-form[data-ajax]').each(function () {
            var $this = $(this);

            $this.on('submit', function () {
                itaFrontOffice.ajax(ajax.action, this.dataset.ajax ? this.dataset.ajax : ajax.model, 'onSubmit', this);

                $this.parents('.ui-dialog-content').dialog('destroy');
                return false;
            });
        });

        $element.find('.italsoft-tabs').each(function () {
            $(this).tabs({
                classes: {
                    'ui-tabs': 'noclass',
                    'ui-tabs-nav': 'noclass',
                    'ui-tabs-tab': 'noclass',
                    'ui-tabs-panel': 'noclass'
                }
            });
        });

        $element.find('.italsoft-highchart-pie').each(function () {
            var metadata = $(this).metadata(),
                uid = Math.random().toString(36).substr(2, 10),
                chartData = new Array(),
                chartTotal = 0;

            $(this).after('<div id="' + uid + '"></div>');

            /*
             * Elaborazione dati
             */

            var columns = $(this).find('tbody td:nth-child(1)').map(function () {
                return $(this).text();
            });

            var values = $(this).find('tbody td:nth-child(2)').map(function () {
                var v = parseInt($(this).text().replace(/\./g, ''));
                chartTotal += v;
                return v;
            });

            for (var i = 0; i < values.length; i++) {
                chartData[i] = new Array(2);
                chartData[i][0] = columns[i];
                chartData[i][1] = parseFloat((100 * values[i] / chartTotal).toFixed(2));
            }

            new Highcharts.Chart({
                chart: {
                    renderTo: uid,
                    plotBackgroundColor: null,
                    plotBorderWidth: null,
                    plotShadow: false,
                    spacingLeft: 50,
                    spacingRight: 50
                },
                title: {
                    text: metadata.caption
                },
                tooltip: {
                    formatter: function () {
                        return '<b>' + this.point.name + '</b>: ' + this.point.y + ' %';
                    }
                },
                plotOptions: {
                    pie: {
                        allowPointSelect: true,
                        cursor: 'pointer',
                        dataLabels: {
                            connectorPadding: 0,
                            softConnector: false,
                            enabled: true,
                            color: '#000000',
                            connectorColor: '#333333',
                            formatter: function () {
                                return this.point.y + '% ' + this.point.name;
                            }
                        }
                    }
                },
                series: [{
                        type: 'pie',
                        name: 'nome',
                        data: chartData,
                        dataLabels: {
                            enabled: true,
                            padding: 0
                        }
                    }]
            });

            $(this).remove();
        });

        $element.find('.italsoft-button-ricerca').click(function () {
            var $divRicerca = $("#" + this.dataset.ricerca), raccolta = this.dataset.raccolta ? this.dataset.raccolta : '';

            $divRicerca.find('.italsoft-table').attr('data-raccolta', raccolta);

            $divRicerca.show().dialog({
                modal: true,
                title: this.title,
                width: $(window).width() - 100,
                height: $(window).height() - 100,
                draggable: false,
                resizable: false,
                close: function (e, ui) {
                    $divRicerca.find('.italsoft-table').removeAttr('data-raccolta');
                }
            }).css('padding', 0).parent().css({position: 'fixed', top: '50px', left: '50px'});
        });

        $element.find('.italsoft-ajax-onchange').on('change', function () {
            var fieldId = this.id.substr(0, 9) === 'raccolta_' ? this.id.substr(9) : this.id;
            fieldId = this.id.substr(0, 9) === 'raccolta[' ? this.id.slice(9, -1) : this.id;

            itafrontoffice_ajax(ajax.action, ajax.model, 'onChange', this, {id: fieldId});
        });

        $element.find('.italsoft-input--hidden').each(function () {
            $(this).removeClass('italsoft-input--hidden').closest('.ita-field').hide();
        });

        $element.find('.italsoft-tooltip, abbr').each(function () {
            $(this).tooltip({
                classes: {
                    'ui-tooltip': 'italsoft-tooltip-content'
                },
                content: function () {
                    return this.getAttribute('title');
                },
                show: {effect: 'fade', duration: 150},
                hide: {effect: 'fade', duration: 350},
                position: {my: 'center bottom-10', at: 'center top'},
                close: function (event, ui) {
                    $('.ui-helper-hidden-accessible').remove();
                }});
        });

        $element.find('.italsoft-tooltip--click').each(function () {
            if (!this.getAttribute('title')) {
                this.setAttribute('title', $(this).data('title'));
            }

            $(this).tooltip({
                classes: {
                    'ui-tooltip': 'italsoft-tooltip-content'
                },
                content: function () {
                    return this.getAttribute('title');
                },
                show: {effect: 'fade', duration: 150},
                hide: {effect: 'fade', duration: 350},
                position: {my: 'center bottom-10', at: 'center top'},
                close: function (e, ui) {
                    if ($(this).is('.italsoft-tooltip--active')) {
                        $(this).tooltip('enable').tooltip('open');
                        return false;
                    }

                    $('.ui-helper-hidden-accessible').remove();
                }
            });

            $(this).tooltip('disable');

            $(this).on('click', function () {
                if ($(this).is('.italsoft-tooltip--active')) {
                    $(this).removeClass('italsoft-tooltip--active').tooltip('disable');
                } else {
                    $('.italsoft-tooltip--active').removeClass('italsoft-tooltip--active').tooltip('disable');
                    $(this).addClass('italsoft-tooltip--active').tooltip('enable').tooltip('open').off('pointerout pointerleave mouseout mouseleave');
                }
            }).on('remove', function () {
                $('#' + this.attributes['aria-describedby'].value).remove();
            });
        });

        $element.find('.italsoft-uploader').each(function () {
            var $uploader = $(this),
                metaData = $uploader.metadata();

            var $form = $(this).parents('form').length ? $(this).parents('form') : false,
                multipart_params = {};

            if ($form) {
                var formData = $form.serializeArray();
                multipart_params = getQueryParams(window.location.href);

                $.map(formData, function (n, i) {
                    multipart_params[n['name']] = n['value'];
                });
            }

            multipart_params.action = ajax.action;
            multipart_params.model = ajax.model;
            multipart_params.data = ajax.data;
            multipart_params.event = 'fileUpload';

            var redirectURI = false;

            var pluploadOptions = {
                runtimes: 'html5',
                url: ajax.url,
                multipart_params: multipart_params,
                chunk_size: '1mb',
                rename: false,
                dragdrop: true,
                init: {
                    PostInit: function () {
                        $(".plupload_add").each(function () {
                            $(this).prepend("<span class=\"ion-icon ion-plus\" style=\"margin-right: 5px;\"></span>");
                            $(this).removeClass().addClass("italsoft-button italsoft-button--inline");
                        });

                        $(".plupload_start").each(function () {
                            $(this).prepend("<span class=\"ion-icon ion-arrow-up-a\" style=\"margin-right: 5px;\"></span>");
                            $(this).removeClass().addClass("italsoft-button italsoft-button--inline");
                            $(this).css("margin", "0 15px 0 5px");
                        });
                    },
                    BeforeUpload: function (uploader, file) {
                        uploader.settings.multipart_params.queue_index = uploader.total.uploaded;

                        var fileTopPosition = $('#' + file.id).position().top;
                        $uploader.find('.plupload_filelist').animate({scrollTop: fileTopPosition});

                        $('body').addClass('italsoft-loading');
                    },
                    FileUploaded: function (up, file, info) {
                        var response = {};

                        try {
                            response = JSON.parse(info.response);
                        } catch (e) {
                            console.log(e);
                            alert(e.message);
                        }

                        if (!response || response.error) {
                            file.status = plupload.FAILED;
                        }

                        if (response.redirect) {
                            redirectURI = response.redirect;
                        }
                    },
                    UploadComplete: function () {
                        if (redirectURI) {
                            window.location.href = redirectURI;
                        } else {
                            $('body').removeClass('italsoft-loading');
                        }
                    }
                }
            };

            if (metaData) {
                for (var key in metaData) {
                    pluploadOptions[key] = metaData[key];
                }
            }

            $(this).pluploadQueue(pluploadOptions);
        });

        $element.find('.ita-datepicker').not('[readonly],[disabled]').each(function () {
            $(this)
                .datepicker({
                    changeYear: true,
                    changeMonth: true,
                    dateFormat: 'dd/mm/yy',
                    showOn: "button",
                    showAnim: 'slideDown',
                    onSelect: function () {
                        $(this).focus();
                    },
                    yearRange: "-100:+10"

                })
                .next('.ui-datepicker-trigger')
                .html('<i class="icon ion-android-calendar italsoft-icon"></i>');

            $(this).mask("99/99/9999");
        });

        $element.find('.ita-time').each(function () {
            var time = $(this).val();
            var splittedTime = time.split(':');
            if (typeof (splittedTime[0]) != 'undefined' && splittedTime[1] != 'undefined') {
                $(this).val(splittedTime[0] + ":" + splittedTime[1]);
            }
            $(this).mask("99:99");
        });

        $element.find('.ita-form-submit').each(function () {
            var currentButton = this;
            $(this).click(function (e) {
                var $myform = $(this).closest('form');
                var $buttonValue = $();
                var $disabledFields = $myform.find('[disabled]');
                $disabledFields.removeAttr('disabled');

                if (currentButton.name && currentButton.value) {
                    $buttonValue = $('<input type="hidden" name="' + currentButton.name + '" value="' + currentButton.value + '"/> ');
                    $myform.append($buttonValue);
                }

                $myform.find('input:checkbox').each(function () {
                    if ($(this).attr('checked')) {
                        $(this).attr('value', '1').attr('checked', true);
                    } else {
                        $(this).attr('value', '0').attr('checked', true);
                    }
                });

                $myform.submit();

                $myform.find('input:checkbox').each(function () {
                    if ($(this).attr('value') == 0) {
                        $(this).prop('checked', false);
                    }
                });

                $disabledFields.attr('disabled', 'disabled');

                $buttonValue.remove();

                e.preventDefault();
            });
        });

        $element.find('input[type="file"].italsoft-dragndrop-upload').each(function () {
            if (!isAdvancedUpload) {
                return;
            }

            var $input = $(this),
                $container = $('body'),
                $divDragover = $('<div style="pointer-events: none; opacity: 0; transition: opacity .35s; z-index: 100; position: fixed; top: 0; left: 0; bottom: 0; right: 0; display: flex; align-items: center; justify-content: center; background-color: rgba(0, 0, 0, .6); border-radius: 5px; color: #fff;"><h1><i class="ion-icon ion-arrow-down-a" style="margin-right: 15px;"></i> Rilascia per caricare il file</h1></div>').prependTo($container);

            $container.on('drag dragstart dragend dragover dragenter dragleave drop', function (e) {
                e.preventDefault();
                e.stopPropagation();
            }).on('dragover dragenter', function () {
                $divDragover.css('opacity', '1');
            }).on('dragleave dragend drop', function () {
                $divDragover.css('opacity', '0');
            }).on('drop', function (e) {
                $input
                    .prop('files', e.originalEvent.dataTransfer.files)
                    .closest('form')
                    .submit();
            });
        });
    }

    function itafrontoffice_ajax(action, model, event, obj, extra, callback) {
        var $form = $(obj).is('form') ? $(obj) : ($(obj).parents('form').length ? $(obj).parents('form') : false),
            data = {};

        if ($form) {
            var formData = $form.serializeArray();
            data = getQueryParams(window.location.href);

            $.map(formData, function (n, i) {
                data[n['name']] = n['value'];
            });
        }

        jQuery.extend(data, (typeof extra === 'object') && !(extra instanceof Array) ? extra : {});

        data.action = action;
        data.model = model;
        data.event = event;

        if (ajax.data) {
            data.data = ajax.data;
        }

        $('body').addClass('italsoft-loading');

        $.ajax({
            url: ajax.url,
            type: 'POST',
            data: data,
            beforeSend: function () {
            },
            success: function (result) {
                try {
                    var json = JSON.parse(result);

                    if (typeof callback === 'function') {
                        return callback(json);
                    }

                    itafrontoffice_parse_ajax_response(json);
                } catch (e) {

                }
            },
            complete: function (jqXHR, textStatus) {
                $('body').removeClass('italsoft-loading');
            }
        });
    }

    function itafrontoffice_parse_ajax_response(json) {
        try {
            /*
             * Verifico subito i comandi per output HTML
             */

            if (typeof json['closeCurrentDialog'] != 'undefined') {
                for (var k in json['closeCurrentDialog']) {
                    var $el = $('body > .ui-dialog').last().children('.ui-dialog-content.ui-widget-content');
                    $el.dialog('destroy');
                }

                delete json['closeCurrentDialog'];
            }

            if (typeof json['dialog'] != 'undefined') {
                if (json['dialog'] && typeof json['dialog']['html'] != 'undefined' && json['dialog']['html']) {
                    var params = {
                        modal: true,
                        resizable: false,
                        close: function () {
                            $(this).dialog('destroy');
                        }
                    };

                    for (var pk in json['dialog']) {
                        if (pk !== 'html') {
                            params[pk] = json['dialog'][pk];
                        }
                    }

                    itafrontoffice_parse($('<div>' + json['dialog']['html'] + '</div>').dialog(params));
                }

                delete json['dialog'];
            }

            if (typeof json['html'] != 'undefined') {
                if (typeof json['html'] === 'string') {
                    itafrontoffice_parse($('.page .page-content').html(json['html']));
                    $(document).scrollTop(0);
                } else {
                    var $html, selector;
                    for (var k in json['html']) {
                        selector = typeof json['html'][k]['id'] != 'undefined' && json['html'][k]['id'] ? '#' + protSelector(json['html'][k]['id']) : '.page .page-content';
                        $html = $('<div></div>').html(json['html'][k]['html']);
                        itafrontoffice_parse($html);
                        switch (json['html'][k]['method']) {
                            default:
                                $(selector).html($html);
                                break;

                            case 'append':
                                $(selector).append($html);
                                break;

                            case 'prepend':
                                $(selector).prepend($html);
                                break;
                        }

                        if (
                            (typeof json['html'][k]['id'] == 'undefined' || !json['html'][k]['id']) &&
                            (typeof json['html'][k]['method'] == 'undefined' || json['html'][k]['method'] != 'append')
                            ) {
                            $(document).scrollTop(0);
                        }
                    }
                }

                delete json['html'];
            }

            for (var key in json) {
                var value = json[key];

                switch (key) {
                    case 'values':
                        var current = document.activeElement;

                        for (var k in value) {
                            var el = document.querySelector('[name="' + k + '"]');
                            if (el.type === 'checkbox') {
                                el.checked = value[k] == '1' || (typeof value[k] == 'string' && value[k].toLowerCase() == 'on') ? true : false;
                            } else {
                                el.value = value[k];
                                if ($(el).is('.italsoft-input--currency')) {
                                    $(el).removeClass('currency-display').trigger('blur');
                                }
                            }
                        }

                        $(current).focus();
                        break;

                    case 'focus':
                        $(document.querySelector('[name="' + value + '"]')).focus();
                        break;

                    case 'redirect':
                        window.location.replace(value);
                        break;

                    case 'show':
                        for (var k in value) {
                            var $el = $('#' + protSelector(value[k]));
                            ($el.closest('.ita-field').length ? $el.closest('.ita-field') : $el).show();
                        }
                        break;

                    case 'hide':
                        for (var k in value) {
                            var $el = $('#' + protSelector(value[k]));
                            ($el.closest('.ita-field').length ? $el.closest('.ita-field') : $el).hide();
                        }
                        break;

                    case 'enableField':
                        for (var k in value) {
                            var $el = $('#' + protSelector(value[k])).length ? $('#' + protSelector(value[k])) : $('[name="' + protSelector(value[k]) + '"]');
                            $el.removeAttr('readonly').removeClass('ita-readonly');
                            if ($el.is('select, input[type="checkbox"], input[type="radio"]')) {
                                $el.removeAttr('disabled');
                            }
                        }
                        break;

                    case 'disableField':
                        for (var k in value) {
                            var $el = $('#' + protSelector(value[k])).length ? $('#' + protSelector(value[k])) : $('[name="' + protSelector(value[k]) + '"]');
                            $el.attr('readonly', 'readonly').addClass('ita-readonly');
                            if ($el.is('select, input[type="checkbox"], input[type="radio"]')) {
                                $el.attr('disabled', 'disabled');
                            }
                        }
                        break;

                    case 'tableData':
                        for (var k in value) {
                            var $table = $('#' + protSelector(k));

                            $table.find('tbody tr').remove();
                            for (var i in value[k]) {
                                var record = value[k][i],
                                    trRecord = [];

                                /*
                                 * Verifica per supporto a record associativi
                                 */

                                if (!$.isArray(record)) {
                                    for (var x in record) {
                                        var nth = $table.find('th[data-key="' + x + '"]').index();
                                        if (nth >= 0) {
                                            trRecord[nth] = record[x];
                                        }
                                    }
                                } else {
                                    trRecord = record;
                                }

                                $tr = $('<tr data-key="' + i + '"></tr>');
                                for (var j in trRecord) {
                                    $tr.append('<td>' + trRecord[j] + '</td>');
                                }

                                if ($table.is('.italsoft-table--button-edit')) {
                                    $tr.prepend($('<td></td>').append(itafrontoffice_table_get_editgridrow_button($table, i)));
                                }

                                if ($table.is('.italsoft-table--button-del')) {
                                    $tr.prepend($('<td></td>').append(itafrontoffice_table_get_delgridrow_button($table, i)));
                                }

                                $table.find('tbody').append($tr);
                            }

                            $table.trigger('update');
                        }
                        break;
                }
            }
        } catch (e) {

        }
    }

    return {
        ajax: itafrontoffice_ajax,
        parse: itafrontoffice_parse,
        clear: clear_form_data,
        ajaxParseResponse: itafrontoffice_parse_ajax_response
    };
})(jQuery);

jQuery(document).ready(function () {
    itaFrontOffice.parse($('body'));
});
