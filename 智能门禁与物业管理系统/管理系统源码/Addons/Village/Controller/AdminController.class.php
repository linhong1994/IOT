<?php

namespace Addons\Village\Controller;
use Home\Controller\AddonsController;

class AdminController extends AddonsController{
	public function _initialize(){
		header("Content-type: text/html; charset=utf-8");
		//判断是否登录并刷新登录过期时间
		if(!$this->isLogon()){
			$this->error("请登录!",addons_url("Village://Logon/admin"));
		}
		$this->assign("admin",session("admin"));
		//$this->assign("grade",$this->grade_to_name($admin["grade"]));
	}
	
	//管理员等级所对应的名称
	public function grade_to_name($grade){
		switch ($grade) {
			case 0:
				return "物业";
				break;
			case 1:
				return "超级管理员";
				break;
		}
	}

	
	//首页
	public function index(){
		$admin=session("admin");
		if($admin["grade"]==0){
			$map["villageID"]=$admin["vid"];
			$map1["villageID"]=$admin["vid"];
			$map3["vid"]=$admin["vid"];
			$map4["vid"]=$admin["vid"];
			$noticeurl=U('adnoticelist');
		}else{
			$noticeurl=U('adminnoticelist');
		}
		//总用户数
		$userCount=M("village_user")->where($map)->count();
		
		//今日新增用户数
		$date=date('Y-m-d');
		$map1['regtime']  = array('gt',$date);
		$NewNumber=M("village_user")->where($map1)->count();
		
		//后台公告信息
		$map2["type"]=3; 
		$notice=M("village_notice")->where($map2)->order('addtime desc')->limit(10)->select();
		if($admin["grade"]==0){
			//待处理报修统计
			$map3["state"]=0;
			$repair=M("village_repair")->where($map3)->count();
			
			//待查看投诉统计 
			$map4["state"]=0;
			$complaint=M("village_complaint")->where($map4)->count();
			
		}
		//判断有没需要弹窗的最新公告
		if(cookie("NewNotice")){
			$this->assign("NewNotice",$NewNotice);
		}
		$this->assign('noticelist',M('village_adminnotice')->order('addtime desc')->limit(10)->select());
		$this->assign("complaint",$complaint);
		$this->assign("noticeurl",$noticeurl);
		$this->assign("repair",$repair);
		$this->assign("indexac",'active');
		$this->assign("userCount",$userCount);
		$this->assign("NewNumber",$NewNumber);
		$this->display();
	}
	


	//小区添加
	public function addVillage(){
		$this->admimnpower();
		if(I("name") || I("address") || I("tel") || I("property") || I("longitude") || I("latitude") || I("introduction")){
			if(I("name") && I("address") && I("tel") && I("property") && I("longitude") && I("latitude") && I("introduction")){
				$data["name"]=I("name");
				$data["address"]=I("address");
				$data["buildingNumber"]=I("buildingNumber");
				$data["tel"]=I("tel");
				$data["property"]=I("property");
				$data["longitude"]=I("longitude");
				$data["latitude"]=I("latitude");
				$data["introduction"]=I("introduction");
				$info=session('village');
				if($info['name']==$data['name'] && $info['adress']==$data['adress'] && $info['buildingNumber']==$data['buildingNumber'] && $info['tel']==$data['tel'] && $info['property']==$data['property'] && $info['longitude']==$data['longitude'] && $info['latitude']==$data['latitude'] && $info['introduction']==$data['introduction']){
					$this->error('请勿重复提交');
					exit;
				}
				session('village',$data);
				$info['savename']='./Addons/Village/View/default/Public/uploads/noimage.jpg';
				if($_FILES['image']){
					$upload = new \Think\Upload();// 实例化上传类
				    $upload->exts      =     array('jpg', 'gif', 'png', 'jpeg');// 设置附件上传类型
				    $upload->rootPath = './Addons/Village/View/default/Public/uploads/';
				    $upload->savePath  =     ''; // 设置附件上传（子）目录
				    $upload->saveName = "village_".date("YmdHis",time())."_".rand(0,100);
				    $upload->subName = 'villageimage';
					$info   =   $upload->uploadOne($_FILES['image']);
					if($info){
						$imgURL='./Addons/Village/View/default/Public/uploads/villageimage/'.$info['savename'];
					}
				}
				$data["imgURL"]=$imgURL;;
				$data["addDate"]=get_time();
				$village = M('village_info'); 
				if($village->data($data)->add()){
					$this->success("添加成功！",addons_url("Village://Admin/villageLists"));
					exit;
				}else{
					$this->error("添加失败");
					exit;
				}
			}else{
				$this->error("请提交完整信息!");
				exit;
			}
		}
		$this->assign("vilagemanac",'active');
		$this->assign("vilageaddac",'active');
		$this->display();
	}
	
	//小区列表
	public function villageLists(){
		$this->admimnpower();
		$User = M('village_info'); 
		if(I("villagesearch")){
			$map['name']=array('like','%'.I("villagesearch").'%');
			$this->assign('villagesearch',I("villagesearch"));
		}
		$count      = $User->where($map)->count();
		$Page       = new \Think\Page($count,25);
		$Page -> setConfig('header','<i>共%TOTAL_ROW%条 %NOW_PAGE%/%TOTAL_PAGE%</i>');
		$Page -> setConfig('first','首页');
		$Page -> setConfig('last','末页');
		$Page -> setConfig('prev','上一页');
		$Page -> setConfig('next','下一页');
		$Page -> setConfig('theme','%HEADER% %FIRST% %UP_PAGE% %LINK_PAGE% %DOWN_PAGE%  %END%');
		$show       = $Page->show();
		$list = $User->where($map)->order('addDate desc')->limit($Page->firstRow.','.$Page->listRows)->select();
		$this->assign('list',$list);
		$this->assign('page',$show);
		$this->assign("vilagemanac",'active');
		$this->assign("villagelistac",'active');
		$this->display(); 
	}
	
	//小区删除
	public function villageremove(){
		$this->admimnpower();
		if(I('vid')){
			$map['id']=I('vid');
			$villageinfo=M('village_info')->where($map)->find();
			if($villageinfo['villagefee']>0){
				$this->error('小区尚有物业费未提现，无法删除');
				exit;
			}
			if(M('village_info')->where($map)->delete()){
				$this->success('删除成功');
				exit;
			}
			else{
				$this->error('删除失败');
				exit;
			}
		}else{
			$this->error('参数错误');
			exit;
		}
	}
	
	//物业费提现记录
	public function admin_villagefeewithdrawalsrecord(){
		$this->admimnpower();
		if(I('vid')){
			$map['vid']=I('vid');
			$this->assign('vid',I('vid'));
		}
		$list=M('village_withdrawals')->where($map)->select();
		$listarray=array();
		foreach($list as $key=>$value){
			$vmap['id']=$value['vid'];
			$adminmap['id']=$value['adminid'];
			$admininfo=M('village_admin')->where($adminmap)->find();
			$villageinfo=M('village_info')->where($vmap)->find();
			$value['adminname']=$admininfo['name'];
			$value['villagename']=$villageinfo['name'];
			$listarray[$key]=$value;
		}
		$this->assign('villagelist',M('village_info')->select());
		$this->assign('list',$listarray);
		$this->assign("vilagemanac",'active');
		$this->assign("villagefeewithdrawalsrecordac",'active');
		$this->display();
	}
	
	//管理员/物业列表
	public function adminLists(){
		$this->admimnpower();
		$map['grade'] ='0';
		$User = M('village_admin'); 
		if(I("searchadmin")){
			$this->assign('searchadmin',I("searchadmin"));
			$where['name']  = array('like', '%'.I("searchadmin").'%');
			$where['phone']  = array('like','%'.I("searchadmin").'%');
			$where['_logic'] = 'or';
			$map['_complex'] = $where;
		}
		$count      = $User->where($map)->count();
		$Page       = new \Think\Page($count,25);
		$Page -> setConfig('header','<i>共%TOTAL_ROW%条 %NOW_PAGE%/%TOTAL_PAGE%</i>');
		$Page -> setConfig('first','首页');
		$Page -> setConfig('last','末页');
		$Page -> setConfig('prev','上一页');
		$Page -> setConfig('next','下一页');
		$Page -> setConfig('theme','%HEADER% %FIRST% %UP_PAGE% %LINK_PAGE% %DOWN_PAGE%  %END%');
		$show       = $Page->show();
		$list = $User->where($map)->order('adddate desc')->limit($Page->firstRow.','.$Page->listRows)->select();
		$villagelist=M('village_info')->order('adddate desc')->select();
		$this->assign('villagelist',$villagelist);
		$this->assign('list',$list);
		$this->assign('page',$show);
		$this->assign("vilagemanac",'active');
		$this->assign("villageadminmanac",'active');
		$this->display(); 
	}

	//添加管理员/物业
	public function addAdmin(){
		$this->admimnpower();
		if(I("phone") || I("password") || I("name") || I("grade") || I("vid")){
			$map['phone']=I("phone");
			if(M('village_admin')->where($map)->find()){
				$this->error('该手机已注册');
					exit;
			}
			if(I("phone") || I("password") || I("name") || I("grade") || I("vid")){
				$data["phone"]=I("phone");
				$data["password"]=md5(I("password"));
				$data["name"]=I("name");
				$data["grade"]=I("grade");
				$data["vid"]=I("vid");
				$info=session('admininfo');
				if($info['phone']==$data['phone'] && $info['password']==$data['password'] && $info['name']==$data['name'] && $info['grade']==$data['grade'] && $info['vid']==$data['vid']){
					$this->error('请勿重复提交');
					exit;
				}

				session('admininfo',$data);
				$data["adddate"]=get_time();
				$admin = M('village_admin'); 
				if($admin->data($data)->add()){
					$this->success("添加成功！",addons_url("Village://Admin/adminLists"));
					exit;
				}else{
					$this->error("添加失败");
					exit;
				}
			}else{
				$this->error("请提交完整信息!");
				exit;
			}
		}
		$this->display();
	} 
	
	//删除管理员
	public function removeAdmin(){
		$this->admimnpower();
		if(I('adminid')){
			$map['id']=I('adminid');
			if(M('village_admin')->where($map)->delete())
			{
				$this->success("删除成功!",U("adminLists"));
				exit;
			}
			else{
				$this->error("删除失败!");
				exit;
			}
		}
		else{
			$this->error("参数错误!");
			exit;
		}
		
	}
	
	//活动列表
	public function activitylist(){
		$this->admimnpower();
		if(I('activitykey')){
			$map['title']=array('like','%'.I('activitykey').'%');
			$this->assign('activitykey',I('activitykey'));
		}
		$count      = M('village_activity')->where($map)->count();
		$Page       = new \Think\Page($count,25);
		$Page -> setConfig('header','<i>共%TOTAL_ROW%条 %NOW_PAGE%/%TOTAL_PAGE%</i>');
		$Page -> setConfig('first','首页');
		$Page -> setConfig('last','末页');
		$Page -> setConfig('prev','上一页');
		$Page -> setConfig('next','下一页');
		$Page -> setConfig('theme','%HEADER% %FIRST% %UP_PAGE% %LINK_PAGE% %DOWN_PAGE%  %END%');
		$show       = $Page->show();
		$activitylist=M('village_activity')->where($map)->order('addtime desc')->limit($Page->firstRow.','.$Page->listRows)->select();
		$this->assign('activitylist',$activitylist);
		$this->assign('page',$show);
		$this->assign("activityac",'active');
		$this->assign("activitylistac",'active');
		$this->display();	
	}
	
	//添加活动
	public function activityadd(){
		$this->admimnpower();
		if(I('title')||I('villageid')||I('type')||I('activitytime')||I('tel')||I('number')||I('registerTime')){
			if(I('title')&&I('villageid')&&I('type')&&I('activitytime')&&I('tel')&&I('number')&&I('registerTime')&&$_FILES['activityimage']){
				foreach(I('villageid') as $key=>$value)
				{
					$vid.=",".$value.",";
				}
				$time=split('/', I('activitytime'));
				$data["title"]=I("title");
				$data["vid"]=$vid;
				$data["tel"]=I("tel");
				$data["describe"]=I("describe");
				$data["startTime"]=$time[0];
				$data["stopTime"]=$time[1];
				$data["registerTime"]=I("registerTime");
				$data["number"]=I("number");
				$data["type"]=I("type")-1;
				$info=session('activityinfo');
				if($info['title']==$data['title'] && $info['vid']==$data['vid'] && $info['tel']==$data['tel'] && $info['describe']==$data['describe'] && $info['startTime']==$data['startTime'] && $info['stopTime']==$data['stopTime'] && $info['registerTime']==$data['registerTime'] && $info['number']==$data['number'] && $info['type']==$data['type']){
					$this->error('请勿重复提交');
					exit;
				}
				session('activityinfo',$data);
				$upload = new \Think\Upload();// 实例化上传类
			    $upload->exts      =     array('jpg', 'gif', 'png', 'jpeg');// 设置附件上传类型
			    $upload->rootPath = './Addons/Village/View/default/Public/uploads/';
			    $upload->savePath  =     ''; // 设置附件上传（子）目录
			    $upload->saveName = "village_".date("YmdHis",time())."_".rand(0,100);
			    $upload->subName = 'activityimage';
				$imageinfo   =   $upload->uploadOne($_FILES['activityimage']);
				$imgURL='./Addons/Village/View/default/Public/uploads/activityimage/'.$imageinfo['savename'];
				if(!$imageinfo){
					$this->error("图片上传失败");
					exit;
				}
				$data["banner"]=$imgURL;
				$data["addtime"]=get_time();
				if(M('village_activity')->data($data)->add()){
					$this->success("添加成功！",U('activitylist'));
					exit;
				}else{
					$this->error("添加失败");
					exit;
				}
			}
			else{
				$this->error("请提交完整信息");
				exit;
			}
			
		}
		$villagelist=M('village_info')->order('adddate desc')->select();
		$this->assign('villagelist',$villagelist);
		$this->assign("activityac",'active');
		$this->assign("activityaddac",'active');
		$this->display();
	}
	
	//删除活动
	public function deleteactivity(){
		$this->admimnpower();
		if(I('activityid')){
			$map['id']=I('activityid');
			$activity=M('village_activity')->where($map)->find();
			unlink($activity['banner']);
			if(M('village_activity')->where($map)->delete())
			{
				$this->success("删除成功!",U("activitylist"));
				exit;
			}
			else{
				$this->error("删除失败!");
				exit;
			}
		}
		else{
			$this->error("参数错误!");
			exit;
		}
	}
	
	//活动详情
	public function activitydetail(){
		$this->admimnpower();
		if(I('activityid')){
			$map['id']=I('activityid');
			$activityinfo=M('village_activity')->where($map)->find();
			if($activityinfo){
				$village=split(",,", substr($activityinfo['vid'], 1,strlen($activityinfo['vid'])-2));
				$villagelist=array();
				foreach($village as $key=>$value){
					$vmap['id']=$value;
					$villagelist[$key]=M('village_info')->where($vmap)->find();
				}
				$this->assign('village',$villagelist);
				$this->assign('activity',$activityinfo);
				$this->assign("activityac",'active');
				$this->assign("activitylistac",'active');
				$this->display();
				exit;
			}
			$this->error('参数错误');
			exit;
		}
		$this->display('activitylist');
	}
	
	//广告列表
	public function advertisementlist(){
		$this->admimnpower();
		$adlist=M('village_advertisement')->order('addtime desc')->select();
		$this->assign('adlist',$adlist);
		$this->assign("advertisementac",'active');
		$this->assign("advertisementlistac",'active');
		$this->display();
	}
	
	
	//广告更新
	public function advertisementupdate(){
		$this->admimnpower();
		if(I('adid')){
			$map['id']=I('adid');
			if(I('type')!='update'){
				$adinfo=M('village_advertisement')->where($map)->find();
				$this->assign('adinfo',$adinfo);
				$this->assign("advertisementac",'active');
				$this->assign("advertisementlistac",'active');
				$this->display();
			}else{
				$adinfo=M('village_advertisement')->where($map)->find();
				$data['adtitle']=I('adtitle');
				$data['adposition']=I('adposition');
				$data['addes']=I('addes');
				if($_FILES['adimage']['size']>0){
					$upload = new \Think\Upload();// 实例化上传类
				    $upload->exts      =     array('jpg', 'gif', 'png', 'jpeg');// 设置附件上传类型
				    $upload->rootPath = './Addons/Village/View/default/Public/uploads/';
				    $upload->savePath  =     ''; // 设置附件上传（子）目录
				    $upload->saveName = "adimage_".date("YmdHis",time())."_".rand(0,100);
				    $upload->subName = 'adimge';
					$imageinfo   =   $upload->uploadOne($_FILES['adimage']);
					$imgURL='./Addons/Village/View/default/Public/uploads/adimge/'.$imageinfo['savename'];
					if(!$imageinfo){
						$this->error("图片上传失败");
						exit;
					}
					$data["adimage"]=$imgURL;
					unlink($adinfo['adimage']);
				}
				if(M('village_advertisement')->where($map)->save($data)){
					$this->success('更新成功',U('advertisementlist'));
					exit;
				}else{
					$this->error("更新失败");
					exit;
				}

			}
		}else{
			$this->error("参数错误",U('advertisementlist'));
			exit;
		}
	}
	
	//广告删除
	public function advertisementremove(){
		$this->admimnpower();
		if(I('adid')){
			$map['id']=I('adid');
			$adinfo=M('village_advertisement')->where($map)->find();
			unlink($adinfo['adimage']);
			if(M('village_advertisement')->where($map)->delete()){
				$this->success('删除成功',U('advertisementlist'));
				exit;
			}
			$this->error('删除失败',U('advertisementlist'));
			exit;
		}
		$this->error('参数错误',U('advertisementlist'));
		exit;
	}

	//添加广告
	public function advertisementadd(){
		$this->admimnpower();
		if(I("adtitle")||I('adposition')||I('addes'))
		{
			if(I("adtitle")&&I('adposition')&&I('addes')&&$_FILES['adimage']){
				$data['adtitle']=I('adtitle');
				$data['adposition']=I('adposition');
				$data['addes']=I('addes');
				$adinfo=session('adinfo');
				if($data['adtitle']!=$adinfo['adtitle']||$data['adposition']!=$adinfo['adposition']||$data['addes']!=$adinfo['addes']){
					$upload = new \Think\Upload();// 实例化上传类
				    $upload->exts      =     array('jpg', 'gif', 'png', 'jpeg');// 设置附件上传类型
				    $upload->rootPath = './Addons/Village/View/default/Public/uploads/';
				    $upload->savePath  =     ''; // 设置附件上传（子）目录
				    $upload->saveName = "adimage_".date("YmdHis",time())."_".rand(0,100);
				    $upload->subName = 'adimge';
					$imageinfo   =   $upload->uploadOne($_FILES['adimage']);
					$imgURL='./Addons/Village/View/default/Public/uploads/adimge/'.$imageinfo['savename'];
					if(!$imageinfo){
						$this->error("图片上传失败");
						exit;
					}
					$data["adimage"]=$imgURL;
					$data["addtime"]=get_time();
					if(M('village_advertisement')->data($data)->add()){
						$this->success("添加成功！",U('advertisementlist'));
						exit;
					}else{
						$this->error("添加失败");
						exit;
					}
				}
			}
			else{
				$this->error("请填写完整信息");
			}
		}
		$this->assign("advertisementac",'active');
		$this->assign("advertisementaddac",'active');
		$this->display();
	}


	//平台公告
	public function adminnoticelist(){
		$this->admimnpower();
		if(I('noticekey')){
			$map['ntitle']=array('like','%'.I('noticekey').'%');
			$this->assign('noticekey',I('noticekey'));
		}
		$count      = M('village_adminnotice')->where($map)->count();
		$Page       = new \Think\Page($count,25);
		$Page -> setConfig('header','<i>共%TOTAL_ROW%条 %NOW_PAGE%/%TOTAL_PAGE%</i>');
		$Page -> setConfig('first','首页');
		$Page -> setConfig('last','末页');
		$Page -> setConfig('prev','上一页');
		$Page -> setConfig('next','下一页');
		$Page -> setConfig('theme','%HEADER% %FIRST% %UP_PAGE% %LINK_PAGE% %DOWN_PAGE%  %END%');
		$show       = $Page->show();
		$noticelist=M('village_adminnotice')->where($map)->order('addtime desc')->limit($Page->firstRow.','.$Page->listRows)->select();
		$this->assign('noticelist',$noticelist);
		$this->assign("adminnoticelistac",'active');
		$this->assign('page',$show);
		$this->display();
	}
	
	//发布平台公告
	public function adminnoticeadd(){
		$this->admimnpower();
		if(I('ntitle')&&I('nbody')){
			$data['ntitle']=I('ntitle');
			$data['nbody']=I('nbody');
			$data['addtime']=get_time();
			if(M('village_adminnotice')->data($data)->add()){
				$this->success('发布成功',U('adminnoticelist'));
				exit;
			}
			$this->error('发布失败',U('adminnoticelist'));
			exit;
		}
		else{
			$this->error('请填写完整信息');
			exit;
		}
	}
	
	//删除公告
	public function adminnoticeremove(){
		$this->admimnpower();
		if(I('nid')){
			$map['id']=I('nid');
			if(M('village_adminnotice')->where($map)->delete()){
				$this->success('删除成功',U('adminnoticelist'));
				exit;
			}
			$this->error('删除失败',U('adminnoticelist'));
			exit;
		}
		$this->error('参数错误',U('adminnoticelist'));
		exit;
	}
	
	
	
	
	
	//------------------物业功能------------------------------
	//用户列表
	public function userLists(){
		$User = M('village_user');
		$admin=session('admin');
		if(I("searchuser")){
			$this->assign('searchuser',I("searchuser"));
			$where['name']  = array('like', '%'.I("searchuser").'%');
			$where['phone']  = array('like','%'.I("searchuser").'%');
			$where['_logic'] = 'or';
			$map['_complex'] = $where;
		}
		if(I("state")){
			$map['state']=I("state")-1;
			$this->assign('state',I("state"));	
		}
		if($admin['grade']==0){
			$map['villageID']=$admin['vid'];
		}
		$map["type"]=0;
		$count      = $User->where($map)->count();
		$Page       = new \Think\Page($count,25);
		$Page -> setConfig('header','<i>共%TOTAL_ROW%条 %NOW_PAGE%/%TOTAL_PAGE%</i>');
		$Page -> setConfig('first','首页');
		$Page -> setConfig('last','末页');
		$Page -> setConfig('prev','上一页');
		$Page -> setConfig('next','下一页');
		$Page -> setConfig('theme','%HEADER% %FIRST% %UP_PAGE% %LINK_PAGE% %DOWN_PAGE%  %END%');
		$show       = $Page->show();
		$userlist = $User->where($map)->order('regtime desc')->limit($Page->firstRow.','.$Page->listRows)->select();
		$list=array();
		foreach($userlist as $key=>$value){
			$villagemap['id']=$value['villageID'];
			$villageinfo=M('village_info')->where($villagemap)->find();
			$value['villagename']=$villageinfo['name'];
			$list[$key]=$value;
		}
		$this->assign('list',$list);
		$this->assign('page',$show);
		$this->assign("userac",'active');
		$this->assign("userlistac",'active');
		$this->display(); 
	}
	
	//查看用户信息
	public function userDetail(){
		
		if(I('userid')){
			$map['id']=I('userid');
			$userinfo=M('village_user')->where($map)->find();
			if($userinfo){
			    $map1["vid"]=$userinfo["villageID"];
			    $map1["building"]=$userinfo["building"];
			    $map1["unit"]=$userinfo["unit"];
			    $map1["room"]=$userinfo["room"];
			    $roominfo=M("village_roominfo")->where($map1)->find();
				
			    $map2["vid"]=$userinfo["villageID"];
				$locklist=M("village_lock")->where($map2)->select();
				
				$lockil=explode(",,", substr($userinfo['lockid'], 1,strlen($userinfo['lockid'])-2));
				
				$lockvn=array();
				foreach($lockil as $key=>$value){
					$lmap['id']=$value;
					$lockar.=$value. ",";
					$lockvn[$key]=M('village_lock')->where($lmap)->find();
				}
				if($userinfo["type"]){
					$userinfo["lock"]=1;
				}
				elseif(($userinfo["lstime"] == null) || ($userinfo["letime"] == null)){
					$userinfo["lock"]=0;
				}
				elseif(strtotime(get_time()) <= strtotime($userinfo["letime"])){
					$userinfo["lock"]=1;
				}
				else{
					$userinfo["lock"]=0;
				}
				$this->assign('lockar',substr($lockar,0,strlen($lockar)-2));
				$this->assign('lockvn',$lockvn);
				$this->assign('locklist',$locklist);
				$this->assign('roominfo',$roominfo);
				$this->assign('userinfo',$userinfo);
				$this->display();
			}
			else{
				$this->error("参数错误");
				exit;	
			}
		}
		else{
			$this->error("参数错误");
			exit;
		}
	}
	
	//用户审核
	public function userexamine(){
		//$this->villageadminpower();
		if(I('userid')){
			$map['id']=I('userid');
			$data['state']=1;
			if(M('village_user')->where($map)->save($data)){
				$userinfo = M('village_user')->where($map)->find();
				$roominfo["uid"]=$userinfo['id'];
				$roominfo["name"]=$userinfo['name'];
				$roominfo["tel"]=$userinfo['phone'];
				M('village_roominfo')->where("id=".$userinfo['roomid'])->save($roominfo);
				$this->success("审核通过",U('userDetail',array('userid'=>I('userid'))));
			}
			else{
				$this->error("审核失败");
				exit;	
			}
		}
		else{
			$this->error("参数错误");
			exit;
		}
	}
	
	//删除用户
	public function deleteuser(){
		$this->villageadminpower();
		if(I('userid')){
			$map['id']=I('userid');
			if(M('village_user')->where($map)->delete()){
				$this->success("删除成功",U('userLists'));
			}
			else{
				$this->error("删除失败");
				exit;	
			}
		}
		else{
			$this->error("参数错误");
			exit;
		}
	}
	
	//设置为物业微信号
	public function propertySetup(){
		if(I('userid')){
			$map['id']=I('userid');
			if(M('village_user')->where($map)->setField('type',1)){
				$this->success("设置成功",U('userLists'));
			}
			else{
				$this->error("设置失败");
				exit;	
			}
		}
	}
	
	//物业列表（微信）
	function propertyLists(){
		$user = M('village_user');
		$admin=session('admin');
		if(I("searchuser")){
			$this->assign('searchuser',I("searchuser"));
			$map['name']  = array('like', '%'.I("searchuser").'%');
		}
		if(I("state")){
			$map['state']=I("state")-1;
			$this->assign('state',I("state"));	
		}
		if($admin['grade']==0){
			$map['villageID']=$admin['vid'];
		}
		$map['type']=1;
		$count      = $user->where($map)->count();

		$Page       = new \Think\Page($count,25);
		$Page -> setConfig('header','<i>共%TOTAL_ROW%条 %NOW_PAGE%/%TOTAL_PAGE%</i>');
		$Page -> setConfig('first','首页');
		$Page -> setConfig('last','末页');
		$Page -> setConfig('prev','上一页');
		$Page -> setConfig('next','下一页');
		$Page -> setConfig('theme','%HEADER% %FIRST% %UP_PAGE% %LINK_PAGE% %DOWN_PAGE%  %END%');
		$show       = $Page->show();
		$userlist = $user->where($map)->order('regtime desc')->limit($Page->firstRow.','.$Page->listRows)->select();
//		print_r($userlist);
		$list=array();
		foreach($userlist as $key=>$value){
			$villagemap['id']=$value['villageID'];
			$villageinfo=M('village_info')->where($villagemap)->find();
			$value['villagename']=$villageinfo['name'];
			$list[$key]=$value;
		}
		
		$this->assign('list',$list);
		$this->assign('page',$show);
		$this->assign("userac",'active');
		$this->assign("userlistac",'active');
		$this->display("propertyLists"); 
	}
	
	//移除管理员
	public function deleteproperty(){
		if(I("userid")){
			$user = M('village_user');
			$map["id"]= I("userid");
			$user = $user->where($map)->setField('type',0);
			$this->propertyLists();
		}
	}
	
	//添加管理员微信
	public function addproperty(){
		if(I("userid")){
			$user = M('village_user');
			$map["id"]= I("userid");
			$user = $user->where($map)->setField('type',1);
			$this->propertyLists();
		}
	}
	
	
	//物业费列表
	public function villagefeelist(){
		$this->villageadminpower();
		$admin=session('admin');
		$map['villageID']=$admin['vid'];
		$map['state']='1';
		$count      =count(M('village_user')->where($map)->distinct(true)->field('villageID,building,unit,room')->select());
		$Page       = new \Think\Page($count,25);
		$Page -> setConfig('header','<i>共%TOTAL_ROW%条 %NOW_PAGE%/%TOTAL_PAGE%</i>');
		$Page -> setConfig('first','首页');
		$Page -> setConfig('last','末页');
		$Page -> setConfig('prev','上一页');
		$Page -> setConfig('next','下一页');
		$Page -> setConfig('theme','%HEADER% %FIRST% %UP_PAGE% %LINK_PAGE% %DOWN_PAGE%  %END%');
		$show       = $Page->show();
		$room=M('village_user')->where($map)->distinct(true)->field('villageID,building,unit,room')->order('villageID,building,unit,room')->limit($Page->firstRow.','.$Page->listRows)->select();
		$roomarray=array();
		foreach($room as $key=>$value){
			$value['feedate']=date('Y年m月',strtotime("last month"));
			$fee=M('village_fee')->where($value)->find();
			$value['fee']="";
			if($fee){
				$value['fee']=$fee['fee'];
				$value['paystate']=$fee['paystate'];
				$value['feeid']=$fee['id'];
				if(I('insertstate')==1){
					continue;
				}
			}else{
				if(I('insertstate')==2){
					continue;
				}
			}
			$roomarray[$key]=$value;
		}
		//$this->Ajaxreturn($roomarray);
		$this->assign('insertstate',I('insertstate'));
		$this->assign('page',$show);
		$this->assign('roomarray',$roomarray);
		$this->assign("villagefeeac",'active');
		$this->assign("villagefeelistac",'active');
		$this->display();
	}
	
	//物业费缴费情况
	public function villagefeesituation(){
		$this->villageadminpower();
		$admin=session('admin');
		$map['villageID']=$admin['vid'];
		if(I('selectdate')){
			$this->assign('selectdate',I('selectdate'));
			$map['feedate']=I('selectdate');
		}
		$list=M('village_fee')->where($map)->select();
		$this->assign('list',$list);
		$select=M('village_fee')->distinct(true)->field('feedate')->order('feedate desc')->select();
		$this->assign('select',$select);
		$this->assign("villagefeeac",'active');
		$this->assign("villagefeesituationac",'active');
		$this->display(); 
	}
	
	//物业费录入
	public function villagefeeinsert(){
		$this->villageadminpower();
		if(I('building')!=null&&I('unit')!=null&&I('room')!=null&&I('feedate')!=null&&I('villagefee')!=null&&I('publicwaterfee')!=null&&I('publicelectricityfee')!=null&&I('otherfee')!=null){
			$admin=session('admin');
			$data['villageID']=$admin['vid'];
			$data['building']=I('building');
			$data['unit']=I('unit');
			$data['room']=I('room');
			$data['feedate']=I('feedate');
			if(M('village_fee')->where($data)->find()){
				$result=array('result'=>'已录入，不能重复录入');
				$this->Ajaxreturn($result);
			}
			$data['fee']=I('villagefee')+I('publicwaterfee')+I('publicelectricityfee')+I('otherfee');
			$data['feeinfo']="物业费:".I('villagefee').",公摊水费:".I('publicwaterfee').",公摊电费:".I('publicelectricityfee').",其他费用:".I('otherfee');
			$data['paystate']='0';
			$data['addtime']=get_time();
			if(M('village_fee')->data($data)->add()){
				$this->success("录入成功");
				exit;
			}else{
				$this->error("录入失败");
				exit;
			}
		}
		else{
			$this->error("参数错误");
			exit;
		}
	}
	//物业费导入
	public function importfee(){
	    $admin=session('admin');
	    if(!$admin["vid"]){
	        $this->error("导入失败!");
	        exit;
	    }
		if(empty ($_FILES ['excelfile'] ['name'])){
	        $this->error("文件上传失败!");
	        exit;			
		}
		$tmp_f = $_FILES ['excelfile'] ['tmp_name'];
		$f_types = explode ( ".", $_FILES ['excelfile'] ['name'] );
		$f_type = $f_types [count ( $f_types ) - 1];
		if (strtolower($f_type)!= "xls" && strtolower($f_type)!= "xlsx"){
	        $this->error("请上传excel文件!");
	        exit;			
		}
		vendor('PHPExcel.IOFactory');
		$objPHPExcel = \PHPExcel_IOFactory::load($tmp_f);
		$objPHPExcel->setActiveSheetIndex(0);
		$sheet0=$objPHPExcel->getSheet(0);
		$rowCount=$sheet0->getHighestRow();//excel行数
        if(($objPHPExcel->getActiveSheet()->getCell("A1")->getValue()=="楼栋") && ($objPHPExcel->getActiveSheet()->getCell("B1")->getValue()=="单元") && ($objPHPExcel->getActiveSheet()->getCell("C1")->getValue()=="室") && ($objPHPExcel->getActiveSheet()->getCell("D1")->getValue()=="物业费") && ($objPHPExcel->getActiveSheet()->getCell("E1")->getValue()=="公摊水费") && ($objPHPExcel->getActiveSheet()->getCell("F1")->getValue()=="公摊电费") && ($objPHPExcel->getActiveSheet()->getCell("G1")->getValue()=="其他费用")){
			for ($i = 2; $i <= $rowCount; $i++){
				$map['building']=$objPHPExcel->getActiveSheet()->getCell("A".$i)->getValue();
				$map['unit']=$objPHPExcel->getActiveSheet()->getCell("B".$i)->getValue();
				$map['room']=$objPHPExcel->getActiveSheet()->getCell("C".$i)->getValue();
				$map2=$map;
				$map['vid']=$admin["vid"];
				if(M('village_roominfo')->where($map)->count()){
					if(M('village_roominfo')->where($map)->find()['uid'] != null){
						$map2['villageID']=$admin["vid"];
						$map2['feedate']=date('Y年m月',strtotime("last month"));
						if(M('village_fee')->where($map2)->count()){
							continue;
						}
						$map2['fee']=$objPHPExcel->getActiveSheet()->getCell("D".$i)->getValue() + $objPHPExcel->getActiveSheet()->getCell("E".$i)->getValue() + $objPHPExcel->getActiveSheet()->getCell("F".$i)->getValue() + $objPHPExcel->getActiveSheet()->getCell("G".$i)->getValue();
						$map2['addtime']=get_time();
						$map2['feeinfo']="物业费:".$objPHPExcel->getActiveSheet()->getCell("D".$i)->getValue().",公摊水费:".$objPHPExcel->getActiveSheet()->getCell("E".$i)->getValue().",公摊电费:".$objPHPExcel->getActiveSheet()->getCell("F".$i)->getValue().",其他费用:".$objPHPExcel->getActiveSheet()->getCell("G".$i)->getValue()."";
						if(M('village_fee')->data($map2)->add()){
							continue;
						}else{
							$this->error("导入意外终止，请检查数据是否错误！");
							exit;
						}
					}
				}
			}
			$this->success("导入完成!",U("villagefeelist"));
			exit;
		}
        else{
	        $this->error("格式错误，请下载模板编辑!");
	        exit;	
		}
	}
	//线下支付
	public function villagefeepayforoutline(){
		$this->villageadminpower();
		if(I('feeid')){
			$data['paystate']='1';
			$data['paytype']='1';
			$map['id']=I('feeid');
			if(M('village_fee')->where($map)->save($data)){
				$this->success('操作成功',U('villagefeelist'));
				exit;
			}
			$this->error('操作失败');
			exit;
		}
		$this->error('参数错误');
		exit;
	}
	
	//物业费查询
	public function villagefeeselect(){
		$this->villageadminpower();
		$admin=session('admin');
		$map["villageID"]=$admin['vid'];
		$map["type"]=1;
		$wx=M("village_user")->where($map)->field('name,openid')->select();
		
		$vmap['id']=$admin['vid'];
		$villagefee=M('village_info')->where($vmap)->field('villagefee')->find();
		$this->assign('villagefee',$villagefee['villagefee']);
		
		$mmap['vid']=$admin['vid'];
		$count      =M('village_withdrawals')->where($mmap)->count();
		$Page       = new \Think\Page($count,25);
		$Page -> setConfig('header','<i>共%TOTAL_ROW%条 %NOW_PAGE%/%TOTAL_PAGE%</i>');
		$Page -> setConfig('first','首页');
		$Page -> setConfig('last','末页');
		$Page -> setConfig('prev','上一页');
		$Page -> setConfig('next','下一页');
		$Page -> setConfig('theme','%HEADER% %FIRST% %UP_PAGE% %LINK_PAGE% %DOWN_PAGE%  %END%');
		$show       = $Page->show();
		$list=M('village_withdrawals')->where($mmap)->order('datetime desc')->limit($Page->firstRow.','.$Page->listRows)->select();
		$listarray=array();
		foreach($list as $key=>$value){
			$adminmap['id']=$value['adminid'];
			$value['admininfo']=M('village_admin')->where($adminmap)->find();
			$listarray[$key]=$value;
		}
		$this->assign('page',$show);
		$this->assign('wx',$wx);
		$this->assign('listarray',$listarray);
		$this->assign("villagefeeac",'active');
		$this->assign("villagefeeselectac",'active');
		$this->display();
	}
	
	//物业费提现
	public function villagefeewithdrawals(){
		$this->villageadminpower();
		if(I('fee') && I("openid")){
			$admin=session('admin');
			$vmap['id']=$admin['vid'];
			$villagefee=M('village_info')->where($vmap)->field('villagefee')->find();
			if(I('fee')>$villagefee['villagefee']||I('fee')<0||I('fee')==0){
				$this->error('输入金额错误');
				exit;
			}
			$map["openid"]=I('openid');
			$map["villageID"]=$admin['vid'];
			$user=M("village_user")->where($map)->find();
			if(!$user){
			    $this->error('提现微信用户信息错误！');
			    exit;
			}
// 			print_r($user);exit;
			$data['vid']=$admin['vid'];
			$data['money']=I('fee');
			$data['name']=$user["name"];
			$data['openid']=I('openid');
			$data['datetime']=get_time();
			$data['adminid']=$admin['id'];
			$data['surplus']=$villagefee['villagefee']-I('fee');
			$newdata['villagefee']=$data['surplus'];
			
			
			$return=$this->wxtransfers(I('openid'),I('fee'));
			if($return=="SUCCESS"){
			    M('village_info')->where($vmap)->save($newdata);
			    if(M('village_withdrawals')->data($data)->add()){
			        $this->success('提现成功',U('villagefeeselect'));
			        exit;
			    }
			    
			}else if($return=="CA证书出错，请登录微信支付商户平台下载证书"){
			    $this->error("提现失败请重新提交，或联系管理员");
			}else{
			    $this->error($return);
			}
			
			$this->error('提现失败');
			exit;
		}
		$this->error('参数错误');
		exit;
	}
	
	//物业费缴费记录
	public function villagefeerecord(){
		$this->villageadminpower();
		$admin=session('admin');
		$map['villageID']=$admin['vid'];
		$map['paystate']="1";
		$count      = M('village_fee')->where($map)->count();
		$Page       = new \Think\Page($count,25);
		$Page -> setConfig('header','<i>共%TOTAL_ROW%条 %NOW_PAGE%/%TOTAL_PAGE%</i>');
		$Page -> setConfig('first','首页');
		$Page -> setConfig('last','末页');
		$Page -> setConfig('prev','上一页');
		$Page -> setConfig('next','下一页');
		$Page -> setConfig('theme','%HEADER% %FIRST% %UP_PAGE% %LINK_PAGE% %DOWN_PAGE%  %END%');
		$show       = $Page->show();
		$village_fee=M('village_fee')->where($map)->order('villageID,building,unit,room')->limit($Page->firstRow.','.$Page->listRows)->select();
		$this->assign('village_fee',$village_fee);
		$this->assign('page',$show);
		$this->assign("villagefeeac",'active');
		$this->assign("villagefeerecordac",'active');
		$this->display();
		
	}
	
	//小区通知
	public function villagenoticelist(){
		$this->villageadminpower();
		$admin=session('admin');
		$map['vid']=$admin['vid'];
		$map['type']='0';
		if(I('keyword')){
			$map['title']=array('like','%'.I('keyword').'%');
			$this->assign('keyword',I('keyword'));
		}
		$count      = M('village_notice')->where($map)->count();
		$Page       = new \Think\Page($count,25);
		$Page -> setConfig('header','<i>共%TOTAL_ROW%条 %NOW_PAGE%/%TOTAL_PAGE%</i>');
		$Page -> setConfig('first','首页');
		$Page -> setConfig('last','末页');
		$Page -> setConfig('prev','上一页');
		$Page -> setConfig('next','下一页');
		$Page -> setConfig('theme','%HEADER% %FIRST% %UP_PAGE% %LINK_PAGE% %DOWN_PAGE%  %END%');
		$show       = $Page->show();
		$noticelist=M('village_notice')->where($map)->order('addtime desc')->limit($Page->firstRow.','.$Page->listRows)->select();
		$this->assign('noticelist',$noticelist);
		$this->assign('page',$show);
		$this->assign("xiaoxitongzhi",'active');
		$this->assign("villagenoticelistac",'active');
		$this->display();
	}
	
	//发布小区通知
	public function villagenoticeadd(){
		$this->villageadminpower();
		if(I('ntitle')&&I('nbody')){
			$admin=session('admin');
			$data['vid']=$admin['vid'];
			$data['type']='0';
			$data['adminID']='1';
			$data['title']=I('ntitle');
			$data['content']=I('nbody');
			$data['addtime']=get_time();
			if(M('village_notice')->data($data)->add()){
				$this->success('发布成功',U('villagenoticelist'));
				exit;
			}
			$this->error('发布失败',U('villagenoticelist'));
			exit;
		}
		else{
			$this->error('请填写完整信息');
			exit;
		}
	}
	
	//删除公告
	public function villagenoticeremove(){
		$this->villageadminpower();
		if(I('nid')){
			$map['id']=I('nid');
			if(M('village_notice')->where($map)->delete()){
				$this->success('删除成功',U('villagenoticelist'));
				exit;
			}
			$this->error('删除失败',U('villagenoticelist'));
			exit;
		}
		$this->error('参数错误',U('villagenoticelist'));
		exit;
	}
	
	//物业后台平台公告列表
	public function adnoticelist(){
		$this->villageadminpower();
		if(I('noticekey')){
			$map['ntitle']=array('like','%'.I('noticekey').'%');
			$this->assign('noticekey',I('noticekey'));
		}
		$count      = M('village_adminnotice')->where($map)->count();
		$Page       = new \Think\Page($count,25);
		$Page -> setConfig('header','<i>共%TOTAL_ROW%条 %NOW_PAGE%/%TOTAL_PAGE%</i>');
		$Page -> setConfig('first','首页');
		$Page -> setConfig('last','末页');
		$Page -> setConfig('prev','上一页');
		$Page -> setConfig('next','下一页');
		$Page -> setConfig('theme','%HEADER% %FIRST% %UP_PAGE% %LINK_PAGE% %DOWN_PAGE%  %END%');
		$show       = $Page->show();
		$noticelist=M('village_adminnotice')->where($map)->order('addtime desc')->limit($Page->firstRow.','.$Page->listRows)->select();
		$this->assign('noticelist',$noticelist);
		$this->assign('page',$show);
		$this->assign("xiaoxitongzhi",'active');
		$this->assign("adnoticelistac",'active');
		$this->display();
	}
	
	//物业后台平台公告详情
	public function adnoticedetail(){
		$this->villageadminpower();
		if(I('nid')){
			$map['id']=I('nid');
			$adnotice=M('village_adminnotice')->where($map)->find();
			$this->assign('adnotice',$adnotice);
			$this->display();
			exit;
		}
		$this->error('参数错误',U('adnoticelist'));
		exit;
	}

	//办事指南
	public function guidelist(){
		$this->villageadminpower();
		$admin=session('admin');
		$map['vid']=$admin['vid'];
		if(I('keyword')){
			$map['title']=array('like','%'.I('keyword').'%');
			$this->assign('keyword',I('keyword'));
		}
		$count      = M('village_guide')->where($map)->count();
		$Page       = new \Think\Page($count,25);
		$Page -> setConfig('header','<i>共%TOTAL_ROW%条 %NOW_PAGE%/%TOTAL_PAGE%</i>');
		$Page -> setConfig('first','首页');
		$Page -> setConfig('last','末页');
		$Page -> setConfig('prev','上一页');
		$Page -> setConfig('next','下一页');
		$Page -> setConfig('theme','%HEADER% %FIRST% %UP_PAGE% %LINK_PAGE% %DOWN_PAGE%  %END%');
		$show       = $Page->show();
		$guidelist=M('village_guide')->where($map)->order('addtime desc')->limit($Page->firstRow.','.$Page->listRows)->select();
		$this->assign('guidelist',$guidelist);
		$this->assign('page',$show);
		$this->assign("xiaoxitongzhi",'active');
		$this->assign("guidelistac",'active');
		$this->display();
	}
	
	//发布指南
	public function guideadd(){
		$this->villageadminpower();
		if(I('title')&&I('body')){
			$admin=session('admin');
			$data['vid']=$admin['vid'];
			$data['type']='0';
			$data['adminID']='1';
			$data['title']=I('title');
			$data['content']=I('body');
			$data['addtime']=get_time();
			if(M('village_guide')->data($data)->add()){
				$this->success('发布成功',U('guidelist'));
				exit;
			}
			$this->error('发布失败',U('guidelist'));
			exit;
		}
		else{
			$this->error('请填写完整信息');
			exit;
		}
	}

	//指南详情
	public function guidedetail(){
		$this->villageadminpower();
		if(I('gid')){
			$map['id']=I('gid');
			$guide=M('village_guide')->where($map)->find();
			$this->assign('guide',$guide);
			$this->assign("xiaoxitongzhi",'active');
			$this->assign("guidelistac",'active');
			$this->display();
			exit;
		}
		$this->error('参数错误',U('guidelist'));
		exit;
	}	
	
	//删除指南
	public function guideremove(){
		$this->villageadminpower();
		if(I('gid')){
			$map['id']=I('gid');
			if(M('village_guide')->where($map)->delete()){
				$this->success('删除成功',U('guidelist'));
				exit;
			}
			$this->error('删除失败',U('guidelist'));
			exit;
		}
		$this->error('参数错误',U('guidelist'));
		exit;
	}
	
	//报修列表
	public function repairlist(){
		$this->villageadminpower();
		if(I('state')){
			switch(I('state')){
				case 0:;break;
				case 1:$map['state']="0";break;
				case 2:$map['state']='1';break;
			}
			$this->assign('state',I('state'));
		}
		$admin=session('admin');
		$map['vid']=$admin['vid'];
		$count      = M('village_repair')->where($map)->count();
		$Page       = new \Think\Page($count,25);
		$Page -> setConfig('header','<i>共%TOTAL_ROW%条 %NOW_PAGE%/%TOTAL_PAGE%</i>');
		$Page -> setConfig('first','首页');
		$Page -> setConfig('last','末页');
		$Page -> setConfig('prev','上一页');
		$Page -> setConfig('next','下一页');
		$Page -> setConfig('theme','%HEADER% %FIRST% %UP_PAGE% %LINK_PAGE% %DOWN_PAGE%  %END%');
		$show       = $Page->show();
		$repairlist=M('village_repair')->where($map)->order('time desc')->limit($Page->firstRow.','.$Page->listRows)->select();
		$dataarr=array();
		foreach($repairlist as $key=>$value){
			$dataarr[$key]['repairinfo']=$value;
			$mapuser['id']=$value['userid'];
			$userinfo=M('village_user')->where($mapuser)->find();
			$dataarr[$key]['userinfo']=$userinfo;
		}
		$this->assign('page',$show);
		$this->assign('repairlist',$dataarr);
		$this->assign("repairlistac",'active');
		$this->display();
	}

    //报修详情
	public function repairdetail(){
		$this->villageadminpower();
		if(I('rid')){
			$map['id']=I('rid');
			$repair=M('village_repair')->where($map)->find();
			$mapuser['id']=$repair['userid'];
			$this->assign('repair',$repair);
			$this->assign('userinfo',M('village_user')->where($mapuser)->find());
			$this->assign("repairlistac",'active');
			$this->display();
			exit;
		}
		$this->error('参数错误',U('repairlist'));
		exit;	
	}
	
	
	//处理报修
	public function repairstate(){
		$this->villageadminpower();
		if(I('rid')){
			$data['state']='1';
			$map['id']=I('rid');
			if(M('village_repair')->where($map)->save($data)){
				$this->success('处理成功！',U('repairlist'));
				exit;
			}
			$this->error('处理失败',U('repairlist'));
			exit;
		}
		$this->error('参数错误',U('repairlist'));
		exit;
	}
	
	//投诉列表
	public function complaintlist(){
		$this->villageadminpower();
		if(I('state')){
			switch(I('state')){
				case 0:;break;
				case 1:$map['state']="0";break;
				case 2:$map['state']='1';break;
			}
			$this->assign('state',I('state'));
		}
		$admin=session('admin');
		$map['vid']=$admin['vid'];
		$count      = M('village_complaint')->where($map)->count();
		$Page       = new \Think\Page($count,25);
		$Page -> setConfig('header','<i>共%TOTAL_ROW%条 %NOW_PAGE%/%TOTAL_PAGE%</i>');
		$Page -> setConfig('first','首页');
		$Page -> setConfig('last','末页');
		$Page -> setConfig('prev','上一页');
		$Page -> setConfig('next','下一页');
		$Page -> setConfig('theme','%HEADER% %FIRST% %UP_PAGE% %LINK_PAGE% %DOWN_PAGE%  %END%');
		$show       = $Page->show();
		$complaintlist=M('village_complaint')->where($map)->order('adddate desc')->limit($Page->firstRow.','.$Page->listRows)->select();
		$dataarr=array();
		foreach($complaintlist as $key=>$value){
			$dataarr[$key]['complaintinfo']=$value;
			$mapuser['id']=$value['uid'];
			$userinfo=M('village_user')->where($mapuser)->find();
			$dataarr[$key]['userinfo']=$userinfo;
		}
		$this->assign('page',$show);
		$this->assign('complaintlist',$dataarr);
		$this->assign("complaintlistac",'active');
		$this->display();
	}
	
	//投诉详情
	public function complaintdetail(){
		$this->villageadminpower();
		if(I('cid')){
// 			$data['state']='1';
			$map['id']=I('cid');
// 			M('village_complaint')->where($map)->save($data);
			$conplaint=M('village_complaint')->where($map)->find();
			$mapuser['id']=$conplaint['uid'];
			$this->assign('complaint',$conplaint);
			$this->assign('userinfo',M('village_user')->where($mapuser)->find());
			$this->assign("complaintlistac",'active');
			$this->display();
			exit;
			
		}
		$this->error('参数错误',U('complaintlist'));
		exit;
	}
	
	public function addComplaintNews(){
	    if(I("uid") && I("reply") && I("source") && I('cid')){
	        $data["uid"]=I("uid");
	        $data["reply"]=I("reply");
	        $data["source"]=I("source");
	        $data["title"]="投诉回复";
	        $data["time"]=date('y-m-d h:i:s',time());
	        if(M("village_news")->data($data)->add()){
	            $map['id']=I('cid');
	            M('village_complaint')->where($map)->setField('state',1);
	            $this->success("回复成功",U('complaintlist'));
	        }else{
	            $this->error("回复失败");
	        }
	    }else{
	        $this->error("参数错误");
	    }
	    
	}
	
	
	
	
	//判断是否登录并刷新登录过期时间
	public function isLogon(){
		if(session("aid") && cookie("loginExpire")){
			cookie("loginExpire",1,1800);
			return true;
		}else{
			return false;
		}
	}
	
	//登录时间更新
	public function loginUpdate($aid){
		$data["lastlogindate"]=get_time();
		$data["loginIP"]=get_client_ip();
		M("village_admin")->where("id=".$aid)->setField($data);
	}
	
	//获取等级所对应的权限
	public function grade_to_permission(){
		$admin=session('admin');
		if($admin['grade']==0){
			return FALSE;
		}else{
			return TRUE;		
		}
	}
	
	//判断是否有物业权限
	public function villageadminpower(){
		if($this->grade_to_permission()){
			$this->error('您没有权限访问该页面');
			exit;
		}
	}
	
	//判断是否有平台权限
	public function admimnpower(){
		if($this->grade_to_permission()==FALSE){
			$this->error('您没有权限访问该页面');
			exit;
		}
	}
	
	/*
	 * 企业付款
	 * @param string $openId 用户openid
	 * @param string $Amount 转账金额(单位 元)
	 */
	public function wxtransfers($openId,$Amount){
		$this->villageadminpower();
		vendor('WXpay.lib.Exception');
        vendor('WXpay.lib.Api');
        vendor('WXpay.lib.JsApiPay');
        vendor('WXpay.lib.Notify');
        vendor('WXpay.lib.Submit');
		
		//②、企业付款
		$input = new \WxPayTransfers();
		$input->SetPartner_trade_no(date("YmdHis").rand(100,999));//商户订单号
		$input->SetOpenid($openId);//用户$openId标识
		$input->SetCheck_name("NO_CHECK");//不校验用户姓名
		$input->SetAmount($Amount*100);//金额
		$input->SetDesc("小区物业费提现");//企业付款描述信息
		$wxpayapi=new \WxPayApi();
		$transfers = $wxpayapi->transfers($input);
		
		if($transfers["return_code"]=="SUCCESS"){
			if($transfers["result_code"]=="SUCCESS"){
				return "SUCCESS";
			}else{
				return $transfers["err_code_des"];
			}
		}else{
			return $transfers["return_msg"];
		}
	}	
	//房间列表
	public function roomlist(){
	    $admin=session('admin');
	    $map['vid']=$admin['vid'];
		if(I("searchuser")){
			$this->assign('searchuser',I("searchuser"));
			$map['name']  = array('like', '%'.I("searchuser").'%');
		}
	    $User = M('village_roominfo'); // 实例化User对象
	    $count      = $User->where($map)->count();// 查询满足要求的总记录数
	    $Page       = new \Think\Page($count,25);// 实例化分页类 传入总记录数和每页显示的记录数(25)
	    $show       = $Page->show();// 分页显示输出
	    // 进行分页数据查询 注意limit方法的参数要使用Page类的属性
	    $list = $User->where($map)->order('building,unit,room')->limit($Page->firstRow.','.$Page->listRows)->select();
	    $this->assign('list',$list);// 赋值数据集
	    $this->assign('page',$show);// 赋值分页输出
	    $this->display(); // 输出模板
	}
	
	//删除房间信息
	public function removeRoom(){
	    $admin=session('admin');
        $map['vid']=$admin['vid'];
        $map['id']=I('roomid');
        if(M('village_roominfo')->where($map)->delete())
        {
            $this->success("删除成功!",U("roomlist"));
            exit;
        }
        else{
            $this->error("删除失败!");
            exit;
        }
	
	}
	
	public function addRoom() {
	    $admin=session('admin');
	    if(!$admin["vid"]){
	        $this->error("添加失败!");
	        exit;
	    }
	    if(I("building") && I("room")){
	        $data=I("post.");
	        if($data["id"]){
	            $map["id"]=$data["id"];
	            $data["vid"]=$admin["vid"];
	            if(M('village_roominfo')->where($map)->count()){
	                if(M('village_roominfo')->save($data)){
	                    $this->success("修改成功!",U("roomlist"));
	                    exit;
	                }else{
	                    $this->error("修改失败!");
	                    exit;
	                }
	            }else{
	                $this->error("提交参数错误!");
	                exit;
	            }
	        }
	        $map["building"]=I("building");
	        $map["room"]=I("room");
	        $map["unit"]=I("unit");
	        $data["vid"]=$admin["vid"];
	        if(M('village_roominfo')->where($map)->count()){
	            $this->error("房间已存在，请勿重复添加!");
	            exit;
	        }
	        if(M('village_roominfo')->data($data)->add()){
	            $this->success("添加成功!",U("roomlist"));
	            exit;
	        }else{
	            $this->error("添加失败!");
	            exit;
	        }
	    }else{
// 	        $data=I("type");
// 	        print_r(I("type"));
	        $this->error("提交参数错误!");
	        exit;
	    }
	}

	//excel导入房间信息
	public function importroom(){
	    $admin=session('admin');
	    if(!$admin["vid"]){
	        $this->error("导入失败!");
	        exit;
	    }
		if(empty ($_FILES ['excelfile'] ['name'])){
	        $this->error("文件上传失败!");
	        exit;			
		}
		$tmp_f = $_FILES ['excelfile'] ['tmp_name'];
		$f_types = explode ( ".", $_FILES ['excelfile'] ['name'] );
		$f_type = $f_types [count ( $f_types ) - 1];
		if (strtolower($f_type)!= "xls" && strtolower($f_type)!= "xlsx"){
	        $this->error("请上传excel文件!");
	        exit;			
		}
		vendor('PHPExcel.IOFactory');
		$objPHPExcel = \PHPExcel_IOFactory::load($tmp_f);
		$objPHPExcel->setActiveSheetIndex(0);
		$sheet0=$objPHPExcel->getSheet(0);
		$rowCount=$sheet0->getHighestRow();//excel行数
        if(($objPHPExcel->getActiveSheet()->getCell("A1")->getValue()=="楼栋") && ($objPHPExcel->getActiveSheet()->getCell("B1")->getValue()=="单元") && ($objPHPExcel->getActiveSheet()->getCell("C1")->getValue()=="室") && ($objPHPExcel->getActiveSheet()->getCell("D1")->getValue()=="人数") && ($objPHPExcel->getActiveSheet()->getCell("E1")->getValue()=="面积") && ($objPHPExcel->getActiveSheet()->getCell("F1")->getValue()=="")){
			for ($i = 2; $i <= $rowCount; $i++){
				$map['vid']=$admin["vid"];
				$map['building']=$objPHPExcel->getActiveSheet()->getCell("A".$i)->getValue();
				$map['unit']=$objPHPExcel->getActiveSheet()->getCell("B".$i)->getValue();
				$map['room']=$objPHPExcel->getActiveSheet()->getCell("C".$i)->getValue();
				if(M('village_roominfo')->where($map)->count()){
					continue;
				}
				$map['population']=$objPHPExcel->getActiveSheet()->getCell("D".$i)->getValue();
				$map['area']=$objPHPExcel->getActiveSheet()->getCell("E".$i)->getValue();
				if(M('village_roominfo')->data($map)->add()){
					continue;
				}else{
					$this->error("导入意外终止，请检查数据是否错误！");
					exit;
				}
			}
			$this->success("导入完成!",U("roomlist"));
			exit;
		}
        else{
	        $this->error("格式错误，请下载模板编辑!");
	        exit;	
		}




    
	}

	//门禁列表
	public function locklist(){
		$admin=session('admin');
		if($admin['grade']==0){
			$map['vid']=$admin['vid'];
		}
		if(I("searchdoor")){
			$this->assign('searchdoor',I("searchdoor"));
			$map['door']  = array('like', '%'.I("searchdoor").'%');
		}
	    $User = M('village_lock'); // 实例化User对象
	    $count      = $User->where($map)->count();// 查询满足要求的总记录数
	    $Page       = new \Think\Page($count,25);// 实例化分页类 传入总记录数和每页显示的记录数(25)
	    $show       = $Page->show();// 分页显示输出
	    // 进行分页数据查询 注意limit方法的参数要使用Page类的属性
	    $list = $User->where($map)->order('vid,id')->limit($Page->firstRow.','.$Page->listRows)->select();
		$listarray=array();
		foreach($list as $key=>$value){
			$vmap['id']=$value['vid'];
			$villageinfo=M('village_info')->where($vmap)->find();
			$value['vname']=$villageinfo['name'];
			if($value['vname'] == null){
				$value['online']=0;		
			}else{
				if(((strtotime(get_time())-strtotime($value['lstime']))/1) <= 35){//除以60为分钟
					$value['online']=1;
				}else{
					$value['online']=0;
				}
			}
			$listarray[$key]=$value;
		}
	    $this->assign('list',$listarray);// 赋值数据集
		$this->assign('vlist',M('village_info')->select());
	    $this->assign('page',$show);// 赋值分页输出
	    $this->display(); // 输出模板
	}
	//检测门禁
	public function checklock(){
		$port = 8888;
		$ip = "120.76.233.4";
		$socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
		$result = socket_connect($socket, $ip, $port);


		$in = "c:" .I('lockid');
		print_r($in);
		if(!socket_write($socket, $in, strlen($in))) {
			$this->error("检测失败!");
			socket_close($socket);
			exit;
		}else {
			$this->success("检测成功，以门禁最后连接时间为准！!",U("locklist"));
			socket_close($socket);
			exit;
		}
	
	}	
	//删除门禁信息
	public function removelock(){
        $map['id']=I('lockid');
        if(M('village_lock')->where($map)->delete())
        {
            $this->success("删除成功!",U("locklist"));
            exit;
        }
        else{
            $this->error("删除失败!");
            exit;
        }
	
	}
	//添加门禁
	public function addlock() {
	    if(I("vid") && I("door") && I("actime")){
	        $data=I("post.");
	        if($data["id"]){
	            $map["id"]=$data["id"];
	            if(M('village_lock')->where($map)->count()){
	                if(M('village_lock')->where($map)->save($data)){
	                    $this->success("修改成功!",U("locklist"));
	                    exit;
	                }else{
	                    $this->error("修改失败!");
	                    exit;
	                }
	            }else{
	                $this->error("提交参数错误!");
	                exit;
	            }
	        }
	        $map["vid"]=I("vid");
	        $map["door"]=I("door");
	        if(M('village_lock')->where($map)->count()){
	            $this->error("门禁已存在，请勿重复添加!");
	            exit;
	        }
	        if(M('village_lock')->data($data)->add()){
	            $this->success("添加成功!",U("locklist"));
	            exit;
	        }else{
	            $this->error("添加失败!");
	            exit;
	        }
	    }else{
	        $this->error("提交参数错误!");
	        exit;
	    }
	}

	
	//用户门禁权限更改
	public function userlockupdate() {
	    if(I("id") && I("lstime") && I("letime")){
			$map["id"]=I("id");
			$lockid = "";
			foreach(I('lockid') as $key=>$value)
			{
				$lockid.=",".$value.",";
			}
			$data=I("post.");
			//$data["id"]=I("id");
			//$data["lstime"]=I("lstime");
			//$data["letime"]=I("letime");
			$data["lockid"]=$lockid;
			if(M('village_user')->where($map)->count()){
				if(M('village_user')->where($map)->save($data)){
					$this->success("修改成功!",U('userDetail',array('userid'=>I('id'))));
					exit;
				}else{
					$this->error("修改失败!");
					exit;
				}
			}else{
				$this->error("提交参数错误!");
				exit;
			}
	        
	    }else{
	        $this->error("提交参数错误!");
	        exit;
	    }
	}
	
	//修改密码
	public function adminpwd() {
		$admin=session('admin');
		$this->assign('phone',$admin);
		
		$this->display(); 
	}
	public function pwdchange(){
	    if(I("id") && I("phone") && I("opwd") && I("npwd") && I("npwd2")){
			if(I("npwd") == I("npwd2")){
				$map["id"]=I("id");
				$admin=M('village_admin')->where($map)->find();
				if(md5(I("opwd")) == $admin["password"]){
					$data["password"]=md5(I("npwd"));
					if(M('village_admin')->where($map)->save($data)){
						$this->success("修改成功!下次登录生效！",U('adminpwd'));
						exit;
					}else{
						$this->error("修改失败!");
						exit;
					}
				}else{
					$this->error("原密码错误，请重试！");
					exit;
				}
			}else{
				$this->error("两次输入密码不一样，请重新确认！");
				exit;				
			}     
	    }else{
	        $this->error("提交参数错误!");
	        exit;
	    }		
	}
}


