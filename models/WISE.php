<?php
include_once "Courier.php";
include_once "Helper.php";

class WISE extends Courier
{
    //DB stuff
    private $courier_code;
    private $default_secret = "CNtxyXimNQKQGo1eEufxfOlwlC6kuZyR";
    private $agent_code = "dl_CNtxyXimNQKQGo1eEufx";

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

                $WISETOKEN = $this->getToken($data_raw);
                // die('token' . $WISETOKEN);
                if (!$WISETOKEN) {
                    $response_arr = array(
                        "orderNumber" => isset($data_raw->strOrderNo) ? $data_raw->strOrderNo : "",
                        "resMsg" => "Login Fail.apikey or apiSecret is not correct.",
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
                $data_string = json_encode($data_arr);
                // die('data_string: ' . $data_string);
                $url = 'https://api.wise-borders.com/waybill/add';
                $curl = curl_init($url);

                curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($curl, CURLOPT_POST, true);
                curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
                curl_setopt($curl, CURLOPT_POSTFIELDS, $data_string);
                curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-Type: application/json', 'Authorization: ' . $WISETOKEN, 'Content-Length: ' . strlen($data_string)));
                $curl_response = curl_exec($curl);

                if ($curl_response === false) {

                    curl_close($curl);
                    die("访问API失败");
                }
// die('response:' . $curl_response);
                $decoded_response = json_decode($curl_response);

                if (isset($decoded->response->errcode) && $decoded->response->errcode != '1000001') {
                    die('error occured: ' . $decoded->response->errmsg);
                }
                $wise_res_message = "";
                if ($decoded_response->errcode == "1000001") {
                    // success create order
                    $wise_res_message = $decoded_response->errmsg;
                } else {
                    // fail to create order
                    $wise_res_message = $decoded_response->errmsg;
                }

                $response_arr = array(
                    "orderNumber" => isset($decoded_response->waybillnumber) ? $decoded_response->waybillnumber : "",
                    "resMsg" => $wise_res_message,
                    "resCode" => 0,
                    "TaxAmount" => "not availiable for this courier",
                    "TaxCurrencyCode" => "not availiable for this courier",
                    "printUrl" => "",
                    "WISEOrderNo" => "",
                );

                return $response_arr;

            case 2:
                $WISETOKEN = $this->getToken($data_raw);
// die('token' . $WISETOKEN);
                if (!$WISETOKEN) {
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
                $WiseOderId = isset($data_raw->strOrderNo) ? Helper::cleanValue($data_raw->strOrderNo) : "";
                $url = 'https://api.wise-borders.com/waybill/getmodeloftrajectory';
                $curl = curl_init($url);
                $data_string = '{"tracknumber":"' . $WiseOderId . '"}';
                curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($curl, CURLOPT_POST, true);
                curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
                curl_setopt($curl, CURLOPT_POSTFIELDS, $data_string);
                curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-Type: application/json', 'Content-Length: ' . strlen($data_string), 'Authorization: ' . $WISETOKEN));

                $curl_response = curl_exec($curl);

// die('response:' . $curl_response);

                $decoded_response = json_decode($curl_response);

//die('auex order id: ' . $AuexOrderId);
                curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
// curl_setopt($curl, CURLOPT_POSTFIELDS, $data_string);
                curl_setopt($curl, CURLOPT_HTTPHEADER, array('Authorization: ' . 'Bearer ' . $WISETOKEN));

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
                    die(json_encode($decoded_response));
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
        $token_data_arr = array("apikey" => isset($data_raw->strShopCode) ? Helper::cleanValue($data_raw->strShopCode) : "", "apisecurity" => isset($data_raw->strSecretKey) ? Helper::cleanValue($data_raw->strSecretKey) : "");
//call api to get data
        $token_data_string = json_encode($token_data_arr);
        $token_url = 'https://api.wise-borders.com/login/jscodesession';
        $token_curl = curl_init($token_url);

        curl_setopt($token_curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($token_curl, CURLOPT_POST, true);
        curl_setopt($token_curl, CURLOPT_SSL_VERIFYPEER, false);
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
        if ($token_decoded_response->errcode === "1000001") {

            return "BasicAuth " . $token_decoded_response->session_key;
        } else {
            return " ";
        }

    }

    private function createRequestArray($data_raw)
    {
        $request_array = array(
            "waybillnumber" => isset($data_raw->strOrderNo) ? Helper::cleanValue($data_raw->strOrderNo) : "",
            "waybilltype" => 3,
            "expressline" => isset($data_raw->strEndDelivertyType) ? Helper::cleanValue($data_raw->strEndDelivertyType) : "年货迷你包专线@STO申通悉尼",
            "hairname" => isset($data_raw->strSenderName) ? Helper::cleanValue($data_raw->strSenderName) : "",
            "hairphone" => isset($data_raw->strSenderMobile) ? Helper::cleanValue($data_raw->strSenderMobile) : "",
            "hairadddress" => $this->getSenderAddress($data_raw),
            "receivingname" => isset($data_raw->strReceiverName) ? Helper::cleanValue($data_raw->strReceiverName) : "",
            "receivingphone" => isset($data_raw->strReceiverMobile) ? Helper::cleanValue($data_raw->strReceiverMobile) : "",
            "ReceiverProvince" => isset($data_raw->strReceiverProvince) ? Helper::cleanValue($data_raw->strReceiverProvince) : "",
            "receivingaddress" => $this->getReceiverAddress($data_raw),
            "packageweight" => isset($data_raw->strOrderWeight) ? Helper::cleanValue($data_raw->strOrderWeight) : "",
            "itemlist" => $this->getItems(isset($data_raw->items) ? $data_raw->items : ""),
        );

        return $request_array;
    }

    private function getItems($items)
    {
        $itemList = [];
        if (isset($items) && count($items) > 0) {
            foreach ($items as $item) {
                $goodsname = isset($item->strItemName) ? $item->strItemName : "";
                $number = isset($item->numItemQuantity) ? $item->numItemQuantity : "";
                array_push($itemList, ["goodsname" => $goodsname, "number" => $number]);
            }
        }
        return $itemList;
    }

    private function getTrackingList($trackList)
    {
        $formated_list = array();
        if (count($trackList) > 0) {
            foreach ($trackList as $trackListItem) {
                $new_node = array();

                $new_node['time'] = isset($trackListItem->time) ? Helper::cleanValue($trackListItem->time) : "";
                $new_node['status'] = isset($trackListItem->status) ? Helper::cleanValue($trackListItem->status) : "";

                array_push($formated_list, $new_node);
            }
        }
        return $formated_list;
    }

    private function getSenderAddress($data_raw)
    {
        $province = isset($data_raw->strSenderProvinceName) ? Helper::cleanValue($data_raw->strSenderProvinceName) : "";
        $city = isset($data_raw->strSenderCityName) ? Helper::cleanValue($data_raw->strSenderCityName) : "";
        $address = isset($data_raw->strSenderAddress) ? Helper::cleanValue($data_raw->strSenderAddress) : "";

        return $province . " " . $city . " " . $address;
    }

    private function getReceiverAddress($data_raw)
    {
        $province = isset($data_raw->strReceiverProvince) ? Helper::cleanValue($data_raw->strReceiverProvince) : "";
        $city = isset($data_raw->strReceiverCity) ? Helper::cleanValue($data_raw->strReceiverCity) : "";
        $district = isset($data_raw->strReceiverDistrict) ? Helper::cleanValue($data_raw->strReceiverDistrict) : "";
        $doorNo = isset($data_raw->strReceiverDoorNo) ? Helper::cleanValue($data_raw->strReceiverDoorNo) : "";

        return $province . " " . $city . " " . $district . " " . $doorNo;
    }

}
