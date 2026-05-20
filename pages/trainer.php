<?php
$pageTitle = 'Treinador - LiftToBig';
$pageStyles = ['../css/trainers.css'];
$activePage = 'trainers';

// Dados mockados — trocar por query à BD depois
$trainers = [
  1 => [
    'id'           => 1,
    'name'         => 'Rui Costa',
    'photo'        => '../img/rui-costa.jpg',
    'specialty'    => 'Personal Trainer · HIIT',
    'rating'       => 4.9,
    'reviews_count'=> 87,
    'sessions'     => 312,
    'experience'   => '8 anos',
    'badges'       => ['HIIT', 'Cardio', 'Perda de Peso'],
    'bio'          => 'Especialista em treino de alta intensidade com foco em resultados rápidos e sustentáveis. Certificado pela NSCA, com formação em nutrição desportiva e biomecânica do movimento.',
    'certifications' => ['NSCA – Certified Personal Trainer', 'Nutrição Desportiva (ISEF)', 'First Aid & CPR'],
    'schedule'     => [
      ['day' => 'Segunda', 'time' => '07:00 – 09:00'],
      ['day' => 'Terça',   'time' => '18:00 – 21:00'],
      ['day' => 'Quinta',  'time' => '07:00 – 09:00'],
      ['day' => 'Sábado',  'time' => '09:00 – 12:00'],
    ],
    'classes' => [
      ['name' => 'HIIT Intensity Max', 'time' => '08:30', 'duration' => '45 min', 'spots' => 4],
      ['name' => 'Boxing Fundamentals', 'time' => '19:00', 'duration' => '60 min', 'spots' => 5],
    ],
    'reviews' => [
      ['author' => 'Sofia C.', 'rating' => 5, 'text' => 'O Rui é incrível! Em 3 meses perdi 8kg e ganhei imenso condicionamento. Super motivador.'],
      ['author' => 'Miguel R.', 'rating' => 5, 'text' => 'Treinos intensos mas muito bem estruturados. Nota-se que sabe o que faz.'],
      ['author' => 'Ana L.',   'rating' => 4, 'text' => 'Muito bom treinador. As aulas de HIIT são desafiadoras mas valeu cada segundo.'],
    ],
  ],
  2 => [
    'id'           => 2,
    'name'         => 'André Brás',
    'photo'        => '../img/andre.jpg',
    'specialty'    => 'Força & Condicionamento',
    'rating'       => 5.0,
    'reviews_count'=> 124,
    'sessions'     => 487,
    'experience'   => '12 anos',
    'badges'       => ['Força', 'Powerlifting', 'Hipertrofia'],
    'bio'          => 'Ex-atleta de powerlifting com participações em campeonatos nacionais. Agora dedica-se a ajudar outros a atingirem o seu máximo potencial de força e massa muscular, com metodologias baseadas em evidência.',
    'certifications' => ['CSCS – Certified Strength & Conditioning Specialist', 'Powerlifting Coach Level 2 (FPP)', 'Corrective Exercise Specialist'],
    'schedule'     => [
      ['day' => 'Segunda',  'time' => '09:00 – 13:00'],
      ['day' => 'Quarta',   'time' => '09:00 – 13:00'],
      ['day' => 'Sexta',    'time' => '09:00 – 13:00'],
      ['day' => 'Sábado',   'time' => '10:00 – 13:00'],
    ],
    'classes' => [
      ['name' => 'Power Barbell', 'time' => '12:15', 'duration' => '50 min', 'spots' => 2],
      ['name' => 'Cycle Rush 300', 'time' => '17:00', 'duration' => '45 min', 'spots' => 6],
    ],
    'reviews' => [
      ['author' => 'Carlos M.', 'rating' => 5, 'text' => 'O André transformou completamente o meu agachamento. Técnica impecável, paciência infinita.'],
      ['author' => 'Pedro S.',  'rating' => 5, 'text' => 'Melhor treinador de força que já tive. Programação inteligente e resultados reais.'],
      ['author' => 'Joana F.',  'rating' => 5, 'text' => 'Comecei do zero e em 6 meses já faço 100kg no supino. Recomendo a toda a gente!'],
    ],
  ],
  3 => [
    'id'           => 3,
    'name'         => 'Tomás Ferreira',
    'photo'        => '../img/tomas.jpg',
    'specialty'    => 'Yoga Master',
    'rating'       => 4.8,
    'reviews_count'=> 63,
    'sessions'     => 256,
    'experience'   => '10 anos',
    'badges'       => ['Yoga', 'Mobilidade', 'Meditação'],
    'bio'          => 'Mestre em Hatha e Vinyasa Yoga, certificado pela Yoga Alliance com 500 horas. Apaixonado pelo equilíbrio entre mente e corpo, oferece aulas que combinam movimento, respiração e consciência plena.',
    'certifications' => ['Yoga Alliance RYT-500', 'Mindfulness-Based Stress Reduction (MBSR)', 'Mobility & Flexibility Specialist'],
    'schedule'     => [
      ['day' => 'Terça',   'time' => '07:00 – 10:00'],
      ['day' => 'Quinta',  'time' => '07:00 – 10:00'],
      ['day' => 'Sábado',  'time' => '08:00 – 11:00'],
      ['day' => 'Domingo', 'time' => '09:00 – 11:00'],
    ],
    'classes' => [
      ['name' => 'Zen Yoga Flow',  'time' => '10:00', 'duration' => '60 min', 'spots' => 12],
      ['name' => 'Mobility & Core', 'time' => '18:30', 'duration' => '40 min', 'spots' => 8],
    ],
    'reviews' => [
      ['author' => 'Rita A.',    'rating' => 5, 'text' => 'As aulas do Tomás são uma experiência única. Saio sempre renovada e em paz.'],
      ['author' => 'Beatriz N.', 'rating' => 5, 'text' => 'Melhorei muito a minha flexibilidade e a minha postura em poucos meses.'],
      ['author' => 'Diogo P.',   'rating' => 4, 'text' => 'Excelente para descomprimir do stress do trabalho. Muito recomendado.'],
    ],
  ],
];

// Obter o ID do treinador da URL, default 1
$trainer_id = isset($_GET['id']) ? (int) $_GET['id'] : 1;
$trainer    = $trainers[$trainer_id] ?? $trainers[1];

$pageTitle = htmlspecialchars($trainer['name']) . ' - LiftToBig';

require __DIR__ . '/../templates/header.php';
?>

  <main>

    <!-- Perfil principal -->
    <section class="trainer-profile-hero" aria-labelledby="trainer-name-heading">
      <div class="container trainer-profile-inner">

        <div class="trainer-profile-photo-wrap">
          <img
            src="<?= htmlspecialchars($trainer['photo']) ?>"
            alt="<?= htmlspecialchars($trainer['name']) ?>"
            class="trainer-profile-photo"
          />
        </div>

        <div class="trainer-profile-info">
          <a href="trainers.php" class="back-link">&#8592; Todos os Treinadores</a>

          <div class="trainer-profile-badges">
            <?php foreach ($trainer['badges'] as $badge): ?>
              <span class="badge badge-blue"><?= htmlspecialchars($badge) ?></span>
            <?php endforeach; ?>
          </div>

          <h1 id="trainer-name-heading"><?= htmlspecialchars($trainer['name']) ?></h1>
          <p class="trainer-profile-specialty"><?= htmlspecialchars($trainer['specialty']) ?></p>

          <div class="trainer-profile-stats">
            <div class="profile-stat">
              <span class="stat-value">&#9733; <?= number_format($trainer['rating'], 1) ?></span>
              <span class="stat-label"><?= $trainer['reviews_count'] ?> avaliações</span>
            </div>
            <div class="profile-stat">
              <span class="stat-value"><?= $trainer['sessions'] ?>+</span>
              <span class="stat-label">Sessões realizadas</span>
            </div>
            <div class="profile-stat">
              <span class="stat-value"><?= htmlspecialchars($trainer['experience']) ?></span>
              <span class="stat-label">Experiência</span>
            </div>
          </div>

          <p class="trainer-profile-bio"><?= htmlspecialchars($trainer['bio']) ?></p>

          <div class="trainer-profile-actions">
            <a href="classes.php?trainer=<?= urlencode($trainer['name']) ?>" class="btn btn-primary">Ver Aulas Disponíveis</a>
            <a href="register.php" class="btn btn-outline">Reservar Consulta</a>
          </div>
        </div>

      </div>
    </section>

    <!-- Detalhes: certificações + horário + aulas -->
    <section class="trainer-details-section">
      <div class="container trainer-details-grid">

        <!-- Certificações -->
        <div class="trainer-detail-block">
          <h2>Certificações</h2>
          <ul class="certifications-list">
            <?php foreach ($trainer['certifications'] as $cert): ?>
              <li>
                <span class="cert-icon" aria-hidden="true">&#10003;</span>
                <?= htmlspecialchars($cert) ?>
              </li>
            <?php endforeach; ?>
          </ul>
        </div>

        <!-- Horário -->
        <div class="trainer-detail-block">
          <h2>Disponibilidade</h2>
          <ul class="schedule-list">
            <?php foreach ($trainer['schedule'] as $slot): ?>
              <li class="schedule-item">
                <span class="schedule-day"><?= htmlspecialchars($slot['day']) ?></span>
                <span class="schedule-time"><?= htmlspecialchars($slot['time']) ?></span>
              </li>
            <?php endforeach; ?>
          </ul>
        </div>

        <!-- Aulas -->
        <div class="trainer-detail-block">
          <h2>Aulas Esta Semana</h2>
          <ul class="trainer-classes-list">
            <?php foreach ($trainer['classes'] as $class): ?>
              <li class="trainer-class-item">
                <div class="trainer-class-info">
                  <h3><?= htmlspecialchars($class['name']) ?></h3>
                  <p><?= htmlspecialchars($class['time']) ?> · <?= htmlspecialchars($class['duration']) ?></p>
                </div>
                <div class="trainer-class-spots">
                  <span class="badge <?= $class['spots'] <= 3 ? 'badge-danger' : 'badge-green' ?>">
                    <?= $class['spots'] ?> vagas
                  </span>
                  <a href="classes.php" class="btn btn-outline btn-sm">Inscrever</a>
                </div>
              </li>
            <?php endforeach; ?>
          </ul>
        </div>

      </div>
    </section>

    <!-- Reviews -->
    <section class="trainer-reviews-section" aria-labelledby="reviews-heading">
      <div class="container">
        <h2 id="reviews-heading">O que dizem os membros</h2>

        <ul class="reviews-grid">
          <?php foreach ($trainer['reviews'] as $review): ?>
          <li class="review-card">
            <div class="review-header">
              <div class="review-avatar"><?= mb_substr($review['author'], 0, 1) ?></div>
              <div>
                <p class="review-author"><?= htmlspecialchars($review['author']) ?></p>
                <p class="review-stars">
                  <?php for ($i = 0; $i < $review['rating']; $i++): ?>&#9733;<?php endfor; ?>
                </p>
              </div>
            </div>
            <p class="review-text"><?= htmlspecialchars($review['text']) ?></p>
          </li>
          <?php endforeach; ?>
        </ul>
      </div>
    </section>

  </main>

<?php require __DIR__ . '/../templates/footer.php'; ?>