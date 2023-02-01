/**
 *  itaMobile JavaScript framework
 *  Versione: 1.0 - 28.11.2014
 *  (c) 2014 Italsoft SRL
 *  
 *
 *  itaMobile is freely distributable under the terms of ...............
 *  For details, see the Italsoft web site: http://italsoft-mc.it
 * 
 *--------------------------------------------------------------------------*/

var serverURL = typeof itaServerUri !== 'undefined' ? itaServerUri : '.';
var urlController = serverURL + '/controller.php';
var token = '';
var tmpToken = '';

var urlPluploader = serverURL + '/plupload.php';
var uploaders_pl = [];

var ita_silentClose = false;
var enableBlockMsg = true;

var dialogShortCutMap = [];
var dialogLightBoxOpt = {};
var dialogLastFocus = [];
var currDialogFocus = '';
var dialogLayoutStack = [];
var dialogChain = [];

var gridScrollLock = [];
var gridParams = [];

var scrollBarWidth = 8;
var clientEngine = 'itaMobile';
var onMobile = document.location.protocol == 'file:' ? true : false;

var itaMobile = {
    Version: '1.0',
    require: function (libraryName) {
        // inserting via DOM fails in Safari 2.0, so brute force approach
        document.write('<script type="text/javascript" src="' + libraryName + '"><\/script>');
    },
    plUploaders: []
};

var desktopContext = false;

/* jQuery Mobile Panel scrollToTop Fix */
$(function () {
    $.mobile.panel.prototype._positionPanel = function () {
        var self = this,
            i = self._panelInner.outerHeight(),
            e = i > $.mobile.getScreenHeight();
        if (e || !self.options.positionFixed) {
            if (e) {
                self._unfixPanel();
                $.mobile.resetActivePageHeight(i);
            }
        } else {
            self._fixPanel();
        }
    };
});

//itaMobile.load();
$.urlParam = function (name) {
    var results = new RegExp('[\?&]' + name + '=([^&#]*)').exec(window.location.href);
    return (results == null ? null : results[1] || 0);
};

/* global functions */

var server = {
    active: false,
    url: serverURL + '/tmp/mobileDebug.php',
    log: function (str) {
        if (!this.active)
            return true;
        try {
            for (var i = 1; i < arguments.length; i++)
                str += ' ' + arguments[i];
            $.post(this.url, {
                str: encodeURI(str),
                type: 'log'
            }, function (res) {
            });
        } catch (e) {
        }
    },
    clear: function () {
        if (!this.active)
            return true;
        try {
            $.post(this.url, {
                type: 'clear'
            }, function (res) {
            });
        } catch (e) {
        }
    }
};

function msgBlock(text, modal, timeout) {
    modal = modal ? modal : false;
    timeout = timeout ? timeout : 1000;

    if (onMobile) {
        window.itaCordova.toast({
            message: text
        });
    } else {
        var id = "msgBlockPopup" + (new Date).getTime();

        $('body').append('<div data-role="popup" id="' + id + '" style="background-color: rgba(0,0,0,.7); font-size: .9em; padding: 8px 15px; color: #fff;"><p>' + text + '</p></div>');
        $('#' + id).popup({
            theme: 'none',
            dismissible: !modal,
            history: false,
            afterclose: function () {
                $('#' + id).popup('destroy').remove();
            }
        }).popup('open').fadeOut(0).fadeIn(300, function () {
            setTimeout(function () {
                $('#' + id).fadeOut(300, function () {
                    $(this).popup('close');
                });
            }, timeout);
        });
    }
}

function itaUILoader(method) {
    $loader = $('[data-italoader], .ui-loader');
    switch (method) {
        case 'show':
            if ($loader.length == 0) {
                $('body').prepend('<div data-italoader="true"></div>');
                $loader = $('[data-italoader]');
                $loader.loader().loader('show');
            }
            $loader.css('display', 'block');
            break;

        case 'hide':
            $loader.css('display', 'none');
            break;
    }
}

function itaImplode(leaf, matchTag) {
    return $(leaf).closest(matchTag).get(0);
}

function protSelector(selector) {
    var pa = "\\["; // PROTEGGO LE PARENTESI PER FAR FUNZIONARE I SELETTORI CSS
    var pc = "\\]";
    var newSelector = selector.replace(/\[/g, pa);
    newSelector = newSelector.replace(/\]/g, pc);
    return newSelector;
}

function mobileCambia() {
    if (onMobile) {
        removeDesktop(false);
        startMobile(true);
    } else {
        location.reload();
    }
}

$(function () {
    var test = '';
    if ($.urlParam('test'))
        test = '?test=' + $.urlParam('test');
    urlController += test;

    $.metadata.setType('data', 'data-itametadata');
    $.mobile.ajaxEnabled = false;
    $.mobile.linkBindingEnabled = false;
    $.mobile.hashListeningEnabled = false;
    $.mobile.toolbar.prototype.options.updatePagePadding = false;
    $.mobile.toolbar.prototype.options.hideDuringFocus = "";
    $.mobile.toolbar.prototype.options.tapToggle = false;

    document.addEventListener("deviceready", function () {
        //
    }, false);

//    setTimeout(function () {
//        if ($('#accLogin').length > 0)
//            itaUIDialog('accLogin', true);
//    }, 2000);

    if (!window.BlobBuilder && window.WebKitBlobBuilder)
        window.BlobBuilder = window.WebKitBlobBuilder;

//    server.clear();

    document.addEventListener('backbutton', backButtonHandler, false);

    window.addEventListener('native.keyboardshow', function () {
        $(window).unbind('resize');
        $('[data-role="footer"][data-position="fixed"]').css('display', 'none');
    });

    window.addEventListener('native.keyboardhide', function () {
        $('[data-role="footer"][data-position="fixed"]').css('display', 'block');
    });

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
        }
    });
});

$(window).on('beforeunload', function () {
    if (token != null && token != "") {
        itaGo('ItaCall', '', {
            event: 'onunload',
            asyncCall: false
        });
    }
});

function backButtonHandler(e) {
    if (dialogChain.length > 0) {
        closeUIDialog(dialogChain[dialogChain.length - 1], true);
    } else if ($('[data-role="page"]:visible').length > 0) {
        closeUIApp($('[data-role="page"]:visible').last());
    }
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

    if (istanzaClasse.beforeRequest() == true) {

        if (istanzaClasse.parametri['conferma']) {
            //@TODO DA RE-IMPLEMENTARE
            //            if ($('#dialog').length == 0) {
            //                $('body').append('<div id="dialog" title="Richiesta di conferma">Confermi l\'operazione?</div>');
            //            //$('#dialog').hide();
            //            }
            //            $("#dialog").dialog({
            //                bgiframe: true,
            //                resizable: false,
            //                height: 140,
            //                modal: true,
            //                close: function(event, ui) {
            //                    $("#dialog").remove();
            //                },
            //                buttons: {
            //                    'NO': function() {
            //                        $(this).dialog('close');
            //                    },
            //                    'SI': function() {
            //                        $(this).dialog('close');
            //                        if (parametri['bloccaui'] == true)
            //                            $.blockUI();
            //                        istanzaClasse.sendRequest();
            //                    }
            //                }
            //            });
        } else {
            if (parametri['bloccaui'] == true) {
                itaUILoader('show');
            }
            istanzaClasse.sendRequest();
        }
    }
    return false;
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
        contentType: 'application/x-www-form-urlencoded; charset=ISO-8859-1',
        timeout: istanza.parametri['timeout'],
        beforeSend: function (xhr) {
            //xhr.setRequestHeader("Accept-Charset", "ISO-8859-15");
            xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded; charset=ISO-8859-1");
        },
        error: function (XMLHttpRequest, textStatus) {
            istanza.errore(textStatus);
            if (istanza.parametri['bloccaui'] == true) {
                itaUILoader('hide');
            }
        },
        success: function (risposta) {
            if (istanza.parametri['bloccaui'] == true) {
                itaUILoader('hide');
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
};

ItaBase.prototype.errore = function (codice) {
    alert('Errore:' + codice);
};

ItaBase.prototype.parseRisposta = function (risposta) {
    $('input.ita-datepicker').unmask();
    $('input.ita-date').unmask();
    $('input.ita-time').unmask();
    $('input.ita-month').unmask();

    $(risposta).children('root').children().each(function () {
        var idElemento;
        var tag = this.tagName;

        switch (tag) {

            case 'html':
                if (this.childNodes.length) {
                    elemento = $(this).attr('container');
                    var $container = elemento == 'body' ? $('body') : $('#' + protSelector(elemento));
                    var modo = $(this).attr('modo');
                    switch (modo) {
                        case '':
                            $container.html(this.childNodes[0].nodeValue);
                            break;
                        case 'append':
                            $container.append(this.childNodes[0].nodeValue);
                            break;
                        case 'prepend':
                            $container.prepend(this.childNodes[0].nodeValue);
                            break;
                    }
                    parseHtmlContainer($container, tag);
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
                            $('#' + protSelector(idElemento)).html(this.childNodes[0].nodeValue);
                            break;
                        case 'append':
                            $('#' + protSelector(idElemento)).append(this.childNodes[0].nodeValue);
                            break;
                        case 'prepend':
                            $('#' + protSelector(idElemento)).prepend(this.childNodes[0].nodeValue);
                            break;
                    }
                    parseHtmlContainer(container, tag);
                }
                break;

            case 'container' :
                var idParent = $(this).attr('parent');
                var idContainer = $(this).attr('id');
                var comando = $(this).attr('comando');

                switch (comando) {
                    case 'del':
                        $('#' + idContainer).remove();
                        break;

                    case 'add':
                        $(idParent).append('<div id="' + idContainer + '"></div>');
                        break;

                    case 'enablefields':
                        $(protSelector('#' + idContainer)).find('input, select, textarea').each(function () {
                            if (this.id) {
                                enableField(this.id);
                            }
                        });
                        break;

                    case 'disablefields':
                        $(protSelector('#' + idContainer)).find('input, select, textarea').each(function () {
                            if (this.id) {
                                disableField(this.id);
                            }
                        });
                        break;
                }
                break;


            case 'hide' :
                idElemento = protSelector($(this).attr('id'));
                $element = $("#" + idElemento).hide();

                if ($element.parents('[data-role="navbar"]').length > 0) {
                    if ($element.parent().attr('class') && $element.parent().attr('class').match(/ui-block-/gi))
                        $element.parent().hide();
                    mobileButtonbarReflow($element.parents('.ita-mobile-buttonbar-footer'));
                }
                break;

            case 'show':
                idElemento = protSelector($(this).attr('id'));
                $element = $("#" + idElemento).show();

                if ($element.parents('[data-role="navbar"]').length > 0) {
                    if ($element.parent().attr('class') && $element.parent().attr('class').match(/ui-block-/gi)) {
                        $element.parent().show();
                    }
                    mobileButtonbarReflow($element.parents('.ita-mobile-buttonbar-footer'));
                }

                if ($element.find('.ita-jqGrid').length > 0) {
                    $element.find('.ita-jqGrid').each(function () {
                        itaMobileGridResize($(this));
                    });
                }
                break;

            case 'valori' :
                var nodeVal = '';
                if (this.childNodes.length) {
                    //nodeVal=$.trim(this.childNodes[0].nodeValue);
                    nodeVal = this.childNodes[0].nodeValue;
                }

                idElemento = protSelector($(this).attr('id'));

                if ($('#' + idElemento).is('input:checkbox')) {
                    if (nodeVal == '1') {
                        $('#' + idElemento).prop('checked', true);
                    } else {
                        $('#' + idElemento).prop('checked', false);
                    }
                } else {
                    if ($('#' + idElemento).hasClass('ita-edit-uppercase')) {
                        $('#' + idElemento).val(ita_ucwords(nodeVal, true));
                    } else if ($('#' + idElemento).hasClass('ita-edit-lowercase')) {
                        $('#' + idElemento).val(ita_lcwords(nodeVal, true));
                    } else if ($('#' + idElemento).hasClass('ita-edit-capitalize')) {
                        $('#' + idElemento).val(ita_ucfirst(nodeVal, true));
                    } else {
                        $('#' + idElemento).val(nodeVal);
                    }
                }
                break;

            case 'select' :
                if (this.childNodes.length) {
                    var voption = this.childNodes[0].nodeValue;
                    idElemento = protSelector($(this).attr('id'));
                    var comando = $(this).attr('comando');
                    var returnval = $(this).attr('returnval');
                    var style = $(this).attr('style');

                    if (comando == '0') {
                        $(protSelector("#" + idElemento) + " option[value='" + returnval + "']").remove();
                    } else {
                        var $option = $('<option></option>').appendTo($('#' + idElemento)).val(returnval).html(voption);

                        if ($(this).attr('selected') == '1') {
                            $option.prop('selected', true);
                        }

                        if ($(this).attr('style') !== '') {
                            $option.attr('style', style);
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
                        $('#desktopBody').itaGetChildForms().each(function () { // @FORM FIXED 16.03.15 | 07.10.15
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

            case 'setFocus' :
                if (this.childNodes.length) {
                    idElemento = protSelector($(this).attr('id'));
                    var idForm = protSelector($(this).attr('form'));

                    if (idElemento == '') {
                        idElemento = $("#" + idForm + " input:text:visible:first").attr('id');
                    }

                    if (typeof ($("#" + idElemento).attr('readonly')) !== 'undefined' && $("#" + idElemento).attr('readonly') == 'readonly') {
                        break;
                    }

                    window.setTimeout(function () {
                        $("#" + idElemento).focus();
                        //$("#" + idElemento).attr('autofocus', 'true');
                    }, 50);

                    dialogLastFocus[$("#" + idElemento).parents('.ui-page:first').attr('id')] = idElemento;

                    if (currDialogFocus != $("#" + idElemento).parents('.ui-page:first').attr('id')) {
                        window.setTimeout(function () {
                            $("#" + currDialogFocus).focus();
                        }, 50);
                    }
                }
                break;

            case 'dialog':
                var idDialog = protSelector($(this).attr('id')) + '_wrapper';
                var $dialog = $('#' + idDialog);
                var comando = $(this).attr('comando');
                var current = false;

                switch (comando) {
                    case 'closeCurrent':
                        closeUIDialog(dialogChain[dialogChain.length - 1]);
                        break;

                    case 'close':
                        if ($('#' + protSelector($(this).attr('id'))).hasClass('ita-app')) {
                            closeUIApp(idDialog);
                        } else {
                            closeUIDialog(idDialog);
                        }

                        var currDialogFocus = getCurrDialog();
                        if (currDialogFocus)
                            $("#" + currDialogFocus).focus();

                        if (dialogLastFocus["#" + currDialogFocus]) {
                            $(protSelector("#" + dialogLastFocus["#" + currDialogFocus])).focus();
                        }

                        delete dialogShortCutMap["#" + idDialog];
                        delete dialogLastFocus["#" + idDialog];
                        break;

                        // INUTILIZZATO
                    case 'moveToTop':
                        if (dialogChain.length > 0) {
                            for (var key in dialogChain) {
                                if (dialogChain[key] == idDialog) {
                                    $('#' + dialogChain[dialogChain.length - 1] + '_wrapper').popup('destroy').hide();
                                    dialogChain.splice(key, 1);
                                    dialogChain[dialogChain.length] = idDialog;
                                    itaUIDialog(idDialog, true);
                                }
                            }
                        }
                        break;

                        // INUTILIZZATO
                    case 'setOpt':
                        if (this.childNodes.length) {
                            var optKey = $(this).attr('option');
                            var optValue = this.childNodes[0].nodeValue;
                            $dialog.popup('option', optKey, optValue);
                        }
                        break;
                }

                break;

            case 'attributi' :
                if (this.childNodes.length) {
                    idElemento = protSelector($(this).attr('id'));
                    var tipoAttributo = $(this).attr('attributo');

                    if ($(this).attr('del') == '1') {
                        $('#' + idElemento).removeAttr(tipoAttributo);
                        break;
                    }
                    if ($(this).attr('del') == '0') {
                        //$('#'+idElemento).attr(tipoAttributo,this.childNodes[0].nodeValue);
                        if (tipoAttributo == 'checked') {
                            if (this.childNodes[0].nodeValue == 'checked') {
                                $('#' + idElemento).prop(tipoAttributo, true);
                            } else {
                                $('#' + idElemento).prop(tipoAttributo, false);
                            }
                            break;
                        } else if (tipoAttributo == 'disabled') {
                            if (this.childNodes[0].nodeValue == 'disabled') {
                                $('#' + idElemento).prop(tipoAttributo, true);
                            } else {
                                $('#' + idElemento).prop(tipoAttributo, false);
                            }
                            break;
                        } else if (tipoAttributo == 'selected') {
                            if (this.childNodes[0].nodeValue == '1') {
                                $('#' + idElemento).prop(tipoAttributo, true);
                            } else {
                                $('#' + idElemento).prop(tipoAttributo, false);
                            }
                            break;
                        } else {
                            $('#' + idElemento).attr(tipoAttributo, this.childNodes[0].nodeValue);
                        }

                    }
                }
                break;

            case 'setAppTitle':
            case 'setDialogTitle':
                if (this.childNodes.length) {
                    var idApp = $(this).attr('id');
                    var appTitle = this.childNodes[0].nodeValue;
                    if ($("#" + idApp).hasClass('ita-app')) {
                        $("#" + idApp + '_appTitle').html(appTitle);
                    } else if ($("#" + idApp).hasClass('ita-dialog')) {
                        $("#" + idApp + "_dialogTitle").html(appTitle);
                    }
                }
                break;

            case 'msgBlock' :
                var parent = protSelector($(this).attr('parent'));
                var timeout = $(this).attr('timeout');
                var modal = $(this).attr('modal');
                var nodeValue = this.childNodes[0].nodeValue;

                msgBlock(nodeValue, modal, timeout);
                break;

            case 'field':
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
                    }
                }
                break;

            case 'tabella' :
                if (this.childNodes.length) {
                    var disableSel = true;
                    var idTabella = $(this).attr('id');
                    var comando = $(this).attr('comando');
                    var $grid = $('#' + idTabella);

                    if (!$grid.length || $grid.length == 0)
                        break;

                    var options = $grid.metadata();

                    switch (comando) {
                        case 'show' :
                            break;

                        case 'new':
                            break;

                        case 'setSelection':
                            break;

                        case 'enableSelection':
                        case 'disableSelection':
                            break;

                        case 'setSelectAll':
                            $grid.find('tbody tr').not('#baseRow, .searchToolbar').each(function () {
                                $(this).addClass('ita-grid-selected');
                                if (options.multiselect) {
                                    $(this).find('td:eq(0) input[type="checkbox"]').prop('checked', true);
                                }
                            });

                        case 'setDeselectAll':
                            break;

                        case 'reload':
                            $grid.find('tbody tr').not('#baseRow, .searchToolbar').remove();

                            var rowNum = gridParams[idTabella]['rowNum'];
                            var sidx = gridParams[idTabella]['sidx'];
                            var sord = gridParams[idTabella]['sord'];
                            gridParams[idTabella] = new Array();
                            gridParams[idTabella]['rowNum'] = rowNum;
                            gridParams[idTabella]['page'] = 1;
                            gridParams[idTabella]['sidx'] = sidx;
                            gridParams[idTabella]['sord'] = sord;
                            gridParams[idTabella]['search'] = 'false';
                            gridScrollLock[idTabella] = true;

                            itaMobileGridPostdata($grid);
                            break;

                        case 'setCellValue':
                            var value = this.childNodes[0].nodeValue;
                            var rowid = $(this).attr('rowid');
                            var colname = $(this).attr('colname');
                            var colindex = $grid.find('#' + colname).index();
                            var cellclass = $(this).attr('class').indexOf('{') < 0 ? $(this).attr('class') : eval("(" + $(this).attr('class') + ")");
                            var properties = $(this).attr('properties');
                            var forceup = $(this).attr('forceup');

                            $grid.find('#' + rowid + ' td').eq(colindex).html(value);

//                            console.log('setCellValue ( value, rowid, colname, cellclass, props, forceup ): ', value, rowid, colname, cellclass, properties, forceup);
                            // $("#" + idTabella).jqGrid('setCell', rowid, colname, value, cellclass, properties, forceup);
                            break;

                        case 'setCellFocus':
                            break;

                        case 'addXML':
                            break;

                        case 'addJson':
                            var jsondata = eval("(" + this.childNodes[0].nodeValue + ")");
                            var tds = new Array();
                            var readerId = options.readerId ? options.readerId : 'ROWID';

                            if (!options.fullsize) {
                                $grid.find('tbody tr').not('#baseRow, .searchToolbar').remove();
                            }

                            //							$grid.attr('data-role', 'table').attr('data-mode', 'columntoggle');
                            //							$grid.addClass('ui-body-a table-stripe').find('thead').addClass('ui-bar-d').find('th').attr('data-priority', '1');
                            //							$grid.find('#baseRow').css('display', 'none');
                            //							$grid.table();

                            $grid.find('#baseRow td').each(function () {
                                var id = $(this).attr('id');
                                tds[id] = $(this).metadata();
                                if (!tds[id].formatoptions)
                                    tds[id].formatoptions = {};
                                tds[id].formatoptions.name = id;
                            });

                            for (var rowsKey in jsondata.row) {
                                // if ( gridParams[idTabella]['rowNum'] && gridParams[idTabella]['rowNum'] < parseInt(rowsKey)+1 ) break;
                                var tr = jsondata.row[rowsKey];
                                if ($grid.find('#' + tr[readerId]).length == 0) {
                                    $grid.find('tbody').append('<tr id="' + tr[readerId] + '"></tr>');
                                    for (var key in tds) {
                                        if (options.multiselect && key == 'multiselect') {
                                            var cellValue = '<input type="checkbox" data-role="none">';
                                        } else {
                                            var cellValue = tr[key];
                                        }
                                        var align = tds[key].formatoptions && tds[key].formatoptions.align ? tds[key].formatoptions.align : 'left';
                                        if (tds[key].formatter) {
                                            if (typeof tds[key].formatter == 'function')
                                                cellValue = tds[key].formatter(cellValue, tds[key].formatoptions, tr[readerId]);
                                            else {
                                                cellValue = jqGridFormatter(tds[key].formatter, cellValue, tds[key].formatoptions, tr[readerId]);
                                            }
                                        }
                                        $grid.find('#' + tr[readerId]).append('<td style="text-align: ' + align + ';">' + cellValue + '</td>');
                                    }
                                }
                            }

                            if (!gridParams[idTabella]['rowNum'])
                                gridParams[idTabella]['rowNum'] = parseInt(jsondata.righe);
                            gridParams[idTabella]['page'] = parseInt(jsondata.pagina);
                            gridParams[idTabella]['totPages'] = parseInt(jsondata.pagine);

                            // Mantiene la stessa altezza ( = numero di righe ) per evitare sfasamenti al cambio pagina
                            var tdshtml = '';
                            for (var i = 0; i < Object.keys(tds).length; i++)
                                tdshtml += '<td>&nbsp;</td>';

                            if (gridParams[idTabella]['totPages'] > 1)
                                while (false || $grid.find('tbody tr').not('#baseRow, .searchToolbar').length < gridParams[idTabella]['rowNum']) {
                                    $grid.find('tbody').append('<tr>' + tdshtml + '</tr>');
                                }

                            var thisPageFirst = (gridParams[idTabella]['rowNum'] * (gridParams[idTabella]['page'] - 1) + 1);
                            var thisPageLast = (thisPageFirst - 1 + gridParams[idTabella]['rowNum'] > jsondata.righe ? jsondata.righe : thisPageFirst - 1 + gridParams[idTabella]['rowNum']);
                            var txt = 'Visualizzati ' + thisPageFirst + ' - ' + thisPageLast + ' di ' + jsondata.righe;
                            txt = gridParams[idTabella]['page'] + ' / ' + gridParams[idTabella]['totPages'];
                            $('#' + idTabella + '_tfoot_text').html(txt);

                            $grid.find('.ita-jqg-editcheckbox').unbind('click').on('click', function (e) {
                                e.stopPropagation();
                                var cellname = $grid.find('#baseRow td').eq($(this).parents('td').index()).attr('id');
                                var value = $(this).prop('checked') ? '1' : '0';
                                itaGo('ItaForm', $grid, {
                                    event: 'afterSaveCell',
                                    validate: true,
                                    rowid: $(this).parents('tr').attr('id'),
                                    cellname: cellname,
                                    value: value
                                });
                            });

                            gridScrollLock[idTabella] = false;

                            try {
                                $grid.table('refresh');
                            } catch (e) {
                            }
                            break;

                        case 'add':
                            var jsondata = eval("(" + this.childNodes[0].nodeValue + ")");
                            var tds = new Array();
                            var rowid = $(this).attr('rowid');
                            var pos = $(this).attr('position');
                            var ref = $(this).attr('reference');

                            $grid.find('#baseRow td').each(function () {
                                var id = $(this).attr('id');
                                tds[id] = $(this).metadata();

                                if (!tds[id].formatoptions)
                                    tds[id].formatoptions = {};

                                tds[id].formatoptions.name = id;
                            });

                            var tr = jsondata;
                            if ($grid.find('#' + rowid).length == 0) {
                                switch (pos) {
                                    case 'first':
                                        $grid.find('tbody').prepend('<tr id="' + rowid + '"></tr>');
                                        break;

                                    case 'last':
                                        $grid.find('tbody').append('<tr id="' + rowid + '"></tr>');
                                        break;

                                    case 'after':
                                        $grid.find('tbody #' + ref).after('<tr id="' + rowid + '"></tr>');
                                        break;

                                    case 'before':
                                        $grid.find('tbody #' + ref).before('<tr id="' + rowid + '"></tr>');
                                        break;
                                }

                                for (var key in tds) {
                                    if (options.multiselect && key == 'multiselect') {
                                        var cellValue = '<input type="checkbox" data-role="none">';
                                    } else {
                                        var cellValue = tr[key];
                                    }
                                    var align = tds[key].formatoptions && tds[key].formatoptions.align ? tds[key].formatoptions.align : 'left';
                                    if (tds[key].formatter) {
                                        if (typeof tds[key].formatter == 'function')
                                            cellValue = tds[key].formatter(cellValue, tds[key].formatoptions, rowid);
                                        else {
                                            cellValue = jqGridFormatter(tds[key].formatter, cellValue, tds[key].formatoptions, rowid);
                                        }
                                    }
                                    $grid.find('#' + rowid).append('<td style="text-align: ' + align + ';">' + cellValue + '</td>');
                                }
                            }

                            $grid.find('.ita-jqg-editcheckbox').unbind('click').on('click', function (e) {
                                e.stopPropagation();
                                var cellname = $grid.find('#baseRow td').eq($(this).parents('td').index()).attr('id');
                                var value = $(this).prop('checked') ? '1' : '0';
                                itaGo('ItaForm', $grid, {
                                    event: 'afterSaveCell',
                                    validate: true,
                                    rowid: $(this).parents('tr').attr('id'),
                                    cellname: cellname,
                                    value: value
                                });
                            });

                            gridScrollLock[idTabella] = false;

                            try {
                                $grid.table('refresh');
                            } catch (e) {
                            }
                            break;

                        case 'del':
                            var rowid = $(this).attr('idRiga');

                            if (!rowid) {
                                $grid.find('tbody tr').not('#baseRow, .searchToolbar').remove();
                            } else {
                                $grid.find('tbody tr#' + rowid).remove();
                            }

                            try {
                                $grid.table('refresh');
                            } catch (e) {
                            }
                            break;

                        case 'upd':
                            break;

                        case 'html':
                            break;
                    }
                }
                break;

            case 'clearFields' :
                if (this.childNodes.length) {
                    var idForm = protSelector($(this).attr('form'));
                    var idContainer = protSelector($(this).attr('container'));
                    if (idContainer !== '') {
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
                    });
                }
                break;

            case 'openDocument' :
                if (this.childNodes.length) {
                    var print = $(this).attr('stampa');
                    var uri = encodeURI(serverURL + '/' + this.childNodes[0].nodeValue);
                    var newwindow;

                    if (onMobile) {
                        var fileURL = cordova.file.externalApplicationStorageDirectory + 'tmp.pdf';
                        var fileTransfer = new FileTransfer();

                        fileTransfer.download(uri, fileURL, function (entry) {

                            if (print == true) {
                                window.itaCordova.intent({
                                    package: 'com.dynamixsoftware.printershare',
                                    url: entry.toURL(),
                                    type: 'application/pdf'
                                }, function () {
                                }, function () {
                                    msgBlock('Errore nell\'avvio della stampa su Printer Share');
                                });

                            } else {
                                window.itaCordova.intent({url: entry.toURL(), type: 'application/pdf'});
                            }

                        }, function (e) {
                            msgBlock('Errore nel download del file');
                        }, true);

                    } else {
                        if (print == true) {
                            alert('Impossibile avviare la stampa da browser. Procedere manualmente a fine download.');
                        }

                        newwindow = window.open(uri, '_blank');

                        if (!newwindow) {
                            newwindow = window.open(uri, '_self');
                        }
                    }
                    break;
                }
                break;

            case 'classi' :
                if (this.childNodes.length) {
                    idElemento = $(this).attr('id');
                    var comando = $(this).attr('comando');
                    switch (comando) {
                        case 'add':
                            $(protSelector('#' + idElemento)).addClass(this.childNodes[0].nodeValue);
//                            if (this.childNodes[0].nodeValue == 'datepicker')
//                                $('#xx' + idElemento).datepicker({
//                                    changeYear: true,
//                                    changeMonth: true
//                                });
                            break;
                        case 'del':
                            $(protSelector('#' + idElemento)).removeClass(this.childNodes[0].nodeValue)
                            break;
                    }
                }
                break;

            case 'codice' :
                if (this.childNodes.length) {
                    eval(this.childNodes[0].nodeValue);
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
                                autogenerate_stylesheet: false,
                                widget_selector: 'div',
                                widget_margins: [5, 5],
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

    parseDateTime();

    $('.ui-checkbox input[type="checkbox"]').each(function () {
        $(this).checkboxradio('refresh');
    });

    $('select.ita-select').each(function () {
        $(this).selectmenu('refresh');
    });

    $('.ui-fixed-hidden').toolbar('option', 'tapToggle', false);

    $('input.ita-datepicker').mask("99/99/9999");
    $('input.ita-date').mask("99/99/9999");
    $('input.ita-time').mask("99:99");
    $('input.ita-month').mask("99/9999");

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

    var myForm;
    var myId;
    if (this.elemento == "") {
        myForm = $("#" + this.parametri['model']);
        delete this.parametri['model'];
        myId = this.parametri['id'];
        delete this.parametri['id'];
    } else {
        if (typeof (this.parametri.model) == 'undefined') {
            myForm = $(itaImplode($(this.elemento), 'form, div.ita-model'));
        } else {
            myForm = $("#" + this.parametri.model);
        }
        delete this.parametri.model;
        if (typeof (this.parametri.id) == 'undefined') {
            myId = $(this.elemento).attr('id');
        } else {
            myId = this.parametri.id;
        }
    }

    myForm.validate({
        meta: 'rules',
        errorClass: 'ui-state-error ita-state-error',
        errorPlacement: function (error, element) {
            console.log(error, element);
            $textPosition = $('#validateMsg p');
            var label = $(element).parent().prev('label');
            if (label) {
                $textPosition.append($(label).html().trim() + " (" + $(element).attr('id') + ") : ");
            } else {
                $textPosition.append($(element).attr('id') + ": ");
            }
            $textPosition.append('<a id ="errlblfor_' + $(element).attr('id') + '" href="#">' + error.html() + '</a>').append('<br>');
            $(protSelector("#errlblfor_" + $(element).attr('id'))).click(function (e) {
                e.preventDefault();
                $("#validateMsg_wrapper").popup('destroy').remove();
                $(element).focus();
            });
            $(element).addClass('ui-state-error');
            $(element).addClass('ita-state-error');
        },
        invalidHandler: function (form, validator) {
            $(form).find('.ui-state-error.ita-state-error').removeClass('ui-state-error');
            var errors = validator.numberOfInvalids();
            if (errors) {
                var message = 'Ci sono ' + errors + ' Campi con errore. Controllare nella lista sottostante:<br>';

                var header = '<div data-role="header"><h1>Errore di Validazione</h1><button data-rel="back" class="ui-btn-right ui-btn ui-btn-b ui-btn-inline ui-mini ui-corner-all ui-btn-icon-right ui-icon-delete ui-btn-icon-notext"></button></div>'
                var body = '<div class="ui-content" role="main" id="validateMsg"><div class="ui-body ui-corner-all ui-body-b"><p>' + message + '</p></div></div>';

                var $popup = $('<div data-role="popup" id="validateMsg_wrapper">' + header + body + '</div>').appendTo('body');
                $popup.find('button').on('click', function (e) {
                    $popup.popup('destroy').remove();
                });

                $popup.enhanceWithin();
                $popup.popup({
                    dismissible: false,
                    history: false,
                    theme: 'a',
                    positionTo: 'window'
                }).popup('open');
            }
        }
    });

    var ita_event = this.parametri['event'];
    delete this.parametri['event'];

    var ita_validate = this.parametri['validate'];
    if (ita_validate == true) {
        if (!myForm.valid()) {
            return false;
        }
    }

    var model = myForm.eq(0).attr('id');
    if (!model) {
        alert('Model di gestione non indicato!');
        return false;
    }

    $(myForm).find('input:checkbox').each(function () {
        if ($(this).prop('checked')) {
            $(this).attr('value', '1').prop('checked', true);
        } else {
            $(this).attr('value', '0').prop('checked', true);
        }
    });

    var formVal = '';
    var formValArray;
    if (this.parametri['leggiform'] == 'tutto') {
        // formValArray = $(myForm).serializeArray();
        formValArray = serializeForm(myForm.attr('id'));
    } else {
        formValArray = $(myForm).find(protSelector("#" + myId)).serializeArray();
    }

    $.each(formValArray, function (i, v) {
        var campo = $(myForm).find('input[name="' + v['name'] + '"]').eq(0);

        if (campo.attr('id') !== undefined) {
            //
            // QUI CONTROLLO Il CARATTERE +
            //
            if (campo.hasClass('ita-datepicker') || campo.hasClass('ita-date')) {
                if (v['value'] !== '' && v['value'] !== null) {
                    var data = new Date(getDateFromFormat(v['value'], 'dd/MM/yyyy'));
                    formValArray[i]['value'] = formatDate(data, 'yyyyMMdd');
                }
            }

            if (campo.hasClass('ita-month')) {
                if (v['value'] !== '' && v['value'] !== null) {
                    var month = new Date(getDateFromFormat(v['value'], 'MM/yyyy'));
                    formValArray[i]['value'] = formatDate(month, 'yyyyMM');
                }
            }

        }
    });

    for (var i = 0; i < formValArray.length; i++) {
        if (!formValArray[i]['name']) {
            continue;
        }

        var $obj = $('[name="' + formValArray[i]['name'] + '"][value="' + formValArray[i]['value'] + '"]');
        if ($obj.is('[type="radio"]') && !$obj.prop('checked')) {
            continue;
        }

        formValArray[i]['value'] = formValArray[i]['value'].replace(/[\x00-\x08\x0B-\x0C\x0E-\x1F]/g, function (i) {
            return '&#' + i.charCodeAt(0) + ';';
        });
        formVal += "&" + formValArray[i]['name'] + "=" + escape(formValArray[i]['value']).replace(new RegExp("\\+", "g"), "%2B");//escape(formValArray[i]['value']);
    }

    $(myForm).find('input:checkbox').each(function () {
        if ($(this).attr('value') == 0) {
            $(this).prop('checked', false);
        }
    });

    $(myForm).find('.ita-jqGrid-activated').each(function () {
        var $that = $(this);
        var metaData = $that.metadata();
        var gridId = $that.attr('id');

        var ids = $that.find('.ita-grid-selected').map(function () {
            return $(this).attr('id');
        }).get();

        if (ids.length < 1) {
            ids = gridParams[gridId]['selrow'];
        } else {
            ids = ids.join();
        }

        formVal += "&" + gridId + "[gridParam][selarrrow]=" + ids;
        formVal += "&" + gridId + "[gridParam][selrow]=" + gridParams[gridId]['selrow'];
        formVal += "&" + gridId + "[gridParam][rowNum]=" + gridParams[gridId]['rowNum'];
        formVal += "&" + gridId + "[gridParam][page]=" + gridParams[gridId]['page'];

        if (metaData.multiselect) {
            $that.find('tbody > tr').not('#baseRow, .searchToolbar').each(function () {
                var rowid = $(this).attr('id'),
                    value = ($(this).hasClass('ita-grid-selected') ? '1' : '0');
                formVal += "&jqg_" + gridId + "_" + rowid + "=" + value;
            });
        }
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

    delete this.parametri['leggiform'];
    this.url = urlController;
    var callPost = $.param(this.parametri);
    this.post = 'TOKEN=' + token + '&tmpToken=' + tmpToken + '&model=' + model + '&id=' + myId + '&event=' + ita_event + '&' + callPost + '&' + formVal;
    myForm = null;
    myId = null;
    formVal = null;

    return true;

};

function serializeForm(form) {
    form = document.getElementById(form);
    var elems = form.elements;
    var serialized = [], i, len = elems.length;
    for (i = 0; i < len; i++) {
        var element = elems[i];
        var type = element.type;
        var name = element.name;
        var value = element.value;

        switch (type) {
            case 'text':
            case 'tel':
            case 'password':
            case 'radio':
            case 'checkbox':
            case 'textarea':
            case 'select-one':
                serialized.push({name: name, value: value});
                break;

            default:
                break;
        }
    }
    return serialized;
}

function objectToQuery(object, key) {
    var ret = '';
    for (var ind in object) {
        if (typeof object[ind] == 'object') {
            ret += objectToQuery(object[ind], key + '[' + ind + ']');
        } else {
            if (object[ind] !== null && object[ind] !== undefined)
                ret += "&" + key + "[" + ind + "]=" + object[ind];
        }
    }
    return ret;
}

ItaForm.prototype.errore = function (codice) {
    switch (codice) {
        case 'parseError':
            alert('Errore nella risposta del server');
            break;
        default:
            alert('(ItaForm) Form non funzionante: ' + codice);
    }
};

ItaForm.prototype.afterRequest = function (risposta) {

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
    if (typeof (href[1]) !== 'undefined')
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
    //	$('#desktopBody').children('img').remove();
    //	if ($('#desktopBody').find(".ita-buttonbar-wrap").length == 0) {
    //		$('#desktopBody').find(".ita-buttonbar").css('width', 'auto').wrap('<div class="ita-buttonbar-wrap" />');
    //	}
    //	$('#desktopBody .ita-app').children("br").remove();
    //	resizeTabs();
};

ItaApp.prototype.errore = function (codice) {
    $('#desktopBody').children('img').remove();
    $('#desktopBody').append('Applicazione attualmente non attiva (' + codice + ')');
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
    if (typeof (href[1]) !== 'undefined')
        this.post += '&' + href[1];
    $(this.elemento).block();
    //pause(300);
    return true;
};


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
    $("#" + timerParam.element).addClass('ita-timer').addClass('ita-activedTimer').everyTime(timerParam.delay, 'ita-timer', function (i) {
        itaGo('ItaCall', '', {
            bloccaui: false,
            asyncCall: false,
            event: 'ontimer',
            model: timerParam.model,
            id: timerParam.element
        });

    });
}

function parseDateTime() {
    $('input.ita-date, input.ita-datepicker').each(function () {
        var data;
        var ret;
        if (isDate(this.value, 'yyyy-MM-dd')) {
            data = new Date(getDateFromFormat(this.value, 'yyyy-MM-dd'));
            ret = formatDate(data, 'dd/MM/yyyy');
            $(this).addClass('ita-isodate');
            this.value = ret;
        } else if (isDate(this.value, 'yyyyMMdd')) {
            data = new Date(getDateFromFormat(this.value, 'yyyyMMdd'));
            ret = formatDate(data, 'dd/MM/yyyy');
            $(this).addClass('ita-eqdate');
            this.value = ret;
        }
    });

    $('input.ita-month').each(function () {
        var month;
        var ret;
        if (isDate(this.value, 'yyyy-MM')) {
            month = new Date(getDateFromFormat(this.value, 'yyyy-MM'));
            ret = formatDate(month, 'MM/yyyy');
            //$(this).addClass('ita-isodate');
            this.value = ret;
        } else {
            if (isDate(this.value, 'yyyyMM')) {
                month = new Date(getDateFromFormat(this.value, 'yyyyMM'));
                ret = formatDate(month, 'MM/yyyy');
                //$(this).addClass('ita-eqdate');
                this.value = ret;
            }
        }
    });

    $('input.ita-time').each(function () {
        var time = this.value;
        var splittedTime = time.split(':');
        if (typeof (splittedTime[0]) !== 'undefined' && typeof (splittedTime[1]) !== 'undefined') {
            this.value = splittedTime[0] + ":" + splittedTime[1];
        }
    });

}

//
// Implementata con la nuova chiamata getItaLib
//
function itaGetScript(itaFunction, itaScript) {
    if (typeof window[itaFunction] == 'undefined' || itaFunction == '') {
        $.ajaxSetup({
            async: false //////----
        });
        $.getScript('getItaLib.php?script=' + itaScript);
        $.ajaxSetup({
            async: true
        });
    }
}
function pluploadActivate(idElemento) {
    if (itaMobile.plUploaders[idElemento].runtime == '') {

        //		itaMobile.plUploaders[idElemento].bind('PostInit', function(up, params) {
        //			$('#' + itaMobile.plUploaders[idElemento].id + '_' + itaMobile.plUploaders[idElemento].runtime).attr('accept', itaMobile.plUploaders[idElemento].settings.filters.mime_types);
        //		});

        itaMobile.plUploaders[idElemento].init();

        itaMobile.plUploaders[idElemento].bind('FilesAdded', function (up, files) {
            up.start();
            up.refresh();
        });

        itaMobile.plUploaders[idElemento].bind('BeforeUpload', function (up, file) {
            //			$.blockUI({
            //				theme: true, // true to enable jQuery UI support
            //				draggable: true, // draggable option is only supported when jquery UI script is included
            //				title: 'Upload Files', // only used when theme == true
            //				message: '<div id="' + up.settings.browseButton + '_pgbar"></div><img src="./public/css/images/wait.gif" /> UPLOAD in corso...'
            //			});
            //        $('#'+protSelector(up.settings.browseButton+'_pgbar')).progressbar({
            //            value: 37
            //        });
        });

        itaMobile.plUploaders[idElemento].bind('UploadProgress', function (up, file) {
            if (file.percent < 100 && file.percent >= 1) {
                console.log(file.percent);
                //				$('#' + protSelector(up.settings.browseButton + '_pgbar')).progressbar({
                //					value: file.percent
                //				});
            } else {
                $('#' + protSelector(up.settings.browseButton + '_pgbar')).fadeOut(600);
            }
        });

        itaMobile.plUploaders[idElemento].bind('FileUploaded', function (up, file, response) {
            obj = ('#' + protSelector(idElemento));

            var $btn = $('#' + protSelector(idElemento.split('_upld_uploader')[0]));
            var metaData = $btn.length > 0 ? $btn.metadata() : {};
            var objResponse = eval("(" + response.response + ")");

            if (metaData.extraCodeBefore) {
                try {
                    eval(metaData.extraCodeBefore);
                } catch (e) {
                    alert("itaMobile error:\n" + e);
                }
            }

            var data = {
                event: 'onClick',
                file: file.name,
                id: idElemento.split('_uploader')[0],
                validate: false,
                response: objResponse.response
            };

            if (metaData.model) {
                data.model = metaData.model;
            }

            if (metaData.id) {
                data.id = metaData.id;
            }

            if (metaData.event) {
                data.event = metaData.event;
            }

            if (metaData.extraData) {
                data = $.extend(data, metaData.extraData);
            }

            itaGo('ItaForm', obj, data);

            if (metaData.extraCode) {
                eval(metaData.extraCode);
            }

        });
    }
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

    container.find('*').each(function () {
        if ($(this).attr('class')) {
            var params = $(this).attr('class').match(/{.*}/gi);
            if (params) {
                // Metto tra apici i valori di formatter che non ne hanno ( escludo quelli presenti con [^'"] )
                // Per aggiungere la medesima regola ad altre key => (formatter|nuovakey|altrakey)
                // params = params[0].replace(/(formatter):([^'",}]+)/gi, "$1:'$2'");
                $(this).attr('data-itametadata', params);
                $(this).attr('class', $(this).attr('class').replace(/{.*}/gi, ''));
            }
        }
    });

    container.find('.ita-time, .ita-datepicker').each(function () {
        $(this).attr('type', 'tel');
    });

    container.find('.ita-jqGrid').each(function () {
        itaMobileGrid($(this).attr('id'));
    });

    container.find('.ita-mobile-pagecontainer').each(function () {
        var $that = $(this);
        setTimeout(function () {
            $that.pagecontainer().pagecontainer('change', '#' + $that.attr('id'), {
                changeHash: false
            });
        }, 0);
    });

    container.find('.ita-mobile-content').each(function () {
        //@TODO place 
    });

    container.find('.ita-mobile-fixed-external-toolbar').each(function () {
        var $that = $(this);
        $that.toolbar({tapToggle: false});
        setTimeout(function () {
            $that.enhanceWithin();
        }, 0);
    });

    container.find('.ita-box').each(function () {
        var theme = $(this).metadata().jqmtheme ? $(this).metadata().jqmtheme : 'a';
        $(this).removeClass('ita-dialog-body').addClass('ui-body ui-corner-all ui-body-' + theme);
    });

    container.find('.ita-box-error').each(function () {
        $(this).addClass('ui-body ui-corner-all ui-body-a');
    });

    container.find('.ita-box-highlight').each(function () {
        $(this).addClass('ui-body ui-corner-all ui-body-a');
    });

    container.find('.ita-jqmtheme').each(function () {
        $(this).removeClass('ita-jqmtheme').addClass('ui-theme-' + ($(this).metadata().jqmtheme));
    });

    container.find('.ita-list').each(function () {
        var id = $(this).attr('id');
        $(this).removeClass('ita-list').attr('data-role', 'listview').listview();

        $(this).children('li').not('[data-role="list-divider"]').each(function () {
            $(this).on('click', function (e) {
                var data = $(this).metadata();
                e.preventDefault();
                itaGo(data.request ? data.request : 'ItaCall', this, {
                    id: id,
                    model: data.model ? data.model : id.split('_')[0],
                    event: data.event ? data.event : 'liSelect',
                    listId: this.id
                });
            });
        });
    });

    container.find('.ita-field').each(function () {
    });

    container.find('label').each(function () {
        var $that = $(this);
        if ($that.next('input[type="checkbox"]').length > 0) {
            if ($that.hasClass('dx')) {
                // @TODO le checkbox hanno una struttura particolare e non basta un float: right
                // http://demos.jquerymobile.com/1.4.5/checkboxradio-checkbox/ - Paragrafo Icon Position
                // Fix temporaneo: riporta a sx
                $that.removeClass('dx').addClass('sx');
            }
        }
    });

    container.find('.ita-dialog').each(function () {
        if (tag == 'dialogHtml') {
            $(this).removeClass('ita-app');
            $(this).removeClass('ita-dialog');
            $(this).addClass('ita-dialog');
        }
        if (tag == 'appHtml') {
            $(this).removeClass('ita-app');
            $(this).removeClass('ita-dialog');
            $(this).addClass('ita-app');
            $(this).addClass('ita-dialog');
        }
        if (tag == 'innerHtml') {
            $(this).removeClass('ita-app');
            $(this).removeClass('ita-dialog');
        }

        if ($(this).hasClass('ita-app')) {
            itaUIApp($(this).attr('id'));
        } else if ($(this).hasClass('ita-dialog')) {
            itaUIDialog($(this).attr('id'));
        }
    });

    container.find('.ita-button, .ita-button-validate, .ita-button-client').each(function () {
        // LETTURA METADATI
        var metaData = $(this).metadata();
        if (typeof (metaData.id) == 'undefined')
            metaData.id = $(this).attr('id');
        if (typeof (metaData.upload) == 'undefined')
            metaData.upload = false;
        if (typeof (metaData.event) == 'undefined')
            metaData.event = "onClick";
        if (typeof (metaData.request) == 'undefined')
            metaData.request = 'ItaForm';
        if (typeof (metaData.noObj) == 'undefined')
            metaData.noObj = false;

        var $this = $(this);
        var id = $this.attr('id');
        //@TODO DA RIVERIFICARE IN APPROFONDIMENTO REFACTOR    
        //var maxH = 0;

        // RENDERING	
        $this.addClass('ui-btn-inline');
        //$(this).attr('data-inline',true);

        if ($this.parents('.ita-mobile-buttonbar').length == 0) {

            if (typeof ($this.attr('value')) !== 'undefined') {
                $this.css('height', 'auto');

                if (typeof (metaData.iconLeft) !== 'undefined') {
                    $('<div id="' + id + '_icon_left" class="ita-button-element ita-button-icon-left ita-icon ' + metaData.iconLeft + '" style ></div>')
                        .appendTo("#" + id);
                }

                $('<div class="ita-button-element ita-button-text"><div id="' + id + '_lbl" style="height: 100%;" class="ita-button-text-content"></div></div>').appendTo("#" + id);

                if (typeof ($this.attr('value')) !== 'undefined') {
                    $('#' + id + '_lbl').html($this.attr('value'));
                    $this.removeAttr('value');
                }
                if (typeof (metaData.iconRight) !== 'undefined') {
                    $('<div id="' + id + '_icon_right" class="ita-button-element ita-button-icon-right ita-icon ' + metaData.iconRight + '"></div>').appendTo("#" + id);
                }

            } else {

                if (typeof (metaData.iconLeft) !== 'undefined') {
                    $this.addClass('ui-btn-icon-left ui-icon ' + metaData.iconLeft);
                }
                if (typeof (metaData.iconRight) !== 'undefined') {
                    $this.addClass('ui-btn-icon-right ui-icon ' + metaData.iconRight);
                }
                $this.addClass('ui-btn-icon-notext');
                $this.removeClass('ita-button');
                $this.addClass('ita-button-notext');

            }

        }

        if (metaData.upload == true) {
            var idupload = $(this).attr('id');
            var iduploader = idupload + '_upld_uploader';
            $(this).wrap('<div id="' + iduploader + '" class="ita-plupload-uploader" style="display:inline-block;"></div>');

            if (onMobile && metaData.fileType == 'image') {
                $('#' + protSelector(iduploader)).on('click', function (e) {
                    navigator.camera.getPicture(function (imageURI) { // success

                        var options = new FileUploadOptions();
                        options.fileKey = "file";
                        options.fileName = imageURI.substr(imageURI.lastIndexOf('/') + 1);
                        options.mimeType = "image/jpg";

                        options.params = new Object();
                        options.params.name = options.fileName;
                        options.params.token = token;

                        var ft = new FileTransfer();

                        ft.upload(imageURI, encodeURI(urlPluploader), function (res) {
                            var body = JSON.parse(res.response);

                            itaGo('ItaForm', $this, {
                                id: idupload,
                                event: 'onClick',
                                file: options.params.name,
                                validate: false,
                                response: body.response
                            });
                        }, function (err) {
                            msgBlock("Errore durante l'upload:\n\n" + JSON.stringify(err, null, 4));
                        }, options);

                    }, function (err) { // error
                        msgBlock('Errore acquisizione immagine: ' + err);
                    }, {
                        quality: 30,
                        mediaType: navigator.camera.MediaType.PICTURE,
                        destinationType: navigator.camera.DestinationType.FILE_URI,
                        encodingType: navigator.camera.EncodingType.JPEG,
                        cameraDirection: navigator.camera.Direction.BACK
                    });
                });
            } else {
                itaMobile.plUploaders[iduploader] = new plupload.Uploader({
                    runtimes: 'html5',
                    browse_button: idupload,
                    container: iduploader,
                    url: serverURL + '/plupload.php',
                    multipart_params: {
                        token: token
                    }
                });
                pluploadActivate(iduploader);
            }
            return true;
        }

        if (typeof (metaData.shortCut) !== 'undefined') {
            var idWrapper = $this.parents('.ita-dialog:first').attr('id');

            if (!dialogShortCutMap[idWrapper]) {
                dialogShortCutMap[idWrapper] = new Array();
            }

            if (!dialogShortCutMap[idWrapper][metaData.shortCut]) {
                dialogShortCutMap[idWrapper][metaData.shortCut] = id;
                $('#' + idWrapper).jkey(metaData.shortCut, function (key) {
                    var $butnTarget = $(protSelector("#" + dialogShortCutMap[idWrapper][key]));
                    if ($butnTarget.is(":visible") && $butnTarget.is(":enabled")) {
                        $butnTarget.click();
                    }
                });
            }
        }

        // EVENTI
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

        if ($(this).hasClass('ita-button') || $(this).hasClass('ita-button-notext')) {
            $(this).click(function () {
                if (metaData.extraCodeBefore) {
                    try {
                        eval(metaData.extraCodeBefore);
                    } catch (e) {
                        alert("itaMobile error:\n" + e);
                    }
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

    if (container.hasClass('ita-mail-body')) {
        $(this).find('a[href^="http://"]').each(function () {
            $(this).attr({
                target: "_blank",
                title: "Apri Esternamente"
            });
        });
        return;
    }

    container.find('.ita-mobile-buttonbar').each(function () {
        var formId = this.id.split('_')[0];
        var $page = $('#' + formId + '_wrapper');
        var $form = $page.find('form');
        var height = $(this).data('ita-height') || 46;
        var $buttonBar = $(this).detach();

        var dataAttr = $page.hasClass('ui-popup') ? '' : ' data-position="fixed"';
        var navbar = '<div class="ita-mobile-buttonbar-footer" data-role="navbar" data-iconpos="left" data-ita-height="' + height + '"><ul></ul></div>';
        var footer = '<div data-role="footer" ' + dataAttr + ' id="' + $buttonBar.attr('id') + '" data-tap-toggle="false">' + navbar + '</div>';

        $form.append($page.hasClass('ui-popup') ? navbar : footer);

        var $footerUl = $page.find('.ita-mobile-buttonbar-footer ul');
        var $buttonBarMob = $page.find('.ita-mobile-buttonbar-footer');
        $buttonBar.find('button').each(function () {
            var metadata = $(this).metadata();
            var icon = (metadata.iconLeft || metadata.iconRight ? metadata.iconLeft || metadata.iconRight : '').split('-');
            icon = icon[icon.length - 1];
            var $button = $(this).html($(this).attr('value')).removeClass().attr('data-icon', icon).attr('style', '');
            $footerUl.append('<li></li>').find('li').last().append($button);
            if (height) {
                $button.css('height', height + 'px');
            }
        });
        setTimeout(function () {
            mobileButtonbarReflow($buttonBarMob);
            $page.enhanceWithin();
        }, 10);
    });

    //	container.find('.ita-tooltip').each(function() {
    //		if($(this).attr('id')){
    //			creaTooltip($(this).attr('id'));
    //		}else{
    //			creaTooltip($(this));
    //		}
    //	});

    container.find('.ita-bullet').each(function () {
        $(this).addClass('ui-state-default ui-corner-all');
    });

    container.find('.ita-header').each(function () {
        $(this).addClass('ui-bar ui-bar-a ui-corner-all').css('width', 'auto').append("<span class=\"ita-header-content\">" + $(this).attr('title') + "<span>");
    });

    container.find('.ita-span').each(function () {
        $(this).html($(this).attr('value'));
    });

    //	container.find('.ita-workspace').each(function() {
    //		$(this).addClass('ui-widget-content ui-corner-all');
    //	});

    //	container.find('textarea.ita-edit-multiline').each(function() {
    //		$(this).addClass('ui-widget-content ui-corner-all');
    //	});

    container.find('select.ita-edit,input.ita-edit,textarea.ita-edit').each(function () {
        var srcField = $(this);
        var metadata = $(this).metadata();

        if (typeof (metadata.serialize) == 'undefined')
            metadata.serialize = true;
        if (typeof (metadata.wrapOptions) !== 'undefined') {
            for (var i in metadata.wrapOptions) {
                $('#' + protSelector($(this).attr('id')) + '_field').attr(i, metadata.wrapOptions[i]);
            }
        }

        var leggi;
        var myForm = itaImplode($(this), 'FORM');

        (metadata.serialize == true) ? leggi = "tutto" : leggi = "singolo";

        if ($(this).is('textarea')) {
            $(this).wrap('<div class="ui-input-text ui-body-inherit ui-corner-all ui-shadow-inset"></div>');
        }

        //
        //  AUTOCOMPLETE EVENTS
        //
        if (typeof (metadata.autocomplete) !== 'undefined') {
            if (metadata.autocomplete.active) {
                var acParm = metadata.autocomplete;
                var minLength = 3;
                if (typeof (acParm.minLength) !== 'undefined') {
                    minLength = acParm.minLength;
                }
                var delay = 500;
                if (typeof (acParm.delay) !== 'undefined') {
                    delay = acParm.delay;
                }
                var waitImg = false;
                if (typeof (acParm.waitImg) !== 'undefined') {
                    waitImg = acParm.waitImg;
                }
                var maxH = false;
                if (typeof (acParm.maxHeight) !== 'undefined') {
                    maxH = acParm.maxHeight;
                }

                var acWidth = 260;
                if (typeof (metadata.autocomplete.width) !== 'undefined') {
                    acWidth = metadata.autocomplete.width;
                }
                var idInput = $(this).attr('id');
//                var myModel = $(itaImplode($(this), 'FORM')).attr('action').substr(1);
//                var myModel = $(this).itaGetParentForm().itaGetId();
                var cache = {};
                var lastXhr;

                $(this).autocomplete({
                    autoFocus: true,
                    minLength: minLength,
                    delay: delay,
                    search: function (event, ui) {
                        if (waitImg == true) {
                            $('#' + protSelector(idInput)).css('background', "white url('public/css/images/ui-anim_basic_16x16.gif') no-repeat scroll right center");
                        }
                        if (maxH !== false) {
                            $('.ui-autocomplete').css('max-height', maxH);
                            $('.ui-autocomplete').css('overflow-y', "auto");
                            $('.ui-autocomplete').css('overflow-x', "hidden");
                        }

                        $('.ui-autocomplete').css('z-index', '1100');
                    },
                    open: function (event, ui) {
                        $('#' + protSelector(idInput)).css('background', "none");
                    },
                    source: function (request, response) {
                        var myModel = srcField.itaGetParentForm().itaGetId();

                        var term = request.term;
                        var params = {
                            id: idInput,
                            TOKEN: token,
                            event: 'suggest',
                            model: myModel,
                            limit: 10
                        };

                        // Parametro "q" spostato fuori dall'oggetto per utilizzare
                        // la funzione escpace (ISO-8859-1) invece di encodeURI (UTF-8) (internamente in jQuery)
                        var newParams = $.param(params) + '&q=' + escape(term);

                        lastXhr = $.post(urlController, newParams, function (data, status, xhr) {
                            $('#' + protSelector(idInput)).css('background', "none");

                            var key = 'MORE';
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
                            if (xhr == lastXhr) {
                                response(dataA);
                            }
                        });
                    },
                    select: function (event, ui) {
                        $(this).addClass('ita-autocomplete-selected');
                        $('#' + protSelector(idInput)).css('background', "none");
                        if (ui.item.value) {
                            $(protSelector("#" + ui.item.altro)).focus();
                            $(protSelector("#" + ui.item.altro)).val(ui.item.codice);
                            if (ui.item.extraCols !== null) {
                                for (var idx = 0; idx < ui.item.extraCols.length; idx = idx + 2) {
                                    $(protSelector("#" + ui.item.extraCols[idx])).val(ui.item.extraCols[idx + 1]);
                                }
                            }
                        }
                    }
                });
            }
        }

        //
        //  CUSTOM BLUR KEYBOARD EVENTS CR AND TAB
        //
        $(this).bind('ita-blur', function (event, mode) {
            var destF;
            if (mode == 'next') {
                destF = moveNext(this);
            } else {
                destF = movePrev(this);
            }

            var prevTab = $(this).parents(".ita-tab");

            if (typeof (prevTab) !== 'undefined') {
                var prevPane = $(this).parents(".ita-tabpane");
            }

            var curTab = $(destF).parents(".ita-tab");

            if (typeof (prevTab) !== 'undefined') {
                var curPane = $(destF).parents(".ita-tabpane");
            }

            if (typeof (curTab) !== 'undefined' && typeof (curPane) !== 'undefined') {
                if (typeof (prevTab) == 'undefined' || $(prevTab).attr('id') + $(prevPane).attr('id') !== $(curTab).attr('id') + $(curPane).attr('id')) {
                    //					$(curTab).tabs('option', 'active', "#" + $(curPane).attr('id'));
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
            dialogLastFocus["#" + $(this).parents('.ui-page:first').attr('id')] = $(this).attr('id');
            if ($(myForm).hasClass('ita-select-field_content')) {
                $(this).select();
            }
        });

        $(this).keyup(function (event) {
            switch (event.keyCode) {
                case 13:
                    event.preventDefault();
                    break;
                case 9:
                    event.preventDefault();
                    break;
                case 27:
                    if ($(this).hasClass('ita-edit-cell')) {
                        //alert("annullo");
                        cancelInlineEdit(this, null, true, true);
                    }
                    break;
                default:
                    if ($(this).hasClass('ita-edit-uppercase')) {
                        this.value = ita_ucwords(this.value, true);
                    } else if ($(this).hasClass('ita-edit-lowercase')) {
                        this.value = ita_lcwords(this.value, true);
                    } else if ($(this).hasClass('ita-edit-capitalize')) {
                        this.value = ita_ucfirst(this.value, true);
                    }
                    break;
            }
        });
        $(this).keypress(function (event) {
            switch (event.keyCode) {
                case 13:
                    event.preventDefault();
                    break;
                case 9:
                    event.preventDefault();
                    break;
            }
        });
        $(this).keydown(function (event) {
            switch (event.keyCode) {
                case 13:
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
        metadata = null;
    });

    container.find('.ita-select').each(function () {
        $(this).addClass('ui-widget-content ui-corner-all');
    });

    container.find('.ita-edit-lookup').each(function () {
        var metaData = $(this).metadata();
        if (typeof (metaData.lookupIcon) == 'undefined')
            metaData.lookupIcon = true;
        var obj = this;

        var idlookup = $(this).attr('id') + "_butt";

        $(this).addClass('ui-widget-content ui-corner-all');
        $(this).parent().after('<button id="' + idlookup + '" type="button" class="ita-icon-right ui-btn ui-icon-search ui-btn-icon-notext ui-corner-all"></button>');

        if (metaData.lookupIcon == false) {
            $(protSelector('#' + idlookup)).css('display', 'none');
        }

        $("#" + protSelector(idlookup)).click(function () {
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
        var $this = $(this);
        var idupload = $this.attr('id') + "_upld";
        var iduploader = idupload + '_uploader';
        var metaData = $(this).metadata();
        //		var mimetypes = data.mime_types ? data.mime_types : '*/*';
        var button = '<button id="' + iduploader + '" type="button" class="ita-plupload-uploader ita-icon-right ui-btn ui-icon-action ui-btn-icon-notext ui-corner-all"></button>';

        /* fix 19.06.15 - Carlo */
        setTimeout(function () {
            $this.addClass('ui-widget-content ui-corner-all').parent().after(button);

            if (onMobile && metaData.fileType == 'image') {
                $('#' + protSelector(iduploader)).on('click', function (e) {
                    navigator.camera.getPicture(function (imageURI) { // success

                        var options = new FileUploadOptions();
                        options.fileKey = "file";
                        options.fileName = imageURI.substr(imageURI.lastIndexOf('/') + 1);
                        options.mimeType = "image/jpg";

                        options.params = new Object();
                        options.params.name = options.fileName;
                        options.params.token = token;

                        var ft = new FileTransfer();

                        ft.upload(imageURI, encodeURI(urlPluploader), function (res) {
                            var body = JSON.parse(res.response);

                            itaGo('ItaForm', $this, {
                                id: idupload,
                                event: 'onClick',
                                file: options.params.name,
                                validate: false,
                                response: body.response
                            });
                        }, function (err) {
                            msgBlock("Errore durante l'upload:\n\n" + JSON.stringify(err, null, 4));
                        }, options);

                    }, function (err) { // error
                        msgBlock('Errore acquisizione immagine: ' + err);
                    }, {
                        quality: 30,
                        mediaType: navigator.camera.MediaType.PICTURE,
                        destinationType: navigator.camera.DestinationType.FILE_URI,
                        encodingType: navigator.camera.EncodingType.JPEG,
                        cameraDirection: navigator.camera.Direction.BACK
                    });
                });
            } else {
                itaMobile.plUploaders[iduploader] = new plupload.Uploader({
                    runtimes: 'html5',
                    browse_button: iduploader,
                    url: serverURL + '/plupload.php',
                    multipart_params: {
                        token: token
                    }
                });
                pluploadActivate(iduploader);
            }
        }, 10);
    });

    container.find('select.ita-edit-onchange, .ita-edit-cell').each(function () {
        var obj = $("#" + protSelector($(this).attr('id')));
        $(this).change(function () {
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
                        validate: false
                    });
                }
            }
        });
    });

    container.find('input.ita-decode').each(function () {
        $(this).attr('disabled', 'disabled').addClass('ui-widget-content ui-corner-all');
    });

    container.find('input.ita-datepicker').each(function () {
        var metaData = $(this).metadata();
        if (typeof (metaData.showOn) == 'undefined')
            metaData.showOn = 'button';
        var dtp = $(this);
        dtp.jkey('f1', function () {
            dtp.datepicker("show");
        });
        //		dtp.attr('data-role', 'date').parent().enhanceWithin();
        dtp.date({
            changeYear: true,
            //			changeMonth: true,
            dateFormat: 'dd/mm/yy',
            showOn: metaData.showOn,
            showAnim: 'slideDown',
            onSelect: function () {
                dtp.focus();
            },
            yearRange: "-100:+10"
        }).next(".ui-datepicker-trigger").detach().insertAfter(dtp.parent()).addClass("ita-icon-right ui-btn ui-icon-calendar ui-btn-icon-notext ui-corner-all");
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
            itaGo(metaData.request, obj, {
                id: metaData.id,
                model: metaData.model,
                event: metaData.event,
                validate: false
            });
            if (metaData.extraCode) {
                eval(metaData.extraCode);
            }
            return false;
        });
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
        if (typeof (metaData.min) !== 'undefined') {
            $("#" + $(this).attr('id')).slider("option", "min", metaData.min);
        }
        if (typeof (metaData.max) !== 'undefined') {
            $("#" + $(this).attr('id')).slider("option", "max", metaData.max);
        }
        if (typeof (metaData.value) !== 'undefined') {
            $("#" + $(this).attr('id')).slider("option", "value", metaData.value);
        }

        if (typeof (metaData.step) !== 'undefined') {
            $("#" + $(this).attr('id')).slider("option", 'step', metaData.step);
        }

        if (typeof (metaData.values) !== 'undefined') {
            $("#" + $(this).attr('id')).slider("option", 'values', metaData.values);
        }

    });

    container.find('.ita-element-animate').each(function () {
        $(this).addClass('ui-state-default').hover(function () {
            $(this).addClass('ui-state-hover');
        }, function () {
            $(this).removeClass('ui-state-hover');
        });
    });

    container.find('div.ita-fixed').each(function () {
        $that = $(this);
        setTimeout(function () {
            $that.nextAll().css('padding-top', $that.innerHeight() + 10 + 'px');
        }, 0);
    });

    container.find('select.ita-readonly,textarea.ita-readonly,input.ita-readonly').each(function () {
        var metadata = $(this).metadata();
        if (typeof (metadata.wrapOptions) !== 'undefined') {
            for (var i in metadata.wrapOptions) {
                $('#' + protSelector($(this).attr('id')) + '_field').attr(i, metadata.wrapOptions[i]);
            }
        }
        $(this).attr('readonly', 'readonly').addClass('ui-widget-content ui-corner-all').focus(function () {
            var destF = moveNext(this);
            $(destF).focus();
        });
        $('#' + protSelector($(this).attr('id')) + '_field button').remove();
    });

    $('body').find('[data-rel]').on('click', function (e) {
        //		e.preventDefault();
    });

    container.find('.ita-menugrid').each(function () {
        if (typeof $.gridster === 'undefined') {
            itaGetLib('jquery.gridster.056.css', 'gridster');
            itaGetLib('jquery.gridster.056.js', 'gridster');
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

function removeDesktop(reload) {
    var reload = reload === false ? false : true;

    token = null;
    tmpToken = null;
    $("body").html('');

    if (onMobile && reload) {
        location.reload();
    }
}

function eqdate(cellvalue, options, rowid) {
    var retValue = cellvalue;
    if (isDate(cellvalue, 'yyyyMMdd')) {
        var data = new Date(getDateFromFormat(cellvalue, 'yyyyMMdd'));
        retValue = formatDate(data, 'dd/MM/yyyy');
    }
    return retValue;
}

function itaTime(cellvalue, options, rowid) {
    var retValue = cellvalue;
    var timeformat = "HHMMSS";
    if (typeof (options) !== 'undefined') {
        if (typeof (options.timeformat) !== 'undefined') {
            timeformat = options.timeformat;
        }
    }
    if (cellvalue !== '') {
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

function itacheckbox(cellvalue, options, rowid) {
    cellvalue = cellvalue + "";
    cellvalue = cellvalue.toLowerCase();
    var id = 'ita-jqg-editcheckbox-' + options.name + "-" + rowid;
    var bchk;
    if (cellvalue == '') {
        bchk = "";
    } else {
        bchk = cellvalue.search(/(false|0|no|off|n)/i) < 0 ? "checked=\"checked\"" : "";
    }
    return '<input id="' + id + '" class="ita-jqg-editcheckbox {rowid:' + rowid + ',cellname:\'' + options.name + '\'}" type="checkbox" ' + bchk + '/>';
}

function jqGridFormatter(format, cellvalue, options, rowid) {
    if (!cellvalue && options.defaultValue)
        cellvalue = options.defaultValue;
    var retValue = cellvalue;

    switch (format) {
        case 'checkbox':
            var disb = options.disabled == false ? '' : 'disabled="disabled"';
            var bchk = cellvalue.search(/(false|0|no|off|n)/i) < 0 ? "checked=\"checked\"" : "";
            retValue = '<input ' + bchk + '  ' + disb + ' type="checkbox" />';
            break;

        case 'currency':
            retValue = parseFloat(cellvalue).toFixed((options.decimalPlaces ? options.decimalPlaces : 2));
            retValue = retValue.replace(/(\d)(?=(\d{3})+\.)/g, '$1' + (options.thousandsSeparator ? options.thousandsSeparator : '.'));
            retValue = retValue.replace(/(.*)([.]{1})/g, '$1' + (options.decimalSeparator ? options.decimalSeparator : ','));
            if (options.prefix) {
                retValue = options.prefix + retValue;
            }
            if (options.suffix) {
                retValue = retValue + options.suffix;
            }
            break;

        case 'date':
            break;
    }

    return retValue;
}

function itaUIApp(idDialog, pageReturn) {
    var $panels = $('.ui-panel-open').panel('close');

    var idWrapper = idDialog + '_wrapper';
    pageReturn = pageReturn !== undefined ? pageReturn : false;

    $('[data-role="page"]').hide();

    var $wrapper = $('#' + idDialog + '_wrapper');

    if ($wrapper.length == 0) {
        // ?
        return;
    }

    var obj = $('#' + idDialog).metadata();
    var label = obj.title;

    $('#envMobileHomeBar_title').html('<span id="' + idDialog + '_appTitle">' + label + '</span>');

    if (obj.closeButton === undefined || obj.closeButton !== false) {
        $('#envMobileHomeBar_title')
            .prepend('<button class="ui-btn ui-shadow ui-corner-all ui-icon-delete ui-btn-icon-notext" data-itaicon="mini" style="position: absolute; right: 10px; top: 12px;"></button>')
            .find('button').on('click', function (e) {
            e.preventDefault();
            closeUIApp($wrapper, true);
        });
    }

    if (!pageReturn) {
        $wrapper.detach().appendTo('#desktopBody').attr('data-role', 'page').children().children().wrapAll('<div role="main"></div>');
        $wrapper.find('[role="main"] > br').remove();
    } else {
        $wrapper.show();
    }

    $wrapper.page();

    setTimeout(function () {
        $('#desktopBody').enhanceWithin().pagecontainer('change', '#' + idWrapper, {
            changeHash: false
        }).on('pagecontainerchange', function (event, ui) {
            setTimeout(function () {
                itaMobilePageReflow($wrapper);
            }, 0);
        });
    }, 0);
}

function closeUIApp(element, closePortlet) {
    var $element = (element instanceof jQuery ? element : $('#' + element));
    closePortlet = closePortlet == true ? true : false;
    var obj = $element.find('form').metadata();

    var myModel = $element.find('form').attr('id');

    if (obj.closeAuto !== 'undefined' && obj.closeAuto === false) {
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

    $element.remove();

    $('#envMobileHomeBar_title').html('itaMobile');
    var $lastPage = $('[data-role="page"]').last();
    if ($lastPage.length > 0) {
        $lastPage.show();
        itaUIApp($lastPage.attr('id').slice(0, -8), true);
    }

    if (typeof (myModel) !== 'undefined' && closePortlet) {
        itaGo('ItaCall', '', {
            id: 'close-portlet',
            event: 'onClick',
            model: myModel,
            validate: false
        });
    }

    delete dialogShortCutMap[$element.attr('id')];
}

function itaUIDialog(idDialog, stackReturn) {
    var $dialog = $('#' + idDialog);
    var idWrapper = idDialog + '_wrapper';
    var $wrapper = $('#' + idWrapper);
    stackReturn = stackReturn !== undefined ? stackReturn : false;
    var $parent = $wrapper.parent();

    if (dialogChain.length > 0 && !stackReturn) {
        for (var key in dialogChain) {
            try {
                $('#' + dialogChain[key] + '_wrapper').popup().popup('destroy');
            } catch (e) {
                console.log(e.stack);
            }
            $('#' + dialogChain[key] + '_wrapper').hide();
        }
    }

    if (stackReturn) {
        $wrapper.show();
    }

    var obj;
    try {
        obj = $dialog.metadata();
    } catch (e) {
        console.trace();
        console.log(idDialog, stackReturn, e.stack);
        obj = {};
    }

    var saveParent = $wrapper.parent().attr('id');

    $dialog.css('max-height', window.innerHeight - 65).css('overflow-y', 'auto');
    $wrapper.attr('data-role', 'popup').attr('data-theme', 'a').attr('data-position-to', 'window').css('overflow', 'hidden');//.detach().appendTo('body');
    //	$wrapper.css('height', '98%').css('overflow-y', 'scroll').css('margin-top', '1%');
//    if ( !$dialog.is('form') && !$dialog.children().is('.ui-listview') ) {
//        $dialog.css('padding', '20px');
//    }

    $dialog.children('br').remove();
    if ($dialog.children('.ui-content').length < 1) {
        $dialog.children().wrapAll('<div class="ui-content" role="main"></div>');
    }

//    $wrapper.find('form').addClass('ui-content');

    var myCloseOnEscape = false;

    var closeButton = true;
    if (typeof (obj.closeButton) !== 'undefined')
        closeButton = obj.closeButton;
    delete obj.closeButton;

    var iconLeft = '';
    if (typeof (obj.iconLeft) !== 'undefined')
        iconLeft = obj.iconLeft;
    delete obj.iconLeft;

    var maximized = false;
    if (typeof (obj.maximized) !== 'undefined')
        maximized = obj.maximized;
    delete obj.maximized;

    if (typeof (obj.maximizedOnMobile) !== 'undefined')
        maximized = obj.maximizedOnMobile;
    delete obj.maximizedOnMobile;

    var dialogHeader = true;
    if (typeof (obj.dialogHeader) !== 'undefined')
        dialogHeader = obj.dialogHeader;
    delete obj.dialogHeader;

    var uiDiagObj = {};

    //	uiDiagObj.autoOpen = false;
    uiDiagObj.closeOnEscape = false;

    if (closeButton == true) {
        myCloseOnEscape = true;
    }

    if (typeof (obj.closeOnEscape) !== 'undefined') {
        myCloseOnEscape = obj.closeOnEscape;
    }

    //	uiDiagObj.height = 'auto';
    //	uiDiagObj.width = 'auto';
    //	uiDiagObj.minHeight = 0;
    //	uiDiagObj.minWidth = 0;
    //	uiDiagObj.bgiframe = true;

    if (saveParent) {
        uiDiagObj.appendTo = "#" + saveParent;
    }

    // HEADER
    if (!stackReturn && dialogHeader) {
        $wrapper.prepend('<div data-role="header" id="' + idDialog + '_dialogHeader"><h1 id="' + idDialog + '_dialogTitle">' + (obj.title ? obj.title : '') + '</h1></div>');
    }

    uiDiagObj.afteropen = function (event, ui) {
        // Prevent background scroll
        $('body').css('overflow-y', 'hidden');

        if (closeButton == false) {
            $(this).find('.ui-icon-delete').hide();
        }
        //			setDialogLayout(this);
        //			dialogShortCutMap["#" + idWrapper] = new Array();
        //			setDialogLightBoxOpt($('#' + idDialog));
        //		dialogLayoutStack[dialogLayoutStack.length] = idDialog;

        $(this).find('.ui-dialog-content').focus();

        if (dialogLastFocus[idDialog]) {
            $('#' + dialogLastFocus[idDialog]).focus();
        }
    };

    uiDiagObj.afterclose = function (event, ui) {
        // Prevent background scroll
        $('body').css('overflow-y', 'initial');
    };

    uiDiagObj.focus = function (event, ui) {
        //		currDialogFocus = $(this).attr('id');
        //        if(currDialogFocus !='') $("#"+currDialogFocus).focus();
    };


    uiDiagObj.resize = function (event, ui) {
        //		dialogLayoutStack[$(this).attr('id')].resizeAll();
    };

    //	if (maximized == true) {
    //		obj.height = $("#" + idWrapper).parent().innerHeight() - 10;
    //		if (dialogHeader == false) {
    //			obj.height = obj.height + 28//$("#"+idWrapper).parents('.ui-dialog').find('.ui-dialog-titlebar').innerHeight();
    //		}
    //		obj.width = $("#" + idWrapper).parent().innerWidth() - 10;
    //		obj.position = [0, 0];
    //		obj.resizable = false;
    //		obj.draggable = false;
    //	}

    //    console.log(obj.fullHeight == true);
    //    if ( obj.fullHeight == true ) {
    //        uiDiagObj.beforeposition = function () {
    //            console.log($(this));
    //            $(this).css({
    //                height: window.innerHeight - 50
    //            });
    //        };
    //    }

    if ($wrapper.find('img').length > 0 && $wrapper.find('img').css('width') == '100%') {
        uiDiagObj.beforeposition = function () {
            $(this).css({
                width: window.innerWidth / 100 * 90
            });
        };
    }

    if (maximized == true) {
        uiDiagObj.beforeposition = function () {
            $dialog.css('max-height', window.innerHeight - 65);
            $(this).css({
                height: window.innerHeight / 100 * 97,
                width: window.innerWidth / 100 * 90
            });
        };
    }

    key = "";
    for (key in obj) {
        uiDiagObj[key] = obj[key];
    }

    obj = null;
    key = null;

    uiDiagObj.dismissible = false;
    uiDiagObj.history = false;
    uiDiagObj.theme = 'a';
    uiDiagObj.positionTo = 'window';
    uiDiagObj.overlayTheme = "b";

    $wrapper.enhanceWithin();

    if ((!uiDiagObj.draggable || uiDiagObj.draggable == true) && !maximized) {
        $wrapper.draggable({
            handle: '[data-role="header"]',
            cursor: "move",
            scroll: false,
            containment: 'document'
        });
    }

    if (!stackReturn) {
        dialogChain[dialogChain.length] = idDialog;
    }

    $wrapper.popup(uiDiagObj);

    setTimeout(function () {
        if (idDialog == dialogChain[dialogChain.length - 1]) {
            $wrapper.popup('open');
            setTimeout(function () {
                $wrapper.popup('reposition', {positionTo: 'window'});
                $wrapper.parent().detach().appendTo($parent);
                $('#' + idWrapper + '-screen').detach().appendTo($parent);
            }, 10);
        }
    }, 0);

    if (dialogHeader == false) {
        $wrapper.parents('.ui-dialog').find('.ui-dialog-titlebar').remove();
    }

    if (myCloseOnEscape) {
        if (!stackReturn) {
            $wrapper.find('[data-role="header"]').append('<button data-rel="back" class="ui-btn-right ui-btn ui-btn-b ui-btn-inline ui-mini ui-corner-all ui-btn-icon-right ui-icon-delete ui-btn-icon-notext"></button>').find('button').on('click', function (e) {
                closeUIDialog($wrapper, true);
            });
        }

        $wrapper.on('keydown', function (evt) {
            if (evt.keyCode == 27) {
                evt.stopPropagation();
            }
        }).on('keyup', function (evt) {
            if (evt.keyCode == 27) {
                evt.stopPropagation();
                closeUIDialog($wrapper, true);
            }
        }).on('keypress', function (evt) {
            if (evt.keyCode == 27) {
                evt.stopPropagation();
            }
        });
    }

    if (iconLeft !== '') {
        $wrapper.find('[data-role="header"]').prepend('<button id="' + idDialog + '_dialogHeaderIconLeft" class="ita-button ui-btn-left ui-btn ui-btn-b ui-btn-inline ui-mini ui-corner-all ui-btn-icon-left ' + iconLeft + ' ui-icon-back ui-btn-icon-notext"></button>');
        $wrapper.find("#" + idDialog + "_dialogHeaderIconLeft").click(function () {
            itaGo('ItaCall', '', {
                id: $(this).attr('id'),
                event: 'onClick',
                model: idDialog,
                validate: false
            });

        });
        setTimeout(function () {
            $wrapper.find('[data-role="header"]').enhanceWithin();
        }, 0);
    }
}

function closeUIDialog(element, closePortlet) {
    var $element = (element instanceof jQuery ? element : $('#' + element));
    var id = $element.attr('id');

    if (!id) {
        return false;
    }

    /* Check in dialogChain - Carlo 17.06.15 */
    var chainName = id.split('_')[0];
    var inChain = false;

    for (var k in dialogChain) {
        if (dialogChain[k] == chainName) {
            inChain = k;
        }
    }
    if (inChain === false) {
        return false;
    }

    if ($('#' + id + '_wrapper').length > 0) {
        $element = $('#' + id + '_wrapper');
    }

    closePortlet = closePortlet == true ? true : false;

    $element.find('.ita-activedTimer').each(function () {
        $element.stopTime('ita-timer');
    });

    var myModel = $element.find('form').attr('id');

    //	delDialogLayout($element);
    //	delete dialogLightBoxOpt[$element.attr('id')];
    //	delete dialogShortCutMap[keyMap];

    delete dialogLastFocus[$element.attr('id')];

    try {
        $element.popup().popup('destroy').remove();
    } catch (e) {
        console.log(e.stack);
    }

    if (typeof (myModel) !== 'undefined' && closePortlet) {
        itaGo('ItaCall', '', {
            id: 'close-portlet',
            event: 'onClick',
            model: myModel,
            validate: false
        });
    }

    dialogChain.splice(inChain, 1);

    if (dialogChain.length > 0 && inChain == dialogChain.length) {
        itaUIDialog(dialogChain[dialogChain.length - 1], true);
    }

    var currDialogFocus = getCurrDialog();
    if (currDialogFocus) {
        $("#" + currDialogFocus).focus();

        if (dialogLastFocus["#" + currDialogFocus]) {
            $(protSelector("#" + dialogLastFocus["#" + currDialogFocus])).focus();
        }
    } else if (dialogLastFocus["#" + $('.ui-page-active').attr('id')]) {
        $(protSelector("#" + dialogLastFocus["#" + $('.ui-page-active').attr('id')])).focus();
    }

    delete dialogShortCutMap[$element.attr('id')];
}

function delDialogLayout(objDiag) {
//	//return; //** MM
//	if (dialogLayoutStack[$(objDiag).attr('id')]) {
//		//dialogLayoutStack.splice($(objDiag).attr('id'),1);
//		delete dialogLayoutStack[$(objDiag).attr('id')];
//	}
}

function getCurrDialog() {
    return dialogChain[dialogChain.length - 1];
}

function itaMobilePageReflow(page) {
    var $page = (page instanceof jQuery ? page : $('#' + page));

    $page.find('.ita-jqGrid').each(function () {
        itaMobileGridResize($(this));
    });
}

function itaMobileGridChangePage(page, grid) {
    var $grid = (grid instanceof jQuery ? grid : $('#' + grid));
    var id = $grid.attr('id');
    if (page == 'next') {
        if (!gridParams[id]['totPages'] || gridParams[id]['totPages'] > gridParams[id]['page']) {
            gridParams[id]['page']++;
            itaMobileGridPostdata($grid);
        }
    } else if (page == 'prev') {
        if (gridParams[id]['page'] > 1) {
            gridParams[id]['page']--;
            itaMobileGridPostdata($grid);
        }
    }
}

function itaMobileGridPostdata(grid) {
    var $grid = (grid instanceof jQuery ? grid : $('#' + grid));
    var id = $grid.attr('id');
    itaGo('ItaForm', $grid, {
        event: 'onClickTablePager',
        validate: false,
        rows: gridParams[id]['rowNum'],
        sidx: gridParams[id]['sidx'],
        sord: gridParams[id]['sord'],
        page: gridParams[id]['page'],
        _search: gridParams[id]['search']
    });
}

function itaMobileGridResize(grid) {
    setTimeout(function () {
        var $grid = (grid instanceof jQuery ? grid : $('#' + grid));
        var id = $grid.attr('id');
        var data = $grid.metadata();

        if (data.fullsize) {
            var top = window.innerHeight - $grid.parent().offset().top;
            var buttonBar = $grid.closest('[data-role="page"]').find('[role="navigation"]:visible').outerHeight();
            var footerHeight = data.fixedToolbar ? 43 : 0;
            $grid.parent().css('height', top - buttonBar - footerHeight - 1 + 'px');
            $('#' + id + '_tfoot').css('margin-bottom', buttonBar + 'px');
        }
    }, 10);
}

function itaMobileGrid(id) {
    var $grid = $('#' + id).attr('data-role', 'table').attr('data-mode', 'none').addClass('ita-jqGrid-activated');
    $grid.addClass('ui-body-a table-stripe ui-responsive').find('thead').addClass('ui-bar-d').find('th').attr('data-priority', '1');
    var fieldNum = $grid.find('#baseRow').find('td').length;

    var data = $grid.metadata();

    if (data.multiselect) {
        $grid.find('thead tr').prepend('<th><input type="checkbox" data-role="none"></th>');
        $grid.find('#baseRow').prepend('<td id="multiselect" data-itametadata="{sortable: false}"></td>');
        fieldNum++;
    }

    var tds = Array();
    for (var i = 0; i < fieldNum; i++) {
        var $td = $grid.find('#baseRow td').eq(i);
        tds[$td.attr('id')] = $td.metadata();
        $td.css('min-width', ($td.attr('width') ? $td.attr('width') + 'px' : '0')).removeAttr('width');
    }

    var height, rowNum;
    if (data.height) {
        height = data.height;
        rowNum = data.rowNum ? data.rowNum : parseInt(height / 50);
    } else {
        rowNum = data.rowNum;
        height = data.height ? data.height : parseInt(rowNum * 45);
    }

    if (data.filterToolbar) {
        $grid.find('tbody').prepend('<tr id="' + id + '_searchToolbar" class="searchToolbar" style="height: 40px;"></tr>');
        var $searchBar = $grid.find('#' + id + '_searchToolbar');

        for (var key in tds) {
            $searchBar.append('<td>&nbsp</td>');
            if (tds[key].search === undefined || tds[key].search == true) {
                $searchBar.find('td').last().html('<input id="gs_' + key + '" name="' + key + '" data-role="none" style="width: 98%;">');
            }
        }

        $grid.find('.searchToolbar input').on('keydown', function (e) {
            if (e.which == 13) {
                $grid.find('.searchToolbar input').each(function () {
                    if (this.value !== '')
                        gridParams[id]['search'] = 'true';
                });
                itaMobileGridPostdata($grid);
                e.preventDefault();
                e.stopPropagation();
            }
        });
    }

    if (data.dataReflow) {
        $grid.attr('data-mode', 'reflow');
    }

    if (data.hideHeaders) {
        $grid.addClass('hide-headers');
    }

    var footerContent = '<div id="' + id + '_tfoot_icons" class="tfoot-icons">&nbsp;</div><div id="' + id + '_tfoot_paginator" class="tfoot-paginator">&nbsp;</div><div id="' + id + '_tfoot_text" class="tfoot-text">&nbsp;</div>';

    if (data.fixedToolbar) {
        $grid.parent().parent().append('<div data-role="footer" data-position="fixed" data-tap-toggle="false" id="' + id + '_tfoot">' + footerContent + '</div>');
        $('#' + id + '_tfoot').css('position', 'fixed').css('height', '43px');

        setTimeout(function () {
            // Alza il footer se presente buttonBar
            $('#' + id + '_tfoot').css('margin-bottom', $('#' + id + '_tfoot').closest('[data-role="page"]').find('[role="navigation"]').innerHeight() + 'px');
        }, 0);

    } else {
        $grid.append('<tfoot id="' + id + '_tfoot"><tr><td colspan="' + fieldNum + '">' + footerContent + '</td></tr></tfoot>');
    }

    var $footer = $('#' + id + '_tfoot'),
        $footerIcons = $footer.find('#' + id + '_tfoot_icons'),
        $footerPaginator = $footer.find('#' + id + '_tfoot_paginator'),
        $footerText = $footer.find('#' + id + '_tfoot_text');

    setTimeout(function () {
        $grid.table();

        if (data.fullsize) {
            $grid.parent().on('scroll', function (e) {
                var $this = $(this);
                if ($grid.is(':visible')) {
                    if ($this.height() + $this.scrollTop() >= this.scrollHeight && !gridScrollLock[id]) {
                        gridScrollLock[id] = true;
                        itaMobileGridChangePage('next', $grid);
                    }
                }
            });
        }

        itaMobileGridResize($grid);
    }, 0);

    /* window.innerHeight - ita-mobile-home-toolbar - page padding - buttonBarHeight */
    //	if ( !data.pgbuttons || data.pgbuttons == true ) {
    //		if ( data.resizeToParent ) {
    //			if ( $grid.parent().css('height').indexOf('px') > -1 && parseInt($grid.parent().css('height')) > 0 ) {
    //				h = parseInt( $grid.parent().css('height') );
    //			} else if ( $grid.parent().parent().css('height').indexOf('px') > -1 && parseInt($grid.parent().parent().css('height')) > 0 ) {
    //				h = parseInt( $grid.parent().parent().css('height') );
    //			} else {
    //				h = parseInt( window.innerHeight );
    //				h -= 100; // - topbar & padding
    //				h -= buttonBarHeight;
    //				h = h + 'px';
    //			}
    //		} else {
    //			if ( data.rowNum ) {
    //				h = 36*data.rowNum + 'px';
    //			}
    //		}
    //	}

    if (data.caption && (!data.hideCaption || data.hideCaption == false)) {
        // $grid.find('thead').prepend('<tr><th data-priority="persist" class="caption" colspan="' + fieldNum + '">' + data.caption + '</th></tr>');
        $grid.parent().prepend('<div style="text-align: center;"><h3>' + data.caption + '</h3></div>');
    }

    // $grid.css('height', height + 'px');

    gridParams[id] = new Array();
    gridParams[id]['rowNum'] = rowNum;
    gridParams[id]['page'] = 1;
    gridParams[id]['sidx'] = $grid.find('#baseRow td').eq(data.multiselect ? 1 : 0).attr('id');
    gridParams[id]['sord'] = 'asc';
    gridParams[id]['search'] = 'false';

    if (data.sortname) {
        gridParams[id]['sidx'] = data.sortname;
    }

    if (data.sortorder) {
        gridParams[id]['sord'] = data.sortorder;
    }

    if ($grid.find('#' + gridParams[id]['sidx']).length > 0) {
        $grid.find('thead th').eq($grid.find('#' + gridParams[id]['sidx']).index()).append(' <span class="sort-arr">' + (gridParams[id]['sord'] == 'desc' ? ' &darr;' : ' &uarr;') + '</span>');
    }

    //	gridLastSel[id] = null;
    //	gridInlineLock[id] = false;

    if (typeof (data.readerId) == 'undefined') {
        data.readerId = 'ROWID';
    }
    if (typeof (data.hidegrid) == 'undefined') {
        data.hidegrid = false;
    }

    if (data.fullsize) {
        $grid.parents('.ita-data-page').css('padding', '0');
        $grid.parent().css('overflow-y', 'auto');
        $grid.find('.sort-arr').remove();
        $footerPaginator.remove();

        $grid.addClass('ita-jqGrid-fullsize');
        gridParams[id]['rowNum'] = data.rowNum && data.rowNum >= parseInt(window.innerHeight / 36) ? data.rowNum : parseInt(window.innerHeight / 36);
    }

    //	var gridObj = {};
    //	
    //	gridObj.datatype = function(postdata) {
    //		if ($("#" + id).hasClass('ita-jqgrid-active')) {
    //			var idObj = $("#" + id);
    //			itaGo('ItaForm', idObj, {
    //				event: 'onClickTablePager',
    //				validate: false,
    //				rows: postdata.rows,
    //				page: postdata.page,
    //				sidx: postdata.sidx,
    //				sord: postdata.sord,
    //				_search: postdata._search,
    //				nodeid: postdata.nodeid,
    //				parentid: postdata.parentid,
    //				n_level: postdata.n_level
    //			});
    //		}
    //	};
    //	
    //	gridObj.jsonReader = {
    //		root: "row",
    //		page: "pagina",
    //		total: "pagine",
    //		records: "righe",
    //		repeatitems: false,
    //		id: obj.readerId
    //	};
    //
    //	gridObj.xmlReader = {
    //		root: "jqgrid",
    //		row: "row",
    //		page: "jqgrid>pagina",
    //		total: "jqgrid>pagine",
    //		records: "jqgrid>righe",
    //		repeatitems: false,
    //		id: obj.readerId
    //	};

    if (!data.fullsize || data.fullsize == false) {
        $grid.on('swipeleft', function (e) {
            var $target = $(e.target);
            if ($target.parents('tbody').length > 0) {
                itaMobileGridChangePage('next', $grid);
            }
        });

        $grid.on('swiperight', function (e) {
            var $target = $(e.target);
            if ($target.parents('tbody').length > 0) {
                itaMobileGridChangePage('prev', $grid);
            }
        });
    }

    $grid.on('dblclick', function (e) {
        var $target = $(e.target);
        if ($target.parents('tbody').length > 0) {
            if ($target.parents('#baseRow, .searchToolbar').length > 0)
                return;

            var rowid = $target.parents('tr').attr('id');
            if (!rowid)
                return;
            itaGo('ItaForm', $grid, {
                event: 'dbClickRow',
                validate: false,
                rowid: rowid
            });
        }
    });

    $grid.on('click', function (e) {
        var $target = $(e.target);

        if ($target.parents('thead').length > 0 && (!data.fullsize || data.fullsize == false)) {
            var $arr = $grid.find('thead .sort-arr').detach();
            var index = $target.index();
            var $baseRowCol = $grid.find('#baseRow td').eq(index);
            gridParams[id]['sord'] = gridParams[id]['sidx'] == $baseRowCol.attr('id') ? (gridParams[id]['sord'] == 'asc' ? 'desc' : 'asc') : 'asc';
            gridParams[id]['sidx'] = $baseRowCol.attr('id');
            var itadata = $baseRowCol.metadata();

            if (data.multiselect) {
                var $cb = ($target.is('th') ? $target : $target.parents('th')).find('input[type="checkbox"]');
                if ($cb.length > 0) {
                    if (!$target.is('input[type="checkbox"]')) {
                        $cb.prop('checked', !$cb.prop('checked'));
                    }

                    $grid.find('tbody > tr > td:first-child input[type="checkbox"]').prop('checked', $cb.prop('checked'));

                    var $trs = $grid.find('tbody > tr').not('#baseRow, .searchToolbar');
                    if ($cb.prop('checked')) {
                        $trs.addClass('ita-grid-selected');
                    } else {
                        $trs.removeClass('ita-grid-selected');
                    }
                }
            }

            if (itadata.sortable == undefined || itadata.sortable != false) {
                $grid.find('thead th').eq(index).append($arr).find('.sort-arr').html(gridParams[id]['sord'] == 'desc' ? ' &darr;' : ' &uarr;');
                itaMobileGridPostdata($grid);
            }
        } else if ($target.parents('tbody').length > 0) {
            if ($target.parents('#baseRow, .searchToolbar').length > 0 || !$target.parents('tr').attr('id'))
                return;

            if ($target.parents('td').length > 0) {
                $target = $target.parents('td');
            }

            var $targetRow = $target.parents('tr');
            var rowid = $targetRow.attr('id');
            var iCol = $target.index();
            var $baseRowCol = $grid.find('tr#baseRow td').eq(iCol);
            var colName = $baseRowCol.attr('id');
            var cellcontent = $target.clone().children().remove().end().text();

            gridParams[id]['selrow'] = rowid; //.index();
            gridParams[id]['rowid'] = rowid;

            if (data.multiselect) {
                var $cb = $targetRow.find('td:eq(0) input[type="checkbox"]');

                $targetRow.toggleClass('ita-grid-selected');
                $cb.prop('checked', $targetRow.hasClass('ita-grid-selected'));

                $grid.find('thead > tr > th:first-child input[type="checkbox"]').prop('checked', false);
            } else {
                $targetRow.addClass('ita-grid-selected');
                $('.ita-grid-selected').removeClass('ita-grid-selected');
            }

            var myColModel = $baseRowCol.metadata();
            if (myColModel.itaSelectable == true) {
                itaGo('ItaForm', $grid, {
                    event: 'cellSelect',
                    validate: false,
                    rowid: rowid,
                    iCol: iCol,
                    colName: colName,
                    cellContent: cellcontent
                });
            } else {
                $target.trigger('dblclick');
            }
        }
    });


    //	gridObj.afterSaveCell = function(rowid, cellname, value, iRow, iCol) {
    //		var idObj = $("#" + id);
    //		itaGo('ItaForm', idObj, {
    //			event: 'afterSaveCell',
    //			validate: false,
    //			rowid: rowid,
    //			cellname: cellname,
    //			value: value
    //		});
    //	};


    var gridObj = new Array();
    gridObj.caption = "Tabella";
    gridObj.autowidth = false;
    gridObj.rowNum = 10;
    gridObj.rowList = [10, 20];
    gridObj.pager = id + "-ita-pager";
    gridObj.cellsubmit = 'clientArray';
    gridObj.viewrecords = true;
    gridObj.sortable = true;
    gridObj.scrollOffset = 0;

    var key = "";
    var filterToolbar = false;
    var navGrid = false;
    var navButtonAdd = false;
    var navButtonDel = false;
    var navButtonEdit = false;
    var navButtonExcel = false;
    var navButtonPrint = false;
    var navButtonRefresh = false;
    var navButtonColch = true;
    var disableselectall = false;
    var sortablerows = false;
    var pginput = true;
    var pgbuttons = true;

    for (key in data) {
        if (key == 'filterToolbar') {
            filterToolbar = data[key];
            continue;
        }
        if (key == 'columnChooser') {
            navButtonColch = data[key];
            continue;
        }
        if (key == 'sortablerows') {
            sortablerows = data[key];
            continue;
        }
        if (key == 'navGrid') {
            navGrid = data[key];
            continue;
        }
        if (key == 'navButtonAdd') {
            navButtonAdd = data[key];
            continue;
        }
        if (key == 'navButtonDel') {
            navButtonDel = data[key];
            continue;
        }
        if (key == 'navButtonEdit') {
            navButtonEdit = data[key];
            continue;
        }
        if (key == 'navButtonExcel') {
            navButtonExcel = data[key];
            continue;
        }
        if (key == 'navButtonPrint') {
            navButtonPrint = data[key];
            continue;
        }
        if (key == 'navButtonRefresh') {
            navButtonRefresh = data[key];
            continue;
        }
        if (key == 'disableselectall') {
            disableselectall = data[key];
            continue;
        }
        if (key == 'pginput') {
            pginput = data[key];
            continue;
        }
        if (key == 'pgbuttons') {
            pgbuttons = data[key];
            continue;
        }
        gridObj[key] = data[key];
    }

    if (navGrid) {
        var iconClass = 'ui-btn ui-shadow ui-corner-all ui-btn-icon-notext ui-btn-inline';
        var title, icon, btnId;

        if (pgbuttons) {
            title = "Avanti";
            icon = "ui-icon-carat-l";
            btnId = id + "_goPrevPage";
            $footerPaginator.append('<button type="button" title="' + title + '" class="' + iconClass + ' ' + icon + '" id="' + btnId + '"></button>').find('#' + btnId).on('click', function (e) {
                itaMobileGridChangePage('prev', $grid);
            });
            title = "Indietro";
            icon = "ui-icon-carat-r";
            btnId = id + "_goNextPage";
            $footerPaginator.append('<button type="button" title="' + title + '" class="' + iconClass + ' ' + icon + '" id="' + btnId + '"></button>').find('#' + btnId).on('click', function (e) {
                itaMobileGridChangePage('next', $grid);
            });
        }

        if (navButtonPrint) {
            title = "Stampa Elenco";
            icon = "ui-icon-ita-icon ita-icon-gridprint";
            btnId = id + "_printTableToHTML";
            $footerIcons.append('<button type="button" title="' + title + '" class="' + icon + ' ' + iconClass + ' " id="' + btnId + '"></button>').find('#' + btnId).on('click', function (e) {
                itaGo('ItaForm', $grid, {
                    event: 'printTableToHTML',
                    validate: false
                });
            });
        }

        if (navButtonExcel) {
            title = "Esporta Excel";
            icon = "ui-icon-ita-icon ita-icon-excel";
            btnId = id + "_exportTableToExcel";
            $footerIcons.append('<button type="button" title="' + title + '" class="' + iconClass + ' ' + icon + '" id="' + btnId + '"></button>').find('#' + btnId).on('click', function (e) {
                itaGo('ItaForm', $grid, {
                    event: 'exportTableToExcel',
                    validate: false
                });
            });
        }

        if (navButtonRefresh) {
            title = "Aggiorna";
            // icon = "ui-icon-ita-icon ita-icon-rotate-right-16x16";
            icon = "ui-icon-recycle";
            btnId = id + "_onClickTablePager";
            $footerIcons.append('<button type="button" title="' + title + '" class="' + iconClass + ' ' + icon + '" id="' + btnId + '"></button>').find('#' + btnId).on('click', function (e) {
                itaMobileGridPostdata($grid);
            });
        }

        if (navButtonDel) {
//            title = "Cancella";
//            icon = "ui-icon-delete";
//            btnId = id + "_delGridRow";
//            $footer.append('<button type="button" title="' + title + '" class="' + iconClass + ' ' + icon + '" id="' + btnId + '"></button>').find('#' + btnId).on('click', function (e) {
//                var rowid = gridParams[id]['rowid'];
//                if (rowid !== null) {
//                    itaGo('ItaForm', $grid, {
//                        event: 'delGridRow',
//                        validate: false,
//                        rowid: rowid
//                    });
//                }
//            });
        }

        if (navButtonEdit) {
//            title = "Modifica";
//            icon = "ui-icon-edit";
//            btnId = id + "_editGridRow";
//            $footer.append('<button type="button" title="' + title + '" class="' + iconClass + ' ' + icon + '" id="' + btnId + '"></button>').find('#' + btnId).on('click', function (e) {
//                var rowid = gridParams[id]['rowid'];
//                if (rowid !== null) {
//                    itaGo('ItaForm', $grid, {
//                        event: 'editGridRow',
//                        validate: false,
//                        rowid: rowid
//                    });
//                }
//            });
        }

        if (navButtonAdd) {
            title = "Aggiungi";
            icon = "ui-icon-plus";
            btnId = id + "_addGridRow";
            $footerIcons.append('<button type="button" title="' + title + '" class="' + iconClass + ' ' + icon + '" id="' + btnId + '"></button>').find('#' + btnId).on('click', function (e) {
                itaGo('ItaForm', $grid, {
                    event: 'addGridRow',
                    validate: false
                });
            });
        }

        if (navButtonColch) {
            //			title = "Configura Colonne";
            //			icon = "";
            //			btnId = id + "_gridColch";
            //			$footer.append('<button type="button" title="'+title+'" class="'+iconClass+' '+icon+'" id="'+btnId+'"></button>').find('#'+btnId).on('click', function(e) {
            //				var idObj = $("#" + id);
            //				itaGo('ItaForm', idObj, {
            //					event: 'printTableToHTML',
            //					validate: false
            //				});
            //			});
        }
    }
}

function mobileButtonbarReflow(buttonBar) {
    var $buttonBar = (buttonBar instanceof jQuery ? buttonBar : $('#' + buttonBar));
    var buttonsLength = $buttonBar.find('button:visible').length;
    var grid = 'abcdef ';
    if (buttonsLength > 0) {
        buttonsLength -= 1;
        $buttonBar.find('ul').removeClass().addClass('ui-grid-' + grid.substr(buttonsLength - 1, 1));
        for (var i = 0; i <= buttonsLength; i++) {
            if (buttonsLength == 0)
                grid = ' ';
            $buttonBar.find('button:visible').eq(i).closest('li').removeClass().addClass('ui-block-' + grid.substr(i, 1));
        }

        if ($buttonBar.parents('.ui-popup').length < 1) {
            $('#' + $buttonBar.parent().attr('id').split('_')[0] + '_wrapper').css('padding-bottom', $buttonBar.data('ita-height') + 'px');
        } else {
//            $('#' + $buttonBar.parent().attr('id').split('_')[0] + '_wrapper').css('padding-bottom', '0');
        }

        $buttonBar.find('button').css('height', $buttonBar.data('ita-height') + 'px');
    } else {
        $('#' + $buttonBar.parent().attr('id').split('_')[0] + '_wrapper').css('padding-bottom', '0');
    }
}

/**
 * Include una libreria di itaEngine
 * @param {String} Nome del file da includere
 * @param {String} Nome della libreria, se non presente cerca fra le librerie di Italsoft
 * @param {String} Se specificato, controlla prima la presenza del namespace sotto lo scope di "window"
 * @returns {Boolean}
 */
function itaGetLib(filename /* , libname, namespace */) {
    var ext = filename.split('.').pop();
    var libname = arguments[1] || '';
    var namespace = arguments[2] || false;

    if (libname !== '')
        libname = '&libname=' + libname;
    if (namespace)
        if (typeof window[namespace] !== 'undefined')
            return false;

    if (ext === 'js') {
        $.ajaxSetup({async: false});
        $.getScript('getItaLib.php?lib=' + filename + libname);
        $.ajaxSetup({async: true});
    } else if (ext === 'css') {
        $.get('getItaLib.php?lib=' + filename + libname, function (data) {
            $('head').append('<style>' + data + '</style>');
        });
    }

    return true;
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

    $elemento.removeAttr('readonly').removeClass('ita-readonly');

    if ($elemento.is('select, input[type="checkbox"], input[type="radio"]')) {
        $elemento.removeAttr('disabled');
    }

    buttons.forEach(function ($button) {
        $button.css('visibility', 'initial');
    });

    itaInputMaskWithin($elemento.parent());
}

function disableField(id) {
    var $elemento = $('#' + id);
    var buttons = [];

    if ($elemento.hasClass('ita-datepicker')) {
        buttons.push($elemento.parent().next());
    } else if ($elemento.hasClass('ita-edit-lookup')) {
        buttons.push($('#' + id + '_butt'));
    } else if ($elemento.hasClass('ita-edit-upload')) {
        buttons.push($('#' + id + '_upld_uploader'));
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