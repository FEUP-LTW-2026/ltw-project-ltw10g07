# LiftToBig — Gym Management Website

Projeto académico de LTW (Linguagens e Tecnologias Web) — Faculdade de Engenharia da Universidade do Porto.



## Como abrir

Como agora as páginas usam templates PHP, é preciso abrir através de um servidor PHP:

```bash
php -S localhost:8000
```

Depois abrir `http://localhost:8000/landingpage.php` no browser.


## Estrutura de Ficheiros

```
/
├── landingpage.php    ← Landing page (página inicial)
├── classes.php        ← Listagem de aulas 
├── login.php          ← Página de login
├── templates/
│   ├── header.php     ← Cabeçalho, navegação e imports CSS comuns
│   └── footer.php     ← Rodapé comum
│
├── css/
│   ├── base.css        ← Variáveis, reset e estilos de elementos HTML
│   ├── components.css  ← Componentes reutilizáveis (botões, cards, badges, forms…)
│   ├── index.css       ← Estilos específicos da landing page
│   ├── classes.css     ← Estilos específicos da página de aulas
│   └── login.css    ← Estilos específicos da página de login
│
└── img/                ← Imagens 
    ├── hero-athlete.jpg
    ├── powerhit.jpg
    ├── yoga.jpg
    ├── zumba.jpg
    ├── rui-costa.jpg
    ├── andre.jpg
    └── tomas.jpg
    └── ...
```


## Páginas para a 1ª Entrega

| `landingpage.php` | [nome] | ✅ PHP + HTML + CSS |
| `classes.php` | [nome] |  ✅ PHP + HTML + CSS |
| `login.php` | [nome] | ✅ PHP + HTML + CSS |

## Organização do CSS

O CSS está dividido em 3 camadas:

1. **`css/base.css`** — tokens de design (variáveis de cor, tipografia, espaçamento), reset e estilos padrão de elementos HTML 

2. **`css/components.css`** — componentes reutilizáveis partilhados entre todas as páginas.

3. **`css/[pagina].css`** — estilos específicos de cada página. Mantido o mais pequeno possível.
