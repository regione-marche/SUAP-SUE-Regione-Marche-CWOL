function getProcMeseSeries(idTabella) {
    // Nomi
    var columns = $('#' + idTabella + ' thead th').map(function() {
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
    return dati;
}

function itaJQChartProcMese(id, dataForChart){
    var obj = $('#'+id).metadata();
    //var caption=$('#'+id).find("caption").nodeValue();
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
			text: ''//obj.caption
		},
		xAxis: {
			categories: [
				'Gennaio',
				'Febbraio',
				'Marzo',
                                'Aprile',
                                'Maggio',
                                'Giugno',
                                'Luglio',
                                'Agosto',
                                'Settembre',
                                'Ottobre',
                                'Novembre',
                                'Dicembre'
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
				return ''+
					this.x +': '+ this.y;
			}
		},
		plotOptions: {
			column: {
				pointPadding: 0.2,
				borderWidth: 0
			}
		},
			series: dataForChart
	});
    return chart;
}