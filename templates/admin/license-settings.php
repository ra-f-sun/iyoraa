<!-- License Settings Page Template -->
<div class="wrap">
	<h1><?php esc_html_e( 'Iyoraa License Settings', 'iyoraa' ); ?></h1>

	<div class="card">
		<h2><?php esc_html_e( 'Current License', 'iyoraa' ); ?></h2>
		<p><strong><?php esc_html_e( 'Tier:', 'iyoraa' ); ?></strong> <?php echo esc_html( $tier_name ); ?></p>
		<p><em><?php esc_html_e( 'For MVP, license is set to FREE. Use the override below for testing PRO features.', 'iyoraa' ); ?></em></p>
	</div>

	<?php if ( current_user_can( 'manage_options' ) ) : ?>
		<div class="card">
			<h2><?php esc_html_e( 'Admin Override (Testing Only)', 'iyoraa' ); ?></h2>
			<form method="post" action="">
				<?php wp_nonce_field( 'iyoraa_set_tier' ); ?>
				<table class="form-table">
					<tr>
						<th scope="row"><?php esc_html_e( 'Force Tier', 'iyoraa' ); ?></th>
						<td>
							<select name="iyoraa_tier">
								<option value="free" <?php selected( $current_tier, 'free' ); ?>>FREE</option>
								<option value="pro-starter" <?php selected( $current_tier, 'pro-starter' ); ?>>PRO STARTER</option>
								<option value="pro-business" <?php selected( $current_tier, 'pro-business' ); ?>>PRO BUSINESS</option>
								<option value="enterprise" <?php selected( $current_tier, 'enterprise' ); ?>>ENTERPRISE</option>
							</select>
						</td>
					</tr>
				</table>
				<?php submit_button( __( 'Set Tier Manually', 'iyoraa' ) ); ?>
			</form>
		</div>
	<?php endif; ?>
</div>
