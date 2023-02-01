function moveNext(fldObj) {
    return moveIndex(fldObj, "next");
}

function movePrev(fldObj) {
    return moveIndex(fldObj, "prev");
}

function moveIndex(fldObj, i) {
    // get the current position and elements
    var aPos = getFieldPosition(fldObj);

    // if a string option has been specified, calculate the position
    if (i == "next") {
        if (typeof ($(aPos[1][aPos[0]]).metadata().nextField) != 'undefined') {
            return $('#' + protSelector($(aPos[1][aPos[0]]).parents('form').attr('id') + '_' + $(aPos[1][aPos[0]]).metadata().nextField));

        }

        i = aPos[0] + 1;
    } else if (i == "prev") {
        if (typeof ($(aPos[1][aPos[0]]).metadata().prevField) != 'undefined') {
            return $('#' + protSelector($(aPos[1][aPos[0]]).parents('form').attr('id') + '_' + $(aPos[1][aPos[0]]).metadata().prevField));
        }

        i = aPos[0] - 1;
    }

    // make sure the index position is within the bounds of the elements array

    if (i < 0)
        i = aPos[1].length - 1;
    else if (i >= aPos[1].length)
        i = 0;

    return aPos[1][i];
}

function getTabIndex(fldObj) {
    // return the position of the form field
    return getFieldPosition(fldObj);
}

function getFieldPosition(jq) {
    var myPage = $(jq).parents('.ita-data-page')[0];

    // get the first matching field
    $field = $(jq).filter("input, select, textarea").get(0),
            // store items with a tabindex
            aTabIndex = [],
            // store items with no tabindex
            aPosIndex = [];

    // if there is no match, return 0
    if (!$field)
        return [-1, []];

    // make a single pass thru all form elements
    //$(myPage).find("input, select, textarea").each(function (){
    $(myPage).find("input:password,input:text,input[type='tel'], select, textarea").each(function () {
        if (this.tagName != "FIELDSET" && !this.disabled && $(this).hasClass('ita-readonly') == false && $(this).is(':visible')) {
            if (this.tabIndex > 0) {
                aTabIndex.push(this);
            } else {
                aPosIndex.push(this);
            }
        }
    });

    // sort the fields that had tab indexes
    aTabIndex.sort(
            function (a, b) {
                return a.tabIndex - b.tabIndex;
            }
    );

    // merge the elements to create the correct tab position
    aTabIndex = $.merge(aTabIndex, aPosIndex);

    for (var i = 0; i < aTabIndex.length; i++) {
        if (aTabIndex[i] == $field)
            return [i, aTabIndex];
    }

    return [-1, aTabIndex];
}

//@TODO VERIFICARE SE SI TROVA NATIVAMENTE  
function addslashes(str) {
    return (str + '').replace(/([\\"'])/g, "\\$1").replace(/\u0000/g, "\\0");
}

//@TODO VERIFICARE SE SI TROVA NATIVAMENTE  
function ita_lcwords(str, force) {
    return str.toLowerCase();
//    return str.replace(/([A-Z])/g,
//            function (ch) {
//                return   ch.toLowerCase();
//            });
}

//@TODO VERIFICARE SE SI TROVA NATIVAMENTE  
function ita_ucwords(str, force) {
    return str.toUpperCase();
//    str = force ? str.toLowerCase() : str;
//    return str.replace(/([a-z])/g, function (ch) {
//        return   ch.toUpperCase();
//    });
}

//@TODO VERIFICARE SE SI TROVA NATIVAMENTE  
function ita_ucfirst(str, force) {
    str = force ? str.toLowerCase() : str;
    return str.replace(/([a-z])/,
            function (ch) {
                return   ch.toUpperCase();
            });
}

function ita_get_pointer_index(dom) {
    var pos = 0;
    if (document.selection) {
        dom.focus();
        var selection = document.selection.createRange();
        selection.moveStart('character', -dom.value.length);
        pos = selection.text.length;
    } else if (dom.selectionStart || dom.selectionStart == '0') {
        pos = dom.selectionStart;
    }

    return pos;
}

function ita_set_pointer_index(dom, pos) {
    if (dom.setSelectionRange) {
        dom.focus();
        dom.setSelectionRange(pos, pos);
    } else if (dom.createTextRange) {
        var range = dom.createTextRange();
        range.collapse(true);
        range.moveEnd('character', pos);
        range.moveStart('character', pos);
        range.select();
    }
}

function ita_iso_encode(string) {
    string = String(string);

    var iso_encode_charmap = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789@*_+-./',
            length = string.length,
            R = '',
            k = 0,
            char,
            byte;

    for (; k < length; k++) {
        if (~iso_encode_charmap.indexOf(string[k])) {
            R += string[k];
        } else {
            char = string[k].charCodeAt(0),
                    byte = char.toString(16).toUpperCase();

            if (char >= 256) {
                R += '%u' + ((byte.length === 3 ? '0' : '') + byte);
            } else {
                R += '%' + ((byte.length === 1 ? '0' : '') + byte);
            }
        }
    }

    return R;
}

function validateMetadata(string) {
    try {
        $('<p>').attr('class', string).metadata();
    } catch (e) {
        return false;
    }

    return true;
}