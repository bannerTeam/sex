$(function() {

	//计算导航宽度
	var lis = $("#head-nav li"),
		w = 0;
	lis.each(function() {
		w += $(this).innerWidth();
	});
	$("#head-nav").width(w + 12);

	$("#find").bind("click", F_side);

});

function F_side() {
	$("#nav").hasClass("out") ? ($("#nav").removeClass("out"), $("#find").addClass("active").find("i").removeClass("ico08").addClass("ico19").find("img").attr({
		src: tpl_url + "/img/ss2.png"
	}), $("#cover").css({
		display: "block"
	})) : ($("#find").removeClass("active").find("i").removeClass("ico19").addClass("ico08").find("img").attr({
		src: tpl_url + "/img/ss1.png"
	}), $("#nav").addClass("out"), $("#cover").css({
		display: "none"
	}))
}

function pagination(options) {
	$("#loading").hide();
	var idx = location.href.indexOf("//"),
		param = location.href.substr(idx + 2).replace(location.host + "/", "");

	var search = decodeURI(window.location.search.substr(1));

	var settings = {
		e: "#vlist",
		loading: true,
		url: "/ajax/video_list",
		page: 2,
		c: 0,
		data: {
			search_query: GetQueryString(search, "search_query"),
			o: GetQueryString(search, "o"),
			t: GetQueryString(search, "t")
		},
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

	function get_list() {
		$("#loading").show();
		o.loading = false;
		$.ajax({
			type: "POST",
			url: o.url,
			dataType: "json",
			data: {
				param: param,
				c: o.c,
				search_query: o.data.search_query,
				o: o.data.o,
				t: o.data.t,
				page: o.page
			},
			success: function(data) {
				
				if(data.ps.page == o.page && o.fn && $.isFunction(o.fn)) {
					o.fn(data);
				}
				
				if(data.ps.total_pages > data.ps.page) {
					o.loading = true;
					o.page = data.ps.page + 1;
				}
				else {
					$("#loading").hide();
				}
				
			}
		});
	}

	function successFn(data) {

		var items = data.list,
			str = "";

		for(var i = 0; i < items.length; i++) {
			str += `<li style="width: 49.5%;">
				<div class="ui-grid-trisect-img" style="padding-top: 54.47%;"><span style="background-image:url('${items[i].thumb_img}')"></span>
					<div class="cnl-tag tag">
						${items[i].duration}
					</div>
				</div>
				<h4 class="ui-nowrap" style="font-size: 100%;font-weight: 400;text-align:center"><a href="/video/${items[i].VID}/${items[i].title}" >${items[i].title}</a></h4>
			</li>`;
		}

		$(o.e).append(str);
	}

	function GetQueryString(_url, name) {
		var reg = new RegExp("(^|&)" + name + "=([^&]*)(&|$)");
		var r = _url.match(reg);
		if(r != null) return unescape(r[2]);
		return null;
	}

}