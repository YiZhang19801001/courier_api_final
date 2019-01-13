<?php
include_once "./Courier.php";
include_once "./Helper.php";

class PX4 extends Courier
{
    //DB stuff
    private $courier_table = 'couriers';

    private $courier_code;

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
        $stmt->bindParam(':courier_code', '4PX');

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
        $stmt->bindParam(':courier_code', '4PX');
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
        $stmt->bindParam(':courier_code', "4PX");
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
                "ITEMS" => $this->getItemsHelper(isset($data_raw->items) ? $data_raw->items : [])
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
        return $curl_response;
    }
}
