<?php

class UnknowCourier
{
    public function callApi($data_raw)
    {

        $response_arr = array(
            "orderNumber" => isset($data_raw->strOrderNo) ? $data_raw->strOrderNo : "",
            "resMsg" => "no courier matched, please check your courier name(运输公司名无法匹配，请检查您提交的运输公司名)",
            "resCode" => 'ERR99999',
            "TrackingList" => []
        );

        return $response_arr;

    }

}
