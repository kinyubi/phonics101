# ReadXYZ

Phonics101 is a collection of online exercises and lessons to assist in tutoring individuals that struggle with reading.

#### Progress Bar code
- progress bar is for the lesson list screen
    - Lesson list is created in src/ReadXYZ/Twig/LessonListTemplate.php
    - Twig template used is in templates/lesson_list.html.twig

### Audience
This repository contains a collection of online "lessons" that assist in tutoring/teaching:
* dyslexic students, 
* ESL students, 
* individuals lacking formal education,
* individuals with receptive or expressive language disorders,
* individuals that struggle with auditory processing and memory difficulties

### Purpose
These lessons intend to:
* systematically teach the connections between letters and sounds
* provide exercises in blending, segmenting, writing, spelling and fluency

### Approach
* combines extensive phoneme awareness instruction with phonological blending and phonic decoding practice
* is effective for students with and without intellectual disabilities
* proven more effective than Orton-Gillingham based programs.

### Design considerations:
- Languages/Frameworks
    - PHP 7.6
    - Javascript / JQuery 3.5
    - CSS (Customized Bootstrap 4.0)
    - HTML5
    - Twig 3.x
- Data: 
    - Dynamic data: MySql
    - Static data: JSON
- Testing
    - PHPUnit
    - Codeception
- Framework
    - Symfony
    - Composer
