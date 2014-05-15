<?php

namespace Addons\MorningExercises;
use Common\Controller\Addon;

/**
 * 早操次数查询插件
 * @author Tairy
 */

    class MorningExercisesAddon extends Addon{

        public $info = array(
            'name'=>'MorningExercises',
            'title'=>'早操次数查询',
            'description'=>'东南大学早操次数查询',
            'status'=>1,
            'author'=>'Tairy',
            'version'=>'0.1',
            'has_adminlist'=>0,
            'type'=>1         
        );

	public function install() {
		$install_sql = './Addons/MorningExercises/install.sql';
		if (file_exists ( $install_sql )) {
			execute_sql_file ( $install_sql );
		}
		return true;
	}
	public function uninstall() {
		$uninstall_sql = './Addons/MorningExercises/uninstall.sql';
		if (file_exists ( $uninstall_sql )) {
			execute_sql_file ( $uninstall_sql );
		}
		return true;
	}

        //实现的weixin钩子方法
        public function weixin($param){

        }

    }