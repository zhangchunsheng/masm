<?php
	/**
	 * 作者：peter
	 * 日期：2012-09-26
	 * 说明：urlParse
	 */
	class urlParse {
		private $url  = '';
		private $desc = '';

		public function __construct() {
			
		}

		public function setMediaDescription($url, $desc = '', $type = "music") {
			$path =  pathinfo($url);
			$ext = $path['extension']; //页面后缀
			$domain = $this -> getDomain($url);//引用页地址

			//如果是音乐模块则解析
			if($type == "music") {
				$sitelist = array(
					'xiami.com' => '_webMusicFromXiami',
					'flamesky.com' => '_webMusicFromFlamesky',
					'yinyuetai.com' => '_webMusicFromYinyuetai',
				); //注册引用解析
				if($ext == 'mp3' || $ext == 'wma') {
					$data = $this -> _webMusicFromWeb($url);
				} else {
					if(array_key_exists($domain, $sitelist)) { //网页解析
						$data = $this -> $sitelist[$domain]($url);
					} else {
						$data = array();
					}
				}
			} elseif($type == "video") {
				$sitelist = array(
					'youku.com' => '_webVideoFromYouku',
					'tudou.com' => '_webVideoFromTudou',
					'ku6.com' => '_webVideoFrom6',
					'sina.com.cn' => '_webVideoFromSina'
				); //注册引用解析
				if(array_key_exists($domain, $sitelist)) {
					$data = $this -> $sitelist[$domain]($url);
				} else {
					$data = array();
				}
			}

			if(is_array($data)) {
				if($desc != '') {
					$data['desc'] = $desc;
				}
				if($data['type'] == '' || $data['id'] == '' || $data['img'] == '' || $data['title'] == '') {
					return array('error' => '亲，我们无法解析这个地址');
				}
				return $data;
			} else {
				return array('error' => '亲，我们无法解析这个地址');
			}
		}

		private function _webMusicFromWeb($url) {
			$base = pathinfo($url);
			return array(
				'type' => 'music',
				'id' => $url,
				'url' => $url,
				'img' => 'tpl/image/add/webmusic.png',
				'title' => urldecode($base['basename'])
			);
		}

		//解析虾米
		private function _webMusicFromXiami($url) {
			import('htmlDomNode.php');
			$html = file_get_html($url);
			$data['type'] = 'xiami';
			$data['id'] = pathinfo($url, PATHINFO_BASENAME);
			$data["albumName"] = $html -> find("#albums_info a", 0) -> innertext;
			$data["singerName"] = $html -> find("#albums_info a", 1) -> innertext;
			$data["singerUrl"] = $html -> find("#albums_info a", 1) -> href;
			$data['img'] = $html -> find('#albumCover img', 0) -> src;
			$data['title'] = $html -> find('#albums_info a', 0) -> innertext . ' - ' . $html -> find('#albums_info a', 1) -> innertext;
			//$data['title'] = $html -> find('#title h1', 0) -> innertext . '-' . $html -> find('#albums_info a', 1) -> innertext;
			$data["albumUrl"] = $html -> find("#albumCover", 0) -> href;
			return $data;
		}

		//解析雅燃
		private function _webMusicFromFlamesky($url) {
			import('htmlDomNode.php');
			$html = file_get_html($url);
			$data['type'] = 'flamesky';
			$data['id'] = pathinfo($url, PATHINFO_BASENAME);
			$data['img'] = 'http://www.flamesky.com' . $html -> find('#conter img', 0) -> src;
			$data['title'] = $html -> find('.tracktitle a', 0) -> innertext;
			return $data;
		}

		//解析音悦台
		private function _webMusicFromYinyuetai($url) {
			import('htmlDomNode.php');
			$html = file_get_html($url);
			$data['type'] = 'yinyuetai';
			$data['id'] = pathinfo($url, PATHINFO_BASENAME);
			$data['img'] = 'http://www.yinyuetai.com' . $html -> find('.mv_list_simple .thumb a img', 0) -> src;
			$tmparr = explode('?', $data['img'], 2);
			$data['img'] = $tmparr[0];
			$data['title'] = $html -> find('#videoTitle', 0) -> innertext;
			return $data;
		}
		
		//解析优酷视频
		private function _webVideoFromYouku($url) {
			preg_match_all("/id\_(\w+)[=.]/", $url, $matches);
			if(!empty($matches[1][0])) {
				$playId = $matches[1][0];
				$url = "http://v.youku.com/player/getPlayList/VideoIDS/$playId/version/5/source/out?onData=%5Btype%20Function%5D&n=3";
				$content = file_get_contents($url); //获取标题
				$content = json_decode($content);
				if(count($content -> data) > 0) {
					$info = $content -> data[0];
					return array(
						'type' => 'youku',
						'id' => $playId,
						'img' => $info -> logo,
						'title' => $info -> title
					);
				} else {
					return array();
				}
			} else {
				return array();
			}
		}
		
		//解析土豆视频
		private function _webVideoFromTudou($url) {
			import('htmlDomNode.php');
			$content = file_get_contents($url);
			$content = explode("\n", $content);
			$data = array();
			$data['type'] = 'tudou';
			$id = 0;
			foreach($content as $key => $value) {
				if(stripos($value, "iid") > 0 || stripos($value, "iid") === 0) {
					$id = str_replace("iid: ", "", $value);
					break;
				}
			}
			$data["id"] = $id;
			$array = array();
			if(strlen($id) == 6) {
				$array[0] = "000";
				$array[1] = substr($id, 0, 3);
				$array[2] = substr($id, 3, 3);
			} elseif(strlen($id) == 7) {
				$array[0] = "00" . substr($id, 0, 1);
				$array[1] = substr($id, 1, 3);
				$array[2] = substr($id, 4, 3);
			} elseif(strlen($id) == 8) {
				$array[0] = "0" . substr($id, 0, 2);
				$array[1] = substr($id, 2, 3);
				$array[2] = substr($id, 5, 3);
			} elseif(strlen($id) == 9) {
				$array[0] = substr($id, 0, 3);
				$array[1] = substr($id, 3, 3);
				$array[2] = substr($id, 6, 3);
			}
			$url = "http://v2.tudou.com/v2/cdn?noCatch=22538&safekey=YouNeverKnowThat&refurl=&id=$id";
			$xml = file_get_html($url);
			$info = $xml -> find("v");
			$data['img'] = "http://i01.img.tudou.com/data/imgs/i/$array[0]/$array[1]/$array[2]/p.jpg";
			$data['title'] = $info[0] -> title;
			return $data;
		}

		//解析6间房
		private function _webVideoFrom6($url) {
			import('htmlDomNode.php');
			$html = file_get_html($url);
			$data = array();
			$data['type'] = '6';
			$data['id'] = $html -> find('.game a', 0) -> href;
			$data['id'] = str_replace('/wp/', '/p/', $data['id']) . '.swf';
			$data['img'] = $html -> find('.vlist2 .vpic1 img', 0) -> src;
			$data['title'] = $html -> find('#watchRelVideoSS a', 0) -> innertext;
			return $data;
		}

		private function _webVideoFromSina($url) {
			import('htmlDomNode.php');
			$html = file_get_html($url);
			$data['type'] = 'sina';
			$data['id'] = rtrim(pathinfo($url, PATHINFO_BASENAME), '.' . pathinfo($url, PATHINFO_EXTENSION ));
			preg_match_all("/pic: '(.*?)'/", $html, $result);
			$data['img'] = $result[1][0];
			$data['title'] = $html -> find('#videoTitle',0) -> innertext;
			return $data;
		}

		//获取域名
		private function getDomain($url) {
			$pattern = "/[\w-]+\.(com|net|org|gov|cc|biz|info|cn|co)(\.(cn|hk))*/";
			preg_match($pattern, $url, $matches);
			if(count($matches) > 0) {
				return $matches[0];
			} else { 
				$rs = parse_url($url);
				$main_url = $rs["host"];

				if(!strcmp((sprintf("%u", ip2long($main_url))), $main_url)) {
					return $main_url;
				} else {
					$arr = explode(".", $main_url);
					$count = count($arr);
					$endArr = array("com", "net", "org", "3322");//com.cn  net.cn 等情况
					if (in_array($arr[$count - 2], $endArr)) {
						$domain = $arr[$count - 3] . "." . $arr[$count - 2] . "." . $arr[$count - 1];
					} else {
						$domain =  $arr[$count - 2] . "." . $arr[$count - 1];
					}
					return $domain;
				}
			}
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
	}
?>