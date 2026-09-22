/* ============================================================
   CHARDS — Landing Page Logic
   Runs on the 'template_redirect' hook so headers aren't sent yet.
   Deploy via Code Snippets, Run Everywhere.
   WordPress page slug: 'landing'
============================================================ */

if (!function_exists('chards_landing_redirect')) {
    function chards_landing_redirect() {
echo '<div style="position:fixed;top:0;left:0;z-index:9999;background:red;color:white;padding:20px;font-size:20px">SHORTCODE IS RUNNING</div>';
       if (!session_id()) session_start();

        /* ── Capture card ID ── */
        $_SESSION['cardID'] = isset($_GET['card']) && $_GET['card'] !== ''
            ? trim($_GET['card'])
            : null;

        /* ── DB ── */
        $pdo = null;
        try {
            $pdo = new PDO(
                'mysql:host=db.fr-pari1.bengt.wasmernet.com;port=10272;dbname=dbGQr4derPM6nWoRL4jPwNPN;charset=utf8mb4',
                '0074c9f9784980005ded7796ee74',
                '06950074-c9fa-7db2-8000-c0aec36ba1ac',
                [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC]
            );
        } catch (PDOException $e) {
			echo '<script type="text/javascript">
			window.location = "https://chards.wasmer.app/index.php/login/"
			</script>';
        }

        $logged_in = !empty($_SESSION['chards_user_id']);

        if ($logged_in) {
            $user_id = (int)$_SESSION['chards_user_id'];

            if ($_SESSION['cardID']) {
                $stmt = $pdo->prepare("
                    SELECT f.*, ft.cooldown_minutes, ft.cards_per_pack, ft.story_chance, ft.minimum_rarity
                    FROM chards_fragmenta f
                    JOIN chards_fragmenta_types ft ON ft.id = f.type_id
                    WHERE f.uid = ?
                ");
                $stmt->execute([$_SESSION['cardID']]);
                $fragmenta = $stmt->fetch();

                if (!$fragmenta) {
                    $_SESSION['arc_scenario'] = 'unknown_card';
					echo '<script type="text/javascript">
					window.location = "https://chards.wasmer.app/index.php/login/"
					</script>';
                }

                $owner_id = $fragmenta['owner_user_id'] ? (int)$fragmenta['owner_user_id'] : null;

                if ($owner_id === null) {
                    /* Scenario D — logged in, unassigned NFC */
                    $_SESSION['arc_scenario']  = 'D';
                    $_SESSION['arc_fragmenta'] = $fragmenta;
                    echo '<script type="text/javascript">
					window.location = "https://chards.wasmer.app/index.php/login/"
					</script>';
                }

                if ($owner_id === $user_id) {
                    /* Scenario F — own card, go straight to pack opening */
                    $_SESSION['arc_scenario']         = 'F';
                    $_SESSION['chards_fragmenta_uid'] = $_SESSION['cardID'];
					echo '<script type="text/javascript">
					window.location = "https://chards.wasmer.app/index.php/pack-opening/"
					</script>';
                }

                /* Someone else's card — check if sharing is enabled */
                $owner_row = $pdo->prepare("SELECT pin_hash FROM chards_users WHERE id = ?");
                $owner_row->execute([$owner_id]);
                $owner_data = $owner_row->fetch();

                if (!$fragmenta['is_locked'] && !empty($owner_data['pin_hash'])) {
                    $_SESSION['arc_scenario'] = 'E';
                } else {
                    $_SESSION['arc_scenario'] = 'no_pin_set';
                }
                $_SESSION['arc_fragmenta'] = $fragmenta;
                echo '<script type="text/javascript">
				window.location = "https://chards.wasmer.app/index.php/login/"
				</script>';

            } else {
                /* Logged in, no card — free play */
                $_SESSION['arc_scenario'] = 'free';
                echo '<script type="text/javascript">
				window.location = "https://chards.wasmer.app/index.php/login/"
				</script>';
            }

        } else {
            /* Not logged in */
            if ($_SESSION['cardID']) {
                $stmt = $pdo->prepare("
                    SELECT f.*, ft.cooldown_minutes, ft.cards_per_pack, ft.story_chance, ft.minimum_rarity
                    FROM chards_fragmenta f
                    JOIN chards_fragmenta_types ft ON ft.id = f.type_id
                    WHERE f.uid = ?
                ");
                $stmt->execute([$_SESSION['cardID']]);
                $fragmenta = $stmt->fetch();

                if (!$fragmenta) {
                    $_SESSION['arc_scenario']  = 'B_new_card';
                    $_SESSION['arc_fragmenta'] = null;
                   echo ' <script type="text/javascript">
					window.location = "https://chards.wasmer.app/index.php/login/"
					</script>';
                }

                if ($fragmenta['owner_user_id'] === null) {
                    /* Scenario B — unassigned NFC */
                    $_SESSION['arc_scenario']  = 'B';
                    $_SESSION['arc_fragmenta'] = $fragmenta;
                } else {
                    /* Check sharing */
                    $owner = $pdo->prepare("SELECT username, pin_hash FROM chards_users WHERE id = ?");
                    $owner->execute([$fragmenta['owner_user_id']]);
                    $owner_data = $owner->fetch();

                    if (!$fragmenta['is_locked'] && !empty($owner_data['pin_hash'])) {
                        $_SESSION['arc_scenario'] = 'C';
                    } else {
                        $_SESSION['arc_scenario'] = 'no_pin_set';
                    }
                    $_SESSION['arc_fragmenta']       = $fragmenta;
                    $_SESSION['arc_fragmenta_owner'] = $owner_data['username'] ?? '';
                }

            } else {
                /* Scenario A — no card, not logged in */
                $_SESSION['arc_scenario']  = 'A';
                $_SESSION['arc_fragmenta'] = null;
            }

          echo '<script type="text/javascript">
			window.location = "https://chards.wasmer.app/index.php/login/"
			</script>';

        }
    }
}
add_action('template_redirect', 'chards_landing_redirect');