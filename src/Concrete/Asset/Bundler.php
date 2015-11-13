<?php
namespace Concrete\Package\AssetMaster\Asset;

defined('C5_EXECUTE') or die('Access Denied.');

class Bundler {
	protected $assetListInstance = null;
	protected $responseAssetGroup = null;

	protected $assets = array();

	private static $instance = null;

	public static function getInstance() {
		if(self::$instance == null ){
			$al = \Concrete\Core\Asset\AssetList::getInstance();
			$rag = \Concrete\Core\Http\ResponseAssetGroup::get();
			self::$instance = new self($al, $rag);
		}

		return self::$instance;
	}

	public function __construct($assetListInstance, $responseAssetGroup) {
		$this->assetListInstance = $assetListInstance;
		$this->responseAssetGroup = $responseAssetGroup;
	}

	public function add($assetType, $assetHandle) {
		$this->assets[$assetType][] = $assetHandle;
	}

	public function remove($assetType, $assetHandle) {
		unset($this->assets[$assetType][$assetHandle]);
	}

	public function bundle() {

		$processedBundledAssets = array();

		//Add all bundled assets as required
		foreach($this->assets as $assetType => $assets) {
			foreach($assets as $assetHandle) {
				$this->responseAssetGroup->requireAsset($assetType, $assetHandle);
				
			}
		}

		if(\Config::get('concrete.cache.assets')) {

			//Populate bundledAssets with asset objects from handles, and mark as included
			$bundledAssets = array();
	        foreach($this->responseAssetGroup->getRequiredAssetsToOutput() as $asset) {
	        	if( is_array($this->assets[$asset->getAssetType()]) &&  array_search($asset->getAssetHandle(), $this->assets[$asset->getAssetType()]) !== false) {
	        		$bundledAssets[$asset->getAssetType()][] = $asset;
	        		$this->responseAssetGroup->markAssetAsIncluded($asset->getAssetType(), $asset->getAssetHandle());
	        	}
	        }

	        $v = \View::getInstance();
	        //1 loop through asset types
	        //2 allow the asset type class to bundle the assets
            foreach ($bundledAssets as $type => $assets) {
                $asset = reset($assets);
                $class = get_class($asset);
                $processedBundledAssets[$type] = call_user_func(array($class, 'process'), $assets);

                $processedAsset = reset($processedBundledAssets[$type]);
                if($processedAsset->getAssetPosition() == $processedAsset::ASSET_POSITION_HEADER) {
                	$v->addHeaderItem($processedAsset->__toString());	
                } else {
                	$v->addFooterItem($processedAsset->__toString());	
                }
            }
	    }

	    return $processedBundledAssets;
	}

	public function getAssets() {
		return $this->assets;
	}

	public function setAssets($assets) {
		$this->assets = $assets;
	}
}
