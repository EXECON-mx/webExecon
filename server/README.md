# Backend del formulario — `envia.php`

El formulario de `hablemos-de-tu-operacion.html` hace `POST` a
`https://www.execon.mx/public/envia.php`, el mismo endpoint que usa el sitio
actual de EXECON. Al migrar el sitio al hosting de execon.mx (servidor con PHP),
este archivo debe acompañar al sitio para que el formulario siga funcionando.

## Contrato observado del endpoint

| Campo POST   | Contenido                              | Requerido |
|--------------|----------------------------------------|-----------|
| `name`       | Nombre del contacto                    | sí        |
| `mail`       | Correo electrónico                     | sí        |
| `phone`      | Teléfono                               | no        |
| `comments`   | Mensaje (incluye `Empresa: X` al inicio) | no      |
| `service`    | Servicio de interés (select)           | no        |
| `empresa`    | Enviado pero ignorado por el PHP       | —         |

Respuesta: `302` → `/public/gracias` (página de agradecimiento del sitio).
El front no lee la respuesta — el POST viaja en un iframe oculto.

## Checklist de respaldo (pedir a TI / al proveedor de hosting)

- [ ] `public/envia.php` — **crítico**, único backend del sitio
- [ ] `.htaccess` o `web.config` — hay rutas sin extensión (`/public/contacto`
      responde 200 pero `/public/contacto.php` da 404 → existe rewrite config)
- [ ] `public/gracias` — página post-submit (código fuente, no accesible por HTTP)
- [ ] Includes/config que `envia.php` requiera (credenciales SMTP, etc.)
- [ ] Cualquier otro `.php` bajo `/public/` que TI identifique

> El formulario de "Bolsa de Trabajo" del sitio viejo (`#bolsaTrabajo`) está
> vacío/roto desde el servidor — no hay nada funcional que rescatar ahí.

## Pasos de migración

1. Subir los archivos del repo al hosting (webroot o `/public/`).
2. Copiar `envia.php` respaldado a la ruta que use el form
   (hoy: `/public/envia.php`; la URL absoluta en el form ya apunta a
   `www.execon.mx`, así que funciona idéntico antes y después de la migración).
3. Probar un envío real y confirmar llegada del correo.
4. Opcional: cambiar el `action` del form a ruta relativa `/public/envia.php`
   para desacoplar del dominio.

## Config a cuidar

- **PHP**: el servidor corre 7.4.30 (EOL). Recomendar a TI subir a ≥8.1.
  `envia.reference.php` es compatible con 7.4 y 8.x.
- **Correo**: confirmar el buzón destino y si el hosting requiere SMTP
  autenticado (si `mail()` no funciona, usar PHPMailer).
- **Cloudflare**: el POST cross-origin actual ya funciona; post-migración será
  mismo dominio, aún más simple. No mover reglas de firewall.
- **Rollback**: este repo (GitHub) es el respaldo del front. El PHP vive aquí
  en `server/` — si el hosting permite, déjalo fuera del webroot público.

## `envia.reference.php`

Implementación de referencia fiel al comportamiento observado, por si el
archivo original no se puede recuperar. **Requiere completar** `$destinatario`
y la config de correo con TI antes de producción.
