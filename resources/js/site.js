/* Hope Media RDC — comportements du site public (sans dépendance).
   Les appels serveur (likes, prière) utilisent les mêmes routes et réponses qu'avant. */
(() => {
  const $ = (s, el = document) => el.querySelector(s);
  const $$ = (s, el = document) => [...el.querySelectorAll(s)];
  const esc = s => String(s).replace(/[&<>"]/g, c => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;' }[c]));
  const store = {
    get(k, d) { try { const v = localStorage.getItem('hm:' + k); return v === null ? d : JSON.parse(v); } catch { return d; } },
    set(k, v) { try { localStorage.setItem('hm:' + k, JSON.stringify(v)); } catch {} },
  };
  const reduceMotion = matchMedia('(prefers-reduced-motion: reduce)').matches;
  const csrf = $('meta[name="csrf-token"]')?.content;

  /* ---------- Notification ---------- */
  let toastTimer;
  function toast(msg, type = 'ok') {
    const el = $('#toast'); if (!el) return;
    el.textContent = msg;
    el.classList.toggle('err', type === 'error');
    el.classList.add('show');
    clearTimeout(toastTimer); toastTimer = setTimeout(() => el.classList.remove('show'), 3000);
  }

  /* ---------- Thème clair / sombre ---------- */
  const root = document.documentElement, themeBtn = $('#themeToggle');
  function applyTheme(t) {
    if (t === 'light') root.setAttribute('data-theme', 'light'); else root.removeAttribute('data-theme');
    if (themeBtn) {
      themeBtn.innerHTML = `<i class="fa-solid ${t === 'light' ? 'fa-moon' : 'fa-sun'}" aria-hidden="true"></i>`;
      themeBtn.setAttribute('aria-label', t === 'light' ? 'Passer en mode sombre' : 'Passer en mode clair');
    }
  }
  applyTheme(store.get('theme', 'dark'));
  themeBtn?.addEventListener('click', () => {
    const next = root.getAttribute('data-theme') === 'light' ? 'dark' : 'light';
    applyTheme(next); store.set('theme', next);
  });

  /* ---------- En-tête, horloge, messages ---------- */
  const header = $('#header');
  const onScroll = () => header?.classList.toggle('is-solid', scrollY > 40);
  addEventListener('scroll', onScroll, { passive: true }); onScroll();

  const clock = $('#clock');
  const kinshasaNow = () => { const d = new Date(); return new Date(d.getTime() + d.getTimezoneOffset() * 60000 + 3600000); };
  const tick = () => { if (clock) clock.textContent = kinshasaNow().toTimeString().slice(0, 5); };
  tick(); setInterval(tick, 15000);

  const flash = $('#flash');
  if (flash) setTimeout(() => { flash.style.opacity = '0'; setTimeout(() => flash.remove(), 450); }, 6000);

  /* ---------- Menu mobile ---------- */
  const drawer = $('#drawer');
  let drawerReturn;
  const openDrawer = () => { drawerReturn = document.activeElement; drawer.hidden = false; document.body.style.overflow = 'hidden'; $('a, button', drawer)?.focus(); };
  const closeDrawer = () => { drawer.hidden = true; document.body.style.overflow = ''; drawerReturn?.focus(); };
  $('#menuOpen')?.addEventListener('click', openDrawer);
  $('#menuClose')?.addEventListener('click', closeDrawer);
  drawer?.addEventListener('click', e => { if (e.target === drawer) closeDrawer(); });

  /* ---------- Recherche ---------- */
  const searchEl = $('#search'), searchInput = $('#searchInput');
  let searchReturn;
  const openSearch = () => { searchReturn = document.activeElement; searchEl.hidden = false; searchInput.focus(); document.body.style.overflow = 'hidden'; };
  const closeSearch = () => { searchEl.hidden = true; document.body.style.overflow = ''; searchReturn?.focus(); };
  $$('[data-search-open]').forEach(b => b.addEventListener('click', openSearch));
  $('#searchClose')?.addEventListener('click', closeSearch);
  addEventListener('keydown', e => {
    if (e.key === 'Escape') { if (searchEl && !searchEl.hidden) closeSearch(); if (drawer && !drawer.hidden) closeDrawer(); }
    if (e.key === '/' && searchEl?.hidden && !/input|textarea|select/i.test(document.activeElement.tagName)) { e.preventDefault(); openSearch(); }
  });

  /* ---------- Carrousel du hero ---------- */
  const hero = $('#hero');
  const slides = hero ? $$('.hero-slide', hero) : [];
  if (slides.length > 1) {
    const dots = $$('.hero-dots button', hero);
    const ms = +hero.dataset.slideMs || 6500;
    let idx = 0, timer, paused = false, touchX = 0;
    const show = i => {
      idx = (i + slides.length) % slides.length;
      slides.forEach((s, k) => s.classList.toggle('is-active', k === idx));
      dots.forEach((d, k) => d.setAttribute('aria-current', k === idx));
    };
    const start = () => { clearInterval(timer); if (!reduceMotion) timer = setInterval(() => { if (!paused) show(idx + 1); }, ms); };
    dots.forEach(d => d.addEventListener('click', () => { show(+d.dataset.index); start(); }));
    hero.addEventListener('mouseenter', () => { paused = true; });
    hero.addEventListener('mouseleave', () => { paused = false; });
    hero.addEventListener('focusin', () => { paused = true; });
    hero.addEventListener('focusout', e => { if (!hero.contains(e.relatedTarget)) paused = false; });
    hero.addEventListener('touchstart', e => { touchX = e.changedTouches[0].screenX; }, { passive: true });
    hero.addEventListener('touchend', e => {
      const dx = e.changedTouches[0].screenX - touchX;
      if (Math.abs(dx) < 40) return;
      show(idx + (dx < 0 ? 1 : -1)); start();
    }, { passive: true });
    show(0); start();
  }

  /* ---------- Flash info ---------- */
  $('#tickerPause')?.addEventListener('click', e => {
    const p = $('#ticker').classList.toggle('is-paused');
    e.currentTarget.innerHTML = `<i class="fa-solid ${p ? 'fa-play' : 'fa-pause'}" aria-hidden="true"></i>`;
    e.currentTarget.setAttribute('aria-label', p ? 'Reprendre le défilement' : 'Mettre le défilement en pause');
  });

  /* ---------- Rangées glissantes ---------- */
  $$('.row').forEach(row => {
    const rail = $('.rail', row); if (!rail) return;
    const [prev, next] = $$('.rail-btn', row);
    const update = () => {
      if (!prev) return;
      prev.disabled = rail.scrollLeft < 8;
      next.disabled = rail.scrollLeft + rail.clientWidth >= rail.scrollWidth - 8;
    };
    $$('.rail-btn', row).forEach(b => b.addEventListener('click', () =>
      rail.scrollBy({ left: +b.dataset.dir * rail.clientWidth * .85, behavior: reduceMotion ? 'auto' : 'smooth' })));
    rail.addEventListener('scroll', update, { passive: true });
    addEventListener('resize', update); update();
    row._update = update;
    let down = false, startX = 0, startL = 0, moved = false;
    rail.addEventListener('pointerdown', e => { if (e.pointerType !== 'mouse') return; down = true; moved = false; startX = e.clientX; startL = rail.scrollLeft; });
    addEventListener('pointermove', e => {
      if (!down) return;
      const dx = e.clientX - startX;
      if (Math.abs(dx) > 5) { moved = true; rail.classList.add('is-dragging'); }
      rail.scrollLeft = startL - dx;
    });
    addEventListener('pointerup', () => { if (!down) return; down = false; setTimeout(() => rail.classList.remove('is-dragging'), 0); });
    rail.addEventListener('click', e => { if (moved) { e.preventDefault(); moved = false; } }, true);
    rail.addEventListener('dragstart', e => e.preventDefault());
  });

  // Filtre par plateforme sur les rangées vidéo de l'accueil
  const rowFilters = $('#rowFilters');
  rowFilters?.addEventListener('click', e => {
    const b = e.target.closest('button'); if (!b) return;
    const pf = b.dataset.pf;
    $$('button', rowFilters).forEach(x => x.setAttribute('aria-pressed', x === b));
    let shown = 0;
    $$('#videoRows .row').forEach(row => {
      let n = 0;
      $$('.card', row).forEach(c => { const ok = pf === 'all' || c.dataset.pf === pf; c.hidden = !ok; n += ok; });
      row.hidden = !n; shown += n;
      const rail = $('.rail', row); if (rail) rail.scrollLeft = 0;
      row._update?.();
    });
    const empty = $('#rowsEmpty');
    if (empty) empty.hidden = shown > 0;
  });

  /* ---------- J'aime (enseignements et actualités) ---------- */
  function setLiked(btn, liked) {
    btn.classList.toggle('is-liked', liked);
    btn.setAttribute('aria-pressed', liked);
    const icon = $('i', btn);
    if (icon) { icon.classList.toggle('fa-solid', liked); icon.classList.toggle('fa-regular', !liked); }
  }
  $$('[data-like-toggle]').forEach(btn => {
    const count = $('.like-count', btn);
    fetch(btn.dataset.likeCheck, { headers: { Accept: 'application/json' } })
      .then(r => r.json())
      .then(d => { setLiked(btn, !!d.isLiked); if (count && typeof d.likes !== 'undefined') count.textContent = d.likes; })
      .catch(() => {});
    btn.addEventListener('click', () => {
      btn.disabled = true;
      fetch(btn.dataset.likeToggle, {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': csrf, 'Content-Type': 'application/json', Accept: 'application/json' },
      })
        .then(r => r.json())
        .then(d => {
          if (!d.success) return;
          if (count) count.textContent = d.likes;
          setLiked(btn, d.isLiked);
          toast(d.message);
        })
        .catch(() => toast('Une erreur s’est produite. Réessayez.', 'error'))
        .finally(() => { btn.disabled = false; });
    });
  });

  /* ---------- Lecteur vidéo : vignette jusqu'au clic ---------- */
  const player = $('#video-player');
  if (player) {
    const load = () => {
      const src = player.dataset.src; if (!src) return;
      const iframe = document.createElement('iframe');
      iframe.src = src;
      iframe.title = player.getAttribute('aria-label') || 'Lecteur vidéo';
      iframe.allow = 'accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share';
      iframe.allowFullscreen = true;
      player.innerHTML = '';
      player.appendChild(iframe);
      player.style.cursor = 'default';
      player.removeAttribute('role');
      player.removeAttribute('tabindex');
    };
    player.addEventListener('click', load);
    player.addEventListener('keydown', e => { if (e.key === 'Enter' || e.key === ' ') { e.preventDefault(); load(); } });
  }
  const descBtn = $('#desc-toggle'), descText = $('#description-text');
  descBtn?.addEventListener('click', () => {
    const clamped = descText.classList.toggle('clamped');
    descBtn.textContent = clamped ? 'Afficher plus' : 'Afficher moins';
    descBtn.setAttribute('aria-expanded', !clamped);
  });

  /* ---------- Copier un lien ---------- */
  $$('[data-copy]').forEach(b => b.addEventListener('click', async () => {
    try { await navigator.clipboard.writeText(b.dataset.copy); toast('Lien copié'); }
    catch { toast('Copiez le lien depuis la barre d’adresse', 'error'); }
  }));

  /* ---------- Demande de prière (le serveur répond en JSON) ---------- */
  const prayer = $('#prayerForm');
  prayer?.addEventListener('submit', async e => {
    e.preventDefault();
    const msg = $('[data-form-msg]', prayer), btn = $('button[type="submit"]', prayer);
    $$('.error', prayer).forEach(x => x.remove());
    $$('[aria-invalid]', prayer).forEach(x => x.removeAttribute('aria-invalid'));
    btn.disabled = true;
    try {
      const res = await fetch(prayer.action, { method: 'POST', headers: { Accept: 'application/json', 'X-CSRF-TOKEN': csrf }, body: new FormData(prayer) });
      const data = await res.json().catch(() => ({}));
      if (res.ok && data.success) {
        prayer.reset();
        msg.className = 'alert ok';
        msg.innerHTML = `<i class="fa-solid fa-circle-check" aria-hidden="true"></i><span>${esc(data.message)}</span>`;
      } else if (res.status === 422 && data.errors) {
        Object.entries(data.errors).forEach(([name, errs]) => {
          const input = prayer.elements[name]; if (!input) return;
          input.setAttribute('aria-invalid', 'true');
          input.insertAdjacentHTML('afterend', `<p class="error">${esc(errs[0])}</p>`);
        });
        msg.className = 'alert err';
        msg.innerHTML = '<i class="fa-solid fa-circle-exclamation" aria-hidden="true"></i><span>Vérifiez les champs signalés.</span>';
        $('[aria-invalid]', prayer)?.focus();
      } else { throw new Error(); }
    } catch {
      msg.className = 'alert err';
      msg.innerHTML = '<i class="fa-solid fa-circle-exclamation" aria-hidden="true"></i><span>L’envoi a échoué. Vérifiez votre connexion et réessayez.</span>';
    }
    msg.hidden = false;
    btn.disabled = false;
    msg.scrollIntoView({ block: 'nearest', behavior: reduceMotion ? 'auto' : 'smooth' });
  });

  /* ---------- Apparition douce des sections ---------- */
  if (!reduceMotion && 'IntersectionObserver' in window) {
    const io = new IntersectionObserver(es => es.forEach(en => {
      if (en.isIntersecting) { en.target.classList.add('reveal'); io.unobserve(en.target); }
    }), { rootMargin: '0px 0px -10% 0px' });
    $$('.sections > section, .sections .row, .page-body > section').forEach(s => io.observe(s));
  }
})();
