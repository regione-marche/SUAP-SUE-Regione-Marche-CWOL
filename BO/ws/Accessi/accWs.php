<?php

require_once('../lib/wsServer.class.php');
wsServer::load();
//
// Dichiaro i namespaces da usare
//
define("NAME_SPACE", "http://www.italsoft-mc.it/ws/accWs");
define("DEFAULT_NAME_SPACE", "http://www.w3.org/2001/XMLSchema");

$name = "praWsFO";
/* @var $server wsServer */
$server = wsServer::getWsServerInstance($name, NAME_SPACE);

// Use the request to (try to) invoke the service
$HTTP_RAW_POST_DATA = isset($HTTP_RAW_POST_DATA) ? $HTTP_RAW_POST_DATA : '';
$server->service($HTTP_RAW_POST_DATA);

