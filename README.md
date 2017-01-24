

##Primeiramente instale via composer:##

composer require vallovera/vitta-token


##Feito isso, adicione a seguinte linha no Kernel.php que está localizado no## app/Http/Kernel.php

Escolha o prefixo que deseja ser coberto pelo token e insira:

'auth' => VerifyTokenSSO::class

Exemplo utilizando prefixo 'api':

  'api' => [
            'throttle:60,1',
            'bindings',
            'cors',
            'auth' => VerifyTokenSSO::class,
            'polices',
        ],



##Pronto sua verificação de token e middleware já estão funcionando corretamente##

Verifique se as variaveis do .env abaixo encontran-se na aplicação:

SSO_CLIENT_ID=123
SSO_CLIENT_SECRET=123
SSO_ENDPONT=http://www
SSO_URL=http://www


Caso deseja fazer mais validações, ou criar outras classes de serviço que vão consumir a TokenService,
Crie desta maneira:

class ClasseServiceTeste extends TokenServiceAbstract
{
    // Métodos
    //Se usar o $this, tem acesso as variáveis e objetos da classe pai
}
