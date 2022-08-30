<?php

namespace DateifabrikP24DisposalFee;

use Shopware\Components\Plugin;
use Shopware\Components\Plugin\Context\InstallContext;
use Shopware\Components\Plugin\Context\ActivateContext;
use Shopware\Components\Plugin\Context\DeactivateContext;
use Shopware\Components\Plugin\Context\UninstallContext;

class DateifabrikP24DisposalFee extends Plugin
{

    public function install(InstallContext $context)
    {

/* 
            $test = \Shopware\Components\Api\Manager::getResource('Article');
            $params = array(
                'name' => 'LizenzTest',
                'active' => true,
                'tax' => '19',
                'supplier' => 'Lizenzero',
                'categories' => [
                    'id' => 2169
                ],                        
                'mainDetail' => [
                    'number' => 'turn',
                    'active' => true,
                    'prices' => [
                        [
                        'customerGroupKey' => 'EK',
                        'price'=> 999.88
                        ]
                    ]                            
                ]
            )
            ;
            $test->create($params);
 */

    }

    public function activate(ActivateContext $context)
    {

    }

    public function deactivate(DeactivateContext $context)
    {

    }

    public function uninstall(UninstallContext $context)
    {

    }



}

?>