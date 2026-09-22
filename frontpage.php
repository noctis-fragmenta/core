<!DOCTYPE html>
<html lang="en">
	<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Noctis Fragmenta</title>
	<link rel="preconnect" href="https://fonts.googleapis.com">
	<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
	<link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@400;600&family=Space+Grotesk:wght@400;500;600&display=swap" rel="stylesheet">
	<style>
	  :root{
		--void: #0a0713;
		--void-deep: #050308;
		--crystal-violet: #9d5cff;
		--crystal-violet-deep: #6d2fd6;
		--crystal-cyan: #49e0d2;
		--arc-gold: #c9a35f;
		--text: #e9e6f5;
		--text-dim: #7a729c;
	  }

	  *{box-sizing:border-box; margin:0; padding:0;}

	 html, body {
		  background-image: url('/media/background.png');
		  background-size: cover;
		  background-position: center;
		  background-attachment: fixed;
		}

	  .nf-stage{
		position: relative;
		height: 280vh;
	  }

	  .nf-pin{
		position: sticky;
		top: 0;
		height: 100vh;
		width: 100%;
		overflow: hidden;
		display: flex;
		align-items: center;
		justify-content: center;
		background:
		  radial-gradient(ellipse at 50% 40%, rgba(157,92,255,0.16), transparent 60%),
		  radial-gradient(ellipse at 50% 100%, rgba(109,47,214,0.10), transparent 55%);
	  }

	  canvas#nf-particles{
		position: absolute;
		inset: 0;
		width: 100%;
		height: 100%;
		display: block;
	  }

	  .nf-hero{
		position: relative;
		z-index: 3;
		display: flex;
		flex-direction: column;
		align-items: center;
		text-align: center;
		padding: 0 24px;
	  }

	  .nf-logo{
		width: min(800px, 90vw);
		filter: drop-shadow(0 0 28px rgba(139,107,255,0.35));
		user-select: none;
		-webkit-user-drag: none;
	  }

	  /* Fallback wordmark if no image logo is supplied yet */
	  .nf-logo-text{
		font-family: 'Cinzel', serif;
		font-weight: 600;
		font-size: clamp(2.2rem, 6vw, 4.2rem);
		letter-spacing: 0.08em;
		line-height: 1.05;
		background: linear-gradient(180deg, #f2eaff 0%, #c19bff 40%, var(--crystal-violet) 75%, var(--crystal-violet-deep) 100%);
		-webkit-background-clip: text;
		background-clip: text;
		color: transparent;
		filter: drop-shadow(0 0 26px rgba(157,92,255,0.45));
	  }

	  .nf-tagline{
		margin-top: 18px;
		font-family: 'Cinzel', serif;
		font-size: 0.78rem;
		letter-spacing: 0.32em;
		text-transform: uppercase;
		color: var(--text-dim);
	  }

	  /* ---- ARC terminal / login panel ---- */
	  .nf-terminal{
		margin-top: 46px;
		opacity: 0;
		transform: translateY(18px) scale(0.94);
		will-change: opacity, transform;
		pointer-events: none;
	  }

	  .nf-facet{
		position: relative;
		padding: 26px 40px 30px;
		clip-path: polygon(6% 0%, 94% 0%, 100% 30%, 100% 100%, 0% 100%, 0% 30%);
		background: linear-gradient(180deg, rgba(26,17,41,0.92), rgba(12,8,20,0.95));
		border: 1px solid rgba(157,92,255,0.3);
	  }

	  .nf-facet::before{
		content:"";
		position:absolute;
		top:0; left:0; right:0; height:1px;
		background: linear-gradient(90deg, transparent, var(--crystal-violet), var(--crystal-cyan), var(--crystal-violet), transparent);
		opacity: 0.75;
	  }

	  .nf-facet-label{
		display:block;
		font-family: 'Cinzel', serif;
		font-size: 0.66rem;
		letter-spacing: 0.28em;
		text-transform: uppercase;
		color: var(--crystal-cyan);
		margin-bottom: 18px;
		text-align: center;
	  }

	  .nf-actions{
		display: flex;
		gap: 14px;
	  }

	  .nf-btn{
		font-family: 'Space Grotesk', sans-serif;
		font-size: 0.92rem;
		font-weight: 500;
		letter-spacing: 0.02em;
		padding: 13px 28px;
		border-radius: 2px;
		cursor: pointer;
		border: 1px solid transparent;
		transition: transform 0.18s ease, box-shadow 0.18s ease, background 0.18s ease;
	  }

	  .nf-btn-primary{
		background: linear-gradient(135deg, var(--crystal-violet), #6a4de0);
		color: #fff;
		box-shadow: 0 0 0 rgba(139,107,255,0);
	  }
	  .nf-btn-primary:hover{
		box-shadow: 0 0 24px rgba(139,107,255,0.55);
		transform: translateY(-1px);
	  }

	  .nf-btn-secondary{
		background: transparent;
		border-color: rgba(233,230,245,0.25);
		color: var(--text);
	  }
	  .nf-btn-secondary:hover{
		border-color: var(--crystal-cyan);
		color: var(--crystal-cyan);
	  }

	  .nf-scroll-hint{
		position: absolute;
		bottom: 34px;
		left: 50%;
		transform: translateX(-50%);
		z-index: 3;
		font-family: 'Cinzel', serif;
		font-size: 0.92rem;
		letter-spacing: 0.3em;
		text-transform: uppercase;
		color: var(--text);
  		text-shadow: 0 0 10px rgba(0,0,0,0.8), 0 0 4px rgba(0,0,0,0.9);
		display: flex;
		flex-direction: column;
		align-items: center;
		gap: 10px;
	  }

	  .nf-scroll-hint span.line{
		width: 1px;
		height: 26px;
		background: linear-gradient(180deg, var(--crystal-cyan), transparent);
		animation: nf-hint-pulse 1.8s ease-in-out infinite;
	  }

	  @keyframes nf-hint-pulse{
		0%, 100%{ opacity: 0.25; }
		50%{ opacity: 1; }
	  }

	  @media (prefers-reduced-motion: reduce){
		.nf-terminal{ transition: none; }
		.nf-scroll-hint span.line{ animation: none; }
	  }

	  @media (max-width: 560px){
		.nf-facet{ padding: 22px 22px 26px; }
		.nf-actions{ flex-direction: column; width: 220px; }
	  }
	</style>
	</head>
	<body>

	<div class="nf-stage" id="nf-stage">
	  <div class="nf-pin">
		<canvas id="nf-particles"></canvas>

		<div class="nf-hero">
		  <img class="nf-logo" src="/media/nflogo.png" alt="Noctis Fragmenta">
		  <div class="nf-terminal" id="nf-terminal">
			<div class="nf-facet">
			  <span class="nf-facet-label">ARC Access Terminal</span>
			  <div class="nf-actions">
				<button class="nf-btn nf-btn-primary" id="nf-login-btn">Enter</button>
				<button class="nf-btn nf-btn-secondary" id="nf-register-btn">More Info</button>
			  </div>
			</div>
		  </div>
		</div>

		<div class="nf-scroll-hint" id="nf-scroll-hint">
		  <span>Scroll</span>
		  <span class="line"></span>
		</div>
	  </div>
	</div>

	<script>
	(function(){
	  var stage = document.getElementById('nf-stage');
	  var canvas = document.getElementById('nf-particles');
	  var ctx = canvas.getContext('2d');
	  var terminal = document.getElementById('nf-terminal');
	  var hint = document.getElementById('nf-scroll-hint');

	  var reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

	  var W = 0, H = 0, DPR = Math.min(window.devicePixelRatio || 1, 2);

	  function resize(){
		W = window.innerWidth;
		H = window.innerHeight;
		canvas.width = W * DPR;
		canvas.height = H * DPR;
		canvas.style.width = W + 'px';
		canvas.style.height = H + 'px';
		ctx.setTransform(DPR, 0, 0, DPR, 0, 0);
	  }
	  resize();
	  window.addEventListener('resize', resize);

	  var COUNT = reduceMotion ? 0 : (W < 640 ? 40 : 80);
	  var particles = [];

	  function rand(a, b){ return a + Math.random() * (b - a); }

	  for (var i = 0; i < COUNT; i++){
		particles.push({
		  angle: rand(0, Math.PI * 2),
		  rStart: rand(H * 0.45, H * 0.95),
		  rTarget: rand(35, 75),
		  spin: rand(-0.06, 0.06),
		  size: rand(2, 5),
		  hueMix: Math.random(),
		  wobble: rand(0, Math.PI * 2),
		  wobbleSpeed: rand(0.4, 1.1)
		});
	  }

	  // ---- Crystal: a 3D wireframe gem (hexagonal bipyramid) the particles trace ----
	  var crystalVerts3D = [
		{ x: 0, y: -1.6, z: 0 },   // 0: top apex
		{ x: 0, y: 1.6,  z: 0 }    // 1: bottom apex
	  ];
	  var RING_N = 6;
	  for (var vk = 0; vk < RING_N; vk++){
		var vAngle = (vk / RING_N) * Math.PI * 2;
		crystalVerts3D.push({
		  x: Math.cos(vAngle),
		  y: 0,
		  z: Math.sin(vAngle)
		});
	  }
	  // edges: top->ring (6), bottom->ring (6), ring->ring (6) = 18 total, used for the wireframe lines
	  var crystalEdges = [];
	  for (var ek = 0; ek < RING_N; ek++){
		crystalEdges.push([0, 2 + ek]);
		crystalEdges.push([1, 2 + ek]);
		crystalEdges.push([2 + ek, 2 + ((ek + 1) % RING_N)]);
	  }

	  // 12 triangular faces: 6 upper (top apex + ring edge), 6 lower (bottom apex + ring edge)
	  // used to render the solid crystal body, not for particle placement
	  var crystalFaces = [];
	  for (var fk = 0; fk < RING_N; fk++){
		crystalFaces.push([0, 2 + fk, 2 + ((fk + 1) % RING_N)]);
		crystalFaces.push([1, 2 + fk, 2 + ((fk + 1) % RING_N)]);
	  }

	  function vecSub(a, b){ return { x: a.x - b.x, y: a.y - b.y, z: a.z - b.z }; }
	  function vecCross(a, b){
		return {
		  x: a.y * b.z - a.z * b.y,
		  y: a.z * b.x - a.x * b.z,
		  z: a.x * b.y - a.y * b.x
		};
	  }
	  function vecNormalize(a){
		var len = Math.sqrt(a.x * a.x + a.y * a.y + a.z * a.z) || 1;
		return { x: a.x / len, y: a.y / len, z: a.z / len };
	  }
	  function vecDot(a, b){ return a.x * b.x + a.y * b.y + a.z * b.z; }

	  // static per-face normals (model space) — rotated per frame, same as vertices
	  var crystalFaceNormals = [];
	  for (var nf = 0; nf < crystalFaces.length; nf++){
		var fA = crystalVerts3D[crystalFaces[nf][0]];
		var fB = crystalVerts3D[crystalFaces[nf][1]];
		var fC = crystalVerts3D[crystalFaces[nf][2]];
		var faceN = vecNormalize(vecCross(vecSub(fB, fA), vecSub(fC, fA)));
		crystalFaceNormals.push(faceN);
	  }

	  var LIGHT_DIR = vecNormalize({ x: -0.45, y: -0.85, z: -0.55 }); // fixed key light, upper-left-front
	  var VIEW_DIR = { x: 0, y: 0, z: -1 };
	  var HALF_VEC = vecNormalize({ x: LIGHT_DIR.x + VIEW_DIR.x, y: LIGHT_DIR.y + VIEW_DIR.y, z: LIGHT_DIR.z + VIEW_DIR.z });

	  var CAM_TILT = 0.25;          // fixed pitch so we're not viewing it edge-on
	  var CAM_DIST = 4;            // perspective camera distance in local units
	  var CRYSTAL_PX = 150;        // apparent size in pixels

	  function rotateToCamera(local, yaw){
		// yaw rotation around Y
		var x1 = local.x * Math.cos(yaw) + local.z * Math.sin(yaw);
		var z1 = -local.x * Math.sin(yaw) + local.z * Math.cos(yaw);
		var y1 = local.y;

		// fixed pitch rotation around X for viewing angle
		var y2 = y1 * Math.cos(CAM_TILT) - z1 * Math.sin(CAM_TILT);
		var z2 = y1 * Math.sin(CAM_TILT) + z1 * Math.cos(CAM_TILT);
		var x2 = x1;

		return { x: x2, y: y2, z: z2 };
	  }

	  function project3D(local, yaw){
		var cam = rotateToCamera(local, yaw);
		var factor = CAM_DIST / (CAM_DIST + cam.z);
		return {
		  x: cam.x * factor * CRYSTAL_PX,
		  y: cam.y * factor * CRYSTAL_PX,
		  depth: cam.z,      // roughly -1.6 (near) .. 1.6 (far)
		  scale: factor
		};
	  }

	  function smoothstep(edge0, edge1, x){
		var t = Math.max(0, Math.min(1, (x - edge0) / (edge1 - edge0)));
		return t * t * (3 - 2 * t);
	  }

	  function lerp(a, b, t){ return a + (b - a) * t; }
	  function easeOutCubic(t){ return 1 - Math.pow(1 - t, 3); }
	  function easeInOutCubic(t){
		return t < 0.5 ? 4 * t * t * t : 1 - Math.pow(-2 * t + 2, 3) / 2;
	  }

	  function mixColor(t){
		// deep violet -> bright violet, with cyan only as a late highlight
		var c1 = [109, 47, 214];
		var c2 = [157, 92, 255];
		var c3 = [73, 224, 210];
		var r, g, b;
		if (t < 0.8){
		  var tt = t / 0.8;
		  r = lerp(c1[0], c2[0], tt);
		  g = lerp(c1[1], c2[1], tt);
		  b = lerp(c1[2], c2[2], tt);
		} else {
		  var tt2 = (t - 0.8) / 0.2;
		  r = lerp(c2[0], c3[0], tt2 * 0.35);
		  g = lerp(c2[1], c3[1], tt2 * 0.35);
		  b = lerp(c2[2], c3[2], tt2 * 0.35);
		}
		return Math.round(r) + ',' + Math.round(g) + ',' + Math.round(b);
	  }

	  var targetProgress = 0;
	  var progress = 0;
	  var t0 = performance.now();

	  function onScroll(){
		var rect = stage.getBoundingClientRect();
		var total = stage.offsetHeight - H;
		var scrolled = -rect.top;
		var p = total > 0 ? scrolled / total : 0;
		p = Math.max(0, Math.min(1, p));
		targetProgress = p;
	  }
	  window.addEventListener('scroll', onScroll, { passive: true });
	  onScroll();

	  function drawParticle(p, progress, time){
		var eased = easeOutCubic(progress);
		var r = lerp(p.rStart, p.rTarget, eased);
		var wob = Math.sin(time * 0.0006 * p.wobbleSpeed + p.wobble) * (1 - eased) * 18;
		var angle = p.angle + time * 0.00004 * (p.spin > 0 ? 1 : -1) * 6 + p.spin;
		var cx = W / 2 + Math.cos(angle) * (r + wob);
		var cy = H * 0.42 + Math.sin(angle) * (r + wob) * 0.7;

		// Fade out as the solid crystal takes over in the final stretch of the scroll.
		var formT = smoothstep(0.7, 1, progress);
		var fade = 1 - formT;
		if (fade <= 0.01) return;

		var glow = (0.35 + eased * 0.65) * fade;
		var color = mixColor(p.hueMix * 0.4 + eased * 0.6);
		var size = p.size * (0.7 + eased * 0.8);

		ctx.save();
		ctx.translate(cx, cy);
		ctx.rotate(angle);
		ctx.shadowColor = 'rgba(' + color + ',' + glow + ')';
		ctx.shadowBlur = 10 + eased * 14;
		ctx.fillStyle = 'rgba(' + color + ',' + ((0.55 + eased * 0.4) * fade) + ')';
		ctx.beginPath();
		ctx.moveTo(0, -size * 1.6);
		ctx.lineTo(size, 0);
		ctx.lineTo(0, size * 1.6);
		ctx.lineTo(-size, 0);
		ctx.closePath();
		ctx.fill();
		ctx.restore();
	  }

	  function drawCrystalSolid(progress, time){
		var formT = smoothstep(0.7, 1, progress);
		if (formT <= 0.01) return;
		var yaw = time * 0.00035;

		var projected = [];
		for (var vi = 0; vi < crystalVerts3D.length; vi++){
		  projected.push(project3D(crystalVerts3D[vi], yaw));
		}

		var order = [];
		for (var fi = 0; fi < crystalFaces.length; fi++){
		  var f = crystalFaces[fi];
		  var davg = (projected[f[0]].depth + projected[f[1]].depth + projected[f[2]].depth) / 3;
		  order.push({ i: fi, d: davg });
		}
		order.sort(function(a, b){ return b.d - a.d; }); // farthest first, nearest drawn last (on top)

		for (var oi = 0; oi < order.length; oi++){
		  var faceIdx = order[oi].i;
		  var face = crystalFaces[faceIdx];
		  var p0 = projected[face[0]], p1 = projected[face[1]], p2 = projected[face[2]];
		  var depthNear = 1 - ((p0.depth + p1.depth + p2.depth) / 3 + 1.6) / 3.2; // 1 = nearest

		  ctx.beginPath();
		  ctx.moveTo(W / 2 + p0.x, H * 0.42 + p0.y);
		  ctx.lineTo(W / 2 + p1.x, H * 0.42 + p1.y);
		  ctx.lineTo(W / 2 + p2.x, H * 0.42 + p2.y);
		  ctx.closePath();

		  var n = rotateToCamera(crystalFaceNormals[faceIdx], yaw);
		  var diffuse = Math.max(vecDot(n, LIGHT_DIR), 0);
		  var specular = Math.pow(Math.max(vecDot(n, HALF_VEC), 0), 42);
		  var ambient = 0.32;
		  var mixT = Math.min(1, ambient + diffuse * 0.75);

		  var darkC = [58, 32, 128];
		  var litC = [186, 148, 255];
		  var baseR = lerp(darkC[0], litC[0], mixT);
		  var baseG = lerp(darkC[1], litC[1], mixT);
		  var baseB = lerp(darkC[2], litC[2], mixT);

		  var shine = Math.min(1, specular * 1.4);
		  var hiC = [232, 250, 255]; // icy-white shine highlight
		  var fillR = Math.round(lerp(baseR, hiC[0], shine));
		  var fillG = Math.round(lerp(baseG, hiC[1], shine));
		  var fillB = Math.round(lerp(baseB, hiC[2], shine));

		  ctx.fillStyle = 'rgba(' + fillR + ',' + fillG + ',' + fillB + ',' + (formT * 0.60) + ')';
		  ctx.fill();

		  ctx.strokeStyle = 'rgba(232,220,255,' + (formT * (0.16 + depthNear * 0.28 + shine * 0.4)) + ')';
		  ctx.lineWidth = 1;
		  ctx.stroke();
		}
	  }

	  function drawCrystalWireframe(progress, time){
		var formT = smoothstep(0.7, 1, progress);
		if (formT <= 0.01) return;
		var yaw = time * 0.00035;

		for (var i = 0; i < crystalEdges.length; i++){
		  var a = crystalVerts3D[crystalEdges[i][0]];
		  var b = crystalVerts3D[crystalEdges[i][1]];
		  var pa = project3D(a, yaw);
		  var pb = project3D(b, yaw);
		  var depthAvg = 1 - ((pa.depth + pb.depth) / 2 + 1.6) / 3.2;

		  ctx.beginPath();
		  ctx.moveTo(W / 2 + pa.x, H * 0.42 + pa.y);
		  ctx.lineTo(W / 2 + pb.x, H * 0.42 + pb.y);
		  ctx.strokeStyle = 'rgba(157,92,255,' + (formT * (0.12 + depthAvg * 0.22)) + ')';
		  ctx.lineWidth = 1;
		  ctx.stroke();
		}
	  }

	  function drawCore(progress, time){
		var eased = easeInOutCubic(progress);
		if (eased <= 0) return;
		var cx = W / 2, cy = H * 0.42;
		var baseR = lerp(0, 130, eased);
		var pulse = 1 + Math.sin(time * 0.002) * 0.04 * eased;
		var r = baseR * pulse;

		var grad = ctx.createRadialGradient(cx, cy, 0, cx, cy, r);
		grad.addColorStop(0, 'rgba(220,200,255,' + (0.4 * eased) + ')');
		grad.addColorStop(0.35, 'rgba(157,92,255,' + (0.32 * eased) + ')');
		grad.addColorStop(0.75, 'rgba(109,47,214,' + (0.14 * eased) + ')');
		grad.addColorStop(1, 'rgba(109,47,214,0)');
		ctx.fillStyle = grad;
		ctx.beginPath();
		ctx.arc(cx, cy, r, 0, Math.PI * 2);
		ctx.fill();
	  }

	  function frame(time){
		progress = lerp(progress, targetProgress, 0.08);
		ctx.clearRect(0, 0, W, H);

		drawCore(progress, time);
		drawCrystalWireframe(progress, time);
		drawCrystalSolid(progress, time);
		for (var i = 0; i < particles.length; i++){
		  drawParticle(particles[i], progress, time);
		}

		var termProgress = Math.max(0, Math.min(1, (progress - 0.45) / 0.5));
		var termEase = easeOutCubic(termProgress);
		terminal.style.opacity = termEase;
		terminal.style.transform = 'translateY(' + (18 * (1 - termEase)) + 'px) scale(' + (0.94 + 0.06 * termEase) + ')';
		terminal.style.pointerEvents = termEase > 0.05 ? 'auto' : 'none';

		hint.style.opacity = Math.max(0, 1 - progress * 3);

		requestAnimationFrame(frame);
	  }

	  if (reduceMotion){
		terminal.style.opacity = 1;
		terminal.style.transform = 'none';
		terminal.style.pointerEvents = 'auto';
		hint.style.display = 'none';
	  } else {
		requestAnimationFrame(frame);
	  }

	  // Hook points for existing WP/NFC login flow.
	  // Replace with calls into the Aldric Voss dialogue / admin-ajax handlers.
	  document.getElementById('nf-login-btn').addEventListener('click', function(){
		window.location.href = '/login.php';
	  });
	  document.getElementById('nf-register-btn').addEventListener('click', function(){
		window.location.href = '/nfo.php';
	  });
	})();
	</script>

	</body>
</html>
HTML;
        exit;
   }
});