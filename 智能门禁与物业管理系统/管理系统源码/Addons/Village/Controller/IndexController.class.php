<?php

namespace Addons\Village\Controller;

class IndexController extends CommonController{
	public function index(){
		
		$admap['adposition']='1';
		$adlist=M('village_advertisement')->where($admap)->order('addtime desc')->select();		$activitylist=M('village_activity')->where("vid like '%,11,%'")->limit(5)->order('addtime desc')->select();		
		$nmap['vid']=$this->vid();
		$nmap['type']='0';
		$villagenotice=M('village_notice')->where($nmap)->limit(5)->select();		$vmap['id']=$this->vid();		$village_info=M('village_info')->where($vmap)->find();		$tel=$village_info["tel"];						$this->assign('tel',$tel);
		$this->assign('activitylist',$activitylist);		$this->assign('villagenoticelist',$villagenotice);
		$this->assign('adlist',$adlist);
		$this->display("index");
	}
	
	public function index2(){
		
		$admap['adposition']='1';
		$adlist=M('village_advertisement')->where($admap)->order('addtime desc')->select();
		$nmap['vid']=$this->vid();
		if(!$nmap['vid']){
			header("location:".addons_url('Village://Login/select'));
		}
		$nmap['type']='0';
		$villagenotice=M('village_notice')->where($nmap)->limit(5)->select();
		$this->assign('villagenoticelist',$villagenotice);
		$this->assign('adlist',$adlist);
		$this->display("index");
	}
	 
	//平台广告
	public function advertisementDetail(){
		if(I('adid')){
			$map['id']=I('adid');
			$this->assign('advertisement',M('village_advertisement')->where($map)->find());
			$this->display();
		}else{
			$this->error('参数错误');
		}
	}
	//广告
	public function advertisementList(){
		if(I('adposition')==2){
			$this->assign('title',"家政服务");
		}
		$map["adposition"]=I('adposition');
		$this->assign("adlist",M("village_advertisement")->where($map)->order('addtime desc')->limit(10)->select());
		$this->display();
	}
	
	
	//物业服务
	public function villageserver(){
		

		$admap['adposition']='4';
		$adlist=M('village_advertisement')->where($admap)->order('addtime desc')->select();
		$this->assign('adlist',$adlist);
		$this->display();
	}
	
	//小区活动列表
	public function ActivityList(){

		$map["vid"]=array('like','%,'.$this->vid().',%');
		$data=M("village_activity")->order('addtime desc')->where($map)->select();
//		print_r($data);
		$this->assign("activity",$data);
		$this->display();
	}
	
	//活动详情
	public function ActivityDetail(){
		if(I("AID")){
			$map["id"]=I("AID");
			$this->assign("activity",M("village_activity")->where($map)->find());
			$this->display();
		}else{
			$this->display("ActivityList");
		}
	}
	
	//跳蚤市场
	public function FleamarketList(){
		$data=M("village_fleamarket")->order('addtime desc')->limit(10)->select();
		$arr=array();
		foreach($data as $key=>$value){
			$map['id']=$value['fid'];
			$map['type']="Fleamarket";
			$image=M("village_uploadimage")->where($map)->select();
			$item['images']=$image;
			$arr[$key]['fid']=$value['fid'];
			$arr[$key]['fname']=$value['fname'];
			$arr[$key]['fprice']=$value['fprice'];
			$arr[$key]['cname']=$value['cname'];
			$arr[$key]['ctel']=$value['ctel'];
			if($image){
				$arr[$key]['image']=$image[0]['imageurl'];
			}
			else{
				$arr[$key]['image']="./Addons/Village/View/default/Public/uploads/noimage.jpg";
			}
		}
		$this->assign("fleamarket",$arr);
		//print_r($arr);
		$this->display();
	}
	
	//跳蚤市场发布宝贝
	public function FleamarketAdd(){
		$this->is_login();
		echo 1;exit;
 		//print_r(I('images'));
		if(I('fkname')||I("price")||I("des")||I("name")||I("phone")){
			if(I('fkname')&&I("price")&&I("des")&&I("name")&&I("phone")){
				$data["fname"]=I("fkname");
				$data["fprice"]=I("price");
				$data["fdescribe"]=I("des");
				$data["cname"]=I("name");
				$data["ctel"]=I("phone");
				$data["userid"]=$this->user('id');
				$data["vid"]=$this->vid();
				$Fleamarket=session("Fleamarket");
				if($data["fname"]!=$Fleamarket["fname"] ||$data["fprice"]!=$Fleamarket["fprice"] || $data["fdescribe"]!=$Fleamarket["fdescribe"] || $data["cname"]!=$Fleamarket["cname"] || $data["ctel"]!=$Fleamarket["ctel"]){
					session("Fleamarket",$data);
					$data["addtime"]=get_time();
					if(!M("village_fleamarket")->data($data)->add()){
						$this->error("信息发布失败".mysql_error());
						exit;
					}
										
					if(I("imagesarr")){
						$Fleamarketdata=M("village_fleamarket")->where($data)->find();
						$imagesarr=I("imagesarr");
						foreach($imagesarr as $item){
							$map['imagename']=$item;
							$image=M("village_uploadimage");
							$image->id=$Fleamarketdata['fid'];
							$image->type="Fleamarket";
							$image->where($map)->save();
						}
					}
					$this->success("发布成功",U("FleamarketList"));
					exit;
				}else{
					//print_r($Fleamarket);
					$this->error("不能重复发布");
					exit;
				}
			}
			else{
				$this->error("请填写完整信息");
				exit;
			}
		}
		$this->display();
	}
	
	//跳蚤市场宝贝详情
	public function FleamarketDetail(){
		if(I("fid")){
			$map["fid"]=I("fid");
			$data=M("village_fleamarket")->where($map)->find();
			$mapimage['id']=$data['fid'];
			$mapimage['type']='Fleamarket';
			$images=$image=M("village_uploadimage")->where($mapimage)->select();
			$data['images']=$images;
			if(!$images){
				$images[0]['imageurl']="./Addons/Village/View/default/Public/uploads/noimage.jpg";
			}
			$this->assign('Fleamarket',$data);
			$this->display();
		}else{
			$this->display("FleamarketList");
		}
	}
	
	//房屋租赁
	public function Rentallist(){
		$data=M("village_rental")->order('addtime desc')->limit(10)->select();
		$arr=array();
		foreach($data as $key=>$value){
			$map['id']=$value['rid'];
			$map['type']="Rental";
			$image=M("village_uploadimage")->where($map)->select();
			$item['images']=$image;
			$arr[$key]['rid']=$value['rid'];
			$arr[$key]['rtitle']=$value['rtitle'];
			$arr[$key]['rprice']=$value['rprice'];
			$arr[$key]['rroom']=$value['rroom'];
			$arr[$key]['rarea']=$value['rarea'];
			$arr[$key]['radress']=$value['radress'];
			$arr[$key]['image']=$image[0]['imageurl'];
			if($image){
				$arr[$key]['image']=$image[0]['imageurl'];
			}
			else{
				$arr[$key]['image']="./Addons/Village/View/default/Public/uploads/noimage.jpg";
			}
			//print_r($item['images']);
		}
		$this->assign("rental",$arr);
		$this->display();
	}
	
	//房屋详情
	public function RentalDetail(){
		if(I("rid")){
			$map["rid"]=I("rid");
			$data=M("village_rental")->where($map)->find();
			$mapimage['id']=$data['rid'];
			$mapimage['type']='Rental';
			$images=$image=M("village_uploadimage")->where($mapimage)->select();
			if(!$images){
				$images[0]['imageurl']="./Addons/Village/View/default/Public/uploads/noimage.jpg";
			}
			$data['images']=$images;
			$this->assign('Rental',$data);
			$this->display();
		}else{
			$this->display("Rentallist");
		}
	}
	
	//添加房屋
	public function RentalAdd(){
		$this->is_login();
		if(I("villagename")||I("rentaltitle")||I("rentalprice")||I("rentalroom")||I("rentalarea")||I("rentalname")||I("rentalphone")||I("rentaladress")){
			if(I("villagename")&&I("rentaltitle")&&I("rentalprice")&&I("rentalroom")&&I("rentalarea")&&I("rentalname")&&I("rentalphone")&&I("rentaladress")){
				$data['vid']=I("villagename");
				$data['rtitle']=I("rentaltitle");
				$data['rprice']=I("rentalprice");
				$data['radress']=I("rentaladress");
				$data['rdes']=I("rentaldescribe");
				$data['rroom']=I("rentalroom");
				$data['rarea']=I("rentalarea");
				$data['rname']=I("rentalname");
				$data['rtel']=I("rentalphone");
				$data["userid"]=$this->user('id');
				
				//房屋配置
				$data['chuang']=I("chuang");
				$data['kuandai']=I("kuandai");
				$data['jiaju']=I("jiaju");
				$data['nuanqi']=I("nuanqi");
				$data['meiqi']=I("meiqi");
				$data['youxiandianshi']=I("youxiandianshi");
				$data['dianshi']=I("dianshi");
				$data['bingxiang']=I("bingxiang");
				$data['kongtiao']=I("kongtiao");
				$data['reshuiqi']=I("reshuiqi");
				$data['xiyiji']=I("xiyiji");
				$data['weibolu']=I("weibolu");
				$Rental=session("Rental");
				if($data["vid"]!=$Rental["vid"] ||$data["rtitle"]!=$Rental["rtitle"]||$data["radress"]!=$Rental["radress"] || $data["rprice"]!=$Rental["rprice"] || $data["rdes"]!=$Rental["rdes"] || $data["rroom"]!=$Rental["rroom"]|| $data["rarea"]!=$Rental["rarea"]|| $data["rname"]!=$Rental["rname"]|| $data["rtel"]!=$Rental["rtel"]){
					session("Rental",$data);
					$data["addtime"]=get_time();
					if(!M("village_rental")->data($data)->add()){
						$this->error("信息发布失败".mysql_error());
						exit;
					}
										
					if(I("imagesarr")){
						$Rentaldata=M("village_rental")->where($data)->find();
						$imagesarr=I("imagesarr");
						foreach($imagesarr as $item){
							$map['imagename']=$item;
							$image=M("village_uploadimage");
							$image->id=$Rentaldata['rid'];
							$image->type="Rental";
							$image->where($map)->save();
						}
					}
					$this->success("发布成功",U("Rentallist"));
					exit;
				}else{
					$this->error("不能重复发布");
					exit;
				}
			}
			$this->error("请填写完整信息");
			exit;
		}
		$this->display();
	}
	
	//通知页面
	public function NoticeList(){
		$this->is_login();
		$map["vid"]=session("vid");
		$map["type"]=array(array('eq',0),array('eq',1),'or'); 
		$this->assign("notice",M("village_notice")->where($map)->order('addtime desc')->limit(10)->select());
		$this->display();
	}
	
	//通知详情页
	public function NoticeDetail(){
		$this->is_login();
		if(I("NID")){
			$map["id"]=I("NID");
			$this->assign("notice",M("village_notice")->where($map)->find());
			$this->display();
		}else{
			$this->display("NoticeList");
		}
	}
	
	//办事指南页面
	public function GuideList(){
		$map["vid"]=session("vid");
		$this->assign("notice",M("village_notice")->where($map)->order('addtime desc')->limit(10)->select());
		$this->display();
	}
	
	//办事指南详情页
	public function GuideDetail(){
		if(I("NID")){
			$map["id"]=I("NID");
			$this->assign("notice",M("village_notice")->where($map)->find());
			$this->display();
		}else{
			$this->display("NoticeList");
		}
	}

	//生活导航
	public function Lifenav(){
		$this->display();
	}
	
	//投诉页面
	public function Complaint(){
		if(I("describe")){
			if(I("describe")){
				$user=session("user"); 
				
				$data["uid"]=$user["id"];
				$data["vid"]=$user["villageID"];
				$data["describe"]=I("describe");
				$data["adddate"]=get_time();
				
				if(M("village_complaint")->data($data)->add()){
					$this->success("投诉提交成功！");
				}else{
					$this->error("投诉提交失败！");
				}
				
			}else{
				$this->error("请填写完整内容！");
			}
		}
		$this->display();
	}
	
	
	//报修页面
	public function CellRepair(){
		
	    if(!$this->is_login()){
		    exit;
		}
// 		echo "ok";exit; 
		$this->display();
	}
	
	public function jsapi(){
		$appid="wx0f8a550aaba6606f";
		$secret="eabee566acf872b5139221eb0b17e58b";
		$timestamp=time();
		$nonceStr="huiye";
		$url = "http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
		 
		$access_token=cookie("access_token");
		if(!$access_token){
			$url='https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid='.$appid.'&secret='.$secret;  
		
			$file_contents=json_decode(curl($url),true);
			if($file_contents["access_token"]){
				cookie("access_token",$file_contents["access_token"],7100);
				$access_token=$file_contents["access_token"];
			}
		}
		
		$jsapi_ticket=cookie("jsapi_ticket");
		if(!$jsapi_ticket){
			$url="https://api.weixin.qq.com/cgi-bin/ticket/getticket?access_token=".$access_token."&type=jsapi";
			$file_contents2=json_decode(curl($url),true);
			if($file_contents2["ticket"]){
				cookie("jsapi_ticket",$file_contents2["ticket"],7100);
				$jsapi_ticket=$file_contents2["ticket"];
			}
		}

		$string="jsapi_ticket=".$jsapi_ticket."&noncestr=".$nonceStr."&timestamp=".$timestamp."&url=".$url;
		$signature=sha1($string);

		$this->assign("appId",$appid);
		$this->assign("timestamp",$timestamp);
		$this->assign("nonceStr",$nonceStr);
		$this->assign("signature",$signature);
		$this->assign("ticket",$jsapi_ticket);
	}

	//物业费查询
	public function pmfQuery(){
		$this->is_login();
		$this->jsapi();
		$map["id"]=$this->user("id");
		$userinfo=M('village_user')->where($map)->find();
		$feemap['building']=$userinfo['building'];
		$feemap['unit']=$userinfo['unit'];
		$feemap['room']=$userinfo['room'];
		$feelog=M("village_fee")->where($feemap)->select();
		
		$feecount=0;
		foreach ($feelog as $key => $value) {
			if($value["paystate"]==0){
				$feecount+=$value["fee"];
			}
		}
		$orderid=$userinfo["villageid"].$userinfo["building"].$userinfo["unit"].$userinfo["room"].date("YmdHis").rand(1000,9999);
		session("orderid",$orderid);
		if($feecount){
			$jsApiParameters=$this->wxpay($feecount,$orderid);
			$this->assign("jsApiParameters",$jsApiParameters);
		}
		$this->assign("feecount",$feecount);
		$this->assign("feelog",$feelog);
//		print_r($jsApiParameters);

		$this->display();
	}
	
	public function updatafee(){
		$this->is_login();
		$map1["id"]=$this->user("id");
		$userinfo=M('village_user')->where($map1)->find();
		//更新缴费状态
		$map["villageID"]=$userinfo["villageID"];
		$map["building"]=$userinfo["building"];
		$map["unit"]=$userinfo["unit"];
		$map["room"]=$userinfo["room"];
		$map["paystate"]=0;
		$feedata=M("village_fee")->where($map)->select();
//		print_r($map);exit;
		$data["paytime"]=date("y-m-d H:i:s");
		$data["payuser"]=$map1["id"];
		$data["paystate"]=1;
		$data["paytype"]=0;
		M("village_fee")->where($map)->data($data)->save();
		 
		
		//添加缴费记录		$fee["villagefee"]=0;
		$logdata["uid"]=$map1["id"];
		$logdata["time"]=date("y-m-d H:i:s");
		$logdata["orderid"]=session("orderid");
		$feelog=M("village_feelog");
		foreach ($feedata as $key => $value) {
			$logdata["month"]=$value["feedate"];
			$logdata["amount"]=floatval($value["fee"]);			$fee["villagefee"]+=floatval($value["fee"]);//添加到物业
			$feelog->data($logdata)->add();
		}				//更新到物业		$villageinfo=M('village_info')->where("id=".$userinfo["villageID"])->find();		$fee["villagefee"]+=floatval($villageinfo["villagefee"]);		M("village_info")->where("id=".$userinfo["villageID"])->data($fee)->save();
		$this->ajaxReturn("success");
	}
	
	//微信支付
	public function wxpay($SetTotal_fee,$SetOut_trade_no){
		$this->is_login();
		vendor('WXpay.lib.Exception');
        vendor('WXpay.lib.Api');
        vendor('WXpay.lib.JsApiPay');
        vendor('WXpay.lib.Notify');
        vendor('WXpay.lib.Submit');
		$tools = new \JsApiPay();
		//①、获取用户openid
		if(session("openid")){
			$openId=session("openid");
		}else{
			$openId = $tools->GetOpenid();
			$_SESSION["openid"]=$openId;
		}
		
		//②、统一下单
		$input = new \WxPayUnifiedOrder();
		$input->SetBody("Q小区物业费支付"); //商品描述
		$input->SetAttach("Q小区物业费支付");//附加数据
		$input->SetOut_trade_no($SetOut_trade_no);//商户订单号
		$input->SetTotal_fee($SetTotal_fee*100);//总金额
//		$input->SetTotal_fee(1);//总金额
		$input->SetTime_start(date("YmdHis"));//交易起始时间
		$input->SetTime_expire(date("YmdHis", time() + 600));//交易结束时间
		$input->SetGoods_tag("物业费");//商品标记
		$input->SetNotify_url("http://fjhuiye.com/xiaoqu/index.php/addon/Village/Index/notify.html");//通知地址
		$input->SetTrade_type("JSAPI");//交易类型
		$input->SetOpenid($openId);//用户$openId标识
		$wxpayapi=new \WxPayApi();
		$order = $wxpayapi->unifiedOrder($input);
//		print_r($order);exit;
//		echo '<font color="#f00"><b>统一下单支付单信息</b></font><br/>';
//		printf_info($order);
//		echo $order["out_trade_no"];
		session("prepay_id",$order["prepay_id"]);
		$jsApiParameters = $tools->GetJsApiParameters($order);
		return $jsApiParameters;
//		print_r($jsApiParameters);
//		//获取共享收货地址js函数参数
//		$editAddress = $tools->GetEditAddressParameters();
	}
	
	public function notify(){
//		vendor('WXpay.lib.Exception');
//      vendor('WXpay.lib.Api');
//      vendor('WXpay.lib.JsApiPay');
//      vendor('WXpay.lib.Notify');
//      vendor('WXpay.lib.Submit');
//		
		$data["info"]="ok".get_time();
		M("wxpaylog")->data($data)->add();
		echo "ok";
//		$file  = 'log.txt';//要写入文件的文件名（可以是任意文件名），如果文件不存在，将会创建一个
//		$content = "第一次写入的内容\n";
//		$f  = file_put_contents($file, $content,FILE_APPEND);
		
//		$wxpayNotify = new  \  WxPayNotify();
//      $verify_result = $wxpayNotify->Handle();
//		if($verify_result){
//			\\\\\\
//		}
	}
	
	
	//物业费详情
	public  function villagefeedetail(){
		$this->is_login();
		$feemap['id']=I('feeid');
		$feelog=M("village_fee")->where($feemap)->find();
		$this->assign("feelog",$feelog);
		$feedetail=array();
		$list=split(',', $feelog['feeinfo']);
		foreach($list as $key=>$value){
			$info=split(':', $value);
			$feedetail[$key]['feename']=$info[0];
			$feedetail[$key]['feevalue']=$info[1];
		}
		$this->assign('feedetail',$feedetail);
		$this->display();
	}
	
	//小区拼车列表
	public function CarpoolingList(){
		$map["vid"]=$this->user("villageID");
		$map["Dtime"]=array('gt',get_time());
		$carpool=M("village_carpool")->where($map)->select();
//		print_r($carpool);exit;
		$this->assign("data",$carpool);
		$this->display();
	}
	
	//拼车信息发布
	public function CarpoolingAdd(){
		$this->is_login();
		if(I("Owners") || I("beginning") || I("end") || I("Dtime") || I("carType") || I("tel") || I("fee") || I("seats")){
			if(I("Owners") && I("beginning") && I("end") && I("Dtime") && I("carType") && I("tel") && I("seats")){
				$data["Owners"]=I("Owners");
				$data["beginning"]=I("beginning");
				$data["end"]=I("end");
				$data["Dtime"]=I("Dtime");
				$data["carType"]=I("carType");
				$data["tel"]=I("tel");
				$data["seats"]=I("seats");
				$data["vid"]=$this->user("villageID");
				$data["uid"]=$this->user("id");
				$Carpool=session("Carpool");
				if(!($data["Owners"]==$Carpool["Owners"] && $data["beginning"]==$Carpool["beginning"] && $data["end"]==$Carpool["end"] && $data["Dtime"]==$Carpool["Dtime"] && $data["carType"]==$Carpool["carType"] && $data["tel"]==$Carpool["tel"] && $data["seats"]==$Carpool["seats"] && $data["vid"]==$Carpool["vid"] &&  $data["uid"]==$Carpool["uid"])){
					session("Carpool",$data);
					
					$data["addtime"]=get_time();
					if(!M("village_carpool")->data($data)->add()){
						$this->error("信息发布失败".mysql_error());
						exit;
					}
				}
				$this->success("发布成功",U("CarpoolingList"));
				exit;
				
			}else{
				$this->error("请填写完整信息");
				exit;
			}
		}
		
		$this->display();
	}
	
	//智能Q服务
	public function SmartQserver(){
		$this->is_login();
		$this->display();
	}
	
	//生活Q服务
	public function lifeQserver(){
		$this->is_login();
		$this->display();
	}

	public function error(){
		$this->display();
	}
	
	//最新消息
	public function news(){
	    $this->is_login();
	    
	    $map["uid"]=$this->user("id");
	    $list=M("village_news")->where($map)->order('time desc')->select();
	    
	    $this->assign("list",$list);
	    $this->display();
	}
	
	//消息详情
	public function newsDetail(){
	    $this->is_login();
	    if(I("id")){
	        $map["id"]=I("id");
	        $map["uid"]=$this->user("id");
	        $info=M("village_news")->where($map)->find();
	        $this->assign("info",$info);
	        $this->display();
	    }
	}
}