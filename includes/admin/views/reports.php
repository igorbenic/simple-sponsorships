<?php
/**
 * Reports Screen.
 */

$reports_data = array();
/** @var \Simple_Sponsorships\Sponsorship $sponsorship */
foreach ( $sponsorships as $sponsorship ) {

	$sponsorship_object = ss_get_sponsorship( $sponsorship['ID'], false );
	$sponsorship_object->populate_from_data( $sponsorship );
	$date = date( 'Y-m-d', strtotime( $sponsorship_object->get_data( 'date' ) ) );
	if ( ! isset( $reports_data[ $date ] ) ) {
		$reports_data[ $date ] = 0;
	}
	$reports_data[ $date ] += (float) $sponsorship_object->get_data( 'amount' );
}
$date_labels = array_keys( $reports_data );
?>
<h1><?php echo get_admin_page_title(); ?></h1>

<div class="wp-list-table widefat plugin-install">
	<div class="ss-reports-dashboard">
		<h3>
			<?php esc_html_e( 'Total:', 'simple-sponsorships' ); ?>
			<strong>
				<?php echo ss_get_currency(); ?>
				<?php echo array_sum( wp_list_pluck( $sponsorships, 'amount' ) ); ?>
			</strong>
		</h3>
	</div>
	<div class="ss-reports-chart">
		<canvas id="myChart" width="400" height="400"></canvas>
		<script>
			window.addEventListener('load', function(){
				var ctx = document.getElementById('myChart');
				new Chart(ctx, {
					type: 'line',
					data: {
						labels: ['<?php echo implode("','", $date_labels ); ?>'],
						datasets: [{
							label: 'Total',
							data: [<?php echo implode(',', $reports_data ); ?>],
							backgroundColor: 'rgba(255, 99, 132, 0.2)',
							borderColor: 'rgba(255, 99, 132, 1)',
							borderWidth: 1
						}]
					},
					options: {
						scales: {
							yAxes: [{
								ticks: {
									beginAtZero: true
								}
							}]
						}
					}
				});
			});
		</script>
	</div>
</div>
