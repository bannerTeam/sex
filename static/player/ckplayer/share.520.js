function crossdomainCheck() {
    if (hosts) {
        var referagent = document.referrer;
        var hostsarr = hosts.split("|");
        var refer = false;
        for (var i = 0; i <= hostsarr.length; i++) {
            if (!refer) {
                if (referagent.indexOf(hostsarr[i]) > 0) {
                    refer = true;
                }
            };
        };
        if (!refer) {
            top.location.href = redirecturl;
        };
    }
    
}

var time = 0;
var _CK_ = null;
var bOpen = 0;
var bObj = null;
var msgcache = {}

crossdomainCheck();
function barrageShowHandler(b) {
    bOpen = b ? 1 : 0;
}
function getRandomArbitrary(min, max) {
    return parseInt(Math.random() * (max - min) + min);
}
function writeBarrage(s) {
    var o = {
        background: {
            backgroundColor: 0x000000, //背景颜色，16进制作0x开头
            borderColor: null, //边框颜色
            radius: 25, //圆角弧度
            alpha: 50, //背景透明度
            height: 32 //高度
        },
        list: [
            {
                type: "text",//说明是文本
                text: s,//文本内容
                color: "#FFCC00",
                size: parseInt(getRandomArbitrary(18, 36)),
                face: "Microsoft YaHei,微软雅黑",
                alpha: 90,//透明度
                left: 10,//左边距离
                right: 10,//右边距离
                top: -2//离元件顶部距离
            }

        ],
        y: parseInt(getRandomArbitrary(2, 95)) + "%",//这里定义了y属性，则表示是水平方向移动，如果不定义y而定义x，则在垂直方向移动，坐标支持数字和百分比
        time: 20,//移动频率，单位：毫秒，例20指每20毫秒移动一次
        step: 1,//移动距离，单位：像素，正的表示向左或向上，小于0则表示向右或向下
        marginX: 20,//x轴修正，因为元件里第一个元素的坐标如果是小于0，则可能开始出现在界面中，此时需要修正一下。
        marginY: 20//同上
    }
    if (getRandomArbitrary(0, 10) < 5) {
        delete o.y;
        o.x = parseInt(getRandomArbitrary(2, 95)) + "%"
    }

    _CK_.addBarrage(o);
}

function sendBarrageHandler(s) {//褰撶敤鎴锋彁浜や簡寮瑰箷鍐呭鍒欒皟鐢ㄨ鍑芥暟
    //alert('发送了' + s);
    //writeBarrage(s);
    senddanmu(s)
}
function initbarrage() {

    _CK_ = CKobject.getObjectById('ckplayer_a1');
    _CK_.addListener('sendBarrage', 'sendBarrageHandler');

    _CK_.addListener('barrageShow', 'barrageShowHandler');

}

function loadedHandler() {
    if (CKobject.getObjectById('ckplayer_a1').getType()) {
        CKobject.getObjectById('ckplayer_a1').addListener('play', playHandler);
        CKobject.getObjectById('ckplayer_a1').addListener('pause', pauseHandler);

    }
    else {
        CKobject.getObjectById('ckplayer_a1').addListener('play', 'playHandler');
        CKobject.getObjectById('ckplayer_a1').addListener('pause', 'pauseHandler');

    }
    initbarrage();
}
function timeHandler(t) {
    if (t > -1) {
        SetCookie(videoid + "_time", t);
        time = t;
        //console.log("set play time" + t)
    }
}
var firstSeek = false;
function playHandler() {
    console.log("playhandler");
    CKobject._K_('yytf').style.display = 'none';
    addTimeListener();
    if (firstSeek == false) {
        firstSeek = true;
        setTimeout(function () {
            CKobject.getObjectById('ckplayer_a1').videoSeek(getCookie(videoid + "_time"));
        }, 10);

    }
}

function removePlayListener() {
    if (CKobject.getObjectById('ckplayer_a1').getType()) {
        CKobject.getObjectById('ckplayer_a1').removeListener('play', playHandler);
    }
    else {
        CKobject.getObjectById('ckplayer_a1').removeListener('play', 'playHandler');
    }
}
function addTimeListener() {
    if (CKobject.getObjectById('ckplayer_a1').getType()) {
        CKobject.getObjectById('ckplayer_a1').addListener('time', timeHandler);
    }
    else {
        CKobject.getObjectById('ckplayer_a1').addListener('time', 'timeHandler');
    }
}

function SetCookie(name, value) {
    var Days = 30;
    var exp = new Date(); //new Date("December 31, 9998");
    exp.setTime(exp.getTime() + Days * 24 * 60 * 60 * 1000);
    document.cookie = name + "=" + escape(value) + ";expires=" + exp.toGMTString();
}
function getCookie(name) {
    var arr = document.cookie.match(new RegExp("(^| )" + name + "=([^;]*)(;|$)"));
    if (arr != null) return unescape(arr[2]); return null;
}
var frontTime = false;
var frontHtime = false;

//settime();
function settime() {
    var nowT = parseInt(CKobject._K_('djs').innerHTML);
    if (nowT > 0) {
        frontTime = true;
        CKobject._K_('djs').innerHTML = nowT - 1;
        setTimeout('settime()', 1000)
    }
    else {
        frontTime = false;
        CKobject._K_('yytf').style.display = 'none';
        CKobject._K_('daojs').style.display = 'none';
        CKobject.getObjectById('ckplayer_a1').videoPlay();
    }
}
function setTimeend() {
    var nowT = parseInt(CKobject._K_('djs').innerHTML);
    if (nowT > 0) {
        CKobject._K_('djs').innerHTML = nowT - 1;
        setTimeout('setTimeend()', 1000)
    }
}
window.onerror = function () { return true; }

function init() {
	setTimeout(function(){
		play("/ppvod/0xPsaK1v.m3u8", "/ppvod/YRvfxgkM")
	},1000)
     
	
}


function play(main, xml) {
	var  host2100 = "videos.0570dv.com:2100";
    var hostname = "videos.0570dv.com";//window.location.hostname
    var port = 2100 || '80';
    var picurl = window.location.protocol + "//" + host2100 + pic;
    var url = window.location.protocol + "//" + host2100 + main,
    xml = window.location.protocol + "//" + host2100 + xml
    var flashvars = {
        f: 'http://videos.0570dv.com:2100/html/m3u8.swf',
        a: url,
        xml: xml,
        s: 4,
        i: picurl,
        id: id,
        l: l,
        r: r,
        t: t,
        d: d,
        u: u,
        c: 0,
        my_url: encodeURIComponent("http://videos.0570dv.com:2100/share/UAfsSo9yRjqzo1or"),
        my_title: encodeURIComponent(document.title),
        p: 1,
        e: 0,
        loaded: 'loadedHandler'
    };

    var params = { bgcolor: '#FFF', allowFullScreen: true, allowScriptAccess: 'always' };
    var video = [url + '->video/m3u8'];
    CKobject.embed('http://videos.0570dv.com:2100/html/ckplayer/ckplayer.swf', 'a1', 'ckplayer_a1', '100%', '100%', false, flashvars, video, params);

}