<?php

class Itafrontoffice_Download {

    public function __construct() {
        add_action('parse_request', array($this, 'parse_request'));
    }

    public function parse_request() {
        $request_uri = rtrim(strtok($_SERVER["REQUEST_URI"], '?'), '/');

        $blog_details = get_blog_details();
        $current_path = rtrim($blog_details->path, '/');

        if (strpos($request_uri, $current_path . '/itafrontoffice-download') === 0) {
            $encrypted_data = $_GET['key'];
            $data = json_decode(itaCrypt::decrypt($encrypted_data), true);

            $frontOfficeLib = new frontOfficeLib;

            $forceDownload = !isset($_GET['forceDownload']) || $_GET['forceDownload'] ? true : false;
            $utf8decode = isset($_GET['utf8decode']) && $_GET['utf8decode'] ? true : false;
            $headers = !isset($_GET['headers']) || $_GET['headers'] ? true : false;

            if ($forceDownload) {
                $frontOfficeLib->scaricaFile($data['filepath'], $data['filename'], true, $utf8decode, $headers);
            } else {
                $frontOfficeLib->vediAllegato($data['filepath'], true, $utf8decode, $headers);
            }
        }
    }

}

new Itafrontoffice_Download();
