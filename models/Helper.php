<?php
class helper
{
    public static function getDateTime()
    {
        $tz = 'Australia/Sydney';
        $timestamp = time();
        $dt = new DateTime("now", new DateTimeZone($tz));
        $dt->setTimestamp($timestamp);
        $logRowTime = $dt->format('d-m-Y, H:i:s');
        $logFileDate = $dt->format('d-m-Y');
        return json_decode(json_encode(array('time' => $logRowTime, 'date' => $logFileDate)));
    }

    public static function logger($dateTimeForLogger, $myFile, $process, $request_body)
    {
        try
        {

            $file_arr_data = array(); // create empty array

            //Get data
            $formdata = array('time' => $dateTimeForLogger->time, 'process' => $process, 'request_body' => $request_body);

            //Get data from existing json file
            $jsondata = file_get_contents($myFile);

            // converts json data into array
            $file_arr_data = json_decode($jsondata, true) !== null ? json_decode($jsondata, true) : [];

            // Push user data to array
            array_push($file_arr_data, $formdata);

            //Convert updated array to JSON
            $jsondata = json_encode($file_arr_data, JSON_PRETTY_PRINT);

            //save data in log file

            file_put_contents($myFile, $jsondata);

        } catch (Exception $e) {
            echo 'Caught exception: ', $e->getMessage(), "\n";
        }

    }

    public function cleanValue($value)
    {
        return isset($value) ? htmlspecialchars(strip_tags($value)) : "";

    }
}
