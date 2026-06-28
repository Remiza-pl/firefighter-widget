=== Firefighter Statistics ===
Contributors: sync667
Tags: straż pożarna, ratownictwo, statystyki, widget, interwencje
Requires at least: 5.9
Tested up to: 6.9
Requires PHP: 7.4
Stable tag: 1.0.6
License: GPL-2.0-or-later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Rejestruj i wyświetlaj statystyki ratownicze dla jednostek straży pożarnej. Zawiera widżety, blok Gutenberga i shortcode.

== Opis ==

**Firefighter Statistics** dodaje własny typ wpisu i widżety, dzięki którym jednostki straży pożarnej mogą publikować statystyki swoich działań ratowniczych na stronie internetowej.

**Funkcje:**

* **Typ wpisu Interwencje** — twórz szczegółowe wpisy dla poszczególnych zdarzeń ratowniczych.
* **Kategorie i tagi interwencji** — organizuj zdarzenia za pomocą hierarchicznych kategorii (każda z własną ikoną i kolorem) oraz tagów.
* **Widżet Statystyki Ratownicze** — wyświetla liczniki interwencji w kategoriach (łącznie / w tym roku / w tym miesiącu) wraz z konfigurowalną listą ostatnich zdarzeń.
* **Widżet Kategorie Interwencji** — prosta lista kategorii z odnośnikami.
* **Blok Gutenberga** — przeciągnij i upuść blok „Statystyki Ratownicze" z podglądem na żywo w edytorze.
* **Shortcode** — `[firefighter_stats_emergency_list_widget]` do użytku w klasycznym edytorze i kreatorach stron.
* **Szybkie liczniki (Quicklog)** — rejestruj interwencje zbiorczo bez tworzenia osobnych wpisów. Idealne dla rutynowych zdarzeń, które nie wymagają pełnej dokumentacji.
* **Przycisk na pasku administratora** — dodaj interwencję jednym kliknięciem bezpośrednio z paska narzędzi WordPressa.
* **Niestandardowe ścieżki URL** — zmień bazę adresów dla interwencji, kategorii i tagów w **Ustawienia > Bezpośrednie odnośniki**.
* **W pełni przetłumaczony** — tłumaczenie polskie (pl_PL) w zestawie; wszystkie ciągi używają domeny tekstowej `firefighter-stats`.

== Instalacja ==

1. Wgraj folder wtyczki do katalogu `/wp-content/plugins/` lub zainstaluj bezpośrednio z ekranu Wtyczki w panelu WordPressa.
2. Aktywuj wtyczkę na ekranie **Wtyczki**.
3. Przejdź do **Interwencje > Kategorie interwencji** i utwórz pierwsze kategorie (np. Pożar, Ratownictwo medyczne, Wypadek drogowy).
4. Opcjonalnie przypisz ikonę i kolor do każdej kategorii.
5. Dodaj widżet **Statystyki Ratownicze** do paska bocznego, wstaw blok Gutenberga lub użyj shortcode'u na dowolnej stronie.

== Często zadawane pytania ==

= Jak dodać kategorie interwencji? =

Przejdź do **Interwencje > Kategorie interwencji** w panelu WordPressa. Każda kategoria obsługuje własną ikonę (wybraną z predefiniowanego zestawu) oraz kolor tła widoczny w widżetach i listach wpisów.

= Czy mogę rejestrować interwencje bez tworzenia pełnych wpisów? =

Tak. Użyj przycisku **🚨 Szybka interwencja** na pasku administratora lub przejdź do **Interwencje > Szybkie liczniki**, aby dodawać liczniki pogrupowane według kategorii i daty — bez konieczności tworzenia osobnego wpisu dla każdego zdarzenia.

= Jak wyświetlić statystyki na stronie? =

Dostępne są trzy metody:

1. **Widżet** — przejdź do **Wygląd > Widżety** i dodaj *Statystyki Ratownicze* do dowolnego paska bocznego.
2. **Shortcode** — wstaw `[firefighter_stats_emergency_list_widget]` do dowolnego wpisu lub strony.
3. **Blok Gutenberga** — wyszukaj *Statystyki Ratownicze* w wyszukiwarce bloków w edytorze.

= Jakie atrybuty shortcode są dostępne? =

Wszystkie ustawienia widżetu dostępne są jako atrybuty shortcode. Najważniejsze opcje:

* `title` — nagłówek widżetu
* `show_category_summary` — `true`/`false` (pokaż podsumowanie kategorii)
* `category_time_period` — `all` (łącznie), `year` (w tym roku), `month` (w tym miesiącu)
* `show_posts_list` — `true`/`false` (pokaż listę wpisów)
* `limit` — liczba wpisów do wyświetlenia (domyślnie `5`)
* `order` — `default`, `date_desc`, `date_asc`, `title_asc`, `title_desc`, `random`
* `show_date` — `true`/`false` (pokaż datę)
* `show_category` — `true`/`false` (pokaż kategorię)
* `more_label` — etykieta odnośnika „więcej"; zostaw puste, aby go ukryć
* `selected_categories` — ID kategorii oddzielone przecinkami; filtruje podsumowanie do wybranych kategorii

Przykład użycia:
`[firefighter_stats_emergency_list_widget title="Nasze działania" category_time_period="year" limit="10"]`

= Czy mogę zmienić ścieżki URL interwencji? =

Tak. Przejdź do **Ustawienia > Bezpośrednie odnośniki** i znajdź sekcję **Firefighter Stats** na dole strony. Możesz tam ustawić własne człony URL dla interwencji, kategorii i tagów.

= Czy wtyczka jest przetłumaczona na język polski? =

Tak. Tłumaczenie polskie (`pl_PL`) jest dołączone do wtyczki. Wszystkie ciągi tekstowe używają domeny `firefighter-stats`.

= Adresy URL interwencji pokazują błąd 404. Jak to naprawić? =

Przejdź do **Interwencje > Szybkie liczniki** i kliknij przycisk **Napraw adresy URL interwencji**. Spowoduje to odświeżenie reguł przepisywania adresów permalinków.

== Zrzuty ekranu ==

1. Widżet Statystyki Ratownicze na stronie frontendowej.
2. Zarządzanie kategoriami interwencji z wyborem ikony i koloru.
3. Strona administratora Szybkie liczniki do masowego wpisywania danych.
4. Przycisk Szybka interwencja na pasku narzędzi administratora.
5. Blok Gutenberga z podglądem renderowania po stronie serwera.

== Dziennik zmian ==

Pełna historia zmian dostępna w pliku [CHANGELOG.md](https://github.com/OSP-Lagisza/firefighter-stats/blob/main/CHANGELOG.md).

== API Remiza.pl — Bezpośrednia integracja ==

Każda strona — WordPress lub inna — może wysyłać raporty o wyjazdach bezpośrednio do odbiornika statystyk Remiza.pl bez instalowania tej wtyczki. Nie jest wymagane uwierzytelnianie WordPress.

**Adres bazowy:** `https://remiza.pl/wp-json/remiza-stats/v1`

= Krok 1: Zarejestruj stronę =

Wyślij żądanie POST z adresem URL i nazwą strony, aby otrzymać unikalny token:

    POST /register
    {"site_url":"https://twoja-osp.pl","site_name":"OSP Twoja Jednostka"}

    Odpowiedź: {"token":"a1b2c3d4-...","domain_label":"twoja-osp.pl"}

Przechowuj token bezpiecznie — autoryzuje wszystkie przyszłe raporty. Limit: 5 rejestracji na IP na godzinę.

= Krok 2: Wysyłaj raporty =

    POST /report
    {
        "token": "a1b2c3d4-...",
        "post_title": "Pożar budynku mieszkalnego",
        "post_url": "https://twoja-osp.pl/aktualnosci/pozar-2026-03-11",
        "category_slug": "pozar",
        "category_name": "Pożar",
        "category_icon": "🔥",
        "emergency_date": "2026-03-11"
    }

Wymagane pola: `token`, `post_title`, `post_url`, `emergency_date`. Pozostałe pola są opcjonalne.

== Informacja o aktualizacji ==

= 1.0.0 =
Pierwsze wydanie.
