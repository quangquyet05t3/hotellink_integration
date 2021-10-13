<div class="settings integrations">
	<div class="page-header">
		<h2>
			<?php echo l('Hotel Link Mapping with Minical', true); ?>
		</h2>
	</div>
    <div class="col-md-12" >

        <?php if (isset($hotellink_room_types_rate_plans) && count($hotellink_room_types_rate_plans) > 0): ?>

            <div class="col-md-9">
                <div class="position-relative form-group">
                    <label for="hotellink-id" class=""><?php echo l('Hotel Link Property Id: '); ?></label>
                    <?php echo isset($hotellink_room_types[0]['ota_property_id']) ? $hotellink_room_types[0]['ota_property_id'] : ''; ?>
                </div>
            </div>

        <?php else: ?>
            <div class="col-md-9">
                <div class="position-relative form-group">
                    <label for="hotellink-id" class=""><?php echo l('Hotel Link Property Id'); ?></label>
                    <input type="text" name="property" id="property-id" class="form-control" value="<?php echo isset($hotellink_room_types[0]['ota_property_id']) ? $hotellink_room_types[0]['ota_property_id'] : ''; ?>">
                </div>
                <div class="position-relative form-group">
                    <button type="button" class="btn btn-success fetch-room" ><?=l("Fetch room");?></button>
                </div>
            </div>

        <?php endif; ?>

    <?php if (isset($hotellink_room_types_rate_plans) && count($hotellink_room_types_rate_plans) > 0): ?>
        <button type="button" class='btn btn-success full-sync-hotellink pull-right' hotellink_id="<?=$hotellink_id;?>"><?php echo l("Full Sync");?></button>
    <?php endif; ?>

    <form class="save_hotellink_mapping" >
        <div class="col-md-12 col-lg-12 col-xs-12 col-sm-12" style="margin-top: 20px;">
    <?php if (isset($hotellink_room_types_rate_plans) && count($hotellink_room_types_rate_plans) > 0):
        foreach ($hotellink_room_types_rate_plans as $value) :  ?>
    
    <div class="panel panel-default room-type-panel hotellink_room_types <?php echo $is_mapping ? '' : 'hidden'; ?>" data-hotellink_room_id="<?php echo $value['room_type_id']; ?>">
        <div class="panel-heading">
            <span class="hotellink-room-type" >
                <?php echo $value['room_type_name']; ?> (<?php echo $value['room_type_id']; ?>)
            </span>
            <span class="minical-room-type">
                <?php
                    if (isset($minical_room_types)):
                ?>
                        <select name="minical_room_type" class="hotellink_manager">
                            <option value=""><?php echo l("Not selected", true); ?></option>
                            <?php
                                foreach($minical_room_types as $room_type)
                                { ?>
                                    <option value="<?php echo $room_type['id']; ?>" <?php echo isset($value['minical_room_type_id']) && $value['minical_room_type_id'] && $value['minical_room_type_id'] == $room_type['id'] ? 'SELECTED' : ''; ?>>
                                        <?php echo $room_type['name']; ?>
                                    </option>
                            <?php }
                            ?>
                        </select>
                <?php
                    endif;
                ?>
            </span>
        </div>
        <div class="panel-body">
            <?php if(isset($value['rate_plans']) && count($value['rate_plans']) > 0):
                foreach($value['rate_plans'] as $val): ?>
            <div class="rate-plan">
                <span class="hotellink-rate-plan" data-hotellink_room_type_id="<?php echo $value['room_type_id']; ?>" data-hotellink_rate_id="<?php echo $value['room_type_id'].'_'.$val['rate_plan_id']; ?>">
                    <?php echo $val['rate_plan_name']; ?> (<?php echo $val['rate_plan_id']; ?>)
                </span>
                <span class="minical-rate-plan">
                    <?php
                        if (isset($minical_rate_plans)):
                    ?>
                            <select name="minical_rate_plan" class="hotellink_manager" data-ch_id="<?php echo $val['rate_plan_id']; ?>" >
                                <option value=""><?php echo l("Not selected", true); ?></option>
                                <?php
                                    foreach($minical_rate_plans as $rate_plan)
                                    {
                                        $room_type_id = $rate_plan['room_type_id']; 
                                    ?>
                                        <option style="display: none;" data-room_type_id="<?php echo $room_type_id; ?>" value="<?php echo $rate_plan['rate_plan_id']; ?>" <?php echo isset($val['minical_rate_plan_id']) && $val['minical_rate_plan_id'] == $rate_plan['rate_plan_id'] ? 'SELECTED' : ''; ?>>
                                            <?php echo $rate_plan['rate_plan_name']; ?>
                                        </option>
                                <?php    }
                                ?>
                            </select>
                    <?php
                        endif;
                    ?>
                </span>
            </div> <!-- /Rate Plans -->
        <?php endforeach; endif; ?>
        </div>
    </div>
<?php endforeach;  ?>
</div>
<div class="col-md-2">
    <input type="button" class="btn btn-success save_hotellink_mapping_button" value="<?php echo l('Save All', true); ?>"/>
</div>
<?php endif; ?>



        </form>
    </div>
</div>
