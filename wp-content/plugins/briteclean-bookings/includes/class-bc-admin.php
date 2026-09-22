<?php
/**
 * Booking admin experience.
 *
 * The list table is the screen the business actually lives in, so it is built to be
 * worked from directly: status is changeable inline without opening the booking, the
 * customer's phone number is one click to dial, and the filters match the workflow
 * (New → Contacted → Confirmed → Completed).
 *
 * @package BritecleanBookings
 */

defined( 'ABSPATH' ) || exit;

/**
 * Admin columns, filters, meta boxes and inline editing.
 */
class BC_Admin {

	/**
	 * Hook registration.
	 */
	public static function init() {
		$cpt = BRITECLEAN_BOOKING_CPT;

		add_filter( "manage_{$cpt}_posts_columns", array( __CLASS__, 'columns' ) );
		add_action( "manage_{$cpt}_posts_custom_column", array( __CLASS__, 'column_content' ), 10, 2 );
		add_filter( "manage_edit-{$cpt}_sortable_columns", array( __CLASS__, 'sortable_columns' ) );
		add_filter( "views_edit-{$cpt}", array( __CLASS__, 'status_views' ) );

		add_action( 'restrict_manage_posts', array( __CLASS__, 'filter_controls' ) );
		add_action( 'pre_get_posts', array( __CLASS__, 'apply_filters_to_query' ) );

		add_action( 'add_meta_boxes', array( __CLASS__, 'add_meta_boxes' ) );
		add_action( 'save_post_' . $cpt, array( __CLASS__, 'save_meta' ), 10, 2 );

		add_action( 'admin_enqueue_scripts', array( __CLASS__, 'admin_assets' ) );
		add_action( 'wp_ajax_briteclean_set_status', array( __CLASS__, 'ajax_set_status' ) );

		add_action( 'admin_notices', array( __CLASS__, 'mail_failure_notice' ) );
		add_filter( 'post_row_actions', array( __CLASS__, 'row_actions' ), 10, 2 );
		add_filter( 'bulk_actions-edit-' . $cpt, array( __CLASS__, 'bulk_actions' ) );
		add_filter( 'handle_bulk_actions-edit-' . $cpt, array( __CLASS__, 'handle_bulk_actions' ), 10, 3 );
	}

	/**
	 * Define the list table columns.
	 *
	 * @param array $columns Default columns.
	 * @return array
	 */
	public static function columns( $columns ) {
		return array(
			'cb'           => $columns['cb'] ?? '',
			'title'        => __( 'Customer', 'briteclean-bookings' ),
			'bc_status'    => __( 'Status', 'briteclean-bookings' ),
			'bc_services'  => __( 'Services', 'briteclean-bookings' ),
			'bc_preferred' => __( 'Preferred date', 'briteclean-bookings' ),
			'bc_contact'   => __( 'Contact', 'briteclean-bookings' ),
			'bc_submitted' => __( 'Submitted', 'briteclean-bookings' ),
		);
	}

	/**
	 * Render a custom column.
	 *
	 * @param string $column  Column key.
	 * @param int    $post_id Post ID.
	 */
	public static function column_content( $column, $post_id ) {
		switch ( $column ) {
			case 'bc_status':
				self::render_status_select( $post_id, 'bc-status-inline' );
				break;

			case 'bc_services':
				$services = BC_Schema::display_value( $post_id, 'services' );
				echo $services ? esc_html( $services ) : '<span style="color:#a1a1aa">—</span>';
				break;

			case 'bc_preferred':
				$date = BC_Schema::display_value( $post_id, 'preferred_date' );
				$time = BC_Schema::display_value( $post_id, 'time_preference' );

				if ( $date ) {
					printf( '<strong>%s</strong>', esc_html( $date ) );

					if ( $time ) {
						printf( '<br /><span style="color:#6b7280">%s</span>', esc_html( $time ) );
					}
				} else {
					echo '<span style="color:#a1a1aa">—</span>';
				}
				break;

			case 'bc_contact':
				$phone = get_post_meta( $post_id, BC_Schema::meta_key( 'phone' ), true );
				$email = get_post_meta( $post_id, BC_Schema::meta_key( 'email' ), true );

				if ( $phone ) {
					printf(
						'<a href="tel:%s">%s</a><br />',
						esc_attr( preg_replace( '/\D+/', '', $phone ) ),
						esc_html( $phone )
					);
				}

				if ( $email ) {
					printf( '<a href="mailto:%s">%s</a>', esc_attr( $email ), esc_html( $email ) );
				}
				break;

			case 'bc_submitted':
				printf(
					'%s<br /><span style="color:#6b7280">%s</span>',
					esc_html( get_the_date( 'j M Y', $post_id ) ),
					esc_html( get_the_date( 'g:i a', $post_id ) )
				);
				break;
		}
	}

	/**
	 * Make the date and status columns sortable.
	 *
	 * @param array $columns Sortable columns.
	 * @return array
	 */
	public static function sortable_columns( $columns ) {
		$columns['bc_submitted'] = 'date';
		$columns['bc_status']    = 'bc_status';
		$columns['bc_preferred'] = 'bc_preferred';

		return $columns;
	}

	/**
	 * Status filter links above the list table, with counts.
	 *
	 * @param array $views Existing views.
	 * @return array
	 */
	public static function status_views( $views ) {
		$counts  = BC_CPT::status_counts();
		$total   = array_sum( $counts );
		$current = isset( $_GET['bc_status'] ) ? sanitize_key( wp_unslash( $_GET['bc_status'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only filter.
		$base    = admin_url( 'edit.php?post_type=' . BRITECLEAN_BOOKING_CPT );

		$built = array(
			'all' => sprintf(
				'<a href="%s"%s>%s <span class="count">(%d)</span></a>',
				esc_url( $base ),
				'' === $current ? ' class="current"' : '',
				esc_html__( 'All', 'briteclean-bookings' ),
				(int) $total
			),
		);

		foreach ( BC_Schema::statuses() as $key => $label ) {
			$built[ 'bc_' . $key ] = sprintf(
				'<a href="%s"%s>%s <span class="count">(%d)</span></a>',
				esc_url( add_query_arg( 'bc_status', $key, $base ) ),
				$current === $key ? ' class="current"' : '',
				esc_html( $label ),
				(int) ( $counts[ $key ] ?? 0 )
			);
		}

		// Keep core's Trash link, drop its own All (ours has the right counts).
		if ( isset( $views['trash'] ) ) {
			$built['trash'] = $views['trash'];
		}

		return $built;
	}

	/**
	 * Dropdown filters above the list table.
	 *
	 * @param string $post_type Current post type.
	 */
	public static function filter_controls( $post_type ) {
		if ( BRITECLEAN_BOOKING_CPT !== $post_type ) {
			return;
		}

		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only filter.
		$current = isset( $_GET['bc_status'] ) ? sanitize_key( wp_unslash( $_GET['bc_status'] ) ) : '';
		?>
		<label class="screen-reader-text" for="bc_status"><?php esc_html_e( 'Filter by status', 'briteclean-bookings' ); ?></label>
		<select name="bc_status" id="bc_status">
			<option value=""><?php esc_html_e( 'All statuses', 'briteclean-bookings' ); ?></option>
			<?php foreach ( BC_Schema::statuses() as $key => $label ) : ?>
				<option value="<?php echo esc_attr( $key ); ?>" <?php selected( $current, $key ); ?>>
					<?php echo esc_html( $label ); ?>
				</option>
			<?php endforeach; ?>
		</select>

		<?php
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only filter.
		$service = isset( $_GET['bc_service'] ) ? sanitize_title( wp_unslash( $_GET['bc_service'] ) ) : '';
		?>
		<label class="screen-reader-text" for="bc_service"><?php esc_html_e( 'Filter by service', 'briteclean-bookings' ); ?></label>
		<select name="bc_service" id="bc_service">
			<option value=""><?php esc_html_e( 'All services', 'briteclean-bookings' ); ?></option>
			<?php foreach ( BC_Schema::service_choices() as $slug => $label ) : ?>
				<option value="<?php echo esc_attr( $slug ); ?>" <?php selected( $service, $slug ); ?>>
					<?php echo esc_html( $label ); ?>
				</option>
			<?php endforeach; ?>
		</select>
		<?php
	}

	/**
	 * Apply the status/service filters and custom sorting to the admin query.
	 *
	 * @param WP_Query $query Current query.
	 */
	public static function apply_filters_to_query( $query ) {
		if ( ! is_admin() || ! $query->is_main_query() ) {
			return;
		}

		if ( BRITECLEAN_BOOKING_CPT !== $query->get( 'post_type' ) ) {
			return;
		}

		$meta_query = array();

		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only filter.
		$status = isset( $_GET['bc_status'] ) ? sanitize_key( wp_unslash( $_GET['bc_status'] ) ) : '';

		if ( $status && array_key_exists( $status, BC_Schema::statuses() ) ) {
			$meta_query[] = array(
				'key'   => '_bc_status',
				'value' => $status,
			);
		}

		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only filter.
		$service = isset( $_GET['bc_service'] ) ? sanitize_title( wp_unslash( $_GET['bc_service'] ) ) : '';

		if ( $service ) {
			// Services are stored as a serialised array, so match the slug inside it.
			// The delimiters keep "deep-cleaning" from also matching a longer slug that
			// merely contains it.
			$meta_query[] = array(
				'key'     => BC_Schema::meta_key( 'services' ),
				'value'   => '"' . $service . '"',
				'compare' => 'LIKE',
			);
		}

		if ( $meta_query ) {
			$existing = $query->get( 'meta_query' );
			$query->set( 'meta_query', array_merge( is_array( $existing ) ? $existing : array(), $meta_query ) );
		}

		$orderby = $query->get( 'orderby' );

		if ( 'bc_status' === $orderby ) {
			$query->set( 'meta_key', '_bc_status' );
			$query->set( 'orderby', 'meta_value' );
		}

		if ( 'bc_preferred' === $orderby ) {
			$query->set( 'meta_key', BC_Schema::meta_key( 'preferred_date' ) );
			$query->set( 'orderby', 'meta_value' );
		}
	}

	/**
	 * Render a status <select>.
	 *
	 * @param int    $post_id Booking ID.
	 * @param string $class   CSS class.
	 */
	public static function render_status_select( $post_id, $class = '' ) {
		$current = BC_CPT::get_status( $post_id );
		?>
		<select class="<?php echo esc_attr( $class ); ?>"
			name="bc_status_value"
			data-post="<?php echo esc_attr( $post_id ); ?>"
			data-current="<?php echo esc_attr( $current ); ?>"
			aria-label="<?php esc_attr_e( 'Booking status', 'briteclean-bookings' ); ?>">
			<?php foreach ( BC_Schema::statuses() as $key => $label ) : ?>
				<option value="<?php echo esc_attr( $key ); ?>" <?php selected( $current, $key ); ?>>
					<?php echo esc_html( $label ); ?>
				</option>
			<?php endforeach; ?>
		</select>
		<span class="bc-status-feedback" aria-live="polite"></span>
		<?php
	}

	/**
	 * Register the meta boxes on the booking edit screen.
	 */
	public static function add_meta_boxes() {
		add_meta_box(
			'bc_booking_details',
			__( 'Booking Details', 'briteclean-bookings' ),
			array( __CLASS__, 'render_details_box' ),
			BRITECLEAN_BOOKING_CPT,
			'normal',
			'high'
		);

		add_meta_box(
			'bc_booking_status',
			__( 'Status & Notes', 'briteclean-bookings' ),
			array( __CLASS__, 'render_status_box' ),
			BRITECLEAN_BOOKING_CPT,
			'side',
			'high'
		);

		add_meta_box(
			'bc_booking_meta',
			__( 'Submission Info', 'briteclean-bookings' ),
			array( __CLASS__, 'render_submission_box' ),
			BRITECLEAN_BOOKING_CPT,
			'side',
			'low'
		);
	}

	/**
	 * The main details meta box.
	 *
	 * Fields are editable rather than read-only, because bookings also arrive by phone
	 * and details get mis-heard. Everything saves through the same schema and the same
	 * sanitisers as the public form.
	 *
	 * @param WP_Post $post Current post.
	 */
	public static function render_details_box( $post ) {
		wp_nonce_field( 'bc_save_booking_' . $post->ID, 'bc_booking_nonce' );

		echo '<div class="bc-admin-grid">';

		foreach ( BC_Schema::steps() as $step ) {
			if ( 'review' === $step['id'] ) {
				continue;
			}

			$fields = BC_Schema::fields_for_step( $step['id'] );

			if ( ! $fields ) {
				continue;
			}

			printf( '<h3 class="bc-admin-heading">%s</h3>', esc_html( $step['title'] ) );
			echo '<table class="form-table" role="presentation"><tbody>';

			foreach ( $fields as $key => $field ) {
				$raw = get_post_meta( $post->ID, BC_Schema::meta_key( $key ), true );

				echo '<tr>';
				printf(
					'<th scope="row"><label for="bc-admin-%s">%s</label></th>',
					esc_attr( $key ),
					esc_html( $field['label'] )
				);
				echo '<td>';

				self::render_admin_input( $key, $field, $raw );

				echo '</td></tr>';
			}

			echo '</tbody></table>';
		}

		echo '</div>';
	}

	/**
	 * Render one editable field in the admin meta box.
	 *
	 * @param string $key   Field key.
	 * @param array  $field Field definition.
	 * @param mixed  $raw   Stored value.
	 */
	private static function render_admin_input( $key, $field, $raw ) {
		$id      = 'bc-admin-' . $key;
		$name    = 'bc_field[' . $key . ']';
		$choices = BC_Schema::choices( $field );

		switch ( $field['type'] ) {
			case 'checkboxes':
				$selected = is_array( $raw ) ? array_map( 'strval', $raw ) : array();

				echo '<fieldset>';

				foreach ( $choices as $value => $label ) {
					if ( '' === $value ) {
						continue;
					}

					printf(
						'<label style="display:inline-block;margin:0 16px 6px 0"><input type="checkbox" name="%s[]" value="%s" %s /> %s</label>',
						esc_attr( 'bc_field[' . $key . ']' ),
						esc_attr( $value ),
						checked( in_array( (string) $value, $selected, true ), true, false ),
						esc_html( $label )
					);
				}

				echo '</fieldset>';
				break;

			case 'select':
			case 'radios':
				printf( '<select id="%s" name="%s">', esc_attr( $id ), esc_attr( $name ) );

				if ( 'radios' === $field['type'] ) {
					printf( '<option value="">%s</option>', esc_html__( '— none —', 'briteclean-bookings' ) );
				}

				foreach ( $choices as $value => $label ) {
					printf(
						'<option value="%s" %s>%s</option>',
						esc_attr( $value ),
						selected( (string) $raw, (string) $value, false ),
						esc_html( $label )
					);
				}

				echo '</select>';
				break;

			case 'textarea':
				printf(
					'<textarea id="%s" name="%s" rows="4" class="large-text">%s</textarea>',
					esc_attr( $id ),
					esc_attr( $name ),
					esc_textarea( is_array( $raw ) ? '' : (string) $raw )
				);
				break;

			case 'date':
				printf(
					'<input type="date" id="%s" name="%s" value="%s" class="regular-text" />',
					esc_attr( $id ),
					esc_attr( $name ),
					esc_attr( is_array( $raw ) ? '' : (string) $raw )
				);
				break;

			default:
				printf(
					'<input type="%s" id="%s" name="%s" value="%s" class="regular-text" />',
					esc_attr( $field['type'] ),
					esc_attr( $id ),
					esc_attr( $name ),
					esc_attr( is_array( $raw ) ? '' : (string) $raw )
				);
				break;
		}

		if ( ! empty( $field['help'] ) ) {
			printf( '<p class="description">%s</p>', esc_html( $field['help'] ) );
		}
	}

	/**
	 * Status and internal notes meta box.
	 *
	 * @param WP_Post $post Current post.
	 */
	public static function render_status_box( $post ) {
		$notes = get_post_meta( $post->ID, '_bc_admin_notes', true );
		?>
		<p>
			<label for="bc-status-select"><strong><?php esc_html_e( 'Status', 'briteclean-bookings' ); ?></strong></label>
		</p>
		<select id="bc-status-select" name="bc_status_field" style="width:100%">
			<?php foreach ( BC_Schema::statuses() as $key => $label ) : ?>
				<option value="<?php echo esc_attr( $key ); ?>" <?php selected( BC_CPT::get_status( $post->ID ), $key ); ?>>
					<?php echo esc_html( $label ); ?>
				</option>
			<?php endforeach; ?>
		</select>

		<p style="margin-top:14px">
			<label for="bc-admin-notes"><strong><?php esc_html_e( 'Internal notes', 'briteclean-bookings' ); ?></strong></label>
		</p>
		<textarea id="bc-admin-notes" name="bc_admin_notes" rows="5" style="width:100%"><?php echo esc_textarea( (string) $notes ); ?></textarea>
		<p class="description"><?php esc_html_e( 'Only visible here. The customer never sees these.', 'briteclean-bookings' ); ?></p>
		<?php
	}

	/**
	 * Read-only submission metadata.
	 *
	 * @param WP_Post $post Current post.
	 */
	public static function render_submission_box( $post ) {
		$ip     = get_post_meta( $post->ID, '_bc_ip', true );
		$source = get_post_meta( $post->ID, '_bc_source', true );
		$agent  = get_post_meta( $post->ID, '_bc_user_agent', true );
		?>
		<p>
			<strong><?php esc_html_e( 'Submitted:', 'briteclean-bookings' ); ?></strong><br />
			<?php echo esc_html( get_the_date( 'j M Y, g:i a', $post ) ); ?>
		</p>

		<?php if ( $source ) : ?>
			<p>
				<strong><?php esc_html_e( 'From:', 'briteclean-bookings' ); ?></strong><br />
				<?php echo esc_html( 'home' === $source ? __( 'Homepage form', 'briteclean-bookings' ) : __( 'Book Now page', 'briteclean-bookings' ) ); ?>
			</p>
		<?php endif; ?>

		<?php if ( $ip ) : ?>
			<p>
				<strong><?php esc_html_e( 'IP address:', 'briteclean-bookings' ); ?></strong><br />
				<code><?php echo esc_html( $ip ); ?></code>
			</p>
		<?php endif; ?>

		<?php if ( $agent ) : ?>
			<p style="font-size:11px;color:#6b7280;word-break:break-word">
				<?php echo esc_html( $agent ); ?>
			</p>
		<?php endif; ?>
		<?php
	}

	/**
	 * Persist meta box changes.
	 *
	 * @param int     $post_id Post ID.
	 * @param WP_Post $post    Post object.
	 */
	public static function save_meta( $post_id, $post ) {
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}

		if ( ! isset( $_POST['bc_booking_nonce'] ) ) {
			return;
		}

		if ( ! wp_verify_nonce( sanitize_key( wp_unslash( $_POST['bc_booking_nonce'] ) ), 'bc_save_booking_' . $post_id ) ) {
			return;
		}

		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}

		// Reuse the public validator so admin edits are sanitised identically. Errors
		// are not surfaced here — the admin is trusted, values are simply cleaned.
		// phpcs:ignore WordPress.Security.NonceVerification.Missing -- Verified above.
		$raw = isset( $_POST['bc_field'] ) && is_array( $_POST['bc_field'] ) ? wp_unslash( $_POST['bc_field'] ) : array();

		$validator = new BC_Validator();
		$validator->validate( $raw );
		$clean = $validator->clean();

		foreach ( BC_Schema::fields() as $key => $field ) {
			// An unchecked checkbox group is absent from $_POST entirely. Skipping it
			// would make "untick every service" silently do nothing, so groups are
			// always written — the nonce above guarantees the meta box really rendered,
			// so an absent group means deselected rather than never-submitted.
			$is_group = 'checkboxes' === $field['type'];

			if ( ! $is_group && ! array_key_exists( $key, $raw ) ) {
				continue;
			}

			$value = $clean[ $key ] ?? ( $is_group ? array() : '' );

			update_post_meta( $post_id, BC_Schema::meta_key( $key ), $value );
		}

		// phpcs:ignore WordPress.Security.NonceVerification.Missing -- Verified above.
		if ( isset( $_POST['bc_status_field'] ) ) {
			BC_CPT::set_status( $post_id, sanitize_key( wp_unslash( $_POST['bc_status_field'] ) ) );
		}

		// phpcs:ignore WordPress.Security.NonceVerification.Missing -- Verified above.
		if ( isset( $_POST['bc_admin_notes'] ) ) {
			update_post_meta(
				$post_id,
				'_bc_admin_notes',
				sanitize_textarea_field( wp_unslash( $_POST['bc_admin_notes'] ) )
			);
		}
	}

	/**
	 * Enqueue admin CSS and the inline-status script.
	 *
	 * @param string $hook Current admin page.
	 */
	public static function admin_assets( $hook ) {
		$screen = get_current_screen();

		if ( ! $screen || BRITECLEAN_BOOKING_CPT !== $screen->post_type ) {
			return;
		}

		wp_register_style( 'briteclean-admin', false, array(), BRITECLEAN_BOOKINGS_VERSION );
		wp_enqueue_style( 'briteclean-admin' );

		wp_add_inline_style(
			'briteclean-admin',
			'.bc-admin-heading{margin:20px 0 0;padding:14px 0 0;border-top:1px solid #dcdcde;font-size:13px;text-transform:uppercase;letter-spacing:.06em;color:#50575e}
			 .bc-admin-grid .form-table th{width:180px}
			 .bc-status-inline{max-width:130px}
			 .bc-status-feedback{margin-left:6px;font-size:12px;color:#008a20}
			 .bc-status-feedback.is-error{color:#d63638}
			 .bc-pill-status{display:inline-block;padding:2px 10px;border-radius:999px;font-size:11px;font-weight:600}'
		);

		if ( 'edit.php' !== $hook ) {
			return;
		}

		wp_register_script( 'briteclean-admin', false, array(), BRITECLEAN_BOOKINGS_VERSION, true );
		wp_enqueue_script( 'briteclean-admin' );

		$script = "
		( function () {
			var nonce = %s;
			var ajaxUrl = %s;
			var savedText = %s;
			var errorText = %s;

			document.addEventListener( 'change', function ( event ) {
				var select = event.target;

				if ( ! select.classList || ! select.classList.contains( 'bc-status-inline' ) ) {
					return;
				}

				var feedback = select.parentNode.querySelector( '.bc-status-feedback' );
				var body = new FormData();

				body.append( 'action', 'briteclean_set_status' );
				body.append( 'nonce', nonce );
				body.append( 'post_id', select.dataset.post );
				body.append( 'status', select.value );

				select.disabled = true;

				fetch( ajaxUrl, { method: 'POST', body: body, credentials: 'same-origin' } )
					.then( function ( response ) { return response.json(); } )
					.then( function ( result ) {
						select.disabled = false;

						if ( feedback ) {
							feedback.textContent = result && result.success ? savedText : errorText;
							feedback.classList.toggle( 'is-error', ! ( result && result.success ) );
							setTimeout( function () { feedback.textContent = ''; }, 2500 );
						}

						if ( result && result.success ) {
							select.dataset.current = select.value;
						} else {
							select.value = select.dataset.current;
						}
					} )
					.catch( function () {
						select.disabled = false;
						select.value = select.dataset.current;

						if ( feedback ) {
							feedback.textContent = errorText;
							feedback.classList.add( 'is-error' );
						}
					} );
			} );
		}() );";

		wp_add_inline_script(
			'briteclean-admin',
			sprintf(
				$script,
				wp_json_encode( wp_create_nonce( 'briteclean_set_status' ) ),
				wp_json_encode( admin_url( 'admin-ajax.php' ) ),
				wp_json_encode( __( 'Saved', 'briteclean-bookings' ) ),
				wp_json_encode( __( 'Failed', 'briteclean-bookings' ) )
			)
		);
	}

	/**
	 * AJAX endpoint for the inline status dropdown.
	 */
	public static function ajax_set_status() {
		check_ajax_referer( 'briteclean_set_status', 'nonce' );

		$post_id = isset( $_POST['post_id'] ) ? absint( wp_unslash( $_POST['post_id'] ) ) : 0;
		$status  = isset( $_POST['status'] ) ? sanitize_key( wp_unslash( $_POST['status'] ) ) : '';

		if ( ! $post_id || ! current_user_can( 'edit_post', $post_id ) ) {
			wp_send_json_error( array( 'message' => __( 'Not allowed.', 'briteclean-bookings' ) ), 403 );
		}

		if ( get_post_type( $post_id ) !== BRITECLEAN_BOOKING_CPT ) {
			wp_send_json_error( array( 'message' => __( 'Not a booking.', 'briteclean-bookings' ) ), 400 );
		}

		if ( ! array_key_exists( $status, BC_Schema::statuses() ) ) {
			wp_send_json_error( array( 'message' => __( 'Unknown status.', 'briteclean-bookings' ) ), 400 );
		}

		BC_CPT::set_status( $post_id, $status );

		wp_send_json_success( array( 'status' => $status ) );
	}

	/**
	 * Warn when a booking's notification emails did not send.
	 *
	 * Silent mail failure is the worst outcome for this business — the booking exists
	 * but nobody knows about it. This makes it visible.
	 */
	public static function mail_failure_notice() {
		$screen = get_current_screen();

		if ( ! $screen || BRITECLEAN_BOOKING_CPT !== $screen->post_type ) {
			return;
		}

		$failed = get_posts(
			array(
				'post_type'      => BRITECLEAN_BOOKING_CPT,
				'posts_per_page' => 1,
				'fields'         => 'ids',
				'meta_query'     => array(
					array(
						'key'     => '_bc_owner_email_failed',
						'compare' => 'EXISTS',
					),
				),
			)
		);

		if ( ! $failed ) {
			return;
		}

		printf(
			'<div class="notice notice-warning"><p><strong>%s</strong> %s</p></div>',
			esc_html__( 'Some booking notifications did not send.', 'briteclean-bookings' ),
			esc_html__( 'The bookings themselves were saved. This usually means the site cannot send mail — install an SMTP plugin and configure a real mailbox before launch.', 'briteclean-bookings' )
		);
	}

	/**
	 * Add a "call" shortcut to the row actions.
	 *
	 * @param array   $actions Existing actions.
	 * @param WP_Post $post    Current post.
	 * @return array
	 */
	public static function row_actions( $actions, $post ) {
		if ( BRITECLEAN_BOOKING_CPT !== $post->post_type ) {
			return $actions;
		}

		// "View" makes no sense: bookings have no front-end URL.
		unset( $actions['view'], $actions['inline hide-if-no-js'] );

		$phone = get_post_meta( $post->ID, BC_Schema::meta_key( 'phone' ), true );

		if ( $phone ) {
			$actions['bc_call'] = sprintf(
				'<a href="tel:%s">%s</a>',
				esc_attr( preg_replace( '/\D+/', '', $phone ) ),
				esc_html__( 'Call', 'briteclean-bookings' )
			);
		}

		return $actions;
	}

	/**
	 * Add bulk status changes.
	 *
	 * @param array $actions Existing bulk actions.
	 * @return array
	 */
	public static function bulk_actions( $actions ) {
		foreach ( BC_Schema::statuses() as $key => $label ) {
			$actions[ 'bc_mark_' . $key ] = sprintf(
				/* translators: %s: status label. */
				__( 'Mark as %s', 'briteclean-bookings' ),
				$label
			);
		}

		return $actions;
	}

	/**
	 * Apply a bulk status change.
	 *
	 * @param string $redirect Redirect URL.
	 * @param string $action   Chosen action.
	 * @param array  $post_ids Selected IDs.
	 * @return string
	 */
	public static function handle_bulk_actions( $redirect, $action, $post_ids ) {
		if ( 0 !== strpos( $action, 'bc_mark_' ) ) {
			return $redirect;
		}

		$status = substr( $action, strlen( 'bc_mark_' ) );

		if ( ! array_key_exists( $status, BC_Schema::statuses() ) ) {
			return $redirect;
		}

		$changed = 0;

		foreach ( $post_ids as $post_id ) {
			if ( current_user_can( 'edit_post', $post_id ) && BC_CPT::set_status( $post_id, $status ) ) {
				$changed++;
			}
		}

		return add_query_arg( 'bc_bulk_updated', $changed, $redirect );
	}
}
