<?php
include_once "Courier.php";
include_once "Helper.php";

class CQCHS extends Courier
{
    //DB stuff
    private $courier_code;

    //Constructor with DB
    public function __construct($db, $request_type)
    {
        // $request_type [1=>createorder 2=>trackorder]
        parent::__construct($db, $request_type);
    }

    public function callApi($data_raw)
    {
        $response_arr = array();
        switch ($this->request_type) {
            case 1:
                // create web stock request

                try
                {
                    $stock = $this->createStockString($data_raw);
                    $wsdl = "http://www.zhonghuan.com.au:8085/API/cxf/au/recordservice?wsdl";

                    $client = new SoapClient($wsdl, array('trace' => 1));
                    $request_param = array(
                        "stock" => $stock,
                    );

                    // call external api
                    $response_string = json_encode($client->getRecord($request_param));
                    // decode response data
                    $response_json = json_decode($response_string);

                    //$responce_param =  $client->call("webservice_methode_name", $request_param); // Alternative way to call soap method

                    // generate response message for POS
                    $response_arr = array(
                        "orderNumber" => $response_json->return->chrfydh,
                        "resMsg" => $response_json->return->backmsg,
                        "resCode" => $response_json->return->msgtype === "200" ? "0" : "1",
                        "TaxAmount" => "",
                        "TaxCurrencyCode" => "",
                    );
                } catch (Exception $e) {
                    // var_dump($e);
                    $response_arr = array(
                        "orderNumber" => "",
                        "resMsg" => $e->getMessage(),
                        "resCode" => "1",
                        "TaxAmount" => "",
                        "TaxCurrencyCode" => "",
                        "printUrl" => "",
                        "EWEOrderNo" => "",
                    );

                }
                // return result to
                return $response_arr;

            case 2:
                $wsdl = "http://www.zhonghuan.com.au:8085/API/cxf/common/logisticsservice?wsdl";
                $client = new SoapClient($wsdl, array('trace' => 1)); // The trace param will show you errors stack

                $request_param = array(
                    "fydh" => $data_raw->strOrderNo,
                    "countrytype" => "au",
                );

                try
                {
                    $responce_param = $client->getLogisticsInformation($request_param);
                    //$responce_param =  $client->call("webservice_methode_name", $request_param); // Alternative way to call soap method

                    $index = strpos($responce_param->return, ">");

                    $xml = simplexml_load_string(substr($responce_param->return, $index + 1));

                    $json_string = json_encode($xml);
                    $json_obj = json_decode($json_string);

                    $response_arr = array(
                        "orderNumber" => $json_obj->fydh,
                        "resMsg" => !isset($json_obj->Logisticsback) ? "order not found" : "order found",
                        "resCode" => !isset($json_obj->Logisticsback) ? "1" : "0",
                        "TrackingList" => isset($json_obj->Logisticsback) ? $this->getTrackingList($json_obj->Logisticsback, $json_obj->kdgsname) : "",
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

    private function createStockString($data)
    {
        $wsdl = "http://www.zhonghuan.com.au:8085/API/cxf/au/recordservice?wsdl";
        // try {
        //     $client = new SoapClient($wsdl, array('trace' => 1));
        // } catch (\Throwable $th) {
        //     echo 'not good' . $th;
        // }
        $userName = isset($data->strShopCode) ? $data->strShopCode : '0104';
        $password = isset($data->strSecretKey) ? $data->strSecretKey : '123456';
        $receiverAddress = $data->strReceiverProvince . $data->strReceiverProvince . $data->strReceiverDistrict . $data->strReceiverDoorNo;
        $entryType = (isset($data->strEndDelivertyType) && $data->strEndDelivertyType != "") ? $data->strEndDelivertyType : '4';
        $stock = "<ydjbxx>";
        $stock .= '<chrusername>' . $userName . '</chrusername>';
        $stock .= "<chrstockcode>au</chrstockcode>";
        $stock .= '<chrpassword>' . $password . '</chrpassword>';
// $stock.="<chryyrmc>2082</chryyrmc>";
        // $stock.="<chrzydhm>160-91239396</chrzydhm>";
        // $stock.="<chrhbh>CX110/CX052</chrhbh>";
        $stock .= "<entryType>$entryType</entryType>";
        $stock .= "<chrzl>$data->strOrderWeight</chrzl>";
        $stock .= "<chrsjr>$data->strReceiverName</chrsjr>";
        $stock .= "<chrsjrdz>$receiverAddress</chrsjrdz>";
        $stock .= "<chrsjrdh>$data->strReceiverMobile</chrsjrdh>";
        $stock .= "<chrjjr>$data->strSenderName</chrjjr>";
        $stock .= "<chrjjrdh>$data->strSenderMobile</chrjjrdh>";
        $stock .= "<chrsfzhm>352227198407180525</chrsfzhm>";
        $stock .= "<ydhwxxlist>";
        $stock .= "<ydhwxx>";
        $stock .= $this->getItemList($data);
        $stock .= "</ydhwxx>";
        $stock .= "</ydhwxxlist>";
        $stock .= "</ydjbxx>";

        return $stock;

    }

    private function getItemList($data)
    {
        $list_items_string = "";
        if (isset($data->items) && count($data->items) > 0) {
            foreach ($data->items as $item) {
                $list_items_string .= "<chrpm>$item->strItemName</chrpm>";
                $list_items_string .= "<chrpp>$item->strItemBrand</chrpp>";
                $list_items_string .= "<chrggxh>$item->strItemSpecifications</chrggxh>";
                $list_items_string .= "<chrjz>$item->numItemUnitPrice</chrjz>";
                $list_items_string .= "<chrjs>$item->numItemQuantity</chrjs>";
            }
        }
        return $list_items_string;
    }

    private function getTrackingList($data, $kdgsname)
    {
        $formated_list = array();
        $flag = json_encode($kdgsname);

        if (is_array($data)) {
            foreach ($data as $list_item) {
                $new_node = array();
                $new_node['location'] = "";
                $new_node['time'] = isset($list_item->time) ? $list_item->time : "";
                $new_node['status'] = isset($list_item->ztai) ? $list_item->ztai : "";
                array_push($formated_list, $new_node);
            }
        } else {
            $new_node = array();
            $new_node['location'] = "";
            $new_node['time'] = isset($list_item->time) ? $list_item->time : "";
            $new_node['status'] = isset($list_item->ztai) ? $list_item->ztai : "";

            array_push($formated_list, $new_node);
        }
        return $formated_list;
    }
}
