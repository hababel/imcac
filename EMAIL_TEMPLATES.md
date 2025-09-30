# Manual de Estructura de Plantillas de Correo

Este documento explica cómo está organizada la estructura de las plantillas de correo electrónico en la aplicación IMCAC. El objetivo es mantener un diseño uniforme y profesional en todas las comunicaciones enviadas desde la plataforma.

## 1. Estructura de Carpetas

Todas las plantillas y partes de correos se encuentran centralizadas en la siguiente carpeta:

```
app/views/emails/
```

Dentro de esta carpeta, la estructura es la siguiente:

```
/app/views/emails/
├── partials/
│   ├── header.php  (Encabezado fijo para todos los correos)
│   └── footer.php  (Pie de página fijo para todos los correos)
│
├── access_code.php         (Cuerpo del correo para el código de acceso 2FA)
└── invitation_default.php  (Cuerpo por defecto para las invitaciones a diagnósticos)
```

## 2. Cómo Funciona

Cualquier correo enviado por el sistema se construye uniendo tres partes:

1.  **Encabezado (`partials/header.php`):** Contiene el logo y la cabecera de la marca. No es editable por los usuarios.
2.  **Cuerpo del Mensaje:** Es el contenido específico del correo (ej. `access_code.php`). Este es el único archivo que puede ser personalizado (como en el caso de las invitaciones).
3.  **Pie de Página (`partials/footer.php`):** Contiene la información de contacto, legal y de redes sociales. No es editable por los usuarios.

## 3. Ejemplo de Uso en un Controlador

Para enviar un correo, se deben cargar estas tres partes y unirlas:

```php
// 1. Cargar el encabezado y pie de página (siempre los mismos)
$header = file_get_contents(__DIR__ . '/../views/emails/partials/header.php');
$footer = file_get_contents(__DIR__ . '/../views/emails/partials/footer.php');

// 2. Cargar el cuerpo específico del correo
$bodyTemplate = file_get_contents(__DIR__ . '/../views/emails/access_code.php');

// 3. Reemplazar variables y unir todo
$bodyTemplate = str_replace('{{variable}}', $valor, $bodyTemplate);
$fullHtml = $header . $bodyTemplate . $footer;

// 4. Enviar con el Mailer
(new Mailer())->send($destinatario, $asunto, $fullHtml);
```