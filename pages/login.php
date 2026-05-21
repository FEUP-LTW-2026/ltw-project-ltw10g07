<?php
require_once __DIR__ . '/../utils/session.php';

$old = getOldInput(['email' => '']);
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
          <?php if ($errors !== []): ?>
            <div class="form-alert form-alert-error" role="alert">
              <?php foreach ($errors as $error): ?>
                <p><?= htmlspecialchars($error) ?></p>
              <?php endforeach; ?>
            </div>
          <?php endif; ?>

          <?php if ($successMessages !== []): ?>
            <div class="form-alert form-alert-success" role="status">
              <?php foreach ($successMessages as $message): ?>
                <p><?= htmlspecialchars($message) ?></p>
              <?php endforeach; ?>
            </div>
          <?php endif; ?>

          <form action="../actions/action_login.php" method="POST" class="auth-form">
            <div class="form-group">
              <label for="email">Email</label>
              <input type="email" id="email" name="email" placeholder="exemplo@email.com" value="<?= htmlspecialchars($old['email']) ?>" required />
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
