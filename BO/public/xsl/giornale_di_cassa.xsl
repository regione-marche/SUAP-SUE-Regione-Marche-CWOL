<?xml version="1.0" encoding="UTF-8"?>
<!-- giornale_di_cassa.xsl Giornale di Cassa versione: 2018-05-31 -->
<xsl:stylesheet version = "1.0" xmlns:xsl = "http://www.w3.org/1999/XSL/Transform" xmlns = "http://www.w3.org/TR/xhtml1/strict">
    
    <xsl:decimal-format name="european" decimal-separator=',' grouping-separator='.' />
    <xsl:param name="font">arial</xsl:param>
    
    <xsl:output method = "html" encoding = "UTF-8" doctype-public="-//W3C//DTD HTML 4.0 Transitional//EN" doctype-system="http://www.w3.org/TR/html4/loose.dtd"/>
    <!-- The real stylesheet starts here -->  
    <xsl:template match = "/">
        
    <html>
    <head>
        <meta http-equiv="X-UA-Compatible" content="IE=edge"/>
        <style type="text/css">
            #elementi table { font-size: .9em; margin-top: 1em; border-collapse: collapse; border: 1px solid black; }
        </style>
    </head>
    <body style="font-family: {$font}">

    <h2> Giornale di Cassa del <xsl:value-of select="flusso_giornale_di_cassa/data_inizio_periodo_riferimento"/></h2>
   
    <table class="elementi" style="border-collapse: collapse;">
        <tr style="background-color: cyan;">
            <th style="border: 1px solid #000;">Conto</th>
            <th style="border: 1px solid #000;">Tipo Documento</th>
            <th style="border: 1px solid #000;">Numero</th>
            <th style="border: 1px solid #000;">Data</th>
            <th style="border: 1px solid #000;">Progr</th>
            <th style="border: 1px solid #000;">Tipo Operazione</th>
            <th style="border: 1px solid #000;">Importo</th>
            <th style="border: 1px solid #000;">Cliente</th>
            <th style="border: 1px solid #000;">Partita Iva</th>
        </tr>
        <xsl:for-each select="flusso_giornale_di_cassa/informazioni_conto_evidenza/movimento_conto_evidenza">
            <tr>
                <td style="border: 1px solid #000;"><xsl:value-of select="flusso_giornale_di_cassa/informazioni_conto_evidenza/conto_evidenza"/></td>
                <td style="border: 1px solid #000;"><xsl:value-of select="tipo_documento"/></td>
                <td style="border: 1px solid #000;text-align:right;"><xsl:value-of select="numero_documento"/></td>
                <td style="border: 1px solid #000;text-align:right;"><xsl:value-of select="data_movimento"/></td>
                <td style="border: 1px solid #000;text-align:right;"><xsl:value-of select="progressivo_documento"/></td>
                <td style="border: 1px solid #000;"><xsl:value-of select="tipo_operazione"/></td>
                <td style="border: 1px solid #000;text-align:right;"><xsl:value-of select="format-number(importo, '###.###,00', 'european')"/></td>
                <td style="border: 1px solid #000;"><xsl:value-of select="cliente/anagrafica_cliente"/></td>
                <td style="border: 1px solid #000;"><xsl:value-of select="cliente/partita_iva_cliente"/></td>
            </tr>
        </xsl:for-each>
    </table>
	
    <h2> Riepilogo Giornale di Cassa del <xsl:value-of select="flusso_giornale_di_cassa/data_inizio_periodo_riferimento"/></h2>
    <table style="border-collapse: collapse;">
        <tr>
            <td style="border: 1px solid #000;">Saldo Precedente</td>
            <td style="border: 1px solid #000;text-align:right;"><xsl:value-of select="format-number(flusso_giornale_di_cassa/saldo_complessivo_precedente, '###.###,00', 'european')"/></td>
        </tr>
        <tr>
            <td style="border: 1px solid #000;">Totale Entrate</td>
            <td style="border: 1px solid #000;text-align:right;"><xsl:value-of select="format-number(flusso_giornale_di_cassa/totale_complessivo_entrate, '###.###,00', 'european')"/></td>
        </tr>
        <tr>
            <td style="border: 1px solid #000;">Totale Uscite</td>
            <td style="border: 1px solid #000;text-align:right;"><xsl:value-of select="format-number(flusso_giornale_di_cassa/totale_complessivo_uscite, '###.###,00', 'european')"/></td>
        </tr>
        <tr>
            <td style="border: 1px solid #000;">Saldo Finale</td>
            <td style="border: 1px solid #000;text-align:right;"><xsl:value-of select="format-number(flusso_giornale_di_cassa/saldo_complessivo_finale, '###.###,00', 'european')"/></td>
        </tr>
    </table>
	
    <h2> Totali Esercizio</h2>
    <table style="border-collapse: collapse;">
        <tr>
            <td style="border: 1px solid #000;">Fondo di Cassa</td>
            <td style="border: 1px solid #000;text-align:right;"><xsl:value-of select="format-number(flusso_giornale_di_cassa/totali_esercizio/fondo_di_cassa, '###.###,00', 'european')"/></td>
        </tr>
        <tr>
            <td style="border: 1px solid #000;">Totale Reversali Riscosse</td>
            <td style="border: 1px solid #000;text-align:right;"><xsl:value-of select="format-number(flusso_giornale_di_cassa/totali_esercizio/totale_reversali_riscosse, '###.###,00', 'european')"/></td>
        </tr>
        <tr>
            <td style="border: 1px solid #000;">Totale Sospesi Entrata</td>
            <td style="border: 1px solid #000;text-align:right;"><xsl:value-of select="format-number(flusso_giornale_di_cassa/totali_esercizio/totale_sospesi_entrata, '###.###,00', 'european')"/></td>
        </tr>
        <tr>
            <td style="border: 1px solid #000;">Totale Entrate</td>
            <td style="border: 1px solid #000;text-align:right;"><xsl:value-of select="format-number(flusso_giornale_di_cassa/totali_esercizio/totale_entrate, '###.###,00', 'european')"/></td>
        </tr>
        <tr>
            <td style="border: 1px solid #000;">Deficit di Cassa</td>
            <td style="border: 1px solid #000;text-align:right;"><xsl:value-of select="format-number(flusso_giornale_di_cassa/totali_esercizio/deficit_di_cassa, '###.###,00', 'european')"/></td>
        </tr>
        <tr>
            <td style="border: 1px solid #000;">Totale Mandati Pagati</td>
            <td style="border: 1px solid #000;text-align:right;"><xsl:value-of select="format-number(flusso_giornale_di_cassa/totali_esercizio/totale_mandati_pagati, '###.###,00', 'european')"/></td>
        </tr>
        <tr>
            <td style="border: 1px solid #000;">Totale Sospesi Uscita</td>
            <td style="border: 1px solid #000;text-align:right;"><xsl:value-of select="format-number(flusso_giornale_di_cassa/totali_esercizio/totale_sospesi_uscita, '###.###,00', 'european')"/></td>
        </tr>
        <tr>
            <td style="border: 1px solid #000;">Totale Uscite</td>
            <td style="border: 1px solid #000;text-align:right;"><xsl:value-of select="format-number(flusso_giornale_di_cassa/totali_esercizio/totale_uscite, '###.###,00', 'european')"/></td>
        </tr>
        <tr>
            <td style="border: 1px solid #000;">Saldo Esercizio</td>
            <td style="border: 1px solid #000;text-align:right;"><xsl:value-of select="format-number(flusso_giornale_di_cassa/totali_esercizio/saldo_esercizio, '###.###,00', 'european')"/></td>
        </tr>
    </table>
	
    </body>
    </html>
    
    </xsl:template>
    
</xsl:stylesheet>
