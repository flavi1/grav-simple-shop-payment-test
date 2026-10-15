# Guide du dépôt — plugin Grav `simple-shop-payment-test`

> Ce fichier est écrit pour l'agent (Claude) et pour le propriétaire. Il doit permettre de travailler sur ce plugin
> **sans accès immédiat aux autres dépôts** : contexte, décisions, historique et travail restant y sont recopiés.
> Mettre à jour les sections « À faire » et « Historique » à chaque lot de travail.

## Communication
- S'adresser au propriétaire en **français** ; code, commentaires et messages du plugin en français.
- Ne jamais reproduire de secret (clés Stripe, identifiants Colissimo, mots de passe SMTP, sel Grav). Ce dépôt est **public** :
  aucune clé, même de test, ne doit y entrer (configuration réelle : `user/config/plugins/simple-shop-payment-test.yaml` du site, jamais versionnée).
- Ne pas supprimer de fichiers à la place du propriétaire : les lister dans « À supprimer par le propriétaire » ci-dessous.

## Rôle du plugin

Passerelle de paiement **simulée** pour `simple-shop` (identifiant `test_gateway`, libellé « Mode de Test (Simulateur de
Paiement) ») : permet de parcourir le tunnel jusqu'à la commande sans prestataire réel. Utilisée par les tests de simple-shop
(`tests/support/bootstrap.php` l'enregistre) et activée sur le site de test.

**Ne jamais l'activer sur un site en production** : elle « encaisse » sans paiement. Désactivée par défaut depuis l'installation
partagée (`enabled: false`), chaque site de test l'active dans sa configuration.

## Famille de plugins

Extraits le 15/10/2026 du dépôt du site `senonevero.flavieng.com` (`private/grav/plugins/<nom>`), historique Git conservé
(commits ne touchant que ce dossier ; clés Stripe de test présentes dans d'anciennes versions remplacées par `CLE-STRIPE-RETIREE`).

| Dossier du plugin | Dépôt | Rôle |
|---|---|---|
| `mvc` | [flavi1/grav-mvc](https://github.com/flavi1/grav-mvc) | routes → contrôleurs (`Request`, `Response`, `AbstractController`) ; requis par simple-shop |
| `simple-shop` | [flavi1/grav-simple-shop](https://github.com/flavi1/grav-simple-shop) | boutique : panier, tunnel sans JavaScript, OTP, livraison, taxes, commandes, factures Factur-X, API Admin2 |
| `simple-shop-carrier-colissimo` | [flavi1/grav-simple-shop-carrier-colissimo](https://github.com/flavi1/grav-simple-shop-carrier-colissimo) | transporteur Colissimo (grille Facilité, points de retrait) |
| `simple-shop-payment-stripe` | [flavi1/grav-simple-shop-payment-stripe](https://github.com/flavi1/grav-simple-shop-payment-stripe) | paiement Stripe (formulaire intégré ou page hébergée), webhook |
| `simple-shop-payment-test` **(ce dépôt)** | [flavi1/grav-simple-shop-payment-test](https://github.com/flavi1/grav-simple-shop-payment-test) | passerelle de paiement simulée (sites de test) |
| — | [flavi1/senonevero.flavieng.com](https://github.com/flavi1/senonevero.flavieng.com) (privé) | site d'origine : thème `bookstore` (type de produit `book`), configuration, pages |

## Installation sur le serveur (hébergement mutualisé multi-domaines)

- Grav 2.0 est installé **une fois** dans `/shared/grav-2/` et sert plusieurs domaines (`/domains/<domaine>/private/grav` = dossier
  `user://` de chaque site, routeur global `www/index.php`, routeur de domaine `shared/manager/grav/Domain.php`).
- Ce plugin est déployé dans **`/shared/grav-2/user/plugins/simple-shop-payment-test/`**, partagé par tous les domaines.
- Grav cherche un plugin d'abord dans le dossier du site (`user://plugins`, soit `/domains/<domaine>/private/grav/plugins/simple-shop-payment-test`),
  **puis** dans le dossier partagé. Une copie restée dans un site masque donc la version partagée : la supprimer de ce site
  pour qu'il utilise la version partagée.
- **Désactivé par défaut** (`enabled: false` dans `simple-shop-payment-test.yaml`), puisque le dossier est commun à tous les domaines : chaque site
  l'active dans sa propre configuration (`user/config/plugins/simple-shop-payment-test.yaml` : `enabled: true`, ou Admin2 > Plugins).
- Fichiers publics : le routeur de domaine ne sert depuis `/user/plugins/…` que les ressources statiques (CSS, JS, images, polices) ;
  `.php`, `.yaml`, `.md`, `.json`, `.lock`… répondent 403.

## Déploiement (GitHub Actions → FTP)

Chaque push sur `main` lance `.github/workflows/deploy.yml` :
1. **Contrôle** : `php -l` sur tous les fichiers PHP (hors bibliothèques tierces).
2. **Envoi FTP** (action `SamKirkland/FTP-Deploy-Action`) vers `/shared/grav-2/user/plugins/simple-shop-payment-test/` : seuls les fichiers
   **modifiés depuis le dernier envoi** partent (état mémorisé sur le serveur dans `.ftp-deploy-sync-state.json`) ; un fichier
   supprimé du dépôt est supprimé du serveur ; les fichiers que le dépôt n'a jamais envoyés ne sont **jamais** touchés.
   Exclus : `.git*`, `.github/`, `tests/`, `docs/`, `AGENTS.md`.
3. Relance à la main possible : onglet *Actions* → *Déploiement FTP* → *Run workflow*.

**Mise en place (une fois par dépôt)** — GitHub → dépôt `flavi1/grav-simple-shop-payment-test` → *Settings* → *Secrets and variables* → *Actions* :
- onglet *Secrets* → *New repository secret*, trois fois :
  - `FTP_SERVER` : hôte FTP de l'hébergement (ex. `ftp.clusterXXX.hosting.ovh.net`, voir l'espace client OVH → Hébergement → *FTP - SSH*) ;
  - `FTP_USERNAME` : identifiant FTP ;
  - `FTP_PASSWORD` : mot de passe FTP.
- onglet *Variables* (facultatif, valeurs par défaut entre parenthèses) :
  - `FTP_PLUGINS_DIR` (`/shared/grav-2/user/plugins/`) : dossier des plugins **tel que vu à la connexion FTP**, avec `/` final ;
  - `FTP_PROTOCOL` (`ftps`) : mettre `ftp` si le serveur refuse le FTP chiffré ;
  - `FTP_PORT` (`21`).
- Avec l'outil `gh` : `gh secret set FTP_SERVER -R flavi1/grav-simple-shop-payment-test` (idem pour les deux autres ; la valeur est demandée, rien n'est affiché).

Premier envoi : tous les fichiers partent (quelques minutes). Si le dossier existait déjà (copie manuelle), il est complété et
mis à jour, sans suppression de fichiers inconnus. Vérifier ensuite par FTP la présence de `simple-shop-payment-test.php` dans le dossier attendu.

## Conventions (reprises du site d'origine)
- Fonctionnement **sans JavaScript** côté client public (hors option Stripe « formulaire intégré ») ; JavaScript admis côté Admin2.
- Un contrôleur retourne toujours une `Response` ; pas de `exit`, pas d'accès direct à `$_POST`/`$_GET`/`$_SERVER` (passer par `Request`).
- Écriture de fichiers partagés : verrou + fichier temporaire + `rename`.
- Pas de `innerHTML` avec des données dans le JavaScript.
- Tester en ligne de commande ce qui peut l'être (`php -l`, scripts de `tests/`) ; signaler explicitement ce qui n'est testable
  qu'après déploiement (Admin2, Stripe réel, service Colissimo réel).
- Commits distincts par étape, poussés directement sur `main` (le déploiement suit).

## À faire
- Rien de prévu. À la mise en production d'un site, vérifier que ce plugin y est **désactivé**.

## Historique
- `git log` : commits d'origine (messages en français, un par étape).
- `docs/HISTORIQUE.md` : ce qui a été **délibérément** fait pour ce plugin (repris de `DONE.md` du site), avec les preuves.
