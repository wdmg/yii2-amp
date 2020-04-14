<?php

namespace wdmg\amp;

/**
 * Yii2 RSS-feeds manager
 *
 * @category        Module
 * @version         1.0.3
 * @author          Alexsander Vyshnyvetskyy <alex.vyshnyvetskyy@gmail.com>
 * @link            https://github.com/wdmg/yii2-amp
 * @copyright       Copyright (c) 2019 - 2020 W.D.M.Group, Ukraine
 * @license         https://opensource.org/licenses/MIT Massachusetts Institute of Technology (MIT) License
 *
 */

use Yii;
use wdmg\base\BaseModule;
use yii\base\InvalidConfigException;
use yii\helpers\ArrayHelper;
use yii\helpers\Url;

/**
 * RSS-feed module definition class
 */
class Module extends BaseModule
{
    /**
     * {@inheritdoc}
     */
    public $controllerNamespace = 'wdmg\amp\controllers';

    /**
     * {@inheritdoc}
     */
    public $defaultRoute = "list/index";

    /**
     * @var string, the name of module
     */
    public $name = "Google AMP";

    /**
     * @var string, the description of module
     */
    public $description = "AMP pages generator";

    /**
     * @var array list of supported models for displaying a AMP pages
     */
    public $supportModels = [
        'pages' => 'wdmg\pages\models\Pages',
        'news' => 'wdmg\news\models\News',
        'blog' => 'wdmg\blog\models\Posts'
    ];

    /**
     * @var int cache lifetime, `0` - for not use cache
     */
    public $cacheExpire = 3600; // 1 hr.

    /**
     * @var string default route to render AMP pages (use "/" - for root)
     */
    public $ampRoute = "/amp";

    /**
     * @var string the module version
     */
    private $version = "1.0.3";

    /**
     * @var integer, priority of initialization
     */
    private $priority = 5;

    /**
     * {@inheritdoc}
     */
    public function init()
    {
        parent::init();

        // Set version of current module
        $this->setVersion($this->version);

        // Set priority of current module
        $this->setPriority($this->priority);

        // Process and normalize route for frontend
        $this->ampRoute = self::normalizeRoute($this->ampRoute);

    }

    /**
     * {@inheritdoc}
     */
    public function dashboardNavItems($createLink = false)
    {
        $items = [
            'label' => $this->name,
            'icon' => 'fa fa-fw fa-bolt',
            'url' => [$this->routePrefix . '/'. $this->id],
            'active' => (in_array(\Yii::$app->controller->module->id, [$this->id]) &&  Yii::$app->controller->id == 'list'),
        ];
        return $items;
    }

    /**
     * {@inheritdoc}
     */
    public function bootstrap($app)
    {
        parent::bootstrap($app);

        if (isset(Yii::$app->params["amp.supportModels"]))
            $this->supportModels = Yii::$app->params["amp.supportModels"];

        if (isset(Yii::$app->params["amp.cacheExpire"]))
            $this->cacheExpire = Yii::$app->params["amp.cacheExpire"];

        if (isset(Yii::$app->params["amp.ampRoute"]))
            $this->ampRoute = Yii::$app->params["amp.ampRoute"];

        if (!isset($this->supportModels))
            throw new InvalidConfigException("Required module property `supportModels` isn't set.");

        if (!isset($this->cacheExpire))
            throw new InvalidConfigException("Required module property `cacheExpire` isn't set.");

        if (!isset($this->ampRoute))
            throw new InvalidConfigException("Required module property `ampRoute` isn't set.");

        if (!is_array($this->supportModels))
            throw new InvalidConfigException("Module property `supportModels` must be array.");

        if (!is_integer($this->cacheExpire))
            throw new InvalidConfigException("Module property `cacheExpire` must be integer.");

        if (!is_string($this->ampRoute))
            throw new InvalidConfigException("Module property `ampRoute` must be a string.");

        // Add route to pass AMP pages in frontend
        $ampRoute = $this->ampRoute;
        if (empty($ampRoute) || $ampRoute == "/") {
            $app->getUrlManager()->addRules([
                [
                    'pattern' => '/<url:[\w-\/]+>',
                    'route' => 'admin/amp/default',
                    'suffix' => ''
                ],
                '/<url:[\w-\/]+>' => 'admin/amp/default'
            ], true);
        } else if (is_string($ampRoute)) {
            $app->getUrlManager()->addRules([
                [
                    'pattern' => $ampRoute . '/<url:[\w-\/]+>',
                    'route' => 'admin/amp/default',
                    'suffix' => ''
                ],
                $ampRoute . '/<url:[\w-\/]+>' => 'admin/amp/default'
            ], true);
        }

        // Attach to events of create/change/remove of models for the subsequent clearing cache
        if (!($app instanceof \yii\console\Application)) {
            if ($cache = $app->getCache()) {
                if (is_array($models = $this->supportModels)) {
                    foreach ($models as $name => $class) {
                        if (class_exists($class)) {
                            $model = new $class();
                            \yii\base\Event::on($class, $model::EVENT_AFTER_INSERT, function ($event) use ($cache) {
                                $cache->delete(md5('google-amp'));
                            });
                            \yii\base\Event::on($class, $model::EVENT_AFTER_UPDATE, function ($event) use ($cache) {
                                $cache->delete(md5('google-amp'));
                            });
                            \yii\base\Event::on($class, $model::EVENT_AFTER_DELETE, function ($event) use ($cache) {
                                $cache->delete(md5('google-amp'));
                            });
                        }
                    }
                }
            }
        }
    }

    /**
     * Generate base AMP URL
     *
     * @return null|string
     */
    public function getBaseURL() {
        $url = null;
        $ampRoute = $this->ampRoute;
        if (empty($ampRoute) || $ampRoute == "/") {
            $url = Url::to('/amp', true);
        } else {
            $url = Url::to($ampRoute . '/', true);
        }

        return $url;
    }

    /**
     * Build URL to AMP page
     *
     * @param null $url
     * @return bool|mixed
     */
    public function buildAmpPageUrl($url = null) {

        if ($url) {
            $base = Url::base(true);
            $amp_base = $this->getBaseURL();

            return str_replace($base, rtrim($amp_base, '/'), $url);
        }

        return false;
    }

    /**
     * Get items for building a Google AMP pages
     *
     * @return array
     */
    public function getAmpItems() {
        $items = [];
        if (is_array($models = $this->supportModels)) {
            foreach ($models as $name => $class) {

                // If class of model exist
                if (class_exists($class)) {

                    $model = new $class();

                    // If module is loaded
                    if ($model->getModule()) {
                        $append = [];

                        foreach ($model->getAllPublished(['in_amp' => true]) as $item) {
                            $append[] = [
                                'url' => (isset($item->url)) ? $item->url : null,
                                'name' => (isset($item->name)) ? $item->name : null,
                                'title' => (isset($item->title)) ? $item->title : null,
                                'image' => (isset($item->image)) ? $model->getImagePath(true) . '/' . $item->image : null,
                                'description' => (isset($item->excerpt)) ? $item->excerpt : ((isset($item->description)) ? $item->description : null),
                                'content' => (isset($item->content)) ? $item->content : null,
                                'updated_at' => (isset($item->updated_at)) ? $item->updated_at : null,
                                'status' => (isset($item->status)) ? (($item->status) ? true : false) : false
                            ];
                        };
                        $items = ArrayHelper::merge($items, $append);
                    }
                }
            }
        }

        return $items;
    }

    /**
     * Searches page resource among known URLs
     *
     * @param $url
     * @return null if page not exist in AMP list
     */
    public function findPage($url = null) {

        if (is_null($url))
            return null;

        if ($this->cacheExpire !== 0 && ($cache = Yii::$app->getCache())) {
            $data = $cache->getOrSet(md5('google-amp'), function () {
                return $this->getAmpItems();
            }, intval($this->cacheExpire));
        } else {
            $data = $this->getAmpItems();
        }

        foreach ($data as $item) {
            if ($item['url'] === $url) {
                return $item;
            }
        }

        return null;
    }
}