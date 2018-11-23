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
	//var idx = location.href.indexOf("//"),
	//	param = location.href.substr(idx + 2).replace(location.host + "/", "");

	//var search = decodeURI(window.location.search.substr(1));

	var settings = {
		e: "#vlist",
		loading: true,
		url: "/ajax/get_vod_list",
		page: 2,
		limit:12,
		tid: 0,
		timeadd:0,
		by:"",
		wd: "",		
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
			url: o.url,
			dataType: "json",
			data: {
				by: o.by,
				timeadd: o.timeadd,
				tid: o.tid,
				wd: o.wd,
				page: o.page,
				limit:o.limit
			},
			success: function(data) {
				
				if(data.page == o.page && o.fn && $.isFunction(o.fn)) {
					o.fn(data);
				}
				
				if(data.total_pages > data.page) {
					o.loading = true;
					o.page = data.page + 1;
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
				<div class="ui-grid-trisect-img" style="padding-top: 54.47%;"><span style="background-image:url('${items[i].vod_pic}')"></span>
					<div class="cnl-tag tag">
						${date('m-d',items[i].vod_time_add)}
					</div>
				</div>
				<h4 class="ui-nowrap" style="font-size: 100%;font-weight: 400;text-align:center"><a href="/index.php/vod/detail/id/${items[i].vod_id}.html" >${items[i].vod_name}</a></h4>
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

/** 
 * 时间戳格式化函数 
 * @param  {string} format    格式 
 * @param  {int}    timestamp 要格式化的时间 默认为当前时间 
 * @return {string}           格式化的时间字符串 
 */
function date(format, timestamp){  
    var a, jsdate=((timestamp) ? new Date(timestamp*1000) : new Date()); 
    var pad = function(n, c){ 
        if((n = n + "").length < c){ 
            return new Array(++c - n.length).join("0") + n; 
        } else { 
            return n; 
        } 
    }; 
    var txt_weekdays = ["Sunday", "Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday"]; 
    var txt_ordin = {1:"st", 2:"nd", 3:"rd", 21:"st", 22:"nd", 23:"rd", 31:"st"}; 
    var txt_months = ["", "January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December"];  
    var f = { 
        // Day 
        d: function(){return pad(f.j(), 2)}, 
        D: function(){return f.l().substr(0,3)}, 
        j: function(){return jsdate.getDate()}, 
        l: function(){return txt_weekdays[f.w()]}, 
        N: function(){return f.w() + 1}, 
        S: function(){return txt_ordin[f.j()] ? txt_ordin[f.j()] : 'th'}, 
        w: function(){return jsdate.getDay()}, 
        z: function(){return (jsdate - new Date(jsdate.getFullYear() + "/1/1")) / 864e5 >> 0}, 
        
        // Week 
        W: function(){ 
            var a = f.z(), b = 364 + f.L() - a; 
            var nd2, nd = (new Date(jsdate.getFullYear() + "/1/1").getDay() || 7) - 1; 
            if(b <= 2 && ((jsdate.getDay() || 7) - 1) <= 2 - b){ 
                return 1; 
            } else{ 
                if(a <= 2 && nd >= 4 && a >= (6 - nd)){ 
                    nd2 = new Date(jsdate.getFullYear() - 1 + "/12/31"); 
                    return date("W", Math.round(nd2.getTime()/1000)); 
                } else{ 
                    return (1 + (nd <= 3 ? ((a + nd) / 7) : (a - (7 - nd)) / 7) >> 0); 
                } 
            } 
        }, 
        
        // Month 
        F: function(){return txt_months[f.n()]}, 
        m: function(){return pad(f.n(), 2)}, 
        M: function(){return f.F().substr(0,3)}, 
        n: function(){return jsdate.getMonth() + 1}, 
        t: function(){ 
            var n; 
            if( (n = jsdate.getMonth() + 1) == 2 ){ 
                return 28 + f.L(); 
            } else{ 
                if( n & 1 && n < 8 || !(n & 1) && n > 7 ){ 
                    return 31; 
                } else{ 
                    return 30; 
                } 
            } 
        }, 
        
        // Year 
        L: function(){var y = f.Y();return (!(y & 3) && (y % 1e2 || !(y % 4e2))) ? 1 : 0}, 
        //o not supported yet 
        Y: function(){return jsdate.getFullYear()}, 
        y: function(){return (jsdate.getFullYear() + "").slice(2)}, 
        
        // Time 
        a: function(){return jsdate.getHours() > 11 ? "pm" : "am"}, 
        A: function(){return f.a().toUpperCase()}, 
        B: function(){ 
            // peter paul koch: 
            var off = (jsdate.getTimezoneOffset() + 60)*60; 
            var theSeconds = (jsdate.getHours() * 3600) + (jsdate.getMinutes() * 60) + jsdate.getSeconds() + off; 
            var beat = Math.floor(theSeconds/86.4); 
            if (beat > 1000) beat -= 1000; 
            if (beat < 0) beat += 1000; 
            if ((String(beat)).length == 1) beat = "00"+beat; 
            if ((String(beat)).length == 2) beat = "0"+beat; 
            return beat; 
        }, 
        g: function(){return jsdate.getHours() % 12 || 12}, 
        G: function(){return jsdate.getHours()}, 
        h: function(){return pad(f.g(), 2)}, 
        H: function(){return pad(jsdate.getHours(), 2)}, 
        i: function(){return pad(jsdate.getMinutes(), 2)}, 
        s: function(){return pad(jsdate.getSeconds(), 2)}, 
        //u not supported yet 
        
        // Timezone 
        //e not supported yet 
        //I not supported yet 
        O: function(){ 
            var t = pad(Math.abs(jsdate.getTimezoneOffset()/60*100), 4); 
            if (jsdate.getTimezoneOffset() > 0) t = "-" + t; else t = "+" + t; 
            return t; 
        }, 
        P: function(){var O = f.O();return (O.substr(0, 3) + ":" + O.substr(3, 2))}, 
        //T not supported yet 
        //Z not supported yet 
        
        // Full Date/Time 
        c: function(){return f.Y() + "-" + f.m() + "-" + f.d() + "T" + f.h() + ":" + f.i() + ":" + f.s() + f.P()}, 
        //r not supported yet 
        U: function(){return Math.round(jsdate.getTime()/1000)} 
    }; 
    
   //  /[\]?([a-zA-Z])/g
    return format.replace(/([a-zA-Z])/g, function(t, s){ 
        if( t!=s ){ 
            // escaped 
            ret = s; 
        } else if( f[s] ){ 
            // a date function exists 
            ret = f[s](); 
        } else{ 
            // nothing special 
            ret = s; 
        } 
        return ret; 
    }); 
}