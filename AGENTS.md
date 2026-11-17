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
