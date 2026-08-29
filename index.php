<html>
<head>
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width; initial-scale=1.0; maximum-scale=1.0;">
	<meta charset="UTF-8">

	<link rel="preconnect" href="https://fonts.googleapis.com">
	<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
	<link
		href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,400;0,500;0,600;1,400;1,500&family=Jost:wght@300;400;500&family=Pinyon+Script&display=swap"
		rel="stylesheet">

	<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css">

	<link rel="stylesheet" type="text/css" href="css/reset.css"> <!-- CSS reset -->
	<link rel="stylesheet" type="text/css" href="css/tinycolorpicker.css">
	<link rel="stylesheet" type="text/css" href="css/side-panel.css"> <!-- Resource style -->
	<link rel="stylesheet" type="text/css" href="css/style.css">

	<title>Mis XV  ♥ Evelyn Laila | Pon tus fotografías aquí</title>

	<style>
		body {
			font-family: 'Droid', sans-serif;
		}

		a,
		div.price a:visited,
		div.droply-docs a:visited {
			color: white;
			text-decoration: none;
		}

		div.price {
			background: rgba(255, 255, 255, 0.2);
			padding: 10px;
			padding-top: 20px;
			position: absolute;
			right: 200px;
			top: -10px;
			float: left;
			color: white;
			border-radius: 5px;
			width: 100px;
		}

		img.plugin-logo {
			margin-top: 30px;
		}

		div.droply-filedrag {
			background-color: #6e2a3895;
		}

		div.droply-docs {
			background: rgba(255, 255, 255, 0.2);
			padding: 10px;
			position: absolute;
			left: 200px;
			top: 0;
			color: white;
			border-radius: 5px;
		}

		div.price:hover {
			background: rgba(255, 255, 255, 0.4);
			font-weight: bold;
		}

		div.droply-docs:hover {
			background: rgba(255, 255, 255, 0.4);
			font-weight: bold;
		}

		div.output {
			font-family: sans-serif;
			font-size: 12px;
			max-width: 300px;
			position: absolute;
			left: 51px;
			top: 364px;
		}

		div.output:before {
			content: 'Debug : ';
			font-size: 20px;
			color: red;
		}
	</style>
  <!-- Google tag (gtag.js) -->
  <script async src="https://www.googletagmanager.com/gtag/js?id=G-KMW67NJZ60"></script>
  <script>
    window.dataLayer = window.dataLayer || [];
    function gtag() { dataLayer.push(arguments); }
    gtag('js', new Date());

    gtag('config', 'G-KMW67NJZ60');
  </script>

</head>

<body>

	<!-- ============================ HEADER ============================ -->
	<main id="main">
		<!-- ============================ HERO ============================ -->
		<section class="hero" id="home" aria-label="Evelyn Laila Rodríguez Martínez">

			<div class="hero__bg" role="img" aria-label="Bridal bouquet of garden roses on a linen table"></div>
			<div class="hero__inner">
        		
				<h1 class="hero__names">Mis XV <span class="amp">&hearts;</span>Evelyn Laila</h1>
        		<h2 class="hero__venue">Rodríguez Martínez</h2>
				<p class="hero__meta">Sábado, 29 Agosto <span>&bull;</span> 2026</p>
				<p class="hero__venue">Revivamos esta noche juntos</p>
				<p class="footer-fine">Sube las fotografías que tomaste durante la celebración y ayúdanos a crear un álbum lleno de recuerdos inolvidables.</p>
				
				<center>

					<main class="cd-main-content">
						<!-- put your arfaly container anywhere -->
						<div id="mas"></div>
					</main>



				</center>

			</div>
			<a class="scroll-cue" href="#story" aria-label="Scroll to our story">
				<svg viewBox="0 0 24 36" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"
					stroke-linejoin="round" aria-hidden="true">
					<rect x="7" y="1" width="10" height="20" rx="5" />
					<path d="M12 6v4" />
					<path d="M8 27l4 4 4-4" />
				</svg>
			</a>
		</section>


	</main>
	<!-- ============================ FOOTER ============================ -->
	
	<footer class="site-footer">
		<div class="container">
			<p class="footer-monogram"> Mis XV <span class="amp">&hearts;</span>Evelyn Laila </p>
			<p class="footer-date">Sábado, 29 Agosto <span>&bull;</span> 2026</p>
			<p class="footer-hash">#misxvlaila</p>

			<ul class="footer-nav">
				<li><a href="#home" class="active">Home</a></li>
				<li><a href="#details">Misa & Recepción</a></li>
				<li><a href="#registry">Ideas de regalo</a></li>
			</ul>
			</ul>

			<div class="footer-rule"></div>
			<p class="footer-fine">
				&copy; 2026 Mis XV <span class="amp">&hearts;</span>Evelyn Laila<br>
			</p>
		</div>
	</footer>

	<input type="hidden" id="mesa" value="<?php echo $_GET['mesa']; ?>" />
	<script src="https://ajax.googleapis.com/ajax/libs/jquery/2.2.4/jquery.min.js"></script>
	<script language="javascript" type="text/javascript" src="js/modernizr.js"></script>
	<script language="javascript" type="text/javascript" src="js/jquery.tinycolorpicker.js"></script>


	<script language="javascript" type="text/javascript" src="js/droply.js"></script>
	<script language="javascript" type="text/javascript" src="js/custom.js"></script>
</body>

</html>