<?php

class praLibMail {

    private $praLib;
    private $PRAM_DB;
    private $errCode;
    private $errMessage;

    public function __construct() {
        $this->praLib = new praLib;
        $this->PRAM_DB = ItaDB::DBOpen('PRAM', frontOfficeApp::getEnte());
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

    public function invioMail($dati, $protocollaResult) {
        /*
         * Carico i testi parametri per il corpo delle mail decodificando le variabili dizionario
         */

        $arrayDatiMail = $this->praLib->arrayDatiMail($dati, $this->PRAM_DB);
        if (!$arrayDatiMail) {
            $this->setErrCode(-1);
            $this->setErrMessage("Impossibile decodificare il file mail.xml. File " . $dati['CartellaMail'] . "/mail.xml non trovato");
            return false;
        }

        $arrayDatiMail['errStringProt'] = $protocollaResult['RICHIESTA']['errString'];
        $arrayDatiMail['strNoProt'] = $protocollaResult['RICHIESTA']['strNoProt'];
        $arrayParamBloccoMail = $this->praLib->GetParametriBloccoMail($this->PRAM_DB);

        /*
         * SALVO IL BODY DELLA MAIL RICHIEDENTE GIA COMPILATO PER USARLO COME INFORMAZIONE QUANDO
         * USO LA FUNZIONE CONTROLLA RICHIESTYA DA BACK OFFICE SENZA MAIL.
         */

        $txtBody = $dati['CartellaAllegati'] . "/body.txt";
        $File = fopen($txtBody, "w+");

        if (!file_exists($txtBody)) {
            $this->setErrCode(-1);
            $this->setErrMessage("File " . $dati['CartellaAllegati'] . "/body.txt non trovato");
            return false;
        }

        if ($dati['Proric_rec']['RICRPA']) {
            fwrite($File, $arrayDatiMail['bodyIntResp']);
        } elseif ($dati['Proric_rec']['PROPAK']) {
            fwrite($File, $arrayDatiMail['bodyRespParere']);
        } else {
            fwrite($File, $arrayDatiMail['bodyResponsabile']);
        }

        fclose($File);

        $modo = '';

        if ($dati['Ricite_rec']['ITEZIP'] == 1) {
            $modo = "RICHIESTA-INFOCAMERE";
        } else if ($dati['Proric_rec']['RICRPA']) {
            $modo = "RICHIESTA-INTEGRAZIONE";
        } else if ($dati['Ricite_rec']['ITEIRE'] == 1) {
            $modo = "RICHIESTA-ONLINE";
        }

        if ($dati['Proric_rec']['PROPAK']) {
            $modo = "RICHIESTA-PARERE";
        }

        /*
         * Scrivo i file XMLINFO delle richieste accorpate
         * e li copio nella cartella della richiesta padre
         */

        if (!$this->praLib->scriviXmlAccorpate($dati, $modo, $dati['CartellaAllegati'])) {
            $this->setErrCode(-1);
            $this->setErrMessage("Copia file XMLINFO fallita per la pratica accorpata n. " . $dati['Proric_rec']['RICNUM']);
            return false;
        }

        /*
         * Scrivo il file XMLINFO
         */

        if (!$this->praLib->CreaXMLINFO($modo, $dati)) {
            $this->setErrCode(-1);
            $this->setErrMessage("Creazione file XMLINFO fallita per la pratica n. " . $dati['Proric_rec']['RICNUM']);
            return false;
        }

        /*
         * Scrivo XML Richiesta Dati Cityware
         */

        $anapar_rec = $this->praLib->GetAnapar("BLOCK_INOLTRO_CW", "parkey", $this->PRAM_DB, false);

        if ($anapar_rec['PARVAL'] == "Si") {
            $xmlDati = $this->praLib->GetXMLRichiestaDati($dati);
            $Nome_file = $dati['CartellaAllegati'] . "/XMLDATI.xml";
            $File = fopen($Nome_file, "w+");

            if (!file_exists($Nome_file)) {
                $this->setErrCode(-1);
                $this->setErrMessage("Creazione file XMLDATI fallita per la pratica n. " . $dati['Proric_rec']['RICNUM']);
                return false;
            }

            if (!fwrite($File, $xmlDati)) {
                fclose($File);

                $this->setErrCode(-1);
                $this->setErrMessage("Scrittura file XMLDATI fallita per la pratica n. " . $dati['Proric_rec']['RICNUM']);
                return false;
            }

            fclose($File);

            /*
             * Nuovo controllo
             */

            $Nome_file_base64 = $dati['CartellaAllegati'] . "/XMLDATI_base64.txt";
            $base64_stream = base64_encode(file_get_contents($Nome_file));

            if ($base64_stream === false) {
                $this->setErrCode(-1);
                $this->setErrMessage("Preparazione base64 xml dati fallita per la pratica n. " . $dati['Proric_rec']['RICNUM']);
                return false;
            }

            file_put_contents($Nome_file_base64, $base64_stream);
            $retRicezione = $this->praLib->setRicezionePraticaCityware(base64_encode(file_get_contents($Nome_file)), $dati['Proric_rec']['RICNUM']);

            /*
             * Nuovo Salvataggio ricezione
             */

            $Nome_file_esitocw = $dati['CartellaAllegati'] . "/ESITO__RicezionePraticaCityware.xml";
            file_put_contents($Nome_file_esitocw, print_r($retRicezione, true));

            if ($retRicezione === false) {
                $this->setErrCode(-1);
                $this->setErrMessage("Trasmissione dati a back-office Cityware fallita per la pratica n. " . $dati['Proric_rec']['RICNUM']);
                return false;
            }

            if ($retRicezione['EXITCODE'][0]['@textNode'] != "S") {
                $this->setErrCode(-1);
                $this->setErrMessage("Trasmissione dati a back-office Cityware fallita per la pratica n. " . $dati['Proric_rec']['RICNUM']);
                return false;
            }
        }

        /*
         * Preparo gli allegati da inviare
         */

        $TotaleAllegati = $this->praLib->GetAllegatiInvioMail($dati, $arrayParamBloccoMail, $this->PRAM_DB);

        /*
         * MAIL AL RESPONSABILE
         */

        if (!$dati['Ricite_rec'] || $dati['Ricite_rec']['ITEMRE'] == 0) {
            if ($arrayParamBloccoMail['bloccaMailResp'] == null || $arrayParamBloccoMail['bloccaMailResp'] == "No") {
                $ErrorMail = $this->praLib->InvioMailResponsabile($dati, $TotaleAllegati, $this->PRAM_DB, $arrayDatiMail, $modo);

                if ($protocollaResult['PROTOCOLLATO'] == false) {
                    if ($ErrorMail) {
                        $this->setErrCode(-1);
                        $this->setErrMessage("Invio mail responsabile pratica n. " . $dati['Proric_rec']['RICNUM'] . " fallito - " . $ErrorMail);
                        return false;
                    }
                }
            }
        }

        /*
         * MAIL AL RICHIEDENTE
         */

        if (!$dati['Ricite_rec'] || $dati['Ricite_rec']['ITEMRI'] == 0) {
            if ($arrayParamBloccoMail['bloccaMailRich'] == null || $arrayParamBloccoMail['bloccaMailRich'] == "No") {
                $mailRich = $this->praLib->GetMailRichiedente($modo, $dati['Ricdag_tab_totali']);
                $ErrorMail = $this->praLib->InvioMailRichiedente($dati, $this->PRAM_DB, $arrayDatiMail, $mailRich, $modo, $TotaleAllegati);
            }
        }

        return true;
    }

}
