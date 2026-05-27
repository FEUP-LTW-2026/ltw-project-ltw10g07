<?php
declare(strict_types=1);

require_once __DIR__ . '/../utils/session.php';
require_once __DIR__ . '/../database/connection.php';

$errors = getFlashMessages('errors');
$success = getFlashMessages('success');

$db = getDB();
$classId = isset($_GET['id']) ? (int) $_GET['id'] : 0;

$stmt = $db->prepare('
    SELECT
        class.id,
        class.name,
        class.description,
        class.type,
        class.intensity,
        class.duration,
        class.capacity,
        class.image,
        trainer.id AS trainer_id,
        user.name AS trainer_name,
        user.photo AS trainer_photo
    FROM class
    LEFT JOIN trainer ON trainer.id = class.trainer_id
    LEFT JOIN user ON user.id = trainer.user_id
    WHERE class.id = ?
');
$stmt->execute([$classId]);
$classData = $stmt->fetch(PDO::FETCH_ASSOC);

if ($classData === false) {
    header('Location: classes.php');
    exit;
}

$className = $classData['name'];
$category = $classData['type'];
$intensity = $classData['intensity'] ?? 'Média';
$duration = ((int) ($classData['duration'] ?? 45)) . ' min';
$description = $classData['description'] ?: 'Esta aula ainda não tem descrição.';
$photo = $classData['image'] ?: '../img/hiit.jpg';
$whatToExpect = ['Aquecimento geral guiado', 'Bloco de exercícios principal', 'Arrefecimento e alongamentos'];
$requirements = 'Trazer roupa confortável de treino, toalha e água.';
$calories = '300-450 kcal';

if (str_contains(strtolower($className), 'hiit')) {
    $photo = '../img/hiit.jpg';
    $category = 'Cardio';
    $whatToExpect = [
        'Aquecimento dinâmico de 5 minutos',
        'Blocos de trabalho de 40s com 20s de recuperação',
        'Exercícios de força e cardio alternados',
        'Arrefecimento e alongamentos finais',
    ];
    $requirements = 'Nível intermédio recomendado. Trazer garrafa de água e toalha.';
    $calories = '400-600 kcal';
} elseif (str_contains(strtolower($className), 'yoga')) {
    $photo = '../img/yoga.jpg';
    $category = 'Yoga';
    $whatToExpect = [
        'Respiração e meditação de abertura (5 min)',
        'Sequência de aquecimento Surya Namaskar',
        'Fluxo de posturas em pé e equilíbrio',
        'Posturas de chão e relaxamento final (Savasana)',
    ];
    $requirements = 'Todos os níveis bem-vindos. Trazer tapete (disponível para aluguer no ginásio).';
    $calories = '150-250 kcal';
} elseif (str_contains(strtolower($className), 'barbell') || str_contains(strtolower($className), 'força')) {
    $photo = '../img/barbell.jpg';
    $category = 'Força';
    $whatToExpect = [
        'Mobilidade articular e ativação muscular',
        'Trabalho técnico nos movimentos principais',
        'Séries de hipertrofia e força',
        'Arrefecimento com foam rolling',
    ];
    $requirements = 'Nível intermédio/avançado. Recomendado calçado de levantamento.';
    $calories = '300-450 kcal';
} elseif (str_contains(strtolower($className), 'cycle') || str_contains(strtolower($className), 'cycling')) {
    $photo = '../img/cycling.jpg';
    $category = 'Cycling';
    $whatToExpect = [
        'Ajuste da bicicleta e aquecimento progressivo',
        'Blocos de cadência com subidas e sprints',
        'Trabalho cardiovascular de intensidade média',
        'Retorno à calma com mobilidade leve',
    ];
    $requirements = 'Todos os níveis. Trazer água e toalha. Chegar 5 minutos antes para ajustar a bicicleta.';
    $calories = '350-550 kcal';
} elseif (str_contains(strtolower($className), 'mobility') || str_contains(strtolower($className), 'core') || str_contains(strtolower($className), 'flexibilidade')) {
    $photo = '../img/mobility.jpg';
    $category = 'Flexibilidade';
    $whatToExpect = [
        'Ativação respiratória e mobilidade inicial',
        'Exercícios de core anti-rotação e estabilidade',
        'Sequências de mobilidade para ancas, ombros e coluna',
        'Alongamentos finais guiados',
    ];
    $requirements = 'Todos os níveis. Recomendado para quem passa muito tempo sentado ou treina força regularmente.';
    $calories = '120-220 kcal';
} elseif (str_contains(strtolower($className), 'boxing') || str_contains(strtolower($className), 'boxe')) {
    $photo = '../img/boxing.jpg';
    $category = 'Boxe';
    $whatToExpect = [
        'Aquecimento com corda e footwork',
        'Técnica de jab, direto, esquivas e combinações',
        'Rounds no saco com foco em ritmo e potência',
        'Condicionamento final e alongamentos',
    ];
    $requirements = 'Nível iniciante/intermédio. Luvas disponíveis no ginásio, mas podes trazer as tuas.';
    $calories = '450-650 kcal';
}

$intensityBadge = $intensity === 'Alta' ? 'badge-amber' : ($intensity === 'Baixa' ? 'badge-green' : 'badge-blue');
$categoryBadge = $category === 'Flexibilidade' ? 'badge-green' : 'badge-blue';

$stmt = $db->prepare('
    SELECT id, session_date, start_time, room
    FROM class_session
    WHERE class_id = ?
    ORDER BY session_date ASC, start_time ASC
');
$stmt->execute([(int) $classData['id']]);
$realSessions = $stmt->fetchAll(PDO::FETCH_ASSOC);

$mainSession = $realSessions[0] ?? null;
$mainSessionId = $mainSession ? (int) $mainSession['id'] : 0;

$currentEnrollments = 0;
if ($mainSessionId > 0) {
    $stmt = $db->prepare('SELECT COUNT(*) FROM enrollment WHERE session_id = ? AND status = \'active\'');
    $stmt->execute([$mainSessionId]);
    $currentEnrollments = (int) $stmt->fetchColumn();
}

$spotsTotal = (int) $classData['capacity'];
$spotsLeft = max(0, $spotsTotal - $currentEnrollments);
$occupancyPct = $spotsTotal > 0 ? (int) round((1 - $spotsLeft / $spotsTotal) * 100) : 0;

$isEnrolledInMain = false;
if (isLoggedIn() && $mainSessionId > 0) {
    $stmt = $db->prepare('SELECT id FROM enrollment WHERE user_id = ? AND session_id = ? AND status = \'active\'');
    $stmt->execute([getUserId(), $mainSessionId]);
    $isEnrolledInMain = (bool) $stmt->fetch();
}

$pageTitle = $className . ' - LiftToBig';
$pageStyles = ['../css/class.css'];
$activePage = 'classes';

require __DIR__ . '/../templates/header.php';
?>

  <main>
    <section class="class-hero" aria-labelledby="class-name-heading">
      <div class="container class-hero-inner">

        <div class="class-hero-image-wrap">
          <img src="<?= htmlspecialchars($photo) ?>" alt="<?= htmlspecialchars($className) ?>" class="class-hero-img" />
          <div class="class-hero-badges">
            <span class="badge <?= $categoryBadge ?>"><?= htmlspecialchars($category) ?></span>
            <span class="badge <?= $intensityBadge ?>"><?= htmlspecialchars($intensity) ?></span>
          </div>
        </div>

        <div class="class-hero-info">
          <a href="classes.php" class="back-link">&#8592; Todas as Aulas</a>

          <h1 id="class-name-heading"><?= htmlspecialchars($className) ?></h1>

          <div class="class-hero-meta">
            <div class="class-meta-item">
              <span class="meta-icon" aria-hidden="true">&#9202;</span>
              <span><?= htmlspecialchars($duration) ?></span>
            </div>
            <div class="class-meta-item">
              <span class="meta-icon" aria-hidden="true">&#128293;</span>
              <span>Intensidade: <?= htmlspecialchars($intensity) ?></span>
            </div>
            <div class="class-meta-item">
              <span class="meta-icon" aria-hidden="true">&#128101;</span>
              <span><?= $spotsLeft ?> vagas disponíveis</span>
            </div>
            <div class="class-meta-item">
              <span class="meta-icon" aria-hidden="true">&#128293;</span>
              <span>~<?= htmlspecialchars($calories) ?></span>
            </div>
          </div>

          <p class="class-hero-description"><?= htmlspecialchars($description) ?></p>

          <?php if ($errors !== []): ?>
            <div class="form-alert form-alert-error" role="alert" style="margin-bottom: 1rem; padding: 0.5rem 1rem; background: #fee2e2; color: #991b1b; border-radius: 4px;">
              <?php foreach ($errors as $error): ?>
                <p><?= htmlspecialchars($error) ?></p>
              <?php endforeach; ?>
            </div>
          <?php endif; ?>

          <?php if ($success !== []): ?>
            <div class="form-alert form-alert-success" role="status" style="margin-bottom: 1rem; padding: 0.5rem 1rem; background: #dcfce7; color: #166534; border-radius: 4px;">
              <?php foreach ($success as $message): ?>
                <p><?= htmlspecialchars($message) ?></p>
              <?php endforeach; ?>
            </div>
          <?php endif; ?>

          <div class="occupancy-bar-wrap" aria-label="Ocupação da aula: <?= $occupancyPct ?>%">
            <div class="occupancy-bar-header">
              <span><?= $spotsLeft ?> vagas disponíveis</span>
              <span><?= $occupancyPct ?>% ocupado</span>
            </div>
            <div class="occupancy-bar" role="progressbar" aria-valuenow="<?= $occupancyPct ?>" aria-valuemin="0" aria-valuemax="100">
              <div class="occupancy-fill" style="width: <?= $occupancyPct ?>%"></div>
            </div>
          </div>

          <?php if (isLoggedIn() && in_array(getUserRole(), ['trainer', 'admin'], true)): ?>
            <div style="margin-top: 1.5rem; margin-bottom: 1.5rem; padding: 1rem; background: #f8fafc; border: 1px dashed #cbd5e1; border-radius: 8px;">
              <h3 style="font-size: 0.95rem; margin-bottom: 0.75rem; color: #475569; text-transform: uppercase; letter-spacing: 0.5px;">Painel do Treinador</h3>
              <div style="display: flex; gap: 0.5rem;">
                <form action="../actions/action_delete_class.php" method="POST" style="flex: 1; margin: 0;" onsubmit="return confirm('Tens a certeza que queres eliminar esta aula e todas as inscrições associadas?');">
                  <input type="hidden" name="class_id" value="<?= (int) $classData['id'] ?>">
                  <button type="submit" class="btn btn-danger btn-sm" style="width: 100%; background: #dc2626; border: none; color: white; cursor: pointer;">Eliminar Aula</button>
                </form>
              </div>
            </div>
          <?php endif; ?>

          <?php if ($mainSessionId > 0): ?>
            <form action="../actions/action_enroll.php" method="POST">
              <input type="hidden" name="session_id" value="<?= $mainSessionId ?>" />
              <input type="hidden" name="class_id" value="<?= (int) $classData['id'] ?>" />

              <?php if ($isEnrolledInMain): ?>
                <input type="hidden" name="action" value="cancel" />
                <button type="submit" class="btn btn-danger btn-lg" style="background: #dc2626; border-color: #dc2626; color: white; cursor: pointer;">Cancelar Minha Inscrição</button>
              <?php else: ?>
                <input type="hidden" name="action" value="enroll" />
                <button type="submit" class="btn btn-primary btn-lg" <?= $spotsLeft === 0 ? 'disabled' : '' ?> style="cursor: pointer;">
                  <?= $spotsLeft === 0 ? 'Aula Esgotada' : 'Inscrever-me Nesta Aula' ?>
                </button>
              <?php endif; ?>
            </form>
          <?php else: ?>
            <button class="btn btn-ghost btn-lg" disabled style="opacity: 0.6;">Sem sessões agendadas</button>
          <?php endif; ?>
        </div>

      </div>
    </section>

    <section class="class-details">
      <div class="container class-details-grid">

        <div class="class-detail-block">
          <h2>O que podes esperar</h2>
          <ul class="expect-list">
            <?php foreach ($whatToExpect as $item): ?>
              <li>
                <span class="expect-icon" aria-hidden="true">&#9654;</span>
                <?= htmlspecialchars($item) ?>
              </li>
            <?php endforeach; ?>
          </ul>

          <div class="requirements-box">
            <h3>&#8505; Requisitos</h3>
            <p><?= htmlspecialchars($requirements) ?></p>
          </div>
        </div>

        <div class="class-detail-block">
          <h2>O Teu Treinador</h2>
          <div class="class-trainer-card">
            <?php if (!empty($classData['trainer_photo'])): ?>
              <img src="<?= htmlspecialchars($classData['trainer_photo']) ?>" alt="<?= htmlspecialchars($classData['trainer_name'] ?? 'Treinador') ?>" class="avatar" />
            <?php else: ?>
              <div class="avatar" style="display:inline-flex; align-items:center; justify-content:center; background:#4f46e5; color:white; font-weight:bold;">PT</div>
            <?php endif; ?>
            <div>
              <h3><?= htmlspecialchars($classData['trainer_name'] ?? 'Treinador Disponível') ?></h3>
              <?php if (!empty($classData['trainer_id'])): ?>
                <a href="trainer.php?id=<?= (int) $classData['trainer_id'] ?>" class="btn btn-outline btn-sm" style="margin-top:.5rem">Ver Perfil</a>
              <?php endif; ?>
            </div>
          </div>
        </div>

        <div class="class-detail-block">
          <h2>Próximas Sessões</h2>
          <?php if ($realSessions !== []): ?>
            <ul class="next-sessions-list">
              <?php foreach ($realSessions as $session): ?>
                <?php
                  $sessionId = (int) $session['id'];
                  $stmt = $db->prepare('SELECT COUNT(*) FROM enrollment WHERE session_id = ? AND status = \'active\'');
                  $stmt->execute([$sessionId]);
                  $sessionEnrollments = (int) $stmt->fetchColumn();
                  $sessionSpotsLeft = max(0, $spotsTotal - $sessionEnrollments);

                  $userEnrolledInThis = false;
                  if (isLoggedIn()) {
                      $stmt = $db->prepare('SELECT id FROM enrollment WHERE user_id = ? AND session_id = ? AND status = \'active\'');
                      $stmt->execute([getUserId(), $sessionId]);
                      $userEnrolledInThis = (bool) $stmt->fetch();
                  }

                  $displayDate = $session['session_date'];
                  if ($displayDate === date('Y-m-d')) {
                      $displayDate = 'Hoje';
                  } elseif ($displayDate === date('Y-m-d', strtotime('+1 day'))) {
                      $displayDate = 'Amanhã';
                  }
                ?>
                <li class="next-session-item">
                  <div class="session-datetime">
                    <span class="session-date"><?= htmlspecialchars($displayDate) ?></span>
                    <span class="session-time"><?= htmlspecialchars(substr($session['start_time'], 0, 5)) ?></span>
                  </div>
                  <div class="session-spots">
                    <span class="badge <?= $sessionSpotsLeft <= 3 ? 'badge-danger' : ($sessionSpotsLeft <= 7 ? 'badge-amber' : 'badge-green') ?>">
                      <?= $sessionSpotsLeft ?> vagas
                    </span>

                    <form action="../actions/action_enroll.php" method="POST" style="display:inline;">
                      <input type="hidden" name="session_id" value="<?= $sessionId ?>" />
                      <input type="hidden" name="class_id" value="<?= (int) $classData['id'] ?>" />

                      <?php if ($userEnrolledInThis): ?>
                        <input type="hidden" name="action" value="cancel" />
                        <button type="submit" class="btn btn-sm" style="background: #dc2626; color: white; border: none; padding: 4px 8px; border-radius: 4px; font-size: 0.85rem; cursor: pointer;">Cancelar</button>
                      <?php else: ?>
                        <input type="hidden" name="action" value="enroll" />
                        <button type="submit" class="btn btn-primary btn-sm" <?= $sessionSpotsLeft === 0 ? 'disabled' : '' ?> style="cursor: pointer;">
                          <?= $sessionSpotsLeft === 0 ? 'Cheio' : 'Inscrever' ?>
                        </button>
                      <?php endif; ?>
                    </form>
                  </div>
                </li>
              <?php endforeach; ?>
            </ul>
          <?php else: ?>
            <p style="color: var(--color-text-muted); font-size: 0.95rem;">Não existem mais sessões agendadas para esta aula.</p>
          <?php endif; ?>
        </div>

      </div>
    </section>
  </main>

<?php require __DIR__ . '/../templates/footer.php'; ?>
