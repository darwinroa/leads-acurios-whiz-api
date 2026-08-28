<?php
/**
 * Plugin Name:       Whiz Api
 * Plugin URI:        https://github.com/darwinroa/leads-acurios-whiz-api
 * Description:       Integracion de Whiz
 * Version:           2.0.1
 * Requires at least: 5.2
 * Requires PHP:      7.2
 * Author:            Franco Caballero, Hendry M. Flores & Darwin Roa
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Update URI:        https://github.com/darwinroa/leads-acurios-whiz-api
 * Text Domain:       wp-whiz-api
 * Domain Path:       /languages
 */

namespace WhizAPI;
require_once plugin_dir_path(__FILE__) . 'vendor/autoload.php';
require_once plugin_dir_path(__FILE__) . 'overwrite.php';
use WhizApi\Views\WhizViews;
use WhizApi\Migrations\WhizMigrationCore;
use WhizApi\Routers\MainApiRouter;
use WhizApi\Services\WhizApiService;
if (!defined('ABSPATH')) {
    exit;
}

//constants
define('WHIZ_API_PLUGIN_BASE_DIR', __DIR__);
define('WHIZ_API_PLUGIN_BASE_URI', plugin_dir_url(__FILE__));

$views = new WhizViews();
$migrations = new WhizMigrationCore();
$api_router = new MainApiRouter();

//
add_action('admin_enqueue_scripts', function () {
    wp_register_style('admin_css_for_whiz', constant('WHIZ_API_PLUGIN_BASE_URI') . 'public/css/app.css', false, '1.5.0');
    wp_enqueue_style('admin_css_for_whiz');
});
//
//wp_mail overwrite
function whiz_wp_mail($to, $subject, $message, $headers = '', $attachments = [])
{
    ;
    try {
        $whiz_api = new WhizApiService();
        if (is_array($to)) {
            foreach ($to as $to_) {
                $resp = [];
                $resp[] = $whiz_api->send_email($to_, $subject, $message);
            }
        } else {
            $resp = $whiz_api->send_email($to, $subject, $message);
        }
    } catch (Exception $e) {
        return false;
    }
    return $resp;
}


