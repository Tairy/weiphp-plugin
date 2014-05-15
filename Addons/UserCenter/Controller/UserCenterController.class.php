<?php

namespace Addons\UserCenter\Controller;

use Home\Controller\AddonsController;
use User\Api\UserApi;

class UserCenterController extends AddonsController {
	
	/**
	 * 显示微信用户列表数据
	 */
	public function lists() {
		// 获取模型信息
		$model = $this->getModel ();
		
		$page = I ( 'p', 1, 'intval' );
		
		// 解析列表规则
		$fields = array ();
		$grids = preg_split ( '/[;\r\n]+/s', htmlspecialchars_decode ( $model ['list_grid'] ) );
		$grids = array (
				array (
						'field' => array (
								'uid' 
						),
						'title' => 'UID' 
				),
				array (
						'field' => array (
								'nickname' 
						),
						'title' => '昵称' 
				),
				array (
						'field' => array (
								'last_login_time' 
						),
						'title' => '最近登录时间' 
				),
				array (
						'field' => array (
								'id' 
						),
						'title' => '操作',
						'href' => '[DELETE]&uid=[uid]|删除' 
				) 
		);
		
		// 关键字搜索
		$map ['token'] = get_token ();
		$key = 'nickname';
		if (isset ( $_REQUEST [$key] )) {
			$map [$key] = array (
					'like',
					'%' . htmlspecialchars ( $_REQUEST [$key] ) . '%' 
			);
			unset ( $_REQUEST [$key] );
		}
		$row = 20;
		$data = M ( 'member' )->where ( $map )->order ( 'uid DESC' )->page ( $page, $row )->select ();
		foreach ( $data as &$vo ) {
			$vo ['last_login_time'] = time_format ( $vo ['last_login_time'] );
		}
		/* 查询记录总数 */
		$count = M ( 'member' )->where ( $map )->count ();
		
		// 分页
		if ($count > $row) {
			$page = new \Think\Page ( $count, $row );
			$page->setConfig ( 'theme', '%FIRST% %UP_PAGE% %LINK_PAGE% %DOWN_PAGE% %END% %HEADER%' );
			$this->assign ( '_page', $page->show () );
		}
		
		$this->assign ( 'list_grids', $grids );
		$this->assign ( 'list_data', $data );
		$this->meta_title = $model ['title'] . '列表';
		
		$this->assign ( 'add_button', false );
		$this->assign ( 'del_button', false );
		$this->assign ( 'check_all', false );
		
		$this->display ();
	}
	public function del() {
		$map ['uid'] = I ( 'uid', 0 );
		
		// 插件里的操作自动加上Token限制
		$token = get_token ();
		if (defined ( 'ADDON_PUBLIC_PATH' ) && ! empty ( $token )) {
			$map ['token'] = $token;
		}
		
		if (M ( 'member' )->where ( $map )->delete ()) {
			$this->success ( '删除成功' );
		} else {
			$this->error ( '删除失败！' );
		}
	}
	function index() {
		$this->checkUser ();
		$this->display ();
	}
	function checkUser($openid = '', $token = '') {
		! empty ( $openid ) || $openid = get_openid ();
		! empty ( $token ) || $token = get_token ();
		
		// 第一步:是否已注册
		$map ['openid'] = $openid;
		$map ['token'] = $token;
		$uid = M ( 'member' )->where ( $map )->getField ( 'uid' );
		if (! $uid) {
			// 是否能自动从微信接口获取用户信息
			$info = getWeixinUserInfo ( $openid, $token );
			if (! $info ['nickname']) {
				if (IS_POST) {
					$info = $_POST;
				} else {
					$field ['title'] = '昵称';
					$field ['name'] = 'nickname';
					$this->assign ( 'field', $field );
					$this->display ( 'add_userinfo' );
					exit ();
				}
			}
			$email = time () . '@weiphp.cn';
			/* 调用注册接口注册用户 */
			$User = new UserApi ();
			$uid = $User->register ( $info ['nickname'], '123456', $email );
			if ($uid > 0) {
				$info ['uid'] = $uid;
				$info ['status'] = 1;
				M ( 'Member' )->add ( $info );
			}
		}
		
		$member = get_memberinfo ( $uid );
		$config = getAddonConfig ( 'UserCenter' ); // 获取后台插件的配置参数
		                                           
		// 第二步:是否需要填写真实姓名
		if ($config ['need_truename'] && empty ( $member ['truename'] )) {
			if (IS_POST) {
				! empty ( $_POST ['truename'] ) || $this->error ( '真实姓名不能为空' );
				M ( 'member' )->where ( 'uid=' . $uid )->setField ( 'truename', I ( 'post.truename' ) );
			} else {
				$field ['title'] = '真实姓名';
				$field ['name'] = 'truename';
				$this->assign ( 'field', $field );
				$this->display ( 'add_userinfo' );
				exit ();
			}
		}
		// 第三步:是否需要手机实名
		if ($config ['need_mobile'] && empty ( $member ['mobile'] )) {
			if (IS_POST) {
				! empty ( $_POST ['mobile'] ) || $this->error ( '手机号不能为空' );
				// 短信验证 TODO
				M ( 'member' )->where ( 'uid=' . $uid )->setField ( 'mobile', I ( 'post.mobile' ) );
			} else {
				$field ['title'] = '手机号';
				$field ['name'] = 'mobile';
				$this->assign ( 'field', $field );
				$this->display ( 'add_userinfo' );
				exit ();
			}
		}
		// 第四步:其它用户资料初始化 TODO
	}
}
