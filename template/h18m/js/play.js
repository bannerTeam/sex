function pageInit(options) {

	
	var settings = {
		e: "#rlist",
		loading: true,
		url: "/ajax/get_vod_relevant",
		page: 2,
		limit: 12,
		id: 0,
		tid: 0,
		timeadd: 0,
		by: "",
		wd: "",
		fn: successFn
	};
	var o = $.extend(settings, options);

	/**
	 * 获取列表
	 */
	function get_list() {
		$("#loading").show();
		o.loading = false;
		$.ajax({
			url: o.url,
			dataType: "json",
			data: {
				by: o.by,
				timeadd: o.timeadd,
				tid: o.tid,
				wd: o.wd,
				page: o.page,
				limit: o.limit
			},
			success: function(data) {

				if(o.fn && $.isFunction(o.fn)) {
					o.fn(data);
				}

				$("#loading").hide();

			}
		});
	}

	function successFn(data) {

		var items = data.list,
			str = "";

		for(var i = 0; i < items.length; i++) {
			str += `<li style="width: 49.5%;">
				<a href="/vod/detail/id/${items[i].vod_id}.html" >
				<div class="ui-grid-trisect-img" style="padding-top: 54.47%;"><span style="background-image:url('${items[i].vod_pic}')"></span>
					<div class="cnl-tag tag">
						${date('m-d',items[i].vod_time_add)}
					</div>
				</div>
				</a>
				<h4 class="ui-nowrap" style="font-size: 100%;font-weight: 400;text-align:center"><a href="/vod/detail/id/${items[i].vod_id}.html" >${items[i].vod_name}</a></h4>
			</li>`;
		}

		$(o.e).append(str);
	}

	/**
	 * 格式化日期
	 * @param {Object} sj
	 */
	function Rmd(sj) {
		var now = new Date(sj * 1000);
		var year = now.getFullYear();
		var month = now.getMonth() + 1;
		var date = now.getDate();
		return month + " - " + date;
	}

	$("#collection").click(function() {
		h18.login(true);
		if(!is_collection) {
			Collection(this);
		}
		$("#collection span").text("已收藏");
		tips("已收藏");
	})
	/**
	 * 收藏
	 */
	function Collection(obj) {
		if($(obj).data('loading')) {
			return false;
		}
		$(obj).data('loading', true);
		$.ajax({
			url: "/index.php/user/ajax_collection",
			dataType: "json",
			data: {
				ac: "set",
				id: o.id
			},
			success: function(data) {
				is_collection = true;
				if(data.code == 1) {
					$("#collection span").text("已收藏");
				}
				tips(data.msg);
			}
		});
	}
	/**
	 * 获取收藏状态
	 */
	function get_collection() {
		!h18.login() && $.ajax({
			url: "/index.php/user/ajax_collection",
			dataType: "json",
			data: {
				ac: "get",
				id: o.id
			},
			success: function(data) {
				if(data.code == 1) {
					is_collection = true;
					$("#collection span").text("已收藏");
				}
			}
		});
	}
	get_collection();

	/**
	 * 获取影片 的 上一部，下一部
	 */
	function get_front_after() {
		$.ajax({
			url: "/vod/ajax_front_after",
			dataType: "json",
			data: {
				id: o.id
			},
			success: function(data) {
				if(data.after != '') {
					$("#front_after").attr('href', "/vod/detail/id/" + data.after);
				} else if(data.front != '') {
					$("#front_after").attr('href', "/vod/detail/id/" + data.front).val("上一部");
				}
			}
		});
	}
	get_front_after();

	var is_collection = false,
		is_up = false,
		is_down = false;

	$(".j-up").click(function() {
		h18.login(true);
		if(!is_up) {
			up_down(this, 3);
			is_up = true;
		} else {
			tips("已顶");
		}
	})
	$(".j-down").click(function() {
		h18.login(true);
		if(!is_down) {
			up_down(this, 6);
			is_down = true;
		} else {
			tips("已踩");
		}
	});

	/**
	 * 获取状态
	 */
	function get_up_down() {
		!h18.login() && $.ajax({
			url: "/index.php/user/ajax_up_down",
			dataType: "json",
			data: {
				ac: "get",
				id: o.id
			},
			success: function(data) {
				if(data.up > 0) {
					is_up = true;
				}
				if(data.down > 0) {
					is_down = true;
				}
			}
		});
	}
	get_up_down();

	/**
	 * 顶数/顶数
	 * 3想看（顶）; 6.不想看（踩）
	 */
	function up_down(obj, type) {
		if($(obj).data('loading')) {
			return false;
		}
		$(obj).data('loading', true);
		$.ajax({
			url: "/index.php/user/ajax_up_down",
			dataType: "json",
			data: {
				ac: "set",
				id: o.id,
				type: type
			},
			success: function(data) {
				if(data.code == 1) {
					if(type == 3) {
						$("#up").text(Number($.trim($("#up").text())) + 1);
					} else {
						$("#down").text(Number($.trim($("#down").text())) - 1);
					}
					tips("成功");
				}

			}
		});
	}

	function tips(msg) {
		layer.open({
			content: msg,
			skin: 'msg',
			time: 2
		});
	}

	get_list();

}