<?php
require_once __DIR__ . '/../utils/session.php';

$old = [
  'first_name' => '',
  'last_name' => '',
  'username' => '',
  'email' => '',
  'phone' => '',
];
$old = getOldInput($old);
$errors = getFlashMessages('errors');

$pageTitle = 'Criar Conta - LiftToBig';
$pageStyles = ['../css/login.css'];
$activePage = 'register';
require __DIR__ . '/../templates/header.php';
?>

  <main>
    <section class="auth" aria-labelledby="auth-heading">
      <div class="container auth-inner">

        <div class="auth-content">
          <h1 id="auth-heading">Junta-te à Comunidade.</h1>
          <p>Cria a tua conta e começa hoje mesmo a transformar o teu corpo e a tua vida.</p>

        
        </div>

        <div class="auth-form-wrapper">
          <?php if ($errors !== []): ?>
            <div class="form-alert form-alert-error" role="alert">
              <?php foreach ($errors as $error): ?>
                <p><?= htmlspecialchars($error) ?></p>
              <?php endforeach; ?>
            </div>
          <?php endif; ?>

          <form action="../actions/action_register.php" method="POST" class="auth-form">

            <div class="form-row">
              <div class="form-group">
                <label for="first_name">Nome</label>
                <input type="text" id="first_name" name="first_name" placeholder="O teu nome" value="<?= htmlspecialchars($old['first_name']) ?>" required />
              </div>
              <div class="form-group">
                <label for="last_name">Apelido</label>
                <input type="text" id="last_name" name="last_name" placeholder="O teu apelido" value="<?= htmlspecialchars($old['last_name']) ?>" required />
              </div>
            </div>

            <div class="form-group">
              <label for="username">Username</label>
              <input type="text" id="username" name="username" placeholder="o_teu_username" value="<?= htmlspecialchars($old['username']) ?>" required />
            </div>

            <div class="form-group">
              <label for="email">Email</label>
              <input type="email" id="email" name="email" placeholder="exemplo@email.com" value="<?= htmlspecialchars($old['email']) ?>" required />
            </div>

            <div class="form-group">
              <label for="phone">Telemóvel (opcional)</label>
              <input type="tel" id="phone" name="phone" placeholder="+351 912 345 678" value="<?= htmlspecialchars($old['phone']) ?>" />
            </div>

            <div class="form-group">
              <label for="password">Palavra-passe</label>
              <input type="password" id="password" name="password" placeholder="Mínimo 8 caracteres" required />
            </div>

            <div class="form-group">
              <label for="password_confirm">Confirmar Palavra-passe</label>
              <input type="password" id="password_confirm" name="password_confirm" placeholder="Repete a tua password" required />
            </div>

            <div class="form-actions">
              <button type="submit" class="btn btn-primary btn-full">Criar Conta Gratuita</button>
            </div>
          </form>

          <div class="auth-footer">
            <p>Já tens conta? <a href="login.php">Entra aqui</a></p>
          </div>
        </div>

      </div>
    </section>
  </main>

<?php require __DIR__ . '/../templates/footer.php'; ?> 
