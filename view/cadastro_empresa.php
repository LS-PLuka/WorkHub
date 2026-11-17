<?php require __DIR__ . "/layout_publico_inicio.php"; ?>
<section class="workhub-registration-layout">
    <aside class="workhub-registration-layout__intro">
        <p class="workhub-eyebrow">Primeiro acesso</p>
        <h1>Comece a organizar a operação da sua empresa.</h1>
        <p>Informe os dados da empresa e crie o acesso do responsável proprietário.</p>
        <ol>
            <li><strong>Dados da empresa</strong><span>Identificação e contato principal.</span></li>
            <li><strong>Dados do responsável</strong><span>Acesso inicial à plataforma.</span></li>
        </ol>
    </aside>
    <div class="workhub-registration">
    <header class="workhub-registration__heading">
        <p class="workhub-eyebrow">Comece agora</p>
        <h1>Cadastre sua empresa</h1>
        <p>Os campos marcados com <span class="workhub-required" aria-hidden="true">*</span> são obrigatórios.</p>
    </header>
    <?php if ($cadastroConcluido): ?>
        <div class="alert alert-success" role="alert">
            Empresa cadastrada com sucesso. <a href="/entrar" class="alert-link">Entre com o acesso do Proprietário</a>.
        </div>
    <?php endif; ?>
    <?php if ($erros): ?>
        <div class="alert alert-danger" role="alert">
            <p class="mb-1 fw-semibold">Revise os dados informados:</p>
            <ul class="mb-0">
                <?php foreach ($erros as $erro): ?>
                    <li><?= escapar_html($erro) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>
    <form method="post" action="/cadastro-empresa" novalidate>
        <input type="hidden" name="token_csrf" value="<?= escapar_html($_SESSION["cadastro_empresa_token_csrf"]) ?>">
        <section class="workhub-registration__section">
            <h2>Dados da empresa</h2>
            <p class="workhub-registration__section-description">Informações usadas para identificar sua empresa no WorkHub.</p>
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label" for="nome_fantasia">Nome fantasia <span aria-hidden="true">*</span></label>
                    <input class="form-control" id="nome_fantasia" name="nome_fantasia" maxlength="150" required
                        value="<?= escapar_html($formulario["nome_fantasia"]) ?>">
                </div>
                <div class="col-md-6">
                    <label class="form-label" for="razao_social">Razão social <span aria-hidden="true">*</span></label>
                    <input class="form-control" id="razao_social" name="razao_social" maxlength="200" required
                        value="<?= escapar_html($formulario["razao_social"]) ?>">
                </div>
                <div class="col-md-6">
                    <label class="form-label" for="cnpj">CNPJ <span aria-hidden="true">*</span></label>
                    <input class="form-control" id="cnpj" name="cnpj" inputmode="numeric" maxlength="18" required
                        value="<?= escapar_html($formulario["cnpj"]) ?>">
                </div>
                <div class="col-md-6">
                    <label class="form-label" for="telefone">Telefone <span aria-hidden="true">*</span></label>
                    <input class="form-control" id="telefone" name="telefone" maxlength="20" required
                        value="<?= escapar_html($formulario["telefone"]) ?>">
                </div>
                <div class="col-12">
                    <label class="form-label" for="email_empresa">E-mail da empresa <span aria-hidden="true">*</span></label>
                    <input class="form-control" id="email_empresa" name="email_empresa" type="email" maxlength="150" required
                        value="<?= escapar_html($formulario["email_empresa"]) ?>">
                </div>
            </div>
        </section>
        <section class="workhub-registration__section">
            <h2>Dados do responsável</h2>
            <p class="workhub-registration__section-description">Este acesso será criado como Proprietário da empresa.</p>
            <div class="row g-3">
                <div class="col-12">
                    <label class="form-label" for="nome_responsavel">Nome completo <span aria-hidden="true">*</span></label>
                    <input class="form-control" id="nome_responsavel" name="nome_responsavel" maxlength="150" required
                        value="<?= escapar_html($formulario["nome_responsavel"]) ?>">
                </div>
                <div class="col-12">
                    <label class="form-label" for="email_responsavel">E-mail <span aria-hidden="true">*</span></label>
                    <input class="form-control" id="email_responsavel" name="email_responsavel" type="email" maxlength="150" required
                        value="<?= escapar_html($formulario["email_responsavel"]) ?>">
                </div>
                <div class="col-md-6">
                    <label class="form-label" for="senha">Senha <span aria-hidden="true">*</span></label>
                    <input class="form-control" id="senha" name="senha" type="password" minlength="8" required autocomplete="new-password">
                </div>
                <div class="col-md-6">
                    <label class="form-label" for="confirmacao_senha">Confirmação de senha <span aria-hidden="true">*</span></label>
                    <input class="form-control" id="confirmacao_senha" name="confirmacao_senha" type="password" minlength="8" required autocomplete="new-password">
                </div>
            </div>
        </section>
        <div class="workhub-form-actions">
            <button class="btn btn-primary" type="submit">Cadastrar empresa</button>
            <a class="btn btn-link" href="/">Cancelar</a>
        </div>
        <p class="workhub-registration__legal-note">
            Ao continuar, consulte os <a href="/termos-de-uso.php">Termos de Uso</a> e a
            <a href="/politica-de-privacidade.php">Política de Privacidade</a> do WorkHub.
        </p>
    </form>
    </div>
</section>
<?php require __DIR__ . "/layout_publico_fim.php"; ?>
