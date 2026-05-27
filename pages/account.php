<?php
require_once __DIR__ . '/../database/connection.php';
require_once __DIR__ . '/../database/User.php';
require_once __DIR__ . '/../utils/session.php';
require_once __DIR__ . '/../templates/flash_messages.php';

if (!isLoggedIn()) {
  header('Location: login.php');
  exit;
}

$db = getDB();
$user = User::findById($db, getUserId());

if ($user === null) {
  logoutUser();
  header('Location: login.php');
  exit;
}

$old = getOldInput([
  'name' => $user['name'],
  'username' => $user['username'],
  'email' => $user['email'],
]);
$errors = getFlashMessages('errors');
$success = getFlashMessages('success');
$userInitial = function_exists('mb_substr')
  ? mb_strtoupper(mb_substr($user['name'], 0, 1))
  : strtoupper(substr($user['name'], 0, 1));

$pageTitle = 'Editar Conta - LiftToBig';
$pageStyles = ['../css/account.css'];
$activePage = 'account';
require __DIR__ . '/../templates/header.php';
?>

  <main>
    <section class="account" aria-labelledby="account-heading">
      <div class="container account-inner">

        <!-- Coluna esquerda -->
        <div class="account-heading">
          <h1 id="account-heading">A tua conta</h1>
          <p>Atualiza os teus dados pessoais, foto de perfil e palavra-passe.</p>

          <div class="account-avatar-wrap">
            <?php if (!empty($user['photo'])): ?>
              <img
                src="<?= htmlspecialchars($user['photo']) ?>"
                alt="Foto de perfil de <?= htmlspecialchars($user['name']) ?>"
                class="account-avatar"
                id="avatar-preview"
              />
            <?php else: ?>
              <div class="account-avatar account-avatar-placeholder" id="avatar-preview" aria-hidden="true">
                <?= htmlspecialchars($userInitial) ?>
              </div>
            <?php endif; ?>

            <form
              action="../actions/action_upload_photo.php"
              method="POST"
              enctype="multipart/form-data"
              class="avatar-upload-form"
              id="avatar-upload-form"
            >
              <?= csrfInput() ?>
              <label for="photo" class="btn btn-outline btn-sm avatar-upload-btn" title="Alterar foto de perfil">
                Alterar foto
              </label>
              <input
                type="file"
                id="photo"
                name="photo"
                accept="image/jpeg,image/png,image/webp"
                class="visually-hidden"
                aria-label="Carregar nova foto de perfil"
              />
              <p class="avatar-upload-hint">JPEG, PNG ou WebP · máx. 2 MB</p>
            </form>
          </div>
        </div>

        <div class="account-panel">
          <?php drawFlashMessages($errors, $success); ?>

          <form action="../actions/action_update_account.php" method="POST" class="account-form">
            <?= csrfInput() ?>
            <div class="form-group">
              <label for="name">Nome</label>
              <input type="text" id="name" name="name" value="<?= htmlspecialchars($old['name']) ?>" required />
            </div>

            <div class="form-group">
              <label for="username">Username</label>
              <input type="text" id="username" name="username" value="<?= htmlspecialchars($old['username']) ?>" required />
            </div>

            <div class="form-group">
              <label for="email">Email</label>
              <input type="email" id="email" name="email" value="<?= htmlspecialchars($old['email']) ?>" required />
            </div>

            <div class="account-divider"></div>

            <div class="form-group">
              <label for="current_password">Palavra-passe atual</label>
              <input type="password" id="current_password" name="current_password" placeholder="Obrigatória apenas para mudar a password" />
            </div>

            <div class="form-row">
              <div class="form-group">
                <label for="new_password">Nova palavra-passe</label>
                <input type="password" id="new_password" name="new_password" placeholder="Mínimo 8 caracteres" />
              </div>

              <div class="form-group">
                <label for="new_password_confirm">Confirmar nova palavra-passe</label>
                <input type="password" id="new_password_confirm" name="new_password_confirm" placeholder="Repete a nova password" />
              </div>
            </div>

            <div class="form-actions">
              <button type="submit" class="btn btn-primary">Guardar alterações</button>
              <a href="landingpage.php" class="btn btn-ghost">Cancelar</a>
            </div>
          </form>
        </div>

      </div>
    </section>
  </main>
<script>
  document.getElementById('photo').addEventListener('change', function () {
    const file = this.files[0];
    if (!file) return;
    if (file.size > 2 * 1024 * 1024) {
      alert('O ficheiro é demasiado grande (máx. 2 MB).');
      this.value = '';
      return;
    }
    document.getElementById('avatar-upload-form').submit();
  });
</script>

<?php require __DIR__ . '/../templates/footer.php'; ?>
