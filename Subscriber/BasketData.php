<?php

/*

+-------------+-----------+-------------------+----------------------+
| ordernumber | articleID | p24_material      | p24_license_material |
+-------------+-----------+-------------------+----------------------+
| 11012       |         4 | Kunststoff ( PP ) | plastic              |
| 14017       |       114 |                   | cardboard            |
| 12164       |      5461 |                   | plastic              |
+-------------+-----------+-------------------+----------------------+

*/

namespace DateifabrikP24DisposalFee\Subscriber;

use Enlight\Event\SubscriberInterface;

class BasketData implements SubscriberInterface
{

    // set license fee ordernumbers
    protected $alleLizenzArtikelOrdernumbers = array(
        'ENT-ALU-LZ',
        'ENT-CARDBOARD-LZ',
        'ENT-OTHER_MATERIALS-LZ',
        'ENT-PLASTIC-LZ',
    );

    public static function getSubscribedEvents()
    {
        return [
            'Enlight_Controller_Action_PreDispatch_Frontend' => 'onAssignOrdernumbers',
            'Enlight_Controller_Action_PreDispatch_Frontend_Checkout' => 'onPreDispatchCheckout',
            'Shopware_Modules_Basket_getPriceForUpdateArticle_FilterPrice' => 'checkoutPriceUpdateArticleFilter',
        ];
    }

    public function onAssignOrdernumbers(\Enlight_Event_EventArgs $args){

        $subject = $args->getSubject();
        $view = $subject->View();  
        $view->assign('disposalFeeOrdernumbers', $this->alleLizenzArtikelOrdernumbers);               

    }

    public function onPreDispatchCheckout(\Enlight_Event_EventArgs $args){

        ///////////////////////////////////////////////////////////////////////
        // ToDo
        // Wenn der letzte Artikel mit vorhandenem Material entfernt wird,
        // müssen auch die Lizenzartikel gelöscht werden  
        // Sortierung festlegen, Lizenzartikel immer am Ende
        // custom/plugins/DateifabrikLizenzHinweis/Services/DateifabrikLizenzHinweisService.php


        // nur bei Rechnungsadresse (countryId) Deutschland (2) ausführen
        $countryId = $this->getCountryId();
        if($countryId == 2){

            $subject = $args->getSubject();
            $action = $subject->request()->getQuery('action');
            $view = $subject->View();  
            $licenseFeeOption = $this->getSessionOption(); 

            // Formular anzeigen und Optionsänderung überwachen nur nur für Action = confirm        
            if($action == 'confirm')
            {

                // Formular nur auf confirm-Seite anzeigen, wenn countryId = 2 (Deutschland)
                $view->assign('countryId', $countryId);       

                // Zuweisung der neuen Option bei Wechsel
                // functions will be executed only when license fee option is set and has changed
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

            if(isset($licenseFeeOption)){

                switch($licenseFeeOption){
                    case 1:
                        // updates the licensearticles in basket
                        $this->updateLicenseArticles();
                        break;
                    case 2:
                        $this->deleteAllLicenseArticlesFromBasket($this->getBasketData());
                        break;
                }

            } 
            
            $basket = $this->getBasketData();
            //dump($basket['content']);
            $i = 0;
            foreach($basket['content'] as $mycontent){
                if($mycontent['additional_details']['supplierID'] == 20){
                    $hangOn = $mycontent;
                    unset($basket['content'][$i]);
                    array_push($basket['content'], $hangOn);
                }
                $i++;
            }
            
            $basket['content'] = array_values($basket['content']);
            //dump($basket);
            $view->assign('mySort', $basket);
    
    
            foreach($basket['content'] as $content){
                // $content['additional_details'][supplierID] = 20
                //dump($content['additional_details']['supplierID']);
                //$supplierID[] = $content['additional_details']['supplierID'];
                if($content['additional_details']['supplierID'] == 20){
                    
                    //array_push($basket['content'], $content);
                }
                $i++;
    
            }
            //sort($supplierID);
            //dump($supplierID);
            //dump($basket);            

        } 

    }

    public function updateLicenseArticles(){

        $basketData = $this->getBasketData();
        //dump($basketData);

        $alu = array();
        $cardboard = array();
        $other_materials = array();        
        $plastic = array();

        $updateAluInBasket = FALSE;
        $updateCardboardInBasket = FALSE;        
        $updateOtherMaterialsInBasket = FALSE;  
        $updatePlasticInBasket = FALSE;        

        // check, if material in basket is NEW, ADDED or REMOVED
        foreach($basketData['content'] as $basket){

            // don't check license articles
            if(!in_array($basket['ordernumber'], $this->alleLizenzArtikelOrdernumbers)){
                // get the material option value
                $p24LicenseMaterial = $basket['additional_details']['p24_license_material']; // (new values from) combo box option value
                $p24Material = $basket['additional_details']['p24_material']; // (old) text like 'Pappe / PLA', materialHelper() returns new option value, if not empty
                $standardMaterial = 'xoxo';

                // beide sind leer = Standardmaterial
                if(empty($p24LicenseMaterial) && empty($p24Material)){
                    $material = $standardMaterial;
                }
                if(empty($p24LicenseMaterial) && !empty($p24Material)){
                    $material = $this->materialHelper($p24Material);
                }                
                if(!empty($p24LicenseMaterial) && !empty($p24Material)){
                    $material = $p24LicenseMaterial;
                }                                
                if(!empty($p24LicenseMaterial) && empty($p24Material)){
                    $material = $p24LicenseMaterial;
                }                   

                if($material == 'alu'){
                    $alu[] = $basket['quantity'];
                }
                if($material == 'cardboard'){
                    $cardboard[] = $basket['quantity'];
                }
                if($material == 'other_materials'){
                    $other_materials[] = $basket['quantity'];
                }                                
                if($material == 'plastic'){
                    $plastic[] = $basket['quantity'];
                    $plasticWeight = ($basket['quantity'] * $basket['purchaseunit'] * str_replace(",",".",$basket['additional_details']['p24_license_weight']) / 1000);
                }                

                $materialInBasket[$basket['ordernumber']] = [
                    'basketId'              => $basket['id'],
                    'articleId'             => $basket['articleID'],
                    'quantity'              => $basket['quantity'],     
                    'puchaseunit'           => $basket['purchaseunit'],
                    'p24_license_weight'    => str_replace(",",".",$basket['additional_details']['p24_license_weight']),
                    'material'              => $material,
                ];

            }
            // if license article is already in basket, prepare update/add or delete from basket
            else{
                if($basket['ordernumber'] == 'ENT-ALU-LZ' && array_sum($alu) > 0){
                    //Shopware()->Modules()->Basket()->sUpdateArticle($basket['id'], array_sum($alu));
                    //Shopware()->Session()->offsetSet('aluPrice', 10);
                    // change to TRUE, now sAddArticle() will not be executed
                    $updateAluInBasket = TRUE;
                    $basketIdAlu = $basket['id'];                    
                }
                if($basket['ordernumber'] == 'ENT-CARDBOARD-LZ' && array_sum($cardboard) > 0){
                    //Shopware()->Modules()->Basket()->sUpdateArticle($basket['id'], array_sum($cardboard));
                    //Shopware()->Session()->offsetSet('cardboardPrice', 20);
                    // change to TRUE, now sAddArticle() will not be executed
                    $updateCardboardInBasket = TRUE;
                    $basketIdCardboard = $basket['id'];                     
                }                
                if($basket['ordernumber'] == 'ENT-OTHER_MATERIALS-LZ' && array_sum($other_materials) > 0){
                    //Shopware()->Modules()->Basket()->sUpdateArticle($basket['id'], array_sum($other_materials));
                    //Shopware()->Session()->offsetSet('other_materialPrice', 30);
                    // change to TRUE, now sAddArticle() will not be executed
                    $updateOtherMaterialsInBasket = TRUE;
                    $basketIdOtherMaterials = $basket['id'];
                }
                if($basket['ordernumber'] == 'ENT-PLASTIC-LZ' && array_sum($plastic) > 0){
                    //$plasticPrice = (str_replace(",",".",$basket['price']) * $plasticWeight);
                    //Shopware()->Modules()->Basket()->sUpdateArticle($basket['id'], array_sum($plastic));
                    //Shopware()->Session()->offsetSet('plasticPrice', 40);
                    // change to TRUE, now sAddArticle() will not be executed
                    $updatePlasticInBasket = TRUE;
                    $basketIdPlastic = $basket['id'];
                }
                // deleting, material is no longer in basket
                if($basket['ordernumber'] == 'ENT-ALU-LZ' && array_sum($alu) == 0){
                    Shopware()->Modules()->Basket()->sDeleteArticle($basket['id']);
                }                                  
                if($basket['ordernumber'] == 'ENT-CARDBOARD-LZ' && array_sum($cardboard) == 0){
                    Shopware()->Modules()->Basket()->sDeleteArticle($basket['id']);
                }                                  
                if($basket['ordernumber'] == 'ENT-OTHER_MATERIALS-LZ' && array_sum($other_materials) == 0){
                    Shopware()->Modules()->Basket()->sDeleteArticle($basket['id']);
                }                                  
                if($basket['ordernumber'] == 'ENT-PLASTIC-LZ' && array_sum($plastic) == 0){
                    Shopware()->Modules()->Basket()->sDeleteArticle($basket['id']);
                }                                                                                  
            }

        }   

        // license article has to be updated
        if($updateAluInBasket === TRUE){
            Shopware()->Modules()->Basket()->sUpdateArticle($basketIdAlu, array_sum($alu));
            Shopware()->Session()->offsetSet('aluPrice', 10);            
        }
        if($updateCardboardInBasket === TRUE){
            Shopware()->Modules()->Basket()->sUpdateArticle($basketIdCardboard, array_sum($cardboard));
            Shopware()->Session()->offsetSet('cardboardPrice', 20);            
        }
        if($updateOtherMaterialsInBasket === TRUE){
            Shopware()->Modules()->Basket()->sUpdateArticle($basketIdOtherMaterials, array_sum($other_materials));
            Shopware()->Session()->offsetSet('other_materialPrice', 30);
        }                       
        if($updatePlasticInBasket === TRUE){
            Shopware()->Modules()->Basket()->sUpdateArticle($basketIdPlastic, array_sum($plastic));
            Shopware()->Session()->offsetSet('plasticPrice', 40);             
        }

        // license article has to be added
        if($updateAluInBasket === FALSE && array_sum($alu) > 0){
            Shopware()->Modules()->Basket()->sAddArticle('ENT-ALU-LZ', array_sum($alu));
            Shopware()->Session()->offsetSet('aluPrice', 10);
        }          
        if($updateCardboardInBasket === FALSE && array_sum($cardboard) > 0){
            Shopware()->Modules()->Basket()->sAddArticle('ENT-CARDBOARD-LZ', array_sum($cardboard));
            Shopware()->Session()->offsetSet('cardboardPrice', 20);
        }  
        if($updateOtherMaterialsInBasket === FALSE && array_sum($other_materials) > 0){
            Shopware()->Modules()->Basket()->sAddArticle('ENT-OTHER_MATERIALS-LZ', array_sum($other_materials));
            Shopware()->Session()->offsetSet('other_materialPrice', 30);
        }  
        if($updatePlasticInBasket === FALSE && array_sum($plastic) > 0){
            Shopware()->Modules()->Basket()->sAddArticle('ENT-PLASTIC-LZ', array_sum($plastic));
            Shopware()->Session()->offsetSet('plasticPrice', 40);            
        }      
        
        // dump($plasticPrice);    
        // dump($plasticWeight);        
        // dump($materialInBasket);
        // dump(array_sum($alu));
        // dump(array_sum($cardboard));
        // dump(array_sum($other_materials));                        
        // dump(array_sum($plastic));        
        

    }

    public function deleteAllLicenseArticlesFromBasket($basket){

        foreach($basket['content'] as $content){
            if(in_array($content['ordernumber'], $this->alleLizenzArtikelOrdernumbers)){
                $basketIDForDeleting = $content['id'];
                Shopware()->Modules()->Basket()->sDeleteArticle($basketIDForDeleting);
            }
        }        

    }    


    // is thrown on every checkout action
    public function checkoutPriceUpdateArticleFilter(\Enlight_Event_EventArgs $args){

        ///////////////////////////////////////////////////////////////////////
        // ToDo        
        // Sortierung festlegen, Lizenzartikel immer am Ende
        // custom/plugins/DateifabrikLizenzHinweis/Services/DateifabrikLizenzHinweisService.php

        // nur bei deutscher Rechnungsadresse ausführen
        $countryId = $this->getCountryId();
        if($countryId == 2){

            $licenseFeeOption = $this->getSessionOption();
            $plasticPrice = Shopware()->Session()->offsetGet('plasticPrice');
            $cardboardPrice = Shopware()->Session()->offsetGet('cardboardPrice');

            if(isset($licenseFeeOption) && $licenseFeeOption == 1){

                // $basket = $args->getReturn();
                // if($basket['ordernumber'] == 'ENT-PLASTIC-LZ'){
                //     // set new price for disposal fee article
                //     $basket['price'] = $plasticPrice / 1.19; 
                // }
                // $args->setReturn($basket);
                // $basket = $args->getReturn();
                // if($basket['ordernumber'] == 'ENT-CARDBOARD-LZ'){
                //     // set new price for disposal fee article
                //     $basket['price'] = $cardboardPrice / 1.19; 
                // }                
                // // return new values
                // $args->setReturn($basket);

            }        

        }
        
    }




    ################################################################################################
    // helper functions

    public function getSessionOption(){

        return Shopware()->Session()->offsetGet('applyLicenseFeeOption');    

    }    

    public function setSessionOption($licenseFeeOption){
    
        Shopware()->Session()->offsetSet('applyLicenseFeeOption', $licenseFeeOption);

    }

    public function getCountryId(){

        return Shopware()->Modules()->Admin()->sGetUserData()['billingaddress']['countryId'];    

    }

    public function getBasketData(){

        return Shopware()->Modules()->Basket()->sGetBasketData();

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