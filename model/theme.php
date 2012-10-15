<?php
	/**
	 * 作者：peter
	 * 日期：2012-10-07
	 * 说明：主题
	 */
	class theme extends spModel {
		var $pk = "uid"; //主键
		var $table = "theme"; // 数据表的名称

		function getByDomain($domain) {
			return spClass('member') -> find(array('domain' => $domain), '', 'uid,username,domain,blogtag,sign,num,flow,likenum,local,logtime');
		}

		function getByUid($uid) {
			return spClass('member') -> find(array('uid' => $uid), '', 'uid,username,domain,blogtag,sign,num,flow,likenum,local,logtime');
		}

		function getByBid($bid) {
			$rs = spClass('mBlog') -> find(array('bid' => $bid), '', 'uid');
			return spClass('member') -> find(array('uid' => $rs['uid']), '', 'uid,username,domain,blogtag,sign,num,flow,likenum,local,logtime');
		}

		//更新扩展字段并删除之前的内容
		function updateExtField($uid, $field, $rootpath, $filepath) {
			$user = $this -> find(array('uid' => $uid));

			if($user[$field] != '') {
				@unlink($rootpath . $user[$field]);
			}
			$this -> update(array('uid' => $uid), array($field => $filepath));
		}

		function clearCustom() {
			$ext = spExt('luomorUpload');
			$savedir = $ext['savepath'] . '/' . $ext['savedir'] . '/theme/';
			$this -> updateExtField($_SESSION['uid'], 'img1', $savedir, '');
			$this -> updateExtField($_SESSION['uid'], 'img2', $savedir, '');
			$this -> updateExtField($_SESSION['uid'], 'img3', $savedir, '');
			$this -> updateExtField($_SESSION['uid'], 'img4', $savedir, '');
			$this -> update(array('uid' => $_SESSION['uid']), array('setup' => '', 'css' => ''));
		}

		function saveTheme() {
			$rs = $this -> find(array('uid' => $_SESSION['uid']));
			if($rs) {
				$this -> update( array('uid' => $_SESSION['uid']), array('theme' => $_SESSION['customize']['theme'], 'css' => $_SESSION['customize']['css'], 'setup' => serialize($_SESSION['customize']['config'])));
			} else {
				$this -> create(array('uid' => $_SESSION['uid'], 'theme' => $_SESSION['customize']['theme'], 'css' => $_SESSION['customize']['css'], 'setup' => serialize($_SESSION['customize']['config'])));
			}
			spClass('skins') -> incrField(array('skindir' => $_SESSION['customize']['theme']), 'usenumber');
			$_SESSION['customize'] = '';
		}
	}
?>