<?php
include_once "Courier.php";
include_once "Helper.php";

class EWE extends Courier
{
    protected $username = "API-TEST";
    protected $password = "DIM875439GYT892130";

    //Constructor with DB
    public function __construct($db, $request_type)
    {
        parent::__construct($db, $request_type);
    }

    public function getUrl()
    {
        switch ($this->request_type) {
            case 1:
                //test api url
                return "https://newomstest.ewe.com.au/eweApi/ewe/api/createOrder";
            // return "https://api.ewe.com.au/oms/api/createOrder";
            case 2:
                return "https://api.ewe.com.au/oms/api/tracking/ewepost";
            default:
                # code...
                break;
        }
    }

    private function makeResponseMsg($code)
    {

        switch ($code) {
            case 0:
                return 'SUCCESS';
            case 1:
                return 'FAILURE';
            case 2:
                return 'DUPLICATE ORDER';
            case 3:
                return 'INVALID OPERATION';

            default:
                # code...
                return 'FAILURE';
        }
    }

    public function callApi($data_raw)
    {
        $response_arr = array();

        switch ($this->request_type) {
            case 1:
                //map values
                $data_arr = array(
                    "USERNAME" => isset($data_raw->strShopCode) ? $data_raw->strShopCode : "",
                    "APIPASSWORD" => isset($data_raw->strSecretKey) ? $data_raw->strSecretKey : "",
                    "BoxNo" => isset($data_raw->strBoxNo) ? Helper::cleanValue($data_raw->strBoxNo) : "",
                    "REFERENCENO" => isset($data_raw->strReferenceNo) ? Helper::cleanValue($data_raw->strReferenceNo) : "",
                    "ExtraReferences" => [""],
                    "TotalPackage" => 1,
                    "Remark" => isset($data_raw->strRemark) ? Helper::cleanValue($data_raw->strRemark) : "",
                    "DeclaredWeight" => isset($data_raw->strOrderWeight) ? Helper::cleanValue($data_raw->strOrderWeight) : "",
                    "IsEconomic" => isset($data_raw->boolIsEconomic) ? Helper::cleanValue($data_raw->boolIsEconomic) : "",
                    "ContentType" => isset($data_raw->intContentType) ? Helper::cleanValue($data_raw->intContentType) : "",
                    "IsUseStock" => isset($data_raw->intIsUseStock) ? Helper::cleanValue($data_raw->intIsUseStock) : "",
                    "ValueAddedService" => isset($data_raw->strValueAddedService) ? Helper::cleanValue($data_raw->strValueAddedService) : "",
                    "Is3PL" => isset($data_raw->strIs3PL) ? Helper::cleanValue($data_raw->strIs3PL) : "N",
                    // "CustomerClientId" => isset($data_raw->strShopCode) ? Helper::cleanValue($data_raw->strShopCode) : "",
                    "CustomerClientId" => "",
                    "IsUseCcic" => isset($data_raw->intIsUseCcic) ? Helper::cleanValue($data_raw->intIsUseCcic) : "",
                    "auMerchantId" => isset($data_raw->strAuMerchantId) ? Helper::cleanValue($data_raw->strAuMerchantId) : "",
                    "DeclaredValue" => isset($data_raw->numDeclaredValue) ? Helper::cleanValue($data_raw->numDeclaredValue) : "",
                    "RealWeight" => isset($data_raw->numRealWeight) ? Helper::cleanValue($data_raw->numRealWeight) : "",
                    "OutBizCode" => isset($data_raw->strOutBizCode) ? Helper::cleanValue($data_raw->strOutBizCode) : "",
                    "Items" => $this->getItems($data_raw),
                    "Sender" => $this->getSender($data_raw),
                    "Receiver" => $this->getReceiver($data_raw),
                    "Payer" => $this->getPayer($data_raw),
                );

                //prepare request body
                // $data_string = '{' . $this->convertArrayToString($data_arr) . "}";
                $data_string = json_encode($data_arr);
                // $data_string = json_encode($data_arr);
                // build the post string here
                die($data_string);
                $url = $this->getUrl();
                $curl = curl_init($url);

                curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($curl, CURLOPT_POST, true);
                curl_setopt($curl, CURLOPT_POSTFIELDS, $data_string);
                curl_setopt($curl, CURLOPT_HTTPHEADER, array("content-type: application/javascript;charset=UTF-8"));

                $curl_response = curl_exec($curl);
                // die('die: ' . $curl_response);

                if ($curl_response === false) {
                    $info = curl_getinfo($curl);
                    curl_close($curl);
                    die('error occured during curl exec. Additioanl info: ' . var_export($info));
                }

                curl_close($curl);

                // decode json_string to json_object
                $decoded_response = json_decode($curl_response);
                // call model function to refactor the response data which will be used as part of response message to POS
                $res_msg = $this->makeResponseMsg(isset($decoded_response->Status) ? $decoded_response->Status : "999");
                // create reponse array for POS
                $response_arr = array(
                    "orderNumber" => isset($decoded_response->Payload->ORDERNO) ? $decoded_response->Payload->ORDERNO : "",
                    "resMsg" => $res_msg . '  ( ' . $decoded_response->Message . ' )',
                    "resCode" => isset($decoded_response->Status) ? $decoded_response->Status : "999",
                    "TaxAmount" => isset($decoded_response->Total) ? $decoded->Total : "",
                    "TaxCurrencyCode" => "",
                );

                return $response_arr;

            case 2:
                //map values
                $data_arr = array(
                    "querystring" => isset($data_raw->strOrderNo) ? Helper::cleanValue($data_raw->strOrderNo) : "",
                    "IsDetailed" => "true",
                );

//call api to get data?
                $data_string = json_encode($data_arr);

                $url = $this->getUrl();
                $curl = curl_init($url);

                curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($curl, CURLOPT_POST, true);
                curl_setopt($curl, CURLOPT_POSTFIELDS, $data_string);
                curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-Type: application/json', 'Content-Length: ' . strlen($data_string)));

                $curl_response = curl_exec($curl);

                if ($curl_response === false) {
                    $info = curl_getinfo($curl);
                    curl_close($curl);
                    die('error occured during curl exec. Additioanl info: ' . var_export($info));
                }

                curl_close($curl);

                $decoded_response = json_decode($curl_response);

                if (isset($decoded->response->status) && $decoded->response->status == 'ERROR') {
                    die('error occured: ' . $decoded->response->errormessage);
                }
                // die($curl_response);
                if (isset($decoded_response->Payload)) {
                    $res_arr = $this->makeTrackingResponseMsg($decoded_response->Payload);

                    $response_arr = array(
                        "orderNumber" => $res_arr['orderNumber'],
                        "resMsg" => $res_arr['message'],
                        "resCode" => $res_arr['code'],
                        "TrackingList" => isset($decoded_response->Data->TrackingList) ? $this->getTrackingList($decoded_response->Data->TrackingList) : [],
                    );

                } else {
                    $response_arr = array(
                        "orderNumber" => isset($data_raw->strOrderNo) ? $data_raw->strOrderNo : "",
                        "resMsg" => "courier api not available at the moment, please try later(运输公司服务器链接异常，请稍后再试)",
                        "resCode" => 'ERR99999',
                        "TrackingList" => []
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

    private function getItems($data)
    {

        $list_items = array();
        if (isset($data->items)) {
            foreach ($data->items as $item) {
                $list_item = array(
                    "Brand" => isset($item->strItemBrand) ? Helper::cleanValue($item->strItemBrand) : "",
                    "ItemName" => isset($item->strItemName) ? Helper::cleanValue($item->strItemName) : "",
                    "Quantity" => isset($item->numItemQuantity) ? Helper::cleanValue($item->numItemQuantity) : "",
                    "SKU" => isset($item->strItemSKU) ? Helper::cleanValue($item->strItemSKU) : "",
                    "Barcode" => "",
                    "Charge" => isset($item->numItemUnitPrice) ? Helper::cleanValue($item->numItemUnitPrice) : "",
                    "TotalCharge" => isset($item->numTotalPrice) ? Helper::cleanValue($item->numTotalPrice) : "",
                    "Spec" => isset($item->strItemSpecifications) ? Helper::cleanValue($item->strItemSpecifications) : "",
                    "Currency" => "",
                );

                array_push($list_items, $list_item);
            }

        }
        return $list_items;

    }

    private function makeTrackingResponseMsg($array)
    {
        $data = $array[0];
        $response_code = $data->DeliveryStatus == 0 ? "1" : "0";
        return array('orderNumber' => $data->EweNo, 'message' => $data->Reminder, 'code' => $response_code);
    }

    private function getSender($data)
    {
        $sender = array();

        $sender['Name'] = isset($data->strSenderName) ? Helper::cleanValue($data->strSenderName) : "";
        $sender['Email'] = isset($data->strSenderEmail) ? Helper::cleanValue($data->strSenderEmail) : "";
        $sender['Phone'] = isset($data->strSenderMobile) ? Helper::cleanValue($data->strSenderMobile) : "";
        $sender['Street'] = isset($data->strSenderAddress) ? Helper::cleanValue($data->strSenderAddress) : "";
        $sender['City'] = isset($data->strSenderCityName) ? Helper::cleanValue($data->strSenderCityName) : "";
        $sender['State'] = isset($data->strSenderProvinceName) ? Helper::cleanValue($data->strSenderProvinceName) : "";
        $sender['Suburb'] = "";
        $sender['Country'] = "AUD";
        $sender['Company'] = "";
        $sender['Postcode'] = isset($data->strSenderPostCode) ? Helper::cleanValue($data->strSenderPostCode) : "";
        $sender['SetDefault'] = '';

        return $sender;

    }

    private function getReceiver($data)
    {
        $receiver = array();

        $receiver['Name'] = isset($data->strReceiverName) ? Helper::cleanValue($data->strReceiverName) : "";
        $receiver['Email'] = isset($data->strReceiverEmail) ? Helper::cleanValue($data->strReceiverEmail) : "";
        $receiver['Phone'] = isset($data->strReceiverMobile) ? Helper::cleanValue($data->strReceiverMobile) : "";
        $receiver['Street'] = isset($data->strReceiverDoorNo) ? Helper::cleanValue($data->strReceiverDoorNo) : "";
        $receiver['City'] = isset($data->strReceiverCity) ? Helper::cleanValue($data->strReceiverCity) : "";
        $receiver['State'] = isset($data->strReceiverProvince) ? Helper::cleanValue($data->strReceiverProvince) : "";
        $receiver['Suburb'] = isset($data->strReceiverDistrict) ? Helper::cleanValue($data->strReceiverDistrict) : "";
        $receiver['Country'] = "AUD";
        $receiver['Company'] = "";
        $receiver['Postcode'] = isset($data->strSenderPostCode) ? Helper::cleanValue($data->strSenderPostCode) : "";
        $receiver['AliasName'] = '';
        $receiver['CardId'] = isset($data->strReceiverIDNumber) ? Helper::cleanValue($data->strReceiverIDNumber) : "";

        return $receiver;

    }

    private function getPayer($data)
    {

        // add code ...
        return json_decode("{}");
    }

    private function getTrackingList($trackingList)
    {
        $formated_list = array();
        foreach ($trackingList as $list_item) {
            $new_node = array();
            $new_node['location'] = isset($list_item->TrackLocation) ? Helper::cleanValue($list_item->TrackLocation) : "";
            $new_node['time'] = isset($list_item->TrackTime) ? Helper::cleanValue($list_item->TrackTime) : "";
            $new_node['status'] = $this->translateStatus($list_item->TrackStatusCode);
            array_push($formated_list, $new_node);
        }
        return $formated_list;
    }

    private function convertArrayToString($array)
    {
        $res = "";
        // \"ContentType\": \"\",\n
        foreach ($array as $key => $value) {
            if (!is_array($value)) {
                $res .= '\"' . $key . '\":' . '\"' . $value . '\",\n';

            } else {
                $res .= $this->convertArrayToString($value);
            }
        }

        return $res;
    }

}
