<?php
// user/plugins/simple-shop-payment-test/simple-shop-payment-test.php

namespace Grav\Plugin;

use Grav\Common\Plugin;
use RocketTheme\Toolbox\Event\Event;
use Grav\Plugin\SimpleShopPaymentTest\Gateways\PaymentGatewayTest;

class SimpleShopPaymentTestPlugin extends Plugin
{
    public static function getSubscribedEvents()
    {
        return [
            'onPluginsInitialized' => ['onPluginsInitialized', 0]
        ];
    }

    public function onPluginsInitialized()
    {
        // Si nous sommes dans l'administration, on ne fait rien
        if ($this->isAdmin()) {
            return;
        }

        // Enregistrement de l'autoloader PSR-4 pour nos passerelles de test
        $loader = $this->grav['loader'];
        $loader->addPsr4('Grav\\Plugin\\SimpleShopPaymentTest\\', __DIR__ . '/classes/');

        $this->enable([
            'onSimpleShopRegisterGateways' => ['onSimpleShopRegisterGateways', 0]
        ]);
    }

    /**
     * S'accroche à l'écosystème SimpleShop pour déclarer le module de test
     */
	public function onSimpleShopRegisterGateways(Event $event)
    {
        // 1. Récupération d'une copie locale du tableau des passerelles
        $gateways = $event['gateways'] ?? [];
 
        // 2. Ajout de la passerelle de test (tunnel d'achat ET documents si simple-shop les gère : même identifiant)
        $testGateway = interface_exists(\Grav\Plugin\SimpleShop\Gateways\DocumentPaymentGatewayInterface::class)
            ? new \Grav\Plugin\SimpleShopPaymentTest\Gateways\DocumentGatewayTest()
            : new PaymentGatewayTest();
        $gateways[$testGateway->getId()] = $testGateway;

        // 3. Réinjection du tableau modifié dans l'événement
        $event['gateways'] = $gateways;
    }
}
