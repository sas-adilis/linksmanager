# Checkout Terms

Module PrestaShop qui permet de gérer, depuis le back-office, un nombre illimité
de cases à cocher « à approuver » affichées pendant le tunnel de commande
(sur le modèle de la case « conditions générales de vente »).

## Principe

Chaque case est injectée dans le checkout via le hook natif
**`termsAndConditions`**. Le client doit cocher toutes les cases actives avant
de pouvoir valider sa commande — PrestaShop gère lui-même ce contrôle.

## Fonctionnalités

- Création/édition/suppression des cases via une **Helper List** standard
  (Clients → Checkout Terms dans le back-office).
- **Label multilingue** par case, HTML basique autorisé.
- **Lien optionnel vers une page CMS** : entourez une portion du label de
  `[crochets]` pour la transformer en lien vers la page CMS choisie.
  Ex. : `J'accepte les [conditions générales de vente].`
- **Activation/désactivation** de chaque case (colonne « Affichée »).
- **Positionnement** : ordre d'affichage paramétrable, ajout automatique en fin
  de liste, ré-indexation après suppression.

## Données

Deux tables :

- `checkoutterm` — `id_checkoutterm`, `id_cms`, `active`, `position`, dates.
- `checkoutterm_lang` — label par langue et par boutique.

## Configuration

La page de configuration du module redirige directement vers le contrôleur
`AdminCheckoutTerms` (la Helper List). Aucun réglage global : tout se passe au
niveau de chaque case.

## Compatibilité

- PrestaShop **1.7.6.0** → version courante.
- Auteur : **Adilis** — version **1.0.0**.
