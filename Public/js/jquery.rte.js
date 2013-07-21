/*
* jQuery RTE plugin 0.5.1 - create a rich text form for Mozilla, Opera, Safari and Internet Explorer
*
* Copyright (c) 2009 Batiste Bieler
* Distributed under the GPL Licenses.
* Distributed under the MIT License.
*/

// define the rte light plugin
(function($) {

if(typeof $.fn.rte === "undefined") {

    var defaults = {
        media_url: "",
        content_css_url: "rte.css",
        dot_net_button_class: null,
        max_height: 350
    };

    $.fn.rte = function(options) {

    $.fn.rte.html = function(iframe) {
        return iframe.contentWindow.document.getElementsByTagName("body")[0].innerHTML;
    };

    // build main options before element iteration
    var opts = $.extend(defaults, options);

    // iterate and construct the RTEs
    return this.each( function() {
        var textarea = $(this);
        var iframe;
        var element_id = textarea.attr("id");

        // enable design mode
        function enableDesignMode() {

            var content = textarea.val();

            // Mozilla needs this to display caret
            if($.trim(content)=='') {
                content = '<br />';
            }

            // already created? show/hide
            if(iframe) {
                console.log("already created");
                textarea.hide();
                $(iframe).contents().find("body").html(content);
                $(iframe).show();
                // $("#toolbar-" + element_id).remove();
                toolbar();
                // textarea.before(toolbar());
                return true;
            }

            // for compatibility reasons, need to be created this way
            iframe = document.createElement("iframe");
            iframe.frameBorder=0;
            iframe.frameMargin=0;
            iframe.framePadding=0;
            iframe.height=200;
            if(textarea.attr('class'))
                iframe.className = textarea.attr('class');
            if(textarea.attr('id'))
                iframe.id = element_id+'-rte';
            if(textarea.attr('name'))
                iframe.title = textarea.attr('name');

            textarea.after(iframe);

            var css = "";
            if(opts.content_css_url) {
                // css = "<link type='text/css' rel='stylesheet' href='" + opts.content_css_url + "' />";
            }

            var doc = "<html><head>"+css+"</head><body class='frameBody'>"+content+"</body></html>";
            tryEnableDesignMode(doc, function() {
                // $("#toolbar-" + element_id).remove();
                toolbar();
                // textarea.before(toolbar());
                // hide textarea
                textarea.hide();

            });

        }

        function tryEnableDesignMode(doc, callback) {
            if(!iframe) { return false; }

            try {
                iframe.contentWindow.document.open();
                iframe.contentWindow.document.write(doc);
                iframe.contentWindow.document.close();
            } catch(error) {
                //console.log(error);
            }
            if (document.contentEditable) {
                iframe.contentWindow.document.designMode = "On";
                callback();
                return true;
            }
            else if (document.designMode != null) {
                try {
                    iframe.contentWindow.document.designMode = "on";
                    callback();
                    return true;
                } catch (error) {
                    //console.log(error);
                }
            }
            setTimeout(function(){tryEnableDesignMode(doc, callback)}, 500);
            return false;
        }

        function disableDesignMode(submit) {
            var content = $(iframe).contents().find("body").html();

            if($(iframe).is(":visible")) {
                textarea.val(content);
            }

            if(submit !== true) {
                textarea.show();
                $(iframe).hide();
            }
        }

        // create toolbar and bind events to it's elements
        function toolbar() {

            // .NET compatability
            if(opts.dot_net_button_class) {
                var dot_net_button = $(iframe).parents('form').find(opts.dot_net_button_class);
                dot_net_button.click(function() {
                    disableDesignMode(true);
                });
            // Regular forms
            } else {
                $(iframe).parents('form').submit(function(){
                    disableDesignMode(true);
                });
            }

            var iframeDoc = $(iframe.contentWindow.document);

            // var select = $('select', tb)[0];
            // iframeDoc.mouseup(function(){
            //     setSelectedType(getSelectionElement(), select);
            //     return true;
            // });

            // iframeDoc.keyup(function() {
            //     setSelectedType(getSelectionElement(), select);
            //     var body = $('body', iframeDoc);
            //     if(body.scrollTop() > 0) {
            //         var iframe_height = parseInt(iframe.style['height'])
            //         if(isNaN(iframe_height))
            //             iframe_height = 0;
            //         var h = Math.min(opts.max_height, iframe_height+body.scrollTop()) + 'px';
            //         iframe.style['height'] = h;
            //     }
            //     return true;
            // });

            // return tb;
        };

        function formatText(command, option) {
            iframe.contentWindow.focus();
            try{
                iframe.contentWindow.document.execCommand(command, false, option);
            }catch(e){
                //console.log(e)
            }
            iframe.contentWindow.focus();
        };

        function setSelectedType(node, select) {
            while(node.parentNode) {
                var nName = node.nodeName.toLowerCase();
                for(var i=0;i<select.options.length;i++) {
                    if(nName==select.options[i].value){
                        select.selectedIndex=i;
                        return true;
                    }
                }
                node = node.parentNode;
            }
            select.selectedIndex=0;
            return true;
        };

        function getSelectionElement() {
            if (iframe.contentWindow.document.selection) {
                // IE selections
                selection = iframe.contentWindow.document.selection;
                range = selection.createRange();
                try {
                    node = range.parentElement();
                }
                catch (e) {
                    return false;
                }
            } else {
                // Mozilla selections
                try {
                    selection = iframe.contentWindow.getSelection();
                    range = selection.getRangeAt(0);
                }
                catch(e){
                    return false;
                }
                node = range.commonAncestorContainer;
            }
            return node;
        };
        
        // enable design mode now
        enableDesignMode();

    }); //return this.each
    
    }; // rte

} // if

})(jQuery);




//implement an rte in form
$('#editor').rte();
$(function(){
    var iframe = document.getElementById('editor-rte');
    fixIframeCaret(iframe);
    $('iframe#editor-rte').contents().find('body').css('font-size','12px');
    $('iframe#editor-rte').contents().find('body').css('color','#b5b5b5');
    $('iframe#editor-rte').contents().find('body').focus(function(){
        if(!touched_editor_content){
            var docbody = $('iframe#editor-rte').contents().find('body');
            docbody.css('color','#000');
            touched_editor_content = docbody.html();
            docbody.html('');
        }
    });

    $('iframe#editor-rte').contents().find('body').blur(function(){
        var docbody = $('iframe#editor-rte').contents().find('body');
        if(docbody.html() == ''){
            docbody.html(touched_editor_content);
            docbody.css('color','#b5b5b5');
            touched_editor_content = false;
        }
    });
});

var touched_editor_title = false;
var touched_editor_content = false;
$('#editor-title').focus(function(){
    if(!touched_editor_title){
        touched_editor_title = $('#editor-title').val();
        $('#editor-title').val('');
    }
});

$('#editor-title').blur(function(){
    if($('#editor-title').val() == ''){
        $('#editor-title').val(touched_editor_title);
        touched_editor_title = false;
    }
});


$('#insert-video-button').click(function(){
    var youku_url = /youku\.com.+id_(.+)\.html/;
    var tudou_url = /tudou\.com\/programs\/view\/(.+)\//;
    var video_url = $('#insert-video-text').val();
    var url_56 = /56\.com.+v_(.+)\.html/;
    var result = "";
    
    if(m=video_url.match(youku_url)){
        result = 'http://player.youku.com/player.php/sid/'+m[1]+'/v.swf';
    }
    else if(m=video_url.match(tudou_url)){
        result = 'http://www.tudou.com/v/'+m[1]+'/v.swf';
    }
    else if(m=video_url.match(url_56)){
        result = 'http://player.56.com/v_'+m[1]+'.swf';
    }

    if(result != ''){
        var html = '<embed src="'+result+'" quality="high" width="480" height="400" align="middle" allowScriptAccess="sameDomain" allowFullscreen="true" type="application/x-shockwave-flash"></embed>';
        insertIt('editor-rte',html);
        $('#insert-link-dialog').trigger('close');
    }
    else{
        $('#video-error-message').text('无法解析视频地址');
    }
});

$('#insert-link-button').click(function(){
    var link = $('#insert-link-text').val();
    insertIt('editor-rte','<a href="'+link+'">'+link+'</a>');
    $('#insert-link-dialog').trigger('close');
});

//rte-section
$('#insert-image').click(function(){
    $('#upload-dialog').lightbox_me({centered: true});
    new AjaxUpload('upload_button', {
        action: app_path+'/Event/upload_image',
        onComplete: function(file, response) {
            console.log(response);
            $('#upload-dialog').trigger('close');
            insertIt('editor-rte','<img src="'+app_path+'/Public/Uploadedthumb/thumbl_'+response+'"/>');
            //var doc = $('iframe#editor-rte').contents()[0];
            //document.getElementById('editor-rte').contentWindow.focus();
            //doc.execCommand('InsertImage', false, app_path+'/Public/Uploadedthumb/thumbl_'+response);
        }
    });
});
$('#insert-link').click(function(){
    $('#insert-link-dialog').lightbox_me({centered: true});
    $('#insert-link-text').val('http://');
});
$('#insert-video').click(function(){
    $('#insert-video-dialog').lightbox_me({centered: true});
    $('#insert-video-text').val('');
    $('#video-error-message').text('');
});

$('#upload-dialog .close-button').click(function(){
    $('#upload-dialog').trigger('close');
});
$('#insert-link-dialog .close-button').click(function(){
    $('#insert-link-dialog').trigger('close');
});
$('#insert-video-dialog .close-button').click(function(){
    $('#insert-video-dialog').trigger('close');
});

function insertIt(editor,html) {
    var win = document.getElementById(editor).contentWindow, doc = win.document;
    var sel, range;
    if (win.getSelection) {
        // IE9 and non-IE
        sel = win.getSelection();
        if (sel.getRangeAt && sel.rangeCount) {
            range = sel.getRangeAt(0);
            range.deleteContents();

            // Range.createContextualFragment() would be useful here but is
            // non-standard and not supported in all browsers (IE9, for one)
            var el = doc.createElement("div");
            el.innerHTML = html;
            var frag = doc.createDocumentFragment(), node, lastNode;
            while ( (node = el.firstChild) ) {
                lastNode = frag.appendChild(node);
            }
            range.insertNode(frag);

            // Preserve the selection
            if (lastNode) {
                range = range.cloneRange();
                range.setStartAfter(lastNode);
                range.collapse(true);
                sel.removeAllRanges();
                sel.addRange(range);
            }
        }
    } else if (doc.selection && doc.selection.type != "Control") {
        // IE < 9
        win.focus();
        IEselectedRange.pasteHTML(html);
    }
}

var IEselectedRange = null;

function fixIframeCaret(iframe) {
    if (iframe.attachEvent) {
        iframe.attachEvent("onbeforedeactivate", function() {
            var sel = iframe.contentWindow.document.selection;
            IEselectedRange = sel.createRange();
        });
    }
}

