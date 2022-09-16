<?php

namespace DateifabrikP24DisposalFee\Subscriber;

use Enlight\Event\SubscriberInterface;

class BasketData implements SubscriberInterface
{

    public static function getSubscribedEvents()
    {
        return [
            'Enlight_Controller_Action_PreDispatch_Frontend_Checkout' => 'onBeforeCheckout',            
            'Enlight_Controller_Action_PostDispatchSecure_Frontend_Checkout' => 'getActionName',
            'Enlight_Controller_Action_PostDispatchSecure_Frontend_Checkout' => 'onPostDispatchFrontendCheckoutConfirm',
            'Shopware_Modules_Basket_AddArticle_CheckBasketForArticle' => 'checkBasketForArticle',

        ];
    }

    public function onBeforeCheckout(\Enlight_Event_EventArgs $args){
        
        $subject        = $args->getSubject();
        $orderVariables = $subject->get('session')->get('sOrderVariables');
        $basketContent  = $orderVariables['sBasket']['content'];
        $option         = $subject->get('session')->offsetGet('applyLicenseFeeOption');

        if($option){
            switch($option){
                case 1:
                    $this->addLicenseFeeArticles($basketContent);
                    break;
                case 2:
                    $this->removeLicenseFeeArticles($subject);
                    break;
            }
        }

    }

    public function getActionName($args)
    {
        return $args->getSubject()->request()->getQuery('action');
    }

    public function onPostDispatchFrontendCheckoutConfirm(\Enlight_Event_EventArgs $args)
    {

        if($this->getActionName($args) == 'confirm'){    
            
            $subject = $args->getSubject();
            $view = $subject->View();

            $orderVariables     = $subject->get('session')->get('sOrderVariables');
            $countryId          = $orderVariables['sUserData']['billingaddress']['countryId'];

            // Formular nur anzeigen, wenn countryId = 2 (Deutschland)
            $view->assign('countryId', $countryId);

            // Berechnungen nur für Rechnungsadresse Deutschland ausführen
            if($countryId == 2){

                // Ist eine Option für Entsorgungsgebühr ausgewählt?
                // nichts ausgewählt = NULL
                $applyLicenseFeeOption = $subject->get('session')->offsetGet('applyLicenseFeeOption');

                if(isset($applyLicenseFeeOption)){

                    $basketContent = $orderVariables['sBasket']['content'];       

                    // Option 1 => Ja
                    // Option 2 => Nein
                    switch($applyLicenseFeeOption){
                        case 1:
                            $view->assign('selected1', 'selected="selected"');
                            $view->assign('selected2', '');
                            //$this->addLicenseFeeArticles($basketContent);
                            $subject->get('session')->offsetSet('applyLicenseFeeOption', $applyLicenseFeeOption);
                            break;
                        case 2:
                            $view->assign('selected1', '');
                            $view->assign('selected2', 'selected="selected"');
                            //$this->removeLicenseFeeArticles($subject);
                            $subject->get('session')->offsetSet('applyLicenseFeeOption', $applyLicenseFeeOption);
                            break;
                    }
                }

                $conf['Aluminium'] = Shopware()->Config()->getByNamespace('DateifabrikP24DisposalFee', 'Aluminium');
                $conf['Pappe'] = Shopware()->Config()->getByNamespace('DateifabrikP24DisposalFee', 'Pappe');
                $conf['Plastik'] = Shopware()->Config()->getByNamespace('DateifabrikP24DisposalFee', 'Plastik');
                $conf['sonstige'] = Shopware()->Config()->getByNamespace('DateifabrikP24DisposalFee', 'sonstige');
                $view->assign('Material', $conf);

            }

        }

    }

    public function addLicenseFeeArticles($basketContent){
        /*
            Was benötige ich alles?
            - Anzahl
            - Verpackungseinheit
            - Lizenzgewicht (p24_license_weight)
            - Material (p24_license_material)
            - Preis pro kg (aus Config)
            Anzahl * Verpackungseinheit * Lizenzgewicht * Preis pro kg Material
        */

        foreach($basketContent as $content){
            $quantity = $content['quantity'];
            $additionalDetails = $content['additional_details'];
        }   
        
        Shopware()->Modules()->Basket()->sAddArticle(11018);

    }     
    
    public function removeLicenseFeeArticles($subject){

        $sessionId = Shopware()->Session()->get('sessionId');     

        $connection = $subject->get('dbal_connection');
        $builder = $connection->createQueryBuilder();

        $builder
            ->select('id')
            ->from('s_order_basket')
            ->andwhere('sessionID = :sessionID')
            ->andwhere('ordernumber = :ordernumber')
            ->setParameter('sessionID', $sessionId)
            ->setParameter('ordernumber', 11018);

        $orderBasketId = $builder->execute()->fetchColumn();            
        Shopware()->Modules()->Basket()->sDeleteArticle($orderBasketId);

    }    

    public function checkBasketForArticle(\Enlight_Event_EventArgs $args){
        $builder = $args->get('queryBuilder');
        $or = $builder->getParameter('ordernumber');
        dump('Zeile 144 BasketData.php checkBasketForArticle '. $or);
        die();

    }

}



      


        

/*

    public function __construct()
    {
        
        $builder = Shopware()->Container()->get('models')->createQueryBuilder();
        $builder->select(['product', 'mainVariant'])
            ->from(\Shopware\Models\Article\Article::class, 'product')
            ->innerJoin('product.mainDetail', 'mainVariant')
            ->where('product.id = :productId')
            ->setParameter('productId', 2);

        // Array with \Shopware\Models\Article\Article objects
        $objectData = $builder->getQuery()->getResult();
        //dump($objectData);

        // Array with arrays
        $arrayData = $builder->getQuery()->getArrayResult();
        //dump($arrayData);

    }


$articleModel = 'Shopware\Models\Article\Article';

if($this->getActionName($args) == 'confirm'){

    $builder = Shopware()->Models()->createQueryBuilder();
    $builder->select(array('article.name'))
        ->from($articleModel, 'article')
        ->andwhere('article.id = 2');
        
    $result = $builder->getQuery()->getResult();

    dump($result);
    //die();
    

    echo "<p>\$args->getSubject()->getBasket()</p>";
    dump($args->getSubject()->getBasket());

    $basketContent = $args->getSubject()->getBasket()['content'];
    $basketSessionID = $args->getSubject()->getBasket()['content'][0]['sessionID'];
    $basketUserID = $args->getSubject()->getBasket()['content'][0]['userID'];
    $i = 0;
    foreach($basketContent as $basketItem){
        echo "<p>\$args->getSubject()->getBasket()['content'][$i]</p>";
        dump($basketItem);
        $i++;
    }

    $sessionIdFromRepo = Shopware()->Models()->getRepository('Shopware\Models\Order\Basket');
    // hole die Daten des Warenkorbs
    echo "<p>\$sessionIdFromRepo->findBy(['sessionId' => \$basketSessionID])</p>";
    dump($sessionIdFromRepo->findBy(['sessionId' => $basketSessionID]));
    

    $userNameFromRepo = Shopware()->Models()->getRepository('Shopware\Models\Customer\Customer');
    // finde die userId mit Hilfe des Repos
    echo "<p>\$name = \$userNameFromRepo->find(\$basketUserID)->getFirstName(). ' '. \$userNameFromRepo->find(\$basketUserID)->getLastName()</p>";
    //dump($name = $userNameFromRepo->find($basketUserID)->getFirstName(). ' '. $userNameFromRepo->find($basketUserID)->getLastName());

    // Shopware => Core => sBasket.php
    $module = Shopware()->Modules()->Basket()->sGetBasketData();
    dump($module);
    foreach($module['content'] as $m){
        dump($m['additional_details']['p24_license_weight']);
        dump($m['additional_details']['p24_license_material']);
    }

    // Auslesen der Plugin-Konfiguration
    //$conf = Shopware()->Config()->getByNamespace('DateifabrikP24DisposalFee', 'simpleNumberField');
    // O D E R einfach
    $conf = Shopware()->Config()->get('simpleTextField');
    dump($conf);

    $cont = Shopware()->Container()->get('dbal_connection');
    dump($cont);

    //die();            

}

*/