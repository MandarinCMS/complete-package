<?php
/**
 * @package MCMSSEO\Admin
 */

/** @noinspection PhpUnusedLocalVariableInspection */
$alerts_data = Ultimatum_Alerts::get_template_variables();

?>
<div class="wrap ultimatum-alerts">

	<h2><?php
		/* translators: %1$s expands to Ultimatum SEO */
		printf( __( '%1$s Dashboard', 'mandarincms-seo' ), 'Ultimatum SEO' );
		?></h2>
	<div class="ultimatum-container ultimatum-container__alert">
		<?php include MCMSSEO_PATH . 'admin/views/partial-alerts-errors.php'; ?>
	</div>

	<div class="ultimatum-container ultimatum-container__warning">
		<?php include MCMSSEO_PATH . 'admin/views/partial-alerts-warnings.php'; ?>
	</div>

</div>
