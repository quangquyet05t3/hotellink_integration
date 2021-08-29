<?php

if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

class HotellinkIntegration
{
    function __construct()
    {   
        $this->ci =& get_instance();
        $this->ci->load->model('Hotellink_model');

        $this->hotellink_url = ($this->ci->config->item('app_environment') == "development") ? "http://api.hotellinksolutions-staging.com" : "http://api.hotellinksolutions-staging.com";
    }

    public function call_api($api_url, $method, $data, $headers, $method_type = 'POST'){

        $url = $api_url . $method;
        
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
            
        if($method_type == 'GET'){

        } else {
            curl_setopt($curl, CURLOPT_POST, 1);
            curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($data));
        }
               
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
        $response = curl_exec($curl);
        
        curl_close($curl);
        
        return $response;
    }

    public function signin_hotellink($email, $password){

        $api_url = $this->hotellink_url;
        $method = '/external/oauth/token';

        $data = array();

        $headers = array(
            "Authorization: Basic ".base64_encode(sprintf('%s:%s', $email, $password))
        );

        $response = $this->call_api($api_url, $method, $data, $headers);

        return $response;
    }

    public function refresh_token($email, $password){

        $api_url = $this->hotellink_url;
        $method = '/external/oauth/token';

        $data = array();

        $headers = array(
            "Authorization: Basic ".base64_encode(sprintf('%s:%s', $email, $password))
        );

        $response = $this->call_api($api_url, $method, $data, $headers);

        return $response;
    }

    public function get_properties($token){

        return '';
    }

    public function get_rate_plans($property_id, $property_key, $token){

        $api_url = $this->hotellink_url;
        $method = '/external/pms/getRatePlans';
        $method_type = 'POST';

        $data = array(
            "Credential" => array(
                "HotelId" => $property_id,
                "HotelAuthenticationChannelKey" => $property_key
            ),
            "Lang" => "en"
        );

        $headers = array(
            "Authorization: Bearer ".$token,
            "Content-Type: application/json"
        );

        $response = $this->call_api($api_url, $method, $data, $headers, $method_type);

        return $response;
    }


    public function update_inventories($data, $token){

        $api_url = $this->hotellink_url;
        $method = '/external/pms/saveInventory';
        $method_type = 'POST';

        $headers = array(
            "Authorization: Bearer ".$token,
            "Content-Type: application/json"
        );

        $response = $this->call_api($api_url, $method, $data, $headers, $method_type);

        return $response;
    }

    public function get_bookings($data, $token){

        $api_url = $this->hotellink_url;
        $method = '/external/pms/getBookings';
        $method_type = 'POST';

        $headers = array(
            "Authorization: Bearer ".$token,
            "Content-Type: application/json"
        );

        $response = $this->call_api($api_url, $method, $data, $headers, $method_type);

        return $response;
    }

    public function send_booking_confirmed($data, $token){

        $api_url = $this->hotellink_url;
        $method = '/external/pms/readNotification';
        $method_type = 'POST';

        $headers = array(
            "Authorization: Bearer ".$token,
            "Content-Type: application/json"
        );

        $response = $this->call_api($api_url, $method, $data, $headers, $method_type);

        return $response;
    }
}