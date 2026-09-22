<?php
	/* ============================================================
   CHARDS — ARC Registration Dialogue (Login/Register)
   Aldric Voss, Senior Registrar of the Aenephsy Regulation Compendium
   
   Scenarios handled:
   A. No session, no NFC — standard login/register
   B. No session, unassigned NFC — register + claim
   C. No session, assigned NFC — login + borrow/claim
   D. Logged in, unassigned NFC — claim card
   E. Logged in, NFC assigned to someone else — borrow with PIN
   F. Logged in, own NFC — redirect to pack opening (handled before this page loads)
   
   The NFC UID is read from \$_SESSION['cardID'] set by landing.php.
   No UID = scenario A (standard login button).
============================================================ */
require_once("./app-access-helper.php");
if (!session_id()) session_start();

chards_require_app_access('login');
/* ── DB connection ── */
require_once __DIR__ . '/config/db-connection.php';

/* ── Handle email confirmation link ── */
$verify_result = null;
if (isset($_GET['verify']) && $pdo) {
    $verify_result = chards_confirm_email($pdo, trim($_GET['verify']));
}

/* ── Read scenario pre-computed by landing.php ── */
$nfc_uid      = $_SESSION['cardID']           ?? null;
$scenario     = $_SESSION['arc_scenario']     ?? 'A';
$fragmenta    = $_SESSION['arc_fragmenta']    ?? null;
$logged_in    = !empty($_SESSION['chards_user_id']);
$user_id      = $logged_in ? (int)$_SESSION['chards_user_id'] : null;
$username     = $logged_in ? ($_SESSION['chards_username'] ?? null) : null;

/* Guard: logged_in with no username means a corrupt/stale session — clear and bounce */
if ($logged_in && $username === null) {
    session_unset();
    session_destroy();
    setcookie('chards_remember', '', time() - 3600, '/', '', true, true);
    header('Location: /frontpage.php');
    exit;
}
$owner_username = $_SESSION['arc_fragmenta_owner'] ?? '';

/* Safety: scenario F and free should never reach this page
   (landing.php redirects them directly) — but just in case */
if ($scenario === 'F' || ($logged_in && !$nfc_uid && $scenario === 'free')) {
    header('Location: /hub.php');
    exit;
}

/* ── Aldric's dialogue lines per scenario ── */
$dialogues = [
    'A' => [
        'greeting'  => "Ah. Another one. Please, do sit down — the process won't take long if you cooperate.",
        'sub'       => "The ARC requires all citizens to be registered before accessing any Aenephsy-related services. Your name, if you please.",
        'mode'      => 'login_or_register',
    ],
    'B' => [
        'greeting'  => "An unregistered Fragmenta. Again.",
        'sub'       => "Do you have any idea how many incidents began with those exact words? This shard needs to be logged before it can be used. I'll need your details first — are you already registered with the ARC?",
        'mode'      => 'login_or_register_then_claim',
    ],
    'C' => [
        'greeting'  => "This Fragmenta is already on record.",
        'sub'       => "It is registered to " . (($fragmenta['owner_user_id'] ?? null) ? "another citizen" : "no one") . ". Are you its registered owner, or are you borrowing it?",
        'mode'      => 'owner_or_borrow',
    ],
    'D' => [
        'greeting'  => "Welcome back, " . ($username ?? 'Citizen') . ".",
        'sub'       => "I see you've brought an unregistered Fragmenta. Shall I log it under your name? Once registered, it cannot be transferred without ARC involvement.",
        'mode'      => 'claim_confirm',
    ],
    'E' => [
        'greeting'  => "That Fragmenta belongs to another registered citizen.",
        'sub'       => "If you have been granted temporary access, the owner will have provided you with a clearance PIN. Present it now.",
        'mode'      => 'pin_only',
    ],
     'no_pin_set' => [
        'greeting'  => "This Fragmenta has not been cleared for shared access.",
        'sub'       => "The registered owner has not configured a clearance PIN. Sharing cannot be authorised until they do so. If you are the owner, log in and set a PIN from your account settings.",
        'mode'      => 'no_sharing',
    ],
    'pin_manage' => [
        'greeting'  => "Identity confirmed.",
        'sub'       => "You may now set or update the clearance PIN for this Fragmenta. This PIN will be required by anyone borrowing your card.",
        'mode'      => 'pin_manage',
    ],
    'pin_remove' => [
        'greeting'  => "Identity confirmed.",
        'sub'       => "You are about to remove the clearance PIN from this Fragmenta. Once removed, no one will be able to borrow it until a new PIN is set.",
        'mode'      => 'pin_remove',
    ],
    'already_owned' => [
        'greeting'  => "That Fragmenta is already registered to you.",
        'sub'       => "There is nothing further to do here. Everything is in order.",
        'mode'      => 'no_sharing',
    ],
    'register_taken' => [
        'greeting'  => "That Fragmenta is already registered to another citizen.",
        'sub'       => "I cannot transfer registration without the current owner's involvement. If you believe this is an error, contact the ARC directly.",
        'mode'      => 'no_sharing',
    ],
    'pin_not_owner' => [
        'greeting'  => "That Fragmenta does not belong to you.",
        'sub'       => "PIN management is restricted to the registered owner only.",
        'mode'      => 'no_sharing',
    ],
];

$current = $dialogues[$scenario];
/* owner_username already set from $_SESSION['arc_fragmenta_owner'] above */
?>

<link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@400;600&family=IM+Fell+English:ital@0;1&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">

<style>
 html, body {
	  background-image: url('/media/background.png');
	  background-size: cover;
	  background-position: center;
	  background-attachment: fixed;
	}

/* ── ALDRIC DIALOGUE PANEL ── */
#arc-overlay {
  position: fixed;
  inset: 0;
  z-index: 99998;
  background: rgba(5, 3, 2, 0.75);
  backdrop-filter: blur(3px);
  opacity: 0;
  pointer-events: none;
  transition: opacity 0.4s ease;
}
#arc-overlay.open {
  opacity: 1;
  pointer-events: auto;
}

#arc-panel {
  position: fixed;
  top: 50%;
  left: 50%;
  transform: translateX(-50%) translateY(-50%) scale(0.95);
  opacity: 0;
  pointer-events: none;
  width: min(480px, 95vw);
  height: auto;
  max-height: 90vh;
  z-index: 99999;
  background: #0e0b08;
  border-right: 1px solid rgba(201,168,76,0.2);
  box-shadow: 4px 0 40px rgba(0,0,0,0.7);
  display: flex;
  flex-direction: column;
  transform: translateX(-100%);
  transition: transform 0.35s cubic-bezier(0.34,1.4,0.64,1), opacity 0.35s ease;
  overflow: hidden;
}
#arc-panel.open {
  transform: translateX(-50%) translateY(-50%) scale(1);
  opacity: 1;
  pointer-events: auto;
}

/* Aged paper texture overlay */
#arc-panel::before {
  content: '';
  position: absolute;
  inset: 0;
  background:
    repeating-linear-gradient(
      0deg,
      transparent,
      transparent 28px,
      rgba(201,168,76,0.03) 28px,
      rgba(201,168,76,0.03) 29px
    );
  pointer-events: none;
  z-index: 0;
}

/* ARC header bar */
.arc-header {
  position: relative;
  z-index: 1;
  padding: 16px 20px 14px;
  border-bottom: 1px solid rgba(201,168,76,0.15);
  display: flex;
  align-items: center;
  justify-content: space-between;
  flex-shrink: 0;
}
.arc-logo {
  font-family: 'Cinzel', serif;
  font-size: 11px;
  letter-spacing: 0.2em;
  color: rgba(201,168,76,0.6);
  text-transform: uppercase;
}
.arc-logo span {
  display: block;
  font-size: 9px;
  letter-spacing: 0.15em;
  color: rgba(201,168,76,0.35);
  margin-top: 2px;
}
.arc-close {
  width: 28px; height: 28px;
  border-radius: 50%;
  border: 1px solid rgba(201,168,76,0.2);
  background: transparent;
  color: rgba(201,168,76,0.5);
  font-size: 14px;
  cursor: pointer;
  display: flex; align-items: center; justify-content: center;
  transition: border-color 0.2s, color 0.2s;
}
.arc-close:hover {
  border-color: rgba(201,168,76,0.5);
  color: rgba(201,168,76,0.9);
}

/* Character section */
.arc-character {
  position: relative;
  z-index: 1;
  display: flex;
  gap: 16px;
  padding: 20px 20px 0;
  flex-shrink: 0;
}
.arc-portrait {
  flex-shrink: 0;
  width: 80px; height: 80px;
  border-radius: 6px;
  border: 1px solid rgba(201,168,76,0.25);
  background: #1a1510;
  overflow: hidden;
  position: relative;
}
.arc-portrait img {
  width: 100%; height: 100%;
  object-fit: cover;
  display: block;
}
.arc-portrait-placeholder {
  width: 100%; height: 100%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-family: 'Cinzel', serif;
  font-size: 24px;
  color: rgba(201,168,76,0.2);
}
.arc-npc-info {
  display: flex;
  flex-direction: column;
  justify-content: center;
  gap: 3px;
}
.arc-npc-name {
  font-family: 'Cinzel', serif;
  font-size: 14px;
  color: rgba(232,220,200,0.9);
  letter-spacing: 0.05em;
}
.arc-npc-title {
  font-family: 'IM Fell English', serif;
  font-style: italic;
  font-size: 12px;
  color: rgba(201,168,76,0.55);
  line-height: 1.4;
}

/* Speech bubble */
.arc-speech {
  position: relative;
  z-index: 1;
  margin: 14px 20px 0;
  padding: 16px 18px;
  background: rgba(26,21,16,0.8);
  border: 1px solid rgba(201,168,76,0.15);
  border-radius: 0 8px 8px 8px;
  flex-shrink: 0;
}
.arc-speech::before {
  content: '';
  position: absolute;
  top: -1px; left: -1px;
  width: 0; height: 0;
  border-top: 10px solid rgba(201,168,76,0.15);
  border-right: 10px solid transparent;
}
.arc-speech::after {
  content: '';
  position: absolute;
  top: 0; left: 0;
  width: 0; height: 0;
  border-top: 9px solid #1a1510;
  border-right: 9px solid transparent;
}
.arc-greeting {
  font-family: 'IM Fell English', serif;
  font-size: 20px;
  color: rgba(232,220,200,0.95);
  line-height: 1.55;
  margin-bottom: 8px;
}
.arc-sub {
  font-family: 'IM Fell English', serif;
  font-style: italic;
  font-size: 17px;
  color: rgba(201,168,76,0.7);
  line-height: 1.55;
}

/* Scrollable form area */
.arc-body {
  position: relative;
  z-index: 1;
  flex: 1;
  overflow-y: auto;
  padding: 20px;
  display: flex;
  flex-direction: column;
  gap: 14px;
}
.arc-body::-webkit-scrollbar { width: 4px; }
.arc-body::-webkit-scrollbar-track { background: transparent; }
.arc-body::-webkit-scrollbar-thumb { background: rgba(201,168,76,0.2); border-radius: 2px; }

/* Step sections */
.arc-step {
  display: none;
  flex-direction: column;
  gap: 12px;
  animation: arcFadeIn 0.25s ease forwards;
}
.arc-step.active { display: flex; }
@keyframes arcFadeIn {
  from { opacity: 0; transform: translateY(6px); }
  to   { opacity: 1; transform: translateY(0); }
}

/* Form elements */
.arc-field {
  display: flex;
  flex-direction: column;
  gap: 5px;
}
.arc-field label {
  font-family: 'JetBrains Mono', monospace;
  font-size: 9px;
  letter-spacing: 0.15em;
  text-transform: uppercase;
  color: rgba(201,168,76,0.5);
}
.arc-field input {
  background: rgba(10,8,6,0.8);
  border: 1px solid rgba(201,168,76,0.2);
  border-radius: 4px;
  padding: 9px 12px;
  color: rgba(232,220,200,0.9);
  font-family: 'IM Fell English', serif;
  font-size: 17px;
  outline: none;
  width: 100%;
  transition: border-color 0.15s;
}
.arc-field input:focus { border-color: rgba(201,168,76,0.5); }
.arc-field input::placeholder { color: rgba(201,168,76,0.2); font-style: italic; }

/* Buttons */
.arc-btn-row {
  display: flex;
  gap: 8px;
  flex-wrap: wrap;
}
.arc-btn {
  font-family: 'Cinzel', serif;
  font-size: 11px;
  letter-spacing: 0.08em;
  padding: 10px 20px;
  border-radius: 4px;
  cursor: pointer;
  border: none;
  transition: background 0.2s, transform 0.1s;
  text-decoration: none;
  display: inline-flex;
  align-items: center;
  gap: 6px;
}
.arc-btn:active { transform: scale(0.97); }
.arc-btn-primary {
  background: rgba(201,168,76,0.9);
  color: #0a0806;
}
.arc-btn-primary:hover { background: rgba(201,168,76,1); }
.arc-btn-ghost {
  background: transparent;
  border: 1px solid rgba(201,168,76,0.25);
  color: rgba(201,168,76,0.7);
}
.arc-btn-ghost:hover {
  border-color: rgba(201,168,76,0.5);
  color: rgba(201,168,76,0.95);
}

/* Divider */
.arc-divider {
  display: flex;
  align-items: center;
  gap: 10px;
  font-family: 'JetBrains Mono', monospace;
  font-size: 9px;
  letter-spacing: 0.1em;
  color: rgba(201,168,76,0.25);
}
.arc-divider::before, .arc-divider::after {
  content: '';
  flex: 1;
  height: 1px;
  background: rgba(201,168,76,0.12);
}

/* Error / info messages */
.arc-notice {
  padding: 10px 14px;
  border-radius: 4px;
  font-family: 'IM Fell English', serif;
  font-style: italic;
  font-size: 13px;
  border-left: 2px solid;
  display: none;
}
.arc-notice.show { display: block; }
.arc-notice.err  { background: rgba(140,50,50,0.15); border-color: #8c3232; color: #cc8f8f; }
.arc-notice.ok   { background: rgba(50,100,60,0.15); border-color: #326432; color: #8fcc9f; }
.arc-notice.info { background: rgba(60,60,100,0.15); border-color: #323280; color: #9f9fcc; }

/* Aldric's response line (updated dynamically) */
.arc-aldric-response {
  font-family: 'IM Fell English', serif;
  font-style: italic;
  font-size: 13px;
  color: rgba(201,168,76,0.75);
  line-height: 1.55;
  padding: 10px 14px;
  border-left: 2px solid rgba(201,168,76,0.25);
  display: none;
}
.arc-aldric-response.show { display: block; }

/* Stamp — success state */
.arc-stamp {
  text-align: center;
  padding: 20px;
  display: none;
  flex-direction: column;
  align-items: center;
  gap: 12px;
}
.arc-stamp.show { display: flex; }
.arc-stamp-seal {
  width: 80px; height: 80px;
  border-radius: 50%;
  border: 3px solid rgba(201,168,76,0.4);
  display: flex;
  align-items: center;
  justify-content: center;
  font-family: 'Cinzel', serif;
  font-size: 28px;
  color: rgba(201,168,76,0.6);
  box-shadow: 0 0 20px rgba(201,168,76,0.1), inset 0 0 20px rgba(201,168,76,0.05);
}
.arc-stamp-text {
  font-family: 'Cinzel', serif;
  font-size: 12px;
  letter-spacing: 0.12em;
  color: rgba(201,168,76,0.6);
  text-transform: uppercase;
}
.arc-stamp-sub {
  font-family: 'IM Fell English', serif;
  font-style: italic;
  font-size: 13px;
  color: rgba(232,220,200,0.55);
  text-align: center;
  line-height: 1.5;
}

/* Footer */
.arc-footer {
  position: relative;
  z-index: 1;
  padding: 12px 20px;
  border-top: 1px solid rgba(201,168,76,0.1);
  font-family: 'JetBrains Mono', monospace;
  font-size: 9px;
  letter-spacing: 0.1em;
  color: rgba(201,168,76,0.2);
  text-align: center;
  flex-shrink: 0;
}
</style>

<!-- Overlay -->
<div id="arc-overlay"></div>

<!-- Panel -->
<div id="arc-panel">

  <!-- Header -->
  <div class="arc-header">
    <div class="arc-logo">
      ARC
      <span>Aenephsy Regulation Compendium</span>
    </div>
    <button class="arc-close" id="arc-close-btn" aria-label="Close">✕</button>
  </div>

  <!-- Character -->
  <div class="arc-character">
    <div class="arc-portrait">
      <?php if (false): /* Replace false with image check when portrait is ready */ ?>
        <img src="/path/to/aldric-portrait.jpg" alt="Aldric Voss">
      <?php else: ?>
        <div class="arc-portrait-placeholder">⚗</div>
      <?php endif; ?>
    </div>
    <div class="arc-npc-info">
      <div class="arc-npc-name">Aldric Voss</div>
      <div class="arc-npc-title">Senior Registrar<br>Aenephsy Regulation Compendium</div>
    </div>
  </div>

  <!-- Speech -->
  <div class="arc-speech">
    <div class="arc-greeting">"<?= htmlspecialchars($current['greeting']) ?>"</div>
    <div class="arc-sub"><?= htmlspecialchars($current['sub']) ?></div>
  </div>

  <!-- Body — steps rendered per scenario -->
  <div class="arc-body">

    <?php if ($verify_result !== null): ?>
      <div class="arc-notice show <?= $verify_result ? 'ok' : 'err' ?>" style="margin-bottom:14px;">
        <?= $verify_result
            ? 'Your registration has been confirmed. You may now log in.'
            : 'This confirmation link is invalid or has already been used.' ?>
      </div>
    <?php endif; ?>

    <?php if ($current['mode'] === 'login_or_register' || $current['mode'] === 'login_or_register_then_claim'): ?>

      <!-- STEP 1A: Choose login or register 	-->
      <div class="arc-step active" id="step-choose">
        <div class="arc-btn-row">
          <button class="arc-btn arc-btn-primary" onclick="arcShowStep('step-login')">
            I am registered
          </button>
          <button class="arc-btn arc-btn-ghost" onclick="arcShowStep('step-register')">
            New citizen
          </button>
        </div>
      </div>

      <!-- STEP 1B: Login 	-->
      <div class="arc-step" id="step-login">
        <div class="arc-aldric-response show">
          "Very well. Your credentials, please — username and passphrase."
        </div>
        <div class="arc-field">
          <label>Username</label>
          <input type="text" id="login-username" placeholder="Your registered name…" autocomplete="username">
        </div>
        <div class="arc-field">
          <label>Passphrase</label>
          <input type="password" id="login-password" placeholder="••••••••" autocomplete="current-password">
        </div>
        <div class="arc-notice" id="login-error"></div>
        <div class="arc-btn-row">
          <button class="arc-btn arc-btn-primary" onclick="arcDoLogin()">Present credentials</button>
          <button class="arc-btn arc-btn-ghost" onclick="arcShowStep('step-choose')">Back</button>
        </div>
      </div>

      <!-- STEP 1C: Register	-->
      <div class="arc-step" id="step-register">
        <div class="arc-aldric-response show">
          "A new citizen. Welcome — though I use that term loosely until the paperwork is complete. Full details, if you please."
        </div>
        <div class="arc-field">
          <label>Choose a username</label>
          <input type="text" id="reg-username" placeholder="Your name in the registry…">
        </div>
        <div class="arc-field">
          <label>Email address</label>
          <input type="email" id="reg-email" placeholder="For ARC correspondence…">
        </div>
        <div class="arc-field">
          <label>Passphrase</label>
          <input type="password" id="reg-password" placeholder="Choose wisely…">
        </div>
        <div class="arc-field">
          <label>Confirm passphrase</label>
          <input type="password" id="reg-password2" placeholder="Once more…">
        </div>
        <div class="arc-notice" id="reg-error"></div>
        <div class="arc-btn-row">
          <button class="arc-btn arc-btn-primary" onclick="arcDoRegister()">Submit registration</button>
          <button class="arc-btn arc-btn-ghost" onclick="arcShowStep('step-choose')">Back</button>
        </div>
      </div>

    <?php elseif ($current['mode'] === 'owner_or_borrow'): ?>

      <!-- SCENARIO C: Owner or borrower -->
      <div class="arc-step active" id="step-choose">
        <div class="arc-btn-row">
          <button class="arc-btn arc-btn-primary" onclick="arcShowStep('step-login')">
            I am the owner
          </button>
          <button class="arc-btn arc-btn-ghost" onclick="arcShowStep('step-borrow-login')">
            I am borrowing it
          </button>
        </div>
      </div>

      <!-- Owner login -->
      <div class="arc-step" id="step-login">
        <div class="arc-aldric-response show">
          "Then identify yourself. Username and passphrase."
        </div>
        <div class="arc-field">
          <label>Username</label>
          <input type="text" id="login-username" placeholder="Your registered name…" autocomplete="username">
        </div>
        <div class="arc-field">
          <label>Passphrase</label>
          <input type="password" id="login-password" placeholder="••••••••" autocomplete="current-password">
        </div>
        <div class="arc-notice" id="login-error"></div>
        <div class="arc-btn-row">
          <button class="arc-btn arc-btn-primary" onclick="arcDoLogin()">Present credentials</button>
          <button class="arc-btn arc-btn-ghost" onclick="arcShowStep('step-choose')">Back</button>
        </div>
      </div>

      <!-- Borrower — needs owner PIN -->
      <div class="arc-step" id="step-borrow-login">
        <div class="arc-aldric-response show">
          "<?= $owner_username ? "This Fragmenta belongs to <strong>{$owner_username}</strong>. " : "" ?>If they have granted you access, they will have given you a clearance PIN."
        </div>
        <div class="arc-field">
          <label>Your username</label>
          <input type="text" id="borrow-username" placeholder="Your registered name…">
        </div>
        <div class="arc-field">
          <label>Your passphrase</label>
          <input type="password" id="borrow-password" placeholder="••••••••">
        </div>
        <div class="arc-field">
          <label>Owner's clearance PIN</label>
          <input type="password" id="borrow-pin" placeholder="4-digit PIN…" maxlength="10">
        </div>
        <div class="arc-notice" id="borrow-error"></div>
        <div class="arc-btn-row">
          <button class="arc-btn arc-btn-primary" onclick="arcDoBorrow()">Request access</button>
          <button class="arc-btn arc-btn-ghost" onclick="arcShowStep('step-choose')">Back</button>
        </div>
      </div>

    <?php elseif ($current['mode'] === 'claim_confirm'): ?>

      <!-- SCENARIO D: Logged in, claim unassigned NFC -->
      <div class="arc-step active" id="step-claim">
        <div class="arc-btn-row">
          <button class="arc-btn arc-btn-primary" onclick="arcDoClaim()">
            Register to my name
          </button>
          <button class="arc-btn arc-btn-ghost" onclick="arcClosePanel()">
            Not now
          </button>
        </div>
        <div class="arc-notice" id="claim-error"></div>
      </div>

    <?php elseif ($current['mode'] === 'no_sharing'): ?>

      <!-- NO PIN SET — sharing not enabled -->
      <div class="arc-step active" id="step-no-sharing">
        <div class="arc-aldric-response show">
          "I suggest contacting the owner directly. There is nothing more I can do here."
        </div>
        <div class="arc-btn-row">
          <button class="arc-btn arc-btn-ghost" onclick="arcClosePanel()">Understood</button>
        </div>
      </div>

     <?php elseif ($current['mode'] === 'pin_manage'): ?>

      <!-- PIN MANAGE — set or update PIN -->
      <div class="arc-step active" id="step-pin-manage">
        <div class="arc-field">
          <label>New Clearance PIN</label>
          <input type="password" id="pin-manage-input" placeholder="Enter new PIN…" maxlength="10">
        </div>
        <div class="arc-field">
          <label>Confirm PIN</label>
          <input type="password" id="pin-manage-confirm" placeholder="Confirm PIN…" maxlength="10">
        </div>
        <div class="arc-notice" id="pin-manage-error"></div>
        <div class="arc-btn-row">
          <button class="arc-btn arc-btn-primary" onclick="arcDoPinManage()">Set PIN</button>
          <button class="arc-btn arc-btn-ghost" onclick="arcDismiss()">Cancel</button>
        </div>
      </div>

    <?php elseif ($current['mode'] === 'pin_remove'): ?>

      <!-- PIN REMOVE — confirm removal -->
      <div class="arc-step active" id="step-pin-remove">
        <div class="arc-aldric-response show">
          "Are you certain? This will disable borrowing until a new PIN is set."
        </div>
        <div class="arc-notice" id="pin-remove-error"></div>
        <div class="arc-btn-row">
          <button class="arc-btn arc-btn-primary" onclick="arcDoPinRemove()">Remove PIN</button>
          <button class="arc-btn arc-btn-ghost" onclick="arcDismiss()">Cancel</button>
        </div>
      </div>

    <?php elseif ($current['mode'] === 'pin_only'): ?>

      <!-- SCENARIO E: Logged in, someone else's NFC — PIN only -->
      <div class="arc-step active" id="step-pin">
        <div class="arc-field">
          <label>Owner's clearance PIN</label>
          <input type="password" id="pin-input" placeholder="Enter PIN…" maxlength="10">
        </div>
        <div class="arc-notice" id="pin-error"></div>
        <div class="arc-btn-row">
          <button class="arc-btn arc-btn-primary" onclick="arcDoPin()">Submit PIN</button>
        </div>
      </div>

    <?php endif; ?>

    <!-- Set PIN step (shown after first login with a Fragmenta if no PIN set) -->
    <div class="arc-step" id="step-set-pin">
      <div class="arc-aldric-response show">
        "One final matter. If you intend to share this Fragmenta with other citizens, the ARC requires you to set a clearance PIN. They will need it to borrow your card. You may skip this for now."
      </div>
      <div class="arc-field">
        <label>Set a clearance PIN (optional)</label>
        <input type="password" id="new-pin" placeholder="Leave blank to skip…" maxlength="10">
      </div>
      <div class="arc-btn-row">
        <button class="arc-btn arc-btn-primary" onclick="arcDoSetPin()">Confirm</button>
      </div>
    </div>

    <!-- Success stamp -->
    <div class="arc-stamp" id="arc-success">
      <div class="arc-stamp-seal">✦</div>
      <div class="arc-stamp-text">Clearance Granted</div>
      <div class="arc-stamp-sub" id="arc-success-msg">
        "Everything in order. You may proceed. And citizen — do try not to lose it."
      </div>
      <button class="arc-btn arc-btn-primary" onclick="arcProceed()">Proceed</button>
    </div>

  </div><!-- /arc-body -->

  <div class="arc-footer">
    ARC Registration Terminal &nbsp;·&nbsp; Ref. <?= strtoupper(substr(md5(microtime()), 0, 8)) ?>
  </div>

</div><!-- /arc-panel -->

<script>
(function() {

  var panel   = document.getElementById('arc-panel');
  var overlay = document.getElementById('arc-overlay');
  var scenario   = '<?= $scenario ?>';
  var nfcUid     = '<?= htmlspecialchars($nfc_uid ?? '') ?>';
  var redirectTo = '/hub.php';

  /* ── Open / Close ── */
  function arcOpenPanel() {
    panel.classList.add('open');
    overlay.classList.add('open');
    document.body.style.overflow = 'hidden';
  }

 window.arcClosePanel = arcDismiss;
	
  function arcDismiss() {
    panel.classList.remove('open');
    overlay.classList.remove('open');
    document.body.style.overflow = '';
    setTimeout(function() {
      var dest = <?= $logged_in ? "'/hub.php'" : "'/frontpage.php'" ?>;
      window.location.href = dest;
    }, 450);
  }

  document.getElementById('arc-close-btn').addEventListener('click', arcDismiss);
  overlay.addEventListener('click', arcDismiss);

  /* ── Step navigation ── */
  window.arcShowStep = function(stepId) {
    document.querySelectorAll('.arc-step').forEach(function(s) {
      s.classList.remove('active');
    });
    var el = document.getElementById(stepId);
    if (el) el.classList.add('active');
  };

  /* ── Show notice ── */
  function arcNotice(id, msg, type) {
    var el = document.getElementById(id);
    if (!el) return;
    el.textContent = msg;
    el.className   = 'arc-notice show ' + (type || 'err');
  }
  function arcClearNotice(id) {
    var el = document.getElementById(id);
    if (el) { el.className = 'arc-notice'; el.textContent = ''; }
  }

  /* ── Show success ── */
  function arcSuccess(msg) {
    document.querySelectorAll('.arc-step').forEach(function(s) {
      s.classList.remove('active');
    });
    var stamp = document.getElementById('arc-success');
    if (msg) document.getElementById('arc-success-msg').textContent = '"' + msg + '"';
    stamp.classList.add('show');
  }

	  /* ── Show success (register variant — no hub redirect) ── */
  function arcRegisterSuccess(msg) {
    document.querySelectorAll('.arc-step').forEach(function(s) {
      s.classList.remove('active');
    });
    var stamp = document.getElementById('arc-success');
    if (msg) document.getElementById('arc-success-msg').textContent = '"' + msg + '"';
    stamp.classList.add('show');
    var proceedBtn = stamp.querySelector('.arc-btn');
    if (proceedBtn) {
      proceedBtn.textContent = 'Return to login';
      proceedBtn.onclick = function() { window.location.reload(); };
    }
  }
  /* ── Proceed after success ── */
  window.arcProceed = function() {
    window.location.href = redirectTo;
  };

  /* ── AJAX helper ── */
  function arcPost(action, data, cb) {
    data.action = 'chards_arc';
    data.sub = action;
    fetch('/arc-handler.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
      body: Object.keys(data).map(function(k) {
        return encodeURIComponent(k) + '=' + encodeURIComponent(data[k]);
      }).join('&')
    })
    .then(function(r) { return r.json(); })
    .then(cb)
    .catch(function() { cb({ ok: false, msg: 'Connection error. Please try again.' }); });
  }

  /* ── LOGIN ── */
  window.arcDoLogin = function() {
    arcClearNotice('login-error');
    var u = (document.getElementById('login-username') || {}).value || '';
    var p = (document.getElementById('login-password') || {}).value || '';
    if (!u || !p) { arcNotice('login-error', 'Please fill in all fields.'); return; }

    arcPost('arc_login', { username: u, password: p }, function(res) {
      if (res.success) {
		if (res.data && res.data.remember_token && window.AppInventor) {
       		window.AppInventor.setWebViewString(res.data.remember_token); 
     	}
        if (res.data && res.data.needs_pin_setup && nfcUid) {
          arcShowStep('step-set-pin');
        } else if (res.data && res.data.needs_claim && nfcUid) {
          arcSuccess('Registration complete. The Fragmenta has been logged under your name. Everything in order.');
        } else {
          arcSuccess('Credentials verified. You may proceed, citizen.');
        }
      } else {
        arcNotice('login-error', (res.data ? res.data.msg : '') || 'Those credentials do not match our records.');
      }
    });
  };

  /* ── REGISTER ── */
  window.arcDoRegister = function() {
    arcClearNotice('reg-error');
    var u  = document.getElementById('reg-username').value;
    var e  = document.getElementById('reg-email').value;
    var p  = document.getElementById('reg-password').value;
    var p2 = document.getElementById('reg-password2').value;
    if (!u || !e || !p || !p2) { arcNotice('reg-error', 'All fields are required.'); return; }
    if (p !== p2) { arcNotice('reg-error', 'The passphrases do not match.'); return; }
    if (p.length < 8) { arcNotice('reg-error', 'Passphrase must be at least 8 characters.'); return; }

    arcPost('arc_register', { username: u, email: e, password: p }, function(res) {
      if (res.success) {
        arcRegisterSuccess('Your registration has been filed, ' + u + '. A sealed notice has been sent to your correspondence address' + String.fromCharCode(8212) + 'confirm it before you may enter the registry.');
      } else {
        arcNotice('reg-error', (res.data ? res.data.msg : '') || 'Registration failed. That username or email may already exist.');
      }
    });
  };
  /* ── BORROW (scenario C, non-owner) ── */
  window.arcDoBorrow = function() {
    arcClearNotice('borrow-error');
    var u   = document.getElementById('borrow-username').value;
    var p   = document.getElementById('borrow-password').value;
    var pin = document.getElementById('borrow-pin').value;
    if (!u || !p || !pin) { arcNotice('borrow-error', 'All fields are required.'); return; }

    arcPost('arc_borrow', { username: u, password: p, pin: pin }, function(res) {
      if (res.success) {
        arcSuccess('Temporary access granted. The owner has been noted. Proceed.');
      } else {
        arcNotice('borrow-error', (res.data ? res.data.msg : '') || 'Access denied. Invalid credentials or PIN.');
      }
    });
  };

  /* ── PIN (scenario E, already logged in) ── */
  window.arcDoPin = function() {
    arcClearNotice('pin-error');
    var pin = document.getElementById('pin-input').value;
    if (!pin) { arcNotice('pin-error', 'Please enter the clearance PIN.'); return; }

    arcPost('arc_pin', { pin: pin }, function(res) {
      if (res.success) {
        arcSuccess('PIN accepted. Temporary clearance granted. Proceed.');
      } else {
        arcNotice('pin-error', (res.data ? res.data.msg : '') || 'Incorrect PIN. Access denied.');
      }
    });
  };

  /* ── CLAIM (scenario D) ── */
 window.arcDoClaim = function() {
    arcClearNotice('claim-error');
    arcPost('arc_claim', {}, function(res) {
      if (res.success) {
        arcSuccess('Fragmenta registered to your name. Everything in order. You may proceed.');
      } else {
        var msg = res.data ? res.data.msg : '';
        if (msg === 'UNRECOGNISED_CARD') {
          arcNotice('claim-error',
            'This shard is not on our records. ' +
            'Unregistered Fragmenta are not permitted. ' +
            'If you believe this is an error, contact the ARC.',
            'err'
          );
        } else {
          arcNotice('claim-error', msg || 'Registration failed. Please try again.');
        }
      }
    });
  };

 /* ── PIN MANAGE (hub flow — set/update PIN) ── */
  window.arcDoPinManage = function() {
    arcClearNotice('pin-manage-error');
    var pin  = document.getElementById('pin-manage-input').value;
    var pin2 = document.getElementById('pin-manage-confirm').value;
    if (!pin) { arcNotice('pin-manage-error', 'Please enter a PIN.'); return; }
    if (pin !== pin2) { arcNotice('pin-manage-error', 'PINs do not match.'); return; }

    arcPost('arc_set_pin', { pin: pin }, function(res) {
      if (res.success) {
        arcSuccess('Clearance PIN updated. Guard it well, citizen.');
      } else {
        arcNotice('pin-manage-error', (res.data ? res.data.msg : '') || 'Error updating PIN.');
      }
    });
  };

  /* ── PIN REMOVE (hub flow — remove PIN) ── */
  window.arcDoPinRemove = function() {
    arcClearNotice('pin-remove-error');
    arcPost('arc_remove_pin', {}, function(res) {
      if (res.success) {
        arcSuccess('Clearance PIN removed. This Fragmenta can no longer be borrowed.');
      } else {
        arcNotice('pin-remove-error', (res.data ? res.data.msg : '') || 'Error removing PIN.');
      }
    });
  };

  /* ── SET PIN (optional, post login/register) ── */
  window.arcDoSetPin = function() {
    var pin = document.getElementById('new-pin').value;
    arcPost('arc_set_pin', { pin: pin }, function(res) {
      arcSuccess(
        pin
          ? 'PIN recorded. Guard it well — it determines who may borrow your Fragmenta.'
          : 'Noted. You may set a PIN at any time from your account settings. Proceed.'
      );
    });
  };

  /* ── Auto-open if triggered by login button or NFC ── */
  /* Called from your login button or from the Android app */
  window.arcOpen = function() { arcOpenPanel(); };

  /* If page loaded with NFC uid (posted from app), open immediately */
  arcOpenPanel();

})();
</script>
?>