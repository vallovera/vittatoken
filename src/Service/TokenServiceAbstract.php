<?php

namespace VittaToken\Service;

use GuzzleHttp\Exception\RequestException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

abstract class TokenServiceAbstract
{
    protected $accessToken;
    protected $user;

    /**
     * Instance
     *
     * @var Singleton
     */
    protected static $_instance;

    /**
     * Get instance
     *
     * @return Singleton
     */
    public final static function getInstance()
    {
        if (null === static::$_instance) {
            static::$_instance = new TokenService();
        }

        return static::$_instance;
    }

    /**
     * Busca o token no servidor SSO, devolve criptografado
     * @return array
     */
    public function getAccesToken()
    {
        if (is_null($this->accessToken)) {
            $this->setAccesToken();
        }

        return $this->encodeToken();
    }

    /**
     * Criptografa base 64 o token para retorno
     * @return array
     */
    private function encodeToken()
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
            'urlAccessToken' => env('SSO_URL') . '/oauth/token',
            'urlAuthorize' => 'Not used',
            'urlResourceOwnerDetails' => 'Not used'
        ]);

        $this->accessToken = $obj->getAccessToken('client_credentials')->jsonSerialize();
    }


    public function validateToken($token)
    {
        $uri = sprintf(
            env('SSO_URL') . '/oauth/tokeninfo?access_token=%s',
            $token
        );

        $client = new \GuzzleHttp\Client();

        try {
            $retorno = $client->request('GET', $uri);

            $resposta = \GuzzleHttp\json_decode($retorno->getBody()->getContents());

            $user = $resposta->user;

             $temSessao =session('user_auth');

            if (!$temSessao) {
                Session::put('user_auth', (array)$user);
            }

            return $user;
        } catch (RequestException $e) {
            return false;
        }
    }
}

