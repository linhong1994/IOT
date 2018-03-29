<?php

namespace Addons\Village\Controller;
use Home\Controller\AddonsController;

class IndexsController extends AddonsController{
    public function index(){
		$admap['adposition']='1';
		$adlist=M('village_advertisement')->where($admap)->order('addtime desc')->select();
		$nmap['vid']=1;
		$nmap['type']='0';
		$villagenotice=M('village_notice')->where($nmap)->limit(5)->select();
		$this->assign('villagenoticelist',$villagenotice);
		$this->assign('adlist',$adlist);
		$this->display();
	}
	
	public  function qr() {
	    $this->display();
	}
}
