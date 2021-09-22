<?php
class Hotellink_integration extends MY_Controller
{
    public $module_name;
    public $ota_key;
    public $ota_name;

    function __construct()
    {
        parent::__construct();
        $this->module_name = $this->router->fetch_module();
        $this->load->model('../extensions/'.$this->module_name.'/models/Hotellink_model');
        $this->load->model('../extensions/'.$this->module_name.'/models/Room_type_model');
        $this->load->model('../extensions/'.$this->module_name.'/models/Rate_plan_model');
        $this->load->model('../extensions/'.$this->module_name.'/models/Rooms_model');
        $this->load->model('../extensions/'.$this->module_name.'/models/Rates_model');
        $this->load->model('../extensions/'.$this->module_name.'/models/Currency_model');


        $this->load->library('../extensions/'.$this->module_name.'/libraries/HotellinkIntegration');
        $view_data['menu_on'] = true;

        $this->load->vars($view_data);
        $this->ota_key = 'hotellink';
        $this->ota_name = 'Hotel Link';
    }

    function index() {
        $this->hotellink();
    }

    function hotellink()
    {

        $data['company_id'] = $this->company_id;

        $data['main_content'] = '../extensions/'.$this->module_name.'/views/hotellink_authentication';
        $ota_id = $this->Hotellink_model->get_ota_id($this->ota_key);
        $data['hotellink_data'] = $this->Hotellink_model->get_hotellink_data($ota_id);

        $this->template->load('bootstrapped_template', null , $data['main_content'], $data);
    }

    function signin_hotellink(){
        $channel_key = $this->input->post('channel_key');
        $username = getenv("HOTELLINK_USERNAME");
        $password = getenv("HOTELLINK_PASSWORD");
        $authentication = $this->hotellinkintegration->signin_hotellink($username, $password);
        $response = json_decode($authentication, true);

        $is_valid_creds = false;
        if(isset($response['result']) && $response['result'] && isset($response['data']) && $response['data']){

            $data = array(
                'email' => $channel_key,
                'password' => $channel_key,
                'company_id' => $this->company_id,
                'meta_data' => $authentication,
                'created_date' => date('Y-m-d H:i:s'),
            );

            $hotellink_data = $this->Hotellink_model->get_data_by_email($channel_key, $this->company_id);

            if($hotellink_data){
                $this->Hotellink_model->update_token($data);
                $hotellink_id = $hotellink_data['id'];
            } else {
                $ota_id = $this->Hotellink_model->get_ota_id($this->ota_key);
                if(!$ota_id) {
                    $ota_data = array(
                        'key' => $this->ota_key,
                        'name' => $this->ota_name,
                    );
                    $ota_id = $this->Hotellink_model->save_ota($ota_data);
                }
                $data['ota_id'] = $ota_id;
                $data['company_id'] = $this->company_id;
                $hotellink_id = $this->Hotellink_model->save_token($data);
            }

            $is_valid_creds = true;
        }

        if($is_valid_creds){
            echo json_encode(array('success' => true, 'msg' => 'Authenticated successfully.', 'hotellink_id' => $hotellink_id));
        } else {
            echo json_encode(array('success' => false, 'msg' => 'Unauthorized.'));
        }
    }

    function deconfigure_hotellink_AJAX(){
        $ota_manager_id = $this->input->post('hotellink_id');

        $this->Hotellink_model->deconfigure_hotellink($ota_manager_id);
        echo json_encode(array('success' => true));
    }

    function hotellink_properties($hotellink_id)
    {
        $data['company_id'] = $this->company_id;
        $data['main_content'] = '../extensions/'.$this->module_name.'/views/hotellink_properties';

        $hotellink_prop_data = $this->Hotellink_model->get_properties_by_company_id($this->company_id, $hotellink_id);
        $get_token_data = $this->Hotellink_model->get_token($hotellink_id);

        if($hotellink_prop_data){
            //$data['properties'] = json_decode($hotellink_prop_data['channex_property_data'], true);
        } else {

            if($get_token_data){

                if($this->hotellink_refresh_token()){
                    $get_token_data = $this->Hotellink_model->get_token($hotellink_id);
                }

                $token_data = json_decode($get_token_data['meta_data']);

                $token = $token_data->data->access_token;

                $ch_prop_data = array(
                    'ota_manager_id' => $hotellink_id,
                    'company_id' => $this->company_id,
                    'channex_property_data' => '',
                );
                $this->Hotellink_model->save_properties($ch_prop_data);
            }
        }

        $data['hotellink_room_types'] = $this->Hotellink_model->get_hotellink_room_types($this->company_id, $hotellink_id);
        $data['hotellink_rate_plans'] = $this->Hotellink_model->get_hotellink_rate_plans($this->company_id, $hotellink_id);

        $data['hotellink_room_types_data'] = array();
        $is_mapping = false;

        if($data['hotellink_room_types'] && $data['hotellink_rate_plans']){
            $property_id = $data['hotellink_room_types'][0]['ota_property_id'];
            $channel_key = $get_token_data['email'];

            if($this->hotellink_refresh_token()){
                $get_token_data = $this->Hotellink_model->get_token($hotellink_id);
            }

            $token_data = json_decode($get_token_data['meta_data']);

            $token = $token_data->data->access_token;
            $is_mapping = true;

            $rate_plans_data = $this->hotellinkintegration->get_rate_plans($property_id, $channel_key, $token);
            $hotellink_rate_plans = json_decode($rate_plans_data, true);

            /*if (isset($hotellink_rate_plans['data']) && count($hotellink_rate_plans['data']) > 0) {
                foreach ($hotellink_rate_plans['data'] as $key => $value) {
                    if(
                        isset($value['relationships']) &&
                        isset($value['relationships']['parent_rate_plan'])
                    ) {
                        unset($hotellink_rate_plans['data'][$key]);
                    }
                }
            }*/

            $data['hotellink_room_types_rate_plans'] = array();

            if (isset($hotellink_rate_plans['data']['Rooms']) && count($hotellink_rate_plans['data']['Rooms']) > 0) {
                foreach ($hotellink_rate_plans['data']['Rooms'] as $key=>$room_type) {
                    $room_id = $room_type['RoomId'];
                    $room_name = $room_type['Name'];
                    $data['hotellink_room_types_rate_plans'][$key]['room_type_id'] = $room_id;
                    $data['hotellink_room_types_rate_plans'][$key]['room_type_name'] = $room_name;

                    if (isset($room_type['RatePlans']) && count($room_type['RatePlans']) > 0) {
                        foreach ($room_type['RatePlans'] as $key1=>$rate_plan) {
                            $plan_id = $rate_plan['RatePlanId'];
                            $plan_name = $rate_plan['Name'];
                            $data['hotellink_room_types_rate_plans'][$key]['rate_plans'][$key1]['rate_plan_id'] = $plan_id;
                            $data['hotellink_room_types_rate_plans'][$key]['rate_plans'][$key1]['rate_plan_name'] = $plan_name;
                        }
                    }
                }
            }

            $data['minical_room_types'] = $this->Room_type_model->get_room_types($this->company_id);
            $data['minical_rate_plans'] = $this->Rate_plan_model->get_rate_plans($this->company_id);

            foreach ($data['hotellink_room_types'] as $key => $value) {
                foreach ($data['hotellink_room_types_rate_plans'] as $key1 => $value1) {
                    $data['hotellink_room_types_rate_plans'][$key1]['minical_room_type_id'] = $data['hotellink_room_types'][$key1]['minical_room_type_id'];
                }
            }

            foreach ($data['hotellink_rate_plans'] as $key => $value) {
                foreach ($data['hotellink_room_types_rate_plans'] as $key1 => $value1) {
                    if($value['ota_room_type_id'] == $value1['room_type_id']){
                        foreach ($value1['rate_plans'] as $key2 => $value2) {
                            if($value['ota_rate_plan_id'] == $value2['rate_plan_id']){
                                $data['hotellink_room_types_rate_plans'][$key1]['rate_plans'][$key2]['minical_rate_plan_id'] = $value['minical_rate_plan_id'];
                            }
                        }
                    }
                }
            }
        }

        $data['is_mapping'] = $is_mapping;
        $data['hotellink_id'] = $hotellink_id;

        $this->template->load('bootstrapped_template', null , $data['main_content'], $data);
    }

    function hotellink_refresh_token(){
        $hotellink_data = $this->Hotellink_model->get_hotellink_data($this->company_id, $this->ota_key);

        if($hotellink_data){
            if(isset($hotellink_data['created_date']) && $hotellink_data['created_date']){

                $timestamp = strtotime($hotellink_data['created_date']);

                // getting current date
                $cDate = strtotime(date('Y-m-d H:i:s'));

                $meta_data = json_decode($hotellink_data['meta_data']);

                $expire_in = (int)$meta_data->data->expires_in;
                // Getting the value of old date + 24 hours
                $oldDate = $timestamp + $expire_in; // 86400 seconds in 24 hrs

                if($oldDate < $cDate)
                {
                    $username = getenv("HOTELLINK_USERNAME");
                    $password = getenv("HOTELLINK_PASSWORD");
                    $get_refresh_token_data = $this->hotellinkintegration->refresh_token($username, $password);
                    $response = json_decode($get_refresh_token_data);

                    if(isset($response->data) && $response->data){

                        $data = array(
                            'meta_data' => $get_refresh_token_data,
                            'created_date' => date('Y-m-d H:i:s'),
                            'email' => $hotellink_data['email'],
                            'company_id' => $this->company_id,
                        );

                        $this->Hotellink_model->update_token($data);
                        return true;
                    }
                } else {
                    return false;
                }
            }
        }
    }

    function get_room_types()
    {
        $property_id = $this->input->post('property_id');
        $hotellink_id = $this->input->post('hotellink_id');

        $get_token_data = $this->Hotellink_model->get_token($hotellink_id);

        if($get_token_data){

            if($this->hotellink_refresh_token()){
                $get_token_data = $this->Hotellink_model->get_token($hotellink_id);
            }

            $token_data = json_decode($get_token_data['meta_data']);
            $token = $token_data->data->access_token;
            $channel_key = $get_token_data['email'];

            $rate_plans_data = $this->hotellinkintegration->get_rate_plans($property_id, $channel_key, $token);
            $hotellink_rate_plans = json_decode($rate_plans_data, true);

            /*if (isset($hotellink_rate_plans['data']) && count($hotellink_rate_plans['data']) > 0) {
                foreach ($hotellink_rate_plans['data'] as $key => $value) {
                    if(
                        isset($value['relationships']) &&
                        isset($value['relationships']['parent_rate_plan'])
                    ) {
                        unset($hotellink_rate_plans['data'][$key]);
                    }
                }
            }*/

            $data['hotellink_room_types_rate_plans'] = array();

            if (isset($hotellink_rate_plans['data']['Rooms']) && count($hotellink_rate_plans['data']['Rooms']) > 0) {
                foreach ($hotellink_rate_plans['data']['Rooms'] as $key=>$room_type) {
                    $room_id = $room_type['RoomId'];
                    $room_name = $room_type['Name'];

                    $data['hotellink_room_types_rate_plans'][$key]['room_type_id'] = $room_id;
                    $data['hotellink_room_types_rate_plans'][$key]['room_type_name'] = $room_name;

                    if (isset($room_type['RatePlans']) && count($room_type['RatePlans']) > 0) {
                        foreach ($room_type['RatePlans'] as $key1=>$rate_plan) {
                            $plan_id = $rate_plan['RatePlanId'];
                            $plan_name = $rate_plan['Name'];
                            $data['hotellink_room_types_rate_plans'][$key]['rate_plans'][$key1]['rate_plan_id'] = $plan_id;
                            $data['hotellink_room_types_rate_plans'][$key]['rate_plans'][$key1]['rate_plan_name'] = $plan_name;
                        }
                    }
                }
            }

            $data['minical_room_types'] = $this->Room_type_model->get_room_types($this->company_id);
            $data['minical_rate_plans'] = $this->Rate_plan_model->get_rate_plans($this->company_id);

            //$this->save_logs($property_id, 3);

            $this->load->view('../extensions/'.$this->module_name.'/views/room_rate_mapping_view', $data);
        }

    }

    function save_hotellink_mapping_AJAX(){

        $hotellink_id = $this->input->post('hotellink_id');
        $property_id = $this->input->post('property_id');
        $mapping_data = $this->input->post('mapping_data');
        $mapping_data_rp = $this->input->post('mapping_data_rp');

        $hotellink_x_company = $this->Hotellink_model->get_hotellink_x_company_by_channel($this->ota_key, $this->company_id);

        if($hotellink_x_company){
            $hotellink_x_company_id = $hotellink_x_company['ota_x_company_id'];
        } else {
            $hotellink_company_data = array(
                'company_id' => $this->company_id,
                'ota_manager_id' => $hotellink_id,
                'ota_property_id' => $property_id,
                'is_active' => 1,
            );

            $hotellink_x_company_id = $this->Hotellink_model->save_hotellink_company($hotellink_company_data);
        }

        foreach ($mapping_data as $key => $value) {
            foreach ($mapping_data_rp as $key1 => $val) {

                $rtrp_id = $val['hotellink_rate_plan_id'];
                $rt_rp_id = explode('_', $rtrp_id);
                if($value['hotellink_room_type_id'] == $rt_rp_id[0]){
                    $mapping_data[$key]['rate_plan'][$key1]['hotellink_rate_plan_id'] = $rt_rp_id[1];
                    $mapping_data[$key]['rate_plan'][$key1]['minical_rate_plan_id'] = $val['minical_rate_plan_id'];
                }
            }
        }

        foreach ($mapping_data as $mapping) {
            $hotellink_room_type_id = isset($mapping['hotellink_room_type_id']) ? $mapping['hotellink_room_type_id'] : null;

            $minical_room_type_id = isset($mapping['minical_room_type_id']) ? $mapping['minical_room_type_id'] : null;

            $this->Hotellink_model->create_or_update_room_type($hotellink_x_company_id, $hotellink_room_type_id, $minical_room_type_id, $this->company_id);

            if(isset($mapping['rate_plan']) && count($mapping['rate_plan']) > 0){
                foreach ($mapping['rate_plan'] as $key => $value) {
                    $minical_rate_plan_id = isset($value['minical_rate_plan_id']) ? $value['minical_rate_plan_id'] : null;
                    $hotellink_rate_plan_id = isset($value['hotellink_rate_plan_id']) ? $value['hotellink_rate_plan_id'] : null;

                    $this->Hotellink_model->create_or_update_rate_plan($hotellink_x_company_id, $hotellink_room_type_id, $minical_rate_plan_id, $hotellink_rate_plan_id, $this->company_id);
                }
            }
        }

        $this->update_full_refresh();

        echo json_encode(array('success' => true));
    }

    function update_full_refresh(){
        // loop 4 times to cover 360 days
        for ($i = 0; $i < 4; $i++)
        {
            $start_date = date("Y-m-d", time()+86400);
            //$start_date = date("Y-m-d");
            $end_date = Date("Y-m-d", strtotime("+90 days", strtotime($start_date)));

            $this->hotellink_update_availability($start_date, $end_date);

            $this->hotellink_update_restrictions($start_date, $end_date);
            $current_date = $end_date;
        }
    }

    function hotellink_update_availability($start_date = null, $end_date = null){

        if(!$start_date && !$end_date){
            $start_date = $this->input->post('check_in_date');
            $end_date = $this->input->post('check_out_date');
            $booking_id = $this->input->post('booking_id');
            $room_type_id = $this->input->post('room_type_id');
        } else {
            $room_type_id = false;
        }
        $data = array(
            'start_date' => $start_date,
            'end_date' => $end_date,
            'room_type_id' => $room_type_id,
            'company_id' => $this->company_id,
            'update_from' => 'extension'
        );
        do_action('hotellink_update_availability', $data);
    }

    function hotellink_update_restrictions($start_date = null, $end_date = null){

        $currency = $this->Currency_model->get_default_currency($this->company_id);
        $currency_code = isset($currency['currency_code'])? $currency['currency_code']: 'USD';
        if(!$start_date && !$end_date){
            $start_date = $this->input->post('date_start');
            $end_date = $this->input->post('date_end');
            $rate_plan_id = $this->input->post('rate_plan_id');
        } else {
            $rate_plan_id = false;
        }

        $rates = array(
            'adult_1_rate' => $this->input->post('adult_1_rate'),
            'adult_2_rate' => $this->input->post('adult_2_rate'),
            'adult_3_rate' => $this->input->post('adult_3_rate'),
            'adult_4_rate' => $this->input->post('adult_4_rate'),
            'additional_adult_rate' => $this->input->post('additional_adult_rate'),
            'closed_to_arrival' => $this->input->post('closed_to_arrival'),
            'closed_to_departure' => $this->input->post('closed_to_departure'),
            'minimum_length_of_stay' => $this->input->post('minimum_length_of_stay'),
            'maximum_length_of_stay' => $this->input->post('maximum_length_of_stay'),
            'can_be_sold_online' => $this->input->post('can_be_sold_online')
        );

        $get_token_data = $this->Hotellink_model->get_token(null, $this->company_id, $this->ota_key);
        if($this->hotellink_refresh_token()){
            $get_token_data = $this->Hotellink_model->get_token(null, $this->company_id, $this->ota_key);
        }
        $token_data = json_decode($get_token_data['meta_data']);
        $token = $token_data->data->access_token;

        $ota_x_company = $this->Hotellink_model->get_hotellink_x_company_by_channel($this->ota_key, $this->company_id);
        $property_id = $ota_x_company['ota_property_id'] ;
        $property_key = $get_token_data['email'] ;

        if($rate_plan_id) {
            $rate_plan_data = $this->Hotellink_model->get_hotellink_rate_plans_by_id($rate_plan_id);

            $rate_array['values'] = $rate_data = array();
            if($property_id){

                $from = date('Y-m-d', strtotime($start_date));
                $to = date('Y-m-d', strtotime($end_date));
                $room_id = $plan_id = null;
                foreach ($rate_plan_data as $key => $value) {
                    if($value['minical_rate_plan_id'] == $rate_plan_id){
                        $room_id = $value['ota_room_type_id'];
                        $plan_id = $value['ota_rate_plan_id'];
                    }
                }

                $room_type_detail = $this->Hotellink_model->get_room_type_by_rate_plan_id($rate_plan_id);
                $room_maximum_occupancy = $room_type_detail['max_occupancy'];
                $rate = $rates['adult_1_rate'];
                $min_night = $rates['minimum_length_of_stay']>0? $rates['minimum_length_of_stay']: null;
                $max_night = $rates['maximum_length_of_stay']>0? $rates['maximum_length_of_stay']: null;
                $cta = $rates['closed_to_arrival'] == 1 ? "1" : "0";
                $ctd = $rates['closed_to_departure'] == 1 ? "1" : "0";
                $stop_sell = $rates['can_be_sold_online'] == 1 ? "0": "1";
                $extra_adult_rate = null;
                $extra_child_rate = isset($rates['additional_child_rate']) && $rates['additional_child_rate'] > 0 ? $rates['additional_child_rate']: null;

                $extra_rates = [];
                for($i = 1; $i < $room_maximum_occupancy; $i++) {
                    if($i>=4) {
                        $extra_adult_rate = isset($rates['additional_adult_rate']) && $rates['additional_adult_rate'] > 0 ? $rates['additional_adult_rate']: null;
                    } else {
                        $extra = $rates['adult_'.($i+1).'_rate'] - $rates['adult_'.$i.'_rate'];
                        $extra_rates["$i"] = $extra>0? $extra: 0;
                    }
                }

                $rate_array = [];
                $rate_data = $this->build_hotellink_rate_data($room_id, $plan_id, $rate, $extra_rates, $currency_code, $extra_adult_rate, $extra_child_rate, $min_night,
                    $max_night, $cta, $ctd, $stop_sell, $from, $to, $property_id, $property_key);
                $rate_array[] = $rate_data;

            }
        }
        else {
            $rate_plan_data = $this->Hotellink_model->get_hotellink_rate_plans_by_id();

            $minical_rates = array();
            foreach($rate_plan_data as $key => $rate_plan){
                if(isset($rate_plan['minical_rate_plan_id']) && $rate_plan['minical_rate_plan_id']){

                    $rate_plan_id = $rate_plan['minical_rate_plan_id'];
                    $minical_rates[] = $this->Rates_model->get_rates(
                        $rate_plan_id,
                        -1,
                        $start_date,
                        $end_date);
                }
            }


            $rate_array['values'] = $rate_data = array();

            $rate_plan_mapping = [];

            foreach ($rate_plan_data as $rate_plan_item) {
                if($rate_plan_item['minical_rate_plan_id']>0) {
                    $rate_plan_mapping[$rate_plan_item['minical_rate_plan_id']] = $rate_plan_item['ota_room_type_id'] .'|'. $rate_plan_item['ota_rate_plan_id'];
                }
            }


            if($property_id){

                $room_type_detail = $this->Hotellink_model->get_room_type_by_rate_plan_id($rate_plan_id);
                $room_maximum_occupancy = $room_type_detail['max_occupancy'];
                $rate_array = [];
                foreach ($minical_rates as $key => $minical_rate) {
                    foreach($minical_rate as $key1 => $minical_rate_item){

                        $from = $minical_rate_item['date_start'];
                        $to = $minical_rate_item['date'];
                        list($room_id, $plan_id) = explode('|', $rate_plan_mapping[$minical_rate_item['rate_plan_id']]);
                        $rate = isset($minical_rate_item["adult_1_rate"]) ? $minical_rate_item["adult_1_rate"]: 0;
                        $min_night = $minical_rate_item['minimum_length_of_stay']>0? $minical_rate_item['minimum_length_of_stay']: null;
                        $max_night = $minical_rate_item['maximum_length_of_stay']>0? $minical_rate_item['maximum_length_of_stay']: null;
                        $cta = $minical_rate_item['closed_to_arrival'] == 1 ? "1" : "0";
                        $ctd = $minical_rate_item['closed_to_departure'] == 1 ? "1" : "0";
                        $stop_sell = $rates['can_be_sold_online'] == 1 ? "0" : "1";
                        $extra_adult_rate = null;
                        $extra_child_rate = isset($minical_rate_item['additional_child_rate'])? $minical_rate_item['additional_child_rate']: 0;
                        $currency_code = $minical_rate_item['currency_code'];

                        $extra_rates = [];
                        for($i = 1; $i < $room_maximum_occupancy; $i++) {
                            if($i>=4) {
                                $extra_adult_rate = isset($minical_rate_item['additional_adult_rate'])? $minical_rate_item['additional_adult_rate']: null;
                            } else {
                                $extra = $rates['adult_'.($i+1).'_rate'] - $rates['adult_'.$i.'_rate'];
                                $extra_rates["$i"] = $extra>0? $extra: 0;
                            }
                        }

                        $rate_data = $this->build_hotellink_rate_data($room_id, $plan_id, $rate, $extra_rates, $currency_code, $extra_adult_rate, $extra_child_rate, $min_night,
                            $max_night, $cta, $ctd, $stop_sell, $from, $to, $property_id, $property_key);
                        $rate_array[] = $rate_data;
                    }
                }
            }
        }
        //$rate_array = $this->rate_example_request();

        prx($rate_array, 1);
        foreach ($rate_array as $data) {
            $response = $this->hotellinkintegration->update_inventories($data, $token);
            $this->save_logs($property_id, 1, 1, json_encode($data), $response);
            $response = json_decode($response, true);
            echo 'rates resp = ';prx($response, 1);
        }

        // if(!empty($response['data']) && $response['meta']['message'] == 'Success')
        // 	echo json_encode(array('success' => true));
    }

    function build_hotellink_rate_data($room_id, $plan_id, $rate, $extra_rates, $currency_code, $extra_adult_rate, $extra_child_rate, $min_night,
                                       $max_night, $cta, $ctd, $stop_sell, $from, $to, $property_id, $property_key) {
        $rate_data = [];
        $inventories = [];
        //For $inventories
        $inventory = [];
        $inventory['RoomId'] = $room_id;

        $rate_packages = [];
        //For $rate_packages
        $rate_package = [];
        $rate_package['RatePlanId'] = $plan_id;
        if($rate>0) {
            $rate_package['Rate'] = [
                'Amount' => [
                    'Type' => 'FIXED_AMOUNT',
                    'Value' => $rate,
                    'Currency' => $currency_code
                ],
                'Action' => 'Set'
            ];
        }

        $rate_package['ExtraAdultRates'] = $extra_rates;

        if($extra_adult_rate) {
            $rate_package['ExtraAdultRate'] = [
                'Amount' => [
                    'Type' => 'FIXED_AMOUNT',
                    'Value' => $extra_adult_rate,
                    'Currency' => $currency_code
                ],
                'Action' => 'Set'
            ];
        }

        if($extra_child_rate) {
            $rate_package['ExtraChildRate'] = [
                'Amount' => [
                    'Type' => 'FIXED_AMOUNT',
                    'Value' => $extra_child_rate,
                    'Currency' => $currency_code
                ],
                'Action' => 'Set'
            ];
        }
        if($min_night) {
            $rate_package['MinNights'] = $min_night;
        }
        if($max_night) {
            $rate_package['MaxNights'] = $max_night;
        }

        $rate_package['CloseToArrival'] = $cta;
        $rate_package['CloseToDeparture'] = $ctd;
        $rate_package['StopSell'] = $stop_sell;

        $rate_package['DateRange'] = [
            'From' => $from,
            'To' => $to
        ];
        $rate_packages[] = $rate_package;
        //End For $rate_packages

        $inventory['RatePackages'] = $rate_packages;
        //End For $inventories
        $inventories[] = $inventory;

        $rate_data['Inventories'] = $inventories;
        $rate_data['Credential'] = [
            "HotelId" => $property_id,
            "HotelAuthenticationChannelKey" => $property_key
        ];
        $rate_data['Lang'] = 'en';

        return $rate_data;
    }

    function save_logs($ota_property_id = null, $request_type = null, $response_type = null, $xml_in = null, $xml_out = null) {
        $data = array(
            'ota_property_id' => $ota_property_id ? $ota_property_id : null,
            'request_type' => ($request_type || $request_type == 0) ? $request_type : null,
            'response_type' => ($response_type || $response_type == 0) ? $response_type : null,
            'xml_in' => $xml_in ? $xml_in : null,
            'xml_out' => $xml_out ? $xml_out : null,
        );
        $this->Hotellink_model->save_logs($data);
    }
}