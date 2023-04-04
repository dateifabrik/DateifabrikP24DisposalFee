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

---

- Hersteller: Lizenzero
- Kategorie: Lizeenzierung
- Artikel:  
    - Hersteller Lizenzero zuordnen
    - Artikelbezeichnung, Artikelnummer:  
    'ENT-ALU-LZ',  
    'ENT-CARDBOARD-LZ',  
    'ENT-OTHER_MATERIALS-LZ',  
    'ENT-PLASTIC-LZ',  
    - Artikel aktiv  
    - Preise: Shopkunden netto 0,01  
    - Maßeinheit: Stück  

    #000A98  

    Kategorie: Lizenzierung  

    Entsorgungskosten Plastik  
    ENT-PLASTIC-LZ  
    1.01  

    Entsorgungskosten Andere Materialien  
    ENT-OTHER_MATERIALS-LZ  
    0.08  

    Entsorgungskosten Pappe/Papier/Karton  
    ENT-CARDBOARD-LZ  
    0.24  

    Entsorgungskosten Aluminium  
    ENT-ALU-LZ  
    0.84  

---

        #### engine/Shopware/Bundle/StoreFrontBundle/Gateway/ConfiguratorGatewayInterface.php
        /**
        * @inheritdoc
        */
        public function getAvailableConfigurations(Struct\BaseProduct $product): array
        {
            return $this->decoratedInstance->getAvailableConfigurations($product);    
        }    

---

### optional

- Anzeige Lizenzgebühren am Artikel
- Anzeige im OffCanvas/Basket ohne Anzahl