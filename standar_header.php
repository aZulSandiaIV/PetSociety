<header class="site-header">
  <div class="header-row">
    <a href="#" class="brand" id="home-link" aria-label="Ir al inicio">
      <!-- <span class="paw" aria-hidden="true">🐾</span> -->
      <span class="title">Pet Society</span>
    </a>
    <nav class="top-nav">
      <a href="index.php">inicio</a>
      <a href="location.php">ubicacion</a>
      <a href="messages.php">mensajes</a>
      <a href="profile.php">perfil</a>
    </nav>
    <div class="user-area">
      <span id="user-name" class="user-name"></span>
      
      
      <!--
      <button id="logout-btn" class="btn small" style="display: none">
        Salir
      </button>
      
      -->
      <?php
        if(isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true)
          echo 
          '
            <form action="logout.php" method="post">
              <input id="logoutButton" type="submit" name="myButton" value="Logout">
            </form>
            ';
        else
          echo '<button id="login-btn" class="btn small">Iniciar sesión</button>';
      ?>
    </div>
  </div>
</header>