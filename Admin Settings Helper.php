add_action('wp_footer', function() {
    // Only output on the admin page
    if (!isset($_GET['tab']) && !is_page('admin')) return;
    ?>
    <script>
    (function(){
		  /* ── UID Generate + Copy ── */
		  var uidInput   = document.getElementById('frag-uid-input');
		  var uidCopyBtn = document.getElementById('frag-uid-copy');
		  var uidGenBtn  = document.getElementById('frag-uid-generate');

		  function showCopyIfNeeded() {
			if (uidInput && uidCopyBtn) {
			  uidCopyBtn.style.display = uidInput.value.trim() ? '' : 'none';
			}
		  }
		  if (uidInput)   uidInput.addEventListener('input', showCopyIfNeeded);
		  if (uidGenBtn)  uidGenBtn.addEventListener('click', function() {
			var bytes = [];
			for (var i = 0; i < 7; i++) {
			  bytes.push(('0' + Math.floor(Math.random() * 256).toString(16).toUpperCase()).slice(-2));
			}
			if (uidInput) uidInput.value = bytes.join(':');
			showCopyIfNeeded();
		  });
		  if (uidCopyBtn) uidCopyBtn.addEventListener('click', function() {
			if (!uidInput || !uidInput.value) return;
			navigator.clipboard.writeText(uidInput.value).then(function() {
			  uidCopyBtn.textContent = '✓ Copied!';
			  uidCopyBtn.style.color = '#8fcc9f';
			  setTimeout(function() { uidCopyBtn.textContent = '⎘ Copy'; uidCopyBtn.style.color = ''; }, 2000);
			}).catch(function() {
			  uidInput.select();
			  document.execCommand('copy');
			  uidCopyBtn.textContent = '✓ Copied!';
			  setTimeout(function() { uidCopyBtn.textContent = '⎘ Copy'; }, 2000);
			});
		  });

		  /* ── Card type toggle (collectible/event) ── */
		  var collType = document.getElementById('coll-type');
		  if (collType) {
			collType.addEventListener('change', function() {
			  var isEvent = this.value === 'event';
			  var note = document.getElementById('coll-event-note');
			  if (note) { note.style.display = isEvent ? '' : 'none'; note.querySelector('input').disabled = !isEvent; }
			});
		  }

		  /* ── Live image previews ── */
		  function livePreview(inputId, targetId, isWrap) {
			var inp = document.getElementById(inputId);
			if (!inp) return;
			inp.addEventListener('input', function() {
			  var url = this.value.trim();
			  if (isWrap) {
				var w = document.getElementById(targetId);
				if (w) w.innerHTML = url
				  ? '<img src="' + url + '" style="width:100%;height:100%;object-fit:cover">'
				  : '<span class="ca-preview-empty">Preview</span>';
			  } else {
				var img = document.getElementById(targetId);
				if (img) img.src = url;
			  }
			});
		  }
		  livePreview('add-img-url',  'add-preview-wrap',  true);
		  livePreview('edit-img-url', 'edit-img-preview',  false);
		  livePreview('coll-img-url', 'coll-preview-wrap', true);

		  document.querySelectorAll('[id^="tier-url-"]').forEach(function(inp) {
			var tierId = inp.id.replace('tier-url-', '');
			inp.addEventListener('input', function() {
			  var wrap = document.getElementById('tier-preview-' + tierId);
			  var img  = document.getElementById('tier-img-' + tierId);
			  if (!this.value.trim()) return;
			  if (img) { img.src = this.value.trim(); }
			  else if (wrap) { wrap.innerHTML = '<img src="' + this.value.trim() + '" style="width:100%;height:100%;object-fit:cover">'; }
			});
		  });

		  /* ── Auto-scroll ── */
		  if (window.location.hash) {
		  var scrollTarget = document.querySelector(window.location.hash);
		  if (scrollTarget) scrollTarget.scrollIntoView({ behavior:'smooth', block:'start' });
		}

		  /* ── AJAX core ── */
		  var AJAX_URL = '/wp-admin/admin-ajax.php';

		  function caSubmit(formEl, sub, msgId, onSuccess) {
			var data = new FormData(formEl);
			data.delete('action');
			data.append('action', 'chards_admin');
			data.append('sub', sub);

			var msgEl = msgId ? document.getElementById(msgId) : null;
			if (msgEl) { msgEl.className = 'ca-msg info'; msgEl.textContent = 'Saving…'; msgEl.style.display = ''; }

			var xhr = new XMLHttpRequest();
			xhr.open('POST', AJAX_URL);
			xhr.onload = function() {
			  try {
				var res = JSON.parse(xhr.responseText);
				if (msgEl) {
				  msgEl.className = 'ca-msg ' + (res.success ? 'ok' : 'err');
				  msgEl.textContent = (res.data && res.data.msg) ? res.data.msg : (res.success ? 'Done.' : 'Error.');
				  msgEl.style.display = '';
				}
				if (res.success) {
				  if (onSuccess) onSuccess(res);
				  else setTimeout(function() { window.location.reload(); }, 800);
				}
			  } catch(e) {
				if (msgEl) { msgEl.className = 'ca-msg err'; msgEl.textContent = 'Bad response: ' + xhr.responseText.substring(0, 120); msgEl.style.display = ''; }
			  }
			};
			xhr.onerror = function() {
			  if (msgEl) { msgEl.className = 'ca-msg err'; msgEl.textContent = 'Connection error.'; msgEl.style.display = ''; }
			};
			xhr.send(data);
		  }

		  /* ── Wire up all data-ca-submit buttons ── */
		  document.addEventListener('click', function(e) {
			var btn = e.target.closest('[data-ca-submit]');
			if (!btn) return;
			e.preventDefault();
			var formId = btn.getAttribute('data-ca-submit');
			var sub    = btn.getAttribute('data-ca-sub');
			var msgId  = btn.getAttribute('data-ca-msg');
			var form   = document.getElementById(formId);
			if (!form) return;
			var required = form.querySelectorAll('[required]');
			for (var i = 0; i < required.length; i++) {
			  if (!required[i].value.trim()) {
				try { required[i].focus(); } catch(e) {}
				if (msgId) {
				  var me = document.getElementById(msgId);
				  if (me) { me.className = 'ca-msg err'; me.textContent = 'Please fill in all required fields.'; me.style.display = ''; }
				}
				return;
			  }
			}
			caSubmit(form, sub, msgId);
		  });

		  /* ── Reset finds ── */
		  document.addEventListener('click', function(e) {
			var btn = e.target.closest('[data-ca-reset]');
			if (!btn) return;
			e.preventDefault();
			if (!confirm('Reset ALL found panels in this chapter? This cannot be undone.')) return;
			caSubmit(btn.closest('form'), 'reset_finds', null);
		  });

		  /* ── Fragmenta: toggle lock ── */
		  window.toggleLock = function(fragId, btn) {
			if (!confirm('Toggle lock status for this Fragmenta?')) return;
			var payload = new FormData();
			payload.append('action',  'chards_admin');
			payload.append('sub',     'toggle_fragmenta_lock');
			payload.append('frag_id', fragId);
			var xhr = new XMLHttpRequest();
			xhr.open('POST', AJAX_URL);
			xhr.onload = function() {
			  try {
				var res = JSON.parse(xhr.responseText);
				if (res.success) {
				  var pill = document.getElementById('frag-status-' + fragId);
				  if (pill) {
					pill.textContent = res.data.is_locked ? 'Locked' : 'Active';
					pill.className   = 'ca-pill ' + (res.data.is_locked ? 'pill-locked' : 'pill-unlocked');
				  }
				  btn.textContent = res.data.is_locked ? 'Unlock' : 'Lock';
				} else {
				  alert(res.data ? res.data.msg : 'Error toggling lock.');
				}
			  } catch(e) { alert('Unexpected response.'); }
			};
			xhr.send(payload);
		  };

		  /* ── Fragmenta: delete ── */
		  window.deleteFragmenta = function(fragId, uid) {
			if (!confirm('Permanently delete Fragmenta "' + uid + '"? This cannot be undone.')) return;
			var payload = new FormData();
			payload.append('action',  'chards_admin');
			payload.append('sub',     'delete_fragmenta');
			payload.append('frag_id', fragId);
			var xhr = new XMLHttpRequest();
			xhr.open('POST', AJAX_URL);
			xhr.onload = function() {
			  try {
				var res = JSON.parse(xhr.responseText);
				if (res.success) {
				  var row = document.getElementById('frag-row-' + fragId);
				  if (row) row.remove();
				} else {
				  alert(res.data ? res.data.msg : 'Error deleting.');
				}
			  } catch(e) { alert('Unexpected response.'); }
			};
			xhr.send(payload);
		  };

		  /* ── Fragmenta type: delete ── */
		  window.deleteType = function(typeId, name) {
			if (!confirm('Delete type "' + name + '"? This will fail if any Fragmenta cards use it.')) return;
			var payload = new FormData();
			payload.append('action',  'chards_admin');
			payload.append('sub',     'delete_fragmenta_type');
			payload.append('type_id', typeId);
			var xhr = new XMLHttpRequest();
			xhr.open('POST', AJAX_URL);
			xhr.onload = function() {
			  try {
				var res = JSON.parse(xhr.responseText);
				if (res.success) {
				  var row = document.getElementById('type-row-' + typeId);
				  if (row) row.remove();
				} else {
				  alert(res.data ? res.data.msg : 'Error deleting type.');
				}
			  } catch(e) { alert('Unexpected response.'); }
			};
			xhr.send(payload);
		  };

		  /* ── Fragmenta: activity log drawer ── */
		  window.toggleLog = function(fragId) {
			var drawer = document.getElementById('frag-log-' + fragId);
			if (!drawer) return;
			var isOpen = drawer.classList.contains('open');
			document.querySelectorAll('.ca-log.open').forEach(function(d) { d.classList.remove('open'); });
			if (isOpen) return;
			drawer.classList.add('open');
			var body = document.getElementById('frag-log-body-' + fragId);
			if (body.dataset.loaded) return;
			var payload = new FormData();
			payload.append('action',  'chards_admin');
			payload.append('sub',     'fragmenta_log');
			payload.append('frag_id', fragId);
			var xhr = new XMLHttpRequest();
			xhr.open('POST', AJAX_URL);
			xhr.onload = function() {
			  try {
				var res = JSON.parse(xhr.responseText);
				if (res.success && res.data.log.length) {
				  var html = '';
				  res.data.log.forEach(function(row) {
					var who = row.username || row.finder_name || 'Unknown';
					html += '<div class="ca-log-row">'
					  + '<span class="ca-log-time">' + row.opened_at + '</span>'
					  + '<span>' + who + '</span>'
					  + '</div>';
				  });
				  body.innerHTML = html;
				} else {
				  body.innerHTML = '<span class="ca-log-empty">No pack opens recorded yet.</span>';
				}
				body.dataset.loaded = '1';
			  } catch(e) { body.innerHTML = '<span class="ca-log-empty">Error loading log.</span>'; }
			};
			xhr.send(payload);
		  };
    })();
    </script>
    <?php
});