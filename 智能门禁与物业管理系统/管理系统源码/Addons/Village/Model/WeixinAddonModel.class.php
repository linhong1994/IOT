<?php
        	
namespace Addons\Village\Model;
use Home\Model\WeixinModel;
        	
/**
 * Village的微信模型
 */
class WeixinAddonModel extends WeixinModel{
	function reply($dataArr, $keywordArr = array()) {
		$config = getAddonConfig ( 'Village' ); // 获取后台插件的配置参数	
		//dump($config);
	}
}
        	