<?php
// user/plugins/simple-shop-payment-test/classes/Gateways/PaymentGatewayTest.php

namespace Grav\Plugin\SimpleShopPaymentTest\Gateways;

use Grav\Plugin\SimpleShop\Gateways\PaymentGatewayInterface;
use Grav\Common\Utils;

class PaymentGatewayTest implements PaymentGatewayInterface
{
    public function getId(): string
    {
        return 'test_gateway';
    }

    public function getLabel(): string
    {
        return 'Mode de Test (Simulateur de Paiement)';
    }

    public function renderPaymentForm(array $orderData): string
    {
        // Génération d'un nonce spécifique à l'action finale de paiement de Grav
        $grav = \Grav\Common\Grav::instance();
        $nonce = Utils::getNonce('shop-payment-step');
        $actionUrl = $grav['uri']->route();

        // Affichage des informations de la commande pour le testeur et des deux boutons de simulation
        $html = "
        <div class='payment-gateway-test-box' style='background: #fff3cd; border: 1px solid #ffeba2; padding: 1.5rem; border-radius: 4px; margin: 1rem 0;'>
            <h4 style='margin-top:0; color: #856404;'>🤖 Environnement de Test</h4>
            <p>Vous vous apprêtez à simuler le règlement d'un montant de : <strong>" . number_format($orderData['billing_total'] ?? 0, 2, ',', ' ') . " €</strong></p>
            <p style='font-size: 0.9rem;'>Veuillez choisir l'issue de cette transaction fictive :</p>
            
            <div style='display: flex; gap: 1rem; margin-top: 1.5rem;'>
                <!-- Formulaire Succès -->
                <form method='POST' action='" . htmlspecialchars($actionUrl, ENT_QUOTES) . "' style='margin:0;'>
                    <input type='hidden' name='action' value='execute_payment' />
                    <input type='hidden' name='nonce' value='{$nonce}' />
                    <input type='hidden' name='test_payment_status' value='success' />
                    <button type='submit' class='btn' style='background: #28a745; color: #fff; border:none; padding: 0.75rem 1.5rem; cursor:pointer; border-radius:4px;'>
                        🟢 Simuler un Paiement Réussi
                    </button>
                </form>

                <!-- Formulaire Échec -->
                <form method='POST' action='" . htmlspecialchars($actionUrl, ENT_QUOTES) . "' style='margin:0;'>
                    <input type='hidden' name='action' value='execute_payment' />
                    <input type='hidden' name='nonce' value='{$nonce}' />
                    <input type='hidden' name='test_payment_status' value='failure' />
                    <button type='submit' class='btn' style='background: #dc3545; color: #fff; border:none; padding: 0.75rem 1.5rem; cursor:pointer; border-radius:4px;'>
                        🔴 Simuler un Échec de Paiement
                    </button>
                </form>
            </div>
        </div>
        ";

        return $html;
    }

    public function processPayment(array $postData): array
    {
        $status = $postData['test_payment_status'] ?? 'failure';

        if ($status === 'success') {
            return [
                'success' => true,
                'error' => null
            ];
        }

        return [
            'success' => false,
            'error' => 'Le simulateur de test a renvoyé un refus de transaction (Carte refusée / Solde insuffisant).'
        ];
    }
}
