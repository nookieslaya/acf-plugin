# Przewodnik użytkownika ACF Schema Guard

## Podstawowy proces

1. W **History** utwórz snapshot poprawnego schematu i ustaw go jako baseline.
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

## Praca zespołowa

Dodaj do Git `acf-schema-baseline.json`, aby współdzielić baseline, oraz
opcjonalnie `acf-schema-guard-policy.json`, aby współdzielić próg release.
Polityka z repozytorium ma priorytet nad ustawieniem lokalnym.

## Proces Pro

Pro pozwala ustawić próg release, tworzyć audytowalne tymczasowe wyjątki i
eksportować raporty. Wyjątki zachowują oryginalny finding, wygasają lub mogą
zostać cofnięte. Raport Markdown lub JSON pobierzesz w **Changes**, a WP-CLI
pozostaje ścieżką CI. Szablon Markdown może zawierać markery:

```md
<!-- acf-schema-guard:summary -->
<!-- acf-schema-guard:findings -->
```
