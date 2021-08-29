<?php

add_action('hotellink_update_availability', 'hotellink_update_availability_fn', 10, 1);
    
function hotellink_update_availability_fn ($data) {

    $CI = &get_instance();
    $CI->load->model('../extensions/hotellink_integration/models/Hotellink_model');
    $CI->load->model('../extensions/hotellink_integration/models/Room_type_model');
    $CI->load->model('../extensions/hotellink_integration/models/Companies_model');
    $CI->load->library('../extensions/hotellink_integration/libraries/HotellinkIntegration');
    if($data['start_date'] && $data['end_date']){
        $start_date = date('Y-m-d', strtotime($data['start_date']));
        $end_date = date('Y-m-d', strtotime($data['end_date']));
    } else {
        $start_date = date("Y-m-d");
        $end_date = Date("Y-m-d", strtotime("+500 days", strtotime($start_date)));
    }
    $room_type_id = $data['room_type_id'];
    
    $update_from = $data['update_from'];

    if($update_from == 'extension')
        $CI->company_id = $data['company_id'];

    $hotellink_x_company = $CI->Hotellink_model->get_hotellink_x_company_by_channel($CI->ota_key, $CI->company_id);
    $property_id = $hotellink_x_company['ota_property_id'] ;
    $property_key = $hotellink_x_company['ota_property_key'] ;
    $ota_x_company_id = $hotellink_x_company['ota_x_company_id'] ;

    if($room_type_id)
        $room_type_data = $CI->Hotellink_model->get_hotellink_room_types_by_id($room_type_id);
    else
        $room_type_data = $CI->Hotellink_model->get_hotellink_room_types_by_id(null, $CI->company_id, $ota_x_company_id);

    $avail_array["values"] = $availability_data = array();
    if($room_type_data){

        $get_ota = $CI->Hotellink_model->get_hotellink_data($CI->company_id, $CI->ota_key);

        if($update_from == 'extension'){

            $company_key_data = $CI->Companies_model->get_company_api_permission($CI->company_id);
            $company_access_key = isset($company_key_data[0]['key']) && $company_key_data[0]['key'] ? $company_key_data[0]['key'] : null;

            $room_types_avail_array = $CI->Room_type_model->get_room_type_availability(
                $CI->company_id,
                $get_ota['ota_id'], 
                $start_date, 
                $end_date, 
                null, 
                null, 
                true,
                $company_access_key
            );
        } else {
            $room_types_avail_array = $CI->Room_type_model->get_room_type_availability(
                $CI->company_id,
                $get_ota['ota_id'], 
                $start_date, 
                $end_date, 
                null, 
                null, 
                true
            );
        }
        
        foreach ($room_types_avail_array as $key => $value) {
            foreach ($room_type_data as $key1 => $value1) {
                if($key == $value1['minical_room_type_id']){
                    $room_types_avail_array[$key]['ota_room_type_id'] = $value1['ota_room_type_id'];
                }
            }
        }

        //Convert to hotel link request
        $avail_array = $availability_data = $inventories = [];
        foreach ($room_types_avail_array as $key => $value) {
            $room_id = $value['ota_room_type_id'];
            //For $inventories
            $inventory = [];
            $inventory['RoomId'] = $room_id;
            //Start $availabilities
            $availabilities = [];
            foreach ($value['availability'] as $key1 => $avail) {
                if(isset($value['ota_room_type_id']) && $value['ota_room_type_id']){
                    $from = $avail['date_start'];
                    $to = ($update_from == 'extension' ? Date("Y-m-d", strtotime("-1 day", strtotime($avail['date_end']))) : $avail['date_end']);
                    if(strtotime($from) > strtotime($to)) {
                        continue;
                    }

                    $avail = (int)$avail['availability'];
                    $availability = [];
                    $availability['DateRange'] = [
                        'From' => $from,
                        'To' => $to
                    ];
                    $availability['Quantity'] = $avail;
                    //$availability['ReleasePeriod'] = $release;
                    $availability['Action'] = 'Set';
                    $availabilities[] = $availability;
                }
            }
            $inventory['Availabilities'] = $availabilities;
            $inventories[] = $inventory;
        }
        $availability_data['Inventories'] = $inventories;

        $availability_data['Credential'] = [
            "HotelId" => $property_id,
            "HotelAuthenticationChannelKey" => $property_key
        ];
        $availability_data['Lang'] = 'en';
        $avail_array[] = $availability_data;

        $get_token_data = $CI->Hotellink_model->get_token(null, $CI->company_id, $CI->ota_key);

        if($CI->hotellink_refresh_token()){
            $get_token_data = $CI->Hotellink_model->get_token(null, $CI->company_id, $CI->ota_key);
        }

        $token_data = json_decode($get_token_data['meta_data']);
        $token = $token_data->data->access_token;

        prx($avail_array, 1);
        foreach ($avail_array as $data) {
            $response = $CI->hotellinkintegration->update_inventories($data, $token);
            save_logs($property_id, 0, 0, json_encode($avail_array), $response);
            $response = json_decode($response, true);
            echo 'availability resp = ';prx($response, 1);
        }
    }
}

function hotellink_refresh_token(){
    $CI = &get_instance();
    $CI->load->model(array('Hotellink_model','Room_type_model'));

    $hotellink_data = $CI->Hotellink_model->get_hotellink_data($CI->company_id);

    if($hotellink_data){
        if(isset($hotellink_data['created_date']) && $hotellink_data['created_date']){
            
            $timestamp = strtotime($hotellink_data['created_date']); //1373673600

            // getting current date 
            $cDate = strtotime(date('Y-m-d H:i:s'));

            // Getting the value of old date + 24 hours
            $oldDate = $timestamp + 86400; // 86400 seconds in 24 hrs

            if($oldDate < $cDate)
            {
                $token_data = json_decode($hotellink_data['meta_data']);

                $refresh_token = $token_data->data->attributes->refresh_token;

                $get_refresh_token_data = $CI->hotellinkintegration->refresh_token($refresh_token);
                $response = json_decode($get_refresh_token_data);

                if(isset($response->data) && $response->data){

                    $data = array(
                                    'meta_data' => $get_refresh_token_data,
                                    'created_date' => date('Y-m-d H:i:s'),
                                    'email' => $response->data->relationships->user->data->attributes->email,
                                    'company_id' => $CI->company_id,
                                );

                    $CI->Hotellink_model->update_token($data);
                    return true;
                }
            } else {
                return false;
            }
        }
    }
}

function hotellink_save_logs($ota_property_id = null, $request_type = null, $response_type = null, $xml_in = null, $xml_out = null) {

    $CI = &get_instance();
    $CI->load->model(array('Hotellink_model'));

    $data = array(
                    'ota_property_id' => $ota_property_id ? $ota_property_id : null,
                    'request_type' => ($request_type || $request_type == 0) ? $request_type : null,
                    'response_type' => ($response_type || $response_type == 0) ? $response_type : null,
                    'xml_in' => $xml_in ? $xml_in : null,
                    'xml_out' => $xml_out ? $xml_out : null,
                );
    $CI->Hotellink_model->save_logs($data);
}