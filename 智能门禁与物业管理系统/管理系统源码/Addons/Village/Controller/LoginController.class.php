<?php

namespace Addons\Village\Controller;

class LoginController extends CommonController{
	public function _initialize(){
		header("Content-type: text/html; charset=utf-8"); 
		$user_agent = $_SERVER['HTTP_USER_AGENT'];
		if (strpos($user_agent, 'MicroMessenger') === false) {
		    header("location: https://open.weixin.qq.com/connect/oauth2/authorize?appid=wx0f8a550aaba6606f&redirect_uri=#&response_type=code&scope=snsapi_base&state=&connect_redirect=1#wechat_redirect");
			exit;
		}
		$openid=$this->openid();
	}
	
	//小区选择
	public function select(){
		$keywork=I("keywork");
		$this->assign("bind","bind");
		//echo $keywork;
		$map['name'] = array('like','%'.$keywork.'%');
		$this->assign("keywork",$keywork);
		$this->assign("info",M("village_info")->where($map)->field("id,name,address,tel,imgURL")->select());
		//print_r(M("village_info")->where($map)->field("id,name,address,tel,imgURL")->select());
		$this->display("select");
	}
	
	public function tryout(){
		session("vid",I("id"));
		header("location:".addons_url('Village://Index/index'));
	}
	
	//个人信息绑定
	public function bind(){
		
// 		if(!I("id")){
// 			$this->assign("bind","bind");
// 			$this->select();
// 			exit;
// 		}else{
// 			session("vid",I("id"));
// 			$vid=session("vid");
// 			cookie("VID",I("id"));
// 			$village=M("village_info")->where("id=".$vid)->find();
// 			$this->assign('xqname',$village["name"]);
// 		}
	    session("vid",I("id"));
	    $vid=session("vid");
	    cookie("VID",I("id"));
	    $village=M("village_info")->where("id=".$vid)->find();		$roomlist=M("village_roominfo")->where("vid=".$vid." and name is null and tel is null")->select();
	    $this->assign('xqname',$village["name"]);		$this->assign('roomlist',$roomlist);
	    
		if(I("name") || I("phone") || I("roomid")){
			$token=session("token");
			if(empty($token)){
				$this->error("系统参数错误！");
			}
			if(!(I("name") && I("phone") && I("roomid") && I("verifyCode"))){
				$this->error("请将信息填写完整");
			}else{
				$data["openid"]=session("openid");
				if(!$data["openid"]){
					$this->error("没有获取到用户信息！");
					exit;
				}
				if(!SMSCheck($data["phone"],I("verifyCode"))){
					$this->error("验证码错误！");
					exit;
				}
				
				$village_user=M("village_user");
				$count=$village_user->where($data)->count();
				
				$data["token"]=$token;
				$data["phone"]=I("phone");
				$data["name"]=I("name");				$data["roomid"]=I("roomid");
				$data["villageID"]=$vid;
				$data["regtime"]=date("y-m-d H:i:s",time());								$roominfo=M("village_roominfo")->where("id=".I("roomid"))->find();				$data["building"]=$roominfo["building"];				$data["unit"]=$roominfo["unit"];				$data["room"]=$roominfo["room"];
				$ret="";
				if($count){
					$data["state"]=0;
					$ret=$village_user->where(array("openid"=>$data["openid"]))->data($data)->save();
				}else{
					$ret=$village_user->data($data)->add();
				}
				
				
				if($ret){
					session("upuserdata",1);
					$this->success("注册成功！",addons_url("Village://Index/index"));
					exit;
				}else{
					$this->error("注册失败".mysql_error());
					exit;
				}
			}
		}
		
		$this->display();
	}
}

