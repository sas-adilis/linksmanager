<?php
/**
 * 2017 IQIT-COMMERCE.COM
 *
 * NOTICE OF LICENSE
 *
 * This file is licenced under the Software License Agreement.
 * With the purchase or the installation of the software in your application
 * you accept the licence agreement
 *
 *  @author    IQIT-COMMERCE.COM <support@iqit-commerce.com>
 *  @copyright 2017 IQIT-COMMERCE.COM
 *  @license   Commercial license (You can not resell or redistribute this software.)
 *
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

use PrestaShop\PrestaShop\Core\Module\WidgetInterface;

require_once dirname(__FILE__).'/src/LinkBlockRepository.php';
require_once dirname(__FILE__).'/src/LinkBlock.php';
require_once dirname(__FILE__).'/src/LinkBlockPresenter.php';

class LinksManager extends Module implements WidgetInterface
{
    private $linkBlockPresenter;
    private $linkBlockRepository;
    protected $templateFile;


    public function __construct()
    {
        $this->name = 'linksmanager';
        $this->tab = 'front_office_features';
        $this->version = '1.4.0';
        $this->author = 'ADILIS';
        $this->bootstrap = true;
        $this->need_instance = 0;

        parent::__construct();

        $this->displayName = $this->trans('LINKSMANAGER - block links', [], 'Modules.Linksmanager.Admin');
        $this->description = $this->trans('Adds block with links in variouse hooks in your page', [], 'Modules.Linksmanager.Admin');

        $this->linkBlockRepository = new LinkBlockRepository(
            Db::getInstance(),
            $this->context->shop
        );
        $this->linkBlockPresenter = new LinkBlockPresenter(
            new Link(),
            $this->context->language
        );

        $this->templateFile = 'module:'.$this->name.'/views/templates/hook/'.$this->name.'.tpl';
    }

    public function install(): bool
    {
        return parent::install() && (bool) $this->linkBlockRepository->createTables();
    }

    public function isUsingNewTranslationSystem(): bool
    {
        return false;
    }

    public function uninstall(): bool
    {
        return
            parent::uninstall() &&
            $this->linkBlockRepository->dropTables();
    }

    /**
     * Reset du module : rejoue l'installation complète (hooks, tables,
     * etc.) sans supprimer les données existantes. Surcharge le
     * comportement par défaut de PrestaShop qui appelle
     * uninstall() + install() et ferait donc perdre les link blocks.
     */
    public function reset(): bool
    {
        return parent::uninstall() && $this->install();
    }

    public function installTab(): bool
    {
        $tab = new Tab();
        $tab->active = 1;
        $tab->class_name = "AdminLinkWidget";
        $tab->name = array();
        foreach (Language::getLanguages(true) as $lang) {
            $tab->name[$lang['id_lang']] = "Links Manager";
        }
        $tab->id_parent = (int)Tab::getIdFromClassName('AdminParentThemes');
        $tab->module = $this->name;
        return $tab->add();
    }

    /**
     * @throws PrestaShopException
     * @throws PrestaShopDatabaseException
     */
    public function uninstallTab(): bool
    {
        $id_tab = (int)Tab::getIdFromClassName('AdminLinkWidget');
        $tab = new Tab($id_tab);
        if (Validate::isLoadedObject($tab)) {
            return $tab->delete();
        }
        return true;
    }

    public function getContent()
    {
        Tools::redirectAdmin($this->context->link->getAdminLink('AdminLinkWidget'));
    }

    public function renderWidget($hookName = null, array $configuration = []): string
    {
        if ($hookName == null && isset($configuration['hook'])) {
            $hookName = $configuration['hook'];
        }

        $templateFile = $this->getTemplateForHook($hookName);

        if (!$this->isCached($templateFile, $this->getCacheId($hookName))) {
            $this->smarty->assign([
                'linkBlocks' => $this->getWidgetVariables($hookName, $configuration),
            ]);
        }

        return $this->fetch($templateFile, $this->getCacheId($hookName));
    }

    /**
     * Returns a hook-specific template if it exists, otherwise the default one.
     *
     * Looks for: linksmanager-{hookname}.tpl (lowercase)
     * In theme:  themes/sevens/modules/linksmanager/views/templates/hook/
     * In module: modules/linksmanager/views/templates/hook/
     *
     * @param string|null $hookName
     * @return string Smarty module: path
     */
    private function getTemplateForHook($hookName)
    {
        if (!empty($hookName)) {
            $hookTemplateName = $this->name . '-' . Tools::strtolower($hookName) . '.tpl';
            $hookTemplateFile = 'module:' . $this->name . '/views/templates/hook/' . $hookTemplateName;

            // Check in theme first, then in module directory
            $themePath = _PS_THEME_DIR_ . 'modules/' . $this->name . '/views/templates/hook/' . $hookTemplateName;
            $modulePath = _PS_MODULE_DIR_ . $this->name . '/views/templates/hook/' . $hookTemplateName;

            if (file_exists($themePath) || file_exists($modulePath)) {
                return $hookTemplateFile;
            }
        }

        return $this->templateFile;
    }

    /**
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     */
    public function getWidgetVariables($hookName = null, array $configuration = []): array
    {
        if ($hookName == null && isset($configuration['hook'])) {
            $hookName = $configuration['hook'];
        }

        $id_hook = Hook::getIdByName($hookName);
        $linkBlocks = $this->linkBlockRepository->getByIdHook($id_hook);

        $blocks = array();
        foreach ($linkBlocks as $block) {
            $blocks[] = $this->linkBlockPresenter->present($block, true);
        }

        return $blocks;
    }

    public function clearCache($template = null, $cache_id = null, $compile_id = null)
    {
        parent::_clearCache($this->templateFile);

        // Also clear cache for hook-specific templates
        $hookTemplates = glob(_PS_MODULE_DIR_ . $this->name . '/views/templates/hook/' . $this->name . '-*.tpl');
        if ($hookTemplates) {
            foreach ($hookTemplates as $tplPath) {
                $tplName = basename($tplPath);
                parent::_clearCache('module:' . $this->name . '/views/templates/hook/' . $tplName);
            }
        }
    }

    public function getCacheId($hookName = null): string
    {
        return parent::getCacheId() . '|' . $hookName;
    }

    private function addNameArrayToPost()
    {
        $languages = Language::getLanguages();
        $names = array();
        foreach ($languages as $lang) {
            if ($name = Tools::getValue('name_'.(int)$lang['id_lang'])) {
                $names[(int)$lang['id_lang']] = $name;
            }
        }
        $_POST['name_link_block'] = $names;
    }
}
