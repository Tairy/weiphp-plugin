<?php

namespace Addons\Curriculum\Model;
use Home\Model\WeixinModel;

/**
 * Curriculum的微信模型
 */
class WeixinAddonModel extends WeixinModel{
	function reply($dataArr, $keywordArr = array()) {
		//$config = getAddonConfig ( 'Curriculum' ); // 获取后台插件的配置参数
		//dump($config);
    if(! empty( $keywordArr ['aim_id'] )){
      //$map ['id'] = $keywordArr ['aim_id'];
      switch( $keywordArr ['aim_id'] ){
        case '2':
          $this -> getTodayCurrium();
          break;
        default:
          $this -> replyText('未知错误');
          break;
      }
    }
	}

	// 关注公众号事件
	public function subscribe() {
		return true;
	}

	// 取消关注公众号事件
	public function unsubscribe() {
		return true;
	}

	// 扫描带参数二维码事件
	public function scan() {
		return true;
	}

	// 上报地理位置事件
	public function location() {
		return true;
	}

	// 自定义菜单事件
	public function click() {
		return true;
	}

	private function getTodayCurrium(){
		$day = $this -> getDayNum();
		$card_num = $this -> getCardNum();
		if(empty($card_num)){
			$this -> replyText("回复[绑定],绑定账号信息之后才能查询课表");
			return;
		}
		$curriculum = $this -> getCurriculum($card_num, $day, '13-14-3');
		$this -> replyText($curriculum);
	}

	private function getCardNum(){
		$FormsValue = M('FormsValue');
		$param['openid'] = get_openid ();
		$param['token'] = get_token();
		$result = $FormsValue -> where($param) -> find();
		$userinfo = unserialize($result['value']);
		return $userinfo['card_num'];
	}

	private function getDayNum(){
		return date("w") -1;
	}

	private function getCurriculum($card_num, $day,$currentTerm){
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, 'http://herald.seu.edu.cn/public_api/FD76C03E0B8F7B38739D0EB9C2FBC226/curriculum/'.$card_num.'/'.$currentTerm.'/');
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		$responseword = curl_exec($ch);
		$termArray = json_decode($responseword);
		curl_close($ch);
		$currResult = "";
		foreach ($termArray[$day] as $curr) {
			$currResult .= $curr[0]."\n".$curr[1]."\n".$curr[2]."\n";
		}
		return $currResult;
	}
}
