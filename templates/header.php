<?php
require_once __DIR__ . '/../utils/session.php';
require_once __DIR__ . '/../utils/security.php';

$pageTitle = $pageTitle ?? 'LiftToBig';
$pageStyles = $pageStyles ?? [];
$activePage = $activePage ?? '';
$isLoggedIn = isLoggedIn();
$userName = getUserName();
$userRole = getUserRole();

$navLinks = [
  'home' => ['label' => 'Home', 'href' => 'landingpage.php'],
  'classes' => ['label' => 'Aulas', 'href' => 'classes.php'],
  'trainers' => ['label' => 'Treinadores', 'href' => 'trainers.php'],
  'equipment' => ['label' => 'Equipamentos', 'href' => 'equipment.php'],
];
?>
<!DOCTYPE html>
<html lang="pt">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title><?= htmlspecialchars($pageTitle) ?></title>
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet" />
  <link rel="stylesheet" href="../css/base.css" />
  <link rel="stylesheet" href="../css/components.css" />
  <?php foreach ($pageStyles as $style): ?>
    <link rel="stylesheet" href="<?= htmlspecialchars($style) ?>" />
  <?php endforeach; ?>
  <link rel="stylesheet" href="../css/responsive.css" />
</head>
<body>

  <header class="site-header">
    <input type="checkbox" id="menu-toggle" class="menu-checkbox" />
    <div class="container header-inner">

      <a href="landingpage.php" class="logo" aria-label="LiftToBig - pagina inicial">
        <img src="../img/logo.jpg" alt="" class="logo-image" aria-hidden="true" />
        LiftToBig
      </a>

      <label for="menu-toggle" class="nav-toggle">
        <span class="visually-hidden">Abrir/fechar menu</span>
      </label>

      <nav class="main-nav" aria-label="Navegacao principal">
        <ul class="nav-links">
          <?php foreach ($navLinks as $key => $link): ?>
            <li>
              <a href="<?= htmlspecialchars($link['href']) ?>" class="<?= $activePage === $key ? 'active' : '' ?>">
                <?= htmlspecialchars($link['label']) ?>
              </a>
            </li>
          <?php endforeach; ?>
        </ul>
      </nav>

      <div class="nav-actions">
        <?php if ($isLoggedIn): ?>
          <details class="account-menu">
            <summary class="account-menu-toggle" aria-label="Abrir menu da conta">
              <span class="account-menu-icon" aria-hidden="true"></span>
              <span class="nav-user">Olá, <?= htmlspecialchars($userName ?? 'membro') ?></span>
            </summary>
            <div class="account-menu-panel">
              <?php if ($userRole === 'trainer'): ?>
                <a href="publish_class.php" class="<?= $activePage === 'publish-class' ? 'active' : '' ?>">Publicar aula</a>
              <?php endif; ?>
              <?php if ($userRole === 'trainer'): ?>
                <a href="trainer_dashboard.php" class="<?= $activePage === 'trainer-dashboard' ? 'active' : '' ?>">Painel treinador</a>
                <a href="trainer_profile_edit.php" class="<?= $activePage === 'trainer-profile-edit' ? 'active' : '' ?>">Perfil treinador</a>
              <?php endif; ?>
              <?php if ($userRole === 'admin'): ?>
                <a href="admin.php" class="<?= $activePage === 'admin' ? 'active' : '' ?>">Admin</a>
              <?php endif; ?>
              <?php if ($userRole !== 'admin'): ?>
                <a href="account.php" class="<?= $activePage === 'account' ? 'active' : '' ?>">Editar conta</a>
              <?php endif; ?>
              <form action="../actions/action_logout.php" method="POST" class="account-menu-form">
                <?= csrfInput() ?>
                <button type="submit">Sair</button>
              </form>
            </div>
          </details>
        <?php else: ?>
          <a href="login.php" class="nav-signin">Entrar</a>
          <a href="register.php" class="btn btn-primary btn-sm">Join Now</a>
        <?php endif; ?>
      </div>

    </div>
  </header>
