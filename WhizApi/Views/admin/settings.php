<?php
    if ( ! defined( 'ABSPATH' ) ) {
        exit;
    }
    ?>
    <h1 class="wp-heading-inline">
        Settings
    </h1>
    <form id="wz_conf_form" action="">
        <h2 class="wp-heading-inline">
            Configuraciones de email
        </h2>
        <table class="form-table" role="presentation">
            <tr class="builds_api_webhook">
                <th scope="row">
                    <label for="whiz_settings[admin_name]">
                        Project Name
                    </label>
                </th>
                <td>
                    <input type="text" placeholder="your-incredible-project" value="<?= $data['project_name'] ?>"
                           id="whiz_settings[admin_name]" name="whiz_settings[admin_name]">
                    <p class="description">

                    </p>
                </td>
            </tr>
            <tr class="builds_api_webhook">
                <th scope="row">
                    <label for="whiz_settings[resend_api_key]">
                        Resend API Key
                    </label>
                </th>
                <td>
                    <input type="password" placeholder="re_123456789..." value="<?= esc_attr($data['resend_api_key'] ?? '') ?>"
                           id="whiz_settings[resend_api_key]" name="whiz_settings[resend_api_key]" class="regular-text">
                    <p class="description">API Key de Resend (obtenida desde tu dashboard en resend.com)</p>
                </td>
            </tr>
            <tr class="builds_api_webhook">
                <th scope="row">
                    <label for="whiz_settings[export_api_token]">
                        Export API Token (Seguridad Leads)
                    </label>
                </th>
                <td>
                    <input type="text" placeholder="Ej: whiz_sec_8a7f9b..." value="<?= esc_attr($data['export_api_token'] ?? '') ?>"
                           id="whiz_settings[export_api_token]" name="whiz_settings[export_api_token]" class="regular-text">
                    <p class="description">Token secreto para proteger la exportación de leads JSON (Google Sheets).</p>
                </td>
            </tr>
            <tr class="builds_api_webhook">
                <th scope="row">
                    <label for="whiz_settings[sender_email]">
                        Email From
                    </label>
                </th>
                <td>
                    <input type="text" placeholder="mail@tu-dominio.com" value="<?= esc_attr($data['sender_email'] ?? '') ?>"
                           id="whiz_settings[sender_email]" name="whiz_settings[sender_email]">
                    <p class="description">Debe pertenecer a un dominio verificado en Resend.</p>
                </td>
            </tr>
            <tr class="builds_api_webhook">
                <th scope="row">
                    <label for="whiz_settings[sender_name]">
                        Name From
                    </label>
                </th>
                <td>
                    <input type="text" placeholder="Astrid y Gastón" value="<?= esc_attr($data['sender_name'] ?? '') ?>"
                           id="whiz_settings[sender_name]" name="whiz_settings[sender_name]">
                    <p class="description">
                    </p>
                </td>
            </tr>
            <tr class="builds_api_webhook">
                <th scope="row">
                    <label for="whiz_settings[to_email]">Email To</label>
                </th>
                <td>
                    <input type="text" placeholder="admin@tu-dominio.com" value="<?= esc_attr($data['to_email'] ?? '') ?>"
                        id="whiz_settings[to_email]" name="whiz_settings[to_email]">
                    <p class="description"></p>
                </td>
            </tr>
            <tr class="builds_api_webhook">
                <th scope="row">
                    <label for="whiz_settings[to_event_email]">Event Email To</label>
                </th>
                <td>
                    <input type="text" placeholder="eventos@tu-dominio.com" value="<?= esc_attr($data['to_event_email'] ?? '') ?>"
                        id="whiz_settings[to_event_email]" name="whiz_settings[to_event_email]">
                    <p class="description"></p>
                </td>
            </tr>
            <tr class="builds_api_webhook">
                <th scope="row">
                    <label for="whiz_settings[to_contact_email]">Contact Email To</label>
                </th>
                <td>
                    <input type="text" placeholder="contacto@tu-dominio.com" value="<?= esc_attr($data['to_contact_email'] ?? '') ?>"
                        id="whiz_settings[to_contact_email]" name="whiz_settings[to_contact_email]">
                    <p class="description"></p>
                </td>
            </tr>
            <tr class="builds_api_webhook">
                <th scope="row">
                    <label for="whiz_settings[to_work_email]">Work With Us Email To</label>
                </th>
                <td>
                    <input type="text" placeholder="rrhh@tu-dominio.com" value="<?= esc_attr($data['to_work_email'] ?? '') ?>"
                        id="whiz_settings[to_work_email]" name="whiz_settings[to_work_email]">
                    <p class="description"></p>
                </td>
            </tr>
            <tr class="builds_api_webhook">
                <th scope="row">
                    <label for="whiz_settings[to_footer_email]">Suscription Email To</label>
                </th>
                <td>
                    <input type="text" placeholder="suscripciones@tu-dominio.com" value="<?= esc_attr($data['to_footer_email'] ?? '') ?>"
                        id="whiz_settings[to_footer_email]" name="whiz_settings[to_footer_email]">
                    <p class="description"></p>
                </td>
            </tr>
        </table>
        <button id="send_conf" class="button pr principal">
            Guardar
        </button>
    </form>

    <script>
        // variables
        const $sendConfButton = document.getElementById('send_conf');
        const $confForm = document.getElementById('wz_conf_form');
        const $messageBox = document.getElementById('message-box');

        // Functions
        window.customAlert = (message, type = 'normal') => {
            $messageBox.classList.add('active');
            $messageBox.innerText = message;
        }

        window.sendConfig = () => {
            let form = new FormData($confForm);
            let payload = {
                options: [
                    { slug: 'project_name', value: form.get('whiz_settings[admin_name]') },
                    { slug: 'resend_api_key', value: form.get('whiz_settings[resend_api_key]') },
                    { slug: 'export_api_token', value: form.get('whiz_settings[export_api_token]') },
                    { slug: 'sender_email', value: form.get('whiz_settings[sender_email]') },
                    { slug: 'sender_name', value: form.get('whiz_settings[sender_name]') },
                    { slug: 'to_email', value: form.get('whiz_settings[to_email]') },
                    { slug: 'to_event_email', value: form.get('whiz_settings[to_event_email]') },
                    { slug: 'to_contact_email', value: form.get('whiz_settings[to_contact_email]') },
                    { slug: 'to_work_email', value: form.get('whiz_settings[to_work_email]') },
                    { slug: 'to_footer_email', value: form.get('whiz_settings[to_footer_email]') },
                ]
            };
            console.log(payload);
            fetch('/wp-json/whiz-api/v1/settings/', {
                method: 'POST',
                body: JSON.stringify(payload),
                headers: {
                    'Content-Type': 'Application/json'
                }
            })
                .then(res => res.json())
                .then(res => {
                    console.log(res);
                    if(res.status){
                        location.reload();
                    }else{
                        console.error('Error updating the configuration');
                    }
                });
        }

        if($sendConfButton){
            $sendConfButton.addEventListener('click', () => {
                $sendConfButton.disabled = true;
                $sendConfButton.classList.add('disabled');
                if($confForm){
                    window.sendConfig();
                }else{
                    console.error('There isn\'t the correct form object');
                }
            });
        }
    </script>
