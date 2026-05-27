<?php
declare(strict_types=1);

function drawFlashMessages(array $errors = [], array $success = []): void { ?>
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
<?php }
