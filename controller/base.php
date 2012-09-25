<?php
	/**
	 * 作者：peter
	 * 日期：2012-09-25
	 * 说明：baseController
	 */
	class base extends spController {
		// 构造函数，进行全局操作的位置
		function __construct() {
			parent::__construct();
			global $site_uri;
			$this -> luomor = $GLOBALS['LUOMOR'] + $GLOBALS['G_SP']['luomor'];
			$this -> url = $site_uri;
		}

		/*调用基本信息及通知信息*/
		protected function memberinfo() {
			if(islogin()) {
				$this -> user = spClass('member') -> find(array('uid' => $_SESSION['uid'])); //用户信息
				$this -> local = explode('市', $this -> user['local']);
				$this -> followuid = spClass('follow') -> getFollowUid($_SESSION['uid']);//获取跟随者信息
				$this -> messagestate();
			}
		}

		public function messagestate($echo = 0) {
			if(islogin()) {
				$newmsg = 0;
				//0 用户私信  1 评论通知  2 系统通知 3关注通知
				$where = "uid ='{$_SESSION['uid']}' and isread =0 and sys in(1,2,3)";
				$this -> notice = spClass('notice') -> spLinker() -> findAll($where, 'id desc'); //未读通知
				$this -> noticeCount =  $this -> pmCount =  '';
				if($this -> notice) {
					$newmsg = 1;
					$this -> noticeCount = '<span>' . count($this->notice) . '</span>';
				}

				$where = "uid ='{$_SESSION['uid']}' and isread = 0 and sys = 0";
				$this -> pm = spClass('notice') -> spLinker() -> findAll($where, 'id desc'); //未读通知

				if($this -> pm) {
					$newpm = 1;
					$this -> pmCount = '<cite>' . count($this -> pm) . '</cite>';
				}

				if($echo == 1) {
					$data['state'] = $newmsg;
					$data['pm'] = $newpm;
					$data['notice'] = $this -> notice;
					$data['pm'] = $this -> notice;
					echo json_encode($data);
				}
			}
		}

		/*处理信息tag user.c and  add.c used*/
		protected function __parse_mytag($mytag) {
			if($mytag != '') {
				$mytag = explode(',', $mytag);
				if(is_array($mytag)) {
					$this -> myTag = $mytag;
				}
			}
		}
		
		/*无提示跳转*/
		public function jslocation($x) {
			exit('<script language="javascript" type="text/javascript"> parent.window.location.href="' . $x . '";</script>');
		}
	}
?>