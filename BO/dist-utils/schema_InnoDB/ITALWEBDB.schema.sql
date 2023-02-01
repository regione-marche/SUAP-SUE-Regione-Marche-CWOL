/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ACCESSI` (
  `ROWID` int(11) NOT NULL AUTO_INCREMENT,
  `USERSESSION` varchar(200) COLLATE latin1_general_cs DEFAULT NULL,
  `HASHSESSION` varchar(200) COLLATE latin1_general_cs NOT NULL,
  `PWDSESSION` text COLLATE latin1_general_cs NOT NULL,
  `SECSESSION` text COLLATE latin1_general_cs NOT NULL,
  `DOMAINSESSION` varchar(20) COLLATE latin1_general_cs NOT NULL DEFAULT '',
  PRIMARY KEY (`ROWID`),
  UNIQUE KEY `I_USERSESSION` (`USERSESSION`),
  KEY `I_HASHSESSION` (`HASHSESSION`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `DOMAINS` (
  `ROWID` int(11) NOT NULL AUTO_INCREMENT,
  `CODICE` varchar(20) COLLATE latin1_general_cs NOT NULL,
  `DESCRIZIONE` varchar(60) COLLATE latin1_general_cs NOT NULL,
  `SEQUENZA` int(11) NOT NULL,
  `STATO` varchar(4) COLLATE latin1_general_cs NOT NULL,
  `MESSAGGIO` text COLLATE latin1_general_cs NOT NULL,
  `HASH` text COLLATE latin1_general_cs NOT NULL,
  `ACTIVATION` text COLLATE latin1_general_cs NOT NULL,
  `APPS` text COLLATE latin1_general_cs,
  `RISERVATO` smallint(6) NOT NULL COMMENT 'RISERVATO NON ACCESSIBILE DA SELECT LOGIN',
  PRIMARY KEY (`ROWID`),
  UNIQUE KEY `CODICE` (`CODICE`),
  UNIQUE KEY `DESCRIZIONE` (`DESCRIZIONE`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `LOCKTAB` (
  `ROWID` int(11) NOT NULL AUTO_INCREMENT,
  `LOCKRECID` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `LOCKTOKEN` varchar(40) COLLATE latin1_general_cs NOT NULL,
  `LOCKTIME` int(11) NOT NULL,
  `LOCKEXP` int(11) NOT NULL,
  `LOCKMODE` varchar(40) COLLATE latin1_general_cs NOT NULL,
  PRIMARY KEY (`ROWID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `PARAMETRIENTE` (
  `ROWID` int(11) NOT NULL AUTO_INCREMENT,
  `CODICE` varchar(16) COLLATE latin1_general_cs NOT NULL,
  `DENOMINAZIONE` varchar(80) COLLATE latin1_general_cs DEFAULT NULL,
  `INDIRIZZO` varchar(80) COLLATE latin1_general_cs DEFAULT NULL,
  `CITTA` varchar(80) COLLATE latin1_general_cs DEFAULT NULL,
  `CAP` varchar(5) COLLATE latin1_general_cs DEFAULT NULL,
  `PROVINCIA` varchar(2) COLLATE latin1_general_cs DEFAULT NULL,
  `CODFIS` varchar(16) COLLATE latin1_general_cs NOT NULL,
  `PIVA` varchar(16) COLLATE latin1_general_cs NOT NULL,
  `TELEFONO` varchar(60) COLLATE latin1_general_cs NOT NULL,
  `FAX` varchar(60) COLLATE latin1_general_cs NOT NULL,
  `EMAIL` varchar(100) COLLATE latin1_general_cs NOT NULL,
  `WWW` varchar(100) COLLATE latin1_general_cs NOT NULL,
  `ATTIVITA` varchar(20) COLLATE latin1_general_cs NOT NULL,
  `REGIONE` varchar(30) COLLATE latin1_general_cs NOT NULL,
  `CODICEREGIONE` varchar(2) COLLATE latin1_general_cs NOT NULL,
  `CODICEBELFIORE` varchar(4) COLLATE latin1_general_cs NOT NULL,
  `TIPOPROTOCOLLO` varchar(50) COLLATE latin1_general_cs NOT NULL COMMENT 'TIPO PROTOCOLLO',
  `TIPOZTL` varchar(50) COLLATE latin1_general_cs NOT NULL COMMENT 'indica se utilizza anagrafica italsoft o da ws',
  `ISTAT` varchar(11) COLLATE latin1_general_cs NOT NULL COMMENT 'CODICE ISTAT O IDENTIFICATIVO ENTE',
  PRIMARY KEY (`ROWID`),
  UNIQUE KEY `CODICE` (`CODICE`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `PATCH_APPLD` (
  `ROW_ID` int(11) NOT NULL AUTO_INCREMENT,
  `PATCH_APPLT_ID` int(11) NOT NULL,
  `FILENAME` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `HASH_FILE_NEW` varchar(64) COLLATE latin1_general_cs NOT NULL,
  `HASH_FILE_ORIG` varchar(64) COLLATE latin1_general_cs NOT NULL,
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
CREATE TABLE `PATCH_APPLT` (
  `ROW_ID` int(11) NOT NULL AUTO_INCREMENT,
  `PATCH_DEFT_ID` int(11) NOT NULL,
  `PATCH_NAME` varchar(50) COLLATE latin1_general_cs NOT NULL,
  `APPL_MODE` int(11) NOT NULL,
  `CODUTE` varchar(20) COLLATE latin1_general_cs NOT NULL,
  `DATAOPER` date NOT NULL,
  `TIMEOPER` char(8) COLLATE latin1_general_cs NOT NULL,
  `CODUTEINS` varchar(20) COLLATE latin1_general_cs NOT NULL,
  `DATAINSER` date NOT NULL,
  `TIMEINSER` char(8) COLLATE latin1_general_cs NOT NULL,
  PRIMARY KEY (`ROW_ID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
