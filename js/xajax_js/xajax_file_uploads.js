if (xajax)
{
        xajax.workId = 'xajaxWork'+ new Date().getTime();

        xajax.initFileInputs = function ()
        {
                inputs = document.getElementsByTagName('input');
                for( var i=0; i < inputs.length; i++)
                {
                        inp=inputs[i];
                        if (!inp.className)
                                continue; //doesnt have a class defined
                        if (inp.className.indexOf('xajax_file')==-1)
                                continue; //not an xajax file upload
                        if (inp.style.visibility=='hidden')
                                continue; //already converted this file upload	
                        xajax.newFileUpload(inp.id, inp.id+'-'+xajax.workId)
                                inp.style.visibility = 'hidden';
                        inp.style.height = '0';
                        inp.style.width = '0';
                }
        }
        xajax.newFileUpload = function(sParentId, sId)
        {
                xajax.dom.insertAfter(sParentId, 'iframe', sId);
                newFrame = $(sId);
                while (!newFrame.contentDocument)
                {
                	sleep(60);
                    newFrame = $(sId);
                }
                newFrame.name=sId;
                newFrame.style.height="230px";
                newFrame.style.width="300px";
                newFrame.style.overflow="auto";
                newFrame.position="relative";
                newFrame.scrolling="yes";
                newFrame.allowtransparency=true;
                newFrame.style.backgroundColor="transparent";
                //need to wait for Mozilla to notice there's an iframe
               	while(!$(newFrame))  {
               		sleep(60);
               	}
                setTimeout('xajax._fileUploadContinue("'+sId+'");', 360);
        }
        xajax._fileUploadContinue = function(sId)
        {
                //uploadIframe = window.frames[sId];
                uploadIframe = $(sId);
                if (!uploadIframe.contentDocument)
                {
                        //fix for internet explorer
                        uploadIframe.contentDocument = window.frames[sId].document;
                }
                while (!uploadIframe.contentDocument)
                {
                	sleep(60);
                    uploadIframe = $(sId);
                }
                uploadIframe.contentDocument.body.style.backgroundColor="transparent";
                uploadIframe.contentDocument.xajax=this;
                uploadIframe.contentDocument.body.innerHTML='<span id="workId" style="font-size:0px;height: 0px;position:absolute;">'+xajax.workId+'</span><form style="position:absolute;top:0;left:0;height:98%;width:98%;margin:0;padding:0;overflow:hidden;" name="iform" action="'+xajax.config.requestURI+'" method="post" enctype="multipart/form-data"><input id="file" type="file" name="file" onchange="document.xajax._fileUploading(\''+sId+'\');document.iform.submit();" onmouseout="if(this.value)document.iform.submit();"/><input type="hidden" name="xajax" value="fileUpload" /></form>';
                uploadIframe.style.border='0';
        }
        xajax._fileUploading = function(sId)
        {
                uploadIframe = $(sId);
                xajax.dom.insertAfter(sId, 'div', sId+'-progress');
                uploadProgress = $(sId+'-progress');
                //uploadIframe.style.visibility='hidden';
                uploadIframe.style.width='100%';
                //uploadIframe.style.height='100';
                uploadProgress.style.fontSize="25";
                uploadProgress.innerHTML="Uploading... ";
                Effect.toggle('loading-mask','blind'); 
                setTimeout('xajax._fileProgressCheck("'+sId+'");', 100);
        }
        xajax._fileProgressCheck = function(sId)
        {
                uploadIframe = $(sId);
                uploadProgress = $(sId+'-progress');
                if (uploadIframe.contentDocument.documentElement.tagName.indexOf('xjx') !== -1)
                {
                        //this isn't a proper detection, but we'll work on it later
                        uploadProgress.innerHTML="Upload Finished.";  
                        Effect.toggle('loading-mask','blind'); 
                } else {
                        setTimeout('xajax._fileProgressCheck("'+sId+'");', 100);
                }
        }
        xajax._uploadFinished = function()
        {
                //this isn't a proper detection, but we'll work on it later
                uploadProgress.innerHTML="Upload Finished.";  
                Effect.toggle('loading-mask','blind'); 
        }
}
