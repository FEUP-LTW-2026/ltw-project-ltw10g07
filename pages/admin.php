<?php
require_once __DIR__ . '/../database/connection.php';
require_once __DIR__ . '/../database/User.php';
require_once __DIR__ . '/../database/ClassCatalog.php';
require_once __DIR__ . '/../database/Trainer.php';
require_once __DIR__ . '/../database/Equipment.php';
require_once __DIR__ . '/../utils/session.php';
require_once __DIR__ . '/../templates/flash_messages.php';

if (!isLoggedIn() || getUserRole() !== 'admin') {
  header('Location: login.php');
  exit;
}

$db = getDB();
$users = User::getAll($db);
$classes = ClassCatalog::getAll($db);
$trainers = Trainer::getAll($db);
$equipment = Equipment::getAll($db);
$equipmentRentals = $db->query('
  SELECT
    equipment_rentals.id,
    equipment_rentals.start_time,
    equipment_rentals.end_time,
    equipment_rentals.status,
    equipment.name AS equipment_name,
    user.name AS user_name,
    user.role AS user_role
  FROM equipment_rentals
  JOIN equipment ON equipment.id = equipment_rentals.equipment_id
  JOIN user ON user.id = equipment_rentals.user_id
  WHERE DATE(equipment_rentals.start_time) = DATE("now", "localtime")
    AND equipment_rentals.status = "active"
  ORDER BY
    equipment_rentals.start_time ASC,
    equipment.name ASC
')->fetchAll(PDO::FETCH_ASSOC);
$errors = getFlashMessages('errors');
$success = getFlashMessages('success');

$statusLabels = [
  'available' => 'Disponível',
  'in_use' => 'Em uso',
  'maintenance' => 'Manutenção',
];

$roleLabels = [
  'member' => 'Membro',
  'trainer' => 'Treinador',
  'admin' => 'Admin',
];

$pageTitle = 'Admin - LiftToBig';
$pageStyles = ['../css/admin.css'];
$pageScripts = ['../javascript/admin.js'];
$activePage = 'admin';
require __DIR__ . '/../templates/header.php';
?>

  <main class="admin">
    <section class="admin-hero">
      <div class="container admin-hero-inner">
        <div>
          <p class="admin-eyebrow">Painel administrativo</p>
          <h1>Operação do sistema</h1>
          <p>Gere utilizadores, catálogo de aulas e equipamento da área principal.</p>
        </div>
        <div class="admin-stats" aria-label="Resumo do sistema">
          <div><strong><?= count($users) ?></strong><span>Utilizadores</span></div>
          <div><strong><?= count($classes) ?></strong><span>Aulas</span></div>
          <div><strong><?= count($equipment) ?></strong><span>Equipamentos</span></div>
        </div>
      </div>
    </section>

    <section class="admin-content">
      <div class="container">
        <?php drawFlashMessages($errors, $success); ?>

        <nav class="admin-tabs" aria-label="Secções de administração">
          <button type="button" class="active" data-admin-tab="users">Utilizadores</button>
          <button type="button" data-admin-tab="classes">Aulas</button>
          <button type="button" data-admin-tab="equipment">Equipamento</button>
        </nav>

        <section id="users" class="admin-section active" data-admin-panel="users">
          <div class="admin-section-header">
            <div>
              <h2>Utilizadores</h2>
              <p>Cria, atualiza, desativa e promove contas a admin.</p>
            </div>
            <form class="admin-search" role="search" data-user-search-form>
              <label for="user-search">Pesquisar utilizador</label>
              <div class="admin-search-row">
                <input type="search" id="user-search" placeholder="Nome, username, email ou tipo" />
                <button type="submit" class="btn btn-outline btn-sm">Pesquisar</button>
              </div>
            </form>
          </div>

          <div class="admin-grid">
            <form action="../actions/action_admin_user.php" method="POST" class="admin-card admin-form">
              <?= csrfInput() ?>
              <input type="hidden" name="action" value="create" />
              <h3>Criar utilizador</h3>
              <div class="form-row">
                <div class="form-group">
                  <label for="new-user-name">Nome</label>
                  <input type="text" id="new-user-name" name="name" required />
                </div>
                <div class="form-group">
                  <label for="new-user-username">Username</label>
                  <input type="text" id="new-user-username" name="username" required />
                </div>
              </div>
              <div class="form-row">
                <div class="form-group">
                  <label for="new-user-email">Email</label>
                  <input type="email" id="new-user-email" name="email" required />
                </div>
                <div class="form-group">
                  <label for="new-user-password">Palavra-passe</label>
                  <input type="password" id="new-user-password" name="password" minlength="8" required />
                </div>
              </div>
              <div class="form-row">
                <div class="form-group">
                  <label for="new-user-role">Tipo</label>
                  <select id="new-user-role" name="role">
                    <?php foreach ($roleLabels as $value => $label): ?>
                      <option value="<?= htmlspecialchars($value) ?>"><?= htmlspecialchars($label) ?></option>
                    <?php endforeach; ?>
                  </select>
                </div>
                <label class="admin-check">
                  <input type="checkbox" name="is_active" checked />
                  Conta ativa
                </label>
              </div>
              <button type="submit" class="btn btn-primary">Criar conta</button>
            </form>

            <?php foreach ($users as $user): ?>
              <form
                action="../actions/action_admin_user.php"
                method="POST"
                class="admin-card admin-form"
                data-user-id="<?= (int) $user['id'] ?>"
                data-user-card="<?= htmlspecialchars(strtolower($user['name'] . ' ' . $user['username'] . ' ' . $user['email'] . ' ' . ($roleLabels[$user['role']] ?? $user['role']))) ?>"
              >
                <?= csrfInput() ?>
                <input type="hidden" name="action" value="update" />
                <input type="hidden" name="user_id" value="<?= (int) $user['id'] ?>" />
                <div class="admin-card-title">
                  <h3><?= htmlspecialchars($user['name']) ?></h3>
                  <span class="admin-pill"><?= htmlspecialchars($roleLabels[$user['role']] ?? $user['role']) ?></span>
                </div>
                <div class="form-row">
                  <div class="form-group">
                    <label for="user-name-<?= (int) $user['id'] ?>">Nome</label>
                    <input type="text" id="user-name-<?= (int) $user['id'] ?>" name="name" value="<?= htmlspecialchars($user['name']) ?>" required />
                  </div>
                  <div class="form-group">
                    <label for="user-username-<?= (int) $user['id'] ?>">Username</label>
                    <input type="text" id="user-username-<?= (int) $user['id'] ?>" name="username" value="<?= htmlspecialchars($user['username']) ?>" required />
                  </div>
                </div>
                <div class="form-row">
                  <div class="form-group">
                    <label for="user-email-<?= (int) $user['id'] ?>">Email</label>
                    <input type="email" id="user-email-<?= (int) $user['id'] ?>" name="email" value="<?= htmlspecialchars($user['email']) ?>" required />
                  </div>
                  <div class="form-group">
                    <label for="user-password-<?= (int) $user['id'] ?>">Nova palavra-passe</label>
                    <input type="password" id="user-password-<?= (int) $user['id'] ?>" name="password" placeholder="Manter atual" />
                  </div>
                </div>
                <div class="form-row">
                  <div class="form-group">
                    <label for="user-role-<?= (int) $user['id'] ?>">Tipo</label>
                    <select id="user-role-<?= (int) $user['id'] ?>" name="role">
                      <?php foreach ($roleLabels as $value => $label): ?>
                        <option value="<?= htmlspecialchars($value) ?>" <?= $user['role'] === $value ? 'selected' : '' ?>><?= htmlspecialchars($label) ?></option>
                      <?php endforeach; ?>
                    </select>
                  </div>
                  <label class="admin-check">
                    <input type="checkbox" name="is_active" <?= (int) $user['is_active'] === 1 ? 'checked' : '' ?> />
                    Conta ativa
                  </label>
                </div>
                <div class="admin-actions">
                  <button type="submit" class="btn btn-outline">Guardar utilizador</button>
                  <?php if ((int) $user['id'] !== (int) getUserId()): ?>
                    <button
                      type="submit"
                      class="btn btn-ghost"
                      name="action"
                      value="delete"
                      formnovalidate
                      onclick="return confirm('Tens a certeza que queres remover esta conta? Esta ação não pode ser desfeita.');"
                    >
                      Remover conta
                    </button>
                  <?php endif; ?>
                </div>
              </form>
            <?php endforeach; ?>
            <p class="admin-empty" data-user-empty hidden>Nenhum utilizador encontrado.</p>
          </div>
        </section>

        <section id="classes" class="admin-section" data-admin-panel="classes" hidden>
          <div class="admin-section-header">
            <div>
              <h2>Catálogo de aulas</h2>
              <p>Cria aulas e edita os detalhes gerais do catálogo.</p>
            </div>
          </div>

          <div class="admin-grid">
            <form action="../actions/action_admin_class.php" method="POST" class="admin-card admin-form" enctype="multipart/form-data">
              <?= csrfInput() ?>
              <input type="hidden" name="action" value="create" />
              <h3>Criar aula</h3>
              <div class="form-row">
                <div class="form-group">
                  <label for="new-class-name">Nome</label>
                  <input type="text" id="new-class-name" name="name" required />
                </div>
                <div class="form-group">
                  <label for="new-class-type">Tipo</label>
                  <input type="text" id="new-class-type" name="type" required />
                </div>
                <div class="form-group">
                  <label for="new-class-intensity">Intensidade</label>
                  <select id="new-class-intensity" name="intensity" required>
                    <option value="Baixa">Baixa</option>
                    <option value="Média">Média</option>
                    <option value="Alta">Alta</option>
                  </select>
                </div>
              </div>
              <div class="form-row">
                <div class="form-group">
                  <label for="new-class-trainer">Treinador</label>
                  <select id="new-class-trainer" name="trainer_id" required>
                    <option value="">Selecionar treinador</option>
                    <?php foreach ($trainers as $trainer): ?>
                      <option value="<?= (int) $trainer['id'] ?>"><?= htmlspecialchars($trainer['name']) ?></option>
                    <?php endforeach; ?>
                  </select>
                </div>
                <div class="form-group">
                  <label for="new-class-capacity">Capacidade</label>
                  <input type="number" id="new-class-capacity" name="capacity" min="1" max="80" required />
                </div>
              </div>
              <div class="form-group">
                <span class="form-label">Imagem</span>
                <label for="new-class-image" class="btn btn-outline btn-sm admin-file-button">Escolher imagem</label>
                <input type="file" id="new-class-image" name="image_file" class="admin-file-input" accept="image/jpeg,image/png,image/webp" />
                <span class="admin-file-name" data-file-name>Nenhuma imagem escolhida</span>
              </div>
              <div class="form-group">
                <label for="new-class-description">Descrição</label>
                <textarea id="new-class-description" name="description"></textarea>
              </div>
              <button type="submit" class="btn btn-primary">Criar aula</button>
            </form>

            <?php foreach ($classes as $class): ?>
              <form action="../actions/action_admin_class.php" method="POST" class="admin-card admin-form" enctype="multipart/form-data">
                <?= csrfInput() ?>
                <input type="hidden" name="action" value="update" />
                <input type="hidden" name="class_id" value="<?= (int) $class['id'] ?>" />
                <div class="admin-card-title">
                  <h3><?= htmlspecialchars($class['name']) ?></h3>
                  <span class="admin-pill"><?= htmlspecialchars($class['type']) ?></span>
                </div>
                <div class="form-row">
                  <div class="form-group">
                    <label for="class-name-<?= (int) $class['id'] ?>">Nome</label>
                    <input type="text" id="class-name-<?= (int) $class['id'] ?>" name="name" value="<?= htmlspecialchars($class['name']) ?>" required />
                  </div>
                  <div class="form-group">
                    <label for="class-type-<?= (int) $class['id'] ?>">Tipo</label>
                    <input type="text" id="class-type-<?= (int) $class['id'] ?>" name="type" value="<?= htmlspecialchars($class['type']) ?>" required />
                  </div>
                  <div class="form-group">
                    <label for="class-intensity-<?= (int) $class['id'] ?>">Intensidade</label>
                    <select id="class-intensity-<?= (int) $class['id'] ?>" name="intensity" required>
                      <?php foreach (['Baixa', 'Média', 'Alta'] as $intensity): ?>
                        <option value="<?= htmlspecialchars($intensity) ?>" <?= $class['intensity'] === $intensity ? 'selected' : '' ?>><?= htmlspecialchars($intensity) ?></option>
                      <?php endforeach; ?>
                    </select>
                  </div>
                </div>
                <div class="form-row">
                  <div class="form-group">
                    <label for="class-trainer-<?= (int) $class['id'] ?>">Treinador</label>
                    <select id="class-trainer-<?= (int) $class['id'] ?>" name="trainer_id" required>
                      <?php foreach ($trainers as $trainer): ?>
                        <option value="<?= (int) $trainer['id'] ?>" <?= (int) $class['trainer_id'] === (int) $trainer['id'] ? 'selected' : '' ?>>
                          <?= htmlspecialchars($trainer['name']) ?>
                        </option>
                      <?php endforeach; ?>
                    </select>
                  </div>
                  <div class="form-group">
                    <label for="class-capacity-<?= (int) $class['id'] ?>">Capacidade</label>
                    <input type="number" id="class-capacity-<?= (int) $class['id'] ?>" name="capacity" min="1" max="80" value="<?= (int) $class['capacity'] ?>" required />
                  </div>
                </div>
                <div class="form-group">
                  <span class="form-label">Imagem</span>
                  <label for="class-image-<?= (int) $class['id'] ?>" class="btn btn-outline btn-sm admin-file-button">Escolher imagem</label>
                  <input type="file" id="class-image-<?= (int) $class['id'] ?>" name="image_file" class="admin-file-input" accept="image/jpeg,image/png,image/webp" />
                  <span class="admin-file-name" data-file-name>
                    <?= !empty($class['image']) ? 'Imagem atual guardada' : 'Nenhuma imagem escolhida' ?>
                  </span>
                </div>
                <div class="form-group">
                  <label for="class-description-<?= (int) $class['id'] ?>">Descrição</label>
                  <textarea id="class-description-<?= (int) $class['id'] ?>" name="description"><?= htmlspecialchars($class['description'] ?? '') ?></textarea>
                </div>
                <div class="admin-actions">
                  <button type="submit" class="btn btn-outline">Guardar aula</button>
                  <button type="submit" class="btn btn-ghost" name="action" value="delete">Remover</button>
                </div>
              </form>
            <?php endforeach; ?>
          </div>
        </section>

        <section id="equipment" class="admin-section" data-admin-panel="equipment" hidden>
          <div class="admin-section-header">
            <div>
              <h2>Equipamento</h2>
              <p>Controla os itens da área principal, disponibilidade e alugueres de hoje.</p>
            </div>
          </div>

          <div class="admin-card admin-rentals-card">
            <div class="admin-card-title">
              <h3>Alugueres de hoje</h3>
              <button type="button" class="btn btn-outline btn-sm" data-rentals-toggle aria-expanded="false" aria-controls="admin-rentals-list">
                Ver alugueres (<?= count($equipmentRentals) ?>)
              </button>
            </div>

            <div id="admin-rentals-list" data-rentals-list hidden>
              <?php if ($equipmentRentals !== []): ?>
                <div class="admin-rentals-list">
                <?php foreach ($equipmentRentals as $rental): ?>
                  <div class="admin-rental-row">
                    <div>
                      <strong><?= htmlspecialchars($rental['equipment_name']) ?></strong>
                      <span><?= htmlspecialchars($rental['user_name']) ?> · <?= htmlspecialchars($roleLabels[$rental['user_role']] ?? $rental['user_role']) ?></span>
                    </div>
                    <div>
                      <strong><?= htmlspecialchars(substr($rental['start_time'], 11, 5)) ?>-<?= htmlspecialchars(substr($rental['end_time'], 11, 5)) ?></strong>
                      <span><?= htmlspecialchars($rental['status']) ?></span>
                    </div>
                  </div>
                <?php endforeach; ?>
                </div>
              <?php else: ?>
                <p class="admin-empty">Ainda não existem alugueres para hoje.</p>
              <?php endif; ?>
            </div>
          </div>

          <div class="admin-grid">
            <form action="../actions/action_admin_equipment.php" method="POST" class="admin-card admin-form">
              <?= csrfInput() ?>
              <input type="hidden" name="action" value="create" />
              <h3>Adicionar equipamento</h3>
              <div class="form-row">
                <div class="form-group">
                  <label for="new-equipment-name">Nome</label>
                  <input type="text" id="new-equipment-name" name="name" required />
                </div>
                <div class="form-group">
                  <label for="new-equipment-category">Categoria</label>
                  <input type="text" id="new-equipment-category" name="category" required />
                </div>
              </div>
              <div class="form-row">
                <div class="form-group">
                  <label for="new-equipment-status">Estado</label>
                  <select id="new-equipment-status" name="status" required>
                    <?php foreach ($statusLabels as $value => $label): ?>
                      <option value="<?= htmlspecialchars($value) ?>"><?= htmlspecialchars($label) ?></option>
                    <?php endforeach; ?>
                  </select>
                </div>
                <div class="form-group">
                  <label for="new-equipment-location">Localização</label>
                  <input type="text" id="new-equipment-location" name="location" />
                </div>
              </div>
              <div class="form-row">
                <div class="form-group">
                  <label for="new-equipment-quantity">Quantidade total</label>
                  <input type="number" id="new-equipment-quantity" name="total_quantity" min="0" value="1" required />
                </div>
                <div class="form-group">
                  <label for="new-equipment-maintenance">Em manutenção</label>
                  <input type="number" id="new-equipment-maintenance" name="maintenance_quantity" min="0" value="0" required />
                </div>
                <label class="admin-check">
                  <input type="checkbox" name="rental_available" />
                  Alugável
                </label>
              </div>
              <button type="submit" class="btn btn-primary">Adicionar equipamento</button>
            </form>

            <?php foreach ($equipment as $item): ?>
              <form action="../actions/action_admin_equipment.php" method="POST" class="admin-card admin-form">
                <?= csrfInput() ?>
                <input type="hidden" name="action" value="update" />
                <input type="hidden" name="equipment_id" value="<?= (int) $item['id'] ?>" />
                <div class="admin-card-title">
                  <h3><?= htmlspecialchars($item['name']) ?></h3>
                </div>
                <div class="form-row">
                  <div class="form-group">
                    <label for="equipment-name-<?= (int) $item['id'] ?>">Nome</label>
                    <input type="text" id="equipment-name-<?= (int) $item['id'] ?>" name="name" value="<?= htmlspecialchars($item['name']) ?>" required />
                  </div>
                  <div class="form-group">
                    <label for="equipment-category-<?= (int) $item['id'] ?>">Categoria</label>
                    <input type="text" id="equipment-category-<?= (int) $item['id'] ?>" name="category" value="<?= htmlspecialchars($item['category']) ?>" required />
                  </div>
                </div>
                <div class="form-row">
                  <div class="form-group">
                    <label for="equipment-status-<?= (int) $item['id'] ?>">Estado</label>
                    <select id="equipment-status-<?= (int) $item['id'] ?>" name="status" required>
                      <?php foreach ($statusLabels as $value => $label): ?>
                        <option value="<?= htmlspecialchars($value) ?>" <?= $item['status'] === $value ? 'selected' : '' ?>><?= htmlspecialchars($label) ?></option>
                      <?php endforeach; ?>
                    </select>
                  </div>
                  <div class="form-group">
                    <label for="equipment-location-<?= (int) $item['id'] ?>">Localização</label>
                    <input type="text" id="equipment-location-<?= (int) $item['id'] ?>" name="location" value="<?= htmlspecialchars($item['location'] ?? '') ?>" />
                  </div>
                </div>
                <div class="form-row">
                  <div class="form-group">
                    <label for="equipment-quantity-<?= (int) $item['id'] ?>">Quantidade total</label>
                    <input type="number" id="equipment-quantity-<?= (int) $item['id'] ?>" name="total_quantity" min="0" value="<?= (int) ($item['total_quantity'] ?? 1) ?>" required />
                  </div>
                  <div class="form-group">
                    <label for="equipment-maintenance-<?= (int) $item['id'] ?>">Em manutenção</label>
                    <input type="number" id="equipment-maintenance-<?= (int) $item['id'] ?>" name="maintenance_quantity" min="0" value="<?= (int) ($item['maintenance_quantity'] ?? 0) ?>" required />
                  </div>
                  <label class="admin-check">
                    <input type="checkbox" name="rental_available" <?= (int) ($item['rental_available'] ?? 0) === 1 ? 'checked' : '' ?> />
                    Alugável
                  </label>
                </div>
                <div class="admin-actions">
                  <button type="submit" class="btn btn-outline">Guardar equipamento</button>
                  <button type="submit" class="btn btn-ghost" name="action" value="delete">Remover</button>
                </div>
              </form>
            <?php endforeach; ?>
          </div>
        </section>
      </div>
    </section>
  </main>

<?php require __DIR__ . '/../templates/footer.php'; ?>
