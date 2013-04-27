<?php defined('BASEPATH') OR exit('No direct script access allowed');

header('Content-type: application/json');

echo json_encode($yield, JSON_NUMERIC_CHECK);