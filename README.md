# WP Whiz API

**Versión:** 2.0.0  
**Licencia:** GPL v2 o posterior  
**Requisitos mínimos:** WordPress 5.2 | PHP 7.2+  

---

## 📌 Descripción

**WP Whiz API** es un plugin modular para WordPress desarrollado para gestionar el procesamiento, captura y almacenamiento de solicitudes enviadas desde formularios web o aplicaciones frontend (React, Gatsby, Next.js, etc.).

Incluye integración con servicios externos de mensajería para el envío de notificaciones por correo electrónico y expone endpoints seguros de la REST API para la sincronización remota de datos.

---

## 🚀 Funcionalidades Principales

* 📥 **Recepción y Procesamiento de Solicitudes**: Procesa datos provenientes de diferentes formularios web mediante la REST API nativa de WordPress.
* 💾 **Almacenamiento de Datos**: Registra los envíos recibidos localmente en la base de datos de WordPress.
* 📧 **Servicio de Envíos por Correo**: Integración por API REST con proveedores de email transaccional (Resend) para la entrega de notificaciones.
* 🎨 **Plantillas HTML**: Notificaciones por correo electrónico formateadas dinámicamente.
* 🔒 **Endpoints de Exportación Protegidos**: Mecanismo de autenticación mediante Bearer Token para la extracción segura de información hacia herramientas externas o dashboards.
* 📊 **Panel de Configuración**: Interfaz en el Dashboard de WordPress para administrar credenciales y destinatarios.

---

## 🛠️ Requisitos e Instalación

### Requisitos
* WordPress 5.2 o superior.
* PHP 7.2 o superior con extensión `cURL` habilitada.

### Instalación
1. Coloque la carpeta del plugin en el directorio de plugins de WordPress:
   ```bash
   wp-content/plugins/wp-whiz-api-v1
   ```
2. Acceda al panel de administración de WordPress > **Plugins** y active **Whiz Api**.

---

## ⚙️ Configuración

Acceda a la sección de ajustes en el panel lateral del Dashboard (**Whiz > Settings**):

1. **Credenciales de Servicio de Correo**: Ingrese la API Key del proveedor de correos (Resend).
2. **Token de Exportación (Seguridad)**: Configure una clave de seguridad única para autenticar las peticiones a los endpoints de consulta.
3. **Remitente y Destinatarios**: Establezca las direcciones de correo de remitente y de notificación para los administradores.

---

## 🔒 Seguridad y Buenas Prácticas

* **Autenticación en Endpoints de Lectura**: Las consultas a las APIs de extracción requieren el envio de la cabecera `Authorization: Bearer <TOKEN_CONFIGURADO>`.
* **Consultas Preparadas**: El plugin utiliza `$wpdb->prepare()` para la prevención de ataques de inyección SQL.
* **Escapado de Salidas**: Se aplica escapado y sanitización en todas las vistas de administración.

---

## 📋 Historial de Cambios

### 2.0.0
- [x] Actualización del servicio de correo a proveedor REST API (Resend).
- [x] Rediseño de plantillas de correo electrónico.
- [x] Fortalecimiento de seguridad en endpoints de exportación mediante tokens Bearer.
- [x] Sanitización y actualización de consultas SQL con `$wpdb->prepare()`.

### 1.1.0
- [x] Soporte para subida de archivos adjuntos.
- [x] Ajustes en la gestión de registros.

### 1.0.4
- [x] Exportador de registros a formato CSV.
- [x] Derivación de notificaciones según tipo de solicitud.
