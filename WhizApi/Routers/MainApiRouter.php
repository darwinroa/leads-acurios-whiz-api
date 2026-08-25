<?php
namespace WhizApi\Routers;

use WhizApi\Models\WhizApiConfigModel;
use WhizApi\Models\WhizSimpleLeadModel;
use WP_REST_Response;
use WP_Error;

if (!defined('ABSPATH')) {
    exit;
}

class MainApiRouter
{
    private const API_NAMESPACE = 'whiz-api/v1';
    
    public function __construct()
    {
        $this->initRouters();
    }

    public function initRouters(): void
    {
        add_action('rest_api_init', function () {
            $routes = [
                'settings' => [
                    'methods' => 'POST',
                    'callback' => [$this, 'save_settings']
                ],
                'lead' => [
                    'methods' => 'POST',
                    'callback' => [$this, 'save_lead']
                ],
                'upload' => [
                    'methods' => 'POST',
                    'callback' => [$this, 'upload_file']
                ],
                'lead/(?P<id>\d+)' => [
                    'methods' => 'GET',
                    'callback' => [$this, 'get_lead_details']
                ],
                'lead/download-csv' => [
                    'methods' => 'GET',
                    'callback' => [$this, 'download_csv']
                ],
                'lead/json' => [
                    'methods' => 'GET',
                    'callback' => [$this, 'api_json']
                ],
            ];

            foreach ($routes as $route => $args) {
                register_rest_route(self::API_NAMESPACE, '/' . $route . '/', $args);
            }
        });

    }

    public function handle_lead_details_ajax(): void
    {   
        $lead_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
        if ($lead_id <= 0) {
            wp_send_json_error('ID inválido', 400);
            return;
        }
        
        $lead = WhizSimpleLeadModel::get_by_id($lead_id);
        
        if ($lead) {
            wp_send_json_success(['lead' => $lead]);
        } else {
            wp_send_json_error('Lead no encontrado', 404);
        }
    }

    public function save_lead(\WP_REST_Request $request): array
    {
        $content_type = $request->get_header('content_type');
        $body = [];
        $uploaded_file = null;
        $resume_url = null;
        $attachments = [];

        if (stripos($content_type, 'application/json') !== false) {
            $body = $request->get_json_params();
        } elseif (stripos($content_type, 'multipart/form-data') !== false) {
            $body = $request->get_body_params();
            $files = $request->get_file_params();
            $uploaded_file = $files['resume'] ?? null;
        }

        if (empty($body)) {
            return [
                'status' => false,
                'message' => 'Todos los parámetros son requeridos'
            ];
        }

        // Handle file upload
        if ($uploaded_file && $uploaded_file['error'] === UPLOAD_ERR_OK) {
            $upload_result = $this->handle_file_upload($uploaded_file);
            
            if (isset($upload_result['error'])) {
                return $upload_result;
            }
            
            $resume_url = $upload_result['url'];
            $attachments[] = $upload_result['file'];
        }

        // Prepare lead data
        $lead_data = $this->prepare_lead_data($body, $resume_url ?? '');

        $resp = WhizSimpleLeadModel::create($lead_data);

        if (!$resp) {
            return [
                'status' => false,
                'message' => 'Problemas al crear el lead.',
                'data' => $resp
            ];
        }

        // Send email notification
        list($content, $to, $subject) = $this->generate_email_template($body, $resume_url ?? '');

        $headers = array('Content-Type: text/html; charset=UTF-8');

        $emailResponse = wp_mail($to, $subject, $content, $headers, $attachments);

        error_log("Email sending attempt to $to. Result: " . ($emailResponse ? 'Success' : 'Failure'));

        // Trigger Real-time Webhook to Google Sheets (Non-blocking / Background)
        $this->trigger_google_webhook($resp);

        if (!$emailResponse) {
            error_log('Email sending failed. Last error: ' . print_r(error_get_last(), true));
            
            return [
                'status' => false,
                'message' => 'Lead creado pero falló el envío de notificación por correo',
                'data' => $resp
            ];
        }

        return [
            'status' => true,
            'message' => 'Nuevo lead agregado exitosamente',
            'data' => $resp
        ];
    }

    private function handle_file_upload(array $file): array
    {
        $upload_overrides = ['test_form' => false];
        $uploaded_file_data = wp_handle_upload($file, $upload_overrides);

        if (!isset($uploaded_file_data['url'], $uploaded_file_data['file']) || !file_exists($uploaded_file_data['file'])) {
            return [
                'status' => false,
                'message' => 'Error al subir el archivo',
                'error' => $uploaded_file_data['error'] ?? 'Error desconocido'
            ];
        }

        if (!is_readable($uploaded_file_data['file'])) {
            return [
                'status' => false,
                'message' => 'El archivo no es legible'
            ];
        }

        return [
            'url' => $uploaded_file_data['url'],
            'file' => $uploaded_file_data['file']
        ];
    }

    private function prepare_lead_data(array $body, ?string $resume_url): array
    {
        $is_subscription = ($body['form'] ?? '') === "subscription";
        
        return [
            'name' => $body['name'] ?? null,
            'lastname' => $body['lastname'] ?? null,
            'phone' => $body['phone'] ?? null,
            'email' => $body['email'] ?? null,
            'doc_type' => $body['doc_type'] ?? null,
            'doc_number' => $body['doc_number'] ?? null,
            'message' => $body['message'] ?? null,
            'department' => $body['department'] ?? null,
            'province' => $body['province'] ?? null,
            'district' => $body['district'] ?? null,
            'address' => $body['address'] ?? null,
            'nationality' => $body['nationality'] ?? null,
            'area' => $body['area'] ?? null,
            'resume' => $resume_url,
            'subscribed' => isset($body['subscribed']) ? (int)$body['subscribed'] : ($is_subscription ? 1 : 0),
            'accepts_advertising' => isset($body['accepts_advertising']) ? (int)$body['accepts_advertising'] : 0,            
            'more18' => isset($body['more18']) ? (int)$body['more18'] : 0,
            'parent_name' => $body['parent_name'] ?? null,
            'parent_phone' => $body['parent_phone'] ?? null,
            'parent_dni' => $body['parent_dni'] ?? null,
            'parent_address' => $body['parent_address'] ?? null,
            'parent_title' => $body['parent_title'] ?? null,
            'parent_accepts_advertising' => isset($body['parent_accepts_advertising']) ? (int)$body['parent_accepts_advertising'] : 0,
            'attendees' => $body['asistentes'] ?? null,
            'company_name' => $body['empresa'] ?? null,
            'event_date' => $body['fecha'] ?? null,
            'utm_medium' => $body['utm_medium'] ?? null,
            'utm_campaign' => $body['utm_campaign'] ?? null,
            'utm_source' => $body['utm_source'] ?? null
        ];
    }

    private function generate_email_template(array $body, string $resume_url = ""): array
    {
        $form = $body['form'] ?? '';
        $name = trim(($body['name'] ?? '') . ' ' . ($body['lastname'] ?? ''));
        $email = $body['email'] ?? '';
        $phone = $body['phone'] ?? '';
        $utm_medium = $body['utm_medium'] ?? '';
        $utm_campaign = $body['utm_campaign'] ?? '';
        $utm_source = $body['utm_source'] ?? '';
        $more18 = $body['more18'] ?? 0;

        $templates = [
            'event' => [
                'subject' => 'Nuevo Lead - Evento',
                'title' => 'Nuevo registro en la web: Eventos',
                'email_slug' => 'to_event_email',
                'fields' => function() use ($body, $name, $email, $phone, $utm_medium, $utm_campaign, $utm_source) {
                    $fields = [
                        'Origen' => 'Eventos',
                        'Nombre:' => $name,
                        'Correo:' => $email,
                        'Teléfono:' => $phone,
                        'Mensaje:' => $body['message'] ?? '',
                        'Asistentes:' => $body['asistentes'] ?? '',
                        'Empresa:' => $body['empresa'] ?? '',
                        'Fecha:' => isset($body['fecha']) ? date('d-m-Y', strtotime($body['fecha'])) : '',
                    ];
                    if ($utm_medium) $fields['UTM Medium:'] = $utm_medium;
                    if ($utm_campaign) $fields['UTM Campaign:'] = $utm_campaign;
                    if ($utm_source) $fields['UTM Source:'] = $utm_source;
                    return array_filter($fields, fn($val) => $val !== '');
                }
            ],
            'contact-us' => [
                'subject' => 'Nuevo Lead - Contáctanos',
                'title' => 'Nuevo registro en la web: Contáctanos',
                'email_slug' => 'to_contact_email',
                'fields' => function() use ($body, $name, $email, $phone, $utm_medium, $utm_campaign, $utm_source) {
                    $fields = [
                        'Origen:' => 'Contáctanos',
                        'Nombre:' => $name,
                        'Correo:' => $email,
                        'Teléfono:' => $phone,
                        'Mensaje:' => $body['message'] ?? '',
                    ];
                    if ($utm_medium) $fields['UTM Medium:'] = $utm_medium;
                    if ($utm_campaign) $fields['UTM Campaign:'] = $utm_campaign;
                    if ($utm_source) $fields['UTM Source:'] = $utm_source;
                    return array_filter($fields, fn($val) => $val !== '');
                }
            ],
            'work-with-us' => [
                'subject' => 'Nuevo Lead - Trabaja con nosotros',
                'title' => 'Nuevo registro en la web: Trabaja con nosotros',
                'email_slug' => 'to_work_email',
                'fields' => function() use ($body, $name, $email, $phone, $utm_medium, $utm_campaign, $utm_source, $resume_url) {
                    $fields = [
                        'Origen:' => 'Trabaja con nosotros',
                        'Nombre:' => $name,
                        'Correo:' => $email,
                        'Teléfono:' => $phone,
                        'Departamento:' => $body['department'] ?? '',
                        'Provincia:' => $body['province'] ?? '',
                        'Distrito:' => $body['district'] ?? '',
                        'Dirección:' => $body['address'] ?? '',
                        'Nacionalidad:' => $body['nationality'] ?? '',
                        'Área:' => $body['area'] ?? '',
                        'URL de CV:' => $resume_url,
                    ];
                    if ($utm_medium) $fields['UTM Medium:'] = $utm_medium;
                    if ($utm_campaign) $fields['UTM Campaign:'] = $utm_campaign;
                    if ($utm_source) $fields['UTM Source:'] = $utm_source;
                    return array_filter($fields, fn($val) => $val !== '');
                }
            ],
            'subscription' => [
                'subject' => 'Nuevo Lead - Suscripción',
                'title' => 'Nuevo registro en la web: Suscripción / Newsletter',
                'email_slug' => 'to_footer_email',
                'fields' => function() use ($body, $name, $email, $utm_medium, $utm_campaign, $utm_source, $more18) {
                    $fields = [
                        'Origen:' => 'Newsletter',
                        'Nombre:' => $name,
                        'Correo:' => $email,
                    ];
                    if ($more18 == 1 || !empty($body['parent_name'])) {
                        $fields['Tutor: Nombre'] = $body['parent_name'] ?? '';
                        $fields['Parentesco:'] = $body['parent_title'] ?? '';
                        $fields['Parent DNI:'] = $body['parent_dni'] ?? '';
                        $fields['Tutor: Teléfono'] = $body['parent_phone'] ?? '';
                        $fields['Parent Dirección:'] = $body['parent_address'] ?? '';
                    }
                    if ($utm_medium) $fields['UTM Medium:'] = $utm_medium;
                    if ($utm_campaign) $fields['UTM Campaign:'] = $utm_campaign;
                    if ($utm_source) $fields['UTM Source:'] = $utm_source;
                    return array_filter($fields, fn($val) => $val !== '');
                }
            ],
            'default' => [
                'subject' => 'Nuevo Lead Creado',
                'title' => 'Nuevo registro en la web: Formulario',
                'email_slug' => 'to_email',
                'fields' => function() use ($form, $name, $email, $phone, $body, $utm_medium, $utm_campaign, $utm_source) {
                    $fields = [
                        'Origen:' => $form ?: 'General',
                        'Nombre:' => $name,
                        'Correo:' => $email,
                        'Teléfono:' => $phone,
                        'Mensaje:' => $body['message'] ?? '',
                    ];
                    if ($utm_medium) $fields['UTM Medium:'] = $utm_medium;
                    if ($utm_campaign) $fields['UTM Campaign:'] = $utm_campaign;
                    if ($utm_source) $fields['UTM Source:'] = $utm_source;
                    return array_filter($fields, fn($val) => $val !== '');
                }
            ]
        ];

        $template_config = $templates[$form] ?? $templates['default'];
        $subject = $template_config['subject'];
        $title   = $template_config['title'];
        $to      = $this->get_email_by_slug($template_config['email_slug']);
        $fields  = $template_config['fields']();

        $content = $this->build_html_table_template($title, $fields);

        return [$content, $to, $subject];
    }

    private function build_html_table_template(string $title, array $fields): string
    {
        $rows_html = '';
        foreach ($fields as $label => $value) {
            $label_clean = esc_html(rtrim($label, ':') . ':');
            $val_html = '';

            if (filter_var($value, FILTER_VALIDATE_URL)) {
                $val_html = '<a href="' . esc_url($value) . '" target="_blank">' . esc_html($value) . '</a>';
            } elseif (filter_var($value, FILTER_VALIDATE_EMAIL)) {
                $val_html = '<a href="mailto:' . esc_attr($value) . '">' . esc_html($value) . '</a>';
            } else {
                $val_html = esc_html($value);
            }

            $rows_html .= "
            <tr>
                <th style=\"width: 35%; font-weight: bold; color: #111827; background-color: #ffffff; padding: 12px 16px; border: 1px solid #e5e7eb; text-align: left; vertical-align: middle;\">{$label_clean}</th>
                <td style=\"color: #374151; padding: 12px 16px; border: 1px solid #e5e7eb; text-align: left; vertical-align: middle;\">{$val_html}</td>
            </tr>";
        }

        return "<!DOCTYPE html>
<html>
<head>
    <meta charset=\"utf-8\">
    <meta name=\"viewport\" content=\"width=device-width, initial-scale=1.0\">
</head>
<body style=\"font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; background-color: #ffffff; color: #374151; margin: 0; padding: 20px; -webkit-font-smoothing: antialiased;\">
    <div style=\"max-width: 650px; margin: 0 auto; background-color: #ffffff; padding: 10px 0;\">
        <h2 style=\"color: #0073aa; font-size: 22px; font-weight: 700; margin-top: 0; margin-bottom: 12px;\">" . esc_html($title) . "</h2>
        <p style=\"color: #6b7280; font-size: 14px; margin-top: 0; margin-bottom: 24px;\">Se ha recibido un nuevo envío de formulario desde el sitio web.</p>
        
        <table style=\"width: 100%; border-collapse: collapse; margin-bottom: 24px; font-size: 14px;\">
            <tbody>
                {$rows_html}
            </tbody>
        </table>
        
        <p style=\"font-size: 12px; color: #9ca3af; margin-top: 24px; margin-bottom: 0;\">Este correo fue enviado automáticamente por el sistema.</p>
    </div>
</body>
</html>";
    }

    private function get_email_by_slug(string $slug): string
    {
        global $wpdb;

        return (string) $wpdb->get_var($wpdb->prepare(
            "SELECT value FROM whiz_api_options WHERE slug = %s",
            $slug
        ));
    }

    public function upload_file(\WP_REST_Request $request): array
    {
        $files = $request->get_file_params();
        
        if (!isset($files['documento'])) {
            return [
                'success' => false,
                'error' => 'No se ha recibido ningún archivo.'
            ];
        }

        $attachment_id = media_handle_upload('documento', 0);

        if (is_wp_error($attachment_id)) {
            return [
                'success' => false,
                'error' => $attachment_id->get_error_message()
            ];
        }

        return [
            'success' => true,
            'attachment_id' => $attachment_id,
            'url' => wp_get_attachment_url($attachment_id)
        ];
    }

    public function save_settings(\WP_REST_Request $request): array
    {
        $body = $request->get_json_params();

        if (empty($body['options'])) {
            return [
                'status' => false,
                'message' => 'El parámetro "options" es requerido'
            ];
        }

        $results = [];
        foreach ($body['options'] as $option) {
            $results[] = WhizApiConfigModel::set_option($option['slug'], $option['value']);
        }

        return [
            'status' => true,
            'message' => 'Configuración actualizada',
            'data' => $results
        ];
    }

    public function get_lead_details(\WP_REST_Request $request): WP_REST_Response
    {
        $lead_id = $request->get_param('id');
        $lead = WhizSimpleLeadModel::get_by_id($lead_id);
        
        if ($lead) {
            return new WP_REST_Response([
                'status' => true,
                'data' => ['lead' => $lead]
            ], 200);
        }
        
        return new WP_REST_Response([
            'status' => false,
            'message' => 'Lead no encontrado'
        ], 404);
    }

    public function api_json(\WP_REST_Request $request): WP_REST_Response
    {
        try {
            $expected_token = (string) WhizApiConfigModel::get_option_value('export_api_token');
            
            if (!empty($expected_token)) {
                $auth_header = $request->get_header('authorization') ?? '';
                $provided_token = '';
                
                if (preg_match('/Bearer\s+(.*)$/i', $auth_header, $matches)) {
                    $provided_token = trim($matches[1]);
                } else {
                    $provided_token = trim($request->get_param('token') ?? '');
                }

                if (empty($provided_token) || !hash_equals($expected_token, $provided_token)) {
                    return new WP_REST_Response([
                        'status' => false,
                        'message' => 'Acceso denegado: Token de seguridad no válido o ausente'
                    ], 401);
                }
            }

            $params = $request->get_params();
            $page = absint($params['page'] ?? 1);
            $per_page = absint($params['per_page'] ?? 1000);
            
            $data = WhizSimpleLeadModel::get_leads_json($page, $per_page);
            
            if (empty($data['data'])) {
                return new WP_REST_Response([
                    'status' => false,
                    'message' => 'No hay datos para exportar',
                    'data' => []
                ], 404);
            }
            
            return new WP_REST_Response([
                'status' => true,
                'message' => 'Datos exportados correctamente',
                'pagination' => [
                    'current_page' => $page,
                    'per_page' => $per_page,
                    'total_items' => $data['total'] ?? 0
                ],
                'data' => $data['data']
            ], 200);
            
        } catch (\Exception $e) {
            return new WP_REST_Response([
                'status' => false,
                'message' => 'Error al procesar la solicitud',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}