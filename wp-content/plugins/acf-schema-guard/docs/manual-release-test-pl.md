# Ręczne testy release

Wykonaj test w lokalnym WordPressie przed wydaniem wersji.

## Free i Local JSON

1. Otwórz **Overview** i potwierdź brak ostrzeżeń PHP.
2. W **History** utwórz baseline poprawnego schematu.
3. Zmień typ lub nazwę testowego pola ACF i otwórz **Changes**.
4. Sprawdź severity, szczegóły zmiany, Code Impact oraz Stored Data Impact.
5. Otwórz **Field Groups**, a następnie **Code Usage** i **Unused Fields**.
6. Przywró pole do stanu bazowego i potwierdź brak findingów.

## Solo Mode

1. Użyj instalacji bez skonfigurowanego Local JSON.
2. Potwierdź w Overview i Field Groups komunikat database-first, bez błędu
   Source Health.
3. Powtórz baseline i Changes.

## Pro

1. W Settings włącz lokalny podgląd Pro.
2. Zmień próg Release policy i sprawdź jego działanie w `check --fail-on-breaking`.
3. Utwórz krótki wyjątek dla konkretnego findingu, potwierdź widoczny powód,
   autora oraz możliwość cofnięcia.
4. W Changes pobierz raport Markdown i JSON. Sprawdź format oraz czy Markdown
   zawiera severity, szczegóły i uzasadnienie.
5. Dodaj `acf-schema-guard-policy.json` do repozytorium i potwierdź, że Settings
   pokazuje aktywną politykę zespołową.

## Panel

Sprawdź Overview, Changes, History, Settings, Code Usage, Field Groups i Unused
Fields na desktopie oraz telefonie. Kontrolki muszą pozostać dostępne klawiaturą,
czytelne i nie mogą wychodzić poza ekran.
