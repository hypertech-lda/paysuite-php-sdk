## Como utilizar a biblioteca PaySuite PHP SDK

A biblioteca `paysuite-php-sdk` permite que você processe pagamentos de forma fácil e rápida usando os métodos de pagamento disponíveis em Moçambique, como Mpesa, eMola, PayPal e transferência bancária.

[🇬🇧 English Documentation](../README.md)

### Instalação

1. Crie uma conta no [Paysuite.tech](https://paysuite.tech) e obtenha seu token de acesso no dashboard

2. Instale a biblioteca usando o Composer:

```bash
composer require hypertech/paysuite-php-sdk
```

### Uso Básico

Primeiro, importe e inicialize o cliente com seu token:

```php
use Hypertech\Paysuite\Client;
use Hypertech\Paysuite\Exception\ValidationException;
use Hypertech\Paysuite\Exception\PaysuiteException;

$token = "seu-token-de-acesso";
$client = new Client($token);
```

#### Criar um Pedido de Pagamento

```php
try {
    $response = $client->createPaymentRequest([
        'amount' => '100.50',
        'reference' => 'FACT123',
        'description' => 'Pagamento de factura',
        'return_url' => 'https://seusite.com/retorno'
    ]);

    if ($response->isSuccessfully()) {
        $data = $response->getData();
        $checkoutUrl = $data['checkout_url'];
        $paymentId = $data['id'];
        
        // Redirecione o cliente para a página de pagamento
        header("Location: " . $checkoutUrl);
        exit;
    }
} catch (ValidationException $e) {
    // Trate erros de validação
    echo "Erro de validação: " . $e->getMessage();
} catch (PaysuiteException $e) {
    // Trate erros da API
    echo "Erro da API: " . $e->getMessage();
}
```

#### Verificar Status do Pagamento

```php
try {
    $response = $client->getPaymentRequest($paymentId);
    
    if ($response->isSuccessfully()) {
        $data = $response->getData();
        $status = $data['status'];
        
        if (isset($data['transaction'])) {
            $transactionId = $data['transaction']['transaction_id'];
            $paidAt = $data['transaction']['paid_at'];
        }
    }
} catch (ValidationException $e) {
    echo "Erro de validação: " . $e->getMessage();
} catch (PaysuiteException $e) {
    echo "Erro da API: " . $e->getMessage();
}
```

### Tratamento de Erros

O SDK inclui dois tipos principais de exceções:

- `ValidationException`: Para erros de validação (dados inválidos ou ausentes)
- `PaysuiteException`: Para erros da API (autenticação, servidor, etc.)

### Executando Testes

Configure seu token de teste no arquivo `phpunit.xml` ou via variável de ambiente:

```bash
export TOKEN="seu-token-de-teste"
composer test
```

### Changelog

Por favor, veja [CHANGELOG](../CHANGELOG.md) para mais detalhes.

## Contribuição

Por favor, veja [CONTRIBUTING](../CONTRIBUTING.md) para mais detalhes.

### Segurança

Se você descobrir algum problema relacionado à segurança, envie um e-mail para security@hypertech.co.mz em vez de usar o rastreador de problemas.

## Licença

The MIT License (MIT). Por favor, veja [License File](../LICENSE.md) para mais informações.
