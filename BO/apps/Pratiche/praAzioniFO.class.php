<?php

/**
 *
 * Raccolta di funzioni per gestioni della zioni front-office
 *
 * PHP Version 5
 *
 * @category   itaModel
 * @package    Pratiche
 * @author     Michele Moscioni <michele.moscioni@italsoft.eu>
 * @copyright  1987-2017 Italsoft sRL
 * @license
 * @version    22.03.2017
 * @link
 * @see
 * @since
 * @deprecated
 * */
class praAzioniFO {

    private $PRAM_DB;
    private $praLib;
    private $azioniProcedimento;
    public $errCode;
    public $errMessage;

    function __construct() {

        $this->praLib = new praLib();
        $this->PRAM_DB = $this->praLib->getPRAMDB();

        $this->azioniProcedimento = array(
            array(
                "CODICEAZIONE" => "PRE_ISTANZIA_RICHIESTA",
                "DESCRIZIONEAZIONE" => "Prima di avviare una nuova richiesta on-line."
            ),
            array(
                "CODICEAZIONE" => "POST_ISTANZIA_RICHIESTA",
                "DESCRIZIONEAZIONE" => "Dopo aver avviato  la richiesta on-line."
            ),
            array(
                "CODICEAZIONE" => "PRE_INOLTRA_RICHIESTA",
                "DESCRIZIONEAZIONE" => "Prima di iniziare il processo di inoltro della richiesta on-line."
            ),
            array(
                "CODICEAZIONE" => "PRE_PROTOCOLLAZIONE_RICHIESTA",
                "DESCRIZIONEAZIONE" => "Prima di iniziare il processo di prot0ocollazione della richiesta on-line."
            ),
            array(
                "CODICEAZIONE" => "POST_PROTOCOLLAZIONE_RICHIESTA",
                "DESCRIZIONEAZIONE" => "Dopo aver protocollato la richiesta on line"
            ),
            array(
                "CODICEAZIONE" => "POST_INOLTRA_RICHIESTA",
                "DESCRIZIONEAZIONE" => "Dopo aver inoltrato la richiesta on line."
            ),
            array(
                "CODICEAZIONE" => "PRE_RENDER_PASSO",
                "DESCRIZIONEAZIONE" => "Prima di disegnare il contenuto del passo."
            ),
            array(
                "CODICEAZIONE" => "POST_RENDER_PASSO",
                "DESCRIZIONEAZIONE" => "Dopo aver disegnato il contenuto del passo."
            ),
            array(
                "CODICEAZIONE" => "PRE_RENDER_INFO_RICHIESTA",
                "DESCRIZIONEAZIONE" => "Prima di disegnare le informazioni della richiesta."
            ),
            array(
                "CODICEAZIONE" => "POST_RENDER_INFO_RICHIESTA_NUMERO",
                "DESCRIZIONEAZIONE" => "Dopo aver disegnato il numero della richiesta nelle informazioni."
            ),
            array(
                "CODICEAZIONE" => "POST_RENDER_INFO_RICHIESTA_OGGETTO",
                "DESCRIZIONEAZIONE" => "Dopo aver disegnato l'oggetto della richiesta nelle informazioni."
            ),
            array(
                "CODICEAZIONE" => "POST_RENDER_INFO_RICHIESTA",
                "DESCRIZIONEAZIONE" => "Dopo aver disegnato le informazioni della richiesta."
            )
        );

        $this->azioniPasso = array(
            array(
                "CODICEAZIONE" => "PRE_SUBMIT_RACCOLTA",
                "DESCRIZIONEAZIONE" => "Prima di registrare i dati di una raccolta, singola o multipla."
            ),
            array(
                "CODICEAZIONE" => "POST_SUBMIT_RACCOLTA",
                "DESCRIZIONEAZIONE" => "Dopo avere registrato i dati di una raccolta, singola o multipla."
            ),
            array(
                "CODICEAZIONE" => "PRE_RENDER_RACCOLTA",
                "DESCRIZIONEAZIONE" => "Prima di disegnare i dati di una raccolta, singola o multipla."
            ),
            array(
                "CODICEAZIONE" => "POST_RENDER_RACCOLTA",
                "DESCRIZIONEAZIONE" => "Dopo avere disegnato i dati di una raccolta, singola o multipla."
            ),
            array(
                "CODICEAZIONE" => "PRE_UPLOAD_ALLEGATO",
                "DESCRIZIONEAZIONE" => "Prima di effettuare il caricamento di un file su un passo upload, singolo o multiplo."
            ),
            array(
                "CODICEAZIONE" => "POST_UPLOAD_ALLEGATO",
                "DESCRIZIONEAZIONE" => "Dopo avere effettuato il caricamento di un file su un passo upload, singolo o multiplo."
            )
        );
    }

    function __destruct() {
        
    }

    public function getErrCode() {
        return $this->errCode;
    }

    public function setErrCode($errCode) {
        $this->errCode = $errCode;
    }

    public function getErrMessage() {
        return $this->errMessage;
    }

    public function setErrMessage($errMessage) {
        $this->errMessage = $errMessage;
    }

    public function getAzioniProcedimento() {
        return $this->azioniProcedimento;
    }

    public function getGridAzioniProcedimento($procedimento) {
        $gridAzioniProcedimento = array();
        foreach ($this->azioniProcedimento as $value) {
            $sql = "SELECT * FROM PRAAZIONI WHERE PRANUM='$procedimento' AND CODICEAZIONE='{$value['CODICEAZIONE']}'";
            $Praazioni_rec = ItaDB::DBSQLSelect($this->PRAM_DB, $sql, false);
            if ($Praazioni_rec) {
                $gridAzioniProcedimento[] = array_merge($value, $Praazioni_rec);
            } else {
                $gridAzioniProcedimento[] = $value;
            }
        }

        /*
         * Altro ciclo per decodifcare la descrizione dell'errore azione
         */
        foreach ($gridAzioniProcedimento as $key => $azione) {
            $gridAzioniProcedimento[$key]['OPERAZIONE'] = $this->GetDescErroreAzione($azione['ERROREAZIONE']);
        }

        return $gridAzioniProcedimento;
    }
    public function getGridAzioniSportello($sportello) {
        $gridAzioniProcedimento = array();
        foreach ($this->azioniProcedimento as $value) {
            $sql = "SELECT * FROM PRAAZIONI WHERE PRATSP='$sportello' AND CODICEAZIONE='{$value['CODICEAZIONE']}'";
            $Praazioni_rec = ItaDB::DBSQLSelect($this->PRAM_DB, $sql, false);
            if ($Praazioni_rec) {
                $gridAzioniProcedimento[] = array_merge($value, $Praazioni_rec);
            } else {
                $gridAzioniProcedimento[] = $value;
            }
        }

        /*
         * Altro ciclo per decodifcare la descrizione dell'errore azione
         */
        foreach ($gridAzioniProcedimento as $key => $azione) {
            $gridAzioniProcedimento[$key]['OPERAZIONE'] = $this->GetDescErroreAzione($azione['ERROREAZIONE']);
        }

        return $gridAzioniProcedimento;
    }

    public function GetDescErroreAzione($erroreAzione) {
        switch ($erroreAzione) {
            case "CONT":
                $operazione = "Continua esecuzione";
                break;
            case "ERR":
                $operazione = "Blocca esecuzione";
                break;
            case "WARN":
                $operazione = "Continua con invio segnalazione silenziosa";
                break;
            case "INV":
                $operazione = "Interrompi esecuzione, stampa pagina e mostra il messaggio";
                break;
            default:
                $operazione = "";
                break;
        }
        return $operazione;
    }

    public function getAzioniPasso() {
        return $this->azioniPasso;
    }

    public function getGridAzioniPasso($procedimento = false, $passo = false) {
        $gridAzioniPasso = array();
        foreach ($this->azioniPasso as $value) {
            if (!$passo || !$procedimento) {
                $gridAzioniPasso[] = $value;
                continue;
            }

            $sql = "SELECT * FROM PRAAZIONI WHERE PRANUM = '$procedimento' AND ITEKEY = '$passo' AND CODICEAZIONE = '{$value['CODICEAZIONE']}'";
            $Praazioni_rec = ItaDB::DBSQLSelect($this->PRAM_DB, $sql, false);
            if ($Praazioni_rec) {
                $gridAzioniPasso[] = array_merge($value, $Praazioni_rec);
            } else {
                $gridAzioniPasso[] = $value;
            }
        }

        /*
         * Altro ciclo per decodifcare la descrizione dell'errore azione
         */
        foreach ($gridAzioniPasso as $key => $azione) {
            $gridAzioniPasso[$key]['OPERAZIONE'] = $this->GetDescErroreAzione($azione['ERROREAZIONE']);
        }

        return $gridAzioniPasso;
    }
  function SalvaAzioniFO() {
        foreach ($this->azioniFO as $Praazioni_rec) {
            unset($Praazioni_rec['DESCRIZIONEAZIONE']);
            unset($Praazioni_rec['OPERAZIONE']);
            $Praazioni_rec['PRANUM'] = $this->currPranum;
            if (isset($Praazioni_rec['ROWID'])) {
                $update_Info = 'Oggetto: Aggiornamento azione FO ' . $Praazioni_rec['DESCRIZIONEAZIONE'];
                if (!$this->updateRecord($this->PRAM_DB, 'PRAAZIONI', $Praazioni_rec, $update_Info)) {
                    Out::msgStop("Errore", "Aggiornamneto azione FO " . $Praazioni_rec['DESCRIZIONEAZIONE'] . " fallito");
                    return false;
                }
            } else {
                $insert_Info = 'Oggetto: Inserimento azione FO ' . $Praazioni_rec['DESCRIZIONEAZIONE'];
                if (!$this->insertRecord($this->PRAM_DB, 'PRAAZIONI', $Praazioni_rec, $insert_Info)) {
                    Out::msgStop("Errore", "Inserimento azione FO " . $Praazioni_rec['DESCRIZIONEAZIONE'] . " fallito");
                    return false;
                }
            }
        }
        return true;
    }
}

?>