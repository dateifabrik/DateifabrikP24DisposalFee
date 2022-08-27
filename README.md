# DateifabrikP24DisposalFee

Plugin zur Berechung der Verpackungskosten für Packing24

***

### Brain Storm

- grundsätzliches
    - Daten sollten auch später noch in der Bestellübersicht abrufbar sein
    - Tabelle mit Materialarten    
    - Tabelle mit Verweis auf Kunden-ID, Bestellnummer, Felder mit ID für Materialart, Gesamtkosten in dieser Bestellung

- Basket:
    - Anzahl Artikel
    - Gewicht des jeweiligen Artikels (NOT NULL)
    - Material (NOT NULL)

- Config
    - eigene Tabelle für Materialkosten
    - Materialkosten können per Plugin-Konfiguration aktualisiert werden

- Berechnung:
    - [Anzahl Artikel] x [Gewicht des jeweiligen Artikels] x Kosten für dieses [Material]
    - als Artikel mit Gesamtpreis hinzufügen (reicht das, um es in der Bestellung zu haben???)

- Template
    - checkout/confirm
    - anklickbar: ja, ich will
    - Ausgabe der Verpackungskosten als Artikel

- Session
    - Daten der Session hinzufügen

### Beispiele zur Orientierung

- [biologischverpacken.de](https://www.biologischverpacken.de/) *CTRL+Click*
- [verpacken24.com/shop](https://www.verpacken24.com/shop/) *CTRL+Click*

### Vorbereitungen

- Hersteller anlegen
- Produkte anlegen mit eigener Artikelnummer (Entsorgungskosten [Papier/Karton, Aluminium, sonstige Materialien *(Naturmaterialien)*, Kunststoff, Verbundstoffe])
- Datenbank Materialien vereinheitlichen, Freitextfeld mit select?
- p24_* Felder checken, ob alle in der Form belegt sind und benötigt werden

