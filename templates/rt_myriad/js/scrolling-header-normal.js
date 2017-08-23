((function(){

var scrollingHeader = function(){
	var body = $(document.body);
	var scroll = body.getScroll();

	var height = window.innerHeight;

	if(scroll.y > 0) body.addClass('scrolling-enable');
	if(scroll.y == 0 && body.hasClass('scrolling-enable')) body.removeClass('scrolling-enable');

	console.log(scroll.y);
};

window.addEvent('scroll', scrollingHeader);

})());