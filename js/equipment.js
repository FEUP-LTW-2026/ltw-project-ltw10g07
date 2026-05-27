document.addEventListener('DOMContentLoaded', function () {
  const filters = document.getElementById('equipment-filters');
  const searchInput = document.getElementById('equipment-search');
  const categorySelect = document.getElementById('equipment-category');
  const rentableSelect = document.getElementById('equipment-rentable');
  const grid = document.getElementById('equipment-grid');
  const emptyState = document.getElementById('equipment-filter-empty');
  const rentalModal = document.getElementById('equipment-rental-modal');
  const slotGrid = document.getElementById('equipment-slot-grid');
  const rentalEquipment = document.getElementById('rental-modal-equipment');
  const rentalMessage = document.getElementById('equipment-rental-message');
  const confirmRental = document.getElementById('confirm-equipment-rental');
  const currentRentalPanel = document.getElementById('equipment-current-rental');
  const currentRentalText = document.getElementById('equipment-current-rental-text');
  const cancelRental = document.getElementById('cancel-equipment-rental');

  if (!filters || !searchInput || !categorySelect || !rentableSelect || !grid || !emptyState || !rentalModal || !slotGrid || !rentalEquipment || !rentalMessage || !confirmRental || !currentRentalPanel || !currentRentalText || !cancelRental) {
    return;
  }

  let requestTimer = null;
  let selectedEquipment = null;
  let selectedSlot = null;
  let currentRental = null;

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

  function formatSlotTime(value) {
    return value.slice(11, 16);
  }

  function clearRentalState() {
    selectedSlot = null;
    confirmRental.disabled = true;
    rentalMessage.textContent = '';
    slotGrid.querySelectorAll('.equipment-slot').forEach((button) => {
      button.classList.remove('selected');
    });
  }

  function closeRentalModal() {
    rentalModal.hidden = true;
    selectedEquipment = null;
    currentRental = null;
    currentRentalPanel.hidden = true;
    clearRentalState();
  }

  function updateCurrentRental(rental) {
    currentRental = rental || null;
    currentRentalPanel.hidden = !currentRental;

    if (!currentRental) {
      currentRentalText.textContent = '';
      return;
    }

    currentRentalText.textContent = 'Já tens um aluguer ativo hoje: ' + currentRental.equipment_name + ' (' + formatSlotTime(currentRental.start) + '-' + formatSlotTime(currentRental.end) + ').';
  }

  function renderSlots(slots) {
    slotGrid.replaceChildren();
    clearRentalState();

    slots.forEach((slot) => {
      const button = document.createElement('button');
      button.type = 'button';
      button.className = 'equipment-slot';
      button.disabled = Number(slot.available) <= 0 || currentRental !== null;
      button.dataset.start = slot.start;
      button.dataset.end = slot.end;
      button.textContent = formatSlotTime(slot.start) + '-' + formatSlotTime(slot.end);

      const availability = document.createElement('span');
      availability.textContent = Number(slot.available) > 0 ? slot.available + ' disp.' : 'Indisponível';
      button.appendChild(availability);

      button.addEventListener('click', function () {
        selectedSlot = {
          start: slot.start,
          end: slot.end,
        };
        slotGrid.querySelectorAll('.equipment-slot').forEach((slotButton) => {
          slotButton.classList.toggle('selected', slotButton === button);
        });
        confirmRental.disabled = false;
        rentalMessage.textContent = '';
      });

      slotGrid.appendChild(button);
    });
  }

  function loadSlots(equipmentId, messageAfterLoad) {
    slotGrid.replaceChildren();
    rentalMessage.textContent = 'A carregar horários...';
    confirmRental.disabled = true;

    fetch('../api/equipment_availability.php?equipment_id=' + encodeURIComponent(equipmentId), {
      headers: { Accept: 'application/json' },
    })
      .then((response) => response.json())
      .then((payload) => {
        if (!payload.success) {
          throw new Error(payload.message || 'Erro ao carregar horários.');
        }

        rentalMessage.textContent = '';
        updateCurrentRental(payload.current_rental);
        renderSlots(payload.slots);
        rentalMessage.textContent = messageAfterLoad || (currentRental ? 'Já alugaste este equipamento hoje. Cancela esse aluguer para escolher outro horário.' : '');
      })
      .catch((error) => {
        slotGrid.replaceChildren();
        rentalMessage.textContent = error.message || 'Erro ao carregar horários.';
      });
  }

  function openRentalModal(equipmentId, equipmentName) {
    selectedEquipment = {
      id: equipmentId,
      name: equipmentName,
    };
    rentalEquipment.textContent = equipmentName;
    rentalModal.hidden = false;
    loadSlots(equipmentId);
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
    appendDetail(details, 'Total', String(item.total_quantity));
    appendDetail(details, 'Em manutenção', String(item.maintenance_quantity));
    appendDetail(details, 'Localização', item.location);
    appendDetail(details, 'Aluguer', item.rental_available ? 'Disponível' : 'Indisponível');
    card.appendChild(details);

    const button = document.createElement('button');
    button.type = 'button';
    button.className = item.rental_available ? 'btn btn-primary btn-full' : 'btn btn-outline btn-full';
    button.textContent = item.rental_available ? 'Alugar hoje' : 'Apenas uso no ginásio';

    if (item.rental_available) {
      button.dataset.rentEquipment = 'true';
      button.dataset.equipmentId = String(item.id);
      button.dataset.equipmentName = item.name;
    } else {
      button.disabled = true;
    }

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

  grid.addEventListener('click', function (event) {
    const rentalsToggle = event.target.closest('[data-equipment-rentals-toggle]');

    if (rentalsToggle) {
      const rentalsPanel = rentalsToggle.nextElementSibling;

      if (rentalsPanel) {
        const shouldOpen = rentalsPanel.hidden;
        rentalsPanel.hidden = !shouldOpen;
        rentalsToggle.setAttribute('aria-expanded', shouldOpen ? 'true' : 'false');
        rentalsToggle.textContent = rentalsToggle.textContent.replace(shouldOpen ? 'Ver' : 'Esconder', shouldOpen ? 'Esconder' : 'Ver');
      }

      return;
    }

    const button = event.target.closest('[data-rent-equipment]');

    if (!button) {
      return;
    }

    openRentalModal(button.dataset.equipmentId, button.dataset.equipmentName);
  });

  rentalModal.addEventListener('click', function (event) {
    if (event.target.matches('[data-close-rental-modal]')) {
      closeRentalModal();
    }
  });

  confirmRental.addEventListener('click', function () {
    if (!selectedEquipment || !selectedSlot) {
      return;
    }

    confirmRental.disabled = true;
    rentalMessage.textContent = 'A confirmar aluguer...';

    const body = new URLSearchParams({
      equipment_id: selectedEquipment.id,
      start_time: selectedSlot.start,
      end_time: selectedSlot.end,
    });

    fetch('../api/create_equipment_rental.php', {
      method: 'POST',
      headers: {
        Accept: 'application/json',
        'Content-Type': 'application/x-www-form-urlencoded',
      },
      body: body.toString(),
    })
      .then((response) => response.json())
      .then((payload) => {
        if (!payload.success) {
          rentalMessage.textContent = payload.message || 'Não foi possível criar o aluguer.';
          confirmRental.disabled = false;
          return;
        }

        loadSlots(selectedEquipment.id, payload.message || 'Aluguer confirmado com sucesso.');
        fetchEquipment();
      })
      .catch(() => {
        rentalMessage.textContent = 'Erro ao confirmar aluguer.';
        confirmRental.disabled = false;
      });
  });

  cancelRental.addEventListener('click', function () {
    if (!selectedEquipment || !currentRental) {
      return;
    }

    cancelRental.disabled = true;
    rentalMessage.textContent = 'A cancelar aluguer...';

    fetch('../api/cancel_equipment_rental.php', {
      method: 'POST',
      headers: {
        Accept: 'application/json',
        'Content-Type': 'application/x-www-form-urlencoded',
      },
      body: new URLSearchParams({ rental_id: currentRental.id }).toString(),
    })
      .then((response) => response.json())
      .then((payload) => {
        rentalMessage.textContent = payload.message || (payload.success ? 'Aluguer cancelado.' : 'Não foi possível cancelar o aluguer.');

        if (payload.success) {
          updateCurrentRental(null);
          loadSlots(selectedEquipment.id, payload.message || 'Aluguer cancelado com sucesso.');
          fetchEquipment();
        }
      })
      .catch(() => {
        rentalMessage.textContent = 'Erro ao cancelar aluguer.';
      })
      .finally(() => {
        cancelRental.disabled = false;
      });
  });
});
