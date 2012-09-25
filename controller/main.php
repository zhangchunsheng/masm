<?php
	/**
	 * 作者：张春生
	 * 日期：2012-09-25
	 * 说明：入口页面
	 */
	class main extends base {
		public function index() {
			$this -> favatag = spClass('db_mytag') -> myFavaTag($_SESSION['uid'], 5); //显示收藏标签
			$this -> CurrentModule = 'index';
			if(islogin() && $_SESSION['username'] == '') {
				header('Location:' . spUrl('user', 'setting'));
			}

			$this -> memberinfo();
			$uid = $_SESSION['uid'];
			//likeId 为喜欢id 如果不为空,则我喜欢.
			//followId 为关注id 如果不为空,则我关注

			$sql = "SELECT b. * , k.id AS likeid , f.id AS followid ,m.username,m.domain
							FROM `".DBPRE."blog` AS b LEFT JOIN `".DBPRE."likes` AS k ON ( b.bid = k.bid AND k.uid ='$uid' )
							LEFT JOIN `".DBPRE."follow` AS f ON ( b.uid = f.touid and f.uid = '$uid' )
							LEFT JOIN `".DBPRE."member`  as m on b.uid = m.uid where b.open = 1 ";

			if($this -> user['flow'] >= 15) {
				$sql .= "and  b.uid in ($this->followuid,$uid) and b.open=1 ORDER BY b.time desc";
			} else {
				$sql .= "ORDER BY b.time desc";
			}
			$this -> blogs = spClass('db_blog') -> spPager($this -> spArgs('page', 1), 10) -> findSql($sql);

			$this -> pager = spClass('db_blog') -> spPager() -> pagerHtml('main');
			$page = spClass('db_blog') -> spPager() -> getPager('');

			if(!islogin()) {
				$this -> email = $_COOKIE['unames'];
				$this -> display('login.html');
			} else {
				if($this -> spArgs('ajaxload')) {
					if($this -> spArgs('page') <= $page['total_page']) {
						$this -> limits = 4;
						$this -> data = $this->blogs; //将内容给模板变量
						$this -> display('require_feeds.html');
						exit;
					}
				} else {
					$this -> display('index.html');
				}
			}
		}

		public function recommend()
		{
			$this->memberinfo();
			$tags = spClass('db_tags')->getHotTag(30);
			if($tags['string'])
			{
				$where = "`open` = 1 and `tag` in ({$tags['string']})";
			}

			$blogs = spClass('db_blog')->spLinker()->findAll($where,'bid desc','',30);


			$this->feeds = feddshtml($blogs,0,'recommend');
			$this->hotMax = $tags['rs'][0]['num'];

			$this->htag = $tags['rs'];



			$this->title = '推荐频道';
			$this->CurrentModule = 'recommend';
			$this->display('recommend.html');

		}


		public function discovery()
		{
			$this->memberinfo();
			 $this->cate = spClass('db_category')->findCate();


			if($this->spArgs('catename'))
			{
				$_SESSION['discover_catename'] = $this->spArgs('catename');
				$cname = spClass('member')->escape('%'.urldecode($this->spArgs('catename')).'%');
				if($this->spArgs('local'))
				{
					$local = spClass('member')->escape('%'.urldecode($this->spArgs('local')).'%');
					$_SESSION['discover_local'] = TRUE;
					$where = "`local` like $cname ";
				}else{
					unset($_SESSION['discover_local']);
				}
				$this->currcid = $this->spArgs('cid');

			}

			if($_SESSION['discover_catename'])
			{
				$blogtag = explode(',',urldecode($_SESSION['discover_catename']));
				$pre = '';
					foreach($blogtag as $d)
					{
						$pre .= '`blogtag` like \'%'.$d.'%\' or ';
					}
					$pre = substr($pre,0,-4);
					$where = "$pre and `blogtag` != ''";
			}

			if($_SESSION['discover_local'])
			{
				$cname = spClass('member')->escape('%'.urldecode($_SESSION['discover_catename']).'%');
				$where = "`local` like $cname ";
			}

			if($this->spArgs('cateall'))
			{
				unset($_SESSION['discover_catename']);
				header("Location:".spUrl('main','discovery'));
			}


			$this->userinfo = spClass('member')->spPager($this->spArgs('page',1),16)->findAll($where,'flow desc,num desc');
			$count  = spClass('member')->findCount($where);
			$page = spClass('member')->spPager()->getPager();



			$this->CurrentModule = 'discover';
			if($this->spArgs('catename'))
			{
				$this->titlepre = urldecode($this->spArgs('catename')).' - 发现';
			}else{
				$this->titlepre = '发现';
			}
			if($this->spArgs('ajaxload'))
			{
				if($this->spArgs('page') <= $page['total_page'])
				{
					$this->data = $this->userinfo;
					$this->display('require_discovery_user.html');
				}
			}else{
				$this->display('discovery.html');
			}
		}


		public function now()
		{

			$this->memberinfo();
			$this->favatag = spClass('db_mytag')->myFavaTag($_SESSION['uid'],5); //显示收藏标签
			$sql = "SELECT b. * , k.id AS likeid , f.id AS followid ,m.username,m.domain
							FROM `".DBPRE."blog` AS b LEFT JOIN `".DBPRE."likes` AS k ON ( b.bid = k.bid AND k.uid ='$uid' )
							LEFT JOIN `".DBPRE."follow` AS f ON ( b.uid = f.touid and f.uid = '$uid' )
							LEFT JOIN `".DBPRE."member` as m on b.uid = m.uid where b.open = 1 order by b.bid desc";

			$this->blogs = spClass('db_blog')->spPager($this->spArgs('page',1),10)->findSql($sql);
			$page = spClass('db_blog')->spPager()->getPager('');

			$this->title = '此刻最新';
			$this->CurrentModule = 'now';
			if($this->spArgs('ajaxload'))
			{
				if($this->spArgs('page') <= $page['total_page'])
				{
					$this->limits = 4;
					$this->data = $this->blogs; //将内容给模板变量
					$this->display('require_feeds.html');
				}
			}else{
				$this->display('now.html');
			}
		}














































		/*用户登陆*/
		public function login()
		{

			if($this->spArgs('email'))
			{

				$userobj = spClass('member');

				if($this->yb['loginCodeSwitch'] != 'close') //如果开启
				{
					$userobj->verifier = $userobj->verifier_login;
				}else{
					$userobj->verifier = $userobj->verifier_openConnect_Login;
				}

				if( false == $userobj->spVerifier($this->spArgs()) ){
					$userobj->userLogin($this->spArgs());


					if($this->spArgs('callback'))
					{
						$this->jslocation(base64_decode($this->spArgs('callback')));
					}else{
						$this->jslocation(spUrl('main','index'));
					}

				}else{
					$err = $userobj->spVerifier($this->spArgs());
					foreach($err as $d){$errs[] = $d;}
					$this->errmsg = $errs[0][0];

				}
			}
			$this->callback = $this->spArgs('callback');
			$this->time = time();
			$this->email = $_COOKIE['unames'];
			$this->display('login.html');
		}

		/*用户退出*/
		public function logout()
		{
			$_SESSION = array();
			session_destroy();
			if($this->spArgs('callback'))
			{
				$this->jslocation(base64_decode($this->spArgs('callback')));
			}else{
				$this->jslocation(spUrl('main','index'));
			}
		}

		/*用户注册*/
		public function reg()
		{
			$this->time = time();
			if($this->spArgs('doing'))
			{
				$userobj = spClass('member');

				if($this->yb['regCodeSwitch'] != 'close') //如果开启
				{
					$userobj->verifier = $userobj->verifier_reg;
				}else{
					$userobj->verifier = $userobj->verifier_openConnect_Reg;
				}

				if( false == $userobj->spVerifier($this->spArgs()) ){
					$userobj->userReg($this->spArgs());
					if($this->spArgs('callback'))
					{
						$this->jslocation(base64_decode($this->spArgs('callback')));
					}else{
						$this->jslocation(spUrl('main','index'));
					}
				}else{
					$err = $userobj->spVerifier($this->spArgs());
					foreach($err as $d){$errs[] = $d;}
					$this->errmsg = $errs[0][0];
				}
			}
			$this->callback = $this->spArgs('callback');
			$this->display('reg_new.html');
		}





		/*获取验证码*/
		public function vcode()
		{
			spClass('spVerifyCode')->display();
		}

	}
?>