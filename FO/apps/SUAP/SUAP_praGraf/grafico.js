// NOTA: Per i sorgenti dei vari grafici, vedere i file nella stessa cartella
//         es: graphPie.js, graphColumn.js, ecc..

// Variabile globale che contiene il chart
var globalChart = 0;

/* CREA I GRAFICI */
function insData(tipo, idTabella) {
    switch (tipo) {
        case 'proc_tot':
            var procTotData = getProcTotSeries(idTabella);
            globalChart = itaJQChartProcTot(idTabella, procTotData);
            break;
        case 'proc_mese':
            var procMeseData = getProcMeseSeries(idTabella);
            globalChart = itaJQChartProcMese(idTabella, procMeseData);
            break;
        case 'proc_sport':
            var procSportData = getProcSportSeries(idTabella);
            globalChart = itaJQChartProcSport(idTabella, procSportData);
            break;
        case 'proc_sett':
            var procSettData = getProcSettSeries(idTabella);
            globalChart = itaJQChartProcSett(idTabella, procSettData);
            break;
		case 'proc_segn':
            var procSegnData = getProcSegnSeries(idTabella);
            globalChart = itaJQChartProcSegn(idTabella, procSegnData);
            break;
        default:
            break;
    }
}