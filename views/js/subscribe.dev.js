/**
 * MultiMailChimp client JavaScript library
 *
 * @author CreativeMinds (http://www.cminds.com)
 * @version 1.0
 * @copyright Copyright (c) 2012, CreativeMinds
 * @package MultiMailChimp/JavaScript
 */
jQuery(document).ready(function() {
    var MultiMailChimp = {
        /**
         * Init client library, bind to .mmc_button
         */
        init: function() {
            jQuery('.mmc_button').click(function(e) {
                e.preventDefault();
                var button = jQuery(this);
                if (button.hasClass('mmc_follow') || button.hasClass('mmc_pending')) {
                    button.removeClass('mmc_follow mmc_pending').addClass('mmc_loading');
                    MultiMailChimp.subscribe(button.parents('.mmc_list_row').data('id'),
                        function(data) {
                            var className = 'mmc_unfollow';
                            if (data.status=='pending')
                                className = 'mmc_pending';
                            button.removeClass('mmc_follow mmc_loading').addClass(className);
                        });
                } else if (button.hasClass('mmc_unfollow') || button.hasClass('mmc_pending')) {
                    button.removeClass('mmc_unfollow mmc_pending').addClass('mmc_loading');
                    MultiMailChimp.unsubscribe(button.parents('.mmc_list_row').data('id'),
                        function(data) {
                            var className = 'mmc_follow';
                            if (data.status=='pending')
                                className = 'mmc_pending';
                            button.removeClass('mmc_unfollow mmc_loading').addClass(className);
                        });
                }
            });
        },
        /**
         * Subscribe user to a list via AJAX request
         * 
         * @param string id MailChimp List ID
         * @param callback successFunc
         */
        subscribe: function(id, successFunc) {
            jQuery.ajax({
                type: 'POST',
                data: {
                    mmc_ajax:1, 
                    mmc_id: id, 
                    mmc_action: 'subscribe'
                },
                success: successFunc
            });
        },
        /**
         * Unsubscribe user from a list via AJAX request
         * 
         * @param string id MailChimp List ID
         * @param callback successFunc
         */
        unsubscribe: function(id, successFunc) {
            jQuery.ajax({
                type: 'POST',
                data: {
                    mmc_ajax:1, 
                    mmc_id: id, 
                    mmc_action: 'unsubscribe'
                },
                success: successFunc
            });
        }
    };
    MultiMailChimp.init();
});