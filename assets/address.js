function validName(descr,name)
{
    var fullName;
    if (/^(Респ)?(обл)?(г)?(Аобл)?(у)?(п)?(тер)?(х)?(с)?(д)?(ст)?(мкр)?(у)?(м)?(жд_ст)?(ул)?(пер)?(туп)?(пл)?(стр)?(платф)?(ст)?$/i.test(descr)){
        descr=descr+'.';
    }
    
    if (/^(край)?(обл)?(Аобл)?(АО)?(р-н)?(у)?(с\/с)?(с\/п)?(п\/о)?(с\/мо)?(с\/а)?(дп)?(с\/о)?(x)?(\.)?$/i.test(descr)) {
        fullName=name+' '+descr;
    } else {
        fullName=descr+' '+name;
    }
    return fullName;
}


function clearIfBlank (id,parentName,hiddenId,type) 
{
    if (flagEmpty==false){
        if (parseInt($('[name="'+parentName+'['+hiddenId+']"]').val())==0) {
            if (type==1) {
                $('[name="'+parentName+'[area]"]').val("");
                $('#codeArea').val("000");
            }
            if (type<=2) {
                $('[name="'+parentName+'[city]"]').val("");
                $('#codeCity').val("000");
            }
            if (type<=3) {
                $('[name="'+parentName+'[locality]"]').val("");
                $('#codeLocality').val("000");
            }
            if (type<=4) {
                $('[name="'+parentName+'[street]"]').val("");
                $('#codeStreet').val("0000");
            }
            $('#'+id).val(""); 
            setFieldsStatus(type,parentName);
        }
    } else
    {
        
    }
}


function fillAddress(parentName, name){
    fillName=($('[name="'+parentName+'[region]"]').val()=="") ? "" : $('[name="'+parentName+'[region]"]').val()+", ";
    fillName2=($('[name="'+parentName+'[area]"]').val()=="") ? "":$('[name="'+parentName+'[area]"]').val()+", ";
    fillName3=($('[name="'+parentName+'[city]"]').val()=="") ? "":$('[name="'+parentName+'[city]"]').val()+", ";
    fillName4=($('[name="'+parentName+'[locality]"]').val()=="") ? "":$('[name="'+parentName+'[locality]"]').val()+", ";
    fillName5=($('[name="'+parentName+'[street]"]').val()=="") ? "":$('[name="'+parentName+'[street]"]').val()+", ";
    $('[name="'+parentName+'[address]"]').val((fillName+fillName2+fillName3+fillName4+fillName5).slice(0,-2));
}

function clearAll(type,parentName,level)
{
    if (level==0) {
        if (type==1) {
            $('[name="'+parentName+'[area]"]').val("");
            $('#codeRegion').val("00");               
        }
        if (type<=2) {
            $('[name="'+parentName+'[city]"]').val("");
            $('#codeArea').val("000");
        }
        if (type<=3) {
            $('[name="'+parentName+'[locality]"]').val("");
            $('#codeCity').val("000");
        }
        if (type<=4) {
            $('[name="'+parentName+'[street]"]').val("");
            $('#codeLocality').val("000");
        }
        if (type<=5) {
            $('#codeStreet').val("0000");
        }
    } else {
        if (type==1) {
            $('[name="'+parentName+'[area]"]').val("");
            $('#codeArea').val("00");               
        }
        if (type<=2) {
            $('[name="'+parentName+'[city]"]').val("");
            $('#codeCity').val("000");
        }
        if (type<=3) {
            $('[name="'+parentName+'[locality]"]').val("");
            $('#codeLocality').val("000");
        }
        if (type<=4) {
            $('[name="'+parentName+'[street]"]').val("");
            $('#codeStreet').val("000");
        }     
        
    }
}

function getSource(request,response)
{  
    $.ajax({
     url: 'http://localhost/basic/web/index.php?r=address/get-address',
     type: 'post',
     data: {
         'data':this.element.activeElement.value,
         'type':this.type,
         'region':$('[name="'+this.parentName+'[codeRegion]"]').val(),
         'area':$('[name="'+this.parentName+'[codeArea]"]').val(),
         'city':$('[name="'+this.parentName+'[codeCity]"]').val(),
         'locality':$('[name="'+this.parentName+'[codeLocality]"]').val(),
         'street':$('[name="'+this.parentName+'[codeStreet]"]').val()
     },
     success: function (responseServer) {
         responseServer=$.parseJSON(responseServer);
         var objAddress={};
         var obj = [];
         for (var index in responseServer){
            var objAddress={};
            fullName=validName(responseServer[index].descr,responseServer[index].name);
            objAddress.label=fullName;
            objAddress.id=responseServer[index].id;
            obj[obj.length] = objAddress;
         }
         response(obj); 
     },
     beforeSend: $.proxy(function () {
         $('#'+this.id).css('background-image','url(\'img/ajax-loader.gif\')');
     },{'id':this.element.activeElement.id}),
     complete: $.proxy(function () {
         $('#'+this.id).css('background-image','none');
     },{'id':this.element.activeElement.id})
    });            
                
}   

function selectActions(event, ui)
{
    switch(this.type) {
        case 1:
            $('[name="'+this.parentName+'[codeRegion]"]').val(ui.item.id.substring(0,2));
            break;
        case 2:
            $('[name="'+this.parentName+'[codeArea]"]').val(ui.item.id.substring(2,5));
            break;
        case 3:
            $('[name="'+this.parentName+'[codeCity]"]').val(ui.item.id.substring(5,8));
            break;
        case 4:
            $('[name="'+this.parentName+'[codeLocality]"]').val(ui.item.id.substring(8,11));
            break;
        case 5:
            $('[name="'+this.parentName+'[codeStreet]"]').val(ui.item.id.substring(11,15));
            break;   
    }
    clearAll(this.type,this.parentName,1);
    setFieldsStatus(this.type,this.parentName); 
}

function setFieldsStatus(type,parentName)
{
    if (type<5) {
        $.ajax({
            url: 'http://localhost/basic/web/index.php?r=address/geta',
            type: 'post',
            data: {
                'type':type,
                'region':$('[name="'+parentName+'[codeRegion]"]').val(),
                'area':$('[name="'+parentName+'[codeArea]"]').val(),
                'city':$('[name="'+parentName+'[codeCity]"]').val(),
                'locality':$('[name="'+parentName+'[codeLocality]"]').val(),
                'street':$('[name="'+parentName+'[codeStreet]"]').val()
            },
            success: function (responseServer) {
                responseServer=$.parseJSON(responseServer);
                statuses=responseServer;
                if ("statusStreet" in statuses){
                    if (statuses.statusStreet==0){
                        $('button#street').attr('disabled',true);
                        $('input#modeladdress-street').attr('disabled',true);
                    } else {
                        $('button#street').attr('disabled',false);
                        $('input#modeladdress-street').attr('disabled',false);
                    }
                } 
                if ("statusArea" in statuses){
                    if (statuses.statusArea==0){
                        $('button#area').attr('disabled',true);
                        $('input#modeladdress-area').attr('disabled',true);
                    } else {
                        $('button#area').attr('disabled',false);
                        $('input#modeladdress-area').attr('disabled',false);
                    }
                }  
                if ("statusCity" in statuses){
                    if (statuses.statusCity==0){
                        $('button#city').attr('disabled',true);
                        $('input#modeladdress-city').attr('disabled',true);
                    } else {
                        $('button#city').attr('disabled',false);
                        $('input#modeladdress-city').attr('disabled',false);
                    }
                } 
                if ("statusLocality" in statuses){
                    if (statuses.statusLocality==0){
                        $('button#locality').attr('disabled',true);
                        $('input#modeladdress-locality').attr('disabled',true);
                    } else {
                        $('button#locality').attr('disabled',false);
                        $('input#modeladdress-locality').attr('disabled',false);
                    }
                } 
            },
            beforeSend: $.proxy(function () {
                if (type<=4) {
                    $('#modeladdress-street').css('background-image','url(\'img/ajax-loader.gif\')');
                }
                if (type<=3) {
                    $('#modeladdress-locality').css('background-image','url(\'img/ajax-loader.gif\')');
                }  
                if (type<=2) {
                    $('#modeladdress-city').css('background-image','url(\'img/ajax-loader.gif\')');
                }
                if (type==1) {
                    $('#modeladdress-area').css('background-image','url(\'img/ajax-loader.gif\')');
                }
            },{'type':type}),
            complete: $.proxy(function () {
                if (type<=4) {
                    $('#modeladdress-street').css('background-image','none');
                }
                if (type<=3) {
                    $('#modeladdress-locality').css('background-image','none');
                }  
                if (type<=2) {
                    $('#modeladdress-city').css('background-image','none');
                }
                if (type==1) {
                    $('#modeladdress-area').css('background-image','none');
                }
            },{'type':type})
        });                       
    }
  
}
var flagEmpty=false;