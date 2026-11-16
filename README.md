# WorkHub — Etapa 1

Base inicial do WorkHub, uma plataforma de gestão empresarial. Esta etapa contém somente a estrutura MVC simplificada e o ambiente Docker; não há autenticação, CRUD, tabelas de negócio ou regras de negócio.

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

## Verificar a conexão

Após os serviços estarem saudáveis, execute:

```powershell
docker compose exec app php scripts/check_database.php
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
database/     Scripts SQL (sem tabelas de negócio nesta etapa)
scripts/      Verificações executadas apenas no terminal
docker/       Configuração do Apache
```
