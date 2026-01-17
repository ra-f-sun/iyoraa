<!-- Main Admin Page Template (React Mount Point) -->
<div class="wrap">
	<h1>Iyoraa Hospital Management System</h1>
	
	<!-- Debug Info (remove after testing) -->
	<?php if ( WP_DEBUG ) : ?>
		<div class="notice notice-info">
			<p><strong>Debug Info:</strong></p>
			<ul>
				<li>Plugin URL: <?php echo esc_html( IYORAA_URL ); ?></li>
				<li>Assets path: <?php echo esc_html( IYORAA_URL . 'assets/dist/' ); ?></li>
				<li>index.js exists: <?php echo file_exists( IYORAA_PATH . 'assets/dist/index.js' ) ? '✓ YES' : '✗ NO'; ?></li>
				<li>index.css exists: <?php echo file_exists( IYORAA_PATH . 'assets/dist/index.css' ) ? '✓ YES' : '✗ NO'; ?></li>
			</ul>
		</div>
	<?php endif; ?>
	
	<div id="iyoraa-app"></div>
	
	<!-- Fallback message if React doesn't load -->
	<noscript>
		<p style="color: red;">JavaScript is required for this page to work.</p>
	</noscript>
</div>