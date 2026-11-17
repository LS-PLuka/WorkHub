<?php

$tituloPagina = "Privacidade e Proteção de Dados | WorkHub";
require __DIR__ . "/../view/layout_publico_inicio.php";

?>
<article class="workhub-legal-page">
    <header class="workhub-legal-page__header">
        <p class="workhub-eyebrow">Documento institucional</p>
        <h1>Privacidade e Proteção de Dados</h1>
        <p class="workhub-legal-page__summary">Resumo preliminar das práticas observadas na plataforma.</p>
        <p class="workhub-legal-page__version">Versão preliminar — 17 de novembro de 2026.</p>
    </header>
    <div class="workhub-legal-content">
        <p class="workhub-legal-pending">
            O WorkHub não declara conformidade integral com a LGPD nesta versão. A adequação depende de revisão jurídica,
            definição de responsáveis, processos operacionais e evolução dos controles técnicos.
        </p>
        <h2>1. Compromisso de transparência</h2>
        <p>
            O WorkHub busca deixar claro quais dados são usados na operação atual e para quais funcionalidades. A
            <a href="/politica-de-privacidade.php">Política de Privacidade</a> apresenta o detalhamento inicial desses dados.
        </p>
        <h2>2. Separação entre dados</h2>
        <p>
            A plataforma trata os dados necessários ao cadastro e acesso das empresas. As empresas clientes registram e consultam,
            em seus próprios ambientes, dados de clientes, produtos, pedidos e produção. O isolamento entre empresas é aplicado no
            acesso autenticado da plataforma.
        </p>
        <h2>3. Controles observados</h2>
        <p>
            A implementação atual utiliza autenticação por sessão, separação de acesso por perfil, proteção contra envio indevido de
            formulários e armazenamento de senhas por hash. Esses controles não dispensam a avaliação contínua de riscos e não
            representam declaração de certificação ou auditoria independente.
        </p>
        <h2>4. Próximos requisitos de governança</h2>
        <ul>
            <li>definir quem responde pelo tratamento de dados e disponibilizar canal de atendimento aos titulares;</li>
            <li>formalizar papéis e responsabilidades com empresas clientes e eventuais fornecedores;</li>
            <li>definir políticas de retenção, descarte, incidentes e revisão de acessos;</li>
            <li>avaliar registros, medidas técnicas e processos necessários antes de declarar conformidade.</li>
        </ul>
        <h2>5. Materiais relacionados</h2>
        <p>
            Consulte os <a href="/termos-de-uso.php">Termos de Uso</a>, a
            <a href="/politica-de-cookies.php">Política de Cookies</a> e a Política de Privacidade. Estes documentos devem ser
            revisados pelo responsável pelo projeto antes de serem apresentados como instrumentos finais.
        </p>
    </div>
</article>
<?php require __DIR__ . "/../view/layout_publico_fim.php"; ?>
