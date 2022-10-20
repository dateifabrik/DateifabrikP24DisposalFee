<?php

namespace DateifabrikP24DisposalFee\Subscriber;

use Enlight\Event\SubscriberInterface;

class BasketData implements SubscriberInterface
{

    public static function getSubscribedEvents()
    {
        return [
            'Shopware_Modules_Basket_GetBasket_FilterItemStart' => 'filter',
        ];
    }

    public function filter(\Enlight_Event_EventArgs $args){

        $test = $args->getReturn();
        foreach($test as $t){
            if($test['ordernumber'] == 11012){
                $test['price'] = 555;
                $test['netprice'] = $test['price']/1.19;
            }
        }
        return $test;
    }




}