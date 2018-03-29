<?php

namespace Addons\Village\Controller;
class KsController extends CommonController{
	public function index(){
		$this->is_login();
		$admap['adposition']='3';
		$adlist=M('village_advertisement')->where($admap)->order('addtime desc')->select();				$lmap['vid']=$this->user('villageID');		if($this->user('type')){			$locklist=M('village_lock')->where($lmap)->select();		}else{			$lockil=split(",,", substr($this->user('lockid'), 1,strlen($this->user('lockid'))-2));			$locklist=array();			foreach($lockil as $key=>$value){				$lmap['id']=$value;				$locklist[$key]=M('village_lock')->where($lmap)->find();			}		}		$map['id']=$this->user('id');		$userinfo=M('village_user')->where($map)->find();		if($userinfo["type"]){			$userinfo["lock"]=1;		}		elseif(($userinfo["lstime"] == null) || ($userinfo["letime"] == null)){			$userinfo["lock"]=0;		}		elseif(strtotime(get_time()) <= strtotime($userinfo["letime"])){			$userinfo["lock"]=1;		}		else{			$userinfo["lock"]=0;		}		$this->assign('userinfo',$userinfo);		$this->assign('locklist',$locklist);
		$this->assign('adlist',$adlist);
		$this->display();	
	}			public function jsapi(){		$appid="wx0f8a550aaba6606f";		$secret="eabee566acf872b5139221eb0b17e58b";		$timestamp=time();		$nonceStr="huiye";		$url = "http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";		 		$access_token=cookie("access_token");		if(!$access_token){			$url='https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid='.$appid.'&secret='.$secret;  					$file_contents=json_decode(curl($url),true);			if($file_contents["access_token"]){				cookie("access_token",$file_contents["access_token"],7100);				$access_token=$file_contents["access_token"];			}		}				$jsapi_ticket=cookie("jsapi_ticket");		if(!$jsapi_ticket){			$url="https://api.weixin.qq.com/cgi-bin/ticket/getticket?access_token=".$access_token."&type=jsapi";			$file_contents2=json_decode(curl($url),true);			if($file_contents2["ticket"]){				cookie("jsapi_ticket",$file_contents2["ticket"],7100);				$jsapi_ticket=$file_contents2["ticket"];			}		}		$string="jsapi_ticket=".$jsapi_ticket."&noncestr=".$nonceStr."&timestamp=".$timestamp."&url=".$url;		$signature=sha1($string);		$this->assign("appId",$appid);		$this->assign("timestamp",$timestamp);		$this->assign("nonceStr",$nonceStr);		$this->assign("signature",$signature);		$this->assign("ticket",$jsapi_ticket);	}		public function lockpay(){		$this->is_login();		$this->jsapi();		$admap['adposition']='3';		$adlist=M('village_advertisement')->where($admap)->order('addtime desc')->select();				$paylist=M('village_lockcombo')->select();		foreach ($paylist as $key => $value) {			$orderid=$value["id"].date("YmdHis").rand(1000,9999);			$jsApiParameters[$value["id"]]=array(json_decode($this->wxpay($value["price"],$orderid)));		}		$this->assign('adlist',$adlist);		$this->assign('paylist',$paylist);		$this->assign('jsApiParameters',json_encode($jsApiParameters));		$this->display();		}			//更新付费状态	
	public function updatapay(){		$this->is_login();		$data["uid"]=$this->user('id');		$map["id"]=I("data");		$lockcombo=M('village_lockcombo')->where($map)->find();		$data["price"]=$lockcombo["price"];		$data["time"]=get_time();		M("village_lockpaylog")->data($data)->add();				$umap["id"]=$this->user('id');		$userinfo=M("village_user")->where($umap)->find();		$addtime=$lockcombo["addtime"];		if(($userinfo["letime"] == null) || (strtotime($userinfo["letime"]) < strtotime(get_time()))){			$data1["lstime"]=get_time();			$data1["letime"]=date("Y-m-d H:i:s", strtotime('+'.(31*$addtime).' day'));		}else{			$data1["letime"]=date('Y-m-d',strtotime('+'.(31*$addtime).' day',strtotime($userinfo["letime"])));		}		if(M("village_user")->where($umap)->save($data1)){			$this->ajaxReturn("success");		}else{			$this->ajaxReturn("error");		}			}	//微信支付	public function wxpay($SetTotal_fee,$SetOut_trade_no){		$this->is_login();		vendor('WXpay.lib.Exception');        vendor('WXpay.lib.Api');        vendor('WXpay.lib.JsApiPay');        vendor('WXpay.lib.Notify');        vendor('WXpay.lib.Submit');		$tools = new \JsApiPay();		//①、获取用户openid		if(session("openid")){			$openId=session("openid");		}else{			$openId = $tools->GetOpenid();			$_SESSION["openid"]=$openId;		}				//②、统一下单		$input = new \WxPayUnifiedOrder();		$input->SetBody("汇业Q小区门禁Q钥匙开通续费"); //商品描述		$input->SetAttach("汇业Q小区门禁Q钥匙开通续费");//附加数据		$input->SetOut_trade_no($SetOut_trade_no);//商户订单号		$input->SetTotal_fee($SetTotal_fee*100);//总金额//		$input->SetTotal_fee(1);//总金额		$input->SetTime_start(date("YmdHis"));//交易起始时间		$input->SetTime_expire(date("YmdHis", time() + 600));//交易结束时间		$input->SetGoods_tag("物业费");//商品标记		$input->SetNotify_url("http://fjhuiye.com/xiaoqu/index.php/addon/Village/Ks/notify.html");//通知地址		$input->SetTrade_type("JSAPI");//交易类型		$input->SetOpenid($openId);//用户$openId标识		$wxpayapi=new \WxPayApi();		$order = $wxpayapi->unifiedOrder($input);//		print_r($order);exit;//		echo '<font color="#f00"><b>统一下单支付单信息</b></font><br/>';//		printf_info($order);//		echo $order["out_trade_no"];		session("prepay_id",$order["prepay_id"]);		$jsApiParameters = $tools->GetJsApiParameters($order);		return $jsApiParameters;//		print_r($jsApiParameters);//		//获取共享收货地址js函数参数//		$editAddress = $tools->GetEditAddressParameters();	}		public function notify(){//		vendor('WXpay.lib.Exception');//      vendor('WXpay.lib.Api');//      vendor('WXpay.lib.JsApiPay');//      vendor('WXpay.lib.Notify');//      vendor('WXpay.lib.Submit');//				$data["info"]="ok".get_time();		M("wxpaylog")->data($data)->add();		echo "ok";//		$file  = 'log.txt';//要写入文件的文件名（可以是任意文件名），如果文件不存在，将会创建一个//		$content = "第一次写入的内容\n";//		$f  = file_put_contents($file, $content,FILE_APPEND);		//		$wxpayNotify = new  \  WxPayNotify();//      $verify_result = $wxpayNotify->Handle();//		if($verify_result){//			\\\\\\//		}	}																										
	//启动端口监听服务
	public function server(){
		$url="../WorkerMan/server.php";
		echo curl($url);
	}
	
	public function ks(){
		$this->is_login();
		$port = 8888;
		$ip = "120.76.233.4";		$socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);		$ret = array();		$state="";		$ret['success'] = 0;		$ret['msg']="小Q开锁失败了，请重试！\n重试多次无法开启，请联系客服！";		if ($socket < 0) {			$state.="socket_create() failed: reason: " . socket_strerror($socket) . "\n";			//$this->error("出错啦T_T:".socket_strerror($socket),addons_url("Village://Index/index"));			$ret['state']= $state;			exit;		}else {			$state.="OK.\n";		}		$result = socket_connect($socket, $ip, $port);		if ($result < 0) {			$state.="socket_connect() failed.\nReason: ($result) " . socket_strerror($result) . "\n";			//$this->error("出错啦T_T:".socket_strerror($result),addons_url("Village://Index/index"));			$ret['state']= $state;			exit;		}else {			$state.="连接OK\n";		}		$in = "o:" . I("data");		if(!socket_write($socket, $in, strlen($in))) {			$state.="socket_write() failed: reason: " . socket_strerror($socket) . "\n";			//$this->error("出错啦T_T:".socket_strerror($socket),addons_url("Village://Index/index"));			$ret['state']= $state;			exit;		}else {			$state.="open:" . I("data");			$ret['success'] = 1;			$ret['state']= $state;			$ret['msg']="操作成功！\n小Q正在努力开锁，请稍后！\n重试多次无法开启，请联系客服！";						$data["uid"]=$this->user('id');			$data["lockid"]=I("data");			$data["time"]=get_time();			M("village_kslog")->data($data)->add();		}		socket_close($socket);		$this->ajaxReturn($ret);		//$this->success("发送成功！<br>信号君正在飞快通知门禁君！<br>如长时间无反应，请联系客服！",addons_url("Village://Index/index"));
		//$state="";
		//$state=$this->request($port,$ip,I("data"));
		//$data["state"]="new ".$state;
		//$data["time"]=get_time();
		//M("village_kslog")->data($data)->add();
		//$this->ajaxReturn($state);
	}
	
	public function a(){
		
		$socket = socket_create(AF_INET, SOCK_STREAM,SOL_TCP);
		if (!$socket) {
//		    echo "socket_create() failed: reason: " . socket_strerror($socket) . "\n";
			echo "error:".socket_strerror($socket);
		}
		socket_close($socket);
		var_dump(socket_last_error());
	}
	
	public function newRequest($port,$ip,$lockid){
		$state="success";
		$url="../WorkerMan/wx.php";
		return curl($url);
		exit;
		$socket = socket_create(AF_INET,SOCK_STREAM, SOL_TCP);
		if ($socket < 0) {
//		    echo "socket_create() failed: reason: " . socket_strerror($socket) . "\n";
			$state="error socket_create:".socket_strerror($socket);
		}
		
		$result = socket_connect($socket, $ip, $port);
		if ($result < 0) {
//		    echo "socket_connect() failed.\nReason: ($result) " . socket_strerror($result) . "\n";
			$state="error socket_connect:".socket_strerror($result);
		}
		
		$in = "o:".$lockid;
		if(!socket_write($socket, $in, strlen($in))) {
//		    echo "socket_write() failed: reason: " . socket_strerror($socket) . "\n";
			$state="error socket_write:".socket_strerror($socket);
		}
		socket_close($socket);
		return $state;
	}
	
	
	public function request($port,$ip,$lockid){
//		error_reporting(E_ALL);
//		set_time_limit(0);
		/*
		 +-------------------------------
		 *    @socket连接整个过程
		 +-------------------------------
		 *    @socket_create
		 *    @socket_connect
		 *    @socket_write
		 *    @socket_read
		 *    @socket_close
		 +--------------------------------
		 */
		$state="success";
		$socket = socket_create(AF_INET,SOCK_STREAM, SOL_TCP);
		if ($socket < 0) {
//		    echo "socket_create() failed: reason: " . socket_strerror($socket) . "\n";
			$state="error socket_create:".socket_strerror($socket);
		}
		$result = socket_connect($socket, $ip, $port);
		if ($result < 0) {
//		    echo "socket_connect() failed.\nReason: ($result) " . socket_strerror($result) . "\n";
			$state="error socket_connect:".socket_strerror($result);
		}
		
		$in = "o:".$lockid;
		if(!socket_write($socket, $in, strlen($in))) {
//		    echo "socket_write() failed: reason: " . socket_strerror($socket) . "\n";
			$state="error socket_write:".socket_strerror($socket);
		}
		socket_close($socket);
		return $state;
	}

	function tcp_server(){
		$serv = new swoole_server("120.76.233.4", 8888, SWOOLE_BASE, SWOOLE_SOCK_TCP);
		$serv->set(array(
		    'worker_num' => 8,   //工作进程数量
		    'daemonize' => true, //是否作为守护进程
		));
		$serv->on('connect', function ($serv, $fd){
		    echo "Client:Connect.\n";
		});
		$serv->on('receive', function ($serv, $fd, $from_id, $data) {
		    $serv->send($fd, 'Swoole: '.$data);
		    $serv->close($fd);
		});
		$serv->on('close', function ($serv, $fd) {
		    echo "Client: Close.\n";
		});
		$serv->start();
	}
}

