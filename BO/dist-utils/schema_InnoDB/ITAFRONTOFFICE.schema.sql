/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ENV_CONFIG` (
  `ROWID` int(11) NOT NULL AUTO_INCREMENT,
  `CHIAVE` varchar(50) COLLATE latin1_general_cs NOT NULL,
  `CLASSE` varchar(50) COLLATE latin1_general_cs NOT NULL,
  `CONFIG` text COLLATE latin1_general_cs NOT NULL,
  PRIMARY KEY (`ROWID`),
  UNIQUE KEY `CHIAVE` (`CHIAVE`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
