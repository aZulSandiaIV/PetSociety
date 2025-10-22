<!doctype html>
<html lang="es">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Mensajes — PetSociety</title>
  <link rel="stylesheet" href="css/styles.css">
  </head>
  <body>
    
    <?php include 'standar_header.php'; ?>

    <main class="container">
      <section class="messages-wrap container" style="margin-top:1rem">
        <aside class="conversations-col">
          <h3>Conversaciones</h3>
          <ul id="conversations" class="conversations-list"></ul>
        </aside>
        <section class="chat-col">
          <div id="chat-window" class="chat-window"></div>
          <form id="chat-form" class="chat-form">
            <input id="chat-input" placeholder="Escribe un mensaje..." class="chat-input" />
            <button class="btn" type="submit">Enviar</button>
          </form>
        </section>
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

    <script src="js/messages.js" defer></script>
  </body>
</html>