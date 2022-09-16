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
            $articleApi = $this->container->get('shopware.api.article');

            $params = array(
                'name' => 'MeinArtikelname',
                'active' => true,
                'taxId' => 1,
                'supplier' => 'MeinHersteller',
                'categories' => [
                    [
                    'id' => 2169
                    ]
                ],                        
                'mainDetail' => [
                    'number' => 'MeineNumber',
                    'active' => true,
                    'prices' => [
                        [
                        'customerGroupKey' => 'EK',
                        'price'=> 999.88 // netto
                        ]
                    ]                            
                ]
            )
            ;
            $articleApi->create($params);
            
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