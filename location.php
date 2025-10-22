<!doctype html>
<html lang="es">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Ubicación — PetSociety</title>
    <link rel="stylesheet" href="css/styles.css">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
  </head>
  <body>
    
    <?php include 'standar_header.php'; ?>

    <main class="container">
      <section style="margin-top:1rem">
        <h2>Buscar animales cerca</h2>
        <p class="muted">Usa el mapa para localizar publicaciones reportadas por ubicación. Haz click en un marcador para ver la publicación.</p>
        <div id="map" style="height:520px;border-radius:12px;overflow:hidden;margin-top:1rem;box-shadow:var(--card-shadow)"></div>
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

    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script src="js/location.js" defer></script>
  </body>
</html>