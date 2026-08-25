<?php
namespace WhizApi\Services;

use WhizApi\Models\WhizApiConfigModel;

if (!defined('ABSPATH')) {
    exit;
}

class WhizApiService
{
    private string $api_key;
    private string $api_url = 'https://api.resend.com/emails';

    public function __construct()
    {
        $this->api_key = (string) WhizApiConfigModel::get_option_value('resend_api_key');
    }

    /**
     * Sends an email using the Resend REST API.
     *
     * @param string|array $to Recipient email address or array of addresses.
     * @param string $subject Email subject line.
     * @param string $content Email HTML body content.
     * @return bool True if successfully accepted by Resend, false otherwise.
     */
    public function send_email($to, string $subject, string $content): bool
    {
        if (empty($this->api_key)) {
            error_log('[WhizApiService] Resend API Key no está configurada.');
            return false;
        }

        $from_email = WhizApiConfigModel::get_option_value('sender_email');
        $from_name  = WhizApiConfigModel::get_option_value('sender_name');

        if (empty($from_email)) {
            $from_email = get_option('admin_email');
        }

        if (empty($from_name)) {
            $from_name = get_option('blogname');
        }

        $formatted_from = "{$from_name} <{$from_email}>";

        $recipients = is_array($to) ? array_values($to) : [$to];

        $payload = [
            'from'    => $formatted_from,
            'to'      => $recipients,
            'subject' => $subject,
            'html'    => $content,
        ];

        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_URL            => $this->api_url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING       => '',
            CURLOPT_MAXREDIRS      => 10,
            CURLOPT_TIMEOUT        => 30,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION   => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST  => 'POST',
            CURLOPT_POSTFIELDS     => json_encode($payload),
            CURLOPT_HTTPHEADER     => [
                'Accept: application/json',
                'Content-Type: application/json',
                'Authorization: Bearer ' . trim($this->api_key),
            ],
        ]);

        $response = curl_exec($curl);
        $http_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        $curl_error = curl_error($curl);
        curl_close($curl);

        if (!empty($curl_error)) {
            error_log("[WhizApiService] Error cURL al enviar correo via Resend: {$curl_error}");
            return false;
        }

        if ($http_code >= 200 && $http_code < 300) {
            return true;
        }

        error_log("[WhizApiService] Resend API respondió con código HTTP {$http_code}. Respuesta: {$response}");
        return false;
    }
}
