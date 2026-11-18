# WorkHub

Plataforma de Gestão Empresarial em PHP 8.3, Apache, MySQL 8.4 e Docker Compose.

## Instalação local

Pré-requisitos: Git, Docker Desktop com Compose v2 (Windows 11) ou Docker Engine e o plugin
Compose (Ubuntu 24.04). As portas `8080` e as portas internas do Docker devem estar disponíveis.

1. Clone o repositório e entre no diretório.
2. Crie o arquivo local de configuração: `Copy-Item .env.example .env` no PowerShell ou
   `cp .env.example .env` no Linux.
3. Altere, no mínimo, `MYSQL_PASSWORD`, `MYSQL_ROOT_PASSWORD`, `ADMIN_INICIAL_EMAIL`,
   `ADMIN_INICIAL_SENHA` e `APP_CHAVE_RATE_LIMIT`. Mantenha `DB_*` consistente com `MYSQL_*`.
4. Inicie: `docker compose up -d --build`.
5. Verifique: `docker compose ps` e `docker compose logs --tail=100 app db`.
6. Acesse [http://localhost:8080](http://localhost:8080) e entre em
   `/administracao/entrar` com o administrador inicial configurado.

O bootstrap administrativo é exclusivamente local: requer `APP_ENV=local` (ou
`development`/`desenvolvimento`) e `ADMIN_BOOTSTRAP_HABILITADO=true`. Ele só cria a conta quando
não existe outro administrador e nunca redefine uma senha existente. Para criar um administrador
fora desse cenário, use `docker compose exec -it app php scripts/criar_administrador.php`.

O MySQL não é publicado no host. O volume nomeado `mysql_data` persiste entre `docker compose down`
e uma nova inicialização. Os SQLs em `database/` são processados pelo MySQL somente quando o volume
é novo; para um banco já existente, aplique o schema conscientemente após revisar a alteração:

```powershell
Get-Content -Raw database/schema.sql | docker compose exec -T db sh -c 'mysql -u"$MYSQL_USER" -p"$MYSQL_PASSWORD" "$MYSQL_DATABASE"'
```

Não use `docker compose down -v` a menos que queira apagar deliberadamente os dados locais.

## Verificação e testes

```powershell
docker compose exec app php scripts/verificar_conexao_banco.php
docker compose exec app php scripts/verificar_banco.php
docker compose exec app php scripts/testar_cadastro_empresa.php
docker compose exec app php scripts/testar_autenticacao.php
docker compose exec app php scripts/testar_administracao.php
docker compose exec app php scripts/testar_bootstrap_administrador.php
docker compose exec app php scripts/testar_usuarios.php
docker compose exec app php scripts/testar_minha_empresa.php
docker compose exec app php scripts/testar_clientes.php
docker compose exec app php scripts/testar_produtos.php
docker compose exec app php scripts/testar_pedidos.php
docker compose exec app php scripts/testar_detalhes_cliente.php
docker compose exec app php scripts/testar_dashboard.php
docker compose exec app php scripts/testar_seguranca_validacoes.php
```

Os scripts de testes transacionais usam `ROLLBACK` e não mantêm dados de teste. Para investigar
falhas, consulte `docker compose logs app`, `docker compose logs db` e o HTML/headers retornados.

Problemas comuns: no Windows, confirme que Docker Desktop está iniciado e que a porta 8080 não está
ocupada; no Linux, confirme acesso do usuário ao Docker (`docker ps`) e não converta scripts `.sh`
para CRLF. O repositório força LF para scripts e arquivos de contêiner via `.gitattributes`.

## Produção e AWS EC2

`compose.prod.yaml` é uma composição independente para produção: não possui bind mount de código,
não publica MySQL e põe Apache atrás do Caddy. Requer um domínio público já apontado à instância e
um `.env` de produção, fora do Git, com senhas e `APP_CHAVE_RATE_LIMIT` fortes, `APP_ENV=production`,
`SESSAO_COOKIE_SECURE=true`, `ADMIN_BOOTSTRAP_HABILITADO=false`, `PROXY_REVERSO_CONFIAVEL=true` e
`WORKHUB_DOMINIO` definido. Inicie-a com:

```sh
docker compose --env-file .env -f compose.prod.yaml up -d --build
```

Não execute essa composição até concluir o checklist em
[docs/deploy_aws_ec2.md](docs/deploy_aws_ec2.md). Ela não configura AWS, DNS ou certificados sem um
domínio válido.

Para a demonstração pública temporária por IPv4 e HTTP, use exclusivamente `compose.demo.yaml` e
`.env.demo`, conforme a seção Etapa 10M em [docs/deploy_aws_ec2.md](docs/deploy_aws_ec2.md). Essa
composição não inicia Caddy, publica somente TCP 80, mantém MySQL interno ao Docker e usa
`APP_ENV=demo` com cookies de sessão compatíveis com HTTP. Não reutilize o `.env` local ou o compose
de produção nesse cenário.

## Estrutura

`config/` contém configuração; `controller/`, `model/` e `view/` formam o MVC simplificado;
`public/` é a raiz Apache; `database/` contém schema; `scripts/` contém verificações de terminal; e
`docker/` contém Apache, entrada e proxy de produção.
