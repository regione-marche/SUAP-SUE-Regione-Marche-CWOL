/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ACCGRU` (
  `ROWID` int(11) NOT NULL AUTO_INCREMENT,
  `ACCGRU` double NOT NULL,
  `ACCMEN` varchar(60) COLLATE latin1_general_cs NOT NULL,
  `ACCLET` varchar(2) COLLATE latin1_general_cs NOT NULL,
  `ACCSCR` varchar(2) COLLATE latin1_general_cs NOT NULL,
  `ACCVIS` varchar(2) COLLATE latin1_general_cs NOT NULL,
  `ACCFIA__1` varchar(30) COLLATE latin1_general_cs NOT NULL,
  `ACCFIA__2` varchar(30) COLLATE latin1_general_cs NOT NULL,
  `ACCFIA__3` varchar(30) COLLATE latin1_general_cs NOT NULL,
  `ACCFIL__1` double NOT NULL,
  `ACCFIL__2` double NOT NULL,
  `ACCFIL__3` double NOT NULL,
  PRIMARY KEY (`ROWID`),
  KEY `A_ACCGRU` (`ACCGRU`),
  KEY `I_ACCMEN` (`ACCMEN`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `APLGRU` (
  `ROWID` int(11) NOT NULL AUTO_INCREMENT,
  `APLGRU` varchar(10) COLLATE latin1_general_cs NOT NULL,
  `APLMEN` varchar(26) COLLATE latin1_general_cs NOT NULL,
  `APLPRG` varchar(26) COLLATE latin1_general_cs NOT NULL,
  `APLOFF` varchar(2) COLLATE latin1_general_cs NOT NULL,
  `APLREA` varchar(2) COLLATE latin1_general_cs NOT NULL,
  `APLNOE` varchar(2) COLLATE latin1_general_cs NOT NULL,
  `APLFLA__1` varchar(20) COLLATE latin1_general_cs NOT NULL,
  `APLFLA__2` varchar(20) COLLATE latin1_general_cs NOT NULL,
  `APLFLA__3` varchar(20) COLLATE latin1_general_cs NOT NULL,
  `APLFIA__1` double NOT NULL,
  `APLFIA__2` double NOT NULL,
  `APLFIA__3` double NOT NULL,
  `APLSEQ` varchar(6) COLLATE latin1_general_cs NOT NULL,
  `APLNOC` varchar(2) COLLATE latin1_general_cs NOT NULL,
  PRIMARY KEY (`ROWID`),
  KEY `I_MGRU` (`APLGRU`,`APLMEN`,`APLPRG`,`APLSEQ`),
  KEY `I_MGRS` (`APLGRU`,`APLMEN`,`APLSEQ`),
  KEY `I_MGR1` (`APLMEN`,`APLPRG`,`APLSEQ`,`APLGRU`),
  KEY `I_MGR2` (`APLPRG`,`APLGRU`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `APLWRK` (
  `ROWID` int(11) NOT NULL AUTO_INCREMENT,
  `APLGRU` varchar(10) COLLATE latin1_general_cs NOT NULL,
  `APLMEN` varchar(26) COLLATE latin1_general_cs NOT NULL,
  `APLPRG` varchar(26) COLLATE latin1_general_cs NOT NULL,
  `APLOFF` varchar(2) COLLATE latin1_general_cs NOT NULL,
  `APLREA` varchar(2) COLLATE latin1_general_cs NOT NULL,
  `APLNOE` varchar(2) COLLATE latin1_general_cs NOT NULL,
  `APLFLA__1` varchar(20) COLLATE latin1_general_cs NOT NULL,
  `APLFLA__2` varchar(20) COLLATE latin1_general_cs NOT NULL,
  `APLFLA__3` varchar(20) COLLATE latin1_general_cs NOT NULL,
  `APLFIA__1` double NOT NULL,
  `APLFIA__2` double NOT NULL,
  `APLFIA__3` double NOT NULL,
  `APLSEQ` varchar(6) COLLATE latin1_general_cs NOT NULL,
  `APLNOC` varchar(2) COLLATE latin1_general_cs NOT NULL,
  `APWUTE` varchar(20) COLLATE latin1_general_cs NOT NULL,
  PRIMARY KEY (`ROWID`),
  KEY `I_WGRU` (`APWUTE`,`APLGRU`,`APLMEN`,`APLPRG`,`APLSEQ`),
  KEY `I_WGR1` (`APLGRU`,`APLMEN`,`APLPRG`,`APLSEQ`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ARAPPX` (
  `ROWID` int(11) NOT NULL AUTO_INCREMENT,
  `ARCODA` varchar(26) COLLATE latin1_general_cs NOT NULL,
  `ARDESA` varchar(80) COLLATE latin1_general_cs NOT NULL,
  `ARUMIS` varchar(2) COLLATE latin1_general_cs NOT NULL,
  `ARCATE` varchar(2) COLLATE latin1_general_cs NOT NULL,
  `ARGRUP` varchar(2) COLLATE latin1_general_cs NOT NULL,
  `ARSTGR` float NOT NULL,
  `ARFLAG` varchar(2) COLLATE latin1_general_cs NOT NULL,
  PRIMARY KEY (`ROWID`),
  UNIQUE KEY `K_MASTER` (`ARCODA`),
  KEY `IXARDE` (`ARDESA`),
  KEY `IXARCG` (`ARCATE`,`ARGRUP`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ASAPPX` (
  `ROWID` int(11) NOT NULL AUTO_INCREMENT,
  `ASCODA` varchar(26) COLLATE latin1_general_cs NOT NULL,
  `ASRAPU` float NOT NULL,
  `ASDTRE` varchar(6) COLLATE latin1_general_cs NOT NULL,
  `ASDTAG` varchar(6) COLLATE latin1_general_cs NOT NULL,
  `ASFLAG` varchar(2) COLLATE latin1_general_cs NOT NULL,
  PRIMARY KEY (`ROWID`),
  UNIQUE KEY `K_MASTER` (`ASCODA`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `COAPPX` (
  `ROWID` int(11) NOT NULL AUTO_INCREMENT,
  `ASCODA` varchar(26) COLLATE latin1_general_cs NOT NULL,
  `COCODA` varchar(26) COLLATE latin1_general_cs NOT NULL,
  `COOPT2` varchar(10) COLLATE latin1_general_cs NOT NULL,
  `COPROG` varchar(6) COLLATE latin1_general_cs NOT NULL,
  `COSEQU` varchar(6) COLLATE latin1_general_cs NOT NULL,
  `CODATA` varchar(6) COLLATE latin1_general_cs NOT NULL,
  `COIMPI` varchar(20) COLLATE latin1_general_cs NOT NULL,
  PRIMARY KEY (`ROWID`),
  KEY `IXKEY1` (`ASCODA`,`COCODA`,`COOPT2`,`COPROG`),
  KEY `IXKEY2` (`ASCODA`,`COCODA`,`COOPT2`,`COSEQU`),
  KEY `IXKEYC` (`COCODA`,`COOPT2`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `GRUPPI` (
  `ROWID` int(11) NOT NULL AUTO_INCREMENT,
  `GRUCOD` double NOT NULL,
  `GRUDES` varchar(60) COLLATE latin1_general_cs NOT NULL,
  `GRUFIA__1` varchar(30) COLLATE latin1_general_cs NOT NULL,
  `GRUFIA__2` varchar(30) COLLATE latin1_general_cs NOT NULL,
  `GRUFIA__3` varchar(30) COLLATE latin1_general_cs NOT NULL,
  `GRUFIL__1` double NOT NULL,
  `GRUFIL__2` double NOT NULL,
  `GRUFIL__3` double NOT NULL,
  `GRUMETA` text COLLATE latin1_general_cs NOT NULL,
  PRIMARY KEY (`ROWID`),
  UNIQUE KEY `K_MASTER` (`GRUCOD`),
  KEY `I_GRUDES` (`GRUDES`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ITALOG` (
  `ROWID` int(11) NOT NULL AUTO_INCREMENT,
  `LOGDAT` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `LOGUTE` double NOT NULL,
  `LOGMEN` varchar(60) COLLATE latin1_general_cs NOT NULL,
  `LOGORA` varchar(4) COLLATE latin1_general_cs NOT NULL,
  `LOGOPE` varchar(20) COLLATE latin1_general_cs NOT NULL,
  `LOGFIA__1` varchar(30) COLLATE latin1_general_cs NOT NULL,
  `LOGFIA__2` varchar(30) COLLATE latin1_general_cs NOT NULL,
  `LOGFIA__3` varchar(30) COLLATE latin1_general_cs NOT NULL,
  `LOGFIL__1` double NOT NULL,
  `LOGFIL__2` double NOT NULL,
  `LOGFIL__3` double NOT NULL,
  PRIMARY KEY (`ROWID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `MENU` (
  `ROWID` int(11) NOT NULL AUTO_INCREMENT,
  `MENCOD` varchar(60) COLLATE latin1_general_cs NOT NULL,
  `MENDES` varchar(100) COLLATE latin1_general_cs NOT NULL,
  `MENPRO` varchar(100) COLLATE latin1_general_cs NOT NULL,
  `MENPAR` varchar(100) COLLATE latin1_general_cs NOT NULL,
  `MENFIA__1` varchar(30) COLLATE latin1_general_cs NOT NULL,
  `MENFIA__2` varchar(30) COLLATE latin1_general_cs NOT NULL,
  `MENFIA__3` varchar(30) COLLATE latin1_general_cs NOT NULL,
  `MENFIL__1` double NOT NULL,
  `MENFIL__2` double NOT NULL,
  `MENFIL__3` double NOT NULL,
  `MENGPR` varchar(20) COLLATE latin1_general_cs NOT NULL,
  PRIMARY KEY (`ROWID`),
  UNIQUE KEY `K_MASTER` (`MENCOD`),
  KEY `I_MENGPR` (`MENGPR`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `RICHUT` (
  `ROWID` int(11) NOT NULL AUTO_INCREMENT,
  `RICCOG` varchar(78) COLLATE latin1_general_cs NOT NULL,
  `RICNOM` varchar(78) COLLATE latin1_general_cs NOT NULL,
  `RICFIS` varchar(16) COLLATE latin1_general_cs NOT NULL,
  `RICMAI` varchar(256) COLLATE latin1_general_cs NOT NULL COMMENT 'Indirizzo Mail Utente',
  `RICDEN` varchar(70) COLLATE latin1_general_cs NOT NULL,
  `RICVIA` varchar(40) COLLATE latin1_general_cs NOT NULL,
  `RICCOM` varchar(30) COLLATE latin1_general_cs NOT NULL,
  `RICCAP` double NOT NULL,
  `RICPRO` varchar(4) COLLATE latin1_general_cs NOT NULL,
  `RICPIV` varchar(16) COLLATE latin1_general_cs NOT NULL,
  `RICDAT` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `RICTIM` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `RICRES` varchar(2) COLLATE latin1_general_cs NOT NULL,
  `RICIIP` varchar(20) COLLATE latin1_general_cs NOT NULL,
  `RICSTA` varchar(2) COLLATE latin1_general_cs NOT NULL,
  `RICPWD` varchar(100) COLLATE latin1_general_cs NOT NULL,
  `RICCOD` double NOT NULL,
  `RICALB` varchar(50) COLLATE latin1_general_cs NOT NULL,
  `RICALD` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `RICALN` varchar(20) COLLATE latin1_general_cs NOT NULL,
  `RICRCF` varchar(16) COLLATE latin1_general_cs NOT NULL,
  `RICRPI` varchar(16) COLLATE latin1_general_cs NOT NULL,
  `RICRCO` varchar(50) COLLATE latin1_general_cs NOT NULL,
  `RICRNO` varchar(50) COLLATE latin1_general_cs NOT NULL,
  `RICRDN` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `RICRLG` varchar(30) COLLATE latin1_general_cs NOT NULL,
  `RICRCM` varchar(30) COLLATE latin1_general_cs NOT NULL,
  `RICRCP` varchar(6) COLLATE latin1_general_cs NOT NULL,
  `RECRVI` varchar(100) COLLATE latin1_general_cs NOT NULL,
  `RECRES` varchar(50) COLLATE latin1_general_cs NOT NULL,
  `RICTIP` varchar(2) COLLATE latin1_general_cs NOT NULL,
  `RICHSM` varchar(50) COLLATE latin1_general_cs NOT NULL,
  `RICUSM` varchar(100) COLLATE latin1_general_cs NOT NULL,
  `RICPWM` varchar(50) COLLATE latin1_general_cs NOT NULL,
  `RICPRT` varchar(10) COLLATE latin1_general_cs NOT NULL COMMENT 'PORTA INVIO MAIL',
  `RICSMT` varchar(50) COLLATE latin1_general_cs NOT NULL COMMENT 'SECURE SMTP HOST',
  `RICFROM` varchar(100) COLLATE latin1_general_cs NOT NULL COMMENT 'Campo From per la comunicazione',
  PRIMARY KEY (`ROWID`),
  KEY `I_RICCOG` (`RICCOG`,`RICNOM`),
  KEY `I_RICDAT` (`RICDAT`),
  KEY `I_RICFIS` (`RICFIS`),
  KEY `I_RICSTA` (`RICSTA`),
  KEY `I_RICCOD` (`RICCOD`),
  KEY `I_RICRES` (`RICRES`),
  KEY `I_RICPIV` (`RICPIV`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `SESSIO` (
  `ROWID` int(11) NOT NULL AUTO_INCREMENT,
  `SESTOK` varchar(40) COLLATE latin1_general_cs NOT NULL,
  `SESSEQ` varchar(6) COLLATE latin1_general_cs NOT NULL,
  `SESSUB` varchar(6) COLLATE latin1_general_cs NOT NULL,
  `SESNOM` varchar(20) COLLATE latin1_general_cs NOT NULL,
  `SESVAL` varchar(100) COLLATE latin1_general_cs NOT NULL,
  PRIMARY KEY (`ROWID`),
  KEY `I_SESTOS` (`SESTOK`,`SESSEQ`,`SESSUB`,`SESNOM`),
  KEY `I_SESTON` (`SESTOK`,`SESNOM`,`SESSEQ`,`SESSUB`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `STYLE` (
  `ROWID` int(11) NOT NULL AUTO_INCREMENT,
  `STYUTE` double NOT NULL,
  `STYCOD` varchar(20) COLLATE latin1_general_cs NOT NULL,
  `STYDES` text COLLATE latin1_general_cs NOT NULL,
  PRIMARY KEY (`ROWID`),
  KEY `A_STYUTE` (`STYUTE`),
  KEY `A_STYCOD` (`STYCOD`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `TABPAR` (
  `ROWID` int(11) NOT NULL AUTO_INCREMENT,
  `TABCOD` double NOT NULL,
  `TABPAR` varchar(100) COLLATE latin1_general_cs NOT NULL,
  PRIMARY KEY (`ROWID`),
  UNIQUE KEY `K_MASTER` (`TABCOD`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `TOKEN` (
  `ROWID` int(11) NOT NULL AUTO_INCREMENT,
  `TOKCOD` varchar(40) COLLATE latin1_general_cs NOT NULL,
  `TOKDAT` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `TOKORA` varchar(4) COLLATE latin1_general_cs NOT NULL,
  `TOKNUL` double NOT NULL,
  `TOKFIA__1` varchar(30) COLLATE latin1_general_cs NOT NULL,
  `TOKFIA__2` varchar(30) COLLATE latin1_general_cs NOT NULL,
  `TOKFIA__3` varchar(30) COLLATE latin1_general_cs NOT NULL,
  `TOKFIL__1` double NOT NULL,
  `TOKFIL__2` double NOT NULL,
  `TOKFIL__3` double NOT NULL,
  `TOKUTE` double NOT NULL,
  PRIMARY KEY (`ROWID`),
  UNIQUE KEY `K_MASTER` (`TOKCOD`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `TOKSTA` (
  `ROWID` int(11) NOT NULL AUTO_INCREMENT,
  `TOKCOD` varchar(40) COLLATE latin1_general_cs NOT NULL,
  `TOKDAT` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `TOKORA` varchar(4) COLLATE latin1_general_cs NOT NULL,
  `TOKNUL` double NOT NULL,
  `TOKFIA__1` varchar(30) COLLATE latin1_general_cs NOT NULL,
  `TOKFIA__2` varchar(30) COLLATE latin1_general_cs NOT NULL,
  `TOKFIA__3` varchar(30) COLLATE latin1_general_cs NOT NULL,
  `TOKFIL__1` double NOT NULL,
  `TOKFIL__2` double NOT NULL,
  `TOKFIL__3` double NOT NULL,
  `TOKUTE` double NOT NULL,
  PRIMARY KEY (`ROWID`),
  UNIQUE KEY `K_MASTER` (`TOKCOD`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `UTENTI` (
  `ROWID` int(11) NOT NULL AUTO_INCREMENT,
  `UTECOD` double NOT NULL,
  `UTEGRU` double NOT NULL,
  `UTELOG` varchar(20) COLLATE latin1_general_cs NOT NULL,
  `UTEPAS` varchar(100) COLLATE latin1_general_cs NOT NULL,
  `UTEFIS` varchar(16) COLLATE latin1_general_cs NOT NULL,
  `UTEANN__1` double NOT NULL,
  `UTEANN__2` double NOT NULL,
  `UTEANN__3` double NOT NULL,
  `UTEANN__4` double NOT NULL,
  `UTEANN__5` double NOT NULL,
  `UTEANN__6` double NOT NULL,
  `UTEANN__7` double NOT NULL,
  `UTEANN__8` double NOT NULL,
  `UTEANN__9` double NOT NULL,
  `UTEANN__10` double NOT NULL,
  `UTEANA__1` varchar(20) COLLATE latin1_general_cs NOT NULL,
  `UTEANA__2` varchar(20) COLLATE latin1_general_cs NOT NULL,
  `UTEANA__3` varchar(20) COLLATE latin1_general_cs NOT NULL,
  `UTEANA__4` varchar(20) COLLATE latin1_general_cs NOT NULL,
  `UTEANA__5` varchar(20) COLLATE latin1_general_cs NOT NULL,
  `UTEANA__6` varchar(20) COLLATE latin1_general_cs NOT NULL,
  `UTEANA__7` varchar(20) COLLATE latin1_general_cs NOT NULL,
  `UTEANA__8` varchar(20) COLLATE latin1_general_cs NOT NULL,
  `UTEANA__9` varchar(20) COLLATE latin1_general_cs NOT NULL,
  `UTEANA__10` varchar(20) COLLATE latin1_general_cs NOT NULL,
  `UTEFIA__1` varchar(30) COLLATE latin1_general_cs NOT NULL,
  `UTEFIA__2` varchar(30) COLLATE latin1_general_cs NOT NULL,
  `UTEFIA__3` varchar(30) COLLATE latin1_general_cs NOT NULL,
  `UTEFIL__1` double NOT NULL,
  `UTEFIL__2` double NOT NULL,
  `UTEFIL__3` double NOT NULL,
  `UTEDPA` double NOT NULL,
  `UTEDAC` double NOT NULL,
  `UTEUPA` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `UTESPA` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `UTEADD` varchar(10) COLLATE latin1_general_cs NOT NULL,
  `UTEGEX__1` double NOT NULL,
  `UTEGEX__2` double NOT NULL,
  `UTEGEX__3` double NOT NULL,
  `UTEGEX__4` double NOT NULL,
  `UTEGEX__5` double NOT NULL,
  `UTEGEX__6` double NOT NULL,
  `UTEGEX__7` double NOT NULL,
  `UTEGEX__8` double NOT NULL,
  `UTEGEX__9` double NOT NULL,
  `UTEGEX__10` double NOT NULL,
  `UTEGEX__11` double NOT NULL,
  `UTEGEX__12` double NOT NULL,
  `UTEGEX__13` double NOT NULL,
  `UTEGEX__14` double NOT NULL,
  `UTEGEX__15` double NOT NULL,
  `UTEGEX__16` double NOT NULL,
  `UTEGEX__17` double NOT NULL,
  `UTEGEX__18` double NOT NULL,
  `UTEGEX__19` double NOT NULL,
  `UTEGEX__20` double NOT NULL,
  `UTEGEX__21` double NOT NULL,
  `UTEGEX__22` double NOT NULL,
  `UTEGEX__23` double NOT NULL,
  `UTEGEX__24` double NOT NULL,
  `UTEGEX__25` double NOT NULL,
  `UTEGEX__26` double NOT NULL,
  `UTEGEX__27` double NOT NULL,
  `UTEGEX__28` double NOT NULL,
  `UTEGEX__29` double NOT NULL,
  `UTEGEX__30` double NOT NULL,
  `UTEPIN` varchar(20) COLLATE latin1_general_cs NOT NULL,
  `UTEACC` double NOT NULL,
  `UTEPEG` varchar(60) COLLATE latin1_general_cs NOT NULL,
  `UTEDUA` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `UTEVIG` varchar(10) COLLATE latin1_general_cs NOT NULL,
  `UTEDIS` varchar(20) COLLATE latin1_general_cs NOT NULL,
  `UTECLI` varchar(10) COLLATE latin1_general_cs NOT NULL,
  `UTEREG` varchar(20) COLLATE latin1_general_cs NOT NULL,
  `UTECIM` varchar(20) COLLATE latin1_general_cs NOT NULL,
  `UTEDATAULUSO` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `DATAINIZ` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `DATAFINE` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `UTEFLADMIN` smallint(1) NOT NULL,
  `UTELDAP` varchar(255) COLLATE latin1_general_cs NOT NULL,
  PRIMARY KEY (`ROWID`),
  UNIQUE KEY `K_MASTER` (`UTECOD`),
  KEY `I_UTEGRU` (`UTEGRU`),
  KEY `I_UTELOG` (`UTELOG`),
  KEY `I_UTEFIS` (`UTEFIS`),
  KEY `I_UTEPAS` (`UTEPAS`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `UTEPSP` (
  `ROWID` int(11) NOT NULL AUTO_INCREMENT,
  `UTSPCD` varchar(6) COLLATE latin1_general_cs NOT NULL,
  `UTSPAP` varchar(2) COLLATE latin1_general_cs NOT NULL,
  `UTSPTP` varchar(2) COLLATE latin1_general_cs NOT NULL,
  `UTSPTX` varchar(40) COLLATE latin1_general_cs NOT NULL,
  `UTSPFL` varchar(2) COLLATE latin1_general_cs NOT NULL,
  PRIMARY KEY (`ROWID`),
  KEY `I_UTSPK1` (`UTSPCD`,`UTSPAP`,`UTSPTX`,`UTSPTP`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
