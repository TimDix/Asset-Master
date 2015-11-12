<?php
namespace Concrete\Package\AssetMaster;

defined('C5_EXECUTE') or die('Access Denied.');

class Controller extends \Concrete\Core\Package\Package {

    protected $pkgHandle = 'asset_master';
    protected $appVersionRequired = '5.7.0';
    protected $pkgVersion = '0.9.0';
    protected $pkgAutoloaderMapCoreExtensions = true;

    public function getPackageDescription() {
    	return t("Adds helper functionallity to bundle assets.");
    }

    public function getPackageName() {
         return t("Asset Master");
    }
}
