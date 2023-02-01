/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ANA_AUTOMEZZI` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `AUTO` smallint(6) NOT NULL,
  `DESCAUTO` varchar(20) COLLATE latin1_general_cs NOT NULL,
  `TARGA` varchar(10) COLLATE latin1_general_cs NOT NULL,
  `KMPART` double NOT NULL,
  `FOTOAUTO` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `COMITATO` smallint(6) NOT NULL,
  `NOTE` text COLLATE latin1_general_cs NOT NULL,
  `VTELAIO` varchar(20) COLLATE latin1_general_cs NOT NULL,
  `VCILINDRATA` varchar(5) COLLATE latin1_general_cs NOT NULL,
  `VCATALITICA` varchar(5) COLLATE latin1_general_cs NOT NULL,
  `VKW` varchar(6) COLLATE latin1_general_cs NOT NULL,
  `VHP` varchar(3) COLLATE latin1_general_cs NOT NULL,
  `VPOSTI` smallint(6) NOT NULL,
  `VPORTATA` double NOT NULL,
  `VTARA` double NOT NULL,
  `VASSI` varchar(1) COLLATE latin1_general_cs NOT NULL,
  `VDISELECO` varchar(1) COLLATE latin1_general_cs NOT NULL,
  `VDATAIMM` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `CODICEPRESTAZIONE` varchar(2) COLLATE latin1_general_cs NOT NULL,
  `TIPMEZZO` varchar(2) COLLATE latin1_general_cs NOT NULL,
  `CODUTE` varchar(20) COLLATE latin1_general_cs NOT NULL,
  `DATAOPER` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `TIMOPER` varchar(10) COLLATE latin1_general_cs NOT NULL,
  `FLAG_DIS` smallint(6) NOT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ANA_COMUNE` (
  `ROWID` int(11) NOT NULL AUTO_INCREMENT,
  `ANACAT` varchar(6) COLLATE latin1_general_cs NOT NULL,
  `ANACOD` varchar(6) COLLATE latin1_general_cs NOT NULL,
  `ANADES` varchar(100) COLLATE latin1_general_cs NOT NULL,
  `ANAFI1__1` varchar(100) COLLATE latin1_general_cs NOT NULL,
  `ANAFI1__2` varchar(100) COLLATE latin1_general_cs NOT NULL,
  `ANAFI1__3` varchar(100) COLLATE latin1_general_cs NOT NULL,
  `ANAFI1__4` varchar(100) COLLATE latin1_general_cs NOT NULL,
  `ANAFI2__1` double NOT NULL,
  `ANAFI2__2` double NOT NULL,
  `ANAFI2__3` double NOT NULL,
  `ANAFI2__4` double NOT NULL,
  `ANATEX` text COLLATE latin1_general_cs NOT NULL,
  PRIMARY KEY (`ROWID`),
  KEY `I_CODKEY` (`ANACAT`,`ANACOD`),
  KEY `I_DESKEY` (`ANACAT`,`ANADES`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ANA_LOOKUP` (
  `ROW_ID` int(11) NOT NULL AUTO_INCREMENT,
  `CODICE` varchar(6) COLLATE latin1_general_cs NOT NULL,
  `DESCRIZIONE` varchar(255) COLLATE latin1_general_cs NOT NULL,
  PRIMARY KEY (`ROW_ID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ANA_PEC` (
  `ROWID` int(11) NOT NULL AUTO_INCREMENT,
  `CFPI` varchar(16) COLLATE latin1_general_cs NOT NULL,
  `PEC` varchar(100) COLLATE latin1_general_cs NOT NULL,
  `FONTEDATI` varchar(20) COLLATE latin1_general_cs NOT NULL,
  `VALIDAFINOAL` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `DATAULTFORM` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `DATAINSERIMENTO` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `ORAINSERIMENTO` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `UTENTEINSERIMENTO` varchar(20) COLLATE latin1_general_cs NOT NULL,
  `DATAAGGIORNAMENTO` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `ORAAGGIORNAMENTO` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `UTENTEAGGIORNAMENTO` varchar(20) COLLATE latin1_general_cs NOT NULL,
  PRIMARY KEY (`ROWID`),
  KEY `I_CFPI` (`CFPI`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ANA_RECAPITISOGGETTI` (
  `ROW_ID` int(11) NOT NULL AUTO_INCREMENT,
  `ROW_ID_SOGGETTO` int(11) NOT NULL,
  `ROW_ID_ANARECAPITO` int(11) NOT NULL,
  `RECAPITO` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `NOTE` text COLLATE latin1_general_cs NOT NULL,
  `PREDEFINITO` tinyint(4) NOT NULL,
  `DATAVALINI` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `DATAVALFIN` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `FONTEDATI` varchar(20) COLLATE latin1_general_cs NOT NULL,
  `DATAINSERIMENTO` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `ORAINSERIMENTO` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `UTENTEINSERIMENTO` varchar(20) COLLATE latin1_general_cs NOT NULL,
  `DATAAGGIORNAMENTO` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `ORAAGGIORNAMENTO` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `UTENTEAGGIORNAMENTO` varchar(20) COLLATE latin1_general_cs NOT NULL,
  PRIMARY KEY (`ROW_ID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ANA_RUOLI` (
  `ROWID` int(11) NOT NULL AUTO_INCREMENT,
  `RUOCOD` varchar(4) COLLATE latin1_general_cs NOT NULL,
  `RUODES` text COLLATE latin1_general_cs NOT NULL,
  `RUOINSEDITOR` text COLLATE latin1_general_cs NOT NULL,
  `RUOINSDATE` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `RUOINSTIME` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `RUOUPDEDITOR` text COLLATE latin1_general_cs NOT NULL,
  `RUOUPDDATE` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `RUOUPDTIME` varchar(8) COLLATE latin1_general_cs NOT NULL,
  PRIMARY KEY (`ROWID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ANA_RUOLISOGGETTI` (
  `ROW_ID` int(11) NOT NULL AUTO_INCREMENT,
  `ROW_ID_PRESTATORE` int(11) NOT NULL,
  `ROW_ID_DATORE` int(11) NOT NULL,
  `ROW_ID_ANARUOLI` int(11) NOT NULL,
  `NOTE` text COLLATE latin1_general_cs NOT NULL,
  `PREDEFINITO` tinyint(4) NOT NULL,
  `DATAVALINI` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `DATAVALFIN` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `FONTEDATI` varchar(20) COLLATE latin1_general_cs NOT NULL,
  `DATAINSERIMENTO` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `ORAINSERIMENTO` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `UTENTEINSERIMENTO` varchar(20) COLLATE latin1_general_cs NOT NULL,
  `DATAAGGIORNAMENTO` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `ORAAGGIORNAMENTO` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `UTENTEAGGIORNAMENTO` varchar(20) COLLATE latin1_general_cs NOT NULL,
  PRIMARY KEY (`ROW_ID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ANA_SOGGETTI` (
  `ROWID` int(11) NOT NULL AUTO_INCREMENT,
  `COGNOME` varchar(200) COLLATE latin1_general_cs NOT NULL,
  `NOME` varchar(60) COLLATE latin1_general_cs NOT NULL,
  `CF` varchar(16) COLLATE latin1_general_cs NOT NULL,
  `DATANASCITA` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `CITTANASCITA` varchar(50) COLLATE latin1_general_cs NOT NULL,
  `PROVNASCITA` varchar(2) COLLATE latin1_general_cs NOT NULL,
  `CITTARESI` varchar(50) COLLATE latin1_general_cs NOT NULL,
  `PROVRESI` varchar(4) COLLATE latin1_general_cs NOT NULL,
  `CAPRESI` varchar(10) COLLATE latin1_general_cs NOT NULL COMMENT 'lunghezza 10 per cap stranieri',
  `DESCRIZIONEVIA` varchar(100) COLLATE latin1_general_cs NOT NULL,
  `CIVICO` varchar(10) COLLATE latin1_general_cs NOT NULL,
  `CODANAG` double NOT NULL,
  `DATAINSERIMENTO` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `ORAINSERIMENTO` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `UTENTEINSERIMENTO` varchar(20) COLLATE latin1_general_cs NOT NULL,
  `DATAAGGIORNAMENTO` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `ORAAGGIORNAMENTO` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `UTENTEAGGIORNAMENTO` varchar(20) COLLATE latin1_general_cs NOT NULL,
  `CHIUSURA` text COLLATE latin1_general_cs NOT NULL,
  `NATGIU` smallint(6) NOT NULL COMMENT '0=Fisica,1=Giuridica',
  `PIVA` varchar(16) COLLATE latin1_general_cs NOT NULL,
  `FONTEDATI` varchar(20) COLLATE latin1_general_cs NOT NULL,
  `DATAULTFORM` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `NOTE` text COLLATE latin1_general_cs NOT NULL,
  PRIMARY KEY (`ROWID`),
  KEY `I_COGNOME` (`COGNOME`),
  KEY `I_NOME` (`NOME`),
  KEY `I_COGNOM` (`COGNOME`,`NOME`),
  KEY `I_CFPI` (`CF`,`PIVA`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `BGE_EXCELD` (
  `ROW_ID` int(11) NOT NULL AUTO_INCREMENT,
  `PROGINT` int(11) NOT NULL,
  `PROG_RIGA` int(11) NOT NULL,
  `NOMECAMPOE` varchar(30) COLLATE latin1_general_cs NOT NULL,
  `NOMECOLON` varchar(60) COLLATE latin1_general_cs NOT NULL,
  `ISTR_CAT` varchar(300) COLLATE latin1_general_cs NOT NULL,
  `CODUTE` varchar(20) COLLATE latin1_general_cs NOT NULL,
  `DATAOPER` date NOT NULL,
  `TIMEOPER` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `COL_META` varchar(4000) COLLATE latin1_general_cs NOT NULL,
  PRIMARY KEY (`ROW_ID`),
  UNIQUE KEY `BGE_EXCELDK00` (`PROGINT`,`PROG_RIGA`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `BGE_EXCELP` (
  `ROW_ID` int(11) NOT NULL AUTO_INCREMENT,
  `CODUTE` varchar(20) COLLATE latin1_general_cs NOT NULL,
  `NOMESCHEMA` varchar(30) COLLATE latin1_general_cs NOT NULL,
  `DES_SELEZ` varchar(120) COLLATE latin1_general_cs NOT NULL,
  `CAMPOUNICO` varchar(1000) COLLATE latin1_general_cs NOT NULL,
  `DATAOPER` date NOT NULL,
  `TIMEOPER` varchar(8) COLLATE latin1_general_cs NOT NULL,
  PRIMARY KEY (`ROW_ID`),
  UNIQUE KEY `BGE_EXCELPK00` (`CODUTE`,`NOMESCHEMA`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `BGE_EXCELT` (
  `ROW_ID` int(11) NOT NULL AUTO_INCREMENT,
  `PROGINT` int(11) NOT NULL,
  `NOMEWIN` varchar(50) COLLATE latin1_general_cs NOT NULL,
  `UTENTE` varchar(20) COLLATE latin1_general_cs NOT NULL,
  `DES_EXPORT` varchar(120) COLLATE latin1_general_cs NOT NULL,
  `PERCORSO` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `NOME_FILE` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `TIPO_ESPOR` smallint(6) NOT NULL,
  `CODUTE` varchar(20) COLLATE latin1_general_cs NOT NULL,
  `TIMEOPER` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `DATAOPER` date NOT NULL,
  `FLAG_DIS` smallint(6) NOT NULL,
  PRIMARY KEY (`ROW_ID`),
  UNIQUE KEY `BGE_EXCELT` (`PROGINT`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `BUILD_DEFT` (
  `ROW_ID` int(11) NOT NULL AUTO_INCREMENT,
  `BUILD_NAME` varchar(50) COLLATE latin1_general_cs NOT NULL,
  `BUILD_TYPES` smallint(6) NOT NULL,
  `BUILD_RELEASE_REF` varchar(10) COLLATE latin1_general_cs NOT NULL,
  `BUILD_NOTES` text COLLATE latin1_general_cs NOT NULL,
  PRIMARY KEY (`ROW_ID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `CAL_ATTIVITA` (
  `ROWID` int(11) NOT NULL AUTO_INCREMENT,
  `TITOLO` varchar(100) COLLATE latin1_general_cs NOT NULL,
  `DESCRIZIONE` text COLLATE latin1_general_cs NOT NULL,
  `START` varchar(14) COLLATE latin1_general_cs NOT NULL,
  `END` varchar(14) COLLATE latin1_general_cs NOT NULL,
  `ALLDAY` int(11) NOT NULL,
  `ROWID_CALENDARIO` int(11) NOT NULL,
  `CLASSEAPP` varchar(32) COLLATE latin1_general_cs NOT NULL,
  `CLASSEROWID` int(11) NOT NULL,
  `CLASSEMETA` text COLLATE latin1_general_cs NOT NULL,
  `COMPLETATO` int(11) NOT NULL,
  `PRIORITA` int(11) NOT NULL,
  `METADATI` text COLLATE latin1_general_cs NOT NULL,
  `CLASSEVENTO` varchar(4) COLLATE latin1_general_cs NOT NULL COMMENT 'Codice Classificazione evento',
  `RIPETI` int(11) NOT NULL COMMENT 'RIPETI SCANDENZA',
  `TEMPO` int(11) NOT NULL COMMENT 'OGNI N MESI',
  `UNITA` int(11) NOT NULL COMMENT 'COSTANTE PER MESE',
  `TERMINA` varchar(8) COLLATE latin1_general_cs NOT NULL COMMENT 'TERMINA IL',
  PRIMARY KEY (`ROWID`),
  KEY `ROWID_CALENDARIO` (`ROWID_CALENDARIO`),
  KEY `I_EVTSTART` (`START`),
  KEY `I_EVTEND` (`END`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `CAL_CALENDARI` (
  `ROWID` int(11) NOT NULL AUTO_INCREMENT,
  `TITOLO` varchar(100) COLLATE latin1_general_cs NOT NULL,
  `METADATI` text COLLATE latin1_general_cs NOT NULL,
  `UTENTE` double NOT NULL,
  `GRUPPO` double NOT NULL,
  `TIPO` varchar(32) COLLATE latin1_general_cs NOT NULL,
  `PROPRIETARIO` varchar(4) COLLATE latin1_general_cs NOT NULL,
  `GRUPPI` varchar(4) COLLATE latin1_general_cs NOT NULL,
  `ALTRI` varchar(4) COLLATE latin1_general_cs NOT NULL,
  PRIMARY KEY (`ROWID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `CAL_EVENTI` (
  `ROWID` int(11) NOT NULL AUTO_INCREMENT,
  `TITOLO` varchar(100) COLLATE latin1_general_cs NOT NULL,
  `DESCRIZIONE` text COLLATE latin1_general_cs NOT NULL,
  `START` varchar(14) COLLATE latin1_general_cs NOT NULL,
  `END` varchar(14) COLLATE latin1_general_cs NOT NULL,
  `ALLDAY` int(11) NOT NULL,
  `ROWID_CALENDARIO` int(11) NOT NULL,
  `CLASSEAPP` varchar(32) COLLATE latin1_general_cs NOT NULL,
  `CLASSEROWID` int(11) NOT NULL,
  `CLASSEMETA` text COLLATE latin1_general_cs NOT NULL,
  `METADATI` text COLLATE latin1_general_cs NOT NULL,
  `CLASSEVENTO` varchar(4) COLLATE latin1_general_cs NOT NULL COMMENT 'Codice Classificazione evento',
  `RIPETI` int(11) NOT NULL COMMENT 'RIPETI SCANDENZA',
  `TEMPO` int(11) NOT NULL COMMENT 'OGNI N MESI',
  `UNITA` int(11) NOT NULL COMMENT 'COSTANTE PER MESE',
  `TERMINA` varchar(8) COLLATE latin1_general_cs NOT NULL COMMENT 'TERMINA IL',
  PRIMARY KEY (`ROWID`),
  KEY `ROWID_CALENDARIO` (`ROWID_CALENDARIO`),
  KEY `I_EVTSTART` (`START`),
  KEY `I_EVTEND` (`END`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `CAL_PROMEMORIA` (
  `ROWID` int(11) NOT NULL AUTO_INCREMENT,
  `TIPO` varchar(16) COLLATE latin1_general_cs NOT NULL,
  `TEMPO` int(11) NOT NULL,
  `UNITA` int(11) NOT NULL,
  `SCADENZA` int(11) NOT NULL COMMENT 'timestamp unix',
  `INVIATO` int(11) NOT NULL COMMENT 'timestamp unix',
  `TAB_GENITORE` varchar(32) COLLATE latin1_general_cs NOT NULL,
  `ROWID_GENITORE` int(11) NOT NULL,
  PRIMARY KEY (`ROWID`),
  KEY `ROWID_EVENTO` (`ROWID_GENITORE`),
  KEY `TAB_GENITORE` (`TAB_GENITORE`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `CAL_TABFESTA` (
  `ROWID` int(11) NOT NULL AUTO_INCREMENT,
  `DATAFESTA` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `NOMEFESTA` varchar(50) COLLATE latin1_general_cs NOT NULL,
  `TIPOFESTA` varchar(2) COLLATE latin1_general_cs NOT NULL,
  PRIMARY KEY (`ROWID`),
  UNIQUE KEY `DATAFESTA` (`DATAFESTA`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `CART_ATTIVITA` (
  `ROW_ID` int(11) NOT NULL AUTO_INCREMENT,
  `IDCARTCATEGORIA` int(11) NOT NULL,
  `CODICE` varchar(200) COLLATE latin1_general_cs NOT NULL,
  `DESCATTIVITA` text COLLATE latin1_general_cs NOT NULL,
  `DATAINIZIO` date NOT NULL,
  `DATAFINE` date NOT NULL,
  `DESCREGIONALE` text COLLATE latin1_general_cs NOT NULL,
  `ALTREINFOREG` text COLLATE latin1_general_cs NOT NULL,
  `REQUISITIOGGETTIVI` text COLLATE latin1_general_cs NOT NULL,
  `REQUISITIMORALI` text COLLATE latin1_general_cs NOT NULL,
  `REQUISITIPROFESSIONALI` text COLLATE latin1_general_cs NOT NULL,
  `REQUISITIEXTRACOMUNITARI` text COLLATE latin1_general_cs NOT NULL,
  `APERSTANDARD` varchar(250) COLLATE latin1_general_cs NOT NULL,
  `APERNOSTANDARD` varchar(250) COLLATE latin1_general_cs NOT NULL,
  `APERNOSTANDARDFILE` varchar(250) COLLATE latin1_general_cs NOT NULL,
  `APERNOSTANDARDDATIFILE` text COLLATE latin1_general_cs NOT NULL,
  `QUANDOINIZIARE` text COLLATE latin1_general_cs NOT NULL,
  `ALTRECOMUNICAZIONI` text COLLATE latin1_general_cs NOT NULL,
  `TEMPICONCLUSIONE` text COLLATE latin1_general_cs NOT NULL,
  `MARCHEBOLLO` varchar(250) COLLATE latin1_general_cs NOT NULL,
  `ADEMPIMENTIREG` text COLLATE latin1_general_cs NOT NULL,
  `DATASCAD` date NOT NULL,
  `MODRINNOVO` text COLLATE latin1_general_cs NOT NULL,
  `ENTERINNOVO` varchar(250) COLLATE latin1_general_cs NOT NULL,
  `NOTEREGIONALI` text COLLATE latin1_general_cs NOT NULL,
  `FORMULAZIONE` text COLLATE latin1_general_cs NOT NULL,
  `TABELLANOMEFILE` varchar(250) COLLATE latin1_general_cs NOT NULL,
  `TABELLADATIFILE` text COLLATE latin1_general_cs NOT NULL,
  `FILEWORD` varchar(250) COLLATE latin1_general_cs NOT NULL,
  `FILEPDF` varchar(250) COLLATE latin1_general_cs NOT NULL,
  `TABELLATIPOCONTENUTO` varchar(250) COLLATE latin1_general_cs NOT NULL,
  `DATASCADENZA` varchar(250) COLLATE latin1_general_cs NOT NULL,
  `USATO` varchar(1) COLLATE latin1_general_cs NOT NULL,
  PRIMARY KEY (`ROW_ID`),
  KEY `IDCARTCATEGORIA` (`IDCARTCATEGORIA`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs COMMENT='Procedimenti Suap presenti nel CART';
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `CART_INVIO` (
  `ROW_ID` int(11) NOT NULL AUTO_INCREMENT,
  `IDMSGCARTSTIMOLO` varchar(100) COLLATE latin1_general_cs NOT NULL COMMENT 'Collegamento con tabella CART_STIMOLO',
  `IDMESSAGGIO` varchar(100) COLLATE latin1_general_cs NOT NULL COMMENT 'Identificativo del messaggio',
  `DATAINVIO` varchar(20) COLLATE latin1_general_cs NOT NULL COMMENT 'Data di invio',
  `TIPOINVIO` varchar(100) COLLATE latin1_general_cs NOT NULL COMMENT 'Tipo del messaggio che si invia',
  `DESTINATARIO` varchar(50) COLLATE latin1_general_cs NOT NULL COMMENT 'Destinatario',
  `ESITO` varchar(100) COLLATE latin1_general_cs NOT NULL COMMENT 'Esito invio',
  `IDTABRIF` int(11) NOT NULL COMMENT 'Identificativo tabella da cui è stato fatto l''invio',
  `NOMETABRIF` varchar(100) COLLATE latin1_general_cs NOT NULL COMMENT 'Nome tabella da cui è stato fatto l''invio',
  `DATACONFRICEZIONE` varchar(20) COLLATE latin1_general_cs NOT NULL COMMENT 'Data Conferma Ricezione',
  `DATARICEZIONE` date NOT NULL COMMENT 'Data Ricezione',
  `RICHIEDENTE` varchar(100) COLLATE latin1_general_cs NOT NULL COMMENT 'Chi richiede l''invio',
  `NUMPROTOCOLLO` varchar(50) COLLATE latin1_general_cs NOT NULL COMMENT 'Numero protocollo',
  `DATAPROTOCOLLO` date NOT NULL COMMENT 'Data Protocollo',
  `EDILIZIA` varchar(1) COLLATE latin1_general_cs NOT NULL COMMENT 'Invio all''ufficio Edilizia. Disponibile sul Servizio Web',
  `ESITORICEZIONE` varchar(250) COLLATE latin1_general_cs NOT NULL COMMENT 'Esito Ricezione',
  `MSGRICEZIONE` text COLLATE latin1_general_cs NOT NULL COMMENT 'Metadati in formato JASON del messaggio di ricezione esito',
  `IDMSGANNULLATO` varchar(100) COLLATE latin1_general_cs NOT NULL COMMENT 'Identificativo del messaggio annulato in caso di Segnazione Errore',
  PRIMARY KEY (`ROW_ID`),
  UNIQUE KEY `IDMESSAGGIO` (`IDMESSAGGIO`,`IDMSGCARTSTIMOLO`),
  KEY `IDCARTSTIMOLO` (`IDMSGCARTSTIMOLO`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs COMMENT='Messaggi inviati al CART';
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `CART_INVIOFILE` (
  `ROW_ID` int(11) NOT NULL AUTO_INCREMENT,
  `IDMSGCARTSTIMOLO` varchar(100) COLLATE latin1_general_cs NOT NULL COMMENT 'Collegamento con tabella CART_STIMOLO',
  `IDMSGCARTINVIO` varchar(100) COLLATE latin1_general_cs NOT NULL COMMENT 'Identificativo del messaggio',
  `NOMEFILE` varchar(200) COLLATE latin1_general_cs NOT NULL COMMENT 'Nome File',
  `HASHFILE` varchar(100) COLLATE latin1_general_cs NOT NULL COMMENT 'Hash del file',
  `CONTENTID` varchar(200) COLLATE latin1_general_cs NOT NULL COMMENT 'ID del file',
  `CONTENTTYPE` varchar(100) COLLATE latin1_general_cs NOT NULL COMMENT 'Tipo del file',
  `ESITO` varchar(100) COLLATE latin1_general_cs NOT NULL COMMENT 'Esito invio file',
  `FILEFIL` varchar(200) COLLATE latin1_general_cs NOT NULL COMMENT 'Nome con cui viene salvato il file nel fileSystem',
  PRIMARY KEY (`ROW_ID`),
  UNIQUE KEY `IDCARTINVIO` (`IDMSGCARTSTIMOLO`,`IDMSGCARTINVIO`,`NOMEFILE`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs COMMENT='Allegati associati ai messaggi inviati al CART';
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `CART_STIMOLO` (
  `ROW_ID` int(11) NOT NULL AUTO_INCREMENT,
  `IDMESSAGGIO` varchar(100) COLLATE latin1_general_cs NOT NULL COMMENT 'Identificativo del messaggio',
  `MITTENTE_TIPO` varchar(50) COLLATE latin1_general_cs NOT NULL COMMENT 'Tipo Mittente',
  `MITTENTE_ENTE` varchar(50) COLLATE latin1_general_cs NOT NULL COMMENT 'Codice Ente Mittente',
  `DATACAR` varchar(20) COLLATE latin1_general_cs NOT NULL COMMENT 'Data Estesa arrivo',
  `DATA` date NOT NULL COMMENT 'Data arrivo',
  `IDPRATICA` varchar(50) COLLATE latin1_general_cs NOT NULL COMMENT 'Identificativo Pratica CART',
  `TIPOSTIMOLO` varchar(100) COLLATE latin1_general_cs NOT NULL COMMENT 'Tipo messaggio ricevuto',
  `TIPOPROCEDIMENTO` varchar(50) COLLATE latin1_general_cs NOT NULL COMMENT 'Tipo del procedimento',
  `LETTOTUTTO` smallint(6) NOT NULL COMMENT '0 - Non Letto Tutto ; 1-Letto tutto',
  `CONFERMARICEZIONE` smallint(6) NOT NULL COMMENT '1-Inviatala confema ricezione ; 0-Non inviata la conferma ricezione',
  `PRAFOLISTROWID` int(11) NOT NULL COMMENT 'ROW_ID di PRAFOLIST in cui si riporta il messaggio',
  `OGGETTO` varchar(250) COLLATE latin1_general_cs NOT NULL COMMENT 'Oggetto del messaggio',
  `MESSAGGIO` varchar(2000) COLLATE latin1_general_cs NOT NULL COMMENT 'Corpo del messaggio ricevuto',
  `NUMGIORNICAR` varchar(50) COLLATE latin1_general_cs NOT NULL COMMENT 'Numero giorni scadenza',
  `NUMGIORNI` int(11) NOT NULL COMMENT 'Numero giorni scadenza',
  `PROPOSTA` smallint(6) NOT NULL COMMENT 'Proposta 0-false; 1-true',
  `CODICEENDO` varchar(50) COLLATE latin1_general_cs NOT NULL COMMENT 'Codice Endo',
  `IDENTIFICATIVOMODULO` varchar(50) COLLATE latin1_general_cs NOT NULL COMMENT 'Identificativo modulo',
  `DESTINATARIO` varchar(100) COLLATE latin1_general_cs NOT NULL COMMENT 'Destinatario del messaggio',
  `NUMPROTOCOLLO` varchar(50) COLLATE latin1_general_cs NOT NULL COMMENT 'Numero protocollo',
  `DATAPROTOCOLLO` date NOT NULL COMMENT 'Data protocollo',
  `CODERRORE` varchar(250) COLLATE latin1_general_cs NOT NULL COMMENT 'Codice Errore ricevuto da una Conferma Ricezione',
  `DESCERRORE` varchar(250) COLLATE latin1_general_cs NOT NULL COMMENT 'Descrizione Errore ricevuto da una Conferma Ricezione',
  `EDILIZIA` smallint(6) NOT NULL COMMENT '1-Da rendere disponibile per Edilizia',
  `ED_LETTA` smallint(6) NOT NULL COMMENT '1-Edilizia ha letto il messaggio',
  `METADATI` text COLLATE latin1_general_cs NOT NULL COMMENT 'Metadati in formato JASON',
  PRIMARY KEY (`ROW_ID`),
  UNIQUE KEY `IDMESSAGGIO` (`IDMESSAGGIO`),
  KEY `CONFERMARIC` (`CONFERMARICEZIONE`,`LETTOTUTTO`),
  KEY `TABRIF` (`PRAFOLISTROWID`),
  KEY `TIPOSTIMOLO` (`TIPOSTIMOLO`,`PRAFOLISTROWID`),
  KEY `IDPRATICA` (`IDPRATICA`,`TIPOSTIMOLO`),
  KEY `EDILIZIA` (`EDILIZIA`,`LETTOTUTTO`),
  KEY `LETTO` (`LETTOTUTTO`,`IDMESSAGGIO`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs COMMENT='Salvataggio messaggi riletti dal CART';
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `CART_STIMOLOFILE` (
  `ROW_ID` int(11) NOT NULL AUTO_INCREMENT,
  `IDMSGCARTSTIMOLO` varchar(100) COLLATE latin1_general_cs NOT NULL COMMENT 'Collegamento CARTSTIMOLO',
  `IDFILE` varchar(200) COLLATE latin1_general_cs NOT NULL COMMENT 'Identificativo Univoco file',
  `NOMEFILE` varchar(200) COLLATE latin1_general_cs NOT NULL COMMENT 'Nome File',
  `HASHFILE` varchar(100) COLLATE latin1_general_cs NOT NULL COMMENT 'Hash del File',
  `TIPOCONTENUTO` varchar(100) COLLATE latin1_general_cs NOT NULL COMMENT 'Tipo di contenuto del file',
  `DIMENSIONE` varchar(20) COLLATE latin1_general_cs NOT NULL COMMENT 'Dimensione',
  `USOMODELLO` varchar(50) COLLATE latin1_general_cs NOT NULL COMMENT 'Tipo di uso modello (STANDARD0; ASL21; ecc..)',
  `NOTE` varchar(250) COLLATE latin1_general_cs NOT NULL COMMENT 'Note',
  `CODICE` varchar(250) COLLATE latin1_general_cs NOT NULL COMMENT 'Codice',
  `IDSEMANTICO` varchar(250) COLLATE latin1_general_cs NOT NULL,
  `TIPOFILE` varchar(50) COLLATE latin1_general_cs NOT NULL COMMENT 'Tipologia di File (copertina-xml; copertina-pdf;modello-PDF; ecc)',
  `ENTE` varchar(150) COLLATE latin1_general_cs NOT NULL,
  `LINK` varchar(250) COLLATE latin1_general_cs NOT NULL COMMENT 'Link dove scaricare il file',
  `FIRMATO` varchar(50) COLLATE latin1_general_cs NOT NULL,
  PRIMARY KEY (`ROW_ID`),
  UNIQUE KEY `IDFILE` (`IDMSGCARTSTIMOLO`,`IDFILE`,`LINK`),
  KEY `USOMODELLO` (`USOMODELLO`,`IDMSGCARTSTIMOLO`),
  KEY `TIPOFILE` (`TIPOFILE`,`IDMSGCARTSTIMOLO`),
  KEY `LINK` (`IDMSGCARTSTIMOLO`,`LINK`),
  KEY `IDMESSAGGIO` (`IDMSGCARTSTIMOLO`,`NOMEFILE`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs COMMENT='Allegati riletti nei messaggi CART';
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `CART_STIMOLOFILE_DEST` (
  `ROW_ID` int(11) NOT NULL AUTO_INCREMENT,
  `DESTINATARIO` varchar(50) COLLATE latin1_general_cs NOT NULL COMMENT 'Destinatario del File (SUAP; ASL; AMBRT; ecc.)',
  `IDMSGCARTSTIMOLO` varchar(100) COLLATE latin1_general_cs NOT NULL COMMENT 'Collegamento CARTSTIMOLO',
  `IDFILE` varchar(200) COLLATE latin1_general_cs NOT NULL COMMENT 'Identificativo Univoco file',
  PRIMARY KEY (`ROW_ID`),
  UNIQUE KEY `CARTSTIMOLOFILE` (`IDMSGCARTSTIMOLO`,`IDFILE`,`DESTINATARIO`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs COMMENT='Associazione destinatari per gli allegati riletti nei messag';
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `CGW_DOMAINS` (
  `ROW_ID` int(11) NOT NULL AUTO_INCREMENT,
  `DOMAIN` varchar(20) COLLATE latin1_general_cs NOT NULL,
  `DESCRIPTION` text COLLATE latin1_general_cs NOT NULL,
  PRIMARY KEY (`ROW_ID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `CGW_REPOSITORIES` (
  `ROW_ID` int(11) NOT NULL AUTO_INCREMENT,
  `DOMAIN` int(11) NOT NULL COMMENT 'ROW_ID della tabella CGW_DOMAINS',
  `CONTEXT` varchar(6) COLLATE latin1_general_cs NOT NULL,
  `URI` text COLLATE latin1_general_cs NOT NULL,
  `SECRET` varchar(32) COLLATE latin1_general_cs NOT NULL,
  `UNENCRYPTED` smallint(6) NOT NULL,
  PRIMARY KEY (`ROW_ID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `CLA_ARCHIVIO` (
  `ROW_ID` int(11) NOT NULL AUTO_INCREMENT,
  `ARC_UUID` varchar(36) COLLATE latin1_general_cs NOT NULL,
  `ARC_SEQUENZA` smallint(6) NOT NULL,
  `ARC_PARENTUUID` varchar(36) COLLATE latin1_general_cs NOT NULL,
  `ARC_NODOUUID` varchar(36) COLLATE latin1_general_cs NOT NULL,
  `ARC_NODOUUID_PARENT` varchar(36) COLLATE latin1_general_cs NOT NULL,
  `ARC_DESCRIZIONE` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `ARC_UTEINS` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `ARC_DATAINS` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `ARC_TIMEINS` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `ARC_EDITORINS` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `ARC_UTEMOD` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `ARC_DATAMOD` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `ARC_TIMEMOD` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `ARC_EDITORMOD` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `ARC_TRASHED` tinyint(4) NOT NULL,
  `ARC_VALIDODA` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `ARC_VALIDOAL` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `ARC_SPENTO` tinyint(4) NOT NULL,
  `ARC_NOTE` text COLLATE latin1_general_cs NOT NULL,
  PRIMARY KEY (`ROW_ID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `CLA_ARCHIVIO_PERS` (
  `ROW_ID` int(11) NOT NULL AUTO_INCREMENT,
  `ARC_UUID` varchar(36) COLLATE latin1_general_cs NOT NULL,
  `ARC_DESCRIZIONE` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `ARC_NOTE` text COLLATE latin1_general_cs NOT NULL,
  `PER_UTEMOD` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `PER_DATAMOD` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `PER_TIMEMOD` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `PER_EDITORMOD` varchar(255) COLLATE latin1_general_cs NOT NULL,
  PRIMARY KEY (`ROW_ID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `CLA_DEFCAT` (
  `ROW_ID` int(11) NOT NULL AUTO_INCREMENT COMMENT 'Chiave primaria autoincrementale',
  `DEF_UUID` varchar(36) COLLATE latin1_general_cs NOT NULL COMMENT 'UUID definizione categoria',
  `DEF_DESCRIZIONE` varchar(255) COLLATE latin1_general_cs NOT NULL COMMENT 'Descrizione della categoria',
  `DEF_UTEINS` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `DEF_DATAINS` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `DEF_TIMEINS` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `DEF_EDITORINS` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `DEF_UTEMOD` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `DEF_DATAMOD` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `DEF_TIMEMOD` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `DEF_EDITORMOD` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `DEF_TRASHED` tinyint(4) NOT NULL,
  PRIMARY KEY (`ROW_ID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs COMMENT='Tabella anagrafica di definizione delle categorie di apparte';
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `CLA_DEFDATO` (
  `ROW_ID` int(11) NOT NULL AUTO_INCREMENT COMMENT 'Chiave primaria autoincrementale',
  `DEF_UUID` varchar(36) COLLATE latin1_general_cs NOT NULL COMMENT 'UUID definizione metadato',
  `DEF_DESCRIZIONE` varchar(255) COLLATE latin1_general_cs NOT NULL COMMENT 'Descrizione del metadato',
  `DEF_TIPO` varchar(50) COLLATE latin1_general_cs NOT NULL,
  `DEF_META` text COLLATE latin1_general_cs NOT NULL,
  `DEF_UTEINS` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `DEF_DATAINS` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `DEF_TIMEINS` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `DEF_EDITORINS` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `DEF_UTEMOD` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `DEF_DATAMOD` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `DEF_TIMEMOD` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `DEF_EDITORMOD` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `DEF_TRASHED` tinyint(4) NOT NULL,
  PRIMARY KEY (`ROW_ID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs COMMENT='Tabella anagrafica dei metadati di nodo classificazione';
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `CLA_DEFNODO` (
  `ROW_ID` int(11) NOT NULL AUTO_INCREMENT COMMENT 'Chiave primaria autoincrementale',
  `DEF_UUID` varchar(36) COLLATE latin1_general_cs NOT NULL COMMENT 'UUID nodo',
  `DEF_DESCRIZIONE` varchar(255) COLLATE latin1_general_cs NOT NULL COMMENT 'Descrizione del nodo',
  `DEF_NOTE` text COLLATE latin1_general_cs NOT NULL,
  `DEF_STATICO` tinyint(4) NOT NULL,
  `DEF_RICORSIVO` tinyint(4) NOT NULL,
  `DEF_TEMPLATEUUID` varchar(36) COLLATE latin1_general_cs NOT NULL,
  `DEF_UTEINS` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `DEF_DATAINS` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `DEF_TIMEINS` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `DEF_EDITORINS` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `DEF_UTEMOD` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `DEF_DATAMOD` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `DEF_TIMEMOD` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `DEF_EDITORMOD` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `DEF_TRASHED` tinyint(4) NOT NULL,
  `DEF_FLAGANAENT` tinyint(4) NOT NULL,
  PRIMARY KEY (`ROW_ID`),
  KEY `I_STRUNODI_UUID` (`DEF_UUID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs COMMENT='Tabella di definizione della struttura e dei nodi di classif';
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `CLA_ENTITIES` (
  `ROW_ID` int(11) NOT NULL AUTO_INCREMENT,
  `CLA_UUID` varchar(36) COLLATE latin1_general_cs NOT NULL,
  `CLA_SEQUENZA` smallint(6) NOT NULL,
  `ARC_UUID` varchar(36) COLLATE latin1_general_cs NOT NULL,
  `ENT_ROW_ID` int(11) NOT NULL,
  `ENT_CLASSE` int(11) NOT NULL,
  `CLA_UTEINS` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `CLA_DATAINS` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `CLA_TIMEINS` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `CLA_UTEMOD` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `CLA_DATAMOD` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `CLA_TIMEMOD` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `CLA_TRASHED` tinyint(4) NOT NULL,
  PRIMARY KEY (`ROW_ID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `CLA_ENTMETADATI` (
  `ROW_ID` int(11) NOT NULL AUTO_INCREMENT,
  `ENT_ROW_ID` int(11) NOT NULL,
  `ENT_CLASSE` int(11) NOT NULL,
  `ENT_CATUUID` varchar(36) COLLATE latin1_general_cs NOT NULL,
  `ENT_DATOUUID` varchar(36) COLLATE latin1_general_cs NOT NULL,
  `ENT_VALUE_VARCHAR` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `ENT_VALUE_INT` int(11) NOT NULL,
  `ENT_VALUE_FLOAT` float NOT NULL,
  `ENT_VALUE_DOUBLE` double NOT NULL,
  `ENT_VALUE_TEXT` text COLLATE latin1_general_cs NOT NULL,
  `ENT_TRASHED` tinyint(4) NOT NULL,
  PRIMARY KEY (`ROW_ID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `CLA_METADATI` (
  `ROW_ID` int(11) NOT NULL AUTO_INCREMENT,
  `MET_UUID` varchar(36) COLLATE latin1_general_cs NOT NULL,
  `MET_ARCUUID` varchar(36) COLLATE latin1_general_cs NOT NULL,
  `MET_CATUUID` varchar(36) COLLATE latin1_general_cs NOT NULL,
  `MET_DATOUUID` varchar(36) COLLATE latin1_general_cs NOT NULL,
  `MET_VALUE_VARCHAR` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `MET_VALUE_INT` int(11) NOT NULL,
  `MET_VALUE_FLOAT` float NOT NULL,
  `MET_VALUE_TEXT` text COLLATE latin1_general_cs NOT NULL,
  `MET_TRASHED` tinyint(4) NOT NULL,
  `MET_VALUE_DOUBLE` double NOT NULL,
  PRIMARY KEY (`ROW_ID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `CLA_RELCATARC` (
  `ROW_ID` int(11) NOT NULL AUTO_INCREMENT,
  `DEF_ARC_UUID` varchar(36) COLLATE latin1_general_cs NOT NULL,
  `DEF_CAT_UUID` varchar(36) COLLATE latin1_general_cs NOT NULL,
  `DEF_SHOWSEQ` smallint(6) NOT NULL,
  `DEF_UTEINS` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `DEF_DATAINS` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `DEF_TIMEINS` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `DEF_UTEMOD` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `DEF_DATAMOD` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `DEF_TIMEMOD` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `DEF_TRASHED` tinyint(4) NOT NULL,
  PRIMARY KEY (`ROW_ID`),
  UNIQUE KEY `UN_CATENT` (`DEF_ARC_UUID`,`DEF_CAT_UUID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `CLA_RELCATNODO` (
  `ROW_ID` int(11) NOT NULL AUTO_INCREMENT COMMENT 'Chiave primaria autoincrementale',
  `DEF_STRUTNODI_UUID` varchar(36) COLLATE latin1_general_cs NOT NULL COMMENT 'UUID definizione nodo',
  `DEF_CAT_UUID` varchar(36) COLLATE latin1_general_cs NOT NULL COMMENT 'UUID definizione categoria',
  `DEF_SHOWSEQ` smallint(6) NOT NULL COMMENT 'Sequenza di visualizzazione del gruppo di metadati legati alla categoria in gestione istanza nodo',
  `DEF_UTEINS` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `DEF_DATAINS` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `DEF_TIMEINS` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `DEF_UTEMOD` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `DEF_DATAMOD` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `DEF_TIMEMOD` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `DEF_TRASHED` tinyint(4) NOT NULL,
  PRIMARY KEY (`ROW_ID`),
  UNIQUE KEY `UN_CATNODO` (`DEF_STRUTNODI_UUID`,`DEF_CAT_UUID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs COMMENT='Tabella di relazione che identifica le categorie di metadato';
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `CLA_RELDATOCAT` (
  `ROW_ID` int(11) NOT NULL AUTO_INCREMENT COMMENT 'Chiave primaria autoincrementale',
  `CLA_RELDATO_UUID` varchar(36) COLLATE latin1_general_cs NOT NULL,
  `CLA_RELCAT_UUID` varchar(36) COLLATE latin1_general_cs NOT NULL,
  `CLA_RELSEQ` smallint(6) NOT NULL,
  `DEF_UTEINS` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `DEF_DATAINS` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `DEF_TIMEINS` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `DEF_UTEMOD` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `DEF_DATAMOD` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `DEF_TIMEMOD` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `DEF_TRASHED` tinyint(4) NOT NULL,
  PRIMARY KEY (`ROW_ID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `CLA_RELNODI` (
  `ROW_ID` int(11) NOT NULL AUTO_INCREMENT COMMENT 'Chiave primaria autoincrementale',
  `DEF_NODOUUID` varchar(36) COLLATE latin1_general_cs NOT NULL COMMENT 'UUID nodo',
  `DEF_NODOUUID_PARENT` varchar(36) COLLATE latin1_general_cs NOT NULL COMMENT 'UUID nodo padre',
  `DEF_RELSEQ` smallint(6) NOT NULL,
  `DEF_UTEINS` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `DEF_DATAINS` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `DEF_TIMEINS` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `DEF_UTEMOD` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `DEF_DATAMOD` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `DEF_TIMEMOD` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `DEF_TRASHED` tinyint(4) NOT NULL,
  PRIMARY KEY (`ROW_ID`),
  UNIQUE KEY `U_NODOPARENT` (`DEF_NODOUUID`,`DEF_NODOUUID_PARENT`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `CLA_VISCLASS` (
  `ROW_ID` int(11) NOT NULL AUTO_INCREMENT,
  `VIS_NODO` varchar(36) COLLATE latin1_general_cs NOT NULL,
  `VIS_CLASSE` int(11) NOT NULL,
  PRIMARY KEY (`ROW_ID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `DOC_ANAG_CLASSIFICAZIONE` (
  `ROW_ID` int(11) NOT NULL AUTO_INCREMENT,
  `ROW_ID_PADRE` int(11) NOT NULL,
  `OGGETTO` text COLLATE latin1_general_cs NOT NULL,
  `DESCRIZIONE` text COLLATE latin1_general_cs NOT NULL,
  `SEQUENZA` smallint(6) NOT NULL,
  `ANNULLATO` smallint(6) NOT NULL,
  `CODICEDOC` text COLLATE latin1_general_cs NOT NULL,
  PRIMARY KEY (`ROW_ID`),
  KEY `ROW_ID_PADRE` (`ROW_ID_PADRE`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `DOC_DOCUMENTI` (
  `ROWID` int(11) NOT NULL AUTO_INCREMENT,
  `CODICE` text COLLATE latin1_general_cs NOT NULL COMMENT 'CODICE',
  `CLASSIFICAZIONE` varchar(10) COLLATE latin1_general_cs NOT NULL COMMENT 'CLASSIFICAZIONE',
  `DATAREV` varchar(8) COLLATE latin1_general_cs NOT NULL COMMENT 'DATA ULTIMA REVISIONE',
  `NUMREV` int(11) NOT NULL COMMENT 'NUMERO PROGRESSIVO REVISIONE',
  `URI` text COLLATE latin1_general_cs NOT NULL COMMENT 'URI - NOME FILE',
  `OGGETTO` text COLLATE latin1_general_cs NOT NULL COMMENT 'DESCRIZIONE DOCUMENTO',
  `TIPO` varchar(20) COLLATE latin1_general_cs NOT NULL COMMENT 'TIPO TESTO: HTML EMBEDDED,MSWORD HTML,RTF,ODT,XML,TXT',
  `METADATI` text COLLATE latin1_general_cs NOT NULL COMMENT 'META DATI',
  `CONTENT` longblob NOT NULL,
  `CARATTERISTICA` varchar(100) COLLATE latin1_general_cs NOT NULL COMMENT 'Interno/Esterno',
  `SOCI` double NOT NULL COMMENT 'estendi oper soci',
  `OBBLIGATORIO` double NOT NULL COMMENT 'obbligatorio',
  `FUNZIONE` varchar(20) COLLATE latin1_general_cs NOT NULL,
  `DATASCAD` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `MAPPATURA` int(11) NOT NULL,
  PRIMARY KEY (`ROWID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `DOC_MAP_ANAG` (
  `ROW_ID` int(11) NOT NULL AUTO_INCREMENT,
  `DESCRIZIONE` varchar(50) COLLATE latin1_general_cs NOT NULL,
  `TIPO_SINTASSI` varchar(50) COLLATE latin1_general_cs NOT NULL,
  PRIMARY KEY (`ROW_ID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `DOC_MAP_VOCI` (
  `ROW_ID` int(11) NOT NULL AUTO_INCREMENT,
  `ANAG_ID` int(11) NOT NULL,
  `VARIABILE_EXT` text COLLATE latin1_general_cs NOT NULL,
  `VARIABILE_INT` text COLLATE latin1_general_cs NOT NULL,
  `DESCRIZIONE` text COLLATE latin1_general_cs NOT NULL,
  `METADATI` text COLLATE latin1_general_cs NOT NULL,
  PRIMARY KEY (`ROW_ID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `DOC_STORICO` (
  `ROWID` int(11) NOT NULL AUTO_INCREMENT,
  `CODICE` text COLLATE latin1_general_cs NOT NULL COMMENT 'CODICE',
  `CLASSIFICAZIONE` varchar(10) COLLATE latin1_general_cs NOT NULL COMMENT 'CLASSIFICAZIONE',
  `DATAREV` varchar(8) COLLATE latin1_general_cs NOT NULL COMMENT 'DATA ULTIMA REVISIONE',
  `NUMREV` int(11) NOT NULL COMMENT 'NUMERO PROGRESSIVO REVISIONE',
  `URI` text COLLATE latin1_general_cs NOT NULL COMMENT 'URI - NOME FILE',
  `OGGETTO` text COLLATE latin1_general_cs NOT NULL COMMENT 'DESCRIZIONE DOCUMENTO',
  `TIPO` varchar(20) COLLATE latin1_general_cs NOT NULL COMMENT 'TIPO TESTO: HTML EMBEDDED,MSWORD HTML,RTF,ODT,XML,TXT',
  `METADATI` text COLLATE latin1_general_cs NOT NULL COMMENT 'META DATI',
  `CONTENT` longblob,
  `CARATTERISTICA` varchar(100) COLLATE latin1_general_cs NOT NULL COMMENT 'Interno/Esterno',
  `SOCI` double NOT NULL COMMENT 'estendi oper soci',
  `OBBLIGATORIO` double NOT NULL COMMENT 'obbligatorio',
  `FUNZIONE` varchar(20) COLLATE latin1_general_cs NOT NULL,
  `DATASCAD` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `MAPPATURA` int(11) NOT NULL,
  PRIMARY KEY (`ROWID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ENV_CONFIG` (
  `ROWID` int(11) NOT NULL AUTO_INCREMENT,
  `CHIAVE` varchar(50) COLLATE latin1_general_cs NOT NULL,
  `CLASSE` varchar(50) COLLATE latin1_general_cs NOT NULL,
  `CONFIG` text COLLATE latin1_general_cs NOT NULL,
  PRIMARY KEY (`ROWID`),
  UNIQUE KEY `CHIAVE` (`CHIAVE`,`CLASSE`),
  KEY `CHIAVE_2` (`CHIAVE`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ENV_NOTIFICHE` (
  `ROWID` int(11) NOT NULL AUTO_INCREMENT,
  `OGGETTO` varchar(80) COLLATE latin1_general_cs NOT NULL COMMENT 'Oggetto',
  `TESTO` text COLLATE latin1_general_cs NOT NULL COMMENT 'Testo Descrizione',
  `UTEINS` varchar(20) COLLATE latin1_general_cs NOT NULL COMMENT 'Utente Inserimento',
  `MODELINS` varchar(80) COLLATE latin1_general_cs NOT NULL,
  `DATAINS` varchar(8) COLLATE latin1_general_cs NOT NULL COMMENT 'Data Inserimento',
  `ORAINS` varchar(8) COLLATE latin1_general_cs NOT NULL COMMENT 'Ora Inserimento',
  `UTEDEST` varchar(20) COLLATE latin1_general_cs NOT NULL COMMENT 'Utente Destinatario',
  `DATADELIV` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `ORADELIV` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `DATAVIEW` varchar(8) COLLATE latin1_general_cs NOT NULL COMMENT 'Data Visualizzazione',
  `ORAVIEW` varchar(8) COLLATE latin1_general_cs NOT NULL COMMENT 'ORA visualizzazione',
  `ACTIONMENU` varchar(80) COLLATE latin1_general_cs NOT NULL COMMENT 'Codice menu azione',
  `ACTIONPROG` varchar(80) COLLATE latin1_general_cs NOT NULL COMMENT 'Codice programma azione',
  `ACTIONMODEL` varchar(80) COLLATE latin1_general_cs NOT NULL,
  `ACTIONPARAM` text COLLATE latin1_general_cs NOT NULL COMMENT 'Parametri Azione',
  `PRIORITY` tinyint(4) NOT NULL COMMENT 'Priorita',
  `STYLEAPP` text COLLATE latin1_general_cs NOT NULL COMMENT 'Configurazione apparenza',
  `METADATA` text COLLATE latin1_general_cs NOT NULL COMMENT 'Meta dati',
  `MAILDEST` varchar(255) COLLATE latin1_general_cs NOT NULL COMMENT 'Mail di destinazione Avviso di Notifica',
  `MAILDATE` varchar(8) COLLATE latin1_general_cs NOT NULL COMMENT 'DATA INVIO MAIL',
  `MAILTIME` varchar(8) COLLATE latin1_general_cs NOT NULL COMMENT 'ORA INVIO MAIL',
  `MAILTOSEND` smallint(6) NOT NULL COMMENT 'FLAG MAIL DA INVIARE',
  `MAILSENDATTEMPT` smallint(6) NOT NULL COMMENT 'NUMERO DI TENTATIVI DI INVIO',
  `MAILSENDERR` smallint(6) NOT NULL COMMENT 'INVIO MAIL ANNULLATO CON ERRORI',
  `MAILSENDMSG` text COLLATE latin1_general_cs NOT NULL COMMENT 'MESSAGGIO ULTIMO TENTATIVO DI INVIO',
  PRIMARY KEY (`ROWID`),
  KEY `I_UTEINS` (`UTEINS`),
  KEY `I_UTEDEST` (`UTEDEST`,`DATAVIEW`,`ORAVIEW`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ENV_PROCESSI` (
  `ROWID` int(11) NOT NULL AUTO_INCREMENT,
  `PROCTOKEN` varchar(40) COLLATE latin1_general_cs NOT NULL,
  `PROCDATE` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `PROCTIME` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `PROCUSER` varchar(40) COLLATE latin1_general_cs NOT NULL,
  `PROCINFO` text COLLATE latin1_general_cs NOT NULL,
  PRIMARY KEY (`ROWID`),
  UNIQUE KEY `PROCTOKEN` (`PROCTOKEN`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ENV_PROFILI` (
  `ROWID` int(11) NOT NULL AUTO_INCREMENT,
  `UTECOD` int(11) NOT NULL,
  `ELEMENTO` varchar(100) COLLATE latin1_general_cs NOT NULL,
  `CONFIG` text COLLATE latin1_general_cs,
  PRIMARY KEY (`ROWID`),
  KEY `UTECOD` (`UTECOD`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ENV_SEMAFORI` (
  `ROWID` int(11) NOT NULL AUTO_INCREMENT,
  `CHIAVE` varchar(40) COLLATE latin1_general_cs NOT NULL,
  `TABELLA` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `TOKEN` varchar(40) COLLATE latin1_general_cs NOT NULL,
  `UTENTE` varchar(40) COLLATE latin1_general_cs NOT NULL,
  `DATASEM` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `ORASEM` varchar(8) COLLATE latin1_general_cs NOT NULL,
  PRIMARY KEY (`ROWID`),
  UNIQUE KEY `UNIQ_SEMAFORI` (`CHIAVE`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ENV_TIPI` (
  `ROWID` int(11) NOT NULL AUTO_INCREMENT,
  `CODICE` varchar(4) COLLATE latin1_general_cs NOT NULL,
  `DESCRIZIONE` varchar(50) COLLATE latin1_general_cs NOT NULL,
  PRIMARY KEY (`ROWID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ENV_UTEMETA` (
  `ROWID` int(11) NOT NULL AUTO_INCREMENT,
  `UTECOD` double NOT NULL COMMENT 'Codice Utente',
  `METAKEY` varchar(255) COLLATE latin1_general_cs NOT NULL COMMENT 'Chiave Dati Extra',
  `METAVALUE` text COLLATE latin1_general_cs,
  PRIMARY KEY (`ROWID`),
  KEY `I_UTECOD` (`UTECOD`),
  KEY `I_METAKEY` (`UTECOD`,`METAKEY`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `FNG_GRUPPO` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `CONTESTO` varchar(20) COLLATE latin1_general_cs NOT NULL COMMENT 'Contesto applicativo',
  `IDPADRE` int(11) NOT NULL DEFAULT '0',
  `NOME` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `DESCRIZIONE` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `TRASHED` int(11) NOT NULL DEFAULT '0' COMMENT 'Flag eliminazione',
  `UTENTE_UMOD` varchar(255) COLLATE latin1_general_cs NOT NULL COMMENT 'Utente cancellazione',
  `DATA_UMOD` varchar(255) COLLATE latin1_general_cs NOT NULL COMMENT 'Data cancellazione',
  `SUPER` int(11) NOT NULL,
  PRIMARY KEY (`ID`),
  KEY `I_FNG_GRUPPO_K01` (`TRASHED`),
  KEY `I_FNG_GRUPPO_K02` (`CONTESTO`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `FNG_MEMBRI` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `IDGRUPPO` int(11) NOT NULL,
  `USERNAME` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `DATAINI` varchar(10) COLLATE latin1_general_cs NOT NULL COMMENT 'Data inizio validità',
  `DATAEND` varchar(10) COLLATE latin1_general_cs NOT NULL COMMENT 'Data fine validità',
  `TRASHED` int(11) NOT NULL DEFAULT '0' COMMENT 'Flag eliminazione',
  PRIMARY KEY (`ID`),
  KEY `I_FNG_MEMBRI_K01` (`IDGRUPPO`,`USERNAME`,`DATAINI`,`DATAEND`,`TRASHED`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `FNG_SECMETA` (
  `ROW_ID` int(11) NOT NULL AUTO_INCREMENT,
  `SEC_OBJ_ID` int(11) NOT NULL,
  `CHIAVE` varchar(60) COLLATE latin1_general_cs NOT NULL,
  `VALORE` varchar(60) COLLATE latin1_general_cs NOT NULL,
  PRIMARY KEY (`ROW_ID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `FNG_SECOBJ` (
  `ROW_ID` int(11) NOT NULL AUTO_INCREMENT,
  `GRUPPO_ID` int(11) NOT NULL,
  `OBJ_CLASS` varchar(60) COLLATE latin1_general_cs NOT NULL,
  `OBJ_ID` int(11) NOT NULL,
  `DATAINI` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `DATAEND` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `TRASHED` int(11) NOT NULL,
  PRIMARY KEY (`ROW_ID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `FRM_RICPAR` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `MODELKEY` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `KCODUTE` varchar(20) COLLATE latin1_general_cs NOT NULL,
  `PARAMETRI` text COLLATE latin1_general_cs,
  `PAR_OW` varchar(4000) COLLATE latin1_general_cs NOT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `GEO_ATTIVAZIONI` (
  `ROW_ID` int(11) NOT NULL AUTO_INCREMENT,
  `DESCRIZIONE` text COLLATE latin1_general_cs NOT NULL,
  `APPCONTEXT` varchar(80) COLLATE latin1_general_cs NOT NULL,
  `APPCONTEXFILTER` text COLLATE latin1_general_cs NOT NULL COMMENT 'json Format',
  `CODICEOGGETTO` int(11) NOT NULL,
  `METADATA` text COLLATE latin1_general_cs NOT NULL,
  `APPCLASS` text COLLATE latin1_general_cs NOT NULL,
  `APPMETHOD` text COLLATE latin1_general_cs NOT NULL,
  `NOTE` text COLLATE latin1_general_cs NOT NULL,
  PRIMARY KEY (`ROW_ID`),
  UNIQUE KEY `I_GEO_ATTIVATZIONI_TIPOOGGETTO_APP_CONTEXT` (`APPCONTEXT`,`CODICEOGGETTO`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `GEO_OGGETTI` (
  `ROW_ID` int(11) NOT NULL AUTO_INCREMENT,
  `CODICEOGGETTO` int(11) NOT NULL,
  `APPLOCALCONTEXT` varchar(80) COLLATE latin1_general_cs NOT NULL,
  `APPLOCALKEY` varchar(80) COLLATE latin1_general_cs NOT NULL,
  `APPREMOTECONTEXT` varchar(80) COLLATE latin1_general_cs NOT NULL,
  `APPREMOTEKEY` varchar(80) COLLATE latin1_general_cs NOT NULL,
  `APPREMOTEDATA` text COLLATE latin1_general_cs NOT NULL,
  `NOTE` text COLLATE latin1_general_cs NOT NULL,
  `ATTIVAZIONE_ROW_ID` int(11) NOT NULL,
  PRIMARY KEY (`ROW_ID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `GEO_TIPOGGETTO` (
  `ROW_ID` int(11) NOT NULL AUTO_INCREMENT,
  `CODICE` int(11) NOT NULL,
  `DESCRIZIONE` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `TIPOLOGIA` varchar(80) COLLATE latin1_general_cs NOT NULL,
  `METADATA` text COLLATE latin1_general_cs NOT NULL,
  `URITEMPLATE` text COLLATE latin1_general_cs NOT NULL,
  `CUSTOMCLASS` text COLLATE latin1_general_cs NOT NULL,
  `CUSTOMMETHOD` text COLLATE latin1_general_cs NOT NULL,
  PRIMARY KEY (`ROW_ID`),
  UNIQUE KEY `CODICE` (`CODICE`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `INIPEC_FLUSSI` (
  `ROW_ID` int(11) NOT NULL AUTO_INCREMENT,
  `DATAINVIO` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `ORAINVIO` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `IDRICHIESTA` varchar(20) COLLATE latin1_general_cs NOT NULL,
  `FILE_RICHIESTA` varchar(200) COLLATE latin1_general_cs NOT NULL,
  `SHA2_RICHIESTA` varchar(64) COLLATE latin1_general_cs NOT NULL,
  `FILE_RISPOSTA` varchar(200) COLLATE latin1_general_cs NOT NULL,
  `SHA2_RISPOSTA` varchar(64) COLLATE latin1_general_cs NOT NULL,
  `DATASCARICO` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `ORASCARICO` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `IPAPPCONTEXT` varchar(128) COLLATE latin1_general_cs NOT NULL,
  `IPAPPKEY` text COLLATE latin1_general_cs NOT NULL,
  PRIMARY KEY (`ROW_ID`),
  UNIQUE KEY `I_RICHIESTA` (`IDRICHIESTA`),
  KEY `I_CONTEXT` (`IPAPPCONTEXT`,`IPAPPKEY`(255))
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ITS_RAPPORTINI_GG` (
  `ROWID` int(11) NOT NULL AUTO_INCREMENT,
  `DATA` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `TECNICO` varchar(20) COLLATE latin1_general_cs NOT NULL,
  `DAORA` varchar(6) COLLATE latin1_general_cs NOT NULL,
  `AORA` varchar(6) COLLATE latin1_general_cs NOT NULL,
  `ORE` double NOT NULL,
  `DESCRIZIONE` text COLLATE latin1_general_cs NOT NULL,
  `CLIENTE` text COLLATE latin1_general_cs NOT NULL,
  `COMMESSA` varchar(20) COLLATE latin1_general_cs NOT NULL,
  `TIPO` text COLLATE latin1_general_cs NOT NULL,
  `RICHIEDEMODIFICA` tinyint(4) NOT NULL,
  `AZIONE` varchar(2) COLLATE latin1_general_cs NOT NULL,
  `NOTE` text COLLATE latin1_general_cs NOT NULL,
  PRIMARY KEY (`ROWID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `MAIL_ACCLIST` (
  `ROWID` int(11) NOT NULL AUTO_INCREMENT,
  `ACCOUNT` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `STRUID` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `RECDATE` varchar(8) COLLATE latin1_general_cs NOT NULL,
  PRIMARY KEY (`ROWID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `MAIL_ACCOUNT` (
  `ROWID` int(11) NOT NULL AUTO_INCREMENT,
  `NAME` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `MAILADDR` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `USER` text COLLATE latin1_general_cs NOT NULL,
  `PASSWORD` text COLLATE latin1_general_cs NOT NULL,
  `DOMAIN` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `CUSTOMHEADERS` text COLLATE latin1_general_cs NOT NULL,
  `SPOOLSENDDELAY` smallint(6) NOT NULL COMMENT 'Tempo di attesa sec. dopo invio massivo',
  PRIMARY KEY (`ROWID`),
  UNIQUE KEY `I_MAILADDR` (`MAILADDR`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs COMMENT='PARAMETRI ACCOUNT MAIL';
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `MAIL_ARCHIVIO` (
  `ROWID` int(11) NOT NULL AUTO_INCREMENT,
  `IDMAIL` text COLLATE latin1_general_cs NOT NULL COMMENT 'IDENTIFICATIVO',
  `MSGID` text COLLATE latin1_general_cs NOT NULL,
  `ACCOUNT` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `STRUID` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `FROMADDR` text COLLATE latin1_general_cs NOT NULL,
  `TOADDR` text COLLATE latin1_general_cs NOT NULL,
  `CCADDR` text COLLATE latin1_general_cs NOT NULL,
  `BCCADDR` text COLLATE latin1_general_cs NOT NULL,
  `SUBJECT` text COLLATE latin1_general_cs NOT NULL,
  `MSGDATE` varchar(20) COLLATE latin1_general_cs NOT NULL COMMENT 'YmdHis',
  `FOLDER` int(11) NOT NULL,
  `CLASS` text COLLATE latin1_general_cs NOT NULL,
  `DATAFILE` text COLLATE latin1_general_cs NOT NULL COMMENT 'NOME DEL FILE IN MD5',
  `METADATA` text COLLATE latin1_general_cs,
  `ATTACHMENTS` text COLLATE latin1_general_cs NOT NULL,
  `BODYTEXT` text COLLATE latin1_general_cs,
  `SENDREC` varchar(2) COLLATE latin1_general_cs NOT NULL COMMENT 'S=SEND ; R=RECEIVED',
  `READED` smallint(6) NOT NULL COMMENT 'CAMPO POSTA LETTA/APERTA',
  `PECTIPO` text COLLATE latin1_general_cs NOT NULL,
  `PECERRORE` text COLLATE latin1_general_cs NOT NULL,
  `INTEROPERABILE` smallint(6) NOT NULL,
  `TIPOINTEROPERABILE` varchar(20) COLLATE latin1_general_cs NOT NULL COMMENT 'TIPO MESSAGGIO INTEROPERABILE',
  `IDMAILPADRE` text COLLATE latin1_general_cs NOT NULL,
  `SCARTOMOTIVO` text COLLATE latin1_general_cs NOT NULL COMMENT 'MOTIVAZIONE DELLO SCARTO',
  `SENDRECDATE` varchar(8) COLLATE latin1_general_cs NOT NULL COMMENT 'Data di invio o ricezione',
  `SENDRECTIME` varchar(8) COLLATE latin1_general_cs NOT NULL COMMENT 'Ora di invio o ricezione',
  `PECERROREESTESO` text COLLATE latin1_general_cs NOT NULL COMMENT 'ERRORE PEC ESTESO',
  `TIMEARCEML` varchar(8) COLLATE latin1_general_cs NOT NULL COMMENT 'ORA ARCHIVIAZIONE EML',
  `DATEARCEML` varchar(8) COLLATE latin1_general_cs NOT NULL COMMENT 'DATA ARCHIVIAZIONE EML',
  `HASHARCEML` text COLLATE latin1_general_cs NOT NULL COMMENT 'HASH EML ARCHIVIATO',
  `STATOEML` smallint(6) NOT NULL COMMENT 'STATO ARCHIVIAZIONE EML',
  PRIMARY KEY (`ROWID`),
  KEY `I_IDMAIL` (`IDMAIL`(255)),
  KEY `I_IDMAILPADRE` (`IDMAILPADRE`(255))
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs COMMENT='ARCHIVIO EMAIL';
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `MAIL_AUTORIZZAZIONI` (
  `ROWID` int(11) NOT NULL AUTO_INCREMENT,
  `LOGIN` varchar(20) COLLATE latin1_general_cs NOT NULL,
  `MAIL` text COLLATE latin1_general_cs NOT NULL,
  `DADATA` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `ADATA` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `PERM_SEND` tinyint(4) NOT NULL,
  `PERM_REC` tinyint(4) NOT NULL,
  PRIMARY KEY (`ROWID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `MAIL_DOMAIN` (
  `ROWID` int(11) NOT NULL AUTO_INCREMENT,
  `NAME` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `DESCRIPTION` text COLLATE latin1_general_cs NOT NULL,
  `SMTPHOST` text COLLATE latin1_general_cs NOT NULL,
  `POP3HOST` text COLLATE latin1_general_cs NOT NULL,
  `SMTPPORT` int(11) NOT NULL,
  `POP3PORT` int(11) NOT NULL,
  `SMTPSECURE` varchar(20) COLLATE latin1_general_cs NOT NULL,
  `POP3SECURE` varchar(20) COLLATE latin1_general_cs NOT NULL,
  `POP3REALM` text COLLATE latin1_general_cs NOT NULL,
  `POP3AUTHM` text COLLATE latin1_general_cs NOT NULL,
  `POP3WORKST` text COLLATE latin1_general_cs NOT NULL,
  `DELMSG` smallint(6) NOT NULL,
  `DELWAIT` smallint(6) NOT NULL,
  `ISPEC` smallint(6) NOT NULL,
  PRIMARY KEY (`ROWID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs COMMENT='PARAMETRI ACCOUNT MAIL';
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `MAIL_ENVELOPES` (
  `ROW_ID` int(11) NOT NULL AUTO_INCREMENT,
  `PACKAGES_ROWID` int(11) NOT NULL,
  `EVPDATE` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `EVPTIME` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `EVPMAIL_ROWID` int(11) NOT NULL,
  `EVPMAILTO` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `EVPMAIL_ID` text COLLATE latin1_general_cs NOT NULL,
  `EVPXMLDATA` text COLLATE latin1_general_cs NOT NULL,
  `EVPSTATUS` smallint(6) NOT NULL,
  `EVPLASTMESSAGE` text COLLATE latin1_general_cs NOT NULL,
  `CODUTE` varchar(20) COLLATE latin1_general_cs NOT NULL,
  `DATAOPER` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `TIMEOPER` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `FLAG_DIS` smallint(6) NOT NULL,
  `FLAG_DIS_UTE` varchar(20) COLLATE latin1_general_cs NOT NULL,
  `FLAG_DIS_DATA` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `FLAG_DIS_TIME` varchar(8) COLLATE latin1_general_cs NOT NULL,
  PRIMARY KEY (`ROW_ID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `MAIL_FILTRI` (
  `ROWID` int(11) NOT NULL AUTO_INCREMENT,
  `ACCOUNT` varchar(200) COLLATE latin1_general_cs NOT NULL COMMENT 'NOME ACCOUNT',
  `NOME` text COLLATE latin1_general_cs NOT NULL,
  `SEQUENZA` int(11) NOT NULL,
  `FLAG_ATTIVO` double NOT NULL,
  `CLASSIFICA` varchar(200) COLLATE latin1_general_cs NOT NULL COMMENT 'Classifica come',
  `METADATA` text COLLATE latin1_general_cs NOT NULL,
  PRIMARY KEY (`ROWID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `MAIL_PACKAGES` (
  `ROW_ID` int(11) NOT NULL AUTO_INCREMENT,
  `PKGDATE` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `PKGTIME` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `PKGMAILACCOUNT` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `PKGCLOSEDATE` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `PKGCLOSETIME` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `PKGNOTE` text COLLATE latin1_general_cs NOT NULL,
  `PKGAPPCONTEXT` text COLLATE latin1_general_cs NOT NULL,
  `PKGAPPKEY` text COLLATE latin1_general_cs NOT NULL,
  `PKGFLAGACTIVATION` smallint(6) NOT NULL,
  `CODUTE` varchar(20) COLLATE latin1_general_cs NOT NULL,
  `DATAOPER` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `TIMEOPER` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `FLAG_DIS` smallint(6) NOT NULL,
  PRIMARY KEY (`ROW_ID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `MEN_FREQUENTI` (
  `ROWID` int(11) NOT NULL AUTO_INCREMENT,
  `FR_UTECOD` double NOT NULL COMMENT 'Codice Utente',
  `FR_QUANTE` int(11) NOT NULL COMMENT 'Numero Utilizzi',
  `FR_MENU` varchar(50) COLLATE latin1_general_cs NOT NULL COMMENT 'Codice Menu',
  `FR_PROG` varchar(50) COLLATE latin1_general_cs NOT NULL COMMENT 'Codice Programma',
  PRIMARY KEY (`ROWID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `MEN_PERMESSI` (
  `ROWID` int(11) NOT NULL AUTO_INCREMENT,
  `PER_GRU` varchar(10) COLLATE latin1_general_cs NOT NULL COMMENT 'Codice Gruppo',
  `PER_MEN` varchar(26) COLLATE latin1_general_cs NOT NULL COMMENT 'Codice menù',
  `PER_VME` varchar(26) COLLATE latin1_general_cs NOT NULL COMMENT 'Codice Vode Menù',
  `PER_FLAGVIS` int(11) NOT NULL COMMENT 'Flag Consenti / Nega Visualizzazione',
  `PER_FLAGACC` int(11) NOT NULL COMMENT 'Flag Consenti / Nega Accesso',
  `PER_FLAGEDT` int(11) NOT NULL COMMENT 'Flag Consenti / Nega  Modifica',
  `PER_FLAGINS` int(11) NOT NULL COMMENT 'Flag Consenti / Nega Inserimento',
  `PER_FLAGDEL` int(11) NOT NULL COMMENT 'Flag Consenti / Nega Cancellazione',
  PRIMARY KEY (`ROWID`),
  UNIQUE KEY `PER_KEY` (`PER_GRU`,`PER_MEN`,`PER_VME`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `MEN_PREFERITI` (
  `ROWID` int(11) NOT NULL AUTO_INCREMENT,
  `PR_UTECOD` double NOT NULL COMMENT 'Codice Utente',
  `PR_MENU` varchar(50) COLLATE latin1_general_cs NOT NULL COMMENT 'Codice Menu',
  `PR_PROG` varchar(50) COLLATE latin1_general_cs NOT NULL COMMENT 'Codice Programma',
  PRIMARY KEY (`ROWID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `MEN_RECENTI` (
  `ROWID` int(11) NOT NULL AUTO_INCREMENT,
  `UTECOD` double NOT NULL,
  `TEMPO` int(11) NOT NULL,
  `MENU` varchar(50) COLLATE latin1_general_cs NOT NULL,
  `PROG` varchar(50) COLLATE latin1_general_cs NOT NULL,
  `CONTEXT` varchar(32) COLLATE latin1_general_cs NOT NULL COMMENT 'CONTESTO APPLICATIVO',
  PRIMARY KEY (`ROWID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `PATCH_DEFD` (
  `ROW_ID` int(11) NOT NULL AUTO_INCREMENT,
  `PATCH_DEFT_ID` int(11) NOT NULL,
  `FILENAME` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `HASH_FILE_NEW` varchar(64) COLLATE latin1_general_cs NOT NULL,
  `CODUTE` varchar(20) COLLATE latin1_general_cs NOT NULL,
  `DATAOPER` date NOT NULL,
  `TIMEOPER` char(8) COLLATE latin1_general_cs NOT NULL,
  `CODUTEINS` varchar(20) COLLATE latin1_general_cs NOT NULL,
  `DATAINSER` date NOT NULL,
  `TIMEINSER` char(8) COLLATE latin1_general_cs NOT NULL,
  PRIMARY KEY (`ROW_ID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `PATCH_DEFT` (
  `ROW_ID` int(11) NOT NULL AUTO_INCREMENT,
  `PATCH_NAME` varchar(50) COLLATE latin1_general_cs NOT NULL,
  `RELEASE_RIF` varchar(10) COLLATE latin1_general_cs NOT NULL,
  `BUILD_TAG_MIN` varchar(50) COLLATE latin1_general_cs NOT NULL,
  `BUILD_TAG_MAX` varchar(50) COLLATE latin1_general_cs NOT NULL,
  `AUTHOR` varchar(50) COLLATE latin1_general_cs NOT NULL,
  `PATCH_DES` varchar(100) COLLATE latin1_general_cs NOT NULL,
  `PATCH_UPLOAD_DATE` date NOT NULL,
  `PATCH_UPLOAD_TIME` char(8) COLLATE latin1_general_cs NOT NULL,
  `PATCH_CONTEXT` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `PATCH_NOTES` text COLLATE latin1_general_cs NOT NULL,
  `CODUTE` varchar(20) COLLATE latin1_general_cs NOT NULL,
  `DATAOPER` date NOT NULL,
  `TIMEOPER` char(8) COLLATE latin1_general_cs NOT NULL,
  `CODUTEINS` varchar(20) COLLATE latin1_general_cs NOT NULL,
  `DATAINSER` date NOT NULL,
  `TIMEINSER` char(8) COLLATE latin1_general_cs NOT NULL,
  PRIMARY KEY (`ROW_ID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `PPA_POSIZIONI` (
  `ROWID` int(11) NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`ROWID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `PPA_RUOLI` (
  `ROWID` int(11) NOT NULL AUTO_INCREMENT,
  `ID_RUOLO` int(11) NOT NULL,
  `ESITO` varchar(200) COLLATE latin1_general_cs NOT NULL,
  `DESCRIZIONE` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `ANNOIMPOSTA` int(11) NOT NULL,
  `DATAINIZIOPERIODO` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `DATAFINEPERIODO` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `NOTE` text COLLATE latin1_general_cs NOT NULL,
  `TIPODOCUMENTO` varchar(200) COLLATE latin1_general_cs NOT NULL,
  `STATO` varchar(200) COLLATE latin1_general_cs NOT NULL,
  `POSIZIONIAPPROVATE` int(11) NOT NULL,
  `IMPORTOPOSIZIONIAPPROVATE` double NOT NULL,
  `POSIZIONIANNULLATE` int(11) NOT NULL,
  `IMPORTOPOSIZIONIANNULLATE` double NOT NULL,
  `POSIZIONITOTALI` int(11) NOT NULL,
  `IMPORTOPOSIZIONITOTALI` double NOT NULL,
  PRIMARY KEY (`ROWID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `PRG_DESTINAZIONI_URBANISTICHE` (
  `ROW_ID` int(11) NOT NULL AUTO_INCREMENT,
  `ID_DESTINAZIONE` smallint(6) NOT NULL,
  `DESC_DESTINAZIONE` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `INDICE_DENSITA_FONDIARIA` double NOT NULL,
  `INDICE_COPERTURA` double NOT NULL,
  `ALTEZZA_MASSIMA_EDIFICIO` double NOT NULL,
  `NUMERO_MASSIMO_PIANI` smallint(6) NOT NULL,
  `SUPERFICIE_LOTTO_MINIMO` double NOT NULL,
  `COSTRUZIONE_A_CONFINE` varchar(2) COLLATE latin1_general_cs NOT NULL,
  `PERCENTUALE_A_VERDE` double NOT NULL,
  `PERCENTUALE_SUPERFICIE_DRENANTE` double NOT NULL,
  `DISTANZA_STRADALE_MINIMA` double NOT NULL,
  `DISTANZA_MINIMA_CONFINI_NORD` double NOT NULL,
  `DISTANZA_MINIMA_CONFINI_EST` double NOT NULL,
  `DISTANZA_MINIMA_CONFINI_SUD` double NOT NULL,
  `DISTANZA_MINIMA_CONFINI_OVEST` double NOT NULL,
  `DISTANZA_MINIMA_EDIFICI_NORD` double NOT NULL,
  `DISTANZA_MINIMA_EDIFICI_EST` double NOT NULL,
  `DISTANZA_MINIMA_EDIFICI_SUD` double NOT NULL,
  `DISTANZA_MINIMA_EDIFICI_OVEST` double NOT NULL,
  `NUMERO_POSTI_AUTO` smallint(6) NOT NULL,
  `SUPERFICIE_PARCHEGGIO` double NOT NULL,
  `NUMERO_POSTI_AUTO_USO_PUBBLICO` smallint(6) NOT NULL,
  `NUMERO_POSTI_AUTO_USO_INDUSTRIALE` smallint(6) NOT NULL,
  `PERC_PUBBLICO_AUTO_USO_PUBBLICO` double NOT NULL,
  `PERC_PRIVATO_AUTO_USO_PUBBLICO` double NOT NULL,
  `PERC_PUBBLICO_AUTO_USO_INDUSTRIALE` double NOT NULL,
  `PERC_PRIVATO_AUTO_USO_INDUSTRIALE` double NOT NULL,
  `STATO` varchar(1) COLLATE latin1_general_cs NOT NULL,
  `INDICE_UTILIZZAZIONE_SUPERFICIE` double NOT NULL,
  `INDICE_VOLUMETRIA_ACCESSORIA` double NOT NULL,
  `ARTICOLO_NTA` varchar(20) COLLATE latin1_general_cs NOT NULL,
  `SUPERFICIE_LOTTO_MASSIMO` double NOT NULL,
  `PERC_RESIDENZIALE_MINIMO` double NOT NULL,
  `PERC_RESIDENZIALE_MASSIMO` double NOT NULL,
  `PERC_ACCESSORI_RESIDENZIALE` double NOT NULL,
  `PERC_NON_RESIDENZIALE` double NOT NULL,
  `PERC_PARCHEGGI_PRIVATI` double NOT NULL,
  `PERC_PARCHEGGI_PRIVATI_ALTRI_USI` double NOT NULL,
  `PERC_PARCHEGGI_PUBBLICI` double NOT NULL,
  `INDICE_PIANTUMAZIONE` double NOT NULL,
  `DISTANZA_MINIMA_CONFINI` double NOT NULL,
  `DISTANZA_MINIMA_EDIFICI` double NOT NULL,
  `DISTANZA_FERROVIARIA_MINIMA` double NOT NULL,
  `DISTANZA_ALTA_TENSIONE_MINIMA` double NOT NULL,
  PRIMARY KEY (`ROW_ID`),
  KEY `I_ID_DESTINAZIONE` (`ID_DESTINAZIONE`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `PRG_DESTINAZIONI_USO` (
  `ROW_ID` int(11) NOT NULL AUTO_INCREMENT,
  `ID_DESTINAZIONE` int(11) DEFAULT NULL,
  `DESC_DESTINAZIONE` varchar(33) COLLATE latin1_general_cs DEFAULT NULL,
  `STATO` varchar(1) COLLATE latin1_general_cs DEFAULT NULL,
  PRIMARY KEY (`ROW_ID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `REP_GEST` (
  `ROWID` int(11) NOT NULL AUTO_INCREMENT COMMENT 'CHIAVE',
  `CODICE` varchar(50) COLLATE latin1_general_cs NOT NULL COMMENT 'NOME DEL REPORT',
  `DESCRIZIONE` varchar(200) COLLATE latin1_general_cs NOT NULL COMMENT 'DESCRIZIONE DEL REPORT',
  `SEQUENZA` float NOT NULL COMMENT 'SEQUENZA',
  `CATEGORIA` varchar(100) COLLATE latin1_general_cs NOT NULL COMMENT 'CATEGORIA',
  `FILLER` varchar(200) COLLATE latin1_general_cs NOT NULL COMMENT 'F1',
  `FILLER2` varchar(200) COLLATE latin1_general_cs NOT NULL COMMENT 'F2',
  PRIMARY KEY (`ROWID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs COMMENT='ELENCO DEI REPORT DI STAMPA';
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `SMS_ARCHIVIO` (
  `ROWID` int(11) NOT NULL AUTO_INCREMENT,
  `IDSMS` text COLLATE latin1_general_cs NOT NULL COMMENT 'IDENTIFICATIVO formato da DITTA-TIPO-UNIQID',
  `ACCOUNT` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `TOADDR` varchar(50) COLLATE latin1_general_cs NOT NULL,
  `MSGDATE` varchar(20) COLLATE latin1_general_cs NOT NULL COMMENT 'YmdHis',
  `BODYTEXT` text COLLATE latin1_general_cs,
  PRIMARY KEY (`ROWID`),
  KEY `I_IDMAIL` (`IDSMS`(255))
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs COMMENT='ARCHIVIO EMAIL';
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `XOL_ACCOUNT` (
  `ROWID` int(11) NOT NULL AUTO_INCREMENT,
  `DESCRIZIONE` text COLLATE latin1_general_cs NOT NULL,
  `NUMEROAUTORIZZAZIONE` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `DATAAUTORIZZAZIONE` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `UTENTE` text COLLATE latin1_general_cs NOT NULL,
  `PASSWORD` text COLLATE latin1_general_cs NOT NULL,
  `WSENDPOINT` text COLLATE latin1_general_cs NOT NULL,
  `WSWSDL` text COLLATE latin1_general_cs NOT NULL,
  `NAMESPACES` text COLLATE latin1_general_cs NOT NULL,
  PRIMARY KEY (`ROWID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `XOL_ARCHIVIO` (
  `ROWID` int(11) NOT NULL AUTO_INCREMENT,
  `IDACCOUNT` int(11) NOT NULL,
  `IDRICHIESTA` varchar(50) COLLATE latin1_general_cs NOT NULL,
  `GUIDUTENTE` varchar(50) COLLATE latin1_general_cs NOT NULL,
  `DATAFILE` text COLLATE latin1_general_cs NOT NULL,
  `DATADCS` text COLLATE latin1_general_cs NOT NULL,
  `FILENAME` text COLLATE latin1_general_cs NOT NULL,
  `DCSNAME` text COLLATE latin1_general_cs NOT NULL,
  `TIPOXOL` varchar(10) COLLATE latin1_general_cs NOT NULL,
  `FLAGAR` tinyint(4) NOT NULL,
  `PREZZOXOL` double NOT NULL COMMENT 'prezzo xol',
  `UTEINS` varchar(20) COLLATE latin1_general_cs NOT NULL,
  `DATAINS` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `ORAINS` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `STATOTRANSAZIONE` varchar(10) COLLATE latin1_general_cs NOT NULL,
  `DESCRIZIONESTATOTRANSAZIONE` text COLLATE latin1_general_cs NOT NULL,
  `STATOLAVORAZIONE` varchar(50) COLLATE latin1_general_cs NOT NULL,
  `DATACONFERMA` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `ORACONFERMA` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `DATAANNULLAMENTO` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `ORAANNULLAMENTO` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `UTEANN` varchar(20) COLLATE latin1_general_cs NOT NULL,
  PRIMARY KEY (`ROWID`),
  KEY `IDRICHIESTA` (`IDRICHIESTA`,`GUIDUTENTE`),
  KEY `IDACCOUNT` (`IDACCOUNT`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `XOL_NOMINATIVI` (
  `ROWID` int(11) NOT NULL AUTO_INCREMENT,
  `ROWID_ARCHIVIO` int(11) NOT NULL,
  `TIPOSOGGETTO` varchar(1) COLLATE latin1_general_cs NOT NULL COMMENT 'D, M, R',
  `ZONA` varchar(44) COLLATE latin1_general_cs NOT NULL,
  `TIPOINDIRIZZO` varchar(101) COLLATE latin1_general_cs NOT NULL,
  `STATO` varchar(44) COLLATE latin1_general_cs NOT NULL,
  `TELEFONO` varchar(18) COLLATE latin1_general_cs NOT NULL,
  `FORZADESTINAZIONE` tinyint(4) NOT NULL,
  `CASELLAPOSTALE` varchar(44) COLLATE latin1_general_cs NOT NULL,
  `UFFICIOPOSTALE` varchar(50) COLLATE latin1_general_cs NOT NULL,
  `PROVINCIA` varchar(4) COLLATE latin1_general_cs NOT NULL,
  `COMPLEMENTONOMINATIVO` varchar(50) COLLATE latin1_general_cs NOT NULL,
  `RAGIONESOCIALE` varchar(44) COLLATE latin1_general_cs NOT NULL,
  `COGNOME` varchar(44) COLLATE latin1_general_cs NOT NULL,
  `NOME` varchar(44) COLLATE latin1_general_cs NOT NULL,
  `FRAZIONE` varchar(44) COLLATE latin1_general_cs NOT NULL,
  `CITTA` varchar(44) COLLATE latin1_general_cs NOT NULL,
  `COMPLEMENTOINDIRIZZO` varchar(44) COLLATE latin1_general_cs NOT NULL,
  `CAP` varchar(10) COLLATE latin1_general_cs NOT NULL,
  `DUG` varchar(44) COLLATE latin1_general_cs NOT NULL,
  `TOPONIMO` varchar(44) COLLATE latin1_general_cs NOT NULL,
  `NUMEROCIVICO` varchar(5) COLLATE latin1_general_cs NOT NULL,
  `ESPONENTE` varchar(44) COLLATE latin1_general_cs NOT NULL,
  `IDRICEVUTA` varchar(50) COLLATE latin1_general_cs NOT NULL,
  `NUMERORACCOMANDATA` varchar(20) COLLATE latin1_general_cs NOT NULL,
  `STATOVERIFICA` varchar(10) COLLATE latin1_general_cs NOT NULL,
  `DESCRIZIONEVERIFICA` text COLLATE latin1_general_cs NOT NULL,
  `ALTNOMINATIVI` text COLLATE latin1_general_cs NOT NULL,
  PRIMARY KEY (`ROWID`),
  KEY `ROWID_ARCHIVIO` (`ROWID_ARCHIVIO`),
  KEY `NUMERORACCOMANDATA` (`NUMERORACCOMANDATA`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
