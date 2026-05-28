<?php
declare(strict_types=1);

require_once __DIR__ . '/../database/connection.php';
require_once __DIR__ . '/../database/Trainer.php';
require_once __DIR__ . '/../utils/session.php';

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

$stmt = $db->prepare('
    SELECT
      class.id AS class_id,
      class.name AS class_name,
      class.type,
      class.intensity,
      class.duration,
      class.capacity,
      class_session.id AS session_id,
      class_session.session_date,
      class_session.start_time,
      class_session.end_time,
      class_session.room,
      COUNT(enrollment.id) AS enrolled_count
    FROM class
    LEFT JOIN class_session ON class_session.class_id = class.id
    LEFT JOIN enrollment ON enrollment.session_id = class_session.id AND enrollment.status = \'active\'
    WHERE class.trainer_id = ?
      AND (
        class_session.id IS NULL
        OR datetime(class_session.session_date || " " || class_session.end_time) >= datetime("now", "-5 hours")
      )
    GROUP BY
      class.id,
      class.name,
      class.type,
      class.intensity,
      class.duration,
      class.capacity,
      class_session.id,
      class_session.session_date,
      class_session.start_time,
      class_session.end_time,
      class_session.room
    ORDER BY
      class_session.session_date IS NULL ASC,
      class_session.session_date ASC,
      class_session.start_time ASC,
      class.name ASC
');
$stmt->execute([(int) $trainer['id']]);
$scheduleRows = $stmt->fetchAll(PDO::FETCH_ASSOC);

$sessionIds = array_values(array_filter(array_map(
  static fn (array $row): int => (int) ($row['session_id'] ?? 0),
  $scheduleRows
)));
$rosters = [];

if ($sessionIds !== []) {
  $placeholders = implode(',', array_fill(0, count($sessionIds), '?'));
  $rosterStmt = $db->prepare("
      SELECT
        enrollment.session_id,
        user.name,
        user.username,
        user.email
      FROM enrollment
      JOIN user ON user.id = enrollment.user_id
      WHERE enrollment.status = 'active'
        AND enrollment.session_id IN ($placeholders)
      ORDER BY user.name ASC
  ");
  $rosterStmt->execute($sessionIds);

  foreach ($rosterStmt->fetchAll(PDO::FETCH_ASSOC) as $member) {
    $rosters[(int) $member['session_id']][] = $member;
  }
}

$totalClasses = count(array_unique(array_map(
  static fn (array $row): int => (int) $row['class_id'],
  $scheduleRows
)));
$totalSessions = count($sessionIds);
$totalEnrollments = array_sum(array_map(
  static fn (array $row): int => (int) $row['enrolled_count'],
  $scheduleRows
));

$pageTitle = 'Painel do Treinador - LiftToBig';
$pageStyles = ['../css/trainer_dashboard.css'];
$activePage = 'trainer-dashboard';

require __DIR__ . '/../templates/header.php';
?>

  <main class="trainer-dashboard">
    <section class="dashboard-hero">
      <div class="container dashboard-hero-inner">
        <div>
          <p class="dashboard-eyebrow">Área do treinador</p>
          <h1>As tuas aulas e inscrições</h1>
          <p>Acompanha o teu horário atribuído e consulta a lista de membros inscritos em cada sessão.</p>
          <a href="trainer_profile_edit.php" class="btn btn-outline btn-sm dashboard-profile-link">Editar perfil público</a>
        </div>

        <div class="dashboard-stats" aria-label="Resumo do treinador">
          <div><strong><?= $totalClasses ?></strong><span>Aulas</span></div>
          <div><strong><?= $totalSessions ?></strong><span>Sessões</span></div>
          <div><strong><?= $totalEnrollments ?></strong><span>Inscrições</span></div>
        </div>
      </div>
    </section>

    <section class="dashboard-content">
      <div class="container">
        <?php if ($scheduleRows === []): ?>
          <div class="dashboard-empty">
            <h2>Ainda não tens aulas atribuídas.</h2>
            <p>Quando um admin te atribuir aulas, elas aparecem aqui com as sessões e inscrições.</p>
          </div>
        <?php else: ?>
          <div class="dashboard-list">
            <?php foreach ($scheduleRows as $row): ?>
              <?php
                $sessionId = (int) ($row['session_id'] ?? 0);
                $members = $rosters[$sessionId] ?? [];
                $spotsLeft = max(0, (int) $row['capacity'] - (int) $row['enrolled_count']);
              ?>
              <article class="dashboard-card">
                <header class="dashboard-card-header">
                  <div>
                    <span class="dashboard-pill"><?= htmlspecialchars($row['type']) ?></span>
                    <h2><?= htmlspecialchars($row['class_name']) ?></h2>
                    <p><?= (int) $row['duration'] ?> min · Intensidade <?= htmlspecialchars($row['intensity']) ?></p>
                  </div>
                  <a href="class.php?id=<?= (int) $row['class_id'] ?>" class="btn btn-outline btn-sm">Ver aula</a>
                </header>

                <div class="session-summary">
                  <?php if ($sessionId > 0): ?>
                    <div>
                      <span>Data</span>
                      <strong><?= htmlspecialchars($row['session_date']) ?></strong>
                    </div>
                    <div>
                      <span>Hora</span>
                      <strong><?= htmlspecialchars(substr($row['start_time'], 0, 5)) ?> - <?= htmlspecialchars(substr($row['end_time'], 0, 5)) ?></strong>
                    </div>
                    <div>
                      <span>Sala</span>
                      <strong><?= htmlspecialchars($row['room'] ?: 'Por definir') ?></strong>
                    </div>
                    <div>
                      <span>Vagas</span>
                      <strong><?= $spotsLeft ?> livres</strong>
                    </div>
                  <?php else: ?>
                    <p class="session-missing">Esta aula ainda não tem sessões marcadas.</p>
                  <?php endif; ?>
                </div>

                <?php if ($sessionId > 0): ?>
                  <div class="roster">
                    <h3>Inscritos nesta sessão</h3>
                    <?php if ($members === []): ?>
                      <p class="roster-empty">Ainda não há membros inscritos.</p>
                    <?php else: ?>
                      <ul>
                        <?php foreach ($members as $member): ?>
                          <li>
                            <div>
                              <strong><?= htmlspecialchars($member['name']) ?></strong>
                              <span>@<?= htmlspecialchars($member['username']) ?></span>
                            </div>
                            <a href="mailto:<?= htmlspecialchars($member['email']) ?>"><?= htmlspecialchars($member['email']) ?></a>
                          </li>
                        <?php endforeach; ?>
                      </ul>
                    <?php endif; ?>
                  </div>
                <?php endif; ?>
              </article>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>
      </div>
    </section>
  </main>

<?php require __DIR__ . '/../templates/footer.php'; ?>
