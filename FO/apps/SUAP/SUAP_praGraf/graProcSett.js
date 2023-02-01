function getProcSettSeries(idTabella) {
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
    for(var i=0; i < datiTable.length; i++){
        dati[i] = new Array(2);
        dati[i][0] = columns[i];
        dati[i][1] = parseFloat(datiTable[i]);
    }
    return dati;
}

function itaJQChartProcSett(id, dataForChart){
    var obj = $('#'+id).metadata();
    //var caption=$('#'+id).find("caption").nodeValue();
    var chart = 0;
    chart = new Highcharts.Chart({
        chart: {
            renderTo: obj.container,
            plotBackgroundColor: null,
            plotBorderWidth: null,
            plotShadow: false
        },
        title: {
            text: obj.caption
        },
        tooltip: {
            formatter: function() {
                return '<b>'+ this.point.name +'</b>: '+ this.point.y +' %';
            }
        },
        plotOptions: {
            pie: {
                allowPointSelect: true,
                cursor: 'pointer',
                dataLabels: {
                    enabled: true,
                    color: '#000000',
                    connectorColor: '#000000',
                    formatter: function() {
                        return '<b>'+ this.point.name +'</b>: '+ this.point.y +' %';
                    }
                }
            }
        },
        series: [{
            type: obj.type,
            name: 'nome',
            data: dataForChart
        }]
    });
    return chart;
}