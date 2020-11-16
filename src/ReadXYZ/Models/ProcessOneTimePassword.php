<?php


namespace App\ReadXYZ\Models;


use App\ReadXYZ\Data\OneTimePass;
use App\ReadXYZ\Data\TrainersData;
use App\ReadXYZ\Twig\LoginTemplate;

class ProcessOneTimePassword
{

    public function handleRequestAndEchoResponse(array $parameters): void
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
        $userId = (new TrainersData())->getTrainerId($username);
        $session = new Session();
        $session->clearSession();
        $session->updateUser($userId);
        RouteMe::autoLoginDisplay();
    }


}
