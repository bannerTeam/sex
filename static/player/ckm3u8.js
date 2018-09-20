//MacPlayer.Html = '<iframe src="/m3u8.php?url='+MacPlayer.PlayUrl+'" height="'+MacPlayer.Height+'" width="100%" scrolling="no" id="Player"></iframe>';
//MacPlayer.Show();

if(/Safari/.test(navigator.userAgent) && !/Chrome/.test(navigator.userAgent)){
	document.write('<script type="text/javascript" src="'+ maccms.path +'/static/player/video.js"></script>');
}else{
	document.write('<script type="text/javascript" src="'+ maccms.path +'/static/player/ckplayer/ckplayer.js"></script>');
	MacPlayer.Html = '';
	MacPlayer.Show();
	
	setTimeout(function(){
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
	    	advertisements:'/Adv/get_adv'
	    };
	    var player = new ckplayer(videoObject);    
	   
	
	}, MacPlayer.Second * 1000 - 1000);
}




