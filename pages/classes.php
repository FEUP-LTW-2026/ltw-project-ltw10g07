<?php
declare(strict_types=1);

require_once __DIR__ . '/../utils/session.php';
require_once __DIR__ . '/../database/connection.php';

$db = getDB();

// Apenas ler a lista de treinadores para o dropdown
$trainerStmt = $db->query('SELECT trainer.id, user.name FROM trainer JOIN user ON user.id = trainer.user_id ORDER BY user.name ASC');
$allTrainers = $trainerStmt->fetchAll(PDO::FETCH_ASSOC);

$pageTitle = 'Horário de Aulas - LiftToBig';
$pageStyles = ['../css/classes.css'];
$activePage = 'classes';

require __DIR__ . '/../templates/header.php';
?>

  <main>
    <section class="classes-hero">
      <div class="container">
        <div class="hero-eyebrow">★ O TEU HORÁRIO</div>
        <h1 class="hero-title">Reserva a tua <br><span>Sessão</span>.</h1>
        <p class="hero-subtitle">Encontra o dia e a hora perfeitos para o teu treino.</p>

        <div class="filters-wrapper">
          <form id="filters-form">
            <div class="hero-search" role="search">
              <label for="hero-search-input">Pesquisar aulas</label>
              <span aria-hidden="true" style="color:var(--color-text-muted);font-size:.95rem">&#9906;</span>
              <input type="search" id="hero-search-input" name="q" placeholder="Pesquisar por Yoga, HIIT..." autocomplete="off"/>
            </div>
            
            <div class="filters-row" style="margin-top: 1rem;">
              
              <div class="filter-item">
                <label for="filter-date">Data</label>
                <input type="date" id="filter-date" name="date" style="padding: 0.5rem; border: 1px solid #cbd5e1; border-radius: 4px; width: 100%;">
              </div>

              <div class="filter-item">
                <label for="filter-time">Período</label>
                <select id="filter-time" name="time">
                  <option value="all">Qualquer Hora</option>
                  <option value="morning">Manhã (&lt; 12:00)</option>
                  <option value="afternoon">Tarde (12:00 - 18:00)</option>
                  <option value="evening">Noite (> 18:00)</option>
                </select>
              </div>

              <div class="filter-item">
                <label for="filter-intensity">Intensidade</label>
                <select id="filter-intensity" name="intensity">
                  <option value="Todas as Intensidades">Todas as Intensidades</option>
                  <option value="Alta">Alta</option>
                  <option value="Média">Média</option>
                  <option value="Baixa">Baixa</option>
                </select>
              </div>

              <div class="filter-item">
                <label for="filter-trainer">Treinador</label>
                <select id="filter-trainer" name="trainer_id">
                  <option value="0">Todos os Treinadores</option>
                  <?php foreach ($allTrainers as $t): ?>
                    <option value="<?= $t['id'] ?>"><?= htmlspecialchars($t['name']) ?></option>
                  <?php endforeach; ?>
                </select>
              </div>

            </div>
          </form>
        </div>
      </div>
    </section>

    <section class="classes-grid-section">
      <div class="container">
        <div id="classes-container">
            <div style="text-align: center; padding: 3rem 0; color: var(--color-text-muted);">
              A carregar o horário...
            </div>
        </div>
      </div>
    </section>
  </main>

<script src="../js/classes.js"></script>
<?php require __DIR__ . '/../templates/footer.php'; ?>