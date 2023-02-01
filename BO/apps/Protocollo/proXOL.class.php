<?php
    /**
     * Libreria di Utilità per gestione Piattaforma NPCE ichieste di tipo XOL
     *
     */

class proXOL {
    
    public static function inviaRaccomandataDestinatari($modelObj,$returnId,$anapro_rec, $proArriAlle, $proAltriDestinatari) {

        $proLib=$modelObj->proLib;
        
        //
        // Controllo presenza Allegato
        //
        $allegati = array();
        foreach ($proArriAlle as $allegato) {
            if ($allegato['ROWID'] != 0 && $allegato['DOCSERVIZIO'] == 0 && strtolower(pathinfo($allegato['FILENAME'], PATHINFO_EXTENSION)) == 'pdf') {
                $allegati[] = $allegato;
            }
        }
        if (!$allegati) {
            Out::msgStop("Apertura dettaglio Raccomandata", "Documento Mancante.");
            return false;
        }

        //
        // Creo Nuova transazione
        //
        include_once ITA_BASE_PATH . '/apps/PosteItaliane/ptiLib.class.php';
        include_once ITA_BASE_PATH . '/apps/PosteItaliane/ptiXOLTransaction.class.php';
        $ptiLib = new ptiLib();
        $XOLTransaction = ptiXOLTransaction::getInstance($ptiLib);
        if (!$XOLTransaction) {
            Out::msgStop("Apertura dettaglio Raccomandata", "Errore apertura transazione.");
            return false;
        }
        $XOLTransaction->setTipoXOL('ROL');
        //
        // Mittente
        //
        $anades_mitt = $proLib->GetAnades($anapro_rec['PRONUM'], 'codice', false, $anapro_rec['PROPAR'], 'M');
        $XOLTransaction->setXOL_Mittente_rec(
                array(
                    "TIPOSOGGETTO" => "M",
                    "TIPOINDIRIZZO" => ptiXOLTransaction::XOL_TIPO_INDIRIZZO_NORMALE,
                    "STATO" => "ITA",
                    "PROVINCIA" => "MC",
                    "RAGIONESOCIALE" => $anades_mitt['DESNOM'],
                    "CITTA" => "POTENZA PICENA",
                    "CAP" => "62018",
                    "DUG" => "VIA",
                    "TOPONIMO" => "LAZIO",
                    "NUMEROCIVICO" => "6",
                    "ESPONENTE" => ""
                )
        );
        $XOLNominativo = array(
            'TIPOSOGGETTO' => "D",
            "TIPOINDIRIZZO" => ptiXOLTransaction::XOL_TIPO_INDIRIZZO_NORMALE,
            "STATO" => "ITA",
            'PROVINCIA' => $anapro_rec['PROPRO'],
            'RAGIONESOCIALE' => $anapro_rec['PRONOM'],
            'CITTA' => $anapro_rec['PROCIT'],
            'CAP' => $anapro_rec['PROCAP'],
            'DUG' => '',
            'TOPONIMO' => $anapro_rec['PROIND'],
            "NUMEROCIVICO" => "",
            "ESPONENTE" => ""
        );
        $XOLTransaction->setXOL_Destinatari_rec($XOLNominativo);
        $XOLTransaction->setStato(ptiXOLTransaction::XOL_STATO_INSERITO);

        foreach ($proAltriDestinatari as $i => $destinatario) {
            $XOLNominativo = array(
                'TIPOSOGGETTO' => "D",
                "TIPOINDIRIZZO" => ptiXOLTransaction::XOL_TIPO_INDIRIZZO_NORMALE,
                "STATO" => "ITA",
                'PROVINCIA' => $destinatario['PROPRO'],
                'RAGIONESOCIALE' => $destinatario['DESNOM'],
                'CITTA' => $destinatario['DESCIT'],
                'CAP' => $destinatario['DESCAP'],
                'DUG' => '',
                'TOPONIMO' => $destinatario['DESIND'],
                "NUMEROCIVICO" => "",
                "ESPONENTE" => ""
            );
            $XOLTransaction->setXOL_Destinatari_rec($XOLNominativo);
            $XOLTransaction->setStato(ptiXOLTransaction::XOL_STATO_INSERITO);
        }

        //
        // Documento Allegato
        //
        $XOLTransaction->setDataFile($allegati[0]['FILEPATH'], $allegati[0]['FILEINFO']);
        $XOLTransaction->setDescrizioneStato("Nominativi Modificati: Validare.");

        
        //
        // Apro Dialog di gestione
        //
        itaLib::openForm('ptiROLAppend');
        /* @var $ptiROLAppendObj ptiROLAppend */
        $ptiROLAppendObj = itaModel::getInstance('ptiROLAppend');
        $ptiROLAppendObj->setXOLTransaction($XOLTransaction);
        $ptiROLAppendObj->setEvent('openform');
        $ptiROLAppendObj->setReturnModel($modelObj->nameForm);
        $ptiROLAppendObj->setReturnEvent('returnFromPtiROLAppend');
        $ptiROLAppendObj->setReturnId($returnId);
        $ptiROLAppendObj->parseEvent();
        return $XOLTransaction;
    }
}
?>
