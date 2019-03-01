<?php
include_once "Courier.php";
include_once "Helper.php";

class PX4 extends Courier
{
    //DB stuff
    private $courier_table = 'couriers';

    private $courier_code = '4PX';

    //Constructor with DB
    public function __construct($db, $request_type)
    {
        parent::__construct($db, $request_type);
    }

    public function getApiKey()
    {
        //create query
        $query = 'SELECT * FROM couriers WHERE code = :courier_code';

        //Prepare statement
        $stmt = $this->conn->prepare($query);

        // Bind Param
        $stmt->bindParam(':courier_code', $this->courier_code);

        //Execute query
        $stmt->execute();

        //$num = $stmt->rowCount();

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row['api_key'];
    }

    public function getUrl()
    {
        //create query
        $query = 'SELECT * FROM api_urls WHERE courier_code = :courier_code && request_type = :request_type';

        //Prepare statement
        $stmt = $this->conn->prepare($query);

        // Bind Param
        $stmt->bindParam(':courier_code', $this->courier_code);
        $stmt->bindParam(':request_type', $this->request_type);

        //Execute query
        $stmt->execute();

        //$num = $stmt->rowCount();

        $api_url = $stmt->fetch(PDO::FETCH_ASSOC);

        return $api_url['request_url'];
    }

    public function makeResponseMsg($code)
    {

        //create query
        $query = 'SELECT * FROM error_messages WHERE courier_code = :courier_code && request_type = :request_type && code = :code';

        //Prepare statement
        $stmt = $this->conn->prepare($query);

        // Bind Param
        $stmt->bindParam(':courier_code', $this->courier_code);
        $stmt->bindParam(':request_type', $this->request_type);
        $stmt->bindParam(':code', $code);

        //Execute query
        $stmt->execute();

        $num = $stmt->rowCount();

        if ($num > 0) {

            $row = $stmt->fetch(PDO::FETCH_ASSOC);

            //create returning oject
            $res_arr = array(
                'text' => $row['res_msg'],
                'code' => $row['res_code'],
            );
        } else {
            //create returning oject
            $res_arr = array(
                'text' => 'error! contact XXXX-XXX-XXX',
                'code' => 'ERR99999',
            );
        }

        return $res_arr;
    }

    public function callApi($data_raw)
    {
        $response_arr = array();
        switch ($this->request_type) {
            case 1:
                //map values
                $data_arr = array(
                    "Token" => isset($data_raw->strSecretKey) ? $data_raw->strSecretKey : $this->getApiKey(),
                    "Data" => [
                        "ShipperOrderNo" => isset($data_raw->strOrderNo) ? Helper::cleanValue($data_raw->strOrderNo) : "",
                        "ServiceTypeCode" => isset($data_raw->strServiceTypeCode) ? Helper::cleanValue($data_raw->strServiceTypeCode) : "",
                        "TerminalCode" => isset($data_raw->strShopCode) ? Helper::cleanValue($data_raw->strShopCode) : "",
                        "ConsignerName" => isset($data_raw->strSenderName) ? Helper::cleanValue($data_raw->strSenderName) : "",
                        "ConsignerMobile" => isset($data_raw->strSenderMobile) ? Helper::cleanValue($data_raw->strSenderMobile) : "",
                        "ConsignerProvinceName" => isset($data_raw->strSenderProvinceName) ? Helper::cleanValue($data_raw->strSenderProvinceName) : "",
                        "ConsignerCityName" => isset($data_raw->strSenderCityName) ? Helper::cleanValue($data_raw->strSenderCityName) : "",
                        "ConsignerAddress" => isset($data_raw->strSenderAddress) ? Helper::cleanValue($data_raw->strSenderAddress) : "",
                        "ConsignerPostCode" => isset($data_raw->strSenderPostCode) ? Helper::cleanValue($data_raw->strSenderPostCode) : "",
                        "ItemDeclareCurrency" => isset($data_raw->strItemCurrency) ? Helper::cleanValue($data_raw->strItemCurrency) : "",
                        "ConsigneeName" => isset($data_raw->strReceiverName) ? Helper::cleanValue($data_raw->strReceiverName) : "",
                        "CountryISO2" => isset($data_raw->strCountryISO2) ? Helper::cleanValue($data_raw->strCountryISO2) : "",
                        "Province" => isset($data_raw->strReceiverProvince) ? Helper::cleanValue($data_raw->strReceiverProvince) : "",
                        "City" => isset($data_raw->strReceiverCity) ? Helper::cleanValue($data_raw->strReceiverCity) : "",
                        "District" => isset($data_raw->strReceiverDistrict) ? Helper::cleanValue($data_raw->strReceiverDistrict) : "",
                        "ConsigneeStreetDoorNo" => isset($data_raw->strReceiverDoorNo) ? Helper::cleanValue($data_raw->strReceiverDoorNo) : "",
                        "ConsigneeMobile" => isset($data_raw->strReceiverMobile) ? Helper::cleanValue($data_raw->strReceiverMobile) : "",
                        "ConsigneeIDNumber" => isset($data_raw->strReceiverIDNumber) ? Helper::cleanValue($data_raw->strReceiverIDNumber) : "",
                        "ConsigneeIDFrontCopy" => isset($data_raw->strReceiverIDFrontCopy) ? Helper::cleanValue($data_raw->strReceiverIDFrontCopy) : "",
                        "ConsigneeIDBackCopy" => isset($data_raw->strReceiverIDBackCopy) ? Helper::cleanValue($data_raw->strReceiverIDBackCopy) : "",
                        "OrderWeight" => isset($data_raw->strOrderWeight) ? Helper::cleanValue($data_raw->strOrderWeight) : "",
                        "WeightUnit" => isset($data_raw->strWeightUnit) ? Helper::cleanValue($data_raw->strWeightUnit) : "",
                        "EndDeliveryType" => isset($data_raw->strEndDelivertyType) ? Helper::cleanValue($data_raw->strEndDelivertyType) : "",
                        "InsuranceTypeCode" => isset($data_raw->strInsuranceTypeCode) ? Helper::cleanValue($data_raw->strInsuranceTypeCode) : "",
                        "InsuranceExpense" => isset($data_raw->numInsuranceExpense) ? Helper::cleanValue($data_raw->numInsuranceExpense) : "",
                        "TraceSourceNumber" => isset($data_raw->strTraceNumber) ? Helper::cleanValue($data_raw->strTraceNumber) : "",
                        "Remarks" => isset($data_raw->strRemarks) ? Helper::cleanValue($data_raw->strRemarks) : "",
                        "ITEMS" => $this->getItems(isset($data_raw->items) ? $data_raw->items : [])
                    ]

                );

                //prepare request body
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

                // decode json_string to json_object
                $decoded_response = json_decode($curl_response);
                // call model function to refactor the response data which will be used as part of response message to POS
                $res_arr = $this->makeResponseMsg($decoded_response->ResponseCode);
                // create reponse array for POS
                $response_arr = array(
                    "orderNumber" => isset($decoded_response->Data) ? $decoded_response->Data : "",
                    "resMsg" => $res_arr['text'] . '  ( ' . $decoded_response->Message . ' )',
                    "resCode" => $res_arr['code'],
                    "TaxAmount" => isset($decoded_response->TaxAmount) ? $decoded_response->UnionOrderNumber : "",
                    "TaxCurrencyCode" => isset($decoded_response->CurrencyCodeTax) ? $decoded_response->CurrencyCodeTax : "",
                    "printUrl" => "",
                    "EWEOrderNo" => "",
                );

                return $response_arr;

            case 2:
                //map values
                $data_arr = array(
                    "Token" => $this->getApiKey(),
                    "Data" => ["ShipperOrderNo" => Helper::cleanValue($data_raw->strOrderNo)],
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

                $res_arr = $this->makeResponseMsg($decoded_response->ResponseCode);

                $response_arr = array(
                    "orderNumber" => isset($decoded_response->Data->ShipperOrderNo) ? $decoded_response->Data->ShipperOrderNo : "",
                    "resMsg" => $res_arr['text'],
                    "resCode" => $res_arr['code'],
                    "TrackingList" => isset($decoded_response->Data->TrackingList) ? $this->getTrackingList($decoded_response->Data->TrackingList) : [],
                );

                return $response_arr;

            case 3:
                //map values
                $data_arr = array(
                    "Token" => $this->getApiKey(),
                    "Data" => ["ReferenceNumber" => Helper::cleanValue($data_raw->strOrderNo)],
                );

//call api to get data?
                $data_string = json_encode($data_arr);

                $url = $courier->getUrl();
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

                $res_arr = $this->makeResponseMsg($decoded_response->ResponseCode);

                $response_arr = array(
                    "orderNumber" => Helper::cleanValue($data_raw->strOrderNo),
                    "resMsg" => $res_arr['text'],
                    "resCode" => $res_arr['code'],
                );
                return $response_arr;

            default:
                # code...
                break;
        }
    }

    private function getItems($arr_item)
    {
        $list_items = array();
        foreach ($arr_item as $item) {
            $list_item = array(
                "ItemSKU" => isset($item->strItemSKU) ? Helper::cleanValue($item->strItemSKU) : "",
                "ItemDeclareType" => isset($item->strItemDeclareType) ? Helper::cleanValue($item->strItemDeclareType) : "",
                "ItemName" => isset($item->strItemName) ? Helper::cleanValue($item->strItemName) : "",
                "Specifications" => isset($item->strItemSpecifications) ? Helper::cleanValue($item->strItemSpecifications) : "",
                "ItemQuantity" => isset($item->numItemQuantity) ? Helper::cleanValue($item->numItemQuantity) : "",
                "ItemBrand" => isset($item->strItemBrand) ? Helper::cleanValue($item->strItemBrand) : "",
                "ItemUnitPrice" => isset($item->numItemUnitPrice) ? Helper::cleanValue($item->numItemUnitPrice) : "",
                "PreferentialSign" => isset($item->strIsDiscounted) ? Helper::cleanValue($item->strIsDiscounted) : "",
            );

            array_push($list_items, $list_item);
        }
        return $list_items;

    }

    private function getTrackingList($trackingList)
    {
        $formated_list = array();
        foreach ($trackingList as $list_item) {
            $new_node = array();
            $new_node['location'] = isset($list_item->TrackLocation) ? Helper::cleanValue($list_item->TrackLocation) : "";
            $new_node['time'] = isset($list_item->TrackTime) ? Helper::cleanValue($list_item->TrackTime) : "";
            $new_node['status'] = isset($list_item->TrackStatusCode) ? $this->translateStatus($list_item->TrackStatusCode) : "";
            array_push($formated_list, $new_node);
        }
        return $formated_list;
    }

    private function translateStatus($code)
    {
        switch ($code) {
            case 'PU':
                return "The goods have been taken from the sender";
            case 'CL':
                return "Site collection";
            case 'AO':
                return "arrived oversea warehouse";
            case 'OC':
                return "operation complete";
            case 'LO':
                return "leave oversea warehouse";
            case 'FT':
                return "departure";
            case 'FL':
                return "arrived";
            case 'TRM':
                return "Being sent to customs clearance port";
            case 'CCE':
                return "clearance port complete";
            case 'OK':
                return "Delivery Complete";
            case 'CP':
                return "await";
            case 'CCMC':
                return "product lost";
            case 'CCSD':
                return "The goods have been destroyed";
            case 'HC':
                return "Customs fastener";
            case 'IDCS':
                return "ID card information collection";
            case 'IS':
                return "Handed over domestic delivery service provider";
            case 'PL':
                return "Internal operation of the operation center";
            case 'PO':
                return "Overseas warehouse made orders";
            case 'RT':
                return "The goods have been returned to the place of delivery";
            case 'SD':
                return "Damaged goods";
            case 'SH':
                return "Temporary deduction of goods";
            case 'PTW':
                return "The parcel is taken from the airport and transferred to the customs supervision warehouse.";
            case 'WA':
                return "Waiting to arrange a flight";
            case 'WT':
                return "Waiting for a transfer";
            case "WD":
                return "Waiting for customs clearance";
            default:
                return "unkown status";
        }

    }
}
