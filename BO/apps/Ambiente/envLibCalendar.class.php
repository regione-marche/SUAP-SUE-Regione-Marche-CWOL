<?php

/**
 *
 * PHP Version 5
 *
 * @category
 * @package
 * @author     Carlo Iesari <carlo@iesari.me>
 * @copyright  1987-2014 Italsoft snc
 * @license
 * @version    09.10.2014
 * @link
 * @see
 * @since
 * @deprecated
 * */
include_once ITA_BASE_PATH . '/apps/Ambiente/envLib.class.php';

class envLibCalendar {

    /**
     * Libreria di funzioni Generiche e Utility per Calendario
     */
    public $envLib;

    const PROP_OWN_EVENT = 0;
    const PROP_GROUP_EVENT = 1;
    const PROP_OTHER_EVENT = 2;
    const PROP_APP_EVENT = 3;
    const PROP_GOOGLE_EVENT = 4;

    private $classi = array(
        "PASSI_SUAP",
        "SUAP_PRATICA",
        "SEGR_INDICE",
        "TRIB_ADESIONE",
        "BDA_AGGIUDICAZIONEGARAGES",
        "BDA_COMUNICAZIONINONAGG",
        "BDA_CONTRATTOGARA",
        "BDAELENCOT",
        "TIM_TURNI"
    );
    private $errMessage;
    private $errCode;
    public $ITALWEB_DB;
    public $ITW_DB;

    function __construct() {
        try {
            $this->envLib = new envLib();
            $this->ITALWEB_DB = ItaDB::DBOpen('ITALWEB');
            $this->ITW_DB = ItaDB::DBOpen('ITW');
            // $this->personalCalendarCheck();
        } catch (Exception $e) {
            Out::msgStop("Errore", $e->getMessage());
        }
    }

    public function getErrMessage() {
        return $this->errMessage;
    }

    public function setErrMessage($errMessage) {
        $this->errMessage = $errMessage;
    }

    public function getErrCode() {
        return $this->errCode;
    }

    public function setErrCode($errCode) {
        $this->errCode = $errCode;
    }

    private function classIsValid($class) {
        return in_array($class, $this->classi);
    }

    /**
     * Ritorna il rowid del nuovo evento.
     * @param Integer $calId
     * @param String $title
     * @param String $description
     * @param Integer $start YYYYMMDDHHMMSS
     * @param Integer $end YYYYMMDDHHMMSS
     * @param Integer $allDay 1 / 0
     */
    public function insertEvent($calId, $title, $description, $start, $end, $allDay) {
        if (strlen($start) < 8) {
            $this->setErrCode(-1);
            $this->setErrMessage('Data inizio non valida');
            return false;
        }
        $start = str_pad($start, 14, 0, STR_PAD_RIGHT);

        if (strlen($end) < 8) {
            $this->setErrCode(-1);
            $this->setErrMessage('Data fine non valida');
            return false;
        }
        $end = str_pad($end, 14, 0, STR_PAD_RIGHT);

        $evt = array(
            'TITOLO' => $title,
            'DESCRIZIONE' => $description,
            'START' => $start,
            'END' => $end,
            'ALLDAY' => $allDay,
            'ROWID_CALENDARIO' => $calId
        );
        if (ItaDB::DBInsert($this->ITALWEB_DB, 'CAL_EVENTI', 'ROWID', $evt)) {
            return ItaDB::DBLastId($this->ITALWEB_DB);
        } else {
            $this->setErrCode(-1);
            $this->setErrMessage('Errore nella creazione dell\'evento');
            return false;
        }
    }

    /**
     * Ritorna il rowid del nuovo evento.
     * @param String $title
     * @param String $description
     * @param Integer $start YYYYMMDDHHMMSS
     * @param Integer $end YYYYMMDDHHMMSS
     * @param Integer $allDay 1 / 0
     * @param String $app Classe valida (vedere $this->classi)
     * @param Integer $rowid
     * @param type $meta Serializzato dalla funzione
     * @param String $promTipo Tipo del promemoria (notifica, email)
     * @param Integer $promVal
     * @param Integer $promUnit Unità di tempo del promemoria in secondi ( 60 - minuti 3600 - ore, ecc.)
     * @param Integer $userId False prende utente attivo
     * @return boolean
     */
    public function insertEventApp($title, $description, $start, $end, $allDay, $app, $rowid, $meta, $promTipo = false, $promVal = false, $promUnit = false, $userId = false, $idCalendar = "") {
        if (strlen($start) < 8) {
            $this->setErrCode(-1);
            $this->setErrMessage('Data inizio non valida');
            return false;
        }
        $start = str_pad($start, 14, 0, STR_PAD_RIGHT);

        if (strlen($end) < 8) {
            $this->setErrCode(-1);
            $this->setErrMessage('Data fine non valida');
            return false;
        }
        $end = str_pad($end, 14, 0, STR_PAD_RIGHT);

        if (!$userId) {
            $userId = App::$utente->getIdUtente();
        }
        if ($idCalendar == "") {
            if (!($calId = $this->personalCalendarCheck($userId))) {
                return false;
            }
        } else {
            $calId = $idCalendar;
        }
        if ($this->classIsValid($app)) {
            $evt = array(
                'TITOLO' => $title,
                'DESCRIZIONE' => $description,
                'START' => $start,
                'END' => $end,
                'ALLDAY' => $allDay,
                'ROWID_CALENDARIO' => $calId,
                'CLASSEAPP' => $app,
                'CLASSEROWID' => $rowid,
                'CLASSEMETA' => serialize($meta)
            );
            if (ItaDB::DBInsert($this->ITALWEB_DB, 'CAL_EVENTI', 'ROWID', $evt)) {
                $evt_id = ItaDB::DBLastId($this->ITALWEB_DB);

                if ($promTipo && $promUnit && $promVal) {
                    if (!$this->addPromemoriaEvent($evt_id, $promTipo, $promVal, $promUnit)) {
                        return false;
                    }
//                    $prom_rec = array(
//                        'TIPO' => $promTipo,
//                        'TEMPO' => $promVal,
//                        'UNITA' => $promUnit,
//                        'ROWID_GENITORE' => $evt_id
//                    );
//                    if (ItaDB::DBInsert($this->ITALWEB_DB, 'CAL_PROMEMORIA', 'ROWID', $prom_rec)) {
//                        return $evt_id;
//                    } else {
//                        $this->setErrCode(-1);
//                        $this->setErrMessage('Errore nella creazione del promemoria');
//                        return false;
//                    }
                }

                return $evt_id;
            } else {
                $this->setErrCode(-1);
                $this->setErrMessage('Errore nella creazione dell\'evento');
                return false;
            }
        } else {
            $this->setErrCode(-1);
            $this->setErrMessage("La classe $app non è valida");
            return false;
        }
    }

    public function insertTodoApp($title, $description, $start, $app, $rowid, $meta, $promTipo = false, $promVal = false, $promUnit = false, $userId = false) {
        if (strlen($start) < 8) {
            $this->setErrCode(-1);
            $this->setErrMessage('Data inizio non valida');
            return false;
        }
        $start = str_pad($start, 14, 0, STR_PAD_RIGHT);

        if (!$userId) {
            $userId = App::$utente->getIdUtente();
        }

        if (!($calId = $this->personalCalendarCheck($userId) )) {
            return false;
        }

        if ($this->classIsValid($app)) {
            $evt = array(
                'TITOLO' => $title,
                'DESCRIZIONE' => $description,
                'START' => $start,
                'ROWID_CALENDARIO' => $calId,
                'CLASSEAPP' => $app,
                'CLASSEROWID' => $rowid,
                'CLASSEMETA' => serialize($meta)
            );
            if (ItaDB::DBInsert($this->ITALWEB_DB, 'CAL_ATTIVITA', 'ROWID', $evt)) {
                $todo_id = ItaDB::DBLastId($this->ITALWEB_DB);
                if ($promTipo && $promUnit && $promVal) {
                    if ($this->addPromemoriaTodo($todo_id, $promTipo, $promVal, $promUnit)) {
                        return $todo_id;
                    } else {
                        $this->setErrCode(-1);
                        $this->setErrMessage('Errore nella creazione del promemoria');
                        return false;
                    }
                }
            } else {
                $this->setErrCode(-1);
                $this->setErrMessage('Errore nella creazione dell\'attività');
                return false;
            }
        } else {
            $this->setErrCode(-1);
            $this->setErrMessage("La classe $app non è valida");
            return false;
        }
    }

    /**
     * Ritorna un array di record
     * @param type $app Nome applicativo
     * @param type $rowid Rowid applicativo
     */
    public function getAppEvents($app, $rowid, $multi = true) {
        $sql = "SELECT * FROM CAL_EVENTI WHERE CLASSEAPP = '$app' AND CLASSEROWID = '$rowid'";
        return ItaDB::DBSQLSelect($this->ITALWEB_DB, $sql, $multi);
    }

    public function getAppTodos($app, $rowid) {
        $sql = "SELECT * FROM CAL_ATTIVITA WHERE CLASSEAPP = '$app' AND CLASSEROWID = $rowid";
        return ItaDB::DBSQLSelect($this->ITALWEB_DB, $sql);
    }

    /**
     * Ritorna true / false.
     * @param String $title
     * @param String $description
     * @param Integer $start YYYYMMDDHHMMSS
     * @param Integer $end YYYYMMDDHHMMSS
     * @param Integer $allDay 1 / 0
     * @param String $app
     * @param Integer $rowid
     * @param type $meta Serializzato dalla funzione
     * @param String $promTipo Tipo del promemoria. (notifica, email) Se 'cancella' elimina i promemoria di questo evento
     * @param Integer $promVal
     * @param Integer $promUnit Unità di tempo del promemoria in secondi ( 60 - minuti 3600 - ore, ecc.)
     * @param Integer $userId INUTILIZZATO
     */
    public function updateEventApp($rowidEvt, $title, $description, $start, $end, $allDay, $meta, $promTipo = false, $promVal = false, $promUnit = false, $userId = false, $idCalendar = "") {
//        if (!$userId) {
//            $userId = App::$utente->getIdUtente();
//        }
//        if (!($calId = $this->personalCalendarCheck($userId))) {
//            return false;
//        }
        if ($start) {
            if (strlen($start) < 8) {
                $this->setErrCode(-1);
                $this->setErrMessage('Data inizio non valida');
                return false;
            }
            $start = str_pad($start, 14, 0, STR_PAD_RIGHT);
        }

        if ($end) {
            if (strlen($end) < 8) {
                $this->setErrCode(-1);
                $this->setErrMessage('Data fine non valida');
                return false;
            }
            $end = str_pad($end, 14, 0, STR_PAD_RIGHT);
        }

        $sql = "SELECT * FROM CAL_EVENTI WHERE ROWID = $rowidEvt";
        $event = ItaDB::DBSQLSelect($this->ITALWEB_DB, $sql, false);
        $event['TITOLO'] = $title ? $title : $event['TITOLO'];
        $event['DESCRIZIONE'] = $description ? $description : $event['DESCRIZIONE'];
        $event['START'] = $start ? $start : $event['START'];
        $event['END'] = $end ? $end : $event['END'];
        $event['ALLDAY'] = $allDay ? $allDay : $event['ALLDAY'];
        $event['CLASSEMETA'] = $meta ? $meta : serialize($event['CLASSEMETA']);
        if ($idCalendar) {
            $event['ROWID_CALENDARIO'] = $idCalendar;
        }
        if (ItaDB::DBUpdate($this->ITALWEB_DB, 'CAL_EVENTI', 'ROWID', $event)) {
            if ($promTipo == 'cancella') {
                if ($this->deletePromemoriaFromEvent($rowidEvt)) {
                    return true;
                } else {
                    return false;
                }
            } else if ($promTipo && $promUnit && $promVal) {
                if (!$this->deletePromemoriaFromEvent($rowidEvt)) {
                    return false;
                }
//
                if ($this->addPromemoriaEvent($rowidEvt, $promTipo, $promVal, $promUnit)) {
                    return true;
                } else {
                    $this->setErrCode(-1);
                    $this->setErrMessage('Errore nella creazione del promemoria');
                    return false;
                }
//                $prom_rec = array(
//                    'TIPO' => $promTipo,
//                    'TEMPO' => $promVal,
//                    'UNITA' => $promUnit,
//                    'ROWID_GENITORE' => $rowidEvt
//                );
//                if (ItaDB::DBInsert($this->ITALWEB_DB, 'CAL_PROMEMORIA', 'ROWID', $prom_rec)) {
//                    return true;
//                } else {
//                    $this->setErrCode(-1);
//                    $this->setErrMessage('Errore nella creazione del promemoria');
//                    return false;
//                }
            } else {
                return true;
            }
        } else {
            $this->setErrCode(-1);
            $this->setErrMessage('Errore nell\'update dell\'evento ' . $event['ROWID']);
            return false;
        }
    }

    public function updateTodoApp($rowidTodo, $title, $description, $start, $meta, $promTipo = false, $promVal = false, $promUnit = false) {
        if ($start) {
            if (strlen($start) < 8) {
                $this->setErrCode(-1);
                $this->setErrMessage('Data inizio non valida');
                return false;
            }
            $start = str_pad($start, 14, 0, STR_PAD_RIGHT);
        }

        $sql = "SELECT * FROM CAL_ATTIVITA WHERE ROWID = $rowidTodo";
        $todo = ItaDB::DBSQLSelect($this->ITALWEB_DB, $sql, false);
        $todo['TITOLO'] = $title ? $title : $todo['TITOLO'];
        $todo['DESCRIZIONE'] = $description ? $description : $todo['DESCRIZIONE'];
        $todo['START'] = $start ? $start : $todo['START'];
        $todo['CLASSEMETA'] = $meta ? $meta : serialize($todo['CLASSEMETA']);
        if (ItaDB::DBUpdate($this->ITALWEB_DB, 'CAL_ATTIVITA', 'ROWID', $todo)) {
            if ($promTipo == 'cancella') {
                if ($this->deletePromemoriaFromTodo($rowidTodo)) {
                    return true;
                } else {
                    return false;
                }
            } else if ($promTipo && $promUnit && $promVal) {
                if (!$this->deletePromemoriaFromTodo($rowidTodo)) {
                    return false;
                }
//
                if ($this->addPromemoriaTodo($rowidTodo, $promTipo, $promVal, $promUnit)) {
                    return true;
                } else {
                    $this->setErrCode(-1);
                    $this->setErrMessage('Errore nella creazione del promemoria');
                    return false;
                }
            } else {
                return true;
            }
        } else {
            $this->setErrCode(-1);
            $this->setErrMessage('Errore nell\'update dell\'attività ' . $todo['ROWID']);
            return false;
        }
    }

    /**
     * Ritorna true / false.
     * @param String $app
     * @param Integer $rowid
     * @param type $userId False prende utente di default, null elimina tutti gli eventi del tipo selezionato in tutti i calendari
     */
    public function deleteEventApp($app, $rowid, $userId = null, $idCalendar = "") {
        if (!is_null($userId)) {
            $userId = $userId !== false ? $userId : App::$utente->getIdUtente();
            if ($idCalendar == "") {
                if (!($calId = $this->personalCalendarCheck($userId))) {
                    return false;
                }
            } else {
                $calId = $idCalendar;
            }
        }

        $sql = "SELECT ROWID FROM CAL_EVENTI WHERE CLASSEAPP = '$app' AND CLASSEROWID = $rowid";

        if (isset($calId)) {
            $sql .= " AND ROWID_CALENDARIO = $calId";
        }

        $events = ItaDB::DBSQLSelect($this->ITALWEB_DB, $sql);
        foreach ($events as $event) {
            if (!ItaDB::DBDelete($this->ITALWEB_DB, 'CAL_EVENTI', 'ROWID', $event['ROWID'])) {
                $this->setErrCode(-1);
                $this->setErrMessage('Errore nell\'eliminazione dell\'evento ' . $event['ROWID']);

                return false;
            }
        }
        return true;
    }

    public function deleteTodoApp($app, $rowid, $userId = null) {
        if (!is_null($userId)) {
            $userId = $userId !== false ? $userId : App::$utente->getIdUtente();
            if (!($calId = $this->personalCalendarCheck($userId))) {
                return false;
            }
        }

        $sql = "SELECT ROWID FROM CAL_ATTIVITA WHERE CLASSEAPP = '$app' AND CLASSEROWID = $rowid";

        if (isset($calId)) {
            $sql .= " AND ROWID_CALENDARIO = $calId";
        }

        $todos = ItaDB::DBSQLSelect($this->ITALWEB_DB, $sql);
        foreach ($todos as $todo) {
            if (!ItaDB::DBDelete($this->ITALWEB_DB, 'CAL_ATTIVITA', 'ROWID', $todo['ROWID'])) {
                $this->setErrCode(-1);
                $this->setErrMessage('Errore nell\'eliminazione dell\'attività ' . $todo['ROWID']);

                return false;
            }
        }
    }

    private function personalCalendarCheck($userId = false, $create = false) {
        if (!$userId) {
            $userId = App::$utente->getIdUtente();
        }

        $sql = "SELECT * FROM CAL_CALENDARI WHERE UTENTE = '$userId' AND TIPO = 'APPLICATIVI'";
        $calendario_rec = ItaDB::DBSQLSelect($this->ITALWEB_DB, $sql, false);
        if (!$calendario_rec) {

            if ($create) {
                if (!($id_appcal = $this->createCalendar($userId, 'APPLICATIVI'))) {
                    return false;
                }
                return $id_appcal;
            } else {
                //$this->setErrCode(-1);
                //$this->setErrMessage("Calendario APPLICATIVI non trovato");
                return false;
            }
        } else {
            return $calendario_rec['ROWID'];
        }
    }

    public function getSelectedCalendars($onlyCals = true) {
        $sql = "SELECT * FROM ENV_UTEMETA WHERE UTECOD = " . App::$utente->getIdUtente() . " AND METAKEY = 'CaleCalendariSelezionati'";
        $meta = ItaDB::DBSQLSelect($this->ITALWEB_DB, $sql, false);
        if ($onlyCals) {
            if (!$meta || !$meta['METAVALUE']) {
                return false;
            }
            return unserialize($meta['METAVALUE']);
        } else {
            return $meta;
        }
    }

    public function setSelectedCalendars($calendars) {
        $record = $this->getSelectedCalendars(false);
        if (!$record) {
            $record = array(
                'UTECOD' => App::$utente->getIdUtente(),
                'METAKEY' => 'CaleCalendariSelezionati',
                'METAVALUE' => serialize($calendars)
            );
            try {
                ItaDB::DBInsert($this->ITALWEB_DB, 'ENV_UTEMETA', 'ROWID', $record);
            } catch (Exception $exc) {
                $this->setErrCode(-1);
                $this->setErrMessage($exc->getMessage());

                return false;
            }
        } else {
            //$sql = "SELECT * FROM ENV_UTEMETA WHERE UTECOD = " . App::$utente->getIdUtente() . " AND METAKEY = 'CaleCalendariSelezionati'";
            //$record = ItaDB::DBSQLSelect($this->ITALWEB_DB, $sql, false);
            $record['METAVALUE'] = serialize($calendars);
            try {
                ItaDB::DBUpdate($this->ITALWEB_DB, 'ENV_UTEMETA', 'ROWID', $record);
            } catch (Exception $exc) {
                $this->setErrCode(-1);
                $this->setErrMessage($exc->getMessage());

                return false;
            }
        }
        return true;
    }

//	public function sqlUserGroupsIds( $userId = false ) {
//		if ( !$userId ) {
//			$userId = App::$utente->getIdUtente();
//		}
//		
//        $sql = "SELECT " . $this->ITW_DB->getDB() . ".UTENTI.UTEGRU";
//		
//        for ( $i = 1; $i < 31; $i++ ) {
//			$sql .= ", " . $this->ITW_DB->getDB() . ".UTENTI.UTEGEX__$i";
//		}
//		
//        $sql .= "
//				FROM
//					" . $this->ITW_DB->getDB() . ".UTENTI
//				WHERE
//					" . $this->ITW_DB->getDB() . ".UTENTI.UTECOD = '$userId'";
//
//        $groups = ItaDB::DBSQLSelect($this->ITW_DB, $sql, false);
//
//        foreach ($groups as $group) {
//			if ( $group !== '' && $group !== '0' ) {
//				if ( isset( $result ) ) {
//					$result .= ", $group";
//				} else {
//					$result = $group;
//				}
//			}
//		}
//		
//        return "($result)";
//	}
//
//    public function sqlUsersGroups($ret_sql = false) {
//        $groups = $this->sqlUserGroupsIds();
//
//        $sql_users = "(SELECT
//                        " . $this->ITW_DB->getDB() . ".UTENTI.UTECOD
//                      FROM
//                        " . $this->ITW_DB->getDB() . ".UTENTI
//                      WHERE
//                        " . $this->ITW_DB->getDB() . ".UTENTI.UTEGRU IN $groups";
//        for ($i = 1; $i < 31; $i++)
//            $sql_users .= " OR UTENTI.UTEGEX__$i IN $groups ";
//        $sql_users .= "AND " . $this->ITW_DB->getDB() . ".UTENTI.UTECOD <> " . App::$utente->getIdUtente() . ")";
//        if ($ret_sql)
//            return $sql_users;
//        else {
//            $cods = array();
//            $users = ItaDB::DBSQLSelect($this->ITALWEB_DB, $sql_users);
//            foreach ($users as $user)
//                if ($user['UTECOD'])
//                    array_push($cods, $user['UTECOD']);
//            return $cods;
//        }
//    }

    public function sqlUsersGroups($return_sql = false, $userId = false) {
        if (!$userId) {
            $userId = App::$utente->getIdUtente();
        }

        $sql = "SELECT " . $this->ITW_DB->getDB() . ".UTENTI.UTEGRU";

        for ($i = 1; $i < 31; $i++) {
            $sql .= ", " . $this->ITW_DB->getDB() . ".UTENTI.UTEGEX__$i";
        }

        $sql .= "
				FROM
					" . $this->ITW_DB->getDB() . ".UTENTI
				WHERE
					" . $this->ITW_DB->getDB() . ".UTENTI.UTECOD = '$userId'";

        $groups = ItaDB:: DBSQLSelect($this->ITW_DB, $sql, false);

        $result = array();
        foreach ($groups as $group) {
            if ($group !== '' && $group !== '0') {
                $result_sql = isset($result_sql) ? $result_sql . ", $group" : $group;
                array_push($result, $group);
            }
        }


        if ($return_sql) {
            if (!isset($result_sql)) {
                $result_sql = "'NESSUN-GRUPPO'";
            }

            return "( $result_sql )";
        } else {
            return $result;
        }
    }

    public function getCalendars($perms = '1___') {
        $sql = "SELECT
                    CAL_CALENDARI.ROWID,
                    CAL_CALENDARI.TITOLO,
                    CAL_CALENDARI.METADATI,
                    CAL_CALENDARI.TIPO,
                    CAL_CALENDARI.GRUPPI,
                    CAL_CALENDARI.ALTRI,
                    CAL_CALENDARI.UTENTE AS IDUTENTE,
                    " . $this->ITW_DB->getDB() . ".UTENTI.UTELOG AS UTENTE
                FROM
                    CAL_CALENDARI
                LEFT OUTER JOIN
                    " . $this->ITW_DB->getDB() . ".UTENTI
                ON
                    CAL_CALENDARI.UTENTE = " . $this->ITW_DB->getDB() . ".UTENTI.UTECOD
                WHERE
                    ( CAL_CALENDARI.UTENTE = " . App::$utente->getIdUtente() . " AND CAL_CALENDARI.PROPRIETARIO LIKE '$perms' )
                OR
                    ( CAL_CALENDARI.GRUPPO IN " . $this->sqlUsersGroups(true) . " AND CAL_CALENDARI.GRUPPI LIKE '$perms' )
                OR
                    CAL_CALENDARI.ALTRI LIKE '$perms'
				ORDER BY UTENTE ASC, CAL_CALENDARI.ROWID ASC";

        $calendars = ItaDB::DBSQLSelect($this->ITALWEB_DB, $sql);
        return $calendars;
    }

    public function createCalendar($userId, $tipo = '') {
        $sql = "SELECT UTELOG FROM UTENTI WHERE UTECOD = '$userId'";
        $utenti_rec = ItaDB:: DBSQLSelect($this->ITW_DB, $sql, false);

        if (!$utenti_rec) {
            $this->setErrCode(-1);
            $this->setErrMessage("Utente $userId non trovato");

            return false;
        }

        $perms = $tipo == 'APPLICATIVI' ? '1000' : '1110';
        $calName = $tipo == 'APPLICATIVI' ? 'Calendario Applicativi ' . $utenti_rec['UTELOG'] : 'Calendario di ' . $utenti_rec['UTELOG'];
        $calendario_rec = array(
            'TITOLO' => $calName,
            'UTENTE' => $userId,
            'TIPO' => $tipo,
            'PROPRIETARIO' => $perms,
            'GRUPPI' => '0000',
            'ALTRI' => '0000'
        );
        if (ItaDB::DBInsert($this->ITALWEB_DB, 'CAL_CALENDARI', 'ROWID', $calendario_rec)) {
            App::log("Calendario {$tipo} creato");
            return ItaDB::DBLastId($this->ITALWEB_DB);
        } else {
            $this->setErrCode(-1);
            $this->setErrMessage("Errore nella creazione del calendario");

            return false;
        }
    }

    public function getCalendar($calendarId) {
        $sql = "SELECT * FROM CAL_CALENDARI WHERE ROWID = $calendarId";
        $calendario_rec = ItaDB::DBSQLSelect($this->ITALWEB_DB, $sql, false);
        return $calendario_rec;
    }

    public function deleteCalendar($calendarId) {
        ItaDB::DBDelete($this->ITALWEB_DB, 'CAL_CALENDARI', 'ROWID', $calendarId);
        $sql = "SELECT ROWID FROM CAL_EVENTI WHERE ROWID_CALENDARIO = $calendarId";
        $events = ItaDB::DBSQLSelect($this->ITALWEB_DB, $sql);
        foreach ($events as $event) {
            ItaDB::DBDelete($this->ITALWEB_DB, 'CAL_EVENTI', 'ROWID', $event['ROWID']);
        }
        return true;
    }

    public function isCalendarEmpty($calendarId) {
        $sql = "SELECT
                    CAL_EVENTI.ROWID AS E,
                    CAL_ATTIVITA.ROWID AS A
                FROM
                    CAL_CALENDARI
                LEFT OUTER JOIN CAL_EVENTI ON CAL_EVENTI.ROWID_CALENDARIO = CAL_CALENDARI.ROWID
                LEFT OUTER JOIN CAL_ATTIVITA ON CAL_ATTIVITA.ROWID_CALENDARIO = CAL_CALENDARI.ROWID
                WHERE
                    CAL_CALENDARI.ROWID = $calendarId";

        $calendari_rec = ItaDB::DBSQLSelect($this->ITALWEB_DB, $sql, false);
        return is_null($calendari_rec['E']) ? true : false;
    }

    public function getAllTodo($calendarsId, $priority = array(1, 2, 3), $done = false) {
        if (count($priority) > 0) {
            $prio_in = '(';
            foreach ($priority as $prio) {
                $prio_in .= $prio . ',';
            }
            $prio_in .= '0)';
        } else {
            $prio_in = '(0)';
        }

        $sql = "SELECT
                    CAL_ATTIVITA.*,
                    CAL_CALENDARI.TITOLO AS CALENDARIO,
                    CAL_CALENDARI.GRUPPI,
                    CAL_CALENDARI.ALTRI,
                    CAL_CALENDARI.UTENTE,
                    CAL_CALENDARI.GRUPPO
                FROM
                    CAL_ATTIVITA
                LEFT OUTER JOIN
                    CAL_CALENDARI
                ON
                    CAL_CALENDARI.ROWID = CAL_ATTIVITA.ROWID_CALENDARIO
                WHERE
                    (CAL_CALENDARI.ROWID = 0";

        foreach ($calendarsId as $id => $value) {
            if ($id && $value['visible']) {
                $sql .= ' OR CAL_CALENDARI.ROWID = ' . $id;
            }
        }

        $sql .= ") AND
                    CAL_ATTIVITA.PRIORITA IN " . $prio_in . "
                AND
                    (
                        ( CAL_CALENDARI.UTENTE = " . App::$utente->getIdUtente() . " AND CAL_CALENDARI.PROPRIETARIO LIKE '1___' )
                    OR
                        ( CAL_CALENDARI.GRUPPO IN " . $this->sqlUsersGroups(true) . " AND CAL_CALENDARI.GRUPPI LIKE '1___' )
                    OR
                        CAL_CALENDARI.ALTRI LIKE '1___'
                    )";
        $sql .= " AND CAL_ATTIVITA.COMPLETATO IN (" . ( $done ? '0, 1' : '0' ) . ")";

        return $sql;
    }

    public function selectEvents($calendarsId, $from, $to) {
        if (!is_array($calendarsId)) {
            $calendarsId = array($calendarsId => array('visible' => true));
        }

        $group_users = $this->sqlUsersGroups();

        $sql = 'SELECT
                    CAL_EVENTI.ROWID,
                    CAL_EVENTI.TITOLO,
                    CAL_EVENTI.DESCRIZIONE,
                    CAL_EVENTI.START,
                    CAL_EVENTI.END,
                    CAL_EVENTI.ALLDAY,
                    CAL_CALENDARI.ROWID AS ROWID_CALENDARIO,
                    CAL_EVENTI.CLASSEAPP,
                    CAL_CALENDARI.TITOLO AS CALNAME,
                    CAL_CALENDARI.METADATI,
                    CAL_CALENDARI.TIPO,
                    CAL_CALENDARI.GRUPPI,
                    CAL_CALENDARI.ALTRI,
                    CAL_CALENDARI.UTENTE,
                    CAL_CALENDARI.GRUPPO
                FROM
                    CAL_CALENDARI
                LEFT OUTER JOIN
                    CAL_EVENTI
                ON
                    CAL_CALENDARI.ROWID = CAL_EVENTI.ROWID_CALENDARIO
                WHERE
                    (CAL_CALENDARI.ROWID = 0';

        foreach ($calendarsId as $id => $value) {
            if ($id) {
                $sql .= ' OR CAL_CALENDARI.ROWID = ' . $id;
            }
        }

        $sql .= ") AND
                    (
                        ( START BETWEEN $from AND $to ) OR
                        ( START <= $to AND END >= $from )
                    )
                AND (
                        ( CAL_CALENDARI.UTENTE = " . App::$utente->getIdUtente() . " AND CAL_CALENDARI.PROPRIETARIO LIKE '1___' )
                    OR
                        ( CAL_CALENDARI.GRUPPO IN " . $this->sqlUsersGroups(true) . " AND CAL_CALENDARI.GRUPPI LIKE '1___' )
                    OR
                        CAL_CALENDARI.ALTRI LIKE '1___'
                    )
                ORDER BY ROWID_CALENDARIO ASC";

        $events = ItaDB::DBSQLSelect($this->ITALWEB_DB, $sql);
        foreach ($events as &$event) {
            $property = envLibCalendar::PROP_OTHER_EVENT;
            $editable = substr($event ['ALTRI'], 1, 1) == '1' ? true : false;
            if ($event['UTENTE'] == App::$utente->getIdUtente()) {
                $property = envLibCalendar::PROP_OWN_EVENT;
                $editable = true;
            } else if (in_array($event['GRUPPO'], $group_users) && substr($event ['GRUPPI'], 0, 1) == '1') {
                $property = envLibCalendar::PROP_GROUP_EVENT;
                $gruppi = substr($event ['GRUPPI'], 1, 1) == '1' ? true : false;
                $editable = $editable == false ? $gruppi : $editable;
            }

            if ($event ['CLASSEAPP'] !== '') {
                $property = envLibCalendar::PROP_APP_EVENT;
                $editable = false;
            }

            if ($event['TIPO'] == 'GOOGLE') {
                $meta = unserialize($event['METADATI']);
                $event = array(
                    'url' => $meta['url'],
                    'calendar' => $event['ROWID_CALENDARIO'],
                    'property' => envLibCalendar::PROP_GOOGLE_EVENT,
                    'calname' => $event['CALNAME']
                );
            } else {
                $proms = $this->getPromemoriaFromEvent($event['ROWID']);
                $hasProm = false;
                $promType = '';
                if (count($proms) > 0) {
                    $hasProm = $proms [0]['INVIATO'] > 0 ? false : ( $proms [0]['TEMPO'] * $proms[0]['UNITA'] );
                    $promType = $proms[0]['TIPO'];
                }

                $event = array(
                    'id' => $event['ROWID'],
                    'title' => $event['TITOLO'],
                    'descrizione' => $event['DESCRIZIONE'],
                    'start' => substr($event ['START'], 0, 4) . '-' .
                    substr($event ['START'], 4, 2) . '-' .
                    substr($event ['START'], 6, 2) . 'T' .
                    substr($event ['START'], 8, 2) . ':' .
                    substr($event ['START'], 10, 2) . ':' .
                    substr($event['START'], 12, 2),
                    'end' => $event['END'] ? substr($event ['END'], 0, 4) . '-' .
                    substr($event ['END'], 4, 2) . '-' .
                    substr($event ['END'], 6, 2) . 'T' .
                    substr($event ['END'], 8, 2) . ':' .
                    substr($event ['END'], 10, 2) . ':' .
                    substr($event['END'], 12, 2) : '',
                    'allDay' => $event ['ALLDAY'] == '1' ? true : false,
                    'property' => $property,
                    'editable' => $editable,
                    'calendar' => $event['ROWID_CALENDARIO'],
                    'calname' => $event['CALNAME'],
                    'hasProm' => $hasProm,
                    'promType' => $promType
                );
            }
        }
        return $events;
    }

    public function getEvent($eventId) {
        $sql = "SELECT * FROM CAL_EVENTI WHERE ROWID = $eventId";
        $eventi_rec = ItaDB::DBSQLSelect($this->ITALWEB_DB, $sql, false);
        return $eventi_rec;
    }

    public function getTodo($todoId) {
        $sql = "SELECT * FROM CAL_ATTIVITA WHERE ROWID = $todoId";
        return ItaDB::DBSQLSelect($this->ITALWEB_DB, $sql, false);
    }

    public function isEventDeletable($eventId) {
        $sql = "SELECT CAL_CALENDARI.GRUPPI, CAL_CALENDARI.ALTRI, CAL_CALENDARI.UTENTE, CAL_CALENDARI.GRUPPO
                FROM CAL_EVENTI
                LEFT OUTER JOIN CAL_CALENDARI ON CAL_CALENDARI.ROWID = CAL_EVENTI.ROWID_CALENDARIO
                WHERE CAL_EVENTI.ROWID = $eventId";
        $event = ItaDB::DBSQLSelect($this->ITALWEB_DB, $sql, false);
        $ret = substr($event ['ALTRI'], 2, 1) == '1' ? true : false;
        if ($event['UTENTE'] == App::$utente->getIdUtente()) {
            $ret = true;
        } else if (in_array($event['GRUPPO'], $this->sqlUsersGroups())) {
            $gruppi = substr($event ['GRUPPI'], 2, 1) == '1' ? true : false;
            $ret = $ret == false ? $gruppi : $ret;
        }
        return $ret;
    }

    public function isTodoDeletable($todoId) {
        $sql = "SELECT CAL_CALENDARI.GRUPPI, CAL_CALENDARI.ALTRI, CAL_CALENDARI.UTENTE, CAL_CALENDARI.GRUPPO
                FROM CAL_ATTIVITA
                LEFT OUTER JOIN CAL_CALENDARI ON CAL_CALENDARI.ROWID = CAL_ATTIVITA.ROWID_CALENDARIO
                WHERE CAL_ATTIVITA.ROWID = $todoId";
        $event = ItaDB::DBSQLSelect($this->ITALWEB_DB, $sql, false);
        $ret = substr($event ['ALTRI'], 2, 1) == '1' ? true : false;
        if ($event['UTENTE'] == App::$utente->getIdUtente()) {
            $ret = true;
        } else if (in_array($event['GRUPPO'], $this->sqlUsersGroups())) {
            $gruppi = substr($event ['GRUPPI'], 2, 1) == '1' ? true : false;
            $ret = $ret == false ? $gruppi : $ret;
        }
        return $ret;
    }

    public function isEventEditable($eventId) {
        $sql = "SELECT CAL_CALENDARI.GRUPPI, CAL_CALENDARI.ALTRI, CAL_CALENDARI.UTENTE, CAL_CALENDARI.GRUPPO
                FROM CAL_EVENTI
                LEFT OUTER JOIN CAL_CALENDARI ON CAL_CALENDARI.ROWID = CAL_EVENTI.ROWID_CALENDARIO
                WHERE CAL_EVENTI.ROWID = $eventId";
        $event = ItaDB::DBSQLSelect($this->ITALWEB_DB, $sql, false);
        $ret = substr($event ['ALTRI'], 1, 1) == '1' ? true : false;
        if ($event['UTENTE'] == App::$utente->getIdUtente()) {
            $ret = true;
        } else if (in_array($event['GRUPPO'], $this->sqlUsersGroups())) {
            $gruppi = substr($event ['GRUPPI'], 1, 1) == '1' ? true : false;
            $ret = $ret == false ? $gruppi : $ret;
        }
        return $ret;
    }

    public function isTodoEditable($todoId) {
        $sql = "SELECT CAL_CALENDARI.GRUPPI, CAL_CALENDARI.ALTRI, CAL_CALENDARI.UTENTE, CAL_CALENDARI.GRUPPO
                FROM CAL_ATTIVITA
                LEFT OUTER JOIN CAL_CALENDARI ON CAL_CALENDARI.ROWID = CAL_ATTIVITA.ROWID_CALENDARIO
                WHERE CAL_ATTIVITA.ROWID = $todoId";
        $event = ItaDB::DBSQLSelect($this->ITALWEB_DB, $sql, false);
        $ret = substr($event ['ALTRI'], 1, 1) == '1' ? true : false;
        if ($event['UTENTE'] == App::$utente->getIdUtente()) {
            $ret = true;
        } else if (in_array($event['GRUPPO'], $this->sqlUsersGroups())) {
            $gruppi = substr($event ['GRUPPI'], 1, 1) == '1' ? true : false;
            $ret = $ret == false ? $gruppi : $ret;
        }
        return $ret;
    }

    public function addPromemoriaEvent($eventId, $type, $time, $unit) {
        $eventi_rec = $this->getEvent($eventId);
        if (!$eventi_rec) {
            $this->setErrCode(-1);
            $this->setErrMessage("Nessun evento con ROWID $eventId, impossibile creare il promemoria");

            return false;
        }
        $start = substr($eventi_rec ['START'], 0, 4) . '-' . substr($eventi_rec ['START'], 4, 2) . '-' . substr($eventi_rec ['START'], 6, 2) . 'T' . substr($eventi_rec ['START'], 8, 2) . ':' . substr($eventi_rec ['START'], 10, 2) . ':' . substr($eventi_rec['START'], 12, 2);
        $scad = date('U', strtotime($start)) - $time * $unit;
        $promemoria_rec = array(
            'TIPO' => $type,
            'TEMPO' => $time,
            'UNITA' => $unit,
            'SCADENZA' => $scad,
            'TAB_GENITORE' => 'CAL_EVENTI',
            'ROWID_GENITORE' => $eventi_rec['ROWID']
        );
        if (ItaDB::DBInsert($this->ITALWEB_DB, 'CAL_PROMEMORIA', 'ROWID', $promemoria_rec)) {
            return ItaDB::DBLastId($this->ITALWEB_DB);
        } else {
            $this->setErrCode(-1);
            $this->setErrMessage('Errore nella creazione del promemoria');

            return false;
        }
    }

    public function addPromemoriaTodo($todoId, $type, $time, $unit) {
        $todo_rec = $this->getTodo($todoId);
        if (!$todo_rec) {
            $this->setErrCode(-1);
            $this->setErrMessage("Nessun evento con ROWID $todoId, impossibile creare il promemoria");

            return false;
        }
        $start = substr($todo_rec ['START'], 0, 4) . '-' . substr($todo_rec ['START'], 4, 2) . '-' . substr($todo_rec ['START'], 6, 2) . 'T' . substr($todo_rec ['START'], 8, 2) . ':' . substr($todo_rec ['START'], 10, 2) . ':' . substr($todo_rec['START'], 12, 2);
        $scad = date('U', strtotime($start)) - $time * $unit;
        $promemoria_rec = array(
            'TIPO' => $type,
            'TEMPO' => $time,
            'UNITA' => $unit,
            'SCADENZA' => $scad,
            'TAB_GENITORE' => 'CAL_ATTIVITA',
            'ROWID_GENITORE' => $todo_rec['ROWID']
        );
        if (ItaDB::DBInsert($this->ITALWEB_DB, 'CAL_PROMEMORIA', 'ROWID', $promemoria_rec)) {
            return ItaDB::DBLastId($this->ITALWEB_DB);
        } else {
            $this->setErrCode(-1);
            $this->setErrMessage('Errore nella creazione del promemoria');

            return false;
        }
    }

    public function getPromemoria($promId) {
        $sql = "SELECT * FROM CAL_PROMEMORIA WHERE ROWID = $promId";
        $prom_rec = ItaDB::DBSQLSelect($this->ITALWEB_DB, $sql, false);
        return $prom_rec;
    }

    public function getPromemoriaFromEvent($eventId) {
        $sql = "SELECT * FROM CAL_PROMEMORIA WHERE TAB_GENITORE = 'CAL_EVENTI' AND ROWID_GENITORE = $eventId";
        $prom_tab = ItaDB::DBSQLSelect($this->ITALWEB_DB, $sql);
        return $prom_tab;
    }

    public function getPromemoriaFromTodo($todoId) {
        $sql = "SELECT * FROM CAL_PROMEMORIA WHERE TAB_GENITORE = 'CAL_ATTIVITA' AND ROWID_GENITORE = $todoId";
        $prom_tab = ItaDB::DBSQLSelect($this->ITALWEB_DB, $sql);
        return $prom_tab;
    }

    public function updatePromemoria($promId, $type, $time, $unit) {
        $prom_rec = $this->getPromemoria($promId);
        $prom_rec['TIPO'] = $type;
        $prom_rec['TEMPO'] = $time;
        $prom_rec['UNITA'] = $unit;
        if ($prom_rec ['TAB_GENITORE'] == 'CAL_EVENTI') {
            $gen_rec = $this->getEvent($prom_rec['ROWID_GENITORE']);
            if (!$gen_rec) {
                ItaDB::DBDelete($this->ITALWEB_DB, 'CAL_PROMEMORIA', 'ROWID', $promId);
                $this->setErrCode(-1);
                $this->setErrMessage('L\'evento associato al promemoria non esiste, promemoria eliminato');

                return false;
            }
        } else if ($prom_rec ['TAB_GENITORE'] == 'CAL_ATTIVITA') {
            $gen_rec = $this->getTodo($prom_rec['ROWID_GENITORE']);
            if (!$gen_rec) {
                ItaDB::DBDelete($this->ITALWEB_DB, 'CAL_PROMEMORIA', 'ROWID', $promId);
                $this->setErrCode(-1);
                $this->setErrMessage('L\'attività associata al promemoria non esiste, promemoria eliminato');

                return false;
            }
        }
        $start = substr($gen_rec ['START'], 0, 4) . '-' . substr($gen_rec ['START'], 4, 2) . '-' . substr($gen_rec ['START'], 6, 2) . 'T' . substr($gen_rec ['START'], 8, 2) . ':' . substr($gen_rec ['START'], 10, 2) . ':' . substr($gen_rec['START'], 12, 2);
        $prom_rec['SCADENZA'] = date('U', strtotime($start)) - $prom_rec ['TEMPO'] * $prom_rec['UNITA'];
        if ($prom_rec ['SCADENZA'] > time()) {
            $prom_rec['INVIATO'] = 0;
        }
        ItaDB::DBUpdate($this->ITALWEB_DB, 'CAL_PROMEMORIA', 'ROWID', $prom_rec);
        return $prom_rec;
    }

    /**
     * Riaggiorna il campo SCADENZA per adattarsi all'evento associato
     */
    public function refreshPromemoria($genId) {
        $sql = "SELECT * FROM CAL_PROMEMORIA WHERE ROWID_GENITORE = $genId";
        $prom_rec = ItaDB::DBSQLSelect($this->ITALWEB_DB, $sql, false);
        if ($prom_rec ['TAB_GENITORE'] == 'CAL_EVENTI') {
            $gen_rec = $this->getEvent($genId);
            if (!$gen_rec) {
                ItaDB::DBDelete($this->ITALWEB_DB, 'CAL_PROMEMORIA', 'ROWID', $prom_rec['ROWID']);
                $this->setErrCode(-1);
                $this->setErrMessage('L\'evento associato al promemoria non esiste, promemoria eliminato');

                return false;
            }
        } else if ($prom_rec ['TAB_GENITORE'] == 'CAL_ATTIVITA') {
            $gen_rec = $this->getTodo($genId);
            if (!$gen_rec) {
                ItaDB::DBDelete($this->ITALWEB_DB, 'CAL_PROMEMORIA', 'ROWID', $prom_rec['ROWID']);
                $this->setErrCode(-1);
                $this->setErrMessage('L\'attività associata al promemoria non esiste, promemoria eliminato');

                return false;
            }
        }
        $start = substr($gen_rec ['START'], 0, 4) . '-' . substr($gen_rec ['START'], 4, 2) . '-' . substr($gen_rec ['START'], 6, 2) . 'T' . substr($gen_rec ['START'], 8, 2) . ':' . substr($gen_rec ['START'], 10, 2) . ':' . substr($gen_rec['START'], 12, 2);
        $prom_rec['SCADENZA'] = date('U', strtotime($start)) - $prom_rec ['TEMPO'] * $prom_rec['UNITA'];
        if ($prom_rec ['SCADENZA'] > time()) {
            $prom_rec['INVIATO'] = 0;
        }
        ItaDB::DBUpdate($this->ITALWEB_DB, 'CAL_PROMEMORIA', 'ROWID', $prom_rec);
        return $prom_rec;
    }

    public function setPromemoriaSent($promId) {
        $sql = "SELECT * FROM CAL_PROMEMORIA WHERE ROWID = $promId";
        $prom_rec = ItaDB::DBSQLSelect($this->ITALWEB_DB, $sql, false);
        if ($prom_rec ['TAB_GENITORE'] == 'CAL_EVENTI') {
            $gen_rec = $this->getEvent($prom_rec['ROWID_GENITORE']);
            if (!$gen_rec) {
                ItaDB::DBDelete($this->ITALWEB_DB, 'CAL_PROMEMORIA', 'ROWID', $promId);
                $this->setErrCode(-1);
                $this->setErrMessage('L\'evento associato al promemoria non esiste, promemoria eliminato');

                return false;
            }
        } else if ($prom_rec ['TAB_GENITORE'] == 'CAL_ATTIVITA') {
            $gen_rec = $this->getTodo($prom_rec['ROWID_GENITORE']);
            if (!$gen_rec) {
                ItaDB::DBDelete($this->ITALWEB_DB, 'CAL_PROMEMORIA', 'ROWID', $promId);
                $this->setErrCode(-1);
                $this->setErrMessage('L\'attività associata al promemoria non esiste, promemoria eliminato');

                return false;
            }
        }
        $prom_rec['INVIATO'] = time();
        ItaDB::DBUpdate($this->ITALWEB_DB, 'CAL_PROMEMORIA', 'ROWID', $prom_rec);
        return $prom_rec;
    }

    public function deletePromemoria($promId) {
        ItaDB::DBDelete($this->ITALWEB_DB, 'CAL_PROMEMORIA', 'ROWID', $promId);

        return true;
    }

    public function deletePromemoriaFromEvent($eventId) {
        $sql = "SELECT * FROM CAL_PROMEMORIA WHERE TAB_GENITORE = 'CAL_EVENTI' AND ROWID_GENITORE = $eventId";
        $prom_tab = ItaDB::DBSQLSelect($this->ITALWEB_DB, $sql);
        foreach ($prom_tab as $prom_rec) {
            ItaDB::DBDelete($this->ITALWEB_DB, 'CAL_PROMEMORIA', 'ROWID', $prom_rec['ROWID']);
        }
        return true;
    }

    public function deletePromemoriaFromTodo($todoId) {
        $sql = "SELECT * FROM CAL_PROMEMORIA WHERE TAB_GENITORE = 'CAL_ATTIVITA' AND ROWID_GENITORE = $todoId";
        $prom_tab = ItaDB::DBSQLSelect($this->ITALWEB_DB, $sql);
        foreach ($prom_tab as $prom_rec) {
            ItaDB::DBDelete($this->ITALWEB_DB, 'CAL_PROMEMORIA', 'ROWID', $prom_rec['ROWID']);
        }
        return true;
    }

    public function checkPromemoriaCalendarioQuery($tipo, $cosa) {
        $utente = App::$utente->getIdUtente();
        $oggi = date('Ymd');
        $union_sql = array();
        $tables = array('CAL_EVENTI', 'CAL_ATTIVITA');
        foreach ($tables as $table) {
            $sql = "SELECT
                        CAL_PROMEMORIA.*,
                        $table.TITOLO AS EVENTO,
                        $table.TITOLO AS TITOLO,
                        $table.DESCRIZIONE AS DESCRIZIONE,
                        $table.START AS START,
                        $table.END AS END," .
                    $this->ITALWEB_DB->dateDiff(
                            $this->ITALWEB_DB->nullIf("START", "''"), "'$oggi'"
                    ) . " AS NUMEROGIORNI,                        
                        CAL_CALENDARI.TITOLO AS CALENDARIO,
                        CAL_CALENDARI.UTENTE
                    FROM
                        CAL_PROMEMORIA
                    LEFT OUTER JOIN $table ON CAL_PROMEMORIA.ROWID_GENITORE = $table.ROWID
                    LEFT OUTER JOIN CAL_CALENDARI ON $table.ROWID_CALENDARIO = CAL_CALENDARI.ROWID
                    WHERE
                        CAL_PROMEMORIA.TAB_GENITORE = '$table'
                    AND
                        CAL_PROMEMORIA.TIPO = '$tipo'
                    AND
                        CAL_PROMEMORIA.SCADENZA <= " . time() . "
                    AND
                        CAL_PROMEMORIA.INVIATO = 0";

            switch ($tipo) {
                case 'notifica':
                    $sql .= " AND CAL_CALENDARI.UTENTE = $utente ";
                    break;

                case 'popup':
                    $sql .= " AND $table.START > " . date('YmdHis');
                    /*
                     * Filtro per utente
                     */
//                    $sql .= " AND CAL_CALENDARI.UTENTE = $utente ";

                    /*
                     * Filtro per calendari visualizzabili
                     */
//                    $calendars = $this->getSelectedCalendars();
//                    unset($calendars['GOOGLE']);
//                    $calendars_id = array_keys($calendars);
//                    $sql .= " AND $table.ROWID_CALENDARIO IN ( " . implode(', ', $calendars_id) . " ) ";
                    $sql .= " AND CAL_CALENDARI.UTENTE = $utente ";
                    break;
            }

            $union_sql[] = $sql;
        }

        $sql_promemoria = "SELECT * FROM ( " . implode(" UNION ", $union_sql) . " ) PROMEMORIA";
        if ($cosa == 'sql') {
            $sql_promemoria = "SELECT * FROM ( " . implode(" UNION ", $union_sql) . " ORDER BY NUMEROGIORNI) PROMEMORIA";
            return $sql_promemoria;
        }
        return ItaDB::DBSQLSelect($this->ITALWEB_DB, $sql_promemoria);
    }

    public function checkPromemoriaCalendarioNotifiche() {
        $utente = App::$utente->getIdUtente();
        $nomeUtente = App::$utente->getKey('nomeUtente');

        $prom_tab = $this->checkPromemoriaCalendarioQuery('notifica', '');
        foreach ($prom_tab as $prom_rec) {
            $testo = 'Promemoria ' . $prom_rec ['EVENTO'] . ' dal calendario ' . $prom_rec['CALENDARIO'];
            if (!$this->envLib->inserisciNotifica('envFullCalendar', 'Promemoria Calendario', $testo, $nomeUtente, array(
                        'ACTIONMODEL' => $prom_rec ['TAB_GENITORE'] == 'CAL_EVENTI' ? 'envFullCalendarEvent' : 'envFullCalendarTodo',
                        'ACTIONPARAM' => serialize(array(
                            'setRowid' => array(
                                $prom_rec['ROWID_GENITORE']
                            )
                        ))
                    ))) {
                return false;
            }
            if (!$this->setPromemoriaSent($prom_rec['ROWID'])) {
                return false;
            }
        }

        if (count($prom_tab) > 0) {
            Out:: broadcastMessage($this->nameForm, 'AGGIORNACALENDARIO');
        }

        return true;
    }

    public function checkPromemoriaCalendarioEmail() {
        $prom_tab = $this->checkPromemoriaCalendarioQuery('email', '');
        foreach ($prom_tab as $prom_rec) {
            $this->setPromemoriaSent($prom_rec['ROWID']);
            if (!$this->sendEmail($prom_rec['ROWID_GENITORE'])) {
                return false;
            }
        }
        return true;
    }

    public function checkPromemoriaCalendarioPopup() {
        return $this->checkPromemoriaCalendarioQuery('popup', '');
    }

    public function chiamaCalendarPopup($prom_tab) {
        if ($prom_tab) {
            $model = "envFullCalendarPopUp";
            itaLib::openDialog($model);
            $formObj = itaModel::getInstance($model);
            if (!$formObj) {
                Out ::msgStop("Errore", "apertura envFullCalendarPopUp fallita");
                return;
            }
            $formObj->setReturnModel($this->nameForm);
            $formObj->setReturnId('');
            $formObj->setArray($prom_tab);
            $formObj->setEvent('openform');
            $formObj->parseEvent();
        }
    }

    public function popupEventoAttivita($record) {
        $text = '<b>' . $record ['CALENDARIO'] . '</b><br>Dal ' . $this->preparaData($record ['START']) . ( $record['END'] ? ' al ' . $this->preparaData($record['END']) : '' ) . '<br>';
        $text .= '<b>' . $record ['TITOLO'] . '</b><br>' . $record['DESCRIZIONE'];
        //Out::msgInfo("Avviso Evento", $text);
        return $text;
    }

    private function preparaData($data) {
        return substr($data, 6, 2) . '/' . substr($data, 4, 2) . '/' . substr($data, 0, 4);
    }

    public function sendEmail($eventId) {
        $sql = "SELECT CAL_EVENTI.*, CAL_CALENDARI.TITOLO AS CALENDARIO, CAL_CALENDARI.UTENTE FROM CAL_EVENTI LEFT OUTER JOIN CAL_CALENDARI ON CAL_CALENDARI.ROWID = CAL_EVENTI.ROWID_CALENDARIO WHERE CAL_EVENTI.ROWID = $eventId";
        $eventi_rec = ItaDB::DBSQLSelect($this->ITALWEB_DB, $sql, false);

        if (!$eventi_rec) {
            $this->setErrCode(-1);
            $this->setErrMessage('Impossibile trovare l\'evento');

            return false;
        }

        include_once ITA_BASE_PATH . '/apps/Sviluppo/devLib.class.php';
        $devLib = new devLib();
        $mailCalendario = $devLib->getEnv_config('CALENDARIO', 'codice', 'ACCOUNT_MAIL_INVIO_PROMEMORIA', false);

        include_once ITA_BASE_PATH . '/apps/Accessi/accLib.class.php';
        $accLib = new accLib();
        $parametriUser = $accLib->GetParamentriMail($eventi_rec['UTENTE']);
        $mailDest = $parametriUser['FROM'];

        include_once ITA_BASE_PATH . '/apps/Mail/emlMailBox.class.php';

        $emlMailBox = emlMailBox::getInstance($mailCalendario['CONFIG']);


        if (!$emlMailBox) {
            $this->setErrCode(-1);
            $this->setErrMessage('Impossibile accedere alle funzioni dell\'account');

            return false;
        }

        $outgoingMessage = $emlMailBox->newEmlOutgoingMessage();
        if (!$outgoingMessage) {
            $this->setErrCode(-1);
            $this->setErrMessage('Impossibile creare un nuovo messaggio in uscita');

            return false;
        }

        $body = $eventi_rec ['TITOLO'] . ' (' . $eventi_rec ['CALENDARIO'] . ')<br>';
        if ($eventi_rec ['ALLDAY'] == true) {
            $body .= 'Tutto il giorno';
        } else {
            if ($eventi_rec ['END'] && date('jnY', $eventi_rec ['START']) == date('jnY', $eventi_rec['END'])) {
                $body .= date('D, H:i', $eventi_rec ['START']) . ' - ' . date('H:i', $eventi_rec['END']);
            } else {
                $body .= date('D, H:i', $eventi_rec['START']);
                if ($eventi_rec['END']) {
                    $body .= ' - ' . date('D, H:i', $eventi_rec['END']);
                }
            }
        }
        $body .= '<br><br>' . $eventi_rec['DESCRIZIONE'];

        $outgoingMessage->setSubject('Promemoria evento ' . $eventi_rec['TITOLO']);
        $outgoingMessage->setBody($body);
        $outgoingMessage->setEmail($mailDest);

        $mailSent = $emlMailBox->sendMessage($outgoingMessage, false, false);
        if ($mailSent) {
            return true;
        } else {
            $this->setErrCode(-1);
            $this->setErrMessage($emlMailBox->getLastMessage());

            return false;
        }
    }

    /*
     *  Ritorna un array associativo ( 'GRUPPO' => GRUCOD, 'DESCRI' => GRUDES )
     */

    public function getUserGroups($userId = false) {
        if (!$userId) {
            $userId = App::$utente->getIdUtente();
        }
        $sql = "SELECT
					" . $this->ITW_DB->getDB() . ".GRUPPI.GRUCOD AS GRUPPO,
					" . $this->ITW_DB->getDB() . ".GRUPPI.GRUDES AS DESCRI
				FROM
					" . $this->ITW_DB->getDB() . ".GRUPPI
				WHERE
					" . $this->ITW_DB->getDB() . ".GRUPPI.GRUCOD IN " . $this->sqlUsersGroups(true, $userId);

        return ItaDB:: DBSQLSelect($this->ITW_DB, $sql);
    }

    public function getNomeUtente($userId = false) {
        if (!$userId) {
            $userId = App::$utente->getIdUtente();
        }
        $sql = "SELECT
					" . $this->ITW_DB->getDB() . ".UTENTI.UTELOG
				FROM
					" . $this->ITW_DB->getDB() . ".UTENTI
				WHERE
					" . $this->ITW_DB->getDB() . ".UTENTI.UTECOD = '" . $userId . "'";
        $utente = ItaDB:: DBSQLSelect($this->ITW_DB, $sql, false);
        return $utente['UTELOG'];
    }

}

?>