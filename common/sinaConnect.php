<?php
	/**
	 * 作者：peter
	 * 日期：2012-10-02
	 * 说明：新浪微博
	 */
	include_once("saetv2.ex.class.php");
	class sinaConnect extends base {
		private $appid =0;
		private $appkey = 0;
		private $callback = 0;
		private $queryUrl = '';

		function init($appid, $appkey, $callback) {
			$this -> appid  = $appid;
			$this -> appkey  = $appkey;
			$this -> callback = $callback;
		}

		/**
		 * 获得授权Url
		 */
		function getLoginUrl() {
			$o = new SaeTOAuthV2($this -> appid, $this -> appkey);
			$aurl = $o -> getAuthorizeURL($this -> callback);
			header("Location:$aurl");
		}
		
		function LoginCallback() {
			$o = new SaeTOAuthV2($this -> appid, $this -> appkey);
			if (isset($_REQUEST['code'])) {
				$keys = array();
				$keys['code'] = $_REQUEST['code'];
				$keys['redirect_uri'] = $this -> callback;
				try {
					$token = $o -> getAccessToken('code', $keys);
				} catch(OAuthException $e) {
					
				}
			}

			if($token) {
				$_SESSION['weibo']['openid'] = $token['uid'];
				$_SESSION['weibo']['access_token'] = $token['access_token'];
				$_SESSION['weibo']['expires_in'] = $token['expires_in'];
				$userInfo = $this -> getUserInfo($token);
				$_SESSION['weibo']['nickname'] = $userInfo['screen_name'];
				$_SESSION['weibo']['pic'] = $userInfo['avatar_large'];
				return true;
			} else {
				exit("亲，现在无法连接新浪微博服务");
			}
		}

		/**
		 * 获得用户信息
		 * https://api.weibo.com/2/users/show.json
		 * 请求参数
		 * 必选	类型及范围	说明
		 * source	false	string	采用OAuth授权方式不需要此参数，其他授权方式为必填参数，数值为应用的AppKey。
		 * access_token	false	string	采用OAuth授权方式为必填参数，其他授权方式不需要此参数，OAuth授权后获得。
		 * uid	false	int64	需要查询的用户ID。
		 * screen_name	false	string	需要查询的用户昵称。
		 * 返回值字段	字段类型	字段说明
		 * id	int64	用户UID
		 * idstr	string	字符串型的用户UID
		 * screen_name	string	用户昵称
		 * name	string	友好显示名称
		 * province	int	用户所在省级ID
		 * city	int	用户所在城市ID
		 * location	string	用户所在地
		 * description	string	用户个人描述
		 * url	string	用户博客地址
		 * profile_image_url	string	用户头像地址，50×50像素
		 * profile_url	string	用户的微博统一URL地址
		 * domain	string	用户的个性化域名
		 * weihao	string	用户的微号
		 * gender	string	性别，m：男、f：女、n：未知
		 * followers_count	int	粉丝数
		 * friends_count	int	关注数
		 * statuses_count	int	微博数
		 * favourites_count	int	收藏数
		 * created_at	string	用户创建（注册）时间
		 * following	boolean	暂未支持
		 * allow_all_act_msg	boolean	是否允许所有人给我发私信，true：是，false：否
		 * geo_enabled	boolean	是否允许标识用户的地理位置，true：是，false：否
		 * verified	boolean	是否是微博认证用户，即加V用户，true：是，false：否
		 * verified_type	int	暂未支持
		 * remark	string	用户备注信息，只有在查询用户关系时才返回此字段
		 * status	object	用户的最近一条微博信息字段
		 * allow_all_comment	boolean	是否允许所有人对我的微博进行评论，true：是，false：否
		 * avatar_large	string	用户大头像地址
		 * verified_reason	string	认证原因
		 * follow_me	boolean	该用户是否关注当前登录用户，true：是，false：否
		 * online_status	int	用户的在线状态，0：不在线、1：在线
		 * bi_followers_count	int	用户的互粉数
		 * lang	string	用户当前的语言版本，zh-cn：简体中文，zh-tw：繁体中文，en：英语
		 */
		function getUserInfo($token) {
			$c = new SaeTClientV2($this -> appid, $this -> appkey, $token["access_token"]);
			return $c -> show_user_by_id($token["uid"]);
		}
	}
?>