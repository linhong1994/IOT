<?php
// +----------------------------------------------------------------------
// | OneThink [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013 http://www.onethink.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: 麦当苗儿 <zuojiazi@vip.qq.com> <http://www.zjzit.cn>
// +----------------------------------------------------------------------

/**
 * 系统配文件
 * 所有系统级别的配置
 */
return array(

		//天下畅通短信配置
		"SMS_CONFIG" => array(
				"userid"   => '',//企业ID
				"account"  => '',//账号
				"passwork" => '',//密码
				"signed"   => '【福建汇业信息技术】',//签名   必须在【】内
		),
		
		
		// 数据库配置
        'DB_TYPE'   => 'mysql', // 数据库类型
        'DB_HOST'   => '127.0.0.1', // 服务器地址
        'DB_NAME'   => 'xiaoqu', // 数据库名
        'DB_USER'   => 'root', // 用户名
        'DB_PWD'    => '',  // 密码
        'DB_PORT'   => '3306', // 端口
        'DB_PREFIX' => 'wp_', // 数据库表前缀
		'DB_PARAMS' => array (
				\PDO::ATTR_CASE => \PDO::CASE_NATURAL 
		),
		
		// 系统数据加密设置
		'DATA_AUTH_KEY' => 'S}#VK7Iaryk)/M`_&94=OEPWld"vj6{H;b<:80NT', // 默认数据加密KEY
		                                                               
		// 调试配置
		'SHOW_PAGE_TRACE' => false,
		
		// 用户相关设置数
		'USER_ADMINISTRATOR' => 1, // 管理员用户ID
		                           
		// URL配置
		'URL_CASE_INSENSITIVE' => false, // 默认false 表示URL区分大小写 true则表示不区分大小写
		'URL_MODEL' => 3, // URL模式
		'DIV_DOMAIN' => false, // 泛域名支持,注：在localhost 或者IP地址下访问下无效
		                      
		// 全局过滤配置
		'DEFAULT_FILTER' => 'safe', // 全局过滤函数
		                            
		// 数据缓存设置
		'DATA_CACHE_PREFIX' => SITE_DIR_NAME . '_', // 缓存前缀
		'DATA_CACHE_TYPE' => 'File', // 数据缓存类型
		'MEMCACHE_HOST' => '127.0.0.1',
		'MEMCACHE_PORT' => 11211,
		'DATA_CACHE_TIMEOUT' => 86400,
		
		'PICTURE_UPLOAD_DRIVER' => 'Local',
		
		// 本地上传文件驱动配置
		'UPLOAD_LOCAL_CONFIG' => array (),
		
		// 七牛上传文件驱动配置
		'UPLOAD_QINIU_CONFIG' => array (
				'accessKey' => '',
				'secrectKey' => '',
				'bucket' => '',
				'domain' => '',
				'timeout' => 3600 
		),
		
		// 百度云上传文件驱动配置
		'UPLOAD_BCS_CONFIG' => array (
				'AccessKey' => '',
				'SecretKey' => '',
				'bucket' => '',
				'rename' => false 
		),
		
		// 图片上传相关配置
		'PICTURE_UPLOAD' => array (
				'maxSize' => 2097152, // 2M 上传的文件大小限制 (0-不做限制)
				'exts' => 'jpg,gif,png,jpeg', // 允许上传的文件后缀
				'rootPath' => './Uploads/Picture/' 
		),
		
		// 编辑器图片上传相关配置
		'EDITOR_UPLOAD' => array (
				'maxSize' => 2097152, // 2M 上传的文件大小限制 (0-不做限制)
				'exts' => 'jpg,gif,png,jpeg', // 允许上传的文件后缀
				'rootPath' => './Uploads/Editor/' 
		),
		
		// 文件上传相关配置
		'DOWNLOAD_UPLOAD' => array (
				'maxSize' => 5242880, // 5M 上传的文件大小限制 (0-不做限制)
				'exts' => 'jpg,gif,png,jpeg,zip,rar,tar,gz,7z,doc,docx,txt,xml,xls,xlsx,csv,pem,amr', // 允许上传的文件后缀
				'rootPath' => './Uploads/Download/' 
		),
		
		//微信支付参数
		'WXPAY_DATA' => array(
				//=======【基本信息设置】=====================================
				//
				/**
				 * 
				 * 微信公众号信息配置
				 * APPID：绑定支付的APPID（必须配置）
				 * MCHID：商户号（必须配置）
				 * KEY：商户支付密钥，参考开户邮件设置（必须配置）
				 * APPSECRET：公众帐号secert（仅JSAPI支付的时候需要配置）
				 * @var string
				 */
				'wx_appid' => '',
				'wx_mchid' => '',
				'wx_key' => '',
				'wx_appsecret' => '',
				
				//=======【证书路径设置】=====================================
				/**
				 * 
				 * 证书路径,注意应该填写绝对路径（仅退款、撤销订单时需要）
				 * @var path
				 */
				'wx_sslcert_path' => '',
				'wx_sslkey_path' => '',
		)
		
		
);