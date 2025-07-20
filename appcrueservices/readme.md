# Plugin local_appcrueservices

## Description
--------------
This Moodle plugin exposes the following functionalities:

1. **Student Grades**  
   Web service `local_appcrueservices_get_grades` that returns the complete list of a student’s grades based on the applied restrictions.

2. **Student Calendar**  
   Web service `local_appcrueservices_get_calendar` that returns the complete list of a student’s calendar events based on the applied restrictions.

3. **Student Forums**  
   Web service `local_appcrueservices_get_forums` that returns the visible forums in the courses where the student is enrolled, along with discussions and posted messages.

## Installation
---------------
**Requirements:** Moodle 3.7 or higher
1. Copy the `appcrueservices/` folder into your Moodle installation (inside `moodle/local/`).
2. Go to `Site administration > Notifications` so Moodle can install and register the plugin.
3. Configure the API Key:
   - Go to `Site administration > Plugins > Local plugins > Appcrueservices`.
   - Enter your API Key in the corresponding field and save changes.
4. Web service configuration:
   - Go to `Site administration > Plugins > Web services > External services`.
   - Ensure that the service with shortname `appcrueservices_service` exists (or the name you defined).
   - Make sure the function `local_appcrueservices_get_grades` is enabled.
5. Permissions:
   - Assign the capability `local/appcrueservices:use` to the `manager` role (or another role) via `Site administration > Users > Permissions > Define roles`.

## Usage
--------
### 1. **Get student grades**
Call the Moodle REST service using the token of a generic user with WS permission, the student's email, and the API Key:

  ```
  GET https://YOUR_MOODLE_DOMAIN/local/appcrueservices/grades_proxy.php?
    studentemail=EMAIL_ESTUDIANTE
    &apikey=YOUR_API_KEY
    &moodlewsrestformat=json
  ```

  **Parameters:**
  - `studentemail`: Email of the student whose grades are to be retrieved (required)
  - `apikey`: API Key configured in the plugin (required)
  - `moodlewsrestformat`: Response format (json recommended)

  **Successful response (JSON):**
  ```json
  [
    {
      "courseid": 3,
      "coursename": "2024 - Data Analysis",
      "itemname": "Course Total",
      "finalgrade": 11.20002,
      "gradeformatted": "11.20"
    },
    {
      "courseid": 3,
      "coursename": "2024 - Data Analysis",
      "itemname": "Exam 1 - Room 1",
      "finalgrade": 5.2885999999999997,
      "gradeformatted": "5.29"
    },
    ...
  ]
    ```

  **Notes**
  - The parameters `timestart` and `timeend` are optional. If not provided, the default range is from now to 30 days ahead.

2. **Get student calendar**
  Call the Moodle REST service using the token of a generic user with WS permission, the student's email, and the API Key. Optional parameters are timestart and timeend. If omitted, the next 30 days from the current date will be returned.

  ```
  GET https://YOUR_MOODLE_DOMAIN/local/appcrueservices/calendar_proxy.php?
    studentemail=EMAIL_ESTUDIANTE
    &apikey=YOUR_API_KEY
    &moodlewsrestformat=json
    &timestart=1751788139
    &timeend=1752133800
  ```

  **Parameters:**
  - `studentemail`: Email of the student whose grades are to be retrieved (required)
  - `apikey`: API Key configured in the plugin (required)
  - `moodlewsrestformat`: Response format (json recommended)
  - `timestart`: Start date in epoch format (optional)
  - `timeend`: End date in epoch format (optional)

  **Successful response (JSON):**
    ```json
    {
      "userid": 50,
      "events": [
        {
          "id": 9,
          "name": "Test 1 Calendar",
          "description": "Plugin Calendar Test 1.1.00",
          "timestart": 1751791380,
          "timeduration": 82800,
          "timemodified": 1751874233,
          "courseid": 0,
          "coursename": ""
        },
        {
          "id": 10,
          "name": "Test 2",
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

  **Error response (JSON):**
    ```json
    {
      "error": "Descriptive error message"
    }
    ```

3. **Get student forums, discussions, and messages**
  Call the Moodle REST service using the token of a generic user with WS permission, the student's email, and the API Key:

  ```
  GET https:/YOUR_MOODLE_DOMAIN/local/appcrueservices/forums_proxy.php?
    studentemail=EMAIL_ESTUDIANTE
    &apikey=YOUR_API_KEY
    &moodlewsrestformat=json
  ```

  **Parameters:**
  - `studentemail`: Email of the student whose grades are to be retrieved (required)
  - `apikey`: API Key configured in the plugin (required)
  - `moodlewsrestformat`: Response format (json recommended)

  **Successful response (JSON):**
    ```json
  {
    "forums": [
      {
        "course_title": "Introduction to Programming",
        "forum_name": "Welcome Forum",
        "description": "This forum is for introductions and getting to know each other.",
        "lock_at": "",
        "todo_date": "",
        "html_url": "",
        "topic_title": "Introductions",
        "posted_at": 1720000000,
        "unread_count": "3",
        "replies": [
          {
            "id": "101",
            "parent_id": "0",
            "display_name": "Alberto Otero",
            "createdAt": 1720000001,
            "message": "Hi everyone, I'm Alberto and I like programming.",
            "replies": [
              {
                "id": "102",
                "parent_id": "101",
                "display_name": "Laura Gómez",
                "createdAt": 1720000100,
                "message": "Welcome Alberto! I'm just getting started too.",
                "replies": [
                  {
                    "id": "103",
                    "parent_id": "102",
                    "display_name": "Alberto Otero",
                    "createdAt": 1720000200,
                    "message": "Thanks Laura :)",
                    "replies": []
                  }
                ]
              },
              {
                "id": "104",
                "parent_id": "101",
                "display_name": "Carlos Ruiz",
                "createdAt": 1720000300,
                "message": "Hi Alberto! Great to have you here.",
                "replies": []
              }
            ]
          }
        ]
      }
    ]
  }
   ```

  **Error response (JSON):**
    ```json
    {
      "error": "Descriptive error message"
    }
    ```


Example with curl
-----------------
# 1. Get student grades
curl "https://YOUR_MOODLE_DOMAIN/local/appcrueservices/grades_proxy.php?studentemail=STUDENT_EMAIL&apikey=YOUR_API_KEY"

# 2. Get student calendar
curl "https://YOUR_MOODLE_DOMAIN/local/appcrueservices/calendar_proxy.php?moodlewsrestformat=json&studentemail=STUDENT_EMAIL&apikey=YOUR_API_KEY&timestart=1751788139&timeend=0"

# 3. Get student forums
curl "https://YOUR_MOODLE_DOMAIN/local/appcrueservices/forums_proxy.php?moodlewsrestformat=json&studentemail=STUDENT_EMAIL&apikey=YOUR_API_KEY"
