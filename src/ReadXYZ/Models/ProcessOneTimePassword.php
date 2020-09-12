<?php


namespace ReadXYZ\Models;


use ReadXYZ\Database\OneTimePass;
use ReadXYZ\Twig\Twigs;

class ProcessOneTimePassword
{

    public function handleRequestAndGetResponse(array $parameters): string
    {
        $twigs = Twigs::getInstance();
        $otp = $parameters['otp'] ?? '';
        if (!$otp) {
            return $twigs->login('Auto-login failed. Try logging in manually.');
        }
        $decoder = new OneTimePass();
        $username = $decoder->decodeOTP($otp);
        if (!$username) {
            return $twigs->login('Invalid one-time password' . $otp . '. Try logging in manually.');
        }
        $identity = Identity::getInstance();
        $identity->clearIdentity();
        $result = $identity->validateSignin($username, 'xx');
        if ($result->failed()) {
            return $twigs->login("Username validation failed for $username.");
        }
    }


}
