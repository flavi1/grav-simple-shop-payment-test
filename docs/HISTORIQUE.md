# Historique — ce qui a été délibérément fait (simple-shop-payment-test)

> Repris le 15/10/2026 de `private/prompts/DONE.md` du site d'origine (senonevero.flavieng.com), en ne gardant que ce qui concerne
> ce plugin. « Preuve » = fichier, test ou procédure qui le montre. Les numéros (A1, T4, O6, Q9…) sont ceux des anciens fichiers de
> suivi du site ; ils ne renvoient à rien dans ce dépôt mais sont gardés pour pouvoir retrouver une discussion.

| Point | État | Preuve |
|---|---|---|
| Passerelle simulée (`test_gateway`) | Fait (août 2026, commit « conception ») | `classes/Gateways/PaymentGatewayTest.php` |
| Utilisée par le test du tunnel de simple-shop | Fait (14/10/2026) | `simple-shop/tests/support/bootstrap.php`, `tests/checkout-flow.php` |
