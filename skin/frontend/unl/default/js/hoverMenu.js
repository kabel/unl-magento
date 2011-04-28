var HoverMenu = function(ele, timeout) {
	if (timeout) {
		this.timeout = timeout;
	}
	this.element = ele;
	var menu = WDN.jQuery('.hover-menu', ele);
	menu.css('left', ele.position().left - 1);
	
	if (this.element) {
		var self = this; //closure
		this.element.hover(
			function() {
				self.onOver();
			}, 
			function() {
				self.onOut();
			}
		);
	}
}

HoverMenu.prototype.element = null;
HoverMenu.prototype.timeout = 500;
HoverMenu.prototype.timer = null;

HoverMenu.prototype.clearTimer = function()
{
	if (this.timer) {
		clearTimeout(this.timer);
	}
	delete this.timer;
};

HoverMenu.prototype.onOver = function()
{
	this.clearTimer();
	var self = this; //closure
	this.timer = setTimeout(function() {
		self.show();
	}, this.timeout);
};

HoverMenu.prototype.onOut = function()
{
	this.clearTimer();
	this.hide();
};


HoverMenu.prototype.show = function()
{
	this.element.addClass('over');
};

HoverMenu.prototype.hide = function()
{
	this.element.removeClass('over');
};

WDN.jQuery(function() {
	WDN.jQuery('.hover-menu').parent().each(function() {
		var ele = WDN.jQuery(this);
		ele.addClass('menu');
		var temp = new HoverMenu(ele, 200);
	});
});
