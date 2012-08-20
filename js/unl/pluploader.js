Pluploader = Class.create();
Pluploader.prototype = {
	uploader: null,
	containerId:null,
	container:null,
	fileRowTemplate:null,
	fileProgressTemplate:null,
	templatesPattern: /(^|.|\r|\n)(\{\{(.*?)\}\})/,
    onContainerHideBefore:null,
	
    initialize: function(containerId, config) {
		this.containerId = containerId;
		this.container = $(containerId);
		
		this.container.controller = this;
		
		this.config = config;
		
		this.fileRowTemplate = new Template(
            this.getInnerElement('template').innerHTML,
            this.templatesPattern
        );

        this.fileProgressTemplate = new Template(
            this.getInnerElement('template-progress').innerHTML,
            this.templatesPattern
        );
        
        if (!this.config.hide_upload_button) {
        	this.getInnerElement('upload').hide();
        }
        
        if (this.config.replace_browse_with_remove) {
        	this.getInnerElement('remove').observe('click', this.removeAllFiles.bind(this));
        	this.getInnerElement('remove').hide();
        }
        
        this.uploader = new plupload.Uploader(config);
        
        this.uploader.bind('FilesAdded', this.handleSelect.bind(this));
        this.uploader.bind('UploadProgress', this.handleProgress.bind(this));
        this.uploader.bind('Error', this.handleError.bind(this));
        this.uploader.bind('FileUploaded', this.handleFileComplete.bind(this));
        this.uploader.bind('UploadComplete', this.handleComplete.bind(this));
        
        this.uploader.init();
        
        this.onContainerHideBefore = this.handleContainerHideBefore.bind(this);
        this.onTabShow = this.handleTabShow.bind(this);
	},
	
	getInnerElement: function(elementName) {
        return $(this.containerId + '-' + elementName);
    },
    
    getFileId: function(file) {
        var id;
        if(typeof file == 'object') {
            id = file.id;
        } else {
            id = file;
        }
        return this.containerId + '-file-' + id;
    },
    
    getDeleteButton: function(file) {
        return $(this.getFileId(file) + '-delete');
    },
    
    browse: function() {
        return true;
    },
    
    upload: function() {
        this.uploader.start();
    },
    
    destroy: function() {
    	if (this.config.replace_browse_with_remove) {
    		this.getInnerElement('remove').stopObserving('click');
    	}
    	
    	this.uploader.destroy();
    },
    
    removeFile: function(id) {
    	var file = this.uploader.getFile(id), fileDOM = $(this.getFileId(id));
    	if (file) {
    		this.uploader.removeFile(file);
    	}
    	if (fileDOM) {
    		fileDOM.remove();
    	}
    	
    	if (!file && !fileDOM) {
    		return;
    	}
    	
        if (this.onFileRemove) {
            this.onFileRemove(id);
        }
        this.updateFiles();
    },
    
    removeAllFiles: function() {
        this.uploader.splice(0, this.uploader.files.length);
        $(this.container).select('.file-row').each(function(fileDOM) {
        	fileDOM.remove();
        });
        this.updateFiles();
        
        if (this.onFileRemoveAll) {
        	this.onFileRemoveAll();
        }
    },
    
    handleSelect: function (up, files) {
    	files.each(function(file) {
            this.updateFile(file);
        }.bind(this));
    	if (!this.config.hide_upload_button) {
    		this.getInnerElement('upload').show();
    	}
        if (this.onFileSelect) {
            this.onFileSelect();
        }
    },
    
    handleProgress: function (up, file) {
        this.updateFile(file);
        if (this.onFileProgress) {
            this.onFileProgress(file);
        }
    },
    
    handleError: function (up, err) {
    	if (err.file) {
    		var isAccepted = !!this.uploader.getFile(err.file.id);
    		if (isAccepted) {
    			err.file.errorText = err.message;
    			this.updateFile(err.file);
    		} else {
    			this.removeFile(err.file.id);
    			alert('File "' + err.file.name + '" was refused. Error: ' + err.message);
    		}
    	} else {
    		alert('Image Uploader Error: ' + err.code + ', Message: ' + err.message);
    	}
    },
    
    handleFileComplete: function(up, file, resp) {
    	file.response = resp.response;
    	this.updateFile(file);
    },
    
    handleComplete: function (up, files) {
        this.updateFiles();
        if (this.onFilesComplete) {
            this.onFilesComplete(files);
        }
    },
    
    updateFiles: function () {
        this.uploader.files.each(function(file) {
            this.updateFile(file);
        }.bind(this));
        
        if (!this.uploader.files.length) {
        	if (!this.config.hide_upload_button) {
        		this.getInnerElement('upload').hide();
        	}
        	if (this.config.replace_browse_with_remove) {
        		this.getInnerElement('browse').show();
        		this.getInnerElement('remove').hide();
        	}
        }
    },
    
    updateFile:  function (file) {
        if (!$(this.getFileId(file))) {
            if (this.config.replace_browse_with_remove) {
                $(this.containerId+'-new').show();
                $(this.containerId+'-new').innerHTML = this.fileRowTemplate.evaluate(this.getFileVars(file));
                $(this.containerId+'-old').hide();
                this.getInnerElement('browse').hide();
                this.getInnerElement('remove').show();
            } else {
                Element.insert(this.container, {bottom: this.fileRowTemplate.evaluate(this.getFileVars(file))});
            }
        }
        if (file.status == plupload.DONE && file.response.isJSON()) {
            var response = file.response.evalJSON();
            if (typeof response == 'object') {
                if (typeof response.cookie == 'object') {
                    var date = new Date();
                    date.setTime(date.getTime()+(parseInt(response.cookie.lifetime)*1000));

                    document.cookie = escape(response.cookie.name) + "="
                        + escape(response.cookie.value)
                        + "; expires=" + date.toGMTString()
                        + (response.cookie.path.blank() ? "" : "; path=" + response.cookie.path)
                        + (response.cookie.domain.blank() ? "" : "; domain=" + response.cookie.domain);
                }
                if (typeof response.error != 'undefined' && response.error != 0) {
                    file.status = plupload.FAILED;
                    file.errorText = response.error;
                }
            }
        }

        if (file.status == plupload.DONE && !file.response.isJSON()) {
            file.status = plupload.FAILED;
        }

        var progress = $(this.getFileId(file)).getElementsByClassName('progress-text')[0];
        if (file.status==plupload.UPLOADING) {
            $(this.getFileId(file)).addClassName('progress');
            $(this.getFileId(file)).removeClassName('new');
            $(this.getFileId(file)).removeClassName('error');
            if (file.size && file.percent) {
                progress.update(this.fileProgressTemplate.evaluate(this.getFileProgressVars(file)));
            } else {
                progress.update('');
            }
            if (! this.config.replace_browse_with_remove) {
                this.getDeleteButton(file).hide();
            }
        } else if (file.status==plupload.FAILED) {
            $(this.getFileId(file)).addClassName('error');
            $(this.getFileId(file)).removeClassName('progress');
            $(this.getFileId(file)).removeClassName('new');
            var errorText = file.errorText ? file.errorText : this.errorText(file);
            if (this.config.replace_browse_with_remove) {
            	this.getInnerElement('browse').hide();
            } else {
                this.getDeleteButton(file).show();
            }

            progress.update(errorText);

        } else if (file.status==plupload.DONE) {
            $(this.getFileId(file)).addClassName('complete');
            $(this.getFileId(file)).removeClassName('progress');
            $(this.getFileId(file)).removeClassName('error');
            if (!this.config.replace_browse_with_remove && this.getDeleteButton(file)) {
            	this.getDeleteButton(file).remove();
            }
            progress.update(this.translate('Complete'));
        }
        
        this.uploader.refresh();
    },
    
    getFileVars: function(file) {
        return {
            id      : this.getFileId(file),
            fileId  : file.id,
            name    : file.name,
            size    : this.formatSize(file.size)
        };
    },
    
    getFileProgressVars: function(file) {
        return {
            total    : this.formatSize(file.size),
            uploaded : this.formatSize(file.loaded),
            percent  : file.percent
        };
    },
    
    formatSize: function(size) {
        if (size > 1024 * 1024 * 1024 * 1024) {
            return this.round(size / (1024 * 1024 * 1024 * 1024)) + ' ' + this.translate('TB');
        } else if (size > 1024 * 1024 * 1024) {
            return this.round(size / (1024 * 1024 * 1024)) + ' ' + this.translate('GB');
        } else if (size > 1024 * 1024) {
            return this.round(size / (1024 * 1024)) + ' ' + this.translate('MB');
        } else if (size > 1024) {
            return this.round(size / (1024)) + ' ' + this.translate('kB');
        }
        return size + ' ' + this.translate('B');
    },
    
    round: function(number) {
        return Math.round(number*100)/100;
    },
    
    translate: function(text) {
        try {
            if(Translator){
               return Translator.translate(text);
            }
        }
        catch(e){}
        return text;
    },
    
    errorText: function(file) {
        var error = '';

        switch(file.errorCode) {
            case 1: // Size 0
                error = 'File size should be more than 0 bytes';
                break;
            case 2: // Http error
                error = 'Upload HTTP Error';
                break;
            case 3: // I/O error
                error = 'Upload I/O Error';
                break;
            case 4: // Security error
                error = 'Upload Security Error';
                break;
            case 5: // SSL self-signed certificate
                error = 'SSL Error: Invalid or self-signed certificate';
                break;
        }

        if(error) {
            return this.translate(error);
        }

        return error;
    },
    
    handleContainerHideBefore: function(container) {
        if (container && Element.descendantOf(this.container, container) && !this.checkAllComplete()) {
            if (! confirm('There are files that were selected but not uploaded yet. After switching to another tab your selections will be lost. Do you wish to continue ?')) {
                return 'cannotchange';
            } else {
                this.removeAllFiles();
            }
        }
    },
    
    handleTabShow: function(info) {
    	var tabContentElement = $(info.tab.id+'_content');
    	if (tabContentElement && Element.descendantOf(this.container, tabContentElement)) {
    		this.uploader.refresh();
    	}
    },
    
    checkAllComplete: function() {
        if (this.uploader.files.length) {
            return !this.uploader.files.any(function(file) {
                return (file.status !== plupload.DONE);
            });
        }
        return true;
    }
};
