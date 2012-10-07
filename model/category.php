<?php
	/**
	 * 作者：peter
	 * 日期：2012-10-07
	 * 说明：分类
	 */
	class category extends spModel {
		var $pk = "cid"; // 主键
		var $table = "catetags"; // 数据表的名称

		function findCate() {
			return $this -> spCache(3600) -> findAll('', 'sort ASC');
		}

		function saveCate($post) {
			if(is_array($post['cate'])) {
				foreach($post['cate'] as $k => $v) {
					$arr['sort'] = $v['sort'];
					$arr['catename'] = $v['catename'];
					$this -> update(array('cid' => $k), $arr);
				}
			}
		}

	}
?>