<?php

namespace Addons\Village\Controller;

class LogonController extends AdminController{
	public function _initialize(){
		header("Content-type: text/html; charset=utf-8"); 
	}
	
	//管理员登录
	public function admin(){
		if(I("phone")){
			$map['phone'] =I("phone");
			$map["password"]=md5(I("password"));
			$admin=M("village_admin")->where($map)->find();
			if($admin["id"]){
				session("aid",$admin["id"]);
				session("admin",$admin);
				//登录过期设置
				cookie("loginExpire",1,1800);
				//刷新登录信息
				$this->loginUpdate($admin["id"]);
				//登录成功获取一条最新的公告信息，第一次进入后弹窗
				$map1["type"]=3; 
				$notice=M("village_notice")->where($map1)->order('addtime desc')->find();
				cookie("NewNotice",$notice);
				
				$this->success("登录成功！",addons_url("Village://Admin/index"));
				exit;
			}else{
				$this->error("账号或密码错误！");
				exit;
			}
		}
		$this->display();
	}
	
	//管理员注册
	public function register(){
		if(I("phone")){
			$data["phone"]=I("phone");
			$data["password"]=md5(I("password"));
			$admin=M("village_admin")->data($data)->add();
			$admin=M("village_admin")->where($data)->find();
			if($admin["id"]){
				session("aid",$admin["id"]);
				session("admin",$admin);
				$this->loginUpdate($admin["id"]);
				$this->success("注册成功！",addons_url("Village://Admin/index"));
			}else{
				$this->error("注册失败！");
			}
		}
//		$this->display();
	}

	//退出登录
	public function logout(){
		session('admin','');
		cookie('loginExpire',0);
		$this->success("退出成功！",addons_url("Village://Logon/admin"));
	}
}

