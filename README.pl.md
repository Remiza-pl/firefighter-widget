# Firefighter Statistics — Wtyczka WordPress

[![Licencja: GPL v2+](https://img.shields.io/badge/Licencja-GPL%20v2%2B-blue.svg)](https://www.gnu.org/licenses/gpl-2.0.html)
[![WordPress](https://img.shields.io/badge/WordPress-5.9%2B-21759b)](https://wordpress.org/)
[![PHP](https://img.shields.io/badge/PHP-7.4%2B-777bb4)](https://www.php.net/)
[![Testowano do](https://img.shields.io/badge/Testowano%20do-WP%206.9-0073aa)](https://wordpress.org/)

> 🇬🇧 [Read in English → README.md](README.md)

---

<p align="center">
  <a href="https://remiza.pl" target="_blank">
    <img src="https://remiza.pl/wp-content/uploads/2026/01/logoR-bez-tla.png" alt="Remiza.pl" height="52">
  </a>
  <br>
  <strong>✅ Oficjalnie wspierane przez <a href="https://remiza.pl">Remiza.pl</a></strong>
  <br>
  <sub>Największy polski portal strażacki — aktualności, zasoby i wsparcie społeczności</sub>
</p>

---

Wtyczka WordPress dla jednostek straży pożarnej do rejestrowania i wyświetlania statystyk wyjazdów interwencyjnych. Loguj zdarzenia według kategorii, pokazuj liczniki w widżetach lub blokach Gutenberga, a nowe wpisy dodawaj bezpośrednio z paska administracyjnego lub z widżetu na stronie.

---

## Funkcje

- **Własny typ wpisu** — dedykowany typ `firefighter_stats` (Wyjazdy) z taksonomią kategorii i tagów
- **13 predefiniowanych kategorii** — Pożar, Medyczny, Ratownictwo, Wypadek, Zagrożenie, Substancje niebezpieczne, Ratownictwo wodne, Techniczny, Pojazd, Budowlany, Fałszywy alarm, Ćwiczenia, Inne — każda z domyślnym kolorem i ikoną emoji; w pełni konfigurowalne
- **Strona Szybkie Liczniki** — rejestrowanie zdarzeń według kategorii i daty bez tworzenia pełnych wpisów; obsługuje opcjonalne pole czasu i filtrowanie roczne/całościowe
- **Przycisk na pasku admina** — dodaj +1 licznik jednym kliknięciem z dowolnej strony w panelu admina lub na froncie
- **Widżet** — widżet listy wyjazdów z podsumowaniem kategorii, listą ostatnich wpisów, konfigurowalnym okresem czasu i sortowaniem; na froncie zawiera panel szybkich akcji widoczny tylko dla administratorów
- **Widżet Kategorie wyjazdów** — wyświetla linki do kategorii z licznikami
- **Blok Gutenberga** — natywny blok (bez procesu budowania) oparty na tym samym silniku renderowania co widżet
- **Shortcode** — `[firefighter_stats_emergency_list_widget]` z pełną obsługą atrybutów
- **Dwujęzyczność (EN/PL)** — cały interfejs admina i domyślna treść dostępne po angielsku i polsku bez potrzeby skompilowanego pliku MO; dołączone pełne pliki `.po`/`.mo` dla języka polskiego

---

## Wymagania

| Wymaganie | Minimum |
|-----------|---------|
| WordPress | 5.9 |
| PHP | 7.4 |
| Testowano do WP | 6.9 |

---

## Instalacja

1. Pobierz lub sklonuj to repozytorium do katalogu `/wp-content/plugins/`:
   ```bash
   git clone https://github.com/sync667/firefighter-widget.git firefighter-widget
   ```
2. Aktywuj wtyczkę przez **Wtyczki → Zainstalowane wtyczki** w panelu WordPress.
3. Przy pierwszej aktywacji zostanie zasiana 13 domyślnych kategorii w języku Twojej strony.
4. Przejdź do **Ustawienia → Bezpośrednie odnośniki** i kliknij **Zapisz zmiany**, aby odświeżyć reguły przepisywania.
5. Dodaj widżet **Statystyki Wyjazdów**, wstaw blok Gutenberga lub użyj shortcode.

---

## Użytkowanie

### Dodawanie widżetu

Przejdź do **Wygląd → Widżety** (lub Edytor witryny dla motywów blokowych) i dodaj **Statystyki Wyjazdów**. Panel ustawień widżetu pozwala skonfigurować:

- Okres zliczania (cały czas / ten rok / ten miesiąc)
- Które kategorie wyświetlać i jak je sortować
- Czy pokazywać listę ostatnich wpisów i ile ich wyświetlić

**Panel szybkich akcji admina** — gdy jesteś zalogowany jako administrator i przeglądasz stronę, na dole każdego widżetu pojawia się pasek ⚡ Szybkie akcje. Stamtąd możesz dodać licznik lub otworzyć ekran nowego wpisu bez przechodzenia do panelu admina.

### Shortcode

```
[firefighter_stats_emergency_list_widget]
```

Wszystkie dostępne atrybuty:

| Atrybut | Domyślnie | Opis |
|---------|-----------|------|
| `title` | `🚨 Statystyki wyjazdów` | Nagłówek widżetu |
| `show_category_summary` | `true` | Pokazuj siatkę liczników kategorii |
| `category_time_period` | `all` | `all` (cały czas) / `year` (ten rok) / `month` (ten miesiąc) |
| `selected_categories` | *(wszystkie)* | Identyfikatory kategorii oddzielone przecinkami |
| `category_sort` | `alphabet` | `alphabet` / `count_desc` / `count_asc` |
| `show_zero_categories` | `true` | Uwzględniaj kategorie z licznikiem 0 |
| `show_posts_list` | `true` | Pokazuj listę ostatnich wyjazdów |
| `category` | *(wszystkie)* | Filtruj wpisy do jednej kategorii (ID terminu) |
| `limit` | `5` | Maksymalna liczba wpisów |
| `order` | `default` | `default` / `date_desc` / `date_asc` / `title_asc` / `title_desc` / `random` |
| `show_date` | `true` | Pokazuj datę wpisu |
| `show_category` | `true` | Pokazuj etykietę kategorii wpisu |
| `recent_emergencies_title` | `📝 Ostatnie interwencje` | Nagłówek sekcji wpisów |
| `more_label` | *(ukryty)* | Etykieta linku „Zobacz wszystkie" — zostaw puste, żeby ukryć |
| `id` | *(brak)* | Własny atrybut HTML `id` na kontenerze |
| `className` | *(brak)* | Dodatkowa klasa CSS na kontenerze |

**Przykład:**
```
[firefighter_stats_emergency_list_widget category_time_period="year" limit="10" show_posts_list="false"]
```

### Strona Szybkie Liczniki

Przejdź do **Wyjazdy → Szybkie liczniki**, aby:
- Przeglądać karty kategorii z sumami dla bieżącego roku i datą ostatniego wpisu
- Logować liczniki — kliknij kartę → wpisz liczbę, datę i opcjonalnie godzinę w oknie modalnym
- Filtrować wpisy według roku za pomocą zakładek
- Usuwać poszczególne wpisy

### Blok Gutenberga

W edytorze bloków wyszukaj **Emergency Statistics** (kategoria: Widżety). Blok współdzieli wszystkie ustawienia z shortcode i renderuje się identycznie na froncie.

---

## Wsparcie dla kreatorów stron

| Kreator | Typ integracji | Jak używać |
|---------|---------------|------------|
| **Elementor** | Natywny widżet | Wyszukaj „Emergency Statistics" w panelu widżetów Elementora → przeciągnij i upuść |
| **WPBakery** | Natywny element | Dostępny na liście elementów w Classic Backend Editor |
| **Beaver Builder** | Natywny moduł | Dostępny w panelu modułów Beaver Buildera w grupie „Statystyki" |
| **Bricks** | Natywny element | Dostępny w panelu elementów Bricks w grupie „Statystyki Wyjazdów" |
| **Divi** | Moduł shortcode | Wstaw moduł Tekst / Kod i wklej `[firefighter_stats_emergency_list_widget]` |
| **Oxygen** | Widżet shortcode | Dodaj widżet Shortcode i wklej `[firefighter_stats_emergency_list_widget]` |
| **Każdy inny kreator** | Shortcode | Użyj `[firefighter_stats_emergency_list_widget]` w dowolnym polu obsługującym shortcode |

Wszystkie natywne integracje udostępniają te same 13 ustawień co shortcode (z wyjątkiem `selected_categories` — w celu wielokrotnego wyboru użyj shortcode bezpośrednio).

---

## Dostosowywanie

### Własny szablon

Nadpisz szablon widżetu/shortcode, kopiując plik:

```
templates/widgets/emergency-list.php → wasz-motyw/firefighter-stats/widgets/emergency-list.php
```

Użyj filtra, aby wskazać własny plik:

```php
add_filter( 'firefighter_stats_widget_emergency_list_template_path', function( $path ) {
    $custom = get_template_directory() . '/firefighter-stats/widgets/emergency-list.php';
    return file_exists( $custom ) ? $custom : $path;
} );
```

### Ikony i kolory kategorii

Przejdź do **Wyjazdy → Kategorie wyjazdów**, edytuj dowolną kategorię i ustaw własną ikonę emoji oraz kolor hex. Są one używane w widżecie, bloku i stronach admina.

### Domyślne kategorie

13 domyślnych kategorii jest tworzonych przy aktywacji. Możesz je dowolnie zmieniać nazwy, usuwać lub dodawać własne — są to zwykłe terminy taksonomii. Seeder jest idempotentny (bezpieczne wielokrotne uruchamianie) i używa języka witryny, więc polska strona otrzyma polskie nazwy.

Filtruj listę przed jej utworzeniem:

```php
add_filter( 'firefighter_stats_default_categories', function( $categories ) {
    $categories[] = array(
        'name'        => 'Powódź',
        'slug'        => 'powodz',
        'description' => 'Działania przeciwpowodziowe',
        'icon'        => '🌧️',
        'color'       => '#5dade2',
    );
    return $categories;
} );
```

### Atrybuty shortcode

Rozszerz akceptowane atrybuty shortcode bez modyfikowania kodu wtyczki:

```php
add_filter( 'firefighter_stats_emergency_list_widget_shortcode_atts', function( $extra ) {
    $extra['moj_parametr'] = 'wartość_domyślna';
    return $extra;
} );
```

### Własne slugi URL

Przejdź do **Ustawienia → Bezpośrednie odnośniki** — sekcja **Firefighter Stats** na dole pozwala zmienić slugi URL dla wpisów, kategorii i tagów.

---

## Filtry i akcje

| Hook | Typ | Opis |
|------|-----|------|
| `firefighter_stats_widget_emergency_list_template_path` | filtr | Nadpisz szablon widżetu listy wyjazdów |
| `firefighter_stats_widget_emergency_categories_template_path` | filtr | Nadpisz szablon widżetu kategorii |
| `firefighter_stats_default_categories` | filtr | Modyfikuj domyślne kategorie przed zasiewem |
| `firefighter_stats_emergency_list_widget_shortcode_atts` | filtr | Dodaj własne atrybuty shortcode |
| `firefighter_stats_cpt_wp_args` | filtr | Nadpisz argumenty rejestracji CPT |
| `firefighter_stats_cat_tax_wp_args` | filtr | Nadpisz argumenty rejestracji taksonomii kategorii |
| `firefighter_stats_tag_tax_wp_args` | filtr | Nadpisz argumenty rejestracji taksonomii tagów |

---

## Struktura plików

```
firefighter-widget/
├── firefighter-stats.php              # Punkt wejścia wtyczki
├── uninstall.php                      # Czyszczenie przy odinstalowaniu
├── readme.txt                         # Readme dla WordPress.org
├── assets/
│   ├── css/
│   │   ├── admin.css                  # Style admina i panelu szybkich akcji
│   │   └── firefighter-stats-widget.css  # Style widżetu na froncie
│   └── js/
│       ├── admin-quick-add.js         # Szybkie dodawanie z paska admina
│       ├── admin-counts.js            # Strona Szybkie Liczniki + panel widżetu
│       └── block-editor.js            # Edytor bloku Gutenberga (bez procesu budowania)
├── blocks/
│   └── emergency-list-widget/
│       └── block.json                 # Schemat bloku i atrybuty
├── inc/
│   ├── core-functions.php             # Wspólne funkcje pomocnicze
│   ├── blocks-config.php              # Rejestracja bloku
│   ├── integrations/
│   │   ├── load.php                   # Rejestracje hooków integracji
│   │   ├── elementor.php              # Widżet Elementora
│   │   ├── wpbakery.php               # Element WPBakery (vc_map)
│   │   ├── beaver-builder.php         # Moduł Beaver Buildera
│   │   ├── beaver-builder/
│   │   │   └── includes/
│   │   │       └── frontend.php       # Szablon modułu BB
│   │   └── bricks.php                 # Element Bricks
│   └── classes/
│       ├── firefighter-stats-cpt.php
│       ├── firefighter-stats-cpt-notice.php
│       ├── firefighter-stats-widget.php
│       ├── firefighter-stats-category-meta.php
│       ├── firefighter-stats-admin-counts.php
│       ├── firefighter-stats-admin-guide.php
│       ├── firefighter-stats-category-seeder.php
│       ├── firefighter-stats-permalink-settings.php
│       ├── shortcodes/
│       │   └── firefighter-stats-shortcode-emergency-list-widget.php
│       └── widgets/
│           ├── firefighter-stats-widget-emergency-list.php
│           └── firefighter-stats-widget-emergency-categories.php
├── languages/
│   ├── firefighter-stats.pot
│   ├── firefighter-stats-pl_PL.po
│   └── firefighter-stats-pl_PL.mo
└── templates/
    └── widgets/
        ├── emergency-list.php
        └── emergency-categories.php
```

---

## Współtworzenie

Zgłoszenia pull request i issues są mile widziane.

1. Forkuj repozytorium
2. Utwórz gałąź funkcji: `git checkout -b feature/moja-funkcja`
3. Zatwierdź zmiany: `git commit -m "Dodaj moją funkcję"`
4. Wypchnij: `git push origin feature/moja-funkcja`
5. Otwórz Pull Request

Prosimy o zachowanie kompatybilności z **PHP 7.4+** i nieprowadzenie procesu budowania — skrypt edytora bloku jest celowo napisany w zwykłym ES5.

---

## API Remiza.pl — Bezpośrednia integracja

Każda strona internetowa — WordPress lub inna — może wysyłać raporty o wyjazdach bezpośrednio do odbiornika statystyk [Remiza.pl](https://remiza.pl) bez instalowania tej wtyczki.

**Adres bazowy:** `https://remiza.pl/wp-json/remiza-stats/v1`
Nie wymaga uwierzytelniania WordPress. Autoryzacja odbywa się przez token strony wydawany przy rejestracji.

### 1. Zarejestruj stronę

```bash
curl -X POST https://remiza.pl/wp-json/remiza-stats/v1/register \
  -H "Content-Type: application/json" \
  -d '{"site_url":"https://twoja-osp.pl","site_name":"OSP Twoja Jednostka"}'
```

Odpowiedź `201 Created`:
```json
{
  "token": "a1b2c3d4-e5f6-7890-abcd-ef1234567890",
  "domain_label": "twoja-osp.pl"
}
```

Przechowuj token bezpiecznie — autoryzuje wszystkie przyszłe wywołania `/report`. Jeśli domena była już zarejestrowana, do `domain_label` dodawany jest sufiks (np. `twoja-osp.pl-2`), a historyczne dane są zachowane.

**Limit żądań:** 5 rejestracji na IP na godzinę.

### 2. Wyślij raport

```bash
curl -X POST https://remiza.pl/wp-json/remiza-stats/v1/report \
  -H "Content-Type: application/json" \
  -d '{
    "token": "a1b2c3d4-e5f6-7890-abcd-ef1234567890",
    "event": "new_emergency",
    "post_title": "Pożar budynku mieszkalnego w Łagiszu",
    "post_url": "https://twoja-osp.pl/aktualnosci/pozar-2026-03-11",
    "post_excerpt": "Jednostka została zadysponowana do pożaru...",
    "category_slug": "pozar",
    "category_name": "Pożar",
    "category_icon": "🔥",
    "emergency_date": "2026-03-11",
    "reported_at": "2026-03-11T17:45:00+01:00"
  }'
```

Odpowiedź `200 OK`:
```json
{ "status": "ok" }
```

### Pola raportu

| Pole | Wymagane | Opis |
|------|----------|------|
| `token` | **Tak** | Token z `/register` |
| `post_title` | **Tak** | Tytuł wpisu o wyjeździe |
| `post_url` | **Tak** | Pełny URL — wyświetlany jako link w panelu admina odbiornika |
| `emergency_date` | **Tak** | Data w formacie `RRRR-MM-DD` lub ISO 8601 |
| `event` | Nie | Typ zdarzenia (domyślnie: `new_emergency`) |
| `post_excerpt` | Nie | Krótki fragment treści wpisu |
| `category_slug` | Nie | Slug kategorii (np. `pozar`) |
| `category_name` | Nie | Czytelna nazwa kategorii |
| `category_icon` | Nie | Emoji (np. `🔥`) |
| `reported_at` | Nie | Znacznik czasu ISO 8601 zdarzenia |

### Kody błędów

| Status | Kod | Znaczenie |
|--------|-----|-----------|
| `400` | `missing_site_url` | `/register`: brak `site_url` |
| `400` | `missing_token` | `/report`: brak `token` |
| `401` | `invalid_token` | Token nierozpoznany lub strona nieaktywna |
| `429` | `rate_limit_exceeded` | Za dużo rejestracji (5 / IP / godzinę) |
| `500` | `registration_failed` / `storage_failed` | Błąd po stronie serwera |

---

## Historia zmian

Pełna historia zmian dostępna w pliku [CHANGELOG.md](CHANGELOG.md).

---

## Licencja

[GPL-2.0-or-later](https://www.gnu.org/licenses/gpl-2.0.html)
