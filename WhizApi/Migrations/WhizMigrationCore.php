<?php
namespace WhizApi\Migrations;

if (!defined('ABSPATH')) {
    exit;
}

class WhizMigrationCore {

    public function __construct() {
        $this->run_migrations();
    }

    public function create_table_if_no_exist($table_name, $create_ddl, $seeds = false) {
        global $wpdb;

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

        dbDelta($create_ddl);

        if ($seeds) {
            $this->run_seeds($table_name, $seeds);
        }
    }

    public function run_seeds($table_name, $data) {
        global $wpdb;

        foreach ($data as $item) {
            $query = $wpdb->prepare("SELECT COUNT(*) FROM $table_name WHERE slug = %s", $item['slug']);
            $exists = $wpdb->get_var($query);

            if (!$exists) {
                $wpdb->insert($table_name, $item);
            }
        }
    }

    public function run_migrations() {
        $this->create_table_if_no_exist('whiz_api_options', "
          CREATE TABLE `whiz_api_options` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `slug` VARCHAR(230) UNIQUE,
            `value` TEXT DEFAULT NULL,
            `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`)
          ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
        ", [
            ['slug' => 'sender_email', 'value' => ''],
            ['slug' => 'sender_name', 'value' => ''],
            ['slug' => 'access_token', 'value' => ''],
            ['slug' => 'whiz_api_id', 'value' => ''],
            ['slug' => 'whiz_api_token', 'value' => ''],
            ['slug' => 'project_name', 'value' => ''],
            ['slug' => 'to_email', 'value' => ''],
            ['slug' => 'to_event_email', 'value' => ''],
            ['slug' => 'to_contact_email', 'value' => ''],
            ['slug' => 'to_work_email', 'value' => ''],
            ['slug' => 'to_footer_email', 'value' => ''],
            ['slug' => 'resend_api_key', 'value' => ''],
            ['slug' => 'export_api_token', 'value' => ''],
        ]);

        $this->create_table_if_no_exist('whiz_api_basic_users', "
          CREATE TABLE `whiz_api_basic_users` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `name` VARCHAR(230) DEFAULT NULL,
            `lastname` VARCHAR(230) DEFAULT NULL,
            `email` VARCHAR(230) DEFAULT NULL,
            `phone` VARCHAR(230) DEFAULT NULL,
            `address` VARCHAR(230) DEFAULT NULL,
            `doc_type` VARCHAR(230) DEFAULT NULL,
            `doc_number` VARCHAR(230) DEFAULT NULL,
            `subscribed` TINYINT(1) DEFAULT 0,
            `message` TEXT DEFAULT NULL,
            `department` VARCHAR(230) DEFAULT NULL,
            `province` VARCHAR(230) DEFAULT NULL,
            `district` VARCHAR(230) DEFAULT NULL,
            `nationality` VARCHAR(230) DEFAULT NULL,
            `area` VARCHAR(230) DEFAULT NULL,
            `resume` TEXT DEFAULT NULL,
            `accepts_advertising` TINYINT(1) DEFAULT 0,
            `more18` TINYINT(1) DEFAULT 1,
            `parent_name` VARCHAR(230) DEFAULT NULL,
            `parent_phone` VARCHAR(230) DEFAULT NULL,
            `parent_dni` VARCHAR(230) DEFAULT NULL,
            `parent_address` VARCHAR(230) DEFAULT NULL,
            `parent_title` VARCHAR(230) DEFAULT NULL,
            `parent_accepts_advertising` TINYINT(1) DEFAULT 0,
            `attendees` INT DEFAULT NULL,
            `company_name` VARCHAR(230) DEFAULT NULL,
            `event_date` DATE DEFAULT NULL,
            `utm_medium` VARCHAR(230) DEFAULT NULL,
            `utm_campaign` VARCHAR(230) DEFAULT NULL,
            `utm_source` VARCHAR(230) DEFAULT NULL,
            `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`)
          ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
        ");
    }
}