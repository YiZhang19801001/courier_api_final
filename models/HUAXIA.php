<?php
include_once "Courier.php";
include_once "Helper.php";

class HUAXIA extends Courier
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

                return "http://www.uschuaxia.com/third/customer/placeorder";

            case 2:
                return "http://www.uschuaxia.com/third/customer/order/select";
            default:
                # code...
                break;
        }
    }

    public function callApi($data_raw)
    {
        $response_arr = array();

        switch ($this->request_type) {
            case 1:
                //map values
                $data_arr = array(
                    "tid" => isset($data_raw->strShopCode) ? $data_raw->strShopCode : "",
                    "tkey" => isset($data_raw->strSecretKey) ? $data_raw->strSecretKey : "",
                    // "make" => isset($data_raw->strRemark) ? Helper::cleanValue($data_raw->strRemark) : "",
                    "re_name" => isset($data_raw->strReceiverName) ? Helper::cleanValue($data_raw->strReceiverName) : "",
                    "re_tel" => isset($data_raw->strReceiverMobile) ? Helper::cleanValue($data_raw->strReceiverMobile) : "",
                    "re_province" => isset($data_raw->strReceiverProvince) ? Helper::cleanValue($data_raw->strReceiverProvince) : "",
                    "re_city" => isset($data_raw->strReceiverCity) ? Helper::cleanValue($data_raw->strReceiverCity) : "",
                    "re_addre" => isset($data_raw->strReceiverDoorNo) ? $data_raw->strReceiverDistrict . ' ' . $data_raw->strReceiverDoorNo : "",
                    "re_logistics" => '顺丰专线路线',
                    "sender_name" => isset($data_raw->strSenderName) ? Helper::cleanValue($data_raw->strSenderName) : "",
                    "sender_tel" => isset($data_raw->strSenderMobile) ? Helper::cleanValue($data_raw->strSenderMobile) : "",
                    "sender_addre" => isset($data_raw->strSenderAddress) ? Helper::cleanValue($data_raw->strSenderAddress) : "",
                    "sender_country" => 'Australia',
                    "make" => isset($data_raw->strRemarks) ? Helper::cleanValue($data_raw->strRemarks) : "",
                    "goods" => null,
                    "goodsnew" => $this->getItems(isset($data_raw->items) ? $data_raw->items : []),
                    "order_no" => isset($data_raw->strOrderNo) ? Helper::cleanValue($data_raw->strOrderNo) : ""
                );

                //prepare request body
                $data_string = $this->makeRequestBody($data_arr);
                // $data_string = json_encode($data_arr);
                // build the post string here
                $url = $this->getUrl();
                $curl = curl_init($url);
                // die($data_string);
                curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($curl, CURLOPT_POST, true);
                curl_setopt($curl, CURLOPT_POSTFIELDS, $data_string);
                curl_setopt($curl, CURLOPT_HTTPHEADER, array("content-type: application/x-www-form-urlencoded;charset=UTF-8"));

                $curl_response = curl_exec($curl);
                // die('die: ' . $curl_response);

                if ($curl_response === false) {
                    $info = curl_getinfo($curl);
                    curl_close($curl);
                    return array(
                        "orderNumber" => $data_raw->strOrderNo,
                        "resMsg" => json_encode($info),
                        "resCode" => "1",
                        "TaxAmount" => "",
                        "TaxCurrencyCode" => "",
                        "printUrl" => "",
                        "EWEOrderNo" => "",
                    );

                    die('error occured during curl exec. Additioanl info: ' . var_export($info));
                }

                curl_close($curl);

                // create reponse array for POS
                $response_arr = $this->createResponseBody($curl_response);

                return $response_arr;

            case 2:
                //map values
                $data_arr = array(
                    "tid" => isset($data_raw->strShopCode) ? $data_raw->strShopCode : "",
                    "tkey" => isset($data_raw->strSecretKey) ? $data_raw->strSecretKey : "",
                    "orderno" => isset($data_raw->strOrderNo) ? $data_raw->strOrderNo : "",
                );

                $data_string = $this->makeRequestBody($data_arr);
                $url = $this->getUrl();
                $curl = curl_init($url);

                curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($curl, CURLOPT_POST, true);
                curl_setopt($curl, CURLOPT_POSTFIELDS, $data_string);
                curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-Type: application/x-www-form-urlencoded', 'Content-Length: ' . strlen($data_string)));

                $curl_response = curl_exec($curl);
                // die($curl_response);
                if ($curl_response === false) {
                    $info = curl_getinfo($curl);
                    curl_close($curl);
                    return array(
                        "orderNumber" => isset($data_raw->strOrderNo) ? $data_raw->strOrderNo : "",
                        "resMsg" => "api not available",
                        "resCode" => "Error 404",
                    );
                    // die('error occured during curl exec. Additioanl info: ' . var_export($info));
                }

                curl_close($curl);

                $decoded_response = json_decode($curl_response);
                // die('abc');
                if (isset($decoded->response->status) && $decoded->response->status == 'ERROR') {
                    die('error occured: ' . $decoded->response->errormessage);
                }
                if ($decoded_response[0]->key == 1) {
                    // var_dump($decoded_response);

                    $response_arr = array(
                        "orderNumber" => isset($data_raw->strOrderNo) ? $data_raw->strOrderNo : "",
                        "resMsg" => $decoded_response[0]->msg,
                        "resCode" => "0",
                        "TrackingList" => $this->makeTrackingResponseMsg($decoded_response[1][0]->zOrderNode),
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

        $list_items = "";

        foreach ($data as $item) {
            $Brand = isset($item->strItemBrand) ? Helper::cleanValue($item->strItemBrand) : "";
            $ItemName = isset($item->strItemName) ? Helper::cleanValue($item->strItemName) : "";
            $Quantity = isset($item->numItemQuantity) ? Helper::cleanValue($item->numItemQuantity) : "";
            $Spec = isset($item->strItemSpecifications) ? Helper::cleanValue($item->strItemSpecifications) : "";

            if ($list_items === "") {
                $list_items = $list_items . $ItemName . '{{a}}' . $Brand . '{{a}}' . $Spec . '{{a}}' . $Quantity;

            } else {
                $list_items = $list_items . '{{b}}' . $ItemName . '{{a}}' . $Brand . '{{a}}' . $Spec . '{{a}}' . $Quantity;
            }

        }

        return $list_items;

    }

    private function makeRequestBody($data)
    {
        // var_dump($data);
        $resultString = "";
        foreach ($data as $key => $value) {
            if ($resultString == "") {
                $resultString .= $key . '=' . $value;

            } else {
                $resultString .= '&' . $key . '=' . $value;
            }
        }

        return $resultString;
    }

    private function createResponseBody($data)
    {

        die($data);
        $decoded_response = json_decode($data);

        $key = $decoded_response[0]->key;
        $msg = $decoded_response[0]->msg;
        $orderNo = "";
        $company = "";

        if ($key == 1) {
            $orderNo = $decoded_response[1]->orderNo;
            $company = $decoded_response[1]->company;
        }

        return array(
            "orderNumber" => $orderNo,
            "resMsg" => $msg,
            "resCode" => $key == 1 ? "0" : "1",
            "TaxAmount" => "not avaliable for" . $company . '. ',
            "TaxCurrencyCode" => "",
            "printUrl" => isset($decoded_response->Payload->PrintURL) ? $decoded_response->Payload->PrintURL : "",
            "EWEOrderNo" => isset($decoded_response->Payload->ORDERNO) ? $decoded_response->Payload->ORDERNO : "",
        );

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

    private function makeTrackingResponseMsg($array)
    {
        $msg = "";
        $index = 1;
        foreach ($array as $value) {
            $msg = $index . ". 订单状态: " . $value->node_name . "日期时间: " . $value->node_time . ";";
        }

        return $msg;
    }

}
