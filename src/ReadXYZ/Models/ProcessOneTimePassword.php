<?php


namespace ReadXYZ\Models;


use ReadXYZ\Database\OneTimePass;
use ReadXYZ\Twig\LoginTemplate;

class ProcessOneTimePassword
{

    public function handleRequestAndEchoResponse(array $parameters): string
    {
        $loginTemplate = new LoginTemplate();
        $otp = $parameters['otp'] ?? '';
        if (!$otp) {
            $loginTemplate->display('Auto-login failed. Try logging in manually.');
        }
        $decoder = new OneTimePass();
        $username = $decoder->decodeOTP($otp);
        if (!$username) {
            $loginTemplate->display('Invalid one-time password' . $otp . '. Try logging in manually.');
        }
        $identity = Identity::getInstance();
        $identity->clearIdentity();
        $result = $identity->validateSignin($username, 'xx');
        if ($result->failed()) {
            $loginTemplate->display("Username validation failed for $username.");
        }
    }


}
