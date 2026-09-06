<?php
/**
 * Plugin Name:       Plugin-Zustandsprüfung
 * Plugin URI:        https://chesi.net/
 * Description:       Prüft alle installierten Plugins gegen das WordPress.org-Verzeichnis und meldet geschlossene, verwaiste oder lange nicht mehr gepflegte Plugins.
 * Version:           1.1.3
 * Requires at least: 6.0
 * Requires PHP:      7.4
 * Author:            Michele Chesi
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       wp-plugin-health-check
 * Domain Path:       /languages
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Automatic updates via the public GitHub repo.
if ( file_exists( __DIR__ . '/vendor/plugin-update-checker/plugin-update-checker.php' ) ) {
	require_once __DIR__ . '/vendor/plugin-update-checker/plugin-update-checker.php';

	YahnisElsts\PluginUpdateChecker\v5\PucFactory::buildUpdateChecker(
		'https://github.com/Michele64/wp-plugin-health-check/',
		__FILE__,
		'wp-plugin-health-check'
	);
}

final class PZP_Plugin_Health {

	const TRANSIENT = 'pzp_report';
	const OPTION    = 'pzp_settings';
	const CRON_HOOK = 'pzp_weekly_scan';
	const CAP       = 'activate_plugins';
	const SLUG      = 'plugin-zustandspruefung';
	const TD        = 'wp-plugin-health-check';

	/** Severity levels, ascending. */
	const LEVELS = array( 'ok' => 0, 'info' => 1, 'warnung' => 2, 'kritisch' => 3 );

	/** @var PZP_Plugin_Health|null */
	private static $instance = null;

	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Liest die Versionsnummer direkt aus dem Plugin-Header, statt sie ein
	 * zweites Mal fest im Code zu hinterlegen — so gibt es nur noch eine
	 * Stelle (den Header) plus die readme.txt, die bei einem Release
	 * synchron gehalten werden müssen.
	 */
	public static function version() {
		static $version = null;
		if ( null === $version ) {
			$data    = get_file_data( __FILE__, array( 'Version' => 'Version' ) );
			$version = $data['Version'];
		}
		return $version;
	}

	private function __construct() {
		add_action( 'init', array( $this, 'load_textdomain' ) );

		add_action( 'admin_menu', array( $this, 'register_page' ) );
		add_action( 'admin_init', array( $this, 'handle_actions' ) );

		add_filter( 'manage_plugins_columns', array( $this, 'add_column' ) );
		add_action( 'manage_plugins_custom_column', array( $this, 'render_column' ), 10, 3 );
		add_action( 'after_plugin_row', array( $this, 'render_warning_row' ), 10, 2 );
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_styles' ) );

		add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), array( $this, 'action_links' ) );

		add_filter( 'cron_schedules', array( $this, 'add_cron_interval' ) );
		add_action( self::CRON_HOOK, array( $this, 'run_cron_scan' ) );
	}

	/**
	 * Loads the translation files from /languages, since this plugin
	 * isn't hosted on WordPress.org (where this happens automatically).
	 */
	public function load_textdomain() {
		load_plugin_textdomain( self::TD, false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
	}

	/**
	 * Custom intervals instead of the core schedules, so availability
	 * doesn't depend on the WordPress version.
	 */
	public function add_cron_interval( $schedules ) {
		$schedules['pzp_daily'] = array(
			'interval' => DAY_IN_SECONDS,
			'display'  => __( 'Täglich (Plugin-Zustandsprüfung)', self::TD ),
		);
		$schedules['pzp_weekly'] = array(
			'interval' => WEEK_IN_SECONDS,
			'display'  => __( 'Wöchentlich (Plugin-Zustandsprüfung)', self::TD ),
		);
		$schedules['pzp_monthly'] = array(
			'interval' => MONTH_IN_SECONDS,
			'display'  => __( 'Monatlich (Plugin-Zustandsprüfung)', self::TD ),
		);
		return $schedules;
	}

	/** Selectable frequencies: key = cron interval suffix. */
	public function frequencies() {
		return array(
			'daily'   => __( 'Täglich', self::TD ),
			'weekly'  => __( 'Wöchentlich', self::TD ),
			'monthly' => __( 'Monatlich', self::TD ),
		);
	}

	/* ---------------------------------------------------------------------
	 * Settings
	 * ------------------------------------------------------------------ */

	public function settings() {
		$defaults = array(
			'stale_months'  => 24,
			'notice_months' => 12,
			'email_notify'    => 0,
			'email_frequency' => 'weekly',
			'email_to'        => get_option( 'admin_email' ),
		);
		return wp_parse_args( (array) get_option( self::OPTION, array() ), $defaults );
	}

	/* ---------------------------------------------------------------------
	 * Scan
	 * ------------------------------------------------------------------ */

	/**
	 * Runs the scan and stores the result in a transient.
	 * Calls the WordPress.org API once per plugin — with many plugins
	 * this takes a while, so it never runs automatically on page load.
	 *
	 * @return array
	 */
	public function scan() {
		if ( ! function_exists( 'get_plugins' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}
		if ( ! function_exists( 'plugins_api' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin-install.php';
		}

		@set_time_limit( 300 );

		$installed = get_plugins();
		$active    = (array) get_option( 'active_plugins', array() );
		$known     = $this->slugs_known_to_wporg();
		$settings  = $this->settings();
		$report    = array();

		foreach ( $installed as $file => $data ) {
			$slug = $this->slug_from_file( $file );

			$item = array(
				'file'         => $file,
				'name'         => isset( $data['Name'] ) ? $data['Name'] : $file,
				'slug'         => $slug,
				'version'      => isset( $data['Version'] ) ? $data['Version'] : '',
				'active'       => in_array( $file, $active, true ),
				'last_updated' => '',
				'tested'       => '',
				'in_repo'      => false,
				'level'        => 'ok',
				'messages'     => array(),
			);

			$info = plugins_api(
				'plugin_information',
				array(
					'slug'   => $slug,
					'fields' => array(
						'last_updated'      => true,
						'tested'            => true,
						'active_installs'   => true,
						'sections'          => false,
						'short_description' => false,
						'banners'           => false,
						'icons'             => false,
						'screenshots'       => false,
						'ratings'           => false,
						'reviews'           => false,
						'contributors'      => false,
						'versions'          => false,
						'tags'              => false,
						'homepage'          => false,
						'donate_link'       => false,
					),
				)
			);

			if ( is_wp_error( $info ) ) {
				if ( in_array( $file, $known, true ) ) {
					// WordPress still recognizes the plugin as a directory plugin, but the API no longer does.
					$this->flag( $item, 'kritisch', __( 'Nicht mehr im Verzeichnis abrufbar. Das Plugin wurde vermutlich geschlossen — als Grund kommen ein Rückzug durch den Autor, ein Richtlinienverstoß oder eine Sicherheitslücke infrage.', self::TD ) );
				} else {
					$this->flag( $item, 'info', __( 'Stammt nicht aus dem WordPress.org-Verzeichnis. Bei Premium-Plugins ist das normal; die Aktualität muss dann beim Anbieter geprüft werden.', self::TD ) );
				}
				$report[ $file ] = $item;
				continue;
			}

			$item['in_repo']      = true;
			$item['last_updated'] = isset( $info->last_updated ) ? $info->last_updated : '';
			$item['tested']       = isset( $info->tested ) ? $info->tested : '';

			$months = $this->months_since( $item['last_updated'] );

			if ( null !== $months ) {
				if ( $months >= (int) $settings['stale_months'] ) {
					$this->flag(
						$item,
						'kritisch',
						sprintf( __( 'Seit %d Monaten kein Update. Für ein Plugin auf einer produktiven Seite ist das ein Ersatzkandidat.', self::TD ), $months )
					);
				} elseif ( $months >= (int) $settings['notice_months'] ) {
					$this->flag(
						$item,
						'warnung',
						sprintf( __( 'Seit %d Monaten kein Update.', self::TD ), $months )
					);
				}
			}

			if ( $item['tested'] && version_compare( $item['tested'], $this->wp_major(), '<' ) ) {
				$this->flag(
					$item,
					'warnung',
					sprintf(
						__( 'Nur bis WordPress %1$s getestet, installiert ist %2$s.', self::TD ),
						$item['tested'],
						$this->wp_major()
					)
				);
			}

			$report[ $file ] = $item;
		}

		uasort( $report, array( $this, 'sort_by_severity' ) );

		$result = array(
			'generated' => time(),
			'wp'        => $this->wp_major(),
			'items'     => $report,
		);

		set_transient( self::TRANSIENT, $result, WEEK_IN_SECONDS );

		return $result;
	}

	public function get_report() {
		$report = get_transient( self::TRANSIENT );
		return is_array( $report ) ? $report : null;
	}

	/**
	 * Plugin files that WordPress itself associates with the directory.
	 * Basis for distinguishing between "closed" and "premium".
	 */
	private function slugs_known_to_wporg() {
		$updates = get_site_transient( 'update_plugins' );
		$known   = array();

		if ( is_object( $updates ) ) {
			if ( ! empty( $updates->response ) ) {
				$known = array_merge( $known, array_keys( (array) $updates->response ) );
			}
			if ( ! empty( $updates->no_update ) ) {
				$known = array_merge( $known, array_keys( (array) $updates->no_update ) );
			}
		}

		return $known;
	}

	private function slug_from_file( $file ) {
		if ( false === strpos( $file, '/' ) ) {
			return basename( $file, '.php' );
		}
		return dirname( $file );
	}

	private function months_since( $date ) {
		if ( ! $date ) {
			return null;
		}
		$ts = strtotime( $date );
		if ( ! $ts ) {
			return null;
		}
		return (int) floor( ( time() - $ts ) / MONTH_IN_SECONDS );
	}

	private function wp_major() {
		$parts = explode( '.', get_bloginfo( 'version' ) );
		return isset( $parts[1] ) ? $parts[0] . '.' . $parts[1] : $parts[0];
	}

	private function flag( &$item, $level, $message ) {
		$item['messages'][] = $message;
		if ( self::LEVELS[ $level ] > self::LEVELS[ $item['level'] ] ) {
			$item['level'] = $level;
		}
	}

	private function sort_by_severity( $a, $b ) {
		$diff = self::LEVELS[ $b['level'] ] - self::LEVELS[ $a['level'] ];
		if ( 0 !== $diff ) {
			return $diff;
		}
		return strcasecmp( $a['name'], $b['name'] );
	}

	/* ---------------------------------------------------------------------
	 * Cron
	 * ------------------------------------------------------------------ */

	public function run_cron_scan() {
		$result   = $this->scan();
		$settings = $this->settings();

		if ( empty( $settings['email_notify'] ) ) {
			return;
		}

		$critical = array_filter(
			$result['items'],
			static function ( $item ) {
				return 'kritisch' === $item['level'];
			}
		);

		if ( ! $critical ) {
			return;
		}

		$lines = array();
		foreach ( $critical as $item ) {
			$lines[] = sprintf( '- %s (%s)', $item['name'], implode( ' ', $item['messages'] ) );
		}

		wp_mail(
			$settings['email_to'],
			sprintf( __( '[%1$s] %2$d Plugins brauchen Aufmerksamkeit', self::TD ), wp_specialchars_decode( get_bloginfo( 'name' ) ), count( $critical ) ),
			implode( "\n", $lines ) . "\n\n" . admin_url( 'tools.php?page=' . self::SLUG )
		);
	}

	/* ---------------------------------------------------------------------
	 * Plugins list: column and warning row
	 * ------------------------------------------------------------------ */

	public function add_column( $columns ) {
		$columns['pzp_health'] = esc_html__( 'Zustand', self::TD );
		return $columns;
	}

	/**
	 * Link to the report page from the plugins list.
	 * The anchor jumps straight to the settings, which sit on the same
	 * page below the report.
	 */
	public function action_links( $links ) {
		$link = sprintf(
			'<a href="%s">%s</a>',
			esc_url( admin_url( 'tools.php?page=' . self::SLUG . '#pzp-einstellungen' ) ),
			esc_html__( 'Einstellungen', self::TD )
		);
		array_unshift( $links, $link );
		return $links;
	}

	public function render_column( $column, $file, $data ) {
		if ( 'pzp_health' !== $column ) {
			return;
		}

		$report = $this->get_report();
		if ( ! $report || ! isset( $report['items'][ $file ] ) ) {
			printf(
				'<span aria-hidden="true">&ndash;</span><span class="screen-reader-text">%s</span>',
				esc_html__( 'Noch nicht geprüft', self::TD )
			);
			return;
		}

		$item  = $report['items'][ $file ];
		$color = array(
			'ok'       => '#008a20',
			'info'     => '#646970',
			'warnung'  => '#996800',
			'kritisch' => '#d63638',
		);
		$label = array(
			'ok'       => __( 'Gepflegt', self::TD ),
			'info'     => __( 'Externe Quelle', self::TD ),
			'warnung'  => __( 'Prüfen', self::TD ),
			'kritisch' => __( 'Handlungsbedarf', self::TD ),
		);

		printf(
			'<strong style="color:%1$s">%2$s</strong>',
			esc_attr( $color[ $item['level'] ] ),
			esc_html( $label[ $item['level'] ] )
		);

		if ( $item['last_updated'] ) {
			printf(
				'<br><span class="description">%s</span>',
				esc_html( sprintf( __( 'Update: %s', self::TD ), date_i18n( 'M Y', strtotime( $item['last_updated'] ) ) ) )
			);
		}
	}

	public function render_warning_row( $file, $data ) {
		$report = $this->get_report();
		if ( ! $report || ! isset( $report['items'][ $file ] ) ) {
			return;
		}

		$item = $report['items'][ $file ];
		if ( in_array( $item['level'], array( 'ok', 'info' ), true ) ) {
			return;
		}

		$columns = 4;
		if ( isset( $GLOBALS['wp_list_table'] ) && method_exists( $GLOBALS['wp_list_table'], 'get_column_count' ) ) {
			$columns = $GLOBALS['wp_list_table']->get_column_count();
		}

		$border = 'kritisch' === $item['level'] ? '#d63638' : '#dba617';
		?>
		<tr class="plugin-update-tr pzp-warning-tr<?php echo $item['active'] ? ' active' : ''; ?>">
			<td colspan="<?php echo (int) $columns; ?>" class="plugin-update colspanchange">
				<div class="update-message notice inline notice-alt" style="border-left-color:<?php echo esc_attr( $border ); ?>">
					<p><?php echo esc_html( implode( ' ', $item['messages'] ) ); ?></p>
				</div>
			</td>
		</tr>
		<?php
	}

	/**
	 * Removes the divider between a plugin and its warning row, so both
	 * read as one unit and the warning isn't mistaken for belonging to
	 * the plugin listed below it.
	 */
	public function admin_styles( $hook ) {
		if ( 'plugins.php' !== $hook ) {
			return;
		}

		$css = '
		.plugins tr:has(+ tr.pzp-warning-tr) > td,
		.plugins tr:has(+ tr.pzp-warning-tr) > th {
			box-shadow: none;
			border-bottom: 0;
		}
		.plugins tr.pzp-warning-tr > td.plugin-update {
			border-top: 0;
		}
		.plugins tr.pzp-warning-tr .update-message {
			margin: 0 20px 12px 40px;
		}';

		wp_add_inline_style( 'list-tables', $css );
	}

	/* ---------------------------------------------------------------------
	 * Report page
	 * ------------------------------------------------------------------ */

	public function register_page() {
		add_management_page(
			__( 'Plugin-Zustandsprüfung', self::TD ),
			__( 'Plugin-Zustand', self::TD ),
			self::CAP,
			self::SLUG,
			array( $this, 'render_page' )
		);
	}

	public function handle_actions() {
		if ( ! isset( $_POST['pzp_action'] ) || ! current_user_can( self::CAP ) ) {
			return;
		}

		check_admin_referer( 'pzp_action' );

		if ( 'scan' === $_POST['pzp_action'] ) {
			$this->scan();
			$redirect = add_query_arg( 'pzp_scanned', '1', admin_url( 'tools.php?page=' . self::SLUG ) );
		} else {
			$frequency = sanitize_key( wp_unslash( $_POST['email_frequency'] ?? 'weekly' ) );
			if ( ! array_key_exists( $frequency, $this->frequencies() ) ) {
				$frequency = 'weekly';
			}

			$settings = array(
				'stale_months'    => max( 1, absint( $_POST['stale_months'] ?? 24 ) ),
				'notice_months'   => max( 1, absint( $_POST['notice_months'] ?? 12 ) ),
				'email_notify'    => empty( $_POST['email_notify'] ) ? 0 : 1,
				'email_frequency' => $frequency,
				'email_to'        => sanitize_email( wp_unslash( $_POST['email_to'] ?? get_option( 'admin_email' ) ) ),
			);
			update_option( self::OPTION, $settings );
			$this->sync_cron( $settings );
			$redirect = add_query_arg( 'pzp_saved', '1', admin_url( 'tools.php?page=' . self::SLUG ) );
		}

		wp_safe_redirect( $redirect );
		exit;
	}

	private function sync_cron( $settings ) {
		$scheduled = wp_next_scheduled( self::CRON_HOOK );

		if ( empty( $settings['email_notify'] ) ) {
			if ( $scheduled ) {
				wp_clear_scheduled_hook( self::CRON_HOOK );
			}
			return;
		}

		$wanted = 'pzp_' . $settings['email_frequency'];

		// Already scheduled with the desired interval: nothing to do.
		if ( $scheduled && wp_get_schedule( self::CRON_HOOK ) === $wanted ) {
			return;
		}

		if ( $scheduled ) {
			wp_clear_scheduled_hook( self::CRON_HOOK );
		}

		wp_schedule_event( time() + HOUR_IN_SECONDS, $wanted, self::CRON_HOOK );
	}

	public function render_page() {
		if ( ! current_user_can( self::CAP ) ) {
			return;
		}

		$report   = $this->get_report();
		$settings = $this->settings();
		?>
		<div class="wrap">
			<h1><?php esc_html_e( 'Plugin-Zustandsprüfung', self::TD ); ?></h1>

			<?php if ( isset( $_GET['pzp_scanned'] ) ) : ?>
				<div class="notice notice-success is-dismissible"><p><?php esc_html_e( 'Prüfung abgeschlossen.', self::TD ); ?></p></div>
			<?php elseif ( isset( $_GET['pzp_saved'] ) ) : ?>
				<div class="notice notice-success is-dismissible"><p><?php esc_html_e( 'Einstellungen gespeichert.', self::TD ); ?></p></div>
			<?php endif; ?>

			<form method="post" style="margin:1em 0">
				<?php wp_nonce_field( 'pzp_action' ); ?>
				<input type="hidden" name="pzp_action" value="scan">
				<?php submit_button( __( 'Jetzt prüfen', self::TD ), 'primary', 'submit', false ); ?>
				<?php if ( $report ) : ?>
					<span class="description" style="margin-left:1em">
						<?php esc_html_e( 'Letzte Prüfung:', self::TD ); ?>
						<?php echo esc_html( date_i18n( 'j. F Y, H:i', $report['generated'] ) ); ?>
					</span>
				<?php endif; ?>
			</form>

			<?php if ( ! $report ) : ?>
				<p><?php esc_html_e( 'Noch keine Daten. Der erste Durchlauf ruft für jedes installierte Plugin einmal die WordPress.org-API auf und kann je nach Anzahl eine halbe bis zwei Minuten dauern.', self::TD ); ?></p>
			<?php else : ?>
				<?php $this->render_table( $report ); ?>
			<?php endif; ?>

			<h2 id="pzp-einstellungen"><?php esc_html_e( 'Einstellungen', self::TD ); ?></h2>
			<form method="post">
				<?php wp_nonce_field( 'pzp_action' ); ?>
				<input type="hidden" name="pzp_action" value="settings">
				<table class="form-table" role="presentation">
					<tr>
						<th scope="row"><label for="notice_months"><?php esc_html_e( 'Warnung ab', self::TD ); ?></label></th>
						<td>
							<input name="notice_months" id="notice_months" type="number" min="1" max="120"
								value="<?php echo esc_attr( $settings['notice_months'] ); ?>" class="small-text"> <?php esc_html_e( 'Monate ohne Update', self::TD ); ?>
						</td>
					</tr>
					<tr>
						<th scope="row"><label for="stale_months"><?php esc_html_e( 'Handlungsbedarf ab', self::TD ); ?></label></th>
						<td>
							<input name="stale_months" id="stale_months" type="number" min="1" max="120"
								value="<?php echo esc_attr( $settings['stale_months'] ); ?>" class="small-text"> <?php esc_html_e( 'Monate ohne Update', self::TD ); ?>
						</td>
					</tr>
					<tr>
						<th scope="row"><?php esc_html_e( 'E-Mail-Benachrichtigung', self::TD ); ?></th>
						<td>
							<label>
								<input type="checkbox" name="email_notify" value="1" <?php checked( $settings['email_notify'], 1 ); ?>>
								<?php esc_html_e( 'Automatisch prüfen und bei Handlungsbedarf benachrichtigen', self::TD ); ?>
							</label>
							<p>
								<label for="email_frequency" class="screen-reader-text"><?php esc_html_e( 'Häufigkeit', self::TD ); ?></label>
								<select name="email_frequency" id="email_frequency">
									<?php foreach ( $this->frequencies() as $key => $label ) : ?>
										<option value="<?php echo esc_attr( $key ); ?>" <?php selected( $settings['email_frequency'], $key ); ?>>
											<?php echo esc_html( $label ); ?>
										</option>
									<?php endforeach; ?>
								</select>
								<input type="email" name="email_to" class="regular-text"
									value="<?php echo esc_attr( $settings['email_to'] ); ?>">
							</p>
							<p class="description">
								<?php esc_html_e( 'Die Mail geht nur raus, wenn tatsächlich etwas als „Handlungsbedarf" eingestuft wurde.', self::TD ); ?>
								<?php if ( $settings['email_notify'] && wp_next_scheduled( self::CRON_HOOK ) ) : ?>
									<?php echo esc_html( sprintf( __( 'Nächster Lauf: %s.', self::TD ), date_i18n( 'j. F Y, H:i', wp_next_scheduled( self::CRON_HOOK ) ) ) ); ?>
								<?php endif; ?>
							</p>
						</td>
					</tr>
				</table>
				<?php submit_button( __( 'Einstellungen speichern', self::TD ) ); ?>
			</form>

			<h3><?php esc_html_e( 'Zur Einordnung der Befunde', self::TD ); ?></h3>
			<p class="description" style="max-width:46em">
				<?php
				echo wp_kses_post(
					sprintf(
						/* translators: %1$s and %2$s are the <code> tags wrapping "Tested up to" and "readme.txt". */
						__( 'Der Wert „Getestet bis" stammt aus dem Header %1$s der %2$s. Er wird vom Plugin-Autor selbst gepflegt und von niemandem überprüft — die Zahl lässt sich hochsetzen, ohne eine Zeile Code zu ändern. Ein Befund dazu ist also ein Hinweis, kein Beweis. Im Zweifel sagt das Datum des letzten Updates mehr über den Pflegezustand aus.', self::TD ),
						'<code>Tested up to</code>',
						'<code>readme.txt</code>'
					)
				);
				?>
			</p>
		</div>
		<?php
	}

	private function render_table( $report ) {
		$colors = array(
			'ok'       => '#008a20',
			'info'     => '#646970',
			'warnung'  => '#996800',
			'kritisch' => '#d63638',
		);
		$labels = array(
			'ok'       => __( 'Gepflegt', self::TD ),
			'info'     => __( 'Externe Quelle', self::TD ),
			'warnung'  => __( 'Prüfen', self::TD ),
			'kritisch' => __( 'Handlungsbedarf', self::TD ),
		);
		?>
		<table class="wp-list-table widefat fixed striped">
			<thead>
				<tr>
					<th style="width:22%"><?php esc_html_e( 'Plugin', self::TD ); ?></th>
					<th style="width:10%"><?php esc_html_e( 'Version', self::TD ); ?></th>
					<th style="width:12%"><?php esc_html_e( 'Letztes Update', self::TD ); ?></th>
					<th style="width:10%"><?php esc_html_e( 'Getestet bis', self::TD ); ?></th>
					<th style="width:12%"><?php esc_html_e( 'Zustand', self::TD ); ?></th>
					<th><?php esc_html_e( 'Befund', self::TD ); ?></th>
				</tr>
			</thead>
			<tbody>
			<?php foreach ( $report['items'] as $item ) : ?>
				<tr>
					<td>
						<strong><?php echo esc_html( $item['name'] ); ?></strong>
						<?php if ( ! $item['active'] ) : ?>
							<span class="description"> (<?php esc_html_e( 'inaktiv', self::TD ); ?>)</span>
						<?php endif; ?>
					</td>
					<td><?php echo esc_html( $item['version'] ); ?></td>
					<td>
						<?php
						echo $item['last_updated']
							? esc_html( date_i18n( 'j. M Y', strtotime( $item['last_updated'] ) ) )
							: '&ndash;';
						?>
					</td>
					<td><?php echo $item['tested'] ? esc_html( $item['tested'] ) : '&ndash;'; ?></td>
					<td>
						<strong style="color:<?php echo esc_attr( $colors[ $item['level'] ] ); ?>">
							<?php echo esc_html( $labels[ $item['level'] ] ); ?>
						</strong>
					</td>
					<td><?php echo esc_html( implode( ' ', $item['messages'] ) ); ?></td>
				</tr>
			<?php endforeach; ?>
			</tbody>
		</table>
		<p class="description">
			<?php echo esc_html( sprintf( __( 'Geprüft gegen WordPress %s.', self::TD ), $report['wp'] ) ); ?>
			<?php esc_html_e( '„Externe Quelle" heißt, dass das Plugin im Verzeichnis nicht gefunden wurde und WordPress es auch nie von dort bezogen hat — bei Premium-Plugins der Normalfall.', self::TD ); ?>
		</p>
		<?php
	}

	/* ---------------------------------------------------------------------
	 * Activation / Deactivation
	 * ------------------------------------------------------------------ */

	public static function deactivate() {
		$scheduled = wp_next_scheduled( self::CRON_HOOK );
		if ( $scheduled ) {
			wp_unschedule_event( $scheduled, self::CRON_HOOK );
		}
		delete_transient( self::TRANSIENT );
	}
}

register_deactivation_hook( __FILE__, array( 'PZP_Plugin_Health', 'deactivate' ) );

add_action( 'plugins_loaded', array( 'PZP_Plugin_Health', 'instance' ) );
