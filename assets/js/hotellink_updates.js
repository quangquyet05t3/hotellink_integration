// Add an event listener for rate create
document.addEventListener("rate_created", function(e) {

    console.log('hotellink update');
    if(e && e.detail.rate_data && e.detail.rate_data.rate_plan_id)
    {
        var rates = e.detail.rate_data;
        console.log(rates);
        $.ajax({
            type    : "POST",
            dataType: 'json',
            url     : getBaseURL() + 'hotellink_update_restrictions',
            data: {
                    rate_plan_id : rates.rate_plan_id,
                    date_start : rates.date_start,
                    date_end : rates.date_end,
                    adult_1_rate : rates.adult_1_rate,
                    adult_2_rate : rates.adult_2_rate,
                    adult_3_rate : rates.adult_3_rate,
                    adult_4_rate : rates.adult_4_rate,
                    additional_adult_rate : rates.additional_adult_rate,
                    closed_to_arrival : rates.closed_to_arrival,
                    closed_to_departure : rates.closed_to_departure,
                    minimum_length_of_stay : rates.minimum_length_of_stay,
                    maximum_length_of_stay : rates.maximum_length_of_stay,
                    can_be_sold_online : rates.can_be_sold_online
                },
            success: function( resp ) {
                console.log(resp);
            }
        });
    }
});