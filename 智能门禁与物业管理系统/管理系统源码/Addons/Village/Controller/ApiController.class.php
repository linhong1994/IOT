<?php

namespace Addons\Village\Controller;

class ApiController extends CommonController{
	public function _initialize(){
		header("Content-type: text/html; charset=utf-8"); 
		$openid=$this->openid();
		if(!$openid){
			return false;
			exit;
		}
	}
	
	//发送短信验证码
	public function verifyCode(){
		$mobile=I("mobile");
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
	
	//返回公告信息列表
	public function NoticeList(){
		$data=false;
		if(I("p")){
			$map["vid"]=$this->user("villageID");
			$data=M("village_notice")->where($map)->order('addtime desc')->limit(((I("p")*10)+1),10)->select();
		}
		$this->ajaxReturn($data);
	}
	
	//报修接口
	function repair(){		$this->is_login();
		if(I("title")){
			if(I("title")){
				$data["title"]=I("title");
				$data["content"]=I("content");
				$data["openid"]=$this->openid();
				$data["vid"]=$this->user("villageID");
				if(!$data["vid"]){
					$this->error("报修信息提交失败,没有小区信息");
					exit;
				}
				$repair=session("repair".I("title"));
				if(!($data["title"]==$repair["title"] && $data["content"]==$repair["content"] && $data["openid"]==$repair["openid"] && $data["vid"]==$repair["vid"])){
					$data["time"]=get_time();
					if(!M("village_repair")->data($data)->add()){
						$this->error("报修信息提交失败".mysql_error());
						exit;
					}
					session("repair".I("title"),$data);
					$this->success("报修信息已提交");
					exit;
				}
				$this->error("请勿重复提交");
				exit;
			}else{
				$this->error("请提交完整信息");
				exit;
			}
		}
	}
	
	//上传图片
	function uploadimages(){
		$upload = new \Think\Upload();// 实例化上传类
	    $upload->exts      =     array('jpg', 'gif', 'png', 'jpeg');// 设置附件上传类型
	    $upload->rootPath = './Addons/Village/View/default/Public/uploads/';
	    $upload->savePath  =     ''; // 设置附件上传（子）目录
	    $upload->saveName = $this->openid()."_".date("YmdHis",time())."_".I('imageid');
	    $upload->subName = date("Y-m-d",time());
		
	    // 上传文件 
	    $info   =   $upload->uploadOne($_FILES['file']);
	    if(!$info) {// 上传错误提示错误信息
	        $arr=array('imagename'=>$_FILES['file']['name'],'savename'=>$info['savename'],'status'=>'filed','err'=>$upload->getError());
			$this->ajaxReturn($arr);
	    }else{// 上传成功
		   	$data['type']="";
			$data['id']="";
			$data['imageurl']="./Addons/Village/View/default/Public/uploads/".date("Y-m-d",time())."/".$info['savename'];
			$data['imagename']=$info['savename'];
			if(!M("village_uploadimage")->data($data)->add($data)){
				$arr=array('imagename'=>$info['name'],'savename'=>$info['savename'],'status'=>'filed','err'=>"图片信息录入数据失败");
			  	$this->ajaxReturn($arr);
			}
			$arr=array('imagename'=>$info['name'],'savename'=>$info['savename'],'status'=>'success','err'=>"");
			$this->ajaxReturn($arr);
	    }
	}
}

