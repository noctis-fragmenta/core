<?php
/* ============================================================
   Chards — User & Fragmenta Helper Functions
   Include this wherever pack opening logic runs.
   All functions use the $pdo connection passed as argument.
============================================================ */


/* ============================================================
   FREE ACCOUNT LIMITS (hardcoded — change here to adjust)
============================================================ */
if (!defined('FREE_COOLDOWN_MINUTES')) define('FREE_COOLDOWN_MINUTES', 1440);
if (!defined('FREE_STORY_CHANCE'))    define('FREE_STORY_CHANCE',    0);
if (!defined('FREE_CARDS_PER_PACK'))  define('FREE_CARDS_PER_PACK',  5);


/* ============================================================
   USER HELPERS
============================================================ */

/**
 * Find a user by username.
 */
if (!function_exists('chards_get_user_by_username')) {
function chards_get_user_by_username($pdo, $username) {
    $stmt = $pdo->prepare("SELECT * FROM chards_users WHERE username = ?");
    $stmt->execute([$username]);
    return $stmt->fetch() ?: null;
}
}


/**
 * Find a user by id.
 */
if (!function_exists('chards_get_user')) {
function chards_get_user($pdo, $user_id) {
    $stmt = $pdo->prepare("SELECT * FROM chards_users WHERE id = ?");
    $stmt->execute([$user_id]);
    return $stmt->fetch() ?: null;
}
}


/**
 * Register a new user. Returns new user id or throws on duplicate.
 */
if (!function_exists('chards_register_user')) {
	function chards_register_user($pdo, $username, $password, $email) {
		$token = bin2hex(random_bytes(32));
		$stmt = $pdo->prepare("
			INSERT INTO chards_users
				(username, password_hash, email, is_active, email_verified, confirm_token, confirm_token_expiry)
			VALUES (?, ?, ?, 1, 0, ?, DATE_ADD(NOW(), INTERVAL 24 HOUR))
		");
		$stmt->execute([
			$username,
			password_hash($password, PASSWORD_BCRYPT),
			strtolower(trim($email)),
			$token,
		]);
		return [
			'id'    => (int) $pdo->lastInsertId(),
			'token' => $token,
		];
	}
}

if (!function_exists('chards_confirm_email')) {
	function chards_confirm_email($pdo, $token) {
		$stmt = $pdo->prepare("
			UPDATE chards_users
			SET email_verified = 1, confirm_token = NULL, confirm_token_expiry = NULL
			WHERE confirm_token = ?
			  AND email_verified = 0
			  AND confirm_token_expiry > NOW()
		");
		$stmt->execute([$token]);
		return $stmt->rowCount() > 0;
	}
}

/**
 * Verify a user's password.
 */
if (!function_exists('chards_verify_password')) {
function chards_verify_password($password, $hash) {
    return password_verify($password, $hash);
}
}


/**
 * Verify a user's PIN (for Fragmenta sharing).
 */
if (!function_exists('chards_verify_pin')) {
function chards_verify_pin($pin, $hash) {
    return password_verify($pin, $hash);
}
}


/**
 * Set or update a user's PIN.
 */
if (!function_exists('chards_set_pin')) {
	function chards_set_pin($pdo, $user_id, $pin) {
		$pdo->prepare("UPDATE chards_users SET pin_hash = ? WHERE id = ?")
			->execute([password_hash($pin, PASSWORD_BCRYPT), $user_id]);
	}
}



/* ============================================================
   FRAGMENTA HELPERS
============================================================ */

/**
 * Look up a Fragmenta by its NFC UID.
 * Returns the Fragmenta row joined with its type.
 */
if (!function_exists('chards_get_fragmenta_by_uid')) {
function chards_get_fragmenta_by_uid($pdo, $uid) {
    $stmt = $pdo->prepare("
        SELECT f.*,
               ft.slug             AS type_slug,
               ft.name             AS type_name,
               ft.cooldown_minutes,
               ft.cards_per_pack,
               ft.story_chance,
               ft.minimum_rarity
        FROM chards_fragmenta f
        JOIN chards_fragmenta_types ft ON ft.id = f.type_id
        WHERE f.uid = ?
    ");
    $stmt->execute([$uid]);
    return $stmt->fetch() ?: null;
}
}


/**
 * Claim an unregistered Fragmenta for a user.
 * Returns true on success, false if already claimed.
 */
if (!function_exists('chards_claim_fragmenta')) {
function chards_claim_fragmenta($pdo, $uid, $user_id) {
    $stmt = $pdo->prepare("
        UPDATE chards_fragmenta
        SET owner_user_id = ?, registered_at = NOW()
        WHERE uid = ? AND owner_user_id IS NULL
    ");
    $stmt->execute([$user_id, $uid]);
    return $stmt->rowCount() > 0;
}
}


/**
 * Check if a user is authorised to use a Fragmenta.
 *
 * Rules:
 *   - Owner can always use it (unless locked, in which case only owner can)
 *   - Non-owner can use it if is_locked = 0 AND they provide the owner's PIN
 *
 * Returns: 'owner' | 'guest' | 'locked' | 'invalid_pin' | 'unregistered'
 */
if (!function_exists('chards_check_fragmenta_access')) {
function chards_check_fragmenta_access($pdo, $fragmenta, $user_id, $pin = null) {
    if ($fragmenta['owner_user_id'] === null) return 'unregistered';

    $is_owner = (int)$fragmenta['owner_user_id'] === $user_id;

    if ($is_owner) return 'owner';

    /* Non-owner access */
    if ($fragmenta['is_locked']) return 'locked';

    /* Non-owner needs to supply owner's PIN */
    $owner = chards_get_user($pdo, (int)$fragmenta['owner_user_id']);
    if (!$owner || !$owner['pin_hash']) return 'no_pin_set'; /* owner hasn't enabled sharing */

    if ($pin === null || !chards_verify_pin($pin, $owner['pin_hash'])) return 'invalid_pin';

    return 'guest';
}
}



/* ============================================================
   PACK ALLOWANCE CHECKS
============================================================ */

/**
 * Check if a free (no Fragmenta) account can open a pack.
 * Free accounts have a 24h cooldown (FREE_COOLDOWN_MINUTES) between packs.
 *
 * Returns array:
 *   ['allowed' => bool, 'reason' => string, 'resets_in' => int (seconds)]
 */
if (!function_exists('chards_free_can_open')) {
function chards_free_can_open($pdo, $user_id) {
    $stmt = $pdo->prepare("
        SELECT MAX(opened_at) FROM chards_pack_log
        WHERE user_id      = ?
          AND fragmenta_id IS NULL
    ");
    $stmt->execute([$user_id]);
    $last_opened = $stmt->fetchColumn();

    if (!$last_opened) {
        return ['allowed' => true, 'reason' => '', 'resets_in' => 0];
    }

    $cooldown_secs = FREE_COOLDOWN_MINUTES * 60;
    $next_open     = strtotime($last_opened) + $cooldown_secs;
    $resets_in     = $next_open - time();

    if ($resets_in > 0) {
        return [
            'allowed'   => false,
            'reason'    => 'cooldown',
            'resets_in' => $resets_in,
        ];
    }

    return ['allowed' => true, 'reason' => '', 'resets_in' => 0];
}
}


/**
 * Check if a Fragmenta can be used for a pack opening.
 * The cooldown_minutes value IS the limit — no separate daily counter.
 *
 * Returns array:
 *   ['allowed' => bool, 'reason' => string, 'resets_in' => int (seconds)]
 */
if (!function_exists('chards_fragmenta_can_open')) {
function chards_fragmenta_can_open($pdo, $fragmenta) {
    $cooldown_secs = (int)$fragmenta['cooldown_minutes'] * 60;

    if (!$fragmenta['last_used_at']) {
        return ['allowed' => true, 'reason' => '', 'resets_in' => 0];
    }

    $last_used = strtotime($fragmenta['last_used_at']);
    $next_open = $last_used + $cooldown_secs;
    $resets_in = $next_open - time();

    if ($resets_in > 0) {
        return [
            'allowed'   => false,
            'reason'    => 'cooldown',
            'resets_in' => $resets_in,
        ];
    }

    return ['allowed' => true, 'reason' => '', 'resets_in' => 0];
}
}


/**
 * Record a pack opening in the log and update Fragmenta last_used_at.
 * Call this immediately after a pack is generated.
 */
if (!function_exists('chards_log_pack_open')) {
function chards_log_pack_open($pdo, $user_id, $pack_id, $fragmenta_id = null) {
    $pdo->prepare("
        INSERT INTO chards_pack_log (user_id, fragmenta_id, pack_id)
        VALUES (?, ?, ?)
    ")->execute([$user_id, $fragmenta_id, $pack_id]);

    if ($fragmenta_id !== null) {
        $pdo->prepare("
            UPDATE chards_fragmenta SET last_used_at = NOW() WHERE id = ?
        ")->execute([$fragmenta_id]);
    }
}
}


/**
 * Get all Fragmenta owned by a user, with type info.
 */
if (!function_exists('chards_get_user_fragmenta')) {
function chards_get_user_fragmenta($pdo, $user_id) {
    $stmt = $pdo->prepare("
        SELECT f.*,
               ft.name            AS type_name,
               ft.cooldown_minutes,
               ft.cards_per_pack,
               ft.story_chance,
               ft.minimum_rarity,
               /* packs used on this Fragmenta in last 24h */
               (SELECT COUNT(*) FROM chards_pack_log pl
                WHERE pl.fragmenta_id = f.id
                  AND pl.opened_at > NOW() - INTERVAL 24 HOUR) AS recent_packs
        FROM chards_fragmenta f
        JOIN chards_fragmenta_types ft ON ft.id = f.type_id
        WHERE f.owner_user_id = ?
        ORDER BY f.registered_at ASC
    ");
    $stmt->execute([$user_id]);
    return $stmt->fetchAll();
}
}


/**
 * Get the effective pack config for a session.
 * Pass $fragmenta = null for a free account.
 *
 * Returns array with all pack-opening parameters resolved.
 */
if (!function_exists('chards_get_pack_config')) {
function chards_get_pack_config($fragmenta) {
    if ($fragmenta === null) {
        return [
            'cooldown_minutes' => FREE_COOLDOWN_MINUTES,
            'cards_per_pack'   => FREE_CARDS_PER_PACK,
            'story_chance'     => FREE_STORY_CHANCE,
            'minimum_rarity'   => 0,
            'is_free'          => true,
        ];
    }
    return [
        'cooldown_minutes' => (int)$fragmenta['cooldown_minutes'],
        'cards_per_pack'   => (int)$fragmenta['cards_per_pack'],
        'story_chance'     => (int)$fragmenta['story_chance'],
        'minimum_rarity'   => (int)$fragmenta['minimum_rarity'],
        'is_free'          => false,
    ];
}
}
?>