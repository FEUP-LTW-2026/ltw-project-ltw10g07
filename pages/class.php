<?php
$pageTitle = 'Aula - LiftToBig';
$pageStyles = ['../css/class.css'];
$activePage = 'classes';

// Dados mockados — trocar por query à BD depois
$classes = [
  1 => [
    'id'          => 1,
    'name'        => 'HIIT Intensity Max',
    'photo'       => '../img/hiit.jpg',
    'category'    => 'Cardio',
    'intensity'   => 'Alta',
    'intensity_badge' => 'badge-amber',
    'category_badge'  => 'badge-blue',
    'duration'    => '45 min',
    'time'        => '08:30',
    'spots_total' => 20,
    'spots_left'  => 4,
    'trainer_id'  => 1,
    'trainer'     => 'Rui Costa',
    'trainer_photo' => '../img/rui-costa.jpg',
    'description' => 'Uma aula de alta intensidade desenhada para queimar calorias ao máximo, melhorar o condicionamento cardiovascular e acelerar o metabolismo. Combinação de sprints, saltos, exercícios de peso corporal e recuperações ativas.',
    'what_to_expect' => [
      'Aquecimento dinâmico de 5 minutos',
      'Blocos de trabalho de 40s com 20s de recuperação',
      'Exercícios de força e cardio alternados',
      'Arrefecimento e alongamentos finais',
    ],
    'requirements' => 'Nível intermédio recomendado. Trazer garrafa de água e toalha.',
    'calories'    => '400–600 kcal',
    'next_sessions' => [
      ['date' => 'Hoje',    'time' => '08:30', 'spots' => 4],
      ['date' => 'Amanhã',  'time' => '08:30', 'spots' => 11],
      ['date' => 'Quinta',  'time' => '08:30', 'spots' => 8],
    ],
  ],
  2 => [
    'id'          => 2,
    'name'        => 'Zen Yoga Flow',
    'photo'       => '../img/yoga.jpg',
    'category'    => 'Yoga',
    'intensity'   => 'Baixa',
    'intensity_badge' => 'badge-green',
    'category_badge'  => 'badge-blue',
    'duration'    => '60 min',
    'time'        => '10:00',
    'spots_total' => 15,
    'spots_left'  => 12,
    'trainer_id'  => 3,
    'trainer'     => 'Tomás Ferreira',
    'trainer_photo' => '../img/tomas.jpg',
    'description' => 'Uma jornada de movimento fluido e consciência plena. Esta aula combina posturas de Vinyasa com técnicas de respiração Pranayama, criando uma experiência transformadora que acalma a mente e fortalece o corpo.',
    'what_to_expect' => [
      'Respiração e meditação de abertura (5 min)',
      'Sequência de aquecimento Surya Namaskar',
      'Fluxo de posturas em pé e equilíbrio',
      'Posturas de chão e relaxamento final (Savasana)',
    ],
    'requirements' => 'Todos os níveis bem-vindos. Trazer tapete (disponível para aluguer no ginásio).',
    'calories'    => '150–250 kcal',
    'next_sessions' => [
      ['date' => 'Hoje',    'time' => '10:00', 'spots' => 12],
      ['date' => 'Quinta',  'time' => '10:00', 'spots' => 9],
      ['date' => 'Sábado',  'time' => '09:00', 'spots' => 15],
    ],
  ],
  3 => [
    'id'          => 3,
    'name'        => 'Power Barbell',
    'photo'       => '../img/barbell.jpg',
    'category'    => 'Força',
    'intensity'   => 'Alta',
    'intensity_badge' => 'badge-amber',
    'category_badge'  => 'badge-blue',
    'duration'    => '50 min',
    'time'        => '12:15',
    'spots_total' => 12,
    'spots_left'  => 2,
    'trainer_id'  => 2,
    'trainer'     => 'André Brás',
    'trainer_photo' => '../img/andre.jpg',
    'description' => 'Aula focada nos movimentos fundamentais com barra: agachamento, supino e peso morto. Ideal para quem quer ganhar força funcional e massa muscular com técnica impecável.',
    'what_to_expect' => [
      'Mobilidade articular e ativação muscular',
      'Trabalho técnico nos movimentos principais',
      'Séries de hipertrofia e força',
      'Arrefecimento com foam rolling',
    ],
    'requirements' => 'Nível intermédio/avançado. Recomendado calçado de levantamento.',
    'calories'    => '300–450 kcal',
    'next_sessions' => [
      ['date' => 'Hoje',    'time' => '12:15', 'spots' => 2],
      ['date' => 'Quarta',  'time' => '12:15', 'spots' => 7],
      ['date' => 'Sexta',   'time' => '12:15', 'spots' => 10],
    ],
  ],
  4 => [
    'id'          => 4,
    'name'        => 'Cycle Rush 300',
    'photo'       => '../img/cycling.jpg',
    'category'    => 'Cycling',
    'intensity'   => 'Média',
    'intensity_badge' => 'badge-amber',
    'category_badge'  => 'badge-blue',
    'duration'    => '45 min',
    'time'        => '17:00',
    'spots_total' => 18,
    'spots_left'  => 6,
    'trainer_id'  => 2,
    'trainer'     => 'André Brás',
    'trainer_photo' => '../img/andre.jpg',
    'description' => 'Uma aula de cycling energética com intervalos, subidas simuladas e sprints curtos para melhorar resistência cardiovascular e força nas pernas.',
    'what_to_expect' => [
      'Ajuste da bicicleta e aquecimento progressivo',
      'Blocos de cadência com subidas e sprints',
      'Trabalho cardiovascular de intensidade média',
      'Retorno à calma com mobilidade leve',
    ],
    'requirements' => 'Todos os níveis. Trazer água e toalha. Chegar 5 minutos antes para ajustar a bicicleta.',
    'calories'    => '350–550 kcal',
    'next_sessions' => [
      ['date' => 'Hoje',    'time' => '17:00', 'spots' => 6],
      ['date' => 'Quarta',  'time' => '17:00', 'spots' => 10],
      ['date' => 'Sexta',   'time' => '18:00', 'spots' => 7],
    ],
  ],
  5 => [
    'id'          => 5,
    'name'        => 'Mobility & Core',
    'photo'       => '../img/mobility.jpg',
    'category'    => 'Flexibilidade',
    'intensity'   => 'Baixa',
    'intensity_badge' => 'badge-green',
    'category_badge'  => 'badge-green',
    'duration'    => '40 min',
    'time'        => '18:30',
    'spots_total' => 14,
    'spots_left'  => 8,
    'trainer_id'  => 3,
    'trainer'     => 'Tomás Ferreira',
    'trainer_photo' => '../img/tomas.jpg',
    'description' => 'Sessão focada em mobilidade articular, estabilidade do core e controlo postural. Ideal para recuperar melhor e prevenir lesões.',
    'what_to_expect' => [
      'Ativação respiratória e mobilidade inicial',
      'Exercícios de core anti-rotação e estabilidade',
      'Sequências de mobilidade para ancas, ombros e coluna',
      'Alongamentos finais guiados',
    ],
    'requirements' => 'Todos os níveis. Recomendado para quem passa muito tempo sentado ou treina força regularmente.',
    'calories'    => '120–220 kcal',
    'next_sessions' => [
      ['date' => 'Hoje',    'time' => '18:30', 'spots' => 8],
      ['date' => 'Quinta',  'time' => '18:30', 'spots' => 6],
      ['date' => 'Sábado',  'time' => '11:00', 'spots' => 9],
    ],
  ],
  6 => [
    'id'          => 6,
    'name'        => 'Boxing Fundamentals',
    'photo'       => '../img/boxing.jpg',
    'category'    => 'Boxe',
    'intensity'   => 'Alta',
    'intensity_badge' => 'badge-amber',
    'category_badge'  => 'badge-blue',
    'duration'    => '60 min',
    'time'        => '19:00',
    'spots_total' => 16,
    'spots_left'  => 5,
    'trainer_id'  => 1,
    'trainer'     => 'Rui Costa',
    'trainer_photo' => '../img/rui-costa.jpg',
    'description' => 'Aula técnica e intensa de boxe para aprender guarda, deslocamentos, combinações básicas e trabalho cardiovascular com saco.',
    'what_to_expect' => [
      'Aquecimento com corda e footwork',
      'Técnica de jab, direto, esquivas e combinações',
      'Rounds no saco com foco em ritmo e potência',
      'Condicionamento final e alongamentos',
    ],
    'requirements' => 'Nível iniciante/intermédio. Luvas disponíveis no ginásio, mas podes trazer as tuas.',
    'calories'    => '450–650 kcal',
    'next_sessions' => [
      ['date' => 'Hoje',    'time' => '19:00', 'spots' => 5],
      ['date' => 'Quarta',  'time' => '19:00', 'spots' => 3],
      ['date' => 'Sexta',   'time' => '19:30', 'spots' => 8],
    ],
  ],
];

// Obter ID da aula da URL, default 1
$class_id = isset($_GET['id']) ? (int) $_GET['id'] : 1;
$class    = $classes[$class_id] ?? $classes[1];

$pageTitle = htmlspecialchars($class['name']) . ' - LiftToBig';

// Percentagem de ocupação
$occupancy_pct = round((1 - $class['spots_left'] / $class['spots_total']) * 100);

require __DIR__ . '/../templates/header.php';
?>

  <main>

    <!-- Hero da aula -->
    <section class="class-hero" aria-labelledby="class-name-heading">
      <div class="container class-hero-inner">

        <div class="class-hero-image-wrap">
          <img
            src="<?= htmlspecialchars($class['photo']) ?>"
            alt="<?= htmlspecialchars($class['name']) ?>"
            class="class-hero-img"
          />
          <div class="class-hero-badges">
            <span class="badge <?= $class['category_badge'] ?>"><?= htmlspecialchars($class['category']) ?></span>
            <span class="badge <?= $class['intensity_badge'] ?>"><?= htmlspecialchars($class['intensity']) ?></span>
          </div>
        </div>

        <div class="class-hero-info">
          <a href="classes.php" class="back-link">&#8592; Todas as Aulas</a>

          <h1 id="class-name-heading"><?= htmlspecialchars($class['name']) ?></h1>

          <div class="class-hero-meta">
            <div class="class-meta-item">
              <span class="meta-icon" aria-hidden="true">&#9202;</span>
              <span><?= htmlspecialchars($class['duration']) ?></span>
            </div>
            <div class="class-meta-item">
              <span class="meta-icon" aria-hidden="true">&#128293;</span>
              <span>Intensidade: <?= htmlspecialchars($class['intensity']) ?></span>
            </div>
            <div class="class-meta-item">
              <span class="meta-icon" aria-hidden="true">&#128293;</span>
              <span>~<?= htmlspecialchars($class['calories']) ?></span>
            </div>
          </div>

          <p class="class-hero-description"><?= htmlspecialchars($class['description']) ?></p>

          <!-- Barra de ocupação -->
          <div class="occupancy-bar-wrap" aria-label="Ocupação da aula: <?= $occupancy_pct ?>%">
            <div class="occupancy-bar-header">
              <span><?= $class['spots_left'] ?> vagas disponíveis</span>
              <span><?= $occupancy_pct ?>% ocupado</span>
            </div>
            <div class="occupancy-bar" role="progressbar" aria-valuenow="<?= $occupancy_pct ?>" aria-valuemin="0" aria-valuemax="100">
              <div class="occupancy-fill" style="width: <?= $occupancy_pct ?>%"></div>
            </div>
          </div>

          <a href="register.php" class="btn btn-primary btn-lg">Inscrever-me Nesta Aula</a>
        </div>

      </div>
    </section>

    <!-- Detalhes + Treinador + Próximas sessões -->
    <section class="class-details-section">
      <div class="container class-details-grid">

        <!-- O que esperar -->
        <div class="class-detail-block">
          <h2>O que podes esperar</h2>
          <ul class="expect-list">
            <?php foreach ($class['what_to_expect'] as $item): ?>
              <li>
                <span class="expect-icon" aria-hidden="true">&#9654;</span>
                <?= htmlspecialchars($item) ?>
              </li>
            <?php endforeach; ?>
          </ul>

          <div class="requirements-box">
            <h3>&#8505; Requisitos</h3>
            <p><?= htmlspecialchars($class['requirements']) ?></p>
          </div>
        </div>

        <!-- Treinador -->
        <div class="class-detail-block">
          <h2>O Teu Treinador</h2>
          <div class="class-trainer-card">
            <img
              src="<?= htmlspecialchars($class['trainer_photo']) ?>"
              alt="<?= htmlspecialchars($class['trainer']) ?>"
              class="avatar"
            />
            <div>
              <h3><?= htmlspecialchars($class['trainer']) ?></h3>
              <a href="trainer.php?id=<?= $class['trainer_id'] ?>" class="btn btn-outline btn-sm" style="margin-top:.5rem">
                Ver Perfil
              </a>
            </div>
          </div>
        </div>

        <!-- Próximas sessões -->
        <div class="class-detail-block">
          <h2>Próximas Sessões</h2>
          <ul class="next-sessions-list">
            <?php foreach ($class['next_sessions'] as $session): ?>
              <li class="next-session-item">
                <div class="session-datetime">
                  <span class="session-date"><?= htmlspecialchars($session['date']) ?></span>
                  <span class="session-time"><?= htmlspecialchars($session['time']) ?></span>
                </div>
                <div class="session-spots">
                  <span class="badge <?= $session['spots'] <= 3 ? 'badge-danger' : ($session['spots'] <= 7 ? 'badge-amber' : 'badge-green') ?>">
                    <?= $session['spots'] ?> vagas
                  </span>
                  <a href="register.php" class="btn btn-primary btn-sm">Inscrever</a>
                </div>
              </li>
            <?php endforeach; ?>
          </ul>
        </div>

      </div>
    </section>

  </main>

<?php require __DIR__ . '/../templates/footer.php'; ?>
