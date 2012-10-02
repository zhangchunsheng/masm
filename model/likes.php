<?php
	/**
	 * 作者：peter
	 * 日期：2012-10-02
	 * 说明：用户喜欢
	 */
	class likes extends spModel {
		var $pk = "id"; // 主键
		var $table = "likes"; // 数据表的名称

		var $linker = array(
			array(
				'type' => 'hasone', // 关联类型，这里是一对一关联
				'map' => 'blog', // 关联的标识
				'mapkey' => 'bid', // 本表与对应表关联的字段名
				'fclass' => 'blog', // 对应表的类名
				'fkey' => 'bid', // 对应表中关联的字段名
				'enabled' => true // 启用关联
			)
		);

		//进行喜欢和取消喜欢的操作
		function changeLikes($rows, $uid) {
			$result = $this -> find(array('bid' => $rows['bid'], 'uid' => $uid));
			$rs = spClass('blog') -> find(array('bid' => $rows['bid']), '', 'uid');
			if($rs['uid'] == $uid) {
				return '不能标记自己的内容';
			}

			if(is_array($result)) {//如果已经标记喜欢
				$this -> delete(array('bid' => $rows['bid'], 'uid' => $uid));
				spClass('feeds') -> changeFeedsLike($rows, $uid);
				spClass('member') -> decrField(array('uid' => $uid), 'likenum'); //减少喜欢统计
				return 2;
			} else {
				$this -> create(array('bid' => $rows['bid'], 'uid' => $uid,'time' => time()));
				spClass('feeds') -> changeFeedsLike($rows, $uid);
				spClass('member') -> incrField(array('uid' => $uid), 'likenum'); //增加回复统计
				return 1;
			}
		}
	}
?>