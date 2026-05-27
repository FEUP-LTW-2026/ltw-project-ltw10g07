<?php
require_once __DIR__ . '/../utils/session.php';
require_once __DIR__ . '/../templates/flash_messages.php';

$old = getOldInput(['login' => '']);
$errors = getFlashMessages('errors');
$successMessages = getFlashMessages('success');

$pageTitle = 'Entrar - LiftToBig';
$pageStyles = ['../css/login.css'];
$activePage = 'login';
require __DIR__ . '/../templates/header.php';
?>

  <main>
    <section class="auth" aria-labelledby="auth-heading">
      <div class="container auth-inner">
  
        <div class="auth-content">
          <h1 id="auth-heading">Bem-vindo de volta!</h1>
          <p>Entra na tua conta para veres o teu plano de treinos.</p>
        </div>

        <div class="auth-form-wrapper">
          <?php drawFlashMessages($errors, $successMessages); ?>

          <form action="../actions/action_login.php" method="POST" class="auth-form">
            <div class="form-group">
              <label for="login">Email ou username</label>
              <input type="text" id="login" name="login" placeholder="exemplo@email.com ou o_teu_username" value="<?= htmlspecialchars($old['login']) ?>" required />
            </div>

            <div class="form-group">
              <label for="password">Palavra-passe</label>
              <input type="password" id="password" name="password" placeholder="Insere a tua password" required />
            </div>

            <div class="form-actions">
              <button type="submit" class="btn btn-outline btn-full">Entrar na Conta</button>
            </div>
          </form>

          <div class="auth-footer">
            <p>Ainda não fazes parte da comunidade? <a href="register.php">Regista-te aqui</a></p>
          </div>
        </div>
      </div>
    </section>
  </main>

<?php require __DIR__ . '/../templates/footer.php'; ?>
