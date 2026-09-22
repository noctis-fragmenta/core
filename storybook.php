<?php
/* ============================================================
   CHARDS — Story Page
   URL: /story.php?chapter=1&page=1
   Pulls panels from chards_story_page view, grouped by page.
============================================================ */
chards_require_app_access('storybook');
$dsn = 'mysql:host=db.fr-pari1.bengt.wasmernet.com;port=10272;dbname=dbGQr4derPM6nWoRL4jPwNPN;charset=utf8mb4';
try {
    $pdo = new PDO($dsn, '0074c9f9784980005ded7796ee74', '06950074-c9fa-7db2-8000-c0aec36ba1ac', [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
} catch (PDOException $e) {
    $pdo = null;
}

/* --- fetch all chapters for the nav --- */
$chapters = [];
if ($pdo) {
    $chapters = $pdo->query(
        "SELECT id, slug, name, release_date FROM chards_chapters WHERE is_active = 1 ORDER BY id ASC"
    )->fetchAll();
}

/* --- resolve current chapter --- */
$chapter_id = isset($_GET['chapter']) ? (int)$_GET['chapter'] : ($chapters[0]['id'] ?? 1);
$current_chapter = null;
foreach ($chapters as $ch) {
    if ($ch['id'] === $chapter_id) { $current_chapter = $ch; break; }
}

/* --- fetch all panels for this chapter, grouped by page --- */
$pages = [];  /* $pages[page_number][panel_number] = panel row */
if ($pdo && $current_chapter) {
    $stmt = $pdo->prepare(
        "SELECT * FROM chards_story_page WHERE chapter_id = ? ORDER BY panel_page, panel_number"
    );
    $stmt->execute([$chapter_id]);
    foreach ($stmt->fetchAll() as $panel) {
        $pages[$panel['panel_page']][$panel['panel_number']] = $panel;
    }
}

$total_pages  = count($pages);
$current_page = max(1, min((int)($_GET['page'] ?? 1), $total_pages ?: 1));
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Chards — <?= htmlspecialchars($current_chapter['name'] ?? 'Story') ?></title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Cinzel+Decorative:wght@400;700&family=Crimson+Pro:ital,wght@0,300;0,400;1,300&display=swap" rel="stylesheet">
<style>
  /* ── RESET & BASE ── */
  *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

  :root {
    --ink:        #0f0b08;
    --parchment:  #f5efe0;
    --aged:       #e8dcc8;
    --gold:       #c9a84c;
    --gold-dim:   #7a5f28;
    --shadow:     rgba(0,0,0,0.55);
    --found:      #f5efe0;
    --locked-bg:  #2a2520;
    --locked-num: rgba(201,168,76,0.65);
    --panel-r:    4px;
  }

  html, body {
    height: 100%;
    background: var(--ink);
    color: var(--parchment);
    font-family: 'Crimson Pro', Georgia, serif;
    overflow: hidden; /* no scroll — book is viewport-sized */
  }

  /* ── BACKGROUND TEXTURE ── */
  body::before {
    content: '';
    position: fixed; inset: 0; z-index: 0;
    background:
      radial-gradient(ellipse at 20% 50%, rgba(100,70,20,0.15) 0%, transparent 60%),
      radial-gradient(ellipse at 80% 20%, rgba(80,50,10,0.12) 0%, transparent 55%);
    pointer-events: none;
  }

  /* ── LAYOUT ── */
  .story-wrap {
    position: relative; z-index: 1;
    display: flex; flex-direction: column;
    height: 100vh; padding: 16px 12px 12px;
    gap: 12px;
  }

  /* ── HEADER ── */
  .story-header {
    display: flex; align-items: center; justify-content: space-between;
    flex-shrink: 0;
  }

  .story-title {
    font-family: 'Cinzel Decorative', serif;
    font-size: clamp(13px, 2vw, 20px);
    color: var(--gold);
    letter-spacing: 0.06em;
    text-shadow: 0 0 20px rgba(201,168,76,0.4);
  }

  /* chapter selector */
  .chapter-nav {
    display: flex; gap: 6px; flex-wrap: wrap; justify-content: flex-end;
  }
  .chapter-btn {
    font-family: 'Crimson Pro', serif;
    font-size: 13px;
    padding: 4px 12px;
    border-radius: 20px;
    border: 1px solid var(--gold-dim);
    background: transparent;
    color: var(--parchment);
    cursor: pointer;
    text-decoration: none;
    transition: background 0.2s, color 0.2s;
    opacity: 0.7;
  }
  .chapter-btn:hover, .chapter-btn.active {
    background: var(--gold);
    color: var(--ink);
    opacity: 1;
    border-color: var(--gold);
  }

  /* ── BOOK CONTAINER ── */
  .book-wrap {
    flex: 1;
    display: flex; align-items: center; justify-content: center;
    position: relative;
    min-height: 0;
  }

  /* Arrow buttons */
  .page-arrow {
    position: absolute; top: 50%; transform: translateY(-50%);
    z-index: 20;
    width: 40px; height: 40px;
    border-radius: 50%;
    border: 1.5px solid rgba(201,168,76,0.35);
    background: rgba(15,11,8,0.7);
    color: var(--gold);
    font-size: 20px;
    display: flex; align-items: center; justify-content: center;
    cursor: pointer;
    transition: background 0.2s, border-color 0.2s;
    user-select: none;
  }
  .page-arrow:hover { background: rgba(201,168,76,0.15); border-color: var(--gold); }
  .page-arrow.prev { left: 4px; }
  .page-arrow.next { right: 4px; }
  .page-arrow:disabled { opacity: 0.2; cursor: default; pointer-events: none; }

  /* Book itself */
  .book {
    position: relative;
    width: min(calc(100vw - 100px), calc((100vh - 120px) * 0.78));
    aspect-ratio: 3 / 4; /* matches 3x4 grid */
    perspective: 1200px;
  }

  /* Page — only one shown at a time, transitions via class */
  .book-page {
    position: absolute; inset: 0;
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    grid-template-rows: repeat(4, 1fr);


    overflow: hidden;
    box-shadow:
      0 8px 40px rgba(0,0,0,0.7),
      0 0 0 1px rgba(201,168,76,0.15),
      inset 0 0 60px rgba(0,0,0,0.3);
    opacity: 0;
    pointer-events: none;
    transform: rotateY(8deg) scale(0.97);
    transition: opacity 0.45s ease, transform 0.45s ease;
    will-change: transform, opacity;
  }
  .book-page.active {
    opacity: 1;
    pointer-events: auto;
    transform: rotateY(0deg) scale(1);
  }
  .book-page.exit-left {
    opacity: 0;
    transform: rotateY(-12deg) scale(0.95);
  }
  .book-page.exit-right {
    opacity: 0;
    transform: rotateY(12deg) scale(0.95);
  }

  /* ── PANEL ── */
  .panel {
    position: relative;
    overflow: hidden;

    cursor: default;
  }

  /* Found panel */
  .panel.found {
    cursor: pointer;
    background: #000;
  }
  .panel.found img {
    width: 100%; height: 100%;
    object-fit: cover;
    display: block;
    transition: transform 0.3s ease, filter 0.3s ease;
  }
  .panel.found:hover img {
    transform: scale(1.04);
    filter: brightness(1.1);
  }

  /* Unfound panel */
  .panel.locked {
    background: var(--locked-bg);
    display: flex; align-items: center; justify-content: center;
    border: 1px solid rgba(255,255,255,0.04);
  }
  .panel-number { font-weight: 700;
    font-family: 'Cinzel Decorative', serif;
    font-size: clamp(12px, 2.5vw, 22px);
    color: var(--locked-num);
    user-select: none;
  }

  /* ── PAGE INDICATOR ── */
  .page-indicator {
    flex-shrink: 0;
    display: flex; align-items: center; justify-content: center; gap: 8px;
  }
  .page-dot {
    width: 6px; height: 6px; border-radius: 50%;
    background: var(--gold-dim);
    transition: background 0.2s, transform 0.2s;
  }
  .page-dot.active {
    background: var(--gold);
    transform: scale(1.4);
  }

  /* ── LIGHTBOX ── */
  #panel-lightbox {
    display: none; position: fixed; inset: 0; z-index: 1000;
    align-items: center; justify-content: center;
    background: rgba(10,7,4,0.88);
    cursor: pointer;
    backdrop-filter: blur(4px);
  }
  #panel-lightbox.open { display: flex; }

  .lb-panel {
    position: relative;
    width: min(380px, 88vw);
    cursor: default;
    animation: lbIn 0.35s cubic-bezier(0.34,1.4,0.64,1) forwards;
  }
  @keyframes lbIn {
    from { transform: scale(0.7); opacity: 0; }
    to   { transform: scale(1);   opacity: 1; }
  }
  .lb-panel.closing { animation: lbOut 0.22s ease forwards; }
  @keyframes lbOut {
    from { transform: scale(1);   opacity: 1; }
    to   { transform: scale(0.7); opacity: 0; }
  }

  .lb-img-wrap {
    width: 100%;
    aspect-ratio: 3 / 4;
    border-radius: 8px;
    overflow: hidden;
    box-shadow: 0 20px 60px rgba(0,0,0,0.8);
  }
  .lb-img-wrap img {
    width: 100%; height: 100%; object-fit: cover; display: block;
  }

  .lb-info {
    margin-top: 12px;
    text-align: center;
  }
  .lb-card-name {
    font-family: 'Cinzel Decorative', serif;
    font-size: 17px;
    color: var(--gold);
    margin-bottom: 6px;
  }
  .lb-finder {
    font-size: 15px;
    color: rgba(245,239,224,0.55);
    font-style: italic;
  }
  .lb-finder strong {
    color: var(--parchment);
    font-style: normal;
  }
  .lb-found-date {
    font-size: 13px;
    color: rgba(245,239,224,0.3);
    margin-top: 3px;
  }

  .lb-close {
    position: absolute; top: -12px; right: -12px;
    width: 30px; height: 30px; border-radius: 50%;
    background: rgba(15,11,8,0.9);
    border: 1.5px solid rgba(201,168,76,0.3);
    color: var(--gold);
    font-size: 14px;
    display: flex; align-items: center; justify-content: center;
    cursor: pointer; z-index: 2;
    transition: background 0.2s;
  }
  .lb-close:hover { background: rgba(201,168,76,0.2); }

  /* no pages state */
  .no-panels {
    display: flex; align-items: center; justify-content: center;
    height: 100%;
    font-family: 'Cinzel Decorative', serif;
    font-size: 18px;
    color: var(--gold-dim);
    opacity: 0.6;
  }
</style>
</head>
<body>
<div class="story-wrap">

  <!-- Header -->
  <header class="story-header">
    <div class="story-title">
      <?= htmlspecialchars($current_chapter['name'] ?? 'Chards — Story') ?>
    </div>
	  <a href="/index.php/hub" style="
		  font-family: 'Crimson Pro', serif;
		  font-size: 13px;
		  color: rgba(255,255,255,0.35);
		  text-decoration: none;
		  border: 1px solid rgba(255,255,255,0.1);
		  border-radius: 20px;
		  padding: 4px 14px;
		  transition: color 0.2s, border-color 0.2s;
		" onmouseover="this.style.color='rgba(255,255,255,0.7)';this.style.borderColor='rgba(255,255,255,0.3)'"
		   onmouseout="this.style.color='rgba(255,255,255,0.35)';this.style.borderColor='rgba(255,255,255,0.1)'">
		  ⌂ Hub
		</a>
    <nav class="chapter-nav">
      <?php foreach ($chapters as $ch): ?>
        <a href="?chapter=<?= $ch['id'] ?>&page=1"
           class="chapter-btn <?= $ch['id'] === $chapter_id ? 'active' : '' ?>">
          <?= htmlspecialchars($ch['name']) ?>
        </a>
      <?php endforeach; ?>
    </nav>
  </header>

  <!-- Book -->
  <div class="book-wrap">
    <button class="page-arrow prev" id="btn-prev" aria-label="Previous page">&#8249;</button>

    <div class="book" id="book">
    <nav class="chapter-nav">
      <?php if (empty($pages)): ?>
        <div class="no-panels">No panels found for this chapter yet.</div>
      <?php else: ?>
        <?php foreach ($pages as $page_num => $panels): ?>
          <div class="book-page <?= $page_num === $current_page ? 'active' : '' ?>"
               data-page="<?= $page_num ?>">
            <?php for ($row = 1; $row <= 4; $row++): ?>
              <?php for ($col = 1; $col <= 3; $col++): ?>
                <?php
                  $slot  = ($row - 1) * 3 + $col;  /* panel_number 1–12 */
                  $panel = $panels[$slot] ?? null;
                  $found = $panel && $panel['is_found'];
                ?>
                <?php if ($found): ?>
                  <div class="panel found"
                       data-name="<?= htmlspecialchars($panel['card_name']) ?>"
                       data-img="<?= htmlspecialchars($panel['image_url']) ?>"
                       data-finder="<?= htmlspecialchars($panel['finder_name']) ?>"
                       data-found-at="<?= htmlspecialchars($panel['found_at']) ?>"
                       data-panel="<?= $slot ?>">
                    <img src="<?= htmlspecialchars($panel['image_url']) ?>"
                         alt="Panel <?= $slot ?>">
                  </div>
                <?php else: ?>
                  <div class="panel locked">
                    <span class="panel-number"><?= $slot ?></span>
                  </div>
                <?php endif; ?>
              <?php endfor; ?>
            <?php endfor; ?>
          </div>
        <?php endforeach; ?>
      <?php endif; ?>
    </div>

    <button class="page-arrow next" id="btn-next" aria-label="Next page">&#8250;</button>
  </div>

  <!-- Page dots -->
  <div class="page-indicator" id="page-dots">
    <?php for ($i = 1; $i <= $total_pages; $i++): ?>
      <div class="page-dot <?= $i === $current_page ? 'active' : '' ?>"
           data-page="<?= $i ?>"></div>
    <?php endfor; ?>
  </div>

</div>

<!-- Lightbox -->
<div id="panel-lightbox">
  <div class="lb-panel" id="lb-panel">
    <div class="lb-close" id="lb-close">✕</div>
    <div class="lb-img-wrap">
      <img id="lb-img" src="" alt="Panel">
    </div>
    <div class="lb-info">
      <div class="lb-card-name" id="lb-name"></div>
      <div class="lb-finder">Discovered by <strong id="lb-finder"></strong></div>
      <div class="lb-found-date" id="lb-date"></div>
    </div>
  </div>
</div>

<script>
  const TOTAL_PAGES  = <?= $total_pages ?: 0 ?>;
  let currentPage    = <?= $current_page ?>;

  /* ── PAGE NAVIGATION ── */
  const bookEl   = document.getElementById('book');
  const btnPrev  = document.getElementById('btn-prev');
  const btnNext  = document.getElementById('btn-next');
  const dots     = document.querySelectorAll('.page-dot');

  function getPageEl(n) {
    return bookEl.querySelector(`.book-page[data-page="${n}"]`);
  }

  function goTo(n, direction) {
    if (n < 1 || n > TOTAL_PAGES || n === currentPage) return;

    const outClass = direction === 'next' ? 'exit-left' : 'exit-right';
    const current  = getPageEl(currentPage);
    const next     = getPageEl(n);

    /* exit current */
    current.classList.remove('active');
    current.classList.add(outClass);
    current.addEventListener('transitionend', function h() {
      current.classList.remove(outClass);
      current.removeEventListener('transitionend', h);
    });

    /* enter next from opposite side */
    next.style.transform = direction === 'next' ? 'rotateY(12deg) scale(0.97)' : 'rotateY(-12deg) scale(0.97)';
    next.style.opacity   = '0';
    next.style.pointerEvents = 'none';
    /* force reflow */
    void next.offsetWidth;
    next.style.transform = '';
    next.style.opacity   = '';
    next.classList.add('active');

    /* update dots */
    dots.forEach(d => d.classList.toggle('active', parseInt(d.dataset.page) === n));

    currentPage = n;
    updateArrows();

    /* update URL without reload */
    const url = new URL(window.location);
    url.searchParams.set('page', n);
    history.replaceState(null, '', url);
  }

  function updateArrows() {
    btnPrev.disabled = currentPage <= 1;
    btnNext.disabled = currentPage >= TOTAL_PAGES;
  }

  btnPrev.addEventListener('click', () => goTo(currentPage - 1, 'prev'));
  btnNext.addEventListener('click', () => goTo(currentPage + 1, 'next'));

  dots.forEach(d => d.addEventListener('click', () => {
    const n = parseInt(d.dataset.page);
    goTo(n, n > currentPage ? 'next' : 'prev');
  }));

  updateArrows();

  /* ── SWIPE ── */
  let swipeStartX = null;
  const SWIPE_THRESHOLD = 50;

  bookEl.addEventListener('pointerdown', e => { swipeStartX = e.clientX; });
  bookEl.addEventListener('pointerup', e => {
    if (swipeStartX === null) return;
    const dx = e.clientX - swipeStartX;
    swipeStartX = null;
    if (Math.abs(dx) < SWIPE_THRESHOLD) return;
    dx < 0 ? goTo(currentPage + 1, 'next') : goTo(currentPage - 1, 'prev');
  });

  /* ── LIGHTBOX ── */
  const lightbox  = document.getElementById('panel-lightbox');
  const lbPanel   = document.getElementById('lb-panel');
  const lbImg     = document.getElementById('lb-img');
  const lbName    = document.getElementById('lb-name');
  const lbFinder  = document.getElementById('lb-finder');
  const lbDate    = document.getElementById('lb-date');
  const lbClose   = document.getElementById('lb-close');

  function openLightbox(panel) {
    lbImg.src        = panel.dataset.img;
    lbName.textContent   = panel.dataset.name;
    lbFinder.textContent = panel.dataset.finder;

    /* format the found_at date nicely */
    const d = new Date(panel.dataset.foundAt);
    lbDate.textContent = isNaN(d) ? '' : d.toLocaleDateString('en-GB', {
      day: 'numeric', month: 'long', year: 'numeric'
    });

    lbPanel.classList.remove('closing');
    lightbox.classList.add('open');
  }

  function closeLightbox() {
    lbPanel.classList.add('closing');
    lbPanel.addEventListener('animationend', function h() {
      lightbox.classList.remove('open');
      lbPanel.classList.remove('closing');
      lbPanel.removeEventListener('animationend', h);
    });
  }

  /* delegate click to found panels */
  bookEl.addEventListener('click', e => {
    const panel = e.target.closest('.panel.found');
    if (panel) openLightbox(panel);
  });

  lightbox.addEventListener('click', e => { if (e.target === lightbox) closeLightbox(); });
  lbClose.addEventListener('click',  e => { e.stopPropagation(); closeLightbox(); });

  /* keyboard: left/right arrows, escape */
  document.addEventListener('keydown', e => {
    if (lightbox.classList.contains('open')) {
      if (e.key === 'Escape') closeLightbox();
      return;
    }
    if (e.key === 'ArrowRight') goTo(currentPage + 1, 'next');
    if (e.key === 'ArrowLeft')  goTo(currentPage - 1, 'prev');
  });
</script>
</body>
</html>