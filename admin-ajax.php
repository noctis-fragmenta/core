<?php
if (!function_exists('chards_arc_ajax_handler')) {

   function chards_arc_ajax_handler() {

    if (!session_id()) session_start();
        if (!session_id()) session_start();
        
       require_once __DIR__ . '/config/db-connection.php';

        $sub = $_POST['sub'] ?? '';
        $nfc = $_SESSION['cardID'] ?? null;

        /* ── LOGIN ── */
        if ($sub === 'arc_login') {
            $u    = trim($_POST['username'] ?? '');
            $p    = trim($_POST['password'] ?? '');
            $user = chards_get_user_by_username($pdo, $u);
           if (!$user || !chards_verify_password($p, $user['password_hash'])) {
                wp_send_json_error(['msg' => 'Those credentials do not match our records.']);
            }
            if (!$user['is_active']) {
                wp_send_json_error(['msg' => 'This account has been suspended by the ARC.']);
            }
            if (!$user['email_verified']) {
                wp_send_json_error(['msg' => 'Your registration has not yet been confirmed. Check your correspondence for the confirmation notice from the ARC.']);
            }
            $_SESSION['chards_user_id']  = $user['id'];
            $_SESSION['chards_username'] = $user['username'];
			
            /* ── Set remember me cookie ── */
            $raw_token = bin2hex(random_bytes(32));
            $pdo->prepare("
                INSERT INTO chards_remember_tokens (user_id, token_hash, expires_at)
                VALUES (?, ?, DATE_ADD(NOW(), INTERVAL 30 DAY))
            ")->execute([$user['id'], hash('sha256', $raw_token)]);
            setcookie('chards_remember', $raw_token, time() + (30 * 24 * 3600), '/', '', true, true);

            $needs_claim     = false;
            $needs_pin_setup = false;
            if ($nfc) {
                $frag = chards_get_fragmenta_by_uid($pdo, $nfc);
                if ($frag && $frag['owner_user_id'] === null) {
                    chards_claim_fragmenta($pdo, $nfc, $user['id']);
                    $needs_claim     = true;
                    $needs_pin_setup = !$user['pin_hash'];
                }
            }
            wp_send_json_success(['needs_claim' => $needs_claim, 'needs_pin_setup' => $needs_pin_setup, 'remember_token'  => $raw_token]);
        }
		
        /* ── REGISTER ── */
        elseif ($sub === 'arc_register') {
            $u = trim($_POST['username'] ?? '');
            $e = trim($_POST['email']    ?? '');
            $p = trim($_POST['password'] ?? '');
            try {
                $result = chards_register_user($pdo, $u, $p, $e);

				$verify_link = home_url('/index.php/verify-app/') . '?token=' . urlencode($result['token']);

                wp_mail(
                    $e,
                    'Confirm your ARC registration',
                    "Citizen {$u},\n\nYour registration with the Aenephsy Regulation Compendium is nearly complete. Confirm your correspondence address to finalise your record:\n\n{$verify_link}\n\nIf you did not request this, disregard this notice.\n\n" . chr(8212) . " Aldric Voss, Senior Registrar"
                );

             //   wp_send_json_success([]);
            } catch (PDOException $e2) {
                wp_send_json_error(['msg' => 'That username or email is already registered.']);
            }
        }
		
        /* ── BORROW ── */
        elseif ($sub === 'arc_borrow') {
            $u    = trim($_POST['username'] ?? '');
            $p    = trim($_POST['password'] ?? '');
            $pin  = trim($_POST['pin']      ?? '');
            $user = chards_get_user_by_username($pdo, $u);
            if (!$user || !chards_verify_password($p, $user['password_hash'])) {
                wp_send_json_error(['msg' => 'Invalid credentials.']);
            }
            $frag = $nfc ? chards_get_fragmenta_by_uid($pdo, $nfc) : null;
            if (!$frag) wp_send_json_error(['msg' => 'Fragmenta not found.']);

            $access = chards_check_fragmenta_access($pdo, $frag, (int)$user['id'], $pin);
            if ($access === 'guest' || $access === 'owner') {
                $_SESSION['chards_user_id']      = $user['id'];
                $_SESSION['chards_username']      = $user['username'];
                $_SESSION['chards_fragmenta_uid'] = $nfc;
                wp_send_json_success([]);
            } elseif ($access === 'no_pin_set') {
                wp_send_json_error(['msg' => 'This Fragmenta has not been cleared for shared access.']);
            } elseif ($access === 'locked') {
                wp_send_json_error(['msg' => 'This Fragmenta is locked by its owner.']);
            } else {
                wp_send_json_error(['msg' => 'Incorrect PIN. Access denied.']);
            }
        }

        /* ── PIN ONLY (scenario E) ── */
        elseif ($sub === 'arc_pin') {
            $pin  = trim($_POST['pin'] ?? '');
            $frag = $nfc ? chards_get_fragmenta_by_uid($pdo, $nfc) : null;
            if (!$frag) wp_send_json_error(['msg' => 'Fragmenta not found.']);

            $uid    = $_SESSION['chards_user_id'] ?? 0;
            $access = chards_check_fragmenta_access($pdo, $frag, (int)$uid, $pin);
            if ($access === 'guest') {
                $_SESSION['chards_fragmenta_uid'] = $nfc;
                wp_send_json_success([]);
            } elseif ($access === 'no_pin_set') {
                wp_send_json_error(['msg' => 'This Fragmenta has not been cleared for shared access.']);
            } elseif ($access === 'locked') {
                wp_send_json_error(['msg' => 'This Fragmenta is locked. The owner has disabled sharing.']);
            } else {
                wp_send_json_error(['msg' => 'Incorrect PIN. Access denied.']);
            }
        }
 /* ── CLAIM (scenario D — logged in, unassigned NFC) ── */
        elseif ($sub === 'arc_claim') {
            $uid = $_SESSION['chards_user_id'] ?? null;
            if (!$uid || !$nfc) wp_send_json_error(['msg' => 'Session or NFC error.']);

            /* Check if this UID exists in the system at all */
            $exists = $pdo->prepare(
                "SELECT COUNT(id) FROM chards_fragmenta WHERE uid = ?"
            );
            $exists->execute([$nfc]);
            $count = (int)$exists->fetchColumn();

            if ($count === 0) {
                /* UID not in DB — not an official card */
                wp_send_json_error(['msg' => 'UNRECOGNISED_CARD']);
            }

            $ok = chards_claim_fragmenta($pdo, $nfc, $uid);
            if ($ok) {
                $_SESSION['chards_fragmenta_uid'] = $nfc;
                wp_send_json_success([]);
            } else {
                wp_send_json_error(['msg' => 'This Fragmenta is already registered to another citizen.']);
            }
        }

 /* ── SET PIN ── */
        elseif ($sub === 'arc_set_pin') {
            $uid = $_SESSION['chards_user_id'] ?? null;
            $pin = trim($_POST['pin'] ?? '');
            if (!$uid) wp_send_json_error(['msg' => 'Session error.']);
            if ($pin) chards_set_pin($pdo, $uid, $pin);
            if ($nfc) $_SESSION['chards_fragmenta_uid'] = $nfc;
            wp_send_json_success();
        }

        /* ── REMOVE PIN ── */
        elseif ($sub === 'arc_remove_pin') {
            $uid = $_SESSION['chards_user_id'] ?? null;
            if (!$uid) wp_send_json_error(['msg' => 'Session error.']);
            $pdo->prepare(
                "UPDATE chards_users SET pin_hash = NULL WHERE id = ?"
            )->execute([$uid]);
            $_SESSION['PIN_TARGET_UID'] = null;
            wp_send_json_success();
        }

     	/* ── SET ACTION FLAGS ── */
        elseif ($sub === 'set_register_flag') {
            $_SESSION['REGISTER_CARD'] = true;
            $_SESSION['SET_PIN']       = false;
            $_SESSION['REMOVE_PIN']    = false;
            wp_send_json_success();
        }
        elseif ($sub === 'set_pin_flag') {
            $_SESSION['SET_PIN']        = true;
            $_SESSION['REGISTER_CARD']  = false;
            $_SESSION['REMOVE_PIN']     = false;
            wp_send_json_success();
        }
        elseif ($sub === 'set_remove_pin_flag') {
            $_SESSION['REMOVE_PIN']     = true;
            $_SESSION['REGISTER_CARD']  = false;
            $_SESSION['SET_PIN']        = false;
            wp_send_json_success();
        }

        /* ── RESET FLAGS ── */
        elseif ($sub === 'reset_flags') {
            $_SESSION['REGISTER_CARD']  = false;
            $_SESSION['SET_PIN']        = false;
            $_SESSION['REMOVE_PIN']     = false;
            $_SESSION['PIN_TARGET_UID'] = null;
            wp_send_json_success();
        }
        wp_die();
    }
}
add_action('wp_ajax_chards_arc',        'chards_arc_ajax_handler');
add_action('wp_ajax_nopriv_chards_arc', 'chards_arc_ajax_handler');
?>