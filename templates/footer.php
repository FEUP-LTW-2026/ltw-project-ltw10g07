  <footer class="site-footer">
    <div class="container footer-grid">

      <div class="footer-brand">
        <a href="landingpage.php" class="logo">
          <span class="logo-icon" aria-hidden="true">LB</span>
          LiftToBig
        </a>
        <p>
          Eleva a tua jornada de fitness com tecnologia e comunidade.
          Os teus objetivos, o nosso foco.
        </p>
        <nav aria-label="Redes sociais">
          <ul class="footer-social">
            <li><a href="#" aria-label="Facebook">f</a></li>
            <li><a href="#" aria-label="Instagram">&#9419;</a></li>
            <li><a href="#" aria-label="Twitter">X</a></li>
          </ul>
        </nav>
      </div>

      <div class="footer-col">
        <h2>Links Rapidos</h2>
        <ul>
          <li><a href="classes.php">Ver Aulas</a></li>
          <li><a href="trainers.php">Encontrar Treinador</a></li>
          <li><a href="schedule.php">O Meu Horario</a></li>
          <li><a href="equipment.php">Estado do Ginasio</a></li>
        </ul>
      </div>

      <div class="footer-col">
        <h2>Suporte</h2>
        <ul>
          <li><a href="#">Centro de Ajuda</a></li>
          <li><a href="#">Planos de Membro</a></li>
          <li><a href="#">Protocolos de Seguranca</a></li>
          <li><a href="#">Contacta-nos</a></li>
        </ul>
      </div>

      <div class="footer-col">
        <h2>Contacto</h2>
        <address>
          <p class="footer-contact-item">
            <span class="contact-icon" aria-hidden="true">&#9679;</span>
            FEUP
          </p>
          <p class="footer-contact-item">
            <span class="contact-icon" aria-hidden="true">&#9743;</span>
            912 345 678
          </p>
          <p class="footer-contact-item">
            <span class="contact-icon" aria-hidden="true">&#9993;</span>
            lifttobig@gmail.com
          </p>
        </address>
      </div>

    </div>

    <div class="footer-bottom">
      <p>&copy; 2025 LiftToBig. Todos os direitos reservados. Projeto academico LTW.</p>
    </div>
  </footer>

<?php foreach (($pageScripts ?? []) as $script): ?>
  <script src="<?= htmlspecialchars($script) ?>" defer></script>
<?php endforeach; ?>

</body>
</html>
