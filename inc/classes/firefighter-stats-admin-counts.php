<?php
/**
 * Firefighter Stats Admin Counts Management
 *
 * Provides an admin interface for manually managing emergency counts
 * without creating individual posts for each emergency
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'Firefighter_Stats_Admin_Counts' ) ) {
    class Firefighter_Stats_Admin_Counts {

        /** @var string|null Hook suffix returned by add_submenu_page(). */
        public $admin_page_hook = null;

        public function __construct() {
            add_action( 'admin_menu', array( $this, 'add_admin_menu' ) );
            add_action( 'admin_init', array( $this, 'handle_form_submission' ) );
            add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_scripts' ) );
            add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_frontend_scripts' ) );
            add_action( 'admin_bar_menu', array( $this, 'add_admin_bar_menu' ), 100 );
            add_action( 'wp_ajax_firefighter_stats_quick_add', array( $this, 'handle_quick_add_ajax' ) );
            add_action( 'wp_ajax_firefighter_stats_get_count', array( $this, 'handle_get_count_ajax' ) );
        }

        /**
         * Add admin menu page
         */
        public function add_admin_menu() {
            $this->admin_page_hook = add_submenu_page(
                'edit.php?post_type=firefighter_stats',
                esc_html__( 'Emergency Counts', 'firefighter-stats' ),
                esc_html__( 'Quick Counts', 'firefighter-stats' ),
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

            // Admin counts page script — only on the dedicated admin page.
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
                        'i18n'          => array(
                            'totalCount' => __( 'Total count for this date:', 'firefighter-stats' ),
                        ),
                    )
                );
            }
        }

        /**
         * Enqueue frontend scripts and styles for admin bar
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
        }

        /**
         * Register and enqueue the quick-add admin bar script with localised data.
         * Uses wp_script_is() to ensure localisation only runs once even if this
         * method is called from both admin and frontend hooks.
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
                            'confirm'      => __( 'Add +1 emergency to', 'firefighter-stats' ),
                            'success'      => __( 'Emergency count added successfully!', 'firefighter-stats' ),
                            'error'        => __( 'Error adding emergency count.', 'firefighter-stats' ),
                            'networkError' => __( 'Network error. Please try again.', 'firefighter-stats' ),
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

            // Get emergency categories
            $categories = get_terms( array(
                'taxonomy' => 'firefighter_stats_cat',
                'hide_empty' => false,
                'number' => 10, // Limit to prevent overcrowding
            ) );

            if ( is_wp_error( $categories ) || empty( $categories ) ) {
                return;
            }

            // Add main menu
            $wp_admin_bar->add_menu( array(
                'id' => 'firefighter-stats-quick',
                'title' => '🚨 ' . esc_html__( 'Quick Emergency', 'firefighter-stats' ),
                'href' => admin_url( 'edit.php?post_type=firefighter_stats&page=firefighter-stats-counts' ),
            ) );

            // Add category submenus
            foreach ( $categories as $category ) {
                $icon = $this->get_category_emoji_for_admin_bar( $category->term_id );

                $wp_admin_bar->add_menu( array(
                    'parent' => 'firefighter-stats-quick',
                    'id' => 'firefighter-stats-quick-' . $category->term_id,
                    'title' => $icon . ' ' . esc_html( $category->name ),
                    'href' => '#',
                    'meta' => array(
                        'class' => 'firefighter-stats-quick-add',
                        'onclick' => 'firefighterStatsQuickAdd(' . $category->term_id . ', "' . esc_js( $category->name ) . '"); return false;',
                    ),
                ) );
            }

            // Add separator and manage link
            $wp_admin_bar->add_menu( array(
                'parent' => 'firefighter-stats-quick',
                'id' => 'firefighter-stats-quick-separator',
                'title' => '<hr style="margin: 5px 0; border: none; border-top: 1px solid #ddd;">',
                'href' => '#',
                'meta' => array( 'class' => 'firefighter-stats-separator' ),
            ) );

            $wp_admin_bar->add_menu( array(
                'parent' => 'firefighter-stats-quick',
                'id' => 'firefighter-stats-quick-manage',
                'title' => '⚙️ ' . esc_html__( 'Manage Counts', 'firefighter-stats' ),
                'href' => admin_url( 'edit.php?post_type=firefighter_stats&page=firefighter-stats-counts' ),
            ) );

        }

        /**
         * Get emoji for category in admin bar
         */
        private function get_category_emoji_for_admin_bar( $category_id ) {
            $icon = get_term_meta( $category_id, 'firefighter_stats_category_icon', true );

            $icon_map = array(
                'fire' => '🔥',
                'medical' => '🚑',
                'rescue' => '🆘',
                'accident' => '⚠️',
                'threat' => '⚠️',
                'hazmat' => '☢️',
                'water' => '🌊',
                'technical' => '🔧',
                'vehicle' => '🚗',
                'structure' => '🏢',
                'false-alarm' => '🚫',
                'exercise' => '🏋️',
                'other' => '📋',
            );

            return isset( $icon_map[ $icon ] ) ? $icon_map[ $icon ] : '📋';
        }

        /**
         * Handle AJAX quick add request
         */
        public function handle_quick_add_ajax() {
            // Verify nonce
            if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'firefighter_stats_quick_add' ) ) {
                wp_send_json_error( array( 'message' => 'Invalid nonce' ) );
            }

            // Check capabilities
            if ( ! current_user_can( 'manage_options' ) ) {
                wp_send_json_error( array( 'message' => 'Insufficient permissions' ) );
            }

            $category_id = isset( $_POST['category_id'] ) ? absint( $_POST['category_id'] ) : 0;

            if ( empty( $category_id ) ) {
                wp_send_json_error( array( 'message' => 'Invalid category' ) );
            }

            // Add the emergency count
            $this->add_emergency_count( $category_id, 1, wp_date( 'Y-m-d' ) );

            wp_send_json_success( array(
                'message' => __( 'Emergency count added successfully!', 'firefighter-stats' ),
            ) );
        }

        /**
         * Handle AJAX request to get current count for a specific date
         */
        public function handle_get_count_ajax() {
            // Verify nonce
            if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'firefighter_stats_get_count' ) ) {
                wp_send_json_error( array( 'message' => 'Invalid nonce' ) );
            }

            // Check capabilities
            if ( ! current_user_can( 'manage_options' ) ) {
                wp_send_json_error( array( 'message' => 'Insufficient permissions' ) );
            }

            $category_id = isset( $_POST['category_id'] ) ? absint( $_POST['category_id'] ) : 0;
            $date        = isset( $_POST['date'] ) ? sanitize_text_field( wp_unslash( $_POST['date'] ) ) : '';

            if ( empty( $category_id ) || empty( $date ) ) {
                wp_send_json_error( array( 'message' => 'Invalid parameters' ) );
            }

            // Get manual counts for this category and date
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

            // Handle flush_permalinks before category validation — it doesn't need a category.
            if ( 'flush_permalinks' === $action ) {
                flush_rewrite_rules();
                add_action( 'admin_notices', static function () {
                    echo '<div class="notice notice-success"><p>' . esc_html__( 'Permalinks flushed successfully. URLs should now work correctly.', 'firefighter-stats' ) . '</p></div>';
                } );
                return;
            }

            if ( empty( $category_id ) || $count <= 0 ) {
                add_action( 'admin_notices', static function () {
                    echo '<div class="notice notice-error"><p>' . esc_html__( 'Please select a category and enter a valid count.', 'firefighter-stats' ) . '</p></div>';
                } );
                return;
            }

            if ( 'add_count' === $action ) {
                $this->add_manual_emergency_count( $category_id, $count, $date );
                $display_date = empty( $date ) ? wp_date( 'Y-m-d' ) : $date;
                add_action( 'admin_notices', static function () use ( $count, $display_date ) {
                    echo '<div class="notice notice-success"><p>' . esc_html( sprintf( __( 'Successfully added %d emergency count(s) for %s.', 'firefighter-stats' ), $count, $display_date ) ) . '</p></div>';
                } );
            } elseif ( 'delete_entry' === $action ) {
                $entry_id = isset( $_POST['entry_id'] ) ? sanitize_text_field( wp_unslash( $_POST['entry_id'] ) ) : '';
                $this->delete_manual_emergency_entry( $category_id, $entry_id );
                add_action( 'admin_notices', static function () {
                    echo '<div class="notice notice-success"><p>' . esc_html__( 'Emergency count entry deleted successfully.', 'firefighter-stats' ) . '</p></div>';
                } );
            }
        }

        /**
         * Add manual emergency count entry for category
         */
        private function add_manual_emergency_count( $category_id, $count, $date = '' ) {
            if ( empty( $date ) ) {
                $date = wp_date( 'Y-m-d' );
            }

            // Get existing manual counts
            $manual_counts = get_term_meta( $category_id, 'firefighter_stats_manual_counts', true );
            if ( ! is_array( $manual_counts ) ) {
                $manual_counts = array();
            }

            // Create unique entry ID
            $entry_id = uniqid( $date . '_' );

            // Add new entry
            $manual_counts[ $entry_id ] = array(
                'count'     => intval( $count ),
                'date'      => $date,
                'timestamp' => time(),
                'user_id'   => get_current_user_id(),
            );

            // Save updated counts
            update_term_meta( $category_id, 'firefighter_stats_manual_counts', $manual_counts );

            // Update cached totals
            $this->update_category_total_count( $category_id );
        }

        /**
         * Delete manual emergency count entry
         */
        private function delete_manual_emergency_entry( $category_id, $entry_id ) {
            // Get existing manual counts
            $manual_counts = get_term_meta( $category_id, 'firefighter_stats_manual_counts', true );
            if ( ! is_array( $manual_counts ) ) {
                return;
            }

            // Remove the entry
            if ( isset( $manual_counts[ $entry_id ] ) ) {
                unset( $manual_counts[ $entry_id ] );

                // Save updated counts
                update_term_meta( $category_id, 'firefighter_stats_manual_counts', $manual_counts );

                // Update cached totals
                $this->update_category_total_count( $category_id );
            }
        }

        /**
         * Add emergency count to category (for quick add functionality)
         */
        private function add_emergency_count( $category_id, $count, $date = '' ) {
            // Use the same method as the admin form
            $this->add_manual_emergency_count( $category_id, $count, $date );
        }

        /**
         * Update category total count cache
         */
        private function update_category_total_count( $category_id ) {
            // Calculate manual total from all entries
            $manual_counts = get_term_meta( $category_id, 'firefighter_stats_manual_counts', true );
            $manual_total = 0;

            if ( is_array( $manual_counts ) ) {
                foreach ( $manual_counts as $entry_id => $data ) {
                    $manual_total += intval( $data['count'] );
                }
            }

            // Get post counts
            $post_count = firefighter_stats_count_posts_by_term( $category_id, 'firefighter_stats_cat' );

            // Total count
            $total_count = $manual_total + $post_count;

            // Cache the totals
            update_term_meta( $category_id, 'firefighter_stats_total_count', $total_count );
            update_term_meta( $category_id, 'firefighter_stats_manual_total', $manual_total );
        }

        /**
         * Get category total count (including manual counts)
         */
        public static function get_category_total_count( $category_id, $time_period = 'all' ) {
            // For now, return all-time count
            // TODO: Add time period filtering for manual counts
            if ( 'all' === $time_period ) {
                $cached_total = get_term_meta( $category_id, 'firefighter_stats_total_count', true );
                if ( $cached_total !== '' ) {
                    return (int) $cached_total;
                }
            }

            // Fallback to calculating on the fly
            $manual_counts = get_term_meta( $category_id, 'firefighter_stats_manual_counts', true );
            $manual_total = 0;
            
            if ( is_array( $manual_counts ) ) {
                foreach ( $manual_counts as $entry ) {
                    // Add time period filtering here if needed
                    $manual_total += (int) $entry['count'];
                }
            }

            $post_count = firefighter_stats_count_posts_by_term( $category_id, 'firefighter_stats_cat' );
            return $manual_total + $post_count;
        }

        /**
         * Admin page content
         */
        public function admin_page() {
            $categories = get_terms( array(
                'taxonomy' => 'firefighter_stats_cat',
                'hide_empty' => false,
            ) );

            ?>
            <div class="wrap">
                <h1><?php esc_html_e( 'Emergency Counts Management', 'firefighter-stats' ); ?></h1>
                <p><?php esc_html_e( 'Quickly add emergency counts without creating individual posts. Perfect for routine emergencies that don\'t need detailed documentation.', 'firefighter-stats' ); ?></p>

                <!-- Permalink Flush Button -->
                <div style="background: #fff; border: 1px solid #ccd0d4; border-radius: 4px; padding: 15px; margin-bottom: 20px;">
                    <h3 style="margin-top: 0;"><?php esc_html_e( 'URL Fix', 'firefighter-stats' ); ?></h3>
                    <p><?php esc_html_e( 'If emergency URLs are showing 404 errors, click this button to fix them:', 'firefighter-stats' ); ?></p>
                    <form method="post" action="" style="display: inline;">
                        <?php wp_nonce_field( 'firefighter_stats_counts_action', 'firefighter_stats_counts_nonce' ); ?>
                        <input type="hidden" name="action" value="flush_permalinks">
                        <input type="hidden" name="category_id" value="0">
                        <input type="hidden" name="count" value="0">
                        <input type="hidden" name="date" value="">
                        <?php submit_button( esc_html__( 'Fix Emergency URLs', 'firefighter-stats' ), 'secondary', 'submit', false ); ?>
                    </form>
                </div>

                <div class="firefighter-stats-admin-layout">
                    
                    <!-- Add Count Form -->
                    <div class="firefighter-stats-add-count">
                        <h2><?php esc_html_e( 'Add Manual Emergency Count', 'firefighter-stats' ); ?></h2>

                        <form method="post" action="">
                            <?php wp_nonce_field( 'firefighter_stats_counts_action', 'firefighter_stats_counts_nonce' ); ?>
                            <input type="hidden" name="action" value="add_count">

                            <table class="form-table">
                                <tr>
                                    <th scope="row">
                                        <label for="category_id"><?php esc_html_e( 'Emergency Category', 'firefighter-stats' ); ?></label>
                                    </th>
                                    <td>
                                        <select name="category_id" id="category_id" required onchange="updateCurrentCount()">
                                            <option value=""><?php esc_html_e( 'Select Category', 'firefighter-stats' ); ?></option>
                                            <?php if ( ! is_wp_error( $categories ) && ! empty( $categories ) ) : ?>
                                                <?php foreach ( $categories as $category ) : ?>
                                                    <option value="<?php echo esc_attr( $category->term_id ); ?>">
                                                        <?php echo esc_html( $category->name ); ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            <?php endif; ?>
                                        </select>
                                        <p class="description" id="current-count-display" style="margin-top: 5px; font-weight: bold; color: #0073aa;"></p>
                                    </td>
                                </tr>
                                <tr>
                                    <th scope="row">
                                        <label for="date"><?php esc_html_e( 'Date', 'firefighter-stats' ); ?></label>
                                    </th>
                                    <td>
                                        <input type="date" name="date" id="date" value="<?php echo esc_attr( current_time( 'Y-m-d' ) ); ?>" onchange="updateCurrentCount()">
                                        <p class="description"><?php esc_html_e( 'Date for the manual count (defaults to today).', 'firefighter-stats' ); ?></p>
                                    </td>
                                </tr>
                                <tr>
                                    <th scope="row">
                                        <label for="count"><?php esc_html_e( 'Count to Add', 'firefighter-stats' ); ?></label>
                                    </th>
                                    <td>
                                        <input type="number" name="count" id="count" min="1" max="100" value="1" required>
                                        <p class="description"><?php esc_html_e( 'Number of emergencies to add for this date.', 'firefighter-stats' ); ?></p>
                                    </td>
                                </tr>
                            </table>

                            <?php submit_button( esc_html__( 'Add Emergency Count', 'firefighter-stats' ), 'primary', 'submit', false ); ?>
                        </form>
                    </div>

                    <!-- Current Counts Overview -->
                    <div class="firefighter-stats-overview">
                        <h2><?php esc_html_e( 'Current Counts Overview', 'firefighter-stats' ); ?></h2>
                        
                        <?php if ( ! is_wp_error( $categories ) && ! empty( $categories ) ) : ?>
                            <table class="wp-list-table widefat fixed striped">
                                <thead>
                                    <tr>
                                        <th><?php esc_html_e( 'Category', 'firefighter-stats' ); ?></th>
                                        <th><?php esc_html_e( 'Posts', 'firefighter-stats' ); ?></th>
                                        <th><?php esc_html_e( 'Manual Counts', 'firefighter-stats' ); ?></th>
                                        <th><?php esc_html_e( 'Total', 'firefighter-stats' ); ?></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ( $categories as $category ) : ?>
                                        <?php
                                        $post_count = firefighter_stats_count_posts_by_term( $category->term_id, 'firefighter_stats_cat' );
                                        $manual_total = get_term_meta( $category->term_id, 'firefighter_stats_manual_total', true );
                                        $manual_total = $manual_total ? (int) $manual_total : 0;
                                        $total_count = $post_count + $manual_total;
                                        ?>
                                        <tr>
                                            <td><strong><?php echo esc_html( $category->name ); ?></strong></td>
                                            <td><?php echo esc_html( $post_count ); ?></td>
                                            <td><?php echo esc_html( $manual_total ); ?></td>
                                            <td><strong><?php echo esc_html( $total_count ); ?></strong></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        <?php else : ?>
                            <p><?php esc_html_e( 'No emergency categories found. Please create some categories first.', 'firefighter-stats' ); ?></p>
                        <?php endif; ?>
                    </div>

                    <!-- Manual Count Entries Management -->
                    <div class="firefighter-stats-entries">
                        <h2><?php esc_html_e( 'Manual Count Entries', 'firefighter-stats' ); ?></h2>

                        <?php if ( ! is_wp_error( $categories ) && ! empty( $categories ) ) : ?>
                            <?php foreach ( $categories as $category ) : ?>
                                <?php
                                $manual_counts = get_term_meta( $category->term_id, 'firefighter_stats_manual_counts', true );
                                if ( ! is_array( $manual_counts ) || empty( $manual_counts ) ) {
                                    continue;
                                }
                                ?>
                                <h3><?php echo esc_html( $category->name ); ?></h3>
                                <table class="wp-list-table widefat fixed striped">
                                    <thead>
                                        <tr>
                                            <th><?php esc_html_e( 'Date', 'firefighter-stats' ); ?></th>
                                            <th><?php esc_html_e( 'Count', 'firefighter-stats' ); ?></th>
                                            <th><?php esc_html_e( 'Added By', 'firefighter-stats' ); ?></th>
                                            <th><?php esc_html_e( 'Time', 'firefighter-stats' ); ?></th>
                                            <th><?php esc_html_e( 'Actions', 'firefighter-stats' ); ?></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ( $manual_counts as $entry_id => $data ) : ?>
                                            <?php
                                            $user = get_user_by( 'id', $data['user_id'] );
                                            $user_name = $user ? $user->display_name : __( 'Unknown', 'firefighter-stats' );
                                            $time = date_i18n( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), $data['timestamp'] );
                                            ?>
                                            <tr>
                                                <td><?php echo esc_html( $data['date'] ); ?></td>
                                                <td><strong><?php echo esc_html( $data['count'] ); ?></strong></td>
                                                <td><?php echo esc_html( $user_name ); ?></td>
                                                <td><?php echo esc_html( $time ); ?></td>
                                                <td>
                                                    <form method="post" action="" style="display: inline;">
                                                        <?php wp_nonce_field( 'firefighter_stats_counts_action', 'firefighter_stats_counts_nonce' ); ?>
                                                        <input type="hidden" name="action" value="delete_entry">
                                                        <input type="hidden" name="category_id" value="<?php echo esc_attr( $category->term_id ); ?>">
                                                        <input type="hidden" name="entry_id" value="<?php echo esc_attr( $entry_id ); ?>">
                                                        <button type="submit" class="button button-small" onclick="return confirm('<?php echo esc_js( __( 'Are you sure you want to delete this entry?', 'firefighter-stats' ) ); ?>')">
                                                            <?php esc_html_e( 'Delete', 'firefighter-stats' ); ?>
                                                        </button>
                                                    </form>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                                <br>
                            <?php endforeach; ?>
                        <?php else : ?>
                            <p><?php esc_html_e( 'No manual count entries found.', 'firefighter-stats' ); ?></p>
                        <?php endif; ?>
                    </div>

                </div>
            </div>

            <?php
        }
    }
}

/**
 * Count published posts belonging to a specific term.
 *
 * Uses posts_per_page=1 + SQL_CALC_FOUND_ROWS instead of fetching all IDs,
 * which is far more efficient for large datasets.
 */
if ( ! function_exists( 'firefighter_stats_count_posts_by_term' ) ) {
    function firefighter_stats_count_posts_by_term( $term_id, $taxonomy ) {
        $query = new WP_Query( array(
            'post_type'      => 'firefighter_stats',
            'post_status'    => 'publish',
            'posts_per_page' => 1,
            'fields'         => 'ids',
            'no_found_rows'  => false,
            'tax_query'      => array(
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
