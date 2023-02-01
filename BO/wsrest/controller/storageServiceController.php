<?php

require_once 'RestController.class.php';

class storageServiceController extends RestController {

    public function getDocument($params) {
        $this->resetLastError();

//        require_once ITA_LIB_PATH . '/Cache/CacheFactory.class.php';
//        $cache = CacheFactory::newCache();
//        $resource = $cache->get($params['resourceToken']);
//
//        if (!$resource) {
//            $this->setLastErrorCode(-1);
//            $this->setLastErrorDescription('Risorsa inesistente.');
//            return false;
//        }
//
//        $cache->delete($params['resourceToken']);

        return file_get_contents(base64_decode($params['resourceid']));
        //return print_r(bbase64_decode($params['resourceid']),true);
    }

    public function storeDocument($params) {
        ob_end_clean();
        if (($body_stream = file_get_contents("php://input")) === FALSE) {
            echo "Bad Request";
        }

        $data = json_decode($body_stream, TRUE);

        if ($data["status"] == 2) {
            $downloadUri = $data["url"];

            if (($new_data = file_get_contents($downloadUri)) === FALSE) {
                echo "Bad Response";
//                file_put_contents("c:/works/tmp/storage1.log", '[' . date('Y-m-d H:m:s') . "] bad request\n" . print_r($params, true), FILE_APPEND);
            } else {
                file_put_contents(base64_decode($params['resourceid']), $new_data, LOCK_EX);
//                file_put_contents("c:/works/tmp/storage1.log", '[' . date('Y-m-d H:m:s') . ']' . print_r($params, true), FILE_APPEND);
            }
        }
        die("{\"error\":0}");


        /*
          $body = file_get_contents('php://input');
          $decodedBody = json_decode();


          file_put_contents("/users/tmp/testonlyoffice_saved2.log", print_r($body,true));
         */
    }
    
    public function getOTR($params) {
        $this->resetLastError();

        require_once ITA_LIB_PATH . '/Cache/CacheFactory.class.php';
        $cache = CacheFactory::newCache(CacheFactory::TYPE_FILE, null, '');
        $resource = $cache->get($params['resourceToken']);

        if (!$resource) {
            $this->setLastErrorCode(-1);
            $this->setLastErrorDescription('Risorsa inesistente.');
            return false;
        }
        
        if (isSet($resource['DeleteFile']) && $resource['DeleteFile'] === true) {
            $cache->delete($params['resourceToken']);
        }

//        require_once 'loginController.php';
//        $loginController = new loginController();
//
//        if (!$loginController->CheckItaEngineContextToken(array('TokenKey' => $resource['TOKEN']))) {
//            $this->setLastErrorCode(-1);
//            $this->setLastErrorDescription('Sessione non valida.');
//            return false;
//        }

        return array(
            'filename' => $resource['Filename'],
            'filepath' => $resource['Filepath'],
            'forcedownload' => (isSet($resource['ForceDownload']) && $resource['ForceDownload'] == false ? false : true),
            'deletefile' => (isSet($resource['DeleteFile']) && $resource['DeleteFile'] == false ? false : true)
        );
    }

}
