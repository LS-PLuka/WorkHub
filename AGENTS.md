# Convenções de desenvolvimento do WorkHub

## Tecnologia e arquitetura

- Utilizar PHP 8.3 procedural, sem classes próprias.
- Utilizar MySQLi procedural, sem PDO.
- Manter a arquitetura monolítica com MVC simplificado.
- Implementar todas as regras de negócio em PHP.
- Utilizar JavaScript somente para interatividade visual.
- Não criar abstrações, camadas ou serviços desnecessários.
- Manter o código simples, legível e adequado a um projeto acadêmico.

## Nomenclatura e formatação

- Utilizar a nomenclatura própria do projeto em português brasileiro.
- Preservar nomes convencionais como `controller`, `model`, `view`, `Dockerfile` e APIs nativas.
- Escrever variáveis PHP em camelCase.
- Escrever funções e arquivos próprios em snake_case.
- Usar indentação de 4 espaços.
- Escrever uma instrução PHP por linha.
- Formatar chaves e blocos condicionais de maneira legível.
- Preferir linhas de até 120 caracteres.
- Organizar SQL longo em múltiplas linhas.
- Nunca compactar funções ou condicionais em uma única linha.
- Seguir PSR-12 como referência, adaptado ao PHP procedural.

## Dados e segurança

- Utilizar MySQLi com consultas preparadas.
- Manter autenticação, RBAC, CSRF e isolamento multiempresa.
- Nunca executar exclusão física em operações de negócio.

## Identidade Visual e Experiência do Usuário

### Identidade

- Nome: WorkHub.
- Descrição: Plataforma de Gestão Empresarial.
- Slogan: Organize seu negócio. Gerencie sua produção.
- O produto deve transmitir profissionalismo, confiabilidade, clareza, organização e eficiência operacional.

### Estilo visual

- Adotar uma identidade de SaaS B2B profissional e funcional, evitando aparência de interfaces genéricas geradas por IA.
- Manter design limpo, sóbrio e consistente, com fundos neutros e hierarquia tipográfica objetiva.
- Utilizar sidebar lateral para navegação e cabeçalho superior discreto.
- Equilibrar largura do conteúdo e espaçamentos para privilegiar a operação.
- Construir tabelas legíveis, com filtros e ações bem posicionados.
- Utilizar cards simples para indicadores; não usar elementos decorativos desnecessários.
- Organizar formulários para uso fácil e manter cores e textos de status consistentes.
- Usar ícones discretos, semanticamente adequados e nunca emojis como ícones de interface.
- Preservar responsividade para desktop, tablet e celular.

Evitar:

- Gradientes chamativos.
- Excesso de sombras e bordas arredondadas.
- Cards desnecessários para informações tabulares.
- Animações exageradas ou elementos decorativos sem função.
- Grandes espaços vazios sem justificativa.
- Interfaces que priorizam aparência em detrimento da operação.
- Inconsistência visual entre módulos.

### Paleta preliminar

- Azul principal: `#156A96`.
- Texto principal: `#243247`.
- Fundo: `#F7F9FC`.
- Superfícies: `#FFFFFF`.
- Bordas: `#E2E8F0`.

A Etapa 10 poderá refinar a paleta e definir cores semânticas, preservando esta identidade.

### Tecnologias

- Manter HTML5, CSS3 e Bootstrap 5 no frontend.
- Usar JavaScript somente para interatividade visual.
- Usar PHP para renderização e processamento das páginas.
- Não introduzir React, Vue, Angular, Next.js ou Tailwind.
- Não transferir regras de negócio do PHP para JavaScript.
