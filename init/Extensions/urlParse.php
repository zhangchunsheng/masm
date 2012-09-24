<?php
/////////////////////////////////////////////////////////////////
//云边开源轻博, Copyright (C)   2010 - 2011  qing.thinksaas.cn 
//EMAIL:nxfte@qq.com QQ:234027573                              
//$Id: urlParse.php 45 2011-11-07 06:33:21Z anythink $ 


class urlParse {
	
	private $url  = '';
	private $desc = '';


    public function __construct() {
    }
	
	
	public function seturldesc($url,$desc='',$type='music')
	{
		$sitelist = array('xiami.com'=>'_webMusicGetXiami',
						  'flamesky.com'=>'_webMusicGetFlamesky'
						); //注册引用解析
	

		$path =  pathinfo($url);
		$ext = $path['extension']; //页面后缀
		$domain = $this->getDomain($url);//引用页地址
		
		
		if($ext != 'mp3' && $ext != 'wma')
		{
			if (array_key_exists($domain, $sitelist)) {   //网页解析
					$data = $this->$sitelist[$domain]($url); 
				}else{
					$data = $this->_webMusicGetOther($url);
				}
		}
		
		//如果是音乐模块则解析
		if($type == 'music')
		{
			if($ext =='mp3' || $ext== 'wma'){ $data = $this->_webMusicGetMp3($url); }
		}
		

		if(is_array($data)){
			
			if($desc != ''){$data['desc'] = $desc;}
			if($data['type'] == '' || $data['id'] == '' || $data['img'] == '' || $data['title'] == '')
			{
				return array('error'=>'我们无法解析这个地址,请尝试更换方法');
			}
			return $data; 
		}else{
			return array('error'=>'我们无法解析这个地址,请尝试更换方法');
		}

	}
    
    


	/*引用解析器 解析MP3*/
	private function _webMusicGetMp3($url)
	{
		$base = pathinfo($url);
		return array('type'=>'weblink','id'=>$url,'url'=>$url,'img'=>'tpl/image/add/webmusic.png','title'=>urldecode($base['basename']));
	}
	
	
	/*引用解析器 解析优酷*/
	/*
	private function _webMusicGetYouKu($url)
	{
		preg_match_all("/id\_(\w+)[=.]/", $url, $matches);
		if(!empty($matches[1][0])) {
			$playid = $matches[1][0];
			$text = file_get_contents($url); //获取标题
			preg_match("/\+0800\|(.*?)\|\">/i",$text,$img);
			preg_match("/<title>(.*?)<\/title>/i",$text,$title);
			preg_match("/<meta name=\"title\" content=\"(.*?)\"> /i",$text,$desc);
			$arr = array('- 视频','- 优酷视频','- 在线观看'); $rep = array('','','');
			$des = str_replace($arr,$rep,$desc[1]);
			return array('type'=>'youku','id'=>$playid,'img'=>$img[1],'title'=>$des);
		}else{
			return '';	
		}	
	}
	*/
	
	/*引用解析器 解析虾米*/
	
	private function _webMusicGetXiami($url){
		import('htmlDomNode.php');
		$html = file_get_html($url);
		$data['type'] = 'xiami';
		$data['id'] = pathinfo($url,PATHINFO_BASENAME);
		$data['img'] = $html->find('#song_block img',0)->src;
		$data['title'] = $html->find('#albums_info a',0)->innertext.'-'.$html->find('#albums_info a',1)->innertext;
		return $data;
	}
	
	
	
	/*引用解析器 解析雅燃*/
	
	private function _webMusicGetFlamesky($url){
		import('htmlDomNode.php');
		$html = file_get_html($url);
		$data['type'] = 'flamesky';
		$data['id'] = pathinfo($url,PATHINFO_BASENAME);
		$data['img'] = 'http://www.flamesky.com'.$html->find('#conter img',0)->src;
		$data['title'] = $html->find('.tracktitle a',0)->innertext;
		return $data;
	}
	

	/*引用解析器 解析音悦台*/
	/*
	private function _webMusicGetYinyuetai($url){
		import('htmlDomNode.php');
		$html = file_get_html($url);
		$data['type'] = 'yinyuetai';
		$data['id'] = pathinfo($url,PATHINFO_BASENAME);
		$data['img'] = 'http://www.yinyuetai.com'.$html->find('.mv_list_simple .thumb a img',0)->src;
		$tmparr = explode('?',$data['img'],2);
		$data['img'] = $tmparr[0];
		$data['title'] = $html->find('#videoTitle',0)->innertext;
		return $data;
	}
	*/
	
	/*引用解析器 解析6间房*/
	/*
	private function _webMusicGet6($url){
		import('htmlDomNode.php');
		$html = file_get_html($url);
		$data['type'] = '6';
		$data['id'] = $html->find('.game a',0)->href;
		$data['id'] = str_replace('/wp/','/p/',$data['id']).'.swf';
		$data['img'] = $html->find('.vlist2 .vpic1 img',0)->src;
		$data['title'] = $html->find('#watchRelVideoSS a',0)->innertext;
		return $data;
	}
	*/
	
	/*引用解析器 解析土豆*/
	/*
	private function _webMusicGetTuDou($url)
	{
		import('htmlDomNode.php');
		$html = file_get_html($url);
		$data['type'] = 'tudou';
		$data['id'] = pathinfo($url,PATHINFO_BASENAME);
		preg_match_all("/thumbnail = pic = '(.*?)'/",$html,$result);
		$data['img'] = $result[1][0];
		$data['title'] = iconv('gbk', 'UTF-8', $html->find('#video_details .v_title',0)->innertext);
		return $data;
	}
	
	private function _webMusicGetSina($url){
		import('htmlDomNode.php');
		$html = file_get_html($url);
		$data['type'] = 'sina';
		$data['id'] = rtrim(pathinfo($url,PATHINFO_BASENAME),'.'.pathinfo($url,PATHINFO_EXTENSION ));
		preg_match_all("/pic: '(.*?)'/",$html,$result);
		$data['img'] = $result[1][0];
		$data['title'] = $html->find('#videoTitle',0)->innertext;
		return $data;
	}
	*/
	
	private function _webMusicGetOther($url){
		$urlArr = parse_url($url);
		$domainArr = explode('.',$urlArr['host']);
		$data['type'] = $domainArr[count($domainArr)-2];
		$str = $this->formPost('http://share.pengyou.com/index.php?mod=usershare&act=geturlinfo',array('url'=>$url));
		$arr = json_decode($str,true);
		$data['id'] = $arr['result']['flash'];
		$data['title'] = $arr['result']['title'];
		$data['img'] = $arr['result']['coverurl'];
		$data['url'] = $url;
		return $data;
	}
		


	/*获取域名*/
	private function getDomain($url){
		$pattern = "/[\w-]+\.(com|net|org|gov|cc|biz|info|cn|co)(\.(cn|hk))*/";
		preg_match($pattern, $url, $matches);
		if(count($matches)>0)
		{
			return $matches[0];
		}else{
		$rs = parse_url($url);
		$main_url = $rs["host"];
				
			if(!strcmp((sprintf("%u",ip2long($main_url))),$main_url)) {
				return $main_url;
			}else{
				$arr = explode(".",$main_url);
				$count=count($arr);
				$endArr = array("com","net","org","3322");//com.cn  net.cn 等情况
			
				if (in_array($arr[$count-2],$endArr)){
					$domain = $arr[$count-3].".".$arr[$count-2].".".$arr[$count-1];
				}else{
					$domain =  $arr[$count-2].".".$arr[$count-1];
				}
			return $domain;
			}
		}
	}
	
	
	private function formPost($url,$post_data){
		$o='';
		foreach ($post_data as $k=>$v){
			$o.= "$k=".urlencode($v)."&";
		}
		$post_data=substr($o,0,-1);
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true );
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_URL,$url);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
		$result = curl_exec($ch);
		return $result;
	}


}

?>