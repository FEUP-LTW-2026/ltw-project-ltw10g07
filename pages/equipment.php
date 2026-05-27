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
$canRentEquipment = in_array(getUserRole(), ['member', 'trainer'], true);
$isAdmin = getUserRole() === 'admin';
$equipmentRentals = [];

if ($isAdmin) {
  $stmt = $db->query('
    SELECT
      equipment_rentals.equipment_id,
      equipment_rentals.start_time,
      equipment_rentals.end_time,
      user.name AS user_name
    FROM equipment_rentals
    JOIN user ON user.id = equipment_rentals.user_id
    WHERE equipment_rentals.status = "active"
      AND DATE(equipment_rentals.start_time) = DATE("now", "localtime")
    ORDER BY equipment_rentals.start_time ASC, user.name ASC
  ');

  foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $rental) {
    $equipmentRentals[(int) $rental['equipment_id']][] = $rental;
  }
}

$equipment = array_values(array_filter(Equipment::getAll($db), static function (array $item): bool {
  $availableUnits = max(0, (int) ($item['total_quantity'] ?? 1) - (int) ($item['maintenance_quantity'] ?? 0));

  return $item['status'] === 'available' && $availableUnits > 0;
}));

$totalAvailable = array_sum(array_map(
  static fn (array $item): int => max(0, (int) ($item['total_quantity'] ?? 1) - (int) ($item['maintenance_quantity'] ?? 0)),
  $equipment
));
$rentableCount = count(array_filter(
  $equipment,
  static fn (array $item): bool => (int) ($item['rental_available'] ?? 0) === 1
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
                $totalQuantity = (int) ($item['total_quantity'] ?? 1);
                $maintenanceQuantity = (int) ($item['maintenance_quantity'] ?? 0);
                $availableUnits = max(0, $totalQuantity - $maintenanceQuantity);
                $isRentable = (int) ($item['rental_available'] ?? 0) === 1;
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
                    <dd><?= $totalQuantity ?></dd>
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

                <?php if ($isRentable && $canRentEquipment): ?>
                  <button type="button" class="btn btn-primary btn-full" data-rent-equipment data-equipment-id="<?= (int) $item['id'] ?>" data-equipment-name="<?= htmlspecialchars($item['name']) ?>">Alugar hoje</button>
                <?php elseif ($isRentable && $isAdmin): ?>
                  <?php $rentals = $equipmentRentals[(int) $item['id']] ?? []; ?>
                  <button
                    type="button"
                    class="btn btn-outline btn-full"
                    data-equipment-rentals-toggle
                    aria-expanded="false"
                  >
                    Ver alugueres (<?= count($rentals) ?>)
                  </button>
                  <div class="equipment-admin-rentals" hidden>
                    <h4>Alugueres de hoje</h4>
                    <?php if ($rentals !== []): ?>
                      <ul>
                        <?php foreach ($rentals as $rental): ?>
                          <li>
                            <strong><?= htmlspecialchars($rental['user_name']) ?></strong>
                            <span><?= htmlspecialchars(substr($rental['start_time'], 11, 5)) ?>-<?= htmlspecialchars(substr($rental['end_time'], 11, 5)) ?></span>
                          </li>
                        <?php endforeach; ?>
                      </ul>
                    <?php else: ?>
                      <p>Sem alugueres ativos hoje.</p>
                    <?php endif; ?>
                  </div>
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

    <div class="equipment-modal" id="equipment-rental-modal" hidden>
      <div class="equipment-modal-backdrop" data-close-rental-modal></div>
      <section class="equipment-modal-panel" role="dialog" aria-modal="true" aria-labelledby="rental-modal-title">
        <button type="button" class="equipment-modal-close" data-close-rental-modal aria-label="Fechar">×</button>
        <p class="equipment-eyebrow">Aluguer para hoje</p>
        <h2 id="rental-modal-title">Escolhe uma hora</h2>
        <p class="equipment-modal-copy" id="rental-modal-equipment"></p>
        <div class="equipment-current-rental" id="equipment-current-rental" hidden>
          <p id="equipment-current-rental-text"></p>
          <button type="button" class="btn btn-outline btn-sm" id="cancel-equipment-rental">Cancelar aluguer</button>
        </div>
        <div class="equipment-slot-grid" id="equipment-slot-grid"></div>
        <p class="equipment-rental-message" id="equipment-rental-message" role="status"></p>
        <button type="button" class="btn btn-primary btn-full" id="confirm-equipment-rental" disabled>Confirmar aluguer</button>
      </section>
    </div>
  </main>

<?php require __DIR__ . '/../templates/footer.php'; ?>
