<?php
	/**
	 * 作者：peter
	 * 日期：2012-10-02
	 * 说明：附件管理
	 */
	class attach extends spModel {
		var $pk = "id"; //主键
		var $table = "attachments"; // 数据表的名称

		//删除某一个附件,连数据带文件都删除
		function delById($id, $uid) {
			$rs = $this -> find(array('id' => $id, 'uid' => $uid), '', 'path');
			$path = pathinfo($rs['path']);
			$file = $path['dirname'] . '/t_' . $path['basename'];
			@unlink(APP_PATH . '/' . $rs['path']);
			@unlink(APP_PATH . '/' . $file);

			return $this -> deleteByPk($id);
		}

		//_localMusicParse使用
		function findById($id) {
			return $this -> find(array('id' => $id), '', 'uid,path');
		}
	}
?>