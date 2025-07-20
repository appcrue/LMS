
Plugin local_webtoken
=====================

Descripción
-----------
Este plugin para Moodle expone las siguientes funcionalidades:

1. **Notas del alumno**  
  Servicio web `local_appcrueservices_get_grades` que devuelve la lista completa de notas de un alumno en función de las restricciones que tenga aplicadas.

2. **Calendario del alumno**  
  Servicio web `local_appcrueservices_get_calendar` que devuelve la lista completa de los eventos de calendario de un alumno en función de las restricciones que tenga aplicadas.

3. **Foros del alumno**  
   Servicio web `local_appcrueservices_get_forums` que devuelve los foros visibles en los cursos en los que está inscrito el estudiante, junto con las discusiones y mensajes publicados.

Instalación
-----------
1. Copie la carpeta `appcrueservices/` a su instalación de Moodle (dentro de `moodle/local/`).
2. Acceda a la sección `Administración del sitio > Notificaciones` para que Moodle instale y registre el plugin.
3. Configure la API Key:
  - Vaya a `Administración del sitio > Plugins > Plugins locales > Appcrueservices`.
  - Ingrese su API Key en el campo correspondiente y guarde los cambios
4. Configuración de servicios web:
  - Vaya a `Administración del sitio > Plugins > Servicios web > Servicios externos`.
  - Verifique que existe el servicio con shortname `appcrueservices_service` (o el nombre que haya definido).
  - Asegúrese de que la función `local_appcrueservices_get_grades` esté habilitada.
5. Permisos:
  - Asigne la capability `local/appcrueservices:use` al rol `manager` (u otro rol) vía `Administración del sitio > Usuarios > Permisos > Definir roles`.

Uso
---
1. **Obtener notas del alumno**
  Llama al servicio REST de Moodle con el token del usuario genérico que tenga permiso de WS, el email del estudiante y la API Key:

  ```
  GET https://TU_MOODLE_DOMAIN/local/appcrueservices/grades_proxy.php?
    studentemail=EMAIL_ESTUDIANTE
    &apikey=TU_API_KEY
    &moodlewsrestformat=json
  ```

  **Parámetros:**
  - studentemail: Email del alumno a obtener sus notas (requerido)
  - apikey: API Key configurada en el plugin (requerido)
  - moodlewsrestformat: Formato de respuesta (json recomendado)

  **Respuesta exitosa (JSON):**
    ```json
    [
      {
        "courseid": 3,
        "coursename": "2024 - Analisis de datos",
        "itemname": "Total del curso",
        "finalgrade": 11.20002,
        "gradeformatted": "11,20"
      },
      {
        "courseid": 3,
        "coursename": "2024 - Analisis de datos",
        "itemname": "Examen 1 - Aula 1",
        "finalgrade": 5.2885999999999997,
        "gradeformatted": "5,29"
      },
      ...
    ]
    ```

  **Notas**
  - Los parámetros `timestart` y `timeend` son opcionales, si no se proporcionan, se toma por defecto el rango desde ahora hasta 30 días después.

2. **Obtener calendario del alumno**
  Llama al servicio REST de Moodle con el token del usuario genérico que tenga permiso de WS, el email del estudiante y la API Key. Parámetros opcionales son timestart y timeend los cuales si no se ponen se recuperará los próximos 30 días desde la fecha actual.

  ```
  GET https://TU_MOODLE_DOMAIN/local/appcrueservices/calendar_proxy.php?
    studentemail=EMAIL_ESTUDIANTE
    &apikey=TU_API_KEY
    &moodlewsrestformat=json
    &timestart=1751788139
    &timeend=1752133800
  ```

  **Parámetros:**
  - studentemail: Email del alumno a obtener sus notas (requerido)
  - apikey: API Key configurada (requerido)
  - moodlewsrestformat: Formato de respuesta (json recomendado)
  - timestart: Fecha inicio en formato epoch (opcional)
  - timeend: Fecha fin en formato epoch (opcional)

  **Respuesta exitosa (JSON):**
    ```json
    {
      "userid": 50,
      "events": [
          {
              "id": 9,
              "name": "Prueba 1 Calendario",
              "description": "Prueba del plugin Calendario 1.1.00",
              "timestart": 1751791380,
              "timeduration": 82800,
              "timemodified": 1751874233,
              "courseid": 0,
              "coursename": ""
          },
          {
            "id": 10,
            "name": "Prueba 2",
            "description": "",
            "timestart": 1751874240,
            "timeduration": 0,
            "timemodified": 1751874278,
            "courseid": 0,
            "coursename": ""
        }
    ]
    }
   ```

  **Respuesta de error (JSON):**
    ```json
    {
      "error": "Mensaje de error descriptivo"
    }
    ```

3. **Obtener foros, discusiones y mensajes del alumno**
  Llama al servicio REST de Moodle con el token del usuario genérico que tenga permiso de WS, el email del estudiante y la API Key:

  ```
  GET https:/TU_MOODLE_DOMAIN/local/appcrueservices/forums_proxy.php?
    studentemail=EMAIL_ESTUDIANTE
    &apikey=TU_API_KEY
    &moodlewsrestformat=json
  ```

  **Parámetros:**
  - studentemail: Email del alumno a obtener sus notas (requerido)
  - apikey: API Key configurada en el plugin (requerido)
  - moodlewsrestformat: Formato de respuesta (json recomendado)

  **Respuesta exitosa (JSON):**
    ```json
  {
    "forums": [
    {
      "course_title": "Introducción a la Programación",
      "forum_name": "Foro de bienvenida",
      "description": "Este foro es para presentarse y conocerse.",
      "lock_at": "",
      "todo_date": "",
      "html_url": "",
      "topic_title": "Presentaciones",
      "posted_at": 1720000000,
      "unread_count": "3",
      "replies": [
        {
          "id": "101",
          "parent_id": "0",
          "display_name": "Alberto Otero",
          "createdAt": 1720000001,
          "message": "Hola a todos, soy Alberto y me gusta programar.",
          "replies": [
            {
              "id": "102",
              "parent_id": "101",
              "display_name": "Laura Gómez",
              "createdAt": 1720000100,
              "message": "¡Bienvenido Alberto! Yo también estoy empezando.",
              "replies": [
                {
                  "id": "103",
                  "parent_id": "102",
                  "display_name": "Alberto Otero",
                  "createdAt": 1720000200,
                  "message": "Gracias Laura :)",
                  "replies": []
                }
              ]
            },
            {
              "id": "104",
              "parent_id": "101",
              "display_name": "Carlos Ruiz",
              "createdAt": 1720000300,
              "message": "¡Hola Alberto! Qué bueno tenerte aquí.",
              "replies": []
            }
          ]
        }
      ]
    }
  ]
   ```

  **Respuesta de error (JSON):**
    ```json
    {
      "error": "Mensaje de error descriptivo"
    }
    ```


Ejemplo con curl
----------------
# 1. Obtener notas del alumno
curl "https://TU_MOODLE_DOMAIN/local/appcrueservices/grades_proxy.php?studentemail=EMAIL_ESTUDIANTE&apikey=TU_API_KEY"

# 2. Obtener calendarios del alumno
curl "https://TU_MOODLE_DOMAIN/http:/local/appcrueservices/calendar_proxy.php?moodlewsrestformat=json&studentemail=EMAIL_ESTUDIANTE&apikey=TU_API_KEY&timestart=1751788139&timeend=0"

# 3. Obtener forums del alumno
curl "https:/TU_MOODLE_DOMAIN/local/appcrueservices/forums_proxy.php?moodlewsrestformat=json&studentemail=EMAIL_ESTUDIANTE&apikey=TU_API_KEY"
