<?php
	/**
	 * 作者：peter
	 * 日期：2012-09-25
	 * 说明：自定义函数
	 */
	function randstr($len = 6) {
		$chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789-@#~';
		mt_srand((double)microtime() * 1000000 * getmypid());
		$passWord = '';
		while(strlen($passWord) < $len)
			$passWord .= substr($chars, (mt_rand() % strlen($chars)), 1);
		return $passWord;
	}

	function islogin() {
		if(isset($_SESSION['uid']) && $_SESSION['uid'] != 0) {
			return true;
		} else {
			return false;
		}
	}

	function prient_jump($x) {
		exit( '<script language="javascript">top.location="' . $x . '";</script>');
	}

	/**
	 * 处理domain uid bid之间的关系
	 * 去用户首页的链接
	 */
	function goUserHome($params) {
		$domain = $params['domain']; //判断是否存在domain
		$uid = $params['uid'];     //判断是否存在uid

		if($domain != '' && $domain !='home') {
			return spUrl('userblog', 'index', array('domain' => $domain));
		} else {
			return spUrl('userblog', 'index', array('domain' => 'home', 'uid' => $uid));
		}
	}
	spAddViewFunction('goUserHome', 'goUserHome');

	/**
	 * 用户访问博客正文的链接
	 * bid domain uid
	 */
	function goUserBlog($params) {
		$bid = $params['bid']; //判断是否存在domain
		$domain = $params['domain']; //判断是否存在domain
		$uid = $params['uid'];     //判断是否存在uid
		if($domain && $domain != 'home') {
			return spUrl('userblog', 'show', array('bid' => $bid, 'domain' => $domain));
		}
		if($uid) {
			return spUrl('userblog', 'show', array('bid' => $bid, 'domain' => 'home', 'uid' => $uid));
		}
		if($bid) {
			return spUrl('userblog', 'show', array('bid' => $bid));
		}
	}
	spAddViewFunction('goUserBlog', 'goUserBlog');


	function feeds($params) {
		$site_uri = trim(dirname($GLOBALS['G_SP']['url']["url_path_base"]), "\/\\");
		if($site_uri == '') {
			$site_uri = 'http://' . $_SERVER["HTTP_HOST"];
		} else{
			$site_uri = 'http://' . $_SERVER["HTTP_HOST"] . '/' . $site_uri;
		}

		//处理基本数据
		$bid = $params['bid'];
		$d = $params['item'];
		$type = $params['type'];   //模型 类型
		$limit = $params['limit']; //feed 条数
		$show = $params['showmedia']; //是否展开媒体
		$readall = $params['readall'];  //是否显示阅读全文文字
		$d = split_attribute($d);
		$content = $d['content'];
		$attr = $d['attr'];

		$more = 0;

		if($type == 1) { //文字
			//只有limit不显示全部的时候才显示
			if($limit == 'all') {
				echo $content;
			} else {
				if($attr != '') {
					if(file_exists($attr)) {
						echo '<img src="' . $site_uri . '/' . $attr . '" alt=""/>';
						$outimg = 1;
					} //是否有代表图封面
				}

				if(utf8_strlen($content) > 700) {
					if(!$outimg) {
						$content = utf8_substr(strip_tags($content, '<p><ul><li><br><img>'), 0, 700);
					} else {
						$content = utf8_substr(strip_tags($content, '<p><ul><li><br>'), 0, 700);
					}
					$more = 1;
				} else {
					if(!$outimg) {
						$content = strip_tags($content, '<p><ul><li><br><img>');
					} else {
						$content = strip_tags($content, '<p><ul><li><br>');
					}
				}
				echo $content;
			}
		} elseif($type == 3) {//图片
			if($limit == 'all') {//如果显示全部
				foreach($attr['img'] as $d) {
					echo '<a class="highslide" href="' . getBigImg($d['url']) . '" onclick="return hs.expand(this)"><img src="' . $site_uri . '/' . $d['url'] . '" class="feedimg" alt="' . $d['desc'] . '"/></a><p>' . $d['desc'] . '</p>';
				}
				echo $content;
			} else {
				if($attr['count'] > $limit) {
					$attr['img'] = arrayPage($attr['img'], $limit);
					$more = 1;
				} //不然只显示4个
				$img = '<div class="highslide-gallery">';
				foreach($attr['img'] as $d) {
					$img .= '<a class="highslide" href="' . $site_uri . '/' . getBigImg($d['url']) . '" onclick="return hs.expand(this)"><img src="' . $site_uri . '/' . $d['url'] . '"  alt="' . $d['desc'] . '" class="feedimg"/></a>';
				}
				$img .= '</div>';
				
				if(utf8_strlen($content) > 700) {
					$content = utf8_substr(strip_tags($content, '<p><ul><li><br><img>'), 0, 700);
					$more = 1;
				} else {
					$content = strip_tags($content, '<p><ul><li><br><img>');
				}
				echo $img . $content;
				if($more == 1) {
					echo '<p>共' . $attr['count'] . '张图</p>';
				}
			}
		} elseif($type == 2 || $type == 4) {//如果是音乐
			if($limit != 'all') {//如果不显示全部
				$count = count($attr);
				if($count > $limit) {
					$more = 1;
					$attr = arrayPage($attr, $limit);
				}
				if(utf8_strlen($content) > 700) {//如果内容超过700字则标记
					$content = utf8_substr(strip_tags($content, '<p><ul><li><br><img>'), 0, 700);
					$more = 1;
				} else {
					$content = strip_tags($content, '<p><ul><li><br><img>');
				}
			}
			
			foreach($attr as $rs) {
				if($rs['type'] == 'youku') {
					$url = 'http://player.youku.com/player.php/sid/' . $rs['pid'] . '/v.swf';
					if($show == 1) {
						echo '<p> ' . $rs['desc'] . '</p>' . _getSwfplayer($url);
					} else {
						echo '<div class="player">
								<a href="javascript:;" onclick="OMP(\'' . $url . '\', this)">
									<img src="' . $rs['img'] . '" class="img" alt=""/>
									<img src="' . $site_uri . '/tpl/images/videoplay.gif" class="playbtn" alt="" />
								</a>
								<div class="area">
									<p>
										<a href="javascript:;;" onclick="LMP(this)">收起</a> ' . $rs['desc'] . '
									</p>
									<div class="playbox"></div>
								</div>
							</div>';
					}
				} elseif($rs['type'] == 'sina') {
					$pid = str_replace('-', '_', $rs['pid']);
					$url = 'http://you.video.sina.com.cn/api/sinawebApi/outplayrefer.php/vid=' . $pid;
					if($show == 1) {
						echo '<p> ' . $rs['desc'] . '</p>' . _getSwfplayer($url);
					} else {
						echo '<div class="player">
								<a href="javascript:;" onclick="OMP(\'' . $url . '\', this)">
									<img src="' . $rs['img'] . '" class="img" alt=""/>
									<img src="' . $site_uri . '/tpl/images/videoplay.gif" class="playbtn" alt="" />
								</a>
								<div class="area">
									<p>
										<a href="javascript:;;" onclick="LMP(this)">收起</a> ' . $rs['desc'] . '
									</p>
									<div class="playbox"></div>
								</div>
							</div>';
					}
				} elseif($rs['type'] == 'xiami') {
					echo '<div class="xiami clearfix">
							<img src="' . $rs['img'] . '" class="img" alt=""/>
							<div>' . $rs['desc'] . '</div>
							<embed src="http://www.xiami.com/widget/0_' . $rs['pid'] . '/singlePlayer.swf" type="application/x-shockwave-flash" width="257" height="33" wmode="transparent"></embed>
							<div class="clear"></div>
						</div>';
				} elseif($rs['type'] == 'flamesky') {
					echo '<div class="xiami clearfix">
							<img src="' . $rs['img'] . '" class="img" alt=""/>
							<div>' . $rs['desc'] . '</div>
							<embed src="http://www.flamesky.com/track/' . $rs['pid'] . '/ffp.swf" quality="high" width="310" height="45" allowScriptAccess="allways" wmode="transparent" type="application/x-shockwave-flash" />
						</div>';
				} elseif($rs['type'] == 'local') {
					if(stripos($rs['desc'],'mp3')) {
						$mtype = 1;
					} else {
						$mtype = 3;
					}
					echo '<div class="localmu">
							<p>' . $rs['desc'] . '</p>
							<div id="player_' . $bid . '"></div>
						</div>
						<script type="text/javascript">
							var flashvars = {
								name: "烙馍网 - 本地音乐",
								skin: "' . $site_uri . '/tpl/swf/skins/mini/blueplayer.zip",
								src: "' . spUrl('blog', 'getmusic', array('id' => $rs['pid'])) . '",
								type: ' . $mtype . ',
								label: "' . $rs['desc'] . '"
							};
							var html = CMP.create("cmp", "100%", "50", "' . $site_uri . '/tpl/swf/cmp.swf", flashvars);
							document.getElementById("player_' . $bid . '").innerHTML = html;
						</script>';
				} elseif($rs['type'] == 'weblink') {
					if(stripos($rs['pid'], 'mp3')) {
						$mtype = 1;
					} else {
						$mtype = 3;
					}
					echo '<div class="localmu">
							<p>' . $rs['desc'] . ' / <a href="' . $rs['pid'] . '" target="_blank">点击下载</a></p>
							<div id="player_' . $bid . '"></div>
						</div>
						<script type="text/javascript">
							var flashvars = {
								name: "烙馍网 - 网络音乐",
								skin: "' . $site_uri . '/tpl/swf/skins/mini/blueplayer.zip",
								src: "' . $rs['pid'] . '",
								type: ' . $mtype . ',
								label: "' . $rs['desc'] . '"
							};
							var html = CMP.create("cmp", "100%", "50", "' . $site_uri . '/tpl/swf/cmp.swf", flashvars);
							document.getElementById("player_' . $bid . '").innerHTML = html;
						</script>';
				} elseif($rs['type'] == 'yinyuetai') {
					if($show == 1) {
						echo '<p> ' . $rs['desc'] . '</p>' . _getSwfplayer($rs['pid'] . '?autostart=false');
					} else {
						echo '<div class="player">
								<a href="javascript:;" onclick="OMP(\'' . $rs['pid'] . '\', this)">
								<img src="' . $rs['img'] . '" class="img" onerror="this.src=\'tpl/image/videobg.png\'" width="127" height="95"/><img src="' . $site_uri . '/tpl/images/videoplay.gif" class="playbtn" /></a>
								<div class="area"> <p><a href="javascript:;;" onclick="LMP(this)">收起</a> ' . $rs['desc'] . '</p><div class="playbox"></div> </div>
							</div>';
					}
				} else {
					$url = $rs['pid'];
					if($show == 1) {
						echo '<p> ' . $rs['desc'] . '</p>' . _getSwfplayer($url);
					} else {
						echo '<div class="player">
								<a href="javascript:;" onclick="OMP(\'' . $url . '\',this)">
									<img src="' . $rs['img'] . '" class="img" alt="" />
									<img src="' . $site_uri . '/tpl/images/videoplay.gif" class="playbtn" alt="" />
								</a>
								<div class="area">
									<p>
										<a href="javascript:;;" onclick="LMP(this)">收起</a> ' . $rs['desc'] . '
									</p>
									<div class="playbox"></div>
								</div>
							</div>';
					}
				}
			}
			echo '<span class="feed_content">' . $content . '</span>';
		}
		
		if($more == 1) {
			echo '<p class="readMore">
					<a href="' . goUserBlog(array('bid' => $bid, 'domain' => $d['domain'], 'uid' => $uid)) . '">未完,阅读全文</a>
				</p>';
		}

		if($readall == 1 && $more != 1) {
			echo '<p class="showAll">
					<a href="' . goUserBlog(array('bid' => $bid, 'domain' => $d['domain'], 'uid' => $uid)) . '">查看全文</a>
				</p>';
		}
	}
	spAddViewFunction('feeds', 'feeds');
	
	//feed parse getswfplayer
	function _getSwfplayer($playUrl) {
		$player = '<object width="630" height="385">
						<param name="allowscriptaccess" value="always"></param>
						<param name="allowFullScreen" value="true"></param>
						<param name="wmode" value="Opaque"></param>
						<param name="movie" value="' . $playUrl . '"></param>
						<embed src="' . $playUrl . '" width="640" height="385" type="application/x-shockwave-flash"></embed>
				  </object>';
		return $player;
	}

	//处理回复的链接
	function reply_preg($params) {
		$msg = $params['msg'];
		preg_match("/\[at=(.*?)](.*?)\[\/at\]/i", $msg, $msag); //$msg[1]
		$msag = str_replace($msag[0], '<a href="' . goUserHome(array('uid' => $msag[1])) . '" target="_blank">' . $msag[2] . '</a>',$msg);
		echo $msag;
	}
	spAddViewFunction('reply_preg', 'reply_preg');

	//处理通知的链接
	function notice_preg($params) {
		$msg = $params['url'];
		$url = explode('|', $msg);
		if($url[0] == 'blog') {
			echo goUserBlog(array('bid' => $url[1]));
		}
		if($url[0] == 'user') {
			echo goUserHome(array('uid' => $url[1]));
		}
	}
	spAddViewFunction('notice_preg', 'notice_preg');

	//获取大图
	function getBigImg($img) {
		$imgs = str_replace('t_', '', $img);
		return $imgs;
	}

	function attach_insert($params) {
		$img = $params['file'];
		$name = $params['name'];
		$fid = $params['id'];
		$file = APP_PATH .'/'. $img;
		$newpath = pathinfo($img);
		$ext = strtolower($newpath['extension']);
		if($ext == 'jpg' || $ext == 'png' || $ext == 'gif' || $ext == 'bmp') {//如果是图像
			$newfile = APP_PATH . '/' . $newpath['dirname'] . '/' . 't_' . $newpath['filename'] . '.' . $ext;
			if(is_file($newfile)) {
				return 'iattachBigImg(\'' . $img . '|' . $newpath['dirname'] . '/' . 't_' . $newpath['filename'] . '.' . $ext . '\')';
			} else {
				return 'iattachImg(\'' . $img . '\')';
			}
		} elseif($ext == 'mp3' || $ext == 'wma') {
			return 'iattachMp3(' . $fid . ',\'' . $name . '\')';
		} else {
			return $ext . '|' . $name . '|' . $fid;
		}
	}
	spAddViewFunction('attach_insert', 'attach_insert');

	//发布图片的时候处理缩略图显示
	function thubimg($params) {
		$path = $params['path'];
		$path = pathinfo($path);
		$img = $path['dirname'] . '/' . 't_' . $path['filename'] . '.' . $path['extension'];
		if(file_exists($img)) {
			echo $img;
		} else {
			echo $params['path'];
		}
	}
	spAddViewFunction('thubimg', 'thubimg');
	
	//显示标签链接
	function tag($params) {
		$tag = $params['tag'];
		$c = 'blog';
		$a = 'tag';
		$tagarr = explode(',', $tag);
		$tagstr = '';
		if(is_array($tagarr)) {
			foreach($tagarr as $d) {
				$tagstr .= '<a href="' . spUrl($c, $a, array('tag' => $d)) . '" target="_blank">' . $d . '</a>';
			}
		}
		return $tagstr;
	}
	spAddViewFunction('tag', 'tag');
	
	//显示头像
	function avatar($params) {
		$uid = $params['uid'];
		$size = $params['size'];
		$time = $params['time'];
		if($time == 1) {
			return APP_NAME . 'avatar.php?uid=' . $uid . '&size=' . $size . '&random=' . time();
		} else {
			return APP_NAME . 'avatar.php?uid=' . $uid . '&size=' . $size;
		}
	}
	spAddViewFunction('avatar', 'avatar');


	//显示POST error
	function verifierMsg($params) {
		$config =  array(
			'defaultTag'        => 'span',   //默认提示的包围标签
			'defaultTagClass'   => 'nomail', //默认提示的标签附加class
			'errTag'            => 'span',       //错误的提示包围标签
			'errTagClass'       => 'err',  //错误的提示包围标签附加class
			'showType'          => 1,      //错误显示方式,1为单独调用,2为全部显示在某个地方
			'ShowAre'           => 'ul',    //全部显示的时候外筐
			'ShowAreClass'      => 'tab', //外矿css
			'ShowInner'         => 'li',     //内部循环
			'ShowInnerClass'    => '',     //内部循环
		);

		$set_array = $params['set'];
		$input = $params['in'];
		$msg = $params['msg'];
		$show = $params['showtype'];

		if($config['showType']  == 2 || $show == 2) {
			if(is_array($set_array)) {
				$str .= '<' . $config['ShowAre'] . ' class="' . $config['ShowAreClass'] . '">';
				foreach($set_array as $k => $v) {
					$str .= '<' . $config['ShowInner'] . ' class="' . $config['ShowInnerClass'] . '">' . $v[0] . '</' . $config['ShowInner'] . '>';
				}
				$str .= '</' . $config['ShowAre'] . '>';
				return $str;
			}
		} else {
			if(is_array($set_array)) {
				foreach($set_array as $k => $v) {
					if($k == $input) {
						return '<' . $config['errTag'] . ' class="' . $config['errTagClass'] . '">' . $v[0] . '</' . $config['errTag'] . '>';
					} else {
						return '<' . $config['defaultTag'] . ' class="' . $config['defaultTagClass'] . '">' . $msg . '</' . $config['defaultTag'] . '>';
					}
				}
			} else {
				return '<' . $config['defaultTag'] . ' class="' . $config['defaultTagClass'] . '">' . $msg . '</' . $config['defaultTag'] . '>';
			}
		}
	}
	spAddViewFunction('verifierMsg', 'verifierMsg');

	//计算热度百分比
	function checkHit($params) {
		$one = $params['max'];
		$two = $params['val'];
		if($tow == $one) {
			return '100';
		} elseif($two == 0) {
			return '1';
		} else {
			return ceil(($two / $one) * 100);
		}
	}
	spAddViewFunction('checkHit', 'checkHit');

	function ip2name($ip) {
		$url = "http://www.youdao.com/smartresult-xml/search.s?type=ip&q=" . $ip;
		$doc = new DOMDocument();
		$doc -> load($url);
		$smartresult = $doc -> getElementsByTagName("product");
		foreach($smartresult as $product) {
			$locations = $product -> getElementsByTagName("location");
			$location = $locations -> item(0) -> nodeValue;
		}

		if($location != "") {
			$local = explode(' ', $location);
			return $local[0];
		} else {
			return '火星';
		}
	}
	
	function getIP() {
		if(@$_SERVER["HTTP_X_FORWARDED_FOR"])
			$ip = $_SERVER["HTTP_X_FORWARDED_FOR"];
		elseif(@$_SERVER["HTTP_CLIENT_IP"])
			$ip = $_SERVER["HTTP_CLIENT_IP"];
		elseif(@$_SERVER["REMOTE_ADDR"])
			$ip = $_SERVER["REMOTE_ADDR"];
		elseif(@getenv("HTTP_X_FORWARDED_FOR"))
			$ip = getenv("HTTP_X_FORWARDED_FOR");
		elseif(@getenv("HTTP_CLIENT_IP"))
			$ip = getenv("HTTP_CLIENT_IP");
		elseif(@getenv("REMOTE_ADDR"))
			$ip = getenv("REMOTE_ADDR");
		else
			$ip = "127.0.0.1";
		return $ip;
	}
	
	//处理显示首页或推荐的博文类型feeds
	function feedshtml($blogs, $index = 1, $type = 'index') {
		$site_uri = trim(dirname($GLOBALS['G_SP']['url']["url_path_base"]), "\/\\");
		if($site_uri == '') {
			$site_uri = 'http://' . $_SERVER["HTTP_HOST"];
		} else {
			$site_uri = 'http://' . $_SERVER["HTTP_HOST"] . '/' . $site_uri;
		}
		$feeds = feedParse($blogs, $type);
		$i = 1;
		$msg = '';
		foreach($feeds as $d) {
			if($d['btype'] == 1) {
				$msg .= '<div class="box">
							<div class="textcontents">' . strip_tags($d['content']) . '</div>
							<span class="tag">' . _setTags('地图', $d['tag'], $index) . '</span>
							<a class="boxhover" href="' . goUserBlog(array('bid' => $d['bid'])) . '" target="_blank">
								<div class="img">
									<img src="' . avatar(array('uid' => $d['uid'], 'size' => 'middle')) . '" alt=""/>
								</div>
								<div class="p">来自:' . $d['username'] . '</div>
							</a>
						</div>';
			} elseif($d['btype'] == 2) {//文字
				$msg .= '<div class="box">
							<div class="textcontents">' . strip_tags($d['content']) . '</div>
							<span class="tag">' . _setTags('文字', $d['tag'], $index) . '</span>
							<a class="boxhover" href="' . goUserBlog(array('bid' => $d['bid'])) . '" target="_blank">
								<div class="img">
									<img src="' . avatar(array('uid' => $d['uid'], 'size' => 'middle')) . '" alt=""/>
								</div>
								<div class="p">来自:' . $d['username'] . '</div>
							</a>
						</div>';
			} elseif($d['btype'] == 3) {//音乐
				if($d['metype'] == 'local') {
					$d['img']  = '';
				}
				$msg .= '<div class="box">
							<img src="' . $d['img'] . '" width="180" alt=""/>
							<div class="mediadesc"> ' . $d['count'] . ' </div>
							<span class="tag">' . _setTags('音乐', $d['tag'], $index) . '</span>
							<div class="music" href="javascript:alert(1)">
								<img src="tpl/image/music.png" alt=""/>
							</div>' . $d['desc'] . '
							<a class="boxhover" href="' . goUserBlog(array('bid' => $d['bid'])) . '" target="_blank">
								<div class="img">
									<img src="' . avatar(array('uid' => $d['uid'], 'size' => 'middle')) . '" alt=""/>
								</div>
								<div class="p">来自:' . $d['username'] . '</div>
							</a>
						</div>';
			} elseif($d['btype'] == 4) {//照片
				$msg .= '<div class="box"  style="background:url(' . $d['img'] . ') no-repeat center top;">
							<div class="imgcount"> ' . $d['count'] . ' 张</div>
							<span class="tag">' . _setTags('照片', $d['tag'], $index) . '</span>
							<a class="boxhover" href="' . goUserBlog(array('bid' => $d['bid'])) . '" target="_blank">
								<div class="img">
									<img src="' . avatar(array('uid' => $d['uid'], 'size' => 'middle')) . '" alt=""/>
								</div>
								<div class="p">来自:' . $d['username'] . '</div>
							</a>
						</div>';
			} elseif($d['btype'] == 5) {//视频
				if($d['metype'] == 'local')
					$d['img']  = 'tpl/image/add/local.png';
				$msg .= '<div class="box">
							<img src="' . $d['img'] . '" width="180" alt=""/>
							<div class="mediadesc"> ' . $d['count'] . ' </div>
							<span class="tag">' . _setTags('视频', $d['tag'], $index) . '</span>
							<div class="video">
								<img src="' . $site_uri . '/tpl/images/videoplay.gif" alt=""/>
							</div>
							' . $d['desc'] . '
							<a class="boxhover" href="' . goUserBlog(array('bid' => $d['bid'])) . '" target="_blank">
								<div class="img">
									<img src="' . avatar(array('uid' => $d['uid'], 'size' => 'middle')) . '"alt="" />
								</div>
								<div class="p">来自:' . $d['username'] . '</div>
							</a>
						</div>';
			}
			$i++;
		}
		return $msg;
	}
	
	function _setTags($txt, $tag, $index = 1) {
		if($index) {
			return $txt;
		} else {
			$tag = explode(',', $tag);
			return $tag[0];
		}
	}

	function formatBytes($params) {
		$bytes = $params['size'];
		if($bytes >= 1073741824) {
			$bytes = round($bytes / 1073741824 * 100) / 100 . 'GB';
		} elseif($bytes >= 1048576) {
			$bytes = round($bytes / 1048576 * 100) / 100 . 'MB';
		} elseif($bytes >= 1024) {
			$bytes = round($bytes / 1024 * 100) / 100 . 'KB';
		} else {
			$bytes = $bytes . 'Bytes';
		}
		return $bytes;
	}
	spAddViewFunction('formatBytes', 'formatBytes');

	function themecustom($params) {
		$type = $params['type'];
		$data = $params['data'];
		$id = $params['id'];
		$forid = $params['lable'];
		$default = $params['default'];
		$formvalue = $_SESSION['customize']['config'];
		
		if($type == 'radio') {
			foreach($data as $k => $d) {
				if($forid == $default || $k == arrrayKeyByVelue($formvalue, $forid, $id, $default)) {
					echo '<input type="radio" name="' . $forid . '|' . $id . '" value="' . $k . '" checked="checked" />' . $d;
				} else {
					echo '<input type="radio" name="' . $forid . '|' . $id . '" value="' . $k . '" />' . $d;
				}
				echo ' ';
			}
		} elseif($type == 'select') {
			echo '<select  name="' . $forid . '|' . $id . '">';
			foreach($data as $k => $d) {
				if($forid == $default || $k == arrrayKeyByVelue($formvalue, $forid, $id, $default)) {
					echo '<option value="' . $k . '" selected="selected">' . $d . '</option>';
				} else {
					echo '<option value="' . $k . '">' . $d . '</option>';
				}
				echo ' ';
			}
			echo '</select>';
		} elseif($type == 'color') {
			echo '<input type="text" name="' . $forid . '|' . $id . '"  value="' . arrrayKeyByVelue($formvalue, $forid, $id, $default) . '" class="colorinput" />';
		} elseif($type == 'upload') {
			if(arrrayKeyByVelue($formvalue, $forid, $id, $default) != '') {
				echo '<input type="text" name="' . $forid . '|' . $id . '" id="' . base64_encode($forid . '|' . $id) . '_input"  value="' . arrrayKeyByVelue($formvalue, $forid, $id, $default) . '" class="upload" />';
				echo '<a href="javascript:changeupload(\'' . base64_encode($forid . '|' . $id) . '\')" id="' . base64_encode($forid . '|' . $id) . '_txt">上传新图</a> <input type="file" name="' . $forid . '|' . $id . '" id="' . base64_encode($forid . '|' . $id) . '_upload"  value="" class="upload" style="display:none" />';
			} else {
				echo '<input type="file" name="' . $forid . '|' . $id . '"  value="" class="upload" />';
			}
		}
	}
	spAddViewFunction('themecustom','themecustom');

	//通过数组的键名查到值
	function arrrayKeyByVelue($formvalue, $forid, $id, $default) {
		$fid = $forid . '|' . $id;
		if($formvalue[$fid]) {
			return $formvalue[$fid];
		} else {
			return $default;
		}
	}

	function parseTime($params) {
		$timestamp = $params['time'];
		if($params['format'] === null) {
			$dateformat = 'Y年m月d日, H:i';
		} else {
			$dateformat = $params['format'];
		}
		$limit = time() - $timestamp;
		if($limit < 60) {
			$promptStr = '刚刚';
		} elseif($limit < 3600) {
			$promptStr = floor($limit / 60) . '分钟前';
		} elseif($limit >= 3600 && $limit < 86400) {
			$promptStr = floor($limit / 3600) . '小时前';
		} elseif($limit >= 86400 && $limit < 2592000) {
			$promptStr = floor($limit / 86400) . '天前';
		} elseif($limit >= 2592000 && $limit < 31104000) {
			$promptStr = floor($limit / 2592000) . '个月前';
		} else {
			$promtStr = date($dateformat, $timestamp);
		}
		return $promptStr;
	}
	spAddViewFunction('parseTime','parseTime');

	//404页面
	function err404($msg) {
		header("HTTP/1.0 404 Not Found");
		include APP_PATH . '/tpl/404.html';
		exit;
	}

	if(!function_exists('mime_content_type')) {
		function mime_content_type($filename) {
			$mime_types = array(
				'txt' => 'text/plain',
				'htm' => 'text/html',
				'html' => 'text/html',
				'php' => 'text/html',
				'css' => 'text/css',
				'js' => 'application/javascript',
				'json' => 'application/json',
				'xml' => 'application/xml',
				'swf' => 'application/x-shockwave-flash',
				'flv' => 'video/x-flv',

				// images
				'png' => 'image/png',
				'jpe' => 'image/jpeg',
				'jpeg' => 'image/jpeg',
				'jpg' => 'image/jpeg',
				'gif' => 'image/gif',
				'bmp' => 'image/bmp',
				'ico' => 'image/vnd.microsoft.icon',
				'tiff' => 'image/tiff',
				'tif' => 'image/tiff',
				'svg' => 'image/svg+xml',
				'svgz' => 'image/svg+xml',

				// archives
				'zip' => 'application/zip',
				'rar' => 'application/x-rar-compressed',
				'exe' => 'application/x-msdownload',
				'msi' => 'application/x-msdownload',
				'cab' => 'application/vnd.ms-cab-compressed',

				// audio/video
				'mp3' => 'audio/mpeg',
				'qt' => 'video/quicktime',
				'mov' => 'video/quicktime',

				// adobe
				'pdf' => 'application/pdf',
				'psd' => 'image/vnd.adobe.photoshop',
				'ai' => 'application/postscript',
				'eps' => 'application/postscript',
				'ps' => 'application/postscript',

				// ms office
				'doc' => 'application/msword',
				'rtf' => 'application/rtf',
				'xls' => 'application/vnd.ms-excel',
				'ppt' => 'application/vnd.ms-powerpoint',

				// open office
				'odt' => 'application/vnd.oasis.opendocument.text',
				'ods' => 'application/vnd.oasis.opendocument.spreadsheet',
			);

			$ext = strtolower(array_pop(explode('.', $filename)));
			if (array_key_exists($ext, $mime_types)) {
				return $mime_types[$ext];
			} elseif (function_exists('finfo_open')) {
				$finfo = finfo_open(FILEINFO_MIME);
				$mimetype = finfo_file($finfo, $filename);
				finfo_close($finfo);
				return $mimetype;
			} else {
				return 'application/octet-stream';
			}
		}
	}


	//将正文和属性剥离出来分别存放
	function split_attribute($body) {
		$body = preg_replace('/[\n\r\t]/', '', $body);
		preg_match("/\[attribute\](.*?)\[\/attribute\]/i", $body, $matches);
		$data['content'] = str_replace($matches, '', $body);
		if($matches[1]) {
			$data['attr'] = unserialize($matches[1]);
		} else {
			$data['attr'] = '';
		}
		return $data;
	}

	//显示首页和推荐的内容用来处理
	function feedParse($data,$type) {
		if(!spAccess('r', $type)) {
			$rsdata = array();
			if(is_array($data)) {
				foreach($data as $d) {
					$img = $desc = $midetype =  $pid = '';
					$rs = split_attribute($d['body']); //解析内容
					if($d['type'] == 1) {//文字
						$img = $rs['attr'];
						$content = utf8_substr(strip_tags($rs['content'], '<br>'), 0, 400);
						$rsdata[] = array(
										'bid' => $d['bid'],
										'btype' => $d['type'],
										'img' => $img,
										'content' => $content,
										'uid' => $d['user']['uid'],
										'username' => $d['user']['username'],
										'domian' => $d['user']['domain'],
										'tag' => $d['tag']
									);
					} elseif($d['type'] == 2 || $d['type'] == 4) { //音乐
						if($rs['attr']) {
							$img = $rs['attr'][0]['img'];
							$desc = $rs['attr'][0]['desc'];
							$midetype = $rs['attr'][0]['type'];
							$pid = $rs['attr'][0]['pid'];
						}
						$rsdata[] = array(
										'bid' => $d['bid'],
										'btype' => $d['type'],
										'metype' => $midetype,
										'img' => $img,
										'pid' => $pid,
										'desc' => $desc,
										'uid' => $d['user']['uid'],
										'username' => $d['user']['username'],
										'domian' => $d['user']['domain'],
										'tag' => $d['tag']
									);
					} elseif($d['type'] == 3) { //图片
						$count  = $rs['attr']['count'];
						if($rs['attr']['img']) {
							$img = $rs['attr']['img'][0]['url'];
							$rsdata[] = array(
											'bid' => $d['bid'],
											'btype' => $d['type'],
											'img' => $img,
											'count' => $count,
											'uid' => $d['user']['uid'],
											'username' => $d['user']['username'],
											'domian' => $d['user']['domain'],
											'tag' => $d['tag']
										);
						}
					}
				}
			}
			$data = $rsdata;

			spAccess('w', $type, $data, 60);
			return $data;
		} else {
			return spAccess('r', $type);
		}
	}
	
	function password_encode($pwd, $salt) {
		 $encodestring = $GLOBALS['G_SP']['encodestring'];
		 return md5(md5($pwd . $encodestring . $salt));
	}
	
	//数组分页截断
	function arrayPage($arr, $num) {
		for ($i = 0 ; $i < $num ; $i++) {
			$data[$i] = $arr[$i];
		}
		return $data;
	}

	//针对UTF-8的strlen
	function utf8_strlen($string = null) {
		preg_match_all("/./us", $string, $match);
		return count($match[0]);
	}

	//针对UTF-8的sub_str
	/**********************************
	 * 截取字符串(UTF-8)
	 *
	 * @param string $str 原始字符串
	 * @param $position 开始截取位置
	 * @param $length 需要截取的偏移量
	 * @return string 截取的字符串
	 * $type=1 等于1时末尾加'...'不然不加
	 *********************************/
	function utf8_substr($str, $position, $length,$type = 1) {
		$startPos = strlen($str);
		$startByte = 0;
		$endPos = strlen($str);
		$count = 0;
		for($i = 0 ; $i < strlen($str) ; $i++) {
			if($count >= $position && $startPos > $i) {
				$startPos = $i;
				$startByte = $count;
			}
			if(($count-$startByte) >= $length) {
				$endPos = $i;
				break;
			}
			$value = ord($str[$i]);
			if($value > 127){
				$count++;
				if($value >= 192 && $value <= 223)
					$i++;
				elseif($value >= 224 && $value <= 239)
					$i = $i + 2;
				elseif($value >= 240 && $value <= 247)
					$i = $i + 3;
				else
					return self::raiseError("\"$str\" Not a UTF-8 compatible string", 0, __CLASS__, __METHOD__, __FILE__, __LINE__);
			}
			$count++;
		}
		if($type == 1 && ($endPos - 6) > $length){
			return substr($str, $startPos, $endPos - $startPos) . "...";
		} else {
			return substr($str, $startPos, $endPos-$startPos);
		}
	}
	
	//处理不同浏览器下get 中文是否为gb2312 或者utf8的问题
	function tagEncodeParse($tag) {
		if(is_utf8($tag)) {
			return preg_replace('/\s/', '', trim(urldecode($tag)));
		}
		return preg_replace('/\s/','', trim(urldecode(iconv("GB2312", "UTF-8", $tag))));
	}

	function is_utf8($string) {
		return preg_match('%^(?:
					[\x09\x0A\x0D\x20-\x7E]            # ASCII
					| [\xC2-\xDF][\x80-\xBF]             # non-overlong 2-byte
					|  \xE0[\xA0-\xBF][\x80-\xBF]        # excluding overlongs
					| [\xE1-\xEC\xEE\xEF][\x80-\xBF]{2}  # straight 3-bytes
					|  \xED[\x80-\x9F][\x80-\xBF]        # excluding surrogates
					 \xF0[\x90-\xBF][\x80-\xBF]{2}     # planes 1-3
					| [\xF1-\xF3][\x80-\xBF]{3}          # planes 4-15
					|  \xF4[\x80-\x8F][\x80-\xBF]{2}     # plane 16
					)*$%xs', $string);
	}

	//过滤不安全因素
	function strreplaces($str) {
		$farr = array(
			"/\s+/", //过滤多余的空白
			"/<(\/?)(script|i?frame|object|html|body|title|link|meta|div|\?|\%)([^>]*?)>/isU", //过滤tag
			"/(<[^>]*)on[a-zA-Z]+\s*=([^>]*>)/isU", //过滤javascript的on事件
		);
		$tarr = array(
			" ",
			"＜\\1\\2\\3＞", //如果要直接清除不安全的标签，这里可以留空
			"\\1\\2",
		);
		$str = preg_replace($farr, $tarr, $str);
		return $str;
	}
	
	/**
	 * 个性化域名
	 * 保留子域名
	 */
	function getDomainArray() {
		return array("zhangchunsheng","chunshengzhang","luomor","www","bbs","blog","news","yiluxiangbei","google","justwannabewithyou");
	}
	
	/**
	 * 获得位置信息
	 * HTTP 地址解析器支持并返回以下类型：
	 * street_address 表示一个精确的街道地址。
	 * route 表示一条已命名的路线（如“US 101”）。
	 * intersection 表示一个大十字路口，通常由两条主道交叉形成。
	 * political 表示一个政治实体。此类型通常表示代表某个行政管理区的多边形。
	 * country 表示国家政治实体。在地址解析器返回的结果中，该部分通常列在最前面。
	 * administrative_area_level_1 表示仅次于国家级别的行政实体。在美国，这类行政实体是指州。并非所有国家都有该行政级别。
	 * administrative_area_level_2 表示国家级别下的二级行政实体。在美国，这类行政实体是指县。并非所有国家都有该行政级别。
	 * administrative_area_level_3 表示国家级别下的三级行政实体。此类型表示较小的行政单位。并非所有国家都有该行政级别。
	 * colloquial_area 表示实体的通用别名。
	 * locality 表示合并的市镇级别政治实体。
	 * sublocality 表示仅次于地区级别的行政实体。
	 * neighborhood 表示已命名的邻近地区。
	 * premise 表示已命名的位置，通常是具有常用名称的建筑物或建筑群。
	 * subpremise 表示仅次于已命名位置级别的实体，通常是使用常用名称的建筑群中的某座建筑物。
	 * postal_code 表示邮政编码，用于确定相应国家/地区内的信件投递地址。
	 * natural_feature 表示某个明显的自然特征。
	 * airport 表示机场。
	 * park 表示已命名的公园。
	 * point_of_interest 表示已命名的兴趣点。通常，这些“POI”是一些不易归入其他类别的比较有名的当地实体，如“帝国大厦”或“自由女神像”。
	 */
	function getPositionInfo($latitude, $longitude) {
		$url = "http://maps.googleapis.com/maps/api/geocode/json?latlng=$latitude,$longitude&sensor=false&language=zh-CN&region=cn";
		$info = json_decode(request($url, "get"));
		$positionInfo = new StdClass();
		if($info -> status == "OK") {
			/**
			 * street_address postal_code sublocality locality administrative_area_level_1 country
			 */
			$results = $info -> results;
			foreach($results as $key_result => $value_result) {
				$type = $value_result -> types[0];
				switch($type) {
				case "street_address":
				case "postal_code":
				case "sublocality":
					$address_components = $value_result -> address_components;
					foreach($address_components as $key_address => $value_address) {
						if($value_address -> types[0] == "locality") {
							$positionInfo -> localityName = $value_address -> long_name;
							break;
						}
					}
					foreach($address_components as $key_address => $value_address) {
						if($value_address -> types[0] == "sublocality") {
							$positionInfo -> sublocalityName = $value_address -> long_name;
							break;
						}
					}
					$positionInfo -> address_type = $type;
					$positionInfo -> address = $value_result -> formatted_address;
					break;
				case "locality":
					$address_components = $value -> address_components;
					foreach($address_components as $key_address => $value_address) {
						if($value_address -> types[0] == "locality") {
							$positionInfo -> localityName = $value_address -> long_name;
							break;
						}
					}
					$positionInfo -> sublocalityName = "";
					$positionInfo -> address_type = $type;
					$positionInfo -> address = $value_result -> formatted_address;
					break;
				case "administrative_area_level_1":
					$address_components = $value -> address_components;
					foreach($address_components as $key_address => $value_address) {
						if($value_address -> types[0] == "administrative_area_level_1") {
							$positionInfo -> localityName = $value_address -> long_name;
							break;
						}
					}
					$positionInfo -> sublocalityName = "";
					$positionInfo -> address_type = $type;
					$positionInfo -> address = $value_result -> formatted_address;
					break;
				}
				if($positionInfo -> localityName)
					break;
			}
			if(!$positionInfo -> localityName) {
				$positionInfo -> localityName = "中国";
				$positionInfo -> sublocalityName = "";
				$positionInfo -> address_type = "country";
				$positionInfo -> address = "中国";
			}
		} else {
			$positionInfo -> localityName = "中国";
			$positionInfo -> sublocalityName = "";
			$positionInfo -> address_type = "country";
			$positionInfo -> address = "中国";
			writeLog("error", $latitude . " " . $longitude . " " . json_encode($info));
		}
		return $positionInfo;
	}
	
	/**
	 * http请求
	 */
	function request($url, $type, $data = array()) {
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		if($type == "get") {
			curl_setopt($ch, CURLOPT_GET, true);
		}
		if($type == "post") {
			curl_setopt($ch, CURLOPT_POST, true);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
		}
		$result = curl_exec($ch);
		curl_close($ch);
		return $result;
	}
	
	/**
	 * 写日志
	 */
	function writeLog($level, $content) {
		switch($level) {
		case "info":
			$file = fopen(APP_PATH . "/logs/luomor_info.log", "a+");
			break;
		case "error":
			$file = fopen(APP_PATH . "/logs/luomor_error.log", "a+");
			break;
		default:
			$file = fopen(APP_PATH . "/logs/luomor_info.log", "a+");
		}
		fwrite($file, date("Y-m-d H:i:s") . " " . $content . "\r\n");
		fclose($file);
	}