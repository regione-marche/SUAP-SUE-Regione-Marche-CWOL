/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ALIQUOTE` (
  `ROWID` int(11) NOT NULL AUTO_INCREMENT,
  `ANNO` varchar(8) COLLATE latin1_general_cs NOT NULL COMMENT 'Anno Aliquote',
  `SEQUENZA` smallint(6) NOT NULL COMMENT 'Sequenza Controlli',
  `CATEGORIA` varchar(6) COLLATE latin1_general_cs NOT NULL COMMENT 'Categoria',
  `ALIQUOTA` double NOT NULL COMMENT 'Aliquota ICI/IMU',
  `CONDIZIONE` varchar(500) COLLATE latin1_general_cs NOT NULL COMMENT 'Condizione Logica di attribuzione aliquota',
  `RIDTASI` double NOT NULL COMMENT 'RIDUZIONE TASI',
  PRIMARY KEY (`ROWID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ANACAT` (
  `ROWID` int(11) NOT NULL AUTO_INCREMENT,
  `CATCOD` varchar(4) COLLATE latin1_general_cs NOT NULL,
  `CATDES` varchar(40) COLLATE latin1_general_cs NOT NULL,
  `CATCOE` double NOT NULL,
  `CATIMU` double NOT NULL,
  `CATDAL` varchar(8) COLLATE latin1_general_cs NOT NULL COMMENT 'VALIDA DAL',
  `CATAL` varchar(8) COLLATE latin1_general_cs NOT NULL COMMENT 'VALIDA AL',
  PRIMARY KEY (`ROWID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ANACIT` (
  `ROWID` int(11) NOT NULL AUTO_INCREMENT,
  `CODCIT` varchar(6) COLLATE latin1_general_cs NOT NULL,
  `RESID` varchar(26) COLLATE latin1_general_cs NOT NULL,
  `FRACAP` varchar(6) COLLATE latin1_general_cs NOT NULL,
  PRIMARY KEY (`ROWID`),
  UNIQUE KEY `K_MASTER` (`CODCIT`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ANAGALTR` (
  `ROWID` int(11) NOT NULL AUTO_INCREMENT,
  `ALTRNPR` double NOT NULL,
  `ALTRPROT` double NOT NULL,
  `ALTRCOG` varchar(120) COLLATE latin1_general_cs NOT NULL,
  `ALTRNOM` varchar(20) COLLATE latin1_general_cs NOT NULL,
  `ALTRIND` varchar(36) COLLATE latin1_general_cs NOT NULL,
  `ALTRCAP` varchar(6) COLLATE latin1_general_cs NOT NULL,
  `ALTRCOM` varchar(30) COLLATE latin1_general_cs NOT NULL,
  `ALTRPRO` varchar(2) COLLATE latin1_general_cs NOT NULL,
  `ALTRCF` varchar(16) COLLATE latin1_general_cs NOT NULL,
  `ALTRCOFI` varchar(16) COLLATE latin1_general_cs NOT NULL,
  `ALTRDNO` varchar(8) COLLATE latin1_general_cs NOT NULL COMMENT 'data notifica',
  `ALTRDRI` varchar(8) COLLATE latin1_general_cs NOT NULL COMMENT 'data ricezione',
  PRIMARY KEY (`ROWID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ANAGRA` (
  `ROWID` int(11) NOT NULL AUTO_INCREMENT,
  `ANACOD` varchar(16) COLLATE latin1_general_cs NOT NULL,
  `ANAIDE` varchar(10) COLLATE latin1_general_cs NOT NULL,
  `ANACOR` double NOT NULL,
  `ANACOG` varchar(60) COLLATE latin1_general_cs NOT NULL,
  `ANANOM` varchar(20) COLLATE latin1_general_cs NOT NULL,
  `ANADNA` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `ANASEX` varchar(2) COLLATE latin1_general_cs NOT NULL,
  `ANACNA` varchar(30) COLLATE latin1_general_cs NOT NULL,
  `ANAPNA` varchar(2) COLLATE latin1_general_cs NOT NULL,
  `ANAIND` varchar(36) COLLATE latin1_general_cs NOT NULL,
  `ANACOM` varchar(30) COLLATE latin1_general_cs NOT NULL,
  `ANAPRO` varchar(2) COLLATE latin1_general_cs NOT NULL,
  `ANACAP` varchar(6) COLLATE latin1_general_cs NOT NULL,
  `ANAPTE` varchar(4) COLLATE latin1_general_cs NOT NULL,
  `ANANTE` varchar(10) COLLATE latin1_general_cs NOT NULL,
  `ANANNO` varchar(4) COLLATE latin1_general_cs NOT NULL,
  `ANAPEG` smallint(6) NOT NULL,
  `DIDENO` varchar(60) COLLATE latin1_general_cs NOT NULL,
  `DIDCFI` varchar(16) COLLATE latin1_general_cs NOT NULL,
  `DIDIND` varchar(36) COLLATE latin1_general_cs NOT NULL,
  `DIDLOC` varchar(30) COLLATE latin1_general_cs NOT NULL,
  `DIDCAP` varchar(6) COLLATE latin1_general_cs NOT NULL,
  `DIDPRO` varchar(2) COLLATE latin1_general_cs NOT NULL,
  `ANAFLG` smallint(6) NOT NULL,
  `ANADDE` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `ANAFIL__1` double NOT NULL,
  `ANAFIL__2` double NOT NULL,
  `ANAFIL__3` double NOT NULL,
  `ANAFIA__1` varchar(30) COLLATE latin1_general_cs NOT NULL,
  `ANAFIA__2` varchar(30) COLLATE latin1_general_cs NOT NULL,
  `ANAFIA__3` varchar(30) COLLATE latin1_general_cs NOT NULL,
  `CODIND` varchar(6) COLLATE latin1_general_cs NOT NULL,
  `CIVICO` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `COBENA` varchar(4) COLLATE latin1_general_cs NOT NULL,
  `ANABEL` varchar(4) COLLATE latin1_general_cs NOT NULL,
  `DIDCIN` varchar(6) COLLATE latin1_general_cs NOT NULL,
  `DIDCIV` varchar(6) COLLATE latin1_general_cs NOT NULL,
  `DIDBEL` varchar(4) COLLATE latin1_general_cs NOT NULL,
  `DATINS` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `EMAIL` varchar(70) COLLATE latin1_general_cs NOT NULL,
  `ANAIRE` double NOT NULL,
  `ANANOT` text COLLATE latin1_general_cs NOT NULL,
  `AIREDAL` varchar(8) COLLATE latin1_general_cs NOT NULL COMMENT 'ISCRITTO AIRE DAL',
  `AIREAL` varchar(8) COLLATE latin1_general_cs NOT NULL COMMENT 'ISCRITTO AIRE AL',
  PRIMARY KEY (`ROWID`),
  UNIQUE KEY `K_MASTER` (`ANACOD`),
  KEY `I_ANACOG` (`ANACOG`,`ANANOM`),
  KEY `I_CODRIS` (`ANACOR`),
  KEY `I_SOGCAT` (`ANAIDE`),
  KEY `I_ANACOD` (`ANACOD`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
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
  PRIMARY KEY (`ROWID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ANINDI` (
  `ROWID` int(11) NOT NULL AUTO_INCREMENT,
  `CODIND` varchar(6) COLLATE latin1_general_cs NOT NULL,
  `VIAANA` varchar(6) COLLATE latin1_general_cs NOT NULL,
  `INDIR` varchar(30) COLLATE latin1_general_cs NOT NULL,
  `GIRO` smallint(6) NOT NULL,
  `SPECIE` varchar(16) COLLATE latin1_general_cs NOT NULL,
  `INDFIL` varchar(10) COLLATE latin1_general_cs NOT NULL,
  PRIMARY KEY (`ROWID`),
  KEY `A_CODIND` (`CODIND`),
  KEY `A_VIAANA` (`VIAANA`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `AVVADED` (
  `ROWID` int(11) NOT NULL AUTO_INCREMENT,
  `ADEDAVV` double NOT NULL,
  `ADEDAPC` varchar(2) COLLATE latin1_general_cs NOT NULL,
  `ADEDPRO` varchar(10) COLLATE latin1_general_cs NOT NULL,
  `ADEDDAT` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `ADEDALL` text COLLATE latin1_general_cs NOT NULL,
  `ADEDCOMP` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `ADEDNOTE` varchar(200) COLLATE latin1_general_cs NOT NULL COMMENT 'Note',
  `ADEDANN` varchar(4) COLLATE latin1_general_cs NOT NULL COMMENT 'ANNO AVVISO',
  `ADEDORACOMP` varchar(5) COLLATE latin1_general_cs NOT NULL,
  `TOKEN` varchar(100) COLLATE latin1_general_cs NOT NULL,
  `ADEDTESTO` varchar(50) COLLATE latin1_general_cs NOT NULL,
  PRIMARY KEY (`ROWID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `AVVADET` (
  `ROWID` int(11) NOT NULL AUTO_INCREMENT,
  `ADETAVV` double NOT NULL,
  `ADETCFI` varchar(16) COLLATE latin1_general_cs NOT NULL,
  `ADETANN` varchar(4) COLLATE latin1_general_cs NOT NULL,
  `ADETIST` int(11) NOT NULL,
  `ADETSOS` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `ADETCHI` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `ADETESI` int(11) NOT NULL,
  `ADETPRE` int(11) NOT NULL,
  `ADETULT` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `ADETPAG` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `ADETRED` int(11) NOT NULL COMMENT 'Atto Redatto',
  `ADETCOG` varchar(60) COLLATE latin1_general_cs NOT NULL,
  `ADETNOM` varchar(20) COLLATE latin1_general_cs NOT NULL,
  `ADETVIA` varchar(36) COLLATE latin1_general_cs NOT NULL,
  `ADETCIV` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `ADETCIT` varchar(30) COLLATE latin1_general_cs NOT NULL,
  `ADETCAP` varchar(6) COLLATE latin1_general_cs NOT NULL,
  `ADETPROV` varchar(2) COLLATE latin1_general_cs NOT NULL,
  `ADETPTE` varchar(4) COLLATE latin1_general_cs NOT NULL,
  `ADETNTE` varchar(10) COLLATE latin1_general_cs NOT NULL,
  `ADETMAIL` varchar(70) COLLATE latin1_general_cs NOT NULL,
  PRIMARY KEY (`ROWID`),
  KEY `ID_KEY` (`ADETAVV`,`ADETANN`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `AVVADO` (
  `ROWID` int(11) NOT NULL,
  `AVANPR` double NOT NULL,
  `AVADDO` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `AVACDO` varchar(5) COLLATE latin1_general_cs NOT NULL,
  `AVAFILE` varchar(100) COLLATE latin1_general_cs NOT NULL,
  `AVAPROTINT` varchar(20) COLLATE latin1_general_cs NOT NULL,
  `AVATIPO` varchar(50) COLLATE latin1_general_cs NOT NULL,
  `AVAPROTEST` varchar(20) COLLATE latin1_general_cs NOT NULL,
  `AVADEST` varchar(50) COLLATE latin1_general_cs NOT NULL,
  `AVADESIND` varchar(50) COLLATE latin1_general_cs NOT NULL,
  `AVADESCAP` varchar(5) COLLATE latin1_general_cs NOT NULL,
  `AVADESCOM` varchar(50) COLLATE latin1_general_cs NOT NULL,
  `AVADESPROV` varchar(4) COLLATE latin1_general_cs NOT NULL,
  `AVANOTE` varchar(200) COLLATE latin1_general_cs NOT NULL,
  `AVAFUN` varchar(20) COLLATE latin1_general_cs NOT NULL,
  `AVARIF` varchar(80) COLLATE latin1_general_cs NOT NULL,
  PRIMARY KEY (`ROWID`),
  KEY `A_AVANPR` (`AVANPR`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `AVVCLASS` (
  `TIPOAVVISO` double NOT NULL,
  `TIPODETTAGLIO` smallint(6) NOT NULL,
  `CODICERIGA` smallint(6) NOT NULL,
  `CLASSE` varchar(10) COLLATE latin1_general_cs NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `AVVF24` (
  `ROWID` int(11) NOT NULL AUTO_INCREMENT,
  `F24NPR` double NOT NULL,
  `F24ANN` varchar(4) COLLATE latin1_general_cs NOT NULL,
  `F24CFI` varchar(16) COLLATE latin1_general_cs NOT NULL,
  `F24CTER` varchar(6) COLLATE latin1_general_cs NOT NULL,
  `F24CARE` varchar(6) COLLATE latin1_general_cs NOT NULL,
  `F24CABI` varchar(6) COLLATE latin1_general_cs NOT NULL,
  `F24CALT` varchar(6) COLLATE latin1_general_cs NOT NULL,
  `F24CSAN` varchar(6) COLLATE latin1_general_cs NOT NULL,
  `F24CINT` varchar(6) COLLATE latin1_general_cs NOT NULL,
  `F24ITER` double NOT NULL,
  `F24IARE` double NOT NULL,
  `F24IABI` double NOT NULL,
  `F24IALT` double NOT NULL,
  `F24ISAN` double NOT NULL,
  `F24IINT` double NOT NULL,
  `F24DET` double NOT NULL,
  `F24ACC` smallint(6) NOT NULL,
  `F24SAL` smallint(6) NOT NULL,
  `F24IMM` smallint(6) NOT NULL,
  `F24NOT` double NOT NULL,
  PRIMARY KEY (`ROWID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `AVVIMM` (
  `ROWID` int(11) NOT NULL AUTO_INCREMENT,
  `IMMAVV` double NOT NULL,
  `IMMCFI` varchar(16) COLLATE latin1_general_cs NOT NULL,
  `IMMANN` varchar(4) COLLATE latin1_general_cs NOT NULL,
  `IMMTIP` varchar(2) COLLATE latin1_general_cs NOT NULL,
  `IMMFOG` varchar(6) COLLATE latin1_general_cs NOT NULL,
  `IMMNUM` varchar(6) COLLATE latin1_general_cs NOT NULL,
  `IMMSUB` varchar(4) COLLATE latin1_general_cs NOT NULL,
  `IMMCAT` varchar(4) COLLATE latin1_general_cs NOT NULL,
  `IMMVAL` double NOT NULL,
  `IMMPOS` double NOT NULL,
  `IMMMPO` smallint(6) NOT NULL,
  `IMMMQA` double NOT NULL,
  `IMMVQA` double NOT NULL,
  `IMMFLA` smallint(6) NOT NULL,
  `IMMTOK` varchar(40) COLLATE latin1_general_cs NOT NULL,
  `IMMSEL` int(11) NOT NULL,
  `IMMKEY` double NOT NULL,
  `IMMSTA` varchar(20) COLLATE latin1_general_cs NOT NULL,
  `IMMSELACC` int(11) NOT NULL,
  PRIMARY KEY (`ROWID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `AVVISD` (
  `ROWID` int(11) NOT NULL AUTO_INCREMENT,
  `AVDNPR` double NOT NULL,
  `AVDCOD` smallint(6) NOT NULL,
  `AVDIMP` double NOT NULL,
  `AVDRIG` smallint(6) NOT NULL,
  PRIMARY KEY (`ROWID`),
  KEY `A_AVDNPR` (`AVDNPR`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `AVVISM` (
  `ROWID` int(11) NOT NULL AUTO_INCREMENT,
  `AVVNPR` double NOT NULL,
  `AVVRIG` double NOT NULL,
  `AVVCOM` varchar(200) COLLATE latin1_general_cs NOT NULL,
  PRIMARY KEY (`ROWID`),
  KEY `A_AVVNPR` (`AVVNPR`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `AVVIST` (
  `ROWID` int(11) NOT NULL AUTO_INCREMENT,
  `AVVCFI` varchar(16) COLLATE latin1_general_cs NOT NULL COMMENT 'CODICE FISCALE',
  `AVVANN` varchar(4) COLLATE latin1_general_cs NOT NULL COMMENT 'ANNO ACCERTAMENTO',
  `AVVNPR` double NOT NULL COMMENT 'N.PROTOCOLLO  ANNO+NUMERO',
  `AVVDAT` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `AVVTIP` smallint(6) NOT NULL,
  `AVVFLG` varchar(6) COLLATE latin1_general_cs NOT NULL,
  `AVVDNO` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `AVVNOT` varchar(50) COLLATE latin1_general_cs NOT NULL,
  `AVVDES` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `AVVTNO` varchar(2) COLLATE latin1_general_cs NOT NULL,
  `AVVSTA` smallint(6) NOT NULL,
  `AVVPRO` varchar(12) COLLATE latin1_general_cs NOT NULL,
  `AVVDPR` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `AVVLPA` varchar(2) COLLATE latin1_general_cs NOT NULL,
  `AVVMPA` varchar(30) COLLATE latin1_general_cs NOT NULL,
  `AVVDPA` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `AVVIPA` double NOT NULL,
  `AVVFLN__1` double NOT NULL,
  `AVVFLN__2` double NOT NULL,
  `AVVFLN__3` double NOT NULL,
  `AVVFLA__1` varchar(100) COLLATE latin1_general_cs NOT NULL,
  `AVVFLA__2` varchar(100) COLLATE latin1_general_cs NOT NULL,
  `AVVFLA__3` varchar(100) COLLATE latin1_general_cs NOT NULL,
  `AVVPMI` double NOT NULL,
  `AVVPMA` double NOT NULL,
  `AVVPIR` double NOT NULL,
  `AVVPFI` double NOT NULL,
  `AVVDRI` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `AVVDN1` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `AVVDR1` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `AVVPROT` double NOT NULL,
  `AVVDET` double NOT NULL,
  `AVVMOT` text COLLATE latin1_general_cs NOT NULL,
  `AVVNOSANZ` smallint(6) NOT NULL,
  `MULTIPLO` double NOT NULL,
  `AVVDRU` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `AVVCARTELLA` varchar(50) COLLATE latin1_general_cs NOT NULL,
  `AVVDSPE` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `AVVNOTE` text COLLATE latin1_general_cs NOT NULL,
  `AVVDRIC` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `OLDAVVSTA` smallint(6) NOT NULL,
  `PROGRUOLO` double NOT NULL,
  `DATACOMUNICAZIONE` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `LISTADICARICO` double NOT NULL,
  `CODUTE` varchar(20) COLLATE latin1_general_cs NOT NULL,
  `DATAOPER` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `TIMEOPER` varchar(8) COLLATE latin1_general_cs NOT NULL,
  PRIMARY KEY (`ROWID`),
  KEY `A_AVVNPR` (`AVVNPR`),
  KEY `I_AVVCFI` (`AVVCFI`,`AVVANN`),
  KEY `I_NUMPRO` (`AVVDPR`,`AVVPRO`),
  KEY `I_ANNONUMERO` (`AVVANN`,`AVVNPR`),
  KEY `I_ANNOPROT` (`AVVANN`,`AVVPROT`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `AVVNOT` (
  `ROWID` int(11) NOT NULL AUTO_INCREMENT,
  `AVNNPR` double NOT NULL COMMENT 'n. avviso',
  `AVNANN` varchar(4) COLLATE latin1_general_cs NOT NULL COMMENT 'anno',
  `AVNDAT` varchar(8) COLLATE latin1_general_cs NOT NULL COMMENT 'data notifica',
  `AVNTIP` varchar(2) COLLATE latin1_general_cs NOT NULL COMMENT 'S/N/T',
  `AVNNOT` varchar(100) COLLATE latin1_general_cs NOT NULL COMMENT 'REGOLARE / CAD ...',
  `AVNSPE` double NOT NULL COMMENT 'spese di notifica',
  `AVNNDO` varchar(20) COLLATE latin1_general_cs NOT NULL COMMENT 'n. documento',
  `AVNSCA` varchar(10) COLLATE latin1_general_cs NOT NULL COMMENT 'scatola',
  `AVNBUS` varchar(10) COLLATE latin1_general_cs NOT NULL COMMENT 'busta',
  `AVNPDO` double NOT NULL COMMENT 'progressivo documento',
  `AVNBIN` longblob NOT NULL COMMENT 'file binario',
  `AVNFILE` varchar(100) COLLATE latin1_general_cs NOT NULL COMMENT 'file path',
  `AVNAUT` tinyint(4) NOT NULL COMMENT 'automatica',
  `AVNSOG` varchar(40) COLLATE latin1_general_cs NOT NULL,
  `AVNNOTE` text COLLATE latin1_general_cs NOT NULL COMMENT 'note',
  `AVNCF` varchar(16) COLLATE latin1_general_cs NOT NULL COMMENT 'CF soggetto',
  PRIMARY KEY (`ROWID`),
  UNIQUE KEY `AVNNPR` (`AVNNPR`,`AVNANN`,`AVNDAT`,`AVNCF`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs COMMENT='NOTIFICHE AVVISI';
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `AVVPAG` (
  `ROWID` int(11) NOT NULL,
  `AVPNPR` double NOT NULL,
  `AVPANN` varchar(4) COLLATE latin1_general_cs NOT NULL,
  `AVPDRIS` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `AVPSOM` double NOT NULL,
  `AVPTIPOPE` varchar(1) COLLATE latin1_general_cs NOT NULL,
  `AVPSOG` varchar(40) COLLATE latin1_general_cs NOT NULL,
  `AVPDRIV` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `AVPLET` varchar(1) COLLATE latin1_general_cs NOT NULL,
  `AVPDAV` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `AVPQUI` varchar(20) COLLATE latin1_general_cs NOT NULL,
  `AVPTIPDOC` varchar(1) COLLATE latin1_general_cs NOT NULL,
  `AVPERR` varchar(1) COLLATE latin1_general_cs NOT NULL,
  `AVPCONC` varchar(1) COLLATE latin1_general_cs NOT NULL,
  `AVPNOTE` varchar(200) COLLATE latin1_general_cs NOT NULL,
  `AVPFILE` varchar(100) COLLATE latin1_general_cs NOT NULL,
  `AVPBIN` longblob NOT NULL,
  PRIMARY KEY (`ROWID`),
  KEY `A_AVPNPR` (`AVPNPR`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `AVVRET` (
  `ROWID` int(11) NOT NULL AUTO_INCREMENT,
  `AVRNPR` double NOT NULL COMMENT 'PROTOCOLLO',
  `AVRANN` varchar(4) COLLATE latin1_general_cs NOT NULL,
  `AVRNUM` varchar(20) COLLATE latin1_general_cs NOT NULL,
  `AVRTIP` varchar(100) COLLATE latin1_general_cs NOT NULL,
  `AVRDNO` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `AVRIMP` double NOT NULL,
  `AVRSAN` double NOT NULL,
  `AVRSANRID` double NOT NULL,
  `AVRINT` double NOT NULL,
  `AVRSPE` double NOT NULL,
  `AVRTOT` double NOT NULL,
  PRIMARY KEY (`ROWID`),
  KEY `A_AVRNPR` (`AVRNPR`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs COMMENT='RETTIFICHE AVVISI';
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `AVVRIC` (
  `ROWID` int(11) NOT NULL,
  `AVCNPR` double NOT NULL,
  `AVCANN` varchar(4) COLLATE latin1_general_cs NOT NULL,
  `AVCDRI` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `AVCTIPO` varchar(30) COLLATE latin1_general_cs NOT NULL,
  `AVCORD` varchar(30) COLLATE latin1_general_cs NOT NULL,
  `AVCESITO` varchar(30) COLLATE latin1_general_cs NOT NULL,
  `AVCNOTE` varchar(250) COLLATE latin1_general_cs NOT NULL,
  `AVCSAN` double NOT NULL,
  `AVCSPESE` double NOT NULL,
  `AVCTOT` double NOT NULL,
  `AVCPROV` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `AVCDPR` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `AVCGIO` int(11) NOT NULL,
  `AVCSOMMA` double NOT NULL,
  `AVCSPESESN` double NOT NULL,
  `AVCSPESE1` double NOT NULL,
  `AVCSPESE2` double NOT NULL,
  `AVCALTRESPESE` double NOT NULL,
  `AVCSOMMAAVV` double NOT NULL,
  `AVCFILE` varchar(100) COLLATE latin1_general_cs NOT NULL,
  `AVCSOG` varchar(40) COLLATE latin1_general_cs NOT NULL,
  `AVCRIF` varchar(1) COLLATE latin1_general_cs NOT NULL,
  `AVCANNOPRA` varchar(4) COLLATE latin1_general_cs NOT NULL,
  `AVCPROGPRA` double NOT NULL,
  `AVCSOSP` varchar(1) COLLATE latin1_general_cs NOT NULL,
  `AVCGIUDICE` varchar(50) COLLATE latin1_general_cs NOT NULL,
  `AVCDATUD` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `AVCORAUD` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `AVCDATTRAPRE` varchar(8) COLLATE latin1_general_cs NOT NULL,
  PRIMARY KEY (`ROWID`),
  KEY `A_AVCNPR` (`AVCNPR`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `CATTEP` (
  `ROWID` int(11) NOT NULL AUTO_INCREMENT,
  `TPANNO` varchar(4) COLLATE latin1_general_cs NOT NULL,
  `TPPART` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `TPINTE` double NOT NULL,
  `TPNPAR` double NOT NULL,
  `TPNSUB` smallint(6) NOT NULL,
  `TPETTA` double NOT NULL,
  `TPARE` double NOT NULL,
  `TPCENT` double NOT NULL,
  `TPREDD` double NOT NULL,
  `TPREDA` double NOT NULL,
  `TPREDE` double NOT NULL,
  `TPREAE` double NOT NULL,
  `TPRIMM` double NOT NULL,
  PRIMARY KEY (`ROWID`),
  KEY `A_TPPART` (`TPPART`),
  KEY `A_TPRIMM` (`TPRIMM`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `CATTET` (
  `ROWID` int(11) NOT NULL AUTO_INCREMENT,
  `TTANNO` varchar(4) COLLATE latin1_general_cs NOT NULL,
  `TTPART` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `TTNOME` varchar(50) COLLATE latin1_general_cs NOT NULL,
  `TTCODF` varchar(16) COLLATE latin1_general_cs NOT NULL,
  `TTTITO` smallint(6) NOT NULL,
  `TTINFO` varchar(50) COLLATE latin1_general_cs NOT NULL,
  `TTNREC` smallint(6) NOT NULL,
  `TTIMMO` double NOT NULL,
  PRIMARY KEY (`ROWID`),
  KEY `A_TTPART` (`TTPART`),
  KEY `A_TTCODF` (`TTCODF`),
  KEY `A_TTIMMO` (`TTIMMO`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `CATTIT` (
  `ROWID` int(11) NOT NULL AUTO_INCREMENT,
  `TITCAT` smallint(6) NOT NULL,
  `DESTIT` varchar(54) COLLATE latin1_general_cs NOT NULL,
  PRIMARY KEY (`ROWID`),
  UNIQUE KEY `K_MASTER` (`TITCAT`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `CATURT` (
  `ROWID` int(11) NOT NULL AUTO_INCREMENT,
  `TUANNO` varchar(4) COLLATE latin1_general_cs NOT NULL,
  `TUPART` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `TUNOME` varchar(50) COLLATE latin1_general_cs NOT NULL,
  `TUCODF` varchar(16) COLLATE latin1_general_cs NOT NULL,
  `TUTITO` smallint(6) NOT NULL,
  `TUINFO` varchar(50) COLLATE latin1_general_cs NOT NULL,
  `TURIGA` smallint(6) NOT NULL,
  `TUIMMO` double NOT NULL,
  PRIMARY KEY (`ROWID`),
  KEY `A_TUPART` (`TUPART`),
  KEY `A_TUCODF` (`TUCODF`),
  KEY `A_TUIMMO` (`TUIMMO`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `CATURU` (
  `ROWID` int(11) NOT NULL AUTO_INCREMENT,
  `UIANNO` varchar(4) COLLATE latin1_general_cs NOT NULL,
  `UIPART` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `UISEZ` varchar(4) COLLATE latin1_general_cs NOT NULL,
  `UIFOG` varchar(6) COLLATE latin1_general_cs NOT NULL,
  `UINUM` varchar(6) COLLATE latin1_general_cs NOT NULL,
  `UISUB` varchar(4) COLLATE latin1_general_cs NOT NULL,
  `UICATE` varchar(4) COLLATE latin1_general_cs NOT NULL,
  `UICONS` double NOT NULL,
  `UIREDD` double NOT NULL,
  `UIREDE` double NOT NULL,
  `UIFLAG` smallint(6) NOT NULL,
  `UIIMMO` double NOT NULL,
  PRIMARY KEY (`ROWID`),
  KEY `A_UIPART` (`UIPART`),
  KEY `A_UIIMMO` (`UIIMMO`),
  KEY `I_CATURB` (`UISEZ`,`UIFOG`,`UINUM`,`UISUB`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `COMLOC` (
  `ROWID` int(11) NOT NULL AUTO_INCREMENT,
  `CLTIPO` int(11) NOT NULL,
  `CLFOGLIO` varchar(6) COLLATE latin1_general_cs NOT NULL,
  `CLNUMERO` varchar(6) COLLATE latin1_general_cs NOT NULL,
  `CLSUB` varchar(4) COLLATE latin1_general_cs NOT NULL,
  `CLCODFIS` varchar(16) COLLATE latin1_general_cs NOT NULL,
  `CLDAL` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `CLAL` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `CLCF` varchar(16) COLLATE latin1_general_cs NOT NULL,
  `CLCONC` varchar(6) COLLATE latin1_general_cs NOT NULL,
  `CLCOGNOM` varchar(30) COLLATE latin1_general_cs NOT NULL,
  `CLPAREN` varchar(10) COLLATE latin1_general_cs NOT NULL,
  PRIMARY KEY (`ROWID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `CTRANA1D` (
  `ROWID` int(11) NOT NULL AUTO_INCREMENT,
  `UTENTE` varchar(20) COLLATE latin1_general_cs NOT NULL,
  `DATA` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `ORA` varchar(4) COLLATE latin1_general_cs NOT NULL,
  `NOMEREPORT` varchar(30) COLLATE latin1_general_cs NOT NULL,
  `CODICEFISCALE` varchar(16) COLLATE latin1_general_cs NOT NULL,
  `DDEANN` varchar(4) COLLATE latin1_general_cs NOT NULL,
  `FOGLIO` varchar(5) COLLATE latin1_general_cs NOT NULL,
  `NUMERO` varchar(5) COLLATE latin1_general_cs NOT NULL,
  `SUBALTERNO` varchar(5) COLLATE latin1_general_cs NOT NULL,
  `DDECAT` varchar(4) COLLATE latin1_general_cs NOT NULL,
  `DDEREND` double NOT NULL,
  `DDEVAL` double NOT NULL,
  `DDEPER` double NOT NULL,
  `DDEMMP` double NOT NULL,
  `IMPOSTA` double NOT NULL,
  `ABIPRI` int(11) NOT NULL,
  `PERTINENZA` int(11) NOT NULL,
  `DETFIG` double NOT NULL,
  PRIMARY KEY (`ROWID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `CTRANA1T` (
  `ROWID` int(11) NOT NULL AUTO_INCREMENT,
  `UTENTE` varchar(20) COLLATE latin1_general_cs NOT NULL,
  `DATA` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `ORA` varchar(4) COLLATE latin1_general_cs NOT NULL,
  `NOMEREPORT` varchar(30) COLLATE latin1_general_cs NOT NULL,
  `CODICEFISCALE` varchar(16) COLLATE latin1_general_cs NOT NULL,
  `COGNOME` varchar(30) COLLATE latin1_general_cs NOT NULL,
  `NOME` varchar(20) COLLATE latin1_general_cs NOT NULL,
  `INDIRIZZO` varchar(30) COLLATE latin1_general_cs NOT NULL,
  `IMPOSTA_CITTADINO` double NOT NULL,
  `VERSATO_CITTADINO` double NOT NULL,
  `ABIPRI_CITTADINO` varchar(300) COLLATE latin1_general_cs NOT NULL,
  `CODICEFISCALE_CONIUGE` varchar(16) COLLATE latin1_general_cs NOT NULL,
  `CONIUGE` varchar(50) COLLATE latin1_general_cs NOT NULL,
  `INDIRIZZO_CONIUGE` varchar(30) COLLATE latin1_general_cs NOT NULL,
  `IMPOSTA_CONIUGE` double NOT NULL,
  `VERSATO_CONIUGE` double NOT NULL,
  `ABIPRI_CONIUGE` varchar(300) COLLATE latin1_general_cs NOT NULL,
  `CONTROLLO` varchar(30) COLLATE latin1_general_cs NOT NULL,
  `NOTE` text COLLATE latin1_general_cs NOT NULL,
  PRIMARY KEY (`ROWID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `DICDET` (
  `ROWID` int(11) NOT NULL AUTO_INCREMENT,
  `DDECFI` varchar(16) COLLATE latin1_general_cs NOT NULL,
  `DDEANN` varchar(4) COLLATE latin1_general_cs NOT NULL,
  `DDECNT` smallint(6) NOT NULL,
  `DDEDAT` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `DDETIP` varchar(2) COLLATE latin1_general_cs NOT NULL,
  `DDEIND` varchar(60) COLLATE latin1_general_cs NOT NULL,
  `DDERIF` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `DDEIMM` double NOT NULL,
  `DDEIDE` varchar(10) COLLATE latin1_general_cs NOT NULL,
  `DDEPAR` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `DDESEZ` varchar(4) COLLATE latin1_general_cs NOT NULL,
  `DDEFOG` varchar(6) COLLATE latin1_general_cs NOT NULL,
  `DDENUM` varchar(6) COLLATE latin1_general_cs NOT NULL,
  `DDESUB` varchar(4) COLLATE latin1_general_cs NOT NULL,
  `DDEPRO` varchar(6) COLLATE latin1_general_cs NOT NULL,
  `DDEAAC` varchar(4) COLLATE latin1_general_cs NOT NULL,
  `DDECAT` varchar(4) COLLATE latin1_general_cs NOT NULL,
  `DDECLA` varchar(2) COLLATE latin1_general_cs NOT NULL,
  `DDESTO` smallint(6) NOT NULL,
  `DDEVAL` double NOT NULL,
  `DDEVPR` smallint(6) NOT NULL,
  `DDEPER` double NOT NULL,
  `DDEMMP` smallint(6) NOT NULL,
  `DDEMME` smallint(6) NOT NULL,
  `DDEMMR` smallint(6) NOT NULL,
  `DDEDET` double NOT NULL,
  `DDEMMA` smallint(6) NOT NULL,
  `DDEFL1` smallint(6) NOT NULL,
  `DDEFL2` smallint(6) NOT NULL,
  `DDEFL3` smallint(6) NOT NULL,
  `DDEFL4` smallint(6) NOT NULL,
  `DDEFL5` smallint(6) NOT NULL,
  `DDETIT` smallint(6) NOT NULL,
  `DDEEST` varchar(60) COLLATE latin1_general_cs NOT NULL,
  `DDEREC` double NOT NULL,
  `DDERE1` smallint(6) NOT NULL,
  `DDERED` smallint(6) NOT NULL,
  `DDRFLG` smallint(6) NOT NULL,
  `DDRVAL` double NOT NULL,
  `DDRPER` double NOT NULL,
  `DDRMMP` smallint(6) NOT NULL,
  `DDRMME` smallint(6) NOT NULL,
  `DDRMMR` smallint(6) NOT NULL,
  `DDRDET` double NOT NULL,
  `DDRMMA` smallint(6) NOT NULL,
  `DDVTIP` varchar(2) COLLATE latin1_general_cs NOT NULL,
  `DDVIND` varchar(60) COLLATE latin1_general_cs NOT NULL,
  `DDVPAR` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `DDVSEZ` varchar(4) COLLATE latin1_general_cs NOT NULL,
  `DDVFOG` varchar(6) COLLATE latin1_general_cs NOT NULL,
  `DDVNUM` varchar(6) COLLATE latin1_general_cs NOT NULL,
  `DDVSUB` varchar(4) COLLATE latin1_general_cs NOT NULL,
  `DDVCAT` varchar(4) COLLATE latin1_general_cs NOT NULL,
  `DDRNOD` smallint(6) NOT NULL,
  `DDRNOT` varchar(60) COLLATE latin1_general_cs NOT NULL,
  `DDEFLN__1` double NOT NULL,
  `DDEFLN__2` double NOT NULL,
  `DDEFLN__3` double NOT NULL,
  `DDEFLN__4` double NOT NULL,
  `DDEFLN__5` double NOT NULL,
  `DDEFLN__6` double NOT NULL,
  `DDEFLN__7` double NOT NULL,
  `DDEFLN__8` double NOT NULL,
  `DDEFLN__9` double NOT NULL,
  `DDEFLA__1` varchar(10) COLLATE latin1_general_cs NOT NULL,
  `DDEFLA__2` varchar(10) COLLATE latin1_general_cs NOT NULL,
  `DDEFLA__3` varchar(10) COLLATE latin1_general_cs NOT NULL,
  `DDEFLA__4` varchar(10) COLLATE latin1_general_cs NOT NULL,
  `DDEFLA__5` varchar(10) COLLATE latin1_general_cs NOT NULL,
  `DDEFLA__6` varchar(10) COLLATE latin1_general_cs NOT NULL,
  `DDEFLA__7` varchar(10) COLLATE latin1_general_cs NOT NULL,
  `DDEFLA__8` varchar(10) COLLATE latin1_general_cs NOT NULL,
  `DDEFLA__9` varchar(10) COLLATE latin1_general_cs NOT NULL,
  `DDECIN` varchar(6) COLLATE latin1_general_cs NOT NULL,
  `DDECIV` varchar(6) COLLATE latin1_general_cs NOT NULL,
  `DDVCIN` varchar(6) COLLATE latin1_general_cs NOT NULL,
  `DDVCIV` varchar(6) COLLATE latin1_general_cs NOT NULL,
  `DDEGRA__1` varchar(20) COLLATE latin1_general_cs NOT NULL,
  `DDEGRA__2` varchar(20) COLLATE latin1_general_cs NOT NULL,
  `DDEGRA__3` varchar(20) COLLATE latin1_general_cs NOT NULL,
  `DDEGRA__4` varchar(20) COLLATE latin1_general_cs NOT NULL,
  `DDEGRA__5` varchar(20) COLLATE latin1_general_cs NOT NULL,
  `DDEGRA__6` varchar(20) COLLATE latin1_general_cs NOT NULL,
  `DDEGRA__7` varchar(20) COLLATE latin1_general_cs NOT NULL,
  `DDEGRA__8` varchar(20) COLLATE latin1_general_cs NOT NULL,
  `DDEGRA__9` varchar(20) COLLATE latin1_general_cs NOT NULL,
  `DDEGRA__10` varchar(20) COLLATE latin1_general_cs NOT NULL,
  `DDEFIN` varchar(40) COLLATE latin1_general_cs NOT NULL,
  `DDECFL` varchar(16) COLLATE latin1_general_cs NOT NULL,
  `DDECFC` varchar(16) COLLATE latin1_general_cs NOT NULL,
  `DDEDIR` varchar(60) COLLATE latin1_general_cs NOT NULL,
  `DDECK1` double NOT NULL,
  `DDECK2` double NOT NULL,
  `DDECDA` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `DDECAL` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `DDECCN` varchar(30) COLLATE latin1_general_cs NOT NULL,
  `DDECPA` varchar(10) COLLATE latin1_general_cs NOT NULL,
  `DDELDA` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `DDELAL` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `DDEFIG` smallint(6) NOT NULL,
  `DDEMQT` double NOT NULL,
  `IMPOSTA` double NOT NULL,
  `DDEVQT` double NOT NULL,
  `DDRSEZ` varchar(4) COLLATE latin1_general_cs NOT NULL,
  `DDRFOG` varchar(6) COLLATE latin1_general_cs NOT NULL,
  `DDRNUM` varchar(6) COLLATE latin1_general_cs NOT NULL,
  `DDRSUB` varchar(4) COLLATE latin1_general_cs NOT NULL,
  `DDRCAT` varchar(4) COLLATE latin1_general_cs NOT NULL,
  `DDEDCA` smallint(6) NOT NULL,
  `DDEITP` varchar(8) COLLATE latin1_general_cs NOT NULL COMMENT 'data inizio/termine possesso',
  `DDEDUL` varchar(8) COLLATE latin1_general_cs NOT NULL COMMENT 'data ultimazione lavori',
  `DDEACQ` tinyint(4) NOT NULL COMMENT 'acquisto',
  `DDECES` tinyint(4) NOT NULL COMMENT 'cessione',
  `LOCCON` smallint(6) NOT NULL COMMENT 'Locato con contratto Concordato',
  `DICABB` int(11) NOT NULL COMMENT 'Codice Abbattimento TASI',
  `IMPOTASI` double NOT NULL COMMENT 'Imposta TASI',
  `DDETER` varchar(8) COLLATE latin1_general_cs NOT NULL COMMENT 'Data Termine Variazione o Possesso',
  `DDETDI` varchar(30) COLLATE latin1_general_cs NOT NULL COMMENT 'Tipo di Dichiarazione',
  `DDENPR` varchar(10) COLLATE latin1_general_cs NOT NULL COMMENT 'Numero di Protocollo',
  `DDEALQPAR` double NOT NULL COMMENT 'ALIQUOT PARTICOLARE',
  `DDEALQSAVE` double NOT NULL COMMENT 'ALIQUOTA PARTICOLARE SAVE',
  `DDEATTIV` int(11) NOT NULL COMMENT 'ATTIVITA SVOLTA IN IMMOBILE DI PROPRIETA',
  `DDEINAG` int(11) NOT NULL COMMENT 'IMM. INAGIBILE',
  `DDEREND` double NOT NULL COMMENT 'RENDITA',
  `CONTROLLATA` int(11) NOT NULL,
  `NATDIC` int(11) NOT NULL,
  PRIMARY KEY (`ROWID`),
  KEY `A_DDECFI` (`DDECFI`),
  KEY `A_DDERIF` (`DDERIF`),
  KEY `A_DDEIMM` (`DDEIMM`),
  KEY `A_DDEPAR` (`DDEPAR`),
  KEY `A_DDECIN` (`DDECIN`),
  KEY `A_DDVCIN` (`DDVCIN`),
  KEY `I_FABBRICATO` (`DDESEZ`,`DDEFOG`,`DDENUM`,`DDESUB`),
  KEY `I_DETKEY` (`DDECFI`,`DDEANN`),
  KEY `I_CATASTO` (`DDEIDE`),
  KEY `I_DDEFIN` (`DDEFIN`),
  KEY `I_SEZ` (`DDESEZ`),
  KEY `I_FOG` (`DDEFOG`),
  KEY `I_NUM` (`DDENUM`),
  KEY `I_SUB` (`DDESUB`),
  KEY `I_DDECK1` (`DDECK1`),
  KEY `I_DDECK2` (`DDECK2`),
  KEY `DDEANN` (`DDEANN`),
  KEY `ANNO_CATA` (`DDEANN`,`DDEDCA`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `DICDETVAR` (
  `ROWID` int(11) NOT NULL AUTO_INCREMENT,
  `DDECFI` varchar(16) COLLATE latin1_general_cs NOT NULL,
  `DDEANN` varchar(4) COLLATE latin1_general_cs NOT NULL,
  `DDECNT` smallint(6) NOT NULL,
  `DDEDAT` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `DDETIP` varchar(2) COLLATE latin1_general_cs NOT NULL,
  `DDEIND` varchar(60) COLLATE latin1_general_cs NOT NULL,
  `DDERIF` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `DDEIMM` double NOT NULL,
  `DDEIDE` varchar(10) COLLATE latin1_general_cs NOT NULL,
  `DDEPAR` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `DDESEZ` varchar(4) COLLATE latin1_general_cs NOT NULL,
  `DDEFOG` varchar(6) COLLATE latin1_general_cs NOT NULL,
  `DDENUM` varchar(6) COLLATE latin1_general_cs NOT NULL,
  `DDESUB` varchar(4) COLLATE latin1_general_cs NOT NULL,
  `DDEPRO` varchar(6) COLLATE latin1_general_cs NOT NULL,
  `DDEAAC` varchar(4) COLLATE latin1_general_cs NOT NULL,
  `DDECAT` varchar(4) COLLATE latin1_general_cs NOT NULL,
  `DDECLA` varchar(2) COLLATE latin1_general_cs NOT NULL,
  `DDESTO` smallint(6) NOT NULL,
  `DDEVAL` double NOT NULL,
  `DDEVPR` smallint(6) NOT NULL,
  `DDEPER` double NOT NULL,
  `DDEMMP` smallint(6) NOT NULL,
  `DDEMME` smallint(6) NOT NULL,
  `DDEMMR` smallint(6) NOT NULL,
  `DDEDET` double NOT NULL,
  `DDEMMA` smallint(6) NOT NULL,
  `DDEFL1` smallint(6) NOT NULL,
  `DDEFL2` smallint(6) NOT NULL,
  `DDEFL3` smallint(6) NOT NULL,
  `DDEFL4` smallint(6) NOT NULL,
  `DDEFL5` smallint(6) NOT NULL,
  `DDETIT` smallint(6) NOT NULL,
  `DDEEST` varchar(60) COLLATE latin1_general_cs NOT NULL,
  `DDEREC` double NOT NULL,
  `DDERE1` smallint(6) NOT NULL,
  `DDERED` smallint(6) NOT NULL,
  `DDRFLG` smallint(6) NOT NULL,
  `DDRVAL` double NOT NULL,
  `DDRPER` double NOT NULL,
  `DDRMMP` smallint(6) NOT NULL,
  `DDRMME` smallint(6) NOT NULL,
  `DDRMMR` smallint(6) NOT NULL,
  `DDRDET` double NOT NULL,
  `DDRMMA` smallint(6) NOT NULL,
  `DDVTIP` varchar(2) COLLATE latin1_general_cs NOT NULL,
  `DDVIND` varchar(60) COLLATE latin1_general_cs NOT NULL,
  `DDVPAR` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `DDVSEZ` varchar(4) COLLATE latin1_general_cs NOT NULL,
  `DDVFOG` varchar(6) COLLATE latin1_general_cs NOT NULL,
  `DDVNUM` varchar(6) COLLATE latin1_general_cs NOT NULL,
  `DDVSUB` varchar(4) COLLATE latin1_general_cs NOT NULL,
  `DDVCAT` varchar(4) COLLATE latin1_general_cs NOT NULL,
  `DDRNOD` smallint(6) NOT NULL,
  `DDRNOT` varchar(60) COLLATE latin1_general_cs NOT NULL,
  `DDEFLN__1` double NOT NULL,
  `DDEFLN__2` double NOT NULL,
  `DDEFLN__3` double NOT NULL,
  `DDEFLN__4` double NOT NULL,
  `DDEFLN__5` double NOT NULL,
  `DDEFLN__6` double NOT NULL,
  `DDEFLN__7` double NOT NULL,
  `DDEFLN__8` double NOT NULL,
  `DDEFLN__9` double NOT NULL,
  `DDEFLA__1` varchar(10) COLLATE latin1_general_cs NOT NULL,
  `DDEFLA__2` varchar(10) COLLATE latin1_general_cs NOT NULL,
  `DDEFLA__3` varchar(10) COLLATE latin1_general_cs NOT NULL,
  `DDEFLA__4` varchar(10) COLLATE latin1_general_cs NOT NULL,
  `DDEFLA__5` varchar(10) COLLATE latin1_general_cs NOT NULL,
  `DDEFLA__6` varchar(10) COLLATE latin1_general_cs NOT NULL,
  `DDEFLA__7` varchar(10) COLLATE latin1_general_cs NOT NULL,
  `DDEFLA__8` varchar(10) COLLATE latin1_general_cs NOT NULL,
  `DDEFLA__9` varchar(10) COLLATE latin1_general_cs NOT NULL,
  `DDECIN` varchar(6) COLLATE latin1_general_cs NOT NULL,
  `DDECIV` varchar(6) COLLATE latin1_general_cs NOT NULL,
  `DDVCIN` varchar(6) COLLATE latin1_general_cs NOT NULL,
  `DDVCIV` varchar(6) COLLATE latin1_general_cs NOT NULL,
  `DDEGRA__1` varchar(20) COLLATE latin1_general_cs NOT NULL,
  `DDEGRA__2` varchar(20) COLLATE latin1_general_cs NOT NULL,
  `DDEGRA__3` varchar(20) COLLATE latin1_general_cs NOT NULL,
  `DDEGRA__4` varchar(20) COLLATE latin1_general_cs NOT NULL,
  `DDEGRA__5` varchar(20) COLLATE latin1_general_cs NOT NULL,
  `DDEGRA__6` varchar(20) COLLATE latin1_general_cs NOT NULL,
  `DDEGRA__7` varchar(20) COLLATE latin1_general_cs NOT NULL,
  `DDEGRA__8` varchar(20) COLLATE latin1_general_cs NOT NULL,
  `DDEGRA__9` varchar(20) COLLATE latin1_general_cs NOT NULL,
  `DDEGRA__10` varchar(20) COLLATE latin1_general_cs NOT NULL,
  `DDEFIN` varchar(40) COLLATE latin1_general_cs NOT NULL,
  `DDECFL` varchar(16) COLLATE latin1_general_cs NOT NULL,
  `DDECFC` varchar(16) COLLATE latin1_general_cs NOT NULL,
  `DDEDIR` varchar(60) COLLATE latin1_general_cs NOT NULL,
  `DDECK1` double NOT NULL,
  `DDECK2` double NOT NULL,
  `DDECDA` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `DDECAL` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `DDECCN` varchar(30) COLLATE latin1_general_cs NOT NULL,
  `DDECPA` varchar(10) COLLATE latin1_general_cs NOT NULL,
  `DDELDA` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `DDELAL` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `DDEFIG` smallint(6) NOT NULL,
  `DDEMQT` double NOT NULL,
  `IMPOSTA` double NOT NULL,
  `DDEVQT` double NOT NULL,
  `DDRSEZ` varchar(4) COLLATE latin1_general_cs NOT NULL,
  `DDRFOG` varchar(6) COLLATE latin1_general_cs NOT NULL,
  `DDRNUM` varchar(6) COLLATE latin1_general_cs NOT NULL,
  `DDRSUB` varchar(4) COLLATE latin1_general_cs NOT NULL,
  `DDRCAT` varchar(4) COLLATE latin1_general_cs NOT NULL,
  `DDEDCA` smallint(6) NOT NULL,
  `DDEITP` varchar(8) COLLATE latin1_general_cs NOT NULL COMMENT 'data inizio/termine possesso',
  `DDEDUL` varchar(8) COLLATE latin1_general_cs NOT NULL COMMENT 'data ultimazione lavori',
  `DDEACQ` tinyint(4) NOT NULL COMMENT 'acquisto',
  `DDECES` tinyint(4) NOT NULL COMMENT 'cessione',
  `LOCCON` smallint(6) NOT NULL COMMENT 'Locato con contratto Concordato',
  `DICABB` int(11) NOT NULL COMMENT 'Codice Abbattimento TASI',
  `IMPOTASI` double NOT NULL COMMENT 'Imposta TASI',
  `DDETER` varchar(8) COLLATE latin1_general_cs NOT NULL COMMENT 'Data Termine Variazione o Possesso',
  `DDETDI` varchar(30) COLLATE latin1_general_cs NOT NULL COMMENT 'Tipo di Dichiarazione',
  `DDENPR` varchar(10) COLLATE latin1_general_cs NOT NULL COMMENT 'Numero di Protocollo',
  `DDEALQPAR` double NOT NULL COMMENT 'ALIQUOT PARTICOLARE',
  `DDEALQSAVE` double NOT NULL COMMENT 'ALIQUOTA PARTICOLARE SAVE',
  `DDEATTIV` int(11) NOT NULL COMMENT 'ATTIVITA SVOLTA IN IMMOBILE DI PROPRIETA',
  `DDEINAG` int(11) NOT NULL COMMENT 'IMM. INAGIBILE',
  `DDEREND` double NOT NULL COMMENT 'RENDITA',
  `CONTROLLATA` int(11) NOT NULL,
  `NATDIC` int(11) NOT NULL,
  `IMMIDPADRE` double NOT NULL,
  `EVENTO` char(20) COLLATE latin1_general_cs NOT NULL,
  PRIMARY KEY (`ROWID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `DICHIA` (
  `ROWID` int(11) NOT NULL AUTO_INCREMENT,
  `DICCFI` varchar(16) COLLATE latin1_general_cs NOT NULL,
  `DICANN` varchar(4) COLLATE latin1_general_cs NOT NULL,
  `DICNEW` smallint(6) NOT NULL,
  `DICDAT` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `DICRIF` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `DICCON` smallint(6) NOT NULL,
  PRIMARY KEY (`ROWID`),
  KEY `A_DICCFI` (`DICCFI`),
  KEY `A_DICRIF` (`DICRIF`),
  KEY `I_DICKEY` (`DICCFI`,`DICANN`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `DICTRL` (
  `ROWID` int(11) NOT NULL AUTO_INCREMENT,
  `CTRANN` varchar(4) COLLATE latin1_general_cs NOT NULL,
  `CTRCFI` varchar(16) COLLATE latin1_general_cs NOT NULL,
  `CTRDAT` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `CTRSTA` smallint(6) NOT NULL,
  `CTRNOT` varchar(40) COLLATE latin1_general_cs NOT NULL,
  `CTRFLD` smallint(6) NOT NULL,
  `CTRFLV` smallint(6) NOT NULL,
  `CTRFLA` smallint(6) NOT NULL,
  PRIMARY KEY (`ROWID`),
  KEY `I_CTRKEY` (`CTRCFI`,`CTRANN`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `LISTACARICO` (
  `ROWID` int(11) NOT NULL AUTO_INCREMENT,
  `TOKEN` varchar(50) COLLATE latin1_general_cs NOT NULL,
  `UTENTE` varchar(20) COLLATE latin1_general_cs NOT NULL,
  `DATA` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `ORA` varchar(4) COLLATE latin1_general_cs NOT NULL,
  `NOMEREPORT` varchar(30) COLLATE latin1_general_cs NOT NULL,
  `VERBALE` int(11) NOT NULL,
  `ANNO` int(11) NOT NULL,
  `PROPRIETARIO` varchar(30) COLLATE latin1_general_cs NOT NULL,
  `TIPOVERBALE` varchar(2) COLLATE latin1_general_cs NOT NULL,
  `DATAINFRAZIONE` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `DATANOTIFICA` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `RUOLOTOTALE` double NOT NULL,
  `TASSA` double NOT NULL,
  `SANZIONE` double NOT NULL,
  `INTERESSI` double NOT NULL,
  `SPESE` double NOT NULL,
  `ROWID_ANAGRA` int(11) NOT NULL,
  `TOTALEPARZIALE` double NOT NULL,
  `FONTEDATI` varchar(10) COLLATE latin1_general_cs NOT NULL,
  `STATO` varchar(30) COLLATE latin1_general_cs NOT NULL,
  `PROTOCOLLO` double NOT NULL COMMENT 'Protocollo dell''Accertamento',
  PRIMARY KEY (`ROWID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `PARAM` (
  `ROWID` int(11) NOT NULL AUTO_INCREMENT,
  `CHIAVE` varchar(20) COLLATE latin1_general_cs NOT NULL,
  `VALORE` varchar(300) COLLATE latin1_general_cs NOT NULL,
  PRIMARY KEY (`ROWID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `REGAVV` (
  `ROWID` int(11) NOT NULL AUTO_INCREMENT,
  `REGCOD` double NOT NULL,
  `REGDES` varchar(60) COLLATE latin1_general_cs NOT NULL,
  `REGAVV` double NOT NULL,
  `REGMOT` varchar(6) COLLATE latin1_general_cs NOT NULL,
  `REGAZI__1` double NOT NULL,
  `REGAZI__2` double NOT NULL,
  `REGAZI__3` double NOT NULL,
  `REGAZI__4` double NOT NULL,
  `REGAZI__5` double NOT NULL,
  `REGTES` varchar(20) COLLATE latin1_general_cs NOT NULL,
  PRIMARY KEY (`ROWID`),
  UNIQUE KEY `K_MASTER` (`REGCOD`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `SUCANAG` (
  `ROWID` int(11) NOT NULL AUTO_INCREMENT,
  `IDENTIFICATIVO` int(11) NOT NULL,
  `UNIVOCO` varchar(23) COLLATE latin1_general_cs NOT NULL,
  `RECORD` varchar(1) COLLATE latin1_general_cs NOT NULL,
  `PROGRECORD` varchar(5) COLLATE latin1_general_cs NOT NULL,
  `PROG` varchar(3) COLLATE latin1_general_cs NOT NULL,
  `TIPO` varchar(1) COLLATE latin1_general_cs NOT NULL,
  `DATASUCC` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `CF` varchar(16) COLLATE latin1_general_cs NOT NULL,
  `COGNOME` varchar(50) COLLATE latin1_general_cs NOT NULL,
  `NOME` varchar(25) COLLATE latin1_general_cs NOT NULL,
  `SESSO` varchar(1) COLLATE latin1_general_cs NOT NULL,
  `CITTANAS` varchar(30) COLLATE latin1_general_cs NOT NULL,
  `PVNAS` varchar(2) COLLATE latin1_general_cs NOT NULL,
  `DATANAS` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `CITTARES` varchar(30) COLLATE latin1_general_cs NOT NULL,
  `PVRES` varchar(2) COLLATE latin1_general_cs NOT NULL,
  `INDRES` varchar(30) COLLATE latin1_general_cs NOT NULL,
  PRIMARY KEY (`ROWID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `SUCCESSIONI` (
  `ROWID` int(11) NOT NULL AUTO_INCREMENT,
  `FILE` varchar(50) COLLATE latin1_general_cs NOT NULL,
  `IDENTIFICATIVO` int(11) NOT NULL,
  `DATAFILE` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `DATAIMPORTAZIONE` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `DATAREGISTRAZIONE` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `UTEIMPORTAZIONE` varchar(50) COLLATE latin1_general_cs NOT NULL,
  `UTEREGISTRAZIONE` varchar(50) COLLATE latin1_general_cs NOT NULL,
  PRIMARY KEY (`ROWID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `SUCDETT` (
  `ROWID` int(11) NOT NULL AUTO_INCREMENT,
  `IDENTIFICATIVO` int(11) NOT NULL,
  `UNIVOCO` varchar(23) COLLATE latin1_general_cs NOT NULL,
  `RECORD` varchar(1) COLLATE latin1_general_cs NOT NULL,
  `PROGRECORD` varchar(5) COLLATE latin1_general_cs NOT NULL,
  `PROGIMM` varchar(3) COLLATE latin1_general_cs NOT NULL,
  `PROGERE` varchar(3) COLLATE latin1_general_cs NOT NULL,
  `QUOTNUM` double NOT NULL,
  `QUOTDEN` double NOT NULL,
  `AGEV` varchar(1) COLLATE latin1_general_cs NOT NULL,
  PRIMARY KEY (`ROWID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `SUCDICH` (
  `ROWID` int(11) NOT NULL AUTO_INCREMENT,
  `IDENTIFICATIVO` int(11) NOT NULL,
  `UNIVOCO` varchar(23) COLLATE latin1_general_cs NOT NULL,
  `RECORD` varchar(1) COLLATE latin1_general_cs NOT NULL,
  `PROGRECORD` varchar(5) COLLATE latin1_general_cs NOT NULL,
  `PROG` varchar(3) COLLATE latin1_general_cs NOT NULL,
  `QUOTNUM` double NOT NULL,
  `QUOTDEN` double NOT NULL,
  `DIRITTO` varchar(2) COLLATE latin1_general_cs NOT NULL,
  `PROGPART` int(11) NOT NULL,
  `CATASTO` varchar(2) COLLATE latin1_general_cs NOT NULL,
  `SEZIONE` varchar(2) COLLATE latin1_general_cs NOT NULL,
  `TIPODATI` varchar(1) COLLATE latin1_general_cs NOT NULL,
  `FOGLIO` varchar(4) COLLATE latin1_general_cs NOT NULL,
  `PART1` varchar(5) COLLATE latin1_general_cs NOT NULL,
  `PART2` varchar(2) COLLATE latin1_general_cs NOT NULL,
  `SUBALT1` varchar(3) COLLATE latin1_general_cs NOT NULL,
  `SUBALT2` varchar(1) COLLATE latin1_general_cs NOT NULL,
  `DENU1` varchar(7) COLLATE latin1_general_cs NOT NULL,
  `DENU2` varchar(3) COLLATE latin1_general_cs NOT NULL,
  `ANNOCAT` varchar(2) COLLATE latin1_general_cs NOT NULL,
  `NATURA` varchar(3) COLLATE latin1_general_cs NOT NULL,
  `SUPETT` double NOT NULL,
  `SUPMQ` double NOT NULL,
  `VANI` double NOT NULL,
  `INDIRIZZO` varchar(40) COLLATE latin1_general_cs NOT NULL,
  PRIMARY KEY (`ROWID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `TABABB` (
  `ROWID` int(11) NOT NULL AUTO_INCREMENT,
  `ABBCOD` int(11) NOT NULL,
  `ABBDES` varchar(50) COLLATE latin1_general_cs NOT NULL,
  `ABBPER` double NOT NULL,
  `ABBTIP` int(11) NOT NULL COMMENT 'Tipo Valore di ABBPER 1= Perc, 2= Importo',
  PRIMARY KEY (`ROWID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `TABPAR` (
  `ROWID` int(11) NOT NULL AUTO_INCREMENT,
  `TABCOD` float NOT NULL,
  `TABDES` varchar(40) COLLATE latin1_general_cs NOT NULL,
  `TABPAR` varchar(60) COLLATE latin1_general_cs NOT NULL,
  `TABVAL` double NOT NULL,
  PRIMARY KEY (`ROWID`),
  UNIQUE KEY `K_MASTER` (`TABCOD`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `TABRET` (
  `ROWID` int(11) NOT NULL AUTO_INCREMENT,
  `DATVAR` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `DATVAL` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `DDEIMM` double NOT NULL,
  `RETANN` varchar(4) COLLATE latin1_general_cs NOT NULL,
  `RETTIP` varchar(2) COLLATE latin1_general_cs NOT NULL,
  `RETCIN` varchar(6) COLLATE latin1_general_cs NOT NULL,
  `RETIND` varchar(60) COLLATE latin1_general_cs NOT NULL,
  `RETCIV` varchar(6) COLLATE latin1_general_cs NOT NULL,
  `RETPAR` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `RETSEZ` varchar(4) COLLATE latin1_general_cs NOT NULL,
  `RETFOG` varchar(6) COLLATE latin1_general_cs NOT NULL,
  `RETNUM` varchar(6) COLLATE latin1_general_cs NOT NULL,
  `RETSUB` varchar(4) COLLATE latin1_general_cs NOT NULL,
  `RETCAT` varchar(4) COLLATE latin1_general_cs NOT NULL,
  `DDRVAL` double NOT NULL,
  `DDRPER` double NOT NULL,
  `DDRMMP` smallint(6) NOT NULL,
  `DDEMQT` double NOT NULL,
  `DDEVQT` double NOT NULL,
  `DDRDET` double NOT NULL,
  `DDRMMA` smallint(6) NOT NULL,
  `DDRMME` smallint(6) NOT NULL,
  `DDRMMR` smallint(6) NOT NULL,
  `DDRNOD` smallint(6) NOT NULL,
  `DDECFI` varchar(16) COLLATE latin1_general_cs NOT NULL,
  `FINEVAL` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `FLAG` int(11) NOT NULL COMMENT 'flag di ricalcolo valore',
  `RETREND` double NOT NULL,
  PRIMARY KEY (`ROWID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `TARIFF` (
  `ROWID` int(11) NOT NULL AUTO_INCREMENT,
  `TARCOD` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `TARDES__1` varchar(60) COLLATE latin1_general_cs NOT NULL,
  `TARDES__2` varchar(60) COLLATE latin1_general_cs NOT NULL,
  `TARDES__3` varchar(60) COLLATE latin1_general_cs NOT NULL,
  `TARDES__4` varchar(60) COLLATE latin1_general_cs NOT NULL,
  `TARDES__5` varchar(60) COLLATE latin1_general_cs NOT NULL,
  `TARDES__6` varchar(60) COLLATE latin1_general_cs NOT NULL,
  `TARDES__7` varchar(60) COLLATE latin1_general_cs NOT NULL,
  `TARDES__8` varchar(60) COLLATE latin1_general_cs NOT NULL,
  `TARDES__9` varchar(60) COLLATE latin1_general_cs NOT NULL,
  `TARDES__10` varchar(60) COLLATE latin1_general_cs NOT NULL,
  `TARDES__11` varchar(60) COLLATE latin1_general_cs NOT NULL,
  `TARDES__12` varchar(60) COLLATE latin1_general_cs NOT NULL,
  `TARDES__13` varchar(60) COLLATE latin1_general_cs NOT NULL,
  `TARDES__14` varchar(60) COLLATE latin1_general_cs NOT NULL,
  `TARDES__15` varchar(60) COLLATE latin1_general_cs NOT NULL,
  `TARDES__16` varchar(60) COLLATE latin1_general_cs NOT NULL,
  `TARDES__17` varchar(60) COLLATE latin1_general_cs NOT NULL,
  `TARDES__18` varchar(60) COLLATE latin1_general_cs NOT NULL,
  `TARDES__19` varchar(60) COLLATE latin1_general_cs NOT NULL,
  `TARDES__20` varchar(60) COLLATE latin1_general_cs NOT NULL,
  `TARVAL__1` double NOT NULL,
  `TARVAL__2` double NOT NULL,
  `TARVAL__3` double NOT NULL,
  `TARVAL__4` double NOT NULL,
  `TARVAL__5` double NOT NULL,
  `TARVAL__6` double NOT NULL,
  `TARVAL__7` double NOT NULL,
  `TARVAL__8` double NOT NULL,
  `TARVAL__9` double NOT NULL,
  `TARVAL__10` double NOT NULL,
  `TARVAL__11` double NOT NULL,
  `TARVAL__12` double NOT NULL,
  `TARVAL__13` double NOT NULL,
  `TARVAL__14` double NOT NULL,
  `TARVAL__15` double NOT NULL,
  `TARVAL__16` double NOT NULL,
  `TARVAL__17` double NOT NULL,
  `TARVAL__18` double NOT NULL,
  `TARVAL__19` double NOT NULL,
  `TARVAL__20` double NOT NULL,
  PRIMARY KEY (`ROWID`),
  UNIQUE KEY `K_MASTER` (`TARCOD`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `TIPNOT` (
  `ROWID` int(11) NOT NULL AUTO_INCREMENT,
  `CODICE` smallint(6) NOT NULL,
  `TIPONOTIFICA` varchar(50) COLLATE latin1_general_cs NOT NULL,
  `SPESE` double NOT NULL,
  `FLAGTIPO` varchar(1) COLLATE latin1_general_cs NOT NULL,
  PRIMARY KEY (`ROWID`),
  UNIQUE KEY `CODICE` (`CODICE`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `TRACCIATI` (
  `ROWID` int(11) NOT NULL AUTO_INCREMENT,
  `TRACCIATO` varchar(20) COLLATE latin1_general_cs NOT NULL,
  `SEQUENZA` smallint(6) NOT NULL,
  `FIELDNAME` varchar(50) COLLATE latin1_general_cs NOT NULL,
  `TYPE` varchar(5) COLLATE latin1_general_cs NOT NULL,
  `START` smallint(6) NOT NULL,
  `LENGTH` smallint(6) NOT NULL,
  UNIQUE KEY `ROWID` (`ROWID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs COMMENT='TABELLA TRACCIATI RECORD';
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `VERSAM` (
  `ROWID` int(11) NOT NULL AUTO_INCREMENT,
  `VERCFI` varchar(16) COLLATE latin1_general_cs NOT NULL,
  `VERANN` varchar(4) COLLATE latin1_general_cs NOT NULL,
  `VERQUI` varchar(10) COLLATE latin1_general_cs NOT NULL,
  `VERDAT` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `VERNFA` smallint(6) NOT NULL,
  `VERFL1` smallint(6) NOT NULL,
  `VEIMP1` double NOT NULL,
  `VEIMP2` double NOT NULL,
  `VEIMP3` double NOT NULL,
  `VEIMP4` double NOT NULL,
  `VEIMP5` double NOT NULL,
  `VEIMP6` double NOT NULL,
  `VERDET` double NOT NULL,
  `VERTOT` double NOT NULL,
  `VERFL2` smallint(6) NOT NULL,
  `VERFL3` smallint(6) NOT NULL,
  `VERNOM` varchar(60) COLLATE latin1_general_cs NOT NULL,
  `VERIND` varchar(36) COLLATE latin1_general_cs NOT NULL,
  `VERCOM` varchar(30) COLLATE latin1_general_cs NOT NULL,
  `VERCAP` varchar(6) COLLATE latin1_general_cs NOT NULL,
  `VERPRO` varchar(2) COLLATE latin1_general_cs NOT NULL,
  `VERDE1` double NOT NULL,
  `VERCOR` double NOT NULL,
  `VEROPE` varchar(2) COLLATE latin1_general_cs NOT NULL,
  `VERREG` double NOT NULL,
  `VERMOD` smallint(6) NOT NULL,
  `VERNPR` double NOT NULL,
  `VERFLN__1` double NOT NULL,
  `VERFLN__2` double NOT NULL,
  `VERFLN__3` double NOT NULL,
  `VERFLA__1` varchar(30) COLLATE latin1_general_cs NOT NULL,
  `VERFLA__2` varchar(30) COLLATE latin1_general_cs NOT NULL,
  `VERFLA__3` varchar(30) COLLATE latin1_general_cs NOT NULL,
  `VEIMP7` double NOT NULL,
  `VESTA1` double NOT NULL,
  `VESTA2` double NOT NULL,
  `VESTA4` double NOT NULL,
  `VERSNO` double NOT NULL,
  `VERTIP` smallint(6) NOT NULL,
  `VERNOT` text COLLATE latin1_general_cs NOT NULL,
  `VERBLO` varchar(8) COLLATE latin1_general_cs NOT NULL COMMENT 'DATA COMUNICAZIONE SU FILE',
  `VEIMP8` double NOT NULL,
  `VESTA8` double NOT NULL,
  `COMPIMP` double NOT NULL COMMENT 'IMPORTO COMPENSATO',
  `COMPANNO` double NOT NULL COMMENT 'ANNO COMPETENZA COMPENSAZIONE',
  `COMPDATA` varchar(8) COLLATE latin1_general_cs NOT NULL COMMENT 'DATA OPRRAZIONE',
  `VERCCP` double NOT NULL,
  PRIMARY KEY (`ROWID`),
  KEY `A_VERCFI` (`VERCFI`),
  KEY `A_VERQUI` (`VERQUI`),
  KEY `A_VERCOR` (`VERCOR`),
  KEY `A_VERNPR` (`VERNPR`),
  KEY `I_VERKEY` (`VERCFI`,`VERANN`),
  KEY `VERANN` (`VERANN`),
  KEY `I_ANNOACCERT` (`VERANN`,`VERFLN__2`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `W_BOLL123` (
  `ROWID` int(11) NOT NULL AUTO_INCREMENT,
  `TOKEN` varchar(50) COLLATE latin1_general_cs NOT NULL,
  `UTENTE` varchar(20) COLLATE latin1_general_cs NOT NULL,
  `DATA` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `ORA` varchar(4) COLLATE latin1_general_cs NOT NULL,
  `NOMEREPORT` varchar(30) COLLATE latin1_general_cs NOT NULL,
  `IMPORTONUMERICO` double NOT NULL,
  `IMPORTOLETTERE` varchar(100) COLLATE latin1_general_cs NOT NULL,
  `COGNOME` varchar(60) COLLATE latin1_general_cs NOT NULL,
  `NOME` varchar(20) COLLATE latin1_general_cs NOT NULL,
  `CAP` varchar(6) COLLATE latin1_general_cs NOT NULL,
  `COMUNE` varchar(30) COLLATE latin1_general_cs NOT NULL,
  `VIA` varchar(36) COLLATE latin1_general_cs NOT NULL,
  `CIVICO` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `CF` varchar(16) COLLATE latin1_general_cs NOT NULL,
  `AVVNPR` varchar(9) COLLATE latin1_general_cs NOT NULL,
  `AVVDAT` varchar(6) COLLATE latin1_general_cs NOT NULL,
  PRIMARY KEY (`ROWID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `W_CONTRABBAT` (
  `ROWID` int(11) NOT NULL AUTO_INCREMENT,
  `TOKEN` varchar(50) COLLATE latin1_general_cs NOT NULL,
  `UTENTE` varchar(20) COLLATE latin1_general_cs NOT NULL,
  `DATA` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `ORA` varchar(4) COLLATE latin1_general_cs NOT NULL,
  `NOMEREPORT` varchar(30) COLLATE latin1_general_cs NOT NULL,
  `CF` varchar(16) COLLATE latin1_general_cs NOT NULL,
  `NOMINATIVO` varchar(60) COLLATE latin1_general_cs NOT NULL,
  `INDIRIZZO` varchar(80) COLLATE latin1_general_cs NOT NULL,
  `CAP` varchar(5) COLLATE latin1_general_cs NOT NULL,
  `CITTA` varchar(50) COLLATE latin1_general_cs NOT NULL,
  `INDIMMOB` varchar(80) COLLATE latin1_general_cs NOT NULL,
  `TIPO` varchar(10) COLLATE latin1_general_cs NOT NULL,
  `FOGLIO` varchar(5) COLLATE latin1_general_cs NOT NULL,
  `NUMERO` varchar(5) COLLATE latin1_general_cs NOT NULL,
  `SUB` varchar(5) COLLATE latin1_general_cs NOT NULL,
  `PERC` varchar(10) COLLATE latin1_general_cs NOT NULL,
  `MESIPOS` varchar(15) COLLATE latin1_general_cs NOT NULL,
  `EVENTO` varchar(50) COLLATE latin1_general_cs NOT NULL,
  PRIMARY KEY (`ROWID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `W_CONTRDICH` (
  `ROWID` int(11) NOT NULL AUTO_INCREMENT,
  `TOKEN` varchar(50) COLLATE latin1_general_cs NOT NULL,
  `UTENTE` varchar(20) COLLATE latin1_general_cs NOT NULL,
  `DATA` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `ORA` varchar(4) COLLATE latin1_general_cs NOT NULL,
  `NOMEREPORT` varchar(30) COLLATE latin1_general_cs NOT NULL,
  `CF` varchar(16) COLLATE latin1_general_cs NOT NULL,
  `NOMINATIVO` varchar(60) COLLATE latin1_general_cs NOT NULL,
  `INDIRIZZO` varchar(80) COLLATE latin1_general_cs NOT NULL,
  `CAP` varchar(5) COLLATE latin1_general_cs NOT NULL,
  `CITTA` varchar(50) COLLATE latin1_general_cs NOT NULL,
  `INDIMMOB` varchar(80) COLLATE latin1_general_cs NOT NULL,
  `TIPO` varchar(10) COLLATE latin1_general_cs NOT NULL,
  `FOGLIO` varchar(5) COLLATE latin1_general_cs NOT NULL,
  `NUMERO` varchar(5) COLLATE latin1_general_cs NOT NULL,
  `SUB` varchar(5) COLLATE latin1_general_cs NOT NULL,
  `PERC` varchar(10) COLLATE latin1_general_cs NOT NULL,
  `MESIPOS` varchar(15) COLLATE latin1_general_cs NOT NULL,
  `DETRAZPRECE` varchar(15) COLLATE latin1_general_cs NOT NULL,
  `DETRAZNUOVE` varchar(15) COLLATE latin1_general_cs NOT NULL,
  `EVENTO` varchar(50) COLLATE latin1_general_cs NOT NULL,
  `DATAEVENTO` varchar(10) COLLATE latin1_general_cs NOT NULL,
  PRIMARY KEY (`ROWID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `W_STAADESIONI` (
  `ROWID` int(11) NOT NULL AUTO_INCREMENT,
  `TOKEN` varchar(50) COLLATE latin1_general_cs NOT NULL,
  `UTENTE` varchar(20) COLLATE latin1_general_cs NOT NULL,
  `DATA` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `ORA` varchar(4) COLLATE latin1_general_cs NOT NULL,
  `NOMEREPORT` varchar(30) COLLATE latin1_general_cs NOT NULL,
  `CF` varchar(16) COLLATE latin1_general_cs NOT NULL,
  `NOMINATIVO` varchar(60) COLLATE latin1_general_cs NOT NULL,
  `AVVISO` varchar(10) COLLATE latin1_general_cs NOT NULL,
  `AVVDEL` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `TIPOADE` varchar(30) COLLATE latin1_general_cs NOT NULL,
  `DATASOS` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `ESITO` varchar(50) COLLATE latin1_general_cs NOT NULL,
  `CHIUSOIL` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `DATAINVIO` varchar(8) COLLATE latin1_general_cs NOT NULL,
  PRIMARY KEY (`ROWID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `W_STALAV` (
  `ROWID` int(11) NOT NULL AUTO_INCREMENT,
  `TOKEN` varchar(50) COLLATE latin1_general_cs NOT NULL,
  `UTENTE` varchar(20) COLLATE latin1_general_cs NOT NULL,
  `DATA` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `ORA` varchar(4) COLLATE latin1_general_cs NOT NULL,
  `NOMEREPORT` varchar(30) COLLATE latin1_general_cs NOT NULL,
  `CONTRIBUENTE` varchar(50) COLLATE latin1_general_cs NOT NULL,
  `ANNO` varchar(4) COLLATE latin1_general_cs NOT NULL,
  `NUMERO` varchar(20) COLLATE latin1_general_cs NOT NULL,
  `PROTOCOLLO` varchar(20) COLLATE latin1_general_cs NOT NULL,
  `TIPO` varchar(10) COLLATE latin1_general_cs NOT NULL,
  `DATAAVV` varchar(10) COLLATE latin1_general_cs NOT NULL,
  `IMPOSTA` double NOT NULL,
  `ARTICOLO14` double NOT NULL,
  `ARTICOLO13` double NOT NULL,
  `INTERESSI` double NOT NULL,
  `TOTACCERT` double NOT NULL,
  `SPESENOTIF` double NOT NULL,
  `TOTDAVER` double NOT NULL,
  `DEFAGEVO` double NOT NULL,
  `DEFSOLOSANZ` double NOT NULL,
  `DESSTATO` char(30) COLLATE latin1_general_cs NOT NULL,
  `DATANOTIF` char(10) COLLATE latin1_general_cs NOT NULL,
  `TOTVERSATO` double NOT NULL,
  PRIMARY KEY (`ROWID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `W_STAMPARUOLO` (
  `ROWID` int(11) NOT NULL AUTO_INCREMENT,
  `TOKEN` varchar(50) COLLATE latin1_general_cs NOT NULL,
  `UTENTE` varchar(20) COLLATE latin1_general_cs NOT NULL,
  `DATA` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `ORA` varchar(4) COLLATE latin1_general_cs NOT NULL,
  `NOMEREPORT` varchar(30) COLLATE latin1_general_cs NOT NULL,
  `VERBALE` int(11) NOT NULL,
  `ANNO` int(11) NOT NULL,
  `PROPRIETARIO` varchar(30) COLLATE latin1_general_cs NOT NULL,
  `TIPOVERBALE` varchar(2) COLLATE latin1_general_cs NOT NULL,
  `DATAINFRAZIONE` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `DATANOTIFICA` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `DATAESIGIBILITA` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `RUOLODATARISCOSSIONE` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `RUOLOGIORNI` int(11) NOT NULL,
  `SOMMAESIGIBILE` double NOT NULL,
  `SPESE` double NOT NULL,
  `RUOLOMAGGIORAZIONE` double NOT NULL,
  `RUOLOTOTALE` double NOT NULL,
  `TASSA` double NOT NULL,
  `SANZIONE` double NOT NULL,
  `INTERESSI` double NOT NULL,
  `SEQUENZA` double NOT NULL,
  `ROWID_ANAGRA` int(11) NOT NULL,
  `ROWID_ANAGALTR` int(11) NOT NULL,
  `PAGATO` double NOT NULL,
  `TOTALEPARZIALE` double NOT NULL,
  `FONTEDATI` varchar(10) COLLATE latin1_general_cs NOT NULL,
  PRIMARY KEY (`ROWID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `W_STATASI` (
  `ROWID` int(11) NOT NULL AUTO_INCREMENT,
  `TOKEN` varchar(50) COLLATE latin1_general_cs NOT NULL,
  `UTENTE` varchar(20) COLLATE latin1_general_cs NOT NULL,
  `DATA` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `ORA` varchar(4) COLLATE latin1_general_cs NOT NULL,
  `NOMEREPORT` varchar(30) COLLATE latin1_general_cs NOT NULL,
  `DDECFI` varchar(16) COLLATE latin1_general_cs NOT NULL,
  `DDEIND` varchar(60) COLLATE latin1_general_cs NOT NULL,
  `DDECAT` varchar(4) COLLATE latin1_general_cs NOT NULL,
  `DDEFOG` varchar(6) COLLATE latin1_general_cs NOT NULL,
  `DDENUM` varchar(6) COLLATE latin1_general_cs NOT NULL,
  `DDESUB` varchar(4) COLLATE latin1_general_cs NOT NULL,
  `DDEPER` varchar(15) COLLATE latin1_general_cs NOT NULL,
  `DDEVAL` varchar(15) COLLATE latin1_general_cs NOT NULL,
  `DICABB` varchar(10) COLLATE latin1_general_cs NOT NULL,
  `DDEDET` varchar(10) COLLATE latin1_general_cs NOT NULL,
  `DDRDET` varchar(10) COLLATE latin1_general_cs NOT NULL,
  `DDETIP` varchar(2) COLLATE latin1_general_cs NOT NULL,
  `ANACOG` varchar(60) COLLATE latin1_general_cs NOT NULL,
  `ANANOM` varchar(20) COLLATE latin1_general_cs NOT NULL,
  `ANAIND` varchar(36) COLLATE latin1_general_cs NOT NULL,
  `ANACAP` varchar(6) COLLATE latin1_general_cs NOT NULL,
  `ANACOM` varchar(30) COLLATE latin1_general_cs NOT NULL,
  `DDRNOD` varchar(10) COLLATE latin1_general_cs NOT NULL,
  `DDRCAT` varchar(4) COLLATE latin1_general_cs NOT NULL,
  `DDRFOG` varchar(6) COLLATE latin1_general_cs NOT NULL,
  `DDRNUM` varchar(6) COLLATE latin1_general_cs NOT NULL,
  `DDRSUB` varchar(4) COLLATE latin1_general_cs NOT NULL,
  `DDRPER` varchar(15) COLLATE latin1_general_cs NOT NULL,
  `DDRVAL` varchar(15) COLLATE latin1_general_cs NOT NULL,
  `ABBPER` varchar(15) COLLATE latin1_general_cs NOT NULL,
  `IMPOSTA` double DEFAULT NULL,
  `IMPOTASI` double DEFAULT NULL,
  `RIDUZIONE` double DEFAULT NULL,
  `DDEDCA` varchar(10) COLLATE latin1_general_cs NOT NULL,
  `DDEREND` varchar(15) COLLATE latin1_general_cs NOT NULL,
  `VERSATO` double NOT NULL,
  PRIMARY KEY (`ROWID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
