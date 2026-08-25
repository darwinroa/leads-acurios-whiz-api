<?php
namespace WhizApi\Models;
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
class WhizApiConfigModel extends WhizModels {
    public static $table_name = 'whiz_api_options';

    public static function get_option($option) {
        global $wpdb;
        $table = self::$table_name;
        $options = $wpdb->get_results($wpdb->prepare("SELECT * FROM {$table} WHERE slug = %s", $option));
        return !empty($options) ? $options[0] : null;
    }

    public static function get_option_value($option) {
        global $wpdb;
        $table = self::$table_name;
        $options = $wpdb->get_results($wpdb->prepare("SELECT * FROM {$table} WHERE slug = %s", $option));
        if(!empty($options)) {
            return $options[0]->value;
        } else {
            return false;
        }
    }

    public static function set_option($option, $value) {
        global $wpdb;
        return $wpdb->update(self::$table_name, ['value' => $value], ['slug' => $option]);
    }
}