(function (window, undefined) {

    window.Asc.plugin.init = function () {        
        window.Asc.plugin.executeMethod("GetAllContentControls");

        // Caricamento dizionario
        $.ajax({
            type: "GET",
            url: window.ooPluginConfig.endpoint,
            headers: {
                'X-Ita-Token': window.ooPluginConfig.token
            },
            data: {
                classificazione: window.ooPluginConfig.classificazione
            },
            success: function (data) {
                createDOM(data);
            }
        });
                
        // Crea DOM
        function createDOM(data) {                  
            var $main = $('#main');
            var $table = $('<table id="tree-table" class="table table-hover table-bordered"></table>');
            var $thead = $('<thead></thead>');
            var $tbody = $('<tbody></tbody>');
            var $trHead = $('<tr></tr>');
            
            // Table header
            $trHead.append('<th style="width: 5%;"></th>');
            var $thCode = $('<th style="width: 25%;"></th>').text('Codice');
            $trHead.append($thCode);
            var $thVariable = $('<th style="width: 30%;"></th>').text('Variabile');
            $trHead.append($thVariable);
            var $thDescription = $('<th style="width: 40%;"></th>').text('Descrizione');
            $trHead.append($thDescription);

            $thead.append($trHead);
            $table.append($thead);
			
			$tbody.append('<tr class="table-filters"><th></th><th><input type="text" style="width: 100%;"></th><th><input type="text" style="width: 100%;"></th><th><input type="text" style="width: 100%;"></th></tr>');
            
            // Table data         
            for (var i in data) {
                (function(entry) {
                    if (entry.chiave.length === 0) {
                        return;
                    }                
                    
                    var classTd = entry.isLeaf == 'true' ? 'dictionary-element' : 'dictionary-parent';
                    
                    var $tr = $('<tr data-id="' + entry.varidx + '" data-parent="' + entry.parent + '" data-level="' + entry.level + '" data-dictkey="' + entry.markupkey + '"></tr>');
                    var $tdExpand = $('<td data-expand></td>');
                    $tr.append($tdExpand);
                    var $tdCode = $('<td class="' + classTd + '" data-column="name">' + entry.chiave + '</td>');
                    $tr.append($tdCode);
                    var $tdVariable = $('<td class="' + classTd + '">' + entry.markupkey + '</td>');
                    $tr.append($tdVariable);
                    var $tdDescription = $('<td class="' + classTd + '">' + entry.descrizione + '</td>');
                    $tr.append($tdDescription);
                    $tbody.append($tr);                
                })(data[i]);
            }

            $table.append($tbody);
            $main.append($table);     

            renderTreeTable();
        }

        function renderTreeTable() {
            var
                    $table = $('#tree-table'),
                    rows = $table.find('tr');

            rows.each(function (index, row) {
                var
                        $row = $(row),
                        level = $row.data('level'),
                        id = $row.data('id'),
                        $columnName = $row.find('td[data-expand]'),
                        children = $table.find('tr[data-parent="' + id + '"]');

                if (children.length) {
                    var expander = $columnName.prepend('' +
                            '<span class="treegrid-expander glyphicon glyphicon-chevron-right"></span>' +
                            '');

                    children.hide();

                    expander.on('click', function (e) {
                        var $target = $(e.target);
						if ($target.is('.glyphicon')) {
                            if ($target.hasClass('glyphicon-chevron-right')) {
                                $target
                                        .removeClass('glyphicon-chevron-right')
                                        .addClass('glyphicon-chevron-down');

                                children.show();
                            } else {
                                $target
                                        .removeClass('glyphicon-chevron-down')
                                        .addClass('glyphicon-chevron-right');

                                reverseHide($table, $row);
                            }
                        }
                    });
                }

                $columnName.prepend('' +
                        '<span class="treegrid-indent" style="width:' + 5 * level + 'px"></span>' +
                        '');
            });

            // Reverse hide all elements
            reverseHide = function (table, element) {
                var
                        $element = $(element),
                        id = $element.data('id'),
                        children = table.find('tr[data-parent="' + id + '"]');

                if (children.length) {
                    children.each(function (i, e) {
                        reverseHide(table, e);
                    });

                    $element
                            .find('.glyphicon-chevron-down')
                            .removeClass('glyphicon-chevron-down')
                            .addClass('glyphicon-chevron-right');

                    children.hide();
                }
            };

            $table.find('.table-filters input').on('keyup', function () {
                applyFilters($table);
            });
        }
		
		function applyFilters($table) {
			var allEmpty = true;

			$table.find('tbody tr').show();

			$table.find('.table-filters input').each(function() {
				var idx = $(this).parent().index();
				var src = this.value && this.value.trim() ? this.value.trim().toLowerCase() : false;
                var $trs = $table.find('.dictionary-element').parent(),
					$trsParents = $table.find('.dictionary-parent').parent();
				
				if (!src) {
					return;
				}
				
				allEmpty = false;
					
				$trsParents.hide();

				$trs.each(function() {
					if ( $(this).find('td').eq(idx).text().toLowerCase().indexOf(src) < 0 ) {
						$(this).hide();
					}
				});
			});
			
			if (allEmpty) {
				$table.find('tbody [data-level="1"]').show();
				$table.find('tbody tr').not('[data-level="1"], .table-filters').hide();
			} else {
				$table.find('.glyphicon-chevron-down').removeClass('glyphicon-chevron-down').addClass('glyphicon-chevron-right');
			}
		}

        // Click su voce dizionario
        $('body').on('click', '.dictionary-element', function (event) {            
            $target = $(event.currentTarget);            
            insertText($target.parent().data("dictkey"));            
        });
    };

    window.Asc.plugin.button = function (id) {
        if (id === -1) {
            this.executeCommand("close", "");
        }
    };

    function insertText(text) {
        window.Asc.plugin.executeMethod("PasteHtml", [text]);
        
        // Blocco di codice necessario per problema focus su editor
        var sScript = "var oDocument = Api.GetDocument();";
        window.Asc.plugin.info.recalculate = true;        
        window.Asc.plugin.executeCommand("command", sScript);        
        
        window.Asc.plugin.button(-1);
    }

})(window, undefined);
