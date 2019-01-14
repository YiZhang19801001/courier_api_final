<?php
include_once "./Courier.php";
include_once "./Helper.php";

class PX4 extends Courier
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
                return "https://newomstest.ewe.com.au/eweApi/ewe/api/createOrder";

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
                    "USERNAME" => $this->username,
                    "APIPASSWORD" => $this->password,
                    "BoxNo" => Helper::cleanValue($data_raw->strBoxNo),
                    "REFERENCENO" => Helper::cleanValue($data_raw->strReferenceNo),
                    "ExtraRefernces" => [""],
                    "TotalPackage" => 1,
                    "Remark" => Helper::cleanValue($data_raw->strRemark),
                    "DeclaredWeight" => Helper::cleanValue($data_raw->strOrderWeight),
                    "IsEconomic" => Helper::cleanValue($data_raw->boolIsEconomic),
                    "ContentType" => Helper::cleanValue($data_raw->intContentType),
                    "IsUseStock" => Helper::cleanValue($data_raw->intIsUseStock),
                    "ValueAddedService" => Helper::cleanValue($data_raw->strValueAddedService),
                    "Is3PL" => Helper::cleanValue($data_raw->strIs3PL),
                    "CustomerClientId" => Helper::cleanValue($data_raw->strShopCode),
                    "IsUseCcic" => Helper::cleanValue($data_raw->intIsUseCcic),
                    "auMerchantId" => Helper::cleanValue($data_raw->strAuMerchantId),
                    "DeclareValue" => Helper::cleanValue($data_raw->numDeclaredValue),
                    "RealWeight" => Helpler::cleanValue($data_raw->numRealWeight),
                    "OutBizCode" => Helper::cleanValue($data_raw->strOutBizCode),
                    "Items" => $this->getItems($data_raw),
                    "Sender" => $this->getSender($data_raw),
                    "Receiver" => $this->getReceiver($data_raw),
                    "Payer" => $this->getPayer($data_raw),
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

    private function getItems($data)
    {

        $list_items = array();
        if (isset($data->items)) {
            foreach ($data->items as $item) {
                $list_item = array(
                    "Brand" => Helper::cleanValue($item->strItemBrand),
                    "ItemName" => Helper::cleanValue($item->strItemName),
                    "Quantity" => Helper::cleanValue($item->numItemQuantity),
                    "SKU" => Helper::cleanValue($item->strItemSKU),
                    "Barcode" => "",
                    "Charge" => Helper::cleanValue($item->numItemUnitPrice),
                    "TotalCharge" => Helper::cleanValue($item->numTotalPrice),
                    "Spec" => Helper::cleanValue($item->strItemSpecifications),
                    "Currency" => "",
                );

                array_push($list_items, $list_item);
            }

        }
        return $list_items;

    }

    private function getSender($data)
    {
        $sender = array();

        $sender['Name'] = Helper::cleanValue($data->strSenderName);
        $sender['Email'] = Helper::cleanValue($data->strSenderEmail);
        $sender['Phone'] = Helper::cleanValue($data->strSenderMobile);
        $sender['Street'] = Helper::cleanValue($data->strSenderAddress);
        $sender['City'] = Helper::cleanValue($data->strSenderCityName);
        $sender['state'] = Helper::cleanValue($data->strSenderProvinceName);
        $sender['Suburb'] = "";
        $sender['Country'] = "AUD";
        $sender['Company'] = "";
        $sender['Postcode'] = Helper::cleanValue($data->strSenderPostCode);
        $sender['SetDefault'] = '';

        return $sender;

    }

    private function getReceiver($data)
    {
        $receiver = array();

        $receiver['Name'] = Helper::cleanValue($data->strReceiverName);
        $receiver['Email'] = Helper::cleanValue($data->strReceiverEmail);
        $receiver['Phone'] = Helper::cleanValue($data->strReceiverMobile);
        $receiver['Street'] = Helper::cleanValue($data->strReceiverDoorNo);
        $receiver['City'] = Helper::cleanValue($data->strReceiverCity);
        $receiver['state'] = Helper::cleanValue($data->strReceiverProvince);
        $receiver['Suburb'] = Hepler::cleanValue($data->strReceiverDistrict);
        $receiver['Country'] = "AUD";
        $receiver['Company'] = "";
        $receiver['Postcode'] = Helper::cleanValue($data->strSenderPostCode);
        $receiver['AliasName'] = '';
        $receiver['CardId'] = Helper::cleanValue($data->strReceiverIDNumber);

        return $receiver;

    }

    private function getPayer($data)
    {
        $payer = [];
        // add code ...
        return $payer;
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
