<?php
        	
namespace Addons\Library\Model;
use Home\Model\WeixinModel;
        	
/**
 * Library的微信模型
 */
class WeixinAddonModel extends WeixinModel{
	function reply($dataArr, $keywordArr = array()) {
		$userinfo = $this -> getUserInfo();
		if(empty($userinfo)){
			$this -> replyText("未绑定，请回复'绑定'绑定体育系账号");
			return;
		}
		$this -> getLibrary($userinfo['card_num'], $userinfo['tsg_pw']);
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

	private function getLibrary($card_num, $tsg_pw){
		$ch = curl_init();
		$postdata ="username=".$tsg_pw."&password=".$card_num;
		curl_setopt($ch, CURLOPT_URL, 'http://herald.seu.edu.cn/public_api/FD76C03E0B8F7B38739D0EB9C2FBC226/library/rendered_books/');
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $postdata);
		$responseword = curl_exec($ch);
		curl_close($ch);
		if($responseword == "ACOUNT_ERROR"){
			$this -> replyText('绑定信息错误，请重新绑定!');
			return;
		}elseif ($responseword == "SERVER_ERROR") {
			$this -> replyText("无法连接图书馆服务器，请稍后重试");
			return;
		}elseif ($responseword == "REQUEST_POST_ERROR") {
			$this -> replyText("错误请求");
			return;
		}else{
			$result = json_decode($responseword);
			if(empty($result)){
				$this -> replyText("未借书");
				return;
			}
			$retext = "";
			foreach ($result as $val) {
				$retext .= $val -> render_date ."至\n".$val -> due_date."\n".$val -> title."\n------------\n";
			}
			$this -> replyText($retext);
			return;
		}
	}
}
        	