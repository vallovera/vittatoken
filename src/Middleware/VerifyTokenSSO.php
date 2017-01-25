<?php

namespace VittaToken\Middleware;

use Illuminate\Http\Response;
use VittaToken\Service\TokenService;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Route;
use Closure;

class VerifyTokenSSO
{
    /**
     * @var \VittaToken\Service\Singleton
     */
    protected $serviceToken;

    /**
     * @var Token Objeto token já decodificado
     */
    protected $token;

    /**
     * @var Integer armazena id da unidade
     */
    protected $unidadeId;

    /**
     * @var Integer armazena id da instituição
     */
    protected $instituicaoId;


    /**
     * VerifyTokenSSO constructor.
     */
    public function __construct()
    {
        $this->serviceToken = TokenService::getInstance();
    }

    /**
     * Faz o controle da request e valida se o token está inserido, caso sim valida
     * @param $request
     * @param Closure $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if (is_null($request->header('token'))) {
            if (is_null($request->token)) {
                 abort(401);
            } else {
                 $jsonDecode = json_decode($request->token);
            }
        } else {
            $jsonDecode = json_decode($request->header('token'));
        }

        if (!is_object($jsonDecode)) {
            abort(401);
        }

        $token_decode = $this->decodeToken($jsonDecode);
        $this->token = $token_decode;

        if (!$token_decode->access_token || !$this->serviceToken->validateToken($token_decode->access_token)) {
            if ($request->ajax()) {
                response('Not Autorized.', Response::HTTP_FORBIDDEN);
            }

            abort(401);
        }

        if (property_exists($jsonDecode, 'unidade_id')) {
            $this->unidadeId = base64_decode($jsonDecode->unidade_id);
            $request->request->add(['unidade_id' => $this->unidadeId]);
        }

        if (property_exists($jsonDecode, 'instituicao_id')) {
            $this->instituicaoId = base64_decode($jsonDecode->instituicao_id);
            $request->request->add(['instituicao_id' => $jsonDecode->instituicao_id]);
        }

        return $next($request);
    }

    /**
     * Decodifica o token recebido em base 64
     * @param $token
     * @return object
     */
    public function decodeToken($token)
    {
        $decodeToken = (object)[
            'access_token' => base64_decode($token->access_token),
            'refresh_token' => base64_decode($token->refresh_token),
            'token_type' => base64_decode($token->token_type)
        ];

        return $decodeToken;
    }
}
