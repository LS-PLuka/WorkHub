<?php

$tituloPagina = "Política de Cookies | WorkHub";
require __DIR__ . "/../view/layout_publico_inicio.php";

?>
<article class="workhub-legal-page">
    <header class="workhub-legal-page__header">
        <p class="workhub-eyebrow">Documento institucional</p>
        <h1>Política de Cookies</h1>
        <p class="workhub-legal-page__summary">Informações sobre os cookies identificados na implementação atual.</p>
        <p class="workhub-legal-page__version">Versão preliminar — 17 de novembro de 2026.</p>
    </header>
    <div class="workhub-legal-content">
        <p class="workhub-legal-pending">
            Esta política retrata a implementação atual. Qualquer inclusão futura de cookies analíticos, publicitários ou de terceiros
            exige nova avaliação, atualização desta página e definição do mecanismo aplicável.
        </p>
        <h2>1. O que são cookies</h2>
        <p>
            Cookies são pequenos dados enviados pelo site ao navegador para apoiar funções específicas. O WorkHub usa cookie técnico
            de sessão para disponibilizar recursos que dependem do estado de acesso e da proteção dos formulários.
        </p>
        <h2>2. Cookie necessário identificado</h2>
        <p>
            A aplicação utiliza o cookie de sessão do PHP. Ele é necessário para manter a sessão de navegação, reconhecer o estado
            de autenticação e vincular os tokens de proteção CSRF usados nos formulários. A sessão é encerrada no navegador ao fim
            de sua duração e também pode ser encerrada pelo usuário ao sair da conta.
        </p>
        <p>
            No código atual, esse cookie é configurado com caminho raiz, atributo <code>HttpOnly</code>, <code>SameSite=Lax</code>
            e atributo <code>Secure</code> quando o acesso ocorre por HTTPS.
        </p>
        <h2>3. Cookies opcionais</h2>
        <p>
            Não foram identificados cookies opcionais, ferramentas de análise de audiência, rastreadores publicitários ou scripts de
            terceiros nas páginas públicas. Por isso, não foi implementado banner de consentimento nesta etapa.
        </p>
        <h2>4. Controle pelo usuário</h2>
        <p>
            O navegador permite gerenciar cookies. A desativação do cookie necessário pode impedir o login, o uso de formulários e
            outras funcionalidades que dependem da sessão.
        </p>
        <h2>5. Referência</h2>
        <p>
            Esta classificação considera a orientação da ANPD para cookies necessários, isto é, aqueles essenciais ao funcionamento
            da página ou do serviço solicitado. Consulte também a <a href="/politica-de-privacidade.php">Política de Privacidade</a>.
        </p>
    </div>
</article>
<?php require __DIR__ . "/../view/layout_publico_fim.php"; ?>
