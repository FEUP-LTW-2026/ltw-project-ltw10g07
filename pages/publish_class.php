<?php
require_once __DIR__ . '/../utils/session.php';
require_once __DIR__ . '/../templates/flash_messages.php';

if (!isLoggedIn()) {
  header('Location: login.php');
  exit;
}

$userRole = getUserRole();
$canPublishClass = $userRole === 'trainer' || $userRole === 'admin';
$errors = getFlashMessages('errors');
$success = getFlashMessages('success');

$pageTitle = 'Publicar Aula - LiftToBig';
$pageStyles = ['../css/publish_class.css'];
$activePage = 'publish-class';
require __DIR__ . '/../templates/header.php';
?>

  <main>
    <section class="publish-class" aria-labelledby="publish-class-heading">
      <div class="container publish-class-inner">

        <div class="publish-class-heading">
          
          <h1 id="publish-class-heading">Publicar nova aula</h1>
        
            
          

          <div class="publish-class-summary" aria-label="Resumo do processo">
            <div>
              <strong>1</strong>
              <span>Detalhes da aula</span>
            </div>
            <div>
              <strong>2</strong>
              <span>Horário e sala</span>
            </div>
            <div>
              <strong>3</strong>
              <span>Publicação</span>
            </div>
          </div>
        </div>

        <div class="publish-class-panel">
          <?php drawFlashMessages($errors, $success); ?>

          <?php if (!$canPublishClass): ?>
            <div class="form-alert form-alert-error" role="alert">
              <p>Esta página é reservada a treinadores. A validação final pode ser ligada na action.</p>
            </div>
          <?php endif; ?>

          <form action="../actions/action_create_class.php" method="POST" enctype="multipart/form-data" class="publish-class-form">
            <fieldset <?= !$canPublishClass ? 'disabled' : '' ?>>
              <legend>Dados da aula</legend>

              <div class="form-group">
                <label for="name">Nome da aula</label>
                <input type="text" id="name" name="name" placeholder="Nome da aula" required />
              </div>

              <div class="form-row">
                <div class="form-group">
                  <label for="type">Tipo</label>
                  <select id="type" name="type" required>
                    <option value="">Escolher tipo</option>
                    <option value="Cardio">Cardio</option>
                    <option value="Força">Força</option>
                    <option value="Yoga">Yoga</option>
                    <option value="Cycling">Cycling</option>
                    <option value="Boxe">Boxe</option>
                    <option value="Mobilidade">Mobilidade</option>
                  </select>
                </div>

                <div class="form-group">
                  <label for="intensity">Intensidade</label>
                  <select id="intensity" name="intensity" required>
                    <option value="">Escolher intensidade</option>
                    <option value="Baixa">Baixa</option>
                    <option value="Média">Média</option>
                    <option value="Alta">Alta</option>
                  </select>
                </div>
              </div>

              <div class="form-group">
                <label for="description">Descrição</label>
                <textarea id="description" name="description" placeholder="Explica o objetivo da aula, o nível recomendado e o que os membros podem esperar." required></textarea>
              </div>

              <div class="form-row">
                <div class="form-group">
                  <label for="duration">Duração em minutos</label>
                  <input type="number" id="duration" name="duration" min="15" max="180" step="5" placeholder="45" required />
                </div>

                <div class="form-group">
                  <label for="capacity">Capacidade</label>
                  <input type="number" id="capacity" name="capacity" min="1" max="80" placeholder="20" required />
                </div>
              </div>

              <div class="form-group">
                <label for="image">Imagem da aula</label>
                <input type="file" id="image" name="image" accept="image/*" />
              </div>
            </fieldset>

            <fieldset <?= !$canPublishClass ? 'disabled' : '' ?>>
              <legend>Primeira sessão</legend>

              <div class="form-row">
                <div class="form-group">
                  <label for="session_date">Data</label>
                  <input type="date" id="session_date" name="session_date" required />
                </div>

                <div class="form-group">
                  <label for="room">Sala</label>
                  <input type="text" id="room" name="room" placeholder="Ex: Studio 2" required />
                </div>
              </div>

              <div class="form-row">
                <div class="form-group">
                  <label for="start_time">Hora de início</label>
                  <input type="time" id="start_time" name="start_time" required />
                </div>

                <div class="form-group">
                  <label for="end_time">Hora de fim</label>
                  <input type="time" id="end_time" name="end_time" required />
                </div>
              </div>
            </fieldset>

            <div class="form-actions">
              <button type="submit" class="btn btn-primary" <?= !$canPublishClass ? 'disabled' : '' ?>>Publicar aula</button>
              <a href="classes.php" class="btn btn-ghost">Cancelar</a>
            </div>
          </form>
        </div>

      </div>
    </section>
  </main>

<?php require __DIR__ . '/../templates/footer.php'; ?>
