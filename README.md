# LiftToBig — Gym Management Website

Projeto académico de LTW (Linguagens e Tecnologias Web) — Faculdade de Engenharia da Universidade do Porto.



## Como abrir

Como agora as páginas usam templates PHP, é preciso abrir através de um servidor PHP:

```bash
php -S localhost:8000
```

Depois abrir `http://localhost:8000/pages/landingpage.php` no browser.


## Estrutura de Ficheiros

```
/
├── actions/
│   ├── action_login.php    ← Processa o formulário de login
│   ├── action_logout.php   ← Termina a sessão do utilizador
│   ├── action_register.php ← Processa o formulário de registo
│   └── action_update_account.php ← Atualiza os detalhes da conta
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
│   └── account.php     ← Página de edição da conta
├── templates/
│   ├── header.php      ← Cabeçalho, navegação e imports CSS comuns
│   ├── footer.php      ← Rodapé comum
│   └── trainers.php    ← Template com dados/listagem de treinadores
│
├── database/
│   ├── connection.php  ← Ligação à base de dados SQLite
│   ├── database.sql    ← Script de criação/inicialização da base de dados
│   ├── ltw.db          ← Base de dados SQLite do projeto
│   ├── Trainer.php     ← Modelo e operações relacionadas com treinadores
│   └── User.php        ← Modelo e operações relacionadas com utilizadores
│
├── utils/
│   └── session.php     ← Funções auxiliares para gestão de sessões
│
├── css/
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

Não foi implementado a criação de contas para Trainers/Staff, foram injetados os dados, ainda vai ser implementado.
