<?php

/**
 *
 * Interfaccia per i metodi pagopa
 *
 * PHP Version 5
 *
 * @category   CORE
 * @package    lib
 * @author     Luca Cardinali <l.cardinali@palinformatica.it>
 * 
 */
interface iPagoPa {

    public function ricevutaAccettazionePubblicazione();

    public function ricevutaPubblicazione();

    public function ricevutaAccettazioneCancellazione();

    public function rendicontazione($params);

    public function riversamenti($params);

    public function riconciliazione();

    public function cancellazioneMassiva();

    public function getConfIntermediario();

    public function getDatiPagamentoDaIUV($IUV);

    public function getEmissioneDaIUV($IUV);

    public function ricercaPosizioneDaIUV($IUV);

    public function ricercaPosizioneChiaveEsterna($codtipscad, $subtipscad, $progcitysc, $annorif, $numRata, $progcitysca = null);

    public function rettificaPosizioneDaIUV($IUV, $toUpdate);

    public function ricercaPosizioniDaInfoAggiuntive($params);

    public function ricercaPosizioniDataPagamDaA($dataDa, $dataA, $codtipscad, $subtipscad);

    public function generaBollettinoDaIUV($iuv);

    public function generaBollettinoDaChiavePendenza($codtipscad, $subtipscad, $annorif, $progcitysc, $numrata);

    public function eseguiPagamentoDaIuv($iuv, $urlReturn, $redirectVerticale = 0);

    public function eseguiPagamentoDaChiavePendenza($codtipscad, $subtipscad, $annorif, $progcitysc, $numrata, $urlReturn);

    public function recuperaIUV($codtipscad, $subtipscad, $annorif, $progcitysc, $numrata, $progcitysca = null);

//    public function reinvioPubblicazione($progkeytabInvio);

    public function recuperaRicevutaPagamento($iuv, $arricchita);

    public function rimuoviPosizione($iuv);

    public function rimuoviPosizioni($idRuolo);

    public function ricevutaArricchita();

//    public function pubblica($arrayPenden, $arrayPenddet, &$msgError, $massivo = false);

    public function rimuoviPosizioneEPenden($iuv);

//    public function eseguiInserimento($scadenze);

    public function pubblicazioneSingolaDaChiavePendenza($codtipscad, $subtipscad, $progcitysc, $annorif, $progcitysca = null, $infoAggiuntive = null, $progsoggex = null);

    public function pubblicazioneSingolaDaPendenza($pendenza, $insertPenden = true);

    public function pubblicazioneSingolaDaChiavePendenzaConRate($pendenze);

    public function pubblicazioneSingolaPagataDaPendenza($pendenza);

    public function pubblicazioneMassivaDaPendenze($pendenze, $insertPenden = true);

    public function pubblicazioneMassivaDaChiaveEmissione($annoEmi, $numEmi, $idBolSere);

    public function pubblicazioneMassiva();

    public function inserimentoMassivo();

    public function inserisciPendenze(&$pendenze);

    public function pubblicazioneScadenzeCreateMassiva();

    public function inserisciAgidSoggetto($soggetto);

    public function testConnection($massivo, $tipoChiamata);
}

?>
