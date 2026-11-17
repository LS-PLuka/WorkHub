<?php

$paginaAtualAdministracao = "empresas";
require __DIR__ . "/layout_administracao_inicio.php";

?>
<header class="workhub-page-heading workhub-page-heading--actions">
    <div>
        <p class="workhub-eyebrow">Empresa #<?= (int) $empresa["id"] ?></p>
        <h1><?= escapar_html($empresa["nome_fantasia"]) ?></h1>
        <p>Dados cadastrais e situação da empresa na plataforma.</p>
    </div>
    <a class="btn btn-outline-primary" href="/administracao/empresas">Voltar para empresas</a>
</header>

<section class="workhub-panel workhub-admin-company-details">
    <div class="workhub-section-heading">
        <div><h2>Dados cadastrais</h2><p>Informações fornecidas no cadastro da empresa.</p></div>
        <span class="workhub-status <?= (int) $empresa["ativo"] === 1 ? "workhub-status--success" : "" ?>">
            <?= (int) $empresa["ativo"] === 1 ? "Ativa" : "Desativada" ?>
        </span>
    </div>
    <dl class="workhub-details-grid workhub-details-grid--three">
        <div><dt>Razão social</dt><dd><?= escapar_html($empresa["razao_social"]) ?></dd></div>
        <div><dt>CNPJ</dt><dd><?= escapar_html($empresa["cnpj"]) ?></dd></div>
        <div><dt>E-mail</dt><dd><?= escapar_html($empresa["email"]) ?></dd></div>
        <div><dt>Telefone</dt><dd><?= escapar_html($empresa["telefone"]) ?></dd></div>
        <div><dt>Cadastro</dt><dd><?= escapar_html(date("d/m/Y H:i", strtotime($empresa["data_cadastro"]))) ?></dd></div>
        <div><dt>Situação</dt><dd><?= (int) $empresa["ativo"] === 1 ? "Ativa" : "Desativada" ?></dd></div>
    </dl>
    <form class="workhub-form-actions" method="post" action="/administracao/empresas/situacao">
        <input type="hidden" name="token_csrf" value="<?= escapar_html($tokenCsrfSituacao) ?>">
        <input type="hidden" name="id_empresa" value="<?= (int) $empresa["id"] ?>">
        <input type="hidden" name="ativo" value="<?= (int) $empresa["ativo"] === 1 ? 0 : 1 ?>">
        <button class="btn btn-outline-<?= (int) $empresa["ativo"] === 1 ? "danger" : "success" ?>" type="submit"
            title="A alteração de situação afeta o acesso empresarial.">
            <?= (int) $empresa["ativo"] === 1 ? "Desativar empresa" : "Ativar empresa" ?>
        </button>
    </form>
</section>
<?php require __DIR__ . "/layout_administracao_fim.php"; ?>
