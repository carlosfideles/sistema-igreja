# SISTEMA DE GESTÃO IEADM-DF — DOCUMENTAÇÃO MESTRE

**Documentação Arquitetural, Referência Técnica e Guia de Implantação em Produção**

---

## 🏛️ 1. Visão Geral do Sistema

O **SISTEMA DE GESTÃO IEADM-DF** é uma plataforma corporativa web desenvolvida em **Laravel 11**, **PHP 8.2+**, **Tailwind CSS** e **MySQL**, projetada especificamente para a administração eclesiástica de uma Regional e de todas as suas congregações subordinadas no Distrito Federal e entorno.

O sistema implementa **isolamento estrito de dados entre congregações**, controle de acesso baseado em papéis e permissões granulares (**RBAC**), auditoria estruturada de eventos, segurança avançada contra vulnerabilidades web e uma suíte completa de testes automatizados.

---

## 🔐 2. Arquitetura de Autorização e Escopos

### 2.1 Princípio Fundamental de Isolamento de Dados
Um usuário **somente pode acessar informações das congregações às quais possui autorização expressa**. Esta regra é aplicada de ponta a ponta: rotas, controllers, models, form requests, policies, queries e relatórios.

### 2.2 Níveis de Abrangência (`scope`)

| Abrangência | Descrição | Comportamento no Sistema |
|-------------|-----------|--------------------------|
| **`REGIONAL`** | Acesso amplo a toda a Regional | Possui acesso automático a todas as congregações cadastradas e às que forem criadas no futuro, sem necessidade de vínculos manuais. |
| **`LOCAL`** | Acesso restrito a congregações | Acessa exclusivamente as congregações expressamente autorizadas na tabela `church_user`. Tentativas de acesso a outras congregações via URL ou ID (IDOR) são bloqueadas com **HTTP 403 Forbidden**. |

### 2.3 Hierarquia e Papéis da Secretaria

| Papel (`slug`) | Nível (`level`) | Descrição |
|----------------|-----------------|-----------|
| **1º Secretário Regional** | `1` | Administrador supremo do sistema. Possui permissões plenas e irrestritas. **É imutável: não pode ser excluído, desativado, bloqueado ou rebaixado por nenhum usuário.** |
| **2º Secretário Regional** | `2` | Auxilia na gestão regional conforme permissões atribuídas. Não pode alterar dados do 1º Secretário. |
| **3º Secretário Regional** | `3` | Suporte operacional regional. |
| **1º Secretário Local** | `1` | Gestor executivo das congregações autorizadas. Não pode criar secretários regionais nem conceder acessos superiores ao seu. |
| **2º Secretário Local** | `2` | Operador administrativo local. |
| **3º Secretário Local** | `3` | Operador cadastral local. |

---

## 🗄️ 3. Modelos Eloquent e Estrutura de Banco de Dados

### 3.1 Diagrama de Relacionamentos

```
[Role] 1 ──── N [User] N ──── N [Church] (via church_user)
  │               │              │
  N               │              1
  │               │              │
[Permission]      │              N
(role_permissions)│          [Member] 1 ──── N [MemberTransfer]
                  │              │
                  N              N
                  └───── [AuditLog] ─────┘
```

### 3.2 Modelos Principais

1. **`User`** (`app/Models/User.php`):
   - **Campos**: `id`, `name`, `email`, `password`, `cpf`, `phone`, `photo`, `status` (`active`/`inactive`/`blocked`), `role_id`, `scope` (`REGIONAL`/`LOCAL`), `last_login_at`, timestamps, soft deletes.
   - **Relacionamentos**: `belongsTo(Role)`, `belongsToMany(Church, 'church_user')`.
   - **Métodos Chave**:
     - `isRegional()`: Verifica se o escopo é regional.
     - `isLocal()`: Verifica se o escopo é local.
     - `isFirstSecretaryRegional()`: Verifica se é o 1º Secretário Regional.
     - `canAccessChurch(int $churchId)`: Função central de autorização por congregação.
     - `accessibleChurchesQuery()`: Query Builder pré-filtrado com as congregações visíveis pelo usuário.
     - `hasPermission(string $slug)`: Verificação de permissões granulares.

2. **`Church`** (`app/Models/Church.php`):
   - **Campos**: `id`, `name`, `code`, `cnpj`, `phone`, `email`, `zip_code`, `address`, `number`, `complement`, `neighborhood`, `city`, `state`, `responsible_name`, `foundation_date`, `status` (`active`/`inactive`), `logo`, `notes`, timestamps, soft deletes.
   - **Relacionamentos**: `hasMany(Member)`, `belongsToMany(User, 'church_user')`.

3. **`Member`** (`app/Models/Member.php`):
   - **Campos**: `id`, `church_id`, `full_name`, `social_name`, `cpf`, `rg`, `birth_date`, `gender`, `marital_status`, `phone`, `whatsapp`, `email`, `zip_code`, `address`, `number`, `complement`, `neighborhood`, `city`, `state`, `photo`, `entry_date`, `status` (`ativo`/`inativo`/`transferido`/`disciplina`/`falecido`), `notes`, timestamps, soft deletes.
   - **Relacionamentos**: `belongsTo(Church)`, `hasMany(MemberTransfer)`.

4. **`MemberTransfer`** (`app/Models/MemberTransfer.php`):
   - **Campos**: `id`, `member_id`, `from_church_id`, `to_church_id`, `transferred_by`, `transferred_at`, `reason`, `notes`, timestamps.
   - **Relacionamentos**: `belongsTo(Member)`, `belongsTo(Church, 'from_church_id')`, `belongsTo(Church, 'to_church_id')`, `belongsTo(User, 'transferred_by')`.

5. **`Role`** & **`Permission`** (`app/Models/Role.php`, `app/Models/Permission.php`):
   - Estrutura RBAC com tabela pivô `role_permissions`.

6. **`AuditLog`** (`app/Models/AuditLog.php`):
   - **Campos**: `id`, `user_id`, `church_id`, `action`, `module`, `entity_type`, `entity_id`, `description`, `old_values` (JSON), `new_values` (JSON), `ip_address`, `user_agent`, `created_at`.
   - **Método**: `AuditLog::record(...)` para auditoria automática e rastreabilidade integral.

---

## 🌐 4. Mapeamento Completo de Rotas

Todas as rotas autenticadas utilizam os middlewares `['auth', 'active.user', SecurityHeaders::class]`.

| Método | URI | Nome da Rota | Controller & Ação | Permissão Requerida |
|--------|-----|--------------|-------------------|---------------------|
| `GET` | `/` | — | Redirecionamento para `/login` | — |
| `GET` | `/login` | `login` | `AuthenticatedSessionController@create` | Visitante (`guest`) |
| `POST` | `/login` | — | `AuthenticatedSessionController@store` | Visitante (`guest`) |
| `POST` | `/logout` | `logout` | `AuthenticatedSessionController@destroy` | Autenticado |
| `GET` | `/dashboard` | `dashboard` | `DashboardController@index` | `dashboard.view` |
| `GET` | `/secretaries` | `secretaries.index` | `SecretaryController@index` | `secretaries.view` |
| `GET` | `/secretaries/create` | `secretaries.create` | `SecretaryController@create` | `secretaries.create` |
| `POST` | `/secretaries` | `secretaries.store` | `SecretaryController@store` | `secretaries.create` |
| `GET` | `/secretaries/{secretary}` | `secretaries.show` | `SecretaryController@show` | `secretaries.view` |
| `GET` | `/secretaries/{secretary}/edit` | `secretaries.edit` | `SecretaryController@edit` | `secretaries.update` |
| `PUT/PATCH` | `/secretaries/{secretary}` | `secretaries.update` | `SecretaryController@update` | `secretaries.update` |
| `DELETE` | `/secretaries/{secretary}` | `secretaries.destroy` | `SecretaryController@destroy` | `secretaries.delete` |
| `PATCH` | `/secretaries/{secretary}/status` | `secretaries.status` | `SecretaryController@toggleStatus` | `secretaries.delete` |
| `GET` | `/churches` | `churches.index` | `ChurchController@index` | `churches.view` |
| `GET` | `/churches/create` | `churches.create` | `ChurchController@create` | `churches.create` |
| `POST` | `/churches` | `churches.store` | `ChurchController@store` | `churches.create` |
| `GET` | `/churches/{church}` | `churches.show` | `ChurchController@show` | `churches.view` |
| `GET` | `/churches/{church}/edit` | `churches.edit` | `ChurchController@edit` | `churches.update` |
| `PUT/PATCH` | `/churches/{church}` | `churches.update` | `ChurchController@update` | `churches.update` |
| `DELETE` | `/churches/{church}` | `churches.destroy` | `ChurchController@destroy` | `churches.delete` |
| `PATCH` | `/churches/{church}/status` | `churches.status` | `ChurchController@toggleStatus` | `churches.update` |
| `GET` | `/members` | `members.index` | `MemberController@index` | `members.view` |
| `GET` | `/members/create` | `members.create` | `MemberController@create` | `members.create` |
| `POST` | `/members` | `members.store` | `MemberController@store` | `members.create` |
| `GET` | `/members/{member}` | `members.show` | `MemberController@show` | `members.view` |
| `GET` | `/members/{member}/edit` | `members.edit` | `MemberController@edit` | `members.update` |
| `PUT/PATCH` | `/members/{member}` | `members.update` | `MemberController@update` | `members.update` |
| `DELETE` | `/members/{member}` | `members.destroy` | `MemberController@destroy` | `members.delete` |
| `GET` | `/members/{member}/print` | `members.print` | `MemberController@print` | `members.view` |
| `PATCH` | `/members/{member}/status` | `members.status` | `MemberController@toggleStatus` | `members.update` |
| `GET` | `/transfers` | `transfers.index` | `TransferController@index` | `members.transfer` |
| `GET` | `/transfers/{transfer}` | `transfers.show` | `TransferController@show` | `members.transfer` |
| `GET` | `/members/{member}/transfer` | `members.transfer.create` | `TransferController@create` | `members.transfer` |
| `POST` | `/members/{member}/transfer` | `members.transfer.store` | `TransferController@store` | `members.transfer` |
| `GET` | `/reports` | `reports.index` | `ReportController@index` | `reports.view` |
| `GET` | `/reports/members` | `reports.members` | `ReportController@members` | `reports.view` |
| `GET` | `/reports/birthdays` | `reports.birthdays` | `ReportController@birthdays` | `reports.view` |
| `GET` | `/reports/churches` | `reports.churches` | `ReportController@churches` | `reports.view` |
| `GET` | `/reports/transfers` | `reports.transfers` | `ReportController@transfers` | `reports.view` |
| `GET` | `/reports/secretaries` | `reports.secretaries` | `ReportController@secretaries` | `reports.view` |
| `GET` | `/audit` | `audit.index` | `AuditController@index` | `audit.view` |
| `GET` | `/audit/{log}` | `audit.show` | `AuditController@show` | `audit.view` |

---

## 🛡️ 5. Proteções de Segurança Implementadas

1. **Rate Limiting no Login**: Limite de 5 tentativas consecutivas por e-mail/IP com bloqueio temporário exponencial via `LoginRequest`.
2. **Security Headers HTTP Globais**: Middleware [`SecurityHeaders.php`](file:///c:/Users/Carlos%20Fideles/Desktop/Documentos%20da%20igreja/sistema-igreja/app/Http/Middleware/SecurityHeaders.php) injeta:
   - `X-Frame-Options: SAMEORIGIN` (proteção contra Clickjacking)
   - `X-Content-Type-Options: nosniff` (proteção contra MIME-Sniffing)
   - `X-XSS-Protection: 1; mode=block`
   - `Referrer-Policy: strict-origin-when-cross-origin`
   - `Permissions-Policy: camera=(), microphone=(), geolocation=()`
   - Remoção dos cabeçalhos informativos `X-Powered-By` e `Server`.
3. **Validação de Uploads por Conteúdo Real (finfo)**:
   - Os arquivos de fotos de membros e logotipos de congregações são inspecionados via `finfo_file` para validação do MIME real (`image/jpeg`, `image/png`, `image/webp`), impedindo o envio de scripts executáveis mascarados.
   - Nomes de arquivos sanitizados com `$file->guessExtension()`.
4. **Proteção contra IDOR (Insecure Direct Object Reference)**:
   - As Policies (`ChurchPolicy`, `MemberPolicy`, `UserPolicy`) e Form Requests validam o vínculo da congregação alvo com o usuário logado antes de processar qualquer exibição ou modificação.
5. **Prevenção de Escalada de Privilégios**:
   - Secretários locais são proibidos de atribuir abrangência `REGIONAL` ou conceder cargos com nível hierárquico superior ao próprio.
6. **Imunidade do 1º Secretário Regional**:
   - O 1º Secretário Regional não pode ser desativado, bloqueado ou excluído por nenhum usuário.

---

## 🧪 6. Suíte de Testes Automatizados

O sistema conta com **26 testes automatizados e 119 asserções** validadas via PHPUnit com banco SQLite in-memory:

- **Autenticação (`AuthTest.php`)**: Login, Rate Limiting, bloqueio de inativos/bloqueados e Logout.
- **Isolamento de Escopo (`ScopeIsolationTest.php`)**: Testes com usuários Regionais e Locais, validação de bloqueio 403 para congregações/membros de terceiros e isolamento em relatórios.
- **Gestão de Membros (`MemberManagementTest.php`)**: CRUD, enums de status, transferências históricas e soft delete.
- **Hierarquia (`SecretaryHierarchyTest.php`)**: Criação de secretários, imunidade do 1º Secretário e bloqueio de auto-exclusão.
- **Segurança & Auditoria (`SecurityAndAuditTest.php`)**: Presença de cabeçalhos HTTP e geração estruturada de logs de auditoria.
- **Unitários (`UserTest.php`)**: Lógica dos métodos de escopo e `canAccessChurch()`.

Para rodar os testes:
```bash
php artisan test
```

---

## 🚀 7. Guia de Implantação e Preparação para Produção

### 7.1 Requisitos do Servidor
- **PHP**: 8.2 ou superior (com extensões: `bcmath`, `ctype`, `curl`, `dom`, `fileinfo`, `filter`, `hash`, `mbstring`, `openssl`, `pcre`, `pdo_mysql`, `session`, `tokenizer`, `xml`).
- **Banco de Dados**: MySQL 8.0+ ou MariaDB 10.5+.
- **Servidor Web**: Nginx ou Apache com módulo `mod_rewrite` ativado e apontamento da raiz para o diretório `/public`.
- **Composer**: Versão 2.x+.

### 7.2 Passo a Passo de Instalação no Servidor

1. **Clonar o Repositório e Instalar Dependências**:
   ```bash
   git clone <url-do-repositorio> sistema-igreja
   cd sistema-igreja
   composer install --no-dev --optimize-autoloader
   ```

2. **Configuração de Variáveis de Ambiente (`.env`)**:
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```
   *Edite o arquivo `.env` ajustando as credenciais de banco e URL:*
   ```env
   APP_NAME="SISTEMA DE GESTÃO IEADM-DF"
   APP_ENV=production
   APP_DEBUG=false
   APP_URL=https://gestao.ieadm-df.org.br

   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=ieadm_gestao
   DB_USERNAME=usuario_seguro
   DB_PASSWORD=senha_forte_aqui

   SESSION_DRIVER=file
   SESSION_LIFETIME=120
   SESSION_ENCRYPT=true
   SESSION_SECURE_COOKIE=true
   ```

3. **Execução das Migrações e Seeders Iniciais**:
   ```bash
   php artisan migrate --force
   php artisan db:seed --class=RoleSeeder --force
   php artisan db:seed --class=PermissionSeeder --force
   php artisan db:seed --class=AdminUserSeeder --force
   ```

4. **Link Simbólico de Armazenamento de Arquivos**:
   ```bash
   php artisan storage:link
   ```

5. **Permissões de Diretórios**:
   ```bash
   chown -R www-data:www-data storage bootstrap/cache
   chmod -R 775 storage bootstrap/cache
   ```

6. **Otimização de Desempenho do Laravel**:
   ```bash
   php artisan config:cache
   php artisan route:cache
   php artisan view:cache
   ```

7. **Configuração do Logo Oficial**:
   - Faça o upload do arquivo oficial do logotipo em formato PNG para `public/assets/images/logo.png`.
   - O sistema aplicará o logotipo automaticamente na tela de login, barra lateral e cabeçalhos.

### 7.3 Rotina de Backup Recomendada
Agendar via `crontab` ou ferramenta do servidor o backup diário do banco de dados MySQL:
```bash
mysqldump -u usuario_seguro -p'senha' ieadm_gestao | gzip > /backups/ieadm_gestao_$(date +\%Y\%m\%d).sql.gz
```

---

## 📌 8. Acesso Inicial Padrão

Após executar os seeders, o usuário administrador supremo inicial estará disponível:
- **E-mail**: `admin@ieadm-df.org.br`
- **Senha Inicial**: `Admin@2026!`
- **Cargo**: 1º Secretário
- **Abrangência**: REGIONAL (Acesso pleno a todas as congregações)

> **IMPORTANTE**: Altere a senha do administrador logo no primeiro acesso ao sistema através do módulo de edição de perfil.