<?php
declare(strict_types=1);

require_once __DIR__ . '/../database/connection.php';
require_once __DIR__ . '/../database/Trainer.php';
require_once __DIR__ . '/../utils/session.php';
require_once __DIR__ . '/../templates/flash_messages.php';

if (!isLoggedIn() || getUserRole() !== 'trainer') {
  header('Location: login.php');
  exit;
}

$db = getDB();
$trainer = Trainer::findByUserId($db, (int) getUserId());

if ($trainer === null) {
  header('Location: account.php');
  exit;
}

$old = getOldInput([
  'bio' => $trainer['bio'] ?? '',
  'specializations' => $trainer['specializations'] ?? '',
  'certifications' => $trainer['certifications'] ?? '',
]);
$errors = getFlashMessages('errors');
$success = getFlashMessages('success');

$pageTitle = 'Editar Perfil de Treinador - LiftToBig';
$pageStyles = ['../css/account.css'];
$activePage = 'trainer-profile-edit';

require __DIR__ . '/../templates/header.php';
?>

  <main>
    <section class="account" aria-labelledby="trainer-profile-heading">
      <div class="container account-inner">

        <div class="account-heading">
          <h1 id="trainer-profile-heading">Editar perfil público</h1>
          <p>Atualiza a informação que os membros veem no teu perfil de treinador.</p>
        </div>

        <div class="account-panel">
          <?php drawFlashMessages($errors, $success); ?>

          <form action="../actions/action_update_trainer_profile.php" method="POST" class="account-form">
            <?= csrfInput() ?>
            <div class="form-group">
              <label for="bio">Bio</label>
              <textarea id="bio" name="bio" rows="6" maxlength="1000" placeholder="Resumo da tua experiência, abordagem e objetivos como treinador."><?= htmlspecialchars($old['bio']) ?></textarea>
            </div>

            <div class="form-group">
              <label for="specializations">Especializações</label>
              <input
                type="text"
                id="specializations"
                name="specializations"
                value="<?= htmlspecialchars($old['specializations']) ?>"
                maxlength="500"
                placeholder="Ex: Força, Mobilidade, HIIT"
              />
            </div>

            <div class="form-group">
              <label for="certifications">Certificações</label>
              <textarea id="certifications" name="certifications" rows="4" maxlength="700" placeholder="Separa certificações por vírgulas."><?= htmlspecialchars($old['certifications']) ?></textarea>
            </div>

            <div class="form-actions">
              <button type="submit" class="btn btn-primary">Guardar perfil</button>
              <a href="trainer_dashboard.php" class="btn btn-ghost">Voltar ao painel</a>
            </div>
          </form>
        </div>

      </div>
    </section>
  </main>

<?php require __DIR__ . '/../templates/footer.php'; ?>
