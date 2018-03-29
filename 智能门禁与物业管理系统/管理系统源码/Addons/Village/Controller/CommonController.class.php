<?php

namespace Addons\Village\Controller;
use Home\Controller\AddonsController;

class CommonController extends AddonsController{
	public function _initialize(){
		header("Content-type: text/html; charset=utf-8");
		$user_agent = $_SERVER['HTTP_USER_AGENT'];
		if (strpos($user_agent, 'MicroMessenger') === false) {

		} 

		if(!$user || $user["openid"]=="-1" || $user["openid"]=="-2" || !session("vid") || session("upuserdata")){
			$map["openid"]=$this->openid();
			if($map["openid"]=="-1"){
				$this->error("openid获取失败",100000);
			}
		}
	}
	
	//登录判断
	public function is_login(){
		$map["openid"]=$this->openid();
		if($map["openid"]=="-1"){
			$this->error("openid获取失败",100000);
			return false;exit;
		}
		$map["token"]=$this->token();
		if(!$map){
			$this->error("参数错误！");
			return false;exit;
		}
		$user=M("village_user")->where($map)->find();
		if($user){			if($user['state']){
				session("user",$user);
				session("vid",$user["villageID"]);
				session("upuserdata",null);
				return true;exit;			}			else{				$this->error("账号通过审核！<br>请耐心等待物业审核！！");				return false;exit;							}
		}else{
		    session("register","register");
		    $this->assign ( 'error', "亲！您需要先注册呦！<br>赶快选择自己所在的小区吧！" ); 
		    $this->assign ( 'waitSecond', '3' );
		    $this->assign ( 'jumpUrl', addons_url('Village://Login/select'));
		    $this->display ( C ( 'TMPL_ACTION_ERROR' ) );
		    // 中止执行 避免出错后继续执行
		    exit ();
		}
		
	}
	
	
	//获取openid
	public function openid(){
		session("token","gh_98fb44038265");
		if(!session("openid") || session("openid")=="-1"){
			session("openid",get_openid());
//			session("openid","sdfasd123");
		}
		return session("openid");
	}
	
	//获取小区id 
	public function vid(){
		if(session("vid")){
			return session("vid");
		}
		$user["openid"]=$this->openid();
		if($user["openid"] && $user["openid"]!="-1"){
			$user=M("village_user")->where($user)->field("villageID")->find();
			return $user["villageID"];
		}
		
		
	}
	
	//获取token
	public function token(){
		if(!session("token")){
			session("token","gh_ea952be9b20d");
			if(I("token")){
				session("token",I("token"));
			}
		}
		return session("token");
	}
	
	//发送短信验证码
	public function verifyCode($mobile){
		if(!empty($mobile)){
			$this->ajaxReturn(SMSVerify($mobile));
		}
	}
	
	//根据回调信息进行跳转
	function change(){
		if(I("token")){
			session("token",I("token"));
		}
		echo get_openid();
	}
	
	//获取session中的用户信息
	function user($field){
		$user=session("user");
		if($field){
			return $user[$field];
		}else{
			return $user;
		}
	}
	
}
