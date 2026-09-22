# Przewodnik użytkownika ACF Schema Guard

Jeśli korzystasz z wtyczki pierwszy raz, zacznij od
[szybkiego startu](quick-start-pl.md). Ten przewodnik opisuje także pracę
zespołową, Git i ustawienia release.

## Podstawowy proces

1. W **History** utwórz snapshot poprawnego schematu, poproś o review i ustaw
   go jako baseline dopiero po zatwierdzeniu review.
2. Zmień oraz zapisz pola ACF.
3. Otwórz **Changes**. Plugin porównuje baseline z bieżącym schematem.
4. Przed wdrożeniem sprawdź severity, referencje w kodzie i wpływ na dane.

## Interpretacja wyników

`Safe` nie wymaga oczekiwanej akcji. `Warning` wymaga review. `High` może
uszkodzić istniejący kod lub użycie danych. `Critical` należy rozwiązać przed
release. Pierwotne ryzyko pozostaje widoczne także przy aktywnym wyjątku Pro.

## Zdrowie źródeł i kodu

**Field Groups** porównuje definicje bazy z ACF Local JSON. W Solo Mode Local
JSON jest opcjonalny, a plugin korzysta z procesu database-first. **Code Usage**
pokazuje wspierane literalne wywołania PHP ACF. **Unused Fields** to wyłącznie
sygnał do review: wywołania dynamiczne lub nieskanowane foldery oznaczają, że
nie można uznać pola za bezpieczne do usunięcia.

## Wpływ na dane

Dla zmienionych nazw lub usuniętych pól Changes może pokazać ograniczoną listę
pasujących rekordów. Plugin nigdy nie odczytuje wartości pól. Dane bezpośrednie
i wspierane struktury zagnieżdżone są dowodem, nie pełnym planem migracji.

## Safe Rename Assistant

Gdy bezpośrednie pole ACF zmieni nazwę, **Changes** może pokazać plan Safe
Rename Assistant. Łączy on starą i nową nazwę, literalne wywołania PHP oraz
read-only dry-run zakresu bezpośrednich danych post-meta. Dry-run pokazuje
rekordy używające starego klucza, rekordy mające już nowy klucz i wymagające
ręcznego rozstrzygnięcia konfliktu oraz rekordy wymagające późniejszej decyzji
o migracji.

Nie odczytuje wartości pola i nie zmienia danych. Dla zagnieżdżonych pól ACF
wyświetla ograniczenie zamiast niewiarygodnego oszacowania migracji.

## Git i próg release

Dodaj do Git `acf-schema-baseline.json`, aby współdzielić baseline, oraz
opcjonalnie `acf-schema-guard-policy.json`, aby współdzielić próg release.
Polityka z repozytorium ma priorytet nad ustawieniem lokalnym.

### Baseline zespołowy krok po kroku

Baseline z panelu **History** jest lokalny: zapisuje się w bazie konkretnej
instalacji WordPressa. Wspólnym punktem odniesienia dla zespołu i CI jest
wersjonowany plik JSON. Nie tworzy się on ani nie aktualizuje automatycznie,
ponieważ automatyczne nadpisanie mogłoby ukryć niezaakceptowaną zmianę schematu.

Po zatwierdzeniu baseline możesz pobrać ten plik bez terminala: w **History**
odszukaj sekcję **Team baseline**, sprawdź ID snapshotu i czas utworzenia, a
następnie kliknij **Download baseline JSON**. Zapisz plik jako
`acf-schema-baseline.json` w wersjonowanym motywie lub pluginie. WordPress nie
zapisuje go sam w repozytorium i nie wykonuje poleceń Git.

Alternatywnie możesz utworzyć ten sam plik przez WP-CLI. Po review i akceptacji
uruchom polecenia w powłoce Local, z katalogu zawierającego `wp-config.php`.
Gdy do Gita trafia tylko motyw, przechowuj plik w jego katalogu:

```bash
wp acf-schema-guard baseline export \
  wp-content/themes/your-theme/acf-schema-baseline.json

git add wp-content/themes/your-theme/acf-json/
git add wp-content/themes/your-theme/acf-schema-baseline.json
git commit -m "chore: approve ACF schema baseline"
```

Przy następnej świadomie zaakceptowanej zmianie istniejący plik wymaga jawnej
zgody na nadpisanie:

```bash
wp acf-schema-guard baseline export \
  wp-content/themes/your-theme/acf-schema-baseline.json \
  --force

wp acf-schema-guard baseline check \
  wp-content/themes/your-theme/acf-schema-baseline.json \
  --fail-on-breaking
```

`--force` jest zabezpieczeniem: używaj go dopiero po sprawdzeniu Changes,
referencji w kodzie oraz wpływu na dane. Następnie commit i `git push` przekazują
ten sam baseline każdemu członkowi zespołu po `git pull`.

## Status wersji

W wersji 1.0 wszystkie obecnie dostępne funkcje są darmowe w okresie walidacji
produktu. Lokalny podgląd wersji pozostaje wyłącznie przygotowaniem do
przyszłego licencjonowania i nie ukrywa obecnych funkcji. Przyszła wersja
komercyjna może objąć konfigurację progu release, audytowalne tymczasowe
wyjątki i eksport raportów. Raport Markdown lub JSON pobierzesz w **Changes**,
a WP-CLI pozostaje ścieżką CI. Szablon Markdown może zawierać markery:

```md
<!-- acf-schema-guard:summary -->
<!-- acf-schema-guard:findings -->
```
## Praca zespołowa

Zakładka **History** zawiera lokalną kolejkę review. Osoba zgłaszająca opisuje,
co ma zostać sprawdzone, a reviewer zapisuje decyzję: zatwierdzenie albo
odrzucenie, wraz z notatką i czasem. Snapshoty są niezmienne; kolejne zgłoszenie
po zakończonej decyzji pozostaje widoczne w lokalnej historii audytowej. Nie jest
to zewnętrzna usługa akceptacji.
