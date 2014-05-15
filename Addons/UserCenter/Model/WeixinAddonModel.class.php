<?php

namespace Addons\UserCenter\Model;

use Home\Model\WeixinModel;

/**
 * UserCenter的微信模型
 */
class WeixinAddonModel extends WeixinModel {
	var $config = array ();
	function reply($dataArr, $keywordArr = array()) {
		$this->config = getAddonConfig ( 'UserCenter' ); // 获取后台插件的配置参数
		
		$reply [0] = array (
				'Title' => 'WeiPHP首页',
				'Description' => '欢迎光临WeiPHP的3G首页，请点击进入',
				'PicUrl' => SITE_URL . '/Public/Home/images/desc_pic.jpg',
				'Url' => addons_url ( 'UserCenter://UserCenter/index' ) . '&openid=' . $dataArr ['FromUserName'] . '&token=' . get_token () 
		);
		$this->replyNews ( $reply );
	}
	// 关注时的操作
	function subscribe($dataArr) {
		D ( 'Home/Member' )->initWeixinUser($dataArr ['FromUserName']);
	}
}
        	