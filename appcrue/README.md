# AppCRUE services #

Implements services for extracting different types of information for an user identified by a token or an APIkey.

The token needs to be validated and authenticated by a configurable external identity provider (IdP).

It was developed to enable Moodle to publish information for the AppCrue application.

# Functionality #

This local plugin provides the following services following the AppCRUE API:
- usercalendar: reports calendar events for a user. It takes the params fromDate, toDate, token.

```
{
  "calendar": [
    { // day item
      "date": "2020-04-21"
      "events": [
        { // event item
          "id": 1033992237,
          "title": "Tutoring",
          "description": "Tutoring in Mathematics",
          "url": "http://universidad.es/tuperfil/tutorias",
          "nameAuthor": "Autor",
          "imgDetail": "http://test.host/uploads/event/logo/1033992237/example.png",
          "type": "TUTORIA",
          “startsAt”: “1575990139”,
             “endsAt”: “1575990139”
        },
        { "id": 1033992247,
          "title": "Clase",
          "description": "Clase asignatura Inglés",
          "url": "http://universidad.es/tuperfil/tutorias",

          "nameAuthor": "Autor",
          "imgDetail": "http://test.host/uploads/event/logo/1033992237/example.png",
          "type": "HORARIO",
          “startsAt”: “1575990139”,
          “endsAt”: “1575990139”
        }
      ]
    },
    { // day item
      "date": "2020-04-22"
      "events": [
        { "id": 1033945237,
          "title": "Tutoria",
          "description": "Tutoria asignatura Matemáticas”,
          "url": "http://universidad.es/tuperfil/tutorias",
          "nameAuthor": "Autor",
          "imgDetail": "http://test.host/uploads/event/logo/1033992237/example.png",
          "type": "TUTORIA,
          “startsAt”: “157593453”,
          “endsAt”: “157593453”,
        },
        { // event item
          "id": 1033992449,
          "title": "Clase",
          "description": "Clase asignatura Inglés",
          "url": "http://universidad.es/tuperfil/tutorias",
          "nameAuthor": "Autor",
          "imgDetail": "http://test.host/uploads/event/logo/1033992237/example.png",
          "type": "HORARIO,
          “startsAt”: “157593453”,
          “endsAt”: “157593453”,
        }
      ]
    }
  ]
}

```
- autologin: logs-in a user with an external token and redirects him to a deep link. Also can build redirection URLs from a library of configured url patterns and some parameters. It takes the params token, fallback, urltogo, course, group, pattern, param1, param2, param3.
  - token: auth token.
  - pattern: If specified parameters are used to generate the url replacing placeholders in a registered pattern.
  - fallback: Response when if token is absent or invalid. Values "ignore", "error", "logout".
  - urltogo: deep link relative to Moodle site to go after token validation.
  - course: parameter to create urls using patterns in local_appcrue/pattern_lib or finding courses with local_appcrue/course_pattern setting..
  - group: parameter to create urls using patterns in local_appcrue/pattern_lib or finding courses with local_appcrue/course_pattern setting.
  - param1: general purpose parameter to create urls using patterns in local_appcrue/pattern_lib or finding courses with local_appcrue/course_pattern setting.
  - param2: general purpose parameter to create urls using patterns in local_appcrue/pattern_lib or finding courses with local_appcrue/course_pattern setting..
  - param3: general purpose parameter to create urls using patterns in local_appcrue/pattern_lib or finding courses with local_appcrue/course_pattern setting..

- sitemap: generates a JSON representation of the categories and courses. It takes params token, category, includecourses, hiddencats, urlsonlyonends.
  - token: auth token.
  - includecourses: whether to stop in category level or not
  - hiddencats: omit a list of categories from the sitemap in PHP array form parameter. Example: hiddencats[0]=2&hiddencats[1]=34.
  - urlsonlyonends: only last element of each branch has a URL to access it. Otherwise every node has an URL.
```
{
    "navegable": [
        {
            "name": "Miscellaneous",
            "description": "A bit of everything",
            "id": "1",
            "navegable": [
                {
                    "name": "Ingenier\u00eda",
                    "description": "&lt;p dir=&quot;ltr&quot; style=&quot;text-align: left;&quot;&gt;Categoria xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx&lt;\/p&gt;",
                    "id": "3",
                    "url": "https:\/\/XXXXX\/moodle310\/course\/index.php?categoryid=3"
                }
            ]
        },
        {
            "name": "empty",
            "description": "&lt;p&gt;&amp;nbsp;vccc&lt;\/p&gt;",
            "id": "2",
            "url": "https:\/\/XXXXX\/moodle310\/course\/index.php?categoryid=2"
        }
    ]
}
```
- avatar: identifies a user with an external token and returns his avatar picture in raw or base64 format.
- notifygrades web service: receives a webhook from an external academic management system and composes a localized message with the grade and other details and sends it via messaging API (may be routed to AppCRUE as well).
- send_instant_message web service: send a private message for a user via messaging API.

## New LMS integration API
- appcrue_calendar: provides calendar events for a user. It takes the params apikey, studentemail, timestart, timeend.
  - apikey: API key for authentication.
  - studentemail: email of the student to get calendar events for.
  - timestart: start timestamp for the calendar events.
  - timeend: end timestamp for the calendar events.
- appcrue_grades: provides grades for a user. It takes the params apikey, studentemail.
  - apikey: API key for authentication.
  - studentemail: email of the student to get grades for.
- appcrue_forums: provides forum posts for a user. It takes the params apikey, studentemail.
  - apikey: API key for authentication.
  - studentemail: email of the student to get forum posts for.
- appcrue_announcements: provides announcements for a user. It takes the params apikey, studentemail.
  - apikey: API key for authentication.
  - studentemail: email of the student to get announcements for.
- appcrue_files: provides files for a user. It takes the params apikey, studentemail.
  - apikey: API key for authentication.
  - studentemail: email of the student to get files for.

## Web Service ##

Activate the web service following the instructions at: https://[SERVER]/admin/settings.php?section=webservicesoverview
## What is AppCRUE? ##

AppCRUE (https://tic.crue.org/app-crue/) is a mobile App develop by the CRUE (Conference of Rectors of Spanish Universities) and Santander Bank. It is used by 44 spanish universities and more than 150 000 students.

## License ##

This program is free software: you can redistribute it and/or modify it under
the terms of the GNU General Public License as published by the Free Software
Foundation, either version 3 of the License, or (at your option) any later
version.

This program is distributed in the hope that it will be useful, but WITHOUT ANY
WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A
PARTICULAR PURPOSE.  See the GNU General Public License for more details.

You should have received a copy of the GNU General Public License along with
this program.  If not, see <http://www.gnu.org/licenses/>.
