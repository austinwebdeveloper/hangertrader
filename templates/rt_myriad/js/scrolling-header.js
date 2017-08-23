((function(){

var scrollingHeader = function(){
	var body = $(document.body);
	var scroll = body.getScroll();

	if(scroll.y > 0) body.addClass('scrolling-enable');
	if(scroll.y == 0 && body.hasClass('scrolling-enable')) body.removeClass('scrolling-enable');
};

window.addEvent('scroll', scrollingHeader);

})());