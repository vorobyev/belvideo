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

function clearAll(type,parentName)
{
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
}

function getSource(request,response)
{  
    var now = new Date();
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
         var now2 = new Date();
         now3=now2-now;
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
        }
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
}