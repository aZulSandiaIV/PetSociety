document.addEventListener('DOMContentLoaded', ()=>{

  const yearEl = document.getElementById('year');
  const modal = document.getElementById('modal');

  //const openPostBtn = document.getElementById('open-post');
  const imgPreview = document.getElementById('img-preview');
  const loginBtn = document.getElementById('login-btn');
  const authModal = document.getElementById('auth-modal');

  yearEl && (yearEl.textContent = new Date().getFullYear());

  // small toast (optional)
  function showToast(msg, timeout=2500){
    const t = document.getElementById('toast');
    if(!t) return; 
    const item = document.createElement('div');
    item.className = 'item';
    item.textContent = msg;
    t.appendChild(item);
    setTimeout(()=>{ item.classList.add('removing'); setTimeout(()=>item.remove(),300); }, timeout);
  }

  // Modal helpers (keep for accessibility)
  const lastActive = new WeakMap();
  function openModal(modalEl, opener){
    if(!modalEl) return;
    if(opener) lastActive.set(modalEl, opener);
    modalEl.setAttribute('aria-hidden','false');
    const focusable = modalEl.querySelector('button, [href], input, select, textarea, [tabindex]:not([tabindex="-1"])');
    if(focusable) focusable.focus();
    document.body.style.overflow = 'hidden';
  }
  function closeModal(modalEl){
    if(!modalEl) return;
    modalEl.setAttribute('aria-hidden','true');
    document.body.style.overflow = '';
    const opener = lastActive.get(modalEl);
    if(opener && typeof opener.focus === 'function') opener.focus();
  }

  // close on overlay / ESC
  if(modal) modal.addEventListener('click', e=>{ if(e.target===modal) closeModal(modal); });
  
  if(authModal) authModal.addEventListener('click', e=>{ if(e.target===authModal) closeModal(authModal); });
  document.addEventListener('keydown', e=>{ if(e.key==='Escape'){ closeModal(modal); closeModal(postModal); if(authModal) closeModal(authModal); } });

  

  // Image preview for file inputs (keep)
  const fileInput = document.querySelector('#create-post-form input[type="file"][name="image"]');
  if(fileInput && imgPreview){
    fileInput.addEventListener('change', (ev)=>{
      const f = ev.target.files && ev.target.files[0];
      if(!f){ imgPreview.src = ''; return; }
      const reader = new FileReader();
      reader.onload = e=> imgPreview.src = e.target.result;
      reader.readAsDataURL(f);
    });
  }

  // Open detail modal using data-attributes that PHP outputs on each card.
  document.querySelectorAll('.view-btn').forEach(btn=>{
    btn.addEventListener('click', (e)=>{
      const card = e.currentTarget.closest('.card');
      if(!card) return;
      // expected attributes rendered by PHP: data-image, data-type, data-breed, data-desc, data-size, data-color, data-author
      const img = card.getAttribute('data-image') || '';
      const title = `${card.getAttribute('data-type')||''} · ${card.getAttribute('data-breed')||''}`;
      const desc = card.getAttribute('data-desc') || '';
      const size = card.getAttribute('data-size') || '';
      const color = card.getAttribute('data-color') || '';
      const author = card.getAttribute('data-author') || '';

      const modalImg = document.getElementById('modal-img');
      const modalTitle = document.getElementById('modal-title');
      const modalDesc = document.getElementById('modal-desc');
      const modalMeta = document.getElementById('modal-meta');
      const modalAuthor = document.getElementById('modal-author');

      if(modalImg) modalImg.src = img || modalImg.src;
      if(modalTitle) modalTitle.textContent = title;
      if(modalDesc) modalDesc.textContent = desc;
      if(modalMeta){
        modalMeta.innerHTML = '';
        if(size) { const li=document.createElement('li'); li.textContent = size; modalMeta.appendChild(li); }
        if(color){ const li=document.createElement('li'); li.textContent = color; modalMeta.appendChild(li); }
      }
      if(modalAuthor) modalAuthor.textContent = author;

      openModal(modal, e.currentTarget);
    });
  });

  // Confirm deletion for server-side delete links/forms.
  document.querySelectorAll('.delete-btn').forEach(btn=>{
    btn.addEventListener('click', (e)=>{
      // expected: button is either inside a form or an anchor with data-delete-url
      if(!confirm('¿Borrar esta publicación?')) return;
      const form = e.currentTarget.closest('form');
      if(form){ form.submit(); return; }
      const url = e.currentTarget.getAttribute('data-delete-url');
      if(url) window.location.href = url; // or do a fetch POST to url if required
    });
  });

  // Login button (open server-side modal or redirect to login page)
  if(loginBtn){
    loginBtn.addEventListener('click', ()=> openModal(authModal, loginBtn));
  }

});