((function(){

var scrollingHeader = function(){
	var body = $(document.body);
	var scroll = body.getScroll();

	var height = window.innerHeight;

	if(scroll.y > (height - 250)) body.addClass('scrolling-enable');
	if(scroll.y < (height - 250) && body.hasClass('scrolling-enable')) body.removeClass('scrolling-enable');

	//console.log(scroll.y, height - 250);
};

window.addEvent('scroll', scrollingHeader);

})());