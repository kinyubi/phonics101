USE readxyz0_phonics;

DROP VIEW IF EXISTS vw_accordion;
DROP VIEW IF EXISTS vw_group_with_keychain;
DROP VIEW IF EXISTS vw_lessons_with_group_fields;
DROP VIEW IF EXISTS vw_mastery_subquery;
DROP VIEW IF EXISTS vw_lesson_mastery;
DROP VIEW IF EXISTS vw_students_with_username;
DROP VIEW IF EXISTS vw_student_lessons;

CREATE VIEW vw_accordion AS 
	SELECT al.lessonCode AS lessonCode,
	al.lessonName AS lessonName,
	al.lessonDisplayAs AS lessonDisplayAs,
	al.active AS active,
	ag.groupCode AS groupCode,
	ag.groupName AS groupName,
	ag.groupDisplayAs AS groupDisplayAs,
	ifnull(asl.masteryLevel,'none') AS mastery,
	ifnull((asl.masteryLevel + 0),0) AS masteryIndex,
	asl.studentCode AS studentCode,
	ak.fileName AS animalFileName 
	FROM (((abc_lessons al 
		LEFT JOIN abc_groups ag ON((al.groupCode = ag.groupCode))) 
		LEFT JOIN abc_student_lesson asl ON((al.lessonCode = asl.lessonCode))) 
		LEFT JOIN abc_keychain ak ON((ag.groupCode = ak.groupCode))) 
	WHERE (al.active = 'y') order by al.lessonCode;
	
CREATE VIEW vw_group_with_keychain AS
	SELECT G.*, K.fileName, K.friendlyName FROM abc_groups G
	LEFT JOIN abc_keychain K USING (groupCode);
	
CREATE VIEW vw_lessons_with_group_fields AS
	SELECT L.lessonCode, L.lessonName, L.lessonDisplayAs, L.ordinal, L.groupCode, G. groupName, G.groupDisplayAs 
	FROM abc_lessons L  INNER JOIN abc_groups G ON L.groupCode = G.groupCode 
	WHERE L.active = 'Y' 
	ORDER BY L.lessonCode;
	
CREATE VIEW vw_mastery_subquery AS
	SELECT SL.masteryLevel, SL.studentCode, SL.lessonCode
	FROM abc_student_lesson SL 
	INNER JOIN abc_students  USING (studentCode)
	INNER JOIN abc_lessons USING (lessonCode);
		
CREATE VIEW vw_lesson_mastery AS
	SELECT S.studentCode, S.studentName,  L.lessonCode, L.lessonName, G.groupCode, G.groupName, IFNULL(X.masteryLevel,'none') AS masteryLevel
	FROM  abc_lessons L 
	INNER JOIN abc_groups G USING (groupCode)   
	CROSS JOIN abc_students S  
	LEFT JOIN vw_mastery_subquery X ON L.lessonCode = X.lessonCode AND S.studentCode = X.studentCode;
	
CREATE VIEW vw_students_with_username AS
	SELECT S.studentCode, S.studentName, T.userName, T.trainerCode, S.active 
	FROM abc_students S INNER JOIN abc_trainers T ON S.userName = T.userName;
	
CREATE VIEW vw_student_lessons AS
	SELECT  L.*, SL.masteryLevel, SL.fluencyTimes, SL.testTimes, SL.studentCode FROM abc_lessons AS L 
	LEFT JOIN abc_student_lesson SL ON L.lessonCode = SL.lessonCode;