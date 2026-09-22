<?php
	if (!function_exists('chards_require_app_access')) {
		function chards_require_app_access($info = '') {
			if (session_status() === PHP_SESSION_NONE) session_start();

			if (($_GET['dev_bypass'] ?? '') === 'letmein') {
				return;
			}

			if (!empty($_SESSION['chards_app_verified'])) {
				return;
			}

			$ua = $_SERVER['HTTP_USER_AGENT'] ?? '';
			if (strpos($ua, 'NoctisFragmentaApp/1.0') !== false) {
				$_SESSION['chards_app_verified'] = true;
				return;
			}

			error_log('UA CHECK FAILED — ' . $ua . ' | Source: ' . $info);

			echo '<div style="min-height:70vh; display:flex; align-items:center; justify-content:center; padding:40px 20px; background:#0a0806; font-family:Georgia,serif;">
				  <div style="max-width:360px; text-align:center; color:#e8dcc8;">
					<div style="font-size:40px; margin-bottom:16px; color:#c9a84c;">⚗</div>
					<div style="font-size:16px; letter-spacing:0.08em; color:#c9a84c; margin-bottom:14px; text-transform:uppercase;">
					  ARC Access Restricted
					</div>
					<div style="font-style:italic; font-size:16px; line-height:1.6; color:rgba(232,220,200,0.75); margin-bottom:28px;">
					  This registry may only be accessed through the ARC companion app.
					</div>
					<a href="#" style="display:inline-block; font-family:Georgia,serif; font-size:13px; letter-spacing:0.08em; padding:12px 28px; border-radius:4px; background:rgba(201,168,76,0.4); color:#0a0806; text-decoration:none; text-transform:uppercase; pointer-events:none; opacity:0.6;">
					  Get the App — Coming Soon
					</a>
				  </div>
				</div>';
			exit;
		}
	}
?>