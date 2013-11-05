function change_type(event,id){
	$(event.target).parent().html('<a href="javascript:void(0);" onclick="change_type_to(\'ngo\','+id+',event)">公益组织</a><br/><a href="javascript:void(0);" onclick="change_type_to(\'csr\','+id+',event)">企业</a><br/><a href="javascript:void(0);" onclick="change_type_to(\'ind\','+id+',event)">公益人</a><br/><a href="javascript:void(0);" onclick="change_type_to(\'fund\','+id+',event)">基金会</a>');
}
function change_type_to(type, id, event){
	$.get(app_path+"/Admin/change_type", {'id': id, 'type': type}, function(result){
		$(event.target).parent().html($(event.target).text()+'<a href="javascript:void(0);" onclick="change_type(event,'+id+')">切换</a>');
	});
}

//全选当前页事件
function CheckAll(strSection)
{
    var i;
    var colInputs = $('.'+strSection).find('input');
    for(i=1;i<colInputs.length;i++)
    {
        colInputs[i].checked=colInputs[0].checked;
    }
}


//获取已选事件ids
function getSelectCheckboxValues(){
	var obj = document.getElementsByName('key');
	var result ='';
	for (var i=0;i<obj.length;i++)
	{
		if (obj[i].checked==true)
				result += obj[i].value+",";

	}
	return result.substring(0, result.length-1);
}


//点击批量操作标签响应事件
function batch(item,action,type){
	var keyValue= getSelectCheckboxValues();

	if (!keyValue)
	{
		alert(check_word);
		return false;
	}
    else{
        $(item).fastConfirm({
                position: "right",
                questionText:lock_word,
                onProceed: function(trigger) {
                       window.location.href=app_path+'/Admin/batch/ids/'+keyValue+'/action/'+action+'/type/'+type;
                },
                onCancel: function(trigger) {
                }
        });
         return false;
    }
}