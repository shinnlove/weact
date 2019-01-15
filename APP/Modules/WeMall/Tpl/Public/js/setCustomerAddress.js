$(function(){
    var loc = new Location();
    var title = ['省份' , '地级市' , '市、县、区'];
    Zepto.each(title , function(k , v) {
      title[k]  = '<option value="">'+v+'</option>';
    });
    $('#loc_province').append(title[0]);
    $('#loc_city').append(title[1]);
    $('#loc_town').append(title[2]);
    
    loc.fillOption('loc_province' , '0');

    $('#loc_province').change(function() {
      $('#loc_city').empty();
      $('#loc_city').append(title[1]);
      loc.fillOption('loc_city' , '0,'+$('#loc_province').val());
      $('#loc_city').change()
    })
    $('#loc_city').change(function() {
      $('#loc_town').empty();
      $('#loc_town').append(title[2]);
      loc.fillOption('loc_town' , '0,' + $('#loc_province').val() + ',' + $('#loc_city').val());
    })
    $('#loc_town').change(function() {
    })
    var addr="";
    if(sessionStorage.receiveAddressJson){
       addr = JSON.parse(sessionStorage.receiveAddressJson);
       $(".rName").val(addr.personName);
       $(".rMobile").val(addr.mobile);
       $(".addrDetail").val(addr.detail);
       $("#remove").data("id",addr.id);

       $(".rProvince option").each(function(){
        if($(this).text() === addr.province){
          $(this).prop("selected",true);
          $('#loc_province').change()
          return;
        }
       });
       $(".rCity option").each(function(){
        if($(this).text() === addr.city){
          $(this).prop("selected",true);
          $('#loc_city').change()
          return;
        }
       });
       $(".rDistrict option").each(function(){
        if($(this).text() === addr.district){
          $(this).prop("selected",true);
          return;
        }
       });
    }else{
      $("#remove").hide();
    }

    $("#submit").on("click",function(){
      var full = true,addressData = {
        personName:$.trim($(".rName").val()),
        country:"中国",
        province:$(".rProvince option:selected").text(),
        city:$(".rCity option:selected").text(),
        district:$(".rDistrict option:selected").text(),
        detail:$.trim($(".addrDetail").val()),
        mobile:$.trim($(".rMobile").val())
      },addressCheck={
        personName:$.trim($(".rName").val()),
        province:$(".rProvince").val(),
        city:$(".rCity").val(),
        district:$(".rDistrict").val(),
        detail:$.trim($(".addrDetail").val()),
        mobile:$.trim($(".rMobile").val())
      }
      for(var item in addressCheck){
        if(addressCheck[item] == ""){
          full = false;
        }
      }
      if(full){
        if(sessionStorage.receiveAddressJson){ //有sessionStorage说明为修改
          Zepto.ajax({
            type:"post",
            url:"updateCustomerAddress.json",
            dataType:"json",
            data: $.extend(addressData,{id:addr.id}),
            success:function(data){
              updateAddress(data);
            }
          });
        }else{
          Zepto.ajax({
            type:"post",
            url:"insertCustomerAddress.json",
            dataType:"json",
            data:addressData,
            success:function(data){
              updateAddress(data);
            }
          });
        }
      }else{
        mobileAlert("请填写完整信息");
      }
    });
    $("#giveup").on("click",function(){
      if(confirm("确认不保存退出？")){
        history.go(-1);
      }
    });
    $("#remove").on("click",function(){
      if(confirm("确认删除该地址？")){
        var id = $(this).data("id");
        $.getJSON("deleteCustomerAddress.json?id="+id,function(data){
          if(data.status=="0"){
            if(getUrlParam('from') == "order"){
              location.href="confirmOrderInfo.htm?from=order";
            }else{
              location.href="getCustomerAddressList.htm";
            }
          }
        })
      }
    });
    function updateAddress(data){
      if(getUrlParam('from') == "order"){
        sessionStorage.receiveAddress = 
          '<div class="item">'+data.result.address.province+data.result.address.city+data.result.address.district+'</div>\
          <div class="item">'+data.result.address.detail+'</div>\
          <div class="item">'+data.result.address.personName+'&nbsp;&nbsp;'+data.result.address.mobile+'</div>';
        sessionStorage.receiveAddressId = data.result.address.id;
        location.href="confirmOrderInfo.htm?from=order";
      }else{
        location.href="getCustomerAddressList.htm";
      }
    }
});