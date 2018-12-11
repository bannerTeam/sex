$(function(){
	
	if($(window).width()<720){
		return false;
	}
	
	$.ajax({
		url:"/ajax/get_bfixed",
		dataType:"json",
		data:{
			type:"8,9,10"
		},
		success:function(data){
			addHtml(data.list);
		}
	});
	
	
	function addHtml(datas){
		
		if(datas.length == 0){
			return false;
		}
		
		var str = `<div class="bfixed bfixed-l bfixed_top1"><span class="close">[关闭]</span></div>
<div class="bfixed bfixed-r bfixed_top2"><span class="close">[关闭]</span></div>
<div class="bfixed bfixed-l bfixed_middle1"><span class="close">[关闭]</span></div>
<div class="bfixed bfixed-r bfixed_middle2"><span class="close">[关闭]</span></div>
<div class="bfixed bfixed-l bfixed_bottom1"><span class="close">[关闭]</span></div>
<div class="bfixed bfixed-r bfixed_bottom2"><span class="close">[关闭]</span></div>`;
		
		$("body").append(str);
		
		var ss = "";
		for (var i = 0; i < datas.length; i++) {
			
			if(datas[i].adv_group_id == 8){			
				if(datas[i].content && datas[i].content.length > 20){
					s = datas[i].content;
				}else{
					s = `<a href="${datas[i].link}" target="_blank"><img src="${datas[i].img}"></a>`;
				}								
				$(".bfixed_top1,.bfixed_top2").append(s).show();				
			}else if(datas[i].adv_group_id == 9){
				if(datas[i].content && datas[i].content.length > 20){
					s = datas[i].content;
				}else{
					s = `<a href="${datas[i].link}" target="_blank"><img src="${datas[i].img}"></a>`;				
					$(".bfixed_middle1,.bfixed_middle2").append(s).show();	
				}
			}else if(datas[i].adv_group_id == 10){
				if(datas[i].content && datas[i].content.length > 20){
					s = datas[i].content;
				}else{
					s = `<a href="${datas[i].link}" target="_blank"><img src="${datas[i].img}"></a>`;				
					$(".bfixed_bottom1,.bfixed_bottom2").append(s).show();	
				}
			}			
		}
		$(".bfixed .close").click(function(){		
			$(this).parent().remove();		
		});
		
	}
	
	

	
	
	
})
