<?php

namespace DateifabrikP24DisposalFee\Subscriber;

use Enlight\Event\SubscriberInterface;

class BasketData implements SubscriberInterface
{

    // checkout post dispatch

    public static function getSubscribedEvents()
    {
        return [
            //'Enlight_Controller_Action_PreDispatch_Frontend_Checkout' => 'onPreDispatchCheckout',
            'Enlight_Controller_Action_PostDispatchSecure_Frontend_Checkout' => 'onPostDispatchCheckout',
            //'Shopware_Modules_Basket_getPriceForUpdateArticle_FilterPrice' => 'onBasketUpdatePrice',
        ];
    }

    public function onPreDispatchCheckout(\Enlight_Event_EventArgs $args){

        $subject = $args->getSubject();
        $action = $subject->request()->getQuery('action');
        $view = $subject->View();        
        $countryId = Shopware()->Modules()->Admin()->sGetUserData()['billingaddress']['countryId'];        

        // Formular anzeigen und Berechnungen nur für Action = confirm und Rechnungsadresse (countryId) Deutschland (2) ausführen        
        if($action == 'confirm' AND $countryId == 2)
        {

            // Formular nur anzeigen, wenn countryId = 2 (Deutschland)
            $view->assign('countryId', $countryId);         

            // Ist eine Option für Entsorgungsgebühr ausgewählt?
            // NULL = nichts ausgewählt
            $licenseFeeOptionBefore = Shopware()->Session()->offsetGet('licenseFeeOptionBefore');
            $licenseFeeOption = Shopware()->Session()->offsetGet('applyLicenseFeeOption');

            // function will be executed only when license fee option is set and has changed
            if(isset($licenseFeeOption) && $licenseFeeOption != $licenseFeeOptionBefore){

                // option 1 => Yes
                // option 2 => No
                switch($licenseFeeOption){
                    case 1:
                        $view->assign('selected1', 'selected="selected"');
                        $view->assign('selected2', '');
                        // function will be executed only when license fee option is set and has changed
                        $this->setSessionOption($licenseFeeOption);
                        break;
                    case 2:
                        $view->assign('selected1', '');
                        $view->assign('selected2', 'selected="selected"');
                        // function will be executed only when license fee option is set and has changed
                        $this->setSessionOption($licenseFeeOption);
                        break;
                }
            }

        }
    }

    public function onPostDispatchCheckout(){

        $basketData = [];
        $collectedContentData = [];
        // get data from basket
        $basketData = Shopware()->Modules()->Basket()->sGetBasketData();
        //dump($basketData);

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
        //dump($collectedContentData);

        // get calculated disposal fee price(s) and ordernumber(s) for the given materials
        $disposalFeeOrdernumberOfMaterialAndPrice = $this->calculateDisposalFeePrice($collectedContentData);
        dump($disposalFeeOrdernumberOfMaterialAndPrice);

        // store new price for disposal fees in session, so we can use them in onUpdatePrice() function                
        //Shopware()->Session()->offsetSet('testBla', 888);

    }

    // is thrown on every checkout action (n times for every item in basket, 3 times for every item update (pre, now, after))
    public function onBasketUpdatePrice(\Enlight_Event_EventArgs $args){

        $session = Shopware()->Session()->offsetGet('testBla');
        //dump($session);

        $basket = $args->getReturn();    
        //dump($basket);
        if($session == 888 AND $basket['ordernumber'] == 11012){
            // set new price for disposal fee article
            $basket['price'] = 1000 / 1.19; 
        }

        // return new values
        //$args->setReturn($basket);

    }




    
    // helper functions

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

        // Option 1 => Ja
        // Option 2 => Nein        
        Shopware()->Session()->offsetSet('applyLicenseFeeOption', $licenseFeeOption);
        Shopware()->Session()->offsetSet('licenseFeeOptionBefore', $licenseFeeOption);

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