<?php

class ifoCalendar extends itaModelFO {

    public $ifoErr;
    public $ITALWEB_DB;
    
    private $transientData;
    private $defaultDate;
    
    public function __construct() {
        parent::__construct();

        try {
            /*
             * Caricamento librerie.
             */
            $this->ifoErr = new frontOfficeErr();

            /*
             * Caricamento database.
             */
            $this->ITALWEB_DB = ItaDB::DBOpen('ITALWEB', frontOfficeApp::getEnte());
        } catch (Exception $e) {
            
        }
    }

    public function parseEvent() {        
        switch ($this->request['event']) {
            default:                
                $defaultDate = ($this->defaultDate != null ? $this->defaultDate: date('Y-m-d'));                                  
                if ($this->request['transient'] === true) {                    
                    if (!isset($this->transientData['calendar'])) {
                        output::addAlert('Dati calendario non presenti.', 'Attenzione', 'warning');
                        output::addBr();
                    } else {
                        $calendario_rec = $this->transientData['calendar'];
                    }                     
                } else {
                    $sql = "SELECT
                                TITOLO, ALTRI
                            FROM CAL_CALENDARI
                            WHERE
                                ROWID = '{$this->config['id']}'";

                    $calendario_rec = ItaDB::DBSQLSelect($this->ITALWEB_DB, $sql, false);                    
                }                

                if (!$calendario_rec) {
                    output::addAlert('Il calendario impostato non è stato trovato.', 'Attenzione', 'warning');
                    output::addBr();
                }

                if ($calendario_rec && substr($calendario_rec['ALTRI'], 0, 1) != '1') {
                    output::addAlert('Il calendario impostato non è visualizzabile pubblicamente.', 'Attenzione', 'warning');
                    output::addBr();
                } else {
                    output::appendHtml('<h1 style="text-align: center;">' . $calendario_rec['TITOLO'] . '</h1>');
                    output::addBr();
                }

                output::appendHtml('<div id="fullcalendar"></div>');

                output::appendHtml(<<<SCRIPT
<script>
$('#fullcalendar').fullCalendar({
    nowIndicator: true,
    weekNumbers: true,
    weekNumbersWithinDays: true,
    timeFormat: 'H:mm',            
    defaultDate: '$defaultDate',   
    eventSources: [ {
        url: ajax.url,
            data: {
            action: ajax.action,
            model: ajax.model,
            data: ajax.data,
            event: 'fetchEvents'
        }
    } ],
    header: {
        left:   'prev,next today',
        center: 'title',
        right:  'month,agendaWeek,listWeek'
    },
    eventRender: function(event, element) {
        if ( event.description ) { element.addClass('italsoft-tooltip--click').attr('title', event.description); }
    },
    eventAfterAllRender: function() {
        itaFrontOffice.parse($('#fullcalendar'));
        $('body').removeClass('italsoft-loading');
    },
    loading: function(loading, view) {
        if ( loading ) {
            $('body').addClass('italsoft-loading');
        }
    }
});
                         
$('.fc-left').css('width', $('.fc-right').width() + 'px');
</script>
SCRIPT
                );
                break;

            case 'fetchEvents':
                if ($this->request['transient'] === true) {                    
                    if (!isset($this->transientData['calendarEvents'])) {
                        $eventi_tab = array();
                    } else {
                        $eventi_tab = $this->transientData['calendarEvents'];
                    }
                } else {
                    $formattedIniz = str_replace('-', '', substr($this->request['start'], 0, 10)) . '000000';
                    $formattedFine = str_replace('-', '', substr($this->request['end'], 0, 10)) . '000000';

                    $sql = "SELECT
                                TITOLO, START, END, ALLDAY, DESCRIZIONE
                            FROM CAL_EVENTI
                            WHERE
                                ROWID_CALENDARIO = '{$this->config['id']}' AND
                                START > $formattedIniz AND
                                START < $formattedFine";

                    $eventi_tab = ItaDB::DBSQLSelect($this->ITALWEB_DB, $sql);
                }
                
                $fullcalendar_events = array();
                foreach ($eventi_tab as $eventi_rec) {
                    $fullcalendar_events[] = array(
                        'title' => $eventi_rec['TITOLO'],
                        'start' => $this->formatDateTime($eventi_rec['START']),
                        'end' => $eventi_rec['END'] ? $this->formatDateTime($eventi_rec['END']) : '',
                        'allDay' => (boolean) $eventi_rec['ALLDAY'],
                        'description' => $eventi_rec['DESCRIZIONE']
                    );
                }

                output::$ajax_out = (array) $this->utf8EncodeArray($fullcalendar_events);
                output::ajaxSendResponse();
                break;
        }

        return output::$html_out;
    }

    private function utf8EncodeArray($array) {
        foreach ($array as $idx => $record) {
            foreach ($record as $key => $value) {
                $array[$idx][$key] = is_string($value) ? utf8_encode($value) : $value;
            }
        }

        return $array;
    }

    private function formatDateTime($str) {
        return substr($str, 0, 4) . '-' . substr($str, 4, 2) . '-' . substr($str, 6, 2) . 'T' . substr($str, 8, 2) . ':' . substr($str, 10, 2) . ':' . substr($str, 12, 2);
    }
    
    public function getTransientData() {
        return $this->transientData;
    }

    public function setTransientData($transientData) {
        $this->transientData = $transientData;
    }
    
    public function getDefaultDate() {
        return $this->defaultDate;
    }

    public function setDefaultDate($defaultDate) {
        $this->defaultDate = $defaultDate;
    }
    
}
