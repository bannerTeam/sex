function pageInit(options) {

	$("#loading").hide();
	var settings = {
		e: "#vlist",
		loading: true,
		url: "/ajax/get_favs",
		page: 2,
		limit: 12,
		id: 0,
		tid: 0,
		fn: successFn
	};
	var o = $.extend(settings, options);
	
	var totalHeight = 0;
	$(window).scroll(function() {
		//浏览器的高度加上滚动条的高度
		totalHeight = parseFloat($(window).height()) + parseFloat($(window).scrollTop());
		//当文档的高度小于或者等于总的高度时，开始动态加载数据
		if($(document).height() <= totalHeight + 100) {
			if(o.loading) {
				get_list();
			}
		}
	});
	
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
				<div class="ui-grid-trisect-img" style="padding-top: 54.47%;"><span style="background-image:url('${items[i].data.pic}')"></span>
					<div class="cnl-tag tag">
						${items[i].data.timeadd}
					</div>
					<div class="delete" data-id="${items[i].ulog_id}"><i></i>移除</div>
				</div>
				<h4 class="ui-nowrap" style="font-size: 100%;font-weight: 400;text-align:center"><a href="/index.php/vod/detail/id/${items[i].data.id}.html" >${items[i].data.name}</a></h4>
			</li>`;
		}

		$(o.e).append(str);
	}


	$("#vlist").on("click", ".delete", function() {	
		var obj = this,id = $(this).data('id');
		layer.open({
			content: '您确定要删除吗？',
			btn: ['确定', '取消'],
			yes: function(index) {				
				layer.close(index);
				delCollection(id);
				$(obj).parents('li').fadeOut("slow");
			}
		});
	})

	
	/**
	 * 收藏
	 */
	function delCollection(id) {
		$.ajax({
			url: "/index.php/user/ajax_collection",
			dataType: "json",
			data: {
				ac: "del",
				id: id,
				type: 2
			}
		});
	}

}