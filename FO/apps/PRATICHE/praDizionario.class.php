<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

class praDizionario {

    /**
     * Libreria di funzioni per Dizionario Pratiche
     *
     * @param <type> $returnModel programma chiamante
     */
    public $Dictionary = array();

    public function setDictionary($Dictionary) {
        $this->Dictionary = $Dictionary;
    }

    public function getDictionary() {
        if (!$this->Dictionary) {
            $this->Dictionary = array();
            $this->Dictionary[0]['chiave'] = '$.NUMPRO$';
            $this->Dictionary[0]['valore'] = 'Numero Procedimento';

            $this->Dictionary[1]['chiave'] = '$.DATAPRO$';
            $this->Dictionary[1]['valore'] = 'Data Inizio Procedimento';

            $this->Dictionary[2]['chiave'] = '$.ANNOPRO$';
            $this->Dictionary[2]['valore'] = 'Anno Procedimento';

            $this->Dictionary[3]['chiave'] = '$.DATAINOLTROPRO$';
            $this->Dictionary[3]['valore'] = 'Data Inoltro Procedimento';

            $this->Dictionary[4]['chiave'] = '$.ORAINOLTROPRO$';
            $this->Dictionary[4]['valore'] = 'Ora Inoltro Procedimento';

            $this->Dictionary[5]['chiave'] = '$.CODPRO$';
            $this->Dictionary[5]['valore'] = 'Codice Procedimento';

            $this->Dictionary[6]['chiave'] = '$.DESCPRO$';
            $this->Dictionary[6]['valore'] = 'Descrizione Procedimento';

            $this->Dictionary[7]['chiave'] = '$.RESPPRO$';
            $this->Dictionary[7]['valore'] = 'Responsabile Procedimento';

            $this->Dictionary[8]['chiave'] = '$.DESCSET$';
            $this->Dictionary[8]['valore'] = 'Descrizione Settore';

            $this->Dictionary[9]['chiave'] = '$.RESPSET$';
            $this->Dictionary[9]['valore'] = 'Responsabile Settore';

            $this->Dictionary[10]['chiave'] = '$.DESCSER$';
            $this->Dictionary[10]['valore'] = 'Descrizione Servizio';

            $this->Dictionary[11]['chiave'] = '$.RESPSER$';
            $this->Dictionary[11]['valore'] = 'Responsabile Servizio';

            $this->Dictionary[12]['chiave'] = '$.UNITA$';
            $this->Dictionary[12]['valore'] = 'Codice Unita';

            $this->Dictionary[13]['chiave'] = '$.OPERATORE$';
            $this->Dictionary[13]['valore'] = 'Operatore';

            $this->Dictionary[14]['chiave'] = '$.CODRICH$';
            $this->Dictionary[14]['valore'] = 'Codice Richiedente';

            $this->Dictionary[15]['chiave'] = '$.NOME$';
            $this->Dictionary[15]['valore'] = 'Nome Richiedente';

            $this->Dictionary[16]['chiave'] = '$.COGNOME$';
            $this->Dictionary[16]['valore'] = 'Cognome Richiedente';

            $this->Dictionary[17]['chiave'] = '$.FISCALE$';
            $this->Dictionary[17]['valore'] = 'Codice Fiscale Richiedente';

            $this->Dictionary[18]['chiave'] = '$.INDIRIZZO$';
            $this->Dictionary[18]['valore'] = 'Indirizzo Richiedente';

            $this->Dictionary[19]['chiave'] = '$.COMUNE$';
            $this->Dictionary[19]['valore'] = 'Comune Residenza Richiedente';

            $this->Dictionary[20]['chiave'] = '$.CAP$';
            $this->Dictionary[20]['valore'] = 'CAP Richiedente';

            $this->Dictionary[21]['chiave'] = '$.PROVINCIA$';
            $this->Dictionary[21]['valore'] = 'Provincia Richiedente';

            $this->Dictionary[22]['chiave'] = '$.MOTIVO$';
            $this->Dictionary[22]['valore'] = 'Motivo Annullamento';

            $this->Dictionary[23]['chiave'] = '$.ENTE$';
            $this->Dictionary[23]['valore'] = 'Ente';

            $this->Dictionary[24]['chiave'] = '$.SPORTELLO$';
            $this->Dictionary[24]['valore'] = 'Sportello on-line';

            $this->Dictionary[25]['chiave'] = '$.SPORTELLOAGG$';
            $this->Dictionary[25]['valore'] = 'Sportello Aggregato';

            $this->Dictionary[26]['chiave'] = '$.NUMRICHIESTAMADRE$';
            $this->Dictionary[26]['valore'] = 'Numero Richiesta di riferimento per integrazione';

            $this->Dictionary[27]['chiave'] = '$.DESCPROCMADRE$';
            $this->Dictionary[27]['valore'] = 'Numero Richiesta di riferimento per integrazione';

            $this->Dictionary[28]['chiave'] = '$.ANNORICHIESTAMADRE$';
            $this->Dictionary[28]['valore'] = 'Anno Richiesta di riferimento per integrazione';

            $this->Dictionary[29]['chiave'] = '$.DENOMIMPRESA$';
            $this->Dictionary[29]['valore'] = 'Denominazione Impresa';

            $this->Dictionary[30]['chiave'] = '$.CODFISIMPRESA$';
            $this->Dictionary[30]['valore'] = 'Codice Fiscale Impresa';

            $this->Dictionary[31]['chiave'] = '$.CIVICOIMPRESA$';
            $this->Dictionary[31]['valore'] = 'Civico Impresa';

            $this->Dictionary[32]['chiave'] = '$.CAPIMPRESA$';
            $this->Dictionary[32]['valore'] = 'Cap Impresa';

            $this->Dictionary[33]['chiave'] = '$.COMUNEIMPRESA$';
            $this->Dictionary[33]['valore'] = 'Comune Impresa';

            $this->Dictionary[34]['chiave'] = '$.PROVINCIAIMPRESA$';
            $this->Dictionary[34]['valore'] = 'Provincia Impresa';

            $this->Dictionary[35]['chiave'] = '$.INDIRIZZOIMPRESA$';
            $this->Dictionary[35]['valore'] = 'Indirizzo Impresa';

            $this->Dictionary[36]['chiave'] = '$.ISTATIMPRESA$';
            $this->Dictionary[36]['valore'] = 'Codice istat Impresa';

            $this->Dictionary[37]['chiave'] = '$.USERID$';
            $this->Dictionary[37]['valore'] = 'User id Richiedente';

            $this->Dictionary[38]['chiave'] = '$.TELEFONO$';
            $this->Dictionary[38]['valore'] = 'Telefono Richiedente';

            $this->Dictionary[39]['chiave'] = '$.IND_SEGNALAZIONE$';
            $this->Dictionary[39]['valore'] = 'Luogo Segnalazione';

            $this->Dictionary[40]['chiave'] = '$.DESC_SEGNALAZIONE$';
            $this->Dictionary[40]['valore'] = 'Descrizione Segnalazione';

            $this->Dictionary[41]['chiave'] = '$.PRIORITA_RICH$';
            $this->Dictionary[41]['valore'] = 'Priorita Richiesta';

            $this->Dictionary[42]['chiave'] = '$.NUMPROT$';
            $this->Dictionary[42]['valore'] = 'Numero Protocollo';

            $this->Dictionary[43]['chiave'] = '$.ANNONUMPROT$';
            $this->Dictionary[43]['valore'] = 'Anno/Numero Protocollo';

            $this->Dictionary[44]['chiave'] = '$.DATAPROT$';
            $this->Dictionary[44]['valore'] = 'Data Protocollo';

            $this->Dictionary[45]['chiave'] = '$.DATAODIERNA$';
            $this->Dictionary[45]['valore'] = 'Data Odierna';

            $this->Dictionary[46]['chiave'] = '$.DICH_COG_NOM$';
            $this->Dictionary[46]['valore'] = 'Cognome Nome Dichiarante';

            $this->Dictionary[47]['chiave'] = '$.OGGETTO_DOMANDA$';
            $this->Dictionary[47]['valore'] = 'Oggetto della Domanda';

            $this->Dictionary[48]['chiave'] = '$.ENTETERZO_DENOM$';
            $this->Dictionary[48]['valore'] = 'Denominazione ente terzo';

            $this->Dictionary[49]['chiave'] = '$.ENTETERZO_FISCALE$';
            $this->Dictionary[49]['valore'] = 'Codice fiscale ente terzo';

            $this->Dictionary[50]['chiave'] = '$.ENTETERZO_PEC$';
            $this->Dictionary[50]['valore'] = 'Pec ente terzo';

            $this->Dictionary[51]['chiave'] = '$.ENTETERZO_REFERENTE$';
            $this->Dictionary[51]['valore'] = 'Referente ente terzo';

            $this->Dictionary[52]['chiave'] = '$.FASCICOLO_NUMERO$';
            $this->Dictionary[52]['valore'] = 'N. Fascicolo elettronico(NNNNNN/AAAA)';

            $this->Dictionary[53]['chiave'] = '$.ARTICOLO_TITOLO$';
            $this->Dictionary[53]['valore'] = 'Titolo articolo Suap';

            $this->Dictionary[54]['chiave'] = '$.DESC_PARERE$';
            $this->Dictionary[54]['valore'] = 'Descrizione Parere';

            $this->Dictionary[55]['chiave'] = '$.ISCRITTO_COGNOME$';
            $this->Dictionary[55]['valore'] = 'Cognome Iscritto';

            $this->Dictionary[56]['chiave'] = '$.ISCRITTO_NOME$';
            $this->Dictionary[56]['valore'] = 'Nome Iscritto';

            $this->Dictionary[57]['chiave'] = '$.ISCRITTO_SCUOLA$';
            $this->Dictionary[57]['valore'] = 'Prima scuola scelta';

            $this->Dictionary[58]['chiave'] = '$.PEC_ESIBENTE$';
            $this->Dictionary[58]['valore'] = 'Pec utente esterno';

            $this->Dictionary[59]['chiave'] = '$.SCUOLA_TRASPORTO$';
            $this->Dictionary[59]['valore'] = 'Scuola scelta per il trapsoto';

            $this->Dictionary[60]['chiave'] = '$.HOOK_CATEG_LABEL$';
            $this->Dictionary[60]['valore'] = 'Categorie Albo Fornitori';

            $this->Dictionary[61]['chiave'] = '$.CAP_SEDELEGALE$';
            $this->Dictionary[61]['valore'] = 'CAP Sede Legale';

            $this->Dictionary[62]['chiave'] = '$.COMUNE_SEDELEGALE$';
            $this->Dictionary[62]['valore'] = 'Comune Sede Legale';

            $this->Dictionary[63]['chiave'] = '$.ENTE_DESTINATARIO$';
            $this->Dictionary[63]['valore'] = 'Ente Destinatario';

            $this->Dictionary[64]['chiave'] = '$.SPORTELLOCOM$';
            $this->Dictionary[64]['valore'] = 'Sportello Comune';

            $this->Dictionary[65]['chiave'] = '$.COMUNE_DESTINATARIO$';
            $this->Dictionary[65]['valore'] = 'Comune Destinatario';
        }

        return $this->Dictionary;
    }

    public function CaricaDizionario($arrayDizionario, $returnModel, $returnEvent, $contenuto = '') {
        $model = 'utiRicDiag';
        $gridOptions = array(
            "Caption" => 'Dizionario',
            "width" => '450',
            "height" => '430',
            "sortname" => "valore",
            "sortorder" => "desc",
            "rowNum" => '200',
            "rowList" => '[]',
            "arrayTable" => $arrayDizionario,
            "colNames" => array(
                "CODICE",
                "DESCRIZIONE"
            ),
            "colModel" => array(
                array("name" => 'chiave', "width" => 100),
                array("name" => 'valore', "width" => 300)
            )
        );
        $_POST = array();
        $_POST['event'] = 'openform';
        $_POST['gridOptions'] = $gridOptions;
        $_POST['returnModel'] = $returnModel;
        $_POST['returnEvent'] = $returnEvent;
        if ($returnEvent != '')
            $_POST['returnEvent'] = $returnEvent;
        $_POST['retid'] = $contenuto;
        $_POST['returnKey'] = 'retKey';
        itaLib::openForm($model);
        $appRoute = App::getPath('appRoute.' . substr($model, 0, 3));
        require_once App::getConf('modelBackEnd.php') . '/' . $appRoute . '/' . $model . '.php';
        $model();
    }

}

?>