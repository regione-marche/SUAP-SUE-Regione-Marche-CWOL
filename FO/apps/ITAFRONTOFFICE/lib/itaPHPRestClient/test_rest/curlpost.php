<?php

//       <DatiSpecifici>
//          <VersioneDatiSpecifici>1.0</VersioneDatiSpecifici>
//          <NumeroIniziale>2010</NumeroIniziale>
//          <NumeroFinale>2010</NumeroFinale>
//          <DataInizioRegistrazioni>2015-10-05</DataInizioRegistrazioni>
//          <DataFineRegistrazioni>2015-10-05</DataFineRegistrazioni>
//          <Originatore>xxxxx</Originatore>
//          <Responsabile>Mario Rossi</Responsabile>
//          <Operatore>CED</Operatore>
//          <NumeroDocumentiRegistrati>1</NumeroDocumentiRegistrati>
//          <NumeroDocumentiAnnullati>0</NumeroDocumentiAnnullati>
//          <DenominazioneApplicativo>itaEngine</DenominazioneApplicativo>
//          <VersioneApplicativo>5.1</VersioneApplicativo>
//          <ProduttoreApplicativo>Italsoft</ProduttoreApplicativo>
//          <DenominazioneSistemaGestioneBaseDati>denominazione db</DenominazioneSistemaGestioneBaseDati>
//          <VersioneSistemaGestioneBaseDati>versione db</VersioneSistemaGestioneBaseDati>
//          <ProduttoreSistemaGestioneBaseDati>produttore db</ProduttoreSistemaGestioneBaseDati>
//          <TempoConservazione>ILLIMITATO</TempoConservazione>
//       </DatiSpecifici>

//<Allegati>
//          <Allegato>
//             <IDDocumento>5f378b4e-dd5c-4283-a993-fdb1beeffedf</IDDocumento>
//             <TipoDocumento>REGISTRO MODIFICHE</TipoDocumento>
//             <StrutturaOriginale>
//                <TipoStruttura>DocumentoGenerico</TipoStruttura>
//                <Componenti>
//                   <Componente>
//                      <ID>ID2</ID>
//                      <OrdinePresentazione>1</OrdinePresentazione>
//                      <TipoComponente>Contenuto</TipoComponente>
//                      <TipoSupportoComponente>FILE</TipoSupportoComponente>
//                      <NomeComponente>RegistroModifiche05-10-2015.pdf</NomeComponente>
//                      <FormatoFileVersato>pdf</FormatoFileVersato>
//                   </Componente>
//                </Componenti>
//             </StrutturaOriginale>
//          </Allegato>
//       </Allegati>


$xml = '
    <?xml version="1.0" encoding="UTF-8"?>
	<UnitaDocumentaria>
       <Intestazione>
          <Versione>1.3</Versione>
          <Versatore>
             <Ambiente>MARCHE DIGIP_TEST</Ambiente>
             <Ente>Comune di Civitanova Marche</Ente>
             <Struttura>C_C770</Struttura>
             <UserID>it@lProt_C_CIVITANOVAMARCHE</UserID>
          </Versatore>
          <Chiave>
             <Numero>1</Numero>
             <Anno>2015</Anno>
             <TipoRegistro>DFI</TipoRegistro>
          </Chiave>
          <TipologiaUnitaDocumentaria>Documento protocollato</TipologiaUnitaDocumentaria>
       </Intestazione>
       <Configurazione>
          <TipoConservazione>Sostitutiva</TipoConservazione>
          <ForzaAccettazione>true</ForzaAccettazione>
          <ForzaConservazione>true</ForzaConservazione>
       </Configurazione>
       <ProfiloUnitaDocumentaria>
          <Oggetto>Registro giornaliero di protocollo dal n.xxxx al n. yyyy del 07-10-2015</Oggetto>
          <Data>2015-10-07</Data>
       </ProfiloUnitaDocumentaria>
       <NumeroAllegati>0</NumeroAllegati>
       <DocumentoPrincipale>
          <IDDocumento>1234567890</IDDocumento>
          <TipoDocumento>REGISTRO GIORNALIERO</TipoDocumento>
          <StrutturaOriginale>
             <TipoStruttura>DocumentoGenerico</TipoStruttura>
             <Componenti>
                <Componente>
                   <ID>ID1</ID>
                   <OrdinePresentazione>1</OrdinePresentazione>
                   <TipoComponente>Contenuto</TipoComponente>
                   <TipoSupportoComponente>FILE</TipoSupportoComponente>
                   <NomeComponente>RegistroProtocollo05-10-2015.pdf</NomeComponente>
                   <FormatoFileVersato>pdf</FormatoFileVersato>
                </Componente>
             </Componenti>
          </StrutturaOriginale>
       </DocumentoPrincipale>
    </UnitaDocumentaria>';

$resource = curl_init();
$assoc = array(
    'VERSIONE' => '1.3',
    'LOGINNAME' => 'it@lProt_C_CIVITANOVAMARCHE',
    'PASSWORD' => 'italsoft15',
    'XMLSIP' => ''
);
$files = array(
    'ID1'=>'/users/itaEngine/tmp/test_rest/RegistroProtocollo05-10-2015.pdf'
);

curl_custom_postfields($resource, $assoc, $files);
$ret_post = curl_exec($resource);
print_r('<pre>');
print_r($ret_post);
print_r('</pre>');

curl_close($resource);

/**
 * For safe multipart POST request for PHP5.3 ~ PHP 5.4.
 *
 * @param resource $ch cURL resource
 * @param array $assoc "name => value"
 * @param array $files "name => path"
 * @return bool
 */
function curl_custom_postfields($ch, array $assoc = array(), array $files = array()) {

    // invalid characters for "name" and "filename"
    static $disallow = array("\0", "\"", "\r", "\n");

    // build normal parameters
    foreach ($assoc as $k => $v) {
        $k = str_replace($disallow, "_", $k);
        $body[] = implode("\r\n", array(
            "Content-Disposition: form-data; name=\"{$k}\"",
            "",
            filter_var($v),
        ));
    }

    // build file parameters
    foreach ($files as $k => $v) {
        switch (true) {
            case false === $v = realpath(filter_var($v)):
            case!is_file($v):
            case!is_readable($v):
                continue; // or return false, throw new InvalidArgumentException
        }
        $data = file_get_contents($v);
        $v = call_user_func("end", explode(DIRECTORY_SEPARATOR, $v));
        $k = str_replace($disallow, "_", $k);
        $v = str_replace($disallow, "_", $v);
        $body[] = implode("\r\n", array(
            "Content-Disposition: form-data; name=\"{$k}\"; filename=\"{$v}\"",
            "Content-Type: application/octet-stream",
            "",
            $data,
        ));
    }

    // generate safe boundary
    do {
        $boundary = "---------------------" . md5(mt_rand() . microtime());
    } while (preg_grep("/{$boundary}/", $body));

    // add boundary for each parameters
//    array_walk($body, function (&$part) use ($boundary) {
//        $part = "--{$boundary}\r\n{$part}";
//    });
    array_walk($body, 'add_boundary', $boundary);

    // add final boundary
    $body[] = "--{$boundary}--";
    $body[] = "";
//    print_r('<pre>');
//    print_r($body);
//    print_r("Content-Type: multipart/form-data; boundary={$boundary}");
//    print_r('</pre>');
// set options
    return @curl_setopt_array($ch, array(
                //CURLOPT_URL => 'http://192.168.191.1/itaEngine/tmp/test_rest/restsimu.php',
                CURLOPT_URL => 'http://84.38.48.88:8080/sacer/VersamentoSync',
                CURLOPT_POST => true,
                CURLOPT_RETURNTRANSFER=> true,
                CURLOPT_POSTFIELDS => implode("\r\n", $body),
                CURLOPT_HTTPHEADER => array(
                    //"Expect: 100-continue",
                    "Content-Type: multipart/form-data; boundary={$boundary}", // change Content-Type
                ),
    ));
}

function add_boundary(&$part, $key, $boundary) {
    $part = "--{$boundary}\r\n{$part}";
}

?>
