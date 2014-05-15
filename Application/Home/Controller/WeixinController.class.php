<?php

namespace Home\Controller;

/**
 * 微信交互控制器
 * 主要获取和反馈微信平台的数据
 */
class WeixinController extends HomeController {
	var $token;
	public function index() {
		$this->token = get_token ();
		$weixin = D ( 'Weixin' );
		// 获取数据
		$data = $weixin->getData ();
		
		if (! empty ( $data ['FromUserName'] )) {
			session ( 'openid', $data ['FromUserName'] );
		}
		// 记录日志
		addWeixinLog ( $data, $GLOBALS ['HTTP_RAW_POST_DATA'] );
		
		// 回复数据
		$this->reply ( $data, $weixin );
		
		// 结束程序。防止oneThink框架的调试信息输出
		exit ();
	}
	private function reply($data, $weixin) {
		$key = $data ['Content'];
		$keywordArr = array ();
		
		// 插件权限控制
		$token_status = D ( 'Common/AddonStatus' )->getList ();
		foreach ( $token_status as $a => $s ) {
			$s == 1 || $forbit_addon [$a] = $a;
		}
		
		// 所有安装过的微信插件
		$addon_list = ( array ) D ( 'Addons' )->getWeixinList ( false, $token_status );
		
		/**
		 * 通过微信事件来定位处理的插件
		 * event可能的值：
		 * subscribe : 关注公众号
		 * unsubscribe : 取消关注公众号
		 * scan : 扫描带参数二维码事件
		 * location : 上报地理位置事件
		 * click : 自定义菜单事件
		 */
		if ($data ['MsgType'] == 'event') {
			$event = strtolower ( $data ['Event'] );
			foreach ( $addon_list as $vo ) {
				require_once ONETHINK_ADDON_PATH . $vo ['name'] . '/Model/WeixinAddonModel.class.php';
				$model = D ( 'Addons://' . $vo ['name'] . '/WeixinAddon' );
				! method_exists ( $model, $event ) || $model->$event ( $data );
			}
			if ($event == 'click' && ! empty ( $data ['EventKey'] )) {
				$key = $data ['EventKey'];
			} else {
				return true;
			}
		}
		
		// 通过获取上次缓存的用户状态来定位处理的插件
		$uid = intval ( $this->mid );
		$user_status = S ( 'user_status_' . $uid );
		if (! isset ( $addons [$key] ) && $user_status) {
			$addons [$key] = $user_status ['addon'];
			$keywordArr = $user_status ['keywordArr'];
			S ( 'user_status_' . $uid, null );
		}
		
		// 通过插件标识名和插件名来定位处理的插件
		if (! isset ( $addons [$key] )) {
			foreach ( $addon_list as $k => $vo ) {
				$addons [$vo ['name']] = $k;
				$addons [$vo ['title']] = $k;
			}
		}
		
		// 通过精准关键词来定位处理的插件 token=0是插件安装时初始化的模糊关键词，所有公众号都可以用
		if (! empty ( $forbit_addon )) {
			$like ['addon'] = array (
					'not in',
					$forbit_addon 
			);
		}
		$like ['token'] = array (
				'exp',
				"=0 or token='{$this->token}'" 
		);
		if (! isset ( $addons [$key] )) {
			$like ['keyword'] = $key;
			$like ['keyword_type'] = 0;
			$keywordArr = M ( 'keyword' )->where ( $like )->order ( 'id desc' )->find ();
			$addons [$key] = $keywordArr ['addon'];
		}
		// 通过模糊关键词来定位处理的插件
		if (! isset ( $addons [$key] )) {
			unset ( $like ['keyword'] );
			$like ['keyword_type'] = array (
					'gt',
					0 
			);
			$list = M ( 'keyword' )->where ( $like )->order ( 'keyword_lenght desc, id desc' )->select ();
			
			foreach ( $list as $keywordInfo ) {
				$this->_contain_keyword ( $keywordInfo, $key, $addons, $keywordArr );
			}
		}
		
		// 以上都无法定位插件时，如果开启了智能聊天，则默认使用智能聊天插件
		if (! isset ( $addons [$key] ) && isset ( $addon_list ['Chat'] )) {
			$addons [$key] = 'Chat';
		}
		
		// 最终也无法定位到插件，终止操作
		if (! isset ( $addons [$key] ) || ! file_exists ( ONETHINK_ADDON_PATH . $addons [$key] . '/Model/WeixinAddonModel.class.php' )) {
			return false;
		}
		
		// 加载相应的插件来处理并反馈信息
		require_once ONETHINK_ADDON_PATH . $addons [$key] . '/Model/WeixinAddonModel.class.php';
		$model = D ( 'Addons://' . $addons [$key] . '/WeixinAddon' );
		$model->reply ( $data, $keywordArr );
	}
	
	// 处理关键词包含的算法
	private function _contain_keyword($keywordInfo, $key, &$addons, &$keywordArr) {
		if (isset ( $addons [$key] ))
			return false;
		
		$arr = explode ( $keywordInfo ['keyword'], $key );
		if (count ( $arr ) > 1) {
			// 在关键词不相等的情况下进行左右匹配判断，否则相等的情况肯定都匹配
			if ($keywordInfo ['keyword'] != $key) {
				// 左边匹配
				if ($keywordInfo ['keyword_type'] == 1 && ! empty ( $arr [0] ))
					return false;
					
					// 右边 匹配
				if ($keywordInfo ['keyword_type'] == 2 && ! empty ( $arr [1] ))
					return false;
			}
			
			$addons [$key] = $keywordInfo ['addon'];
			
			$keywordArr = $keywordInfo;
			$keywordArr ['prefix'] = trim ( $arr [0] ); // 关键词前缀，即包含关键词的前面部分
			$keywordArr ['suffix'] = trim ( $arr [1] ); // 关键词后缀，即包含关键词的后面部分
		}
	}
}
?>