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

class LinkBlockPresenter
{
    private $link;
    private $language;

    public function __construct(Link $link, Language $language)
    {
        $this->link = $link;
        $this->language = $language;
    }

    /**
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     */
    public function present(LinkBlock $cmsBlock): array
    {
        return array(
            'id' => (int)$cmsBlock->id,
            'title' => $cmsBlock->name[(int)$this->language->id],
            'hook' => (new Hook((int)$cmsBlock->id_hook))->name,
            'position' => $cmsBlock->position,
            'links' => $this->makeLinks($cmsBlock->content),
        );
    }

    /**
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     */
    public function makeLinks($content): array
    {
        foreach ($content as $key => $page) {
            if ($page['type'] == 'custom') {
                $content[$key]['data'] = $this->makeCustomLink($page);
            }
            if ($page['type'] == 'static') {
                $content[$key]['data'] = $this->makeStaticLink($page['id']);
            } elseif ($page['type'] == 'cms_category') {
                $content[$key]['data'] = $this->makeCmsCategoryLink($page['id']);
            } elseif ($page['type'] == 'cms_page') {
                $content[$key]['data'] = $this->makeCmsPageLink($page['id']);
            } elseif ($page['type'] == 'category') {
                $content[$key]['data'] = $this->makeCategoryLink($page['id']);
            }
        }
        return $content;
    }

    private function makeCategoryLink($id): array
    {
        $cmsLink = array();

        $cat = new Category((int)$id);

        if (null !== $cat->id) {
            $cmsLink = array(
                'title' => $cat->name[(int)$this->language->id],
                'description' => $cat->meta_description[(int)$this->language->id],
                'url' => $cat->getLink(),
            );
        }
        return $cmsLink;
    }

    private function makeCmsPageLink($cmsId): array
    {
        $cmsLink = array();

        $cms = new CMS((int)$cmsId);
        if (null !== $cms->id) {
            $cmsLink = array(
                'title' => $cms->meta_title[(int)$this->language->id],
                'description' => $cms->meta_description[(int)$this->language->id],
                'url' => $this->link->getCMSLink($cms),
                );
        }
        return $cmsLink;
    }

    /**
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     */
    private function makeCmsCategoryLink($cmsId): array
    {
        $cmsLink = array();

        $cms = new CMSCategory((int)$cmsId);
        if (null !== $cms->id) {
            $cmsLink = array(
                'title' => $cms->name[(int)$this->language->id],
                'description' => $cms->meta_description[(int)$this->language->id],
                'url' => $this->link->getCMSCategoryLink($cms),
                );
        }
        return $cmsLink;
    }

    private function makeCustomLink($page): array
    {
        return array(
                'title' => $page['title'][(int)$this->language->id],
                'url' => $page['url'][(int)$this->language->id],
        );
    }

    private function makeStaticLink($staticId): array
    {
        $meta = Meta::getMetaByPage($staticId, (int)$this->language->id);
        return array(
            'title' => $meta['title'],
            'description' => $meta['description'],
            'url' => $this->link->getPageLink($staticId, true),
            );
    }
}
