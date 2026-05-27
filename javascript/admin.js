const adminTabs = document.querySelectorAll('[data-admin-tab]');
const adminPanels = document.querySelectorAll('[data-admin-panel]');
const defaultPanel = 'users';

function showAdminPanel(panelName) {
  adminTabs.forEach((tab) => {
    const isActive = tab.dataset.adminTab === panelName;
    tab.classList.toggle('active', isActive);
    tab.setAttribute('aria-selected', isActive ? 'true' : 'false');
  });

  adminPanels.forEach((panel) => {
    const isActive = panel.dataset.adminPanel === panelName;
    panel.hidden = !isActive;
    panel.classList.toggle('active', isActive);
  });

  if (history.replaceState) {
    history.replaceState(null, '', '#' + panelName);
  }
}

adminTabs.forEach((tab) => {
  tab.addEventListener('click', () => showAdminPanel(tab.dataset.adminTab));
});

const initialPanel = window.location.hash.replace('#', '') || defaultPanel;
showAdminPanel(document.querySelector('[data-admin-panel="' + initialPanel + '"]') ? initialPanel : defaultPanel);

const userSearch = document.querySelector('#user-search');
const userSearchForm = document.querySelector('[data-user-search-form]');
const userCards = document.querySelectorAll('[data-user-card]');
const userEmpty = document.querySelector('[data-user-empty]');
let userSearchTimeout = null;

function filterUserCardsLocally(query) {
  let visibleCount = 0;

  userCards.forEach((card) => {
    const isVisible = card.dataset.userCard.includes(query);
    card.hidden = !isVisible;
    if (isVisible) {
      visibleCount += 1;
    }
  });

  if (userEmpty) {
    userEmpty.hidden = visibleCount !== 0;
  }
}

async function filterUserCardsWithAjax(query) {
  const response = await fetch('../api/admin_users.php?q=' + encodeURIComponent(query), {
    headers: { Accept: 'application/json' },
  });

  if (!response.ok) {
    throw new Error('User search failed');
  }

  const payload = await response.json();
  const visibleIds = new Set(payload.users.map((user) => String(user.id)));
  let visibleCount = 0;

  userCards.forEach((card) => {
    const isVisible = visibleIds.has(card.dataset.userId);
    card.hidden = !isVisible;
    if (isVisible) {
      visibleCount += 1;
    }
  });

  if (userEmpty) {
    userEmpty.hidden = visibleCount !== 0;
  }
}

function runUserSearch() {
  if (!userSearch) {
    return;
  }

  const query = userSearch.value.trim().toLowerCase();
  filterUserCardsWithAjax(query).catch(() => filterUserCardsLocally(query));
}

if (userSearch) {
  userSearch.addEventListener('input', () => {
    window.clearTimeout(userSearchTimeout);

    userSearchTimeout = window.setTimeout(() => {
      runUserSearch();
    }, 200);
  });
}

if (userSearchForm) {
  userSearchForm.addEventListener('submit', (event) => {
    event.preventDefault();
    window.clearTimeout(userSearchTimeout);
    runUserSearch();
  });
}

const rentalsToggle = document.querySelector('[data-rentals-toggle]');
const rentalsList = document.querySelector('[data-rentals-list]');

if (rentalsToggle && rentalsList) {
  rentalsToggle.addEventListener('click', () => {
    const shouldOpen = rentalsList.hidden;
    rentalsList.hidden = !shouldOpen;
    rentalsToggle.setAttribute('aria-expanded', shouldOpen ? 'true' : 'false');
    rentalsToggle.textContent = rentalsToggle.textContent.replace(shouldOpen ? 'Ver' : 'Esconder', shouldOpen ? 'Esconder' : 'Ver');
  });
}
