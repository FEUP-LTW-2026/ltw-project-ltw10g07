<?php
$pageTitle = 'LiftToBig - Ginasio & Gestao Online';
$pageStyles = ['../css/landingpage.css'];
$activePage = 'home';
require_once __DIR__ . '/../database/connection.php';
require_once __DIR__ . '/../database/ClassCatalog.php';
require_once __DIR__ . '/../database/Trainer.php';
require_once __DIR__ . '/../templates/trainers.php';

$db = getDB();
$featuredTrainers = Trainer::getFeatured($db);

// NOVA LÓGICA: Ir buscar as 3 próximas SESSÕES cronologicamente
$currentDate = date('Y-m-d');
$currentTime = date('H:i');

$stmt = $db->prepare('
    SELECT 
        c.id, c.name, c.type, c.intensity, c.duration, c.image,
        cs.id AS session_id, cs.session_date, cs.start_time
    FROM class_session cs
    JOIN class c ON c.id = cs.class_id
    WHERE cs.session_date > ? OR (cs.session_date = ? AND cs.start_time >= ?)
    ORDER BY cs.session_date ASC, cs.start_time ASC
    LIMIT 3
');
$stmt->execute([$currentDate, $currentDate, $currentTime]);
$upcomingSessions = $stmt->fetchAll(PDO::FETCH_ASSOC);

require __DIR__ . '/../templates/header.php';
?>


  <main>

    <!-- ============================================================
         HERO
         ============================================================ -->
    <section class="hero" aria-labelledby="hero-heading">
      <div class="container hero-inner">

        <!-- Esquerda -->
        <div class="hero-content">
          <p class="hero-eyebrow">&#9733; #1 Comunidade de Fitness em Portugal</p>

          <h1 id="hero-heading">
            Transforma-te na<br />
            <span>Melhor Versão</span><br />
            de Ti.
          </h1>

          <p>
            Desbloqueia o teu potencial com treinadores de excelência,
            equipamento de última geração e uma comunidade que evolui contigo.
          </p>
          <form class="hero-search" role="search" action="trainers.php" method="GET">
            <label for="hero-search-input" class="visually-hidden">Pesquisar treinadores</label>
            <span aria-hidden="true" style="color:var(--color-text-muted);font-size:.95rem">&#9906;</span>
            <input
              type="search"
              id="hero-search-input"
              name="q"
              placeholder="Pesquisar treinadores..."
            />
            <button type="submit" class="btn btn-primary btn-sm">Pesquisar</button>
          </form>

        </div>

        <!-- Imagem principal -->
        <div class="hero-image-wrap" aria-hidden="true">
         
            <img src="../img/hero-athlete.jpg" alt="Atleta a treinar no ginásio" class="hero-img" />
          

          <!-- Card flutuante -->
          <aside class="hero-float-card" aria-label="Estado atual do ginásio">
            <p class="float-card-header">
              <span class="float-card-dot"></span>
              Horas de Pico
            </p>
            <p class="float-card-body">
              50 passadeiras!<br />
              Todas as horas são perfeitas para treinar!
            </p>
          </aside>
        </div>

      </div>
    </section>


    <!-- ============================================================
         FEATURED CLASSES
         ============================================================ -->
    <section class="featured-classes" aria-labelledby="classes-heading">
      <div class="container">

        <div class="section-header">
          <div>
            <h2 id="classes-heading">Próximas Sessões</h2>
            <p>Junta-te a uma das nossas aulas que estão prestes a começar.</p>
          </div>
          <a href="classes.php" class="link-more">Ver Horário Completo &rsaquo;</a>
        </div>

        <?php if ($upcomingSessions !== []): ?>
          <ul class="classes-grid">
            <?php foreach ($upcomingSessions as $class): ?>
              <?php
                $badgeClass = $class['intensity'] === 'Alta'
                  ? 'badge-amber'
                  : ($class['intensity'] === 'Baixa' ? 'badge-green' : 'badge-blue');
              ?>
              <li class="class-card">
                <div class="class-img-wrap">
                  <?php if (!empty($class['image'])): ?>
                    <img src="<?= htmlspecialchars($class['image']) ?>" alt="<?= htmlspecialchars($class['name']) ?>" />
                  <?php else: ?>
                    <div class="class-image-placeholder" aria-hidden="true">
                      <?= htmlspecialchars(strtoupper(substr($class['name'], 0, 1))) ?>
                    </div>
                  <?php endif; ?>
                  <div class="class-badge-img">
                    <span class="badge <?= $badgeClass ?>"><?= htmlspecialchars($class['type']) ?></span>
                  </div>
                  <div class="class-name-overlay">
                    <h3><?= htmlspecialchars($class['name']) ?></h3>
                  </div>
                </div>

                <div class="class-info">
                  <!-- NOVA ADIÇÃO: Mostrar a data e a hora do lado esquerdo do cartão -->
                  <div style="display: flex; gap: 1rem; margin-bottom: 0.5rem;">
                    <div class="class-meta-item" style="color: #4f46e5; font-weight: bold;">
                      <span class="meta-icon" aria-hidden="true">📅</span>
                      <span><?= htmlspecialchars(substr($class['start_time'], 0, 5)) ?> (<?= htmlspecialchars(substr($class['session_date'], 5, 5)) ?>)</span>
                    </div>
                  </div>

                  <div style="display: flex; gap: 1rem; align-items: center; justify-content: space-between;">
                    <div style="display: flex; gap: 1rem;">
                      <div class="class-meta-item">
                        <span class="meta-icon" aria-hidden="true">&#9202;</span>
                        <span><?= (int) $class['duration'] ?>m</span>
                      </div>
                      <div class="class-meta-item">
                        <span class="meta-icon" aria-hidden="true">&#128293;</span>
                        <span><?= htmlspecialchars($class['intensity']) ?></span>
                      </div>
                    </div>
                    <!-- Botão de ver aula ajustado para levar ao session_id correto -->
                    <a href="class.php?id=<?= (int) $class['id'] ?>&session_id=<?= (int) $class['session_id'] ?>" class="btn btn-outline btn-sm">Inscrever</a>
                  </div>
                </div>
              </li>
            <?php endforeach; ?>
          </ul>
        <?php else: ?>
          <p>Neste momento, não existem sessões agendadas para os próximos dias.</p>
        <?php endif; ?>
      </div>
    </section>

    <!-- ============================================================
         TRAINERS
         ============================================================ -->
    <section class="trainers-section" aria-labelledby="trainers-heading">
      <div class="container trainers-inner">

        <!-- Texto à esquerda -->
        <div class="trainers-text">
          <p class="trainers-badge-label">&#9989; Orientação Especializada</p>
          <h2 id="trainers-heading">Conhece os Teus Futuros Mentores.</h2>
          <p>
            Os nossos personal trainers certificados estão dedicados ao teu sucesso.
            Obtém um plano personalizado adaptado aos teus objetivos.
          </p>
          <a href="trainers.php" class="btn btn-primary">Ver Todos os Treinadores</a>
        </div>

        <!-- Grid de trainers à direita -->
        <ul class="trainers-grid">
          <?php foreach ($featuredTrainers as $trainer): ?>
            <?php drawTrainerPreviewCard($trainer); ?>
          <?php endforeach; ?>
        </ul>

      </div>
    </section>


    <?php if (!isLoggedIn()): ?>
      <!-- ============================================================
           CTA FINAL
           ============================================================ -->
      <section class="cta-section" aria-labelledby="cta-heading">
        <div class="container">
          <h2 id="cta-heading">Pronto para Encontrar o Teu Ritmo?</h2>
          <p>
            Junta-te a mais de 15.000 membros que transformaram as suas vidas.
            Inscreve-te hoje e recebe a primeira consulta com personal trainer gratuita.
          </p>
          <div class="cta-buttons">
            <a href="register.php" class="btn btn-primary btn-lg">Juntar à Comunidade</a>
            <a href="classes.php" class="btn btn-ghost btn-lg">Ver Horários</a>
          </div>
        </div>
      </section>
    <?php endif; ?>

  </main>


<?php require __DIR__ . '/../templates/footer.php'; ?>