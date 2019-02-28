<?php
/**
 * RocketWeb
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the GNU General Public License (GPL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * https://www.gnu.org/licenses/gpl-3.0.en.html
 *
 * @category  RocketWeb
 * @package   RocketWeb_ContentUpdate
 * @copyright Copyright (c) 2016-2019 RocketWeb (http://rocketweb.com)
 * @license   https://www.gnu.org/licenses/gpl-3.0.en.html  GNU General Public License (GPL 3.0)
 * @author    Rocket Web Inc.
 */
namespace RocketWeb\ContentUpdate\Setup;

use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Store\Model\StoreManager;
use Magento\Store\Model\App\Emulation;
use Magento\Cms\Model\Page;
use Magento\Cms\Model\PageFactory;
use Magento\Cms\Model\ResourceModel\Page\CollectionFactory as PageCollectionFactory;
use Magento\Cms\Model\Block;
use Magento\Cms\Model\BlockFactory;
use Magento\Cms\Model\ResourceModel\Block\CollectionFactory as BlockCollectionFactory;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Email\Model\TemplateFactory;
use Magento\Framework\App\TemplateTypesInterface;

/**
 * Class HelperSetup
 */
class HelperSetup
{
    /**
     * @var ModuleDataSetupInterface
     */
    protected $setup;

    /**
     * @var StoreManager
     */
    protected $storeManager;

    /**
     * @var Emulation
     */
    protected $emulation;

    /**
     * @var PageFactory
     */
    protected $pageFactory;

    /**
     * @var PageCollectionFactory
     */
    protected $pageCollectionFactory;

    /**
     * @var BlockFactory
     */
    protected $blockFactory;

    /**
     * @var BlockCollectionFactory
     */
    protected $blockCollectionFactory;

    /**
     * @var \Magento\Widget\Model\Widget\InstanceFactory
     */
    protected $widgetFactory;

    /**
     * @var \Magento\Widget\Model\ResourceModel\Widget\InstanceFactory
     */
    protected $widgetInstanceResourceFactory;

    /**
     * @var \Magento\Theme\Model\ResourceModel\Theme\CollectionFactory
     */
    protected $themeCollectionFactory;

    /**
     * @var TemplateFactory
     */
    protected $templateFactory;

    /**
     * @var ScopeConfigInterface
     */
    protected $_config;

    /**
     * @var \Magento\Framework\App\Config\Storage\WriterInterface
     */
    protected $configWriter;

    /**
     * @var DirectoryList
     */
    protected $directoryList;

    /**
     * Init
     *
     * @param ModuleDataSetupInterface $setup
     * @param StoreManager $storeManager
     * @param Emulation $emulation
     * @param PageFactory $pageFactory
     * @param PageCollectionFactory $pageCollectionFactory
     * @param BlockFactory $blockFactory
     * @param BlockCollectionFactory $blockCollectionFactory
     * @param \Magento\Widget\Model\Widget\InstanceFactory $widgetFactory
     * @param \Magento\Widget\Model\ResourceModel\Widget\InstanceFactory $widgetInstanceResourceFactory
     * @param \Magento\Theme\Model\ResourceModel\Theme\CollectionFactory $themeCollectionFactory
     * @param TemplateFactory $templateFactory
     * @param ScopeConfigInterface $config
     * @param \Magento\Framework\App\Config\Storage\WriterInterface $configWriter
     * @param DirectoryList $directoryList
     */
    public function __construct(
        ModuleDataSetupInterface $setup,
        StoreManager $storeManager,
        Emulation $emulation,
        PageFactory $pageFactory,
        PageCollectionFactory $pageCollectionFactory,
        BlockFactory $blockFactory,
        BlockCollectionFactory $blockCollectionFactory,
        \Magento\Widget\Model\Widget\InstanceFactory $widgetFactory,
        \Magento\Widget\Model\ResourceModel\Widget\InstanceFactory $widgetInstanceResourceFactory,
        \Magento\Theme\Model\ResourceModel\Theme\CollectionFactory $themeCollectionFactory,
        TemplateFactory $templateFactory,
        ScopeConfigInterface $config,
        \Magento\Framework\App\Config\Storage\WriterInterface $configWriter,
        DirectoryList $directoryList
    ) {
        $this->setup = $setup;
        $this->storeManager = $storeManager;
        $this->emulation = $emulation;
        $this->pageFactory = $pageFactory;
        $this->pageCollectionFactory = $pageCollectionFactory;
        $this->blockFactory = $blockFactory;
        $this->blockCollectionFactory = $blockCollectionFactory;
        $this->widgetFactory = $widgetFactory;
        $this->widgetInstanceResourceFactory = $widgetInstanceResourceFactory;
        $this->themeCollectionFactory = $themeCollectionFactory;
        $this->templateFactory = $templateFactory;
        $this->configWriter = $configWriter;
        $this->_config = $config;
        $this->directoryList = $directoryList;
    }

    /**
     * Create a CMS page
     *
     * @param string $identifier
     * @param array $data
     * @param array $stores the CMS page is assigned to specific Store IDs
     */
    public function createCmsPage($identifier, $data, $stores = [])
    {
        if (!(is_array($stores) && count($stores))) {
            throw new \InvalidArgumentException(
                "CMS page must have at least one store id. Empty stores variable."
            );
        }
        /** @var \Magento\Cms\Model\Page $page */
        $page = $this->pageFactory->create();
        $page->setIdentifier($identifier);
        foreach ($data as $key => $value) {
            $page->setData($key, $value);
        }
        $page->setStores($stores);
        $page->save();
    }

    /**
     * Create / Update a CMS page
     *
     * @param string $identifier
     * @param array $data
     * @param array|int $storeIds store(s) ID
     * @throws \Exception
     */
    public function updateCmsPage($identifier, $data, $storeIds)
    {
        if (!is_array($storeIds)) {
            $storeIds = [$storeIds];
        }

        /** @var \Magento\Cms\Model\ResourceModel\Page\Collection $collection */
        $collection = $this->pageCollectionFactory->create();
        $collection->addStoreFilter($storeIds);
        $collection->addFieldToFilter('identifier', $identifier);
        if ($collection->getSize() <= 0) {
            $this->createCmsPage($identifier, $data, $storeIds);
            return;
        }
        /** @var Page $page */
        $page = $collection->getFirstItem();

        foreach ($data as $key => $value) {
            $page->setData($key, $value);
        }

        $page->save();
    }

    /**
     * Delete a CMS page
     *
     * @param string $identifier
     * @param int $storeId
     */
    public function deleteCmsPage($identifier, $storeId)
    {
        /** @var \Magento\Cms\Model\ResourceModel\Page\Collection $collection */
        $collection = $this->pageCollectionFactory->create();
        $collection->addStoreFilter([$storeId]);
        $collection->addFieldToFilter('identifier', $identifier);
        $page = $collection->getFirstItem();
        if ($page->getId() > 0) {
            /** @var Page $page */
            $page = $this->pageFactory->create()
                ->load($page->getId());
            if ($page->getId() > 0) {
                $page->delete();
            }
        }
    }

    /**
     * Create a CMS block
     *
     * @param string $identifier
     * @param array $data
     * @param array $stores the CMS block is assigned to specific Store IDs
     */
    public function createCmsBlock($identifier, $data, $stores = [])
    {
        if (!(is_array($stores) && count($stores))) {
            throw new \InvalidArgumentException(
                "CMS block must have at least one store id. Empty stores variable."
            );
        }
        /** @var \Magento\Cms\Model\Block $block */
        $block = $this->blockFactory->create();
        $block->setIdentifier($identifier);
        foreach ($data as $key => $value) {
            $block->setData($key, $value);
        }
        $block->setStores($stores);
        $block->save();
    }

    /**
     * Create / Update a CMS block
     *
     * @param string $identifier
     * @param array $data
     * @param array|int $storeIds store ID to load the block
     * @throws \Exception
     */
    public function updateCmsBlock($identifier, $data, $storeIds)
    {
        if (!is_array($storeIds)) {
            $storeIds = [$storeIds];
        }

        /** @var \Magento\Cms\Model\ResourceModel\Block\Collection $collection */
        $collection = $this->blockCollectionFactory->create();
        $collection->addStoreFilter($storeIds);
        $collection->addFieldToFilter('identifier', $identifier);
        if ($collection->getSize() <= 0) {
            $this->createCmsBlock($identifier, $data, $storeIds);
            return;
        }
        /** @var Block $block */
        $block = $collection->getFirstItem();

        foreach ($data as $key => $value) {
            $block->setData($key, $value);
        }

        $block->save();
    }

    /**
     * Get CMS block
     *
     * @param string $identifier
     * @param array|int $storeIds
     * @return bool|Block
     */
    public function getCmsBLock($identifier, $storeIds)
    {
        if (!is_array($storeIds)) {
            $storeIds = [$storeIds];
        }

        /** @var \Magento\Cms\Model\ResourceModel\Block\Collection $collection */
        $collection = $this->blockCollectionFactory->create();
        $collection->addStoreFilter($storeIds);
        $collection->addFieldToFilter('identifier', $identifier);
        /** @var Block $block */
        $block = $collection->getFirstItem();
        if ($block->getId() <= 0) {
            return false;
        }
        return $block;
    }

    /**
     * Delete a CMS block
     *
     * @param string $identifier
     * @param int $storeId
     */
    public function deleteCmsBlock($identifier, $storeId)
    {
        /** @var \Magento\Cms\Model\ResourceModel\Block\Collection $collection */
        $collection = $this->blockCollectionFactory->create();
        $collection->addStoreFilter([$storeId]);
        $collection->addFieldToFilter('identifier', $identifier);
        $block = $collection->getFirstItem();
        if ($block->getId() > 0) {
            /** @var Block $block */
            $block = $this->blockFactory->create()
                ->load($block->getId());
            if ($block->getId() > 0) {
                $block->delete();
            }
        }
    }

    /**
     * Create / Update widget
     *
     * @param array|int $storeIds
     * @param array $params
     */
    public function addWidget($storeIds, $params)
    {
        if (!is_array($storeIds)) {
            $storeIds = [$storeIds];
        }

        $pageGroupConfig = [
            'pages' => [
                'block' => '',
                'for' => 'all',
                'layout_handle' => 'default',
                'template' => 'widget/static_block/default.phtml',
                'page_id' => '',
            ],
            'all_pages' => [
                'block' => '',
                'for' => 'all',
                'layout_handle' => 'default',
                'template' => 'widget/static_block/default.phtml',
                'page_id' => '',
            ],
            'anchor_categories' => [
                'entities' => '',
                'block' => '',
                'for' => 'all',
                'is_anchor_only' => 0,
                'layout_handle' => 'catalog_category_view_type_layered',
                'template' => 'widget/static_block/default.phtml',
                'page_id' => '',
            ],
            'virtual_products' => [
                'entities' => '',
                'block' => '',
                'for' => 'specific',
                'layout_handle' => 'catalog_product_view_type_virtual',
                'template' => 'widget/static_block/default.phtml',
                'page_id' => '',
            ],
        ];

        if (!isset($params['widget'])) {
            $params['widget'] = [];
        }

        if (isset($params['widget']['block_id'])) {
            $block = $this->getCmsBLock($params['widget']['block_id'], $storeIds);
            if ($block === false || (is_object($block) && $block->getId() <= 0)) {
                return;
            }
            $params['widget']['block_id'] = $block->getId();
        }

        /** @var \Magento\Widget\Model\Widget\Instance $widgetInstance */
        $widgetInstance = $this->widgetFactory->create();

        $code = $params['type_code'];
        $themeId = $this->themeCollectionFactory->create()->getThemeByFullPath($params['theme_path'])->getId();

        $type = $widgetInstance->getWidgetReference('code', $code, 'type');
        $pageGroup = [];
        $group = $params['page_group'];
        $pageGroup['page_group'] = $group;
        $pageGroup[$group] = array_merge($pageGroupConfig[$group], $params['group_data']);

        $widgetInstance->setType($type)->setCode($code)->setThemeId($themeId);
        $widgetInstance->setTitle($params['title'])
            ->setStoreIds($storeIds)
            ->setWidgetParameters($params['widget'])
            ->setPageGroups([$pageGroup]);
        if (isset($params['sort_order'])) {
            $widgetInstance->setSortOrder($params['sort_order']);
        }

        $widgetInstance->save();
    }

    /**
     * Delete widget
     *
     * @param string $title
     */
    public function deleteWidgetByTitle($title)
    {
        /** @var \Magento\Widget\Model\Widget\Instance $widgetInstance */
        $widgetInstance = $this->widgetFactory->create();
        $widgetInstance->load($title, 'title');
        if ($widgetInstance->getId() > 0) {
            $widgetInstanceId = $widgetInstance->getId();
            $widgetInstance->delete();

            // Clean table `widget_instance_page` and table `widget_instance_page_layout`

            /** @var \Magento\Widget\Model\ResourceModel\Widget\Instance $widgetInstanceResource */
            $widgetInstanceResource = $this->widgetInstanceResourceFactory->create();
            $connection = $widgetInstanceResource->getConnection();

            $select = $connection->select()->from(
                $connection->getTableName('widget_instance_page'),
                ['page_id' => 'page_id']
            )->where(
                'instance_id = :instance_id'
            );
            $bind = [':instance_id' => $widgetInstanceId];
            $pageIds = $connection->fetchCol($select, $bind);

            $inCond = $connection->prepareSqlCondition('instance_id', ['eq' => $widgetInstanceId]);
            $connection->delete($widgetInstanceResource->getTable('widget_instance_page'), $inCond);

            if (is_array($pageIds) && !empty($pageIds)) {
                $inCond = $connection->prepareSqlCondition('page_id', ['in' => $pageIds]);
                $connection->delete($widgetInstanceResource->getTable('widget_instance_page_layout'), $inCond);
            }
        }
    }

    /**
     * Load email template
     *
     * @param string $code
     * @return \Magento\Email\Model\Template
     */
    public function getTemplateByCode($code)
    {
        /** @var \Magento\Email\Model\Template $template */
        $template = $this->templateFactory->create();
        $template->load($code, 'template_code');
        return $template;
    }

    /**
     * Remove comment lines and extra spaces
     * @see \Magento\Email\Model\AbstractTemplate::loadDefault()
     *
     * @param $text
     * @return string
     */
    public function cleanTemplateText($text)
    {
        $text = trim(preg_replace('#\{\*.*\*\}#suU', '', $text));
        return $text;
    }

    /**
     * Retrieve config value by path and scope.
     *
     * @param string $path
     * @param string $scopeType
     * @param null|string $scopeCode
     * @return mixed
     */
    public function getConfigValue(
        $path,
        $scopeType = ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
        $scopeCode = null
    ) {
        return $this->_config->getValue($path, $scopeType, $scopeCode);
    }

    /**
     * Retrieve config flag by path and scope
     *
     * @param string $path
     * @param string $scopeType
     * @param null|string $scopeCode
     * @return bool
     */
    public function isConfigSetFlag(
        $path,
        $scopeType = ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
        $scopeCode = null
    ) {
        return $this->_config->isSetFlag($path, $scopeType, $scopeCode);
    }

    /**
     * Save config value to storage
     *
     * @param string $path
     * @param mixed $value
     * @param string $scope
     * @param int $scopeId
     */
    public function saveConfigValue(
        $path,
        $value,
        $scope = ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
        $scopeId = 0
    ) {
        $this->configWriter->save($path, $value, $scope, $scopeId);
    }

    /**
     * Delete config value from storage
     *
     * @param string $path
     * @param string $scope
     * @param int $scopeId
     */
    public function deleteConfigValue(
        $path,
        $scope = ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
        $scopeId = 0
    ) {
        $this->configWriter->delete($path, $scope, $scopeId);
    }

    /**
     * Get website id by website code
     *
     * @param string $code
     * @return int
     * @throws NoSuchEntityException
     */
    public function getWebsiteId($code)
    {
        $websites = $this->storeManager->getWebsites(true, true);
        /** @var \Magento\Store\Model\Website $website */
        foreach ($websites as $c => $website) {
            if ($website->getCode() == $code) {
                return $website->getId();
            }
        }
        throw new NoSuchEntityException("Can't find website with code: " . $code);
    }

    /**
     * Get website code by website id
     *
     * @param int $websiteId
     * @return mixed|string
     */
    public function getWebsiteCode($websiteId)
    {
        return $this->storeManager->getWebsite($websiteId)->getCode();
    }

    /**
     * Get store id by store code
     *
     * @param string $storeCode
     * @return int
     */
    public function getStoreId($storeCode)
    {
        return $this->storeManager->getStore($storeCode)->getId();
    }

    /**
     * Get store code by store id
     *
     * @param int $storeId
     * @return string
     */
    public function getStoreCode($storeId)
    {
        return $this->storeManager->getStore($storeId)->getCode();
    }

    /**
     * Start store emulation
     *
     * @param string $storeCode
     */
    public function startEmulation($storeCode)
    {
        $storeId = $this->storeManager->getStore($storeCode)->getId();
        $this->emulation->startEnvironmentEmulation($storeId);
    }

    /**
     * End store emulation
     */
    public function endEmulation()
    {
        $this->emulation->stopEnvironmentEmulation();
    }

    /**
     * Copy file from var/content_images to pub/media/wysiwyg
     *
     * @param string $source
     * @param string $destination
     * @return void
     */
    public function copyFile($source, $destination)
    {
        $source = trim($source, "/");
        $destination = trim($destination, "/");
        if (empty($source) || empty($destination)) {
            return;
        }

        $sourceDir = $this->directoryList->getPath(DirectoryList::VAR_DIR) . '/content_images';
        $destinationDir = $this->directoryList->getPath(DirectoryList::MEDIA) . '/wysiwyg';

        $sourceArr = explode("/", $source);
        if (count($sourceArr) > 1) {
            array_pop($sourceArr);
            @mkdir($destinationDir . '/' . implode("/", $sourceArr), 0775, true);
        }

        @copy($sourceDir . '/' . $source, $destinationDir . '/' . $destination);
    }
}
