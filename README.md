# WorkHub

![PHP](https://img.shields.io/badge/PHP-8.3-777BB4?style=flat-square&logo=php&logoColor=white)
![MySQL](https://img.shields.io/badge/MySQL-8.4-4479A1?style=flat-square&logo=mysql&logoColor=white)
![Apache](https://img.shields.io/badge/Apache-2.4-D22128?style=flat-square&logo=apache&logoColor=white)
![Bootstrap](https://img.shields.io/badge/Bootstrap-5-7952B3?style=flat-square&logo=bootstrap&logoColor=white)
![Docker Compose](https://img.shields.io/badge/Docker_Compose-v2-2496ED?style=flat-square&logo=docker&logoColor=white)

O WorkHub é uma plataforma web multiempresa para gestão de negócios, produtos, clientes, pedidos e acompanhamento da produção. Ela organiza processos operacionais de pequenos negócios e facilita o acompanhamento de pedidos desde o registro até a entrega.

É um projeto acadêmico, com arquitetura e experiência de uso inspiradas em sistemas empresariais reais. A proposta se relaciona à ODS 9 - Indústria, Inovação e Infraestrutura - ao explorar software como apoio à organização de operações produtivas.

<p align="center">
  <img src="public/assets/images/banner.png" alt="WorkHub - Plataforma de Gestão Empresarial Multiempresa" width="100%">
</p>

---

## Índice

- [Arquitetura](#arquitetura)
- [Funcionalidades](#funcionalidades)
- [Modelo multiempresa e permissões](#modelo-multiempresa-e-permissões)
- [Como executar](#como-executar)
- [Como testar](#como-testar)
- [Segurança](#segurança)
- [Configuração](#configuração)
- [Stack](#stack)
- [Estrutura do projeto](#estrutura-do-projeto)
- [Status do projeto](#status-do-projeto)
- [Decisões de arquitetura](#decisões-de-arquitetura)

## Arquitetura

O WorkHub é um monólito web em PHP. O Apache entrega a aplicação e encaminha rotas não estáticas para `public/index.php`. O router seleciona o controller, que trata a requisição, usa os models e renderiza as views. A persistência usa MySQLi procedural e MySQL.

~~~
Navegador
    |
    | HTTP
    v
Apache + PHP 8.3
    |
    v
public/index.php
    |
    v
controller/router.php
    |
    v
Controller -----> View (HTML + Bootstrap)
    |
    v
Model (MySQLi procedural)
    |
    v
MySQL 8.4
~~~

| Componente | Responsabilidade |
| --- | --- |
| `public/` | Ponto de entrada HTTP e ativos estáticos; é a raiz configurada no Apache. |
| `controller/router.php` | Resolve caminhos e delega cada rota ao controller correspondente. |
| `controller/` | Valida requisições, exige autenticação e permissões, coordena models e escolhe views. |
| `model/` | Executa consultas preparadas MySQLi e transações de persistência. |
| `view/` | Renderiza áreas pública, empresarial e administrativa em HTML com Bootstrap 5. |
| `config/` | Configura conexão, sessão, CSRF, formatação e tratamento de erros. |
| MySQL | Armazena empresas, usuários, cadastros operacionais, pedidos e histórico de status. |

Há três superfícies: a área pública, com landing, cadastro de empresa e entrada; a área empresarial, protegida por sessão e vinculada a uma empresa; e a administração global, com autenticação própria. A aplicação não é formada por microsserviços nem expõe uma API REST independente.

## Funcionalidades

| Módulo | Responsabilidade |
| --- | --- |
| Cadastro de empresas | Registra empresa e primeiro responsável em uma transação; o responsável recebe o perfil `PROPRIETARIO`. |
| Autenticação empresarial | Autentica usuários de empresas ativas e inicia a sessão empresarial. |
| Usuários | Permite ao proprietário incluir, editar e ativar/desativar gerentes e funcionários da própria empresa. |
| Produtos | Lista e pesquisa produtos; proprietário e gerente podem cadastrar, editar e alterar sua situação. |
| Clientes | Mantém dados de clientes, pesquisa, consulta detalhes e exibe o histórico de pedidos do cliente. |
| Pedidos | Cria pedidos com itens, calcula o total, permite filtros, consulta detalhes e edita pedidos em etapas iniciais. |
| Produção | Organiza pedidos por status, mostra prazos e permite avançar o fluxo ou cancelar quando autorizado. |
| Dashboard empresarial | Apresenta indicadores do período, pedidos recentes e alertas de prazo da empresa logada. |
| Minha Empresa | Permite ao proprietário atualizar os dados cadastrais da própria empresa. |
| Administração global | Mantém painel de indicadores, consulta empresas e permite ativá-las ou desativá-las. |

### Fluxo de pedidos

~~~
Cadastro do pedido
        |
        v
    RECEBIDO
        |
        v
 EM_PRODUCAO
        |
        v
      PRONTO
        |
        v
     ENTREGUE

CANCELADO pode ser aplicado por PROPRIETARIO ou GERENTE
enquanto o pedido não estiver ENTREGUE ou CANCELADO.
~~~

As transições normais só avançam na ordem apresentada. Cada alteração registra status anterior, novo status, usuário responsável e data no histórico do pedido.

## Modelo multiempresa e permissões

Cada usuário empresarial pertence a uma empresa. O contexto da sessão é consultado no banco em cada requisição protegida, verificando usuário e empresa ativos. Controllers e models usam o `empresa_id` desse contexto em consultas e alterações operacionais.

O schema reforça essa separação com chaves estrangeiras e vínculos compostos entre empresas, usuários, clientes, produtos, pedidos, itens e histórico. Um pedido, por exemplo, só pode referenciar cliente e usuário da mesma empresa. As verificações de autorização permanecem no backend; a interface não é a única barreira de acesso.

`ADMIN_PLATAFORMA` é uma identidade separada, armazenada em `administradores_plataforma`. Ela acessa a administração da plataforma e não substitui os perfis internos de empresas.

| Ação confirmada | PROPRIETARIO | GERENTE | FUNCIONARIO | ADMIN_PLATAFORMA |
| --- | --- | --- | --- | --- |
| Acessar dashboard, produtos, clientes e pedidos da empresa | Sim | Sim | Sim | Não |
| Consultar detalhes de cliente e histórico de pedidos | Sim | Sim | Sim | Não |
| Gerenciar usuários da empresa | Sim | Não | Não | Não |
| Atualizar Minha Empresa | Sim | Não | Não | Não |
| Cadastrar, editar ou ativar/desativar produtos e clientes | Sim | Sim | Não | Não |
| Criar pedido e avançar seu status | Sim | Sim | Sim | Não |
| Editar ou cancelar pedido | Sim | Sim | Não | Não |
| Consultar e ativar/desativar empresas globalmente | Não | Não | Não | Sim |

O proprietário não pode ser alterado ou desativado pelo módulo de usuários. O banco mantém um único proprietário por empresa.

## Como executar

### Pré-requisitos

- [Git](https://git-scm.com/)
- Docker Engine ou Docker Desktop
- Docker Compose v2

PHP, Apache e MySQL são executados nos contêineres; não é necessário instalá-los no host.

### Instalação local

Clone o repositório e entre na pasta:

~~~sh
git clone https://github.com/LS-PLuka/WorkHub.git
cd WorkHub
~~~

Crie o arquivo local de ambiente:

~~~powershell
Copy-Item .env.example .env
~~~

~~~sh
cp .env.example .env
~~~

Edite o `.env` e substitua senhas do MySQL, as credenciais do administrador inicial e `APP_CHAVE_RATE_LIMIT`. Mantenha `DB_NAME`, `DB_USER` e `DB_PASSWORD` compatíveis com `MYSQL_DATABASE`, `MYSQL_USER` e `MYSQL_PASSWORD`.

Inicie os serviços e confira seu estado:

~~~sh
docker compose up -d --build
docker compose ps
~~~

A aplicação fica disponível em [http://localhost:8080](http://localhost:8080), salvo se `APP_PORT` tiver sido alterada. A porta do MySQL não é publicada no host.

O `database/schema.sql` é montado no diretório de inicialização do MySQL e é aplicado somente na primeira criação do volume. O volume `mysql_data` persiste após `docker compose down`.

### Primeiro acesso administrativo local

Com `APP_ENV=local` e `ADMIN_BOOTSTRAP_HABILITADO=true`, o entrypoint cria o administrador informado em `ADMIN_INICIAL_NOME`, `ADMIN_INICIAL_EMAIL` e `ADMIN_INICIAL_SENHA` somente quando ainda não há um administrador. O bootstrap não redefine senhas existentes.

Entre por [/administracao/entrar](http://localhost:8080/administracao/entrar) usando os valores do seu `.env`. Para criar outro administrador de forma interativa dentro do contêiner:

~~~sh
docker compose exec app php scripts/criar_administrador.php
~~~

As composições `compose.demo.yaml` e `compose.prod.yaml` atendem cenários específicos e exigem configuração própria. A implantação na AWS EC2 segue planejada; consulte [docs/deploy_aws_ec2.md](docs/deploy_aws_ec2.md) antes de usá-las.

## Como testar

### Fluxo funcional sugerido

1. Na página inicial, cadastre empresa e proprietário em `/cadastro-empresa`.
2. Entre em `/entrar` com o proprietário e abra o dashboard.
3. Em **Usuários**, cadastre gerente e funcionário.
4. Cadastre produtos e clientes.
5. Crie um pedido com ao menos um produto e data de entrega.
6. Consulte seus detalhes e, se necessário, edite-o enquanto estiver em `RECEBIDO` ou `EM_PRODUCAO`.
7. Em **Produção**, avance por `EM_PRODUCAO`, `PRONTO` e `ENTREGUE`; o cancelamento é restrito a proprietário e gerente.
8. Consulte o dashboard e os detalhes do cliente para conferir indicadores, alertas e histórico.

### Scripts de verificação

Os scripts abaixo existem no repositório e devem ser executados com os contêineres locais em execução. Os scripts transacionais usam `ROLLBACK` e não preservam seus dados de teste.

~~~sh
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
~~~

Esses scripts não foram executados durante esta atualização documental, pois ela não inicia nem altera o ambiente local.

## Segurança

A implementação atual inclui:

- Senhas geradas e verificadas com `PASSWORD_BCRYPT`.
- Sessões PHP com cookie `HttpOnly`, `SameSite=Lax`, regeneração do identificador no login e expiração após 30 minutos de inatividade.
- Autorização por perfil no backend, inclusive na área administrativa.
- Isolamento por `empresa_id` no contexto, nas consultas e nas operações empresariais.
- Consultas preparadas MySQLi e conexão com charset `utf8mb4`.
- Tokens CSRF em formulários mutáveis.
- Bloqueio de novas tentativas de login após cinco falhas por 15 minutos, identificado por HMAC de e-mail e origem.
- Em `production` e `demo`, registro de erros sem exposição de detalhes técnicos ao visitante.

Esses controles reduzem riscos comuns, mas não substituem revisão de segurança e configuração adequada para cada ambiente.

## Configuração

Copie `.env.example` para `.env`; o arquivo preenchido não deve ser versionado.

| Grupo | Variável | Finalidade |
| --- | --- | --- |
| MySQL | `MYSQL_DATABASE` | Banco criado pelo contêiner MySQL. |
| MySQL | `MYSQL_USER`, `MYSQL_PASSWORD` | Credenciais da aplicação no MySQL. |
| MySQL | `MYSQL_ROOT_PASSWORD` | Senha administrativa do servidor MySQL. |
| Conexão PHP/MySQL | `DB_HOST`, `DB_PORT` | Host e porta da conexão da aplicação. |
| Conexão PHP/MySQL | `DB_NAME`, `DB_USER`, `DB_PASSWORD` | Banco e credenciais usados pelo PHP. |
| Aplicação | `APP_ENV` | Ambiente da aplicação; `local` habilita comportamento de desenvolvimento. |
| Aplicação | `APP_PORT` | Porta publicada no ambiente local; padrão `8080`. |
| Bootstrap administrativo | `ADMIN_BOOTSTRAP_HABILITADO` | Habilita o bootstrap local do primeiro administrador. |
| Bootstrap administrativo | `ADMIN_INICIAL_NOME`, `ADMIN_INICIAL_EMAIL`, `ADMIN_INICIAL_SENHA` | Dados do primeiro administrador local. |
| Sessões e proxy | `SESSAO_COOKIE_SECURE` | Define o atributo `Secure` do cookie de sessão. |
| Sessões e proxy | `PROXY_REVERSO_CONFIAVEL` | Permite confiar em `X-Forwarded-For` apenas atrás do proxy configurado. |
| Rate limiting | `APP_CHAVE_RATE_LIMIT` | Chave HMAC para identificação de tentativas de login. |
| Produção | `WORKHUB_DOMINIO` | Domínio consumido pelo Caddy na composição de produção. |

`compose.demo.yaml` utiliza `.env.demo`, baseado em `.env.demo.example`, e fixa `APP_ENV=demo`. `compose.prod.yaml` exige variáveis de banco, `APP_CHAVE_RATE_LIMIT` e `WORKHUB_DOMINIO`, além de desabilitar o bootstrap administrativo.

## Stack

| Tecnologia | Versão | Responsabilidade |
| --- | --- | --- |
| PHP | 8.3 | Aplicação web procedural e regras de negócio. |
| Apache HTTP Server | 2.4 (imagem `php:8.3-apache`) | Servidor web e encaminhamento de rotas. |
| MySQL | 8.4 | Persistência relacional. |
| MySQLi | Extensão nativa do PHP 8.3 | Consultas preparadas e acesso procedural ao MySQL. |
| Bootstrap | 5 | Componentes e layout das views. |
| HTML5 e CSS3 | - | Estrutura e apresentação das páginas. |
| JavaScript | - | Interações visuais e máscaras de formulário. |
| Docker Compose | v2 | Subida padronizada de desenvolvimento e implantação. |

AWS EC2 está documentada como infraestrutura planejada para demonstração e produção; a publicação pública não é declarada como concluída.

## Estrutura do projeto

~~~
WorkHub/
├── config/                 # Conexão, sessão, erros e configuração
├── controller/             # Router e controllers
├── model/                  # Consultas MySQLi e persistência
├── view/                   # Views públicas, empresariais e administrativas
├── public/                 # Front controller e ativos do Apache
├── database/               # Schema inicial do MySQL
├── docker/                 # Apache, entrypoint e Caddy
├── scripts/                # Bootstrap e verificações de terminal
├── docs/                   # Guia de implantação e referências
├── compose.yaml            # Ambiente local
├── compose.demo.yaml       # Demonstração HTTP
├── compose.prod.yaml       # Produção com Caddy
└── Dockerfile              # Imagem PHP 8.3 + Apache
~~~

## Status do projeto

| Área | Estado atual |
| --- | --- |
| Funcionalidades empresariais e administrativas | Implementadas conforme controllers, models e views presentes no repositório. |
| Scripts de verificação | Disponíveis em `scripts/`; não executados nesta atualização do README. |
| Ambiente Docker local | Preparado por `compose.yaml` com aplicação e MySQL 8.4. |
| Demonstração e produção | Composições e guia de preparação disponíveis; implantação AWS EC2 pendente de validação real. |

## Decisões de arquitetura

- **Monólito MVC simplificado:** adequado ao escopo acadêmico e à entrega integrada de páginas, regras e persistência, sem complexidade de serviços distribuídos.
- **PHP procedural com MySQLi:** abordagem direta e legível, com consultas preparadas em vez de uma camada ORM.
- **MySQL relacional:** chaves estrangeiras e vínculos compostos contribuem para a consistência entre empresas, usuários, clientes, produtos e pedidos.
- **Isolamento lógico multiempresa:** `empresa_id` acompanha contexto, consultas e relacionamentos operacionais.
- **Sessões PHP e RBAC:** sessão identifica o usuário e perfis delimitam as operações permitidas.
- **Docker Compose:** padroniza PHP/Apache e MySQL sem instalação local dessas dependências.
- **Desativação lógica:** empresas, usuários, produtos, clientes e pedidos possuem situação ativa em vez de exclusão física nos fluxos de negócio.
- **Histórico de status:** cada alteração de pedido é registrada para acompanhar a produção e identificar o responsável.

Desenvolvido por [Pedro Luka](https://github.com/LS-PLuka) · [LinkedIn](https://linkedin.com/in/pedroluka-dev)
