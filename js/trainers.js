document.addEventListener('DOMContentLoaded', function () {
  const form = document.getElementById('trainers-search-form');
  const searchInput = document.getElementById('trainer-search');
  const grid = document.getElementById('trainers-grid');
  const emptyState = document.getElementById('trainers-empty-state');
  const resultSummary = document.getElementById('trainers-result-summary');

  if (!form || !searchInput || !grid || !emptyState || !resultSummary) {
    return;
  }

  let requestTimer = null;

  function appendText(parent, tagName, text, className) {
    const element = document.createElement(tagName);
    element.textContent = text;

    if (className) {
      element.className = className;
    }

    parent.appendChild(element);
    return element;
  }

  function createButtonLink(href, text, className) {
    const link = document.createElement('a');
    link.href = href;
    link.textContent = text;
    link.className = className;
    return link;
  }

  function createTrainerCard(trainer) {
    const card = document.createElement('li');
    card.className = 'trainer-card-full';

    const photoWrap = document.createElement('div');
    photoWrap.className = 'trainer-photo-wrap';

    if (trainer.photo) {
      const img = document.createElement('img');
      img.src = trainer.photo;
      img.alt = trainer.name;
      img.className = 'trainer-photo';
      photoWrap.appendChild(img);
    } else {
      const initials = document.createElement('span');
      initials.className = 'trainer-photo trainer-photo-initials';
      initials.setAttribute('aria-hidden', 'true');
      initials.textContent = trainer.initials;
      photoWrap.appendChild(initials);
    }

    card.appendChild(photoWrap);

    const body = document.createElement('div');
    body.className = 'trainer-body';

    const header = document.createElement('div');
    header.className = 'trainer-header';
    const titleWrap = document.createElement('div');
    appendText(titleWrap, 'h3', trainer.name);
    appendText(titleWrap, 'p', trainer.specialty, 'trainer-specialty');
    header.appendChild(titleWrap);
    appendText(header, 'span', '★ ' + Number(trainer.rating).toFixed(1), 'trainer-rating');
    body.appendChild(header);

    appendText(body, 'p', trainer.bio, 'trainer-bio');

    const badges = document.createElement('div');
    badges.className = 'trainer-badges';
    trainer.badges.forEach((badge) => {
      appendText(badges, 'span', badge, 'badge badge-blue');
    });
    body.appendChild(badges);

    const stats = document.createElement('div');
    stats.className = 'trainer-stats';
    [
      [String(trainer.sessions) + '+', 'Sessões'],
      ['★ ' + Number(trainer.rating).toFixed(1), 'Avaliação'],
    ].forEach(([value, label]) => {
      const stat = document.createElement('div');
      stat.className = 'trainer-stat';
      appendText(stat, 'span', value, 'stat-value');
      appendText(stat, 'span', label, 'stat-label');
      stats.appendChild(stat);
    });
    body.appendChild(stats);

    const actions = document.createElement('div');
    actions.className = 'trainer-actions';
    actions.appendChild(createButtonLink('trainer.php?id=' + trainer.id, 'Ver Perfil', 'btn btn-outline btn-sm'));
    actions.appendChild(createButtonLink('classes.php?trainer_id=' + trainer.id, 'Ver Aulas', 'btn btn-primary btn-sm'));
    body.appendChild(actions);

    card.appendChild(body);
    return card;
  }

  function updateSummary(count, query) {
    if (query === '') {
      resultSummary.textContent = 'Profissionais certificados, apaixonados pelo que fazem e totalmente dedicados ao teu sucesso. Escolhe o teu mentor e começa hoje.';
      return;
    }

    const noun = count === 1 ? 'treinador compatível' : 'treinadores compatíveis';
    resultSummary.textContent = 'Encontrámos ' + count + ' ' + noun + ' com a tua pesquisa.';
  }

  function renderTrainers(trainers) {
    grid.replaceChildren();

    trainers.forEach((trainer) => {
      grid.appendChild(createTrainerCard(trainer));
    });

    grid.hidden = trainers.length === 0;
    emptyState.hidden = trainers.length !== 0;
    updateSummary(trainers.length, searchInput.value.trim());
  }

  function fetchTrainers() {
    const params = new URLSearchParams({ q: searchInput.value });

    fetch('../api/api_trainers.php?' + params.toString(), {
      headers: { Accept: 'application/json' },
    })
      .then((response) => response.json())
      .then((payload) => {
        if (payload.status === 'success') {
          renderTrainers(payload.data);
        }
      })
      .catch(() => {
        grid.hidden = true;
        emptyState.hidden = false;
      });
  }

  function scheduleFetch() {
    window.clearTimeout(requestTimer);
    requestTimer = window.setTimeout(fetchTrainers, 150);
  }

  form.addEventListener('submit', function (event) {
    event.preventDefault();
    fetchTrainers();
  });
  searchInput.addEventListener('input', scheduleFetch);
});
