<?php
	/**
	 * 作者：peter
	 * 日期：2012-10-10
	 * 说明：member
	 */
	class member extends spModel {
		var $pk = "uid"; // 主键
		var $table = "member"; // 数据表的名称

		var $addrules = array(
			'emailHasUsed' => array('member', 'emailHasUsed'), //检查用户名是否重复
			'isverifycode' => array('member', 'checkverifycode'), //检查验证码是否输入正确
			'checklogin' => array('member', 'checklogin'), //检查登录信息
			'hasEmail' => array("member", "hasEmail"),
			'isopen' => array('member', 'isopen'), //检查是否允许登录
			'keepmail' => array('member', 'keepmail'), //检查是否被限制
			'userNameHasUsed' => array('member', 'userNameHasUsed') //检查昵称是否使用
		);

		var $verifier_login  = array(
		   "rules" => array(
				'email' => array(
					'notnull' => TRUE, //username不能为空
					'minlength' => 5, //username长度不能小于5
					'maxlength' => 50, //username长度不能大于
					'email' => TRUE,
					"hasEmail" => 'email',
					'isopen' => true //是否被禁用
				),
				'password' => array(
					'notnull' => TRUE,
					'minlength' => 6
				),
				'verifycode' => array(
					'notnull' => TRUE,
					'isverifycode' => 'verifycode',
					'checklogin' => TRUE
				)
			),
		   "messages" => array( //提示信息
				'email' => array(
					'notnull' => "邮箱不能为空",
					'minlength' => "邮箱不能少于5个字符",
					'maxlength' => "邮箱不能大于50个字符",
					'email' => "请输入正确的邮箱",
					"hasEmail" => "用户名或密码错误",
					'isopen' => "帐号已被锁定"
				),
				'password' => array(
					'notnull' => "密码不能为空",
					'minlength' => "密码不能少于6个字符"
				),
				'verifycode' => array(
					'notnull' => "请填写验证码",
					'isverifycode' => "请输入正确的验证码",
					'checklogin' => "用户名或密码不正确"
				)
			)
		);

		//连接登录的验证
		var $verifier_openConnect_Login  = array(
			"rules" => array(
				'email' => array(
					'notnull' => TRUE, //username不能为空
					'minlength' => 5,  //username长度不能小于5
					'maxlength' => 50, //username长度不能大于
					'email' => TRUE,
					"hasEmail" => 'email',
					'isopen' => true //是否被禁用
				),
				'password' => array(
					'notnull' => TRUE,
					'minlength' => 6,
					'checklogin' => TRUE
				)
			),
			"messages" => array( //提示信息
				'email' => array(
					'notnull' => "邮箱不能为空",
					'minlength' => "邮箱不能少于5个字符",
					'maxlength' => "邮箱不能大于50个字符",
					'email' => "请输入正确的邮箱",
					"hasEmail" => "用户名或密码错误",
					'isopen' => "帐号已被锁定"
				),
				'password' => array(
					'notnull' => "密码不能为空",
					'minlength' => "密码不能少于6个字符",
					'checklogin' => "用户名或密码不正确"
				)
			)
		);
		
	   var $verifier_reg  = array(
			"rules" => array(
				'email' => array(
					'notnull' => TRUE, //username不能为空
					'minlength' => 5,  //username长度不能小于5
					'maxlength' => 50, //username长度不能大于
					'email' => TRUE,
					'keepmail' => TRUE,
					'emailHasUsed' => 'email'
				),
				'password' => array(
					'notnull' => TRUE,
					'minlength' => 6
				),
				'username'=>array(
					'minlength' => 3,  //username长度不能小于5
					'maxlength' => 12, //username长度不能大于
					'userNameHasUsed' => TRUE
				),
				'password2' => array(
					'equalto' => 'password'
				)
			),
			"messages" => array( //提示信息
				'email' => array(
					'notnull' => "注册邮箱不能为空",
					'minlength' => "注册邮箱不能少于5个字符",
					'maxlength' => "注册邮箱不能大于50个字符",
					'email' => "请输入正确的邮箱",
					'keepmail' => "该邮箱被限制使用请更换",
					'emailHasUsed' => "注册邮箱已经存在,试试绑定?"
				),
				'username' => array(
					'minlength' => "昵称不能小于3个字",  //username长度不能小于5
					'maxlength' => "昵称不能超过12个字", //username长度不能大于
					'userNameHasUsed' => "昵称已被使用请更换"
				),
				'password' => array(
					'notnull' => "密码不能为空",
					'minlength' => "密码不能少于5个字符"
				),
				'password2' => array(
					'equalto' => '两次密码输入不一致'
				),
				'verifycode' => array(
					'notnull' => "请填写验证码",
					'isverifycode'=> "请输入正确的验证码"
				)
			)
		);

		//连接注册的验证
		 var $verifier_openConnect_Reg  = array(
			"rules" => array(
				'email' => array(
					'notnull' => TRUE, //username不能为空
					'minlength' => 5,  //username长度不能小于5
					'maxlength' => 50, //username长度不能大于
					'email' => TRUE,
					'keepmail' => TRUE,
					'emailHasUsed' => 'email' //如果真重复了
				),
				'password' => array(
					'notnull' => TRUE,
					'minlength' => 6
				),
				'username' => array(
					'minlength' => 3,  //username长度不能小于5
					'maxlength' => 12, //username长度不能大于
					'userNameHasUsed' => TRUE
				),
				'password2' => array(
					'equalto' => 'password'
				),
			),
			"messages" => array( //提示信息
				'email' => array(
					'notnull' => "注册邮箱不能为空",
					'minlength' => "注册邮箱不能少于5个字符",
					'maxlength' => "注册邮箱不能大于50个字符",
					'email' => "请输入正确的邮箱",
					'keepmail' => "该邮箱被限制使用请更换",
					'emailHasUsed' => "注册邮箱已经存在,试试绑定?"
				),
				'username' => array(
					'minlength' => "昵称不能小于3个字",  //username长度不能小于5
					'maxlength' => "昵称不能超过12个字", //username长度不能大于
					'userNameHasUsed' => "昵称已被使用请更换"
				),
				'password' => array(
					'notnull' => "密码不能为空",
					'minlength' => "密码不能少于5个字符"
				),
				'password2' => array(
					'equalto' => "两次密码输入不一致"
				)
			)
		);
		
		//用户登录，规则验证
		function userLogin($row) {
			return true;
		}
		
		//用户注册
		function userReg($row) {
			$ip = $_SERVER["REMOTE_ADDR"];
			$salt = randstr();
			$password = password_encode($row['password'], $salt);
			$arr = array(
				'password' => $password,
				'salt' => $salt,
				'regtime' => time(),
				'regip' => $ip,
				"livecity_code" => "001001",
				"livecity_name" => "徐州"
			);
			$row = array_merge($row, $arr);

			$uid = $this -> create($row);
			$_SESSION['uid'] = $uid;
			spClass('notice') -> sendRegisgtr($uid);
			return $uid;
		}

		//检查邮箱是否已经使用
		function emailHasUsed($val, $right) {
			$result = $this -> findBy('email', $val);
			if(is_array($result)) {
				return FALSE;
			} else {
				return TRUE;
			}
		}
		
		//注册昵称是否重复
		function userNameHasUsed($val, $right, $all) {
			$result = $this -> findBy('username', $all['username']);
			if(is_array($result)) {
				return FALSE;
			} else {
				return TRUE;
			}
		}

		//检测限制账号
		function keepmail($val, $right, $all) {
			$keep =  $GLOBALS['LUOMOR']['keep_email'];
			if($keep != '') {
				$keeparray = explode(',', $keep);
				$emails = explode('@', $all['email']);
				if(in_array($emails[0], $keeparray)) {
					return false;
				} else {
					return true;
				}
			} else {
				return true;
			}
		}
		
		function hasEmail($val, $right, $all) {
			$result = $this -> findBy("email", $all["email"]);
			if(!is_array($result)) {
				return false;
			} else {
				return true;
			}
		}

		//检查账号是否被禁用
		function isopen($val, $right, $all) {
			$result = $this -> findBy('email', $all['email']);
			if($result['open'] == 0) {
				return false;
			} else {
				return true;
			}
		}

		//检查用户名密码是否正确
		function checklogin($val, $right, $all) {
			$result = $this -> findBy('email', $all['email']);
			if($all['email'] == '' || $all['password'] == '') {
				return false;
			}
			$password = password_encode($all['password'], $result['salt']);
			if($result['password'] == $password) {
				$ip = getIP();
				$_SESSION["uid"] = $result["uid"];
				$_SESSION["email"] = $result["email"];
				$_SESSION["domain"] = $result["domain"];
				$_SESSION["username"] = $result["username"];
				$_SESSION["admin"] = $result["admin"];
				$_SESSION["livecity_code"] = $result["livecity_code"];
				$_SESSION["livecity_name"] = $result["livecity_name"];
				if($all['savename'] == 1) {
					setcookie('unames', $result['email'], time() + 60*60*24*30, '/');
				} else {
					setcookie('unames', $result['email'], time() - 60*60*24*30, '/');
				}

				$local = ip2name($ip);
				$this -> update(array('uid' => $result['uid']), array('logtime' => time(), 'logip' => $ip, 'local' => $local));
				$this -> _getActionToken($result['uid']);
				return true;
			} else {
				return false;
			}
		}

		//注册checkverifycode是否正确
		function checkverifycode($val, $right, $all) {
			$vcode = spClass('spVerifyCode');
			if($vcode -> verify($all['verifycode'])) {
				return TRUE;
			} else {
				return FALSE;
			}
		}

		//获取所有活动的扩展登录信息
		 function _getActionToken($uid) {
			$rs = spClass('memberex') -> spLinker() -> findAll(array('uid' => $uid));
			foreach($rs as $d) {
				$_SESSION['openconnect'][$d['platform']]['openid'] = $d['openid'];
				$_SESSION['openconnect'][$d['platform']]['access_token'] = $d['access_token'];
				$_SESSION['openconnect'][$d['platform']]['client_secret'] = $d['client_secret'];
			}
		}
	}
?>