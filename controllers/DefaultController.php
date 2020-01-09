<?php

namespace wdmg\amp\controllers;

use Yii;
use yii\web\Controller;
use yii\web\Response;
use yii\helpers\ArrayHelper;
use yii\helpers\Url;

/**
 * DefaultController implements actions
 */
class DefaultController extends Controller
{

    public $defaultAction = 'amp';

    /**
     * Displays the rss-feed in xml for frontend.
     *
     * @return string
     */
    public function actionAmp() {

        $module = $this->module;
        if ($module->cacheExpire !== 0 && ($cache = Yii::$app->getCache())) {
            $data = $cache->getOrSet(md5('google-amp'), function () use ($module)  {
                return [
                    'items' => $module->getAmpItems(),
                    'builded_at' => date('r')
                ];
            }, intval($module->cacheExpire));
        } else {
            $data = [
                'items' => $module->getAmpItems(),
                'builded_at' => date('r')
            ];
        }

        Yii::$app->response->format = Response::FORMAT_RAW;
        Yii::$app->getResponse()->getHeaders()->set('Content-Type', 'text/xml; charset=UTF-8');
        return $this->renderPartial('amp', [
            'items' => $data['items']
        ]);
    }
}
