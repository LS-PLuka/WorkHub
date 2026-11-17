<?php

$modoCabecalho = "administracao";
require __DIR__ . "/layout_publico_inicio.php";

?>
<section class="workhub-admin-login" aria-labelledby="titulo-entrada-administracao">
    <div class="workhub-admin-login__intro">
        <p class="workhub-eyebrow">Ambiente restrito</p>
        <h1 id="titulo-entrada-administracao">Administração da Plataforma</h1>
        <p>Consulte empresas cadastradas e acompanhe a situação geral da plataforma.</p>
        <div class="workhub-admin-login__context">
            <strong>WorkHub</strong>
            <span>Acesso exclusivo para administradores.</span>
        </div>
    </div>
    <div class="workhub-auth-card workhub-auth-card--admin">
        <p class="workhub-eyebrow">Entrar</p>
        <h2>Identifique-se</h2>
        <p>Use as credenciais administrativas para continuar.</p>
    <?php if ($mensagemSaida): ?>
        <div class="alert alert-success" role="alert">Sessão administrativa encerrada.</div>
    <?php endif; ?>
    <?php if ($erros): ?>
        <div class="alert alert-danger" role="alert"><ul class="mb-0">
            <?php foreach ($erros as $erro): ?>
                <li><?= escapar_html($erro) ?></li>
            <?php endforeach; ?>
        </ul></div>
    <?php endif; ?>
        <form method="post" action="/administracao/entrar">
            <input type="hidden" name="token_csrf" value="<?= escapar_html($tokenCsrf) ?>">
            <div class="mb-3">
                <label class="form-label" for="email">E-mail</label>
                <input class="form-control" id="email" name="email" type="email" required value="<?= escapar_html($email) ?>"
                    autocomplete="email">
            </div>
            <div class="mb-4">
                <label class="form-label" for="senha">Senha</label>
                <input class="form-control" id="senha" name="senha" type="password" required autocomplete="current-password">
            </div>
            <button class="btn btn-primary w-100" type="submit">Entrar na administração</button>
        </form>
    </div>
</section>
<?php require __DIR__ . "/layout_publico_fim.php"; ?>
