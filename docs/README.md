# phonics101

### To Do
*[ ] use warmup field in lesson class

*[ ] tool to populate abc_trainers and abc_students from selective old_people.json file.

### 2020-12-11 Migrate readxyz10_phonics
*[ ] scripts to get JSON for abc_Users, abc_Students, and abc_usermastery. See Siteground JSON Extract OneNote.
*[x] function to convert epoch time to sql DATE - Util::dbDate()


### COMPLETED
*[x] create a sql for the entire database and tables (readxyz0_phonics_struct.sql) 
*[x] change all json data types to MEDIUMTEXT.
*[x] Bring over fixed data
  - create sql to migrate abc_gametypes, abc_tabtypes, abc_warmups, abc_zoo_animals, abc_keychain, abc_lessons (readxyz0_fixed_data.sql)
*[x] create sql for views. (readxyz0_phonics_views.sql)


### FUTURE
*[ ] convert actions/timers.php to routing
*[ ] convert warmups json to mysql
