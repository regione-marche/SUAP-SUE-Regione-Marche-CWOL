
<?php

/**
 *  Programma Popolamento passo dati catastali Form
 *
 *
 * @category   Library
 * @package    /apps/Menu
 * @author     Andrea Bufarini
 * @copyright  1987-2011 Italsoft snc
 * @license
 * @version    15.05.2015
 * @link
 * @see
 * @since
 * @deprecated
 */
include_once ITA_BASE_PATH . '/apps/Pratiche/praLib.class.php';

function praPopolaPassoPraticheIncorporate() {
    $praPopolaPassoPraticheIncorporate = new praPopolaPassoPraticheIncorporate();
    $praPopolaPassoPraticheIncorporate->parseEvent();
    return;
}

class praPopolaPassoPraticheIncorporate extends itaModel {

    public $praLib;
    public $PRAM_DB;
    public $nameForm = "praPopolaPassoPraticheIncorporate";
    public $divRic = "praPopolaPassoPraticheIncorporate_divRicerca";
    public $divRis = "praPopolaPassoPraticheIncorporate_divRisultato";
    public $divGes = "praPopolaPassoPraticheIncorporate_divGestione";
    public $gridPraclt = "praPopolaPassoPraticheIncorporate_gridPraclt";
    public $Itepas_tab;
    public $newPassi;
    public $newAggiuntivi;
    public $newTipiPassi;
    public $Anapra_tab;

    const PASSI_DA_INSERIRE = 'a:4:{i:0;a:91:{s:5:"ROWID";s:5:"11147";s:6:"ITECOD";s:6:"002020";s:6:"ITESET";s:0:"";s:6:"ITESER";s:0:"";s:6:"ITEOPE";s:0:"";s:6:"ITERES";s:0:"";s:6:"ITEDES";s:41:"Vuoi accorpare altre richieste on line ? ";s:6:"ITEGIO";s:1:"0";s:6:"ITESEQ";s:2:"70";s:6:"ITETER";s:0:"";s:6:"ITECLT";s:6:"000065";s:6:"ITETES";s:0:"";s:6:"ITEWRD";s:0:"";s:6:"ITEOBL";s:1:"0";s:6:"ITEUPL";s:1:"0";s:6:"ITEDOW";s:1:"0";s:6:"ITECTR";s:0:"";s:6:"ITEIMG";s:0:"";s:6:"ITENOT";s:100:"Rispondere SI se vuoi procedere ad accorpare altre richieste (già create o da creare) alla presente.";s:6:"ITEPUB";s:1:"1";s:6:"ITECOM";s:1:"0";s:6:"ITEPAY";s:1:"0";s:6:"ITEAML";s:1:"0";s:6:"ITEIVA";s:0:"";s:6:"ITEFVA";s:0:"";s:6:"ITESTR";s:1:"0";s:6:"ITEPST";s:1:"0";s:6:"ITEKEY";s:18:"002020148042924834";s:6:"ITEQST";s:1:"1";s:6:"ITEDAT";s:1:"0";s:6:"ITEVPA";s:18:"002020148042947148";s:6:"ITEVPN";s:18:"002020148042953427";s:6:"ITEIRE";s:1:"0";s:6:"ITEMLT";s:1:"0";s:6:"ITEINF";s:0:"";s:6:"ITESTA";s:1:"0";s:6:"ITETIM";s:1:"0";s:6:"ITECDE";s:0:"";s:6:"ITERUO";s:0:"";s:6:"ITECTP";s:0:"";s:6:"ITEINT";s:1:"0";s:6:"ITEIDR";s:1:"0";s:6:"ITEEXT";s:0:"";s:6:"ITEDRR";s:1:"0";s:7:"ITESTAP";s:1:"0";s:7:"ITESTCH";s:1:"0";s:6:"ITEATE";s:0:"";s:6:"ITEDIS";s:1:"0";s:6:"ITEZIP";s:1:"0";s:6:"ITEIFC";s:1:"0";s:6:"ITEMRI";s:1:"0";s:6:"ITEMRE";s:1:"0";s:6:"ITEURL";s:0:"";s:6:"ITETBA";s:0:"";s:6:"ITETAL";s:0:"";s:7:"ITEFILE";s:1:"0";s:6:"ITERIF";s:1:"0";s:7:"ITEPROC";s:0:"";s:6:"ITEDAP";s:0:"";s:6:"ITEALP";s:0:"";s:7:"ITEPRIV";s:1:"0";s:6:"ITECOL";s:1:"0";s:6:"ITECTB";s:1:"0";s:7:"ITEMETA";s:6:"a:0:{}";s:6:"ITERDM";s:1:"0";s:6:"ITENRA";s:0:"";s:8:"ITEQALLE";s:1:"0";s:7:"ITEQCLA";s:1:"0";s:8:"ITEQDEST";s:1:"0";s:8:"ITEQNOTE";s:1:"0";s:9:"ITEQSTDAG";s:1:"0";s:6:"ITEOBE";s:0:"";s:10:"ITECOMPSEQ";s:1:"0";s:11:"ITECOMPFLAG";s:0:"";s:6:"ITEAGE";s:1:"0";s:6:"ITEDWP";s:0:"";s:11:"ITENOTSTYLE";s:46:"font-size:14px;font-weight:bolder;color:black;";s:7:"ITEHTML";s:0:"";s:7:"ITEHELP";s:0:"";s:11:"TEMPLATEKEY";s:0:"";s:6:"ITEPRR";s:1:"0";s:12:"ITECUSTOMTML";s:0:"";s:8:"ITESOSTF";s:1:"0";s:9:"ITERICUNI";s:1:"0";s:9:"ITERICSUB";s:1:"0";s:11:"ITEDEFSTATO";s:1:"0";s:7:"ITEKPRE";s:0:"";s:13:"ITECARICAAUTO";s:1:"0";s:11:"ITEAPRIAUTO";s:1:"0";s:10:"ITEASSAUTO";s:0:"";s:8:"ITEMIGRA";s:0:"";}i:1;a:91:{s:5:"ROWID";s:5:"11149";s:6:"ITECOD";s:6:"002020";s:6:"ITESET";s:0:"";s:6:"ITESER";s:0:"";s:6:"ITEOPE";s:0:"";s:6:"ITERES";s:0:"";s:6:"ITEDES";s:46:"Vuoi accorpare la richiesta ad una principale?";s:6:"ITEGIO";s:1:"0";s:6:"ITESEQ";s:2:"80";s:6:"ITETER";s:0:"";s:6:"ITECLT";s:6:"000066";s:6:"ITETES";s:0:"";s:6:"ITEWRD";s:0:"";s:6:"ITEOBL";s:1:"0";s:6:"ITEUPL";s:1:"0";s:6:"ITEDOW";s:1:"0";s:6:"ITECTR";s:0:"";s:6:"ITEIMG";s:0:"";s:6:"ITENOT";s:95:"Rispondere SI per inoltrare, con un unico invio, la presente richiesta insieme alla principale.";s:6:"ITEPUB";s:1:"1";s:6:"ITECOM";s:1:"0";s:6:"ITEPAY";s:1:"0";s:6:"ITEAML";s:1:"0";s:6:"ITEIVA";s:0:"";s:6:"ITEFVA";s:0:"";s:6:"ITESTR";s:1:"0";s:6:"ITEPST";s:1:"0";s:6:"ITEKEY";s:18:"002020148042953427";s:6:"ITEQST";s:1:"1";s:6:"ITEDAT";s:1:"0";s:6:"ITEVPA";s:18:"002020148042959973";s:6:"ITEVPN";s:18:"002020148035058831";s:6:"ITEIRE";s:1:"0";s:6:"ITEMLT";s:1:"0";s:6:"ITEINF";s:0:"";s:6:"ITESTA";s:1:"0";s:6:"ITETIM";s:1:"0";s:6:"ITECDE";s:0:"";s:6:"ITERUO";s:0:"";s:6:"ITECTP";s:0:"";s:6:"ITEINT";s:1:"0";s:6:"ITEIDR";s:1:"0";s:6:"ITEEXT";s:0:"";s:6:"ITEDRR";s:1:"0";s:7:"ITESTAP";s:1:"0";s:7:"ITESTCH";s:1:"0";s:6:"ITEATE";s:0:"";s:6:"ITEDIS";s:1:"0";s:6:"ITEZIP";s:1:"0";s:6:"ITEIFC";s:1:"0";s:6:"ITEMRI";s:1:"0";s:6:"ITEMRE";s:1:"0";s:6:"ITEURL";s:0:"";s:6:"ITETBA";s:0:"";s:6:"ITETAL";s:0:"";s:7:"ITEFILE";s:1:"0";s:6:"ITERIF";s:1:"0";s:7:"ITEPROC";s:0:"";s:6:"ITEDAP";s:0:"";s:6:"ITEALP";s:0:"";s:7:"ITEPRIV";s:1:"0";s:6:"ITECOL";s:1:"0";s:6:"ITECTB";s:1:"0";s:7:"ITEMETA";s:6:"a:0:{}";s:6:"ITERDM";s:1:"0";s:6:"ITENRA";s:0:"";s:8:"ITEQALLE";s:1:"0";s:7:"ITEQCLA";s:1:"0";s:8:"ITEQDEST";s:1:"0";s:8:"ITEQNOTE";s:1:"0";s:9:"ITEQSTDAG";s:1:"0";s:6:"ITEOBE";s:0:"";s:10:"ITECOMPSEQ";s:1:"0";s:11:"ITECOMPFLAG";s:0:"";s:6:"ITEAGE";s:1:"0";s:6:"ITEDWP";s:0:"";s:11:"ITENOTSTYLE";s:0:"";s:7:"ITEHTML";s:0:"";s:7:"ITEHELP";s:0:"";s:11:"TEMPLATEKEY";s:0:"";s:6:"ITEPRR";s:1:"0";s:12:"ITECUSTOMTML";s:0:"";s:8:"ITESOSTF";s:1:"0";s:9:"ITERICUNI";s:1:"0";s:9:"ITERICSUB";s:1:"0";s:11:"ITEDEFSTATO";s:1:"0";s:7:"ITEKPRE";s:0:"";s:13:"ITECARICAAUTO";s:1:"0";s:11:"ITEAPRIAUTO";s:1:"0";s:10:"ITEASSAUTO";s:0:"";s:8:"ITEMIGRA";s:0:"";}i:2;a:91:{s:5:"ROWID";s:5:"11150";s:6:"ITECOD";s:6:"002020";s:6:"ITESET";s:0:"";s:6:"ITESER";s:0:"";s:6:"ITEOPE";s:0:"";s:6:"ITERES";s:0:"";s:6:"ITEDES";s:59:"Indica il numero della richiesta principale dove accorpare.";s:6:"ITEGIO";s:1:"0";s:6:"ITESEQ";s:2:"90";s:6:"ITETER";s:0:"";s:6:"ITECLT";s:6:"000067";s:6:"ITETES";s:0:"";s:6:"ITEWRD";s:0:"";s:6:"ITEOBL";s:1:"1";s:6:"ITEUPL";s:1:"0";s:6:"ITEDOW";s:1:"0";s:6:"ITECTR";s:0:"";s:6:"ITEIMG";s:0:"";s:6:"ITENOT";s:0:"";s:6:"ITEPUB";s:1:"1";s:6:"ITECOM";s:1:"0";s:6:"ITEPAY";s:1:"0";s:6:"ITEAML";s:1:"0";s:6:"ITEIVA";s:0:"";s:6:"ITEFVA";s:0:"";s:6:"ITESTR";s:1:"0";s:6:"ITEPST";s:1:"0";s:6:"ITEKEY";s:18:"002020148042959973";s:6:"ITEQST";s:1:"0";s:6:"ITEDAT";s:1:"1";s:6:"ITEVPA";s:0:"";s:6:"ITEVPN";s:0:"";s:6:"ITEIRE";s:1:"0";s:6:"ITEMLT";s:1:"0";s:6:"ITEINF";s:0:"";s:6:"ITESTA";s:1:"0";s:6:"ITETIM";s:1:"0";s:6:"ITECDE";s:0:"";s:6:"ITERUO";s:0:"";s:6:"ITECTP";s:0:"";s:6:"ITEINT";s:1:"0";s:6:"ITEIDR";s:1:"0";s:6:"ITEEXT";s:0:"";s:6:"ITEDRR";s:1:"0";s:7:"ITESTAP";s:1:"0";s:7:"ITESTCH";s:1:"0";s:6:"ITEATE";s:0:"";s:6:"ITEDIS";s:1:"0";s:6:"ITEZIP";s:1:"0";s:6:"ITEIFC";s:1:"0";s:6:"ITEMRI";s:1:"0";s:6:"ITEMRE";s:1:"0";s:6:"ITEURL";s:0:"";s:6:"ITETBA";s:0:"";s:6:"ITETAL";s:0:"";s:7:"ITEFILE";s:1:"0";s:6:"ITERIF";s:1:"0";s:7:"ITEPROC";s:0:"";s:6:"ITEDAP";s:0:"";s:6:"ITEALP";s:0:"";s:7:"ITEPRIV";s:1:"0";s:6:"ITECOL";s:1:"0";s:6:"ITECTB";s:1:"0";s:7:"ITEMETA";s:6:"a:0:{}";s:6:"ITERDM";s:1:"0";s:6:"ITENRA";s:0:"";s:8:"ITEQALLE";s:1:"0";s:7:"ITEQCLA";s:1:"0";s:8:"ITEQDEST";s:1:"0";s:8:"ITEQNOTE";s:1:"0";s:9:"ITEQSTDAG";s:1:"0";s:6:"ITEOBE";s:0:"";s:10:"ITECOMPSEQ";s:1:"0";s:11:"ITECOMPFLAG";s:0:"";s:6:"ITEAGE";s:1:"0";s:6:"ITEDWP";s:0:"";s:11:"ITENOTSTYLE";s:0:"";s:7:"ITEHTML";s:0:"";s:7:"ITEHELP";s:0:"";s:11:"TEMPLATEKEY";s:0:"";s:6:"ITEPRR";s:1:"0";s:12:"ITECUSTOMTML";s:0:"";s:8:"ITESOSTF";s:1:"0";s:9:"ITERICUNI";s:1:"0";s:9:"ITERICSUB";s:1:"1";s:11:"ITEDEFSTATO";s:1:"0";s:7:"ITEKPRE";s:0:"";s:13:"ITECARICAAUTO";s:1:"0";s:11:"ITEAPRIAUTO";s:1:"0";s:10:"ITEASSAUTO";s:0:"";s:8:"ITEMIGRA";s:0:"";}i:3;a:91:{s:5:"ROWID";s:5:"11148";s:6:"ITECOD";s:6:"002020";s:6:"ITESET";s:0:"";s:6:"ITESER";s:0:"";s:6:"ITEOPE";s:0:"";s:6:"ITERES";s:0:"";s:6:"ITEDES";s:50:"Conferma le richieste incorporate alla principale.";s:6:"ITEGIO";s:1:"0";s:6:"ITESEQ";s:3:"100";s:6:"ITETER";s:0:"";s:6:"ITECLT";s:6:"000068";s:6:"ITETES";s:0:"";s:6:"ITEWRD";s:0:"";s:6:"ITEOBL";s:1:"1";s:6:"ITEUPL";s:1:"0";s:6:"ITEDOW";s:1:"0";s:6:"ITECTR";s:0:"";s:6:"ITEIMG";s:0:"";s:6:"ITENOT";s:142:"Per accorpare: crea una nuova richiesta on line o aprine una esistente ed indica il numero di questa richiesta principale nell\'apposito passo.";s:6:"ITEPUB";s:1:"1";s:6:"ITECOM";s:1:"0";s:6:"ITEPAY";s:1:"0";s:6:"ITEAML";s:1:"0";s:6:"ITEIVA";s:0:"";s:6:"ITEFVA";s:0:"";s:6:"ITESTR";s:1:"0";s:6:"ITEPST";s:1:"0";s:6:"ITEKEY";s:18:"002020148042947148";s:6:"ITEQST";s:1:"0";s:6:"ITEDAT";s:1:"1";s:6:"ITEVPA";s:0:"";s:6:"ITEVPN";s:0:"";s:6:"ITEIRE";s:1:"0";s:6:"ITEMLT";s:1:"0";s:6:"ITEINF";s:0:"";s:6:"ITESTA";s:1:"0";s:6:"ITETIM";s:1:"0";s:6:"ITECDE";s:0:"";s:6:"ITERUO";s:0:"";s:6:"ITECTP";s:0:"";s:6:"ITEINT";s:1:"0";s:6:"ITEIDR";s:1:"1";s:6:"ITEEXT";s:0:"";s:6:"ITEDRR";s:1:"0";s:7:"ITESTAP";s:1:"0";s:7:"ITESTCH";s:1:"0";s:6:"ITEATE";s:0:"";s:6:"ITEDIS";s:1:"0";s:6:"ITEZIP";s:1:"0";s:6:"ITEIFC";s:1:"0";s:6:"ITEMRI";s:1:"0";s:6:"ITEMRE";s:1:"0";s:6:"ITEURL";s:0:"";s:6:"ITETBA";s:0:"";s:6:"ITETAL";s:0:"";s:7:"ITEFILE";s:1:"0";s:6:"ITERIF";s:1:"0";s:7:"ITEPROC";s:0:"";s:6:"ITEDAP";s:0:"";s:6:"ITEALP";s:0:"";s:7:"ITEPRIV";s:1:"0";s:6:"ITECOL";s:1:"0";s:6:"ITECTB";s:1:"0";s:7:"ITEMETA";s:2421:"a:1:{s:14:"TESTOBASEXHTML";s:2383:"<p><strong>ELENCO DELLE PRATICHE ACCORPATE:</strong> <br /><br /></p>
<table class="ita-table-template" style="width: 600px; height: 112px; border: 1px solid #000000;" border="1">
<tbody>
<tr class="ita-table-header">
<td style="background-color: #ffffff; text-align: center; border: 1px solid #000000;"><span style="font-size: medium;"><strong><span style="font-family: arial,helvetica,sans-serif;">Numero</span></strong></span></td>
<td style="background-color: #ffffff; text-align: center; border: 1px solid #000000;"><span style="font-size: medium;"><strong><span style="font-family: arial,helvetica,sans-serif;">Descrizione</span></strong></span></td>
<td style="background-color: #ffffff; text-align: center; border: 1px solid #000000;"><span style="font-size: medium;"><strong><span style="font-family: arial,helvetica,sans-serif;">Settore</span></strong></span></td>
<td style="background-color: #ffffff; text-align: center; border: 1px solid #000000;"><span style="font-size: medium;"><strong><span style="font-family: arial,helvetica,sans-serif;">Attivit&agrave;</span></strong></span></td>
<td style="background-color: #ffffff; text-align: center; border: 1px solid #000000;"><span style="font-size: medium;"><strong><span style="font-family: arial,helvetica,sans-serif;">Data/Ora Inizio<br /></span></strong></span></td>
</tr>
<tr>
<td style="background-color: #ffffff; border: 1px solid #000000; text-align: center;"><span style="font-family: arial,helvetica,sans-serif; font-size: x-small;">@{$PRAACCORPATE.NUMERO}@</span></td>
<td style="background-color: #ffffff; border: 1px solid #000000; text-align: center;"><span style="font-family: arial,helvetica,sans-serif; font-size: x-small;">@{$PRAACCORPATE.DESCRIZIONE}@</span></td>
<td style="background-color: #ffffff; border: 1px solid #000000; text-align: center;"><span style="font-family: arial,helvetica,sans-serif; font-size: x-small;">@{$PRAACCORPATE.SETTORE}@</span></td>
<td style="background-color: #ffffff; border: 1px solid #000000; text-align: center;"><span style="font-family: arial,helvetica,sans-serif; font-size: x-small;">@{$PRAACCORPATE.ATTIVITA}@</span></td>
<td style="background-color: #ffffff; border: 1px solid #000000; text-align: center;"><span style="font-family: arial,helvetica,sans-serif; font-size: x-small;">@{$PRAACCORPATE.INIZIO}@</span></td>
</tr>
</tbody>
</table>";}";s:6:"ITERDM";s:1:"0";s:6:"ITENRA";s:0:"";s:8:"ITEQALLE";s:1:"0";s:7:"ITEQCLA";s:1:"0";s:8:"ITEQDEST";s:1:"0";s:8:"ITEQNOTE";s:1:"0";s:9:"ITEQSTDAG";s:1:"1";s:6:"ITEOBE";s:0:"";s:10:"ITECOMPSEQ";s:2:"30";s:11:"ITECOMPFLAG";s:0:"";s:6:"ITEAGE";s:1:"0";s:6:"ITEDWP";s:0:"";s:11:"ITENOTSTYLE";s:46:"font-size:14px;font-weight:bolder;color:black;";s:7:"ITEHTML";s:0:"";s:7:"ITEHELP";s:0:"";s:11:"TEMPLATEKEY";s:0:"";s:6:"ITEPRR";s:1:"0";s:12:"ITECUSTOMTML";s:0:"";s:8:"ITESOSTF";s:1:"0";s:9:"ITERICUNI";s:1:"1";s:9:"ITERICSUB";s:1:"0";s:11:"ITEDEFSTATO";s:1:"0";s:7:"ITEKPRE";s:0:"";s:13:"ITECARICAAUTO";s:1:"0";s:11:"ITEAPRIAUTO";s:1:"0";s:10:"ITEASSAUTO";s:0:"";s:8:"ITEMIGRA";s:0:"";}}';
    const AGGIUNTIVI_DA_INSERIRE = 'a:2:{i:0;a:26:{s:5:"ROWID";s:6:"128039";s:6:"ITECOD";s:6:"002020";s:6:"ITEKEY";s:18:"002020148042959973";s:6:"ITDDES";s:0:"";s:6:"ITDSEQ";s:2:"20";s:6:"ITDKEY";s:15:"RICHIESTA_UNICA";s:8:"ITDALIAS";s:0:"";s:6:"ITDVAL";s:0:"";s:6:"ITDSET";s:0:"";s:6:"ITDTIP";s:0:"";s:6:"ITDCTR";s:0:"";s:6:"ITDNOT";s:0:"";s:6:"ITDLAB";s:0:"";s:6:"ITDTIC";s:4:"Text";s:6:"ITDROL";s:1:"0";s:6:"ITDVCA";s:0:"";s:6:"ITDREV";s:0:"";s:6:"ITDLEN";s:0:"";s:6:"ITDDIM";s:0:"";s:6:"ITDDIZ";s:1:"C";s:6:"ITDACA";s:1:"0";s:6:"ITDPOS";s:8:"Sinistra";s:7:"ITDMETA";s:0:"";s:11:"ITDLABSTYLE";s:0:"";s:13:"ITDFIELDSTYLE";s:14:"display: none;";s:10:"ITDEXPROUT";s:6:"a:0:{}";}i:1;a:26:{s:5:"ROWID";s:6:"128040";s:6:"ITECOD";s:6:"002020";s:6:"ITEKEY";s:18:"002020148042959973";s:6:"ITDDES";s:15:"Richiesta Unica";s:6:"ITDSEQ";s:2:"10";s:6:"ITDKEY";s:25:"RICHIESTA_UNICA_FORMATTED";s:8:"ITDALIAS";s:0:"";s:6:"ITDVAL";s:0:"";s:6:"ITDSET";s:0:"";s:6:"ITDTIP";s:15:"Richiesta_unica";s:6:"ITDCTR";s:0:"";s:6:"ITDNOT";s:0:"";s:6:"ITDLAB";s:39:"N. Richiesta Principale a cui Accorpare";s:6:"ITDTIC";s:4:"Text";s:6:"ITDROL";s:1:"0";s:6:"ITDVCA";s:0:"";s:6:"ITDREV";s:0:"";s:6:"ITDLEN";s:0:"";s:6:"ITDDIM";s:0:"";s:6:"ITDDIZ";s:1:"C";s:6:"ITDACA";s:1:"0";s:6:"ITDPOS";s:8:"Sinistra";s:7:"ITDMETA";s:0:"";s:11:"ITDLABSTYLE";s:0:"";s:13:"ITDFIELDSTYLE";s:0:"";s:10:"ITDEXPROUT";s:6:"a:0:{}";}}';
    const TIPIPASSI_DA_INSERIRE = 'a:4:{i:0;a:13:{s:5:"ROWID";s:2:"57";s:6:"CLTCOD";s:6:"000065";s:6:"CLTOFF";s:1:"0";s:6:"CLTOBL";s:1:"0";s:6:"CLTDES";s:28:"Domanda Conferma Invio Unico";s:6:"CLTOPE";s:0:"";s:7:"CLTMETA";s:0:"";s:12:"CLTINSEDITOR";s:12:"Italsoft srl";s:10:"CLTINSDATE";s:8:"20170214";s:10:"CLTINSTIME";s:8:"15:03:13";s:12:"CLTUPDEDITOR";s:12:"Italsoft srl";s:10:"CLTUPDDATE";s:8:"20170214";s:10:"CLTUPDTIME";s:8:"15:03:13";}i:1;a:13:{s:5:"ROWID";s:2:"58";s:6:"CLTCOD";s:6:"000066";s:6:"CLTOFF";s:1:"0";s:6:"CLTOBL";s:1:"0";s:6:"CLTDES";s:42:"Domanda Conferma Accorpamento a Principale";s:6:"CLTOPE";s:0:"";s:7:"CLTMETA";s:0:"";s:12:"CLTINSEDITOR";s:12:"Italsoft srl";s:10:"CLTINSDATE";s:8:"20170214";s:10:"CLTINSTIME";s:8:"15:04:18";s:12:"CLTUPDEDITOR";s:12:"Italsoft srl";s:10:"CLTUPDDATE";s:8:"20170214";s:10:"CLTUPDTIME";s:8:"15:04:18";}i:2;a:13:{s:5:"ROWID";s:2:"59";s:6:"CLTCOD";s:6:"000067";s:6:"CLTOFF";s:1:"0";s:6:"CLTOBL";s:1:"0";s:6:"CLTDES";s:43:"Conferma Pratiche da Allegare a Invio Unico";s:6:"CLTOPE";s:0:"";s:7:"CLTMETA";s:0:"";s:12:"CLTINSEDITOR";s:12:"Italsoft srl";s:10:"CLTINSDATE";s:8:"20170214";s:10:"CLTINSTIME";s:8:"15:16:13";s:12:"CLTUPDEDITOR";s:12:"Italsoft srl";s:10:"CLTUPDDATE";s:8:"20170214";s:10:"CLTUPDTIME";s:8:"15:16:13";}i:3;a:13:{s:5:"ROWID";s:2:"60";s:6:"CLTCOD";s:6:"000068";s:6:"CLTOFF";s:1:"0";s:6:"CLTOBL";s:1:"0";s:6:"CLTDES";s:41:"Scelta Pratica Principale a cui Accorpare";s:6:"CLTOPE";s:0:"";s:7:"CLTMETA";s:0:"";s:12:"CLTINSEDITOR";s:12:"Italsoft srl";s:10:"CLTINSDATE";s:8:"20170214";s:10:"CLTINSTIME";s:8:"15:17:22";s:12:"CLTUPDEDITOR";s:12:"Italsoft srl";s:10:"CLTUPDDATE";s:8:"20170214";s:10:"CLTUPDTIME";s:8:"15:17:22";}}';

    function __construct() {
        parent::__construct();
        try {
            $this->praLib = new praLib();
            $this->PRAM_DB = $this->praLib->getPRAMDB();
            $this->Itepas_tab = App::$utente->getKey($this->nameForm . '_Itepas_tab');
            $this->newPassi = App::$utente->getKey($this->nameForm . '_newPassi');
            $this->newAggiuntivi = App::$utente->getKey($this->nameForm . '_newAggiuntivi');
            $this->newTipiPassi = App::$utente->getKey($this->nameForm . '_newTipiPassi');
            $this->Anapra_tab = App::$utente->getKey($this->nameForm . '_Anapra_tab');
        } catch (Exception $e) {
            App::log($e->getMessage());
        }
    }

    function __destruct() {
        parent::__destruct();
        App::$utente->setKey($this->nameForm . '_Itepas_tab', $this->Itepas_tab);
        App::$utente->setKey($this->nameForm . '_newPassi', $this->newPassi);
        App::$utente->setKey($this->nameForm . '_newAggiuntivi', $this->newAggiuntivi);
        App::$utente->setKey($this->nameForm . '_newTipiPassi', $this->newTipiPassi);
        App::$utente->setKey($this->nameForm . '_Anapra_tab', $this->Anapra_tab);
        if ($this->close != true) {
            
        }
    }

    public function parseEvent() {
        parent::parseEvent();
        switch ($_POST['event']) {
            case 'openform':
                $this->newPassi = unserialize(self::PASSI_DA_INSERIRE);
                $this->newAggiuntivi = unserialize(self::AGGIUNTIVI_DA_INSERIRE);
                $this->newTipiPassi = unserialize(self::TIPIPASSI_DA_INSERIRE);


                $this->OpenRicerca();
                $this->Nascondi();
                $this->trovaProcedimenti();

                /*  per uso temporaneo
                 * 
                 */
                //$this->crea_serializzati();
//                Out::msgInfo("passi", print_r($this->newPassi, true));
//                Out::msgInfo("aggiuntivi", print_r($this->newAggiuntivi, true));
//                Out::msgInfo("tipipassi", print_r($this->newTipiPassi, true));


                break;
            case 'onClick':
                switch ($_POST['id']) {

                    case $this->nameForm . '_ConfermaProcedimento':
                        $procedimento = $_POST[$this->nameForm . '_Procedimento'];
                        if ($procedimento) {
                            $procedimento = str_pad($procedimento, 6, '0', STR_PAD_LEFT);
                            $this->trovaPassiProcedimento($procedimento);
                        } else {
                            Out::msgInfo('AVVISO', 'Indicare un procedimento da dove prendere i passi per le pratiche incorporate.');
                        }
                        break;

                    case $this->nameForm . '_Inserisci':
                        $ciSonoScartati = false;
                        $quanti = 0;
//                        $i=0;
                        foreach ($this->Anapra_tab as $anapra) {
//                            $i++;
//                            if ($i == 4) {
//                                break;
//                            }
                            if ($anapra['SCARTATO'] == 0) {
                                //
                                //  CALCOLO NUOVO ITEKEY
                                //
                                $decremento = 4;
                                foreach ($this->newPassi as $key => $passo) {
                                    $this->newPassi[$key]['ITECOD'] = $anapra['PRANUM'];
                                    $this->newPassi[$key]['ITEKEY'] = $this->praLib->keyGenerator($anapra['PRANUM']);
                                    $this->newPassi[$key]['ITESEQ'] = $anapra['ITESEQ'] - $decremento;
                                    $decremento = $decremento - 1;
                                }
                                //
                                //  ASSEGNAZIONE NUOVI RIFERIMENTI ALLE RISPOSTE DELLE DOMANDE
                                //
                                $this->newPassi[0]['ITEVPA'] = $this->newPassi[3]['ITEKEY'];
                                $this->newPassi[0]['ITEVPN'] = $this->newPassi[1]['ITEKEY'];
                                $this->newPassi[1]['ITEVPA'] = $this->newPassi[2]['ITEKEY'];
                                $this->newPassi[1]['ITEVPN'] = $anapra['ITEKEY'];
                                $keyDatiAggiuntivi = $this->newPassi[2]['ITEKEY'];
                                //
                                //  RESPONSABILE
                                //
                                $this->newPassi[0]['ITERES'] = '000001';
                                $this->newPassi[1]['ITERES'] = '000001';
                                $this->newPassi[2]['ITERES'] = '000001';
                                $this->newPassi[3]['ITERES'] = '000001';
                                //
                                //  REGISTRAZIONE PASSI
                                //
                                foreach ($this->newPassi as $passo) {
                                    $passo['ITEMIGRA'] = 'PRAT-INCORP-' . date('Ymd');
                                    $insert_Info = "Inserisco passo domanda pratiche incorporate su procedimento: " . $passo['ITECOD'];
                                    if (!$this->insertRecord($this->PRAM_DB, 'ITEPAS', $passo, $insert_Info)) {
                                        Out::msgStop("Inserimento ITEPAS", "Inserimento passo domanda pratiche incorporate fallita");
                                        break;
                                    }
                                }
                                /*
                                 * RIBALTO DATI AGGIUNTIVI 
                                 */
                                foreach ($this->newAggiuntivi as $keyAgg => $aggiuntivo) {
                                    $aggiuntivo['ITECOD'] = $anapra['PRANUM'];
                                    $aggiuntivo['ITEKEY'] = $keyDatiAggiuntivi;
                                    //
                                    $insert_Info = "Inserisco dati aggiunti per passo " . $aggiuntivo['ITEKEY'] . "sequenza: " . $passo['ITESEQ'];
                                    if (!$this->insertRecord($this->PRAM_DB, 'ITEDAG', $aggiuntivo, $insert_Info)) {
                                        Out::msgStop("Inserimento ITEDAG", "Inserimento dati aggiuntivi passo domanda pratiche incorporate fallita");
                                        break;
                                    }
                                }
                                //
                                // RIORDINO PASSI
                                //
                                $this->praLib->ordinaPassiProc($anapra['PRANUM']);
                                $quanti++;
                            } else {
                                $ciSonoScartati = true;
                            }
                        }
                        /*
                         * REGISTRO TIPI PASSI 
                         */
                        if ($ciSonoScartati === false) {
                            foreach ($this->newTipiPassi as $tipoPasso) {
                                $insert_Info = "Inserisco tipo passo " . $tipoPasso['CLTCOD'];
                                if (!$this->insertRecord($this->PRAM_DB, 'PRACLT', $tipoPasso, $insert_Info)) {
                                    Out::msgStop("Inserimento PRACLT", "Inserimento tipo passo fallita");
                                    break;
                                }
                            }
                        }
                        //
                        Out::msgInfo('AVVISO', 'Elaborazione terminata. Aggiornati ' . $quanti . ' procedimenti.');
                        break;
                }
                break;

            case 'close-portlet':
                App::$utente->removeKey($this->nameForm . '_Itepas_tab');
                App::$utente->removeKey($this->nameForm . '_newPassi');
                App::$utente->removeKey($this->nameForm . '_newTipiPassi');
                App::$utente->removeKey($this->nameForm . '_Anapra_tab');
                $this->returnToParent();
                break;
        }
    }

    public function returnToParent($close = true) {
        if ($close)
            $this->close();
    }

    public function close() {
        $this->close = true;
        Out::closeDialog($this->nameForm);
    }

    function Nascondi() {
        Out::hide($this->nameForm . '_Inserisci');
    }

    public function OpenRicerca() {
        Out::hide($this->divGes);
        Out::hide($this->divRis);
        Out::hide($this->divRic);
    }

    public function popolaTabella() {
        $ita_grid01 = new TableView(
                $this->gridPraclt, array('arrayTable' => $this->Anapra_tab,
            'rowIndex' => 'idx')
        );
        $ita_grid01->setPageRows('10000');
        $ita_grid01->setPageNum(1);
        TableView::enableEvents($this->gridPraclt);
        if ($ita_grid01->getDataPage('json', true)) {
            TableView::enableEvents($this->gridPraclt);
        }
    }
    
    public function trovaProcedimenti() {

//        $arrayEccezioni = array(
//                                0 => '000001', 
//                                1 => '000011', 
//                                2 => '000115'
//                                );
        $sql = "SELECT
                    ANAPRA.PRANUM,
                    ANAPRA.PRADES__1,
                    ITEPAS.ITEDES,
                    ITEPAS.ITECLT,
                    ITEPAS.ITESEQ,
                    ITEPAS.ITEKEY
                FROM 
                    ANAPRA
                LEFT OUTER JOIN ITEPAS ON ANAPRA.PRANUM = ITEPAS.ITECOD
                LEFT OUTER JOIN ITEEVT ITEEVT ON ANAPRA.PRANUM=ITEEVT.ITEPRA
                WHERE
                    (IEVTSP='1' OR IEVTSP='2') AND ITECLT = '000001' AND ITEDRR = 1 AND ANAPRA.PRAAVA=''
                GROUP BY PRANUM
";
        if ($arrayEccezioni) {
            foreach ($arrayEccezioni as $eccezione) {
                $sql.= " AND ITECOD <> $eccezione";
            }
        }
        $sql.=" ORDER BY 
                    PRANUM";
        $this->Anapra_tab = ItaDB::DBSQLSelect($this->PRAM_DB, $sql, true);
        if ($this->Anapra_tab) {
            Out::hide($this->divGes);
            Out::show($this->divRis);
            Out::hide($this->divRic);
            $this->controllaProcedimenti();
            $this->popolaTabella();
            Out::show($this->nameForm . '_Inserisci');
        } else {
            Out::msgInfo('AVVISO', 'Non trovati procedimenti da elaborare.');
        }
    }

    private function crea_serializzati() {

        $sql_pas = "
            SELECT *
            FROM
                `ITEPAS`
            WHERE 
                ITECOD = '002020' AND
                (ITESEQ = 70 OR ITESEQ = 80 OR ITESEQ = 90 OR ITESEQ = 100) ORDER BY ITESEQ";
        $Itepas_tab = ItaDB::DBSQLSelect($this->PRAM_DB, $sql_pas, true);
        file_put_contents("c:/tmp/pas_serialize.txt", serialize($Itepas_tab));

        $sql_dag = "SELECT * FROM `ITEDAG` WHERE `ITEKEY` LIKE '002020148042959973'";
        $Itedag_tab = ItaDB::DBSQLSelect($this->PRAM_DB, $sql_dag, true);
        file_put_contents("c:/tmp/dag_serialize.txt", serialize($Itedag_tab));

        $sql_tipiPassi = "SELECT * FROM `PRACLT` WHERE `CLTCOD` = '000065' OR `CLTCOD` = '000066' OR `CLTCOD` = '000067' OR `CLTCOD` = '000068'";
        $Praclt_tab = ItaDB::DBSQLSelect($this->PRAM_DB, $sql_tipiPassi, true);
        file_put_contents("c:/tmp/clt_serialize.txt", serialize($Praclt_tab));
    }

    public function controllaProcedimenti() {
        $scartati = 0;
        foreach ($this->Anapra_tab as $key => $Anapra_rec) {
            $sql = "SELECT * FROM ITEPAS WHERE ITECOD = '" . $Anapra_rec['PRANUM'] . "'";
            $Itepas_tab = ItaDB::DBSQLSelect($this->PRAM_DB, $sql, true);
            $presente = false;
            foreach ($Itepas_tab as $Itepas_rec) {
                if ($Itepas_rec['ITECLT'] == "000065" || $Itepas_rec['ITECLT'] == "000066" || $Itepas_rec['ITECLT'] == "000067" || $Itepas_rec['ITECLT'] == "000068") {
                    $presente = true;
                    break;
                }
            }
            if ($presente == true) {
                $this->Anapra_tab[$key]['SCARTATO'] = 1;
                $this->Anapra_tab[$key]['STATO'] = '<div class="ita-html"><span style="width:20px;" title="Scartato " class="ita-tooltip">' . "<p align = \"center\"><span class=\"ita-icon ita-icon-bullet-red-16x16 \" style=\"height:10px;width:10px;background-size:100%;vertical-align:bottom;margin-left:1px;display:inline-block;\" ></span></p>" . '</span></div>';
            } else {
                $this->Anapra_tab[$key]['SCARTATO'] = 0;
                $this->Anapra_tab[$key]['STATO'] = '<div class="ita-html"><span style="width:20px;" title="Da elaborare " class="ita-tooltip">' . "<p align = \"center\"><span class=\"ita-icon ita-icon-bullet-green-16x16 \" style=\"height:10px;width:10px;background-size:100%;vertical-align:bottom;margin-left:1px;display:inline-block;\" ></span></p>" . '</span></div>';
            }
        }
    }

}

?>
