# LiftToBig — Gym Management Website

Projeto académico de LTW (Linguagens e Tecnologias Web) — Faculdade de Engenharia da Universidade do Porto.



## Como abrir

Abrir diretamente os ficheiros `landingpage.html`, `classes.html` e `trainers.html` num browser.


Não é necessário servidor, PHP ou JavaScript para esta primeira entrega.


## Estrutura de Ficheiros

```
/
├── landingpage.html    ← Landing page (página inicial)
├── classes.html        ← Listagem de aulas 
├── trainers.html       ← Perfis de treinadores 
│
├── css/
│   ├── base.css        ← Variáveis, reset e estilos de elementos HTML
│   ├── components.css  ← Componentes reutilizáveis (botões, cards, badges, forms…)
│   ├── index.css       ← Estilos específicos da landing page
│   ├── classes.css     ← Estilos específicos da página de aulas
│   └── trainers.css    ← Estilos específicos dos treinadores
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

| Ficheiro | Responsável | Estado |
|---|---|---|
| `landingpage.html` | [nome] | ✅ HTML + CSS |
| `classes.html` | [nome] |  ✅ HTML + CSS |
| `trainers.html` | [nome] | ✅ HTML + CSS |

## Organização do CSS

O CSS está dividido em 3 camadas:

1. **`css/base.css`** — tokens de design (variáveis de cor, tipografia, espaçamento), reset e estilos padrão de elementos HTML (`body`, `h1–h6`, `a`, `p`, `ul`, `img`, `table`, etc.)

2. **`css/components.css`** — componentes reutilizáveis partilhados entre todas as páginas: botões (primary, outline, ghost, white, disabled, sm/lg/xl), badges, cards, progress bars, avatars, rating, formulários, tabelas, etc.

3. **`css/[pagina].css`** — estilos específicos de cada página (header, hero, secções únicas). Mantido o mais pequeno possível.

## Tag para entrega

```bash
git tag first-delivery-v1
git push origin first-delivery-v1
```