# DateifabrikP24DisposalFee

Plugin zur Berechung der Verpackungskosten für Packing24

***

### Brain Storm

- grundsätzliches
    - Daten sollten auch später noch in der Bestellübersicht abrufbar sein
    - aufgeschlüsselt nach Material (erleichtert die spätere Auswertung)

- Basket:
    - Anzahl Artikel
    - Gewicht des jeweiligen Artikels (NOT NULL)
    - Material (NOT NULL)

- Config
    - eigene Tabelle für Materialkosten
    - Materialkosten können per Plugin-Konfiguration aktualisiert werden

- Berechnung 1:
    - [Anzahl Artikel] x [Gewicht des jeweiligen Artikels] x Kosten für dieses [Material]

- Template
    - checkout/confirm
    - anklickbar: ja, ich will
    - Verpackungskosten als Artikel hinzufügen (reicht das, um es in der Bestellung zu haben)

- Session
    - Daten der Session hinzufügen


