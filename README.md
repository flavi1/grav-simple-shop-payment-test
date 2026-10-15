# Simple Shop Payment Test

Passerelle de paiement **simulée** pour le plugin Grav [Simple Shop](https://github.com/flavi1/grav-simple-shop) : le client
« paie » sans prestataire, ce qui permet de tester le tunnel jusqu'à la commande, aux e-mails et à la facture.

**Réservée aux sites de test** : elle valide une commande sans encaissement. Désactivée par défaut ; l'activer dans la
configuration d'un site de test (`user/config/plugins/simple-shop-payment-test.yaml` : `enabled: true`).

Installation : dossier `user/plugins/simple-shop-payment-test/` (hébergement mutualisé : `/shared/grav-2/user/plugins/…`,
déployé par GitHub Actions, voir `AGENTS.md`). Requiert `simple-shop`.
