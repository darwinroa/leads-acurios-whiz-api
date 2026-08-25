<?php
    namespace WhizApi\Models;
    if ( ! defined( 'ABSPATH' ) ) {
        exit;
    }
    class WhizSimpleLeadModel extends WhizModels
    {
        public static $table_name = 'whiz_api_basic_users';

        public static function get_leads($page = 1, $per_page = 10) {
            global $wpdb;
            $table = static::$table_name;
            
            $offset = ($page - 1) * $per_page;
            
            $leads = $wpdb->get_results(
                $wpdb->prepare("SELECT * FROM {$table} ORDER BY created_at DESC LIMIT %d OFFSET %d", 
                $per_page, $offset)
            );
            
            $total = $wpdb->get_var("SELECT COUNT(*) FROM {$table}");
            
            return [
                'leads' => $leads,
                'total' => $total,
                'per_page' => $per_page,
                'current_page' => $page,
                'total_pages' => ceil($total / $per_page)
            ];
        }

        public static function download_csv() {
            global $wpdb;
            $table = static::$table_name;
            
            $columns = [
                'id', 'name', 'lastname', 'email', 'phone', 'address', 'doc_type', 
                'doc_number', 'subscribed', 'message', 'department', 'province', 
                'district', 'nationality', 'area', 'resume', 'accepts_advertising', 
                'more18', 'parent_name', 'parent_phone', 'parent_dni', 'parent_address', 
                'parent_title', 'parent_accepts_advertising', 'attendees', 'company_name',
                'event_date', 'utm_medium', 'utm_campaign', 'utm_source', 'created_at'
            ];
            
            $query = "SELECT " . implode(', ', $columns) . " FROM {$table}";
            $results = $wpdb->get_results($query, ARRAY_A);

            if (empty($results)) {
                return [
                    'status' => false,
                    'message' => 'No hay datos para exportar.'
                ];
            }

            $filename = "export_" . date('d_m_Y') . "_leads.csv";

            header('Content-Type: text/csv');
            header('Content-Disposition: attachment; filename="' . $filename . '"');
            header('Pragma: no-cache');
            header('Expires: 0');
            
            ob_start();
            $output = fopen('php://output', 'w');
            
            fputcsv($output, $columns);
            
            foreach ($results as $lead) {
                $lead['subscribed'] = $lead['subscribed'] ? 'Si' : 'No';
                $lead['accepts_advertising'] = $lead['accepts_advertising'] ? 'Si' : 'No';
                $lead['more18'] = $lead['more18'] ? 'Si' : 'No';
                $lead['parent_accepts_advertising'] = $lead['parent_accepts_advertising'] ? 'Si' : 'No';
                
                if (isset($lead['message'])) {
                    $lead['message'] = self::clean_message($lead['message']);
                }
                
                if (array_filter($lead)) {
                    fputcsv($output, $lead);
                }
            }
            
            fclose($output);
            ob_end_flush();
            exit();
        }

        public static function get_leads_json($page = 1, $per_page = 1000) {
            global $wpdb;
            $table = static::$table_name;
            
            $offset = ($page - 1) * $per_page;
            
            $results = $wpdb->get_results(
                $wpdb->prepare(
                    "SELECT * FROM {$table} ORDER BY created_at DESC LIMIT %d OFFSET %d",
                    $per_page,
                    $offset
                ),
                ARRAY_A
            );
            
            $total = $wpdb->get_var("SELECT COUNT(*) FROM {$table}");
            
            if (empty($results)) {
                return ['data' => [], 'total' => 0];
            }
            
            $processed_data = array_map(function($lead) {
                return [
                    'id' => (int)$lead['id'],
                    'name' => $lead['name'],
                    'lastname' => $lead['lastname'],
                    'email' => $lead['email'],
                    'phone' => $lead['phone'],
                    'address' => $lead['address'],
                    'doc_type' => $lead['doc_type'],
                    'doc_number' => $lead['doc_number'],
                    'subscribed' => (bool)$lead['subscribed'],
                    'message' => self::clean_message($lead['message']),
                    'department' => $lead['department'],
                    'province' => $lead['province'],
                    'district' => $lead['district'],
                    'nationality' => $lead['nationality'],
                    'area' => $lead['area'],
                    'resume' => $lead['resume'],
                    'accepts_advertising' => (bool)$lead['accepts_advertising'],
                    'more18' => (bool)$lead['more18'],
                    'parent_name' => $lead['parent_name'],
                    'parent_phone' => $lead['parent_phone'],
                    'parent_dni' => $lead['parent_dni'],
                    'parent_address' => $lead['parent_address'],
                    'parent_title' => $lead['parent_title'],
                    'parent_accepts_advertising' => (bool)$lead['parent_accepts_advertising'],
                    'attendees' => $lead['attendees'],
                    'company_name' => $lead['company_name'],
                    'event_date' => $lead['event_date'],
                    'utm_medium' => $lead['utm_medium'],
                    'utm_campaign' => $lead['utm_campaign'],
                    'utm_source' => $lead['utm_source'],
                    'created_at' => $lead['created_at']
                ];
            }, $results);
            
            return [
                'data' => $processed_data,
                'total' => (int)$total
            ];
        }
        

    }
