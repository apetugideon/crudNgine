<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

header("Access-Control-Allow-Origin: *");
header('Content-Type: text/json');
ini_set('memory_limit','1024M');

include('crudEngine/app-config.php');
include('utilities/renderer.php');
include('utilities/DbHandlers.php');
include('crudEngine/Entrance.php');

//print_r($_POST);
$to_rend = new renderer();
$initObj = new Entrance();
$retn    = $initObj->initAction();
if (is_array($retn) && !empty($retn)) echo $to_rend->render('json',$retn,'retn,<list></list>');
