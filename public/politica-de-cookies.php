<?php

$tituloPagina = "Política de Cookies | WorkHub";
require __DIR__ . "/../view/layout_publico_inicio.php";

?>
<article class="workhub-legal-page">
    <header class="workhub-legal-page__header">
        <p class="workhub-eyebrow">WorkHub</p>
        <h1>Política de Cookies</h1>
        <p class="workhub-legal-page__summary">Informações sobre o cookie técnico utilizado para manter o funcionamento da sessão.</p>
        <p class="workhub-legal-page__version">Atualizado em 17 de novembro de 2026.</p>
    </header>
    <div class="workhub-legal-content">
        <h2>1. O que são cookies</h2>
        <p>Cookies são pequenos dados que o navegador guarda para permitir funções específicas de um site. No WorkHub, eles são usados para sustentar a sessão necessária aos recursos autenticados da plataforma.</p>
        <h2>2. Cookie utilizado</h2>
        <p>O WorkHub utiliza o cookie técnico de sessão do PHP. Ele mantém o estado de navegação após o login e vincula os tokens usados para proteger o envio dos formulários. Sem esse cookie, a plataforma não consegue reconhecer a sessão autenticada do usuário.</p>
        <h2>3. Configurações de proteção</h2>
        <p>O cookie de sessão é configurado com acesso restrito ao próprio site (<code>HttpOnly</code>) e com a política <code>SameSite=Lax</code>, que reduz o envio em contextos externos. O atributo <code>Secure</code> é utilizado quando a aplicação é acessada por HTTPS ou quando essa exigência é configurada no ambiente.</p>
        <h2>4. Cookies opcionais</h2>
        <p>A implementação atual não inclui cookies analíticos ou publicitários, nem ferramentas de rastreamento de audiência nas páginas públicas. Por essa razão, não há banner de consentimento para cookies opcionais.</p>
        <h2>5. Controle no navegador</h2>
        <p>O usuário pode consultar, bloquear ou remover cookies nas configurações do navegador. O bloqueio do cookie de sessão pode impedir o login, o envio de formulários e o uso das áreas que exigem autenticação. Consulte também a <a href="/politica-de-privacidade.php">Política de Privacidade</a> para entender como as informações são tratadas.</p>
        <h2>6. Atualizações desta política</h2>
        <p>Esta página será atualizada caso o uso de cookies ou tecnologias semelhantes seja alterado na plataforma.</p>
    </div>
</article>
<?php require __DIR__ . "/../view/layout_publico_fim.php"; ?>
