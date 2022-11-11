<?php

namespace DateifabrikP24DisposalFee\Subscriber;

use Enlight\Event\SubscriberInterface;

class BasketData implements SubscriberInterface
{

    // set license fee ordernumbers
    protected $alleLizenzArtikelOrdernumbers = array(15003, 15004);

    public static function getSubscribedEvents()
    {
        return [
            'Enlight_Controller_Action_PreDispatch_Frontend_Checkout' => 'onPreDispatchCheckout',
            //'Enlight_Controller_Action_PostDispatchSecure_Frontend_Checkout' => 'onPostDispatchCheckout',
            'Shopware_Modules_Basket_getPriceForUpdateArticle_FilterPrice' => 'onBasketUpdatePrice',
        ];
    }

    public function onPreDispatchCheckout(\Enlight_Event_EventArgs $args){


        ///////////////////////////////////////////////////////////////////////
        // ToDo
        // Wenn der letzte Artikel mit vorhandenem Material entfernt wird,
        // muss der Warenkorb gelöscht werden und die Option wieder auf NULL gesetzt werden

        // nur bei Rechnungsadresse (countryId) Deutschland (2) ausführen
        $countryId = $this->getCountryId();
        if($countryId == 2){

            $subject = $args->getSubject();
            $action = $subject->request()->getQuery('action');
            $view = $subject->View();        

            // Formular anzeigen und Optionsänderung überwachen nur nur für Action = confirm        
            if($action == 'confirm')
            {

                // Formular nur auf confirm-Seite anzeigen, wenn countryId = 2 (Deutschland)
                $view->assign('countryId', $countryId);         

                // Zuweisung der neuen Option bei Wechsel
                // function will be executed only when license fee option is set and has changed
                $licenseFeeOption = $this->getSessionOption();
                if(isset($licenseFeeOption)){

                    switch($licenseFeeOption){
                        case 1:
                            $view->assign('selected1', 'selected="selected"');
                            $view->assign('selected2', '');
                            break;
                        case 2:
                            $view->assign('selected1', '');
                            $view->assign('selected2', 'selected="selected"');
                            break;
                    }

                    $this->setSessionOption($licenseFeeOption);
                }

            }

        } 

    }


    // is thrown on every checkout action
    public function onBasketUpdatePrice(\Enlight_Event_EventArgs $args){

        // nur bei deutscher Rechnungsadresse ausführen
        $countryId = $this->getCountryId();
        if($countryId == 2){

            $licenseFeeOption = $this->getSessionOption();

            if(isset($licenseFeeOption)){

                // option 1 => Ja
                // option 2 => Nein                
                switch($licenseFeeOption){
                    case 1:
                    // interne Funktion aufrufen, Lizenzartikel aktualisieren (neu / nicht mehr vorhanden / Anzahl geändert)  
                    $this->disposalFeeOrdernumberOfMaterialAndPrice();
// 
// 
// 
// 
// 
// 
// 
// 
// 
// 


                        break;                      
                    case 2:
                    // interne Funktion aufrufen, alle Lizenzartikel aus Basket löschen
                        $this->deleteAllLicenseArticlesFromBasket($this->getBasketData());
                        break;
                }


                //////////////////////////////////////////////////
                //ToDo
                // Funktion wird für onPreDispatchCheckout UND beim Hinzufügen/Anzahl ändern von Artikeln benötigt

                $basket = $args->getReturn();
                if($basket['ordernumber'] == 15003){
                    // set new price for disposal fee article
                    $basket['price'] = 1000 / 1.19; 
                }
                // return new values
                $args->setReturn($basket);                

            }        

        }
        

/*         $basketData = Shopware()->Modules()->Basket()->sGetBasketData();
        dump($basketData['content'][0]['id']);

        $session = Shopware()->Session()->get('sessionId');
        //dump($session);

        $basket = $args->getReturn();    
        //dump($basket);
        if($session == 888 AND $basket['ordernumber'] == 11012){
            // set new price for disposal fee article
            $basket['price'] = 1000 / 1.19; 
        }

        // return new values
        $args->setReturn($basket); */

    }




    ################################################################################################
    // helper functions

    public function disposalFeeOrdernumberOfMaterialAndPrice(){

        $basketData = [];
        $collectedContentData = [];
        // get data from basket
        $basketData = $this->getBasketData();

        foreach($basketData['content'] as $content){

            $collectedContentData[] = [
                'basketId'              => $content['id'],
                'articleId'             => $content['articleID'],
                'ordernumber'           => $content['ordernumber'],
                'quantity'              => $content['quantity'],                
                'price'                 => $content['netprice'],
                'tax_rate'              => $content['tax_rate'],
                'p24_license_weight'    => $content['additional_details']['p24_license_weight'],
                'p24_license_material'  => $content['additional_details']['p24_license_material'],
                // used by materialHelper(), if p24_license_weight / p24_license_material empty or null
                'p24_gewicht'          => $content['additional_details']['p24_gewicht'],
                'p24_material'         => $content['additional_details']['p24_material'],
            ];

        }

        // get calculated disposal fee price(s) and ordernumber(s) for the given materials
        $disposalFeeOrdernumberOfMaterialAndPrice = $this->calculateDisposalFeePrice($collectedContentData);
        dump($disposalFeeOrdernumberOfMaterialAndPrice);

    }    


    // here we have to calculate the price(s) for the ordernumber(s) of all given material(s)
    public function calculateDisposalFeePrice($collectedContentData){

        $calculatedDisposalFeePrice = array();

        // ToDo: ALL relevant fields must be not empty
        // but if one of them is empty, return NULL and/or leave calculating        
        foreach($collectedContentData as $calcData){

            if(empty($calcData['p24_license_material']) OR $calcData['p24_license_material'] == NULL){
                //dump($calcData['p24_material']);
                $material = $this->materialHelper($calcData['p24_material']);
            }
            else{
                $material = $calcData['p24_license_material'];
            }

            // if material exists...
////////////////////////////////////////////////////////////////////////////////////////// 08.11.2022            
            $calculatedDisposalFeePrice[$calcData['ordernumber']] = $calcData['price'];            


            // else break here

            //dump($material);
            
        }

        return $calculatedDisposalFeePrice;

    }    

    public function addOrUpdateLicenseFeeArticles($args){

        // wenn action = confirm
        // case 1: noch kein lizenartikel im wk
        // füge alle benötigten lizenzartikel hinzu
        // case 2: es ist bereits mindestens ein lizenzartikel im wk
        // update alle lizenzartikel

        // wenn basket leer muss auch applyLicenseFeeOption auf 0 sein

        $option = Shopware()->Session()->offsetGet('applyLicenseFeeOption');
        dump($option);
        //die();

    }   
    
    public function setSessionOption($licenseFeeOption){
    
        Shopware()->Session()->offsetSet('applyLicenseFeeOption', $licenseFeeOption);

    }

    public function getSessionOption(){

        return Shopware()->Session()->offsetGet('applyLicenseFeeOption');    

    }

    public function getCountryId(){

        return Shopware()->Modules()->Admin()->sGetUserData()['billingaddress']['countryId'];    

    }

    public function getBasketData(){

        return Shopware()->Modules()->Basket()->sGetBasketData();

    }
    public function deleteAllLicenseArticlesFromBasket($basket){

        foreach($basket['content'] as $content){
            if(in_array($content['ordernumber'], $this->alleLizenzArtikelOrdernumbers)){
                $basketIDForDeleting = $content['id'];
                Shopware()->Modules()->Basket()->sDeleteArticle($basketIDForDeleting);
            }
        }        

    }
    

    public function materialHelper($material){
        
        $alu = [];
        $cardboard = [
            'Karton',
            'Kraft / PLA',
            'Kraftpapier 50g / AL7 / CPP60',
            'Kraftpapier 50g / PET12 / CPP40',
            'PA / PE',
            'Papier',
            'Papier / PE',
            'Papier / Pergament-Ersatz',
            'Pappe',
            'Pappe / PE',
            'Pappe / PLA',
            'Pappe / PP',
        ];
        $otherMaterial = [
            'Bambus',
            'Biokunststoff ( PLA )',
            'Birke',
            'CPLA',
            'CPLA (Polymilchsäure)',
            'Holz',
            'Holz / Zellstoff',
            'Mono-PP',
            'Palmblatt',
            'PLA',
            'PLA ( Bio-Kunststoff )',
            'Vlies',
            'Zellulose / Maisstärke',
            'Zellulose / PLA',
            'Zuckerrohr',
        ];
        $plastic = [
            'HDPE',
            'Kunststoff ( LDPE )',
            'Kunststoff ( PET )',
            'Kunststoff ( PP )',
            'Kunststoff ( PS )',
            'Latex',
            'Nitril',
            'OPP25 / CPP40',
            'OPPmatt19/AL7/PE110',
            'PET',
            'PET12 / AL7 / LDPE110',
            'PET12 / LDPE110',
            'PET12/AL7/LDPE110',
            'Plastik',
            'Plastik ( PET )',
            'Plastik ( PP )',
            'Plastik ( PS )',
            'Plastik (PS)',
            'PP',
            'pp ( Polypropylen )',
            'PS ( Polystyrol )',
            'rPET',
            'Styropor',
        ];

        if(in_array($material, $alu)){
            $material = 'alu';
        }
        if(in_array($material, $cardboard)){
            $material = 'cardboard';
        }
        if(in_array($material, $otherMaterial)){
            $material = 'other_material';
        }        
        if(in_array($material, $plastic)){
            $material = 'plastic';
        }        

        return $material;

    }    


}