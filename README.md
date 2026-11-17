# WorkHub — Etapa 8A

Base do WorkHub com ambiente Docker, cadastro empresarial, autenticação, usuários, produtos, clientes,
pedidos, acompanhamento de produção e dashboard empresarial por sessão.

## Requisitos

- Docker Desktop com Docker Compose v2

## Executar

1. Crie o arquivo local de variáveis e altere as senhas de exemplo:

   ```powershell
   Copy-Item .env.example .env
   ```

2. Inicie os serviços:

   ```powershell
   docker compose up -d --build
   ```

3. Abra [http://localhost:8080](http://localhost:8080).

O MySQL não publica porta para a máquina host. Os dados ficam no volume nomeado `mysql_data` e persistem entre reinicializações dos contêineres.

## Aplicar o schema

Em um volume novo, o MySQL executa automaticamente os scripts de `database/` na primeira inicialização. Para aplicar o schema em um banco já inicializado, sem apagar volume ou banco, execute:

```powershell
Get-Content -Raw database/schema.sql | docker compose exec -T db sh -c 'mysql -u"$MYSQL_USER" -p"$MYSQL_PASSWORD" "$MYSQL_DATABASE"'
```

O script usa `CREATE TABLE IF NOT EXISTS`; ele não executa `DROP DATABASE`, `DROP TABLE` ou exclusões de dados.

## Verificar banco e integridade

Após os serviços estarem saudáveis, execute:

```powershell
docker compose exec app php scripts/verificar_conexao_banco.php
```

Para validar tabelas, colunas e as principais restrições de integridade:

```powershell
docker compose exec app php scripts/verificar_banco.php
```

Os testes usam uma transação com `ROLLBACK`, portanto não mantêm dados fictícios.

## Cadastro de empresas

A página pública de cadastro está disponível em [http://localhost:8080/cadastro-empresa](http://localhost:8080/cadastro-empresa). Ela cria a empresa ativa e um único usuário ativo com perfil `PROPRIETARIO` em uma transação MySQL.

Para executar os testes específicos do cadastro, sem persistir dados fictícios:

```powershell
docker compose exec app php scripts/testar_cadastro_empresa.php
```

## Autenticação

A entrada está disponível em [http://localhost:8080/entrar](http://localhost:8080/entrar). Após autenticar, o usuário empresarial ativo é direcionado para `/painel`. A saída usa POST protegido por token CSRF.

```powershell
docker compose exec app php scripts/testar_autenticacao.php
```

## Usuários

O gerenciamento de usuários é exclusivo do Proprietário e está disponível em `/usuarios` após a autenticação.

```powershell
docker compose exec app php scripts/testar_usuarios.php
```

## Minha Empresa

O Proprietário consulta e atualiza os dados cadastrais de sua própria empresa em
`/minha-empresa`. O CNPJ e a situação cadastral são apenas informativos; o formulário permite
alterar razão social, nome fantasia, e-mail de contato e telefone.

```powershell
docker compose exec app php scripts/testar_minha_empresa.php
```

## Dashboard empresarial

O dashboard está disponível em `/dashboard`; `/painel` permanece como rota compatível para a mesma página.
Proprietário, Gerente e Funcionário visualizam apenas dados da empresa presente na sessão validada no servidor.

Os indicadores consideram somente pedidos com `ativo = 1`. O total inclui todos os status ativos, inclusive
`CANCELADO`; os cartões de produção contam apenas seu status. O valor do período soma `pedidos.valor_total`
pela data de criação, incluindo as datas inicial e final, e exclui pedidos cancelados. Ele representa pedidos
registrados, não recebimentos. Os valores usam `DECIMAL` no banco e são somente formatados para exibição.

Alertas de prazo excluem pedidos entregues e cancelados: atrasados possuem entrega anterior a hoje, e próximos
do vencimento possuem entrega entre hoje e os dois dias seguintes, inclusive.

```powershell
docker compose exec app php scripts/testar_dashboard.php
```

## Administração da plataforma

A área global está em `/administracao` e utiliza autenticação própria em
`/administracao/entrar`. Ela é isolada das contas empresariais e permite consultar empresas, seus dados
cadastrais, indicadores de situação e ativar ou desativar empresas sem apagar dados.

Para criar o primeiro administrador, execute exclusivamente no terminal do contêiner e informe os dados quando
solicitado. A senha não é exibida nem armazenada no código-fonte:

```powershell
docker compose exec -it app php scripts/criar_administrador.php
```

### Bootstrap local opcional

Para preparar automaticamente apenas o primeiro administrador durante a inicialização local do contêiner, adicione
as variáveis abaixo ao arquivo `.env` local. O arquivo é ignorado pelo Git e não deve receber credenciais de produção:

```dotenv
APP_ENV=local
ADMIN_BOOTSTRAP_HABILITADO=true
ADMIN_INICIAL_NOME=Administrador local
ADMIN_INICIAL_EMAIL=<e-mail-local-do-administrador>
ADMIN_INICIAL_SENHA=<senha-local-com-ao-menos-8-caracteres>
```

O bootstrap só atua em `local`, `development` ou `desenvolvimento`, e somente quando habilitado explicitamente. Em
produção, ele é ignorado mesmo se a variável de habilitação estiver definida. A conta é criada apenas quando não há
nenhum administrador da plataforma; reinicializações não criam duplicatas nem alteram senhas. O script CLI manual
continua sendo o procedimento indicado para ambientes de produção.

```powershell
docker compose exec app php scripts/testar_administracao.php
```

```powershell
docker compose exec app php scripts/testar_bootstrap_administrador.php
```

## Clientes

O gerenciamento de clientes está disponível em `/clientes` para usuários autenticados. O histórico de pedidos de cada cliente será implementado após a criação do módulo de pedidos.

```powershell
docker compose exec app php scripts/testar_clientes.php
```

## Parar o ambiente

```powershell
docker compose down
```

Use `docker compose down -v` somente se quiser apagar deliberadamente os dados persistidos do banco.

## Estrutura

```text
config/       Configuração de banco de dados
controller/   Controle de rotas e página inicial
model/        Funções de acesso a dados (reservado para próximas etapas)
view/         Páginas visuais
public/       Único diretório servido pelo Apache
database/     Schema SQL e scripts de banco
scripts/      Verificações executadas apenas no terminal
docker/       Configuração do Apache
```
