# WorkHub — Etapa 3

Base inicial do WorkHub, uma plataforma de gestão empresarial. Esta etapa inclui o ambiente Docker, o schema relacional e o cadastro público transacional de empresas com seu primeiro Proprietário. Não há autenticação, login, dashboard ou CRUDs de negócio.

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
docker compose exec app php scripts/check_database.php
```

Para validar tabelas, colunas e as principais restrições de integridade:

```powershell
docker compose exec app php scripts/verify_database.php
```

Os testes usam uma transação com `ROLLBACK`, portanto não mantêm dados fictícios.

## Cadastro de empresas

A página pública de cadastro está disponível em [http://localhost:8080/cadastro-empresa](http://localhost:8080/cadastro-empresa). Ela cria a empresa ativa e um único usuário ativo com perfil `PROPRIETARIO` em uma transação MySQL.

Para executar os testes específicos do cadastro, sem persistir dados fictícios:

```powershell
docker compose exec app php scripts/test_company_registration.php
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
