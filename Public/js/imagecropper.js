var aspectRatio='2:1'; // ratio of the image
var x1=152; // x1 for the image selection start
var y1=75; // y1 for the image selection start
var x2=252; // x2 for the image selection start
var y2=125; // y2 for the image selection start
var selectionWidth=100; // width for the image selection start
var selectionHeight=50; // height for the image selection start
var maxWidth=400; // max width for the image selection
var maxHeight=200; // max height for the image selection
var minHeight=30; // min height for the image selection
var minWidth=60; // min width for the image selection
var sizefactor=3; // factor for the real size of the uploaded image
var bigWidthPrev=400; // 
var bigHeightPrev=200;
var thumbWidthPrev=200;
var thumbHeightPrev=100;
var uploadingtext='正在上传...'; // text for uploading
var creatingtext='正在生成图片...'; // text for generating thumb
var selWidth=0;
var selHeight=0;
var alertText='必须选择图片中需要的部分'; // text if there is no selection
var useMobile=false;
var cropper_callback;
// $.browser.msie = /msie/.test(navigator.userAgent.toLowerCase());

function init_cropper(width, height, callback){
	$('.uploader-wrapper').lightbox_me({centered: true});
	if(width > height * 2){
		var dx = 200;
		var dy = 200 / width * height;
	}
	else{
		var dy = 100;
		var dx = 100 / height * width;
	}
	x1 = 200 - dx;
	y1 = 100 - dy;
	x2 = 200 + dx;
	y2 = 100 + dy
	aspectRatio = width + ':' + height;
	cropper_callback = callback;

}

$(document).ready(function() {

	$('#upload-file').change(function(){
		if($("#upload-file").val()!=''){
			$('#notice').text(uploadingtext).fadeIn();
			$("#upload_big").submit();
		}
		else {
			$('.notice').hide();
		}
	});
	
	$("#upload_thumb").submit(function() {
		$('#upload_thumb').hide();
		if(useMobile){
			$('.mobileSelection').hide();
		}
		$('#notice2').text(creatingtext).fadeIn();
	});
	
	$("form.uploaderForm").submit(function() {
		
		// get the sended form
		var fname = $(this).attr('name');
		var img_id='';
	
		// check if there is a thumbnail selection
		if(fname == 'upload_thumb'){
			if($('#x1').val() =="" || $('#y1').val() =="" || $('#width').val() <="0" || $('#height').val() <="0"){
				$('#notice2').text(alertText).fadeIn();
				return false;
			}
		}
		
		// hide Imageareaselect first
		$('#big').imgAreaSelect({hide:true});
	
		$('#upload_target').unbind().load( function(){
			
			// get content from hidden iframe
			var img = $('#upload_target').contents().find('body ').html();			
			
			// proof content if there is an error
			if(img.indexOf("uperror") != -1){
				$('#upload_thumb').hide();// hide the generate button
				$('.notice').hide();
				$('#notice').html(img).fadeIn();//show error message
			}
			else {
				
				// save the image source
				$('.img_src').attr('value',img);
			
				if(fname == 'upload_big'){

					// load to preview image
					$('#preview').html(img);
					img_id = 'big';

					// set the preview image
					$('#preview').css({width:selectionWidth+"px",height:selectionHeight+"px"}).show();
					$('#preview').html('<img src="'+img+'" width="'+bigWidthPrev+'" height="'+bigHeightPrev+'" />');
					$('#preview img').css({'left':'-'+x1+'px','top':'-'+y1+'px'});

					// set selection image
					$('#div_'+fname).html('<img id="'+img_id+'" src="'+img+'" width="'+bigWidthPrev+'" height="'+bigHeightPrev+'" />');

					$('#upload_thumb').show();
					if(useMobile){
						$('.mobileSelection').show();
					}

					$('.x1').val(x1*sizefactor);
					$('.y1').val(y1*sizefactor);
					$('.x2').val(x2*sizefactor);
					$('.y2').val(y2*sizefactor);
					$('.width').val(selectionWidth*sizefactor);
					$('.height').val(selectionHeight*sizefactor);

					$('#big').imgAreaSelect({ 
						aspectRatio:aspectRatio,
						show:true,
						x1:x1,y1:y1,x2:x2,y2:y2,
						handles: true,
						fadeSpeed:200,
						resizeable:true,
						maxHeight:maxHeight,
						maxWidth:maxWidth,			
						minHeight:minHeight,
						minWidth:minWidth,
						persistent:true,
						onSelectChange: preview,
						parent: '.uploader-wrapper'
					});
				}
				else {

					//used the standard selection?
					// if(selWidth==0||selHeight==0){
					// 	selWidth=selectionWidth;
					// 	selHeight=selectionHeight;
					// }
					
					// img_id = 'thumbImg';
					// $('#div_'+fname).html('<img id="'+img_id+'" src="'+img+'" width="'+selWidth+'" height="'+selHeight+'" />');

					// $('#details .x1').val('');
					// $('#details .y1').val('');
					// $('#details .x2').val('');
					// $('#details .y2').val('');
					$('.uploader-wrapper').trigger('close');
					img = img.match(/Uploadedthumb\/(.+)/)[1];
					cropper_callback(img);

				}
				
				$('.notice').fadeOut();
			
			}
		
		});
	});
	
});

function preview(img, selection) {
    if (!selection.width || !selection.height)
        return;	
    $('.x1').val(selection.x1*sizefactor);
    $('.y1').val(selection.y1*sizefactor);
    $('.x2').val(selection.x2*sizefactor);
    $('.y2').val(selection.y2*sizefactor);
    $('.width').val(selection.width*sizefactor);
    $('.height').val(selection.height*sizefactor);
	$('#preview').css({'width':selection.width+'px','height':selection.height+'px'});
	selWidth=selection.width;
	selHeight=selection.height;
	$('#preview img').css({'left':'-'+selection.x1+'px','top':'-'+selection.y1+'px'});
}

function reset(){
	$('#div_upload_big img').remove();
	$('#preview img').remove();
}