<?php
	
$extension_route['hotellink'] = 'hotellink_integration/index';
$extension_route['signin_hotellink'] = 'hotellink_integration/signin_hotellink';
$extension_route['hotellink_properties/(:any)'] = 'hotellink_integration/hotellink_properties/$1';
$extension_route['hotellink_get_room_types'] = 'hotellink_integration/get_room_types';
$extension_route['save_hotellink_mapping_AJAX'] = 'hotellink_integration/save_hotellink_mapping_AJAX';
$extension_route['hotellink_update_availability'] = 'hotellink_integration/hotellink_update_availability';
$extension_route['hotellink_update_restrictions'] = 'hotellink_integration/hotellink_update_restrictions';
$extension_route['deconfigure_hotellink_AJAX'] = 'hotellink_integration/deconfigure_hotellink_AJAX';
$extension_route['hotellink_update_full_refresh'] = 'hotellink_integration/update_full_refresh';
$extension_route['cron/hotellink_get_bookings/(:any)'] = 'hotellink_bookings/hotellink_get_bookings/$1';
$extension_route['hotellink_refresh_token'] = 'hotellink_integration/hotellink_refresh_token';
$extension_route['refresh_token'] = 'hotellink_bookings/hotellink_refresh_token';

