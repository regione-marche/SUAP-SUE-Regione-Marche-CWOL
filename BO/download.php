<?php

/**
 *  Avvio l'applicazione
 */
ob_start();
require_once './ConfigLoader.php';
try {
    require_once(ITA_LIB_PATH . '/itaPHPCore/App.class.php');
    require_once ITA_LIB_PATH . '/itaPHPCore/itaDocumentale.class.php';
    require_once ITA_LIB_PATH . '/itaPHPCore/itaMimeTypeUtils.class.php';

    if (!isset($_POST['TOKEN'])) {
        if (!isset($_GET['TOKEN'])) {
            exit;
        } else {
            $_POST['TOKEN'] = $_GET['TOKEN'];
        }
    }
    App::load();

    /*
     * parametri di aperttura/salvataggio file
     */
    $disposition = "inline";
    $forceDownload = $_GET['forceDownload'];
    if ($forceDownload == true) {
        $disposition = "attachment ";
    }
    $headers = true;
    if (isset($_GET['headers'])) {
        $headers = $_GET['headers'];
    }

    $utf8decode = $_GET['utf8decode'];

    /*
     * parametri base di sicurezza
     */
    $ditta = App::$utente->getKey('ditta');
    $token = App::$utente->getKey('TOKEN');
    echo("Token:" . $token . "<br>" . $ditta);

    /*
     * verifica del TOKEN
     */
    if (!App::$utente->getKey('idUtente') === 999999999999 && App::$utente->getStato() !== Utente::AUTENTICATO_ADMIN) {
        $ret_token = ita_token($token, $ditta, 0, 3);
        if ($ret_token['status'] == '0') {
            $token = $ret_token['token'];
            var_dump($ret_token);
        } else {
            exit;
        }
    }
    
    if (isset($_GET['key'])) {
        $dataFile = App::$utente->getKey($_GET['key'] . "_DATAFILE");
        $fileName = App::$utente->getKey($_GET['key'] . "_FILENAME");
        @ob_end_clean();
        $CType = itaMimeTypeUtils::estraiEstensione($fileName);

        if (substr($dataFile, 0, 8) != 'DOCUUID:') {
            if ($headers == true) {
                header("Content-type: " . $CType . ($utf8decode ? '; charset=utf-8' : ''));
                header("Content-Disposition: $disposition; filename=\"$fileName\"");
                header("Content-Transfer-Encoding: binary");
                header("Content-Description: \"$fileName\"");
                header("Content-Length: " . filesize($dataFile));
                header('Cache-Control: max-age=0, no-store, must-revalidate, post-check=0, pre-check=0');
                //
            }
            set_time_limit(0);
            $file = @fopen($dataFile, "rb");
            if ($file) {
                while (!feof($file)) {
                    print(@fread($file, 1024 * 1024));
                    ob_flush();
                    flush();
                }
                fclose($file);
            }
        } else {
            /*
             *  Lettura da Alfresco.
             */

            $uuid = substr($dataFile, 7);
            $documentale = new itaDocumentale('ALFCITY');
            $documentale->setUtf8_encode(true);
            $documentale->setUtf8_decode(true);

            if (!$documentale->contentByUUID($uuid)) {
                echo 'Download non riuscito.';
                exit;
            }
            $ContenutoFile = $documentale->getResult();
            if (!$ContenutoFile) {
                echo 'Download non riuscito.';
                exit;
            }

            if ($headers == true) {
                header("Content-type: " . $CType);
                header("Content-Disposition: $disposition; filename=\"$fileName\"");
                header("Content-Transfer-Encoding: binary");
                header("Content-Description: \"$fileName\"");
                header("Content-Length: " . strlen($ContenutoFile));
                header('Cache-Control: max-age=0, no-store, must-revalidate, post-check=0, pre-check=0');
            }
            set_time_limit(0);
            if ($ContenutoFile) {
                while ($ContenutoFile) {
                    print(substr($ContenutoFile, 0, (1024 * 1024)));
                    ob_flush();
                    flush();
                    $ContenutoFile = substr($ContenutoFile, (1024 * 1024));
                }
            }
        }
    }
} catch (Exception $e) {
    
}