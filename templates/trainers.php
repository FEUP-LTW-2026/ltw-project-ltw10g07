<?php
declare(strict_types=1);

function drawTrainerPreviewCard(array $trainer): void { ?>
  <li class="trainer-card">
    <?php if (!empty($trainer['photo'])): ?>
      <img src="<?= htmlspecialchars($trainer['photo']) ?>" alt="<?= htmlspecialchars($trainer['name']) ?>" class="avatar" />
    <?php else: ?>
      <span class="avatar avatar-initials" aria-hidden="true"><?= htmlspecialchars($trainer['initials']) ?></span>
    <?php endif; ?>
    <div class="trainer-info">
      <h3><?= htmlspecialchars($trainer['name']) ?></h3>
      <p class="trainer-specialty"><?= htmlspecialchars($trainer['specialty']) ?></p>
      <p class="top-rated-tag">&#9733; Top Rated (<?= number_format((float) $trainer['rating'], 1) ?>)</p>
    </div>
  </li>
<?php }

function drawTrainerCard(array $trainer): void { ?>
  <li class="trainer-card-full">
    <div class="trainer-photo-wrap">
      <?php if (!empty($trainer['photo'])): ?>
        <img src="<?= htmlspecialchars($trainer['photo']) ?>" alt="<?= htmlspecialchars($trainer['name']) ?>" class="trainer-photo" />
      <?php else: ?>
        <span class="trainer-photo trainer-photo-initials" aria-hidden="true"><?= htmlspecialchars($trainer['initials']) ?></span>
      <?php endif; ?>
    </div>
    <div class="trainer-body">
      <div class="trainer-header">
        <div>
          <h3><?= htmlspecialchars($trainer['name']) ?></h3>
          <p class="trainer-specialty"><?= htmlspecialchars($trainer['specialty']) ?></p>
        </div>
        <span class="trainer-rating">&#9733; <?= number_format((float) $trainer['rating'], 1) ?></span>
      </div>

      <p class="trainer-bio"><?= htmlspecialchars($trainer['bio']) ?></p>

      <div class="trainer-badges">
        <?php foreach ($trainer['badges'] as $badge): ?>
          <span class="badge badge-blue"><?= htmlspecialchars($badge) ?></span>
        <?php endforeach; ?>
      </div>

      <div class="trainer-stats">
        <div class="trainer-stat">
          <span class="stat-value"><?= (int) $trainer['sessions'] ?>+</span>
          <span class="stat-label">Sessões</span>
        </div>
        <div class="trainer-stat">
          <span class="stat-value">&#9733; <?= number_format((float) $trainer['rating'], 1) ?></span>
          <span class="stat-label">Avaliação</span>
        </div>
      </div>

      <div class="trainer-actions">
        <a href="trainer.php?id=<?= (int) $trainer['id'] ?>" class="btn btn-outline btn-sm">Ver Perfil</a>
        <a href="classes.php?trainer_id=<?= (int) $trainer['id'] ?>" class="btn btn-primary btn-sm">Ver Aulas</a>
      </div>
    </div>
  </li>
<?php }
