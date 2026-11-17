<?php

$tituloPagina = "Privacidade e Proteção de Dados | WorkHub";
require __DIR__ . "/../view/layout_publico_inicio.php";

?>
<article class="workhub-legal-page">
    <header class="workhub-legal-page__header">
        <p class="workhub-eyebrow">WorkHub</p>
        <h1>Privacidade e Proteção de Dados</h1>
        <p class="workhub-legal-page__summary">Como o WorkHub organiza o acesso e a proteção das informações na plataforma.</p>
        <p class="workhub-legal-page__version">Atualizado em 17 de novembro de 2026.</p>
    </header>
    <div class="workhub-legal-content">
        <h2>1. Uso responsável das informações</h2>
        <p>O WorkHub organiza dados cadastrais e operacionais necessários à gestão empresarial. A plataforma busca aplicar controles compatíveis com suas funcionalidades e apresentar de forma clara as práticas relacionadas ao acesso e à proteção das informações.</p>
        <h2>2. Separação dos ambientes empresariais</h2>
        <p>Cada usuário empresarial atua no ambiente da empresa a que está vinculado. A aplicação verifica esse vínculo nas operações de produtos, clientes, pedidos, produção e indicadores, mantendo a separação lógica desses registros.</p>
        <h2>3. Perfis e permissões</h2>
        <p>O Proprietário pode administrar usuários e dados da própria empresa. Gerentes realizam operações de gestão liberadas ao seu perfil, enquanto Funcionários acessam os recursos operacionais permitidos. A administração global da plataforma utiliza conta e sessão administrativas próprias.</p>
        <h2>4. Proteção de acesso e operações</h2>
        <p>O WorkHub utiliza senha armazenada por hash bcrypt, autenticação por sessão, expiração por inatividade e renovação da sessão após o login. As operações de formulário usam tokens de proteção, e as tentativas de autenticação são limitadas para reduzir tentativas repetidas de acesso.</p>
        <h2>5. Papel dos usuários</h2>
        <p>Usuários devem utilizar contas individuais, proteger suas credenciais, conferir as informações antes de registrá-las e solicitar ao Proprietário da empresa a atualização ou retirada de acessos que não sejam mais necessários.</p>
        <h2>6. Boas práticas de segurança</h2>
        <p>Recomenda-se usar senhas exclusivas, encerrar a sessão em equipamentos compartilhados, manter o navegador atualizado e não compartilhar dados de acesso. Essas práticas complementam os controles presentes na aplicação.</p>
        <h2>7. Relação com a privacidade</h2>
        <p>Controles de acesso e separação de dados contribuem para reduzir acessos indevidos a informações pessoais e empresariais. Para o detalhamento das categorias de dados, finalidades e retenção, consulte a <a href="/politica-de-privacidade.php">Política de Privacidade</a>.</p>
    </div>
</article>
<?php require __DIR__ . "/../view/layout_publico_fim.php"; ?>
