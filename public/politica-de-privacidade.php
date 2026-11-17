<?php

$tituloPagina = "Política de Privacidade | WorkHub";
require __DIR__ . "/../view/layout_publico_inicio.php";

?>
<article class="workhub-legal-page">
    <header class="workhub-legal-page__header">
        <p class="workhub-eyebrow">Documento institucional</p>
        <h1>Política de Privacidade</h1>
        <p class="workhub-legal-page__summary">
            Esta versão preliminar descreve os dados observados na implementação atual do WorkHub.
        </p>
        <p class="workhub-legal-page__version">Versão preliminar — 17 de novembro de 2026.</p>
    </header>
    <div class="workhub-legal-content">
        <p class="workhub-legal-pending">
            Pendência de revisão: a identificação do responsável pela plataforma, o canal para solicitações de titulares,
            a definição dos papéis de tratamento e as regras de retenção precisam ser definidos antes da publicação final.
        </p>
        <h2>1. Escopo</h2>
        <p>
            O WorkHub é uma plataforma de gestão empresarial. Esta política se aplica às páginas públicas, ao cadastro e
            ao uso autenticado da plataforma, conforme as funcionalidades atualmente disponíveis.
        </p>
        <h2>2. Dados tratados pela plataforma</h2>
        <p>Na operação técnica do WorkHub, podem ser tratados os seguintes dados:</p>
        <ul>
            <li>dados cadastrais da empresa: nome fantasia, razão social, CNPJ, e-mail e telefone;</li>
            <li>dados de usuários da empresa: nome, e-mail, perfil de acesso, situação de acesso e senha armazenada em hash;</li>
            <li>dados de clientes cadastrados pelas empresas: nome, telefone e, quando informado, e-mail;</li>
            <li>dados operacionais: produtos, descrições, preços, pedidos, itens, valores, datas, status e histórico de status;</li>
            <li>dados técnicos de sessão: identificador de sessão, tipo de autenticação, identificador do usuário ou administrador,
                momento da última atividade e tokens de proteção contra envio indevido de formulários.</li>
        </ul>
        <h2>3. Origem e finalidade</h2>
        <p>
            Os dados da empresa e do responsável são informados no cadastro. Os demais dados são inseridos por usuários
            autorizados durante a operação. Eles são usados para cadastrar a empresa, controlar acessos, manter cadastros,
            registrar pedidos e acompanhar a produção e os prazos dentro da plataforma.
        </p>
        <h2>4. Dados das empresas clientes</h2>
        <p>
            As empresas que usam o WorkHub incluem dados de seus clientes, produtos e pedidos para sua própria operação.
            A definição de responsabilidades entre a plataforma e cada empresa cliente deve ser formalizada pelo responsável
            pelo projeto antes da versão definitiva desta política.
        </p>
        <h2>5. Compartilhamento</h2>
        <p>
            A implementação revisada não contém integrações de publicidade, análise de navegação ou compartilhamento automático
            com serviços de terceiros. Qualquer compartilhamento futuro deve ser avaliado e informado antes de sua adoção.
        </p>
        <h2>6. Direitos dos titulares</h2>
        <p>
            A LGPD prevê direitos como informação, confirmação de tratamento, acesso, correção e, conforme o caso, bloqueio,
            eliminação, portabilidade e revisão de decisões automatizadas. A forma de receber e atender solicitações ainda depende
            da definição de um canal de contato pelo responsável pelo projeto.
        </p>
        <h2>7. Segurança e retenção</h2>
        <p>
            A implementação possui controles técnicos específicos, como autenticação por sessão, proteção CSRF e armazenamento
            de senha por hash. Esta política não declara certificações, auditorias ou controles além dos verificados no código.
            Os prazos de retenção e os procedimentos de descarte ainda não foram definidos.
        </p>
        <h2>8. Atualizações</h2>
        <p>
            Esta política deverá ser revisada quando houver mudança relevante no tratamento de dados ou na plataforma. A versão
            final deverá indicar o responsável, o canal de contato e a data de vigência.
        </p>
    </div>
</article>
<?php require __DIR__ . "/../view/layout_publico_fim.php"; ?>
