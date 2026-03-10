<?php
/**
 * Firefighter Stats Admin Counts Management
 *
 * Provides an admin interface for manually managing emergency counts
 * without creating individual posts for each emergency.
 *
 * Supports English and Polish out of the box via the t() helper —
 * no compiled MO file required.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'Firefighter_Stats_Admin_Counts' ) ) {
    class Firefighter_Stats_Admin_Counts {

        /** @var string|null Hook suffix returned by add_submenu_page(). */
        public $admin_page_hook = null;

        /** @var bool|null Cached Polish-locale flag. */
        private $is_pl = null;

        public function __construct() {
            add_action( 'admin_menu', array( $this, 'add_admin_menu' ) );
            add_action( 'admin_init', array( $this, 'handle_form_submission' ) );
            add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_scripts' ) );
            add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_frontend_scripts' ) );
            add_action( 'admin_bar_menu', array( $this, 'add_admin_bar_menu' ), 100 );
            add_action( 'wp_ajax_firefighter_stats_quick_add', array( $this, 'handle_quick_add_ajax' ) );
            add_action( 'wp_ajax_firefighter_stats_get_count', array( $this, 'handle_get_count_ajax' ) );
            add_action( 'wp_ajax_firefighter_stats_add_count_ajax', array( $this, 'handle_add_count_ajax' ) );
        }

        /**
         * Return the localised string for the current admin user's language.
         * Falls back to English for any locale other than pl_*.
         *
         * @param string $en English text.
         * @param string $pl Polish text.
         * @return string
         */
        private function t( $en, $pl ) {
            if ( null === $this->is_pl ) {
                $this->is_pl = ( strpos( get_user_locale(), 'pl' ) === 0 );
            }
            return $this->is_pl ? $pl : $en;
        }

        /**
         * Add admin menu page
         */
        public function add_admin_menu() {
            $this->admin_page_hook = add_submenu_page(
                'edit.php?post_type=firefighter_stats',
                $this->t( 'Emergency Counts', 'Liczniki Wyjazdów' ),
                $this->t( 'Quick Counts', 'Szybkie Liczniki' ),
                'manage_options',
                'firefighter-stats-counts',
                array( $this, 'admin_page' )
            );
        }

        /**
         * Enqueue admin scripts and styles
         */
        public function enqueue_admin_scripts( $hook ) {
            wp_enqueue_style(
                'firefighter-stats-admin',
                FIREFIGHTER_STATS_PLUGIN_URL . 'assets/css/admin.css',
                array(),
                FIREFIGHTER_STATS_VERSION
            );

            // Quick-add script needed on all admin pages (admin bar is present everywhere).
            $this->enqueue_quick_add_script();

            // Admin counts page script — only on the dedicated Quick Counts admin page.
            if ( $hook === $this->admin_page_hook ) {
                wp_enqueue_script(
                    'firefighter-stats-admin-counts',
                    FIREFIGHTER_STATS_PLUGIN_URL . 'assets/js/admin-counts.js',
                    array(),
                    FIREFIGHTER_STATS_VERSION,
                    true
                );
                wp_localize_script(
                    'firefighter-stats-admin-counts',
                    'firefighterStatsCountsData',
                    array(
                        'ajaxUrl'       => admin_url( 'admin-ajax.php' ),
                        'getCountNonce' => wp_create_nonce( 'firefighter_stats_get_count' ),
                        'addCountNonce' => wp_create_nonce( 'firefighter_stats_add_count_ajax' ),
                        'i18n'          => array(
                            'totalCount' => $this->t( 'Total count for this date:', 'Łączna liczba dla tej daty:' ),
                            'adding'     => $this->t( 'Adding...', 'Dodawanie...' ),
                            'added'      => $this->t( 'Added!', 'Dodano!' ),
                            'errorMsg'   => $this->t( 'Error. Please try again.', 'Błąd. Spróbuj ponownie.' ),
                            'confirmDel' => $this->t( 'Are you sure you want to delete this entry?', 'Czy na pewno chcesz usunąć ten wpis?' ),
                        ),
                    )
                );
            }
        }

        /**
         * Enqueue frontend scripts and styles for admin bar + widget quick-actions panel.
         */
        public function enqueue_frontend_scripts() {
            if ( ! is_user_logged_in() || ! current_user_can( 'manage_options' ) ) {
                return;
            }
            wp_enqueue_style(
                'firefighter-stats-admin',
                FIREFIGHTER_STATS_PLUGIN_URL . 'assets/css/admin.css',
                array(),
                FIREFIGHTER_STATS_VERSION
            );
            $this->enqueue_quick_add_script();

            // Load admin-counts.js so fsWidgetQuickAdd() is available for the
            // admin quick-actions panel rendered inside widgets on the frontend.
            if ( ! wp_script_is( 'firefighter-stats-admin-counts', 'registered' ) ) {
                wp_register_script(
                    'firefighter-stats-admin-counts',
                    FIREFIGHTER_STATS_PLUGIN_URL . 'assets/js/admin-counts.js',
                    array(),
                    FIREFIGHTER_STATS_VERSION,
                    true
                );
                wp_localize_script(
                    'firefighter-stats-admin-counts',
                    'firefighterStatsCountsData',
                    array(
                        'ajaxUrl'       => admin_url( 'admin-ajax.php' ),
                        'addCountNonce' => wp_create_nonce( 'firefighter_stats_add_count_ajax' ),
                        'i18n'          => array(
                            'adding'   => $this->t( 'Adding...', 'Dodawanie...' ),
                            'errorMsg' => $this->t( 'Error. Please try again.', 'Błąd. Spróbuj ponownie.' ),
                        ),
                    )
                );
            }
            wp_enqueue_script( 'firefighter-stats-admin-counts' );
        }

        /**
         * Register and enqueue the quick-add admin bar script with localised data.
         */
        private function enqueue_quick_add_script() {
            if ( ! current_user_can( 'manage_options' ) ) {
                return;
            }

            $handle = 'firefighter-stats-quick-add';

            if ( ! wp_script_is( $handle, 'registered' ) ) {
                wp_register_script(
                    $handle,
                    FIREFIGHTER_STATS_PLUGIN_URL . 'assets/js/admin-quick-add.js',
                    array(),
                    FIREFIGHTER_STATS_VERSION,
                    true
                );
                wp_localize_script(
                    $handle,
                    'firefighterStatsQuickAddData',
                    array(
                        'ajaxUrl' => admin_url( 'admin-ajax.php' ),
                        'nonce'   => wp_create_nonce( 'firefighter_stats_quick_add' ),
                        'i18n'    => array(
                            'confirm'      => $this->t( 'Add +1 emergency to', 'Dodaj +1 wyjazd do' ),
                            'success'      => $this->t( 'Emergency count added successfully!', 'Licznik wyjazdów dodany pomyślnie!' ),
                            'error'        => $this->t( 'Error adding emergency count.', 'Błąd podczas dodawania licznika wyjazdów.' ),
                            'networkError' => $this->t( 'Network error. Please try again.', 'Błąd sieci. Spróbuj ponownie.' ),
                        ),
                    )
                );
            }

            wp_enqueue_script( $handle );
        }

        /**
         * Add admin bar menu for quick emergency logging
         */
        public function add_admin_bar_menu( $wp_admin_bar ) {
            if ( ! current_user_can( 'manage_options' ) ) {
                return;
            }

            $categories = get_terms( array(
                'taxonomy'   => 'firefighter_stats_cat',
                'hide_empty' => false,
                'number'     => 10,
            ) );

            if ( is_wp_error( $categories ) || empty( $categories ) ) {
                return;
            }

            $wp_admin_bar->add_menu( array(
                'id'    => 'firefighter-stats-quick',
                'title' => '🚨 ' . esc_html( $this->t( 'Quick Emergency', 'Szybki Wyjazd' ) ),
                'href'  => admin_url( 'edit.php?post_type=firefighter_stats&page=firefighter-stats-counts' ),
            ) );

            foreach ( $categories as $category ) {
                $icon = firefighter_stats_get_category_emoji( $category->term_id );

                $wp_admin_bar->add_menu( array(
                    'parent' => 'firefighter-stats-quick',
                    'id'     => 'firefighter-stats-quick-' . $category->term_id,
                    'title'  => $icon . ' ' . esc_html( $category->name ),
                    'href'   => '#',
                    'meta'   => array(
                        'class'   => 'firefighter-stats-quick-add',
                        'onclick' => 'firefighterStatsQuickAdd(' . $category->term_id . ', "' . esc_js( $category->name ) . '"); return false;',
                    ),
                ) );
            }

            $wp_admin_bar->add_menu( array(
                'parent' => 'firefighter-stats-quick',
                'id'     => 'firefighter-stats-quick-separator',
                'title'  => '<hr style="margin: 5px 0; border: none; border-top: 1px solid #ddd;">',
                'href'   => '#',
                'meta'   => array( 'class' => 'firefighter-stats-separator' ),
            ) );

            $wp_admin_bar->add_menu( array(
                'parent' => 'firefighter-stats-quick',
                'id'     => 'firefighter-stats-quick-manage',
                'title'  => '⚙️ ' . esc_html( $this->t( 'Manage Counts', 'Zarządzaj Licznikami' ) ),
                'href'   => admin_url( 'edit.php?post_type=firefighter_stats&page=firefighter-stats-counts' ),
            ) );
        }

        /**
         * Handle AJAX quick add request (admin bar)
         */
        public function handle_quick_add_ajax() {
            if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'firefighter_stats_quick_add' ) ) {
                wp_send_json_error( array( 'message' => 'Invalid nonce' ) );
            }

            if ( ! current_user_can( 'manage_options' ) ) {
                wp_send_json_error( array( 'message' => 'Insufficient permissions' ) );
            }

            $category_id = isset( $_POST['category_id'] ) ? absint( $_POST['category_id'] ) : 0;

            if ( empty( $category_id ) ) {
                wp_send_json_error( array( 'message' => 'Invalid category' ) );
            }

            $this->add_manual_emergency_count( $category_id, 1, wp_date( 'Y-m-d' ) );

            wp_send_json_success( array(
                'message' => $this->t( 'Emergency count added successfully!', 'Licznik wyjazdów dodany pomyślnie!' ),
            ) );
        }

        /**
         * Handle AJAX request to get current count for a specific date
         */
        public function handle_get_count_ajax() {
            if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'firefighter_stats_get_count' ) ) {
                wp_send_json_error( array( 'message' => 'Invalid nonce' ) );
            }

            if ( ! current_user_can( 'manage_options' ) ) {
                wp_send_json_error( array( 'message' => 'Insufficient permissions' ) );
            }

            $category_id = isset( $_POST['category_id'] ) ? absint( $_POST['category_id'] ) : 0;
            $date        = isset( $_POST['date'] ) ? sanitize_text_field( wp_unslash( $_POST['date'] ) ) : '';

            if ( empty( $category_id ) || empty( $date ) ) {
                wp_send_json_error( array( 'message' => 'Invalid parameters' ) );
            }

            $manual_counts = get_term_meta( $category_id, 'firefighter_stats_manual_counts', true );
            $count         = 0;

            if ( is_array( $manual_counts ) ) {
                foreach ( $manual_counts as $data ) {
                    if ( $data['date'] === $date ) {
                        $count += intval( $data['count'] );
                    }
                }
            }

            wp_send_json_success( array( 'count' => $count ) );
        }

        /**
         * Handle AJAX add-count request from the modal
         */
        public function handle_add_count_ajax() {
            if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'firefighter_stats_add_count_ajax' ) ) {
                wp_send_json_error( array( 'message' => 'Invalid nonce' ) );
            }

            if ( ! current_user_can( 'manage_options' ) ) {
                wp_send_json_error( array( 'message' => 'Insufficient permissions' ) );
            }

            $category_id = isset( $_POST['category_id'] ) ? absint( $_POST['category_id'] ) : 0;
            $count       = isset( $_POST['count'] ) ? (int) $_POST['count'] : 0;
            $date        = isset( $_POST['date'] ) ? sanitize_text_field( wp_unslash( $_POST['date'] ) ) : '';
            $time        = isset( $_POST['time'] ) ? sanitize_text_field( wp_unslash( $_POST['time'] ) ) : '';

            if ( empty( $category_id ) || $count <= 0 ) {
                wp_send_json_error( array( 'message' => 'Invalid parameters' ) );
            }

            if ( ! empty( $time ) && ! preg_match( '/^\d{2}:\d{2}$/', $time ) ) {
                $time = '';
            }

            $this->add_manual_emergency_count( $category_id, $count, $date, $time );

            $entry_year = ! empty( $date ) ? (int) substr( $date, 0, 4 ) : (int) wp_date( 'Y' );
            $year_count = $this->get_category_count_for_year( $category_id, $entry_year );
            $last_entry = $this->get_category_last_entry( $category_id );

            wp_send_json_success( array(
                'message'    => $this->t( 'Emergency count added successfully!', 'Licznik wyjazdów dodany pomyślnie!' ),
                'year_count' => $year_count,
                'last_entry' => $last_entry,
            ) );
        }

        /**
         * Handle form submissions
         */
        public function handle_form_submission() {
            if ( ! isset( $_POST['firefighter_stats_counts_nonce'] ) ||
                 ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['firefighter_stats_counts_nonce'] ) ), 'firefighter_stats_counts_action' ) ) {
                return;
            }

            if ( ! current_user_can( 'manage_options' ) ) {
                return;
            }

            $action      = isset( $_POST['action'] ) ? sanitize_text_field( wp_unslash( $_POST['action'] ) ) : '';
            $category_id = isset( $_POST['category_id'] ) ? (int) $_POST['category_id'] : 0;
            $count       = isset( $_POST['count'] ) ? (int) $_POST['count'] : 0;
            $date        = isset( $_POST['date'] ) ? sanitize_text_field( wp_unslash( $_POST['date'] ) ) : '';
            $time        = isset( $_POST['time'] ) ? sanitize_text_field( wp_unslash( $_POST['time'] ) ) : '';

            if ( ! empty( $time ) && ! preg_match( '/^\d{2}:\d{2}$/', $time ) ) {
                $time = '';
            }

            if ( 'flush_permalinks' === $action ) {
                flush_rewrite_rules();
                $msg = $this->t(
                    'Permalinks flushed successfully. URLs should now work correctly.',
                    'Permalinki odświeżone pomyślnie. URL-e powinny teraz działać poprawnie.'
                );
                add_action( 'admin_notices', static function () use ( $msg ) {
                    echo '<div class="notice notice-success"><p>' . esc_html( $msg ) . '</p></div>';
                } );
                return;
            }

            if ( empty( $category_id ) || $count <= 0 ) {
                $msg = $this->t(
                    'Please select a category and enter a valid count.',
                    'Proszę wybrać kategorię i wprowadzić prawidłową liczbę.'
                );
                add_action( 'admin_notices', static function () use ( $msg ) {
                    echo '<div class="notice notice-error"><p>' . esc_html( $msg ) . '</p></div>';
                } );
                return;
            }

            if ( 'add_count' === $action ) {
                $this->add_manual_emergency_count( $category_id, $count, $date, $time );
                $display_date = empty( $date ) ? wp_date( 'Y-m-d' ) : $date;
                $tpl = $this->t(
                    'Successfully added %d emergency count(s) for %s.',
                    'Pomyślnie dodano %d licznik(i) wyjazdów dla %s.'
                );
                add_action( 'admin_notices', static function () use ( $count, $display_date, $tpl ) {
                    echo '<div class="notice notice-success"><p>' . esc_html( sprintf( $tpl, $count, $display_date ) ) . '</p></div>';
                } );
            } elseif ( 'delete_entry' === $action ) {
                $entry_id = isset( $_POST['entry_id'] ) ? sanitize_text_field( wp_unslash( $_POST['entry_id'] ) ) : '';
                $this->delete_manual_emergency_entry( $category_id, $entry_id );
                $msg = $this->t(
                    'Emergency count entry deleted successfully.',
                    'Wpis licznika wyjazdów usunięty pomyślnie.'
                );
                add_action( 'admin_notices', static function () use ( $msg ) {
                    echo '<div class="notice notice-success"><p>' . esc_html( $msg ) . '</p></div>';
                } );
            }
        }

        /**
         * Add manual emergency count entry for category
         *
         * @param int    $category_id Term ID.
         * @param int    $count       Number of emergencies.
         * @param string $date        Y-m-d format.
         * @param string $time        H:i format (optional).
         */
        private function add_manual_emergency_count( $category_id, $count, $date = '', $time = '' ) {
            if ( empty( $date ) ) {
                $date = wp_date( 'Y-m-d' );
            }

            $manual_counts = get_term_meta( $category_id, 'firefighter_stats_manual_counts', true );
            if ( ! is_array( $manual_counts ) ) {
                $manual_counts = array();
            }

            $entry_id = uniqid( $date . '_' );

            $manual_counts[ $entry_id ] = array(
                'count'     => intval( $count ),
                'date'      => $date,
                'time'      => $time,
                'timestamp' => time(),
                'user_id'   => get_current_user_id(),
            );

            update_term_meta( $category_id, 'firefighter_stats_manual_counts', $manual_counts );
            $this->update_category_total_count( $category_id );
        }

        /**
         * Delete manual emergency count entry
         */
        private function delete_manual_emergency_entry( $category_id, $entry_id ) {
            $manual_counts = get_term_meta( $category_id, 'firefighter_stats_manual_counts', true );
            if ( ! is_array( $manual_counts ) ) {
                return;
            }

            if ( isset( $manual_counts[ $entry_id ] ) ) {
                unset( $manual_counts[ $entry_id ] );
                update_term_meta( $category_id, 'firefighter_stats_manual_counts', $manual_counts );
                $this->update_category_total_count( $category_id );
            }
        }

        /**
         * Update category total count cache
         */
        private function update_category_total_count( $category_id ) {
            $manual_counts = get_term_meta( $category_id, 'firefighter_stats_manual_counts', true );
            $manual_total  = 0;

            if ( is_array( $manual_counts ) ) {
                foreach ( $manual_counts as $data ) {
                    $manual_total += intval( $data['count'] );
                }
            }

            $post_count  = firefighter_stats_count_posts_by_term( $category_id, 'firefighter_stats_cat' );
            $total_count = $manual_total + $post_count;

            update_term_meta( $category_id, 'firefighter_stats_total_count', $total_count );
            update_term_meta( $category_id, 'firefighter_stats_manual_total', $manual_total );
        }

        /**
         * Get the most recent manual count entry for a category.
         *
         * @param int $category_id Term ID.
         * @return array|null Entry data or null.
         */
        private function get_category_last_entry( $category_id ) {
            $manual_counts = get_term_meta( $category_id, 'firefighter_stats_manual_counts', true );
            if ( ! is_array( $manual_counts ) || empty( $manual_counts ) ) {
                return null;
            }

            $last = null;
            foreach ( $manual_counts as $data ) {
                if ( null === $last ) {
                    $last = $data;
                    continue;
                }
                $cmp = strcmp( $data['date'], $last['date'] );
                if ( $cmp > 0 ) {
                    $last = $data;
                } elseif ( 0 === $cmp ) {
                    $data_time = ! empty( $data['time'] ) ? $data['time'] : '';
                    $last_time = ! empty( $last['time'] ) ? $last['time'] : '';
                    if ( $data_time > $last_time ) {
                        $last = $data;
                    } elseif ( $data_time === $last_time && $data['timestamp'] > $last['timestamp'] ) {
                        $last = $data;
                    }
                }
            }

            return $last;
        }

        /**
         * Get total emergency count for a category within a specific year (0 = all time).
         *
         * @param int $category_id Term ID.
         * @param int $year        4-digit year, or 0 for all time.
         * @return int
         */
        private function get_category_count_for_year( $category_id, $year ) {
            $query_args = array(
                'post_type'      => 'firefighter_stats',
                'post_status'    => 'publish',
                'posts_per_page' => 1,
                'fields'         => 'ids',
                'no_found_rows'  => false,
                'tax_query'      => array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query
                    array(
                        'taxonomy' => 'firefighter_stats_cat',
                        'field'    => 'term_id',
                        'terms'    => $category_id,
                    ),
                ),
            );
            if ( $year > 0 ) {
                $query_args['date_query'] = array( array( 'year' => $year ) );
            }
            $query      = new WP_Query( $query_args );
            $post_count = $query->found_posts;

            $manual_counts = get_term_meta( $category_id, 'firefighter_stats_manual_counts', true );
            $manual_total  = 0;
            if ( is_array( $manual_counts ) ) {
                foreach ( $manual_counts as $data ) {
                    if ( $year > 0 ) {
                        $entry_year = (int) substr( $data['date'], 0, 4 );
                        if ( $entry_year !== $year ) {
                            continue;
                        }
                    }
                    $manual_total += (int) $data['count'];
                }
            }

            return $post_count + $manual_total;
        }

        /**
         * Get category total count (including manual counts) — public API used by widgets.
         */
        public static function get_category_total_count( $category_id, $time_period = 'all' ) {
            if ( 'all' === $time_period ) {
                $cached_total = get_term_meta( $category_id, 'firefighter_stats_total_count', true );
                if ( $cached_total !== '' ) {
                    return (int) $cached_total;
                }
            }

            $manual_counts = get_term_meta( $category_id, 'firefighter_stats_manual_counts', true );
            $manual_total  = 0;

            if ( is_array( $manual_counts ) ) {
                foreach ( $manual_counts as $entry ) {
                    $manual_total += (int) $entry['count'];
                }
            }

            $post_count = firefighter_stats_count_posts_by_term( $category_id, 'firefighter_stats_cat' );
            return $manual_total + $post_count;
        }

        /**
         * Collect all years that have manual count entries across all categories.
         *
         * @param array $categories Array of WP_Term objects.
         * @return int[] Sorted descending list of years.
         */
        private function get_available_years( $categories ) {
            $years = array( (int) wp_date( 'Y' ) );

            foreach ( $categories as $category ) {
                $counts = get_term_meta( $category->term_id, 'firefighter_stats_manual_counts', true );
                if ( ! is_array( $counts ) ) {
                    continue;
                }
                foreach ( $counts as $data ) {
                    if ( ! empty( $data['date'] ) ) {
                        $y = (int) substr( $data['date'], 0, 4 );
                        if ( $y > 2000 && ! in_array( $y, $years, true ) ) {
                            $years[] = $y;
                        }
                    }
                }
            }

            rsort( $years );
            return $years;
        }

        /**
         * Admin page content
         */
        public function admin_page() {
            $categories = get_terms( array(
                'taxonomy'   => 'firefighter_stats_cat',
                'hide_empty' => false,
            ) );
            if ( is_wp_error( $categories ) ) {
                $categories = array();
            }

            // Year filter — validate GET param.
            $selected_year = 0;
            if ( isset( $_GET['year'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
                $raw = sanitize_text_field( wp_unslash( $_GET['year'] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
                if ( preg_match( '/^\d{4}$/', $raw ) ) {
                    $selected_year = (int) $raw;
                }
            }
            if ( 0 === $selected_year ) {
                $selected_year = (int) wp_date( 'Y' ); // Default to current year.
            }

            $available_years = $this->get_available_years( $categories );
            $base_url        = admin_url( 'edit.php?post_type=firefighter_stats&page=firefighter-stats-counts' );

            ?>
            <div class="wrap">
                <h1><?php echo esc_html( $this->t( 'Emergency Counts Management', 'Zarządzanie Licznikami Wyjazdów' ) ); ?></h1>
                <p><?php echo esc_html( $this->t(
                    'Quickly add emergency counts without creating individual posts. Perfect for routine emergencies that don\'t need detailed documentation.',
                    'Szybko dodawaj liczniki wyjazdów bez tworzenia pojedynczych postów. Idealne dla rutynowych wyjazdów, które nie wymagają szczegółowej dokumentacji.'
                ) ); ?></p>

                <!-- Year filter tabs -->
                <div class="fs-year-tabs">
                    <a href="<?php echo esc_url( $base_url ); ?>"
                       class="fs-year-tab <?php echo 0 === $selected_year ? 'active' : ''; ?>">
                        <?php echo esc_html( $this->t( 'All', 'Wszystkie' ) ); ?>
                    </a>
                    <?php foreach ( $available_years as $year ) : ?>
                        <a href="<?php echo esc_url( add_query_arg( 'year', $year, $base_url ) ); ?>"
                           class="fs-year-tab <?php echo $selected_year === $year ? 'active' : ''; ?>">
                            <?php echo esc_html( $year ); ?>
                        </a>
                    <?php endforeach; ?>
                </div>

                <?php if ( ! empty( $categories ) ) : ?>
                    <!-- Category Cards Grid -->
                    <div class="fs-cards-grid">
                        <?php foreach ( $categories as $category ) : ?>
                            <?php
                            $emoji      = firefighter_stats_get_category_emoji( $category->term_id );
                            $year_count = $this->get_category_count_for_year( $category->term_id, $selected_year );
                            $last       = $this->get_category_last_entry( $category->term_id );

                            if ( $last ) {
                                $last_display = esc_html( $last['date'] );
                                if ( ! empty( $last['time'] ) ) {
                                    $last_display .= ' ' . esc_html( $last['time'] );
                                }
                            } else {
                                $last_display = '—';
                            }
                            ?>
                            <div class="fs-card"
                                 id="fs-card-<?php echo esc_attr( $category->term_id ); ?>"
                                 data-category-id="<?php echo esc_attr( $category->term_id ); ?>">
                                <div class="fs-card__emoji"><?php echo esc_html( $emoji ); ?></div>
                                <div class="fs-card__name"><?php echo esc_html( $category->name ); ?></div>
                                <div class="fs-card__count">
                                    <span class="fs-card__count-num"
                                          id="fs-card-count-<?php echo esc_attr( $category->term_id ); ?>">
                                        <?php echo esc_html( $year_count ); ?>
                                    </span>
                                    <span class="fs-card__count-label">
                                        <?php echo esc_html( sprintf(
                                            $this->t( 'in %d', 'w %d' ),
                                            $selected_year
                                        ) ); ?>
                                    </span>
                                </div>
                                <div class="fs-card__last">
                                    <?php echo esc_html( $this->t( 'Last:', 'Ostatni:' ) ); ?>
                                    <span id="fs-card-last-<?php echo esc_attr( $category->term_id ); ?>">
                                        <?php echo esc_html( $last_display ); ?>
                                    </span>
                                </div>
                                <button type="button"
                                        class="button button-primary fs-card__add-btn"
                                        onclick="fsOpenModal(<?php echo esc_attr( $category->term_id ); ?>, '<?php echo esc_js( $category->name ); ?>', <?php echo esc_attr( $selected_year ); ?>)">
                                    + <?php echo esc_html( $this->t( 'Add Count', 'Dodaj Licznik' ) ); ?>
                                </button>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else : ?>
                    <p><?php echo esc_html( $this->t(
                        'No emergency categories found. Please create some categories first.',
                        'Nie znaleziono kategorii wyjazdów. Proszę najpierw utworzyć kategorie.'
                    ) ); ?></p>
                <?php endif; ?>

                <!-- Flat Entries Table -->
                <h2><?php echo esc_html( $this->t( 'Manual Count Entries', 'Wpisy Ręcznych Liczników' ) ); ?></h2>
                <?php
                $all_entries = array();
                foreach ( $categories as $category ) {
                    $counts = get_term_meta( $category->term_id, 'firefighter_stats_manual_counts', true );
                    if ( ! is_array( $counts ) ) {
                        continue;
                    }
                    foreach ( $counts as $entry_id => $data ) {
                        $entry_year = ! empty( $data['date'] ) ? (int) substr( $data['date'], 0, 4 ) : 0;
                        if ( $selected_year > 0 && $entry_year !== $selected_year ) {
                            continue;
                        }
                        $all_entries[] = array_merge( $data, array(
                            'entry_id'    => $entry_id,
                            'category_id' => $category->term_id,
                            'cat_name'    => $category->name,
                        ) );
                    }
                }

                usort( $all_entries, static function ( $a, $b ) {
                    $cmp = strcmp( $b['date'], $a['date'] );
                    return $cmp !== 0 ? $cmp : $b['timestamp'] - $a['timestamp'];
                } );
                ?>
                <?php if ( ! empty( $all_entries ) ) : ?>
                    <table class="wp-list-table widefat fixed striped">
                        <thead>
                            <tr>
                                <th><?php echo esc_html( $this->t( 'Category', 'Kategoria' ) ); ?></th>
                                <th><?php echo esc_html( $this->t( 'Date', 'Data' ) ); ?></th>
                                <th><?php echo esc_html( $this->t( 'Time', 'Czas' ) ); ?></th>
                                <th><?php echo esc_html( $this->t( 'Count', 'Liczba' ) ); ?></th>
                                <th><?php echo esc_html( $this->t( 'Added By', 'Dodane Przez' ) ); ?></th>
                                <th><?php echo esc_html( $this->t( 'Added At', 'Dodano O' ) ); ?></th>
                                <th><?php echo esc_html( $this->t( 'Actions', 'Akcje' ) ); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ( $all_entries as $entry ) : ?>
                                <?php
                                $user      = get_user_by( 'id', $entry['user_id'] );
                                $user_name = $user ? $user->display_name : $this->t( 'Unknown', 'Nieznany' );
                                $added_at  = date_i18n( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), $entry['timestamp'] );
                                $inc_time  = ! empty( $entry['time'] ) ? $entry['time'] : '—';
                                ?>
                                <tr>
                                    <td><?php echo esc_html( $entry['cat_name'] ); ?></td>
                                    <td><?php echo esc_html( $entry['date'] ); ?></td>
                                    <td><?php echo esc_html( $inc_time ); ?></td>
                                    <td><strong><?php echo esc_html( $entry['count'] ); ?></strong></td>
                                    <td><?php echo esc_html( $user_name ); ?></td>
                                    <td><?php echo esc_html( $added_at ); ?></td>
                                    <td>
                                        <form method="post" action="" style="display:inline;">
                                            <?php wp_nonce_field( 'firefighter_stats_counts_action', 'firefighter_stats_counts_nonce' ); ?>
                                            <input type="hidden" name="action" value="delete_entry">
                                            <input type="hidden" name="category_id" value="<?php echo esc_attr( $entry['category_id'] ); ?>">
                                            <input type="hidden" name="count" value="0">
                                            <input type="hidden" name="entry_id" value="<?php echo esc_attr( $entry['entry_id'] ); ?>">
                                            <button type="submit" class="button button-small"
                                                    onclick="return confirm('<?php echo esc_js( $this->t( 'Are you sure you want to delete this entry?', 'Czy na pewno chcesz usunąć ten wpis?' ) ); ?>')">
                                                <?php echo esc_html( $this->t( 'Delete', 'Usuń' ) ); ?>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php else : ?>
                    <p><?php echo esc_html( $this->t(
                        'No manual count entries found for this period.',
                        'Nie znaleziono wpisów ręcznych liczników dla tego okresu.'
                    ) ); ?></p>
                <?php endif; ?>

                <!-- URL Fix (collapsed) -->
                <details class="fs-url-fix" style="margin-top: 30px;">
                    <summary><?php echo esc_html( $this->t( 'URL Fix', 'Naprawa URL' ) ); ?></summary>
                    <div style="padding: 15px; background: #fff; border: 1px solid #ccd0d4; margin-top: 8px; border-radius: 4px;">
                        <p><?php echo esc_html( $this->t(
                            'If emergency URLs are showing 404 errors, click this button to fix them:',
                            'Jeśli URL-e wyjazdów pokazują błędy 404, kliknij ten przycisk, aby je naprawić:'
                        ) ); ?></p>
                        <form method="post" action="" style="display: inline;">
                            <?php wp_nonce_field( 'firefighter_stats_counts_action', 'firefighter_stats_counts_nonce' ); ?>
                            <input type="hidden" name="action" value="flush_permalinks">
                            <input type="hidden" name="category_id" value="0">
                            <input type="hidden" name="count" value="0">
                            <input type="hidden" name="date" value="">
                            <?php submit_button( $this->t( 'Fix Emergency URLs', 'Napraw URL-e Wyjazdów' ), 'secondary', 'submit', false ); ?>
                        </form>
                    </div>
                </details>

            </div><!-- .wrap -->

            <!-- Modal -->
            <div id="fs-count-modal" class="fs-modal" hidden aria-modal="true" role="dialog">
                <div class="fs-modal__backdrop" onclick="fsCloseModal()"></div>
                <div class="fs-modal__box">
                    <h3 id="fs-modal-cat-name"></h3>
                    <p id="fs-modal-current-count" class="fs-modal__count-note"></p>
                    <p>
                        <label for="fs-modal-count"><?php echo esc_html( $this->t( 'Count:', 'Liczba:' ) ); ?></label><br>
                        <input type="number" id="fs-modal-count" min="1" max="999" value="1">
                    </p>
                    <p>
                        <label for="fs-modal-date"><?php echo esc_html( $this->t( 'Date:', 'Data:' ) ); ?></label><br>
                        <input type="date" id="fs-modal-date" value="<?php echo esc_attr( wp_date( 'Y-m-d' ) ); ?>">
                    </p>
                    <p>
                        <label for="fs-modal-time"><?php echo esc_html( $this->t( 'Time (optional):', 'Czas (opcjonalnie):' ) ); ?></label><br>
                        <input type="time" id="fs-modal-time">
                    </p>
                    <div class="fs-modal__actions">
                        <button id="fs-modal-submit" type="button" class="button button-primary" onclick="fsSubmitModal()">
                            <?php echo esc_html( $this->t( 'Add', 'Dodaj' ) ); ?>
                        </button>
                        <button id="fs-modal-cancel" type="button" class="button" onclick="fsCloseModal()">
                            <?php echo esc_html( $this->t( 'Cancel', 'Anuluj' ) ); ?>
                        </button>
                    </div>
                </div>
            </div>
            <?php
        }
    }
}

/**
 * Count published posts belonging to a specific term.
 */
if ( ! function_exists( 'firefighter_stats_count_posts_by_term' ) ) {
    function firefighter_stats_count_posts_by_term( $term_id, $taxonomy ) {
        $query = new WP_Query( array(
            'post_type'      => 'firefighter_stats',
            'post_status'    => 'publish',
            'posts_per_page' => 1,
            'fields'         => 'ids',
            'no_found_rows'  => false,
            'tax_query'      => array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query
                array(
                    'taxonomy' => $taxonomy,
                    'field'    => 'term_id',
                    'terms'    => $term_id,
                ),
            ),
        ) );

        return $query->found_posts;
    }
}
