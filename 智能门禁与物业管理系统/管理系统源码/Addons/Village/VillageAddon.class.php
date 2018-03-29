<?php

namespace Addons\Village;
use Common\Controller\Addon;

/**
 * 微小区插件
 * @author 物业管理系统
 */

    class VillageAddon extends Addon{

        public $info = array(
            'name'=>'Village',
            'title'=>'微小区',
            'description'=>'微小区',
            'status'=>1,
            'author'=>'物业管理系统',
            'version'=>'0.1',
            'has_adminlist'=>0
        );

		public function install() {
			$install_sql = './Addons/Village/install.sql';
			if (file_exists ( $install_sql )) {
				execute_sql_file ( $install_sql );
			}
			return true;
		}
		public function uninstall() {
			$uninstall_sql = './Addons/Village/uninstall.sql';
			if (file_exists ( $uninstall_sql )) {
				execute_sql_file ( $uninstall_sql );
			}
			return true;
		}

        //实现的weixin钩子方法
        public function weixin($param){

        }

    }