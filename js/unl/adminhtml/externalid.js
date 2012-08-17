/********************* EXTERNAL ID POPUP ***********************/
var ExternalIdPopup = Class.create();
ExternalIdPopup.prototype = {
	windowMask: null,
	window: null,
	container: null,
	
	initialize: function(url) {
		$$('.action-link-ext').each(function (el) {
            Event.observe(el, 'click', this.show.bind(this));
        }, this);

        // Move popup to start of body, because soon it will contain FORM tag that can break DOM layout if within other FORM
        var oldPopupContainer = $('externalid_configure');
        if (oldPopupContainer) {
            oldPopupContainer.remove();
        }

        var newPopupContainer = $('externalid_configure_new');
        $(document.body).insert({top: newPopupContainer});
        newPopupContainer.id = 'externalid_configure';
        this.window = newPopupContainer;
        this.windowMask = $('externalid_window_mask');
        this.container = $('external_id');

        // Put controls container inside a FORM tag so we can use Validator
        var form = new Element('form', {action: url, id: 'externalid_form', method: 'post'});
        var formContents = $('externalid_form_contents');
        if (formContents) {
            formContents.parentNode.appendChild(form);
            form.appendChild(formContents);
        }
        
        Event.observe($('externalid_cancel_button'), 'click', this.onCloseButton.bind(this));
        Event.observe($('externalid_ok_button'), 'click', this.onOkButton.bind(this));
	},
	
	show: function(event) {
		Form.Element.setValue($('externalid_value'), this.container.down('.value').innerHTML);
		
		this.windowMask.setStyle({'height': $('html-body').getHeight() + 'px'}).show();
		this.window.setStyle({'marginTop': -this.window.getHeight()/2 + 'px'}).show();
		Event.stop(event);
	},
	
	onOkButton: function() {
		var form = new varienForm('externalid_form');
		form.canShowError = true;
		if (!form.validate()) {
			return false;
		}
		form.validator.reset();
		
		this.container.down('.value').update($F('externalid_value'));
		
		$('externalid_form').request();
		this.close();
		return true;
	},
	
	onCloseButton: function() {
		this.close();
	},
	
	close: function() {
		this.windowMask.hide();
		this.window.hide();
	}
};

/********************* EXTERNAL ID SET ***********************/
ExternalIdSet = Class.create();
ExternalIdSet.prototype = {
	initialize: function() {
		
	}
};
