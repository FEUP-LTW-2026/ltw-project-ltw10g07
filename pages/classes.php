<?php
require_once __DIR__ . '/../database/connection.php';
require_once __DIR__ . '/../database/ClassCatalog.php';

$db = getDB();
$classes = ClassCatalog::getAll($db);

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
          <form class="hero-search" role="search" action="classes.php" method="GET">
            <label for="hero-search-input">Pesquisar aulas ou treinadores</label>
            <span aria-hidden="true" style="color:var(--color-text-muted);font-size:.95rem">&#9906;</span>
            <input type="search" id="hero-search-input" name="q" placeholder="Pesquisar aulas ou treinadores..." />
            <button type="submit" class="btn btn-primary btn-sm">Pesquisar</button>
          </form>
        </div>
      </div>
    </section>

    <section class="classes-grid-section">
      <div class="container">
        <h2 class="visually-hidden">Aulas disponíveis</h2>

        <?php if ($classes === []): ?>
          <div class="form-alert form-alert-error" role="status">
            <p>Ainda não existem aulas publicadas no catálogo.</p>
          </div>
        <?php else: ?>
          <ul class="classes-grid">
            <?php foreach ($classes as $class): ?>
              <?php
                $intensityBadge = $class['intensity'] === 'Baixa' ? 'badge-green' : 'badge-amber';
                $image = $class['image'] ?: '../img/hiit.jpg';
              ?>
              <li class="class-card">
                <div class="class-img-wrap">
                  <img src="<?= htmlspecialchars($image) ?>" alt="<?= htmlspecialchars($class['name']) ?>" />
                  <span class="badge badge-success card-status"><?= (int) $class['capacity'] ?> vagas</span>
                </div>
                <div class="class-card-body">
                  <div class="class-card-tags">
                    <span class="badge badge-blue"><?= htmlspecialchars($class['type']) ?></span>
                    <span class="badge <?= $intensityBadge ?>"><?= htmlspecialchars($class['intensity']) ?></span>
                  </div>
                  <h3><?= htmlspecialchars($class['name']) ?></h3>
                  <p class="class-time"><?= (int) $class['duration'] ?> min</p>
                  <div class="class-card-footer">
                    <span class="trainer-name"><?= htmlspecialchars($class['trainer_name'] ?? 'Sem treinador') ?></span>
                    <a href="class.php?id=<?= (int) $class['id'] ?>" class="btn btn-outline btn-sm">Ver Detalhes</a>
                  </div>
                </div>
              </li>
            <?php endforeach; ?>
          </ul>
        <?php endif; ?>
      </div>
    </section>
  </main>

<?php require __DIR__ . '/../templates/footer.php'; ?>
