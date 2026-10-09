<?php
/**
 * envia.reference.php — IMPLEMENTACIÓN DE REFERENCIA (no es el archivo de producción)
 *
 * Reconstrucción del contrato observado de https://www.execon.mx/public/envia.php
 * (sitio EXECON anterior). Usar solo si TI no puede recuperar el archivo original.
 *
 * Contrato:
 *   POST name, mail, phone, comments, service
 *   → envía correo al equipo → 302 redirect a /public/gracias
 *
 * TODO (completar con TI antes de producción):
 *   - $destinatario: buzón real que recibe los contactos
 *   - Si el hosting requiere SMTP autenticado en vez de mail(), usar PHPMailer
 *   - Confirmar el valor de $redirect según la estructura final de rutas
 */

// ---------- Config ----------
$destinatario = 'info@execon.mx';              // TODO: confirmar con TI
$redirect     = '/public/gracias';             // TODO: ajustar si la ruta cambia
$asunto       = 'Contacto desde el sitio web EXECON';
$hostOrigenes = ['execon.mx', 'www.execon.mx']; // dominios permitidos

// ---------- Solo POST ----------
if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
    header('Location: ' . $redirect, true, 302);
    exit;
}

// ---------- Campos (mismos nombres que el formulario) ----------
$name     = trim(strip_tags($_POST['name']     ?? ''));
$mail     = trim(strip_tags($_POST['mail']     ?? ''));
$phone    = trim(strip_tags($_POST['phone']    ?? ''));
$comments = trim(strip_tags($_POST['comments'] ?? ''));
$service  = trim(strip_tags($_POST['service']  ?? ''));
// 'empresa' llega concatenada dentro de comments desde el front (ver hablemos-de-tu-operacion.html)

if ($name === '' || $mail === '' || !filter_var($mail, FILTER_VALIDATE_EMAIL)) {
    header('Location: ' . $redirect, true, 302);
    exit;
}

// ---------- Cuerpo del correo ----------
$lineas = [
    'Nombre: ' . $name,
    'Correo: ' . $mail,
    'Teléfono: ' . $phone,
    'Servicio de interés: ' . ($service !== '' ? $service : '(no especificado)'),
    '',
    'Mensaje:',
    $comments,
];
$cuerpo = implode("\r\n", $lineas);

$headers = implode("\r\n", [
    'From: sitio-web@' . ($_SERVER['HTTP_HOST'] ?? 'execon.mx'),
    'Reply-To: ' . $mail,
    'Content-Type: text/plain; charset=UTF-8',
    'X-Mailer: PHP/' . PHP_VERSION,
]);

// ---------- Envío ----------
// mail() usa la config del hosting; si no envía, migrar a PHPMailer con SMTP (ver README)
@mail($destinatario, '=?UTF-8?B?' . base64_encode($asunto) . '?=', $cuerpo, $headers);

// ---------- Mismo comportamiento que el original: redirect a gracias ----------
header('Location: ' . $redirect, true, 302);
exit;
