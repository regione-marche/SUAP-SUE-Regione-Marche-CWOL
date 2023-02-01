/* jQuery plugin themeswitcher
---------------------------------------------------------------------*/
$.fn.themeswitcher = function(settings){
    var options = jQuery.extend({
        loadTheme: null,
        initialText: 'Cambia tema',
        width: 100,
        height: 200,
        buttonPreText: 'Tema: ',
        closeOnSelect: true,
        buttonHeight: 11,
        cookieName: 'jquery-ui-theme',
        onOpen: function(){},
        onClose: function(){},
        onSelect: function(){}
    }, settings);
    //markup
    var button = $('<a href="#" class="jquery-ui-themeswitcher-trigger" onclick="return false;"><span class="jquery-ui-themeswitcher-title">'+ options.initialText +'</span></a>');
    var switcherpane='';
    var testSwitch;
    (typeof($.url(true).param('test')) == 'undefined' || $.url(true).param('test') == '') ?  testSwitch = '' :testSwitch = '&test='+$.url(true).param('test') ;
    $.ajax({
        url:'public/themes.php?m=get'+testSwitch,
        async: false,
        success:function(resp){
            switcherpane = $(resp);
        }
    });

    //button events
    button.click(
        function(){
            if(switcherpane.is(':visible')){
                switcherpane.spHide();
            }
            else{
                switcherpane.spShow();
            }
            return false;
        }
        );

    //menu events (mouseout didn't work...)
    switcherpane.hover(
        function(){},
        function(){
            if(switcherpane.is(':visible')){
                $(this).spHide();
            }
        }
        );

    //show/hide panel functions
    $.fn.spShow = function(){
        $(this).css({
            top: button.offset().top + options.buttonHeight + 6,
            left: button.offset().left
        }).slideDown(50);
        button.css(button_active);
        options.onOpen();
    }
    $.fn.spHide = function(){
        $(this).slideUp(50, function(){
            options.onClose();
        });
        button.css(button_default);
    }


    /* Theme Loading
	---------------------------------------------------------------------*/
    switcherpane.find('a').click(function(){
        updateCSS( $(this).attr('href') );
        var themeName = $(this).find('span').text();
        button.find('.jquery-ui-themeswitcher-title').html( options.buttonPreText );
        $.cookie(options.cookieName, themeName);
        options.onSelect();
        if(options.closeOnSelect && switcherpane.is(':visible')){
            switcherpane.spHide();
        }
        return false;
    });

    //function to append a new theme stylesheet with the new style changes
    function updateCSS(locStr){

        var cssLink = $('<link href="'+locStr+'" type="text/css" rel="Stylesheet" class="ui-theme" />');
        $("link.ui-theme").after(cssLink);
        $("link.ui-theme:first").remove();
    }

    /* Inline CSS
	---------------------------------------------------------------------*/
    var button_default = {
        fontFamily: 'Trebuchet MS',
        textDecoration: 'none',
        display: 'block',
        height: options.buttonHeight,
        outline: '0',
        marginTop: '3px',
        marginRight: '3px'
    };
    var button_hover = {
        cursor: 'pointer'
    };
    var button_active = {
        outline: '0'
    };



    //button css
    button.css(button_default)
    .hover(
        function(){
            $(this).css(button_hover);
        },
        function(){
            if( !switcherpane.is(':animated') && switcherpane.is(':hidden') ){
                $(this).css(button_default);
            }
        }
        )
    .find('.jquery-ui-themeswitcher-icon').css({
        float: 'right',
        width: '16px',
        height: '16px'
    });
    //pane css
    switcherpane.css({
        position: 'absolute',
        float: 'left',
        fontFamily: 'Trebuchet MS, Verdana, sans-serif',
        fontSize: '12px',
        background: '#eee',
        color: '#fff',
        padding: '8px 3px 3px',
        borderTop: 0,
        zIndex: 999999,
        width: options.width-6//minus must match left and right padding
    })
    .find('ul').css({
        listStyle: 'none',
        margin: '0',
        padding: '0',
        overflow: 'auto',
        height: options.height
    }).end()
    .find('li').hover(
        function(){
            $(this).css({
                cursor: 'pointer'
            });
        },
        function(){
            $(this).css({
                'background': '#eee',
                cursor: 'auto'
            });
        }
        ).css({
        width: options.width-30,
        height: '',
        padding: '2px',
        margin: '1px',
        borderBottom: '1px solid #ccc'
    }).end()
    .find('a').css({
        color: '#aaa',
        textDecoration: 'none',
        display: 'block',
        width: '100%',
        outline: '0'
    }).end()
    .find('img').css({
        float: 'left',
        margin: '0 2px',
        width:'70px'
    }).end()
    .find('.themeName').css({
        textAlign: 'center',
        fontSize: '.8em',
        display: 'block'
    }).end();



    $(this).append(button);
    $('body').append(switcherpane);
    switcherpane.hide();
    if( $.cookie(options.cookieName) || options.loadTheme ){
        var themeName = $.cookie(options.cookieName) || options.loadTheme;
        switcherpane.find('a:contains('+ themeName +')').trigger('click');
    }

    return this;
};