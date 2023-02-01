/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ANAARC` (
  `ROWID` int(11) NOT NULL AUTO_INCREMENT,
  `ARCCOD` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `ARCDES` varchar(40) COLLATE latin1_general_cs NOT NULL,
  PRIMARY KEY (`ROWID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs COMMENT='QUALIFICHE';
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ANAATT` (
  `ROWID` int(11) NOT NULL AUTO_INCREMENT,
  `ATTCOD` smallint(6) NOT NULL,
  `ATTDES` text COLLATE latin1_general_cs NOT NULL,
  `ATTNOT` text COLLATE latin1_general_cs NOT NULL,
  `ATTSET` smallint(6) NOT NULL,
  `ATTSEQ` varchar(100) COLLATE latin1_general_cs NOT NULL COMMENT 'SEQUENZA',
  `ATTFIA__1` varchar(50) COLLATE latin1_general_cs NOT NULL,
  `ATTFIA__2` varchar(50) COLLATE latin1_general_cs NOT NULL,
  `ATTFIA__3` varchar(50) COLLATE latin1_general_cs NOT NULL,
  `ATTFIN__1` double NOT NULL,
  `ATTFIN__2` double NOT NULL,
  `ATTFIN__3` double NOT NULL,
  `ATTCLA` text COLLATE latin1_general_cs NOT NULL COMMENT 'Classificazione per protocollazione',
  `ATTINSEDITOR` text COLLATE latin1_general_cs NOT NULL COMMENT 'AUTORE INSERIMENTO',
  `ATTINSDATE` varchar(8) COLLATE latin1_general_cs NOT NULL COMMENT 'Data Inserimento',
  `ATTINSTIME` varchar(8) COLLATE latin1_general_cs NOT NULL COMMENT 'Orario inserimento o modifica',
  `ATTUPDEDITOR` text COLLATE latin1_general_cs NOT NULL COMMENT 'AUTORE ULTIMA MODIFICA',
  `ATTUPDDATE` varchar(8) COLLATE latin1_general_cs NOT NULL COMMENT 'Data aggiornamento',
  `ATTUPDTIME` varchar(8) COLLATE latin1_general_cs NOT NULL COMMENT 'ORARIO AGGIORNAMENTO',
  `ATTFASCICOLO` varchar(50) COLLATE latin1_general_cs NOT NULL COMMENT 'Fascicolo',
  `ATTCLACONS` text COLLATE latin1_general_cs NOT NULL COMMENT 'Classificazione per Conservazione',
  PRIMARY KEY (`ROWID`),
  KEY `A_ATTSET` (`ATTSET`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs COMMENT='ATTIVITA';
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ANACLA` (
  `ROWID` int(11) NOT NULL AUTO_INCREMENT,
  `CLACOD` varchar(30) COLLATE latin1_general_cs NOT NULL COMMENT 'codice classificazione',
  `CLADES` text COLLATE latin1_general_cs NOT NULL COMMENT 'descrizione classificazione',
  `CLASPO` double NOT NULL COMMENT 'codice sportello on-line',
  `CLAEXT` varchar(500) COLLATE latin1_general_cs NOT NULL COMMENT 'estensioni',
  `CLAPDR` varchar(30) COLLATE latin1_general_cs NOT NULL COMMENT 'codice padre',
  `CLATIP` varchar(2) COLLATE latin1_general_cs NOT NULL COMMENT 'Tipologia classificazione',
  `CLAMSGCTRFIRMA` text COLLATE latin1_general_cs NOT NULL COMMENT 'Messaggio controllo firma',
  `CLAEXPRCTRFIRMA` text COLLATE latin1_general_cs NOT NULL COMMENT 'Espressione controllo firma',
  PRIMARY KEY (`ROWID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ANADDO` (
  `ROWID` int(11) NOT NULL AUTO_INCREMENT,
  `DDOCOD` varchar(6) COLLATE latin1_general_cs NOT NULL,
  `DDONOM` varchar(100) COLLATE latin1_general_cs NOT NULL,
  `DDOIND` varchar(100) COLLATE latin1_general_cs NOT NULL,
  `DDOCAP` varchar(5) COLLATE latin1_general_cs NOT NULL,
  `DDOCIT` varchar(40) COLLATE latin1_general_cs NOT NULL,
  `DDOPRO` varchar(2) COLLATE latin1_general_cs NOT NULL,
  `DDOEMA` text COLLATE latin1_general_cs NOT NULL,
  `DDOFIS` varchar(16) COLLATE latin1_general_cs NOT NULL,
  `DDOTEL` varchar(20) COLLATE latin1_general_cs NOT NULL,
  `DDONOTE` text COLLATE latin1_general_cs NOT NULL,
  `DDOCDE` varchar(100) COLLATE latin1_general_cs NOT NULL COMMENT 'Codice Destinazione',
  `DDONOFO` smallint(6) NOT NULL COMMENT 'Non usato nel FO',
  `DDOCART` varchar(20) COLLATE latin1_general_cs NOT NULL COMMENT 'Codice Endo CART',
  PRIMARY KEY (`ROWID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ANADES` (
  `ROWID` int(11) NOT NULL AUTO_INCREMENT,
  `DESNUM` varchar(10) COLLATE latin1_general_cs NOT NULL,
  `DESCOD` varchar(6) COLLATE latin1_general_cs NOT NULL,
  `DESNOME` varchar(50) COLLATE latin1_general_cs NOT NULL COMMENT 'Nome',
  `DESCOGNOME` varchar(50) COLLATE latin1_general_cs NOT NULL COMMENT 'Cognome',
  `DESRAGSOC` varchar(300) COLLATE latin1_general_cs NOT NULL COMMENT 'Ragione Sociale',
  `DESSESSO` varchar(2) COLLATE latin1_general_cs NOT NULL COMMENT 'Sesso',
  `DESNASCIT` varchar(40) COLLATE latin1_general_cs NOT NULL COMMENT 'Comune di Nascita',
  `DESNASPROV` varchar(4) COLLATE latin1_general_cs NOT NULL COMMENT 'Provincia di nascita',
  `DESNASNAZ` varchar(100) COLLATE latin1_general_cs NOT NULL COMMENT 'Nazione di Nascita',
  `DESNASDAT` varchar(8) COLLATE latin1_general_cs NOT NULL COMMENT 'Data di nascita',
  `DESNOM` varchar(300) COLLATE latin1_general_cs NOT NULL COMMENT 'Nominativo Completo',
  `DESEMA` varchar(100) COLLATE latin1_general_cs NOT NULL COMMENT 'EMAIL INTESTATARIO',
  `DESPEC` varchar(100) COLLATE latin1_general_cs NOT NULL COMMENT 'I',
  `DESTEL` varchar(20) COLLATE latin1_general_cs NOT NULL COMMENT 'Telefono',
  `DESCEL` varchar(20) COLLATE latin1_general_cs NOT NULL COMMENT 'NUMERO CELLULARE',
  `DESFAX` varchar(20) COLLATE latin1_general_cs NOT NULL COMMENT 'Fax',
  `DESSITO` varchar(500) COLLATE latin1_general_cs NOT NULL COMMENT 'Sito Web',
  `DESCODIND` varchar(6) COLLATE latin1_general_cs NOT NULL COMMENT 'CODICE VIA',
  `DESIND` varchar(100) COLLATE latin1_general_cs NOT NULL COMMENT 'Indirizzo Residenza',
  `DESCIV` varchar(10) COLLATE latin1_general_cs NOT NULL COMMENT 'Civico di residenza',
  `DESCAP` varchar(5) COLLATE latin1_general_cs NOT NULL,
  `DESCIT` varchar(40) COLLATE latin1_general_cs NOT NULL,
  `DESPRO` varchar(4) COLLATE latin1_general_cs NOT NULL,
  `DESNAZ` varchar(100) COLLATE latin1_general_cs NOT NULL,
  `DESDAT` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `DESDRE` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `DESDCH` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `DESANN` varchar(36) COLLATE latin1_general_cs NOT NULL,
  `DESSON` varchar(2) COLLATE latin1_general_cs NOT NULL,
  `DESFIS` varchar(16) COLLATE latin1_general_cs NOT NULL,
  `DESPIVA` varchar(11) COLLATE latin1_general_cs NOT NULL COMMENT 'Partita IVA',
  `DESFISGIU` varchar(2) COLLATE latin1_general_cs NOT NULL COMMENT 'Persona Fisica Giuridica',
  `DESNATLEGALE` varchar(4) COLLATE latin1_general_cs NOT NULL COMMENT 'Natura Legale',
  `DESRUO` varchar(4) COLLATE latin1_general_cs NOT NULL,
  `DESRUOEXT` text COLLATE latin1_general_cs NOT NULL COMMENT 'Estensione descrizione Ruolo',
  `DESPAK` varchar(30) COLLATE latin1_general_cs NOT NULL COMMENT 'Chiave relazione passo',
  `DESDSET` varchar(40) COLLATE latin1_general_cs NOT NULL COMMENT 'Chiave data set passo',
  `DESDIDX` varchar(10) COLLATE latin1_general_cs NOT NULL COMMENT 'Indice Aggiuntivo',
  `DESCMSUSER` varchar(50) COLLATE latin1_general_cs NOT NULL COMMENT 'username utente',
  `DESQUALIFICA` text COLLATE latin1_general_cs NOT NULL COMMENT 'qualifica',
  `DESNUMISCRIZIONE` varchar(80) COLLATE latin1_general_cs NOT NULL COMMENT 'num iscrizione',
  `DESPROVISCRIZIONE` varchar(4) COLLATE latin1_general_cs NOT NULL COMMENT 'prov iscrizione',
  `DESORDISCRIZIONE` text COLLATE latin1_general_cs NOT NULL COMMENT 'ordine-albo-collegio',
  `DESLOC` varchar(200) COLLATE latin1_general_cs NOT NULL COMMENT 'Localita',
  `DESREF` text COLLATE latin1_general_cs NOT NULL COMMENT 'Nominativo Riferimento',
  `DESREFTEL` varchar(20) COLLATE latin1_general_cs NOT NULL COMMENT 'Telefono Referente',
  `DESREFEMA` varchar(100) COLLATE latin1_general_cs NOT NULL COMMENT 'Email Referente',
  `DESNOTE` text COLLATE latin1_general_cs NOT NULL,
  PRIMARY KEY (`ROWID`),
  KEY `I_DESNOM` (`DESNOM`),
  KEY `I_DESFIS` (`DESFIS`),
  KEY `I_DESNUM` (`DESNUM`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs COMMENT='INTESTATARI';
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ANADESDAG` (
  `ROW_ID` int(11) NOT NULL AUTO_INCREMENT,
  `ANADES_ROWID` int(11) NOT NULL COMMENT 'ROWID ANADES',
  `DESKEY` varchar(60) COLLATE latin1_general_cs NOT NULL COMMENT 'Chiave metadato',
  `DESVAL` text COLLATE latin1_general_cs NOT NULL COMMENT 'Valore metadato',
  PRIMARY KEY (`ROW_ID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ANADIS` (
  `ROWID` int(11) NOT NULL AUTO_INCREMENT,
  `DISCOD` varchar(20) COLLATE latin1_general_cs NOT NULL COMMENT 'CODICE DISCIPLINA',
  `DISDES` text COLLATE latin1_general_cs NOT NULL COMMENT 'DESCRIZIONE DISCIPLINA',
  `DISTIP` varchar(40) COLLATE latin1_general_cs NOT NULL COMMENT 'TIPO DISCIPLINA',
  `DISFIL` varchar(100) COLLATE latin1_general_cs NOT NULL COMMENT 'FILE DISCIPLINA',
  `DISINSEDITOR` text COLLATE latin1_general_cs NOT NULL COMMENT 'AUTORE INSERIMENTO',
  `DISINSDATE` varchar(8) COLLATE latin1_general_cs NOT NULL COMMENT 'Data Inserimento',
  `DISINSTIME` varchar(8) COLLATE latin1_general_cs NOT NULL COMMENT 'Orario inserimento o modifica',
  `DISUPDEDITOR` text COLLATE latin1_general_cs NOT NULL COMMENT 'AUTORE ULTIMA MODIFICA',
  `DISUPDDATE` varchar(8) COLLATE latin1_general_cs NOT NULL COMMENT 'Data aggiornamento',
  `DISUPDTIME` varchar(8) COLLATE latin1_general_cs NOT NULL COMMENT 'ORARIO AGGIORNAMENTO',
  PRIMARY KEY (`ROWID`),
  UNIQUE KEY `NORCOD` (`DISCOD`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs COMMENT='NORMATIVA';
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ANADOCTIPREG` (
  `ROWID` int(11) NOT NULL AUTO_INCREMENT,
  `CODDOCREG` smallint(6) NOT NULL COMMENT 'Codice',
  `DESDOCREG` varchar(100) COLLATE latin1_general_cs NOT NULL COMMENT 'Descrizione',
  `ULTPROGDOCREG` int(11) NOT NULL COMMENT 'Ultimo Progressivo',
  `FL_ATTIVO` smallint(6) NOT NULL COMMENT 'Attivo',
  `TIPOPDOCPROG` varchar(8) COLLATE latin1_general_cs NOT NULL COMMENT 'Tipo progressivo Assoluto-Annuale',
  PRIMARY KEY (`ROWID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ANAEVENTI` (
  `ROWID` int(11) NOT NULL AUTO_INCREMENT,
  `EVTCOD` varchar(6) COLLATE latin1_general_cs NOT NULL,
  `EVTDESCR` varchar(100) COLLATE latin1_general_cs NOT NULL COMMENT 'DESCRIZIONE',
  `EVTSEGCOMUNICA` varchar(50) COLLATE latin1_general_cs NOT NULL COMMENT 'TIPO SEGNALAZIONE COMUNICA',
  `EVTINSEDITOR` text COLLATE latin1_general_cs NOT NULL COMMENT 'Autore Inserimento',
  `EVTINSDATE` text COLLATE latin1_general_cs NOT NULL COMMENT 'DATA INSERIMENTO',
  `EVTINSTIME` varchar(8) COLLATE latin1_general_cs NOT NULL COMMENT 'ORARIO INSERIMENTO',
  `EVTUPDEDITOR` text COLLATE latin1_general_cs NOT NULL COMMENT 'AUTORE ULTIMA MODIFICA',
  `EVTUPDDATE` varchar(8) COLLATE latin1_general_cs NOT NULL COMMENT 'DATA ULTIMA MODIFICA',
  `EVTUPDTIME` varchar(8) COLLATE latin1_general_cs NOT NULL COMMENT 'ORARIO ULTIMA MODIFICA',
  PRIMARY KEY (`ROWID`),
  UNIQUE KEY `EVTCOD` (`EVTCOD`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ANAHELP` (
  `ROWID` int(11) NOT NULL AUTO_INCREMENT,
  `HELPCOD` text COLLATE latin1_general_cs NOT NULL COMMENT 'CODICE HELP',
  `HELPDES` text COLLATE latin1_general_cs NOT NULL COMMENT 'DESC HELP',
  `HELPFORMATO` text COLLATE latin1_general_cs NOT NULL COMMENT 'FORMATO',
  PRIMARY KEY (`ROWID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ANAMODELLI` (
  `ROWID` int(11) NOT NULL AUTO_INCREMENT,
  `CODICE` varchar(6) COLLATE latin1_general_cs NOT NULL,
  `DESCRIZIONE` text COLLATE latin1_general_cs NOT NULL,
  `TIPOEVENTO` varchar(50) COLLATE latin1_general_cs NOT NULL COMMENT 'TIPOLOGIA SEGNALAZIONE',
  `TIPOLOGIA` varchar(6) COLLATE latin1_general_cs NOT NULL COMMENT 'TIPOLOGIA',
  `SETTORE` double NOT NULL COMMENT 'SETTORE',
  `ATTIVITA` double NOT NULL COMMENT 'ATTIVITA',
  `INIZIOVALIDITA` varchar(8) COLLATE latin1_general_cs NOT NULL COMMENT 'DATA INIZIO VALIDITA',
  `FINEVALIDITA` varchar(8) COLLATE latin1_general_cs NOT NULL COMMENT 'DATA FINE VALIDITA',
  PRIMARY KEY (`ROWID`),
  UNIQUE KEY `CODICE` (`CODICE`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ANANOM` (
  `ROWID` int(11) NOT NULL AUTO_INCREMENT,
  `NOMSET` varchar(6) COLLATE latin1_general_cs NOT NULL,
  `NOMSER` varchar(6) COLLATE latin1_general_cs NOT NULL,
  `NOMOPE` varchar(6) COLLATE latin1_general_cs NOT NULL,
  `NOMADD` varchar(6) COLLATE latin1_general_cs NOT NULL,
  `NOMAD1` varchar(6) COLLATE latin1_general_cs NOT NULL,
  `NOMAD2` varchar(6) COLLATE latin1_general_cs NOT NULL,
  `NOMCOG` varchar(26) COLLATE latin1_general_cs NOT NULL,
  `NOMNOM` varchar(26) COLLATE latin1_general_cs NOT NULL,
  `NOMQUA` varchar(6) COLLATE latin1_general_cs NOT NULL,
  `NOMPRO` varchar(6) COLLATE latin1_general_cs NOT NULL,
  `NOMDAP` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `NOMAPE` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `NOMRES` varchar(6) COLLATE latin1_general_cs NOT NULL,
  `NOMPSW` varchar(10) COLLATE latin1_general_cs NOT NULL,
  `NOMANN` varchar(50) COLLATE latin1_general_cs NOT NULL,
  `NOMAN2` varchar(50) COLLATE latin1_general_cs NOT NULL,
  `NOMAN3` varchar(50) COLLATE latin1_general_cs NOT NULL,
  `NOMEML` text COLLATE latin1_general_cs NOT NULL,
  `NOMDEP` varchar(6) COLLATE latin1_general_cs NOT NULL,
  `NOMTSP` double NOT NULL COMMENT 'Visibilità Sportello on line ( 0 = tutti)',
  `NOMSPA` int(11) NOT NULL COMMENT 'Visibilità Sportello Aggregato',
  `NOMTEL` varchar(20) COLLATE latin1_general_cs NOT NULL COMMENT 'Telefono',
  `NOMFAX` varchar(20) COLLATE latin1_general_cs NOT NULL COMMENT 'Fax',
  `NOMMETA` text COLLATE latin1_general_cs,
  `NOMABILITAASS` smallint(6) NOT NULL COMMENT 'Check abilita assegnazione',
  `NOMRESPASS` smallint(6) NOT NULL COMMENT 'ChCheck responsabile assegnazione',
  `NOMPRIVMAIL` smallint(6) NOT NULL COMMENT 'VEDE SOLO MAIL ASSEGNATE',
  `NOMTSPEXT` varchar(60) COLLATE latin1_general_cs NOT NULL COMMENT 'ESTENSIONE AGLI SPORTELLI ABILITATI',
  PRIMARY KEY (`ROWID`),
  KEY `I_NOMQUA` (`NOMQUA`),
  KEY `I_NOMRES` (`NOMRES`),
  KEY `I_NOMCOD` (`NOMSET`,`NOMSER`,`NOMOPE`,`NOMADD`,`NOMAD1`),
  KEY `I_NOMPRO` (`NOMPRO`),
  KEY `I_NOMPSW` (`NOMPSW`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs COMMENT='DIPENDENTI';
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ANANOR` (
  `ROWID` int(11) NOT NULL AUTO_INCREMENT,
  `NORCOD` varchar(20) COLLATE latin1_general_cs NOT NULL COMMENT 'CODICE NORMATIVA',
  `NORDES` text COLLATE latin1_general_cs NOT NULL COMMENT 'DESCRIZIONE NORMATIVA',
  `NORTIP` varchar(40) COLLATE latin1_general_cs NOT NULL COMMENT 'TIPO NORMATIVA (NAZ.,COM.REG.)',
  `NORENT` varchar(60) COLLATE latin1_general_cs NOT NULL COMMENT 'ENTE EMANAZIONE NORMA',
  `NORFIL` varchar(100) COLLATE latin1_general_cs NOT NULL COMMENT 'FILE NORMATIVA',
  `NORURL` text COLLATE latin1_general_cs NOT NULL COMMENT 'Url  Associato',
  `NORINSEDITOR` text COLLATE latin1_general_cs NOT NULL COMMENT 'AUTORE INSERIMENTO',
  `NORINSDATE` varchar(8) COLLATE latin1_general_cs NOT NULL COMMENT 'Data Inserimento',
  `NORINSTIME` varchar(8) COLLATE latin1_general_cs NOT NULL COMMENT 'Orario inserimento o modifica',
  `NORUPDEDITOR` text COLLATE latin1_general_cs NOT NULL COMMENT 'AUTORE ULTIMA MODIFICA',
  `NORUPDDATE` varchar(8) COLLATE latin1_general_cs NOT NULL COMMENT 'Data aggiornamento',
  `NORUPDTIME` varchar(8) COLLATE latin1_general_cs NOT NULL COMMENT 'ORARIO AGGIORNAMENTO',
  PRIMARY KEY (`ROWID`),
  UNIQUE KEY `NORCOD` (`NORCOD`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs COMMENT='NORMATIVA';
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ANAPAG` (
  `ROWID` int(11) NOT NULL AUTO_INCREMENT,
  `PAGCOD` varchar(6) COLLATE latin1_general_cs NOT NULL,
  `PAGDES` varchar(50) COLLATE latin1_general_cs NOT NULL,
  `PAGURL` varchar(200) COLLATE latin1_general_cs NOT NULL,
  PRIMARY KEY (`ROWID`),
  UNIQUE KEY `K_MASTER` (`PAGCOD`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ANAPAR` (
  `ROWID` int(11) NOT NULL AUTO_INCREMENT,
  `PARKEY` varchar(50) COLLATE latin1_general_cs NOT NULL COMMENT 'chiave parametro',
  `PARCLA` varchar(50) COLLATE latin1_general_cs NOT NULL COMMENT 'Classificazione Parametri',
  `PARVAL` text COLLATE latin1_general_cs NOT NULL COMMENT 'valore parametro',
  PRIMARY KEY (`ROWID`),
  UNIQUE KEY `PARKEY` (`PARKEY`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs COMMENT='PARAMETRI  FO';
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ANAPCO` (
  `ROWID` int(11) NOT NULL AUTO_INCREMENT,
  `PCOCOD` varchar(50) COLLATE latin1_general_cs NOT NULL,
  `PCODES` text COLLATE latin1_general_cs NOT NULL,
  `PCOTIP` varchar(10) COLLATE latin1_general_cs NOT NULL,
  `PCOPAR` text COLLATE latin1_general_cs NOT NULL,
  PRIMARY KEY (`ROWID`),
  UNIQUE KEY `PCOCOD` (`PCOCOD`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs COMMENT='PROCEDURE DI CONTROLLO';
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ANAPRA` (
  `ROWID` int(11) NOT NULL AUTO_INCREMENT,
  `PRANUM` varchar(6) COLLATE latin1_general_cs NOT NULL COMMENT 'Codice Procedimento',
  `PRANUMEST` varchar(100) COLLATE latin1_general_cs NOT NULL,
  `PRAMODELLO` varchar(6) COLLATE latin1_general_cs NOT NULL COMMENT 'CODICE MODELLO ON LINE',
  `PRASET` varchar(6) COLLATE latin1_general_cs NOT NULL COMMENT 'Codice Settore',
  `PRASER` varchar(6) COLLATE latin1_general_cs NOT NULL COMMENT 'Codice Servizio',
  `PRAOPE` varchar(6) COLLATE latin1_general_cs NOT NULL COMMENT 'Codice Unita Operativa',
  `PRARES` varchar(6) COLLATE latin1_general_cs NOT NULL COMMENT 'Codice Responsabile',
  `PRATSP` double NOT NULL COMMENT 'Codice Sportello on-line',
  `PRADES__1` varchar(80) COLLATE latin1_general_cs NOT NULL COMMENT 'Descrizione 1',
  `PRADES__2` varchar(80) COLLATE latin1_general_cs NOT NULL COMMENT 'Descrizione 2',
  `PRADES__3` varchar(80) COLLATE latin1_general_cs NOT NULL COMMENT 'Descrizione 3',
  `PRADES__4` varchar(80) COLLATE latin1_general_cs NOT NULL COMMENT 'Descrizione 4',
  `PRAORG` varchar(6) COLLATE latin1_general_cs NOT NULL COMMENT 'non usato',
  `PRAODE` varchar(36) COLLATE latin1_general_cs NOT NULL COMMENT 'non usato',
  `PRACNO` varchar(6) COLLATE latin1_general_cs NOT NULL COMMENT 'non usato',
  `PRANDE` varchar(36) COLLATE latin1_general_cs NOT NULL COMMENT 'non usato',
  `PRANOR` varchar(40) COLLATE latin1_general_cs NOT NULL COMMENT 'Codice Normativa',
  `PRACEV` varchar(6) COLLATE latin1_general_cs NOT NULL COMMENT 'non usato',
  `PRAEVE` varchar(40) COLLATE latin1_general_cs NOT NULL COMMENT 'non usato',
  `PRAGIO` double NOT NULL COMMENT 'Giorni Validità',
  `PRAIMP` varchar(80) COLLATE latin1_general_cs NOT NULL COMMENT 'non usato',
  `PRATES` varchar(24) COLLATE latin1_general_cs NOT NULL COMMENT 'File Associato',
  `PRASUA` double NOT NULL COMMENT 'non usato',
  `PRATIP` varchar(6) COLLATE latin1_general_cs NOT NULL COMMENT 'Tipologia',
  `PRASTT` double NOT NULL COMMENT 'Codice Settore',
  `PRAATT` double NOT NULL COMMENT 'Codice Attività',
  `PRADVA` varchar(8) COLLATE latin1_general_cs NOT NULL COMMENT 'dalla Data di Validità',
  `PRAAVA` varchar(8) COLLATE latin1_general_cs NOT NULL COMMENT 'alla Data di Validità',
  `PRAMOD` varchar(80) COLLATE latin1_general_cs NOT NULL COMMENT 'CHIAVE CLASSE MODELLO',
  `PRAMKY` text COLLATE latin1_general_cs NOT NULL COMMENT 'CHIAVE IDENTIFICATIVA MODELLO',
  `PRATPR` varchar(20) COLLATE latin1_general_cs NOT NULL COMMENT 'Tipo procedimento (Generico|OnLine|Modello Controller)',
  `PRASEG` varchar(50) COLLATE latin1_general_cs NOT NULL COMMENT 'Tipo segnalazione',
  `PRACTR` varchar(50) COLLATE latin1_general_cs NOT NULL COMMENT 'Codice procedura di controllo',
  `PRACLA` text COLLATE latin1_general_cs NOT NULL COMMENT 'Classificazione per protocollazione',
  `PRASLAVE` smallint(6) NOT NULL COMMENT 'FLAG CODICE PRATICA SLAVE AL MASTER',
  `PRAINSEDITOR` text COLLATE latin1_general_cs NOT NULL COMMENT 'AUTORE INSERIMENTO',
  `PRAINSDATE` varchar(8) COLLATE latin1_general_cs NOT NULL COMMENT 'Data Inserimento',
  `PRAINSTIME` varchar(8) COLLATE latin1_general_cs NOT NULL COMMENT 'Orario Inserimeto o Modifica',
  `PRAUPDEDITOR` text COLLATE latin1_general_cs NOT NULL COMMENT 'AUTORE ULTIMA MODIFICA',
  `PRAUPDDATE` varchar(8) COLLATE latin1_general_cs NOT NULL COMMENT 'Data aggiornamento',
  `PRAUPDTIME` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `PRAOFFLINE` smallint(6) NOT NULL COMMENT 'Flag per spegnere il procedimento',
  `PRAFASCICOLO` varchar(50) COLLATE latin1_general_cs NOT NULL COMMENT 'Fascicolo',
  `PRADIAG` text COLLATE latin1_general_cs,
  `PRAINF` smallint(6) NOT NULL COMMENT 'Flag informativa personalizzata',
  `PRAOGGTML` text COLLATE latin1_general_cs NOT NULL COMMENT 'Template per Oggetti',
  `PRAOGGTML_ACQ` smallint(6) NOT NULL COMMENT 'Usa Oggetto Richiesta in Acquisizione',
  `PRAUUID` varchar(36) COLLATE latin1_general_cs DEFAULT NULL,
  `PRACLACONS` text COLLATE latin1_general_cs NOT NULL COMMENT 'Classificazione per Conservazione',
  PRIMARY KEY (`ROWID`),
  KEY `I_PRACOD` (`PRASET`,`PRASER`,`PRAOPE`,`PRARES`),
  KEY `I_PROCED` (`PRASET`,`PRASER`,`PRAOPE`,`PRARES`,`PRANUM`),
  KEY `I_PRANUM` (`PRANUM`),
  KEY `I_PRAMODELLO` (`PRAMODELLO`,`PRANUM`),
  KEY `I_PRAUUID` (`PRAUUID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs COMMENT='PROCEDIMENTI';
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ANAQUIET` (
  `ROWID` int(11) NOT NULL AUTO_INCREMENT,
  `CODQUIET` smallint(6) NOT NULL,
  `QUIETANZATIPO` varchar(30) COLLATE latin1_general_cs NOT NULL,
  `IDENTIFICAZIONETIPO` varchar(30) COLLATE latin1_general_cs NOT NULL,
  `IDENTIFICAZIONE` varchar(80) COLLATE latin1_general_cs NOT NULL,
  PRIMARY KEY (`ROWID`),
  UNIQUE KEY `UNIQ_ANAQUIET` (`CODQUIET`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ANAREQ` (
  `ROWID` int(11) NOT NULL AUTO_INCREMENT,
  `REQCOD` smallint(6) NOT NULL COMMENT 'CODICE REQUISITO',
  `REQDES` text COLLATE latin1_general_cs NOT NULL COMMENT 'DESCRIZIONE REQUISITO',
  `REQTIPO` varchar(20) COLLATE latin1_general_cs NOT NULL COMMENT 'TIPO REQUISITO',
  `REQAREA` varchar(20) COLLATE latin1_general_cs NOT NULL,
  `REQFIL` varchar(100) COLLATE latin1_general_cs NOT NULL,
  `REQINSEDITOR` text COLLATE latin1_general_cs NOT NULL COMMENT 'AUTORE INSERIMENTO',
  `REQINSDATE` varchar(8) COLLATE latin1_general_cs NOT NULL COMMENT 'Data Inserimento',
  `REQINSTIME` varchar(8) COLLATE latin1_general_cs NOT NULL COMMENT 'Orario inserimento o modifica',
  `REQUPDEDITOR` text COLLATE latin1_general_cs NOT NULL COMMENT 'AUTORE ULTIMA MODIFICA',
  `REQUPDDATE` varchar(8) COLLATE latin1_general_cs NOT NULL COMMENT 'Data aggiornamento',
  `REQUPDTIME` varchar(8) COLLATE latin1_general_cs NOT NULL COMMENT 'ORARIO AGGIORNAMENTO',
  PRIMARY KEY (`ROWID`),
  UNIQUE KEY `REQCOD` (`REQCOD`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs COMMENT='REQUISITI';
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ANARUO` (
  `ROWID` int(11) NOT NULL AUTO_INCREMENT,
  `RUOCOD` varchar(4) COLLATE latin1_general_cs NOT NULL,
  `RUODES` text COLLATE latin1_general_cs NOT NULL,
  `RUODIS` smallint(6) NOT NULL COMMENT 'Flag disabilita dizionario',
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
CREATE TABLE `ANASET` (
  `ROWID` int(11) NOT NULL AUTO_INCREMENT,
  `SETCOD` smallint(6) NOT NULL,
  `SETDES` text COLLATE latin1_general_cs NOT NULL,
  `SETSEQ` varchar(100) COLLATE latin1_general_cs NOT NULL COMMENT 'SEQUENZA',
  `SETFIA__1` varchar(50) COLLATE latin1_general_cs NOT NULL,
  `SETFIA__2` varchar(50) COLLATE latin1_general_cs NOT NULL,
  `SETFIA__3` varchar(50) COLLATE latin1_general_cs NOT NULL,
  `SETFIN__1` double NOT NULL,
  `SETFIN__2` double NOT NULL,
  `SETFIN__3` double NOT NULL,
  `SETCLA` text COLLATE latin1_general_cs NOT NULL COMMENT 'Classificazione per protocollazione',
  `SETINSEDITOR` text COLLATE latin1_general_cs NOT NULL COMMENT 'AUTORE INSERIMENTO',
  `SETINSDATE` varchar(8) COLLATE latin1_general_cs NOT NULL COMMENT 'Data Inserimento',
  `SETINSTIME` varchar(8) COLLATE latin1_general_cs NOT NULL COMMENT 'Orario inserimento o modifica',
  `SETUPDEDITOR` text COLLATE latin1_general_cs NOT NULL COMMENT 'AUTORE ULTIMA MODIFICA',
  `SETUPDDATE` varchar(8) COLLATE latin1_general_cs NOT NULL COMMENT 'Data aggiornamento',
  `SETUPDTIME` varchar(8) COLLATE latin1_general_cs NOT NULL COMMENT 'ORARIO AGGIORNAMENTO',
  `SETFASCICOLO` varchar(50) COLLATE latin1_general_cs NOT NULL COMMENT 'Fascicolo',
  `SETCLACONS` text COLLATE latin1_general_cs NOT NULL COMMENT 'Classificazione per Conservazione',
  PRIMARY KEY (`ROWID`),
  UNIQUE KEY `K_MASTER` (`SETCOD`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ANASPA` (
  `ROWID` int(11) NOT NULL AUTO_INCREMENT,
  `SPACOD` int(11) NOT NULL COMMENT 'Codice sportello Aggregato',
  `SPATSP` double NOT NULL COMMENT 'Codice Sportello on-line Master',
  `SPASEQ` int(11) NOT NULL,
  `SPADES` varchar(40) COLLATE latin1_general_cs NOT NULL COMMENT 'Descrizione',
  `SPAAMMIPA` varchar(50) COLLATE latin1_general_cs NOT NULL COMMENT 'CODICE AMMINISTRAZIONE IPA',
  `SPAAOO` varchar(50) COLLATE latin1_general_cs NOT NULL COMMENT 'CODICE AOO',
  `SPANOT` text COLLATE latin1_general_cs NOT NULL COMMENT 'Annotazioni',
  `SPAATT` int(11) NOT NULL COMMENT 'Flag Sportello Attivo',
  `SPARES` varchar(6) COLLATE latin1_general_cs NOT NULL COMMENT 'Responsabile',
  `SPACCA` varchar(4) COLLATE latin1_general_cs NOT NULL COMMENT 'Codice catastale',
  `SPAPRV` varchar(2) COLLATE latin1_general_cs NOT NULL COMMENT 'Provincia CCIAA',
  `SPAENT` varchar(4) COLLATE latin1_general_cs NOT NULL COMMENT 'Ente destinatario',
  `SPAIST` varchar(6) COLLATE latin1_general_cs NOT NULL COMMENT 'codice istat',
  `SPACOM` varchar(100) COLLATE latin1_general_cs NOT NULL COMMENT 'comune',
  `SPAIND` varchar(100) COLLATE latin1_general_cs NOT NULL COMMENT 'Indirizzo',
  `SPAPRO` varchar(4) COLLATE latin1_general_cs NOT NULL COMMENT 'provincia',
  `SPACAP` varchar(6) COLLATE latin1_general_cs NOT NULL COMMENT 'cap',
  `SPATES` text COLLATE latin1_general_cs NOT NULL COMMENT 'TESORERIA',
  `SPADEST` text COLLATE latin1_general_cs NOT NULL COMMENT 'TESORERIA',
  `SPAIBAN` text COLLATE latin1_general_cs NOT NULL COMMENT 'TESORERIA',
  `SPABANCA` text COLLATE latin1_general_cs NOT NULL COMMENT 'BANCA',
  `SPAPEC` varchar(100) COLLATE latin1_general_cs NOT NULL,
  `SPANCI` varchar(8) COLLATE latin1_general_cs NOT NULL COMMENT 'civico',
  `SPASUPERADMIN` double NOT NULL COMMENT 'Gruppo Super Admin',
  `SPAENTECOMM` varchar(50) COLLATE latin1_general_cs NOT NULL COMMENT 'Codice Ente Commercio',
  `SPASWIFT` varchar(20) COLLATE latin1_general_cs NOT NULL COMMENT 'Codice swift',
  `SPACC` varchar(15) COLLATE latin1_general_cs NOT NULL COMMENT 'Conto corrente bancario',
  `SPACCP` varchar(15) COLLATE latin1_general_cs NOT NULL COMMENT 'Conto corrente postale',
  `SPACAUSALECC` text COLLATE latin1_general_cs NOT NULL COMMENT 'Causale bonifico',
  `SPACAUSALECCP` text COLLATE latin1_general_cs NOT NULL COMMENT 'Causale bollettino',
  `SPADISABILITAPROT` double NOT NULL COMMENT 'Flag Disabilita Protocollazione',
  `SPATIPOPROT` varchar(50) COLLATE latin1_general_cs NOT NULL COMMENT 'Tipo protocollazione aggregato',
  `SPAMETAPROT` text COLLATE latin1_general_cs NOT NULL COMMENT 'Metadati aggregato',
  `SPACLA` text COLLATE latin1_general_cs NOT NULL COMMENT 'Classificazione per protocollazione',
  `SPAUOP` text COLLATE latin1_general_cs NOT NULL COMMENT 'UFFICIO / UNITA OPERATIVA PROTOCOLLAZIONE',
  `SPATDO` varchar(20) COLLATE latin1_general_cs NOT NULL COMMENT 'Tipo Documento Protocollo',
  `SPATDOENDOPAR` varchar(20) COLLATE latin1_general_cs NOT NULL COMMENT 'TIPO DOCUMENTO PROTOCOLLO ENDO PROCEDIMENTO IN PARTENZA',
  `SPATDOENDOARR` varchar(20) COLLATE latin1_general_cs NOT NULL COMMENT 'TIPO DOCUMENTO PROTOCOLLO ENDO PROCEDIMENTO IN ARRIVO',
  `SPAFASCICOLO` varchar(50) COLLATE latin1_general_cs NOT NULL COMMENT 'Fascicolo',
  `SPAPWDPAGOPA` varchar(100) COLLATE latin1_general_cs NOT NULL COMMENT 'password pago pa',
  `SPAUTENTEPAGOPA` varchar(100) COLLATE latin1_general_cs NOT NULL COMMENT 'utente pago pa',
  `SPADISABILITAPAGOPA` double NOT NULL COMMENT 'Flag per disabilitare PAgo PA',
  `SPADITTAPAGOPA` varchar(100) COLLATE latin1_general_cs NOT NULL COMMENT 'codice ditta pago pa',
  `SPAURLLOGINPAGOPA` text COLLATE latin1_general_cs NOT NULL COMMENT 'url login pago pa',
  `SPAURLPAGOPA` text COLLATE latin1_general_cs NOT NULL COMMENT 'url pago pa',
  PRIMARY KEY (`ROWID`),
  UNIQUE KEY `SPACOD` (`SPACOD`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs COMMENT='SPORTELLI AGGREGATI';
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ANASTA` (
  `ROWID` int(11) NOT NULL AUTO_INCREMENT,
  `STACOD` smallint(6) NOT NULL,
  `STADES` varchar(70) COLLATE latin1_general_cs NOT NULL,
  `STAFIA__1` varchar(50) COLLATE latin1_general_cs NOT NULL,
  `STAFIA__2` varchar(50) COLLATE latin1_general_cs NOT NULL,
  `STAFIA__3` varchar(50) COLLATE latin1_general_cs NOT NULL,
  `STAFIN__1` double NOT NULL,
  `STAFIN__2` double NOT NULL,
  `STAFIN__3` double NOT NULL,
  PRIMARY KEY (`ROWID`),
  UNIQUE KEY `K_MASTER` (`STACOD`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ANASTP` (
  `ROWID` int(11) NOT NULL AUTO_INCREMENT COMMENT 'CODICE STATO',
  `STPDES` text COLLATE latin1_general_cs NOT NULL COMMENT 'DESCRIZIONE STATO',
  `STPFLAG` varchar(50) COLLATE latin1_general_cs NOT NULL COMMENT 'flag stato',
  PRIMARY KEY (`ROWID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs COMMENT='STATI PRATICA';
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ANATIP` (
  `ROWID` int(11) NOT NULL AUTO_INCREMENT,
  `TIPCOD` varchar(6) COLLATE latin1_general_cs NOT NULL,
  `TIPDES` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `TIPINSEDITOR` text COLLATE latin1_general_cs NOT NULL COMMENT 'AUTORE INSERIMENTO',
  `TIPINSDATE` varchar(8) COLLATE latin1_general_cs NOT NULL COMMENT 'Data Inserimento',
  `TIPINSTIME` varchar(8) COLLATE latin1_general_cs NOT NULL COMMENT 'Orario inserimento o modifica',
  `TIPUPDEDITOR` text COLLATE latin1_general_cs NOT NULL COMMENT 'AUTORE ULTIMA MODIFICA',
  `TIPUPDDATE` varchar(8) COLLATE latin1_general_cs NOT NULL COMMENT 'Data aggiornamento',
  `TIPUPDTIME` varchar(8) COLLATE latin1_general_cs NOT NULL COMMENT 'ORARIO AGGIORNAMENTO',
  PRIMARY KEY (`ROWID`),
  UNIQUE KEY `K_MASTER` (`TIPCOD`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs COMMENT='TIPOLOGIA PROCEDIMENTO';
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ANATIPIMPO` (
  `ROWID` int(11) NOT NULL AUTO_INCREMENT,
  `CODTIPOIMPO` smallint(6) NOT NULL COMMENT 'Codice tipo Importo',
  `DESCTIPOIMPO` text COLLATE latin1_general_cs NOT NULL COMMENT 'Descrizione',
  `CLASTIPOIMPO` smallint(6) NOT NULL COMMENT 'Classificazione',
  PRIMARY KEY (`ROWID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ANATSP` (
  `ROWID` int(11) NOT NULL AUTO_INCREMENT,
  `TSPCOD` double NOT NULL COMMENT 'Codice Interno Sportello',
  `TSPDES` varchar(100) COLLATE latin1_general_cs NOT NULL COMMENT 'Descrizione Sportello',
  `TSPIDE` varchar(6) COLLATE latin1_general_cs NOT NULL COMMENT 'identificativo suap',
  `TSPDEN` text COLLATE latin1_general_cs NOT NULL COMMENT 'denominazione suap',
  `TSPAMMIPA` varchar(50) COLLATE latin1_general_cs NOT NULL COMMENT 'CODICE IPA AMMINISTRAZIONE',
  `TSPAOO` varchar(50) COLLATE latin1_general_cs NOT NULL COMMENT 'codice aoo',
  `TSPCOM` varchar(100) COLLATE latin1_general_cs NOT NULL COMMENT 'comune',
  `TSPTIP` varchar(50) COLLATE latin1_general_cs NOT NULL COMMENT 'tipologia',
  `TSPWEB` text COLLATE latin1_general_cs NOT NULL COMMENT 'indirizzo sito web',
  `TSPMOD` text COLLATE latin1_general_cs NOT NULL COMMENT 'sito web modulistica',
  `TSPPEC` varchar(100) COLLATE latin1_general_cs NOT NULL COMMENT 'email pec',
  `TSPRES` varchar(6) COLLATE latin1_general_cs NOT NULL COMMENT 'Responsabile',
  `TSPCCA` varchar(4) COLLATE latin1_general_cs NOT NULL COMMENT 'Codice catastale',
  `TSPPRV` varchar(2) COLLATE latin1_general_cs NOT NULL COMMENT 'Provincia CCIAA',
  `TSPENT` varchar(4) COLLATE latin1_general_cs NOT NULL COMMENT 'Ente destinatario',
  `TSPIND` varchar(100) COLLATE latin1_general_cs NOT NULL COMMENT 'Indirizzo',
  `TSPNCI` varchar(8) COLLATE latin1_general_cs NOT NULL COMMENT 'numero civico',
  `TSPPRO` varchar(4) COLLATE latin1_general_cs NOT NULL COMMENT 'provincia',
  `TSPCAP` varchar(6) COLLATE latin1_general_cs NOT NULL COMMENT 'cap',
  `TSPTES` text COLLATE latin1_general_cs NOT NULL COMMENT 'TESORERIA',
  `TSPDEST` text COLLATE latin1_general_cs NOT NULL COMMENT 'TESORERIA',
  `TSPIBAN` text COLLATE latin1_general_cs NOT NULL COMMENT 'TESORERIA',
  `TSPBANCA` text COLLATE latin1_general_cs NOT NULL COMMENT 'BANCA',
  `TSPIST` varchar(6) COLLATE latin1_general_cs NOT NULL COMMENT 'codice istat',
  `TSPSUPERADMIN` double NOT NULL COMMENT 'Gruppo Super Admin',
  `TSPCLA` text COLLATE latin1_general_cs NOT NULL COMMENT 'Classificazione per protocollazione',
  `TSPUOP` text COLLATE latin1_general_cs NOT NULL COMMENT 'UFFICIO / UNITA OPERATIVA PROTOCOLLAZIONE',
  `TSPTDO` varchar(20) COLLATE latin1_general_cs NOT NULL COMMENT 'Tipo Documento Protocollo',
  `TSPTDOENDOPAR` varchar(20) COLLATE latin1_general_cs NOT NULL COMMENT 'TIPO DOCUMENTO PROTOCOLLO ENDO PROCEDIMENTO IN PARTENZA',
  `TSPTDOENDOARR` varchar(20) COLLATE latin1_general_cs NOT NULL COMMENT 'TIPO DOCUMENTO PROTOCOLLO ENDO PROCEDIMENTO IN ARRIVO',
  `TSPBLOCK` double NOT NULL COMMENT 'Flag per bolccare le funzioni dei pdf informativi',
  `TSPMETA` text COLLATE latin1_general_cs NOT NULL COMMENT 'Metedati Sportello',
  `TSPSWIFT` varchar(20) COLLATE latin1_general_cs NOT NULL COMMENT 'Codice swift',
  `TSPCC` varchar(15) COLLATE latin1_general_cs NOT NULL COMMENT 'Conto corrente bancario',
  `TSPCCP` varchar(15) COLLATE latin1_general_cs NOT NULL COMMENT 'Conto corrente postale',
  `TSPCAUSALECC` text COLLATE latin1_general_cs NOT NULL COMMENT 'Causale bonifico',
  `TSPCAUSALECCP` text COLLATE latin1_general_cs NOT NULL COMMENT 'Causale bollettino',
  `TSPACTORARIO` smallint(6) NOT NULL COMMENT 'FLAG ATTIVA ORARIO',
  `TSPTOLORARIO` smallint(6) NOT NULL COMMENT 'MIN TOLLERANZA SU ORARIO INOLTRO',
  `TSPSENDREMOTEMAIL` varchar(100) COLLATE latin1_general_cs NOT NULL COMMENT 'Indirizzo mail per invio remoto',
  `TSPSCO` double NOT NULL COMMENT 'Flag Sportello Condizionato',
  `TSPSDE` double NOT NULL COMMENT 'Sportello di Default',
  `TSPFASCICOLO` varchar(50) COLLATE latin1_general_cs NOT NULL COMMENT 'Fascicolo',
  `TSPSERIE` int(11) NOT NULL COMMENT 'Serie Fascicolo Procedimento',
  `TSPFIRMPA` text COLLATE latin1_general_cs NOT NULL COMMENT 'Firmatario in Partenza',
  `TSPRUOLO` varchar(50) COLLATE latin1_general_cs NOT NULL COMMENT 'Ruolo Utente per Protocollazione',
  `TSPPORT` varchar(10) COLLATE latin1_general_cs NOT NULL COMMENT 'Porta Mail per Invio Richieste',
  `TSPSECURESMTP` varchar(50) COLLATE latin1_general_cs NOT NULL COMMENT 'Secure Smtp per Invio Richieste',
  `TSPCLACONS` text COLLATE latin1_general_cs NOT NULL COMMENT 'Classificazione per Conservazione',
  `TSPUSER` varchar(100) COLLATE latin1_general_cs NOT NULL COMMENT 'User Mail per Invio Richieste',
  `TSPPASSWORD` varchar(50) COLLATE latin1_general_cs NOT NULL COMMENT 'Password Mail per Invio Richieste',
  `TSPMAIL` varchar(256) COLLATE latin1_general_cs NOT NULL COMMENT 'Indirizzo Mail per Invio Richieste',
  `TSPHOST` varchar(50) COLLATE latin1_general_cs NOT NULL COMMENT 'Host Mail per Invio Richieste',
  `TSPFROM` varchar(100) COLLATE latin1_general_cs NOT NULL COMMENT 'Mail From per Invio Richieste',
  `TSPMETAJSON` text COLLATE latin1_general_cs NOT NULL COMMENT 'Metadati in formato Json',
  `TSPGGSCAD` smallint(6) NOT NULL COMMENT 'Giorni Scadenza invio pratica',
  PRIMARY KEY (`ROWID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs COMMENT='SPORTELLI ON-LINE';
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ANAUNI` (
  `ROWID` int(11) NOT NULL AUTO_INCREMENT,
  `UNISET` varchar(6) COLLATE latin1_general_cs NOT NULL,
  `UNISER` varchar(6) COLLATE latin1_general_cs NOT NULL,
  `UNIOPE` varchar(6) COLLATE latin1_general_cs NOT NULL,
  `UNIADD` varchar(6) COLLATE latin1_general_cs NOT NULL,
  `UNIAD1` varchar(6) COLLATE latin1_general_cs NOT NULL,
  `UNIAD2` varchar(6) COLLATE latin1_general_cs NOT NULL,
  `UNIDES` varchar(40) COLLATE latin1_general_cs NOT NULL,
  `UNIRES` varchar(6) COLLATE latin1_general_cs NOT NULL,
  `UNIQUA` varchar(6) COLLATE latin1_general_cs NOT NULL,
  `UNIPRO` varchar(6) COLLATE latin1_general_cs NOT NULL,
  `UNIDAP` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `UNIAPE` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `UNITEL` varchar(40) COLLATE latin1_general_cs NOT NULL,
  `UNIFAX` varchar(40) COLLATE latin1_general_cs NOT NULL,
  `UNIEMA` varchar(100) COLLATE latin1_general_cs NOT NULL,
  `UNIMTA` text COLLATE latin1_general_cs NOT NULL,
  `UNIORA` varchar(100) COLLATE latin1_general_cs NOT NULL,
  `UNIGIO` varchar(100) COLLATE latin1_general_cs NOT NULL,
  `UNIFIA__1` varchar(50) COLLATE latin1_general_cs NOT NULL,
  `UNIFIA__2` varchar(50) COLLATE latin1_general_cs NOT NULL,
  `UNIFIA__3` varchar(50) COLLATE latin1_general_cs NOT NULL,
  `UNIFIN__1` double NOT NULL,
  `UNIFIN__2` double NOT NULL,
  `UNIFIN__3` double NOT NULL,
  PRIMARY KEY (`ROWID`),
  KEY `I_UNICOD` (`UNISET`,`UNISER`,`UNIOPE`,`UNIADD`,`UNIAPE`),
  KEY `I_UNIRES` (`UNIRES`,`UNIAPE`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs COMMENT='STRUTTURA PIANTA ORGANICA';
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ANAVAR` (
  `ROWID` int(11) NOT NULL AUTO_INCREMENT COMMENT 'ROWID',
  `VARCOD` varchar(20) COLLATE latin1_general_cs NOT NULL COMMENT 'CODICE',
  `VARDES` varchar(100) COLLATE latin1_general_cs NOT NULL COMMENT 'DESCRIZIONE',
  `VARTIP` varchar(2) COLLATE latin1_general_cs NOT NULL COMMENT 'TIPO',
  `VAREXP` text COLLATE latin1_general_cs NOT NULL COMMENT 'ESPRESSIONE',
  `VARCLA` varchar(6) COLLATE latin1_general_cs NOT NULL COMMENT 'CLASSIFICAZIONE',
  `VARFONTE` varchar(100) COLLATE latin1_general_cs NOT NULL COMMENT 'FONTE DATI SPECIALE DELLA VARIABILE',
  `VAREXPDOCX` text COLLATE latin1_general_cs NOT NULL COMMENT 'Espressione DOCX',
  PRIMARY KEY (`ROWID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ANNGPR` (
  `ROWID` int(11) NOT NULL AUTO_INCREMENT,
  `GPRNUM` varchar(10) COLLATE latin1_general_cs NOT NULL,
  `GPRNRI` varchar(4) COLLATE latin1_general_cs NOT NULL,
  `GPRRIG` varchar(70) COLLATE latin1_general_cs NOT NULL,
  `GPRSON` varchar(2) COLLATE latin1_general_cs NOT NULL,
  `GPRDAT` varchar(8) COLLATE latin1_general_cs NOT NULL,
  PRIMARY KEY (`ROWID`),
  KEY `I_GPRRIG` (`GPRNUM`,`GPRNRI`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ANPDOC` (
  `ROWID` int(11) NOT NULL AUTO_INCREMENT,
  `ANPKEY` varchar(6) COLLATE latin1_general_cs NOT NULL,
  `ANPFIL` varchar(50) COLLATE latin1_general_cs NOT NULL,
  `ANPORF` varchar(100) COLLATE latin1_general_cs NOT NULL,
  `ANPFDT` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `ANPFMT` varchar(6) COLLATE latin1_general_cs NOT NULL,
  `ANPLNK` varchar(100) COLLATE latin1_general_cs NOT NULL,
  `ANPNOT` text COLLATE latin1_general_cs NOT NULL,
  `ANPCLA` varchar(50) COLLATE latin1_general_cs NOT NULL,
  `ANPUTC` varchar(6) COLLATE latin1_general_cs NOT NULL,
  `ANPUTE` varchar(60) COLLATE latin1_general_cs NOT NULL,
  `ANPNAME` varchar(100) COLLATE latin1_general_cs NOT NULL,
  `ANPSEQ` varchar(100) COLLATE latin1_general_cs NOT NULL COMMENT 'Sequenza documento',
  PRIMARY KEY (`ROWID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs COMMENT='ALLEGATI PROCEDIMENTO';
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `CTRRTN` (
  `ROWID` int(11) NOT NULL AUTO_INCREMENT,
  `CTRKEY` text COLLATE latin1_general_cs NOT NULL COMMENT 'Chiave configurazione',
  `CTRSEQ` double NOT NULL COMMENT 'Sequenza filtro configurazioni',
  `CTRCOD` varchar(80) COLLATE latin1_general_cs NOT NULL COMMENT 'Codice controller',
  `CTRPRO` varchar(6) COLLATE latin1_general_cs NOT NULL COMMENT 'Codice procedimento modello',
  `CTRDES` text COLLATE latin1_general_cs NOT NULL COMMENT 'Descrizione Configurazione',
  `CTRNOT` text COLLATE latin1_general_cs NOT NULL COMMENT 'Note Utente',
  `CTRPAR` text COLLATE latin1_general_cs NOT NULL COMMENT 'Parametri di funzionemento istanza controller',
  PRIMARY KEY (`ROWID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs COMMENT='Configurazioni Routing Controllers';
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `FILENT` (
  `ROWID` int(11) NOT NULL AUTO_INCREMENT,
  `FILKEY` int(11) NOT NULL COMMENT 'Chiave Parametri',
  `FILDE1` varchar(36) COLLATE latin1_general_cs NOT NULL,
  `FILDE2` varchar(36) COLLATE latin1_general_cs NOT NULL,
  `FILDE3` varchar(36) COLLATE latin1_general_cs NOT NULL,
  `FILDE4` varchar(36) COLLATE latin1_general_cs NOT NULL,
  `FILDE5` varchar(36) COLLATE latin1_general_cs NOT NULL,
  `FILDE6` varchar(36) COLLATE latin1_general_cs NOT NULL,
  `FILCOD` smallint(6) NOT NULL,
  `FILVAL` text COLLATE latin1_general_cs NOT NULL,
  PRIMARY KEY (`ROWID`),
  UNIQUE KEY `I_FILKEY` (`FILKEY`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs COMMENT='PARAMETRI VARI';
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `GENMETADATA` (
  `ROW_ID` int(11) NOT NULL AUTO_INCREMENT,
  `CLASSE` varchar(50) COLLATE latin1_general_cs NOT NULL COMMENT 'Classe Metadati (Tabella collegata)',
  `CHIAVE` varchar(100) COLLATE latin1_general_cs NOT NULL COMMENT 'Chiave identificativa del record della tabella collegata',
  `CAMPO` varchar(100) COLLATE latin1_general_cs NOT NULL COMMENT 'Identificativo del valore gestito',
  `VALORE` text COLLATE latin1_general_cs NOT NULL COMMENT 'Valore gestito',
  PRIMARY KEY (`ROW_ID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs COMMENT='TABELLA GENERICA PER METADATI';
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ITECONTROLLI` (
  `ROWID` int(11) NOT NULL AUTO_INCREMENT,
  `ITEKEY` varchar(22) COLLATE latin1_general_cs NOT NULL,
  `SEQUENZA` int(11) NOT NULL,
  `DESCRIZIONE` text COLLATE latin1_general_cs NOT NULL,
  `MESSAGGIO` text COLLATE latin1_general_cs NOT NULL,
  `ESPRESSIONE` text COLLATE latin1_general_cs NOT NULL,
  `AZIONE` int(11) NOT NULL,
  PRIMARY KEY (`ROWID`),
  KEY `ITEKEY` (`ITEKEY`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ITEDAG` (
  `ROWID` int(11) NOT NULL AUTO_INCREMENT,
  `ITECOD` varchar(6) COLLATE latin1_general_cs NOT NULL COMMENT 'CODICE PROCEDIMENTO',
  `ITEKEY` varchar(22) COLLATE latin1_general_cs NOT NULL COMMENT 'CHIAVE CODICE PROCEDIMENTO/PASSO',
  `ITDDES` text COLLATE latin1_general_cs NOT NULL COMMENT 'DESCRIZIONE CAMPO',
  `ITDSEQ` double NOT NULL COMMENT 'SEQUENZA',
  `ITDKEY` varchar(60) COLLATE latin1_general_cs NOT NULL COMMENT 'NOME CAMPO',
  `ITDALIAS` varchar(60) COLLATE latin1_general_cs NOT NULL COMMENT 'Nome Campo PDF',
  `ITDVAL` text COLLATE latin1_general_cs NOT NULL COMMENT 'VALORE CAMPO / VALORE SELECT',
  `ITDSET` varchar(40) COLLATE latin1_general_cs NOT NULL,
  `ITDTIP` varchar(20) COLLATE latin1_general_cs NOT NULL COMMENT 'TIPO DATO',
  `ITDCTR` text COLLATE latin1_general_cs NOT NULL COMMENT 'FORMULA DI CONTROLLO',
  `ITDNOT` text COLLATE latin1_general_cs NOT NULL COMMENT 'NOTE',
  `ITDLAB` text COLLATE latin1_general_cs NOT NULL COMMENT 'Label per input',
  `ITDTIC` varchar(50) COLLATE latin1_general_cs NOT NULL COMMENT 'TIPO INPUT',
  `ITDROL` int(11) NOT NULL COMMENT 'Campo READONLY',
  `ITDVCA` text COLLATE latin1_general_cs NOT NULL COMMENT 'Tipo Validità',
  `ITDREV` text COLLATE latin1_general_cs NOT NULL COMMENT 'Regula Expression validità',
  `ITDLEN` varchar(50) COLLATE latin1_general_cs NOT NULL COMMENT 'Lunghezza campo text',
  `ITDDIM` varchar(50) COLLATE latin1_general_cs NOT NULL COMMENT 'Dimensione campo',
  `ITDDIZ` varchar(10) COLLATE latin1_general_cs NOT NULL COMMENT 'Flag per valore campo',
  `ITDACA` int(11) NOT NULL COMMENT 'Flag per andare a capo checkbox',
  `ITDPOS` varchar(50) COLLATE latin1_general_cs NOT NULL COMMENT 'Posizione Label',
  `ITDMETA` text COLLATE latin1_general_cs NOT NULL COMMENT 'Metadati',
  `ITDLABSTYLE` text COLLATE latin1_general_cs NOT NULL COMMENT 'Style Label',
  `ITDFIELDSTYLE` text COLLATE latin1_general_cs NOT NULL COMMENT 'Style Campo',
  `ITDEXPROUT` text COLLATE latin1_general_cs NOT NULL,
  `ITDCLASSE` text COLLATE latin1_general_cs NOT NULL COMMENT 'CLASSE DISEGNO',
  `ITDMETODO` text COLLATE latin1_general_cs NOT NULL COMMENT 'METODO DISEGNO',
  `ITDFIELDERRORACT` smallint(6) NOT NULL,
  `ITDFIELDCLASS` text COLLATE latin1_general_cs NOT NULL COMMENT 'Attributo class campo',
  PRIMARY KEY (`ROWID`),
  KEY `ITDKEY` (`ITDKEY`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs COMMENT='DATI AGG. PROCEDIMENTO';
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ITEDEST` (
  `ROW_ID` int(11) NOT NULL AUTO_INCREMENT,
  `ITECOD` varchar(6) COLLATE latin1_general_cs NOT NULL COMMENT 'CODICE PROCEDIMENTO',
  `ITEKEY` varchar(22) COLLATE latin1_general_cs NOT NULL COMMENT 'CHIAVE PASSO',
  `CODICE` varchar(6) COLLATE latin1_general_cs NOT NULL COMMENT 'CODICE DESTINATARIO',
  `RUOLO` varchar(4) COLLATE latin1_general_cs NOT NULL COMMENT 'CODICE RUOLO',
  PRIMARY KEY (`ROW_ID`),
  KEY `I_ITECOD` (`ITECOD`),
  KEY `I_ITEKEY` (`ITEKEY`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ITEDIAGGRUPPI` (
  `ROW_ID` int(11) NOT NULL AUTO_INCREMENT,
  `DESCRIZIONE` varchar(100) COLLATE latin1_general_cs NOT NULL COMMENT 'Descrizione del gruppo',
  `PRANUM` varchar(6) COLLATE latin1_general_cs NOT NULL COMMENT 'Chiave procedimento di ANAPRA',
  `STATO` int(11) NOT NULL COMMENT 'Stato di Default al momento del ribaltamento (0-Nascosto; 1-Visibile)',
  PRIMARY KEY (`ROW_ID`),
  KEY `ANAPRA` (`PRANUM`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs COMMENT='Definizioni dei gruppi per gestire le figure nascoste di un ';
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ITEDIAGPASSIGRUPPI` (
  `ROW_ID` int(11) NOT NULL AUTO_INCREMENT,
  `ITEKEY` varchar(30) COLLATE latin1_general_cs NOT NULL COMMENT 'CHIAVE PASSO DI ITEPAS',
  `ROW_ID_ITEDIAGGRUPPI` int(11) NOT NULL COMMENT 'ROW_ID DI ITEDIAGGRUPPI',
  PRIMARY KEY (`ROW_ID`),
  KEY `ITEPAS` (`ITEKEY`),
  KEY `ITEDIAGGRUPPI` (`ROW_ID_ITEDIAGGRUPPI`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs COMMENT='Collega i passi presenti in un procedimento amministrativo (';
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ITEDIS` (
  `ROWID` int(11) NOT NULL AUTO_INCREMENT,
  `ITEPRA` varchar(6) COLLATE latin1_general_cs NOT NULL COMMENT 'CODICE PROCEDIMENTO',
  `DISCOD` varchar(20) COLLATE latin1_general_cs NOT NULL COMMENT 'CODICE DISCIPLINA',
  PRIMARY KEY (`ROWID`),
  KEY `A_ITEPRA` (`ITEPRA`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs COMMENT='NORMATIVA PER PROCEDIMENTO';
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ITEEVT` (
  `ROWID` int(11) NOT NULL AUTO_INCREMENT,
  `ITEPRA` varchar(6) COLLATE latin1_general_cs NOT NULL COMMENT 'CODICE PRATICA',
  `ITENUMEST` varchar(100) COLLATE latin1_general_cs NOT NULL COMMENT 'CODICE ESTERNO PROCEDIMENTO',
  `IEVCOD` varchar(6) COLLATE latin1_general_cs NOT NULL COMMENT 'CODICE EVENTO',
  `IEVDESCR` text COLLATE latin1_general_cs NOT NULL COMMENT 'DESCRIZIONE COMPELTA PROC ED EVENTO',
  `IEVDVA` varchar(8) COLLATE latin1_general_cs NOT NULL COMMENT 'DA DATA VALIDITA EVENTO',
  `IEVAVA` varchar(8) COLLATE latin1_general_cs NOT NULL COMMENT 'A DATA VALIDITA EVENTO',
  `IEVTSP` double NOT NULL COMMENT 'sportello on-line',
  `IEVTIP` varchar(6) COLLATE latin1_general_cs NOT NULL COMMENT 'tipologia',
  `IEVSTT` double NOT NULL COMMENT 'Settore',
  `IEVATT` double NOT NULL COMMENT 'Attivita',
  `FLAG_TEMPLATE` varchar(10) COLLATE latin1_general_cs NOT NULL,
  `IEVSEQ` smallint(6) NOT NULL COMMENT 'Sequenza di Visualizzazione',
  `PEREVT` smallint(6) NOT NULL COMMENT 'Flag Personalizza Evento',
  `IEVUUID` varchar(36) COLLATE latin1_general_cs DEFAULT NULL,
  `PRAUUID` varchar(36) COLLATE latin1_general_cs DEFAULT NULL,
  PRIMARY KEY (`ROWID`),
  KEY `I_IEVUUID` (`IEVUUID`),
  KEY `I_PRAUUID` (`PRAUUID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ITELIS` (
  `ROWID` int(11) NOT NULL AUTO_INCREMENT,
  `CODVAL` smallint(6) NOT NULL COMMENT 'Codice validita',
  `SEQUENZA` smallint(6) NOT NULL COMMENT 'SEQUENZA VISUALIZZAZIONE',
  `CODICETIPOIMPO` smallint(6) NOT NULL COMMENT 'CODICE TIPO ONERE IMPORTO',
  `CODICESPORTELLO` double NOT NULL COMMENT 'CODICE SPORTELLO',
  `TIPOPASSO` varchar(6) COLLATE latin1_general_cs NOT NULL COMMENT 'TIPO PASSO',
  `SETTORE` smallint(6) NOT NULL COMMENT 'SETTORE',
  `ATTIVITA` smallint(6) NOT NULL COMMENT 'ATTIVITA',
  `PROCEDIMENTO` varchar(6) COLLATE latin1_general_cs NOT NULL COMMENT 'PROCEDIMENTO',
  `ITEKEY` varchar(22) COLLATE latin1_general_cs NOT NULL COMMENT 'Chiave Passo',
  `EVENTO` varchar(6) COLLATE latin1_general_cs NOT NULL COMMENT 'CODICE EVENTO',
  `IMPORTO` double NOT NULL COMMENT 'Importo',
  `DESCRIZIONE` text COLLATE latin1_general_cs NOT NULL COMMENT 'Descrizione Listino',
  `TARIFFA` varchar(6) COLLATE latin1_general_cs NOT NULL COMMENT 'Codice Tariffa',
  `ATTIVO` smallint(6) NOT NULL COMMENT 'Flag Attivo',
  `AGGREGATO` int(11) NOT NULL COMMENT 'Codice Aggregato',
  `CLAUUID` varchar(36) COLLATE latin1_general_cs NOT NULL COMMENT 'UUID classificazione',
  PRIMARY KEY (`ROWID`),
  KEY `I_CLAUUID` (`CLAUUID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ITELISVAL` (
  `ROWID` int(11) NOT NULL AUTO_INCREMENT,
  `CODLISVAL` smallint(6) NOT NULL COMMENT 'CODICE VALIDITA',
  `DESCLISVAL` varchar(100) COLLATE latin1_general_cs NOT NULL COMMENT 'DESCRIZIONE',
  `INILISVAL` varchar(8) COLLATE latin1_general_cs NOT NULL COMMENT 'VALIDO DAL',
  `FINLISVAL` varchar(8) COLLATE latin1_general_cs NOT NULL COMMENT 'VALIDO AL',
  PRIMARY KEY (`ROWID`),
  UNIQUE KEY `CODLISVAL` (`CODLISVAL`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ITENOR` (
  `ROWID` int(11) NOT NULL AUTO_INCREMENT,
  `ITEPRA` varchar(6) COLLATE latin1_general_cs NOT NULL COMMENT 'CODICE PROCEDIMENTO',
  `NORCOD` varchar(20) COLLATE latin1_general_cs NOT NULL COMMENT 'CODICE NORMATIVA',
  PRIMARY KEY (`ROWID`),
  KEY `A_ITEPRA` (`ITEPRA`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs COMMENT='NORMATIVA PER PROCEDIMENTO';
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ITEPAS` (
  `ROWID` int(11) NOT NULL AUTO_INCREMENT,
  `ITECOD` varchar(6) COLLATE latin1_general_cs NOT NULL COMMENT 'Codice Procedimento',
  `ITESET` varchar(6) COLLATE latin1_general_cs NOT NULL COMMENT 'Codice Settore',
  `ITESER` varchar(6) COLLATE latin1_general_cs NOT NULL COMMENT 'Codice Servizio',
  `ITEOPE` varchar(6) COLLATE latin1_general_cs NOT NULL COMMENT 'Codice Unità Operativa',
  `ITERES` varchar(6) COLLATE latin1_general_cs NOT NULL COMMENT 'Codice Responsabile',
  `ITEDES` text COLLATE latin1_general_cs NOT NULL COMMENT 'Descrizione Passo',
  `ITEGIO` double NOT NULL COMMENT 'Giorni Validità',
  `ITESEQ` double NOT NULL COMMENT 'Sequenza Passo',
  `ITETER` varchar(80) COLLATE latin1_general_cs NOT NULL COMMENT 'Utente+Data+Ora',
  `ITECLT` varchar(6) COLLATE latin1_general_cs NOT NULL COMMENT 'Tipo Passo',
  `ITETES` varchar(6) COLLATE latin1_general_cs NOT NULL COMMENT 'non usato',
  `ITEWRD` varchar(100) COLLATE latin1_general_cs NOT NULL COMMENT 'File Associato',
  `ITEOBL` smallint(6) NOT NULL COMMENT 'Flag Obbligatorio',
  `ITEUPL` smallint(6) NOT NULL COMMENT 'Flag Upload',
  `ITEDOW` smallint(6) NOT NULL COMMENT 'Flag Download',
  `ITECTR` varchar(50) COLLATE latin1_general_cs NOT NULL COMMENT 'procedura controllo',
  `ITEIMG` varchar(100) COLLATE latin1_general_cs NOT NULL COMMENT 'Immagine Associata',
  `ITENOT` text COLLATE latin1_general_cs NOT NULL COMMENT 'Note',
  `ITEPUB` double NOT NULL COMMENT 'Flag Pubblicato',
  `ITECOM` double NOT NULL COMMENT 'Flag Comunicazione',
  `ITEPAY` smallint(6) NOT NULL COMMENT 'Indirizzo Pagamento',
  `ITEAML` double NOT NULL COMMENT 'non usato',
  `ITEIVA` varchar(8) COLLATE latin1_general_cs NOT NULL COMMENT 'dalla Data di Validità',
  `ITEFVA` varchar(8) COLLATE latin1_general_cs NOT NULL COMMENT 'alla Data di Validità',
  `ITESTR` double NOT NULL COMMENT 'Flag Streaming',
  `ITEPST` smallint(6) NOT NULL COMMENT 'non usato',
  `ITEKEY` varchar(22) COLLATE latin1_general_cs NOT NULL COMMENT 'Chiave Univoca Passo',
  `ITEQST` smallint(6) NOT NULL COMMENT 'Flag Domanda',
  `ITEDAT` smallint(6) NOT NULL COMMENT 'Flag raccolta Dati',
  `ITEVPA` varchar(22) COLLATE latin1_general_cs NOT NULL COMMENT 'Destinazione Risposta SI',
  `ITEVPN` varchar(22) COLLATE latin1_general_cs NOT NULL COMMENT 'Destinazione Risposta NO',
  `ITEIRE` smallint(6) NOT NULL COMMENT 'Flag Passo Invio Mail',
  `ITEMLT` smallint(6) NOT NULL COMMENT 'Flag MultiUpload',
  `ITEINF` varchar(100) COLLATE latin1_general_cs NOT NULL COMMENT 'non usato',
  `ITESTA` smallint(6) NOT NULL COMMENT 'non usato',
  `ITETIM` smallint(6) NOT NULL COMMENT 'non usato',
  `ITECDE` varchar(6) COLLATE latin1_general_cs NOT NULL COMMENT 'Codice Destinatario Interno al Comune',
  `ITERUO` varchar(4) COLLATE latin1_general_cs NOT NULL COMMENT 'Enti che possono gestire il Passo',
  `ITECTP` varchar(22) COLLATE latin1_general_cs NOT NULL COMMENT 'Chive Passo Download di controllo campi',
  `ITEINT` double NOT NULL COMMENT 'Flag invia mail intestatario',
  `ITEIDR` smallint(6) NOT NULL COMMENT 'FLAG INCLUSIONE NEL DETTAGIO RAPPORTO RICHIESTA',
  `ITEEXT` text COLLATE latin1_general_cs NOT NULL COMMENT 'estensioni supportate',
  `ITEDRR` smallint(6) NOT NULL COMMENT 'FLAG PASSO SCARICO RAPPORTO COMPLETO PDF RICHIESTA',
  `ITESTAP` int(11) NOT NULL COMMENT 'stato apertura passo',
  `ITESTCH` int(11) NOT NULL COMMENT 'stato chiusura passo',
  `ITEATE` text COLLATE latin1_general_cs NOT NULL COMMENT 'Espressione serializzata per attivazione passo',
  `ITEDIS` double NOT NULL COMMENT 'Flag Passo Distinta',
  `ITEZIP` double NOT NULL COMMENT 'Flag Passo Download ZIP',
  `ITEIFC` double NOT NULL COMMENT 'Flag per file comunica',
  `ITEMRI` double NOT NULL COMMENT 'blocca mail richiedente',
  `ITEMRE` double NOT NULL COMMENT 'blocca mail responsabile',
  `ITEURL` text COLLATE latin1_general_cs NOT NULL COMMENT 'url associato',
  `ITETBA` text COLLATE latin1_general_cs NOT NULL COMMENT 'id testo base',
  `ITETAL` varchar(80) COLLATE latin1_general_cs NOT NULL COMMENT 'tipologia allegato',
  `ITEFILE` double NOT NULL COMMENT 'Passo inserimento automatico file',
  `ITERIF` smallint(6) NOT NULL COMMENT 'Flag Passo Riferiemnto ad un procedimento',
  `ITEPROC` varchar(6) COLLATE latin1_general_cs NOT NULL COMMENT 'Codice Procedimento di riferimento',
  `ITEDAP` varchar(22) COLLATE latin1_general_cs NOT NULL COMMENT 'Dal Passo',
  `ITEALP` varchar(22) COLLATE latin1_general_cs NOT NULL COMMENT 'Al Passo',
  `ITEPRIV` double NOT NULL COMMENT 'Flag passo italsoft',
  `ITECOL` double NOT NULL COMMENT 'Numero colonne',
  `ITECTB` double NOT NULL COMMENT 'produci modello testo base',
  `ITEMETA` text COLLATE latin1_general_cs,
  `ITERDM` double NOT NULL COMMENT 'Raccolta dati Multipla',
  `ITENRA` varchar(100) COLLATE latin1_general_cs NOT NULL COMMENT 'Numero raccolte multiple',
  `ITEQALLE` tinyint(4) NOT NULL COMMENT 'QUALIFICA ALLEGATO',
  `ITEQCLA` tinyint(4) NOT NULL COMMENT 'CHIEDI CLASSIFICAZIONE ALLEGATO',
  `ITEQDEST` tinyint(4) NOT NULL COMMENT 'CHIEDI DESTINAZIONE ALLEGATO',
  `ITEQNOTE` tinyint(4) NOT NULL COMMENT 'CHIEDI NOTE ALLEGATO',
  `ITEQSTDAG` smallint(6) NOT NULL COMMENT 'PASSO QUESTIONARIO DA DATI AGGIUNTIVI',
  `ITEOBE` text COLLATE latin1_general_cs NOT NULL COMMENT 'Espressione Obbligatorio se',
  `ITECOMPSEQ` double NOT NULL,
  `ITECOMPFLAG` varchar(10) COLLATE latin1_general_cs NOT NULL,
  `ITEAGE` double NOT NULL COMMENT 'PASSO INOLTRO AGENZIA',
  `ITEDWP` varchar(22) COLLATE latin1_general_cs NOT NULL COMMENT 'Downlaod Upload Precedente',
  `ITENOTSTYLE` text COLLATE latin1_general_cs NOT NULL COMMENT 'Note Style',
  `ITEHTML` text COLLATE latin1_general_cs NOT NULL,
  `ITEHELP` text COLLATE latin1_general_cs NOT NULL,
  `TEMPLATEKEY` varchar(22) COLLATE latin1_general_cs NOT NULL,
  `ITEPRR` double NOT NULL COMMENT 'Passo Protocollo Remoto',
  `ITECUSTOMTML` varchar(200) COLLATE latin1_general_cs NOT NULL COMMENT 'Nome template personalizzato',
  `ITESOSTF` smallint(6) NOT NULL COMMENT 'Passo scelta files da sostituire',
  `ITERICUNI` smallint(6) NOT NULL COMMENT 'Accorpa richieste per pratica unica',
  `ITERICSUB` smallint(6) NOT NULL COMMENT 'Passo di gestione richiesta dove accorpare',
  `ITEDEFSTATO` int(11) NOT NULL COMMENT 'STATO DI DEFAULT DEL PASSO',
  `ITEKPRE` varchar(30) COLLATE latin1_general_cs NOT NULL COMMENT 'CHIAVE PASSO ANTECEDENTE',
  `ITECARICAAUTO` smallint(6) NOT NULL COMMENT 'CARICA AUTOM SU FASCICOLO',
  `ITEAPRIAUTO` smallint(6) NOT NULL COMMENT 'APRI PASSO AL CARICA SU FASCICOLO',
  `ITEASSAUTO` varchar(20) COLLATE latin1_general_cs NOT NULL COMMENT 'ASSEGNA AUTOMATICAMENTE A TIPO SOGGETTO',
  `ITEMIGRA` varchar(20) COLLATE latin1_general_cs NOT NULL,
  `ITECOMDEST` double NOT NULL COMMENT 'Crea passi comunicazione da destinazione',
  `ITETDC` varchar(100) COLLATE latin1_general_cs NOT NULL COMMENT 'Tipo Data Attivazione Conteggio',
  `ITEDCS` varchar(8) COLLATE latin1_general_cs NOT NULL COMMENT 'Data attivazione conteggio stimata',
  `ITEGEF` double NOT NULL COMMENT 'giorni effettivamente utilizzati',
  `ITEDISCOMUNICA` double NOT NULL COMMENT 'Flag Distinta Infocamere',
  `ROWID_DOC_CLASSIFICAZIONE` int(11) NOT NULL,
  `ITEPDR` tinyint(4) NOT NULL COMMENT 'Passo dipedenza rapporto richiesta',
  `ITEPRI` smallint(6) NOT NULL COMMENT 'Flag Documento Principale',
  `ITEFLRISERVATO` smallint(6) NOT NULL COMMENT 'Flag tipologia riservatezza',
  `ITEEXPRRISERVATO` text COLLATE latin1_general_cs NOT NULL COMMENT 'Espressione di valutazione riservatezza',
  PRIMARY KEY (`ROWID`),
  KEY `A_ITECOD` (`ITECOD`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs COMMENT='PASSI PROCEDIMENTO';
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ITEPRAOBB` (
  `ROWID` int(11) NOT NULL AUTO_INCREMENT,
  `OBBPRA` varchar(6) COLLATE latin1_general_cs NOT NULL COMMENT 'PRATICA PADRE',
  `OBBEVCOD` varchar(6) COLLATE latin1_general_cs NOT NULL COMMENT 'EVENTO PADRE',
  `OBBSUBPRA` varchar(6) COLLATE latin1_general_cs NOT NULL COMMENT 'PRATICA OBBLIGATORIA',
  `OBBSUBEVCOD` varchar(6) COLLATE latin1_general_cs NOT NULL COMMENT 'EVENTO PRATICA OBBLIGATORIA',
  `OBBEXPRCTR` text COLLATE latin1_general_cs NOT NULL COMMENT 'ESPRESSIONE DI OBBLIGATORIETA''',
  PRIMARY KEY (`ROWID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs COMMENT='PRATICHE OBBLIGATORIE ';
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ITEREQ` (
  `ROWID` int(11) NOT NULL AUTO_INCREMENT,
  `ITEPRA` varchar(6) COLLATE latin1_general_cs NOT NULL COMMENT 'CODICE PRATICA',
  `REQCOD` int(11) NOT NULL COMMENT 'CODICE REQUISITO',
  PRIMARY KEY (`ROWID`),
  KEY `A_ITEPRA` (`ITEPRA`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs COMMENT='REQUISITI PER PRATICA';
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ITEVPADETT` (
  `ROW_ID` int(11) NOT NULL AUTO_INCREMENT,
  `ITECOD` varchar(6) COLLATE latin1_general_cs NOT NULL,
  `ITEKEY` varchar(22) COLLATE latin1_general_cs NOT NULL,
  `ITESEQEXPR` double NOT NULL,
  `ITEVPA` varchar(30) COLLATE latin1_general_cs NOT NULL,
  `ITEEXPRVPA` text COLLATE latin1_general_cs NOT NULL,
  `ITEVPADESC` text COLLATE latin1_general_cs NOT NULL,
  PRIMARY KEY (`ROW_ID`),
  KEY `I_ITEVPADETT_ITECOD` (`ITECOD`),
  KEY `I_ITEVPADETT_ITEKEY` (`ITEKEY`),
  KEY `I_ITEVPADETT` (`ITEVPA`)
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
CREATE TABLE `ORARIFO` (
  `ROWID` int(11) NOT NULL AUTO_INCREMENT,
  `ORTSPCOD` double NOT NULL COMMENT 'Sportello',
  `ORTIPO` varchar(2) COLLATE latin1_general_cs NOT NULL COMMENT 'Tipo giorno',
  `ORDATA` varchar(8) COLLATE latin1_general_cs NOT NULL COMMENT 'Data',
  `ORINI` varchar(8) COLLATE latin1_general_cs NOT NULL COMMENT 'Ora inizio',
  `ORFIN` varchar(8) COLLATE latin1_general_cs NOT NULL COMMENT 'Ora fine',
  `ORGIORNOSTR` varchar(2) COLLATE latin1_general_cs NOT NULL COMMENT 'Giorno Settimana stringa',
  `ORGIORNONUM` smallint(6) NOT NULL COMMENT 'Giorno Settimana numero',
  `ORNEGA` smallint(6) NOT NULL COMMENT 'Nega per tutto il giorno',
  `ORDESCRIZIONE` varchar(50) COLLATE latin1_general_cs NOT NULL,
  `ORUTEADD` varchar(50) COLLATE latin1_general_cs NOT NULL,
  `ORDATEADD` text COLLATE latin1_general_cs NOT NULL,
  `ORUTEEDIT` varchar(50) COLLATE latin1_general_cs NOT NULL,
  `ORDATEEDIT` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `ORORAEDIT` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `ORDATEANN` varchar(8) COLLATE latin1_general_cs NOT NULL,
  PRIMARY KEY (`ROWID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `PARAMBO` (
  `ROWID` int(11) NOT NULL AUTO_INCREMENT,
  `PRANUM` varchar(6) COLLATE latin1_general_cs NOT NULL COMMENT 'Codice Procedimento',
  `PRADAGVIS` smallint(6) NOT NULL COMMENT 'Visualizzazione dati aggiuntivi',
  `PRADAGGES` smallint(6) NOT NULL COMMENT 'Getione dati aggiuntivi',
  `PRAFLNODADD` smallint(6) NOT NULL COMMENT 'Blocca Aggiunta campo aggiuntivo',
  `PRAFLNODDEL` smallint(6) NOT NULL COMMENT 'Blocca Cancellazione campo aggiuntivo',
  `PRAFLEDITST` smallint(6) NOT NULL COMMENT 'Abilita il disegno dei campi di edit standard',
  PRIMARY KEY (`ROWID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs COMMENT='Gestione dei campi aggiuntivi e raccolta dati back office';
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `PASDAG` (
  `ROWID` int(11) NOT NULL AUTO_INCREMENT,
  `PASCOD` varchar(6) COLLATE latin1_general_cs NOT NULL,
  `PASIDC` varchar(60) COLLATE latin1_general_cs NOT NULL,
  `PASSEQ` double NOT NULL,
  PRIMARY KEY (`ROWID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs COMMENT='DATI AGGIUNTIVI PRATICA';
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `PASDOC` (
  `ROWID` int(11) NOT NULL AUTO_INCREMENT,
  `PASKEY` varchar(30) COLLATE latin1_general_cs NOT NULL COMMENT 'CHIAVE PASSO',
  `PASFIL` varchar(200) COLLATE latin1_general_cs NOT NULL,
  `PASSTA` varchar(100) COLLATE latin1_general_cs NOT NULL COMMENT 'Stato Allegato',
  `PASORF` varchar(100) COLLATE latin1_general_cs NOT NULL,
  `PASFDT` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `PASFMT` varchar(6) COLLATE latin1_general_cs NOT NULL,
  `PASLNK` varchar(100) COLLATE latin1_general_cs NOT NULL,
  `PASNOT` text COLLATE latin1_general_cs NOT NULL,
  `PASCLA` varchar(50) COLLATE latin1_general_cs NOT NULL,
  `PASTESTOBASE` text COLLATE latin1_general_cs NOT NULL COMMENT 'TESTO BASE DI PROVENIENZA',
  `PASUTC` varchar(6) COLLATE latin1_general_cs NOT NULL,
  `PASUTE` varchar(60) COLLATE latin1_general_cs NOT NULL,
  `PASSTATO` varchar(100) COLLATE latin1_general_cs NOT NULL,
  `PASTIPO` varchar(30) COLLATE latin1_general_cs NOT NULL,
  `PASLOG` text COLLATE latin1_general_cs NOT NULL COMMENT 'Stato documento',
  `PASNAME` text COLLATE latin1_general_cs NOT NULL COMMENT 'NOME ORIGINALE ALLEGATO',
  `PASMD5` text COLLATE latin1_general_cs NOT NULL,
  `PASEVI` double NOT NULL COMMENT 'flag allegato evidenziato',
  `PASLOCK` double NOT NULL COMMENT 'Flag allegato bloccato',
  `PASCLAS` varchar(30) COLLATE latin1_general_cs NOT NULL COMMENT 'Classificazione SUE',
  `PASDEST` text COLLATE latin1_general_cs NOT NULL COMMENT 'Destinazioni SUE',
  `PASNOTE` text COLLATE latin1_general_cs NOT NULL COMMENT 'Note SUE',
  `PASSERVIZIO` smallint(6) NOT NULL COMMENT 'DOCUMENTO DI SERVIZIO',
  `PASDAFIRM` smallint(6) NOT NULL COMMENT 'DOCUMENTO DA FIRMARE',
  `PASDATAFIRMA` varchar(8) COLLATE latin1_general_cs NOT NULL COMMENT 'DATA DELLA FIRMA',
  `PASUTELOG` varchar(20) COLLATE latin1_general_cs NOT NULL COMMENT 'UTENTE CARICAMENTO ALLEGATO',
  `PASDATADOC` varchar(8) COLLATE latin1_general_cs NOT NULL COMMENT 'DATA ALLEGATO',
  `PASORADOC` varchar(8) COLLATE latin1_general_cs NOT NULL COMMENT 'ORA ALLEGATO',
  `PASMETA` text COLLATE latin1_general_cs NOT NULL COMMENT 'Metadati allegato',
  `PASPRTCLASS` varchar(20) COLLATE latin1_general_cs NOT NULL COMMENT 'TABELLA ORIGINE PROTOCOLLAZIONE',
  `PASPRTROWID` int(11) NOT NULL COMMENT 'ID REC TABELLA ORIGINE PROTOCOLLAZIONE',
  `PASROWIDBASE` int(11) NOT NULL,
  `PASSUBTIPO` varchar(10) COLLATE latin1_general_cs NOT NULL,
  `PASSHA2` varchar(64) COLLATE latin1_general_cs NOT NULL,
  `PASPUB` double NOT NULL COMMENT 'Flag Allegato Pubblicato',
  `PASSHA2SOST` varchar(64) COLLATE latin1_general_cs NOT NULL COMMENT 'SHA2 DEL FILE SOSTITUITO (FLAG)',
  `PASMIGRA` text COLLATE latin1_general_cs NOT NULL COMMENT 'Old path allegato',
  `PASDWONLINE` smallint(6) NOT NULL COMMENT 'Abilita Download on-line',
  `PASFLCDS` smallint(6) NOT NULL COMMENT 'Flag Attivazione Allegato per CDS',
  `PASRIS` smallint(6) NOT NULL COMMENT 'Flag riservatezza',
  `PASPRI` smallint(6) NOT NULL COMMENT 'FLAG ALLEGATO PRINCIPALE',
  `PASRELUUID` varchar(36) COLLATE latin1_general_cs NOT NULL COMMENT 'UUID UNIVOCO DEL RECORD',
  `PASRELCHIAVE` int(11) NOT NULL COMMENT 'CHIAVE TABELLA RELAZIONATA (ROWID)',
  `PASRELCLASSE` varchar(20) COLLATE latin1_general_cs NOT NULL COMMENT 'RIFERIMENTO A TABELLA RELAZIONATA',
  PRIMARY KEY (`ROWID`),
  KEY `I_PASKEY` (`PASKEY`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `PIANOM` (
  `ROWID` int(11) NOT NULL AUTO_INCREMENT,
  `NOMSET` varchar(6) COLLATE latin1_general_cs NOT NULL,
  `NOMSER` varchar(6) COLLATE latin1_general_cs NOT NULL,
  `NOMOPE` varchar(6) COLLATE latin1_general_cs NOT NULL,
  `NOMADD` varchar(6) COLLATE latin1_general_cs NOT NULL,
  `NOMAD1` varchar(6) COLLATE latin1_general_cs NOT NULL,
  `NOMAD2` varchar(6) COLLATE latin1_general_cs NOT NULL,
  `NOMCOG` varchar(26) COLLATE latin1_general_cs NOT NULL,
  `NOMNOM` varchar(26) COLLATE latin1_general_cs NOT NULL,
  `NOMQUA` varchar(6) COLLATE latin1_general_cs NOT NULL,
  `NOMPRO` varchar(6) COLLATE latin1_general_cs NOT NULL,
  `NOMDAP` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `NOMAPE` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `NOMRES` varchar(6) COLLATE latin1_general_cs NOT NULL,
  `NOMPSW` varchar(10) COLLATE latin1_general_cs NOT NULL,
  `NOMANN` varchar(50) COLLATE latin1_general_cs NOT NULL,
  `NOMAN2` varchar(50) COLLATE latin1_general_cs NOT NULL,
  `NOMAN3` varchar(50) COLLATE latin1_general_cs NOT NULL,
  `PIAANN` varchar(4) COLLATE latin1_general_cs NOT NULL,
  PRIMARY KEY (`ROWID`),
  KEY `I_PIAQUA` (`NOMQUA`),
  KEY `I_PIARES` (`NOMRES`,`PIAANN`),
  KEY `I_PIACOD` (`NOMSET`,`NOMSER`,`NOMOPE`,`NOMADD`,`NOMAD1`),
  KEY `I_PIAPSW` (`NOMPSW`),
  KEY `I_PIAPRO` (`NOMPRO`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `PRAAZIONI` (
  `ROWID` int(11) NOT NULL AUTO_INCREMENT,
  `PRANUM` varchar(6) COLLATE latin1_general_cs NOT NULL,
  `ITEKEY` varchar(30) COLLATE latin1_general_cs NOT NULL COMMENT 'CHIAVE PASSO',
  `CODICEAZIONE` varchar(80) COLLATE latin1_general_cs NOT NULL COMMENT 'CODICE AZIONE',
  `CLASSEAZIONE` text COLLATE latin1_general_cs NOT NULL COMMENT 'CLASSE AZIONE PHP',
  `METODOAZIONE` text COLLATE latin1_general_cs NOT NULL COMMENT 'METODO AZIONE PHP',
  `ERROREAZIONE` varchar(4) COLLATE latin1_general_cs NOT NULL,
  `PRATSP` double NOT NULL COMMENT 'Sportello Azione',
  PRIMARY KEY (`ROWID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `PRACLASSIFICAZIONI` (
  `ROW_ID` int(11) NOT NULL AUTO_INCREMENT,
  `CLA_UUID` varchar(36) COLLATE latin1_general_cs NOT NULL,
  `CLA_SEQUENZA` smallint(6) NOT NULL,
  `ARC_UUID` varchar(36) COLLATE latin1_general_cs NOT NULL,
  `PRA_UUID` varchar(36) COLLATE latin1_general_cs NOT NULL,
  `PRA_CLASSE` int(11) NOT NULL,
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
CREATE TABLE `PRACLT` (
  `ROWID` int(11) NOT NULL AUTO_INCREMENT,
  `CLTCOD` varchar(6) COLLATE latin1_general_cs NOT NULL,
  `CLTOFF` double NOT NULL COMMENT 'Flag per spegnere tutti i passi di quel tipo',
  `CLTOBL` double NOT NULL COMMENT 'Flag tipo passo obbligatorio',
  `CLTDES` varchar(255) COLLATE latin1_general_cs NOT NULL COMMENT 'Descrizione Tipo Passo',
  `CLTOPE` varchar(20) COLLATE latin1_general_cs NOT NULL COMMENT 'Tipo Operazione WF',
  `CLTOPEFO` varchar(20) COLLATE latin1_general_cs NOT NULL COMMENT 'Funzione passo FO',
  `CLTMETA` text COLLATE latin1_general_cs,
  `CLTINSEDITOR` text COLLATE latin1_general_cs NOT NULL COMMENT 'AUTORE INSERIMENTO',
  `CLTINSDATE` varchar(8) COLLATE latin1_general_cs NOT NULL COMMENT 'Data Inserimento',
  `CLTINSTIME` varchar(8) COLLATE latin1_general_cs NOT NULL COMMENT 'Orario inserimento o modifica',
  `CLTUPDEDITOR` text COLLATE latin1_general_cs NOT NULL COMMENT 'AUTORE ULTIMA MODIFICA',
  `CLTUPDDATE` varchar(8) COLLATE latin1_general_cs NOT NULL COMMENT 'Data aggiornamento',
  `CLTUPDTIME` varchar(8) COLLATE latin1_general_cs NOT NULL COMMENT 'ORARIO AGGIORNAMENTO',
  `CLTMETAPANEL` text COLLATE latin1_general_cs NOT NULL COMMENT 'Metadati pannelli passo',
  `CLTGESTPANEL` smallint(6) NOT NULL COMMENT 'Attiva gestione pannelli passo',
  `CLTDIZIONARIO` smallint(6) NOT NULL COMMENT 'Crea dizionario Specifico',
  PRIMARY KEY (`ROWID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs COMMENT='ANAGRAFICA TIPI PASSO';
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `PRACOM` (
  `ROWID` int(11) NOT NULL AUTO_INCREMENT,
  `COMNUM` varchar(10) COLLATE latin1_general_cs NOT NULL,
  `COMPAK` varchar(30) COLLATE latin1_general_cs NOT NULL,
  `COMTIP` varchar(20) COLLATE latin1_general_cs NOT NULL,
  `COMMLD` varchar(100) COLLATE latin1_general_cs NOT NULL,
  `COMPRT` varchar(20) COLLATE latin1_general_cs NOT NULL,
  `COMDPR` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `COMDRI` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `COMGRS` smallint(6) NOT NULL,
  `COMFSA` smallint(6) NOT NULL,
  `COMIND` varchar(30) COLLATE latin1_general_cs NOT NULL,
  `COMCAP` double NOT NULL,
  `COMCIT` varchar(20) COLLATE latin1_general_cs NOT NULL,
  `COMPRO` varchar(4) COLLATE latin1_general_cs NOT NULL,
  `COMFIL` varchar(30) COLLATE latin1_general_cs NOT NULL,
  `COMNOM` varchar(60) COLLATE latin1_general_cs NOT NULL,
  `COMDFI` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `COMNOT` text COLLATE latin1_general_cs NOT NULL,
  `COMCDE` varchar(6) COLLATE latin1_general_cs NOT NULL,
  `COMDRE` varchar(8) COLLATE latin1_general_cs NOT NULL COMMENT 'DATA REGISTRAZIONE',
  `COMDAT` varchar(8) COLLATE latin1_general_cs NOT NULL COMMENT 'DATA ARRIVO/PARTENZA',
  `COMORA` varchar(10) COLLATE latin1_general_cs NOT NULL COMMENT 'Ora invio comunicazone',
  `COMTIN` varchar(4) COLLATE latin1_general_cs NOT NULL COMMENT 'TIPO INVIO COMUNICAZIONE',
  `COMRIF` int(11) NOT NULL COMMENT 'RIFERIMENTO ID COMUNICAZIONE',
  `COMINT` smallint(6) NOT NULL COMMENT 'INVIATO ALL''INTESTATARIO',
  `COMMETA` text COLLATE latin1_general_cs,
  `COMFIS` varchar(16) COLLATE latin1_general_cs NOT NULL COMMENT 'Codice fiscale dest comunicazione',
  `COMIDMAIL` text COLLATE latin1_general_cs NOT NULL,
  `COMIDDOC` varchar(20) COLLATE latin1_general_cs NOT NULL COMMENT 'id documeto nel protocollo',
  `COMIDTIP` varchar(50) COLLATE latin1_general_cs NOT NULL,
  `COMDATADOC` varchar(8) COLLATE latin1_general_cs NOT NULL COMMENT 'Data Documento',
  `COMAMMPR` varchar(50) COLLATE latin1_general_cs NOT NULL COMMENT 'Codice Amministrazione Protocollo',
  `COMAOOPR` varchar(50) COLLATE latin1_general_cs NOT NULL COMMENT 'Codice AOO Protocollo',
  PRIMARY KEY (`ROWID`),
  KEY `I_COMPAK` (`COMPAK`),
  KEY `I_COMNUM` (`COMNUM`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `PRAFODECODE` (
  `ROW_ID` int(11) NOT NULL AUTO_INCREMENT,
  `FOTIPO` varchar(20) COLLATE latin1_general_cs NOT NULL COMMENT 'Codice FrontOffice Sorgente',
  `FOSRCKEY` varchar(50) COLLATE latin1_general_cs NOT NULL COMMENT 'Chiave Sorgente',
  `FODESTPRO` varchar(6) COLLATE latin1_general_cs NOT NULL COMMENT 'PROCEDIMENTO',
  `FODESTEVCOD` varchar(6) COLLATE latin1_general_cs NOT NULL COMMENT 'CODICE EVENTO',
  `FODESTTSP` int(11) NOT NULL COMMENT 'CODICE SPORTELLO',
  `FODESTSTT` smallint(6) NOT NULL COMMENT 'SETTORE',
  `FODESTATT` smallint(6) NOT NULL COMMENT 'ATTIVITA',
  `ATTIVO` smallint(6) NOT NULL COMMENT 'Flag Attivo',
  PRIMARY KEY (`ROW_ID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `PRAFOFILES` (
  `ROW_ID` int(11) NOT NULL AUTO_INCREMENT,
  `FOTIPO` varchar(20) COLLATE latin1_general_cs NOT NULL,
  `FOPRAKEY` varchar(250) COLLATE latin1_general_cs NOT NULL,
  `FILESHA2` varchar(64) COLLATE latin1_general_cs NOT NULL,
  `FILEID` varchar(250) COLLATE latin1_general_cs NOT NULL,
  `FILENAME` varchar(250) COLLATE latin1_general_cs NOT NULL,
  `FILEFIL` varchar(250) COLLATE latin1_general_cs NOT NULL,
  `PASDOCROWID` int(11) NOT NULL,
  PRIMARY KEY (`ROW_ID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `PRAFOLIST` (
  `ROW_ID` int(11) NOT NULL AUTO_INCREMENT,
  `FOTIPO` varchar(20) COLLATE latin1_general_cs NOT NULL COMMENT 'Tipo FrontOffice da cui si rileggono i dati',
  `FODATASCARICO` varchar(8) COLLATE latin1_general_cs NOT NULL COMMENT 'Data di lettura pratica (AAAAMMGG)',
  `FOORASCARICO` varchar(8) COLLATE latin1_general_cs NOT NULL COMMENT 'Ora di lettura pratica (HH:MM:SS)',
  `FOPRAKEY` varchar(250) COLLATE latin1_general_cs NOT NULL COMMENT 'Identificativo del messaggio di origine.',
  `FOPRASPACATA` varchar(20) COLLATE latin1_general_cs NOT NULL COMMENT 'Codice Sportello On-Line (codice catastale del Comune). Questo può servire in caso di comuni aggregati (Unione Comune) – Sportello Aggregato Codice Catastale',
  `FOPRADESC` text COLLATE latin1_general_cs NOT NULL COMMENT 'Descrizione',
  `FOPRADATA` varchar(8) COLLATE latin1_general_cs NOT NULL COMMENT 'Data invio pratica (AAAAMMGG)',
  `FOPRAORA` varchar(8) COLLATE latin1_general_cs NOT NULL COMMENT 'Ora invio pratica (HH:MM:SS)',
  `FOPROTDATA` varchar(8) COLLATE latin1_general_cs NOT NULL COMMENT 'Data protocollo stimolo (AAAAMMGG)',
  `FOPROTORA` varchar(8) COLLATE latin1_general_cs NOT NULL COMMENT 'Ora protocollo stimolo (HH:MM:SS)',
  `FOPROTNUM` varchar(20) COLLATE latin1_general_cs NOT NULL COMMENT 'Numero del protocollo',
  `FOESIBENTE` text COLLATE latin1_general_cs NOT NULL COMMENT 'Nominativo di chi ha compilato lo stimolo',
  `FODICHIARANTE` text COLLATE latin1_general_cs NOT NULL COMMENT 'Nominativo del dichiarante dello stimolo',
  `FODICHIARANTECF` varchar(16) COLLATE latin1_general_cs NOT NULL COMMENT 'Codice Fiscale del dichiarante',
  `FODICHIARANTEQUALIFICA` text COLLATE latin1_general_cs NOT NULL COMMENT 'Qualifica del dichiarante',
  `FOALTRORIFERIMENTODESC` text COLLATE latin1_general_cs NOT NULL COMMENT 'Label che ci indica cosa contiene il campo successivo FOALTRORIFERIMENTO',
  `FOALTRORIFERIMENTO` text COLLATE latin1_general_cs NOT NULL COMMENT 'Campo generico per altro riferimento, per il Suap è la Ragione Sociale dell''impresa',
  `FOALTRORIFERIMENTOIND` varchar(250) COLLATE latin1_general_cs NOT NULL COMMENT 'Indirizzo Impresa',
  `FOALTRORIFERIMENTOCAP` varchar(5) COLLATE latin1_general_cs NOT NULL COMMENT 'CAP impresa',
  `FOMETADATA` text COLLATE latin1_general_cs NOT NULL COMMENT 'Meta Data in formato JASON',
  `FOPRATSP` varchar(10) COLLATE latin1_general_cs NOT NULL COMMENT 'Codice Sportello ItalSoft',
  `FOGESNUM` varchar(10) COLLATE latin1_general_cs NOT NULL COMMENT 'NUMNERO FASCICOLO ELETTRONICO ACQUSITO',
  `FOCODICEPRATICASW` text COLLATE latin1_general_cs NOT NULL COMMENT 'Codice Pratica Starweb',
  `FOPROPAK` varchar(30) COLLATE latin1_general_cs NOT NULL COMMENT 'NUMERO DEL PASSO ASSOCIATO',
  `FOUUIDRICHIESTA` varchar(36) COLLATE latin1_general_cs NOT NULL COMMENT 'UUID Univoco Richiesta',
  `FOTIPOSTIMOLO` varchar(250) COLLATE latin1_general_cs NOT NULL COMMENT 'Tipo di stimolo (PresentazionePratica; Comunicazione; ecc..)',
  `FOIDPRATICA` varchar(50) COLLATE latin1_general_cs NOT NULL COMMENT 'Identificativo Pratica',
  PRIMARY KEY (`ROW_ID`),
  UNIQUE KEY `I_PRAFOLIST_KEY` (`FOTIPO`,`FOPRAKEY`),
  KEY `FOUUIDRICHIESTA` (`FOUUIDRICHIESTA`),
  KEY `IDPRATICA` (`FOIDPRATICA`,`FOTIPO`,`FOTIPOSTIMOLO`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `PRAIDC` (
  `ROWID` int(11) NOT NULL AUTO_INCREMENT,
  `IDCKEY` varchar(60) COLLATE latin1_general_cs NOT NULL,
  `IDCSEQ` double NOT NULL,
  `IDCPAS` text COLLATE latin1_general_cs NOT NULL,
  `IDCDES` varchar(60) COLLATE latin1_general_cs NOT NULL,
  `IDCTIP` varchar(20) COLLATE latin1_general_cs NOT NULL,
  `IDCDEF` text COLLATE latin1_general_cs NOT NULL,
  `IDCCTR` varchar(200) COLLATE latin1_general_cs NOT NULL COMMENT 'Tipo Controllo',
  `IDCEXPR` text COLLATE latin1_general_cs NOT NULL COMMENT 'Espressione Regolare',
  `IDCMETA` text COLLATE latin1_general_cs NOT NULL COMMENT 'Metadati',
  `IDCFIN__1` double NOT NULL,
  `IDCFIN__2` double NOT NULL,
  `IDCFIN__3` double NOT NULL,
  `IDCFIA__1` varchar(10) COLLATE latin1_general_cs NOT NULL,
  `IDCFIA__2` varchar(10) COLLATE latin1_general_cs NOT NULL,
  `IDCFIA__3` varchar(10) COLLATE latin1_general_cs NOT NULL,
  PRIMARY KEY (`ROWID`),
  UNIQUE KEY `K_MASTER` (`IDCKEY`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `PRAIMM` (
  `ROWID` int(11) NOT NULL AUTO_INCREMENT,
  `PRONUM` varchar(10) COLLATE latin1_general_cs NOT NULL,
  `TIPO` varchar(1) COLLATE latin1_general_cs NOT NULL,
  `SEZIONE` varchar(3) COLLATE latin1_general_cs NOT NULL,
  `FOGLIO` varchar(4) COLLATE latin1_general_cs NOT NULL,
  `PARTICELLA` varchar(5) COLLATE latin1_general_cs NOT NULL,
  `SUBALTERNO` varchar(4) COLLATE latin1_general_cs NOT NULL,
  `SEQUENZA` int(11) NOT NULL,
  `NOTE` text COLLATE latin1_general_cs NOT NULL,
  `META` text COLLATE latin1_general_cs NOT NULL,
  `CODICE` varchar(20) COLLATE latin1_general_cs NOT NULL COMMENT 'codice immobile',
  `CTRRET` smallint(6) NOT NULL COMMENT 'Esito controllo da ws',
  `CTRMSG` text COLLATE latin1_general_cs NOT NULL COMMENT 'Messaggio Esito controllo da ws',
  PRIMARY KEY (`ROWID`),
  KEY `I_PRONUM` (`PRONUM`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `PRAMAIL` (
  `ROWID` int(11) NOT NULL AUTO_INCREMENT,
  `IDMAIL` text COLLATE latin1_general_cs NOT NULL COMMENT 'CHIAVE UNIVOCA',
  `ROWIDARCHIVIO` int(11) NOT NULL,
  `TIPOMAIL` varchar(20) COLLATE latin1_general_cs NOT NULL,
  `MAILSTATO` varchar(100) COLLATE latin1_general_cs NOT NULL COMMENT 'STATO DELLA MAIL',
  `ISGENERIC` smallint(6) NOT NULL,
  `ISINTEGRATION` smallint(6) NOT NULL,
  `ISFRONTOFFICE` smallint(6) NOT NULL,
  `ISCOMUNICA` smallint(6) NOT NULL,
  `ISRICEVUTA` smallint(6) NOT NULL,
  `TIPORICEVUTA` varchar(20) COLLATE latin1_general_cs NOT NULL,
  `MSGIDRICEVUTA` text COLLATE latin1_general_cs NOT NULL,
  `INFOCOMUNICA` text COLLATE latin1_general_cs NOT NULL,
  `INFOFRONTOFFICE` text COLLATE latin1_general_cs NOT NULL,
  `ANALISIMAIL` text COLLATE latin1_general_cs NOT NULL,
  `RICNUM` varchar(10) COLLATE latin1_general_cs NOT NULL,
  `GESNUM` varchar(10) COLLATE latin1_general_cs NOT NULL,
  `PROPAK` varchar(30) COLLATE latin1_general_cs NOT NULL,
  `COMPAK` varchar(30) COLLATE latin1_general_cs NOT NULL,
  `COMIDMAIL` text COLLATE latin1_general_cs NOT NULL,
  `ISANNULLAMENTO` smallint(6) NOT NULL,
  `ASSRES` varchar(6) COLLATE latin1_general_cs NOT NULL COMMENT 'CODICE NOMINATIVO ASSEGNAZIONE',
  `FLPROT` double NOT NULL COMMENT 'Flag ricevuta protocollata',
  `ISPARERE` smallint(6) NOT NULL COMMENT 'Flag mail parere',
  `SCARTOMOTIVO` text COLLATE latin1_general_cs NOT NULL COMMENT 'MOTIVODELLOSCARTO',
  `CODICEPRATICASW` text COLLATE latin1_general_cs NOT NULL COMMENT 'Codice Pratica Starweb',
  PRIMARY KEY (`ROWID`),
  KEY `I_ROWIDARCHIVIO` (`ROWIDARCHIVIO`),
  KEY `I_IDMAIL` (`IDMAIL`(255)),
  KEY `I_COMIDMAIL` (`COMIDMAIL`(255))
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `PRAMITDEST` (
  `ROWID` int(11) NOT NULL AUTO_INCREMENT,
  `KEYPASSO` varchar(30) COLLATE latin1_general_cs NOT NULL COMMENT 'chiave passo',
  `TIPOCOM` varchar(1) COLLATE latin1_general_cs NOT NULL COMMENT 'Mittenti/Destinatari',
  `CODICE` varchar(6) COLLATE latin1_general_cs NOT NULL COMMENT 'Codice destinatario',
  `NOME` varchar(300) COLLATE latin1_general_cs NOT NULL COMMENT 'destinatario',
  `FISCALE` varchar(16) COLLATE latin1_general_cs NOT NULL COMMENT 'fiscale',
  `INDIRIZZO` varchar(30) COLLATE latin1_general_cs NOT NULL COMMENT 'indirizzo',
  `COMUNE` varchar(40) COLLATE latin1_general_cs NOT NULL COMMENT 'comune',
  `CAP` varchar(5) COLLATE latin1_general_cs NOT NULL COMMENT 'cap',
  `PROVINCIA` varchar(4) COLLATE latin1_general_cs NOT NULL COMMENT 'provincia',
  `MAIL` varchar(100) COLLATE latin1_general_cs NOT NULL COMMENT 'mail',
  `DATAINVIO` varchar(8) COLLATE latin1_general_cs NOT NULL COMMENT 'data invio',
  `ORAINVIO` varchar(8) COLLATE latin1_general_cs NOT NULL COMMENT 'ora invio',
  `DATARISCONTRO` varchar(8) COLLATE latin1_general_cs NOT NULL COMMENT 'data riscontro',
  `SCADENZARISCONTRO` varchar(8) COLLATE latin1_general_cs NOT NULL COMMENT 'scadenza riscontro',
  `TIPOINVIO` varchar(4) COLLATE latin1_general_cs NOT NULL COMMENT 'tipo invio',
  `IDMAIL` text COLLATE latin1_general_cs NOT NULL COMMENT 'id mail partenza',
  `ROWIDPRACOM` int(11) NOT NULL COMMENT 'rowid pracom',
  `SEQUENZA` smallint(6) NOT NULL COMMENT 'Sequenza Mitt/Dest',
  `IDMESSAGGIOCART` varchar(100) COLLATE latin1_general_cs NOT NULL COMMENT 'Identificativo messaggio CART inviato (ITALWEB01.CART_INVIO)',
  `DESTCART` varchar(20) COLLATE latin1_general_cs NOT NULL COMMENT 'Codice Destinatario CART',
  PRIMARY KEY (`ROWID`),
  KEY `I_KEYPASSO` (`KEYPASSO`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `PRASTA` (
  `ROWID` int(11) NOT NULL AUTO_INCREMENT,
  `STANUM` varchar(10) COLLATE latin1_general_cs NOT NULL,
  `STANRC` varchar(10) COLLATE latin1_general_cs NOT NULL,
  `STADES` varchar(70) COLLATE latin1_general_cs NOT NULL,
  `STAPRO` varchar(6) COLLATE latin1_general_cs NOT NULL,
  `STAPAS` varchar(6) COLLATE latin1_general_cs NOT NULL,
  `STADIN` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `STADFI` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `STAPAK` varchar(30) COLLATE latin1_general_cs NOT NULL COMMENT 'CHIAVE PASSO PRATICA ELABORATO',
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
CREATE TABLE `PRATES` (
  `ROWID` int(11) NOT NULL AUTO_INCREMENT,
  `TESCLT` varchar(6) COLLATE latin1_general_cs NOT NULL,
  `TESCOD` varchar(6) COLLATE latin1_general_cs NOT NULL,
  `TESDES` varchar(60) COLLATE latin1_general_cs NOT NULL,
  `TESNOM` varchar(20) COLLATE latin1_general_cs NOT NULL,
  PRIMARY KEY (`ROWID`),
  KEY `I_TESCOD` (`TESCOD`),
  KEY `I_CLTCOD` (`TESCLT`,`TESCOD`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `PROANA` (
  `ROWID` int(11) NOT NULL AUTO_INCREMENT,
  `ANANUM` varchar(10) COLLATE latin1_general_cs NOT NULL,
  `ANAPRO` double NOT NULL,
  `ANADPR` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `ANAPAR` varchar(2) COLLATE latin1_general_cs NOT NULL,
  `ANAOG1` varchar(60) COLLATE latin1_general_cs NOT NULL,
  `ANAOG2` varchar(60) COLLATE latin1_general_cs NOT NULL,
  `ANAOG3` varchar(60) COLLATE latin1_general_cs NOT NULL,
  `ANANOM` varchar(36) COLLATE latin1_general_cs NOT NULL,
  PRIMARY KEY (`ROWID`),
  KEY `I_ANADPR` (`ANADPR`),
  KEY `I_ANANUM` (`ANANUM`,`ANADPR`,`ANAPAR`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `PROANN` (
  `ROWID` int(11) NOT NULL AUTO_INCREMENT,
  `ANNNUM` varchar(6) COLLATE latin1_general_cs NOT NULL,
  `ANNNRI` varchar(4) COLLATE latin1_general_cs NOT NULL,
  `ANNRIG` varchar(70) COLLATE latin1_general_cs NOT NULL,
  PRIMARY KEY (`ROWID`),
  KEY `I_ANNRIG` (`ANNNUM`,`ANNNRI`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `PROCAM` (
  `ROWID` int(11) NOT NULL AUTO_INCREMENT,
  `CAMTES` varchar(24) COLLATE latin1_general_cs NOT NULL,
  `CAMDBA` varchar(20) COLLATE latin1_general_cs NOT NULL,
  `CAMPDF` varchar(30) COLLATE latin1_general_cs NOT NULL,
  `CAMAXM` varchar(50) COLLATE latin1_general_cs NOT NULL,
  `CAMCXM` varchar(50) COLLATE latin1_general_cs NOT NULL,
  PRIMARY KEY (`ROWID`),
  KEY `A_CAMTES` (`CAMTES`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `PROCONCILIAZIONE` (
  `ROWID` int(11) NOT NULL AUTO_INCREMENT,
  `IMPONUM` varchar(10) COLLATE latin1_general_cs NOT NULL,
  `IMPOPROG` smallint(6) NOT NULL,
  `DATAQUIETANZA` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `CONCILIAZIONE` varchar(1) COLLATE latin1_general_cs NOT NULL,
  `QUIETANZA` varchar(20) COLLATE latin1_general_cs NOT NULL,
  `SOMMAPAGATA` double NOT NULL,
  `DIFFERENZA` double NOT NULL,
  `TOTALE` double NOT NULL,
  `NOTE` varchar(100) COLLATE latin1_general_cs NOT NULL,
  `MODIFICATO` varchar(1) COLLATE latin1_general_cs NOT NULL,
  `OPERATORE` varchar(30) COLLATE latin1_general_cs NOT NULL,
  `CONCILIATORE` varchar(20) COLLATE latin1_general_cs NOT NULL,
  `DATARIVERSAMENTO` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `FILECONCILIAZIONE` varchar(100) COLLATE latin1_general_cs NOT NULL,
  `NUMEROQUIETANZA` varchar(20) COLLATE latin1_general_cs NOT NULL,
  `IUV` varchar(60) COLLATE latin1_general_cs NOT NULL,
  `DATAINSERIMENTO` varchar(8) COLLATE latin1_general_cs NOT NULL,
  PRIMARY KEY (`ROWID`),
  KEY `I_IMPONUM` (`IMPONUM`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `PRODAG` (
  `ROWID` int(11) NOT NULL AUTO_INCREMENT,
  `DAGNUM` varchar(10) COLLATE latin1_general_cs NOT NULL,
  `DAGCOD` varchar(6) COLLATE latin1_general_cs NOT NULL,
  `DAGDES` text COLLATE latin1_general_cs NOT NULL COMMENT 'DESCRIZIONE CAMPO',
  `DAGSEQ` double NOT NULL,
  `DAGSFL` double NOT NULL,
  `DAGKEY` varchar(60) COLLATE latin1_general_cs NOT NULL,
  `DAGALIAS` varchar(60) COLLATE latin1_general_cs NOT NULL COMMENT 'NOME CAMPO PDF',
  `DAGVAL` text COLLATE latin1_general_cs NOT NULL,
  `DAGDEF` text COLLATE latin1_general_cs NOT NULL COMMENT 'SALVATAGGIO DEI DEAFULT DEL CAMPO PER ERRATO UTILIZZO DI DAGVAL',
  `DAGSET` varchar(40) COLLATE latin1_general_cs NOT NULL,
  `DAGFIN__1` double NOT NULL,
  `DAGFIN__2` double NOT NULL,
  `DAGFIN__3` double NOT NULL,
  `DAGFIA__1` varchar(100) COLLATE latin1_general_cs NOT NULL,
  `DAGFIA__2` varchar(100) COLLATE latin1_general_cs NOT NULL,
  `DAGFIA__3` varchar(100) COLLATE latin1_general_cs NOT NULL,
  `DAGPAK` varchar(30) COLLATE latin1_general_cs NOT NULL,
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
  `DAGPOS` varchar(50) COLLATE latin1_general_cs NOT NULL COMMENT 'Posizione label',
  `DAGMETA` text COLLATE latin1_general_cs NOT NULL COMMENT 'Metadati',
  `DAGLABSTYLE` text COLLATE latin1_general_cs NOT NULL COMMENT 'Style Label',
  `DAGFIELDSTYLE` text COLLATE latin1_general_cs NOT NULL COMMENT 'Style Campo',
  `DAGEXPROUT` text COLLATE latin1_general_cs NOT NULL COMMENT 'Espressione valutazione aspetto',
  `DAGCLASSE` text COLLATE latin1_general_cs NOT NULL COMMENT 'Classe Disegno',
  `DAGMETODO` text COLLATE latin1_general_cs NOT NULL COMMENT 'Metodo Disegno',
  `DAGFIELDERRORACT` smallint(6) NOT NULL,
  `DAGFIELDCLASS` text COLLATE latin1_general_cs NOT NULL COMMENT 'Attributo class campo',
  PRIMARY KEY (`ROWID`),
  KEY `I_DAGNUM` (`DAGNUM`),
  KEY `I_DAGPAK` (`DAGPAK`),
  KEY `I_DAGNUMTIP` (`DAGNUM`,`DAGTIP`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `PRODIAGGRUPPI` (
  `ROW_ID` int(11) NOT NULL AUTO_INCREMENT,
  `DESCRIZIONE` varchar(100) COLLATE latin1_general_cs NOT NULL COMMENT 'Descrizione del gruppo',
  `GESNUM` varchar(10) COLLATE latin1_general_cs NOT NULL COMMENT 'Chiave fascicolo di PROGES',
  `STATO` int(11) NOT NULL COMMENT 'Stato del gruppo 0-Nascosto; 1-Visibile',
  PRIMARY KEY (`ROW_ID`),
  KEY `PROGES` (`GESNUM`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs COMMENT='Definizioni dei gruppi utilizzati nei Fascicoli Elettronici ';
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `PRODIAGPASSIGRUPPI` (
  `ROW_ID` int(11) NOT NULL AUTO_INCREMENT,
  `PROPAK` varchar(30) COLLATE latin1_general_cs NOT NULL COMMENT 'CHIAVE PASSO DI PROPAS',
  `ROW_ID_PRODIAGGRUPPI` int(11) NOT NULL COMMENT 'ROW_ID DI PRODIAGGRUPPI',
  PRIMARY KEY (`ROW_ID`),
  KEY `PROGES` (`PROPAK`),
  KEY `PRODIAGGRUPPI` (`ROW_ID_PRODIAGGRUPPI`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs COMMENT='Collega i passi presenti in un fascicolo elettronico (PROGES';
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `PRODOC` (
  `ROWID` int(11) NOT NULL AUTO_INCREMENT,
  `PRDKEY` varchar(6) COLLATE latin1_general_cs NOT NULL,
  `PRDFIL` varchar(50) COLLATE latin1_general_cs NOT NULL,
  `PRDORF` varchar(100) COLLATE latin1_general_cs NOT NULL,
  `PRDFDT` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `PRDFMT` varchar(6) COLLATE latin1_general_cs NOT NULL,
  `PRDLNK` varchar(100) COLLATE latin1_general_cs NOT NULL,
  `PRDNOT` text COLLATE latin1_general_cs NOT NULL,
  `PRDCLA` varchar(50) COLLATE latin1_general_cs NOT NULL,
  `PRDUTC` varchar(6) COLLATE latin1_general_cs NOT NULL,
  `PRDUTE` varchar(60) COLLATE latin1_general_cs NOT NULL,
  PRIMARY KEY (`ROWID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `PRODST` (
  `ROWID` int(11) NOT NULL AUTO_INCREMENT,
  `DSTSET` varchar(40) COLLATE latin1_general_cs NOT NULL COMMENT 'CHIAVE SET',
  `DSTTRS` int(11) NOT NULL COMMENT 'TRASMESSO',
  `DSTDES` varchar(100) COLLATE latin1_general_cs NOT NULL COMMENT 'DESCRIZIONE SET',
  PRIMARY KEY (`ROWID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs COMMENT='CLASSIFICAZIONE SET DATI AGGIUNTIVI';
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `PROGES` (
  `ROWID` int(11) NOT NULL AUTO_INCREMENT,
  `GESNUM` varchar(10) COLLATE latin1_general_cs NOT NULL COMMENT 'PROGRESSIVO PRATICA',
  `SERIEANNO` smallint(6) NOT NULL COMMENT 'Anno Serie Specifica',
  `SERIEPROGRESSIVO` int(11) NOT NULL COMMENT 'PROGRESSIVO DELLA SERIE',
  `SERIECODICE` int(11) NOT NULL COMMENT 'Codice della serie del fascicolo',
  `GESPRA` varchar(10) COLLATE latin1_general_cs NOT NULL COMMENT 'N. Richiesta On-line',
  `GESKEY` varchar(30) COLLATE latin1_general_cs NOT NULL COMMENT 'Chiave Fascicolo Archivistico',
  `GESPRO` varchar(6) COLLATE latin1_general_cs NOT NULL COMMENT 'Procedimento Modello',
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
  `GESPRE` varchar(10) COLLATE latin1_general_cs NOT NULL COMMENT 'N antecedente',
  `GESCTR` varchar(50) COLLATE latin1_general_cs NOT NULL COMMENT 'Codice procedura di controllo',
  `GESMETA` text COLLATE latin1_general_cs,
  `GESOGG` text COLLATE latin1_general_cs NOT NULL COMMENT 'OGGETTO',
  `GESCLOSE` varchar(30) COLLATE latin1_general_cs NOT NULL COMMENT 'Propak del passo che chiude la pratica',
  `GESDATAREG` varchar(8) COLLATE latin1_general_cs NOT NULL COMMENT 'Data di controllo se l''acqisizione della pratica è andatat a abuion fine',
  `GESORAREG` varchar(8) COLLATE latin1_general_cs NOT NULL COMMENT 'Ora di controllo se l''acqisizione della pratica è andatat a abuion fine',
  `GESDSC` varchar(8) COLLATE latin1_general_cs NOT NULL COMMENT 'Data Scadenza Pratica',
  `GESTIP` varchar(6) COLLATE latin1_general_cs NOT NULL COMMENT 'TIPOLOGIA PROCEDIMENTO',
  `GESSTT` double NOT NULL COMMENT 'SETTORE PROCEDIMENTO',
  `GESATT` double NOT NULL COMMENT 'ATTIVITA PROCEDIEMNTO',
  `GESEVE` varchar(6) COLLATE latin1_general_cs NOT NULL COMMENT 'Codice evento',
  `GESSEG` varchar(50) COLLATE latin1_general_cs NOT NULL COMMENT 'TIPO SEGNALAZIONE COMUNICA',
  `GESDESCR` varchar(100) COLLATE latin1_general_cs NOT NULL COMMENT 'Descrizione evento',
  `GESCODPROC` varchar(20) COLLATE latin1_general_cs NOT NULL COMMENT 'CODICE PROCEDURA ESTERNA',
  `GESSOR` double NOT NULL COMMENT 'Sportello Originale',
  `GESDAC` varchar(8) COLLATE latin1_general_cs NOT NULL COMMENT 'Data Attivazione Conteggio',
  `GESDEG` varchar(8) COLLATE latin1_general_cs NOT NULL COMMENT 'Data evasione generale',
  `GESGEF` double NOT NULL COMMENT 'Giorni effettivi',
  `GESAMMPR` varchar(50) COLLATE latin1_general_cs NOT NULL COMMENT 'Codice Amministrazione Protocollo',
  `GESAOOPR` varchar(50) COLLATE latin1_general_cs NOT NULL COMMENT 'Codice AOO Protocollo',
  `GESREMOTESTATO` int(11) NOT NULL COMMENT 'Stato operazione acquisizione remota',
  `GESMIGRA` text COLLATE latin1_general_cs NOT NULL,
  `GESDIAG` text COLLATE latin1_general_cs,
  `GESWFPRO` varchar(6) COLLATE latin1_general_cs NOT NULL COMMENT 'Endo-Procedimento workflow di controllo iter',
  `GESEXTKEY` varchar(60) COLLATE latin1_general_cs NOT NULL COMMENT 'Chiave esterna richiesta',
  `GESUUID` varchar(36) COLLATE latin1_general_cs DEFAULT NULL,
  PRIMARY KEY (`ROWID`),
  UNIQUE KEY `GESNUM` (`GESNUM`),
  KEY `I_GESPRO` (`GESPRO`,`GESDRE`),
  KEY `I_GESDRE` (`GESDRE`),
  KEY `I_GESDCH` (`GESDCH`),
  KEY `I_GESSET` (`GESSET`,`GESSER`,`GESOPE`,`GESRES`),
  KEY `I_GESRES` (`GESRES`),
  KEY `I_GESCHI` (`GESCHI`),
  KEY `I_GESNRC` (`GESNRC`),
  KEY `I_GESPRA` (`GESPRA`),
  KEY `I_GESCODPROC` (`GESCODPROC`),
  KEY `I_GESUUID` (`GESUUID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs COMMENT='FASCICOLO ELETTRONICO';
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `PROGESSUB` (
  `ROWID` int(11) NOT NULL AUTO_INCREMENT,
  `PRONUM` varchar(10) COLLATE latin1_general_cs NOT NULL COMMENT 'Numero del fascicolo',
  `RICHIESTA` varchar(10) COLLATE latin1_general_cs NOT NULL COMMENT 'Numero richiesta on-line',
  `RICHKEY` varchar(60) COLLATE latin1_general_cs NOT NULL COMMENT 'Chiave Esterna Richiesta',
  `PROPRO` varchar(6) COLLATE latin1_general_cs NOT NULL COMMENT 'Numero procedimento',
  `EVENTO` varchar(6) COLLATE latin1_general_cs NOT NULL COMMENT 'codice evento',
  `SPORTELLO` varchar(6) COLLATE latin1_general_cs NOT NULL COMMENT 'Sportello on-line',
  `SETTORE` double NOT NULL COMMENT 'codice settore',
  `ATTIVITA` double NOT NULL COMMENT 'codice attività',
  `TIPSEG` varchar(50) COLLATE latin1_general_cs NOT NULL COMMENT 'tipo segnalazione comunica',
  `PROGRESSIVO` double NOT NULL COMMENT 'Numero progressivo',
  PRIMARY KEY (`ROWID`),
  KEY `I_PRONUM` (`PRONUM`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `PROIMPO` (
  `ROWID` int(11) NOT NULL AUTO_INCREMENT,
  `IMPONUM` varchar(10) COLLATE latin1_general_cs NOT NULL COMMENT 'Numero Pratica',
  `IMPOPROG` smallint(6) NOT NULL COMMENT 'Progressivo Importo',
  `IMPOCOD` smallint(6) NOT NULL COMMENT 'Codice Tipo Importo',
  `DATAREG` varchar(8) COLLATE latin1_general_cs NOT NULL COMMENT 'Data registrazione',
  `IMPORTO` double NOT NULL,
  `CODIFICAIUV` varchar(20) COLLATE latin1_general_cs NOT NULL COMMENT 'Tipo di indetificativo versamento',
  `IUV` varchar(60) COLLATE latin1_general_cs NOT NULL COMMENT 'Identificativo versamento',
  `DATASCAD` varchar(8) COLLATE latin1_general_cs NOT NULL COMMENT 'Data Scadenza',
  `PAGATO` double NOT NULL COMMENT 'Importo Pagato',
  `DIFFERENZA` double NOT NULL COMMENT 'Differenza',
  `NOTE` text COLLATE latin1_general_cs NOT NULL COMMENT 'Note',
  PRIMARY KEY (`ROWID`),
  UNIQUE KEY `I_IMPONUM_PROG` (`IMPONUM`,`IMPOPROG`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `PROPAS` (
  `ROWID` int(11) NOT NULL AUTO_INCREMENT,
  `PROCDE` varchar(6) COLLATE latin1_general_cs NOT NULL COMMENT 'Destinatario comunicazione',
  `PRONUM` varchar(10) COLLATE latin1_general_cs NOT NULL,
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
  `PROANN` text COLLATE latin1_general_cs NOT NULL,
  `PRORIS` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `PROTPA` varchar(80) COLLATE latin1_general_cs NOT NULL,
  `PROCLT` varchar(6) COLLATE latin1_general_cs NOT NULL,
  `PROIMP` double NOT NULL,
  `PROSCA` varchar(10) COLLATE latin1_general_cs NOT NULL,
  `PROCTP` varchar(22) COLLATE latin1_general_cs NOT NULL,
  `PRODTP` varchar(60) COLLATE latin1_general_cs NOT NULL,
  `PRODPA` text COLLATE latin1_general_cs NOT NULL,
  `PROITK` varchar(30) COLLATE latin1_general_cs NOT NULL,
  `PROPAK` varchar(30) COLLATE latin1_general_cs NOT NULL,
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
  `PROVPA` varchar(30) COLLATE latin1_general_cs NOT NULL,
  `PROVPN` varchar(22) COLLATE latin1_general_cs NOT NULL COMMENT 'VAI AL PASSO RISP NEGATIVA',
  `PROCTR` varchar(50) COLLATE latin1_general_cs NOT NULL,
  `PROIRE` smallint(6) NOT NULL,
  `PROMLT` smallint(6) NOT NULL,
  `PRODRR` smallint(6) NOT NULL COMMENT 'FLAG RAPPORTO COMPLETO',
  `PROIDR` smallint(6) NOT NULL COMMENT 'FLAF ACCORPA NEL RAPPORTO',
  `PROZIP` double NOT NULL COMMENT 'FLAG INVIO INFOCAMERE COMUNICA',
  `PRODIS` double NOT NULL COMMENT 'FLAG DISTINTA RICHIESTA',
  `PRONOT` text COLLATE latin1_general_cs NOT NULL COMMENT 'Note da esporre',
  `PRONOTSTYLE` text COLLATE latin1_general_cs NOT NULL COMMENT 'Stile css per le note da esporre',
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
  `PROUTEEDIT` varchar(50) COLLATE latin1_general_cs NOT NULL COMMENT 'Utente che modifica il passo',
  `PRODATEEDIT` text COLLATE latin1_general_cs NOT NULL COMMENT 'Data e ora modifica  del passo',
  `PROVISIBILITA` varchar(50) COLLATE latin1_general_cs NOT NULL COMMENT 'Visibilita passo',
  `PRORIN` varchar(10) COLLATE latin1_general_cs NOT NULL COMMENT 'N. richiesta di integrazione',
  `PROCAR` varchar(100) COLLATE latin1_general_cs NOT NULL COMMENT 'categoria Articolo',
  `PRONODE` double NOT NULL,
  `PROPARENT` varchar(30) COLLATE latin1_general_cs NOT NULL,
  `PRODSC` varchar(8) COLLATE latin1_general_cs NOT NULL COMMENT 'Data Scadenza Pratica',
  `PROTARIFFA` double NOT NULL COMMENT 'Tariffa',
  `PRODOCINIVAL` varchar(8) COLLATE latin1_general_cs NOT NULL COMMENT 'DATA INIZIO VALIDITA DOCUMENTO RILASCIATO',
  `PRODOCTIPREG` smallint(6) NOT NULL COMMENT 'Tipolgia registro documento',
  `PRODOCFINVAL` varchar(8) COLLATE latin1_general_cs NOT NULL COMMENT 'DATA FINE VALIDITA DOCUMENTO RILASCIATO',
  `PRODOCPROG` varchar(20) COLLATE latin1_general_cs NOT NULL COMMENT 'PROGRESSIVO DOCUMENTO RILASCIATO',
  `PRODOCANNO` varchar(4) COLLATE latin1_general_cs NOT NULL COMMENT 'ANNO DOCUMENTO RILASCIATO',
  `PROOPE` varchar(20) COLLATE latin1_general_cs NOT NULL COMMENT 'Tipo operazione WF',
  `PROKPRE` varchar(30) COLLATE latin1_general_cs NOT NULL COMMENT 'Chiave passo antecedente',
  `PROPUBALL` double NOT NULL COMMENT 'Flag Pubblica Allegati',
  `PROFLPARERE` double NOT NULL COMMENT 'Flag atiivatore parere sul FO',
  `PROCOMDEST` double NOT NULL COMMENT 'Crea passi comunicazione da destinazione',
  `PROOBL` smallint(6) NOT NULL COMMENT 'Flag Passo Obbligatorio',
  `PROTDC` varchar(100) COLLATE latin1_general_cs NOT NULL COMMENT 'Tipo Data Attivazione Conteggio',
  `PRODCS` varchar(8) COLLATE latin1_general_cs NOT NULL COMMENT 'Data attivazione conteggio stimata',
  `PROGEF` double NOT NULL COMMENT 'giorni effettivamente utilizzati',
  `PRODISCOMUNICA` double NOT NULL COMMENT 'Flag Distinta Infocamere',
  `PRORICUNI` smallint(6) NOT NULL COMMENT 'Accorpa richieste per pratica unica',
  `PROMETA` text COLLATE latin1_general_cs,
  `PRORDM` smallint(6) NOT NULL COMMENT 'Flag Raccolta dati Multipla',
  `PRODWONLINE` smallint(6) NOT NULL COMMENT 'Abilita Download on-line',
  `PROFLCDS` smallint(6) NOT NULL COMMENT 'Flag Attivazione CDS',
  `PASPAR` varchar(2) COLLATE latin1_general_cs NOT NULL COMMENT 'Tipo protocollo trasmissione',
  `PASPRO` double NOT NULL COMMENT 'Numero protocollo trasmissione',
  `ROWID_DOC_CLASSIFICAZIONE` int(11) NOT NULL,
  `PROPDR` tinyint(4) NOT NULL COMMENT 'Passo dipedenza rapporto richiesta',
  `PROPRI` smallint(6) NOT NULL COMMENT 'Flag Documento Principale',
  `PROFLRISERVATO` smallint(6) NOT NULL COMMENT 'Flag tipologia riservatezza',
  `PROEXPRRISERVATO` text COLLATE latin1_general_cs NOT NULL COMMENT 'Espressione di valutazione riservatezza',
  `PROKEY` varchar(60) COLLATE latin1_general_cs NOT NULL COMMENT 'Chiave Esterna Richiesta di Integrazione',
  `TIMEINSER` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `DATAINSER` date DEFAULT NULL,
  `CODUTE` varchar(20) COLLATE latin1_general_cs NOT NULL,
  `DATAOPER` date DEFAULT NULL,
  `FLAG_DIS` smallint(6) NOT NULL,
  `TIMEOPER` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `CODUTEINS` varchar(20) COLLATE latin1_general_cs NOT NULL,
  `PROTIMEPUBART` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `PRODATEPUBART` date DEFAULT NULL,
  PRIMARY KEY (`ROWID`),
  KEY `I_PROPRO` (`PROPRO`),
  KEY `I_PRORES` (`PRORES`),
  KEY `I_PRORPA` (`PRORPA`),
  KEY `I_PROUOP` (`PROUOP`),
  KEY `I_PROPAK` (`PROPAK`),
  KEY `I_PRONUM` (`PRONUM`),
  KEY `I_PRORIN` (`PRORIN`),
  KEY `I_PROOPE` (`PRONUM`,`PROOPE`),
  KEY `I_PASPAR` (`PASPRO`,`PASPAR`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `PROPASFATTI` (
  `ROW_ID` int(11) NOT NULL AUTO_INCREMENT,
  `PRONUM` varchar(10) COLLATE latin1_general_cs NOT NULL,
  `PROPRO` varchar(6) COLLATE latin1_general_cs NOT NULL,
  `PROPAK` varchar(30) COLLATE latin1_general_cs NOT NULL,
  `PROSPA` varchar(30) COLLATE latin1_general_cs NOT NULL,
  PRIMARY KEY (`ROW_ID`),
  KEY `I_PROPASFATTI_PROPAK` (`PROPAK`),
  KEY `I_PROPASFATTI_PROSPA` (`PROSPA`),
  KEY `I_PROPASFATTI_PRONUM` (`PRONUM`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `PRORIC` (
  `ROWID` int(11) NOT NULL AUTO_INCREMENT,
  `RICNUM` varchar(10) COLLATE latin1_general_cs NOT NULL,
  `RICKEY` varchar(60) COLLATE latin1_general_cs NOT NULL COMMENT 'Chiave esterna richiesta',
  `RICPRO` varchar(6) COLLATE latin1_general_cs NOT NULL,
  `RICSET` varchar(6) COLLATE latin1_general_cs NOT NULL,
  `RICSER` varchar(6) COLLATE latin1_general_cs NOT NULL,
  `RICOPE` varchar(6) COLLATE latin1_general_cs NOT NULL,
  `RICRES` varchar(6) COLLATE latin1_general_cs NOT NULL,
  `RICDRE` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `RICORE` varchar(10) COLLATE latin1_general_cs NOT NULL COMMENT 'ora apertura pratica',
  `RICDCH` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `RICNPR` double NOT NULL,
  `RICPAR` varchar(2) COLLATE latin1_general_cs NOT NULL,
  `RICDES` varchar(70) COLLATE latin1_general_cs NOT NULL,
  `RICCHI` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `RICDCO` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `RICDPR` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `RICGIO` double NOT NULL,
  `RICGGG` double NOT NULL,
  `RICFIS` varchar(16) COLLATE latin1_general_cs NOT NULL,
  `RICNAS` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `RICSTA` varchar(2) COLLATE latin1_general_cs NOT NULL,
  `RICSOG` varchar(100) COLLATE latin1_general_cs NOT NULL,
  `RICCOG` varchar(30) COLLATE latin1_general_cs NOT NULL,
  `RICNOM` varchar(30) COLLATE latin1_general_cs NOT NULL,
  `RICANA` double NOT NULL,
  `RICSEQ` text COLLATE latin1_general_cs NOT NULL COMMENT 'Sequenza passi compilati',
  `RICDAT` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `RICTIM` varchar(10) COLLATE latin1_general_cs NOT NULL,
  `RICVIA` varchar(40) COLLATE latin1_general_cs NOT NULL,
  `RICCOM` varchar(40) COLLATE latin1_general_cs NOT NULL,
  `RICEMA` varchar(100) COLLATE latin1_general_cs NOT NULL COMMENT 'EMAIL INTESTATARIO',
  `RICCAP` double NOT NULL,
  `RICPRV` varchar(4) COLLATE latin1_general_cs NOT NULL,
  `RICNAZ` varchar(100) COLLATE latin1_general_cs NOT NULL,
  `RICTSP` double NOT NULL COMMENT 'SPORTELLO ON LINE DI PROVENIENZA',
  `RICSPA` int(11) NOT NULL COMMENT 'SPORTELLO ON LINE AGGREGATO DI PROVENIENZA',
  `RICRPA` varchar(10) COLLATE latin1_general_cs NOT NULL COMMENT 'Pratica padre della richiesta di integrazione',
  `RICTOK` varchar(100) COLLATE latin1_general_cs NOT NULL,
  `RICAGE` varchar(200) COLLATE latin1_general_cs NOT NULL COMMENT 'Codice Agenzia',
  `RICMETA` text COLLATE latin1_general_cs NOT NULL COMMENT 'Metadati',
  `RICEVE` varchar(6) COLLATE latin1_general_cs NOT NULL COMMENT 'Codice Evento',
  `RICSEG` varchar(50) COLLATE latin1_general_cs NOT NULL COMMENT 'Segnalazione Comunica',
  `RICTIP` varchar(6) COLLATE latin1_general_cs NOT NULL COMMENT 'Tipologia',
  `RICSTT` double NOT NULL COMMENT 'Settore',
  `RICATT` double NOT NULL COMMENT 'Attività',
  `RICDESCR` varchar(100) COLLATE latin1_general_cs NOT NULL COMMENT 'Descrizione evento',
  `PROPAK` varchar(30) COLLATE latin1_general_cs NOT NULL COMMENT 'chiave passo articolo BO',
  `RICRUN` varchar(10) COLLATE latin1_general_cs NOT NULL,
  `RICCONFDATA` varchar(8) COLLATE latin1_general_cs NOT NULL COMMENT 'DATA CONFERMA ACQUISIZIONE',
  `RICCONFORA` varchar(10) COLLATE latin1_general_cs NOT NULL COMMENT 'ORACONFERMA ACQUISIZIONE',
  `RICCONFCONTEXT` text COLLATE latin1_general_cs NOT NULL COMMENT 'CONTESTO CONFERMA ACQUISIZIONE',
  `RICCONFINFO` text COLLATE latin1_general_cs NOT NULL COMMENT 'INFO CONTESTO CONFERMA ACQUISIZIONE',
  `RICCONFUTE` varchar(10) COLLATE latin1_general_cs NOT NULL COMMENT 'UTENTE CONFERMA ACQUISIZIONE',
  `CODICEPRATICASW` text COLLATE latin1_general_cs NOT NULL COMMENT 'Codice Pratica Starweb',
  `RICPC` varchar(100) COLLATE latin1_general_cs NOT NULL COMMENT 'Pratica Collegata',
  `RICDATARPROT` varchar(8) COLLATE latin1_general_cs NOT NULL COMMENT 'Data di richiesta protocollazione differita',
  `RICORARPROT` varchar(10) COLLATE latin1_general_cs NOT NULL COMMENT 'Ora di richiesta protocollazione differita',
  `RICERRRPROT` text COLLATE latin1_general_cs NOT NULL COMMENT 'Messaggio d''errore delle procedure differite di protocollazione',
  `RICOGG` text COLLATE latin1_general_cs NOT NULL COMMENT 'Oggetto Richiesta',
  `RICUUID` varchar(36) COLLATE latin1_general_cs NOT NULL COMMENT 'UUID Univoco Richiesta',
  `RICCLAUUID` varchar(36) COLLATE latin1_general_cs DEFAULT NULL,
  `RICFORZAINVIO` tinyint(4) NOT NULL COMMENT 'Forza invio pratica se scaduta',
  PRIMARY KEY (`ROWID`),
  UNIQUE KEY `RICNUM` (`RICNUM`),
  KEY `I_RICPRO` (`RICPRO`,`RICDRE`),
  KEY `I_RICDRE` (`RICDRE`),
  KEY `I_RICDCH` (`RICDCH`),
  KEY `I_RICSET` (`RICSET`,`RICSER`,`RICOPE`,`RICRES`),
  KEY `I_RICRES` (`RICRES`),
  KEY `I_RICCHI` (`RICCHI`),
  KEY `I_RICFIS` (`RICFIS`),
  KEY `I_RICSTA` (`RICSTA`),
  KEY `I_RICDPR` (`RICDPR`),
  KEY `RICUUID` (`RICUUID`),
  KEY `I_RICCLAUUID` (`RICCLAUUID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `PROVPADETT` (
  `ROW_ID` int(11) NOT NULL AUTO_INCREMENT,
  `PRONUM` varchar(10) COLLATE latin1_general_cs NOT NULL,
  `PROPRO` varchar(6) COLLATE latin1_general_cs NOT NULL,
  `PROPAK` varchar(30) COLLATE latin1_general_cs NOT NULL,
  `PROSEQEXPR` double NOT NULL,
  `PROVPA` varchar(30) COLLATE latin1_general_cs NOT NULL,
  `PROEXPRVPA` text COLLATE latin1_general_cs NOT NULL,
  `PROVPADESC` text COLLATE latin1_general_cs NOT NULL,
  `PROEXPRATT` smallint(6) NOT NULL,
  PRIMARY KEY (`ROW_ID`),
  KEY `I_PROVPADETT_PROPAK` (`PROPAK`),
  KEY `I_PROVPADETT_PROVPA` (`PROVPA`),
  KEY `I_PROVPADETT_PRONUM` (`PRONUM`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `RICACL` (
  `ROW_ID` int(11) NOT NULL AUTO_INCREMENT,
  `ROW_ID_RICSOGGETTI` int(11) DEFAULT NULL,
  `ROW_ID_PASSO` int(11) DEFAULT NULL,
  `RICACLMETA` text COLLATE latin1_general_cs,
  `RICACLDATA` varchar(8) COLLATE latin1_general_cs DEFAULT NULL,
  `RICACLORA` varchar(8) COLLATE latin1_general_cs DEFAULT NULL,
  `RICACLDATA_INIZIO` varchar(8) COLLATE latin1_general_cs DEFAULT NULL,
  `RICACLDATA_FINE` varchar(8) COLLATE latin1_general_cs DEFAULT NULL,
  `RICACLNOTE` varchar(8) COLLATE latin1_general_cs DEFAULT NULL,
  `RICACLTRASHED` smallint(6) DEFAULT NULL,
  `RICACLATTIVA` smallint(6) NOT NULL,
  PRIMARY KEY (`ROW_ID`),
  KEY `RICACL_K001` (`ROW_ID_RICSOGGETTI`),
  KEY `RICACL_K002` (`ROW_ID_PASSO`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `RICAZIONI` (
  `ROWID` int(11) NOT NULL AUTO_INCREMENT,
  `RICNUM` varchar(10) COLLATE latin1_general_cs NOT NULL,
  `PRANUM` varchar(6) COLLATE latin1_general_cs NOT NULL,
  `ITEKEY` varchar(30) COLLATE latin1_general_cs NOT NULL COMMENT 'CHIAVE PASSO',
  `CODICEAZIONE` varchar(80) COLLATE latin1_general_cs NOT NULL,
  `CLASSEAZIONE` text COLLATE latin1_general_cs NOT NULL,
  `METODOAZIONE` text COLLATE latin1_general_cs NOT NULL,
  `ERROREAZIONE` varchar(4) COLLATE latin1_general_cs NOT NULL,
  `PRATSP` double NOT NULL COMMENT 'Sportello Azione',
  PRIMARY KEY (`ROWID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `RICCONTROLLI` (
  `ROWID` int(11) NOT NULL AUTO_INCREMENT,
  `RICNUM` varchar(10) COLLATE latin1_general_cs NOT NULL,
  `ITEKEY` varchar(22) COLLATE latin1_general_cs NOT NULL,
  `SEQUENZA` int(11) NOT NULL,
  `DESCRIZIONE` text COLLATE latin1_general_cs NOT NULL,
  `MESSAGGIO` text COLLATE latin1_general_cs NOT NULL,
  `ESPRESSIONE` text COLLATE latin1_general_cs NOT NULL,
  `AZIONE` int(11) NOT NULL,
  PRIMARY KEY (`ROWID`),
  KEY `ITEKEY` (`ITEKEY`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `RICDAG` (
  `ROWID` int(11) NOT NULL AUTO_INCREMENT,
  `DAGNUM` varchar(10) COLLATE latin1_general_cs NOT NULL COMMENT 'numero procedimento',
  `ITECOD` varchar(6) COLLATE latin1_general_cs NOT NULL COMMENT 'Codice Procedimento',
  `ITEKEY` varchar(22) COLLATE latin1_general_cs NOT NULL COMMENT 'Chiave codice procedimento / Passo',
  `DAGDES` text COLLATE latin1_general_cs NOT NULL COMMENT 'Descrizione',
  `DAGSEQ` double NOT NULL COMMENT 'Sequenza',
  `DAGKEY` varchar(60) COLLATE latin1_general_cs NOT NULL COMMENT 'Nome campo',
  `DAGALIAS` varchar(60) COLLATE latin1_general_cs NOT NULL COMMENT 'NOME CAMPO PDF',
  `DAGVAL` text COLLATE latin1_general_cs NOT NULL COMMENT 'Valore campo / Valore select',
  `DAGSET` varchar(40) COLLATE latin1_general_cs NOT NULL COMMENT 'Set di appartenenza campo (Nome del pdf)',
  `DAGTIP` varchar(20) COLLATE latin1_general_cs NOT NULL COMMENT 'Tipo Campo',
  `DAGCTR` text COLLATE latin1_general_cs NOT NULL COMMENT 'Formula di Controllo',
  `DAGNOT` text COLLATE latin1_general_cs NOT NULL COMMENT 'Note',
  `DAGLAB` text COLLATE latin1_general_cs NOT NULL COMMENT 'Label per input',
  `DAGTIC` varchar(50) COLLATE latin1_general_cs NOT NULL COMMENT 'Tipo Input',
  `DAGROL` int(11) NOT NULL COMMENT 'Campo READONLY',
  `DAGVCA` text COLLATE latin1_general_cs NOT NULL COMMENT 'Tipo Validità',
  `DAGREV` text COLLATE latin1_general_cs NOT NULL COMMENT 'Regular ExpressionValidità',
  `DAGLEN` varchar(50) COLLATE latin1_general_cs NOT NULL COMMENT 'Lunghezza campo text',
  `DAGDIZ` varchar(10) COLLATE latin1_general_cs NOT NULL COMMENT 'Flag per valore campo',
  `DAGDIM` varchar(50) COLLATE latin1_general_cs NOT NULL COMMENT 'Dimensione campo',
  `DAGACA` int(11) NOT NULL COMMENT 'Flag per andare a capo checkbox',
  `RICDAT` text COLLATE latin1_general_cs NOT NULL COMMENT 'Dato Aggiuntivo Inserito',
  `DAGPOS` varchar(50) COLLATE latin1_general_cs NOT NULL COMMENT 'Posizione Label',
  `DAGMETA` text COLLATE latin1_general_cs NOT NULL COMMENT 'Metadati',
  `DAGLABSTYLE` text COLLATE latin1_general_cs NOT NULL COMMENT 'Style Label',
  `DAGFIELDSTYLE` text COLLATE latin1_general_cs NOT NULL COMMENT 'Style Campo',
  `DAGEXPROUT` text COLLATE latin1_general_cs NOT NULL,
  `DAGCLASSE` text COLLATE latin1_general_cs NOT NULL COMMENT 'CLASSE DISEGNO',
  `DAGMETODO` text COLLATE latin1_general_cs NOT NULL COMMENT 'METODO DISEGNO',
  `DAGFIELDERRORACT` smallint(6) NOT NULL,
  `DAGFIELDCLASS` text COLLATE latin1_general_cs NOT NULL COMMENT 'Attributo class campo',
  PRIMARY KEY (`ROWID`),
  KEY `I_DAGNUM` (`DAGNUM`),
  KEY `I_ITEKEY` (`ITEKEY`),
  KEY `I_DAGALIAS` (`DAGNUM`,`ITEKEY`,`DAGALIAS`),
  KEY `I_DAGKEY` (`DAGNUM`,`ITEKEY`,`DAGKEY`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `RICDOC` (
  `ROWID` int(11) NOT NULL AUTO_INCREMENT,
  `DOCNUM` varchar(10) COLLATE latin1_general_cs NOT NULL,
  `ITECOD` varchar(6) COLLATE latin1_general_cs NOT NULL,
  `ITEKEY` varchar(60) COLLATE latin1_general_cs NOT NULL,
  `DOCUPL` varchar(200) COLLATE latin1_general_cs NOT NULL,
  `DOCNAME` text COLLATE latin1_general_cs NOT NULL,
  `DOCMETA` text COLLATE latin1_general_cs NOT NULL COMMENT 'Metadati',
  `DOCSEQ` double NOT NULL COMMENT 'Sequenza uplaod',
  `DOCFLSERVIZIO` smallint(6) NOT NULL,
  `DOCSHA2` varchar(64) COLLATE latin1_general_cs NOT NULL COMMENT 'Sha2 File',
  `DOCPRT` smallint(6) NOT NULL COMMENT 'FLAG ALLEGATO PROTOCOLLATO',
  `DOCPRI` smallint(6) NOT NULL,
  `DOCRIS` smallint(6) NOT NULL COMMENT 'Flag riservatezza',
  PRIMARY KEY (`ROWID`),
  KEY `I_DOCORIG` (`DOCNUM`,`DOCNAME`(255))
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `RICEVT` (
  `ROWID` int(11) NOT NULL AUTO_INCREMENT,
  `EVTNUM` varchar(10) COLLATE latin1_general_cs NOT NULL,
  `EVTDAT` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `EVTORA` varchar(6) COLLATE latin1_general_cs NOT NULL,
  `EVTNOT` varchar(100) COLLATE latin1_general_cs NOT NULL,
  `EVTFLA` double NOT NULL,
  PRIMARY KEY (`ROWID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `RICITE` (
  `ROWID` int(11) NOT NULL AUTO_INCREMENT,
  `RICNUM` varchar(10) COLLATE latin1_general_cs NOT NULL,
  `ITECOD` varchar(6) COLLATE latin1_general_cs NOT NULL,
  `ITESET` varchar(6) COLLATE latin1_general_cs NOT NULL,
  `ITESER` varchar(6) COLLATE latin1_general_cs NOT NULL,
  `ITEOPE` varchar(6) COLLATE latin1_general_cs NOT NULL,
  `ITERES` varchar(6) COLLATE latin1_general_cs NOT NULL,
  `ITEDES` text COLLATE latin1_general_cs NOT NULL,
  `ITEGIO` double NOT NULL,
  `ITESEQ` double NOT NULL,
  `ITETER` varchar(80) COLLATE latin1_general_cs NOT NULL,
  `ITECLT` varchar(6) COLLATE latin1_general_cs NOT NULL,
  `ITETES` varchar(6) COLLATE latin1_general_cs NOT NULL,
  `ITEWRD` varchar(100) COLLATE latin1_general_cs NOT NULL,
  `ITEOBL` smallint(6) NOT NULL,
  `ITEUPL` smallint(6) NOT NULL,
  `ITEDOW` smallint(6) NOT NULL,
  `ITECTR` varchar(50) COLLATE latin1_general_cs NOT NULL,
  `ITEIMG` varchar(100) COLLATE latin1_general_cs NOT NULL,
  `ITENOT` text COLLATE latin1_general_cs NOT NULL,
  `ITEPUB` double NOT NULL,
  `ITECOM` double NOT NULL COMMENT 'Flag Comunicazione',
  `ITEPAY` smallint(6) NOT NULL,
  `ITEAML` double NOT NULL,
  `ITEIVA` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `ITEFVA` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `ITESTR` double NOT NULL,
  `ITEPST` smallint(6) NOT NULL,
  `ITEKEY` varchar(22) COLLATE latin1_general_cs NOT NULL,
  `ITEQST` smallint(6) NOT NULL,
  `ITEDAT` smallint(6) NOT NULL COMMENT 'Flag raccolta Dati',
  `ITEVPA` varchar(22) COLLATE latin1_general_cs NOT NULL,
  `ITEVPN` varchar(22) COLLATE latin1_general_cs NOT NULL COMMENT 'VAI AL PASSO RISP NEGATIVA',
  `ITEIRE` smallint(6) NOT NULL,
  `ITEMLT` smallint(6) NOT NULL,
  `ITEINF` varchar(100) COLLATE latin1_general_cs NOT NULL,
  `ITESTA` smallint(6) NOT NULL,
  `ITETIM` smallint(6) NOT NULL,
  `ITECDE` varchar(6) COLLATE latin1_general_cs NOT NULL,
  `ITERUO` varchar(4) COLLATE latin1_general_cs NOT NULL,
  `ITECTP` varchar(22) COLLATE latin1_general_cs NOT NULL COMMENT 'RIFERIMENTO PASSO CONTROLLI CAMPI AGGIUNTIVI',
  `ITEINT` double NOT NULL COMMENT 'Flag invia mail intestatario',
  `ITEIDR` smallint(6) NOT NULL COMMENT 'FLAG INCLUSIONE NEL DETTAGIO RAPPORTO RICHIESTA',
  `RCIRIS` varchar(4) COLLATE latin1_general_cs NOT NULL,
  `RICERF` smallint(6) NOT NULL,
  `RICERM` text COLLATE latin1_general_cs NOT NULL,
  `ITEEXT` text COLLATE latin1_general_cs NOT NULL COMMENT 'estensioni supportate',
  `ITEDRR` smallint(6) NOT NULL COMMENT 'FLAG PASSO SCARICO RAPPORTO COMPLETO PDF RICHIESTA',
  `ITESTAP` int(11) NOT NULL COMMENT 'stato apertura passo',
  `ITESTCH` int(11) NOT NULL COMMENT 'stato chiusura passo',
  `ITEATE` text COLLATE latin1_general_cs NOT NULL COMMENT 'Espressione serializzata per attivazione passo',
  `ITEDIS` double NOT NULL COMMENT 'Flag Passo Distinta',
  `ITEZIP` double NOT NULL COMMENT 'Flag Passo Download ZIP',
  `ITEIFC` double NOT NULL COMMENT 'Flag per file comunica',
  `RICNOT` text COLLATE latin1_general_cs NOT NULL COMMENT 'Altre note passo',
  `ITEMRI` double NOT NULL COMMENT 'blocca mail richiedente',
  `ITEMRE` double NOT NULL COMMENT 'blocca mail responsabile',
  `ITEURL` text COLLATE latin1_general_cs NOT NULL COMMENT 'url associato',
  `ITETBA` text COLLATE latin1_general_cs NOT NULL COMMENT 'id testo base',
  `ITETAL` varchar(80) COLLATE latin1_general_cs NOT NULL COMMENT 'tipologia allegato',
  `ITEFILE` double NOT NULL COMMENT 'Passo inserimento automatico file',
  `ITERIF` smallint(6) NOT NULL COMMENT 'Flag Passo Riferiemnto ad un procedimento',
  `ITEPROC` varchar(6) COLLATE latin1_general_cs NOT NULL COMMENT 'Codice Procedimento di riferimento',
  `ITEDAP` varchar(22) COLLATE latin1_general_cs NOT NULL COMMENT 'Dal Passo',
  `ITEALP` varchar(22) COLLATE latin1_general_cs NOT NULL COMMENT 'Al Passo',
  `ITEPRIV` double NOT NULL COMMENT 'Flag passo italsoft',
  `ITECOL` double NOT NULL COMMENT 'Numero colonne',
  `ITECTB` double NOT NULL COMMENT 'produci modello testo base',
  `ITEMETA` text COLLATE latin1_general_cs,
  `ITERDM` double NOT NULL COMMENT 'Raccolta dati Multipla',
  `ITENRA` varchar(100) COLLATE latin1_general_cs NOT NULL COMMENT 'Numero raccolte multiple',
  `ITEQALLE` tinyint(4) NOT NULL COMMENT 'QUALIFICA ALLEGATO',
  `ITEQCLA` tinyint(4) NOT NULL COMMENT 'CHIEDI CLASSIFICAZIONE ALLEGATO',
  `ITEQDEST` tinyint(4) NOT NULL COMMENT 'CHIEDI DESTINAZIONE ALLEGATO',
  `ITEQNOTE` tinyint(4) NOT NULL COMMENT 'CHIEDI NOTE ALLEGATO',
  `ITEQSTDAG` smallint(6) NOT NULL COMMENT 'PASSO QUESTIONARIO DA DATI AGGIUNTIVI',
  `RICQSTRIS` smallint(6) NOT NULL COMMENT 'STATO RISPOSTA A QUESTIONARIO SU DATI AGGIUNTIVI',
  `ITEOBE` text COLLATE latin1_general_cs NOT NULL COMMENT 'Espressione Obbligatorio se',
  `RICOBL` smallint(6) NOT NULL COMMENT 'Obbligatorio dopo espressione',
  `ITECOMPSEQ` double NOT NULL,
  `ITECOMPFLAG` varchar(10) COLLATE latin1_general_cs NOT NULL,
  `ITEAGE` double NOT NULL COMMENT 'PASSO INOLTRO AGENZIA',
  `ITEDWP` varchar(22) COLLATE latin1_general_cs NOT NULL COMMENT 'Downlaod Upload Precedente',
  `ITENOTSTYLE` text COLLATE latin1_general_cs NOT NULL COMMENT 'Note Style',
  `ITEHTML` text COLLATE latin1_general_cs NOT NULL,
  `ITEHELP` text COLLATE latin1_general_cs NOT NULL COMMENT 'Codice Help',
  `TEMPLATEKEY` varchar(22) COLLATE latin1_general_cs NOT NULL COMMENT 'CHIAVE PASSO TEMPLATE',
  `ITEPRR` double NOT NULL COMMENT 'Passo Protocollo Remoto',
  `TARIFFA` double NOT NULL COMMENT 'Tariffa',
  `ITECUSTOMTML` varchar(200) COLLATE latin1_general_cs NOT NULL COMMENT 'Nome template personalizzato',
  `ITESOSTF` smallint(6) NOT NULL COMMENT 'Passo scelta files da sostituire',
  `ITERICUNI` smallint(6) NOT NULL COMMENT 'Accorpa richieste per pratica unica',
  `ITERICSUB` smallint(6) NOT NULL COMMENT 'Passo di gestione richiesta dove accorpare',
  `RICSHA2SOST` varchar(64) COLLATE latin1_general_cs NOT NULL COMMENT 'SHA2 DEL FILE SOSTITUITO (FLAG)',
  `ITEDEFSTATO` int(11) NOT NULL COMMENT 'STATO DI DEFAULT DEL PASSO',
  `ITEKPRE` varchar(30) COLLATE latin1_general_cs NOT NULL COMMENT 'CHIAVE PASSO ANTECEDENTE',
  `ITECARICAAUTO` smallint(6) NOT NULL COMMENT 'CARICA AUTOM SU FASCICOLO',
  `ITEAPRIAUTO` smallint(6) NOT NULL COMMENT 'APRI PASSO AL CARICA SU FASCICOLO',
  `ITEASSAUTO` varchar(20) COLLATE latin1_general_cs NOT NULL COMMENT 'ASSEGNA AUTOMATICAMENTE A TIPO SOGGETTO',
  `ITEMIGRA` varchar(20) COLLATE latin1_general_cs NOT NULL,
  `ITECOMDEST` double NOT NULL COMMENT 'Crea passi comunicazione da destinazione',
  `ITETDC` varchar(100) COLLATE latin1_general_cs NOT NULL COMMENT 'Tipo Data Attivazione Conteggio',
  `ITEDCS` varchar(8) COLLATE latin1_general_cs NOT NULL COMMENT 'Data attivazione conteggio stimata',
  `ITEGEF` double NOT NULL COMMENT 'giorni effettivamente utilizzati',
  `ITEDISCOMUNICA` double NOT NULL COMMENT 'Flag Distinta Infocamere',
  `ROWID_DOC_CLASSIFICAZIONE` int(11) NOT NULL,
  `ITEPDR` tinyint(4) NOT NULL COMMENT 'Passo dipedenza rapporto richiesta',
  `ITEPRI` smallint(6) NOT NULL COMMENT 'Flag Documento Principale',
  `ITEFLRISERVATO` smallint(6) NOT NULL COMMENT 'Flag tipologia riservatezza',
  `ITEEXPRRISERVATO` text COLLATE latin1_general_cs NOT NULL COMMENT 'Espressione di valutazione riservatezza',
  PRIMARY KEY (`ROWID`),
  KEY `I_RCISEQ` (`RICNUM`,`ITESEQ`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `RICMAIL` (
  `ROWID` int(11) NOT NULL AUTO_INCREMENT,
  `RICNUM` varchar(10) COLLATE latin1_general_cs NOT NULL,
  `ITEKEY` varchar(22) COLLATE latin1_general_cs NOT NULL,
  `MAILSTATO` text COLLATE latin1_general_cs NOT NULL COMMENT 'Stato della mail',
  `METADATI` text COLLATE latin1_general_cs NOT NULL,
  `MSGDATE` varchar(20) COLLATE latin1_general_cs NOT NULL COMMENT 'Ymd His',
  `SENDREC` varchar(2) COLLATE latin1_general_cs NOT NULL COMMENT 'Tipo Mail',
  `TOADDR` text COLLATE latin1_general_cs NOT NULL,
  `TOCLASS` text COLLATE latin1_general_cs NOT NULL,
  `IDMAIL` text COLLATE latin1_general_cs NOT NULL,
  `NOMRES` varchar(6) COLLATE latin1_general_cs NOT NULL COMMENT 'Codice responsabile',
  PRIMARY KEY (`ROWID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `RICOPERAZ` (
  `ROW_ID` int(11) NOT NULL AUTO_INCREMENT,
  `OPEFIS` varchar(16) COLLATE latin1_general_cs DEFAULT NULL,
  `ROWID_PRORIC` int(11) DEFAULT NULL,
  `ROWID_PASSO` int(11) DEFAULT NULL,
  `RICOPEOPE` varchar(2) COLLATE latin1_general_cs DEFAULT NULL,
  `RICOPEKEY` varchar(100) COLLATE latin1_general_cs DEFAULT NULL,
  `RICOPEEST` text COLLATE latin1_general_cs,
  `RICOPEMETA` text COLLATE latin1_general_cs,
  `RICOPEIP` varchar(30) COLLATE latin1_general_cs DEFAULT NULL,
  `RICOPEDAT` varchar(8) COLLATE latin1_general_cs DEFAULT NULL,
  `RICOPETIM` varchar(8) COLLATE latin1_general_cs DEFAULT NULL,
  `RICOPESPIDCODE` varchar(20) COLLATE latin1_general_cs DEFAULT NULL,
  PRIMARY KEY (`ROW_ID`),
  KEY `RICOPERAZ_K001` (`OPEFIS`,`RICOPEDAT`,`RICOPETIM`),
  KEY `RICOPERAZ_K002` (`RICOPEDAT`,`RICOPETIM`,`ROWID_PRORIC`),
  KEY `RICOPERAZ_K003` (`ROWID_PRORIC`,`RICOPEDAT`,`RICOPETIM`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `RICPRAOBB` (
  `ROWID` int(11) NOT NULL AUTO_INCREMENT,
  `OBBNUM` varchar(10) COLLATE latin1_general_cs NOT NULL COMMENT 'NUMERO PRATICA',
  `OBBPRA` varchar(6) COLLATE latin1_general_cs NOT NULL COMMENT 'PRATICA PADRE',
  `OBBEVCOD` varchar(6) COLLATE latin1_general_cs NOT NULL COMMENT 'EVENTO PADRE',
  `OBBSUBPRA` varchar(6) COLLATE latin1_general_cs NOT NULL COMMENT 'PRATICA OBBLIGATORIA',
  `OBBSUBEVCOD` varchar(6) COLLATE latin1_general_cs NOT NULL COMMENT 'EVENTO PRATICA OBBLIGATORIA',
  `OBBEXPRCTR` text COLLATE latin1_general_cs NOT NULL COMMENT 'ESPRESSIONE DI OBBLIGATORIETA''',
  PRIMARY KEY (`ROWID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs COMMENT='PRATICHE OBBLIGATORIE ';
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `RICSOGGETTI` (
  `ROW_ID` int(11) NOT NULL AUTO_INCREMENT,
  `SOGRICNUM` varchar(10) COLLATE latin1_general_cs DEFAULT NULL,
  `SOGRICUUID` varchar(36) COLLATE latin1_general_cs DEFAULT NULL,
  `SOGRICFIS` varchar(16) COLLATE latin1_general_cs DEFAULT NULL,
  `SOGRICDENOMINAZIONE` varchar(300) COLLATE latin1_general_cs DEFAULT NULL,
  `SOGRICRUOLO` varchar(4) COLLATE latin1_general_cs DEFAULT NULL,
  `SOGRICRICDATA_INIZIO` varchar(8) COLLATE latin1_general_cs DEFAULT NULL,
  `SOGRICDATA_FINE` varchar(8) COLLATE latin1_general_cs DEFAULT NULL,
  `SOGRICNOTE` text COLLATE latin1_general_cs,
  PRIMARY KEY (`ROW_ID`),
  KEY `RICSOGGETTI_K002` (`SOGRICNUM`),
  KEY `RICACL_K001` (`SOGRICFIS`,`SOGRICRUOLO`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `RICSTA` (
  `ROWID` int(11) NOT NULL AUTO_INCREMENT,
  `RICNUM` varchar(10) COLLATE latin1_general_cs NOT NULL,
  `PROPAK` varchar(22) COLLATE latin1_general_cs NOT NULL COMMENT 'Chiave Passo',
  `READED` smallint(6) NOT NULL,
  `READDATA` varchar(10) COLLATE latin1_general_cs NOT NULL COMMENT 'Data Lettura',
  `READORA` varchar(8) COLLATE latin1_general_cs NOT NULL COMMENT 'Ora Lettura',
  PRIMARY KEY (`ROWID`),
  KEY `STANUM` (`RICNUM`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `SYNCLOG` (
  `ROWID` int(11) NOT NULL AUTO_INCREMENT,
  `ENTESYNC` text COLLATE latin1_general_cs NOT NULL COMMENT 'Ente sincronizzato',
  `TABELLASYNC` text COLLATE latin1_general_cs NOT NULL,
  `DATASYNC` varchar(16) COLLATE latin1_general_cs NOT NULL COMMENT 'Data sincronizzazione',
  `EDITORMASTER` text COLLATE latin1_general_cs NOT NULL COMMENT 'Autore dati sincronizzati',
  `DATAMASTER` varchar(16) COLLATE latin1_general_cs NOT NULL COMMENT 'Versione dati sincronizzati',
  `ENTEMASTER` text COLLATE latin1_general_cs NOT NULL COMMENT 'Ente master',
  `DESCRIZIONEMASTER` text COLLATE latin1_general_cs NOT NULL COMMENT 'Descrizione master',
  `LOGSYNC` text COLLATE latin1_general_cs NOT NULL COMMENT 'Log testuale sincronizzazione',
  PRIMARY KEY (`ROWID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `VARIABILIAMBIENTE` (
  `ROWID` int(11) NOT NULL AUTO_INCREMENT,
  `VARKEY` varchar(60) COLLATE latin1_general_cs NOT NULL,
  `VARVAL` text COLLATE latin1_general_cs NOT NULL,
  PRIMARY KEY (`ROWID`),
  UNIQUE KEY `VARKEY` (`VARKEY`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs COMMENT='VARIABILI D''AMBIENTE';
/*!40101 SET character_set_client = @saved_cs_client */;
