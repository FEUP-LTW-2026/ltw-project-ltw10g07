# LiftToBig

Projeto académico de LTW (Linguagens e Tecnologias Web) da Faculdade de Engenharia da Universidade do Porto.

LiftToBig é uma aplicação web em PHP para gestão de um ginásio. Inclui páginas públicas de apresentação, aulas, treinadores e equipamentos, além de áreas autenticadas para membros, treinadores e administradores.

## Funcionalidades

- Landing page com destaques do ginásio, aulas e treinadores.
- Registo, login, logout e edição de conta.
- Upload de fotografia de perfil.
- Catálogo de aulas com pesquisa/filtros AJAX.
- Página de detalhe de aula com sessões, inscrições e avaliações.
- Catálogo de treinadores com pesquisa AJAX, perfil público e avaliações.
- Área de treinador para consultar sessões atribuídas e membros inscritos.
- Edição de perfil público de treinador.
- Catálogo de equipamentos com pesquisa/filtros AJAX.
- Aluguer de equipamentos por membros e treinadores, com horários disponíveis e cancelamento.
- Painel de administração para gerir utilizadores, aulas, equipamentos e alugueres ativos.

## Perfis

O projeto suporta três papéis:

- `member`: pode inscrever-se em aulas, avaliar aulas/treinadores e alugar equipamentos.
- `trainer`: pode agendar sessões, consultar o painel de treinador, editar o perfil público e alugar equipamentos.
- `admin`: pode gerir utilizadores, aulas e equipamentos. Na página de equipamentos, o admin consulta alugueres ativos em vez de alugar.

## Como correr

É necessário abrir o projeto através de um servidor PHP, porque as páginas usam sessões, templates e SQLite.

```bash
php -S localhost:8000
```

Depois abre:

```text
http://localhost:8000/
```

O `index.php` redireciona para `pages/landingpage.php`.

## Base de Dados

A aplicação usa SQLite. A ligação principal está em:

```text
database/ltw.db
```

O schema está em:

```text
database/database.sql
```

As tabelas principais são:

- `user`
- `trainer`
- `class`
- `class_session`
- `enrollment`
- `review`
- `trainer_review`
- `equipment`
- `equipment_rentals`

## Estrutura

```text
/
├── index.php
├── actions/
│   ├── action_admin_class.php
│   ├── action_admin_equipment.php
│   ├── action_admin_user.php
│   ├── action_create_class.php
│   ├── action_create_session.php
│   ├── action_delete_class.php
│   ├── action_enroll.php
│   ├── action_login.php
│   ├── action_logout.php
│   ├── action_register.php
│   ├── action_review_class.php
│   ├── action_review_trainer.php
│   ├── action_update_account.php
│   ├── action_update_trainer_profile.php
│   └── action_upload_photo.php
├── api/
│   ├── admin_users.php
│   ├── api_classes.php
│   ├── api_equipment.php
│   ├── api_trainers.php
│   ├── cancel_equipment_rental.php
│   ├── create_equipment_rental.php
│   └── equipment_availability.php
├── css/
│   ├── account.css
│   ├── admin.css
│   ├── base.css
│   ├── class.css
│   ├── classes.css
│   ├── components.css
│   ├── equipment.css
│   ├── landingpage.css
│   ├── login.css
│   ├── publish_class.css
│   ├── responsive.css
│   ├── trainer_dashboard.css
│   └── trainers.css
├── database/
│   ├── ClassCatalog.php
│   ├── ClassReview.php
│   ├── Equipment.php
│   ├── Trainer.php
│   ├── TrainerReview.php
│   ├── User.php
│   ├── connection.php
│   ├── database.sql
│   └── ltw.db
├── js/
│   ├── classes.js
│   ├── equipment.js
│   └── trainers.js
├── javascript/
│   └── admin.js
├── pages/
│   ├── account.php
│   ├── admin.php
│   ├── class.php
│   ├── classes.php
│   ├── equipment.php
│   ├── index.php
│   ├── landingpage.php
│   ├── login.php
│   ├── publish_class.php
│   ├── register.php
│   ├── trainer.php
│   ├── trainer_dashboard.php
│   ├── trainer_profile_edit.php
│   └── trainers.php
├── templates/
│   ├── flash_messages.php
│   ├── footer.php
│   ├── header.php
│   └── trainers.php
├── uploads/
│   └── profile_photos/
└── utils/
    ├── security.php
    └── session.php
```

## Páginas Principais

| Página | Descrição |
| --- | --- |
| `pages/landingpage.php` | Página inicial |
| `pages/classes.php` | Catálogo de aulas |
| `pages/class.php` | Detalhe de uma aula |
| `pages/trainers.php` | Catálogo de treinadores |
| `pages/trainer.php` | Perfil de treinador |
| `pages/equipment.php` | Catálogo e aluguer de equipamentos |
| `pages/account.php` | Edição de conta |
| `pages/publish_class.php` | Criação de sessões por treinadores |
| `pages/trainer_dashboard.php` | Painel do treinador |
| `pages/trainer_profile_edit.php` | Edição do perfil público do treinador |
| `pages/admin.php` | Painel de administração |

## Organização do CSS e JS

O CSS está dividido por responsabilidades:

- `css/base.css`: tokens de design, reset e estilos globais.
- `css/components.css`: botões, cards, formulários, badges e componentes reutilizáveis.
- `css/[pagina].css`: estilos específicos de cada página.
- `css/responsive.css`: ajustes globais de responsividade.

O JavaScript está separado por área:

- `js/classes.js`: filtros AJAX das aulas.
- `js/trainers.js`: pesquisa AJAX dos treinadores.
- `js/equipment.js`: filtros, alugueres e modal de horários dos equipamentos.
- `javascript/admin.js`: tabs, pesquisa de utilizadores e interações do painel admin.

## Segurança

- Sessões com cookies `HttpOnly` e `SameSite=Lax`.
- Tokens CSRF em formulários sensíveis.
- Passwords guardadas com hash.
- Validações de papel antes de ações restritas.
- Queries preparadas nas operações com input do utilizador.

## Nota de Desenvolvimento

O ficheiro `database/ltw.db` é usado para desenvolvimento local. Se for necessário reconstruir a base de dados, usa o schema em `database/database.sql` e volta a inserir os dados de teste necessários.
