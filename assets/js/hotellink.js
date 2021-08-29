$(document).ready(function(){
    $('.manage-channel').on("click", function() {
        var hotellinkId = $(this).data('hotellink_id');
        window.location.href = getBaseURL() + 'hotellink_properties/' + hotellinkId
    });

    $('body').on('click','.login-hotellink',function(){
        var email = $('input[name="email"]').val();
        var password = $('input[name="password"]').val();
        if(email == ''){
            alert('Please enter channel manager username');
        } else if(password == ''){
            alert('Please enter channel manager password');
        } else {
            $.ajax({
                type    : "POST",
                dataType: 'json',
                url     : getBaseURL() + 'signin_hotellink',
                data: {email : email, password : password},
                success: function( data ) {
                    if(data.success){
                        window.location.href = getBaseURL() + 'hotellink_properties/' + data.hotellink_id
                    } else {
                        alert(data.msg);
                    }
                }
            });
        }
    });

    $(".edit-channel-configuration").on("click", function() {
        $('#manage-hotellink').addClass('hidden');
        $('#configure-hotellink').removeClass('hidden');
    });

    $(".deconfigure-channel").on("click", function() {
        var hotellinkId = $(this).data('hotellink_id');
        $.ajax({
            type: "POST",
            url: getBaseURL() + "deconfigure_hotellink_AJAX",
            dataType: 'json',
            data: {
                hotellink_id: hotellinkId
            },
            success: function (response) {
                if(response.success){
                    alert(l('Channel Configuration Updated!'));
                    location.reload();
                }
            }
        });
    });

    $('body').on('click', '.full-sync-hotellink',function() {
        var button = $(this);
        button.prop('disabled', true);
        $.post(getBaseURL() + 'hotellink_update_full_refresh',
            {},
            function(res) {
                button.prop('disabled', false);
            }
        ).always(function(res) {
            alert(l('Data refreshed successfully!'));
        });
    });

    $('body').on('click','.fetch-room',function(){
        var property_id = $('#property-id').val();
        var property_key = $('#property-key').val();


        if(property_id == ''){
            alert('Please input property id');
        } else {
            var pathArray = window.location.pathname.split( '/' );

            $.ajax({
                type    : "POST",
                dataType: 'html',
                url     : getBaseURL() + 'hotellink_get_room_types',
                data: {property_id : property_id, property_key: property_key, hotellink_id : pathArray[pathArray.length-1]},
                success: function( data ) {
                    $('.save_hotellink_mapping').html(data);
                }
            });
        }
    });

    $('body').on('change','select[name="minical_room_type"]',function(){
        $('div.hotellink_room_types').each(function(){
            var room_type_id = $(this).find('select[name="minical_room_type"]').val();
            $(this).find('select[name="minical_rate_plan"]').find('option').hide();
            $(this).find('select[name="minical_rate_plan"]').find('option:first-child').show();
            $(this).find('select[name="minical_rate_plan"]').find('option[data-room_type_id="'+room_type_id+'"]').show();
        });
    });

    $('body').on('click', '.save_hotellink_mapping_button', function(){
        $(this).val('Loading..').attr('disabled', true);
        var mappingData = [];
        var mappingDataRP = [];
        var propertyId = $('#property-id').val();
        var propertyKey = $('#property-key').val();

        $('.hotellink_room_types').each(function(){
            var chRoomTypeId = $(this).data('hotellink_room_id');
            mappingData.push({
                "hotellink_room_type_id": $(this).data('hotellink_room_id'),
                "minical_room_type_id": $(this).find('select[name="minical_room_type"]').val()
            });

        });

        $('.rate-plan').each(function(){
            var minRPId = $(this).find('select[name="minical_rate_plan"]').val();
            mappingDataRP.push({
                "hotellink_rate_plan_id": $(this).find('.hotellink-rate-plan').data('hotellink_rate_id'),
                "minical_rate_plan_id": $(this).find('select[name="minical_rate_plan"]').val()
            });
        });

        var pathArray = window.location.pathname.split( '/' );

        $.ajax({
            url    : getBaseURL() + "save_hotellink_mapping_AJAX",
            type   : "POST",
            dataType: "json",
            data   : {
                hotellink_id : pathArray[pathArray.length-1],
                property_id : propertyId,
                property_key : propertyKey,
                mapping_data : mappingData,
                mapping_data_rp : mappingDataRP
            },
            success: function (data) {
                if(data.success){
                    $('.save_hotellink_mapping_button').val('Save All').attr('disabled', false);
                    // updateRates("","","");
                    alert(l('Channel Configuration Updated!'));
                    location.reload();
                }
            },
            error: function (data, error) {
                console.log(data);
                $('.save_hotellink_mapping_button').val('Save All').attr('disabled', false);
                location.reload();
            }
        });
        return false;
    });
});


function showRatePlansByRoomType (){
    $('div.hotellink_room_types').each(function(){
        var room_type_id = $(this).find('select[name="minical_room_type"]').val();
        $(this).find('select[name="minical_rate_plan"]').find('option').hide();
        $(this).find('select[name="minical_rate_plan"]').find('option:first-child').show();
        $(this).find('select[name="minical_rate_plan"]').find('option[data-room_type_id="'+room_type_id+'"]').show();
    });
}

showRatePlansByRoomType();