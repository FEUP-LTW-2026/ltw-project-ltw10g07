<?php
declare(strict_types=1);

require_once __DIR__ . '/../utils/session.php';
require_once __DIR__ . '/../database/connection.php';

$db = getDB();

// Apenas para o carregamento INICIAL da página
$search    = trim($_GET['q'] ?? '');
$intensity = trim($_GET['intensity'] ?? 'Todas as Intensidades');
$trainerId = isset($_GET['trainer_id']) ? (int)$_GET['trainer_id'] : 0;
$sortBy    = trim($_GET['sort'] ?? 'next');

// Lista de treinadores para o menu Dropdown
$trainerStmt = $db->query('SELECT trainer.id, user.name FROM trainer JOIN user ON user.id = trainer.user_id ORDER BY user.name ASC');
$allTrainers = $trainerStmt->fetchAll(PDO::FETCH_ASSOC);

// Carregamento inicial via PHP (para que não fique vazio quando entras na página a 1ª vez)
$query = '
    SELECT 
        class.id, class.name, class.type, class.intensity, class.duration, class.image,
        user.name AS trainer_name,
        (SELECT COUNT(*) FROM enrollment WHERE session_id = (
            SELECT id FROM class_session WHERE class_id = class.id ORDER BY session_date ASC, start_time ASC LIMIT 1
        ) AND status = "active") AS active_enrollments, class.capacity
    FROM class
    LEFT JOIN trainer ON trainer.id = class.trainer_id
    LEFT JOIN user ON user.id = trainer.user_id
    WHERE 1=1
';
$params = [];

if ($search !== '') { $query .= ' AND (class.name LIKE ? OR class.type LIKE ?)'; $params[] = "%$search%"; $params[] = "%$search%"; }
if ($intensity !== 'Todas as Intensidades') { $query .= ' AND class.intensity = ?'; $params[] = $intensity; }
if ($trainerId > 0) { $query .= ' AND class.trainer_id = ?'; $params[] = $trainerId; }
if ($sortBy === 'spots') { $query .= ' ORDER BY (class.capacity - active_enrollments) DESC'; } else { $query .= ' ORDER BY class.id ASC'; }

$stmt = $db->prepare($query);
$stmt->execute($params);
$displayedClasses = $stmt->fetchAll(PDO::FETCH_ASSOC);

$pageTitle = 'Aulas - LiftToBig';
$pageStyles = ['../css/classes.css'];
$activePage = 'classes';

require __DIR__ . '/../templates/header.php';
?>

  <main>
    <section class="classes-hero">
      <div class="container">
        <div class="hero-eyebrow">★ EXPLORA O NOSSO CATÁLOGO</div>
        <h1 class="hero-title">Encontra o Teu <br><span>Ritmo</span>.</h1>
        <p class="hero-subtitle">Explora as nossas aulas, reserva com treinadores e atinge a tua melhor performance.</p>

        <div class="filters-wrapper">
          <form method="GET" action="classes.php">
            <div class="hero-search" role="search">
              <label for="hero-search-input">Pesquisar aulas ou treinadores</label>
              <span aria-hidden="true" style="color:var(--color-text-muted);font-size:.95rem">&#9906;</span>
              <input
                type="search"
                id="hero-search-input"
                name="q"
                value="<?= htmlspecialchars($search) ?>"
                placeholder="Pesquisar aulas ou treinadores..."
                autocomplete="off"
                />
              <button type="submit" class="btn btn-primary btn-sm">Pesquisar</button>
            </div>
            
            <div class="filters-row" style="margin-top: 1rem;">
              <div class="filter-item">
                <label for="filter-intensity">Intensidade</label>
                <select id="filter-intensity" name="intensity">
                  <option <?= $intensity === 'Todas as Intensidades' ? 'selected' : '' ?>>Todas as Intensidades</option>
                  <option <?= $intensity === 'Alta' ? 'selected' : '' ?>>Alta</option>
                  <option <?= $intensity === 'Média' ? 'selected' : '' ?>>Média</option>
                  <option <?= $intensity === 'Baixa' ? 'selected' : '' ?>>Baixa</option>
                </select>
              </div>

              <div class="filter-item">
                <label for="filter-trainer">Treinador</label>
                <select id="filter-trainer" name="trainer_id">
                  <option value="0">Todos os Treinadores</option>
                  <?php foreach ($allTrainers as $t): ?>
                    <option value="<?= $t['id'] ?>" <?= $trainerId === (int)$t['id'] ? 'selected' : '' ?>>
                      <?= htmlspecialchars($t['name']) ?>
                    </option>
                  <?php endforeach; ?>
                </select>
              </div>

              <div class="filter-item">
                <label for="filter-sort">Ordenar por</label>
                <select id="filter-sort" name="sort">
                  <option value="next" <?= $sortBy === 'next' ? 'selected' : '' ?>>Próximas a começar</option>
                  <option value="spots" <?= $sortBy === 'spots' ? 'selected' : '' ?>>Mais vagas</option>
                </select>
              </div>
            </div>
          </form>
        </div>
      </div>
    </section>

    <section class="classes-grid-section">
      <div class="container">
        <h2 class="visually-hidden">Aulas disponíveis</h2>
        
        <div id="classes-container">
          <?php if ($displayedClasses !== []): ?>
            <ul class="classes-grid">
              <?php foreach ($displayedClasses as $c): 
                $i_badge = ($c['intensity'] === 'Alta') ? 'badge-amber' : (($c['intensity'] === 'Baixa') ? 'badge-green' : 'badge-blue');
              ?>
                <li class="class-card">
                  <div class="class-img-wrap">
                    <img src="<?= htmlspecialchars($c['image'] ?: '../img/hiit.jpg') ?>" alt="<?= htmlspecialchars($c['name']) ?>" />
                  </div>
                  <div class="class-card-body">
                    <div class="class-card-tags">
                      <span class="badge badge-blue"><?= htmlspecialchars($c['type']) ?></span>
                      <span class="badge <?= $i_badge ?>"><?= htmlspecialchars($c['intensity']) ?></span>
                    </div>
                    <h3><?= htmlspecialchars($c['name']) ?></h3>
                    <p class="class-time"><?= htmlspecialchars((string)$c['duration']) ?> min</p>
                    <div class="class-card-footer">
                      <span class="trainer-name"><?= htmlspecialchars($c['trainer_name'] ?? 'Ginásio') ?></span>
                      <a href="class.php?id=<?= $c['id'] ?>" class="btn btn-outline btn-sm">Ver Detalhes</a>
                    </div>
                  </div>
                </li>
              <?php endforeach; ?>
            </ul>
          <?php else: ?>
            <div style="text-align: center; padding: 3rem 0; color: var(--color-text-muted);">
              <h3>Nenhuma aula encontrada com os filtros selecionados.</h3>
              <p>Tenta mudar os critérios de pesquisa ou intensidade.</p>
            </div>
          <?php endif; ?>
        </div>

      </div>
    </section>
  </main>

<script src="../js/classes.js"></script>

<?php require __DIR__ . '/../templates/footer.php'; ?>