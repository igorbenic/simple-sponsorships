<?php
/**
 * Showing Sponsorships
 */

if ( ! $args['sponsor'] ) {
	?>
	<p><?php esc_html_e( 'You don\'t have any sponsor data', 'simple-sponsorships' ); ?></p>
	<?php
	return;
}

$sponsor = $args['sponsor'];
$reports = ss_get_reports_for_sponsor( $sponsor->get_id() );
$reports_data = array();
foreach ( $reports as $report ) {

	$date = date( 'Y-m-d', strtotime( $report['date'] ) );
	if ( ! isset( $reports_data[ $date ] ) ) {
		$reports_data[ $date ] = 0;
	}
	$reports_data[ $date ]++;
}
$date_labels = array_keys( $reports_data );
?>

<div class="ss-sponsor-reports">
	<?php
	echo '<p>' . esc_html( sprintf( __( 'Total Clicks: %d', 'simple-sponsorships'), count( $reports_data ) ) ) . '</p>';
	if ( $reports_data ) {
	?>
	<canvas id="myChart" width="400" height="400"></canvas>
	<script>
		window.addEventListener('load', function(){
			var ctx = document.getElementById('myChart');
			new Chart(ctx, {
				type: 'line',
				data: {
					labels: ['<?php echo implode("','", $date_labels ); ?>'],
					datasets: [{
						label: 'Clicks',
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
	<?php } ?>
</div>
