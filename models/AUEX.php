<?php
include_once "./Courier.php";
include_once "./Helper.php";

class AUEX extends Courier
{
    //DB stuff
    private $courier_code;

    //Constructor with DB
    public function __construct($db, $request_type)
    {
        parent::__construct($db, $request_type);
    }

    public function callApi($data_raw)
    {
        $response_arr = array();
        switch ($this->request_type) {
            case 1:

                $AUEXTOKEN = $this->getToken();
// die('token' . $AUEXTOKEN);
                //** get token end */

                $data_arr = $this->createRequestArray($data_raw);
// die('data_arr:' . json_encode($data_arr));
                //call api to get data
                $data_string = '[' . json_encode($data_arr) . ']';

// die('data_string: ' . $data_string);
                $url = 'http://aueapi.auexpress.com/api/AgentShipmentOrder/Create';
                $curl = curl_init($url);

                curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($curl, CURLOPT_POST, true);
                curl_setopt($curl, CURLOPT_POSTFIELDS, $data_string);
                curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-Type: application/json', 'Content-Length: ' . strlen($data_string), 'Authorization: ' . 'Bearer ' . $AUEXTOKEN));

                $curl_response = curl_exec($curl);

// die('response:' . $curl_response);

                $decoded_response = json_decode($curl_response);

                if (isset($decoded->response->status) && $decoded->response->status == 'ERROR') {
                    die('error occured: ' . $decoded->response->errormessage);
                }

                $auex_res_msg = "";
                if ($decoded_response->Code == 0) {
                    $auex_res_msg = $decoded_response->ReturnResult;
                } else {
                    $auex_res_msg = $decoded_response->Errors;
                }

                $response_arr = array(
                    "orderNumber" => isset($data_raw->strOrderNo) ? $data_raw->strOrderNo : "",
                    "resMsg" => $auex_res_msg,
                    "resCode" => $decoded_response->Code,
                    "TaxAmount" => "not availiable for this courier",
                    "TaxCurrencyCode" => "not availiable for this courier",
                );

                return $response_arr;

            default:
                # code...
                break;
        }

    }

    private function getToken()
    {
        //** get token */
        $token_data_arr = array("MemberId" => 2742, "Password" => 'A09062742');
//call api to get data
        $token_data_string = json_encode($token_data_arr);

        $token_url = 'http://auth.auexpress.com/api/token';
        $token_curl = curl_init($token_url);

        curl_setopt($token_curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($token_curl, CURLOPT_POST, true);
        curl_setopt($token_curl, CURLOPT_POSTFIELDS, $token_data_string);
        curl_setopt($token_curl, CURLOPT_HTTPHEADER, array('Content-Type: application/json', 'Content-Length: ' . strlen($token_data_string)));

        $token_curl_response = curl_exec($token_curl);

        if ($token_curl_response === false) {
            $token_info = curl_getinfo($token_curl);
            curl_close($token_curl);
            die('error occured during curl exec. Additioanl info: ' . var_export($token_info));
        }

        curl_close($token_curl);
        $token_decoded_response = json_decode($token_curl_response);

        return $token_decoded_response->Token;

    }

    private function createRequestArray($data_raw)
    {
        $request_array = array(
            "OrderId" => Helper::cleanValue($data_raw->strOrderNo),
            "MemberId" => 2742,
            "BrandId" => 1,
            // "TerminalCode" => isset($data_raw->strShopCode) ? $Helper->cleanValue($data_raw->strShopCode) : null,
            "SenderName" => Helper::cleanValue($data_raw->strSenderName),
            "SenderPhone" => Helper::cleanValue($data_raw->strSenderMobile),
            "SenderProvince" => Helper::cleanValue($data_raw->strSenderProvinceName),
            "SenderCity" => Helper::cleanValue($data_raw->strSenderCityName),
            "SenderAddr1" => Helper::cleanValue($data_raw->strSenderAddress),
            "SenderPostCode" => Helper::cleanValue($data_raw->strSenderPostCode),
            // "ItemDeclareCurrency" => isset($data_raw->strItemCurrency) ? $Helper->cleanValue($data_raw->strItemCurrency) : null,
            "ReceiverName" => Helper::cleanValue($data_raw->strReceiverName),
            "ReceiverPhone" => Helper::cleanValue($data_raw->strReceiverMobile),
            // "CountryISO2" => isset($data_raw->strCountryISO2) ? $Helper->cleanValue($data_raw->strCountryISO2) : null,
            "ReceiverProvince" => Helper::cleanValue($data_raw->strReceiverProvince),
            "ReceiverCity" => Helper::cleanValue($data_raw->strReceiverCity),
            // "District" => isset($data_raw->strReceiverDistrict) ? $Helper->cleanValue($data_raw->strReceiverDistrict) : null,
            "ReceiverAddr1" => Helper::cleanValue($data_raw->strReceiverDoorNo),
            "ReceiverEmail" => "",
            "ReceiverCountry" => "",
            "ReceiverPostCode" => "",
            "ReceiverPhotoId" => Helper::cleanValue($data_raw->strReceiverIDNumber),
            // "ConsigneeIDFrontCopy" => isset($data_raw->strReceiverIDFrontCopy) ? $Helper->cleanValue($data_raw->strReceiverIDFrontCopy) : null,
            // "ConsigneeIDBackCopy" => isset($data_raw->strReceiverIDBackCopy) ? $Helper->cleanValue($data_raw->strReceiverIDBackCopy) : null,
            "ChargeWeight" => Helper::cleanValue($data_raw->strOrderWeight),
            // "WeightUnit" => isset($data_raw->strWeightUnit) ? $Helper->cleanValue($data_raw->strWeightUnit) : null,
            // "EndDeliveryType" => isset($data_raw->strEndDelivertyType) ? $Helper->cleanValue($data_raw->strEndDelivertyType) : null,
            // "InsuranceTypeCode" => isset($data_raw->strInsuranceTypeCode) ? $Helper->cleanValue($data_raw->strInsuranceTypeCode) : "",
            // "InsuranceExpense" => isset($data_raw->numInsuranceExpense) ? $Helper->cleanValue($data_raw->numInsuranceExpense) : null,
            // "TraceSourceNumber" => isset($data_raw->strTraceNumber) ? $Helper->cleanValue($data_raw->strTraceNumber) : null,
            // "Marks" => isset($data_raw->strRemarks) ? $Helper->cleanValue($data_raw->strRemarks) : "",
            "ShipmentContent" => $this->getItems(isset($data_raw->items) ? $data_raw->items : ""),
            "ShipmentCustomContent" => "",
            "Value" => "",
            "IsPaid" => "",
            "PayTime" => "",
            "Marks" => "",
            "Volume" => "",
            "Notes" => "",
            "OrderTime" => "",
            "ShipmentStatus" => "",

        );

        return $request_array;
    }

    private function getItems($items)
    {
        $content_string = "";
        if (isset($items) && count($items) > 0) {
            foreach ($items as $item) {
                $orderItem = isset($item->strItemName) ? $item->strItemName : "";
                $quantity = isset($item->numItemQuantity) ? $item->numItemQuantity : "";
                $newOrderItem = $orderItem . '*' . $quantity;
                $content_string .= $newOrderItem;
            }
        }

        return $content_string;

    }

}
