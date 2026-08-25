<?php
    namespace WhizApi\Models;
    if (!defined('ABSPATH')) {
        exit;
    }
    class WhizModels
    {
        public static $table_name;
        
        //Crud Basico
        public static function get_all() 
        {
            global $wpdb;
            $table = static::$table_name;

            return $wpdb->get_results("SELECT * FROM {$table}");
        }

        public static function get_by_id(int $id): ?object
        {
            global $wpdb;
            $table = static::$table_name;
            
            return $wpdb->get_row(
                $wpdb->prepare("SELECT * FROM {$table} WHERE id = %d", $id)
            );
        }

        public static function create($attributes)
        {
            global $wpdb;
            return $wpdb->insert(static::$table_name, $attributes);
        }

         public static function update($id, $data)
        {
            global $wpdb;
            return $wpdb->update(static::$table_name, $data, ['id' => $id]);
        }

        public static function delete($id)
        {
            global $wpdb;
            return $wpdb->delete(static::$table_name, ['id'=> $id]);
        }
        
        //Helpers
        public static function clean_message($message) {
            $message = str_replace(["\r\n", "\r", "\n"], ' ', $message);
            $message = preg_replace('/\s+/', ' ', $message);
            $message = preg_replace('/[\x{00A0}]/u', ' ', $message);
            return trim($message);
        }

    }
