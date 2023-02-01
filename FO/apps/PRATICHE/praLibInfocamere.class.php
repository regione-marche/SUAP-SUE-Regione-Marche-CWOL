<?php

/**
 * Description of praLibInfocamere
 *
 * @author Moscioni Michele <cmichele.moscioni@italsoft.eu>
 */
class praLibInfocamere {

    const ARR_KEY_DATI_INFOCAMERE = 'dati_infocamere';

    private $praLib;
    private $dati;
    private $codfis;
    private $cartellaTemporary;
    private $cartellaZIP;
    private $fileNameZIP;
    private $cartellaTmpZIP;
    private $codicePratica;
    private $fileNameDistinta;
    private $fileNameDistintaFirmato;
    private $errCode;
    private $errMessage;

    function __construct($praLib) {
        $this->praLib = $praLib;
    }

    function getErrCode() {
        return $this->errCode;
    }

    function getErrMessage() {
        return $this->errMessage;
    }

    function setErrCode($errCode) {
        $this->errCode = $errCode;
    }

    function setErrMessage($errMessage) {
        $this->errMessage = $errMessage;
    }

    function getCartellaZIP() {
        return $this->cartellaZIP;
    }

    function getCodicePratica() {
        return $this->codicePratica;
    }

    function getFileNameDistinta() {
        return $this->fileNameDistinta;
    }

    function setDati($dati) {
        $this->dati = $dati;
    }

    function setCartellaZIP($cartellaZIP) {
        $this->cartellaZIP = $cartellaZIP;
    }

    function getFileNameZIP() {
        return $this->fileNameZIP;
    }

    public function creaPratica($dati) {
        $this->errCode = 0;
        $this->errMessage = '';
        $this->dati = $dati;

        if (!$this->checkPrecodizioni()) {
            return false;
        }

        /*
         * Confermo i nuovi campi per infocamere
         */
        if (!$this->confermaDatiInfocamere()) {
            $this->errCode = $this->getErrCode();
            $this->errMessage = $this->getErrMessage();
            return false;
        }


        $codfis = $this->dati[self::ARR_KEY_DATI_INFOCAMERE]['datiImpresa']['codfis_suap'];
        if ($codfis == '' || $codfis == "." || $codfis == "*" || (strlen($codfis) !== 11 && strlen($codfis) !== 16)) {
            $this->errCode = "W0099";
            $this->errMessage = "Codice Fiscale non conforme";
            return false;
        }
        $this->codfis = $codfis;

        $this->cartellaTemporary = $this->dati['CartellaTemporary'];
        if ($this->dati['CartellaTemporary'] == '') {
            $this->errCode = -1;
            $this->errMessage = "Cartella temporanea non definita";
            return false;
        }

        $this->creaCodicePratica($codfis);
        if (!$this->creaCartellaZIP()) {
            $this->errCode = -1;
            $this->errMessage = "Creazione cartella zip fallita";
            return false;
        }

        if (!$this->copiaFileInfocamere()) {
            return false;
        }

        if (!$this->creaXMLSUAP()) {
            return false;
        }

        if (!$this->creaPDFDistintaSuap()) {
            return false;
        }

        if (!$this->salvaCartellaPraticaSuap()) {
            return false;
        }

        return $this->codicePratica;
    }

    public function caricaPratica($dati) {
        $this->errCode = 0;
        $this->errMessage = '';
        //
        $this->dati = $dati;
        $this->codicePratica = $this->dati['Proric_rec']['CODICEPRATICASW'];
        if (!$this->codicePratica) {
            $this->errCode = -1;
            $this->errMessage = "Codice Pratica non trovato";
            return false;
        }
        $this->fileNameDistinta = $this->codicePratica . ".SUAP.PDF";
        $this->fileNameDistintaFirmato = $this->fileNameDistinta . ".P7M";

        $this->cartellaZIP = $this->dati['CartellaAllegati'] . "/" . $this->codicePratica;
        if (!is_dir($this->cartellaZIP)) {
            $this->errCode = -1;
            $this->errMessage = "Cartella zip non trovata";
            return false;
        }
        return $this->codicePratica;
    }

    public function creaCodicePratica($codfis) {
        $this->codicePratica = $codfis . "-" . date('dmY') . "-" . date("Hi");
        $this->dati[self::ARR_KEY_DATI_INFOCAMERE]['datiAdempimento']['codice_pratica'] = $this->codicePratica;
        return $this->codicePratica;
    }

    public function creaCartellaZIP() {
        $cartellaZip = $this->cartellaTemporary . "/" . $this->codicePratica;
        if (is_dir($cartellaZip)) {
            $this->RemoveZipDir($cartellaZip);
        }
        if (!is_dir($cartellaZip)) {
            if (!@mkdir($cartellaZip, 0777, true)) {
                return false;
            }
        }
        $this->cartellaTmpZIP = $cartellaZip;
        return $cartellaZip;
    }

    function RemoveZipDir($dirname) {
        // Verifica necessaria
        if (!file_exists($dirname)) {
            return false;
        }
        // Cancella un semplice file
        if (is_file($dirname)) {
            return unlink($dirname);
        }
        // Loop per le dir
        $dir = dir($dirname);
        while (false !== $entry = $dir->read()) {
            // Salta i punti
            if ($entry == '.' || $entry == '..') {
                continue;
            }
            // Recursiva
            $this->RemoveZipDir("$dirname/$entry");
        }
        // Chiude tutto
        $dir->close();
        return rmdir($dirname);
    }

    public function copiaFileInfocamere() {
        //
        // copio allegati estratti per INFOCAMERE nella cartella temporanea
        //
        $this->dati[self::ARR_KEY_DATI_INFOCAMERE]['files']['nome_file_firmato'] = $this->codicePratica . ".001.MDA.PDF.P7M";
        if (!@copy($this->dati[self::ARR_KEY_DATI_INFOCAMERE]['files']['path_file_firmato'], $this->cartellaTemporary . "/" . $this->codicePratica . "/" . $this->dati[self::ARR_KEY_DATI_INFOCAMERE]['files']['nome_file_firmato'])) {
            $this->RemoveZipDir($cartellaZip);
            $this->errCode = "E0071";
            $this->errMessage = "Errore copia rapporto firmato " . $this->dati[self::ARR_KEY_DATI_INFOCAMERE]['files']['path_file_firmato'] . ' in ' . $this->dati[self::ARR_KEY_DATI_INFOCAMERE]['files']['nome_file_firmato'];
            return false;
        }

        foreach ($this->dati[self::ARR_KEY_DATI_INFOCAMERE]['files']['allegati'] as $key1 => $alle) {

            $inc = str_pad($key1 + 1, 3, 0, STR_PAD_LEFT);
            $this->dati[self::ARR_KEY_DATI_INFOCAMERE]['files']['allegati'][$key1]['PROGRESSIVO'] = $inc;

            $p7mPathInfo = $this->praLib->getP7mPathInfo($alle['FILEPATH']);
            $this->dati[self::ARR_KEY_DATI_INFOCAMERE]['files']['allegati'][$key1]['ATTACHMENTNAME'] = "{$this->dati[self::ARR_KEY_DATI_INFOCAMERE]['datiAdempimento']['codice_pratica']}.$inc." . strtoupper($p7mPathInfo["extension"]);

            if (!@copy($alle['FILEPATH'], $this->cartellaTemporary . "/" . $this->codicePratica . "/" . $this->dati[self::ARR_KEY_DATI_INFOCAMERE]['files']['allegati'][$key1]['ATTACHMENTNAME'])) {
                $this->errCode = "E0071";
                $this->errMessage = "Errore copia allegato " . $alle['FILEPATH'] . ' in ' . $this->cartellaTemporary . "/" . $this->dati[self::ARR_KEY_DATI_INFOCAMERE]['files']['allegati'][$key1]['ATTACHMENTNAME'];
                return false;
            }
        }
        return true;
    }

    private function creaXMLSUAP() {
        /*
         * Creo il file SUAP.XML
         */
        $suapXMLPath = $this->cartellaTmpZIP . "/" . $this->codicePratica . ".SUAP.XML";
        /*
         * Nome distinta
         */
        $distinta = $this->dati[self::ARR_KEY_DATI_INFOCAMERE]['files']['nome_file_firmato'];
        $size = filesize($this->cartellaTemporary . "/" . $this->codicePratica . "/" . $this->dati[self::ARR_KEY_DATI_INFOCAMERE]['files']['nome_file_firmato']);
        $di = $this->dati[self::ARR_KEY_DATI_INFOCAMERE];
        //
        $xml = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\r\n";
        $xml .= "<ps:riepilogo-pratica-suap xmlns:ps=\"http://www.impresainungiorno.gov.it/schema/suap/pratica\">";
        $xml .= "<info-schema versione=\"1.0.0\" data=\"2013-10-03+02:00\"/>\r\n";
        $xml .= "<intestazione>\r\n";
        $xml .= "<ufficio-destinatario codice-amministrazione=\"{$di['datiSportello']['cod_amm_suap']}\" codice-aoo=\"{$di['datiSportello']['cod_aoo_suap']}\" identificativo-suap=\"{$di['datiSportello']['identificativo_suap']}\">{$di['datiSportello']['denominazione_suap']}</ufficio-destinatario>\r\n";
        /*
         * Dati impresa
         */
        $xml .= "<impresa>\r\n";
        $xml .= "<forma-giuridica codice=\"{$di['datiImpresa']['formaGiuridica_impresa']}\">{$di['datiImpresa']['formaGiuridica_impresa_descrizione']}</forma-giuridica>\r\n";
        $xml .= "<ragione-sociale><![CDATA[" . strtoupper($di['datiImpresa']['denominazione_impresa']) . "]]></ragione-sociale>\r\n";
        $xml .= "<codice-fiscale><![CDATA[" . strtoupper($di['datiImpresa']['codfis_suap']) . "]]></codice-fiscale>\r\n";
        if ($di['datiImpresa']['codfis_suap'] && strlen($di['datiImpresa']['codfis_suap']) == 11) {
            $xml .= "<partita-iva>{$di['datiImpresa']['codfis_suap']}</partita-iva>\r\n";
        }
        /*
         * Dati REA
         */
        if ($di['datiImpresa']['provincia_rea'] && $di['datiImpresa']['data_iscrizione_rea'] && $di['datiImpresa']['codice_iscrizione_rea']) {
            $data_iscrizione_rea = $di['datiImpresa']['data_iscrizione_rea'];
            $data_iscrizione_rea = date("Y-m-d", strtotime($data_iscrizione_rea));
            $xml .= "<codice-REA provincia=\"" . strtoupper($di['datiImpresa']['provincia_rea']) . "\" data-iscrizione=\"$data_iscrizione_rea\">{$di['datiImpresa']['codice_iscrizione_rea']}</codice-REA>\r\n";
        }
        /*
         * Dati Indirizzo Impresa
         */
        $xml .= "<indirizzo>\r\n";
        $xml .= "   <stato codice=\"I\">ITALIA</stato>\r\n";
        $xml .= "   <provincia sigla=\"" . strtoupper($di['datiSedeLegale']['sedeLegale_provincia']) . "\" codice-istat=\"{$di['datiSedeLegale']['sedeLegale_istat_provincia']}\">{$di['datiSedeLegale']['sedeLegale_descrizione_provincia']}</provincia>\r\n";
        $xml .= "   <comune codice-catastale=\"{$di['datiSedeLegale']['sedeLegale_codice_catastale']}\">" . htmlentities(strtoupper($di['datiSedeLegale']['sedeLegale_comune'])) . "</comune>\r\n";
        $xml .= "   <cap>{$di['datiSedeLegale']['sedeLegale_cap']}</cap>\r\n";
        $toponimoSedeLegale = $di['datiSedeLegale']['sedeLegale_toponimo_indirizzo'];
        if ($toponimoSedeLegale == "") {
            $toponimoSedeLegale = "VIA";
        }
        $xml .= "   <toponimo><![CDATA[$toponimoSedeLegale]]></toponimo>\r\n";
        $xml .= "   <denominazione-stradale><![CDATA[{$di['datiSedeLegale']['sedeLegale_denominazione_stradale_indirizzo']}]]></denominazione-stradale>\r\n";
        $xml .= "   <numero-civico>{$di['datiSedeLegale']['sedeLegale_civico']}</numero-civico>\r\n";
        $xml .= "</indirizzo>\r\n";
        /*
         * Fine Dati indirizzo
         */

        /*
         * Rappresentante legale
         */
        $xml .= "<legale-rappresentante>\r\n";
        $xml .= "   <cognome><![CDATA[{$di['datiLegRapp']['legale_cognome']}]]></cognome>\r\n";
        $xml .= "   <nome><![CDATA[{$di['datiLegRapp']['legale_nome']}]]></nome>\r\n";
        $xml .= "   <codice-fiscale><![CDATA[" . strtoupper($di['datiLegRapp']['legale_fiscale']) . "]]></codice-fiscale>\r\n";
        $xml .= "   <carica codice=\"{$di['datiLegRapp']['legale_carica']}\">{$di['datiLegRapp']['legale_carica_descrizione']}</carica>\r\n";
        $xml .= "</legale-rappresentante>\r\n";
        /*
         * Fine rappresentante Legale
         */
        $xml .= "</impresa>\r\n";
        /*
         * Fine Impresa
         */
        $tipologia_adempimento = $di['datiAdempimento']['tipologia_adempimento'];
        $oggetto_adempimento = "$tipologia_adempimento di {$di['datiAdempimento']['tipologia_segnalazione']} impresa " . htmlentities($di['datiImpresa']['denominazione_impresa']);
        $xml .= "<oggetto-comunicazione tipo-procedimento=\"$tipologia_adempimento\" tipo-intervento=\"" . strtolower($di['datiAdempimento']['tipologia_segnalazione']) . "\">" . $oggetto_adempimento . "</oggetto-comunicazione>\r\n";
        //@TODO: normalizzare
        $xml .= "<codice-pratica>" . $this->codicePratica . "</codice-pratica>\r\n";
        $xml .= "<dichiarante qualifica=\"" . $di['datiEsibente']['esibente_qualifica'] . "\">\r\n";
        $cognDich = $di['datiEsibente']['esibente_cognome'];
        $nomeDich = $di['datiEsibente']['esibente_nome'];
        if ($cognDich == "") { // Capita se l'esibente si registra come impresa e compila solo il nome
            $cognDich = $di['datiEsibente']['esibente_nome'];
        }
        if ($nomeDich == "") { // Capita se l'esibente si registra come impresa e compila solo il cognome
            $nomeDich = $di['datiEsibente']['esibente_cognome'];
        }
        $xml .= "<cognome><![CDATA[$cognDich]]></cognome>\r\n";
        $xml .= "<nome><![CDATA[$nomeDich]]></nome>\r\n";

        $codFisEsibente = strtoupper($di['datiEsibente']['esibente_codfis_starweb']);
        if ($codFisEsibente == "") {
            $codFisEsibente = strtoupper($this->dati['ita_cftelemaco']);
        }
        $xml .= "<codice-fiscale><![CDATA[$codFisEsibente]]></codice-fiscale>\r\n";
        $xml .= "<pec><![CDATA[" . $di['datiEsibente']['esibente_pec'] . "]]></pec>\r\n";
        if ($di['datiEsibente']['esibente_telefono']) {
            $xml .= "<telefono><![CDATA[" . $di['datiEsibente']['esibente_telefono'] . "]]></telefono>\r\n";
        }
        $xml .= "</dichiarante>\r\n";
        $xml .= "<domicilio-elettronico>" . strtoupper(htmlentities($di['datiSedeLegale']['sedeLegale_pec'])) . "</domicilio-elettronico>\r\n";

        /*
         * Impianto Produttivo
         */

        $xml .= "<impianto-produttivo>\r\n";
        $xml .= "   <indirizzo>\r\n";
        $xml .= "       <stato codice=\"I\">ITALIA</stato>\r\n";
        //$xml .= "       <provincia sigla=\"" . strtoupper($di['datiImpresa']['provincia_suap']) . "\" codice-istat=\"{$di['datiImpresa']['insProduttivo_istat_provincia']}\">{$di['datiImpresa']['insProduttivo_descrizione_provincia']}</provincia>\r\n";
        $xml .= "       <provincia sigla=\"" . strtoupper($di['datiSportello']['cciaa_destinataria']) . "\" codice-istat=\"{$di['datiImpresa']['insProduttivo_istat_provincia']}\">{$di['datiImpresa']['insProduttivo_descrizione_provincia']}</provincia>\r\n";
        $xml .= "       <comune codice-catastale=\"{$di['datiImpresa']['insProduttivo_codice_catastale']}\">" . strtoupper($di['datiImpresa']['comune_suap']) . "</comune>\r\n";
        $xml .= " <cap>{$di['datiImpresa']['cap_suap']}</cap>\r\n";
        $toponimoInsProd = $di['datiImpresa']['insProduttivo_toponimo_indirizzo'];
        if ($toponimoInsProd == "") {
            $toponimoInsProd = "VIA";
        }
        $xml .= " <toponimo><![CDATA[$toponimoInsProd]]></toponimo>\r\n";
        $xml .= " <denominazione-stradale><![CDATA[" . htmlentities($di['datiImpresa']['insProduttivo_denominazione_stradale_indirizzo']) . "]]></denominazione-stradale>\r\n";
        $xml .= " <numero-civico>{$di['datiImpresa']['num_civico_suap']}</numero-civico>\r\n";
        $xml .= " </indirizzo>\r\n";
        $xml .= "</impianto-produttivo>\r\n";
        $xml .= "</intestazione>\r\n";
        $xml .= "<struttura>\r\n";
        $xml .= "<modulo nome = \"PDF ADEMPIMENTO\">\r\n";
        $xml .= "<distinta-modello-attivita nome-file=\"$distinta\">\r\n";
        //$xml .= "<descrizione>" . htmlentities($di['datiAdempimento']['nome_adempimento']) . "</descrizione>\r\n";
        $xml .= "<nome-file-originale><![CDATA[" . $di['files']['nome_originale_file_firmato'] . "]]></nome-file-originale>\r\n";
        $xml .= "<mime>application/pkcs7</mime>\r\n";
        $xml .= "<mime-base>application/pdf</mime-base>\r\n";
        $xml .= "<dimensione>$size</dimensione>\r\n";
        $xml .= "</distinta-modello-attivita>\r\n";
        foreach ($di['files']['allegati'] as $alle) {
            $ricdoc_recAll = $this->praLib->GetRicdoc($alle['FILENAME'], "codice", $this->dati['PRAM_DB'], false, $this->dati['Proric_rec']['RICNUM']);
            if (!$ricdoc_recAll) {
                $this->errCode = "E0071";
                $this->errMessage = "Errore preparazione elenco allegati per xml suap";
                return false;
            }
            $xml .= "<documento-allegato nome-file=\"{$alle['ATTACHMENTNAME']}\">\r\n";
            $xml .= "<descrizione>{$alle['codice_e_descrizione']}</descrizione>\r\n";
            $xml .= "<nome-file-originale><![CDATA[" . $alle['nome_file_originale'] . "]]></nome-file-originale>\r\n";
            $xml .= "<mime>" . $this->praLib->getMimeType($alle['nome_file_originale']) . "</mime>\r\n";
            $nomeBase = $this->praLib->GetP7MFileContentName($alle['nome_file_originale']);
            $xml .= "<mime-base>" . $this->praLib->getMimeType($nomeBase) . "</mime-base>\r\n";
            $xml .= "<dimensione>" . filesize($alle['FILEPATH']) . "</dimensione>\r\n";
            $xml .= "</documento-allegato>\r\n";
        }
        $xml .= "</modulo>\r\n";
        $xml .= "</struttura>\r\n";
        $xml .= "</ps:riepilogo-pratica-suap>";
        //
        $xmlUtf8 = utf8_encode($xml);
        //
        $File = fopen($suapXMLPath, "w+");
        fwrite($File, $xmlUtf8);
        fclose($File);
        return true;
    }

    private function creaPDFDistintaSuap() {
        /*
         * Creo il pdf della distinta dal report
         */
        $valori = array();
        $valori['DENOMINAZIONE'] = utf8_encode($this->dati[self::ARR_KEY_DATI_INFOCAMERE]['datiSportello']['denominazione_suap']);
        $valori['IDSUAP'] = utf8_encode($this->dati[self::ARR_KEY_DATI_INFOCAMERE]['datiSportello']['identificativo_suap']);
        $valori['COMUNEDEST'] = utf8_encode($this->dati[self::ARR_KEY_DATI_INFOCAMERE]['datiSportello']['comune_destinatario']);
        $valori['PROVINCIADEST'] = utf8_encode($this->dati[self::ARR_KEY_DATI_INFOCAMERE]['datiSportello']['provincia_suap']);
        $valori['IDSUAPDEST'] = utf8_encode($this->dati[self::ARR_KEY_DATI_INFOCAMERE]['datiSportello']['identificativo_suap']);
        $valori['UFFICIODEST'] = utf8_encode($this->dati[self::ARR_KEY_DATI_INFOCAMERE]['datiSportello']['denominazione_suap']);
        $valori['DENOMINAZIONEIMPRESA'] = utf8_encode($this->dati[self::ARR_KEY_DATI_INFOCAMERE]['datiImpresa']['denominazione_impresa']);
        $valori['CFIMPRESA'] = utf8_encode($this->dati[self::ARR_KEY_DATI_INFOCAMERE]['datiImpresa']['codfis_suap']);
        $valori['FORMAGIURIDICAIMPRESA'] = utf8_encode($this->dati[self::ARR_KEY_DATI_INFOCAMERE]['datiImpresa']['formaGiuridica_impresa_descrizione']);
        $valori['PVREAIMPRESA'] = utf8_encode($this->dati[self::ARR_KEY_DATI_INFOCAMERE]['datiImpresa']['provincia_rea']);
        $valori['CODREAIMPRESA'] = utf8_encode($this->dati[self::ARR_KEY_DATI_INFOCAMERE]['datiImpresa']['codice_iscrizione_rea']);
        $valori['TIPOADEMPIMENTO'] = utf8_encode($this->dati[self::ARR_KEY_DATI_INFOCAMERE]['datiAdempimento']['tipologia_adempimento']);
        $valori['OGGETTOCOMUNICAZIONE'] = utf8_encode($this->dati[self::ARR_KEY_DATI_INFOCAMERE]['datiAdempimento']['oggetto_comunicazione']);
        $valori['PRATICA'] = utf8_encode($this->dati[self::ARR_KEY_DATI_INFOCAMERE]['datiAdempimento']['codice_pratica']);
        $valori['COGNOME'] = utf8_encode($this->dati[self::ARR_KEY_DATI_INFOCAMERE]['datiEsibente']['esibente_cognome']);
        $valori['NOME'] = utf8_encode($this->dati[self::ARR_KEY_DATI_INFOCAMERE]['datiEsibente']['esibente_nome']);
        $valori['QUALIFICA'] = utf8_encode($this->dati[self::ARR_KEY_DATI_INFOCAMERE]['datiEsibente']['esibente_qualifica']);
        $valori['CODFISCALE'] = utf8_encode($this->dati[self::ARR_KEY_DATI_INFOCAMERE]['datiEsibente']['esibente_codfis']);
        $valori['MAILPEC'] = utf8_encode($this->dati[self::ARR_KEY_DATI_INFOCAMERE]['datiEsibente']['esibente_pec']);
        $valori['TELEFONO'] = utf8_encode($this->dati[self::ARR_KEY_DATI_INFOCAMERE]['datiEsibente']['esibente_telefono']);
        $valori['DOMPEC'] = utf8_encode($this->dati[self::ARR_KEY_DATI_INFOCAMERE]['datiSedeLegale']['sedeLegale_pec']);
        $valori['NOMEFILEALLEGATI_001'] = utf8_encode($this->dati[self::ARR_KEY_DATI_INFOCAMERE]['files']['nome_file_firmato']);
        $valori['DESCRIZIONEALLEGATI_001'] = utf8_encode($this->dati[self::ARR_KEY_DATI_INFOCAMERE]['files']['descrizione_pdf']);
        foreach ($this->dati[self::ARR_KEY_DATI_INFOCAMERE]['files']['allegati'] as $key => $value) {
            if (0 === strpos($value['codice_e_descrizione'], 'PS-')) {
                $valori['NOMEFILEPROCURASPECIALE'] = utf8_encode($value['ATTACHMENTNAME']);
                $valori['DESCRIZIONEFILEPROCURASPECIALE'] = utf8_encode($value['codice_e_descrizione']);
            } else {
                $valori['NOMEFILEALLEGATI_' . $value['PROGRESSIVO']] = utf8_encode($value['ATTACHMENTNAME']);
                $valori['DESCRIZIONEALLEGATI_' . $value['PROGRESSIVO']] = utf8_encode($value['codice_e_descrizione']);
            }
        }

        $distintaComunicaJrxml = ITA_SUAP_PATH . '/SUAP_italsoft/resources/distintaComunica.jrxml';
        $this->fileNameDistinta = $this->codicePratica . ".SUAP.PDF";
        $this->fileNameDistintaFirmato = $this->fileNameDistinta . ".P7M";
        $output_file = $this->cartellaTmpZIP . "/" . $this->fileNameDistinta;
        $Comunica_jrdef_xml = "<jrDefinition>\n";
        $Comunica_jrdef_xml .= "<ReportFile>" . $distintaComunicaJrxml . "</ReportFile>\n";
        $Comunica_jrdef_xml .= "<OutputFile>" . $output_file . "</OutputFile>\n";
        $Comunica_jrdef_xml .= "<DataSource class=\"JREmptyDataSource\" count=\"1\"></DataSource>\n";

        foreach ($valori as $key => $value) {
            $Comunica_jrdef_xml .= "<Parameter name=\"" . $key . "\" class=\"String\"><![CDATA[" . $value . "]]></Parameter>\n";
        }
        $Comunica_jrdef_xml .= "</jrDefinition>\n";

        /*
         * Scrivo xml di definizione per il generatore di report
         */
        $xmlJrDefPath = $this->cartellaTemporary . "/task_distintaComunica_" . $this->dati[self::ARR_KEY_DATI_INFOCAMERE]['datiAdempimento']['codice_pratica'] . ".xml";
        $xjh = fopen($xmlJrDefPath, 'w');
        if ($xjh === false) {
            $this->errCode = "E0099";
            $this->errMessage = "Errore apertura PDF distinta suap comunica per la richiesta n. " . $this->dati['Proric_rec']['RICNUM'];
            return false;
        }
        if (fwrite($xjh, "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n") === false) {
            $this->errCode = "E0099";
            $this->errMessage = "Errore in scrittura 1 PDF distinta suap comunica per la richiesta n. " . $this->dati['Proric_rec']['RICNUM'];
            return false;
        }
        if (fwrite($xjh, "<root>\n") === false) {
            $this->errCode = "E0099";
            $this->errMessage = "Errore in scrittura 2 PDF distinta suap comunica per la richiesta n. " . $this->dati['Proric_rec']['RICNUM'];
            return false;
        }
        fwrite($xjh, $Comunica_jrdef_xml);
        if (fwrite($xjh, "</root>\n") === false) {
            $this->errCode = "E0099";
            $this->errMessage = "Errore in scrittura 3 PDF distinta suap comunica per la richiesta n. " . $this->dati['Proric_rec']['RICNUM'];
            return false;
        }
        if (fclose($xjh) === false) {
            $this->errCode = "E0099";
            $this->errMessage = "Errore chiusura PDF distinta suap comunica per la richiesta n. " . $this->dati['Proric_rec']['RICNUM'];
            return false;
        }

        $commandJr = ITA_JVM_PATH . " -jar " . ITA_LIB_PATH . "/java/itaJRGenerator/ItaJrGenerator.jar " . $xmlJrDefPath;
        exec($commandJr, $outJr, $retJr);
        unlink($xmlJrDefPath);

        if (!file_exists($output_file)) {
            $this->errCode = "E0099";
            $this->errMessage = "PDF distinta suap comunica non trovata per la richiesta n. " . $this->dati['Proric_rec']['RICNUM'];
            return false;
        }

        /*
         * Conversione in PDF/A
         */
        if ($retJr == 0) {
            $distinta_pdfa = $this->cartellaTmpZIP . "/" . $this->codicePratica . "_pdfa.SUAP.PDF";
            $manager = itaPDFA::getManagerInstance(); //new itaPDFA();
            if ($manager) {
                $manager->convertPDF($this->cartellaTmpZIP . "/" . $this->codicePratica . ".SUAP.PDF", $distinta_pdfa);
                if ($manager->getLastExitCode() == 0) {
                    rename($distinta_pdfa, $this->cartellaTmpZIP . "/" . $this->codicePratica . ".SUAP.PDF");
                } else {
                    $this->errCode = $manager->getLastOutput();
                    $this->errMessage = $manager->getLastMessage();
                    return false;
                }
            }
        }

        return true;
    }

    function salvaPDFDistintaSuapFirmato($fileFirmato) {
        $this->errCode = 0;
        $this->errMessage = "";

        if (!file_exists($fileFirmato)) {
            $this->errCode = "99";
            $this->errMessage = "File distinta firmato non trovato";
            return false;
        }


        $sha1_drr = sha1_file($fileFirmato);
        $sha1_p7m = $p7m->getContentSHA();
        if ($sha1_drr !== $sha1_p7m) {
            $p7m->cleanData();
            return "File firmato incongruente con il file scaricato.";
        }

        return true;
    }

    function salvaCartellaPraticaSuap() {
        /*
         * Sposto la cartella da Temp ad Attachment
         */
        $this->cartellaZIP = $this->dati['CartellaAllegati'] . "/" . $this->codicePratica;
        if (!rename($this->cartellaTmpZIP, $this->cartellaZIP)) {
            $this->errCode = "E0074";
            $this->errMessage = "Impossibile spostare la cartella infocamere da $this->cartellaTmpZIP a " . $this->cartellaZIP;
            return false;
        }

        /*
         * Mi salvo il codice pratica
         */
        $this->dati['Proric_rec']['CODICEPRATICASW'] = $this->codicePratica;
        try {
            $nRows = ItaDB::DBUpdate($this->dati['PRAM_DB'], 'PRORIC', 'ROWID', $this->dati['Proric_rec']);
        } catch (Exception $e) {
            $this->errCode = "E0074";
            $this->errMessage = "Impossibile aggiornare codice pratica $this->codicePratica per la richiesta on-line " . $this->dati['Proric_rec']['RICNUM'];
            return false;
        }
        return true;
    }

    function cancellaPratica() {
        /*
         * Carico lo stato della distinta
         */
        $statoDistinta = $this->getStatoDistintaInfocamere();

        /*
         * Se la distinta è stata caricata, cancello l'upload
         */
        if ($statoDistinta['UploadCaricato'] == 1) {
            if (!$this->cancellaUploadPratica($statoDistinta)) {
                $this->errCode = $this->getErrCode();
                $this->errMessage = $this->GetErrMessage();
                return false;
            }
        }

        /*
         * Cancello la cartella dello zip 
         */
        if (is_dir($this->cartellaZIP)) {
            if (!$this->praLib->RemoveDir($this->cartellaZIP)) {
                $this->errCode = "E0075";
                $this->errMessage = "Impossibile eliminare la cartella dello zip $this->cartellaZIP richiesta " . $this->dati['Proric_rec']['RICNUM'];
                return false;
            }

            /*
             * Annullo il passo della distinta e vuoto il CODICEPRATICASW
             */
            $this->dati['Proric_rec']['CODICEPRATICASW'] = "";
            $this->dati['Proric_rec'] = $this->praLib->AnnullaPasso($this->dati['PRAM_DB'], $statoDistinta['Sequenza'], $this->dati['Proric_rec']);
            if (!$this->dati['Proric_rec']) {
                $this->errCode = "E0076";
                $this->errMessage = "Impossibile annullare il passo scarico distinta infocamere della richiesta " . $this->dati['Proric_rec']['RICNUM'];
                return false;
            }
        }


        return true;
    }

    function cancellaFileZIP() {
        if (!$this->codicePratica) {
            $this->errCode = "E0099";
            $this->errMessage = "Nessuna Pratica Definita Impossibile Procedere alla creazione del file zip per la pratica comunica";
            return false;
        }

        $this->fileNameZIP = $this->cartellaZIP . ".zip";
        //
        if (!unlink($this->fileNameZIP)) {
            $this->errCode = "E0099";
            $this->errMessage = "Impossibile cancellare il file zip indocamere $this->fileNameZIP della richiesta n. " . $this->dati['Proric_rec']['RICNUM'];
            return false;
        }
        return true;
    }

    function creaFileZIP() {
        if (!$this->codicePratica) {
            $this->errCode = "E0099";
            $this->errMessage = "Nessuna Pratica Definita Impossibile Procedere alla creazione del file zip per la pratica comunica";
            return false;
        }

        /*
         * Copio la cartella dello zip in una temporanea
         */
        $cartellaZipTEMP = $this->cartellaZIP . "_TEMP";


        if (!$this->copiaCartella($this->cartellaZIP, $cartellaZipTEMP)) {
            $this->errCode = "E0079";
            $this->errMessage = "Erroe creazione cartella $cartellaZipTEMP della richiesta " . $this->dati['Proric_rec']['RICNUM'];
            return false;
        }

        /*
         * Mi trovo il passo di upload della distinta firmata e il relativo RICDOC
         */
        $passoUploadDistinta = array();
        foreach ($this->dati['Navigatore']['Ricite_tab_new'] as $key => $ricite_rec) {
            if ($ricite_rec['ITEDISCOMUNICA'] == 1) {
                //$passoUploadDistinta = ItaDB::DBSQLSelect($this->dati['PRAM_DB'], "SELECT * FROM RICITE WHERE RICNUM=" . $this->dati['Proric_rec']['RICNUM'] . " AND ITECTP='" . $ricite_rec['ITEKEY'] . "'", false);
                $passoUploadDistinta = $this->praLib->GetRicite($ricite_rec['ITEKEY'], "itectp", $this->dati['PRAM_DB'], false, $this->dati['Proric_rec']['RICNUM']);
                break;
            }
        }
        if (!$passoUploadDistinta) {
            $this->errCode = "E0081";
            $this->errMessage = "Passo Uplaod Distinta non trovato";
            return false;
        }
        $ricdoc_recDistinta = $this->praLib->GetRicdoc($passoUploadDistinta['ITEKEY'], "itekey", $this->dati['PRAM_DB'], false, $passoUploadDistinta['RICNUM']);
        if (!$ricdoc_recDistinta) {
            $this->errCode = "E0082";
            $this->errMessage = "Record allegato Distinta firmata non trovato";
            return false;
        }

        /*
         * Copio la distinta firmata nella cartella temporanea
         */
        if (!copy($this->dati['CartellaAllegati'] . "/" . $ricdoc_recDistinta['DOCUPL'], $cartellaZipTEMP . "/" . $this->fileNameDistintaFirmato)) {
            $this->errCode = "E0080";
            $this->errMessage = "Erroe copia distinta firmata da " . $this->dati['CartellaAllegati'] . "/" . $ricdoc_recDistinta['DOCUPL'] . " a " . $cartellaZipTEMP . "/" . $this->fileNameDistintaFirmato . " della richiesta " . $this->dati['Proric_rec']['RICNUM'];
            return false;
        }

        /*
         * Tolgo il pdf della distinta dalla cartella temporanea
         */
        if (!unlink($cartellaZipTEMP . "/" . $this->fileNameDistinta)) {
            $this->errCode = "E0078";
            $this->errMessage = "Impossibile eliminare $this->fileNameDistinta dalla cartella temp dello zip della richiesta " . $this->dati['Proric_rec']['RICNUM'];
            return false;
        }

        /*
         * Creo il file zip dalla cartella temporanea
         */
        $this->fileNameZIP = $this->cartellaZIP . ".zip";
        //$this->fileNameZIP = $this->codicePratica . ".zip";
        if (itaZip::zipRecursive($this->dati['CartellaAllegati'], $cartellaZipTEMP, $this->fileNameZIP, 'zip', false, false) !== 0) {
            $this->praLib->RemoveDir($cartellaZipTEMP);
            $this->errCode = "E0076";
            $this->errMessage = "Impossibile creare il file zip per infocamere della richiesta " . $this->dati['Proric_rec']['RICNUM'];
            return false;
        }
        $this->praLib->RemoveDir($cartellaZipTEMP);
        return true;
    }

    function checkPrecodizioni() {
        $errMancanti = "";
        foreach ($this->dati[self::ARR_KEY_DATI_INFOCAMERE]['files']['allegati'] as $alle) {
            if ($alle['allegato_mancante'] == 1) {
                $errMancanti .= $alle['codice_e_descrizione'] . "<br>";
            }
        }

        if ($errMancanti) {
            $this->errCode = "W0099";
            $this->errMessage = "Impossibile creare la distinta per la pratica n. " . $this->dati['Proric_rec']['RICNUM'] . ".Uno o più allegati mancanti: <br>$errMancanti";
            return false;
        }

        return true;
    }

    function copiaCartella($src, $dest) {
        if (!is_dir($dest)) {
            if (!mkdir($dest)) {
                return false;
            }
        }
        foreach (scandir($src) as $file) {
            if (!is_readable($src . '/' . $file))
                continue;
            if ($file == '.' || $file == '..')
                continue;
            if (is_dir($file)) {
                if (!mkdir($dest . '/' . $file)) {
                    return false;
                }
                $this->copiaCartella($src . '/' . $file, $dest . '/' . $file);
            } else {
                if (!copy($src . '/' . $file, $dest . '/' . $file)) {
                    return false;
                }
            }
        }
        return true;
    }

    public function inviaZipWsComunica() {
        $this->errCode = 0;
        $this->errMessage = '';
        if (!$this->codicePratica) {
            $this->errCode = "E0099";
            $this->errMessage = "Nessuna Pratica Definita Impossibile Procedere alla creazione del file zip per la pratica comunica";
            return false;
        }
        if (!ITA_JVM_PATH || !file_exists(ITA_JVM_PATH)) {
            $this->errCode = "W0099";
            $this->errMessage = "Attenzione java virtual-machine non definita impossibile inviare i file zip.";
            return false;
        }
        if (!file_exists(ITA_LIB_PATH . "/java/itaComunicaWS/itaComunicaWS.properties")) {
            $this->errCode = "W0099";
            $this->errMessage = "Attenzione configurazione per invio zip definita impossibile inviare i file zip.";
            return false;
        }
        $utenteTelemaco = frontOfficeApp::$cmsHost->getUserInfo('telemaco');
        $filePathZIP = $this->fileNameZIP;
//        print_r("<pre>");
//        print_r(ITA_JVM_PATH . " -jar " . ITA_LIB_PATH . "/java/itaComunicaWS/itaComunicaWS.jar " . $filePathZIP . " " . $this->codicePratica . ".SUAP.ZIP " . $this->codicePratica . " " . $utenteTelemaco);
//        print_r("</pre>");
//        exit();
        $exec = exec(ITA_JVM_PATH . " -jar " . ITA_LIB_PATH . "/java/itaComunicaWS/itaComunicaWS.jar " . $filePathZIP . " " . $this->codicePratica . ".SUAP.zip " . $this->codicePratica . " " . $utenteTelemaco, $ret);

        if (substr($exec, 0, 2) == "00") {
            return true;
        } else {
            $this->errCode = "E0099";
            $this->errMessage = "L'invio del file Zip comunica non è riuscito. $exec per la richiesta n. " . $this->dati['Proric_rec']['RICNUM'];
            return false;
        }
    }

    function getStatoDistintaInfocamere() {
        foreach ($this->dati['Navigatore']['Ricite_tab_new'] as $ricite_rec) {
            if ($ricite_rec['ITEDISCOMUNICA'] == 1) {
                $arrDistinta = array();
                if (strpos($this->dati['Proric_rec']['RICSEQ'], "." . $ricite_rec['ITESEQ'] . ".") !== false && $this->dati['Proric_rec']['CODICEPRATICASW']) {
                    $arrDistinta['PassoEseguito'] = true;
                } else {
                    $arrDistinta['PassoEseguito'] = false;
                }
                //$arrDistinta['Allegati'] = $this->praLib->ControllaRapportoConfig($dati, $ricite_rec);
                //$arrDistinta['AllegatiMancanti'] = $this->checkAllegatiMancantiRapporto($arrDistinta['Allegati']);
                $arrDistinta['UploadCaricato'] = $this->checkStatoUploadDistinta($ricite_rec['ITEKEY']);
                $arrDistinta['Sequenza'] = $ricite_rec['ITESEQ'];
                $arrDistinta['Itekey'] = $ricite_rec['ITEKEY'];
            }
        }
        return $arrDistinta;
    }

    function checkStatoUploadDistinta($itekeyDistinta) {
        foreach ($this->dati['Navigatore']['Ricite_tab_new'] as $ricite_rec) {
            if ($ricite_rec['ITECTP'] == $itekeyDistinta) {
                if (strpos($this->dati['Proric_rec']['RICSEQ'], "." . $ricite_rec['ITESEQ'] . ".") !== false) {
                    return true;
                } else {
                    return false;
                }
            }
        }
    }

    function cancellaUploadPratica($statoDistinta) {
        $passoUplDistinta_rec = $this->praLib->GetRicite($statoDistinta['Itekey'], "itectp", $this->dati['PRAM_DB'], false, $this->dati['Proric_rec']['RICNUM']);
        if ($passoUplDistinta_rec) {
            $ricdoc_recDistinta = $this->praLib->GetRicdoc($passoUplDistinta_rec['ITEKEY'], "itekey", $this->dati['PRAM_DB'], false, $passoUplDistinta_rec['RICNUM']);
            if (!$ricdoc_recDistinta) {
                $this->errCode = "E0082";
                $this->errMessage = "Record allegato Distinta firmata non trovato per la pratica n. " . $passoUplDistinta_rec['RICNUM'];
                return false;
            }
            if (file_exists($this->dati['CartellaAllegati'] . "/" . $ricdoc_recDistinta['DOCUPL'])) {
                if (!$this->praLib->CancellaUpload($this->dati['PRAM_DB'], $this->dati, $passoUplDistinta_rec, $ricdoc_recDistinta['DOCUPL'])) {
                    $this->errCode = "E0087";
                    $this->errMessage = "Errore cancellazione Distinta firmata per la pratica n. " . $passoUplDistinta_rec['RICNUM'];
                    return false;
                }
            }
        }
        return true;
    }

    public function checkDatiInfocamere($datiInfocamere) {
        $checkInfocamere = array();
        //
        // Controlli su Dati Impresa
        //
        $checkDatiImpresa = array();
        if (!$datiInfocamere['datiImpresa']['denominazione_impresa'])
            $checkDatiImpresa[] = "Manca Denominazione Impresa";
        if (!$datiInfocamere['datiImpresa']['codfis_suap'])
            $checkDatiImpresa[] = "Manca Codice fiscale Impresa";
        if (!$datiInfocamere['datiImpresa']['provincia_suap'])
            $checkDatiImpresa[] = "Manca Provincia Insediamento Produttivo";
        if (!$datiInfocamere['datiImpresa']['comune_suap'])
            $checkDatiImpresa[] = "Manca Comune Insediamento Produttivo";
        if (!$datiInfocamere['datiImpresa']['indirizzo_suap'])
            $checkDatiImpresa[] = "Manca Indirizzo Insediamento Produttivo";
        if (!$datiInfocamere['datiImpresa']['num_civico_suap'])
            $checkDatiImpresa[] = "Manca N. Civico Insediamento Produttivo";
        if (!$datiInfocamere['datiImpresa']['cap_suap'])
            $checkDatiImpresa[] = "Manca Cap Insediamento Produttivo";
        if (($datiInfocamere['datiImpresa']['provincia_rea'] && $datiInfocamere['datiImpresa']['data_iscrizione_rea'] && $datiInfocamere['datiImpresa']['codice_iscrizione_rea']) ||
                ($datiInfocamere['datiImpresa']['provincia_rea'] == "" && $datiInfocamere['datiImpresa']['data_iscrizione_rea'] == "" && $datiInfocamere['datiImpresa']['codice_iscrizione_rea'] == "")) {
            
        } else {
            $checkDatiImpresa[] = "Compilare tutti e 3 i campi relativi al REA";
        }

        //
        // Controlli su dati Sportello
        //
        $checkDatiSportello = array();
        if (!$datiInfocamere['datiSportello']['codice_catastale_destinatario'])
            $checkDatiSportello[] = "Manca Codice catastale Destinatario";
        if (!$datiInfocamere['datiSportello']['cciaa_destinataria'])
            $checkDatiSportello[] = "Manca Codice cciaa destinataria";
        if (!$datiInfocamere['datiSportello']['provincia_suap'])
            $checkDatiSportello[] = "Manca Provincia suap";
        if (!$datiInfocamere['datiSportello']['comune_destinatario'])
            $checkDatiSportello[] = "Manca Comune destinatario";
        if (!$datiInfocamere['datiSportello']['cod_istat_suap'])
            $checkDatiSportello[] = "Manca codice Istat suap";
        if (!$datiInfocamere['datiSportello']['cap_suap'])
            $checkDatiSportello[] = "Manca cap suap";
        if (!$datiInfocamere['datiSportello']['cod_amm_suap'])
            $checkDatiSportello[] = "Manca codice amministrazione suap";
        if (!$datiInfocamere['datiSportello']['cod_aoo_suap'])
            $checkDatiSportello[] = "Manca codice AOO suap";

        //
        // Controlli su dati Richiesta
        //
        $checkDatiAdempimento = array();
        //if (!$datiInfocamere['datiAdempimento']['oggetto_comunicazione'])
        //    $checkDatiAdempimento = "Manca l'oggetto della Comunicazione";
        if (!$datiInfocamere['datiAdempimento']['nome_adempimento'])
            $checkDatiAdempimento = "manca il nome dell'adempimento";
        if (!$datiInfocamere['datiAdempimento']['user_telemaco'])
            $checkDatiAdempimento = "Manca l'utente telemaco";
        if (!$datiInfocamere['datiAdempimento']['tipologia_segnalazione'])
            $checkDatiAdempimento = "Manca la tipologia della segnalazione";

        /*
         * Controlli su esibente
         */
        $checkEsibente = array();
        if ($datiInfocamere['datiAdempimento']['codice_pratica']) {
            if ($datiInfocamere['datiEsibente']['esibente_qualifica'] == "") {
                $checkEsibente[] = "manca la qualifica del legale rappresentante";
            }
        }

        /*
         * Controlli su legale rappresentante
         */
        $COMUNI_DB = ItaDB::DBOpen('COMUNI', '');
        $provicicaSedeLegale_rec = ItaDB::DBSQLSelect($COMUNI_DB, "SELECT * FROM PROVINCE WHERE SIGLA = '" . trim(addslashes($datiInfocamere['datiSedeLegale']['sedeLegale_provincia'])) . "'", false);
        if (!$provicicaSedeLegale_rec) {
            $checkLegRapp[] = "Provincia Sede Legale {$datiInfocamere['datiSedeLegale']['sedeLegale_provincia']} non trovata.Si prega di tornare nel modello principale e scrivere correttamente la provincia.";
        }
        $comuneSedeLegale_rec = ItaDB::DBSQLSelect($COMUNI_DB, "SELECT * FROM COMUNI WHERE COMUNE = '" . trim(addslashes($datiInfocamere['datiSedeLegale']['sedeLegale_comune'])) . "'", false);
        if (!$comuneSedeLegale_rec) {
            $comuneSedeLegaleSuggest_rec = ItaDB::DBSQLSelect($COMUNI_DB, "SELECT * FROM COMUNI WHERE COMUNE LIKE '%" . trim(addslashes($datiInfocamere['datiSedeLegale']['sedeLegale_comune'])) . "%'", true);
        }
        //$comuneSedeLegale_rec = ItaDB::DBSQLSelect($COMUNI_DB, "SELECT * FROM COMUNI WHERE COMUNE LIKE '{$datiInfocamere['datiSedeLegale']['sedeLegale_comune']}%'", true);
        $strComuni = array();
        if (count($comuneSedeLegaleSuggest_rec) > 0) {
            foreach ($comuneSedeLegaleSuggest_rec as $comune_rec) {
                $strComuni[] = $comune_rec['COMUNE'] . "  (" . $comune_rec['PROVIN'] . ")";
            }
            $checkLegRapp[] = "<span title=\"Vedi Errori \" style=\"color:red;\">Per la sede legale della ditta il comune inserito <span style=\"font-weight:bold;color:black;\">( {$datiInfocamere['datiSedeLegale']['sedeLegale_comune']} )</span> non è verificabile.<br>Controlla tra i vari comuni suggeriti,</span>";
            $checkLegRapp[] = "<span title=\"Vedi Errori \" style=\"color:red;\">quindi torna ad insere il comune della sede legale correttamente.</span>";
            $checkLegRapp[] = implode('<br>', $strComuni) . '<br><br>';
        }

        //
        // Controlli su files da allegare
        //
        $i = 0;
        $checkAllegati = array();
        if ($datiInfocamere['files']['sequenza_passo_file_firmato'] == "") {
            $i = $i + 1;
            $checkAllegati[$i]['sequenza_passo'] = "";
            $checkAllegati[$i]['progressivo_passo'] = "";
            $checkAllegati[$i]['anomalia'] = "Manca Passo file adempimento firmato";
        }
        if ($datiInfocamere['files']['source_file_firmato'] == "") {
            $i = $i + 1;
            $checkAllegati[$i]['sequenza_passo'] = $datiInfocamere['files']['sequenza_passo_file_firmato'];
            $checkAllegati[$i]['progressivo_passo'] = $datiInfocamere['files']['progressivo_passo_file_firmato'];
            $checkAllegati[$i]['anomalia'] = "Manca il file Adempimento firmato";
        }
        if ($datiInfocamere['files']['sequenza_passo_file_non_firmato'] == "") {
            $i = $i + 1;
            $checkAllegati[$i]['sequenza_passo'] = "";
            $checkAllegati[$i]['progressivo_passo'] = "";
            $checkAllegati[$i]['anomalia'] = "Manca Passo Rapporto completo";
        }
        if ($datiInfocamere['files']['source_file_non_firmato'] == "") {
            $i = $i + 1;
            $checkAllegati[$i]['sequenza_passo'] = $datiInfocamere['files']['sequenza_passo_file_non_firmato'];
            $checkAllegati[$i]['progressivo_passo'] = $datiInfocamere['files']['progressivo_passo_file_non_firmato'];
            $checkAllegati[$i]['anomalia'] = "Rapporto Completo non generato.";
        }

        if (count($datiInfocamere['files']['allegati']) > 7) {
            $i = $i + 1;
            $checkAllegati[$i]['sequenza_passo'] = "";
            $checkAllegati[$i]['progressivo_passo'] = "";
            $checkAllegati[$i]['anomalia'] = "Superato il numero massimo di allegati (7)";
        }

        foreach ($datiInfocamere['files']['allegati'] as $allegato) {
            if ($allegato['nome_file'] == '') {
                $i = $i + 1;
                $checkAllegati[$i]['sequenza_passo'] = $allegato['sequenza_passo'];
                $checkAllegati[$i]['progressivo_passo'] = $allegato['progressivo_passo'];
                $checkAllegati[$i]['anomalia'] = "Manca file allegato del passo N. " . $checkAllegati[$i]['progressivo_passo'];
            }

            if (filesize($allegato['FILEPATH']) / 1048576 > 2) {
                $i = $i + 1;
                $checkAllegati[$i]['sequenza_passo'] = $allegato['sequenza_passo'];
                $checkAllegati[$i]['progressivo_passo'] = $allegato['progressivo_passo'];
                $checkAllegati[$i]['anomalia'] = "Dimensione massima dell'allegato del passo N. " . $checkAllegati[$i]['progressivo_passo'] . " (2 Mega-Byte) superata";
            }

            if ($allegato['codice_e_descrizione'] == '') {
                $i = $i + 1;
                $checkAllegati[$i]['sequenza_passo'] = $allegato['sequenza_passo'];
                $checkAllegati[$i]['progressivo_passo'] = $allegato['progressivo_passo'];
                $checkAllegati[$i]['anomalia'] = "Manca codice e descrizione Infocamere dell'allegato del passo N. " . $checkAllegati[$i]['progressivo_passo'];
            }
        }

        $checkInfocamere['datiImpresa'] = $checkDatiImpresa;
        $checkInfocamere['datiSportello'] = $checkDatiSportello;
        $checkInfocamere['datiAdempimenti'] = $checkDatiAdempimento;
        $checkInfocamere['datiEsibente'] = $checkEsibente;
        $checkInfocamere['datiLegRapp'] = $checkLegRapp;
        $checkInfocamere['files'] = $checkAllegati;
        $tot_err = count($checkInfocamere['datiImpresa']) + count($checkInfocamere['datiSportello']) + count($checkInfocamere['datiAdempimenti']) + count($checkInfocamere['datiEsibente']) + count($checkInfocamere['datiLegRapp']) + count($checkInfocamere['files']);
        $checkInfocamere['files'] = $checkAllegati;
        $checkInfocamere['Errors'] = $tot_err;
        return $checkInfocamere;
    }

    function getChecksErrMsg() {
        $html = new html();
        $arrHtmlErr = array();

        if (!$this->dati['Note_Infocamere']['INFOCAMERE']['DATE']) {
            /*
             * Errori dati impresa
             */
            foreach ($this->dati['dati_infocamere']['checks']['datiImpresa'] as $value) {
                $htmlContent .= $html->getAlert($value, '', 'error');
//                $htmlContent .= "<span title=\"Vedi Errori \" style=\"text-decoration: underline;color:red;\">$value</span><br>";
            }
            $arrHtmlErr['datiImpresa'] = $htmlContent;

            /*
             * Errori dati sportello
             */

            foreach ($this->dati['dati_infocamere']['checks']['datiSportello'] as $value1) {
                $htmlContent1 .= $html->getAlert($value1, '', 'error');
//                $htmlContent1 .= "<span title=\"Vedi Errori \" style=\"text-decoration: underline;color:red;\">$value1</span><br>";
            }
            $arrHtmlErr['datiSportello'] = $htmlContent1;

            /*
             * Errori dati adempimento
             */
            if ($this->dati['dati_infocamere']['checks']['datiAdempimenti']) {
                $htmlContent2 .= $html->getAlert($this->dati['dati_infocamere']['checks']['datiAdempimenti'], '', 'error');
//                $htmlContent2 = "<span title=\"Vedi Errori \" style=\"text-decoration: underline;color:red;\">" . $this->dati['dati_infocamere']['checks']['datiAdempimenti'] . "</span><br>";
                $arrHtmlErr['datiAdempimenti'] = $htmlContent2;
            }

            /*
             * Errori esibente
             */
            $htmlContent4 = "";
            foreach ($this->dati['dati_infocamere']['checks']['datiEsibente'] as $value4) {
                $htmlContent4 .= $html->getAlert($value4, '', 'error');
//                $htmlContent4 .= "<span title=\"Vedi Errori \" style=\"text-decoration: underline;color:red;\">$value4</span><br>";
            }
            $arrHtmlErr['datiEsibente'] = $htmlContent4;

            /*
             * Errori legale rappresentante
             */
            if (count($this->dati['dati_infocamere']['checks']['datiLegRapp'])) {
                $htmlContent3 = "";
                $htmlContent3 .= "Errori su dati Sede Legale:<br />";
                foreach ($this->dati['dati_infocamere']['checks']['datiLegRapp'] as $value3) {
                    $htmlContent3 .= "$value3<br />";
                }
                $arrHtmlErr['datiLegRapp'] = $html->getAlert($htmlContent3, '', 'error');
            }

            /*
             * Errori files
             */
            $htmlErrFiles = "";
            foreach ($this->dati['dati_infocamere']['checks']['files'] as $value) {
                $descrizione = $value['anomalia'];
                $passo = ""; //$value['progressivo_passo'];
                $salta = $value['sequenza_passo'];
                $allegato = $value['nome_file'];
                $spamApri = "<span class=\"legenda\">";
                $spamChiudi = "</span>";
                $presente = ""; // mancante ";
                if ($salta) {
                    $hRefPasso = " (<a href=\"" . ItaUrlUtil::GetPageUrl(array('event' => 'navClick',
                                'ricnum' => $this->dati['Proric_rec']['RICNUM'], 'seq' => $salta, 'risposta' => '', 'allegato' => '')) . "\">Vai al Passo</a>)";
                } else {
                    $hRefPasso = "";
                }
                $htmlErrFiles .= $html->getAlert($descrizione . $hRefPasso, '', 'error');
            }
            $arrHtmlErr['files'] = $htmlErrFiles;

            /*
             * Errori file firmato
             */
            if ($this->dati['dati_infocamere']['files']['source_file_firmato']) {
                $descrizione = "File Adempimento firmato presente.";
                $passo = $this->dati['dati_infocamere']['files']['progressivo_passo_file_firmato'];
                $salta = $this->dati['dati_infocamere']['files']['sequenza_passo_file_firmato'];
                $allegato = $this->dati['dati_infocamere']['files']['source_file_firmato'];
                $spamApri = $spamChiudi = "";
                $presente = ""; // presente ";
                if ($salta) {
                    $hRefPasso = " (<a title=\"Vedi Allegato \" style=\"text-decoration: underline;color:blue;\" href=\"" . ItaUrlUtil::GetPageUrl(array('event' => 'vediAllegato',
                                'seq' => $salta, 'file' => $allegato, 'ricnum' => $this->dati['Proric_rec']['RICNUM'])) . "\">Vedi Allegato</a>)";
                } else {
                    $hRefPasso = "";
                }
                $htmlErrFileFirmato .= $html->getAlert($descrizione . ' Passo N. ' . $passo . $hRefPasso);
                $arrHtmlErr['source_file_firmato'] = $htmlErrFileFirmato;
            }
        }

        return $arrHtmlErr;
    }

    function confermaDatiInfocamere() {
        /*
         * Se prima volta, aggiorno il campo utente telemaco nel profilo utente
         */
        $userTelemaco = frontOfficeApp::$cmsHost->getUserInfo('telemaco');
        if ($userTelemaco == "") {
            if (!frontOfficeApp::$cmsHost->setUserInfo('telemaco', $this->dati['ita_usertelemaco'])) {
                $this->errCode = "E0085";
                $this->errMessage = "Errore aggiornamento campo 'telemaco' del profilo utente nella richiesta n. " . $this->dati['Proric_rec']['RICNUM'];
                return false;
            }
        }

        /*
         * Se prima volta, aggiorno il campo codice fiscale telemaco nel profilo utente
         */
        $cfTelemaco = frontOfficeApp::$cmsHost->getUserInfo('cftelemaco');
        if ($cfTelemaco == "") {
            if (!frontOfficeApp::$cmsHost->setUserInfo('cftelemaco', $this->dati['ita_cftelemaco'])) {
                $this->errCode = "E0085";
                $this->errMessage = "Errore aggiornamento campo 'cftelemaco' del profilo utente nella richiesta n. " . $this->dati['Proric_rec']['RICNUM'];
                return false;
            }
            $retRibalta = $this->praLib->ribaltaDatoUtenteFO($this->dati['Proric_rec']['RICNUM'], $this->dati['Proric_rec']['RICPRO'], $this->dati['PRAM_DB'], "ESIBENTE_ITA_CFTELEMACO", $this->dati['ita_cftelemaco']);
            if ($retRibalta['Status'] == "-1") {
                $this->errCode = "E0085";
                $this->errMessage = $retRibalta['Status'] . " nella richiesta n. " . $this->dati['Proric_rec']['RICNUM'];
                return false;
            }
        }
        return true;
    }

}
