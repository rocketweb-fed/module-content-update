<?php
/**
 * RocketWeb
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * @category  RocketWeb
 * @package   RocketWeb_ContentUpdate
 * @copyright Copyright (c) 2016-2017 RocketWeb (http://rocketweb.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @author    Rocket Web Inc.
 */
namespace RocketWeb\ContentUpdate\Setup;

use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\UpgradeDataInterface;
use RocketWeb\ContentUpdate\Setup\HelperSetup;
use RocketWeb\ContentUpdate\Setup\HelperSetupFactory;
use Magento\Cms\Model\Page;
use Magento\Cms\Model\Block;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\Store;
use Magento\Framework\App\TemplateTypesInterface;

class UpgradeData implements UpgradeDataInterface
{
    /**
     * @var HelperSetupFactory
     */
    private $helperSetupFactory;

    /**
     * @param HelperSetupFactory $helperSetupFactory
     */
    public function __construct(HelperSetupFactory $helperSetupFactory)
    {
        $this->helperSetupFactory = $helperSetupFactory;
    }

    /**
     * Examples of HelperSetup usage
     *
     * @param ModuleDataSetupInterface $setup
     * @param ModuleContextInterface $context
     * @return void
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function upgrade(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();
        /** @var HelperSetup $helperSetup */
        $helperSetup = $this->helperSetupFactory->create(['setup' => $setup]);

        if (version_compare($context->getVersion(), '1.0.1') < 0) {
            //$this->createExamplePage($helperSetup);
        }

        if (version_compare($context->getVersion(), '1.0.2') < 0) {
            //$this->updateExamplePage($helperSetup);
        }

        if (version_compare($context->getVersion(), '1.0.3') < 0) {
            // Delete this example's CMS page
            //$helperSetup->deleteCmsPage('other-test-page', $helperSetup->getStoreId('admin'));
        }

        if (version_compare($context->getVersion(), '1.0.4') < 0) {
            //$this->createExampleBlock($helperSetup);
        }

        if (version_compare($context->getVersion(), '1.0.5') < 0) {
            //$this->updateExampleBlock($helperSetup);
        }

        if (version_compare($context->getVersion(), '1.0.6') < 0) {
            // Delete this example's CMS block
            //$helperSetup->deleteCmsBlock('dolor-sit-amet-other', $helperSetup->getStoreId('admin'));
        }

        if (version_compare($context->getVersion(), '1.0.7') < 0) {
            //$this->saveExampleConfiguration($helperSetup);
        }

        if (version_compare($context->getVersion(), '1.0.8') < 0) {
            //$this->updateExampleConfiguration($helperSetup);
        }

        if (version_compare($context->getVersion(), '1.0.9') < 0) {
            //$this->deleteExampleConfiguration($helperSetup);
        }

        if (version_compare($context->getVersion(), '1.0.10') < 0) {
            //$this->createExampleWidget($helperSetup);
        }

        if (version_compare($context->getVersion(), '1.0.11') < 0) {
            //$this->updateExampleEmailTemplate($helperSetup);
        }

        $setup->endSetup();
    }

    /**
     * Create a new CMS page
     *
     * @param HelperSetup $helperSetup
     */
    public function createExamplePage($helperSetup)
    {
        $storeId = $helperSetup->getStoreId('admin');
        $stores = [$storeId]; // Or $stores = = [$storeId, .. ]; to assign the page to more than one store

        $content = <<<EOD
<p>Lorem Ipsum is simply dummy text of the printing and typesetting industry.
Lorem Ipsum has been the industry's standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book.
It has survived not only five centuries, but also the leap into electronic typesetting, remaining essentially unchanged.
It was popularised in the 1960s with the release of Letraset sheets containing Lorem Ipsum passages,
and more recently with desktop publishing software like Aldus PageMaker including versions of Lorem Ipsum.</p>
EOD;

        $layoutUpdateXml = <<<EOD
<referenceContainer name="content"></referenceContainer>
EOD;

        $data = [
            'title'             => 'Test Page',
            'page_layout'       => '1column',
            'meta_title'        => '',
            'meta_keywords'     => '',
            'meta_description'  => '',
            //'identifier'      => 'other-page', // Only to update old identifier if we want to
            'content_heading'   => '',
            'content'           => $content,
            'is_active'         => Page::STATUS_ENABLED,
            'sort_order'        => 0,
            'layout_update_xml' => $layoutUpdateXml
        ];

        $helperSetup->createCmsPage('test-page', $data, $stores);
    }

    /**
     * Update something in Lorem Ipsum CMS page
     *
     * @param HelperSetup $helperSetup
     */
    public function updateExamplePage($helperSetup)
    {
        $storeId = $helperSetup->getStoreId('admin');

        $data = [
            'title'             => 'Test Page Changed Title',
            'identifier'        => 'other-test-page', // This time we change identifier
            'layout_update_xml' => '',
        ];

        $helperSetup->updateCmsPage('test-page', $data, $storeId);
    }

    /**
     * Create a new CMS block
     *
     * @param HelperSetup $helperSetup
     */
    public function createExampleBlock($helperSetup)
    {
        $storeId = $helperSetup->getStoreId('admin');
        $stores = [$storeId]; // Or $stores = = [$storeId, .. ]; to assign the block to more than one store

        $content = <<<EOD
<p>Lorem Ipsum is simply dummy text of the printing and typesetting industry.</p>
EOD;

        $data = [
            'title'        => 'Test Block',
            'content'      => $content,
            'is_active'    => Block::STATUS_ENABLED
        ];

        $helperSetup->createCmsBlock('test-block', $data, $stores);
    }

    /**
     * Update something in Test Block CMS Block
     *
     * @param HelperSetup $helperSetup
     */
    public function updateExampleBlock($helperSetup)
    {
        $storeId = $helperSetup->getStoreId('admin');

        $data = [
            'is_active'  => Block::STATUS_DISABLED,
            'identifier' => 'other-test-block', // This time we change identifier
        ];

        $helperSetup->updateCmsBlock('test-block', $data, $storeId);
    }

    /**
     * Create 3 configurations
     *
     * @param HelperSetup $helperSetup
     */
    public function saveExampleConfiguration($helperSetup)
    {
        // Set value to default Scope, to admin store implicitly
        $helperSetup->saveConfigValue(
            'rw_test/general/hello_world',
            'Hello!'
        );

        // Set value to Scope 'store', to store with id 1
        $helperSetup->saveConfigValue(
            'rw_test/general/hello_usa',
            'Hi!',
            ScopeInterface::SCOPE_STORE,
            Store::DISTRO_STORE_ID
        );

        // Can use to get store id
        // $storeId = $helperSetup->getStoreId('my store code');

        $websiteId = $helperSetup->getWebsiteId('base');
        if ($websiteId) {
            // Set value to Scope 'website', to website with id $websiteId
            $helperSetup->saveConfigValue(
                'rw_test/general/we_said_hello',
                '1',
                ScopeInterface::SCOPE_WEBSITES,
                $websiteId
            );
        }
    }

    /**
     * Do more with configuration
     *
     * @param HelperSetup $helperSetup
     */
    public function updateExampleConfiguration($helperSetup)
    {
        // Get value to Scope 'store', to store with id 1
        $usa = $helperSetup->getConfigValue(
            'rw_lorem_ipsum/general/hello_usa',
            ScopeInterface::SCOPE_STORE,
            $helperSetup->getStoreCode(Store::DISTRO_STORE_ID)
        );
        $usa .= " Lorem Ipsum";
        // Set value to Scope 'store', to store with id 1
        $helperSetup->saveConfigValue(
            'rw_lorem_ipsum/general/hello_usa',
            $usa,
            ScopeInterface::SCOPE_STORE,
            Store::DISTRO_STORE_ID
        );

        $websiteId = $helperSetup->getWebsiteId('base');
        if ($websiteId) {
            // Set value to Scope 'website', to website with id $websiteId
            /*$is = $helperSetup->isConfigSetFlag(
                'rw_lorem_ipsum/general/we_said_hello',
                ScopeInterface::SCOPE_WEBSITES,
                $helperSetup->getWebsiteCode($websiteId)
            );*/

            $helperSetup->saveConfigValue(
                'rw_lorem_ipsum/general/we_said_hello',
                '0',
                ScopeInterface::SCOPE_WEBSITES,
                $websiteId
            );
        }
    }

    /**
     * Delete example's configurations
     *
     * @param HelperSetup $helperSetup
     */
    public function deleteExampleConfiguration($helperSetup)
    {
        // Delete value of implicitly default Scope, of implicitly admin store
        $helperSetup->deleteConfigValue('rw_lorem_ipsum/general/hello_world');

        // Delete value of Scope 'store', of store with id 1
        $helperSetup->deleteConfigValue(
            'rw_lorem_ipsum/general/hello_usa',
            ScopeInterface::SCOPE_STORE,
            Store::DISTRO_STORE_ID
        );

        $websiteId = $helperSetup->getWebsiteId('base');
        if ($websiteId) {
            // Delete value of Scope 'website', of website with id $websiteId
            $helperSetup->deleteConfigValue(
                'rw_lorem_ipsum/general/we_said_hello',
                ScopeInterface::SCOPE_WEBSITES,
                $websiteId
            );
        }
    }

    /**
     * Example function to create a widget
     *
     * @param HelperSetup $helperSetup
     */
    public function createExampleWidget($helperSetup)
    {
        // This is an example function
        return;

        $content = <<<EOD
<div>Lorem Ipsum</div>
EOD;
        $data = [
            'title'        => 'Test Widget',
            'content'      => $content,
            'is_active'    => Block::STATUS_ENABLED
        ];
        $helperSetup->updateCmsBlock('test-block', $data, $helperSetup->getStoreId('admin'));

        $params = [
            'title'         => 'A brand new widget',
            'sort_order'    => '100',
            'type_code'     => 'cms_static_block',
            'theme_path'    => 'frontend/RocketWeb/mytheme',
            'page_group'    => 'virtual_products',
            'group_data' => [
                'block'         => 'parent_block_name_here',
                'for'           => 'specific',
                'layout_handle' => 'catalog_product_view_type_virtual', // any handler
                'entities'      => 999999 // Some product id
            ],
            'widget' => [
                'block_id' => 'test-block'
            ]
        ];

        $helperSetup->addWidget(
            [$helperSetup->getStoreId('admin')],
            $params
        );
    }

    /**
     * Example function to update transactional email template
     *
     * @param HelperSetup $helperSetup
     */
    public function updateExampleEmailTemplate($helperSetup)
    {
        // This is an example function
        return;

        $template = $helperSetup->getTemplateByCode('Lore Ipsum Forgot Password');
        if ($template->getId() <= 0) {
            return;
        }

        $text = <<<EOD
{{template config_path="design/email/header_template"}}

<p class="greeting">{{trans "%name," name=\$customer.name}}</p>
<p>{{trans "There was recently a request to change the password for your account."}}</p>
<p>{{trans "If you requested this change, set a new password here:"}}</p>

<table class="button" width="100%" border="0" cellspacing="0" cellpadding="0">
    <tr>
        <td>
            <table class="inner-wrapper" border="0" cellspacing="0" cellpadding="0" align="center">
                <tr>
                    <td align="center">
                        <a href="{{var this.getUrl(\$store,'customer/account/createPassword/',[_query:[id:\$customer.id,token:\$customer.rp_token],_nosid:1])}}" target="_blank">{{trans "Set a New Password"}}</a>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>

<p>{{trans "If you did not make this request, you can ignore this email and your password will remain the same."}}</p>

{{template config_path="design/email/footer_template"}}

EOD;

        $text = $helperSetup->cleanTemplateText($text);

        $variables = <<<EOD
{
"var customer.name":"Customer Name",
"var this.getUrl(\$store, 'customer/account/createPassword/', [_query:[id:\$customer.id, token:\$customer.rp_token]])":"Reset Password URL"
}
EOD;
        $variables = str_replace("\n", '', $variables);

        $data = [
            'template_text'            => $text,
            'template_styles'          => '',
            'template_type'            => TemplateTypesInterface::TYPE_HTML,
            'template_subject'         => '{{trans "Reset your %store_name password" store_name=$store.getFrontendName()}}',
            'orig_template_code'       => 'customer_password_forgot_email_template',
            'orig_template_variables'  => $variables,
        ];

        $template->addData($data);
        $template->save();
    }
}
