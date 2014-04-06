$('.btn-audit').click(function(event){
            if(!$(event.currentTarget).hasClass('btn-success')){ //audit the post
                $.get(app_path+'/Local/post_audit/id/'+$(event.currentTarget).attr('post-id'), function(result){
                    if(result == 'ok'){
                        $(event.currentTarget).addClass('btn-success');
                        $(event.currentTarget).text('撤下');
                    }
                });
            }
            else{   //unaudit the post
                $.get(app_path+'/Local/post_unaudit/id/'+$(event.currentTarget).attr('post-id'), function(result){
                    if(result == 'ok'){
                        $(event.currentTarget).removeClass('btn-success');
                        $(event.currentTarget).text('审核');
                    }
                });
            }
       });
       
       $('.btn-stick').click(function(event){
            if(!$(event.currentTarget).hasClass('btn-warning')){ //audit the post
                $.get(app_path+'/Local/post_stick/id/'+$(event.currentTarget).attr('post-id'), function(result){
                    if(result == 'ok'){
                        $(event.currentTarget).addClass('btn-warning');
                        $(event.currentTarget).text('还原');
                    }
                });
            }
            else{   //unaudit the post
                $.get(app_path+'/Local/post_unstick/id/'+$(event.currentTarget).attr('post-id'), function(result){
                    if(result == 'ok'){
                        $(event.currentTarget).removeClass('btn-warning');
                        $(event.currentTarget).text('置顶');
                    }
                });
            }
       });