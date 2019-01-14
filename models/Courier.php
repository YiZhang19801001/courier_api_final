<?php

include_once 'Helper.php';

class Courier
{

    //DB stuff
    protected $conn;

    protected $request_type;

    //Constructor with DB
    public function __construct($db, $request_type)
    {
        $this->conn = $db;
        $this->request_type = $request_type;
    }

}
