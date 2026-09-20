# ACF Schema Guard — szybki start

Ta instrukcja prowadzi przez pierwsze użycie wtyczki w panelu WordPressa.
Nie wymaga znajomości kodu, Gita ani terminala.

## Co robi wtyczka?

ACF Schema Guard zapamiętuje bezpieczną wersję ustawień pól ACF, a później
pokazuje, co zmieniło się w polach i czy zmiana może wpłynąć na stronę, kod lub
zapisane treści. Wtyczka nie zmienia samodzielnie pól ACF, wpisów ani danych.

## Zanim zaczniesz

1. Zaloguj się do WordPressa jako administrator.
2. Upewnij się, że **Advanced Custom Fields** i **ACF Schema Guard** są aktywne
   w **Wtyczki → Zainstalowane wtyczki**.
3. W lewym menu otwórz **ACF Schema Guard**.

## Pierwsze bezpieczne ustawienie baseline

Baseline to zapamiętany punkt odniesienia. Możesz myśleć o nim jak o zdjęciu
ustawień ACF z chwili, w której wszystko działa poprawnie.

1. Otwórz **ACF Schema Guard → History**.
2. Kliknij **Capture current schema**. Wtyczka zapisze snapshot, czyli
   niezmienną kopię bieżących ustawień pól.
3. Przy nowym snapshotcie rozwiń **Request review**.
4. Wpisz prostą notatkę, na przykład: „Sprawdzone na stronie testowej — pola
   działają poprawnie.”, a następnie kliknij **Request review**.
5. Rozwiń **Record decision**.
6. Wybierz **Approve**, wpisz uzasadnienie, na przykład: „Zatwierdzone po
   sprawdzeniu formularzy i strony głównej.”, i kliknij **Save decision**.
7. Kliknij **Set approved baseline**.

Od tej chwili wtyczka porównuje bieżące pola ACF z zatwierdzonym baseline.

> Gdy pracujesz samodzielnie, możesz wykonać zgłoszenie i zatwierdzenie na tym
> samym koncie. W zespole lepiej, aby druga osoba zapisała decyzję review.

## Pierwszy test zmiany

Na początek wykonaj łagodny, odwracalny test:

1. Otwórz **ACF → Field Groups**.
2. Wybierz dowolną testową grupę pól.
3. Zmień tylko **Field Label** jednego pola, np. „Tytuł” na „Tytuł testowy”.
4. Zapisz grupę pól.
5. Otwórz **ACF Schema Guard → Changes**.

Zobaczysz zmianę z opisem tego, co zostało zmienione. Zmiana samej etykiety
zwykle ma poziom **Warning** — warto ją sprawdzić, ale nie oznacza od razu, że
strona przestanie działać.

Po teście przywróć poprzednią etykietę i zapisz grupę ponownie. Widok
**Changes** powinien wrócić do stanu bez zmian.

## Jak czytać poziomy ryzyka

| Poziom | Co oznacza | Co zrobić |
| --- | --- | --- |
| Safe | Zmiana zwykle nie psuje istniejącego działania. | Sprawdź ją przed publikacją. |
| Warning | Zmiana wymaga świadomego sprawdzenia. | Obejrzyj opis i przetestuj stronę. |
| High | Kod lub istniejące dane mogą przestać pasować. | Sprawdź Code Usage i popraw zależne szablony. |
| Critical | Usunięto ważne pole lub grupę pól. | Nie wdrażaj zmiany bez naprawy i testu. |

## Gdzie szukać pomocy w panelu

- **Overview** — krótki stan bezpieczeństwa: baseline, zmiany i oczekujące
  review.
- **Changes** — najważniejszy widok po zmianie pól; pokazuje różnice, użycie w
  kodzie oraz możliwy wpływ na dane.
- **History** — snapshoty, review i ustawienie zatwierdzonego baseline.
- **Code Usage** — miejsca w plikach PHP, w których używane jest dane pole.
- **Field Groups** — porównanie definicji w bazie WordPressa i plikach ACF Local
  JSON, jeśli projekt ich używa.
- **Settings** — wybór katalogów, które wtyczka ma przeszukiwać w poszukiwaniu
  użycia pól w kodzie.

## Co robić na co dzień

1. Przed większą zmianą upewnij się, że masz zatwierdzony baseline.
2. Zmień pola ACF i zapisz je.
3. Otwórz **Changes**.
4. Jeśli widzisz High lub Critical, sprawdź opis, **Code Usage** i wpływ na dane.
5. Przetestuj odpowiednią stronę lub formularz.
6. Dopiero gdy zmiana jest świadomie zaakceptowana, utwórz nowy snapshot,
   wykonaj review i ustaw nowy baseline.

## Praca w zespole — opcjonalnie

Snapshoty i review w panelu są lokalne dla jednej instalacji WordPressa. Aby
zespół oraz CI używali tego samego punktu odniesienia, po zatwierdzeniu zmiany
eksportuje się ręcznie plik `acf-schema-baseline.json` i dodaje go do Gita.

Dokładne komendy i przykład dla wersjonowanego motywu znajdziesz w
[polskim przewodniku użytkownika](user-guide-pl.md#baseline-zespołowy-krok-po-kroku).
