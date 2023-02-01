$(function(){ 
    $(".ita-upload-dest-add-button").each(function(){
        $(this).click(function(){
            var incId = parseInt($("div.ita-div-upload-dest:last").attr("id").substring(8,9));
            incId = incId + 1;
            $(".ita-div-upload-dest-template").eq(0).clone(true, true).removeClass("ita-div-upload-dest-template").insertAfter("div.ita-div-upload-dest:last");
            var delButton = "<button id=\"removeDest_"+incId+"\" name=\"removeDest["+incId+"]\"";
            delButton += "style=\"font-size: .8em; margin-left:5px;\""; 
            delButton += "class=\"ita-upload-dest-remove-button italsoft-button italsoft-button--secondary\" type=\"button\">";
            delButton += "<div class=\"buttonAddRem ion-minus italsoft-icon\"></div>";
            delButton += "</button>";
            $("div.ita-div-upload-dest:last").append(delButton);
            $("#removeDest_"+incId).each(function(){
                $(this).click(function(){
                    $("#divDest_"+incId).remove();
                    return;
                });
            });
            $("div.ita-div-upload-dest:last").attr("id","divDest_"+incId);
            $("select.ita-select-upload-dest:last").attr("id","QualificaAllegato_DESTINAZIONE_"+incId).attr("name","QualificaAllegato[DESTINAZIONE]["+incId+"]");
            $("button.ita-upload-dest-add-button:last").attr("id","addDest_"+incId).attr("name","addDest["+incId+"]");
            return;
        });
    });
    
    

});
    
