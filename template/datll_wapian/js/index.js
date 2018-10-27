(function ($)  {
	    
	var loadImg  =   function (url,  fn)  {        
		var  img  =  new  Image();        
		img.src  =  url;        
		if (img.complete)  {            
			fn.call(img);        
		} 
		else  {            
			img.onload  =   function ()  {                
				fn.call(img);                
				img.onload  =  null;            
			};        
		};    
	};

	$(".Film_list .cover img").lazyload({
		effect: "fadeIn",
		fn: function(self,that) {
			imgPosition($(self).parent(),self,that);
		}
	});　    
	$.fn.imgAutoSize  =   function (padding)  {        
		var  maxWidth  =  this.innerWidth()  -  (padding  ||  0);
		var maxHeight = this.innerHeight()  -  (padding  ||  0);
		var ratio = maxWidth / maxHeight,
			ratio_n = 1;        
		return  this.find('img').each(function (i,  img)  {            
			loadImg(this.src,  function ()  {
				ratio_n = (this.width / this.height);
				if(ratio > ratio_n) {
					if (this.width  >  maxWidth)  {                    
						var  height  =  maxWidth  /  this.width  *  this.height,
							                        width  =  maxWidth;                    
						img.width  =  width;                    
						img.height  =  height;                
					};
				} else {
					if (this.height  >  maxHeight)  {                    
						var  height  =  maxHeight;                    
						img.height  =  height;                
					};
				}                            
			});        
		});    
	};

	function imgPosition(div, self,img) {		
		
		var  maxWidth  =  $(div).innerWidth() ,
			maxHeight = $(div).innerHeight(),
			ratio = maxWidth / maxHeight,
			ratio_n = 1; 		
		ratio_n = (img.width / img.height);
		if(ratio > ratio_n) {
			if (img.width  >  maxWidth)  {                    
				var  height  =  maxWidth  /  img.width  *  img.height,
					 width  =  maxWidth;                    
				self.width  =  width;                    
				self.height  =  height;                
			};
		} else {
			if (img.height  >  maxHeight)  {                    
				var height  =  maxHeight;
				self.height  =  height;                
			};
		}
	}

	$('.index_film .cover').imgAutoSize();

	　　　　　

	function get_adv() {
		$.ajax({
			url: "/Adv/get_index_adv",
			success: function(data) {
				var vod = $(".Film_list"),
					len = vod.length;
				if(data && data.length > 0) {

					for(var i = 0; i < len; i++) {
						if(i < data.length) {
							vod.eq(i).before(`<div style="clear:both;margin-bottom:20px;"><a href="${data[i].link}" target="_blank"><img src="${data[i].img}" style="width:100%;" /></a></div>`)
						}
					}
				}
			}
		})
	}

	get_adv();
})(jQuery);