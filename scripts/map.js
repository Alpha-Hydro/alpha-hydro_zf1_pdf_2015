map_init = false;

function mapInit(){
	if ( map_init ) return false;
	
	var myLatlng = new google.maps.LatLng(59.930348, 30.484946);
	var myOptions = {
		zoom : 16,
		center : myLatlng,
		mapTypeId : google.maps.MapTypeId.ROADMAP,
	};
	
	var map = new google.maps.Map(document.getElementById("map_canvas"),
			myOptions);
	var contentString = '<div style="color: #3e3e3e;"><b>«Альфа-Гидро»</b><br />г. Санкт-Петербург, ул. Кржижановского, д.12/1 </div>';
	var infowindow = new google.maps.InfoWindow({
		content : contentString
	});
	var marker = new google.maps.Marker({
		position : myLatlng,
		map : map,
		title : 'Офис "Альфа-Гидро"'
	});
	google.maps.event.addListener(marker, 'click', function() {
		infowindow.open(map, marker);
	});
	
	map_init = true;
}
