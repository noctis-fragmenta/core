<?php
/* ============================================================
   CHARDS — Player Hub
   User dashboard: Fragmenta status, pack stats, navigation.
   Deploy via Code Snippets on a WordPress page (slug: hub)
============================================================ */
require_once("./app-access-helper.php");
if (!session_id()) session_start();


/* ── DB connection ── */
require_once __DIR__ . '/config/db-connection.php';

	/* ── Require login ── */
	if (empty($_SESSION['chards_user_id'])) {
		header('Location: /login.php');
		exit;
	}
	chards_require_app_access('hub');
	$user_id  = (int)$_SESSION['chards_user_id'];
	$username = $_SESSION['chards_username'] ?? 'Citizen';

	/* ── FETCH USER'S FRAGMENTA ── */
	$fragmenta_list = [];
	if ($pdo) {
		$stmt = $pdo->prepare("
			SELECT f.id, f.uid, f.is_locked, f.last_used_at,
				   ft.name AS type_name, ft.cooldown_minutes,
				   ft.cards_per_pack, ft.story_chance,
				   (u.pin_hash IS NOT NULL) AS pin_set
			FROM chards_fragmenta f
			JOIN chards_fragmenta_types ft ON ft.id = f.type_id
			JOIN chards_users u ON u.id = f.owner_user_id
			WHERE f.owner_user_id = ?
			ORDER BY f.registered_at ASC
		");
		$stmt->execute([$user_id]);
		$fragmenta_list = $stmt->fetchAll();
	}

	/* ── FETCH PACK STATS ── */
	$total_packs = 0;
	if ($pdo) {
		$stmt = $pdo->prepare("
			SELECT COUNT(*) FROM chards_packs
			WHERE finder_name = ? AND status = 'opened'
		");
		$stmt->execute([$username]);
		$total_packs = (int)$stmt->fetchColumn();
	}

	/* ── COMPUTE COOLDOWN STATUS FOR EACH FRAGMENTA ── */
	/* Free account cooldown */
	$free_cooldown_mins = 1440;
	$free_last_pack     = null;
	$free_available     = true;
	if ($pdo) {
		$stmt = $pdo->prepare("
			SELECT MAX(opened_at) FROM chards_pack_log
			WHERE user_id = ? AND fragmenta_id IS NULL
		");
		$stmt->execute([$user_id]);
		$free_last_pack = $stmt->fetchColumn();
		if ($free_last_pack) {
			$next = strtotime($free_last_pack) + ($free_cooldown_mins * 60);
			$free_available = time() >= $next;
			$free_secs_left = max(0, $next - time());
		}
	}

	foreach ($fragmenta_list as &$frag) {
		$frag['available']  = true;
		$frag['secs_left']  = 0;
		if ($frag['last_used_at']) {
			$next = strtotime($frag['last_used_at']) + ((int)$frag['cooldown_minutes'] * 60);
			$frag['available'] = time() >= $next;
			$frag['secs_left'] = max(0, $next - time());
		}
	}
	unset($frag);

	$has_fragmenta    = !empty($fragmenta_list);
	$any_frag_ready   = $has_fragmenta && count(array_filter($fragmenta_list, fn($f) => $f['available'])) > 0;

?>
<link href="https://fonts.googleapis.com/css2?family=Cinzel+Decorative:wght@400;700&family=Cinzel:wght@400;600&family=Crimson+Pro:ital,wght@0,300;0,400;0,600;1,300;1,400&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">

<style>
#chards-hub *, #chards-hub *::before, #chards-hub *::after {
  box-sizing: border-box; margin: 0; padding: 0;
}
#chards-hub {
  --bg:       #0a0806;
  --surface:  #13100d;
  --surface2: #1c1712;
  --border:   rgba(201,168,76,0.12);
  --border-hi:rgba(201,168,76,0.35);
  --gold:     #c9a84c;
  --gold-dim: #6b5020;
  --parch:    #e8dcc8;
  --muted:    rgba(232,220,200,0.45);
  --ok:       #4a8c5c;
  --err:      #8c4a4a;
  --r:        8px;

  background: var(--bg);
  color: var(--parch);
  font-family: 'Crimson Pro', Georgia, serif;
  min-height: 80vh;
  padding: 40px 20px 60px;
}

/* ── HEADER ── */
#chards-hub .hub-header {
  text-align: center;
  margin-bottom: 36px;
  position: relative;
}
#chards-hub .hub-header::after {
  content: '';
  display: block;
  width: 60px; height: 1px;
  background: var(--gold-dim);
  margin: 16px auto 0;
}
#chards-hub .hub-welcome {
  font-family: 'Cinzel Decorative', serif;
  font-size: clamp(11px, 2vw, 14px);
  color: var(--gold-dim);
  letter-spacing: 0.15em;
  text-transform: uppercase;
  margin-bottom: 6px;
}
#chards-hub .hub-username {
  font-family: 'Cinzel', serif;
  font-size: clamp(22px, 5vw, 36px);
  color: var(--gold);
  letter-spacing: 0.05em;
  text-shadow: 0 0 30px rgba(201,168,76,0.25);
}

/* ── LAYOUT ── */
#chards-hub .hub-wrap {
  max-width: 700px;
  margin: 0 auto;
  display: flex;
  flex-direction: column;
  gap: 24px;
}

/* ── CARDS / SECTIONS ── */
#chards-hub .hub-section {
  background: var(--surface);
  border: 1px solid var(--border);
  border-radius: var(--r);
  padding: 20px 24px;
}
#chards-hub .hub-section-title {
  font-family: 'Cinzel', serif;
  font-size: 11px;
  letter-spacing: 0.15em;
  text-transform: uppercase;
  color: var(--gold-dim);
  margin-bottom: 16px;
  padding-bottom: 10px;
  border-bottom: 1px solid var(--border);
  display: flex;
  align-items: center;
  justify-content: space-between;
}

/* ── STATS ROW ── */
#chards-hub .hub-stats {
  display: flex;
  gap: 16px;
  flex-wrap: wrap;
}
#chards-hub .hub-stat {
  flex: 1;
  min-width: 100px;
  text-align: center;
  padding: 14px 10px;
  background: var(--surface2);
  border-radius: var(--r);
  border: 1px solid var(--border);
}
#chards-hub .hub-stat-value {
  font-family: 'Cinzel', serif;
  font-size: 28px;
  color: var(--gold);
  line-height: 1;
  margin-bottom: 4px;
}
#chards-hub .hub-stat-label {
  font-size: 11px;
  color: var(--muted);
  letter-spacing: 0.08em;
  text-transform: uppercase;
}

/* ── FRAGMENTA LIST ── */
#chards-hub .hub-frag-item {
  display: flex;
  align-items: center;
  gap: 14px;
  padding: 12px 0;
  border-bottom: 1px solid var(--border);
}
#chards-hub .hub-frag-item:last-child { border-bottom: none; }

#chards-hub .hub-frag-icon {
  width: 42px; height: 42px;
  border-radius: 50%;
  background: var(--surface2);
  border: 1px solid var(--border);
  display: flex; align-items: center; justify-content: center;
  font-size: 18px;
  flex-shrink: 0;
  color: var(--gold);
}
#chards-hub .hub-frag-icon.ready {
  border-color: var(--gold);
  box-shadow: 0 0 12px rgba(201,168,76,0.2);
}

#chards-hub .hub-frag-info { flex: 1; min-width: 0; }
#chards-hub .hub-frag-name {
  font-family: 'Cinzel', serif;
  font-size: 13px;
  color: var(--parch);
  margin-bottom: 3px;
}
#chards-hub .hub-frag-meta {
  font-size: 12px;
  color: var(--muted);
  font-family: 'JetBrains Mono', monospace;
}
#chards-hub .hub-frag-meta.ready { color: #8fcc9f; }
#chards-hub .hub-frag-meta.cooldown { color: rgba(201,168,76,0.5); }

#chards-hub .hub-frag-open {
  font-family: 'Cinzel', serif;
  font-size: 10px;
  letter-spacing: 0.08em;
  padding: 8px 14px;
  border-radius: 4px;
  border: none;
  cursor: pointer;
  text-decoration: none;
  display: inline-flex;
  align-items: center;
  transition: background 0.2s;
  white-space: nowrap;
}
#chards-hub .hub-frag-open.ready {
  background: rgba(201,168,76,0.9);
  color: #0a0806;
}
#chards-hub .hub-frag-open.ready:hover { background: var(--gold); }
#chards-hub .hub-frag-open.waiting {
  background: transparent;
  border: 1px solid var(--border);
  color: var(--muted);
  cursor: default;
  pointer-events: none;
}

/* ── FREE PACK SECTION ── */
#chards-hub .hub-free-prominent {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 12px;
  padding: 8px 0 4px;
}
#chards-hub .hub-free-btn {
  font-family: 'Cinzel', serif;
  font-size: 13px;
  letter-spacing: 0.08em;
  padding: 14px 36px;
  border-radius: 50px;
  border: none;
  cursor: pointer;
  text-decoration: none;
  display: inline-flex;
  align-items: center;
  gap: 8px;
  background: linear-gradient(135deg, #5b3fbe 0%, #3a2a8a 100%);
  color: #fff;
  box-shadow: 0 6px 28px rgba(80,50,200,0.35);
  transition: transform 0.15s, box-shadow 0.15s;
}
#chards-hub .hub-free-btn:hover {
  transform: translateY(-2px);
  box-shadow: 0 8px 36px rgba(80,50,200,0.5);
}
#chards-hub .hub-free-btn.disabled {
  background: var(--surface2);
  color: var(--muted);
  box-shadow: none;
  cursor: default;
  pointer-events: none;
  border: 1px solid var(--border);
}
#chards-hub .hub-free-countdown {
  font-family: 'JetBrains Mono', monospace;
  font-size: 12px;
  color: var(--muted);
  letter-spacing: 0.05em;
}

/* ── FREE PACK SUBTLE LINK (when Fragmenta present) ── */
#chards-hub .hub-free-subtle {
  text-align: center;
  padding-top: 4px;
}
#chards-hub .hub-free-subtle a,
#chards-hub .hub-free-subtle span {
  font-size: 12px;
  color: var(--muted);
  font-style: italic;
  text-decoration: none;
  border-bottom: 1px solid transparent;
  transition: color 0.2s, border-color 0.2s;
  cursor: pointer;
}
#chards-hub .hub-free-subtle a:hover { color: var(--parch); border-bottom-color: var(--border); }
#chards-hub .hub-free-subtle span { cursor: default; }

/* ── NAV BUTTONS ── */
#chards-hub .hub-nav {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 12px;
}
#chards-hub .hub-nav-btn {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  gap: 8px;
  padding: 20px 16px;
  background: var(--surface2);
  border: 1px solid var(--border);
  border-radius: var(--r);
  text-decoration: none;
  color: var(--parch);
  transition: border-color 0.2s, background 0.2s, transform 0.15s;
  cursor: pointer;
}
#chards-hub .hub-nav-btn:hover {
  border-color: var(--border-hi);
  background: rgba(201,168,76,0.05);
  transform: translateY(-2px);
}
#chards-hub .hub-nav-icon { font-size: 24px; color: var(--gold); }
#chards-hub .hub-nav-label {
  font-family: 'Cinzel', serif;
  font-size: 11px;
  letter-spacing: 0.1em;
  text-transform: uppercase;
  color: var(--parch);
}
#chards-hub .hub-nav-sub {
  font-size: 12px;
  color: var(--muted);
  font-style: italic;
  text-align: center;
}

/* ── LOGOUT ── */
#chards-hub .hub-logout {
  text-align: center;
  padding-top: 8px;
}
#chards-hub .hub-logout a {
  font-size: 12px;
  color: var(--muted);
  text-decoration: none;
  font-style: italic;
  transition: color 0.2s;
}
#chards-hub .hub-logout a:hover { color: var(--parch); }
	
#chards-hub .hub-action-btn {
  font-family: 'Cinzel', serif;
  font-size: 9px;
  letter-spacing: 0.1em;
  padding: 5px 12px;
  border-radius: 4px;
  border: 1px solid var(--border-hi);
  background: transparent;
  color: var(--gold);
  cursor: pointer;
  transition: background 0.2s;
}
#chards-hub .hub-action-btn:hover {
  background: rgba(201,168,76,0.1);
}
#chards-hub .hub-pin-btn {
  font-family: 'JetBrains Mono', monospace;
  font-size: 9px;
  padding: 4px 8px;
  border-radius: 4px;
  border: 1px solid var(--border);
  background: transparent;
  color: var(--muted);
  cursor: pointer;
  transition: border-color 0.2s, color 0.2s;
}
#chards-hub .hub-pin-btn:hover {
  border-color: var(--border-hi);
  color: var(--parch);
}
#chards-hub .hub-pin-btn.remove:hover {
  border-color: rgba(140,74,74,0.5);
  color: #cc8f8f;
}

/* ── SCAN POPUP ── */
#hub-scan-overlay {
  display: none;
  position: fixed; inset: 0;
  z-index: 9999;
  background: rgba(5,3,2,0.85);
  backdrop-filter: blur(4px);
  align-items: center;
  justify-content: center;
}
#hub-scan-overlay.open { display: flex; }

#hub-scan-popup {
  background: #0e0b08;
  border: 1px solid rgba(201,168,76,0.25);
  border-radius: 12px;
  padding: 36px 40px;
  text-align: center;
  max-width: 320px;
  width: 90%;
  box-shadow: 0 20px 60px rgba(0,0,0,0.7);
  animation: popIn 0.3s cubic-bezier(0.34,1.4,0.64,1) forwards;
}
@keyframes popIn {
  from { transform: scale(0.85); opacity: 0; }
  to   { transform: scale(1);    opacity: 1; }
}
#hub-scan-icon {
  font-size: 48px;
  margin-bottom: 16px;
  animation: pulse 2s ease-in-out infinite;
}
@keyframes pulse {
  0%,100% { opacity: 0.6; transform: scale(1); }
  50%      { opacity: 1;   transform: scale(1.08); }
}
#hub-scan-title {
  font-family: 'Cinzel', serif;
  font-size: 14px;
  letter-spacing: 0.1em;
  color: rgba(201,168,76,0.9);
  margin-bottom: 10px;
}
#hub-scan-msg {
  font-family: 'Crimson Pro', serif;
  font-style: italic;
  font-size: 15px;
  color: rgba(232,220,200,0.6);
  line-height: 1.5;
  margin-bottom: 24px;
}
#hub-scan-cancel {
  font-family: 'Cinzel', serif;
  font-size: 10px;
  letter-spacing: 0.1em;
  padding: 9px 24px;
  border-radius: 4px;
  border: 1px solid rgba(201,168,76,0.2);
  background: transparent;
  color: rgba(201,168,76,0.5);
  cursor: pointer;
  transition: border-color 0.2s, color 0.2s;
}
#hub-scan-cancel:hover {
  border-color: rgba(201,168,76,0.5);
  color: rgba(201,168,76,0.9);
}
</style>

<div id="chards-hub">
  <div class="hub-header">
    <div class="hub-welcome">Welcome back,</div>
    <div class="hub-username"><?= htmlspecialchars($username) ?></div>
  </div>

  <div class="hub-wrap">

    <!-- Stats -->
    <div class="hub-section">
      <div class="hub-section-title">Registry Record</div>
      <div class="hub-stats">
        <div class="hub-stat">
          <div class="hub-stat-value"><?= $total_packs ?></div>
          <div class="hub-stat-label">Packs Opened</div>
        </div>
        <div class="hub-stat">
          <div class="hub-stat-value"><?= count($fragmenta_list) ?></div>
          <div class="hub-stat-label">Fragmenta</div>
        </div>
      </div>
    </div>

<!-- Fragmenta section — always visible -->
<div class="hub-section">
  <div class="hub-section-title">
    Your Fragmenta
    <button class="hub-action-btn" onclick="hubScanPrompt('register')">+ Register New</button>
  </div>
  
<?php if ($has_fragmenta): ?>
  <?php foreach ($fragmenta_list as $frag): ?>
    <?php
      $ready    = $frag['available'];
      $h        = floor($frag['secs_left'] / 3600);
      $m        = floor(($frag['secs_left'] % 3600) / 60);
      $s        = $frag['secs_left'] % 60;
      $countdown = sprintf('%02d:%02d:%02d', $h, $m, $s);
      $pack_url = '/pack-opening.php?frag=' . $frag['id'];
    ?>
    <div class="hub-frag-item">
      <div class="hub-frag-icon <?= $ready ? 'ready' : '' ?>">◈</div>
      <div class="hub-frag-info">
        <div class="hub-frag-name"><?= htmlspecialchars($frag['type_name']) ?></div>
        <div class="hub-frag-meta <?= $ready ? 'ready' : 'cooldown' ?>">
          <?php if ($ready): ?>
            Ready to open
          <?php else: ?>
            Available in <?= $countdown ?>
          <?php endif; ?>
        </div>
      </div>
      <div style="display:flex; flex-direction:column; gap:6px; align-items:flex-end;">
        <a href="<?= $ready ? $pack_url : '#' ?>"
           class="hub-frag-open <?= $ready ? 'ready' : 'waiting' ?>">
          <?= $ready ? 'Open Pack' : 'Cooling Down' ?>
        </a>
        <div style="display:flex; gap:6px;">
          <button class="hub-pin-btn" onclick="hubScanPrompt('pin')">
            <?= $frag['is_locked'] || !empty($frag['pin_set']) ? '✎ Edit PIN' : '+ Set PIN' ?>
          </button>
          <?php if (!empty($frag['pin_set'])): ?>
          <button class="hub-pin-btn remove" onclick="hubScanPrompt('remove_pin')">✕ PIN</button>
          <?php endif; ?>
        </div>
      </div>
    </div>
  <?php endforeach; ?>

  <!-- Subtle free pack option (shown when user HAS fragmenta) -->
  <div class="hub-free-subtle" style="margin-top:14px">
    <?php if ($free_available): ?>
      <a href="/pack-opening.php">
        Or open a free pack instead
      </a>
    <?php else: ?>
      <span>Free pack available in
        <span id="hub-free-timer"><?php
          $fl = isset($free_secs_left) ? $free_secs_left : 0;
          echo sprintf('%02d:%02d:%02d', floor($fl/3600), floor(($fl%3600)/60), $fl%60);
        ?></span>
      </span>
    <?php endif; ?>
  </div>

</div> <!-- Close the hub-section -->

<?php else: ?>
<!-- No Fragmenta — prominent free pack CTA -->
<div class="hub-section">
  <div class="hub-section-title">Pack Opening</div>
  <div class="hub-free-prominent">
    <?php if ($free_available): ?>
      <a href="/pack-opening.php" class="hub-free-btn">
        ✦ Open a Free Pack
      </a>
      <div class="hub-free-countdown">Your daily pack is ready</div>
    <?php else: ?>
      <a class="hub-free-btn disabled">✦ Open a Free Pack</a>
      <div class="hub-free-countdown">
        Next free pack in <span id="hub-free-timer"><?php
          $fl = isset($free_secs_left) ? $free_secs_left : 0;
          echo sprintf('%02d:%02d:%02d', floor($fl/3600), floor(($fl%3600)/60), $fl%60);
        ?></span>
      </div>
    <?php endif; ?> 
  </div>
</div>
<?php endif; ?>

    <!-- Navigation -->
    <div class="hub-section">
      <div class="hub-section-title">Compendium</div>
      <div class="hub-nav">
        <a href="/story.php" class="hub-nav-btn">
          <div class="hub-nav-icon">📖</div>
          <div class="hub-nav-label">Story</div>
          <div class="hub-nav-sub">The Fragmenta Chronicles</div>
        </a>
        <a href="/collection.php" class="hub-nav-btn">
          <div class="hub-nav-icon">✦</div>
          <div class="hub-nav-label">Collection</div>
          <div class="hub-nav-sub">Your collected cards</div>
        </a>
      </div>
    </div>

    <!-- Logout -->
    <div class="hub-logout">
      <a href="/logout.php">Sign out of the registry</a>
    </div>
	  <!-- Scan Popup -->
	  <div id="hub-scan-overlay">
		<div id="hub-scan-popup">
		  <div id="hub-scan-icon">𒋝</div>
		  <div id="hub-scan-title">ARC Registration Terminal</div>
		  <div id="hub-scan-msg">
			Please present your Fragmenta for Analysis.
		  </div>
		  <button id="hub-scan-cancel" onclick="hubCancelScan()">Cancel</button>
		</div>
	  </div>
  </div>
</div>

<script>
/* Live countdown for free pack timer */
(function() {
  var timerEl = document.getElementById('hub-free-timer');
  if (!timerEl) return;

  var secs = <?= isset($free_secs_left) ? (int)$free_secs_left : 0 ?>;
  if (secs <= 0) return;

  var interval = setInterval(function() {
    secs--;
    if (secs <= 0) {
      clearInterval(interval);
      window.location.reload();
      return;
    }
    var h = Math.floor(secs / 3600);
    var m = Math.floor((secs % 3600) / 60);
    var s = secs % 60;
    timerEl.textContent =
      (h < 10 ? '0' : '') + h + ':' +
      (m < 10 ? '0' : '') + m + ':' +
      (s < 10 ? '0' : '') + s;
  }, 1000);
})();
	/* ── SCAN POPUP ── */
var AJAX_URL = '/arc-handler.php';
console.log(AJAX_URL)
	
function hubScanPrompt(action) {
  /* Set session flag via AJAX then show popup */
  var sub = action === 'register'   ? 'set_register_flag'   :
            action === 'pin'        ? 'set_pin_flag'        :
                                      'set_remove_pin_flag' ;

  fetch(AJAX_URL, {
    method: 'POST',
    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
    body: 'action=chards_arc&sub=' + sub
  }).then(function() {
    document.getElementById('hub-scan-overlay').classList.add('open');
  });
}

function hubCancelScan() {
  /* Reset all flags and close popup */
  fetch(AJAX_URL, {
    method: 'POST',
    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
    body: 'action=chards_arc&sub=reset_flags'
  }).then(function() {
    document.getElementById('hub-scan-overlay').classList.remove('open');
  });
}
</script>