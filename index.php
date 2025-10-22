<?php
include ('session.php');
include ('set_bdd.php');
?>
<!DOCTYPE html>
<html lang="es">
  <head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>PetSociety</title>
    <link rel="stylesheet" href="css/styles.css" />
    <link
      rel="stylesheet"
      href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
    />
    <link
      href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&display=swap"
      rel="stylesheet"
    />
  </head>
  <body>

    <?php include 'standar_header.php'; ?>

    <main>
      <section class="search-wrap container">
        <div class="search">
          <input
            id="search-input"
            type="search"
            placeholder="Buscar mi mascota perdida"
          />
          <button id="search-btn" aria-label="Buscar">🔍</button>
        </div>
      </section>

      <section class="hero-container container">
        <div class="hero-content">
          <h2>¿Perdiste o Encontraste?</h2>
          <h2 class="grey-h2">¿Quieres dar en adopción?</h2>
          <button id="open-post">Publicar ahora</button>
        </div>
        <div class="hero-image">
          <img src="img/Fotos De Perros Y Gatos.jpg" alt="Perro y Gato" />
        </div>
      </section>

      <section class="filters container">
        <h3>Quiero adoptar</h3>
        <h4>Selecciona perro/gato</h4>
        <div class="filter-row">
          <select id="filter-type">
            <option value="">Todos</option>
            <option value="perro">Perro</option>
            <option value="gato">Gato</option>
            <option value="otro">Otro</option>
          </select>
          <select id="filter-size">
            <option value="">Tamaño</option>
            <option value="pequeno">Pequeño</option>
            <option value="mediano">Mediano</option>
            <option value="grande">Grande</option>
          </select>
          <select id="filter-color">
            <option value="">Color</option>
            <option value="blanco">Blanco</option>
            <option value="negro">Negro</option>
            <option value="marron">Marrón</option>
          </select>
          <input id="filter-breed" placeholder="Buscar raza" />
        </div>
      </section>

      <section class="gallery container" id="posts">
        <!-- Las publicaciones se insertarán aquí -->
      </section>
    </main>

    <?php
      include('bottom-navbar.php');
    ?>
    
    <footer class="site-footer">
      <div class="container footer-row">
        <p>© <span id="year"></span> PetSociety</p>
        <div class="legal">
          <a href="#">Términos y condiciones</a>
          <a href="#">Política de privacidad</a>
          <a href="#">Contacto legal</a>
        </div>
      </div>
    </footer>

    <!-- Toast notifications -->
    <div id="toast" class="toast" aria-live="polite" aria-atomic="true"></div>

    <!-- Modal ver publicación -->
    <div
      id="modal"
      class="modal"
      aria-hidden="true"
      role="dialog"
      aria-modal="true"
      aria-labelledby="modal-title"
    >
      <div class="modal-content" tabindex="-1">
        <button id="modal-close" class="modal-close">✕</button>
        <div class="modal-body">
          <img id="modal-img" src="" alt="imagen publicación" />
          <div class="modal-info">
            <h3 id="modal-title"></h3>
            <p id="modal-desc"></p>
            <ul id="modal-meta"></ul>
            <div
              class="modal-actions"
              style="margin-top: 0.6rem; display: flex; gap: 0.5rem"
            >
              <button id="modal-edit" class="btn small" style="display: none">
                Editar
              </button>
              <button
                id="modal-delete"
                class="btn danger"
                style="display: none"
              >
                Borrar
              </button>
            </div>
            <div class="comments">
              <h4>Comentarios</h4>
              <ul id="comment-list"></ul>
              <form id="comment-form">
                <input
                  type="text"
                  id="comment-input"
                  placeholder="Escribe un comentario..."
                  required
                />
                <button type="submit" class="btn">Comentar</button>
              </form>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Modal publicar -->
    
    <?php include 'post-modal.php'; ?>

    <!-- Modal login/registro -->
    <div
      id="auth-modal"
      class="modal"
      aria-hidden="true"
      role="dialog"
      aria-modal="true"
      aria-labelledby="auth-title"
    >
      <div class="modal-content auth-modal-content" tabindex="-1">
        <button id="auth-close" class="modal-close">✕</button>
        <div class="auth-container">
          <!-- Formulario con logo arriba -->
          <div class="auth-form-wrapper">
            <!-- Logo arriba -->
              <img src="img/Captura de pantalla 2025-10-19 022632.png" alt="Pet Society Logo" /> 
            </div>

            <h2 id="auth-title">Login</h2>
            <p class="auth-subtitle">Please Sign In to continue.</p>

            <form id="login-form" action = "signinverifier.php" method ="POST">
              <div class="input-group">
                <span class="input-icon">👤</span>
                <input name="email" type="email" placeholder="Email" required />
              </div>

              <div class="input-group">
                <span class="input-icon">🔒</span>
                <input
                  name="password"
                  type="password"
                  placeholder="Password"
                  required
                />
                <span class="input-toggle">👁️</span>
              </div>

              <div class="input-group" id="name-group" style="display: none">
                <span class="input-icon">✏️</span>
                <input name="name" placeholder="Nombre" />
              </div>

              <div class="remember-row">
                <label class="remember-checkbox">
                  <input type="checkbox" />
                  <span>Remember me next time</span>
                </label>
              </div>

              <button type="submit" class="btn-auth">Sign In</button>

              <p class="auth-switch">
                Don't have account? <a href="#" id="switch-auth">Sign Up</a>
              </p>
            </form>
          </div>
        </div>
      </div>
    </div>

    <!-- Map picker modal for assigning a location to a post -->
    <div
      id="map-modal"
      class="modal"
      aria-hidden="true"
      role="dialog"
      aria-modal="true"
      aria-labelledby="map-modal-title"
    >
      <div class="modal-content" tabindex="-1" style="max-width: 820px">
        <button id="map-close" class="modal-close">✕</button>
        <div class="modal-body" style="flex-direction: column">
          <h3 id="map-modal-title">Seleccionar ubicación</h3>
          <div
            id="mini-map"
            style="
              height: 420px;
              border-radius: 10px;
              margin-top: 0.6rem;
              overflow: hidden;
            "
          ></div>
          <div
            style="
              display: flex;
              gap: 0.6rem;
              margin-top: 0.6rem;
              justify-content: flex-end;
            "
          >
            <button id="map-confirm" class="btn">Confirmar ubicación</button>
            <button id="map-cancel" class="btn small">Cancelar</button>
          </div>
        </div>
      </div>
    </div>

    <script src="js/main.js"></script>
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

  </body>
</html>
