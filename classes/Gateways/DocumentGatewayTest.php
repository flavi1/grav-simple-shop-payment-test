<?php
// user/plugins/simple-shop-payment-test/classes/Gateways/DocumentGatewayTest.php

namespace Grav\Plugin\SimpleShopPaymentTest\Gateways;

use Grav\Common\Utils;
use Grav\Plugin\SimpleShop\Gateways\DocumentPaymentGatewayInterface;
use Grav\Plugin\SimpleShop\Gateways\PaymentContext;

/**
 * Paiement simulé d'un document (facture, acompte, demande de paiement envoyés depuis l'administration de simple-shop).
 * Même identifiant que la passerelle du tunnel (`test_gateway`) : deux boutons, succès ou échec. Enregistrée seulement si
 * simple-shop fournit le contrat des documents (simple-shop récent).
 */
class DocumentGatewayTest extends PaymentGatewayTest implements DocumentPaymentGatewayInterface
{
    public function isOffline(): bool
    {
        return false;
    }

    public function renderDocumentPayment(PaymentContext $ctx): string
    {
        $nonce = Utils::getNonce(PaymentContext::NONCE_ACTION);
        $action = htmlspecialchars($ctx->pageUrl, ENT_QUOTES, 'UTF-8');
        $amount = htmlspecialchars($ctx->amountText() . ' ' . $ctx->currency, ENT_QUOTES, 'UTF-8');
        $form = static fn (string $status, string $label, string $color): string =>
            "<form method='POST' action='{$action}' style='margin:0;'>"
            . "<input type='hidden' name='action' value='execute_document_payment' />"
            . "<input type='hidden' name='method' value='test_gateway' />"
            . "<input type='hidden' name='nonce' value='{$nonce}' />"
            . "<input type='hidden' name='test_payment_status' value='{$status}' />"
            . "<button type='submit' class='btn' style='background:{$color};color:#fff;border:none;padding:.75rem 1.5rem;cursor:pointer;border-radius:4px;'>{$label}</button>"
            . '</form>';

        return "<div class='payment-gateway-test-box' style='background:#fff3cd;border:1px solid #ffeba2;padding:1.5rem;border-radius:4px;margin:1rem 0;'>"
            . "<h4 style='margin-top:0;color:#856404;'>Environnement de test</h4>"
            . "<p>Règlement simulé de <strong>{$amount}</strong>.</p>"
            . "<div style='display:flex;gap:1rem;flex-wrap:wrap;'>"
            . $form('success', 'Simuler un paiement réussi', '#1e7e34')
            . $form('failure', 'Simuler un échec', '#bd2130')
            . '</div></div>';
    }

    public function processDocumentPayment(PaymentContext $ctx, array $postData): array
    {
        if (($postData['test_payment_status'] ?? '') !== 'success') {
            return ['success' => false, 'error' => 'Le simulateur de test a refusé la transaction.'];
        }

        return ['success' => true, 'error' => null, 'payment_reference' => 'test-' . bin2hex(random_bytes(8)), 'amount_cents' => $ctx->amountCents];
    }
}
