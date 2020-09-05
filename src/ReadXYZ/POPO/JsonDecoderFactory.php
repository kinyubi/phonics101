<?php

namespace ReadXYZ\POPO;


class JsonDecoderFactory
{
    public static function unserializeLesson(string $json) : LessonPOPO
    {
        $o = json_decode($json);
        $lesson = new LessonPOPO();
        $lesson->lessonId = $o->lessonId;
        $lesson->lessonName = $o->lessonName;
    }
}