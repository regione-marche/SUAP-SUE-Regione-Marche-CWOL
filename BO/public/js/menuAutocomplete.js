function menuAutocomplete(searchField,menuMaster){
    var _this = this;
    this.cache = {};
    this.searchField = $('#'+searchField);
    if(typeof menuMaster == "undefined"){
        this.menuMaster = $('#menButtonPopup_popupcontent');
    }
    else{
        this.menuMaster = $('#'+menuMaster);
    }
    
    this.searchField.autocomplete({
        minLength: 2,
        source: function(request,response){
            var term = request.term;
            if(!(term in _this.cache)){
                itaGo('ItaCall','',{
                    asyncCall: false,
                    bloccaui: false,
                    model: 'menButton',
                    event: 'searchProgram',
                    msgData: request
                });
            }
            response(_this.cache[term]);
            $('.ui-autocomplete').css({
                            'max-height': '70%',
                            'overflow-y': 'auto',
                            'overflow-x': 'hidden'
                        });
        },
        select: function(event,ui){
            var menu = ui.item.id.split('|')[0];
            var prog = ui.item.id.split('|')[1];
            itaGo('ItaCall','',{
                model: 'menButton',
                event: 'onClick',
                prog: prog,
                menu: menu
            });
            
            $(this).val("");
            event.preventDefault();
            _this.menuMaster.css('display','none');
        }
    });
    
    this.addCache = function(term,data){
        _this.cache[term] = data;
    }
};