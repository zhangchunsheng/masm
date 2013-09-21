<?php
	/**
	 * 作者：peter
	 * 日期：2012-10-06
	 * 说明：我的标签
	 */
	class mytag extends spModel {
		var $pk = "id"; //主键
		var $table = "mytags"; // 数据表的名称

		var $linker = array(
			array(
				'type' => 'hasone', // 关联类型，这里是一对一关联
				'map' => 'tag', // 关联的标识
				'mapkey' => 'tagid', // 本表与对应表关联的字段名
				'fclass' => 'tags', // 对应表的类名
				'fkey' => 'tid', // 对应表中关联的字段名
				'field' => 'title,num,updates', //你要限制的字段
				'enabled' => true // 启用关联
			)
		);

		//我喜欢的标签
		function myFavaTag($uid, $limit = '') {
			return $this -> spLinker() -> findAll(array('uid' => $uid), 'id DESC', '', $limit);
		}

		//添加我喜欢的tag
		function addMyFavaTag($tag, $uid) {
			$rs = spClass('tags') -> find(array('title' => $tag), '', 'tid,title');
			if($rs) {
				$find = $this -> find(array('tagid' => $rs['tid'], 'uid' => $uid));
				if($find) {
				  $this -> delete(array('tagid' => $rs['tid'], 'uid' => $uid));
				  return 2;
				} else {
					$this -> create(array('tagid' => $rs['tid'], 'uid' => $uid));
					return 1;
				}
			} else {
				return '系统没有检索到相关信息';
			}
		}
	}
?>