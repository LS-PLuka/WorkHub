<?php require __DIR__ . "/layout_publico_inicio.php"; ?>
<section class="workhub-public-hero">
    <div class="workhub-public-hero__content">
        <p class="workhub-eyebrow">Plataforma de Gestão Empresarial</p>
        <h1>Organize pedidos, produção e prazos em um único lugar.</h1>
        <p class="workhub-public-hero__lead">
            O WorkHub reúne clientes, produtos, pedidos e acompanhamento da produção para tornar a rotina da sua empresa
            mais clara e organizada.
        </p>
        <div class="workhub-public-actions">
            <a class="btn btn-primary" href="/cadastro-empresa">Cadastrar empresa</a>
            <a class="btn btn-outline-primary" href="/entrar">Entrar na plataforma</a>
        </div>
        <p class="workhub-public-hero__note">Crie sua empresa e comece a estruturar a operação.</p>
    </div>
    <div class="workhub-product-preview" aria-label="Prévia da interface de dashboard do WorkHub">
        <div class="workhub-product-preview__bar">
            <strong>WorkHub</strong>
            <span>Empresa</span>
        </div>
        <div class="workhub-product-preview__body">
            <aside class="workhub-product-preview__nav" aria-hidden="true">
                <span class="is-active">Dashboard</span>
                <span>Usuários</span>
                <span>Produtos</span>
                <span>Clientes</span>
                <span>Pedidos</span>
                <span>Produção</span>
            </aside>
            <div class="workhub-product-preview__content">
                <p>Visão geral</p>
                <h2>Dashboard</h2>
                <div class="workhub-product-preview__metrics">
                    <div><span>Pedidos</span><strong>24</strong></div>
                    <div><span>Em produção</span><strong>08</strong></div>
                    <div><span>Prontos</span><strong>05</strong></div>
                </div>
                <div class="workhub-product-preview__table">
                    <div class="workhub-product-preview__table-head"><strong>Pedidos recentes</strong><span>Ver todos</span></div>
                    <div><span>#024</span><span>Em produção</span></div>
                    <div><span>#023</span><span>Pronto</span></div>
                    <div><span>#022</span><span>Recebido</span></div>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="workhub-public-section" id="funcionalidades" aria-labelledby="titulo-funcionalidades">
    <div class="workhub-public-section__heading">
        <p class="workhub-eyebrow">Recursos para a rotina</p>
        <h2 id="titulo-funcionalidades">Informações essenciais, organizadas para a operação.</h2>
        <p>Cadastros, pedidos e produção ficam conectados em uma interface direta para consulta e acompanhamento.</p>
    </div>
    <div class="workhub-feature-grid">
        <article><h3>Usuários e perfis</h3><p>Organize os acessos da empresa de acordo com o perfil de cada pessoa.</p></article>
        <article><h3>Produtos e clientes</h3><p>Mantenha a base de produtos e clientes disponível para o dia a dia.</p></article>
        <article><h3>Pedidos</h3><p>Registre pedidos, itens e datas previstas de entrega.</p></article>
        <article><h3>Produção por etapas</h3><p>Acompanhe o status de cada pedido ao longo da produção.</p></article>
        <article><h3>Dashboard</h3><p>Consulte indicadores, pedidos recentes e alertas de prazo.</p></article>
        <article><h3>Prazos de entrega</h3><p>Identifique pedidos próximos do vencimento e os que exigem atenção.</p></article>
    </div>
</section>

<section class="workhub-public-section workhub-public-section--split" aria-labelledby="titulo-beneficios">
    <div class="workhub-public-section__heading">
        <p class="workhub-eyebrow">Uma visão organizada</p>
        <h2 id="titulo-beneficios">Mais contexto para acompanhar cada pedido.</h2>
    </div>
    <div class="workhub-benefit-list">
        <div><strong>Informações centralizadas</strong><span>Clientes, produtos, pedidos e produção no mesmo ambiente.</span></div>
        <div><strong>Pedidos bem estruturados</strong><span>Consulte registros, status e itens de cada solicitação.</span></div>
        <div><strong>Andamento visível</strong><span>Acompanhe a produção por etapas e a situação dos prazos.</span></div>
        <div><strong>Acesso por perfil</strong><span>Separe o acesso às funcionalidades conforme a responsabilidade.</span></div>
    </div>
</section>

<section class="workhub-public-story" aria-labelledby="titulo-producao-organizada">
    <figure>
        <img src="/assets/images/producao-oficina.jpg" alt="Pessoa operando uma máquina em uma oficina de produção" width="795"
            height="1192" loading="lazy">
    </figure>
    <div>
        <p class="workhub-eyebrow">Produção acompanhada</p>
        <h2 id="titulo-producao-organizada">Acompanhe cada pedido sem perder o contexto da produção.</h2>
        <p>O status dos pedidos ajuda a visualizar o que foi recebido, está em produção, ficou pronto ou já foi entregue.</p>
    </div>
</section>

<section class="workhub-public-section workhub-public-section--audience" id="para-quem" aria-labelledby="titulo-publico">
    <div class="workhub-public-section__heading">
        <p class="workhub-eyebrow">Para quem é</p>
        <h2 id="titulo-publico">Feito para empresas que produzem e trabalham com encomendas.</h2>
    </div>
    <p>O WorkHub apoia pequenas empresas produtoras, fabricantes e negócios que precisam organizar pedidos,
        acompanhar a produção e manter os dados da operação acessíveis.</p>
</section>

<section class="workhub-public-section" id="como-funciona" aria-labelledby="titulo-como-funciona">
    <div class="workhub-public-section__heading">
        <p class="workhub-eyebrow">Como funciona</p>
        <h2 id="titulo-como-funciona">Estruture a operação em quatro passos.</h2>
    </div>
    <ol class="workhub-steps">
        <li><span>01</span><div><h3>Cadastre a empresa</h3><p>Crie o acesso do responsável pela operação.</p></div></li>
        <li><span>02</span><div><h3>Organize a base</h3><p>Registre produtos, clientes e usuários.</p></div></li>
        <li><span>03</span><div><h3>Registre pedidos</h3><p>Inclua os itens e a data prevista de entrega.</p></div></li>
        <li><span>04</span><div><h3>Acompanhe a produção</h3><p>Atualize as etapas e consulte os indicadores.</p></div></li>
    </ol>
</section>

<section class="workhub-public-story workhub-public-story--reverse" aria-labelledby="titulo-pedidos-organizados">
    <figure>
        <img src="/assets/images/preparacao-pedidos.jpg" alt="Pessoa organizando caixas e etiquetas para preparar pedidos" width="819"
            height="1228" loading="lazy">
    </figure>
    <div>
        <p class="workhub-eyebrow">Pedidos bem estruturados</p>
        <h2 id="titulo-pedidos-organizados">Da organização do cliente à preparação do pedido.</h2>
        <p>Registre clientes, produtos, itens e a data prevista de entrega para consultar as informações quando precisar.</p>
    </div>
</section>

<section class="workhub-public-cta" aria-labelledby="titulo-cta">
    <div>
        <p class="workhub-eyebrow">Comece com o essencial</p>
        <h2 id="titulo-cta">Dê mais organização à rotina da sua empresa.</h2>
        <p>Cadastre sua empresa para reunir pedidos, produção e dados operacionais no WorkHub.</p>
    </div>
    <a class="btn btn-primary" href="/cadastro-empresa">Cadastrar empresa</a>
</section>
<?php require __DIR__ . "/layout_publico_fim.php"; ?>
