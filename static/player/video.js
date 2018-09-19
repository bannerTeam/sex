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

MacPlayer.Html = '';
MacPlayer.Show();
setTimeout(function(){
	
	$("#playleft").append('<video id="roomVideo1" class="video-js vjs-big-play-centered" controls preload="none" ></video>');
				
	var w = $(".MacPlayer").width();
	var h = $(".MacPlayer").height();
	var myPlayer = videojs('roomVideo1',{
		autoplay:true,
        //poster: "封面",
        height:h, 
		width:w
   });
   myPlayer.src(MacPlayer.PlayUrl);

}, MacPlayer.Second * 1000 - 1000);



