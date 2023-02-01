
var ie,ieurl,ieuser,iepass,iedomain,ietoken;

function setIeUrl(xurl){
    ieurl=xurl;
}

function setIeUser(user){
    ieuser=user;
}

function setIePass(pass){
    iepass=pass;
}

function setIeDomain(domain){
    iedomain=domain;
}

function setIeToken(token){
    ietoken=token;
}

function ieLancia(param){
    var encodedParam="";
    for(var propertyName in param) {
        encodedParam += "&"+propertyName+"="+param[propertyName];   
    }
    if(ie !== undefined && typeof ie.itaGo == 'function') {
        ie.itaGo('ItaCall','',param);
    }else{
        ie=null;
        ie=window.open(ieurl+"?accesstoken="+ietoken+"&accessorg="+iedomain+"&access=direct"+encodedParam);                                        
    }        
}
