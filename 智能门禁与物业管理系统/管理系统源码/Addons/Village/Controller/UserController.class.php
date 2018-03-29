<?php

namespace Addons\Village\Controller;

class UserController extends CommonController{
	public function index(){
		$this->is_login();
		$this->display();
	}
	
	//个人中心首页
	public function my(){
		$this->is_login();
		$user=M("village_user");
		$data=$user->where(array("id"=>$this->user("id")))->find();
		$this->assign('username',"*".mb_substr($data['name'], 1,7,'utf-8'));
		//print_r($data);
		$this->display();
	}
	
	
	//用户签到
	public function Sign(){
		$this->is_login();
		$userid=$this->user('id');
		$d=strtotime('-1 days');
		$predate=date('Y-m-d',$d);
		$map['signdate']=array('like',$predate."%");
		$map['userid']=$userid;
		$signhis=M("village_sign")->where($map)->find();
		if(I("type")){
			$data['userid']=$userid;
			$data['signdate']=get_time();
			$data['continuity']=$signhis["continuity"]+1;
			if(M("village_sign")->data($data)->add()){
				$this->success('签到成功');
			}
			else{
				$this->error('签到失败');
			}
		}
		$date=date('Y-m-d',time());
		$map['signdate']=array('like',$date."%");
		$signnow=M("village_sign")->where($map)->find();
		
		$maplist['userid']=$userid;
		$this->assign("signlist",M("village_sign")->where($maplist)->order('signdate desc')->limit(10)->select());
		if($signnow){
			$this->assign('signed','true');
			$this->assign("continuity",$signnow['continuity']);
		}
		else{
			$this->assign("continuity",$signhis['continuity']);
		}
		$this->display();
	}
	
	
	//我发布的拼车信息
	public function CarpoolingList(){
		$this->is_login();
		$map["uid"]=$this->user("id");
		$carpool=M("village_carpool")->where($map)->select();
//		print_r($carpool);exit;
		$this->assign("data",$carpool);
		$this->display();
	}
	
	//删除我发布的拼车信息
	public function delcarpool(){
		$this->is_login();
		//print_r(I('carpoolid'));
		if(count(I('carpoolid'))){
			$success=0;
			foreach(I('carpoolid') as $item){
				$map['id']=$item;
				if(M('village_carpool')->where($map)->delete()){
					$success++;
				}
			}
			if($success==0){
				$this->error("删除失败");
			}
			$this->success("删除成功");
		}
		$this->error("请选择要删除的对象");
	}
	
	//我发布的跳蚤市场宝贝
	public function FleamarketList(){
		$this->is_login();
		$maplist["uid"]=$this->user("id");
		$data=M("village_fleamarket")->where($maplist)->order('addtime desc')->limit(10)->select();
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
				$arr[$key]['image']="/weiphp/Addons/Village/View/default/Public/uploads/noimage.jpg";
			}
		}
		$this->assign("fleamarket",$arr);
		$this->display();
	}
	
		//跳蚤市场宝贝详情
	public function FleamarketDetail(){
		$this->is_login();
		if(I("fid")){
			$map["fid"]=I("fid");
			$data=M("village_fleamarket")->where($map)->find();
			$mapimage['id']=$data['fid'];
			$mapimage['type']='Fleamarket';
			$images=$image=M("village_uploadimage")->where($mapimage)->select();
			$data['images']=$images;
			if(!$images){
				$images[0]['imageurl']="/weiphp/Addons/Village/View/default/Public/uploads/noimage.jpg";
			}
			$this->assign('Fleamarket',$data);
			$this->display();
		}else{
			$this->display("FleamarketList");
		}
	}
	
	//删除发布的跳蚤市场
	public function delFleamarket(){
		$this->is_login();
		if(I('id')){
			$map['fid']=I('id');
			$imgmap['id']=I('id');
			$imgmap['type']="Fleamarket";
			$images=M("village_uploadimage")->where($imgmap)->select();
			foreach($images as $key=>$value){
				unlink($value['imageurl']);
			}
			M("village_uploadimage")->where($imgmap)->delete();
			if(M('village_fleamarket')->where($map)->delete()){
				$this->success("删除成功",U("FleamarketList"));
			}
		}
		$this->error("删除失败",U("FleamarketList"));
	}
	
	
	//我的房屋租赁
	public function Rentallist(){
		$this->is_login();
		$maplist["uid"]=$this->user("id");
		$data=M("village_rental")->where($maplist)->order('addtime desc')->limit(10)->select();
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
				$arr[$key]['image']="/weiphp/Addons/Village/View/default/Public/uploads/noimage.jpg";
			}
			//print_r($item['images']);
		}
		$this->assign("rental",$arr);
		$this->display();
	}
	
	//我的房屋详情
	public function RentalDetail(){
		$this->is_login();
		if(I("rid")){
			$map["rid"]=I("rid");
			$data=M("village_rental")->where($map)->find();
			$mapimage['id']=$data['rid'];
			$mapimage['type']='Rental';
			$images=$image=M("village_uploadimage")->where($mapimage)->select();
			if(!$images){
				$images[0]['imageurl']="/weiphp/Addons/Village/View/default/Public/uploads/noimage.jpg";
			}
			$data['images']=$images;
			$this->assign('Rental',$data);
			$this->display();
		}else{
			$this->display("Rentallist");
		}
	}
	
	//删除我发布的房屋信息
	public function delRental(){
		$this->is_login();
		$map['rid']=I('id');
		$imgmap['id']=I('id');
		$imgmap['type']="Rental";
		$images=M("village_uploadimage")->where($imgmap)->select();
		foreach($images as $key=>$value){
			unlink($value['imageurl']);
		}
		M("village_uploadimage")->where($imgmap)->delete();
		if(M('village_rental')->where($map)->delete()){
			$this->success("删除成功",U("RentalList"));
		}
		$this->error("删除失败",U("RentalList"));
	}
}

