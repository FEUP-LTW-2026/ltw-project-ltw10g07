<?php
require_once __DIR__ . '/../database/connection.php';
require_once __DIR__ . '/../database/User.php';
require_once __DIR__ . '/../utils/session.php';

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

$pageTitle = 'Editar Conta - LiftToBig';
$pageStyles = ['../css/account.css'];
$activePage = 'account';
require __DIR__ . '/../templates/header.php';
?>

  <main>
    <section class="account" aria-labelledby="account-heading">
      <div class="container account-inner">

        <div class="account-heading">
          <h1 id="account-heading">Editar detalhes da conta</h1>
          <p>Atualiza os teus dados pessoais e, se quiseres, define uma nova palavra-passe.</p>
        </div>

        <div class="account-panel">
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

          <form action="../actions/action_update_account.php" method="POST" class="account-form">
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

<?php require __DIR__ . '/../templates/footer.php'; ?>
