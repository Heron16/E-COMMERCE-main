<?php
define('PIX_CHAVE', '11987654321');
define('PIX_NOME_BENEFICIARIO', 'Lavagem Auto Center');
define('PIX_CIDADE', 'São Paulo');

define('QRCODE_API_URL', 'https://api.qrserver.com/v1/create-qr-code/');
define('QRCODE_SIZE', '250x250');

define('PAGAMENTO_TIMEOUT_MINUTOS', 30);
define('PAGAMENTO_VERIFICACAO_INTERVALO', 10);

define('FORMAS_PAGAMENTO', [
    'pix' => [
        'nome' => 'PIX',
        'icone' => '💳',
        'descricao' => 'Pagamento instantâneo',
        'disponivel' => true
    ],
    'dinheiro' => [
        'nome' => 'Dinheiro',
        'icone' => '💵',
        'descricao' => 'Pagar no local',
        'disponivel' => true
    ],
    'cartao' => [
        'nome' => 'Cartão',
        'icone' => '💳',
        'descricao' => 'Débito ou crédito no local',
        'disponivel' => true
    ]
]);

function gerarQRCodePix($chave_pix, $valor, $descricao = '') {
    $payload = gerarPayloadPix($chave_pix, $valor, $descricao);
    $qrcode_url = QRCODE_API_URL . '?size=' . QRCODE_SIZE . '&data=' . urlencode($payload);
    return $qrcode_url;
}

function gerarPayloadPix($chave_pix, $valor, $descricao = '') {
    return $chave_pix;
}

function formaPagamentoDisponivel($forma) {
    $formas = FORMAS_PAGAMENTO;
    return isset($formas[$forma]) && $formas[$forma]['disponivel'];
}

function getFormaPagamentoInfo($forma) {
    $formas = FORMAS_PAGAMENTO;
    return isset($formas[$forma]) ? $formas[$forma] : null;
}
