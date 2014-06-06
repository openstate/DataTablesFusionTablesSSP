<?php
// config

//fill in the Goog Fusion Tables key
define(GF_API_KEY, 'xxxxxxxx');
// fill in the Google Fusion Tables table name
define(GF_API_TABLE, 'yyyyyyyyy');

// do not touch below unless you know what you're doing :)
include_once('FusionTablesSSP.class.php');

header("Access-Control-Allow-Origin: *");

$ssp = new FusionTablesSSP(GF_API_TABLE, GF_API_KEY);
echo json_encode($ssp->execute($_GET));
