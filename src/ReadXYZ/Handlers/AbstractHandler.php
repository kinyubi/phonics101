<?php


namespace App\ReadXYZ\Handlers;


use App\ReadXYZ\Helpers\Util;

class AbstractHandler
{
    protected static int     $previousErrorReporting;

    protected static function fullLocalErrorReportingOn()
    {
        if (Util::isLocal()) {
            self::$previousErrorReporting =  error_reporting(E_ALL | E_STRICT);
        }
    }

    protected static function fullLocalErrorReportingOff()
    {
        if (Util::isLocal()) {
            error_reporting(self::$previousErrorReporting );
        }
    }


    /**
     * @param int $http_code the http code we want the response to send
     * @param string $msg the message we want the response to return (default: OK)
     */
    protected static function sendResponse(int $http_code = 200, string $msg = 'OK'): void
    {
        header('Content-Type: application/json');
        http_response_code($http_code);
        echo json_encode(['code' => $http_code, 'msg' => $msg]);
    }

    protected static function httpPost($url, $data){
    	$options = array(
    		'http' => array(
         		'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
            	'method'  => 'POST',
            	'content' => http_build_query($data)
        	)
        );
    	$context  = stream_context_create($options);
    	return file_get_contents($url, false, $context);
    }
}
