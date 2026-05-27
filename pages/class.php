<?php
require_once __DIR__ . '/../database/connection.php';
require_once __DIR__ . '/../database/ClassCatalog.php';

$db = getDB();
$classId = isset($_GET['id']) ? (int) $_GET['id'] : 0;
$class = $classId > 0 ? ClassCatalog::findById($db, $classId) : null;

if ($class === null) {
  header('Location: classes.php');
  exit;
}

$image = $class['image'] ?: '../img/hiit.jpg';
$trainerName = $class['trainer_name'] ?: 'Sem treinador atribuído';
$trainerPhoto = $class['trainer_photo'] ?: '../img/logo.jpg';
$intensityBadge = $class['intensity'] === 'Baixa' ? 'badge-green' : 'badge-amber';

$pageTitle = $class['name'] . ' - LiftToBig';
$pageStyles = ['../css/class.css'];
$activePage = 'classes';
require __DIR__ . '/../templates/header.php';
?>

  <main>
    <section class="class-hero" aria-labelledby="class-name-heading">
      <div class="container class-hero-inner">

        <div class="class-hero-image-wrap">
          <img src="<?= htmlspecialchars($image) ?>" alt="<?= htmlspecialchars($class['name']) ?>" class="class-hero-img" />
          <div class="class-hero-badges">
            <span class="badge badge-blue"><?= htmlspecialchars($class['type']) ?></span>
            <span class="badge <?= $intensityBadge ?>"><?= htmlspecialchars($class['intensity']) ?></span>
          </div>
        </div>

        <div class="class-hero-info">
          <a href="classes.php" class="back-link">&#8592; Todas as Aulas</a>

          <h1 id="class-name-heading"><?= htmlspecialchars($class['name']) ?></h1>

          <div class="class-hero-meta">
            <div class="class-meta-item">
              <span class="meta-icon" aria-hidden="true">&#9202;</span>
              <span><?= (int) $class['duration'] ?> min</span>
            </div>
            <div class="class-meta-item">
              <span class="meta-icon" aria-hidden="true">&#128293;</span>
              <span>Intensidade: <?= htmlspecialchars($class['intensity']) ?></span>
            </div>
            <div class="class-meta-item">
              <span class="meta-icon" aria-hidden="true">&#128101;</span>
              <span><?= (int) $class['capacity'] ?> vagas</span>
            </div>
          </div>

          <p class="class-hero-description"><?= htmlspecialchars($class['description'] ?: 'Descrição por definir.') ?></p>

          <a href="register.php" class="btn btn-primary btn-lg">Inscrever-me Nesta Aula</a>
        </div>

      </div>
    </section>

    <section class="class-details-section">
      <div class="container class-details-grid">

        <div class="class-detail-block">
          <h2>O que podes esperar</h2>
          <p><?= htmlspecialchars($class['description'] ?: 'Esta aula ainda não tem detalhes adicionais.') ?></p>

          <div class="requirements-box">
            <h3>&#8505; Requisitos</h3>
            <p>Chega alguns minutos antes, traz água e segue as indicações do treinador.</p>
          </div>
        </div>

        <div class="class-detail-block">
          <h2>O Teu Treinador</h2>
          <div class="class-trainer-card">
            <img src="<?= htmlspecialchars($trainerPhoto) ?>" alt="<?= htmlspecialchars($trainerName) ?>" class="avatar" />
            <div>
              <h3><?= htmlspecialchars($trainerName) ?></h3>
              <?php if (!empty($class['trainer_id'])): ?>
                <a href="trainer.php?id=<?= (int) $class['trainer_id'] ?>" class="btn btn-outline btn-sm" style="margin-top:.5rem">Ver Perfil</a>
              <?php endif; ?>
            </div>
          </div>
        </div>

        <div class="class-detail-block">
          <h2>Informação da aula</h2>
          <ul class="next-sessions-list">
            <li class="next-session-item">
              <div class="session-datetime">
                <span class="session-date"><?= htmlspecialchars($class['type']) ?></span>
                <span class="session-time"><?= (int) $class['duration'] ?> minutos</span>
              </div>
              <div class="session-spots">
                <span class="badge badge-green"><?= (int) $class['capacity'] ?> vagas</span>
              </div>
            </li>
          </ul>
        </div>

      </div>
    </section>
  </main>

<?php require __DIR__ . '/../templates/footer.php'; ?>
