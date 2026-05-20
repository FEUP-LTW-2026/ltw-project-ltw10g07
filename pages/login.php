<?php
$pageTitle = 'Entrar - LiftToBig';
$pageStyles = ['../css/login.css'];
$activePage = 'login';
require __DIR__ . '/../templates/header.php';
require __DIR__ . '/db.php';
?>

  <main>
    <section class="auth" aria-labelledby="auth-heading">
      <div class="container auth-inner">
  
        <div class="auth-content">
          <h1 id="auth-heading">Bem-vindo de volta!</h1>
          <p>Entra na tua conta para veres o teu plano de treinos.</p>
        </div>

        <div class="auth-form-wrapper">
          <form action="#" method="POST" class="auth-form">
            <div class="form-group">
              <label for="email">Email</label>
              <input type="email" id="email" name="email" placeholder="exemplo@email.com" required />
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
