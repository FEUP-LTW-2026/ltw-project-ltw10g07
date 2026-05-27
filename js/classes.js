document.addEventListener('DOMContentLoaded', function() {
    // 1. Apanhar os elementos do ecrã
    const form = document.querySelector('.filters-wrapper form');
    const searchInput = document.getElementById('hero-search-input');
    const intensitySelect = document.getElementById('filter-intensity');
    const trainerSelect = document.getElementById('filter-trainer');
    const sortSelect = document.getElementById('filter-sort');
    const classesContainer = document.getElementById('classes-container'); // Onde vamos injetar as aulas

    // 2. A função mágica que vai à API buscar as aulas via AJAX
    function fetchClasses() {
        // Preparar os dados do filtro para enviar no link
        const params = new URLSearchParams({
            q: searchInput.value,
            intensity: intensitySelect.value,
            trainer_id: trainerSelect.value,
            sort: sortSelect.value
        });

        // Chamar a API em pano de fundo
        fetch('../api/api_classes.php?' + params.toString())
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    renderClasses(data.data); // Mandar desenhar as aulas recebidas
                }
            })
            .catch(error => console.error('Erro no AJAX:', error));
    }

    // 3. A função que apaga o que lá estava e desenha as novas aulas
    function renderClasses(classes) {
        if (classes.length === 0) {
            classesContainer.innerHTML = `
                <div style="text-align: center; padding: 3rem 0; color: var(--color-text-muted);">
                    <h3>Nenhuma aula encontrada com os filtros selecionados.</h3>
                    <p>Tenta mudar os critérios de pesquisa ou intensidade.</p>
                </div>
            `;
            return;
        }

        let html = '<ul class="classes-grid">';

        classes.forEach(c => {
            // Calcular a cor da badge da intensidade
            const badgeColor = c.intensity === 'Alta' ? 'badge-amber' : (c.intensity === 'Baixa' ? 'badge-green' : 'badge-blue');
            const imageMarkup = c.image
                ? `<img src="${c.image}" alt="${c.name}" />`
                : `<div class="class-image-placeholder" aria-hidden="true">${c.name.charAt(0).toUpperCase()}</div>`;
            const trainerName = c.trainer_name ? c.trainer_name : 'Ginásio';

            html += `
                <li class="class-card">
                    <div class="class-img-wrap">
                        ${imageMarkup}
                    </div>
                    <div class="class-card-body">
                        <div class="class-card-tags">
                            <span class="badge badge-blue">${c.type}</span>
                            <span class="badge ${badgeColor}">${c.intensity}</span>
                        </div>
                        <h3>${c.name}</h3>
                        <p class="class-time">${c.duration} min</p>
                        <div class="class-card-footer">
                            <span class="trainer-name">${trainerName}</span>
                            <a href="class.php?id=${c.id}" class="btn btn-outline btn-sm">Ver Detalhes</a>
                        </div>
                    </div>
                </li>
            `;
        });

        html += '</ul>';
        classesContainer.innerHTML = html; // Injetar o novo HTML na página!
    }

    // 4. Avisar o JavaScript para correr a pesquisa quando mudamos os filtros
    searchInput.addEventListener('input', fetchClasses); // Assim que o utilizador digita uma letra!
    intensitySelect.addEventListener('change', fetchClasses);
    trainerSelect.addEventListener('change', fetchClasses);
    sortSelect.addEventListener('change', fetchClasses);

    // Bloquear o envio normal do formulário (que recarregava a página)
    form.addEventListener('submit', function(e) {
        e.preventDefault();
        fetchClasses();
    });
});
