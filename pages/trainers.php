<?php
$pageTitle = 'Treinadores - LiftToBig';
$pageStyles = ['../css/trainers.css'];
$activePage = 'trainers';

// Dados mockados — trocar por query à BD depois
$trainers = [
  [
    'id'         => 1,
    'name'       => 'Rui Costa',
    'slug'       => 'rui-costa',
    'photo'      => '../img/rui-costa.jpg',
    'specialty'  => 'Personal Trainer · HIIT',
    'rating'     => 4.9,
    'sessions'   => 312,
    'experience' => '8 anos',
    'badges'     => ['HIIT', 'Cardio', 'Perda de Peso'],
    'bio'        => 'Especialista em treino de alta intensidade com foco em resultados rápidos e sustentáveis. Certificado pela NSCA.',
  ],
  [
    'id'         => 2,
    'name'       => 'André Brás',
    'slug'       => 'andre-bras',
    'photo'      => '../img/andre.jpg',
    'specialty'  => 'Força & Condicionamento',
    'rating'     => 5.0,
    'sessions'   => 487,
    'experience' => '12 anos',
    'badges'     => ['Força', 'Powerlifting', 'Hipertrofia'],
    'bio'        => 'Ex-atleta de powerlifting, agora dedicado a ajudar outros a atingirem o seu máximo potencial de força e massa muscular.',
  ],
  [
    'id'         => 3,
    'name'       => 'Tomás Ferreira',
    'slug'       => 'tomas-ferreira',
    'photo'      => '../img/tomas.jpg',
    'specialty'  => 'Yoga Master',
    'rating'     => 4.8,
    'sessions'   => 256,
    'experience' => '10 anos',
    'badges'     => ['Yoga', 'Mobilidade', 'Meditação'],
    'bio'        => 'Mestre em Hatha e Vinyasa Yoga, certificado pela Yoga Alliance. Apaixonado por bem-estar mental e físico.',
  ],
];

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
          <li class="trainer-card-full">
            <div class="trainer-photo-wrap">
              <img src="<?= htmlspecialchars($trainer['photo']) ?>" alt="<?= htmlspecialchars($trainer['name']) ?>" class="trainer-photo" />
            </div>
            <div class="trainer-body">
              <div class="trainer-header">
                <div>
                  <h3><?= htmlspecialchars($trainer['name']) ?></h3>
                  <p class="trainer-specialty"><?= htmlspecialchars($trainer['specialty']) ?></p>
                </div>
                <span class="trainer-rating">&#9733; <?= number_format($trainer['rating'], 1) ?></span>
              </div>

              <p class="trainer-bio"><?= htmlspecialchars($trainer['bio']) ?></p>

              <div class="trainer-badges">
                <?php foreach ($trainer['badges'] as $badge): ?>
                  <span class="badge badge-blue"><?= htmlspecialchars($badge) ?></span>
                <?php endforeach; ?>
              </div>

              <div class="trainer-stats">
                <div class="trainer-stat">
                  <span class="stat-value"><?= $trainer['sessions'] ?>+</span>
                  <span class="stat-label">Sessões</span>
                </div>
                <div class="trainer-stat">
                  <span class="stat-value"><?= htmlspecialchars($trainer['experience']) ?></span>
                  <span class="stat-label">Experiência</span>
                </div>
                <div class="trainer-stat">
                  <span class="stat-value">&#9733; <?= number_format($trainer['rating'], 1) ?></span>
                  <span class="stat-label">Avaliação</span>
                </div>
              </div>

              <div class="trainer-actions">
                <a href="trainer.php?id=<?= $trainer['id'] ?>" class="btn btn-outline btn-sm">Ver Perfil</a>
                <a href="classes.php?trainer=<?= urlencode($trainer['name']) ?>" class="btn btn-primary btn-sm">Ver Aulas</a>
              </div>
            </div>
          </li>
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