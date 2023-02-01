/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ANARTI` (
  `ROWID` int(11) NOT NULL AUTO_INCREMENT,
  `CODART` varchar(6) COLLATE latin1_general_cs DEFAULT NULL,
  `DESART` varchar(80) COLLATE latin1_general_cs DEFAULT NULL,
  `UM` varchar(2) COLLATE latin1_general_cs DEFAULT NULL,
  `CATMER` varchar(2) COLLATE latin1_general_cs DEFAULT NULL,
  `RAGFIS` varchar(2) COLLATE latin1_general_cs DEFAULT NULL,
  `UI` float DEFAULT NULL,
  `GRUPPO` varchar(2) COLLATE latin1_general_cs DEFAULT NULL,
  `CODALT` varchar(26) COLLATE latin1_general_cs DEFAULT NULL,
  PRIMARY KEY (`ROWID`),
  UNIQUE KEY `K_MASTER` (`CODART`),
  KEY `IIADES` (`DESART`,`CODART`),
  KEY `IIACAT` (`CATMER`,`GRUPPO`),
  KEY `IIAALT` (`CODALT`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ARAPPL` (
  `ROWID` int(11) NOT NULL AUTO_INCREMENT,
  `ARCODA` varchar(26) COLLATE latin1_general_cs DEFAULT NULL,
  `ARDESA` varchar(80) COLLATE latin1_general_cs DEFAULT NULL,
  `ARUMIS` varchar(2) COLLATE latin1_general_cs DEFAULT NULL,
  `ARCATE` varchar(2) COLLATE latin1_general_cs DEFAULT NULL,
  `ARGRUP` varchar(2) COLLATE latin1_general_cs DEFAULT NULL,
  `ARSTGR` float DEFAULT NULL,
  `ARFLAG` varchar(2) COLLATE latin1_general_cs DEFAULT NULL,
  PRIMARY KEY (`ROWID`),
  UNIQUE KEY `K_MASTER` (`ARCODA`),
  KEY `IIARDE` (`ARDESA`),
  KEY `IIARCG` (`ARCATE`,`ARGRUP`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ASAPPL` (
  `ROWID` int(11) NOT NULL AUTO_INCREMENT,
  `ASCODA` varchar(26) COLLATE latin1_general_cs DEFAULT NULL,
  `ASRAPU` float DEFAULT NULL,
  `ASDTRE` varchar(6) COLLATE latin1_general_cs DEFAULT NULL,
  `ASDTAG` varchar(6) COLLATE latin1_general_cs DEFAULT NULL,
  `ASFLAG` varchar(2) COLLATE latin1_general_cs DEFAULT NULL,
  PRIMARY KEY (`ROWID`),
  UNIQUE KEY `K_MASTER` (`ASCODA`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `COAPPL` (
  `ROWID` int(11) NOT NULL AUTO_INCREMENT,
  `ASCODA` varchar(26) COLLATE latin1_general_cs DEFAULT NULL,
  `COCODA` varchar(26) COLLATE latin1_general_cs DEFAULT NULL,
  `COOPT2` varchar(10) COLLATE latin1_general_cs DEFAULT NULL,
  `COPROG` varchar(6) COLLATE latin1_general_cs DEFAULT NULL,
  `COSEQU` varchar(6) COLLATE latin1_general_cs DEFAULT NULL,
  `CODATA` varchar(6) COLLATE latin1_general_cs DEFAULT NULL,
  `COIMPI` varchar(20) COLLATE latin1_general_cs DEFAULT NULL,
  PRIMARY KEY (`ROWID`),
  KEY `IIKEY1` (`ASCODA`,`COCODA`,`COOPT2`,`COPROG`),
  KEY `IIKEY2` (`ASCODA`,`COCODA`,`COOPT2`,`COSEQU`),
  KEY `IIKEYC` (`COCODA`,`COOPT2`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `DBASSI` (
  `ROWID` int(11) NOT NULL AUTO_INCREMENT,
  `ASSKEY` varchar(30) COLLATE latin1_general_cs DEFAULT NULL,
  `ASSOBJ` varchar(30) COLLATE latin1_general_cs DEFAULT NULL,
  `ASSCOD` varchar(6) COLLATE latin1_general_cs DEFAULT NULL,
  `ASSOP2` varchar(10) COLLATE latin1_general_cs DEFAULT NULL,
  `ASSOP1` varchar(4) COLLATE latin1_general_cs DEFAULT NULL,
  `ASSINF` varchar(10) COLLATE latin1_general_cs DEFAULT NULL,
  `ASSFLA` varchar(2) COLLATE latin1_general_cs DEFAULT NULL,
  `ASSLEV` double DEFAULT NULL,
  `ASSRPU` double DEFAULT NULL,
  `ASSDAG` varchar(8) COLLATE latin1_general_cs DEFAULT NULL,
  `ASSFIL` varchar(20) COLLATE latin1_general_cs DEFAULT NULL,
  PRIMARY KEY (`ROWID`),
  UNIQUE KEY `K_MASTER` (`ASSKEY`),
  KEY `IIASK1` (`ASSINF`),
  KEY `IIASKS` (`ASSOBJ`),
  KEY `IIASCO` (`ASSCOD`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `DBLEGA` (
  `ROWID` int(11) NOT NULL AUTO_INCREMENT,
  `LEGASS` varchar(30) COLLATE latin1_general_cs DEFAULT NULL,
  `LEGKEY` varchar(30) COLLATE latin1_general_cs DEFAULT NULL,
  `LEGOBJ` varchar(36) COLLATE latin1_general_cs DEFAULT NULL,
  `LEGCOM` varchar(6) COLLATE latin1_general_cs DEFAULT NULL,
  `LEGOP2` varchar(10) COLLATE latin1_general_cs DEFAULT NULL,
  `LEGOP1` varchar(4) COLLATE latin1_general_cs DEFAULT NULL,
  `LEGINF` varchar(10) COLLATE latin1_general_cs DEFAULT NULL,
  `LEGTIP` varchar(2) COLLATE latin1_general_cs DEFAULT NULL,
  `LEGIMP` varchar(20) COLLATE latin1_general_cs DEFAULT NULL,
  `LEGSEQ` varchar(6) COLLATE latin1_general_cs DEFAULT NULL,
  `LEGFIL` varchar(20) COLLATE latin1_general_cs DEFAULT NULL,
  `LEGDES` varchar(30) COLLATE latin1_general_cs DEFAULT NULL,
  `LEGCOS` double DEFAULT NULL,
  `LEGCOI` double DEFAULT NULL,
  `LEGFRM` varchar(4) COLLATE latin1_general_cs DEFAULT NULL,
  `LEGPRO` varchar(6) COLLATE latin1_general_cs DEFAULT NULL,
  `LEGALT` varchar(20) COLLATE latin1_general_cs DEFAULT NULL,
  `LEGSCA` varchar(20) COLLATE latin1_general_cs DEFAULT NULL,
  `LEGFMI` varchar(80) COLLATE latin1_general_cs DEFAULT NULL,
  `LEGFMS` varchar(80) COLLATE latin1_general_cs DEFAULT NULL,
  PRIMARY KEY (`ROWID`),
  KEY `A_LEGASS` (`LEGASS`),
  KEY `IILGKE` (`LEGASS`,`LEGSEQ`),
  KEY `IILGK1` (`LEGASS`,`LEGPRO`),
  KEY `IILGK2` (`LEGOBJ`),
  KEY `IILGK3` (`LEGASS`,`LEGKEY`),
  KEY `IILGK4` (`LEGKEY`),
  KEY `IILGCO` (`LEGKEY`),
  KEY `IILINF` (`LEGINF`,`LEGCOM`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `DBSUPA` (
  `ROWID` int(11) NOT NULL AUTO_INCREMENT,
  `DBSUKE` varchar(30) COLLATE latin1_general_cs DEFAULT NULL,
  `DBNUME` varchar(10) COLLATE latin1_general_cs DEFAULT NULL,
  `DBCIPO` double DEFAULT NULL,
  `DBCSTD` double DEFAULT NULL,
  `DBFILL` varchar(60) COLLATE latin1_general_cs DEFAULT NULL,
  `DBPRIV` double DEFAULT NULL,
  `DBMDCI` double DEFAULT NULL,
  `DBFRMI` varchar(80) COLLATE latin1_general_cs DEFAULT NULL,
  `DBFRMS` varchar(80) COLLATE latin1_general_cs DEFAULT NULL,
  PRIMARY KEY (`ROWID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `DETMOD` (
  `ROWID` int(11) NOT NULL AUTO_INCREMENT,
  `DMOPAD` varchar(26) COLLATE latin1_general_cs DEFAULT NULL,
  `DMOFIG` varchar(26) COLLATE latin1_general_cs DEFAULT NULL,
  `DMOMOD` varchar(6) COLLATE latin1_general_cs DEFAULT NULL,
  PRIMARY KEY (`ROWID`),
  KEY `I_DTK1` (`DMOPAD`,`DMOFIG`,`DMOMOD`),
  KEY `I_DTK2` (`DMOFIG`,`DMOMOD`),
  KEY `I_DTK3` (`DMOMOD`,`DMOFIG`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `DOCUME` (
  `ROWID` int(11) NOT NULL AUTO_INCREMENT,
  `DOCPRG` varchar(10) COLLATE latin1_general_cs DEFAULT NULL,
  `DOCFIE` varchar(10) COLLATE latin1_general_cs DEFAULT NULL,
  `DOCKEY` varchar(20) COLLATE latin1_general_cs DEFAULT NULL,
  `DOCSEQ` double DEFAULT NULL,
  `DOCRIG` varchar(80) COLLATE latin1_general_cs DEFAULT NULL,
  `DOCDAT` varchar(8) COLLATE latin1_general_cs DEFAULT NULL,
  `DOCFIL` varchar(20) COLLATE latin1_general_cs DEFAULT NULL,
  PRIMARY KEY (`ROWID`),
  KEY `IIDCMK` (`DOCPRG`,`DOCFIE`,`DOCKEY`,`DOCSEQ`),
  KEY `IIDCK1` (`DOCPRG`,`DOCKEY`,`DOCSEQ`),
  KEY `IIDCRG` (`DOCRIG`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `FILTAB` (
  `ROWID` int(11) NOT NULL AUTO_INCREMENT,
  `FILKEY` varchar(20) COLLATE latin1_general_cs DEFAULT NULL,
  `FILREC` varchar(60) COLLATE latin1_general_cs DEFAULT NULL,
  PRIMARY KEY (`ROWID`),
  UNIQUE KEY `K_MASTER` (`FILKEY`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `OPERAZ` (
  `ROWID` int(11) NOT NULL AUTO_INCREMENT,
  `OPEUID` varchar(10) COLLATE latin1_general_cs DEFAULT NULL,
  `OPELOG` varchar(20) COLLATE latin1_general_cs DEFAULT NULL,
  `OPEIIP` varchar(20) COLLATE latin1_general_cs DEFAULT NULL,
  `OPEDBA` varchar(20) COLLATE latin1_general_cs DEFAULT NULL,
  `OPEDSE` varchar(100) COLLATE latin1_general_cs DEFAULT NULL,
  `OPEPRG` varchar(20) COLLATE latin1_general_cs DEFAULT NULL,
  `OPEOPE` varchar(2) COLLATE latin1_general_cs DEFAULT NULL,
  `OPEDAT` varchar(8) COLLATE latin1_general_cs DEFAULT NULL,
  `OPETIM` varchar(6) COLLATE latin1_general_cs DEFAULT NULL,
  `OPEEST` varchar(200) COLLATE latin1_general_cs DEFAULT NULL,
  `OPEDIT` varchar(20) COLLATE latin1_general_cs DEFAULT NULL,
  `OPEKEY` text COLLATE latin1_general_cs NOT NULL COMMENT 'CHIAVE DEL RECORD IN ELABORAZIONE',
  `OPESPIDCODE` varchar(20) COLLATE latin1_general_cs NOT NULL,
  PRIMARY KEY (`ROWID`),
  KEY `I_PROG` (`OPEPRG`,`OPEDBA`),
  KEY `I_DBAS` (`OPEDBA`,`OPEDSE`,`OPEOPE`),
  KEY `I_DSET` (`OPEDSE`,`OPEOPE`),
  KEY `I_OPER` (`OPEOPE`,`OPEDSE`,`OPEDBA`),
  KEY `I_USER` (`OPEUID`,`OPEDAT`,`OPETIM`),
  KEY `I_DATE` (`OPEDAT`,`OPETIM`),
  KEY `I_LGIN` (`OPELOG`),
  KEY `I_IDIP` (`OPEIIP`),
  KEY `I_ESTR` (`OPEEST`),
  KEY `OPESPIDCODE` (`OPESPIDCODE`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `UIDLOG` (
  `ROWID` int(11) NOT NULL AUTO_INCREMENT,
  `UIDKEY` varchar(20) COLLATE latin1_general_cs DEFAULT NULL,
  `UIDUID` varchar(6) COLLATE latin1_general_cs DEFAULT NULL,
  `UIDNUT` varchar(40) COLLATE latin1_general_cs DEFAULT NULL,
  `UIDPWD` varchar(40) COLLATE latin1_general_cs DEFAULT NULL,
  `UIDUTE` varchar(6) COLLATE latin1_general_cs DEFAULT NULL,
  `UIDDAT` varchar(8) COLLATE latin1_general_cs DEFAULT NULL,
  PRIMARY KEY (`ROWID`),
  UNIQUE KEY `K_MASTER` (`UIDKEY`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `WILTAB` (
  `ROWID` int(11) NOT NULL AUTO_INCREMENT,
  `KEYFOR` varchar(12) COLLATE latin1_general_cs DEFAULT NULL,
  `IVAFIL` varchar(20) COLLATE latin1_general_cs DEFAULT NULL,
  `FILNUM__1` double DEFAULT NULL,
  `FILNUM__2` double DEFAULT NULL,
  `FILNUM__3` double DEFAULT NULL,
  `FILNUM__4` double DEFAULT NULL,
  `FILNUM__5` double DEFAULT NULL,
  `WILFIL` varchar(50) COLLATE latin1_general_cs DEFAULT NULL,
  `WILFI1__1` varchar(10) COLLATE latin1_general_cs DEFAULT NULL,
  `WILFI1__2` varchar(10) COLLATE latin1_general_cs DEFAULT NULL,
  `WILFI1__3` varchar(10) COLLATE latin1_general_cs DEFAULT NULL,
  `WILFI1__4` varchar(10) COLLATE latin1_general_cs DEFAULT NULL,
  `WILFI1__5` varchar(10) COLLATE latin1_general_cs DEFAULT NULL,
  PRIMARY KEY (`ROWID`),
  KEY `IIWFIL` (`WILFIL`),
  KEY `IIWDES` (`IVAFIL`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
