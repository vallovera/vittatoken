<?php

namespace VittaToken\Middleware;

use VittaToken\Services\TokenService;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Closure;


abstract class VerifyTokenSSO
{
    protected $serviceToken;

    public function __construct(TokenService $tokenService)
    {
        $this->serviceToken = $tokenService;
    }

    public function handle($request, Closure $next)
    {
        $token_decode = $this->decodeToken(json_decode($request->header('token')));

        //Verifica se usuário está logado
        if (!$request->hasHeader('token') || !$this->serviceToken->validateToken($token_decode->access_token)) {
            if ($request->ajax()) {
                response('Not Autorized.', 401);
            }

            abort(401);
        }

        //Adiciono body para validar nas polices
        $request->request->add(['instituicao_id' => $token_decode->instituicao_id]);
        $request->request->add(['unidade_id' => $token_decode->unidade_id]);

        return $next($request);
    }

    public function decodeToken($token)
    {
        $decodeToken = (object)[
            'access_token' => base64_decode($token->access_token),
            'refresh_token' => base64_decode($token->refresh_token),
            'token_type' => base64_decode($token->token_type),
            'instituicao_id' =>  base64_decode($token->instituicao_id),
            'unidade_id' => base64_decode($token->unidade_id)
        ];

        return $decodeToken;
    }
}
