<?php
/**
 * MultiMailChimp processors
 * @package MultiMailChimp/Library
 */
require_once MMC_PATH . '/lib/MCAPI.class.php';

/**
 * MailChimp processor singleton
 *
 * @author CreativeMinds (http://www.cminds.com)
 * @version 1.0
 * @copyright Copyright (c) 2012, CreativeMinds
 * @package MultiMailChimp/Library
 */
class MultiMailChimp {

    /**
     * @var string $_api_key MailChimp API Key
     */
    protected $_api_key = '';

    /**
     * @var array $_lists_ids Enabled lists for subscription
     */
    protected $_lists_ids = array();

    /**
     * @var array $_all_lists All lists for the account
     */
    protected $_all_lists = array();

    /**
     * @var array $_list_descriptions User-defined descriptions for lists
     */
    protected $_list_descriptions = array();

    /**
     * @var MCAPI $_api MailChimp API Handler
     */
    protected $_api = null;

    /**
     * @var MultiMailChimp $_instance Singleton instance
     * @static
     */
    protected static $_instance = null;

    /**
     * WP option key for MailChimp API Key
     */
    const OPTION_API_KEY = 'mmc_api_key';
    /**
     * WP option key for enabled lists
     */
    const OPTION_LISTS_IDS = 'mmc_lists_ids';
    /**
     * WP option key for lists descriptions
     */
    const OPTION_LIST_DESCRIPTIONS = 'mmc_list_descriptions';
    /**
     * WP option key for all lists
     */
    const OPTION_ALL_LISTS = 'mmc_all_lists';

    /**
     * MailChimp subscription status: not exists
     */
    const STATUS_NOTEXISTS = 'notexists';
    /**
     * MailChimp subscription status: pending
     */
    const STATUS_PENDING = 'pending';
    /**
     * MailChimp subscription status: unsubscribed
     */
    const STATUS_UNSUBSCRIBED = 'unsubscribed';
    /**
     * MailChimp subscription status: subscribed
     */
    const STATUS_SUBSCRIBED = 'subscribed';

    /**
     * Init plugin
     */
    public static function init() {
        add_action('admin_menu', array(get_class(), 'registerOptionsPage'));
        add_shortcode('mmc-display-lists', array(get_class(), 'shortcodeDisplayLists'));
        add_action('widgets_init', create_function('', 'return register_widget("MultiMailChimpWidget");'));
        if (!is_admin()) {
            add_action('init', array(get_class(), 'processSubscribeAjax'));
            wp_register_style('multimailchimp_style', MMC_URL . '/views/style.css');
            wp_enqueue_style('multimailchimp_style');
            wp_register_script('mmc_subscribe', MMC_URL . '/views/js/subscribe.js', array('jquery'));
            wp_enqueue_script('mmc_subscribe');
        }
    }

    /**
     * Get singleton instance
     * 
     * @static
     * @return MultiMailChimp
     */
    public static function getInstance() {
        if (empty(self::$_instance)) {
            self::$_instance = new self;
        }
        return self::$_instance;
    }

    /**
     * Constructor
     */
    public function __construct() {
        $this->getOptions();
    }

    /**
     * Get MailChimp API handler
     */
    public function getAPI() {
        return $this->_api;
    }

    /**
     * Subscribe email to list
     * 
     * @static
     * @param string $email E-mail to be subscribed
     * @param string $list MailChimp List ID
     * @return boolean 
     */
    public static function subscribeMail($email, $list, $data = array()) {
        $mc = self::getInstance();
        $subscribed = false;
        $api = $mc->getAPI();
        if (!empty($api)) {
            if ($email && is_email($email)) {
                $subscribed = $api->listSubscribe($list, $email, $data, 'html', false, true);
            }
        }
        return $subscribed;
    }

    /**
     * Unsubscribe e-mail from list
     * 
     * @static
     * @param string $email E-mail to be unsubscribed
     * @param string $list MailChimp List ID
     * @return boolean 
     */
    public static function unsubscribeMail($email, $list) {
        $mc = self::getInstance();
        $unsubscribed = false;
        if (!empty($mc->_api)) {
            if ($email && is_email($email)) {
                $unsubscribed = $mc->_api->listUnsubscribe($list, $email, true, false, false);
            }
        }
        return $unsubscribed;
    }

    /**
     * Get subscription status
     * 
     * @static
     * @param string $email E-mail
     * @param string $list MailChimp List ID
     * @return string Status name 
     */
    public static function getSubscriptionStatus($email, $list) {
        $mc = self::getInstance();
        $subscribed = self::STATUS_NOTEXISTS;
        $api = $mc->getAPI();
        if (!empty($api)) {
            if ($email && is_email($email)) {
                $response = $api->listMemberInfo($list, array($email));
                if (!$api->errorCode && $response['success'] == 1) {
                    $subscribed = $response['data'][0]['status'];
                }
            }
        }
        return $subscribed;
    }

    /**
     * Checks whether e-mail is subscribed to a list
     * 
     * @static
     * @param string $email E-mail
     * @param string $list MailChimp List ID
     * @return boolean 
     */
    public static function isEmailSubscribed($email, $list) {
        $mc = self::getInstance();
        $subscribed = false;
        $api = $mc->getAPI();
        if (!empty($api)) {
            if ($email && is_email($email)) {
                $response = $api->listMemberInfo($list, array($email));
                if (!$api->errorCode) {
                    $subscribed = ($response['success'] == 1);
                }
            }
        }
        return $subscribed;
    }

    /**
     * Get options for plugin
     * 
     * @return array
     */
    public function getOptions() {
        $this->_api_key = get_option(self::OPTION_API_KEY, $this->_api_key);
        $this->_lists_ids = get_option(self::OPTION_LISTS_IDS, $this->_lists_ids);
        $this->_all_lists = get_option(self::OPTION_ALL_LISTS, $this->_all_lists);
        $this->_list_descriptions = get_option(self::OPTION_LIST_DESCRIPTIONS, $this->_list_descriptions);
        if (!empty($this->_api_key)) {
            $this->_api = new MCAPI($this->_api_key);
        }
        return array(self::OPTION_API_KEY => $this->_api_key, self::OPTION_LISTS_IDS => $this->_lists_ids, self::OPTION_LIST_DESCRIPTIONS => $this->_list_descriptions);
    }

    /**
     * Get list of available MailChimp lists for API Key
     * 
     * @param string $apiKey MailChimp API Key
     * @param boolean $forceReload Force reloading lists from MailChimp instead of read cached ones
     * @return array List of List IDs 
     */
    public function getAvailableLists($apiKey = '', $forceReload = false) {
        if (!empty($apiKey)) {
            $this->_api = new MCAPI($apiKey);
        }
        if (empty($apiKey) && !empty($this->_all_lists) && !$forceReload) {
            return $this->_all_lists;
        }
        if (empty($this->_api))
            return null;
        $lists = $this->_api->lists(array(), 0, 100);
        $lists = $lists['data'];
        $listArr = array();
        foreach ($lists as $list) {
            $listArr[$list['id']] = $list['name'];
        }
        $this->_all_lists = $listArr;
        update_option(self::OPTION_ALL_LISTS, $this->_all_lists);
        return $listArr;
    }

    /**
     * Save plugin options
     * 
     * @param array $options 
     */
    public function saveOptions($options) {
        update_option(self::OPTION_API_KEY, $options[self::OPTION_API_KEY]);
        update_option(self::OPTION_LISTS_IDS, $options[self::OPTION_LISTS_IDS]);
        update_option(self::OPTION_LIST_DESCRIPTIONS, $options[self::OPTION_LIST_DESCRIPTIONS]);
    }

    /**
     * Add new options page in admin panel
     * 
     * @static
     */
    public static function registerOptionsPage() {
        self::processAdminAjax();
        add_options_page('Multi MailChimp', 'Multi MailChimp', 'manage_options', 'multi-mailchimp', array(get_class(), 'adminMenu'));
    }

    /**
     * Render new page in admin panel
     * 
     * @static
     */
    public static function adminMenu() {
        wp_register_script('mmc_admin', MMC_URL . '/views/js/admin.js', array('jquery'));
        wp_enqueue_script('mmc_admin');
        $mailChimp = self::getInstance();
        if (isset($_POST['options'])) {
            check_admin_referer('multi-mailchimp-config');
            $mailChimp->saveOptions($_POST['options']);
        }


        $options = $mailChimp->getOptions();
        $lists = $mailChimp->getAvailableLists('', true);
        $descriptions = $options[self::OPTION_LIST_DESCRIPTIONS];
        require MMC_PATH . '/views/admin_page.php';
    }

    /**
     * Display available lists + subscribe controls to a user
     * 
     * @static
     */
    public static function displayLists() {
        if (is_user_logged_in()) {
            $mc = self::getInstance();
            if ($mc->getAPI() != null) {
                $listNames = $mc->getAvailableLists();
                $descriptions = $mc->getListDescriptions();
                $allowedLists = $mc->getListsIDs();
                $email = wp_get_current_user()->user_email;
                foreach ($allowedLists as $listId) {
                    $listName = $listNames[$listId];
                    $description = isset($descriptions[$listId])?$descriptions[$listId]:'';
                    $isSubscribed = self::isEmailSubscribed($email, $listId);
                    $subscriptionList[] = array('id' => $listId, 'name' => $listName, 'description' => $description, 'isSubscribed' => $isSubscribed);
                }
                require MMC_PATH . '/views/display_lists.php';
            }
        }
    }

    /**
     * Display lists from shortcode [mmc_display_lists]
     * 
     * @param array $atts
     * @param string $content
     * @param string $code
     */
    public static function shortcodeDisplayLists($atts, $content = null, $code = '') {
        if (is_feed())
            return '';
        self::displayLists();
    }

    /**
     * Process ajax "fetch available lists" in admin panel
     */
    public static function processAdminAjax() {
        if (current_user_can('manage_options') && isset($_POST['ajax']) && isset($_POST['mmc_api_key'])) {
            $apiKey = $_POST['mmc_api_key'];
            header('Content-type: application/json');
            $lists = self::getInstance()->getAvailableLists($apiKey);
            $descriptions = self::getInstance()->getListDescriptions();
            $output = array();
            foreach ($lists as $key => $name) {
                $descr = isset($descriptions[$key]) ? $descriptions[$key] : '';
                $output[$key] = array('name' => $name, 'description' => $descr);
            }
            echo json_encode($output);
            exit;
        }
    }

    /**
     * Process ajax "subscribe/unsubscribe" on user side
     */
    public static function processSubscribeAjax() {
        if (is_user_logged_in() && isset($_POST['mmc_ajax']) && isset($_POST['mmc_id']) && isset($_POST['mmc_action'])) {
            $result = false;
            $user = wp_get_current_user();
            $email = $user->user_email;
            $firstName = $user->first_name;
            $lastName = $user->last_name;
            switch ($_POST['mmc_action']) {
                case 'subscribe':
                    $data = array(
                        'FNAME' => $firstName,
                        'LNAME' => $lastName
                    );
                    self::subscribeMail($email, $_POST['mmc_id'], $data);
                    $result = self::getSubscriptionStatus($email, $_POST['mmc_id']);
                    break;
                case 'unsubscribe':
                    self::unsubscribeMail($email, $_POST['mmc_id']);
                    $result = self::getSubscriptionStatus($email, $_POST['mmc_id']);
                    break;
            }
            $apiKey = $_POST['mmc_api_key'];
            header('Content-type: application/json');
            echo json_encode(array('status' => $result));
            exit;
        }
    }

    /**
     * Get enabled lists IDs
     * 
     * @return array MailChimp Lists IDs
     */
    public function getListsIDs() {
        return $this->_lists_ids;
    }
    
        /**
     * Get list descriptions
     * 
     * @return array MailChimp List Descriptions
     */
    public function getListDescriptions() {
        return $this->_list_descriptions;
    }

}

/**
 * MultiMailChimp widget
 *
 * @author Sebastian Palus
 * @version 0.1.0
 * @copyright Copyright (c) 2012, REC
 * @package MultiMailChimp/Library
 */
class MultiMailChimpWidget extends WP_Widget {

    /**
     * Create widget
     */
    public function MultiMailChimpWidget() {
        $widget_ops = array('classname' => 'MultiMailChimpWidget', 'description' => 'Allows user to choose which lists he wants to be subscribed to');
        $this->WP_Widget('MultiMailChimpWidget', 'Multi-MailChimp Widget', $widget_ops);
    }

    /**
     * Widget options form
     * @param WP_Widget $instance 
     */
    public function form($instance) {
        $instance = wp_parse_args((array) $instance, array('title' => ''));
        $title = $instance['title'];
        ?>
        <p><label for="<?php echo $this->get_field_id('title'); ?>">Title: <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo attribute_escape($title); ?>" /></label></p>
        <p><label for="mmc_shortcode">Shortcode: </label><textarea class="widefat" id="mmc_shortcode" readonly>[mmc-display-lists]</textarea>
            <?php
        }

        /**
         * Update widget options
         * @param WP_Widget $new_instance
         * @param WP_Widget $old_instance
         * @return WP_Widget 
         */
        public function update($new_instance, $old_instance) {
            $instance = $old_instance;
            $instance['title'] = $new_instance['title'];
            return $instance;
        }

        /**
         * Render widget
         * 
         * @param array $args
         * @param WP_Widget $instance 
         */
        public function widget($args, $instance) {
            extract($args, EXTR_SKIP);
            if (is_user_logged_in()) {

                echo $before_widget;
                $title = empty($instance['title']) ? ' ' : apply_filters('widget_title', $instance['title']);

                if (!empty($title))
                    echo $before_title . $title . $after_title;;

                // WIDGET CODE GOES HERE
                MultiMailChimp::displayLists();

                echo $after_widget;
            }
        }

    }
    ?>
