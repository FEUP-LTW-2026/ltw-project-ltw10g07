<?php
$pageTitle = 'Aulas - LiftToBig';
$pageStyles = ['../css/classes.css'];
$activePage = 'classes';
require __DIR__ . '/../templates/header.php';
?>

  <main>
    <section class="classes-hero">
      <div class="container">
        
        <div class="hero-eyebrow">★ EXPLORA O NOSSO CATÁLOGO</div>

        <h1 class="hero-title">Encontra o Teu <br><span>Ritmo</span>.</h1>
        <p class="hero-subtitle">Explora as nossas aulas, reserva com treinadores e atinge a tua melhor performance.</p>

        <div class="filters-wrapper">
          
          <div class="hero-search" role="search">
            <label for="hero-search-input">Pesquisar aulas ou treinadores</label>
            <span aria-hidden="true" style="color:var(--color-text-muted);font-size:.95rem">&#9906;</span>
            <input
              type="search"
              id="hero-search-input"
              name="q"
              placeholder="Pesquisar aulas ou treinadores..."
              />
            <button type="submit" class="btn btn-primary btn-sm">Pesquisar</button>
          </div>
          
          <div class="filters-row">
            <div class="filter-item">
              <label>Dia</label>
              <select>
                <option>Hoje</option>
                <option>Amanhã</option>
                <option>Esta Semana</option>
              </select>
            </div>
            
            <div class="filter-item">
              <label>Intensidade</label>
              <select>
                <option>Todas as Intensidades</option>
                <option>Alta</option>
                <option>Média</option>
                <option>Baixa</option>
              </select>
            </div>

            <div class="filter-item">
              <label>Treinador</label>
              <select>
                <option>Todos os Treinadores</option>
                <option>Rui Costa</option>
                <option>André Brás</option>
                <option>Tomás</option>
              </select>
            </div>

            <div class="filter-item">
              <label>Ordenar por</label>
              <select>
                <option>Próximas a começar</option>
                <option>Mais vagas</option>
              </select>
            </div>
          </div>

        </div>
      </div>
    </section>

    <section class="classes-grid-section">
      <div class="container">
        <h2 class="visually-hidden">Aulas disponíveis</h2>
        <ul class="classes-grid">
          
          <li class="class-card">
            <div class="class-img-wrap">
              <img src="../img/hiit.jpg" alt="HIIT Intensity Max" />
              <span class="badge badge-accent card-status">4 vagas</span>
            </div>
            <div class="class-card-body">
              <div class="class-card-tags">
                <span class="badge badge-blue">Cardio</span>
                <span class="badge badge-amber">Alta</span>
              </div>
              <h3>HIIT Intensity Max</h3>
              <p class="class-time">08:30 - 45 min</p>
              <div class="class-card-footer">
                <span class="trainer-name">Rui Costa</span>
                <a href="class.php?id=1" class="btn btn-outline btn-sm">Ver Detalhes</a>
              </div>
            </div>
          </li>

          <li class="class-card">
            <div class="class-img-wrap">
              <img src="../img/yoga.jpg" alt="Zen Yoga Flow" />
              <span class="badge badge-success card-status">12 vagas</span>
            </div>
            <div class="class-card-body">
              <div class="class-card-tags">
                <span class="badge badge-blue">Yoga</span>
                <span class="badge badge-green">Baixa</span>
              </div>
              <h3>Zen Yoga Flow</h3>
              <p class="class-time">10:00 - 60 min</p>
              <div class="class-card-footer">
                <span class="trainer-name">Tomás</span>
                <a href="class.php?id=2" class="btn btn-outline btn-sm">Ver Detalhes</a>
              </div>
            </div>
          </li>

          <li class="class-card">
            <div class="class-img-wrap">
              <img src="../img/cycling.jpg" alt="Cycle Rush 300" />
              <span class="badge badge-accent card-status">6 vagas</span>
            </div>
            <div class="class-card-body">
              <div class="class-card-tags">
                <span class="badge badge-blue">Cycling</span>
                <span class="badge badge-amber">Média</span>
              </div>
              <h3>Cycle Rush 300</h3>
              <p class="class-time">17:00 - 45 min</p>
              <div class="class-card-footer">
                <span class="trainer-name">André Brás</span>
                <a href="class.php?id=4" class="btn btn-outline btn-sm">Ver Detalhes</a>
              </div>
            </div>
          </li>

          <li class="class-card">
            <div class="class-img-wrap">
              <img src="../img/mobility.jpg" alt="Mobility & Core" />
              <span class="badge badge-success card-status">8 vagas</span>
            </div>
            <div class="class-card-body">
              <div class="class-card-tags">
                <span class="badge badge-green">Flexibilidade</span>
                <span class="badge badge-green">Baixa</span>
              </div>
              <h3>Mobility & Core</h3>
              <p class="class-time">18:30 - 40 min</p>
              <div class="class-card-footer">
                <span class="trainer-name">Tomás</span>
                <a href="class.php?id=5" class="btn btn-outline btn-sm">Ver Detalhes</a>
              </div>
            </div>
          </li>

          <li class="class-card">
            <div class="class-img-wrap">
              <img src="../img/barbell.jpg" alt="Power Barbell" />
              <span class="badge badge-danger card-status">2 vagas</span>
            </div>
            <div class="class-card-body">
              <div class="class-card-tags">
                <span class="badge badge-blue">Força</span>
                <span class="badge badge-amber">Alta</span>
              </div>
              <h3>Power Barbell</h3>
              <p class="class-time">12:15 - 50 min</p>
              <div class="class-card-footer">
                <span class="trainer-name">André Brás</span>
                <a href="class.php?id=3" class="btn btn-outline btn-sm">Ver Detalhes</a>
              </div>
            </div>
          </li>

          <li class="class-card">
            <div class="class-img-wrap">
              <img src="../img/boxing.jpg" alt="Boxing" />
              <span class="badge badge-accent card-status">5 vagas</span>
            </div>
            <div class="class-card-body">
              <div class="class-card-tags">
                <span class="badge badge-blue">Boxe</span>
                <span class="badge badge-amber">Alta</span>
              </div>
              <h3>Boxing Fundamentals</h3>
              <p class="class-time">19:00 - 60 min</p>
              <div class="class-card-footer">
                <span class="trainer-name">Rui Costa</span>
                <a href="class.php?id=6" class="btn btn-outline btn-sm">Ver Detalhes</a>
              </div>
            </div>
          </li>

        </ul>

        <div class="load-more">
          <button class="btn btn-ghost btn-lg">Mostrar Mais Aulas</button>
        </div>
      </div>
    </section>
  </main>

<?php require __DIR__ . '/../templates/footer.php'; ?>
