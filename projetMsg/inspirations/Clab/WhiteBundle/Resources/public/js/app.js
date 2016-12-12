var geocoder = new google.maps.Geocoder();
// initialize custom form elements
function geolocate($input)
{
    navigator.geolocation.getCurrentPosition(function(position) {
        latlng = new google.maps.LatLng(position.coords.latitude, position.coords.longitude);

        geocoder.geocode({ 'latLng': latlng }, function(results, status) {
            if (status == google.maps.GeocoderStatus.OK && results[0]) {
                $input.val(results[0].formatted_address);
                $('#bh-sl-user-location').submit();
            } else {
                alert('Impossible de vous géolocaliser : ' + status);
            }
        });
  });
}