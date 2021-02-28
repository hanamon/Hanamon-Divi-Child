jQuery(function(){ // custom.js
	customOBJ.init();
});

var $ = jQuery;

var customOBJ = {
	init: function(){
		customOBJ.headerEvent();
	},
	headerEvent: function(){
		// Mobile 메뉴 (열기)
		$('a.mobileAppbarBtn').on({
			click: function(){
				$("html").addClass("addHtmlScroll");
				$(".mobile-side-menu-container").stop().show(0);
				$(".mobile-side-menu-container .mobile-side-bg-wrap").stop().animate({ opacity:0 }, 0).animate({ opacity:1 }, 200);
				$(".mobile-side-menu-container .mobile-side-menu-wrap").stop().animate({ right:'-100%' }, 0).animate({ right:0 }, 200);
			}
		});
		
		// Mobile 메뉴 (닫기) : 배경 & 버튼
		$(".mobile-side-menu-container .mobile-side-bg-wrap").on({
			click: function(){
				$("html").removeClass("addHtmlScroll");
				$(".mobile-side-menu-container .mobile-side-menu-wrap").stop().animate({ right:0 }, 0).animate({ right:'-100%' }, 400);
				$(".mobile-side-menu-container .mobile-side-bg-wrap").stop().animate({ opacity:1 }, 0).animate({ opacity:0 }, 400, function(){
					$(".mobile-side-menu-container").stop().hide(0);
				});
			}
		});
	}
}