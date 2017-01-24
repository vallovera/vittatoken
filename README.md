

Primeiramente instale via composer:

composer require vallovera/vitta-token


Feito isso, adicione a seguinte linha no Kernel.php que está localizado no app/Http/Kernel.php

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

