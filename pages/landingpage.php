<?php
$pageTitle = 'LiftToBig - Ginasio & Gestao Online';
$pageStyles = ['../css/landingpage.css'];
$activePage = 'home';
require_once __DIR__ . '/../database/connection.php';
require_once __DIR__ . '/../database/Trainer.php';
require_once __DIR__ . '/../templates/trainers.php';

$db = getDB();
$featuredTrainers = Trainer::getFeatured($db);

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
            <label for="hero-search-input" >Pesquisar treinadores</label>
            <span aria-hidden="true" style="color:var(--color-text-muted);font-size:.95rem">&#9906;</span>
            <input
              type="search"
              id="hero-search-input"
              name="q"
              placeholder="Pesquisar treinadores..."
            />
            <button type="submit" class="btn btn-primary btn-sm">Pesquisar</button>
          </form>

          <div class="hero-members">
            <div class="member-avatars" aria-hidden="true">
              <div class="member-avatar-placeholder">SC</div>
              <div class="member-avatar-placeholder">RM</div>
              <div class="member-avatar-placeholder">AL</div>
              <div class="member-avatar-placeholder">+</div>
            </div>
            <p><strong>2.400+</strong> membros inscritos este mês</p>
          </div>
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
              A zona de cardio está a 25% de ocupação.<br />
              Hora perfeita para treinar!
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
            <h2 id="classes-heading">Aulas em Destaque</h2>
            <p>Encontra a sessão perfeita para o teu nível de energia hoje.</p>
          </div>
          <a href="classes.php" class="link-more">Ver Horários &rsaquo;</a>
        </div>

        <ul class="classes-grid">
            <!--Power Hit-->
          <li class="class-card">
            <div class="class-img-wrap">
              
              <img src="../img/powerhit.jpg" alt="Aula de Power HIIT" />
              <div class="class-badge-img">
                <span class="badge badge-blue">Cardio</span>
              </div>
              <div class="class-name-overlay">
                <h3>Power HIIT</h3>
              </div>
            </div>

            <div class="class-info">
              <div class="class-meta-item">
                <span class="meta-icon" aria-hidden="true">&#9202;</span>
                <span>45m</span>
              </div>
              <div class="class-meta-item">
                <span class="meta-icon" aria-hidden="true">&#128293;</span>
                <span>Alta</span>
              </div>
              <a href="classes.php" class="btn btn-outline btn-sm" style="margin-left:auto">Inscrever</a>
            </div>
          </li>
          <!--Yoga-->
          <li class="class-card">
            <div class="class-img-wrap">
              <img src="../img/yoga.jpg" alt="yoga" />
              <div class="class-badge-img">
                <span class="badge badge-green">Yoga</span>
              </div>
              <div class="class-name-overlay">
                <h3>Yoga</h3>
              </div>
            </div>
            <div class="class-info">
              <div class="class-meta-item">
                <span class="meta-icon" aria-hidden="true">&#9202;</span>
                <span>60m</span>
              </div>
              <div class="class-meta-item">
                <span class="meta-icon" aria-hidden="true">&#127774;</span>
                <span>Média</span>
              </div>
              <a href="classes.php" class="btn btn-outline btn-sm" style="margin-left:auto">Inscrever</a>
            </div>
          </li>
          <!--Zumba -->
          <li class="class-card">
            <div class="class-img-wrap">
              <img src="../img/zumba.jpg" alt="zumba" />
              <div class="class-badge-img">
                <span class="badge badge-amber">Cardio</span>
              </div>
              <div class="class-name-overlay">
                <h3>Zumba</h3>
              </div>
            </div>
            <div class="class-info">
              <div class="class-meta-item">
                <span class="meta-icon" aria-hidden="true">&#9202;</span>
                <span>60m</span>
              </div>
              <div class="class-meta-item">
               <span class="meta-icon" aria-hidden="true">&#127774;</span>
                <span>Média</span>
              </div>
              <a href="classes.php" class="btn btn-outline btn-sm" style="margin-left:auto">Inscrever</a>
            </div>
          </li>

        </ul>
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

  </main>


<?php require __DIR__ . '/../templates/footer.php'; ?>
