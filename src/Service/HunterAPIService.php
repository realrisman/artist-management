<?php

namespace App\Service;

use Doctrine\Common\Persistence\ManagerRegistry;

class HunterAPIService
{

    protected $registry;

    /**
     * HunterAPIService constructor.
     */
    public function __construct(ManagerRegistry $registry)
    {
        $this->registry = $registry;
    }


    /*Get Hunter Io Account Information*/
    public function getHunterAccountInfo()
    {
        $apiKey = getenv('HUNTER_API_KEY');
        $endpoint = 'https://api.hunter.io/v2/account?';
        $url = $endpoint . 'api_key=' . $apiKey;
        $result = $this->callApi($url);
        return $result;
    }

    /*Hunter Io Verification Email Api*/

    public function callHunterApi($email)
    {
        $apiKey = getenv('HUNTER_API_KEY');
        $endpoint = 'https://api.hunter.io/v2/email-verifier?';
        $url = $endpoint . 'email=' . $email . '&api_key=' . $apiKey;
        $result = $this->callApi($url);
        return $result;
    }

    public function callApi($url)
    {
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "GET",
            CURLOPT_POSTFIELDS => "",
        ));
        $response = curl_exec($curl);
        $err = curl_error($curl);

        curl_close($curl);

        if ($err) {
            return "cURL Error #:" . $err;
        } else {
            return json_decode($response, true);
        }
    }
}
