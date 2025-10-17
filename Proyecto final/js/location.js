// location.js - mapa interactivo (Leaflet) que muestra posts con coordenadas (simuladas)
document.addEventListener('DOMContentLoaded', ()=>{
  const yearEl = document.getElementById('year'); yearEl && (yearEl.textContent = new Date().getFullYear());
  // initialize map centered on Buenos Aires (CABA)
  const map = L.map('map').setView([-34.6037, -58.3816], 13);
  L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    attribution: '&copy; OpenStreetMap contributors'
  }).addTo(map);

  // load posts from localStorage and place markers
  function loadPosts(){ try{return JSON.parse(localStorage.getItem('ps_posts')||'[]')}catch(e){return []} }
  const posts = loadPosts();

  // helper to create a popup content
  function popupContent(p, idx){
    const title = `${p.type} · ${p.breed}`;
    const short = p.desc ? p.desc.substring(0,120) + (p.desc.length>120? '...':'') : '';
    return `<div style="max-width:260px"><strong>${title}</strong><div style="font-size:.9rem;color:#666;margin-top:.35rem">${short}</div><div style="margin-top:.5rem"><a href="index.html#" data-id="${idx}" class="map-view-link">Ver publicación</a></div></div>`;
  }

  // if posts don't have coordinates, generate some around Buenos Aires (demo)
  posts.forEach((p, idx)=>{
    const baseLat = -34.6037, baseLng = -58.3816;
    const lat = p.lat || (baseLat + (Math.random() - 0.5) * 0.08); // small random offset ~ few km
    const lng = p.lng || (baseLng + (Math.random() - 0.5) * 0.08);
    const marker = L.marker([lat,lng]).addTo(map);
    marker.bindPopup(popupContent(p, idx));
  });

  // delegate clicks on map popup links
  document.body.addEventListener('click', (e)=>{
    const a = e.target.closest('.map-view-link');
    if(!a) return;
    e.preventDefault();
    const id = a.dataset.id; if(typeof id==='undefined') return;
    // open index and show modal by setting hash and using localStorage flag
    // We'll set a temp key so index can open the modal on load (index already exists in this workspace)
    localStorage.setItem('ps_open_post', id);
    // navigate to index page
    window.location.href = 'index.html';
  });

});
