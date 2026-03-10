<?php
/**
 * Firefighter Stats — Getting Started Guide admin page.
 *
 * Supports English and Polish out of the box without requiring a compiled
 * MO file. The guide renders in the admin user's language (get_user_locale()).
 * Other locales fall back to English.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'Firefighter_Stats_Admin_Guide' ) ) {
    class Firefighter_Stats_Admin_Guide {

        /** @var bool|null Cached Polish-locale flag. */
        private $is_pl = null;

        public function __construct() {
            add_action( 'admin_menu', array( $this, 'add_menu' ) );
            // Move Getting Started to appear right after the CPT list item.
            add_action( 'admin_menu', array( $this, 'reorder_menu' ), 999 );
        }

        /**
         * Reorder the submenu so Getting Started appears at the top.
         */
        public function reorder_menu() {
            global $submenu;
            $parent = 'edit.php?post_type=firefighter_stats';
            if ( empty( $submenu[ $parent ] ) ) {
                return;
            }

            // Find and detach the Getting Started item.
            $guide_item = null;
            $guide_key  = null;
            foreach ( $submenu[ $parent ] as $key => $item ) {
                if ( isset( $item[2] ) && 'firefighter-stats-guide' === $item[2] ) {
                    $guide_item = $item;
                    $guide_key  = $key;
                    break;
                }
            }
            if ( null === $guide_item ) {
                return;
            }

            unset( $submenu[ $parent ][ $guide_key ] );

            // Re-index, keep CPT list first, insert guide second.
            $items = array_values( $submenu[ $parent ] );
            $first = array_shift( $items );
            array_unshift( $items, $guide_item );
            array_unshift( $items, $first );
            $submenu[ $parent ] = $items;
        }

        public function add_menu() {
            add_submenu_page(
                'edit.php?post_type=firefighter_stats',
                $this->t( 'Getting Started', 'Jak Zacząć' ),
                $this->t( 'Getting Started', 'Jak Zacząć' ),
                'manage_options',
                'firefighter-stats-guide',
                array( $this, 'render' )
            );
        }

        /**
         * Return the localised string for the admin's current language.
         * Uses get_user_locale() so each admin sees their own language.
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

        public function render() {
            $counts_url     = admin_url( 'edit.php?post_type=firefighter_stats&page=firefighter-stats-counts' );
            $categories_url = admin_url( 'edit-tags.php?taxonomy=firefighter_stats_cat&post_type=firefighter_stats' );
            $new_post_url   = admin_url( 'post-new.php?post_type=firefighter_stats' );
            $widgets_url    = admin_url( 'widgets.php' );

            $t = array( $this, 't' ); // shorthand for call_user_func
            ?>
            <div class="wrap">
                <h1><?php echo esc_html( $this->t(
                    'Getting Started — Firefighter Statistics',
                    'Jak Zacząć — Statystyki Wyjazdów'
                ) ); ?></h1>
                <p style="max-width:680px; color:#646970;">
                    <?php echo esc_html( $this->t(
                        'Follow the steps below to set up the plugin and start tracking emergency statistics for your fire department.',
                        'Wykonaj poniższe kroki, aby skonfigurować wtyczkę i rozpocząć śledzenie statystyk wyjazdów dla swojej straży pożarnej.'
                    ) ); ?>
                </p>

                <!-- 1. Quick Setup -->
                <div class="card" style="max-width:760px; margin-bottom:20px; padding:20px 24px;">
                    <h2 style="margin-top:0;">⚡ <?php echo esc_html( $this->t( 'Quick Setup', 'Szybka Konfiguracja' ) ); ?></h2>
                    <ol style="line-height:2;">
                        <li><?php echo sprintf(
                            esc_html( $this->t(
                                '%s to see the 13 default categories (Fire, Medical, Rescue…).',
                                '%s aby zobaczyć 13 domyślnych kategorii (Pożar, Medyczny, Ratownictwo…).'
                            ) ),
                            '<a href="' . esc_url( $categories_url ) . '">' . esc_html( $this->t( 'Open Emergency Categories', 'Otwórz Kategorie Wyjazdów' ) ) . '</a>'
                        ); ?></li>
                        <li><?php echo esc_html( $this->t(
                            'Edit each category to assign an icon and colour (shown in the widget).',
                            'Edytuj każdą kategorię, aby przypisać ikonę i kolor (wyświetlane w widżecie).'
                        ) ); ?></li>
                        <li><?php echo sprintf(
                            esc_html( $this->t(
                                '%s to log routine emergencies quickly without creating posts.',
                                '%s aby szybko logować rutynowe wyjazdy bez tworzenia postów.'
                            ) ),
                            '<a href="' . esc_url( $counts_url ) . '">' . esc_html( $this->t( 'Open Quick Counts', 'Otwórz Szybkie Liczniki' ) ) . '</a>'
                        ); ?></li>
                        <li><?php echo sprintf(
                            esc_html( $this->t(
                                '%s to add the emergency statistics widget to your sidebar.',
                                '%s aby dodać widżet statystyk wyjazdów do paska bocznego.'
                            ) ),
                            '<a href="' . esc_url( $widgets_url ) . '">' . esc_html( $this->t( 'Open Widgets', 'Otwórz Widżety' ) ) . '</a>'
                        ); ?></li>
                        <li><?php echo esc_html( $this->t(
                            'Optionally create detailed emergency posts for incidents that need documentation.',
                            'Opcjonalnie twórz szczegółowe posty wyjazdów dla incydentów wymagających dokumentacji.'
                        ) ); ?></li>
                    </ol>
                </div>

                <!-- 2. Categories -->
                <div class="card" style="max-width:760px; margin-bottom:20px; padding:20px 24px;">
                    <h2 style="margin-top:0;">🏷️ <?php echo esc_html( $this->t( 'Emergency Categories', 'Kategorie Wyjazdów' ) ); ?></h2>
                    <p><?php echo esc_html( $this->t(
                        'Categories organise your emergencies. Each category can have:',
                        'Kategorie organizują Twoje wyjazdy. Każda kategoria może mieć:'
                    ) ); ?></p>
                    <ul style="list-style:disc; padding-left:20px; line-height:2;">
                        <li>
                            <strong><?php echo esc_html( $this->t( 'Icon:', 'Ikona:' ) ); ?></strong>
                            <?php echo esc_html( $this->t(
                                'Choose from the predefined list (Fire 🔥, Medical 🚑, etc.) or enter any emoji in the Custom Icon field.',
                                'Wybierz z predefiniowanej listy (Pożar 🔥, Medyczny 🚑 itp.) lub wpisz dowolne emoji w polu Własna Ikona.'
                            ) ); ?>
                        </li>
                        <li>
                            <strong><?php echo esc_html( $this->t( 'Colour:', 'Kolor:' ) ); ?></strong>
                            <?php echo esc_html( $this->t(
                                'Used for the category badge in the emergency posts list.',
                                'Używany dla etykiety kategorii na liście postów wyjazdów.'
                            ) ); ?>
                        </li>
                    </ul>
                    <p style="background:#fff8e1; border-left:4px solid #ffb900; padding:10px 14px; margin-top:12px;">
                        💡 <?php echo esc_html( $this->t(
                            'The 13 default categories are a starting point — feel free to rename, delete, or add your own. Only keep the categories that match your department\'s operations.',
                            '13 domyślnych kategorii to punkt startowy — możesz je dowolnie zmieniać, usuwać lub dodawać własne. Zostaw tylko te, które pasują do działalności Twojej jednostki.'
                        ) ); ?>
                    </p>
                    <p>
                        <a href="<?php echo esc_url( $categories_url ); ?>" class="button">
                            <?php echo esc_html( $this->t( 'Manage Categories', 'Zarządzaj Kategoriami' ) ); ?>
                        </a>
                    </p>
                </div>

                <!-- 3. Logging Counts -->
                <div class="card" style="max-width:760px; margin-bottom:20px; padding:20px 24px;">
                    <h2 style="margin-top:0;">📊 <?php echo esc_html( $this->t( 'Logging Emergency Counts', 'Logowanie Liczników Wyjazdów' ) ); ?></h2>
                    <p><?php echo esc_html( $this->t(
                        'There are three ways to log emergencies:',
                        'Istnieją trzy sposoby logowania wyjazdów:'
                    ) ); ?></p>
                    <table class="widefat striped" style="max-width:680px;">
                        <thead>
                            <tr>
                                <th><?php echo esc_html( $this->t( 'Method', 'Metoda' ) ); ?></th>
                                <th><?php echo esc_html( $this->t( 'Best for', 'Najlepsze do' ) ); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>🚨 <?php echo esc_html( $this->t( 'Admin Bar Quick Add', 'Szybkie Dodawanie z Paska' ) ); ?></td>
                                <td><?php echo esc_html( $this->t(
                                    'Add +1 to any category from any admin or frontend page (hover the 🚨 menu in the toolbar).',
                                    'Dodaj +1 do dowolnej kategorii z dowolnej strony (najedź na menu 🚨 w pasku narzędzi).'
                                ) ); ?></td>
                            </tr>
                            <tr>
                                <td>⚡ <?php echo esc_html( $this->t( 'Quick Counts page', 'Strona Szybkich Liczników' ) ); ?></td>
                                <td><?php echo esc_html( $this->t(
                                    'Click a category card to open the modal — set date, optional time, and count. Best for batch logging.',
                                    'Kliknij kartę kategorii, aby otworzyć okno — ustaw datę, opcjonalny czas i liczbę. Najlepsze do wsadowego logowania.'
                                ) ); ?></td>
                            </tr>
                            <tr>
                                <td>📝 <?php echo esc_html( $this->t( 'Emergency post', 'Post wyjazdu' ) ); ?></td>
                                <td><?php echo esc_html( $this->t(
                                    'Full post with title, description, gallery, and custom fields. Counted automatically.',
                                    'Pełny post z tytułem, opisem, galerią i polami niestandardowymi. Liczony automatycznie.'
                                ) ); ?></td>
                            </tr>
                        </tbody>
                    </table>
                    <p style="margin-top:12px;">
                        <a href="<?php echo esc_url( $counts_url ); ?>" class="button">
                            <?php echo esc_html( $this->t( 'Open Quick Counts', 'Otwórz Szybkie Liczniki' ) ); ?>
                        </a>
                        <a href="<?php echo esc_url( $new_post_url ); ?>" class="button" style="margin-left:6px;">
                            <?php echo esc_html( $this->t( 'Add Emergency Post', 'Dodaj Post Wyjazdu' ) ); ?>
                        </a>
                    </p>
                </div>

                <!-- 4. Widget & Shortcode -->
                <div class="card" style="max-width:760px; margin-bottom:20px; padding:20px 24px;">
                    <h2 style="margin-top:0;">🔌 <?php echo esc_html( $this->t( 'Widget & Shortcode', 'Widżet i Shortcode' ) ); ?></h2>
                    <p><?php echo sprintf(
                        esc_html( $this->t(
                            'Add the "%s" widget to any widget area via %s.',
                            'Dodaj widżet "%s" do dowolnego obszaru widżetów przez %s.'
                        ) ),
                        esc_html( $this->t( 'Firefighter Stats Emergencies', 'Statystyki Wyjazdów Straży' ) ),
                        '<a href="' . esc_url( $widgets_url ) . '">' . esc_html( $this->t( 'Appearance → Widgets', 'Wygląd → Widżety' ) ) . '</a>'
                    ); ?></p>
                    <p><?php echo esc_html( $this->t(
                        'You can also embed statistics in any page or post using the shortcode:',
                        'Możesz również osadzić statystyki na dowolnej stronie lub poście za pomocą shortcode:'
                    ) ); ?></p>
                    <code>[firefighter_stats_emergency_list_widget]</code>
                    <h4><?php echo esc_html( $this->t( 'Shortcode attributes', 'Atrybuty shortcode' ) ); ?></h4>
                    <table class="widefat striped" style="max-width:680px;">
                        <thead>
                            <tr>
                                <th><?php echo esc_html( $this->t( 'Attribute', 'Atrybut' ) ); ?></th>
                                <th><?php echo esc_html( $this->t( 'Default', 'Domyślna' ) ); ?></th>
                                <th><?php echo esc_html( $this->t( 'Description', 'Opis' ) ); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><code>title</code></td>
                                <td><em><?php echo esc_html( $this->t( 'widget title', 'tytuł widżetu' ) ); ?></em></td>
                                <td><?php echo esc_html( $this->t( 'Section heading', 'Nagłówek sekcji' ) ); ?></td>
                            </tr>
                            <tr>
                                <td><code>show_category_summary</code></td>
                                <td><code>true</code></td>
                                <td><?php echo esc_html( $this->t( 'Show category count grid', 'Pokaż siatkę liczników kategorii' ) ); ?></td>
                            </tr>
                            <tr>
                                <td><code>category_time_period</code></td>
                                <td><code>year</code></td>
                                <td><code>all | year | month</code></td>
                            </tr>
                            <tr>
                                <td><code>selected_categories</code></td>
                                <td><em><?php echo esc_html( $this->t( 'empty = all', 'puste = wszystkie' ) ); ?></em></td>
                                <td><?php echo esc_html( $this->t( 'Comma-separated term IDs', 'Oddzielone przecinkami ID kategorii' ) ); ?></td>
                            </tr>
                            <tr>
                                <td><code>show_posts_list</code></td>
                                <td><code>true</code></td>
                                <td><?php echo esc_html( $this->t( 'Show recent emergency posts', 'Pokaż ostatnie posty wyjazdów' ) ); ?></td>
                            </tr>
                            <tr>
                                <td><code>limit</code></td>
                                <td><code>5</code></td>
                                <td><?php echo esc_html( $this->t( 'Number of posts to show', 'Liczba postów do wyświetlenia' ) ); ?></td>
                            </tr>
                            <tr>
                                <td><code>category</code></td>
                                <td><em><?php echo esc_html( $this->t( 'all', 'wszystkie' ) ); ?></em></td>
                                <td><?php echo esc_html( $this->t( 'Filter posts by term ID', 'Filtruj posty według ID kategorii' ) ); ?></td>
                            </tr>
                            <tr>
                                <td><code>order</code></td>
                                <td><code>default</code></td>
                                <td><code>date_desc | date_asc | title_asc | random</code></td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- 5. Gutenberg Block -->
                <div class="card" style="max-width:760px; margin-bottom:20px; padding:20px 24px;">
                    <h2 style="margin-top:0;">🧱 <?php echo esc_html( $this->t( 'Gutenberg Block', 'Blok Gutenberga' ) ); ?></h2>
                    <p><?php echo esc_html( $this->t(
                        'In the block editor, search for "Emergency List" to insert the statistics block. The block shares the same settings as the widget and shortcode.',
                        'W edytorze bloków wyszukaj "Emergency List", aby wstawić blok statystyk. Blok używa tych samych ustawień co widżet i shortcode.'
                    ) ); ?></p>
                    <ol style="line-height:2;">
                        <li><?php echo esc_html( $this->t(
                            'Edit any page or post using the block editor.',
                            'Edytuj dowolną stronę lub post za pomocą edytora bloków.'
                        ) ); ?></li>
                        <li><?php echo esc_html( $this->t(
                            'Click the + button to add a block.',
                            'Kliknij przycisk +, aby dodać blok.'
                        ) ); ?></li>
                        <li><?php echo esc_html( $this->t(
                            'Search for "Emergency List Widget".',
                            'Wyszukaj "Emergency List Widget".'
                        ) ); ?></li>
                        <li><?php echo esc_html( $this->t(
                            'Configure the block in the sidebar panel on the right.',
                            'Skonfiguruj blok w panelu bocznym po prawej stronie.'
                        ) ); ?></li>
                    </ol>
                </div>

            </div>
            <?php
        }
    }
}
