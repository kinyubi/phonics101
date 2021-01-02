<?php


namespace App\ReadXYZ\Models;


use App\ReadXYZ\Data\OneTimePass;
use App\ReadXYZ\Data\TrainersData;
use App\ReadXYZ\Twig\LoginTemplate;

class ProcessOneTimePassword
{

    /**
     * @param array $parameters
     * @throws \App\ReadXYZ\Helpers\PhonicsException
     */
    public function handleRequestAndEchoResponse(array $parameters): void
    {
        $loginTemplate = new LoginTemplate();
        $otp = $parameters['otp'] ?? '';
        if (!$otp) {
            $loginTemplate->display('Auto-login failed. Try logging in manually.');
        }
        $decoder = new OneTimePass();
        $username = $decoder->decodeJson($otp);
        if (!$username) {
            $loginTemplate->display('Invalid one-time password' . $otp . '. Try logging in manually.');
        }
        $userCode = (new TrainersData())->getTrainerCode($username);
        Session::clearSession();
        Session::updateUser($userCode);
        RouteMe::computeImpliedRoute();
    }


}
