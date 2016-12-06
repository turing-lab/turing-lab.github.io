<?php
require_once('Zend/Amf/Server.php');
require_once('DataCerebrex.php');

$server = new Zend_Amf_Server();
$server->setClass("DataCerebrex");
echo($server -> handle());

?>