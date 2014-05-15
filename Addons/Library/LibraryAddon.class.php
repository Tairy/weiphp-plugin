<?php

namespace Addons\Library;
use Common\Controller\Addon;

/**
 * 图书馆插件
 * @author Tairy
 */

    class LibraryAddon extends Addon{

        public $info = array(
            'name'=>'Library',
            'title'=>'图书馆',
            'description'=>'东南大学图书馆借书信息查询',
            'status'=>1,
            'author'=>'Tairy',
            'version'=>'0.1',
            'has_adminlist'=>0,
            'type'=>1         
        );

	public function install() {
		$install_sql = './Addons/Library/install.sql';
		if (file_exists ( $install_sql )) {
			execute_sql_file ( $install_sql );
		}
		return true;
	}
	public function uninstall() {
		$uninstall_sql = './Addons/Library/uninstall.sql';
		if (file_exists ( $uninstall_sql )) {
			execute_sql_file ( $uninstall_sql );
		}
		return true;
	}

        //实现的weixin钩子方法
        public function weixin($param){

        }

    }