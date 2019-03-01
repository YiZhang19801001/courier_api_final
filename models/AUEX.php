<?php
include_once "Courier.php";
include_once "Helper.php";

class AUEX extends Courier
{
    //DB stuff
    private $courier_code;
    private $default_secret = "A09062742";
    private $agent_code = "2742";

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

                $AUEXTOKEN = $this->getToken($data_raw);
// die('token' . $AUEXTOKEN);
                if (!$AUEXTOKEN) {
                    $response_arr = array(
                        "orderNumber" => isset($data_raw->strOrderNo) ? $data_raw->strOrderNo : "",
                        "resMsg" => "Login Fail.password or username is not correct.",
                        "resCode" => 1,
                        "TaxAmount" => "not availiable for this courier",
                        "TaxCurrencyCode" => "not availiable for this courier",
                    );

                    return $response_arr;
                }
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
                    $auex_res_msg = $decoded_response->Errors[0]->Message;
                }

                $response_arr = array(
                    "orderNumber" => isset($data_raw->strOrderNo) ? $data_raw->strOrderNo : "",
                    "resMsg" => $auex_res_msg,
                    "resCode" => $decoded_response->Code,
                    "TaxAmount" => "not availiable for this courier",
                    "TaxCurrencyCode" => "not availiable for this courier",
                    "printUrl" => "",
                    "EWEOrderNo" => "",
                );

                return $response_arr;

            case 2:
                $AUEXTOKEN = $this->getToken($data_raw);
// die('token' . $AUEXTOKEN);
                if (!$AUEXTOKEN) {
                    $response_arr = array(
                        "orderNumber" => isset($data_raw->strOrderNo) ? $data_raw->strOrderNo : "",
                        "resMsg" => "Login Fail.password or username is not correct.",
                        "resCode" => "1",
                        "TaxAmount" => "not availiable for this courier",
                        "TaxCurrencyCode" => "not availiable for this courier",
                    );

                    return $response_arr;
                }

// die('data_arr:' . json_encode($data_arr));
                //call api to get data

// die('data_string: ' . $data_string);
                $AuexOrderId = isset($data_raw->strOrderNo) ? Helper::cleanValue($data_raw->strOrderNo) : "";
                $url = 'http://aueapi.auexpress.com/api/ShipmentOrderTrack?OrderId=' . $AuexOrderId;
                $curl = curl_init($url);

//die('auex order id: ' . $AuexOrderId);
                curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
// curl_setopt($curl, CURLOPT_POSTFIELDS, $data_string);
                curl_setopt($curl, CURLOPT_HTTPHEADER, array('Authorization: ' . 'Bearer ' . $AUEXTOKEN));

                $curl_response = curl_exec($curl);

// die('response:' . $curl_response);

                if ($curl_response == "") {
                    $response_arr = array(
                        "orderNumber" => isset($data_raw->strOrderNo) ? Helper::cleanValue($data_raw->strOrderNo) : "",
                        "resMsg" => 'no found',
                        "resCode" => '1',
                        "TrackingList" => [],
                    );
                } else {
                    if (isset($decoded->response->status) && $decoded->response->status == 'ERROR') {
                        die('error occured: ' . $decoded->response->errormessage);
                    }
                    $decoded_response = json_decode($curl_response);

                    $response_arr = array(
                        "orderNumber" => isset($data_raw->strOrderNo) ? Helper::cleanValue($data_raw->strOrderNo) : "",
                        "resMsg" => isset($decoded_response->ReturnResult) ? $decoded_response->ReturnResult : "",
                        "resCode" => isset($decoded_response->Code) ? $decoded_response->Code : "",
                        "TrackingList" => isset($decoded_response->TrackList) ? $this->getTrackingList($decoded_response->TrackList) : [],
                    );

                }
                return $response_arr;

            case 3:
                $response_arr = array(
                    "orderNumber" => isset($data_raw->strOrderNo) ? $data_raw->strOrderNo : "",
                    "resMsg" => "method not allow, please check your courier name(运输公司名未开放该服务，请检查您提交的运输公司名)",
                    "resCode" => 'ERR99999',
                    "TrackingList" => []
                );

                return $response_arr;

            default:
                # code...
                break;
        }

    }

    private function getToken($data_raw)
    {
        //** get token */
        $token_data_arr = array("MemberId" => isset($data_raw->strShopCode) ? Helper::cleanValue($data_raw->strShopCode) : "", "Password" => isset($data_raw->strSecretKey) ? Helper::cleanValue($data_raw->strSecretKey) : "");
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
            "OrderId" => isset($data_raw->strOrderNo) ? Helper::cleanValue($data_raw->strOrderNo) : "",
            "MemberId" => isset($data_raw->strShopCode) ? Helper::cleanValue($data_raw->strShopCode) : "",
            "BrandId" => 1,
            // "TerminalCode" => isset($data_raw->strShopCode) ? $Helper->cleanValue($data_raw->strShopCode) : null,
            "SenderName" => isset($data_raw->strSenderName) ? Helper::cleanValue($data_raw->strSenderName) : "",
            "SenderPhone" => isset($data_raw->strSenderMobile) ? Helper::cleanValue($data_raw->strSenderMobile) : "",
            "SenderProvince" => isset($data_raw->strSenderProvinceName) ? Helper::cleanValue($data_raw->strSenderProvinceName) : "",
            "SenderCity" => isset($data_raw->strSenderCityName) ? Helper::cleanValue($data_raw->strSenderCityName) : "",
            "SenderAddr1" => isset($data_raw->strSenderAddress) ? Helper::cleanValue($data_raw->strSenderAddress) : "",
            "SenderPostCode" => isset($data_raw->strSenderPostCode) ? Helper::cleanValue($data_raw->strSenderPostCode) : "",
            // "ItemDeclareCurrency" => isset($data_raw->strItemCurrency) ? $Helper->cleanValue($data_raw->strItemCurrency) : null,
            "ReceiverName" => isset($data_raw->strReceiverName) ? Helper::cleanValue($data_raw->strReceiverName) : "",
            "ReceiverPhone" => isset($data_raw->strReceiverMobile) ? Helper::cleanValue($data_raw->strReceiverMobile) : "",
            // "CountryISO2" => isset($data_raw->strCountryISO2) ? $Helper->cleanValue($data_raw->strCountryISO2) : null,
            "ReceiverProvince" => isset($data_raw->strReceiverProvince) ? Helper::cleanValue($data_raw->strReceiverProvince) : "",
            "ReceiverCity" => isset($data_raw->strReceiverCity) ? Helper::cleanValue($data_raw->strReceiverCity) : "",
            // "District" => isset($data_raw->strReceiverDistrict) ? $Helper->cleanValue($data_raw->strReceiverDistrict) : null,
            "ReceiverAddr1" => isset($data_raw->strReceiverDoorNo) ? Helper::cleanValue($data_raw->strReceiverDoorNo) : "",
            "ReceiverEmail" => "",
            "ReceiverCountry" => "",
            "ReceiverPostCode" => "2127",
            "ReceiverPhotoId" => isset($data_raw->strReceiverIDNumber) ? Helper::cleanValue($data_raw->strReceiverIDNumber) : "",
            // "ConsigneeIDFrontCopy" => isset($data_raw->strReceiverIDFrontCopy) ? $Helper->cleanValue($data_raw->strReceiverIDFrontCopy) : null,
            // "ConsigneeIDBackCopy" => isset($data_raw->strReceiverIDBackCopy) ? $Helper->cleanValue($data_raw->strReceiverIDBackCopy) : null,
            "ChargeWeight" => isset($data_raw->strOrderWeight) ? Helper::cleanValue($data_raw->strOrderWeight) : "",
            // "WeightUnit" => isset($data_raw->strWeightUnit) ? $Helper->cleanValue($data_raw->strWeightUnit) : null,
            // "EndDeliveryType" => isset($data_raw->strEndDelivertyType) ? $Helper->cleanValue($data_raw->strEndDelivertyType) : null,
            // "InsuranceTypeCode" => isset($data_raw->strInsuranceTypeCode) ? $Helper->cleanValue($data_raw->strInsuranceTypeCode) : "",
            // "InsuranceExpense" => isset($data_raw->numInsuranceExpense) ? $Helper->cleanValue($data_raw->numInsuranceExpense) : null,
            // "TraceSourceNumber" => isset($data_raw->strTraceNumber) ? $Helper->cleanValue($data_raw->strTraceNumber) : null,
            "Marks" => isset($data_raw->strRemarks) ? Helper::cleanValue($data_raw->strRemarks) : "",
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

    private function getTrackingList($trackList)
    {
        $formated_list = array();
        if (count($trackList) > 0) {
            foreach ($trackList as $trackListItem) {
                $new_node = array();
                $new_node['location'] = isset($trackListItem->Location) ? Helper::cleanValue($trackListItem->Location) : "";
                $new_node['time'] = isset($trackListItem->StatusTime) ? Helper::cleanValue($trackListItem->StatusTime) : "";
                $new_node['status'] = isset($trackListItem->StatusDetail) ? Helper::cleanValue($trackListItem->StatusDetail) : "";

                array_push($formated_list, $new_node);
            }
        }
        return $formated_list;
    }

}
