<?php
	/**
	 * 作者：peter
	 * 日期：2012-10-07
	 * 说明：分类
	 */
	class mcate extends spController {
		function __construct() {
			parent :: __construct();
			if($_SESSION['admin'] != 1) {
				prient_jump(spUrl('main'));
			}
		}

		function index() {
			$this -> cate = spClass('category') -> spPager($this -> spArgs('page', 1),15) -> findAll('', 'sort ASC');
			$this -> pager = spClass('category') -> spPager() -> pagerHtml('mcate');
			$this -> curr_cate = 'class="curr"';
			$this -> display('admin/catelist.html');
		}

		function create() {
			$catename = $this -> spArgs('catename');
			$sort = intval($this -> spArgs('sort'));
			if($catename == '' || $sort == '') {
				$this -> error('不能为空,排序必须为数字');
			}

			spClass("category") -> create($this -> spArgs());
			$this -> success('添加成功', spUrl('mcate'));
		}

		function edit() {
			spClass("category") -> update(array('cid' => $this -> spArgs('cid')), $this -> spArgs());
			$this -> success('修改成功！', spUrl('mcate'));
		}
	}