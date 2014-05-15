// JavaScript Document by jacy
/**
 定义基本常量
*/
var RESULT_SUCCESS = 'success';
var RESULT_FAIL = 'fail';
var WeiPHP_RAND_COLOR = ["#ff6600","#ff9900","#99cc00","#33cc00","#0099cc","#3399ff","#9933ff","#cc3366","#333333","#339999","#ff6600","#ff9900","#99cc00","#33cc00","#0099cc","#3399ff","#9933ff","#cc3366","#333333","#339999","#ff6600","#ff9900","#99cc00","#33cc00","#0099cc","#3399ff","#9933ff","#cc3366","#333333","#339999"];

/***/
(function(){
	//异步请求提交表单
	//提交后返回格式json json格式 {'result':'success|fail',data:{....}}
	function doAjaxSubmit(form,callback){
		$.Dialog.loading();
		$.ajax({
			data:form.serializeArray(),
			type:'post',
			dataType:'json',
			url:form.attr('action'),
			success:function(data){
				$.Dialog.close();
				callback(data);
				}
			})
	}
	
	function initFixedLayout(){
		var navHeight = $('#fixedNav').height();
		$('#fixedContainer').height($(window).height()-navHeight);	
	}
	//banner
	function banner(isAuto,delayTime){
		var screenWidth = $('.container').width();
		var count = $('.banner li').size();
		$('.banner ul').width(screenWidth*count);
		$('.banner ul').height(screenWidth/2);
		$('.banner').height(screenWidth/2);
		$('.banner li').width(screenWidth).height(screenWidth/2);
		$('.banner li img').width(screenWidth).height(screenWidth/2);
		// With options
		$('.banner li .title').each(function(index, element) {
            $(this).text($(this).text().substring(0,15)+" ...");
        });
		var flipsnap = Flipsnap('.banner ul');
		flipsnap.element.addEventListener('fstouchend', function(ev) {
			$('.identify em').eq(ev.newPoint).addClass('cur').siblings().removeClass('cur');
		}, false);
		$('.identify em').eq(0).addClass('cur')
		if(isAuto){
			var point = 1;
			setInterval(function(){
				console.log(point);
				flipsnap.moveToPoint(point);
				$('.identify em').eq(point).addClass('cur').siblings().removeClass('cur');
				if(point+1==$('.banner li').size()){
					point=0;
				}else{
					point++;
					}
				
				},delayTime)
		}
	}
	//随机颜色
	function setRandomColor(selector){
		$(selector).each(function(index, element) {
			$(this).css('background-color',WeiPHP_RAND_COLOR[index]);
		});;
	}
	var WeiPHP = {
		doAjaxSubmit:doAjaxSubmit,
		setRandomColor:setRandomColor,
		initBanner:banner,
		initFixedLayout:initFixedLayout
	};
	$.extend({
		WeiPHP: WeiPHP
	});
})();

/*
*/
$(function(){
	$('.toggle_list .title').click(function(){
		$(this).parents('li').toggleClass("toggle_list_open");
		})
	})