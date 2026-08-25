<div class="wrap">
    <div id="leads-header-title" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
        <h1>Leads</h1>
        <div>
            <a href="<?php echo esc_url(home_url('/wp-json/whiz-api/v1/lead/download-csv')); ?>" class="button button-primary">
                Descargar CSV
            </a>
        </div>
    </div>

    <div class="table-wrap" style="overflow-x: auto;">
        <table class="wp-list-table widefat fixed striped table-view-list">
            <thead>
                <tr>
                    <th>Nombres</th>
                    <th>Email</th>
                    <th>Teléfono</th>
                    <th>Departamento</th>
                    <th>Provincia</th>
                    <th>Distrito</th>
                    <th>Nacionalidad</th>
                    <th>UTM Source</th>
                    <th>UTM Medium</th>
                    <th>UTM Campaign</th>
                    <th>Fecha Registro</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($data['leads'])): ?>
                    <tr>
                        <td colspan="9" style="text-align: center;">No hay leads registrados</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($data['leads'] as $lead): ?>
                        <tr>
                            <td><?= esc_html($lead->name . ' ' . $lead->lastname) ?></td>
                            <td><?= esc_html($lead->email) ?></td>
                            <td><?= esc_html($lead->phone) ?></td>
                            <td><?= esc_html($lead->department) ?></td>
                            <td><?= esc_html($lead->province) ?></td>
                            <td><?= esc_html($lead->district) ?></td>
                            <td><?= esc_html($lead->nationality) ?></td>
                            <td><?= esc_html($lead->utm_source) ?></td>
                            <td><?= esc_html($lead->utm_medium) ?></td>
                            <td><?= esc_html($lead->utm_campaign) ?></td>
                            <td><?= date('d/m/Y', strtotime($lead->created_at ?? 'now')) ?></td>
                            <td>
                                <button onclick="showMoreDataFrom(this)" 
                                        class="button" 
                                        data-more-target="<?= $lead->id ?>">
                                    Ver Más
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
        
        <div class="tablenav bottom" style="margin-top: 20px;">
            <div class="tablenav-pages">
                <span class="displaying-num"><?= $data['total_leads'] ?> items</span>
                <span class="pagination-links">
                    <a class="first-page button <?= $data['page'] <= 1 ? 'disabled' : '' ?>" 
                       href="admin.php?page=whiz_api_panel_general_leads&pagenum=1">
                        <span class="screen-reader-text">Primera página</span>
                        <span aria-hidden="true">«</span>
                    </a>
                    <a class="prev-page button <?= $data['page'] <= 1 ? 'disabled' : '' ?>" 
                       href="admin.php?page=whiz_api_panel_general_leads&pagenum=<?= $data['page'] - 1 ?>">
                        <span class="screen-reader-text">Página anterior</span>
                        <span aria-hidden="true">‹</span>
                    </a>
                    <span class="screen-reader-text">Página actual</span>
                    <span id="table-paging" class="paging-input">
                        <span class="tablenav-paging-text"><?= $data['page'] ?> de <span class="total-pages"><?= $data['total_pages'] ?></span></span>
                    </span>
                    <a class="next-page button <?= $data['page'] >= $data['total_pages'] ? 'disabled' : '' ?>" 
                       href="admin.php?page=whiz_api_panel_general_leads&pagenum=<?= $data['page'] + 1 ?>">
                        <span class="screen-reader-text">Siguiente página</span>
                        <span aria-hidden="true">›</span>
                    </a>
                    <a class="last-page button <?= $data['page'] >= $data['total_pages'] ? 'disabled' : '' ?>" 
                       href="admin.php?page=whiz_api_panel_general_leads&pagenum=<?= $data['total_pages'] ?>">
                        <span class="screen-reader-text">Última página</span>
                        <span aria-hidden="true">»</span>
                    </a>
                </span>
            </div>
        </div>
    </div>
</div>

<!-- Modal para detalles completos -->
<div id="lead-details-modal" class="modal" style="display:none;">
    <div class="modal-content">
        <div class="modal-header">
            <h2>Detalles completos del Lead</h2>
            <span class="close">&times;</span>
        </div>
        <div class="modal-body" id="lead-details-content">
            <!-- Los detalles se cargarán aquí via AJAX -->
        </div>
    </div>
</div>

<style>
    .modal {
        display: none;
        position: fixed;
        z-index: 9999;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        overflow: auto;
        background-color: rgba(0,0,0,0.4);
    }
    
    .modal-content {
        background-color: #fefefe;
        margin: 5% auto;
        padding: 20px;
        border: 1px solid #888;
        width: 80%;
        max-width: 900px;
    }
    
    .close {
        color: #aaa;
        float: right;
        font-size: 28px;
        font-weight: bold;
        cursor: pointer;
    }
    
    .close:hover {
        color: black;
    }
    
    .modal-header {
        padding: 10px 0;
        border-bottom: 1px solid #ddd;
        margin-bottom: 15px;
    }
    
    .table-wrap {
        margin-top: 20px;
    }
    
    .disabled {
        opacity: 0.5;
        pointer-events: none;
    }
    
    .details-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 15px;
    }
    
    .details-section {
        margin-bottom: 20px;
    }
    
    .details-section h3 {
        border-bottom: 1px solid #eee;
        padding-bottom: 5px;
    }
</style>

<script>
async function showMoreDataFrom(button) {
    const leadId = button.getAttribute('data-more-target');
    
    // Mostrar modal de carga
    const modal = document.getElementById('lead-details-modal');
    const modalContent = document.getElementById('lead-details-content');
    modalContent.innerHTML = '<p>Cargando detalles...</p>';
    modal.style.display = 'block';
    
    // Cerrar modal al hacer clic en la X
    document.querySelector('.close').onclick = function() {
        modal.style.display = 'none';
    }
    
    // Cerrar modal al hacer clic fuera
    window.onclick = function(event) {
        if (event.target == modal) {
            modal.style.display = 'none';
        }
    }
    
    // Fetch lead details via AJAX
   await fetch(`/wp-json/whiz-api/v1/lead/${leadId}`)
    .then(response => response.json())
    .then(data => {
        if (data.status) {
            const lead = data.data.lead;
            modalContent.innerHTML = `
                <div class="details-grid">
                    <div class="details-section">
                        <h3>Información Básica</h3>
                        <p><strong>Nombre:</strong> ${lead.name}</p>
                        <p><strong>Apellido:</strong> ${lead.last_name || ''}</p>
                        <p><strong>Email:</strong> ${lead.email || ''}</p>
                        <p><strong>Teléfono:</strong> ${lead.phone || ''}</p>
                        <p><strong>Tipo Documento:</strong> ${lead.doc_type || ''}</p>
                        <p><strong>Número Documento:</strong> ${lead.doc_number || ''}</p>
                        <p><strong>Dirección:</strong> ${lead.address || ''}</p>
                    </div>
                    
                    <div class="details-section">
                        <h3>Ubigeo</h3>
                        <p><strong>Departamento:</strong> ${lead.department || ''}</p>
                        <p><strong>Provincia:</strong> ${lead.province || ''}</p>
                        <p><strong>Distrito:</strong> ${lead.district || ''}</p>
                        <p><strong>Nacionalidad:</strong> ${lead.nationality || ''}</p>
                    </div>
                    
                    <div class="details-section">
                        <h3>Preferencias</h3>
                        <p><strong>Área de interés:</strong> ${lead.area || ''}</p>
                        <p><strong>Acepta publicidad:</strong> ${lead.accepts_advertising ? 'Sí' : 'No'}</p>
                        <p><strong>Suscrito:</strong> ${lead.subscribed ? 'Sí' : 'No'}</p>
                        <p><strong>Mayor de 18 años:</strong> ${lead.more18 ? 'Sí' : 'No'}</p>
                        ${lead.message ? `<p><strong>Mensaje:</strong> ${lead.message}</p>` : ''}
                    </div>
                    
                    <div class="details-section">
                        <h3>Datos de Marketing</h3>
                        <p><strong>UTM Source:</strong> ${lead.utm_source || 'N/A'}</p>
                        <p><strong>UTM Medium:</strong> ${lead.utm_medium || 'N/A'}</p>
                        <p><strong>UTM Campaign:</strong> ${lead.utm_campaign || 'N/A'}</p>
                        <p><strong>Fecha de registro:</strong> ${new Date(lead.created_at).toLocaleString()}</p>
                    </div>
                    
                    ${lead.resume ? `
                    <div class="details-section">
                        <h3>Archivos</h3>
                        <p><strong>Currículum:</strong> <a href="${lead.resume}" target="_blank">Descargar</a></p>
                    </div>
                    ` : ''}
                    
                    ${lead.parent_name ? `
                    <div class="details-section">
                        <h3>Información del Padre/Tutor</h3>
                        <p><strong>Nombre:</strong> ${lead.parent_name || ''}</p>
                        <p><strong>Teléfono:</strong> ${lead.parent_phone || ''}</p>
                        <p><strong>DNI:</strong> ${lead.parent_dni || ''}</p>
                        <p><strong>Dirección:</strong> ${lead.parent_address || ''}</p>
                        <p><strong>Título:</strong> ${lead.parent_title || ''}</p>
                        <p><strong>Acepta publicidad:</strong> ${lead.parent_accepts_advertising ? 'Sí' : 'No'}</p>
                    </div>
                    ` : ''}
                    
                    ${lead.company_name || lead.event_date || lead.attendees ? `
                    <div class="details-section">
                        <h3>Información de Evento</h3>
                        ${lead.company_name ? `<p><strong>Empresa:</strong> ${lead.company_name}</p>` : ''}
                        ${lead.event_date ? `<p><strong>Fecha de evento:</strong> ${lead.event_date}</p>` : ''}
                        ${lead.attendees ? `<p><strong>Asistentes:</strong> ${lead.attendees}</p>` : ''}
                    </div>
                    ` : ''}
                </div>
            `;
        } else {
            modalContent.innerHTML = '<p>Error al cargar los detalles del lead.</p>';
        }
    })
    .catch(error => {
        modalContent.innerHTML = '<p>Error al conectar con el servidor.</p>';
        console.error('Error:', error);
    });
}
</script>