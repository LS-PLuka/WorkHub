<?php

require_once __DIR__ . "/../controller/empresa_controller.php";
require_once __DIR__ . "/../controller/cliente_controller.php";
require_once __DIR__ . "/../model/limite_login.php";

putenv("APP_ENV=production");
putenv("SESSAO_COOKIE_SECURE=false");
iniciar_sessao_segura();
$parametrosCookie = session_get_cookie_params();

function afirmar_seguranca($condicao, $mensagem)
{
    if (!$condicao) {
        fwrite(STDERR, "FALHA: {$mensagem}" . PHP_EOL);
        exit(1);
    }

    echo "OK: {$mensagem}" . PHP_EOL;
}

$cnpjValido = "12345678000195";
afirmar_seguranca(cnpj_tem_formato_aceito("12.345.678/0001-95"), "formato de CNPJ com máscara é aceito");
afirmar_seguranca(cnpj_tem_formato_aceito($cnpjValido), "formato de CNPJ normalizado é aceito");
afirmar_seguranca(!cnpj_tem_formato_aceito("12a345678000195"), "CNPJ com caracteres inesperados é rejeitado");
afirmar_seguranca(cnpj_eh_valido($cnpjValido), "dígitos verificadores de CNPJ válido são aceitos");
afirmar_seguranca(!cnpj_eh_valido("12345678000100"), "dígitos verificadores inválidos são rejeitados");

afirmar_seguranca(telefone_celular_eh_valido("(11) 99999-9999"), "telefone formatado válido é aceito");
afirmar_seguranca(telefone_celular_eh_valido("11999999999"), "telefone normalizado válido é aceito");
afirmar_seguranca(!telefone_celular_eh_valido("(00) 99999-9999"), "DDD inválido é rejeitado");
afirmar_seguranca(!telefone_celular_eh_valido("(11) 89999-9999"), "telefone sem nono dígito é rejeitado");
afirmar_seguranca(!telefone_celular_eh_valido("abc11999999999"), "telefone com letras é rejeitado");
afirmar_seguranca(normalizar_telefone("(11) 99999-9999") === "11999999999", "telefone é normalizado para 11 dígitos");
afirmar_seguranca(
    validar_dados_cliente(["nome" => "Cliente", "telefone" => "11999999999", "email" => "cliente@empresa.com.br"]) === [],
    "e-mail .com.br e telefone válido passam na validação de cliente",
);
afirmar_seguranca(!senha_tem_tamanho_valido(str_repeat("a", 73)), "senha acima do limite bcrypt é rejeitada");
afirmar_seguranca(senha_tem_tamanho_valido(str_repeat("a", 72)), "senha no limite bcrypt é aceita");

$conexao = conectar_banco_dados();
afirmar_seguranca($conexao !== false, "conexão com banco disponível para rate limiting");
$email = "teste-rate-limit@workhub.test";
limpar_falhas_login($conexao, "empresarial", $email);
for ($indice = 0; $indice < MAXIMO_FALHAS_LOGIN; $indice++) {
    afirmar_seguranca(registrar_falha_login($conexao, "empresarial", $email), "falha de login registrada");
}
afirmar_seguranca(login_esta_bloqueado($conexao, "empresarial", $email), "login é bloqueado após cinco falhas");
afirmar_seguranca(limpar_falhas_login($conexao, "empresarial", $email), "falhas são limpas após autenticação válida");
afirmar_seguranca(!login_esta_bloqueado($conexao, "empresarial", $email), "limpeza remove bloqueio");
mysqli_close($conexao);

afirmar_seguranca($parametrosCookie["secure"] === true, "cookie Secure é obrigatório em produção");
afirmar_seguranca($parametrosCookie["httponly"] === true, "cookie HttpOnly é preservado");
afirmar_seguranca($parametrosCookie["samesite"] === "Lax", "SameSite=Lax é preservado");

echo "OK: testes de segurança e validações concluídos." . PHP_EOL;
