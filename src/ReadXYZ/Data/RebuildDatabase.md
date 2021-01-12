- Drop tables
```mysql
SET FOREIGN_KEY_CHECKS = 0;

TRUNCATE abc_student_animals; # delete before abc_groups, abc_zoo_animals
TRUNCATE abc_student_keychain; # delete before abc_keychain, abc_students
TRUNCATE abc_student_lesson; # delete before abc_students, abc_lessons
TRUNCATE abc_warmups; # delete before abc_lessons
TRUNCATE abc_word_mastery; # delete before abc_students
TRUNCATE abc_zoo_animals; # delete before abc_lessons
TRUNCATE abc_lessons; # delete before abc_groups
TRUNCATE abc_students; # delete before abc_trainers
TRUNCATE abc_gametypes;  # delete before abc_tabtypes
TRUNCATE abc_keychain; # delete before abc_groups
TRUNCATE abc_groups;
TRUNCATE abc_onetime_pass;
TRUNCATE abc_system_log;
TRUNCATE abc_tabtypes;
TRUNCATE abc_trainers;

SET FOREIGN_KEY_CHECKS = 1;
```
- run tools/updateGroupsAndLessonsFromUnifiedLessons
    - creates abc_groups from unifiedLessons.json
    - creates abc_group_lessons from unifiedLessons.json
