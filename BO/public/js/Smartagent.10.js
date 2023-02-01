/**
 *  Smartagent 
 *  Versione: 1.0 - 15.10.2015
 *  2015 APRA
 *
 * 
 *--------------------------------------------------------------------------*/
var Smartagent = {};
//handshake per funzionamento smartagent
Smartagent.handshake = function (host, formName, componentId, event) {
    $.ajax({
        url: "http://" + host + "/handshake/",
        type: 'GET',
        contentType: "application/json",
        dataType: "jsonp",
        timeout: 1000
    }).always(function (dataOrJqXHR, textStatus, jqXHROrErrorThrown) {
        Smartagent.smartAgentCompleteCallback(dataOrJqXHR, textStatus, jqXHROrErrorThrown, formName, componentId, event);
    });
};
//SmartCart Pknet Firma il documento 
Smartagent.smartCardSign = function (host, source, signMode, encoding, filterValidCred, multiple, formName, componentId, event) {
    var locationCallback = unescape(location.pathname).substring(0, unescape(location.pathname).lastIndexOf("/")) + "/";
    if (multiple === "1") {
        //callback multipla nel caso di firma per più file 
        callbackname = "SaSmartcardSignMultipleCallback";
    } else {
        callbackname = "SaSmartcardSignCallback";
    }

    var url_callback = locationCallback + 'public/sacallback/' + callbackname + '.php';

    var url = 'http://' + host + '/smartCardSign/';

    var mode = 0; //htpp

    $.ajax({
        type: "POST",
        url: url,
        data: {
            'mode': mode,
            'sign_mode': signMode,
            'encoding': encoding,
            'filter_valid_cred': filterValidCred,
            'multiple': multiple,
            'source': source,
            'url_callback': url_callback
        },
        contentType: "application/x-www-form-urlencoded;",
        crossDomain: true,
        timeout: 300000
    }).always(
            function (dataOrJqXHR, textStatus, jqXHROrErrorThrown) {
                Smartagent.smartAgentCompleteCallback(dataOrJqXHR, textStatus, jqXHROrErrorThrown, formName, componentId, event);
            });
};
//SmartCart Pknet Verifica il documento 
Smartagent.smartCardVerify = function (host, sign, data, formName, componentId, event) {
    var locationCallback = unescape(location.pathname).substring(0, unescape(location.pathname).lastIndexOf("/")) + "/";
    var url_callback = locationCallback + 'public/sacallback/SaSmartcardVerifyCallback.php';

    var url = 'http://' + host + '/smartCardVerifyPost/';
    var mode = 0;
    var signmode = 0;

    $.ajax({
        type: "POST",
        url: url,
        data: {
            'mode': mode,
            'sign_mode': signmode,
            'source': sign,
            'data': data,
            'url_callback': url_callback
        },
        contentType: "application/x-www-form-urlencoded;",
        crossDomain: true
    }).always(
            function (dataOrJqXHR, textStatus, jqXHROrErrorThrown) {
                Smartagent.smartAgentCompleteCallback(dataOrJqXHR, textStatus, jqXHROrErrorThrown, formName, componentId, event);
            });
};
//SmartCart Pknet Ritorna le informazioni dei firmatari 
Smartagent.smartCardSignersInfo = function (host, sign, data, formName, componentId, event) {
    var url = 'http://' + host + '/smartCardSignersInfoPost/';
    var mode = 0;
    var signmode = 0;

    $.ajax({
        type: "POST",
        url: url,
        data: {
            'mode': mode,
            'sign_mode': signmode,
            'source': sign,
            'data': data
        },
        contentType: "application/x-www-form-urlencoded;",
        crossDomain: true
    }).always(
            function (dataOrJqXHR, textStatus, jqXHROrErrorThrown) {
                Smartagent.smartAgentCompleteCallback(dataOrJqXHR, textStatus, jqXHROrErrorThrown, formName, componentId, event);
            });
};
//Scanner Driver wia
Smartagent.wiaScan = function (host, forcePdf, color, quality, forceClose, formName, componentId, event) {
    var locationCallback = unescape(location.pathname).substring(0, unescape(location.pathname).lastIndexOf("/")) + "/";
    var url_callback = locationCallback + 'public/sacallback/SaWiaScanCallback.php';
    var url = 'http://' + host + '/wiaScan/?url_callback=' + url_callback;
    if (typeof (forcePdf) !== 'undefined' && forcePdf !== null) {
        url = url + '&force_pdf=' + forcePdf;
    }
    ;
    if (typeof (color) !== 'undefined' && color !== null) {
        url = url + '&color=' + color;
    }
    ;
    if (typeof (quality) !== 'undefined' && quality !== null) {
        url = url + '&quality=' + quality;
    }
    ;
    if (typeof (forceClose) !== 'undefined' && forceClose !== null) {
        url = url + '&force_close=' + forceClose;
    }
    ;
    $.ajax({
        url: url,
        type: 'GET',
        contentType: "application/json",
        dataType: "jsonp"
    }).always(function (dataOrJqXHR, textStatus, jqXHROrErrorThrown) {
        Smartagent.smartAgentCompleteCallback(dataOrJqXHR, textStatus, jqXHROrErrorThrown, formName, componentId, event);
    });
};
//Scanner Driver twain
Smartagent.twainScan = function (host, forcePdf, color, quality, forceClose, formName, componentId, event) {
    var locationCallback = unescape(location.pathname).substring(0, unescape(location.pathname).lastIndexOf("/")) + "/";
    var url_callback = locationCallback + 'public/sacallback/SaTwainScanCallback.php';
    var url = 'http://' + host + '/twainSingleScan/?url_callback=' + url_callback;
    if (typeof (forcePdf) !== 'undefined' && forcePdf !== null) {
        url = url + '&force_pdf=' + forcePdf;
    }
    ;
    if (typeof (color) !== 'undefined' && color !== null) {
        url = url + '&color=' + color;
    }
    ;
    if (typeof (quality) !== 'undefined' && quality !== null) {
        url = url + '&quality=' + quality;
    }
    ;
    if (typeof (forceClose) !== 'undefined' && forceClose !== null) {
        url = url + '&force_close=' + forceClose;
    }
    ;
    $.ajax({
        url: url,
        type: 'GET',
        contentType: "application/json",
        dataType: "jsonp"
    }).always(function (dataOrJqXHR, textStatus, jqXHROrErrorThrown) {
        Smartagent.smartAgentCompleteCallback(dataOrJqXHR, textStatus, jqXHROrErrorThrown, formName, componentId, event);
    });
};
//Scanner Driver isis
Smartagent.isisScan = function (host, forcePdf, show_ui, color, quality, formName, componentId, event) {
    var locationCallback = unescape(location.pathname).substring(0, unescape(location.pathname).lastIndexOf("/")) + "/";
    var url_callback = locationCallback + 'public/sacallback/SaIsisScanCallback.php';
    var url = 'http://' + host + '/twainIsisScan/?url_callback=' + url_callback;

    if (typeof (color) !== 'undefined' && color !== null) {
        url = url + '&color=' + color;
    }
    ;
    if (typeof (show_ui) !== 'undefined' && show_ui !== null) {
        url = url + '&show_ui=' + show_ui;
    }
    ;

    if (typeof (forcePdf) !== 'undefined' && forcePdf !== null) {
        url = url + '&force_pdf=' + forcePdf;
    }
    ;

    if (typeof (quality) !== 'undefined' && quality !== null) {
        url = url + '&quality=' + quality;
    }
    ;
    $.ajax({
        url: url,
        type: 'GET',
        contentType: "application/json",
        dataType: "jsonp"
    }).always(function (dataOrJqXHR, textStatus, jqXHROrErrorThrown) {
        Smartagent.smartAgentCompleteCallback(dataOrJqXHR, textStatus, jqXHROrErrorThrown, formName, componentId, event);
    });
};
//Chimata shellExec
Smartagent.shellExec = function (host, cmd, args, hidden, procname, formName, componentId, event) {
    var locationCallback = unescape(location.pathname).substring(0, unescape(location.pathname).lastIndexOf("/")) + "/";
    var url = 'http://' + host + '/shellExec/?' + 'cmd=' + cmd + '&args=' + args;
    if (hidden !== null) {
        url = url + '&hidden=' + hidden;
    }
    url = url + '&useOmnis=' + false;
    url = url + '&procname=' + procname;

    setTimeout(function () {
        $.ajax({
            url: url,
            type: 'GET',
            contentType: "application/json",
            dataType: "jsonp",
            timeout: 2500
        }).always(function (dataOrJqXHR, textStatus, jqXHROrErrorThrown) {
            Smartagent.smartAgentCompleteCallback(dataOrJqXHR, textStatus, jqXHROrErrorThrown, formName, componentId, event);
        });
    }, 1000);
};
//Apertura remoteAppExec
Smartagent.remoteAppExec = function (host, cmd, args, procname, formName, componentId, event) {
    var locationCallback = unescape(location.pathname).substring(0, unescape(location.pathname).lastIndexOf("/")) + "/";
    setTimeout(function () {
        $.ajax({
            url: 'http://' + host + '/remoteAppExec/?' + 'cmd=' + cmd + '&args=' + args + '&procname=' + procname,
            type: 'GET',
            contentType: "application/json",
            dataType: "jsonp",
            timeout: 2500
        }).always(function (dataOrJqXHR, textStatus, jqXHROrErrorThrown) {
            Smartagent.smartAgentCompleteCallback(dataOrJqXHR, textStatus, jqXHROrErrorThrown, formName, componentId, event);
        });
    }, 1000);
};
//Torna il nome della macchina 
Smartagent.getMachineName = function (host, formName, componentId, event) {
    setTimeout(function () {
        $.ajax({
            url: 'http://' + host + '/getMachineName/',
            type: 'GET',
            contentType: "application/json",
            dataType: "jsonp",
            timeout: 2500
        }).always(function (dataOrJqXHR, textStatus, jqXHROrErrorThrown) {
            Smartagent.smartAgentCompleteCallback(dataOrJqXHR, textStatus, jqXHROrErrorThrown, formName, componentId, event);
        });
    }, 1000);
};
//Imposta il nome della macchina 
Smartagent.setMachineName = function (host, machineName, formName, componentId, event) {
    setTimeout(function () {
        $.ajax({
            url: 'http://' + host + '/setMachineName/',
            type: 'POST',
            data: {
                'MACHINE_NAME': machineName
            },
            contentType: "application/x-www-form-urlencoded;",
            timeout: 2500
        }).always(function (dataOrJqXHR, textStatus, jqXHROrErrorThrown) {
            Smartagent.smartAgentCompleteCallback(dataOrJqXHR, textStatus, jqXHROrErrorThrown, formName, componentId, event);
        });
    }, 1000);
};
//Effettua il downloadFile
Smartagent.downloadFile = function (host, fileName, url_download, formName, componentId, event) {
    var locationCallback = unescape(location.pathname).substring(0, unescape(location.pathname).lastIndexOf("/")) + "/";
    url_download = locationCallback + url_download;
    setTimeout(function () {
        $.ajax({
            url: 'http://' + host + '/downloadFile/?url_download=' + url_download + '&filename=' + fileName,
            type: 'GET',
            contentType: "application/json",
            dataType: "jsonp",
            timeout: 300000
        }).always(function (dataOrJqXHR, textStatus, jqXHROrErrorThrown) {
            Smartagent.smartAgentCompleteCallback(dataOrJqXHR, textStatus, jqXHROrErrorThrown, formName, componentId, event);
        });
    }, 1000);
};
//Effettua la firma grafometrica usando il software namirial
Smartagent.namirialSignature = function (host, device, certificate, source, biometricData, noPdfSignInfo, makePdfOriginal, saveInSameFolder, forceOverwrite, formName, componentId, event) {
    var locationCallback = unescape(location.pathname).substring(0, unescape(location.pathname).lastIndexOf("/")) + "/";
    var url_callback = locationCallback + 'public/sacallback/SaNamirialSignatureCallback.php';
    $.ajax({
        type: "POST",
        url: "http://" + host + "/namirialFEASign/",
        data: {
            'device': device,
            'certificate': certificate,
            'source': source,
            'url_callback': url_callback,
            'biometric_data': biometricData,
            'no_pdf_sign_info': noPdfSignInfo,
            'make_pdf_original': makePdfOriginal,
            'save_in_same_folder': saveInSameFolder,
            'force_overwrite': forceOverwrite
        },
        contentType: "application/x-www-form-urlencoded;",
        crossDomain: true
    }).always(function (dataOrJqXHR, textStatus, jqXHROrErrorThrown) {
        var escapeResult = atob(dataOrJqXHR.substring(1, dataOrJqXHR.length - 1));
        Smartagent.smartAgentCompleteCallback(escapeResult, textStatus, jqXHROrErrorThrown, formName, componentId, event);
    });
};
//Effettua la verifca della firma grafometrica usando il software namirial
Smartagent.namirialVerifySignature = function (host, source, formName, componentId, event) {
    var locationCallback = unescape(location.pathname).substring(0, unescape(location.pathname).lastIndexOf("/")) + "/";
    var url_callback = locationCallback + 'public/sacallback/SaNamirialVerifySignatureCallback.php';
    $.ajax({
        type: "POST",
        url: "http://" + host + "/namirialFEAVerify/",
        data: {
            'source': source,
            'url_callback': url_callback
        },
        contentType: "application/x-www-form-urlencoded;",
        crossDomain: true
    }).always(function (dataOrJqXHR, textStatus, jqXHROrErrorThrown) {
        var escapeResult = atob(dataOrJqXHR.substring(1, dataOrJqXHR.length - 1));
        Smartagent.smartAgentCompleteCallback(escapeResult, textStatus, jqXHROrErrorThrown, formName, componentId, event);
    });
};
//Callback
Smartagent.smartAgentCompleteCallback = function (dataOrJqXHR, textStatus, jqXHROrErrorThrown, formName, componentId, event) {
    var data, jqXhr;

    if ("success" === textStatus) {
        data = dataOrJqXHR;
        jqXhr = jqXHROrErrorThrown;
    } else {
        jqXhr = dataOrJqXHR;
        data = [];
    }

    if (formName) {
        itaGo('ItaCall', '', {
            id: componentId,
            event: event,
            model: formName,
            data: data,
            status: jqXhr.status,
            statusText: jqXhr.statusText,
            validate: false
        });
    }

};


