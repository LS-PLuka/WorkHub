# Preparação para AWS EC2

Este guia prepara uma implantação; não cria recursos AWS nem afirma que HTTPS já foi validado.

## Arquitetura proposta

Em uma EC2 Ubuntu Server 24.04 (planejada como `t3.small` em `us-east-1`), Caddy recebe as portas
80/443 e encaminha para Apache/PHP somente pela rede Docker. MySQL permanece sem `ports`, com dados
no volume `mysql_data`, armazenado no EBS da instância. A composição de produção é
`compose.prod.yaml`; ela não reutiliza o bind mount de desenvolvimento. Os limites iniciais são
128 MiB (Caddy), 512 MiB (app) e 768 MiB (MySQL), com buffer pool MySQL de 256 MiB. Devem ser
monitorados e ajustados após carga real.

Caddy foi escolhido em vez de Nginx por manter a configuração curta e automatizar emissão e renovação
de certificados quando há domínio resolvendo publicamente para a EC2. Nginx é uma alternativa válida,
mas exige configurar e renovar o certificado separadamente. Sem domínio, use temporariamente acesso
restrito por HTTP para testes ou forneça certificados próprios; não anuncie HTTPS como funcional.

O Caddy remove qualquer `X-Forwarded-For` recebido do cliente e cria o cabeçalho a partir da conexão
real. Somente defina `PROXY_REVERSO_CONFIAVEL=true` na composição de produção, onde `app` não possui
porta pública e recebe tráfego exclusivamente do Caddy. Nunca publique a porta da aplicação junto
com essa opção.

## Preparação da instância

1. Crie Security Group: TCP 80 e 443 de `0.0.0.0/0` e `::/0`; TCP 22 apenas do IP/VPN administrativo.
   Não adicione 3306.
2. Associe uma Elastic IP se precisar manter o endereço público entre paradas. Ela pode ter custo quando
   não estiver associada a uma instância em execução. Registre no DNS um domínio e confirme que aponta
   para o IP antes de iniciar o Caddy.
3. Atualize o Ubuntu, instale Docker Engine e o plugin Docker Compose pelas instruções oficiais da
   distribuição/Docker, e permita Docker somente a administradores confiáveis. O grupo `docker` equivale
   a privilégios elevados na máquina.
4. Clone o repositório em diretório pertencente ao administrador. Crie `.env` com permissão `600`, sem
   valores de exemplo ou senhas previsíveis; mantenha a chave SSH em local protegido e também com
   permissões restritas.
5. Use `APP_ENV=production`, `SESSAO_COOKIE_SECURE=true`,
   `ADMIN_BOOTSTRAP_HABILITADO=false`, `PROXY_REVERSO_CONFIAVEL=true` e uma chave aleatória exclusiva
   em `APP_CHAVE_RATE_LIMIT`. Não envie o `.env` para Git, imagens ou logs.
6. Valide `docker compose --env-file .env -f compose.prod.yaml config --quiet`, então suba a composição.
   Verifique `docker compose -f compose.prod.yaml ps`, logs e login antes de liberar usuários.

Mantenha atualizações de segurança do Ubuntu e das imagens em rotina controlada. Centralize ou retenha
logs do Docker/Apache conforme a política operacional e monitore CPU, memória, saúde dos contêineres,
espaço no EBS e expiração de certificados.

## Banco, persistência e recuperação

O schema é importado por `/docker-entrypoint-initdb.d` apenas quando `mysql_data` é criado pela primeira
vez. Reinícios e `docker compose down` preservam o volume. Não use `down -v` em produção.

Exemplo de backup lógico (guarde o arquivo fora da EC2, cifrado e com acesso restrito):

```sh
docker compose -f compose.prod.yaml exec -T db sh -c 'mysqldump -u"$MYSQL_USER" -p"$MYSQL_PASSWORD" "$MYSQL_DATABASE"' > workhub-AAAA-MM-DD.sql
```

Restaure somente em ambiente de recuperação previamente testado:

```sh
docker compose -f compose.prod.yaml exec -T db sh -c 'mysql -u"$MYSQL_USER" -p"$MYSQL_PASSWORD" "$MYSQL_DATABASE"' < workhub-AAAA-MM-DD.sql
```

Backup não é considerado válido até que uma restauração seja testada em banco isolado e a aplicação
seja verificada. Programe cópias externas, retenção, verificação de criptografia EBS em repouso,
monitoramento de disco e um procedimento de recuperação com RPO/RTO definidos.

## Checklist de implantação

- [ ] Instância, EBS criptografado, Security Group e SSH restrito revisados.
- [ ] Elastic IP e DNS definidos, se acesso público permanente for necessário.
- [ ] `.env` de produção criado com segredos fortes e permissões restritas.
- [ ] MySQL sem porta pública e aplicativo sem porta pública direta.
- [ ] Compose validado, serviços saudáveis e dados persistentes após reinício.
- [ ] HTTP redirecionado/servido por Caddy e HTTPS testado com domínio real.
- [ ] Login, CSRF, RBAC, isolamento multiempresa e páginas de erro testados no ambiente implantado.
- [ ] Backup externo e restauração em ambiente isolado testados.
- [ ] Logs, atualizações, espaço em disco e alertas operacionais definidos.

Pendências bloqueantes: domínio/DNS e teste real de HTTPS, provisionamento seguro da EC2, segredos de
produção e restauração de backup testada. Importantes: monitoramento, retenção de logs e teste de carga
na capacidade da instância. Opcionais: Elastic IP quando o IP público já for estável e aceitável.

## Demonstração pública por IPv4 e HTTP

Para a demonstração acadêmica da Etapa 10M, use `compose.demo.yaml`, e não a composição de produção
descrita acima. Esse compose não utiliza Caddy, domínio, HTTPS ou Elastic IP. Ele publica somente
`app` na porta TCP 80; o MySQL 8.4 não tem `ports` e permanece somente na rede Docker. O volume
`mysql_demo_data` persiste após reinicializações e após `docker compose down` normal. Nunca execute
`docker compose down -v`.

`APP_ENV=demo` é fixado no compose: erros e stack traces não são exibidos a visitantes, mas continuam
nos logs técnicos. Autenticação, CSRF, rate limiting, RBAC e isolamento multiempresa permanecem
ativos. Apenas este compose define `SESSAO_COOKIE_SECURE=false`, necessária ao HTTP; os cookies ainda
são `HttpOnly` e `SameSite=Lax`. O bootstrap de administrador fica desativado. Os limites são 512 MiB
para a aplicação e 768 MiB para MySQL, com buffer pool de 256 MiB, adequados como ponto inicial para
a `t3.small` de 2 GiB. Ambos os serviços usam `restart: unless-stopped` e healthchecks.

### Console AWS

1. Em **EC2 > Instances**, localize a EC2 existente `workhub-server`, confirme `Running` e anote o
   **Public IPv4 address** atual. Não crie outra instância nem associe Elastic IP.
2. Em **Security Groups > [grupo da instância] > Edit inbound rules**, adicione HTTP TCP 80 de
   `0.0.0.0/0`. Mantenha SSH TCP 22 apenas no IPv4 administrativo autorizado (por exemplo,
   `203.0.113.10/32`). Não abra TCP 3306 nem TCP 443 e não remova uma rota SSH necessária.

### Ubuntu e publicação

Conecte-se sem colocar a chave privada no repositório e inventarie a instância:

```sh
ssh -i /caminho/protegido/chave.pem ubuntu@IP_PUBLICO_DA_EC2
uname -a
docker --version || true
docker compose version || true
git --version || true
free -h
nproc
df -h /
```

Se necessário, instale Git e Docker Engine com o repositório oficial Docker para Ubuntu 24.04:

```sh
sudo apt update
sudo apt install -y git ca-certificates curl
sudo install -m 0755 -d /etc/apt/keyrings
sudo curl -fsSL https://download.docker.com/linux/ubuntu/gpg -o /etc/apt/keyrings/docker.asc
sudo chmod a+r /etc/apt/keyrings/docker.asc
sudo tee /etc/apt/sources.list.d/docker.sources > /dev/null <<EOF
Types: deb
URIs: https://download.docker.com/linux/ubuntu
Suites: $(. /etc/os-release && echo "${UBUNTU_CODENAME:-$VERSION_CODENAME}")
Components: stable
Architectures: $(dpkg --print-architecture)
Signed-By: /etc/apt/keyrings/docker.asc
EOF
sudo apt update
sudo apt install -y docker-ce docker-ce-cli containerd.io docker-buildx-plugin docker-compose-plugin
sudo docker run hello-world
sudo usermod -aG docker $USER
```

Abra uma nova sessão SSH antes de usar Docker sem `sudo`. Não remova Docker ou dados existentes sem
antes verificar se há uma instalação em uso; a documentação oficial lista os pacotes conflitantes e o
procedimento de avaliação.

Clone a versão revisada, crie o arquivo de segredos apenas na EC2 e valide o compose:

```sh
git clone URL_DO_REPOSITORIO workhub
cd workhub
cp .env.demo.example .env.demo
chmod 600 .env.demo
openssl rand -hex 32
editor .env.demo
docker compose --env-file .env.demo -f compose.demo.yaml config --quiet
docker compose --env-file .env.demo -f compose.demo.yaml up -d --build
docker compose --env-file .env.demo -f compose.demo.yaml ps
docker compose --env-file .env.demo -f compose.demo.yaml logs --tail=100 app db
curl -i http://127.0.0.1/
```

Preencha senhas fortes em `.env.demo`; `DB_PASSWORD` precisa ser igual a `MYSQL_PASSWORD`. O schema é
inicializado somente ao criar `mysql_demo_data`; não execute SQL destrutivo nem remova esse volume.

Se não houver administrador, crie `admin@admin.com` uma única vez, de forma interativa, para que a
senha `teste123` não seja registrada em arquivo ou log:

```sh
docker compose --env-file .env.demo -f compose.demo.yaml exec app php scripts/criar_administrador.php
```

Se o e-mail já existir, não altere a conta automaticamente.

### Testes e operação

Execute as verificações de conexão, schema, autenticação, administração, segurança, clientes,
produtos, pedidos, detalhes de cliente e dashboard, usando o mesmo prefixo:

```sh
docker compose --env-file .env.demo -f compose.demo.yaml exec app php scripts/verificar_conexao_banco.php
docker compose --env-file .env.demo -f compose.demo.yaml exec app php scripts/verificar_banco.php
docker compose --env-file .env.demo -f compose.demo.yaml exec app php scripts/testar_autenticacao.php
docker compose --env-file .env.demo -f compose.demo.yaml exec app php scripts/testar_administracao.php
docker compose --env-file .env.demo -f compose.demo.yaml exec app php scripts/testar_seguranca_validacoes.php
```

Complete a navegação pública em `http://IP_PUBLICO_DA_EC2/`: landing, cadastro empresarial,
login/logout, dashboard, clientes, produtos, pedidos, histórico e detalhes, produção, Minha Empresa,
administração e páginas institucionais. Confirme CSS, JavaScript, imagens, favicon, ausência de erros
PHP e isolamento entre empresas fictícias. O acesso externo só estará validado ao abrir essa URL em
um celular com Wi-Fi desligado, usando 4G/5G.

Para reiniciar sem apagar dados:

```sh
cd ~/workhub
docker compose --env-file .env.demo -f compose.demo.yaml restart
docker compose --env-file .env.demo -f compose.demo.yaml ps
```

Para atualizar código posteriormente, revise primeiro variáveis e schema, depois use
`git pull --ff-only`, `docker compose --env-file .env.demo -f compose.demo.yaml config --quiet` e
`docker compose --env-file .env.demo -f compose.demo.yaml up -d --build`.
