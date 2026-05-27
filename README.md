# LiftToBig — Gym Management Website

Projeto académico de LTW (Linguagens e Tecnologias Web) — Faculdade de Engenharia da Universidade do Porto.



## Como abrir

Como agora as páginas usam templates PHP, é preciso abrir através de um servidor PHP:

```bash
php -S localhost:8000
```

Depois abrir `http://localhost:8000/pages/landingpage.php` no browser.
Também podes abrir `http://localhost:8000/`, que redireciona para a landing page.


## Estrutura de Ficheiros

```
/
├── index.php           ← Entrada do projeto; redireciona para pages/landingpage.php
│
├── actions/
│   ├── action_admin_class.php     ← Gestão admin do catálogo de aulas
│   ├── action_admin_equipment.php ← Gestão admin do equipamento
│   ├── action_admin_user.php      ← Gestão admin de utilizadores
│   ├── action_create_class.php    ← Publicação de aulas por trainers/admins
│   ├── action_login.php    ← Processa o formulário de login
│   ├── action_logout.php   ← Termina a sessão do utilizador
│   ├── action_register.php ← Processa o formulário de registo
│   └── action_update_account.php ← Atualiza os detalhes da conta
│
├── api/
│   └── admin_users.php ← Endpoint JSON/AJAX para pesquisa de utilizadores
│
├── pages/
│   ├── index.php       ← Redireciona para a landing page
│   ├── landingpage.php ← Landing page (página inicial)
│   ├── classes.php     ← Listagem de aulas
│   ├── class.php       ← Detalhe de uma aula
│   ├── trainers.php    ← Listagem de treinadores
│   ├── trainer.php     ← Perfil de um treinador
│   ├── login.php       ← Página de login
│   ├── register.php    ← Página de registo
│   ├── account.php     ← Página de edição da conta
│   ├── publish_class.php ← Página de publicação de aulas
│   └── admin.php       ← Painel de administração
├── templates/
│   ├── header.php      ← Cabeçalho, navegação e imports CSS comuns
│   ├── footer.php      ← Rodapé comum
│   ├── flash_messages.php ← Template reutilizável para mensagens de erro/sucesso
│   └── trainers.php    ← Template com dados/listagem de treinadores
│
├── database/
│   ├── connection.php  ← Ligação à base de dados SQLite
│   ├── database.sql    ← Script de criação/inicialização da base de dados
│   ├── ltw.db          ← Base de dados SQLite do projeto
│   ├── ClassCatalog.php ← Modelo e operações relacionadas com aulas
│   ├── Equipment.php   ← Modelo e operações relacionadas com equipamento
│   ├── TrainerReview.php ← Modelo e operações relacionadas com avaliações
│   ├── Trainer.php     ← Modelo e operações relacionadas com treinadores
│   └── User.php        ← Modelo e operações relacionadas com utilizadores
│
├── utils/
│   └── session.php     ← Funções auxiliares para gestão de sessões
│
├── css/
│   ├── admin.css       ← Estilos específicos do painel admin
│   ├── base.css        ← Variáveis, reset e estilos de elementos HTML
│   ├── components.css  ← Componentes reutilizáveis (botões, cards, badges, forms…)
│   ├── landingpage.css ← Estilos específicos da landing page
│   ├── classes.css     ← Estilos específicos da página de aulas
│   ├── class.css       ← Estilos específicos do detalhe de uma aula
│   ├── trainers.css    ← Estilos específicos das páginas de treinadores
│   ├── login.css       ← Estilos específicos da página de login/registo
│   ├── account.css     ← Estilos específicos da página de conta
│   └── responsive.css  ← Ajustes de responsividade partilhados
│
├── javascript/
│   └── admin.js        ← JS do painel admin, incluindo tabs e pesquisa AJAX
│
└── img/                ← Imagens 
    ├── hero-athlete.jpg
    ├── barbell.jpg
    ├── logo.jpg
    ├── powerhit.jpg
    ├── yoga.jpg
    ├── zumba.jpg
    ├── cycling.jpg
    ├── hiit.jpg
    ├── mobility.jpg
    ├── boxing.jpg
    ├── rui-costa.jpg
    ├── andre.jpg
    └── tomas.jpg
```


## Páginas para a 1ª Entrega

| `pages/landingpage.php` | [nome] | ✅ PHP + HTML + CSS |
| `pages/classes.php` | [nome] |  ✅ PHP + HTML + CSS |
| `pages/login.php` | [nome] | ✅ PHP + HTML + CSS |

## Organização do CSS

O CSS está dividido em 3 camadas:

1. **`css/base.css`** — tokens de design (variáveis de cor, tipografia, espaçamento), reset e estilos padrão de elementos HTML 

2. **`css/components.css`** — componentes reutilizáveis partilhados entre todas as páginas.

3. **`css/[pagina].css`** — estilos específicos de cada página. Mantido o mais pequeno possível.

O projeto já suporta contas `member`, `trainer` e `admin`. Admins conseguem gerir utilizadores, aulas e equipamento no painel de administração.
