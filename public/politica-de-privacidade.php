<?php

$tituloPagina = "Política de Privacidade | WorkHub";
require __DIR__ . "/../view/layout_publico_inicio.php";

?>
<article class="workhub-legal-page">
    <header class="workhub-legal-page__header">
        <p class="workhub-eyebrow">WorkHub</p>
        <h1>Política de Privacidade</h1>
        <p class="workhub-legal-page__summary">Como o WorkHub trata as informações necessárias ao funcionamento da plataforma.</p>
        <p class="workhub-legal-page__version">Atualizado em 17 de novembro de 2026.</p>
    </header>
    <div class="workhub-legal-content">
        <h2>1. Finalidade desta política</h2>
        <p>Esta política explica as informações tratadas pelo WorkHub durante o cadastro e o uso do sistema de gestão empresarial. Ela se aplica às páginas públicas e às áreas autenticadas da plataforma.</p>
        <h2>2. Informações cadastradas</h2>
        <p>O WorkHub pode tratar dados informados durante a operação, incluindo:</p>
        <ul>
            <li>dados da empresa, como nome fantasia, razão social, CNPJ, e-mail e telefone;</li>
            <li>dados de usuários empresariais e administradores, como nome, e-mail, perfil e situação de acesso;</li>
            <li>dados de clientes cadastrados pelas empresas, como nome, telefone e e-mail quando informado;</li>
            <li>dados operacionais, como produtos, preços, pedidos, itens, datas, valores, status e histórico de produção.</li>
        </ul>
        <h2>3. Finalidades do uso</h2>
        <p>As informações são utilizadas para criar e manter cadastros, autenticar usuários, aplicar permissões, registrar pedidos e itens, acompanhar a produção, calcular indicadores e exibir informações relacionadas à empresa autenticada.</p>
        <h2>4. Acesso e separação entre empresas</h2>
        <p>O acesso empresarial é associado à empresa do usuário autenticado. Consultas e operações dos módulos empresariais consideram esse vínculo, de modo que produtos, clientes, pedidos, itens, históricos e indicadores permaneçam separados entre empresas. A administração da plataforma utiliza autenticação própria e não se confunde com uma conta empresarial.</p>
        <h2>5. Autenticação e segurança</h2>
        <p>As senhas são armazenadas por meio de hash bcrypt. A aplicação usa sessão de autenticação, controle de perfis, renovação do identificador de sessão no login, proteção contra envios indevidos de formulários e limitação de tentativas de entrada. Esses controles ajudam a proteger o acesso, mas não eliminam a necessidade de cuidado dos usuários com suas credenciais.</p>
        <h2>6. Armazenamento, retenção e desativação</h2>
        <p>Os dados operacionais são armazenados no banco de dados utilizado pela plataforma enquanto permanecerem nos registros do sistema. As operações de desativação de empresas, usuários, produtos, clientes e pedidos preservam os registros, alterando sua situação quando aplicável. A aplicação não oferece, nesta versão, um fluxo público de exclusão definitiva ou um prazo automático de retenção.</p>
        <h2>7. Solicitações sobre dados</h2>
        <p>Usuários empresariais devem inicialmente procurar o Proprietário da empresa para atualização de seus acessos e cadastros. A página Minha Empresa permite ao Proprietário atualizar dados cadastrais específicos da empresa. Um canal institucional para solicitações de titulares ainda depende de definição do responsável pelo projeto.</p>
        <h2>8. Atualizações desta política</h2>
        <p>Esta política poderá ser atualizada quando houver mudança relevante nas funcionalidades ou no tratamento de informações. A data da atualização mais recente permanecerá indicada nesta página.</p>
    </div>
</article>
<?php require __DIR__ . "/../view/layout_publico_fim.php"; ?>
