var myVar;
//вспомогательная функция вывода объекта
function dump(obj) {
    var out = "";
    if(obj && typeof(obj) == "object"){
        for (var i in obj) {
            out += i + ": " + obj[i] + "n";
        }
    } else {
        out = obj;
    }
    alert(out);
}

function addPlaceToUser(user,place)
{
     $.ajax({
     url: window.location.protocol+"//"+window.location.hostname+window.location.pathname+'?r=users/add-place-to-user',
     type: 'post',
     data: {
         'userId':user,
         'placeId':place
     },
     success: function (responseServer) {
         responseServer=$.parseJSON(responseServer);
         if (responseServer.error=="1") {
             alert("Сервер не получил эти данные. Перезагрузите страницу и попробуйте еще раз");
         }
         if (responseServer.error=="2") {
             alert("Серверу не удалось записать эти данные в базу");
         }

     }
    });    
}

function delPlaceFromUser(user,place)
{
     $.ajax({
     url: window.location.protocol+"//"+window.location.hostname+window.location.pathname+'?r=users/del-place-from-user',
     type: 'post',
     data: {
         'userId':user,
         'placeId':place
     },
     success: function (responseServer) {
         responseServer=$.parseJSON(responseServer);
         if (responseServer.error=="1") {
             alert("Сервер не получил все нужные данные. Перезагрузите страницу и попробуйте еще раз");
         }
         if (responseServer.error=="2") {
             alert("Серверу не удалось удалить эти данные из базы");
         }
         
     }
    });    
}

function actionPlace(ui, ev)
{
    var user=getUrlVars()["id"];
    var place=ui.item[0].id.replace("place","");
    if ((ui.startparent[0].id=='placesOther')&&(ui.endparent[0].id=='placesUser')){
        addPlaceToUser(user,place);
    }
    if ((ui.startparent[0].id=='placesUser')&&(ui.endparent[0].id=='placesOther')){
        delPlaceFromUser(user,place);
    }
    jQuery('#'+ui.endparent[0].id).sortable().one('sortupdate',function(ev, ui) { actionPlace(ui,ev);});
}

//вспомогательная функция сравнения объектов
function compareObjects(newObj, oldObj) {
    'use strict';
    var clone = "function" === typeof newObj.pop ? [] : {},
        changes = 0,
        prop = null,
        result = null,
        check = function(o1, o2) {
            for(prop in o1) {
                if(!o1.hasOwnProperty(prop)) continue;
                if(o1[prop] instanceof Date){
                    if(!(o2[prop] instanceof Date && o1[prop].getTime() == o2[prop].getTime())){
                        clone[prop] = newObj[prop];
                        changes++;
                    }
                }else if (o1[prop] && o2[prop] && "object" === typeof o1[prop]) {
                    if(result = compareObjects(newObj[prop], oldObj[prop])){
                        clone[prop] = "function" === typeof o1[prop].pop ? newObj[prop] : result;
                        changes++;
                    }
                }else if(o1[prop] !== o2[prop]){
                    clone[prop] = newObj[prop];
                    changes++;
                }
            }
        };
    check(newObj, oldObj);
    check(oldObj, newObj);
    return changes ? clone : false;
}
//вспомогательная функция чтения параметров GET из url
function getUrlVars() {
    var vars = {};
    var parts = window.location.href.replace(/[?&]+([^=&]+)=([^&]*)/gi, function(m,key,value) {
        vars[key] = value;
    });
    return vars;
}

function translit(myText){
	// Символ, на который будут заменяться все спецсимволы
	var space = '-'; 
	// Берем значение из нужного поля и переводим в нижний регистр
	var text = myText.toLowerCase();
	//var text = document.getElementById('name').value.toLowerCase();	
	// Массив для транслитерации
	var transl = { 
					'а': 'a', 'б': 'b', 'в': 'v', 'г': 'g', 'д': 'd', 'е': 'e', 'ё': 'e', 'ж': 'zh', 'з': 'z', 'и': 'i',
					'й': 'j', 'к': 'k', 'л': 'l', 'м': 'm', 'н': 'n', 'о': 'o', 'п': 'p', 'р': 'r', 'с': 's', 'т': 't',
					'у': 'u', 'ф': 'f', 'х': 'h', 'ц': 'c', 'ч': 'ch', 'ш': 'sh', 'щ': 'sh', 'ъ': space, 'ы': 'y',
					'ь': space, 'э': 'e', 'ю': 'yu', 'я': 'ya','.': '.',
					
					' ': space, '_': space, '`': space, '~': space, '!': space, '@': space, '#': space, '$': space,
					'%': space, '^': space, '&': space, '*': space, '(': space, ')': space, '-': space, '\=': space,
					'+': space, '[': space, ']': space, '\\': space, '|': space, '/': space, ',': space,
					'{': space, '}': space, '\'': space, '"': space, ';': space, ':': space, '?': space, '<': space,
					'>': space, '№': space					
				 }
	
    var result = '';
	
	var curent_sim = '';
	
    for(i=0; i < text.length; i++) {
        // Если символ найден в массиве то меняем его
		if(transl[text[i]] != undefined) {			
			if(curent_sim != transl[text[i]] || curent_sim != space){
				result += transl[text[i]];
				curent_sim = transl[text[i]];				
			}					
		}
		// Если нет, то оставляем так как есть
        else {
			result += text[i];
			curent_sim = text[i];
		}		
    }	
	
	result = TrimStr(result);	
        return result;
}

function TrimStr(s) {
	s = s.replace(/^-/, '');
	return s.replace(/-$/, '');
}

function filePreLoad (data,userId) {
if (data.files[0].size>1024*1024*300) {
    return {message:"Размер файла не должен превышать 300МБ"};
} 

//text = translit(data.files[0].name);

//if (/(^[a-zA-Z0-9а-яА-Я]+([a-zA-Zа-яА-Я\_0-9\.-]*))$/.test(data.files[0].name)==false) {
//    return {message:"В имени файла содержатся недопустимые символы!"};
//}

responseServer=$.parseJSON($.ajax({
     url: window.location.protocol+"//"+window.location.hostname+window.location.pathname+'?r=files/check-by-name',
     type: 'post',
     async: false,
     data: {
         'name':data.files[0].name,
         'user':userId
     }
    }).responseText);
if (responseServer.success==false){
   return {message:"Файл с таким именем уже существует. Пожалуйста, переименуйте загружаемый или загруженный файл, чтобы избежать совпадения имен"};
} 
return {};



}


function deleteRowFiles(id) {
     $.ajax({
     url: window.location.protocol+"//"+window.location.hostname+window.location.pathname+'?r=files/del-file-by-id',
     type: 'post',
     data: {
         'id':id
     },
     success: function (responseServer) {
         //responseServer=$.parseJSON(responseServer);
         return responseServer;
         
     }
    });       
}


function changeFileName(id){
    $.ajax({
     url: window.location.protocol+"//"+window.location.hostname+window.location.pathname+'?r=files/change-filename',
     type: 'post',
     data: {
         'id':id,
         'name':$('#fileName'+id).val()
     },
     success: function (responseServer) {
         responseServer=$.parseJSON(responseServer);
         if (responseServer.error === undefined) {
             location.reload();
         } else {
             $('#alert'+id).css('display','block');
             $('#alert'+id).html(responseServer.error);
         }
         
     }
    });    
}


function viewVideo(href){
    window.location.href=window.location.protocol+"//"+window.location.hostname+window.location.pathname+'?r=files/view&href='+href;
}


function changeUserFiles (id, admin) {
    id = id.replace('btn','');
    is_confirm = false;
    massJSON = new Object();
    $('.cell'+id).each(function(i,elem) {
         id2 = $(elem).attr('id').replace('cell','');
         mass = id2.split('_');
         confirmMes = mass[3];

         userId = mass[0];
         place = mass[1];
         fileId = mass[2];
         val = $(elem).val();
         massJSON[place] = val;
         if ((val == 0) && (confirmMes == '1')) {
             is_confirm = true;
         }

    });
    
    if (is_confirm == true) {
        if (confirm("Вы убрали метки с точек, которые уже были подтверждены ранее. Подтвердите действие")) {
                 $.ajax({
                    url: window.location.protocol+"//"+window.location.hostname+window.location.pathname+'?r=files/rewrite-file-places',
                    type: 'post',
                    data: {
                        places : JSON.stringify(massJSON),
                        user : userId,
                        file : fileId
                    },
                    success: function (responseServer) {
                        //responseServer=$.parseJSON(responseServer);
                        $('#alertPlace'+fileId).css('display','block');
                        if (admin == 0) {
                            mail = "Изменения успешно сохранены! О порядке и сроках подтверждения файлов Вы можете прочитать <a href='_blank'>здесь</a>";
                        } else {
                            mail = "Изменения успешно сохранены! Для дальнейшей работы с неподтвержденными файлами перейдите в раздел 'Заявки'"
                        }
                        $('#alertPlace'+fileId).html(mail);
                        
                           $('.cell'+id).each(function(i,elem) {
                                id3 = $(elem).attr('id').replace('cell','');
                                mass = id3.split('_');
                                confirmMes = mass[3];
                                place = mass[1];
                                fileId = mass[2];
                                el = $('[id*=tr'+place+"_"+fileId+']');
                                el.css('background','none');
                                
                                val = $(elem).val();
                                if ((val == 1) && ((confirmMes == 'null')||(confirmMes == '0'))) {
                                    $('#al'+place+"_"+fileId+"_0").css('display','inline');
                                } 
                                if ((val == 0)) {
                                    $('#al'+place+"_"+fileId+"_1").css('display','none');
                                    $('#al'+place+"_"+fileId+"_0").css('display','none');
                                } 
                                if ((val == 0) && (confirmMes == '1')) {
                                    $(elem).attr('id','cell'+mass[0]+"_"+place+"_"+fileId+"_null");
                                } 
                                
                                
                            });
                                    
                    }
                   }); 
        }
    } else {
        $.ajax({
            url: window.location.protocol+"//"+window.location.hostname+window.location.pathname+'?r=files/rewrite-file-places',
            type: 'post',
            data: {
                places : JSON.stringify(massJSON),
                        user : userId,
                        file : fileId
            },
            success: function (responseServer) {
                //responseServer=$.parseJSON(responseServer);
                        $('#alertPlace'+fileId).css('display','block');
                        if (admin == 0) {
                            mail = "Изменения успешно сохранены! О порядке и сроках подтверждения файлов Вы можете прочитать <a href='_blank'>здесь</a>";
                        } else {
                            mail = "Изменения успешно сохранены! Для дальнейшей работы с неподтвержденными файлами перейдите в раздел 'Заявки'"
                        }
                        $('#alertPlace'+fileId).html(mail);
                        $('.cell'+id).each(function(i,elem) {
                                id3 = $(elem).attr('id').replace('cell','');
                                mass = id3.split('_');
                                confirmMes = mass[3];
                                place = mass[1];
                                fileId = mass[2];
                                el = $('[id*=tr'+place+"_"+fileId+']');
                                el.css('background','none');
                                
                                val = $(elem).val();
                                if ((val == 1) && ((confirmMes == 'null')||(confirmMes == '0'))) {
                                    $('#al'+place+"_"+fileId+"_0").css('display','inline');
                                } 
                                if ((val == 0)) {
                                    $('#al'+place+"_"+fileId+"_1").css('display','none');
                                    $('#al'+place+"_"+fileId+"_0").css('display','none');
                                } 
                                if ((val == 0) && (confirmMes == '1')) {
                                    $(elem).attr('id','cell'+mass[0]+"_"+place+"_"+fileId+"_null");
                                }
                                
                                
                            });
            }
           }); 
    }
    

}

    function changeColorOnCheck (id) {
        id = id.replace('cell',''); 
        mass = id.split('_');
        place = mass[1];
        fileId = mass[2];
        el = $('[id*=tr'+place+"_"+fileId+']');
        changed = el.attr('id').replace('tr','').split('_')[2];
        if (changed == '0') {
            el.css('background','#EBE5E5');
            el.attr('id','tr'+place+"_"+fileId+"_1");
        } else {
            el.css('background','none');
            el.attr('id','tr'+place+"_"+fileId+"_0");           
        }

    }
    
   


function changeVideoblocks (id) {
    id = id.replace('btn','');
    massJSON = new Object();
    $('.cell'+id).each(function(i,elem) {
         id2 = $(elem).attr('id').replace('cell','');
         mass = id2.split('_');
         confirmMes = mass[3];

         userId = mass[0];
         place = mass[1];
         fileId = mass[2];
         val = $(elem).val();
         if (((val == 1)&&(confirmMes == 'null'))||((val == 0)&&(confirmMes == '1'))) {
            massJSON[place] = val;
        }
    });
    

    $.ajax({
       url: window.location.protocol+"//"+window.location.hostname+window.location.pathname+'?r=files/rewrite-playlist',
       type: 'post',
       data: {
           places : JSON.stringify(massJSON),
           file : fileId
       },
       beforeSend: function(){
           $('#alertPlace'+fileId).css('display','block');
           mail = "<img src='img/ajax-loader.gif'/> Ожидайте, выполняются операции с файлами на сервере"
           $('#alertPlace'+fileId).html(mail);
       },
       success: function (responseServer) {
           responseServer=$.parseJSON(responseServer);
           if (responseServer.success) {
           //$('#alertPlace'+fileId).css('display','block');

            mail = "Изменения успешно сохранены! Добавленные видео-блоки помещены в конец списка плэйлиста. <a href="+window.location.protocol+"//"+window.location.hostname+window.location.pathname+'?r=files/playlists'+">Перейти к плейлистам</a>" ;

           $('#alertPlace'+fileId).html(mail);

              $('.cell'+id).each(function(i,elem) {
                   id3 = $(elem).attr('id').replace('cell','');
                   mass = id3.split('_');
                   confirmMes = mass[3];
                   place = mass[1];
                   fileId = mass[2];
                   el = $('[id*=tr'+place+"_"+fileId+']');
                   el.css('background','none');

                   val = $(elem).val();
                   if (val == 1) {
                       $('#al'+place+"_"+fileId+"_1").css('display','inline');
                       $(elem).attr('id','cell'+mass[0]+"_"+place+"_"+fileId+"_1");
                   } 
                   if ((val == 0)) {
                       $('#al'+place+"_"+fileId+"_1").css('display','none');
                       $(elem).attr('id','cell'+mass[0]+"_"+place+"_"+fileId+"_null");
                   } 

               });
           } else {
               alert(responseServer.msg);
           }    
       }
      }); 
}

function coding(id) {
    if (confirm("Привести видеофайл к нужному формату?")) {
        $.ajax({
           url: window.location.protocol+"//"+window.location.hostname+window.location.pathname+'?r=files/coding',
           type: 'post',
           data: {
               file : id
           },
           success: function (responseServer) {
               if (responseServer != "error") {
                    $('#modal-convert').modal('show');
                     status = "";
                     myVar = setInterval(function(){
                         status = $.ajax({
                             url: window.location.protocol+"//"+window.location.hostname+window.location.pathname+'?r=files/check-file',
                             async: false,
                             type: 'post',
                             data: {
                             }
                         }).responseText; 

                         $.ajax({
                             url: window.location.protocol+"//"+window.location.hostname+window.location.pathname+'?r=files/check-cpu',
                             //async: false,
                             type: 'post',
                             data: {
                             },
                             success: function (responseServer) {
                                 $('#text-CPU').html(responseServer);
                             }
                         });                        


                         $('#text-load').html(status);
                         if (status.indexOf('endfile') != -1){
                             $.ajax({
                                 url: window.location.protocol+"//"+window.location.hostname+window.location.pathname+'?r=files/after-coding',
                                 type: 'post',
                                 data: {
                                     file : id
                                 },
                                 success: function (responseServer) {



                                 }
                             }); 
                             soundClick();
                             clearInterval(myVar);
                             $('#modal-convert').modal('hide');
                             location.reload();
                         }
                     },2000); 
                } else {
                    alert("Файл уже был преобразован ранее!");
                }
           }
          });                
    }
}

function soundClick() {
  var audio = new Audio(); // Создаём новый элемент Audio
  audio.src = 'sound.mp3'; // Указываем путь к звуку "клика"
  audio.autoplay = true; // Автоматически запускаем
}

function addToPlaylist (id2,placeIdMy,fileIdMy) {
    id = id2;
    id = id.replace('btn','');
    mass = id.split('_');
    placeId = mass[1];
    fileId = mass[0];    
    
    $.ajax({
           url: window.location.protocol+"//"+window.location.hostname+window.location.pathname+'?r=files/rewrite-playlist-prop',
           type: 'post',
           data: {
               place : placeId,
               file : fileId
           },
           beforeSend: function(){
               $('#'+id2).attr('disabled','true');
               id = id2.replace('btn','btncancel');
               $('#'+id).attr('disabled','true');
               mail = "<img src='img/ajax-loader.gif'/> Ожидайте"
               $('#'+id2).html(mail);
           },
           success: function (responseServer) {
               responseServer=$.parseJSON(responseServer);
               if (responseServer.success) {
                //$('#alertPlace'+fileId).css('display','block');
                    mail = "Подтверждено";
                    $('#'+id2).html(mail);
                    $("#play"+placeIdMy).attr("href",$("#play"+placeIdMy).attr("href")+"&fid="+fileIdMy); 
                    $("#play"+placeIdMy).click();

               } else {
                   alert(responseServer.msg);
               }    
           }
          });
    

}

function delProp (id2) {

    id = id2;
    id = id.replace('btncancel','');
    mass = id.split('_');
    placeId = mass[1];
    fileId = mass[0]; 
    $.ajax({
       url: window.location.protocol+"//"+window.location.hostname+window.location.pathname+'?r=files/delete-prop',
       type: 'post',
       data: {
           place : placeId,
           file : fileId
       },
       beforeSend: function(){
           mail = "<img src='img/ajax-loader.gif'/> Ожидайте";
           $('#'+id2).attr('disabled','true');
           id3 = id2.replace('btncancel','btn');
           $('#'+id3).attr('disabled','true');
           $('#'+id2).html(mail);
       },
       success: function (responseServer) {
           responseServer=$.parseJSON(responseServer);
           if (responseServer.success) {
           //$('#alertPlace'+fileId).css('display','block');

            mail = "Удалено";
            $('#'+id2).html(mail);
            $('#mail-abort').val('Вы подали заявку на размещение в точке '+$('#placef'+placeId).html()+' файла '+$('#namef'+fileId).html()+', но Вам было отказано по следующей причине:\r\n - \r\nС уважением, администратор');
            $('#btnsend').html('Отправить пользователю '+$('#emailf'+fileId).html()+' на e-mail');
            $('#btnsend').attr('id','btnsend'+fileId);
            $('#modal-not_agree').modal('show');


           } else {
               alert(responseServer.msg);
           }    
       }
      });    
}

function addToPL(id) {
    id = id.replace('btn','');
    mass = id.split('_');
    id = mass[0];
    flag = mass[1];
    if (flag == '1') {
        $('#filesPlaylist').append('<li id="file'+id+'" role="option" aria-grabbed="false" draggable="true"><div class="files-blocks"><table style="width:100%;"><tbody><tr><td style="width:90%; word-break: break-all;"><div class="file-name">'+$('#filename'+id).html()+'</div><div class="file-info">'+$('#fileinfo'+id).html()+'</div></td><td style="text-align:center"><button type="button" id="btn'+id+'" class="btn btn-danger btn-my" onclick="delPL(this);">x</button></td></tr></tbody></table></div></li>');
    } else {
        $('#filesPlaylist').append('<li id="file'+id+'" role="option" aria-grabbed="false" draggable="true"><div class="files-user"><table style="width:100%;"><tbody><tr><td style="width:90%; word-break: break-all;"><div class="file-name">'+$('#filename'+id).html()+'</div><div class="file-info">'+$('#fileinfo'+id).html()+'</div></td><td style="text-align:center"><button type="button" id="btn'+id+'" class="btn btn-danger btn-my" onclick="delPL(this);">x</button></td></tr></tbody></table></div></li>');
    }
    $('#filesPlaylist').sortable();
}

function delPL(id) {
    node = id.parentElement.parentElement.parentElement.parentElement.parentElement.parentElement;
    $(node).remove();
    $('#filesPlaylist').sortable();
}

function savePlaylist(pl){
    $('#filesPlaylist');
    massJSON = new Object();
    sort = 1;
    $('#filesPlaylist').children().each(function(i,elem) {
        massJSON[sort] = $(elem).attr('id').replace('file','');
        
        sort+=1;
    });
    
    $.ajax({
            url: window.location.protocol+"//"+window.location.hostname+window.location.pathname+'?r=files/write-playlist',
            type: 'post',
            data: {
                files : JSON.stringify(massJSON),
                place : pl
            },
            beforeSend: function(){
                $('#save-playlist').html("<img src='img/ajax-loader.gif'/> Сохранение плейлиста");
                $('#save-playlist').attr('disabled',true);
            },
            success: function (responseServer) {
                $('#save-playlist').attr('disabled',false);
                $('#save-playlist').html("Сохранить плейлист");
                $('#alert-playlist').css('display','block');
                setTimeout("$('#alert-playlist').css('display','none');",2000);
            }           
           }); 
    

}

function sendMailToUser(id){
    fileId = id.replace('btnsend','');
    $.ajax({
            url: window.location.protocol+"//"+window.location.hostname+window.location.pathname+'?r=files/send-mail-to-user',
            type: 'post',
            data: {
                id : fileId,
                mail : $('#mail-abort').val()
            },
            beforeSend: function(){
                mail = "<img src='img/ajax-loader.gif'/> Идет отправка письма, ожидайте";
                $('#btnsend'+fileId).attr('disabled',true);
                $('#btnnotsend').attr('disabled',true);
                $('#btnsend'+fileId).html(mail);
            },
            success: function (responseServer) {
                $('#btnsend'+fileId).attr('disabled',false);
                $('#btnnotsend').attr('disabled',false);
                $('#btnsend'+fileId).attr('id','btnsend');
                $('#modal-not_agree').modal('hide');
            }           
    });    
    
} 

function generatePromo(){
    $.ajax({
            url: window.location.protocol+"//"+window.location.hostname+window.location.pathname+'?r=files/generate-promo',
            type: 'post',
            data: {},
            success: function (responseServer) {
                location.reload();
            }           
    });        
}


function deletePromo(id){
    $.ajax({
            url: window.location.protocol+"//"+window.location.hostname+window.location.pathname+'?r=files/delete-promo',
            type: 'post',
            data: {
                id : id,
            },
            success: function (responseServer) {
                location.reload();
            }           
    });        
}
