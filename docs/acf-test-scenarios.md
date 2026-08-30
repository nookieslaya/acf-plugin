# ACF Schema Guard - scenariusze testowe

Ten motyw jest środowiskiem developerskim dla przyszłej wtyczki ACF Schema
Guard. Nie jest przeznaczony do użycia produkcyjnego.

## Uruchomienie środowiska

1. Włącz ACF PRO i motyw `ACF Schema Guard Dev`.
2. Otwórz **Custom Fields > Field Groups**. ACF automatycznie wykrywa pliki z
   `wp-content/themes/acf-schema-guard-dev/acf-json/`.
3. Gdy ACF pokaże zakładkę **Sync available**, zsynchronizuj grupy. Nie dodawaj
   własnego filtra `acf/settings/save_json` ani `acf/settings/load_json`: dla
   aktywnego motywu katalog `acf-json/` jest domyślnym punktem Local JSON.
4. Utwórz lub edytuj stronę, uzupełnij pola i ustaw ją jako stronę główną.
5. Otwórz stronę na froncie. Komponenty Hero, Features, Card i Flexible Content
   stanowią kontrolowane przykłady referencji ACF w PHP.

## Mapa fixtures

| Fixture | Pola lub layouty | Cel |
| --- | --- | --- |
| Hero | `hero_title`, `hero_text`, `hero_image`, `hero_cta` | usunięcie i zmiana formatu obrazu |
| Card | `card_title`, `card_text`, `card_image`, `card_link` | proste pola top-level |
| Features | `features` z `feature_title`, `feature_text`, `feature_icon` | repeater i zagnieżdżone pola |
| Flexible Content | `hero`, `text_section`, `cards` | layouty i zagnieżdżony repeater |
| Test Settings | Options Page ACF PRO | opcjonalne pola globalne |

## Scenariusze breaking changes

### 1. Zmiana nazwy obrazu hero

**Before:** `hero_image`  
**After:** `hero_background`

**Expected:** `CRITICAL` / breaking change.

Raport powinien wykazać usunięcie `hero_image`, dodanie `hero_background` i
referencję w `template-parts/acf/hero.php`. Nie należy automatycznie zakładać,
że jest to rename, ale ryzyko musi być krytyczne, ponieważ kod wciąż odwołuje się
do poprzedniej nazwy.

### 2. Zmiana typu tytułu hero

**Before:** `hero_title: text`  
**After:** `hero_title: textarea`

**Expected:** `HIGH` / type change.

Nazwa pola pozostaje taka sama, jednak format danych oraz zachowanie edytora
mogą zmienić oczekiwania template parts.

### 3. Dodanie pola opcjonalnego

**Add:** `hero_subtitle` jako opcjonalne pole tekstowe.

**Expected:** `SAFE`.

Dodanie pola bez zmiany istniejącego kontraktu nie powinno blokować deployu.

### 4. Usunięcie pola repeatera

**Before:** `features.feature_icon`  
**After:** pole `feature_icon` usunięte.

**Expected:** `CRITICAL`.

To jest zmiana w strukturze zagnieżdżonej. Kod w
`template-parts/acf/features.php` nadal wywołuje `get_sub_field( 'feature_icon' )`.

### 5. Zmiana formatu zwracanego obrazu

**Before:** `hero_image.return_format: array`  
**After:** `hero_image.return_format: id`

**Expected:** `HIGH`.

Template oczekuje tablicy z kluczami `url` i `alt`. Identyfikator załącznika
nie spełnia tego kontraktu.

### 6. Usunięcie layoutu Flexible Content

**Before:** layout `cards` w `page_sections`  
**After:** layout `cards` usunięty.

**Expected:** `CRITICAL`.

Usunięcie layoutu narusza zagnieżdżoną strukturę danych i może unieważnić treść
istniejących stron oraz obsługę w `template-parts/acf/flexible-content.php`.

## Ręczna weryfikacja po zmianie fixtures

1. Sprawdź **Custom Fields > Field Groups** i zsynchronizuj zmienione JSON.
2. Uzupełnij stronę testową danymi dla Hero, Features i Card.
3. Dodaj każdy layout `page_sections`, w tym kilka elementów nested `cards`.
4. Otwórz frontend i sprawdź, czy każda sekcja renderuje się bez ostrzeżeń PHP.
5. Otwórz **Schema Guard Test** i potwierdź dostępność opcyjnych pól ACF PRO.
