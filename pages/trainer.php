<?php
require_once __DIR__ . '/../database/connection.php';
require_once __DIR__ . '/../database/Trainer.php';
require_once __DIR__ . '/../database/TrainerReview.php';
require_once __DIR__ . '/../utils/session.php';

$db = getDB();
$trainerId = isset($_GET['id']) ? (int) $_GET['id'] : 1;
$trainer = Trainer::findById($db, $trainerId);

if ($trainer === null) {
  header('Location: trainers.php');
  exit;
}

$reviews = TrainerReview::getForTrainer($db, $trainerId);
$existingReview = isLoggedIn() ? TrainerReview::findByUserAndTrainer($db, (int) getUserId(), $trainerId) : null;
$old = getOldInput([
  'rating' => $existingReview['rating'] ?? '5',
  'comment' => $existingReview['comment'] ?? '',
]);
$errors = getFlashMessages('errors');
$success = getFlashMessages('success');
$canReview = isLoggedIn() && getUserRole() === 'member';

$pageTitle = htmlspecialchars($trainer['name']) . ' - LiftToBig';
$pageStyles = ['../css/trainers.css'];
$activePage = 'trainers';

require __DIR__ . '/../templates/header.php';
?>

  <main>

    <section class="trainer-profile-hero" aria-labelledby="trainer-name-heading">
      <div class="container trainer-profile-inner">

        <div class="trainer-profile-photo-wrap">
          <?php if (!empty($trainer['photo'])): ?>
            <img
              src="<?= htmlspecialchars($trainer['photo']) ?>"
              alt="<?= htmlspecialchars($trainer['name']) ?>"
              class="trainer-profile-photo"
            />
          <?php else: ?>
            <div class="trainer-profile-photo trainer-profile-initials" aria-hidden="true">
              <?= htmlspecialchars($trainer['initials']) ?>
            </div>
          <?php endif; ?>
        </div>

        <div class="trainer-profile-info">
          <a href="trainers.php" class="back-link">&#8592; Todos os Treinadores</a>

          <?php if ($trainer['badges'] !== []): ?>
            <div class="trainer-profile-badges">
              <?php foreach ($trainer['badges'] as $badge): ?>
                <span class="badge badge-blue"><?= htmlspecialchars($badge) ?></span>
              <?php endforeach; ?>
            </div>
          <?php endif; ?>

          <h1 id="trainer-name-heading"><?= htmlspecialchars($trainer['name']) ?></h1>
          <p class="trainer-profile-specialty"><?= htmlspecialchars($trainer['specialty']) ?></p>

          <div class="trainer-profile-stats">
            <div class="profile-stat">
              <span class="stat-value">&#9733; <?= number_format($trainer['rating'], 1) ?></span>
              <span class="stat-label"><?= (int) $trainer['reviews_count'] ?> avaliações</span>
            </div>
            <div class="profile-stat">
              <span class="stat-value"><?= (int) $trainer['sessions'] ?>+</span>
              <span class="stat-label">Sessões realizadas</span>
            </div>
          </div>

          <p class="trainer-profile-bio"><?= htmlspecialchars($trainer['bio']) ?></p>

          <div class="trainer-profile-actions">
            <a href="classes.php?trainer_id=<?= (int)$trainer['id'] ?>" class="btn btn-primary">Ver Aulas Disponíveis</a>
            <?php if (isLoggedIn()): ?>
              <a href="#review-form" class="btn btn-outline">Avaliar Treinador</a>
            <?php else: ?>
              <a href="login.php" class="btn btn-outline">Entrar para Avaliar</a>
            <?php endif; ?>
          </div>
        </div>

      </div>
    </section>

    <section class="trainer-details-section">
      <div class="container trainer-details-grid">

        <div class="trainer-detail-block">
          <h2>Certificações</h2>
          <?php if ($trainer['certifications'] !== []): ?>
            <ul class="certifications-list">
              <?php foreach ($trainer['certifications'] as $cert): ?>
                <li>
                  <span class="cert-icon" aria-hidden="true">&#10003;</span>
                  <?= htmlspecialchars($cert) ?>
                </li>
              <?php endforeach; ?>
            </ul>
          <?php else: ?>
            <p class="empty-state">Sem certificações registadas.</p>
          <?php endif; ?>
        </div>

        <div class="trainer-detail-block">
          <h2>Aulas Esta Semana</h2>
          <?php if ($trainer['classes'] !== []): ?>
            <ul class="trainer-classes-list">
              <?php foreach ($trainer['classes'] as $class): ?>
                <li class="trainer-class-item">
                  <div class="trainer-class-info">
                    <h3><?= htmlspecialchars($class['name']) ?></h3>
                    <p><?= htmlspecialchars($class['time']) ?> · <?= htmlspecialchars($class['duration']) ?></p>
                  </div>
                  <div class="trainer-class-spots">
                    <span class="badge <?= $class['spots'] <= 3 ? 'badge-danger' : 'badge-green' ?>">
                      <?= (int) $class['spots'] ?> vagas
                    </span>
                    <a href="class.php?id=<?= (int) $class['id'] ?>" class="btn btn-outline btn-sm">Ver</a>
                  </div>
                </li>
              <?php endforeach; ?>
            </ul>
          <?php else: ?>
            <p class="empty-state">Ainda não existem aulas registadas para este treinador.</p>
          <?php endif; ?>
        </div>

      </div>
    </section>

    <section class="trainer-reviews-section" aria-labelledby="reviews-heading">
      <div class="container trainer-reviews-layout">
        <div>
          <h2 id="reviews-heading">O que dizem os membros</h2>

          <?php if ($reviews !== []): ?>
            <ul class="reviews-grid">
              <?php foreach ($reviews as $review): ?>
                <li class="review-card">
                  <div class="review-header">
                    <?php if (!empty($review['author_photo'])): ?>
                      <img src="<?= htmlspecialchars($review['author_photo']) ?>" alt="<?= htmlspecialchars($review['author']) ?>" class="review-avatar review-avatar-img" />
                    <?php else: ?>
                      <div class="review-avatar" aria-hidden="true"><?= htmlspecialchars($review['author_initials']) ?></div>
                    <?php endif; ?>
                    <div>
                      <p class="review-author"><?= htmlspecialchars($review['author']) ?></p>
                      <p class="review-stars" aria-label="<?= (int) $review['rating'] ?> em 5 estrelas">
                        <?php for ($i = 0; $i < 5; $i++): ?><?= $i < (int) $review['rating'] ? '&#9733;' : '&#9734;' ?><?php endfor; ?>
                      </p>
                    </div>
                  </div>
                  <p class="review-text"><?= htmlspecialchars($review['comment']) ?></p>
                </li>
              <?php endforeach; ?>
            </ul>
          <?php else: ?>
            <p class="empty-state">Ainda não há avaliações. Sê a primeira pessoa a deixar feedback.</p>
          <?php endif; ?>
        </div>

        <aside class="review-form-card" id="review-form" aria-labelledby="review-form-heading">
          <h2 id="review-form-heading"><?= $existingReview ? 'Atualizar avaliação' : 'Avaliar treinador' ?></h2>

          <?php if ($errors !== []): ?>
            <div class="form-alert form-alert-error" role="alert">
              <?php foreach ($errors as $error): ?>
                <p><?= htmlspecialchars($error) ?></p>
              <?php endforeach; ?>
            </div>
          <?php endif; ?>

          <?php if ($success !== []): ?>
            <div class="form-alert form-alert-success" role="status">
              <?php foreach ($success as $message): ?>
                <p><?= htmlspecialchars($message) ?></p>
              <?php endforeach; ?>
            </div>
          <?php endif; ?>

          <?php if ($canReview): ?>
            <form action="../actions/action_review_trainer.php" method="POST" class="trainer-review-form">
              <?= csrfInput() ?>
              <input type="hidden" name="trainer_id" value="<?= (int) $trainer['id'] ?>" />

              <fieldset class="rating-fieldset">
                <legend>Rating</legend>
                <input type="hidden" name="rating" value="0" />
                <div class="star-rating" aria-label="Escolher avaliação de 1 a 5 estrelas">
                  <?php for ($rating = 5; $rating >= 1; $rating--): ?>
                    <input type="radio" id="rating-<?= $rating ?>" name="rating" value="<?= $rating ?>" <?= (string) $old['rating'] === (string) $rating ? 'checked' : '' ?> />
                    <label for="rating-<?= $rating ?>" aria-label="<?= $rating ?> estrelas">&#9733;</label>
                  <?php endfor; ?>
                </div>
              </fieldset>

              <div class="form-group">
                <label for="comment">Comentário</label>
                <textarea id="comment" name="comment" maxlength="700" placeholder="Como foi a tua experiência com este treinador?" required><?= htmlspecialchars((string) $old['comment']) ?></textarea>
              </div>

              <button type="submit" class="btn btn-primary btn-full">Publicar avaliação</button>
            </form>
          <?php elseif (isLoggedIn()): ?>
            <p class="empty-state">Só contas de membro podem avaliar treinadores.</p>
          <?php else: ?>
            <a href="login.php" class="btn btn-primary btn-full">Entrar para avaliar</a>
          <?php endif; ?>
        </aside>
      </div>
    </section>

  </main>

<?php require __DIR__ . '/../templates/footer.php'; ?>
