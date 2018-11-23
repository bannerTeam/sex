function loadCss(src) {
	var cssTag = document.getElementById('loadCss');
	var head = document.getElementsByTagName('head').item(0);
	if(cssTag) head.removeChild(cssTag);
	css = document.createElement('link');
	css.href = src;
	css.rel = 'stylesheet';
	css.type = 'text/css';
	css.id = 'loadCss';
	head.appendChild(css);
}

function jsnull() {
	return
}
var MacPlayer = {
	Adv: {}
};
var player = null;
var dtime = null;
$(document).ready(function($) {

	if($(window).width() < 620) {
		var ww = $(window).width(),
			w = (ww < 620) ? $("body").width() : ww,
			h = w / 16 * 9;
	
		MacPlayer.w = w;
		MacPlayer.h = h;
	
		$("#video").css({
			width: MacPlayer.w,
			height: MacPlayer.h
		});
		$("#video_adv").css({
			width: MacPlayer.w,
			height: MacPlayer.h
		});
		$("#video-container").css({
			height: MacPlayer.h
		});
		
	}else{
		var w = $(".video-play").width(),
		h = w / 16 * 9;
		MacPlayer.w = w;
		MacPlayer.h = h;
		
		$("#video").css({
			width: MacPlayer.w,
			height: MacPlayer.h
		});
		$("#video_adv").css({
			width: MacPlayer.w,
			height: MacPlayer.h
		}).hide();
		$("#video-container").css({
			height: MacPlayer.h
		});
		
	}
	

	if(/Safari/.test(navigator.userAgent) && !/Chrome/.test(navigator.userAgent)) {
		loadCss("/static/player/video/video.css");
		$("head").append('<script type="text/javascript" src="/static/player/video/video.min.js"></script>');
		$("head").append('<script type="text/javascript" src="/static/player/video/videojs-contrib-hls.js"></script>');
		$("#video").hide();
		yhVideo();
		return false;
	}

	if(videoObject.uvip == "1") {
		videoObject.advertisements = "";
	}

	if($(window).width() < 620) {
		$("#video").hide();
		videoObject.advertisements = "";
		getPlayerAdv(function() {
			player = new ckplayer(videoObject);
		});
	} else {
		player = new ckplayer(videoObject);
	}

});

function yhVideo() {

	getPlayerAdv(function() {

		var playUrl = videoObject.video[0][0];

		$("#video").append(`<video id="roomVideo1" class="video-js vjs-big-play-centered" controls preload="none" ><source id="source" src="${playUrl}" type="application/x-mpegURL"></video>`);

		var myPlayer = videojs('roomVideo1', {
			autoplay: false,
			height: MacPlayer.h,
			width: MacPlayer.w
		});

		myPlayer.src(playUrl);

	});

	return false;

}

function getPlayerAdv(fn) {

	if(videoObject.uvip == "1") {
		if(fn && $.isFunction(fn)) {
			fn();
		}
		$("#video").show();
		$("#video_adv").hide();
		return false;
	}

	$.ajax({
		type: "get",
		url: "/Adv/get_adv",
		dataType: "json",
		success: function(ret) {
			MacPlayer.Adv = {
				front: ret.front[0],
				pause: ret.pause[0]
			};

			if(MacPlayer.Adv.front) {

				var ti = parseInt(MacPlayer.Adv.front.time);

				$("#video_adv").append(`<div id="ad_front" style="" class="ad_box">
						<div><span id="adv_count_down" class="adv_count_down"></span><a target="_blank" href="${MacPlayer.Adv.front.link}"><img src="${MacPlayer.Adv.front.file}" /></a></div>						
					</div>`);

				var aaa = setInterval(function() {
					$("#adv_count_down").text('(' + ti + 's)');
					ti--;
					if(ti < 0) {
						clearInterval(aaa);
						$("#video").show();
						$("#video_adv").hide();
						if(fn && $.isFunction(fn)) {
							fn();
						}
					}
				}, 1000);
			}

			if(MacPlayer.Adv.pause) {
				$("#video_adv").append(`<div id="ad_pause" style="display: none;" class="ad_box">
						<div><a target="_blank" href="${MacPlayer.Adv.pause.link}"><img src="${MacPlayer.Adv.pause.file}" /></a></div>
						<span href="javascript:;" class="ad_close"><span class="ad_close_s"></span></span>
					</div>`);
			}

			$("#video_adv img").css({
				"max-height": MacPlayer.h,
				"max-width": MacPlayer.w
			});

		}
	});

}
