# RocketWeb_ContentUpdate
Set default payment method, Google Search Address in shipping address.

## Installation
Install using Composer
```
$ composer require rocketweb/module-content-update
$ bin/magento module:enable RocketWeb_ContentUpdate
$ bin/magento setup:upgrade
```
To install manually download the module contents into app/code/RocketWeb/ContentUpdate


## Configuration
Create new module **_ProjectNamespace_/ContentUpdate** in the `app/code` directory  using steps below:

> Replace _ProjectNamespace_ in the steps below with project or vendor namespace

1. Create `composer.json`
```json
{
    "name": "projectNamespace/module-content-update",
    "description": "Module for creating and updating static content using data scripts.",
    "type": "magento2-module",
    "version": "1.0.0",
    "license": "GPL-3.0",
    "authors": [
        {
            "name": "Company Name",
            "email": "company@email.com"
        }
    ],
    "autoload": {
        "files": [
            "registration.php"
        ],
        "psr-4": {
            "Projectnamespace\\UpgradeData\\": ""
        }
    }
}

```
2. Create `registration.php`
```php
<?php
\Magento\Framework\Component\ComponentRegistrar::register(
    \Magento\Framework\Component\ComponentRegistrar::MODULE,
    'ProjectNamespace_ContentUpdate',
    __DIR__
);
```

3. Create `etc/module.xml`

```xml
<?xml version="1.0"?>
<config xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:noNamespaceSchemaLocation="urn:magento:framework:Module/etc/module.xsd">
    <module name="ProjectNamespace_ContentUpdate" setup_version="1.0.0">
        <sequence>
            <module name="RocketWeb_ContentUpdate"/>
        </sequence>
    </module>
</config>
```

4. Clone `vendor/rocketweb/module-content-update/Setup/UpgradeData` to `app/code/ProjectNamespace/ContentUpdate/Setup/UpgradeData.php` 

5. Open `Setup/UpgradeData.php` and replace 
```
namespace RocketWeb\ContentUpdate\Setup
```
with

```
namespace ProjectNamespace\ContentUpdate\Setup
```
6. Clear example functions in UpgradeData.php and add your own (see reference in the next section)

7. Run 
```
bin/magento module:enable ProjectNamespace/ContentUpdate
bin/magento setup:upgrade
```

---
## Usage

### Adding update functions and triggering them
1. Open `ProjectNamespace_ContentUpdate/etc/module.xml`
2. Change setup_version attribute from x.y.z to x.y.z++ eg. 1.0.9 to 1.0.10
3. Open `ProjectNamespace_ContentUpdate/Setup/UpgradeData.php`
4. Scroll to the bottom of the file
5. Before the closing braces add your function using a unique name (createUIPage). Use instructions below as a reference for creating and updating various elements.
6. When done creating function scroll up and find $setup->endSetup();
7. The last entry before that line should look something like
```
if (version_compare($context->getVersion(), '1.0.9') < 0) {
            $this->someFunction($helperSetup);
        }
```
8. Duplicate this entry and update both setup version number (to match the one from module.xml) and function name (to the recently created one)
9. When done save the file and run `bin/magento setup:upgrade`
10. Go to the page/block you created to confirm it's working properly

> If you need to revert module's setup_version number while making adjustments you can do that by modifying a database entry in setup_module table. Make sure to revert both schema_version in data_version before running `magento setup:uprade`. Note that you can only change it via db until you commit.

### Update functions
#### Create a new CMS page
```php
public function createNewCmsPage($helperSetup)
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
        'title'             => 'Lorem Ipsum',
        'page_layout'       => '1column',
        'meta_title'        => '',
        'meta_keywords'     => '',
        'meta_description'  => '',
        //'identifier'      => 'other-lorem-ipsum', // Only to update old identifier if we want to
        'content_heading'   => '',
        'content'           => $content,
        'is_active'         => Page::STATUS_ENABLED,
        'sort_order'        => 0,
        'layout_update_xml' => $layoutUpdateXml
    ];

    $helperSetup->createCmsPage('lorem-ipsum', $data, $stores);
}
```

#### Update existing CMS page
```php
public function updateExistingCmsPage($helperSetup)
{
    $storeId = $helperSetup->getStoreId('admin');

    $data = [
        'title'             => 'Lorem Ipsum Changed Title',
        'identifier'        => 'other-lorem-ipsum', // This time we change identifier
        'layout_update_xml' => '',
    ];

    $helperSetup->updateCmsPage('lorem-ipsum', $data, $storeId);
}
```

#### Delete existing CMS page
```php
public function deleteExistingCmsPage($helperSetup)
{
    $storeId = $helperSetup->getStoreId('admin');

    $helperSetup->deleteCmsPage('other-lorem-ipsum', $storeId);
}
```

#### Create a new CMS block
```php
public function createNewCmsBlock($helperSetup)
{
    $storeId = $helperSetup->getStoreId('admin');
    $stores = [$storeId]; // Or $stores = = [$storeId, .. ]; to assign the block to more than one store

    $content = <<<EOD
<p>Lorem Ipsum is simply dummy text of the printing and typesetting industry.</p>
EOD;

    $data = [
        'title'        => 'Lorem Ipsum',
        'content'      => $content,
        'is_active'    => Block::STATUS_ENABLED
    ];

    $helperSetup->createCmsBlock('dolor-sit-amet', $data, $stores);
}
```

#### Update existing CMS Block
```php
public function updateExistingCmsBlock($helperSetup)
{
    $storeId = $helperSetup->getStoreId('admin');

    $data = [
        'is_active'  => Block::STATUS_DISABLED,
        'identifier' => 'dolor-sit-amet-other', // This time we change identifier
    ];

    $helperSetup->updateCmsBlock('dolor-sit-amet', $data, $storeId);
}
```

#### Delete existing CMS block
```php
public function deleteExistingCmsBlock($helperSetup)
{
    $storeId = $helperSetup->getStoreId('admin');

    $helperSetup->deleteCmsBlock('dolor-sit-amet-other', $storeId);
}
```

#### Create new configurations
```php
public function createConfiguration($helperSetup)
{
    // Set value to default Scope, to admin store implicitly
    $helperSetup->saveConfigValue(
        'rw_lorem_ipsum/general/hello_world',
        'Hello!'
    );

    // Set value to Scope 'store', to store with id 1
    $helperSetup->saveConfigValue(
        'rw_lorem_ipsum/general/hello_usa',
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
            'rw_lorem_ipsum/general/we_said_hello',
            '1',
            ScopeInterface::SCOPE_WEBSITES,
            $websiteId
        );
    }
}
```

#### Update existing configuration
```php
public function updateConfiguration($helperSetup)
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
```

#### Delete existing configuration
```php
public function deleteConfiguration($helperSetup)
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
```

#### Create a widget
```php
public function createNewWidget($helperSetup)
{
    // This is an example function
    return;

    $content = <<<EOD
<div>Lorem Ipsum</div>
EOD;
    $data = [
        'title'        => 'Lorem Ipsum',
        'content'      => $content,
        'is_active'    => Block::STATUS_ENABLED
    ];
    $helperSetup->updateCmsBlock('lorem_ipsum', $data, $helperSetup->getStoreId('admin'));

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
            'block_id' => 'lorem_ipsum'
        ]
    ];

    $helperSetup->addWidget(
        [$helperSetup->getStoreId('admin')],
        $params
    );
}
```

#### Update transactional email template
```php
public function updateEmailTemplate($helperSetup)
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
