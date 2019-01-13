<?php
//headers
header('Access-Control-Allow-Origin:*');
header('Content-Type: application/json');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Access-Control-Allow-Headers,Content-Type,Access-Control-Allow-Methods, Authorization, X-Requested-With');

include_once './config/Database.php';
include_once './models/Courier.php';
include_once './models/Helper.php';
// Turn off all error reporting
// error_reporting(0);

$dateTimeForLogger = Helper::getDateTime();

$myFile = "./log/create/logger $dateTimeForLogger->date.json";

// write POS request body into logger
$request_body = file_get_contents("php://input");
Helper::logger($dateTimeForLogger, $myFile, 'before decode', $request_body);

//get raw posted data
$data_raw = json_decode(file_get_contents("php://input"));
// write Decode json object to logger
Helper::logger($dateTimeForLogger, $myFile, 'after decode', $data_raw);

//instantiate DB & connect
$database = new Database();
$db = $database->connect();

// get courier name from request to determine which instance to create and which process to apply
$courier_name = isset($data_raw->strProviderCode) ? $data_raw->strProviderCode : '';

// json result container
$response_arr = array();
// courier instance container
$courier;

switch ($courier_name) {
    case '4PX':
        $courier = new PX4($db, 1);
        break;
    case 'CQCHS':
        $courier = new CQCHS($db, 1);
        break;
    case 'AUEX':
        $courier = new AUEX($db, 1);
        break;
    case 'EWE':
        $courier = new EWE($db, 1);
        break;

    default:
        $courier = new UnknowCourier();
        break;
}

// call api to finish the request
$curl_response = $courier->callApi($data_raw);
$decoded_response = json_decode($curl_response);

$res_arr = $courier->makeResponseMsg($decoded_response->ResponseCode);

$response_arr = array(
    "orderNumber" => isset($decoded_response->Data) ? $decoded_response->Data : "",
    "resMsg" => $res_arr['text'] . '  ( ' . $decoded_response->Message . ' )',
    "resCode" => $res_arr['code'],
    "TaxAmount" => isset($decoded_response->TaxAmount) ? $decoded_response->UnionOrderNumber : "",
    "TaxCurrencyCode" => isset($decoded_response->CurrencyCodeTax) ? $decoded_response->CurrencyCodeTax : "",
);

// encode response objet to json_string
$final_response = json_encode($response_arr);

// write api response data to logger
Helper::logger($dateTimeForLogger, $myFile, 'finish request', $response_arr);

// return response to POS
echo $final_response;
