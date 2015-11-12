# Asset-Master

## Introduction

The Asset Master package provides an optimization option for bundling assets within Concrete5.

## Current C5 Asset Caching

The way that the current caching is set up, is that all assets which can be combined are combined on a page by page basis. Take a look at the following table. With the current implementation, since each of these pages have a unique combination of scripts, each would be compiled into a unique bundle and downloaded.

| Page One      | Page Two      | Page Three    | Page Four    | Total Pages  |
|---------------|---------------|---------------|--------------|--------------|
| jquery.js     | jquery.js     | jquery.js     | jquery.js    | 100%         |
| main.js       | main.js       | main.js       | main.js      | 100%         |
| autonav.js    | autonav.js    | autonav.js    | autonav.js   | 100%         |
| imageslider.js| imageslider.js|               |              | 50%          |
|               |               | googlemap.js  | googlemap.js | 50%          |
| content.js    |               |               |              | 25%          |
|               | pagelist.js   |               |              | 25%          |  
|               |               | form.js       |              | 25%          | 
|               |               |               | lightbox.js  | 25%          |
| bundle1.js    | bundle2.js    | bundle3.js    | bundle4.js   | 25%          |

## Asset Master Asset Caching

Asset Master caching allows you as a developer to make smart decisions about which assets you want to bundle together. Using the above example, you could select to bundle these assets:

* jquery.js
* main.js
* autonav.js
* imageslider.js
* googlemap.js

They would be included as a single download, which can be cached in your users browser, and then allow C5 to handle page specific javascript like normal:

| Page One      | Page Two      | Page Three    | Page Four    | Total Pages  |
|---------------|---------------|---------------|--------------|--------------|
| content.js    |               |               |              | 25%          |
|               | pagelist.js   |               |              | 25%          |  
|               |               | form.js       |              | 25%          | 
|               |               |               | lightbox.js  | 25%          |
| bundle1.js    | bundle2.js    | bundle3.js    | bundle4.js   | 25%          |

## Implementation

### Example PageTheme::registerAssets()
```php
	public function registerAssets() {

        $this->assetBundler = AssetBundler::getInstance();

        $assetTypes = array(

            'css' => array(
                'styles.css'                    => array('themes/your_theme/css/styles.css', array(), 'your_package'),
                'bootstrap-modified.css'        => array('themes/your_theme/css/bootstrap-modified.css', array(), 'your_package'),
                'font-awesome'                  => null
            ),

            'javascript' => array(
                'jquery'                        => null,
                'global/settings.js'            => array('themes/your_theme/js/global/settings.js', array(), 'your_package'),
                'global/utilities.js'           => array('themes/your_theme/js/global/utilities.js', array(), 'your_package'),
                'page/home.js'                  => array('themes/your_theme/js/page/home.js', array(), 'your_package'),
                'main.js'                       => array('themes/your_theme/js/main.js', array(), 'your_package')
            )
        );

        foreach($assetTypes as $assetType => $assets) {
            foreach($assets as $assetHandle => $asset) {

                //Register the asset if needed
                if($asset !== null) {
                    $asset = $this->al->register($assetType, $assetHandle, $asset[0],$asset[1],$asset[2]);
                }

                //Flag asset to be bundled
                $this->assetBundler->add($assetType, $assetHandle);
            }
        }
    }
```

### PageTheme's footer_bottom.php
There's currently no effective hooks in C5 after rendering, and before post processing, so you'll need to add the following to your theme's footer_bottom (right near footer_required is appropiate)
```php
<?php $theme->assetBundler->bundle(); ?>
```

### Example output
```html
<!-- Asset Master Bundle -->
<script type="text/javascript" src="/application/files/cache/js/67e0367588a828476980f2af413124fb4c9a85dd.js" data-source="/concrete/js/jquery.js /packages/your_package/themes/your_theme/js/global/settings.js /packages/your_package/themes/your_theme/js/global/utilities.js /packages/your_package/themes/your_theme/js/libraries/collapse.js /packages/your_package/themes/your_theme/js/page/home.js /packages/your_package/themes/your_theme/js/main.js"></script>

<!-- Concrete5 controlled bundle -->
<script type="text/javascript" src="/application/files/cache/js/f670ea5e09ee15eee8e58b769dbb4cbeb8fb621f.js" data-source="/concrete/blocks/autonav/templates/responsive_header_navigation/view.js /concrete/js/responsive-slides.js"></script>
```
