<?php

class itaPlUpload {

    public function getResponse() {
        die('{"jsonrpc" : "2.0", "error" : {"code": 102, "message": "Failed to open output stream."}, "id" : "id", "response" : "error"}');
        die('{"jsonrpc" : "2.0", "result" : null, "id" : "id", "response" : "success"}');
    }

    public function handleUpload($uploadPath) {
        $response = array(
            'jsonrpc' => '2.0',
            'status' => 'partial',
            'response' => 'success'
        );

        if (empty($_FILES) || $_FILES['file']['error']) {
            $response['error'] = array();
            $response['error']['code'] = 103;
            $response['error']['message'] = 'Failed to move uploaded file.';
            $response['response'] = 'error';
            return $response;
        }

        $chunk = isset($_REQUEST["chunk"]) ? intval($_REQUEST["chunk"]) : 0;
        $chunks = isset($_REQUEST["chunks"]) ? intval($_REQUEST["chunks"]) : 0;

        $fileName = isset($_REQUEST["name"]) ? $_REQUEST["name"] : $_FILES["file"]["name"];
        $filePath = $uploadPath . DIRECTORY_SEPARATOR . $fileName;

        $response['filename'] = $fileName;
        $response['filepath'] = $filePath;

        $out = @fopen("{$filePath}.part", $chunk == 0 ? "wb" : "ab");
        if (!$out) {
            $response['error'] = array();
            $response['error']['code'] = 102;
            $response['error']['message'] = 'Failed to open output stream.';
            $response['response'] = 'error';
            return $response;
        }

        $in = @fopen($_FILES['file']['tmp_name'], "rb");
        if (!$in) {
            $response['error'] = array();
            $response['error']['code'] = 101;
            $response['error']['message'] = 'Failed to open input stream.';
            $response['response'] = 'error';
            return $response;
        }

        while ($buff = fread($in, 4096)) {
            fwrite($out, $buff);
        }

        @fclose($in);
        @fclose($out);

        @unlink($_FILES['file']['tmp_name']);

        if (!$chunks || $chunk == $chunks - 1) {
            $response['status'] = 'complete';
            rename("{$filePath}.part", $filePath);
        }

        return $response;
    }

}
