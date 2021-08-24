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
                    <label for="email" class="col-sm-3 control-label">
                        <span alt="email" title="email"><?=l("Channel Manager Username");?></span>
                    </label>
                    <div class="col-sm-9">
                        <input type="text" name="email" class="form-control" value="<?php echo isset($hotellink_data['email']) ? $hotellink_data['email'] : ''; ?>">
                    </div>
                </div>
                <div class="form-group rate-group text-center">
                    <label for="password" class="col-sm-3 control-label">
                        <span alt="password" title="password"><?=l("Channel Manager Password");?></span>
                    </label>
                    <div class="col-sm-9">
                        <input type="password" name="password" class="form-control" value="<?php echo isset($hotellink_data['password']) ? $hotellink_data['password'] : ''; ?>">
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
                    <label for="email" class="col-sm-3 control-label">
                        <span alt="email" title="email"><?=l("Channel Manager Username");?></span>
                    </label>
                    <div class="col-sm-9">
                        <input type="text" name="email" class="form-control" value="<?php echo isset($hotellink_data['email']) ? $hotellink_data['email'] : ''; ?>">
                    </div>
                </div>
                <div class="form-group rate-group text-center">
                    <label for="password" class="col-sm-3 control-label">
                        <span alt="password" title="password"><?=l("Channel Manager Password");?></span>
                    </label>
                    <div class="col-sm-9">
                        <input type="password" name="password" class="form-control" value="<?php echo isset($hotellink_data['password']) ? $hotellink_data['password'] : ''; ?>">
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
