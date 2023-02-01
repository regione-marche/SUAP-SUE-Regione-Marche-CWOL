/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `a0_anagraficaprogetti` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `cup` varchar(15) COLLATE latin1_general_cs NOT NULL,
  `titoloprogetto` text COLLATE latin1_general_cs NOT NULL,
  `noteprogetto` text COLLATE latin1_general_cs NOT NULL,
  `stato` int(11) NOT NULL DEFAULT '0' COMMENT 'se 0 indica che deve essere condiderato per BDAP',
  `utente_umod` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `data_umod` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `azione` varchar(10) COLLATE latin1_general_cs NOT NULL,
  `revisione` varchar(255) COLLATE latin1_general_cs DEFAULT NULL,
  `rendicontabdap` varchar(1) COLLATE latin1_general_cs NOT NULL COMMENT 'assume valore S= SI o N=NO',
  `gestione` varchar(1) COLLATE latin1_general_cs NOT NULL COMMENT 'Assume il valore G= gestito S=storico',
  `id_progetto` varchar(68) COLLATE latin1_general_cs NOT NULL COMMENT 'codice del progetto',
  `id_a0` int(11) NOT NULL,
  `id_a21` int(11) NOT NULL,
  `id_pt_scheda3` int(11) NOT NULL,
  `utente_creatore` varchar(255) COLLATE latin1_general_cs NOT NULL COMMENT 'utente che crea l''opera',
  `gruppo_gestione` int(11) NOT NULL COMMENT 'gruppo che ha visibilità e che gestisce l''opera',
  `tipotracciato` int(11) NOT NULL DEFAULT '0' COMMENT '0= opera con tracciato stadndard 1=opera con tracciato ridotto',
  `pubblicaweb` varchar(1) COLLATE latin1_general_cs NOT NULL,
  `descrizioneweb` text COLLATE latin1_general_cs NOT NULL,
  `archiviata` int(11) NOT NULL DEFAULT '0' COMMENT '0 = opera attiva 1= opera archiviata',
  `permessoaccesso` int(11) NOT NULL DEFAULT '1',
  `proprietario` varchar(255) COLLATE latin1_general_cs NOT NULL DEFAULT '',
  `f_ediliziascolastica` varchar(10) COLLATE latin1_general_cs NOT NULL DEFAULT 'N',
  `a31compilato` int(11) NOT NULL DEFAULT '0' COMMENT 'scheda A31 0 non compilata; 1 compilata',
  `a31codedificio` varchar(255) COLLATE latin1_general_cs NOT NULL DEFAULT '' COMMENT 'scheda A31 codice edificio',
  `a31rowid_c16` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `a11_quadroeconomico` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_progetto` varchar(68) COLLATE latin1_general_cs NOT NULL,
  `id_a11` int(11) NOT NULL,
  `tipologiavocespesa` varchar(1) COLLATE latin1_general_cs NOT NULL COMMENT 'P: prevista E: effettiva',
  `vocespesa` int(11) NOT NULL COMMENT 'tabella decodifica C14',
  `importo` double NOT NULL,
  `dbap` varchar(2) COLLATE latin1_general_cs NOT NULL COMMENT 'S=SI considera questo quadro economico x BDAP N=NO',
  `id_a21_iterprocedurale` int(11) NOT NULL COMMENT 'id tipo del progetto (preliminare, defiitivo, esecutivo)',
  `tiposomma` varchar(1) COLLATE latin1_general_cs NOT NULL COMMENT 'B= base d''asta - S=somme a disposizione, con questo tipo si indica se l''importo è da ritenersi nel quadro della base   d''asta o nel quadro delle somme a disposizione',
  `utente_umod` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `data_umod` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `azione` varchar(10) COLLATE latin1_general_cs NOT NULL,
  `revisione` varchar(255) COLLATE latin1_general_cs DEFAULT NULL,
  `gestione` varchar(1) COLLATE latin1_general_cs NOT NULL,
  PRIMARY KEY (`id`),
  KEY `azione` (`azione`),
  KEY `id_a11` (`id_a11`),
  KEY `id_a21_iterprocedurale` (`id_a21_iterprocedurale`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `a12_economie` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_progetto` varchar(68) COLLATE latin1_general_cs NOT NULL,
  `id_a12` int(11) NOT NULL,
  `annoeconomie` varchar(4) COLLATE latin1_general_cs NOT NULL,
  `fontefinanziamento` varchar(3) COLLATE latin1_general_cs NOT NULL,
  `importo` double NOT NULL,
  `utente_umod` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `data_umod` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `azione` varchar(10) COLLATE latin1_general_cs NOT NULL,
  `revisione` varchar(255) COLLATE latin1_general_cs DEFAULT NULL,
  `gestione` varchar(1) COLLATE latin1_general_cs NOT NULL,
  PRIMARY KEY (`id`),
  KEY `id_a12` (`id_a12`),
  KEY `azione` (`azione`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `a13_ribassi` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_progetto` varchar(68) COLLATE latin1_general_cs NOT NULL,
  `id_a13` int(11) NOT NULL,
  `cig` varchar(10) COLLATE latin1_general_cs NOT NULL,
  `percentualeribasso` double NOT NULL,
  `importoribasso` double NOT NULL,
  `importoa` double NOT NULL COMMENT 'importo lavori soggetti a ribasso',
  `importob` double NOT NULL COMMENT 'importo sicurezza non soggetta a ribasso',
  `importoc` double NOT NULL COMMENT 'importo sicurezza non soggetta a ribasso',
  `baseasta` double NOT NULL,
  `importoe` double NOT NULL COMMENT 'importo sicurezza non soggetta a ribasso',
  `importof` double NOT NULL COMMENT 'importo sicurezza non soggetta a ribasso',
  `importog` double NOT NULL COMMENT 'importo sicurezza non soggetta a ribasso',
  `importocontratto` double NOT NULL,
  `utente_umod` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `data_umod` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `azione` varchar(10) COLLATE latin1_general_cs NOT NULL,
  `revisione` varchar(255) COLLATE latin1_general_cs DEFAULT NULL,
  `gestione` varchar(1) COLLATE latin1_general_cs NOT NULL,
  `id_a28` int(11) NOT NULL,
  `importolavori` double NOT NULL,
  PRIMARY KEY (`id`),
  KEY `id_a13` (`id_a13`),
  KEY `azione` (`azione`),
  KEY `id_a28` (`id_a28`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `a14_impegni` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_progetto` varchar(68) COLLATE latin1_general_cs NOT NULL,
  `id_a14` int(11) NOT NULL,
  `dataimpegno` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `codiceimpegno` varchar(20) COLLATE latin1_general_cs NOT NULL,
  `tipologiaimpegno` varchar(1) COLLATE latin1_general_cs NOT NULL COMMENT 'I: impegno D: revoca',
  `importo` double NOT NULL,
  `note` text COLLATE latin1_general_cs NOT NULL,
  `id_a28` int(11) NOT NULL,
  `cig` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `nr_registrazione` int(11) NOT NULL,
  `data_registrazione` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `serie` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `volume` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `luogo` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `tempoutileesecuzione` int(11) NOT NULL,
  `utente_umod` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `data_umod` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `azione` varchar(10) COLLATE latin1_general_cs NOT NULL,
  `revisione` varchar(255) COLLATE latin1_general_cs DEFAULT NULL,
  `gestione` varchar(1) COLLATE latin1_general_cs NOT NULL,
  `id_allegato` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `id_a14` (`id_a14`),
  KEY `id_progetto` (`id_progetto`),
  KEY `id_a28` (`id_a28`),
  KEY `azione` (`azione`),
  KEY `gestione` (`gestione`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `a15_pagamenti` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_progetto` varchar(68) COLLATE latin1_general_cs NOT NULL,
  `id_a15` int(11) NOT NULL,
  `datapagamento` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `codicepagamento` varchar(20) COLLATE latin1_general_cs NOT NULL,
  `tipopagamento` varchar(1) COLLATE latin1_general_cs NOT NULL COMMENT 'assume P per pagamento e R per recupero',
  `importo` double NOT NULL,
  `causale` varchar(1) COLLATE latin1_general_cs NOT NULL COMMENT 'assume A: anticipo, B anticipi corrispettivi S: saldo P: pagamento',
  `note` text COLLATE latin1_general_cs NOT NULL,
  `codicegestionale` varchar(56) COLLATE latin1_general_cs NOT NULL COMMENT 'tabella decodifica C15',
  `utente_umod` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `data_umod` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `azione` varchar(10) COLLATE latin1_general_cs NOT NULL,
  `revisione` varchar(255) COLLATE latin1_general_cs DEFAULT NULL,
  `gestione` varchar(1) COLLATE latin1_general_cs NOT NULL,
  `progressivosal` int(11) NOT NULL,
  `id_cig` varchar(255) COLLATE latin1_general_cs NOT NULL,
  PRIMARY KEY (`id`),
  KEY `id_progetto` (`id_progetto`),
  KEY `id_a15` (`id_a15`),
  KEY `azione` (`azione`),
  KEY `revisione` (`revisione`),
  KEY `gestione` (`gestione`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `a16_pianodeiconti` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_progetto` varchar(68) COLLATE latin1_general_cs NOT NULL,
  `id_a16` int(11) NOT NULL,
  `anno` varchar(4) COLLATE latin1_general_cs NOT NULL,
  `importorealizzato` double NOT NULL,
  `importodarealizzare` double NOT NULL,
  `utente_umod` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `data_umod` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `azione` varchar(10) COLLATE latin1_general_cs NOT NULL,
  `revisione` varchar(255) COLLATE latin1_general_cs DEFAULT NULL,
  `gestione` varchar(1) COLLATE latin1_general_cs NOT NULL,
  PRIMARY KEY (`id`),
  KEY `id_progetto` (`id_progetto`),
  KEY `id_a16` (`id_a16`),
  KEY `azione` (`azione`),
  KEY `revisione` (`revisione`),
  KEY `gestione` (`gestione`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `a17_indicatori` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_progetto` varchar(68) COLLATE latin1_general_cs NOT NULL,
  `id_a17` int(11) NOT NULL,
  `codicetipoindicatore` varchar(2) COLLATE latin1_general_cs NOT NULL,
  `indicatore` varchar(6) COLLATE latin1_general_cs NOT NULL,
  `valoreprogrammatoiniziale` double NOT NULL,
  `valoreprogrammatoaggiornato` double NOT NULL,
  `valoreimpegnato` double NOT NULL,
  `valoreconclusione` double NOT NULL,
  `baseline` double NOT NULL,
  `utente_umod` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `data_umod` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `azione` varchar(10) COLLATE latin1_general_cs NOT NULL,
  `revisione` varchar(255) COLLATE latin1_general_cs DEFAULT NULL,
  `gestione` varchar(1) COLLATE latin1_general_cs NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `a1_anagrafica_infogen` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_progetto` varchar(68) COLLATE latin1_general_cs NOT NULL,
  `id_a1` int(11) NOT NULL,
  `settoreprevalentecpt` varchar(2) COLLATE latin1_general_cs NOT NULL COMMENT 'tabella decodifica C1',
  `tipologiafinanziamento` varchar(2) COLLATE latin1_general_cs NOT NULL COMMENT 'tabella decodifica C12',
  `generatoreentrate` varchar(1) COLLATE latin1_general_cs NOT NULL COMMENT 's: si    n: no',
  `codiceintesa` int(11) NOT NULL COMMENT 'tabella decodifica C2',
  `leggeobiettivo` varchar(1) COLLATE latin1_general_cs NOT NULL COMMENT 'S:SI       N: NO',
  `codicestrumentoattuativo` varchar(4) COLLATE latin1_general_cs NOT NULL COMMENT 'tabella decodifica C3',
  `utente_umod` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `data_umod` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `azione` varchar(10) COLLATE latin1_general_cs NOT NULL,
  `revisione` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `gestione` varchar(1) COLLATE latin1_general_cs NOT NULL COMMENT 'G= gestione S= storico',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `a21_iterprocedurale` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_progetto` varchar(68) COLLATE latin1_general_cs NOT NULL,
  `id_a21` int(11) NOT NULL,
  `fase` varchar(3) COLLATE latin1_general_cs NOT NULL COMMENT 'tabella decodifica C6',
  `datainizioprevista` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `datainizioeffettiva` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `datafineprevista` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `datafineeffettiva` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `soggettocompetente` varchar(100) COLLATE latin1_general_cs NOT NULL,
  `motivoscostamento` varchar(2) COLLATE latin1_general_cs NOT NULL COMMENT 'A1: ''PROBLEMI AMMINISTRATIVI''   A2: PROBLEMATICHE TECNICHE''',
  `descrizione` text COLLATE latin1_general_cs NOT NULL COMMENT 'campo non presente nel tracciato BDAP ma utile',
  `note` text COLLATE latin1_general_cs NOT NULL,
  `progettoingara` varchar(1) COLLATE latin1_general_cs NOT NULL,
  `id_a21_variante` int(11) NOT NULL,
  `utente_umod` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `data_umod` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `azione` varchar(10) COLLATE latin1_general_cs NOT NULL,
  `revisione` varchar(255) COLLATE latin1_general_cs DEFAULT NULL,
  `gestione` varchar(1) COLLATE latin1_general_cs NOT NULL,
  `attoapprovazione` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `numeroatto` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `dataatto` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `progettocorrente` varchar(1) COLLATE latin1_general_cs NOT NULL,
  `txbdap` varchar(10) COLLATE latin1_general_cs NOT NULL,
  PRIMARY KEY (`id`),
  KEY `id_progetto` (`id_progetto`),
  KEY `id_a21` (`id_a21`),
  KEY `azione` (`azione`),
  KEY `revisione` (`revisione`),
  KEY `gestione` (`gestione`),
  KEY `datainizioprevista` (`datainizioprevista`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `a22_sal` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_progetto` varchar(68) COLLATE latin1_general_cs NOT NULL,
  `id_a22` int(11) NOT NULL,
  `cig` varchar(10) COLLATE latin1_general_cs NOT NULL,
  `progressivosal` int(11) NOT NULL,
  `descrizionesal` text COLLATE latin1_general_cs NOT NULL,
  `dataemissione` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `importo` double NOT NULL,
  `note` text COLLATE latin1_general_cs NOT NULL,
  `salfinale` varchar(1) COLLATE latin1_general_cs NOT NULL COMMENT 'S: SI N: NO per default impostare NO',
  `utente_umod` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `data_umod` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `azione` varchar(10) COLLATE latin1_general_cs NOT NULL,
  `revisione` varchar(255) COLLATE latin1_general_cs DEFAULT NULL,
  `gestione` varchar(1) COLLATE latin1_general_cs NOT NULL,
  `id_a28` int(11) NOT NULL,
  `id_sal` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `id_progetto` (`id_progetto`),
  KEY `id_a22` (`id_a22`),
  KEY `id_a28` (`id_a28`),
  KEY `azione` (`azione`),
  KEY `revisione` (`revisione`),
  KEY `gestione` (`gestione`),
  KEY `dataemissione` (`dataemissione`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `a23_sospensioni` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_progetto` varchar(68) COLLATE latin1_general_cs NOT NULL,
  `id_a23` int(11) NOT NULL,
  `datainizio` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `motivosospensione` text COLLATE latin1_general_cs NOT NULL,
  `dataprevistafine` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `datafinesospensione` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `utente_umod` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `data_umod` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `azione` varchar(10) COLLATE latin1_general_cs NOT NULL,
  `revisione` varchar(255) COLLATE latin1_general_cs DEFAULT NULL,
  `gestione` varchar(1) COLLATE latin1_general_cs NOT NULL,
  `id_allegato` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `id_progetto` (`id_progetto`),
  KEY `id_a23` (`id_a23`),
  KEY `datainizio` (`datainizio`),
  KEY `azione` (`azione`),
  KEY `revisione` (`revisione`),
  KEY `gestione` (`gestione`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `a24_revoche` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_progetto` varchar(68) COLLATE latin1_general_cs NOT NULL,
  `id_a24` int(11) NOT NULL,
  `tiporevoca` varchar(1) COLLATE latin1_general_cs NOT NULL COMMENT '1= REVOCA; 2 REVOCA PARZIALE 3: RINUNCIA',
  `motivo` varchar(3) COLLATE latin1_general_cs NOT NULL COMMENT 'tabella decodifica C7',
  `importo` double NOT NULL,
  `data` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `utente_umod` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `data_umod` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `azione` varchar(10) COLLATE latin1_general_cs NOT NULL,
  `revisione` varchar(255) COLLATE latin1_general_cs DEFAULT NULL,
  `gestione` varchar(1) COLLATE latin1_general_cs NOT NULL,
  `id_allegato` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `id_progetto` (`id_progetto`),
  KEY `id_a24` (`id_a24`),
  KEY `azione` (`azione`),
  KEY `revisione` (`revisione`),
  KEY `gestione` (`gestione`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `a26_anagraficasoggetticorrelati` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_progetto` varchar(68) COLLATE latin1_general_cs NOT NULL,
  `id_a26` int(11) NOT NULL,
  `ruolorivestito` int(11) NOT NULL COMMENT '1= ''PROGRAMMATORE DEL PROGETTO''    2:ATTUATORE DEL PROGETTO   3: DESTINATARIO DEL PROGETTO      4: REALIZZATORE DEL PROGETTO',
  `cf` varchar(16) COLLATE latin1_general_cs NOT NULL,
  `denominazione` text COLLATE latin1_general_cs NOT NULL,
  `formagiuridica` varchar(6) COLLATE latin1_general_cs NOT NULL COMMENT 'tabella decodifica C8',
  `settoreattivitaeconomica` varchar(8) COLLATE latin1_general_cs NOT NULL COMMENT 'tabella decodifica C9',
  `istatregione` varchar(2) COLLATE latin1_general_cs NOT NULL,
  `istatprovincia` varchar(3) COLLATE latin1_general_cs NOT NULL,
  `istatcomune` varchar(6) COLLATE latin1_general_cs NOT NULL,
  `rappresentantelegale` varchar(200) COLLATE latin1_general_cs NOT NULL,
  `dimensione` int(11) NOT NULL COMMENT '1: ''MEDIA IMPRESA   2 PICCOLA IMPRESA     3: MICROIMPRESA     4 GRANDE IMPRESA',
  `classeaddetti` varchar(1) COLLATE latin1_general_cs NOT NULL COMMENT 'A:  FINO A 9     B: DA 10 A 49     C: DA 50 A 249     D: OLTRE 249',
  `indirizzo` text COLLATE latin1_general_cs NOT NULL,
  `cap` varchar(5) COLLATE latin1_general_cs NOT NULL,
  `utente_umod` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `data_umod` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `azione` varchar(10) COLLATE latin1_general_cs NOT NULL,
  `revisione` varchar(255) COLLATE latin1_general_cs DEFAULT NULL,
  `gestione` varchar(1) COLLATE latin1_general_cs NOT NULL,
  `fontedati` varchar(1) COLLATE latin1_general_cs NOT NULL,
  `id_tipoente` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `id_progetto` (`id_progetto`),
  KEY `id_a26` (`id_a26`),
  KEY `azione` (`azione`),
  KEY `revisione` (`revisione`),
  KEY `gestione` (`gestione`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `a28_proceduraaggiudicazione` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_progetto` int(11) NOT NULL,
  `id_a28` int(11) NOT NULL,
  `cig` varchar(10) COLLATE latin1_general_cs NOT NULL,
  `descrizioneprocedura` varchar(200) COLLATE latin1_general_cs NOT NULL,
  `tipoprocedura` int(11) NOT NULL COMMENT 'tabella decodifica C11',
  `note` text COLLATE latin1_general_cs NOT NULL,
  `importo` double NOT NULL,
  `tipocontratto` varchar(1) COLLATE latin1_general_cs NOT NULL COMMENT 'P: contratto principale  S: contratto secondario',
  `id_a21` int(11) NOT NULL COMMENT 'contriene id della tab a21 per il progetto dell''appalto principale',
  `utente_umod` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `data_umod` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `azione` varchar(10) COLLATE latin1_general_cs NOT NULL,
  `revisione` varchar(255) COLLATE latin1_general_cs DEFAULT NULL,
  `gestione` varchar(1) COLLATE latin1_general_cs NOT NULL,
  `id_gara_simog` varchar(255) COLLATE latin1_general_cs NOT NULL COMMENT 'identificativo della gara su SIMOG',
  PRIMARY KEY (`id`),
  KEY `id_progetto` (`id_progetto`),
  KEY `id_a28` (`id_a28`),
  KEY `azione` (`azione`),
  KEY `revisione` (`revisione`),
  KEY `gestione` (`gestione`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `a29_iterproceduraaggiudicazione` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_progetto` varchar(68) COLLATE latin1_general_cs NOT NULL,
  `id_a29` int(11) NOT NULL,
  `cig` varchar(10) COLLATE latin1_general_cs NOT NULL,
  `stepprocedura` int(11) NOT NULL COMMENT 'tabella decodifica C10',
  `dataprevista` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `dataeffettiva` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `cfsoggettocompetente` varchar(60) COLLATE latin1_general_cs NOT NULL,
  `importostep` double NOT NULL,
  `motivoscostamento` varchar(2) COLLATE latin1_general_cs NOT NULL COMMENT 'A1: ''PROBLEMI AMMINISTRATIVI''   A2: PROBLEMATICHE TECNICHE''',
  `note` text COLLATE latin1_general_cs NOT NULL,
  `utente_umod` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `data_umod` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `azione` varchar(10) COLLATE latin1_general_cs NOT NULL,
  `revisione` varchar(255) COLLATE latin1_general_cs DEFAULT NULL,
  `gestione` varchar(1) COLLATE latin1_general_cs NOT NULL,
  `id_a28` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `id_progetto` (`id_progetto`),
  KEY `id_a29` (`id_a29`),
  KEY `azione` (`azione`),
  KEY `revisione` (`revisione`),
  KEY `gestione` (`gestione`),
  KEY `id_a28` (`id_a28`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `a30_progettocig` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_progetto` varchar(68) COLLATE latin1_general_cs NOT NULL,
  `id_a30` int(11) NOT NULL,
  `cig` varchar(10) COLLATE latin1_general_cs NOT NULL,
  `dataeffettiva` date NOT NULL,
  `codiceufficioamministrazione` int(11) NOT NULL COMMENT 'ufficio che bandisce la gara SIMOG',
  `id_lp_soggetto` int(11) NOT NULL,
  `utente_umod` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `data_umod` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `azione` varchar(10) COLLATE latin1_general_cs NOT NULL,
  `revisione` varchar(255) COLLATE latin1_general_cs DEFAULT NULL,
  `gestione` varchar(1) COLLATE latin1_general_cs NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `a7_anagrafica_localizzazione` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_progetto` varchar(68) COLLATE latin1_general_cs NOT NULL,
  `id_a7` int(11) NOT NULL,
  `istatregione` varchar(2) COLLATE latin1_general_cs NOT NULL,
  `istatprovincia` varchar(3) COLLATE latin1_general_cs NOT NULL,
  `istatcomune` varchar(6) COLLATE latin1_general_cs NOT NULL,
  `cap` varchar(5) COLLATE latin1_general_cs NOT NULL,
  `utente_umod` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `data_umod` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `azione` varchar(10) COLLATE latin1_general_cs NOT NULL,
  `revisione` varchar(255) COLLATE latin1_general_cs DEFAULT NULL,
  `gestione` varchar(1) COLLATE latin1_general_cs NOT NULL COMMENT 'G= gestione S= storico',
  `toponimo` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `indirizzo` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `civico` varchar(10) COLLATE latin1_general_cs NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `a8_anagrafica_geolocalizzazione` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_progetto` varchar(68) COLLATE latin1_general_cs NOT NULL,
  `id_a8` int(11) NOT NULL,
  `coordx` double NOT NULL,
  `coordy` double NOT NULL,
  `utente_umod` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `data_umod` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `azione` varchar(10) COLLATE latin1_general_cs NOT NULL,
  `revisione` varchar(255) COLLATE latin1_general_cs DEFAULT NULL,
  `gestione` varchar(1) COLLATE latin1_general_cs NOT NULL COMMENT 'G o S',
  PRIMARY KEY (`id`,`id_progetto`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `a9_finanziamento` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_progetto` varchar(68) COLLATE latin1_general_cs NOT NULL,
  `id_a9` int(11) NOT NULL,
  `fontefinanziamento` varchar(3) COLLATE latin1_general_cs NOT NULL COMMENT 'tabella decodifica C13',
  `importofinanziamento` double NOT NULL,
  `numeronorma` int(11) NOT NULL,
  `annonorma` int(11) NOT NULL,
  `tiponorma` varchar(4) COLLATE latin1_general_cs NOT NULL COMMENT 'tabella decodifica C4',
  `numerodelcipe` int(11) NOT NULL,
  `annodelcipe` int(11) NOT NULL,
  `estremiprovvedimento` text COLLATE latin1_general_cs NOT NULL,
  `istatregione` varchar(2) COLLATE latin1_general_cs NOT NULL,
  `istatprovincia` varchar(3) COLLATE latin1_general_cs NOT NULL,
  `istatcomune` varchar(6) COLLATE latin1_general_cs NOT NULL,
  `descrizionesoggettoprivato` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `cfprivato` varchar(16) COLLATE latin1_general_cs NOT NULL,
  `criticita` text COLLATE latin1_general_cs NOT NULL,
  `presenzaeconomie` varchar(1) COLLATE latin1_general_cs NOT NULL COMMENT 'S= Si   N=No',
  `note` text COLLATE latin1_general_cs NOT NULL COMMENT 'facoltativo e non considerato da BDAP',
  `id_lp_finanziaria` int(11) NOT NULL COMMENT 'facoltativo e non considerato da BDAP',
  `utente_umod` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `data_umod` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `azione` varchar(10) COLLATE latin1_general_cs NOT NULL,
  `revisione` varchar(255) COLLATE latin1_general_cs DEFAULT NULL,
  `gestione` varchar(1) COLLATE latin1_general_cs NOT NULL,
  `id_a21` int(11) NOT NULL,
  `capitolobilancio` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `id_lp_impresa` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `id_a9` (`id_a9`),
  KEY `id_a21` (`id_a21`),
  KEY `azione` (`azione`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `albof_anagcertificazioni` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `codice` varchar(50) COLLATE latin1_general_cs NOT NULL,
  `descrizione` varchar(1000) COLLATE latin1_general_cs NOT NULL,
  `norma` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `trashed` int(11) NOT NULL DEFAULT '0',
  `utente_umod` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `data_umod` varchar(20) COLLATE latin1_general_cs NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `albof_anagreqdic` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `tipo` char(1) COLLATE latin1_general_cs NOT NULL COMMENT 'R richiesta, D dichiarazione',
  `codice` varchar(50) COLLATE latin1_general_cs NOT NULL,
  `descrizione` varchar(1000) COLLATE latin1_general_cs NOT NULL,
  `testo` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `valori` text COLLATE latin1_general_cs NOT NULL,
  `tipocampo` int(11) NOT NULL,
  `norma` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `trashed` int(11) NOT NULL DEFAULT '0',
  `fvalore` int(11) NOT NULL DEFAULT '0' COMMENT '0: non rieschie un valore 1: richiesto valore all esterno es. fatturato',
  `utente_umod` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `data_umod` varchar(20) COLLATE latin1_general_cs NOT NULL,
  PRIMARY KEY (`id`),
  KEY `tipo` (`tipo`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `albof_anagupdate` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `idrichiesta` int(11) NOT NULL,
  `tiporec` int(11) NOT NULL COMMENT '1)soggetto - 2)dichiarante - 3)leg rappr',
  `azione` tinyint(4) NOT NULL COMMENT '1) INS - 2)UPD - 3) DEL',
  `idrec_relativo` int(11) NOT NULL,
  `jsonrec` text COLLATE latin1_general_cs NOT NULL COMMENT 'json del record',
  PRIMARY KEY (`id`),
  KEY `idrichiesta` (`idrichiesta`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `albof_categorie` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `tipo_lsf` char(1) COLLATE latin1_general_cs NOT NULL,
  `id_padre` int(11) NOT NULL,
  `codice` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `label` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `descrizione` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `ordine` int(11) NOT NULL DEFAULT '0',
  `attivo` int(11) NOT NULL DEFAULT '1',
  `requisiti` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `foglia` int(11) NOT NULL DEFAULT '0',
  `f_tiposoglia` tinyint(4) NOT NULL COMMENT '0-NO SOGLIA; 1-fino a vmax; 2-oltre vmax',
  `vmin` double NOT NULL,
  `vmax` double NOT NULL,
  `dataini` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `dataend` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `f_dispoimprese` int(11) NOT NULL COMMENT 'flag voce dispo x imprese',
  `f_dispoprof` int(11) NOT NULL COMMENT 'flag voce dispo x professionisti',
  `f_cvprof` int(11) NOT NULL DEFAULT '0',
  `trashed` int(11) NOT NULL DEFAULT '0',
  `utente_umod` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `data_umod` varchar(20) COLLATE latin1_general_cs NOT NULL,
  PRIMARY KEY (`id`),
  KEY `tipo_lsf` (`tipo_lsf`),
  KEY `id_padre` (`id_padre`),
  KEY `trashed` (`trashed`),
  KEY `albof_categorie_datainiend` (`dataini`,`dataend`),
  KEY `f_dispoprof` (`f_dispoprof`),
  KEY `f_dispoimprese` (`f_dispoimprese`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `albof_configurazionealbo` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `codiceProcedimentoOnline` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `itecodrinnovo` varchar(10) COLLATE latin1_general_cs NOT NULL COMMENT 'codice del procedimento per rinnovo/modifica iscrizione',
  `contatoreGare` varchar(255) COLLATE latin1_general_cs NOT NULL COMMENT 'indica come contare le gare a cui il fornitore è stato invitato',
  `domainCode` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `wsEndpoint` varchar(1000) COLLATE latin1_general_cs NOT NULL,
  `wsWsdl` varchar(1000) COLLATE latin1_general_cs NOT NULL,
  `wsNamespace` varchar(1000) COLLATE latin1_general_cs NOT NULL,
  `wsUser` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `wsPassword` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `wsDomain` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `ggvalidata` int(11) NOT NULL COMMENT 'giorni di validità delle iscrizioni all''albo. dato usato per calcolare lascadenza',
  `umvalidita` tinyint(4) NOT NULL DEFAULT '1' COMMENT '1-days, 2-month, 3-years',
  `policyvalidita` tinyint(4) NOT NULL DEFAULT '1' COMMENT '1-validita per soggetto, 2-validita per singola categoria',
  `attivacollapse` int(11) NOT NULL DEFAULT '0' COMMENT 'attiva la funzione di collapse nelle categorie',
  `modocollapse` int(11) NOT NULL DEFAULT '0' COMMENT '0) tutto aperto 1) chiudi ultimo livello (no foglie)',
  `rdc_dichiara` int(11) NOT NULL DEFAULT '1' COMMENT 'Valore predefinito FO R/D/C 1=>DICHIARA 0=>blank',
  `rdc_solounafascia` int(11) NOT NULL DEFAULT '1' COMMENT '1 mostra tutto; 2 solo req/dic fascia piu alta',
  `frasecategorie` text COLLATE latin1_general_cs NOT NULL COMMENT 'frase al passo di selezione delle categorie',
  `style_frasecategorie` text COLLATE latin1_general_cs NOT NULL,
  `data_umod` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `utente_umod` varchar(255) COLLATE latin1_general_cs NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `albof_fasce` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `tipo_lsf` char(1) COLLATE latin1_general_cs DEFAULT NULL,
  `descrizione` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `vmin` double NOT NULL,
  `vmax` double NOT NULL,
  `dataini` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `dataend` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `trashed` int(11) NOT NULL DEFAULT '0',
  `utente_umod` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `data_umod` varchar(10) COLLATE latin1_general_cs NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `albof_iscrizione_categ` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `tiposoggetto` int(11) NOT NULL COMMENT '1-lp_imprese, 2-lp_professionisti',
  `idsoggetto` int(11) NOT NULL,
  `id_albof_categorie` int(11) NOT NULL,
  `id_albof_richiesta` int(11) NOT NULL,
  `dataini` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `dataend` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `trashed` int(11) NOT NULL DEFAULT '0',
  `utente_umod` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `data_umod` varchar(20) COLLATE latin1_general_cs NOT NULL,
  PRIMARY KEY (`id`),
  KEY `tiposoggetto` (`tiposoggetto`,`idsoggetto`),
  KEY `idcateg` (`id_albof_categorie`),
  KEY `dataini` (`dataini`,`dataend`),
  KEY `trashed` (`trashed`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `albof_op_estratti` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_albof_salva_estrazione` int(11) NOT NULL,
  `tipo_operatore` varchar(10) COLLATE latin1_general_cs NOT NULL,
  `id_operatore` int(11) NOT NULL,
  `data_umod` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `utente_umod` varchar(255) COLLATE latin1_general_cs NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `albof_permessi_gruppi` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `idcateg` int(11) NOT NULL,
  `idstruttura` int(11) NOT NULL,
  `f_approva` int(11) NOT NULL COMMENT 'flag permesso approva richiesta',
  `f_iscrivi` int(11) NOT NULL COMMENT 'flag permesso iscrivi albo',
  `dataini` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `dataend` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `trashed` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `idstruttura` (`idstruttura`),
  KEY `idcateg` (`idcateg`),
  KEY `dataini` (`dataini`,`dataend`),
  KEY `trashed` (`trashed`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `albof_r_categcertificazioni` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `idcategoria` int(11) NOT NULL,
  `idcertificazioni` int(11) NOT NULL,
  `obblig` int(11) NOT NULL DEFAULT '0',
  `trashed` int(11) NOT NULL DEFAULT '0',
  `utente_umod` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `data_umod` varchar(20) COLLATE latin1_general_cs NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idcategoria` (`idcategoria`),
  KEY `idcertificazioni` (`idcertificazioni`),
  KEY `trashed` (`trashed`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `albof_r_categreqdic` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `idcategoria` int(11) NOT NULL,
  `idreqdic` int(11) NOT NULL,
  `obblig` int(11) NOT NULL DEFAULT '0',
  `trashed` int(11) NOT NULL DEFAULT '0',
  `utente_umod` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `data_umod` varchar(20) COLLATE latin1_general_cs NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idcategoria` (`idcategoria`),
  KEY `idreqdic` (`idreqdic`),
  KEY `trashed` (`trashed`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `albof_r_esibente_soggetto` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_albofsogg` int(11) NOT NULL,
  `id_esibente` int(11) NOT NULL,
  `id_richiesta` int(11) NOT NULL,
  `autorizzato` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `albof_r_richiesta_certificazioni` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `idrichiesta` int(11) NOT NULL,
  `idcert` int(11) NOT NULL,
  `stato` int(11) NOT NULL COMMENT '10-Da valutare, 20-Valido, 30-NON valido, 40-Richiesta integrazione, 50-NON VALUDATO, 60-NON DICHIARATO',
  `obblig` tinyint(4) NOT NULL,
  `note` text COLLATE latin1_general_cs NOT NULL,
  `livello_soggetto` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `dataini` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `dataend` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `rif_cert` text COLLATE latin1_general_cs NOT NULL COMMENT 'riferimenti della certificazioni',
  `rif_soggetto` text COLLATE latin1_general_cs NOT NULL COMMENT 'eventuali soggetti a cui la certificazione si riferisce',
  `idcopertura` int(11) NOT NULL,
  `trashed` tinyint(4) NOT NULL,
  `utente_umod` varchar(50) COLLATE latin1_general_cs NOT NULL,
  `data_umod` varchar(20) COLLATE latin1_general_cs NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idrichiesta` (`idrichiesta`),
  KEY `idcert` (`idcert`),
  KEY `trashed` (`trashed`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `albof_r_richiesta_reqdic` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `idrichiesta` int(11) NOT NULL,
  `idreqdic` int(11) NOT NULL,
  `idcategistanza` int(11) NOT NULL,
  `obblig` tinyint(4) NOT NULL,
  `note` text COLLATE latin1_general_cs NOT NULL,
  `stato` int(11) NOT NULL COMMENT '10-Da valutare, 20-Valido, 30-NON valido, 40-Richiesta integrazione, 50-NON VALUDATO;60-NON DICHIARATO',
  `jrisposta` text COLLATE latin1_general_cs NOT NULL,
  `fcopertosoa` tinyint(4) NOT NULL,
  `idcopertura` int(11) NOT NULL,
  `valore` varchar(255) COLLATE latin1_general_cs NOT NULL COMMENT 'campo per esperire un valore es. fatturato ecc',
  `dataini` varchar(8) COLLATE latin1_general_cs NOT NULL DEFAULT '',
  `dataend` varchar(8) COLLATE latin1_general_cs NOT NULL DEFAULT '',
  `trashed` tinyint(4) NOT NULL,
  `utente_umod` varchar(50) COLLATE latin1_general_cs NOT NULL,
  `data_umod` varchar(20) COLLATE latin1_general_cs NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idrichiesta` (`idrichiesta`),
  KEY `idreqdic` (`idreqdic`),
  KEY `trashed` (`trashed`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `albof_richiesta` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `tiposoggetto` int(11) NOT NULL COMMENT '1-lp_imprese, 3-lp_professionisti',
  `idsoggetto` int(11) NOT NULL,
  `richiesta_nprot` varchar(50) COLLATE latin1_general_cs NOT NULL,
  `richiesta_data` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `richiesta_rif` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `idesibente` int(11) NOT NULL,
  `stato` int(11) NOT NULL COMMENT '10-da valutare, 20-rifiutata, 30-integrazioni, 40-verifica requisiti, 50-accettato, 60-pubblicato in albo',
  `esito` int(11) NOT NULL,
  `esito_prot` varchar(50) COLLATE latin1_general_cs NOT NULL COMMENT 'STATO PUBBLICATO IN ALBO',
  `esito_data` varchar(8) COLLATE latin1_general_cs NOT NULL COMMENT 'STATO PUBBLICATO IN ALBO',
  `accettaz_prot` varchar(50) COLLATE latin1_general_cs NOT NULL COMMENT 'STATO ACCETTAZIONE',
  `accettaz_data` varchar(8) COLLATE latin1_general_cs NOT NULL COMMENT 'STATO ACCETTAZIONE',
  `motivazione` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `richiesta_prec` int(11) NOT NULL,
  `sorgente` int(11) NOT NULL DEFAULT '-1' COMMENT '1-ALBOF_PORTALE, 2-LOCALE',
  `idsorgente` int(11) NOT NULL,
  `trashed` tinyint(4) NOT NULL,
  `itecodrichiesta` varchar(20) COLLATE latin1_general_cs NOT NULL DEFAULT '2700' COMMENT 'codice del procedimento che ha generato la richiesta',
  `utente_umod` varchar(50) COLLATE latin1_general_cs NOT NULL,
  `data_umod` varchar(20) COLLATE latin1_general_cs NOT NULL,
  PRIMARY KEY (`id`),
  KEY `trashed` (`trashed`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `albof_richiesta_categ` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_albof_richiesta` int(11) NOT NULL,
  `id_albof_categorie` int(11) NOT NULL,
  `esito` int(11) NOT NULL DEFAULT '0' COMMENT '0->isc. non convalidata  1->iscr. convalidata',
  `motivazione` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `iscrizione_dataini` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `iscrizione_dataend` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `vrequisiti` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `utente_umod` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `data_umod` varchar(20) COLLATE latin1_general_cs NOT NULL,
  `trashed` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `idrichiesta` (`id_albof_richiesta`),
  KEY `idcateg` (`id_albof_categorie`),
  KEY `trashed` (`trashed`),
  KEY `iscrizione_dataini` (`iscrizione_dataini`,`iscrizione_dataend`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `albof_rifiutoinviti` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `idestrazione` int(11) NOT NULL,
  `tiposogg` int(11) NOT NULL,
  `idsogg` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `id` (`id`,`idestrazione`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `albof_salva_estrazione` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `oggetto_estrazione` varchar(1000) COLLATE latin1_general_cs NOT NULL,
  `tipo_salvataggio` varchar(1000) COLLATE latin1_general_cs NOT NULL COMMENT 'F: silvi - R: risultato',
  `dati` text COLLATE latin1_general_cs NOT NULL,
  `id_tipo_impresa` int(11) NOT NULL COMMENT '1: singola impresa 2: raggrupp. 3: professionista',
  `id_operatore` int(11) NOT NULL,
  `importogara` double NOT NULL,
  `dataaggiudicazione` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `data_estrazione` varchar(255) COLLATE latin1_general_cs NOT NULL COMMENT 'data di estrazione considerata come data invito',
  `trashed` int(11) NOT NULL DEFAULT '0',
  `data_umod` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `utente_umod` varchar(255) COLLATE latin1_general_cs NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `albof_soggetto` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `tiposogg` int(11) NOT NULL,
  `idsogg` int(11) NOT NULL,
  `stato` int(11) NOT NULL,
  `dtini` varchar(8) COLLATE latin1_general_cs NOT NULL COMMENT 'dt validita'' soggetto',
  `dtend` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `sosp_dtini` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `sosp_dtend` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `dtini_allcateg` varchar(20) COLLATE latin1_general_cs NOT NULL COMMENT 'dt inizio validita'' tutte le categorie',
  `dtend_allcateg` varchar(20) COLLATE latin1_general_cs NOT NULL COMMENT 'dt fine validita'' tutte categorie',
  `ngaresaltate` int(11) NOT NULL COMMENT 'n.gare saltate',
  `trashed` int(11) NOT NULL,
  `utente_umod` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `data_umod` varchar(20) COLLATE latin1_general_cs NOT NULL,
  `data_ins` varchar(20) COLLATE latin1_general_cs NOT NULL,
  `utente_ins` varchar(255) COLLATE latin1_general_cs NOT NULL,
  PRIMARY KEY (`id`),
  KEY `tiposogg` (`tiposogg`,`idsogg`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `amt_allegati` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_amt` int(11) NOT NULL COMMENT 'FK',
  `model_sorgente` varchar(255) COLLATE latin1_general_cs NOT NULL COMMENT 'nameForm associato allegato',
  `id_sorgente` int(11) NOT NULL,
  `allegato` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `filename` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `size` int(11) NOT NULL COMMENT 'dimensione allegati espressa in byte',
  `preview` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `gestione` varchar(10) COLLATE latin1_general_cs NOT NULL,
  `utente_umod` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `data_umod` varchar(20) COLLATE latin1_general_cs NOT NULL,
  `ordine` int(11) NOT NULL,
  `data_ins` varchar(10) COLLATE latin1_general_cs NOT NULL,
  `utente_ins` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `descrizione` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `id_classificazione` int(11) NOT NULL,
  `importazione` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `amt_allegati_tipo` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `descrizione` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `trashed` int(11) NOT NULL,
  `utente_umod` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `data_umod` varchar(10) COLLATE latin1_general_cs NOT NULL,
  `utente_ins` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `data_ins` varchar(10) COLLATE latin1_general_cs NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `amt_indice` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `voce` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `livello` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `id_padre` int(11) NOT NULL,
  `norma` text COLLATE latin1_general_cs NOT NULL,
  `linknorma` text COLLATE latin1_general_cs NOT NULL,
  `tipoconfigurazione` int(11) NOT NULL COMMENT '1: contenuto html 2:ws 3:grid 4:link 5:itaengine',
  `utente_umod` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `data_umod` varchar(10) COLLATE latin1_general_cs NOT NULL,
  `ordine` int(11) NOT NULL,
  `trashed` int(11) NOT NULL DEFAULT '0',
  `dataini` varchar(10) COLLATE latin1_general_cs NOT NULL,
  `dataend` varchar(10) COLLATE latin1_general_cs NOT NULL,
  `vocebase` int(11) NOT NULL DEFAULT '0' COMMENT '1 => voce base prefinita  0=> voce utente',
  `attivo` int(11) NOT NULL DEFAULT '1' COMMENT '1=> ATTIVO   0=> NON ATTIVO',
  `datainizio` varchar(10) COLLATE latin1_general_cs NOT NULL,
  `datafine` varchar(10) COLLATE latin1_general_cs NOT NULL,
  `codice_amt` int(11) NOT NULL,
  `codice_padre_amt` int(11) NOT NULL,
  `testohtml` text COLLATE latin1_general_cs NOT NULL COMMENT 'testo per inserire l''html in testa alla sezione',
  `utente_ins` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `data_ins` varchar(10) COLLATE latin1_general_cs NOT NULL,
  `sezioni_content_class` text COLLATE latin1_general_cs NOT NULL,
  `sezioni_content_tml` int(11) NOT NULL,
  `id_fixed_tipo_documento` int(11) NOT NULL COMMENT 'Tipo documento vincolato',
  `tipoordine` int(11) NOT NULL,
  `meta` text COLLATE latin1_general_cs NOT NULL COMMENT 'metadati per ws task',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `amt_parametriportale` (
  `id` int(11) NOT NULL,
  `tipoOutput` int(11) NOT NULL COMMENT '1-plugin WP',
  `parametri` text COLLATE latin1_general_cs NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `amt_test` (
  `id` int(11) NOT NULL,
  `descrizione` varchar(10) COLLATE latin1_general_cs NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `amt_tipo_documento` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `codice` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `descrizione` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `trashed` int(11) NOT NULL DEFAULT '0',
  `utente_umod` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `data_umod` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `data_ins` varchar(10) COLLATE latin1_general_cs NOT NULL,
  `utente_ins` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `custom_type` text COLLATE latin1_general_cs NOT NULL COMMENT 'Informazioni personalizzate',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `amt_tipo_documento_dag` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_tipo_documento` int(11) NOT NULL COMMENT 'tipo dicumento',
  `codice` varchar(60) COLLATE latin1_general_cs NOT NULL COMMENT 'codice campo',
  `descrizione` text COLLATE latin1_general_cs NOT NULL COMMENT 'descrizione',
  `sequenza` int(11) NOT NULL COMMENT 'sequenza',
  `tipo` varchar(20) COLLATE latin1_general_cs NOT NULL COMMENT 'tipo dato',
  `meta` text COLLATE latin1_general_cs NOT NULL COMMENT 'meta dati',
  `label` text COLLATE latin1_general_cs NOT NULL COMMENT 'etichetta in/out',
  `size` int(11) NOT NULL COMMENT 'dimensione in/out',
  `required` smallint(6) NOT NULL COMMENT 'obbligatorio',
  `fl_search` smallint(6) NOT NULL COMMENT 'cerca fo',
  `fl_table` smallint(6) NOT NULL COMMENT 'in tabella fo',
  `fl_card` smallint(6) NOT NULL COMMENT 'in scheda fo',
  `trashed` smallint(6) NOT NULL COMMENT 'cancellazione logica',
  `utente_umod` varchar(255) COLLATE latin1_general_cs NOT NULL COMMENT 'utente modifica',
  `data_umod` varchar(8) COLLATE latin1_general_cs NOT NULL COMMENT 'data modifica',
  `data_ins` varchar(10) COLLATE latin1_general_cs NOT NULL,
  `utente_ins` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `base_val` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `custom_type` text COLLATE latin1_general_cs NOT NULL COMMENT 'Informazioni personalizzate',
  PRIMARY KEY (`id`),
  KEY `id_tipo_documento` (`id_tipo_documento`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `amt_voce_dag` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_tipo_documento_dag` int(11) NOT NULL,
  `id_voce_grid` int(11) NOT NULL,
  `dag_value` text COLLATE latin1_general_cs NOT NULL,
  `data_ins` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `utente_ins` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `data_umod` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `utente_umod` varchar(255) COLLATE latin1_general_cs NOT NULL,
  PRIMARY KEY (`id`),
  KEY `id_tipo_documento_dag` (`id_tipo_documento_dag`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `amt_voce_grid` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_amt_indice` int(11) NOT NULL,
  `id_amt_tipo_documento` int(11) NOT NULL,
  `descrizione` text COLLATE latin1_general_cs NOT NULL,
  `norma` text COLLATE latin1_general_cs NOT NULL,
  `linknorma` text COLLATE latin1_general_cs NOT NULL,
  `dataini` varchar(10) COLLATE latin1_general_cs NOT NULL,
  `dataend` varchar(10) COLLATE latin1_general_cs NOT NULL,
  `utente_umod` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `data_umod` varchar(10) COLLATE latin1_general_cs NOT NULL,
  `trashed` int(11) NOT NULL DEFAULT '0',
  `ordine` int(11) NOT NULL,
  `data_ins` varchar(10) COLLATE latin1_general_cs NOT NULL,
  `utente_ins` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `text` text COLLATE latin1_general_cs NOT NULL COMMENT 'Testo Libero',
  `importazione` int(11) NOT NULL,
  `sync_date` varchar(8) COLLATE latin1_general_cs NOT NULL COMMENT 'DATA ULTIMA SINCRONIZZAZIONE',
  `sync_external_context` varchar(20) COLLATE latin1_general_cs NOT NULL COMMENT 'EXTERNAL CONTEXT',
  `sync_id` int(11) NOT NULL COMMENT 'ID RESTITUITO DALLA SINCRONIZZAZIONE',
  `sync_time` varchar(8) COLLATE latin1_general_cs NOT NULL COMMENT 'ORA ULTIMA SINCRONIZZAZIONE',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `amt_voce_itaengine` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_amt_indice` int(11) NOT NULL,
  `tiposorgente` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `parametri` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `trashed` int(11) NOT NULL DEFAULT '0',
  `dataini` varchar(10) COLLATE latin1_general_cs NOT NULL,
  `dataend` varchar(10) COLLATE latin1_general_cs NOT NULL,
  `utente_umod` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `data_umod` varchar(10) COLLATE latin1_general_cs NOT NULL,
  `data_ins` varchar(10) COLLATE latin1_general_cs NOT NULL,
  `utente_ins` varchar(255) COLLATE latin1_general_cs NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `amt_voce_link` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_amt_indice` int(11) NOT NULL,
  `link` text COLLATE latin1_general_cs NOT NULL,
  `descrizione_link` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `tipo_apertura` int(11) NOT NULL DEFAULT '0' COMMENT '0: link esterno 1:stessa pagina',
  `parametri` text COLLATE latin1_general_cs NOT NULL,
  `trashed` int(11) NOT NULL DEFAULT '0',
  `dataini` varchar(10) COLLATE latin1_general_cs NOT NULL,
  `dataend` varchar(10) COLLATE latin1_general_cs NOT NULL,
  `utente_umod` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `data_umod` varchar(10) COLLATE latin1_general_cs NOT NULL,
  `ordine` int(11) NOT NULL,
  `utente_ins` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `data_ins` varchar(10) COLLATE latin1_general_cs NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `amt_voce_statica` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_amt_indice` int(11) NOT NULL,
  `html` text COLLATE latin1_general_cs NOT NULL,
  `trashed` int(11) NOT NULL DEFAULT '0',
  `dataini` varchar(10) COLLATE latin1_general_cs NOT NULL,
  `dataend` varchar(10) COLLATE latin1_general_cs NOT NULL,
  `utente_umod` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `data_umod` varchar(10) COLLATE latin1_general_cs NOT NULL,
  `data_ins` varchar(10) COLLATE latin1_general_cs NOT NULL,
  `utente_ins` varchar(255) COLLATE latin1_general_cs NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `amt_voce_text` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_amt_indice` int(11) NOT NULL,
  `text` text COLLATE latin1_general_cs NOT NULL,
  `dataini` varchar(10) COLLATE latin1_general_cs NOT NULL,
  `dataend` varchar(10) COLLATE latin1_general_cs NOT NULL,
  `utente_umod` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `data_umod` varchar(10) COLLATE latin1_general_cs NOT NULL,
  `trashed` int(11) NOT NULL DEFAULT '0',
  `ordine` int(11) NOT NULL,
  `data_ins` varchar(10) COLLATE latin1_general_cs NOT NULL,
  `utente_ins` varchar(255) COLLATE latin1_general_cs NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `amt_voce_ws` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_amt_indice` int(11) NOT NULL,
  `endpoint` text COLLATE latin1_general_cs NOT NULL,
  `user` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `psw` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `parametri` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `trashed` int(11) NOT NULL DEFAULT '0',
  `dataini` varchar(10) COLLATE latin1_general_cs NOT NULL,
  `dataend` varchar(100) COLLATE latin1_general_cs NOT NULL,
  `utente_ins` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `data_ins` varchar(10) COLLATE latin1_general_cs NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `anagrafica_b1` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `codicefiscalesoggettocorrelatocup` text COLLATE latin1_general_cs,
  `denominazionesoggettocorrelatocup` text COLLATE latin1_general_cs,
  `cup` text COLLATE latin1_general_cs,
  `descrizionecup` text COLLATE latin1_general_cs,
  `descrizionenatura` text COLLATE latin1_general_cs,
  `natura` text COLLATE latin1_general_cs,
  `descrizionetipologiaintervento` text COLLATE latin1_general_cs,
  `descrizionesettoreintervento` text COLLATE latin1_general_cs,
  `descrizionesottosettoreintervento` text COLLATE latin1_general_cs,
  `descrizionecategoriaintervento` text COLLATE latin1_general_cs,
  `descrizioneindicatore` text COLLATE latin1_general_cs,
  `descrizioneregionecup` text COLLATE latin1_general_cs,
  `descrizioneprovinciacup` text COLLATE latin1_general_cs,
  `descrizionecomunecup` text COLLATE latin1_general_cs,
  `ruolocup` text COLLATE latin1_general_cs,
  `descrizioneruolocup` text COLLATE latin1_general_cs,
  `formagiuridicacup` text COLLATE latin1_general_cs,
  `cfrappresentantelegale` text COLLATE latin1_general_cs,
  `codiceistatsedesoggetto` text COLLATE latin1_general_cs,
  `indirizzosedesoggettocup` text COLLATE latin1_general_cs,
  `capsedesoggettocup` text COLLATE latin1_general_cs,
  `statocup` text COLLATE latin1_general_cs,
  `descrizionestatocup` text COLLATE latin1_general_cs,
  `datainiziovalidita` text COLLATE latin1_general_cs,
  `datafinevalidita` text COLLATE latin1_general_cs,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `anagrafica_b2` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `codicefiscalesoggettocorrelatocup` text COLLATE latin1_general_cs,
  `denominazionesoggettocorrelatocup` text COLLATE latin1_general_cs,
  `cup` text COLLATE latin1_general_cs,
  `gara` text COLLATE latin1_general_cs,
  `descrizionegara` text COLLATE latin1_general_cs,
  `cig` text COLLATE latin1_general_cs,
  `descrizioneoggettolotto` text COLLATE latin1_general_cs,
  `datascadenzaofferta` text COLLATE latin1_general_cs,
  `regionecig` text COLLATE latin1_general_cs,
  `provinciacig` text COLLATE latin1_general_cs,
  `comunecig` text COLLATE latin1_general_cs,
  `tiposceltacontraente` text COLLATE latin1_general_cs,
  `descrizionetiposceltacontraente` text COLLATE latin1_general_cs,
  `codicefiscalesoggettocorrelatocig` text COLLATE latin1_general_cs,
  `denominazionesoggettocorrelatocig` text COLLATE latin1_general_cs,
  `descrizioneruolocig` text COLLATE latin1_general_cs,
  `ausa` text COLLATE latin1_general_cs,
  `formagiuridicacig` text COLLATE latin1_general_cs,
  `settoreateco` text COLLATE latin1_general_cs,
  `indirizzo` text COLLATE latin1_general_cs,
  `descrizionesedelegale` text COLLATE latin1_general_cs,
  `cap` text COLLATE latin1_general_cs,
  `denominazionerappresentantelegale` text COLLATE latin1_general_cs,
  `nutszonacig` text COLLATE latin1_general_cs,
  `nutsregionecig` text COLLATE latin1_general_cs,
  `nutsprovinciacig` text COLLATE latin1_general_cs,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `anagrafica_b3` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `codicefiscalesoggettocorrelatocup` text COLLATE latin1_general_cs,
  `denominazionesoggettocorrelatocup` text COLLATE latin1_general_cs,
  `cup` text COLLATE latin1_general_cs,
  `descrizionecup` text COLLATE latin1_general_cs,
  `descrizionenatura` text COLLATE latin1_general_cs,
  `natura` text COLLATE latin1_general_cs,
  `descrizionetipologiaintervento` text COLLATE latin1_general_cs,
  `descrizionesettoreintervento` text COLLATE latin1_general_cs,
  `descrizionesottosettoreintervento` text COLLATE latin1_general_cs,
  `descrizionecategoriaintervento` text COLLATE latin1_general_cs,
  `descrizioneindicatore` text COLLATE latin1_general_cs,
  `descrizioneregionecup` text COLLATE latin1_general_cs,
  `descrizioneprovinciacup` text COLLATE latin1_general_cs,
  `descrizionecomunecup` text COLLATE latin1_general_cs,
  `ruolocup` text COLLATE latin1_general_cs,
  `descrizioneruolocup` text COLLATE latin1_general_cs,
  `formagiuridicacup` text COLLATE latin1_general_cs,
  `cfrappresentantelegale` text COLLATE latin1_general_cs,
  `codiceistatsedesoggetto` text COLLATE latin1_general_cs,
  `indirizzosedesoggettocup` text COLLATE latin1_general_cs,
  `capsedesoggettocup` text COLLATE latin1_general_cs,
  `descrizionestatocup` text COLLATE latin1_general_cs,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `bdap_batchmop` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `utente` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `psw` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `utente_umod` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `data_umod` varchar(10) COLLATE latin1_general_cs NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `bdap_codici_errori` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `scheda` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `codice` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `descrizione` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `scarto` varchar(255) COLLATE latin1_general_cs NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `bi_finanziamento` (
  `id` int(11) NOT NULL,
  `id_progetto` varchar(68) COLLATE latin1_general_cs NOT NULL,
  `id_a9` int(11) NOT NULL,
  `fontefinanziamento` varchar(3) COLLATE latin1_general_cs NOT NULL,
  `importofinanziamento` float NOT NULL,
  `numeronorma` int(11) NOT NULL,
  `annonorma` int(11) NOT NULL,
  `tiponorma` varchar(4) COLLATE latin1_general_cs NOT NULL,
  `numerodelcipe` int(11) NOT NULL,
  `annodelcipe` int(11) NOT NULL,
  `estremiprovvedimento` text COLLATE latin1_general_cs NOT NULL,
  `istatregione` varchar(2) COLLATE latin1_general_cs NOT NULL,
  `istatprovincia` varchar(3) COLLATE latin1_general_cs NOT NULL,
  `istatcomune` varchar(6) COLLATE latin1_general_cs NOT NULL,
  `descrizionesoggettoprivato` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `cfprivato` varchar(16) COLLATE latin1_general_cs NOT NULL,
  `criticita` text COLLATE latin1_general_cs NOT NULL,
  `presenzaeconomie` varchar(1) COLLATE latin1_general_cs NOT NULL,
  `cig` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `data_umod` varchar(20) COLLATE latin1_general_cs NOT NULL,
  `cup` varchar(10) COLLATE latin1_general_cs NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `bi_impegni` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_progetto` varchar(68) COLLATE latin1_general_cs NOT NULL,
  `id_a14` int(11) NOT NULL,
  `dataimpegno` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `codiceimpegno` varchar(20) COLLATE latin1_general_cs NOT NULL,
  `tipologiaimpegno` varchar(1) COLLATE latin1_general_cs NOT NULL COMMENT 'I: impegno  D: revoca',
  `importo` double NOT NULL,
  `note` text COLLATE latin1_general_cs NOT NULL,
  `id_cig` int(11) NOT NULL,
  `cig` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `data_umod` varchar(20) COLLATE latin1_general_cs NOT NULL,
  `cup` varchar(255) COLLATE latin1_general_cs NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `bi_log` (
  `id` int(11) NOT NULL,
  `dati` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `datalog` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `esito` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `nrrecord` int(11) NOT NULL,
  `tabsorgente` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `tabdestinataria` varchar(255) COLLATE latin1_general_cs NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `bi_pagamenti` (
  `id` int(11) NOT NULL,
  `id_progetto` varchar(68) COLLATE latin1_general_cs NOT NULL,
  `id_a15` int(11) NOT NULL,
  `datapagamento` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `codicepagamento` varchar(20) COLLATE latin1_general_cs NOT NULL,
  `tipopagamento` varchar(1) COLLATE latin1_general_cs NOT NULL,
  `importo` float NOT NULL,
  `causale` varchar(1) COLLATE latin1_general_cs NOT NULL,
  `note` text COLLATE latin1_general_cs NOT NULL,
  `codicegestionale` varchar(56) COLLATE latin1_general_cs NOT NULL,
  `progressivosal` int(11) NOT NULL,
  `cup` varchar(10) COLLATE latin1_general_cs NOT NULL,
  `cig` varchar(255) COLLATE latin1_general_cs DEFAULT NULL,
  `id_cig` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `data_umod` varchar(20) COLLATE latin1_general_cs NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `bi_quadroeconomico_dettaglio` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_progetto` varchar(68) COLLATE latin1_general_cs NOT NULL,
  `cup` varchar(68) COLLATE latin1_general_cs NOT NULL,
  `vocespesa` int(11) NOT NULL,
  `importo` float NOT NULL,
  `descrizione` text COLLATE latin1_general_cs NOT NULL,
  `aliquotaiva` float NOT NULL,
  `tipologiavocespesa` varchar(1) COLLATE latin1_general_cs NOT NULL,
  `id_a21` int(11) NOT NULL,
  `data_umod` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `descrizione_voce` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `tipo` varchar(255) COLLATE latin1_general_cs NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `c10_stepproceduraaggiudicazione` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `codice` int(11) NOT NULL,
  `descrizione` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `tipo` varchar(255) COLLATE latin1_general_cs NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `c11_tipoproceduraaggiudicazione` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `codice` int(11) NOT NULL,
  `descrizione` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `codice_descrizione_avcp` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `descrizione_avcp` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `codice_avcp` int(11) NOT NULL,
  `descrizione_simog` varchar(255) COLLATE latin1_general_cs NOT NULL COMMENT 'descrizione simog ScaltaContraenteType',
  `codice_simog` varchar(255) COLLATE latin1_general_cs NOT NULL COMMENT 'codice simog ScaltaContraenteType',
  `codice_smartcig` varchar(10) COLLATE latin1_general_cs NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `c12_tipologiadifinanziamento` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `codice` varchar(2) COLLATE latin1_general_cs NOT NULL,
  `descrizione` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `trashed` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `c13_fontedifinanziamento` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `codice` varchar(3) COLLATE latin1_general_cs NOT NULL,
  `descrizione` varchar(255) COLLATE latin1_general_cs NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `c14_vocidispesa` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `codice` int(11) NOT NULL,
  `descrizione` text COLLATE latin1_general_cs NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `c15_tipoentesiope` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `codice` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `descrizione` varchar(255) COLLATE latin1_general_cs NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `c16_codicefontedati` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `predef` int(11) NOT NULL DEFAULT '0' COMMENT '0 valore custom, 1 valore predefinito',
  `codicefontedati` varchar(255) COLLATE latin1_general_cs NOT NULL DEFAULT '',
  `descrizione` varchar(255) COLLATE latin1_general_cs NOT NULL DEFAULT '',
  `attivo` int(11) NOT NULL DEFAULT '0' COMMENT '0 non attivo; 1 attivo',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `c1_settorecpt` (
  `codice` varchar(2) COLLATE latin1_general_cs NOT NULL,
  `descrizione` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `id` int(11) NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `c2_intestaistituzionale` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `codice` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `descrizione` varchar(255) COLLATE latin1_general_cs NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `c3_strumentoattuativo` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `codice` varchar(4) COLLATE latin1_general_cs NOT NULL,
  `descrizione` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `tipo` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `responsabile` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `dataapprovazione` varchar(255) COLLATE latin1_general_cs NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `c4_tiponorma` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `codice` varchar(4) COLLATE latin1_general_cs NOT NULL,
  `descrizione` varchar(255) COLLATE latin1_general_cs NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `c5_indicatori` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `codice` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `descrizione` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `tipo` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `descrizionetipo` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `unitamisura` varchar(255) COLLATE latin1_general_cs NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `c6_fasiprocedurali` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `codice` varchar(3) COLLATE latin1_general_cs NOT NULL,
  `descrizione` varchar(255) COLLATE latin1_general_cs NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `c7_motivorevoca` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `codice` varchar(3) COLLATE latin1_general_cs NOT NULL,
  `descrizione` varchar(255) COLLATE latin1_general_cs NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `c8_formagiuridica` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `codice` varchar(6) COLLATE latin1_general_cs NOT NULL,
  `descrizione` varchar(255) COLLATE latin1_general_cs NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `c9_settoreattivitaeconomica` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `codice` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `descrizione` varchar(255) COLLATE latin1_general_cs NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cpv` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `codice` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `descrizione` varchar(255) COLLATE latin1_general_cs NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `crypt_pki` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_gara` int(11) NOT NULL,
  `pubkey` text COLLATE latin1_general_cs NOT NULL,
  `prikey` text COLLATE latin1_general_cs NOT NULL,
  `shapsw` text COLLATE latin1_general_cs NOT NULL COMMENT 'sha256 della passw inserita dal RUP',
  `data_umod` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `utente_umod` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `data_cre` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `utente_cre` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `validato` int(11) NOT NULL DEFAULT '-1' COMMENT 'pki validato -1=>NO  1=>SI',
  `data_valid` varchar(20) COLLATE latin1_general_cs NOT NULL,
  `utente_valid` varchar(50) COLLATE latin1_general_cs NOT NULL,
  PRIMARY KEY (`id`),
  KEY `crypt_pki_validato` (`validato`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `crypt_sim` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `simkey` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `psha` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `tipo` int(11) NOT NULL,
  `data_umod` varchar(50) COLLATE latin1_general_cs NOT NULL,
  `utente_umod` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `data_cre` varchar(50) COLLATE latin1_general_cs NOT NULL,
  `utente_cre` varchar(255) COLLATE latin1_general_cs NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cupweb` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `cup` text COLLATE latin1_general_cs,
  `cupprovvisorio` text COLLATE latin1_general_cs,
  `descrizione` text COLLATE latin1_general_cs,
  `annodecisione` text COLLATE latin1_general_cs,
  `master` text COLLATE latin1_general_cs,
  `statoprogetto` text COLLATE latin1_general_cs,
  `soggettorichiedente` text COLLATE latin1_general_cs,
  `concentratore` text COLLATE latin1_general_cs,
  `soggettotitolare` text COLLATE latin1_general_cs,
  `unitaorganizzativa` text COLLATE latin1_general_cs,
  `natura` text COLLATE latin1_general_cs,
  `tipologia` text COLLATE latin1_general_cs,
  `settore` text COLLATE latin1_general_cs,
  `sottosettore` text COLLATE latin1_general_cs,
  `categoria` text COLLATE latin1_general_cs,
  `cv1` text COLLATE latin1_general_cs,
  `cpv2` text COLLATE latin1_general_cs,
  `cpv3` text COLLATE latin1_general_cs,
  `cpv4` text COLLATE latin1_general_cs,
  `cpv5` text COLLATE latin1_general_cs,
  `cpv6` text COLLATE latin1_general_cs,
  `cpv7` text COLLATE latin1_general_cs,
  `cumulativo` text COLLATE latin1_general_cs,
  `cupmaster` text COLLATE latin1_general_cs,
  `leragionidelcollegamentoviamaster` text COLLATE latin1_general_cs,
  `localizzazioni` text COLLATE latin1_general_cs,
  `strutturainfrastrutturaunica` text COLLATE latin1_general_cs,
  `tipostrumentodiprogrammazione` text COLLATE latin1_general_cs,
  `descrizionestrumentodiprogrammazione` text COLLATE latin1_general_cs,
  `leggeobiettivo` text COLLATE latin1_general_cs,
  `ndeliberacipe` text COLLATE latin1_general_cs,
  `annodelibera` text COLLATE latin1_general_cs,
  `altro` text COLLATE latin1_general_cs,
  `codificalocale` text COLLATE latin1_general_cs,
  `partitaivacodicefiscale` text COLLATE latin1_general_cs,
  `sponsorizzazione` text COLLATE latin1_general_cs,
  `finanzadiprogetto` text COLLATE latin1_general_cs,
  `costoinmigliaiadieuro` text COLLATE latin1_general_cs,
  `tipologiacoperturafinanziaria` text COLLATE latin1_general_cs,
  `importodelfinanziamentoinmigliaiadieuro` text COLLATE latin1_general_cs,
  `sezioneateco2002` text COLLATE latin1_general_cs,
  `sottosezioneateco2002` text COLLATE latin1_general_cs,
  `divisioneateco2002` text COLLATE latin1_general_cs,
  `gruppoateco2002` text COLLATE latin1_general_cs,
  `sezioneateco2007` text COLLATE latin1_general_cs,
  `divisioneateco2007` text COLLATE latin1_general_cs,
  `gruppoateco2007` text COLLATE latin1_general_cs,
  `classeateco2007` text COLLATE latin1_general_cs,
  `categoriaateco2007` text COLLATE latin1_general_cs,
  `sottocategoriaateco2007` text COLLATE latin1_general_cs,
  `provvisorio` text COLLATE latin1_general_cs,
  `datagenerazione` text COLLATE latin1_general_cs,
  `dataultimamodifica` text COLLATE latin1_general_cs,
  `listaindicatoricodicedescrizionetipologia` text COLLATE latin1_general_cs,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `gara_aggiudicazione` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `importo_offerto` double NOT NULL COMMENT 'valore ereditato in automatico dalla graduatoria',
  `percentuale_ribasso` double NOT NULL COMMENT 'valore ereditato in automatico dalla graduatoria',
  `iva_tot` double NOT NULL,
  `oneri_sicurezza` double NOT NULL,
  `totalecontratto` double NOT NULL,
  `id_gara` int(11) NOT NULL,
  `id_lotto` int(11) NOT NULL COMMENT 'FK gara_lotto',
  `utente_umod` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `data_umod` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `id_tipo_impresa` int(11) NOT NULL COMMENT '1 singola   2 raggruppamennto    3 professionista',
  `id_operatore` int(11) NOT NULL,
  `verbale_agg_num` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `verbale_agg_data` varchar(10) COLLATE latin1_general_cs NOT NULL,
  `verbale_allegato` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `f_ric_subappalto` varchar(10) COLLATE latin1_general_cs NOT NULL COMMENT '''S''  ''N''''',
  `note` text COLLATE latin1_general_cs NOT NULL,
  `tipoatto` int(11) NOT NULL,
  `num_determina_aggiudicazione` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `data_determina_aggiudicazione` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `data_esecutivita_determina_aggiudicazione` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `prot_rich_subappalto` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `data_rich_subappalto` varchar(10) COLLATE latin1_general_cs NOT NULL,
  `data_scad_aut_subappalto` varchar(10) COLLATE latin1_general_cs NOT NULL,
  `autorizzazione_subappalto` varchar(10) COLLATE latin1_general_cs NOT NULL COMMENT 'S o N',
  `prot_risp_subappalto` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `data_risp_subappalto` varchar(10) COLLATE latin1_general_cs NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `gara_allegati` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_gara` int(11) NOT NULL COMMENT 'FK gara_datigenerali',
  `id_lotto` int(11) NOT NULL DEFAULT '-1' COMMENT 'FK gara_lotto  -1 no rif  != -1 FK',
  `model_sorgente` varchar(255) COLLATE latin1_general_cs NOT NULL COMMENT 'nameForm associato allegato',
  `id_sorgente` int(11) NOT NULL,
  `allegato` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `filename` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `size` int(11) NOT NULL COMMENT 'dimensione in byte del file',
  `preview` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `gestione` varchar(10) COLLATE latin1_general_cs NOT NULL,
  `utente_umod` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `data_umod` varchar(20) COLLATE latin1_general_cs NOT NULL,
  `allegatipdf` text COLLATE latin1_general_cs NOT NULL,
  PRIMARY KEY (`id`),
  KEY `id_gara` (`id_gara`,`id_lotto`,`model_sorgente`,`id_sorgente`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `gara_allegaticrypt` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_gara` int(11) NOT NULL,
  `id_lotto` int(11) NOT NULL,
  `id_offerta` int(11) NOT NULL,
  `id_tipo_impresa` int(11) NOT NULL COMMENT '1 lp_imprese; 2 lp_raggruppamento; 3 lp_professionisti',
  `id_operatore` int(11) NOT NULL,
  `tipodoc` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `contenitore_master` int(11) NOT NULL,
  `tipobusta` int(11) NOT NULL,
  `nomefileorig` varchar(1000) COLLATE latin1_general_cs NOT NULL,
  `pathorig` text COLLATE latin1_general_cs NOT NULL,
  `sha512fo` text COLLATE latin1_general_cs NOT NULL COMMENT 'impronta rilevata da FO',
  `simkey_id` int(11) NOT NULL,
  `sha512_repo` text COLLATE latin1_general_cs NOT NULL,
  `nomefile_repo` text COLLATE latin1_general_cs NOT NULL,
  `data_umod` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `utente_umod` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `data_decrypt` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `utente_decrypt` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `stato_decrypt` int(11) NOT NULL DEFAULT '-1' COMMENT '-1 NON DECIFRATO; -2 FILE UPLODATO E DECIFRATO 10 DECIFRATO',
  `trashed` int(11) NOT NULL,
  `note` text COLLATE latin1_general_cs NOT NULL,
  `id_gara_allegaticrypt` int(11) NOT NULL COMMENT 'id del file unzip',
  PRIMARY KEY (`id`),
  KEY `idx_id_offerta` (`id_offerta`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `gara_autorita` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `utente_umod` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `data_umod` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `id_gara` int(11) NOT NULL COMMENT 'fk gara_datigenerali',
  `id_lp_soggetti` int(11) NOT NULL,
  `id_lp_gara_tipo_incarico` int(11) NOT NULL,
  `dirittovoto` int(11) NOT NULL COMMENT '0: SI 1: no',
  `ordinevoto` int(11) NOT NULL COMMENT 'ordinamento in fase di votazione',
  `denominazionevoto` varchar(255) COLLATE latin1_general_cs NOT NULL COMMENT 'denominazione del commissario in fase di voto es. commissario 1 o commissario A',
  `note` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `id_lotto` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `gara_avvisibandi` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_gara` int(11) NOT NULL COMMENT 'FK gara_datigenerali',
  `utente_umod` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `data_umod` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `id_tipoformulario` int(11) NOT NULL COMMENT 'FK gara_formulari',
  `descrizione` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `descrizioneweb` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `pubblicazione_dataini` varchar(10) COLLATE latin1_general_cs NOT NULL COMMENT 'data inizio pubblicazione',
  `pubblicazione_datafin` varchar(10) COLLATE latin1_general_cs NOT NULL COMMENT 'data fine pubblicazione',
  `azionescadenza` int(11) NOT NULL COMMENT 'azione dopo scadenza  0 vedi in bandi scaduti    1 mantieni su bandi attivi   2 nascondi',
  `scopoavviso` int(11) NOT NULL COMMENT '0 NON DEFINITO    1 solo preinfo     2 ridurre tempi     3 avviso indizione gara',
  `accessoillimitato` int(11) NOT NULL COMMENT 'sez.1.3    1 Accesso illimitato    2 Accesso limitato',
  `urlaccesso` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `f_infoaltroindirizzo` varchar(10) COLLATE latin1_general_cs NOT NULL COMMENT '''S'' => si altro indirizzo   ''N'' => indirizzo sopraindicato sez 1.3-2',
  `id_lp_contatti_infoaltroindirizzo` int(11) NOT NULL COMMENT 'sez 1.3-2',
  `invioofferte_url` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `f_invioofferte_altroindirizzo` varchar(10) COLLATE latin1_general_cs NOT NULL COMMENT '''S''   ''N''  sez 1.3.-3',
  `luogo_consegna` text COLLATE latin1_general_cs NOT NULL COMMENT 'cambo testo per dire dove svolgere il servizio o la consegna dei beni',
  `id_lp_contatti_invioofferte_altroindirizzo` int(11) NOT NULL,
  `strumenti_url` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `data_ricezofferte` varchar(10) COLLATE latin1_general_cs NOT NULL COMMENT 'sez 4.2.2',
  `ora_ricezofferte` varchar(10) COLLATE latin1_general_cs NOT NULL COMMENT 'sez 4.2.2',
  `data_spedizinviti` varchar(10) COLLATE latin1_general_cs NOT NULL,
  `lingueofferte` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `data_valid_offerte` varchar(10) COLLATE latin1_general_cs NOT NULL COMMENT 'sez 4.2.6',
  `mesi_valid_offerte` int(11) NOT NULL COMMENT 'sez 4.2.6',
  `data_apertura_offerte` varchar(10) COLLATE latin1_general_cs NOT NULL,
  `ora_apertura_offerte` varchar(10) COLLATE latin1_general_cs NOT NULL,
  `luogo_apertura_offerte` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `note_apertura_offerte` text COLLATE latin1_general_cs NOT NULL,
  `data_pubb_bando` varchar(10) COLLATE latin1_general_cs NOT NULL COMMENT 'sez 2.3',
  `criteri_concprogettazione` text COLLATE latin1_general_cs NOT NULL COMMENT 'sez 3.1.10 criteri concorso progettazione',
  `f_profspecifica` varchar(10) COLLATE latin1_general_cs NOT NULL COMMENT '''S''    ''N''  sez 3.2.1',
  `descr_profspecifica` text COLLATE latin1_general_cs NOT NULL,
  `condiz_esecuzione` text COLLATE latin1_general_cs NOT NULL COMMENT 'sez 3.2.2',
  `f_personaleesecuzione` varchar(10) COLLATE latin1_general_cs NOT NULL COMMENT 'sez. 3.2.3',
  `data_avvio_procagg` varchar(10) COLLATE latin1_general_cs NOT NULL COMMENT 'sez 4.2.5',
  `f_ordinazelettronica` varchar(10) COLLATE latin1_general_cs NOT NULL COMMENT '''S''   ''N''',
  `f_fatturelettronica` varchar(10) COLLATE latin1_general_cs NOT NULL COMMENT '''S''   ''N''',
  `f_pagamelettronico` varchar(10) COLLATE latin1_general_cs NOT NULL COMMENT '''S''   ''N''',
  `info_complementari` text COLLATE latin1_general_cs NOT NULL,
  `data_spedizione_bandoavviso` varchar(10) COLLATE latin1_general_cs NOT NULL,
  `data_pubbprececedente` varchar(255) COLLATE latin1_general_cs NOT NULL COMMENT 'sez 4.2.1 rif. pubblicazione precedente per la stessa gara',
  `contatti_infoaltroindirizzo` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `contatti_invioofferte_altroindirizzo` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `f_pubblicaweb` varchar(10) COLLATE latin1_general_cs NOT NULL COMMENT 'S N',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `gara_categ_equivalenti` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `lp_categorie_opere_id` int(11) NOT NULL,
  `importo` double NOT NULL,
  `id_gara` int(11) NOT NULL COMMENT 'id tabella fk gara_datigenerali',
  `utente_umod` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `data_umod` varchar(10) COLLATE latin1_general_cs NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `gara_categorie_gara` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `lp_categorie_opere_id` int(11) NOT NULL,
  `importo` double NOT NULL,
  `prevalente` varchar(2) COLLATE latin1_general_cs NOT NULL COMMENT 'S=SI N=NO se lavoro è prevalente',
  `scorporabile` varchar(2) COLLATE latin1_general_cs NOT NULL COMMENT 'N=NO se lavoro è scorporabile',
  `perc_subappaltabile` double NOT NULL COMMENT '% subappaltabile',
  `perc_manodopera` double NOT NULL COMMENT '% di incicenza della manodopera',
  `importooperesub` double NOT NULL,
  `utente_umod` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `data_umod` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `id_sim_class_importo` int(11) NOT NULL,
  `id_gara` int(11) NOT NULL COMMENT 'FK gara_datigenerali',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `gara_commissione` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_gara` int(11) NOT NULL COMMENT 'FK gara_datigenerali',
  `cod_provenienza` int(11) NOT NULL COMMENT '0 NO   1 INTERNA    2 ANAC',
  `nomina_doc` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `nomina_proto_num` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `nomina_prodo_data` varchar(10) COLLATE latin1_general_cs NOT NULL,
  `richiesta_prot_num` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `richiesta_prot_data` varchar(10) COLLATE latin1_general_cs NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `gara_configurazione_ente` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_lp_amministrazioni_pubbliche` int(11) NOT NULL,
  `utente_software` varchar(255) COLLATE latin1_general_cs NOT NULL DEFAULT '',
  `autorizzazione` int(11) NOT NULL DEFAULT '0' COMMENT 'assume diversi valore per sire 0: non vede nulla 1: carica solo le gare e lotti 2: fa anche lo svolgimento',
  `utente_umod` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `data_umod` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `pubblicazione_singolo_ente` int(11) NOT NULL DEFAULT '0' COMMENT '0: ente non abilitato a pubblicare solo le sue gare 1: ente abilitato a pubblicare le sue gare',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `gara_configurazione_svolgimento` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `n_max_decimaliofferta` int(11) NOT NULL,
  `n_max_decimalicalcolo` int(11) NOT NULL,
  `tipo_ragguaglio` int(11) NOT NULL COMMENT '0: no ragguaglio 1: solo su foglie 2: su tutti i livelli',
  `data_umod` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `utente_umod` varchar(255) COLLATE latin1_general_cs NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `gara_consulenti` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_gara` int(11) NOT NULL COMMENT 'fk gara_datigenerali',
  `id_lp_soggetti` int(11) NOT NULL,
  `note` text COLLATE latin1_general_cs NOT NULL,
  `atto_numero` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `atto_data` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `id_lp_tipoattoamministrativo` varchar(10) COLLATE latin1_general_cs NOT NULL,
  `importo` double NOT NULL,
  `dataini` varchar(10) COLLATE latin1_general_cs NOT NULL,
  `datafin` varchar(10) COLLATE latin1_general_cs NOT NULL,
  `pubbdl33` varchar(10) COLLATE latin1_general_cs NOT NULL COMMENT '''S''  ''N''',
  `utente_umod` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `data_umod` varchar(255) COLLATE latin1_general_cs NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `gara_contratti_dati_ente` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `tipo_soggetto_rogante` int(11) NOT NULL COMMENT 'unimod_tipopu',
  `ufficio_entrate` varchar(255) COLLATE latin1_general_cs NOT NULL COMMENT 'nimod_ufficientrate',
  `cf_pu` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `denominazione_pu` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `prov_unimod` varchar(255) COLLATE latin1_general_cs NOT NULL COMMENT 'unimod_prov',
  `comune_unimod` varchar(255) COLLATE latin1_general_cs NOT NULL COMMENT 'unimod_comune',
  `indirizzo` varchar(1000) COLLATE latin1_general_cs NOT NULL,
  `cap` varchar(10) COLLATE latin1_general_cs NOT NULL,
  `email` varchar(400) COLLATE latin1_general_cs NOT NULL,
  `pec` varchar(400) COLLATE latin1_general_cs NOT NULL,
  `cf_pu_banca` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `cf_ente_intest_tesoreria` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `iban` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `abi` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `cab` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `cc` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `cin` varchar(10) COLLATE latin1_general_cs NOT NULL,
  `predefinito` varchar(11) COLLATE latin1_general_cs NOT NULL COMMENT 'indicata i dati predefiniti',
  `utente_umod` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `data_umod` varchar(255) COLLATE latin1_general_cs NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `gara_contratti_datigenerali` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_lotto` int(11) NOT NULL COMMENT 'FK gara_lotto',
  `id_gara` int(11) NOT NULL,
  `gestione` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `utente_umod` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `data_umod` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `progressivo` int(11) NOT NULL,
  `tipo_contratto` varchar(255) COLLATE latin1_general_cs NOT NULL COMMENT 'tab unimod_tipotitolo',
  `id_tipo_impresa` int(11) NOT NULL COMMENT '1: singola 2: raggruppamento 3: professionista',
  `id_operatore` int(11) NOT NULL,
  `repertorio_num` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `repertorio_anno` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `data_atto` varchar(10) COLLATE latin1_general_cs NOT NULL,
  `data_stipula` varchar(10) COLLATE latin1_general_cs NOT NULL,
  `data_esecutivita` varchar(10) COLLATE latin1_general_cs NOT NULL,
  `data_termine_consegna` varchar(10) COLLATE latin1_general_cs NOT NULL,
  `data_scadenza_comunic_stipula` varchar(10) COLLATE latin1_general_cs NOT NULL,
  `id_soggetto_rogante` int(11) NOT NULL COMMENT 'lp_soggetti',
  `tipo_soggetto_rogante` int(11) NOT NULL COMMENT 'pk unimod_tipopu',
  `tipo_pubblico_ufficiale` int(11) NOT NULL COMMENT 'tab unimod_qualificarappresentante',
  `id_soggetto_firmatarioente` int(11) NOT NULL COMMENT 'lp_soggetti',
  `tipo_soggetto_firmatario` int(11) NOT NULL COMMENT 'pk unimod_qualificarappresentante',
  `id_firmatario_impresa` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `id_multipli_firmatari_impresa` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `tipo_soggetto_impresa` int(11) NOT NULL,
  `istat_sede_stipula` varchar(10) COLLATE latin1_general_cs NOT NULL,
  `registrazione_num` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `registrazione_data` varchar(10) COLLATE latin1_general_cs NOT NULL,
  `registrazione_serie` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `registrazione_volume` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `registrazione_luogo_istat` varchar(10) COLLATE latin1_general_cs NOT NULL,
  `id_ente` int(11) NOT NULL COMMENT 'lp_amministrazionipubbliche',
  `id_negozio_codice` varchar(255) COLLATE latin1_general_cs NOT NULL COMMENT 'codice negozio giuridico',
  `id_negozio_descrizione` varchar(255) COLLATE latin1_general_cs NOT NULL COMMENT 'cmb',
  `f_garanzia_debito` varchar(10) COLLATE latin1_general_cs NOT NULL COMMENT '''S''   ''N''',
  `f_immobile_strumentale` varchar(10) COLLATE latin1_general_cs NOT NULL COMMENT '''S''  ''N''''',
  `f_esente` varchar(10) COLLATE latin1_general_cs NOT NULL COMMENT '''S''   ''N''',
  `f_soggetto_iva` varchar(10) COLLATE latin1_general_cs NOT NULL COMMENT '''S''   ''N''',
  `f_sospeso` varchar(10) COLLATE latin1_general_cs NOT NULL COMMENT '''S''   ''N''',
  `importo` double NOT NULL,
  `iva_tot` double NOT NULL COMMENT 'tot. iv ain tutti i lotti compresi nel contratto',
  `oneri_sicurezza` double NOT NULL COMMENT 'tot. oneri sicurezza compresi nel contratto',
  `id_lp_polizzeassicurative` int(11) NOT NULL COMMENT 'fk lp_polizzeassicurative   CAUZIONE DEFINITIVA',
  `importo_cauzione_def` double NOT NULL COMMENT 'letto in automatico dalla selzione della polizza',
  `oggetto` text COLLATE latin1_general_cs NOT NULL,
  `note` text COLLATE latin1_general_cs NOT NULL,
  `duratacontratto_giorni` int(11) NOT NULL,
  `datafinecontratto` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `id_gara_aggiudicazione` int(11) NOT NULL COMMENT 'fk gara_aggiudicazione',
  `naturaatto` int(11) NOT NULL COMMENT 'metadati parer es. appalto, locazione ecc',
  `cig` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `cup` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `previstaregistrazione` int(11) NOT NULL DEFAULT '0' COMMENT '0: no 1: SI',
  `datainvioae` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `st_struttura` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `nfacciate` int(11) NOT NULL DEFAULT '0' COMMENT 'nr facciate del contratto',
  `costofacciata` double NOT NULL,
  `calcolodirittirogito` text COLLATE latin1_general_cs NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `gara_contratti_documenti` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_lotto` int(11) NOT NULL COMMENT 'FK gara_lotto',
  `id_gara_contratti_datigenerali` int(11) NOT NULL,
  `utente_umod` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `data_umod` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `id_gara` int(11) NOT NULL,
  `tipoallegato` varchar(255) COLLATE latin1_general_cs NOT NULL COMMENT 'tabella unimod + testo atto',
  `numero_allegati` int(11) NOT NULL,
  `descrizione` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `testoatto` text COLLATE latin1_general_cs NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `gara_contratti_imponibile` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_gara_contratti_datigenerali` int(11) NOT NULL,
  `id_gara_contratti_registrazione_negozio` int(11) NOT NULL,
  `codicenegozio` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `garanzia_per_debito` varchar(10) COLLATE latin1_general_cs NOT NULL DEFAULT '0' COMMENT 'S o N',
  `immobile_strumentale` varchar(10) COLLATE latin1_general_cs NOT NULL DEFAULT '0',
  `esente` varchar(10) COLLATE latin1_general_cs NOT NULL DEFAULT '0',
  `soggetto_iva` varchar(10) COLLATE latin1_general_cs NOT NULL DEFAULT '0',
  `sospeso` varchar(10) COLLATE latin1_general_cs NOT NULL DEFAULT '0',
  `tipo_dante` int(11) NOT NULL COMMENT 'assume 0 se è l''ente, 1 se è impresa 2: raggruppamento e 3: se professionisti',
  `dante` int(11) NOT NULL,
  `tipo_avente` int(11) NOT NULL COMMENT 'assume 0 se è l''ente, 1 se è impresa 2: raggruppamento e 3: se professionisti',
  `avente` int(11) NOT NULL,
  `data_umod` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `utente_umod` varchar(255) COLLATE latin1_general_cs NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `gara_contratti_istanze` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `idpadre` int(11) NOT NULL,
  `idpadre_template` int(11) NOT NULL,
  `id_gara_contratti_datigenerali` int(11) NOT NULL,
  `id_row_tabella` int(11) NOT NULL COMMENT 'id dela tabella gara_contratti specifica a contenere l''informazione di quesl livello',
  `descrizione` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `livello` int(11) NOT NULL,
  `trashed` int(11) NOT NULL,
  `codice` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `tabella` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `datavalidita` varchar(10) COLLATE latin1_general_cs NOT NULL,
  `ordine` int(11) NOT NULL,
  `f_foglia` int(11) NOT NULL COMMENT '0: ramo 1: foglia',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `gara_contratti_lotti` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_gara_contratti_datigenerali` int(11) NOT NULL,
  `id_lotto` int(11) NOT NULL,
  `id_gara` int(11) NOT NULL,
  `cup` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `cig` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `importo` double NOT NULL,
  `utente_umod` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `data_umod` varchar(10) COLLATE latin1_general_cs NOT NULL,
  `iva_tot` double NOT NULL,
  `oneri_sicurezza` double NOT NULL,
  `importo_tot` double NOT NULL,
  `oggetto` text COLLATE latin1_general_cs NOT NULL,
  `percentuale_ribasso` double NOT NULL,
  `id_gara_aggiudicazione` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `gara_contratti_registrazione_negozio` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_gara_contratti_datigenerali` int(11) NOT NULL,
  `identificativo` int(11) NOT NULL,
  `valorenegozio` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `annotazioni` varchar(1000) COLLATE latin1_general_cs NOT NULL,
  `data_umod` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `utente_umod` varchar(255) COLLATE latin1_general_cs NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `gara_contratti_tassazione` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_lotto` int(11) NOT NULL COMMENT 'FK gara_lotto',
  `id_gara_contratti_datigenerali` int(11) NOT NULL,
  `utente_umod` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `data_umod` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `id_gara_contratti` int(11) NOT NULL,
  `progressivo` int(11) NOT NULL,
  `codice_tributo` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `aliquota` double NOT NULL,
  `importo` double NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `gara_contratti_tassazione_entrate` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_gara_contratti_datigenerali` int(11) NOT NULL,
  `id_gara_contratti_registrazione_negozio` int(11) NOT NULL,
  `id_gara_contratti_imponibile` int(11) NOT NULL,
  `codice_tributo` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `importo` varchar(225) COLLATE latin1_general_cs NOT NULL,
  `progressivo` int(11) NOT NULL,
  `aliquota` int(11) NOT NULL,
  `data_umod` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `utente_umod` varchar(255) COLLATE latin1_general_cs NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `gara_contratti_template` (
  `id` int(11) NOT NULL,
  `idpadre` int(11) NOT NULL,
  `ambito` char(1) COLLATE latin1_general_cs NOT NULL,
  `descrizione` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `livello` int(11) NOT NULL,
  `trashed` int(11) NOT NULL,
  `codice` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `tabella` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `datavalidita` varchar(10) COLLATE latin1_general_cs NOT NULL,
  `ordine` int(11) NOT NULL,
  `f_foglia` int(11) NOT NULL COMMENT '0: ramo 1: foglia',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `gara_criteri_calcolo_anomalie` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `criterio` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `dettagliocriterio` text COLLATE latin1_general_cs NOT NULL,
  `tipocriterio` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `versione` varchar(255) COLLATE latin1_general_cs NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `gara_criteridiscelta` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `codice` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `descrizione` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `descrizione_breve` varchar(255) COLLATE latin1_general_cs NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `gara_datacambiofase` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_gara` int(11) NOT NULL,
  `id_lotto` int(11) NOT NULL,
  `utente_umod` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `data_umod` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `fase` int(11) NOT NULL COMMENT 'corrisponde a idstato in cui si arriva',
  `dataini` varchar(20) COLLATE latin1_general_cs NOT NULL,
  `dataend` varchar(20) COLLATE latin1_general_cs NOT NULL,
  PRIMARY KEY (`id`),
  KEY `id_lotto` (`id_lotto`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `gara_datigenerali` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uso_asta_elettronica` varchar(10) COLLATE latin1_general_cs NOT NULL COMMENT 'sezione 4.1.6',
  `note_asta_elettronica` text COLLATE latin1_general_cs NOT NULL COMMENT 'sezione 4.1.6',
  `utente_umod` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `data_umod` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `gestione` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `oggetto` varchar(1024) COLLATE latin1_general_cs NOT NULL,
  `id_stazione_appaltante` varchar(40) COLLATE latin1_general_cs NOT NULL,
  `denom_stazione_appaltante` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `cf_amministrazione` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `denom_amministrazione` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `cf_utente` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `id_gara` int(11) NOT NULL,
  `id_scelta_contraente` varchar(255) COLLATE latin1_general_cs NOT NULL COMMENT 'codice simog riportato',
  `importo_gara` double NOT NULL DEFAULT '0',
  `iva_tot` double NOT NULL COMMENT 'importo totale dell''IVA sulla gara',
  `oneri_sicurezza` double NOT NULL COMMENT 'tot. oneri della sicurezza su tutta la gara',
  `importo_sa_gara` double NOT NULL,
  `data_cancellazione_gara` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `data_termine_pagamento` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `data_comun` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `data_inib_pagam` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `data_conferma_gara` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `tipo_scheda` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `modo_indizione` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `modo_realizzazione` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `id_motivazione` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `note_canc` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `cig_acc_quadro` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `numero_lotti` int(11) NOT NULL,
  `data_creazione` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `id_osservatorio` varchar(1000) COLLATE latin1_general_cs NOT NULL,
  `id_stato_gara` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `data_perfezionamento_bando` int(11) NOT NULL,
  `urgenza_dl133` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `provv_presa_carico` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `motivo_rich_cig_comuni` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `motivo_rich_cig` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `escluso_avcpass` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `pubblicaweb` varchar(10) COLLATE latin1_general_cs NOT NULL,
  `amm_agg_tiposorgente` int(11) NOT NULL COMMENT '0 non definito 1 lp_configurazioneente  2 lp_amministrazioni_pubbliche',
  `amm_agg_id_rec` int(11) NOT NULL COMMENT 'id record',
  `commitente_tiposorgente` int(11) NOT NULL,
  `amm_committente_id_rec` int(11) NOT NULL,
  `f_lotti` int(11) NOT NULL COMMENT 'flag 0 NO 1 SI',
  `num_offerte_lotti` int(11) NOT NULL COMMENT '0 tutti i lotti   1 solo uno   != max num lotti',
  `num_lotti_offerente` int(11) NOT NULL COMMENT '0 non impostato  != valore',
  `note_proc_ricorso` text COLLATE latin1_general_cs NOT NULL,
  `id_contatto_ricorso` int(11) NOT NULL COMMENT 'id lp_contatti_ente',
  `id_contatto_mediazione` int(11) NOT NULL,
  `id_contatto_info_ricorso` int(11) NOT NULL,
  `data_sped_avviso` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `note` text COLLATE latin1_general_cs NOT NULL,
  `id_contatto_chiarimenti` int(11) NOT NULL COMMENT 'richiesta info-chiarimenti',
  `accordo_aapp` varchar(10) COLLATE latin1_general_cs NOT NULL COMMENT 'S oN sezione 4.1.8',
  `req_albo` text COLLATE latin1_general_cs NOT NULL COMMENT 'sezione 3.1.1',
  `f_cap_economica` varchar(10) COLLATE latin1_general_cs NOT NULL COMMENT 'S o N  sezione 3.1.2',
  `criteri_capeconomica` text COLLATE latin1_general_cs NOT NULL COMMENT 'sezione 3.1.2',
  `livelli_capeconomica` text COLLATE latin1_general_cs NOT NULL COMMENT 'sezione 3.1.2',
  `f_cap_professionale` varchar(10) COLLATE latin1_general_cs NOT NULL COMMENT 'S o N  sezione 3.1.3',
  `criteri_cap_professionale` text COLLATE latin1_general_cs NOT NULL COMMENT 'sezione 3.1.3',
  `livelli_capprofessionale` text COLLATE latin1_general_cs NOT NULL COMMENT 'sezione 3.1.3',
  `f_limitato_integsociale` varchar(10) COLLATE latin1_general_cs NOT NULL COMMENT 'S o N  sezione 3.1.5',
  `f_esecuz_integ_sociale` varchar(10) COLLATE latin1_general_cs NOT NULL COMMENT 'S oN  sezione 3.1.5',
  `id_dirigenteArea` int(11) NOT NULL COMMENT 'fk id_lp_soggetti',
  `id_rup` int(11) NOT NULL COMMENT 'fk id_lp_soggetti',
  `stato_gara` int(11) NOT NULL COMMENT '1:in definizione 2:pubblicata 3:agg. provvisoria 4: agg. dafinitiva 5: revocata 6: archiviata',
  `id_tipoattoamministrativo` int(11) NOT NULL COMMENT 'tipo atto lp_tipoattoamministrativo',
  `dataatto` varchar(10) COLLATE latin1_general_cs NOT NULL,
  `nratto` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `altri_importi` double NOT NULL,
  `st_struttura` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `garatelematica` varchar(10) COLLATE latin1_general_cs NOT NULL COMMENT 'S: SI N:NO',
  `id_crypt_pki` int(11) NOT NULL,
  `data_ins` varchar(10) COLLATE latin1_general_cs NOT NULL,
  `utente_ins` varchar(50) COLLATE latin1_general_cs NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `gara_documentirichiesti` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_gara` int(11) NOT NULL COMMENT 'FK gara_datigenerali',
  `id_gara_avvisibandi` int(11) NOT NULL,
  `id_lotto` int(11) NOT NULL DEFAULT '-1' COMMENT '-1 solo della gara    != -1   associato ad un lotto',
  `utente_umod` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `data_umod` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `fasedocumento` int(11) NOT NULL COMMENT '1  Amministrativo     2 Tecnico',
  `descrizione` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `ordine` int(11) NOT NULL,
  `f_pubblicaweb` varchar(10) COLLATE latin1_general_cs NOT NULL COMMENT 'S  N',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `gara_elaboratigara` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_gara` int(11) NOT NULL COMMENT 'FK gara_datigenerali',
  `id_gara_avvisibandi` int(11) NOT NULL COMMENT 'FK gara_avvisibandi',
  `id_lotto` int(11) NOT NULL DEFAULT '-1' COMMENT '-1 solo della gara    != -1   associato ad un lotto',
  `utente_umod` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `data_umod` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `fasedocumento` int(11) NOT NULL COMMENT '1  Amministrativo     2 Tecnico',
  `descrizione` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `ordine` int(11) NOT NULL,
  `f_pubblicaweb` varchar(10) COLLATE latin1_general_cs NOT NULL COMMENT 'S  N',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `gara_elementivalutazione` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_gara` int(11) NOT NULL,
  `id_lotto` int(11) NOT NULL,
  `id_padre` int(11) NOT NULL,
  `livello` int(11) NOT NULL,
  `ordine` int(11) NOT NULL,
  `codice` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `descrizione` text COLLATE latin1_general_cs NOT NULL,
  `peso` double NOT NULL,
  `base_gara` double NOT NULL,
  `tipo_elemento` int(11) NOT NULL,
  `criterio` int(11) NOT NULL,
  `dett_criterio` int(11) NOT NULL COMMENT 'modo_inserimento',
  `note` text COLLATE latin1_general_cs NOT NULL,
  `utente_umod` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `data_umod` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `sottocriteri` int(11) NOT NULL DEFAULT '0' COMMENT '0: non sono presenti sottocriteri  1: sono presenti sottocriteri',
  `riparametrazione` int(11) NOT NULL DEFAULT '0' COMMENT '=. No riparametraz. 1: riparametraz',
  `tipoformula` int(11) NOT NULL DEFAULT '-1',
  `param_soglia` double NOT NULL,
  `param_sogliamin` double NOT NULL,
  `param_alfa` float NOT NULL,
  `param_n` float NOT NULL,
  `param_k` float NOT NULL,
  `param_m` float NOT NULL,
  `qtavoce` float NOT NULL,
  `um` varchar(100) COLLATE latin1_general_cs NOT NULL,
  `importomin` float NOT NULL DEFAULT '-1',
  `importomax` float NOT NULL DEFAULT '-1',
  `f_valorecontratto` int(11) NOT NULL DEFAULT '-1' COMMENT 'flag x criterio che definisce valore economico contratto (prezzo o ribasso)',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `gara_formulari` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `codice` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `descrizione` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `normariferimento` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `enteemittente` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `datavalidoini` varchar(10) COLLATE latin1_general_cs NOT NULL,
  `datavalidofin` varchar(10) COLLATE latin1_general_cs NOT NULL,
  `xsdvalidazione` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `pdfmodello` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `pdfistruzioni` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `trashed` int(11) NOT NULL COMMENT '1:  cancellato da non far vedere',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `gara_graduatoria` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_lotto` int(11) NOT NULL COMMENT 'FK gara_lotto',
  `utente_umod` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `data_umod` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `id_tipo_impresa` int(11) NOT NULL COMMENT '1 singola   2 raggruppamennto    3 professionista',
  `id_operatore` int(11) NOT NULL,
  `id_gara_offerta` int(11) NOT NULL COMMENT 'FK gara_offertericevute',
  `importo_offerto` double NOT NULL,
  `percentuale_ribasso` double NOT NULL,
  `punteggio_offerta` double NOT NULL,
  `punteggio_tecnico` float NOT NULL,
  `punteggio_economico` float NOT NULL,
  `note` text COLLATE latin1_general_cs NOT NULL,
  `posizione` int(11) NOT NULL,
  `f_anomalia` varchar(10) COLLATE latin1_general_cs NOT NULL COMMENT '''S''   ''N''  NO INTERFACCIA UTENTE',
  `f_aggiudicatario` int(11) NOT NULL DEFAULT '0' COMMENT '0 NO     1  PROVVISIORIO     2 DEFINTIVO',
  `f_taglioali` varchar(10) COLLATE latin1_general_cs NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `gara_incaricati` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_gara` int(11) NOT NULL COMMENT 'FK gara_datigenerali',
  `utente_umod` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `data_umod` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `id_lp_soggetti` int(11) NOT NULL,
  `id_ruolo` int(11) NOT NULL COMMENT 'sim_ruoloresponsabile',
  `id_sezione` int(11) NOT NULL COMMENT 'sim_sezione',
  `persona_fisica` varchar(10) COLLATE latin1_general_cs NOT NULL COMMENT '''S''   ''N''',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `gara_invitati` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_gara` int(11) NOT NULL COMMENT 'fk gara_datigenerali',
  `id_gara_avvisibandi` int(11) NOT NULL COMMENT 'FK gara_datigenerali',
  `utente_umod` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `data_umod` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `id_tipo_impresa` int(11) NOT NULL COMMENT '1 singola   2 raggruppamennto    3 professionista',
  `id_operatore` int(11) NOT NULL,
  `invito_prot_num` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `invito_prot_data` varchar(10) COLLATE latin1_general_cs NOT NULL,
  `note` text COLLATE latin1_general_cs NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `gara_log_cambiostatogara` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_stato_prec` int(11) NOT NULL,
  `id_stato_attuale` int(11) NOT NULL,
  `utente_umod` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `data_umod` varchar(20) COLLATE latin1_general_cs NOT NULL,
  `id_gara` int(11) NOT NULL,
  `id_lotto` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `gara_lotto` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `utente_umod` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `data_umod` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `id_gara` int(11) NOT NULL COMMENT 'chiave della tab fk gara_datigenerali',
  `gestione` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `id_gara_simog` int(11) NOT NULL,
  `cig` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `oggetto` varchar(1024) COLLATE latin1_general_cs NOT NULL,
  `somma_urgenza` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `importo_lotto` double NOT NULL DEFAULT '0',
  `iva_tot` double NOT NULL COMMENT 'tot.iva sul lotto',
  `importo_sa` double NOT NULL DEFAULT '0',
  `importo_impresa` double NOT NULL DEFAULT '0',
  `cpv` varchar(12) COLLATE latin1_general_cs NOT NULL,
  `id_categoria_prevalente` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `data_pubblicazione` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `data_scadenza_pagamenti` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `data_comunicazione` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `data_inib_pagamento` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `id_motivazione` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `note_canc` varchar(1000) COLLATE latin1_general_cs NOT NULL,
  `tipo_contratto` varchar(255) COLLATE latin1_general_cs NOT NULL COMMENT 'lavoro servizi forniture',
  `flag_escluso` varchar(255) COLLATE latin1_general_cs NOT NULL COMMENT 'S è escluso un contratto va indicato id_esclusione campo sotto',
  `id_esclusione` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `luogo_istat` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `luogo_nuts` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `importo_attuazione_sicurezza` double NOT NULL DEFAULT '0',
  `triennio_anno_inizio` int(11) NOT NULL,
  `triennio_anno_fine` int(11) NOT NULL,
  `triennio_progressivo` int(11) NOT NULL,
  `annuale_cui_mininf` int(11) NOT NULL,
  `data_creazione_lotto` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `data_cancellazione_lotto` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `stato_avcpass` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `ora_scadenza` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `flag_ripetizione` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `flag_prevede_rip` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `data_scadenza_richiesta_invito` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `flag_cup` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `data_lettera_invito` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `cig_orgine_rip` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `f_cupvalido` varchar(10) COLLATE latin1_general_cs NOT NULL COMMENT '''S''   ''N''',
  `f_cup_utentevalido` varchar(10) COLLATE latin1_general_cs NOT NULL COMMENT '''S''  ''N''',
  `cup_idrichiesta` int(11) NOT NULL,
  `cup_dipe` text COLLATE latin1_general_cs NOT NULL,
  `cup` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `id_a0` int(11) NOT NULL,
  `id_a21` int(11) NOT NULL COMMENT 'progetto in gara',
  `id_rup` int(11) NOT NULL COMMENT 'lp_soggetti derivato da a21',
  `id_criteriagg` int(11) NOT NULL,
  `max_pt_tecnico` double NOT NULL COMMENT 'da utilizzre nel criterio economicamente + vantaggiosa',
  `max_pt_economico` double NOT NULL COMMENT 'da utilizzre nel criterio economicamente + vantaggiosa',
  `durata_mesi` int(11) NOT NULL,
  `durata_giorni` int(11) NOT NULL,
  `durata_dataini` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `durata_datafin` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `rinnovi_descr` text COLLATE latin1_general_cs NOT NULL,
  `codice_strumento_simog` varchar(10) COLLATE latin1_general_cs NOT NULL COMMENT 'decod. sim_tipostrumenti',
  `f_accordo_quadro` varchar(10) COLLATE latin1_general_cs NOT NULL COMMENT '''S''   ''N''',
  `tipo_prestazione_simog` varchar(255) COLLATE latin1_general_cs NOT NULL COMMENT 'decod. sim_tipoprestazione',
  `f_preinformazione` varchar(10) COLLATE latin1_general_cs NOT NULL COMMENT '''S''   ''N''',
  `f_proc_accreditamento` varchar(10) COLLATE latin1_general_cs NOT NULL COMMENT '''S''   ''N''',
  `f_termine_ridotto` varchar(10) COLLATE latin1_general_cs NOT NULL COMMENT '''S''   ''N''',
  `importo_finanziamento` double NOT NULL,
  `tipo_finanziamento` varchar(255) COLLATE latin1_general_cs NOT NULL COMMENT 'decod. sim_tipofinanziamento',
  `cigKKK` varchar(255) COLLATE latin1_general_cs NOT NULL COMMENT 'codici di controllo di SIMOG DOPO il rilascio del CIG',
  `calcolo_soglia_anomalia` varchar(10) COLLATE latin1_general_cs NOT NULL COMMENT 'S o N',
  `esclusione_automatica` varchar(10) COLLATE latin1_general_cs NOT NULL COMMENT 'S o N',
  `stato_lotto` int(11) NOT NULL COMMENT 'stato del lotto',
  `n_max_decimaliofferta` int(11) NOT NULL,
  `n_max_decimalicalcolo` int(11) NOT NULL,
  `tipo_ragguaglio` int(11) NOT NULL COMMENT '0: no ragguaglio 1: solo su foglie 2: su tutti i livelli',
  `altri_importi` double NOT NULL,
  `oepv_idcriterio_valorecontratto` int(11) NOT NULL,
  `data_ins` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `utente_ins` varchar(255) COLLATE latin1_general_cs NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `gara_nuts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `codice` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `descrizione` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `trashed` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `gara_oepv_valutazione` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `idcriterio` int(11) NOT NULL,
  `idcommissario` int(11) NOT NULL,
  `idofferta` int(11) NOT NULL,
  `idoffertaB` int(11) NOT NULL,
  `valore` double NOT NULL,
  `voto` double NOT NULL,
  `prefcoppie` int(11) NOT NULL COMMENT '0-> no pref, 1-> pref offerta 1, 2->pref offerta 2',
  `ribasso` double NOT NULL DEFAULT '-1',
  `importo` double NOT NULL DEFAULT '-1',
  PRIMARY KEY (`id`),
  KEY `idcriterio` (`idcriterio`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `gara_oepv_valutazione_albero` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `idcriteriopadre` int(11) NOT NULL,
  `idcriterio` int(11) NOT NULL,
  `idofferta` int(11) NOT NULL,
  `punteggio` double NOT NULL,
  `valore_g` double NOT NULL COMMENT 'PE criterio 100 (P) - QTA x prezzo',
  PRIMARY KEY (`id`),
  KEY `idx2` (`idcriteriopadre`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `gara_oepv_valutazione_calc` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `idcriterio` int(11) NOT NULL,
  `idofferta` int(11) NOT NULL,
  `valore_a` double NOT NULL COMMENT 'media',
  `valore_b` double NOT NULL COMMENT 'coeff norm o raggu',
  `valore_c` double NOT NULL COMMENT 'peso x B',
  `valore_d` double NOT NULL COMMENT 'somma xi !foglia',
  `valore_g` double NOT NULL COMMENT 'criterio 101 (P) - QTA x prezzo',
  `valore_h` double NOT NULL COMMENT 'somma valoreG',
  `valore_k` double NOT NULL,
  `valore_w` double NOT NULL COMMENT 'coeff formula PE da * peso',
  `f_criteriofoglia` tinyint(4) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx2` (`idcriterio`,`idofferta`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `gara_oepv_valutazione_daticalc` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `idcriterio` int(11) NOT NULL,
  `a_max` double NOT NULL COMMENT 'MAX del valore_A usato per la riparametrazione',
  `nrecofferte` int(11) NOT NULL COMMENT 'num rec valutazione_calc rilevate dalla funzione di calcolo della struttura',
  `stato` int(11) NOT NULL COMMENT 'blank-> non elaborato, 1-> OK, 2-> dati non completi, 3->dato variato da ultima elaborazione',
  `debug` varchar(1000) COLLATE latin1_general_cs NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `gara_offertericevute` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_gara` int(11) NOT NULL COMMENT 'FK gara_datigenerali',
  `id_lotto` int(11) NOT NULL,
  `utente_umod` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `data_umod` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `id_tipo_impresa` int(11) NOT NULL COMMENT '1 singola   2 raggruppamennto    3 professionista',
  `id_operatore` int(11) NOT NULL,
  `soggetto50id` int(11) NOT NULL,
  `itecod` varchar(20) COLLATE latin1_general_cs NOT NULL DEFAULT '-1' COMMENT 'ITECOD procedimento',
  `ricnum` varchar(20) COLLATE latin1_general_cs NOT NULL DEFAULT '-1' COMMENT 'RICNUM richiesta',
  `offerta_prot_num` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `offerta_prot_data` varchar(10) COLLATE latin1_general_cs NOT NULL,
  `dataarrivo` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `oraarrivo` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `posizionearrivo` int(11) NOT NULL,
  `f_ammessa` varchar(10) COLLATE latin1_general_cs NOT NULL COMMENT '''S''   ''N''',
  `motivoesclusione` text COLLATE latin1_general_cs NOT NULL,
  `note` text COLLATE latin1_general_cs NOT NULL,
  `fuoritempo` varchar(255) COLLATE latin1_general_cs NOT NULL COMMENT 'S: busta arrivata dopo l''orario N: arrivata in orario',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `gara_pubblicazioni_avvisibandi` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_gara` int(11) NOT NULL COMMENT 'FK gara_datigenerali',
  `id_gara_avvisibandi` int(11) NOT NULL,
  `utente_umod` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `data_umod` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `tipobando` int(11) NOT NULL COMMENT '1  indizione   2 esito',
  `data_guce` varchar(10) COLLATE latin1_general_cs NOT NULL,
  `numero_guce` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `data_bore` varchar(10) COLLATE latin1_general_cs NOT NULL,
  `numero_bore` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `data_guri` varchar(10) COLLATE latin1_general_cs NOT NULL,
  `numero_guri` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `data_albo` varchar(10) COLLATE latin1_general_cs NOT NULL,
  `f_benicul` varchar(10) COLLATE latin1_general_cs NOT NULL COMMENT '''S''   ''N''',
  `f_sospeso` varchar(10) COLLATE latin1_general_cs NOT NULL COMMENT '''S''  ''N''',
  `urlsito` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `periodici` int(11) NOT NULL,
  `quotidiani_naz` int(11) NOT NULL,
  `quotidiani_reg` int(11) NOT NULL,
  `f_profilo_committente` varchar(10) COLLATE latin1_general_cs NOT NULL COMMENT '''S''  ''N''',
  `f_mit` varchar(10) COLLATE latin1_general_cs NOT NULL COMMENT '''S''   ''N''',
  `f_osservatorio` varchar(10) COLLATE latin1_general_cs NOT NULL COMMENT '''S''    ''N''',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `gara_r_commissione_soggetti` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_gara` int(11) NOT NULL COMMENT 'FK gara_datigenerali',
  `id_commissione` int(11) NOT NULL COMMENT 'FK gara_commissione',
  `sorteggiato` int(11) NOT NULL DEFAULT '-1' COMMENT '-1 non sorteggiato    nn ordine di sorteggio',
  `f_presidente` varchar(255) COLLATE latin1_general_cs NOT NULL COMMENT '''S''   ''N''',
  `id_soggetto` int(11) NOT NULL COMMENT 'FK',
  `utente_umod` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `data_umod` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `gestione` varchar(255) COLLATE latin1_general_cs NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `gara_richiestapartecipazione` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_gara` int(11) NOT NULL COMMENT 'FK gara_datigenerali',
  `id_gara_avvisibandi` int(11) NOT NULL,
  `utente_umod` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `data_umod` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `id_tipo_impresa` int(11) NOT NULL COMMENT '1 singola   2 raggruppamennto    3 professionista',
  `id_operatore` int(11) NOT NULL,
  `richiesta_prot_num` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `richiesta_prot_data` varchar(10) COLLATE latin1_general_cs NOT NULL,
  `f_ammessa` varchar(10) COLLATE latin1_general_cs NOT NULL COMMENT '''S''   ''N''',
  `motivoesclusione` text COLLATE latin1_general_cs NOT NULL,
  `note` text COLLATE latin1_general_cs NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `gara_sedute` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_gara` int(11) NOT NULL COMMENT 'fk gara_datigenerali',
  `utente_umod` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `data_umod` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `id_tipo_seduta` int(11) NOT NULL COMMENT '1 unica  2  Amministrativa   3  Tecnica  4 Economica   5 Quantitativi',
  `f_riservata` varchar(10) COLLATE latin1_general_cs NOT NULL COMMENT '''S''   ''N''',
  `num_seduta` varchar(10) COLLATE latin1_general_cs NOT NULL,
  `dataini` varchar(10) COLLATE latin1_general_cs NOT NULL,
  `oraini` varchar(10) COLLATE latin1_general_cs NOT NULL,
  `datafin` varchar(10) COLLATE latin1_general_cs NOT NULL,
  `orafin` varchar(10) COLLATE latin1_general_cs NOT NULL,
  `num_verbale` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `data_verbale` varchar(10) COLLATE latin1_general_cs NOT NULL,
  `luogo_seduta` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `note` text COLLATE latin1_general_cs NOT NULL,
  `id_lotto` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `gara_settoreattivita` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `codice` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `descrizione` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `ordine` int(11) NOT NULL,
  `trashed` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `gara_soccorsoistruttorio` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_gara` int(11) NOT NULL COMMENT 'FK gara_datigenerali',
  `id_lotto` int(11) NOT NULL,
  `utente_umod` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `data_umod` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `gestione` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `id_tipo_impresa` int(11) NOT NULL COMMENT '1 singola   2 raggruppamennto    3 professionista',
  `id_operatore` int(11) NOT NULL,
  `id_gara_offerta` int(11) NOT NULL COMMENT 'FK gara_offertericevute',
  `richiesta_prot_num` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `richiesta_prot_data` varchar(10) COLLATE latin1_general_cs NOT NULL,
  `importo` double NOT NULL,
  `risposta_prot_num` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `risposta_prot_data` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `allegato_ricevuta` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `f_esito` varchar(10) COLLATE latin1_general_cs NOT NULL COMMENT '''S''   ''N''',
  `motivoesclusione` text COLLATE latin1_general_cs NOT NULL,
  `note` text COLLATE latin1_general_cs NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `gara_sogliaanomalia` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_lotto` int(11) NOT NULL COMMENT 'FK gara_lotto',
  `utente_umod` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `data_umod` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `soglia_anomalia` varchar(66) COLLATE latin1_general_cs NOT NULL,
  `num_offerte_ammesse` int(11) NOT NULL,
  `coefficiente_taglioali` float NOT NULL,
  `num_offerte_datagliare` int(11) NOT NULL,
  `num_offerte_tagliate` int(11) NOT NULL,
  `num_offerte_residue` int(11) NOT NULL,
  `media_ribassi` varchar(66) COLLATE latin1_general_cs NOT NULL,
  `somma_ribassi` varchar(66) COLLATE latin1_general_cs NOT NULL,
  `coefficiente_decremento` float NOT NULL,
  `coefficiente_incremento` float NOT NULL,
  `valore_coeff_incdec` varchar(66) COLLATE latin1_general_cs NOT NULL,
  `scarto_medio_calcolato` varchar(66) COLLATE latin1_general_cs NOT NULL,
  `scarto_medio_rettificato` varchar(66) COLLATE latin1_general_cs NOT NULL,
  `prima_cifra_dec` int(11) NOT NULL,
  `somma_tutti_ribassi` varchar(66) COLLATE latin1_general_cs NOT NULL,
  `max_pt` float NOT NULL COMMENT 'max punteggio tecnico',
  `max_pe` float NOT NULL COMMENT 'max punteggio economico',
  `soglia_anomalia_pt` float NOT NULL,
  `soglia_anomalia_pe` float NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `gara_sopralluogo_datigenerali` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_lotto` int(11) NOT NULL COMMENT 'FK gara_lotto',
  `id_gara_avvisibandi` int(11) NOT NULL,
  `utente_umod` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `data_umod` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `descrizione` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `data_scadenzarichiesta` varchar(10) COLLATE latin1_general_cs NOT NULL,
  `data_scadenzaeffettuazione` varchar(10) COLLATE latin1_general_cs NOT NULL,
  `id_lp_soggetti` int(11) NOT NULL,
  `note` text COLLATE latin1_general_cs NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `gara_sopralluogo_richiesta` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_gara` int(11) NOT NULL,
  `id_lotto` int(11) NOT NULL COMMENT 'FK gara_lotto',
  `id_gara_avvisibandi` int(11) NOT NULL,
  `utente_umod` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `data_umod` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `id_tipo_impresa` int(11) NOT NULL COMMENT '1 singola   2 raggruppamennto    3 professionista',
  `id_operatore` int(11) NOT NULL,
  `richiesta_prot_num` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `richiesta_prot_data` varchar(10) COLLATE latin1_general_cs NOT NULL,
  `data_prenotazione` varchar(10) COLLATE latin1_general_cs NOT NULL,
  `ora_prenotazione` varchar(10) COLLATE latin1_general_cs NOT NULL,
  `f_eseguito` varchar(10) COLLATE latin1_general_cs NOT NULL COMMENT '''S''   ''N''',
  `id_lp_soggetti` int(11) NOT NULL,
  `ruolo_soggetto` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `allegato_verbale` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `note` text COLLATE latin1_general_cs NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `gara_sorteggio_criterianomalia` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_gara` int(11) NOT NULL,
  `id_lotto` int(11) NOT NULL,
  `id_gara_criteridiscelta` int(11) NOT NULL COMMENT 'fk tab. gara_criteridiscelta 1: al prezzo più basso  2:  economicamente più vantaggiosa',
  `id_gara_criteri_calcolo_anomalie` int(11) NOT NULL,
  `note` text COLLATE latin1_general_cs NOT NULL,
  `valore_criterio5` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `utente_umod` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `data_umod` varchar(10) COLLATE latin1_general_cs NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `gara_subappaltatori` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_gara` int(11) NOT NULL,
  `id_lotto` int(11) NOT NULL,
  `id_tipo_impresa` int(11) NOT NULL,
  `id_operatore` int(11) NOT NULL,
  `stato` varchar(10) COLLATE latin1_general_cs NOT NULL COMMENT '''S'' o ''N''  SI se è l''impresa che subappata ''N'' non è l''impresa',
  `id_gara_categorie_gara` int(11) NOT NULL COMMENT 'per quale categorie viene eseguito il subappalto',
  `importosubappalto` double NOT NULL DEFAULT '0' COMMENT 'importo lordo del SUBAPPALTO',
  `utente_umod` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `data_umod` varchar(10) COLLATE latin1_general_cs NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `gara_tipo_amm_agg` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `codice` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `descrizione` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `trashed` int(11) NOT NULL,
  `ordine` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `gara_tipobando` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `cod` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `descrizione` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `ordine` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs COMMENT='DECOD TIPOLOGIA AVVISI-BANDI';
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `gara_valutazione_valutazione_albero` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `idcriteriopadre` int(11) NOT NULL,
  `idcriterio` int(11) NOT NULL,
  `idofferta` int(11) NOT NULL,
  `punteggio` double NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `gara_verbalicomunicazioni_lotto` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_gara` int(11) NOT NULL,
  `id_lotto` int(11) NOT NULL,
  `tipo_documento` int(11) NOT NULL COMMENT '1:verbale 2:comunicazione 3:documentazione esterna  4: altro',
  `descrizione` text COLLATE latin1_general_cs NOT NULL,
  `utente_umod` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `data_umod` varchar(10) COLLATE latin1_general_cs NOT NULL,
  `id_allegato` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `gara_verificadocumentazione` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_gara` int(11) NOT NULL,
  `id_lotto` int(11) NOT NULL COMMENT 'FK gara_lotto',
  `utente_umod` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `data_umod` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `id_tipo_impresa` int(11) NOT NULL COMMENT '1 singola   2 raggruppamennto    3 professionista',
  `id_operatore` int(11) NOT NULL,
  `id_gara_offerta` int(11) NOT NULL COMMENT 'FK gara_offertericevute',
  `esito_amministrativo` varchar(10) COLLATE latin1_general_cs NOT NULL COMMENT '''S''   ''N''',
  `esito_tecnico` varchar(10) COLLATE latin1_general_cs NOT NULL COMMENT '''S''   ''N''',
  `esito_economico` varchar(10) COLLATE latin1_general_cs NOT NULL COMMENT '''S''    ''N''',
  `id_seduta_amministrativo` int(11) NOT NULL,
  `id_seduta_tecnico` int(11) NOT NULL,
  `id_seduta_economico` int(11) NOT NULL,
  `punteggio_tecnico` double NOT NULL,
  `punteggio_economico` double NOT NULL,
  `importo_offerto` double NOT NULL,
  `percentuale_ribasso` double NOT NULL,
  `punteggio_offerta` double NOT NULL,
  `f_esclusa` varchar(10) COLLATE latin1_general_cs NOT NULL COMMENT '''S''   ''N''',
  `note` text COLLATE latin1_general_cs NOT NULL,
  `garanzia_numero` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `garanzia_dataattivazione` varchar(10) COLLATE latin1_general_cs NOT NULL,
  `garanzia_importo` double NOT NULL,
  `garanzia_scadenza` varchar(10) COLLATE latin1_general_cs NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `infoc_naturegiuridiche` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `codice` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `descrizione` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `codicebdap` varchar(255) COLLATE latin1_general_cs NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `infocamere_accessoutenti` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `utente` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `password` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `abilitato` varchar(255) COLLATE latin1_general_cs NOT NULL COMMENT 'S=si  N=NO',
  `utente_umod` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `data_umod` varchar(8) COLLATE latin1_general_cs NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `infocamere_associazioneutenti` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `utente_software` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `id_infocamere_accessoutenti` int(11) NOT NULL,
  `stato` int(11) NOT NULL COMMENT '0 abilitato 1 non abilitato',
  `utente_umod` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `data_umod` varchar(8) COLLATE latin1_general_cs NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `l190_ackallineamentoapa` (
  `id` int(11) NOT NULL,
  `ambito` varchar(20) COLLATE latin1_general_cs NOT NULL,
  `ambitorowid` int(11) NOT NULL,
  `impronta` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `data_ins` varchar(20) COLLATE latin1_general_cs NOT NULL COMMENT 'data nel formato YmdHis',
  `utente_ins` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `data_umod` varchar(20) COLLATE latin1_general_cs NOT NULL,
  `utente_umod` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `trashed` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `ambito` (`ambito`,`ambitorowid`,`trashed`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs COMMENT='gestione delle ACK per ignorare un determinato set di modifi';
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `l190_aggiudicatari` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `idlotto` int(11) NOT NULL,
  `fragg` int(11) NOT NULL,
  `idrecord` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `l190_autoexport` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `idorganization` int(11) NOT NULL,
  `attivo` int(11) NOT NULL,
  `ultimo` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `esito` int(11) NOT NULL,
  `esitomsg` text COLLATE latin1_general_cs NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `l190_esportazione` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `tipoesportazione` int(11) NOT NULL COMMENT '1=>REPORT 2=>XML PUBBLICATO',
  `anno` varchar(30) COLLATE latin1_general_cs NOT NULL DEFAULT '0',
  `sdtpubbindice` varchar(20) COLLATE latin1_general_cs DEFAULT NULL,
  `url` varchar(1000) COLLATE latin1_general_cs NOT NULL,
  `idorganization` int(11) NOT NULL DEFAULT '0',
  `xmlfileaux` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `impost_dataprimapubb` int(11) NOT NULL COMMENT 'combo override data prima pubb indice',
  `impost_dataaggpubb` int(11) NOT NULL COMMENT 'combo override last upd indice',
  `singolodataset` int(11) NOT NULL COMMENT 'flag unico dataset',
  `sds_titolo` varchar(255) COLLATE latin1_general_cs NOT NULL COMMENT 'unico DS - titolo',
  `sds_abstract` varchar(255) COLLATE latin1_general_cs NOT NULL COMMENT 'unico DS - abstract',
  `sds_ente` varchar(255) COLLATE latin1_general_cs NOT NULL COMMENT 'unico DS - ente pubb',
  `sds_licenza` varchar(255) COLLATE latin1_general_cs NOT NULL COMMENT 'unico DS - licenza',
  `sds_dtprimapubb` varchar(8) COLLATE latin1_general_cs NOT NULL COMMENT 'unico DS - sdt prima pubb indice',
  `sds_dtultimoagg` varchar(8) COLLATE latin1_general_cs NOT NULL COMMENT 'unico DS - sdt last upd indice',
  `noCFExport` int(11) NOT NULL COMMENT '0=>mostraCF 1=> oscuraCF, show solo PIVA',
  `f_autoexport` int(11) NOT NULL COMMENT '1=>attivo !=1 => non attivo',
  `repoxml` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `repozip` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `repohtml` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `repoxls` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `repoods` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `repopdf` varchar(1000) COLLATE latin1_general_cs NOT NULL,
  `urlxml` varchar(1000) COLLATE latin1_general_cs NOT NULL,
  `urlportalexml` varchar(1000) COLLATE latin1_general_cs NOT NULL,
  `urlportalezip` varchar(1000) COLLATE latin1_general_cs NOT NULL,
  `urlportalexls` varchar(1000) COLLATE latin1_general_cs NOT NULL,
  `urlportaleods` varchar(1000) COLLATE latin1_general_cs NOT NULL,
  `urlportalepdf` varchar(1000) COLLATE latin1_general_cs NOT NULL,
  `datacreazione` varchar(20) COLLATE latin1_general_cs NOT NULL,
  `utente_umod` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `data_umod` varchar(255) COLLATE latin1_general_cs NOT NULL,
  PRIMARY KEY (`id`),
  KEY `anno` (`anno`),
  KEY `idorganization` (`idorganization`),
  KEY `anno_2` (`anno`,`idorganization`),
  KEY `tipoesportazione` (`tipoesportazione`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `l190_importflusso` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `tipoflusso` varchar(50) COLLATE latin1_general_cs NOT NULL,
  `dataimport` varchar(20) COLLATE latin1_general_cs NOT NULL,
  `campochiave` varchar(50) COLLATE latin1_general_cs NOT NULL,
  `nomefile` varchar(100) COLLATE latin1_general_cs NOT NULL,
  `st_struttura` varchar(255) COLLATE latin1_general_cs NOT NULL DEFAULT '',
  `nrighe` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `tipoflusso` (`tipoflusso`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `l190_importrow` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `idflusso` int(11) NOT NULL,
  `keyval` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `serialrow` text COLLATE latin1_general_cs,
  `stato` int(11) NOT NULL DEFAULT '0',
  `statomsg` varchar(255) COLLATE latin1_general_cs NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idflusso` (`idflusso`,`keyval`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `l190_lotto` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `idpubb` int(11) NOT NULL,
  `cig` varchar(100) COLLATE latin1_general_cs NOT NULL,
  `oggetto` text COLLATE latin1_general_cs NOT NULL,
  `idsceltacontraente` int(11) NOT NULL COMMENT 'ID tabella c11',
  `stproponente` varchar(250) COLLATE latin1_general_cs NOT NULL DEFAULT '',
  `stproponentecf` varchar(250) COLLATE latin1_general_cs NOT NULL DEFAULT '',
  `sdtinizio` varchar(20) COLLATE latin1_general_cs NOT NULL,
  `sdtfine` varchar(20) COLLATE latin1_general_cs NOT NULL,
  `sommaliquidata` double NOT NULL,
  `importoaggiudicazione` double NOT NULL,
  `tipoaffidamento` varchar(50) COLLATE latin1_general_cs NOT NULL DEFAULT '',
  `attonum` varchar(20) COLLATE latin1_general_cs NOT NULL,
  `attosdt` varchar(20) COLLATE latin1_general_cs NOT NULL,
  `attotipo` int(11) NOT NULL,
  `attourl` text COLLATE latin1_general_cs NOT NULL,
  `note` text COLLATE latin1_general_cs NOT NULL,
  `utentelastmod` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `dataoperlastmod` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `trashed` int(11) NOT NULL,
  `idorganization` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idpubb` (`idpubb`),
  KEY `cig` (`cig`),
  KEY `trashed` (`trashed`),
  KEY `idorganization` (`idorganization`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `l190_newshome` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `titolo` text COLLATE latin1_general_cs NOT NULL,
  `news` text COLLATE latin1_general_cs NOT NULL,
  `immagine` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `datanews` varchar(10) COLLATE latin1_general_cs NOT NULL,
  `trashed` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `l190_partecipanti` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `idlotto` int(11) NOT NULL,
  `fragg` int(11) NOT NULL,
  `idrecord` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `fragg` (`fragg`),
  KEY `idrecord` (`idrecord`),
  KEY `idlotto` (`idlotto`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `l190_pubblicazione` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `cod` varchar(250) COLLATE latin1_general_cs NOT NULL,
  `titolo` varchar(1024) COLLATE latin1_general_cs NOT NULL,
  `abstract` varchar(1024) COLLATE latin1_general_cs NOT NULL,
  `sdtpubblicazionedataset` varchar(20) COLLATE latin1_general_cs NOT NULL,
  `entepubblicatore` varchar(250) COLLATE latin1_general_cs NOT NULL,
  `sdtultimoaggdataset` varchar(20) COLLATE latin1_general_cs NOT NULL,
  `annorif` int(11) NOT NULL,
  `urlfile` text COLLATE latin1_general_cs NOT NULL,
  `licenza` varchar(250) COLLATE latin1_general_cs NOT NULL,
  `stato` int(11) DEFAULT NULL,
  `warning` text COLLATE latin1_general_cs,
  `errori` text COLLATE latin1_general_cs,
  `trashed` int(11) NOT NULL DEFAULT '0',
  `idorganization` int(11) NOT NULL DEFAULT '0',
  `utente` varchar(255) COLLATE latin1_general_cs NOT NULL COMMENT 'aggiornamento 02-12-2013',
  `dataoper` varchar(255) COLLATE latin1_general_cs NOT NULL COMMENT 'aggiornamento 02-12-2013',
  `utentelastmod` varchar(255) COLLATE latin1_general_cs NOT NULL COMMENT 'aggiornamento 02-12-2013 ultimo utente che ha modificato il record',
  `dataoperlastmod` varchar(255) COLLATE latin1_general_cs NOT NULL COMMENT 'aggiornamento 02-12-2013 data ultima modifica del record',
  `st_struttura` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `idrevisione` int(11) DEFAULT NULL,
  `md5warn` varchar(1000) COLLATE latin1_general_cs NOT NULL DEFAULT '-1',
  `ackanomalie` int(11) NOT NULL DEFAULT '0',
  `ackmd5` varchar(1000) COLLATE latin1_general_cs NOT NULL DEFAULT '-1',
  `note` text COLLATE latin1_general_cs NOT NULL,
  `idflussoimport` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `trashed` (`trashed`),
  KEY `idorganization` (`idorganization`),
  KEY `sdtpubblicazionedataset` (`sdtpubblicazionedataset`),
  KEY `entepubblicatore` (`entepubblicatore`),
  KEY `sdtultimoaggdataset` (`sdtultimoaggdataset`),
  KEY `annorif` (`annorif`),
  KEY `utente` (`utente`),
  KEY `utentelastmod` (`utentelastmod`),
  KEY `dataoper` (`dataoper`),
  KEY `dataoperlastmod` (`dataoperlastmod`),
  KEY `st_struttura` (`st_struttura`),
  KEY `idrevisione` (`idrevisione`),
  KEY `titolo` (`titolo`(767)),
  KEY `abstract` (`abstract`(767))
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `l190_revisioni` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `idrevisione` int(11) NOT NULL,
  `idpubb` int(11) NOT NULL,
  `annorif` varchar(50) COLLATE latin1_general_cs NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `l190_urlpubblicazione` (
  `id` int(11) NOT NULL,
  `url` varchar(250) COLLATE latin1_general_cs DEFAULT NULL,
  `idorganization` int(11) NOT NULL,
  `utentelastmod` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `dataoperlastmod` varchar(255) COLLATE latin1_general_cs NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idorganization` (`idorganization`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `lp_aggiudicazione` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_progetto` varchar(68) COLLATE latin1_general_cs NOT NULL,
  `id_a28_procedura_aggiudicazione` int(11) NOT NULL,
  `perc_ribassoaggiudicazione` double NOT NULL,
  `perc_offertaaumento` double NOT NULL,
  `importoaggiudicato` double NOT NULL,
  `dataaggiud_definitiva` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `rich_subappalto` varchar(255) COLLATE latin1_general_cs NOT NULL COMMENT 'S:  si    N: no',
  `cig` varchar(10) COLLATE latin1_general_cs NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `lp_allegati` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_progetto` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `descrizionepro` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `tipodoc` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `allegato` text COLLATE latin1_general_cs NOT NULL,
  `descrizionedoc` text COLLATE latin1_general_cs NOT NULL,
  `filename` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `tipodocumento` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `size` int(11) NOT NULL COMMENT 'dimensione del file espressa in byte',
  `id_documento` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `preview` text COLLATE latin1_general_cs NOT NULL,
  `datains` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `orains` varchar(8) COLLATE latin1_general_cs NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `lp_amministrazioni_pubbliche` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `descrizione` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `istatregione` varchar(3) COLLATE latin1_general_cs NOT NULL,
  `istatprovincia` varchar(3) COLLATE latin1_general_cs NOT NULL,
  `istatcomune` varchar(6) COLLATE latin1_general_cs NOT NULL,
  `cf` varchar(16) COLLATE latin1_general_cs NOT NULL,
  `toponimo` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `indirizzo` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `numero` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `pec` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `telefono` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `formagiuridica` varchar(6) COLLATE latin1_general_cs NOT NULL COMMENT 'tabella decodifica c8',
  `settoreattivitaeconomica` varchar(8) COLLATE latin1_general_cs NOT NULL COMMENT 'tabella decodifica C9',
  `cap` varchar(10) COLLATE latin1_general_cs NOT NULL,
  `gruppo` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `nuts` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `fax` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `email` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `web` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `id_gara_tipo_amm_agg` int(11) NOT NULL DEFAULT '-1',
  `id_gara_settoreattivita` int(11) DEFAULT '-1',
  `iban` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `datitesoreria` text COLLATE latin1_general_cs NOT NULL COMMENT 'dati relativi alla tesoreria',
  `plugin_trasparenza` int(11) NOT NULL COMMENT '0: non attivo 1: attivo plugin trasparenza',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `lp_bisogni` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_progetto` varchar(68) COLLATE latin1_general_cs NOT NULL COMMENT 'sarà utilizzato come id_progetto nelle tabelle pt_scheda1 - 2 -2b -3 -4',
  `descrizione` text COLLATE latin1_general_cs NOT NULL,
  `stimacosti` double NOT NULL,
  `proponente` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `id_bisogno` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `lp_bisogni_documenti` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_progetto` int(11) NOT NULL,
  `id_lp_documenti_opera` int(11) NOT NULL COMMENT 'indica il tipo di documento allegato al bisogno preliminare, fattibilità ecc',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs COMMENT='tablla che consente di archiviare per ogni bisogno un proget';
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `lp_categorie_lavori` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `lp_categorie_opere_id` int(11) NOT NULL,
  `id_sim_class_importo` int(11) NOT NULL COMMENT 'fk sim_classeimporto',
  `a0_anagraficaprogetti_id_progetto` varchar(68) COLLATE latin1_general_cs NOT NULL,
  `importo` double NOT NULL,
  `id_a21_iterprocedurale` int(11) NOT NULL,
  `prevalente` varchar(2) COLLATE latin1_general_cs NOT NULL COMMENT 'S=SI N=NO se lavoro è prevalente',
  `scorporabile` varchar(2) COLLATE latin1_general_cs NOT NULL COMMENT 'N=NO se lavoro è scorporabile',
  `perc_subappaltabile` double NOT NULL COMMENT '% subappaltabile',
  `perc_manodopera` double NOT NULL COMMENT '% di incicenza della manodopera',
  `importooperesub` double NOT NULL,
  `versionefinale` varchar(2) COLLATE latin1_general_cs NOT NULL COMMENT 'S=SI considera questo record per BDAP N=NO',
  `utente_umod` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `data_umod` varchar(8) COLLATE latin1_general_cs NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `lp_categorie_opere` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `categoria` varchar(2) COLLATE latin1_general_cs NOT NULL,
  `codice` varchar(4) COLLATE latin1_general_cs NOT NULL,
  `descrizione` text COLLATE latin1_general_cs NOT NULL,
  `codice_simog` varchar(255) COLLATE latin1_general_cs NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `lp_causastipula_assicurazione` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `codice` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `descrizione` varchar(255) COLLATE latin1_general_cs NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `lp_cauzioni` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_progetto` int(11) NOT NULL,
  `id_cig` int(11) NOT NULL,
  `id_lp_polizzeassicurative` int(11) NOT NULL,
  `id_lp_causastipula_assicurazione` int(11) NOT NULL,
  `utente_umod` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `data_umod` varchar(8) COLLATE latin1_general_cs NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `lp_certificatopagamento` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_progetto` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `datacertificato` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `nrcertificato` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `importo` double NOT NULL,
  `id_a22` int(11) NOT NULL,
  `note` text COLLATE latin1_general_cs NOT NULL,
  `utente_umod` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `data_umod` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `gestione` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `id_allegato` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `lp_collaudo` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_progetto` varchar(68) COLLATE latin1_general_cs NOT NULL,
  `id_a21_iterprocedurale` int(11) NOT NULL,
  `dataultimavisita` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `importoriserve` double NOT NULL,
  `importoriservecollaudo` double NOT NULL,
  `termineredazioneverbale` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `datapprovazionecertificato_sa` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `dataratasaldo` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `nr_infortuni` int(11) NOT NULL,
  `nr_infortunimortali` int(11) NOT NULL,
  `nr_visite` int(11) NOT NULL,
  `importocorrezionicollaudatore` double NOT NULL,
  `dataredazionecertificato` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `nr_giornimanodoperatot` int(11) NOT NULL,
  `importorataasaldo` double NOT NULL,
  `esito` varchar(255) COLLATE latin1_general_cs NOT NULL COMMENT 'P positivo  N negativo',
  `id_collaudo` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs COMMENT='nella schede SIMOG rappresenta la scheda CONCLUSIONE';
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `lp_configurazioneente` (
  `id` int(11) NOT NULL,
  `descrizione` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `istatregione` varchar(3) COLLATE latin1_general_cs NOT NULL,
  `istatprovincia` varchar(3) COLLATE latin1_general_cs NOT NULL,
  `istatcomune` varchar(6) COLLATE latin1_general_cs NOT NULL,
  `cf` varchar(16) COLLATE latin1_general_cs NOT NULL,
  `toponimo` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `indirizzo` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `numero` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `pec` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `telefono` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `formagiuridica` varchar(6) COLLATE latin1_general_cs NOT NULL,
  `settoreattivitaeconomica` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `cap` varchar(6) COLLATE latin1_general_cs NOT NULL,
  `gruppo` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `fax` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `web` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `iban` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `email` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `datitesoreria` text COLLATE latin1_general_cs NOT NULL COMMENT 'dati relativi alla tesoreria dell''ente',
  `id_gara_tipo_amm_agg` int(11) NOT NULL,
  `id_gara_settoreattivita` int(11) NOT NULL,
  `belfiore` varchar(10) COLLATE latin1_general_cs NOT NULL DEFAULT '',
  `ftpbdap_ultimalettura` varchar(30) COLLATE latin1_general_cs NOT NULL,
  `statocliente` int(11) NOT NULL DEFAULT '0' COMMENT '0: attivo 1: disattivato',
  `utente_umod` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `data_umod` varchar(255) COLLATE latin1_general_cs NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `lp_contatti_ente` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `tipo_sorgente` int(11) NOT NULL COMMENT '0 non definito 1 lp_configurazioneente 2 lp_amministrazioni_pubbliche',
  `id_ente` int(11) NOT NULL,
  `denominazione` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `indirizzo` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `istatcitta` varchar(6) COLLATE latin1_general_cs NOT NULL,
  `cap` varchar(10) COLLATE latin1_general_cs NOT NULL,
  `nazione` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `email` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `pec` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `fax` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `telefono` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `web` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `referente` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `note` text COLLATE latin1_general_cs NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `lp_contenziosi` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_progetto` int(11) NOT NULL,
  `id_cig` int(11) NOT NULL,
  `id_lp_modalita_contenziosi` int(11) NOT NULL,
  `importoproposto` double NOT NULL,
  `importoaccordato` double NOT NULL,
  `data` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `utente_umod` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `data_umod` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `id_allegato` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `lp_dichiarante` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `tiposogg` int(11) NOT NULL,
  `idsogg` int(11) NOT NULL,
  `cognome` varchar(100) COLLATE latin1_general_cs NOT NULL,
  `nome` varchar(100) COLLATE latin1_general_cs NOT NULL,
  `nascita_prov` varchar(100) COLLATE latin1_general_cs NOT NULL,
  `nascita_comune` varchar(100) COLLATE latin1_general_cs NOT NULL,
  `nascita_data` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `cf` varchar(20) COLLATE latin1_general_cs NOT NULL,
  `id_tipo_ruolo` int(11) NOT NULL,
  `utente_umod` varchar(100) COLLATE latin1_general_cs NOT NULL,
  `data_umod` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `trashed` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `lp_documenti_a21_iterprocedurale` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `a21_iterprocedurale_id` int(11) NOT NULL,
  `id_progetto` varchar(68) COLLATE latin1_general_cs NOT NULL,
  `lp_documenti_opera_dettaglio_id` int(11) NOT NULL,
  `datadocumento` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `note` text COLLATE latin1_general_cs NOT NULL,
  `utente_umod` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `data_umod` varchar(8) COLLATE latin1_general_cs NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `lp_documenti_opera` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `descrizione` varchar(255) COLLATE latin1_general_cs NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `lp_documenti_opera_dettaglio` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `lp_documenti_opera_id` int(11) NOT NULL,
  `descrizione` varchar(255) COLLATE latin1_general_cs NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `lp_duratalavori` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ggutiliesecuzione` int(11) NOT NULL,
  `dataconsegnalavori` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `datainiziolavori` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `datapresuntafinelavori` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `ggsospensione` int(11) NOT NULL,
  `ggproroga` int(11) NOT NULL,
  `dataeffettivafinelavori` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `totgglavori` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `difffinelavori` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `id_progetto` varchar(255) COLLATE latin1_general_cs NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `lp_entepreposto` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `codice` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `descrizione` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `piva` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `Indirizzo` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `citta` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `cap` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `pec` varchar(255) COLLATE latin1_general_cs NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `lp_esibente` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `tiposogg` int(11) NOT NULL,
  `idsogg` int(11) NOT NULL,
  `f_albo` int(11) NOT NULL COMMENT 'flag sorgente albo fornitori',
  `attivo` int(11) NOT NULL,
  `trashed` int(11) NOT NULL,
  `utente_umod` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `data_umod` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `tiposogg` (`tiposogg`,`idsogg`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `lp_fatture` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_progetto` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `datafattura` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `nrfattura` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `id_a28` int(11) NOT NULL,
  `imponibile` double NOT NULL,
  `imposta` double NOT NULL,
  `aliquota` double NOT NULL,
  `totale` double NOT NULL,
  `tipoiva` varchar(255) COLLATE latin1_general_cs NOT NULL COMMENT 'R:  reverse charge  S: Split Payment  N: Normale',
  `contributocassa` double NOT NULL COMMENT 'contributo cassa professionisti',
  `note` text COLLATE latin1_general_cs NOT NULL,
  `descrizione` text COLLATE latin1_general_cs NOT NULL,
  `id_lp_impresa` int(11) NOT NULL,
  `tipoimpresa` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `id_lp_certificatopagamento` int(11) NOT NULL,
  `gestione` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `utente_umod` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `data_umod` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `id_allegato` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `lp_finanziaria` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `annoesercizio` int(11) NOT NULL,
  `capitolo` int(11) NOT NULL,
  `descrizionecapitolo` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `importo` double NOT NULL,
  `residuo` double NOT NULL,
  `utente_umod` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `data_umod` varchar(8) COLLATE latin1_general_cs NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `lp_finanziaria_impegni` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_finanziaria` int(11) NOT NULL,
  `id_progetto` int(11) NOT NULL,
  `nr_impegno` int(11) NOT NULL,
  `importo` double NOT NULL,
  `utente_umod` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `data_umod` varchar(8) COLLATE latin1_general_cs NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `lp_gara_tipoincarico` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `codice` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `descrizione` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `ordine` int(11) NOT NULL,
  `ambito` varchar(255) COLLATE latin1_general_cs NOT NULL COMMENT 'server per utilizzare questa tabella in più ambiti tipo commissione di gara o altre tab',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `lp_impegni_pagamenti` (
  `id` int(11) NOT NULL,
  `id_a15` int(11) NOT NULL,
  `nr_atto_liq` int(11) NOT NULL,
  `data_atto_lid` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `utente_umod` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `data_umod` int(11) NOT NULL,
  `id_pt_scheda2` int(11) NOT NULL,
  `annoprogrammazione` varchar(255) COLLATE latin1_general_cs NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `lp_import_avcp` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `cig` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `json` text COLLATE latin1_general_cs NOT NULL,
  `utente_umod` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `data_umod` varchar(8) COLLATE latin1_general_cs NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `lp_import_cityware` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `cig` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `cup` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `numeromandato` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `datamandato` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `importo` double NOT NULL,
  `descrizione` varchar(1000) COLLATE latin1_general_cs NOT NULL,
  `numeroatto` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `tipoatto` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `dataatto` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `codicefornitore` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `fornitore` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `piva` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `cf` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `datafattura` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `numerofattura` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `ripartizionefattura` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `totalemandato` double NOT NULL,
  `utente_umod` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `data_umod` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `progressivo_caricamento` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `lp_import_halley` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `cig` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `cup` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `numeromandato` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `datamandato` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `progressivorigo` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `importorigo` double NOT NULL,
  `descrizione` varchar(1000) COLLATE latin1_general_cs NOT NULL,
  `numeroatto` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `tipoatto` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `dataatto` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `codicefornitore` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `fornitore` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `piva` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `cf` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `datafattura` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `numerofattura` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `ripartizionefattura` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `totalemandato` double NOT NULL,
  `utente_umod` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `data_umod` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `progressivo_caricamento` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `lp_import_impegni` (
  `id` int(11) NOT NULL,
  `cig` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `cup` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `numeroimpegno` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `dataimpegno` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `progressivorigo` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `importorigo` double NOT NULL,
  `descrizione` varchar(1000) COLLATE latin1_general_cs NOT NULL,
  `numeroatto` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `tipoatto` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `dataatto` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `codicefornitore` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `fornitore` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `piva` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `cf` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `utente_umod` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `data_umod` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `progressivo_caricamento` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `lp_impresa` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ragionesociale` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `piva` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `cf` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `cf_estero` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `posizioneinps` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `posizioneinail` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `settoreattivitaeconomica` varchar(8) COLLATE latin1_general_cs NOT NULL COMMENT 'tabella decodifica C9',
  `formagiuridica` varchar(6) COLLATE latin1_general_cs NOT NULL COMMENT 'tabella c8',
  `classeaddetti` varchar(1) COLLATE latin1_general_cs NOT NULL COMMENT 'A:  FINO A 9     B: DA 10 A 49     C: DA 50 A 249     D: OLTRE 249',
  `cassaediledi` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `cognome_leg` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `nome_leg` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `dimensione` int(11) NOT NULL COMMENT '1: ''MEDIA IMPRESA   2 PICCOLA IMPRESA     3: MICROIMPRESA     4 GRANDE IMPRESA',
  `datafinevalidita` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `cf_leg_rappresentante` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `indirizzo` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `civico` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `cap` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `istat_citta` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `istat_provincia` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `email` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `pec` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `telefono` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `nr_cciaa` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `data_iscrizione_cciaa` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `citta_cciaa` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `inail_sede` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `inps_sede` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `utente_umod` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `data_umod` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `sedioperative` text COLLATE latin1_general_cs NOT NULL,
  `iscrizionemepa` varchar(10) COLLATE latin1_general_cs NOT NULL COMMENT '0: iscritta 1: non iscritta',
  `f_statoanag` int(11) NOT NULL DEFAULT '1' COMMENT 'stato anagrafica: 1-ATTIVO -1-CONGELATO(no attivo)',
  `recmaster` int(11) NOT NULL,
  `fax` varchar(100) COLLATE latin1_general_cs NOT NULL,
  `data_ins` varchar(20) COLLATE latin1_general_cs NOT NULL,
  `utente_ins` varchar(255) COLLATE latin1_general_cs NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `lp_impresa_affidamento` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_lp_impresa` int(11) NOT NULL,
  `id_a30` int(11) NOT NULL,
  `tipoimpresa` varchar(255) COLLATE latin1_general_cs NOT NULL COMMENT '1: singola  2: RTI 3: ATI',
  `dataconsegnalavori` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `importocontratto` double NOT NULL,
  `num_prot_convocazione` int(11) NOT NULL,
  `data_prot_convocazione` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `num_determina_aggiudicazione` int(11) NOT NULL,
  `tipoatto` int(11) NOT NULL COMMENT 'tabella decodifica lp_tipoattoamministrativo',
  `data_determina_aggiudicazione` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `nr_verbgara` int(11) NOT NULL,
  `data_verbgara` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `utente_umod` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `data_umod` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `cig` varchar(10) COLLATE latin1_general_cs NOT NULL,
  `id_progetto` varchar(68) COLLATE latin1_general_cs NOT NULL,
  `id_a28` int(11) NOT NULL,
  `impegno` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `vocequad` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `richsub` varchar(2) COLLATE latin1_general_cs NOT NULL,
  `tipoaffidamentoavcp` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `datafineafidamento` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `datainizioaffidamento` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `id_allegato` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `lp_imprese_soggetti` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_lp_soggetti` int(11) NOT NULL,
  `tiposogg` int(11) DEFAULT '1' COMMENT 'tipo soggetto:1 lp_impresa, 2_raggruppamenti, 3 prof',
  `id_lp_impresa` int(11) NOT NULL,
  `id_lp_tipo_ruolo_impresa` int(11) NOT NULL,
  `legalerappresentante` int(11) NOT NULL COMMENT '0: NO legale rappres. 1: SI RAPPRESENTANTE LEGALE',
  `dataini` varchar(10) COLLATE latin1_general_cs NOT NULL,
  `dataend` varchar(10) COLLATE latin1_general_cs NOT NULL,
  `utente_umod` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `data_umod` varchar(10) COLLATE latin1_general_cs NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `lp_invitiofferte` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_progetto` int(11) NOT NULL,
  `id_a28` int(11) NOT NULL,
  `datascad_man_interesse` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `datascad_rich_invito` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `datainvito` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `datascad_pres_offerte` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `nr_sogg_man_interesse` int(11) NOT NULL,
  `nr_sogg_invitati` int(11) NOT NULL,
  `nr_offerte` int(11) NOT NULL,
  `perc_off_max_ribasso` double NOT NULL,
  `per_off_min_ribasso` double NOT NULL,
  `val_soglia_anomalia` double NOT NULL,
  `nr_offerte_mag_soglia` int(11) NOT NULL,
  `nr_imprese_escluse_aut` int(11) NOT NULL,
  `nr_imprese_escluse_nn_giust` int(11) NOT NULL,
  `cig` varchar(255) COLLATE latin1_general_cs NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs COMMENT='tabella simog per la gestione inviti/offerte soglia di anoma';
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `lp_istat_comuni` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `codiceprovincia` varchar(3) COLLATE latin1_general_cs NOT NULL,
  `codiceregione` varchar(2) COLLATE latin1_general_cs NOT NULL,
  `codice` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `descrizione` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `codregione` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `codcittametropolitana` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `codprovincia` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `codprogessivocomune` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `codcatastale` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `pop2011` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `nuts_s1_2010` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `nuts_s2_2010` varchar(255) COLLATE latin1_general_cs NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_codiceprovincia` (`codiceprovincia`),
  KEY `idx_codiceregione` (`codiceregione`),
  KEY `codcatastale` (`codcatastale`),
  KEY `idx_codice` (`codice`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `lp_istat_provincie` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `codice` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `descrizione` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `codiceregione` varchar(2) COLLATE latin1_general_cs NOT NULL,
  `sigla` varchar(255) COLLATE latin1_general_cs NOT NULL COMMENT 'sigla provincia',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `lp_istat_regioni` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `codice` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `descrizione` varchar(255) COLLATE latin1_general_cs NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `lp_modalita_contenziosi` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `codice` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `descrizione` varchar(255) COLLATE latin1_general_cs NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `lp_modalita_stipula_polizza` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `codice` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `descrizione` varchar(255) COLLATE latin1_general_cs NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `lp_modulisoftware_appaltipa` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `codmodulo` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `nomemodulo` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `dataattivazione` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `datadisattivazione` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `stato` varchar(1) COLLATE latin1_general_cs NOT NULL COMMENT '0: modulo attivato  1:moduulo non attivato',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `lp_modulisoftware_dateattivazioni` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_lp_modulisoftware_appaltipa` int(11) NOT NULL,
  `dataattivazione` varchar(10) COLLATE latin1_general_cs NOT NULL,
  `datascadenza` varchar(10) COLLATE latin1_general_cs NOT NULL,
  `note` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `utente_umod` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `data_umod` varchar(255) COLLATE latin1_general_cs NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `lp_pareri_a21_iterprocedurale` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_progetto` varchar(68) COLLATE latin1_general_cs NOT NULL,
  `id_lp_tipoparere` int(11) NOT NULL,
  `id_a21` int(11) NOT NULL,
  `oggetto` text COLLATE latin1_general_cs NOT NULL,
  `nr_protocollo_rich` int(11) NOT NULL,
  `data_protocollo_rich` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `nr_protocollo_arr` int(11) NOT NULL,
  `data_protocollo_arr` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `esitoparere` varchar(255) COLLATE latin1_general_cs NOT NULL COMMENT 'P: positivo  N: negativo  X: parere positivo condizionato',
  `noteparere` text COLLATE latin1_general_cs NOT NULL,
  `utente_umod` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `data_umod` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `id_parere` int(11) NOT NULL,
  `entepreposto` varchar(255) COLLATE latin1_general_cs NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `lp_partecipantigara` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_progetto` varchar(68) COLLATE latin1_general_cs NOT NULL,
  `id_a28_procedura_aggiudicazione` int(11) NOT NULL,
  `id_partecipante` int(11) NOT NULL,
  `tipoimpresa` int(11) NOT NULL,
  `cig` varchar(10) COLLATE latin1_general_cs NOT NULL,
  `utente_umod` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `data_umod` varchar(8) COLLATE latin1_general_cs NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `lp_piani18` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `descrizione` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `tipo` varchar(255) COLLATE latin1_general_cs NOT NULL COMMENT 'L piano triennale Lavori  S piano biennale servizi ecc',
  `annoinizio` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `annofine` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `dataapprovazione` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `tipoatto` varchar(255) COLLATE latin1_general_cs NOT NULL COMMENT 'Delibera giunta, delibera consiglio ecc',
  `nratto` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `dataadozione` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `tipoattoadozione` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `nrattoadozione` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `id_lp_soggetti` int(11) NOT NULL COMMENT 'ID del responsabile di attuazione del piano',
  `id_allegato` int(11) NOT NULL COMMENT 'funzione che mi collega l''atto di approvazione pdf, doc ecc',
  `id_ente` varchar(255) COLLATE latin1_general_cs NOT NULL COMMENT 'identificativo univo ALIPROG',
  `pubblicaweb` int(11) NOT NULL DEFAULT '0' COMMENT '0: pubblica web 1: non pubblicare',
  `id_prec_lp_piani18` int(11) NOT NULL COMMENT 'identificativo piano triennale precedente a questo',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `lp_pianotriennale` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `descrizionetriennio` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `annoinizio` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `annofine` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `dataapprovazione` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `tipoatto` varchar(255) COLLATE latin1_general_cs NOT NULL COMMENT 'Delibera giunta, delibera consiglio ecc',
  `nratto` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `id_allegato` int(11) NOT NULL COMMENT 'funzione che mi collega l''atto di approvazione pdf, doc ecc',
  `id_ente` varchar(255) COLLATE latin1_general_cs NOT NULL COMMENT 'identificativo univo ALIPROG',
  `pubblicaweb` int(11) NOT NULL DEFAULT '0' COMMENT '0: pubblica web 1: non pubblicare',
  `dataadozione` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `tipoattoadozione` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `nrattoadozione` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `id_lp_soggetti` int(11) NOT NULL COMMENT 'ID del responsabile di attuazione del piano',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `lp_polizzeassicurative` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `datapolizza` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `id_lp_modalita_stipula_polizza` int(11) NOT NULL,
  `id_tipo_impresa` int(11) NOT NULL COMMENT 'tipo impresa 1 singola 2 raggruppamento 3 professionista',
  `id_operatore` int(11) NOT NULL,
  `importo` double NOT NULL,
  `datascadenza` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `datasvincolo` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `id_lp_configurazioneente` int(11) NOT NULL,
  `utente_umod` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `data_umod` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `numeropolizza` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `agenzia` varchar(255) COLLATE latin1_general_cs NOT NULL COMMENT 'agenzia/compagnia che garantisce la polizza',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `lp_professionisti` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ragionesociale` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `cognome` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `nome` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `piva` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `cf` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `cf_estero` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `posizioneinps` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `posizioneinail` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `settoreattivitaeconomica` varchar(8) COLLATE latin1_general_cs NOT NULL COMMENT 'tabella decodifica C9',
  `formagiuridica` varchar(6) COLLATE latin1_general_cs NOT NULL COMMENT 'tabella c8',
  `datafinevalidita` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `indirizzo` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `civico` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `cap` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `istat_citta` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `istat_provincia` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `email` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `pec` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `utente_umod` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `data_umod` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `cf_leg_rappresentante` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `inail_sede` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `inps_sede` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `telefono` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `fax` varchar(100) COLLATE latin1_general_cs NOT NULL,
  `iscrizionemepa` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `ordine_professionale` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `sedioperative` varchar(200) COLLATE latin1_general_cs NOT NULL,
  `f_statoanag` int(11) NOT NULL DEFAULT '1' COMMENT 'stato anagrafica: 1-ATTIVO -1-CONGELATO(no attivo)',
  `recmaster` int(11) NOT NULL,
  `tipopersona` smallint(6) NOT NULL,
  `data_ins` varchar(20) COLLATE latin1_general_cs NOT NULL,
  `utente_ins` varchar(255) COLLATE latin1_general_cs NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `lp_progressivi` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `chiave` varchar(40) COLLATE latin1_general_cs NOT NULL COMMENT 'chiave progressivo',
  `descrizione` text COLLATE latin1_general_cs NOT NULL COMMENT 'descrizione progressivo',
  `valore` int(11) NOT NULL COMMENT 'valore progressivo',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs COMMENT='tabella prenotazione progressivi';
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `lp_proroghe` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_progetto` varchar(68) COLLATE latin1_general_cs NOT NULL,
  `datarichiestaproroga` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `totggproroga` int(11) NOT NULL,
  `nr_protrichiesta` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `data_inizioproroga` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `esitoproroga` varchar(255) COLLATE latin1_general_cs NOT NULL COMMENT 'decodicifa CONCESSA - NON CONCESSA',
  `motivoproroga` text COLLATE latin1_general_cs NOT NULL,
  `utente_umod` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `data_umod` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `id_proroga` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `lp_qte_cronoprogamma` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `idvoceqte` int(11) NOT NULL COMMENT 'FK lp_quadroeconomico_dettaglio',
  `idbase` int(11) NOT NULL,
  `idpadre` int(11) NOT NULL,
  `idapprova` int(11) NOT NULL COMMENT 'FK lp_qte_attoapprova',
  `descr` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `note` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `cp_sdtinizio` varchar(20) COLLATE latin1_general_cs NOT NULL,
  `cp_sdtfine` varchar(20) COLLATE latin1_general_cs NOT NULL,
  `cp_importo` float NOT NULL,
  `cs_sdtinizio` varchar(20) COLLATE latin1_general_cs NOT NULL,
  `cs_sdtfine` varchar(20) COLLATE latin1_general_cs NOT NULL,
  `cs_importo` float NOT NULL,
  `cp_cs_linked` int(11) NOT NULL DEFAULT '1' COMMENT 'flag per CASSA (cs) == COMPETENZA (cp)',
  `utentecre` varchar(50) COLLATE latin1_general_cs NOT NULL,
  `datacre` varchar(20) COLLATE latin1_general_cs NOT NULL,
  `utenteumod` varchar(50) COLLATE latin1_general_cs NOT NULL,
  `dataumod` varchar(20) COLLATE latin1_general_cs NOT NULL,
  `trashed` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `idvoceqte` (`idvoceqte`),
  KEY `idbase` (`idbase`),
  KEY `trashed` (`trashed`),
  KEY `trashed_2` (`trashed`),
  KEY `idvoceqte_2` (`idvoceqte`),
  KEY `idbase_2` (`idbase`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `lp_qte_cronoprogramma` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `idvoceqte` int(11) NOT NULL COMMENT 'FK lp_quadroeconomico_dettaglio',
  `idbase` int(11) NOT NULL,
  `idpadre` int(11) NOT NULL,
  `idapprova` int(11) NOT NULL COMMENT 'FK lp_qte_attoapprova',
  `descr` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `note` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `cp_sdtinizio` varchar(20) COLLATE latin1_general_cs NOT NULL,
  `cp_sdtfine` varchar(20) COLLATE latin1_general_cs NOT NULL,
  `cp_ngg` int(11) NOT NULL DEFAULT '0' COMMENT 'CP - numero giorni competenza',
  `cp_importo` float NOT NULL,
  `cs_sdtinizio` varchar(20) COLLATE latin1_general_cs NOT NULL,
  `cs_sdtfine` varchar(20) COLLATE latin1_general_cs NOT NULL,
  `cs_importo` float NOT NULL,
  `cp_cs_linked` int(11) NOT NULL DEFAULT '1' COMMENT 'flag per CASSA (cs) == COMPETENZA (cp): 1-LINKED   0-NON LINKED',
  `utentecre` varchar(50) COLLATE latin1_general_cs NOT NULL,
  `datacre` varchar(20) COLLATE latin1_general_cs NOT NULL,
  `utenteumod` varchar(50) COLLATE latin1_general_cs NOT NULL,
  `dataumod` varchar(20) COLLATE latin1_general_cs NOT NULL,
  `trashed` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `idvoceqte` (`idvoceqte`),
  KEY `idbase` (`idbase`),
  KEY `trashed` (`trashed`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `lp_qte_statoblocco` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_a21` int(11) NOT NULL,
  `stato` int(11) NOT NULL DEFAULT '1' COMMENT 'STATO BLOCCO: 1-APERTO 2-BLOCCATO',
  `attosdt` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `attoestremi` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `note` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `utentecre` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `datacre` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `utenteumod` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `dataumod` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `attivo` int(11) NOT NULL DEFAULT '1',
  `trashed` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `id_a21` (`id_a21`),
  KEY `attivo` (`attivo`,`trashed`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `lp_quadroeconomico_dettaglio` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_progetto` varchar(68) COLLATE latin1_general_cs NOT NULL,
  `id_a11` int(11) NOT NULL,
  `vocespesa` int(11) NOT NULL,
  `importo` double NOT NULL,
  `descrizione` text COLLATE latin1_general_cs NOT NULL,
  `aliquotaiva` double NOT NULL,
  `tipologiavocespesa` varchar(1) COLLATE latin1_general_cs NOT NULL,
  `id_a21` int(11) NOT NULL,
  `id_lp_vocispesa_tipoimporto` int(11) NOT NULL,
  `componente` int(11) NOT NULL COMMENT 'la voce della base sta può essere 0=Lavori 1=Servizi 2=Forniture',
  `id_vocepadre` int(11) NOT NULL,
  `utente_umod` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `data_umod` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `importoiva` double NOT NULL,
  `automatismo_attivo` varchar(255) COLLATE latin1_general_cs NOT NULL COMMENT '0 attivo 1 non attivo',
  `automatismo` varchar(255) COLLATE latin1_general_cs NOT NULL COMMENT 'assume 1: che contiene il calcolo automatico IVA e 2 per calcolo automatico contributi',
  `aliquotaoneri` int(11) NOT NULL,
  `importooneri` double NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `lp_quadroeconomico_impegni` (
  `id` int(11) NOT NULL,
  `id_lp_quadroeconomico` int(11) NOT NULL,
  `id_lp_finanziaria_impegni` int(11) NOT NULL,
  `id_impresa` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `lp_raggruppamento` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `descrizione` varchar(250) COLLATE latin1_general_cs DEFAULT NULL,
  `tiporaggruppamento_simog` varchar(255) COLLATE latin1_general_cs NOT NULL COMMENT 'tipo raggruppamento simog TipoAggiudicataio type - Tabella lp_simog_tiporaggruppamento',
  `TipoRaggOrizVert` varchar(1) COLLATE latin1_general_cs NOT NULL,
  `trashed` int(11) NOT NULL,
  `f_statoanag` int(11) NOT NULL DEFAULT '1' COMMENT 'stato anagrafica: 1-ATTIVO -1-CONGELATO(no attivo)',
  `recmaster` int(11) NOT NULL,
  `data_ins` varchar(20) COLLATE latin1_general_cs NOT NULL,
  `data_umod` varchar(20) COLLATE latin1_general_cs NOT NULL,
  `utente_ins` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `utente_umod` varchar(255) COLLATE latin1_general_cs NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `lp_raggruppamentoimpresa` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `fragg` int(11) NOT NULL DEFAULT '1' COMMENT '1 impresa - 3 prof',
  `id_lp_impresa` int(11) NOT NULL,
  `id_lp_ruoloavcp` int(11) NOT NULL,
  `id_lp_raggruppamento` int(11) NOT NULL,
  `TipoRaggOrizVert` varchar(1) COLLATE latin1_general_cs NOT NULL COMMENT 'O: orizzontane V: verticale',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `lp_rapportoconclusivo` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_soggetti_rapportoconclusivo` int(11) NOT NULL,
  `data_rapportoconclusivo` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `id_soggetti_appaltabilita` int(11) NOT NULL,
  `data_appaltabilita` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `id_soggetti_validazione` int(11) NOT NULL,
  `data_validazione` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `id_soggetto_verbalecantierabilita` int(11) NOT NULL,
  `data_acantierabilita` varchar(8) COLLATE latin1_general_cs NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `lp_ripresalavori` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_progetto` varchar(68) COLLATE latin1_general_cs NOT NULL,
  `dataripresa` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `nuovotermineultimazione` date NOT NULL,
  `utente_umod` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `data_umod` varchar(8) COLLATE latin1_general_cs NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `lp_riserve` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_progetto` int(11) NOT NULL,
  `id_cig` int(11) NOT NULL,
  `nr_riserva` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `datapresentazione` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `descrizione` text COLLATE latin1_general_cs NOT NULL,
  `dataquantificazione` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `richiestoimpresa` double NOT NULL,
  `id_a22` int(11) NOT NULL,
  `datatecnico` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `numerotecnico` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `propostatecnico` double NOT NULL,
  `oggetto` text COLLATE latin1_general_cs NOT NULL,
  `note` text COLLATE latin1_general_cs NOT NULL,
  `utente_umod` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `data_umod` varchar(8) COLLATE latin1_general_cs NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `lp_ruolo` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `codice` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `descrizione` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `codice_simog` varchar(255) COLLATE latin1_general_cs NOT NULL COMMENT 'decodifica ruoli ai fini SIMOG',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `lp_ruoloavcp` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `descr` varchar(250) COLLATE latin1_general_cs DEFAULT NULL,
  `codice_simog` varchar(255) COLLATE latin1_general_cs NOT NULL COMMENT 'codice simog RuoloAggiudicatarioType',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `lp_rup` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_progetto` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `id_lp_soggetti` int(11) NOT NULL,
  `datainizio` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `datafine` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `id_tipoattoamministrativo` int(11) NOT NULL COMMENT 'id lp_tipoattoamministrativo',
  `dataatto` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `nratto` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `utente_umod` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `data_umod` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `id_allegato` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `lp_sal_pagamenti` (
  `id` int(11) NOT NULL,
  `id_a22` int(11) NOT NULL,
  `id_a15` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `lp_simog_condizionipn_no_bando` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `codice` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `descrizione` text COLLATE latin1_general_cs NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs COMMENT='si riferisce alla tabella simog condizioni proc neg. senza b';
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `lp_simog_criteriaggiudicazione` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `codice` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `descrizione` varchar(255) COLLATE latin1_general_cs NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `lp_simog_dettaglioprocedura` (
  `id` int(11) NOT NULL,
  `id_progetto` int(11) NOT NULL,
  `id_a28_proceduraaggiudicazione` int(11) NOT NULL,
  `astaelettronica` int(11) NOT NULL COMMENT 'S: si   N: no  -  dati x simog',
  `criteriaggiudicazione` int(11) NOT NULL COMMENT 'tabella di decodifica lp_criteriaggiudicazione',
  `proceduraurgenza` varchar(255) COLLATE latin1_general_cs NOT NULL COMMENT 'S: si    N: no',
  `preinformazione` varchar(255) COLLATE latin1_general_cs NOT NULL COMMENT 'S: si  N: no',
  `termineridottopreinformazione` varchar(255) COLLATE latin1_general_cs NOT NULL COMMENT 'S: si  N: no',
  `modalitasettorispeciali` text COLLATE latin1_general_cs NOT NULL,
  `criterisel_sa` varchar(255) COLLATE latin1_general_cs NOT NULL COMMENT 'S: si  N: no',
  `sistemaqualificazioneinterno` varchar(255) COLLATE latin1_general_cs NOT NULL COMMENT 'S: si  N: no',
  `utente_umod` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `data_umod` varchar(8) COLLATE latin1_general_cs NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `lp_simog_motivazionivariante` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `codice` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `descrizione` varchar(255) COLLATE latin1_general_cs NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `lp_simog_sospensioni` (
  `id` int(11) NOT NULL,
  `id_progetto` varchar(68) COLLATE latin1_general_cs NOT NULL,
  `dataverbale` varchar(8) COLLATE latin1_general_cs NOT NULL COMMENT 'no bda',
  `numeroverbale` varchar(255) COLLATE latin1_general_cs NOT NULL COMMENT 'no bdap',
  `superoquartotempo` varchar(255) COLLATE latin1_general_cs NOT NULL COMMENT 'S si o N no supero del quarto di tempo',
  `utente_umod` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `data_umod` varchar(8) COLLATE latin1_general_cs NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `lp_simog_tipologialavoro` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `codice` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `descrizione` varchar(255) COLLATE latin1_general_cs NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `lp_simog_tiporaggruppamento` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `descrizione` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `codice_simog` varchar(255) COLLATE latin1_general_cs NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `lp_soggetti` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `cognome` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `nome` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `email` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `titolo` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `telefono` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `cf` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `luogonascita` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `datanascita` varchar(10) COLLATE latin1_general_cs NOT NULL,
  `pswsimog` varchar(255) COLLATE latin1_general_cs NOT NULL COMMENT 'password per accedere a SIMOG',
  `organizzazione` varchar(1) COLLATE latin1_general_cs NOT NULL COMMENT 'I: interno  E: esterno',
  `domicilio_nazione` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `residenza_citta` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `domicilio_citta` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `sorgente` int(11) NOT NULL,
  `f_curricula` varchar(10) COLLATE latin1_general_cs NOT NULL,
  `f_dichiarazioni` varchar(10) COLLATE latin1_general_cs NOT NULL,
  `pec` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `residenza_indirizzo` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `residenza_istatcitta` varchar(6) COLLATE latin1_general_cs NOT NULL,
  `residenza_cap` varchar(10) COLLATE latin1_general_cs NOT NULL,
  `residenza_nazione` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `domicilio_indirizzo` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `domicilio_istatcitta` varchar(6) COLLATE latin1_general_cs NOT NULL,
  `domicilio_cap` varchar(10) COLLATE latin1_general_cs NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `lp_soggetti_a21_iterprocedurale` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `a21_iterprocedurale_id` int(11) NOT NULL,
  `id_progetto` varchar(68) COLLATE latin1_general_cs NOT NULL,
  `lp_ruolo_id` int(11) NOT NULL,
  `lp_soggetti_id` int(11) NOT NULL,
  `esterno` varchar(255) COLLATE latin1_general_cs NOT NULL COMMENT 'assume I interno e punto alla tabella dei soggetti e E sterno punto alla tab dei professionisti',
  `dataincarico` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `importoincarico` double NOT NULL,
  `note` text COLLATE latin1_general_cs NOT NULL,
  `utente_umod` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `data_umod` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `id_allegato` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `lp_soggetti_esecuzione` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_progetto` varchar(68) COLLATE latin1_general_cs NOT NULL,
  `esterno` varchar(255) COLLATE latin1_general_cs DEFAULT NULL,
  `lp_ruolo_id` int(11) NOT NULL,
  `lp_soggetti_id` int(11) NOT NULL,
  `dataincarico` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `importoincarico` double NOT NULL,
  `note` text COLLATE latin1_general_cs NOT NULL,
  `datainizio` varchar(8) COLLATE latin1_general_cs NOT NULL COMMENT 'data inizio incarico',
  `datafine` varchar(8) COLLATE latin1_general_cs NOT NULL COMMENT 'data termine incarico',
  `utente_umod` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `data_umod` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `id_allegato` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `lp_subappalto` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_lp_impresa` int(11) NOT NULL,
  `tipoatto` int(11) NOT NULL COMMENT 'decodifica lp_tipoattoamministrativo',
  `data_atto` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `numero_atto` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `id_progetto` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `lp_categorie_opere_id` int(11) NOT NULL,
  `importo` double NOT NULL,
  `id_a28` int(11) NOT NULL,
  `cig` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `utente_umod` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `data_umod` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `oggetto` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `note` text COLLATE latin1_general_cs NOT NULL,
  `id_allegato` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `lp_terminiesecuzione` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_progetto` varchar(68) COLLATE latin1_general_cs NOT NULL,
  `id_a28_proceduraaggiudicazione` int(11) NOT NULL,
  `datastipulacontratto` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `dataesecutiviacontratto` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `importocauzione` double NOT NULL,
  `dataverbaleavvioesecuzione` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `datainiziolavori` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `giorniultimazionelavori` int(11) NOT NULL,
  `dataterminelavoriprevista` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `repcontratto` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `consegnariservalegge` varchar(255) COLLATE latin1_general_cs NOT NULL COMMENT 'S: si   N: no',
  `utente_umod` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `data_umod` varchar(8) COLLATE latin1_general_cs NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `lp_tipo_ruolo_impresa` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `descrizione` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `trashed` int(11) NOT NULL DEFAULT '0',
  `codice` varchar(255) COLLATE latin1_general_cs NOT NULL COMMENT 'campo a disposizione per eventuale codifiche',
  `data_umod` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `utente_umod` varchar(255) COLLATE latin1_general_cs NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `lp_tipoaffidamentoavcp` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `descrizione` varchar(50) COLLATE latin1_general_cs NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `lp_tipoattoamministrativo` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `codice` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `descrizione` varchar(255) COLLATE latin1_general_cs NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `lp_tipoparere` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_lp_entepreposto` int(11) NOT NULL,
  `codice` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `descrizione` varchar(255) COLLATE latin1_general_cs NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `lp_verifica` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_progetto` varchar(68) COLLATE latin1_general_cs NOT NULL,
  `id_a21` int(11) NOT NULL,
  `esitoverifica` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `dataverifica` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `utente_umod` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `data_umodo` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `id_verifica` int(11) NOT NULL,
  `tecnico` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `id_lp_soggetti` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `lp_verifica_lp_soggetti_a21_iterprocedurale` (
  `id_lp_verifica` int(11) NOT NULL,
  `id_lp_soggetti_a21_iterprocedurale` int(11) NOT NULL,
  `id_lp_ruolo_a21_iterprocedurale` int(11) NOT NULL COMMENT 'id del ruolo assunot dal soggetto nella fase di verifica'
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `lp_visure_infocamere` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `cf` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `ragionesociale` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `xml` text COLLATE latin1_general_cs NOT NULL,
  `utente_umod` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `data_umod` varchar(8) COLLATE latin1_general_cs NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `lp_vociappalto` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `codice` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `descrizione` varchar(255) COLLATE latin1_general_cs NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `lp_vocispesa_tipoimporto` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `descrizione` varchar(255) COLLATE latin1_general_cs NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pt18_fs_schedaa` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_lp_pianobiennale` int(11) NOT NULL COMMENT 'id programma biennale',
  `tipologiarisorse` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `disponibilitaprimoanno` double NOT NULL,
  `disponibilitasecondoanno` double NOT NULL,
  `importotot` double NOT NULL,
  `utente_umod` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `data_umod` varchar(255) COLLATE latin1_general_cs NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pt18_fs_schedab` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `cui` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `annoprogrammazione` varchar(255) COLLATE latin1_general_cs NOT NULL COMMENT 'prima annualita del primo programma nel quale l intervento e stato prog.',
  `annoavvio` varchar(255) COLLATE latin1_general_cs NOT NULL COMMENT 'annualita nella quale si prevede di dare avvio',
  `cup` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `acq_ricompreso` varchar(2) COLLATE latin1_general_cs NOT NULL COMMENT 'S: si  N: no acquisto ricompreso nell importo complessivo di un lavoro...',
  `cui_altro` varchar(255) COLLATE latin1_general_cs NOT NULL COMMENT 'cup di altro lavoro o acquisizione ..',
  `lottofunzionale` varchar(2) COLLATE latin1_general_cs NOT NULL COMMENT 'S: si  N: no',
  `ambito_geo` varchar(255) COLLATE latin1_general_cs NOT NULL COMMENT 'ambito geografico di esecuzioooon dell acquisto (regione)',
  `settore` varchar(255) COLLATE latin1_general_cs NOT NULL COMMENT 'forniture o servizi',
  `cpv` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `descrizioneacquisto` text COLLATE latin1_general_cs NOT NULL,
  `priorita` varchar(10) COLLATE latin1_general_cs NOT NULL COMMENT 'tabella B1',
  `cognomenomerup` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `duratacontratto` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `tipoacquisto` varchar(2) COLLATE latin1_general_cs NOT NULL COMMENT 'S: si  N: no  SI=relativo a nuovo affidamento di contratto in essere',
  `primoanno` double NOT NULL,
  `secondoanno` double NOT NULL,
  `annisucc` double NOT NULL,
  `tot` double NOT NULL,
  `cap_priv` double NOT NULL,
  `tipologia` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `uasa` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `denominazione_uasa` varchar(255) COLLATE latin1_general_cs NOT NULL COMMENT 'centrale di commit. che farà laa gara',
  `acq_agg` varchar(10) COLLATE latin1_general_cs NOT NULL COMMENT 'acquisto aggiuntivo o variato a seguito di modifiche della prg tabella B2',
  `utente_umod` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `data_umod` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `id_lp_piano18` int(11) NOT NULL COMMENT 'id piano biennale',
  `codfisamm` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `id_lp_soggetti` int(11) NOT NULL COMMENT 'id del resp del proc',
  `riproponi` int(11) NOT NULL DEFAULT '0' COMMENT '0: riprononi negli anni successivi 1: non riproporre negli anni successivi',
  `riproponimotivo` varchar(255) COLLATE latin1_general_cs NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs COMMENT=' scheda servizi e forniture nr B';
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pt18_fs_schedab_risorse` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_pt18_fs_schedab` int(11) NOT NULL,
  `imp_vincolato` double NOT NULL,
  `imp_mutuo` double NOT NULL,
  `imp_capitalipriv` double NOT NULL,
  `imp_trasfimmobili` double NOT NULL,
  `imp_bilancio` double NOT NULL,
  `imp_altro` double NOT NULL,
  `imp_acquisibili90` double NOT NULL COMMENT 'nuova voce 2018 art 3 l 22 dicembre 1990 nr 403',
  `tot` double NOT NULL,
  `utente_umod` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `data_umod` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `annualita` int(11) NOT NULL COMMENT '1: primo anno 2: secondo anno  3: terzo anno',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pt18_l_livmin` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_progetto` int(11) NOT NULL,
  `cui` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `codinternoamm` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `cup` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `anno_prev_ini` int(11) NOT NULL,
  `id_lp_soggetti` int(11) NOT NULL,
  `nominativorup` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `lottofunzionale` varchar(2) COLLATE latin1_general_cs NOT NULL COMMENT 'S: si  N: no',
  `lavorocomplesso` varchar(2) COLLATE latin1_general_cs NOT NULL COMMENT 'S: si  N: no',
  `istatregione` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `istatprovincia` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `istatcomune` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `codicenuts` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `tipologia` varchar(255) COLLATE latin1_general_cs NOT NULL COMMENT 'tabella D1',
  `settore` varchar(255) COLLATE latin1_general_cs NOT NULL COMMENT 'tabella D2',
  `descrizioneintervento` text COLLATE latin1_general_cs NOT NULL,
  `priorita` varchar(10) COLLATE latin1_general_cs NOT NULL COMMENT 'tabella D3',
  `primoanno` double NOT NULL,
  `secondoanno` double NOT NULL,
  `terzoanno` double NOT NULL,
  `costiannualitasuccessive` double NOT NULL,
  `importotriennio` double NOT NULL COMMENT 'somma dell''importo del 1-2-3 anno',
  `tot_immobili_schedac_coll` double NOT NULL,
  `scad_temp_uso_finanziamento` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `importoprivato` double NOT NULL,
  `tipologiaprivato` varchar(255) COLLATE latin1_general_cs NOT NULL COMMENT 'tabella 4',
  `interv_agg_modo` varchar(255) COLLATE latin1_general_cs NOT NULL COMMENT 'tabella D5',
  `annoavvio` int(11) NOT NULL COMMENT 'anno in cui si pensa di avviare l''opera',
  `utente_umod` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `data_umod` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `finalita` varchar(255) COLLATE latin1_general_cs NOT NULL COMMENT 'tabella E1',
  `conf_urb` varchar(2) COLLATE latin1_general_cs NOT NULL COMMENT 'S: si N: no',
  `ver_vincoli` varchar(2) COLLATE latin1_general_cs NOT NULL COMMENT 'S: si N: no',
  `liv_prog` varchar(255) COLLATE latin1_general_cs NOT NULL COMMENT 'tabella E2',
  `uasa` varchar(255) COLLATE latin1_general_cs NOT NULL COMMENT 'tabella E2',
  `denominazione_uasa` varchar(255) COLLATE latin1_general_cs NOT NULL COMMENT 'centrale di commit. che farà laa gara',
  `id_lp_piano18` int(11) NOT NULL COMMENT 'ID  piano triennale',
  `id_lp_bisogni` int(11) NOT NULL COMMENT 'id tabella dei bisogni',
  `riproponi` int(11) NOT NULL DEFAULT '0' COMMENT '0: riprononi negli anni successivi 1: non riproporre negli anni successivi',
  `riproponimotivo` varchar(255) COLLATE latin1_general_cs NOT NULL COMMENT 'motivo per il quale non si è riproposto -Scheda F',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs COMMENT='tabella utilizzata per la scheda E e scheda D';
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pt18_l_livmin_risorse` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_pt18_l_livmin` int(11) NOT NULL,
  `imp_vincolato` double NOT NULL,
  `imp_mutuo` double NOT NULL,
  `imp_capitalipriv` double NOT NULL,
  `imp_trasfimmobili` double NOT NULL,
  `imp_bilancio` double NOT NULL,
  `imp_altro` double NOT NULL,
  `imp_acquisibili90` double NOT NULL COMMENT 'nuova voce 2018 art 3 l 22 dicembre 1990 nr 403',
  `tot` double NOT NULL,
  `utente_umod` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `data_umod` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `annualita` int(11) NOT NULL COMMENT '1: primo anno 2: secondo anno  3: terzo anno',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pt18_l_schedaa` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_lp_pianotriennale` int(11) NOT NULL,
  `tipologiarisorse` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `disponibilitaprimoanno` double NOT NULL,
  `disponibilitasecondoanno` double NOT NULL,
  `disponibilitaterzoanno` double NOT NULL,
  `importotot` double NOT NULL,
  `utente_umod` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `data_umod` varchar(255) COLLATE latin1_general_cs NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pt18_l_schedab` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `cup` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `id_pt18_l_schedad` int(11) NOT NULL,
  `determinazione_amm` text COLLATE latin1_general_cs NOT NULL,
  `id_pt18_l_tabellab2` varchar(11) COLLATE latin1_general_cs NOT NULL COMMENT 'ambito di interesse',
  `anno_ult_qe` varchar(10) COLLATE latin1_general_cs NOT NULL,
  `importo_compl_interv` double NOT NULL,
  `importo_compl_lavori` double NOT NULL,
  `importo_x_ultim_lav` double NOT NULL,
  `importo_ultim_sal` double NOT NULL,
  `perc_avanz_lav` varchar(10) COLLATE latin1_general_cs NOT NULL,
  `id_pt18_l_tabellab3` varchar(11) COLLATE latin1_general_cs NOT NULL COMMENT 'causa per la quale l opera è incomp',
  `parz_fruibile` varchar(10) COLLATE latin1_general_cs NOT NULL,
  `id_pt18_l_tabellab4` varchar(11) COLLATE latin1_general_cs NOT NULL COMMENT 'stato realizz.',
  `poss_util` varchar(10) COLLATE latin1_general_cs NOT NULL COMMENT 'possibile utilizzo dell opera',
  `id_pt18_l_tabellab5` varchar(11) COLLATE latin1_general_cs NOT NULL COMMENT 'destinazione uso.',
  `cessione_tit_corr` varchar(10) COLLATE latin1_general_cs NOT NULL COMMENT 'cessione atitolo di corrispettivo per la realizzazione..',
  `vendita_demoliz` varchar(10) COLLATE latin1_general_cs NOT NULL COMMENT 'vendita ovvero demolizione',
  `parte_infrastr` varchar(10) COLLATE latin1_general_cs NOT NULL COMMENT 'parte di infrastruttura di rete',
  `utente_umod` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `data_umod` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `id_lp_piano18` int(11) NOT NULL COMMENT 'ID  piano triennale',
  `descrizioneintervento` varchar(255) COLLATE latin1_general_cs NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs COMMENT='tabella utilizzata per la scheda B';
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pt18_l_schedac` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `codunivocoimm` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `cui` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `cup` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `descrizioneimmobile` text COLLATE latin1_general_cs NOT NULL,
  `istatregione` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `istatprovincia` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `istatcomune` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `codicenuts` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `id_pt18_l_tabellac1` int(11) NOT NULL COMMENT 'trasf. immbobile titolo corr.',
  `id_pt18_l_tabellac2` int(11) NOT NULL COMMENT 'immob. dispo',
  `id_pt18_l_tabellac3` int(11) NOT NULL COMMENT 'gia incl. nel prog. di dismissione',
  `id_pt18_l_tabellac4` int(11) NOT NULL COMMENT 'disponb. derivanta da opera incompiuta',
  `primoanno` double NOT NULL,
  `secondoanno` double NOT NULL,
  `terzoanno` double NOT NULL,
  `totale` double NOT NULL,
  `utente_umod` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `data_umod` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `id_lp_pianotriennale` int(11) NOT NULL COMMENT 'ID  piano triennale',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs COMMENT='tabella utilizzata per la scheda E e scheda D';
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pt18_l_schedad` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_progetto` int(11) NOT NULL,
  `cui` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `codinternoamm` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `cup` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `anno_prev_ini` int(11) NOT NULL,
  `id_lp_soggetti` int(11) NOT NULL,
  `nominativorup` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `lottofunzionale` varchar(2) COLLATE latin1_general_cs NOT NULL COMMENT 'S: si  N: no',
  `lavorocomplesso` varchar(2) COLLATE latin1_general_cs NOT NULL COMMENT 'S: si  N: no',
  `istatregione` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `istatprovincia` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `istatcomune` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `codicenuts` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `tipologia` varchar(255) COLLATE latin1_general_cs NOT NULL COMMENT 'tabella D1',
  `settore` varchar(255) COLLATE latin1_general_cs NOT NULL COMMENT 'tabella D2',
  `descrizioneintervento` text COLLATE latin1_general_cs NOT NULL,
  `priorita` varchar(10) COLLATE latin1_general_cs NOT NULL COMMENT 'tabella D3',
  `primoanno` double NOT NULL,
  `secondoanno` double NOT NULL,
  `terzoanno` double NOT NULL,
  `costiannualitasuccessive` double NOT NULL,
  `importotriennio` double NOT NULL COMMENT 'somma dell''importo del 1-2-3 anno',
  `tot_immobili_schedac_coll` double NOT NULL,
  `scad_temp_uso_finanziamento` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `importoprivato` double NOT NULL,
  `tipologiaprivato` varchar(255) COLLATE latin1_general_cs NOT NULL COMMENT 'tabella 4',
  `interv_agg_modo` varchar(255) COLLATE latin1_general_cs NOT NULL COMMENT 'tabella D5',
  `annoavvio` int(11) NOT NULL COMMENT 'anno in cui si pensa di avviare l''opera',
  `utente_umod` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `data_umod` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `finalita` varchar(255) COLLATE latin1_general_cs NOT NULL COMMENT 'tabella E1',
  `conf_urb` varchar(2) COLLATE latin1_general_cs NOT NULL COMMENT 'S: si N: no',
  `ver_vincoli` varchar(2) COLLATE latin1_general_cs NOT NULL COMMENT 'S: si N: no',
  `liv_prog` varchar(255) COLLATE latin1_general_cs NOT NULL COMMENT 'tabella E2',
  `uasa` varchar(255) COLLATE latin1_general_cs NOT NULL COMMENT 'tabella E2',
  `denominazione_uasa` varchar(255) COLLATE latin1_general_cs NOT NULL COMMENT 'centrale di commit. che farà laa gara',
  `id_lp_piano18` int(11) NOT NULL COMMENT 'ID  piano triennale',
  `id_lp_bisogni` int(11) NOT NULL COMMENT 'id tabella dei bisogni',
  `riproponi` int(11) NOT NULL DEFAULT '0' COMMENT '0: riprononi negli anni successivi 1: non riproporre negli anni successivi',
  `riproponimotivo` varchar(255) COLLATE latin1_general_cs NOT NULL COMMENT 'motivo per il quale non si è riproposto -Scheda F',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs COMMENT='tabella utilizzata per la scheda E e scheda D';
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pt18_l_schedad_risorse` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_pt18_l_schedad` int(11) NOT NULL,
  `imp_vincolato` double NOT NULL,
  `imp_mutuo` double NOT NULL,
  `imp_capitalipriv` double NOT NULL,
  `imp_trasfimmobili` double NOT NULL,
  `imp_bilancio` double NOT NULL,
  `imp_altro` double NOT NULL,
  `imp_acquisibili90` double NOT NULL COMMENT 'nuova voce 2018 art 3 l 22 dicembre 1990 nr 403',
  `tot` double NOT NULL,
  `utente_umod` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `data_umod` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `annualita` int(11) NOT NULL COMMENT '1: primo anno 2: secondo anno  3: terzo anno',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pt18_l_schedaf` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_pt18_l_schedad` int(11) NOT NULL,
  `motivo` text COLLATE latin1_general_cs NOT NULL COMMENT 'motivo per il quale l intervento non e riproposto',
  `utente_umod` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `data_umod` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `id_lp_pianotriennale` int(11) NOT NULL COMMENT 'ID  piano triennale',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs COMMENT=' eredita tutte le info dalla scheda D';
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pt18_l_tabellab1` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `codice` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `descrizione` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `cod_aliprog` varchar(255) COLLATE latin1_general_cs NOT NULL COMMENT 'codice per ALIPROG4',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pt18_l_tabellab2` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `codice` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `descrizione` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `cod_aliprog` int(11) NOT NULL COMMENT 'codice per ALIPROG4',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pt18_l_tabellab3` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `codice` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `descrizione` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `cod_aliprog` int(11) NOT NULL COMMENT 'codice per ALIPROG4',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pt18_l_tabellab4` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `codice` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `descrizione` varchar(500) COLLATE latin1_general_cs NOT NULL,
  `cod_aliprog` int(11) NOT NULL COMMENT 'codice per ALIPROG4',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pt18_l_tabellab5` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `codice` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `descrizione` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `cod_aliprog` int(11) NOT NULL COMMENT 'codice per ALIPROG4',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pt18_l_tabellac1` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `codice` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `descrizione` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `cod_aliprog` int(11) NOT NULL COMMENT 'codice per ALIPROG4',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pt18_l_tabellac2` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `codice` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `descrizione` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `cod_aliprog` int(11) NOT NULL COMMENT 'codice per ALIPROG4',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pt18_l_tabellac3` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `codice` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `descrizione` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `cod_aliprog` int(11) NOT NULL COMMENT 'codice per ALIPROG4',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pt18_l_tabellac4` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `codice` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `descrizione` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `cod_aliprog` int(11) NOT NULL COMMENT 'codice per ALIPROG4',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pt18_l_tabellad1` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `codice` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `descrizione` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `cod_aliprog` int(11) NOT NULL COMMENT 'codice per ALIPROG4',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pt18_l_tabellad2` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `codice` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `descrizione` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `cod_aliprog` int(11) NOT NULL COMMENT 'codice per ALIPROG4',
  `codsettore` varchar(250) COLLATE latin1_general_cs NOT NULL,
  `descsettore` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `codsottosettore` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `descsottosettore` varchar(255) COLLATE latin1_general_cs NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pt18_l_tabellad3` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `codice` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `descrizione` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `cod_aliprog` int(11) NOT NULL COMMENT 'codice per ALIPROG4',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pt18_l_tabellad4` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `codice` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `descrizione` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `cod_aliprog` int(11) NOT NULL COMMENT 'codice per ALIPROG4',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pt18_l_tabellad5` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `codice` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `descrizione` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `cod_aliprog` int(11) NOT NULL COMMENT 'codice per ALIPROG4',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pt18_l_tabellae1` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `codice` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `descrizione` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `cod_aliprog` int(11) NOT NULL COMMENT 'codice per ALIPROG4',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pt18_l_tabellae2` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `codice` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `descrizione` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `cod_aliprog` int(11) NOT NULL COMMENT 'codice per ALIPROG4',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pt18_sf_tabellab1` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `codice` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `descrizione` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `cod_aliprog` int(11) NOT NULL COMMENT 'codice per ALIPROG4',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pt18_sf_tabellab2` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `codice` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `descrizione` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `cod_aliprog` int(11) NOT NULL COMMENT 'codice per ALIPROG4',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pt18_tipologiarisorse` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `codice` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `descrizione` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `cod_aliprog` int(11) NOT NULL COMMENT 'codice per ALIPROG4',
  `tipo` int(11) NOT NULL COMMENT 'L:lavori S:servizi F: forniture T:tutte',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pt_scheda1` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_lp_pianotriennale` int(11) NOT NULL,
  `id_scheda2` int(11) NOT NULL,
  `tipologiarisorse` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `disponibilitaprimoanno` double NOT NULL,
  `disponibilitasecondoanno` double NOT NULL,
  `disponibilitaterzoanno` double NOT NULL,
  `importotot` double NOT NULL,
  `accantonamento` double NOT NULL,
  `utente_umod` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `data_umod` varchar(255) COLLATE latin1_general_cs NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pt_scheda2` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_lp_bisogni` int(11) NOT NULL,
  `id_progetto` int(11) NOT NULL,
  `istatregione` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `istatprovincia` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `istatcomune` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `codicenuts` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `tipologia` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `categoria` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `descrizioneintervento` text COLLATE latin1_general_cs NOT NULL,
  `priorita` varchar(10) COLLATE latin1_general_cs NOT NULL,
  `primoanno` double NOT NULL,
  `secondoanno` double NOT NULL,
  `terzoanno` double NOT NULL,
  `importotriennio` double NOT NULL COMMENT 'somma dell''importo del 1-2-3 anno',
  `cessioneimmobili` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `importoprivato` double NOT NULL,
  `tipologiaprivato` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `annoavvio` int(11) NOT NULL COMMENT 'anno in cui si pensa di avviare l''opera',
  `utente_umod` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `data_umod` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `nr_progressivo` int(11) NOT NULL,
  `id_pt_scheda2` int(11) NOT NULL,
  `annoprogrammazione` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `riferimentointerno` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `note` text COLLATE latin1_general_cs NOT NULL,
  `piuannualita` varchar(1) COLLATE latin1_general_cs NOT NULL COMMENT 'S: si è un opera che si svolge su piu annualità N: no',
  `id_lp_pianotriennale` int(11) NOT NULL COMMENT 'ID piano triennale',
  `cod_interv_amm` varchar(255) COLLATE latin1_general_cs NOT NULL COMMENT 'campo utilizzato per mettere un codice dell''amministazione',
  `nomerup` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `cognomerup` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `codicerup` varchar(255) COLLATE latin1_general_cs NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pt_scheda2_risorse` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_pt_scheda2` int(11) NOT NULL,
  `imp_vincolato` double NOT NULL,
  `imp_mutuo` double NOT NULL,
  `imp_capitalipriv` double NOT NULL,
  `imp_trasfimmobili` double NOT NULL,
  `imp_bilancio` double NOT NULL,
  `imp_altro` double NOT NULL,
  `tot` double NOT NULL,
  `utente_umod` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `data_umod` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `annualita` int(11) NOT NULL COMMENT '1: primo anno 2: secondo anno  3: terzo anno',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pt_scheda2b` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_progetto` int(11) NOT NULL,
  `descrizioneimmobile` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `solodirittosuperficie` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `pienaproprieta` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `primoanno` double NOT NULL,
  `secondoanno` double NOT NULL,
  `terzoanno` double NOT NULL,
  `utente_umod` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `data_umod` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `riferimentointerno` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `annoprogrammazione` varchar(255) COLLATE latin1_general_cs NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pt_scheda3` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_progetto` int(11) NOT NULL,
  `nr_progressivo` int(11) NOT NULL COMMENT 'numero progressivo previsto nel piano annuale',
  `annoprogrammazione` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `cui` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `cup` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `descrizioneintervento` text COLLATE latin1_general_cs NOT NULL,
  `cpv` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `cognomerup` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `nomerup` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `codicerup` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `importoannualita` double NOT NULL,
  `importotot` double NOT NULL,
  `finalita` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `priorita` int(11) NOT NULL,
  `urb` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `amb` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `statoprogettazione` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `trimestreannoinizio` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `trimestreannofine` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `annoinizio` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `annofine` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `utente_umod` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `data_umod` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `codriferimentotri` int(11) NOT NULL,
  `descrizionecontratto` text COLLATE latin1_general_cs NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pt_scheda4` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_progetto` int(11) NOT NULL,
  `servizi` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `forniture` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `cui` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `cpv` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `descrizionecontratto` text COLLATE latin1_general_cs NOT NULL,
  `cognomerup` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `nomerup` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `codicerup` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `importopresunto` double NOT NULL,
  `fonterisorsefinanziarie` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `nr_progressivo` int(11) NOT NULL,
  `annoprogrammazione` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `id_lp_pianotriennale` int(11) NOT NULL,
  `imp_stanz_bilancio` double NOT NULL,
  `imp_risorse_regionali` double NOT NULL,
  `imp_risorse_stato` double NOT NULL,
  `imp_risorse_ue` double NOT NULL,
  `imp_mutuo` double NOT NULL,
  `imp_capitali_priv` double NOT NULL,
  `imp_altro` double NOT NULL,
  `utente_umod` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `data_umod` varchar(255) COLLATE latin1_general_cs NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pt_tabella1` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `codice` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `descrizione` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `cod_aliprog` int(11) NOT NULL COMMENT 'codice per ALIPROG4',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pt_tabella2` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `codice1` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `codice2` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `descrizione` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `cod_aliprog` int(11) NOT NULL COMMENT 'codice per ALIPROG4',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pt_tabella3` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `codice` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `descrizione` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `cod_aliprog` int(11) NOT NULL COMMENT 'codice per ALIPROG4',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pt_tabella4` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `codice` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `descrizione` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `cod_aliprog` int(11) NOT NULL COMMENT 'codice per ALIPROG4',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pt_tabella5` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `codice` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `descrizione` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `cod_aliprog` int(11) NOT NULL COMMENT 'codice per ALIPROG4',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pt_tabella6` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `codice` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `descrizione` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `cod_aliprog` int(11) NOT NULL COMMENT 'codice per ALIPROG4',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sc_cig` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `cig` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `codiceFattispecieContrattuale` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `importo` double NOT NULL,
  `oggetto` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `codiceProceduraSceltaContraente` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `codiceClassificazioneGara` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `cigAccordoQuadro` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `cup` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `motivo_rich_cig_comuni` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `motivo_rich_cig_catmerc` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `categoria_merc` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `utente_umod` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `data_umod` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `stato` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `utente_annullamento` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `data_annullamento` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `cf_rup` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `data_operazione_ws` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `sc_tipo_indicatore` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `data_cig_ws` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `data_ins` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `utente_ins` varchar(50) COLLATE latin1_general_cs NOT NULL,
  PRIMARY KEY (`id`),
  KEY `cig` (`cig`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sc_classificazione_gara` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `codice` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `descrizione` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `data_ultimo_agg` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `trashed` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sc_fattispecie_contrattuale` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `codice` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `descrizione` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `data_ultimo_agg` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `trashed` int(11) NOT NULL,
  `dataend` varchar(255) COLLATE latin1_general_cs NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sc_motivi_comuni` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `codice` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `descrizione` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `data_ultimo_agg` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `trashed` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sc_tipo_indicatore` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `codice` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `descrizione` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `data_ultimo_agg` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `trashed` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sc_tipo_merceologia_agg` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `codice` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `descrizione` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `data_ultimo_agg` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `trashed` int(11) NOT NULL,
  `dataend` varchar(255) COLLATE latin1_general_cs NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sc_tipo_procedura` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `codice` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `descrizione` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `data_ultimo_agg` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `trashed` int(11) NOT NULL,
  `dataend` varchar(255) COLLATE latin1_general_cs NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sim_accessoutenti` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `cognome` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `nome` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `cf` varchar(255) COLLATE latin1_general_cs NOT NULL COMMENT 'il CF è anche lo username per l''accesso a simog',
  `password` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `abilitato` varchar(1) COLLATE latin1_general_cs NOT NULL COMMENT '0 = utente abilitato  1= utente non abilitato',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sim_artesclusione` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `codice` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `descrizione` varchar(255) COLLATE latin1_general_cs NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sim_categsat` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `codice` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `descrizione` varchar(255) COLLATE latin1_general_cs NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sim_classeimporto` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `codice` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `descrizione` varchar(255) COLLATE latin1_general_cs NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sim_coltipodoc` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `codice` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `descrizione` varchar(255) COLLATE latin1_general_cs NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sim_credenziali_utenti` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username_software` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `id_simog_accessoutenti` int(11) NOT NULL,
  `id_simog_strutture_accreditate` int(11) NOT NULL COMMENT 'identificativo della struttura di simog con cui prendere un CIG',
  `stato` varchar(1) COLLATE latin1_general_cs NOT NULL COMMENT '0= UTENTE ATTIVO    1= UTENTE NON ATTIVO',
  `contesto` int(11) NOT NULL COMMENT 'ambito di interoperabilità - 0: simog 1: smartcig',
  `index_collaborazione` int(11) NOT NULL DEFAULT '0' COMMENT 'index di collaborazione utilizzato da SIMOG',
  `utente_umod` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `data_umod` varchar(8) COLLATE latin1_general_cs NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sim_esitocollaudo` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `codice` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `descrizione` varchar(255) COLLATE latin1_general_cs NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sim_esitoprocedura` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `codice` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `descrizione` varchar(255) COLLATE latin1_general_cs NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sim_flagesitocollaudo` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `codice` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `descrizione` varchar(255) COLLATE latin1_general_cs NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sim_flagritardo` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `codice` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `descrizione` varchar(255) COLLATE latin1_general_cs NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sim_modogara` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `codice` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `descrizione` varchar(255) COLLATE latin1_general_cs NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sim_modoindizione` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `codice` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `descrizione` varchar(255) COLLATE latin1_general_cs NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sim_modorealizzazione` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `codice` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `descrizione` varchar(255) COLLATE latin1_general_cs NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sim_modoriaggiud` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `codice` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `descrizione` varchar(255) COLLATE latin1_general_cs NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sim_motivicancellazione` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `codice` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `descrizione` varchar(255) COLLATE latin1_general_cs NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sim_motivivariazione` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `codice` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `descrizione` varchar(255) COLLATE latin1_general_cs NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sim_motivivariazionesat` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `codice` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `descrizione` varchar(255) COLLATE latin1_general_cs NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sim_motivointerruzione` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `codice` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `descrizione` varchar(255) COLLATE latin1_general_cs NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sim_motivorisoluzione` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `codice` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `descrizione` varchar(255) COLLATE latin1_general_cs NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sim_motivosospensione` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `codice` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `descrizione` varchar(255) COLLATE latin1_general_cs NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sim_motivovariante` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `codice` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `descrizione` varchar(255) COLLATE latin1_general_cs NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sim_parametriconnessione` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `certificato` varchar(255) COLLATE latin1_general_cs NOT NULL COMMENT 'path e nome del certificato client accreditato simog',
  `url` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `passwordcertificato` varchar(255) COLLATE latin1_general_cs NOT NULL COMMENT 'password del certificato',
  `ws_smartcig_dati` text COLLATE latin1_general_cs NOT NULL,
  `ws_smartcig_tabelle` text COLLATE latin1_general_cs NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sim_ruoloaggiudicatario` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `codice` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `descrizione` varchar(255) COLLATE latin1_general_cs NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sim_ruoloresponsabile` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `codice` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `descrizione` varchar(255) COLLATE latin1_general_cs NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sim_session_query` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `utente_umod` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `data_umod` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `resultxml` text COLLATE latin1_general_cs NOT NULL,
  `cig` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `datainterrogazione` varchar(255) COLLATE latin1_general_cs NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sim_session_ticket` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ticket` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `utente` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `timelimit` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `dataticket` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `utente_umod` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `data_umod` varchar(8) COLLATE latin1_general_cs NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sim_statoavcpass` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `codice` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `descrizione` varchar(255) COLLATE latin1_general_cs NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sim_statocig` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `codice` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `descrizione` varchar(255) COLLATE latin1_general_cs NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sim_tipoappalto` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `codice` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `descrizione` varchar(255) COLLATE latin1_general_cs NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sim_tipofinanziamento` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `codice` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `descrizione` varchar(255) COLLATE latin1_general_cs NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sim_tipologiaprocedura` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `codice` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `descrizione` varchar(255) COLLATE latin1_general_cs NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sim_tipologiasat` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `codice` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `descrizione` varchar(255) COLLATE latin1_general_cs NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sim_tipoprestazione` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `codice` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `descrizione` varchar(255) COLLATE latin1_general_cs NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sim_tipostrumento` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `codice` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `descrizione` varchar(255) COLLATE latin1_general_cs NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `simog_accessoutenti` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `cognome` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `nome` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `cf` varchar(255) COLLATE latin1_general_cs NOT NULL COMMENT 'il CF è anche lo username per l''accesso a simog',
  `password` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `abilitato` varchar(1) COLLATE latin1_general_cs NOT NULL COMMENT '0 = utente abilitato  1= utente non abilitato',
  `denom_amministrazione` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `denom_stazione_appaltante` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `id_stazione_appaltante` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `cf_amministrazione` varchar(255) COLLATE latin1_general_cs NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `simog_gara` (
  `id` int(11) NOT NULL,
  `utente_umod` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `data_umod` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `gestione` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `oggetto` varchar(1024) COLLATE latin1_general_cs NOT NULL,
  `id_stazione_appaltante` varchar(40) COLLATE latin1_general_cs NOT NULL,
  `denom_stazione_appaltante` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `cf_amministrazione` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `denom_amministrazione` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `cf_utente` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `id_gara` int(11) NOT NULL,
  `importo_gara` double NOT NULL,
  `importo_sa_gara` double NOT NULL,
  `data_cancellazione_gara` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `data_termine_pagamento` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `data_comun` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `data_inib_pagam` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `data_conferma_gara` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `tipo_scheda` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `modo_indizione` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `modo_realizzazione` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `id_motivazione` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `note_canc` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `cig_acc_quadro` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `numero_lotti` int(11) NOT NULL,
  `data_creazione` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `id_osservatorio` varchar(1000) COLLATE latin1_general_cs NOT NULL,
  `id_stato_gara` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `data_perfezionamento_bando` int(11) NOT NULL,
  `urgenza_dl133` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `provv_presa_carico` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `motivo_rich_cig_comuni` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `motivo_rich_cig` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `escluso_avcpass` varchar(255) COLLATE latin1_general_cs NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `simog_strutture_accreditate` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `denom_amministrazione` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `denom_stazione_appaltante` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `id_stazione_appaltante` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `cf_amministrazione` varchar(255) COLLATE latin1_general_cs NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `struttura_gruppo` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `contesto` varchar(20) COLLATE latin1_general_cs NOT NULL,
  `idpadre` int(11) NOT NULL,
  `nome` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `descrizione` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `trashed` int(11) NOT NULL DEFAULT '0',
  `utente_umod` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `data_umod` varchar(255) COLLATE latin1_general_cs NOT NULL,
  PRIMARY KEY (`id`),
  KEY `trashed` (`trashed`),
  KEY `contesto` (`contesto`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `struttura_membri` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `idgruppo` int(11) NOT NULL,
  `username` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `dataini` varchar(10) COLLATE latin1_general_cs NOT NULL,
  `dataend` varchar(10) COLLATE latin1_general_cs NOT NULL,
  `trashed` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `idgruppo` (`idgruppo`,`username`,`dataini`,`dataend`,`trashed`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tr_elencoopere` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_progetto` varchar(68) COLLATE latin1_general_cs NOT NULL,
  `cup` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `descrizione` text COLLATE latin1_general_cs NOT NULL,
  `ultimatrbdap` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `id_tr_elencotrasmissioni` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tr_elencotrasmissioni` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `datarilevazioneal` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `datascadenzainvio` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `note` text COLLATE latin1_general_cs NOT NULL,
  `datagenerazione` varchar(30) COLLATE latin1_general_cs NOT NULL,
  `nomefileinvio` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `vlock` text COLLATE latin1_general_cs NOT NULL COMMENT 'serial array rowid dei record presi per la tx',
  `statotrasmissione` varchar(2) COLLATE latin1_general_cs NOT NULL COMMENT '0: non generato 1: trasmissione tipo simulazione 2: trasmissione definitiva 3: CONFERMA trasmissione definitiva',
  `statogenerazione` int(11) NOT NULL DEFAULT '0' COMMENT '-1: Errori nella creazione del file 1: Esportazione creata senza errori',
  `gestione` varchar(255) COLLATE latin1_general_cs NOT NULL DEFAULT '',
  `txdata` text COLLATE latin1_general_cs NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tr_esitibdap_ftp` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `utente_umod` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `data_umod` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `nomefile` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `esito` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `txcollegata` int(11) NOT NULL COMMENT 'id trasmissione che ha generato la rendicontazione',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `unimod_codicinegozio` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `cod` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `danti` varchar(225) COLLATE latin1_general_cs NOT NULL,
  `aventi` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `iva` varchar(225) COLLATE latin1_general_cs NOT NULL,
  `valore` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `des` varchar(255) COLLATE latin1_general_cs NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `unimod_codicitributo` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `cod` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `tipo` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `compensazione` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `entrate` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `convenzione` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `tavolare` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `des` varchar(255) COLLATE latin1_general_cs NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `unimod_comuni` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `cod` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `provincia` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `nuova` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `soppresso` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `des` varchar(400) COLLATE latin1_general_cs NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `unimod_province` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `cod` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `des` varchar(400) COLLATE latin1_general_cs NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `unimod_qualificarappresentante` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `codice` varchar(10) COLLATE latin1_general_cs NOT NULL,
  `descrizione` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `descrizione_1` varchar(255) COLLATE latin1_general_cs NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `unimod_tipoallegato` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `codice` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `descrizione` varchar(255) COLLATE latin1_general_cs NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `unimod_tipopu` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `codice` varchar(10) COLLATE latin1_general_cs NOT NULL,
  `descrizione` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `descrizione_1` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `unimod_tipotitolo` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `codice` varchar(10) COLLATE latin1_general_cs NOT NULL,
  `descrizione` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `descrizione_1` varchar(255) COLLATE latin1_general_cs NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `unimod_ufficientrate` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `cod` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `tipo` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `soppresso` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `des` varchar(500) COLLATE latin1_general_cs NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `w_controllotrienni` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `token` varchar(50) COLLATE latin1_general_cs NOT NULL,
  `utente` varchar(20) COLLATE latin1_general_cs NOT NULL,
  `data` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `ora` varchar(4) COLLATE latin1_general_cs NOT NULL,
  `nomereport` varchar(30) COLLATE latin1_general_cs NOT NULL,
  `ent_vinc_1` double NOT NULL,
  `mutuo_1` double NOT NULL,
  `ent_cp_1` double NOT NULL,
  `trs_imm_1` double NOT NULL,
  `staz_bil_1` double NOT NULL,
  `altro_1` double NOT NULL,
  `ent_vinc_2` double NOT NULL,
  `mutuo_2` double NOT NULL,
  `ent_cp_2` double NOT NULL,
  `trs_imm_2` double NOT NULL,
  `staz_bil_2` double NOT NULL,
  `altro_2` double NOT NULL,
  `ent_vinc_3` double NOT NULL,
  `mutuo_3` double NOT NULL,
  `ent_cp_3` double NOT NULL,
  `trs_imm_3` double NOT NULL,
  `staz_bil_3` double NOT NULL,
  `altro_3` double NOT NULL,
  `note` text COLLATE latin1_general_cs NOT NULL,
  `rup` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `id_scheda2` int(11) NOT NULL,
  `descrizioneintervento` varchar(255) COLLATE latin1_general_cs NOT NULL,
  `progressivo` smallint(6) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
