<div class="settings integrations">
	<div class="page-header">
		<h2>
			<?php echo l('Hotel Link'); ?>
		</h2>
	</div>

    <div class="panel panel-default ">
        <?php if($hotellink_data): ?>
        <div class="panel-body form-horizontal ">
            <div id="configure-hotellink" class="hidden">
                <div class="form-group rate-group text-center">
                    <label for="channel_key" class="col-sm-3 control-label">
                        <span alt="channel_key" title="channel_key"><?=l("Hotel Authentication Channel Key");?></span>
                    </label>
                    <div class="col-sm-6">
                        <input type="text" name="channel_key" class="form-control" value="<?php echo isset($hotellink_data['email']) ? $hotellink_data['email'] : ''; ?>">
                    </div>
                </div>
                <div class="text-center">
                    <button type="button" class="btn btn-success login-hotellink" ><?=l("Sign in");?></button>
                </div>
            </div>
            <div id="manage-hotellink" class="">
                <div class="text-center">
                    <button class="btn btn-success manage-channel" data-hotellink_id="<?php echo isset($hotellink_data['id']) ? $hotellink_data['id'] : ''; ?>">Map Room Types &amp; Rates</button>
                    <button class="btn btn-warning edit-channel-configuration">Account Setup</button>
                    <button class="btn btn-danger deconfigure-channel" data-hotellink_id="<?php echo isset($hotellink_data['id']) ? $hotellink_data['id'] : ''; ?>">De-Configure</button>
                </div>
            </div>
        </div>
    <?php else: ?>
        <div class="panel-body form-horizontal ">
            <div id="configure-hotellink" class="">
                <div class="form-group rate-group text-center">
                    <label for="channel_key" class="col-sm-3 control-label">
                        <span alt="channel_key" title="channel_key"><?=l("Hotel Authentication Channel Key");?></span>
                    </label>
                    <div class="col-sm-6">
                        <input type="text" name="channel_key" class="form-control" value="<?php echo isset($hotellink_data['email']) ? $hotellink_data['email'] : ''; ?>">
                    </div>
                </div>
                <div class="text-center">
                    <button type="button" class="btn btn-success login-hotellink" ><?=l("Sign in");?></button>
                </div>
            </div>
        </div>
    <?php endif; ?>
    </div>
</div>
