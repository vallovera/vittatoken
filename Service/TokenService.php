<?php

namespace VittaToken\Services\SSO;

use GuzzleHttp\Exception\RequestException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Facades\Session;

class TokenService
{
    protected $accessToken;
    protected $user;

    public function __construct(Authenticatable $user)
    {
        $this->user = $user;
    }


    public function getAccesToken()
    {
        if (is_null($this->accessToken)) {
            $this->setAccesToken();
        }

        return $this->encondeToken();
    }

    private function encondeToken()
    {
        $retornoToken = [];
        $retornoToken['access_token'] = base64_encode($this->accessToken['access_token']);
        $retornoToken['refresh_token'] = base64_encode($this->accessToken['refresh_token']);
        $retornoToken['token_type'] = base64_encode($this->accessToken['token_type']);

        return $retornoToken;
    }

    private function setAccesToken()
    {
        $obj = new \League\OAuth2\Client\Provider\GenericProvider([
            'clientId' => env('SSO_CLIENT_ID'),
            'clientSecret' => env('SSO_CLIENT_SECRET'),
            'urlAccessToken' =>  env('SSO_URL').'/oauth/token',
            'urlAuthorize' => 'Not used',
            'urlResourceOwnerDetails' => 'Not used'
        ]);

        $this->accessToken = $obj->getAccessToken('client_credentials')->jsonSerialize();
    }


    public function validateToken($token)
    {
        $uri = sprintf(
            env('SSO_URL').'/oauth/tokeninfo?access_token=%s',
            $token
        );

        $client = new \GuzzleHttp\Client();

        try {
            $retorno = $client->request('GET', $uri);

            $resposta = \GuzzleHttp\json_decode($retorno->getBody()->getContents());

            $user = $resposta->user;

            Session::put('user_auth', (array) $user);

            return $user;
        } catch (RequestException $e) {

            return false;
        }
    }
