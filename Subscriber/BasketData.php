<?php

namespace DateifabrikP24DisposalFee\Subscriber;

use Enlight\Event\SubscriberInterface;
use Shopware;

class BasketData implements SubscriberInterface
{

    public $blablub = 'irgendwas';

    /**
     * {@inheritdoc}
     */
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

    public static function getSubscribedEvents()
    {
        return [
            'Enlight_Controller_Action_PostDispatchSecure_Frontend_Checkout' => 'getActionName',
            'Enlight_Controller_Action_PostDispatchSecure_Frontend_Checkout' => 'onPostDispatchFrontendCheckoutConfirm',
        ];
    }

    public function getActionName($args)
    {
        return $actionName = $args->getSubject()->request()->getQuery('action');
    }

    public function onPostDispatchFrontendCheckoutConfirm(\Enlight_Event_EventArgs $args)
    {

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
            dump($name = $userNameFromRepo->find($basketUserID)->getFirstName(). ' '. $userNameFromRepo->find($basketUserID)->getLastName());

            // Shopware => Core => sBasket.php
            $module = Shopware()->Modules()->Basket()->sGetBasketData();
            dump($module);
            foreach($module['content'] as $m){
                dump($m['additional_details']['p24_license_weight']);
                dump($m['additional_details']['p24_license_material']);
            }

            // Auslesen der Plugin-Konfiguration
            //$conf = Shopware()->Config()->get('DateifabrikP24DisposalFee', 'simpleNumberField');
            // O D E R einfach
            $conf = Shopware()->Config()->get('simpleNumberField');
            dump($conf);

            die();            

        }

    }

}