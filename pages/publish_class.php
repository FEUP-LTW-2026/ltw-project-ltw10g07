<?php
require_once __DIR__ . '/../database/connection.php';
require_once __DIR__ . '/../utils/session.php';
require_once __DIR__ . '/../utils/security.php';
require_once __DIR__ . '/../templates/flash_messages.php';

// Apenas Treinadores podem entrar aqui!
if (!isLoggedIn() || getUserRole() !== 'trainer') {
  header('Location: ../pages/classes.php');
  exit;
}

$errors = getFlashMessages('errors');
$success = getFlashMessages('success');

$db = getDB();

// Apanhar apenas as aulas atribuídas a este treinador
$stmt = $db->prepare('
    SELECT class.id, class.name 
    FROM class 
    JOIN trainer ON class.trainer_id = trainer.id 
    WHERE trainer.user_id = ?
    ORDER BY class.name ASC
');
$stmt->execute([getUserId()]);
$myClasses = $stmt->fetchAll(PDO::FETCH_ASSOC);

$pageTitle = 'Agendar Sessão - LiftToBig';
$pageStyles = ['../css/publish_class.css'];
$activePage = 'publish-class';
require __DIR__ . '/../templates/header.php';
?>

  <main>
    <section class="publish-class" aria-labelledby="publish-class-heading">
      <div class="container publish-class-inner">

        <div class="publish-class-heading">
          <h1 id="publish-class-heading">Agendar Sessão</h1>
          <p>Agenda um novo horário para as aulas que te foram atribuídas pelo Administrador.</p>
        </div>

        <div class="publish-class-panel">
          <?php drawFlashMessages($errors, $success); ?>

          <?php if (empty($myClasses)): ?>
            <div class="form-alert form-alert-error" role="alert">
              <p>Ainda não tens nenhuma aula atribuída. Contacta um Administrador para te associar a uma aula.</p>
            </div>
          <?php else: ?>

            <form action="../actions/action_create_session.php" method="POST" class="publish-class-form">
              <?= csrfInput() ?>
              <fieldset>
                <legend>Detalhes do Agendamento</legend>

                <div class="form-group">
                  <label for="class_id">Aula Disponível</label>
                  <select id="class_id" name="class_id" required>
                    <option value="">Seleciona a aula...</option>
                    <?php foreach ($myClasses as $c): ?>
                      <option value="<?= (int)$c['id'] ?>"><?= htmlspecialchars($c['name']) ?></option>
                    <?php endforeach; ?>
                  </select>
                </div>

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
                <button type="submit" class="btn btn-primary">Agendar Sessão</button>
                <a href="classes.php" class="btn btn-ghost">Cancelar</a>
              </div>
            </form>

          <?php endif; ?>
        </div>

      </div>
    </section>
  </main>

<?php require __DIR__ . '/../templates/footer.php'; ?>