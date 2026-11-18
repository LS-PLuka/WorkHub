#!/bin/sh
set -eu

for variavel in DB_HOST DB_PORT DB_NAME DB_USER DB_PASSWORD; do
    eval "valor=\${$variavel:-}"
    if [ -z "$valor" ]; then
        echo "Variável obrigatória não configurada: $variavel" >&2
        exit 1
    fi
done

tentativa=1
while ! php -r '
    mysqli_report(MYSQLI_REPORT_OFF);
    $conexao = @mysqli_connect(getenv("DB_HOST"), getenv("DB_USER"), getenv("DB_PASSWORD"), getenv("DB_NAME"), (int) getenv("DB_PORT"));
    exit($conexao === false ? 1 : 0);
'; do
    if [ "$tentativa" -ge 30 ]; then
        echo "Não foi possível conectar ao MySQL após 60 segundos." >&2
        exit 1
    fi

    echo "Aguardando MySQL ($tentativa/30)..." >&2
    tentativa=$((tentativa + 1))
    sleep 2
done

php scripts/bootstrap_administrador_inicial.php

exec apache2-foreground
