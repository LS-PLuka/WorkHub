# Preparação para AWS EC2

Este guia prepara uma implantação; não cria recursos AWS nem afirma que HTTPS já foi validado.

## Arquitetura proposta

Em uma EC2 Ubuntu Server 24.04 (planejada como `t3.small` em `us-east-1`), Caddy recebe as portas
80/443 e encaminha para Apache/PHP somente pela rede Docker. MySQL permanece sem `ports`, com dados
no volume `mysql_data`, armazenado no EBS da instância. A composição de produção é
`compose.prod.yaml`; ela não reutiliza o bind mount de desenvolvimento. Os limites iniciais são
128 MiB (Caddy), 512 MiB (app) e 768 MiB (MySQL), com buffer pool MySQL de 256 MiB. Devem ser
monitorados e ajustados após carga real.

Caddy foi escolhido em vez de Nginx por manter a configuração curta e automatizar emissão e renovação
de certificados quando há domínio resolvendo publicamente para a EC2. Nginx é uma alternativa válida,
mas exige configurar e renovar o certificado separadamente. Sem domínio, use temporariamente acesso
restrito por HTTP para testes ou forneça certificados próprios; não anuncie HTTPS como funcional.

O Caddy remove qualquer `X-Forwarded-For` recebido do cliente e cria o cabeçalho a partir da conexão
real. Somente defina `PROXY_REVERSO_CONFIAVEL=true` na composição de produção, onde `app` não possui
porta pública e recebe tráfego exclusivamente do Caddy. Nunca publique a porta da aplicação junto
com essa opção.

## Preparação da instância

1. Crie Security Group: TCP 80 e 443 de `0.0.0.0/0` e `::/0`; TCP 22 apenas do IP/VPN administrativo.
   Não adicione 3306.
2. Associe uma Elastic IP se precisar manter o endereço público entre paradas. Ela pode ter custo quando
   não estiver associada a uma instância em execução. Registre no DNS um domínio e confirme que aponta
   para o IP antes de iniciar o Caddy.
3. Atualize o Ubuntu, instale Docker Engine e o plugin Docker Compose pelas instruções oficiais da
   distribuição/Docker, e permita Docker somente a administradores confiáveis. O grupo `docker` equivale
   a privilégios elevados na máquina.
4. Clone o repositório em diretório pertencente ao administrador. Crie `.env` com permissão `600`, sem
   valores de exemplo ou senhas previsíveis; mantenha a chave SSH em local protegido e também com
   permissões restritas.
5. Use `APP_ENV=production`, `SESSAO_COOKIE_SECURE=true`,
   `ADMIN_BOOTSTRAP_HABILITADO=false`, `PROXY_REVERSO_CONFIAVEL=true` e uma chave aleatória exclusiva
   em `APP_CHAVE_RATE_LIMIT`. Não envie o `.env` para Git, imagens ou logs.
6. Valide `docker compose --env-file .env -f compose.prod.yaml config --quiet`, então suba a composição.
   Verifique `docker compose -f compose.prod.yaml ps`, logs e login antes de liberar usuários.

Mantenha atualizações de segurança do Ubuntu e das imagens em rotina controlada. Centralize ou retenha
logs do Docker/Apache conforme a política operacional e monitore CPU, memória, saúde dos contêineres,
espaço no EBS e expiração de certificados.

## Banco, persistência e recuperação

O schema é importado por `/docker-entrypoint-initdb.d` apenas quando `mysql_data` é criado pela primeira
vez. Reinícios e `docker compose down` preservam o volume. Não use `down -v` em produção.

Exemplo de backup lógico (guarde o arquivo fora da EC2, cifrado e com acesso restrito):

```sh
docker compose -f compose.prod.yaml exec -T db sh -c 'mysqldump -u"$MYSQL_USER" -p"$MYSQL_PASSWORD" "$MYSQL_DATABASE"' > workhub-AAAA-MM-DD.sql
```

Restaure somente em ambiente de recuperação previamente testado:

```sh
docker compose -f compose.prod.yaml exec -T db sh -c 'mysql -u"$MYSQL_USER" -p"$MYSQL_PASSWORD" "$MYSQL_DATABASE"' < workhub-AAAA-MM-DD.sql
```

Backup não é considerado válido até que uma restauração seja testada em banco isolado e a aplicação
seja verificada. Programe cópias externas, retenção, verificação de criptografia EBS em repouso,
monitoramento de disco e um procedimento de recuperação com RPO/RTO definidos.

## Checklist de implantação

- [ ] Instância, EBS criptografado, Security Group e SSH restrito revisados.
- [ ] Elastic IP e DNS definidos, se acesso público permanente for necessário.
- [ ] `.env` de produção criado com segredos fortes e permissões restritas.
- [ ] MySQL sem porta pública e aplicativo sem porta pública direta.
- [ ] Compose validado, serviços saudáveis e dados persistentes após reinício.
- [ ] HTTP redirecionado/servido por Caddy e HTTPS testado com domínio real.
- [ ] Login, CSRF, RBAC, isolamento multiempresa e páginas de erro testados no ambiente implantado.
- [ ] Backup externo e restauração em ambiente isolado testados.
- [ ] Logs, atualizações, espaço em disco e alertas operacionais definidos.

Pendências bloqueantes: domínio/DNS e teste real de HTTPS, provisionamento seguro da EC2, segredos de
produção e restauração de backup testada. Importantes: monitoramento, retenção de logs e teste de carga
na capacidade da instância. Opcionais: Elastic IP quando o IP público já for estável e aceitável.
