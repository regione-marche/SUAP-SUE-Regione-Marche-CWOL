function zoomControl(zoomButton, dezoomButton){
    var that = this;
    that.zoomButton = $('#'+zoomButton);
    that.dezoomButton = $('#'+dezoomButton);
    if($.browser.mozilla){
        that.browser = 'mozilla';
        that.currZoom = 1;
        that.step = 0.02;
    }
    else{
        that.browser = 'other';
        that.currZoom = 100;
        that.step = 2;
    }
    
    that.zoomButton.on('click', function(){
        that.currZoom += that.step;
        if(that.browser == 'mozilla'){
            $('body').css('MozTransform','scale('+that.currZoom+')');
        }
        else{
            $('body').css('zoom',' '+that.currZoom+'%');
        }
    });
    
    that.dezoomButton.on('click', function(){
        that.currZoom -= that.step;
        if(that.browser == 'mozilla'){
            $('body').css('MozTransform','scale('+that.currZoom+')');
        }
        else{
            $('body').css('zoom',' '+that.currZoom+'%');
        }
    });
}

function autoZoom(modelName, targetWidth){
    var element = $('#tab-'+modelName+'Body');
    var windowWidth = $(document).width();
    
    var scale = windowWidth/targetWidth;
    parseFloat(scale.toFixed(2));
    if(!$.browser.mozilla){
        scale *= 100;
    }
    
    console.log(element);
    console.log(windowWidth);
    console.log(scale);
    
    if($.browser.mozilla){
        element.css('MozTransform','scale('+scale+')');
    }
    else{
        element.css('zoom',' '+scale+'%');
    }
    element.css('width','1000px');
    element.css('height','1000px');
}