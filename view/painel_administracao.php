<?php

$paginaAtualAdministracao = "visao_geral";
require __DIR__ . "/layout_administracao_inicio.php";

?>
<header class="workhub-page-heading workhub-page-heading--actions">
    <div>
        <p class="workhub-eyebrow">Administração global</p>
        <h1>Visão geral</h1>
        <p>Resumo das empresas cadastradas na plataforma.</p>
    </div>
    <a class="btn btn-outline-primary" href="/administracao/empresas">Consultar empresas</a>
</header>

<?php if ($indicadores === false): ?>
    <div class="alert alert-danger" role="alert">Não foi possível carregar os indicadores administrativos.</div>
<?php else: ?>
    <section class="workhub-metrics workhub-metrics--admin" aria-label="Indicadores de empresas">
        <article class="workhub-metric workhub-metric--primary">
            <p>Total de empresas</p><strong><?= (int) $indicadores["total_empresas"] ?></strong>
        </article>
        <article class="workhub-metric workhub-metric--success">
            <p>Empresas ativas</p><strong><?= (int) $indicadores["empresas_ativas"] ?></strong>
        </article>
        <article class="workhub-metric workhub-metric--neutral">
            <p>Empresas desativadas</p><strong><?= (int) $indicadores["empresas_desativadas"] ?></strong>
        </article>
    </section>
<?php endif; ?>
<?php require __DIR__ . "/layout_administracao_fim.php"; ?>
