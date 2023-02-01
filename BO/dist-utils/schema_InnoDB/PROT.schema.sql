/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `AACVERS` (
  `ROWID` int(11) NOT NULL AUTO_INCREMENT,
  `VERSIONE_T` int(11) NOT NULL COMMENT 'N.VERSIONE',
  `DESCRI` varchar(30) COLLATE latin1_general_cs NOT NULL,
  `DESCRI_B` varchar(12) COLLATE latin1_general_cs NOT NULL,
  `DATAINIZ` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `DATAFINE` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `FLAG_DIS` smallint(6) NOT NULL,
  PRIMARY KEY (`ROWID`),
  UNIQUE KEY `I_AACVERS_VERSIONE_T` (`VERSIONE_T`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ABILITAPROT` (
  `ROWID` int(11) NOT NULL AUTO_INCREMENT,
  `CODICE` varchar(30) COLLATE latin1_general_cs NOT NULL COMMENT 'CODICE DIV DA GESTIRE',
  `SEZIONE` varchar(30) COLLATE latin1_general_cs NOT NULL COMMENT 'SEZIONE DEL PROTOCOLLO DA GESTIRE',
  `MODIFICA` int(11) NOT NULL COMMENT 'MODIFICA',
  `INSERISCI` int(11) NOT NULL COMMENT 'INSERISCI',
  `ELIMINA` int(11) NOT NULL COMMENT 'ELIMINA',
  PRIMARY KEY (`ROWID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ACCRIS` (
  `ACCNUM` double NOT NULL,
  `ACCPAR` varchar(2) COLLATE latin1_general_cs NOT NULL,
  `ACCDES` varchar(6) COLLATE latin1_general_cs NOT NULL,
  `ACCDAINI` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `ACCDAFIN` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `ACCDAREV` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `ACCDESINS` varchar(6) COLLATE latin1_general_cs NOT NULL,
  `ACCDATAINS` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `ACCDATAMOD` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `ACCNOTE` text COLLATE latin1_general_cs NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ALBDOC` (
  `ROWID` int(11) NOT NULL AUTO_INCREMENT,
  `ALBANN` varchar(4) COLLATE latin1_general_cs NOT NULL,
  `ALBNUM` varchar(6) COLLATE latin1_general_cs NOT NULL,
  `ALBIND` varchar(18) COLLATE latin1_general_cs NOT NULL,
  `DOCFIL` text COLLATE latin1_general_cs NOT NULL,
  `DOCFDT` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `DOCFTM` varchar(6) COLLATE latin1_general_cs NOT NULL,
  `DOCLNK` varchar(100) COLLATE latin1_general_cs NOT NULL,
  `DOCNOT` text COLLATE latin1_general_cs NOT NULL,
  `DOCTIPO` varchar(30) COLLATE latin1_general_cs NOT NULL COMMENT 'TIPO DEL DOCUMENTO: DOCUMENTO PRINCIPALE O ALLEGATO O CERTIFICATO',
  `DOCNAME` text COLLATE latin1_general_cs NOT NULL,
  `DOCMD5` text COLLATE latin1_general_cs NOT NULL COMMENT 'MD5 DEL CONTENUTO DEL FILE',
  `DOCRELEASE` varchar(20) COLLATE latin1_general_cs NOT NULL COMMENT 'LABEL DI VERISIONE',
  `DOCEVI` double NOT NULL,
  `DOCLOCK` double NOT NULL,
  `DOCNOTE` text COLLATE latin1_general_cs NOT NULL,
  `DOCMETA` text COLLATE latin1_general_cs NOT NULL COMMENT 'METADATI',
  `DOCROWIDBASE` int(11) NOT NULL,
  `DOCSUBTIPO` varchar(10) COLLATE latin1_general_cs NOT NULL,
  `DOCSHA2` varchar(64) COLLATE latin1_general_cs NOT NULL,
  `DOCROWIDEXPORT` int(11) NOT NULL,
  PRIMARY KEY (`ROWID`),
  KEY `I_ALBKEY` (`ALBANN`,`ALBNUM`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ANAATTI` (
  `ROWID` int(11) NOT NULL AUTO_INCREMENT,
  `CODATTO` int(11) NOT NULL COMMENT 'Codice',
  `ATTO` varchar(100) COLLATE latin1_general_cs NOT NULL COMMENT 'Descrizione',
  PRIMARY KEY (`ROWID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs COMMENT='Anagrafica atti';
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ANACAT` (
  `ROWID` int(11) NOT NULL AUTO_INCREMENT,
  `CATCOD` varchar(4) COLLATE latin1_general_cs NOT NULL,
  `CATDES` varchar(100) COLLATE latin1_general_cs NOT NULL,
  `CATDAT` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `CATATTGIUD` smallint(6) NOT NULL COMMENT 'FLAG ATTO GIUDIZIARIO',
  `CATRIS` smallint(6) NOT NULL COMMENT 'FLAG RISERVATO',
  `VERSIONE_T` int(11) NOT NULL COMMENT 'Versione Categoria',
  `CATCOD_SUCC` varchar(4) COLLATE latin1_general_cs NOT NULL COMMENT 'CODICE CATEGORIA TITOLARIO SUCCESSIVO',
  `VERSIONE_SUCC` int(11) NOT NULL COMMENT 'VERSIONE TITOLARIO SUCCESSIVO',
  `NUMROMANA` varchar(15) COLLATE latin1_general_cs NOT NULL COMMENT 'CODIFICA IN NUM. ROMANI',
  PRIMARY KEY (`ROWID`),
  KEY `A_CATCOD` (`CATCOD`),
  KEY `I_CATCOD` (`CATDAT`,`CATCOD`),
  KEY `I_CATDAT` (`CATDAT`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ANACLA` (
  `ROWID` int(11) NOT NULL AUTO_INCREMENT,
  `CLACOD` varchar(4) COLLATE latin1_general_cs NOT NULL,
  `CLADE1` varchar(100) COLLATE latin1_general_cs NOT NULL,
  `CLADE2` varchar(100) COLLATE latin1_general_cs NOT NULL,
  `CLACAT` varchar(4) COLLATE latin1_general_cs NOT NULL,
  `CLACCA` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `CLADAT` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `CLAATTGIUD` smallint(6) NOT NULL COMMENT 'FLAG ATTO GIUDIZIARIO',
  `CLARIS` smallint(6) NOT NULL COMMENT 'FLAG RISERVATO',
  `VERSIONE_T` int(11) NOT NULL COMMENT 'VERSIONE CLASSE',
  `CLACCA_SUCC` varchar(8) COLLATE latin1_general_cs NOT NULL COMMENT 'CODICE CATEGORIA-CLASSE SUCCESSIVO',
  `VERSIONE_SUCC` int(11) NOT NULL COMMENT 'VERSIONE TITOLARIO SUCCESSIVO',
  `NUMROMANA` varchar(15) COLLATE latin1_general_cs NOT NULL COMMENT 'CODIFICA IN NUM. ROMANI',
  PRIMARY KEY (`ROWID`),
  KEY `A_CLACOD` (`CLACOD`),
  KEY `A_CLACAT` (`CLACAT`),
  KEY `A_CLACCA` (`CLACCA`),
  KEY `I_CLACCA` (`CLADAT`,`CLACCA`),
  KEY `I_CLADAT` (`CLADAT`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ANADES` (
  `ROWID` int(11) NOT NULL AUTO_INCREMENT,
  `DESNUM` double NOT NULL,
  `DESPAR` varchar(2) COLLATE latin1_general_cs NOT NULL,
  `DESCOD` varchar(6) COLLATE latin1_general_cs NOT NULL,
  `DESNOM` varchar(300) COLLATE latin1_general_cs NOT NULL,
  `DESIND` varchar(300) COLLATE latin1_general_cs NOT NULL,
  `DESCAP` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `DESCIT` varchar(40) COLLATE latin1_general_cs NOT NULL,
  `DESPRO` varchar(4) COLLATE latin1_general_cs NOT NULL,
  `DESDAT` double NOT NULL,
  `DESDUF` double NOT NULL,
  `DESANN` varchar(36) COLLATE latin1_general_cs NOT NULL,
  `DESDAA` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `DESDSC` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `DESSET` varchar(6) COLLATE latin1_general_cs NOT NULL,
  `DESSER` varchar(30) COLLATE latin1_general_cs NOT NULL,
  `DESUOP` varchar(6) COLLATE latin1_general_cs NOT NULL,
  `DESDLE` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `DESDIN` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `DESFLA` varchar(2) COLLATE latin1_general_cs NOT NULL,
  `DESCUF` varchar(50) COLLATE latin1_general_cs NOT NULL,
  `DESGES` varchar(2) COLLATE latin1_general_cs NOT NULL,
  `DESRES` varchar(10) COLLATE latin1_general_cs NOT NULL COMMENT 'CHECK RESPONSABILE',
  `DESORIGINALE` varchar(10) COLLATE latin1_general_cs NOT NULL COMMENT 'DOCUMENTO ORIGINALE',
  `DESMAIL` text COLLATE latin1_general_cs NOT NULL COMMENT 'EMAIL DESTINATARI',
  `DESTIPO` varchar(2) COLLATE latin1_general_cs NOT NULL COMMENT 'M=MITTENTE;D=DESTINATARIO',
  `DESRUOLO` varchar(30) COLLATE latin1_general_cs NOT NULL COMMENT 'RUOLO',
  `DESTERMINE` varchar(8) COLLATE latin1_general_cs NOT NULL COMMENT 'TERMINA INIZIALE PER SOGGETTO',
  `DESIDMAIL` text COLLATE latin1_general_cs NOT NULL COMMENT 'IDMAIL NOTIFICA',
  `DESCONOSCENZA` smallint(6) NOT NULL,
  `DESMIGRA` varchar(20) COLLATE latin1_general_cs NOT NULL,
  `DESTSP` varchar(4) COLLATE latin1_general_cs NOT NULL COMMENT 'Tipo di spedizione',
  `DESINV` tinyint(4) NOT NULL COMMENT 'Flag per indicare se deve essere proposto per inviare mail destinatari interni',
  `DESFIS` varchar(16) COLLATE latin1_general_cs NOT NULL,
  `DESNRAC` varchar(20) COLLATE latin1_general_cs NOT NULL COMMENT 'NUMERO RACCOMANDATA',
  `DESRUO_EXT` varchar(4) COLLATE latin1_general_cs NOT NULL COMMENT 'RUOLI PER SOGGETTI ESTERNI',
  PRIMARY KEY (`ROWID`),
  KEY `A_DESNUM` (`DESNUM`),
  KEY `A_DESCOD` (`DESCOD`),
  KEY `I_DATUFF` (`DESCOD`,`DESDAA`),
  KEY `I_DATNOM` (`DESDAA`,`DESNOM`),
  KEY `I_DESDSC` (`DESPAR`,`DESSET`,`DESDSC`),
  KEY `I_DESDLE` (`DESCOD`,`DESDLE`),
  KEY `I_DESDIN` (`DESCOD`,`DESDIN`),
  KEY `I_DESPAR` (`DESNUM`,`DESPAR`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ANADESSAVE` (
  `ROWID` int(11) NOT NULL AUTO_INCREMENT,
  `DESNUM` double NOT NULL,
  `DESPAR` varchar(2) COLLATE latin1_general_cs NOT NULL,
  `DESCOD` varchar(6) COLLATE latin1_general_cs NOT NULL,
  `DESNOM` varchar(100) COLLATE latin1_general_cs NOT NULL,
  `DESIND` varchar(300) COLLATE latin1_general_cs NOT NULL,
  `DESCAP` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `DESCIT` varchar(40) COLLATE latin1_general_cs NOT NULL,
  `DESPRO` varchar(4) COLLATE latin1_general_cs NOT NULL,
  `DESDAT` double NOT NULL,
  `DESDUF` double NOT NULL,
  `DESANN` varchar(36) COLLATE latin1_general_cs NOT NULL,
  `DESDAA` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `DESDSC` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `DESSET` varchar(6) COLLATE latin1_general_cs NOT NULL,
  `DESSER` varchar(30) COLLATE latin1_general_cs NOT NULL,
  `DESUOP` varchar(6) COLLATE latin1_general_cs NOT NULL,
  `DESDLE` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `DESDIN` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `DESFLA` varchar(2) COLLATE latin1_general_cs NOT NULL,
  `DESCUF` varchar(50) COLLATE latin1_general_cs NOT NULL,
  `DESGES` varchar(2) COLLATE latin1_general_cs NOT NULL,
  `DESRES` varchar(10) COLLATE latin1_general_cs NOT NULL COMMENT 'CHECK RESPONSABILE',
  `SAVEDATA` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `SAVEORA` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `SAVEUTENTE` varchar(20) COLLATE latin1_general_cs NOT NULL,
  `DESORIGINALE` varchar(10) COLLATE latin1_general_cs NOT NULL,
  `DESMAIL` text COLLATE latin1_general_cs NOT NULL COMMENT 'EMAIL DESTINATARI',
  `DESTIPO` varchar(2) COLLATE latin1_general_cs NOT NULL,
  `DESRUOLO` varchar(30) COLLATE latin1_general_cs NOT NULL COMMENT 'RUOLO',
  `DESTERMINE` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `DESIDMAIL` text COLLATE latin1_general_cs NOT NULL COMMENT 'IDMAIL NOTIFICA',
  `DESCONOSCENZA` smallint(6) NOT NULL,
  `DESMIGRA` varchar(20) COLLATE latin1_general_cs NOT NULL,
  `DESTSP` varchar(4) COLLATE latin1_general_cs NOT NULL COMMENT 'Tipo di spedizione',
  `DESINV` tinyint(4) NOT NULL COMMENT 'Flag per indicare se deve essere proposto per inviare mail destinatari interni',
  `DESFIS` varchar(16) COLLATE latin1_general_cs NOT NULL,
  `DESNRAC` varchar(20) COLLATE latin1_general_cs NOT NULL COMMENT 'NUMERO RACCOMANDATA',
  `DESRUO_EXT` varchar(4) COLLATE latin1_general_cs NOT NULL COMMENT 'RUOLI PER SOGGETTI ESTERNI',
  PRIMARY KEY (`ROWID`),
  KEY `A_DESNUM` (`DESNUM`),
  KEY `A_DESCOD` (`DESCOD`),
  KEY `I_DATUFF` (`DESCOD`,`DESDAA`),
  KEY `I_DATNOM` (`DESDAA`,`DESNOM`),
  KEY `I_DESDSC` (`DESPAR`,`DESSET`,`DESDSC`),
  KEY `I_DESDLE` (`DESCOD`,`DESDLE`),
  KEY `I_DESDIN` (`DESCOD`,`DESDIN`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ANADIR` (
  `ROWID` int(11) NOT NULL AUTO_INCREMENT,
  `CODICE` int(11) NOT NULL COMMENT 'Codice',
  `DIRITTI` varchar(100) COLLATE latin1_general_cs NOT NULL COMMENT 'Diritti',
  `IMPORTO` double NOT NULL COMMENT 'Importo',
  PRIMARY KEY (`ROWID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs COMMENT='Anagrafica Diritti';
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ANADOC` (
  `ROWID` int(11) NOT NULL AUTO_INCREMENT,
  `DOCKEY` varchar(30) COLLATE latin1_general_cs NOT NULL,
  `DOCNUM` double NOT NULL,
  `DOCPAR` varchar(2) COLLATE latin1_general_cs NOT NULL,
  `DOCFIL` varchar(50) COLLATE latin1_general_cs NOT NULL,
  `DOCSTA` varchar(100) COLLATE latin1_general_cs NOT NULL,
  `DOCORF` varchar(100) COLLATE latin1_general_cs NOT NULL,
  `DOCFDT` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `DOCFTM` varchar(6) COLLATE latin1_general_cs NOT NULL,
  `DOCLNK` varchar(100) COLLATE latin1_general_cs NOT NULL,
  `DOCNOT` text COLLATE latin1_general_cs NOT NULL,
  `DOCCLA` varchar(50) COLLATE latin1_general_cs NOT NULL,
  `DOCUTC` varchar(6) COLLATE latin1_general_cs NOT NULL,
  `DOCUTE` varchar(60) COLLATE latin1_general_cs NOT NULL,
  `DOCSTATO` varchar(100) COLLATE latin1_general_cs NOT NULL,
  `DOCTIPO` varchar(30) COLLATE latin1_general_cs NOT NULL COMMENT 'TIPO DEL DOCUMENTO: DOCUMENTO PRINCIPALE O ALLEGATO',
  `DOCLOG` text COLLATE latin1_general_cs NOT NULL,
  `DOCNAME` text COLLATE latin1_general_cs NOT NULL,
  `DOCMD5` text COLLATE latin1_general_cs NOT NULL COMMENT 'MD5 DEL CONTENUTO DEL FILE',
  `DOCIDMAIL` text COLLATE latin1_general_cs NOT NULL,
  `DOCPATHASSOLUTA` text COLLATE latin1_general_cs NOT NULL COMMENT 'PERCORSO ASSOLUTO DEL FILE',
  `DOCRELEASE` varchar(20) COLLATE latin1_general_cs NOT NULL COMMENT 'LABEL DI VERISIONE',
  `DOCEVI` double NOT NULL,
  `DOCLOCK` double NOT NULL,
  `DOCCLAS` varchar(30) COLLATE latin1_general_cs NOT NULL,
  `DOCDEST` text COLLATE latin1_general_cs NOT NULL,
  `DOCNOTE` text COLLATE latin1_general_cs NOT NULL,
  `DOCSERVIZIO` smallint(6) NOT NULL COMMENT 'Documento di Servizio',
  `DOCRAGGRUPPAMENTO` int(11) NOT NULL,
  `DOCMETA` text COLLATE latin1_general_cs NOT NULL COMMENT 'METADATI',
  `DOCMIGRA` text COLLATE latin1_general_cs NOT NULL,
  `DOCDAFIRM` smallint(6) NOT NULL COMMENT 'DOCUMENTO DA FIRMARE',
  `DOCDATAFIRMA` varchar(8) COLLATE latin1_general_cs NOT NULL COMMENT 'DATA DELLA FIRMA',
  `DOCUTELOG` varchar(20) COLLATE latin1_general_cs NOT NULL,
  `DOCDATADOC` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `DOCORADOC` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `DOCPRTCLASS` varchar(20) COLLATE latin1_general_cs NOT NULL COMMENT 'TABELLA ORIGINE PROTOCOLLAZIONE',
  `DOCPRTROWID` int(11) NOT NULL COMMENT 'ID REC TABELLA ORIGINE PROTOCOLLAZIONE',
  `DOCROWIDBASE` int(11) NOT NULL,
  `DOCSUBTIPO` varchar(10) COLLATE latin1_general_cs NOT NULL,
  `DOCSHA2` varchar(64) COLLATE latin1_general_cs NOT NULL,
  `DOCPUBB` varchar(2) COLLATE latin1_general_cs NOT NULL COMMENT 'FLAG PER INDICARE IL DOCUMENTO PUBBLICABILE',
  `DOCUUID` varchar(255) COLLATE latin1_general_cs NOT NULL COMMENT 'ID DOCUMENTALE',
  `DOCRELUUID` varchar(36) COLLATE latin1_general_cs NOT NULL COMMENT 'UUID del documento',
  `DOCRELCHIAVE` int(11) NOT NULL COMMENT 'Chiave del dcoumento',
  `DOCRELCLASSE` varchar(20) COLLATE latin1_general_cs NOT NULL COMMENT 'Classe del documento',
  PRIMARY KEY (`ROWID`),
  KEY `I_DOCLNK` (`DOCLNK`),
  KEY `I_DOCKEY` (`DOCKEY`),
  KEY `I_DOCNUM` (`DOCNUM`,`DOCPAR`),
  KEY `I_DOCROWID` (`DOCROWIDBASE`),
  KEY `I_ANADOC_DOCIDMAIL` (`DOCIDMAIL`(255)),
  KEY `I_ANADOC_DOCROWIDBASE` (`DOCROWIDBASE`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ANADOCSAVE` (
  `ROWID` int(11) NOT NULL AUTO_INCREMENT,
  `DOCKEY` varchar(30) COLLATE latin1_general_cs NOT NULL,
  `DOCNUM` double NOT NULL,
  `DOCPAR` varchar(2) COLLATE latin1_general_cs NOT NULL,
  `DOCFIL` varchar(50) COLLATE latin1_general_cs NOT NULL,
  `DOCORF` varchar(100) COLLATE latin1_general_cs NOT NULL,
  `DOCFDT` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `DOCFTM` varchar(6) COLLATE latin1_general_cs NOT NULL,
  `DOCLNK` varchar(100) COLLATE latin1_general_cs NOT NULL,
  `DOCNOT` text COLLATE latin1_general_cs NOT NULL,
  `DOCCLA` varchar(50) COLLATE latin1_general_cs NOT NULL,
  `DOCUTC` varchar(6) COLLATE latin1_general_cs NOT NULL,
  `DOCUTE` varchar(60) COLLATE latin1_general_cs NOT NULL,
  `DOCSTATO` varchar(100) COLLATE latin1_general_cs NOT NULL,
  `SAVEDATA` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `SAVEORA` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `SAVEUTENTE` varchar(20) COLLATE latin1_general_cs NOT NULL,
  `DOCTIPO` varchar(30) COLLATE latin1_general_cs NOT NULL,
  `DOCMD5` text COLLATE latin1_general_cs NOT NULL COMMENT 'MD5 DEL CONTENUTO DEL FILE',
  `DOCSTA` varchar(100) COLLATE latin1_general_cs NOT NULL,
  `DOCLOG` text COLLATE latin1_general_cs NOT NULL,
  `DOCNAME` text COLLATE latin1_general_cs NOT NULL,
  `DOCIDMAIL` text COLLATE latin1_general_cs NOT NULL,
  `DOCPATHASSOLUTA` text COLLATE latin1_general_cs NOT NULL COMMENT 'PERCORSO ASSOLUTO DEL FILE',
  `DOCRELEASE` varchar(20) COLLATE latin1_general_cs NOT NULL,
  `DOCEVI` double NOT NULL,
  `DOCLOCK` double NOT NULL,
  `DOCCLAS` varchar(30) COLLATE latin1_general_cs NOT NULL,
  `DOCDEST` text COLLATE latin1_general_cs NOT NULL,
  `DOCNOTE` text COLLATE latin1_general_cs NOT NULL,
  `DOCSERVIZIO` smallint(6) NOT NULL COMMENT 'Documento di Servizio',
  `DOCRAGGRUPPAMENTO` int(11) NOT NULL,
  `DOCMETA` text COLLATE latin1_general_cs NOT NULL COMMENT 'METADATI',
  `DOCMIGRA` text COLLATE latin1_general_cs NOT NULL,
  `DOCDAFIRM` smallint(6) NOT NULL COMMENT 'DOCUMENTO DA FIRMARE',
  `DOCDATAFIRMA` varchar(8) COLLATE latin1_general_cs NOT NULL COMMENT 'DATA DELLA FIRMA',
  `DOCUTELOG` varchar(20) COLLATE latin1_general_cs NOT NULL,
  `DOCDATADOC` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `DOCORADOC` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `ROWIDPASDOC` int(11) NOT NULL,
  `DOCPRTCLASS` varchar(20) COLLATE latin1_general_cs NOT NULL COMMENT 'TABELLA ORIGINE PROTOCOLLAZIONE',
  `DOCPRTROWID` int(11) NOT NULL COMMENT 'ID REC TABELLA ORIGINE PROTOCOLLAZIONE',
  `DOCROWIDBASE` int(11) NOT NULL,
  `DOCSUBTIPO` varchar(10) COLLATE latin1_general_cs NOT NULL,
  `DOCSHA2` varchar(64) COLLATE latin1_general_cs NOT NULL,
  `DOCPUBB` varchar(2) COLLATE latin1_general_cs NOT NULL COMMENT 'FLAG PER INDICARE IL DOCUMENTO PUBBLICABILE',
  `DOCUUID` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `DOCRELCHIAVE` int(11) NOT NULL COMMENT 'Chiave del dcoumento',
  `DOCRELUUID` varchar(36) COLLATE latin1_general_cs NOT NULL COMMENT 'UUID del documento',
  `DOCRELCLASSE` varchar(20) COLLATE latin1_general_cs NOT NULL COMMENT 'Classe del documento',
  PRIMARY KEY (`ROWID`),
  KEY `I_DOCLNK` (`DOCLNK`),
  KEY `I_DOCNUM` (`DOCNUM`,`DOCPAR`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ANADOG` (
  `ROWID` int(11) NOT NULL AUTO_INCREMENT,
  `DOGCOD` varchar(4) COLLATE latin1_general_cs NOT NULL,
  `DOGDES__1` varchar(100) COLLATE latin1_general_cs NOT NULL,
  `DOGDES__2` varchar(100) COLLATE latin1_general_cs NOT NULL,
  `DOGDES__3` varchar(100) COLLATE latin1_general_cs NOT NULL,
  `DOGDES__4` varchar(100) COLLATE latin1_general_cs NOT NULL,
  `DOGCO1` double NOT NULL,
  `DOGDE1` varchar(30) COLLATE latin1_general_cs NOT NULL,
  `DOGDAT` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `DOGMED` varchar(6) COLLATE latin1_general_cs NOT NULL,
  `DOGUFF` text COLLATE latin1_general_cs NOT NULL,
  `DOGCAT` varchar(4) COLLATE latin1_general_cs NOT NULL,
  `DOGCLA` varchar(4) COLLATE latin1_general_cs NOT NULL,
  `DOGFAS` varchar(4) COLLATE latin1_general_cs NOT NULL,
  `DOGORG` varchar(6) COLLATE latin1_general_cs NOT NULL,
  `DOGDEX` text COLLATE latin1_general_cs NOT NULL,
  `DOGINCREMENTALE` double NOT NULL COMMENT 'INCREMENTALE PER ARCHIVIAZIONE',
  PRIMARY KEY (`ROWID`),
  KEY `A_DOGCOD` (`DOGCOD`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ANAENT` (
  `ROWID` int(11) NOT NULL AUTO_INCREMENT,
  `ENTDE1` varchar(100) COLLATE latin1_general_cs NOT NULL,
  `ENTDE2` varchar(100) COLLATE latin1_general_cs NOT NULL,
  `ENTDE3` varchar(100) COLLATE latin1_general_cs NOT NULL,
  `ENTDE4` varchar(100) COLLATE latin1_general_cs NOT NULL,
  `ENTDE5` varchar(100) COLLATE latin1_general_cs NOT NULL,
  `ENTDE6` varchar(100) COLLATE latin1_general_cs NOT NULL,
  `ENTKEY` varchar(6) COLLATE latin1_general_cs NOT NULL,
  `ENTVAL` text COLLATE latin1_general_cs NOT NULL,
  PRIMARY KEY (`ROWID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ANAESITO` (
  `ROWID` int(11) NOT NULL AUTO_INCREMENT,
  `CODICE` int(11) NOT NULL COMMENT 'Codice',
  `ESITO` text COLLATE latin1_general_cs NOT NULL COMMENT 'Descrizione',
  PRIMARY KEY (`ROWID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs COMMENT='Esito';
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ANAFAS` (
  `ROWID` int(11) NOT NULL AUTO_INCREMENT,
  `FASCOD` varchar(4) COLLATE latin1_general_cs NOT NULL,
  `FASDE1` varchar(36) COLLATE latin1_general_cs NOT NULL,
  `FASDE2` varchar(36) COLLATE latin1_general_cs NOT NULL,
  `FASDE3` varchar(36) COLLATE latin1_general_cs NOT NULL,
  `FASDE4` varchar(36) COLLATE latin1_general_cs NOT NULL,
  `FASCCA` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `FASCCF` varchar(12) COLLATE latin1_general_cs NOT NULL,
  `FASDAT` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `FASDES` varchar(200) COLLATE latin1_general_cs NOT NULL,
  `FASATTGIUD` smallint(6) NOT NULL COMMENT 'FLAG ATTO GIUDIZIARIO',
  `FASRIS` smallint(6) NOT NULL COMMENT 'FLAG RISERVATO',
  `VERSIONE_T` int(11) NOT NULL,
  `FASCCF_SUCC` varchar(12) COLLATE latin1_general_cs NOT NULL COMMENT 'CODICE CATEGORIA-CLASSE-SOTTOCLASSE SUCCESSIVO',
  `VERSIONE_SUCC` int(11) NOT NULL COMMENT 'VERSIONE TITOLARIO SUCCESSIVO',
  `NUMROMANA` varchar(15) COLLATE latin1_general_cs NOT NULL COMMENT 'CODIFICA IN NUM. ROMANI',
  PRIMARY KEY (`ROWID`),
  KEY `A_FASCOD` (`FASCOD`),
  KEY `A_FASCCA` (`FASCCA`),
  KEY `A_FASCCF` (`FASCCF`),
  KEY `I_FASCCF` (`FASDAT`,`FASCCF`),
  KEY `I_FASDAT` (`FASDAT`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ANAMED` (
  `ROWID` int(11) NOT NULL AUTO_INCREMENT,
  `MEDCOD` varchar(6) COLLATE latin1_general_cs NOT NULL,
  `MEDNOM` varchar(300) COLLATE latin1_general_cs NOT NULL,
  `MEDIND` varchar(300) COLLATE latin1_general_cs NOT NULL,
  `MEDCAP` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `MEDCIT` varchar(40) COLLATE latin1_general_cs NOT NULL,
  `MEDPRO` varchar(4) COLLATE latin1_general_cs NOT NULL,
  `MEDUFF` varchar(50) COLLATE latin1_general_cs NOT NULL,
  `MEDEMA` text COLLATE latin1_general_cs NOT NULL,
  `MEDPRI` varchar(2) COLLATE latin1_general_cs NOT NULL,
  `MEDFIS` varchar(16) COLLATE latin1_general_cs NOT NULL,
  `MEDTEL` varchar(20) COLLATE latin1_general_cs NOT NULL COMMENT 'Telefono',
  `MEDNOTE` text COLLATE latin1_general_cs NOT NULL COMMENT 'Note',
  `MEDCODAOO` varchar(8) COLLATE latin1_general_cs NOT NULL COMMENT 'CODICE AOO',
  `MEDDENAOO` varchar(50) COLLATE latin1_general_cs NOT NULL COMMENT 'DENOMINAZIONE AOO',
  `MEDTIPIND` varchar(30) COLLATE latin1_general_cs NOT NULL COMMENT 'TIPO INDIRIZZO TELEMATICO',
  `MEDIPA` tinyint(4) NOT NULL COMMENT 'importato da IPA',
  `MEDANN` smallint(6) NOT NULL COMMENT 'ANNULLATO',
  `MEDFAX` varchar(20) COLLATE latin1_general_cs NOT NULL COMMENT 'fax',
  `MEDSEX` varchar(2) COLLATE latin1_general_cs NOT NULL COMMENT 'SESSO',
  `MEDTIT` varchar(26) COLLATE latin1_general_cs NOT NULL COMMENT 'TITOLO',
  `MEDCELL` varchar(20) COLLATE latin1_general_cs NOT NULL COMMENT 'numero cellulare',
  `MEDTAG` text COLLATE latin1_general_cs NOT NULL COMMENT 'Tag Soggetto',
  PRIMARY KEY (`ROWID`),
  KEY `A_MEDCOD` (`MEDCOD`),
  KEY `I_MEDUFF` (`MEDUFF`),
  KEY `I_MEDFIS` (`MEDFIS`),
  KEY `I_MEDNOM` (`MEDNOM`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ANANOM` (
  `ROWID` int(11) NOT NULL AUTO_INCREMENT,
  `NOMNUM` double NOT NULL,
  `NOMNOM` varchar(300) COLLATE latin1_general_cs NOT NULL,
  `NOMPAR` varchar(2) COLLATE latin1_general_cs NOT NULL,
  `NOMMIGRA` varchar(20) COLLATE latin1_general_cs NOT NULL,
  PRIMARY KEY (`ROWID`),
  KEY `A_NOMNUM` (`NOMNUM`,`NOMPAR`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ANANOMSAVE` (
  `ROWID` int(11) NOT NULL AUTO_INCREMENT,
  `NOMNUM` double NOT NULL,
  `NOMNOM` varchar(300) COLLATE latin1_general_cs NOT NULL,
  `NOMPAR` varchar(2) COLLATE latin1_general_cs NOT NULL,
  `SAVEDATA` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `SAVEORA` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `SAVEUTENTE` varchar(20) COLLATE latin1_general_cs NOT NULL,
  `NOMMIGRA` varchar(20) COLLATE latin1_general_cs NOT NULL,
  PRIMARY KEY (`ROWID`),
  KEY `A_NOMNUM` (`NOMNUM`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ANAOGG` (
  `ROWID` int(11) NOT NULL AUTO_INCREMENT,
  `OGGNUM` double NOT NULL,
  `OGGDE1` varchar(100) COLLATE latin1_general_cs NOT NULL,
  `OGGDE2` varchar(100) COLLATE latin1_general_cs NOT NULL,
  `OGGDE3` varchar(100) COLLATE latin1_general_cs NOT NULL,
  `OGGNOM` varchar(36) COLLATE latin1_general_cs NOT NULL,
  `OGGCHI` varchar(16) COLLATE latin1_general_cs NOT NULL,
  `OGGDAT` double NOT NULL,
  `OGGPAR` varchar(2) COLLATE latin1_general_cs NOT NULL,
  `OGGOGG` text COLLATE latin1_general_cs NOT NULL,
  `OGGMIGRA` varchar(20) COLLATE latin1_general_cs NOT NULL,
  PRIMARY KEY (`ROWID`),
  KEY `A_OGGNUM` (`OGGNUM`),
  KEY `I_OGGPAR` (`OGGNUM`,`OGGPAR`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ANAOGGSAVE` (
  `ROWID` int(11) NOT NULL AUTO_INCREMENT,
  `OGGNUM` double NOT NULL,
  `OGGDE1` varchar(100) COLLATE latin1_general_cs NOT NULL,
  `OGGDE2` varchar(100) COLLATE latin1_general_cs NOT NULL,
  `OGGDE3` varchar(100) COLLATE latin1_general_cs NOT NULL,
  `OGGNOM` varchar(36) COLLATE latin1_general_cs NOT NULL,
  `OGGCHI` varchar(16) COLLATE latin1_general_cs NOT NULL,
  `OGGDAT` double NOT NULL,
  `OGGPAR` varchar(2) COLLATE latin1_general_cs NOT NULL,
  `OGGOGG` text COLLATE latin1_general_cs NOT NULL,
  `SAVEDATA` varchar(8) COLLATE latin1_general_cs NOT NULL COMMENT 'DATA MODIFICA',
  `SAVEORA` varchar(8) COLLATE latin1_general_cs NOT NULL COMMENT 'ORA MODIFICA',
  `SAVEUTENTE` varchar(20) COLLATE latin1_general_cs NOT NULL COMMENT 'UTENTE MODIFICA',
  `OGGMIGRA` varchar(20) COLLATE latin1_general_cs NOT NULL,
  PRIMARY KEY (`ROWID`),
  KEY `A_OGGNUM` (`OGGNUM`),
  KEY `I_OGGPAR` (`OGGNUM`,`OGGPAR`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ANAORDNODE` (
  `ROWID` int(11) NOT NULL AUTO_INCREMENT,
  `TIPONODE` varchar(2) COLLATE latin1_general_cs NOT NULL,
  `SEQORD` int(11) NOT NULL,
  PRIMARY KEY (`ROWID`),
  UNIQUE KEY `TIPONODE` (`TIPONODE`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ANAORG` (
  `ROWID` int(11) NOT NULL AUTO_INCREMENT,
  `ORGCOD` varchar(6) COLLATE latin1_general_cs NOT NULL,
  `ORGDE1` varchar(36) COLLATE latin1_general_cs NOT NULL,
  `ORGDE2` varchar(36) COLLATE latin1_general_cs NOT NULL,
  `ORGCCF` varchar(12) COLLATE latin1_general_cs NOT NULL,
  `ORGCHI` varchar(16) COLLATE latin1_general_cs NOT NULL,
  `ORGUFF` varchar(36) COLLATE latin1_general_cs NOT NULL,
  `ORGDAT` double NOT NULL,
  `ORGAPI` double NOT NULL,
  `ORGDCA` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `ORGDE3` varchar(36) COLLATE latin1_general_cs NOT NULL,
  `ORGDE4` varchar(36) COLLATE latin1_general_cs NOT NULL,
  `ORGAPE` double NOT NULL,
  `ORGDES` text COLLATE latin1_general_cs NOT NULL,
  `ORGUOF` varchar(4) COLLATE latin1_general_cs NOT NULL COMMENT 'UNITA'' OPERATIVA PER LA FASCICOLAZIONE',
  `ORGANN` varchar(4) COLLATE latin1_general_cs NOT NULL COMMENT 'ANNO FASCICOLAZIONE',
  `ORGKEY` varchar(30) COLLATE latin1_general_cs NOT NULL COMMENT 'CHIAVE FASCICOLAZIONE',
  `ORGAOO` varchar(20) COLLATE latin1_general_cs NOT NULL COMMENT 'AOO CHE HA APERTO IL FASCICOLO',
  `VERSIONE_T` int(11) NOT NULL COMMENT 'VERSIONE TITOLARIO',
  `CODSERIE` int(11) NOT NULL COMMENT 'CODICE DELLA SERIE',
  `PROGSERIE` int(11) NOT NULL COMMENT 'PROGRESSIVO SERIE',
  `ORGSEG` varchar(100) COLLATE latin1_general_cs NOT NULL COMMENT 'SEGNATURA FASCICOLO',
  `NATFAS` tinyint(4) NOT NULL COMMENT '0 DIGITALE, 1 CARTACEO, 2 IBRIDO',
  `ORGKEYPRE` varchar(30) COLLATE latin1_general_cs NOT NULL COMMENT 'CHIAVE FASCICOLO PRECEDENTE COLLEGATO',
  `GESNUMFASC` varchar(10) COLLATE latin1_general_cs NOT NULL COMMENT 'CHIAVE DELLA PRATICA AMMINISTRATIVA',
  PRIMARY KEY (`ROWID`),
  UNIQUE KEY `I_ORGKEY` (`ORGKEY`),
  KEY `I_ORGSERIE` (`CODSERIE`,`ORGANN`,`PROGSERIE`),
  KEY `I_ORGSEG` (`ORGSEG`),
  KEY `ORGKEYPRE` (`ORGKEYPRE`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ANAPRO` (
  `ROWID` int(11) NOT NULL AUTO_INCREMENT,
  `PRONUM` double NOT NULL,
  `PRODAR` double NOT NULL,
  `PROAGG` double NOT NULL,
  `PRONPA` varchar(50) COLLATE latin1_general_cs NOT NULL,
  `PRONOM` varchar(300) COLLATE latin1_general_cs NOT NULL,
  `PROIND` varchar(300) COLLATE latin1_general_cs NOT NULL,
  `PROCAP` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `PROCIT` varchar(40) COLLATE latin1_general_cs NOT NULL,
  `PROPRO` varchar(4) COLLATE latin1_general_cs NOT NULL,
  `PRODRA` varchar(8) COLLATE latin1_general_cs NOT NULL COMMENT 'DATA RACCOMANDATA IN PARTENZA',
  `PRONUR` varchar(20) COLLATE latin1_general_cs NOT NULL COMMENT 'N.RACCOMANDATA IN PARTENZA',
  `PROUFF` varchar(4) COLLATE latin1_general_cs NOT NULL,
  `PRONAL` double NOT NULL,
  `VERSIONE_T` int(11) NOT NULL COMMENT 'VERSIONE TITOLARIO',
  `PROCAT` varchar(4) COLLATE latin1_general_cs NOT NULL,
  `PROCCA` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `PROCCF` varchar(12) COLLATE latin1_general_cs NOT NULL,
  `PROCHI` varchar(16) COLLATE latin1_general_cs NOT NULL,
  `PROARG` varchar(6) COLLATE latin1_general_cs NOT NULL,
  `PRONRA` double NOT NULL,
  `PRONRI` double NOT NULL,
  `PRONLE` double NOT NULL,
  `PROPAR` varchar(2) COLLATE latin1_general_cs NOT NULL,
  `PROCON` varchar(6) COLLATE latin1_general_cs NOT NULL,
  `PROPRE` double NOT NULL,
  `PROSUC` double NOT NULL,
  `PRODAS` double NOT NULL,
  `PRONAF` varchar(4) COLLATE latin1_general_cs NOT NULL,
  `PRODAA` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `PRODSC` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `PROSET` varchar(6) COLLATE latin1_general_cs NOT NULL,
  `PROSER` varchar(6) COLLATE latin1_general_cs NOT NULL,
  `PROUOP` varchar(6) COLLATE latin1_general_cs NOT NULL,
  `PROPER` varchar(6) COLLATE latin1_general_cs NOT NULL,
  `PROLOG` varchar(20) COLLATE latin1_general_cs NOT NULL,
  `PROTSP` varchar(4) COLLATE latin1_general_cs NOT NULL,
  `PROCOL` double NOT NULL,
  `PROUTE` varchar(20) COLLATE latin1_general_cs NOT NULL,
  `PRORDA` varchar(10) COLLATE latin1_general_cs NOT NULL,
  `PROROR` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `PROEME` varchar(20) COLLATE latin1_general_cs NOT NULL,
  `PROUOF` varchar(4) COLLATE latin1_general_cs NOT NULL COMMENT 'UNITA'' OPERATIVA PER LA FASCICOLAZIONE',
  `PRORUO` varchar(30) COLLATE latin1_general_cs NOT NULL COMMENT 'RUOLO',
  `PROFASKEY` varchar(30) COLLATE latin1_general_cs NOT NULL COMMENT 'CHIAVE FASCICOLAZIONE',
  `PROSUBKEY` varchar(30) COLLATE latin1_general_cs NOT NULL COMMENT 'Chiave sottofascicoli',
  `PRONOTE` text COLLATE latin1_general_cs NOT NULL COMMENT 'NOTE',
  `PROSEG` varchar(100) COLLATE latin1_general_cs NOT NULL COMMENT 'CHIAVE SEGNATURA',
  `PROORA` varchar(8) COLLATE latin1_general_cs NOT NULL COMMENT 'ORA DI REGISTRAZIONE DEL PROTOCOLLO',
  `PROINCOGG` double NOT NULL COMMENT 'INCREMENTALE DELL OGGETTO',
  `PRODOGCOD` varchar(4) COLLATE latin1_general_cs NOT NULL COMMENT 'CODICE OGGETTO',
  `PROMAIL` text COLLATE latin1_general_cs NOT NULL COMMENT 'EMAIL DESTINATARI',
  `PRORISERVA` varchar(1) COLLATE latin1_general_cs NOT NULL,
  `PRORICEVUTA` varchar(2) COLLATE latin1_general_cs NOT NULL COMMENT 'FLAG RICEVUTA STAMPATA',
  `PROLRIS` varchar(1) COLLATE latin1_general_cs NOT NULL COMMENT 'LIVELLO RISERVATEZZA',
  `PRODATEME` varchar(8) COLLATE latin1_general_cs NOT NULL COMMENT 'DATA PROTOCOLLO EMERGENZA',
  `PROPARPRE` varchar(2) COLLATE latin1_general_cs NOT NULL COMMENT 'TIPO PROTOCOLLO PRECEDENTE',
  `PROIDMAILDEST` text COLLATE latin1_general_cs NOT NULL,
  `PROSECURE` int(11) NOT NULL COMMENT 'Livello protezione',
  `PROTSO` smallint(6) NOT NULL,
  `PROTEMPLATE` smallint(6) NOT NULL COMMENT 'PROTOCOLLO TEMPLATE',
  `PROMIGRA` varchar(20) COLLATE latin1_general_cs NOT NULL,
  `PROSEGEME` varchar(100) COLLATE latin1_general_cs NOT NULL COMMENT 'SEGNATURA EMERGENZA',
  `PROCODTIPODOC` varchar(6) COLLATE latin1_general_cs NOT NULL COMMENT 'Codice Tipo Documento',
  `PROSTATOPROT` smallint(6) NOT NULL COMMENT 'Stato del protocollo',
  `PROANNPTIPO` varchar(20) COLLATE latin1_general_cs NOT NULL COMMENT 'Tipo di Annullamento',
  `PROANNPNUM` varchar(20) COLLATE latin1_general_cs NOT NULL COMMENT 'Provvedimento N.',
  `PROANNPDATA` varchar(8) COLLATE latin1_general_cs NOT NULL COMMENT 'Data provvedimento',
  `PROANNMOTIVO` varchar(255) COLLATE latin1_general_cs NOT NULL COMMENT 'Motivo Annullamento',
  `PROANNAUTOR` varchar(255) COLLATE latin1_general_cs NOT NULL COMMENT 'Chi autorizza annullamento',
  `PROFIS` varchar(16) COLLATE latin1_general_cs NOT NULL COMMENT 'CODICE FISCALE MITTDEST',
  `PRONAZ` text COLLATE latin1_general_cs NOT NULL COMMENT 'NAZIONE',
  `PROIDMAIL` text COLLATE latin1_general_cs NOT NULL COMMENT 'ID MAIL DEL PROTOCOLLO',
  `PRODAAORA` varchar(8) COLLATE latin1_general_cs NOT NULL COMMENT 'ORA ARRIVO DELLE CARTE',
  `PROORAEME` varchar(8) COLLATE latin1_general_cs NOT NULL COMMENT 'ORA DEL PROTOCOLLO DI EMERGENZA',
  PRIMARY KEY (`ROWID`),
  KEY `A_PRONUM` (`PRONUM`),
  KEY `A_PRODAR` (`PRODAR`),
  KEY `A_PROAGG` (`PROAGG`),
  KEY `A_PRONPA` (`PRONPA`),
  KEY `A_PRONOM` (`PRONOM`),
  KEY `A_PROUFF` (`PROUFF`),
  KEY `A_PROCAT` (`PROCAT`),
  KEY `A_PROCCA` (`PROCCA`),
  KEY `A_PROCON` (`PROCON`),
  KEY `A_PROTSP` (`PROTSP`),
  KEY `I_PRODAR` (`PRODAR`,`PRONUM`),
  KEY `I_PROUFF` (`PROUFF`,`PRODAA`),
  KEY `I_PROCON` (`PROCON`,`PRODAA`),
  KEY `I_PROARG` (`PROARG`,`PRODAA`),
  KEY `I_PRODSC` (`PROPAR`,`PROSET`,`PRODSC`),
  KEY `I_PROPAR` (`PRONUM`,`PROPAR`),
  KEY `I_PROPRE` (`PROPRE`),
  KEY `I_PROSEG` (`PROSEG`),
  KEY `I_PROSUBKEY` (`PROFASKEY`,`PROSUBKEY`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ANAPROSAVE` (
  `ROWID` int(11) NOT NULL AUTO_INCREMENT,
  `PRONUM` double NOT NULL,
  `PRODAR` double NOT NULL,
  `PROAGG` double NOT NULL,
  `PRONPA` varchar(50) COLLATE latin1_general_cs NOT NULL,
  `PRONOM` varchar(300) COLLATE latin1_general_cs NOT NULL,
  `PROIND` varchar(300) COLLATE latin1_general_cs NOT NULL,
  `PROCAP` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `PROCIT` varchar(40) COLLATE latin1_general_cs NOT NULL,
  `PROPRO` varchar(4) COLLATE latin1_general_cs NOT NULL,
  `PRODRA` varchar(8) COLLATE latin1_general_cs NOT NULL COMMENT 'DATA RACCOMANDATA IN PARTENZA',
  `PRONUR` varchar(20) COLLATE latin1_general_cs NOT NULL COMMENT 'N.RACCOMANDATA IN PARTENZA',
  `PROUFF` varchar(4) COLLATE latin1_general_cs NOT NULL,
  `PRONAL` double NOT NULL,
  `VERSIONE_T` int(11) NOT NULL COMMENT 'VERSIONE TITOLARIO',
  `PROCAT` varchar(4) COLLATE latin1_general_cs NOT NULL,
  `PROCCA` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `PROCCF` varchar(12) COLLATE latin1_general_cs NOT NULL,
  `PROCHI` varchar(16) COLLATE latin1_general_cs NOT NULL,
  `PROARG` varchar(6) COLLATE latin1_general_cs NOT NULL,
  `PRONRA` double NOT NULL,
  `PRONRI` double NOT NULL,
  `PRONLE` double NOT NULL,
  `PROPAR` varchar(2) COLLATE latin1_general_cs NOT NULL,
  `PROCON` varchar(6) COLLATE latin1_general_cs NOT NULL,
  `PROPRE` double NOT NULL,
  `PROSUC` double NOT NULL,
  `PRODAS` double NOT NULL,
  `PRONAF` varchar(4) COLLATE latin1_general_cs NOT NULL,
  `PRODAA` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `PRODSC` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `PROSET` varchar(6) COLLATE latin1_general_cs NOT NULL,
  `PROSER` varchar(6) COLLATE latin1_general_cs NOT NULL,
  `PROUOP` varchar(6) COLLATE latin1_general_cs NOT NULL,
  `PROPER` varchar(6) COLLATE latin1_general_cs NOT NULL,
  `PROLOG` varchar(20) COLLATE latin1_general_cs NOT NULL,
  `PROTSP` varchar(4) COLLATE latin1_general_cs NOT NULL,
  `PROCOL` double NOT NULL,
  `PROUTE` varchar(20) COLLATE latin1_general_cs NOT NULL,
  `PRORDA` varchar(10) COLLATE latin1_general_cs NOT NULL,
  `PROROR` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `PROEME` varchar(20) COLLATE latin1_general_cs NOT NULL,
  `PROUOF` varchar(4) COLLATE latin1_general_cs NOT NULL COMMENT 'UNITA'' OPERATIVA PER LA FASCICOLAZIONE',
  `PRORUO` varchar(30) COLLATE latin1_general_cs NOT NULL COMMENT 'RUOLO',
  `PROFASKEY` varchar(30) COLLATE latin1_general_cs NOT NULL COMMENT 'CHIAVE FASCICOLAZIONE',
  `PROSUBKEY` varchar(30) COLLATE latin1_general_cs NOT NULL COMMENT 'Chiave sottofascicoli',
  `SAVEDATA` varchar(8) COLLATE latin1_general_cs NOT NULL COMMENT 'DATA MODIFICA',
  `SAVEORA` varchar(8) COLLATE latin1_general_cs NOT NULL COMMENT 'ORA MODIFICA',
  `SAVEUTENTE` varchar(20) COLLATE latin1_general_cs NOT NULL COMMENT 'UTENTE MODIFICA',
  `SAVEMOTIVAZIONE` varchar(300) COLLATE latin1_general_cs NOT NULL COMMENT 'MOTIVAZIONE MODIFICA',
  `PRONOTE` text COLLATE latin1_general_cs NOT NULL COMMENT 'NOTE',
  `PROSEG` varchar(100) COLLATE latin1_general_cs NOT NULL,
  `PROORA` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `PROINCOGG` double NOT NULL COMMENT 'INCREMENTALE DELL OGGETTO',
  `PRODOGCOD` varchar(4) COLLATE latin1_general_cs NOT NULL COMMENT 'CODICE OGGETTO',
  `PROMAIL` text COLLATE latin1_general_cs NOT NULL COMMENT 'EMAIL DESTINATARI',
  `PRORISERVA` varchar(1) COLLATE latin1_general_cs NOT NULL,
  `PRORICEVUTA` varchar(2) COLLATE latin1_general_cs NOT NULL COMMENT 'FLAG RICEVUTA STAMPATA',
  `PROLRIS` varchar(1) COLLATE latin1_general_cs NOT NULL COMMENT 'LIVELLO RISERVATEZZA',
  `PRODATEME` varchar(8) COLLATE latin1_general_cs NOT NULL COMMENT 'DATA PROTOCOLLO EMERGENZA',
  `PROPARPRE` varchar(2) COLLATE latin1_general_cs NOT NULL COMMENT 'TIPO PROTOCOLLO PRECEDENTE',
  `PROIDMAILDEST` text COLLATE latin1_general_cs NOT NULL,
  `PROSECURE` int(11) NOT NULL COMMENT 'Livello protezione',
  `PROTSO` smallint(6) NOT NULL,
  `PROTEMPLATE` smallint(6) NOT NULL COMMENT 'PROTOCOLLO TEMPLATE',
  `PROMIGRA` varchar(20) COLLATE latin1_general_cs NOT NULL,
  `PROSEGEME` varchar(100) COLLATE latin1_general_cs NOT NULL COMMENT 'SEGNATURA EMERGENZA',
  `PROCODTIPODOC` varchar(6) COLLATE latin1_general_cs NOT NULL COMMENT 'Codice Tipo Documento',
  `PROSTATOPROT` smallint(6) NOT NULL COMMENT 'Stato del protocollo',
  `PROANNPTIPO` varchar(20) COLLATE latin1_general_cs NOT NULL COMMENT 'Tipo di Annullamento',
  `PROANNPNUM` varchar(20) COLLATE latin1_general_cs NOT NULL COMMENT 'Provvedimento N.',
  `PROANNPDATA` varchar(8) COLLATE latin1_general_cs NOT NULL COMMENT 'Data provvedimento',
  `PROANNMOTIVO` varchar(255) COLLATE latin1_general_cs NOT NULL COMMENT 'Motivo Annullamento',
  `PROANNAUTOR` varchar(255) COLLATE latin1_general_cs NOT NULL COMMENT 'Chi autorizza annullamento',
  `PROFIS` varchar(16) COLLATE latin1_general_cs NOT NULL,
  `PRONAZ` text COLLATE latin1_general_cs NOT NULL COMMENT 'NAZIONE',
  `PROIDMAIL` text COLLATE latin1_general_cs NOT NULL COMMENT 'ID MAIL DEL PROTOCOLLO',
  `PRODAAORA` varchar(8) COLLATE latin1_general_cs NOT NULL COMMENT 'ORA ARRIVO DELLE CARTE',
  `PROORAEME` varchar(8) COLLATE latin1_general_cs NOT NULL COMMENT 'ORA DEL PROTOCOLLO DI EMERGENZA',
  PRIMARY KEY (`ROWID`),
  KEY `A_PRONUM` (`PRONUM`),
  KEY `A_PRODAR` (`PRODAR`),
  KEY `A_PROAGG` (`PROAGG`),
  KEY `A_PRONPA` (`PRONPA`),
  KEY `A_PRONOM` (`PRONOM`),
  KEY `A_PROUFF` (`PROUFF`),
  KEY `A_PROCAT` (`PROCAT`),
  KEY `A_PROCCA` (`PROCCA`),
  KEY `A_PROCON` (`PROCON`),
  KEY `A_PROTSP` (`PROTSP`),
  KEY `I_PRODAR` (`PRODAR`,`PRONUM`),
  KEY `I_PROUFF` (`PROUFF`,`PRODAA`),
  KEY `I_PROCON` (`PROCON`,`PRODAA`),
  KEY `I_PROARG` (`PROARG`,`PRODAA`),
  KEY `I_PRODSC` (`PROPAR`,`PROSET`,`PRODSC`),
  KEY `I_PROPAR` (`PRONUM`,`PROPAR`),
  KEY `I_PROPRE` (`PROPRE`),
  KEY `I_PROSEG` (`PROSEG`),
  KEY `I_PROSUBKEY` (`PROFASKEY`,`PROSUBKEY`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ANAQUA` (
  `ROWID` int(11) NOT NULL AUTO_INCREMENT,
  `CODICE` int(11) NOT NULL,
  `QUALIFICA` varchar(100) COLLATE latin1_general_cs NOT NULL,
  PRIMARY KEY (`ROWID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ANAREGISTRIARC` (
  `ROW_ID` int(11) NOT NULL AUTO_INCREMENT,
  `SIGLA` varchar(20) COLLATE latin1_general_cs NOT NULL COMMENT 'SIGLA DELLA SERIE',
  `DESCRIZIONE` varchar(100) COLLATE latin1_general_cs NOT NULL COMMENT 'Descrizione',
  `TIPOPROGRESSIVO` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `PROGRESSIVO` int(11) NOT NULL,
  PRIMARY KEY (`ROW_ID`),
  UNIQUE KEY `I_ANAREGISTRIARC_K001` (`SIGLA`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ANAREGISTRIPROG` (
  `ROW_ID` int(11) NOT NULL AUTO_INCREMENT,
  `ROWID_ANAREGISTRO` int(11) NOT NULL,
  `ANNO` int(11) NOT NULL,
  `PROGRESSIVO` int(11) NOT NULL,
  `DATAPROGRESSIVO` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `FLAG_CHIUSO` smallint(6) NOT NULL,
  `CODUTE` varchar(20) COLLATE latin1_general_cs NOT NULL,
  `DATAOPER` date NOT NULL,
  `TIMEOPER` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `FLAG_DIS` smallint(6) NOT NULL,
  PRIMARY KEY (`ROW_ID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ANAREPARC` (
  `ROWID` int(11) NOT NULL AUTO_INCREMENT,
  `CODICE` varchar(10) COLLATE latin1_general_cs NOT NULL,
  `DESCRIZIONE` varchar(100) COLLATE latin1_general_cs NOT NULL,
  `CARATTERE` varchar(4) COLLATE latin1_general_cs NOT NULL,
  `CLASSIFICAZIONE` varchar(20) COLLATE latin1_general_cs NOT NULL,
  `PROGRESSIVO` int(11) NOT NULL,
  `TIPOPROGRESSIVO` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `DOCVOLUME` varchar(80) COLLATE latin1_general_cs NOT NULL,
  `APPROUTE` text COLLATE latin1_general_cs NOT NULL,
  `APPCONFIG` text COLLATE latin1_general_cs NOT NULL,
  PRIMARY KEY (`ROWID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ANARICE` (
  `ROWID` int(11) NOT NULL AUTO_INCREMENT,
  `CODICE` int(11) NOT NULL COMMENT 'Codice',
  `RICEVENTE` varchar(100) COLLATE latin1_general_cs NOT NULL COMMENT 'Nominativo',
  `QUALIFICA` varchar(100) COLLATE latin1_general_cs NOT NULL COMMENT 'Qualifica',
  PRIMARY KEY (`ROWID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs COMMENT='Anagrafica Ricevente';
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ANARIS` (
  `ROWID` int(11) NOT NULL AUTO_INCREMENT,
  `RISNUM` double NOT NULL,
  `RISRDA` double NOT NULL,
  `RISRDE` varchar(36) COLLATE latin1_general_cs NOT NULL,
  `RISPAR` varchar(2) COLLATE latin1_general_cs NOT NULL,
  PRIMARY KEY (`ROWID`),
  KEY `A_RISNUM` (`RISNUM`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ANARISSAVE` (
  `ROWID` int(11) NOT NULL AUTO_INCREMENT,
  `RISNUM` double NOT NULL,
  `RISRDA` double NOT NULL,
  `RISRDE` varchar(36) COLLATE latin1_general_cs NOT NULL,
  `RISPAR` varchar(2) COLLATE latin1_general_cs NOT NULL,
  `SAVEDATA` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `SAVEORA` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `SAVEUTENTE` varchar(20) COLLATE latin1_general_cs NOT NULL,
  PRIMARY KEY (`ROWID`),
  KEY `A_RISNUM` (`RISNUM`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ANARUOLI` (
  `ROWID` int(11) NOT NULL AUTO_INCREMENT,
  `RUOCOD` varchar(30) COLLATE latin1_general_cs NOT NULL COMMENT 'CODICE',
  `RUODES` text COLLATE latin1_general_cs NOT NULL COMMENT 'DESCRIZIONE',
  `RUOABBREV` varchar(6) COLLATE latin1_general_cs NOT NULL COMMENT 'ABBREVIAZIONE',
  `KRUOLO` int(11) DEFAULT NULL COMMENT 'Raccordo ruolo cityware BOR_RUOLI',
  PRIMARY KEY (`ROWID`),
  UNIQUE KEY `KRUOLO` (`KRUOLO`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ANASERIEARC` (
  `ROWID` int(11) NOT NULL AUTO_INCREMENT,
  `CODICE` int(11) NOT NULL,
  `DESCRIZIONE` varchar(100) COLLATE latin1_general_cs NOT NULL COMMENT 'Descrizione',
  `CLASSIFICAZIONE` varchar(20) COLLATE latin1_general_cs NOT NULL COMMENT 'CLASSIFICAZIONE DI RIFERIMENTO',
  `TIPOPROGRESSIVO` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `PROGRESSIVO` int(11) NOT NULL,
  `DOCVOLUME` varchar(80) COLLATE latin1_general_cs NOT NULL,
  `APPROUTE` text COLLATE latin1_general_cs NOT NULL,
  `APPCONFIG` text COLLATE latin1_general_cs NOT NULL,
  `SIGLA` varchar(20) COLLATE latin1_general_cs NOT NULL COMMENT 'SIGLA DELLA SERIE',
  `SEGSEPARATORE` varchar(1) COLLATE latin1_general_cs NOT NULL COMMENT 'Separatore variabili modello',
  `SEGTEMPLATE` text COLLATE latin1_general_cs NOT NULL,
  PRIMARY KEY (`ROWID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ANASERVIZI` (
  `ROWID` int(11) NOT NULL AUTO_INCREMENT,
  `SERCOD` varchar(30) COLLATE latin1_general_cs NOT NULL,
  `SERDES` text COLLATE latin1_general_cs NOT NULL,
  `SERABBREV` varchar(6) COLLATE latin1_general_cs NOT NULL COMMENT 'ABBREVIAZIONE SERVIZIO',
  `SERRES` varchar(6) COLLATE latin1_general_cs NOT NULL,
  PRIMARY KEY (`ROWID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ANASPE` (
  `ROWID` int(11) NOT NULL AUTO_INCREMENT,
  `PRONUM` double NOT NULL,
  `PRODAR` double NOT NULL,
  `PROPAR` varchar(2) COLLATE latin1_general_cs NOT NULL,
  `PRONOM` varchar(100) COLLATE latin1_general_cs NOT NULL,
  `PROIND` varchar(100) COLLATE latin1_general_cs NOT NULL,
  `PROCAP` varchar(5) COLLATE latin1_general_cs NOT NULL,
  `PROCIT` varchar(40) COLLATE latin1_general_cs NOT NULL,
  `PROPRO` varchar(4) COLLATE latin1_general_cs NOT NULL,
  `PROTSP` varchar(4) COLLATE latin1_general_cs NOT NULL,
  `PRODRA` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `PROGRA` double NOT NULL,
  `PROIRA` double NOT NULL,
  `PROQTA` double NOT NULL,
  `PRONUR` varchar(20) COLLATE latin1_general_cs NOT NULL,
  `PRODER` varchar(36) COLLATE latin1_general_cs NOT NULL,
  `PROPES` double NOT NULL,
  PRIMARY KEY (`ROWID`),
  KEY `A_PRONUM` (`PRONUM`),
  KEY `A_PRODAR` (`PRODAR`),
  KEY `A_PRONOM` (`PRONOM`),
  KEY `A_PROTSP` (`PROTSP`),
  KEY `A_PRONUR` (`PRONUR`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ANASPESAVE` (
  `ROWID` int(11) NOT NULL AUTO_INCREMENT,
  `PRONUM` double NOT NULL,
  `PRODAR` double NOT NULL,
  `PROPAR` varchar(2) COLLATE latin1_general_cs NOT NULL,
  `PRONOM` varchar(100) COLLATE latin1_general_cs NOT NULL,
  `PROIND` varchar(100) COLLATE latin1_general_cs NOT NULL,
  `PROCAP` varchar(5) COLLATE latin1_general_cs NOT NULL,
  `PROCIT` varchar(40) COLLATE latin1_general_cs NOT NULL,
  `PROPRO` varchar(4) COLLATE latin1_general_cs NOT NULL,
  `PROTSP` varchar(4) COLLATE latin1_general_cs NOT NULL,
  `PRODRA` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `PROGRA` double NOT NULL,
  `PROIRA` double NOT NULL,
  `PROQTA` double NOT NULL,
  `PRONUR` varchar(20) COLLATE latin1_general_cs NOT NULL,
  `PRODER` varchar(36) COLLATE latin1_general_cs NOT NULL,
  `PROPES` double NOT NULL,
  `SAVEDATA` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `SAVEORA` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `SAVEUTENTE` varchar(20) COLLATE latin1_general_cs NOT NULL,
  PRIMARY KEY (`ROWID`),
  KEY `A_PRONUM` (`PRONUM`),
  KEY `A_PRODAR` (`PRODAR`),
  KEY `A_PRONOM` (`PRONOM`),
  KEY `A_PROTSP` (`PROTSP`),
  KEY `A_PRONUR` (`PRONUR`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ANASPESE` (
  `ROWID` int(11) NOT NULL AUTO_INCREMENT,
  `CODSPE` int(11) NOT NULL COMMENT 'CODICE',
  `SPESE` varchar(100) COLLATE latin1_general_cs NOT NULL COMMENT 'TIPO',
  `IMPORTO` double NOT NULL COMMENT 'IMPORTO',
  PRIMARY KEY (`ROWID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs COMMENT='Anagrafica spese';
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ANATIP` (
  `ROWID` int(11) NOT NULL AUTO_INCREMENT,
  `TIPCOD` varchar(2) COLLATE latin1_general_cs NOT NULL,
  `TIPDES` varchar(40) COLLATE latin1_general_cs NOT NULL,
  `TIPFIL__1` varchar(40) COLLATE latin1_general_cs NOT NULL,
  `TIPFIL__2` varchar(40) COLLATE latin1_general_cs NOT NULL,
  `TIPFIL__3` varchar(40) COLLATE latin1_general_cs NOT NULL,
  `TIPFIL__4` varchar(40) COLLATE latin1_general_cs NOT NULL,
  `TIPFIL__5` varchar(40) COLLATE latin1_general_cs NOT NULL,
  `TIPFIL__6` varchar(40) COLLATE latin1_general_cs NOT NULL,
  `TIPFIN__1` double NOT NULL,
  `TIPFIN__2` double NOT NULL,
  `TIPFIN__3` double NOT NULL,
  `TIPFIN__4` double NOT NULL,
  `TIPFIN__5` double NOT NULL,
  `TIPFIN__6` double NOT NULL,
  `TIPEMA` varchar(100) COLLATE latin1_general_cs NOT NULL COMMENT 'EMAIL',
  `TIPDOC` text COLLATE latin1_general_cs NOT NULL COMMENT 'Testo utilizzato per incorpora documento',
  `TIPMOD` varchar(2) COLLATE latin1_general_cs NOT NULL COMMENT 'Modalita di incorpora',
  `TIPMETASEG` text COLLATE latin1_general_cs NOT NULL COMMENT 'Metadati segreteria',
  `TIPODOCSEG` varchar(20) COLLATE latin1_general_cs NOT NULL,
  `TIPANN` smallint(6) NOT NULL,
  PRIMARY KEY (`ROWID`),
  UNIQUE KEY `K_MASTER` (`TIPCOD`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ANATIPODOC` (
  `ROWID` int(11) NOT NULL AUTO_INCREMENT,
  `CODICE` varchar(6) COLLATE latin1_general_cs NOT NULL,
  `CLASSE` varchar(20) COLLATE latin1_general_cs NOT NULL,
  `DESCRIZIONE` text COLLATE latin1_general_cs NOT NULL,
  `OGGASSOCIATO` varchar(4) COLLATE latin1_general_cs NOT NULL,
  `FLGANN` varchar(2) COLLATE latin1_general_cs NOT NULL,
  PRIMARY KEY (`ROWID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ANATSP` (
  `ROWID` int(11) NOT NULL AUTO_INCREMENT,
  `TSPCOD` varchar(4) COLLATE latin1_general_cs NOT NULL,
  `TSPDES` varchar(36) COLLATE latin1_general_cs NOT NULL,
  `TSPGRA` double NOT NULL,
  `TSPPRE` double NOT NULL,
  `TSPTAR__1` double NOT NULL,
  `TSPTAR__2` double NOT NULL,
  `TSPTAR__3` double NOT NULL,
  `TSPTAR__4` double NOT NULL,
  `TSPTAR__5` double NOT NULL,
  `TSPTAR__6` double NOT NULL,
  `TSPTAR__7` double NOT NULL,
  `TSPTAR__8` double NOT NULL,
  `TSPTAR__9` double NOT NULL,
  `TSPTAR__10` double NOT NULL,
  `TSPTAR__11` double NOT NULL,
  `TSPTAR__12` double NOT NULL,
  `TSPTAR__13` double NOT NULL,
  `TSPTAR__14` double NOT NULL,
  `TSPTAR__15` double NOT NULL,
  `TSPTAR__16` double NOT NULL,
  `TSPTAR__17` double NOT NULL,
  `TSPTAR__18` double NOT NULL,
  `TSPTAR__19` double NOT NULL,
  `TSPTAR__20` double NOT NULL,
  `TSPTAR__21` double NOT NULL,
  `TSPTAR__22` double NOT NULL,
  `TSPTAR__23` double NOT NULL,
  `TSPTAR__24` double NOT NULL,
  `TSPTAR__25` double NOT NULL,
  `TSPTAR__26` double NOT NULL,
  `TSPTAR__27` double NOT NULL,
  `TSPTAR__28` double NOT NULL,
  `TSPTAR__29` double NOT NULL,
  `TSPTAR__30` double NOT NULL,
  `TSPPES__1` double NOT NULL,
  `TSPPES__2` double NOT NULL,
  `TSPPES__3` double NOT NULL,
  `TSPPES__4` double NOT NULL,
  `TSPPES__5` double NOT NULL,
  `TSPPES__6` double NOT NULL,
  `TSPPES__7` double NOT NULL,
  `TSPPES__8` double NOT NULL,
  `TSPPES__9` double NOT NULL,
  `TSPPES__10` double NOT NULL,
  `TSPPES__11` double NOT NULL,
  `TSPPES__12` double NOT NULL,
  `TSPPES__13` double NOT NULL,
  `TSPPES__14` double NOT NULL,
  `TSPPES__15` double NOT NULL,
  `TSPPES__16` double NOT NULL,
  `TSPPES__17` double NOT NULL,
  `TSPPES__18` double NOT NULL,
  `TSPPES__19` double NOT NULL,
  `TSPPES__20` double NOT NULL,
  `TSPPES__21` double NOT NULL,
  `TSPPES__22` double NOT NULL,
  `TSPPES__23` double NOT NULL,
  `TSPPES__24` double NOT NULL,
  `TSPPES__25` double NOT NULL,
  `TSPPES__26` double NOT NULL,
  `TSPPES__27` double NOT NULL,
  `TSPPES__28` double NOT NULL,
  `TSPPES__29` double NOT NULL,
  `TSPPES__30` double NOT NULL,
  `TSPTIPO` varchar(2) COLLATE latin1_general_cs NOT NULL COMMENT 'Tipologia spedizione',
  `TSPGRACC` tinyint(4) NOT NULL COMMENT 'FLAG ATTIVA GESTIONE RACCOMANDATE',
  PRIMARY KEY (`ROWID`),
  UNIQUE KEY `K_MASTER` (`TSPCOD`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ANAUFF` (
  `ROWID` int(11) NOT NULL AUTO_INCREMENT,
  `UFFCOD` varchar(4) COLLATE latin1_general_cs NOT NULL,
  `UFFDES` varchar(256) COLLATE latin1_general_cs NOT NULL,
  `UFFSET` varchar(100) COLLATE latin1_general_cs NOT NULL,
  `UFFSER` varchar(30) COLLATE latin1_general_cs NOT NULL,
  `UFFUOP` text COLLATE latin1_general_cs NOT NULL,
  `UFFANN` double NOT NULL,
  `UFFRES` varchar(6) COLLATE latin1_general_cs NOT NULL COMMENT 'CODICE RESPONSABILE UFFICIO',
  `UFFABB` varchar(4) COLLATE latin1_general_cs NOT NULL COMMENT 'ABBREVIAZIONE UFFICIO',
  `UFFSEGSER` varchar(2) COLLATE latin1_general_cs NOT NULL COMMENT 'CODICE ANASER.CODSER DI SEGRETERIA',
  `UFFFATCODUNICO` varchar(6) COLLATE latin1_general_cs NOT NULL,
  `UFFFATNOME` text COLLATE latin1_general_cs NOT NULL,
  `UFFFATCF` varchar(16) COLLATE latin1_general_cs NOT NULL,
  `UFFFATOGG` varchar(4) COLLATE latin1_general_cs NOT NULL,
  `UFFSEGCLA` varchar(100) COLLATE latin1_general_cs NOT NULL COMMENT 'ASSOCIAZIONE A CLASSIFICAZIONE DELLA SEGRETERIA',
  `UFFNOALL` tinyint(4) NOT NULL COMMENT 'FLAG PER INDICARE SE UFFICIO ABILITATO A PROTOCOLLARE IN ARRIVO SENZA ALLEGATI',
  `TIPOUFFICIO` varchar(2) COLLATE latin1_general_cs NOT NULL COMMENT 'TIPO URP',
  `LIVELLOVIS` int(11) NOT NULL COMMENT 'LIVELLO VISIBILITA',
  `ABILITAFASC` varchar(20) COLLATE latin1_general_cs NOT NULL COMMENT 'FLAG ABILITAZIONE FASCICOLI',
  `IDORGAN` int(11) DEFAULT NULL,
  `ABILITAPROT` varchar(20) COLLATE latin1_general_cs NOT NULL COMMENT 'FLAG ABILITAZIONI PROTOCOLLI',
  `CODICE_PADRE` varchar(4) COLLATE latin1_general_cs NOT NULL COMMENT 'CODICE UFFICIO PADRE',
  PRIMARY KEY (`ROWID`),
  UNIQUE KEY `IDORGAN` (`IDORGAN`),
  KEY `A_UFFCOD` (`UFFCOD`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ARCITE` (
  `ROWID` int(11) NOT NULL AUTO_INCREMENT,
  `ITEPRO` double NOT NULL COMMENT 'NUMERO DEL PROTOCOLLO',
  `ITEPAR` varchar(2) COLLATE latin1_general_cs NOT NULL COMMENT 'TIPO DI PROTOCOLLO',
  `ITEUFF` varchar(50) COLLATE latin1_general_cs NOT NULL COMMENT 'CODICI UFFICIO',
  `ITEDAT` varchar(8) COLLATE latin1_general_cs NOT NULL COMMENT 'DATA DELLA TRASMISSIONE',
  `ITEDATORA` varchar(8) COLLATE latin1_general_cs NOT NULL COMMENT 'ORA DELLA DATA TRASMISSIONE',
  `ITEFIN` varchar(8) COLLATE latin1_general_cs NOT NULL COMMENT 'DATA DELLA CHIUSURA',
  `ITEFINORA` varchar(8) COLLATE latin1_general_cs NOT NULL COMMENT 'ORA DELLA DATA CHIUSURA',
  `ITEULT` varchar(2) COLLATE latin1_general_cs NOT NULL,
  `ITEANN` text COLLATE latin1_general_cs NOT NULL COMMENT 'ANNOTAZIONI DELLA TRASMISSIONE',
  `ITEAN2` varchar(40) COLLATE latin1_general_cs NOT NULL,
  `ITETER` varchar(40) COLLATE latin1_general_cs NOT NULL,
  `ITEDES` varchar(6) COLLATE latin1_general_cs NOT NULL COMMENT 'CODICE DESTINATARIO',
  `ITEANT` varchar(6) COLLATE latin1_general_cs NOT NULL COMMENT 'CODICE ANTECEDENTE',
  `ITESUS` varchar(6) COLLATE latin1_general_cs NOT NULL COMMENT 'CODICE DESTINATARIO SUCCESSIVO',
  `ITEPRA` varchar(6) COLLATE latin1_general_cs NOT NULL,
  `ITETIP` varchar(6) COLLATE latin1_general_cs NOT NULL COMMENT 'TIPO/NATURA DELLA TRASMISSIONE',
  `ITEFLA` varchar(2) COLLATE latin1_general_cs NOT NULL COMMENT 'SE BLANK = DA FARE, SE 2 = CHIUSO',
  `ITEDLE` varchar(8) COLLATE latin1_general_cs NOT NULL COMMENT 'DATA DELLA LETTURA',
  `ITEDLEORA` varchar(8) COLLATE latin1_general_cs NOT NULL COMMENT 'ORA DELLA DATA LETTURA',
  `ITEKEY` varchar(30) COLLATE latin1_general_cs NOT NULL COMMENT 'CHIAVE DEL ITER',
  `ITEGES` varchar(2) COLLATE latin1_general_cs NOT NULL COMMENT 'INDICA CHE GESTIONE HA LA TRASMISSIONE',
  `ITEPRE` varchar(30) COLLATE latin1_general_cs NOT NULL COMMENT 'CHIAVE DEL ITER DELLA PRECEDENTE TRASMISSIONE',
  `ITEKPR` varchar(30) COLLATE latin1_general_cs NOT NULL COMMENT 'CHIAVE USATA SUI FASCICOLI ELETTRONICI',
  `ITEEVIDENZA` tinyint(4) NOT NULL COMMENT 'SE VALORIZZATO INDICA CHE IL PROTOCOLLO E'' IN EVIDENZA',
  `ITETERMINE` varchar(8) COLLATE latin1_general_cs NOT NULL COMMENT 'DATA TERMINE',
  `ITETERMINEORA` varchar(8) COLLATE latin1_general_cs NOT NULL COMMENT 'ORA DELLA DATA SCADENZA-TERMINE',
  `ITESTATO` int(11) NOT NULL COMMENT 'STATO TRASMISSIONE',
  `ITEMOTIVO` text COLLATE latin1_general_cs NOT NULL COMMENT 'MOTIVO STATO',
  `ITENOTEACC` text COLLATE latin1_general_cs NOT NULL COMMENT 'NOTE ACCETTAZIONE',
  `ITEDATACC` varchar(8) COLLATE latin1_general_cs NOT NULL COMMENT 'DATA ACCETTAZIONE',
  `ITEDATACCORA` varchar(8) COLLATE latin1_general_cs NOT NULL COMMENT 'ORA DELLA DATA PRESA IN CARICO',
  `ITEDATRIF` varchar(8) COLLATE latin1_general_cs NOT NULL COMMENT 'DATA RIFIUTO',
  `ITEDATRIFORA` varchar(8) COLLATE latin1_general_cs NOT NULL COMMENT 'ORA DELLA DATA RIFIUTO',
  `ITEDATACQ` varchar(8) COLLATE latin1_general_cs NOT NULL COMMENT 'Data Acquisizione',
  `ITEDATACQORA` varchar(8) COLLATE latin1_general_cs NOT NULL COMMENT 'ORA DELLA DATA DI ACQUISIZIONE',
  `ITERUO` varchar(30) COLLATE latin1_general_cs NOT NULL COMMENT 'RUOLO SOGGETTO',
  `ITEBASE` tinyint(4) NOT NULL COMMENT 'INDICA SE è ITER DI PARTENZA',
  `ITEORA` varchar(8) COLLATE latin1_general_cs NOT NULL COMMENT 'ORARIO DI REGISTRAZIONE',
  `ITESETT` varchar(30) COLLATE latin1_general_cs NOT NULL COMMENT 'SETTORE DEL DESTINATARIO DELLA TRASMISSIONE',
  `ITEORGKEY` varchar(80) COLLATE latin1_general_cs NOT NULL COMMENT 'chiave organigramma',
  `ITEORGWORKLIV` tinyint(4) NOT NULL COMMENT 'Livello accessibilita nodo iter',
  `ITEPROTECT` int(11) NOT NULL COMMENT 'LIVELLO DI PROTEZIONE DELLA TRASMISSIONE',
  `ITEORGLAYOUT` varchar(60) COLLATE latin1_general_cs NOT NULL COMMENT 'Lyout chiave organigramma',
  `ITENTRAS` int(11) NOT NULL COMMENT 'Numero trasmissioni',
  `ITENLETT` int(11) NOT NULL COMMENT 'Trasmissioni Lette',
  `ITENODO` varchar(8) COLLATE latin1_general_cs NOT NULL COMMENT 'Tipo Nodo Iter',
  `ITEANNULLATO` tinyint(4) NOT NULL COMMENT 'INDICA SE LA TRASMISSIONE è ANNULLATA',
  `ITEDATAANNULLAMENTO` varchar(8) COLLATE latin1_general_cs NOT NULL COMMENT 'DATA ANNULLAMENTO TRASMISSIONE',
  `ITEDATAANNULLAMENTOORA` varchar(8) COLLATE latin1_general_cs NOT NULL COMMENT 'ORA DELLA DATA ANNULLAMENTO',
  `ITEMIGRA` varchar(20) COLLATE latin1_general_cs NOT NULL,
  `ITEPRIVACY` smallint(6) NOT NULL,
  `ITECODRAGIONE` smallint(6) NOT NULL COMMENT 'Ragione della trasmissione',
  PRIMARY KEY (`ROWID`),
  KEY `I_ITEPRO` (`ITEPRO`,`ITEPAR`),
  KEY `I_ITEKEY` (`ITEKEY`),
  KEY `I_ITESETT` (`ITESETT`,`ITEUFF`),
  KEY `I_ITEDES` (`ITEDES`),
  KEY `I_ITEPRE` (`ITEPRE`),
  KEY `I_ITEUFF` (`ITEUFF`,`ITEORGWORKLIV`),
  KEY `I_ITEDAT_ORA` (`ITEDAT`,`ITEDATORA`),
  KEY `I_ITEFIN_ORA` (`ITEFIN`,`ITEFINORA`),
  KEY `I_ITEMIGRA` (`ITEMIGRA`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `CONNSERIEARC` (
  `ROWID` int(11) NOT NULL AUTO_INCREMENT,
  `CODICESERIE` int(11) NOT NULL COMMENT 'CODICE DELLA SERIE',
  `VERSIONE_T` int(11) NOT NULL COMMENT 'VERSIONE TITOLARIO',
  `ORGCCF` varchar(12) COLLATE latin1_general_cs NOT NULL COMMENT 'CODICE TITOLARIO',
  `FLGANN` tinyint(4) NOT NULL COMMENT 'FLAG CONNESSIONE ANNULLATA',
  PRIMARY KEY (`ROWID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `DELEGHEITER` (
  `ROWID` int(11) NOT NULL AUTO_INCREMENT,
  `DELESRCCOD` varchar(6) COLLATE latin1_general_cs NOT NULL COMMENT 'CODICE DELEGANTE',
  `DELESRCUFF` varchar(4) COLLATE latin1_general_cs NOT NULL COMMENT 'CODICE UFFICIO DEL DELGANTE',
  `DELEINIVAL` varchar(8) COLLATE latin1_general_cs NOT NULL COMMENT 'INIZIO VALIDITA'' DELEGA',
  `DELEFINVAL` varchar(8) COLLATE latin1_general_cs NOT NULL COMMENT 'FINE VALIDITA'' DELEGA',
  `DELEDSTCOD` varchar(6) COLLATE latin1_general_cs NOT NULL COMMENT 'CODICE DELEGATO',
  `DELEDSTUFF` varchar(4) COLLATE latin1_general_cs NOT NULL COMMENT 'CODICE UFFICIO DEL DELEGATO',
  `DELECOPIA` int(11) NOT NULL COMMENT 'FLAG PER DEFINIZIONE COPIA TRASMISSIONE A DELEGANTE',
  `DELENOTE` text COLLATE latin1_general_cs NOT NULL COMMENT 'NOTE',
  `DELEDATEANN` varchar(8) COLLATE latin1_general_cs NOT NULL COMMENT 'DATA ANNULLAMENTO DELEGA',
  `DELEUTEADD` varchar(30) COLLATE latin1_general_cs NOT NULL COMMENT 'LOGNAME INSERIMENTO',
  `DELETIMEADD` varchar(20) COLLATE latin1_general_cs NOT NULL COMMENT 'DATA/ORA INSERIMENTO',
  `DELEUTEEDIT` varchar(30) COLLATE latin1_general_cs NOT NULL COMMENT 'LOGNAME MODIFICA',
  `DELETIMEEDIT` varchar(20) COLLATE latin1_general_cs NOT NULL COMMENT 'DATE/TIME MODIFICA',
  `DELEATTIVAZIONE` int(11) NOT NULL COMMENT 'FLAG DI ATTIVAZIONE',
  `DELEPROTNUM` double NOT NULL COMMENT 'NUMERO DI PROTOCOLLO AUTORIZZAZIONE DELEGA',
  `DELEPROTPAR` varchar(2) COLLATE latin1_general_cs NOT NULL COMMENT 'TIPO PROTOCOLLO AUTORIZZAZIONE DELEGA',
  `DELESCRIVANIA` smallint(6) NOT NULL COMMENT 'Attiva la delega della scrivania',
  `DELEFUNZIONE` smallint(6) NOT NULL COMMENT 'FUNZIONE DI DELEGA',
  PRIMARY KEY (`ROWID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `DOCFIRMA` (
  `ROWID` int(11) NOT NULL AUTO_INCREMENT,
  `FIRDATARICH` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `FIRCODRICH` varchar(6) COLLATE latin1_general_cs NOT NULL,
  `FIRCOD` varchar(6) COLLATE latin1_general_cs NOT NULL,
  `FIRUFF` varchar(4) COLLATE latin1_general_cs NOT NULL,
  `ROWIDANADOC` int(11) NOT NULL,
  `ROWIDARCITE` int(11) NOT NULL,
  `FIRDATA` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `FIRANN` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `FIRORA` varchar(8) COLLATE latin1_general_cs NOT NULL,
  PRIMARY KEY (`ROWID`),
  KEY `ROWIDANADOC` (`ROWIDANADOC`),
  KEY `ROWIDARCITE` (`ROWIDARCITE`),
  KEY `FIRCOD` (`FIRCOD`,`FIRUFF`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `DOCVERSION` (
  `ROWID` int(11) NOT NULL AUTO_INCREMENT,
  `DOCROWID` int(11) NOT NULL COMMENT 'ROWID DOCUMENTO',
  `VERDATA` varchar(8) COLLATE latin1_general_cs NOT NULL COMMENT 'DATA VERSIONE',
  `VERORA` varchar(6) COLLATE latin1_general_cs NOT NULL COMMENT 'ORA VERSIONE',
  `VERID` int(11) NOT NULL COMMENT 'ID SEQUENZIALE VERSIONE',
  `VERNOTE` text COLLATE latin1_general_cs NOT NULL COMMENT 'NOTE',
  `VERMETA` text COLLATE latin1_general_cs NOT NULL COMMENT 'METADATI',
  `VERMD5` varchar(32) COLLATE latin1_general_cs NOT NULL,
  `VERSHA2` varchar(64) COLLATE latin1_general_cs NOT NULL,
  PRIMARY KEY (`ROWID`),
  UNIQUE KEY `DOCROWID` (`DOCROWID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `MEDRUOLI` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `MEDCOD` varchar(6) COLLATE latin1_general_cs NOT NULL COMMENT 'CODICE SOGGETTO',
  `RUOCOD` varchar(30) COLLATE latin1_general_cs NOT NULL COMMENT 'CODICE RUOLO',
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `MEDSERIE` (
  `ROW_ID` int(11) NOT NULL AUTO_INCREMENT,
  `SERIECODICE` int(11) NOT NULL,
  `MEDCOD` varchar(6) COLLATE latin1_general_cs NOT NULL,
  `DATAINI` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `DATAEND` varchar(8) COLLATE latin1_general_cs NOT NULL,
  PRIMARY KEY (`ROW_ID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `NOTE` (
  `ROWID` int(11) NOT NULL AUTO_INCREMENT,
  `OGGETTO` varchar(60) COLLATE latin1_general_cs NOT NULL COMMENT 'Oggetto Breve',
  `TESTO` text COLLATE latin1_general_cs NOT NULL COMMENT 'Testo',
  `DATAINS` varchar(8) COLLATE latin1_general_cs NOT NULL COMMENT 'Data Inserimento',
  `ORAINS` varchar(8) COLLATE latin1_general_cs NOT NULL COMMENT 'Ora Inserimento',
  `DATAMOD` varchar(8) COLLATE latin1_general_cs NOT NULL COMMENT 'Data Modifica',
  `DATAANN` varchar(8) COLLATE latin1_general_cs NOT NULL COMMENT 'Data Annullamento',
  `ORAMOD` varchar(8) COLLATE latin1_general_cs NOT NULL COMMENT 'Ora modifica',
  `ORAANN` varchar(8) COLLATE latin1_general_cs NOT NULL COMMENT 'Ora annullamento',
  `UTELOG` varchar(20) COLLATE latin1_general_cs NOT NULL COMMENT 'Utente Login',
  `UTELOGMOD` varchar(20) COLLATE latin1_general_cs NOT NULL COMMENT 'Logname Modifica',
  `UTELOGANN` varchar(20) COLLATE latin1_general_cs NOT NULL COMMENT 'Utente ANNULLAMENTO',
  `MEDCOD` varchar(6) COLLATE latin1_general_cs NOT NULL COMMENT 'Codice Destinatario interno',
  PRIMARY KEY (`ROWID`),
  KEY `TESTO` (`TESTO`(255))
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `NOTECLAS` (
  `ROWID` int(11) NOT NULL AUTO_INCREMENT,
  `ROWIDNOTE` int(11) NOT NULL,
  `ROWIDPADRE` int(11) NOT NULL,
  `CLASSE` varchar(20) COLLATE latin1_general_cs NOT NULL,
  `ROWIDCLASSE` int(11) NOT NULL,
  `METADATA` text COLLATE latin1_general_cs NOT NULL,
  PRIMARY KEY (`ROWID`),
  KEY `ROWIDNOTE` (`ROWIDNOTE`),
  KEY `ROWIDPADRE` (`ROWIDPADRE`),
  KEY `I_CLASSE` (`CLASSE`,`ROWIDCLASSE`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `OGGUTENTI` (
  `ROWID` int(11) NOT NULL AUTO_INCREMENT,
  `DOGCOD` varchar(4) COLLATE latin1_general_cs NOT NULL,
  `UTELOG` varchar(20) COLLATE latin1_general_cs NOT NULL,
  PRIMARY KEY (`ROWID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ORGCONN` (
  `ROWID` int(11) NOT NULL AUTO_INCREMENT,
  `ORGKEY` varchar(30) COLLATE latin1_general_cs NOT NULL,
  `PRONUM` double NOT NULL,
  `PROPAR` varchar(2) COLLATE latin1_general_cs NOT NULL,
  `PRONUMPARENT` double NOT NULL,
  `PROPARPARENT` varchar(2) COLLATE latin1_general_cs NOT NULL,
  `CONNSEQ` int(11) NOT NULL,
  `CONNMETA` text COLLATE latin1_general_cs NOT NULL,
  `CONNUTEINS` varchar(20) COLLATE latin1_general_cs NOT NULL COMMENT 'UTENTE INSERIMENTO',
  `CONNDATAINS` varchar(8) COLLATE latin1_general_cs NOT NULL COMMENT 'DATA INSERIMENTO',
  `CONNORAINS` varchar(8) COLLATE latin1_general_cs NOT NULL COMMENT 'ORA INSERIMENTO',
  `CONNUTEMOD` varchar(20) COLLATE latin1_general_cs NOT NULL COMMENT 'UTENTE MODIFICA',
  `CONNDATAMOD` varchar(8) COLLATE latin1_general_cs NOT NULL COMMENT 'DATA MODIFICA',
  `CONNORAMOD` varchar(8) COLLATE latin1_general_cs NOT NULL COMMENT 'ORA MODIFICA',
  `CONNUTEANN` varchar(20) COLLATE latin1_general_cs NOT NULL COMMENT 'UTENTE ANNULLAMENTO',
  `CONNDATAANN` varchar(8) COLLATE latin1_general_cs NOT NULL COMMENT 'DATA ANNULLAMENTO',
  `CONNORAANN` varchar(8) COLLATE latin1_general_cs NOT NULL COMMENT 'ORA ANNULLAMENTO',
  PRIMARY KEY (`ROWID`),
  KEY `ORGKEY` (`ORGKEY`),
  KEY `I_PRONUM` (`PRONUM`,`PROPAR`),
  KEY `I_PRONUMPARENT` (`PRONUMPARENT`,`PROPARPARENT`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ORGNODE` (
  `ROWID` int(11) NOT NULL AUTO_INCREMENT,
  `ORGKEY` varchar(30) COLLATE latin1_general_cs NOT NULL,
  `PRONUM` double NOT NULL,
  `PROPAR` varchar(2) COLLATE latin1_general_cs NOT NULL,
  `NODEMETA` text COLLATE latin1_general_cs NOT NULL,
  PRIMARY KEY (`ROWID`),
  KEY `ORGKEY` (`ORGKEY`),
  KEY `I_PRONUM` (`PRONUM`,`PROPAR`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `PAKDOC` (
  `ROWID` int(11) NOT NULL AUTO_INCREMENT,
  `PROPAK` varchar(40) COLLATE latin1_general_cs NOT NULL,
  `PRONUM` double NOT NULL,
  `PROPAR` varchar(2) COLLATE latin1_general_cs NOT NULL,
  `PAKTIPO` varchar(8) COLLATE latin1_general_cs NOT NULL,
  PRIMARY KEY (`ROWID`),
  UNIQUE KEY `I_PROPAKPRONUM` (`PROPAK`,`PRONUM`,`PROPAR`),
  KEY `PROPAK` (`PROPAK`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `PRASTA` (
  `ROWID` int(11) NOT NULL AUTO_INCREMENT,
  `STANUM` varchar(20) COLLATE latin1_general_cs NOT NULL,
  `STANRC` varchar(10) COLLATE latin1_general_cs NOT NULL,
  `STADES` varchar(70) COLLATE latin1_general_cs NOT NULL,
  `STAPRO` varchar(6) COLLATE latin1_general_cs NOT NULL,
  `STAPAS` varchar(6) COLLATE latin1_general_cs NOT NULL,
  `STADIN` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `STADFI` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `STAPAK` varchar(40) COLLATE latin1_general_cs NOT NULL COMMENT 'CHIAVE PASSO PRATICA ELABORATO',
  `STADEX` text COLLATE latin1_general_cs NOT NULL COMMENT 'DESCRIZIONE PUBBLICA',
  `STAPST` smallint(6) NOT NULL COMMENT 'FLAG PASSO PUBBLICO',
  `STACOD` int(11) NOT NULL COMMENT 'Codice stato',
  `STAFLAG` varchar(50) COLLATE latin1_general_cs NOT NULL COMMENT 'Flag stato',
  PRIMARY KEY (`ROWID`),
  KEY `I_STANRC` (`STANRC`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `PREALB` (
  `ROWID` int(11) NOT NULL AUTO_INCREMENT,
  `ALBANN` varchar(4) COLLATE latin1_general_cs NOT NULL,
  `ALBNUM` varchar(6) COLLATE latin1_general_cs NOT NULL,
  `ALBDAR` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `ALBCMI` varchar(6) COLLATE latin1_general_cs NOT NULL,
  `ALBDMI` varchar(100) COLLATE latin1_general_cs NOT NULL,
  `ALBVIA` varchar(100) COLLATE latin1_general_cs NOT NULL,
  `ALBCAP` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `ALBCIT` varchar(40) COLLATE latin1_general_cs NOT NULL,
  `ALBPRO` varchar(4) COLLATE latin1_general_cs NOT NULL,
  `ALBMAIL` text COLLATE latin1_general_cs NOT NULL,
  `ALBFLRIC` smallint(6) NOT NULL,
  `ALBANP` varchar(4) COLLATE latin1_general_cs NOT NULL,
  `ALBNUP` varchar(20) COLLATE latin1_general_cs NOT NULL,
  `ALBDAP` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `ALBTIP` varchar(2) COLLATE latin1_general_cs NOT NULL,
  `ALBOGG__1` varchar(78) COLLATE latin1_general_cs NOT NULL,
  `ALBOGG__2` varchar(78) COLLATE latin1_general_cs NOT NULL,
  `ALBOGG__3` varchar(78) COLLATE latin1_general_cs NOT NULL,
  `ALBNDO` varchar(18) COLLATE latin1_general_cs NOT NULL,
  `ALBGIO` double NOT NULL,
  `ALBDPU` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `ALBAPU` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `ALBNOT` text COLLATE latin1_general_cs NOT NULL,
  `ALBCME` varchar(6) COLLATE latin1_general_cs NOT NULL,
  `ALBLOG` varchar(100) COLLATE latin1_general_cs NOT NULL,
  `ALBIND` varchar(18) COLLATE latin1_general_cs NOT NULL,
  `ALBALL` double NOT NULL,
  `ALBNAL__1` varchar(18) COLLATE latin1_general_cs NOT NULL,
  `ALBNAL__2` varchar(18) COLLATE latin1_general_cs NOT NULL,
  `ALBNAL__3` varchar(18) COLLATE latin1_general_cs NOT NULL,
  `ALBNAL__4` varchar(18) COLLATE latin1_general_cs NOT NULL,
  `ALBNAL__5` varchar(18) COLLATE latin1_general_cs NOT NULL,
  `ALBNAL__6` varchar(18) COLLATE latin1_general_cs NOT NULL,
  `ALBNAL__7` varchar(18) COLLATE latin1_general_cs NOT NULL,
  `ALBNAL__8` varchar(18) COLLATE latin1_general_cs NOT NULL,
  `ALBNAL__9` varchar(18) COLLATE latin1_general_cs NOT NULL,
  `ALBNAL__10` varchar(18) COLLATE latin1_general_cs NOT NULL,
  `ALBANL` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `ALBLO1` varchar(100) COLLATE latin1_general_cs NOT NULL,
  `ALBLNK` text COLLATE latin1_general_cs NOT NULL,
  `ALBDAT` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `ALBFAN` double NOT NULL,
  `ALBESE` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `ALBCER` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `DATCER` varchar(8) COLLATE latin1_general_cs NOT NULL COMMENT 'DATA CERTIFICATO',
  `NOMCER` varchar(200) COLLATE latin1_general_cs NOT NULL COMMENT 'NOME CERTIFICATORE',
  `DATAVV` varchar(8) COLLATE latin1_general_cs NOT NULL COMMENT 'DATA AVVISO',
  `NOMAVV` varchar(200) COLLATE latin1_general_cs NOT NULL COMMENT 'NOME DI CHI EFFETTUA L''AVVISO',
  `DATRCN` varchar(8) COLLATE latin1_general_cs NOT NULL COMMENT 'DATA RICONSEGNA DOCUMENTO',
  `NOMMES` varchar(200) COLLATE latin1_general_cs NOT NULL COMMENT 'NOME DEL MESSO CHE HA RICONSEGNATO IL DOCUMENTO',
  `PROUTE` varchar(20) COLLATE latin1_general_cs NOT NULL,
  `PROUOF` varchar(4) COLLATE latin1_general_cs NOT NULL,
  `DESCOD` varchar(6) COLLATE latin1_general_cs NOT NULL,
  `DESNOM` varchar(100) COLLATE latin1_general_cs NOT NULL,
  `DESCUF` varchar(50) COLLATE latin1_general_cs NOT NULL,
  `ALBRXTIME` varchar(16) COLLATE latin1_general_cs NOT NULL,
  `ALBRXUTE` varchar(50) COLLATE latin1_general_cs NOT NULL,
  `ALBTAN` varchar(16) COLLATE latin1_general_cs NOT NULL,
  `ALBMOTANN` text COLLATE latin1_general_cs NOT NULL,
  `ALBOGGETTO` text COLLATE latin1_general_cs NOT NULL,
  `EXTCLASS` varchar(10) COLLATE latin1_general_cs NOT NULL COMMENT 'Riferimento Applicativo Esterno Classe',
  `EXTSTRKEY` varchar(20) COLLATE latin1_general_cs NOT NULL COMMENT 'Riferimento applicativo Estrno chiave Stringa',
  `EXTINTKEY` int(11) NOT NULL COMMENT 'Riferimento applicativo esterno numerico',
  `EXTNOTE` text COLLATE latin1_general_cs NOT NULL COMMENT 'Note Inserimento da Applicativo Esterno',
  `ALBDESCSE` text COLLATE latin1_general_cs NOT NULL COMMENT 'Descrizione Servizio proponente',
  PRIMARY KEY (`ROWID`),
  KEY `I_ALBNUM` (`ALBANN`,`ALBNUM`),
  KEY `I_ALBDAR` (`ALBDAR`),
  KEY `I_ALBNUP` (`ALBANP`,`ALBNUP`),
  KEY `I_ALBDMI` (`ALBDMI`),
  KEY `I_ALBNDO` (`ALBNDO`),
  KEY `I_ALBTIP` (`ALBTIP`),
  KEY `I_ALBDPU` (`ALBDPU`),
  KEY `I_ALBIND` (`ALBIND`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `PROCONSER` (
  `ROWID` int(11) NOT NULL AUTO_INCREMENT,
  `PRONUM` double NOT NULL,
  `PROPAR` varchar(2) COLLATE latin1_general_cs NOT NULL,
  `ROWID_ANAPRO` int(11) NOT NULL,
  `PROGVERSAMENTO` int(11) NOT NULL,
  `DATAVERSAMENTO` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `ORAVERSAMENTO` varchar(8) COLLATE latin1_general_cs NOT NULL COMMENT 'ORA VERSAMENTO',
  `MOTIVOVERSAMENTO` varchar(20) COLLATE latin1_general_cs NOT NULL,
  `ESITOVERSAMENTO` varchar(20) COLLATE latin1_general_cs NOT NULL,
  `DOCVERSAMENTO` text COLLATE latin1_general_cs NOT NULL,
  `DOCESITO` text COLLATE latin1_general_cs NOT NULL,
  `COD_UNITA_DOCUMENTARIA` varchar(10) COLLATE latin1_general_cs NOT NULL,
  `CONSERVATORE` text COLLATE latin1_general_cs NOT NULL,
  `VERSIONE` varchar(10) COLLATE latin1_general_cs NOT NULL,
  `CODICEERRORE` varchar(40) COLLATE latin1_general_cs NOT NULL,
  `MESSAGGIOERRORE` text COLLATE latin1_general_cs NOT NULL,
  `CHIAVEVERSAMENTO` text COLLATE latin1_general_cs NOT NULL,
  `UUIDSIP` varchar(64) COLLATE latin1_general_cs NOT NULL,
  `UTENTEVERSAMENTO` varchar(20) COLLATE latin1_general_cs NOT NULL,
  `FLSTORICO` smallint(6) NOT NULL,
  `ESITOCONSERVAZIONE` varchar(20) COLLATE latin1_general_cs NOT NULL COMMENT 'CODICE ESITO CONSERVAZIONE',
  `CODICEESITOCONSERVAZIONE` varchar(20) COLLATE latin1_general_cs NOT NULL COMMENT 'CODICE ESITO DEL  CONSERVATORE',
  `MESSAGGIOCONSERVAZIONE` text COLLATE latin1_general_cs NOT NULL COMMENT 'MESSAGGIO DEL CONSERVATORE',
  `DOCRDV` text COLLATE latin1_general_cs NOT NULL COMMENT 'FILE ESITO RDV',
  `NOTECONSER` text COLLATE latin1_general_cs NOT NULL COMMENT 'NOTE AZIONI MANUALI UTENTE',
  `PENDINGUUID` varchar(64) COLLATE latin1_general_cs NOT NULL COMMENT 'UUID DELLA PENDING REQUEST',
  PRIMARY KEY (`ROWID`),
  KEY `PRONUM_PROPAR` (`PRONUM`,`PROPAR`),
  KEY `PENDINGUUIDKEY` (`PENDINGUUID`),
  KEY `UUIDKEY` (`UUIDSIP`),
  KEY `ROWID_ANAPRO` (`ROWID_ANAPRO`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `PRODAG` (
  `ROWID` int(11) NOT NULL AUTO_INCREMENT,
  `DAGNUM` varchar(20) COLLATE latin1_general_cs NOT NULL,
  `DAGCOD` varchar(6) COLLATE latin1_general_cs NOT NULL,
  `DAGDES` text COLLATE latin1_general_cs NOT NULL COMMENT 'DESCRIZIONE CAMPO',
  `DAGSEQ` double NOT NULL,
  `DAGSFL` double NOT NULL,
  `DAGKEY` varchar(60) COLLATE latin1_general_cs NOT NULL,
  `DAGALIAS` varchar(60) COLLATE latin1_general_cs NOT NULL COMMENT 'NOME CAMPO PER DIZIONARIO',
  `DAGVAL` text COLLATE latin1_general_cs NOT NULL,
  `DAGSET` varchar(40) COLLATE latin1_general_cs NOT NULL,
  `DAGFIN__1` double NOT NULL,
  `DAGFIN__2` double NOT NULL,
  `DAGFIN__3` double NOT NULL,
  `DAGFIA__1` varchar(100) COLLATE latin1_general_cs NOT NULL,
  `DAGFIA__2` varchar(100) COLLATE latin1_general_cs NOT NULL,
  `DAGFIA__3` varchar(100) COLLATE latin1_general_cs NOT NULL,
  `DAGPAK` varchar(40) COLLATE latin1_general_cs NOT NULL,
  `DAGTIP` varchar(20) COLLATE latin1_general_cs NOT NULL COMMENT 'TIPO DATO',
  `DAGCTR` text COLLATE latin1_general_cs NOT NULL COMMENT 'FORMULA CONTROLLO',
  `DAGNOT` text COLLATE latin1_general_cs NOT NULL COMMENT 'NOTE',
  `DAGLAB` text COLLATE latin1_general_cs NOT NULL COMMENT 'LABEL PER INPUT',
  `DAGTIC` varchar(50) COLLATE latin1_general_cs NOT NULL COMMENT 'TIPO INPUT',
  `DAGROL` int(11) NOT NULL COMMENT 'CAMPO READONLY',
  `DAGVCA` text COLLATE latin1_general_cs NOT NULL COMMENT 'TIPO VALIDITA',
  `DAGREV` text COLLATE latin1_general_cs NOT NULL COMMENT 'REGAULAR EXPRESSION VALIDITA',
  `DAGLEN` varchar(50) COLLATE latin1_general_cs NOT NULL COMMENT 'Dimensione Campo',
  `DAGDIM` varchar(50) COLLATE latin1_general_cs NOT NULL COMMENT 'DIMENSIONE CAMPO',
  `DAGDIZ` varchar(10) COLLATE latin1_general_cs NOT NULL COMMENT 'FLAG PER VALORE CAMPO',
  `DAGACA` int(11) NOT NULL COMMENT 'FLAG PER ANDARE A CAPO CHECKBOX',
  `DAGDAT` text COLLATE latin1_general_cs NOT NULL COMMENT 'valore inserito',
  `DAGPRI` int(11) NOT NULL COMMENT 'valore priorita',
  PRIMARY KEY (`ROWID`),
  KEY `I_DAGNUM` (`DAGNUM`),
  KEY `I_DAGPAK` (`DAGPAK`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `PRODOCPROT` (
  `ROWID` int(11) NOT NULL AUTO_INCREMENT,
  `SORGNUM` double NOT NULL COMMENT 'NUMERO DOCUMENTOSORGENTE',
  `SORGTIP` varchar(2) COLLATE latin1_general_cs NOT NULL COMMENT 'TIPO DOCUMENTO SORGENTE',
  `DESTNUM` double NOT NULL COMMENT 'NUMERO PROTOCOLLO DESTINO',
  `DESTTIP` varchar(2) COLLATE latin1_general_cs NOT NULL COMMENT 'TIPO PROTOCOLLO DESTINO',
  PRIMARY KEY (`ROWID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `PRODST` (
  `ROWID` int(11) NOT NULL AUTO_INCREMENT,
  `DSTSET` varchar(50) COLLATE latin1_general_cs NOT NULL COMMENT 'CHIAVE SET',
  `DSTTRS` int(11) NOT NULL COMMENT 'TRASMESSO',
  `DSTDES` varchar(100) COLLATE latin1_general_cs NOT NULL COMMENT 'DESCRIZIONE SET',
  PRIMARY KEY (`ROWID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs COMMENT='CLASSIFICAZIONE SET DATI AGGIUNTIVI';
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `PROEXTCONSER` (
  `ROW_ID` int(11) NOT NULL AUTO_INCREMENT,
  `ESTENSIONE` varchar(10) COLLATE latin1_general_cs NOT NULL,
  `DESCRIZIONE` text COLLATE latin1_general_cs NOT NULL,
  `EXTPROTO` tinyint(4) NOT NULL COMMENT 'Estensione allegati protocollabili',
  `EXTCONSER` tinyint(4) NOT NULL COMMENT 'Estensione allegati conservabili',
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
CREATE TABLE `PROGES` (
  `ROWID` int(11) NOT NULL AUTO_INCREMENT,
  `GESNUM` varchar(20) COLLATE latin1_general_cs NOT NULL,
  `GESREP` varchar(10) COLLATE latin1_general_cs NOT NULL,
  `GESKEY` varchar(30) COLLATE latin1_general_cs NOT NULL COMMENT 'Chiave fascicolo',
  `GESPRA` varchar(10) COLLATE latin1_general_cs NOT NULL COMMENT 'N. PROCEDIMENTO SPORTELLO ONLINE',
  `GESPRO` varchar(6) COLLATE latin1_general_cs NOT NULL,
  `GESSET` varchar(6) COLLATE latin1_general_cs NOT NULL,
  `GESSER` varchar(6) COLLATE latin1_general_cs NOT NULL,
  `GESOPE` varchar(6) COLLATE latin1_general_cs NOT NULL,
  `GESRES` varchar(6) COLLATE latin1_general_cs NOT NULL,
  `GESDRE` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `GESDRI` varchar(8) COLLATE latin1_general_cs NOT NULL COMMENT 'DATA RICEZIONE PRATICA',
  `GESORA` varchar(8) COLLATE latin1_general_cs NOT NULL COMMENT 'ORA RICEZIONE PRATICA',
  `GESDCH` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `GESNPR` double NOT NULL,
  `GESPAR` varchar(2) COLLATE latin1_general_cs NOT NULL,
  `GESDES` varchar(70) COLLATE latin1_general_cs NOT NULL,
  `GESCHI` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `GESDCO` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `GESDPR` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `GESGIO` double NOT NULL,
  `GESGGG` double NOT NULL,
  `GESNRC` varchar(10) COLLATE latin1_general_cs NOT NULL,
  `GESESI` varchar(2) COLLATE latin1_general_cs NOT NULL,
  `GESTSP` double NOT NULL COMMENT 'SPORTELLO ON LINE DI PROVENIENZA',
  `GESSPA` int(11) NOT NULL COMMENT 'CODICE SPORTELLO AGGREGATO DI COMPETENZA',
  `GESNOT` text COLLATE latin1_general_cs NOT NULL COMMENT 'Note pratica',
  `GESPRE` varchar(20) COLLATE latin1_general_cs NOT NULL COMMENT 'N antecedente',
  `GESCTR` varchar(50) COLLATE latin1_general_cs NOT NULL COMMENT 'Codice procedura di controllo',
  `GESMETA` text COLLATE latin1_general_cs,
  `GESOGG` text COLLATE latin1_general_cs NOT NULL COMMENT 'OGGETTO',
  `GESCLOSE` varchar(40) COLLATE latin1_general_cs NOT NULL COMMENT 'Propak del passo che chiude la pratica',
  `GESUFFRES` varchar(4) COLLATE latin1_general_cs NOT NULL COMMENT 'UFFICIO DEL RESPONSABILE',
  `GESPROUTE` varchar(20) COLLATE latin1_general_cs NOT NULL,
  `GESPROUFF` varchar(4) COLLATE latin1_general_cs NOT NULL,
  PRIMARY KEY (`ROWID`),
  UNIQUE KEY `GESNUM` (`GESNUM`),
  KEY `I_GESPRO` (`GESPRO`,`GESDRE`),
  KEY `I_GESDRE` (`GESDRE`),
  KEY `I_GESDCH` (`GESDCH`),
  KEY `I_GESSET` (`GESSET`,`GESSER`,`GESOPE`,`GESRES`),
  KEY `I_GESRES` (`GESRES`),
  KEY `I_GESCHI` (`GESCHI`),
  KEY `I_GESNRC` (`GESNRC`),
  KEY `I_GESKEY` (`GESKEY`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs COMMENT='FASCICOLO ELETTRONICO';
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `PROGSERIE` (
  `ROWID` int(11) NOT NULL AUTO_INCREMENT,
  `CODICE` int(11) NOT NULL,
  `ANNO` int(11) NOT NULL,
  `PROGRESSIVO` int(11) NOT NULL,
  `DATAPROGRESSIVO` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `FLAG_CHIUSO` smallint(6) NOT NULL,
  `CODUTE` varchar(20) COLLATE latin1_general_cs NOT NULL,
  `DATAOPER` date NOT NULL,
  `TIMEOPER` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `FLAG_DIS` smallint(6) NOT NULL,
  PRIMARY KEY (`ROWID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `PROMAIL` (
  `ROWID` int(11) NOT NULL AUTO_INCREMENT,
  `PRONUM` double NOT NULL,
  `PROPAR` varchar(2) COLLATE latin1_general_cs NOT NULL,
  `IDMAIL` text COLLATE latin1_general_cs NOT NULL,
  `SENDREC` varchar(2) COLLATE latin1_general_cs NOT NULL,
  PRIMARY KEY (`ROWID`),
  KEY `I_PRONUM` (`PRONUM`,`PROPAR`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs COMMENT='TABELLA LEGAME PROTxx.ANAPRO - ITALWEBxx.MAIL_ARCHIVIO';
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `PROMITAGG` (
  `ROWID` int(11) NOT NULL AUTO_INCREMENT,
  `PRONUM` double NOT NULL,
  `PROPAR` varchar(2) COLLATE latin1_general_cs NOT NULL,
  `PRODESCOD` varchar(6) COLLATE latin1_general_cs NOT NULL,
  `PRODESUFF` varchar(4) COLLATE latin1_general_cs NOT NULL,
  `PRONOM` varchar(300) COLLATE latin1_general_cs NOT NULL,
  `PROIND` varchar(300) COLLATE latin1_general_cs NOT NULL,
  `PROCAP` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `PROCIT` varchar(40) COLLATE latin1_general_cs NOT NULL,
  `PROPRO` varchar(4) COLLATE latin1_general_cs NOT NULL,
  `PROMAIL` text COLLATE latin1_general_cs NOT NULL COMMENT 'EMAIL DESTINATARI',
  `PROIDMAILDEST` text COLLATE latin1_general_cs NOT NULL,
  `PROMITMIGRA` varchar(20) COLLATE latin1_general_cs NOT NULL,
  `PROFIS` varchar(16) COLLATE latin1_general_cs NOT NULL,
  PRIMARY KEY (`ROWID`),
  KEY `I_PROMITAGGNUM` (`PRONUM`,`PROPAR`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `PROMITAGGSAVE` (
  `ROWID` int(11) NOT NULL AUTO_INCREMENT,
  `PRONUM` double NOT NULL,
  `PROPAR` varchar(2) COLLATE latin1_general_cs NOT NULL,
  `PRODESCOD` varchar(6) COLLATE latin1_general_cs NOT NULL,
  `PRODESUFF` varchar(4) COLLATE latin1_general_cs NOT NULL,
  `PRONOM` varchar(300) COLLATE latin1_general_cs NOT NULL,
  `PROIND` varchar(300) COLLATE latin1_general_cs NOT NULL,
  `PROCAP` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `PROCIT` varchar(40) COLLATE latin1_general_cs NOT NULL,
  `PROPRO` varchar(4) COLLATE latin1_general_cs NOT NULL,
  `SAVEDATA` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `SAVEORA` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `SAVEUTENTE` varchar(20) COLLATE latin1_general_cs NOT NULL,
  `PROMAIL` text COLLATE latin1_general_cs NOT NULL COMMENT 'EMAIL DESTINATARI',
  `PROIDMAILDEST` text COLLATE latin1_general_cs NOT NULL,
  `PROMITMIGRA` varchar(20) COLLATE latin1_general_cs NOT NULL,
  `PROFIS` varchar(16) COLLATE latin1_general_cs NOT NULL,
  PRIMARY KEY (`ROWID`),
  KEY `I_PROMITAGGNUM` (`PRONUM`,`PROPAR`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `PROPAS` (
  `ROWID` int(11) NOT NULL AUTO_INCREMENT,
  `PRONUM` varchar(20) COLLATE latin1_general_cs NOT NULL,
  `PROPRO` varchar(6) COLLATE latin1_general_cs NOT NULL,
  `PRORES` varchar(6) COLLATE latin1_general_cs NOT NULL,
  `PROSEQ` double NOT NULL,
  `PRORPA` varchar(6) COLLATE latin1_general_cs NOT NULL,
  `PROUOP` varchar(6) COLLATE latin1_general_cs NOT NULL,
  `PROSET` varchar(6) COLLATE latin1_general_cs NOT NULL,
  `PROSER` varchar(6) COLLATE latin1_general_cs NOT NULL,
  `PROGIO` double NOT NULL,
  `PROINI` varchar(10) COLLATE latin1_general_cs NOT NULL,
  `PROFIN` varchar(10) COLLATE latin1_general_cs NOT NULL,
  `PROANN` varchar(80) COLLATE latin1_general_cs NOT NULL,
  `PRORIS` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `PROTPA` varchar(80) COLLATE latin1_general_cs NOT NULL,
  `PROCLT` varchar(6) COLLATE latin1_general_cs NOT NULL,
  `PROIMP` double NOT NULL,
  `PROSCA` varchar(10) COLLATE latin1_general_cs NOT NULL,
  `PROCTP` varchar(6) COLLATE latin1_general_cs NOT NULL,
  `PRODTP` varchar(60) COLLATE latin1_general_cs NOT NULL,
  `PRODPA` varchar(80) COLLATE latin1_general_cs NOT NULL,
  `PROITK` varchar(30) COLLATE latin1_general_cs NOT NULL,
  `PROPAK` varchar(40) COLLATE latin1_general_cs NOT NULL,
  `PROCDR` varchar(6) COLLATE latin1_general_cs NOT NULL,
  `PRONRE` varchar(200) COLLATE latin1_general_cs NOT NULL,
  `PRODRE` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `PRODER` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `PROESR` varchar(20) COLLATE latin1_general_cs NOT NULL,
  `PROPST` smallint(6) NOT NULL,
  `PROQST` smallint(6) NOT NULL,
  `PRODAT` smallint(6) NOT NULL COMMENT 'Flag raccolta Dati',
  `PROCOM` double NOT NULL COMMENT 'Flag Comunicazione',
  `PROPUB` double NOT NULL COMMENT 'Flag Passo Suap',
  `PRODOW` smallint(6) NOT NULL COMMENT 'FLAG PASSO DOWNLOAD',
  `PROUPL` smallint(6) NOT NULL COMMENT 'FLAG UPLOAD SINGOLO',
  `PROVPA` varchar(40) COLLATE latin1_general_cs NOT NULL,
  `PROVPN` varchar(40) COLLATE latin1_general_cs NOT NULL COMMENT 'VAI AL PASSO RISP NEGATIVA',
  `PROCTR` varchar(50) COLLATE latin1_general_cs NOT NULL,
  `PROIRE` smallint(6) NOT NULL,
  `PROMLT` smallint(6) NOT NULL,
  `PRODRR` smallint(6) NOT NULL COMMENT 'FLAG RAPPORTO COMPLETO',
  `PROZIP` double NOT NULL COMMENT 'FLAG INVIO INFOCAMERE COMUNICA',
  `PRODIS` double NOT NULL COMMENT 'FLAG DISTINTA RICHIESTA',
  `PROSTA` smallint(6) NOT NULL,
  `PROTIM` smallint(6) NOT NULL,
  `PROALL` text COLLATE latin1_general_cs NOT NULL COMMENT 'PRESENZA ALLEGATI',
  `PROINT` double NOT NULL COMMENT 'Flag invia mail intestatario',
  `PROSTATO` int(11) NOT NULL,
  `PROSTAP` int(11) NOT NULL COMMENT 'stato passo aperto',
  `PROSTCH` int(11) NOT NULL COMMENT 'stato passo chiuso',
  `PROTBA` text COLLATE latin1_general_cs NOT NULL COMMENT 'id testo base',
  `PROPART` int(11) NOT NULL COMMENT 'FLAG PUBBLICA ARTICOLO',
  `PROPTIT` text COLLATE latin1_general_cs NOT NULL COMMENT 'Titolo Articolo',
  `PROPDADATA` varchar(8) COLLATE latin1_general_cs NOT NULL COMMENT 'Pubblica da data',
  `PROPPDAORA` varchar(6) COLLATE latin1_general_cs NOT NULL COMMENT 'Pubblica da ora',
  `PROPADDATA` varchar(8) COLLATE latin1_general_cs NOT NULL COMMENT 'Pubblica fino a data',
  `PROPADORA` varchar(6) COLLATE latin1_general_cs NOT NULL COMMENT 'Pubblica fino ad ora',
  `PROPCONT` text COLLATE latin1_general_cs NOT NULL COMMENT 'Pubblica ContenutoContenuto',
  `PROPFLALLE` int(11) NOT NULL COMMENT 'Pubblica Allegati',
  `PROPUSER` text COLLATE latin1_general_cs NOT NULL COMMENT 'Utente Pubblicazione',
  `PROPGRUP` text COLLATE latin1_general_cs NOT NULL COMMENT 'Gruppo Pubblicazione',
  `PROPPASS` text COLLATE latin1_general_cs NOT NULL COMMENT 'Password Pubblicazione',
  `PROUTEADD` varchar(50) COLLATE latin1_general_cs NOT NULL COMMENT 'utente che aggiunge un passo',
  `PRODATEADD` text COLLATE latin1_general_cs NOT NULL COMMENT 'Data e ora aggiunta  del passo',
  `PROORAADD` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `PROUTEEDIT` varchar(50) COLLATE latin1_general_cs NOT NULL COMMENT 'Utente che modifica il passo',
  `PRODATEEDIT` text COLLATE latin1_general_cs NOT NULL COMMENT 'Data e ora modifica  del passo',
  `PROORAEDIT` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `PROVISIBILITA` varchar(50) COLLATE latin1_general_cs NOT NULL COMMENT 'Visibilita passo',
  `PRORIN` varchar(10) COLLATE latin1_general_cs NOT NULL COMMENT 'N. richiesta di integrazione',
  `PROCAR` varchar(100) COLLATE latin1_general_cs NOT NULL COMMENT 'categoria Articolo',
  `PROCDE` varchar(6) COLLATE latin1_general_cs NOT NULL COMMENT 'Destinatario default comunicazione Partenza',
  `PRONODE` double NOT NULL,
  `PROUFFRES` varchar(4) COLLATE latin1_general_cs NOT NULL COMMENT 'UFFICIO DEL RESPONSABILE',
  `PASPRO` double NOT NULL COMMENT 'Codice su ANAPRO',
  `PASPAR` varchar(2) COLLATE latin1_general_cs NOT NULL COMMENT 'Tipo su ANAPRO',
  `PASPROUTE` varchar(20) COLLATE latin1_general_cs NOT NULL,
  `PASPROUFF` varchar(4) COLLATE latin1_general_cs NOT NULL,
  PRIMARY KEY (`ROWID`),
  KEY `I_PROPRO` (`PROPRO`),
  KEY `I_PRORES` (`PRORES`),
  KEY `I_PRORPA` (`PRORPA`),
  KEY `I_PROUOP` (`PROUOP`),
  KEY `I_PROPAK` (`PROPAK`),
  KEY `I_PASPRO` (`PASPRO`,`PASPAR`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `PROREGISTROARC` (
  `ROW_ID` int(11) NOT NULL AUTO_INCREMENT,
  `ROWID_REGISTRO` int(11) NOT NULL,
  `ROWID_ANAPRO` int(11) NOT NULL,
  `ANNO` smallint(6) NOT NULL,
  `PROGRESSIVO` int(11) NOT NULL,
  PRIMARY KEY (`ROW_ID`),
  UNIQUE KEY `I_PROREGISTRO_K001` (`ROWID_ANAPRO`),
  UNIQUE KEY `I_PROREGISTROARC_K002` (`ROWID_REGISTRO`,`ANNO`,`PROGRESSIVO`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `PROREPDOC` (
  `ROWID` int(11) NOT NULL AUTO_INCREMENT,
  `PRONUM` double NOT NULL COMMENT 'NUM PROTO',
  `PROPAR` varchar(2) COLLATE latin1_general_cs NOT NULL COMMENT 'TIPO PROTO',
  `CLASSE` varchar(20) COLLATE latin1_general_cs NOT NULL COMMENT 'TIPO APP ESTERNO',
  `CHIAVE` varchar(80) COLLATE latin1_general_cs NOT NULL COMMENT 'CON DOCUMENTO DA APP ESTERNO',
  `CODREP` varchar(10) COLLATE latin1_general_cs NOT NULL COMMENT 'TIPO REPERTORIO',
  `PROGREP` int(11) NOT NULL COMMENT 'REPERTORIO',
  `ANNOREP` int(11) NOT NULL COMMENT 'ANNO REPERTORIO',
  PRIMARY KEY (`ROWID`),
  KEY `I_PROREPDOC_PRONUM` (`PRONUM`,`PROPAR`),
  KEY `I_PROREPODOC_APP` (`CLASSE`,`CHIAVE`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `PROUPDATECONSER` (
  `ROWID` int(11) NOT NULL AUTO_INCREMENT,
  `PRONUM` double NOT NULL,
  `PROPAR` varchar(2) COLLATE latin1_general_cs NOT NULL,
  `ROWID_ANAPRO` int(11) NOT NULL,
  `DATAVARIAZIONE` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `ORAVARIAZIONE` varchar(8) COLLATE latin1_general_cs NOT NULL COMMENT 'ORA VARIAZIONE',
  `UPDATETIPO` text COLLATE latin1_general_cs NOT NULL,
  `CHIAVEVERSAMENTO` text COLLATE latin1_general_cs NOT NULL,
  `FLESEGUITO` smallint(6) NOT NULL,
  PRIMARY KEY (`ROWID`),
  KEY `PRONUM_PROPAR` (`PRONUM`,`PROPAR`),
  KEY `ROWID_ANAPRO` (`ROWID_ANAPRO`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `RAGIONITRASM` (
  `ROW_ID` int(11) NOT NULL AUTO_INCREMENT,
  `CODICE` smallint(6) NOT NULL,
  `DESCRAGIONE` varchar(32) COLLATE latin1_general_cs NOT NULL,
  `NOTE` text COLLATE latin1_general_cs NOT NULL,
  `FL_PRIVACY` smallint(6) NOT NULL,
  PRIMARY KEY (`ROW_ID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `REGISTRO` (
  `ROWID` int(11) NOT NULL AUTO_INCREMENT,
  `ANNO` varchar(4) COLLATE latin1_general_cs NOT NULL COMMENT 'Anno',
  `PROGRESSIVO` int(11) NOT NULL COMMENT 'Progressivo',
  `DATA` varchar(8) COLLATE latin1_general_cs NOT NULL COMMENT 'Data',
  `PROTOCOLLONUM` double NOT NULL COMMENT 'Numero Protocollo',
  `PROTOCOLLOANNO` smallint(6) NOT NULL,
  `PROTOCOLLOTIPO` varchar(1) COLLATE latin1_general_cs NOT NULL COMMENT 'Tipo Protocollo',
  `CODMIT` varchar(6) COLLATE latin1_general_cs NOT NULL COMMENT 'Codice Mittente',
  `MITTENTE` varchar(100) COLLATE latin1_general_cs NOT NULL COMMENT 'Nominativo Mittente',
  `CODATTO` int(11) NOT NULL COMMENT 'Codice Atto',
  `ATTO` varchar(100) COLLATE latin1_general_cs NOT NULL COMMENT 'Atto',
  `PROTOCOLLOATTO` varchar(10) COLLATE latin1_general_cs NOT NULL COMMENT 'Protocollo Atto',
  `DATAATTO` varchar(8) COLLATE latin1_general_cs NOT NULL COMMENT 'Data Atto',
  `CODMESSO` varchar(6) COLLATE latin1_general_cs NOT NULL COMMENT 'Codice Messo',
  `MESSO` varchar(100) COLLATE latin1_general_cs NOT NULL COMMENT 'Messo',
  `CODDIR` int(11) NOT NULL COMMENT 'Codice Diritto',
  `DIRITTI` varchar(100) COLLATE latin1_general_cs NOT NULL COMMENT 'Diritto',
  `IMPORTODIR` double NOT NULL COMMENT 'Importo Diritto',
  `CODSPESA` int(11) NOT NULL COMMENT 'Codice Spese',
  `SPESE` varchar(100) COLLATE latin1_general_cs NOT NULL COMMENT 'Spesa',
  `IMPORTOSPESE` double NOT NULL COMMENT 'Importo Spesa',
  `CODDEST` varchar(6) COLLATE latin1_general_cs NOT NULL COMMENT 'Codice Destinatario',
  `DESTINATARIO` varchar(100) COLLATE latin1_general_cs NOT NULL COMMENT 'Nominativo Destinatario',
  `INDIRIZZO` varchar(100) COLLATE latin1_general_cs NOT NULL COMMENT 'Indirizzo',
  `CIVICO` varchar(10) COLLATE latin1_general_cs NOT NULL,
  `CAP` varchar(5) COLLATE latin1_general_cs NOT NULL COMMENT 'CAP',
  `COMUNE` varchar(40) COLLATE latin1_general_cs NOT NULL COMMENT 'Comune',
  `PROVINCIA` varchar(4) COLLATE latin1_general_cs NOT NULL COMMENT 'Provincia',
  `TELEFONO` varchar(20) COLLATE latin1_general_cs NOT NULL COMMENT 'Telefono',
  `CODFISC` varchar(16) COLLATE latin1_general_cs NOT NULL COMMENT 'Codice fiscale',
  `CODRIC` int(11) NOT NULL COMMENT 'codice Ricevente',
  `RICEVENTE` varchar(100) COLLATE latin1_general_cs NOT NULL COMMENT 'Nominativo Ricevente',
  `CODQUA` int(11) NOT NULL COMMENT 'Codice Qualifica',
  `QUALIFICA` varchar(100) COLLATE latin1_general_cs NOT NULL COMMENT 'Qualifica',
  `DATANOTIFICA` varchar(8) COLLATE latin1_general_cs NOT NULL COMMENT 'Data notifica',
  `DATANOTRACC` varchar(8) COLLATE latin1_general_cs NOT NULL COMMENT 'Data notifica raccomandata',
  `CODESITO` int(11) NOT NULL COMMENT 'Codice Esito',
  `ESITO` text COLLATE latin1_general_cs NOT NULL COMMENT 'Esito',
  `DATAPAGDIR` varchar(8) COLLATE latin1_general_cs NOT NULL COMMENT 'Data pagamento diritto',
  `DATAPAGSPE` varchar(8) COLLATE latin1_general_cs NOT NULL COMMENT 'Data pagamento spese',
  `NUMRACCOMANDATA` varchar(10) COLLATE latin1_general_cs NOT NULL COMMENT 'Numero raccomandata',
  `ALTRARACCOMANDATA` varchar(10) COLLATE latin1_general_cs NOT NULL COMMENT 'Raccomandata',
  `NOTE` text COLLATE latin1_general_cs NOT NULL COMMENT 'Note',
  `DATADEPOSITO` varchar(8) COLLATE latin1_general_cs NOT NULL COMMENT 'Data deposito',
  `REGFIL` varchar(100) COLLATE latin1_general_cs NOT NULL COMMENT 'Filler',
  PRIMARY KEY (`ROWID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs COMMENT='Registro Notifiche';
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `TABDAG` (
  `ROWID` int(11) NOT NULL AUTO_INCREMENT,
  `TDCLASSE` varchar(20) COLLATE latin1_general_cs NOT NULL,
  `TDROWIDCLASSE` int(11) NOT NULL,
  `TDAGCHIAVE` varchar(60) COLLATE latin1_general_cs NOT NULL,
  `TDPROG` int(11) NOT NULL,
  `TDAGSET` varchar(40) COLLATE latin1_general_cs NOT NULL,
  `TDAGSEQ` int(11) NOT NULL,
  `TDAGVAL` text COLLATE latin1_general_cs NOT NULL,
  `TDAGNOTE` text COLLATE latin1_general_cs NOT NULL,
  `TDAGFONTE` varchar(20) COLLATE latin1_general_cs NOT NULL,
  `TDAGMETA` text COLLATE latin1_general_cs NOT NULL,
  PRIMARY KEY (`ROWID`),
  KEY `I_CLASSEROWID` (`TDCLASSE`,`TDROWIDCLASSE`,`TDAGCHIAVE`,`TDPROG`),
  KEY `I_TDDAGVAL` (`TDAGVAL`(255))
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `TITPROC` (
  `ROWID` int(11) NOT NULL AUTO_INCREMENT,
  `PRANUM` varchar(6) COLLATE latin1_general_cs NOT NULL COMMENT 'NUMERO PRATICA',
  `CATCOD` varchar(4) COLLATE latin1_general_cs NOT NULL COMMENT 'CODICE CATEGORIA',
  `CLACOD` varchar(4) COLLATE latin1_general_cs NOT NULL COMMENT 'CODICE CLASSE',
  `FASCOD` varchar(4) COLLATE latin1_general_cs NOT NULL COMMENT 'CODICE SOTTOCLASSE',
  PRIMARY KEY (`ROWID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs COMMENT='RACCORDO TITOLARIO-PROCEDIMENTO';
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `TMPPRO` (
  `ROWID` int(11) NOT NULL AUTO_INCREMENT,
  `UTENTE` varchar(100) COLLATE latin1_general_cs NOT NULL COMMENT 'CHIAVE UTENTE',
  `CHIAVE` varchar(100) COLLATE latin1_general_cs NOT NULL COMMENT 'CHIAVE RELAZIONALE',
  `CAMPO1` varchar(100) COLLATE latin1_general_cs NOT NULL,
  `CAMPO2` text COLLATE latin1_general_cs NOT NULL,
  `CHIAVENUM` double NOT NULL COMMENT 'CHIAVE RELAZIONALE NUMERICA',
  PRIMARY KEY (`ROWID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs COMMENT='TABELLA DI APPOGGIO DATI PER ELABORAZIONI';
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `UFFDES` (
  `ROWID` int(11) NOT NULL AUTO_INCREMENT,
  `UFFKEY` varchar(6) COLLATE latin1_general_cs NOT NULL,
  `UFFCOD` varchar(4) COLLATE latin1_general_cs NOT NULL,
  `UFFSCA` varchar(2) COLLATE latin1_general_cs NOT NULL,
  `UFFFI1__1` varchar(30) COLLATE latin1_general_cs NOT NULL,
  `UFFFI1__2` varchar(30) COLLATE latin1_general_cs NOT NULL,
  `UFFFI1__3` varchar(30) COLLATE latin1_general_cs NOT NULL,
  `UFFFI1__4` varchar(30) COLLATE latin1_general_cs NOT NULL,
  `UFFFI1__5` varchar(30) COLLATE latin1_general_cs NOT NULL,
  `UFFFI1__6` varchar(30) COLLATE latin1_general_cs NOT NULL,
  `UFFFI1__7` varchar(30) COLLATE latin1_general_cs NOT NULL,
  `UFFFI1__8` varchar(30) COLLATE latin1_general_cs NOT NULL,
  `UFFFI1__9` varchar(30) COLLATE latin1_general_cs NOT NULL,
  `UFFFI1__10` varchar(30) COLLATE latin1_general_cs NOT NULL,
  `UFFFI2__1` double NOT NULL,
  `UFFFI2__2` double NOT NULL,
  `UFFFI2__3` double NOT NULL,
  `UFFFI2__4` double NOT NULL,
  `UFFFI2__5` double NOT NULL,
  `UFFFI2__6` double NOT NULL,
  `UFFFI2__7` double NOT NULL,
  `UFFFI2__8` double NOT NULL,
  `UFFFI2__9` double NOT NULL,
  `UFFFI2__10` double NOT NULL,
  `UFFPROTECT` int(11) NOT NULL COMMENT 'LIVELLO PROTEZIONE',
  `UFFINIVAL` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `UFFCESVAL` varchar(8) COLLATE latin1_general_cs NOT NULL,
  PRIMARY KEY (`ROWID`),
  KEY `A_UFFKEY` (`UFFKEY`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `UFFMAIL` (
  `ROW_ID` int(11) NOT NULL AUTO_INCREMENT,
  `UFFCOD` varchar(4) COLLATE latin1_general_cs NOT NULL,
  `UFFMAIL` text COLLATE latin1_general_cs NOT NULL,
  `DADATA` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `ADATA` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `PERM_REC` tinyint(4) NOT NULL,
  `PERM_SEND` tinyint(4) NOT NULL,
  PRIMARY KEY (`ROW_ID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `UFFPRO` (
  `ROWID` int(11) NOT NULL AUTO_INCREMENT,
  `PRONUM` double NOT NULL,
  `UFFCOD` varchar(4) COLLATE latin1_general_cs NOT NULL,
  `UFFFI1` double NOT NULL,
  `UFFFI2` varchar(50) COLLATE latin1_general_cs NOT NULL,
  `UFFPAR` varchar(2) COLLATE latin1_general_cs NOT NULL,
  `UFFPROMIGRA` varchar(20) COLLATE latin1_general_cs NOT NULL,
  PRIMARY KEY (`ROWID`),
  KEY `A_UFFCOD` (`UFFCOD`),
  KEY `A_PRONUM` (`PRONUM`,`UFFPAR`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `UFFPROSAVE` (
  `ROWID` int(11) NOT NULL AUTO_INCREMENT,
  `PRONUM` double NOT NULL,
  `UFFCOD` varchar(4) COLLATE latin1_general_cs NOT NULL,
  `UFFFI1` double NOT NULL,
  `UFFFI2` varchar(50) COLLATE latin1_general_cs NOT NULL,
  `SAVEDATA` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `SAVEORA` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `SAVEUTENTE` varchar(20) COLLATE latin1_general_cs NOT NULL,
  `UFFPAR` varchar(2) COLLATE latin1_general_cs NOT NULL,
  `UFFPROMIGRA` varchar(20) COLLATE latin1_general_cs NOT NULL,
  PRIMARY KEY (`ROWID`),
  KEY `A_PRONUM` (`PRONUM`),
  KEY `A_UFFCOD` (`UFFCOD`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `UFFTIT` (
  `ROWID` int(11) NOT NULL AUTO_INCREMENT,
  `UFFCOD` varchar(4) COLLATE latin1_general_cs NOT NULL COMMENT 'CODICE UFFICIO',
  `CATCOD` varchar(4) COLLATE latin1_general_cs NOT NULL COMMENT 'CODICE CATEGORIA',
  `CLACOD` varchar(4) COLLATE latin1_general_cs NOT NULL COMMENT 'CODICE CLASSE',
  `FASCOD` varchar(4) COLLATE latin1_general_cs NOT NULL COMMENT 'CODICE SOTTOCLASSE',
  `VERSIONE_T` int(11) NOT NULL COMMENT 'VERSIONE TITOLARIO',
  PRIMARY KEY (`ROWID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs COMMENT='LEGAME TITOLARI PER UFFICIO';
/*!40101 SET character_set_client = @saved_cs_client */;
