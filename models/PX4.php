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
                    "Token" => $this->getApiKey(),
                    "Data" => [
                        "ShipperOrderNo" => Helper::cleanValue($data_raw->strOrderNo),
                        "ServiceTypeCode" => Helper::cleanValue($data_raw->strServiceTypeCode),
                        "TerminalCode" => Helper::cleanValue($data_raw->strShopCode),
                        "ConsignerName" => Helper::cleanValue($data_raw->strSenderName),
                        "ConsignerMobile" => Helper::cleanValue($data_raw->strSenderMobile),
                        "ConsignerProvinceName" => Helper::cleanValue($data_raw->strSenderProvinceName),
                        "ConsignerCityName" => Helper::cleanValue($data_raw->strSenderCityName),
                        "ConsignerAddress" => Helper::cleanValue($data_raw->strSenderAddress),
                        "ConsignerPostCode" => Helper::cleanValue($data_raw->strSenderPostCode),
                        "ItemDeclareCurrency" => Helper::cleanValue($data_raw->strItemCurrency),
                        "ConsigneeName" => Helper::cleanValue($data_raw->strReceiverName),
                        "CountryISO2" => Helper::cleanValue($data_raw->strCountryISO2),
                        "Province" => Helper::cleanValue($data_raw->strReceiverProvince),
                        "City" => Helper::cleanValue($data_raw->strReceiverCity),
                        "District" => Helper::cleanValue($data_raw->strReceiverDistrict),
                        "ConsigneeStreetDoorNo" => Helper::cleanValue($data_raw->strReceiverDoorNo),
                        "ConsigneeMobile" => Helper::cleanValue($data_raw->strReceiverMobile),
                        "ConsigneeIDNumber" => Helper::cleanValue($data_raw->strReceiverIDNumber),
                        "ConsigneeIDFrontCopy" => Helper::cleanValue($data_raw->strReceiverIDFrontCopy),
                        "ConsigneeIDBackCopy" => Helper::cleanValue($data_raw->strReceiverIDBackCopy),
                        "OrderWeight" => Helper::cleanValue($data_raw->strOrderWeight),
                        "WeightUnit" => Helper::cleanValue($data_raw->strWeightUnit),
                        "EndDeliveryType" => Helper::cleanValue($data_raw->strEndDelivertyType),
                        "InsuranceTypeCode" => Helper::cleanValue($data_raw->strInsuranceTypeCode),
                        "InsuranceExpense" => Helper::cleanValue($data_raw->numInsuranceExpense),
                        "TraceSourceNumber" => Helper::cleanValue($data_raw->strTraceNumber),
                        "Remarks" => Helper::cleanValue($data_raw->strRemarks),
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
                "ItemSKU" => Helper::cleanValue($item->strItemSKU),
                "ItemDeclareType" => Helper::cleanValue($item->strItemDeclareType),
                "ItemName" => Helper::cleanValue($item->strItemName),
                "Specifications" => Helper::cleanValue($item->strItemSpecifications),
                "ItemQuantity" => Helper::cleanValue($item->numItemQuantity),
                "ItemBrand" => Helper::cleanValue($item->strItemBrand),
                "ItemUnitPrice" => Helper::cleanValue($item->numItemUnitPrice),
                "PreferentialSign" => Helper::cleanValue($item->strIsDiscounted),
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
            $new_node['location'] = Helper::cleanValue($list_item->TrackLocation);
            $new_node['time'] = Helper::cleanValue($list_item->TrackTime);
            $new_node['status'] = $this->translateStatus($list_item->TrackStatusCode);
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
