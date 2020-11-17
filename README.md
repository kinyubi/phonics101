# phonics101

### 2020-11-11
*[x] fix queryAndGetScalar
*[x] GroupData::getGroupCode 
### 2020-11-12
*[x] get rid of prepareCurrentForUpdate
*[x] fix all StudentData references
*[x] fix all StudentTable reference 
*[x] fix all Student class references
  * change to non-singleton based on session
*[x] fix all Cookie references
*[x] fix all Identity references
*[x] rename BlendingInfo to OldBlendingInfo

### 2020-11-14

*[ ] refactor LessonListTemplate
*[ ] lessons->getGroupName => GroupData=>getGroupName (returns DbResult instead of string)
*[ ] refactor Lesson class to use LessonsData class

### 2020-11-15
*[x] migrate GameTypes from JSON to mysql
*[x] migrate TabTypes from JSON to mysql

### 2020-11-16
*[x] remove addSoundClass references.
*[x] make sure all MasteryLevel code is refactored.
*[x] deprecate Util::getHumanReadableDate()
*[x] deprecate Util::getHumanReadableDateTime()
*[x] make sure lesson alternateNames are now non-associative array.
*[ ] create a method that will populate lesson's accordion template with mastery values.
*[ ] uses lesson GroupCode instead of GroupName where appropriate
*[ ] make sure functions with a lesson name parameter take into consideration alternates
*[ ] use warmup field in lesson class
*[x] deprecate Util::fixTabName()

### FUTURE
*[ ] convert actions/timers.php to routing
*[ ] convert warmups json to mysql
