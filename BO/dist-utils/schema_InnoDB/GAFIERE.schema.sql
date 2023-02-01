/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ANADITTA` (
  `ROWID` int(11) NOT NULL AUTO_INCREMENT,
  `CODICE` double DEFAULT NULL,
  `TITOLO` varchar(20) COLLATE latin1_general_cs NOT NULL,
  `DENOMINAZIONE` varchar(100) COLLATE latin1_general_cs NOT NULL,
  `SESSO` varchar(1) COLLATE latin1_general_cs NOT NULL,
  `DATANASCITA` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `COMUNENASCITA` varchar(35) COLLATE latin1_general_cs NOT NULL,
  `CODICECOMUNE` varchar(6) COLLATE latin1_general_cs DEFAULT NULL,
  `COMUNE` varchar(35) COLLATE latin1_general_cs NOT NULL,
  `CAP` varchar(5) COLLATE latin1_general_cs NOT NULL,
  `PROVINCIA` varchar(2) COLLATE latin1_general_cs NOT NULL,
  `INDIRIZZO` varchar(35) COLLATE latin1_general_cs NOT NULL,
  `NUMEROCIVICO` varchar(5) COLLATE latin1_general_cs NOT NULL,
  `CODICENAZIONE` varchar(6) COLLATE latin1_general_cs DEFAULT NULL,
  `NAZIONALITA` varchar(50) COLLATE latin1_general_cs NOT NULL,
  `TELEFONO` varchar(30) COLLATE latin1_general_cs NOT NULL,
  `FAX` varchar(30) COLLATE latin1_general_cs NOT NULL,
  `CODICEFISCALE` varchar(16) COLLATE latin1_general_cs NOT NULL,
  `PIVA` varchar(11) COLLATE latin1_general_cs NOT NULL,
  `NOTE` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `COMUNICAZIONI` text COLLATE latin1_general_cs,
  `FOTO` longblob,
  `DATIVARI` text COLLATE latin1_general_cs,
  `TIPOATTIVITA` varchar(5) COLLATE latin1_general_cs NOT NULL,
  `TIPOSETTORE` varchar(5) COLLATE latin1_general_cs NOT NULL,
  `ANAGRAFICAATTIVA` tinyint(4) DEFAULT NULL,
  `ENTE` smallint(6) DEFAULT NULL,
  `SPUNTISTA` varchar(1) COLLATE latin1_general_cs NOT NULL,
  `RAPINDIRIZZO` varchar(35) COLLATE latin1_general_cs NOT NULL,
  `RAPCOMUNE` varchar(35) COLLATE latin1_general_cs NOT NULL,
  `RAPPROV` varchar(2) COLLATE latin1_general_cs NOT NULL,
  `RAPTELEFONO` varchar(30) COLLATE latin1_general_cs NOT NULL,
  `RAPCAP` varchar(5) COLLATE latin1_general_cs NOT NULL,
  `RAPNUM` varchar(5) COLLATE latin1_general_cs NOT NULL,
  `RAPLEGALE` varchar(50) COLLATE latin1_general_cs NOT NULL,
  `CODFISRAP` varchar(16) COLLATE latin1_general_cs NOT NULL,
  `PIVARAP` varchar(11) COLLATE latin1_general_cs NOT NULL,
  `BADGE` varchar(20) COLLATE latin1_general_cs NOT NULL,
  `DATACONSEGNABADGE` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `DATARESBADGE` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `CAUZIONEBADGE` double DEFAULT NULL,
  `CELLULARE1` varchar(20) COLLATE latin1_general_cs NOT NULL,
  `CELLULARE2` varchar(20) COLLATE latin1_general_cs NOT NULL,
  `EMAIL` varchar(50) COLLATE latin1_general_cs NOT NULL,
  `WWW` varchar(50) COLLATE latin1_general_cs NOT NULL,
  `ARTICOLI` varchar(50) COLLATE latin1_general_cs NOT NULL,
  `RDATANASCITA` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `RCOMUNENASCITA` varchar(35) COLLATE latin1_general_cs NOT NULL,
  `MATRICOLAINPS` varchar(20) COLLATE latin1_general_cs NOT NULL,
  `CODICEINAIL` varchar(20) COLLATE latin1_general_cs NOT NULL,
  `SEDEINPS` varchar(50) COLLATE latin1_general_cs NOT NULL,
  `SEDEINAIL` varchar(50) COLLATE latin1_general_cs NOT NULL,
  `DATARESDGETB` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `DATADURC` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `UTEADD` varchar(50) COLLATE latin1_general_cs NOT NULL COMMENT 'utente inserimento',
  `DATEADD` varchar(8) COLLATE latin1_general_cs NOT NULL COMMENT 'data inserimento',
  `UTEEDIT` varchar(50) COLLATE latin1_general_cs NOT NULL COMMENT 'utente modifica',
  `DATEEDIT` varchar(8) COLLATE latin1_general_cs NOT NULL COMMENT 'data modifica',
  `CODICEDESTINATARIO` varchar(20) COLLATE latin1_general_cs NOT NULL,
  PRIMARY KEY (`ROWID`),
  UNIQUE KEY `UNIQ_ANADITTA` (`CODICE`),
  KEY `I_DENOMINAZIONE` (`DENOMINAZIONE`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ANAEVENTI` (
  `ROWID` int(11) NOT NULL AUTO_INCREMENT,
  `CODICE` varchar(5) COLLATE latin1_general_cs NOT NULL,
  `DESCRIZIONE` varchar(200) COLLATE latin1_general_cs NOT NULL,
  PRIMARY KEY (`ROWID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ANAFIERE` (
  `ROWID` int(11) NOT NULL AUTO_INCREMENT,
  `TIPO` varchar(5) COLLATE latin1_general_cs NOT NULL,
  `FIERA` varchar(50) COLLATE latin1_general_cs NOT NULL,
  `ENTE` smallint(6) DEFAULT NULL,
  `PERIODICITA` varchar(15) COLLATE latin1_general_cs NOT NULL,
  `GIORNO` smallint(6) DEFAULT NULL,
  `MESE` smallint(6) DEFAULT NULL,
  `NUMEROPOSTI` smallint(6) DEFAULT NULL,
  `NUMEROPOSTILIBERI` smallint(6) DEFAULT NULL,
  `NUMEROGIORNI` smallint(6) DEFAULT NULL,
  `TIPOCONCESSIONE` varchar(5) COLLATE latin1_general_cs NOT NULL,
  `PROGRESSIVOCONCESSIONE` double DEFAULT NULL,
  `NOTE` text COLLATE latin1_general_cs,
  `LOGOFIERA` longblob,
  `TIPOORDINAMENTO` varchar(5) COLLATE latin1_general_cs NOT NULL,
  `NUMEROPOSTIPRODUTTORI` double DEFAULT NULL,
  `ORARIO` varchar(40) COLLATE latin1_general_cs NOT NULL,
  `COMUNE` varchar(50) COLLATE latin1_general_cs NOT NULL,
  `INDIRIZZO` varchar(50) COLLATE latin1_general_cs NOT NULL,
  `CAP` varchar(50) COLLATE latin1_general_cs NOT NULL,
  `FIRMATARIO` varchar(50) COLLATE latin1_general_cs NOT NULL,
  `LUOGOASSEGNAZIONE` varchar(50) COLLATE latin1_general_cs NOT NULL,
  `UBICAZIONE` varchar(200) COLLATE latin1_general_cs NOT NULL,
  `SVOLGIMENTO` varchar(50) COLLATE latin1_general_cs NOT NULL,
  `ORARIOSGOMBRO` varchar(10) COLLATE latin1_general_cs NOT NULL,
  `ENTEFIERE` varchar(20) COLLATE latin1_general_cs NOT NULL,
  `CODICEFIERAENTE` varchar(5) COLLATE latin1_general_cs NOT NULL,
  `SERIECONCESSIONE` varchar(10) COLLATE latin1_general_cs NOT NULL,
  PRIMARY KEY (`ROWID`),
  UNIQUE KEY `UNIQ_ANAFIERE` (`TIPO`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ANAMERC` (
  `ROWID` int(11) NOT NULL AUTO_INCREMENT,
  `CODICE` varchar(5) COLLATE latin1_general_cs NOT NULL,
  `MERCATO` varchar(50) COLLATE latin1_general_cs NOT NULL,
  `NUMEROPOSTI` smallint(6) DEFAULT NULL,
  `GIORNO` varchar(15) COLLATE latin1_general_cs NOT NULL,
  `TIPOCONCESSIONE` varchar(5) COLLATE latin1_general_cs NOT NULL,
  `NOTE` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `ALTRIDATI` longblob,
  `ENTE` smallint(6) DEFAULT NULL,
  `MASSIMEASSENZE` smallint(6) DEFAULT NULL,
  `SEGNALAASSENZE` smallint(6) DEFAULT NULL,
  `ANNO` smallint(6) DEFAULT NULL,
  `DALLADATA` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `ALLADATA` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `TIPOORDINAMENTO` varchar(5) COLLATE latin1_general_cs NOT NULL,
  `ORARIO` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `ORARIOSPUNTISTI` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `UBICAZIONE` varchar(200) COLLATE latin1_general_cs NOT NULL,
  `DATACOMUNICAZIONE` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `ENTEFIERE` varchar(20) COLLATE latin1_general_cs NOT NULL,
  `CODICEFIERAENTE` varchar(5) COLLATE latin1_general_cs NOT NULL,
  PRIMARY KEY (`ROWID`),
  UNIQUE KEY `UNIQ_ANAMERC` (`CODICE`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `BANDIM` (
  `ROWID` int(11) NOT NULL AUTO_INCREMENT,
  `FIERA` varchar(5) COLLATE latin1_general_cs NOT NULL,
  `DATA` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `ASSEGNAZIONE` smallint(6) DEFAULT NULL,
  `DATATERMINE` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `IMPORTO` double DEFAULT NULL,
  `NOTE` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `AGGIORNATE` varchar(2) COLLATE latin1_general_cs NOT NULL,
  `DATAAGGIORNAMENTO` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `NOMEUTENTE` varchar(20) COLLATE latin1_general_cs NOT NULL,
  `DATAINSERIMENTO` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `UTENTEMODIFICA` varchar(20) COLLATE latin1_general_cs NOT NULL,
  `DATAMODIFICA` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `ENTE` smallint(6) DEFAULT NULL,
  `PROTOCOLLO` varchar(10) COLLATE latin1_general_cs NOT NULL,
  `DATAPROTOCOLLO` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `PROTOCOLLO1` varchar(10) COLLATE latin1_general_cs NOT NULL,
  `DATAPROTOCOLLO1` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `PROTOCOLLO2` varchar(10) COLLATE latin1_general_cs NOT NULL,
  `DATAPROTOCOLLO2` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `DECENNALE` tinyint(4) NOT NULL,
  `PROTGRADUATORIA` varchar(30) COLLATE latin1_general_cs NOT NULL,
  `PROTGRADDATA` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `PROTCONVOCAZIONE` varchar(30) COLLATE latin1_general_cs NOT NULL,
  `PROTCONVDATA` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `PROTCONCESSIONE` varchar(30) COLLATE latin1_general_cs NOT NULL,
  `PROTCONCDATA` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `NATTO` varchar(30) COLLATE latin1_general_cs NOT NULL,
  `DATAATTO` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `TIPONS` varchar(20) COLLATE latin1_general_cs NOT NULL,
  `BLOCCAPUBB` smallint(6) NOT NULL COMMENT 'Flag Blocca Pubblicazione FO',
  `STATO` varchar(100) COLLATE latin1_general_cs NOT NULL,
  `UTENTERILEVAZIONE` varchar(20) COLLATE latin1_general_cs NOT NULL,
  `FL_RILEVAZIONE` tinyint(4) NOT NULL,
  `BANDO` tinyint(4) NOT NULL,
  `TIPOATTIVITA` varchar(5) COLLATE latin1_general_cs NOT NULL,
  PRIMARY KEY (`ROWID`),
  UNIQUE KEY `UNIQ_FIERE` (`FIERA`,`DATA`,`ASSEGNAZIONE`,`DECENNALE`,`TIPONS`,`TIPOATTIVITA`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `COMUNICAZIONILIC` (
  `ROWID` int(11) NOT NULL AUTO_INCREMENT,
  `CODICE` double NOT NULL,
  `TIPOAUTORIZZAZIONE` varchar(3) COLLATE latin1_general_cs NOT NULL,
  `NUMERO` varchar(20) COLLATE latin1_general_cs NOT NULL,
  `CODICEMERCATO` varchar(5) COLLATE latin1_general_cs NOT NULL,
  `CODICEFIERA` varchar(5) COLLATE latin1_general_cs NOT NULL,
  PRIMARY KEY (`ROWID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `CONVERSIONE` (
  `ROWID` int(11) NOT NULL AUTO_INCREMENT,
  `DESCRIZIONE` varchar(255) COLLATE latin1_general_cs NOT NULL,
  PRIMARY KEY (`ROWID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `COSAP` (
  `ROWID` int(11) NOT NULL AUTO_INCREMENT,
  `TIPO` varchar(5) COLLATE latin1_general_cs NOT NULL,
  `TIPOORDINE` varchar(10) COLLATE latin1_general_cs NOT NULL,
  `POSTO` double NOT NULL,
  `ANNO` varchar(4) COLLATE latin1_general_cs NOT NULL,
  `DATA` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `CODICEDITTA` double NOT NULL,
  `DENOMINAZIONE` varchar(100) COLLATE latin1_general_cs NOT NULL,
  `RATA` varchar(20) COLLATE latin1_general_cs NOT NULL,
  `VIE` varchar(50) COLLATE latin1_general_cs NOT NULL,
  `LUNGHEZZA` double NOT NULL,
  `LARGHEZZA` double NOT NULL,
  `MQ` double NOT NULL,
  `GIORNI` double NOT NULL,
  `ORE` double NOT NULL,
  `CODICETASSA` varchar(5) COLLATE latin1_general_cs NOT NULL,
  `IMPORTOTASSA` double NOT NULL,
  `DAPAGARE` double NOT NULL,
  `PAGATO` double NOT NULL,
  `ESTREMI` varchar(30) COLLATE latin1_general_cs NOT NULL,
  `NOTE` text COLLATE latin1_general_cs NOT NULL,
  `DATAPAGAMENTO` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `FONTECOSAP` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `STAMPE` text COLLATE latin1_general_cs NOT NULL,
  `PROTOCOLLO` varchar(10) COLLATE latin1_general_cs NOT NULL COMMENT 'numero protocollo',
  `GIORNATAINTERA` tinyint(4) NOT NULL,
  `ID_RUOLO` varchar(20) COLLATE latin1_general_cs NOT NULL,
  `FILE_ARCHIVIO` varchar(100) COLLATE latin1_general_cs NOT NULL,
  `COSAP_NEW` int(11) NOT NULL COMMENT 'ROWID della cosap collegata',
  `CODICEVIA` varchar(6) COLLATE latin1_general_cs NOT NULL,
  `LETTERA` varchar(5) COLLATE latin1_general_cs NOT NULL,
  `INTERESSI` double NOT NULL,
  `MODALITAPAGFE` varchar(4) COLLATE latin1_general_cs NOT NULL COMMENT 'Modalità di pagamento Fattura Elettronica',
  PRIMARY KEY (`ROWID`),
  KEY `I_DENOMINAZIONE` (`DENOMINAZIONE`),
  KEY `I_DITTA` (`CODICEDITTA`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `COSAPDETT` (
  `ROW_ID` int(11) NOT NULL AUTO_INCREMENT,
  `ID_COSAP` int(11) NOT NULL,
  `CODICETASSA` varchar(5) COLLATE latin1_general_cs NOT NULL,
  `IMPORTOTASSA` double NOT NULL,
  `DAPAGARE` double NOT NULL,
  `PAGATO` double NOT NULL,
  `ESTREMI` varchar(30) COLLATE latin1_general_cs NOT NULL,
  `NOTE` varchar(200) COLLATE latin1_general_cs NOT NULL,
  `DATAPAGAMENTO` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `PROTOCOLLO` varchar(10) COLLATE latin1_general_cs NOT NULL COMMENT 'numero protocollo',
  `INTERESSI` double NOT NULL,
  `IVA` double NOT NULL,
  PRIMARY KEY (`ROW_ID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `COSAPPOSIZIONI` (
  `ROWID` int(11) NOT NULL AUTO_INCREMENT,
  `ID_COSAP` int(11) NOT NULL,
  `ID_RUOLO` int(11) NOT NULL,
  `IUV` varchar(50) COLLATE latin1_general_cs NOT NULL,
  `IMPORTO` double NOT NULL,
  `DATASCADENZA` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `RATA` int(11) NOT NULL COMMENT '0=rata unica',
  `ANNULLATO` tinyint(4) NOT NULL,
  `PAGATO` double NOT NULL COMMENT 'importo pagato',
  `DATAPAGAMENTO` varchar(8) COLLATE latin1_general_cs NOT NULL,
  PRIMARY KEY (`ROWID`),
  UNIQUE KEY `U_IUV` (`IUV`),
  UNIQUE KEY `U_POSIZIONE` (`ID_COSAP`,`ID_RUOLO`,`RATA`,`IUV`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `COSAPS` (
  `ROWID` int(11) NOT NULL AUTO_INCREMENT,
  `TIPO` varchar(5) COLLATE latin1_general_cs NOT NULL,
  `ANNO` varchar(4) COLLATE latin1_general_cs NOT NULL,
  `RATA` varchar(20) COLLATE latin1_general_cs NOT NULL,
  `CODICEDITTA` double NOT NULL,
  `DATA` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `DENOMINAZIONE` varchar(100) COLLATE latin1_general_cs NOT NULL,
  `MQ` double NOT NULL,
  `GIORNI` double NOT NULL,
  `ORE` double NOT NULL,
  `CODICETASSA` varchar(5) COLLATE latin1_general_cs NOT NULL,
  `IMPORTOTASSA` double NOT NULL,
  `DAPAGARE` double NOT NULL,
  `PAGATO` double NOT NULL,
  `ESTREMI` varchar(30) COLLATE latin1_general_cs NOT NULL,
  `NOTE` varchar(200) COLLATE latin1_general_cs NOT NULL,
  `DATAPAGAMENTO` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `FONTECOSAPS` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `ID_RUOLO` varchar(20) COLLATE latin1_general_cs NOT NULL,
  `IUV` varchar(50) COLLATE latin1_general_cs NOT NULL,
  PRIMARY KEY (`ROWID`),
  UNIQUE KEY `UNIQ_COSAPS` (`TIPO`,`ANNO`,`RATA`,`CODICEDITTA`),
  KEY `I_DENOMINAZIONE` (`DENOMINAZIONE`),
  KEY `I_DITTA` (`CODICEDITTA`),
  KEY `I_GIORNI` (`ANNO`,`RATA`,`GIORNI`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `COSAPSTORICO` (
  `ROWID` int(11) NOT NULL AUTO_INCREMENT,
  `TIPO` varchar(5) COLLATE latin1_general_cs NOT NULL,
  `TIPOORDINE` varchar(10) COLLATE latin1_general_cs NOT NULL,
  `POSTO` double DEFAULT NULL,
  `ANNO` varchar(4) COLLATE latin1_general_cs NOT NULL,
  `DATA` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `CODICEDITTA` double DEFAULT NULL,
  `DENOMINAZIONE` varchar(100) COLLATE latin1_general_cs NOT NULL,
  `RATA` varchar(20) COLLATE latin1_general_cs NOT NULL,
  `VIE` varchar(50) COLLATE latin1_general_cs NOT NULL,
  `LUNGHEZZA` double DEFAULT NULL,
  `LARGHEZZA` double DEFAULT NULL,
  `MQ` double DEFAULT NULL,
  `GIORNI` double DEFAULT NULL,
  `ORE` double DEFAULT NULL,
  `CODICETASSA` varchar(5) COLLATE latin1_general_cs NOT NULL,
  `IMPORTOTASSA` double DEFAULT NULL,
  `DAPAGARE` double DEFAULT NULL,
  `PAGATO` double DEFAULT NULL,
  `ESTREMI` varchar(30) COLLATE latin1_general_cs NOT NULL,
  `NOTE` varchar(200) COLLATE latin1_general_cs NOT NULL,
  `DATAPAGAMENTO` varchar(8) COLLATE latin1_general_cs NOT NULL,
  PRIMARY KEY (`ROWID`),
  UNIQUE KEY `UNIQ_COSAPSTORICO` (`TIPO`,`TIPOORDINE`,`POSTO`,`ANNO`,`DATA`,`CODICEDITTA`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `DITTEDOC` (
  `ROWID` int(11) NOT NULL AUTO_INCREMENT,
  `CODICE` double DEFAULT NULL,
  `DATA` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `PROGRESSIVO` int(11) DEFAULT NULL,
  `PROTOCOLLO` varchar(20) COLLATE latin1_general_cs NOT NULL,
  `DATAPROTOCOLLO` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `CODICEDOCUMENTO` varchar(30) COLLATE latin1_general_cs NOT NULL,
  `TIPODOCUMENTO` varchar(50) COLLATE latin1_general_cs NOT NULL,
  `FILE` varchar(200) COLLATE latin1_general_cs NOT NULL,
  `NOTE` varchar(200) COLLATE latin1_general_cs NOT NULL,
  `GIUSTIFICATODAL` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `GIUSTIFICATOAL` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `TIPOAUTORIZZAZIONE` varchar(3) COLLATE latin1_general_cs NOT NULL,
  `NUMERO` varchar(20) COLLATE latin1_general_cs NOT NULL,
  `DATAARCHIVIAZIONE` varchar(8) COLLATE latin1_general_cs NOT NULL,
  PRIMARY KEY (`ROWID`),
  UNIQUE KEY `UNIQ_DITTEDOC` (`CODICE`,`DATA`,`PROGRESSIVO`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `DITTELIC` (
  `ROWID` int(11) NOT NULL AUTO_INCREMENT,
  `CODICE` double DEFAULT NULL,
  `TIPOAUTORIZZAZIONE` varchar(3) COLLATE latin1_general_cs NOT NULL,
  `NUMERO` varchar(20) COLLATE latin1_general_cs NOT NULL,
  `ISTAT` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `UTILIZZAPERFIERE` varchar(1) COLLATE latin1_general_cs NOT NULL,
  `DATARILASCIO` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `DATARILASCIOLICENZA` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `DATAISCRIZIONEREGD` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `DATAISCRLICENZA` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `DATAINIZIOATTIVITA` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `DATAINIZIOATTIVITALIC` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `NUMEROISCRREGD` varchar(20) COLLATE latin1_general_cs NOT NULL,
  `CODICECOMUNE` varchar(6) COLLATE latin1_general_cs DEFAULT NULL,
  `COMUNE` varchar(50) COLLATE latin1_general_cs NOT NULL,
  `CODICEREGIONE` varchar(3) COLLATE latin1_general_cs NOT NULL,
  `REGIONE` varchar(30) COLLATE latin1_general_cs NOT NULL,
  `NUMEROCONCESSIONE` varchar(20) COLLATE latin1_general_cs NOT NULL,
  `DATACONCESSIONE` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `CODICEAUTORITA` smallint(6) DEFAULT NULL,
  `AUTORITA` varchar(50) COLLATE latin1_general_cs NOT NULL,
  `PROPRIETARIO_I` varchar(100) COLLATE latin1_general_cs NOT NULL,
  `PROPRIETARIO_II` varchar(100) COLLATE latin1_general_cs NOT NULL,
  `PROPRIETARIO_III` varchar(100) COLLATE latin1_general_cs NOT NULL,
  `PROPRIETARIO_IV` varchar(100) COLLATE latin1_general_cs NOT NULL,
  `RD_I` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `RD_II` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `RD_III` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `RD_IV` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `DRIL_I` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `DRIL_II` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `DRIL_III` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `DRIL_IV` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `DIA_I` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `DIA_II` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `DIA_III` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `DIA_IV` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `NUMAUT_I` varchar(20) COLLATE latin1_general_cs NOT NULL,
  `NUMAUT_II` varchar(20) COLLATE latin1_general_cs NOT NULL,
  `NUMAUT_III` varchar(20) COLLATE latin1_general_cs NOT NULL,
  `NUMAUT_IV` varchar(20) COLLATE latin1_general_cs NOT NULL,
  `SETTOREMERCEOLOGICO` varchar(30) COLLATE latin1_general_cs NOT NULL,
  `ALIMENTIBEVANDE` varchar(2) COLLATE latin1_general_cs NOT NULL,
  `MERCATO` varchar(5) COLLATE latin1_general_cs NOT NULL,
  `FIERA` varchar(5) COLLATE latin1_general_cs NOT NULL,
  `TIPOORDINE` varchar(10) COLLATE latin1_general_cs NOT NULL,
  `POSTO` double DEFAULT NULL,
  `CODICEVIA` varchar(6) COLLATE latin1_general_cs DEFAULT NULL,
  `DATADOMANDADEC` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `DATAVALIDITA` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `NOTE` text COLLATE latin1_general_cs NOT NULL,
  `NONATTIVA` tinyint(4) NOT NULL,
  `TIPOI` varchar(1) COLLATE latin1_general_cs NOT NULL,
  `TIPOII` varchar(1) COLLATE latin1_general_cs NOT NULL,
  `TIPOIII` varchar(1) COLLATE latin1_general_cs NOT NULL,
  `TIPOIV` varchar(1) COLLATE latin1_general_cs NOT NULL,
  `NOTE1` text COLLATE latin1_general_cs NOT NULL,
  `CCIAA` varchar(30) COLLATE latin1_general_cs NOT NULL,
  `DATABOLLO` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `NUMEROBOLLO` text COLLATE latin1_general_cs NOT NULL,
  `BADGE` varchar(20) COLLATE latin1_general_cs NOT NULL,
  `DELIBERA` varchar(20) COLLATE latin1_general_cs NOT NULL,
  `DATADELIBERA` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `TIPOLICENZA` varchar(1) COLLATE latin1_general_cs NOT NULL COMMENT 'Opzioni: N = Nuova Licenza, S = Subingresso, C = Conversione',
  `DATACESSAZIONE` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `IDCOMLIC` int(11) NOT NULL,
  `FASCICOLO` varchar(10) COLLATE latin1_general_cs NOT NULL,
  `PREFERENZASPUNTA` tinyint(4) NOT NULL,
  `DATTO_I` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `DATTO_II` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `DATTO_III` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `DATTO_IV` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `CODICEPROP_I` int(11) NOT NULL,
  `CODICEPROP_II` int(11) NOT NULL,
  `CODICEPROP_III` int(11) NOT NULL,
  `CODICEPROP_IV` int(11) NOT NULL,
  `EVENTOATTIVAZIONE` varchar(5) COLLATE latin1_general_cs NOT NULL,
  `EVENTOCESSAZIONE` varchar(5) COLLATE latin1_general_cs NOT NULL,
  `LETTERA` varchar(5) COLLATE latin1_general_cs NOT NULL,
  `DATABOLLOLIC` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `NUMEROBOLLOLIC` text COLLATE latin1_general_cs NOT NULL,
  `DATACOMUNICAZIONE` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `PROTCOMUNICAZIONE` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `LICMETA` text COLLATE latin1_general_cs,
  PRIMARY KEY (`ROWID`),
  UNIQUE KEY `UNIQ_DITTELIC` (`CODICE`,`TIPOAUTORIZZAZIONE`,`NUMERO`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `DITTEMAIL` (
  `ROWID` int(11) NOT NULL AUTO_INCREMENT,
  `CODICE` int(11) NOT NULL,
  `PEC` tinyint(4) NOT NULL COMMENT 'true = PEC, false = email',
  `INDIRIZZO` varchar(200) COLLATE latin1_general_cs NOT NULL,
  `NOTE` text COLLATE latin1_general_cs NOT NULL,
  PRIMARY KEY (`ROWID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `DITTEPRE` (
  `ROWID` int(11) NOT NULL AUTO_INCREMENT,
  `CODICE` double DEFAULT NULL,
  `TIPOFIERA` varchar(5) COLLATE latin1_general_cs NOT NULL,
  `TIPOAUTORIZZAZIONE` varchar(3) COLLATE latin1_general_cs NOT NULL,
  `NUMERO` varchar(20) COLLATE latin1_general_cs NOT NULL,
  `DATA` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `ANNI` smallint(6) DEFAULT NULL,
  `NUMEROPRESENZE` double NOT NULL,
  `NUMEROASSENZE` double NOT NULL,
  `NUMEROSPUNTA` double NOT NULL,
  `CODICEPRESENZA` varchar(5) COLLATE latin1_general_cs NOT NULL,
  `TIPOLOGIAPRESENZA` varchar(2) COLLATE latin1_general_cs NOT NULL,
  `PRESENZADISPUNTA` double NOT NULL,
  `SPUNTISTA` varchar(20) COLLATE latin1_general_cs NOT NULL,
  `NOTE` varchar(255) COLLATE latin1_general_cs NOT NULL,
  PRIMARY KEY (`ROWID`),
  UNIQUE KEY `UNIQ_DITTEPRE` (`CODICE`,`TIPOFIERA`,`TIPOAUTORIZZAZIONE`,`NUMERO`,`DATA`),
  KEY `FIERA` (`TIPOFIERA`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `DITTEPRM` (
  `ROWID` int(11) NOT NULL AUTO_INCREMENT,
  `CODICE` double NOT NULL,
  `TIPOMERCATO` varchar(5) COLLATE latin1_general_cs NOT NULL,
  `TIPOAUTORIZZAZIONE` varchar(3) COLLATE latin1_general_cs NOT NULL,
  `NUMERO` varchar(20) COLLATE latin1_general_cs NOT NULL,
  `DATA` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `ANNI` smallint(6) DEFAULT NULL,
  `MESI` smallint(6) DEFAULT NULL,
  `CODICEPRESENZA` varchar(5) COLLATE latin1_general_cs NOT NULL,
  `TIPOLOGIAPRESENZA` varchar(2) COLLATE latin1_general_cs NOT NULL,
  `NUMEROPRESENZE` double NOT NULL,
  `NUMEROASSENZE` double NOT NULL,
  `NUMEROSPUNTA` double NOT NULL,
  `SPUNTISTA` varchar(20) COLLATE latin1_general_cs NOT NULL,
  `CONSIDERA` varchar(1) COLLATE latin1_general_cs NOT NULL,
  `NOTE` varchar(50) COLLATE latin1_general_cs NOT NULL,
  PRIMARY KEY (`ROWID`),
  UNIQUE KEY `UNIQ_DITTEPRM` (`CODICE`,`TIPOMERCATO`,`TIPOAUTORIZZAZIONE`,`NUMERO`,`DATA`),
  KEY `I_MERCATO` (`TIPOMERCATO`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `DITTESOGG` (
  `ROWID` int(11) NOT NULL AUTO_INCREMENT,
  `CODICE` int(11) NOT NULL,
  `RUOCOD` varchar(4) COLLATE latin1_general_cs NOT NULL COMMENT 'codice tabella ANA_RUOLI',
  `NOMINATIVO` varchar(100) COLLATE latin1_general_cs NOT NULL,
  `DATANASCITA` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `COMUNENASCITA` varchar(50) COLLATE latin1_general_cs NOT NULL,
  `INDIRIZZO` varchar(100) COLLATE latin1_general_cs NOT NULL,
  `CIVICO` varchar(5) COLLATE latin1_general_cs NOT NULL,
  `COMUNE` varchar(50) COLLATE latin1_general_cs NOT NULL,
  `CAP` varchar(5) COLLATE latin1_general_cs NOT NULL,
  `PROVINCIA` varchar(2) COLLATE latin1_general_cs NOT NULL,
  `TELEFONO` varchar(30) COLLATE latin1_general_cs NOT NULL,
  `CF` varchar(16) COLLATE latin1_general_cs NOT NULL,
  `PIVA` varchar(11) COLLATE latin1_general_cs NOT NULL,
  PRIMARY KEY (`ROWID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `DITTEVER` (
  `ROWID` int(11) NOT NULL AUTO_INCREMENT,
  `CODICE` double DEFAULT NULL,
  `PROGRESSIVO` int(11) DEFAULT NULL,
  `NUMEROREGGEN` varchar(20) COLLATE latin1_general_cs NOT NULL,
  `NUMEROREGPAR` varchar(20) COLLATE latin1_general_cs NOT NULL,
  `NOTE` varchar(100) COLLATE latin1_general_cs NOT NULL,
  PRIMARY KEY (`ROWID`),
  UNIQUE KEY `UNIQ_DITTEVER` (`CODICE`,`PROGRESSIVO`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `FIERA` (
  `ROWID` int(11) NOT NULL AUTO_INCREMENT,
  `FIERA` varchar(5) COLLATE latin1_general_cs NOT NULL,
  `DATA` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `ASSEGNAZIONE` smallint(6) DEFAULT NULL,
  `TIPO` varchar(20) COLLATE latin1_general_cs NOT NULL,
  `DATATERMINE` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `IMPORTO` double DEFAULT NULL,
  `NOTE` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `AGGIORNATE` varchar(2) COLLATE latin1_general_cs NOT NULL,
  `DATAAGG` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `NOMEUTENTE` varchar(20) COLLATE latin1_general_cs NOT NULL,
  `DATAINSERIMENTO` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `UTENTEMODIFICA` varchar(20) COLLATE latin1_general_cs NOT NULL,
  `DATAMODIFICA` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `ENTE` smallint(6) DEFAULT NULL,
  PRIMARY KEY (`ROWID`),
  UNIQUE KEY `UNIQ_FIERA` (`FIERA`,`DATA`,`ASSEGNAZIONE`,`TIPO`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `FIERAID` (
  `ROWID` int(11) NOT NULL AUTO_INCREMENT,
  `FIERA` varchar(5) COLLATE latin1_general_cs NOT NULL,
  `DATA` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `ASSEGNAZIONE` smallint(6) DEFAULT NULL,
  `TIPO` varchar(20) COLLATE latin1_general_cs NOT NULL,
  `DENOMINAZIONE` varchar(100) COLLATE latin1_general_cs NOT NULL,
  `CODICEDITTA` double DEFAULT NULL,
  `TIPOORDINE` varchar(10) COLLATE latin1_general_cs NOT NULL,
  `POSTO` double DEFAULT NULL,
  `CODICEVIA` varchar(6) COLLATE latin1_general_cs DEFAULT NULL,
  `CODICEPRESENZA` varchar(5) COLLATE latin1_general_cs NOT NULL,
  `TIPOAUTORIZZAZIONE` varchar(3) COLLATE latin1_general_cs NOT NULL,
  `NUMERO` varchar(20) COLLATE latin1_general_cs NOT NULL,
  `NOTE` varchar(200) COLLATE latin1_general_cs NOT NULL,
  `ENTE` smallint(6) DEFAULT NULL,
  PRIMARY KEY (`ROWID`),
  UNIQUE KEY `UNIQ_FIERAID` (`FIERA`,`DATA`,`ASSEGNAZIONE`,`TIPO`,`DENOMINAZIONE`,`CODICEDITTA`,`TIPOORDINE`,`POSTO`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `FIERE` (
  `ROWID` int(11) NOT NULL AUTO_INCREMENT,
  `FIERA` varchar(5) COLLATE latin1_general_cs NOT NULL,
  `DATA` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `ASSEGNAZIONE` smallint(6) DEFAULT NULL,
  `DATATERMINE` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `IMPORTO` double DEFAULT NULL,
  `NOTE` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `AGGIORNATE` varchar(2) COLLATE latin1_general_cs NOT NULL,
  `DATAAGGIORNAMENTO` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `NOMEUTENTE` varchar(20) COLLATE latin1_general_cs NOT NULL,
  `DATAINSERIMENTO` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `UTENTEMODIFICA` varchar(20) COLLATE latin1_general_cs NOT NULL,
  `DATAMODIFICA` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `ENTE` smallint(6) DEFAULT NULL,
  `PROTOCOLLO` varchar(10) COLLATE latin1_general_cs NOT NULL,
  `DATAPROTOCOLLO` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `PROTOCOLLO1` varchar(10) COLLATE latin1_general_cs NOT NULL,
  `DATAPROTOCOLLO1` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `PROTOCOLLO2` varchar(10) COLLATE latin1_general_cs NOT NULL,
  `DATAPROTOCOLLO2` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `DECENNALE` tinyint(4) NOT NULL,
  `PROTGRADUATORIA` varchar(30) COLLATE latin1_general_cs NOT NULL,
  `PROTGRADDATA` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `PROTCONVOCAZIONE` varchar(30) COLLATE latin1_general_cs NOT NULL,
  `PROTCONVDATA` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `PROTCONCESSIONE` varchar(30) COLLATE latin1_general_cs NOT NULL,
  `PROTCONCDATA` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `NATTO` varchar(30) COLLATE latin1_general_cs NOT NULL,
  `DATAATTO` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `TIPONS` varchar(20) COLLATE latin1_general_cs NOT NULL,
  `BLOCCAPUBB` smallint(6) NOT NULL COMMENT 'Flag Blocca Pubblicazione FO',
  `STATO` varchar(100) COLLATE latin1_general_cs NOT NULL,
  `UTENTERILEVAZIONE` varchar(20) COLLATE latin1_general_cs NOT NULL,
  `FL_RILEVAZIONE` tinyint(4) NOT NULL,
  `BANDO` tinyint(4) NOT NULL,
  `TIPOATTIVITA` varchar(5) COLLATE latin1_general_cs NOT NULL,
  PRIMARY KEY (`ROWID`),
  UNIQUE KEY `UNIQ_FIERE` (`FIERA`,`DATA`,`ASSEGNAZIONE`,`DECENNALE`,`TIPONS`,`TIPOATTIVITA`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `FIERECOM` (
  `ROWID` int(11) NOT NULL AUTO_INCREMENT,
  `FIERA` varchar(5) COLLATE latin1_general_cs NOT NULL,
  `DATA` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `ASSEGNAZIONE` smallint(6) DEFAULT NULL,
  `TIPOATTIVITA` varchar(5) COLLATE latin1_general_cs NOT NULL,
  `GRADUATORIA` smallint(6) NOT NULL,
  `CODICE` double DEFAULT NULL,
  `TIPOAUTORIZZAZIONE` varchar(3) COLLATE latin1_general_cs NOT NULL,
  `NUMERO` varchar(20) COLLATE latin1_general_cs NOT NULL,
  `POSTO` double NOT NULL,
  `CODICEVIA` varchar(6) COLLATE latin1_general_cs NOT NULL,
  `TIPOORDINE` varchar(10) COLLATE latin1_general_cs NOT NULL,
  `DENOMINAZIONE` varchar(100) COLLATE latin1_general_cs NOT NULL,
  `KW` double DEFAULT NULL,
  `IMPORTO` double DEFAULT NULL,
  `RIFERIMENTO` varchar(20) COLLATE latin1_general_cs NOT NULL,
  `CONFERMATO` varchar(2) COLLATE latin1_general_cs NOT NULL,
  `PRESENZE` double NOT NULL,
  `DATADOMANDA` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `IDONEO` varchar(2) COLLATE latin1_general_cs NOT NULL,
  `TIPOITER` smallint(6) DEFAULT NULL,
  `DOCUMENTO1` varchar(50) COLLATE latin1_general_cs NOT NULL,
  `DOCUMENTO2` varchar(50) COLLATE latin1_general_cs NOT NULL,
  `DOCUMENTO3` varchar(50) COLLATE latin1_general_cs NOT NULL,
  `DOCUMENTO4` varchar(50) COLLATE latin1_general_cs NOT NULL,
  `DOCUMENTO5` varchar(50) COLLATE latin1_general_cs NOT NULL,
  `TIPO1` varchar(1) COLLATE latin1_general_cs NOT NULL,
  `TIPO2` varchar(1) COLLATE latin1_general_cs NOT NULL,
  `TIPO3` varchar(1) COLLATE latin1_general_cs NOT NULL,
  `TIPO4` varchar(1) COLLATE latin1_general_cs NOT NULL,
  `TIPO5` varchar(1) COLLATE latin1_general_cs NOT NULL,
  `DATADOC1` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `DATADOC2` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `DATADOC3` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `DATADOC4` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `DATADOC5` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `CONSEGNATO` smallint(6) DEFAULT NULL,
  `DATACONSEGNA` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `AGGIORNATO` tinyint(4) DEFAULT NULL,
  `COMUNICAZIONEFIERA` text COLLATE latin1_general_cs,
  `DOMANDADEC` tinyint(4) DEFAULT NULL,
  `ENTE` smallint(6) DEFAULT NULL,
  `TASSA` double DEFAULT NULL,
  `IVA` double DEFAULT NULL,
  `TOTALE` double DEFAULT NULL,
  `FATTURA` varchar(20) COLLATE latin1_general_cs NOT NULL,
  `PAGATO` tinyint(4) DEFAULT NULL,
  `DATAPAGAMENTO` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `DECENNALE` tinyint(4) NOT NULL,
  `VALIDAPERFIERE` varchar(100) COLLATE latin1_general_cs NOT NULL,
  `PROTOCOLLO` varchar(10) COLLATE latin1_general_cs NOT NULL,
  `DATAPROTOCOLLO` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `TIPONS` varchar(20) COLLATE latin1_general_cs NOT NULL,
  `ORACONVOCAZIONE` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `DATACONVOCAZIONE` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `PEC` varchar(50) COLLATE latin1_general_cs NOT NULL,
  `SETTORE` varchar(30) COLLATE latin1_general_cs NOT NULL,
  `ORAPEC` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `IDPEC` varchar(100) COLLATE latin1_general_cs NOT NULL,
  `ROWID_PARENT` int(11) NOT NULL COMMENT 'rowid domanda unica',
  `LETTERA` varchar(5) COLLATE latin1_general_cs NOT NULL,
  `PUNTEGGIO1` int(11) NOT NULL,
  `PUNTEGGIO2` int(11) NOT NULL,
  `PUNTEGGIO3` int(11) NOT NULL,
  `PUNTEGGIO4` int(11) NOT NULL,
  `PUNTEGGIOTOTALE` int(11) NOT NULL,
  PRIMARY KEY (`ROWID`),
  UNIQUE KEY `UNIQ_FIERECOM` (`FIERA`,`DATA`,`ASSEGNAZIONE`,`TIPOATTIVITA`,`GRADUATORIA`,`CODICE`,`TIPOAUTORIZZAZIONE`,`NUMERO`,`POSTO`,`CODICEVIA`),
  KEY `CODICE` (`CODICE`,`TIPOAUTORIZZAZIONE`,`NUMERO`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `FIERECOM_PIAZZOLE` (
  `ROW_ID` int(11) NOT NULL AUTO_INCREMENT,
  `ID_FIERECOM` int(11) NOT NULL,
  `POSTO` int(11) NOT NULL,
  `CODICEVIA` varchar(6) COLLATE latin1_general_cs NOT NULL,
  `LETTERA` varchar(5) COLLATE latin1_general_cs NOT NULL,
  PRIMARY KEY (`ROW_ID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `FIEREDOC` (
  `ROWID` int(11) NOT NULL AUTO_INCREMENT,
  `DOCUMENTO` varchar(50) COLLATE latin1_general_cs NOT NULL,
  `NOTE` text COLLATE latin1_general_cs NOT NULL,
  `DATAPRESENTAZIONE` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `ID_DOMANDA` int(11) NOT NULL,
  `NECESSARIO` varchar(1) COLLATE latin1_general_cs NOT NULL COMMENT 'N/F',
  `FILE` varchar(100) COLLATE latin1_general_cs NOT NULL COMMENT 'nome del file',
  PRIMARY KEY (`ROWID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `FIEREIMM` (
  `ROWID` int(11) NOT NULL AUTO_INCREMENT,
  `TIPO` varchar(5) COLLATE latin1_general_cs NOT NULL,
  `PROGRESSIVO` double DEFAULT NULL,
  `IMMAGINE` varchar(200) COLLATE latin1_general_cs DEFAULT NULL,
  `DESCRIZIONE` varchar(250) COLLATE latin1_general_cs NOT NULL,
  `COLORELIBERO` varchar(20) COLLATE latin1_general_cs NOT NULL,
  `COLOREOCCUPATO` varchar(20) COLLATE latin1_general_cs NOT NULL,
  `COLORESPUNTISTA` varchar(20) COLLATE latin1_general_cs NOT NULL,
  PRIMARY KEY (`ROWID`),
  UNIQUE KEY `UNIQ_FIEREIMM` (`TIPO`,`PROGRESSIVO`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `FIEREPOS` (
  `ROWID` int(11) NOT NULL AUTO_INCREMENT,
  `TIPO` varchar(5) COLLATE latin1_general_cs NOT NULL,
  `TIPOORDINE` varchar(10) COLLATE latin1_general_cs NOT NULL,
  `POSTO` double DEFAULT NULL,
  `ASSEGNAZIONE` smallint(6) DEFAULT NULL,
  `CODICEVIA` varchar(6) COLLATE latin1_general_cs DEFAULT NULL,
  `VIE` varchar(50) COLLATE latin1_general_cs NOT NULL,
  `POSTOTIPO` smallint(6) DEFAULT NULL,
  `DESCRIZIONE` varchar(100) COLLATE latin1_general_cs NOT NULL,
  `LATO` varchar(2) COLLATE latin1_general_cs NOT NULL,
  `LUNGHEZZA` double DEFAULT NULL,
  `LARGHEZZA` double DEFAULT NULL,
  `MQ` double DEFAULT NULL,
  `CODICEDITTA` double DEFAULT NULL,
  `TIPOAUTORIZZAZIONE` varchar(3) COLLATE latin1_general_cs NOT NULL,
  `NUMERO` varchar(20) COLLATE latin1_general_cs NOT NULL,
  `NOTE` varchar(200) COLLATE latin1_general_cs NOT NULL,
  `IMMAGINE` varchar(200) COLLATE latin1_general_cs DEFAULT NULL,
  `ALTRIDATI` longblob,
  `LETTERA` varchar(5) COLLATE latin1_general_cs NOT NULL,
  `COORDINATEIMG` text COLLATE latin1_general_cs NOT NULL COMMENT 'coord. per disegno su img',
  `ZONA` varchar(6) COLLATE latin1_general_cs NOT NULL,
  `NONATTIVO` tinyint(4) NOT NULL,
  `IMPORTO` double NOT NULL,
  `CODICETASSA` varchar(5) COLLATE latin1_general_cs NOT NULL,
  PRIMARY KEY (`ROWID`),
  KEY `DITTA` (`CODICEDITTA`),
  KEY `UNIQ_FIEREPOS` (`TIPO`,`POSTO`,`TIPOORDINE`,`ASSEGNAZIONE`,`CODICEVIA`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `FIERESUAP` (
  `ROWID` int(11) NOT NULL AUTO_INCREMENT,
  `IDFIERECOM` int(11) NOT NULL COMMENT 'rowid fierecom',
  `SUAPID` int(11) NOT NULL COMMENT 'rowid pratica',
  `SUAKEY` varchar(30) COLLATE latin1_general_cs NOT NULL COMMENT 'codice ditta SUAP',
  `IDMERCACOM` int(11) NOT NULL,
  PRIMARY KEY (`ROWID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `FTPPARM` (
  `ROWID` int(11) NOT NULL AUTO_INCREMENT,
  `AGENTE` varchar(20) COLLATE latin1_general_cs NOT NULL,
  `SERVER` varchar(100) COLLATE latin1_general_cs NOT NULL,
  `TIPOCONNESSIONE` varchar(20) COLLATE latin1_general_cs NOT NULL,
  `AUTODIAL` tinyint(4) NOT NULL,
  `UTENTE` varchar(30) COLLATE latin1_general_cs NOT NULL,
  `PASSWORD` varchar(30) COLLATE latin1_general_cs NOT NULL,
  `ROOTPATH` varchar(100) COLLATE latin1_general_cs NOT NULL,
  `NOTE` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `DATAINSERIMENTO` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `DATAMODIFICA` varchar(8) COLLATE latin1_general_cs NOT NULL,
  PRIMARY KEY (`ROWID`),
  UNIQUE KEY `UNIQ_FTPPARM` (`AGENTE`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `MAIL` (
  `ROWID` int(11) NOT NULL AUTO_INCREMENT,
  `IDRECORD` int(11) NOT NULL,
  `IDMAIL` text COLLATE latin1_general_cs NOT NULL,
  `TIPOSR` varchar(1) COLLATE latin1_general_cs NOT NULL,
  `TABORIG` varchar(20) COLLATE latin1_general_cs NOT NULL,
  `AMBITO` varchar(50) COLLATE latin1_general_cs NOT NULL,
  `DATA` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `MITTENTE` varchar(50) COLLATE latin1_general_cs NOT NULL,
  `DESTINATARIO` varchar(50) COLLATE latin1_general_cs NOT NULL,
  PRIMARY KEY (`ROWID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `MERCACOM` (
  `ROWID` int(11) NOT NULL AUTO_INCREMENT,
  `FIERA` varchar(5) COLLATE latin1_general_cs NOT NULL,
  `DATA` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `ASSEGNAZIONE` smallint(6) DEFAULT NULL,
  `TIPOATTIVITA` varchar(5) COLLATE latin1_general_cs NOT NULL,
  `GRADUATORIA` smallint(6) NOT NULL,
  `CODICE` double DEFAULT NULL,
  `TIPOAUTORIZZAZIONE` varchar(3) COLLATE latin1_general_cs NOT NULL,
  `NUMERO` varchar(20) COLLATE latin1_general_cs NOT NULL,
  `POSTO` double NOT NULL,
  `CODICEVIA` varchar(6) COLLATE latin1_general_cs NOT NULL,
  `TIPOORDINE` varchar(10) COLLATE latin1_general_cs NOT NULL,
  `DENOMINAZIONE` varchar(100) COLLATE latin1_general_cs NOT NULL,
  `KW` double DEFAULT NULL,
  `IMPORTO` double DEFAULT NULL,
  `RIFERIMENTO` varchar(20) COLLATE latin1_general_cs NOT NULL,
  `CONFERMATO` varchar(2) COLLATE latin1_general_cs NOT NULL,
  `PRESENZE` double NOT NULL,
  `DATADOMANDA` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `IDONEO` varchar(2) COLLATE latin1_general_cs NOT NULL,
  `TIPOITER` smallint(6) DEFAULT NULL,
  `DOCUMENTO1` varchar(50) COLLATE latin1_general_cs NOT NULL,
  `DOCUMENTO2` varchar(50) COLLATE latin1_general_cs NOT NULL,
  `DOCUMENTO3` varchar(50) COLLATE latin1_general_cs NOT NULL,
  `DOCUMENTO4` varchar(50) COLLATE latin1_general_cs NOT NULL,
  `DOCUMENTO5` varchar(50) COLLATE latin1_general_cs NOT NULL,
  `TIPO1` varchar(1) COLLATE latin1_general_cs NOT NULL,
  `TIPO2` varchar(1) COLLATE latin1_general_cs NOT NULL,
  `TIPO3` varchar(1) COLLATE latin1_general_cs NOT NULL,
  `TIPO4` varchar(1) COLLATE latin1_general_cs NOT NULL,
  `TIPO5` varchar(1) COLLATE latin1_general_cs NOT NULL,
  `DATADOC1` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `DATADOC2` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `DATADOC3` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `DATADOC4` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `DATADOC5` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `CONSEGNATO` smallint(6) DEFAULT NULL,
  `DATACONSEGNA` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `AGGIORNATO` tinyint(4) DEFAULT NULL,
  `COMUNICAZIONEFIERA` text COLLATE latin1_general_cs,
  `DOMANDADEC` tinyint(4) DEFAULT NULL,
  `ENTE` smallint(6) DEFAULT NULL,
  `TASSA` double DEFAULT NULL,
  `IVA` double DEFAULT NULL,
  `TOTALE` double DEFAULT NULL,
  `FATTURA` varchar(20) COLLATE latin1_general_cs NOT NULL,
  `PAGATO` tinyint(4) DEFAULT NULL,
  `DATAPAGAMENTO` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `DECENNALE` tinyint(4) NOT NULL,
  `VALIDAPERFIERE` varchar(100) COLLATE latin1_general_cs NOT NULL,
  `PROTOCOLLO` varchar(10) COLLATE latin1_general_cs NOT NULL,
  `DATAPROTOCOLLO` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `TIPONS` varchar(20) COLLATE latin1_general_cs NOT NULL,
  `ORACONVOCAZIONE` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `DATACONVOCAZIONE` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `PEC` varchar(50) COLLATE latin1_general_cs NOT NULL,
  `SETTORE` varchar(30) COLLATE latin1_general_cs NOT NULL,
  `ORAPEC` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `IDPEC` varchar(100) COLLATE latin1_general_cs NOT NULL,
  `ROWID_PARENT` int(11) NOT NULL COMMENT 'rowid domanda unica',
  `LETTERA` varchar(5) COLLATE latin1_general_cs NOT NULL,
  `PUNTEGGIO1` int(11) NOT NULL,
  `PUNTEGGIO2` int(11) NOT NULL,
  `PUNTEGGIO3` int(11) NOT NULL,
  `PUNTEGGIO4` int(11) NOT NULL,
  `PUNTEGGIOTOTALE` int(11) NOT NULL,
  PRIMARY KEY (`ROWID`),
  UNIQUE KEY `UNIQ_FIERECOM` (`FIERA`,`DATA`,`ASSEGNAZIONE`,`TIPOATTIVITA`,`GRADUATORIA`,`CODICE`,`TIPOAUTORIZZAZIONE`,`NUMERO`,`POSTO`,`CODICEVIA`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `MERCADOC` (
  `ROWID` int(11) NOT NULL AUTO_INCREMENT,
  `DOCUMENTO` varchar(50) COLLATE latin1_general_cs NOT NULL,
  `NOTE` text COLLATE latin1_general_cs NOT NULL,
  `DATAPRESENTAZIONE` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `ID_DOMANDA` int(11) NOT NULL,
  `NECESSARIO` varchar(1) COLLATE latin1_general_cs NOT NULL COMMENT 'N/F',
  `FILE` varchar(100) COLLATE latin1_general_cs NOT NULL COMMENT 'nome del file',
  PRIMARY KEY (`ROWID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `MERCAIMM` (
  `ROWID` int(11) NOT NULL AUTO_INCREMENT,
  `TIPO` varchar(5) COLLATE latin1_general_cs NOT NULL,
  `PROGRESSIVO` double DEFAULT NULL,
  `IMMAGINE` varchar(200) COLLATE latin1_general_cs DEFAULT NULL,
  `DESCRIZIONE` varchar(250) COLLATE latin1_general_cs NOT NULL,
  `COLORELIBERO` varchar(20) COLLATE latin1_general_cs NOT NULL,
  `COLOREOCCUPATO` varchar(20) COLLATE latin1_general_cs NOT NULL,
  `COLORESPUNTISTA` varchar(20) COLLATE latin1_general_cs NOT NULL,
  PRIMARY KEY (`ROWID`),
  UNIQUE KEY `UNIQ_MERCAIMM` (`TIPO`,`PROGRESSIVO`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `MERCAPOS` (
  `ROWID` int(11) NOT NULL AUTO_INCREMENT,
  `TIPO` varchar(5) COLLATE latin1_general_cs NOT NULL,
  `TIPOORDINE` varchar(10) COLLATE latin1_general_cs NOT NULL,
  `POSTO` double DEFAULT NULL,
  `ASSEGNAZIONE` smallint(6) NOT NULL,
  `CODICEVIA` varchar(6) COLLATE latin1_general_cs DEFAULT NULL,
  `VIE` varchar(50) COLLATE latin1_general_cs NOT NULL,
  `POSTOTIPO` smallint(6) DEFAULT NULL,
  `DESCRIZIONE` varchar(100) COLLATE latin1_general_cs NOT NULL,
  `LATO` varchar(2) COLLATE latin1_general_cs NOT NULL,
  `LUNGHEZZA` double DEFAULT NULL,
  `LARGHEZZA` double DEFAULT NULL,
  `MQ` double DEFAULT NULL,
  `CODICEDITTA` double DEFAULT NULL,
  `TIPOAUTORIZZAZIONE` varchar(3) COLLATE latin1_general_cs NOT NULL,
  `NUMERO` varchar(20) COLLATE latin1_general_cs NOT NULL,
  `NOTE` text COLLATE latin1_general_cs NOT NULL,
  `IMMAGINE` varchar(200) COLLATE latin1_general_cs DEFAULT NULL,
  `ALTRIDATI` longblob,
  `LETTERA` varchar(5) COLLATE latin1_general_cs NOT NULL,
  `COORDINATEIMG` text COLLATE latin1_general_cs NOT NULL COMMENT 'coord. per disegno su img',
  `ZONA` varchar(6) COLLATE latin1_general_cs NOT NULL,
  `NONATTIVO` tinyint(4) NOT NULL,
  `IMPORTO` double NOT NULL,
  `CODICETASSA` varchar(5) COLLATE latin1_general_cs NOT NULL,
  PRIMARY KEY (`ROWID`),
  KEY `DITTA` (`CODICEDITTA`),
  KEY `UNIQ_MERCAPOS` (`TIPO`,`POSTO`,`TIPOORDINE`,`CODICEVIA`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `MERCATI` (
  `ROWID` int(11) NOT NULL AUTO_INCREMENT,
  `MERCATO` varchar(5) COLLATE latin1_general_cs NOT NULL,
  `DATA` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `TIPO` varchar(20) COLLATE latin1_general_cs NOT NULL,
  `TESTO` text COLLATE latin1_general_cs,
  `DATIVARI` text COLLATE latin1_general_cs,
  `AGGIORNATE` smallint(6) NOT NULL,
  `DATAAGG` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `NOTE` text COLLATE latin1_general_cs,
  `NOMEUTENTE` varchar(20) COLLATE latin1_general_cs NOT NULL,
  `DATAINSERIMENTO` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `UTENTEMODIFICA` varchar(20) COLLATE latin1_general_cs NOT NULL,
  `DATAMODIFICA` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `ENTE` smallint(6) DEFAULT NULL,
  `STATO` varchar(100) COLLATE latin1_general_cs NOT NULL,
  `UTENTERILEVAZIONE` varchar(20) COLLATE latin1_general_cs NOT NULL,
  `FL_RILEVAZIONE` tinyint(4) NOT NULL,
  PRIMARY KEY (`ROWID`),
  UNIQUE KEY `UNIQ_MERCATI` (`MERCATO`,`DATA`,`TIPO`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `MERCATID` (
  `ROWID` int(11) NOT NULL AUTO_INCREMENT,
  `FIERA` varchar(5) COLLATE latin1_general_cs NOT NULL,
  `DATA` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `TIPO` varchar(20) COLLATE latin1_general_cs NOT NULL,
  `DENOMINAZIONE` varchar(100) COLLATE latin1_general_cs NOT NULL,
  `CODICEDITTA` double NOT NULL,
  `TIPOORDINE` varchar(10) COLLATE latin1_general_cs NOT NULL,
  `POSTO` double DEFAULT NULL,
  `CODICEVIA` varchar(6) COLLATE latin1_general_cs DEFAULT NULL,
  `CODICEPRESENZA` varchar(5) COLLATE latin1_general_cs NOT NULL,
  `TIPOAUTORIZZAZIONE` varchar(3) COLLATE latin1_general_cs NOT NULL,
  `NUMERO` varchar(20) COLLATE latin1_general_cs NOT NULL,
  `NOTE` varchar(200) COLLATE latin1_general_cs NOT NULL,
  `ENTE` smallint(6) DEFAULT NULL,
  `ORAINGRESSO` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `NUMEROBADGE` varchar(20) COLLATE latin1_general_cs NOT NULL,
  `LETTERA` varchar(5) COLLATE latin1_general_cs NOT NULL,
  `UTENTEMODIFICA` varchar(20) COLLATE latin1_general_cs NOT NULL,
  `DATAMODIFICA` varchar(8) COLLATE latin1_general_cs NOT NULL,
  PRIMARY KEY (`ROWID`),
  UNIQUE KEY `UNIQ_MERCATID` (`FIERA`,`DATA`,`TIPO`,`CODICEDITTA`,`TIPOORDINE`,`POSTO`,`CODICEVIA`,`LETTERA`),
  KEY `DITTA` (`CODICEDITTA`),
  KEY `POSTO` (`POSTO`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `PARAMETRITIPOCOSAP` (
  `ROWID` int(11) NOT NULL AUTO_INCREMENT,
  `TIPO` varchar(20) COLLATE latin1_general_cs NOT NULL,
  `CODICE` smallint(6) NOT NULL,
  `CAMPO` varchar(50) COLLATE latin1_general_cs NOT NULL,
  `VALORE` varchar(200) COLLATE latin1_general_cs NOT NULL,
  PRIMARY KEY (`ROWID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `POSTIASS` (
  `ROWID` int(11) NOT NULL AUTO_INCREMENT,
  `FIERA` varchar(5) COLLATE latin1_general_cs NOT NULL,
  `DATA` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `ASSEGNAZIONE` smallint(6) DEFAULT NULL,
  `TIPOATTIVITA` varchar(5) COLLATE latin1_general_cs NOT NULL,
  `GRADUATORIA` smallint(6) NOT NULL,
  `CODICE` double DEFAULT NULL,
  `TIPOAUTORIZZAZIONE` varchar(3) COLLATE latin1_general_cs NOT NULL,
  `NUMERO` varchar(20) COLLATE latin1_general_cs NOT NULL,
  `POSTO` double DEFAULT NULL,
  `CODICEVIA` varchar(6) COLLATE latin1_general_cs DEFAULT NULL,
  `TIPOORDINE` varchar(10) COLLATE latin1_general_cs NOT NULL,
  `DENOMINAZIONE` varchar(100) COLLATE latin1_general_cs NOT NULL,
  `KW` double DEFAULT NULL,
  `IMPORTO` double DEFAULT NULL,
  `RIFERIMENTO` varchar(20) COLLATE latin1_general_cs NOT NULL,
  `CONFERMATO` varchar(2) COLLATE latin1_general_cs NOT NULL,
  `PRESENZE` double NOT NULL,
  `DATADOMANDA` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `IDONEO` varchar(2) COLLATE latin1_general_cs NOT NULL,
  `TIPOITER` smallint(6) DEFAULT NULL,
  `DOCUMENTO1` varchar(50) COLLATE latin1_general_cs NOT NULL,
  `DOCUMENTO2` varchar(50) COLLATE latin1_general_cs NOT NULL,
  `DOCUMENTO3` varchar(50) COLLATE latin1_general_cs NOT NULL,
  `DOCUMENTO4` varchar(50) COLLATE latin1_general_cs NOT NULL,
  `DOCUMENTO5` varchar(50) COLLATE latin1_general_cs NOT NULL,
  `TIPO1` varchar(1) COLLATE latin1_general_cs NOT NULL,
  `TIPO2` varchar(1) COLLATE latin1_general_cs NOT NULL,
  `TIPO3` varchar(1) COLLATE latin1_general_cs NOT NULL,
  `TIPO4` varchar(1) COLLATE latin1_general_cs NOT NULL,
  `TIPO5` varchar(1) COLLATE latin1_general_cs NOT NULL,
  `DATADOC1` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `DATADOC2` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `DATADOC3` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `DATADOC4` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `DATADOC5` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `CONSEGNATO` smallint(6) DEFAULT NULL,
  `DATACONSEGNA` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `AGGIORNATO` tinyint(4) DEFAULT NULL,
  `COMUNICAZIONEFIERA` text COLLATE latin1_general_cs,
  `DOMANDADEC` tinyint(4) DEFAULT NULL,
  `ENTE` smallint(6) DEFAULT NULL,
  `TASSA` double DEFAULT NULL,
  `IVA` double DEFAULT NULL,
  `TOTALE` double DEFAULT NULL,
  `FATTURA` varchar(20) COLLATE latin1_general_cs NOT NULL,
  `PAGATO` tinyint(4) DEFAULT NULL,
  `DATAPAGAMENTO` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `CODICE_1` double DEFAULT NULL,
  `TIPOAUTORIZZAZIONE_1` varchar(3) COLLATE latin1_general_cs NOT NULL,
  `NUMERO_1` varchar(20) COLLATE latin1_general_cs NOT NULL,
  `ISTAT` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `UTILIZZAPERFIERE` varchar(1) COLLATE latin1_general_cs NOT NULL,
  `DATARILASCIO` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `DATARILASCIOLICENZA` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `DATAISCRIZIONEREGD` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `DATAISCRLICENZA` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `DATAINIZIOATTIVITA` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `DATAINIZIOATTIVITALIC` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `NUMEROISCRREGD` varchar(20) COLLATE latin1_general_cs NOT NULL,
  `CODICECOMUNE` smallint(6) DEFAULT NULL,
  `COMUNE` varchar(50) COLLATE latin1_general_cs NOT NULL,
  `CODICEREGIONE` varchar(3) COLLATE latin1_general_cs NOT NULL,
  `REGIONE` varchar(30) COLLATE latin1_general_cs NOT NULL,
  `NUMEROCONCESSIONE` varchar(20) COLLATE latin1_general_cs NOT NULL,
  `DATACONCESSIONE` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `CODICEAUTORITA` smallint(6) DEFAULT NULL,
  `AUTORITA` varchar(50) COLLATE latin1_general_cs NOT NULL,
  `PROPRIETARIO_I` varchar(100) COLLATE latin1_general_cs NOT NULL,
  `PROPRIETARIO_II` varchar(100) COLLATE latin1_general_cs NOT NULL,
  `PROPRIETARIO_III` varchar(100) COLLATE latin1_general_cs NOT NULL,
  `PROPRIETARIO_IV` varchar(100) COLLATE latin1_general_cs NOT NULL,
  `RD_I` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `RD_II` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `RD_III` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `RD_IV` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `DRIL_I` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `DRIL_II` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `DRIL_III` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `DRIL_IV` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `DIA_I` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `DIA_II` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `DIA_III` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `DIA_IV` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `NUMAUT_I` varchar(10) COLLATE latin1_general_cs NOT NULL,
  `NUMAUT_II` varchar(10) COLLATE latin1_general_cs NOT NULL,
  `NUMAUT_III` varchar(10) COLLATE latin1_general_cs NOT NULL,
  `NUMAUT_IV` varchar(10) COLLATE latin1_general_cs NOT NULL,
  `SETTOREMERCEOLOGICO` varchar(30) COLLATE latin1_general_cs NOT NULL,
  `ALIMENTIBEVANDE` varchar(2) COLLATE latin1_general_cs NOT NULL,
  `MERCATO` varchar(5) COLLATE latin1_general_cs NOT NULL,
  `FIERA_1` varchar(5) COLLATE latin1_general_cs NOT NULL,
  `TIPOORDINE_1` varchar(10) COLLATE latin1_general_cs NOT NULL,
  `POSTO_1` double DEFAULT NULL,
  `CODICEVIA_1` smallint(6) DEFAULT NULL,
  `DATADOMANDADEC` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `DATAVALIDITA` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `NOTE` varchar(200) COLLATE latin1_general_cs NOT NULL,
  `NONATTIVA` tinyint(4) DEFAULT NULL,
  `TIPOI` varchar(1) COLLATE latin1_general_cs NOT NULL,
  `TIPOII` varchar(1) COLLATE latin1_general_cs NOT NULL,
  `TIPOIII` varchar(1) COLLATE latin1_general_cs NOT NULL,
  `TIPOIV` varchar(1) COLLATE latin1_general_cs NOT NULL,
  `NOTE1` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `NUMEROPRESENZE` double DEFAULT NULL,
  `DECENNALE` tinyint(4) NOT NULL,
  `NUMEROSPUNTA` double DEFAULT NULL,
  `VALIDAPERFIERE` varchar(100) COLLATE latin1_general_cs NOT NULL,
  `PROTOCOLLO` varchar(10) COLLATE latin1_general_cs NOT NULL,
  `DATAPROTOCOLLO` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `TIPONS` varchar(20) COLLATE latin1_general_cs NOT NULL,
  `PROTGRADUATORIA` varchar(30) COLLATE latin1_general_cs NOT NULL,
  `PROTGRADDATA` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `CCIAA` varchar(30) COLLATE latin1_general_cs NOT NULL,
  `DATABOLLO` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `NUMEROBOLLO` text COLLATE latin1_general_cs NOT NULL,
  `BADGE` varchar(20) COLLATE latin1_general_cs NOT NULL,
  `DELIBERA` varchar(20) COLLATE latin1_general_cs NOT NULL,
  `DATADELIBERA` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `TIPOLICENZA` varchar(1) COLLATE latin1_general_cs NOT NULL COMMENT 'Opzioni: N = Nuova Licenza, S = Subingresso, C = Conversione',
  `ORACONVOCAZIONE` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `DATACONVOCAZIONE` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `DATACESSAZIONE` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `IDCOMLIC` int(11) NOT NULL,
  `FASCICOLO` varchar(10) COLLATE latin1_general_cs NOT NULL,
  `PREFERENZASPUNTA` tinyint(4) NOT NULL,
  `PEC` varchar(50) COLLATE latin1_general_cs NOT NULL,
  `SETTORE` varchar(30) COLLATE latin1_general_cs NOT NULL,
  `ORAPEC` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `IDPEC` varchar(100) COLLATE latin1_general_cs NOT NULL,
  `ROWID_PARENT` int(11) NOT NULL COMMENT 'rowid domanda unica',
  `DATTO_I` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `DATTO_II` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `DATTO_III` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `DATTO_IV` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `CODICEPROP_I` int(11) NOT NULL,
  `CODICEPROP_II` int(11) NOT NULL,
  `CODICEPROP_III` int(11) NOT NULL,
  `CODICEPROP_IV` int(11) NOT NULL,
  `EVENTOATTIVAZIONE` varchar(5) COLLATE latin1_general_cs NOT NULL,
  `EVENTOCESSAZIONE` varchar(5) COLLATE latin1_general_cs NOT NULL,
  `LETTERA` varchar(5) COLLATE latin1_general_cs NOT NULL,
  `DATABOLLOLIC` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `NUMEROBOLLOLIC` text COLLATE latin1_general_cs NOT NULL,
  `PUNTEGGIO1` int(11) NOT NULL,
  `PUNTEGGIO2` int(11) NOT NULL,
  `PUNTEGGIO3` int(11) NOT NULL,
  `PUNTEGGIO4` int(11) NOT NULL,
  `PUNTEGGIOTOTALE` int(11) NOT NULL,
  `DATACOMUNICAZIONE` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `PROTCOMUNICAZIONE` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `LICMETA` text COLLATE latin1_general_cs,
  PRIMARY KEY (`ROWID`),
  UNIQUE KEY `UNIQ_POSTIASS` (`FIERA`,`DATA`,`ASSEGNAZIONE`,`TIPOATTIVITA`,`GRADUATORIA`,`CODICE`,`TIPOAUTORIZZAZIONE`,`NUMERO`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `POSTIASSM` (
  `ROWID` int(11) NOT NULL AUTO_INCREMENT,
  `FIERA` varchar(5) COLLATE latin1_general_cs NOT NULL,
  `DATA` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `ASSEGNAZIONE` smallint(6) DEFAULT NULL,
  `TIPOATTIVITA` varchar(5) COLLATE latin1_general_cs NOT NULL,
  `GRADUATORIA` smallint(6) NOT NULL,
  `CODICE` double DEFAULT NULL,
  `TIPOAUTORIZZAZIONE` varchar(3) COLLATE latin1_general_cs NOT NULL,
  `NUMERO` varchar(20) COLLATE latin1_general_cs NOT NULL,
  `POSTO` double DEFAULT NULL,
  `CODICEVIA` varchar(6) COLLATE latin1_general_cs DEFAULT NULL,
  `TIPOORDINE` varchar(10) COLLATE latin1_general_cs NOT NULL,
  `DENOMINAZIONE` varchar(100) COLLATE latin1_general_cs NOT NULL,
  `KW` double DEFAULT NULL,
  `IMPORTO` double DEFAULT NULL,
  `RIFERIMENTO` varchar(20) COLLATE latin1_general_cs NOT NULL,
  `CONFERMATO` varchar(2) COLLATE latin1_general_cs NOT NULL,
  `PRESENZE` double NOT NULL,
  `DATADOMANDA` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `IDONEO` varchar(2) COLLATE latin1_general_cs NOT NULL,
  `TIPOITER` smallint(6) DEFAULT NULL,
  `DOCUMENTO1` varchar(50) COLLATE latin1_general_cs NOT NULL,
  `DOCUMENTO2` varchar(50) COLLATE latin1_general_cs NOT NULL,
  `DOCUMENTO3` varchar(50) COLLATE latin1_general_cs NOT NULL,
  `DOCUMENTO4` varchar(50) COLLATE latin1_general_cs NOT NULL,
  `DOCUMENTO5` varchar(50) COLLATE latin1_general_cs NOT NULL,
  `TIPO1` varchar(1) COLLATE latin1_general_cs NOT NULL,
  `TIPO2` varchar(1) COLLATE latin1_general_cs NOT NULL,
  `TIPO3` varchar(1) COLLATE latin1_general_cs NOT NULL,
  `TIPO4` varchar(1) COLLATE latin1_general_cs NOT NULL,
  `TIPO5` varchar(1) COLLATE latin1_general_cs NOT NULL,
  `DATADOC1` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `DATADOC2` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `DATADOC3` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `DATADOC4` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `DATADOC5` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `CONSEGNATO` smallint(6) DEFAULT NULL,
  `DATACONSEGNA` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `AGGIORNATO` tinyint(4) DEFAULT NULL,
  `COMUNICAZIONEFIERA` text COLLATE latin1_general_cs,
  `DOMANDADEC` tinyint(4) DEFAULT NULL,
  `ENTE` smallint(6) DEFAULT NULL,
  `TASSA` double DEFAULT NULL,
  `IVA` double DEFAULT NULL,
  `TOTALE` double DEFAULT NULL,
  `FATTURA` varchar(20) COLLATE latin1_general_cs NOT NULL,
  `PAGATO` tinyint(4) DEFAULT NULL,
  `DATAPAGAMENTO` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `CODICE_1` double DEFAULT NULL,
  `TIPOAUTORIZZAZIONE_1` varchar(3) COLLATE latin1_general_cs NOT NULL,
  `NUMERO_1` varchar(20) COLLATE latin1_general_cs NOT NULL,
  `ISTAT` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `UTILIZZAPERFIERE` varchar(1) COLLATE latin1_general_cs NOT NULL,
  `DATARILASCIO` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `DATARILASCIOLICENZA` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `DATAISCRIZIONEREGD` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `DATAISCRLICENZA` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `DATAINIZIOATTIVITA` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `DATAINIZIOATTIVITALIC` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `NUMEROISCRREGD` varchar(20) COLLATE latin1_general_cs NOT NULL,
  `CODICECOMUNE` smallint(6) DEFAULT NULL,
  `COMUNE` varchar(50) COLLATE latin1_general_cs NOT NULL,
  `CODICEREGIONE` varchar(3) COLLATE latin1_general_cs NOT NULL,
  `REGIONE` varchar(30) COLLATE latin1_general_cs NOT NULL,
  `NUMEROCONCESSIONE` varchar(20) COLLATE latin1_general_cs NOT NULL,
  `DATACONCESSIONE` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `CODICEAUTORITA` smallint(6) DEFAULT NULL,
  `AUTORITA` varchar(50) COLLATE latin1_general_cs NOT NULL,
  `PROPRIETARIO_I` varchar(100) COLLATE latin1_general_cs NOT NULL,
  `PROPRIETARIO_II` varchar(100) COLLATE latin1_general_cs NOT NULL,
  `PROPRIETARIO_III` varchar(100) COLLATE latin1_general_cs NOT NULL,
  `PROPRIETARIO_IV` varchar(100) COLLATE latin1_general_cs NOT NULL,
  `RD_I` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `RD_II` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `RD_III` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `RD_IV` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `DRIL_I` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `DRIL_II` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `DRIL_III` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `DRIL_IV` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `DIA_I` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `DIA_II` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `DIA_III` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `DIA_IV` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `NUMAUT_I` varchar(10) COLLATE latin1_general_cs NOT NULL,
  `NUMAUT_II` varchar(10) COLLATE latin1_general_cs NOT NULL,
  `NUMAUT_III` varchar(10) COLLATE latin1_general_cs NOT NULL,
  `NUMAUT_IV` varchar(10) COLLATE latin1_general_cs NOT NULL,
  `SETTOREMERCEOLOGICO` varchar(30) COLLATE latin1_general_cs NOT NULL,
  `ALIMENTIBEVANDE` varchar(2) COLLATE latin1_general_cs NOT NULL,
  `MERCATO` varchar(5) COLLATE latin1_general_cs NOT NULL,
  `FIERA_1` varchar(5) COLLATE latin1_general_cs NOT NULL,
  `TIPOORDINE_1` varchar(10) COLLATE latin1_general_cs NOT NULL,
  `POSTO_1` double DEFAULT NULL,
  `CODICEVIA_1` smallint(6) DEFAULT NULL,
  `DATADOMANDADEC` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `DATAVALIDITA` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `NOTE` varchar(200) COLLATE latin1_general_cs NOT NULL,
  `NONATTIVA` tinyint(4) DEFAULT NULL,
  `TIPOI` varchar(1) COLLATE latin1_general_cs NOT NULL,
  `TIPOII` varchar(1) COLLATE latin1_general_cs NOT NULL,
  `TIPOIII` varchar(1) COLLATE latin1_general_cs NOT NULL,
  `TIPOIV` varchar(1) COLLATE latin1_general_cs NOT NULL,
  `NOTE1` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `NUMEROPRESENZE` double DEFAULT NULL,
  `DECENNALE` tinyint(4) NOT NULL,
  `NUMEROSPUNTA` double DEFAULT NULL,
  `VALIDAPERFIERE` varchar(100) COLLATE latin1_general_cs NOT NULL,
  `PROTOCOLLO` varchar(10) COLLATE latin1_general_cs NOT NULL,
  `DATAPROTOCOLLO` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `TIPONS` varchar(20) COLLATE latin1_general_cs NOT NULL,
  `PROTGRADUATORIA` varchar(30) COLLATE latin1_general_cs NOT NULL,
  `PROTGRADDATA` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `CCIAA` varchar(30) COLLATE latin1_general_cs NOT NULL,
  `DATABOLLO` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `NUMEROBOLLO` text COLLATE latin1_general_cs NOT NULL,
  `BADGE` varchar(20) COLLATE latin1_general_cs NOT NULL,
  `DELIBERA` varchar(20) COLLATE latin1_general_cs NOT NULL,
  `DATADELIBERA` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `TIPOLICENZA` varchar(1) COLLATE latin1_general_cs NOT NULL COMMENT 'Opzioni: N = Nuova Licenza, S = Subingresso, C = Conversione',
  `ORACONVOCAZIONE` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `DATACONVOCAZIONE` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `DATACESSAZIONE` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `IDCOMLIC` int(11) NOT NULL,
  `FASCICOLO` varchar(10) COLLATE latin1_general_cs NOT NULL,
  `PREFERENZASPUNTA` tinyint(4) NOT NULL,
  `PEC` varchar(50) COLLATE latin1_general_cs NOT NULL,
  `SETTORE` varchar(30) COLLATE latin1_general_cs NOT NULL,
  `ORAPEC` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `IDPEC` varchar(100) COLLATE latin1_general_cs NOT NULL,
  `ROWID_PARENT` int(11) NOT NULL COMMENT 'rowid domanda unica',
  `DATTO_I` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `DATTO_II` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `DATTO_III` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `DATTO_IV` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `CODICEPROP_I` int(11) NOT NULL,
  `CODICEPROP_II` int(11) NOT NULL,
  `CODICEPROP_III` int(11) NOT NULL,
  `CODICEPROP_IV` int(11) NOT NULL,
  `EVENTOATTIVAZIONE` varchar(5) COLLATE latin1_general_cs NOT NULL,
  `EVENTOCESSAZIONE` varchar(5) COLLATE latin1_general_cs NOT NULL,
  `LETTERA` varchar(5) COLLATE latin1_general_cs NOT NULL,
  `DATABOLLOLIC` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `NUMEROBOLLOLIC` text COLLATE latin1_general_cs NOT NULL,
  `PUNTEGGIO1` int(11) NOT NULL,
  `PUNTEGGIO2` int(11) NOT NULL,
  `PUNTEGGIO3` int(11) NOT NULL,
  `PUNTEGGIO4` int(11) NOT NULL,
  `PUNTEGGIOTOTALE` int(11) NOT NULL,
  `DATACOMUNICAZIONE` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `PROTCOMUNICAZIONE` varchar(8) COLLATE latin1_general_cs NOT NULL,
  PRIMARY KEY (`ROWID`),
  UNIQUE KEY `UNIQ_POSTIASS` (`FIERA`,`DATA`,`ASSEGNAZIONE`,`TIPOATTIVITA`,`GRADUATORIA`,`CODICE`,`TIPOAUTORIZZAZIONE`,`NUMERO`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `PROGRESSIVI` (
  `ROWID` int(11) NOT NULL AUTO_INCREMENT,
  `CHIAVE` varchar(50) COLLATE latin1_general_cs NOT NULL,
  `PROGRESSIVO` int(11) NOT NULL,
  PRIMARY KEY (`ROWID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `RACCOMANDATE` (
  `ROWID` int(11) NOT NULL AUTO_INCREMENT,
  `IDRECORD` int(11) NOT NULL,
  `IDRICHIESTA` text COLLATE latin1_general_cs NOT NULL,
  `IDXOL` int(11) NOT NULL,
  `TIPOXOL` varchar(3) COLLATE latin1_general_cs NOT NULL,
  `TABORIG` varchar(20) COLLATE latin1_general_cs NOT NULL,
  `AMBITO` varchar(50) COLLATE latin1_general_cs NOT NULL,
  `DATA` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `MITTENTE` varchar(50) COLLATE latin1_general_cs NOT NULL,
  `PREZZO` double NOT NULL,
  PRIMARY KEY (`ROWID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `STORICOBADGE` (
  `ROWID` int(11) NOT NULL AUTO_INCREMENT,
  `NUMEROBADGE` varchar(20) COLLATE latin1_general_cs NOT NULL,
  `INIZIOVALIDITA` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `FINEVALIDITA` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `CODICEDITTA` double NOT NULL,
  `DENOMINAZIONE` varchar(100) COLLATE latin1_general_cs NOT NULL,
  `TIPOAUTORIZZAZIONE` varchar(3) COLLATE latin1_general_cs NOT NULL,
  `NUMEROLICENZA` varchar(20) COLLATE latin1_general_cs NOT NULL,
  `MERCATO` varchar(5) COLLATE latin1_general_cs NOT NULL,
  `POSTO` double NOT NULL,
  PRIMARY KEY (`ROWID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `TABORDFI` (
  `ROWID` int(11) NOT NULL AUTO_INCREMENT,
  `CAMPODAORDINARE` varchar(25) COLLATE latin1_general_cs NOT NULL,
  `TIPOORDINE` varchar(1) COLLATE latin1_general_cs NOT NULL,
  PRIMARY KEY (`ROWID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `TABORDME` (
  `ROWID` int(11) NOT NULL AUTO_INCREMENT,
  `CAMPODAORDINARE` varchar(25) COLLATE latin1_general_cs NOT NULL,
  `TIPOORDINE` varchar(1) COLLATE latin1_general_cs NOT NULL,
  PRIMARY KEY (`ROWID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `TIPOATTI` (
  `ROWID` int(11) NOT NULL AUTO_INCREMENT,
  `TIPO` varchar(5) COLLATE latin1_general_cs NOT NULL,
  `ATTIVITA` varchar(50) COLLATE latin1_general_cs NOT NULL,
  `ENTE` smallint(6) DEFAULT NULL,
  `COLORE` varchar(20) COLLATE latin1_general_cs NOT NULL,
  `DISABILITASPUNTA` tinyint(4) NOT NULL,
  PRIMARY KEY (`ROWID`),
  UNIQUE KEY `UNIQ_TIPOATTI` (`TIPO`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `TIPOAUTORITA` (
  `ROWID` int(11) NOT NULL AUTO_INCREMENT,
  `CODICE` smallint(6) DEFAULT NULL,
  `AUTORITA` varchar(50) COLLATE latin1_general_cs NOT NULL,
  `ENTE` smallint(6) DEFAULT NULL,
  PRIMARY KEY (`ROWID`),
  UNIQUE KEY `UNIQ_TIPOAUTORITA` (`CODICE`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `TIPOAUTR` (
  `ROWID` int(11) NOT NULL AUTO_INCREMENT,
  `TIPO` varchar(3) COLLATE latin1_general_cs NOT NULL,
  `AUTORIZZAZIONE` varchar(100) COLLATE latin1_general_cs NOT NULL,
  `ENTE` smallint(6) DEFAULT NULL,
  PRIMARY KEY (`ROWID`),
  UNIQUE KEY `UNIQ_TIPOAUTR` (`TIPO`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `TIPOCOMU` (
  `ROWID` int(11) NOT NULL AUTO_INCREMENT,
  `COMUNICAZIONE` varchar(200) COLLATE latin1_general_cs NOT NULL,
  `ENTE` smallint(6) DEFAULT NULL,
  PRIMARY KEY (`ROWID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `TIPOCONC` (
  `ROWID` int(11) NOT NULL AUTO_INCREMENT,
  `CODICECONCESSIONE` varchar(5) COLLATE latin1_general_cs NOT NULL,
  `DESCRIZIONECONCESSIONE` varchar(40) COLLATE latin1_general_cs NOT NULL,
  `IMPORTOCONCESSIONE` double DEFAULT NULL,
  `ENTE` smallint(6) DEFAULT NULL,
  PRIMARY KEY (`ROWID`),
  UNIQUE KEY `UNIQ_TIPOCONC` (`CODICECONCESSIONE`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `TIPOCOSAP` (
  `ROWID` int(11) NOT NULL AUTO_INCREMENT,
  `TIPOTASSA` varchar(5) COLLATE latin1_general_cs NOT NULL,
  `DESCRIZIONETASSA` varchar(100) COLLATE latin1_general_cs NOT NULL,
  `IMPORTOTASSA` double DEFAULT NULL,
  `ENTE` smallint(6) DEFAULT NULL,
  `COD_IVA` varchar(5) COLLATE latin1_general_cs NOT NULL,
  PRIMARY KEY (`ROWID`),
  UNIQUE KEY `UNIQ_TIPOCOSAP` (`TIPOTASSA`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `TIPOFIER` (
  `ROWID` int(11) NOT NULL AUTO_INCREMENT,
  `TIPO` varchar(5) COLLATE latin1_general_cs NOT NULL,
  `FIERA` varchar(50) COLLATE latin1_general_cs NOT NULL,
  `ENTE` smallint(6) DEFAULT NULL,
  `PERIODICITA` varchar(15) COLLATE latin1_general_cs NOT NULL,
  `GIORNO` smallint(6) DEFAULT NULL,
  `MESE` smallint(6) DEFAULT NULL,
  `NUMEROPOSTI` smallint(6) DEFAULT NULL,
  `NUMEROGIORNI` smallint(6) DEFAULT NULL,
  `TIPOCONCESSIONE` varchar(5) COLLATE latin1_general_cs NOT NULL,
  `NOTE` varchar(50) COLLATE latin1_general_cs NOT NULL,
  PRIMARY KEY (`ROWID`),
  UNIQUE KEY `UNIQ_TIPOFIER` (`TIPO`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `TIPOITER` (
  `ROWID` int(11) NOT NULL AUTO_INCREMENT,
  `TIPOITER` smallint(6) DEFAULT NULL,
  `DESCRIZIONE` varchar(50) COLLATE latin1_general_cs NOT NULL,
  `DOCUMENTO1` varchar(50) COLLATE latin1_general_cs NOT NULL,
  `TIPO1` varchar(1) COLLATE latin1_general_cs NOT NULL,
  `DOCUMENTO2` varchar(50) COLLATE latin1_general_cs NOT NULL,
  `TIPO2` varchar(1) COLLATE latin1_general_cs NOT NULL,
  `DOCUMENTO3` varchar(50) COLLATE latin1_general_cs NOT NULL,
  `TIPO3` varchar(1) COLLATE latin1_general_cs NOT NULL,
  `DOCUMENTO4` varchar(50) COLLATE latin1_general_cs NOT NULL,
  `TIPO4` varchar(1) COLLATE latin1_general_cs NOT NULL,
  `DOCUMENTO5` varchar(50) COLLATE latin1_general_cs NOT NULL,
  `TIPO5` varchar(1) COLLATE latin1_general_cs NOT NULL,
  `ENTE` smallint(6) DEFAULT NULL,
  `TIPO6` varchar(1) COLLATE latin1_general_cs NOT NULL,
  `DOCUMENTO6` varchar(50) COLLATE latin1_general_cs NOT NULL,
  `TIPO7` varchar(1) COLLATE latin1_general_cs NOT NULL,
  `DOCUMENTO7` varchar(50) COLLATE latin1_general_cs NOT NULL,
  `TIPO8` varchar(1) COLLATE latin1_general_cs NOT NULL,
  `DOCUMENTO8` varchar(50) COLLATE latin1_general_cs NOT NULL,
  `TIPO9` varchar(1) COLLATE latin1_general_cs NOT NULL,
  `DOCUMENTO9` varchar(50) COLLATE latin1_general_cs NOT NULL,
  `TIPO10` varchar(1) COLLATE latin1_general_cs NOT NULL,
  `DOCUMENTO10` varchar(50) COLLATE latin1_general_cs NOT NULL,
  PRIMARY KEY (`ROWID`),
  UNIQUE KEY `UNIQ_TIPOITER` (`TIPOITER`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `TIPOMERC` (
  `ROWID` int(11) NOT NULL AUTO_INCREMENT,
  `TIPOMERCATO` varchar(20) COLLATE latin1_general_cs NOT NULL,
  `COMPILASEQUENZA` varchar(1) COLLATE latin1_general_cs NOT NULL,
  `ENTE` smallint(6) DEFAULT NULL,
  PRIMARY KEY (`ROWID`),
  UNIQUE KEY `UNIQ_TIPOMERC` (`TIPOMERCATO`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `TIPOMOD` (
  `ROWID` int(11) NOT NULL AUTO_INCREMENT,
  `CODICEDOCUMENTO` varchar(5) COLLATE latin1_general_cs NOT NULL,
  `TIPODOCUMENTO` varchar(50) COLLATE latin1_general_cs NOT NULL,
  `FILE` varchar(100) COLLATE latin1_general_cs NOT NULL,
  `ENTE` smallint(6) DEFAULT NULL,
  PRIMARY KEY (`ROWID`),
  UNIQUE KEY `UNIQ_TIPOMOD` (`CODICEDOCUMENTO`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `TIPOPOST` (
  `ROWID` int(11) NOT NULL AUTO_INCREMENT,
  `TIPO` smallint(6) DEFAULT NULL,
  `TIPOPOSTO` varchar(100) COLLATE latin1_general_cs NOT NULL,
  `ENTE` smallint(6) DEFAULT NULL,
  `COLORE` varchar(20) COLLATE latin1_general_cs NOT NULL,
  PRIMARY KEY (`ROWID`),
  UNIQUE KEY `UNIQ_TIPOPOST` (`TIPO`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `TIPOPRES` (
  `ROWID` int(11) NOT NULL AUTO_INCREMENT,
  `CODICEPRESENZA` varchar(5) COLLATE latin1_general_cs NOT NULL,
  `DESCRIZIONEPRESENZA` varchar(40) COLLATE latin1_general_cs NOT NULL,
  `TIPOLOGIAPRESENZA` varchar(2) COLLATE latin1_general_cs NOT NULL,
  `NUMEROPRESENZE` int(11) DEFAULT NULL,
  `NUMEROASSENZE` int(11) DEFAULT NULL,
  `NUMEROSPUNTA` int(11) DEFAULT NULL,
  `VALORE` smallint(6) DEFAULT NULL,
  `TIPO` varchar(1) COLLATE latin1_general_cs NOT NULL,
  `ENTE` smallint(6) DEFAULT NULL,
  PRIMARY KEY (`ROWID`),
  UNIQUE KEY `UNIQ_TIPOPRES` (`CODICEPRESENZA`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `TIPOREGIONE` (
  `ROWID` int(11) NOT NULL AUTO_INCREMENT,
  `CODICE` varchar(3) COLLATE latin1_general_cs NOT NULL,
  `REGIONE` varchar(30) COLLATE latin1_general_cs NOT NULL,
  `ENTE` smallint(6) DEFAULT NULL,
  PRIMARY KEY (`ROWID`),
  UNIQUE KEY `UNIQ_TIPOREGIONE` (`CODICE`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `TIPOSEME` (
  `ROWID` int(11) NOT NULL AUTO_INCREMENT,
  `TIPOSETTORE` varchar(5) COLLATE latin1_general_cs NOT NULL,
  `SETTOREMERCEOLOGICO` varchar(100) COLLATE latin1_general_cs NOT NULL,
  `ENTE` smallint(6) DEFAULT NULL,
  PRIMARY KEY (`ROWID`),
  UNIQUE KEY `UNIQ_TIPOSEME` (`TIPOSETTORE`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `W_TAB_IMPORTDAOROLOGI` (
  `ROWID` int(11) NOT NULL AUTO_INCREMENT,
  `TOKEN` varchar(50) COLLATE latin1_general_cs NOT NULL,
  `UTENTE` varchar(20) COLLATE latin1_general_cs NOT NULL,
  `DATA_CRE` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `ORA_CRE` varchar(4) COLLATE latin1_general_cs NOT NULL,
  `CODICE` varchar(10) COLLATE latin1_general_cs NOT NULL,
  `DENOMINAZIONE` varchar(100) COLLATE latin1_general_cs NOT NULL,
  `DATA` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `ORA` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `FLG_LETT` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `BADGE` float NOT NULL,
  PRIMARY KEY (`ROWID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
