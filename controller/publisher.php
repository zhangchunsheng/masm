<?php
	/**
	 * 作者：peter
	 * 日期：2012-09-25
	 * 说明：发布博客
	 */
	class publisher extends base {
		function __construct() {
			parent::__construct();
			if(!islogin()) {
				prient_jump(spUrl('main'));
			}
		}

		function index() {
			prient_jump(spUrl('main'));
		}
		
		public function map() {
			if($this -> luomor["allowPublishfoodmap"] == 0) {
				$this -> error("亲，该功能正在维护");//没有开放该功能
			}
			$this -> loadmap = "yes";
			$livecity_code = $_SESSION["livecity_code"];
			if(empty($livecity_code))
				$livecity_code = "001001";
			$this -> getCreateBid();
			$this -> attach();
			$this -> myTagUsually();
			$this -> city = spClass("city") -> findBy("code", $livecity_code);
			$this -> display("publish_map.html");
		}

		//发布文字模型
		public function text() {
			if($this -> luomor['allowPublishtext'] == 0) {
				$this -> error('亲，该功能正在维护');
			}
			$this -> getCreateBid();
			$this -> attach();
			$this -> myTagUsually(); //我的常用标签
			$this -> display('publish_text.html');
		}

		//发布音乐模型
		public function music() {
			if($this -> luomor['allowPublishmusic'] == 0) {
				$this -> error('亲，该功能正在维护');
			}
			$this -> getCreateBid();
			$this -> attach();
			$this -> myTagUsually(); //我的常用标签
			$this -> display('publish_music.html');
		}

		//发布图片模型
		public function image() {
			if($this -> luomor['allowPublishimg'] == 0) {
				$this -> error('亲，该功能正在维护');
			}
			$this -> getCreateBid();
			$this -> attach();
			$this -> myTagUsually(); //我的常用标签
			$this -> display('publish_image.html');
		}

		//发布视频模型/
		public function video() {
			if($this -> luomor['allowPublishvideo'] == 0) {
				$this -> error('亲，该功能正在维护');
			}
			$this -> getCreateBid();
			$this -> attach();
			$this -> myTagUsually(); //我的常用标签
			$this -> display('publish_video.html');
		}

		public function edit() {
			$this -> getCreateBid();
			$this -> attach(false);
			$this -> myTagUsually(); //我的常用标签
			$this -> __parse_mytag($this -> blog['tag']); //如果是编辑的则推送edit时的标签
			$this -> body = split_attribute($this -> blog['body']); //获得属性和正文信息

			if($this -> blog["type"] == 1) { //地图
				$city = array();
				$this -> loadmap = "yes";
				$city["latitude"] = $this -> blog["latitude"];
				$city["longitude"] = $this -> blog["longitude"];
				$this -> city = $city;
				$this -> display("publish_map.html");
			} elseif($this -> blog['type'] == 2) { //文字
				$this -> display('publish_text.html');
			} elseif($this -> blog['type'] == 3) { //音乐
				$this -> display('publish_music.html');
			} elseif($this -> blog['type'] == 4) { //照片
				$this -> display('publish_image.html');
			} elseif($this -> blog['type'] == 5) { //视频
				$this -> display('publish_video.html');
			} else {
				exit("亲，没有这个功能");//未知数据流
			}
		}

		//发布或更新轻博客
		public function post() {
			if($_SESSION['tempid'] == 0) {
				$this -> error("亲，系统压力太大出错了");//丢失临时id
			}
			$one = spClass("mblog") -> findBy('bid', $_SESSION['tempid']);
			$cityCode = "";
			$cityName = "";
			$address_type = "";
			$address = "";
			if($this -> spArgs("blog-types") == 1) {
				$positionInfo = getPositionInfo($this -> spArgs("pb-text-latitude"), $this -> spArgs("pb-text-longitude"));
				$cityInfo = spClass("city") -> findByGoogleName($positionInfo);
				$cityCode = $cityInfo["code"];
				$cityName = $cityInfo["name"];
				$address_type = $positionInfo -> addressType;
				$address = $positionInfo -> address;
			}
			
			if($this -> spArgs('blog-types') == 2) {
				$this -> _localImgParse($this -> spArgs('textarea')); //处理图像资源
				if($this -> spArgs('blog-attach') != '') {
					$bodypre = '[attribute]' . serialize($this -> spArgs('blog-attach')) . '[/attribute]';
				} //加入属性关键字
			}
			
			//发布音乐或视频
			if($this -> spArgs('blog-types') == 3 || $this -> spArgs('blog-types') == 5) {
				if($this -> spArgs('useedit') != 1) {//如果有特殊则处理
					if($this -> spArgs('localmusicfid') == '' && $this -> spArgs('urlmedia') == '') {
						exit('未完成的内容');
					}
					if($this -> spArgs('urlmedia')) {//进行音乐列表的处理
						$music = $this -> __loadMediaString($this -> spArgs('urlmedia'));
						$music_count = count($music); //总共几首音乐
					}
					$bodypre = '[attribute]' . serialize($music) . '[/attribute]';//加入属性关键字
				}
			}

			//发布图片
			if($this -> spArgs('blog-types') == 4) {
				$image = $this -> _imagemodeparse($this -> spArgs('localimg')); //处理图片
				if(is_array($image)) {
					$bodypre = '[attribute]' . serialize($image) . '[/attribute]';
				} //加入属性关键字
			}
			
			$rows = array(
				'title' => strip_tags($this -> spArgs('pb-text-title')),
				'type' => $this -> spArgs('blog-types'),
				'top' => $this -> spArgs('pb-top-post', 0),
				'tag' => substr((strip_tags($this -> spArgs('blog-tags'))), 0, -1),
				'attribute' => $attribute,
				'body' => $bodypre . strreplaces($this -> spArgs('textarea')),
				'cityCode' => $cityCode,
				'cityName' => $cityName,
				'latitude' => $this -> spArgs('pb-text-latitude'),
				'longitude' => $this -> spArgs('pb-text-longitude'),
				'address_type' => $address_type,
				'address' => $address,
				'open' => $this -> spArgs('blog-open'),
				'noreply' => $this -> spArgs('pb-nowrite-post', 0),
				'open' => $this -> spArgs('post-privacy-select'),
				'time' => time()
			);
			$this -> tagCreate(trim($this -> spArgs('blog-tags'))); //处理TAG
			if($one['open'] == -1) {
				spClass('member') -> incrField(array('uid' => $_SESSION['uid']), 'num');
			} //如果不是编辑的话就加
			spClass("mblog") -> update(array('bid' => $_SESSION['tempid']), $rows,$_SESSION['uid']);

			$this -> postToConnect($this -> spArgs());
			$_SESSION['tempid'] = NULL;
			prient_jump(spUrl('main'));
		}
		
		public function uploadimg() {
			if(isset($_SESSION['tempid'])) {
				$upfile = spClass("uploadFile");
				$upfile -> set_filesize($this -> luomor['addimg_upsize']); //改为后台配置
				$upfile -> set_filetypes('jpg|png|jpge|bmp');
				$upfile -> set_diydir($_SESSION['tempid']);
				$files = $upfile -> fileupload();
				$farray = json_decode($files);
				echo $files;
			} else {
				echo json_encode(array('err' => '亲，系统丢失参数', 'msg' => ''));
			}
		}

		public function swfupload() {
			if(isset($_SESSION['tempid'])) {
				$upfile = spClass("uploadFile");
				$upfile -> set_filesize($this -> luomor['addimg_upsize']); //改为后台配置
				$upfile -> set_filetypes('jpg|png|jpge|bmp');
				$upfile -> set_diydir($_SESSION['tempid']);
				$files = $upfile -> fileupload();
				$farray = json_decode($files);
				echo $files;
			} else {
				echo json_encode(array('err' => '亲，系统丢失参数', 'msg' => ''));
			}
		}

		//上传媒体
		public function uploadmedia() {
			if($this -> luomor['addmusic_up_switch'] == 0) {
				$this -> error('没有启用媒体上传', spUrl('main'));
			}
			if(isset($_SESSION['tempid'])) {
				$upfile = spClass("uploadFile");
				$upfile -> set_filesize($this -> luomor['addmusic_upsize']); //改为后台配置
				$upfile -> set_filetypes('mp3|wma|mid');
				$upfile -> set_diydir($_SESSION['tempid']);
				$files = $upfile -> fileupload();
				$farray = json_decode($files);
				echo $files;
			} else {
				$this -> error('亲，系统丢失参数', spUrl('main'));
			}
		}

		//附件管理器
		public function attach($del = true) {
			if($this -> tempid) {
				if($del) {
					foreach($this -> attach as $d) {
						spClass('attach') -> delById($d['id'], $_SESSION['uid']);
					}
				}

				$rs = spClass('attach') -> findAll(array('bid' => $this -> tempid), 'time desc');
				if($rs[0]['uid'] == $_SESSION['uid'] || $_SESSION['admin'] == 1) {
					$this -> attach = $rs;
				}
			} else {
				$this -> attach = spClass('attach') -> spPager($this -> spArgs('page', 1), 10) -> findAll(array('uid' => $_SESSION['uid']));
				$this -> pager = spClass('attach') -> spPager() -> pagerHtml('mblog', 'attach');
			}
		}

		/**
		 * 删除日志以及附件
		 * 没有依赖db库
		 * 7月12日测试完毕 如果日志没有附件不会自动删除那个博客的文件夹
		 */
		public function del() {
			$blog = spClass("mblog") -> findBy('bid', $this -> spArgs('id'));
			if($blog['uid'] == $_SESSION['uid'] || $_SESSION['admin'] == 1) {
				$attach = spClass("attach") -> findAll(array('bid' => $blog['bid']), '', 'path');
				if($attach != '') {
					$path = pathinfo($attach[0]['path']);
					if($this -> _deldir($path['dirname'])) {
						spClass("attach") -> delete(array('bid' => $blog['bid']));
					}
				}
				spClass("mblog") -> deleteByPk($blog['bid']); //删除日志
				spClass('member') -> decrField(array('uid' => $blog['uid']), 'num'); //计数减一
				//删除喜欢，删除评论。
				spClass('reply') -> delete(array('bid' => $blog['bid']));
				spClass('likes') -> delete(array('bid' => $blog['bid']));
				exit('ok');
			} else {
				exit('删除失败,无权限或不存在该档案');
			}
		}

		//删除某个媒体
		public function delattach() {
			$rs = spClass('attach') -> delById($this -> spArgs('id'), $_SESSION['uid']);
			exit('ok');
		}
		
		//解析多媒体地址
		public function media() {
			$type = $this -> spArgs("type");
			$result = spClass('urlParse') -> setMediaDescription($this -> spArgs("url"), $this -> spArgs('desc'), $type);
			echo json_encode($result);
		}
		
		//处理发布图片模型
		private function _imagemodeparse($image) {
			$data = '';
			$i = 1;
			if(is_array($image)) {
				foreach($image as $k => $d) {
					if($i > $this -> luomor['addimg_count']) {//如果超过后台设定张数则把超过的删除
						spClass('attach') -> delById($k);
					} else {
						$rs = spClass('attach') -> find(array('id' => $k), '', 'id,path');
						if(is_array($rs)) {//如果记录验证成功
							$dir = pathinfo($rs['path']);
							$thum = $dir['dirname'] . '/t_' . $dir['basename'];
							if($d != '图片说明可选') {
								$desc = $d;
							} else {
								$desc="";
							}
							if(file_exists($thum)) {
								$data[] = array('url' => $thum, 'desc' => $desc);
							} else {
								$data[] = array('url' => $rs['path'], 'desc' => $desc);
							}
							spClass('attach') -> update(array('id' => $k), array('blogdesc' => $desc));
						}
					}
					$i++;
				}

				$datas['img'] = $data;
				$datas['count'] = count($data);
				return $datas;
			}
			return '';
		}

		//发布到其他媒体通过内部api
		private function postToConnect($args) {
			if($args['openconnect']['WEIB']) {
				import('sinaConnect.php');
				$sina = new sinaConnect();
				$sina -> init($this -> luomor['openlogin_weib_appid'], $this -> luomor['openlogin_weib_appkey'], $this -> luomor['openlogin_weib_callback']);
				$keys = $_SESSION['openconnect']['WEIB'];
				$c = new WeiboClient($this -> luomor['openlogin_weib_appid'], $this -> luomor['openlogin_weib_appkey'], $keys['token'], $keys['secret']);

				if($args['filedata']) {
					$title = $args['pb-text-title'];
					$pic = stripslashes($args['filedata']);
					$rs = $c  -> upload( $title ,$pic); //update
				} else {
					$title = strip_tags($args['pb-text-title']);
					$subject = '"' . strip_tags($args['textarea']) . '"';
					$rs = $c  -> update($title . $subject); //update
				}
			}
		}
		
		//处理TAG
		private function tagCreate($tag) {
			$tag_array = explode(',', substr($tag, 0, -1)); //用逗号分隔,避免空格分隔出断章
			if(is_array($tag_array)) {
				foreach($tag_array as $d) {
					if($d != '') {
						spClass('tags') -> tagCreate($d, $_SESSION['uid']);
					}
				}
			}
		}

		//获取我的常用tag
		private function myTagUsually($num = 10) {
			$this -> myTagUsually = spClass('tags') -> spCache(3600) -> findAll(array('uid' => $_SESSION['uid']), 'num desc', '', $num);
		}
		
		//获取一个可用的临时ID
		private function getCreateBid() {
			$result = spClass("mblog") -> find(array('uid' => $_SESSION['uid'], 'open' => -1), '', 'bid');
			if($result == '') {
				$_SESSION['tempid'] = spClass("mblog") -> create(array('title' => '', 'open' => -1, 'body' => '', 'abstract' => '', 'tag' => '', 'uid' => $_SESSION['uid']));
				$this -> tempid = $_SESSION['tempid'];
			} else {
				$_SESSION['tempid'] = $result['bid'];
				$this -> tempid = $_SESSION['tempid'];
			}

			if($this -> spArgs('id') != '') {
				$ras = spClass("mblog") -> findBy('bid', $this -> spArgs('id'));
				if($ras['uid'] == $_SESSION['uid'] || $_SESSION['admin'] == 1) {
					$bid = $ras['bid'];
					$_SESSION['tempid'] = $bid;
					$this -> tempid = $bid;
					$this -> times = $ras['time'];
					$this -> blog = $ras;
				} else {
					$this -> error('您没有权限编辑', spUrl('main', 'index'));
				}
			}
		}

		/**
		 * 进行发布音乐的处理
		 * id  附件id
		 * desc 描述
		 * 需要判断是否归所属人
		 * 如果此id没查出来则返回false 接到的方法要删除这个id
		 */
		private function _localMediaParse($id, $desc) {
			$result = spClass("attach") -> findBy($id, $_SESSION['uid']); //检出文件是否存在
			if($result['uid'] == $_SESSION['uid']) {//判断是否是我发的
				if($desc[$d] != '描述') {
					spClass("attach") -> update(array('id' => $id), array('blogdesc' => $desc));
				}//如果有描述则更新描述
				return true;
			} else {
				return false;
			}
		}

		//获得编辑器实际使用的图片
		private function _localImgParse($body) {
			preg_match_all( "/<img.[^>]*?(src|SRC)=\"[\"|'| ]{0,}([^>]*\\.(gif|jpg|jpeg|bmp|png))([\"|'|\\s]).*[\\/]?>/isU", stripslashes($body) , $info);
			$info = array_unique($info[2]);

			$str = '';
			if(is_array($info)) {
				foreach($info as $d) {
					if (substr($d,0,4) != 'http') {//非本地连接不管
						if(substr($d,0,7) == 'attachs') {//如果不是 attachs开头不管
							$path = pathinfo($d);
							if(substr($path['basename'], 0, 2) == 't_') {
								$d = $path['dirname'] . '/' . substr($path['basename'], 2, 99);
							}//如果是缩略图
						}
					}
					$str .= '\'' . $d . '\',';
				}

				$str = substr($str, 0, -1); //去掉逗号
				if($str) {
					$where = "`path` not in ($str) and";
				} //如果存在 就加限制
				$result = spClass('attach') -> findAll("$where  uid = {$_SESSION['uid']} and bid = {$_SESSION['tempid']}", '', 'id,path'); //获取到编辑器没有使用的

				if(is_array($result)) {
					foreach($result as $d) {//全部搞定
						spClass('attach') -> delById($d['id'], $_SESSION['uid']);
					}
				}
			}
		}

		/**
		 * 处理发布的字符串
		 * 发布时的文件如果小于上传的媒体文件，则本函数会自动清理
		 */
		private function __loadMediaString($strings) {
			$music = substr($strings, 0, -6);
			$music = explode('LUOMOR', $music); //分隔
			$locadata = ''; //本博客媒体数量
			$compdata = array(); //上传使用了几个
			if(is_array($music)) {
				foreach($music as $d) {
					$rs = explode('|', $d);
					if($rs[0] == 'local') {
						$compdata[] = $rs[2];
						if($this -> _localMediaParse($rs[2], $rs[3])) {
							$data[] = array('type' => 'local', 'img' => $rs[1], 'pid' => $rs[2], 'desc' => $rs[3]);
						} //验证成功或修改成功
					} else {
						if($rs[0] == "xiami") {
							$data[] = array('type' => $rs[0], 'img' => $rs[1], 'pid' => $rs[2], 'desc' => $rs[3], 'url' => $rs[4], 'albumName' => $rs[5],'albumUrl' => $rs[6], 'singerName' => $rs[7], 'singerUrl' => $rs[8]);
						} elseif($rs[0] == "sina") {
							$data[] = array('type' => $rs[0], 'img' => $rs[1], 'pid' => $rs[2], 'desc' => $rs[3], 'url' => $rs[4], 'swfUrl' => $rs[5]);
						} else {
							$data[] = array('type' => $rs[0], 'img' => $rs[1], 'pid' => $rs[2], 'desc' => $rs[3], 'url' => $rs[4]);
						}
					}
				}

				//检查上传媒体的使用情况
				$result = spClass('attach') -> findAll(array('bid' => $_SESSION['tempid'], 'uid' => $_SESSION['uid']), '', 'id,bid,mime'); //锁定用户文件,防止提交非自己的id以至于被删除
				if(is_array($result)) {
					foreach($result as $d) {//整理本地文件
						if($d['mime'] == 'mp3' || $d['mime'] == 'wma' || $d['mime'] == 'mid' ) {//判断只有媒体文件才被处理
							$locadata[] = $d['id'];
						}
					}
					//计算差集,删除编辑器未使用媒体
					$compute = array_diff($locadata, $compdata);
					if(is_array($compute)) {
						foreach($compute as $d) {
							spClass('attach') -> delById($d, $_SESSION['uid']);
						}
					}
				}
			}
			$data = $this -> assoc_unique($data, 'pid'); //数组去重
			return $data;
		}

		//数组去重
		private function assoc_unique($arr, $key) {
			$tmp_arr = array();
			foreach($arr as $k => $v) {
				if(in_array($v[$key], $tmp_arr)) {
					unset($arr[$k]);
				} else {
					$tmp_arr[] = $v[$key];
				}
			}
			sort($arr);
			return $arr;
		}

		//删除文件夹所有内容
		private function _deldir($dir) {
			if($dir == '') {
				return false;
			}
			$dh = opendir($dir);
			while($file = readdir($dh)) {
				if($file != "." && $file != "..") {
					$fullpath = APP_PATH . '/' . $dir . "/" . $file;
					@unlink($fullpath);
				}
			}
			closedir($dh);
			if(rmdir(APP_PATH . '/' . $dir)) {
				return true;
			} else {
				return false;
			}
			exit;
		}
	}
?>