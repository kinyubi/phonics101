# to see which students don't have trainers
USE readxyz0_1;
SELECT studentid, StudentName, trainer1 FROM abc_student WHERE trainer1 NOT IN (SELECT UserName FROM abc_users);

# to delete students who don't have trainers
DELETE FROM abc_student WHERE trainer1 NOT IN (SELECT UserName FROM abc_users);

# show trainers that don't have students
USE readxyz0_1;
SELECT uuid, UserName FROM abc_users WHERE UserName NOT IN (SELECT trainer1 FROM abc_student) AND UserName NOT LIKE 'test%';

#delete trainers that don't have students
USE readxyz0_1;
DELETE FROM abc_users WHERE UserName NOT IN (SELECT trainer1 FROM abc_student) AND UserName NOT LIKE 'test%';
