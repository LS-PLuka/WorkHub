<?php require __DIR__ . "/layout_publico_inicio.php"; ?>
<section class="workhub-auth-card">
    <p class="workhub-eyebrow">Acesso empresarial</p>
    <h1>Entrar no WorkHub</h1>
    <p>Acesse a gestão da sua empresa.</p>
    <?php if ($mensagemSaida): ?>
        <div class="alert alert-success" role="alert">Sessão encerrada com sucesso.</div>
    <?php endif; ?>
    <?php if ($erros): ?>
        <div class="alert alert-danger" role="alert"><ul class="mb-0">
            <?php foreach ($erros as $erro): ?>
                <li><?= escapar_html($erro) ?></li>
            <?php endforeach; ?>
        </ul></div>
    <?php endif; ?>
    <form method="post" action="/entrar">
        <input type="hidden" name="token_csrf" value="<?= escapar_html($tokenCsrf) ?>">
        <div class="mb-3">
            <label class="form-label" for="email_usuario">E-mail</label>
            <input class="form-control" id="email_usuario" name="email_usuario" type="email" maxlength="150" required
                value="<?= escapar_html($emailUsuario) ?>" autocomplete="email">
        </div>
        <div class="mb-4">
            <label class="form-label" for="senha">Senha</label>
            <input class="form-control" id="senha" name="senha" type="password" required autocomplete="current-password">
        </div>
        <button class="btn btn-primary w-100" type="submit">Entrar</button>
    </form>
    <p class="workhub-auth-card__footer">Ainda não utiliza o WorkHub? <a href="/cadastro-empresa">Cadastre sua empresa</a>.</p>
</section>
<?php require __DIR__ . "/layout_publico_fim.php"; ?>
