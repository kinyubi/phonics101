# phonics101 Change Log
### 2021-01-15
*[x] Database changes
    - removed userEmail from abc_trainers and vw_students_with_username
    - dropped table abc_zoo_animals (now json-based)
    - delete ZooAnimalsData class
    - dropped vw_lessons_with_group_fields
    - dropped vw_lesson_mastery
    - dropped vw_student_lessons
    - dropped vw_mastery_subquery
    - dropped vw_accordion
### 2020-12-24
*[x] abc_student_lesson: 
    * timePresented field removed.
    * fluencyTimes changed to JSON MEDIUMTEXT
    * testTimes changed to JSON MEDIUMTEXT
*[x] removed OldStudent class.
*[x] fixed initial tab name (again)
*[x] fixed timers to pause and not act wrong if clicked multiple times.

### 2020-12-10
*[x] fixed queryAndGetScalar
*[x] GroupData::getGroupCode 
*[x] got rid of prepareCurrentForUpdate
*[x] fixed all StudentData references
*[x] fixed all StudentTable reference 
*[x] fixed all Student class references
  * change to non-singleton based on session
*[x] fixed all Cookie references
*[x] fixed all Identity references
*[x] renamed BlendingInfo to OldBlendingInfo
*[x] migrated GameTypes from JSON to mysql
*[x] migrated TabTypes from JSON to mysql
*[x] removed addSoundClass references.
*[x] made sure all MasteryLevel code is refactored.
*[x] deprecated Util::getHumanReadableDate()
*[x] deprecated Util::getHumanReadableDateTime()
*[x] made sure lesson alternateNames are now non-associative array
*[x] deprecated Util::fixTabName()
*[x] converted all studentId to studentCode
*[x] converted all userId to userCode
*[x] finished Lessons::setAccordion()
*[x] created a tool to convert old user/student data to old_people.json
*[x] refactored LessonListTemplate
*[x] fixed lessons->getGroupName and GroupData=>getGroupName to return string (empty if not found)
*[x] refactored Lesson class to use LessonsData class
*[x] created a method that would populate lessons accordion template with mastery values
*[x] created ActionForms class to replace timers.php, processStudentSelection.php, render.php, processUserLogin.php, processLessonSelection.php, practiceTimer.php and login.php
*[x] make sure functions with a lesson name parameter take into consideration alternates
*[x] uses lesson GroupCode instead of GroupName where appropriate

### 2020_1226 Differences
* abc_student_lesson:timesPresented deleted
* abc_student_lessons:fluencyTimes, testTimes
    * varchar array to JSON MEDIUMTEXT array
* vw_lesson_with_group_fields changed
