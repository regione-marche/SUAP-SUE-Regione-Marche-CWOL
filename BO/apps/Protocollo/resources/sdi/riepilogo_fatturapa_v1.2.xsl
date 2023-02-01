<?xml version="1.0" encoding="UTF-8"?>
<!-- versione 1.2.4 -->
<xsl:stylesheet version="2.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:p="http://ivaservizi.agenziaentrate.gov.it/docs/xsd/fatture/v1.2" >
  <xsl:output method="html" />
  <xsl:template name="FormatDate">
    <xsl:param name="DateTime"/>
    <xsl:variable name="year" select="substring($DateTime,1,4)"/>
    <xsl:variable name="month" select="substring($DateTime,6,2)"/>
    <xsl:variable name="day" select="substring($DateTime,9,2)"/>
    <xsl:value-of select="' '"/>
    <xsl:value-of select="$day"/>
    <xsl:value-of select="' '"/>
    <xsl:choose>
      <xsl:when test="$month = '1' or $month = '01'">Gennaio</xsl:when>
      <xsl:when test="$month = '2' or $month = '02'">Febbraio</xsl:when>
      <xsl:when test="$month = '3' or $month = '03'">Marzo</xsl:when>
      <xsl:when test="$month = '4' or $month = '04'">Aprile</xsl:when>
      <xsl:when test="$month = '5' or $month = '05'">Maggio</xsl:when>
      <xsl:when test="$month = '6' or $month = '06'">Giugno</xsl:when>
      <xsl:when test="$month = '7' or $month = '07'">Luglio</xsl:when>
      <xsl:when test="$month = '8' or $month = '08'">Agosto</xsl:when>
      <xsl:when test="$month = '9' or $month = '09'">Settembre</xsl:when>
      <xsl:when test="$month = '10'">Ottobre</xsl:when>
      <xsl:when test="$month = '11'">Novembre</xsl:when>
      <xsl:when test="$month = '12'">Dicembre</xsl:when>
      <xsl:otherwise>Mese non riconosciuto</xsl:otherwise>
    </xsl:choose>
    <xsl:value-of select="' '"/>
    <xsl:value-of select="$year"/>
    <xsl:variable name="time" select="substring($DateTime,12)"/>
    <xsl:if test="$time != ''">
      <xsl:variable name="hh" select="substring($time,1,2)"/>
      <xsl:variable name="mm" select="substring($time,4,2)"/>
      <xsl:variable name="ss" select="substring($time,7,2)"/>
      <xsl:value-of select="' - '"/>
      <xsl:value-of select="$hh"/>
      <xsl:value-of select="':'"/>
      <xsl:value-of select="$mm"/>
      <xsl:value-of select="':'"/>
      <xsl:value-of select="$ss"/>
    </xsl:if>
    <xsl:value-of select="' '"/>
  </xsl:template>
  <xsl:template name="dataItaliana">
    <xsl:param name="data"/>
    <xsl:variable name="anno" select="substring($data,1,4)"/>
    <xsl:variable name="mese" select="substring($data,6,2)"/>
    <xsl:variable name="giorno" select="substring($data,9,2)"/>
    <xsl:if test="$anno != ''">
      <xsl:value-of select="''"/>
      <xsl:value-of select="$giorno"/>
      <xsl:value-of select="'/'"/>
      <xsl:value-of select="$mese"/>
      <xsl:value-of select="'/'"/>
      <xsl:value-of select="$anno"/>
    </xsl:if>
  </xsl:template>
  <xsl:template name="nomeAllegatoConFormato">
    <xsl:param name="nomeFile"/>
    <xsl:param name="formatoFile"/>
    <xsl:variable name="lenFormato">
      <xsl:choose>
        <xsl:when test="substring($formatoFile, 1, 1) = '.'">
          <xsl:value-of select="string-length($formatoFile)-1"/>
        </xsl:when>
        <xsl:otherwise>
          <xsl:value-of select="string-length($formatoFile)"/>
        </xsl:otherwise>
      </xsl:choose>
    </xsl:variable>
    <xsl:variable name="lenNome" select="string-length($nomeFile) -$lenFormato"/>
    <xsl:variable name="nome" select="substring($nomeFile,$lenNome,1)"/>
    <xsl:choose>
	  <xsl:when test="(substring-after($nomeFile, '.') != $formatoFile) and ($lenFormato &gt; 0)">
        <xsl:value-of select="$nomeFile"/>
        <xsl:value-of select="'.'"/>
        <xsl:value-of select="$formatoFile"/>
      </xsl:when>
      <xsl:when test="($nome != '.') and ($lenFormato &gt; 0) and (substring($formatoFile, 1, 1) != '.')">
        <xsl:value-of select="$nomeFile"/>
        <xsl:value-of select="'.'"/>
        <xsl:value-of select="$formatoFile"/>
      </xsl:when>
      <xsl:when test="($nome != '.') and ($lenFormato &gt; 0) and (substring($formatoFile, 1, 1) = '.')">
        <xsl:value-of select="$nomeFile"/>
        <xsl:value-of select="$formatoFile"/>
      </xsl:when>
      <xsl:otherwise>
        <xsl:value-of select="$nomeFile"/>
      </xsl:otherwise>
    </xsl:choose>
  </xsl:template>
  <xsl:template name="tipoMime">
    <xsl:param name="formato"/>
    <xsl:choose>
      <!-- MIME Type plain -->
      <xsl:when test="$formato = 'txt'">
        <xsl:value-of select="'text'"/>
      </xsl:when>
      <xsl:when test="$formato = '.txt'">
        <xsl:value-of select="'text'"/>
      </xsl:when>
      <xsl:when test="$formato = 'TXT'">
        <xsl:value-of select="'text'"/>
      </xsl:when>
      <xsl:when test="$formato = '.TXT'">
        <xsl:value-of select="'text'"/>
      </xsl:when>
      <!-- MIME Type Image -->
      <xsl:when test="$formato = 'jpeg'">
        <xsl:value-of select="'image'"/>
      </xsl:when>
      <xsl:when test="$formato = '.jpeg'">
        <xsl:value-of select="'image'"/>
      </xsl:when>
      <xsl:when test="$formato = 'JPEG'">
        <xsl:value-of select="'image'"/>
      </xsl:when>
      <xsl:when test="$formato = '.JPEG'">
        <xsl:value-of select="'image'"/>
      </xsl:when>
      <xsl:when test="$formato = 'jpg'">
        <xsl:value-of select="'image'"/>
      </xsl:when>
      <xsl:when test="$formato = '.jpg'">
        <xsl:value-of select="'image'"/>
      </xsl:when>
      <xsl:when test="$formato = 'gif'">
        <xsl:value-of select="'image'"/>
      </xsl:when>
      <xsl:when test="$formato = '.gif'">
        <xsl:value-of select="'image'"/>
      </xsl:when>
      <xsl:when test="$formato = 'GIF'">
        <xsl:value-of select="'image'"/>
      </xsl:when>
      <xsl:when test="$formato = '.GIF'">
        <xsl:value-of select="'image'"/>
      </xsl:when>
      <xsl:when test="$formato = 'png'">
        <xsl:value-of select="'image'"/>
      </xsl:when>
      <xsl:when test="$formato = '.png'">
        <xsl:value-of select="'image'"/>
      </xsl:when>
      <xsl:when test="$formato = 'PNG'">
        <xsl:value-of select="'image'"/>
      </xsl:when>
      <xsl:when test="$formato = '.PNG'">
        <xsl:value-of select="'image'"/>
      </xsl:when>
      <xsl:otherwise>
        <xsl:value-of select="'application'"/>
      </xsl:otherwise>
    </xsl:choose>
  </xsl:template>
  <xsl:template name="formatoAllegato">
    <xsl:param name="nomeFile"/>
    <xsl:param name="formatoFile"/>
	<xsl:param name="compressione"/>
    <xsl:variable name="formatoFormattato">
      <xsl:choose>
        <xsl:when test="substring($formatoFile, 1, 1) = '.'">
          <xsl:value-of select="substring($formatoFile, 2, string-length($formatoFile)-1)"/>
        </xsl:when>
        <xsl:otherwise>
          <xsl:value-of select="substring($formatoFile, 1, string-length($formatoFile))"/>
        </xsl:otherwise>
      </xsl:choose>
    </xsl:variable>
	<xsl:variable name="compressioneFormattata">
      <xsl:choose>
        <xsl:when test="substring($compressione, 1, 1) = '.'">
          <xsl:value-of select="substring($compressione, 2, string-length($compressione)-1)"/>
        </xsl:when>
        <xsl:otherwise>
          <xsl:value-of select="substring($compressione, 1, string-length($compressione))"/>
        </xsl:otherwise>
      </xsl:choose>
    </xsl:variable>
    <xsl:choose>
      <xsl:when test="string-length($compressioneFormattata) &gt; 0">
        <xsl:value-of select="$compressioneFormattata"/>
      </xsl:when>
	  <xsl:when test="string-length($formatoFormattato) &gt; 0">
        <xsl:value-of select="$formatoFormattato"/>
      </xsl:when>
      <xsl:otherwise>
        <xsl:value-of select="substring-after($nomeFile, '.')"/>
      </xsl:otherwise>
    </xsl:choose>
  </xsl:template>
  <xsl:decimal-format name="euro" decimal-separator="," NaN="" grouping-separator="." />
  <xsl:template match="/">
    <html>
      <head>
        <meta http-equiv="X-UA-Compatible" content="IE=edge"/>
        <title>Fattura elettronica (ver. <xsl:value-of select="p:FatturaElettronica/@versione" />) - Visualizzazione Dati</title>
        <style type="text/css">
			@media screen {
				body {
					margin: auto;
					display: block;
					text-align: center;
					max-width: 1280px;
					min-width: 930px;
					font-family: sans-serif;
					color: #000000;
					background-color: #ffffff;
				}
			}
			@page {
				size: A4;
				margin-bottom: 5pt;
			}
			@media print {
				html, body {
					color: #000000;
					font-family: sans-serif;
					font-size: 12pt;
					border: none;
					width: 210mm;
					height: 297mm;
					text-align: center;
					margin: 0;
					top: 0;
					left: 0;
				}
			}
			@media screen {
				div.footer {
					font-size: small;
					text-align: center;
					padding: 10px;
				}
			}
			@media print {
				div.footer {
					position: fixed;
					bottom: 0;
					left: 0;
					width: 100%;
					text-align: center;
					font-size: 8pt;
				}
			}

			@media print {
				div.aCapo { page-break-before: always; }
			}

			@media screen {
				div.watermark {
					display: none;
				}
			}
			@media print {
				img.watermarkStyle {
					width: 210mm;
					height: 297mm;
					position: fixed;
					top: 0;
					left: 0;
					text-align: center;
					opacity: 0.1;
					z-index: -1;
				  /* Firefox, Chrome, Safari, Opera, IE 9 (preview) */
					filter: alpha(opacity=10);
				}
			}
			@media screen {
				#fattura-container {
					margin: 20px;
					background-color: #f1f1f1;
					background-color: #FFFFFF ;
					border: 1px solid #CCCCCC;
					-webkitbox-shadow: 0 0 10px rgba(0, 0, 0, 0.3);
					-mozbox-shadow: 0 0 10px rgba(0, 0, 0, 0.3);
					box-shadow: 0 0 10px rgba(0, 0, 0, 0.3);
				}
			}
			@media print {
				#fattura-container {
					clear: both;
					margin: auto;
					background: none;
					border: none;
				}
			}
			@media screen {
				div.srBox {
					width: 45%;
					text-align: justify;
					font-size: 14px;
					padding-left: 10px;
					padding-right: 10px;
					margin-left: 10px;
					margin-right: 10px;
					margin-bottom: 10px;
				}
			}
			@media print {
				div.srBox {
					width: 100mm;
					text-align: justify;
					font-size: 10pt;
					padding-right: 3pt;
					margin-left: 5pt;
					margin-right: 5pt;
					margin-bottom: 5pt;
				}
			}
			div.mittente {
				float: left;
			}
			div.cessionario {
				float: right;
			}
			.titolo {
				text-align: left;
				margin-left: 20px;
				font-variant: small-caps;
			}
			@media screen {
				h1.titolo {
					color: #2E3333;
					margin-left: 0px;
				}
			}
			@media print {
				h1.titolo {
					color: #2E3333;
					margin-left: 0;
					font-size: 15pt;
				}
			}
			div.titolo {
				float: left;
				text-align: left;
				margin-left: 20px;
				font-variant: small-caps;
			}
			@media screen {
				div.separa {
					clear: both;
					font-size: 5px;
				}
			}

			@media screen {
				h2 span {
					margin: 0px;
					padding: 0px;
				}
			}

			@media print {
				div.separa {
					clear: both;
					font-size: 5pt;
					margin: 3pt;
					}
			}
			ul {
				margin-top: 0;
				margin-bottom: 0;
				padding: 0;
				list-style: none;
				display: inline;
			}
			h3 {
				margin: 0;
				padding-bottom: 3px;
			}
			h4 {
				margin: 0;
				padding: 0;
			}
			.titoloSx {
				text-align: left;
				margin-left: 20px;
			}

			@media print {
				h4.titoloSx {
					margin-left: 5pt;
				}
			}

			.titoloDx {
				text-align: right;
				margin-right: 20px;
			}
			span.titoloSx {
				float: left;
				margin-left: 20px;
			}
			span.titoloDx {
				float: right;
				margin-right: 20px;
			}
			li.titoloDx {
				float: right;
			}
			li.titoloSx {
				float: left;
			}
			@media screen {
				caption {
					display: table-caption;
					text-align: left;
					font-size: 14px;
					font-weight: bold;
					color: #6e6e6e;
				}
			}
			@media print {
				caption {
					display: table-caption;
					text-align: left;
					font-size: 14px;
					font-weight: bold;
					color: #6e6e6e;
				}
			}
			li {
				text-align: left;
			}
			@media screen {
				div.dettagli {
					margin: auto;
					padding-top: 10px;
				}
			}
			@media print {
				div.dettagli {
					margin: auto;
					padding-top: 10pt;
				}
			}
			@media screen {
				#riassuntoLotto {
					border-bottom: 1px solid #CCCCCC;
					border-collapse: collapse;
					font-variant: small-caps;
					font-weight: bold;
					padding: 5px;
				}
			}
			@media print {
				#riassuntoLotto {
					display: none;
				}
			}
			@media screen {
				table.tableDettagli {
					width: 96%;
					table-layout: fixed;
					font-size: 12px;
					border-collapse: collapse;
					margin-right: 20px;
					margin-left: 20px;
					text-align: center;
					word-wrap:break-word;
				}
				table.tableDettagli th {
					padding-left: 5px;
					padding-right: 5px;
					border: ridge 1px #000000;
					font-size: 14px;
					background-color: #e1e1e1;
					
					filter: progid:DXImageTransform.Microsoft.gradient( startColorstr='#f6f8f9', endColorstr='#f5f7f9',GradientType=0 ); /* IE6-9 */

				}
				table.tableDettagli td {
					border: ridge 1px #000000;
					font-size: 12px;
				}
				table tr:last-child th:first-child {

				}
				table tr:last-child th:last-child {

				}
				table tr:last-child td:first-child {

				}
				table tr:last-child td:last-child {

				}
			}
			@media print {
				table.tableDettagli {
					width: 100%;
					table-layout: fixed;
					page-break-inside: auto;
					border-collapse: collapse;
					text-align: center;
					word-wrap:break-word;
				}
				table.tableDettagli th {
					padding-left: 2pt;
					padding-right: 2pt;
					border: solid 1pt #000000;
					font-size: 11pt;
					background-color: #e9e9e9;
				}
				table.tableDettagli tr {
					page-break-inside: avoid;
					page-break-after: auto;
				}
				table.tableDettagli td {
					border: solid 1pt #000000;
					font-size: 10pt;
				}
				table tr:last-child th:first-child {

				}
				table tr:last-child th:last-child {

				}
				table tr:last-child td:first-child {
				}
				table tr:last-child td:last-child {
				}
			}
			td.aSx {
				text-align: left;
			}
			td.aDx {
				text-align: right;
			}
			td.tipo {
				text-align: left;
				background-color: #CCCCCC;
				filter: progid:DXImageTransform.Microsoft.gradient( startColorstr='#f6f8f9', endColorstr='#f5f7f9',GradientType=0 ); /* IE6-9 */
			}
			table.tableDettagli td.senzaBordo {
				border: none;
			}
			span.evidenzia {
				color: red;
			}
			@media screen {
				div.testataFattura {
					border-collapse: collapse;
					padding-bottom: 10px;
					text-align: left;
				}
			}
			@media print {
				div.testataFattura {
					border-collapse: collapse;
					padding-bottom: 10pt;
					text-align: left;
					/*page-break-before: always;*/
				}
			}
			@media screen {
				div.nascondiIntestazione {
					display: none;
				}
			}

			ul.elencoInLine li {
				display: inline;
			}
			@media screen {
				div.elementoLotto {
					border-collapse: collapse;
					padding-top: 10px;
				}
			}
			@media print {
				div.elementoLotto {
					clear: both;
					border-collapse: collapse;
					padding-top: 10pt;
					margin: 0;
					font-size: small;
					/*page-break-after: always;*/
				}
			}
			p.info {
				text-align: left;
				margin: 0;
			}
			@media screen {
				div.intestazioneElemLotto {
					border: 1px solid #2E64FE;
					border-collapse: collapse;
					padding: 5px;
					margin: 0px 15px 0px 15px;
					text-align: center;
					-webkitbox-shadow: 0 0 10px rgba(0, 0, 0, 0.3);
					-mozbox-shadow: 0 0 10px rgba(0, 0, 0, 0.3);
					box-shadow: 0 0 10px rgba(0, 0, 0, 0.3);
				}
				div.intestazioneElemLotto span {
					margin: 0;
				}
				div.intestazioneElemLotto li {
					margin: 0;
				}
				div.intestazioneElemLotto h2 {
					margin: 0;
				}
			}
			@media print {
				div.intestazioneElemLotto {
					border: 1pt solid #2E64FE;
					border-collapse: collapse;
					padding-bottom: 5pt;
					margin: 0;
					text-align: center;
				}
			}
			div.riepilogoTotalePag {
				margin: auto;
				text-align: center;
			}
			ul.riepilogoGeneraleDettaglio li {
				display: inline;
				text-align: justify;
			}
			@media screen {
					div.trasmissioneFattura {
					float: right;
					width: 45%;
					text-align: left;
					font-size: 14px;
					padding: 10px;
					margin: 10px;
					border: 1px solid #2E64FE;
					border-collapse: collapse;
					-webkitbox-shadow: 0 0 10px rgba(0, 0, 0, 0.3);
					-mozbox-shadow: 0 0 10px rgba(0, 0, 0, 0.3);
					box-shadow: 0 0 10px rgba(0, 0, 0, 0.3);
				}
			}
			@media print {
					div.trasmissioneFattura {
					float: right;
					width: 45%;
					text-align: left;
					font-size: 12pt;
					padding: 10pt;
					margin: 10pt;
					border: 1pt solid #2E64FE;
					border-collapse: collapse;
					border-radius: 10pt;
				}
			}
			@media screen {
				div.decora {
					clear: both;
					border-collapse: collapse;
					border: none;
					font-size: 5px;
					padding: 0px;
					margin: 0px;
					background: rgb(242,246,248);
				}
			}
			@media print {
				div.decora {
					display: none;
				}
			}
			@media print {
				table {
					page-break-inside: avoid;
				}
				tr {
					page-break-inside: avoid;
					page-break-after: auto;
				}
			}
			.link:hover {
				background-color: #2E64FE;
				color: #FFFFFF;
				padding-left: 3px;
				padding-right: 3px;
				cursor: pointer; cursor: hand;
			}
			.link {
				color: #2E64FE;
				padding-left: 3px;
				padding-right: 3px;
			}
			.linkAllegato:hover {
				background-color: #2E64FE;
				color: #FFFFFF;
				padding-left: 3px;
				padding-right: 3px;
				cursor: pointer; cursor: hand;
				text-decoration: underline;
			}
			.linkAllegato {
				color: #2E64FE;
				padding-left: 3px;
				padding-right: 3px;
				text-decoration: underline;
			}
			.isIELink {
				display:none;
			}
			span.conSpazio {
				padding-right: 3pt;
			}

			.nascondi { display: none; }
			.mostra { display: inline; }

			@media print {
				div.datiPagamentoCondizioni {
					page-break-inside: avoid;
				}
			}
		</style>
      </head>
      <body>
        <div class="watermark">
        </div>
        <div id="fattura-container">
          <xsl:variable name="TOTALELOTTO">
            <xsl:value-of select="count(p:FatturaElettronica/FatturaElettronicaBody)"/>
          </xsl:variable>
          <!-- Se browser IE con versione 9 o inferiore avviso di aggiornarlo -->
          <!--[if lt IE 10]> <style> @media screen { #oldIE { display:block; border: 1pt solid #FE0000; border-collapse: collapse; padding: 5px; margin: 0px 15px 0px 15px; text-align: center; -webkit-border-radius: 10px; -moz-border-radius: 10px; border-radius: 10px; -webkitbox-shadow: 0 0 10px rgba(0, 0, 0, 0.3); -mozbox-shadow: 0 0 10px rgba(0, 0, 0, 0.3); box-shadow: 0 0 10px rgba(0, 0, 0, 0.3); } } </style> <div id="oldIE"> <span class="evidenzia">Questa versione di Internet Explorer &#232; obsoleta e non consente una corretta visualizzazione del documento.<br /> Si consiglia quindi di aggiornare Internet Explorer o di utilizzare un altro web browser (ad esempio Mozilla Firefox).</span> </div> <![endif]-->
          <xsl:if test="$TOTALELOTTO&gt;1">
            <div id="riassuntoLotto">
              <span>Questo lotto contiene&#32;
                <span class="evidenzia">
                  <xsl:value-of select="$TOTALELOTTO" />
                </span>&#32;documenti</span>
            </div>
          </xsl:if>
          <!-- fine riassuntoLotto -->
          <!-- creo un div fattura per ogni documento all'interno del lotto -->
          <xsl:for-each select="p:FatturaElettronica/FatturaElettronicaBody">
            <xsl:variable name="POSIZIONE">
              <xsl:value-of select="position()"/>
            </xsl:variable>
            <div>
              <xsl:if test="position() &gt; 1">
                <xsl:attribute name="class">testataFattura nascondiIntestazione</xsl:attribute>
              </xsl:if>
              <xsl:if test="position() = 1">
                <xsl:attribute name="class">testataFattura</xsl:attribute>
              </xsl:if>
              <div>
                <xsl:if test="position() &gt; 1">
                  <xsl:attribute name="class">intestazione aCapo</xsl:attribute>
                </xsl:if>
                <xsl:if test="position() = 1">
                  <xsl:attribute name="class">intestazione</xsl:attribute>
                </xsl:if>
                <div class="titolo">
                  <h1 class="titolo">Fattura Elettronica - Versione
                    <xsl:value-of select="../@versione"/>
                  </h1>
                  <xsl:if test="../FatturaElettronicaHeader/SoggettoEmittente">
                    <span>
                      <strong>Soggetto emittente</strong>:
                      <xsl:variable name="SC">
                        <xsl:value-of select="../FatturaElettronicaHeader/SoggettoEmittente"/>
                      </xsl:variable>
                      <xsl:choose>
                        <xsl:when test="$SC='CC'">Cessionario/Committente</xsl:when>
                        <xsl:when test="$SC='TZ'">Terzo</xsl:when>
                        <xsl:when test="$SC=''"></xsl:when>
                        <xsl:otherwise>
                          <span>
                            <xsl:value-of select="../FatturaElettronicaHeader/SoggettoEmittente"/>(codice non previsto)</span>
                        </xsl:otherwise>
                      </xsl:choose>
                    </span>
                  </xsl:if>
                </div>
                <!-- fine TITOLO -->
                <div class="trasmissioneFattura">
                  <xsl:if test="../FatturaElettronicaHeader/DatiTrasmissione">
                    <!--INIZIO DATI DELLA TRASMISSIONE-->
                    <div class="dati-trasmissione">
						<h3>Trasmissione nr.
							<xsl:value-of select="../FatturaElettronicaHeader/DatiTrasmissione/ProgressivoInvio"/>&#32;
							<xsl:variable name="FT">
								<xsl:value-of select="../FatturaElettronicaHeader/DatiTrasmissione/FormatoTrasmissione"/>
							</xsl:variable>
							<xsl:choose>
							<xsl:when test="$FT='FPA12'"> verso PA</xsl:when>
							<xsl:when test="$FT='FPR12'"> verso Privati</xsl:when>
							<xsl:otherwise>
								<span></span>
							</xsl:otherwise>
							</xsl:choose>
						</h3>
                      <p class="info">
                        <xsl:for-each select="../FatturaElettronicaHeader/DatiTrasmissione">
                          <xsl:if test="IdTrasmittente">
                            <span>Da:
                              <span>
                                <strong>
                                  <xsl:value-of select="IdTrasmittente/IdPaese"/>
                                  <xsl:value-of select="IdTrasmittente/IdCodice"/>
                                </strong>
                              </span>&#32;a:
                              <span>
                                <strong>
                                  <xsl:value-of select="CodiceDestinatario"/>
                                </strong>
                              </span>
                            </span>
                            <br />
                          </xsl:if>
                          <xsl:if test="FormatoTrasmissione">
                            <span>Formato:
                              <span>
                                <strong>
                                  <xsl:value-of select="FormatoTrasmissione"/>
                                </strong>
                              </span>
                            </span>
                            <br />
                          </xsl:if>
                          <xsl:if test="ContattiTrasmittente/Telefono">
                            <span>Telefono:
                              <span>
                                <xsl:value-of select="ContattiTrasmittente/Telefono"/>
                              </span>
                            </span>
                            <br />
                          </xsl:if>
                          <xsl:if test="ContattiTrasmittente/Email">
                            <span>E-mail:
                              <span>
                                <a href="mailto:{ContattiTrasmittente/Email}">
                                  <xsl:value-of select="ContattiTrasmittente/Email"/>
                                </a>
                              </span>
                            </span>
                            <br />
                          </xsl:if>
                          <xsl:if test="PECDestinatario">
                            <span>PEC:
                              <span>
                                <a href="mailto:{PECDestinatario}">
                                  <xsl:value-of select="PECDestinatario"/>
                                </a>
                              </span>
                            </span>
                          </xsl:if>
                        </xsl:for-each>
                      </p>
                    </div>
                  </xsl:if>
                  <!--FINE DATI DELLA TRASMISSIONE-->
                </div>
                <!-- fine trasmissioneFattura -->
                <div class="separa">
                  <p>&#32;</p>
                </div>
              </div>
              <!-- fine intestazione -->
              <div class="separa">
                <p>&#32;</p>
              </div>
              <div class="srBox mittente">
                <xsl:if test="../FatturaElettronicaHeader/CedentePrestatore/DatiAnagrafici">
                  <xsl:variable name="MITTENTE">
                    <xsl:value-of select="concat(string(../FatturaElettronicaHeader/CedentePrestatore/DatiAnagrafici/Anagrafica/Nome), ' ', string(../FatturaElettronicaHeader/CedentePrestatore/DatiAnagrafici/Anagrafica/Cognome))" />
                  </xsl:variable>
                  <p class="info">
                    <strong>Mittente:&#32;</strong>
                    <xsl:choose>
                      <xsl:when test="string-length(normalize-space($MITTENTE)) &gt; 0">
                        <xsl:value-of select="normalize-space(concat(string(../FatturaElettronicaHeader/CedentePrestatore/DatiAnagrafici/Anagrafica/Titolo), ' ', $MITTENTE))" />
                      </xsl:when>
                      <xsl:otherwise>
                        <xsl:value-of select="normalize-space(concat(string(../FatturaElettronicaHeader/CedentePrestatore/DatiAnagrafici/Anagrafica/Titolo), ' ', ../FatturaElettronicaHeader/CedentePrestatore/DatiAnagrafici/Anagrafica/Denominazione))"/>
                      </xsl:otherwise>
                    </xsl:choose>
                    <br />
                    <xsl:for-each select="../FatturaElettronicaHeader/CedentePrestatore/DatiAnagrafici">
                      <xsl:if test="IdFiscaleIVA">
                        <span>Partita IVA:&#32;
                          <span>
                            <xsl:value-of select="IdFiscaleIVA/IdPaese"/>
                            <xsl:value-of select="IdFiscaleIVA/IdCodice"/>
                          </span>
                        </span>
                        <br />
                      </xsl:if>
                      <xsl:if test="CodiceFiscale">
                        <span>Codice fiscale:&#32;
                          <span>
                            <xsl:value-of select="CodiceFiscale"/>
                          </span>
                        </span>
                        <br />
                      </xsl:if>
                      <xsl:if test="string-length(normalize-space($MITTENTE)) &gt; 0">
                        <xsl:if test="Anagrafica/Denominazione">
                          <span>Denominazione:&#32;
                            <span>
                              <xsl:value-of select="Anagrafica/Denominazione"/>
                            </span>
                          </span>
                          <br />
                        </xsl:if>
						<xsl:if test="Anagrafica/Nome">
                          <span>Nominativo:&#32;
							<span><xsl:value-of select="normalize-space(concat(string(Anagrafica/Nome), ' ', string(Anagrafica/Cognome)))"/></span>
						  </span>
						  <br />
						</xsl:if>
                      </xsl:if>
                      <xsl:if test="Anagrafica/CodEORI">
                        <span>Codice EORI:&#32;
                          <span>
                            <xsl:value-of select="Anagrafica/CodEORI"/>
                          </span>
                        </span>
                        <br />
                      </xsl:if>
                      <xsl:if test="AlboProfessionale">
                        <span>Albo professionale di appartenenza:&#32;
                          <span>
                            <xsl:value-of select="AlboProfessionale"/>
                          </span>
                        </span>
                        <br />
                      </xsl:if>
                      <xsl:if test="ProvinciaAlbo">
                        <span>Provincia di competenza dell'Albo:&#32;
                          <span>
                            <xsl:value-of select="ProvinciaAlbo"/>
                          </span>
                        </span>
                        <br />
                      </xsl:if>
                      <xsl:if test="NumeroIscrizioneAlbo">
                        <span>Numero iscrizione all'Albo:&#32;
                          <span>
                            <xsl:value-of select="NumeroIscrizioneAlbo"/>
                          </span>
                        </span>
                        <br />
                      </xsl:if>
                      <xsl:if test="DataIscrizioneAlbo">
                        <span>Data iscrizione all'Albo:&#32;
                          <xsl:call-template name="dataItaliana">
                            <xsl:with-param name="data" select="DataIscrizioneAlbo"/>
                          </xsl:call-template>
                        </span>
                        <br />
                      </xsl:if>
                      <xsl:if test="RegimeFiscale">
                        <span>Regime fiscale:&#32;
                          <xsl:variable name="RF">
                            <xsl:value-of select="RegimeFiscale"/>
                          </xsl:variable>
                          <xsl:choose>
                            <xsl:when test="$RF='RF01'">Ordinario</xsl:when>
                            <xsl:when test="$RF='RF02'">Contribuenti minimi</xsl:when>
                            <xsl:when test="$RF='RF03'">Nuove iniziative produttive</xsl:when>
                            <xsl:when test="$RF='RF04'">Agricoltura e attivit&#224; connesse e pesca</xsl:when>
                            <xsl:when test="$RF='RF05'">Vendita sali e tabacchi</xsl:when>
                            <xsl:when test="$RF='RF06'">Commercio fiammiferi</xsl:when>
                            <xsl:when test="$RF='RF07'">Editoria</xsl:when>
                            <xsl:when test="$RF='RF08'">Gestione servizi telefonia pubblica</xsl:when>
                            <xsl:when test="$RF='RF09'">Rivendita documenti di trasporto pubblico e di sosta</xsl:when>
                            <xsl:when test="$RF='RF10'">Intrattenimenti, giochi e altre attivit&#224; di cui alla tariffa allegata al DPR 640/72</xsl:when>
                            <xsl:when test="$RF='RF11'">Agenzie viaggi e turismo</xsl:when>
                            <xsl:when test="$RF='RF12'">Agriturismo</xsl:when>
                            <xsl:when test="$RF='RF13'">Vendite a domicilio</xsl:when>
                            <xsl:when test="$RF='RF14'">Rivendita beni usati, oggetti d'arte, d'antiquariato o da collezione</xsl:when>
                            <xsl:when test="$RF='RF15'">Agenzie di vendite all'asta di oggetti d'arte, antiquariato o da collezione</xsl:when>
                            <xsl:when test="$RF='RF16'">IVA per cassa P.A.</xsl:when>
                            <xsl:when test="$RF='RF17'">IVA per cassa soggetti con vol. d'affari inferiore ad euro 200.000</xsl:when>
                            <xsl:when test="$RF='RF18'">Altro</xsl:when>
                            <xsl:when test="$RF='RF19'">Regime forfettario (art.1, c.54-89, L. 190/2014)</xsl:when>
                            <xsl:when test="$RF=''"></xsl:when>
                            <xsl:otherwise>
                              <span>
                                <xsl:value-of select="RegimeFiscale"/>&#32;(codice non previsto)</span>
                            </xsl:otherwise>
                          </xsl:choose>
                        </span>
                      </xsl:if>
                    </xsl:for-each>
                  </p>
                </xsl:if>
                <xsl:if test="../FatturaElettronicaHeader/CedentePrestatore">
                  <xsl:if test="../FatturaElettronicaHeader/CedentePrestatore/Sede">
                    <xsl:variable name="CIVICO">
                      <xsl:if test="string-length(normalize-space(../FatturaElettronicaHeader/CedentePrestatore/Sede/NumeroCivico))&gt;0">
                        <xsl:value-of select="concat(',',normalize-space(../FatturaElettronicaHeader/CedentePrestatore/Sede/NumeroCivico))" />
                      </xsl:if>
                    </xsl:variable>
                    <xsl:variable name="PROV">
                      <xsl:if test="string-length(normalize-space(../FatturaElettronicaHeader/CedentePrestatore/Sede/Provincia))&gt;0">
                        <xsl:value-of select="concat('(',normalize-space(../FatturaElettronicaHeader/CedentePrestatore/Sede/Provincia), ')')" />
                      </xsl:if>
                    </xsl:variable>
                    <xsl:variable name="cap">
                      <xsl:if test="string-length(normalize-space(../FatturaElettronicaHeader/CedentePrestatore/Sede/CAP))&gt;0">
                        <xsl:value-of select="concat('- ',normalize-space(../FatturaElettronicaHeader/CedentePrestatore/Sede/CAP), ' -')" />
                      </xsl:if>
                    </xsl:variable>
                    <p class="info">
                      <strong>Sede:&#32;</strong>
                      <xsl:for-each select="../FatturaElettronicaHeader/CedentePrestatore/Sede">
                        <xsl:value-of select="normalize-space(concat(Indirizzo, $CIVICO, ' ', $cap, ' ', Comune, ' ', $PROV, ' ', Nazione))" />
                      </xsl:for-each>
                    </p>
                  </xsl:if>
                  <xsl:if test="../FatturaElettronicaHeader/CedentePrestatore/StabileOrganizzazione">
                    <xsl:variable name="CIVICO">
                      <xsl:if test="string-length(normalize-space(../FatturaElettronicaHeader/CedentePrestatore/StabileOrganizzazione/NumeroCivico))&gt;0">
                        <xsl:value-of select="concat(',',normalize-space(../FatturaElettronicaHeader/CedentePrestatore/StabileOrganizzazione/NumeroCivico))" />
                      </xsl:if>
                    </xsl:variable>
                    <xsl:variable name="PROV">
                      <xsl:if test="string-length(normalize-space(../FatturaElettronicaHeader/CedentePrestatore/StabileOrganizzazione/Provincia))&gt;0">
                        <xsl:value-of select="concat('(',normalize-space(../FatturaElettronicaHeader/CedentePrestatore/StabileOrganizzazione/Provincia), ')')" />
                      </xsl:if>
                    </xsl:variable>
                    <xsl:variable name="cap">
                      <xsl:if test="string-length(normalize-space(../FatturaElettronicaHeader/CedentePrestatore/StabileOrganizzazione/CAP))&gt;0">
                        <xsl:value-of select="concat('- ',normalize-space(../FatturaElettronicaHeader/CedentePrestatore/StabileOrganizzazione/CAP), ' -')" />
                      </xsl:if>
                    </xsl:variable>
                    <p class="info">
                      <strong>Stabile organizzazione:&#32;</strong>
                      <xsl:for-each select="../FatturaElettronicaHeader/CedentePrestatore/StabileOrganizzazione">
                        <xsl:value-of select="normalize-space(concat(Indirizzo, $CIVICO, ' ', $cap, ' ', Comune, ' ', $PROV, ' ', Nazione))" />
                      </xsl:for-each>
                    </p>
                  </xsl:if>
                  <xsl:if test="../FatturaElettronicaHeader/CedentePrestatore/IscrizioneREA">
                    <p class="info">
                      <strong>Iscrizione nel registro delle imprese</strong>
                      <br />
                      <xsl:for-each select="../FatturaElettronicaHeader/CedentePrestatore/IscrizioneREA">
                        <xsl:if test="Ufficio">
                          <span>Provincia Ufficio Registro Imprese:&#32;
                            <span>
                              <xsl:value-of select="Ufficio"/>
                            </span>
                          </span>
                          <br />
                        </xsl:if>
                        <xsl:if test="NumeroREA">
                          <span>Numero di iscrizione:&#32;
                            <span>
                              <xsl:value-of select="NumeroREA"/>
                            </span>
                          </span>
                          <br />
                        </xsl:if>
                        <xsl:if test="CapitaleSociale">
                          <span>Capitale sociale:&#32;
                            <span>
                              <xsl:value-of select="CapitaleSociale"/>
                            </span>
                          </span>
                          <br />
                        </xsl:if>
                        <xsl:if test="SocioUnico">
                          <span>
                            <xsl:variable name="NS">
                              <xsl:value-of select="SocioUnico"/>
                            </xsl:variable>
                            <xsl:choose>
                              <xsl:when test="$NS='SU'">Socio unico</xsl:when>
                              <xsl:when test="$NS='SM'">Pi&#249; soci</xsl:when>
                              <xsl:when test="$NS=''"></xsl:when>
                              <xsl:otherwise>
                                <span>
                                  <xsl:value-of select="SocioUnico"/>&#32;(codice non previsto)</span>
                              </xsl:otherwise>
                            </xsl:choose>
                          </span>
                          <br />
                        </xsl:if>
                        <xsl:if test="StatoLiquidazione">
                          <span>Stato di liquidazione:&#32;
                            <xsl:variable name="SL">
                              <xsl:value-of select="StatoLiquidazione"/>
                            </xsl:variable>
                            <xsl:choose>
                              <xsl:when test="$SL='LS'">in liquidazione</xsl:when>
                              <xsl:when test="$SL='LN'">non in liquidazione</xsl:when>
                              <xsl:when test="$SL=''"></xsl:when>
                              <xsl:otherwise>
                                <span>
                                  <xsl:value-of select="StatoLiquidazione"/>&#32;(codice non previsto)</span>
                              </xsl:otherwise>
                            </xsl:choose>
                          </span>
                        </xsl:if>
                      </xsl:for-each>
                    </p>
                  </xsl:if>
                  <xsl:for-each select="../FatturaElettronicaHeader/CedentePrestatore/Contatti">
                    <xsl:if test="Telefono or Fax or Email">
                      <p class="info">
                        <strong>Recapiti:</strong>
                        <br />
                        <xsl:if test="Telefono">
                          <span>Telefono:&#32;
                            <span>
                              <xsl:value-of select="Telefono"/>
                            </span>
                          </span>
                          <br />
                        </xsl:if>
                        <xsl:if test="Fax">
                          <span>Fax:&#32;
                            <span>
                              <xsl:value-of select="Fax"/>
                            </span>
                          </span>
                          <br />
                        </xsl:if>
                        <xsl:if test="Email">
                          <span>E-mail:&#32;
                            <span>
                              <xsl:value-of select="Email"/>
                            </span>
                          </span>
                        </xsl:if>
                      </p>
                    </xsl:if>
                  </xsl:for-each>
                  <xsl:if test="../FatturaElettronicaHeader/CedentePrestatore/RiferimentoAmministrazione">
                    <h4>Riferimento amministrativo:&#32;
                      <xsl:value-of select="../FatturaElettronicaHeader/CedentePrestatore/RiferimentoAmministrazione"/>
                    </h4>
                  </xsl:if>
                </xsl:if>
                <!--FINE DATI CEDENTE PRESTATORE-->
                <!--INIZIO DATI RAPPRESENTANTE FISCALE-->
                <xsl:if test="../FatturaElettronicaHeader/RappresentanteFiscale">
                  <div class="rappresentante-fiscale srBox">
                    <xsl:if test="../FatturaElettronicaHeader/RappresentanteFiscale/DatiAnagrafici">
                      <xsl:variable name="RAPPRESENTANTE">
                        <xsl:value-of select="concat(string(../FatturaElettronicaHeader/RappresentanteFiscale/DatiAnagrafici/Anagrafica/Nome), ' ', string(../FatturaElettronicaHeader/RappresentanteFiscale/DatiAnagrafici/Anagrafica/Cognome))" />
                      </xsl:variable>
                      <p class="info">
                        <strong>Rappresentante fiscale del cedente/prestatore:</strong>&#32;
                        <xsl:choose>
                          <xsl:when test="string-length(normalize-space($RAPPRESENTANTE)) &gt; 0">
                            <xsl:value-of select="normalize-space(concat(string(../FatturaElettronicaHeader/RappresentanteFiscale/DatiAnagrafici/Anagrafica/Titolo), ' ',$RAPPRESENTANTE))" />
                          </xsl:when>
                          <xsl:otherwise>
                            <xsl:value-of select="normalize-space(concat(string(../FatturaElettronicaHeader/RappresentanteFiscale/DatiAnagrafici/Anagrafica/Titolo), ' ', string(../FatturaElettronicaHeader/RappresentanteFiscale/DatiAnagrafici/Anagrafica/Denominazione)))"/>
                          </xsl:otherwise>
                        </xsl:choose>
                        <br />
                        <xsl:for-each select="../FatturaElettronicaHeader/RappresentanteFiscale/DatiAnagrafici">
                          <xsl:if test="IdFiscaleIVA">
                            <span>Identificativo fiscale ai fini IVA:&#32;
                              <span>
                                <xsl:value-of select="IdFiscaleIVA/IdPaese"/>
                                <xsl:value-of select="IdFiscaleIVA/IdCodice"/>
                              </span>
                            </span>
                            <br />
                          </xsl:if>
                          <xsl:if test="CodiceFiscale">
                            <span>Codice fiscale:&#32;
                              <span>
                                <xsl:value-of select="CodiceFiscale"/>
                              </span>
                            </span>
                            <br />
                          </xsl:if>
                          <xsl:if test="string-length(normalize-space($RAPPRESENTANTE)) &gt; 0">
                            <xsl:if test="Anagrafica/Denominazione">
                              <span>Denominazione:&#32;
                                <span>
                                  <xsl:value-of select="Anagrafica/Denominazione"/>
                                </span>
                              </span>
                              <br />
                            </xsl:if>
							<xsl:if test="Anagrafica/Nome">
							  <span>Nominativo:&#32;
								<span><xsl:value-of select="normalize-space(concat(string(Anagrafica/Nome), ' ', string(Anagrafica/Cognome)))"/></span>
							  </span>
							  <br />
							</xsl:if>
                          </xsl:if>
                          <xsl:if test="Anagrafica/CodEORI">
                            <span>Codice EORI:&#32;
                              <span>
                                <xsl:value-of select="Anagrafica/CodEORI"/>
                              </span>
                            </span>
                          </xsl:if>
                        </xsl:for-each>
                      </p>
                    </xsl:if>
                  </div>
                </xsl:if>
                <!--FINE DATI RAPPRESENTANTE FISCALE-->
              </div>
              <!-- fine mittente -->
              <!--INIZIO DATI CESSIONARIO COMMITTENTE-->
              <xsl:if test="../FatturaElettronicaHeader/CessionarioCommittente">
                <div class="srBox cessionario">
                  <xsl:if test="../FatturaElettronicaHeader/CessionarioCommittente/DatiAnagrafici">
                    <xsl:variable name="CESSIONARIO">
                      <xsl:value-of select="concat(string(../FatturaElettronicaHeader/CessionarioCommittente/DatiAnagrafici/Anagrafica/Nome), ' ', string(../FatturaElettronicaHeader/CessionarioCommittente/DatiAnagrafici/Anagrafica/Cognome))" />
                    </xsl:variable>
                    <p class="info">
                      <strong>Cessionario/committente:&#32;</strong>
                      <xsl:choose>
                        <xsl:when test="string-length(normalize-space($CESSIONARIO)) &gt; 0">
                          <xsl:value-of select="normalize-space(concat(string(../FatturaElettronicaHeader/CessionarioCommittente/DatiAnagrafici/Anagrafica/Titolo), ' ',$CESSIONARIO))" />
                        </xsl:when>
                        <xsl:otherwise>
                          <xsl:value-of select="normalize-space(concat(string(../FatturaElettronicaHeader/CessionarioCommittente/DatiAnagrafici/Anagrafica/Titolo), ' ',string(../FatturaElettronicaHeader/CessionarioCommittente/DatiAnagrafici/Anagrafica/Denominazione)))"/>
                        </xsl:otherwise>
                      </xsl:choose>
                      <br />
                      <xsl:for-each select="../FatturaElettronicaHeader/CessionarioCommittente/DatiAnagrafici">
                        <xsl:if test="IdFiscaleIVA">
                          <span>Identificativo fiscale ai fini IVA:&#32;
                            <span>
                              <xsl:value-of select="IdFiscaleIVA/IdPaese"/>
                              <xsl:value-of select="IdFiscaleIVA/IdCodice"/>
                            </span>
                          </span>
                          <br />
                        </xsl:if>
                        <xsl:if test="CodiceFiscale">
                          <span>Codice Fiscale:&#32;
                            <span>
                              <xsl:value-of select="CodiceFiscale"/>
                            </span>
                          </span>
                          <br />
                        </xsl:if>
                        <xsl:if test="string-length(normalize-space($CESSIONARIO)) &gt; 0">
                          <xsl:if test="Anagrafica/Denominazione">
                            <span>Denominazione:&#32;
                              <span>
                                <xsl:value-of select="Anagrafica/Denominazione" />
                              </span>
                            </span>
                            <br />
                          </xsl:if>
						  <xsl:if test="Anagrafica/Nome">
							  <span>Nominativo:&#32;
								<span><xsl:value-of select="normalize-space(concat(string(Anagrafica/Nome), ' ', string(Anagrafica/Cognome)))"/></span>
							  </span>
							  <br />
						  </xsl:if>
                        </xsl:if>
                        <xsl:if test="Anagrafica/CodEORI">
                          <span>Codice EORI:&#32;
                            <span>
                              <xsl:value-of select="Anagrafica/CodEORI"/>
                            </span>
                          </span>
                          <br />
                        </xsl:if>
                      </xsl:for-each>
                    </p>
                  </xsl:if>
                  <xsl:if test="../FatturaElettronicaHeader/CessionarioCommittente/Sede">
                    <xsl:variable name="CIVICO">
                      <xsl:if test="string-length(normalize-space(../FatturaElettronicaHeader/CessionarioCommittente/Sede/NumeroCivico))&gt;0">
                        <xsl:value-of select="concat(',',normalize-space(../FatturaElettronicaHeader/CessionarioCommittente/Sede/NumeroCivico))" />
                      </xsl:if>
                    </xsl:variable>
                    <xsl:variable name="PROV">
                      <xsl:if test="string-length(normalize-space(../FatturaElettronicaHeader/CessionarioCommittente/Sede/Provincia))&gt;0">
                        <xsl:value-of select="concat('(',normalize-space(../FatturaElettronicaHeader/CessionarioCommittente/Sede/Provincia), ')')" />
                      </xsl:if>
                    </xsl:variable>
                    <xsl:variable name="cap">
                      <xsl:if test="string-length(normalize-space(../FatturaElettronicaHeader/CessionarioCommittente/Sede/CAP))&gt;0">
                        <xsl:value-of select="concat('- ',normalize-space(../FatturaElettronicaHeader/CessionarioCommittente/Sede/CAP), ' -')" />
                      </xsl:if>
                    </xsl:variable>
                    <p class="info">
                      <strong>Sede:&#32;</strong>
                      <xsl:for-each select="../FatturaElettronicaHeader/CessionarioCommittente/Sede">
                        <xsl:value-of select="normalize-space(concat(Indirizzo, $CIVICO, ' ', $cap, ' ', Comune, ' ', $PROV, ' ', Nazione))" />
                      </xsl:for-each>
                    </p>
                  </xsl:if>
				  <xsl:if test="../FatturaElettronicaHeader/CessionarioCommittente/StabileOrganizzazione">
                    <xsl:variable name="CIVICO">
                      <xsl:if test="string-length(normalize-space(../FatturaElettronicaHeader/CessionarioCommittente/StabileOrganizzazione/NumeroCivico))&gt;0">
                        <xsl:value-of select="concat(',',normalize-space(../FatturaElettronicaHeader/CessionarioCommittente/StabileOrganizzazione/NumeroCivico))" />
                      </xsl:if>
                    </xsl:variable>
                    <xsl:variable name="PROV">
                      <xsl:if test="string-length(normalize-space(../FatturaElettronicaHeader/CessionarioCommittente/StabileOrganizzazione/Provincia))&gt;0">
                        <xsl:value-of select="concat('(',normalize-space(../FatturaElettronicaHeader/CessionarioCommittente/StabileOrganizzazione/Provincia), ')')" />
                      </xsl:if>
                    </xsl:variable>
                    <xsl:variable name="cap">
                      <xsl:if test="string-length(normalize-space(../FatturaElettronicaHeader/CessionarioCommittente/StabileOrganizzazione/CAP))&gt;0">
                        <xsl:value-of select="concat('- ',normalize-space(../FatturaElettronicaHeader/CessionarioCommittente/StabileOrganizzazione/CAP), ' -')" />
                      </xsl:if>
                    </xsl:variable>
                    <p class="info">
                      <strong>Stabile organizzazione:&#32;</strong>
                      <xsl:for-each select="../FatturaElettronicaHeader/CessionarioCommittente/StabileOrganizzazione">
                        <xsl:value-of select="normalize-space(concat(Indirizzo, $CIVICO, ' ', $cap, ' ', Comune, ' ', $PROV, ' ', Nazione))" />
                      </xsl:for-each>
                    </p>
                  </xsl:if>
				  <xsl:if test="../FatturaElettronicaHeader/CessionarioCommittente/RappresentanteFiscale">
					<p class="info">
						<strong>Rappresentate fiscale:&#32;</strong><br />
						<xsl:if test="../FatturaElettronicaHeader/CessionarioCommittente/RappresentanteFiscale/IdFiscaleIVA">
                          <span>Identificativo fiscale ai fini IVA:&#32;
                            <span>
                              <xsl:value-of select="../FatturaElettronicaHeader/CessionarioCommittente/RappresentanteFiscale/IdFiscaleIVA/IdPaese"/>
                              <xsl:value-of select="../FatturaElettronicaHeader/CessionarioCommittente/RappresentanteFiscale/IdFiscaleIVA/IdCodice"/>
                            </span>
                          </span>
                          <br />
						</xsl:if>
						<xsl:if test="../FatturaElettronicaHeader/CessionarioCommittente/RappresentanteFiscale/Denominazione">
                          <span>Denominazione:&#32;<span><xsl:value-of select="../FatturaElettronicaHeader/CessionarioCommittente/RappresentanteFiscale/Denominazione"/></span></span>
						</xsl:if>
						<xsl:if test="../FatturaElettronicaHeader/CessionarioCommittente/RappresentanteFiscale/Nome">
                          <span>Nominativo:&#32;
							<span><xsl:value-of select="normalize-space(concat(string(../FatturaElettronicaHeader/CessionarioCommittente/RappresentanteFiscale/Nome), ' ', string(../FatturaElettronicaHeader/CessionarioCommittente/RappresentanteFiscale/Cognome)))"/></span>
						  </span>
						</xsl:if>
					</p>
				  </xsl:if>
                </div>
              </xsl:if>
              <!--FINE DATI CESSIONARIO COMMITTENTE-->
              <div class="separa">
                <p>&#32;</p>
              </div>
              <!--INIZIO DATI TERZO INTERMEDIARIO SOGGETTO EMITTENTE-->
              <xsl:if test="../FatturaElettronicaHeader/TerzoIntermediarioOSoggettoEmittente">
                <xsl:variable name="TERZOINTERMEDIARIO">
                  <xsl:value-of select="concat(string(../FatturaElettronicaHeader/TerzoIntermediarioOSoggettoEmittente/DatiAnagrafici/Anagrafica/Nome), ' ', string(../FatturaElettronicaHeader/TerzoIntermediarioOSoggettoEmittente/DatiAnagrafici/Anagrafica/Cognome))" />
                </xsl:variable>
                <div class="terzointermediario srBox">
                  <xsl:for-each select="../FatturaElettronicaHeader/TerzoIntermediarioOSoggettoEmittente">
                    <xsl:if test="DatiAnagrafici">
                      <p class="info">
                        <strong>Terzo intermediario soggetto emittente:</strong>&#32;
                        <xsl:choose>
                          <xsl:when test="string-length(normalize-space($TERZOINTERMEDIARIO)) &gt; 0">
                            <xsl:value-of select="normalize-space(concat(string(../FatturaElettronicaHeader/TerzoIntermediarioOSoggettoEmittente/DatiAnagrafici/Anagrafica/Titolo), ' ',$TERZOINTERMEDIARIO))" />
                          </xsl:when>
                          <xsl:otherwise>
                            <xsl:value-of select="normalize-space(concat(string(../FatturaElettronicaHeader/TerzoIntermediarioOSoggettoEmittente/DatiAnagrafici/Anagrafica/Titolo), ' ',string(../FatturaElettronicaHeader/TerzoIntermediarioOSoggettoEmittente/DatiAnagrafici/Anagrafica/Denominazione)))"/>
                          </xsl:otherwise>
                        </xsl:choose>
                        <br />
                        <xsl:if test="DatiAnagrafici/IdFiscaleIVA">
                          <span>Identificativo fiscale ai fini IVA:&#32;
                            <span>
                              <xsl:value-of select="DatiAnagrafici/IdFiscaleIVA/IdPaese"/>
                              <xsl:value-of select="DatiAnagrafici/IdFiscaleIVA/IdCodice"/>
                            </span>
                          </span>
                          <br />
                        </xsl:if>
                        <xsl:if test="DatiAnagrafici/CodiceFiscale">
                          <span>Codice Fiscale:&#32;
                            <span>
                              <xsl:value-of select="DatiAnagrafici/CodiceFiscale"/>
                            </span>
                          </span>
                          <br />
                        </xsl:if>
                        <xsl:if test="string-length(normalize-space($TERZOINTERMEDIARIO)) &gt; 0">
                          <xsl:if test="DatiAnagrafici/Anagrafica/Denominazione">
                            <span>Denominazione:&#32;
                              <span>
                                <xsl:value-of select="DatiAnagrafici/Anagrafica/Denominazione"/>
                              </span>
                            </span>
                            <br />
                          </xsl:if>
						  <xsl:if test="DatiAnagrafici/Anagrafica/Nome">
						  <span>Nominativo:&#32;
							<span><xsl:value-of select="normalize-space(concat(string(DatiAnagrafici/Anagrafica/Nome), ' ', string(DatiAnagrafici/Anagrafica/Cognome)))"/></span>
						  </span>
						  <br />
						</xsl:if>
                        </xsl:if>
                        <xsl:if test="DatiAnagrafici/Anagrafica/CodEORI">
                          <span>Codice EORI:&#32;
                            <span>
                              <xsl:value-of select="DatiAnagrafici/Anagrafica/CodEORI"/>
                            </span>
                          </span>
                        </xsl:if>
                      </p>
                    </xsl:if>
                  </xsl:for-each>
                </div>
                <div class="separa">
                  <p>&#32;</p>
                </div>
              </xsl:if>
              <!--FINE DATI TERZO INTERMEDIARIO SOGGETTO EMITTENTE-->
              <div class="decora">
                <p>
                  <br />
                </p>
              </div>
            </div>
            <!-- fine testataFattura -->
            <div class="separa">
              <p>&#32;</p>
            </div>
            <div class="elementoLotto">
              <div class="intestazioneElemLotto">
                <h2 class="titolo">
                  <xsl:if test="$TOTALELOTTO&gt;1">(
                    <span class="evidenzia">
                      <strong>
                        <xsl:value-of select="position()"/>
                      </strong>
                    </span>/
                    <xsl:value-of select="$TOTALELOTTO" />)&#32;</xsl:if>
                  <xsl:variable name="TD">
                    <xsl:value-of select="DatiGenerali/DatiGeneraliDocumento/TipoDocumento"/>
                  </xsl:variable>
                  <xsl:choose>
                    <xsl:when test="$TD='TD01'">Fattura</xsl:when>
                    <xsl:when test="$TD='TD02'">Acconto/anticipo su fattura</xsl:when>
                    <xsl:when test="$TD='TD03'">Acconto/anticipo su parcella</xsl:when>
                    <xsl:when test="$TD='TD04'">Nota di credito</xsl:when>
                    <xsl:when test="$TD='TD05'">Nota di debito</xsl:when>
                    <xsl:when test="$TD='TD06'">Parcella</xsl:when>
                    <xsl:when test="$TD=''"></xsl:when>
                    <xsl:otherwise>
                      <xsl:value-of select="DatiGenerali/DatiGeneraliDocumento/TipoDocumento"/>
                      <span>&#32;- (codice non previsto) -</span>
                    </xsl:otherwise>
                  </xsl:choose>&#32;nr.
                  <xsl:value-of select="DatiGenerali/DatiGeneraliDocumento/Numero"/>&#32;del
                  <xsl:call-template name="dataItaliana">
                    <xsl:with-param name="data" select="DatiGenerali/DatiGeneraliDocumento/Data"/>
                  </xsl:call-template>
                  <xsl:if test="DatiGenerali/DatiGeneraliDocumento/Art73 = 'SI'">&#32;(Art. 73 DPR 633/72)</xsl:if>
                </h2>
                <xsl:if test="DatiGenerali/DatiGeneraliDocumento/ImportoTotaleDocumento">
                  <ul class="riepilogoGeneraleDettaglio">
                    <li class="titoloSx">
                      <span>Importo totale documento:</span>
                    </li>
                    <li class="titoloDx">
                      <span class="evidenzia">
                        <strong>
                          <xsl:value-of select="format-number(DatiGenerali/DatiGeneraliDocumento/ImportoTotaleDocumento, '#.##0,00', 'euro')"/>
                        </strong>
                      </span>
                      <xsl:if test="DatiGenerali/DatiGeneraliDocumento/Divisa">
                        <span>&#32;(<xsl:value-of select="DatiGenerali/DatiGeneraliDocumento/Divisa"/>)</span>
                      </xsl:if>
                    </li>
                  </ul>
                  <div class="separa">
                    <p>&#32;</p>
                  </div>
                </xsl:if>

                <xsl:variable name="nrPagamenti">
                	<xsl:value-of select="count(DatiPagamento/DettaglioPagamento/ImportoPagamento)"/>
                </xsl:variable>
                <xsl:variable name="divisaPagamenti">
                	<xsl:value-of select="DatiGenerali/DatiGeneraliDocumento/Divisa"/>
                </xsl:variable>
                <xsl:if test="$nrPagamenti &gt; 0">
                    <xsl:if test="$nrPagamenti &gt; 1">
                      <ul class="riepilogoGeneraleDettaglio">
	                    <li class="titoloSx">
	                      <span><br /><strong>Riepilogo dei pagamenti:</strong></span>
		                </li>
		              </ul>
		              <div class="separa"> <p>&#32;</p> </div>
                    </xsl:if>
                 	<xsl:for-each select="DatiPagamento/DettaglioPagamento">
                  	<ul class="riepilogoGeneraleDettaglio">
	                    <li class="titoloSx">
	                      <span>Importo da pagare
	                      <xsl:if test="DataScadenzaPagamento">&#32;entro il&#32;
	                        <strong>
	                            <xsl:call-template name="dataItaliana">
	                              <xsl:with-param name="data" select="DataScadenzaPagamento"/>
	                            </xsl:call-template>
	                          </strong>
	                        </xsl:if>
	                        <xsl:if test="Beneficiario">&#32;a&#32;<strong><xsl:value-of select="Beneficiario"/></strong>
	                      </xsl:if>:</span>
	                    </li>
	                    <li class="titoloDx">
	                      <span class="evidenzia">
	                        <strong>
	                          <xsl:value-of select="format-number(ImportoPagamento, '#.##0,00', 'euro')"/>
	                        </strong>
	                      </span>
	                      <xsl:if test="not($divisaPagamenti = '')">
	                        <span>&#32;(<xsl:value-of select="$divisaPagamenti"/>)</span>
	                      </xsl:if>
	                    </li>
	                    </ul>
	                    <div class="separa">
                    <p>&#32;</p>
                  </div>
	              	</xsl:for-each>
                  <div class="separa">
                    <p>&#32;<xsl:if test="$nrPagamenti &gt; 1"><br /></xsl:if></p>
                  </div>
                </xsl:if>
                <xsl:if test="DatiGenerali/DatiGeneraliDocumento/Arrotondamento">
                  <ul class="riepilogoGeneraleDettaglio">
                    <li class="titoloSx">
                      <span>Arrotondamento su importo totale documento:</span>
                    </li>
                    <li class="titoloDx">
                      <span class="evidenzia">
                        <strong>
                          <xsl:value-of select="format-number(DatiGenerali/DatiGeneraliDocumento/Arrotondamento, '#.##0,00', 'euro')"/>
                        </strong>
                      </span>
                      <xsl:if test="DatiGenerali/DatiGeneraliDocumento/Divisa">
                        <span>&#32;(<xsl:value-of select="DatiGenerali/DatiGeneraliDocumento/Divisa"/>)</span>
                      </xsl:if>
                    </li>
                  </ul>
                  <div class="separa">
                    <p>&#32;</p>
                  </div>
                </xsl:if>
                <xsl:variable name="hasCausali">
                  <xsl:value-of select="count(DatiGenerali/DatiGeneraliDocumento/Causale)" />
                </xsl:variable>
                <xsl:if test="$hasCausali &gt; 0">
                  <xsl:for-each select="DatiGenerali/DatiGeneraliDocumento/Causale">
                    <span class="titoloSx">Causale:
                      <strong>
                        <xsl:value-of select="."/>
                      </strong>
                    </span>
                    <div class="separa">
                      <p>&#32;</p>
                    </div>
                  </xsl:for-each>
                </xsl:if>
                <!--INIZIO DATI DELLA RITENUTA-->
                <xsl:if test="DatiGenerali/DatiGeneraliDocumento/DatiRitenuta">
                  <span class="titoloSx">
                    <span>
                      <xsl:variable name="TR">
                        <xsl:value-of select="DatiGenerali/DatiGeneraliDocumento/DatiRitenuta/TipoRitenuta"/>
                      </xsl:variable>
                      <xsl:choose>
                        <xsl:when test="$TR='RT01'">Ritenuta persone fisiche</xsl:when>
                        <xsl:when test="$TR='RT02'">Ritenuta persone giuridiche</xsl:when>
                        <xsl:when test="$TR=''"></xsl:when>
                        <xsl:otherwise>
                          <span>
                            <xsl:value-of select="DatiGenerali/DatiGeneraliDocumento/DatiRitenuta/TipoRitenuta"/>(codice non previsto)</span>
                        </xsl:otherwise>
                      </xsl:choose>
                    </span>
                    <span>&#32;di
                      <span>
                        <xsl:value-of select="format-number(DatiGenerali/DatiGeneraliDocumento/DatiRitenuta/ImportoRitenuta, '#.##0,00', 'euro')"/>
                      </span>
                      <span>&#32;(<xsl:value-of select="DatiGenerali/DatiGeneraliDocumento/Divisa"/>)</span>
                    </span>
                    <span>(
                      <xsl:value-of select="format-number(DatiGenerali/DatiGeneraliDocumento/DatiRitenuta/AliquotaRitenuta, '#.##0,00', 'euro')"/>%)</span>
                    <span>- Causale di pagamento
                      <span>
                        <xsl:value-of select="DatiGenerali/DatiGeneraliDocumento/DatiRitenuta/CausalePagamento"/>
                      </span>
                      <xsl:variable name="CP">
                        <xsl:value-of select="DatiGenerali/DatiGeneraliDocumento/DatiRitenuta/CausalePagamento"/>
                      </xsl:variable>
                      <xsl:if test="$CP!=''">(decodifica come da modello 770S)</xsl:if>
                    </span>
                  </span>
                  <div class="separa">
                    <p>&#32;</p>
                  </div>
                </xsl:if>
                <!--FINE DATI DELLA RITENUTA-->
                <!--INIZIO DATI DEL BOLLO-->
                <xsl:if test="DatiGenerali/DatiGeneraliDocumento/DatiBollo">
                  <span class="titoloSx">
                    <xsl:if test="DatiGenerali/DatiGeneraliDocumento/DatiBollo/BolloVirtuale">
                      <span>
                        <xsl:variable name="BV">
                          <xsl:value-of select="DatiGenerali/DatiGeneraliDocumento/DatiBollo/BolloVirtuale"/>
                        </xsl:variable>
                        <xsl:choose>
                          <xsl:when test="$BV='SI'">Bollo virtuale:
                            <strong>SI</strong>
                          </xsl:when>
                          <xsl:when test="$BV=''"></xsl:when>
                          <xsl:otherwise>
                            <span>Bollo Virtuale:&#32;
                              <xsl:value-of select="DatiGenerali/DatiGeneraliDocumento/DatiBollo/BolloVirtuale"/>
                              <strong>(codice non previsto)</strong>
                            </span>
                          </xsl:otherwise>
                        </xsl:choose>
                      </span>
                    </xsl:if>
                    <xsl:if test="DatiGenerali/DatiGeneraliDocumento/DatiBollo/ImportoBollo">
                      <span>&#32;
                        <span>
                          <span>&#32;con importo&#32;</span>
                          <strong>
                            <xsl:value-of select="format-number(DatiGenerali/DatiGeneraliDocumento/DatiBollo/ImportoBollo, '#.##0,00', 'euro')"/>
                          </strong>
                        </span>
                        <span>&#32;(<xsl:value-of select="DatiGenerali/DatiGeneraliDocumento/Divisa"/>)</span>
                      </span>
                    </xsl:if>
                  </span>
                  <div class="separa">
                    <p>&#32;</p>
                  </div>
                </xsl:if>
                <!--FINE DATI DEL BOLLO-->
              </div>
              <!-- fine intestazioneElemLotto -->
              <div class="separa">
                <p>&#32;</p>
              </div>
              <xsl:variable name="TOTALEDETTAGLI">
                <xsl:value-of select="count(DatiBeniServizi/DettaglioLinee)"/>
              </xsl:variable>
              <xsl:variable name="hasValueNumeroLinea">
                <xsl:value-of select="count(DatiBeniServizi/DettaglioLinee/NumeroLinea)"/>
              </xsl:variable>
              <xsl:variable name="hasValueTipoCessionePrestazione">
                <xsl:value-of select="count(DatiBeniServizi/DettaglioLinee/TipoCessionePrestazione)"/>
              </xsl:variable>
              <xsl:variable name="hasValueCodiceArticolo">
                <xsl:value-of select="count(DatiBeniServizi/DettaglioLinee/CodiceArticolo)"/>
              </xsl:variable>
              <xsl:variable name="hasValueDescrizione">
                <xsl:value-of select="count(DatiBeniServizi/DettaglioLinee/Descrizione)"/>
              </xsl:variable>
              <xsl:variable name="hasValueQuantita">
                <xsl:value-of select="count(DatiBeniServizi/DettaglioLinee/Quantita)"/>
              </xsl:variable>
              <xsl:variable name="hasValueUnitaMisura">
                <xsl:value-of select="count(DatiBeniServizi/DettaglioLinee/UnitaMisura)"/>
              </xsl:variable>
              <xsl:variable name="hasValueDataInizioPeriodo">
                <xsl:value-of select="count(DatiBeniServizi/DettaglioLinee/DataInizioPeriodo)"/>
              </xsl:variable>
              <xsl:variable name="hasValueDataFinePeriodo">
                <xsl:value-of select="count(DatiBeniServizi/DettaglioLinee/DataFinePeriodo)"/>
              </xsl:variable>
              <xsl:variable name="hasValuePrezzoUnitario">
                <xsl:value-of select="count(DatiBeniServizi/DettaglioLinee/PrezzoUnitario)"/>
              </xsl:variable>
              <xsl:variable name="hasValueScontoMaggiorazione">
                <xsl:value-of select="count(DatiBeniServizi/DettaglioLinee/ScontoMaggiorazione)"/>
              </xsl:variable>
              <xsl:variable name="hasValuePrezzoTotale">
                <xsl:value-of select="count(DatiBeniServizi/DettaglioLinee/PrezzoTotale)"/>
              </xsl:variable>
              <xsl:variable name="hasValueAliquotaIVA">
                <xsl:value-of select="count(DatiBeniServizi/DettaglioLinee/AliquotaIVA)"/>
              </xsl:variable>
              <xsl:variable name="hasValueRitenuta">
                <xsl:value-of select="count(DatiBeniServizi/DettaglioLinee/Ritenuta)"/>
              </xsl:variable>
              <xsl:variable name="hasValueNatura">
                <xsl:value-of select="count(DatiBeniServizi/DettaglioLinee/Natura)"/>
              </xsl:variable>
              <xsl:variable name="hasValueRiferimentoAmministrazione">
                <xsl:value-of select="count(DatiBeniServizi/DettaglioLinee/RiferimentoAmministrazione)"/>
              </xsl:variable>
              <xsl:variable name="hasValueAltriDatiGestionali">
                <xsl:value-of select="count(DatiBeniServizi/DettaglioLinee/AltriDatiGestionali/TipoDato)"/>
              </xsl:variable>
              <xsl:if test="$TOTALEDETTAGLI&gt;0">
                <div class="dettagli">
                  <div class="separa">
                    <p>&#32;</p>
                  </div>
                  <table class="tableDettagli">
                    <caption>Riassunto dettagli fattura</caption>
                    <thead>
                      <tr>
                        <xsl:if test="$hasValueNumeroLinea&gt;0">
                          <th>Dettaglio doc.</th>
                        </xsl:if>
                        <xsl:if test="$hasValueTipoCessionePrestazione&gt;0">
                          <th>Tipo cessione/prestazione</th>
                        </xsl:if>
                        <xsl:if test="$hasValueCodiceArticolo&gt;0">
                          <th>Cod. articolo</th>
                        </xsl:if>
                        <xsl:if test="$hasValueDescrizione&gt;0">
                          <th>Descrizione</th>
                        </xsl:if>
                        <xsl:if test="$hasValueQuantita&gt;0">
                          <th>Quantit&#224;</th>
                        </xsl:if>
                        <xsl:if test="$hasValueUnitaMisura&gt;0">
                          <th>Unit&#224; misura</th>
                        </xsl:if>
                        <xsl:if test="$hasValueDataInizioPeriodo&gt;0">
                          <th>dal</th>
                        </xsl:if>
                        <xsl:if test="$hasValueDataFinePeriodo&gt;0">
                          <th>al</th>
                        </xsl:if>
                        <xsl:if test="$hasValuePrezzoUnitario&gt;0">
                          <th>Valore unitario
                            <xsl:if test="DatiGenerali/DatiGeneraliDocumento/Divisa">
                              <span>&#32;(<xsl:value-of select="DatiGenerali/DatiGeneraliDocumento/Divisa"/>)</span>
                            </xsl:if>
                          </th>
                        </xsl:if>
                        <xsl:if test="$hasValueScontoMaggiorazione&gt;0">
                          <th>Sconto/maggiorazione</th>
                        </xsl:if>
                        <xsl:if test="$hasValuePrezzoTotale&gt;0">
                          <th>Valore totale
                            <xsl:if test="DatiGenerali/DatiGeneraliDocumento/Divisa">
                              <span>&#32;(<xsl:value-of select="DatiGenerali/DatiGeneraliDocumento/Divisa"/>)</span>
                            </xsl:if>
                          </th>
                        </xsl:if>
                        <xsl:if test="$hasValueAliquotaIVA&gt;0">
                          <th>Aliquota IVA</th>
                        </xsl:if>
                        <xsl:if test="$hasValueRitenuta&gt;0">
                          <th>Ritenuta</th>
                        </xsl:if>
                        <xsl:if test="$hasValueNatura&gt;0">
                          <th>Natura operazione</th>
                        </xsl:if>
                        <xsl:if test="$hasValueRiferimentoAmministrazione&gt;0">
                          <th>Riferimento amm.</th>
                        </xsl:if>
                        <xsl:if test="$hasValueAltriDatiGestionali&gt;0">
                          <th>Altri dati gestionali</th>
                        </xsl:if>
                      </tr>
                    </thead>
                    <tbody>
                      <xsl:for-each select="DatiBeniServizi/DettaglioLinee">
                        <tr>
                          <xsl:if test="$hasValueNumeroLinea&gt;0">
                            <td>
                              <xsl:value-of select="NumeroLinea"/>
                            </td>
                          </xsl:if>
                          <xsl:if test="$hasValueTipoCessionePrestazione&gt;0">
                            <td>
                              <xsl:variable name="TCP">
                                <xsl:value-of select="TipoCessionePrestazione"/>
                              </xsl:variable>
                              <xsl:choose>
                                <xsl:when test="$TCP='SC'">Sconto</xsl:when>
                                <xsl:when test="$TCP='PR'">Premio</xsl:when>
                                <xsl:when test="$TCP='AB'">Abbuono</xsl:when>
                                <xsl:when test="$TCP='AC'">Spesa accessoria</xsl:when>
                                <xsl:otherwise>
                                  <span>
                                    <xsl:value-of select="TipoCessionePrestazione"/>(codice non previsto)</span>
                                </xsl:otherwise>
                              </xsl:choose>
                            </td>
                          </xsl:if>
                          <xsl:if test="$hasValueCodiceArticolo&gt;0">
                            <td>
                              <xsl:value-of select="CodiceArticolo"/>
                            </td>
                          </xsl:if>
                          <xsl:if test="$hasValueDescrizione&gt;0">
                            <td class="aSx">
                              <xsl:value-of select="Descrizione"/>
                            </td>
                          </xsl:if>
                          <xsl:if test="$hasValueQuantita&gt;0">
                            <td>
                              <xsl:value-of select="Quantita"/>
                            </td>
                          </xsl:if>
                          <xsl:if test="$hasValueUnitaMisura&gt;0">
                            <td>
                              <xsl:value-of select="UnitaMisura"/>
                            </td>
                          </xsl:if>
                          <xsl:if test="$hasValueDataInizioPeriodo&gt;0">
                            <td>
                              <xsl:call-template name="dataItaliana">
                                <xsl:with-param name="data" select="DataInizioPeriodo"/>
                              </xsl:call-template>
                            </td>
                          </xsl:if>
                          <xsl:if test="$hasValueDataFinePeriodo&gt;0">
                            <td>
                              <xsl:call-template name="dataItaliana">
                                <xsl:with-param name="data" select="DataFinePeriodo"/>
                              </xsl:call-template>
                            </td>
                          </xsl:if>
                          <xsl:if test="$hasValuePrezzoUnitario&gt;0">
                            <td class="aDx">
                              <xsl:value-of select="format-number(PrezzoUnitario, '#.######0,000000', 'euro')"/>
                            </td>
                          </xsl:if>
                          <xsl:if test="$hasValueScontoMaggiorazione&gt;0">
                            <xsl:variable name="hasValueScontoMaggiorazioneDettaglio">
                              <xsl:value-of select="count(ScontoMaggiorazione/Tipo)"/>
                            </xsl:variable>
                            <td>
                              <xsl:for-each select="ScontoMaggiorazione">
                                <xsl:if test="Tipo">
                                  <xsl:variable name="TSCM">
                                    <xsl:value-of select="Tipo"/>
                                  </xsl:variable>
                                  <xsl:choose>
                                    <xsl:when test="$TSCM='SC'">Sconto</xsl:when>
                                    <xsl:when test="$TSCM='MG'">Maggiorazione</xsl:when>
                                    <xsl:otherwise>
                                      <span>
                                        <xsl:value-of select="Tipo" />(codice non previsto)</span>
                                    </xsl:otherwise>
                                  </xsl:choose>
                                </xsl:if>
                                <xsl:if test="Percentuale">
                                  <span>
                                    <xsl:value-of select="format-number(Percentuale, '#.##0,00', 'euro')"/>
                                    <xsl:if test="Percentuale != ''">%</xsl:if>
                                  </span>
                                </xsl:if>
                                <xsl:if test="Importo">Importo:
                                  <span>
                                    <xsl:value-of select="format-number(Importo, '#.##0,00', 'euro')"/>
                                  </span>
                                </xsl:if>
                                <xsl:if test="position() != last()">
                                  <br />
                                </xsl:if>
                              </xsl:for-each>
                            </td>
                          </xsl:if>
                          <xsl:if test="$hasValuePrezzoTotale&gt;0">
                            <td class="aDx">
                              <xsl:value-of select="format-number(PrezzoTotale, '#.##0,00', 'euro')"/>
                            </td>
                          </xsl:if>
                          <xsl:if test="$hasValueAliquotaIVA&gt;0">
                            <td class="aDx">
                              <xsl:value-of select="format-number(AliquotaIVA, '#.##0,00', 'euro')"/>
                              <xsl:if test="AliquotaIVA != ''">%</xsl:if>
                            </td>
                          </xsl:if>
                          <xsl:if test="$hasValueRitenuta&gt;0">
                            <td>
                              <xsl:value-of select="normalize-space(Ritenuta)"/>
                            </td>
                          </xsl:if>
                          <xsl:if test="$hasValueNatura&gt;0">
                            <td>
                              <xsl:variable name="NAT">
                                <xsl:value-of select="normalize-space(Natura)"/>
                              </xsl:variable>
                              <xsl:choose>
                                <xsl:when test="$NAT='N1'">Esclusa ex art.15</xsl:when>
                                <xsl:when test="$NAT='N2'">Non soggetta</xsl:when>
                                <xsl:when test="$NAT='N3'">Non imponibile</xsl:when>
                                <xsl:when test="$NAT='N4'">Esente</xsl:when>
                                <xsl:when test="$NAT='N5'">Regime del margine</xsl:when>
                                <xsl:when test="$NAT='N6'">Inversione contabile(reverse charge)</xsl:when>
                                <xsl:when test="$NAT='N7'">IVA assolta in altro stato UE (vendite a distanza ex art. 40 c. 3 e 4 e art. 41 c. 1 lett. b, DL 331/93; prestazione di servizi di telecomunicazioni, tele-radiodiffusione ed elettronici ex art. 7-sexies lett. f, g, art. 74-sexies DPR 633/72)</xsl:when>
                                <xsl:when test="$NAT=''"></xsl:when>
                                <xsl:otherwise>
                                  <span>
                                    <xsl:value-of select="Natura"/>(codice non previsto)</span>
                                </xsl:otherwise>
                              </xsl:choose>
                            </td>
                          </xsl:if>
                          <xsl:if test="$hasValueRiferimentoAmministrazione&gt;0">
                            <td>
                              <xsl:value-of select="RiferimentoAmministrazione"/>
                            </td>
                          </xsl:if>
                          <xsl:if test="$hasValueAltriDatiGestionali&gt;0">
                            <td class="aSx">
                              <xsl:for-each select="AltriDatiGestionali">
                                <xsl:if test="TipoDato">
                                  <xsl:if test="position() &gt; 1">
                                    <br />
                                  </xsl:if>
                                  <span>Tipo:
                                    <xsl:value-of select="TipoDato"/>
                                  </span>
                                </xsl:if>
                                <xsl:if test="RiferimentoTesto">
                                  <br />
                                  <span>Testo:
                                    <xsl:value-of select="RiferimentoTesto"/>
                                  </span>
                                </xsl:if>
                                <xsl:if test="RiferimentoNumero">
                                  <br />
                                  <span>Numero:
                                    <xsl:value-of select="RiferimentoNumero"/>
                                  </span>
                                </xsl:if>
                                <xsl:if test="RiferimentoData">
                                  <br />
                                  <span>del&#32;
                                    <xsl:call-template name="dataItaliana">
                                      <xsl:with-param name="data" select="RiferimentoData"/>
                                    </xsl:call-template>
                                  </span>
                                </xsl:if>
                              </xsl:for-each>
                            </td>
                          </xsl:if>
                        </tr>
                      </xsl:for-each>
                    </tbody>
                    <tfoot></tfoot>
                  </table>
                </div>
                <!-- fine dettagli -->
              </xsl:if>
              <div class="separa">
                <p>&#32;</p>
              </div>
              <div class="riassunto">
                <!--INIZIO DATI DELLA CASSA PREVIDENZIALE-->
                <xsl:if test="DatiGenerali/DatiGeneraliDocumento/DatiCassaPrevidenziale">
                  <!-- variabili per creazione colonne celle -->
                  <xsl:variable name="hasImponibileCassa">
                    <xsl:value-of select="count(DatiGenerali/DatiGeneraliDocumento/DatiCassaPrevidenziale/ImponibileCassa)" />
                  </xsl:variable>
                  <xsl:variable name="hasRitenuta">
                    <xsl:value-of select="count(DatiGenerali/DatiGeneraliDocumento/DatiCassaPrevidenziale/Ritenuta)" />
                  </xsl:variable>
                  <xsl:variable name="hasNatura">
                    <xsl:value-of select="count(DatiGenerali/DatiGeneraliDocumento/DatiCassaPrevidenziale/Natura)" />
                  </xsl:variable>
                  <xsl:variable name="hasRiferimentoAmministrazione">
                    <xsl:value-of select="count(DatiGenerali/DatiGeneraliDocumento/DatiCassaPrevidenziale/RiferimentoAmministrazione)" />
                  </xsl:variable>
                  <div class="dati-cassa-previdenziale">
                    <table class="tableDettagli">
                      <caption>Cassa previdenziale</caption>
                      <thead>
                        <tr>
                          <th>Cassa</th>
                          <th>Aliquota</th>
                          <th>Imp. contrib.
                            <span>(<xsl:value-of select="DatiGenerali/DatiGeneraliDocumento/Divisa"/>)</span>
                          </th>
                          <xsl:if test="$hasImponibileCassa &gt; 0">
                            <th>Impon.
                              <span>&#32;(<xsl:value-of select="DatiGenerali/DatiGeneraliDocumento/Divisa"/>)</span>
                            </th>
                          </xsl:if>
                          <th>Aliq. IVA</th>
                          <xsl:if test="$hasRitenuta &gt; 0">
                            <th>Rit.</th>
                          </xsl:if>
                          <xsl:if test="$hasNatura &gt; 0">
                            <th>Natura</th>
                          </xsl:if>
                          <xsl:if test="$hasRiferimentoAmministrazione &gt; 0">
                            <th>Rif. amm.</th>
                          </xsl:if>
                        </tr>
                      </thead>
                      <tbody>
                        <xsl:for-each select="DatiGenerali/DatiGeneraliDocumento/DatiCassaPrevidenziale">
                          <tr>
                            <td class="aSx">
                              <xsl:variable name="TC">
                                <xsl:value-of select="TipoCassa"/>
                              </xsl:variable>
                              <xsl:choose>
                                <xsl:when test="$TC='TC01'">Cassa Nazionale Previdenza e Assistenza Avvocatie Procuratori legali</xsl:when>
                                <xsl:when test="$TC='TC02'">Cassa Previdenza Dottori Commercialisti</xsl:when>
                                <xsl:when test="$TC='TC03'">Cassa Previdenza e Assistenza Geometri</xsl:when>
                                <xsl:when test="$TC='TC04'">Cassa Nazionale Previdenza e Assistenza Ingegneri e Architetti liberi profess.</xsl:when>
                                <xsl:when test="$TC='TC05'">Cassa Nazionale del Notariato</xsl:when>
                                <xsl:when test="$TC='TC06'">Cassa Nazionale Previdenza e Assistenza Ragionieri e Periti commerciali</xsl:when>
                                <xsl:when test="$TC='TC07'">Ente Nazionale Assistenza Agenti e Rappresentanti di Commercio-ENASARCO</xsl:when>
                                <xsl:when test="$TC='TC08'">Ente Nazionale Previdenza e Assistenza Consulenti del Lavoro-ENPACL</xsl:when>
                                <xsl:when test="$TC='TC09'">Ente Nazionale Previdenza e Assistenza Medici-ENPAM</xsl:when>
                                <xsl:when test="$TC='TC10'">Ente Nazionale Previdenza e Assistenza Farmacisti-ENPAF</xsl:when>
                                <xsl:when test="$TC='TC11'">Ente Nazionale Previdenza e Assistenza Veterinari-ENPAV</xsl:when>
                                <xsl:when test="$TC='TC12'">Ente Nazionale Previdenza e Assistenza Impiegati dell'Agricoltura-ENPAIA</xsl:when>
                                <xsl:when test="$TC='TC13'">Fondo Previdenza Impiegati Imprese di Spedizione e Agenzie Marittime</xsl:when>
                                <xsl:when test="$TC='TC14'">Istituto Nazionale Previdenza Giornalisti Italiani-INPGI</xsl:when>
                                <xsl:when test="$TC='TC15'">Opera Nazionale Assistenza Orfani Sanitari Italiani-ONAOSI</xsl:when>
                                <xsl:when test="$TC='TC16'">Cassa Autonoma Assistenza Integrativa Giornalisti Italiani-CASAGIT</xsl:when>
                                <xsl:when test="$TC='TC17'">Ente Previdenza Periti Industriali e Periti Industriali Laureati-EPPI</xsl:when>
                                <xsl:when test="$TC='TC18'">Ente Previdenza e Assistenza Pluricategoriale-EPAP</xsl:when>
                                <xsl:when test="$TC='TC19'">Ente Nazionale Previdenza e Assistenza Biologi-ENPAB</xsl:when>
                                <xsl:when test="$TC='TC20'">Ente Nazionale Previdenza e Assistenza Professione Infermieristica-ENPAPI</xsl:when>
                                <xsl:when test="$TC='TC21'">Ente Nazionale Previdenza e Assistenza Psicologi-ENPAP</xsl:when>
                                <xsl:when test="$TC='TC22'">INPS</xsl:when>
                                <xsl:when test="$TC=''"></xsl:when>
                                <xsl:otherwise>
                                  <span>
                                    <xsl:value-of select="TipoCassa"/>(codice non previsto)</span>
                                </xsl:otherwise>
                              </xsl:choose>
                            </td>
                            <td class="aDx">
                              <xsl:value-of select="format-number(AlCassa, '#.##0,00', 'euro')" />%</td>
                            <td class="aDx">
                              <xsl:value-of select="format-number(ImportoContributoCassa, '#.##0,00', 'euro')" />
                            </td>
                            <xsl:if test="$hasImponibileCassa &gt; 0">
                              <td class="aDx">
                                <xsl:value-of select="format-number(ImponibileCassa, '#.##0,00', 'euro')" />
                              </td>
                            </xsl:if>
                            <td class="aDx">
                              <xsl:value-of select="format-number(AliquotaIVA, '#.##0,00', 'euro')" />%</td>
                            <xsl:if test="$hasRitenuta &gt; 0">
                              <td>
                                <xsl:value-of select="normalize-space(Ritenuta)" />
                              </td>
                            </xsl:if>
                            <xsl:if test="$hasNatura &gt; 0">
                              <td class="aSx">
                                <xsl:variable name="NT">
                                  <xsl:value-of select="normalize-space(Natura)"/>
                                </xsl:variable>
                                <xsl:choose>
                                  <xsl:when test="$NT='N1'">Escluse ex art. 15</xsl:when>
                                  <xsl:when test="$NT='N2'">Non soggette</xsl:when>
                                  <xsl:when test="$NT='N3'">Non imponibili</xsl:when>
                                  <xsl:when test="$NT='N4'">Esenti</xsl:when>
                                  <xsl:when test="$NT='N5'">Regime del margine</xsl:when>
                                  <xsl:when test="$NT='N6'">Inversione contabile(reverse charge)</xsl:when>
                                  <xsl:when test="$NT='N7'">IVA assolta in altro stato UE (vendite a distanza ex art. 40 c. 3 e 4 e art. 41 c. 1 lett. b, DL 331/93; prestazione di servizi di telecomunicazioni, tele-radiodiffusione ed elettronici ex art. 7-sexies lett. f, g, art. 74-sexies DPR 633/72)</xsl:when>
                                  <xsl:when test="$NT=''"></xsl:when>
                                  <xsl:otherwise>
                                    <span>
                                      <xsl:value-of select="Natura"/>(codice non previsto)</span>
                                  </xsl:otherwise>
                                </xsl:choose>
                              </td>
                            </xsl:if>
                            <xsl:if test="$hasRiferimentoAmministrazione &gt; 0">
                              <td class="aSx">
                                <xsl:value-of select="RiferimentoAmministrazione" />
                              </td>
                            </xsl:if>
                          </tr>
                        </xsl:for-each>
                      </tbody>
                      <tfoot></tfoot>
                    </table>
                  </div>
                  <div class="separa">
                    <p>&#32;</p>
                  </div>
                </xsl:if>
                <!--FINE DATI DELLA CASSA PREVIDENZIALE-->
                <!--INIZIO DATI SCONTO / MAGGIORAZIONE-->
                <xsl:if test="DatiGenerali/DatiGeneraliDocumento/ScontoMaggiorazione">
                  <!-- variabili per creazione colonne celle -->
                  <xsl:variable name="hasPercentuale">
                    <xsl:value-of select="count(DatiGenerali/DatiGeneraliDocumento/ScontoMaggiorazione/Percentuale)" />
                  </xsl:variable>
                  <xsl:variable name="hasImporto">
                    <xsl:value-of select="count(DatiGenerali/DatiGeneraliDocumento/ScontoMaggiorazione/Importo)" />
                  </xsl:variable>
                  <div class="dati-sconto-maggiorazione">
                    <table class="tableDettagli">
                      <caption>Sconto/maggiorazione</caption>
                      <thead>
                        <tr>
                          <th>Tipologia</th>
                          <xsl:if test="$hasPercentuale &gt; 0">
                            <th>Percentuale</th>
                          </xsl:if>
                          <xsl:if test="$hasImporto &gt; 0">
                            <th>Importo
                              <span>&#32;(<xsl:value-of select="DatiGenerali/DatiGeneraliDocumento/Divisa"/>)</span>
                            </th>
                          </xsl:if>
                        </tr>
                      </thead>
                      <tbody>
                        <xsl:for-each select="DatiGenerali/DatiGeneraliDocumento/ScontoMaggiorazione">
                          <tr>
                            <td class="aSx">
                              <xsl:variable name="TSM">
                                <xsl:value-of select="Tipo"/>
                              </xsl:variable>
                              <xsl:choose>
                                <xsl:when test="$TSM='SC'">Sconto</xsl:when>
                                <xsl:when test="$TSM='MG'">Maggiorazione</xsl:when>
                                <xsl:otherwise>
                                  <span>
                                    <xsl:value-of select="Tipo" />(codice non previsto)</span>
                                </xsl:otherwise>
                              </xsl:choose>
                            </td>
                            <xsl:if test="$hasPercentuale &gt; 0">
                              <td class="aDx">
                                <xsl:value-of select="format-number(Percentuale, '#.##0,00', 'euro')" />
                              </td>
                            </xsl:if>
                            <xsl:if test="$hasImporto &gt; 0">
                              <td class="aDx">
                                <xsl:value-of select="format-number(Importo, '#.##0,00', 'euro')" />
                              </td>
                            </xsl:if>
                          </tr>
                        </xsl:for-each>
                      </tbody>
                      <tfoot></tfoot>
                    </table>
                    <div class="separa">
                      <p>&#32;</p>
                    </div>
                  </div>
                </xsl:if>
                <!--FINE DATI SCONTO / MAGGIORAZIONE-->
                <!--INIZIO DATI GENERALI RAGGRUPPATI -->
                <div class="datiGeneraliRaggrupati">
                  <div class="separa">
                    <p>&#32;</p>
                  </div>
                  <!-- variabili per capire se devo creare le celle -->
                  <xsl:variable name="hasValueAcquistoRiferimentoNumeroLinea">
                    <xsl:value-of select="count(DatiGenerali/DatiOrdineAcquisto/RiferimentoNumeroLinea)"/>
                  </xsl:variable>
                  <xsl:variable name="hasValueAcquistoIdDocumento">
                    <xsl:value-of select="count(DatiGenerali/DatiOrdineAcquisto/IdDocumento)"/>
                  </xsl:variable>
                  <xsl:variable name="hasValueAcquistoData">
                    <xsl:value-of select="count(DatiGenerali/DatiOrdineAcquisto/Data)"/>
                  </xsl:variable>
                  <xsl:variable name="hasValueAcquistoNumItem">
                    <xsl:value-of select="count(DatiGenerali/DatiOrdineAcquisto/NumItem)"/>
                  </xsl:variable>
                  <xsl:variable name="hasValueAcquistoCodiceCommessaConvenzione">
                    <xsl:value-of select="count(DatiGenerali/DatiOrdineAcquisto/CodiceCommessaConvenzione)"/>
                  </xsl:variable>
                  <xsl:variable name="hasValueAcquistoCodiceCUP">
                    <xsl:value-of select="count(DatiGenerali/DatiOrdineAcquisto/CodiceCUP)"/>
                  </xsl:variable>
                  <xsl:variable name="hasValueAcquistoCodiceCIG">
                    <xsl:value-of select="count(DatiGenerali/DatiOrdineAcquisto/CodiceCIG)"/>
                  </xsl:variable>
                  <xsl:variable name="hasValueContrattoRiferimentoNumeroLinea">
                    <xsl:value-of select="count(DatiGenerali/DatiContratto/RiferimentoNumeroLinea)"/>
                  </xsl:variable>
                  <xsl:variable name="hasValueContrattoIdDocumento">
                    <xsl:value-of select="count(DatiGenerali/DatiContratto/IdDocumento)"/>
                  </xsl:variable>
                  <xsl:variable name="hasValueContrattoData">
                    <xsl:value-of select="count(DatiGenerali/DatiContratto/Data)"/>
                  </xsl:variable>
                  <xsl:variable name="hasValueContrattoNumItem">
                    <xsl:value-of select="count(DatiGenerali/DatiContratto/NumItem)"/>
                  </xsl:variable>
                  <xsl:variable name="hasValueContrattoCodiceCommessaConvenzione">
                    <xsl:value-of select="count(DatiGenerali/DatiContratto/CodiceCommessaConvenzione)"/>
                  </xsl:variable>
                  <xsl:variable name="hasValueContrattoCodiceCUP">
                    <xsl:value-of select="count(DatiGenerali/DatiContratto/CodiceCUP)"/>
                  </xsl:variable>
                  <xsl:variable name="hasValueContrattoCodiceCIG">
                    <xsl:value-of select="count(DatiGenerali/DatiContratto/CodiceCIG)"/>
                  </xsl:variable>
                  <xsl:variable name="hasValueConvenzioneRiferimentoNumeroLinea">
                    <xsl:value-of select="count(DatiGenerali/DatiConvenzione/RiferimentoNumeroLinea)"/>
                  </xsl:variable>
                  <xsl:variable name="hasValueConvenzioneIdDocumento">
                    <xsl:value-of select="count(DatiGenerali/DatiConvenzione/IdDocumento)"/>
                  </xsl:variable>
                  <xsl:variable name="hasValueConvenzioneData">
                    <xsl:value-of select="count(DatiGenerali/DatiConvenzione/Data)"/>
                  </xsl:variable>
                  <xsl:variable name="hasValueConvenzioneNumItem">
                    <xsl:value-of select="count(DatiGenerali/DatiConvenzione/NumItem)"/>
                  </xsl:variable>
                  <xsl:variable name="hasValueConvenzioneCodiceCommessaConvenzione">
                    <xsl:value-of select="count(DatiGenerali/DatiConvenzione/CodiceCommessaConvenzione)"/>
                  </xsl:variable>
                  <xsl:variable name="hasValueConvenzioneCodiceCUP">
                    <xsl:value-of select="count(DatiGenerali/DatiConvenzione/CodiceCUP)"/>
                  </xsl:variable>
                  <xsl:variable name="hasValueConvenzioneCodiceCIG">
                    <xsl:value-of select="count(DatiGenerali/DatiConvenzione/CodiceCIG)"/>
                  </xsl:variable>
                  <xsl:variable name="hasValueRicezioneRiferimentoNumeroLinea">
                    <xsl:value-of select="count(DatiGenerali/DatiRicezione/RiferimentoNumeroLinea)"/>
                  </xsl:variable>
                  <xsl:variable name="hasValueRicezioneIdDocumento">
                    <xsl:value-of select="count(DatiGenerali/DatiRicezione/IdDocumento)"/>
                  </xsl:variable>
                  <xsl:variable name="hasValueRicezioneData">
                    <xsl:value-of select="count(DatiGenerali/DatiRicezione/Data)"/>
                  </xsl:variable>
                  <xsl:variable name="hasValueRicezioneNumItem">
                    <xsl:value-of select="count(DatiGenerali/DatiRicezione/NumItem)"/>
                  </xsl:variable>
                  <xsl:variable name="hasValueRicezioneCodiceCommessaConvenzione">
                    <xsl:value-of select="count(DatiGenerali/DatiRicezione/CodiceCommessaConvenzione)"/>
                  </xsl:variable>
                  <xsl:variable name="hasValueRicezioneCodiceCUP">
                    <xsl:value-of select="count(DatiGenerali/DatiRicezione/CodiceCUP)"/>
                  </xsl:variable>
                  <xsl:variable name="hasValueRicezioneCodiceCIG">
                    <xsl:value-of select="count(DatiGenerali/DatiRicezione/CodiceCIG)"/>
                  </xsl:variable>
                  <xsl:variable name="hasValueFattureCollegateRiferimentoNumeroLinea">
                    <xsl:value-of select="count(DatiGenerali/DatiFattureCollegate/RiferimentoNumeroLinea)"/>
                  </xsl:variable>
                  <xsl:variable name="hasValueFattureCollegateIdDocumento">
                    <xsl:value-of select="count(DatiGenerali/DatiFattureCollegate/IdDocumento)"/>
                  </xsl:variable>
                  <xsl:variable name="hasValueFattureCollegateData">
                    <xsl:value-of select="count(DatiGenerali/DatiFattureCollegate/Data)"/>
                  </xsl:variable>
                  <xsl:variable name="hasValueFattureCollegateNumItem">
                    <xsl:value-of select="count(DatiGenerali/DatiFattureCollegate/NumItem)"/>
                  </xsl:variable>
                  <xsl:variable name="hasValueFattureCollegateCodiceCommessaConvenzione">
                    <xsl:value-of select="count(DatiGenerali/DatiFattureCollegate/CodiceCommessaConvenzione)"/>
                  </xsl:variable>
                  <xsl:variable name="hasValueFattureCollegateCodiceCUP">
                    <xsl:value-of select="count(DatiGenerali/DatiFattureCollegate/CodiceCUP)"/>
                  </xsl:variable>
                  <xsl:variable name="hasValueFattureCollegateCodiceCIG">
                    <xsl:value-of select="count(DatiGenerali/DatiFattureCollegate/CodiceCIG)"/>
                  </xsl:variable>
                  <!-- variabili per capire se devo creare le colonne -->
                  <xsl:variable name="hasValueRiferimentoNumeroLinea">
                    <xsl:value-of select="$hasValueAcquistoRiferimentoNumeroLinea+$hasValueContrattoRiferimentoNumeroLinea+$hasValueConvenzioneRiferimentoNumeroLinea+$hasValueRicezioneRiferimentoNumeroLinea+$hasValueFattureCollegateRiferimentoNumeroLinea"/>
                  </xsl:variable>
                  <xsl:variable name="hasValueIdDocumento">
                    <xsl:value-of select="$hasValueAcquistoIdDocumento+$hasValueContrattoIdDocumento+$hasValueConvenzioneIdDocumento+$hasValueRicezioneIdDocumento+$hasValueFattureCollegateIdDocumento"/>
                  </xsl:variable>
                  <xsl:variable name="hasValueData">
                    <xsl:value-of select="$hasValueAcquistoData+$hasValueContrattoData+$hasValueConvenzioneData+$hasValueRicezioneData+$hasValueFattureCollegateData"/>
                  </xsl:variable>
                  <xsl:variable name="hasValueNumItem">
                    <xsl:value-of select="$hasValueAcquistoNumItem+$hasValueContrattoNumItem+$hasValueConvenzioneNumItem+$hasValueRicezioneNumItem+$hasValueFattureCollegateNumItem"/>
                  </xsl:variable>
                  <xsl:variable name="hasValueCodiceCommessaConvenzione">
                    <xsl:value-of select="$hasValueAcquistoCodiceCommessaConvenzione+$hasValueContrattoCodiceCommessaConvenzione+$hasValueConvenzioneCodiceCommessaConvenzione+$hasValueRicezioneCodiceCommessaConvenzione+$hasValueFattureCollegateCodiceCommessaConvenzione"/>
                  </xsl:variable>
                  <xsl:variable name="hasValueCodiceCUP">
                    <xsl:value-of select="$hasValueAcquistoCodiceCUP+$hasValueContrattoCodiceCUP+$hasValueConvenzioneCodiceCUP+$hasValueRicezioneCodiceCUP+$hasValueFattureCollegateCodiceCUP"/>
                  </xsl:variable>
                  <xsl:variable name="hasValueCodiceCIG">
                    <xsl:value-of select="$hasValueAcquistoCodiceCIG+$hasValueContrattoCodiceCIG+$hasValueConvenzioneCodiceCIG+$hasValueRicezioneCodiceCIG+$hasValueFattureCollegateCodiceCIG"/>
                  </xsl:variable>
                  <xsl:variable name="hasValueRigaTipologia">
                    <xsl:value-of select="$hasValueRiferimentoNumeroLinea+$hasValueIdDocumento+$hasValueData+$hasValueNumItem+$hasValueCodiceCommessaConvenzione+$hasValueCodiceCUP+$hasValueCodiceCIG"/>
                  </xsl:variable>
                  <xsl:if test="$hasValueRigaTipologia&gt;0">
                    <table class="tableDettagli">
                      <caption>Dati generali</caption>
                      <thead>
                        <tr>
                          <xsl:if test="$hasValueRigaTipologia&gt;0">
                            <th>Tipologia</th>
                          </xsl:if>
                          <xsl:if test="$hasValueRiferimentoNumeroLinea&gt;0">
                            <th>Nr. dettaglio doc.</th>
                          </xsl:if>
                          <xsl:if test="$hasValueIdDocumento&gt;0">
                            <th>Documento</th>
                          </xsl:if>
                          <xsl:if test="$hasValueData&gt;0">
                            <th>Data</th>
                          </xsl:if>
                          <xsl:if test="$hasValueNumItem&gt;0">
                            <th>Nr. linea riferita</th>
                          </xsl:if>
                          <xsl:if test="$hasValueCodiceCommessaConvenzione&gt;0">
                            <th>Codice commessa/convenzione</th>
                          </xsl:if>
                          <xsl:if test="$hasValueCodiceCUP&gt;0">
                            <th>CUP</th>
                          </xsl:if>
                          <th>CIG</th>
                        </tr>
                      </thead>
                      <tbody>
                        <xsl:for-each select="DatiGenerali/DatiOrdineAcquisto">
                          <tr>
                            <xsl:if test="(position()) = 1">
                              <td class="tipo">
                                <strong>Ordine d'acquisto</strong>
                              </td>
                            </xsl:if>
                            <xsl:if test="(position()) &gt; 1">
                              <td class="senzaBordo"></td>
                            </xsl:if>
                            <xsl:if test="$hasValueRiferimentoNumeroLinea&gt;0">
                              <td class="aSx">
                                <xsl:for-each select="RiferimentoNumeroLinea">
                                  <xsl:if test="(position()) &gt; 1">,</xsl:if>
                                  <xsl:value-of select="."/>
                                </xsl:for-each>
                              </td>
                            </xsl:if>
                            <xsl:if test="$hasValueIdDocumento&gt;0">
                              <td class="aSx">
                                <xsl:value-of select="IdDocumento"/>
                              </td>
                            </xsl:if>
                            <xsl:if test="$hasValueData&gt;0">
                              <td class="aSx">
                                <xsl:call-template name="dataItaliana">
                                  <xsl:with-param name="data" select="Data"/>
                                </xsl:call-template>
                              </td>
                            </xsl:if>
                            <xsl:if test="$hasValueNumItem&gt;0">
                              <td>
                                <xsl:value-of select="NumItem"/>
                              </td>
                            </xsl:if>
                            <xsl:if test="$hasValueCodiceCommessaConvenzione&gt;0">
                              <td class="aSx">
                                <xsl:value-of select="CodiceCommessaConvenzione"/>
                              </td>
                            </xsl:if>
                            <xsl:if test="$hasValueCodiceCUP&gt;0">
                              <td class="aSx">
                                <xsl:value-of select="CodiceCUP"/>
                              </td>
                            </xsl:if>
                            <td class="aSx">
                              <xsl:value-of select="CodiceCIG"/>
                            </td>
                          </tr>
                        </xsl:for-each>
                        <xsl:for-each select="DatiGenerali/DatiContratto">
                          <tr>
                            <xsl:if test="(position()) = 1">
                              <td class="tipo">
                                <strong>Contratto</strong>
                              </td>
                            </xsl:if>
                            <xsl:if test="(position()) &gt; 1">
                              <td class="senzaBordo"></td>
                            </xsl:if>
                            <xsl:if test="$hasValueRiferimentoNumeroLinea&gt;0">
                              <td class="aSx">
                                <xsl:for-each select="RiferimentoNumeroLinea">
                                  <xsl:if test="(position()) &gt; 1">,</xsl:if>
                                  <xsl:value-of select="."/>
                                </xsl:for-each>
                              </td>
                            </xsl:if>
                            <xsl:if test="$hasValueIdDocumento&gt;0">
                              <td class="aSx">
                                <xsl:value-of select="IdDocumento"/>
                              </td>
                            </xsl:if>
                            <xsl:if test="$hasValueData&gt;0">
                              <td class="aSx">
                                <xsl:call-template name="dataItaliana">
                                  <xsl:with-param name="data" select="Data"/>
                                </xsl:call-template>
                              </td>
                            </xsl:if>
                            <xsl:if test="$hasValueNumItem&gt;0">
                              <td>
                                <xsl:value-of select="NumItem"/>
                              </td>
                            </xsl:if>
                            <xsl:if test="$hasValueCodiceCommessaConvenzione&gt;0">
                              <td class="aSx">
                                <xsl:value-of select="CodiceCommessaConvenzione"/>
                              </td>
                            </xsl:if>
                            <xsl:if test="$hasValueCodiceCUP&gt;0">
                              <td class="aSx">
                                <xsl:value-of select="CodiceCUP"/>
                              </td>
                            </xsl:if>
                            <td class="aSx">
                              <xsl:value-of select="CodiceCIG"/>
                            </td>
                          </tr>
                        </xsl:for-each>
                        <xsl:for-each select="DatiGenerali/DatiConvenzione">
                          <tr>
                            <xsl:if test="(position()) = 1">
                              <td class="tipo">
                                <strong>Convenzione</strong>
                              </td>
                            </xsl:if>
                            <xsl:if test="(position()) &gt; 1">
                              <td class="senzaBordo"></td>
                            </xsl:if>
                            <xsl:if test="$hasValueRiferimentoNumeroLinea&gt;0">
                              <td class="aSx">
                                <xsl:for-each select="RiferimentoNumeroLinea">
                                  <xsl:if test="(position()) &gt; 1">,</xsl:if>
                                  <xsl:value-of select="."/>
                                </xsl:for-each>
                              </td>
                            </xsl:if>
                            <xsl:if test="$hasValueIdDocumento&gt;0">
                              <td class="aSx">
                                <xsl:value-of select="IdDocumento"/>
                              </td>
                            </xsl:if>
                            <xsl:if test="$hasValueData&gt;0">
                              <td class="aSx">
                                <xsl:call-template name="dataItaliana">
                                  <xsl:with-param name="data" select="Data"/>
                                </xsl:call-template>
                              </td>
                            </xsl:if>
                            <xsl:if test="$hasValueNumItem&gt;0">
                              <td>
                                <xsl:value-of select="NumItem"/>
                              </td>
                            </xsl:if>
                            <xsl:if test="$hasValueCodiceCommessaConvenzione&gt;0">
                              <td class="aSx">
                                <xsl:value-of select="CodiceCommessaConvenzione"/>
                              </td>
                            </xsl:if>
                            <xsl:if test="$hasValueCodiceCUP&gt;0">
                              <td class="aSx">
                                <xsl:value-of select="CodiceCUP"/>
                              </td>
                            </xsl:if>
                            <td class="aSx">
                              <xsl:value-of select="CodiceCIG"/>
                            </td>
                          </tr>
                        </xsl:for-each>
                        <xsl:for-each select="DatiGenerali/DatiRicezione">
                          <tr>
                            <xsl:if test="(position()) = 1">
                              <td class="tipo">
                                <strong>Ricezione</strong>
                              </td>
                            </xsl:if>
                            <xsl:if test="(position()) &gt; 1">
                              <td class="senzaBordo"></td>
                            </xsl:if>
                            <xsl:if test="$hasValueRiferimentoNumeroLinea&gt;0">
                              <td class="aSx">
                                <xsl:for-each select="RiferimentoNumeroLinea">
                                  <xsl:if test="(position()) &gt; 1">,</xsl:if>
                                  <xsl:value-of select="."/>
                                </xsl:for-each>
                              </td>
                            </xsl:if>
                            <xsl:if test="$hasValueIdDocumento&gt;0">
                              <td class="aSx">
                                <xsl:value-of select="IdDocumento"/>
                              </td>
                            </xsl:if>
                            <xsl:if test="$hasValueData&gt;0">
                              <td class="aSx">
                                <xsl:call-template name="dataItaliana">
                                  <xsl:with-param name="data" select="Data"/>
                                </xsl:call-template>
                              </td>
                            </xsl:if>
                            <xsl:if test="$hasValueNumItem&gt;0">
                              <td>
                                <xsl:value-of select="NumItem"/>
                              </td>
                            </xsl:if>
                            <xsl:if test="$hasValueCodiceCommessaConvenzione&gt;0">
                              <td class="aSx">
                                <xsl:value-of select="CodiceCommessaConvenzione"/>
                              </td>
                            </xsl:if>
                            <xsl:if test="$hasValueCodiceCUP&gt;0">
                              <td class="aSx">
                                <xsl:value-of select="CodiceCUP"/>
                              </td>
                            </xsl:if>
                            <td class="aSx">
                              <xsl:value-of select="CodiceCIG"/>
                            </td>
                          </tr>
                        </xsl:for-each>
                        <xsl:for-each select="DatiGenerali/DatiFattureCollegate">
                          <tr>
                            <xsl:if test="(position()) = 1">
                              <td class="tipo">
                                <strong>Fatture collegate</strong>
                              </td>
                            </xsl:if>
                            <xsl:if test="(position()) &gt; 1">
                              <td class="senzaBordo"></td>
                            </xsl:if>
                            <xsl:if test="$hasValueRiferimentoNumeroLinea&gt;0">
                              <td class="aSx">
                                <xsl:for-each select="RiferimentoNumeroLinea">
                                  <xsl:if test="(position()) &gt; 1">,</xsl:if>
                                  <xsl:value-of select="."/>
                                </xsl:for-each>
                              </td>
                            </xsl:if>
                            <xsl:if test="$hasValueIdDocumento&gt;0">
                              <td class="aSx">
                                <xsl:value-of select="IdDocumento"/>
                              </td>
                            </xsl:if>
                            <xsl:if test="$hasValueData&gt;0">
                              <td class="aSx">
                                <xsl:call-template name="dataItaliana">
                                  <xsl:with-param name="data" select="Data"/>
                                </xsl:call-template>
                              </td>
                            </xsl:if>
                            <xsl:if test="$hasValueNumItem&gt;0">
                              <td>
                                <xsl:value-of select="NumItem"/>
                              </td>
                            </xsl:if>
                            <xsl:if test="$hasValueCodiceCommessaConvenzione&gt;0">
                              <td class="aSx">
                                <xsl:value-of select="CodiceCommessaConvenzione"/>
                              </td>
                            </xsl:if>
                            <xsl:if test="$hasValueCodiceCUP&gt;0">
                              <td class="aSx">
                                <xsl:value-of select="CodiceCUP"/>
                              </td>
                            </xsl:if>
                            <td class="aSx">
                              <xsl:value-of select="CodiceCIG"/>
                            </td>
                          </tr>
                        </xsl:for-each>
                      </tbody>
                      <tfoot></tfoot>
                    </table>
                  </xsl:if>
                  <div class="separa">
                    <p>&#32;</p>
                  </div>
                </div>
                <!--FINE DATI GENERALI RAGGRUPPATI -->
                <!--INIZIO DATI RIFERIMENTO SAL-->
                <xsl:if test="DatiGenerali/DatiSAL">
                  <div class="separa">
                    <p>&#32;</p>
                  </div>
                  <div class="dati-sal">
                    <p class="info">
                      <strong>Stato avanzamento lavori:</strong>
                      <xsl:if test="DatiGenerali/DatiSAL/RiferimentoFase">
                        <span>Numero fase avanzamento:
                          <xsl:for-each select="DatiGenerali/DatiSAL/RiferimentoFase">
                            <span>
                              <xsl:if test="(position()) &gt; 1">,</xsl:if>
                              <xsl:value-of select="."/>
                            </span>
                          </xsl:for-each>
                        </span>
                        <br />
                      </xsl:if>
                    </p>
                  </div>
                  <div class="separa">
                    <p>&#32;</p>
                  </div>
                </xsl:if>
                <!--FINE DATI RIFERIMENTO SAL-->
                <!--INIZIO DATI DDT-->
                <xsl:if test="DatiGenerali/DatiDDT">
                  <xsl:variable name="hasDDTRiferimentoNumeroLinea">
                    <xsl:value-of select="count(DatiGenerali/DatiDDT/RiferimentoNumeroLinea)" />
                  </xsl:variable>
                  <div class="dati-ddt">
                    <table class="tableDettagli">
                      <caption>Documento di trasporto (DDT)</caption>
                      <thead>
                        <tr>
                          <th>Numero DDT</th>
                          <th>Data DDT</th>
                          <xsl:if test="$hasDDTRiferimentoNumeroLinea &gt; 0">
                            <th>Numero linea di fattura</th>
                          </xsl:if>
                        </tr>
                      </thead>
                      <tbody>
                        <xsl:for-each select="DatiGenerali/DatiDDT">
                          <tr>
                            <td>
                              <xsl:value-of select="NumeroDDT"/>
                            </td>
                            <td>
                              <xsl:call-template name="dataItaliana">
                                <xsl:with-param name="data" select="DataDDT"/>
                              </xsl:call-template>
                            </td>
                            <xsl:if test="$hasDDTRiferimentoNumeroLinea &gt; 0">
                              <td class="aSx">
                                <xsl:for-each select="RiferimentoNumeroLinea">
                                  <span>
                                    <xsl:if test="(position()) &gt; 1">,</xsl:if>
                                    <xsl:value-of select="."/>
                                  </span>
                                </xsl:for-each>
                              </td>
                            </xsl:if>
                          </tr>
                        </xsl:for-each>
                      </tbody>
                      <tfoot></tfoot>
                    </table>
                  </div>
                  <div class="separa">
                    <p>&#32;</p>
                  </div>
                </xsl:if>
                <!--FINE DATI DDT-->
                <!--INIZIO DATI TRASPORTO-->
                <xsl:if test="DatiGenerali/DatiTrasporto">
                  <div class="dati-trasporto">
                    <h4 class="titoloSx">Trasporto</h4>
                    <div class="dati-trasporto srBox mittente">
                      <xsl:if test="DatiGenerali/DatiTrasporto/DatiAnagraficiVettore">
                        <xsl:variable name="VETTORE">
                          <xsl:value-of select="concat(string(DatiGenerali/DatiTrasporto/DatiAnagraficiVettore/Anagrafica/Nome), ' ', string(DatiGenerali/DatiTrasporto/DatiAnagraficiVettore/Anagrafica/Cognome))" />
                        </xsl:variable>
                        <p class="info">
                          <strong>Vettore</strong>:
                          <xsl:choose>
                            <xsl:when test="string-length(normalize-space($VETTORE)) &gt; 0">
                              <xsl:value-of select="normalize-space(concat(string(DatiGenerali/DatiTrasporto/DatiAnagraficiVettore/Anagrafica/Titolo), ' ', $VETTORE))" />
                            </xsl:when>
                            <xsl:otherwise>
                              <xsl:value-of select="normalize-space(concat(string(DatiGenerali/DatiTrasporto/DatiAnagraficiVettore/Anagrafica/Titolo), ' ', DatiGenerali/DatiTrasporto/DatiAnagraficiVettore/Anagrafica/Denominazione))"/>
                            </xsl:otherwise>
                          </xsl:choose>
                          <br />
                          <xsl:for-each select="DatiGenerali/DatiTrasporto/DatiAnagraficiVettore">
                            <xsl:if test="IdFiscaleIVA/IdPaese">
                              <span>Identificativo fiscale ai fini IVA:
                                <span>
                                  <xsl:value-of select="IdFiscaleIVA/IdPaese"/>
                                  <xsl:value-of select="IdFiscaleIVA/IdCodice"/>
                                </span>
                              </span>
                              <br />
                            </xsl:if>
                            <xsl:if test="CodiceFiscale">
                              <span>Codice Fiscale:
                                <span>
                                  <xsl:value-of select="CodiceFiscale"/>
                                </span>
                              </span>
                              <br />
                            </xsl:if>
                            <xsl:if test="string-length(normalize-space($VETTORE)) &gt; 0">
                              <xsl:if test="Anagrafica/Denominazione">
                                <span>Denominazione:
                                  <span>
                                    <xsl:value-of select="Anagrafica/Denominazione"/>
                                  </span>
                                </span>
                                <br />
                              </xsl:if>
							  <xsl:if test="Anagrafica/Nome">
								  <span>Nominativo:&#32;
									<span><xsl:value-of select="normalize-space(concat(string(Anagrafica/Nome), ' ', string(Anagrafica/Cognome)))"/></span>
								  </span>
								  <br />
								</xsl:if>
                            </xsl:if>
                            <xsl:if test="Anagrafica/CodEORI">
                              <span>Codice EORI:
                                <span>
                                  <xsl:value-of select="Anagrafica/CodEORI"/>
                                </span>
                              </span>
                              <br />
                            </xsl:if>
                            <xsl:if test="NumeroLicenzaGuida">
                              <span>Numero licenza di guida:
                                <span>
                                  <xsl:value-of select="NumeroLicenzaGuida"/>
                                </span>
                              </span>
                            </xsl:if>
                          </xsl:for-each>
                        </p>
                      </xsl:if>
                    </div>
                    <div class="dati-trasporto srBox cessionario">
                      <xsl:if test="DatiGenerali/DatiTrasporto/MezzoTrasporto or DatiGenerali/DatiTrasporto/CausaleTrasporto or DatiGenerali/DatiTrasporto/NumeroColli or DatiGenerali/DatiTrasporto/Descrizione or DatiGenerali/DatiTrasporto/UnitaMisuraPeso or DatiGenerali/DatiTrasporto/PesoLordo or DatiGenerali/DatiTrasporto/PesoNetto or DatiGenerali/DatiTrasporto/DataOraRitiro or DatiGenerali/DatiTrasporto/DataInizioTrasporto or DatiGenerali/DatiTrasporto/TipoResa or DatiGenerali/DatiTrasporto/IndirizzoResa">
                        <p class="info">
                          <strong>Altri dati:</strong>
                          <br />
                          <xsl:for-each select="DatiGenerali/DatiTrasporto">
                            <xsl:if test="MezzoTrasporto">
                              <span>Mezzo di trasporto:
                                <span>
                                  <xsl:value-of select="MezzoTrasporto"/>
                                </span>
                              </span>
                              <br />
                            </xsl:if>
                            <xsl:if test="CausaleTrasporto">
                              <span>Causale trasporto:
                                <span>
                                  <xsl:value-of select="CausaleTrasporto"/>
                                </span>
                              </span>
                              <br />
                            </xsl:if>
                            <xsl:if test="NumeroColli">
                              <span>Numero colli trasportati:
                                <span>
                                  <xsl:value-of select="NumeroColli"/>
                                </span>
                              </span>
                              <br />
                            </xsl:if>
                            <xsl:if test="Descrizione">
                              <span>Descrizione beni trasportati:
                                <span>
                                  <xsl:value-of select="Descrizione"/>
                                </span>
                              </span>
                              <br />
                            </xsl:if>
                            <xsl:if test="PesoLordo or PesoNetto">
                              <span>Peso:</span>
                              <xsl:if test="PesoLordo">
                                <span>Lordo
                                  <xsl:value-of select="format-number(PesoLordo, '#.##0,00', 'euro')"/>
                                  <xsl:if test="UnitaMisuraPeso">
                                    <span>(
                                      <xsl:value-of select="UnitaMisuraPeso"/>)</span>
                                  </xsl:if>
                                </span>
                              </xsl:if>
                              <xsl:if test="PesoNetto">
                                <span>Netto
                                  <xsl:value-of select="format-number(PesoNetto, '#.##0,00', 'euro')"/>
                                  <xsl:if test="UnitaMisuraPeso">
                                    <span>(
                                      <xsl:value-of select="UnitaMisuraPeso"/>)</span>
                                  </xsl:if>
                                </span>
                              </xsl:if>
                              <br />
                            </xsl:if>
                            <xsl:if test="DataOraRitiro">
                              <span>Ritiro merce:
                                <span>
                                  <xsl:call-template name="dataItaliana">
                                    <xsl:with-param name="data" select="DataOraRitiro"/>
                                  </xsl:call-template>
                                </span>
                              </span>
                              <br />
                            </xsl:if>
                            <xsl:if test="DataInizioTrasporto">
                              <span>Inizio trasporto:
                                <span>
                                  <xsl:call-template name="dataItaliana">
                                    <xsl:with-param name="data" select="DataInizioTrasporto"/>
                                  </xsl:call-template>
                                </span>
                              </span>
                              <br />
                            </xsl:if>
                            <xsl:if test="TipoResa">
                              <span>Tipologia di resa:
                                <span>
                                  <xsl:value-of select="TipoResa"/>
                                </span>(codifica secondo standard ICC)</span>
                              <br />
                            </xsl:if>
                            <xsl:if test="IndirizzoResa">
                              <xsl:variable name="RESACIVICO">
                                <xsl:if test="string-length(normalize-space(IndirizzoResa/NumeroCivico))&gt;0">
                                  <xsl:value-of select="concat(',',normalize-space(IndirizzoResa/NumeroCivico))" />
                                </xsl:if>
                              </xsl:variable>
                              <xsl:variable name="RESAPROV">
                                <xsl:if test="string-length(normalize-space(IndirizzoResa/Provincia))&gt;0">
                                  <xsl:value-of select="concat('(',normalize-space(IndirizzoResa/Provincia), ')')" />
                                </xsl:if>
                              </xsl:variable>
                              <xsl:variable name="resacap">
                                <xsl:if test="string-length(normalize-space(IndirizzoResa/CAP))&gt;0">
                                  <xsl:value-of select="concat('- ',normalize-space(IndirizzoResa/CAP), ' -')" />
                                </xsl:if>
                              </xsl:variable>
                              <span>Indirizzo di resa:</span>
                              <span>
                                <xsl:value-of select="normalize-space(concat(IndirizzoResa/Indirizzo, $RESACIVICO, ' ', $resacap, ' ', IndirizzoResa/Comune, ' ', $RESAPROV, ' ', IndirizzoResa/Nazione))" />
                              </span>
                              <br />
                            </xsl:if>
                          </xsl:for-each>
                        </p>
                      </xsl:if>
                    </div>
                    <div class="separa">
                      <p>&#32;</p>
                    </div>
                  </div>
                </xsl:if>
                <!--FINE DATI TRASPORTO-->
                <!--INIZIO FATTURA PRINCIPALE-->
                <xsl:if test="DatiGenerali/FatturaPrincipale/NumeroFatturaPrincipale">
                  <div class="fattura-principale srBox cessionario">
                    <p class="info">
                      <strong>Dati relativi alla fattura principale</strong>
                      <br />
                      <xsl:if test="DatiGenerali/FatturaPrincipale/NumeroFatturaPrincipale">
                        <span>Numero fattura principale:
                          <span>
                            <xsl:value-of select="DatiGenerali/FatturaPrincipale/NumeroFatturaPrincipale"/>
                          </span>
                        </span>
                        <br />
                      </xsl:if>
                      <xsl:if test="DatiGenerali/FatturaPrincipale/DataFatturaPrincipale">
                        <span>Data fattura principale:
                          <span>
                            <xsl:value-of select="DatiGenerali/FatturaPrincipale/DataFatturaPrincipale"/>
                          </span>
                          <xsl:call-template name="dataItaliana">
                            <xsl:with-param name="data" select="DatiGenerali/FatturaPrincipale/DataFatturaPrincipale"/>
                          </xsl:call-template>
                        </span>
                      </xsl:if>
                    </p>
                  </div>
                </xsl:if>
                <!--FINE FATTURA PRINCIPALE-->
                <xsl:if test="DatiGenerali/FatturaPrincipale/NumeroFatturaPrincipale">
                  <div class="separa">
                    <p>&#32;</p>
                  </div>
                </xsl:if>
              </div>
              <!-- fine riassunto -->
              <div class="separa">
                <p>&#32;</p>
              </div>
              <!--INIZIO DATI DI RIEPILOGO ALIQUOTE E NATURE-->
              <xsl:if test="DatiBeniServizi/DatiRiepilogo">
                <div class="riepilogo-aliquote-nature">
                  <!-- Variabili per costruzione colonne e celle -->
                  <xsl:variable name="hasNatura">
                    <xsl:value-of select="count(DatiBeniServizi/DatiRiepilogo/Natura)" />
                  </xsl:variable>
                  <xsl:variable name="hasSpeseAccessorie">
                    <xsl:value-of select="count(DatiBeniServizi/DatiRiepilogo/SpeseAccessorie)" />
                  </xsl:variable>
                  <xsl:variable name="hasArrotondamento">
                    <xsl:value-of select="count(DatiBeniServizi/DatiRiepilogo/Arrotondamento)" />
                  </xsl:variable>
                  <xsl:variable name="hasEsigibilitaIVA">
                    <xsl:value-of select="count(DatiBeniServizi/DatiRiepilogo/EsigibilitaIVA)" />
                  </xsl:variable>
                  <xsl:variable name="hasRiferimentoNormativo">
                    <xsl:value-of select="count(DatiBeniServizi/DatiRiepilogo/RiferimentoNormativo)" />
                  </xsl:variable>
                  <table class="tableDettagli">
                    <caption>Dati di riepilogo per aliquota IVA e natura</caption>
                    <thead>
                      <tr>
                        <th>IVA</th>
                        <xsl:if test="$hasNatura &gt; 0">
                          <th>Natura op.</th>
                        </xsl:if>
                        <xsl:if test="$hasSpeseAccessorie &gt; 0">
                          <th>Spese acc.
                            <span>&#32;(<xsl:value-of select="DatiGenerali/DatiGeneraliDocumento/Divisa"/>)</span>
                          </th>
                        </xsl:if>
                        <xsl:if test="$hasArrotondamento &gt; 0">
                          <th>Arr.
                            <span>&#32;(<xsl:value-of select="DatiGenerali/DatiGeneraliDocumento/Divisa"/>)</span>
                          </th>
                        </xsl:if>
                        <th>Impon./Importo
                          <span>&#32;(<xsl:value-of select="DatiGenerali/DatiGeneraliDocumento/Divisa"/>)</span>
                        </th>
                        <th>Imposta
                          <span>&#32;(<xsl:value-of select="DatiGenerali/DatiGeneraliDocumento/Divisa"/>)</span>
                        </th>
                        <xsl:if test="$hasEsigibilitaIVA &gt; 0">
                          <th>Esigibilit&#224;</th>
                        </xsl:if>
                        <xsl:if test="$hasRiferimentoNormativo &gt; 0">
                          <th>Rif. normativo</th>
                        </xsl:if>
                      </tr>
                    </thead>
                    <tbody>
                      <xsl:for-each select="DatiBeniServizi/DatiRiepilogo">
                        <tr>
                          <td class="aDx">
                            <xsl:value-of select="AliquotaIVA" />%</td>
                          <xsl:if test="$hasNatura &gt; 0">
                            <td class="aSx">
                              <xsl:variable name="NAT1">
                                <xsl:value-of select="normalize-space(Natura)"/>
                              </xsl:variable>
                              <xsl:if test="Natura">
                                <xsl:choose>
                                  <xsl:when test="$NAT1='N1'">Escluse ex art.15</xsl:when>
                                  <xsl:when test="$NAT1='N2'">Non soggette</xsl:when>
                                  <xsl:when test="$NAT1='N3'">Non imponibili</xsl:when>
                                  <xsl:when test="$NAT1='N4'">Esenti</xsl:when>
                                  <xsl:when test="$NAT1='N5'">Regime del margine</xsl:when>
                                  <xsl:when test="$NAT1='N6'">Inversione contabile(reverse charge)</xsl:when>
                                  <xsl:when test="$NAT1='N7'">IVA assolta in altro stato UE (vendite a distanza ex art. 40 c. 3 e 4 e art. 41 c. 1 lett. b, DL 331/93; prestazione di servizi di telecomunicazioni, tele-radiodiffusione ed elettronici ex art. 7-sexies lett. f, g, art. 74-sexies DPR 633/72)</xsl:when>
                                  <xsl:when test="$NAT1=''"></xsl:when>
                                  <xsl:otherwise>
                                    <span>
                                      <xsl:value-of select="Natura" />(codice non previsto)</span>
                                  </xsl:otherwise>
                                </xsl:choose>
                              </xsl:if>
                            </td>
                          </xsl:if>
                          <xsl:if test="$hasSpeseAccessorie &gt; 0">
                            <td class="aDx">
                              <xsl:value-of select="format-number(SpeseAccessorie, '#.##0,00', 'euro')"/>
                            </td>
                          </xsl:if>
                          <xsl:if test="$hasArrotondamento &gt; 0">
                            <td class="aDx">
                              <xsl:value-of select="format-number(Arrotondamento, '#.##0,00', 'euro')"/>
                            </td>
                          </xsl:if>
                          <td class="aDx">
                            <xsl:value-of select="format-number(ImponibileImporto, '#.##0,00', 'euro')" />
                          </td>
                          <td class="aDx">
                            <xsl:value-of select="format-number(Imposta, '#.##0,00', 'euro')" />
                          </td>
                          <xsl:if test="$hasEsigibilitaIVA &gt; 0">
                            <td class="aSx">
                              <xsl:variable name="EI">
                                <xsl:value-of select="EsigibilitaIVA"/>
                              </xsl:variable>
                              <xsl:if test="EsigibilitaIVA">
                                <xsl:choose>
                                  <xsl:when test="$EI='I'">Immediata</xsl:when>
                                  <xsl:when test="$EI='D'">Differita</xsl:when>
                                  <xsl:when test="$EI='S'">
                                    <span class="evidenzia">Scissione dei pagamenti</span>
                                  </xsl:when>
                                  <xsl:otherwise>
                                    <span>
                                      <xsl:value-of select="EsigibilitaIVA" />(codice non previsto)</span>
                                  </xsl:otherwise>
                                </xsl:choose>
                              </xsl:if>
                            </td>
                          </xsl:if>
                          <xsl:if test="$hasRiferimentoNormativo &gt; 0">
                            <td class="aSx">
                              <xsl:value-of select="RiferimentoNormativo"/>
                            </td>
                          </xsl:if>
                        </tr>
                      </xsl:for-each>
                    </tbody>
                    <tfoot></tfoot>
                  </table>
                  <div class="separa">
                    <p>&#32;</p>
                  </div>
                </div>
              </xsl:if>
              <!--FINE DATI RIEPILOGO ALIQUOTE E NATURE-->
              <!--INIZIO DATI VEICOLI-->
              <xsl:if test="DatiVeicoli">
                <div class="dati-veicoli">
                  <p class="info">
                    <strong>Dati Veicoli ex art. 38 dl 331/1993</strong>
                    <br />
                    <xsl:for-each select="DatiVeicoli">
                      <xsl:if test="Data">
                        <span>Data prima immatricolazione/iscrizione PR:
                          <span>
                            <xsl:value-of select="Data"/>
                          </span>
                          <xsl:call-template name="dataItaliana">
                            <xsl:with-param name="data" select="Data"/>
                          </xsl:call-template>
                        </span>
                        <br />
                      </xsl:if>
                      <xsl:if test="TotalePercorso">
                        <span>Totale percorso:
                          <span>
                            <xsl:value-of select="format-number(TotalePercorso, '#.##0,00', 'euro')"/>
                          </span>
                        </span>
                        <br />
                      </xsl:if>
                    </xsl:for-each>
                  </p>
                </div>
                <div class="separa">
                  <p>&#32;</p>
                </div>
              </xsl:if>
              <!--FINE DATI VEICOLI-->
              <!--INIZIO DATI PAGAMENTO-->
              <xsl:if test="DatiPagamento">
                <div class="datiPagamentoCondizioni">
                  <h4 class="titoloSx">Pagamento</h4>
                  <xsl:for-each select="DatiPagamento">
                    <!-- variabili per colonne e celle -->
                    <xsl:variable name="hasBeneficiario">
                      <xsl:value-of select="count(DettaglioPagamento/Beneficiario)" />
                    </xsl:variable>
                    <xsl:variable name="hasDataRiferimentoTerminiPagamento">
                      <xsl:value-of select="count(DettaglioPagamento/DataRiferimentoTerminiPagamento)" />
                    </xsl:variable>
                    <xsl:variable name="hasGiorniTerminiPagamento">
                      <xsl:value-of select="count(DettaglioPagamento/GiorniTerminiPagamento)" />
                    </xsl:variable>
                    <xsl:variable name="hasDataScadenzaPagamento">
                      <xsl:value-of select="count(DettaglioPagamento/DataScadenzaPagamento)" />
                    </xsl:variable>
                    <xsl:variable name="hasCodUfficioPostale">
                      <xsl:value-of select="count(DettaglioPagamento/CodUfficioPostale)" />
                    </xsl:variable>
                    <xsl:variable name="hasCognomeQuietanzante">
                      <xsl:value-of select="count(DettaglioPagamento/CognomeQuietanzante)" />
                    </xsl:variable>
                    <xsl:variable name="hasNomeQuietanzante">
                      <xsl:value-of select="count(DettaglioPagamento/NomeQuietanzante)" />
                    </xsl:variable>
                    <xsl:variable name="hasCFQuietanzante">
                      <xsl:value-of select="count(DettaglioPagamento/CFQuietanzante)" />
                    </xsl:variable>
                    <xsl:variable name="hasTitoloQuietanzante">
                      <xsl:value-of select="count(DettaglioPagamento/TitoloQuietanzante)" />
                    </xsl:variable>
                    <xsl:variable name="hasIstitutoFinanziario">
                      <xsl:value-of select="count(DettaglioPagamento/IstitutoFinanziario)" />
                    </xsl:variable>
                    <xsl:variable name="hasIBAN">
                      <xsl:value-of select="count(DettaglioPagamento/IBAN)" />
                    </xsl:variable>
                    <xsl:variable name="hasABI">
                      <xsl:value-of select="count(DettaglioPagamento/ABI)" />
                    </xsl:variable>
                    <xsl:variable name="hasCAB">
                      <xsl:value-of select="count(DettaglioPagamento/CAB)" />
                    </xsl:variable>
                    <xsl:variable name="hasBIC">
                      <xsl:value-of select="count(DettaglioPagamento/BIC)" />
                    </xsl:variable>
                    <xsl:variable name="hasScontoPagamentoAnticipato">
                      <xsl:value-of select="count(DettaglioPagamento/ScontoPagamentoAnticipato)" />
                    </xsl:variable>
                    <xsl:variable name="hasDataLimitePagamentoAnticipato">
                      <xsl:value-of select="count(DettaglioPagamento/DataLimitePagamentoAnticipato)" />
                    </xsl:variable>
                    <xsl:variable name="hasPenalitaPagamentiRitardati">
                      <xsl:value-of select="count(DettaglioPagamento/PenalitaPagamentiRitardati)" />
                    </xsl:variable>
                    <xsl:variable name="hasDataDecorrenzaPenale">
                      <xsl:value-of select="count(DettaglioPagamento/DataDecorrenzaPenale)" />
                    </xsl:variable>
                    <xsl:variable name="hasCodicePagamento">
                      <xsl:value-of select="count(DettaglioPagamento/CodicePagamento)" />
                    </xsl:variable>
                    <xsl:variable name="hasQuietanzante">
                      <xsl:value-of select="$hasTitoloQuietanzante + $hasCognomeQuietanzante + $hasNomeQuietanzante" />
                    </xsl:variable>
                    <table class="tableDettagli">
                      <caption>
                        <xsl:if test="CondizioniPagamento">
                          <xsl:variable name="CP">
                            <xsl:value-of select="CondizioniPagamento"/>
                          </xsl:variable>
                          <xsl:choose>
                            <xsl:when test="$CP='TP01'">Pagamento a rate</xsl:when>
                            <xsl:when test="$CP='TP02'">Pagamento completo</xsl:when>
                            <xsl:when test="$CP='TP03'">Anticipo</xsl:when>
                            <xsl:when test="$CP=''"></xsl:when>
                            <xsl:otherwise>
                              <span>
                                <xsl:value-of select="CondizioniPagamento" />(codice non previsto)</span>
                            </xsl:otherwise>
                          </xsl:choose>
                        </xsl:if>
                      </caption>
                      <thead>
                        <tr>
                          <th>Modalit&#224;</th>
                          <th>Importo
                            <xsl:if test="../DatiGenerali/DatiGeneraliDocumento/Divisa">
                              <span>&#32;(<xsl:value-of select="../DatiGenerali/DatiGeneraliDocumento/Divisa"/>)</span>
                            </xsl:if>
                          </th>
                          <xsl:if test="$hasBeneficiario &gt; 0">
                            <th>Beneficiario</th>
                          </xsl:if>
                          <xsl:if test="$hasDataRiferimentoTerminiPagamento &gt; 0">
                            <th>dal</th>
                          </xsl:if>
                          <xsl:if test="$hasGiorniTerminiPagamento &gt; 0">
                            <th>in (gg)</th>
                          </xsl:if>
                          <xsl:if test="$hasDataScadenzaPagamento &gt; 0">
                            <th>entro il</th>
                          </xsl:if>
                          <xsl:if test="$hasCodUfficioPostale &gt; 0">
                            <th>Uff. postale</th>
                          </xsl:if>
                          <xsl:if test="$hasQuietanzante &gt; 0">
                            <th>Quietanzante</th>
                          </xsl:if>
                          <xsl:if test="$hasCFQuietanzante &gt; 0">
                            <th>CF Quietanzante</th>
                          </xsl:if>
                          <xsl:if test="$hasIstitutoFinanziario &gt; 0">
                            <th>Istituto</th>
                          </xsl:if>
                          <xsl:if test="$hasIBAN &gt; 0">
                            <th>IBAN</th>
                          </xsl:if>
                          <xsl:if test="$hasABI &gt; 0">
                            <th>ABI</th>
                          </xsl:if>
                          <xsl:if test="$hasCAB &gt; 0">
                            <th>CAB</th>
                          </xsl:if>
                          <xsl:if test="$hasBIC &gt; 0">
                            <th>BIC</th>
                          </xsl:if>
                          <xsl:if test="$hasScontoPagamentoAnticipato &gt; 0">
                            <th>Sconto pag. anticipato
                              <xsl:if test="../DatiGenerali/DatiGeneraliDocumento/Divisa">
                                <span>&#32;(<xsl:value-of select="../DatiGenerali/DatiGeneraliDocumento/Divisa"/>)</span>
                              </xsl:if>
                            </th>
                          </xsl:if>
                          <xsl:if test="$hasDataLimitePagamentoAnticipato &gt; 0">
                            <th>se pag. entro</th>
                          </xsl:if>
                          <xsl:if test="$hasPenalitaPagamentiRitardati &gt; 0">
                            <th>Penale
                              <xsl:if test="../DatiGenerali/DatiGeneraliDocumento/Divisa">
                                <span>&#32;(<xsl:value-of select="../DatiGenerali/DatiGeneraliDocumento/Divisa"/>)</span>
                              </xsl:if>
                            </th>
                          </xsl:if>
                          <xsl:if test="$hasDataDecorrenzaPenale &gt; 0">
                            <th>Se pag. dopo</th>
                          </xsl:if>
                          <xsl:if test="$hasCodicePagamento &gt; 0">
                            <th>Cod. pag.</th>
                          </xsl:if>
                        </tr>
                      </thead>
                      <tbody>
                        <xsl:for-each select="DettaglioPagamento">
                          <xsl:variable name="QUIETANZANTE">
                            <xsl:value-of select="concat(string(TitoloQuietanzante), ' ', string(NomeQuietanzante), ' ', string(CognomeQuietanzante), ' ')" />
                          </xsl:variable>
                          <tr>
                            <td class="aSx">
                              <xsl:variable name="MP">
                                <xsl:value-of select="ModalitaPagamento"/>
                              </xsl:variable>
                              <xsl:choose>
                                <xsl:when test="$MP='MP01'">Contanti</xsl:when>
                                <xsl:when test="$MP='MP02'">Assegno</xsl:when>
                                <xsl:when test="$MP='MP03'">Assegno circolare</xsl:when>
                                <xsl:when test="$MP='MP04'">Contanti presso Tesoreria</xsl:when>
                                <xsl:when test="$MP='MP05'">Bonifico</xsl:when>
                                <xsl:when test="$MP='MP06'">Vaglia cambiario</xsl:when>
                                <xsl:when test="$MP='MP07'">Bollettino bancario</xsl:when>
                                <xsl:when test="$MP='MP08'">Carta di pagamento</xsl:when>
                                <xsl:when test="$MP='MP09'">RID</xsl:when>
                                <xsl:when test="$MP='MP10'">RID utenze</xsl:when>
                                <xsl:when test="$MP='MP11'">RID veloce</xsl:when>
                                <xsl:when test="$MP='MP12'">RIBA</xsl:when>
                                <xsl:when test="$MP='MP13'">MAV</xsl:when>
                                <xsl:when test="$MP='MP14'">Quietanza erario</xsl:when>
                                <xsl:when test="$MP='MP15'">Giroconto su conti di contabilit&#224; speciale</xsl:when>
                                <xsl:when test="$MP='MP16'">Domiciliazione bancaria</xsl:when>
                                <xsl:when test="$MP='MP17'">Domiciliazione postale</xsl:when>
                                <xsl:when test="$MP='MP18'">Bollettino di C/C postale</xsl:when>
                                <xsl:when test="$MP='MP19'">SEPA Direct Debit</xsl:when>
                                <xsl:when test="$MP='MP20'">SEPA Direct Debit CORE</xsl:when>
                                <xsl:when test="$MP='MP21'">SEPA Direct Debit B2B</xsl:when>
                                <xsl:when test="$MP='MP22'">Trattenuta su somme gi&#224; riscosse</xsl:when>
                                <xsl:when test="$MP=''"></xsl:when>
                                <xsl:otherwise>
                                  <span>
                                    <xsl:value-of select="ModalitaPagamento" />(codice non previsto)</span>
                                </xsl:otherwise>
                              </xsl:choose>
                            </td>
                            <td class="aDx">
                              <span class="evidenzia">
                                <xsl:value-of select="format-number(ImportoPagamento, '#.##0,00', 'euro')" />
                              </span>
                            </td>
                            <xsl:if test="$hasBeneficiario &gt; 0">
                              <td class="aSx">
                                <xsl:value-of select="Beneficiario" />
                              </td>
                            </xsl:if>
                            <xsl:if test="$hasDataRiferimentoTerminiPagamento &gt; 0">
                              <td class="aSx">
                                <xsl:call-template name="dataItaliana">
                                  <xsl:with-param name="data" select="DataRiferimentoTerminiPagamento"/>
                                </xsl:call-template>
                              </td>
                            </xsl:if>
                            <xsl:if test="$hasGiorniTerminiPagamento &gt; 0">
                              <td>
                                <xsl:value-of select="GiorniTerminiPagamento" />
                              </td>
                            </xsl:if>
                            <xsl:if test="$hasDataScadenzaPagamento &gt; 0">
                              <td class="aSx">
                                <xsl:call-template name="dataItaliana">
                                  <xsl:with-param name="data" select="DataScadenzaPagamento"/>
                                </xsl:call-template>
                              </td>
                            </xsl:if>
                            <xsl:if test="$hasCodUfficioPostale &gt; 0">
                              <td>
                                <xsl:value-of select="CodUfficioPostale" />
                              </td>
                            </xsl:if>
                            <xsl:if test="$hasQuietanzante &gt; 0">
                              <td class="aSx">
                                <xsl:value-of select="normalize-space($QUIETANZANTE)" />
                              </td>
                            </xsl:if>
                            <xsl:if test="$hasCFQuietanzante &gt; 0">
                              <td class="aSx">
                                <xsl:value-of select="CFQuietanzante" />
                              </td>
                            </xsl:if>
                            <xsl:if test="$hasIstitutoFinanziario &gt; 0">
                              <td class="aSx">
                                <xsl:value-of select="IstitutoFinanziario" />
                              </td>
                            </xsl:if>
                            <xsl:if test="$hasIBAN &gt; 0">
                              <td class="aSx">
                                <xsl:value-of select="IBAN" />
                              </td>
                            </xsl:if>
                            <xsl:if test="$hasABI &gt; 0">
                              <td class="aSx">
                                <xsl:value-of select="ABI" />
                              </td>
                            </xsl:if>
                            <xsl:if test="$hasCAB &gt; 0">
                              <td class="aSx">
                                <xsl:value-of select="CAB" />
                              </td>
                            </xsl:if>
                            <xsl:if test="$hasBIC &gt; 0">
                              <td class="aSx">
                                <xsl:value-of select="BIC" />
                              </td>
                            </xsl:if>
                            <xsl:if test="$hasScontoPagamentoAnticipato &gt; 0">
                              <td class="aDx">
                                <xsl:value-of select="format-number(ScontoPagamentoAnticipato, '#.##0,00', 'euro')" />
                              </td>
                            </xsl:if>
                            <xsl:if test="$hasDataLimitePagamentoAnticipato &gt; 0">
                              <td class="aSx">
                                <xsl:call-template name="dataItaliana">
                                  <xsl:with-param name="data" select="DataLimitePagamentoAnticipato"/>
                                </xsl:call-template>
                              </td>
                            </xsl:if>
                            <xsl:if test="$hasPenalitaPagamentiRitardati &gt; 0">
                              <td class="aDx">
                                <xsl:value-of select="format-number(PenalitaPagamentiRitardati, '#.##0,00', 'euro')" />
                              </td>
                            </xsl:if>
                            <xsl:if test="$hasDataDecorrenzaPenale &gt; 0">
                              <td class="aSx">
                                <xsl:call-template name="dataItaliana">
                                  <xsl:with-param name="data" select="DataDecorrenzaPenale"/>
                                </xsl:call-template>
                              </td>
                            </xsl:if>
                            <xsl:if test="$hasCodicePagamento &gt; 0">
                              <td class="aSx">
                                <xsl:value-of select="CodicePagamento" />
                              </td>
                            </xsl:if>
                          </tr>
                        </xsl:for-each>
                      </tbody>
                      <tfoot></tfoot>
                    </table>
                    <div class="separa">
                      <p>&#32;</p>
                    </div>
                  </xsl:for-each>
                  <div class="separa">
                    <p>&#32;</p>
                  </div>
                </div>
              </xsl:if>
              <!--FINE DATI PAGAMENTO-->
              <!--INIZIO ALLEGATI-->
              <xsl:if test="Allegati">
                <div class="allegati">
                  <xsl:variable name="hasAlgoritmoCompressione">
                    <xsl:value-of select="count(Allegati/AlgoritmoCompressione)" />
                  </xsl:variable>
                  <xsl:variable name="hasFormatoAttachment">
                    <xsl:value-of select="count(Allegati/FormatoAttachment)" />
                  </xsl:variable>
                  <xsl:variable name="hasDescrizioneAttachment">
                    <xsl:value-of select="count(Allegati/DescrizioneAttachment)" />
                  </xsl:variable>
                  <table class="tableDettagli">
                    <caption>Allegati</caption>
                    <thead>
                      <tr>
                        <th>Nome</th>
                        <xsl:if test="$hasAlgoritmoCompressione &gt; 0">
                          <th>Algoritmo di compressione</th>
                        </xsl:if>
                        <xsl:if test="$hasFormatoAttachment &gt; 0">
                          <th>Formato</th>
                        </xsl:if>
                        <xsl:if test="$hasDescrizioneAttachment &gt; 0">
                          <th>Descrizione</th>
                        </xsl:if>
                      </tr>
                    </thead>
                    <tbody>
                      <xsl:for-each select="Allegati">
                        <xsl:variable name="urlAllegato">
                          <xsl:value-of select="NomeAttachment" />
                        </xsl:variable>
                        <xsl:variable name="base64Allegato">
                          <xsl:value-of select="Attachment" />
                        </xsl:variable>
                        <xsl:variable name="idLinkAllegato">
                          <xsl:value-of select="concat('link_',NomeAttachment)" />
                        </xsl:variable>
                        <tr>
                          <td class="aSx">
						  <xsl:variable name="formatoAllegatoCorretto">
							  <xsl:call-template name = "formatoAllegato" >
								<xsl:with-param name="nomeFile" select="NomeAttachment"/>
								<xsl:with-param name="formatoFile" select="FormatoAttachment"/>
								<xsl:with-param name="compressione" select="AlgoritmoCompressione"/>
							  </xsl:call-template>
							</xsl:variable>
                            <xsl:variable name="nomeAllegatoCompleto">
                              <xsl:call-template name = "nomeAllegatoConFormato" >
                                <xsl:with-param name="nomeFile" select="NomeAttachment"/>
                                <xsl:with-param name="formatoFile" select="$formatoAllegatoCorretto"/>
                              </xsl:call-template>
                            </xsl:variable>
                            <xsl:variable name="mimeTypeCorretto">
                              <xsl:call-template name = "tipoMime" >
                                <xsl:with-param name="formato" select="FormatoAttachment"/>
                              </xsl:call-template>
                            </xsl:variable>
                              <xsl:value-of select="NomeAttachment" />
                          </td>
                          <xsl:if test="$hasAlgoritmoCompressione &gt; 0">
                            <td class="aSx">
                              <xsl:value-of select="AlgoritmoCompressione" />
                            </td>
                          </xsl:if>
                          <xsl:if test="$hasFormatoAttachment &gt; 0">
                            <td class="aSx">
                              <xsl:value-of select="FormatoAttachment" />
                            </td>
                          </xsl:if>
                          <xsl:if test="$hasDescrizioneAttachment &gt; 0">
                            <td class="aSx">
                              <xsl:value-of select="DescrizioneAttachment" />
                            </td>
                          </xsl:if>
                        </tr>
                      </xsl:for-each>
                    </tbody>
                    <tfoot></tfoot>
                  </table>
                  <xsl:for-each select="Allegati">
                    <xsl:variable name="nomeAllegatoCompleto">
                      <xsl:call-template name = "nomeAllegatoConFormato" >
                        <xsl:with-param name="nomeFile" select="NomeAttachment"/>
                        <xsl:with-param name="formatoFile" select="FormatoAttachment"/>
                      </xsl:call-template>
                    </xsl:variable>
                    <xsl:variable name="mimeTypeCorretto">
                      <xsl:call-template name = "tipoMime" >
                        <xsl:with-param name="formato" select="FormatoAttachment"/>
                      </xsl:call-template>
                    </xsl:variable>
					<xsl:variable name="formatoAllegatoCorretto">
                      <xsl:call-template name = "formatoAllegato" >
						<xsl:with-param name="nomeFile" select="NomeAttachment"/>
                        <xsl:with-param name="formatoFile" select="FormatoAttachment"/>
						<xsl:with-param name="compressione" select="AlgoritmoCompressione"/>
                      </xsl:call-template>
                    </xsl:variable>
                    <xsl:variable name="idInputAllegato">
                      <xsl:value-of select="concat('input_',$nomeAllegatoCompleto)" />
                    </xsl:variable>
                    <xsl:variable name="idFormatoAllegato">
                      <xsl:value-of select="concat('formato_',$nomeAllegatoCompleto)" />
                    </xsl:variable>
                    <xsl:variable name="idFormatoMimeAllegato">
                      <xsl:value-of select="concat('formatoMime_',$nomeAllegatoCompleto)" />
                    </xsl:variable>
                    <input class="nascondi" id="{$idInputAllegato}" value="{normalize-space(Attachment)}" />
                    <input class="nascondi" id="{$idFormatoAllegato}" value="{$formatoAllegatoCorretto}" />
                    <input class="nascondi" id="{$idFormatoMimeAllegato}" value="{$mimeTypeCorretto}" />
                  </xsl:for-each>
                  <div class="separa">
                    <p>&#32;</p>
                  </div>
                </div>
              </xsl:if>
              <!--FINE ALLEGATI-->
            </div>
            <!-- fine elementoLotto -->
            <xsl:if test="position() != last()">
              <div class="decora">
                <p>
                  <br />
                </p>
              </div>
            </xsl:if>
          </xsl:for-each>
          <div class="footer">
          </div>
        </div>
        <!-- fine fattura-container -->
      </body>
    </html>
	<!-- fatturaxmlfree.org -->
  </xsl:template>
</xsl:stylesheet>