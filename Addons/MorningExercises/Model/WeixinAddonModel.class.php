<?php
        	
namespace Addons\MorningExercises\Model;
use Home\Model\WeixinModel;
        	
/**
 * MorningExercises的微信模型
 */
class WeixinAddonModel extends WeixinModel{
	function reply($dataArr, $keywordArr = array()) {
		$userinfo = $this -> getUserInfo();
		if(empty($userinfo)){
			$this -> replyText("未绑定，请回复'绑定'绑定体育系账号");
			return;
		}
		$this -> getExerciseNum($userinfo['card_num'], $userinfo['tyx_pw']);
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

	private function getUserInfo(){
		$FormsValue = M('FormsValue');
		$param['openid'] = get_openid ();
		$param['token'] = get_token();
		$result = $FormsValue -> where($param) -> find();
		$userinfo = unserialize($result['value']);
		return $userinfo;
	}

	private function getExerciseNum($card_num, $tyx_pw){
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, 'http://herald.seu.edu.cn/public_api/FD76C03E0B8F7B38739D0EB9C2FBC226/tyx/'.$card_num.'/'.$tyx_pw.'/');
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		$responseword = curl_exec($ch);
		$termArray = json_decode($responseword);
		curl_close($ch);
		if($responseword == "Account Error"){
			$this -> replyText('绑定信息错误，请重新绑定!');
		}else if($responseword == "Server Error"){
			$this -> replyText("无法连接体育系服务器，请稍后重试");
		}else{
			$this -> replyText("已跑操".$responseword."次");
		}
	}
}
        	