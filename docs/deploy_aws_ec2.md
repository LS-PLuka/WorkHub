# Execução na Amazon EC2

O WorkHub usa o mesmo `compose.yaml` em todos os ambientes. Na EC2, crie um `.env` a partir de
`.env.example`, mantenha-o fora do Git e altere apenas os valores do ambiente escolhido.

## Demonstração acadêmica HTTP

Esta modalidade não precisa de domínio nem HTTPS. No `.env`, use credenciais fortes e independentes das
locais, além destes valores:

~~~dotenv
COMPOSE_PROJECT_NAME=workhub-demo
MYSQL_VOLUME_NAME=workhub_demo_mysql_data
APP_ENV=demo
APP_BIND_ADDRESS=0.0.0.0
APP_PORT=80
SESSAO_COOKIE_SECURE=false
PROXY_REVERSO_CONFIAVEL=false
ADMIN_BOOTSTRAP_HABILITADO=false
ADMIN_INICIAL_EMAIL=
ADMIN_INICIAL_SENHA=
APP_CHAVE_RATE_LIMIT=gere_uma_chave_hmac_aleatoria_e_exclusiva
~~~

Execute:

~~~bash
docker compose config --quiet
docker compose up -d --build
docker compose ps
~~~

Abra somente a porta TCP 80 no Security Group da EC2. O MySQL não possui porta publicada pelo Compose.
Crie o administrador de demonstração manualmente, uma única vez, se ainda não existir:

~~~bash
docker compose exec app php scripts/criar_administrador.php
~~~

## Produção com HTTPS

HTTPS exige domínio público apontado para a EC2. Antes de iniciar, altere o `.env` com segredos fortes:

~~~dotenv
APP_ENV=production
APP_BIND_ADDRESS=127.0.0.1
APP_PORT=8080
SESSAO_COOKIE_SECURE=true
PROXY_REVERSO_CONFIAVEL=true
ADMIN_BOOTSTRAP_HABILITADO=false
ADMIN_INICIAL_EMAIL=
ADMIN_INICIAL_SENHA=
APP_CHAVE_RATE_LIMIT=gere_uma_chave_hmac_aleatoria_e_exclusiva
WORKHUB_DOMINIO=app.seu-dominio.exemplo
~~~

Abra TCP 80 e 443 no Security Group, mantenha TCP 22 restrito ao acesso administrativo e não publique
TCP 3306. Inicie o perfil HTTPS:

~~~bash
docker compose --profile https config --quiet
docker compose --profile https up -d --build
docker compose --profile https ps
~~~

O perfil inicia o Caddy nas portas 80/443. A aplicação fica vinculada apenas a `127.0.0.1:8080`, e o
MySQL continua apenas na rede Docker. O Caddy obtém e renova o certificado TLS quando o domínio aponta
publicamente para a EC2.

## Persistência e instalações existentes

O volume do banco é definido por `MYSQL_VOLUME_NAME`. A configuração local padrão usa
`workhub_mysql_data`, preservando o volume já usado pelo Compose anterior. Não use
`docker compose down -v`.

Uma demonstração criada com a configuração antiga pode continuar usando seus dados ao definir:

~~~dotenv
COMPOSE_PROJECT_NAME=workhub-demo
MYSQL_VOLUME_NAME=workhub-demo_mysql_demo_data
~~~

Não há migração automática: confira o nome com `docker volume ls` antes de iniciar uma instalação
existente. Reinicializações e `docker compose down` normal preservam o volume.

## Backup

Faça backup lógico fora da EC2 e teste a restauração em ambiente isolado:

~~~bash
docker compose exec -T db sh -c 'mysqldump -u"$MYSQL_USER" -p"$MYSQL_PASSWORD" "$MYSQL_DATABASE"' > workhub-AAAA-MM-DD.sql
~~~
