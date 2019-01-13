<?php include_once('./inc/header.php') ?>

    <div class="container">
      <h2>Web API URL</h2>
      <p>http://~/api/token.php</p>
    </div>
    <div class="container">
      <p class="text-primary h6">
        if record with same name & address already exist in database, return the
        exist branchId and branchKey without create a new row in database.
      </p>
    </div>
    <div class="container">
      <h3>Example</h3>
      <h5>request body</h5>
      <code> { "name":"shop1", "address":"some address" } </code>
      <h5>response body</h5>
      <code>
        { "resCode": 0, "resMsg": "your token created", "branchId":
        "5bea33f32df14", "branchKey": "71b180db835842f632477958b490f546" }
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
            <td>name</td>
            <td>C(255)</td>
            <td>客户名</td>
            <td>SHOP567</td>
          </tr>
          <tr class="table-primary">
            <td>address</td>
            <td>C(255)</td>
            <td>客户地址</td>
            <td>99/999 Burwood Road, Burwood</td>
          </tr>
        </tbody>
      </table>
    </div>

    <div class="container col-12 mt-5">
      <h2>Return JSON</h2>
      <table class="table table-hover table-bordered">
        <thead class="thead-dark">
          <tr>
            <th>variable</th>
            <th>description</th>
            <th>sample</th>
          </tr>
        </thead>
        <tbody>
          <tr>
            <th>resCode</th>
            <td>response code for api request</td>
            <td>0: success, 1: fail</td>
          </tr>
          <tr>
            <th>resMsg</th>
            <td>response message to describe the response result</td>
            <td>your token created</td>
          </tr>
          <tr>
            <th>branchId</th>
            <td>generate branchId</td>
            <td>5bea35389a127</td>
          </tr>
          <tr>
            <th>branchKey</th>
            <td>generate branchKey</td>
            <td>712846f7c5bdcf0a1f2d98bb318d6432</td>
          </tr>
        </tbody>
      </table>
    </div>

<?php include_once('./inc/footer.php') ?>

