function loadCss(src){
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
loadCss(maccms.path +"/static/player/video/video.css");

document.write('<script type="text/javascript" src="'+ maccms.path +'/static/player/video/video.min.js"></script>');
document.write('<script type="text/javascript" src="'+ maccms.path +'/static/player/video/videojs-contrib-hls.js"></script>');


function getPlayerAdv(){
				
	$.ajax({
		type:"get",
		url:"/Adv/get_adv",
		success:function(ret){
			MacPlayer.Adv = {
				front:ret.front[0],
				pause:ret.pause[0]
			};
			
			if(MacPlayer.Adv.front){
				$("#playleft").append(`<div id="ad_front" style="" class="ad_box">
					<div><a target="_blank" href="${MacPlayer.Adv.front.link}"><img src="${MacPlayer.Adv.front.file}" /></a></div>
					<span href="javascript:;" class="ad_close"><span class="ad_close_s"></span></span>
				</div>`);
			}
			
			if(MacPlayer.Adv.pause){
				$("#playleft").append(`<div id="ad_pause" style="display: none;" class="ad_box">
					<div><a target="_blank" href="${MacPlayer.Adv.pause.link}"><img src="${MacPlayer.Adv.pause.file}" /></a></div>
					<span href="javascript:;" class="ad_close"><span class="ad_close_s"></span></span>
				</div>`);
			}
			
			
		}
	});
	
}

getPlayerAdv();
MacPlayer.Html = '';
MacPlayer.Show();

setTimeout(function(){
	
	$("#playleft").append(`<video id="roomVideo1" class="video-js vjs-big-play-centered" controls preload="none" ></video>`);
				
	var mp = $(".MacPlayer"),w = mp.width(), h = mp.height();
	var myPlayer = videojs('roomVideo1',{
		autoplay:false,
        //poster: "封面",
        height:h, 
		width:w
  },function(){
  		//暂停
		this.on('pause', function() {
		   $("#ad_pause").show();
		});
   });
   myPlayer.src(MacPlayer.PlayUrl);
   
   
   	//关闭广告
   	$("#playleft").delegate(".ad_close","click",function(){
	 	$("#ad_front").hide();
	 	$("#ad_pause").hide();
   		myPlayer.play();
	});   	
	
	if(MacPlayer.Adv.front){
		var ts = MacPlayer.Adv.front.time;
		setTimeout(function(){
			$("#playleft .ad_close").click();   	
		},(ts * 1000));
	}	
   
   
   $(window).resize(function(){
   		w = mp.width();
   		h = mp.height();
   		myPlayer.width(w);
   		myPlayer.height(h);
   });

}, MacPlayer.Second * 1000 - 1000);
