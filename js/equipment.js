document.addEventListener('DOMContentLoaded', function () {
  const filters = document.getElementById('equipment-filters');
  const searchInput = document.getElementById('equipment-search');
  const categorySelect = document.getElementById('equipment-category');
  const rentableSelect = document.getElementById('equipment-rentable');
  const grid = document.getElementById('equipment-grid');
  const emptyState = document.getElementById('equipment-filter-empty');

  if (!filters || !searchInput || !categorySelect || !rentableSelect || !grid || !emptyState) {
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

  function appendDetail(list, term, description) {
    const row = document.createElement('div');
    appendText(row, 'dt', term);
    appendText(row, 'dd', description);
    list.appendChild(row);
  }

  function createEquipmentCard(item) {
    const card = document.createElement('li');
    card.className = 'equipment-card';

    const header = document.createElement('div');
    header.className = 'equipment-card-header';

    const titleWrap = document.createElement('div');
    appendText(titleWrap, 'p', item.category);
    appendText(titleWrap, 'h3', item.name);
    header.appendChild(titleWrap);
    appendText(header, 'strong', String(item.available_units));
    card.appendChild(header);

    const details = document.createElement('dl');
    details.className = 'equipment-details';
    appendDetail(details, 'Total', String(item.quantity));
    appendDetail(details, 'Em manutenção', String(item.maintenance_quantity));
    appendDetail(details, 'Localização', item.location);
    appendDetail(details, 'Aluguer', item.rentable ? 'Disponível' : 'Indisponível');
    card.appendChild(details);

    const button = document.createElement('button');
    button.type = 'button';
    button.disabled = true;
    button.className = item.rentable ? 'btn btn-primary btn-full' : 'btn btn-outline btn-full';
    button.textContent = item.rentable ? 'Aluguer em breve' : 'Apenas uso no ginásio';
    card.appendChild(button);

    return card;
  }

  function renderEquipment(items) {
    grid.replaceChildren();

    items.forEach((item) => {
      grid.appendChild(createEquipmentCard(item));
    });

    emptyState.hidden = items.length !== 0;
  }

  function fetchEquipment() {
    const params = new URLSearchParams({
      q: searchInput.value,
      category: categorySelect.value,
      rentable: rentableSelect.value,
    });

    fetch('../api/api_equipment.php?' + params.toString(), {
      headers: { Accept: 'application/json' },
    })
      .then((response) => response.json())
      .then((payload) => {
        if (payload.status === 'success') {
          renderEquipment(payload.data);
        }
      })
      .catch(() => {
        emptyState.hidden = false;
      });
  }

  function scheduleFetch() {
    window.clearTimeout(requestTimer);
    requestTimer = window.setTimeout(fetchEquipment, 150);
  }

  filters.addEventListener('submit', function (event) {
    event.preventDefault();
    fetchEquipment();
  });
  searchInput.addEventListener('input', scheduleFetch);
  categorySelect.addEventListener('change', fetchEquipment);
  rentableSelect.addEventListener('change', fetchEquipment);
});
