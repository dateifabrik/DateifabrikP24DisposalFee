<?php

/*

SELECT sad.ordernumber, saa.articleID, saa.p24_material, saa.p24_license_material FROM s_articles_attributes AS saa INNER JOIN s_articles_details AS sad ON sad.articleID = saa.articleID WHERE saa.p24_license_material IS NOT NULL;
+-------------+-----------+-------------------+----------------------+
| ordernumber | articleID | p24_material      | p24_license_material |
+-------------+-----------+-------------------+----------------------+
| 11012       |         4 | Kunststoff ( PP ) | plastic              |
| 14017       |       114 |                   | cardboard            |
| 14033       |       210 |                   | cardboard            |
| 12164       |      5461 |                   | plastic              |
+-------------+-----------+-------------------+----------------------+

*/

namespace DateifabrikP24DisposalFee\Subscriber;

use Enlight\Event\SubscriberInterface;
use Doctrine\Common\Collections\ArrayCollection;

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
            'Enlight_Controller_Action_PostDispatch_Frontend_Checkout' => 'onPreDispatchCheckout',
            'Shopware_Modules_Basket_getPriceForUpdateArticle_FilterPrice' => 'checkoutPriceUpdateArticleFilter',
            'Theme_Compiler_Collect_Plugin_Javascript' => 'collectJavascriptFiles',
        ];
    }

    /* collects all javascript files in the specified locations */
    public function collectJavascriptFiles()
    {
        $js = array(
            __DIR__ . '/../Resources/views/frontend/_public/src/js/jquery.test.js', // javascript from plugin resource
            __DIR__ . '/../Resources/Themes/Frontend/ThemeDateifabrik/frontend/_public/src/js/jquery.test.js', // javascript from theme resource
        );

        return new ArrayCollection($js);
    }

    public function onAssignOrdernumbers(\Enlight_Event_EventArgs $args)
    {

        $subject = $args->getSubject();
        $view = $subject->View();
        $view->assign('disposalFeeOrdernumbers', $this->alleLizenzArtikelOrdernumbers);
    }

    public function onPreDispatchCheckout(\Enlight_Event_EventArgs $args)
    {

        ///////////////////////////////////////////////////////////////////////
        // ToDo
        // Lizenzartikel im Warenkorb können nicht geläscht werden
        // Anzahl der Lizenzartikel im Warenkorb nicht änderbar

        // only execute if the billing address (countryId) is germany (2)
        $countryId = $this->getCountryId();
        if ($countryId == 2) {

            $subject = $args->getSubject();
            $action = $subject->request()->getQuery('action');
            $view = $subject->View();
            $licenseFeeOption = $this->getSessionOption();

            // view form and monitor option change only for action = confirm        
            if ($action == 'confirm') {

                // show form only on confirm page if countryId = 2 (germany)
                $view->assign('countryId', $countryId);

                // assignment of the new option when changing
                // functions will be executed only when license fee option is set and has changed
                if (isset($licenseFeeOption)) {

                    switch ($licenseFeeOption) {
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

            if (isset($licenseFeeOption)) {

                switch ($licenseFeeOption) {
                    case 1:
                        // updates the licensearticles in basket
                        $this->updateLicenseArticles();
                        break;
                    case 2:
                        $this->deleteAllLicenseArticlesFromBasket($this->getBasketData());
                        break;
                }
            }

            $sortedItemsForBasketView = $this->sortItemsForBasketView();
            $view->assign('sortedItemsForBasketView', $sortedItemsForBasketView);
        }
    }

    public function updateLicenseArticles()
    {

        $basketData = $this->getBasketData();
        //dump($basketData);

        $alu = array();
        $cardboard = array();
        $other_materials = array();
        $plastic = array();

        $aluIsInBasket = FALSE;
        $cardboardIsInBasket = FALSE;
        $otherMaterialsIsInBasket = FALSE;
        $plasticIsInBasket = FALSE;

        // check, if material in basket is NEW, ADDED or REMOVED
        foreach ($basketData['content'] as $basket) {

            // don't check license articles
            if (!in_array($basket['ordernumber'], $this->alleLizenzArtikelOrdernumbers)) {
                // get the material option value
                $p24LicenseMaterial = $basket['additional_details']['p24_license_material']; // (new values from) combo box option value
                $p24Material = $basket['additional_details']['p24_material']; // (old) text like 'Pappe / PLA', materialHelper() returns new option value, if not empty
                $standardMaterial = 'xoxo';

                // beide sind leer = Standardmaterial
                if (empty($p24LicenseMaterial) && empty($p24Material)) {
                    $material = $standardMaterial;
                }
                if (empty($p24LicenseMaterial) && !empty($p24Material)) {
                    $material = $this->materialHelper($p24Material);
                }
                if (!empty($p24LicenseMaterial) && !empty($p24Material)) {
                    $material = $p24LicenseMaterial;
                }
                if (!empty($p24LicenseMaterial) && empty($p24Material)) {
                    $material = $p24LicenseMaterial;
                }

                if ($material == 'alu') {
                    $alu[] = $basket['quantity'];
                }
                if ($material == 'cardboard') {
                    $cardboard[] = $basket['quantity'];
                }
                if ($material == 'other_materials') {
                    $other_materials[] = $basket['quantity'];
                }
                if ($material == 'plastic') {
                    $plastic[] = $basket['quantity'];
                    $plasticWeight = ($basket['quantity'] * $basket['purchaseunit'] * str_replace(",", ".", $basket['additional_details']['p24_license_weight']) / 1000);
                }
            }
            // if license article is already in basket, prepare update/add or delete from basket
            else {
                // deleting, material is no longer in basket
                if ($basket['ordernumber'] == 'ENT-ALU-LZ' && array_sum($alu) == 0) {
                    Shopware()->Modules()->Basket()->sDeleteArticle($basket['id']);
                }
                if ($basket['ordernumber'] == 'ENT-CARDBOARD-LZ' && array_sum($cardboard) == 0) {
                    Shopware()->Modules()->Basket()->sDeleteArticle($basket['id']);
                }
                if ($basket['ordernumber'] == 'ENT-OTHER_MATERIALS-LZ' && array_sum($other_materials) == 0) {
                    Shopware()->Modules()->Basket()->sDeleteArticle($basket['id']);
                }
                if ($basket['ordernumber'] == 'ENT-PLASTIC-LZ' && array_sum($plastic) == 0) {
                    Shopware()->Modules()->Basket()->sDeleteArticle($basket['id']);
                }

                if ($basket['ordernumber'] == 'ENT-ALU-LZ' && array_sum($alu) > 0) {
                    $basketIdAlu = $basket['id'];
                    Shopware()->Modules()->Basket()->sUpdateArticle($basketIdAlu, array_sum($alu));                    
                    $aluIsInBasket = TRUE;
                }
                if ($basket['ordernumber'] == 'ENT-CARDBOARD-LZ' && array_sum($cardboard) > 0) {
                    $basketIdCardboard = $basket['id'];
                    Shopware()->Modules()->Basket()->sUpdateArticle($basketIdCardboard, array_sum($cardboard));                    
                    $cardboardIsInBasket = TRUE;
                }
                if ($basket['ordernumber'] == 'ENT-OTHER_MATERIALS-LZ' && array_sum($other_materials) > 0) {
                    $basketIdOtherMaterials = $basket['id'];
                    Shopware()->Modules()->Basket()->sUpdateArticle($basketIdOtherMaterials, array_sum($other_materials));                    
                    $otherMaterialsIsInBasket = TRUE;
                }
                if ($basket['ordernumber'] == 'ENT-PLASTIC-LZ' && array_sum($plastic) > 0) {
                    $basketIdPlastic = $basket['id'];
                    Shopware()->Modules()->Basket()->sUpdateArticle($basketIdPlastic, array_sum($plastic));                    
                    $plasticIsInBasket = TRUE;
                }                        
            }


        }

        if ($aluIsInBasket=== TRUE && array_sum($alu) > 0) {
            Shopware()->Modules()->Basket()->sUpdateArticle($basketIdAlu, array_sum($alu));   
            Shopware()->Session()->offsetSet('aluPrice', 10);
        }
        if ($cardboardIsInBasket === TRUE && array_sum($cardboard) > 0) {
            Shopware()->Modules()->Basket()->sUpdateArticle($basketIdCardboard, array_sum($cardboard));  
            Shopware()->Session()->offsetSet('cardboardPrice', 20);
        }
        if ($otherMaterialsIsInBasket === TRUE && array_sum($other_materials) > 0) {
            Shopware()->Modules()->Basket()->sUpdateArticle($basketIdOtherMaterials, array_sum($other_materials));
            Shopware()->Session()->offsetSet('other_materialPrice', 30);
        }
        if ($plasticIsInBasket === TRUE && array_sum($plastic) > 0) {
            Shopware()->Modules()->Basket()->sUpdateArticle($basketIdPlastic, array_sum($plastic));   
            Shopware()->Session()->offsetSet('plasticPrice', 30);
        }        

        // license article has to be added
        if ($aluIsInBasket === FALSE && array_sum($alu) > 0) {
            Shopware()->Modules()->Basket()->sAddArticle('ENT-ALU-LZ', array_sum($alu));
            Shopware()->Session()->offsetSet('aluPrice', 10);
        }
        if ($cardboardIsInBasket === FALSE && array_sum($cardboard) > 0) {
            Shopware()->Modules()->Basket()->sAddArticle('ENT-CARDBOARD-LZ', array_sum($cardboard));
            Shopware()->Session()->offsetSet('cardboardPrice', 20);
        }
        if ($otherMaterialsIsInBasket === FALSE && array_sum($other_materials) > 0) {
            Shopware()->Modules()->Basket()->sAddArticle('ENT-OTHER_MATERIALS-LZ', array_sum($other_materials));
            Shopware()->Session()->offsetSet('other_materialPrice', 30);
        }
        if ($plasticIsInBasket === FALSE && array_sum($plastic) > 0) {
            Shopware()->Modules()->Basket()->sAddArticle('ENT-PLASTIC-LZ', array_sum($plastic));
            Shopware()->Session()->offsetSet('plasticPrice', 40);
        }


                 
    }

    public function deleteAllLicenseArticlesFromBasket($basket)
    {

        foreach ($basket['content'] as $content) {
            if (in_array($content['ordernumber'], $this->alleLizenzArtikelOrdernumbers)) {
                $basketIDForDeleting = $content['id'];
                Shopware()->Modules()->Basket()->sDeleteArticle($basketIDForDeleting);
            }
        }
    }


    // is thrown on every checkout action
    public function checkoutPriceUpdateArticleFilter(\Enlight_Event_EventArgs $args)
    {

        // show form only on confirm page if countryId = 2 (germany)
        $countryId = $this->getCountryId();
        if ($countryId == 2) {

            $licenseFeeOption = $this->getSessionOption();
            $aluPrice = Shopware()->Session()->offsetGet('aluPrice');
            $cardboardPrice = Shopware()->Session()->offsetGet('cardboardPrice');
            $other_materialPrice = Shopware()->Session()->offsetGet('other_materialPrice');
            $plasticPrice = Shopware()->Session()->offsetGet('plasticPrice');

            if (isset($licenseFeeOption) && $licenseFeeOption == 1) {

                // set new price for disposal fee article                

                $basket = $args->getReturn();
                if ($basket['ordernumber'] == 'ENT-ALU-LZ') {
                    $basket['price'] = $aluPrice / 1.19;
                }
                if ($basket['ordernumber'] == 'ENT-CARDBOARD-LZ') {
                    $basket['price'] = $cardboardPrice / 1.19;
                }
                if ($basket['ordernumber'] == 'ENT-OTHER_MATERIALS-LZ') {
                    $basket['price'] = $other_materialPrice / 1.19;
                }
                if ($basket['ordernumber'] == 'ENT-PLASTIC-LZ') {
                    // set new price for disposal fee article
                    $basket['price'] = $plasticPrice / 1.19;
                }
                $args->setReturn($basket);
            }
        }
    }




    ################################################################################################
    // helper functions

    public function getSessionOption()
    {

        return Shopware()->Session()->offsetGet('applyLicenseFeeOption');
    }

    public function setSessionOption($licenseFeeOption)
    {

        Shopware()->Session()->offsetSet('applyLicenseFeeOption', $licenseFeeOption);
    }

    public function getCountryId()
    {

        return Shopware()->Modules()->Admin()->sGetUserData()['billingaddress']['countryId'];
    }

    public function getBasketData()
    {

        return Shopware()->Modules()->Basket()->sGetBasketData();
    }

    // always sorts the license items at the end of the array so that they can be output as the last item in the basket list in the frontend
    public function sortItemsForBasketView()
    {

        $basket = $this->getBasketData();
        $i = 0;
        foreach ($basket['content'] as $content) {
            if ($content['additional_details']['supplierID'] == 20) {
                $append = $content;
                unset($basket['content'][$i]);
                array_push($basket['content'], $append);
            }
            $i++;
        }

        $basket['content'] = array_values($basket['content']);
        return $basket;
    }

    public function materialHelper($material)
    {

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

        if (in_array($material, $alu)) {
            $material = 'alu';
        }
        if (in_array($material, $cardboard)) {
            $material = 'cardboard';
        }
        if (in_array($material, $otherMaterial)) {
            $material = 'other_material';
        }
        if (in_array($material, $plastic)) {
            $material = 'plastic';
        }

        return $material;
    }
}
