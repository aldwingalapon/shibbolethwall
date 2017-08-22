
<li class="gf_live_validation_type_settings_is_email field_setting gf_lv_is_format_lv" style="display: none;">

    <label for="gf_live_validation_format_email_0" class="" >
        <?php _e("Enter pattern for Email", "gravityforms"); ?>
        <?php gform_tooltip("gf_live_validation_format_email_0") ?>
    </label>

    <textarea class="gf_lv_is_pattern_field" data-ftype="text"  placeholder="Your Regex Here..." id="gf_live_validation_format_email_0" rows="5" cols="40" onkeyup="SetFieldProperty('gf_live_validation_format_email_0', createbaseHash(this));" ></textarea>
    <span class="lv_open_support_box_outer"> <b>Need Help with RegEx Patterns? </b><a href="javascript:void(0);" class="lv_open_support_box"> Click Here</a></span> 

    <span class="lv-toggle-off lv_admin_input_error">OOPS! It doesn't seems like a valid RegEx pattern. </span>


</li>


<li class="gf_live_validation_type_settings_is_email_error field_setting gf_lv_is_format_lv" style="display: none;">

    <label for="gf_live_validation_error_email_0" class="" >
        <?php _e("Error message", "gravityforms"); ?>
        <?php gform_tooltip("gf_live_validation_error_msg_email_0"); ?>
    </label>

    <input type="text" class="gf_live_validation_error_msg_fields" id="gf_live_validation_error_msg_email_0" onkeyup="SetFieldProperty('gf_live_validation_error_msg_email_0', moveErrorTohidden(this));" />


</li>



<!--<li class="gf_live_validation_type_settings_is_email field_setting gf_lv_is_pre_lv">
    <input type="checkbox" id="gf_live_validation_email_is_block_tld" onclick="SetFieldProperty('gf_live_validation_email_is_block_tld', this.checked);" /> 
    <label for="gf_live_validation_email_is_block_tld" class="inline">
        <?php _e("Block Top Level Domains", "lv_validation"); ?>

    </label>
</li>-->

<li class="gf_live_validation_type_settings_is_email field_setting gf_lv_is_pre_lv">
    <label for="gf_live_validation_format_email_block_tld" class="" >
        <?php _e("Enter TLDs to you want to block", "gravityforms"); ?>

    </label>

    <textarea class="" data-ftype="text"  placeholder="Enter Domains here" id="gf_live_validation_format_email_block_tld" rows="5" cols="40" onkeyup="SetFieldProperty('gf_live_validation_format_email_block_tld', LVplaceValue(this,'',this.value));" ></textarea>

</li>


<!--<li class="gf_live_validation_type_settings_is_email field_setting gf_lv_is_pre_lv">
    <input type="checkbox" id="gf_live_validation_email_is_block_domains" onclick="SetFieldProperty('gf_live_validation_email_is_block_domains', this.checked);" /> 
    <label for="gf_live_validation_email_is_block_domains" class="inline">
        <?php _e("Block Specific Domains", "lv_validation"); ?>

    </label>
</li>-->

<li class="gf_live_validation_type_settings_is_email field_setting gf_lv_is_pre_lv">
    <label for="gf_live_validation_email_block_domains" class="" >
        <?php _e("Enter Domains to block", "gravityforms"); ?>

    </label>

    <textarea class="" data-ftype="text"  placeholder="Enter Domains here" id="gf_live_validation_email_block_domains" rows="5" cols="40" onkeyup="SetFieldProperty('gf_live_validation_email_block_domains', LVplaceValue(this,'',this.value));" ></textarea>

</li>
