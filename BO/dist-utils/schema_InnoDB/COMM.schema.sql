/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `AGGIUNTIVI` (
  `ROWID` int(11) NOT NULL AUTO_INCREMENT COMMENT 'id univoco',
  `ALIAS` text COLLATE latin1_general_cs NOT NULL COMMENT 'Alias campo aggiuntivo',
  `INDICE` text COLLATE latin1_general_cs NOT NULL COMMENT 'Indice',
  `TABELLA` text COLLATE latin1_general_cs NOT NULL COMMENT 'nome tabella db',
  `CAMPO` text COLLATE latin1_general_cs NOT NULL COMMENT 'nome campo aggiuntivo',
  `VALIDODA` varchar(8) COLLATE latin1_general_cs NOT NULL COMMENT 'valido da',
  `VALIDOA` varchar(8) COLLATE latin1_general_cs NOT NULL COMMENT 'valido a',
  PRIMARY KEY (`ROWID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `CAMPICOMLIC` (
  `ROWID` int(11) NOT NULL AUTO_INCREMENT COMMENT 'id univoco',
  `CAMPO` text COLLATE latin1_general_cs NOT NULL COMMENT 'nome campo comlic',
  `DESCRIZIONE` text COLLATE latin1_general_cs NOT NULL COMMENT 'Descrizione campo',
  PRIMARY KEY (`ROWID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `COMABILITAAUT` (
  `ROWID` int(11) NOT NULL AUTO_INCREMENT,
  `ANACOD` varchar(6) COLLATE latin1_general_cs NOT NULL,
  `TIPOEVENTO` varchar(20) COLLATE latin1_general_cs NOT NULL,
  `NOMEPADRE` varchar(100) COLLATE latin1_general_cs NOT NULL,
  `NOMEDIV` varchar(100) COLLATE latin1_general_cs NOT NULL,
  `ABILITA` int(11) NOT NULL,
  `ANAFI1__1` text COLLATE latin1_general_cs NOT NULL,
  PRIMARY KEY (`ROWID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs COMMENT='ABILITAZIONE PER TIPOLOGIA AUTORIZZAZIONI';
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `COMANA` (
  `ROWID` int(11) NOT NULL AUTO_INCREMENT,
  `ANACAT` varchar(6) COLLATE latin1_general_cs NOT NULL,
  `ANACOD` varchar(6) COLLATE latin1_general_cs NOT NULL,
  `ANADES` varchar(80) COLLATE latin1_general_cs NOT NULL,
  `ANAFI1__1` text COLLATE latin1_general_cs NOT NULL,
  `ANAFI1__2` varchar(500) COLLATE latin1_general_cs NOT NULL,
  `ANAFI1__3` varchar(30) COLLATE latin1_general_cs NOT NULL,
  `ANAFI1__4` varchar(30) COLLATE latin1_general_cs NOT NULL,
  `ANAFI2__1` double NOT NULL,
  `ANAFI2__2` double NOT NULL,
  `ANAFI2__3` varchar(500) COLLATE latin1_general_cs NOT NULL,
  `ANAFI2__4` text COLLATE latin1_general_cs NOT NULL,
  PRIMARY KEY (`ROWID`),
  KEY `I_CODKEY` (`ANACAT`,`ANACOD`),
  KEY `I_DESKEY` (`ANACAT`,`ANADES`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `COMASC` (
  `ROWID` int(11) NOT NULL AUTO_INCREMENT,
  `ASCPRO` varchar(10) COLLATE latin1_general_cs NOT NULL,
  `ASCFLG` varchar(2) COLLATE latin1_general_cs NOT NULL,
  `ASCVIA__1` varchar(100) COLLATE latin1_general_cs NOT NULL,
  `ASCVIA__2` varchar(100) COLLATE latin1_general_cs NOT NULL,
  `ASCVIA__3` varchar(100) COLLATE latin1_general_cs NOT NULL,
  `ASCVIA__4` varchar(100) COLLATE latin1_general_cs NOT NULL,
  `ASCCIV__1` varchar(6) COLLATE latin1_general_cs NOT NULL,
  `ASCCIV__2` varchar(6) COLLATE latin1_general_cs NOT NULL,
  `ASCCIV__3` varchar(6) COLLATE latin1_general_cs NOT NULL,
  `ASCCIV__4` varchar(6) COLLATE latin1_general_cs NOT NULL,
  `ASCRAG__1` varchar(100) COLLATE latin1_general_cs NOT NULL,
  `ASCRAG__2` varchar(100) COLLATE latin1_general_cs NOT NULL,
  `ASCRAG__3` varchar(100) COLLATE latin1_general_cs NOT NULL,
  `ASCRAG__4` varchar(100) COLLATE latin1_general_cs NOT NULL,
  `ASCTIP` varchar(100) COLLATE latin1_general_cs NOT NULL,
  `ASCNFA` varchar(100) COLLATE latin1_general_cs NOT NULL,
  `ASCVEL` varchar(18) COLLATE latin1_general_cs NOT NULL,
  `ASCPOR` varchar(18) COLLATE latin1_general_cs NOT NULL,
  `ASCCOR` varchar(18) COLLATE latin1_general_cs NOT NULL,
  `ASCNFE` varchar(18) COLLATE latin1_general_cs NOT NULL,
  `ASCAZI` varchar(100) COLLATE latin1_general_cs NOT NULL,
  `ASCLOC__1` varchar(40) COLLATE latin1_general_cs NOT NULL,
  `ASCLOC__2` varchar(40) COLLATE latin1_general_cs NOT NULL,
  `ASCLOC__3` varchar(40) COLLATE latin1_general_cs NOT NULL,
  `ASCCAP__1` varchar(6) COLLATE latin1_general_cs NOT NULL,
  `ASCCAP__2` varchar(6) COLLATE latin1_general_cs NOT NULL,
  `ASCCAP__3` varchar(6) COLLATE latin1_general_cs NOT NULL,
  `ASCTEL__1` varchar(20) COLLATE latin1_general_cs NOT NULL,
  `ASCTEL__2` varchar(20) COLLATE latin1_general_cs NOT NULL,
  `ASCTEL__3` varchar(20) COLLATE latin1_general_cs NOT NULL,
  `ASCMIS` varchar(20) COLLATE latin1_general_cs NOT NULL,
  `ASCMUS` varchar(20) COLLATE latin1_general_cs NOT NULL,
  `ASCCOM` varchar(20) COLLATE latin1_general_cs NOT NULL,
  `ASCTPP` varchar(4) COLLATE latin1_general_cs NOT NULL,
  `ASCVER` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `ASCSIG` varchar(8) COLLATE latin1_general_cs NOT NULL,
  PRIMARY KEY (`ROWID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `COMATECO` (
  `ROWID` int(11) NOT NULL AUTO_INCREMENT,
  `CODATECO` varchar(20) COLLATE latin1_general_cs NOT NULL,
  `DESATECO` text COLLATE latin1_general_cs NOT NULL,
  PRIMARY KEY (`ROWID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs COMMENT='CODICI ATECO';
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `COMCLASSLICSPEC` (
  `ROWID` int(11) NOT NULL AUTO_INCREMENT,
  `CLLID` int(11) NOT NULL COMMENT 'IDENTIFICATIVO CLASSE',
  `CLLDESCRIZIONE` text COLLATE latin1_general_cs NOT NULL COMMENT 'DESCRIZIONE',
  `CLLIDPREC` int(11) NOT NULL,
  PRIMARY KEY (`ROWID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `COMCLASSRIC` (
  `ROWID` int(11) NOT NULL AUTO_INCREMENT,
  `CLRCODICE` int(11) NOT NULL COMMENT 'CODICE CLASSIFICAZIONE',
  `CLRDESCVAR` text COLLATE latin1_general_cs NOT NULL COMMENT 'DESCRIZIONE',
  `CLRFISSO` varchar(2) COLLATE latin1_general_cs NOT NULL,
  `CLRAP` varchar(2) COLLATE latin1_general_cs NOT NULL,
  PRIMARY KEY (`ROWID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `COMCOM` (
  `ROWID` int(11) NOT NULL AUTO_INCREMENT,
  `CMNCOD` varchar(6) COLLATE latin1_general_cs NOT NULL,
  `CMNDES` varchar(40) COLLATE latin1_general_cs NOT NULL,
  `CMNCAP` varchar(6) COLLATE latin1_general_cs NOT NULL,
  `CMNPRO` varchar(4) COLLATE latin1_general_cs NOT NULL,
  `CMNSTA` varchar(12) COLLATE latin1_general_cs NOT NULL,
  PRIMARY KEY (`ROWID`),
  UNIQUE KEY `K_MASTER` (`CMNCOD`),
  KEY `I_CMNDES` (`CMNDES`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `COMCON` (
  `ROWID` int(11) NOT NULL AUTO_INCREMENT,
  `CONLIC` varchar(24) COLLATE latin1_general_cs NOT NULL,
  `CONTIT` varchar(150) COLLATE latin1_general_cs NOT NULL,
  `CONCOF` varchar(16) COLLATE latin1_general_cs NOT NULL,
  `CONIND` varchar(100) COLLATE latin1_general_cs NOT NULL,
  `CONAUT` varchar(10) COLLATE latin1_general_cs NOT NULL,
  `CONDAT` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `CONMEQ` double NOT NULL,
  `CONTIP` varchar(4) COLLATE latin1_general_cs NOT NULL,
  `CONPRO` varchar(10) COLLATE latin1_general_cs NOT NULL,
  `CONMAL` double NOT NULL,
  `CONMNL` double NOT NULL,
  `CONMSP` double NOT NULL,
  PRIMARY KEY (`ROWID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `COMDAG` (
  `ROWID` int(11) NOT NULL AUTO_INCREMENT,
  `DAGPRC` varchar(10) COLLATE latin1_general_cs NOT NULL,
  `DAGIDC` varchar(20) COLLATE latin1_general_cs NOT NULL,
  `DAGVAL` text COLLATE latin1_general_cs NOT NULL,
  PRIMARY KEY (`ROWID`),
  KEY `I_DAGIDC` (`DAGIDC`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `COMDIT` (
  `ROWID` int(11) NOT NULL AUTO_INCREMENT,
  `DITCOD` double NOT NULL,
  `DITCOG` varchar(72) COLLATE latin1_general_cs NOT NULL,
  `DITNOM` varchar(72) COLLATE latin1_general_cs NOT NULL,
  `DITCOF` varchar(16) COLLATE latin1_general_cs NOT NULL,
  `DITDNA` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `DITCIT` varchar(20) COLLATE latin1_general_cs NOT NULL,
  `DITSEX` varchar(2) COLLATE latin1_general_cs NOT NULL,
  `DITSTN` varchar(20) COLLATE latin1_general_cs NOT NULL,
  `DITPRN` varchar(4) COLLATE latin1_general_cs NOT NULL,
  `DITCON` varchar(40) COLLATE latin1_general_cs NOT NULL,
  `DITPRR` varchar(4) COLLATE latin1_general_cs NOT NULL,
  `DITCOR` varchar(40) COLLATE latin1_general_cs NOT NULL,
  `DITIND` varchar(100) COLLATE latin1_general_cs NOT NULL,
  `DITCAP` varchar(6) COLLATE latin1_general_cs NOT NULL,
  `DITCIV` varchar(6) COLLATE latin1_general_cs NOT NULL,
  `DITPPI` varchar(16) COLLATE latin1_general_cs NOT NULL,
  `DITRDE` varchar(100) COLLATE latin1_general_cs NOT NULL,
  `DITRCO` varchar(40) COLLATE latin1_general_cs NOT NULL,
  `DITRPR` varchar(4) COLLATE latin1_general_cs NOT NULL,
  `DITRIN` varchar(100) COLLATE latin1_general_cs NOT NULL,
  `DITRCI` varchar(6) COLLATE latin1_general_cs NOT NULL,
  `DITRCA` varchar(6) COLLATE latin1_general_cs NOT NULL,
  `DITRTE` varchar(30) COLLATE latin1_general_cs NOT NULL,
  `DITRRI` varchar(20) COLLATE latin1_general_cs NOT NULL,
  `DITRCC` varchar(40) COLLATE latin1_general_cs NOT NULL,
  `DITTIT` varchar(16) COLLATE latin1_general_cs NOT NULL,
  `DITLEG` varchar(2) COLLATE latin1_general_cs NOT NULL,
  `DITCLF` varchar(16) COLLATE latin1_general_cs NOT NULL,
  `DITDIR` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `DITNAT` varchar(20) COLLATE latin1_general_cs NOT NULL,
  `DITDOC` varchar(40) COLLATE latin1_general_cs NOT NULL,
  `DITDNU` varchar(30) COLLATE latin1_general_cs NOT NULL,
  `DITRIL` varchar(40) COLLATE latin1_general_cs NOT NULL,
  `DITDRI` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `DITFAX` varchar(40) COLLATE latin1_general_cs NOT NULL,
  `DITMAI` varchar(60) COLLATE latin1_general_cs NOT NULL,
  `DITSIT` varchar(80) COLLATE latin1_general_cs NOT NULL,
  `DITCEL` varchar(40) COLLATE latin1_general_cs NOT NULL,
  `DITNOT` text COLLATE latin1_general_cs NOT NULL,
  `DITRES` varchar(100) COLLATE latin1_general_cs NOT NULL,
  `DITREG` varchar(28) COLLATE latin1_general_cs NOT NULL,
  `DITDPR` varchar(2) COLLATE latin1_general_cs NOT NULL,
  `DITDDR` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `DITIAT` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `DITPOS__1` double NOT NULL,
  `DITPOS__2` double NOT NULL,
  `DITPOS__3` double NOT NULL,
  `DITRET__1` varchar(40) COLLATE latin1_general_cs NOT NULL,
  `DITRET__2` varchar(40) COLLATE latin1_general_cs NOT NULL,
  `DITRET__3` varchar(40) COLLATE latin1_general_cs NOT NULL,
  `DITRER__1` varchar(2) COLLATE latin1_general_cs NOT NULL,
  `DITRER__2` varchar(2) COLLATE latin1_general_cs NOT NULL,
  `DITRER__3` varchar(2) COLLATE latin1_general_cs NOT NULL,
  `DITREA` double NOT NULL,
  `DITDRE` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `DITRED__1` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `DITRED__2` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `DITRED__3` varchar(8) COLLATE latin1_general_cs NOT NULL,
  PRIMARY KEY (`ROWID`),
  UNIQUE KEY `I_DITCOD` (`DITCOD`),
  KEY `I_DITCOF` (`DITCOF`),
  KEY `I_DITCOG` (`DITCOG`,`DITNOM`),
  KEY `I_DITRDE` (`DITRDE`),
  KEY `I_DITPPI` (`DITPPI`),
  KEY `I_DITLEG` (`DITLEG`),
  KEY `I_DITCLF` (`DITCLF`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `COMDOC` (
  `ROWID` int(11) NOT NULL AUTO_INCREMENT,
  `DOCNUM` double NOT NULL,
  `DOCDES` varchar(80) COLLATE latin1_general_cs NOT NULL,
  `DOCTES` varchar(20) COLLATE latin1_general_cs NOT NULL,
  `DOCEST` varchar(2) COLLATE latin1_general_cs NOT NULL,
  `DOCSOC` double NOT NULL,
  `DOCOBL` double NOT NULL,
  PRIMARY KEY (`ROWID`),
  UNIQUE KEY `K_MASTER` (`DOCNUM`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `COMEVENTI` (
  `ROWID` int(11) NOT NULL AUTO_INCREMENT,
  `TIPOEVENTO` varchar(20) COLLATE latin1_general_cs NOT NULL COMMENT 'TIPO EVENTO',
  `DESCEVENTO` varchar(100) COLLATE latin1_general_cs NOT NULL COMMENT 'DESCRIZIONE EVENTO',
  `TIPOAUT` varchar(6) COLLATE latin1_general_cs NOT NULL COMMENT 'TIPO AUTORIZZAZIONE',
  `FILLER` varchar(100) COLLATE latin1_general_cs NOT NULL COMMENT 'FILLER',
  `STATOPREC` varchar(200) COLLATE latin1_general_cs NOT NULL COMMENT 'Stato del precedente',
  `ANAFI1__1` text COLLATE latin1_general_cs NOT NULL,
  `TIPAUTESTESA` varchar(50) COLLATE latin1_general_cs NOT NULL COMMENT 'Tipo Aut. Estesa',
  PRIMARY KEY (`ROWID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs COMMENT='GESTIONE EVENTI';
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `COMGDC` (
  `ROWID` int(11) NOT NULL AUTO_INCREMENT,
  `GDCCOD` text COLLATE latin1_general_cs NOT NULL,
  `GDCTIP` varchar(4) COLLATE latin1_general_cs NOT NULL,
  `GDCSEZ` varchar(4) COLLATE latin1_general_cs NOT NULL,
  `GDCALI` varchar(2) COLLATE latin1_general_cs NOT NULL,
  `GDCNAL` varchar(2) COLLATE latin1_general_cs NOT NULL,
  PRIMARY KEY (`ROWID`),
  KEY `I_GDCTIP` (`GDCTIP`),
  KEY `I_GDCKEY` (`GDCTIP`,`GDCSEZ`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `COMIDATECO` (
  `ROWID` int(11) NOT NULL AUTO_INCREMENT,
  `TIPOAUT` varchar(6) COLLATE latin1_general_cs NOT NULL,
  `ATECO` varchar(20) COLLATE latin1_general_cs NOT NULL,
  PRIMARY KEY (`ROWID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `COMIDC` (
  `ROWID` int(11) NOT NULL AUTO_INCREMENT,
  `IDCKEY` varchar(20) COLLATE latin1_general_cs NOT NULL,
  `IDCSEQ` double NOT NULL,
  `IDCATP` varchar(4) COLLATE latin1_general_cs NOT NULL,
  `IDCDES` varchar(60) COLLATE latin1_general_cs NOT NULL,
  `IDCTIP` varchar(10) COLLATE latin1_general_cs NOT NULL,
  PRIMARY KEY (`ROWID`),
  UNIQUE KEY `K_MASTER` (`IDCATP`,`IDCKEY`),
  KEY `I_IDCATP` (`IDCATP`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `COMIDCABILITA` (
  `ROWID` int(11) NOT NULL AUTO_INCREMENT,
  `ANACOD` varchar(6) COLLATE latin1_general_cs NOT NULL,
  `TIPOEVENTO` varchar(20) COLLATE latin1_general_cs NOT NULL,
  `IDCKEY` varchar(20) COLLATE latin1_general_cs NOT NULL,
  `IDCSEQ` double NOT NULL,
  `IDCATP` varchar(4) COLLATE latin1_general_cs NOT NULL,
  `IDCDES` varchar(60) COLLATE latin1_general_cs NOT NULL,
  `IDCTIP` varchar(10) COLLATE latin1_general_cs NOT NULL,
  `ABILITA` tinyint(4) NOT NULL,
  PRIMARY KEY (`ROWID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `COMLIC` (
  `ROWID` int(11) NOT NULL AUTO_INCREMENT,
  `LICPRO` varchar(10) COLLATE latin1_general_cs NOT NULL,
  `LICAUT` varchar(50) COLLATE latin1_general_cs NOT NULL,
  `LICTIP` varchar(50) COLLATE latin1_general_cs NOT NULL,
  `LICSEZ` varchar(4) COLLATE latin1_general_cs NOT NULL,
  `LICTIPSPEC` int(11) NOT NULL COMMENT 'codice comtipolicspec',
  `LICSEZSPEC` int(11) NOT NULL COMMENT 'codice comtiporic',
  `LICDAT` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `LICDRE` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `LICDIN` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `LICDFI` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `LICRES` varchar(30) COLLATE latin1_general_cs NOT NULL,
  `LICRFI` varchar(30) COLLATE latin1_general_cs NOT NULL,
  `LICNPR` varchar(20) COLLATE latin1_general_cs NOT NULL,
  `LICDPR` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `LICTPI` varchar(16) COLLATE latin1_general_cs NOT NULL,
  `LICTCO` varchar(40) COLLATE latin1_general_cs NOT NULL,
  `LICTPR` varchar(4) COLLATE latin1_general_cs NOT NULL,
  `LICTIN` varchar(100) COLLATE latin1_general_cs NOT NULL,
  `LICTCI` varchar(6) COLLATE latin1_general_cs NOT NULL,
  `LICTCA` varchar(6) COLLATE latin1_general_cs NOT NULL,
  `LICTTE` varchar(30) COLLATE latin1_general_cs NOT NULL,
  `LICTRI` varchar(20) COLLATE latin1_general_cs NOT NULL,
  `LICTCC` varchar(40) COLLATE latin1_general_cs NOT NULL,
  `LICRCF` varchar(16) COLLATE latin1_general_cs NOT NULL,
  `LICRPI` varchar(16) COLLATE latin1_general_cs NOT NULL,
  `LICRDE` varchar(100) COLLATE latin1_general_cs NOT NULL,
  `LICRCO` varchar(40) COLLATE latin1_general_cs NOT NULL,
  `LICRPR` varchar(4) COLLATE latin1_general_cs NOT NULL,
  `LICRIN` varchar(100) COLLATE latin1_general_cs NOT NULL,
  `LICRCI` varchar(6) COLLATE latin1_general_cs NOT NULL,
  `LICRCA` varchar(6) COLLATE latin1_general_cs NOT NULL,
  `LICRTE` varchar(30) COLLATE latin1_general_cs NOT NULL,
  `LICRRI` varchar(20) COLLATE latin1_general_cs NOT NULL,
  `LICRCC` varchar(40) COLLATE latin1_general_cs NOT NULL,
  `LICEIN` varchar(100) COLLATE latin1_general_cs NOT NULL,
  `LICECI` varchar(50) COLLATE latin1_general_cs NOT NULL,
  `LICEAL` varchar(2) COLLATE latin1_general_cs NOT NULL,
  `LICEAM` double NOT NULL,
  `LICENA` varchar(2) COLLATE latin1_general_cs NOT NULL,
  `LICENM` double NOT NULL,
  `LICEMO` varchar(2) COLLATE latin1_general_cs NOT NULL,
  `LICEFA` varchar(2) COLLATE latin1_general_cs NOT NULL,
  `LICECA` varchar(2) COLLATE latin1_general_cs NOT NULL,
  `LICETM` double NOT NULL,
  `LICEMQ` double NOT NULL,
  `LICEPE` varchar(2) COLLATE latin1_general_cs NOT NULL,
  `LICEST` varchar(2) COLLATE latin1_general_cs NOT NULL,
  `LICESD` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `LICESA` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `LICCES` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `LICMOT` varchar(20) COLLATE latin1_general_cs NOT NULL,
  `LICNEW` varchar(24) COLLATE latin1_general_cs NOT NULL,
  `LICOLD` varchar(24) COLLATE latin1_general_cs NOT NULL,
  `LICRCS` varchar(40) COLLATE latin1_general_cs NOT NULL,
  `LICRCN` varchar(10) COLLATE latin1_general_cs NOT NULL,
  `LICRCD` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `LICRIS` varchar(30) COLLATE latin1_general_cs NOT NULL,
  `LICRID` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `LICRIF` varchar(30) COLLATE latin1_general_cs NOT NULL,
  `LICRFC` varchar(20) COLLATE latin1_general_cs NOT NULL,
  `LICRSE` varchar(20) COLLATE latin1_general_cs NOT NULL,
  `LICRFA` varchar(4) COLLATE latin1_general_cs NOT NULL,
  `LICESM` varchar(2) COLLATE latin1_general_cs NOT NULL,
  `LICESG` varchar(2) COLLATE latin1_general_cs NOT NULL,
  `LICESI` varchar(30) COLLATE latin1_general_cs NOT NULL,
  `LICOCS` varchar(20) COLLATE latin1_general_cs NOT NULL,
  `LICSES` varchar(20) COLLATE latin1_general_cs NOT NULL,
  `LICCPA` varchar(4) COLLATE latin1_general_cs NOT NULL,
  `LICPRP` varchar(2) COLLATE latin1_general_cs NOT NULL,
  `LICAFF` varchar(2) COLLATE latin1_general_cs NOT NULL,
  `LICALT` varchar(20) COLLATE latin1_general_cs NOT NULL,
  `LICBMQ` double NOT NULL,
  `LICPR1` varchar(2) COLLATE latin1_general_cs NOT NULL,
  `LICPR2` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `LICAT1` varchar(2) COLLATE latin1_general_cs NOT NULL,
  `LICAT2` varchar(2) COLLATE latin1_general_cs NOT NULL,
  `LICAT3` varchar(2) COLLATE latin1_general_cs NOT NULL,
  `LICAT4` varchar(2) COLLATE latin1_general_cs NOT NULL,
  `LICAT5` varchar(2) COLLATE latin1_general_cs NOT NULL,
  `LICAT6` varchar(2) COLLATE latin1_general_cs NOT NULL,
  `LICAT7` varchar(30) COLLATE latin1_general_cs NOT NULL,
  `LICATT` varchar(30) COLLATE latin1_general_cs NOT NULL,
  `LICATN` varchar(50) COLLATE latin1_general_cs NOT NULL,
  `LICATD` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `LICATR` varchar(30) COLLATE latin1_general_cs NOT NULL,
  `LICCOG` varchar(72) COLLATE latin1_general_cs NOT NULL,
  `LICNOM` varchar(72) COLLATE latin1_general_cs NOT NULL,
  `LICCOF` varchar(16) COLLATE latin1_general_cs NOT NULL,
  `LICDNA` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `LICCIT` varchar(20) COLLATE latin1_general_cs NOT NULL,
  `LICSEX` varchar(2) COLLATE latin1_general_cs NOT NULL,
  `LICSTN` varchar(40) COLLATE latin1_general_cs NOT NULL,
  `LICPRN` varchar(4) COLLATE latin1_general_cs NOT NULL,
  `LICCON` varchar(40) COLLATE latin1_general_cs NOT NULL,
  `LICPRR` varchar(4) COLLATE latin1_general_cs NOT NULL,
  `LICCOR` varchar(40) COLLATE latin1_general_cs NOT NULL,
  `LICIND` varchar(100) COLLATE latin1_general_cs NOT NULL,
  `LICCAP` varchar(6) COLLATE latin1_general_cs NOT NULL,
  `LICCIV` varchar(6) COLLATE latin1_general_cs NOT NULL,
  `LICDSP` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `LICGIU` varchar(20) COLLATE latin1_general_cs NOT NULL,
  `LICPIA` varchar(500) COLLATE latin1_general_cs NOT NULL,
  `LICARD` text COLLATE latin1_general_cs NOT NULL,
  `LICCMR` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `LICDRI` varchar(20) COLLATE latin1_general_cs NOT NULL,
  `LICCRA` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `LICDRA` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `LICNRA` varchar(10) COLLATE latin1_general_cs NOT NULL,
  `LICDPI` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `LICNPI` varchar(10) COLLATE latin1_general_cs NOT NULL,
  `LICDIS` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `LICDAN` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `LICCPP` varchar(40) COLLATE latin1_general_cs NOT NULL,
  `LICAIA` varchar(10) COLLATE latin1_general_cs NOT NULL,
  `LICNRE` varchar(10) COLLATE latin1_general_cs NOT NULL,
  `LICSBD` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `LICSBT` varchar(20) COLLATE latin1_general_cs NOT NULL,
  `LICALE` varchar(30) COLLATE latin1_general_cs NOT NULL,
  `LICALB` varchar(40) COLLATE latin1_general_cs NOT NULL,
  `LICDAL` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `LICNUA` varchar(10) COLLATE latin1_general_cs NOT NULL,
  `LICAUE` varchar(30) COLLATE latin1_general_cs NOT NULL,
  `LICDLI` varchar(40) COLLATE latin1_general_cs NOT NULL,
  `LICDLA` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `LICNUL` varchar(10) COLLATE latin1_general_cs NOT NULL,
  `LICDIA` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `LICAT8` varchar(80) COLLATE latin1_general_cs NOT NULL,
  `LICATE` varchar(40) COLLATE latin1_general_cs NOT NULL,
  `LICIFR` varchar(40) COLLATE latin1_general_cs NOT NULL,
  `LICSFR` varchar(40) COLLATE latin1_general_cs NOT NULL,
  `LICFAX` varchar(20) COLLATE latin1_general_cs NOT NULL,
  `LICSIT` varchar(60) COLLATE latin1_general_cs NOT NULL,
  `LICMAI` varchar(60) COLLATE latin1_general_cs NOT NULL,
  `LICDAP` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `LICAT9` varchar(80) COLLATE latin1_general_cs NOT NULL,
  `LICQUA` varchar(30) COLLATE latin1_general_cs NOT NULL,
  `LICSCO` varchar(40) COLLATE latin1_general_cs NOT NULL,
  `LICSPR` varchar(500) COLLATE latin1_general_cs NOT NULL COMMENT 'Denominazoione sito internet',
  `LICSIN` varchar(100) COLLATE latin1_general_cs NOT NULL,
  `LICSCI` varchar(6) COLLATE latin1_general_cs NOT NULL,
  `LICSCA` varchar(6) COLLATE latin1_general_cs NOT NULL,
  `LICCHI` varchar(2) COLLATE latin1_general_cs NOT NULL,
  `LICZON` varchar(100) COLLATE latin1_general_cs NOT NULL,
  `LICATP` text COLLATE latin1_general_cs NOT NULL,
  `LICINS` varchar(150) COLLATE latin1_general_cs NOT NULL,
  `LICSTP` varchar(50) COLLATE latin1_general_cs NOT NULL,
  `LICNOT` text COLLATE latin1_general_cs NOT NULL,
  `LICRGN` varchar(100) COLLATE latin1_general_cs NOT NULL,
  `OWNAUT` varchar(10) COLLATE latin1_general_cs NOT NULL,
  `OWNDAT` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `LICSTE` varchar(20) COLLATE latin1_general_cs NOT NULL,
  `LICSMQ` double NOT NULL,
  `LICTAP` varchar(4) COLLATE latin1_general_cs NOT NULL,
  `LICDTP` varchar(250) COLLATE latin1_general_cs NOT NULL,
  `LICRDC` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `LICTDC` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `LICPU1` varchar(2) COLLATE latin1_general_cs NOT NULL,
  `LICPU2` varchar(2) COLLATE latin1_general_cs NOT NULL,
  `LICMP1` double NOT NULL,
  `LICMP2` double NOT NULL,
  `LICPAS` varchar(2) COLLATE latin1_general_cs NOT NULL,
  `LICCDE` varchar(40) COLLATE latin1_general_cs NOT NULL,
  `LICTEL` varchar(20) COLLATE latin1_general_cs NOT NULL,
  `LICSIP` varchar(6) COLLATE latin1_general_cs NOT NULL,
  `LICCAT` varchar(20) COLLATE latin1_general_cs NOT NULL,
  `LICDVG` text COLLATE latin1_general_cs NOT NULL COMMENT 'descrizione variazione generica',
  `LICDVA` varchar(8) COLLATE latin1_general_cs NOT NULL COMMENT 'data variazione',
  `LICPRE` varchar(150) COLLATE latin1_general_cs NOT NULL COMMENT 'Presso',
  `LICTIL` varchar(50) COLLATE latin1_general_cs NOT NULL COMMENT 'Inail impresa',
  `LICTPS` varchar(50) COLLATE latin1_general_cs NOT NULL COMMENT 'inps impresa',
  `LICRIL` varchar(50) COLLATE latin1_general_cs NOT NULL COMMENT 'inal legale',
  `LICRPS` varchar(50) COLLATE latin1_general_cs NOT NULL COMMENT 'inps legale',
  `LICTMA` varchar(50) COLLATE latin1_general_cs NOT NULL COMMENT 'Matricola impresa',
  `LICRMA` varchar(50) COLLATE latin1_general_cs NOT NULL COMMENT 'Matricola legale',
  `LICBOL` text COLLATE latin1_general_cs NOT NULL COMMENT 'Numero marca da bollo',
  `LICDBO` varchar(10) COLLATE latin1_general_cs NOT NULL COMMENT 'Data bollo',
  `LICSAB` smallint(6) NOT NULL COMMENT 'Flag SUB',
  `LICCCO` smallint(6) NOT NULL COMMENT 'Check Cento Commerciale',
  `IDDITTELIC` int(11) NOT NULL,
  `LICAPU` double NOT NULL COMMENT 'Flag per Aree Pubbliche',
  `LICAPR` double NOT NULL COMMENT 'Flag per Aree Private',
  PRIMARY KEY (`ROWID`),
  UNIQUE KEY `K_MASTER` (`LICPRO`),
  KEY `I_LICAUT` (`LICAUT`),
  KEY `I_LICTIP` (`LICTIP`),
  KEY `I_IIKEY` (`LICTIP`,`LICAUT`),
  KEY `I_LICCOF` (`LICCOF`),
  KEY `I_LICCOG` (`LICCOG`),
  KEY `I_LICDIA` (`LICDIA`),
  KEY `I_LICSIN` (`LICSIN`),
  KEY `I_LICAT8` (`LICAT8`),
  KEY `I_LICEIN` (`LICEIN`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `COMMGAFIERE` (
  `ROWID` int(11) NOT NULL AUTO_INCREMENT,
  `LICPRO` varchar(10) COLLATE latin1_general_cs NOT NULL,
  `CODICEDITTA` double NOT NULL,
  `TIPOAUTORIZZAZIONE` varchar(3) COLLATE latin1_general_cs NOT NULL,
  `NUMERO` varchar(20) COLLATE latin1_general_cs NOT NULL,
  PRIMARY KEY (`ROWID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `COMNOT` (
  `ROWID` int(11) NOT NULL AUTO_INCREMENT,
  `NOTPRO` varchar(10) COLLATE latin1_general_cs NOT NULL,
  `NOTMEM` text COLLATE latin1_general_cs NOT NULL,
  PRIMARY KEY (`ROWID`),
  UNIQUE KEY `K_MASTER` (`NOTPRO`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `COMPAR` (
  `ROWID` int(11) NOT NULL AUTO_INCREMENT,
  `COMKEY` varchar(20) COLLATE latin1_general_cs NOT NULL,
  `COMPAR` varchar(60) COLLATE latin1_general_cs NOT NULL,
  `COMDES` text COLLATE latin1_general_cs NOT NULL,
  PRIMARY KEY (`ROWID`),
  UNIQUE KEY `K_MASTER` (`COMKEY`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `COMPRK` (
  `ROWID` int(11) NOT NULL AUTO_INCREMENT,
  `PRKPRO` varchar(10) COLLATE latin1_general_cs NOT NULL,
  `PRKUBI` varchar(150) COLLATE latin1_general_cs NOT NULL,
  `PRKLOC` varchar(150) COLLATE latin1_general_cs NOT NULL,
  `PRKMER` varchar(150) COLLATE latin1_general_cs NOT NULL,
  `PRKTIP` varchar(2) COLLATE latin1_general_cs NOT NULL,
  `PRKALM` varchar(50) COLLATE latin1_general_cs NOT NULL,
  `PRKGIO__1` varchar(2) COLLATE latin1_general_cs NOT NULL,
  `PRKGIO__2` varchar(2) COLLATE latin1_general_cs NOT NULL,
  `PRKGIO__3` varchar(2) COLLATE latin1_general_cs NOT NULL,
  `PRKGIO__4` varchar(2) COLLATE latin1_general_cs NOT NULL,
  `PRKGIO__5` varchar(2) COLLATE latin1_general_cs NOT NULL,
  `PRKGIO__6` varchar(2) COLLATE latin1_general_cs NOT NULL,
  `PRKGIO__7` varchar(2) COLLATE latin1_general_cs NOT NULL,
  `PRKPOS__1` varchar(30) COLLATE latin1_general_cs NOT NULL,
  `PRKPOS__2` varchar(30) COLLATE latin1_general_cs NOT NULL,
  `PRKPOS__3` varchar(30) COLLATE latin1_general_cs NOT NULL,
  `PRKPOS__4` varchar(30) COLLATE latin1_general_cs NOT NULL,
  `PRKPOS__5` varchar(30) COLLATE latin1_general_cs NOT NULL,
  `PRKPOS__6` varchar(30) COLLATE latin1_general_cs NOT NULL,
  `PRKPOS__7` varchar(30) COLLATE latin1_general_cs NOT NULL,
  `PRKSUP__1` varchar(30) COLLATE latin1_general_cs NOT NULL,
  `PRKSUP__2` varchar(30) COLLATE latin1_general_cs NOT NULL,
  `PRKSUP__3` varchar(30) COLLATE latin1_general_cs NOT NULL,
  `PRKSUP__4` varchar(30) COLLATE latin1_general_cs NOT NULL,
  `PRKSUP__5` varchar(30) COLLATE latin1_general_cs NOT NULL,
  `PRKSUP__6` varchar(30) COLLATE latin1_general_cs NOT NULL,
  `PRKSUP__7` varchar(30) COLLATE latin1_general_cs NOT NULL,
  PRIMARY KEY (`ROWID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `COMPRO` (
  `ROWID` int(11) NOT NULL AUTO_INCREMENT,
  `PROCOD` varchar(10) COLLATE latin1_general_cs NOT NULL,
  `PRODOC` text COLLATE latin1_general_cs NOT NULL,
  `PROINV` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `PRORIC` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `PROESI` varchar(2) COLLATE latin1_general_cs NOT NULL,
  `PRONPR` varchar(20) COLLATE latin1_general_cs NOT NULL,
  `PROPDT` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `PRONOT` varchar(60) COLLATE latin1_general_cs NOT NULL,
  `PROSEQ` double NOT NULL,
  `PROSOC` double NOT NULL,
  `PRORNP` varchar(20) COLLATE latin1_general_cs NOT NULL,
  `PRORDP` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `PROPRA` varchar(20) COLLATE latin1_general_cs NOT NULL,
  `PROPRR` varchar(20) COLLATE latin1_general_cs NOT NULL,
  `PRONAME` text COLLATE latin1_general_cs NOT NULL COMMENT 'Nome del file nella cartella del procediemnto',
  `PROPAK` varchar(30) COLLATE latin1_general_cs NOT NULL COMMENT 'Chiave del passo Suap',
  `PROADATA` varchar(8) COLLATE latin1_general_cs NOT NULL COMMENT 'Alla Data',
  `PROOGGETTO` text COLLATE latin1_general_cs NOT NULL COMMENT 'Oggetto Evento',
  `PRODADATA` varchar(8) COLLATE latin1_general_cs NOT NULL COMMENT 'Dalla Data',
  `PROEVENTO` varchar(6) COLLATE latin1_general_cs NOT NULL COMMENT 'Codice Evento',
  PRIMARY KEY (`ROWID`),
  KEY `I_PROSEQ` (`PROCOD`,`PROSEQ`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `COMPRP` (
  `ROWID` int(11) NOT NULL AUTO_INCREMENT,
  `PRPLIC` varchar(24) COLLATE latin1_general_cs NOT NULL,
  `PRPCOG` varchar(72) COLLATE latin1_general_cs NOT NULL,
  `PRPNOM` varchar(72) COLLATE latin1_general_cs NOT NULL,
  `PRPPRO` varchar(4) COLLATE latin1_general_cs NOT NULL,
  `PRPRES` varchar(40) COLLATE latin1_general_cs NOT NULL,
  `PRPIND` varchar(100) COLLATE latin1_general_cs NOT NULL,
  `PRPCAP` varchar(6) COLLATE latin1_general_cs NOT NULL,
  `PRPCOF` varchar(16) COLLATE latin1_general_cs NOT NULL,
  `PRPSEQ` double NOT NULL,
  `PRPSOC` varchar(40) COLLATE latin1_general_cs NOT NULL,
  `PRPDNA` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `PRPCIT` varchar(30) COLLATE latin1_general_cs NOT NULL,
  `PRPSEX` varchar(2) COLLATE latin1_general_cs NOT NULL,
  `PRPSTA` varchar(40) COLLATE latin1_general_cs NOT NULL,
  `PRPPRN` varchar(4) COLLATE latin1_general_cs NOT NULL,
  `PRPCMN` varchar(40) COLLATE latin1_general_cs NOT NULL,
  `PRPRCS` varchar(20) COLLATE latin1_general_cs NOT NULL,
  `PRPRCN` varchar(10) COLLATE latin1_general_cs NOT NULL,
  `PRPRCD` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `PRPRIS` varchar(30) COLLATE latin1_general_cs NOT NULL,
  `PRPRID` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `PRPRIF` varchar(30) COLLATE latin1_general_cs NOT NULL,
  `PRPRFC` varchar(20) COLLATE latin1_general_cs NOT NULL,
  `PRPRSE` varchar(20) COLLATE latin1_general_cs NOT NULL,
  `PRPRFA` varchar(4) COLLATE latin1_general_cs NOT NULL,
  `PRPESM` varchar(2) COLLATE latin1_general_cs NOT NULL,
  `PRPESG` varchar(2) COLLATE latin1_general_cs NOT NULL,
  `PRPESI` varchar(30) COLLATE latin1_general_cs NOT NULL,
  `PRPOCS` varchar(20) COLLATE latin1_general_cs NOT NULL,
  `PRPSES` varchar(20) COLLATE latin1_general_cs NOT NULL,
  `PRPCPA` varchar(4) COLLATE latin1_general_cs NOT NULL,
  `PRPCIV` varchar(6) COLLATE latin1_general_cs NOT NULL,
  PRIMARY KEY (`ROWID`),
  KEY `I_PRPCOF` (`PRPCOF`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `COMREGIMP` (
  `ROWID` int(11) NOT NULL AUTO_INCREMENT,
  `DATAREGISTRO` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `PROGRESSIVO` int(11) NOT NULL,
  `PROVINCIA` varchar(4) COLLATE latin1_general_cs NOT NULL,
  `NUMREGIMPRESA` varchar(20) COLLATE latin1_general_cs NOT NULL,
  `NUMEROREA` varchar(10) COLLATE latin1_general_cs NOT NULL,
  `ULSEDE` varchar(10) COLLATE latin1_general_cs NOT NULL,
  `NUMALBOANNO` varchar(10) COLLATE latin1_general_cs NOT NULL,
  `SEZREGIMP` varchar(10) COLLATE latin1_general_cs NOT NULL,
  `NG` varchar(10) COLLATE latin1_general_cs NOT NULL,
  `DATAISCRRI` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `DATAISCRRD` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `DATAISCRAA` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `DATAAPERUL` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `DATACESSAZIONE` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `DATAINIZIOATTIVITA` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `DATACESSIONEATTIVITA` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `DATAFALLIMENTO` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `DATALIQUIDAZIONE` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `DENOMINAZIONE` text COLLATE latin1_general_cs NOT NULL,
  `INDIRIZZO` varchar(100) COLLATE latin1_general_cs NOT NULL,
  `STRADA` varchar(100) COLLATE latin1_general_cs NOT NULL,
  `CAP` varchar(5) COLLATE latin1_general_cs NOT NULL,
  `COMUNE` varchar(60) COLLATE latin1_general_cs NOT NULL,
  `FRAZIONE` varchar(60) COLLATE latin1_general_cs NOT NULL,
  `ALTREINDICAZIONI` varchar(60) COLLATE latin1_general_cs NOT NULL,
  `ANNOADD` varchar(4) COLLATE latin1_general_cs NOT NULL,
  `IND` int(11) NOT NULL,
  `DIPENDENTI` int(11) NOT NULL,
  `CODICEFISCALE` varchar(16) COLLATE latin1_general_cs NOT NULL,
  `PARTITAIVA` varchar(11) COLLATE latin1_general_cs NOT NULL,
  `TELEFONO` varchar(20) COLLATE latin1_general_cs NOT NULL,
  `CAPITALE` double NOT NULL,
  `ATTIVITA` text COLLATE latin1_general_cs NOT NULL,
  `CODICIATTIVITA` text COLLATE latin1_general_cs NOT NULL,
  `VALUTACAPITALE` varchar(20) COLLATE latin1_general_cs NOT NULL,
  `INDIRIZZOPEC` text COLLATE latin1_general_cs NOT NULL,
  PRIMARY KEY (`ROWID`),
  KEY `I_CODICEFISCALE` (`CODICEFISCALE`),
  KEY `I_PARTITAIVA` (`PARTITAIVA`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs COMMENT='REGISTRO DELLE IMPRESE';
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `COMSAP` (
  `ROWID` int(11) NOT NULL AUTO_INCREMENT,
  `SAPTIP` varchar(4) COLLATE latin1_general_cs NOT NULL,
  `SAPSEZ` varchar(4) COLLATE latin1_general_cs NOT NULL,
  `SAPPDF` varchar(40) COLLATE latin1_general_cs NOT NULL,
  `SAPCOM` varchar(10) COLLATE latin1_general_cs NOT NULL,
  `SAPAXM` varchar(50) COLLATE latin1_general_cs NOT NULL,
  `SAPCXM` varchar(50) COLLATE latin1_general_cs NOT NULL,
  `SAPCLA` varchar(10) COLLATE latin1_general_cs NOT NULL,
  `SAPFIA__1` varchar(40) COLLATE latin1_general_cs NOT NULL,
  `SAPFIA__2` varchar(40) COLLATE latin1_general_cs NOT NULL,
  `SAPFIA__3` varchar(40) COLLATE latin1_general_cs NOT NULL,
  `SAPFIA__4` varchar(40) COLLATE latin1_general_cs NOT NULL,
  `SAPFIL__1` double NOT NULL,
  `SAPFIL__2` double NOT NULL,
  `SAPFIL__3` double NOT NULL,
  `SAPFIL__4` double NOT NULL,
  PRIMARY KEY (`ROWID`),
  KEY `I_SAPPDF` (`SAPTIP`,`SAPSEZ`,`SAPPDF`),
  KEY `I_SAPCOM` (`SAPTIP`,`SAPSEZ`,`SAPCOM`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `COMSOC` (
  `ROWID` int(11) NOT NULL AUTO_INCREMENT,
  `SOCLIC` varchar(24) COLLATE latin1_general_cs NOT NULL,
  `SOCCOG` varchar(72) COLLATE latin1_general_cs NOT NULL,
  `SOCNOM` varchar(72) COLLATE latin1_general_cs NOT NULL,
  `SOCPRO` varchar(4) COLLATE latin1_general_cs NOT NULL,
  `SOCRES` varchar(40) COLLATE latin1_general_cs NOT NULL,
  `SOCIND` varchar(100) COLLATE latin1_general_cs NOT NULL,
  `SOCCAP` varchar(6) COLLATE latin1_general_cs NOT NULL,
  `SOCCOF` varchar(16) COLLATE latin1_general_cs NOT NULL,
  `SOCSEQ` double NOT NULL,
  `SOCLEG` varchar(2) COLLATE latin1_general_cs NOT NULL,
  `SOCCAR` varchar(20) COLLATE latin1_general_cs NOT NULL,
  `SOCCIV` varchar(6) COLLATE latin1_general_cs NOT NULL,
  `SOCDNA` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `SOCCIT` varchar(20) COLLATE latin1_general_cs NOT NULL,
  `SOCSEX` varchar(2) COLLATE latin1_general_cs NOT NULL,
  `SOCSTA` varchar(20) COLLATE latin1_general_cs NOT NULL,
  `SOCPRN` varchar(4) COLLATE latin1_general_cs NOT NULL,
  `SOCCMN` varchar(40) COLLATE latin1_general_cs NOT NULL,
  `SOCIMP` text COLLATE latin1_general_cs NOT NULL COMMENT 'TIIPO IMPORTAZIONE',
  PRIMARY KEY (`ROWID`),
  KEY `I_SOCSEQ` (`SOCLIC`,`SOCSEQ`),
  KEY `I_SOCCOF` (`SOCCOF`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `COMSUA` (
  `ROWID` int(11) NOT NULL AUTO_INCREMENT,
  `SUAPRO` varchar(10) COLLATE latin1_general_cs NOT NULL COMMENT 'N. procediemnto commercio',
  `SUAPRA` varchar(10) COLLATE latin1_general_cs NOT NULL COMMENT 'N. pratica suap',
  `SUAANN` varchar(4) COLLATE latin1_general_cs NOT NULL COMMENT 'Anno pratica Suap',
  `SUAKEY` varchar(30) COLLATE latin1_general_cs NOT NULL COMMENT 'Seq. passo pratica Suap',
  `SUAPRC` varchar(6) COLLATE latin1_general_cs NOT NULL COMMENT 'N. procedimento in anagrafica',
  PRIMARY KEY (`ROWID`),
  KEY `I_SUAPRA` (`SUAANN`,`SUAPRA`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `COMTIM` (
  `ROWID` int(11) NOT NULL AUTO_INCREMENT,
  `TIMPRO` varchar(10) COLLATE latin1_general_cs NOT NULL,
  `TIMGIO` varchar(2) COLLATE latin1_general_cs NOT NULL,
  `TIMCMA` varchar(2) COLLATE latin1_general_cs NOT NULL,
  `TIMCPO` varchar(2) COLLATE latin1_general_cs NOT NULL,
  `TIMOAM` varchar(6) COLLATE latin1_general_cs NOT NULL,
  `TIMOCM` varchar(6) COLLATE latin1_general_cs NOT NULL,
  `TIMOAP` varchar(6) COLLATE latin1_general_cs NOT NULL,
  `TIMOCP` varchar(6) COLLATE latin1_general_cs NOT NULL,
  `TIMPER` varchar(20) COLLATE latin1_general_cs NOT NULL,
  PRIMARY KEY (`ROWID`),
  KEY `I_TIMGIO` (`TIMPRO`,`TIMGIO`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `COMTIPOLICSPEC` (
  `ROWID` int(11) NOT NULL AUTO_INCREMENT,
  `TPLID` int(11) NOT NULL COMMENT 'CODICE TIPO',
  `TPLIDCLASSLIC` int(11) NOT NULL COMMENT 'CODICE CLASSIFICAZIONE',
  `TPLCODLIC` int(11) NOT NULL,
  `TPLDESCLIC` varchar(255) COLLATE latin1_general_cs NOT NULL COMMENT 'DESCRIZIONE',
  `TPLTIPO` varchar(80) COLLATE latin1_general_cs NOT NULL COMMENT 'TIPO LEGISLAZIONE',
  `TPLPERTEMP` varchar(10) COLLATE latin1_general_cs NOT NULL COMMENT 'PREMANENTE / TEMPORANEA',
  `TPLVIGENTE` varchar(2) COLLATE latin1_general_cs NOT NULL COMMENT 'S/N',
  `TPLNOTE` text COLLATE latin1_general_cs NOT NULL COMMENT 'NOTE',
  `TPLDESCBREVE` varchar(20) COLLATE latin1_general_cs NOT NULL COMMENT 'DESCRIZIONE BREVE',
  `TPLEDILIZIA` varchar(2) COLLATE latin1_general_cs NOT NULL COMMENT 'S/N',
  `TPLTIPAUT` varchar(4) COLLATE latin1_general_cs NOT NULL COMMENT 'CODICE TIPOLOGIA AUTORIZZAZIONE',
  PRIMARY KEY (`ROWID`),
  UNIQUE KEY `TPLID` (`TPLID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `COMTIPORIC` (
  `ROWID` int(11) NOT NULL AUTO_INCREMENT,
  `TPRID` int(11) NOT NULL COMMENT 'IDENTIFICATIVO TIPO RICHIESTA VARIAZIONE',
  `TPRCODICE` int(11) NOT NULL COMMENT 'CODICE',
  `TPRIDCLASSRIC` int(11) NOT NULL COMMENT 'ID CLASSIFICAZIONE TIPO RICHEIESTA',
  `TPRDESCVARIAZIONE` text COLLATE latin1_general_cs NOT NULL COMMENT 'DESCRIZIONE',
  `TPRIDLGSTATCLASSRIC` int(11) NOT NULL,
  `TPRCODEVENTO` varchar(20) COLLATE latin1_general_cs NOT NULL COMMENT 'CODICE EVENTO',
  PRIMARY KEY (`ROWID`),
  UNIQUE KEY `TPRID` (`TPRID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `COMTIT` (
  `ROWID` int(11) NOT NULL AUTO_INCREMENT,
  `TITCOD` double NOT NULL,
  `TITCOG` varchar(72) COLLATE latin1_general_cs NOT NULL,
  `TITNOM` varchar(72) COLLATE latin1_general_cs NOT NULL,
  `TITCOF` varchar(16) COLLATE latin1_general_cs NOT NULL,
  `TITDNA` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `TITCIT` varchar(20) COLLATE latin1_general_cs NOT NULL,
  `TITSEX` varchar(2) COLLATE latin1_general_cs NOT NULL,
  `TITSTN` varchar(20) COLLATE latin1_general_cs NOT NULL,
  `TITPRN` varchar(4) COLLATE latin1_general_cs NOT NULL,
  `TITCON` varchar(40) COLLATE latin1_general_cs NOT NULL,
  `TITPRR` varchar(4) COLLATE latin1_general_cs NOT NULL,
  `TITCOR` varchar(40) COLLATE latin1_general_cs NOT NULL,
  `TITIND` varchar(100) COLLATE latin1_general_cs NOT NULL,
  `TITCAP` varchar(6) COLLATE latin1_general_cs NOT NULL,
  `TITCIV` varchar(6) COLLATE latin1_general_cs NOT NULL,
  `TITDOC` varchar(40) COLLATE latin1_general_cs NOT NULL,
  `TITDNU` varchar(30) COLLATE latin1_general_cs NOT NULL,
  `TITRIL` varchar(40) COLLATE latin1_general_cs NOT NULL,
  `TITDRI` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `TITFAX` varchar(40) COLLATE latin1_general_cs NOT NULL,
  `TITMAI` varchar(60) COLLATE latin1_general_cs NOT NULL,
  `TITSIT` varchar(80) COLLATE latin1_general_cs NOT NULL,
  `TITNOT` text COLLATE latin1_general_cs NOT NULL,
  `TITATT` text COLLATE latin1_general_cs NOT NULL,
  `TITATD` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `TITTEL` varchar(40) COLLATE latin1_general_cs NOT NULL,
  PRIMARY KEY (`ROWID`),
  UNIQUE KEY `I_TITCOD` (`TITCOD`),
  KEY `I_TITCOG` (`TITCOG`,`TITNOM`),
  KEY `I_TITCOF` (`TITCOF`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `COMVAR` (
  `ROWID` int(11) NOT NULL AUTO_INCREMENT,
  `VARLIC` varchar(24) COLLATE latin1_general_cs NOT NULL,
  `VARSEZ` varchar(4) COLLATE latin1_general_cs NOT NULL,
  `VARDAT` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `VARDES` varchar(60) COLLATE latin1_general_cs NOT NULL,
  `VARIND` varchar(100) COLLATE latin1_general_cs NOT NULL,
  `VARCIV` varchar(14) COLLATE latin1_general_cs NOT NULL,
  `VARALI` varchar(2) COLLATE latin1_general_cs NOT NULL,
  `VARALM` double NOT NULL,
  `VARARI` varchar(2) COLLATE latin1_general_cs NOT NULL,
  `VARNAL` varchar(2) COLLATE latin1_general_cs NOT NULL,
  `VARNAM` double NOT NULL,
  `VARNAR` varchar(2) COLLATE latin1_general_cs NOT NULL,
  `VARMON` varchar(2) COLLATE latin1_general_cs NOT NULL,
  `VARFAR` varchar(2) COLLATE latin1_general_cs NOT NULL,
  `VARCAR` varchar(2) COLLATE latin1_general_cs NOT NULL,
  `VARTBM` double NOT NULL,
  `VARMQC` double NOT NULL,
  `VARTSR` varchar(2) COLLATE latin1_general_cs NOT NULL,
  `VARCES` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `VARBMQ` double NOT NULL,
  `VARTIP` varchar(100) COLLATE latin1_general_cs NOT NULL,
  `VARATT` varchar(40) COLLATE latin1_general_cs NOT NULL,
  `VARDTD` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `VARRAG` varchar(100) COLLATE latin1_general_cs NOT NULL,
  `VARLRO` varchar(20) COLLATE latin1_general_cs NOT NULL,
  `VARLDA` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `VARLRD` varchar(30) COLLATE latin1_general_cs NOT NULL,
  `VARLRA` varchar(30) COLLATE latin1_general_cs NOT NULL,
  `VARLRC` varchar(20) COLLATE latin1_general_cs NOT NULL,
  `VARLNS` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `VARLRR` varchar(20) COLLATE latin1_general_cs NOT NULL,
  `VARLRI` varchar(30) COLLATE latin1_general_cs NOT NULL,
  `VARLRN` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `VARLCF` varchar(16) COLLATE latin1_general_cs NOT NULL,
  `VARPRD` varchar(30) COLLATE latin1_general_cs NOT NULL,
  `VARPRA` varchar(30) COLLATE latin1_general_cs NOT NULL,
  `VARPRC` varchar(20) COLLATE latin1_general_cs NOT NULL,
  `VARPNS` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `VARPRR` varchar(20) COLLATE latin1_general_cs NOT NULL,
  `VARPRI` varchar(30) COLLATE latin1_general_cs NOT NULL,
  `VARPRN` varchar(8) COLLATE latin1_general_cs NOT NULL,
  `VARPCF` varchar(16) COLLATE latin1_general_cs NOT NULL,
  `VARALT__1` varchar(60) COLLATE latin1_general_cs NOT NULL,
  `VARALT__2` varchar(60) COLLATE latin1_general_cs NOT NULL,
  `VARALT__3` varchar(60) COLLATE latin1_general_cs NOT NULL,
  `VARALT__4` varchar(60) COLLATE latin1_general_cs NOT NULL,
  `VARQAA` varchar(2) COLLATE latin1_general_cs NOT NULL,
  `VARQAB` varchar(2) COLLATE latin1_general_cs NOT NULL,
  `VARASS` varchar(2) COLLATE latin1_general_cs NOT NULL,
  `VARSUB` varchar(2) COLLATE latin1_general_cs NOT NULL,
  `VARLDN` varchar(30) COLLATE latin1_general_cs NOT NULL,
  `VARLAN` varchar(30) COLLATE latin1_general_cs NOT NULL,
  `VARPDN` varchar(30) COLLATE latin1_general_cs NOT NULL,
  `VARPAN` varchar(30) COLLATE latin1_general_cs NOT NULL,
  `VARNOT` text COLLATE latin1_general_cs NOT NULL,
  `VARPU1` varchar(2) COLLATE latin1_general_cs NOT NULL,
  `VARMP1` double NOT NULL,
  `VARPU2` varchar(2) COLLATE latin1_general_cs NOT NULL,
  `VARMP2` double NOT NULL,
  `VARAM1` varchar(2) COLLATE latin1_general_cs NOT NULL,
  `VARAM2` varchar(2) COLLATE latin1_general_cs NOT NULL,
  PRIMARY KEY (`ROWID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `PROCTIPAUT` (
  `ROWID` int(11) NOT NULL AUTO_INCREMENT COMMENT 'id univoco',
  `PROCEDIMENTO` varchar(6) COLLATE latin1_general_cs NOT NULL COMMENT 'codice procediemnto',
  `AUTORIZZAZIONE` varchar(4) COLLATE latin1_general_cs NOT NULL COMMENT 'codice autorizzazione',
  `EVENTO` varchar(20) COLLATE latin1_general_cs NOT NULL COMMENT 'evento per tipologia',
  `VALIDODA` varchar(8) COLLATE latin1_general_cs NOT NULL COMMENT 'valido da',
  `VALIDOA` varchar(8) COLLATE latin1_general_cs NOT NULL COMMENT 'valido a',
  `EVENTOPROCID` int(11) NOT NULL COMMENT 'rowid iteevt',
  PRIMARY KEY (`ROWID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
