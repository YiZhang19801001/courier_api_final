<?php include_once './inc/header.php'?>

    <div class="container">
      <h2>Web API URL</h2>
      <p>http://~/api/trace.php</p>
    </div>
    <div class="container">
      <h5>request body</h5>
      <code>
        { "branchId":"SHOP111", "branchKey":"123456","strProviderCode":"4PX",
        "strOrderNo":"TESDF181111-101000" }
      </code>
      <h5>response body</h5>
      <code>
        { "orderNumber": "TESDF181111-101000", "resMsg": "Order Successfully
        Created, Please Save your order number for further service refferences",
        "resCode": "0", "TrackingList": [ { "location": "墨尔本", "time":
        "2018-11-11 09:37:51", "status": "The goods have been taken from the
        sender" } ] }
      </code>
    </div>
    <div class="container col-12 mt-5">
      <h2>Request JSON</h2>
      <table class="table table-hover table-bordered">
        <thead class="thead-dark">
          <tr>
            <th>variable</th>
            <th>data type</th>
            <th>description</th>
            <th>sample</th>
          </tr>
        </thead>
        <tbody>
          <tr class="table-primary">
            <td>branchId</td>
            <td>C(11)</td>
            <td>客户编码</td>
            <td>SHOP111</td>
          </tr>
          <tr class="table-primary">
            <td>branchKey</td>
            <td>C(11)</td>
            <td>客户密匙</td>
            <td>123456</td>
          </tr>
          <tr class="table-primary">
            <td>strProviderCode</td>
            <td>C(11)</td>
            <td>快递公司编码</td>
            <td>4PX-四方, AUEX-澳邮集团,CQCHS-重庆中环,(默认为4PX)</td>
          </tr>
          <tr class="table-primary">
            <th>strOrderNo</th>
            <td>C(50)</td>
            <td>the number which is used to create delivery mission</td>
            <td>TEST20DZS9Z2001</td>
          </tr>
        </tbody>
      </table>
    </div>

    <div class="container col-12 mt-5">
      <h2>Return JSON</h2>
      <table class="table table-hover table-bordered">
        <thead class="thead-dark">
          <tr>
            <th colspan="2">variable</th>
            <th>description</th>
            <th>sample</th>
          </tr>
        </thead>
        <tbody>
          <tr>
            <th colspan="2">orderNumber</th>
            <td>the number which is used to create delivery mission</td>
            <td>TEST20DZS9Z2001</td>
          </tr>
          <tr>
            <th rowspan="3">TrackingList(this is an array)</th>
            <th>location</th>
            <td>each tracking point city name</td>
            <td>墨尔本</td>
          </tr>
          <tr>
            <th>time</th>
            <td>time for this event</td>
            <td>2018-11-08 11:13:44</td>
          </tr>
          <tr>
            <th>status</th>
            <td>what happen in this point</td>
            <td>The goods have been taken from the sender</td>
          </tr>
        </tbody>
      </table>
    </div>
<?php include_once './inc/footer.php'?>

