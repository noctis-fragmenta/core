<?php
require_once("./app-access-helper.php");

 if (!session_id()) session_start();

chards_require_app_access('pack Opening'); 
/* ============================================================
   DB HELPER
============================================================ */
function pack_db() {
    require_once __DIR__ . '/config/db-connection.php';
    return $pdo;
}

/* ============================================================
   AJAX — commit_pack (slider pulled — write pack to DB, lock cards)
   This is the moment the cooldown clock starts.
============================================================ */
if (isset($_POST['action']) && $_POST['action'] === 'commit_pack') {
    header('Content-Type: application/json');
    $pdo = pack_db();
    if (!$pdo) { echo json_encode(['ok' => false]); exit; }

    $session_id  = session_id();
    $card_ids    = $_SESSION['chards_pending_pack'] ?? [];
    $finder_name = $_SESSION['chards_username']     ?? 'Anonymous';

    if (empty($card_ids)) { echo json_encode(['ok' => false, 'msg' => 'No pack in session']); exit; }

    /* Check if already committed (double-fire protection) */
    $existing = $pdo->prepare("SELECT id FROM chards_packs WHERE session_id = ? AND status = 'pending'");
    $existing->execute([$session_id]);
    if ($existing->fetch()) { echo json_encode(['ok' => true, 'already' => true]); exit; }

$pdo->beginTransaction();
    try {
        /* Lock story cards & register finds */
        $story_ids = $_SESSION['chards_pending_story'] ?? [];
        foreach ($story_ids as $sid) {
            $pdo->prepare("UPDATE chards_cards SET is_available = 0 WHERE id = ? AND card_type = 'story'")->execute([$sid]);
            $pdo->prepare("INSERT IGNORE INTO chards_story_finds (card_id, finder_name) VALUES (?,?)")->execute([$sid, $finder_name]);
        }

        /* Store pack in DB */
        $pdo->prepare(
            "INSERT INTO chards_packs (session_id, finder_name, card_ids, status) VALUES (?,?,?,'pending')"
        )->execute([$session_id, $finder_name, implode(',', $card_ids)]);

        /* Add cards to ownership if user is logged in */
        $owner_id = $_SESSION['chards_user_id'] ?? null;
        if ($owner_id && !empty($card_ids)) {
            $own_stmt = $pdo->prepare("
                INSERT INTO chards_card_ownership (user_id, card_id, quantity)
                VALUES (?, ?, 1)
                ON DUPLICATE KEY UPDATE quantity = quantity + 1
            ");
            foreach ($card_ids as $cid) {
                if ($cid) $own_stmt->execute([$owner_id, $cid]);
            }
        }

        $pdo->commit();
        echo json_encode(['ok' => true]);
    } catch (PDOException $e) {
        $pdo->rollBack();
        echo json_encode(['ok' => false, 'msg' => $e->getMessage()]);
    }
    exit;
}

/* ============================================================
   AJAX — close_pack (all cards dismissed)
============================================================ */
if (isset($_POST['action']) && $_POST['action'] === 'close_pack') {
    header('Content-Type: application/json');
    $pdo = pack_db();
    if ($pdo) {
        $pdo->prepare("UPDATE chards_packs SET status = 'opened' WHERE session_id = ? AND status = 'pending'")->execute([session_id()]);
    }
    /* Clear session pack data */
    unset($_SESSION['chards_pending_pack']);
    unset($_SESSION['chards_pending_story']);
    echo json_encode(['ok' => true]);
    exit;
}

/* ============================================================
   PACK CONFIGURATION — edit these to tune pack behaviour
============================================================ */
$NUM_CARDS      = 5;    /* total cards per pack */
$STORY_CHANCE   = 10;  /* % chance a story card appears (0-100) */
$DEFAULT_BORDER = '/media/Borders/Common.png';
$finder_name    = $_SESSION['chards_username'] ?? 'Anonymous';
$session_id     = session_id();

/* DEV MODE — set to 1 to get a fresh pack on every refresh (ignores cooldown/session)
   set to 0 for normal behaviour (same pack until cooldown expires) */
$DEV_NEW_PACK_ON_REFRESH = 1;

/* ============================================================
   PACK GENERATION — session only on page load
   No DB writes here. Cards are drawn and stored in $_SESSION.
   The DB commit happens when the slider is pulled (commit_pack).
   If a pending pack exists in DB (slider already pulled on a
   previous load), resume from DB instead.
============================================================ */
$pack_cards = [];
$pdo        = pack_db();

if ($pdo) {

    /* ── Load rarities ── */
    $rarities_raw = $pdo->query(
        "SELECT id, slug, label, border_url, sort_order FROM chards_rarities ORDER BY sort_order ASC"
    )->fetchAll();
    $rarities_by_id = [];
    foreach ($rarities_raw as $r) $rarities_by_id[$r['id']] = $r;

    $story_rarity = null;
    foreach ($rarities_raw as $r) {
        if ($r['slug'] === 'story') { $story_rarity = $r; break; }
    }
    if (!$story_rarity) $story_rarity = end($rarities_raw);

    /* ── DEV: wipe session pack so a fresh draw happens on every load ── */
    if ($DEV_NEW_PACK_ON_REFRESH) {
        unset($_SESSION['chards_pending_pack']);
        unset($_SESSION['chards_pending_story']);
    }

    /* ── Check if slider was already pulled (pack committed to DB) ── */
    $stmt = $pdo->prepare("SELECT * FROM chards_packs WHERE session_id = ? AND status = 'pending' ORDER BY created_at DESC LIMIT 1");
    $stmt->execute([$session_id]);
    $committed_pack = $stmt->fetch();

    if ($committed_pack) {
        /* Slider already pulled on a previous load — resume from DB */
        $card_ids = array_values(array_filter(explode(',', $committed_pack['card_ids'])));

    } elseif (!empty($_SESSION['chards_pending_pack'])) {
        /* Slider not yet pulled but cards already drawn this session — reuse */
        $card_ids = $_SESSION['chards_pending_pack'];

    } else {
        /* ── Fresh draw — session only, no DB write ── */
        $story_card_id     = null;
        $story_ids         = [];
        $collectible_slots = $NUM_CARDS;

        /* Roll for story card */
        if (mt_rand(1, 1) <= $STORY_CHANCE) {
            $story_row = $pdo->query(
                "SELECT id FROM chards_cards
                 WHERE card_type = 'story' AND is_available = 1 AND is_active = 1
                 ORDER BY RAND() LIMIT 1"
            )->fetch();
            if ($story_row) {
                $story_card_id     = $story_row['id'];
                $story_ids[]       = $story_card_id;
                $collectible_slots = $NUM_CARDS - 1;
            }
        }

        /* Draw collectibles — weighted */
        $collectibles = $pdo->query(
            "SELECT c.id, c.rarity_id, r.drop_weight, r.sort_order
             FROM chards_cards c
             JOIN chards_rarities r ON r.id = c.rarity_id
             WHERE c.card_type = 'collectible' AND c.is_available = 1 AND c.is_active = 1"
        )->fetchAll();

        $chosen = [];
        $pool   = $collectibles;
        for ($i = 0; $i < $collectible_slots && !empty($pool); $i++) {
            $total = array_sum(array_column($pool, 'drop_weight'));
            $roll  = mt_rand(1, max(1, $total));
            $cumul = 0;
            foreach ($pool as $key => $card) {
                $cumul += (int)$card['drop_weight'];
                if ($roll <= $cumul) {
                    $chosen[] = $card;
                    unset($pool[$key]);
                    $pool = array_values($pool);
                    break;
                }
            }
        }

        usort($chosen, function($a, $b) { return (int)$a['sort_order'] - (int)$b['sort_order']; });

        $card_ids = array_column($chosen, 'id');
        if ($story_card_id) $card_ids[] = $story_card_id;

        /* Store in session — no DB write yet */
        $_SESSION['chards_pending_pack']  = $card_ids;
        $_SESSION['chards_pending_story'] = $story_ids;
    }

    /* ── Load full card data ── */
    if (!empty($card_ids)) {
        $placeholders = implode(',', array_fill(0, count($card_ids), '?'));
        $stmt = $pdo->prepare(
            "SELECT c.id, c.image_url, c.card_type, c.rarity_id,
                    r.border_url, r.slug AS rarity_slug, r.label AS rarity_label
             FROM chards_cards c
             JOIN chards_rarities r ON r.id = c.rarity_id
             WHERE c.id IN ({$placeholders})"
        );
        $stmt->execute($card_ids);
        $cards_keyed = [];
        foreach ($stmt->fetchAll() as $c) $cards_keyed[$c['id']] = $c;

        foreach ($card_ids as $cid) {
            $c = $cards_keyed[$cid] ?? null;
            $pack_cards[] = [
                'card_id'      => $cid,
                'image_url'    => $c['image_url'] ?? '',
                'border_url'   => $c ? ($c['border_url'] ?: $DEFAULT_BORDER) : $DEFAULT_BORDER,
                'rarity_slug'  => $c['rarity_slug']  ?? 'common',
                'rarity_label' => $c['rarity_label'] ?? 'Common',
            ];
        }
    }
}

/* Fill any gaps */
while (count($pack_cards) < $NUM_CARDS) {
    $pack_cards[] = [
        'card_id'      => null,
        'image_url'    => '',
        'border_url'   => $DEFAULT_BORDER,
        'rarity_slug'  => 'common',
        'rarity_label' => 'Common',
    ];
}

$card_borders = $pack_cards;
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<style>
  * { box-sizing: border-box; margin: 0; padding: 0; }

	html, body {
	  background-image: url('/media/background.png');
	  background-size: cover;
	  background-position: center;
	  background-attachment: fixed;
	}
  .rotate-toggle, .fade-toggle { display: none; }

  .card {
    width: 300px; height: 400px;
    position: absolute; top: 0; left: 0;
    transform-style: preserve-3d;
    transition: transform 0.6s, opacity 0.6s;
    cursor: pointer;
  }
  .card-inner {
    width: 100%; height: 100%;
    position: absolute; border-radius: 10px;
    transform-style: preserve-3d; transition: transform 0.6s;
  }
  .card-front, .card-back {
    position: absolute; backface-visibility: hidden;
    border-radius: 10px;
    overflow: visible;
  }
  .card-back  { width: 100%; height: 100%; transform: rotateY(0deg); }
  .card-front { width: 100%; height: 100%; transform: rotateY(180deg); }
  .card-front img:not(.border), .card-back img { width: 100%; height: 100%; object-fit: cover; }
  .card-inner:hover { transform: translateY(-10px); }

  .rotate-toggle-1:checked ~ .card-1 .card-inner { transform: rotateY(180deg) scale(1.2); }
  .rotate-toggle-2:checked ~ .card-2 .card-inner { transform: rotateY(180deg) scale(1.2); }
  .rotate-toggle-3:checked ~ .card-3 .card-inner { transform: rotateY(180deg) scale(1.2); }
  .rotate-toggle-4:checked ~ .card-4 .card-inner { transform: rotateY(180deg) scale(1.2); }
  .rotate-toggle-5:checked ~ .card-5 .card-inner { transform: rotateY(180deg) scale(1.2); }

  .fade-toggle-1:checked ~ .card-1 { opacity: 0; transform: translateY(-50px); pointer-events: none; }
  .fade-toggle-2:checked ~ .card-2 { opacity: 0; transform: translateY(-50px); pointer-events: none; }
  .fade-toggle-3:checked ~ .card-3 { opacity: 0; transform: translateY(-50px); pointer-events: none; }
  .fade-toggle-4:checked ~ .card-4 { opacity: 0; transform: translateY(-50px); pointer-events: none; }
  .fade-toggle-5:checked ~ .card-5 { opacity: 0; transform: translateY(-50px); pointer-events: none; }

  .overlay {
    position: absolute; top: 0; left: 0; width: 100%; height: 100%;
    cursor: pointer; z-index: 1; display: none;
  }
  .rotate-toggle-1:checked ~ .overlay-1 { display: block; }
  .rotate-toggle-2:checked ~ .overlay-2 { display: block; }
  .rotate-toggle-3:checked ~ .overlay-3 { display: block; }
  .rotate-toggle-4:checked ~ .overlay-4 { display: block; }
  .rotate-toggle-5:checked ~ .overlay-5 { display: block; }
  .fade-toggle-1:checked ~ .overlay-1 { display: none; }
  .fade-toggle-2:checked ~ .overlay-2 { display: none; }
  .fade-toggle-3:checked ~ .overlay-3 { display: none; }
  .fade-toggle-4:checked ~ .overlay-4 { display: none; }
  .fade-toggle-5:checked ~ .overlay-5 { display: none; }

  .card-1 { z-index: 10; } .card-2 { z-index: 9; } .card-3 { z-index: 8; }
  .card-4 { z-index: 7; }  .card-5 { z-index: 6; }
  .overlay-1 { z-index: 10; transform: scale(1.2); }
  .overlay-2 { z-index: 9;  transform: scale(1.2); }
  .overlay-3 { z-index: 8;  transform: scale(1.2); }
  .overlay-4 { z-index: 7;  transform: scale(1.2); }
  .overlay-5 { z-index: 6;  transform: scale(1.2); }

  .card-image { width: 100%; height: 100%; object-fit: cover; }
  .container  { display: flex; justify-content: center; height: 100vh; margin-top: 10%; }

  .tear-container { position: absolute; width: 400px; height: 100px; z-index: 20; }
  .card-container { width: 300px; height: 400px; position: absolute; top: 70px; left: 45px; z-index: 2; }
  .tear-bottom    { width: 400px; height: 550px; position: absolute; top: 18px; z-index: 2; }

  .tear-top {
    position: absolute; width: 100%; height: 100%;
    z-index: 3; overflow: hidden; pointer-events: none;
    transform-origin: top center;
  }

  .hidden-checkbox { display: none; }
  #box1.tearing { pointer-events: none; }
  #box2.tearing { pointer-events: none; }

  .card-art-inner {
    position: absolute;
    top: 0; left: 0;
    width: 100%; height: 100%;
    border-radius: 10px;
    overflow: hidden;
  }
  .card-art-inner img {
    width: 100%; height: 100%;
    object-fit: cover;
  }

  .card-front > img.border {
    position: absolute;
    z-index: 2;
    top: -25%; left: -25%;
    width: 150%; height: 150%;
    object-fit: fill;
    pointer-events: none;
  }
  .back-card { position: absolute; z-index: 1; top: 0; left: 0; }

  /* ===== SWIPE SLIDER ===== */
  #slider-wrap {
    position: absolute;
    top: 28px; left: 0;
    width: 400px; height: 44px;
    z-index: 20;
    display: flex; align-items: center; justify-content: center;
  }
  #slider-track {
    position: relative;
    width: 388px; height: 36px;
    background: rgba(0,0,0,0.35);
    border: 1.5px solid rgba(255,255,255,0.2);
    border-radius: 18px;
    display: flex; align-items: center;
    overflow: hidden;
    backdrop-filter: blur(4px);
  }
  #slider-fill {
    position: absolute; left: 0; top: 0; bottom: 0;
    width: 0%;
    background: rgba(255,255,255,0.08);
    border-radius: 18px;
    pointer-events: none;
    transition: width 0.05s linear;
  }
  #slider-hint {
    position: absolute; left: 0; right: 0;
    display: flex; align-items: center; justify-content: center; gap: 6px;
    pointer-events: none; transition: opacity 0.3s;
  }
  #slider-hint span {
    font-family: sans-serif; font-size: 11px;
    color: rgba(255,255,255,0.5);
    letter-spacing: 0.1em; text-transform: uppercase;
  }
  .chev { display: inline-block; font-size: 14px; color: rgba(255,255,255,0.35); animation: nudge 1.5s ease-in-out infinite; }
  .chev:nth-child(2) { animation-delay: 0.15s; }
  .chev:nth-child(3) { animation-delay: 0.3s; }
  @keyframes nudge {
    0%,100% { transform: translateX(0); opacity:0.3; }
    50%      { transform: translateX(5px); opacity:0.9; }
  }
  #slider-thumb {
    position: absolute; left: 5px;
    width: 30px; height: 30px;
    border-radius: 50%; background: #fff;
    box-shadow: 0 2px 12px rgba(0,0,0,0.5);
    cursor: grab;
    display: flex; align-items: center; justify-content: center;
    user-select: none; touch-action: none; z-index: 2;
    transition: background 0.2s, box-shadow 0.2s;
  }
  #slider-thumb:active { cursor: grabbing; }
  #slider-thumb.success { background: #6c4fff; box-shadow: 0 0 18px rgba(108,79,255,0.7); }
  #slider-thumb svg { pointer-events: none; }

  /* ===== SIDE DECK ===== */
  #side-deck {
    position: fixed; right: 20px; top: 50%;
    transform: translateY(-50%); width: 130px;
    z-index: 5; display: flex; flex-direction: column; align-items: center;
  }
  #side-deck-label {
    font-family: sans-serif; font-size: 11px;
    color: rgba(255,255,255,0.45); margin-bottom: 14px;
    letter-spacing: 0.1em; text-transform: uppercase;
  }
  #side-deck-stack { position: relative; width: 100px; height: 20px; transition: height 0.4s ease; }
  .deck-card {
    width: 90px; height: 120px; border-radius: 7px;
    overflow: visible; position: absolute; left: 5px;
    box-shadow: 0 4px 16px rgba(0,0,0,0.6);
    border: 1.5px solid rgba(255,255,255,0.2);
    cursor: pointer; transition: transform 0.25s ease, box-shadow 0.25s ease;
  }
  .deck-card-inner { position: relative; width: 100%; height: 100%; border-radius: 6px; overflow: hidden; }
  .deck-card:hover { box-shadow: 0 10px 28px rgba(0,0,0,0.75); z-index: 500 !important; }
  .deck-card img   { width: 100%; height: 100%; object-fit: cover; display: block; }
  #deck-count {
    font-family: sans-serif; font-size: 12px;
    color: rgba(255,255,255,0.55); margin-top: 16px; font-weight: 500;
  }

  /* ===== FLYING CARD ===== */
  .flying-card {
    position: fixed; border-radius: 8px; overflow: hidden;
    pointer-events: none; z-index: 9999;
    box-shadow: 0 8px 24px rgba(0,0,0,0.6);
  }

  /* ===== LIGHTBOX ===== */
  #lightbox {
    display: none; position: fixed; inset: 0;
    background: transparent; z-index: 10000;
    align-items: center; justify-content: center; cursor: pointer;
  }
  #lightbox.open { display: flex; }
  #lightbox-card {
    position: relative; width: 300px; height: 400px;
    border-radius: 12px;
    box-shadow: 0 24px 70px rgba(0,0,0,0.85); cursor: default;
    animation: lbIn 0.38s cubic-bezier(0.34,1.5,0.64,1) forwards;
  }
  @keyframes lbIn {
    from { transform: scale(0.5) rotate(-8deg); opacity:0; }
    to   { transform: scale(1)   rotate(0deg);  opacity:1; }
  }
  @keyframes lbOut {
    from { transform: scale(1)   rotate(0deg);  opacity:1; }
    to   { transform: scale(0.5) rotate(5deg);  opacity:0; }
  }
  #lightbox-card.closing { animation: lbOut 0.22s ease forwards; }
  #lightbox-art    { position:absolute; top:0; left:0; width:100%; height:100%; z-index:1; object-fit:cover; }
  #lightbox-border { position:absolute; top:-25%; left:-25%; width:150%; height:150%; z-index:2; object-fit:fill; pointer-events:none; }
  #lightbox-close {
    position:absolute; top:-12px; right:-12px; width:30px; height:30px;
    border-radius:50%; background:rgba(30,30,50,0.9);
    border:1.5px solid rgba(255,255,255,0.25); color:rgba(255,255,255,0.85);
    font-size:14px; display:flex; align-items:center; justify-content:center;
    cursor:pointer; z-index:3; font-family:sans-serif; transition:background 0.2s;
  }
  #lightbox-close:hover { background:rgba(80,80,120,0.95); }

  /* ===== END SCREEN ===== */
  #end-screen {
    display: none; position: fixed; inset: 0; z-index: 5000;
    align-items: center; justify-content: center;
    flex-direction: column; gap: 28px;
    background: rgba(10,8,15,0.72);
    backdrop-filter: blur(6px);
    animation: fadeIn 0.4s ease forwards;
  }
  #end-screen.show { display: flex; }

  #pack-countdown { display: flex; flex-direction: column; align-items: center; gap: 8px; }
  #countdown-label {
    font-family: sans-serif; font-size: 11px;
    letter-spacing: 0.14em; text-transform: uppercase;
    color: rgba(255,255,255,0.35);
  }
  #countdown-timer {
    font-family: 'Courier New', monospace; font-size: 42px; font-weight: 700;
    color: rgba(255,255,255,0.15); letter-spacing: 0.05em; line-height: 1;
  }
  #countdown-sub {
    font-family: sans-serif; font-size: 11px;
    color: rgba(255,255,255,0.2); letter-spacing: 0.08em;
  }

  #end-btns { display: flex; gap: 14px; flex-wrap: wrap; justify-content: center; }

  .end-btn {
    padding: 13px 32px; font-family: sans-serif; font-size: 14px;
    font-weight: 600; letter-spacing: 0.05em; border-radius: 50px;
    cursor: pointer; border: none; text-decoration: none;
    display: inline-flex; align-items: center; gap: 8px;
    transition: transform 0.15s, box-shadow 0.15s;
  }
  .end-btn:hover { transform: translateY(-2px); }
  .end-btn:active { transform: scale(0.97); }
  .end-btn-story {
    background: linear-gradient(135deg, #5b3fbe 0%, #3a2a8a 100%);
    color: #fff; box-shadow: 0 6px 28px rgba(80,50,200,0.45);
  }
  .end-btn-story:hover { box-shadow: 0 8px 36px rgba(100,70,220,0.65); }
  .end-btn-collection {
    background: transparent; color: rgba(255,255,255,0.6);
    border: 1.5px solid rgba(255,255,255,0.18);
  }
  .end-btn-collection:hover { color: rgba(255,255,255,0.9); border-color: rgba(255,255,255,0.4); }

  @keyframes fadeIn {
    from { opacity:0; } to { opacity:1; }
  }
</style>
</head>
<body>

<div class="container">
  <div class="tear-container">

    <div class="card-container" id="card-container">
      <!-- Card 1 -->
      <input type="checkbox" id="rotate-toggle-1" class="rotate-toggle rotate-toggle-1">
      <input type="checkbox" id="fade-toggle-1"   class="fade-toggle  fade-toggle-1">
      <label for="rotate-toggle-1" class="card card-1" id="card-el-1"
             data-rarity="<?= htmlspecialchars($card_borders[0]['rarity_slug']) ?>"
             data-rarity-label="<?= htmlspecialchars($card_borders[0]['rarity_label']) ?>">
        <div class="card-inner">
          <div class="card-front">
            <img src="<?= htmlspecialchars($card_borders[0]['border_url']) ?>" class="border">
            <div class="card-art-inner">
              <img src="<?= htmlspecialchars($card_borders[0]['image_url']) ?>" alt="Front" class="back-card">
            </div>
          </div>
          <div class="card-back">
            <img src="/media/cardback.png" alt="Back">
          </div>
        </div>
      </label>
      <label for="fade-toggle-1" class="overlay overlay-1" id="overlay-el-1"></label>

      <!-- Card 2 -->
      <input type="checkbox" id="rotate-toggle-2" class="rotate-toggle rotate-toggle-2">
      <input type="checkbox" id="fade-toggle-2"   class="fade-toggle  fade-toggle-2">
      <label for="rotate-toggle-2" class="card card-2" id="card-el-2"
             data-rarity="<?= htmlspecialchars($card_borders[1]['rarity_slug']) ?>"
             data-rarity-label="<?= htmlspecialchars($card_borders[1]['rarity_label']) ?>">
        <div class="card-inner">
          <div class="card-front">
            <img src="<?= htmlspecialchars($card_borders[1]['border_url']) ?>" class="border">
            <div class="card-art-inner">
              <img src="<?= htmlspecialchars($card_borders[1]['image_url']) ?>" alt="Front" class="back-card">
            </div>
          </div>
          <div class="card-back">
            <img src="/media/cardback.png" alt="Back">
          </div>
        </div>
      </label>
      <label for="fade-toggle-2" class="overlay overlay-2" id="overlay-el-2"></label>

      <!-- Card 3 -->
      <input type="checkbox" id="rotate-toggle-3" class="rotate-toggle rotate-toggle-3">
      <input type="checkbox" id="fade-toggle-3"   class="fade-toggle  fade-toggle-3">
      <label for="rotate-toggle-3" class="card card-3" id="card-el-3"
             data-rarity="<?= htmlspecialchars($card_borders[2]['rarity_slug']) ?>"
             data-rarity-label="<?= htmlspecialchars($card_borders[2]['rarity_label']) ?>">
        <div class="card-inner">
          <div class="card-front">
            <img src="<?= htmlspecialchars($card_borders[2]['border_url']) ?>" class="border">
            <div class="card-art-inner">
              <img src="<?= htmlspecialchars($card_borders[2]['image_url']) ?>" alt="Front" class="back-card">
            </div>
          </div>
          <div class="card-back">
            <img src="/media/cardback.png" alt="Back">
          </div>
        </div>
      </label>
      <label for="fade-toggle-3" class="overlay overlay-3" id="overlay-el-3"></label>

      <!-- Card 4 -->
      <input type="checkbox" id="rotate-toggle-4" class="rotate-toggle rotate-toggle-4">
      <input type="checkbox" id="fade-toggle-4"   class="fade-toggle  fade-toggle-4">
      <label for="rotate-toggle-4" class="card card-4" id="card-el-4"
             data-rarity="<?= htmlspecialchars($card_borders[3]['rarity_slug']) ?>"
             data-rarity-label="<?= htmlspecialchars($card_borders[3]['rarity_label']) ?>">
        <div class="card-inner">
          <div class="card-front">
            <img src="<?= htmlspecialchars($card_borders[3]['border_url']) ?>" class="border">
            <div class="card-art-inner">
              <img src="<?= htmlspecialchars($card_borders[3]['image_url']) ?>" alt="Front" class="back-card">
            </div>
          </div>
          <div class="card-back">
            <img src="/media/cardback.png" alt="Back">
          </div>
        </div>
      </label>
      <label for="fade-toggle-4" class="overlay overlay-4" id="overlay-el-4"></label>

      <!-- Card 5 -->
      <input type="checkbox" id="rotate-toggle-5" class="rotate-toggle rotate-toggle-5">
      <input type="checkbox" id="fade-toggle-5"   class="fade-toggle  fade-toggle-5">
      <label for="rotate-toggle-5" class="card card-5" id="card-el-5"
             data-rarity="<?= htmlspecialchars($card_borders[4]['rarity_slug']) ?>"
             data-rarity-label="<?= htmlspecialchars($card_borders[4]['rarity_label']) ?>">
        <div class="card-inner">
          <div class="card-front">
            <img src="<?= htmlspecialchars($card_borders[4]['border_url']) ?>" class="border">
            <div class="card-art-inner">
              <img src="<?= htmlspecialchars($card_borders[4]['image_url']) ?>" alt="Front" class="back-card">
            </div>
          </div>
          <div class="card-back">
            <img src="/media/cardback.png" alt="Back">
          </div>
        </div>
      </label>
      <label for="fade-toggle-5" class="overlay overlay-5" id="overlay-el-5"></label>
    </div>

    <input type="checkbox" id="trigger" class="hidden-checkbox">

    <label for="trigger" class="tear-top" id="box1">
      <img src="/media/packtop.png" alt="Pack Top" class="card-image">
    </label>
    <div class="tear-bottom" id="box2">
      <img src="/media/packbottom.png" alt="Pack Bottom" class="card-image">
    </div>

    <div id="slider-wrap">
      <div id="slider-track">
        <div id="slider-fill"></div>
        <div id="slider-hint">
          <span class="chev">›</span><span class="chev">›</span><span class="chev">›</span>
          <span>Swipe to open</span>
        </div>
        <div id="slider-thumb">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#555" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <circle cx="6" cy="6" r="3"/><circle cx="6" cy="18" r="3"/>
            <line x1="20" y1="4" x2="8.12" y2="15.88"/>
            <line x1="14.47" y1="14.48" x2="20" y2="20"/>
            <line x1="8.12" y1="8.12" x2="12" y2="12"/>
          </svg>
        </div>
      </div>
    </div>

  </div>
</div>

<!-- Side Deck -->
<div id="side-deck">
  <div id="side-deck-label">Collected</div>
  <div id="side-deck-stack"></div>
  <div id="deck-count">0 / 5</div>
</div>

<!-- End Screen -->
<div id="end-screen">
  <div id="pack-countdown">
    <div id="countdown-label">Next pack available in</div>
    <div id="countdown-timer">00:00:00</div>
    <div id="countdown-sub">Come back soon, Citizen</div>
  </div>
  <div id="end-btns">
    <a href="/index.php/story" class="end-btn end-btn-story">✦ View Story</a>
    <a href="/index.php/collection" class="end-btn end-btn-collection">◈ View Collection</a>
    <a href="/index.php/hub" class="end-btn end-btn-hub">⌂ Return to Hub</a>
  </div>
</div>

<!-- Lightbox -->
<div id="lightbox">
  <div id="lightbox-card">
    <div id="lightbox-close">✕</div>
    <img id="lightbox-art" src="" alt="Card art">
    <img id="lightbox-border" src="/media/border1.png" alt="">
  </div>
</div>

<script>
  /* ============================================================
     SLIDER
  ============================================================ */
  const track     = document.getElementById('slider-track');
  const thumb     = document.getElementById('slider-thumb');
  const fill      = document.getElementById('slider-fill');
  const hint      = document.getElementById('slider-hint');
  const triggerCb = document.getElementById('trigger');

  const THUMB_W   = 30;
  const THRESHOLD = 0.88;
  let dragging    = false;
  let startX      = 0;
  let currentX    = 0;
  let fired       = false;

  function trackMax() { return track.offsetWidth - THUMB_W - 10; }

  function setThumb(px) {
    const max     = trackMax();
    const clamped = Math.max(0, Math.min(px, max));
    thumb.style.left   = (clamped + 5) + 'px';
    const pct          = clamped / max;
    fill.style.width   = (pct * 100) + '%';
    hint.style.opacity = Math.max(0, 1 - pct * 2.5);
    return pct;
  }

  function snapBack() {
    thumb.style.transition = 'left 0.35s cubic-bezier(0.34,1.4,0.64,1)';
    fill.style.transition  = 'width 0.35s cubic-bezier(0.34,1.4,0.64,1)';
    setThumb(0);
    hint.style.opacity = '1';
    setTimeout(function() { thumb.style.transition = ''; fill.style.transition = ''; }, 380);
  }

  function fireTear() {
    if (fired) return;
    fired = true;
    thumb.classList.add('success');
    thumb.style.transition = 'left 0.15s ease, background 0.2s';
    setThumb(trackMax());
    setTimeout(function() {
      document.getElementById('slider-wrap').style.transition  = 'opacity 0.4s';
      document.getElementById('slider-wrap').style.opacity     = '0';
      document.getElementById('slider-wrap').style.pointerEvents = 'none';
    }, 300);

    /* Commit pack to DB — cooldown starts here */
    fetch(window.location.href, {
      method: 'POST',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
      body: 'action=commit_pack'
    }).catch(function() {});

    triggerCb.checked = true;
    void triggerCb.offsetWidth;
    runTearAnimation();
  }

  /* ============================================================
     TEAR ANIMATION
  ============================================================ */
  function runTearAnimation() {
    const box1 = document.getElementById('box1');
    const box2 = document.getElementById('box2');
    const W = 400, H = 580, SEAM = 72;

    function topClip(dy) { return `polygon(0px 0px, ${W}px 0px, ${W}px ${SEAM - dy}px, 0px ${SEAM - dy}px)`; }
    function botClip(dy) { return `polygon(0px ${SEAM - dy}px, ${W}px ${SEAM - dy}px, ${W}px ${H}px, 0px ${H}px)`; }

    const DURATION = 1700;
    let start = null;

    function easeOutCubic(t) { return 1 - Math.pow(1 - t, 3); }
    function easeOutBack(t) {
      const c1 = 1.70158, c3 = c1 + 1;
      return 1 + c3 * Math.pow(t - 1, 3) + c1 * Math.pow(t - 1, 2);
    }

    function frame(ts) {
      if (!start) start = ts;
      const p = Math.min((ts - start) / DURATION, 1);

      const p1  = Math.min(p / 0.55, 1);
      const t1  = easeOutCubic(p1);
      const topY   = -t1 * 140;
      const topRot = t1 * 8;
      const topOpa = p1 < 0.6 ? 1 : 1 - ((p1 - 0.6) / 0.4);
      box1.style.transform = `translateY(${topY}px) rotate(${topRot}deg)`;
      box1.style.opacity   = topOpa;
      box1.style.clipPath  = topClip(topY);

      const p2raw = Math.max(0, (p - 0.08) / 0.65);
      const p2    = Math.min(p2raw, 1);
      const t2    = easeOutBack(p2);
      const botY  = t2 * 480;
      const botOpa = p2 < 0.75 ? 1 : 1 - ((p2 - 0.75) / 0.25);
      box2.style.transform = `translateY(${botY}px)`;
      box2.style.opacity   = botOpa;
      box2.style.clipPath  = botClip(botY);

      if (p < 1) { requestAnimationFrame(frame); }
      else { box1.style.display = 'none'; box2.style.display = 'none'; }
    }

    requestAnimationFrame(frame);
  }

  thumb.addEventListener('pointerdown', function(e) {
    if (fired) return;
    dragging = true; startX = e.clientX; currentX = 0;
    thumb.setPointerCapture(e.pointerId); e.preventDefault();
  });
  thumb.addEventListener('pointermove', function(e) {
    if (!dragging || fired) return;
    currentX = e.clientX - startX;
    const pct = setThumb(currentX);
    if (pct >= THRESHOLD) fireTear();
  });
  thumb.addEventListener('pointerup', function() { if (!dragging) return; dragging = false; if (!fired) snapBack(); });
  thumb.addEventListener('pointercancel', function() { if (!dragging) return; dragging = false; if (!fired) snapBack(); });

  /* ============================================================
     CARD DECK & LIGHTBOX
  ============================================================ */
  const NUM_CARDS = 5;
  const CARD_W    = 90;
  const CARD_H    = 120;
  const SPACING   = 108;
  const TILTS     = [-5, 3, -3, 4, -2];

  const deckStack = document.getElementById('side-deck-stack');
  const deckCount = document.getElementById('deck-count');
  const lightbox  = document.getElementById('lightbox');
  const lbCard    = document.getElementById('lightbox-card');
  const lbArt     = document.getElementById('lightbox-art');
  const lbClose   = document.getElementById('lightbox-close');
  let collectedCount = 0;

  function getCardFrontSrc(idx) {
    const el  = document.getElementById('card-el-' + idx);
    const img = el.querySelector('.card-front .card-art-inner .back-card');
    return img ? img.src : '';
  }
  function getCardBorderSrc(idx) {
    const el  = document.getElementById('card-el-' + idx);
    const img = el.querySelector('.card-front > .border');
    return img ? img.src : '';
  }

  function openLightbox(artSrc, borderSrc) {
    lbArt.src = artSrc;
    document.getElementById('lightbox-border').src = borderSrc || '';
    lbCard.classList.remove('closing');
    lightbox.classList.add('open');
  }
  function closeLightbox() {
    lbCard.classList.add('closing');
    lbCard.addEventListener('animationend', function h() {
      lightbox.classList.remove('open');
      lbCard.classList.remove('closing');
      lbCard.removeEventListener('animationend', h);
    });
  }
  lightbox.addEventListener('click', function(e) { if (e.target === lightbox) closeLightbox(); });
  lbClose.addEventListener('click',  function(e) { e.stopPropagation(); closeLightbox(); });

  function addToDeck(cardIndex, frontSrc, borderSrc) {
    const slot   = collectedCount;
    collectedCount++;
    const topPos = slot * SPACING;
    const tilt   = TILTS[slot % TILTS.length];

    const card  = document.createElement('div');
    card.className    = 'deck-card';
    card.style.top    = topPos + 'px';
    card.style.zIndex = slot + 1;
    card.style.transform = `rotate(${tilt}deg)`;

    const inner = document.createElement('div');
    inner.className = 'deck-card-inner';

    if (frontSrc) {
      const artImg    = document.createElement('img');
      const borderImg = document.createElement('img');
      artImg.src    = frontSrc;
      borderImg.src = borderSrc || '';
      artImg.style.cssText    = 'position:absolute;top:0;left:0;width:100%;height:100%;z-index:1;object-fit:cover;';
      borderImg.style.cssText = 'position:absolute;top:-25%;left:-25%;width:150%;height:150%;z-index:2;object-fit:fill;pointer-events:none;';
      inner.appendChild(artImg);
      inner.appendChild(borderImg);
    }
    card.appendChild(inner);

    card.addEventListener('mouseenter', function() {
      card.style.transform = `translateX(-12px) rotate(0deg) scale(1.09)`;
      card.style.boxShadow = '0 10px 30px rgba(0,0,0,0.75)';
    });
    card.addEventListener('mouseleave', function() {
      card.style.transform = `rotate(${tilt}deg)`;
      card.style.boxShadow = '';
    });
    card.addEventListener('click', function() { openLightbox(frontSrc, borderSrc); });

    deckStack.appendChild(card);
    deckStack.style.height = (topPos + CARD_H + 10) + 'px';
    deckCount.textContent  = collectedCount + ' / ' + NUM_CARDS;

    if (collectedCount === NUM_CARDS) {
      document.getElementById('end-screen').classList.add('show');
      startPlaceholderCountdown();
      fetch(window.location.href, {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'action=close_pack'
      });
    }
  }

  function flyCardToDeck(cardIndex) {
    const cardEl    = document.getElementById('card-el-' + cardIndex);
    const deckEl    = document.getElementById('side-deck');
    const frontSrc  = getCardFrontSrc(cardIndex);
    const borderSrc = getCardBorderSrc(cardIndex);

    const cardRect = cardEl.getBoundingClientRect();
    const deckRect = deckEl.getBoundingClientRect();

    const clone = document.createElement('div');
    clone.className = 'flying-card';
    clone.style.cssText = `width:${cardRect.width}px;height:${cardRect.height}px;left:${cardRect.left}px;top:${cardRect.top}px;background:#1a1a2e;`;

    if (frontSrc) {
      const artImg    = document.createElement('img');
      const borderImg = document.createElement('img');
      artImg.src    = frontSrc;
      borderImg.src = borderSrc;
      artImg.style.cssText    = 'position:absolute;top:0;left:0;width:100%;height:100%;z-index:1;object-fit:cover;';
      borderImg.style.cssText = 'position:absolute;top:-25%;left:-25%;width:150%;height:150%;z-index:2;object-fit:fill;pointer-events:none;';
      clone.appendChild(artImg);
      clone.appendChild(borderImg);
    }
    document.body.appendChild(clone);

    const slotTop = collectedCount * SPACING;
    const destX   = deckRect.left + 5;
    const destY   = deckRect.top  + 40 + slotTop;

    const anim = clone.animate([
      { left: cardRect.left+'px', top: cardRect.top+'px', width: cardRect.width+'px', height: cardRect.height+'px', opacity:1, transform:'rotate(0deg) scale(1)' },
      { left: destX+'px',         top: destY+'px',         width: CARD_W+'px',         height: CARD_H+'px',          opacity:1, transform:'rotate(-3deg) scale(1)' }
    ], { duration: 520, easing: 'cubic-bezier(0.4,0,0.2,1)', fill: 'forwards' });

    anim.onfinish = function() { clone.remove(); addToDeck(cardIndex, frontSrc, borderSrc); };
  }

  for (let i = 1; i <= NUM_CARDS; i++) {
    (function(idx) {
      const fadeToggle   = document.getElementById('fade-toggle-' + idx);
      const overlayLabel = document.getElementById('overlay-el-'  + idx);
      const cardEl       = document.getElementById('card-el-'     + idx);
      let used = false;

      overlayLabel.addEventListener('click', function(e) {
        e.preventDefault();
        if (used) return;
        used = true;
        overlayLabel.style.pointerEvents = 'none';
        cardEl.style.pointerEvents       = 'none';
        cardEl.style.visibility          = 'hidden';
        flyCardToDeck(idx);
        setTimeout(function() {
          fadeToggle.checked            = true;
          overlayLabel.style.display    = 'none';
        }, 530);
      });
    })(i);
  }

  /* ============================================================
     PLACEHOLDER COUNTDOWN
     TODO: replace TOTAL_SECONDS with real server value once
     user/Fragmenta system is wired up.
  ============================================================ */
  function startPlaceholderCountdown() {
    var TOTAL_SECONDS = 7 * 3600; /* placeholder — 7h Fragmenta cooldown */
    var remaining     = TOTAL_SECONDS;
    var el            = document.getElementById('countdown-timer');

    function fmt(s) {
      var h   = Math.floor(s / 3600);
      var m   = Math.floor((s % 3600) / 60);
      var sec = s % 60;
      return (h < 10 ? '0' : '') + h + ':' +
             (m < 10 ? '0' : '') + m + ':' +
             (sec < 10 ? '0' : '') + sec;
    }

    el.textContent = fmt(remaining);
    var interval = setInterval(function() {
      remaining--;
      if (remaining <= 0) {
        clearInterval(interval);
        window.location.reload();
      } else {
        el.textContent = fmt(remaining);
      }
    }, 1000);
  }
</script>
</body>
</html>