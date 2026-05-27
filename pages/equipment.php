<?php
declare(strict_types=1);

require_once __DIR__ . '/../database/connection.php';
require_once __DIR__ . '/../database/Equipment.php';
require_once __DIR__ . '/../utils/session.php';

if (!isLoggedIn()) {
  setFlashMessages('errors', ['Inicia sessão para veres os equipamentos disponíveis.']);
  header('Location: login.php');
  exit;
}

$db = getDB();
$equipment = array_values(array_filter(Equipment::getAll($db), static function (array $item): bool {
  $availableUnits = max(0, (int) ($item['quantity'] ?? 1) - (int) ($item['maintenance_quantity'] ?? 0));

  return $item['status'] === 'available' && $availableUnits > 0;
}));

$totalAvailable = array_sum(array_map(
  static fn (array $item): int => max(0, (int) ($item['quantity'] ?? 1) - (int) ($item['maintenance_quantity'] ?? 0)),
  $equipment
));
$rentableCount = count(array_filter(
  $equipment,
  static fn (array $item): bool => (int) ($item['rentable'] ?? 0) === 1
));
$categories = array_unique(array_map(static fn (array $item): string => $item['category'], $equipment));
sort($categories);

$pageTitle = 'Equipamentos - LiftToBig';
$pageStyles = ['../css/equipment.css'];
$pageScripts = ['../js/equipment.js'];
$activePage = 'equipment';

require __DIR__ . '/../templates/header.php';
?>

  <main>
    <section class="equipment-hero" aria-labelledby="equipment-heading">
      <div class="container equipment-hero-inner">
        <div>
          <p class="equipment-eyebrow">Área de equipamento</p>
          <h1 id="equipment-heading">Equipamentos disponíveis.</h1>
          <p>Consulta o material operacional do ginásio e vê rapidamente o que pode ser alugado.</p>
        </div>

        <div class="equipment-summary" aria-label="Resumo dos equipamentos disponíveis">
          <div>
            <strong><?= (int) $totalAvailable ?></strong>
            <span>Unidades disponíveis</span>
          </div>
          <div>
            <strong><?= count($categories) ?></strong>
            <span>Categorias</span>
          </div>
          <div>
            <strong><?= (int) $rentableCount ?></strong>
            <span>Tipos alugáveis</span>
          </div>
        </div>
      </div>
    </section>

    <section class="equipment-section" aria-labelledby="equipment-list-heading">
      <div class="container">
        <h2 id="equipment-list-heading" class="visually-hidden">Lista de equipamentos</h2>

        <?php if ($equipment !== []): ?>
          <form class="equipment-filters" id="equipment-filters" role="search">
            <div class="equipment-search">
              <label for="equipment-search">Pesquisar equipamento</label>
              <input type="search" id="equipment-search" name="q" placeholder="Pesquisar por passadeira, luvas, yoga..." autocomplete="off" />
            </div>

            <div class="equipment-filter-row">
              <div class="equipment-filter">
                <label for="equipment-category">Tipo</label>
                <select id="equipment-category" name="category">
                  <option value="all">Todos os tipos</option>
                  <?php foreach ($categories as $category): ?>
                    <option value="<?= htmlspecialchars($category) ?>"><?= htmlspecialchars($category) ?></option>
                  <?php endforeach; ?>
                </select>
              </div>

              <div class="equipment-filter">
                <label for="equipment-rentable">Aluguer</label>
                <select id="equipment-rentable" name="rentable">
                  <option value="all">Todos</option>
                  <option value="yes">Alugáveis</option>
                  <option value="no">Não alugáveis</option>
                </select>
              </div>
            </div>
          </form>

          <ul class="equipment-grid" id="equipment-grid">
            <?php foreach ($equipment as $item): ?>
              <?php
                $quantity = (int) ($item['quantity'] ?? 1);
                $maintenanceQuantity = (int) ($item['maintenance_quantity'] ?? 0);
                $availableUnits = max(0, $quantity - $maintenanceQuantity);
                $isRentable = (int) ($item['rentable'] ?? 0) === 1;
              ?>
              <li class="equipment-card">
                <div class="equipment-card-header">
                  <div>
                    <p><?= htmlspecialchars($item['category']) ?></p>
                    <h3><?= htmlspecialchars($item['name']) ?></h3>
                  </div>
                  <strong><?= $availableUnits ?></strong>
                </div>

                <dl class="equipment-details">
                  <div>
                    <dt>Total</dt>
                    <dd><?= $quantity ?></dd>
                  </div>
                  <div>
                    <dt>Em manutenção</dt>
                    <dd><?= $maintenanceQuantity ?></dd>
                  </div>
                  <div>
                    <dt>Localização</dt>
                    <dd><?= htmlspecialchars($item['location'] ?: 'Por definir') ?></dd>
                  </div>
                  <div>
                    <dt>Aluguer</dt>
                    <dd><?= $isRentable ? 'Disponível' : 'Indisponível' ?></dd>
                  </div>
                </dl>

                <?php if ($isRentable): ?>
                  <button type="button" class="btn btn-primary btn-full" disabled>Aluguer em breve</button>
                <?php else: ?>
                  <button type="button" class="btn btn-outline btn-full" disabled>Apenas uso no ginásio</button>
                <?php endif; ?>
              </li>
            <?php endforeach; ?>
          </ul>

          <div class="equipment-empty" id="equipment-filter-empty" hidden>
            <h2>Nenhum equipamento encontrado.</h2>
            <p>Tenta pesquisar por outro nome, tipo ou estado de aluguer.</p>
          </div>
        <?php else: ?>
          <div class="equipment-empty">
            <h2>Sem equipamentos disponíveis.</h2>
            <p>Quando a equipa atualizar o inventário, os equipamentos operacionais aparecem aqui.</p>
          </div>
        <?php endif; ?>
      </div>
    </section>
  </main>

<?php require __DIR__ . '/../templates/footer.php'; ?>
