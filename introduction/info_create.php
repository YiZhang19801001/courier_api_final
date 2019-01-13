<?php include_once './inc/header.php'?>
    <div class="container">
        <h2>Web API URL</h2>
        <p>http://~/api/create.php</p>
        <hr>
        <h2>charset</h2>
        <p>utf-8</p>
    </div>

    <div class="container">
        <h5>request body</h5>
        <code>
            {
            "branchId":"SHOP111",
            "branchKey":"123456",
            "strOrderNo": "tes017fezq8f2001",
            "strServiceTypeCode": "EC",
            "strShopCode": " 2023",
            "strSenderName": "王哈哈",
            "strSenderMobile": "16987654321",
            "strReceiverName": "小明",
            "strReceiverProvince": "广东省",
            "strReceiverCity": "深圳市",
            "strReceiverDistrict": "宝安区",
            "strReceiverDoorNo": "宝城6区新安二路57号",
            "strReceiverMobile": "13245671234",
            "strOrderWeight": "1.000",
            "strWeightUnit": "KG",
            "strItemCurrency": "CNY",
            "strInsuranceTypeCode": "",
            "strEndDelivertyType": "",
            "strTraceNumber": "",
            "items": [
                {
                    "strItemSKU": "test123456",
                    "strItemDeclareType": "01010700002",
                    "strItemBrand": "A2",
                    "strItemName": "婴幼儿奶粉",
                    "numItemQuantity": "1",
                    "strItemSpecifications": "900g二段",
                    "numItemUnitPrice": "85",
                    "strIsDiscounted": ""
                },
                {
                    "strItemSKU": "test123ab56",
                    "strItemDeclareType": "01010700002",
                    "strItemBrand": "A2",
                    "strItemName": "婴幼儿奶",
                    "numItemQuantity": "1",
                    "strItemSpecifications": "900g二段",
                    "numItemUnitPrice": "85",
                    "strIsDiscounted": ""
                }
                ]
            }

          </code>
          <h5>Response</h5>
          <code>
            {
                "orderNumber": null,
                "resCode":"0",
                "resMsg":"Order Successfully Created, Please Save your order number for further service refferences",
                "TaxAmount": 10.67,
                "TaxCurrencyCode": "AUD"
            }
          </code>
    </div>
    <hr>
    <div class="container mt-3">
        <h5>request body (inactive user)</h5>
        <code>
                {
                "branchId":"SHOP333",
                "branchKey":"123456",
                "strOrderNo": "tes017fezq8f2001",
                "strServiceTypeCode": "EC",
                "strShopCode": " MPX01",
                "strSenderName": "王哈哈",
                "strSenderMobile": "16987654321",
                "strReceiverName": "小明",
                "strReceiverProvince": "广东省",
                "strReceiverCity": "深圳市",
                "strReceiverDistrict": "宝安区",
                "strReceiverDoorNo": "宝城6区新安二路57号",
                "strReceiverMobile": "13245671234",
                "strOrderWeight": "1.000",
                "strWeightUnit": "KG",
                "strItemCurrency": "CNY",
                "strInsuranceTypeCode": "",
                "strEndDelivertyType": "",
                "strTraceNumber": "",
                "items": [
                    {
                        "strItemSKU": "test123456",
                        "strItemDeclareType": "01010700002",
                        "strItemBrand": "A2",
                        "strItemName": "婴幼儿奶粉",
                        "numItemQuantity": "1",
                        "strItemSpecifications": "900g二段",
                        "numItemUnitPrice": "85",
                        "strIsDiscounted": ""
                    },
                    {
                        "strItemSKU": "test123ab56",
                        "strItemDeclareType": "01010700002",
                        "strItemBrand": "A2",
                        "strItemName": "婴幼儿奶",
                        "numItemQuantity": "1",
                        "strItemSpecifications": "900g二段",
                        "numItemUnitPrice": "85",
                        "strIsDiscounted": ""
                    }
                    ]
                }

                </code>
        <h5>Response</h5>
        <code>
            {
            "resCode": "3",
            "resMsg": "your account is inactived, please contact XXX-XXXX-XXX"
            }
        </code>
    </div>
    <div class="container col-12 mt-5">
        <h2>Request JSON</h2>
        <table class="table table-hover table-bordered">
            <thead class="thead-dark">
                <tr>
                    <th colspan="2" scope="col">#</th>
                    <th scope="col">参数名</th>
                    <th scope="col">参数类型</th>
                    <th scope="col">描述</th>
                    <th scope="col">示例代码</th>
                </tr>
            </thead>
            <tbody>
                <tr class="table-primary">
                    <th colspan="2" scope="row">-2</th>
                    <td>branchId</td>
                    <td>C(11)</td>
                    <td>客户编码</td>
                    <td>SHOP111</td>
                </tr>
                <tr class="table-primary">
                    <th colspan="2" scope="row">-1</th>
                    <td>branchKey</td>
                    <td>C(11)</td>
                    <td>客户密匙</td>
                    <td>123456</td>
                </tr>
                <tr class="table-primary">
                    <th colspan="2" scope="row">0</th>
                    <td>strProviderCode</td>
                    <td>C(11)</td>
                    <td>快递公司编码</td>
                    <td>4PX-四方, CQCHS-重庆中环, AUEX-澳邮集团</td>
                </tr>
                <tr class="table-primary">
                    <th colspan="2" scope="row">1</th>
                    <td>strOrderNo</td>
                    <td>C(50)</td>
                    <td>接入商唯一订单编码，用于唯一标识某订单，并且在分箱与合箱时更新此编号查找需要分箱与合箱的包裹</td>
                    <td>test20170922001</td>
                </tr>
                <tr class="table-primary">
                    <th colspan="2" scope="row">2</th>
                    <td>strServiceTypeCode</td>
                    <td>C(3)</td>
                    <td>服务类型代码 <a href="service_code.html">more...</a></td>
                    <td>EC</td>
                    <!-- ToDo:: link the code table -->
                </tr>
                <tr>
                    <th colspan="2" scope="row">3</th>
                    <td>strShopCode</td>
                    <td>C(10)</td>
                    <td>站点代码,预报客户如果只有一个站点可不填，如果有多个站点，则必须填写。</td>
                    <td>testA</td>
                </tr>
                <tr class="table-primary">
                    <th colspan="2" scope="row">4</th>
                    <td>strSenderName</td>
                    <td>C(50)</td>
                    <td>寄件人姓名</td>
                    <td>王哈哈</td>
                </tr>
                <tr class="table-primary">
                    <th colspan="2" scope="row">5</th>
                    <td>strSenderMobile</td>
                    <td>C(20)</td>
                    <td>寄件人电话</td>
                    <td>16987654321</td>
                </tr>
                <tr>
                    <th colspan="2" scope="row">6</th>
                    <td>strSenderProvinceName</td>
                    <td>C(50)</td>
                    <td>寄件人省份/州（USPS服务必填）</td>
                    <td>广东省</td>
                </tr>
                <tr>
                    <th colspan="2" scope="row">7</th>
                    <td>strSenderCityName</td>
                    <td>C(50)</td>
                    <td>寄件人市（USPS服务必填）</td>
                    <td>深圳市</td>
                </tr>
                <tr>
                    <th colspan="2" scope="row">8</th>
                    <td>strSenderAddress</td>
                    <td>C(200)</td>
                    <td>寄件人详细地址（USPS服务必填）</td>
                    <td>宝城6区新安二路57号</td>
                </tr>
                <tr>
                    <th colspan="2" scope="row">9</th>
                    <td>strSenderPostCode</td>
                    <td>C(10)</td>
                    <td>寄件人邮编（USPS服务必填）</td>
                    <td>123002</td>
                </tr>
                <tr class="table-primary">
                    <th colspan="2" scope="row">10</th>
                    <td>strItemCurrency</td>
                    <td>C(3)</td>
                    <td>货物申报币种－使用国际货币标准码<a href="currency_code.html">more...</a></td>
                    <td>CYN</td>
                </tr>
                <tr class="table-primary">
                    <th colspan="2" scope="row">11</th>
                    <td>strReceiverName</td>
                    <td>C(30)</td>
                    <td>收件人姓名</td>
                    <td>小明</td>
                </tr>
                <tr>
                    <th colspan="2" scope="row">12</th>
                    <td>strCountryISO2</td>
                    <td>C(2)</td>
                    <td>收件人国家代码（ISO2位代码，例如中国为CN），为空则默认为CN，其他国家省市区会被丢弃<a href="country_code.html">more...</a></td>
                    <td>CN</td>
                </tr>
                <tr>
                    <th colspan="2" scope="row">13</th>
                    <td>strReceiverProvince</td>
                    <td>C(30)</td>
                    <td>收件省份（国家为CN或空时必填）</td>
                    <td>广东省</td>
                </tr>
                <tr>
                    <th colspan="2" scope="row">14</th>
                    <td>strReceiverProvince</td>
                    <td>C(30)</td>
                    <td>收件城市（国家为CN或空时必填）</td>
                    <td>深圳市</td>
                </tr>
                <tr>
                    <th colspan="2" scope="row">15</th>
                    <td>strReceiverDistrict</td>
                    <td>C(30)</td>
                    <td>收件区/县（国家为CN或空时必填）</td>
                    <td>宝安区</td>
                </tr>
                <tr class="table-primary">
                    <th colspan="2" scope="row">16</th>
                    <td>strReceiverDoorNo</td>
                    <td>C(255)</td>
                    <td>收件街道及门牌号码</td>
                    <td>宝城6区新安二路57号</td>
                </tr>
                <tr class="table-primary">
                    <th colspan="2" scope="row">17</th>
                    <td>strReceiverMobile</td>
                    <td>C(11)</td>
                    <td>收件人手机号码</td>
                    <td>13245671234</td>
                </tr>
                <tr class="table-primary">
                    <th colspan="2" scope="row">18</th>
                    <td>strReceiverIDNumber</td>
                    <td>C(30)</td>
                    <td>收件人证件号码</td>
                    <td>330722197609192116</td>
                </tr>
                <tr>
                    <th colspan="2" scope="row">19</th>
                    <td>strReceiverIDFrontCopy</td>
                    <td>C(1MB)</td>
                    <td>收件人证件正面扫描图片Base64编码（data:image/jpeg;base64,/9j...）</td>
                    <td>data:image/jpeg;base64,/9j/4QAYRXhpZgAASUkqAAgAAAAAAAAA</td>
                </tr>
                <tr>
                    <th colspan="2" scope="row">20</th>
                    <td>strReceiverIDBackCopy</td>
                    <td>C(1MB)</td>
                    <td>收件人证件背面扫描图片Base64编码（data:image/jpeg;base64,/9j...）</td>
                    <td>data:image/jpeg;base64,/9j/4QAYRXhpZgAASUkqAAgAAAAAAAAA</td>
                </tr>
                <tr class="table-primary">
                    <th colspan="2" scope="row">21</th>
                    <td>strOrderWeight</td>
                    <td>C(10)</td>
                    <td>运单重量</td>
                    <td>1.000</td>
                </tr>
                <tr>
                    <th colspan="2" scope="row">22</th>
                    <td>strWeightUnit</td>
                    <td>C(10)</td>
                    <td>重量单位：KG、LB、千克、磅,不传则默认起运国重量单位。<a href="country_code.html">more...</a></td>
                    <td>KG</td>
                </tr>
                <tr>
                    <th colspan="2" scope="row">23</th>
                    <td>strEndDelivertyType</td>
                    <td>C(30)</td>
                    <td>指定增值服务顺丰派送，为空或者不传表示不购买该服务。（“个人物品”CC 可支持顺丰派送，“经济服务”EC支持顺丰派送（单个包裹价值在RMB300元以上、特殊商品除外））</td>
                    <td></td>
                </tr>
                <tr>
                    <th colspan="2" scope="row">24</th>
                    <td>strInsuranceTypeCode</td>
                    <td>C(10)</td>
                    <td>保险类型标志，A表示购买保险，N或空表示不购买</td>
                    <td></td>
                </tr>
                <tr>
                    <th colspan="2" scope="row">25</th>
                    <td>numInsuranceExpense</td>
                    <td>N(5)</td>
                    <td>保险费用（保险类型标志为A时必填）</td>
                    <td></td>
                </tr>
                <tr>
                    <th colspan="2" scope="row">26</th>
                    <td>strTraceNumber</td>
                    <td>C(30)</td>
                    <td>溯源号(澳洲才可以使用)</td>
                    <td></td>
                </tr>
                <tr>
                    <th colspan="2" scope="row">27</th>
                    <td>strRemarks</td>
                    <td>V(255)</td>
                    <td>订单备注</td>
                    <td></td>
                </tr>
                <tr>
                    <th colspan="6" class="table-info text-center">item information</th>
                </tr>
                <tr>
                    <th rowspan="8">
                        ITEMS(should be an Array<Item>())
                    </th>
                    <th scope="row">28</th>
                    <td>strItemSKU</td>
                    <td>C(30)</td>
                    <td>货物识别码，一般与商品条码对应。(产品为SP时，不需要此参数)</td>
                    <td>test123456</td>
                </tr>
                <tr>
                    <th class="table-primary" scope="row">29</th>
                    <td class="table-primary">strItemDeclareType</td>
                    <td class="table-primary">C(11)</td>
                    <td class="table-primary">货物申报类型，申报类型如果是禁运物品将无法成功预报。(产品为SP时，不需要此参数)</td>
                    <td class="table-primary">01010700002</td>
                </tr>
                <tr class="table-primary">
                    <th scope="row">30</th>
                    <td>strItemName</td>
                    <td>C(300)</td>
                    <td>货物名称</td>
                    <td>婴幼儿奶粉</td>
                </tr>
                <tr>
                    <th scope="row">31</th>
                    <td>strItemSpecifications</td>
                    <td>C(100)</td>
                    <td>货物规格型号，例如尺码、颜色、外形等，某些品类必须要有规格数量与单位。</td>
                    <td>900g二段</td>
                </tr>
                <tr>
                    <th class="table-primary" scope="row">32</th>
                    <td class="table-primary">numItemQuantity</td>
                    <td class="table-primary">N(8)</td>
                    <td class="table-primary">货物数量</td>
                    <td class="table-primary">1</td>
                </tr>
                <tr>
                    <th class="table-primary" scope="row">33</th>
                    <td class="table-primary">strItemBrand</td>
                    <td class="table-primary">C(255)</td>
                    <td class="table-primary">货物品牌</td>
                    <td class="table-primary">A2</td>
                </tr>
                <tr>
                    <th class="table-primary" scope="row">34</th>
                    <td class="table-primary">numItemUnitPrice</td>
                    <td class="table-primary">N(10,5)</td>
                    <td class="table-primary">货物单价</td>
                    <td class="table-primary">85</td>
                </tr>
                <tr>
                    <th scope="row">35</th>
                    <td>strIsDiscounted</td>
                    <td>C(1)</td>
                    <td>优惠标志，Y表示为优惠价，N或空表示正常价格</td>
                    <td></td>
                </tr>
            </tbody>
        </table>

<div class="container mt-5">
    <h2>Return JSON</h2>
            <table class="table table-hover">
                <thead class="thead-dark">
                    <tr>
                        <th>variable name</th>
                        <th>description</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>orderNumber</td>
                        <td>Uniq 4PX order number</td>
                    </tr>
                    <tr>
                        <td>message</td>
                        <td>array("code"=>"Response Code","text"=>"Response Message")</td>
                    </tr>
                    <tr>
                        <td>taxAmount</td>
                        <td>How much tax</td>
                    </tr>
                    <tr>
                        <td>TaxCurrencyCode</td>
                        <td>The currency used for tax</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>


<?php include_once './inc/footer.php'?>
