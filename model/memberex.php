<?php
	/**
	 * 作者：peter
	 * 日期：2012-10-07
	 * 说明：memberex
	 */
	class memberex extends spModel {
		var $pk = "openid"; // 主键
		var $table = "memberex"; // 数据表
		
		var $linker = array(
			array(
				'type' => 'hasone', // 关联类型，这里是一对一关联
				'map' => 'user',  // 关联的标识
				'mapkey' => 'uid', // 本表与对应表关联的字段名
				'fclass' => 'member', // 对应表的类名
				'fkey' => 'uid', // 对应表中关联的字段名
				'field' => 'uid,username,email,domain,admin', //你要限制的字段
				'enabled' => true // 启用关联
			)
		);

		function CancelBind($type, $uid) {
			return $this -> delete(array('uid' => $uid), array('types' => $type));
		}
	}
?>