function getProcSportSeries(idTabella) {
    // Nomi
    var columns = $('#' + idTabella + ' thead th').map(function() {
        return $(this).text();
    });
    
    // Enti (categorie)
    var nomiCategorie = $('#' + idTabella + ' tbody th').map(function() {
        return $(this).text();
    });
   
    // Valori
    var datiTable = $('#' + idTabella + ' tbody td').map(function() {
        return $(this).text();
    });

    // Metto i valori in un array adatto a Highcharts
    var dati = new Array();
    var j = 0;
    var num_val = datiTable.length / columns.length;
    //for (var column in columns) {
    for (var k = 0; k < columns.length; k++) {
        dati[j] = new Object();
        dati[j].name = columns[k];
        var arr = new Array();
        for (var i = 0; i < num_val; i++) {
            arr[i] = parseFloat(datiTable[k + columns.length * i]);
        }
        dati[j].data = arr;
        j++;
    }
    
    // Inserisco le informazioni degli enti
    dati.itaExtras = new Object();
    dati.itaExtras.categorie = new Array();
    for (var i = 0; i < nomiCategorie.length; i++) {
        dati.itaExtras.categorie.push('Ente ' + nomiCategorie[i]);
    }
    return dati;
}

function itaJQChartProcSport(id, dataForChart){
    var obj = $('#'+id).metadata();
    var chart;
    
    chart = new Highcharts.Chart({
		chart: {
			renderTo: obj.container,
			type: obj.type
		},
		title: {
			text: obj.caption
		},
		subtitle: {
			text: obj.subcaption
		},
		xAxis: {
			categories: [
				'1',
				'2',
				'3'
			]
		},
		yAxis: {
			min: 0,
			title: {
				text: 'Quantita (num)'
			}
		},
		legend: {
			layout: 'vertical',
			backgroundColor: '#FFFFFF',
			align: 'left',
			verticalAlign: 'top',
			x: 100,
			y: 70,
			floating: true,
			shadow: true
		},
		tooltip: {
			formatter: function() {
				return '<b>' + this.x +'</b><br/>' +
                                    this.series.name + ': ' + this.y +'<br/>' +
                                    'Totale: ' + this.point.stackTotal;
			}
		},
		plotOptions: {
                        column: {
				stacking: 'normal',
				dataLabels: {
					enabled: true,
					color: (Highcharts.theme && Highcharts.theme.dataLabelsColor) || 'white'
				}
			}
		},
			series: dataForChart
	});
    
    chart.xAxis[0].setCategories(dataForChart.itaExtras.categorie);
    return chart;
}

function sceltaSportello(idTabella) {
    var scelta = $('select#seleziona_sportello').val();
    var dati = getProcSportSeries(idTabella);
    
    // Swap dei valori
    var num_swap = 0;
    for (var i = 0; i < dati.length; i++) {
        if (dati[i].name == scelta) {
            num_swap = i;
        }
    }
    var temp = dati[0];
    dati[0] = dati[num_swap];
    dati[num_swap] = temp;
    
    // Ridisegno tutto
    var chart = itaJQChartProcSport(idTabella, dati);
}