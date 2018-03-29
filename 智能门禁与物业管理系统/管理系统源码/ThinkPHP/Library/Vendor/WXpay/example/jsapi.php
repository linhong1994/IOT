
<?php 

ini_set('date.timezone','Asia/Shanghai');
//error_reporting(E_ERROR);
require_once "../lib/WxPay.Api.php";
require_once "WxPay.JsApiPay.php";
require_once 'log.php';

//初始化日志
$logHandler= new CLogFileHandler("../logs/".date('Y-m-d').'.log');
$log = Log::Init($logHandler, 15);

//打印输出数组信息
function printf_info($data)
{
    foreach($data as $key=>$value){
        echo "<font color='#00ff55;'>$key</font> : $value <br/>";
    }
}
$tools = new JsApiPay();
//①、获取用户openid
if($_SESSION["openid"]){
	$openId=$_SESSION["openid"];
}else if($_GET["openid"]){
	$openId=$_GET["openid"];
	$_SESSION["openid"]=$openId;
}else{
	$openId = $tools->GetOpenid();
	$_SESSION["openid"]=$openId;
}

//②、统一下单
$input = new WxPayUnifiedOrder();
$input->SetBody("新顶发汽修");
$input->SetAttach("新顶发汽修");
$input->SetOut_trade_no(WxPayConfig::MCHID.date("YmdHis"));
$input->SetTotal_fee("100");
$input->SetTime_start(date("YmdHis"));
$input->SetTime_expire(date("YmdHis", time() + 600));
$input->SetGoods_tag("新顶发汽修");
$input->SetNotify_url("http://paysdk.weixin.qq.com/example/notify.php");
$input->SetTrade_type("JSAPI");
$input->SetOpenid($openId);
$order = WxPayApi::unifiedOrder($input);
//echo '<font color="#f00"><b>统一下单支付单信息</b></font><br/>';
//printf_info($order);
//echo $order["out_trade_no"];
$_SESSION["prepay_id"]=$order["prepay_id"];
$jsApiParameters = $tools->GetJsApiParameters($order);

//获取共享收货地址js函数参数
$editAddress = $tools->GetEditAddressParameters();

//③、在支持成功回调通知中处理成功之后的事宜，见 notify.php
/**
 * 注意：
 * 1、当你的回调地址不可访问的时候，回调通知会失败，可以通过查询订单来确认支付是否成功
 * 2、jsapi支付时需要填入用户openid，WxPay.JsApiPay.php中有获取openid流程 （文档可以参考微信公众平台“网页授权接口”，
 * 参考http://mp.weixin.qq.com/wiki/17/c0f37d5704f0b64713d5d2c37b468d75.html）
 */
?>
<html>
<head>
    <meta http-equiv="content-type" content="text/html;charset=utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1"/> 
    <link rel="stylesheet" type="text/css" href="weui.min.css"/>

    <title>顶发</title>
    <script type="text/javascript">
	//调用微信JS api 支付
	function jsApiCall()
	{
		WeixinJSBridge.invoke(
			'getBrandWCPayRequest',
			<?php echo $jsApiParameters; ?>,
			function(res){
				
				WeixinJSBridge.log(res.err_msg);
//				alert(res.err_msg);
				if(res.err_msg=="get_brand_wcpay_request:ok"){
					location.href="http://fjhuiye.com/wp/index.php?s=/addon/Yungou/Yungou/create_order/out_trade_no/<?php echo $_SESSION["out_trade_no"]; ?>";
				}
//				alert(res.err_code+res.err_desc+res.err_msg);
				
			}
		);
	}

	function callpay()
	{
		if (typeof WeixinJSBridge == "undefined"){
		    if( document.addEventListener ){
		        document.addEventListener('WeixinJSBridgeReady', jsApiCall, false);
		    }else if (document.attachEvent){
		        document.attachEvent('WeixinJSBridgeReady', jsApiCall); 
		        document.attachEvent('onWeixinJSBridgeReady', jsApiCall);
		    }
		}else{
		    jsApiCall();
		}
	}
	</script>
	<script type="text/javascript">
	//获取共享地址
	function editAddress()
	{
		WeixinJSBridge.invoke(
			'editAddress',
			<?php echo $editAddress; ?>,
			function(res){
				var value1 = res.proviceFirstStageName;
				var value2 = res.addressCitySecondStageName;
				var value3 = res.addressCountiesThirdStageName;
				var value4 = res.addressDetailInfo;
				var tel = res.telNumber;
				
//				alert(value1 + value2 + value3 + value4 + ":" + tel);
			}
		);
	}
	
	window.onload = function(){
		if (typeof WeixinJSBridge == "undefined"){
		    if( document.addEventListener ){
		        document.addEventListener('WeixinJSBridgeReady', editAddress, false);
		    }else if (document.attachEvent){
		        document.attachEvent('WeixinJSBridgeReady', editAddress); 
		        document.attachEvent('onWeixinJSBridgeReady', editAddress);
		    }
		}else{
			editAddress();
		}
	};
	
	</script>

</head>
<body>
	<style>
	   tr{
	   	padding-left: 20px;
	   }
		td{
			color:darkgray;
			padding-top: 20px;
		}
	</style>

    <h3 style="text-align: center;margin-top: 20px;"><?php echo $input->GetBody()?></h3>
    <h1 style="text-align: center;font-size: 60px;color: red;">￥<?php echo $input->GetTotal_fee()/100?></h2>
    <hr />
    <table style="width: 94%;margin-left: 3%;">
    	<tr>
    		<td>收款方</td>
    		<td style="text-align: right;">新顶发汽修有限公司</td>
    	</tr>
    	<tr>
    		<td>商户订单号</td>
    		<td style="text-align: right;"><?php echo $input->GetOut_trade_no()?></td>
    	</tr>
    	<tr>
    		<td>商品</td>
    		<td style="text-align: right;">新顶发汽修抵用券</td>
    	</tr>
    </table>
    <br />
    <hr />
    <br />
	<div align="center">
		<button class="weui_btn  weui_btn_primary" type="button" onclick="callpay()" >立即支付</button>
	</div>
	<div style="position: absolute; bottom:20px;width: 100%;text-align: center;color:darkgray;opacity: 0.5;">
        	福建汇业技术支持
        </div>
</body>
</html>