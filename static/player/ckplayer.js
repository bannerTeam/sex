/**
 * 判断是否是移动端
 */
function getMobileBrowser(){
	if(/Safari/.test(navigator.userAgent) && !/Chrome/.test(navigator.userAgent)){
		return true;
	}
//	else if(/Android/.test(navigator.userAgent) && MacPlayer.PlayUrl.indexOf(".m3u8")>0){
//		return true;
//	}
	return false;
}
if(getMobileBrowser()){
	var pl = $("#playleft"),w = $(window).width(),h = w/16*9;		
	pl.css({
		width:w,
		height:h
	});
	document.write('<script type="text/javascript" src="'+ maccms.path +'/static/player/video.js"></script>');
}else{
	document.write('<script type="text/javascript" src="'+ maccms.path +'/static/player/ckplayer/ckplayer.js"></script>');
	
	
	if(/Android/.test(navigator.userAgent)){	
		var pl = $("#playleft"),w = $(window).width(),h = w/16*9;		
		pl.css({
			width:w,
			height:h
		});	
		$(".MacPlayer").css({
			height:h
		});	
		getCkPlayerAdv();
		$("#buffer").hide();
		$("#install").hide();
	}else{
		MacPlayer.Html = '';
		MacPlayer.Show();
		loadCkPlayer();	
	}
}

function loadCkPlayer(){	
	var r = typeof(ckplayer);
	if(r && r.toLowerCase() == "function"){
		var video = [
	        [
	            MacPlayer.PlayUrl,
	            "video/mp4"
	        ],
	        [
	            MacPlayer.PlayUrl,
	            "video/m3u8",
	        ],
	        [
	            MacPlayer.PlayUrl,
	            "video/ogg",
	        ],
	        [
	            MacPlayer.PlayUrl,
	            "video/webm",
	        ]
	    ];
	    var videoObject = {
	        container: '#playleft', //容器的ID或className
	        variable: 'player',//播放函数名称
	        //flashplayer:true,
	        autoplay:true,
	        video: MacPlayer.PlayUrl,
	    	advertisements:'/Ajax/get_playbanner'
	    };
	    var player = new ckplayer(videoObject); 
	    
	}else{
		setTimeout(function(){
			loadCkPlayer()
		},2000);
	}	
	
	
}


function getCkPlayerAdv(){
	
	$.ajax({
		type:"get",
		url:"/Ajax/get_playbanner",
		success:function(ret){
			MacPlayer.Adv = {
				front:ret.front[0],
				pause:ret.pause[0]
			};
			
			if(MacPlayer.Adv.front){
				pl.append(`<div id="ad_front" style="text-align: center;" class="ad_box">
					<div><span id="adv_count_down" style="position: absolute; right: 10px;top: 10px; background: #000000; color: #FFFFFF;padding:5px 15px;font-size:14px;" class="adv_count_down"></span><a target="_blank" href="${MacPlayer.Adv.front.link}"><img src="${MacPlayer.Adv.front.file}" /></a></div>					
				</div>`);
				var ti = parseInt(MacPlayer.Adv.front.time);
				var aaa = setInterval(function(){
						$("#adv_count_down").text('('+ti+'s)');
						ti--;
						if(ti < 0){
							clearInterval(aaa);
							$("#ad_front").remove();
							loadCkPlayer();
						}
					},1000);
				
			}
			
			if(MacPlayer.Adv.pause){
				pl.append(`<div id="ad_pause" style="display: none; text-align: center;" class="ad_box">
					<div><a target="_blank" href="${MacPlayer.Adv.pause.link}"><img src="${MacPlayer.Adv.pause.file}" /></a></div>
				</div>`);
			}			
			pl.find(".ad_box a img").css({
				"max-height":h, 
				"max-width":w
			});			
			
		}
	});	
}


