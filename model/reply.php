<?php
	/**
	 * 作者：peter
	 * 日期：2012-10-02
	 * 说明：评论管理
	 */
	class reply extends spModel {
		var $pk = "id";
		var $table = "reply"; // 数据表的名称

		var $linker = array(
			array(
				'type' => 'hasone', // 关联类型，这里是一对一关联
				'map' => 'user', // 关联的标识
				'mapkey' => 'uid', // 本表与对应表关联的字段名
				'fclass' => 'member', // 对应表的类名
				'fkey' => 'uid', // 对应表中关联的字段名
				'field' => 'uid,username,domain ', //你要限制的字段
				'enabled' => true // 启用关联
			),
			array(
				'type' => 'hasone', // 关联类型，这里是一对一关联
				'map' => 'blog', // 关联的标识
				'mapkey' => 'bid', // 本表与对应表关联的字段名
				'fclass' => 'blog', // 对应表的类名
				'fkey' => 'bid', // 对应表中关联的字段名
				//'field' => 'uid,username,domain', //你要限制的字段
				'enabled' => true // 启用关联
			)
		);

		//添加一个回复
		function createReply($row) {
			$err = array('err' => '');
			$msg = $row['inputs'];
			$rs = spClass('mBlog') -> find(array('bid' => $row['bid']), '', 'uid,noreply');
			if($rs['noreply'] == 1) {
				$err['err'] = '该内容作者不允许评论';
				return $err;
			}

			if($GLOBALS['LUOMOR']['keep_rep'] != '') {
				$arr = explode(',', $GLOBALS['LUOMOR']['keep_rep']);
				if(in_array($row['inputs'],$arr)) {
					$err['err'] = '含有敏感字符不允许发布';
					return $err;
				}
			}

			if($row['repuid'] != '') {//如果指定了回复者，则进行处理
				$repuid = $row['repuid'];
				preg_match('/\@(.*?)\:/', $row['inputs'], $msgs); //用数组1
				$msga = trim(str_replace('@' . $msgs[1] . ':', '', $row['inputs']));
				$msga = str_replace('#', '', $msga);
				if($msga =='') {
					$err['err'] = '评论不能为空';
					return $err;
				}
				$msg = "回复[at=$repuid]@$msgs[1][/at]:" . $msga;
			}

			if($row['repuid'] != $_SESSION['uid'] && $row['repuid'] != '') {
				spClass('notice') -> noticeReplay(array('foruid' => $row['repuid'], 'bid' => $row['bid']), '回复了您的博客', $msg); //给@发一个通知
			} else {
				spClass('notice') -> noticeReplay(array('foruid' => $rs['uid'], 'bid' => $row['bid']), '回复了您的博客', $msg); //给作者发
			}

			$this -> create(
				array(
					'bid' => $row['bid'],
					'msg' => $msg,
					'uid' => $_SESSION['uid'],
					'repuid' => $repuid,
					'time' => time()
				)
			);
			spClass('mBlog') -> incrField(array('bid' => $row['bid']), 'replaycount'); //增加回复统计
			//spClass('feeds') -> replayFeeds($row, $msg, $_SESSION['uid']); //增加回复动态

			$bid = spClass('mBlog') -> blogIsMe($row['bid']);  //这个是哪来的..
			return $err;
		}

		//删除评论，feed得单独处理
		function delReply($row, $uid) {
			$rs = $this -> spLinker() -> find(array('id' => $row['id']));

			if($rs['uid'] == $uid || $_SESSION['admin'] == 1) {
				$this -> deleteByPk($row['id']); //删除回复
				 //减少回复统计
				if($rs['blog']['replaycount'] > 0) {
					spClass('mBlog') -> decrField(array('bid' => $rs['bid']), 'replaycount');
				}
				echo 'ok';
			}
		}
	}
?>