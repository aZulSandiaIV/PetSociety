<div
      id="post-modal"
      class="modal"
      aria-hidden="true"
      role="dialog"
      aria-modal="true"
      aria-labelledby="post-modal-title"
    >
      <div class="modal-content" tabindex="-1">
        <button id="post-close" class="modal-close">✕</button>
        <div class="modal-body post-form">
          <h3 id="post-modal-title">Crear publicación</h3>
          <form id="create-post-form">
            <label
              >Tipo
              <select name="type" required>
                <option value="perro">Perro</option>
                <option value="gato">Gato</option>
                <option value="otro">Otro</option>
              </select>
            </label>
            <label
              >Raza<input name="breed" placeholder="Raza" required
            /></label>
            <label
              >Color<input name="color" placeholder="Color" required
            /></label>
            <label
              >Tamaño
              <select name="size" required>
                <option value="pequeno">Pequeño</option>
                <option value="mediano">Mediano</option>
                <option value="grande">Grande</option>
              </select>
            </label>
            <label>Descripción<textarea name="desc" rows="3"></textarea></label>
            <label
              >Imagen<input name="image" type="file" accept="image/*"
            /></label>
            <div class="preview"><img id="img-preview" alt="preview" /></div>
            <div
              style="
                display: flex;
                gap: 0.6rem;
                align-items: center;
                margin-top: 0.5rem;
              "
            >
              <button
                id="assign-loc-btn"
                type="button"
                class="btn small secondary"
              >
                Asignar ubicación
              </button>
              <button id="use-my-loc" type="button" class="btn small">
                Usar mi ubicación
              </button>
              <div
                id="loc-readout"
                style="
                  font-size: 0.85rem;
                  color: var(--muted-text);
                  margin-left: 0.6rem;
                "
              >
                Sin ubicación
              </div>
            </div>
            <input type="hidden" name="lat" />
            <input type="hidden" name="lng" />
            <div class="form-actions">
              <button type="submit" class="btn">Publicar</button>
            </div>
          </form>
        </div>
      </div>
    </div>

    <script>
        // Open and close post modal
        const postModal = document.getElementById('post-modal');
        const openPostBtn = document.getElementById('open-post');
        if(postModal) postModal.addEventListener('click', e=>{ if(e.target===postModal) closeModal(postModal); });
        if(openPostBtn) {
            openPostBtn.addEventListener('click', ()=> openModal(postModal, openPostBtn));     
        }
        
        
    </script>
