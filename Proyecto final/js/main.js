// PetSociety main.js - publicaciones locales, filtros, modales, comentarios y autenticación local
document.addEventListener('DOMContentLoaded', ()=>{
  const postsEl = document.getElementById('posts');
  const yearEl = document.getElementById('year');
  const modal = document.getElementById('modal');
  const postModal = document.getElementById('post-modal');
  const openPostBtn = document.getElementById('open-post');
  const postForm = document.getElementById('create-post-form');
  const imgPreview = document.getElementById('img-preview');

  // auth elements
  const authModal = document.getElementById('auth-modal');
  const loginBtn = document.getElementById('login-btn');
  const logoutBtn = document.getElementById('logout-btn');
  const userNameEl = document.getElementById('user-name');
  const authClose = document.getElementById('auth-close');
  const loginForm = document.getElementById('login-form');
  const switchAuth = document.getElementById('switch-auth');
  const authTitle = document.getElementById('auth-title');

  yearEl && (yearEl.textContent = new Date().getFullYear());

  // Toast helper
  function showToast(msg, timeout=3000){
    const t = document.getElementById('toast');
    if(!t) return alert(msg);
    const item = document.createElement('div');
    item.className = 'item';
    item.textContent = msg;
    t.appendChild(item);
    // remove with CSS animation class for smoother transition
    setTimeout(()=>{
      item.classList.add('removing');
      setTimeout(()=>item.remove(), 380);
    }, timeout);
  }

  // adaptador API (stubs) - delega a window.api
  const api = window.api || {};
  function loadPosts(){
    if(api.loadPosts) return api.loadPosts();
    try{ return JSON.parse(localStorage.getItem('ps_posts')||'[]'); }catch(e){ return []; }
  }
  function savePosts(arr){ if(api.savePosts) return api.savePosts(arr); try{ localStorage.setItem('ps_posts', JSON.stringify(arr)); }catch(e){} }
  function loadUsers(){ if(api.loadUsers) return api.loadUsers(); try{ return JSON.parse(localStorage.getItem('ps_users')||'[]'); }catch(e){ return []; } }
  function saveUsers(u){ if(api.saveUsers) return api.saveUsers(u); try{ localStorage.setItem('ps_users', JSON.stringify(u)); }catch(e){} }
  function getCurrentUser(){ if(api.getCurrentUser) return api.getCurrentUser(); try{ return JSON.parse(localStorage.getItem('ps_current')||'null'); }catch(e){return null} }
  function setCurrentUser(u){ if(api.setCurrentUser) api.setCurrentUser(u); try{ localStorage.setItem('ps_current', JSON.stringify(u)); }catch(e){} updateAuthUI(); }
  function clearCurrentUser(){ if(api.clearCurrentUser) api.clearCurrentUser(); try{ localStorage.removeItem('ps_current'); }catch(e){} updateAuthUI(); }

  function updateAuthUI(){
    const cur = getCurrentUser();
    if(cur){ userNameEl.textContent = cur.name || cur.email; userNameEl.style.display='inline'; loginBtn.style.display='none'; logoutBtn.style.display='inline-block'; }
    else { userNameEl.textContent=''; userNameEl.style.display='none'; loginBtn.style.display='inline-block'; logoutBtn.style.display='none'; }
  }

  // Accessibility: modal helpers to manage focus and aria-hidden
  const lastActive = new WeakMap();
  function openModal(modalEl, opener){
    if(!modalEl) return;
    // save opener to restore focus later
    if(opener) lastActive.set(modalEl, opener);
    modalEl.setAttribute('aria-hidden','false');
    const focusable = modalEl.querySelector('button, [href], input, select, textarea, [tabindex]:not([tabindex="-1"])');
    if(focusable){ focusable.focus(); }
    document.body.style.overflow = 'hidden';
  }
  function closeModal(modalEl){
    if(!modalEl) return;
    modalEl.setAttribute('aria-hidden','true');
    document.body.style.overflow = '';
    const opener = lastActive.get(modalEl);
    if(opener && typeof opener.focus === 'function') opener.focus();
  }

  // render
  function renderPosts(filter){
    const posts = loadPosts();
    let out = '';
    posts.forEach((p, idx)=>{
        const cur = getCurrentUser();
        const canDelete = cur && p.author && cur.email === p.author.email;
        const delHtml = canDelete ? `<button class="delete-btn" data-id="${idx}">Borrar</button>` : '';
      if(filter){
        const byType = filter.type ? p.type === filter.type : true;
        const bySize = filter.size ? p.size === filter.size : true;
        const byColor = filter.color ? p.color === filter.color : true;
        const byBreed = filter.breed ? p.breed.toLowerCase().includes(filter.breed.toLowerCase()) : true;
        if(!(byType && bySize && byColor && byBreed)) return;
      }
        out += `\n        <article class="card" data-id="${idx}">\n          <img src="${p.image||'https://via.placeholder.com/400x300?text=sin+imagen'}" alt="${p.breed}">\n          <div class="meta">\n            <div>\n              <strong>${p.type} · ${p.breed}</strong>\n              <div style=\"font-size:.9rem;color:#666\">${p.size} · ${p.color}</div>\n              <div style=\"font-size:.85rem;color:#333;margin-top:.3rem\">Publicado por: ${p.author?p.author.name:p.author_email||'Anónimo'}</div>\n            </div>\n            <div class="actions">\n              <button class=\"btn view-btn\" data-id=\"${idx}\">Ver</button>\n              ${delHtml}\n            </div>\n          </div>\n        </article>`;
    });
    postsEl.innerHTML = out || '<p>No hay publicaciones aún. Crea la primera.</p>';
    // attach view handlers
    document.querySelectorAll('.view-btn').forEach(b=>b.addEventListener('click', openView));
      // attach delete handlers for per-card delete buttons
      document.querySelectorAll('.delete-btn').forEach(b=>b.addEventListener('click', (e)=>{
        const id = e.currentTarget.dataset.id; if(typeof id==='undefined') return;
        if(!confirm('¿Borrar esta publicación?')) return;
        const posts = loadPosts(); posts.splice(id,1); savePosts(posts); renderPosts(getFilters()); showToast('Publicación borrada');
      }));
      // make the whole card clickable to open the post (but ignore clicks on internal buttons)
      document.querySelectorAll('.card').forEach(card=>{
        card.addEventListener('click', (e)=>{
          // if click originated from a button or link, ignore (those have their own handlers)
          if(e.target.closest('button') || e.target.closest('a')) return;
          const id = card.dataset.id; if(typeof id==='undefined') return;
          openViewById(id, card);
        });
      });
  }

  // abrir vista detalle
  // opens a post by id (used by view button and by clicking the card)
  function openViewById(id, opener){
    const posts = loadPosts();
    const p = posts[id]; if(!p) return;
    document.getElementById('modal-img').src = p.image || 'https://via.placeholder.com/600x400?text=sin+imagen';
    document.getElementById('modal-title').textContent = `${p.type} · ${p.breed}`;
    document.getElementById('modal-desc').textContent = p.desc || '';
    const meta = document.getElementById('modal-meta'); meta.innerHTML = '';
    ['size','color'].forEach(k=>{ const li=document.createElement('li'); li.textContent = p[k]; meta.appendChild(li)});
    // author — create a small circular avatar positioned at top-right of the modal image
    if(p.author){
      // remove any previous avatar/menu to avoid duplicates
      const prev = document.getElementById('modal-author-avatar'); if(prev) prev.remove();
      const modalContent = document.querySelector('.modal-content');
      const imgEl = document.getElementById('modal-img');
      // create avatar element
      const avatarEl = document.createElement('img'); avatarEl.id = 'modal-author-avatar'; avatarEl.className = 'author-avatar';
      const avatarSrc = (p.author.avatar || p.author.image) || (p.author.name ? `data:image/svg+xml;utf8,${encodeURIComponent(`<svg xmlns='http://www.w3.org/2000/svg' width='120' height='120'><rect width='100%' height='100%' fill='%23e5e7eb'/><text x='50%' y='54%' dominant-baseline='middle' text-anchor='middle' font-family='Arial' font-size='48' fill='%23343a40'>${(p.author.name||'U').split(' ').map(n=>n[0]).slice(0,2).join('')}</text></svg>` )}` : 'https://via.placeholder.com/120?text=%F0%9F%91%A4');
      avatarEl.src = avatarSrc; avatarEl.alt = p.author.name || p.author.email || 'Autor';
      // position absolutely over the modal image: top-right
      avatarEl.style.position = 'absolute'; avatarEl.style.top = '12px'; avatarEl.style.right = '12px';
      // ensure modal content is positioned relative (CSS already set)
      if(modalContent){ modalContent.appendChild(avatarEl); }

      // clicking the avatar opens the action menu (reusing previous menu logic)
      avatarEl.addEventListener('click', (ev)=>{
        ev.stopPropagation(); const existing = document.getElementById('author-action-menu'); if(existing) existing.remove();
        const menu = document.createElement('div'); menu.id = 'author-action-menu';
        menu.style.position = 'absolute'; menu.style.background = 'var(--surface)'; menu.style.boxShadow = '0 8px 24px rgba(16,24,40,0.12)'; menu.style.borderRadius = '8px'; menu.style.padding = '6px'; menu.style.zIndex = 3000;
        // position menu under the avatar
        const rect = avatarEl.getBoundingClientRect(); const mcRect = modalContent.getBoundingClientRect();
        menu.style.left = `${rect.left - mcRect.left}px`; menu.style.top = `${rect.bottom - mcRect.top + 8}px`;
        const view = document.createElement('button'); view.className='btn small'; view.textContent = 'Ver perfil'; view.style.display='block'; view.style.width='100%'; view.style.marginBottom='6px';
        const msg = document.createElement('button'); msg.className='btn small secondary'; msg.textContent = 'Enviar mensaje'; msg.style.display='block'; msg.style.width='100%';
        menu.appendChild(view); menu.appendChild(msg); modalContent.appendChild(menu);
        function cleanup(){ const m=document.getElementById('author-action-menu'); if(m) m.remove(); document.removeEventListener('click', cleanup); }
        setTimeout(()=>document.addEventListener('click', cleanup),50);
        view.addEventListener('click', ()=>{ try{ localStorage.setItem('ps_view_user', JSON.stringify(p.author)); }catch(e){}; cleanup(); window.location.href='profile.html'; });
        msg.addEventListener('click', ()=>{ try{ const key='ps_conversations'; const convs = JSON.parse(localStorage.getItem(key)||'[]'); const convId = 'user:'+(p.author.email||(p.author.name||'unknown')); let conv = convs.find(c=>c.id===convId||c.title===p.author.name||c.title===p.author.email); if(!conv){ conv={id:convId,title:p.author.name||p.author.email,messages:[],last:Date.now()}; convs.unshift(conv); localStorage.setItem(key, JSON.stringify(convs)); } localStorage.setItem('ps_open_conversation', conv.id); }catch(e){ console.error(e); } cleanup(); window.location.href='messages.html'; });
      });
    }
    // comments
    const commentList = document.getElementById('comment-list'); commentList.innerHTML = '';
    (p.comments||[]).forEach(c=>{ const li=document.createElement('li'); li.textContent = c; commentList.appendChild(li); });
    // set current id on comment form
    const cf = document.getElementById('comment-form'); cf.dataset.id = id;
    // mostrar botones editar/borrar si es autor
    const cur = getCurrentUser();
    const editBtn = document.getElementById('modal-edit');
    const delBtn = document.getElementById('modal-delete');
    if(cur && p.author && cur.email === p.author.email){ editBtn.style.display='inline-block'; delBtn.style.display='inline-block'; } else { editBtn.style.display='none'; delBtn.style.display='none'; }
    openModal(modal, opener || null);
  }

  function openView(e){
    // delegate to openViewById when called from a button event
    const id = e.currentTarget.dataset.id;
    openViewById(id, e.currentTarget);
  }
  document.getElementById('modal-close').addEventListener('click', ()=> closeModal(modal));
  // close modal on overlay click
  modal.addEventListener('click', e=>{ if(e.target === modal) closeModal(modal); });
  // close post modal on overlay click
  postModal.addEventListener('click', e=>{ if(e.target === postModal) closeModal(postModal); });
  // close auth modal on overlay click
  authModal && authModal.addEventListener('click', e=>{ if(e.target === authModal) closeModal(authModal); });
  // ESC to close
  document.addEventListener('keydown', e=>{ if(e.key === 'Escape'){ closeModal(modal); closeModal(postModal); authModal && closeModal(authModal); } });
  // borrar publicación
  document.getElementById('modal-delete').addEventListener('click', ()=>{
    if(!confirm('¿Borrar esta publicación?')) return;
    const id = document.getElementById('comment-form').dataset.id; if(typeof id==='undefined') return;
    const posts = loadPosts(); posts.splice(id,1); savePosts(posts); closeModal(modal); renderPosts(getFilters()); showToast('Publicación borrada');
  });

  // editar publicación - carga datos en el form de crear y usa un flag data-edit
  document.getElementById('modal-edit').addEventListener('click', ()=>{
    const id = document.getElementById('comment-form').dataset.id; if(typeof id==='undefined') return;
    const posts = loadPosts(); const p = posts[id]; if(!p) return;
    // llenar form
    postForm.elements['type'].value = p.type || 'perro';
    postForm.elements['breed'].value = p.breed || '';
    postForm.elements['color'].value = p.color || '';
    postForm.elements['size'].value = p.size || 'mediano';
    postForm.elements['desc'].value = p.desc || '';
    imgPreview.src = p.image || '';
    postForm.dataset.edit = id;
    closeModal(modal);
    openModal(postModal);
  });
  document.getElementById('post-close').addEventListener('click', ()=> closeModal(postModal));

  // abrir form publicar (requiere usuario)
  openPostBtn.addEventListener('click', ()=>{
    const cur = getCurrentUser();
    if(!cur){ authModal.setAttribute('aria-hidden','false'); return; }
    openModal(postModal, openPostBtn);
  });

  // image preview
  postForm.image && postForm.image.addEventListener('change', (ev)=>{
    const f = ev.target.files && ev.target.files[0];
      if(!f) { imgPreview.src=''; return; }
    const reader = new FileReader();
    reader.onload = e=> imgPreview.src = e.target.result;
    reader.readAsDataURL(f);
  });

  // MAP PICKER logic
  const assignLocBtn = document.getElementById('assign-loc-btn');
  const useMyLocBtn = document.getElementById('use-my-loc');
  const locReadout = document.getElementById('loc-readout');
  const mapModal = document.getElementById('map-modal');
  const miniMapEl = document.getElementById('mini-map');
  let miniMap, miniMarker;
  function initMiniMap(){
    if(miniMap) return;
    miniMap = L.map(miniMapEl).setView([-34.6037, -58.3816], 13);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png').addTo(miniMap);
    miniMarker = L.marker([-34.6037, -58.3816], {draggable:true}).addTo(miniMap);
  }

  assignLocBtn && assignLocBtn.addEventListener('click', ()=>{
    initMiniMap();
    openModal(mapModal, assignLocBtn);
    // try to position marker at existing lat/lng
    const lat = postForm.elements['lat'].value; const lng = postForm.elements['lng'].value;
    if(lat && lng){ miniMarker.setLatLng([Number(lat), Number(lng)]); miniMap.setView([Number(lat), Number(lng)], 15); }
    miniMap.invalidateSize();
  });
  document.getElementById('map-close').addEventListener('click', ()=> closeModal(mapModal));
  document.getElementById('map-cancel').addEventListener('click', ()=> closeModal(mapModal));
  document.getElementById('map-confirm').addEventListener('click', ()=>{
    if(!miniMarker) return closeModal(mapModal);
    const pos = miniMarker.getLatLng();
    postForm.elements['lat'].value = pos.lat; postForm.elements['lng'].value = pos.lng;
    locReadout.textContent = `Lat: ${pos.lat.toFixed(5)}, Lng: ${pos.lng.toFixed(5)}`;
    closeModal(mapModal);
  });

  useMyLocBtn && useMyLocBtn.addEventListener('click', ()=>{
    if(!navigator.geolocation) return showToast('Geolocalización no disponible');
    showToast('Obteniendo ubicación...');
    navigator.geolocation.getCurrentPosition((pos)=>{
      const lat = pos.coords.latitude; const lng = pos.coords.longitude;
      postForm.elements['lat'].value = lat; postForm.elements['lng'].value = lng;
      locReadout.textContent = `Lat: ${lat.toFixed(5)}, Lng: ${lng.toFixed(5)}`;
    }, ()=> showToast('No fue posible obtener la ubicación'));
  });

  // crear o editar publicación (asociada a autor actual)
  postForm.addEventListener('submit', (ev)=>{
    ev.preventDefault();
    const cur = getCurrentUser();
    if(!cur){ showToast('Inicia sesión para continuar.'); openModal(authModal); return; }
    const data = new FormData(postForm);
    const obj = {
      type: data.get('type'),
      breed: data.get('breed'),
      color: data.get('color'),
      size: data.get('size'),
      desc: data.get('desc'),
      image: imgPreview.src || '' ,
      lat: data.get('lat') ? Number(data.get('lat')) : undefined,
      lng: data.get('lng') ? Number(data.get('lng')) : undefined,
      comments: [],
      author: { name: cur.name, email: cur.email }
    };
    const posts = loadPosts();
    if(postForm.dataset.edit){
      // editar
      const id = Number(postForm.dataset.edit);
      const existing = posts[id];
      if(existing){
        // preserve comments and author
        obj.comments = existing.comments || [];
        obj.author = existing.author || obj.author;
        posts[id] = obj;
      }
      delete postForm.dataset.edit;
    } else {
      posts.unshift(obj);
    }
    savePosts(posts);
    postForm.reset(); imgPreview.src=''; closeModal(postModal); renderPosts(getFilters());
  });

  // comentar
  document.getElementById('comment-form').addEventListener('submit', (ev)=>{
  ev.preventDefault();
    const id = ev.target.dataset.id; if(typeof id==='undefined') return;
    const v = document.getElementById('comment-input').value.trim(); if(!v) return;
    const posts = loadPosts(); posts[id].comments = posts[id].comments||[]; posts[id].comments.push(v); savePosts(posts);
    document.getElementById('comment-input').value=''; openView({currentTarget:{dataset:{id}}});
  });

  // filtros
  function getFilters(){ return {
      type: document.getElementById('filter-type').value,
      size: document.getElementById('filter-size').value,
      color: document.getElementById('filter-color').value,
      breed: document.getElementById('filter-breed').value.trim()
    }
  }
  ['filter-type','filter-size','filter-color','filter-breed'].forEach(id=>{ const el = document.getElementById(id); if(el) el.addEventListener('input', ()=>renderPosts(getFilters())); });

  // search quick
  document.getElementById('search-btn').addEventListener('click', ()=>{ const q = document.getElementById('search-input').value.trim(); renderPosts({breed:q}); });

  // auth flows
  loginBtn.addEventListener('click', ()=>{ openModal(authModal, loginBtn); });
  authClose.addEventListener('click', ()=>{ closeModal(authModal); });
  logoutBtn.addEventListener('click', ()=>{ clearCurrentUser(); showToast('Has cerrado sesión'); });

  // home link (logo/title) behavior
  const homeLink = document.getElementById('home-link');
  if(homeLink){
    homeLink.addEventListener('click', (e)=>{
      e.preventDefault();
      // reset filters
      const fids = ['filter-type','filter-size','filter-color','filter-breed'];
      fids.forEach(id=>{ const el = document.getElementById(id); if(el){ if(el.tagName.toLowerCase()==='input') el.value=''; else el.selectedIndex=0; } });
      // close any modals
      closeModal(modal); closeModal(postModal); authModal && closeModal(authModal);
      // scroll to top
      window.scrollTo({top:0,behavior:'smooth'});
      // re-render
      renderPosts(getFilters());
    });
  }

  // switch auth mode -- simple toggle text
  let registerMode = false;
  switchAuth.addEventListener('click', ()=>{
    registerMode = !registerMode; authTitle.textContent = registerMode ? 'Registrar' : 'Iniciar sesión'; switchAuth.textContent = registerMode ? 'Cambiar a iniciar sesión' : 'Cambiar a registrar';
    // move focus to first field when switching
    const firstInput = loginForm.querySelector('input[name="email"]'); if(firstInput) firstInput.focus();
  });

  // handle login/register form
  loginForm.addEventListener('submit', (ev)=>{
    ev.preventDefault();
    const fm = new FormData(loginForm); const email = fm.get('email').trim(); const name = fm.get('name').trim(); const pass = fm.get('password');
  if(!email || !pass) return showToast('Email y contraseña requeridos');
    const users = loadUsers();
    const found = users.find(u=>u.email===email);
      if(found){
        // login
    if(found.password === pass){ setCurrentUser({email:found.email,name:found.name}); closeModal(authModal); showToast('Bienvenido '+found.name); }
    else showToast('Contraseña incorrecta');
      } else {
      // register
  const newUser = { email, name, password: pass };
  users.push(newUser); saveUsers(users); setCurrentUser({ email: newUser.email, name: newUser.name }); closeModal(authModal); showToast('Registrado y autenticado como '+name);
    }
  });

  // init demo posts if empty - create 6 example posts
  if(loadPosts().length===0){
    const demo = [
      {type:'gato',breed:'Siames',color:'blanco',size:'pequeno',desc:'Apareció cerca del mercado, muy mimoso. Tiene collar azul.',image:'https://images.unsplash.com/photo-1518791841217-8f162f1e1131?q=80&w=800&auto=format&fit=crop',comments:['Lo vi en la tarde cerca del quiosco'],author:{name:'Protectora A',email:'shelterA@petsociety'},lat:-34.606, lng:-58.381},
      {type:'perro',breed:'Chihuahua',color:'marron',size:'pequeno',desc:'Pequeño y activo, encontrado en la estación. Necesita hogar.',image:'https://images.unsplash.com/photo-1517423440428-a5a00ad493e8?q=80&w=800&auto=format&fit=crop',comments:['Me encantaría adoptarlo'],author:{name:'Protectora B',email:'shelterB@petsociety'},lat:-34.603, lng:-58.39},
      {type:'perro',breed:'Labrador',color:'negro',size:'grande',desc:'Perro adulto, buen carácter con niños. Se perdió en la zona norte.',image:'https://images.unsplash.com/photo-1601758123927-1c5d42b7f3d4?q=80&w=800&auto=format&fit=crop',comments:['¿Tiene chip?'],author:{name:'Usuario1',email:'user1@petsociety'},lat:-34.61, lng:-58.375},
      {type:'gato',breed:'Común',color:'marron',size:'pequeno',desc:'Gata doméstica encontrada, muy asustadiza pero sana.',image:'https://images.unsplash.com/photo-1543852786-1cf6624b9987?q=80&w=800&auto=format&fit=crop',comments:['Puedo ayudar a cuidarla temporalmente'],author:{name:'Usuario2',email:'user2@petsociety'},lat:-34.599, lng:-58.38},
      {type:'otro',breed:'Conejo',color:'blanco',size:'pequeno',desc:'Conejo doméstico perdido cerca del parque central.',image:'https://images.unsplash.com/photo-1560807707-8cc77767d783?q=80&w=800&auto=format&fit=crop',comments:['Lo vi correr hacia la plaza'],author:{name:'Refugio Central',email:'shelterC@petsociety'},lat:-34.607, lng:-58.39},
      {type:'perro',breed:'Beagle',color:'marron',size:'mediano',desc:'En adopción: cariñoso y sociable, vacunado.',image:'https://images.unsplash.com/photo-1548199973-03cce0bbc87b?q=80&w=800&auto=format&fit=crop',comments:['Interesado en adoptar'],author:{name:'Refugio Norte',email:'sheltern@petsociety'},lat:-34.605, lng:-58.377}
    ];
    // try to save via api, fallback to localStorage
    if(typeof api.savePosts === 'function'){
      savePosts(demo);
    } else {
      try{ localStorage.setItem('ps_posts', JSON.stringify(demo)); }catch(e){ console.error('No se pudo guardar demo posts',e); }
    }
    // ensure a demo user list exists
    const demoUsers = [{email:'admin@petsociety',name:'Admin',password:'admin'}];
    if(typeof api.saveUsers === 'function') saveUsers(demoUsers); else try{ localStorage.setItem('ps_users', JSON.stringify(demoUsers)); }catch(e){}
  }

  updateAuthUI();
  renderPosts(getFilters());
  // If another page (map) requested opening a post, handle it once
  const pending = localStorage.getItem('ps_open_post');
  if(pending){
    try{ const id = Number(pending); if(!isNaN(id)){ openViewById(id); } }catch(e){}
    localStorage.removeItem('ps_open_post');
  }
});