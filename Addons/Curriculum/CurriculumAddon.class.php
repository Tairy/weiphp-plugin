<?php

namespace Addons\Curriculum;
use Common\Controller\Addon;

/**
 * 课表查询插件
 * @author Tairy
 */

    class CurriculumAddon extends Addon{

        public $info = array(
            'name'=>'Curriculum',
            'title'=>'课表查询',
            'description'=>'提供校园课表查询的功能',
            'status'=>1,
            'author'=>'Tairy',
            'version'=>'0.1',
            'has_adminlist'=>0,
            'type'=>1
        );

	public function install() {
		$install_sql = './Addons/Curriculum/install.sql';
		if (file_exists ( $install_sql )) {
			execute_sql_file ( $install_sql );
		}
		return true;
	}
	public function uninstall() {
		$uninstall_sql = './Addons/Curriculum/uninstall.sql';
		if (file_exists ( $uninstall_sql )) {
			execute_sql_file ( $uninstall_sql );
		}
		return true;
	}

        //实现的weixin钩子方法
        public function weixin($param){

        }

    }
