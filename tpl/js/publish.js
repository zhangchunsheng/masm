(function ($) {
	$.fn.disable = function () {
		return $(this).find("*").each(function () {
			$(this).attr("disabled", "disabled");
		});
	}
	$.fn.enable = function () {
		return $(this).find("*").each(function () {
			$(this).removeAttr("disabled");
		});
	}
})(jQuery);

$(document).ready(function () {
	var editPlugin = {
		addImage: {
			c: 'testClassName',
			t: '插入网上图片 (Ctrl+3)',
			s: 'ctrl+3',
			h: 1,
			e: function () {
				var _this = this;
				var jTest = $('<div>请把网络图片地址粘贴到下面的输入框</div><div><input id="xheTestInput" style="width:260px;height:20px;" /></div><div style="text-align:right;"><input type="button" id="xheSave" value="确定" /></div>');
				var jTestInput = $('#xheTestInput', jTest),
					jSave = $('#xheSave', jTest);
				jSave.click(function () {
					_this.loadBookmark();
					var imgs = jTestInput.val();
					var imghttp = imgs.substring(0, 7);
					var imgar = imgs.substr(imgs.length - 3, imgs.length);

					if(imghttp == 'http://' && imgs != '') {
						_this.pasteHTML('<img src=' + jTestInput.val() + ' alt="" class="feedimg" />');
					} else if(imghttp == "https:/") {
						tips("亲，暂时不支持https");
					}
					_this.hidePanel();
					return false;
				});
				_this.showDialog(jTest);
			}
		}
	}

	textbody = $('#textarea').xheditor({
		plugins: editPlugin,
		loadCSS: urlpath + '/tpl/images/css/editor.css',
		urlBase: urlpath + '/',
		internalStyle: false
	});

	var jUpload = $('#upload_img input');
	jUpload.mousedown(function () {
		textbody.saveBookmark();
	}).change(function () {
		var $this = $(this),
			sExt = $this.attr('ext'),
			$prev = $this.prev();
		if($this.val().match(new RegExp('\.(' + sExt.replace(/,/g, '|') + ')$', 'i'))) {
			$('#uploading').show();
			var upload = new textbody.html4Upload(this, urlpath + '/index.php?c=publisher&a=uploadimg', function (sText) {
				$('#uploading').hide();
				var data = Object,
					bOK = false;
				try {
					data = eval('(' + sText + ')');
				} catch (ex) {
					alert(sText)
				};
				if(!data.err) {
					textbody.loadBookmark();
					var urls = data.msg.url.split('||');
					if(urls.length == 2) {
						if($('#blog-types').val() == 2) {
							$('#blog-attach').val(urls[0]);
						}
						textbody.pasteHTML('<img src="' + urls[0] + '" class="feedimg" />');
					} else {
						if($('#blog-types').val() == 2) {
							$('#blog-attach').val(data.msg.url);
						}
						textbody.pasteHTML('<img src="' + data.msg.url + '" class="feedimg" />');
					}
				} else {
					alert(data.err);
				}
			});
			upload.start();
		} else {
			alert('请上传' + sExt + '文件')
		};
	});

	var jUpload = $('#upload_mp3 input');
	jUpload.mousedown(function () {
		textbody.saveBookmark();
	}).change(function () {
		var $this = $(this),
			sExt = $this.attr('ext'),
			$prev = $this.prev();
		if($this.val().match(new RegExp('\.(' + sExt.replace(/,/g, '|') + ')$', 'i'))) {
			var upload = new textbody.html4Upload(this, urlpath + '/index.php?c=publisher&a=uploadmedia', function (sText) {
				$('#uploading').hide();
				var data = Object,
					bOK = false;
				try {
					data = eval('(' + sText + ')');
				} catch (ex) {};
				if(!data.err) {
					iattachMp3(data.msg.fid, data.msg.localname);
				} else {
					alert(data.err);
				}
			});
			upload.start();
			$('#uploading').show();
		} else alert('请上传' + sExt + '文件');
	});

	var jUpload = $('#upload_photo input'),
		$uploading = $('#upload_photo span');
	jUpload.mousedown(function () {
		textbody.saveBookmark();
	}).change(function () {
		var $this = $(this),
			sExt = $this.attr('ext'),
			$prev = $this.prev();
		if($this.val().match(new RegExp('\.(' + sExt.replace(/,/g, '|') + ')$', 'i'))) {
			var upload = new textbody.html4Upload(this, urlpath + '/index.php?c=user&a=upavatar', function (sText) {

				var data = Object,
					bOK = false;
				try {
					data = eval('(' + sText + ')');
				} catch (ex) {
					alert(sText)
				};
				if(!data.err) {
					textbody.loadBookmark();
					$uploading.html('已完成');
					tipok('头像上传完成');
					setTimeout(function () {
						window.location.reload()
					}, 2000);
				} else {
					alert(data.err);
				}
			});
			upload.start();
			$uploading.show();
			$uploading.html('loading...');
		} else alert('请上传' + sExt + '文件');
	});

	var qsearch = '添加标签,写一个回车一下'
	if($('#post-tag-input').val() == '') {
		$('#post-tag-input').val(qsearch);
	}
	$('#post-tag-input').click(function () {
		if($('#post-tag-input').val() == qsearch) {
			$('#post-tag-input').val('');
		}
	});
	$('#post-tag-input').blur(function () {
		if($('#post-tag-input').val() == '') {
			$('#post-tag-input').val(qsearch);
		} else {
			var tags = $('#post-tag-input').val();
			$('#post-tag-list').append('<li tag="' + tags + '"><span>' + tags + '</span><a href="javascript:;" onclick="remTags(this)" title="删除">x</a></li>');
			$('#post-tag-input').val('');
		}
	});

	$('.globox .trg:even').addClass("alt-row");

	$('#preview').click(function () {
		textbody.exec('Preview');
	});
	$('#cancel').click(function () {
		window.history.go(-1);
	});

	$('#draft').click(function () {
		$('#blog-open').val(0);
		if($('#textarea').val() == '') {
			$.dialog({
				icon: 'alert',
				content: '内容不能为空喔',
				time: 2,
				fixed: true
			});
			return false;
		}
		$('#form_publish').submit();
	});

	$('#post-tag-input').bind('keyup', function (event) {
		if(event.keyCode == "13") {
			var tags = $('#post-tag-input').val();
			if(tags != '') $('#post-tag-list').append('<li tag="' + tags + '"><span>' + tags + '</span><a href="javascript:;" onclick="remTags(this)" title="删除">x</a></li>');
			$('#post-tag-input').val('');
		}
	});

	//保存个人资料修
	$('#submit_baseinfo').click(function () {
		var niname = $('#niname').val();
		var domain = $('#domain').val();
		var signss = $('#sign').val();
		var m_reps = $('#m_rep').val();
		var m_fows = $('#m_fow').val();
		var m_pms = $('#m_pm').val();

		var tag_str = '';
		$('#post-tag2 li').each(function () {
			tag_str += $(this).attr('tag') + ',';
		})
		chks = /^[a-zA-Z]{1}([a-zA-Z0-9]|[._]){1,15}$/;
		if(!chks.exec(domain)) {
			tips('个性域名不符合要求');
			return false;
		}
		$('#tag').val(tag_str); //写入标签
		$('#userSetting').submit();
		$('#pb-submiting-tip,#submit_baseinfo,#chgpwd,#cancel').toggle();
	});
	
	//修改密码弹出框
	$('#chgpwd').click(function () {
		var dls = $.dialog({
			content: document.getElementById('pwd_wrap'),
			lock: true,
			title: '修改密码',
			button: [{
				name: '保存修改',
				callback: function () {
					var pwd = $('#pwd');
					var pwd1 = $('#pwd1');
					var pwd2 = $('#pwd2');
					if(pwd.val() == '') {
						pwd.focus();
						return false;
					}
					if(pwd1.val() == '') {
						pwd1.focus();
						return false;
					}
					if(pwd2.val() == '') {
						pwd2.focus();
						return false;
					}
					$('#loadings').toggle();
					this.button({
						name: '保存修改',
						disabled: true
					});
					this.button({
						name: '取消',
						disabled: true
					});
					$.post($('#pwd').attr('submiturl'), {
						'pwd': pwd.val(),
						'pwd1': pwd1.val(),
						'pwd2': pwd2.val()
					}, function (result) {
						$('#loadings').toggle();
						if(result == 'ok') {
							$.dialog({
								id: 'alerts',
								icon: 'success',
								content: '密码成功修改,下次登录不要忘记使用新密码',
								time: 3
							});
							dls.close();
						} else {
							$.dialog({
								id: 'alerts',
								icon: 'alert',
								content: result,
								time: 2
							});
						}

						dls.button({
							name: '保存修改',
							disabled: false
						});
						dls.button({
							name: '取消',
							disabled: false
						});
					});
					return false;
				}
			}, {
				name: '取消'
			}],
			noFn: true
		});
	});
	
	//发布地图
	$("#submit_map").click(function() {
		var title = $("#pb-text-title").val();
		var text = $("#textarea").val();
		var latitude = marker.position.Xa;
		var longitude = marker.position.Ya;
		$("#pb-text-latitude").val(latitude);
		$("#pb-text-longitude").val(longitude);
		if(text == '') {
			tips("内容不能为空喔");
			$("#textarea").focus();
			return false;
		}
		if(!setTags()) {
			tips("亲，定义一个标签呗~回车确定标签");
			return false;
		}
		$("#submit_map,#draft,#preview,#cancel,#pb-submiting-tip").toggle();
		$("#form_publish").submit();
	});
	
	$("#post-tag,#post-tag2").bind("click", function(e) {
		if($('#post-tag-input').val() == qsearch) {
			$('#post-tag-input').val('');
		}
		$("#post-tag-input").focus();
	});

	//发布text
	$('#submit_text').click(function() {
		var title = $('#pb-text-title').val();
		var text = $('#textarea').val();
		if(text == '') {
			tips('内容不能为空喔');
			$('#textarea').focus();
			return false
		}
		if(!setTags()) {
			tips('亲，定义一个标签呗~回车确定标签');
			return false;
		}
		$('#submit_text,#draft,#preview,#cancel,#pb-submiting-tip').toggle();
		$('#form_publish').submit();
	});

	//发布image
	$('#submit_image').click(function() {
		var umus = ''; //获取发布音乐字符串
		$('#uploadArea div').each(function () {
			umus += 1
		}) //获取音乐字串
		if(umus == '') {
			tips('请上传至少一张图片');
			return false;
		}
		if(!setTags()) {
			tips('亲，定义一个标签呗~回车确定标签');
			return false;
		}
		$('#urlmedia').val(umus); //写入数据
		$('#submit_image,#draft,#preview,#cancel,#pb-submiting-tip').toggle();
		$('#form_publish').submit();
	});

	//发布music
	$('#submit_music').click(function() {
		var umus = ''; //获取发布音乐字符串
		$('#mediaList .list').each(function () {
			if($(this).attr("data-type") == "xiami") {
				umus += $(this).attr('data-type') + '|' + $(this).attr('data-img') + '|' + $(this).attr('data-pid') + '|' + $(this).find('input').val() + '|' + $(this).attr('data-url') + '|' + $(this).attr('data-albumName') + '|' + $(this).attr('data-albumUrl') + '|' + $(this).attr('data-singerName') + '|' + $(this).attr('data-singerUrl') + 'LUOMOR';
			} else {
				umus += $(this).attr('data-type') + '|' + $(this).attr('data-img') + '|' + $(this).attr('data-pid') + '|' + $(this).find('input').val() + '|' + $(this).attr('data-url') + 'LUOMOR';
			}
		});
		if(!setTags()) {
			tips('亲，定义一个标签呗~回车确定标签');
			return false;
		}

		if($('#useedit').val() == 1) {
			/*$.dialog({
				content: '您确认使用编辑器中的媒体作为最终发布的内容吗？',
				lock: true,
				yesFn: function () {
					$('#urlmedia').val(umus); //写入数据
					$('#submit_music,#draft,#preview,#cancel,#pb-submiting-tip').toggle();
					$('#form_publish').submit();
				},
				noFn: true
			});*/
			if(umus == '') {
				tips('请添加一个网络音乐或者上传音乐');
				return false;
			}
			$('#urlmedia').val(umus); //写入数据
			$('#submit_music,#draft,#preview,#cancel,#pb-submiting-tip').toggle();
			$('#form_publish').submit();
		} else {
			if(umus == '') {
				tips('请添加一个网络音乐或者上传音乐');
				return false;
			}
			$('#urlmedia').val(umus); //写入数据
			$('#submit_music,#draft,#preview,#cancel,#pb-submiting-tip').toggle();
			$('#form_publish').submit();
		}
	});

	//发布video
	$('#submit_video').click(function() {
		var umus = ''; //获取发布视频字符串
		$('#mediaList .list').each(function () {
			if($(this).attr("data-type") == "sina") {
				umus += $(this).attr('data-type') + '|' + $(this).attr('data-img') + '|' + $(this).attr('data-pid') + '|' + $(this).find('input').val() + '|' + $(this).attr('data-url') + '|' + $(this).attr("data-swfUrl") + 'LUOMOR';
			} else {
				umus += $(this).attr('data-type') + '|' + $(this).attr('data-img') + '|' + $(this).attr('data-pid') + '|' + $(this).find('input').val() + '|' + $(this).attr('data-url') + 'LUOMOR';
			}
		});
		if(umus == '') {
			tips('请添加一个网络视频,并点击保存');
			return false;
		}
		if(!setTags()) {
			tips('亲，定义一个标签呗~回车确定标签');
			return false;
		}
		$('#urlmedia').val(umus); //写入数据
		$('#submit_video,#draft,#preview,#cancel,#pb-submiting-tip').toggle();
		$('#form_publish').submit();
	});
});

function postoff() {
	$('#pb-submiting-tip,#submit_baseinfo,#chgpwd,#cancel').toggle();
}

function setTags() {
	var tag_str = '';
	$('#post-tag-list li').each(function () {
		tag_str += $(this).attr('tag') + ',';
	})
	$('#blog-tags').val(tag_str); //写入标签
	if($('#blog-tags').val() == '') {
		return false
	} else {
		return true
	}
}

//网络音乐
function selectLink() {
	$('#mediaFrom').show();
	$('#mediaUpload').hide();
	$('#useedit').val(0);
	$('#mountchange ul li').removeClass('curr');
	$('#url_link').addClass('curr');
}

//本地音乐
function selectUpload(that) {
	$('#mediaFrom').hide();
	$('#mediaUpload').show();
	$('#useedit').val(1)
	$('#mountchange ul li').removeClass('curr');
	$('#url_upload').addClass('curr');
}

//判断添加网络音乐的mouseover事件
function musicMouse(thisa) {
	if($(thisa).val() == 'http://' || $(thisa).val() == '介绍(选填)') {
		$(thisa).val('');
	}
}
//判断添加网络音乐的mouseout的事件
function musicMosout(thisa, t) {
	if($(thisa).val() == '' && t == 'u') {
		$(thisa).val('http://');
	}
	if($(thisa).val() == '' && t == 'c') {
		$(thisa).val('介绍(选填)');
	}
}

//保存一个条目
function saveMusicList(url) {
	saveMediaList(url, "music");
}

//保存一个条目
function saveVideoList(url) {
	saveMediaList(url, "video")
}

function saveMediaList(url, type) {
	var url = $('#mediaUrl').val();
	if(url == 'http://') {
		tips('请填写一个引用地址');
		return false;
	}

	$("#mediaFrom").disable();
	$("#urlParseLoading").val('正在解析...');
	$.post(urlpath + '/index.php?c=publisher&a=media', {
		'url': url,
		'type': type
	}, function (result) {
		$("#mediaFrom").enable();
		$("#urlParseLoading").val('添加地址');
		var data = eval("(" + result + ")");
		if(data.error != undefined) {
			tips(data.error);
			return false;
		}
		if(data.type == 'mp3' || data.type == 'wma' || data.type == 'swf') {
			data.img = 'tpl/images/publisher/webmusic.png';
		}
		desc = data.title;
		html = "";
		if(data.type == "xiami") {
			html += '<li class="list" data-type="' + data.type + '" data-pid="' + data.id + '" data-img="' + data.img + '" data-url="' + url + '" data-albumName="' + data.albumName + '" data-albumUrl="' + data.albumUrl + '" data-singerName="' + data.singerName + '" data-singerUrl="' + data.singerUrl + '">';
		} else if(data.type == "sina") {
			html += '<li class="list" data-type="' + data.type + '" data-pid="' + data.id + '" data-img="' + data.img + '" data-url="' + url + '" data-swfUrl="' + data.swfUrl + '">';
		} else {
			html += '<li class="list" data-type="' + data.type + '" data-pid="' + data.id + '" data-img="' + data.img + '" data-url="' + url + '">';
		}
		html += '<div class="uri">已添加：';
		html += '<input type="text" name="mediaList[' + data.id + ']" value="' + desc + '" />';
		html += '</div>';
		html += '<a href="javascript:void(0)" onclick="mediaDItem(this, 1, \'' + data.id + '\')">移除</a>';
		html += '</li>';
		$('#mediaList').prepend(html);
		$('#pb-text-title').val(desc);
		$('#mediaUrl').val('http://');
	});
}

//删除多媒体发布的一个条目 DOM
function mediaDItem(that, type, id) {
	$(that).parent().remove();
	if(type == 2) {
		$("#attach_" + id).show();
	}
}

//添加MP3类型媒体 如果是localmusic则说明是在音乐模型
function iattachMMouse(that, id) {
	if(id == 0) {
		if($(that).val() == '描述') {
			$(that).val('');
		}
	}
	if(id == 1) {
		if($(that).val() == '') {
			$(that).val('描述');
		}
	}
}

//remove附件
function removeIattachMp3(that, id) {
	$(that).parent().parent().remove();
	$('#attach_' + id).show();
}
//本地音乐
function iattachMp3(id, name) {
	if($('#blog-types').val() == 3) {
		var html = '<li class="list" data-type="local" data-pid="attach_' + id + '" data-img="0" data-url="">';
		html += '<div class="uri">已添加： ';
		html += '<input type="text" name="localMusic[' + id + ']" value="' + name + '" />';
		html += '</div>';
		html += '<a href="javascript:void(0)" onclick="mediaDItem(this, 2, \'' + id + '\')">移除</a>';
		html += '</li>';
		$('#mediaList').prepend(html);
		$("#pb-text-title").val(name);
		$('#attach_' + id).hide();
	}
}

function iattachBigImg(x) {
	var x = x.split('|');
	if($('#blog-types').val() == 2) {
		$('#blog-attach').val(x[1]);
	}
	textbody.pasteHTML('<a href="' + x[0] + '" target="_blank"><img src="' + x[1] + '" alt="" class="feedimg"/></a>')
}

function iattachImg(x) {
	if($('#blog-types').val() == 2) {
		$('#blog-attach').val(x);
	}
	textbody.pasteHTML('<img src="' + x + '" alt="" class="feedimg"/>')
}

//删除附件
function delAttach(id) {
	$.dialog({
		content: '确认删除附件？',
		lock: true,
		yesFn: function () {
			$.post(urlpath + '/index.php?c=publisher&a=delattach', {
				'id': id
			}, function (result) {
				if(result == 'ok') {
					$('#attach_' + id).hide();
					tips('已删除');
				} else {
					tips('请稍后再试');
				}
			})
		},
		noFn: true
	});
}

//删除附件 图片模块
function delAttachIMAGE(id) {
	$.dialog({
		content: '确认删除附件？',
		lock: true,
		yesFn: function () {
			$.post(urlpath + '/index.php?c=publisher&a=delattach', {
				'id': id
			}, function (result) {
				if(result == 'ok') {
					$('#attach_' + id).remove();
				} else {
					tips('请稍后再试');
				}
			})
		},
		noFn: true
	});
}

//删除tag
function remTags(x) {
	$(x).parent().remove();
}
//从推荐列表选择tag
function tuiTag(x, y) {
	$('#post-tag-list').append('<li tag="' + x + '"><span>' + x + '</span><a href="javascript:;" onclick="remTags(this)" title="删除">x</a></li>');
	$(y).parent().remove();
}

//编辑器插入媒体
function iattach(x, y) {
	var x = x.split('|');
	if(x[0] == 'img') {
		if(x[2] == undefined) {//如果不存在缩略图
			parent.textbody.pasteHTML('<img src="' + x[1] + '" />');
		} else {
			parent.textbody.pasteHTML('<a href="' + x[1] + '" target="_blank"><img src="' + x[2] + '" alt="" /></a>')
		}
	} else if(x[0] == 'mp3' || x[0] == 'mid' || x[0] == 'midi' || x[0] == 'wma') {
		parent.textbody.pasteHTML('[music]' + x[1] + x[2] + '[/muisc]');
	} else {
		parent.textbody.pasteHTML('<a href="' + x[2] + '">' + x[1] + '</a>');
	}
}