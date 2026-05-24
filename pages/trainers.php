<?php
$pageTitle = 'Treinadores - LiftToBig';
$pageStyles = ['../css/trainers.css'];
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
        <?php if ($isSearch): ?>
          <a href="trainers.php" class="back-link">&#8592; Todos os Treinadores</a>
          <h1 id="trainers-hero-heading">Resultados para <span><?= htmlspecialchars($searchQuery) ?></span>.</h1>
          <p class="hero-subtitle">
            Encontrámos <?= count($trainers) ?> treinador<?= count($trainers) === 1 ? '' : 'es' ?>
            compatíve<?= count($trainers) === 1 ? 'l' : 'is' ?> com a tua pesquisa.
          </p>
        <?php else: ?>
          <h1 id="trainers-hero-heading">Os Nossos <span>Treinadores</span>.</h1>
          <p class="hero-subtitle">
            Profissionais certificados, apaixonados pelo que fazem e totalmente dedicados
            ao teu sucesso. Escolhe o teu mentor e começa hoje.
          </p>
        <?php endif; ?>

        <!-- Filtros -->
        <form class="trainers-search-form" action="trainers.php" method="GET" role="search">
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

       
    </section

    <!-- Grid de Treinadores -->
    <section class="trainers-grid-section" aria-labelledby="trainers-list-heading">
      <div class="container">
        <h2 id="trainers-list-heading" class="visually-hidden">Lista de treinadores</h2>

        <?php if ($trainers !== []): ?>
          <ul class="trainers-grid">
            <?php foreach ($trainers as $trainer): ?>
              <?php drawTrainerCard($trainer); ?>
            <?php endforeach; ?>
          </ul>
        <?php else: ?>
          <div class="empty-state trainers-empty-state">
            <h2>Nenhum treinador encontrado.</h2>
            <p>Tenta pesquisar por nome, especialidade ou objetivo de treino.</p>
            <a href="trainers.php" class="btn btn-outline btn-sm">Ver todos os treinadores</a>
          </div>
        <?php endif; ?>

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
