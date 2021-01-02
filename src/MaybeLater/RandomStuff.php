<?php


namespace App\MaybeLater;


class RandomStuff
{

    public static function generatePassword()
    {
        $chars      = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $strength   = rand(10, 20);
        $lastPos    = strlen($chars) - 1;
        $randomWord = '';
        for ($i = 0; $i < $strength; $i++) {
            $randomLetter = $chars[mt_rand(0, $lastPos)];
            $randomWord   .= $randomLetter;
        }

        return $randomWord;
    }
}
