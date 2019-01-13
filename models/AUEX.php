<?php
include_once "./Courier.php";
include_once "./Helper.php";

class CQCHS extends Courier
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
                $stock = $this->createStockString($data_raw);

                $wsdl = "http://www.zhonghuan.com.au:8085/API/cxf/au/recordservice?wsdl";
                $client = new SoapClient($wsdl, array('trace' => 1));

                $request_param = array(
                    "stock" => $stock,
                );

                try
                {
                    $response_string = json_encode($client->getRecord($request_param));
                    $response_json = json_decode($response_string);

                    //$responce_param =  $client->call("webservice_methode_name", $request_param); // Alternative way to call soap method

                    $response_arr = array(
                        "orderNumber" => $response_json->return->chrfydh,
                        "resMsg" => $response_json->return->backmsg,
                        "resCode" => $response_json->return->msgtype === "200" ? "0" : "1",
                        "TaxAmount" => "",
                        "TaxCurrencyCode" => "",
                    );
                } catch (Exception $e) {
                    $response_arr = array(
                        "orderNumber" => "",
                        "resMsg" => $e->getMessage(),
                        "resCode" => "1",
                        "TaxAmount" => "",
                        "TaxCurrencyCode" => "",
                    );

                }

                return $response_arr;

            default:
                # code...
                break;
        }

    }
}
