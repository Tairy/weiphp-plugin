<?php

namespace Common\Model;

use Think\Model;

/**
 * 插件配置操作集成
 */
class AddonStatusModel extends Model {
	/**
	 * 保存配置
	 */
	function set($addon, $status) {
		$map ['token'] = get_token ();
		if (empty ( $map ['token'] )) {
			return false;
		}
		$info = M ( 'member_public' )->where ( $map )->find ();
		if (! $info) {
			$map ['uid'] = session ( 'mid' );
			$addon_status [$addon] = intval ( $status );
			$map ['addon_status'] = json_encode ( $addon_status );
			$flag = M ( 'member_public' )->add ( $map );
		} else {
			$addon_status = json_decode ( $info ['addon_status'], true );
			$addon_status [$addon] = intval ( $status );
			$flag = M ( 'member_public' )->where ( $map )->setField ( 'addon_status', json_encode ( $addon_status ) );
		}
		// dump(M ( 'member_public' )->getLastSql());exit;
		return $flag;
	}
	/**
	 * 获取插件配置
	 * 获取的优先级：当前公众号设置》后台默认配置》安装文件上的配置
	 */
	function getList() {
		// 当前公众号的设置
		$map ['token'] = get_token ();
		if (empty ( $map ['token'] )) {
			return array ();
		}
		$info = M ( 'member_public' )->where ( $map )->find ();
		$token_status = json_decode ( $info ['addon_status'], true );
		
		// 等级权限
		if ($info ['group_id']) {
			$map2 ['id'] = $info ['group_id'];
			$addon_ids = M ( 'member_public_group' )->where ( $map2 )->getField ( 'addon_status' );
			if ($addon_ids) {
				$map3 ['id'] = array (
						'in',
						$addon_ids 
				);
				$addons = M ( 'addons' )->where ( $map3 )->field ( '`name`' )->select ();
				foreach ( $addons as $a ) {
					$token_status [$a ['name']] = '-1';
				}
			}
		}
		// dump ( $token_status );
		// dump(M ( 'member_public' )->getLastSql());exit;
		return $token_status;
	}
}
?>
