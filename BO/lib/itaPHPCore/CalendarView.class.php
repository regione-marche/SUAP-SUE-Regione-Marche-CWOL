<?php

/**
 *  * PHP Version 5
 *
 * @category   CORE
 * @package    /lib/itaPHPCorre
 * @author     Carlo Iesari <carlo@iesari.me>
 * @copyright  1987-2014 Italsoft snc
 * @license 
 * @version    08.10.2014
 * @link
 * @see
 * @since
 * @deprecated
 * */
require_once ITA_BASE_PATH . '/apps/Ambiente/envLibCalendar.class.php';

class CalendarView {

    private $calendarId;

    /**
     * Costruttore CalendarView
     *              
     * @param String $calendarId  L'ID della griglia
     */
    function __construct($calendarId = '') {
        $this->calendarId = $calendarId;
    }

    /**
     * @param String $view month, basicWeek, basicDay, agendaWeek, agendaDay
     */
    public function changeView($view = 'month') {
        Out::codice("$('#" . $this->calendarId . "').fullCalendar('changeView', '$view');");
    }

    /**
     * @param String $to prev, next
     */
    public function changePage($to) {
        if ($to == 'prev' || $to == 'next')
            Out::codice("$('#" . $this->calendarId . "').fullCalendar('$to');");
    }

    /**
     * @param String $date Formati accettati da moment.js. Se false va alla data di oggi
     */
    public function gotoDate($date = false) {
        if ($date)
            Out::codice("$('#" . $this->calendarId . "').fullCalendar('gotoDate', '$date');");
        else
            Out::codice("$('#" . $this->calendarId . "').fullCalendar('today');");
    }

    /**
     * Definisce una data o una range di date da selezionare.
     * @param String $start Formati accettati da moment.js.
     * @param String $end Opzionale. La selezione su $end è esclusiva ($end = 'Martedì', seleziona fino a Lunedì)
     */
    public function setSelection($start, $end = false) {
        if ($end)
            Out::codice("$('#" . $this->calendarId . "').fullCalendar('select', '$start', '$end');");
        else
            Out::codice("$('#" . $this->calendarId . "').fullCalendar('select', '$start');");
    }

    public function resetSelection() {
        Out::codice("$('#" . $this->calendarId . "').fullCalendar('unselect');");
    }

    public function getOwnIcon() {
        return '<span class="ui-icon ui-icon-home ui-icon-white" style="float: left;"></span>';
    }

    public function getGroupIcon() {
        return '<span class="ui-icon ui-icon-person ui-icon-white" style="float: left;"></span>';
    }

    public function getOtherIcon() {
        return '<span class="ui-icon ui-icon-calendar ui-icon-white" style="float: left;"></span>';
    }

    public function getAppIcon() {
        return '<span class="ui-icon ui-icon-gear ui-icon-white" style="float: left;"></span>';
    }

    public function getGoogleIcon() {
        return '<span class="ui-icon ui-icon-google ui-icon-white" style="float: left;"></span>';
    }

    public function getPropIcon($prop) {
        switch ($prop) {
            case envLibCalendar::PROP_OWN_EVENT:
                return $this->getOwnIcon();
                break;
            case envLibCalendar::PROP_GROUP_EVENT:
                return $this->getGroupIcon();
                break;
            case envLibCalendar::PROP_OTHER_EVENT:
                return $this->getOtherIcon();
                break;
            case envLibCalendar::PROP_APP_EVENT:
                return $this->getAppIcon();
                break;
            case envLibCalendar::PROP_GOOGLE_EVENT:
                return $this->getGoogleIcon();
                break;
        }
    }

    /**
     * Aggiunge gli eventi al calendario tramite un array di array associativi.
     * array( array( 'id' => 0 ), array( 'id' => 1 ), ... )
     * I parametri principali di un evento sono:
     *      title, start, (id,) (allDay,) (end,)
     * Lista di tutti i parametri: http://fullcalendar.io/docs/event_data/Event_Object/
     * 
     * @param String $events Array di array associativi
     * @param Boolean $icons
     */
    public function addEventsArray($events, $icons = true) {
        if ($icons) {
            foreach ($events as &$event) {
                if (is_array($event)) {
                    $event['icon'] = $this->getPropIcon($event['property']);

                    /*
                     * Fix per codifica errata (problemi con caratteri
                     * accentati e altro)
                     */
                    foreach (array('title', 'descrizione', 'calname') as $key) {
                        $event[$key] = utf8_encode($event[$key]);
                    }
                }
            }
        }

        $object = json_encode($events);
        Out::codice("$('#" . $this->calendarId . "').fullCalendar('addEventSource', $object);");
        //Out::codice("$('#" . $this->calendarId . "').fullCalendar('rerenderEvents');");
    }

    public function addEventsSource($source, $icons = true) {
        if ($icons) {
            $source['icon'] = $this->getPropIcon($source['property']);
        }
        $object = json_encode($source);
        Out::codice("fullCalendarAddEventSource('" . $this->calendarId . "', $object);");
        //Out::codice("$('#" . $this->calendarId . "').fullCalendar('rerenderEvents');");
    }

    /**
     * Rimuove tutti gli eventi o un singolo evento tramite l'id.
     * 
     * @param Number $id Id dell'evento
     */
    public function removeEvents($id = false) {
        if ($id)
            Out::codice("$('#" . $this->calendarId . "').fullCalendar('removeEvents', '$id');");
        else
            Out::codice("$('#" . $this->calendarId . "').fullCalendar('removeEvents');");
        Out::codice("fullCalendarRemoveEventSources('" . $this->calendarId . "');");
    }

    public function refresh() {
        Out::codice("$('#" . $this->calendarId . "').fullCalendar('refetchEvents');");
        //Out::codice("$('#" . $this->calendarId . "').fullCalendar('rerenderEvents');");
    }

}

?>
