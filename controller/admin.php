<?php
	/**
	 * 作者：peter
	 * 日期：2012-10-07
	 * 说明：admin
	 */
	class admin extends base {
		function __construct() {
			parent :: __construct();
			$this -> get = $this -> spArgs();
			if($_SESSION['admin'] != 1) {
				prient_jump(spUrl('main'));
			}
		}

		function index() {
			$os = explode(" ", php_uname());
			$this -> luomorsoft = $GLOBALS['LUOMOR'];
			$this -> server = $_SERVER;
			$this -> os = php_uname();
			$this -> postupload = ini_get('post_max_size');
			$this -> maxupload = ini_get('upload_max_filesize');
			$this -> version = phpversion();
			$this -> mysql = spClass('mBlog') -> showVersion();
			$this -> luomorsoftencode = base64_encode($GLOBALS['LUOMOR']['version']);

			if(!function_exists("gd_info")) {
				$this -> gd = '不支持,无法处理图像';
			}
			if(function_exists(gd_info)) {
				$gd = @gd_info();
				$this -> gd = $gd["GD Version"];
				$gd ? '&nbsp; 版本：' . $gd : '';
			}
			$this -> curr_index = ' id="current"';
			$this -> display('admin/index.html');
		}

		function system() {
			if($this -> spArgs('submit')) {
				spClass('site_setting') -> saveConfig($this -> spArgs('val'));
				$this -> jump(spUrl('admin', 'system', array('ac' => 'ok')));
			}

			if($this -> spArgs('testSendMail')) {
				spClass('notice') -> sendMailTest();
				exit('<hr/>请确保您打开了邮件发送开关，测试邮件才会发送。开启邮件DEBUG模式会看到详细的发送过程。如果发送成功请关闭。');
			}

			$this -> conf = spClass('site_setting') -> getConfig();
			$this -> curr_system = ' id="current"';
			$this -> display('admin/system.html');
		}

		function blog() {
			if($this -> spArgs('submit')) {
				$title = $this -> spArgs('title');
				$niname = $this -> spArgs('niname');
				$where = "title like '%$title%'";
				if($niname) {
					$where .= " and uid = '$niname'";
				}
			} else {
				$where = "`open` != '-1'";
			}
			$this -> blog = spClass('mBlog') -> spLinker() -> spPager($this -> spArgs('page', 1), 20) -> findAll($where, 'bid desc');
			$this -> pager = spClass('mBlog') -> spPager() -> pagerHtml('admin', 'blog', array('title' => $title, 'niname' => $niname, 'submit' => $this -> spArgs('submit')));

			$this -> curr_blog = ' id="current"';
			$this -> display('admin/blog.html');
		}

		function user() {
			if($this -> spArgs('mod') == 'info') {
				$this -> info = spClass('member') -> find(array('uid' => $this -> spArgs('uid')));
				$this -> display('admin/user_info.html');
				exit;
			}

			if($this -> spArgs('lockuser')) {
				spClass('mBlog') -> lockUser($this -> spArgs('lockuser'));
			}
			if($this -> spArgs('resetpwd')) {
				if(spClass('mBlog') -> resetPwd($this -> spArgs('resetpwd'), $this -> spArgs('pwd'))) {
					exit('ok');
				}
			}
			if($this -> spArgs('where')) {
				$name = $this->spArgs('where');
				$where = " uid = '$name' or email = '$name' or domain = '$name'";
			} else {
				$where = '';
			}

			$this -> user = spClass('member') -> spLinker() -> spPager($this -> spArgs('page', 1), 20) -> findAll($where, 'uid desc');
			$this -> pager = spClass('member') -> spPager() -> pagerHtml('admin', 'user' );
			$this -> countuser = spClass('member') -> findCount();
			$this -> curr_user = ' id="current"';
			$this -> display('admin/user.html');
		}

		function tag() {
			if($this -> spArgs('syscate')) {
				spClass('category') -> saveCate($this -> spArgs());
				$this -> jump(spUrl('admin', 'tag', array('ac' => 'ok')));
			}
			if($this -> spArgs('sysadd')) {
				spClass('category') -> create(array('sort' => $this -> spArgs('sort'), 'catename' => $this -> spArgs('cname')));
				$this -> jump(spUrl('admin', 'tag', array('ac' => 'ok')));
			}
			if($this -> spArgs('usercate')) {
				spClass('tags') -> saveCate( $this -> spArgs() );
				$this -> jump(spUrl('admin', 'tag', array('ac' => 'ok')));
			}
			if($this -> spArgs('sysdel')) {
				spClass('category') -> deleteByPk($this -> spArgs('sysdel'));
			}
			if($this -> spArgs('userdel')) {
				spClass('tags') -> deleteByPk($this -> spArgs('userdel'));
			}

			$this -> systag = spClass('category') -> spPager($this -> spArgs('page', 1), 10) -> findAll($where, 'sort  asc'); //系统tag
			$this -> systagpager = spClass('category') -> spPager() -> pagerHtml('admin', 'tag');
			if($this -> spArgs('showuser')) {
				$this -> usrtag = spClass('tags') -> spLinker() -> spPager($this -> spArgs('page', 1), 20) -> findAll($where, 'tid  asc'); //系统tag
				$this -> usrtagpage = spClass('tags') -> spPager() -> pagerHtml('admin', 'tag', array('showuser' => 'yes'));
			}
			$this -> curr_blog = ' id="current"';
			$this -> display('admin/tag.html');
		}

		function theme() {
			if($this -> spArgs('m') == 'info') {
				if($this -> spArgs('submit')) {
					spClass('skins') -> update(array('id' => $this -> spArgs('id')), $this -> spArgs());
					$this -> success('保存成功', spUrl('admin', 'theme'));
				}
				$this -> skin = spClass('skins') -> find(array('id' => $this -> spArgs('id')));
				$this -> display('admin/theme_info.html');
				exit;
			}

			if($this -> spArgs('m') == 'install') {
				$name = $this -> spArgs('installdir');
				$dir = APP_PATH . '/tpl/theme/' . $name;
				if(!is_dir($dir) || $name =='') {
					$this -> error('请指定正确的主题安装目录!');
				}
				$result = spClass('skins') -> find(array('skindir' => $name));
				if(is_array($result)) {
					$this -> error('该目录已被安装,重新安装请先卸载');
				} else {
					spClass('skins') -> create(array('skindir' => $name));
					$this -> error('主题已经安装，请编辑详情');
				}
			}
			if($this -> spArgs('m') == 'uninstall') {
				spClass('skins') -> delete(array('id' => $this -> spArgs('id')));
				$this -> error('主题已经卸载，您可以删除该主题文件夹');
			}

			if($this -> spArgs('filter')) {
				if($this -> spArgs('filter') == 'close') {
					$where = array('open' => 0);
					$page = array('filter' => 'close');
				}
				if($this -> spArgs('filter') == 'open') {
					$where = "exclusive = 0 and open = 1";
					$page = array('filter' => 'open');
				}
				if($this -> spArgs('filter') == 'exclusive') {
					$where = "exclusive != 0";
					$page = array('filter' => 'exclusive');
				}
			} else {
				$where = '';
			}

			$this -> skins = spClass('skins') -> spPager($this -> spArgs('page', 1), 3) -> findAll($where, 'id desc');
			if($page) {
				$this -> pager = spClass('skins') -> spPager() -> pagerHtml('admin', 'theme', $page);
			} else {
				$this -> pager = spClass('skins') -> spPager() -> pagerHtml('admin', 'theme');
			}

			$this -> curr_theme = ' id="current"';
			$this -> display('admin/theme.html');
		}

		function database() {
			//初始化数据库处理
			$db = spClass('DbBackup', array(0 => $GLOBALS['G_SP']['db']));
			$this -> table = $db -> showAllTable($this -> spArgs('chk'));
			if($this -> spArgs('dbac') == 'op') {
				$db -> optimizeTable($this -> spArgs('tabl'));
				exit;
			}
			if($this -> spArgs('dbac') == 'rep') {
				$db -> repairTable($this -> spArgs('tabl'));
				exit;
			}
			if($this -> spArgs('outab')) {
				$db -> outTable($this -> spArgs('outab'));
				exit;
			}
			if($this -> spArgs('ouall')) {
				$db -> outAllData();
				exit;
			}
			
			$this -> curr_database = ' id="current"';
			$this -> display('admin/database.html');
		}

		function clearcache() {
			spClass('m_access_cache') -> delete();
			$this -> success('已经清除所有缓存');
		}
	}