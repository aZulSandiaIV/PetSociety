// profile.js - simple profile view/edit backed by localStorage
document.addEventListener('DOMContentLoaded', ()=>{
  const yearEl = document.getElementById('year'); yearEl && (yearEl.textContent = new Date().getFullYear());
  const profileForm = document.getElementById('profile-form');
  const profileName = document.getElementById('profile-name');
  const profileEmail = document.getElementById('profile-email');
  const avatar = document.getElementById('avatar');

  function loadUsers(){ try{return JSON.parse(localStorage.getItem('ps_users')||'[]')}catch(e){return []} }
  function saveUsers(u){ try{ localStorage.setItem('ps_users', JSON.stringify(u)) }catch(e){} }
  function getCurrentUser(){ try{return JSON.parse(localStorage.getItem('ps_current')||'null')}catch(e){return null} }
  function setCurrentUser(u){ try{ localStorage.setItem('ps_current', JSON.stringify(u)) }catch(e){} }

  const cur = getCurrentUser();
  // If another page requested to view a user, prefer that view (transient key ps_view_user)
  const pendingUser = (function(){ try{ return JSON.parse(localStorage.getItem('ps_view_user')||'null') }catch(e){ return null } })();
  if(pendingUser){ try{ localStorage.removeItem('ps_view_user'); }catch(e){} }
  const viewUser = pendingUser || cur;
  if(viewUser){
    // display the viewed user's info (may be the current user or a transient viewed user)
    profileName.textContent = viewUser.name || viewUser.email || 'Usuario';
    profileEmail.textContent = viewUser.email || '';
    // prefill form
    profileForm.elements['name'].value = viewUser.name || '';
    profileForm.elements['email'].value = viewUser.email || '';
    if(viewUser.avatar) avatar.innerHTML = `<img src="${viewUser.avatar}" style="width:100%;height:100%;object-fit:cover;border-radius:8px" />`;
    // if viewing another user's profile, disable editing and offer a Send Message button
    if(pendingUser){
      profileForm.querySelectorAll('input,button').forEach(i=>i.disabled=true);
      // create send message button
      const sendBtn = document.createElement('button'); sendBtn.className='btn'; sendBtn.textContent='Enviar mensaje'; sendBtn.style.marginTop='10px';
      sendBtn.addEventListener('click', ()=>{
        // create conversation and navigate
        try{
          const key = 'ps_conversations';
          const convs = JSON.parse(localStorage.getItem(key) || '[]');
          const convId = 'user:'+ (viewUser.email || viewUser.name);
          let conv = convs.find(c=>c.id===convId || c.title===viewUser.name || c.title===viewUser.email);
          if(!conv){ conv = { id: convId, title: viewUser.name || viewUser.email, messages: [], last: Date.now() }; convs.unshift(conv); localStorage.setItem(key, JSON.stringify(convs)); }
          localStorage.setItem('ps_open_conversation', conv.id);
        }catch(e){ console.error('No se pudo crear conversación', e); }
        window.location.href = 'messages.html';
      });
      avatar.parentNode && avatar.parentNode.appendChild(sendBtn);
    }
  } else {
    profileName.textContent = 'Invitado';
    profileEmail.textContent = '';
  }

  profileForm.addEventListener('submit', (e)=>{
    e.preventDefault();
    const fm = new FormData(profileForm); const name = fm.get('name').trim(); const email = fm.get('email').trim();
    const f = profileForm.avatar && profileForm.avatar.files && profileForm.avatar.files[0];
    if(f){ const reader = new FileReader(); reader.onload = (ev)=> saveProfile(name,email,ev.target.result); reader.readAsDataURL(f); }
    else saveProfile(name,email,null);
  });

  function saveProfile(name,email,avatarData){
    let users = loadUsers();
    const cur = getCurrentUser();
    if(cur){
      // update user in users list
      const idx = users.findIndex(u=>u.email===cur.email);
      if(idx>=0){ users[idx].name = name; users[idx].email = email; if(avatarData) users[idx].avatar = avatarData; }
      else { users.push({email,name,avatar:avatarData}); }
      saveUsers(users);
      const newCur = {email,name}; if(avatarData) newCur.avatar = avatarData; setCurrentUser(newCur);
      profileName.textContent = name; profileEmail.textContent = email; if(avatarData) avatar.innerHTML = `<img src="${avatarData}" style="width:100%;height:100%;object-fit:cover;border-radius:8px" />`;
      alert('Perfil actualizado');
    } else {
      alert('Inicia sesión para editar tu perfil');
    }
  }

  document.getElementById('logout-profile').addEventListener('click', ()=>{ localStorage.removeItem('ps_current'); window.location.href = 'index.html'; });
});
