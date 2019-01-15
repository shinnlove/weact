var dataPage = 0,
	allowScroll = true;
getAddress(dataPage);

function getAddress(idx) {
	if (allowScroll) {
		allowScroll = false;
		$(".addr-list").append('<div class="loading-bottom">加载中...</div>');
		$.getJSON("getCustomerAddressList.php?index=" + idx + "&length=12", function(data) {
			var htmlStr = "";
			$.each(data.result.addressList, function(i, e) {
				htmlStr += '<dl><dt>\
              <input name="" type="checkbox" value="" class="yes">\
            </dt>\
          <dd>\
		        <a class="addr" href="javascript:;" data-id="' + e.id + '" >\
		              <div class="item">' + e.province + e.city + e.district + '</div>\
		              <div class="item">' + e.detail + '</div>\
		              <div class="item">' + e.personName + '&nbsp;&nbsp;' + e.mobile + '</div>\
		         </a>\
		         <a href="javascript:;" data-addrjson=\'' + JSON.stringify(e) + '\' class="edit"></a>\
		       </dd>\
		    </dl>';
			});
			$('.addr-list').append(htmlStr);
			$(".loading-bottom").remove();
			allowScroll = data.result.all ? false : true;
		});
	}
}
$('.addr-list').on("click", "a.addr", function(e) {
	e.preventDefault();
	if (getUrlParam("from") == "order") {
		sessionStorage.receiveAddress = $(this).html();
		sessionStorage.receiveAddressId = $(this).data('id');
		location.href = "confirmOrderInfo.htm?from=" + getUrlParam('from');
	} else {
		$(this).parent().find("a.edit").click();
	}
}).on("click", "a.edit", function(e) {
	e.preventDefault();
	sessionStorage.receiveAddressJson = JSON.stringify($(this).data('addrjson'));
	//location.href="setCustomerAddress.htm?from="+getUrlParam('from');
	location.href = "setCustomerAddress.html";
}).on("click", "input[type='checkbox']", function(e) {
	$(this).closest("dl").find("a.addr").click();
});

$(".newAddress").on("click", function(e) {
	e.preventDefault();
	if (sessionStorage.receiveAddressJson) {
		sessionStorage.removeItem('receiveAddressJson');
	}
	//location.href="setCustomerAddress.htm?from="+getUrlParam('from');
	location.href = "setCustomerAddress.html";
});

scrollToLoadMore({
	callback: function() {
		getAddress(dataPage);
	},
	length: 12
});