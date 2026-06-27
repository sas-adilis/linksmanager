# Links Manager

Module PrestaShop qui permet de gérer des **blocs de liens** affichés dans
différents hooks de la page (pied de page, etc.). Chaque bloc regroupe des liens
vers des pages CMS, des produits ou des URL personnalisées.

> À l'origine basé sur le module *block links* d'IQIT-COMMERCE, adapté par Adilis.

## Principe

Le module implémente `WidgetInterface` : chaque bloc est rattaché à un hook et
rendu via `renderWidget()`. Les blocs sont gérés en back-office depuis le
contrôleur **`AdminLinkWidget`** (onglet « Links Manager »).

## Fonctionnalités

- **Blocs de liens** multiples, rattachés au hook de son choix.
- **Liens variés** : pages CMS, produits, ou liens personnalisés (label + URL).
- **Multilingue** : nom de bloc et libellés par langue.
- **Templates par hook** : un template spécifique `linksmanager-{hook}.tpl` (dans
  le thème ou le module) est utilisé s'il existe, sinon le template par défaut.
- **Cache** géré par hook (invalidé à l'enregistrement).
- **`reset()` surchargé** : rejoue l'installation sans supprimer les blocs existants.

## Utilisation

Le bloc peut être affiché :

- via le **hook** auquel il est rattaché ;
- ou explicitement dans un template : `{widget name='linksmanager' hook='displayFooter'}`.

## Architecture

| Élément | Rôle |
|---|---|
| `linksmanager.php` | Classe principale : widget, sélection de template par hook, cache. |
| `src/LinkBlock.php` | Modèle d'un bloc de liens. |
| `src/LinkBlockRepository.php` | Accès BDD (création/suppression des tables, lecture par hook). |
| `src/LinkBlockPresenter.php` | Mise en forme des liens d'un bloc pour le front. |
| `controllers/admin/AdminLinkWidgetController.php` | CRUD des blocs en back-office. |
| `views/templates/hook/linksmanager.tpl` | Template par défaut d'un bloc. |
| `translations/fr.php` / `en.php` | Traductions. |

## Configuration

La page de configuration du module redirige vers le contrôleur `AdminLinkWidget`.

## Compatibilité

- Auteur : **Adilis** (base IQIT-COMMERCE) — version **1.4.1**.
- Système de traduction **legacy** (`isUsingNewTranslationSystem()` = false).
