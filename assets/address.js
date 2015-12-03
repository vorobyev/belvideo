function getPopupAddress (id,value,type, parentName, regionId, areaId, cityId, localityId, streetId, areaIdReal, cityIdReal, localityIdReal, streetIdReal){
        $.ajax({
            url: 'http://localhost/basic/web/index.php?r=address/get-address',
            type: 'post',
            data: {
                'data':value,
                'type':type,
                'region':$('[name="'+parentName+'['+regionId+']"]').val(),
                'area':$('[name="'+parentName+'['+areaId+']"]').val(),
                'city':$('[name="'+parentName+'['+cityId+']"]').val(),
                'locality':$('[name="'+parentName+'['+localityId+']"]').val(),
                'street':$('[name="'+parentName+'['+streetId+']"]').val()
            },
            success: function (response) {
                response=$.parseJSON(response);
                switch(type) {
                    case 1:
                        $('[name="'+parentName+'['+regionId+']"]').val('00');
                        break;
                    case 2:
                        $('[name="'+parentName+'['+areaId+']"]').val('000');
                        break;
                    case 3:
                        $('[name="'+parentName+'['+cityId+']"]').val('000');
                        break;
                    case 4:
                        $('[name="'+parentName+'['+localityId+']"]').val('000');
                        break;
                    case 5:
                        $('[name="'+parentName+'['+streetId+']"]').val('0000');
                        break;
                } 
                var objAddress={};
                var obj = [];
                for (var index in response){
                   var objAddress={};
                   objAddress.label=response[index].name+" "+response[index].descr;
                   objAddress.id=response[index].id;
                   obj[obj.length] = objAddress;
                }

                $('#'+id).autocomplete({
                    "source":obj,
                    select: function(event, ui) {
                        switch(type) {
                            case 1:
                                $('[name="'+parentName+'['+regionId+']"]').val(ui.item.id.substring(0,2));
                                break;
                            case 2:
                                $('[name="'+parentName+'['+areaId+']"]').val(ui.item.id.substring(2,5));
                                break;
                            case 3:
                                $('[name="'+parentName+'['+cityId+']"]').val(ui.item.id.substring(5,8));
                                break;
                            case 4:
                                $('[name="'+parentName+'['+localityId+']"]').val(ui.item.id.substring(8,11));
                                break;
                            case 5:
                                $('[name="'+parentName+'['+streetId+']"]').val(ui.item.id.substring(11,15));
                                break;                               
                        }
                        if (areaIdReal!==undefined) {
                            $('[name="'+parentName+'['+areaIdReal+']"]').val("");
                            $('#code'+ucwords(areaIdReal)).val("000");
                        }
                        if (cityIdReal!==undefined) {
                            $('[name="'+parentName+'['+cityIdReal+']"]').val("");
                            $('#code'+ucwords(cityIdReal)).val("000");
                        }
                        if (localityIdReal!==undefined) {
                            $('[name="'+parentName+'['+localityIdReal+']"]').val("");
                            $('#code'+ucwords(localityIdReal)).val("000");
                        }
                        if (streetIdReal!==undefined) {
                            $('[name="'+parentName+'['+streetIdReal+']"]').val("");
                            $('#code'+ucwords(streetIdReal)).val("0000");
                        }
                    }
                });
            }
        }); 
}

function clearIfBlank (id,parentName,hiddenId,areaId,cityId,localityId,streetId) 
{
    if (parseInt($('[name="'+parentName+'['+hiddenId+']"]').val())==0) {
        if (areaId!==undefined) {
            $('[name="'+parentName+'['+areaId+']"]').val("");
            $('#code'+ucwords(areaId)).val("000");
        }
        if (cityId!==undefined) {
            $('[name="'+parentName+'['+cityId+']"]').val("");
            $('#code'+ucwords(cityId)).val("000");
        }
        if (localityId!==undefined) {
            $('[name="'+parentName+'['+localityId+']"]').val("");
            $('#code'+ucwords(localityId)).val("000");
        }
        if (streetId!==undefined) {
            $('[name="'+parentName+'['+streetId+']"]').val("");
            $('#code'+ucwords(streetId)).val("0000");
        }
    $('#'+id).val("");    
    }
}

function ucwords(string) {  
    return string.charAt(0).toUpperCase() + string.substr(1);  
} 
