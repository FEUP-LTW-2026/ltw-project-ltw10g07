<?php
$pageTitle = 'Treinadores - LiftToBig';
$pageStyles = ['../css/trainers.css'];
$activePage = 'trainers';
require_once __DIR__ . '/../database/connection.php';
require_once __DIR__ . '/../database/Trainer.php';
require_once __DIR__ . '/../templates/trainers.php';

$db = getDB();
$trainers = Trainer::getAll($db);

require __DIR__ . '/../templates/header.php';
?>

  <main>

    <!-- Hero -->
    <section class="trainers-hero" aria-labelledby="trainers-hero-heading">
      <div class="container">
        <h1 id="trainers-hero-heading">Os Nossos <span>Treinadores</span>.</h1>
        <p class="hero-subtitle">
          Profissionais certificados, apaixonados pelo que fazem e totalmente dedicados
          ao teu sucesso. Escolhe o teu mentor e começa hoje.
        </p>

        <!-- Filtros -->
        <div class="trainers-filters">
          <div class="filter-item">
            <label for="filter-specialty">Especialidade</label>
            <select id="filter-specialty">
              <option>Todas as Especialidades</option>
              <option>HIIT</option>
              <option>Força</option>
              <option>Yoga</option>
              <option>Pilates</option>
              <option>Boxe</option>
              <option>Nutrição</option>
            </select>
          </div>
          <div class="filter-item">
            <label for="filter-rating">Avaliação</label>
            <select id="filter-rating">
              <option>Qualquer Avaliação</option>
              <option>5 estrelas</option>
              <option>4.5+</option>
              <option>4.0+</option>
            </select>
          </div>
          <div class="filter-item">
            <label for="filter-sort">Ordenar por</label>
            <select id="filter-sort">
              <option>Mais Populares</option>
              <option>Melhor Avaliação</option>
              <option>Mais Sessões</option>
            </select>
          </div>
        </div>
      </div>
    </section>

    <!-- Grid de Treinadores -->
    <section class="trainers-grid-section" aria-labelledby="trainers-list-heading">
      <div class="container">
        <h2 id="trainers-list-heading" class="visually-hidden">Lista de treinadores</h2>

        <ul class="trainers-grid">
          <?php foreach ($trainers as $trainer): ?>
            <?php drawTrainerCard($trainer); ?>
          <?php endforeach; ?>
        </ul>

      </div>
    </section>

    <!-- CTA -->
    <section class="cta-section" aria-labelledby="cta-heading">
      <div class="container">
        <h2 id="cta-heading">Não Sabes por Onde Começar?</h2>
        <p>Faz o nosso quiz rápido e ajudamos-te a encontrar o treinador ideal para os teus objetivos.</p>
        <div class="cta-buttons">
          <a href="register.php" class="btn btn-primary btn-lg">Criar Conta Grátis</a>
          <a href="classes.php" class="btn btn-ghost btn-lg">Ver Horários</a>
        </div>
      </div>
    </section>

  </main>

<?php require __DIR__ . '/../templates/footer.php'; ?>
