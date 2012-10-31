<?php
	if( substr(PHP_VERSION, 0, 3) == "5.3" ) {
		error_reporting(E_ALL & ~E_NOTICE & ~E_WARNING & ~E_DEPRECATED);
	} else {
		error_reporting(E_ALL & ~E_NOTICE & ~E_WARNING);
	}

	class qqConnect {
		private $appid = 0;
		private $appkey = 0;
		private $callback = 0;
		private $queryUrl = '';
		private $QQhexchars = '0123456789ABCDEF';

		function init($appid, $appkey, $callback) {
			$this -> appid  = $appid;
			$this -> appkey  = $appkey;
			$this -> callback = $callback;
		}

		/**
		 * http://wiki.opensns.qq.com/wiki/%E3%80%90QQ%E7%99%BB%E5%BD%95%E3%80%91%E4%BD%BF%E7%94%A8Authorization_Code%E8%8E%B7%E5%8F%96Access_Token
		 * 参数	是否必须	含义
		 * response_type	必须	授权类型，此值固定为“code”。
		 * client_id	必须	申请QQ登录成功后，分配给应用的appid。
		 * redirect_uri	必须	成功授权后的回调地址，必须是注册appid时填写的主域名下的地址，建议设置为网站首页或网站的用户中心。
		 * scope	可选	请求用户授权时向用户显示的可进行授权的列表。
		 * 可填写的值是【QQ登录】API文档中列出的接口，以及一些动作型的授权（目前仅有：do_like），如果要填写多个接口名称，请用逗号隔开。
		 * 例如：scope=get_user_info,list_album,upload_pic,do_like
		 * 不传则默认请求对接口get_user_info进行授权。
		 * 建议控制授权项的数量，只传入必要的接口名称，因为授权项越多，用户越可能拒绝进行任何授权。
		 * state	可选	client端的状态值。用于第三方应用防止CSRF攻击，成功授权后回调时会原样带回。
		 * display	可选	 仅PC网站接入时使用。
		 * 用于展示的样式。不传则默认展示为PC下的样式。
		 * 如果传入“mobile”，则展示为mobile端下的样式。
		 * g_ut	可选	仅WAP网站接入时使用。
		 * QQ登录页面版本（1：wml版本； 2：xhtml版本），默认值为1。
		 */
		function getLoginUrl() {
			$uri = 'https://graph.qq.com/oauth2.0/authorize?';
			$_SESSION['state'] = md5(uniqid(rand(), TRUE));
			$post = array(
				'response_type' => 'code',
				'scope' => 'get_user_info,list_album,upload_pic,add_album,add_one_blog,add_topic,add_share,add_weibo,list_photo,check_page_fans',
				'client_id' => $this -> appid,
				'redirect_uri' => $this -> callback,
				'state' => $_SESSION['state']
			);
			$redirect = $uri . $this -> postData($post);
			header("Location:$redirect");
		}

		/**
		 * 参数	是否必须	含义
		 * grant_type	必须	授权类型，此值固定为“authorization_code”。
		 * client_id	必须	申请QQ登录成功后，分配给网站的appid。
		 * client_secret	必须	申请QQ登录成功后，分配给网站的appkey。
		 * code	必须	上一步返回的authorization code。
		 * 如果用户成功登录并授权，则会跳转到指定的回调地址，并在URL中带上Authorization Code。
		 * 例如，回调地址为www.qq.com/my.php，则跳转到：
		 * http://www.qq.com/my.php?code=520DD95263C1CFEA087****** 
		 * 注意此code会在10分钟内过期。
		 * state	必须	client端的状态值。用于第三方应用防止CSRF攻击，成功授权后回调时会原样带回。
		 * redirect_uri	必须	与上面一步中传入的redirect_uri保持一致。
		 */
		public function LoginCallback() {
			if($_REQUEST['state'] == $_SESSION['state']) {
				$code = $_REQUEST['code'];
				$uri = 'https://graph.qq.com/oauth2.0/token?';
				$post = array(
					'grant_type' => 'authorization_code',
					'client_id' => $this -> appid,
					'client_secret' => $this -> appkey,
					'redirect_uri' => $this -> callback,
					'code' => $code,
					'state' => $_SESSION['state']
				);
				$response = $this -> formPost($uri, $post);
				if(strpos($response, "callback") !== false) {
					$lpos = strpos($response, "(");
					$rpos = strrpos($response, ")");
					$response  = substr($response, $lpos + 1, $rpos - $lpos -1);
					$msg = json_decode($response);
					if(isset($msg -> error)) {
					   echo "<h3>error:</h3>" . $msg -> error;
					   echo "<h3>msg  :</h3>" . $msg -> error_description;
					   exit;
					}
				}
				$params = array();
				parse_str($response, $params);
				$uri = 'https://graph.qq.com/oauth2.0/me?';
				$post = array(
					'access_token' => $params['access_token']
				);
				$str = $this -> formPost($uri, $post);
				if(strpos($str, "callback") !== false) {
					$lpos = strpos($str, "(");
					$rpos = strrpos($str, ")");
					$str  = substr($str, $lpos + 1, $rpos - $lpos -1);
				}
				$user = json_decode($str);
				if(isset($user -> error)) {
					echo "<h3>error:</h3>" . $user -> error;
					echo "<h3>msg  :</h3>" . $user -> error_description;
					exit;
				}
				$_SESSION['qq']['openid'] = $user -> openid;
				$_SESSION['qq']['oauth_token'] = $params['access_token'];
				$_SESSION['qq']['expires'] = (time() + $params['expires_in']);

				$rs = $this -> get_user_info($params['access_token'], $user -> openid);

				$_SESSION['qq']['nickname'] = $rs['nickname'];
				$_SESSION['qq']['pic'] = $rs['pic'];
				return true;
			} else {
				exit("The state does not match. You may be a victim of CSRF.");
			}
		}

		/**
		 * 获得用户昵称和头像信息
		 * 参数	含义
		 * access_token	可通过使用Authorization_Code获取Access_Token 或使用Implicit Grant方式获取Access Token来获取。
		 * access_token有3个月有效期。
		 * oauth_consumer_key	申请QQ登录成功后，分配给应用的appid
		 * openid	用户的ID，与QQ号码一一对应。
		 * 可通过调用https://graph.qq.com/oauth2.0/me?access_token=YOUR_ACCESS_TOKEN 来获取。
		 * ret：返回码
		 * msg：如果ret<0，会有相应的错误信息提示，返回数据全部用UTF-8编码
		 * nickname：昵称
		 * figureurl：大小为30×30像素的头像URL
		 * figureurl_1：大小为50×50像素的头像URL
		 * figureurl_2：大小为100×100像素的头像URL
		 * gender：性别。如果获取不到则默认返回“男”
		 * vip：标识用户是否为黄钻用户（0：不是；1：是）
		 * level：黄钻等级
		 * is_yellow_year_vip：标识是否为年费黄钻用户（0：不是； 1：是）
		 * {
		 * "ret":0,
		 * "msg":"",
		 * "nickname":"Peter",
		 * "figureurl":"http://qzapp.qlogo.cn/qzapp/111111/942FEA70050EEAFBD4DCE2C1FC775E56/30",
		 * "figureurl_1":"http://qzapp.qlogo.cn/qzapp/111111/942FEA70050EEAFBD4DCE2C1FC775E56/50",
		 * "figureurl_2":"http://qzapp.qlogo.cn/qzapp/111111/942FEA70050EEAFBD4DCE2C1FC775E56/100",
		 * "gender":"男",
		 * "vip":"1",
		 * "level":"7",
		 * "is_yellow_year_vip":"1"
		 * }
		 */
		private function get_user_info($token, $openid) {
			$uri    = 'https://graph.qq.com/user/get_user_info?';
			$post = array(
				'access_token' => $token,
				'oauth_consumer_key' => $this->appid,
				'openid' => $openid,
				'format' => 'json'
			);
			$response = $this -> formPost($uri, $post);
			$rs = json_decode($response, true);
			if($rs['ret'] == 0) {
				$data['nickname'] = $rs['nickname'];
				$data['pic'] = substr($rs['figureurl'],0,-2).'100';
			} else {
				echo "<h3>error:</h3>" . $rs['ret'];
				echo "<h3>msg  :</h3>" . $rs['msg'];
				exit;
			}
			return $data;
		}
		
		private function formPost($url, $post_data) {
			$o = '';
			foreach($post_data as $k => $v) {
				$o .= "$k=" . urlencode($v) . "&";
			}
			$post_data = substr($o, 0, -1);
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_POST, 1);
			curl_setopt($ch, CURLOPT_HEADER, 0);
			curl_setopt($ch, CURLOPT_URL, $url);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
			$result = curl_exec($ch);
			return $result;
		}

		private function postData($post_data) {
			$o = '';
			foreach ($post_data as $k => $v) {
				$o .= "$k=" . urlencode($v) . "&";
			}
			$post_data = substr($o, 0, -1);
			return $post_data;
		}
	}
?>