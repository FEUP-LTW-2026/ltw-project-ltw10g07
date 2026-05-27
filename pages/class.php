<?php
declare(strict_types=1);

require_once __DIR__ . '/../utils/session.php';
require_once __DIR__ . '/../database/connection.php';

// Inicializar mensagens de feedback que possam vir das actions
$errors = getFlashMessages('errors');
$success = getFlashMessages('success');

$db = getDB();

// Obter ID da aula da URL, default 1
$class_id = isset($_GET['id']) ? (int) $_GET['id'] : 1;

// 1. Procurar os dados reais da aula na base de dados
$stmt = $db->prepare('
    SELECT 
        class.id,
        class.name,
        class.description,
        class.type,
        class.intensity,
        class.duration,
        class.capacity,
        class.image,
        trainer.id AS trainer_id,
        user.name AS trainer_name,
        user.photo AS trainer_photo
    FROM class
    LEFT JOIN trainer ON trainer.id = class.trainer_id
    LEFT JOIN user ON user.id = trainer.user_id
    WHERE class.id = ?
');
$stmt->execute([$class_id]);
$classData = $stmt->fetch(PDO::FETCH_ASSOC);

// [CORREÇÃO CRUCIAL]: Se a aula não existir de todo na BD, criamos um array seguro temporário
if ($classData === false) {
    $classData = [
        'id' => $class_id,
        'name' => 'Aula Não Encontrada',
        'description' => 'Esta aula ainda não foi configurada ou criada na base de dados pelo Administrador.',
        'type' => 'Geral',
        'intensity' => 'Média',
        'duration' => 45,
        'capacity' => 20,
        'image' => '../img/hiit.jpg',
        'trainer_id' => null,
        'trainer_name' => 'Treinador Disponível',
        'trainer_photo' => null
    ];
}

// 2. Mapeamento dinâmico dos textos originais dos teus colegas
$className = $classData['name'];
$classType = $classData['type'];

// Valores padrão
$photo = $classData['image'] ?: '../img/hiit.jpg';
$category = $classType;
$intensity = $classData['intensity'] ?? 'Média';
$duration = ($classData['duration'] ?? 45) . ' min';
$description = $classData['description'];
$what_to_expect = ['Aquecimento geral guiado', 'Bloco de exercícios principal', 'Arrefecimento e alongamentos'];
$requirements = 'Trazer roupa confortável de treino, toalha e água.';
$calories = '300–450 kcal';

// Comparar para carregar os mock data originais correspondentes de forma inteligente
if (str_contains(strtolower($className), 'hiit')) {
    $photo = '../img/hiit.jpg';
    $category = 'Cardio';
    $what_to_expect = [
        'Aquecimento dinâmico de 5 minutos',
        'Blocos de trabalho de 40s com 20s de recuperação',
        'Exercícios de força e cardio alternados',
        'Arrefecimento e alongamentos finais',
    ];
    $requirements = 'Nível intermédio recomendado. Trazer garrafa de água e toalha.';
    $calories = '400–600 kcal';
} elseif (str_contains(strtolower($className), 'yoga')) {
    $photo = '../img/yoga.jpg';
    $category = 'Yoga';
    $what_to_expect = [
        'Respiração e meditação de abertura (5 min)',
        'Sequência de aquecimento Surya Namaskar',
        'Fluxo de posturas em pé e equilíbrio',
        'Posturas de chão e relaxamento final (Savasana)',
    ];
    $requirements = 'Todos os níveis bem-vindos. Trazer tapete (disponível para aluguer no ginásio).';
    $calories = '150–250 kcal';
} elseif (str_contains(strtolower($className), 'barbell') || str_contains(strtolower($className), 'força')) {
    $photo = '../img/barbell.jpg';
    $category = 'Força';
    $what_to_expect = [
        'Mobilidade articular e ativação muscular',
        'Trabalho técnico nos movimentos principais',
        'Séries de hipertrofia e força',
        'Arrefecimento com foam rolling',
    ];
    $requirements = 'Nível intermédio/avançado. Recomendado calçado de levantamento.';
    $calories = '300–450 kcal';
} elseif (str_contains(strtolower($className), 'cycle') || str_contains(strtolower($className), 'cycling')) {
    $photo = '../img/cycling.jpg';
    $category = 'Cycling';
    $what_to_expect = [
        'Ajuste da bicicleta e aquecimento progressivo',
        'Blocos de cadência com subidas e sprints',
        'Trabalho cardiovascular de intensidade média',
        'Retorno à calma com mobilidade leve',
    ];
    $requirements = 'Todos os níveis. Trazer água e toalha. Chegar 5 minutos antes para ajustar a bicicleta.';
    $calories = '350–550 kcal';
} elseif (str_contains(strtolower($className), 'mobility') || str_contains(strtolower($className), 'core') || str_contains(strtolower($className), 'flexibilidade')) {
    $photo = '../img/mobility.jpg';
    $category = 'Flexibilidade';
    $what_to_expect = [
        'Ativação respiratória e mobilidade inicial',
        'Exercícios de core anti-rotação e estabilidade',
        'Sequências de mobilidade para ancas, ombros e coluna',
        'Alongamentos finais guiados',
    ];
    $requirements = 'Todos os níveis. Recomendado para quem passa muito tempo sentado ou treina força regularmente.';
    $calories = '120–220 kcal';
} elseif (str_contains(strtolower($className), 'boxing') || str_contains(strtolower($className), 'boxe')) {
    $photo = '../img/boxing.jpg';
    $category = 'Boxe';
    $what_to_expect = [
        'Aquecimento com corda e footwork',
        'Técnica de jab, direto, esquivas e combinações',
        'Rounds no saco com foco em ritmo e potência',
        'Condicionamento final e alongamentos',
    ];
    $requirements = 'Nível iniciante/intermédio. Luvas disponíveis no ginásio, mas podes trazer as tuas.';
    $calories = '450–650 kcal';
}

// Configurar as badges de estilo CSS originais
$intensity_badge = ($intensity === 'Alta') ? 'badge-amber' : (($intensity === 'Baixa') ? 'badge-green' : 'badge-blue');
$category_badge  = ($category === 'Flexibilidade') ? 'badge-green' : 'badge-blue';

// 3. Procurar as SESSÕES REAIS desta aula na base de dados
$stmt = $db->prepare('
    SELECT id, session_date, start_time, room 
    FROM class_session 
    WHERE class_id = ? 
    ORDER BY session_date ASC, start_time ASC
');
$stmt->execute([(int)$classData['id']]);
$realSessions = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Selecionar a primeira sessão encontrada como a "principal" para o topo da página (Hero)
$mainSession = $realSessions[0] ?? null;
$main_session_id = $mainSession ? (int)$mainSession['id'] : 0;

// 4. Calcular ocupação real da sessão principal com base na tabela enrollment
$currentEnrollments = 0;
if ($main_session_id > 0) {
    $stmt = $db->prepare('SELECT COUNT(*) FROM enrollment WHERE session_id = ? AND status = \'active\'');
    $stmt->execute([$main_session_id]);
    $currentEnrollments = (int)$stmt->fetchColumn();
}

$spots_total = (int)$classData['capacity'];
$spots_left  = max(0, $spots_total - $currentEnrollments);
$occupancy_pct = ($spots_total > 0) ? (int)round((1 - $spots_left / $spots_total) * 100) : 0;

// 5. Verificar se o utilizador autenticado já está inscrito nesta sessão principal
$isEnrolledInMain = false;
if (isLoggedIn() && $main_session_id > 0) {
    $stmt = $db->prepare('SELECT id FROM enrollment WHERE user_id = ? AND session_id = ? AND status = \'active\'');
    $stmt->execute([getUserId(), $main_session_id]);
    if ($stmt->fetch()) {
        $isEnrolledInMain = true;
    }
}

// Set das variáveis obrigatórias para o header.php dos teus colegas
$pageTitle = htmlspecialchars($className) . ' - LiftToBig';
$pageStyles = ['../css/class.css'];
$activePage = 'classes';

require __DIR__ . '/../templates/header.php';
?>

  <main>

    <section class="class-hero" aria-labelledby="class-name-heading">
      <div class="container class-hero-inner">

        <div class="class-hero-image-wrap">
          <img
            src="<?= htmlspecialchars($photo) ?>"
            alt="<?= htmlspecialchars($className) ?>"
            class="class-hero-img"
          />
          <div class="class-hero-badges">
            <span class="badge <?= $category_badge ?>"><?= htmlspecialchars($category) ?></span>
            <span class="badge <?= $intensity_badge ?>"><?= htmlspecialchars($intensity) ?></span>
          </div>
        </div>

        <div class="class-hero-info">
          <a href="classes.php" class="back-link">&#8592; Todas as Aulas</a>

          <h1 id="class-name-heading"><?= htmlspecialchars($className) ?></h1>

          <div class="class-hero-meta">
            <div class="class-meta-item">
              <span class="meta-icon" aria-hidden="true">&#9202;</span>
              <span><?= htmlspecialchars($duration) ?></span>
            </div>
            <div class="class-meta-item">
              <span class="meta-icon" aria-hidden="true">&#128293;</span>
              <span>Intensidade: <?= htmlspecialchars($intensity) ?></span>
            </div>
            <div class="class-meta-item">
              <span class="meta-icon" aria-hidden="true">&#128293;</span>
              <span>~<?= htmlspecialchars($calories) ?></span>
            </div>
          </div>

          <p class="class-hero-description"><?= htmlspecialchars($description) ?></p>

          <?php if ($errors !== []): ?>
            <div class="form-alert form-alert-error" role="alert" style="margin-bottom: 1rem; padding: 0.5rem 1rem; background: #fee2e2; color: #991b1b; border-radius: 4px;">
              <?php foreach ($errors as $error): ?>
                <p><?= htmlspecialchars($error) ?></p>
              <?php endforeach; ?>
            </div>
          <?php endif; ?>

          <?php if ($success !== []): ?>
            <div class="form-alert form-alert-success" role="status" style="margin-bottom: 1rem; padding: 0.5rem 1rem; background: #dcfce7; color: #166534; border-radius: 4px;">
              <?php foreach ($success as $message): ?>
                <p><?= htmlspecialchars($message) ?></p>
              <?php endforeach; ?>
            </div>
          <?php endif; ?>

          <div class="occupancy-bar-wrap" aria-label="Ocupação da aula: <?= $occupancy_pct ?>%">
            <div class="occupancy-bar-header">
              <span><?= $spots_left ?> vagas disponíveis</span>
              <span><?= $occupancy_pct ?>% ocupado</span>
            </div>
            <div class="occupancy-bar" role="progressbar" aria-valuenow="<?= $occupancy_pct ?>" aria-valuemin="0" aria-valuemax="100">
              <div class="occupancy-fill" style="width: <?= $occupancy_pct ?>%"></div>
            </div>
          </div>

          <?php if (isLoggedIn() && in_array(getUserRole(), ['trainer', 'admin'])): ?>
            <div style="margin-top: 1.5rem; margin-bottom: 1.5rem; padding: 1rem; background: #f8fafc; border: 1px dashed #cbd5e1; border-radius: 8px;">
                <h3 style="font-size: 0.95rem; margin-bottom: 0.75rem; color: #475569; text-transform: uppercase; letter-spacing: 0.5px;">⚙️ Painel do Treinador</h3>
                <div style="display: flex; gap: 0.5rem;">
                    <a href="edit_class.php?id=<?= (int)$classData['id'] ?>" class="btn btn-outline btn-sm" style="flex: 1; text-align: center;">Editar Aula</a>
                    
                    <form action="../actions/action_delete_class.php" method="POST" style="flex: 1; margin: 0;" onsubmit="return confirm('⚠️ ATENÇÃO: Tens a certeza absoluta que queres eliminar esta aula e apagar todas as inscrições dos alunos? Esta ação não pode ser desfeita!');">
                        <input type="hidden" name="class_id" value="<?= (int)$classData['id'] ?>">
                        <button type="submit" class="btn btn-danger btn-sm" style="width: 100%; background: #dc2626; border: none; color: white; cursor: pointer;">Eliminar Aula</button>
                    </form>
                </div>
            </div>
          <?php endif; ?>

          <?php if ($main_session_id > 0): ?>
            <form action="../actions/action_enroll.php" method="POST">
              <input type="hidden" name="session_id" value="<?= $main_session_id ?>" />
              <input type="hidden" name="class_id" value="<?= (int)$classData['id'] ?>" />
              
              <?php if ($isEnrolledInMain): ?>
                <input type="hidden" name="action" value="cancel" />
                <button type="submit" class="btn btn-danger btn-lg" style="background: #dc2626; border-color: #dc2626; color: white; cursor: pointer;">Cancelar Minha Inscrição</button>
              <?php else: ?>
                <input type="hidden" name="action" value="enroll" />
                <button type="submit" class="btn btn-primary btn-lg" <?= $spots_left === 0 ? 'disabled' : '' ?> style="cursor: pointer;">
                  <?= $spots_left === 0 ? 'Aula Esgotada' : 'Inscrever-me Nesta Aula' ?>
                </button>
              <?php endif; ?>
            </form>
          <?php else: ?>
            <button class="btn btn-ghost btn-lg" disabled style="opacity: 0.6;">Sem sessões agendadas</button>
          <?php endif; ?>
        </div>

      </div>
    </section>

    <section class="class-details-section">
      <div class="container class-details-grid">

        <div class="class-detail-block">
          <h2>O que podes esperar</h2>
          <ul class="expect-list">
            <?php foreach ($what_to_expect as $item): ?>
              <li>
                <span class="expect-icon" aria-hidden="true">&#9654;</span>
                <?= htmlspecialchars($item) ?>
              </li>
            <?php endforeach; ?>
          </ul>

          <div class="requirements-box">
            <h3>&#8505; Requisitos</h3>
            <p><?= htmlspecialchars($requirements) ?></p>
          </div>
        </div>

        <div class="class-detail-block">
          <h2>O Teu Treinador</h2>
          <div class="class-trainer-card">
            <?php if (!empty($classData['trainer_photo'])): ?>
              <img
                src="<?= htmlspecialchars($classData['trainer_photo']) ?>"
                alt="<?= htmlspecialchars($classData['trainer_name'] ?? 'Treinador') ?>"
                class="avatar"
              />
            <?php else: ?>
              <div class="avatar" style="display:inline-flex; align-items:center; justify-content:center; background:#4f46e5; color:white; font-weight:bold;">PT</div>
            <?php endif; ?>
            <div>
              <h3><?= htmlspecialchars($classData['trainer_name'] ?? 'Treinador Disponível') ?></h3>
              <?php if (!empty($classData['trainer_id'])): ?>
                <a href="trainer.php?id=<?= (int)$classData['trainer_id'] ?>" class="btn btn-outline btn-sm" style="margin-top:.5rem">
                  Ver Perfil
                </a>
              <?php endif; ?>
            </div>
          </div>
        </div>

        <div class="class-detail-block">
          <h2>Próximas Sessões</h2>
          <?php if ($realSessions !== []): ?>
            <ul class="next-sessions-list">
              <?php foreach ($realSessions as $session): 
                $s_id = (int)$session['id'];
                
                // Contar inscrições ativas para ESTA sessão específica da lista
                $stmt = $db->prepare('SELECT COUNT(*) FROM enrollment WHERE session_id = ? AND status = \'active\'');
                $stmt->execute([$s_id]);
                $sessionEnrollments = (int)$stmt->fetchColumn();
                $session_spots_left = max(0, $spots_total - $sessionEnrollments);

                // Validar se o utilizador está pessoalmente inscrito nesta iteração
                $userEnrolledInThis = false;
                if (isLoggedIn()) {
                    $stmt = $db->prepare('SELECT id FROM enrollment WHERE user_id = ? AND session_id = ? AND status = \'active\'');
                    $stmt->execute([getUserId(), $s_id]);
                    if ($stmt->fetch()) {
                        $userEnrolledInThis = true;
                    }
                }

                // Traduzir datas estáticas ou formatar
                $displayDate = $session['session_date'];
                if ($displayDate === date('Y-m-d')) $displayDate = 'Hoje';
                elseif ($displayDate === date('Y-m-d', strtotime('+1 day'))) $displayDate = 'Amanhã';
              ?>
                <li class="next-session-item">
                  <div class="session-datetime">
                    <span class="session-date"><?= htmlspecialchars($displayDate) ?></span>
                    <span class="session-time"><?= htmlspecialchars(substr($session['start_time'], 0, 5)) ?></span>
                  </div>
                  <div class="session-spots">
                    <span class="badge <?= $session_spots_left <= 3 ? 'badge-danger' : ($session_spots_left <= 7 ? 'badge-amber' : 'badge-green') ?>">
                      <?= $session_spots_left ?> vagas
                    </span>
                    
                    <form action="../actions/action_enroll.php" method="POST" style="display:inline;">
                      <input type="hidden" name="session_id" value="<?= $s_id ?>" />
                      <input type="hidden" name="class_id" value="<?= (int)$classData['id'] ?>" />
                      
                      <?php if ($userEnrolledInThis): ?>
                        <input type="hidden" name="action" value="cancel" />
                        <button type="submit" class="btn btn-sm" style="background: #dc2626; color: white; border: none; padding: 4px 8px; border-radius: 4px; font-size: 0.85rem; cursor: pointer;">Cancelar</button>
                      <?php else: ?>
                        <input type="hidden" name="action" value="enroll" />
                        <button type="submit" class="btn btn-primary btn-sm" <?= $session_spots_left === 0 ? 'disabled' : '' ?> style="cursor: pointer;">
                          <?= $session_spots_left === 0 ? 'Cheio' : 'Inscrever' ?>
                        </button>
                      <?php endif; ?>
                    </form>
                  </div>
                </li>
              <?php endforeach; ?>
            </ul>
          <?php else: ?>
            <p style="color: var(--color-text-muted); font-size: 0.95rem;">Não existem mais sessões agendadas para esta aula.</p>
          <?php endif; ?>
        </div>

      </div>
    </section>

  </main>

<?php require __DIR__ . '/../templates/footer.php'; ?>