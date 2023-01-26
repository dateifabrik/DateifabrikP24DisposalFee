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


        $shop = Shopware()->Models()->getRepository('Shopware\Models\Shop\Shop')->findOneBy(array('default' => true));
        $pluginManager = Shopware()->Container()->get('shopware_plugininstaller.plugin_manager');
        $plugin = $pluginManager->getPluginByName('DateifabrikP24DisposalFee');

        $config = [
            'Aluminium'     => 500,
            'Pappe'         => 200,
            'Plastik'       => 8.15,
        ];

        foreach ($config as $key => $value) {
            $pluginManager->saveConfigElement($plugin, $key, $value, $shop);
        }

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