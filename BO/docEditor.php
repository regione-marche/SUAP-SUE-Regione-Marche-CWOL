<?php
ob_start();

require_once './ConfigLoader.php';
require_once(ITA_LIB_PATH . '/itaPHPCore/App.class.php');
require_once ITA_LIB_PATH . '/itaPHPCore/itaDocumentale.class.php';
require_once ITA_LIB_PATH . '/itaPHPCore/itaMimeTypeUtils.class.php';
require_once ITA_LIB_PATH . '/itaPHPCore/itaDocEditor.class.php';

try {
    /*
     * Carica app
     */
    if (!isset($_POST['TOKEN'])) {
        if (!isset($_GET['TOKEN'])) {
            exit;
        } else {
            $_POST['TOKEN'] = $_GET['TOKEN'];
        }
    }
    App::load();

    $ditta = App::$utente->getKey('ditta');
    $token = App::$utente->getKey('TOKEN');

    /*
     * verifica del TOKEN
     */
    $ret_token = ita_token($token, $ditta, 0, 3);
    if ($ret_token['status'] == '0') {
        $token = $ret_token['token'];
    } else {
        exit;
    }

    /*
     * Lettura parametri
     */
    $itaDocEditor = new itaDocEditor();
    $docEditorParams = $itaDocEditor->loadDocEditorParams();

    /*
     * Impostazione dati per editor
     */
    $dataArray = App::$utente->getKey($_GET['key'] . "_DATAARRAY");
} catch (Exception $e) {
    error_log($e->getMessage());
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <link rel="icon" href="./favicon.ico" type="image/x-icon" />
        <title>ONLYOFFICE</title>

        <style>
            html {
                height: 100%;
                width: 100%;
            }

            body {
                background: #fff;
                color: #333;
                font-family: Arial, Tahoma,sans-serif;
                font-size: 12px;
                font-weight: normal;
                height: 100%;
                margin: 0;
                overflow-y: hidden;
                padding: 0;
                text-decoration: none;
            }

            form {
                height: 100%;
            }

            div {
                margin: 0;
                padding: 0;
            }
        </style>

        <script type="text/javascript" src="<?php echo $docEditorParams['DOCEDIT_API_URL'] ?>"></script>

        <script type="text/javascript">
            var docEditor;
            var innerAlert = function (message) {
                if (console && console.log)
                    console.log(message);
            };

            var onReady = function () {
                innerAlert("Document editor ready");
            };

            var onDocumentStateChange = function (event) {
                var title = document.title.replace(/\*$/g, "");
                document.title = title + (event.data ? "*" : "");
            };

            var onRequestEditRights = function () {
                location.href = location.href.replace(RegExp("action=view\&?", "i"), "");
            };

            var onError = function (event) {
                if (event)
                    innerAlert(event.data);
            };

            var onOutdatedVersion = function (event) {
                location.reload(true);
            };

            var connectEditor = function () {
                docEditor = new DocsAPI.DocEditor("iframeEditor", JSON.parse('<?php echo json_encode($dataArray) ?>'));
            };

            if (window.addEventListener) {
                window.addEventListener("load", connectEditor);
            } else if (window.attachEvent) {
                window.attachEvent("load", connectEditor);
            }
        </script>
    </head>
    <body>
        <form id="form1">
            <div id="iframeEditor">
            </div>
        </form>
    </body>
</html>





