<?php include_once './inc/header.php'?>

    <div class="container">
      <h2>Web API URL</h2>
      <p>http://~/api/delete.php</p>
    </div>
    <div class="container">
      <h5>request body</h5>
      <code>
{
	"branchId":"SHOP111",
	"branchKey":"123456",
    "strOrderNo": "tes0zz123qzzfqz001"
}
      </code>
      <h5>response body</h5>
      <code>
{
    "orderNumber": "tes0zz123qzzfqz001",
    "resMsg": "Success! Order Deleted!",
    "resCode": "0"
}
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
            <td>4PX-四方, AUEX-澳邮集团(现阶段可不填，默认为4PX,!!重庆中环无此服务)</td>
          </tr>
          <tr class="table-primary">
            <th>strOrderNo</th>
            <td>C(50)</td>
            <td>the number which is used to create delivery mission</td>
            <td>tes0zz123qzzfqz001</td>
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
            <td>tes0zz123qzzfqz001</td>
          </tr>

          <tr>
            <th colspan="2">resMsg</th>
            <td>message to describe request response</td>
            <td>Success! Order Deleted!</td>
          </tr>
          <tr>
            <th colspan="2">resCode</th>
            <td>a code which is used to indicate the request response</td>
            <td>'0'=>'Success','1'=>fail</td>
          </tr>
        </tbody>
      </table>
    </div>
<?php include_once './inc/footer.php'?>