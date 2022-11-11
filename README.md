# DateifabrikP24DisposalFee

Plugin zur Berechung der Verpackungskosten für Packing24

***

### Beispiele zur Orientierung

- [biologischverpacken.de](https://www.biologischverpacken.de/) *CTRL+Click*
- [verpacken24.com/shop](https://www.verpacken24.com/shop/) *CTRL+Click*

### Vorbereitungen

- Hersteller anlegen
- Produkte anlegen mit eigener Artikelnummer (Entsorgungskosten [Papier/Karton, Aluminium, sonstige Materialien *(Naturmaterialien)*, Kunststoff, Verbundstoffe])
- Datenbank Materialien vereinheitlichen, Freitextfeld mit select?
- p24_* Felder checken, ob alle in der Form belegt sind und benötigt werden

### Case 1: Erstaufruf checkout/confirm

- Option ist NULL
- select wird betätigt und auf 1 gestellt
- Controller ändert den Wert in der session auf 1 und leitet an BasketData
- **ToDo:** _if($action == 'confirm' AND $countryId == 2) UND Artikel mit Lizenzmaterial im Basket sind..._
- BasketData prüft, ob sich die Option geändert hat, falls ja, setzt onPreDispatchCheckout licenseFeeOptionBefore und licenseFeeOption auf 1
- 

