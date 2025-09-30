# Manual de Estructura de Vistas de Diagnóstico

Este documento explica cómo está organizada la estructura de las vistas y previsualizaciones de los diagnósticos en la aplicación. El objetivo es tener un sistema claro y reutilizable para mostrar los formularios en diferentes contextos.

## 1. Tipos de Vistas de Diagnóstico

Existen dos tipos principales de vistas para un diagnóstico:

### a) Vista de Diagnóstico Completa (Interactiva)

Esta es la vista principal que se le presenta a un participante para que complete el diagnóstico. Es una vista interactiva con navegación por pasos.

*   **Archivo:** `app/views/survey/show.php`
*   **Layout:** `app/views/layouts/survey_layout.php` (con estilos específicos)
*   **Características:**
    *   Muestra los datos automáticos del participante (nombre, email, equipo).
    *   Muestra los **campos globales** activos.
    *   Presenta las preguntas del formulario agrupadas en pasos.
    *   Incluye navegación "Siguiente" y "Anterior".
*   **Uso:**
    1.  **Diagnóstico del Participante:** Cuando un invitado accede con su token (`/survey/start`). Controlado por `SurveyController@start`.
    2.  **Previsualización General (Admin):** Cuando el superadministrador previsualiza el flujo completo desde "Configuración" (`/admin/settings/preview/...`). Controlado por `AdminController@previewFullSurvey`.

### b) Vista de Contenido del Formulario (Estática)

Esta es una vista simplificada que solo muestra el contenido de un formulario (grupos, preguntas y respuestas) de manera estática y no interactiva.

*   **Archivo:** `app/views/forms/preview.php`
*   **Layout:** `app/views/layouts/layout.php` (layout principal de la aplicación)
*   **Características:**
    *   **No** muestra campos globales ni datos de participante.
    *   **No** tiene navegación por pasos.
    *   Muestra todas las preguntas en una sola página.
    *   Los campos de respuesta están deshabilitados (`disabled`).
*   **Uso:**
    1.  **Previsualización en el Editor:** Cuando un administrador hace clic en "Previsualizar" dentro del "Gestor de Diagnósticos" (`/admin/forms/preview/...`). Controlado por `FormController@preview`.

## 2. Flujo de Controladores

*   `SurveyController`: Se encarga exclusivamente de la lógica para que un participante real responda el diagnóstico. Utiliza la **Vista de Diagnóstico Completa**.
*   `AdminController`: En su función `previewFullSurvey`, simula los datos de un participante y reutiliza la **Vista de Diagnóstico Completa** para ofrecer una previsualización 100% fiel.
*   `FormController`: En su función `preview`, se enfoca en mostrar solo el contenido del formulario que se está editando, utilizando la **Vista de Contenido del Formulario**.

Esta separación asegura que cada controlador tenga una responsabilidad clara y que las vistas sean consistentes y reutilizables, evitando la duplicación de código y facilitando el mantenimiento.