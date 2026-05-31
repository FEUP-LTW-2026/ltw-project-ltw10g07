<?php
declare(strict_types=1);

ini_set('display_errors', '1');
error_reporting(E_ALL);

date_default_timezone_set('Europe/Lisbon');

require_once __DIR__ . '/../utils/session.php';
require_once __DIR__ . '/../database/connection.php';
require_once __DIR__ . '/../utils/security.php';

$errors = getFlashMessages('errors');
$success = getFlashMessages('success');

$db = getDB();

// 1. LER O ID DA AULA E DA SESSÃO ESPECÍFICA QUE VEM DO HORÁRIO
$class_id = isset($_GET['id']) ? (int) $_GET['id'] : 1;
$target_session_id = isset($_GET['session_id']) ? (int) $_GET['session_id'] : 0;

$stmt = $db->prepare('
    SELECT 
        class.id, class.name, class.description, class.type, class.intensity, 
        class.duration, class.capacity, class.image,
        trainer.id AS trainer_id, user.name AS trainer_name, user.photo AS trainer_photo
    FROM class
    LEFT JOIN trainer ON trainer.id = class.trainer_id
    LEFT JOIN user ON user.id = trainer.user_id
    WHERE class.id = ?
');
$stmt->execute([$class_id]);
$classData = $stmt->fetch(PDO::FETCH_ASSOC);

if ($classData === false) {
    $classData = [
        'id' => $class_id,
        'name' => 'Aula Não Encontrada',
        'description' => 'Esta aula não existe.',
        'type' => 'Geral', 'intensity' => 'Média', 'duration' => 45, 'capacity' => 20,
        'image' => '../img/hiit.jpg', 'trainer_id' => null, 'trainer_name' => 'Treinador', 'trainer_photo' => null
    ];
}

$className = $classData['name'];
$classType = $classData['type'];
$photo = $classData['image'] ?: '../img/hiit.jpg';
$category = $classType;
$intensity = $classData['intensity'] ?? 'Média';
$duration = ($classData['duration'] ?? 45) . ' min';
$description = $classData['description'];
$what_to_expect = ['Aquecimento geral guiado', 'Bloco de exercícios principal', 'Arrefecimento e alongamentos'];
$requirements = 'Trazer roupa confortável de treino, toalha e água.';
$calories = '300–450 kcal';

if (str_contains(strtolower($className), 'hiit')) {
    $photo = '../img/hiit.jpg'; $category = 'Cardio'; $calories = '400–600 kcal';
    $what_to_expect = ['Aquecimento dinâmico de 5 min', 'Blocos de 40s/20s', 'Força e cardio', 'Alongamentos'];
    $requirements = 'Nível intermédio recomendado. Trazer água e toalha.';
} elseif (str_contains(strtolower($className), 'yoga')) {
    $photo = '../img/yoga.jpg'; $category = 'Yoga'; $calories = '150–250 kcal';
    $what_to_expect = ['Meditação', 'Surya Namaskar', 'Posturas', 'Savasana'];
    $requirements = 'Todos os níveis bem-vindos. Trazer tapete.';
} elseif (str_contains(strtolower($className), 'barbell') || str_contains(strtolower($className), 'força')) {
    $photo = '../img/barbell.jpg'; $category = 'Força'; $calories = '300–450 kcal';
    $what_to_expect = ['Mobilidade', 'Técnica', 'Séries de hipertrofia', 'Foam rolling'];
    $requirements = 'Nível intermédio/avançado. Recomendado calçado de levantamento.';
} elseif (str_contains(strtolower($className), 'cycle') || str_contains(strtolower($className), 'cycling')) {
    $photo = '../img/cycling.jpg'; $category = 'Cycling'; $calories = '350–550 kcal';
    $what_to_expect = ['Aquecimento', 'Subidas e sprints', 'Cardio', 'Mobilidade leve'];
    $requirements = 'Trazer água e toalha. Chegar 5 min antes.';
} elseif (str_contains(strtolower($className), 'mobility') || str_contains(strtolower($className), 'core')) {
    $photo = '../img/mobility.jpg'; $category = 'Flexibilidade'; $calories = '120–220 kcal';
    $what_to_expect = ['Mobilidade inicial', 'Core anti-rotação', 'Alongamentos guiados'];
    $requirements = 'Todos os níveis.';
} elseif (str_contains(strtolower($className), 'boxing') || str_contains(strtolower($className), 'boxe')) {
    $photo = '../img/boxing.jpg'; $category = 'Boxe'; $calories = '450–650 kcal';
    $what_to_expect = ['Corda e footwork', 'Combinações', 'Rounds no saco', 'Condicionamento'];
    $requirements = 'Nível iniciante/intermédio. Podes trazer as tuas luvas.';
}

$intensity_badge = ($intensity === 'Alta') ? 'badge-amber' : (($intensity === 'Baixa') ? 'badge-green' : 'badge-blue');
$category_badge  = ($category === 'Flexibilidade') ? 'badge-green' : 'badge-blue';

// 2. BUSCAR AS SESSÕES DESTA AULA
$stmt = $db->prepare('SELECT id, session_date, start_time, room FROM class_session WHERE class_id = ? ORDER BY session_date ASC, start_time ASC');
$stmt->execute([(int)$classData['id']]);
$realSessions = $stmt->fetchAll(PDO::FETCH_ASSOC);

// 3. SELECIONAR A SESSÃO CORRETA (A que veio do URL, ou a próxima disponível)
$mainSession = null;
if ($target_session_id > 0) {
    foreach ($realSessions as $s) {
        if ((int)$s['id'] === $target_session_id) {
            $mainSession = $s;
            break;
        }
    }
}
if (!$mainSession && count($realSessions) > 0) {
    $mainSession = $realSessions[0];
}

$main_session_id = $mainSession ? (int)$mainSession['id'] : 0;

$currentEnrollments = 0;
$waitlistCount = 0;
if ($main_session_id > 0) {
    $stmt = $db->prepare('SELECT COUNT(*) FROM enrollment WHERE session_id = ? AND status = \'active\'');
    $stmt->execute([$main_session_id]);
    $currentEnrollments = (int)$stmt->fetchColumn();

    $stmt = $db->prepare('SELECT COUNT(*) FROM waitlist WHERE session_id = ?');
    $stmt->execute([$main_session_id]);
    $waitlistCount = (int)$stmt->fetchColumn();
}

$spots_total = (int)$classData['capacity'];
$spots_left  = max(0, $spots_total - $currentEnrollments);
$occupancy_pct = ($spots_total > 0) ? (int)round((1 - $spots_left / $spots_total) * 100) : 0;

$isEnrolledInMain = false;
$isOnWaitlist     = false;
$waitlistPosition = 0;
if (isLoggedIn() && $main_session_id > 0) {
    $stmt = $db->prepare('SELECT id, status FROM enrollment WHERE user_id = ? AND session_id = ?');
    $stmt->execute([getUserId(), $main_session_id]);
    $userEnrollment = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($userEnrollment && $userEnrollment['status'] === 'active') {
        $isEnrolledInMain = true;
    }

    if (!$isEnrolledInMain) {
        $stmt = $db->prepare('SELECT id FROM waitlist WHERE user_id = ? AND session_id = ?');
        $stmt->execute([getUserId(), $main_session_id]);
        if ($stmt->fetch()) {
            $isOnWaitlist = true;
            $stmt = $db->prepare('
                SELECT COUNT(*) FROM waitlist
                WHERE session_id = ? AND created_at <= (
                    SELECT created_at FROM waitlist WHERE user_id = ? AND session_id = ?
                )
            ');
            $stmt->execute([$main_session_id, getUserId(), $main_session_id]);
            $waitlistPosition = (int)$stmt->fetchColumn();
        }
    }
}

$pageTitle = htmlspecialchars($className) . ' - LiftToBig';
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
            <span class="badge <?= $category_badge ?>"><?= htmlspecialchars($category) ?></span>
            <span class="badge <?= $intensity_badge ?>"><?= htmlspecialchars($intensity) ?></span>
          </div>
        </div>

        <div class="class-hero-info">
          <a href="classes.php" class="back-link">&#8592; Voltar ao Horário</a>

          <h1 id="class-name-heading"><?= htmlspecialchars($className) ?></h1>

          <div class="class-hero-meta" style="display: flex; flex-wrap: wrap; gap: 1rem; margin-bottom: 1rem;">
            <div class="class-meta-item">
              <span class="meta-icon" aria-hidden="true">&#9202;</span>
              <span><?= htmlspecialchars($duration) ?></span>
            </div>
            
            <?php if ($mainSession): ?>
            <div class="class-meta-item" style="color: #4f46e5; font-weight: bold;">
              <span class="meta-icon" aria-hidden="true">📅</span>
              <span><?= htmlspecialchars($mainSession['session_date']) ?> às <?= htmlspecialchars(substr($mainSession['start_time'], 0, 5)) ?></span>
            </div>
            <div class="class-meta-item" style="color: #4f46e5; font-weight: bold;">
              <span class="meta-icon" aria-hidden="true">📍</span>
              <span><?= htmlspecialchars($mainSession['room']) ?></span>
            </div>
            <?php endif; ?>
          </div>

          <p class="class-hero-description"><?= htmlspecialchars($description) ?></p>

          <?php if ($errors !== []): ?>
            <div class="form-alert form-alert-error" role="alert" style="margin-bottom: 1rem; padding: 0.5rem 1rem; background: #fee2e2; color: #991b1b; border-radius: 4px;">
              <?php foreach ($errors as $error): ?><p><?= htmlspecialchars($error) ?></p><?php endforeach; ?>
            </div>
          <?php endif; ?>

          <?php if ($success !== []): ?>
            <div class="form-alert form-alert-success" role="status" style="margin-bottom: 1rem; padding: 0.5rem 1rem; background: #dcfce7; color: #166534; border-radius: 4px;">
              <?php foreach ($success as $message): ?><p><?= htmlspecialchars($message) ?></p><?php endforeach; ?>
            </div>
          <?php endif; ?>

          <div class="occupancy-bar-wrap" aria-label="Ocupação da aula: <?= $occupancy_pct ?>%">
            <div class="occupancy-bar-header">
              <span>
                <?php if ($spots_left > 0): ?>
                  <?= $spots_left ?> vagas disponíveis
                <?php else: ?>
                  Sessão esgotada
                <?php endif; ?>
                <?php if ($waitlistCount > 0): ?>
                  · <?= $waitlistCount ?> em lista de espera
                <?php endif; ?>
              </span>
              <span><?= $occupancy_pct ?>% ocupado</span>
            </div>
            <div class="occupancy-bar" role="progressbar" aria-valuenow="<?= $occupancy_pct ?>" aria-valuemin="0" aria-valuemax="100">
              <div class="occupancy-fill" style="width: <?= $occupancy_pct ?>%"></div>
            </div>
          </div>

          <?php if (isLoggedIn() && in_array(getUserRole(), ['trainer', 'admin'])): ?>
            <div style="margin-top: 1.5rem; margin-bottom: 1.5rem; padding: 1rem; background: #f8fafc; border: 1px dashed #cbd5e1; border-radius: 8px;">
                <h3 style="font-size: 0.95rem; margin-bottom: 0.75rem; color: #475569;">⚙️ Painel do Treinador</h3>
                <div style="display: flex; gap: 0.5rem;">
                    
                    <?php if ($main_session_id > 0): ?>
                    <form action="../actions/action_delete_session.php" method="POST" style="flex: 1; margin: 0;" onsubmit="return confirm('ATENÇÃO: Queres cancelar e apagar definitivamente esta sessão agendada?');">
                        <?= csrfInput() ?> <input type="hidden" name="session_id" value="<?= $main_session_id ?>">
                        <input type="hidden" name="class_id" value="<?= (int)$classData['id'] ?>">
                        <button type="submit" class="btn btn-danger btn-sm" style="width: 100%; background: #dc2626; border: none; color: white;">Eliminar Sessão</button>
                    </form>
                    <?php endif; ?>
                </div>
            </div>
          <?php endif; ?>

          <?php
          $lockEnroll = false;
          if ($mainSession) {
              $sessionStart = strtotime($mainSession['session_date'] . ' ' . $mainSession['start_time']);
              $lockEnroll   = time() > ($sessionStart - 20 * 60);
          }
          ?>

          <?php if ($main_session_id > 0): ?>
            <form action="../actions/action_enroll.php" method="POST">
              <?= csrfInput() ?>
              <input type="hidden" name="session_id" value="<?= $main_session_id ?>" />
              <input type="hidden" name="class_id" value="<?= (int)$classData['id'] ?>" />

              <?php if ($isEnrolledInMain): ?>
                <input type="hidden" name="action" value="cancel" />
                <button type="submit" class="btn btn-danger btn-lg" style="background:#dc2626;border-color:#dc2626;color:white;">Cancelar Minha Inscrição</button>

              <?php elseif ($isOnWaitlist): ?>
                <div style="margin-bottom:.75rem;padding:.75rem 1rem;background:#fef9c3;border:1px solid #fde047;border-radius:8px;color:#854d0e;">
                  <strong>Estás em <?= $waitlistPosition ?>.º lugar na lista de espera</strong>
                </div>
                <input type="hidden" name="action" value="cancel" />
                <button type="submit" class="btn btn-lg" style="background:#f59e0b;border-color:#f59e0b;color:white;">Sair da Lista de Espera</button>

              <?php elseif ($lockEnroll): ?>
                <button type="button" class="btn btn-primary btn-lg" disabled>Inscrições Encerradas</button>

              <?php elseif ($spots_left > 0): ?>
                <input type="hidden" name="action" value="enroll" />
                <button type="submit" class="btn btn-primary btn-lg">Inscrever-me Nesta Sessão</button>

              <?php else: ?>
                <input type="hidden" name="action" value="enroll" />
                <button type="submit" class="btn btn-lg" style="background:#7c3aed;border-color:#7c3aed;color:white;">Entrar na Lista de Espera</button>
              <?php endif; ?>
            </form>
          <?php else: ?>
            <button class="btn btn-ghost btn-lg" disabled style="opacity:0.6;">Sem sessões agendadas</button>
          <?php endif; ?>
        </div>
      </div>
    </section>

    <section class="class-details-section">
      <div class="container class-details-grid">
        <div class="class-detail-block">
          <h2>O que podes esperar</h2>
          <ul class="expect-list">
            <?php foreach ($what_to_expect as $item): ?>
              <li><span class="expect-icon" aria-hidden="true">&#9654;</span> <?= htmlspecialchars($item) ?></li>
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
              <img src="<?= htmlspecialchars($classData['trainer_photo']) ?>" alt="Treinador" class="avatar" />
            <?php else: ?>
              <div class="avatar" style="display:inline-flex; align-items:center; justify-content:center; background:#4f46e5; color:white; font-weight:bold;">PT</div>
            <?php endif; ?>
            <div>
              <h3><?= htmlspecialchars($classData['trainer_name'] ?? 'Treinador Disponível') ?></h3>
              <?php if (!empty($classData['trainer_id'])): ?>
                <a href="trainer.php?id=<?= (int)$classData['trainer_id'] ?>" class="btn btn-outline btn-sm" style="margin-top:.5rem">Ver Perfil</a>
              <?php endif; ?>
            </div>
          </div>
        </div>
      </div>
    </section>
  


    <?php

    require_once __DIR__ . '/../database/ClassReview.php';

    $canReview   = false;
    $userReview  = null;
    $reviews     = [];
    $avgRating   = 0;

    if (isLoggedIn() && getUserRole() === 'member') {
        $canReview  = ClassReview::memberHasEnrolledInClass($db, (int)getUserId(), $class_id);
        $userReview = ClassReview::findByUserAndClass($db, (int)getUserId(), $class_id);
    }

    $reviews   = ClassReview::getForClass($db, $class_id);
    $avgRating = ClassReview::averageRating($db, $class_id);
    ?>

    <section class="class-reviews-section" id="reviews-section">
      <div class="container">

        <h2>Avaliações
          <?php if ($avgRating > 0): ?>
            <span class="reviews-avg">
              <?= str_repeat('★', (int)round($avgRating)) ?><?= str_repeat('☆', 5 - (int)round($avgRating)) ?>
              <?= number_format($avgRating, 1) ?> / 5
            </span>
          <?php endif; ?>
        </h2>

        <?php if ($userReview && isLoggedIn() && getUserRole() === 'member'): ?>
          <form action="../actions/action_review_class.php" method="POST" style="margin-bottom:1rem">
            <input type="hidden" name="class_id" value="<?= $class_id ?>">
            <input type="hidden" name="delete_review" value="1">
            <button type="submit" class="btn btn-danger btn-sm"
              onclick="return confirm('Tens a certeza que queres remover a tua avaliação?')">
              Remover a minha avaliação
            </button>
          </form>
        <?php endif; ?>

        <?php if ($canReview || $userReview): ?>
          <div class="review-form-wrap">
            <h3><?= $userReview ? 'Editar a tua avaliação' : 'Deixa a tua avaliação' ?></h3>
            <form action="../actions/action_review_class.php" method="POST" class="review-form">
              <input type="hidden" name="class_id" value="<?= $class_id ?>">

              <div class="form-group">
                <label>Classificação</label>
                <div class="star-rating">
                  <?php for ($i = 5; $i >= 1; $i--): ?>
                    <input type="radio" id="star<?= $i ?>" name="rating" value="<?= $i ?>"
                      <?= ($userReview && (int)$userReview['rating'] === $i) ? 'checked' : '' ?> required>
                    <label for="star<?= $i ?>">★</label>
                  <?php endfor; ?>
                </div>
              </div>

              <div class="form-group">
                <label for="comment">Comentário</label>
                <textarea id="comment" name="comment" rows="3" maxlength="700" placeholder="Conta a tua experiência..." required><?= htmlspecialchars($userReview['comment'] ?? '') ?></textarea>
              </div>

              <button type="submit" class="btn btn-primary">
                <?= $userReview ? 'Atualizar avaliação' : 'Publicar avaliação' ?>
              </button>
            </form>
            <?php if ($userReview): ?>
              <form action="../actions/action_review_class.php" method="POST" style="margin-top:.75rem">
                <input type="hidden" name="class_id" value="<?= $class_id ?>">
                <input type="hidden" name="delete_review" value="1">
                <button type="submit" class="btn btn-danger btn-sm"
                  onclick="return confirm('Tens a certeza que queres remover a tua avaliação?')">
                  Remover avaliação
                </button>
              </form>
            <?php endif; ?>
          </div>
        <?php elseif (isLoggedIn() && getUserRole() === 'member'): ?>
          <p class="review-locked">Só podes avaliar aulas em que te inscreveste.</p>
        <?php elseif (!isLoggedIn()): ?>
          <p class="review-locked"><a href="login.php">Inicia sessão</a> para deixar uma avaliação.</p>
        <?php endif; ?>

        <?php if ($reviews === []): ?>
          <p class="reviews-empty">Ainda não há avaliações para esta aula.</p>
        <?php else: ?>
          <div class="reviews-list">
            <?php foreach ($reviews as $r): ?>
              <div class="review-card">
                <div class="review-card-header">
                  <?php if (!empty($r['author_photo'])): ?>
                    <img src="<?= htmlspecialchars($r['author_photo']) ?>" class="review-avatar" alt="">
                  <?php else: ?>
                    <div class="review-avatar review-avatar-placeholder"><?= htmlspecialchars($r['author_initials']) ?></div>
                  <?php endif; ?>
                  <div>
                    <strong><?= htmlspecialchars($r['author']) ?></strong>
                    <span class="review-stars"><?= str_repeat('★', $r['rating']) ?><?= str_repeat('☆', 5 - $r['rating']) ?></span>
                  </div>
                  <span class="review-date"><?= htmlspecialchars(substr($r['created_at'], 0, 10)) ?></span>
                </div>
                <p class="review-comment"><?= htmlspecialchars($r['comment']) ?></p>
              </div>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>

      </div>
    </section>
  </main>
<?php require __DIR__ . '/../templates/footer.php'; ?>