<?php
    namespace WhizApi\Views;
    use WhizApi\Models\WhizApiConfigModel;
    use WhizApi\Models\WhizSimpleLeadModel;
    if ( ! defined( 'ABSPATH' ) ) {
        exit;
    }
    class WhizViews
    {
        function __construct(){
            $this->add_all_menu();
        }
        public function add_principal_menu_item(){
            add_menu_page(
                'Whiz',
                'Whiz',
                'manage_options',
                'whiz_api_panel_general',
                function () {
                    $data = [];
                    $opts = WhizApiConfigModel::get_all();
                    foreach($opts as $opt){
                        $data[$opt->slug] = $opt->value;
                    }
                    $this->render('admin/settings.php', $data);
                },
                constant('WHIZ_API_PLUGIN_BASE_URI') . '/public/img/logo/whiz-iso.png',
                1
            );
            add_submenu_page(
                'whiz_api_panel_general',
                'Leads',
                'Leads',
                'manage_options',
                'whiz_api_panel_general_leads',
                function () {
                    $page = isset($_GET['pagenum']) ? max(1, intval($_GET['pagenum'])) : 1;
                    $result = WhizSimpleLeadModel::get_leads($page, 10);
                    
                    $data = [
                        'leads' => $result['leads'],
                        'page' => $result['current_page'],
                        'total_pages' => $result['total_pages'],
                        'total_leads' => $result['total']
                    ];
                    
                    $this->render('admin/leads.php', $data);
                }
            );
        }
        public function add_all_menu(){
            add_action('admin_menu', [ $this, 'add_principal_menu_item' ]);
        }
        public function render($pathname, $data = ['void' => true]){
            require 'layout/header.php';
            require $pathname;
            require 'layout/footer.php';
        }
    }
