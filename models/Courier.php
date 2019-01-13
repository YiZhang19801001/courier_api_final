<?php

include_once './models/Helper.php';

class Courier
{

    //DB stuff
    private $conn;

    private $request_type;

    //Constructor with DB
    public function __construct($db, $request_type)
    {
        $this->conn = $db;
        $this->$request_type = $request_type;
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
        $Helper = new Helper;
        switch ($this->courier_code) {
            case '4PX':
                //map values
                $data_arr = array(
                    "Token" => $this->getApiKey(),
                    "Data" => [
                        "ShipperOrderNo" => isset($data_raw->strOrderNo) ? $Helper->cleanValue($data_raw->strOrderNo) : null,
                        "ServiceTypeCode" => isset($data_raw->strServiceTypeCode) ? $Helper->cleanValue($data_raw->strServiceTypeCode) : null,
                        "TerminalCode" => isset($data_raw->strShopCode) ? $Helper->cleanValue($data_raw->strShopCode) : null,
                        "ConsignerName" => isset($data_raw->strSenderName) ? $Helper->cleanValue($data_raw->strSenderName) : null,
                        "ConsignerMobile" => isset($data_raw->strSenderMobile) ? $Helper->cleanValue($data_raw->strSenderMobile) : null,
                        "ConsignerProvinceName" => isset($data_raw->strSenderProvinceName) ? $Helper->cleanValue($data_raw->strSenderProvinceName) : null,
                        "ConsignerCityName" => isset($data_raw->strSenderCityName) ? $Helper->cleanValue($data_raw->strSenderCityName) : null,
                        "ConsignerAddress" => isset($data_raw->strSenderAddress) ? $Helper->cleanValue($data_raw->strSenderAddress) : null,
                        "ConsignerPostCode" => isset($data_raw->strSenderPostCode) ? $Helper->cleanValue($data_raw->strSenderPostCode) : null,
                        "ItemDeclareCurrency" => isset($data_raw->strItemCurrency) ? $Helper->cleanValue($data_raw->strItemCurrency) : null,
                        "ConsigneeName" => isset($data_raw->strReceiverName) ? $Helper->cleanValue($data_raw->strReceiverName) : null,
                        "CountryISO2" => isset($data_raw->strCountryISO2) ? $Helper->cleanValue($data_raw->strCountryISO2) : null,
                        "Province" => isset($data_raw->strReceiverProvince) ? $Helper->cleanValue($data_raw->strReceiverProvince) : null,
                        "City" => isset($data_raw->strReceiverCity) ? $Helper->cleanValue($data_raw->strReceiverCity) : null,
                        "District" => isset($data_raw->strReceiverDistrict) ? $Helper->cleanValue($data_raw->strReceiverDistrict) : null,
                        "ConsigneeStreetDoorNo" => isset($data_raw->strReceiverDoorNo) ? $Helper->cleanValue($data_raw->strReceiverDoorNo) : null,
                        "ConsigneeMobile" => isset($data_raw->strReceiverMobile) ? $Helper->cleanValue($data_raw->strReceiverMobile) : null,
                        "ConsigneeIDNumber" => isset($data_raw->strReceiverIDNumber) ? $Helper->cleanValue($data_raw->strReceiverIDNumber) : null,
                        "ConsigneeIDFrontCopy" => isset($data_raw->strReceiverIDFrontCopy) ? $Helper->cleanValue($data_raw->strReceiverIDFrontCopy) : null,
                        "ConsigneeIDBackCopy" => isset($data_raw->strReceiverIDBackCopy) ? $Helper->cleanValue($data_raw->strReceiverIDBackCopy) : null,
                        "OrderWeight" => isset($data_raw->strOrderWeight) ? $Helper->cleanValue($data_raw->strOrderWeight) : null,
                        "WeightUnit" => isset($data_raw->strWeightUnit) ? $Helper->cleanValue($data_raw->strWeightUnit) : null,
                        "EndDeliveryType" => isset($data_raw->strEndDelivertyType) ? $Helper->cleanValue($data_raw->strEndDelivertyType) : null,
                        "InsuranceTypeCode" => isset($data_raw->strInsuranceTypeCode) ? $Helper->cleanValue($data_raw->strInsuranceTypeCode) : "",
                        "InsuranceExpense" => isset($data_raw->numInsuranceExpense) ? $Helper->cleanValue($data_raw->numInsuranceExpense) : null,
                        "TraceSourceNumber" => isset($data_raw->strTraceNumber) ? $Helper->cleanValue($data_raw->strTraceNumber) : null,
                        "Remarks" => isset($data_raw->strRemarks) ? $Helper->cleanValue($data_raw->strRemarks) : null,
                        "ITEMS" => $Helper->getItemsHelper(isset($data_raw->items) ? $data_raw->items : [])
                    ]

                );

                //call api to get data?
                $data_string = json_encode($data_arr);
                // die($data_string);
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
                return $curl_response;

            case 'AUEX':
                //map values
                $data_arr = array(
                    "Token" => $this->getApiKey(),
                    "Data" => [
                        "ShipperOrderNo" => isset($data_raw->strOrderNo) ? $Helper->cleanValue($data_raw->strOrderNo) : null,
                        "ServiceTypeCode" => isset($data_raw->strServiceTypeCode) ? $Helper->cleanValue($data_raw->strServiceTypeCode) : null,
                        "TerminalCode" => isset($data_raw->strShopCode) ? $Helper->cleanValue($data_raw->strShopCode) : null,
                        "ConsignerName" => isset($data_raw->strSenderName) ? $Helper->cleanValue($data_raw->strSenderName) : null,
                        "ConsignerMobile" => isset($data_raw->strSenderMobile) ? $Helper->cleanValue($data_raw->strSenderMobile) : null,
                        "ConsignerProvinceName" => isset($data_raw->strSenderProvinceName) ? $Helper->cleanValue($data_raw->strSenderProvinceName) : null,
                        "ConsignerCityName" => isset($data_raw->strSenderCityName) ? $Helper->cleanValue($data_raw->strSenderCityName) : null,
                        "ConsignerAddress" => isset($data_raw->strSenderAddress) ? $Helper->cleanValue($data_raw->strSenderAddress) : null,
                        "ConsignerPostCode" => isset($data_raw->strSenderPostCode) ? $Helper->cleanValue($data_raw->strSenderPostCode) : null,
                        "ItemDeclareCurrency" => isset($data_raw->strItemCurrency) ? $Helper->cleanValue($data_raw->strItemCurrency) : null,
                        "ConsigneeName" => isset($data_raw->strReceiverName) ? $Helper->cleanValue($data_raw->strReceiverName) : null,
                        "CountryISO2" => isset($data_raw->strCountryISO2) ? $Helper->cleanValue($data_raw->strCountryISO2) : null,
                        "Province" => isset($data_raw->strReceiverProvince) ? $Helper->cleanValue($data_raw->strReceiverProvince) : null,
                        "City" => isset($data_raw->strReceiverCity) ? $Helper->cleanValue($data_raw->strReceiverCity) : null,
                        "District" => isset($data_raw->strReceiverDistrict) ? $Helper->cleanValue($data_raw->strReceiverDistrict) : null,
                        "ConsigneeStreetDoorNo" => isset($data_raw->strReceiverDoorNo) ? $Helper->cleanValue($data_raw->strReceiverDoorNo) : null,
                        "ConsigneeMobile" => isset($data_raw->strReceiverMobile) ? $Helper->cleanValue($data_raw->strReceiverMobile) : null,
                        "ConsigneeIDNumber" => isset($data_raw->strReceiverIDNumber) ? $Helper->cleanValue($data_raw->strReceiverIDNumber) : null,
                        "ConsigneeIDFrontCopy" => isset($data_raw->strReceiverIDFrontCopy) ? $Helper->cleanValue($data_raw->strReceiverIDFrontCopy) : null,
                        "ConsigneeIDBackCopy" => isset($data_raw->strReceiverIDBackCopy) ? $Helper->cleanValue($data_raw->strReceiverIDBackCopy) : null,
                        "OrderWeight" => isset($data_raw->strOrderWeight) ? $Helper->cleanValue($data_raw->strOrderWeight) : null,
                        "WeightUnit" => isset($data_raw->strWeightUnit) ? $Helper->cleanValue($data_raw->strWeightUnit) : null,
                        "EndDeliveryType" => isset($data_raw->strEndDelivertyType) ? $Helper->cleanValue($data_raw->strEndDelivertyType) : null,
                        "InsuranceTypeCode" => isset($data_raw->strInsuranceTypeCode) ? $Helper->cleanValue($data_raw->strInsuranceTypeCode) : "",
                        "InsuranceExpense" => isset($data_raw->numInsuranceExpense) ? $Helper->cleanValue($data_raw->numInsuranceExpense) : null,
                        "TraceSourceNumber" => isset($data_raw->strTraceNumber) ? $Helper->cleanValue($data_raw->strTraceNumber) : null,
                        "Remarks" => isset($data_raw->strRemarks) ? $Helper->cleanValue($data_raw->strRemarks) : null,
                        "ITEMS" => $Helper->getItemsHelper($data_raw->items),
                    ],

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
                return $curl_response;
            default:
                # code...
                break;
        }

    }
}
