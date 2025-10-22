<!doctype html>
<html lang="es">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Perfil — PetSociety</title>
    <link rel="stylesheet" href="css/styles.css">
  </head>
  <body>
    
    <?php include 'standar_header.php'; ?>

    <main class="container">
      <section style="margin-top:1rem;display:flex;gap:1rem;align-items:flex-start">
        <div style="width:260px;background:var(--surface);padding:1rem;border-radius:12px;box-shadow:var(--card-shadow)">
          <div id="avatar" style="width:100%;height:220px;border-radius:10px;background:#f3f6fb;display:flex;align-items:center;justify-content:center;font-size:2rem">📷</div>
          <div style="margin-top:.8rem">
            <strong id="profile-name">Nombre</strong>
            <div id="profile-email" style="color:var(--muted-text)"></div>
          </div>
        </div>
        <div style="flex:1;background:var(--surface);padding:1rem;border-radius:12px;box-shadow:var(--card-shadow)">
          <h3>Editar perfil</h3>
          <form id="profile-form" style="display:flex;flex-direction:column;gap:.6rem;margin-top:.6rem">
            <label>Nombre<input name="name" required></label>
            <label>Email<input name="email" type="email" required></label>
            <label>Foto de perfil<input name="avatar" type="file" accept="image/*"></label>
            <div style="display:flex;gap:.6rem;margin-top:.6rem">
              <button class="btn" type="submit">Guardar</button>
              <button class="btn secondary" type="button" id="logout-profile">Cerrar sesión</button>
            </div>
          </form>
        </div>
      </section>
    </main>

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

    <script src="js/profile.js" defer></script>
  </body>
</html>