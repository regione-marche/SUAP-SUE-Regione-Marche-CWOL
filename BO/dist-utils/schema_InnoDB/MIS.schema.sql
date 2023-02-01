/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ARCHIVIO_ENTI` (
  `ROW_ID` int(11) NOT NULL AUTO_INCREMENT,
  `PROGRESSIVO` int(11) NOT NULL,
  `DESCRIZIONE` text COLLATE latin1_general_cs NOT NULL,
  `COD_ISTAT` varchar(6) COLLATE latin1_general_cs NOT NULL,
  `COD_CATASTALE` varchar(4) COLLATE latin1_general_cs NOT NULL,
  `COD_AMM` varchar(20) COLLATE latin1_general_cs NOT NULL,
  `COD_AOO` varchar(30) COLLATE latin1_general_cs NOT NULL,
  `PEC` varchar(100) COLLATE latin1_general_cs NOT NULL,
  `CODICEFISCALE` varchar(16) COLLATE latin1_general_cs NOT NULL,
  `PIVA` varchar(16) COLLATE latin1_general_cs NOT NULL,
  `ATTIVAZIONE_NOTIFICHE_USCITA` tinyint(4) NOT NULL,
  `ATTIVAZIONE_POLLING_DOMUS` tinyint(4) NOT NULL,
  `DATI_NOTIFICA` text COLLATE latin1_general_cs NOT NULL COMMENT 'serializzato json',
  `TIPO_ENDPOINT` varchar(20) COLLATE latin1_general_cs NOT NULL,
  `NOTE` text COLLATE latin1_general_cs NOT NULL,
  `POLLING_LIMITE` tinyint(4) NOT NULL,
  `POLLING_BLOCCO_MARCATURA` tinyint(4) NOT NULL,
  PRIMARY KEY (`ROW_ID`),
  UNIQUE KEY `U_PROGRESSIVO` (`PROGRESSIVO`),
  UNIQUE KEY `U_ENTE` (`COD_ISTAT`,`COD_CATASTALE`,`COD_AMM`,`COD_AOO`,`CODICEFISCALE`,`PIVA`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `FLUSSO_ALLEGATI` (
  `ROW_ID` int(11) NOT NULL AUTO_INCREMENT,
  `IDFLUSSO` int(11) NOT NULL,
  `PROGDETT` int(11) NOT NULL,
  `IDFILE` varchar(80) COLLATE latin1_general_cs NOT NULL,
  `NOMEFILE` varchar(200) COLLATE latin1_general_cs NOT NULL,
  `PATHFILE` varchar(200) COLLATE latin1_general_cs NOT NULL,
  `SHA2FILE` varchar(64) COLLATE latin1_general_cs NOT NULL,
  `METADATI` text COLLATE latin1_general_cs NOT NULL,
  PRIMARY KEY (`ROW_ID`),
  KEY `I_DETTAGLIO` (`IDFLUSSO`,`PROGDETT`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `FLUSSO_DETTAGLI` (
  `ROW_ID` int(11) NOT NULL AUTO_INCREMENT,
  `IDFLUSSO` int(11) NOT NULL,
  `PROGRESSIVO` int(11) NOT NULL,
  `APPCONTEXT` varchar(80) COLLATE latin1_general_cs NOT NULL,
  `APPKEY` varchar(80) COLLATE latin1_general_cs NOT NULL,
  `TIPOEVENTO` varchar(20) COLLATE latin1_general_cs NOT NULL,
  `METADATI` text COLLATE latin1_general_cs NOT NULL,
  `STATODETT` smallint(6) NOT NULL,
  `LASTMESSAGE` text COLLATE latin1_general_cs NOT NULL,
  `REMOTEKEY` varchar(10) COLLATE latin1_general_cs NOT NULL,
  PRIMARY KEY (`ROW_ID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `FLUSSO_TESTATE` (
  `ROW_ID` int(11) NOT NULL AUTO_INCREMENT,
  `IDFLUSSO` int(11) NOT NULL,
  `DATAREGFLUSSO` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `ORAREGFLUSSO` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `VERSO` varchar(2) COLLATE latin1_general_cs NOT NULL COMMENT 'I=ingresso, U=uscita',
  `MITTDEST` int(11) NOT NULL,
  `STATO` varchar(20) COLLATE latin1_general_cs NOT NULL,
  `METADATI` text COLLATE latin1_general_cs NOT NULL,
  `LASTMESSAGE` text COLLATE latin1_general_cs NOT NULL,
  `ORATRASFLUSSO` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `DATATRASFLUSSO` varchar(8) COLLATE latin1_general_cs NOT NULL,
  PRIMARY KEY (`ROW_ID`),
  UNIQUE KEY `I_TESTATA_FLUSSO_CODICE` (`IDFLUSSO`),
  KEY `I_TESTATA_FLUSSO_MITTDEST` (`MITTDEST`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
