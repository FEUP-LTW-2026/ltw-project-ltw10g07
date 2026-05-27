<?php
$pageTitle = 'Treinadores - LiftToBig';
$pageStyles = ['../css/trainers.css'];
$pageScripts = ['../js/trainers.js'];
$activePage = 'trainers';
require_once __DIR__ . '/../database/connection.php';
require_once __DIR__ . '/../database/Trainer.php';
require_once __DIR__ . '/../templates/trainers.php';

$db = getDB();
$searchQuery = trim($_GET['q'] ?? '');
$isSearch = $searchQuery !== '';
$trainers = $isSearch ? Trainer::search($db, $searchQuery) : Trainer::getAll($db);

require __DIR__ . '/../templates/header.php';
?>

  <main>

    <!-- Hero -->
    <section class="trainers-hero" aria-labelledby="trainers-hero-heading">
      <div class="container">
        <h1 id="trainers-hero-heading">Os Nossos <span>Treinadores</span>.</h1>
        <p class="hero-subtitle" id="trainers-result-summary">
          <?php if ($isSearch): ?>
            Encontrámos <?= count($trainers) ?> treinador<?= count($trainers) === 1 ? '' : 'es' ?>
            compatíve<?= count($trainers) === 1 ? 'l' : 'is' ?> com a tua pesquisa.
          <?php else: ?>
            Profissionais certificados, apaixonados pelo que fazem e totalmente dedicados
            ao teu sucesso. Escolhe o teu mentor e começa hoje.
          <?php endif; ?>
        </p>

        <!-- Filtros -->
        <form class="trainers-search-form" id="trainers-search-form" action="trainers.php" method="GET" role="search">
          <label for="trainer-search">Pesquisar treinadores</label>
          <input
            type="search"
            id="trainer-search"
            name="q"
            value="<?= htmlspecialchars($searchQuery) ?>"
            placeholder="Nome, especialidade ou objetivo..."
          />
          <button type="submit" class="btn btn-primary btn-sm">Pesquisar</button>
        </form>

      </div>
    </section>

    <!-- Grid de Treinadores -->
    <section class="trainers-grid-section" aria-labelledby="trainers-list-heading">
      <div class="container">
        <h2 id="trainers-list-heading" class="visually-hidden">Lista de treinadores</h2>

        <ul class="trainers-grid" id="trainers-grid" <?= $trainers === [] ? 'hidden' : '' ?>>
          <?php foreach ($trainers as $trainer): ?>
            <?php drawTrainerCard($trainer); ?>
          <?php endforeach; ?>
        </ul>

        <div class="empty-state trainers-empty-state" id="trainers-empty-state" <?= $trainers !== [] ? 'hidden' : '' ?>>
          <h2>Nenhum treinador encontrado.</h2>
          <p>Tenta pesquisar por nome, especialidade ou objetivo de treino.</p>
        </div>

      </div>
    </section>

    <?php if (!isLoggedIn()): ?>
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
    <?php endif; ?>

  </main>

<?php require __DIR__ . '/../templates/footer.php'; ?>
