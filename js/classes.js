document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('hero-search-input');
    const intensitySelect = document.getElementById('filter-intensity');
    const trainerSelect = document.getElementById('filter-trainer');
    const dateInput = document.getElementById('filter-date');
    const timeSelect = document.getElementById('filter-time');
    const classesContainer = document.getElementById('classes-container');

    // 1. Ler o URL à procura do ID do treinador
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.has('trainer_id')) {
        trainerSelect.value = urlParams.get('trainer_id');
    }

    // 2. Chamar a API inicial
    fetchClasses();

    function fetchClasses() {
        const params = new URLSearchParams({
            q: searchInput.value,
            intensity: intensitySelect.value,
            trainer_id: trainerSelect.value,
            date: dateInput.value,
            time: timeSelect.value
        });

        fetch('../api/api_classes.php?' + params.toString())
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    renderClasses(data.data);
                }
            })
            .catch(error => console.error('Erro no AJAX:', error));
    }

    function renderClasses(sessions) {
        if (sessions.length === 0) {
            classesContainer.innerHTML = `
                <div style="text-align: center; padding: 3rem 0; color: var(--color-text-muted);">
                    <h3>Nenhuma sessão agendada com estes filtros.</h3>
                    <p>Tenta procurar por outro dia ou período horário.</p>
                </div>
            `;
            return;
        }

        let html = '<ul class="classes-grid">';
        
        sessions.forEach(s => {
            const badgeColor = s.intensity === 'Alta' ? 'badge-amber' : (s.intensity === 'Baixa' ? 'badge-green' : 'badge-blue');
            const image = s.image ? s.image : '../img/hiit.jpg';
            const trainerName = s.trainer_name ? s.trainer_name : 'Ginásio';
            
            const timeFormatted = s.start_time.substring(0, 5);
            const spotsLeft = s.capacity - s.active_enrollments;

            html += `
                <li class="class-card">
                    <div class="class-img-wrap">
                        <img src="${image}" alt="${s.name}" />
                    </div>
                    <div class="class-card-body">
                        <div class="class-card-tags">
                            <span class="badge badge-blue">${s.type}</span>
                            <span class="badge ${badgeColor}">${s.intensity}</span>
                        </div>
                        <h3 style="margin-bottom: 0.2rem;">${s.name}</h3>
                        
                        <p style="font-size: 0.9rem; font-weight: bold; color: #4f46e5; margin-bottom: 0.5rem;">
                            ${s.session_date} às ${timeFormatted}
                        </p>
                        <p style="font-size: 0.85rem; color: #64748b; margin-bottom: 1rem;">
                            Sala: ${s.room} <br> Vagas: ${spotsLeft}/${s.capacity}
                        </p>

                        <div class="class-card-footer">
                            <span class="trainer-name">${trainerName}</span>
                            <a href="class.php?id=${s.class_id}&session_id=${s.session_id}" class="btn btn-outline btn-sm">Ver Sessão</a>
                        </div>
                    </div>
                </li>
            `;
        });
        
        html += '</ul>';
        classesContainer.innerHTML = html;
    }

    // Escutar eventos
    searchInput.addEventListener('input', fetchClasses);
    intensitySelect.addEventListener('change', fetchClasses);
    trainerSelect.addEventListener('change', fetchClasses);
    dateInput.addEventListener('change', fetchClasses);
    timeSelect.addEventListener('change', fetchClasses);
});