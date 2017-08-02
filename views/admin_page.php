<?php
/**
 * MailChimp admin setting view
 *
 * @author CreativeMinds (http://www.cminds.com)
 * @version 1.0
 * @copyright Copyright (c) 2012, CreativeMinds 
 * @package MultiMailChimp/View
 */
if (!current_user_can('manage_options')) {
    wp_die(__("You don't have enough privileges to do this", 'sjfilecache'));
}
?>
<script>
jQuery(document).ready(function($) {
    $('#mmc_fetchLists').click(function(e) {
        e.preventDefault();
        var apiKey = $(this).prev('input[type=text]').val();
        mmc_fetchLists({
            'apiKey': apiKey,
            containerId: '#mmc_listsContainer',
            ajaxLoaderId: '#mmc_ajaxLoader',
            optionName: '<?php echo MultiMailChimp::OPTION_LISTS_IDS; ?>',
            checkedValues: <?php echo json_encode($options[self::OPTION_LISTS_IDS]); ?>
        });
    });
});
</script>
<style type="text/css">
    .mmc_listName_label {display: inline-block; width:150px;}
</style>
<div class="wrap">
    <h2>Multi MailChimp Settings</h2>
    <p>You can insert this plugin using a shortcode ([mmc-display-lists]) or as a widget.</p>
    <?php if (!empty($error)) : ?>
        <div class="error"><p><?php echo $error; ?></p></div>
    <?php endif; ?>

    <?php if (!empty($message)) : ?>
        <div class="updated fade"><p><?php echo $message; ?></p></div>
    <?php endif; ?>

    <form method="post" action="<?php echo attribute_escape(stripslashes($_SERVER['REQUEST_URI'])); ?>">
        <table class="form-table">
            <tbody valign="top">
                <tr>
                    <th scope="row"><label for="<?php echo MultiMailChimp::OPTION_API_KEY; ?>">MailChimp API Key</label></th>
                    <td><input type="text" style="width:300px" id="<?php echo MultiMailChimp::OPTION_API_KEY; ?>" name="options[<?php echo MultiMailChimp::OPTION_API_KEY; ?>]" value="<?php echo attribute_escape($options[MultiMailChimp::OPTION_API_KEY]); ?>"/>
                        <button id="mmc_fetchLists">Fetch Lists</button>
                    </td>

                </tr>

                    <tr>
                        <th scope="row"><label for="<?php echo MultiMailChimp::OPTION_LISTS_IDS; ?>">Choose lists</label></th>
                        <td>
<img src="<?php echo MMC_URL; ?>/views/img/ajax-loader.gif" id="mmc_ajaxLoader" style="display:none"/>
<div id="mmc_listsContainer">
                            <?php if (!empty($lists)) {
                                foreach ($lists as $key => $val): ?>
                        <input type="checkbox" id="mmc_option_<?php echo $key; ?>" name="options[<?php echo MultiMailChimp::OPTION_LISTS_IDS; ?>][]" value="<?php echo $key; ?>"<?php if (in_array($key, $options[self::OPTION_LISTS_IDS]))
                            echo 'checked="checked"'; ?>/> <label class="mmc_listName_label" for="mmc_option_<?php echo $key; ?>"><?php echo $val; ?>,</label> <label class="mmc_listDescription_label" for="mmc_option_description_<?php echo $key; ?>">Description:</label><input type="text" size="60" maxlength="50" id="mmc_option_description_<?php echo $key; ?>" name="options[<?php echo MultiMailChimp::OPTION_LIST_DESCRIPTIONS; ?>][<?php echo $key; ?>]" value="<?php echo $descriptions[$key]; ?>"/>&nbsp;(Max 50 chars)<br />
        <?php endforeach;
    } ?></div>
                </td>
                </tr>

            </tbody>
        </table>
        <p class="submit submit-top">
<?php wp_nonce_field('multi-mailchimp-config'); ?>
            <input type="submit" name="submit" value="Save Changes" class="button-primary"/>
        </p>
    </form>
