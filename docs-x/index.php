<!DOCTYPE html>
<html lang="<?php echo $language->currentLanguageShortVersion() ?>">
<head>
	<?php include(THEME_DIR_PHP.'head.php'); ?>
</head>
<body>

	<!-- Load Bludit Plugins: Site Body Begin -->
	<?php Theme::plugins('siteBodyBegin'); ?>

	<!-- Navbar -->
	<div>
		<?php include(THEME_DIR_PHP.'navbar.php'); ?>
	</div>
	<div class="docs-container">
		<!-- Sidebar -->
		<div id="backdrop"></div>
		<div id="sidebar">
			<?php include(THEME_DIR_PHP.'sidebar.php'); ?>
		</div>

		<!-- Content -->
		<div class="main">
			<?php include(THEME_DIR_PHP.'page.php'); ?>
			<footer>
				<p class="m-0 text-right text-black text-uppercase">
					<?php echo $site->footer(); ?>
					<span class="ml-3 text-warning">
						Powered by
						<a target="_blank" class="text-warning" href="https://www.bludit.com">Bludit</a>
					</span>
				</p>
			</footer>
		</div>
	</div>


	<!-- Javascript -->
	<?php
	echo Theme::jquery();
	echo Theme::jsBootstrap();
	echo Theme::js('js/highlight.min.js');
    ?>

	<!-- Init Highlight -->
	<script>
		hljs.initHighlighting();
	</script>

	<!-- TOC generator -->
	<script>
		$(document).ready(function () {
			var enableToc = false;
			if ($('#page-content > h2').length > 1) {
				$('#page-content > h2').each(function () {
					if ($(this).attr('id')) {
						enableToc = true;
						$('#toc-content').append('<li><a href="#' + $(this).attr('id') + '">' + $(this).text() + '</a></li>');
					}
				});
			}
			if (enableToc) {
				$('#toc').show();
			}

			$('.b-burger-btn').on('click', function () {
				if ($(this).hasClass('open')) {
					$('body').removeClass('stop-scrolling');
				} else {
					$('body').addClass('stop-scrolling');
				}
				$(this).toggleClass('open');
				$('#sidebar').toggleClass('open');
				$('#backdrop').toggleClass('open');
			});
			$('#backdrop').on('click', function () {
				$(this).toggleClass('open');
				$('body').removeClass('stop-scrolling');
				$('#sidebar').toggleClass('open');
				$('.b-burger-btn').toggleClass('open');
			});

			//smooth scroll to id
			$('a[href^="#"]').on('click', function (e) {
				e.preventDefault();
				var target = this.hash;
				var $target = $(target);
				$('html, body').stop().animate({
					scrollTop: $target.offset().top - 60
				}, 900
				);
			});
		});
	</script>

	<!-- Copy to clipboard -->
	<script>
		function copyToClipboard(text) {
			var aux = document.createElement("input");
			aux.setAttribute("value", text);
			document.body.appendChild(aux);
			aux.select();
			document.execCommand("copy");
			document.body.removeChild(aux);
		}

		$(document).ready(function() {
			$("h2").click(function() {
				var id = $(this).attr("id");
				var permalink = "<?php echo $page->permalink() ?>";
				var link = permalink+"#"+id;
				copyToClipboard(link);
				console.log("Copied to clipboard: "+link);
			});
		});
	</script>

	<script type="text/javascript" src="https://cdn.jsdelivr.net/npm/docsearch.js@2/dist/cdn/docsearch.min.js"></script>
	<script type="text/javascript"> docsearch({
			apiKey: '606abe5e10850fc4c6095abd4d8f3181',
			indexName: 'bludit',
			inputSelector: '.ds-input',
			algoliaOptions: { 'facetFilters': ["lang:en"] },
			debug: false // Set debug to true if you want to inspect the dropdown
		});
	</script>

	<!-- Load Bludit Plugins: Site Body End -->
	<?php Theme::plugins('siteBodyEnd'); ?>

</body>
</html>
